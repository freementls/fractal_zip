#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Guard: under ultra / LIFESTYLE=0, fzc_bytes must meet or beat pinned fractal floors.
 *
 *   php benchmarks/guard_ultra_fractal_floors.php
 *   php benchmarks/guard_ultra_fractal_floors.php --from-json=benchmarks/.guard_ultra_floors_last.json
 *   php benchmarks/guard_ultra_fractal_floors.php --floors=benchmarks/fractal_bytes_floors.json
 *
 * Default run uses --ultra on the corpora_for_guard list (short micros + mid fractal cases).
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$floorsPath = $repoRoot . '/benchmarks/fractal_bytes_floors.json';
$fromJson = null;
$only = null;

foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--floors=', 9) === 0) {
		$floorsPath = trim(substr($a, 9));
		continue;
	}
	if (strncmp($a, '--from-json=', 12) === 0) {
		$fromJson = trim(substr($a, 12));
		continue;
	}
	if (strncmp($a, '--only=', 7) === 0) {
		$only = trim(substr($a, 7));
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php guard_ultra_fractal_floors.php [--floors=PATH] [--from-json=PATH] [--only=a,b]\n";
		exit(0);
	}
}

if (!is_readable($floorsPath)) {
	fwrite(STDERR, "guard_ultra_fractal_floors: missing floors file: {$floorsPath}\n");
	exit(2);
}
$floorsDoc = bench_json_decode_file_assoc_try($floorsPath, 'guard_ultra_fractal_floors floors');
if ($floorsDoc === null) {
	exit(2);
}
$floors = isset($floorsDoc['floors']) && is_array($floorsDoc['floors']) ? $floorsDoc['floors'] : [];
$corpora = isset($floorsDoc['corpora_for_guard']) && is_array($floorsDoc['corpora_for_guard'])
	? $floorsDoc['corpora_for_guard']
	: array_keys($floors);
if ($only !== null && $only !== '') {
	$corpora = [];
	foreach (explode(',', $only) as $p) {
		$p = trim($p);
		if ($p === '') {
			continue;
		}
		if (preg_match('/^\d+$/', $p)) {
			$p = 'test_files' . $p;
		}
		$corpora[] = $p;
	}
}

$payload = null;
$jsonPathUsed = '';
if ($fromJson !== null && $fromJson !== '') {
	$jsonPathUsed = $fromJson;
	if (!is_readable($jsonPathUsed)) {
		fwrite(STDERR, "guard_ultra_fractal_floors: cannot read --from-json={$jsonPathUsed}\n");
		exit(1);
	}
	$payload = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_ultra_fractal_floors');
	if ($payload === null) {
		exit(1);
	}
} else {
	$tmpJson = $repoRoot . '/benchmarks/.guard_ultra_floors_last.json';
	$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	$onlyArg = implode(',', $corpora);
	$cmd = 'env FRACTAL_ZIP_ULTRA=1 FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0 FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1 '
		. escapeshellarg($phpBin) . ' -d opcache.enable_cli=0 '
		. escapeshellarg($repoRoot . '/benchmarks/run_benchmarks.php')
		. ' --ultra --only=' . escapeshellarg($onlyArg)
		. ' --case-timeout=90 --no-verify --out-json=' . escapeshellarg($tmpJson);
	fwrite(STDERR, "guard_ultra_fractal_floors: running {$onlyArg}\n");
	passthru($cmd, $rc);
	if ((int) $rc !== 0) {
		fwrite(STDERR, "guard_ultra_fractal_floors: benchmark failed exit={$rc}\n");
		exit((int) $rc);
	}
	$jsonPathUsed = $tmpJson;
	$payload = bench_json_decode_file_assoc_try($jsonPathUsed, 'guard_ultra_fractal_floors');
	if ($payload === null) {
		exit(1);
	}
}

$byLabel = [];
foreach (($payload['cases'] ?? []) as $row) {
	if (!is_array($row)) {
		continue;
	}
	$lab = isset($row['label']) ? (string) $row['label'] : '';
	if ($lab !== '') {
		$byLabel[$lab] = $row;
	}
}

$fail = [];
$okN = 0;
foreach ($corpora as $lab) {
	$lab = (string) $lab;
	if (!isset($floors[$lab])) {
		fwrite(STDERR, "guard_ultra_fractal_floors: no floor for {$lab} (skip)\n");
		continue;
	}
	$floor = (int) $floors[$lab];
	if (!isset($byLabel[$lab])) {
		$fail[] = "{$lab}: missing case in JSON";
		continue;
	}
	$fzc = (int) ($byLabel[$lab]['fzc_bytes'] ?? 0);
	if ($fzc <= 0) {
		$fail[] = "{$lab}: fzc_bytes missing";
		continue;
	}
	if ($fzc > $floor) {
		$fail[] = "{$lab}: fzc={$fzc} > floor={$floor} (over by " . ($fzc - $floor) . ')';
		continue;
	}
	$okN++;
	fwrite(STDERR, "ok {$lab}: fzc={$fzc} <= floor={$floor}\n");
}

if ($fail !== []) {
	fwrite(STDERR, "guard_ultra_fractal_floors: FAIL\n");
	foreach ($fail as $line) {
		fwrite(STDERR, "  {$line}\n");
	}
	exit(1);
}

fwrite(STDERR, "guard_ultra_fractal_floors: OK {$okN} corpora (json={$jsonPathUsed})\n");
exit(0);
