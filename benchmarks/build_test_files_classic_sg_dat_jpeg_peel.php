#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_sg_dat_jpeg_peel`: SG .dat whose members are real
 * JPEGs so peel → per-member jpeg-cm beats opaque container coding.
 *
 *   php benchmarks/build_test_files_classic_sg_dat_jpeg_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_sg_dat_jpeg_peel';

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

$sources = [
	$repo . '/test_files111/fireworks.jpeg',
	$repo . '/test_files168/grid_01.jpg',
	$repo . '/test_files168/grid_02.jpg',
	$repo . '/test_files168/grid_03.jpg',
	$repo . '/test_files168/plasma_01.jpg',
	$repo . '/test_files168/plasma_02.jpg',
];
$files = [];
foreach ($sources as $i => $p) {
	if (!is_file($p)) {
		fwrite(STDERR, "missing jpeg: {$p}\n");
		exit(1);
	}
	$files[] = [
		'name' => sprintf('j%02d.jpg', $i + 1),
		'data' => file_get_contents($p),
	];
}
$files[] = ['name' => 'readme.txt', 'data' => "classic sg.dat jpeg peel\n"];

$blob = fractal_zip_classic_rebuild_archive('sg_dat', '', $files);
if ($blob === null) {
	fwrite(STDERR, "sg_dat rebuild failed\n");
	exit(1);
}
file_put_contents("$dest/tex.dat", $blob);
file_put_contents("$dest/00_README.txt", "classic sg.dat jpeg peel — JPEG members for jpeg-cm after peel\n");

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

$m = [];
$r = [];
$ok = fractal_zip_folder_try_expand_sg_dat('tex.dat', $blob, $m, $r);
echo 'peel=' . ($ok ? '1' : '0') . ' kind=' . ($r['tex.dat']['kind'] ?? '?') . ' members=' . count($m) . "\n";
