#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_targz_peel`: classic ustar wrapped in gzencode with
 * shared English across packs so nested CLASSIC targz beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_targz_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_targz_peel';

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
	$shared .= "Shared tar.gz peel prose {$i}: configs and scripts repeat across packs.\n";
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
	$blob = fractal_zip_classic_rebuild_archive('targz', '6', $files);
	if ($blob === null) {
		fwrite(STDERR, "targz rebuild failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("{$dest}/{$tag}.tar.gz", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_gzip("{$tag}.tar.gz", $blob, $m, $r)
		&& (int) ($r["{$tag}.tar.gz"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
		&& ($r["{$tag}.tar.gz"]['format'] ?? '') === 'targz') {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic tar.gz peel — nested ustar+gzip shared English\n");

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
