#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_sg_dat_peel`: Shattered Galaxy .dat packs with
 * shared English so CLASSIC peel + FZCL beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_sg_dat_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_sg_dat_peel';

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
	$shared .= "Shared sg.dat peel prose {$i}: sprites, maps, and scripts repeat across packs.\n";
}
$shared = str_repeat($shared, 8);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$files = [];
	for ($i = 1; $i <= 12; $i++) {
		// SG names are ≤12 chars (+NUL) in the 13-byte field.
		$files[] = [
			'name' => sprintf('%s%02d.txt', substr($tag, 0, 1), $i),
			'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
		];
	}
	$blob = fractal_zip_classic_rebuild_archive('sg_dat', '', $files);
	if ($blob === null) {
		fwrite(STDERR, "sg_dat rebuild failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("{$dest}/{$tag}.dat", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_sg_dat("{$tag}.dat", $blob, $m, $r)
		&& (int) ($r["{$tag}.dat"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic sg.dat peel — deterministic index shared English across packs\n");

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
