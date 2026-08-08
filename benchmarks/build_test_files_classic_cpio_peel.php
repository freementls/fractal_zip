#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_cpio_peel`: deterministic newc cpio archives with
 * shared English across packs so CLASSIC peel + FZCL beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_cpio_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_cpio_peel';

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
	$shared .= "Shared cpio peel prose {$i}: init scripts, firmware blobs, and configs repeat across packs.\n";
}
$shared = str_repeat($shared, 8);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$files = [];
	for ($i = 1; $i <= 12; $i++) {
		$files[] = [
			'name' => sprintf('etc/%s_%02d.conf', $tag, $i),
			'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
		];
	}
	$files[] = [
		'name' => "notes/{$tag}.txt",
		'data' => "notes for {$tag}\n" . substr($shared, 0, 400) . "\n",
	];
	$blob = fractal_zip_classic_rebuild_archive('cpio', '', $files);
	if ($blob === null) {
		fwrite(STDERR, "cpio rebuild failed for {$tag}\n");
		exit(1);
	}
	$path = "{$dest}/{$tag}.cpio";
	file_put_contents($path, $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_cpio("{$tag}.cpio", $blob, $m, $r)
		&& (int) ($r["{$tag}.cpio"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic cpio peel — deterministic newc shared English across packs\n");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize("$dest/$e");
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total}\n";
echo "classic={$classic}\n";
