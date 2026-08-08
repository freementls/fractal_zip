#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Probe FZMS text mode-17 chain vs single-mode literal pick on one member.
 *
 *   php benchmarks/metastruct_text_chain_probe.php test_files53/products_export_1.csv
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';

$rel = 'products_export_1.csv';
$path = $root . DIRECTORY_SEPARATOR . 'test_files53' . DIRECTORY_SEPARATOR . 'products_export_1.csv';
foreach ($argv as $i => $arg) {
	if ($i === 0 || str_starts_with($arg, '-')) {
		continue;
	}
	$path = str_starts_with($arg, '/') ? $arg : ($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $arg));
	$rel = basename($path);
}

if (!is_file($path)) {
	fwrite(STDERR, "missing: {$path}\n");
	exit(1);
}

bench_ultra_apply_env_defaults();
putenv('FRACTAL_ZIP_WEB_REF=0');

$raw = (string) file_get_contents($path);
$n = strlen($raw);
$dir = dirname($path);
$census = fractal_zip_metastruct_census_from_dir($dir);

echo "file={$rel} bytes={$n}\n";
echo 'dom=' . fractal_zip_metastruct_dominant_content_profile($census) . "\n";
echo 'want_chain=' . (fractal_zip_metastruct_want_literal_chain_search($rel, $raw, $census) ? 'yes' : 'no') . "\n\n";

foreach ([false, true] as $fzms) {
	putenv('FRACTAL_ZIP_METastruct');
	putenv('FRACTAL_ZIP_METastruct_TEXT_CHAIN');
	if ($fzms) {
		putenv('FRACTAL_ZIP_METastruct=1');
		putenv('FRACTAL_ZIP_METastruct_TEXT_CHAIN=1');
	}
	$fz = new fractal_zip(120, true, false, null, false);
	$fz->zip_folder_root_for_members = realpath($dir) ?: $dir;
	$fz->folder_metastruct_census = $fzms ? $census : null;
	ob_start();
	list($mode, $store) = $fz->choose_best_literal_bundle_transform($raw, $rel);
	ob_end_clean();
	$gz = $fz->literal_bundle_gzip_deflate_len($store, 9);
	echo ($fzms ? 'FZMS' : 'base') . " mode={$mode} store_len=" . strlen($store) . " gzip9={$gz}\n";
}

$fz = new fractal_zip(120, true, false, null, false);
$pri = fractal_zip_metastruct_literal_chain_priority_text();
$t0 = microtime(true);
list($modes, $data) = $fz->literal_bundle_greedy_transform_chain($raw, false, true, 9, $pri);
$t1 = microtime(true);
echo "\ngreedy text chain modes=" . implode(',', $modes) . ' rounds=' . count($modes) . ' sec=' . round($t1 - $t0, 2) . "\n";
if (count($modes) >= 2) {
	$blob = $fz->encode_literal_transform_chain_payload($modes, $data);
	$gzChain = $fz->literal_bundle_gzip_deflate_len($blob, 9);
	$gzRaw = $fz->literal_bundle_gzip_deflate_len($raw, 9);
	echo "chain gzip9={$gzChain} raw gzip9={$gzRaw} delta=" . ($gzChain - $gzRaw) . "\n";
}
