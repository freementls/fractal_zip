#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Summarize fractal_zip bytes wins on text-ish Squash / HTML corpora.
 *
 * Merges bench JSON rows with baseline_cache min-ext when fzc missing.
 * Win = fzc_bytes <= min(gzip9, 7z, best_ext).
 *
 * Usage (read-only, low memory):
 *   php benchmarks/bench_text_corpus_wins.php
 *   php benchmarks/bench_text_corpus_wins.php benchmarks/.silesia12_perfile_pmb.json
 *
 * Optional fresh encode (ONE corpus at a time; do not batch — memory-heavy):
 *   FRACTAL_ZIP_BENCH_MEMORY_LIMIT=768M php benchmarks/bench_text_corpus_wins.php --run --only=115
 *   (default auto text-inner; add --no-text-inner for legacy baseline without general-text path)
 */

require_once __DIR__ . '/bench_corpus_descriptions.php';
require_once __DIR__ . '/bench_json_helpers.php';

/** Authoritative rows from benchmarks/BYTES_WIN_TRACKER.md (override stale repo-root .fz). */
function bench_text_corpus_pinned_rows(): array
{
	return array(
		'test_files13' => array(
			'fzc_bytes' => 6207,
			'best_ext_folder_bytes' => 6291,
			'best_ext_winner' => 'brotli',
			'raw_bytes' => 62870,
			'source' => 'bytes_win_tracker',
		),
		'test_files49' => array(
			'fzc_bytes' => 20336,
			'best_ext_folder_bytes' => 20393,
			'best_ext_winner' => 'brotli',
			'raw_bytes' => 85083,
			'source' => 'bytes_win_tracker',
		),
		'test_files55' => array(
			'fzc_bytes' => 11647351,
			'best_ext_folder_bytes' => 11700238,
			'best_ext_winner' => 'arc',
			'raw_bytes' => 209994426,
			'outer_codec' => 'arc',
			'source' => 'bytes_win_tracker',
		),
	);
}

$repo = dirname(__DIR__);
$textLabels = array(
	'test_files105', 'test_files106', 'test_files107', 'test_files108', 'test_files109',
	'test_files115', 'test_files118', 'test_files122', 'test_files124', 'test_files129', 'test_files130',
	'test_files13', 'test_files49', 'test_files55', 'test_files55_sample', 'test_files55_stratified',
);
$desc = bench_corpus_description_map();
$jsonPaths = array(
	$repo . '/benchmarks/.silesia12_perfile_pmb.json',
	$repo . '/benchmarks/.text_corpus_bench.json',
	$repo . '/benchmarks/.test_files55_large_eighth.json',
);
$doRun = false;
$runOnly = '';
$noTextInner = false;
foreach (array_slice($argv, 1) as $arg) {
	if ($arg === '--run') {
		$doRun = true;
	} elseif ($arg === '--no-text-inner') {
		$noTextInner = true;
	} elseif (str_starts_with($arg, '--only=')) {
		$runOnly = substr($arg, 7);
	} elseif (is_file($arg)) {
		$jsonPaths[] = $arg;
	}
}

if ($doRun) {
	$only = $runOnly !== '' ? $runOnly : '';
	if ($only === '' || str_contains($only, ',')) {
		fwrite(STDERR, "Refusing batch --run (memory). Pass exactly one corpus: --run --only=115\n");
		exit(2);
	}
	$mem = getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT') ?: '768M';
	$out = $repo . '/benchmarks/.text_corpus_bench.json';
	$innerEnv = $noTextInner ? 'FRACTAL_ZIP_GENERAL_TEXT_INNER=off ' : '';
	$cmd = sprintf(
		'%sFRACTAL_ZIP_BENCH_MEMORY_LIMIT=%s php -d memory_limit=%s %s/benchmarks/run_benchmarks.php --only=%s --large --json --no-case-timeout --out-json=%s 2>&1',
		$innerEnv,
		escapeshellarg($mem),
		escapeshellarg($mem),
		escapeshellarg($repo),
		escapeshellarg($only),
		escapeshellarg($out)
	);
	fwrite(STDERR, "running: {$cmd}\n");
	passthru($cmd, $code);
	if ($code !== 0) {
		exit($code);
	}
	if (!in_array($out, $jsonPaths, true)) {
		$jsonPaths[] = $out;
	}
}

$cachePath = $repo . '/benchmarks/.baseline_cache.json';
$cache = is_file($cachePath)
	? (bench_json_decode_file_assoc_try($cachePath, 'baseline_cache') ?? array())
	: array();
$cacheRows = $cache['entries'] ?? $cache;

/** @var array<string, array<string, mixed>> */
$byLabel = array();
foreach ($jsonPaths as $path) {
	if (!is_file($path)) {
		continue;
	}
	$j = bench_json_decode_file_assoc_try($path, basename($path));
	if ($j === null) {
		continue;
	}
	foreach ($j['cases'] ?? array() as $row) {
		$lab = (string) ($row['label'] ?? '');
		if ($lab === '') {
			continue;
		}
		$byLabel[$lab] = $row;
	}
}

$pinned = bench_text_corpus_pinned_rows();

$rows = array();
foreach ($textLabels as $lab) {
	$row = $byLabel[$lab] ?? null;
	$pin = $pinned[$lab] ?? null;
	if ($pin !== null) {
		$row = is_array($row) ? array_merge($row, $pin) : $pin;
	}
	$base = is_array($cacheRows[$lab] ?? null) ? $cacheRows[$lab] : null;
	$fzc = $row !== null ? (int) ($row['fzc_bytes'] ?? 0) : 0;
	if ($fzc <= 0 && $pin === null) {
		$fzcPath = $repo . DIRECTORY_SEPARATOR . $lab . '.fz';
		if (is_file($fzcPath)) {
			$fzc = (int) filesize($fzcPath);
		}
	}
	$rawBytes = (int) ($row['raw_bytes'] ?? $base['raw_bytes'] ?? 0);
	// Ignore mislabeled tiny .fz on huge tree corpora (sample blob at repo root).
	if ($fzc > 0 && $rawBytes > 100 * 1024 * 1024 && $fzc < 10 * 1024 * 1024) {
		$fzc = 0;
	}
	$gz = (int) ($row['gzip9_bundle_bytes'] ?? $base['gzip9_bundle_bytes'] ?? 0);
	$z7 = (int) ($row['seven_zip_folder_bytes'] ?? $base['seven_zip_folder_bytes'] ?? 0);
	$ext = null;
	if ($row !== null && isset($row['best_ext_folder_bytes']) && $row['best_ext_folder_bytes'] !== null) {
		$ext = (int) $row['best_ext_folder_bytes'];
	} elseif ($base !== null && isset($base['best_ext_folder_bytes'])) {
		$ext = (int) $base['best_ext_folder_bytes'];
	}
	$extWin = (string) ($row['best_ext_winner'] ?? $base['best_ext_winner'] ?? '');
	$cands = array_filter(array($gz, $z7, $ext), static fn ($x) => $x !== null && $x > 0);
	$bestOther = $cands !== array() ? min($cands) : null;
	if ($fzc <= 0 || $bestOther === null) {
		$rows[] = array(
			'label' => $lab,
			'desc' => trim($desc[$lab] ?? ''),
			'status' => 'NO_DATA',
			'fzc' => $fzc,
			'best_other' => $bestOther,
			'ext_winner' => $extWin,
		);
		continue;
	}
	$delta = $fzc - $bestOther;
	if ($delta < 0) {
		$gate = 'WIN';
	} elseif ($delta === 0) {
		$gate = 'TIE';
	} else {
		$gate = 'LOSS';
	}
	$tx = null;
	if (is_array($row['folder_bundle_census'] ?? null)) {
		$tx = (float) ($row['folder_bundle_census']['textish_ratio'] ?? 0);
	}
	$rows[] = array(
		'label' => $lab,
		'desc' => trim($desc[$lab] ?? ''),
		'status' => $gate,
		'fzc' => $fzc,
		'best_other' => $bestOther,
		'delta' => $delta,
		'ext_winner' => $extWin,
		'outer' => (string) ($row['outer_codec'] ?? ''),
		'tx_ratio' => $tx,
		'raw' => $rawBytes,
		'source' => (string) ($row['source'] ?? ($row !== null ? 'bench_json' : (is_file($repo . DIRECTORY_SEPARATOR . $lab . '.fz') ? 'fzc_file' : 'cache_only'))),
	);
}

usort($rows, static function (array $a, array $b): int {
	$ord = array('WIN' => 0, 'TIE' => 1, 'LOSS' => 2, 'NO_DATA' => 3);
	$sa = $ord[$a['status']] ?? 9;
	$sb = $ord[$b['status']] ?? 9;
	if ($sa !== $sb) {
		return $sa <=> $sb;
	}
	return ($a['delta'] ?? PHP_INT_MAX) <=> ($b['delta'] ?? PHP_INT_MAX);
});

$wins = array_values(array_filter($rows, static fn (array $r): bool => $r['status'] === 'WIN'));
$ties = array_values(array_filter($rows, static fn (array $r): bool => $r['status'] === 'TIE'));
$loss = array_values(array_filter($rows, static fn (array $r): bool => $r['status'] === 'LOSS'));
$nodata = array_values(array_filter($rows, static fn (array $r): bool => $r['status'] === 'NO_DATA'));

printf("text corpus bytes wins (%s)\n", date('c'));
printf("  WIN %d  TIE %d  LOSS %d  NO_DATA %d\n\n", count($wins), count($ties), count($loss), count($nodata));

printf("%-18s %-16s %5s %12s %12s %10s %8s\n", 'CORPUS', 'DESC', 'GATE', 'FZC', 'MIN_EXT', 'Δ', 'OUTER');
printf("%'-18s %-16s %5s %12s %12s %10s %8s\n", '', '', '', '', '', '', '');

foreach ($rows as $r) {
	if ($r['status'] === 'NO_DATA') {
		printf("%-18s %-16s %5s %s\n", $r['label'], $r['desc'], 'N/A', 'no fzc row');
		continue;
	}
	printf(
		"%-18s %-16s %5s %12s %12s %+10d %8s\n",
		$r['label'],
		$r['desc'],
		$r['status'],
		number_format($r['fzc']),
		number_format((int) $r['best_other']),
		$r['delta'],
		$r['outer'] !== '' ? $r['outer'] : ($r['ext_winner'] ?: '—')
	);
}

if ($loss !== array()) {
	echo "\nLosses (fz > min-ext):\n";
	foreach ($loss as $r) {
		printf("  %s (%s): fz=%s ext=%s (+%s) ext=%s\n",
			$r['label'], $r['desc'], number_format($r['fzc']), number_format((int) $r['best_other']),
			number_format($r['delta']), $r['ext_winner']);
	}
}

if ($wins !== array()) {
	echo "\nStrict wins (fz < min-ext):\n";
	foreach ($wins as $r) {
		printf("  %s (%s): fz=%s ext=%s (%s B)\n",
			$r['label'], $r['desc'], number_format($r['fzc']), number_format((int) $r['best_other']),
			number_format($r['delta']));
	}
}

$outJson = $repo . '/benchmarks/.text_corpus_wins_report.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'counts' => array('win' => count($wins), 'tie' => count($ties), 'loss' => count($loss), 'no_data' => count($nodata)),
	'rows' => $rows,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "\nJSON: {$outJson}\n";
