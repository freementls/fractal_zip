#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Sweep fractal segment lengths on test_files29 (or another folder) with multipass on and outer trials biased to FreeArc.
 *
 * Important: outer-codec getenv switches are cached inside fractal_zip on first use. This script sets all bench env
 * vars **before** loading fractal_zip.php so SKIP_* / ARC_METHOD apply consistently for every trial in one process.
 *
 * Defaults target the legacy per-member fractal zip path (unified stream off), Arc method 1 (single -m1-style bench
 * compatibility per outer_arc_blob doc), Force Arc on huge payloads, and disable auto segment/multipass tuning.
 * adaptive_compress still evaluates gzip/deflate first; zstd/brotli/7z/xz/zpaq are skipped so Arc can win when it
 * beats gzip. Check column last_outer_codec — if it stays gzip, Arc never beat the zlib baseline for that segment.
 *
 * Usage (repo root):
 *   php benchmarks/sweep_segment_test_files29_freearc.php
 *   php benchmarks/sweep_segment_test_files29_freearc.php --dir=test_files29 --from=8 --to=800 --step=4
 *   php benchmarks/sweep_segment_test_files29_freearc.php --max-extra-passes=unlimited   # full multipass until no gain (can be very slow)
 *   php benchmarks/sweep_segment_test_files29_freearc.php --max-extra-passes=40
 *   php benchmarks/sweep_segment_test_files29_freearc.php --time-budget-ms=120000    # optional cooperative zip_folder cap
 *   php benchmarks/sweep_segment_test_files29_freearc.php --json
 *
 * Requires `arc` on PATH (same as normal fz FreeArc outer).
 *
 * Smoke / sanity (fast): add --single-pass to disable multipass (not what you want for final numbers).
 * Quick exploratory runs (different compression profile): --max-fractal-bytes=262144 caps heavy substring work per member
 * (see README FRACTAL_ZIP_MAX_FRACTAL_BYTES); omit for full corpus behavior.
 * Faster smoke on huge members: --max-rec-depth=8 --max-rec-seconds=5 (not comparable to README-class scores).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

// ---------- CLI (must run before fractal_zip.php is loaded) ----------
$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$corpusDir = $repoRoot . DIRECTORY_SEPARATOR . 'test_files29';
$fromSeg = 8;
$toSeg = 600;
$stepSeg = 4;
$jsonOut = false;
// Default ≥30 extra fractal passes; use --max-extra-passes=unlimited for unconstrained multipass (very slow on large members).
$maxExtraPasses = 30;
$arcMethod = 1;
$dryRun = false;
$forceLarge = false;
$timeBudgetMs = null;
$multipassOn = true;
$maxFractalBytes = null; // optional FRACTAL_ZIP_MAX_FRACTAL_BYTES (0 = unlimited per fz semantics)
$maxRecDepth = 30;
$maxRecSeconds = 120;
$multipassWallSeconds = 0; // 0 = unlimited wall per README-style bench

for ($i = 1; $i < $argc; $i++) {
	$a = $argv[$i];
	if ($a === '--json') {
		$jsonOut = true;
	} elseif ($a === '--dry-run') {
		$dryRun = true;
	} elseif ($a === '--force-large-sweep') {
		$forceLarge = true;
	} elseif ($a === '--single-pass') {
		$multipassOn = false;
	} elseif (preg_match('/^--dir=(.+)$/', $a, $m)) {
		$p = $m[1];
		$corpusDir = ($p[0] === '/' || (strlen($p) > 1 && $p[1] === ':')) ? $p : $repoRoot . DIRECTORY_SEPARATOR . $p;
	} elseif (preg_match('/^--from=(\d+)$/', $a, $m)) {
		$fromSeg = max(8, (int) $m[1]);
	} elseif (preg_match('/^--to=(\d+)$/', $a, $m)) {
		$toSeg = max(8, (int) $m[1]);
	} elseif (preg_match('/^--step=(\d+)$/', $a, $m)) {
		$stepSeg = max(1, (int) $m[1]);
	} elseif (preg_match('/^--arc-method=(\d+)$/', $a, $m)) {
		$arcMethod = max(1, min(9, (int) $m[1]));
	} elseif (preg_match('/^--max-extra-passes=(.+)$/', $a, $m)) {
		$v = strtolower(trim($m[1]));
		if ($v === 'unlimited' || $v === '-1' || $v === 'inf') {
			$maxExtraPasses = null;
		} else {
			$maxExtraPasses = max(30, (int) $m[1]);
		}
	} elseif (preg_match('/^--time-budget-ms=(\d+)$/', $a, $m)) {
		$timeBudgetMs = (int) $m[1];
	} elseif (preg_match('/^--max-fractal-bytes=(\d+)$/', $a, $m)) {
		$maxFractalBytes = max(0, (int) $m[1]);
	} elseif (preg_match('/^--max-rec-depth=(\d+)$/', $a, $m)) {
		$maxRecDepth = max(1, (int) $m[1]);
	} elseif (preg_match('/^--max-rec-seconds=(\d+)$/', $a, $m)) {
		$maxRecSeconds = max(0, (int) $m[1]);
	} elseif (preg_match('/^--multipass-wall-seconds=(\d+)$/', $a, $m)) {
		$multipassWallSeconds = max(0, (int) $m[1]);
	} else {
		fwrite(STDERR, "Unknown argument: {$a}\n");
		fwrite(STDERR, "Usage: php benchmarks/sweep_segment_test_files29_freearc.php [--dir=PATH] [--from=N] [--to=N] [--step=N] [--arc-method=1..9] [--max-extra-passes=N|unlimited] [--max-fractal-bytes=N] [--max-rec-depth=N] [--max-rec-seconds=N] [--multipass-wall-seconds=N] [--time-budget-ms=N] [--single-pass] [--json] [--dry-run] [--force-large-sweep]\n");
		exit(2);
	}
}

if ($toSeg < $fromSeg) {
	fwrite(STDERR, "--to must be >= --from\n");
	exit(2);
}

/** @var list<int> $segments */
$segments = [];
for ($s = $fromSeg; $s <= $toSeg; $s += $stepSeg) {
	$segments[] = max(8, min(500000, $s));
}
$segments = array_values(array_unique($segments));
sort($segments, SORT_NUMERIC);

$trialCount = count($segments);
if ($trialCount > 2500 && !$forceLarge) {
	fwrite(STDERR, "Refusing {$trialCount} trials (cap 2500). Narrow --from/--to/--step or pass --force-large-sweep.\n");
	exit(2);
}

$benchEnv = array(
	'FRACTAL_ZIP_SUPPRESS_HTML' => '1',
	// Legacy fractal zip_folder (not unified-stream inner builder).
	'FRACTAL_ZIP_FOLDER_UNIFIED_STREAM' => '0',
	// No auto segment / multipass / tune trials inside zip_folder.
	'FRACTAL_ZIP_AUTO_TUNE' => '0',
	'FRACTAL_ZIP_AUTO_SEGMENT' => '0',
	'FRACTAL_ZIP_AUTO_MULTIPASS' => '0',
	// Bytes-first: do not use SPEED profile (which can skip Arc on huge non-FZB).
	'FRACTAL_ZIP_SPEED' => '0',
	'FRACTAL_ZIP_FORCE_ARC' => '1',
	// Single Arc method (historical “mode 1” bench knob).
	'FRACTAL_ZIP_ARC_METHOD' => (string) $arcMethod,
	// Drop faster “slow outer” rivals so Arc is compared mainly against gzip/deflate baseline.
	'FRACTAL_ZIP_SKIP_ZSTD' => '1',
	'FRACTAL_ZIP_SKIP_BROTLI' => '1',
	'FRACTAL_ZIP_SKIP_7Z' => '1',
	'FRACTAL_ZIP_SKIP_XZ' => '1',
	'FRACTAL_ZIP_SKIP_ZPAQ' => '1',
	// README test_files29 note: ~30 fractal recursion levels; defaults (depth 12, 10s) severely under-search this corpus.
	'FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_DEPTH' => (string) $maxRecDepth,
	'FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_SECONDS' => (string) $maxRecSeconds,
	// Unlimited multipass equivalence wall while passes improve (without enabling full FRACTAL_ZIP_ULTRA outer extras).
	'FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS' => (string) $multipassWallSeconds,
);

if ($timeBudgetMs !== null && $timeBudgetMs > 0) {
	$benchEnv['FRACTAL_ZIP_TIME_BUDGET_MS'] = (string) $timeBudgetMs;
	// zip_folder disables multipass under a time budget unless this is set (see fractal_zip.php zip_folder).
	$benchEnv['FRACTAL_ZIP_FORCE_MULTIPASS'] = '1';
}
if ($maxFractalBytes !== null) {
	$benchEnv['FRACTAL_ZIP_MAX_FRACTAL_BYTES'] = (string) $maxFractalBytes;
}

foreach ($benchEnv as $k => $v) {
	putenv($k . '=' . $v);
}

require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$corpusReal = realpath($corpusDir);
if ($corpusReal === false || !is_dir($corpusReal)) {
	fwrite(STDERR, "Corpus directory not found: {$corpusDir}\n");
	exit(1);
}

$arcExe = fractal_zip::freearc_executable();
if ($arcExe === null) {
	fwrite(STDERR, "FreeArc executable not found (install `arc` or set FRACTAL_ZIP_FREEARC).\n");
	exit(1);
}

$proto = new fractal_zip(8, true, false, null, false);
$rawBytes = $proto->folder_raw_total_bytes($corpusReal);

if ($dryRun) {
	print("corpus={$corpusReal} raw_bytes={$rawBytes} trials={$trialCount} arc=" . $arcExe . " max_extra_passes=" . ($maxExtraPasses === null ? 'unlimited' : (string) $maxExtraPasses) . "\n");
	print('segments=' . implode(',', array_slice($segments, 0, 20)) . (count($segments) > 20 ? ',...' : '') . "\n");
	exit(0);
}

$overrideBase = array(
	'multipass_max_additional_passes' => $maxExtraPasses,
);

/** @var list<array{segment:int,fzc_bytes:int,seconds:float,last_outer:?string}> $rows */
$rows = array();

$trialIdx = 0;
$trialTotal = count($segments);
foreach ($segments as $segment) {
	++$trialIdx;
	fwrite(STDERR, "[{$trialIdx}/{$trialTotal}] segment={$segment} zip_folder...\n");
	fflush(STDERR);
	$trialOverrides = $overrideBase;
	$tag = 'fzseg29_' . substr(md5($corpusReal . "\0" . (string) $segment . "\0" . (string) microtime(true)), 0, 12) . '_' . $segment;
	$tmpDir = $proto->program_path . DIRECTORY_SEPARATOR . $tag;
	$tmpFzc = $tmpDir . $proto->fractal_zip_container_file_extension;

	$proto->recursive_copy_directory($corpusReal, $tmpDir);

	$trial = new fractal_zip($segment, $multipassOn, false, $trialOverrides, false);

	fractal_zip::$last_outer_codec = null;

	$t0 = microtime(true);
	ob_start();
	$trial->zip_folder($tmpDir, false);
	ob_end_clean();
	$dt = microtime(true) - $t0;

	$size = is_file($tmpFzc) ? (int) filesize($tmpFzc) : PHP_INT_MAX;
	$outer = fractal_zip::$last_outer_codec;

	if (is_file($tmpFzc)) {
		unlink($tmpFzc);
	}
	$proto->recursive_remove_directory($tmpDir);

	$rows[] = array(
		'segment' => $segment,
		'fzc_bytes' => $size,
		'seconds' => $dt,
		'last_outer' => $outer,
	);
	fwrite(STDERR, "[{$trialIdx}/{$trialTotal}] segment={$segment} done fzc_bytes={$size} seconds=" . sprintf('%.4f', $dt) . ' outer=' . ($outer ?? '') . "\n");
	fflush(STDERR);
}

if ($jsonOut) {
	$payload = array(
		'corpus' => $corpusReal,
		'raw_bytes' => $rawBytes,
		'arc_exe' => $arcExe,
		'arc_method_env' => $arcMethod,
		'multipass_max_additional_passes' => $maxExtraPasses,
		'multipass_enabled' => $multipassOn,
		'max_fractal_bytes_env' => $maxFractalBytes,
		'max_recursive_fractal_depth' => $maxRecDepth,
		'max_recursive_fractal_seconds' => $maxRecSeconds,
		'max_fractal_multipass_wall_seconds' => $multipassWallSeconds,
		'time_budget_ms' => $timeBudgetMs,
		'rows' => $rows,
	);
	$js = bench_json_encode_try($payload, true);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (sweep_segment_test_files29_freearc --json): ' . json_last_error_msg() . "\n");
		exit(2);
	}
	echo $js . "\n";
	exit(0);
}

print(str_pad('segment', 10) . str_pad('fzc_bytes', 12) . str_pad('seconds', 12) . "last_outer_codec\n");
foreach ($rows as $r) {
	print(str_pad((string) $r['segment'], 10)
		. str_pad((string) $r['fzc_bytes'], 12)
		. str_pad(sprintf('%.4f', $r['seconds']), 12)
		. ($r['last_outer'] ?? '') . "\n");
}

$best = null;
foreach ($rows as $r) {
	if ($best === null || $r['fzc_bytes'] < $best['fzc_bytes']) {
		$best = $r;
	}
}
if ($best !== null) {
	print("\nbest_segment=" . $best['segment'] . ' best_fzc_bytes=' . $best['fzc_bytes'] . ' best_seconds=' . sprintf('%.4f', $best['seconds']) . ' last_outer=' . ($best['last_outer'] ?? '') . "\n");
}
