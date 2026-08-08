#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_wad_shared_peel`: multiple PWADs with shared English
 * lumps so classic peel + FZCL beats opaque coding hard.
 *
 *   php benchmarks/build_test_files_classic_wad_shared_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_wad_shared_peel';

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';

$shared = '';
for ($i = 0; $i < 40; $i++) {
	$shared .= "Shared WAD peel prose {$i}: textures, scripts, and briefings repeat across maps.\n";
}
$shared = str_repeat($shared, 8);

$classic = 0;
foreach (['MAP01', 'MAP02', 'MAP03'] as $map) {
	$files = [];
	for ($i = 1; $i <= 16; $i++) {
		// WAD lump names ≤8 chars
		$files[] = [
			'name' => sprintf('T%02d%s', $i, substr($map, -1)),
			'data' => "MAP={$map}\n" . $shared . "UNIQUE={$map}-{$i}\nEND\n",
		];
	}
	$blob = fractal_zip_classic_rebuild_archive('wad', 'PWAD', $files);
	if ($blob === null) {
		fwrite(STDERR, "wad rebuild failed for {$map}\n");
		exit(1);
	}
	file_put_contents("{$dest}/{$map}.wad", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_classic("{$map}.wad", $blob, $m, $r)
		&& (int) ($r["{$map}.wad"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic WAD shared peel — multi PWAD shared English\n");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize("$dest/$e");
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total} classic={$classic}\n";
