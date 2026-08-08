#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_zip_shared_peel`: multiple ZIPs with shared English
 * members so ZIP_MULTI peel + FZHM/FZCL beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_zip_shared_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_zip_shared_peel';

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

if (!class_exists(ZipArchive::class)) {
	fwrite(STDERR, "ZipArchive missing\n");
	exit(1);
}

$shared = '';
for ($i = 0; $i < 40; $i++) {
	$shared .= "Shared ZIP peel prose {$i}: configs and notes repeat across packs.\n";
}
$shared = str_repeat($shared, 8);

foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$path = "{$dest}/{$tag}.zip";
	$z = new ZipArchive();
	if ($z->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
		fwrite(STDERR, "cannot create {$path}\n");
		exit(1);
	}
	// Stored (no deflate) so peel is clean and members compress in the tournament.
	for ($i = 1; $i <= 12; $i++) {
		$name = sprintf('%s_%02d.txt', $tag, $i);
		$data = "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n";
		$z->addFromString($name, $data);
		$z->setCompressionName($name, ZipArchive::CM_STORE);
	}
	$z->close();
}

file_put_contents("$dest/00_README.txt", "classic ZIP shared peel — stored members shared English\n");

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
