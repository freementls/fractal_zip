#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare transform sidecar size + payload gzip-1 on sample pages (bytes tuning).
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
if (!is_file($manifestPath)) {
	fwrite(STDERR, "Run build_enwik8_sample_pages.php\n");
	exit(1);
}
$manifest = json_decode((string) file_get_contents($manifestPath), true);
$scheme = 'words_base94_isp';
$transforms = array('none', 'sort_lines_alpha', 'perm_lines');

$gzip1 = static function (string $s): int {
	$g = @gzdeflate($s, 1);
	return is_string($g) ? strlen($g) : 0;
};

$rows = array();
foreach ($manifest['pages'] ?? array() as $p) {
	$text = (string) file_get_contents($sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['text_path']));
	foreach ($transforms as $tr) {
		$enc = fractal_zip_enwik_text_codec_encode($scheme, $text);
		$t = fractal_zip_enwik_text_transform_apply($tr, (string) $enc['payload'], array('seed' => 1));
		$merged = array_merge($enc['sidecar'], $t['sidecar']);
		$undo = fractal_zip_enwik_text_transform_undo($tr, (string) $t['payload'], $merged);
		$dec = fractal_zip_enwik_text_codec_decode($scheme, $undo, $merged);
		$rows[] = array(
			'title' => substr((string) ($p['title'] ?? ''), 0, 40),
			'transform' => $tr,
			'text_bytes' => strlen($text),
			'payload_bytes' => strlen((string) $t['payload']),
			'payload_gzip1' => $gzip1((string) $t['payload']),
			'sidecar_json' => strlen(json_encode($merged, JSON_UNESCAPED_UNICODE) ?: ''),
			'sidecar_gzip1' => $gzip1(json_encode($merged, JSON_UNESCAPED_UNICODE) ?: ''),
			'roundtrip_ok' => $dec === $text,
		);
	}
}

$byTr = array();
foreach ($rows as $r) {
	$tr = $r['transform'];
	if (!isset($byTr[$tr])) {
		$byTr[$tr] = array('payload_gzip1' => 0, 'sidecar_json' => 0, 'sidecar_gzip1' => 0, 'n' => 0);
	}
	$byTr[$tr]['payload_gzip1'] += (int) $r['payload_gzip1'];
	$byTr[$tr]['sidecar_json'] += (int) $r['sidecar_json'];
	$byTr[$tr]['sidecar_gzip1'] += (int) $r['sidecar_gzip1'];
	$byTr[$tr]['n']++;
}

$out = array('generated' => date('c'), 'scheme' => $scheme, 'by_transform' => $byTr, 'rows' => $rows);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_sidecar_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo "sidecar probe ({$scheme}) → {$path}\n";
foreach ($byTr as $tr => $sum) {
	echo "  {$tr}: payload_gzip1=" . number_format($sum['payload_gzip1'])
		. ' sidecar_json=' . number_format($sum['sidecar_json'])
		. ' sidecar_gzip1=' . number_format($sum['sidecar_gzip1']) . "\n";
}
