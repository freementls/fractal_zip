#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Strict sole-win scoreboard from run_benchmarks.php --json output.
 *
 * Sole = fzc_bytes < min(gzip9, 7z, best_ext). Ties and losses are failures.
 *
 *   php benchmarks/report_sole_wins.php benchmarks/.pareto_lifestyle_half.json
 *   php benchmarks/report_sole_wins.php --fragile=64 --json-out=benchmarks/.sole_wins_report.json bench.json
 *   php benchmarks/report_sole_wins.php --universe=benchmarks/sole_win_universe.json bench.json
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$fragileMargin = 64;
$targetMargin = 256;
$jsonOut = null;
$universePath = null;
$path = null;

foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--fragile=', 10) === 0) {
		$fragileMargin = max(0, (int) substr($a, 10));
		continue;
	}
	if (strncmp($a, '--target-margin=', 16) === 0) {
		$targetMargin = max(0, (int) substr($a, 16));
		continue;
	}
	if (strncmp($a, '--json-out=', 11) === 0) {
		$jsonOut = trim(substr($a, 11));
		continue;
	}
	if (strncmp($a, '--universe=', 11) === 0) {
		$universePath = trim(substr($a, 11));
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php report_sole_wins.php [--fragile=64] [--target-margin=256] [--json-out=path] [--universe=sole_win_universe.json] <bench.json|->\n";
		exit(0);
	}
	if ($a !== '' && $a[0] !== '-') {
		$path = $a;
	}
}

if ($path === null || $path === '') {
	$path = '-';
}

if ($path === '-') {
	$raw = stream_get_contents(STDIN);
	if (!is_string($raw) || $raw === '') {
		fwrite(STDERR, "report_sole_wins: need bench JSON path or stdin\n");
		exit(1);
	}
	$j = bench_json_decode_assoc_try($raw, 'report_sole_wins stdin');
} else {
	if (!is_readable($path)) {
		fwrite(STDERR, "report_sole_wins: unreadable {$path}\n");
		exit(1);
	}
	$j = bench_json_decode_file_assoc_try($path, 'report_sole_wins');
}
if ($j === null) {
	fwrite(STDERR, "report_sole_wins: invalid JSON\n");
	exit(1);
}

$universe = null;
if ($universePath !== null && $universePath !== '') {
	if (!is_readable($universePath)) {
		fwrite(STDERR, "report_sole_wins: unreadable universe {$universePath}\n");
		exit(1);
	}
	$u = bench_json_decode_file_assoc_try($universePath, 'report_sole_wins universe');
	if ($u === null || !isset($u['corpora']) || !is_array($u['corpora'])) {
		fwrite(STDERR, "report_sole_wins: bad universe JSON\n");
		exit(1);
	}
	$universe = array();
	foreach ($u['corpora'] as $c) {
		if (is_string($c) && $c !== '') {
			$universe[$c] = true;
		}
	}
	if (isset($u['fragile_margin_bytes'])) {
		$fragileMargin = max(0, (int) $u['fragile_margin_bytes']);
	}
	if (isset($u['target_margin_bytes'])) {
		$targetMargin = max(0, (int) $u['target_margin_bytes']);
	}
}

$sole = [];
$tie = [];
$loss = [];
$nodata = [];
$fragile = [];

foreach (($j['cases'] ?? []) as $r) {
	if (!is_array($r)) {
		continue;
	}
	$lab = (string) ($r['label'] ?? $r['name'] ?? $r['corpus'] ?? '?');
	if ($universe !== null && !isset($universe[$lab])) {
		continue;
	}
	$fzc = isset($r['fzc_bytes']) ? (int) $r['fzc_bytes'] : 0;
	$cands = [];
	foreach (['gzip9_bundle_bytes', 'seven_zip_folder_bytes', 'best_ext_folder_bytes'] as $k) {
		if (isset($r[$k]) && $r[$k] !== null && (int) $r[$k] > 0) {
			$cands[] = (int) $r[$k];
		}
	}
	if ($fzc <= 0 || $cands === []) {
		$nodata[] = $lab;
		continue;
	}
	$bestOther = min($cands);
	$delta = $fzc - $bestOther;
	$row = [
		'label' => $lab,
		'fzc' => $fzc,
		'best_other' => $bestOther,
		'delta' => $delta,
		'margin' => -$delta,
		'outer' => (string) ($r['outer_codec'] ?? ''),
		'ext_winner' => (string) ($r['best_ext_winner'] ?? ''),
	];
	if ($fzc < $bestOther) {
		$sole[] = $row;
		if ((-$delta) <= $fragileMargin) {
			$fragile[] = $row;
		}
	} elseif ($fzc === $bestOther) {
		$tie[] = $row;
	} else {
		$loss[] = $row;
	}
}

usort($sole, static fn(array $a, array $b): int => $a['margin'] <=> $b['margin']);
usort($fragile, static fn(array $a, array $b): int => $a['margin'] <=> $b['margin']);
usort($tie, static fn(array $a, array $b): int => $a['label'] <=> $b['label']);
usort($loss, static fn(array $a, array $b): int => $b['delta'] <=> $a['delta']);

$n = count($sole) + count($tie) + count($loss) + count($nodata);
$profile = $j['bench_profile'] ?? null;

echo "\n=== Strict sole wins (fzc < min gzip9/7z/min-ext) ===\n";
echo 'source: ' . ($path === '-' ? 'stdin' : $path) . "\n";
echo 'bench_profile: ' . (is_string($profile) ? $profile : 'null') . "\n";
printf(
	"SOLE %d  TIE %d  LOSS %d  NO_DATA %d  (n=%d)\n",
	count($sole),
	count($tie),
	count($loss),
	count($nodata),
	$n
);
printf("fragile (margin ≤ %d B): %d\n", $fragileMargin, count($fragile));
printf("below target margin (%d B): %d\n\n", $targetMargin, count(array_filter(
	$sole,
	static fn(array $r): bool => $r['margin'] < $targetMargin
)));

if ($fragile !== []) {
	echo "Fragile soles (smallest margins first):\n";
	printf("%-28s %10s %10s %8s  %s\n", 'corpus', 'fzc', 'best_other', 'margin', 'outer/ext');
	echo str_repeat('-', 78) . "\n";
	foreach (array_slice($fragile, 0, 40) as $row) {
		printf(
			"%-28s %10d %10d %8d  %s/%s\n",
			$row['label'],
			$row['fzc'],
			$row['best_other'],
			$row['margin'],
			$row['outer'],
			$row['ext_winner']
		);
	}
	echo "\n";
}

if ($tie !== []) {
	echo 'Ties (fail strict): ' . count($tie) . "\n";
	foreach (array_slice($tie, 0, 30) as $row) {
		echo "  {$row['label']}  fzc={$row['fzc']} = best_other  ext={$row['ext_winner']}\n";
	}
	echo "\n";
}

if ($loss !== []) {
	echo 'Losses (fail strict): ' . count($loss) . "\n";
	foreach (array_slice($loss, 0, 30) as $row) {
		echo "  {$row['label']}  fzc={$row['fzc']}  best={$row['best_other']}  Δ=+{$row['delta']}  ext={$row['ext_winner']}\n";
	}
	echo "\n";
}

$out = [
	'generated' => date('c'),
	'source' => $path === '-' ? 'stdin' : $path,
	'bench_profile' => $profile,
	'fragile_margin_bytes' => $fragileMargin,
	'target_margin_bytes' => $targetMargin,
	'counts' => [
		'sole' => count($sole),
		'tie' => count($tie),
		'loss' => count($loss),
		'no_data' => count($nodata),
		'fragile' => count($fragile),
		'n' => $n,
	],
	'sole' => $sole,
	'tie' => $tie,
	'loss' => $loss,
	'nodata' => $nodata,
	'fragile' => $fragile,
];

if ($jsonOut !== null && $jsonOut !== '') {
	$enc = bench_json_encode_try($out, true);
	if ($enc === null) {
		fwrite(STDERR, "report_sole_wins: json_encode failed\n");
		exit(2);
	}
	if (@file_put_contents($jsonOut, $enc . "\n") === false) {
		fwrite(STDERR, "report_sole_wins: cannot write {$jsonOut}\n");
		exit(2);
	}
	echo "JSON: {$jsonOut}\n";
}

// Exit 0 only when every scored case is a sole win (no ties/losses).
exit(($tie === [] && $loss === [] && $n > 0) ? 0 : 1);
