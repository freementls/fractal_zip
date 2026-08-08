#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_gzip_peel`: gzencode wrappers with shared English
 * inners so CLASSIC gzip rewrap + FZCL beats opaque coding.
 *
 *   php benchmarks/build_test_files_compress_gzip_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_gzip_peel';

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
for ($i = 0; $i < 50; $i++) {
	$shared .= "Shared gzip peel prose {$i}: textures, scripts, briefings repeat across packs.\n";
}
$shared = str_repeat($shared, 10);

$classic = 0;
$verbatim = 0;
foreach (['alpha', 'bravo', 'charlie', 'delta', 'echo'] as $tag) {
	$body = "TAG={$tag}\n" . $shared . "UNIQUE={$tag}\nEND={$tag}\n";
	$gz = gzencode($body, 6);
	if ($gz === false || $gz === '') {
		fwrite(STDERR, "gzencode failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("$dest/{$tag}.txt.gz", $gz);
}

file_put_contents("$dest/00_README.txt", "compress gzip peel — CLASSIC gzencode rewrap\n");

$m = [];
$r = [];
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..' || !is_file("$dest/$e")) {
		continue;
	}
	$sz = filesize("$dest/$e");
	echo sprintf("%8d  %s\n", $sz, $e);
	$bytes = file_get_contents("$dest/$e");
	if (str_ends_with($e, '.gz') && fractal_zip_folder_try_expand_gzip($e, $bytes, $m, $r)) {
		$k = (int) ($r[$e]['kind'] ?? -1);
		if ($k === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$classic++;
			echo "  CLASSIC fmt={$r[$e]['format']} lvl={$r[$e]['meta']}\n";
		} elseif ($k === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			$verbatim++;
			echo "  VERBATIM\n";
		}
	}
}
echo "classic={$classic} verbatim={$verbatim}\n";
