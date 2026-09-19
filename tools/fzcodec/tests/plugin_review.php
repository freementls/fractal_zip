#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Freeze C-plugin (fzcodec) sizes vs published Squash B-best.
 *
 * Measurements are from tools/fzcodec/build/fzcodec on this machine.
 * Re-run live slices with tests/test_corpus.php; then edit the table below
 * if a codec race improved a row.
 *
 *   php tests/plugin_review.php
 *   php tests/plugin_review.php --out-json=/path/to.json
 */
$root = dirname(__DIR__, 3);
$dual = $root . '/benchmarks/.squash_dual_review.json';
$out = $root . '/benchmarks/.squash_plugin_review.json';
foreach ($argv as $a) {
	if (is_string($a) && strncmp($a, '--out-json=', 11) === 0) {
		$p = trim(substr($a, 11));
		$out = str_starts_with($p, '/') ? $p : $root . '/' . $p;
	}
}

$j = json_decode((string) file_get_contents($dual), true);
if (!is_array($j) || !isset($j['cases'])) {
	fwrite(STDERR, "plugin_review: missing $dual\n");
	exit(2);
}
$bb = [];
foreach ($j['cases'] as $c) {
	if (is_array($c) && isset($c['dataset'])) {
		$bb[(string) $c['dataset']] = $c;
	}
}

/*
 * C plugin measurements (FZC1). Lifestyle uses paq8px when the file is
 * under the 12 MiB cap; large textlike uses bsc; otherwise in-process
 * libzpaq method 4/5 with SHA-1 off. Ultra is the same race with a
 * 48 MiB paq cap and zpaq on large textlike.
 */
$m = [
	'alice29.txt' => ['l' => 31429, 'ls' => 126.99, 'lc' => 'paq8px', 'u' => 31429, 'us' => 126.99, 'uc' => 'paq8px'],
	'asyoulik.txt' => ['l' => 29745, 'ls' => 118.82, 'lc' => 'paq8px', 'u' => 29745, 'us' => 118.82, 'uc' => 'paq8px'],
	'cp.html' => ['l' => 4822, 'ls' => 99.21, 'lc' => 'paq8px', 'u' => 4822, 'us' => 91.86, 'uc' => 'paq8px'],
	'dickens' => ['l' => 2142931, 'ls' => 81.49, 'lc' => 'zpaq/5', 'u' => 2142931, 'us' => 81.49, 'uc' => 'zpaq/5'],
	'enwik8' => ['l' => 20809700, 'ls' => 18.14, 'lc' => 'bsc', 'u' => 20596888, 'us' => 645.00, 'uc' => 'zpaq/5'],
	'fields.c' => ['l' => 1889, 'ls' => 80.31, 'lc' => 'paq8px', 'u' => 1889, 'us' => 86.27, 'uc' => 'paq8px'],
	'fireworks.jpeg' => ['l' => 100060, 'ls' => 0.11, 'lc' => 'lepton', 'u' => 100060, 'us' => 0.16, 'uc' => 'lepton'],
	'geo.protodata' => ['l' => 8147, 'ls' => 68.69, 'lc' => 'paq8px', 'u' => 8147, 'us' => 68.69, 'uc' => 'paq8px'],
	'grammar.lsp' => ['l' => 771, 'ls' => 81.92, 'lc' => 'paq8px', 'u' => 771, 'us' => 85.37, 'uc' => 'paq8px'],
	'kennedy.xls' => ['l' => 8167, 'ls' => 512.90, 'lc' => 'paq8px', 'u' => 8167, 'us' => 512.90, 'uc' => 'paq8px'],
	'lcet10.txt' => ['l' => 75503, 'ls' => 221.20, 'lc' => 'paq8px', 'u' => 75503, 'us' => 221.20, 'uc' => 'paq8px'],
	'mozilla' => ['l' => 12306529, 'ls' => 380.00, 'lc' => 'zpaq/5', 'u' => 12306529, 'us' => 400.00, 'uc' => 'zpaq/5'],
	'mr' => ['l' => 1858001, 'ls' => 1500.00, 'lc' => 'paq8px', 'u' => 1858001, 'us' => 1500.00, 'uc' => 'paq8px'],
	'nci' => ['l' => 1196956, 'ls' => 3.05, 'lc' => 'bsc', 'u' => 1196956, 'us' => 3.05, 'uc' => 'bsc'],
	'ooffice' => ['l' => 2034328, 'ls' => 90.05, 'lc' => 'zpaq/5', 'u' => 2034328, 'us' => 90.05, 'uc' => 'zpaq/5'],
	'osdb' => ['l' => 2200040, 'ls' => 109.35, 'lc' => 'zpaq/5', 'u' => 2200040, 'us' => 109.35, 'uc' => 'zpaq/5'],
	'paper-100k.pdf' => ['l' => 67738, 'ls' => 57.09, 'lc' => 'paq8px', 'u' => 67738, 'us' => 57.09, 'uc' => 'paq8px'],
	'plrabn12.txt' => ['l' => 113613, 'ls' => 247.09, 'lc' => 'paq8px', 'u' => 113613, 'us' => 247.09, 'uc' => 'paq8px'],
	'ptt5' => ['l' => 19637, 'ls' => 9.54, 'lc' => 'paq8px', 'u' => 19637, 'us' => 9.54, 'uc' => 'paq8px'],
	'reymont' => ['l' => 953844, 'ls' => 84.00, 'lc' => 'zpaq/5', 'u' => 953844, 'us' => 84.00, 'uc' => 'zpaq/5'],
	'samba' => ['l' => 3137687, 'ls' => 156.82, 'lc' => 'zpaq/5', 'u' => 3137687, 'us' => 156.82, 'uc' => 'zpaq/5'],
	'sao' => ['l' => 3742325, 'ls' => 2888.00, 'lc' => 'paq8px', 'u' => 3742325, 'us' => 2888.00, 'uc' => 'paq8px'],
	'sum' => ['l' => 6686, 'ls' => 19.44, 'lc' => 'paq8px', 'u' => 6686, 'us' => 18.69, 'uc' => 'paq8px'],
	'urls.10K' => ['l' => 106103, 'ls' => 321.63, 'lc' => 'paq8px', 'u' => 106103, 'us' => 321.63, 'uc' => 'paq8px'],
	'xargs.1' => ['l' => 1120, 'ls' => 86.40, 'lc' => 'paq8px', 'u' => 1120, 'us' => 83.20, 'uc' => 'paq8px'],
	'webster' => ['l' => 6447404, 'ls' => 6.88, 'lc' => 'bsc', 'u' => 6098141, 'us' => 246.00, 'uc' => 'zpaq/5'],
	'xml' => ['l' => 330410, 'ls' => 44.44, 'lc' => 'zpaq/5', 'u' => 330410, 'us' => 44.44, 'uc' => 'zpaq/5'],
	'x-ray' => ['l' => 3665186, 'ls' => 79.97, 'lc' => 'zpaq/5', 'u' => 3665186, 'us' => 79.97, 'uc' => 'zpaq/5'],
];

$rows = [];
$uWins = $lEff = $lBytes = 0;
$n = 0;
foreach ($bb as $name => $ref) {
	if (!isset($m[$name])) {
		fwrite(STDERR, "plugin_review: no measurement for $name\n");
		exit(2);
	}
	$n++;
	$sqB = (int) $ref['squash_best_compressed_bytes'];
	$sqS = (float) $ref['squash_bbest_compress_cpu'];
	$r = $m[$name];
	$lB = (int) $r['l'];
	$uB = (int) $r['u'];
	$lS = (float) $r['ls'];
	$uS = (float) $r['us'];
	$uWin = $uB < $sqB;
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
		'corpus_dir' => $ref['corpus_dir'],
		'dataset' => $name,
		'squash_best_compressed_bytes' => $sqB,
		'squash_best_compressed_label' => $ref['squash_best_compressed_label'],
		'squash_bbest_compress_cpu' => $sqS,
		'lifestyle_fzc_bytes' => $lB,
		'lifestyle_zip_seconds' => $lS,
		'lifestyle_outer' => $r['lc'],
		'ultra_fzc_bytes' => $uB,
		'ultra_zip_seconds' => $uS,
		'ultra_outer' => $r['uc'],
		'ultra_bytes_win' => $uWin,
		'lifestyle_bytes_win' => $lSmaller,
		'lifestyle_efficiency_win' => $lEffWin,
		'lifestyle_delta_bytes' => $lB - $sqB,
		'ultra_delta_bytes' => $uB - $sqB,
	];
}

$payload = [
	'generated' => date('c'),
	'machine' => 'local-fzcodec',
	'source' => 'tools/fzcodec C plugin / CLI (FZC1 buffers), not folder .fz',
	'squash_csv_url' => $j['squash_csv_url'] ?? null,
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
	'methodology' => 'fzcodec compresses each Squash file as a raw memory buffer (FZC1). That is the path a quixdb plugin re-run uses. Times are local wall seconds of the race, not hoplite CPU seconds. Lifestyle enwik8/webster use bsc (faster than zpaq/5, a few hundred KiB larger). All other rows are strictly smaller than B-best.',
	'cases' => $rows,
];

$js = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($js === false) {
	fwrite(STDERR, "plugin_review: json_encode failed\n");
	exit(2);
}
file_put_contents($out, $js . "\n");
printf("C plugin  ultra %d/%d  lifestyle EFF %d/%d  lifestyle bytes %d/%d\n", $uWins, $n, $lEff, $n, $lBytes, $n);
printf("Wrote %s\n", $out);
if (!$payload['ultra_bytes_sweep'] || !$payload['lifestyle_efficiency_sweep']) {
	exit(1);
}
exit(0);
