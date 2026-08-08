#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Rebuild benchmarks/sole_win_universe.json from lifestyle ref + peel dirs + Silesia.
 *
 *   php benchmarks/build_sole_win_universe.php
 */

$repo = dirname(__DIR__);
$outPath = $repo . '/benchmarks/sole_win_universe.json';
$refPath = $repo . '/benchmarks/.pareto_lifestyle_ref.json';
$halfPath = $repo . '/benchmarks/.pareto_lifestyle_half.json';

$labels = [];
foreach ([$refPath, $halfPath] as $p) {
	if (!is_readable($p)) {
		continue;
	}
	$j = json_decode((string) file_get_contents($p), true);
	if (!is_array($j) || !isset($j['cases']) || !is_array($j['cases'])) {
		continue;
	}
	foreach ($j['cases'] as $c) {
		if (!is_array($c)) {
			continue;
		}
		$lab = (string) ($c['label'] ?? '');
		if ($lab !== '' && is_dir($repo . '/' . $lab)) {
			$labels[$lab] = true;
		}
	}
}

$dh = opendir($repo);
if ($dh !== false) {
	while (($e = readdir($dh)) !== false) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$path = $repo . '/' . $e;
		if (!is_dir($path) || !str_starts_with($e, 'test_files')) {
			continue;
		}
		if (str_ends_with($e, '_sample') || str_ends_with($e, '_stratified')) {
			continue;
		}
		$want = str_contains($e, 'peel')
			|| str_starts_with($e, 'test_files_classic')
			|| str_starts_with($e, 'test_files_compress')
			|| $e === 'test_files133'
			|| $e === 'test_files78';
		if ($want) {
			$labels[$e] = true;
		}
	}
	closedir($dh);
}

$corpora = array_keys($labels);
sort($corpora, SORT_STRING);

$payload = [
	'generated' => date('c'),
	'description' => 'Strict sole-win campaign universe: lifestyle 135 + peel corpora + Silesia folders',
	'fragile_margin_bytes' => 64,
	'target_margin_bytes' => 256,
	'lifestyle_ref' => 'benchmarks/.pareto_lifestyle_ref.json',
	'lifestyle_half' => 'benchmarks/.pareto_lifestyle_half.json',
	'corpus_count' => count($corpora),
	'corpora' => $corpora,
];

$json = json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($json) || @file_put_contents($outPath, $json . "\n") === false) {
	fwrite(STDERR, "build_sole_win_universe: write failed\n");
	exit(1);
}
echo "Wrote {$outPath} ({$payload['corpus_count']} corpora)\n";
