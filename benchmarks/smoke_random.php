#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: **`fin`** / **`chain`** use **`fz_inner_linear_fin_lin`** = **strlen(reference)+strlen(equiv)** where **`reference`** is **`fz_inner_row_reference_line(row)`** (the fractal_zip dictionary line; row fields `fractal_ref` + `dict` are only a storage split).
 *
 * Inner linear search helpers live in benchmarks/include/fractal_inner_passes.inc.php
 * (shared with benchmarks/bench_fractal_inner_cases_21_30.php).
 *
 * Select corpus via --case=21..32 (paths align with bench_fractal_inner_cases_definitions where defined).
 * Case 30 materializes test_files30/<member> from the first --bytes of test_files29/showing_off.txt.
 * Cases 31–32 expect on-disk multifractal corpora under test_files31/ and test_files32/.
 * Other cases read test_files{NN}/<file> (full file unless --bytes limits the read).
 *
 * **Golden ladder markers:** pipe-separated ladder lines (`# heuristic_*`) prefix an equiv
 * segment with `*` when it **exactly equals** a string in `smoke_random_golden_ladder_equivs_by_case()` for that `--case`
 * (fill in whichever cases you track — often four benchmark corpora). Legend prints once when that list is non-empty.
 *
 * **Golden table rows:** with `--highlight-golden`, any printed row whose **full** `equiv` equals one of those same
 * strings is wrapped in **gold ANSI background** on the whole line (TTY/`always`; respects `NO_COLOR`). Same data as
 * ladder `*` markers — no row indices or needles hard-coded in PHP.
 *
 * Case 23 ladder semantics: the generator equiv spells recursion only on the **outermost** substring operator (e.g. `<12"17"4>`).
 * Intermediate steps use bare inner `<off"len>` without `rec`; treat implicit ordinals as **x − y** where **x** is that outer recursion
 * value and **y** is the step index counted **from** the outside operator inward (so inner milestones must not be rejected for lacking explicit nested `rec`).
 * Default: **append + replace only** (`fz_inner_pass_search` with structural expansion **off**) so phase‑6 smokes stay light.
 * Structural successors stay in `fractal_inner_passes.inc.php` for benches / future use; turn them on here only when they are
 * **proven** for this smoke or **required** for other transforms, markers, or operations — not enabled for now.
 *
 * tests/run_php_smokes.sh phase 6: `--case=23 --max-passes=1 --quiet-inner`, stdout discarded.
 *
 * Usage (repo root):
 *   php benchmarks/smoke_random.php --case=23 --max-passes=1
 *   php benchmarks/smoke_random.php --case=23 --max-passes=1 --show-equiv --show-fractal-ref --show-replace-op   # substr op + slice of full reference line
 *   php benchmarks/smoke_random.php --case=23 --greedy-ladder=substr-mild --show-equiv   # mild-step ladder toward recipe markers (recommended)
 *   php benchmarks/smoke_random.php --case=30 --help
 */

/**
 * @return array<int, array{dir: string, file: string}>
 */
function smoke_random_inner_case_paths(): array {
	return array(
		21 => array('dir' => 'test_files21', 'file' => '2Dfractal.txt'),
		22 => array('dir' => 'test_files22', 'file' => '2Dfractal.txt'),
		23 => array('dir' => 'test_files23', 'file' => 'potentially_infinite_string.txt'),
		24 => array('dir' => 'test_files24', 'file' => 'dehacking_fractal_substring.txt'),
		25 => array('dir' => 'test_files25', 'file' => 'replace_operation.txt'),
		26 => array('dir' => 'test_files26', 'file' => 'gradient_operation.txt'),
		27 => array('dir' => 'test_files27', 'file' => 'tuple_operation.txt'),
		28 => array('dir' => 'test_files28', 'file' => 'scale_operation.txt'),
		29 => array('dir' => 'test_files29', 'file' => 'showing_off.txt'),
		30 => array('dir' => 'test_files30', 'file' => 'test_files2.txt'),
		31 => array('dir' => 'test_files31', 'file' => 'multifractal.txt'),
		32 => array('dir' => 'test_files32', 'file' => 'harder_multifractal.txt'),
	);
}

/**
 * Exact equiv strings that receive a leading `*` in pipe-separated ladder diagnostics for this run’s `--case`,
 * and that select **golden table rows** when you pass `--highlight-golden` (whole printed row, full `equiv` match).
 *
 * Use byte-for-byte identical milestones as in `# heuristic_*` chains or copied from
 * `smoke_random` output with `--show-equiv --equiv-chars=0`. Add one entry per case you care about (e.g. four corpora).
 *
 * @return array<int, list<string>>
 */
function smoke_random_golden_ladder_equivs_by_case(): array {
	return array(
		30 => array(
			// Paste exact equiv milestones from your golden ladder for case 30 (smoke slice).
		),
		31 => array(
			// Paste exact equiv milestones from your golden ladder for case 31.
		),
	);
}

/** @return list<string> */
function smoke_random_golden_equivs_for_case(int $caseNum): array {
	$by = smoke_random_golden_ladder_equivs_by_case();
	return array_key_exists($caseNum, $by) ? $by[$caseNum] : array();
}

/** Pipe-separated equiv chain; prefixes `*` when equiv is in $goldenExact (exact match). */
function smoke_random_pipe_mark_golden(array $equivChain, array $goldenExact): string {
	if ($goldenExact === array()) {
		return implode('|', $equivChain);
	}
	$parts = array();
	foreach ($equivChain as $eq) {
		$parts[] = (in_array($eq, $goldenExact, true) ? '*' : '') . $eq;
	}
	return implode('|', $parts);
}

/**
 * Whether to emit ANSI gold-row highlights (honours NO_COLOR).
 *
 * @param 'auto'|'always'|'never' $mode
 */
function smoke_random_stdout_color_enabled(string $mode): bool {
	if ($mode === 'never') {
		return false;
	}
	$no = getenv('NO_COLOR');
	if ($no !== false && trim((string) $no) !== '') {
		return false;
	}
	if ($mode === 'always') {
		return true;
	}
	if (\defined('STDOUT') && \is_resource(STDOUT) && function_exists('stream_isatty')) {
		return @stream_isatty(STDOUT);
	}
	return false;
}

/** Whole-row gold background + foreground for aligned table lines (after padding). */
function smoke_random_ansi_gold_row_wrap(string $line): string {
	return "\x1b[48;2;218;165;32m\x1b[30m" . $line . "\x1b[0m";
}

function smoke_random_maybe_print_golden_ladder_legend(int $caseNum): void {
	static $printed = false;
	if ($printed) {
		return;
	}
	$g = smoke_random_golden_equivs_for_case($caseNum);
	if ($g === array()) {
		return;
	}
	$printed = true;
	print("\n# ladder_golden: leading `*` when an equiv segment equals smoke_random_golden_ladder_equivs_by_case()[{$caseNum}] (" . (string) count($g) . ' exact strings).' . "\n");
}

/**
 * Append+replace substring op (`<off"len>` in current marker chars) and matching slice of `fz_inner_row_reference_line($row)`
 * at those byte offsets (same convention as fz_apply_append_replace / row last_slice).
 *
 * @return array{0:string,1:string} operator display, raw corpus slice (empty string if unavailable)
 */
function smoke_random_append_replace_op_slice(fractal_zip $fz, array $row): array {
	$ls = $row['last_slice'] ?? null;
	if (!is_array($ls) || !isset($ls['start'], $ls['end'])) {
		return array('-', '');
	}
	$start = (int) $ls['start'];
	$end = (int) $ls['end'];
	if ($end < $start) {
		return array('-', '');
	}
	$len = $end - $start;
	$corpus = fz_inner_row_reference_line($row);
	if ($start < 0 || $end > strlen($corpus)) {
		return array('-', '');
	}
	$op = $fz->left_fractal_zip_marker . (string) $start . $fz->mid_fractal_zip_marker . (string) $len . $fz->right_fractal_zip_marker;
	return array($op, substr($corpus, $start, $len));
}

/**
 * Space-padded columns so values line up under headers. Right-align is for numeric columns (pad on the left).
 *
 * @param list<string>              $headers
 * @param list<list<string>>        $rows
 * @param list<bool>                $rightAlign length must match $headers
 * @param list<bool>                $goldenMask same length as $rows when highlighting; true = gold background for whole line
 */
function smoke_random_print_aligned_table(array $headers, array $rows, array $rightAlign, string $colGap = '  ', array $goldenMask = array(), bool $goldAnsi = false): void {
	$n = count($headers);
	if ($n === 0) {
		return;
	}
	$widths = array();
	for ($j = 0; $j < $n; $j++) {
		$widths[$j] = strlen($headers[$j]);
	}
	foreach ($rows as $r) {
		for ($j = 0; $j < $n; $j++) {
			$cell = $r[$j] ?? '';
			$widths[$j] = max($widths[$j], strlen($cell));
		}
	}
	$pad = static function (string $s, int $w, bool $right): string {
		$len = strlen($s);
		if ($len >= $w) {
			return $s;
		}
		$sp = str_repeat(' ', $w - $len);
		return $right ? ($sp . $s) : ($s . $sp);
	};
	$parts = array();
	for ($j = 0; $j < $n; $j++) {
		$parts[] = $pad($headers[$j], $widths[$j], $rightAlign[$j] ?? false);
	}
	print(implode($colGap, $parts) . "\n");
	$rowIdx = 0;
	foreach ($rows as $r) {
		$parts = array();
		for ($j = 0; $j < $n; $j++) {
			$parts[] = $pad($r[$j] ?? '', $widths[$j], $rightAlign[$j] ?? false);
		}
		$line = implode($colGap, $parts);
		if ($goldAnsi && ($goldenMask[$rowIdx] ?? false)) {
			$line = smoke_random_ansi_gold_row_wrap($line);
		}
		print($line . "\n");
		$rowIdx++;
	}
}

/**
 * Print CLI help to stdout.
 */
/** Best-effort CPU count for default inner parallelism (caps at 32). */
function smoke_random_cpu_count_best_effort(): int {
	if (\is_readable('/proc/cpuinfo')) {
		$s = @file_get_contents('/proc/cpuinfo');
		if ($s !== false && \preg_match_all('/^processor\\s*:/m', $s, $m)) {
			$n = \count($m[0]);
			if ($n >= 1) {
				return \min(32, $n);
			}
		}
	}
	$env = \getenv('NUMBER_OF_PROCESSORS');
	if ($env !== false && ($n = (int) \trim((string) $env)) >= 1) {
		return \min(32, $n);
	}
	return 4;
}

function smoke_random_print_help(string $argv0): void {
	$bin = $argv0 !== '' ? $argv0 : 'benchmarks/smoke_random.php';
	$msg = <<<HELP
Smoke test: strlen(dictionary)+strlen(equivalence) after up to N append+replace inner passes
on a fractal_zip inner benchmark corpus member (cases 21–32). Uses full substring candidates
(FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1). No zip_folder / no adaptive outer codec.

Usage:
  php {$bin} [options]

Options:
  --help, -h              Show this message and exit.
  --case=N                Inner benchmark case 21–32 (default 30). Paths match bench where defined; cases 31–32 are smoke-only.
  --max-passes=N          Replace depth 0–30 (default 1). Alias: --passes=N
  --max-rows=N            Cap printed table rows (default 25; 0 = unlimited). With --beam-width>0, also caps
                          effective search beam to min(beam, max-rows) unless --beam-ignore-max-rows-cap.
  --top-series=N          Same as --max-rows=N.
  --trials=N              Random pass-1 samples when --max-passes=1 (default 200).
  --seed=N                Fixed RNG seed for --max-passes=1 random trials.
  --bytes=N               Case 30: prefix length read from test_files29/showing_off.txt when materializing
                          the slice (default 1000). Other cases: if set, read at most N bytes from the member file
                          (omit for full file).
  --segment=N             fractal_zip segment length (default 64, min 8).
  --member=NAME           Override the default file name for the case directory (default from --case).
  --refresh-corpus        Case 30 only: rewrite test_files30 member from test_files29/showing_off.txt prefix.
  --exhaustive            Multi-pass: forces full BFS (ignores --beam-width).
  --exhaustive-series     Deprecated; use --max-rows=0.
  --frontier-cap=N        Abort if BFS frontier exceeds N (default 5000000).
  --no-dedupe             Do not merge states with same dict+equiv (much heavier RAM).
  --memory-limit=L        php memory_limit for deep BFS (e.g. 2G). Env: FRACTAL_ZIP_SMOKE_MEMORY_LIMIT
  --beam-width=N          Keep N smallest-finLin states/pass (default 0 = no beam).
  --beam-ignore-max-rows-cap  Use full --beam-width even when --max-rows limits printing (no min() clamp).
  --inner-wall-s=N        Wall-clock seconds for fz_inner_pass_search.
  --inner-wall-reset-multipass  Fresh deadline when multipass expansion starts (bench-compatible).
  --inner-frontier-jobs=N Linux + pcntl_fork: partition multipass frontier rows across processes (env FZ_INNER_FRONTIER_JOBS; optional FZ_INNER_FRONTIER_FORK_MIN).
  --no-inner-frontier-jobs  Force FZ_INNER_FRONTIER_JOBS=1 even if the environment sets it.
  --inner-piece-jobs=N    Linux + pcntl_fork: parallelize append trials within very wide piece lists (env FZ_INNER_PIECE_JOBS; see FZ_INNER_PIECE_FORK_MIN, _GZIP_MIN_BYTES, _IGBINARY in fractal_inner_passes.inc.php).
  --no-inner-piece-jobs   Force FZ_INNER_PIECE_JOBS=1 even if the environment sets it.
  --inner-search-disk-cache   Enable FZ_INNER_PASS_SEARCH_DISK_CACHE (gzip-tagged cache under sys_get_temp_dir(); igbinary when available unless FZ_INNER_PASS_SEARCH_DISK_CACHE_IGBINARY=0 or _SERIAL=php).
  --no-inner-search-disk-cache  Disable disk cache.
  --verified-hint-file=PATH  Opt-in verified encoding hint recipe file (`E:` + `FR:` lines). May repeat.
                          Enables FZ_INNER_VERIFIED_ENCODING_HINTS and FZ_INNER_PROCESS_LADDER_VERIFY for this run.
  --match-chain-substr=S      Multipass repeat runs only: skip loading the giant `.fzic` blob via sidecar lookup (`strpos` on **chain** = `fz_chain_compact(linears)`, same as printed **chain**).
  --match-pieces-substr=S     Same sidecar path, matching joined **`pieces`** (`implode('|', row['pieces'])`, same bytes as the printed **pieces** column before preview truncation). Typical replacement for piping through rg(1) on needle runs.
  Uses `{fzic}.chainidx.sqlite` when PHP sqlite3 is enabled, else `{fzic}.chainrows.tsv.gz` + `.chainidx.meta`. Built on cache write or self-healed when `.fzic` is replayed (sidecar v2 adds `pieces`; older sidecars are rebuilt automatically). Default `--stop-after-chain-matches=1` when either match flag is set (`=0` = unlimited).
  --stop-after-chain-matches=N Cap rows returned from the sidecar index for `--match-chain-substr` / `--match-pieces-substr` (0 = no LIMIT).
  --quiet-inner           Suppress fz_inner_pass_search stderr progress (overrides default for deep multipass).
  --no-quiet-inner        Force stderr progress even when --max-passes>=3 (default is quiet for >=3 passes).
  --preview-chars=N       Max chars per piece snippet in tables (default 28; capped at 200).
  --equiv-chars=N         With --show-equiv: max chars printed in the equiv column (default: max(48, preview-chars)).
                          Use 0 for no truncation (full row equiv string).
  --show-fractal-ref      Add **reference** column before equiv: full dictionary line (`fz_inner_row_reference_line`, same bytes as fractal_zip’s second arg to fractally_process_string). Width from --fractal-ref-chars.
  --fractal-ref-chars=N   Max chars for that reference column (default: max(48, preview-chars); 0 = unlimited).
  --show-replace-op       After pieces: substr_op (`<off"len>`) and corpus_slice bytes for the **last** append (`last_slice`).
                          The **pieces** column is always the exact needle string appended each step (joined with '|'); use this flag for template span vs needle when they differ.
  --pieces-max-width=N    Max width for joined piece column (default 96).
  --greedy-ladder[=MODE]  Sets FZ_INNER_GREEDY_EMIT_LADDER for inner passes (bench use). smoke_random currently prints
                          no `# heuristic_*` ladder lines — block is commented out until structural smoke returns.
                          MODE: interleave (default), substr, substr-mild.
  --single-struct-step    Sets FZ_INNER_SINGLE_STRUCT_STEP for structural inner successors.
  --show-equiv            Add equiv column (width from --equiv-chars or preview-chars).
  --prune-non-improving   Drop table rows with finLin >= baseLin (strict shrink-only view). Default keeps pass-1 regressions so longer intermediates appear.
  --highlight-golden[=MODE]  Paint whole table rows gold when row equiv exactly matches a golden milestone string.
                          Usually smoke_random_golden_ladder_equivs_by_case()[--case].
                          MODE: auto (default if flag given) = ANSI only when stdout is a TTY; always = emit codes even
                          when piped (use with less -R); never = same as omitting flag. Honors NO_COLOR.

Notes:
  Column **fin** is **len(reference)+len(equiv)** (`fz_inner_linear_fin_lin` / `fz_inner_row_fin_lin`). Long **equiv** previews can still make manual sums look wrong — use `--equiv-chars=0` for the full string.
  Structural expansion for `fz_inner_pass_search` is **off** in this script (append+replace rows only).
  By default the results table includes encodings **longer** than baseLin when pass-1 append+replace increases linear (dictionary investment before equiv shrinks); use --prune-non-improving for the old strict filter.
  Case 30 enables FZ_INNER_VERIFY_RAW_PREFIX (disk raw is a prefix of the case-29 recipe expansion).
  Wall time is printed on stderr as wall_s=… after fractal_zip.php loads.
  --trials / --seed apply only when --max-passes=1.
  Case 23 + max-passes>=5 enables inner disk cache by default (repeat runs reuse fz_inner_pass_search output); use --no-inner-search-disk-cache for a cold run.
  Large union replay: `--match-pieces-substr` / `--match-chain-substr` hit the chain sidecar (`*.chainidx.meta` + SQLite or gzip TSV) so PHP does not unserialize every cached row; same behavior via env `FZ_INNER_PASS_SEARCH_PIECES_SUBSTR` / `FZ_INNER_PASS_SEARCH_CHAIN_SUBSTR` before calling `fz_inner_pass_search()` from other scripts.
  Case 23 + max-passes>=5 also picks default frontier/piece job counts (when those env vars are unset) and lowers fork thresholds for inner parallelism; use --no-inner-frontier-jobs / --no-inner-piece-jobs for strictly serial cold runs.
  Profiling: env FZ_INNER_PROFILE=1 prints cumulative buckets on stderr at exit (`expand.asc.*` = phases inside `all_substrings_count`; `expand.cand_miss.*` = fractal_ref merges on cand-cache miss). For inclusive totals without pcntl workers use `--no-inner-frontier-jobs`. xhprof: benchmarks/xhprof_prepend.php — set FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1 so OPcache re-exec does not hide work in a child process.
  Multipass tuning: env FZ_INNER_MP_GC_EVERY_DEPTH=N (default 4; 0 disables periodic gc_collect_cycles between depths).

Examples:
  php {$bin} --case=23 --max-passes=1 --show-equiv --show-fractal-ref --show-replace-op
  php {$bin} --case=23 --max-passes=4 --highlight-golden --max-rows=0 --equiv-chars=0
  php {$bin} --case=23 --max-passes=2 --max-rows=40
  php {$bin} --case=23 --greedy-ladder=substr-mild --show-equiv --show-fractal-ref --equiv-chars=0 --fractal-ref-chars=0 --max-rows=0
  php {$bin} --case=23 --max-passes=5 --match-pieces-substr='aaaaa|bb<0"12>b<0"12>bb|a<12"17>aaaa|bb<0"12>b<0"12>bb|a<12"17>aaaa' --show-fractal-ref --show-equiv --max-rows=0
  php {$bin} --case=30 --max-passes=1 --max-rows=50
  php {$bin} --case=29 --bytes=4096 --max-passes=1
  php {$bin} --case=31 --greedy-ladder=substr-mild --max-passes=1
  php {$bin} --case=32 --verified-hint-file=recipe_32.txt --max-passes=1 --show-fractal-ref --show-equiv

Golden ladder / golden rows: edit smoke_random_golden_ladder_equivs_by_case() in this file; ladder pipes prefix `*`;
  `--highlight-golden` gold-highlights table rows whose full equiv equals one of those strings for `--case`.

HELP;
	fwrite(STDOUT, $msg);
}

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);

if (isset($argv)) {
	foreach ($argv as $i => $arg) {
		if ($i === 0) {
			continue;
		}
		if ($arg === '--help' || $arg === '-h') {
			smoke_random_print_help((string) ($argv[0] ?? ''));
			exit(0);
		}
	}
}

$caseNum = 30;
$trials = 200;
$seed = null;
$bytesExplicit = false;
$bytes = 1000;
$exhaustive = false;
$exhaustiveSeries = false;
$refreshCorpus = false;
$segment = 64;
$memberOverride = false;
$member = '';
$maxPasses = 1;
$maxRows = 25;
$frontierHardCap = 5000000;
$dedupeFrontier = true;
$memoryLimitCli = null;
$beamWidth = null;
$beamIgnoreMaxRowsCap = false;
$innerWallSeconds = null;
$innerWallResetMultipass = false;
/** null = use environment only; else explicit FZ_INNER_FRONTIER_JOBS */
$innerFrontierJobsCli = null;
$innerFrontierJobsAutoOff = false;
/** null = use environment only; else explicit FZ_INNER_PIECE_JOBS */
$innerPieceJobsCli = null;
$innerPieceJobsAutoOff = false;
/** null = tri-state disk cache CLI override; true/false = force */
$innerSearchDiskCacheCli = null;
/** @var list<string> Opt-in verified encoding hint recipe files. */
$verifiedHintFiles = array();
/** null = unset; non-null = literal substring on compact numeric chain (`50>55>…`, see --help). */
$matchChainSubstr = null;
/** null = unset; literal substring on joined append needles (`pieces` column / row['pieces'] joined with '|'). */
$matchPiecesSubstr = null;
/** null = CLI did not pass --stop-after-chain-matches; otherwise cap for chain/pieces sidecar SQL/stream scan (0 = unlimited). */
$stopAfterChainMatchesCli = null;
$quietInner = false;
/** True if --quiet-inner or --no-quiet-inner was passed (skip auto-quiet for deep multipass). */
$quietInnerExplicit = false;
$previewChars = 28;
$piecesMaxWidth = 96;
/** null: use max(48, previewChars); else max equiv chars in results table (0 = unlimited). */
$equivTableChars = null;
/** null: use max(48, previewChars); else max reference-line chars in results table (0 = unlimited). */
$fractalRefTableChars = null;
/** null = legacy collapsed `<substr-intro*>` jumps; else FZ_INNER_GREEDY_EMIT_LADDER value */
$greedyLadderMode = null;
$singleStructStep = false;
$showEquiv = false;
$showFractalRef = false;
$showReplaceOp = false;
$pruneNonImproving = false;
/** @var 'auto'|'always'|'never' Golden full-row ANSI when equiv matches smoke_random_golden_ladder_equivs_by_case()[--case]. */
$highlightGolden = 'never';

for ($i = 1, $n = isset($argv) ? count($argv) : 0; $i < $n; $i++) {
	$a = $argv[$i];
	if (preg_match('/^--case=(\d+)$/', $a, $m)) {
		$caseNum = (int) $m[1];
	} elseif (preg_match('/^--trials=(\d+)$/', $a, $m)) {
		$trials = max(1, (int) $m[1]);
	} elseif (preg_match('/^--seed=(\d+)$/', $a, $m)) {
		$seed = (int) $m[1];
	} elseif (preg_match('/^--bytes=(\d+)$/', $a, $m)) {
		$bytes = max(1, (int) $m[1]);
		$bytesExplicit = true;
	} elseif ($a === '--exhaustive') {
		$exhaustive = true;
	} elseif ($a === '--exhaustive-series') {
		$exhaustiveSeries = true;
	} elseif ($a === '--refresh-corpus') {
		$refreshCorpus = true;
	} elseif (preg_match('/^--segment=(\d+)$/', $a, $m)) {
		$segment = max(8, (int) $m[1]);
	} elseif (preg_match('/^--member=(.+)$/', $a, $m)) {
		$member = $m[1];
		$memberOverride = true;
	} elseif (preg_match('/^--max-passes=(\d+)$/', $a, $m)) {
		$v = (int) $m[1];
		if ($v > 30) {
			fwrite(STDERR, "--max-passes must be between 0 and 30 (got {$v})\n");
			exit(2);
		}
		$maxPasses = $v;
	} elseif (preg_match('/^--passes=(\d+)$/', $a, $m)) {
		$v = (int) $m[1];
		if ($v > 30) {
			fwrite(STDERR, "--passes must be between 0 and 30 (got {$v}); use --max-passes instead.\n");
			exit(2);
		}
		$maxPasses = $v;
	} elseif (preg_match('/^--max-rows=(\d+)$/', $a, $m)) {
		$maxRows = max(0, (int) $m[1]);
	} elseif (preg_match('/^--top-series=(\d+)$/', $a, $m)) {
		$maxRows = max(0, (int) $m[1]);
	} elseif (preg_match('/^--frontier-cap=(\d+)$/', $a, $m)) {
		$frontierHardCap = max(10000, (int) $m[1]);
	} elseif ($a === '--no-dedupe') {
		$dedupeFrontier = false;
	} elseif (preg_match('/^--memory-limit=(.+)$/', $a, $m)) {
		$memoryLimitCli = trim($m[1]);
	} elseif (preg_match('/^--beam-width=(\d+)$/', $a, $m)) {
		$beamWidth = (int) $m[1];
	} elseif ($a === '--beam-ignore-max-rows-cap') {
		$beamIgnoreMaxRowsCap = true;
	} elseif (preg_match('/^--inner-wall-s=([0-9]+(?:\.[0-9]+)?)$/', $a, $m)) {
		$innerWallSeconds = max(0.001, (float) $m[1]);
	} elseif ($a === '--inner-wall-reset-multipass') {
		$innerWallResetMultipass = true;
	} elseif (preg_match('/^--inner-frontier-jobs=(\d+)$/', $a, $m)) {
		$innerFrontierJobsCli = max(1, min(32, (int) $m[1]));
	} elseif ($a === '--no-inner-frontier-jobs') {
		$innerFrontierJobsAutoOff = true;
	} elseif (preg_match('/^--inner-piece-jobs=(\d+)$/', $a, $m)) {
		$innerPieceJobsCli = max(1, min(32, (int) $m[1]));
	} elseif ($a === '--no-inner-piece-jobs') {
		$innerPieceJobsAutoOff = true;
	} elseif ($a === '--inner-search-disk-cache') {
		$innerSearchDiskCacheCli = true;
	} elseif ($a === '--no-inner-search-disk-cache') {
		$innerSearchDiskCacheCli = false;
	} elseif (preg_match('/^--verified-hint-file=(.+)$/', $a, $m)) {
		$hintPath = trim((string) $m[1]);
		if ($hintPath === '') {
			fwrite(STDERR, "--verified-hint-file requires a non-empty path\n");
			exit(2);
		}
		$resolvedHintPath = realpath($hintPath);
		$verifiedHintFiles[] = $resolvedHintPath !== false ? $resolvedHintPath : $hintPath;
	} elseif (preg_match('/^--match-chain-substr=(.*)$/', $a, $m)) {
		$matchChainSubstr = (string) $m[1];
	} elseif (preg_match('/^--match-pieces-substr=(.*)$/', $a, $m)) {
		$matchPiecesSubstr = (string) $m[1];
	} elseif (preg_match('/^--stop-after-chain-matches=(\d+)$/', $a, $m)) {
		$stopAfterChainMatchesCli = (int) $m[1];
	} elseif ($a === '--quiet-inner') {
		$quietInner = true;
		$quietInnerExplicit = true;
	} elseif ($a === '--no-quiet-inner') {
		$quietInner = false;
		$quietInnerExplicit = true;
	} elseif (preg_match('/^--preview-chars=(\d+)$/', $a, $m)) {
		$previewChars = max(8, min(200, (int) $m[1]));
	} elseif (preg_match('/^--equiv-chars=(\d+)$/', $a, $m)) {
		$equivTableChars = (int) $m[1];
	} elseif (preg_match('/^--fractal-ref-chars=(\d+)$/', $a, $m)) {
		$fractalRefTableChars = (int) $m[1];
	} elseif ($a === '--show-fractal-ref') {
		$showFractalRef = true;
	} elseif ($a === '--show-replace-op') {
		$showReplaceOp = true;
	} elseif (preg_match('/^--pieces-max-width=(\d+)$/', $a, $m)) {
		$piecesMaxWidth = max(24, min(500, (int) $m[1]));
	} elseif ($a === '--single-struct-step') {
		$singleStructStep = true;
	} elseif ($a === '--greedy-ladder') {
		$greedyLadderMode = 'interleave';
	} elseif (preg_match('/^--greedy-ladder=(.+)$/', $a, $m)) {
		$v = strtolower(trim($m[1]));
		if ($v === 'substr' || $v === 'substr_chain') {
			$greedyLadderMode = 'substr';
		} elseif ($v === 'substr-mild' || $v === 'substr_mild') {
			$greedyLadderMode = 'substr_mild';
		} else {
			$greedyLadderMode = 'interleave';
		}
	} elseif ($a === '--show-equiv') {
		$showEquiv = true;
	} elseif ($a === '--prune-non-improving') {
		$pruneNonImproving = true;
	} elseif ($a === '--highlight-golden') {
		$highlightGolden = 'auto';
	} elseif (preg_match('/^--highlight-golden=(.+)$/', $a, $m)) {
		$v = strtolower(trim($m[1]));
		if ($v === 'always' || $v === 'auto' || $v === 'never') {
			$highlightGolden = $v;
		} else {
			fwrite(STDERR, "--highlight-golden= expects auto, always, or never (got {$v})\n");
			exit(2);
		}
	} else {
		fwrite(STDERR, "Unknown argument: {$a}\n");
		fwrite(STDERR, "Try: php benchmarks/smoke_random.php --help\n");
		exit(2);
	}
}

if (!$quietInnerExplicit && $maxPasses >= 3) {
	$quietInner = true;
}

$pathsByCase = smoke_random_inner_case_paths();
if (!isset($pathsByCase[$caseNum])) {
	fwrite(STDERR, "--case must be 21–32 (got {$caseNum})\n");
	exit(2);
}

$spec = $pathsByCase[$caseNum];
if (!$memberOverride) {
	$member = $spec['file'];
}

if ($caseNum !== 30 && !$bytesExplicit) {
	$bytes = 0;
}

if ($exhaustiveSeries) {
	$maxRows = 0;
	fwrite(STDERR, "Note: --exhaustive-series is deprecated; use --max-rows=0 instead.\n");
}

if ($beamWidth === null) {
	$beamWidth = 0;
}
if ($exhaustive && $maxPasses >= 2) {
	if ($beamWidth > 0) {
		fwrite(STDERR, "Note: --exhaustive multi-pass uses full BFS (--beam-width ignored).\n");
	}
	$beamWidth = 0;
}

$beamForSearch = $beamWidth;
if (!$beamIgnoreMaxRowsCap && $beamWidth > 0 && $maxRows > 0) {
	$beamForSearch = min($beamWidth, $maxRows);
	if ($beamForSearch < $beamWidth) {
		fwrite(STDERR, "Note: effective search beam=min(beam,max-rows)={$beamForSearch} (--beam-width={$beamWidth} --max-rows={$maxRows}). Pass --beam-ignore-max-rows-cap to use full beam.\n");
	}
}

if ($greedyLadderMode !== null) {
	putenv('FZ_INNER_GREEDY_EMIT_LADDER=' . $greedyLadderMode);
}
if ($singleStructStep) {
	putenv('FZ_INNER_SINGLE_STRUCT_STEP=1');
}

$corpusDir = $repoRoot . DIRECTORY_SEPARATOR . $spec['dir'];
$corpusPath = $corpusDir . DIRECTORY_SEPARATOR . $member;

if ($caseNum === 30) {
	if (!$bytesExplicit) {
		$bytes = 1000;
	}
	$src29 = $repoRoot . DIRECTORY_SEPARATOR . 'test_files29' . DIRECTORY_SEPARATOR . 'showing_off.txt';
	if ($refreshCorpus || !is_file($corpusPath)) {
		if (!is_file($src29)) {
			fwrite(STDERR, "Missing source corpus: {$src29}\n");
			exit(1);
		}
		if (!is_dir($corpusDir) && !mkdir($corpusDir, 0777, true) && !is_dir($corpusDir)) {
			fwrite(STDERR, "Cannot create {$corpusDir}\n");
			exit(1);
		}
		$fh = fopen($src29, 'rb');
		if ($fh === false) {
			fwrite(STDERR, "Cannot read {$src29}\n");
			exit(1);
		}
		$chunk = fread($fh, $bytes);
		fclose($fh);
		if ($chunk === false || $chunk === '') {
			fwrite(STDERR, "Empty read from {$src29}\n");
			exit(1);
		}
		file_put_contents($corpusPath, $chunk);
	}
} else {
	if ($refreshCorpus) {
		fwrite(STDERR, "Note: --refresh-corpus applies only to --case=30; ignoring.\n");
	}
	if (!is_file($corpusPath)) {
		fwrite(STDERR, "Missing corpus file: {$corpusPath}\n");
		exit(1);
	}
	if ($bytesExplicit && $bytes > 0) {
		$fh = fopen($corpusPath, 'rb');
		if ($fh === false) {
			fwrite(STDERR, "Cannot read {$corpusPath}\n");
			exit(1);
		}
		$raw = fread($fh, $bytes);
		fclose($fh);
		if ($raw === false || $raw === '') {
			fwrite(STDERR, "Empty read from {$corpusPath}\n");
			exit(1);
		}
	} else {
		$raw = file_get_contents($corpusPath);
		if ($raw === false || $raw === '') {
			fwrite(STDERR, "Cannot read corpus member {$corpusPath}\n");
			exit(1);
		}
	}
}

if ($caseNum === 30) {
	$raw = file_get_contents($corpusPath);
	if ($raw === false || $raw === '') {
		fwrite(STDERR, "Cannot read corpus member {$corpusPath}\n");
		exit(1);
	}
}

putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'fractal_inner_passes.inc.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

if ($innerFrontierJobsCli !== null) {
	putenv('FZ_INNER_FRONTIER_JOBS=' . (string) $innerFrontierJobsCli);
} elseif ($innerFrontierJobsAutoOff) {
	putenv('FZ_INNER_FRONTIER_JOBS=1');
} elseif ($caseNum === 23 && $maxPasses >= 5) {
	$ef = getenv('FZ_INNER_FRONTIER_JOBS');
	if ($ef === false || trim((string) $ef) === '') {
		putenv('FZ_INNER_FRONTIER_JOBS=' . (string) min(8, smoke_random_cpu_count_best_effort()));
	}
}

if ($innerPieceJobsCli !== null) {
	putenv('FZ_INNER_PIECE_JOBS=' . (string) $innerPieceJobsCli);
} elseif ($innerPieceJobsAutoOff) {
	putenv('FZ_INNER_PIECE_JOBS=1');
} elseif ($caseNum === 23 && $maxPasses >= 5) {
	$ep = getenv('FZ_INNER_PIECE_JOBS');
	if ($ep === false || trim((string) $ep) === '') {
		putenv('FZ_INNER_PIECE_JOBS=' . (string) min(8, smoke_random_cpu_count_best_effort()));
	}
}

if ($caseNum === 23 && $maxPasses >= 5) {
	$epm = getenv('FZ_INNER_PIECE_FORK_MIN');
	if ($epm === false || trim((string) $epm) === '') {
		putenv('FZ_INNER_PIECE_FORK_MIN=128');
	}
	$efm = getenv('FZ_INNER_FRONTIER_FORK_MIN');
	if ($efm === false || trim((string) $efm) === '') {
		putenv('FZ_INNER_FRONTIER_FORK_MIN=16');
	}
}

if ($innerSearchDiskCacheCli === true) {
	putenv('FZ_INNER_PASS_SEARCH_DISK_CACHE=1');
} elseif ($innerSearchDiskCacheCli === false) {
	putenv('FZ_INNER_PASS_SEARCH_DISK_CACHE=0');
} elseif ($caseNum === 23 && $maxPasses >= 5) {
	putenv('FZ_INNER_PASS_SEARCH_DISK_CACHE=1');
}

if (!function_exists('fz_smoke_random_print_wall_shutdown')) {
	function fz_smoke_random_print_wall_shutdown(): void {
		static $done = false;
		if ($done) {
			return;
		}
		$owner = $GLOBALS['FZ_SMOKE_RANDOM_WALL_OWNER_PID'] ?? null;
		if ($owner !== null && $owner !== getmypid()) {
			return;
		}
		$done = true;
		$t0 = $GLOBALS['FZ_SMOKE_RANDOM_T0'] ?? null;
		if ($t0 === null) {
			return;
		}
		$sec = microtime(true) - $t0;
		fwrite(STDERR, sprintf($sec < 1.0 ? "wall_s=%.6f\n" : "wall_s=%.3f\n", $sec));
	}
}

if (!isset($GLOBALS['FZ_SMOKE_RANDOM_T0'])) {
	$GLOBALS['FZ_SMOKE_RANDOM_T0'] = microtime(true);
	$GLOBALS['FZ_SMOKE_RANDOM_WALL_OWNER_PID'] = getmypid();
	register_shutdown_function('fz_smoke_random_print_wall_shutdown');
}

$baselinePre = fz_inner_linear_fin_lin('', '', $raw);
$rawB = strlen($raw);
print('case=' . (string) $caseNum . "\n");
print("corpus=" . $corpusPath . "\n");
print("raw={$rawB} baseLin={$baselinePre} maxPass={$maxPasses} seg={$segment} rows=" . ($maxRows === 0 ? 'all' : (string) $maxRows) . "\n");
$beamOut = $beamWidth === 0 ? 'off' : (string) $beamForSearch;
if ($beamWidth > 0 && $beamForSearch < $beamWidth) {
	$beamOut .= ' (cli ' . (string) $beamWidth . ')';
}
print('ALL_SUBSTR=1 dedupe=' . ($dedupeFrontier ? '1' : '0') . ' beam=' . $beamOut);
print(' inner_wall_s=' . ($innerWallSeconds !== null ? (string) $innerWallSeconds : '-') . ' inner_wall_reset_mp=' . ($innerWallResetMultipass ? '1' : '0'));
$pjDisp = getenv('FZ_INNER_PIECE_JOBS');
print(' inner_piece_jobs=' . ($pjDisp !== false && trim((string) $pjDisp) !== '' ? trim((string) $pjDisp) : 'default'));
$fjDisp = getenv('FZ_INNER_FRONTIER_JOBS');
print(' inner_frontier_jobs=' . ($fjDisp !== false && trim((string) $fjDisp) !== '' ? trim((string) $fjDisp) : 'default'));
print(' quiet_inner=' . ($quietInner ? '1' : '0'));
print(' greedy_ladder=' . ($greedyLadderMode ?? '-') . ' single_struct_step=' . ($singleStructStep ? '1' : '0'));
print(' show_equiv=' . ($showEquiv ? '1' : '0'));
print(' equiv_chars=' . ($equivTableChars === null ? 'default' : (string) $equivTableChars));
print(' show_fractal_ref=' . ($showFractalRef ? '1' : '0'));
print(' fractal_ref_chars=' . ($fractalRefTableChars === null ? 'default' : (string) $fractalRefTableChars));
print(' show_replace_op=' . ($showReplaceOp ? '1' : '0'));
print(' prune_non_improving=' . ($pruneNonImproving ? '1' : '0'));
print(' highlight_golden=' . $highlightGolden);
print(" prvLen={$previewChars} piecesColMax={$piecesMaxWidth}\n");
print("finLin=len(reference)+len(equiv) (fz_inner_reference_corpus(fractal_ref,dict); fz_inner_linear_fin_lin)\n");

if ($maxPasses === 0) {
	print("\nmax_passes=0: no replace enumeration.\n");
	exit(0);
}

$fz = new fractal_zip($segment, false, false, null, false);
$goldenLadderEquivs = smoke_random_golden_equivs_for_case($caseNum);

/*
 * TEMPORARY (smoke_random): disable `# heuristic_*` ladder diagnostics (`--greedy-ladder` is a no-op for printed chains).

if ($greedyLadderMode !== null && !fz_inner_exceeds_inner_soft_byte_cap($raw)) {
	smoke_random_maybe_print_golden_ladder_legend($caseNum);
	if ($greedyLadderMode === 'substr') {
		$tr = fz_inner_substring_intro_greedy_equiv_trace($fz, '', '', $raw, $raw);
		$chain = array_merge(array($raw), $tr);
		print("\n# heuristic_substr_chain (" . (string) count($chain) . " equivs, pipe-separated)\n");
		print(smoke_random_pipe_mark_golden($chain, $goldenLadderEquivs) . "\n");
	} elseif ($greedyLadderMode === 'substr_mild') {
		$tr = fz_inner_substring_intro_mild_equiv_trace($fz, '', '', $raw, $raw);
		$chain = array_merge(array($raw), $tr);
		print("\n# heuristic_substr_mild_chain (" . (string) count($chain) . " equivs, pipe-separated)\n");
		print(smoke_random_pipe_mark_golden($chain, $goldenLadderEquivs) . "\n");
	} else {
		$iv = fz_inner_interleaved_substr_scale_equiv_trace($fz, '', '', $raw, $raw);
		$chain = array($raw);
		foreach ($iv as $step) {
			$chain[] = $step['equiv'];
		}
		print("\n# heuristic_interleaved_substr_scale (" . (string) count($chain) . " equivs, pipe-separated)\n");
		print(smoke_random_pipe_mark_golden($chain, $goldenLadderEquivs) . "\n");
	}
}

 */
$prevRawPrefix = getenv('FZ_INNER_VERIFY_RAW_PREFIX');
$verifyRawPrefix = ($caseNum === 30);
if ($verifyRawPrefix) {
	putenv('FZ_INNER_VERIFY_RAW_PREFIX=1');
}

$prevVerifiedHints = getenv('FZ_INNER_VERIFIED_ENCODING_HINTS');
$prevVerifiedHintFiles = getenv('FZ_INNER_VERIFIED_ENCODING_HINT_FILES');
$prevProcessLadderVerify = getenv('FZ_INNER_PROCESS_LADDER_VERIFY');
if (count($verifiedHintFiles) > 0) {
	putenv('FZ_INNER_VERIFIED_ENCODING_HINTS=1');
	putenv('FZ_INNER_PROCESS_LADDER_VERIFY=1');
	putenv('FZ_INNER_VERIFIED_ENCODING_HINT_FILES=' . implode(',', $verifiedHintFiles));
}

$prevInnerChainStr = getenv('FZ_INNER_PASS_SEARCH_CHAIN_SUBSTR');
$prevInnerPiecesStr = getenv('FZ_INNER_PASS_SEARCH_PIECES_SUBSTR');
$prevInnerChainLim = getenv('FZ_INNER_PASS_SEARCH_CHAIN_MATCH_LIMIT');
$sidecarMatchCli = ($matchChainSubstr !== null && $matchChainSubstr !== '')
	|| ($matchPiecesSubstr !== null && $matchPiecesSubstr !== '');
if ($sidecarMatchCli) {
	$effSidecarStop = $stopAfterChainMatchesCli !== null ? $stopAfterChainMatchesCli : 1;
	putenv('FZ_INNER_PASS_SEARCH_CHAIN_MATCH_LIMIT=' . ($effSidecarStop > 0 ? (string) $effSidecarStop : ''));
}
if ($matchChainSubstr !== null && $matchChainSubstr !== '') {
	putenv('FZ_INNER_PASS_SEARCH_CHAIN_SUBSTR=' . $matchChainSubstr);
}
if ($matchPiecesSubstr !== null && $matchPiecesSubstr !== '') {
	putenv('FZ_INNER_PASS_SEARCH_PIECES_SUBSTR=' . $matchPiecesSubstr);
}

$sr = fz_inner_pass_search(
	$fz,
	$raw,
	$maxPasses,
	$beamForSearch,
	$dedupeFrontier,
	$frontierHardCap,
	$memoryLimitCli,
	$quietInner,
	false,
	false,
	$innerWallSeconds,
	$innerWallResetMultipass
);
if ($verifyRawPrefix) {
	if ($prevRawPrefix === false) {
		putenv('FZ_INNER_VERIFY_RAW_PREFIX');
	} else {
		putenv('FZ_INNER_VERIFY_RAW_PREFIX=' . $prevRawPrefix);
	}
}
if (count($verifiedHintFiles) > 0) {
	if ($prevVerifiedHints === false) {
		putenv('FZ_INNER_VERIFIED_ENCODING_HINTS');
	} else {
		putenv('FZ_INNER_VERIFIED_ENCODING_HINTS=' . $prevVerifiedHints);
	}
	if ($prevVerifiedHintFiles === false) {
		putenv('FZ_INNER_VERIFIED_ENCODING_HINT_FILES');
	} else {
		putenv('FZ_INNER_VERIFIED_ENCODING_HINT_FILES=' . $prevVerifiedHintFiles);
	}
	if ($prevProcessLadderVerify === false) {
		putenv('FZ_INNER_PROCESS_LADDER_VERIFY');
	} else {
		putenv('FZ_INNER_PROCESS_LADDER_VERIFY=' . $prevProcessLadderVerify);
	}
}
if ($matchChainSubstr !== null && $matchChainSubstr !== '') {
	if ($prevInnerChainStr !== false) {
		putenv('FZ_INNER_PASS_SEARCH_CHAIN_SUBSTR=' . $prevInnerChainStr);
	} else {
		putenv('FZ_INNER_PASS_SEARCH_CHAIN_SUBSTR');
	}
}
if ($matchPiecesSubstr !== null && $matchPiecesSubstr !== '') {
	if ($prevInnerPiecesStr !== false) {
		putenv('FZ_INNER_PASS_SEARCH_PIECES_SUBSTR=' . $prevInnerPiecesStr);
	} else {
		putenv('FZ_INNER_PASS_SEARCH_PIECES_SUBSTR');
	}
}
if ($sidecarMatchCli) {
	if ($prevInnerChainLim !== false) {
		putenv('FZ_INNER_PASS_SEARCH_CHAIN_MATCH_LIMIT=' . $prevInnerChainLim);
	} else {
		putenv('FZ_INNER_PASS_SEARCH_CHAIN_MATCH_LIMIT');
	}
}
$baseline = $sr['baseLin'];
$reportRows = $sr['rows'];
$tableNote = $sr['tableNote'];
if (($sr['innerDiskCacheChainSqliteHit'] ?? false) || ($sr['innerDiskCacheChainStreamHit'] ?? false)) {
	$nChainSql = (int) ($sr['innerDiskCacheChainSqliteMatchCount'] ?? 0);
	$ck = (string) ($sr['innerDiskCacheChainKind'] ?? '?');
	fwrite(STDERR, "smoke_random: disk_sidecar kind={$ck} printed_rows={$nChainSql} (aggregate finLin stats still describe the full multipass union)\n");
}
$candidate_pieces1 = $sr['candidate_pieces1'];
$scores_pass1 = $sr['scores_pass1'];
$innerSoftByteCapped = $sr['innerSoftByteCapped'] ?? false;

print('pass1_candidate_count=' . $sr['pass1CandCount'] . "\n");
print('inner_soft_byte_capped=' . ($innerSoftByteCapped ? '1' : '0') . "\n");
print('inner_wall_timed_out=' . (($sr['innerWallTimedOut'] ?? false) ? '1' : '0') . "\n");
print('inner_disk_cache_hit=' . (($sr['innerDiskCacheHit'] ?? false) ? '1' : '0') . "\n");
print('inner_disk_cache_chain_hit=' . ((($sr['innerDiskCacheChainSqliteHit'] ?? false) || ($sr['innerDiskCacheChainStreamHit'] ?? false)) ? '1' : '0') . "\n");
$kindJs = bench_json_encode_try($sr['innerDiskCacheChainKind'] ?? null, false, JSON_UNESCAPED_UNICODE);
$noteJs = bench_json_encode_try($tableNote, false, JSON_UNESCAPED_UNICODE);
if ($kindJs === null || $noteJs === null) {
	fwrite(STDERR, 'smoke_random: json_encode failed (inner_disk_cache_chain_kind / inner_table_note): ' . json_last_error_msg() . "\n");
	exit(1);
}
print('inner_disk_cache_chain_kind=' . $kindJs . "\n");
print('inner_table_note=' . $noteJs . "\n");

if ($sr['pass1CandCount'] === 0 && count($reportRows) === 0) {
	if ($innerSoftByteCapped) {
		print("Inner soft byte cap active (FZ_INNER_MAX_PROCESS_RAW_BYTES, default 1 MiB); substring enumeration skipped — raise limit or shorten corpus.\n");
	} else {
		print("No duplicate fractally-clean substrings; nothing to sample.\n");
	}
	exit(0);
}

if ($pruneNonImproving && $reportRows !== null && count($reportRows) > 0) {
	$nBeforePrune = count($reportRows);
	$reportRows = array_values(array_filter($reportRows, static function (array $r) use ($baseline): bool {
		return fz_row_final_lin($r) < $baseline;
	}));
	$nDropped = $nBeforePrune - count($reportRows);
	if ($nDropped > 0) {
		fwrite(STDERR, "pruned_non_improving={$nDropped} (finLin>=baseLin={$baseline})\n");
	}
}

if ($reportRows !== null) {
	$nSer = count($reportRows);
	if ($nSer > 0) {
		$rfs = $sr['rowFinalLinStats'] ?? null;
		$usePreAgg = !$pruneNonImproving
			&& is_array($rfs)
			&& isset($rfs['count'], $rfs['min'], $rfs['max'], $rfs['mean'])
			&& (int) $rfs['count'] === $nSer;
		if ($usePreAgg) {
			$fMin = (int) $rfs['min'];
			$fMax = (int) $rfs['max'];
			$fMean = (float) $rfs['mean'];
		} else {
			$finals = array();
			foreach ($reportRows as $row) {
				$ln = $row['linears'];
				$finals[] = $ln[count($ln) - 1];
			}
			$fMin = min($finals);
			$fMax = max($finals);
			$fMean = array_sum($finals) / count($finals);
		}
		print("\nrows={$nSer} ({$tableNote})\n");
		print('finLin min=' . $fMin . ' max=' . $fMax . ' mean=' . sprintf('%.2f', $fMean) . "\n");
	} else {
		$emptyMsg = ($maxPasses === 1) ? 'no successful pass-1 replaces' : 'no encodings in search result';
		print("\nrows=0 ({$emptyMsg})\n");
	}

	$lim2 = ($maxRows === 0) ? $nSer : min($maxRows, $nSer);
	$print_rows = ($nSer === 0 || $lim2 === $nSer) ? $reportRows : array_slice($reportRows, 0, $lim2);

	$tablePreviewLim = static function (string $s, ?int $tableChars, int $previewChars): int {
		if ($tableChars === null) {
			return max(48, $previewChars);
		}
		if ($tableChars === 0) {
			return max(8, strlen($s));
		}
		return max(8, $tableChars);
	};
	if (count($print_rows) > 0) {
		print("\n");
		$headers = array('fin', 'eff', 'repl', 'occ', 'chain', 'pieces');
		$rightAlign = array(true, true, true, true, false, false);
		if ($showReplaceOp) {
			$headers[] = 'substr_op';
			$headers[] = 'corpus_slice';
			$rightAlign[] = false;
			$rightAlign[] = false;
		}
		if ($showFractalRef) {
			$headers[] = 'reference';
			$rightAlign[] = false;
		}
		if ($showEquiv) {
			$headers[] = 'equiv';
			$rightAlign[] = false;
		}
		$tableRows = array();
		$goldenMask = array();
		$goldenEquivLookup = array();
		foreach ($goldenLadderEquivs as $gex) {
			$goldenEquivLookup[(string) $gex] = true;
		}
		foreach ($print_rows as $row) {
			$ln = $row['linears'];
			$fin = $ln[count($ln) - 1];
			$eff = fz_row_efficiency($row, $fz);
			$repl = count($row['pieces']);
			$chain = fz_chain_compact($ln);
			$piecesForCol = fz_row_pieces_column_literals($row);
			$pcol = fz_pieces_column($piecesForCol, $previewChars, $piecesMaxWidth);
			$cells = array(
				(string) $fin,
				sprintf('%.4f', $eff),
				(string) $repl,
				(string) $row['last_occ'],
				$chain,
				$pcol,
			);
			if ($showReplaceOp) {
				list($opShow, $sliceRaw) = smoke_random_append_replace_op_slice($fz, $row);
				if ($opShow === '-') {
					$cells[] = '-';
					$cells[] = '-';
				} else {
					$slLim = $tablePreviewLim($sliceRaw, $equivTableChars, $previewChars);
					$cells[] = $opShow;
					$cells[] = fz_preview_piece($sliceRaw, $slLim);
				}
			}
			if ($showFractalRef) {
				$refLine = fz_inner_row_reference_line($row);
				$refLim = $tablePreviewLim($refLine, $fractalRefTableChars, $previewChars);
				$cells[] = fz_preview_piece($refLine, $refLim);
			}
			if ($showEquiv) {
				$eq = $row['equiv'];
				$eqLim = $tablePreviewLim($eq, $equivTableChars, $previewChars);
				$cells[] = fz_preview_piece($eq, $eqLim);
			}
			$tableRows[] = $cells;
			$goldenMask[] = isset($goldenEquivLookup[(string) ($row['equiv'] ?? '')]);
		}
		$useGoldAnsi = $highlightGolden !== 'never'
			&& $goldenLadderEquivs !== array()
			&& smoke_random_stdout_color_enabled($highlightGolden);
		if ($useGoldAnsi) {
			$goldenHits = 0;
			foreach ($goldenMask as $gm) {
				if ($gm) {
					$goldenHits++;
				}
			}
			if ($goldenHits > 0) {
				fwrite(STDERR, "golden_row_table_hits={$goldenHits} (full equiv ∈ smoke_random_golden_ladder_equivs_by_case()[{$caseNum}])\n");
			}
		}
		smoke_random_print_aligned_table($headers, $tableRows, $rightAlign, '  ', $goldenMask, $useGoldAnsi);
	}
	if ($maxRows > 0 && $nSer > $lim2) {
		fwrite(STDERR, "table: {$lim2}/{$nSer} rows (--max-rows={$maxRows}; 0=all)\n");
	}
}

if ($maxPasses === 1 && $seed !== null) {
	mt_srand($seed);
}

if ($maxPasses === 1 && count($candidate_pieces1) > 0) {
	$trial_scores = array();
	for ($t = 0; $t < $trials; $t++) {
		$idx = mt_rand(0, count($candidate_pieces1) - 1);
		$trial_scores[] = $scores_pass1[$idx];
	}
	sort($trial_scores, SORT_NUMERIC);
	$tMin = $trial_scores[0];
	$tMax = $trial_scores[$trials - 1];
	$tMean = array_sum($trial_scores) / $trials;
	$p50 = $trial_scores[(int) floor(($trials - 1) * 0.5)];
	$p90 = $trial_scores[(int) floor(($trials - 1) * 0.90)];

	print("\nrandom_trials={$trials} sample_lin min={$tMin} max={$tMax} mean=" . sprintf('%.2f', $tMean) . " p50={$p50} p90={$p90}\n");
	if ($seed !== null) {
		print('rng_seed=' . $seed . "\n");
	}
} elseif ($maxPasses >= 2) {
	fwrite(STDERR, "Note: random --trials sampling applies only when --max-passes=1.\n");
}
