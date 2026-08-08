#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_peel`: shell-bzip2 wrappers so peel can
 * bit-exact rewrap (CLASSIC bz2) and beat opaque coding via cross-file
 * text dedupe on the inners.
 *
 *   php benchmarks/build_test_files_compress_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_peel';

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

if (!function_exists('fractal_zip_folder_bzip2_shell_compress')) {
	fwrite(STDERR, "bzip2 shell helper missing\n");
	exit(1);
}

$shared = '';
for ($i = 0; $i < 60; $i++) {
	$shared .= "Shared compress-peel prose line {$i}: textures, scripts, and briefings repeat across packs.\n";
}
$shared = str_repeat($shared, 12);

foreach (['alpha', 'bravo', 'charlie', 'delta', 'echo'] as $tag) {
	$body = "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-" . str_repeat($tag, 40) . "\nEND={$tag}\n";
	$bz = fractal_zip_folder_bzip2_shell_compress($body, 9);
	if ($bz === null) {
		fwrite(STDERR, "bzip2 failed for {$tag}\n");
		exit(1);
	}
	file_put_contents($dest . "/{$tag}.txt.bz2", $bz);
}

file_put_contents($dest . '/00_README.txt', "compress peel — bzip2 -9 wrappers (CLASSIC rewrap)\n");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize($dest . '/' . $e);
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total}\n";

$m = [];
$r = [];
$classic = 0;
$verbatim = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..' || !is_file("$dest/$e")) {
		continue;
	}
	$bytes = file_get_contents("$dest/$e");
	if (fractal_zip_folder_try_expand_compress($e, $bytes, $m, $r)) {
		$k = (int) ($r[$e]['kind'] ?? -1);
		if ($k === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$classic++;
			echo "CLASSIC {$e} lvl={$r[$e]['meta']}\n";
		} elseif ($k === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			$verbatim++;
			echo "VERBATIM {$e}\n";
		}
	}
}
echo "classic={$classic} verbatim={$verbatim} logical_members=" . count($m) . "\n";
