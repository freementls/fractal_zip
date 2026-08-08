#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Pin test_files57 Arc native passthrough bytes (628596).
 *
 *   php benchmarks/guard_test_files57_bytes.php
 *   php benchmarks/guard_test_files57_bytes.php --from-json=path/to/bench.json
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$expectedBytes = 628596;
$fromJson = null;

foreach (array_slice($argv, 1) as $a) {
	if (is_string($a) && strncmp($a, '--from-json=', 12) === 0) {
		$fromJson = trim(substr($a, 12));
	}
}

if ($fromJson !== null && is_readable($fromJson)) {
	$payload = bench_json_decode_file_assoc_try($fromJson, 'guard_test_files57');
} else {
	$tmp = $repoRoot . '/benchmarks/.guard_test_files57_last.json';
	$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	$cmd = escapeshellarg($phpBin)
		. ' ' . escapeshellarg($repoRoot . '/benchmarks/run_benchmarks.php')
		. ' --only=test_files57 --large --no-case-timeout --json --out-json=' . escapeshellarg($tmp);
	passthru($cmd, $rc);
	if ((int) $rc !== 0) {
		exit((int) $rc);
	}
	$payload = bench_json_decode_file_assoc_try($tmp, 'guard_test_files57');
}

if ($payload === null) {
	exit(1);
}

$row = null;
foreach ($payload['cases'] ?? array() as $c) {
	if (is_array($c) && (string) ($c['label'] ?? '') === 'test_files57') {
		$row = $c;
		break;
	}
}

if ($row === null) {
	fwrite(STDERR, "guard_test_files57: missing case\n");
	exit(1);
}

$got = (int) ($row['fzc_bytes'] ?? -1);
$outer = (string) ($row['outer_codec'] ?? '');
if (abs($got - $expectedBytes) > 1) {
	fwrite(STDERR, "guard_test_files57: FAIL fzc_bytes={$got} expected={$expectedBytes} (±1) outer={$outer}\n");
	exit(1);
}

fwrite(STDOUT, "guard_test_files57: OK fzc_bytes={$got} (expected {$expectedBytes}) outer_codec={$outer}\n");
exit(0);
