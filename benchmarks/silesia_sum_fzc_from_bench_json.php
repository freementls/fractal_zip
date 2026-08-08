#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Sum **`cases[].fzc_bytes`** (and optional baselines) from a **`run_benchmarks.php --json`** artifact
 * for Mahoney-style comparison: twelve Silesia Squash dirs in one run, or **`test_files133`** alone.
 *
 * Usage:
 *   php benchmarks/silesia_sum_fzc_from_bench_json.php path/to/bench.json
 *   php benchmarks/silesia_sum_fzc_from_bench_json.php path/to/bench.json --labels=test_files133
 *
 * Default **`--labels=`** is the twelve Squash singles (comma-separated). Compare the printed **fzc sum**
 * to the **Total** column on https://mattmahoney.net/dc/silesia.html (per-file compressor sum — same
 * counting model only when each case is one Silesia file). See **`benchmarks/SILESIA_BENCHMARK.md`**.
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$silesia12 = [
	'test_files108',
	'test_files116', 'test_files117', 'test_files118', 'test_files119', 'test_files120',
	'test_files124', 'test_files125', 'test_files126',
	'test_files130', 'test_files131', 'test_files132',
];

$path = $argv[1] ?? '';
$labelsArg = null;
foreach (array_slice($argv, 2) as $a) {
	if (str_starts_with($a, '--labels=')) {
		$labelsArg = substr($a, strlen('--labels='));
	}
}
if ($path === '' || $path[0] === '-') {
	fwrite(STDERR, "Usage: php benchmarks/silesia_sum_fzc_from_bench_json.php <bench.json> [--labels=a,b,c]\n");
	exit(2);
}
if (!is_readable($path)) {
	fwrite(STDERR, "Cannot read: {$path}\n");
	exit(2);
}

$want = null;
if ($labelsArg !== null && trim($labelsArg) !== '') {
	$want = array_fill_keys(array_values(array_filter(array_map('trim', explode(',', $labelsArg)))), true);
} else {
	$want = array_fill_keys($silesia12, true);
}

$data = bench_json_decode_file_assoc_try($path, 'silesia_sum_fzc_from_bench_json', 512, 0, false);
if ($data === null) {
	exit(1);
}
$cases = $data['cases'] ?? null;
if (!is_array($cases)) {
	fwrite(STDERR, "JSON missing cases[] array\n");
	exit(1);
}

$sumRaw = $sumFzc = $sumGz = $sum7z = $sumExt = 0;
$n = 0;
$missing = $want;
foreach ($cases as $row) {
	if (!is_array($row)) {
		continue;
	}
	$label = isset($row['label']) ? (string) $row['label'] : '';
	if ($label === '' || !isset($want[$label])) {
		continue;
	}
	unset($missing[$label]);
	$n++;
	$sumRaw += (int) ($row['raw_bytes'] ?? 0);
	$sumFzc += (int) ($row['fzc_bytes'] ?? 0);
	$sumGz += (int) ($row['gzip9_bundle_bytes'] ?? 0);
	$sum7z += (int) ($row['seven_zip_folder_bytes'] ?? 0);
	$sumExt += (int) ($row['best_ext_folder_bytes'] ?? 0);
}

ksort($missing);
if ($missing !== []) {
	fwrite(STDERR, 'warning: missing labels in JSON: ' . implode(', ', array_keys($missing)) . "\n");
}

echo "cases_matched={$n} path={$path}\n";
echo "sum_raw_bytes={$sumRaw}\n";
echo "sum_fzc_bytes={$sumFzc}\n";
echo "sum_gzip9_bundle_bytes={$sumGz}\n";
echo "sum_seven_zip_folder_bytes={$sum7z}\n";
echo "sum_best_ext_folder_bytes={$sumExt}\n";

// Mahoney reference total (7-Zip -mx=9); see SILLESIA_BENCHMARK.md
$mahoney7z = 48792760;
if ($n === 12 && $sumRaw === 211938580) {
	echo "mahoney_7zip_mx9_total_ref={$mahoney7z}\n";
	echo "sum_fzc_minus_mahoney_7zip=" . ($sumFzc - $mahoney7z) . "\n";
	echo "sum_best_ext_minus_mahoney_7zip=" . ($sumExt - $mahoney7z) . "\n";
}
