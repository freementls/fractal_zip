#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * SA mode must be a superset of serial all_substrings_count on case-30 slice.
 *
 * Usage: php benchmarks/all_substrings_count_sa_regression.php
 */

putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA=1');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA_MIN_BYTES=256');
putenv('FRACTAL_ZIP_PEEL_GPU=0');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY=0');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SLIDE=0');

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip_gpu_substring.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$p = $repoRoot . DIRECTORY_SEPARATOR . 'test_files30' . DIRECTORY_SEPARATOR . 'test_files2.txt';
if (!is_file($p)) {
	fwrite(STDERR, "SKIP missing {$p}\n");
	exit(0);
}
$raw = file_get_contents($p);
if ($raw === false || $raw === '') {
	exit(1);
}

if (!fractal_zip_gpu_substring_sa_available()) {
	fwrite(STDERR, "SKIP SA binary missing\n");
	exit(0);
}

$fz = new fractal_zip(64, false, false, null, false);
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA=0');
$serial = $fz->all_substrings_count($raw);
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA=1');
$sa = $fz->all_substrings_count($raw);

foreach ($serial as $k => $v) {
	if (!isset($sa[$k]) || $sa[$k] !== $v) {
		fwrite(STDERR, "FAIL SA missing or changed serial entry\n");
		exit(1);
	}
}

$extra = count($sa) - count($serial);
fwrite(STDOUT, "OK all_substrings_count_sa_regression (extra={$extra} superset=ok)\n");
