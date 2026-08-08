#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_xz_zstd_peel`: xz + zstd wrappers with shared
 * English inners so CLASSIC rewrap + FZCL beats opaque coding.
 *
 *   php benchmarks/build_test_files_compress_xz_zstd_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_xz_zstd_peel';

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
	$shared .= "Shared xz/zstd peel prose {$i}: textures, scripts, briefings repeat across packs.\n";
}
$shared = str_repeat($shared, 10);

$classic = 0;
$verbatim = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$body = "TAG={$tag}\n" . $shared . "UNIQUE={$tag}\nEND={$tag}\n";
	$xz = fractal_zip_folder_xz_shell_compress($body, 6);
	$zs = fractal_zip_folder_zstd_shell_compress($body, 3, true);
	if ($xz === null || $zs === null) {
		fwrite(STDERR, "compress failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("$dest/{$tag}.txt.xz", $xz);
	file_put_contents("$dest/{$tag}.txt.zst", $zs);
}

file_put_contents("$dest/00_README.txt", "compress xz/zstd peel — CLASSIC rewrap\n");

$m = [];
$r = [];
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..' || !is_file("$dest/$e")) {
		continue;
	}
	$sz = filesize("$dest/$e");
	echo sprintf("%8d  %s\n", $sz, $e);
	$bytes = file_get_contents("$dest/$e");
	if (fractal_zip_folder_try_expand_compress($e, $bytes, $m, $r)) {
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
