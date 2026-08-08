#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_debgz_jpeg_peel`: .deb with gzipped data.tar.gz of
 * real JPEGs so nested CLASSIC → jpeg-cm beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_debgz_jpeg_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_debgz_jpeg_peel';

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
$payloads = [
	['name' => 'debian-binary', 'data' => "2.0\n"],
	['name' => 'control/control', 'data' => "Package: demo-tex\nVersion: 1.0-1\nArchitecture: all\nDescription: jpeg peel deb.gz\n"],
];
foreach ($sources as $i => $p) {
	if (!is_file($p)) {
		fwrite(STDERR, "missing jpeg: {$p}\n");
		exit(1);
	}
	$payloads[] = [
		'name' => sprintf('data/usr/share/pixmaps/j%02d.jpg', $i + 1),
		'data' => file_get_contents($p),
	];
}
$deb = fractal_zip_classic_rebuild_archive('debgz', '6', $payloads);
if ($deb === null) {
	fwrite(STDERR, "debgz rebuild failed\n");
	exit(1);
}
file_put_contents("$dest/demo-tex.deb", $deb);
file_put_contents("$dest/00_README.txt", "classic deb.gz jpeg peel — nested gzipped data.tar JPEGs\n");

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
echo 'peel=' . ($ok ? '1' : '0') . ' kind=' . ($r['demo-tex.deb']['kind'] ?? '?')
	. ' fmt=' . ($r['demo-tex.deb']['format'] ?? '') . ' members=' . count($m) . "\n";
