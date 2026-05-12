#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cli_opcache_bootstrap.php';

/**
 * Wall-clock + optional XHProf/Tideways function-level profile over the same default test_files* set as
 * benchmarks/run_benchmarks.php (or fast/wide presets), with the same bytes-first literal defaults as the bench driver.
 *
 * Default: skip baselines, gzip/7z/min-ext, extract, and verify so almost all time is zip_folder.
 * Preset **default** matches `php benchmarks/run_benchmarks.php` discovery. Without `--maximum-size`, expect ~32 materialized
 * `test_files*` (skips huge 54–59 and Squash mirrors 105–132 by default). **With `--maximum-size=2M`**, the same eligibility rules
 * and on-disk size filter as the bench (see `bench_corpus_size.php`); e.g. all listed dirs under 2_000_000 B raw (often 33 cases) when
 * the working tree matches your `run_benchmarks` run.
 * By default each zip_folder gets a cooperative **FRACTAL_ZIP_TIME_BUDGET_MS** from a **per-case** slice of the
 * global `--budget-sec` (at most `budget / number of listed corpora` seconds per case, and at most
 * `remaining / pending` so work spreads across types). With the full default list, allow a **larger** `--budget-sec` than 300s
 * if you want meaningful depth per case. Use **--no-zip-time-budget** for full-depth encodes (one corpus may dominate wall time again).
 * **Work dir:** default **--work-mode=symlink** links each corpus into `benchmarks/.work` (no full tree copy before `zip_folder`); **--work-mode=copy** matches run_benchmarks (full clone, slower).
 * **Prewalk:** raw byte totals, segment length, and heavy-folder gzip/61/62 settings are computed once; J-curve scale is applied once to the process env and restored on shutdown; per-label `src`/`work` paths and `.fzc` paths are pre-built; `perf_zip_one_case` receives the pre-summed `raw` and flags (no second `du`/walk and no per-case heavy-gzip or label `isset` on the hot path). Non-heavy corpora use folder-gzip <code>-1</code> so the zip path skips save/restore for that env. Per-repeat slice math reuses a hoisted <code>casesN</code>. The main loop uses a single `microtime` per iteration for the budget slice. The HTML-trace / buffer flag is decided once. In typical CLI, output buffering around `zip_folder` is skipped. With XHProf, one **enable** covers all corpora on pass 0 (single call graph) instead of per-corpus enable/disable+merge. When a corpus is present at prewalk, the main loop skips a redundant `is_dir` on each repeat.
 * With `--no-xhprof`, the Linux stack sampler is off by default (lower wall); pass `--stack-hz=N` to sample without xhprof.
 * **`--jobs=N` (Linux + pcntl):** run up to N corpora at once; `zip_folder_sum` remains Σ `zip_s`; `wall_clock_total` drops under parallelism; profiling (XHProf) is disabled when N>1. The fork pool **schedules largest raw-byte corpora first** (better overlap vs FIFO discovery order); JSON/report row order still follows the preset list.
 *
 * Usage (repo root):
 *   php perf_test.php                       # preset=default: same case list as run_benchmarks; default --budget-sec=300; FreeArc off unless shell overrides
 *   php perf_test.php --maximum-size=2M     # same 2M-bench set as `php benchmarks/run_benchmarks.php --maximum-size=2M` (when the same `test_files*` are on disk)
 *   php perf_test.php --preset=fast         # five corpora only (no test_files54_sample) — quick iteration, not the full perf mix
 *   php perf_test.php --preset=wide --budget-sec=600   # more types (FLAC, HTML slice, MPQ, …); raise budget
 *   php perf_test.php --budget-sec=120 --only=test_files69,test_files35
 *   php perf_test.php --verify              # round-trip SHA1 check (slower)
 *   php perf_test.php --bench-keep-shell-literal-env   # inherit shell FRACTAL_ZIP_LITERAL_* (not bytes-first bench defaults)
 *   php perf_test.php --repeat=11 --only=test_files54_sample --no-zip-time-budget   # median/p90 wall + zip_s (noise control)
 *   php perf_test.php --quick --no-stack-sample                                   # shortest wall time: fast preset + speed + prewarm
 *   php perf_test.php --preset=fast --speed --no-stack-sample                      # same speed trade without --quick
 *   php perf_test.php --profile-json=benchmarks/.work/last_profile.json ...        # wall stats + rollups + per-function table JSON
 *   php perf_test.php --profile-csv=benchmarks/.work/last_profile.csv ...           # same breakdown + rollups as CSV
 *   php perf_test.php --xhprof-sort=inclusive ...                                   # main table by subtree ms (coord-style)
 *   JSON adds benchmark_config, corpora[], php_runtime, xhprof_top_edges; stderr ends with run summary (git, OPcache, profiler).
 *   php perf_test.php --xhprof-raw=benchmarks/.work/xhprof.json ...               # full merged XHProf graph for ui.solarium.dev / xhprof.io
 *
 * Function-level hotspots:
 *   Preferred: XHProf or Tideways XHProf (inclusive wall time + call counts).
 *   Fallback (Linux, pcntl+posix): stack sampling (~--stack-hz) when no profiler extension.
 *
 *   pecl install xhprof
 *   php -d extension={extension_dir}/xhprof.so perf_test.php   # see `php -i | grep extension_dir`
 *   # Arch/Manjaro: sudo pacman -S php-xhprof   (path is usually extension_dir/xhprof.so)
 */

$repoRoot = realpath(__DIR__) ?: __DIR__;
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_default_corpus_list.php';
$argvList = array_slice($argv, 1);

$budgetSec = 300.0;
$onlyFilter = null;
$preset = 'default';
$doVerify = false;
$keepShellLiteralEnv = in_array('--bench-keep-shell-literal-env', $argvList, true);
$legacyFolderZip = in_array('--legacy-folder-zip', $argvList, true);
$includeLarge = in_array('--large', $argvList, true);
$speedMode = in_array('--speed', $argvList, true);
$quickMode = in_array('--quick', $argvList, true);
$presetExplicit = false;
$noXhprof = in_array('--no-xhprof', $argvList, true);
/** With --quick, still attach XHProf/Tideways (otherwise --quick skips the profiler for lower wall noise). */
$xhprofEvenOnQuick = in_array('--xhprof', $argvList, true);
$noStackSample = in_array('--no-stack-sample', $argvList, true);
$stackHz = 100.0;
$noZipTimeBudget = in_array('--no-zip-time-budget', $argvList, true);
/** @var 'symlink'|'copy' $workMode Prepare benchmarks/.work/case_*: symlink to corpus (default, fastest) or full copy (like run_benchmarks). */
$workMode = 'symlink';
$repeatCount = 1;
$profileJsonPath = null;
$profileCsvPath = null;
$xhprofRawPath = null;
$xhprofBreakdownRows = 55;
$xhprofEdgeRows = 15;
$xhprofSort = 'exclusive';
/** @var string|null raw CLI token for `benchParseMaximumSizeBytes` */
$maxRawBytesArg = null;
/** @var int|null on-disk cap (same as run_benchmarks --maximum-size) */
$maxRawBytes = null;
/** True when argv included --stack-hz= (keeps the Linux stack sampler on together with --no-xhprof). */
$stackHzExplicit = false;
/** @var int Max concurrent zip_folder workers (1 = sequential). Requires pcntl_fork (not Windows). */
$parallelJobs = 1;

foreach ($argvList as $a) {
	if (preg_match('/^--budget-sec=([\d.]+)$/', $a, $m)) {
		$budgetSec = max(5.0, (float) $m[1]);
	} elseif (preg_match('/^--only=([^,]+(?:,[^,]+)*)$/', $a, $m)) {
		$onlyFilter = array_values(array_filter(array_map('trim', explode(',', $m[1])), static fn ($s) => $s !== ''));
	} elseif (preg_match('/^--preset=(\w+)$/', $a, $m)) {
		$preset = $m[1];
		$presetExplicit = true;
	} elseif (preg_match('/^--stack-hz=([\d.]+)$/', $a, $m)) {
		$stackHz = max(5.0, min(500.0, (float) $m[1]));
		$stackHzExplicit = true;
	} elseif (preg_match('/^--repeat=(\d+)$/', $a, $m)) {
		$repeatCount = max(1, min(500, (int) $m[1]));
	} elseif (preg_match('/^--profile-json=(.+)$/', $a, $m)) {
		$profileJsonPath = $m[1];
	} elseif (preg_match('/^--profile-csv=(.+)$/', $a, $m)) {
		$profileCsvPath = $m[1];
	} elseif (preg_match('/^--xhprof-raw=(.+)$/', $a, $m)) {
		$xhprofRawPath = $m[1];
	} elseif (preg_match('/^--xhprof-rows=(\d+)$/', $a, $m)) {
		$xhprofBreakdownRows = max(5, min(400, (int) $m[1]));
	} elseif (preg_match('/^--xhprof-sort=(exclusive|inclusive)$/i', $a, $m)) {
		$xhprofSort = strtolower($m[1]);
	} elseif (preg_match('/^--xhprof-edge-rows=(\d+)$/', $a, $m)) {
		$xhprofEdgeRows = max(5, min(80, (int) $m[1]));
	} elseif (preg_match('/^--work-mode=(symlink|copy)$/', $a, $m)) {
		$workMode = $m[1];
	} elseif (strncmp($a, '--maximum-size=', 15) === 0) {
		$maxRawBytesArg = trim(substr($a, 15));
	} elseif (preg_match('/^--jobs=(\d+)$/', $a, $m)) {
		$parallelJobs = max(1, min(32, (int) $m[1]));
	}
}
if ($maxRawBytesArg !== null && $maxRawBytesArg !== '') {
	try {
		$maxRawBytes = benchParseMaximumSizeBytes($maxRawBytesArg);
	} catch (InvalidArgumentException $e) {
		fwrite(
			STDERR,
			"perf_test: invalid --maximum-size=…: {$maxRawBytesArg} ({$e->getMessage()}) — use bytes, 2M, 10MiB, same as run_benchmarks\n"
		);
		exit(2);
	}
}
if ($quickMode) {
	$speedMode = true;
	if (!$presetExplicit && $onlyFilter === null) {
		$preset = 'fast';
	}
	// XHProf/Tideways on pass 0 dominates wall on small presets; default --quick measures zip_folder without it.
	if (!$xhprofEvenOnQuick && $profileJsonPath === null && $profileCsvPath === null && $xhprofRawPath === null) {
		$noXhprof = true;
		if (is_resource(STDERR)) {
			fwrite(STDERR, "perf_test: --quick → --no-xhprof (wall-focused); pass --xhprof or --profile-json=… for function-level data\n");
		}
	}
}
if (in_array('--verify', $argvList, true)) {
	$doVerify = true;
}
if (in_array('--help', $argvList, true) || in_array('-h', $argvList, true)) {
	echo <<<TXT
fractal_zip perf_test — wall timings + optional XHProf/Tideways function ranks (same bytes-first env as benchmarks).

Options:
  --preset=default   same `test_files*` list as `run_benchmarks.php` (use `--maximum-size=…` for the size-filtered 2M-style run); default FRACTAL_ZIP_SKIP_ARC=1; default --budget-sec=300
  --preset=fast      five corpora (replaces 70 with test_files10; no 54_sample) for faster local iteration only
  --preset=wide      extra corpora (FLAC, stratified HTML, BMP, SC2, …); use a large --budget-sec
  --speed              FRACTAL_ZIP_SPEED=1 + prewarm literal PAC stack (much lower wall time; .fzc may be larger than bytes-first defaults)
  --quick              same as --speed, and preset=fast when you did not pass --preset or --only (fastest routine perf loop; implies --no-xhprof unless --xhprof or a --profile-* / --xhprof-raw path is set)
  --xhprof             with --quick, still attach XHProf/Tideways (adds overhead; use for function-level tables)
  --only=a,b         override preset
  --maximum-size=2M|N  same on-disk raw-byte cap as `run_benchmarks.php` (e.g. 2M=2_000_000) — with default discovery, makes Squash 105… and huge 54… eligible, then keeps only test_files* that fit; matches `php benchmarks/run_benchmarks.php --maximum-size=…`
  --budget-sec=N     stop before starting another corpus after N seconds (default 300)
  --verify           SHA1 tree compare (+ SC2 semantic when python3 + tools/fractal_zip_mpq_semantic.py)
  --large            same meaning as run_benchmarks --large for folder gzip-fast policy
  --no-xhprof        wall table only even if xhprof is loaded
  --no-stack-sample  disable Linux SIGUSR1 stack sampler (only xhprof/tideways then)
  --stack-hz=N       stack sampler rate when xhprof missing (default 100; needs pcntl+posix)
  --no-zip-time-budget  do not set FRACTAL_ZIP_TIME_BUDGET_MS (full encode; wall can exceed --budget-sec)
  --work-mode=symlink|copy  how to build benchmarks/.work/case_* from each corpus: symlink (default, no tree copy—much faster) or full copy (like run_benchmarks, isolates the tree on disk)
  --bench-keep-shell-literal-env   do not clear FRACTAL_ZIP_LITERAL_* / bundle probe env; do not set FRACTAL_ZIP_SKIP_ARC=1
  --jobs=N             run up to N zip_folder passes in parallel (Linux + pcntl_fork; sums zip_s like sequential; wall_clock_total drops; XHProf disabled for N>1 — use --jobs=1 to profile). Largest corpora (raw bytes) run first for CPU overlap; SIGUSR1 stack sampler is off in workers when N>1 (avoid nested forks).
  --repeat=N           run the full corpus list N times (median/p90/min/max wall + per-corpus zip_s); xhprof/tideways on pass 1 only when N>1 (cold; stable graph — later passes may return empty data)
  --profile-json=PATH  write JSON: wall stats + xhprof wall-by-function table (needs xhprof/tideways)
  --profile-csv=PATH   write CSV: main xhprof breakdown table (+ rollups section; needs xhprof)
  --xhprof-raw=PATH    write merged raw XHProf edge graph JSON (large; use with xhprof viewer / flame tools)
  --xhprof-rows=N      max rows in wall-by-function table (default 55; range 5–400)
  --xhprof-sort=F      sort main table by F=exclusive (default) or inclusive (subtree ms; cum%% disabled)
  --xhprof-edge-rows=N top hot XHProf edges (caller==>callee) in console + CSV (default 15; range 5–80)

Function-level data: install xhprof (see stderr hints for extension path), or rely on stack sampling.
  Fastest loop (wall time): php -d opcache.enable_cli=1 perf_test.php --quick --no-stack-sample  (profiler off by default)
  Faster CLI: php -d opcache.enable_cli=1 perf_test.php …  (when OPcache available)
  Hard OS cap (any corpus):  timeout -k 5 300 php perf_test.php --only=test_files63

TXT;
	exit(0);
}

/** Same list as benchmarks/run_benchmarks.php (folder gzip-fast defaults). */
$heavyCorporaFolderGzipFastDefault = [
	'test_files35',
	'test_files61',
	'test_files54',
	'test_files55',
	'test_files56',
	'test_files57',
	'test_files58',
	'test_files59',
	'test_files58_sample',
	'test_files59_sample',
	'test_files55_stratified',
];

/**
 * `default` is not listed here: it uses `benchDiscoverDefaultRunBenchmarksCorpora($root, false, false, $maxRawBytesFromCli)`; pass
 * `--maximum-size=…` for the same cap and discovery as `run_benchmarks` (see benchmarks/bench_default_corpus_list.php, bench_corpus_size.php).
 * Default perf_test sets FRACTAL_ZIP_SKIP_ARC=1 when unset (opt out: set FRACTAL_ZIP_SKIP_ARC=0 in the shell, or
 * use --bench-keep-shell-literal-env to stop perf_test from touching that var). Fast: quick slice. Wide: extra focus corpora.
 */
$presetCases = [
	'fast' => [
		'test_files2',
		'test_files52',
		'test_files10',        // small BMPs (replaces 70; keeps wall low)
		'test_files62',
		'test_files64',
	],
	'wide' => [
		'test_files2',
		'test_files52',
		'test_files62',
		'test_files63',
		'test_files64',        // single BMP literal baseline
		'test_files54_sample',
		'test_files61',        // multi-format rasters + nested outers
		'test_files56_sample',        // SC2Replay / MPQ subtree (nine replays)
		'test_files60',        // FLAC + sidecars
		'test_files69',        // stratified ≤5 MiB (text/html/raster/replay/phpinfo)
		'test_files35',        // BMP fractal stress (often very slow)
	],
];

$validPresets = ['default', 'fast', 'wide'];
if (!in_array($preset, $validPresets, true)) {
	fwrite(STDERR, "perf_test: unknown --preset={$preset} (try default|fast|wide)\n");
	exit(2);
}

if ($onlyFilter !== null) {
	$cases = $onlyFilter;
} elseif ($preset === 'default') {
	$cases = benchDiscoverDefaultRunBenchmarksCorpora($repoRoot, false, false, $maxRawBytes);
} else {
	$cases = $presetCases[$preset];
}
if ($maxRawBytes !== null) {
	// `benchDiscover` already size-filters for --preset=default; apply the same to --only and fast|wide.
	if ($onlyFilter !== null || $preset !== 'default') {
		$cases = benchFilterCorporaByMaxRawBytes($repoRoot, $cases, $maxRawBytes);
	}
}

if ($cases === []) {
	if ($maxRawBytes !== null) {
		fwrite(
			STDERR,
			"perf_test: no corpora left (materialize `test_files*` under {$repoRoot} or raise --maximum-size; cap={$maxRawBytes} B matches run_benchmarks)\n"
		);
	} else {
		fwrite(STDERR, "perf_test: no corpora to run (materialize test_files* under {$repoRoot}, or use --preset=fast|wide / --only=...)\n");
	}
	exit(2);
}
if ($onlyFilter === null && $preset === 'default' && is_resource(STDERR)) {
	$ms = (string) count($cases) . ' corpora';
	$ms .= $maxRawBytes !== null
		? " (default discovery with --maximum-size=" . (string) $maxRawBytes . " B, same as run_benchmarks; see benchmarks/bench_corpus_size.php)"
		: ' (run_benchmarks default discovery — see benchmarks/bench_default_corpus_list.php)';
	fwrite(STDERR, "perf_test: --preset=default = {$ms}\n");
}

if (!$keepShellLiteralEnv) {
	foreach ([
		'FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES',
		'FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN',
		'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN',
		'FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER',
		'FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT',
		'FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP',
		'FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER',
		'FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE',
	] as $literalEnvKey) {
		putenv($literalEnvKey);
	}
	// FreeArc is an external tool; default perf focus is PHP + 7z/gzip/brotli style paths. Respect explicit shell override.
	if (getenv('FRACTAL_ZIP_SKIP_ARC') === false) {
		putenv('FRACTAL_ZIP_SKIP_ARC=1');
	}
}

if (!$legacyFolderZip) {
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM');
}

if ($speedMode) {
	putenv('FRACTAL_ZIP_SPEED=1');
}

require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

/** Same default as {@see benchmarks/run_benchmarks.php}: zpaqfranz {@code -threads 0} unless overridden. */
if (getenv('FRACTAL_ZIP_ZPAQ_THREADS') === false && getenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS') === false) {
	putenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS=0');
}

if ($speedMode) {
	fractal_zip_ensure_literal_pac_stack_loaded();
	$speedTag = $quickMode ? '--quick' : '--speed';
	fwrite(STDERR, "perf_test: {$speedTag} → FRACTAL_ZIP_SPEED=1; literal PAC stack prewarmed (faster probes / fewer expensive outers; .fzc bytes may differ from default bench)\n");
}

// --- small I/O helpers (aligned with benchmarks/run_benchmarks.php) ---

function perf_remove_dir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
	}
	rmdir($dir);
}

function perf_copy_dir(string $src, string $dst): void
{
	if (is_file($dst) || is_dir($dst) || is_link($dst)) {
		perf_unlink_work_entry($dst);
	}
	if (!@mkdir($dst, 0755, true) && !is_dir($dst)) {
		throw new RuntimeException('mkdir failed: ' . $dst);
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$sub = $it->getSubPathname();
		$target = $dst . DIRECTORY_SEPARATOR . $sub;
		if ($item->isDir()) {
			mkdir($target, 0755, true);
		} else {
			$parent = dirname($target);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			if (!copy($item->getPathname(), $target)) {
				throw new RuntimeException('copy failed: ' . $item->getPathname());
			}
		}
	}
}

/**
 * Total raw file bytes (sum of file sizes). Prefers `du` on Unix for speed, then a PHP walk.
 * May differ from exact sum of filesize() by sparse/hardlink edge cases; fine for heavy-folder policy + table `raw` column.
 */
function perf_folder_total_raw_bytes(string $dir): int
{
	$duPath = $dir;
	$rp = @realpath($dir);
	if (is_string($rp) && $rp !== '' && is_dir($rp)) {
		$duPath = $rp;
	}
	if (PHP_OS_FAMILY === 'Windows') {
		$col = perf_collect_folder_files($duPath);
		return (int) $col['total'];
	}
	$q = escapeshellarg($duPath);
	$out = @shell_exec('du -sb ' . $q . ' 2>/dev/null');
	if (is_string($out) && preg_match('/^(\d+)\s/', trim($out), $m)) {
		$n = (int) $m[1];
		if ($n > 0) {
			return $n;
		}
	}
	$out = @shell_exec('du -sk ' . $q . ' 2>/dev/null');
	if (is_string($out) && preg_match('/^(\d+)\s/', trim($out), $m)) {
		$n = (int) $m[1] * 1024;
		if ($n > 0) {
			return $n;
		}
	}
	$col = perf_collect_folder_files($duPath);
	return (int) $col['total'];
}

/**
 * Remove a benchmarks/.work case entry. Symlinks are removed with unlink() only (never recursive-delete through to the corpus).
 */
function perf_unlink_work_entry(string $p): void
{
	if (is_link($p)) {
		if (!@unlink($p)) {
			throw new RuntimeException('failed to remove symlink: ' . $p);
		}
		return;
	}
	if (is_file($p)) {
		if (!@unlink($p)) {
			throw new RuntimeException('failed to remove file: ' . $p);
		}
		return;
	}
	if (is_dir($p)) {
		perf_remove_dir($p);
	}
}

/** @return true when native copy succeeded */
function perf_copy_dir_subprocess(string $src, string $dst): bool
{
	if (PHP_OS_FAMILY === 'Windows') {
		return false;
	}
	if (is_file($dst) || is_dir($dst) || is_link($dst)) {
		perf_unlink_work_entry($dst);
	}
	$parent = dirname($dst);
	if (!is_dir($parent)) {
		if (!@mkdir($parent, 0755, true) && !is_dir($parent)) {
			return false;
		}
	}
	$line = 'cp -a ' . escapeshellarg($src) . ' ' . escapeshellarg($dst) . ' 2>/dev/null';
	$code = 1;
	$null = null;
	@exec($line, $null, $code);
	return (int) $code === 0 && (is_dir($dst) || is_file($dst));
}

/**
 * Full tree clone: prefer `cp -a` (fast), else PHP per-file copy.
 */
function perf_copy_dir_optimized(string $src, string $dst): void
{
	if (is_file($dst) || is_dir($dst) || is_link($dst)) {
		perf_unlink_work_entry($dst);
	}
	if (perf_copy_dir_subprocess($src, $dst)) {
		return;
	}
	perf_copy_dir($src, $dst);
}

/**
 * Build benchmarks/.work/case_<label>: symlink to repo corpus (default) or copy (isolated tree).
 *
 * @param 'symlink'|'copy' $workMode
 */
function perf_materialize_case_work(string $src, string $workDir, string $workMode): void
{
	// One lstat(2) is cheaper than is_file+is_dir+is_link(3) on the common clean path; matches anything we'd remove.
	if (@lstat($workDir) !== false) {
		perf_unlink_work_entry($workDir);
	}
	if ($workMode === 'symlink') {
		// Windows: symlink to dir may require SeCreateSymbolicLink privilege; fall back to copy.
		$ok = @symlink($src, $workDir);
		if ($ok) {
			return;
		}
		perf_copy_dir_optimized($src, $workDir);
		return;
	}
	perf_copy_dir_optimized($src, $workDir);
}

/** @return array{files: array<string,int>, total: int} */
function perf_collect_folder_files(string $dir): array
{
	$files = [];
	$total = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		$n = filesize($path);
		if ($n === false) {
			continue;
		}
		$files[$rel] = $n;
		$total += $n;
	}
	ksort($files);
	return ['files' => $files, 'total' => $total];
}

/** @return array<string, string> relative path => sha1 hash (matches benchmarks/run_benchmarks.php collectFolderHashes) */
function perf_collect_folder_hashes(string $dir): array
{
	$out = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		if (str_ends_with(strtolower($rel), '.fzc')) {
			continue;
		}
		$h = sha1_file($path);
		if ($h === false) {
			continue;
		}
		$out[$rel] = $h;
	}
	ksort($out);
	return $out;
}

function perf_sc2replay_semantic_verify_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_BENCH_SC2REPLAY_SEMANTIC_VERIFY');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function perf_mpq_semantic_script_path(): ?string
{
	$root = realpath(__DIR__) ?: __DIR__;
	$p = realpath($root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'fractal_zip_mpq_semantic.py');
	return ($p !== false && is_file($p)) ? $p : null;
}

function perf_sc2replay_semantic_files_equal(string $pathA, string $pathB): bool
{
	static $cachedScript = null;
	if ($cachedScript === false) {
		return false;
	}
	if ($cachedScript === null) {
		$cachedScript = perf_mpq_semantic_script_path() ?? false;
	}
	if ($cachedScript === false) {
		return false;
	}
	$null = [];
	$ret = 1;
	exec(
		'command -v python3 >/dev/null 2>&1 && python3 '
		. escapeshellarg((string) $cachedScript)
		. ' semantic-equal '
		. escapeshellarg($pathA)
		. ' '
		. escapeshellarg($pathB)
		. ' 2>/dev/null',
		$null,
		$ret
	);
	return $ret === 0;
}

function perf_count_verify_mismatches(string $sourceDir, string $extractDir): int
{
	$srcH = perf_collect_folder_hashes($sourceDir);
	$dstH = perf_collect_folder_hashes($extractDir);
	$keys = array_fill_keys(array_merge(array_keys($srcH), array_keys($dstH)), true);
	$semantic = perf_sc2replay_semantic_verify_enabled();
	$mismatch = 0;
	foreach (array_keys($keys) as $k) {
		$a = $srcH[$k] ?? null;
		$b = $dstH[$k] ?? null;
		if ($a === $b) {
			continue;
		}
		if ($semantic && str_ends_with(strtolower($k), '.sc2replay')) {
			$pa = $sourceDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			$pb = $extractDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			if (is_file($pa) && is_file($pb) && perf_sc2replay_semantic_files_equal($pa, $pb)) {
				continue;
			}
		}
		$mismatch++;
	}
	return $mismatch;
}

function perf_benchmark_segment_length(): int
{
	$e = getenv('FRACTAL_ZIP_SEGMENT_LENGTH');
	if ($e !== false && $e !== '' && is_numeric($e)) {
		$n = (int) $e;
		return max(8, min(500000, $n));
	}
	return fractal_zip::DEFAULT_SEGMENT_LENGTH;
}

function perf_bench_heavy_folder_gzip_fast_min_raw_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES');
	if ($e !== false && trim((string) $e) !== '' && is_numeric($e)) {
		$n = (int) trim((string) $e);
		return $n > 0 ? $n : 128 * 1024 * 1024;
	}
	return 128 * 1024 * 1024;
}

function perf_bench_resolve_j_curve_w_scale(): float
{
	$e = getenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
	if ($e !== false && trim((string) $e) !== '' && is_numeric($e)) {
		return max(0.0, min(1.0, (float) $e));
	}
	return fractal_zip::J_CURVE_W_SCALE_DEFAULT_BENCH;
}

/** @return array{had: bool, value: string|null} */
function perf_bench_save_folder_gzip_fast_env(): array
{
	$v = getenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
	return ['had' => $v !== false, 'value' => $v === false ? null : $v];
}

/** @param array{had: bool, value: string|null} $saved */
function perf_bench_restore_folder_gzip_fast_env(array $saved): void
{
	if (!$saved['had']) {
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
	} else {
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=' . ($saved['value'] ?? ''));
	}
}

function perf_extension_dir(): string
{
	static $cached;
	if ($cached !== null) {
		return $cached;
	}
	$dir = ini_get('extension_dir');
	return $cached = (is_string($dir) ? $dir : '');
}

/** @return list<string> */
function perf_guess_profiler_so_paths(): array
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$dir = perf_extension_dir();
	if ($dir === '' || !is_dir($dir)) {
		return $cached = [];
	}
	$out = [];
	foreach (['xhprof.so', 'tideways_xhprof.so', 'uprofiler.so'] as $base) {
		$p = $dir . DIRECTORY_SEPARATOR . $base;
		if (is_file($p)) {
			$out[] = $p;
		}
	}
	foreach (['xhprof*.so', 'tideways*.so'] as $pat) {
		$globs = glob($dir . DIRECTORY_SEPARATOR . $pat) ?: [];
		foreach ($globs as $p) {
			if (is_file($p)) {
				$out[] = $p;
			}
		}
	}
	return $cached = array_values(array_unique($out));
}

function perf_stderr_profiler_install_hint(): void
{
	$ed = perf_extension_dir();
	$candidates = perf_guess_profiler_so_paths();
	fwrite(STDERR, "perf_test: xhprof/tideways not loaded in this PHP process.\n");
	fwrite(STDERR, "  extension_dir=" . ($ed !== '' ? $ed : '(empty — check php.ini)') . "\n");
	if ($candidates !== []) {
		fwrite(STDERR, "  found on disk — use the full path (not bare xhprof.so):\n");
		foreach ($candidates as $p) {
			fwrite(STDERR, '    php -d extension=' . $p . " perf_test.php\n");
		}
	} else {
		fwrite(STDERR, "  no xhprof*.so under extension_dir — install, then point -d extension= to that file.\n");
		fwrite(STDERR, "  Arch/Manjaro: sudo pacman -S php-xhprof   (or: pecl install xhprof; pecl config-get ext_dir)\n");
	}
}

function perf_stack_sampler_available(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	return $cached = (
		PHP_OS_FAMILY === 'Linux'
		&& function_exists('pcntl_fork')
		&& function_exists('pcntl_async_signals')
		&& function_exists('pcntl_signal')
		&& function_exists('posix_kill')
		&& function_exists('posix_getppid')
		&& function_exists('pcntl_waitpid')
	);
}

function perf_frame_label(array $f): string
{
	if (isset($f['class'])) {
		return $f['class'] . ($f['type'] ?? '::') . ($f['function'] ?? '');
	}
	return (string) ($f['function'] ?? '');
}

/**
 * Pick innermost meaningful frame; prefer symbols defined under fractal_zip*.php.
 *
 * @param list<array<string,mixed>> $bt
 */
function perf_stack_hot_label(array $bt): string
{
	$fallback = '(internal / C)';
	$n = count($bt);
	for ($i = 0; $i < $n; $i++) {
		$f = $bt[$i];
		$file = isset($f['file']) && is_string($f['file']) ? $f['file'] : '';
		if ($file !== '' && str_contains($file, 'perf_test.php')) {
			continue;
		}
		$label = perf_frame_label($f);
		if ($label === '' || str_starts_with($label, '{closure')) {
			continue;
		}
		$base = $file !== '' ? basename($file) : '';
		$tag = $base !== '' ? "{$label} @{$base}" : $label;
		if ($file !== '' && preg_match('/fractal_zip/i', $file) === 1) {
			return $tag;
		}
		$fallback = $tag;
	}
	return $fallback;
}

/**
 * Linux-only: fork sends SIGUSR1 to parent at ~hz; handler records debug_backtrace() bucket.
 * Overhead is typically a few percent; use xhprof when you need accurate inclusive time.
 *
 * @param callable():void $body
 * @param array<string,int> $hist
 */
function perf_run_with_stack_sampler(callable $body, float $hz, array &$hist): void
{
	if ($hz <= 0.0 || !perf_stack_sampler_available()) {
		$body();
		return;
	}
	$usec = (int) max(5000, (int) round(1_000_000 / min(500.0, max(10.0, $hz))));
	pcntl_async_signals(true);
	$handler = static function () use (&$hist): void {
		$bt = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 48);
		$key = perf_stack_hot_label($bt);
		$hist[$key] = ($hist[$key] ?? 0) + 1;
	};
	pcntl_signal(SIGUSR1, $handler, true);
	$child = pcntl_fork();
	if ($child === -1) {
		pcntl_signal(SIGUSR1, SIG_DFL);
		$body();
		return;
	}
	if ($child === 0) {
		$ppid = posix_getppid();
		while (@posix_kill($ppid, 0)) {
			@posix_kill($ppid, SIGUSR1);
			usleep($usec);
		}
		// Forked sampler child: flush-free quit (PHP may not expose ext/pcntl's _exit in all builds).
		if (function_exists('_exit')) {
			_exit(0);
		}
		exit(0);
	}
	try {
		$body();
	} finally {
		if (function_exists('posix_kill')) {
			@posix_kill($child, SIGKILL);
		}
		$st = 0;
		if (function_exists('pcntl_waitpid')) {
			pcntl_waitpid($child, $st);
		}
		pcntl_signal(SIGUSR1, SIG_DFL);
	}
}

function perf_profiler_backend(): string
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	if (function_exists('xhprof_enable')) {
		return $cached = 'xhprof';
	}
	if (function_exists('tideways_xhprof_enable')) {
		return $cached = 'tideways';
	}
	return $cached = '';
}

function perf_profiler_enable(string $backend): void
{
	if ($backend === 'xhprof') {
		$flags = 0;
		if (defined('XHPROF_FLAGS_CPU')) {
			$flags |= (int) constant('XHPROF_FLAGS_CPU');
		}
		if (defined('XHPROF_FLAGS_MEMORY')) {
			$flags |= (int) constant('XHPROF_FLAGS_MEMORY');
		}
		if (defined('XHPROF_FLAGS_NO_BUILTINS')) {
			$flags |= (int) constant('XHPROF_FLAGS_NO_BUILTINS');
		}
		// Ignore micro-hot builtins so xhprof_disable() stays fast on BMP literal paths (billions of ord/chr/substr edges).
		$opts = ['ignored_functions' => ['ord', 'chr', 'substr', 'intdiv']];
		xhprof_enable($flags, $opts);
		return;
	}
	if ($backend === 'tideways') {
		$flags = 0;
		foreach (['TIDEWAYS_XHPROF_FLAGS_CPU', 'TIDEWAYS_XHPROF_FLAGS_MEMORY'] as $c) {
			if (defined($c)) {
				$flags |= (int) constant($c);
			}
		}
		tideways_xhprof_enable($flags);
	}
}

/** @return array<string, array<string, int|float>>|null */
function perf_profiler_disable(string $backend): ?array
{
	if ($backend === 'xhprof' && function_exists('xhprof_disable')) {
		$d = xhprof_disable();
		return is_array($d) ? $d : null;
	}
	if ($backend === 'tideways' && function_exists('tideways_xhprof_disable')) {
		$d = tideways_xhprof_disable();
		return is_array($d) ? $d : null;
	}
	return null;
}

/**
 * @param array<string, array<string, int|float>> $a
 * @param array<string, array<string, int|float>> $b
 * @return array<string, array<string, int|float>>
 */
function perf_xhprof_merge_runs(array $a, array $b): array
{
	foreach ($b as $edge => $metrics) {
		if (!is_array($metrics)) {
			continue;
		}
		if (!isset($a[$edge])) {
			$a[$edge] = $metrics;
			continue;
		}
		foreach ($metrics as $k => $v) {
			if (is_int($v) || is_float($v)) {
				$prev = $a[$edge][$k] ?? 0;
				$a[$edge][$k] = (is_int($prev) || is_float($prev) ? $prev : 0) + $v;
			}
		}
	}
	return $a;
}

/**
 * Sum wall time and call counts per callee (standard xhprof/tideways parent==>child edges).
 *
 * @param array<string, array<string, int|float>> $run
 * @return array{wt: array<string, float>, ct: array<string, float>}
 */
function perf_xhprof_callee_totals(array $run): array
{
	$wt = [];
	$ct = [];
	foreach ($run as $edge => $m) {
		if (!is_string($edge) || !is_array($m)) {
			continue;
		}
		$parts = explode('==>', $edge, 2);
		if (count($parts) !== 2) {
			continue;
		}
		$callee = $parts[1];
		if (isset($m['wt']) && (is_int($m['wt']) || is_float($m['wt']))) {
			$wt[$callee] = ($wt[$callee] ?? 0.0) + (float) $m['wt'];
		}
		if (isset($m['ct']) && (is_int($m['ct']) || is_float($m['ct']))) {
			$ct[$callee] = ($ct[$callee] ?? 0.0) + (float) $m['ct'];
		}
	}
	return ['wt' => $wt, 'ct' => $ct];
}

/**
 * @param array<string, int|float> $vals
 * @return list<array{0: string, 1: float}>
 */
function perf_top_n_by_value(array $vals, int $n): array
{
	arsort($vals, SORT_NUMERIC);
	$out = [];
	$i = 0;
	foreach ($vals as $k => $v) {
		$out[] = [$k, (float) $v];
		if (++$i >= $n) {
			break;
		}
	}
	return $out;
}

/**
 * @param list<float|int> $vals
 * @return array{min: float, median: float, p90: float, max: float, mean: float}
 */
function perf_stats_numeric(array $vals): array
{
	$vals = array_values(array_filter($vals, static fn ($x) => is_int($x) || is_float($x)));
	$n = count($vals);
	if ($n === 0) {
		return ['min' => 0.0, 'median' => 0.0, 'p90' => 0.0, 'max' => 0.0, 'mean' => 0.0];
	}
	sort($vals, SORT_NUMERIC);
	$min = (float) $vals[0];
	$max = (float) $vals[$n - 1];
	$mid = intdiv($n, 2);
	$median = ($n % 2 === 1) ? (float) $vals[$mid] : ((float) $vals[$mid - 1] + (float) $vals[$mid]) / 2.0;
	// Nearest-rank p90: ceil(0.9*n)-1 in 0..n-1 (avoids p90=min when n=2)
	$p90Idx = (int) min($n - 1, max(0, (int) ceil(0.9 * $n) - 1));
	$p90 = (float) $vals[$p90Idx];
	$mean = array_sum($vals) / $n;

	return ['min' => $min, 'median' => $median, 'p90' => $p90, 'max' => $max, 'mean' => $mean];
}

/**
 * Derive per-function inclusive and exclusive wall time from an XHProf/Tideways edge graph.
 * inclusive[f] = sum of wt on all incoming edges *==>f ; exclusive[f] = inclusive[f] − sum of wt on outgoing edges f==>*.
 *
 * @param array<string, array<string, int|float>> $run
 * @return array{inclusive: array<string, float>, exclusive: array<string, float>}
 */
function perf_xhprof_inclusive_exclusive(array $run): array
{
	$inclusive = [];
	$outWt = [];
	foreach ($run as $edge => $m) {
		if (!is_string($edge) || !is_array($m)) {
			continue;
		}
		$parts = explode('==>', $edge, 2);
		if (count($parts) !== 2) {
			continue;
		}
		$caller = $parts[0];
		$callee = $parts[1];
		$wt = isset($m['wt']) && (is_int($m['wt']) || is_float($m['wt'])) ? (float) $m['wt'] : 0.0;
		$inclusive[$callee] = ($inclusive[$callee] ?? 0.0) + $wt;
		$outWt[$caller] = ($outWt[$caller] ?? 0.0) + $wt;
	}
	$exclusive = [];
	foreach ($inclusive as $fn => $inc) {
		$child = $outWt[$fn] ?? 0.0;
		$ex = $inc - $child;
		$exclusive[$fn] = $ex > 0.0 ? $ex : 0.0;
	}

	return ['inclusive' => $inclusive, 'exclusive' => $exclusive];
}

/**
 * Sorted per-function wall times for one report block (exclusive = “self”, incl = subtree via edges).
 *
 * @param array<string, float> $inclusive
 * @param array<string, float> $exclusive
 * @param array<string, float> $callCounts
 * @param 'exclusive'|'inclusive' $sortBy
 * @return array{total_exclusive_us: float, symbol_count: int, sort: string, rows: list<array{name: string, inclusive_us: float, exclusive_us: float, calls: float, exclusive_pct: float, exclusive_per_call_us: float}>}
 */
function perf_xhprof_wall_breakdown(array $inclusive, array $exclusive, array $callCounts, int $topN, string $sortBy = 'exclusive'): array
{
	$totalExcl = array_sum($exclusive);
	$names = array_unique(array_merge(array_keys($inclusive), array_keys($exclusive), array_keys($callCounts)));
	$list = [];
	foreach ($names as $name) {
		$ex = $exclusive[$name] ?? 0.0;
		$inc = $inclusive[$name] ?? 0.0;
		$ct = $callCounts[$name] ?? 0.0;
		$list[] = [
			'name' => $name,
			'inclusive_us' => $inc,
			'exclusive_us' => $ex,
			'calls' => $ct,
			'exclusive_pct' => $totalExcl > 0.0 ? 100.0 * $ex / $totalExcl : 0.0,
			'exclusive_per_call_us' => $ct > 0.0 ? $ex / $ct : $ex,
		];
	}
	if ($sortBy === 'inclusive') {
		usort($list, static fn ($a, $b) => $b['inclusive_us'] <=> $a['inclusive_us']);
	} else {
		usort($list, static fn ($a, $b) => $b['exclusive_us'] <=> $a['exclusive_us']);
	}
	$symbolCount = count($list);
	if ($topN > 0 && $symbolCount > $topN) {
		$list = array_slice($list, 0, $topN);
	}

	return ['total_exclusive_us' => $totalExcl, 'symbol_count' => $symbolCount, 'sort' => $sortBy, 'rows' => $list];
}

/** Rough grouping for rollup rows (exclusive time only). */
function perf_xhprof_symbol_bucket(string $name): string
{
	if (str_starts_with($name, 'fractal_zip::')) {
		return 'fractal_zip:: (class methods)';
	}
	if (preg_match('/^fractal_zip_literal/', $name) === 1) {
		return 'fractal_zip_literal_*';
	}
	if (str_starts_with($name, 'load::')) {
		return 'load::*';
	}
	if (str_starts_with($name, 'perf_')) {
		return 'perf_* (harness)';
	}
	if (str_contains($name, '{closure')) {
		return 'closures';
	}
	if (preg_match('/^fractal_zip_(image_pac|raster_canonical|literal_stream)/', $name) === 1) {
		return 'image/raster/stream PAC';
	}
	if (str_starts_with($name, 'fractal_zip_')) {
		return 'fractal_zip_* (globals)';
	}

	return 'other';
}

/**
 * @param array<string, float> $exclusive
 * @return list<array{bucket: string, exclusive_us: float, exclusive_ms: float, exclusive_pct: float}>
 */
function perf_xhprof_exclusive_rollups(array $exclusive, float $totalExclusiveUs): array
{
	$buckets = [];
	foreach ($exclusive as $name => $ex) {
		if (!is_string($name)) {
			continue;
		}
		$b = perf_xhprof_symbol_bucket($name);
		$buckets[$b] = ($buckets[$b] ?? 0.0) + (float) $ex;
	}
	arsort($buckets, SORT_NUMERIC);
	$out = [];
	foreach ($buckets as $label => $us) {
		$out[] = [
			'bucket' => $label,
			'exclusive_us' => $us,
			'exclusive_ms' => $us / 1000.0,
			'exclusive_pct' => $totalExclusiveUs > 0.0 ? 100.0 * $us / $totalExclusiveUs : 0.0,
		];
	}

	return $out;
}

function perf_try_git_head(string $repoRoot): ?string
{
	if (!is_dir($repoRoot . DIRECTORY_SEPARATOR . '.git')) {
		return null;
	}
	$out = shell_exec('command -v git >/dev/null 2>&1 && git -C ' . escapeshellarg($repoRoot) . ' rev-parse --short HEAD 2>/dev/null');
	if (!is_string($out)) {
		return null;
	}
	$s = trim($out);

	return $s !== '' ? $s : null;
}

/**
 * @param list<array{name: string, inclusive_us: float, exclusive_us: float, calls: float, exclusive_pct: float, exclusive_per_call_us: float}> $rows
 * @param list<array{bucket: string, exclusive_us: float, exclusive_ms: float, exclusive_pct: float}> $rollups
 * @param list<array{edge: string, wt_us: float, wt_ms: float, calls: float}> $edges
 */
function perf_write_profile_csv(string $path, array $rows, array $rollups, string $sort, array $edges = []): bool
{
	$fh = fopen($path, 'wb');
	if ($fh === false) {
		return false;
	}
	fputcsv($fh, ['section', 'rank', 'function_or_edge', 'exclusive_ms_or_wt_ms', 'exclusive_pct', 'inclusive_ms', 'calls', 'exclusive_us_per_call', 'sort']);
	$r = 1;
	foreach ($rows as $rw) {
		fputcsv($fh, [
			'breakdown',
			(string) $r,
			$rw['name'],
			sprintf('%.6f', $rw['exclusive_us'] / 1000.0),
			sprintf('%.6f', $rw['exclusive_pct']),
			sprintf('%.6f', $rw['inclusive_us'] / 1000.0),
			sprintf('%.0f', $rw['calls']),
			sprintf('%.3f', $rw['exclusive_per_call_us']),
			$sort,
		]);
		$r++;
	}
	foreach ($rollups as $rb) {
		fputcsv($fh, [
			'rollup',
			'',
			$rb['bucket'],
			sprintf('%.6f', $rb['exclusive_ms']),
			sprintf('%.6f', $rb['exclusive_pct']),
			'',
			'',
			'',
			'',
		]);
	}
	$er = 1;
	foreach ($edges as $te) {
		fputcsv($fh, [
			'edge',
			(string) $er,
			$te['edge'],
			sprintf('%.6f', $te['wt_ms']),
			'',
			'',
			sprintf('%.0f', $te['calls']),
			'',
			'',
		]);
		$er++;
	}
	fclose($fh);

	return true;
}

function perf_trunc_str(string $s, int $maxLen): string
{
	if (strlen($s) <= $maxLen) {
		return $s;
	}

	return substr($s, 0, max(0, $maxLen - 3)) . '...';
}

function perf_php_runtime_hint(): string
{
	$parts = [];
	$oe = ini_get('opcache.enable');
	$parts[] = 'opcache.enable=' . ($oe !== false && $oe !== '' ? $oe : '?');
	if (PHP_SAPI === 'cli') {
		$oec = ini_get('opcache.enable_cli');
		$parts[] = 'opcache.enable_cli=' . ($oec !== false && $oec !== '' ? $oec : '?');
	}
	$jit = ini_get('opcache.jit');
	if ($jit !== false && $jit !== '') {
		$parts[] = 'opcache.jit=' . $jit;
	}
	if (function_exists('opcache_get_status')) {
		$st = @opcache_get_status(false);
		if (is_array($st)) {
			$parts[] = 'opcache_active=' . (!empty($st['opcache_enabled']) ? '1' : '0');
		}
	}

	return implode('  ', $parts);
}

/**
 * Hot call-graph edges (merged XHProf parent==>child wt).
 *
 * @return list<array{edge: string, wt_us: float, wt_ms: float, calls: float}>
 */
function perf_xhprof_top_edges(array $merged, int $n): array
{
	$pairs = [];
	foreach ($merged as $edge => $m) {
		if (!is_string($edge) || !is_array($m)) {
			continue;
		}
		$wt = isset($m['wt']) && is_numeric($m['wt']) ? (float) $m['wt'] : 0.0;
		$ct = isset($m['ct']) && is_numeric($m['ct']) ? (float) $m['ct'] : 0.0;
		if ($wt <= 0.0) {
			continue;
		}
		$pairs[] = ['edge' => $edge, 'wt' => $wt, 'ct' => $ct];
	}
	usort($pairs, static fn ($a, $b) => $b['wt'] <=> $a['wt']);
	if ($n > 0 && count($pairs) > $n) {
		$pairs = array_slice($pairs, 0, $n);
	}
	$out = [];
	foreach ($pairs as $p) {
		$out[] = [
			'edge' => $p['edge'],
			'wt_us' => $p['wt'],
			'wt_ms' => $p['wt'] / 1000.0,
			'calls' => $p['ct'],
		];
	}

	return $out;
}

/**
 * @param string $fzcPath Pre-built path to `case_*.fzc` beside the work tree (from caller).
 * @param int    $folderGzipFast <code>-1</code> = do not set FRACTAL_ZIP_FOLDER_GZIP_FAST; <code>0</code>/<code>1</code> = off/on (from caller; includes <code>--large</code> in the decision).
 * @param int    $caseSpecial    <code>0</code> = default; <code>1</code> = test_files62 bundle; <code>2</code> = test_files61 brotli.
 * @param int    $rawBytes      Pre-summed raw byte total for $src (no duplicate du/walk here).
 * @param bool   $bufferFractalStdout When true, buffer stdout during zip (HTML progress when <code>fractal_zip::emit_html_trace()</code>).
 * @return array{zip_s: float, raw: int, fzc: int, verify_ok: ?bool}
 */
function perf_zip_one_case(
	string $label,
	string $src,
	string $workDir,
	string $fzcPath,
	int $folderGzipFast,
	int $caseSpecial,
	bool $doVerify,
	string $workMode,
	int $rawBytes,
	int $segmentLength,
	bool $bufferFractalStdout,
	array &$stackHist,
	float $stackSampleHz,
	?float $deadlineMono
): array {
	$bundleMinFilesTouched = false;
	$brotliHuge61Touched = false;
	if ($caseSpecial === 1) {
		$savedBundleMinFiles = getenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
		putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=65536');
		$bundleMinFilesTouched = true;
	} elseif ($caseSpecial === 2) {
		$savedBrotliHugeMode61 = getenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
		putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=full');
		$brotliHuge61Touched = true;
	}

	perf_materialize_case_work($src, $workDir, $workMode);
	@unlink($fzcPath);

	// FRACTAL_ZIP_J_CURVE_W_SCALE is set once for the run (see main); no per-case getenv/putenv/restore for J.
	// Heavy-folder gzip: caller precomputes whether to set; skip save/restore when -1 to avoid getenv/putenv in finally.
	$gzipEnvScoped = $folderGzipFast >= 0;
	$savedGz = $gzipEnvScoped ? perf_bench_save_folder_gzip_fast_env() : null;
	try {
		if ($gzipEnvScoped) {
			putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=' . (string) $folderGzipFast);
		}

		$useMultipass = true;
		$fz = new fractal_zip($segmentLength, $useMultipass, true, null, $useMultipass);
		$tbTouched = false;
		$tbSaved = false;
		if ($deadlineMono !== null) {
			$secLeft = $deadlineMono - microtime(true);
			if ($secLeft > 0.35) {
				$tbSaved = getenv('FRACTAL_ZIP_TIME_BUDGET_MS');
				$ms = (int) max(150, ($secLeft - 0.28) * 1000 * 0.97);
				putenv('FRACTAL_ZIP_TIME_BUDGET_MS=' . (string) $ms);
				$tbTouched = true;
			}
		}
		if ($bufferFractalStdout) {
			ob_start();
		}
		$t0 = microtime(true);
		try {
			if ($stackSampleHz > 0.0) {
				perf_run_with_stack_sampler(
					static function () use ($fz, $workDir): void {
						$fz->zip_folder($workDir, false);
					},
					$stackSampleHz,
					$stackHist
				);
			} else {
				$fz->zip_folder($workDir, false);
			}
		} finally {
			if ($tbTouched) {
				if ($tbSaved === false) {
					putenv('FRACTAL_ZIP_TIME_BUDGET_MS');
				} else {
					putenv('FRACTAL_ZIP_TIME_BUDGET_MS=' . $tbSaved);
				}
			}
		}
		$zipT1 = microtime(true);
		$zipS = $zipT1 - $t0;
		if ($bufferFractalStdout) {
			ob_end_clean();
		}

		$fzcSize = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
		$verifyOk = null;

		$leftAfterZip = $deadlineMono === null
			? PHP_FLOAT_MAX
			: max(0.0, $deadlineMono - $zipT1);
		if ($doVerify && $fzcSize > 0 && $leftAfterZip > 1.5) {
			$extractScratch = $workDir . '_extracted';
			if (is_dir($extractScratch)) {
				perf_remove_dir($extractScratch);
			}
			mkdir($extractScratch, 0755, true);
			// Aligned with benchmarks/.work/case_{$label} + ".fzc" (avoids basename on the verify path).
			$baseF = 'case_' . $label . '.fzc';
			$extractFzc = $extractScratch . DIRECTORY_SEPARATOR . $baseF;
			// Move when same filesystem to avoid a full data copy; fall back to copy.
			$movedFzc = @rename($fzcPath, $extractFzc);
			if ($movedFzc === false) {
				if (!@copy($fzcPath, $extractFzc)) {
					throw new RuntimeException("verify: could not stage .fzc into extract dir for {$label}");
				}
			}
			$fz2 = new fractal_zip($segmentLength, $useMultipass, true, null, $useMultipass);
			if ($bufferFractalStdout) {
				ob_start();
			}
			$fz2->open_container($extractFzc, true);
			if ($bufferFractalStdout) {
				ob_end_clean();
			}
			$mismatch = perf_count_verify_mismatches($src, $extractScratch);
			$verifyOk = $mismatch === 0;
			perf_remove_dir($extractScratch);
		} elseif ($doVerify && $fzcSize > 0) {
			fwrite(STDERR, "perf_test: skip verify for {$label} (wall budget nearly exhausted)\n");
		}

		perf_unlink_work_entry($workDir);
		@unlink($fzcPath);

		if ($bundleMinFilesTouched) {
			if ($savedBundleMinFiles === false) {
				putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
			} else {
				putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=' . $savedBundleMinFiles);
			}
		}
		if ($brotliHuge61Touched) {
			if ($savedBrotliHugeMode61 === false) {
				putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
			} else {
				putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=' . $savedBrotliHugeMode61);
			}
		}

		return ['zip_s' => $zipS, 'raw' => $rawBytes, 'fzc' => $fzcSize, 'verify_ok' => $verifyOk];
	} catch (Throwable $e) {
		if ($bundleMinFilesTouched) {
			if ($savedBundleMinFiles === false) {
				putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
			} else {
				putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=' . $savedBundleMinFiles);
			}
		}
		if ($brotliHuge61Touched) {
			if ($savedBrotliHugeMode61 === false) {
				putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
			} else {
				putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=' . $savedBrotliHugeMode61);
			}
		}
		if (is_file($workDir) || is_dir($workDir) || is_link($workDir)) {
			perf_unlink_work_entry($workDir);
		}
		@unlink($fzcPath);
		throw $e;
	} finally {
		if ($savedGz !== null) {
			perf_bench_restore_folder_gzip_fast_env($savedGz);
		}
	}
}

/**
 * Run zip_folder for many corpora with up to $jobs concurrent workers (pcntl_fork).
 * Per-case zip_s matches sequential runs; parent wall time is lower under parallelism.
 *
 * @param list<string> $queueInOrder
 * @param array<string,mixed> $srcByLabel
 * @param array<string,string> $workDirByLabel
 * @param array<string,string> $fzcByLabel
 * @param array<string,int> $gzipFolderFastByLabel
 * @param array<string,int> $caseSpecialByLabel
 * @param array<string,int> $rawByLabel
 * @param array<string,int> $stackHist
 *
 * @return array<string, array{label: string, zip_s: float, raw: int, fzc: int, verify_ok: ?bool}>
 */
function perf_zip_cases_parallel_fork(
	int $jobs,
	float $deadline,
	?float $deadlineForZip,
	float $perCaseMaxSec,
	array $queueInOrder,
	array $srcByLabel,
	array $workDirByLabel,
	array $fzcByLabel,
	array $gzipFolderFastByLabel,
	array $caseSpecialByLabel,
	array $rawByLabel,
	int $perfSegment,
	bool $bufferFractalStdout,
	string $workMode,
	bool $doVerify,
	array &$stackHist,
	float $stackHzForCase,
	string $workRoot,
): array {
	$usePerCaseZipSlice = $deadlineForZip !== null;
	$resultsByLabel = [];
	/** @var array<int, array{tmp: string, label: string}> */
	$running = [];
	$queue = $queueInOrder;

	while (count($queue) > 0 || count($running) > 0) {
		while (count($running) < $jobs && count($queue) > 0) {
			$tNow = microtime(true);
			if ($tNow >= $deadline) {
				if (count($queue) > 0 && is_resource(STDERR)) {
					fwrite(
						STDERR,
						'perf_test: global budget reached; draining '
						. (string) count($running) . ' worker(s), skipping '
						. (string) count($queue) . " queued corpora.\n"
					);
				}
				$queue = [];
				break;
			}
			$nLeft = count($queue) + count($running);
			if ($nLeft < 1) {
				$nLeft = 1;
			}
			$label = array_shift($queue);
			if ($usePerCaseZipSlice) {
				$remGlobal = max(0.0, $deadline - $tNow);
				$slice = min($remGlobal / (float) $nLeft, $perCaseMaxSec);
				$caseZipWallDeadline = $tNow + $slice;
				if ($caseZipWallDeadline > $deadline) {
					$caseZipWallDeadline = $deadline;
				}
			} else {
				$caseZipWallDeadline = null;
			}

			$tmpBase = @tempnam($workRoot, 'pfz_');
			if ($tmpBase === false) {
				throw new RuntimeException('tempnam failed under benchmarks/.work');
			}
			$tmpJson = $tmpBase . '.json';
			@unlink($tmpBase);

			$pid = pcntl_fork();
			if ($pid === -1) {
				$localHist = [];
				$dir = $srcByLabel[$label];
				$r = perf_zip_one_case(
					$label,
					$dir,
					$workDirByLabel[$label],
					$fzcByLabel[$label],
					$gzipFolderFastByLabel[$label],
					$caseSpecialByLabel[$label],
					$doVerify,
					$workMode,
					(int) $rawByLabel[$label],
					$perfSegment,
					$bufferFractalStdout,
					$localHist,
					$stackHzForCase,
					$caseZipWallDeadline
				);
				foreach ($localHist as $k => $c) {
					$stackHist[$k] = ($stackHist[$k] ?? 0) + $c;
				}
				$resultsByLabel[$label] = [
					'label' => $label,
					'zip_s' => $r['zip_s'],
					'raw' => $r['raw'],
					'fzc' => $r['fzc'],
					'verify_ok' => $r['verify_ok'],
				];
				@unlink($tmpJson);
				continue;
			}
			if ($pid === 0) {
				$payload = ['ok' => false];
				try {
					$localHist = [];
					$dir = $srcByLabel[$label];
					$r = perf_zip_one_case(
						$label,
						$dir,
						$workDirByLabel[$label],
						$fzcByLabel[$label],
						$gzipFolderFastByLabel[$label],
						$caseSpecialByLabel[$label],
						$doVerify,
						$workMode,
						(int) $rawByLabel[$label],
						$perfSegment,
						$bufferFractalStdout,
						$localHist,
						$stackHzForCase,
						$caseZipWallDeadline
					);
					$payload = [
						'ok' => true,
						'row' => [
							'label' => $label,
							'zip_s' => $r['zip_s'],
							'raw' => $r['raw'],
							'fzc' => $r['fzc'],
							'verify_ok' => $r['verify_ok'],
						],
						'stack' => $localHist,
					];
				} catch (Throwable $e) {
					$payload = ['ok' => false, 'error' => $e->getMessage(), 'label' => $label];
				}
				file_put_contents($tmpJson, json_encode($payload, JSON_UNESCAPED_SLASHES));
				exit(($payload['ok'] ?? false) === true ? 0 : 1);
			}
			$running[$pid] = ['tmp' => $tmpJson, 'label' => $label];
		}

		if ($running === []) {
			break;
		}
		$status = 0;
		$ended = pcntl_waitpid(-1, $status);
		if ($ended <= 0) {
			throw new RuntimeException('pcntl_waitpid failed');
		}
		$endedPid = (int) $ended;
		if (!isset($running[$endedPid])) {
			if (is_resource(STDERR)) {
				fwrite(STDERR, "perf_test: warning: reaped unexpected child pid {$endedPid} (not in fork pool); ignoring\n");
			}
			continue;
		}
		$meta = $running[$endedPid];
		unset($running[$endedPid]);
		$label = $meta['label'];
		$tmpPath = $meta['tmp'];
		$rawJ = is_file($tmpPath) ? file_get_contents($tmpPath) : false;
		@unlink($tmpPath);
		if (!is_string($rawJ) || $rawJ === '') {
			fwrite(STDERR, "perf_test: ERROR {$label}: worker produced no result payload\n");
			exit(1);
		}
		$payload = json_decode($rawJ, true);
		if (!is_array($payload) || ($payload['ok'] ?? false) !== true || !isset($payload['row']) || !is_array($payload['row'])) {
			$err = is_array($payload) ? (string) ($payload['error'] ?? 'unknown') : 'invalid json';
			$lb = is_array($payload) ? (string) ($payload['label'] ?? $label) : $label;
			fwrite(STDERR, "perf_test: ERROR {$lb}: {$err}\n");
			exit(1);
		}
		$row = $payload['row'];
		$resultsByLabel[$label] = [
			'label' => $label,
			'zip_s' => (float) ($row['zip_s'] ?? 0),
			'raw' => (int) ($row['raw'] ?? 0),
			'fzc' => (int) ($row['fzc'] ?? 0),
			'verify_ok' => array_key_exists('verify_ok', $row) ? $row['verify_ok'] : null,
		];
		if (isset($payload['stack']) && is_array($payload['stack'])) {
			foreach ($payload['stack'] as $k => $v) {
				if (!is_string($k) || !is_int($v)) {
					continue;
				}
				$stackHist[$k] = ($stackHist[$k] ?? 0) + $v;
			}
		}
	}

	return $resultsByLabel;
}

// --- main ---

$workRoot = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.work';
if (!is_dir($workRoot)) {
	mkdir($workRoot, 0755, true);
}

$profilerExtensionAvailable = perf_profiler_backend();
$backend = $noXhprof ? '' : $profilerExtensionAvailable;
if ($parallelJobs > 1 && $backend !== '') {
	if (is_resource(STDERR)) {
		fwrite(STDERR, "perf_test: --jobs>1: XHProf/Tideways disabled (fork pool); use --jobs=1 to capture a profile.\n");
	}
	$backend = '';
}
/** Linux + pcntl — when false, --jobs>1 falls back to sequential with one notice. */
$useParallelPool = $parallelJobs > 1 && function_exists('pcntl_fork');
if ($parallelJobs > 1 && !$useParallelPool && is_resource(STDERR)) {
	fwrite(STDERR, "perf_test: --jobs={$parallelJobs} requires pcntl_fork (not available); running corpora sequentially.\n");
}
// With --no-xhprof, the SIGUSR1 stack sampler (default 100 Hz) still runs unless --stack-hz=… opts in — that adds wall overhead.
$suppressStackForWall = ($backend === '' && $noXhprof && !$stackHzExplicit);
$useStackSampler = ($backend === '' && !$noStackSample && perf_stack_sampler_available() && !$suppressStackForWall);

if ($backend !== '') {
	fwrite(STDERR, "perf_test: profiler backend={$backend}\n");
} elseif ($useStackSampler) {
	fwrite(STDERR, "perf_test: stack sampling on (~{$stackHz} Hz, SIGUSR1; coarse proxy for hot PHP — install xhprof for inclusive wall time)\n");
} elseif ($suppressStackForWall && is_resource(STDERR)) {
	fwrite(STDERR, "perf_test: stack sampling off with --no-xhprof (add --stack-hz=50..500 to sample without xhprof; lower wall vs default 100 Hz)\n");
} else {
	perf_stderr_profiler_install_hint();
	if (!$noStackSample && !perf_stack_sampler_available()) {
		fwrite(STDERR, "perf_test: stack sampler needs Linux with pcntl+posix (or install xhprof).\n");
	}
}
if ($workMode === 'symlink' && is_resource(STDERR)) {
	fwrite(STDERR, "perf_test: --work-mode=symlink (no per-case tree copy; use --work-mode=copy to match run_benchmarks clone policy)\n");
}
if ($useParallelPool && is_resource(STDERR)) {
	fwrite(
		STDERR,
		"perf_test: parallel --jobs={$parallelJobs} (queue: largest corpora first for overlap; zip_folder_sum = Σ zip_s unchanged)\n"
	);
}
if ($useParallelPool && $useStackSampler && is_resource(STDERR)) {
	fwrite(
		STDERR,
		"perf_test: --jobs>1: SIGUSR1 stack sampling disabled in workers (nested pcntl_fork); use --jobs=1 --stack-hz=… to sample\n"
	);
}

$tScript = microtime(true);

if (!$noZipTimeBudget) {
	fwrite(STDERR, "perf_test: zip_folder uses FRACTAL_ZIP_TIME_BUDGET_MS from a per-case slice of the global --budget-sec wall: each case gets at most min(remaining/left, budget/total) seconds (stabilizes mix vs one corpus eating the run); not comparable to full bytes bench. --no-zip-time-budget disables.\n");
}
if ($repeatCount > 1) {
	fwrite(STDERR, "perf_test: repeat={$repeatCount} (stack samples merge across passes)\n");
	if ($backend !== '') {
		fwrite(STDERR, "perf_test: xhprof/tideways attaches to pass 1 only (cold); PHP+XHProf yields empty runs on later passes — use --repeat=1 for a single steady-state profile.\n");
	}
}

/** @var array<string, array<string, int|float>>|null $merged */
$merged = null;
/** @var list<list<array{label: string, zip_s: float, raw: int, fzc: int, verify_ok: ?bool}>> $allRepRows */
$allRepRows = [];
/** @var list<float> */
$wallPerRep = [];
/** @var list<float> */
$zipSumPerRep = [];
/** @var array<string,int> */
$stackHist = [];
$stackHzForCase = ($useStackSampler ? $stackHz : 0.0);
/** @var float Stack sampler Hz passed to `perf_zip_cases_parallel_fork` workers (0 when --jobs>1 to avoid nested forks). */
$stackHzForForkPool = ($useParallelPool && $stackHzForCase > 0.0) ? 0.0 : $stackHzForCase;

$perfHeavyGzipSet = array_fill_keys($heavyCorporaFolderGzipFastDefault, true);
$perfMinHeavyGzipBytes = perf_bench_heavy_folder_gzip_fast_min_raw_bytes();
$perfSegment = perf_benchmark_segment_length();
$perfJCurveW = perf_bench_resolve_j_curve_w_scale();
$bufferFractalStdout = fractal_zip::emit_html_trace();
$savedJScaleEnv = getenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
putenv('FRACTAL_ZIP_J_CURVE_W_SCALE=' . (string) $perfJCurveW);
register_shutdown_function(
	static function () use ($savedJScaleEnv): void {
		if ($savedJScaleEnv === false) {
			putenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
		} else {
			putenv('FRACTAL_ZIP_J_CURVE_W_SCALE=' . $savedJScaleEnv);
		}
	}
);
$__ds = DIRECTORY_SEPARATOR;
$__workCaseP = $workRoot . $__ds . 'case_';
$rawByLabel = [];
$srcByLabel = [];
$workDirByLabel = [];
$fzcByLabel = [];
$gzipFolderFastByLabel = [];
$caseSpecialByLabel = [];
foreach ($cases as $lab) {
	$srcByLabel[$lab] = $repoRoot . $__ds . $lab;
	$w = $__workCaseP . $lab;
	$workDirByLabel[$lab] = $w;
	$fzcByLabel[$lab] = $w . '.fzc';
	if ($lab === 'test_files62') {
		$caseSpecialByLabel[$lab] = 1;
	} elseif ($lab === 'test_files61') {
		$caseSpecialByLabel[$lab] = 2;
	} else {
		$caseSpecialByLabel[$lab] = 0;
	}
	$p = $srcByLabel[$lab];
	if (is_dir($p)) {
		$r = perf_folder_total_raw_bytes($p);
		$rawByLabel[$lab] = $r;
		if (isset($perfHeavyGzipSet[$lab])) {
			$gzipFolderFastByLabel[$lab] = (!$includeLarge && $r >= $perfMinHeavyGzipBytes) ? 1 : 0;
		} else {
			$gzipFolderFastByLabel[$lab] = -1;
		}
	} else {
		$gzipFolderFastByLabel[$lab] = -1;
	}
}

$casesN = max(1, count($cases));
$perCaseMaxSec = $budgetSec / (float) $casesN;

for ($rep = 0; $rep < $repeatCount; $rep++) {
	$tRepStart = microtime(true);
	$deadline = $tRepStart + $budgetSec;
	$deadlineForZip = $noZipTimeBudget ? null : $deadline;
	$usePerCaseZipSlice = $deadlineForZip !== null;
	// XHProf/Tideways: one session for the whole pass-0 sub-loop (N−1 fewer enable/disable; single graph vs N merged runs).
	// Other passes often return an empty XHProf graph in PHP+extension combinations (stack samples still merge over repeats).
	$profileThisPass = ($backend !== '' && $rep === 0);
	$xhprofThisRep = false;
	if ($profileThisPass) {
		perf_profiler_enable($backend);
		$xhprofThisRep = true;
	}

	$rowsThis = [];
	$caseIndex = 0;
	try {
		if ($useParallelPool) {
			$queue = [];
			foreach ($cases as $label) {
				$tCase = microtime(true);
				if ($tCase >= $deadline) {
					fwrite(STDERR, "perf_test: global budget ({$budgetSec}s) reached; stopping before {$label}.\n");
					break;
				}
				$dir = $srcByLabel[$label];
				if (!isset($rawByLabel[$label])) {
					if (!is_dir($dir)) {
						fwrite(STDERR, "perf_test: skip missing dir {$label}\n");
						continue;
					}
					$rx = perf_folder_total_raw_bytes($dir);
					$rawByLabel[$label] = $rx;
					if (isset($perfHeavyGzipSet[$label])) {
						$gzipFolderFastByLabel[$label] = (!$includeLarge && $rx >= $perfMinHeavyGzipBytes) ? 1 : 0;
					} else {
						$gzipFolderFastByLabel[$label] = -1;
					}
				}
				$queue[] = $label;
			}
			if (count($queue) > 1) {
				usort(
					$queue,
					static function (string $a, string $b) use ($rawByLabel): int {
						$ra = (int) ($rawByLabel[$a] ?? 0);
						$rb = (int) ($rawByLabel[$b] ?? 0);
						if ($ra !== $rb) {
							return $rb <=> $ra;
						}

						return $a <=> $b;
					}
				);
			}
			$byLabel = perf_zip_cases_parallel_fork(
				$parallelJobs,
				$deadline,
				$deadlineForZip,
				$perCaseMaxSec,
				$queue,
				$srcByLabel,
				$workDirByLabel,
				$fzcByLabel,
				$gzipFolderFastByLabel,
				$caseSpecialByLabel,
				$rawByLabel,
				$perfSegment,
				$bufferFractalStdout,
				$workMode,
				$doVerify,
				$stackHist,
				$stackHzForForkPool,
				$workRoot
			);
			foreach ($cases as $label) {
				if (!isset($byLabel[$label])) {
					continue;
				}
				$rowsThis[] = $byLabel[$label];
			}
		} else {
			foreach ($cases as $label) {
				$tCase = microtime(true);
				if ($tCase >= $deadline) {
					fwrite(STDERR, "perf_test: global budget ({$budgetSec}s) reached; stopping before {$label}.\n");
					break;
				}
				$dir = $srcByLabel[$label];
				if (!isset($rawByLabel[$label])) {
					if (!is_dir($dir)) {
						fwrite(STDERR, "perf_test: skip missing dir {$label}\n");
						$caseIndex++;
						continue;
					}
					$rx = perf_folder_total_raw_bytes($dir);
					$rawByLabel[$label] = $rx;
					if (isset($perfHeavyGzipSet[$label])) {
						$gzipFolderFastByLabel[$label] = (!$includeLarge && $rx >= $perfMinHeavyGzipBytes) ? 1 : 0;
					} else {
						$gzipFolderFastByLabel[$label] = -1;
					}
				}
				$nLeft = $casesN - $caseIndex;
				if ($nLeft < 1) {
					$nLeft = 1;
				}
				if ($usePerCaseZipSlice) {
					$remGlobal = max(0.0, $deadline - $tCase);
					// Fair share: adaptive remaining/nLeft, but never more than budget/casesN so one type (e.g. MPQ peel) cannot
					// consume almost the full global wall; unused budget flows to later cases as remGlobal grows.
					$slice = min($remGlobal / (float) $nLeft, $perCaseMaxSec);
					$caseZipWallDeadline = $tCase + $slice;
					if ($caseZipWallDeadline > $deadline) {
						$caseZipWallDeadline = $deadline;
					}
				} else {
					$caseZipWallDeadline = null;
				}
				try {
					$workDirI = $workDirByLabel[$label];
					$fzcI = $fzcByLabel[$label];
					$gzi = $gzipFolderFastByLabel[$label];
					$csi = $caseSpecialByLabel[$label];
					$rawI = (int) $rawByLabel[$label];
					$r = perf_zip_one_case(
						$label,
						$dir,
						$workDirI,
						$fzcI,
						$gzi,
						$csi,
						$doVerify,
						$workMode,
						$rawI,
						$perfSegment,
						$bufferFractalStdout,
						$stackHist,
						$stackHzForCase,
						$caseZipWallDeadline
					);
					$rowsThis[] = [
						'label' => $label,
						'zip_s' => $r['zip_s'],
						'raw' => $r['raw'],
						'fzc' => $r['fzc'],
						'verify_ok' => $r['verify_ok'],
					];
					$caseIndex++;
				} catch (Throwable $e) {
					if ($xhprofThisRep) {
						$run = perf_profiler_disable($backend);
						if (is_array($run)) {
							$merged = $run;
						}
						$xhprofThisRep = false;
					}
					fwrite(STDERR, "perf_test: ERROR {$label}: " . $e->getMessage() . "\n");
					exit(1);
				}
			}
		}
	} finally {
		if ($xhprofThisRep) {
			$run = perf_profiler_disable($backend);
			if (is_array($run)) {
				$merged = $run;
			}
		}
	}
	$allRepRows[] = $rowsThis;
	$ws = 0.0;
	foreach ($rowsThis as $rw) {
		$ws += $rw['zip_s'];
	}
	$zipSumPerRep[] = $ws;
	$wallPerRep[] = microtime(true) - $tRepStart;
}

$wall = microtime(true) - $tScript;
$wallStats = perf_stats_numeric($wallPerRep);
$zipSumStats = perf_stats_numeric($zipSumPerRep);

$lastRows = $allRepRows[count($allRepRows) - 1] ?? [];
/** @var array<string, list<float>> */
$zipSamplesByLabel = [];
foreach ($allRepRows as $repRows) {
	foreach ($repRows as $row) {
		$zipSamplesByLabel[$row['label']][] = $row['zip_s'];
	}
}
$rows = [];
foreach ($lastRows as $lastRow) {
	$lab = $lastRow['label'];
	$samples = $zipSamplesByLabel[$lab] ?? [$lastRow['zip_s']];
	$zs = perf_stats_numeric($samples);
	$zipShow = $repeatCount > 1 ? $zs['median'] : $lastRow['zip_s'];
	$rows[] = [
		'label' => $lab,
		'zip_s' => $zipShow,
		'raw' => $lastRow['raw'],
		'fzc' => $lastRow['fzc'],
		'verify_ok' => $lastRow['verify_ok'],
	];
}

$corporaPayload = array_map(static fn (array $r): array => [
	'label' => $r['label'],
	'zip_s' => $r['zip_s'],
	'raw_bytes' => $r['raw'],
	'fzc_bytes' => $r['fzc'],
	'verify_ok' => $r['verify_ok'],
], $rows);
$perfMetaGit = perf_try_git_head($repoRoot);

$presetLabel = $onlyFilter !== null ? 'custom(--only)' : $preset;
$benchmarkConfig = [
	'preset' => $preset,
	'preset_label' => $presetLabel,
	'maximum_raw_bytes' => $maxRawBytes,
	'j_curve_w_scale' => $perfJCurveW,
	'work_mode' => $workMode,
	'only' => $onlyFilter,
	'budget_sec' => $budgetSec,
	'zip_time_budget' => !$noZipTimeBudget,
	'verify' => $doVerify,
	'repeat' => $repeatCount,
	'large' => $includeLarge,
	'legacy_folder_zip' => $legacyFolderZip,
	'bench_keep_shell_literal_env' => $keepShellLiteralEnv,
	'xhprof_rows' => $xhprofBreakdownRows,
	'xhprof_sort' => $xhprofSort,
	'xhprof_edge_rows' => $xhprofEdgeRows,
	'stack_sample_hz' => $useStackSampler ? $stackHz : 0.0,
	'no_xhprof_cli' => $noXhprof,
	'profiler_backend' => $backend !== '' ? $backend : null,
	'speed_mode' => $speedMode,
	'quick_mode' => $quickMode,
	'parallel_jobs' => $useParallelPool ? $parallelJobs : 1,
];

$totalZip = 0.0;
foreach ($rows as $row) {
	$totalZip += $row['zip_s'];
}

echo str_repeat('=', 100) . "\n";
$zbt = $noZipTimeBudget ? 'off' : 'on';
$repNote = $repeatCount > 1 ? "  repeat={$repeatCount}" : '';
echo "fractal_zip perf_test  preset={$presetLabel}{$repNote}  wall_clock_total={$wall}s  zip_folder_sum={$totalZip}s  budget={$budgetSec}s  zip_time_budget={$zbt}  verify=" . ($doVerify ? 'on' : 'off') . "\n";
if ($repeatCount > 1) {
	echo '  wall_per_rep_s:  min=' . sprintf('%.4f', $wallStats['min'])
		. '  med=' . sprintf('%.4f', $wallStats['median'])
		. '  p90=' . sprintf('%.4f', $wallStats['p90'])
		. '  max=' . sprintf('%.4f', $wallStats['max']) . "\n";
	echo '  zip_folder_sum_rep_s:  min=' . sprintf('%.4f', $zipSumStats['min'])
		. '  med=' . sprintf('%.4f', $zipSumStats['median'])
		. '  p90=' . sprintf('%.4f', $zipSumStats['p90'])
		. '  max=' . sprintf('%.4f', $zipSumStats['max']) . "\n";
	echo "  (zip_s column = median over repeats; raw_bytes/fzc_bytes/verify from last pass)\n";
}
echo str_repeat('-', 100) . "\n";
$zipCol = $repeatCount > 1 ? 'zip_s_med' : 'zip_s';
printf("%-22s %10s %12s %12s %s\n", 'corpus', $zipCol, 'raw_bytes', 'fzc_bytes', 'verify');
foreach ($rows as $row) {
	$v = $row['verify_ok'];
	$vs = $v === null ? '—' : ($v ? 'ok' : 'FAIL');
	printf(
		"%-22s %10.4f %12d %12d %s\n",
		$row['label'],
		$row['zip_s'],
		$row['raw'],
		$row['fzc'],
		$vs
	);
}
echo str_repeat('=', 100) . "\n";

if (($merged === null || $merged === []) && is_resource(STDERR)) {
	fwrite(STDERR, "\n--- PHP time breakdown (perf_test) ---\n");
	if ($profilerExtensionAvailable === '') {
		fwrite(STDERR, "No XHProf/Tideways in this PHP build — per-corpus zip_s above is all you get unless you install php-xhprof\n");
		fwrite(STDERR, "or use --stack-hz with Linux pcntl (coarse samples, not per-function).\n");
	} elseif ($noXhprof) {
		fwrite(STDERR, "Profiler backend={$profilerExtensionAvailable} is available but --no-xhprof hid the call graph. Omit --no-xhprof and add e.g.\n");
		$guess = perf_guess_profiler_so_paths();
		if ($guess !== []) {
			fwrite(STDERR, '  php -d extension=' . $guess[0] . ' ' . $argv[0] . " ... --profile-json=benchmarks/.work/last_profile.json\n");
		}
	} else {
		fwrite(STDERR, "Profiler ran but returned an empty graph (uncommon).\n");
	}
	fwrite(STDERR, "Typical exclusive-time mix on 2M unified-stream corpora (from xhprof): ~99% fractal_zip::*, dominated by\n");
	fwrite(STDERR, "outer subprocess trials (zpaq, 7z, brotli, zstd, Arc) inside adaptive_compress / native-folder compare — not PHP loops.\n");
	fwrite(STDERR, str_repeat('-', 82) . "\n");
}

if ($merged !== null && $merged !== []) {
	$agg = perf_xhprof_callee_totals($merged);
	$ie = perf_xhprof_inclusive_exclusive($merged);
	$breakdown = perf_xhprof_wall_breakdown($ie['inclusive'], $ie['exclusive'], $agg['ct'], $xhprofBreakdownRows, $xhprofSort);
	$topWt = perf_top_n_by_value($agg['wt'], 15);
	$topCt = perf_top_n_by_value($agg['ct'], 25);
	$rollups = perf_xhprof_exclusive_rollups($ie['exclusive'], $breakdown['total_exclusive_us']);
	$exclSec = $breakdown['total_exclusive_us'] / 1000000.0;
	$zipRatio = $exclSec / max($totalZip, 1e-12);
	$wallRatio = $exclSec / max($wall, 1e-12);
	$topEdges = perf_xhprof_top_edges($merged, $xhprofEdgeRows);

	echo "\nPHP wall time by function (XHProf merged graph). excl_* = self time in the symbol; incl_* = subtree via callees.\n";
	echo sprintf(
		"zip_folder sum (table) ≈ %.3f s  ·  XHProf Σ exclusive ≈ %.3f s  ·  ratio %.2f× (PHP attribution; proc/child time often still charged to the caller)\n",
		$totalZip,
		$exclSec,
		$zipRatio
	);
	echo sprintf(
		"wall_clock_total ≈ %.3f s  ·  Σ exclusive / wall_clock ≈ %.2f× (overhead outside zip_folder + idle/gc)\n",
		$wall,
		$wallRatio
	);
	echo sprintf(
		'exclusive %% uses summed exclusives (~%.3f ms across %d symbols).' . "\n",
		$breakdown['total_exclusive_us'] / 1000.0,
		$breakdown['symbol_count']
	);
	if ($xhprofSort === 'inclusive') {
		echo "Main table sorted by incl_ms (desc). cum% disabled — not meaningful when ordering by subtree size.\n\n";
	} else {
		echo "cum% accumulates exclusive % down the main table.\n\n";
	}
	echo "Exclusive % by bucket (same denominator as excl%):\n";
	echo str_repeat('-', 82) . "\n";
	foreach ($rollups as $rb) {
		printf("  %-36s %7.2f%%  %11.1f ms\n", $rb['bucket'], $rb['exclusive_pct'], $rb['exclusive_ms']);
	}
	echo str_repeat('-', 82) . "\n\n";

	echo str_repeat('-', 112) . "\n";
	printf(
		"%-4s %10s %7s %7s %10s %11s %9s  %s\n",
		'#',
		'excl_ms',
		'excl%',
		($xhprofSort === 'exclusive' ? 'cum%' : '     '),
		'incl_ms',
		'calls',
		'µs/excl',
		'function'
	);
	echo str_repeat('-', 112) . "\n";
	$cumPct = 0.0;
	$rank = 1;
	foreach ($breakdown['rows'] as $rw) {
		$cumPct += $rw['exclusive_pct'];
		$cumCell = $xhprofSort === 'exclusive'
			? sprintf('%6.1f%%', $cumPct)
			: '   —  ';
		printf(
			"%-4d %10.3f %6.2f%% %7s %10.3f %11.0f %9.0f  %s\n",
			$rank,
			$rw['exclusive_us'] / 1000.0,
			$rw['exclusive_pct'],
			$cumCell,
			$rw['inclusive_us'] / 1000.0,
			$rw['calls'],
			$rw['exclusive_per_call_us'],
			$rw['name']
		);
		$rank++;
	}
	echo str_repeat('-', 112) . "\n";
	echo sprintf(
		"Showing top %d of %d symbols  ·  sort=%s  ·  (--xhprof-rows / --xhprof-sort)\n\n",
		count($breakdown['rows']),
		$breakdown['symbol_count'],
		$xhprofSort
	);

	echo "Highest inclusive wall time (subtree — coordinators / wrappers still worth eyeballing):\n";
	echo str_repeat('-', 95) . "\n";
	printf("%-4s %12s %11s  %s\n", '#', 'incl_ms', 'calls', 'function');
	$r2 = 1;
	foreach ($topWt as [$name, $us]) {
		$calls = $agg['ct'][$name] ?? 0.0;
		printf("%-4d %12.3f %11.0f  %s\n", $r2, $us / 1000.0, $calls, $name);
		$r2++;
	}

	echo "\nHighest call counts (often tiny per-call cost but worth sanity-checking):\n";
	echo str_repeat('-', 95) . "\n";
	printf("%-4s %11s  %s\n", '#', 'calls', 'function');
	$r3 = 1;
	foreach ($topCt as [$name, $n]) {
		printf("%-4d %11.0f  %s\n", $r3, $n, $name);
		$r3++;
	}

	echo "\nTop {$xhprofEdgeRows} XHProf edges by wall time (caller==>callee; shows hot call paths in the merged graph):\n";
	echo str_repeat('-', 100) . "\n";
	printf("%-4s %12s %11s  %s\n", '#', 'wt_ms', 'calls', 'edge');
	$re = 1;
	foreach ($topEdges as $te) {
		printf(
			"%-4d %12.3f %11.0f  %s\n",
			$re,
			$te['wt_ms'],
			$te['calls'],
			perf_trunc_str($te['edge'], 92)
		);
		$re++;
	}

	echo "\nexcl_ms + cum% surface where PHP spends wall time; bucket rollups show subsystem mix; edges show parent→child hops; incl_ms can dominate shallow parents.\n";

	$xhprofWallJson = [];
	foreach ($breakdown['rows'] as $r) {
		$xhprofWallJson[] = [
			'function' => $r['name'],
			'exclusive_us' => $r['exclusive_us'],
			'inclusive_us' => $r['inclusive_us'],
			'exclusive_ms' => $r['exclusive_us'] / 1000.0,
			'inclusive_ms' => $r['inclusive_us'] / 1000.0,
			'calls' => $r['calls'],
			'exclusive_pct_of_profiled_exclusive_sum' => $r['exclusive_pct'],
			'exclusive_us_per_call' => $r['exclusive_per_call_us'],
		];
	}

	if ($profileJsonPath !== null) {
		$payload = [
			'perf_test' => 1,
			'generated_at' => gmdate('c'),
			'argv' => $argv,
			'php_version' => PHP_VERSION,
			'php_os_family' => PHP_OS_FAMILY,
			'git_commit_short' => $perfMetaGit,
			'php_runtime' => perf_php_runtime_hint(),
			'benchmark_config' => $benchmarkConfig,
			'corpora' => $corporaPayload,
			'zip_folder_sum_table_s' => $totalZip,
			'xhprof_top_edges' => $topEdges,
			'repeat' => $repeatCount,
			'wall_clock_total_s' => $wall,
			'xhprof_zip_sum_vs_exclusive_ratio' => $zipRatio,
			'xhprof_wall_vs_clock_ratio' => $wallRatio,
			'xhprof_merged_edge_count' => count($merged),
			'wall_per_rep_s' => $wallPerRep,
			'wall_per_rep_stats' => $wallStats,
			'zip_folder_sum_per_rep_s' => $zipSumPerRep,
			'zip_folder_sum_rep_stats' => $zipSumStats,
			'xhprof_backend' => $backend,
			'xhprof_sort' => $xhprofSort,
			'xhprof_exclusive_rollups' => $rollups,
			'xhprof_wall_by_function' => $xhprofWallJson,
			'xhprof_total_exclusive_us' => $breakdown['total_exclusive_us'],
			'xhprof_symbol_count' => $breakdown['symbol_count'],
			'xhprof_breakdown_rows_limit' => $xhprofBreakdownRows,
			'top_exclusive_us' => array_map(static fn ($r) => [$r['name'], $r['exclusive_us']], $breakdown['rows']),
			'top_inclusive_us' => $topWt,
			'top_calls' => $topCt,
		];
		$dir = dirname($profileJsonPath);
		if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}
		if (@file_put_contents($profileJsonPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false) {
			fwrite(STDERR, "perf_test: wrote profile JSON to {$profileJsonPath}\n");
		} else {
			fwrite(STDERR, "perf_test: failed to write --profile-json={$profileJsonPath}\n");
		}
	}
	if ($xhprofRawPath !== null) {
		$dir = dirname($xhprofRawPath);
		if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}
		if (@file_put_contents($xhprofRawPath, json_encode($merged, JSON_UNESCAPED_SLASHES)) !== false) {
			fwrite(STDERR, "perf_test: wrote raw XHProf graph to {$xhprofRawPath}\n");
		} else {
			fwrite(STDERR, "perf_test: failed to write --xhprof-raw={$xhprofRawPath}\n");
		}
	}
	if ($profileCsvPath !== null) {
		$dir = dirname($profileCsvPath);
		if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
			@mkdir($dir, 0755, true);
		}
		if (perf_write_profile_csv($profileCsvPath, $breakdown['rows'], $rollups, $xhprofSort, $topEdges)) {
			fwrite(STDERR, "perf_test: wrote profile CSV to {$profileCsvPath}\n");
		} else {
			fwrite(STDERR, "perf_test: failed to write --profile-csv={$profileCsvPath}\n");
		}
	}
} elseif (!$noXhprof && $backend !== '') {
	echo "\n(profiler extension loaded but returned no xhprof rows)\n";
}

if ($profileCsvPath !== null && ($merged === null || $merged === [])) {
	fwrite(STDERR, "perf_test: --profile-csv skipped (no xhprof graph)\n");
}

if ($profileJsonPath !== null && ($merged === null || $merged === [])) {
	$payload = [
		'perf_test' => 1,
		'generated_at' => gmdate('c'),
		'argv' => $argv,
		'php_version' => PHP_VERSION,
		'php_os_family' => PHP_OS_FAMILY,
		'git_commit_short' => $perfMetaGit,
		'php_runtime' => perf_php_runtime_hint(),
		'benchmark_config' => $benchmarkConfig,
		'corpora' => $corporaPayload,
		'zip_folder_sum_table_s' => $totalZip,
		'repeat' => $repeatCount,
		'wall_clock_total_s' => $wall,
		'wall_per_rep_s' => $wallPerRep,
		'wall_per_rep_stats' => $wallStats,
		'zip_folder_sum_per_rep_s' => $zipSumPerRep,
		'zip_folder_sum_rep_stats' => $zipSumStats,
		'xhprof' => null,
		'note' => 'No xhprof/tideways graph (--no-xhprof or extension missing). Re-run without --no-xhprof for symbol ranks.',
	];
	$dir = dirname($profileJsonPath);
	if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}
	if (@file_put_contents($profileJsonPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false) {
		fwrite(STDERR, "perf_test: wrote profile JSON (wall stats only) to {$profileJsonPath}\n");
	} else {
		fwrite(STDERR, "perf_test: failed to write --profile-json={$profileJsonPath}\n");
	}
}

if ($stackHist !== []) {
	$totalS = array_sum($stackHist);
	arsort($stackHist, SORT_NUMERIC);
	echo "\nStack samples (merged across corpora; innermost fractal_zip*.php frame when visible — biased toward PHP code, not C in zlib):\n";
	echo str_repeat('-', 100) . "\n";
	printf("%-5s %8s %8s  %s\n", 'rank', 'samples', 'pct', 'symbol @file');
	$rank = 1;
	foreach ($stackHist as $sym => $cnt) {
		$pct = $totalS > 0 ? 100.0 * $cnt / $totalS : 0.0;
		printf("%-5d %8d %7.2f%%  %s\n", $rank, $cnt, $pct, $sym);
		if (++$rank > 40) {
			break;
		}
	}
	echo "\nDisable: --no-stack-sample   Rate: --stack-hz=50\n";
}

$profilerSummary = $noXhprof
	? 'profiler=off(--no-xhprof)'
	: ($backend !== '' ? "profiler={$backend}" : 'profiler=none');
fwrite(
	STDERR,
	sprintf(
		"perf_test: run  php=%s  os=%s%s  %s  %s\n",
		PHP_VERSION,
		PHP_OS_FAMILY,
		$perfMetaGit !== null ? "  git={$perfMetaGit}" : '',
		perf_php_runtime_hint(),
		$profilerSummary
	)
);
if (PHP_SAPI === 'cli' && function_exists('opcache_get_status')) {
	$oec = ini_get('opcache.enable_cli');
	if ($oec === '0' && ini_get('opcache.enable') === '1') {
		fwrite(
			STDERR,
			"perf_test: opcache.enable_cli=0 (PHP does not cache compiled script in CLI) — often faster wall: php -d opcache.enable_cli=1 " . basename(__FILE__) . " …\n"
		);
	}
}

exit(0);
