#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Compare inner experiment JSON(s) against pp96 baseline fzc. */
$repo = dirname(__DIR__);
$basePath = $argv[1] ?? ($repo . '/benchmarks/.enwik8_exp_pp96.json');

function load_inner_row(string $path): ?array
{
	if (!is_file($path)) {
		return null;
	}
	$j = json_decode((string) file_get_contents($path), true);
	if (!is_array($j)) {
		return null;
	}
	foreach ($j['cases'] ?? array() as $c) {
		if (($c['label'] ?? '') === 'test_files109') {
			return array(
				'path' => $path,
				'name' => $c['inner_case'] ?? basename($path, '.json'),
				'fzc_bytes' => (int) ($c['fzc_bytes'] ?? 0),
				'zip_seconds' => (float) ($c['zip_seconds'] ?? 0),
				'outer_codec' => $c['outer_codec'] ?? null,
				'outer_zpaq_method' => $c['outer_zpaq_method'] ?? null,
				'env_snapshot' => $c['env_snapshot'] ?? null,
			);
		}
	}
	return null;
}

$base = load_inner_row($basePath);
if ($base === null || ($base['fzc_bytes'] ?? 0) < 1000000) {
	fwrite(STDERR, "Missing pp96 baseline fzc in {$basePath}\n");
	exit(1);
}
$bf = (int) $base['fzc_bytes'];

$glob = glob($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_inner*.json') ?: array();
sort($glob);
echo "enwik8 inner vs pp96 (" . number_format($bf) . " B)\n";
foreach ($glob as $path) {
	$row = load_inner_row($path);
	if ($row === null || ($row['fzc_bytes'] ?? 0) < 1000000) {
		continue;
	}
	$fzc = (int) $row['fzc_bytes'];
	$d = $bf - $fzc;
	$label = (string) ($row['name'] ?? basename($path));
	printf(
		"  %-22s %s B  %s%6s B (%+.3f%%)  %6.0fs  zpaq m%s\n",
		$label,
		number_format($fzc),
		$d >= 0 ? '−' : '+',
		number_format(abs($d)),
		100.0 * $d / $bf,
		$row['zip_seconds'],
		$row['outer_zpaq_method'] ?? '?'
	);
}
