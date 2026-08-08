#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_jpeg_peel`: classic archives whose lumps are real JPEGs
 * so peel → per-member jpeg-cm (lepton/brunsli) can beat opaque container coding.
 *
 *   php benchmarks/build_test_files_classic_jpeg_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_jpeg_peel';

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

/** @param list<array{name: string, data: string}> $lumps */
function classic_build_wad(string $path, string $id, array $lumps): void
{
	$payloads = '';
	$entries = [];
	foreach ($lumps as $l) {
		$entries[] = ['name' => $l['name'], 'off' => 12 + strlen($payloads), 'data' => $l['data']];
		$payloads .= $l['data'];
	}
	$tableOff = 12 + strlen($payloads);
	$dir = '';
	foreach ($entries as $e) {
		$dir .= pack('V', $e['off']) . pack('V', strlen($e['data']))
			. substr(str_pad($e['name'], 8, "\0"), 0, 8);
	}
	file_put_contents($path, $id . pack('V', count($entries)) . pack('V', $tableOff) . $payloads . $dir);
}

/** @param list<array{name: string, data: string}> $files */
function classic_build_pak(string $path, array $files): void
{
	$payload = '';
	$dir = '';
	foreach ($files as $f) {
		$off = 12 + strlen($payload);
		$payload .= $f['data'];
		$dir .= substr(str_pad($f['name'], 56, "\0"), 0, 56)
			. pack('V', $off) . pack('V', strlen($f['data']));
	}
	file_put_contents($path, 'PACK' . pack('V', 12 + strlen($payload)) . pack('V', strlen($dir)) . $payload . $dir);
}

// WAD lump names are 8 chars — use .jpg suffix where possible (TEX01.jpg = 8 chars).
classic_build_wad($dest . '/sprites.wad', 'PWAD', [
	['name' => 'FIRE.JPG', 'data' => $jpgs[0]],
	['name' => 'GRD1.JPG', 'data' => $jpgs[1]],
	['name' => 'GRD2.JPG', 'data' => $jpgs[2]],
	['name' => 'NOISE', 'data' => random_bytes(64)],
]);

classic_build_pak($dest . '/gfx.pak', [
	['name' => 'gfx/grid3.jpg', 'data' => $jpgs[3]],
	['name' => 'gfx/plasma1.jpg', 'data' => $jpgs[4]],
	['name' => 'gfx/plasma2.jpg', 'data' => $jpgs[5]],
	['name' => 'gfx/readme.txt', 'data' => "classic jpeg peel — textures in PAK\n"],
]);

file_put_contents($dest . '/00_README.txt', "classic jpeg peel — JPEG lumps in WAD/PAK for jpeg-cm after peel\n");

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
