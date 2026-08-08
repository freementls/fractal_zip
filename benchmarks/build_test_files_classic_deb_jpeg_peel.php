#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_deb_jpeg_peel`: .deb (classic ar) whose data.tar
 * holds real JPEGs so peel → jpeg-cm beats opaque container coding.
 *
 *   php benchmarks/build_test_files_classic_deb_jpeg_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_deb_jpeg_peel';

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
$dataFiles = [];
foreach ($sources as $i => $p) {
	if (!is_file($p)) {
		fwrite(STDERR, "missing jpeg: {$p}\n");
		exit(1);
	}
	$dataFiles[] = [
		'name' => sprintf('usr/share/pixmaps/j%02d.jpg', $i + 1),
		'data' => file_get_contents($p),
	];
}
$control = fractal_zip_classic_rebuild_archive('tar', '', [
	['name' => 'control', 'data' => "Package: demo-tex\nVersion: 1.0-1\nArchitecture: all\nDescription: jpeg peel deb\n"],
]);
$data = fractal_zip_classic_rebuild_archive('tar', '', $dataFiles);
if ($control === null || $data === null) {
	fwrite(STDERR, "tar rebuild failed\n");
	exit(1);
}
$deb = fractal_zip_classic_rebuild_archive('ar', '', [
	['name' => 'debian-binary', 'data' => "2.0\n"],
	['name' => 'control.tar', 'data' => $control],
	['name' => 'data.tar', 'data' => $data],
]);
if ($deb === null) {
	fwrite(STDERR, "deb rebuild failed\n");
	exit(1);
}
file_put_contents("$dest/demo-tex.deb", $deb);
file_put_contents("$dest/00_README.txt", "classic deb jpeg peel — JPEG members inside data.tar\n");

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
$ok = fractal_zip_folder_try_expand_ar('demo-tex.deb', $deb, $m, $r);
echo 'peel=' . ($ok ? '1' : '0') . ' kind=' . ($r['demo-tex.deb']['kind'] ?? '?') . ' members=' . count($m) . "\n";
