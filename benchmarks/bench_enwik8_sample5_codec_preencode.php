#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * gzip-1 on sample5: raw article text vs best lab pipeline (words_base94 + sort_lines_alpha).
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
if (!is_file($manifestPath)) {
	exit(1);
}
$manifest = json_decode((string) file_get_contents($manifestPath), true);
$cfg = array('scheme' => 'words_base94_isp', 'transform' => 'sort_lines_alpha', 'seed' => 1);

$sumRaw = 0;
$sumRawG = 0;
$sumCodecG = 0;
foreach ($manifest['pages'] ?? array() as $p) {
	$text = (string) file_get_contents($sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['text_path']));
	$sumRaw += strlen($text);
	$enc = fractal_zip_enwik_text_codec_encode('words_base94_isp', $text);
	$dec = fractal_zip_enwik_text_codec_decode('words_base94_isp', (string) $enc['payload'], $enc['sidecar']);
	if ($dec !== $text) {
		fwrite(STDERR, "FAIL lossless roundtrip on page text\n");
		exit(1);
	}
	$t = fractal_zip_enwik_text_transform_apply('sort_lines_alpha', (string) $enc['payload'], array('seed' => 1));
	$measRaw = fractal_zip_enwik_text_measure_payload($text);
	$measCodec = fractal_zip_enwik_text_measure_payload((string) $t['payload']);
	$sumRawG += (int) ($measRaw['gzip1_bytes'] ?? 0);
	$sumCodecG += (int) ($measCodec['gzip1_bytes'] ?? 0);
}

$out = array(
	'generated' => date('c'),
	'pages' => count($manifest['pages'] ?? array()),
	'raw_text_bytes' => $sumRaw,
	'raw_text_gzip1' => $sumRawG,
	'codec_gzip1' => $sumCodecG,
	'codec' => 'words_base94_isp',
	'transform' => 'sort_lines_alpha',
	'vs_text_pct' => $sumRawG > 0 ? round(100.0 * $sumCodecG / $sumRawG, 2) : null,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_sample5_codec_preencode.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo "sample5 preencode: raw_text_gzip1=" . number_format($sumRawG)
	. ' codec_gzip1=' . number_format($sumCodecG)
	. ' (' . ($out['vs_text_pct'] ?? '?') . "% of raw text gzip1)\n";
echo "  wrote {$path}\n";
