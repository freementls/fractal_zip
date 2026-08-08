#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_tarzst_peel`: classic ustar wrapped in zstd with
 * shared English so nested CLASSIC tarzst beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_tarzst_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_tarzst_peel';

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
	$shared .= "Shared tar.zst peel prose {$i}: configs and scripts repeat across packs.\n";
}
$shared = str_repeat($shared, 8);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$files = [];
	for ($i = 1; $i <= 12; $i++) {
		$files[] = [
			'name' => sprintf('%s_%02d.conf', $tag, $i),
			'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
		];
	}
	$blob = fractal_zip_classic_rebuild_archive('tarzst', '3n', $files);
	if ($blob === null) {
		fwrite(STDERR, "tarzst rebuild failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("{$dest}/{$tag}.tar.zst", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_compress("{$tag}.tar.zst", $blob, $m, $r)
		&& (int) ($r["{$tag}.tar.zst"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
		&& ($r["{$tag}.tar.zst"]['format'] ?? '') === 'tarzst') {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic tar.zst peel — nested ustar+zstd shared English\n");

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
