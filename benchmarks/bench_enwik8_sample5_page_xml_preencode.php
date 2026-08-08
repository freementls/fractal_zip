#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * gzip-1 on full page XML (not text-only): raw vs textcodec-packed pages.
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
if (!is_file($manifestPath)) {
	exit(1);
}
$manifest = json_decode((string) file_get_contents($manifestPath), true);
$cfg = array('scheme' => 'words_base94', 'transform' => 'sort_lines_alpha', 'seed' => 1);

$sumRaw = 0;
$sumRawG = 0;
$sumPackG = 0;
foreach ($manifest['pages'] ?? array() as $p) {
	$pagePath = $sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['page_path']);
	$pageXml = (string) file_get_contents($pagePath);
	$sumRaw += strlen($pageXml);
	$patterns = array();
	$nextId = 0;
	$packed = fractal_zip_enwik_pack_text_regions_with_codec($pageXml, $cfg, $patterns, $nextId);
	$g0 = strlen(gzdeflate($pageXml, 1));
	$g1 = strlen(gzdeflate($packed, 1));
	$sumRawG += is_int($g0) ? $g0 : 0;
	$sumPackG += is_int($g1) ? $g1 : 0;
}

$out = array(
	'generated' => date('c'),
	'raw_page_bytes' => $sumRaw,
	'raw_page_gzip1' => $sumRawG,
	'codec_page_gzip1' => $sumPackG,
	'vs_page_pct' => $sumRawG > 0 ? round(100.0 * $sumPackG / $sumRawG, 2) : null,
	'codec' => 'words_base94',
	'transform' => 'sort_lines_alpha',
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_sample5_page_xml_preencode.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo 'sample5 page XML: raw_gzip1=' . number_format($sumRawG)
	. ' codec_gzip1=' . number_format($sumPackG)
	. ' (' . ($out['vs_page_pct'] ?? '?') . "%)\n";
