#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_ar_peel`: deterministic GNU ar packs with shared
 * English across libraries so CLASSIC peel + FZCL beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_ar_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_ar_peel';

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
	$shared .= "Shared AR peel prose {$i}: object text, symbols, and notes repeat across libs.\n";
}
$shared = str_repeat($shared, 8);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$files = [];
	for ($i = 1; $i <= 16; $i++) {
		// GNU ar short names: max 15 chars + trailing slash in header field.
		$files[] = [
			'name' => sprintf('%s%02d.o', substr($tag, 0, 1), $i),
			'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
		];
	}
	$blob = fractal_zip_classic_rebuild_archive('ar', '', $files);
	if ($blob === null) {
		fwrite(STDERR, "ar rebuild failed for {$tag}\n");
		exit(1);
	}
	$path = "{$dest}/lib{$tag}.a";
	file_put_contents($path, $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_ar("lib{$tag}.a", $blob, $m, $r)
		&& (int) ($r["lib{$tag}.a"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic AR peel — deterministic GNU ar shared English across packs\n");

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
