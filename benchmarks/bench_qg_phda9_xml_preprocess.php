#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * QG hybrid preprocess before phda9 on sorted page XML @96p.
 *
 * Δ = (phda9(preprocess payload) + sidecar) − phda9(raw xml). Δ < 0 = WIN.
 *
 * Usage: nice -n 19 php benchmarks/bench_qg_phda9_xml_preprocess.php [--pages=96]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once dirname($repo) . '/quantum_grammar/src/HybridRootCodec.php';

$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	$src = $repo . '/enwik8';
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$chunk = array();
for ($i = 0; $i < $n; $i++) {
	$chunk[] = array('origIndex' => $i, 'start' => (int) $split['pages'][$i]['start'], 'len' => (int) $split['pages'][$i]['len']);
}
$xml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];

putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
$dict = $repo . '/benchmarks/.phda9_external_dict.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$phda = static function (string $plain): array {
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => 'phda9_no_lstm',
		'use_dict' => getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT') !== false,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	return array(
		'bytes' => $r['bytes'] ?? null,
		'rt' => !empty($r['roundtrip_ok']),
		'sec' => (float) ($r['seconds'] ?? 0.0),
		'status' => (string) ($r['status'] ?? ''),
	);
};

$t0 = microtime(true);
$base = $phda($xml);
$tBase = microtime(true) - $t0;

$model = HybridRootCodec::mineModel($xml);
$enc = HybridRootCodec::encode($xml, $model, true);
$sidecarJson = json_encode($enc['sidecar']);
$sidecarBytes = is_string($sidecarJson) ? strlen($sidecarJson) : 0;
$vocabPath = sys_get_temp_dir() . '/qghr_vocab_' . getmypid() . '.json';
file_put_contents($vocabPath, json_encode(array(
	'word' => $model['word']['vocab'],
	'sub' => $model['sub']['vocab'],
)));

$t1 = microtime(true);
$hy = $phda($enc['payload']);
$tHy = microtime(true) - $t1;

$rest = HybridRootCodec::decode($enc['payload'], array_merge($enc['sidecar'], array(
	'word_vocab' => $model['word']['vocab'],
	'sub_vocab' => $model['sub']['vocab'],
)));
$rt = hash_equals($xml, $rest);

printf("bench_qg_phda9_xml_preprocess | @%dp\n\n", $pages);
printf("  sorted_page_xml=%s B\n", number_format(strlen($xml)));
printf("  phda9 raw FZPA=%s B  sec=%.2f  RT=%s\n",
	$base['bytes'] !== null ? number_format((int) $base['bytes']) : 'FAIL',
	$tBase, $base['rt'] ? 'ok' : 'FAIL');
$hyOk = $hy['bytes'] !== null && $hy['rt'];
printf("  hybrid payload FZPA=%s B  sidecar=%s  LTCB=%s  sec=%.2f  status=%s\n",
	$hy['bytes'] !== null ? number_format((int) $hy['bytes']) : 'FAIL',
	number_format($sidecarBytes),
	$hy['bytes'] !== null ? number_format((int) $hy['bytes'] + $sidecarBytes) : 'FAIL',
	$tHy, $hy['status'] !== '' ? $hy['status'] : ($hyOk ? 'ok' : 'FAIL'));
if ($base['bytes'] !== null && $hy['bytes'] !== null) {
	printf("  Δ phda9=%+d  Δ LTCB=%+d  preprocess RT=%s  GATE=%s\n",
		(int) $hy['bytes'] - (int) $base['bytes'],
		((int) $hy['bytes'] + $sidecarBytes) - (int) $base['bytes'],
		$rt ? 'ok' : 'FAIL',
		$hyOk && ((int) $hy['bytes'] + $sidecarBytes) < (int) $base['bytes'] ? 'PASS' : 'FAIL');
} else {
	printf("  phda9 on hybrid binary payload FAIL (not valid English/XML — QG cannot feed phda9 directly)\n");
	printf("  preprocess RT=%s\n", $rt ? 'ok' : 'FAIL');
}
printf("  hybrid meta: %s\n", json_encode($enc['meta']));

@unlink($vocabPath);
fwrite(STDERR, "OK bench_qg_phda9_xml_preprocess\n");
