#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Pin test_files55_sample .fz bytes (Arc-class outer; tracker 2 158 962).
 * Uses --no-baseline-cache so a stale baseline_cache row cannot force zstd outer.
 *
 *   php benchmarks/guard_test_files55_sample_bytes.php
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$expectedBytes = 2158962;
$maxSlack = 25000;
$fromJson = null;

foreach (array_slice($argv, 1) as $a) {
	if (is_string($a) && strncmp($a, '--expected-bytes=', 17) === 0) {
		$v = trim(substr($a, 17));
		if ($v !== '' && ctype_digit($v)) {
			$expectedBytes = (int) $v;
		}
	}
	if (is_string($a) && strncmp($a, '--from-json=', 12) === 0) {
		$p = trim(substr($a, 12));
		if ($p !== '') {
			$fromJson = $p;
		}
	}
}

$tmp = $repoRoot . '/benchmarks/.guard_test_files55_sample_last.json';

if ($fromJson !== null && is_readable($fromJson)) {
	$payload = bench_json_decode_file_assoc_try($fromJson, 'guard_test_files55_sample');
} else {
	$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	$cmd = escapeshellarg($phpBin)
		. ' ' . escapeshellarg($repoRoot . '/benchmarks/run_benchmarks.php')
		. ' --only=test_files55_sample --large --no-case-timeout --no-baseline-cache --json'
		. ' --out-json=' . escapeshellarg($tmp);
	passthru($cmd, $rc);
	if ((int) $rc !== 0) {
		exit((int) $rc);
	}
	$payload = bench_json_decode_file_assoc_try($tmp, 'guard_test_files55_sample');
}
if ($payload === null) {
	exit(1);
}

$row = null;
foreach ($payload['cases'] ?? array() as $c) {
	if (is_array($c) && (string) ($c['label'] ?? '') === 'test_files55_sample') {
		$row = $c;
		break;
	}
}

if ($row === null) {
	fwrite(STDERR, "guard_test_files55_sample: missing case\n");
	exit(1);
}

$got = (int) ($row['fzc_bytes'] ?? -1);
$outer = (string) ($row['outer_codec'] ?? '');
if ($got > $expectedBytes + $maxSlack || $got < $expectedBytes - $maxSlack) {
	fwrite(STDERR, "guard_test_files55_sample: FAIL fzc_bytes={$got} expected≈{$expectedBytes} (±{$maxSlack}) outer={$outer}\n");
	exit(1);
}

fwrite(STDOUT, "guard_test_files55_sample: OK fzc_bytes={$got} (expected≈{$expectedBytes}) outer_codec={$outer}\n");
exit(0);
