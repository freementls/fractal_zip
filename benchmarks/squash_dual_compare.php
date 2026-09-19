#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Dual lifestyle + ultra vs published Squash B-best (hoplite CSV).
 *
 * Bars:
 *   ultra bytes:        fzc_bytes < squash B-best on every dataset
 *   lifestyle efficiency: not Pareto-dominated (smaller or faster than B-best)
 *
 * Usage:
 *   php benchmarks/squash_dual_compare.php
 *   php benchmarks/squash_dual_compare.php --lifestyle=benchmarks/.lifestyle_squash28.json \
 *       --ultra=benchmarks/.ultra_squash28.json --json --out-json=benchmarks/.squash_dual_review.json
 */
$repoRoot = dirname(__DIR__);
$lifeJson = $repoRoot . '/benchmarks/.lifestyle_squash28.json';
$ultraJson = $repoRoot . '/benchmarks/.ultra_squash28.json';
$outJson = $repoRoot . '/benchmarks/.squash_dual_review.json';
$wantJson = in_array('--json', $argv, true);
$refresh = in_array('--refresh-csv', $argv, true);
foreach ($argv as $a) {
	if (is_string($a) && strncmp($a, '--lifestyle=', 12) === 0) {
		$p = trim(substr($a, 12));
		$lifeJson = str_starts_with($p, '/') ? $p : $repoRoot . '/' . $p;
	}
	if (is_string($a) && strncmp($a, '--ultra=', 8) === 0) {
		$p = trim(substr($a, 8));
		$ultraJson = str_starts_with($p, '/') ? $p : $repoRoot . '/' . $p;
	}
	if (is_string($a) && strncmp($a, '--out-json=', 11) === 0) {
		$p = trim(substr($a, 11));
		$outJson = str_starts_with($p, '/') ? $p : $repoRoot . '/' . $p;
	}
}

$load = static function (string $benchJson) use ($repoRoot, $refresh): array {
	$cmd = 'php ' . escapeshellarg($repoRoot . '/benchmarks/squash_benchmarks.php')
		. ' --no-bench --bench-json=' . escapeshellarg($benchJson)
		. ($refresh ? ' --refresh-csv' : '')
		. ' --json';
	$raw = shell_exec($cmd);
	$j = is_string($raw) ? json_decode($raw, true) : null;
	if (!is_array($j) || !isset($j['cases']) || !is_array($j['cases'])) {
		fwrite(STDERR, "[squash_dual] failed to compare {$benchJson}\n");
		exit(2);
	}
	$by = [];
	foreach ($j['cases'] as $c) {
		if (is_array($c) && isset($c['corpus_dir'])) {
			$by[(string) $c['corpus_dir']] = $c;
		}
	}

	return ['meta' => $j, 'by' => $by];
};

$life = $load($lifeJson);
$ultra = $load($ultraJson);
$rows = [];
$uWins = $lEff = $lBytes = 0;
$n = 0;
foreach ($life['by'] as $dir => $L) {
	$U = $ultra['by'][$dir] ?? null;
	if ($U === null) {
		continue;
	}
	$n++;
	$sqB = (int) $L['squash_best_compressed_bytes'];
	$sqS = (float) $L['squash_bbest_compress_cpu'];
	$lB = isset($L['fzc_bytes']) ? (int) $L['fzc_bytes'] : 0;
	$uB = isset($U['fzc_bytes']) ? (int) $U['fzc_bytes'] : 0;
	$lS = (float) ($L['fzc_zip_seconds'] ?? 0);
	$uS = (float) ($U['fzc_zip_seconds'] ?? 0);
	$uWin = $uB > 0 && $uB < $sqB;
	$lSmaller = $lB < $sqB;
	$lFaster = $lS < $sqS;
	$lEffWin = $lSmaller || $lFaster;
	if ($uWin) {
		$uWins++;
	}
	if ($lEffWin) {
		$lEff++;
	}
	if ($lSmaller) {
		$lBytes++;
	}
	$rows[] = [
		'corpus_dir' => $dir,
		'dataset' => $L['dataset'],
		'squash_best_compressed_bytes' => $sqB,
		'squash_best_compressed_label' => $L['squash_best_compressed_label'],
		'squash_bbest_compress_cpu' => $sqS,
		'lifestyle_fzc_bytes' => $lB,
		'lifestyle_zip_seconds' => $lS,
		'lifestyle_outer' => $L['outer_caption'] ?? $L['outer_codec'],
		'ultra_fzc_bytes' => $uB,
		'ultra_zip_seconds' => $uS,
		'ultra_outer' => $U['outer_caption'] ?? $U['outer_codec'],
		'ultra_bytes_win' => $uWin,
		'lifestyle_bytes_win' => $lSmaller,
		'lifestyle_efficiency_win' => $lEffWin,
		'lifestyle_delta_bytes' => $lB - $sqB,
		'ultra_delta_bytes' => $uB - $sqB,
	];
}

$payload = [
	'generated' => date('c'),
	'machine' => $life['meta']['machine'] ?? 'hoplite',
	'squash_csv_url' => $life['meta']['squash_csv_url'] ?? null,
	'lifestyle_bench_json' => $lifeJson,
	'ultra_bench_json' => $ultraJson,
	'bars' => [
		'ultra_bytes' => 'fzc_bytes < published Squash B-best on every dataset',
		'lifestyle_efficiency' => 'not Pareto-dominated by B-best (smaller or faster)',
	],
	'n' => $n,
	'ultra_byte_wins' => $uWins,
	'lifestyle_efficiency_wins' => $lEff,
	'lifestyle_byte_wins' => $lBytes,
	'ultra_bytes_sweep' => $n > 0 && $uWins === $n,
	'lifestyle_efficiency_sweep' => $n > 0 && $lEff === $n,
	'methodology' => '.fz is a folder pipeline (headers, races, wall time). Squash is single-file plugin CPU seconds on other machines. Apples-to-oranges sanity check, not a quixdb listing.',
	'cases' => $rows,
];

$js = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($js === false) {
	fwrite(STDERR, "[squash_dual] json_encode failed\n");
	exit(2);
}
file_put_contents($outJson, $js . "\n");

if ($wantJson) {
	echo $js . "\n";
	exit(0);
}

printf("Squash dual comparison (%s)\n", (string) ($payload['machine'] ?? 'hoplite'));
printf("Ultra bytes %d/%d  Lifestyle efficiency %d/%d  Lifestyle bytes %d/%d\n", $uWins, $n, $lEff, $n, $lBytes, $n);
printf("Wrote %s\n\n", $outJson);
printf("%-16s %-16s %10s %10s %10s %8s %8s  %-7s %-6s\n",
	'corpus', 'dataset', 'sq B', 'life B', 'ultra B', 'life s', 'ultra s', 'U-bytes', 'L-eff');
foreach ($rows as $r) {
	printf("%-16s %-16s %10d %10d %10d %8.2f %8.2f  %-7s %-6s%s\n",
		$r['corpus_dir'],
		$r['dataset'],
		$r['squash_best_compressed_bytes'],
		$r['lifestyle_fzc_bytes'],
		$r['ultra_fzc_bytes'],
		$r['lifestyle_zip_seconds'],
		$r['ultra_zip_seconds'],
		$r['ultra_bytes_win'] ? 'WIN' : 'LOSS',
		$r['lifestyle_efficiency_win'] ? 'WIN' : 'LOSS',
		$r['ultra_bytes_win'] && $r['lifestyle_efficiency_win'] ? '' : sprintf('  dB life=%+d ultra=%+d', $r['lifestyle_delta_bytes'], $r['ultra_delta_bytes'])
	);
}
echo "https://quixdb.github.io/squash-benchmark/\n";
