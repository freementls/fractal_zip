#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Repeat fractal_zip perf_test for median/p90 stability (same idea as LOM/perf_repeat.php).
 *
 * Usage (from repo root):
 *   php benchmarks/perf_repeat.php 11 -- --only=test_files54_sample --no-zip-time-budget
 *   php benchmarks/perf_repeat.php 5 -- --preset=default --budget-sec=120
 *   php benchmarks/perf_repeat.php 7 -- --quick --no-stack-sample   # lower wall time per repeat (FRACTAL_ZIP_SPEED + fast preset)
 *
 * Arguments before `--` are passed to perf_test as --repeat=N. Everything after `--` is forwarded.
 */

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$perfTest = $repoRoot . DIRECTORY_SEPARATOR . 'perf_test.php';

$n = 11;
$forward = array_slice($argv, 1);
$sep = array_search('--', $forward, true);
if ($sep !== false) {
	$before = array_slice($forward, 0, $sep);
	$after = array_slice($forward, $sep + 1);
	if ($before !== [] && is_numeric($before[0])) {
		$n = max(1, min(500, (int) $before[0]));
	}
	$forward = $after;
} elseif ($forward !== [] && is_numeric($forward[0])) {
	$n = max(1, min(500, (int) array_shift($forward)));
}

$php = PHP_BINARY;
$cmd = escapeshellarg($php) . ' ' . escapeshellarg($perfTest)
	. ' --repeat=' . $n;
foreach ($forward as $a) {
	$cmd .= ' ' . escapeshellarg($a);
}

passthru($cmd, $exitCode);
exit($exitCode);
