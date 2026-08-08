#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Profile phda9_xml single-stream encode: where time goes, dict effect, parallel prep.
 *
 * Δ = wire − mono_mi (negative = WIN). Reports seconds per phase.
 *
 * Usage: nice -n 19 php benchmarks/bench_phda9_single_stream_profile.php [--pages=96|384]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/benchmarks/bench_world_record_env.php';

$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}

$t0 = microtime(true);
$pageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($sortedChunk, $blob)['sorted_page_xml'];
$tXml = microtime(true) - $t0;

$tool = 'phda9_no_lstm';
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $tool);
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$runs = array(
	'no_dict' => false,
);
$dictPath = fractal_zip_paq_phda9_dict_path();
if ($dictPath !== null && is_file($dictPath)) {
	$runs['with_dict'] = true;
}

$results = array();
foreach ($runs as $label => $useDict) {
	$t1 = microtime(true);
	$r = fractal_zip_enwik_phda9_english_compress($pageXml, array(
		'tool' => $tool,
		'use_dict' => $useDict,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	$tPhda = microtime(true) - $t1;
	$results[$label] = array(
		'bytes' => $r['bytes'] ?? null,
		'rt' => !empty($r['roundtrip_ok']),
		'phda_sec' => round($tPhda, 2),
		'status' => (string) ($r['status'] ?? ''),
	);
}

$slice = (string) $split['header'];
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
}
$slice .= (string) $split['footer'];
$tmp = sys_get_temp_dir() . '/fz_phda9_prof_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . '/w';
@mkdir($work, 0700, true);
file_put_contents($work . '/enwik8', $slice);

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');

$t2 = microtime(true);
$fz = new fractal_zip(256, false, true, null, false);
$fz->zip_folder($work, false);
$tWire = microtime(true) - $t2;
$fzc = $work . '.fz';
$wireBytes = is_file($fzc) ? (int) filesize($fzc) : 0;

printf("bench_phda9_single_stream_profile | @%dp\n\n", $pages);
printf("  sorted_page_xml=%s B  xml_build=%.2fs\n", number_format(strlen($pageXml)), $tXml);
printf("  tool=%s  dict=%s\n\n", $tool, $dictPath ?? 'none');
printf("  %-12s %10s %8s %6s\n", 'mode', 'FZPA B', 'phda_s', 'RT');
foreach ($results as $label => $row) {
	printf("  %-12s %10s %8s %6s\n",
		$label,
		$row['bytes'] !== null ? number_format((int) $row['bytes']) : 'fail',
		$row['phda_sec'],
		$row['rt'] ? 'ok' : 'no');
}
printf("\n  integrated wire (single-stream .fz)=%s B  total_sec=%.2f\n", number_format($wireBytes), $tWire);
printf("\n  NOTE: single-stream phda9 is ONE adaptive context — parallel chunk phda9\n");
printf("  cannot match these bytes (+36 KiB @384p) but may overlap prep with I/O only.\n");

fractal_zip_enwik_recursive_remove($tmp);
fwrite(STDERR, "OK bench_phda9_single_stream_profile\n");
