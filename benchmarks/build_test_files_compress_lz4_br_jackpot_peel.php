#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_lz4_br_jackpot_peel`: shared English wrapped as
 * lz4 + brotli so CLASSIC rewrap + cross-inner dedupe yields a large FZCL win.
 *
 *   php benchmarks/build_test_files_compress_lz4_br_jackpot_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_lz4_br_jackpot_peel';

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
for ($i = 0; $i < 60; $i++) {
	$shared .= "Shared lz4/br jackpot prose {$i}: textures, scripts, briefings repeat across wrappers.\n";
}
$shared = str_repeat($shared, 12);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$body = "TAG={$tag}\n" . $shared . "UNIQUE={$tag}\nEND={$tag}\n";
	$wraps = [
		'lz4' => fractal_zip_folder_lz4_shell_compress($body, 3),
		'br' => fractal_zip_folder_brotli_shell_compress($body, 6),
	];
	foreach ($wraps as $ext => $blob) {
		if ($blob === null) {
			fwrite(STDERR, "wrap failed {$tag}.{$ext}\n");
			exit(1);
		}
		$path = "{$dest}/{$tag}.txt.{$ext}";
		file_put_contents($path, $blob);
		$m = [];
		$r = [];
		$bn = basename($path);
		$ok = fractal_zip_folder_try_expand_compress($bn, $blob, $m, $r);
		if ($ok && (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$classic++;
		}
	}
}

file_put_contents("$dest/00_README.txt", "compress lz4+brotli jackpot — CLASSIC rewrap shared English\n");

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
