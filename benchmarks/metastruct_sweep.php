#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Batch fractal-leg probe: default vs freq vs metastruct marker proposals.
 *
 *   php benchmarks/metastruct_sweep.php test_files107 test_files53 test_files74
 *   php benchmarks/metastruct_sweep.php --json test_files107 test_files75
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');
putenv('FRACTAL_ZIP_WEB_REF=0');

$jsonOut = in_array('--json', $argv, true);
$corpora = array_values(array_filter($argv, static fn (string $a): bool => $a !== '--json' && !str_starts_with($a, '-')));
if ($corpora === array()) {
	$corpora = array('test_files107', 'test_files53', 'test_files74', 'test_files75', 'test_files110', 'test_files2');
}

$rows = array();
$fz = new fractal_zip(120, true, false, null, false);
$def = fractal_zip_marker_default_config();

foreach ($corpora as $corpus) {
	$path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
	if (!is_dir($path)) {
		$rows[] = array('corpus' => $corpus, 'error' => 'missing');
		continue;
	}
	$sample = fractal_zip_marker_collect_layer_stripped_sample($path, 1024 * 1024);
	$freq = fractal_zip_marker_propose_from_sample($sample, true);
	list($merged, $desc) = fractal_zip_metastruct_marker_propose($path, $sample, true);
	$d = fractal_zip_marker_probe_fractal_outer_len($fz, $path, $def);
	$pF = fractal_zip_marker_probe_fractal_outer_len($fz, $path, $freq);
	$pM = fractal_zip_marker_probe_fractal_outer_len($fz, $path, $merged);
	$rows[] = array(
		'corpus' => $corpus,
		'sample_bytes' => strlen($sample),
		'textish_ratio' => round((float) ($desc['textish_ratio'] ?? 0), 4),
		'dominant_content' => $desc['dominant_content_profile'] ?? null,
		'dominant_delim' => $desc['dominant_delimiter_profile'] ?? null,
		'mid_freq' => $freq['mid'],
		'mid_merged' => $merged['mid'],
		'fractal_leg_default' => $d,
		'fractal_leg_freq' => $pF,
		'fractal_leg_merged' => $pM,
		'delta_merged' => $pM - $d,
		'delta_freq' => $pF - $d,
		'would_apply_freq' => $pF < $d,
		'would_apply_merged' => $pM < $d,
		'note' => 'fractal-leg probe only; small folders may use FZB/gzip-fast and skip marker pass in full zip',
	);
	if (!$jsonOut) {
		printf(
			"%-28s textish=%.2f  leg: def=%d freq=%d(%+d) ms=%d(%+d)  mid %s→%s\n",
			$corpus,
			(float) ($desc['textish_ratio'] ?? 0),
			$d,
			$pF,
			$pF - $d,
			$pM,
			$pM - $d,
			$freq['mid'],
			$merged['mid']
		);
	}
}

if ($jsonOut) {
	echo bench_json_encode_try(array('rows' => $rows), false) . "\n";
}
