#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare cases[0] fields across two bench JSON files (tik-tok A/B).
 *
 *   php benchmarks/compare_bench_json_cases.php a.json b.json
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

if (($argv[1] ?? '') === '' || ($argv[2] ?? '') === '') {
	fwrite(STDERR, "Usage: php compare_bench_json_cases.php <a.json> <b.json>\n");
	exit(2);
}

$load = static function (string $p): array {
	$j = bench_json_decode_file_assoc_try($p, 'compare_bench_json');
	return is_array($j) ? ($j['cases'][0] ?? array()) : array();
};

$a = $load($argv[1]);
$b = $load($argv[2]);
$keys = array('fzc_bytes', 'outer_codec', 'zip_seconds', 'best_ext_folder_bytes', 'best_ext_winner', 'gzip9_bundle_bytes');
foreach ($keys as $k) {
	$va = $a[$k] ?? null;
	$vb = $b[$k] ?? null;
	$same = $va === $vb ? 'same' : 'DIFF';
	echo str_pad($k, 24) . "  {$same}  " . json_encode($va) . "  |  " . json_encode($vb) . "\n";
}
