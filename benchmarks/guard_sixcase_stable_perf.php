#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Stable perf guard for the six small “proxy” corpora: exact .fz bytes and a ceiling on
 * total fzc compress time (same as the benchmark table TOTAL `fzc s`: sum of per-case
 * `zip_seconds`, each already a median when `--repeat` > 1).
 *
 * Defaults:
 *   cases: test_files, test_files2, test_files4, test_files74, test_files75, test_files76
 *   expected fzc bytes: 77, 122, 103, 1307, 1251, 5807
 *   repeat_runs: 7 (median per case inside run_benchmarks.php)
 *   max totals.zip_seconds: 35 (six-case sum; tune --max-sum-zip-seconds for perf-only gates)
 *   Applies “known good” lifestyle tuning env unless --no-lifestyle-known-good (inherited by child PHP).
 *
 * Optional CPU affinity (Linux): --taskset=0-3 or --taskset=0,2  (requires `taskset` in PATH).
 *
 * Usage (repo root):
 *   php benchmarks/guard_sixcase_stable_perf.php
 *   php benchmarks/guard_sixcase_stable_perf.php --repeat=11 --max-sum-zip-seconds=35 --taskset=0-3
 *   php benchmarks/guard_sixcase_stable_perf.php --from-json=benchmarks/.last_bench.json --max-sum-zip-seconds=35
 *
 * Tiered **`--bench-profile`** / **`--jobs`**: **benchmarks/LARGE_CORPUS_SPEED.md**. Ephemeral **`benchmarks/.last_bench.json`** is gitignored when generated in-repo; use **`php benchmarks/run_benchmarks.php --out-json=…`** to keep a file for **`--from-json`**.
 *
 * Anything after a lone `--` is forwarded to run_benchmarks.php (e.g. `--no-stray-sweep`); not used with --from-json.
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$defaultCases = [
	'test_files' => 77,
	'test_files2' => 122,
	'test_files4' => 103,
	'test_files74' => 1307,
	'test_files75' => 1251,
	'test_files76' => 5807,
];

$repeatRuns = 7;
$maxSumZipSeconds = 35.0;
$fromJson = null;
$tasksetCpus = null;
$lifestyleKnownGood = true;
$forwardArgv = [];

$args = array_slice($argv, 1);
$sep = array_search('--', $args, true);
if ($sep !== false) {
	$forwardArgv = array_slice($args, $sep + 1);
	$args = array_slice($args, 0, $sep);
}

foreach ($args as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--repeat=', 9) === 0) {
		$v = trim(substr($a, 9));
		if ($v !== '' && ctype_digit($v)) {
			$repeatRuns = max(1, min(25, (int) $v));
		}
	} elseif (strncmp($a, '--max-sum-zip-seconds=', 22) === 0) {
		$v = trim(substr($a, 22));
		if ($v !== '' && is_numeric($v)) {
			$maxSumZipSeconds = max(0.0, (float) $v);
		}
	} elseif (strncmp($a, '--from-json=', 12) === 0) {
		$p = trim(substr($a, 12));
		if ($p !== '') {
			$fromJson = $p;
		}
	} elseif (strncmp($a, '--taskset=', 10) === 0) {
		$p = trim(substr($a, 10));
		if ($p !== '') {
			$tasksetCpus = $p;
		}
	} elseif ($a === '--no-lifestyle-known-good') {
		$lifestyleKnownGood = false;
	} elseif ($a === '--lifestyle-known-good') {
		$lifestyleKnownGood = true;
	}
}

if ($lifestyleKnownGood) {
	putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=1');
	putenv('FRACTAL_ZIP_SEGMENT_LENGTH=1000');
	putenv('FRACTAL_ZIP_LIFESTYLE_FZBM_PATH_ORDER_PREFILTER_TOP_K=6');
	putenv('FRACTAL_ZIP_LIFESTYLE_FZB4_PATH_ORDER_PREFILTER_TOP_K=6');
	putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_TINY_SKIP_MAX_RAW_BYTES=1024');
}

$payload = null;
$jsonPathUsed = '';

if ($fromJson !== null) {
	$jsonPathUsed = $fromJson;
	if (!is_readable($jsonPathUsed)) {
		fwrite(STDERR, "guard_sixcase_stable_perf: cannot read JSON: {$jsonPathUsed}\n");
		exit(1);
	}
	$decoded = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_sixcase_stable_perf');
	if ($decoded === null) {
		exit(1);
	}
	$payload = $decoded;
} else {
	$tmpJson = $repoRoot . '/benchmarks/.guard_sixcase_stable_perf_last.json';
	$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	$onlyList = implode(',', array_keys($defaultCases));
	$cmdParts = [];
	if ($tasksetCpus !== null && $tasksetCpus !== '') {
		$ts = trim((string) shell_exec('command -v taskset 2>/dev/null'));
		if ($ts !== '') {
			$cmdParts[] = escapeshellarg($ts) . ' -c ' . escapeshellarg($tasksetCpus);
		} else {
			fwrite(STDERR, "guard_sixcase_stable_perf: warning: --taskset given but taskset not found in PATH; ignoring.\n");
		}
	}
	$cmdParts[] = escapeshellarg($phpBin);
	$cmdParts[] = escapeshellarg($repoRoot . '/benchmarks/run_benchmarks.php');
	$cmdParts[] = '--only=' . escapeshellarg($onlyList);
	$cmdParts[] = '--no-case-timeout';
	$cmdParts[] = '--repeat=' . (string) $repeatRuns;
	$cmdParts[] = '--out-json=' . escapeshellarg($tmpJson);
	foreach ($forwardArgv as $fa) {
		if (is_string($fa) && $fa !== '') {
			$cmdParts[] = escapeshellarg($fa);
		}
	}
	$cmd = implode(' ', $cmdParts);
	// Omit `--json` so the driver does not dump the full JSON blob to stdout; we read `--out-json` only.
	if (PHP_OS_FAMILY !== 'Windows') {
		$cmd .= ' >/dev/null';
	}
	passthru($cmd, $rc);
	if ((int) $rc !== 0) {
		fwrite(STDERR, "guard_sixcase_stable_perf: benchmark failed exit={$rc}\n");
		exit((int) $rc);
	}
	$jsonPathUsed = $tmpJson;
	if (!is_readable($jsonPathUsed)) {
		fwrite(STDERR, "guard_sixcase_stable_perf: missing JSON after run: {$jsonPathUsed}\n");
		exit(1);
	}
	$decoded = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_sixcase_stable_perf');
	if ($decoded === null) {
		exit(1);
	}
	$payload = $decoded;
}

$errors = [];
$caseRows = isset($payload['cases']) && is_array($payload['cases']) ? $payload['cases'] : [];
$byLabel = [];
foreach ($caseRows as $row) {
	if (!is_array($row)) {
		continue;
	}
	$lab = isset($row['label']) ? (string) $row['label'] : '';
	if ($lab !== '') {
		$byLabel[$lab] = $row;
	}
}

foreach ($defaultCases as $label => $wantBytes) {
	if (!isset($byLabel[$label])) {
		$errors[] = "{$label}: missing from benchmark payload";
		continue;
	}
	$row = $byLabel[$label];
	if (!array_key_exists('fzc_bytes', $row)) {
		$errors[] = "{$label}: missing fzc_bytes";
		continue;
	}
	$got = (int) $row['fzc_bytes'];
	if ($got !== $wantBytes) {
		$errors[] = "{$label}: fzc_bytes={$got}, expected={$wantBytes}";
	}
}

$totals = isset($payload['totals']) && is_array($payload['totals']) ? $payload['totals'] : null;
$sumZip = null;
if ($totals !== null && array_key_exists('zip_seconds', $totals)) {
	$sumZip = (float) $totals['zip_seconds'];
}
if ($sumZip === null) {
	$errors[] = 'totals.zip_seconds missing';
} elseif ($sumZip > $maxSumZipSeconds) {
	$errors[] = 'sum(totals.zip_seconds)=' . (string) $sumZip . " exceeds max={$maxSumZipSeconds}";
}

$repeatReported = isset($payload['repeat_runs']) ? (int) $payload['repeat_runs'] : null;
if ($fromJson === null && $repeatReported !== null && $repeatReported !== $repeatRuns) {
	$errors[] = "repeat_runs mismatch: json={$repeatReported}, requested={$repeatRuns}";
}

if ($errors !== []) {
	fwrite(STDERR, "guard_sixcase_stable_perf: FAIL\n");
	foreach ($errors as $e) {
		fwrite(STDERR, "  - {$e}\n");
	}
	fwrite(STDERR, "  json: {$jsonPathUsed}\n");
	exit(1);
}

$extra = '';
if ($fromJson === null) {
	$extra = " repeat={$repeatRuns}";
	if ($tasksetCpus !== null && $tasksetCpus !== '') {
		$extra .= ' taskset=' . $tasksetCpus;
	}
	if ($lifestyleKnownGood) {
		$extra .= ' lifestyle_known_good=1';
	}
}
fwrite(
	STDOUT,
	'guard_sixcase_stable_perf: OK bytes (six cases) and sum(zip_seconds)='
		. (string) $sumZip
		. " <= {$maxSumZipSeconds}{$extra}\n"
		. "  json: {$jsonPathUsed}\n"
);
exit(0);
