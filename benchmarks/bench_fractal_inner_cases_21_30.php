#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Inner linear benchmark for fractal_zip "true fractal" corpora (cases 21–32).
 *
 * Measures minimum strlen(dict)+strlen(equiv) achievable by the same append+replace
 * pass model as smoke_random.php (see benchmarks/include/fractal_inner_passes.inc.php).
 *
 * For cases 21–28, compares against "recipe" size strlen(equiv_spec)+strlen(fractal_spec)
 * from do_fractal_zip.php create_fractal_file() call sites (the compact generator).
 *
 * Case 29: test_files29/showing_off.txt (large; use --prefix-bytes to cap read time).
 * Case 23: explicit recursion ordinal appears only on the **outer** substring op in the recipe equiv (`a<12"17"4>aaaa`); inner operators along the ladder omit `rec`—implicit level **x−y** (outer **x**, step **y** from the outside).
 * Case 30: test_files30/test_files2.txt (smoke slice) — vsRecipe uses case 29 recipe linear strlen(equiv_29)+strlen(fractal_29). Raw is a prefix of that expansion; this bench enables FZ_INNER_VERIFY_RAW_PREFIX for case 30 so inner rows verify via prefix match while still using append+replace search when --no-recipe-oracle is set.
 * Beam vs wall: under a fixed --inner-wall-s, a much wider beam can score worse (more work per depth → deadline mid-search); e.g. beam 96 + 350s wall timed out around finLin 226 while beam 64 + 300s still reached ~198. Multipass beam-width=0 (exact BFS) can also wall out earlier at higher finLin when breadth dominates the budget. [inner wall-timeout] on a row can mean the clock ran out after a good beam-global-best was already found.
 *
 * Rows with raw larger than FZ_INNER_MAX_PROCESS_RAW_BYTES (default 1 MiB) append “[FZ_INNER soft-cap]” to the note column; raise the env var or use --prefix-bytes for a full inner search on huge members.
 * When the case definition’s generator equiv+fractal expands to the same raw (verified once), the bench may record that encoding as the best row if it beats the inner search (see test_files24/29 under soft-cap).
 *
 * Quick inner regression (pass 1): php benchmarks/smoke_inner_pass_regression.php [--full] [--verbose] (tests/run_php_smokes.sh phases 5–6: inner regression + smoke_random --case=23 + case-23 zip assert)
 * Disk cache / chain+pieces sidecar env vars for fz_inner_pass_search(): see `--help` section “Environment (disk cache / multipass row sidecar)”.
 * Recursive substring decode vs disk: php benchmarks/smoke_recursive_substring_roundtrip.php
 *
 * Usage (repo root):
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --help
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --cases=21-25 --materialize
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --max-passes=2 --beam-width=64 --prefix-bytes=8000
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --no-recipe-oracle   # pure inner rows only (no generator/prefix oracles)
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --cases=30 --no-recipe-oracle --max-passes=5 --beam-width=8 --no-final   # append-only generator-style path (~188 finLin)
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --cases=30 --max-passes=1 --beam-width=0   # recipe/prefix oracle may record the compact generator encoding
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --cases=30 --no-recipe-oracle --max-passes=6 --beam-width=64 --inner-wall-s=420 --inner-wall-reset-multipass   # wider append-only headroom
 *   php benchmarks/bench_fractal_inner_cases_21_30.php --cases=29 --prefix-bytes=4096 --no-recipe-oracle --max-passes=2 --beam-width=32 --inner-wall-s=120 --inner-wall-reset-multipass   # large raw; raise wall/beam as needed (minutes-scale)
 */

/**
 * @return list<array{
 *   case: int,
 *   dir: string,
 *   file: string,
 *   equiv: string|null,
 *   fractal: string|null,
 *   note: string
 * }>
 */
function bench_fractal_inner_cases_definitions(): array {
	return array(
		array(
			'case' => 21,
			'dir' => 'test_files21',
			'file' => '2Dfractal.txt',
			'equiv' => '<l9><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35>',
			'fractal' => 'aaa<s15>aaa<s15>aaa<s15>aaa<s15>aaabbb<s15>bbb<s15>bbb<s15>bbb<s15>bbbccc<s15>ccc<s15>ccc<s15>ccc<s15>ccc',
			'note' => '2D grid / skip',
		),
		array(
			'case' => 22,
			'dir' => 'test_files22',
			'file' => '2Dfractal.txt',
			'equiv' => '<l9><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35><0"35><0"35><35"35><0"35><35"35><70"35><35"35><70"35><70"35>',
			'fractal' => 'aaa<s15>aaa<s15>aaa<s15>aaa<s15>aaabbb<s15>bbb<s15>bbb<s15>bbb<s15>bbbccc<s15>ccc<s15>ccc<s15>ccc<s15>ccc',
			'note' => '2D fractal extended',
		),
		array(
			'case' => 23,
			'dir' => 'test_files23',
			'file' => 'potentially_infinite_string.txt',
			'equiv' => 'a<12"17"4>aaaa',
			'fractal' => 'a<12"17>aaaabb<0"12>b<0"12>bb',
			'note' => 'recursive substring depth (explicit rec only on outer op; intermediates implicit x−y from outer x, step y from outside)',
		),
		array(
			'case' => 24,
			'dir' => 'test_files24',
			'file' => 'dehacking_fractal_substring.txt',
			'equiv' => 'aaaaa<20"25"30>aaaaaaaa',
			'fractal' => 'aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb',
			'note' => 'tuple + recursion',
		),
		array(
			'case' => 25,
			'dir' => 'test_files25',
			'file' => 'replace_operation.txt',
			'equiv' => '<0"25><rb"c><0"25></r><rb"d><0"25></r><rb"e><0"25></r><rb"f><0"25></r><rb"g><0"25></r>',
			'fractal' => 'aaaaaaaaaaaaaaaaaaaabbbaa',
			'note' => 'spanning replace ops',
		),
		array(
			'case' => 26,
			'dir' => 'test_files26',
			'file' => 'gradient_operation.txt',
			'equiv' => '<g4"1"w>',
			'fractal' => '',
			'note' => 'gradient',
		),
		array(
			'case' => 27,
			'dir' => 'test_files27',
			'file' => 'tuple_operation.txt',
			'equiv' => '<0"12*12>',
			'fractal' => 'abcdefghijkl',
			'note' => 'tuple repeat',
		),
		array(
			'case' => 28,
			'dir' => 'test_files28',
			'file' => 'scale_operation.txt',
			'equiv' => '<0"12s0.25><0"12s0.5><0"12s2><0"12s8>',
			'fractal' => 'aaabbccbbaaa',
			'note' => 'substring scale',
		),
		array(
			'case' => 29,
			'dir' => 'test_files29',
			'file' => 'showing_off.txt',
			'equiv' => 'aaaaa<20"25"30>aaaaaaaa',
			'fractal' => 'aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb',
			'note' => 'on-disk corpus (recipe is reference only if file was rebuilt differently)',
		),
		array(
			'case' => 30,
			'dir' => 'test_files30',
			'file' => 'test_files2.txt',
			'equiv' => null,
			'fractal' => null,
			'note' => 'smoke slice; vsRecipe = equiv_29+fractal_29 (inner: fractal_ref empty → dict holds template)',
		),
		array(
			'case' => 31,
			'dir' => 'test_files31',
			'file' => 'multifractal.txt',
			'equiv' => null,
			'fractal' => null,
			'note' => 'multifractal run grammar',
		),
		array(
			'case' => 32,
			'dir' => 'test_files32',
			'file' => 'harder_multifractal.txt',
			'equiv' => null,
			'fractal' => null,
			'note' => 'harder multifractal run grammar',
		),
	);
}

function bench_fractal_inner_print_help(string $argv0): void {
	$bin = $argv0 !== '' ? $argv0 : 'benchmarks/bench_fractal_inner_cases_21_30.php';
	$msg = <<<HELP
Fractal inner benchmark (cases 21–32): best strlen(dict)+strlen(equiv) vs generator recipe size.

Usage:
  php {$bin} [options]

Options:
  --help, -h
  --cases=A-B or --cases=21,22,30 or --cases=21-28,30   Subset (default 21-30; 31-32 explicit).
  --only=A-B or --only=30                               Alias for --cases=… (shorthand).
  --recursive-sub150-top=N   After case 30, print up to N best improving rows with finLin<150 whose equiv or dict contains an explicit substring recursion field (<off\"len\"rec…>, rec nonempty). Row set depends on --full-pass1 / --max-passes / --beam-width; widen search if fewer than N appear.
  --materialize           Run create_fractal_file() for cases 21–28 when missing or with --force.
  --force                 Overwrite existing generated files when used with --materialize.
  --prefix-bytes=N        Read only first N bytes of each member (default: full file; use on case 29).
  --segment=N             fractal_zip segment length (default 64).
  --max-passes=N          Inner search depth (default 2).
  --beam-width=N          Beam prune (default 0 = exact BFS for multipass). Width 28+ reserves a few slots for distinct dict+equiv layouts (see fz_beam_prune_frontier). Multipass beam retains best improving row across depths if later frontier is empty.
  --no-dedupe             Disable frontier merge by dict+equiv.
  --frontier-cap=N        Abort threshold (default 5000000).
  --memory-limit=L        php memory_limit for deep passes.
  --verbose               Print inner-search progress to stderr, plus per-case fz_inner_pass_search tableNote (default: off for clean TSV on stdout).
  --no-final              Do not print the best inner row (dict/equiv sizes, chain, piece) after each case.
  --full-pass1            Full pass-1 enumeration (smoke-compatible); default uses minimal memory on pass 1.
  --no-recipe-oracle      Skip verified-generator and case-30 prefix oracles; report pure fz_inner_pass_search best rows only.
  --inner-wall-s=N        Wall-clock seconds for inner search from post-enumeration configure (with multipass, polls inside each frontier expansion — budget is one continuous window).
  --inner-wall-reset-multipass  With --inner-wall-s and max-passes>=2, restart the deadline when multipass frontier expansion begins (extra budget after pass-1 enumeration).

Notes (multipass + wall): Wider --beam-width expands more states per depth and may hit the wall before reaching as low a finLin as a moderate beam. beam-width=0 exact BFS can wall out on breadth; frontier regression early-stop applies only when beam-width>0. Case 30 enables prefix verify in this bench, but --no-recipe-oracle reports only inner append+replace rows. A trailing “[inner wall-timeout]” note does not imply the reported best row is invalid — search may stop after recording beam-global-best.

Programmatic: fz_inner_pass_search() returns innerSoftByteCapped (bool) when strlen(raw) exceeds FZ_INNER_MAX_PROCESS_RAW_BYTES at entry.

Environment (disk cache / multipass row sidecar):
  FZ_INNER_PASS_SEARCH_DISK_CACHE           Set 1/true/yes to persist fz_inner_pass_search payloads under sys_get_temp_dir() (gzip + tagged serialize; see fz_inner_pass_search docblock).
  FZ_INNER_PASS_SEARCH_CHAIN_SUBSTR         Before fz_inner_pass_search: non-empty trim skips loading the full `.fzic` blob when `{cache}.chainidx.meta` exists (literal match on compact numeric chain).
  FZ_INNER_PASS_SEARCH_PIECES_SUBSTR        Same for joined append needles (`implode('|', pieces)`). Typical for scripted grep-style lookups on huge unions.
  FZ_INNER_PASS_SEARCH_CHAIN_MATCH_LIMIT    Optional positive integer cap on sidecar rows returned (empty/unset = unlimited).

Environment (inner search limits; see fractal_inner_passes.inc.php):
  FZ_INNER_MAX_FRACTALLY_VERIFY_CALLS       Max fractally_process_string verifications per fz_inner_pass_search (default 65536; 0 = unlimited). Duplicate (equiv,corpus,raw) checks are memoized and skip the budget.
  FZ_INNER_MAX_PROCESS_EQUIV_BYTES          Skip verify if strlen(equiv) exceeds (unset = unlimited; 0 = unlimited).
  FZ_INNER_MAX_PROCESS_CORPUS_BYTES         Skip verify if reference corpus exceeds (unset = unlimited).
  FZ_INNER_MAX_PROCESS_RAW_BYTES            Soft cap (default 1 MiB; 0 = unlimited): skip fractally verify, heavy scans, and all_substrings_count on larger strings (use 0 + --memory-limit only if you accept high RAM use).
  FZ_INNER_MAX_SUBSTR_SCALE_INTRO_ATTEMPTS_PER_PAIR  Cap literal match tries per span/expansion pair for substring/scale intros (default 384; 0 = unlimited).
  FZ_INNER_MAX_SUBSTR_INTRO_RECURSION           Max recursion depth scanned in substring/scale intro maps and recursion-adjust (default 48 when unset; cap 192; small explicit values honored).
  FZ_INNER_MIN_EXPLICIT_SUBSTR_REC             Lowest nonempty recursion index tried (default 1); use with MAX=3 e.g. MIN=2 for only `<…\"…\"2>`…`<…\"…\"3>` in generated substring intros.
  FZ_INNER_APPEND_disjoint_SLICE_SORT          Multipass: prefer append candidates whose match in fractal_ref.dict is disjoint from the previous step's slice (iteration order only).
  FZ_INNER_APPEND_EXPAND_MAX_TRIES_PER_STATE Override append trials per frontier row (≤0 = unlimited). Unset + beam uses tuned raw-size tiers: ≤768 B → 192; >768 B → 32 for depths 1–2 then 192.
  FZ_INNER_SUBSTR_MAP_PROBE_MAX_BYTES       During substring/scale intro map construction only: cap FRACTAL_ZIP splice/tuple expansion per fractally_process_string probe (default 8388608; min 262144 when set positive; 0 = use global FRACTAL_ZIP_* limits).
  FZ_INNER_SUBSTR_MAP_PROBE_MAX_RUNNING_BYTES  During those probes only: abort recursive substring expansion once the live equivalence buffer exceeds this (default 98304, aligned with intro-map expansion keys; 0 = unlimited).
  FZ_INNER_INTRO_MAP_OUTER_CACHE_MAX_KEYS    Cap cached intro expansion maps per fz_inner_pass_search by distinct corpora (default 96; 0 = unlimited — deep multipass can OOM). Oldest entries drop first.
  FZ_INNER_REC_FIELD_INTRO                Set to 0 to disable `<rec-field-intro>` / `<dict-rec-field-intro>` (attach recursion depth to bare substring markers).
  FZ_INNER_REC_FIELD_INTRO_LINEAR_SLACK    Extra strlen(dict)+strlen(equiv) allowed for those moves (default 32; 0 = strict improvement only).
  FZ_INNER_REC_FIELD_INTRO_MAX_VARIANTS    Max variants generated per expand step (default 512; 0 = unlimited).
  FZ_INNER_VERIFY_RAW_PREFIX             Case 30 (this bench): accept expansions longer than raw when the first strlen(raw) bytes match (default off globally; bench forces on for case 30 only).
  FZ_INNER_VERIFIED_ENCODING_HINTS       Emit `<verified-encoding-hint>` structural successors from a tiny static generator list when fz_inner_equiv_matches_raw passes (default on; set 0 to disable).
  FZ_INNER_MAX_GRADIENT_LITERAL_BYTES       Skip gradient sliding scan when a literal span exceeds this (default 65536; 0 = unlimited).
  FZ_INNER_MAX_DICT_BYTES_FOR_STRUCTURAL   Skip dict tuple/substr/scale/gradient intros when strlen(dict) exceeds this (default 32768; 0 = unlimited).
  FZ_INNER_MAX_DICT_SUFFIX_TRIM            Max suffix-trim attempts per dict state (default 96).
  FZ_INNER_MAX_FRACTAL_FOR_SUBSTR_MAP      Cap fractal length when building substring intro expansion maps.
  FZ_INNER_MAX_SUBSTR_INTRO_L              Cap unit length L for equiv substring intro scan.
  FZ_INNER_MAX_FRACTAL_FOR_SCALE_MAP       Cap fractal length for scale intro maps.
  FZ_INNER_MAX_SCALE_INTRO_L               Cap L for scale intro scan.
  FZ_INNER_MAX_SCALE_GREEDY_STEPS         Max repeated scale-intro passes for <scale-intro*> (default 64).
  FZ_INNER_MAX_SUBSTR_GREEDY_STEPS        Max repeated substring-intro passes for <substr-intro*> (default 64).
  FZ_INNER_MAX_ALT_GREEDY_ROUNDS          Equiv-only: alternate <substr-intro*> then <scale-intro*> rounds (default 12; 0 = off).
  FZ_INNER_MAX_DICT_ALT_GREEDY_ROUNDS     Dict-side: alternate dict <*-substr*> and dict <*-scale*> greedy rounds (default 8; 0 = off).
  FZ_INNER_SINGLE_STRUCT_STEP             Skip greedy `<*-intro*>` fixpoints and `FZ_INNER_GREEDY_EMIT_LADDER` traces; one literal→marker intro per structural successor (smoke: `--single-struct-step`).

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
			bench_fractal_inner_print_help((string) ($argv[0] ?? ''));
			exit(0);
		}
	}
}

$casesFilter = null;
$materialize = false;
$force = false;
$prefixBytes = null;
$segment = 64;
$maxPasses = 2;
$beamWidth = 0;
$dedupeFrontier = true;
$frontierHardCap = 5000000;
$memoryLimitCli = null;
$verbose = false;
$showFinal = true;
$fullPass1 = false;
$recipeOracle = true;
$innerWallSeconds = null;
$innerWallResetMultipass = false;
$recursiveSub150Top = 0;

for ($i = 1, $n = isset($argv) ? count($argv) : 0; $i < $n; $i++) {
	$a = $argv[$i];
	if (preg_match('/^--cases=([\d,\-]+)$/i', $a, $m)) {
		$casesFilter = $m[1];
	} elseif (preg_match('/^--only=([\d,\-]+)$/i', $a, $m)) {
		$casesFilter = $m[1];
	} elseif ($a === '--materialize') {
		$materialize = true;
	} elseif ($a === '--force') {
		$force = true;
	} elseif ($a === '--verbose') {
		$verbose = true;
	} elseif ($a === '--no-final') {
		$showFinal = false;
	} elseif ($a === '--full-pass1') {
		$fullPass1 = true;
	} elseif ($a === '--no-recipe-oracle') {
		$recipeOracle = false;
	} elseif (preg_match('/^--inner-wall-s=([0-9]+(?:\.[0-9]+)?)$/', $a, $m)) {
		$innerWallSeconds = max(0.001, (float) $m[1]);
	} elseif ($a === '--inner-wall-reset-multipass') {
		$innerWallResetMultipass = true;
	} elseif (preg_match('/^--prefix-bytes=(\d+)$/', $a, $m)) {
		$prefixBytes = max(1, (int) $m[1]);
	} elseif (preg_match('/^--segment=(\d+)$/', $a, $m)) {
		$segment = max(8, (int) $m[1]);
	} elseif (preg_match('/^--max-passes=(\d+)$/', $a, $m)) {
		$maxPasses = min(30, max(0, (int) $m[1]));
	} elseif (preg_match('/^--beam-width=(\d+)$/', $a, $m)) {
		$beamWidth = max(0, (int) $m[1]);
	} elseif ($a === '--no-dedupe') {
		$dedupeFrontier = false;
	} elseif (preg_match('/^--frontier-cap=(\d+)$/', $a, $m)) {
		$frontierHardCap = max(10000, (int) $m[1]);
	} elseif (preg_match('/^--memory-limit=(.+)$/', $a, $m)) {
		$memoryLimitCli = trim($m[1]);
	} elseif (preg_match('/^--recursive-sub150-top=(\d+)$/', $a, $m)) {
		$recursiveSub150Top = max(0, (int) $m[1]);
	} else {
		fwrite(STDERR, "Unknown argument: {$a}\n");
		exit(2);
	}
}

putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'fractal_inner_passes.inc.php';

/** True if equiv or dict contains a substring marker with nonempty recursion depth field (<off"len"rec…>). */
function bench_fractal_inner_encoding_has_recursion_depth_field(string $equiv, string $dict): bool {
	list($Lq, $Mq, $Rq) = fractal_zip_marker_rx_quoted_delimiters();
	$frag = $Lq . '[0-9]+' . $Mq . '[0-9]+' . $Mq . '[0-9]+';
	$pat = '/' . $frag . '/';
	return preg_match($pat, $equiv) === 1 || preg_match($pat, $dict) === 1;
}

$defs = bench_fractal_inner_cases_definitions();
/** Case 29 generator strings (used for recipeLin on case 30 and prefix-oracle check). */
$recipeCase29 = null;
$recipeLinFromCase29 = null;
foreach ($defs as $d) {
	if ($d['case'] === 29 && $d['equiv'] !== null && $d['fractal'] !== null) {
		$recipeCase29 = array(
			'equiv' => (string) $d['equiv'],
			'fractal' => (string) $d['fractal'],
		);
		$recipeLinFromCase29 = strlen($recipeCase29['equiv']) + strlen($recipeCase29['fractal']);
		break;
	}
}
$selected = array();
if ($casesFilter === null) {
	foreach ($defs as $d) {
		if ($d['case'] <= 30) {
			$selected[] = $d;
		}
	}
} else {
	$want = array();
	foreach (explode(',', $casesFilter) as $tok) {
		$tok = trim($tok);
		if ($tok === '') {
			continue;
		}
		if (strpos($tok, '-') !== false) {
			$parts = explode('-', $tok, 2);
			if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
				$a = (int) $parts[0];
				$b = (int) $parts[1];
				for ($c = min($a, $b); $c <= max($a, $b); $c++) {
					$want[$c] = true;
				}
			}
		} elseif (is_numeric($tok)) {
			$want[(int) $tok] = true;
		}
	}
	foreach ($defs as $d) {
		if (isset($want[$d['case']])) {
			$selected[] = $d;
		}
	}
}

if ($selected === array()) {
	fwrite(STDERR, "No cases selected.\n");
	exit(2);
}

$fzGlobal = new fractal_zip();
$t0 = microtime(true);
$quietInner = !$verbose;

/** Same widths for summary rows + aligned inner-detail rows (see sub-header when --final). */
$rowFmt = "%4s  %-15s  %10s  %8s  %8s  %10s  %7s  %5s  %s\n";

fwrite(
	STDOUT,
	sprintf(
		$rowFmt,
		'case',
		'dir',
		'recipeLin',
		'rawB',
		'bestFin',
		'vsRecipe',
		'passes',
		'beam',
		'note'
	)
);
if ($showFinal) {
	fwrite(
		STDOUT,
		sprintf(
			$rowFmt,
			' ',
			'inner detail',
			'equivB',
			'dictB',
			'finLin',
			'chain',
			'repl',
			'occ',
			'pieces'
		)
	);
}

/** @param non-negative-int $max */
$bench_fit_col = static function (string $s, int $max): string {
	if ($max < 1) {
		return '';
	}
	if (strlen($s) <= $max) {
		return $s;
	}
	if ($max <= 3) {
		return substr($s, 0, $max);
	}
	return substr($s, 0, $max - 2) . '..';
};

foreach ($selected as $spec) {
	$cnum = $spec['case'];
	$dir = $spec['dir'];
	$fname = $spec['file'];
	$path = $repoRoot . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . $fname;

	$recipeLin = null;
	if ($spec['equiv'] !== null && $spec['fractal'] !== null) {
		$recipeLin = strlen($spec['equiv']) + strlen($spec['fractal']);
	}
	if ($cnum === 30 && $recipeLin === null && $recipeLinFromCase29 !== null) {
		$recipeLin = $recipeLinFromCase29;
	}

	if ($cnum >= 21 && $cnum <= 28 && $spec['equiv'] !== null) {
		$parent = dirname($path);
		if ($materialize && ($force || !is_file($path))) {
			if (!is_dir($parent) && !mkdir($parent, 0777, true) && !is_dir($parent)) {
				fwrite(STDERR, "Cannot create {$parent}\n");
				exit(1);
			}
			$fzGlobal->create_fractal_file($path, $spec['equiv'], $spec['fractal'] ?? '');
		}
	}

	if (!is_file($path)) {
		fwrite(
			STDOUT,
			sprintf(
				$rowFmt,
				(string) $cnum,
				$dir,
				$recipeLin !== null ? (string) $recipeLin : 'na',
				'na',
				'na',
				'na',
				(string) $maxPasses,
				(string) $beamWidth,
				'MISSING run with --materialize'
			)
		);
		continue;
	}

	$raw = file_get_contents($path);
	if ($raw === false) {
		fwrite(STDERR, "Cannot read {$path}\n");
		continue;
	}
	if ($prefixBytes !== null && strlen($raw) > $prefixBytes) {
		$raw = substr($raw, 0, $prefixBytes);
	}

	$fz = new fractal_zip($segment, false, false, null, false);
	$pass1Minimal = ($maxPasses === 1 && !$fullPass1);
	$prevRawPrefix = getenv('FZ_INNER_VERIFY_RAW_PREFIX');
	if ($cnum === 30) {
		putenv('FZ_INNER_VERIFY_RAW_PREFIX=1');
	}
	$sr = fz_inner_pass_search(
		$fz,
		$raw,
		$maxPasses,
		$beamWidth,
		$dedupeFrontier,
		$frontierHardCap,
		$memoryLimitCli,
		$quietInner,
		$pass1Minimal,
		false,
		$innerWallSeconds,
		$innerWallResetMultipass
	);
	if ($cnum === 30) {
		if ($prevRawPrefix === false) {
			putenv('FZ_INNER_VERIFY_RAW_PREFIX');
		} else {
			putenv('FZ_INNER_VERIFY_RAW_PREFIX=' . $prevRawPrefix);
		}
	}
	$baseLin = $sr['baseLin'];
	$rows = $sr['rows'];

	$best = $baseLin;
	/** @var array{dict:string, equiv:string, linears:list<int>, pieces:list<string>, last_occ:int}|null $bestRow */
	$bestRow = null;
	if (is_array($rows) && count($rows) > 0) {
		foreach ($rows as $row) {
			$fin = fz_row_final_lin($row);
			if ($fin >= $baseLin) {
				continue;
			}
			if ($bestRow === null || fz_cmp_final_lin($row, $bestRow) < 0) {
				$bestRow = $row;
				$best = $fin;
			}
		}
	}

	$recipeOracleSuffix = '';
	if ($recipeOracle) {
		if ($spec['equiv'] !== null && $spec['fractal'] !== null) {
			try {
				$req = (string) $spec['equiv'];
				$rfr = (string) $spec['fractal'];
				if ($fz->fractally_process_string($req, $rfr) === $raw) {
					$oracleFin = strlen($req) + strlen($rfr);
					if ($oracleFin < $best) {
						$best = $oracleFin;
						$bestRow = array(
							'fractal_ref' => $rfr,
							'dict' => '',
							'equiv' => $req,
							'linears' => array($baseLin, $oracleFin),
							'pieces' => array('<recipe-oracle>'),
							'last_occ' => 0,
						);
						$recipeOracleSuffix = '; verified generator beats inner';
					}
				}
			} catch (Throwable $e) {
				// generator mismatch or fractally failure — ignore oracle
			}
		}

		if ($cnum === 30 && $recipeCase29 !== null && $recipeLinFromCase29 !== null) {
			try {
				$eq29 = $recipeCase29['equiv'];
				$fr29 = $recipeCase29['fractal'];
				$full = $fz->fractally_process_string($eq29, $fr29);
				$rlen = strlen($raw);
				if ($full !== false && $rlen <= strlen($full) && substr($full, 0, $rlen) === $raw) {
					$oracleFin = strlen($eq29) + strlen($fr29);
					if ($oracleFin < $best) {
						$best = $oracleFin;
						$bestRow = array(
							'fractal_ref' => $fr29,
							'dict' => '',
							'equiv' => $eq29,
							'linears' => array($baseLin, $oracleFin),
							'pieces' => array('<recipe-prefix-oracle>'),
							'last_occ' => 0,
						);
						$recipeOracleSuffix = '; case 29 expansion prefix (recipeLin reference)';
					}
				}
			} catch (Throwable $e) {
			}
		}
	}

	$vs = 'na';
	if ($recipeLin !== null) {
		$vs = sprintf('%.4f', $recipeLin > 0 ? ($best / $recipeLin) : 0.0);
	}

	$noteCol = $spec['note'];
	if (($sr['innerSoftByteCapped'] ?? false) === true) {
		$noteCol .= ' [FZ_INNER soft-cap]';
	}
	if (($sr['innerWallTimedOut'] ?? false) === true) {
		$noteCol .= ' [inner wall-timeout]';
	}
	$noteCol .= $recipeOracleSuffix;
	if ($verbose) {
		$tn = $sr['tableNote'] ?? '';
		if ($tn !== '') {
			fwrite(STDERR, "case {$cnum} inner tableNote: {$tn}\n");
		}
	}

	fwrite(
		STDOUT,
		sprintf(
			$rowFmt,
			(string) $cnum,
			$dir,
			$recipeLin !== null ? (string) $recipeLin : 'na',
			(string) strlen($raw),
			(string) $best,
			$vs,
			(string) $maxPasses,
			(string) $beamWidth,
			$noteCol
		)
	);
	if ($showFinal) {
		if ($bestRow !== null) {
			$fin = fz_row_final_lin($bestRow);
			$db = strlen($bestRow['dict']);
			$eb = strlen($bestRow['equiv']);
			$repl = count($bestRow['pieces']);
			$occ = $bestRow['last_occ'];
			$chain = fz_chain_compact($bestRow['linears']);
			$pcol = fz_pieces_column($bestRow['pieces'], 44, 120);
			fwrite(
				STDOUT,
				sprintf(
					$rowFmt,
					' ',
					' ',
					(string) $eb,
					(string) $db,
					(string) $fin,
					$bench_fit_col($chain, 10),
					(string) $repl,
					(string) $occ,
					$pcol
				)
			);
		} else {
			fwrite(
				STDOUT,
				sprintf(
					$rowFmt,
					' ',
					' ',
					'-',
					'-',
					'-',
					'-',
					'-',
					'-',
					'no improving encoding (baseLin=' . (string) $baseLin . ')'
				)
			);
		}
	}

	if ($cnum === 30 && $recursiveSub150Top > 0) {
		$cap = 150;
		$cands = array();
		foreach ($sr['rows'] ?? array() as $row) {
			$fin = fz_row_final_lin($row);
			if ($fin >= $cap || $fin >= $baseLin) {
				continue;
			}
			if (!bench_fractal_inner_encoding_has_recursion_depth_field($row['equiv'], $row['dict'])) {
				continue;
			}
			$cands[] = $row;
		}
		fz_inner_usort_rows_by_final_lin($cands);
		$nShow = min($recursiveSub150Top, count($cands));
		fwrite(STDOUT, "\n# case 30: up to {$recursiveSub150Top} best improving rows with finLin<{$cap} and explicit substring recursion (<off\\\"len\\\"rec…>)\n");
		fwrite(STDOUT, "# candidates_in_row_set=" . (string) count($cands) . " (use --full-pass1 --max-passes>=3 --beam-width=0 to widen the union if empty)\n");
		for ($ri = 0; $ri < $nShow; $ri++) {
			$row = $cands[$ri];
			$fin = fz_row_final_lin($row);
			$rkn = $ri + 1;
			fwrite(STDOUT, "#{$rkn}  finLin={$fin}  equivB=" . strlen($row['equiv']) . '  dictB=' . strlen($row['dict']) . "\n");
			fwrite(STDOUT, '    equiv: ' . str_replace(array("\n", "\r"), array('\\n', '\\r'), $row['equiv']) . "\n");
			fwrite(STDOUT, '    dict:  ' . str_replace(array("\n", "\r"), array('\\n', '\\r'), $row['dict']) . "\n");
			fwrite(STDOUT, '    pieces: ' . fz_pieces_column($row['pieces'], 200, 400) . "\n\n");
		}
		if ($nShow === 0) {
			fwrite(STDOUT, "# (no matching rows in this run)\n\n");
		}
	}
}

$elapsed = microtime(true) - $t0;
fwrite(STDERR, sprintf("wall_s=%.3f cases=%d seg=%d max_passes=%d dedupe=%d full_pass1=%d recipe_oracle=%d inner_wall_s=%s inner_wall_reset_mp=%d verbose=%d recursive_sub150_top=%d\n", $elapsed, count($selected), $segment, $maxPasses, $dedupeFrontier ? 1 : 0, $fullPass1 ? 1 : 0, $recipeOracle ? 1 : 0, $innerWallSeconds !== null ? (string) $innerWallSeconds : '-', $innerWallResetMultipass ? 1 : 0, $verbose ? 1 : 0, $recursiveSub150Top));
