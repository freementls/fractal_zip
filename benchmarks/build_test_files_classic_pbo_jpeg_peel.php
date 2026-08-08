#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_pbo_jpeg_peel`: PBO whose members are real JPEGs
 * so peel → per-member jpeg-cm beats opaque container coding.
 *
 *   php benchmarks/build_test_files_classic_pbo_jpeg_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_pbo_jpeg_peel';

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

$sources = [
	$repo . '/test_files111/fireworks.jpeg',
	$repo . '/test_files168/grid_01.jpg',
	$repo . '/test_files168/grid_02.jpg',
	$repo . '/test_files168/grid_03.jpg',
	$repo . '/test_files168/plasma_01.jpg',
	$repo . '/test_files168/plasma_02.jpg',
];
$jpgs = [];
foreach ($sources as $p) {
	if (!is_file($p)) {
		fwrite(STDERR, "missing jpeg: {$p}\n");
		exit(1);
	}
	$jpgs[] = file_get_contents($p);
}

/** @param list<array{name: string, data: string}> $files */
function classic_build_pbo(string $path, array $files): void
{
	$header = '';
	$blob = '';
	foreach ($files as $f) {
		$name = str_replace('\\', '/', $f['name']);
		$data = $f['data'];
		$header .= $name . "\0\0" . pack('V', strlen($data));
		$blob .= $data;
	}
	file_put_contents($path, $header . "\0" . $blob);
}

classic_build_pbo($dest . '/textures.pbo', [
	['name' => 'tex/fireworks.jpg', 'data' => $jpgs[0]],
	['name' => 'tex/grid01.jpg', 'data' => $jpgs[1]],
	['name' => 'tex/grid02.jpg', 'data' => $jpgs[2]],
	['name' => 'tex/grid03.jpg', 'data' => $jpgs[3]],
	['name' => 'tex/plasma01.jpg', 'data' => $jpgs[4]],
	['name' => 'tex/plasma02.jpg', 'data' => $jpgs[5]],
	['name' => 'tex/readme.txt', 'data' => "classic pbo jpeg peel\n"],
]);

file_put_contents($dest . '/00_README.txt', "classic PBO jpeg peel — JPEG members for jpeg-cm after peel\n");

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

require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';
$bytes = file_get_contents($dest . '/textures.pbo');
$m = [];
$r = [];
$ok = fractal_zip_folder_try_expand_classic('textures.pbo', $bytes, $m, $r);
echo 'peel=' . ($ok ? '1' : '0') . ' members=' . count($m) . "\n";
if ($ok) {
	$payloads = [];
	foreach ($r['textures.pbo']['member_names'] as $n) {
		$payloads[] = ['name' => $n, 'data' => $m['textures.pbo/' . $n]];
	}
	$reb = fractal_zip_classic_rebuild_archive('pbo', '', $payloads);
	echo 'rebuild_ok=' . (($reb === $bytes) ? '1' : '0') . "\n";
}
