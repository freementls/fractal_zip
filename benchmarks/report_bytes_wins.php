#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Summarize fractal_zip bytes wins from run_benchmarks.php --json output.
 *
 *   php benchmarks/report_bytes_wins.php path/to/bench.json
 *   php benchmarks/report_bytes_wins.php --min-pct=33 path/to/bench.json   # "big wins" vs next best
 *   php benchmarks/report_bytes_wins.php --compress-time-audit bench.json   # zip_seconds vs min gzip/7z/ext compress
 *   php benchmarks/report_bytes_wins.php --compress-time-audit --zip-time-margin=0.2 bench.json
 *   ./benchmarks/run_benchmarks_low_priority.sh --json --limit=30 | php benchmarks/report_bytes_wins.php -
 *
 * Large-tree wall vs bytes: **`benchmarks/LARGE_CORPUS_SPEED.md`** (`--bench-profile` including **`large-bytes`**, **`bash benchmarks/run_large_corpus_bytes_push.sh`**); same doc **JSON machine output** (default **`benchmarks/.last_bench.json`** is gitignored — use **`--out-json=`** for a path you pass here). **`--compress-time-audit`** prints **`tx%`** when **`folder_bundle_census.textish_ratio`** is present (100×ratio; — otherwise). With **`--repeat`**, **`cases[]`** rows are already aggregated in **`run_benchmarks.php`** (median times); **`folder_bundle_census`** uses the same merge as **`bench_folder_bundle_census_merge_into_aggregated_row`** in **`benchmarks/bench_folder_census.php`** when the first repeat omitted it.
 *
 * "best_other" = min(gzip9, 7z, min-ext) when all present; min-ext omitted if null in JSON (--no-best-ext).
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_folder_census.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$argvList = array_slice($argv, 1);
$minPct = null;
$compressTimeAudit = false;
$zipTimeMargin = 0.0;
$paths = [];
foreach ($argvList as $a) {
	if (strncmp($a, '--min-pct=', 10) === 0) {
		$minPct = max(0.0, (float) substr($a, 10));
		continue;
	}
	if (strncmp($a, '--zip-time-margin=', 18) === 0) {
		$zipTimeMargin = max(0.0, (float) substr($a, 18));
		continue;
	}
	if ($a === '--compress-time-audit') {
		$compressTimeAudit = true;
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo file_get_contents(__FILE__, false, null, 0, 1200) ?: "report_bytes_wins.php\n";
		exit(0);
	}
	$paths[] = $a;
}
$path = $paths[0] ?? '-';
if ($path === '-') {
	$raw = stream_get_contents(STDIN);
	if (!is_string($raw) || $raw === '') {
		fwrite(STDERR, "Usage: php report_bytes_wins.php [--min-pct=N] [--compress-time-audit] [--zip-time-margin=S] <bench.json|->\n");
		exit(1);
	}
	$j = bench_json_decode_assoc_try($raw, 'report_bytes_wins stdin');
} else {
	if (!is_readable($path)) {
		fwrite(STDERR, "Usage: php report_bytes_wins.php [--min-pct=N] [--compress-time-audit] [--zip-time-margin=S] <bench.json|->\n");
		exit(1);
	}
	$j = bench_json_decode_file_assoc_try($path, 'report_bytes_wins');
}
if ($j === null) {
	fwrite(STDERR, "Invalid JSON\n");
	exit(1);
}
$cases = $j['cases'] ?? [];
$wins = [];
$loss = [];
$tie = [];
foreach ($cases as $r) {
	$lab = $r['label'] ?? '?';
	$fzc = (int) ($r['fzc_bytes'] ?? 0);
	$gz = (int) ($r['gzip9_bundle_bytes'] ?? 0);
	$z7 = (int) ($r['seven_zip_folder_bytes'] ?? 0);
	$ext = isset($r['best_ext_folder_bytes']) && $r['best_ext_folder_bytes'] !== null
		? (int) $r['best_ext_folder_bytes'] : null;
	$cands = array_filter([$gz, $z7, $ext !== null ? $ext : null], static fn ($x) => $x !== null && $x > 0);
	$bestOther = $cands !== [] ? min($cands) : null;
	$wc = $r['winner_compression'] ?? [];
	$fzcWon = is_array($wc) && in_array('fzc', $wc, true);
	if ($bestOther === null || $fzc <= 0) {
		continue;
	}
	$save = $bestOther - $fzc;
	$pct = $bestOther > 0 ? (100.0 * $save / $bestOther) : 0.0;
	if ($fzcWon) {
		$wins[] = [$lab, $fzc, $bestOther, $save, $pct, (string) ($r['outer_codec'] ?? '')];
	} elseif (is_array($wc) && count($wc) === 1 && ($wc[0] ?? '') !== 'fzc') {
		$loss[] = [$lab, $fzc, $bestOther, (string) $wc[0]];
	} else {
		$tie[] = [$lab, $fzc, $bestOther, implode(',', $wc)];
	}
}
usort($wins, static fn ($a, $b) => $b[4] <=> $a[4]);

$title = 'Bytes wins (fzc smallest among gzip9 / 7z / min-ext / fzc)';
if ($minPct !== null) {
	$wins = array_values(array_filter($wins, static fn ($row) => $row[4] >= $minPct));
	$title .= " — save ≥ {$minPct}% vs best other";
}
echo "\n=== {$title} ===\n";
echo sprintf("%-20s %8s %10s %8s %8s  %s\n", 'corpus', 'fzc_B', 'best_other', 'save_B', 'save_%', 'outer');
echo str_repeat('-', 76) . "\n";
foreach ($wins as $row) {
	echo sprintf("%-20s %8d %10d %8d %7.1f%%  %s\n", $row[0], $row[1], $row[2], $row[3], $row[4], $row[5]);
}
echo "\nfzc won (listed): " . count($wins) . " / " . count($cases) . " cases in JSON\n";
if ($tie !== []) {
	echo "\nTies / multi-winner: " . count($tie) . "\n";
}
if ($loss !== []) {
	echo "\nStrict losses (single winner ≠ fzc): " . count($loss) . "\n";
	foreach (array_slice($loss, 0, 20) as $row) {
		echo "  {$row[0]}  fzc={$row[1]}  best_other={$row[2]}  winner={$row[3]}\n";
	}
}
$s = $j['summary'] ?? [];
$sumJs = bench_json_encode_try($s, false);
if ($sumJs === null) {
	fwrite(STDERR, '[bench] json_encode failed (report_bytes_wins summary): ' . json_last_error_msg() . "\n");
	exit(2);
}
echo "\nJSON summary: {$sumJs}\n";

if ($compressTimeAudit) {
	$benchOptSeconds = static function (array $row, string $key): ?float {
		if (!array_key_exists($key, $row) || $row[$key] === null) {
			return null;
		}
		$v = (float) $row[$key];

		return $v >= 0.0 ? $v : null;
	};
	$slower = [];
	foreach ($cases as $r) {
		if (!is_array($r)) {
			continue;
		}
		$lab = (string) ($r['label'] ?? '?');
		$zipS = $benchOptSeconds($r, 'zip_seconds');
		if ($zipS === null) {
			continue;
		}
		$parts = array_filter([
			$benchOptSeconds($r, 'gzip9_seconds'),
			$benchOptSeconds($r, 'seven_zip_seconds'),
			$benchOptSeconds($r, 'best_ext_seconds'),
		], static fn ($x) => $x !== null);
		if ($parts === []) {
			continue;
		}
		$baseMin = min($parts);
		$excess = $zipS - $baseMin;
		if ($excess > $zipTimeMargin) {
			$slower[] = [$lab, $zipS, $baseMin, $excess, $baseMin > 0.0 ? ($zipS / $baseMin) : null, bench_folder_census_textish_pct_cell($r['folder_bundle_census'] ?? null)];
		}
	}
	usort($slower, static fn ($a, $b) => $b[3] <=> $a[3]);
	echo "\n=== Compress-time audit (zip_seconds > min(gzip9, seven_zip, best_ext)";
	if ($zipTimeMargin > 0.0) {
		echo " + margin {$zipTimeMargin}s";
	}
	echo ") ===\n";
	echo sprintf("%-22s %5s %10s %12s %10s %8s\n", 'corpus', 'tx%', 'zip_s', 'baseline_min', 'excess_s', 'ratio');
	echo str_repeat('-', 78) . "\n";
	foreach ($slower as $row) {
		$ratioStr = $row[4] !== null ? sprintf('%7.2fx', $row[4]) : '   —  ';
		echo sprintf("%-22s %5s %10.4f %12.4f %10.4f %s\n", $row[0], $row[5], $row[1], $row[2], $row[3], $ratioStr);
	}
	echo "\nRows listed: " . count($slower) . " / " . count($cases) . " cases in JSON\n";
}
