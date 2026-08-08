#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Locks down fractal_zip::all_substrings_count() output for the case-30 smoke slice under the same env as
 * benchmarks/smoke_inner_pass_regression.php (FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1).
 *
 * If substring enumeration semantics change intentionally, update EXPECTED_* below after verifying wins/regressions
 * (snapshot is full FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1 map for the committed case‑30 member).
 *
 * Usage (repo root):
 *   php benchmarks/all_substrings_count_regression.php
 *
 * CI: tests/run_php_smokes.sh phase 3 (phase 3b next: benchmarks/smoke_repro_folder_zip_roundtrip.php).
 */

putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

// Snapshot for test_files30/test_files2.txt (default 1000 B slice), segment 64, markers < > ".
const EXPECTED_XXH128 = 'fba292d0e06e96daca8b79bc408012c1';
const EXPECTED_N = 292;
const EXPECTED_SUM = 1586;

$p = $repoRoot . DIRECTORY_SEPARATOR . 'test_files30' . DIRECTORY_SEPARATOR . 'test_files2.txt';
if (!is_file($p)) {
	fwrite(STDERR, "SKIP missing {$p}\n");
	exit(0);
}
$raw = file_get_contents($p);
if ($raw === false || $raw === '') {
	fwrite(STDERR, "FAIL read {$p}\n");
	exit(1);
}

$fz = new fractal_zip(64, false, false, null, false);
$map = $fz->all_substrings_count($raw);
ksort($map);

$n = count($map);
$sum = array_sum($map);
$h = hash('xxh128', (string) (bench_json_encode_fingerprint_try($map, JSON_UNESCAPED_UNICODE) ?? ''));

if ($h !== EXPECTED_XXH128 || $n !== EXPECTED_N || $sum !== EXPECTED_SUM) {
	fwrite(STDERR, "FAIL all_substrings_count regression\n");
	fwrite(STDERR, '  got xxh128=' . $h . ' n=' . (string) $n . ' sum=' . (string) $sum . "\n");
	fwrite(STDERR, '  exp xxh128=' . EXPECTED_XXH128 . ' n=' . (string) EXPECTED_N . ' sum=' . (string) EXPECTED_SUM . "\n");
	exit(1);
}

fwrite(STDOUT, "OK all_substrings_count_regression (xxh128={$h}, n={$n}, sum={$sum})\n");
