#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Guardrail for lifestyle corpora (74/75/76): enforce exact bytes and max total fzc time.
 *
 * Defaults:
 *   bytes: 74=1307, 75=1251, 76=5807
 *   sum(case zip_seconds) <= 22.0 (bytes-first outer refinement; use --max-fzc-seconds=2.0 for a tight ceiling when tuning)
 *   repeat=3 (median timings per case from run_benchmarks)
 *
 * Usage:
 *   php benchmarks/guard_lifestyle_bytes_and_time.php
 *   php benchmarks/guard_lifestyle_bytes_and_time.php --max-fzc-seconds=22.0
 *   php benchmarks/guard_lifestyle_bytes_and_time.php --repeat=5
 *   php benchmarks/guard_lifestyle_bytes_and_time.php --from-json=benchmarks/.last_bench.json
 *
 * Tiered **`--bench-profile`** / **`--jobs`**: **benchmarks/LARGE_CORPUS_SPEED.md**. Ephemeral **`benchmarks/.last_bench.json`** is gitignored when generated in-repo; use **`php benchmarks/run_benchmarks.php --out-json=…`** to keep a file for **`--from-json`**.
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$maxFzcSeconds = 22.0;
$fromJson = null;
$repeatRuns = 3;

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
			$repeatRuns = max(1, min(25, (int) $v));
		}
	}
}

$payload = null;
$jsonPathUsed = '';

if ($fromJson !== null) {
	$jsonPathUsed = $fromJson;
	if (!is_readable($jsonPathUsed)) {
		fwrite(STDERR, "guard_lifestyle: cannot read JSON file: {$jsonPathUsed}\n");
		exit(1);
	}
	$decoded = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_lifestyle');
	if ($decoded === null) {
		exit(1);
	}
	$payload = $decoded;
} else {
	$tmpJson = $repoRoot . '/benchmarks/.guard_lifestyle_last.json';
	$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	$cmd = escapeshellarg($phpBin)
		. ' ' . escapeshellarg($repoRoot . '/benchmarks/run_benchmarks.php')
		. ' --only=test_files74,test_files75,test_files76 --no-case-timeout --repeat=' . (string) $repeatRuns
		. ' --json --out-json=' . escapeshellarg($tmpJson);
	passthru($cmd, $rc);
	if ((int) $rc !== 0) {
		fwrite(STDERR, "guard_lifestyle: benchmark command failed with exit={$rc}\n");
		exit((int) $rc);
	}
	$jsonPathUsed = $tmpJson;
	if (!is_readable($jsonPathUsed)) {
		fwrite(STDERR, "guard_lifestyle: missing JSON output after benchmark run: {$jsonPathUsed}\n");
		exit(1);
	}
	$decoded = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_lifestyle');
	if ($decoded === null) {
		exit(1);
	}
	$payload = $decoded;
}

$expectedBytes = array(
	'test_files74' => 1307,
	'test_files75' => 1251,
	'test_files76' => 5807,
);
$observed = array();
$caseRows = isset($payload['cases']) && is_array($payload['cases']) ? $payload['cases'] : array();
foreach ($caseRows as $row) {
	if (!is_array($row)) {
		continue;
	}
	$label = isset($row['label']) ? (string) $row['label'] : '';
	if ($label === '') {
		continue;
	}
	$observed[$label] = isset($row['fzc_bytes']) ? (int) $row['fzc_bytes'] : null;
}

$errors = array();
foreach ($expectedBytes as $label => $wantBytes) {
	if (!array_key_exists($label, $observed) || $observed[$label] === null) {
		$errors[] = "{$label}: missing from benchmark payload";
		continue;
	}
	$got = (int) $observed[$label];
	if ($got !== $wantBytes) {
		$errors[] = "{$label}: fzc_bytes={$got}, expected={$wantBytes}";
	}
}

$caseZipSum = 0.0;
$missingZipS = false;
foreach (array('test_files74', 'test_files75', 'test_files76') as $label) {
	if (!isset($caseRows) || !is_array($caseRows)) {
		$missingZipS = true;
		break;
	}
	$found = false;
	foreach ($caseRows as $row) {
		if (!is_array($row) || (string)($row['label'] ?? '') !== $label) {
			continue;
		}
		if (!array_key_exists('zip_seconds', $row)) {
			$missingZipS = true;
			$found = true;
			break;
		}
		$caseZipSum += (float) $row['zip_seconds'];
		$found = true;
		break;
	}
	if (!$found) {
		$missingZipS = true;
	}
}
if ($missingZipS) {
	$errors[] = "missing one or more case zip_seconds values";
} elseif ($caseZipSum > $maxFzcSeconds) {
	$errors[] = "sum(case.zip_seconds)={$caseZipSum} exceeds max={$maxFzcSeconds}";
}

if ($errors !== array()) {
	fwrite(STDERR, "guard_lifestyle: FAIL\n");
	foreach ($errors as $e) {
		fwrite(STDERR, "  - {$e}\n");
	}
	fwrite(STDERR, "  json: {$jsonPathUsed}\n");
	exit(1);
}

fwrite(
	STDOUT,
	"guard_lifestyle: OK bytes(test_files74=1307,test_files75=1251,test_files76=5807) "
	. "and sum(case.zip_seconds)={$caseZipSum} <= {$maxFzcSeconds}"
	. ($fromJson === null ? " (repeat={$repeatRuns})" : "")
	. "\n"
);
exit(0);
