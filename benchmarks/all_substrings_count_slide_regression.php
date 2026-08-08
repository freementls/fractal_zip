#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Locks all_substrings_count bytes with FRACTAL_ZIP_GPU_SUBSTRING_SLIDE=1 (Rust seed merge).
 *
 * Usage:
 *   php benchmarks/all_substrings_count_slide_regression.php
 */

putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SLIDE=1');

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip_gpu_substring.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

const EXPECTED_XXH128_SERIAL = 'fba292d0e06e96daca8b79bc408012c1';

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

if (!fractal_zip_gpu_substring_slide_seeds_enabled()) {
	fwrite(STDERR, "FAIL slide env not enabled\n");
	exit(1);
}

$rustOk = fractal_zip_gpu_substring_rust_available();
$fz = new fractal_zip(64, false, false, null, false);
$map = $fz->all_substrings_count($raw);
ksort($map);
$h = hash('xxh128', (string) (bench_json_encode_fingerprint_try($map, JSON_UNESCAPED_UNICODE) ?? ''));

if ($h !== EXPECTED_XXH128_SERIAL) {
	fwrite(STDERR, "FAIL slide regression bytes mismatch\n");
	fwrite(STDERR, '  got xxh128=' . $h . "\n");
	fwrite(STDERR, '  exp xxh128=' . EXPECTED_XXH128_SERIAL . "\n");
	exit(1);
}

fwrite(STDOUT, 'OK all_substrings_count_slide_regression (rust=' . ($rustOk ? 'yes' : 'no') . ", xxh128={$h})\n");
