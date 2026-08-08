#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Guardrail for test_files133_sample: pin fzc_bytes and cap sum(zip_seconds).
 *
 * Defaults: fzc_bytes=8435136, sum(zip_seconds) <= 120 (bytes-first --large; tightened after large-balanced ~57s).
 *
 * Usage:
 *   php benchmarks/guard_test_files133_sample_bytes_and_time.php
 *   php benchmarks/guard_test_files133_sample_bytes_and_time.php --max-fzc-seconds=400
 *   php benchmarks/guard_test_files133_sample_bytes_and_time.php --from-json=path/to/bench.json
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$expectedBytes = 8435136;
$maxFzcSeconds = 120.0;
$fromJson = null;
$repeatRuns = 1;
$extraBenchArgs = '--large --no-case-timeout --bench-profile=large-fast';

foreach (array_slice($argv, 1) as $a) {
	if (is_string($a) && strncmp($a, '--max-fzc-seconds=', 18) === 0) {
		$v = trim(substr($a, 18));
		if ($v !== '' && is_numeric($v)) {
			$maxFzcSeconds = max(0.0, (float) $v);
		}
	}
	if (is_string($a) && strncmp($a, '--from-json=', 12) === 0) {
		$p = trim(substr($a, 12));
		if ($p !== '') {
			$fromJson = $p;
		}
	}
	if (is_string($a) && strncmp($a, '--repeat=', 9) === 0) {
		$v = trim(substr($a, 9));
		if ($v !== '' && ctype_digit($v)) {
			$repeatRuns = max(1, min(5, (int) $v));
		}
	}
	if (is_string($a) && strncmp($a, '--expected-bytes=', 17) === 0) {
		$v = trim(substr($a, 17));
		if ($v !== '' && ctype_digit($v)) {
			$expectedBytes = (int) $v;
		}
	}
}

$payload = null;
$jsonPathUsed = '';

if ($fromJson !== null) {
	$jsonPathUsed = $fromJson;
	if (!is_readable($jsonPathUsed)) {
		fwrite(STDERR, "guard_133_sample: cannot read JSON: {$jsonPathUsed}\n");
		exit(1);
	}
	$payload = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_133_sample');
	if ($payload === null) {
		exit(1);
	}
} else {
	$tmpJson = $repoRoot . '/benchmarks/.guard_test_files133_sample_last.json';
	$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	$cmd = escapeshellarg($phpBin)
		. ' ' . escapeshellarg($repoRoot . '/benchmarks/run_benchmarks.php')
		. ' --only=test_files133_sample ' . $extraBenchArgs
		. ' --repeat=' . (string) $repeatRuns
		. ' --json --out-json=' . escapeshellarg($tmpJson);
	passthru($cmd, $rc);
	if ((int) $rc !== 0) {
		fwrite(STDERR, "guard_133_sample: benchmark failed exit={$rc}\n");
		exit((int) $rc);
	}
	$jsonPathUsed = $tmpJson;
	$payload = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_133_sample');
	if ($payload === null) {
		exit(1);
	}
}

$row = null;
foreach ($payload['cases'] ?? array() as $c) {
	if (is_array($c) && (string) ($c['label'] ?? '') === 'test_files133_sample') {
		$row = $c;
		break;
	}
}

$errors = array();
if ($row === null) {
	$errors[] = 'test_files133_sample: missing from JSON';
} else {
	$got = isset($row['fzc_bytes']) ? (int) $row['fzc_bytes'] : -1;
	if ($got !== $expectedBytes) {
		$errors[] = "fzc_bytes={$got}, expected={$expectedBytes}";
	}
	$zipS = isset($row['zip_seconds']) ? (float) $row['zip_seconds'] : null;
	if ($zipS === null) {
		$errors[] = 'missing zip_seconds';
	} elseif ($zipS > $maxFzcSeconds) {
		$errors[] = "zip_seconds={$zipS} exceeds max={$maxFzcSeconds}";
	}
}

if ($errors !== array()) {
	fwrite(STDERR, "guard_test_files133_sample: FAIL\n");
	foreach ($errors as $e) {
		fwrite(STDERR, "  - {$e}\n");
	}
	fwrite(STDERR, "  json: {$jsonPathUsed}\n");
	exit(1);
}

$zipOut = isset($row['zip_seconds']) ? (string) $row['zip_seconds'] : '?';
$outer = isset($row['outer_codec']) ? (string) $row['outer_codec'] : '?';
fwrite(
	STDOUT,
	"guard_test_files133_sample: OK fzc_bytes={$expectedBytes} zip_seconds={$zipOut} outer_codec={$outer} <= {$maxFzcSeconds}\n"
);
exit(0);
