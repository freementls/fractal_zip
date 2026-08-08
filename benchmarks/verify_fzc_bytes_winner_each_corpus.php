#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Run benchmarks one corpus at a time; exit non-zero on the first case where fzc is not in winner_compression
 * (same bytes contest as run_benchmarks: gzip9 vs 7z vs best_ext vs fzc). Ties count as win if fzc is tied.
 *
 *   php benchmarks/verify_fzc_bytes_winner_each_corpus.php [--dry-run]
 *   php benchmarks/verify_fzc_bytes_winner_each_corpus.php --only=test_files2
 *   php benchmarks/verify_fzc_bytes_winner_each_corpus.php --only=133   # same as test_files133 (Silesia x12)
 *
 * When **no `--only=`** is passed, corpora are every **`test_files*`** directory **except** the same default-opt-in
 * skips as **`run_benchmarks.php`** (see **`benchBuildDefaultRunBenchmarksSkipList`** in **`bench_default_corpus_list.php`** —
 * includes **`test_files133`**, stratified samples, etc.). Pass **`--only=`** to include those explicitly.
 *
 * Pass-through: set FRACTAL_ZIP_SEGMENT_LENGTH etc. in the environment before invoking this script.
 *
 * Uses --no-baseline-cache so gzip/7z/min-ext are measured in the same run as .fz (stale
 * benchmarks/.baseline_cache.json can otherwise pair old min-ext bytes with a new .fz and fake a loss).
 *
 * For each corpus, **`bench_corpora_should_pass_large_to_run_benchmarks()`** decides when to pass **`--large`**
 * (same rule as **`run_benchmarks.php`** heavy-folder gzip-fast policy).
 *
 * Large-tree bench orchestration (**`--bench-profile`**, **`--jobs`**, threading): **benchmarks/LARGE_CORPUS_SPEED.md**.
 *
 * Note: best_ext_folder_bytes can still vary run-to-run (external toolchain tournament). A single-shot
 * failure on a tiny corpus may mean min-ext got a “lucky” draw, not that .fz regressed — compare
 * multiple runs (fzc size should be stable; ext often jitters).
 */

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_default_corpus_list.php';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_predict_outer_encode.php';

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dry = in_array('--dry-run', $argv, true);

/** Same bare-digit rule as run_benchmarks.php (`133` → `test_files133`). */
function verify_fzc_normalize_corpus_token(string $token): string
{
	$t = trim($token);
	if ($t === '') {
		return $t;
	}
	if (preg_match('/^[0-9]+$/', $t) === 1) {
		return 'test_files' . (string) (int) $t;
	}

	return $t;
}

$onlyTests = null;
$onlyExplicit = false;
foreach ($argv as $a) {
	if (is_string($a) && strncmp($a, '--only=', 7) === 0) {
		$onlyExplicit = true;
		$raw = array_values(array_filter(array_map('trim', explode(',', substr($a, 7))), static fn ($s) => $s !== ''));
		$onlyTests = [];
		foreach ($raw as $tok) {
			$n = verify_fzc_normalize_corpus_token($tok);
			if ($n !== '') {
				$onlyTests[] = $n;
			}
		}
		break;
	}
}

if ($onlyExplicit && $onlyTests === []) {
	fwrite(STDERR, "empty or invalid --only= token list\n");
	exit(2);
}

$corpora = [];
foreach (glob($repo . DIRECTORY_SEPARATOR . 'test_files*', GLOB_ONLYDIR) ?: [] as $d) {
	$corpora[] = basename($d);
}
if ($corpora === []) {
	fwrite(STDERR, "No test_files* directories under {$repo}\n");
	exit(2);
}
sort($corpora, SORT_NATURAL);

if (!$onlyExplicit) {
	$skipSet = array_fill_keys(benchBuildDefaultRunBenchmarksSkipList(false, false, null), true);
	$before = count($corpora);
	$corpora = array_values(array_filter($corpora, static fn ($n) => !isset($skipSet[$n])));
	if ($dry && $before > count($corpora)) {
		fwrite(STDERR, '[verify] skipping ' . (string) ($before - count($corpora)) . " default-opt-in corpora (see bench_default_corpus_list.php)\n");
	}
}

if ($onlyTests !== null && $onlyTests !== []) {
	$onlySet = array_fill_keys($onlyTests, true);
	$corpora = array_values(array_filter($corpora, static fn ($n) => isset($onlySet[$n])));
	if ($corpora === []) {
		fwrite(STDERR, "No corpora match --only=" . implode(',', $onlyTests) . "\n");
		exit(2);
	}
}

$php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
$bench = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php';

$n = 0;
foreach ($corpora as $name) {
	$n++;
	echo "[{$n}/" . count($corpora) . "] {$name} ... ";
	if ($dry) {
		echo "(dry-run)\n";
		continue;
	}
	$benchLarge = bench_corpora_should_pass_large_to_run_benchmarks($repo, $name);
	// Fresh gzip/7z/min-ext each case so winner_compression is not mixed with stale .baseline_cache.json rows.
	$cmd = escapeshellarg($php) . ' ' . escapeshellarg($bench)
		. ' --only=' . escapeshellarg($name)
		. ($benchLarge ? ' --large' : '')
		. ' --json --no-case-timeout --no-baseline-cache 2>/dev/null';
	$out = shell_exec($cmd);
	if (!is_string($out) || $out === '') {
		echo "FAIL (no output)\n";
		exit(1);
	}
	$data = bench_json_decode_assoc_try($out, 'verify_fzc_bytes_winner_each_corpus ' . $name);
	if ($data === null) {
		echo "FAIL (bad JSON)\n";
		exit(1);
	}
	$skipped = $data['skipped_cases'] ?? [];
	foreach ($skipped as $sk) {
		if (is_array($sk) && isset($sk['label']) && (string) $sk['label'] === $name) {
			echo "FAIL (benchmark skipped: " . ($sk['reason'] ?? '?') . ")\n";
			exit(1);
		}
	}
	if (!isset($data['cases'][0]) || !is_array($data['cases'][0])) {
		echo "FAIL (no case row)\n";
		exit(1);
	}
	$row = $data['cases'][0];
	$win = $row['winner_compression'] ?? null;
	if (!is_array($win)) {
		$win = [];
	}
	if (!in_array('fzc', $win, true)) {
		echo "FAIL\n";
		$winJs = bench_json_encode_try($win, false);
		echo "  winner_compression: " . ($winJs !== null ? $winJs : '[]') . "\n";
		$gz = $row['gzip9_bundle_bytes'] ?? null;
		$z7 = $row['seven_zip_folder_bytes'] ?? null;
		$ex = $row['best_ext_folder_bytes'] ?? null;
		$fz = $row['fzc_bytes'] ?? null;
		echo "  gzip9_B={$gz}  seven_zip_B={$z7}  best_ext_B={$ex}  fzc_B={$fz}\n";
		exit(1);
	}
	$tieJs = bench_json_encode_try($win, false);
	echo "ok (fzc" . (count($win) > 1 ? ' tied ' . ($tieJs !== null ? $tieJs : '[]') : '') . ")\n";
}

echo "All " . count($corpora) . " corpora: fzc is a bytes winner (possibly tied).\n";
