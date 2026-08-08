#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_cpiogz_peel`: classic newc cpio wrapped in gzencode
 * with shared English (initramfs-like) so nested CLASSIC cpiogz beats opaque.
 *
 *   php benchmarks/build_test_files_classic_cpiogz_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_cpiogz_peel';

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
	$shared .= "Shared cpio.gz peel prose {$i}: init scripts and configs repeat across packs.\n";
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
	$blob = fractal_zip_classic_rebuild_archive('cpiogz', '6', $files);
	if ($blob === null) {
		fwrite(STDERR, "cpiogz rebuild failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("{$dest}/{$tag}.cpio.gz", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_gzip("{$tag}.cpio.gz", $blob, $m, $r)
		&& (int) ($r["{$tag}.cpio.gz"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
		&& ($r["{$tag}.cpio.gz"]['format'] ?? '') === 'cpiogz') {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic cpio.gz peel — nested newc+gzip shared English\n");

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
