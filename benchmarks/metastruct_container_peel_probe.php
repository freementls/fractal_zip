#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Per-member literal mode with vs without FZMS container peel on ZIP/OLE blobs in a folder.
 *
 *   php benchmarks/metastruct_container_peel_probe.php test_files61/03_tarballs/raster_formats.zip
 */
putenv('FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1');
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';

$path = $root . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '03_tarballs' . DIRECTORY_SEPARATOR . 'raster_formats.zip';
foreach ($argv as $i => $arg) {
	if ($i === 0 || str_starts_with($arg, '-')) {
		continue;
	}
	$path = str_starts_with($arg, '/') ? $arg : ($root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $arg));
}
if (!is_file($path)) {
	fwrite(STDERR, "missing: {$path}\n");
	exit(1);
}

bench_ultra_apply_env_defaults();
putenv('FRACTAL_ZIP_WEB_REF=0');

$raw = (string) file_get_contents($path);
$rel = basename($path);
$dir = dirname($path);
$censusDir = is_dir($root . DIRECTORY_SEPARATOR . 'test_files61') ? realpath($root . DIRECTORY_SEPARATOR . 'test_files61') : $dir;
if (str_contains(str_replace('\\', '/', $path), '/test_files61/')) {
	$censusDir = realpath($root . DIRECTORY_SEPARATOR . 'test_files61') ?: $censusDir;
}
$census = fractal_zip_metastruct_census_from_dir($censusDir ?: $dir);

echo "file={$rel} bytes=" . strlen($raw) . "\n";
echo 'want_peel=' . (fractal_zip_metastruct_member_want_container_peel_priority($rel, $raw, $census) ? 'yes' : 'no') . "\n\n";

foreach (array(false, true) as $fzms) {
	putenv('FRACTAL_ZIP_METastruct');
	putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL');
	if ($fzms) {
		putenv('FRACTAL_ZIP_METastruct=1');
		putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL=1');
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
