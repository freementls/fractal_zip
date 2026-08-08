#!/usr/bin/env php
<?php
/**
 * Build test_files61: synthetic multi-cell "sprite sheet" rasters (no third-party rips)
 * in several formats, then wrap bytes in common outer compressors (gzip, tar members,
 * zip, 7z, zstd, brotli, xz, bzip2, arc) plus a small .fz from fractal_zip::zip_folder.
 *
 * Requires: ImageMagick `convert`, `tar`, `gzip`, `zip`. Optional: `7z`, `zstd`,
 * `brotli`, `xz`, `bzip2`, `arc` (PATH).
 *
 * Default presets are CPU-light (gzip/zip low level, zstd -3, 7z store, skip xz/bz2).
 * Set TF61_SLOW_CODECS=1 to also emit .tar.xz / .tar.bz2 (heavier).
 * Set TF61_ARC=1 to build raster_formats.arc (some `arc` implementations are very slow on binaries).
 *
 * Usage: php benchmarks/build_test_files61.php
 */
declare(strict_types=1);

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$root = $repo . DIRECTORY_SEPARATOR . 'test_files61';
$slowCodecs = getenv('TF61_SLOW_CODECS') === '1';
$wantArc = getenv('TF61_ARC') === '1';

function sh(string $cmd, string $cwd = ''): void
{
	$descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
	$wd = $cwd !== '' ? $cwd : null;
	$p = proc_open($cmd, $descriptors, $pipes, $wd, null);
	if (!is_resource($p)) {
		throw new RuntimeException('proc_open failed: ' . $cmd);
	}
	fclose($pipes[0]);
	$out = stream_get_contents($pipes[1]);
	$err = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$code = proc_close($p);
	if ($code !== 0) {
		throw new RuntimeException("Command failed ($code): $cmd\n$err\n$out");
	}
}

/**
 * @param list<string> $argv
 */
function run_argv(array $argv, ?string $cwd = null): void
{
	$descriptors = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
	$p = proc_open($argv, $descriptors, $pipes, $cwd, null);
	if (!is_resource($p)) {
		throw new RuntimeException('proc_open failed: ' . implode(' ', $argv));
	}
	fclose($pipes[0]);
	$out = stream_get_contents($pipes[1]);
	$err = stream_get_contents($pipes[2]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$code = proc_close($p);
	if ($code !== 0) {
		throw new RuntimeException("Command failed ($code): " . implode(' ', $argv) . "\n$err\n$out");
	}
}

function which(string $name): ?string
{
	$p = trim((string) shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));
	return $p !== '' ? $p : null;
}

function rrmdir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
	}
	rmdir($dir);
}

function make_grid_png(string $out, int $sheetId): void
{
	$w = 288;
	$h = 192;
	$cw = 24;
	$ch = 24;
	$argv = ['convert', '-size', $w . 'x' . $h, 'xc:#12121c'];
	for ($row = 0; $row < (int) ($h / $ch); $row++) {
		for ($col = 0; $col < (int) ($w / $cw); $col++) {
			$k = (($sheetId * 1103515245 + 12345) ^ ($row * 131 + $col * 911)) & 0xffffff;
			$r = ($k >> 16) & 0xff;
			$g = ($k >> 8) & 0xff;
			$b = $k & 0xff;
			$color = sprintf('#%02x%02x%02x', $r, $g, $b);
			$x1 = $col * $cw;
			$y1 = $row * $ch;
			$x2 = $x1 + $cw - 1;
			$y2 = $y1 + $ch - 1;
			$argv[] = '-fill';
			$argv[] = $color;
			$argv[] = '-draw';
			$argv[] = "rectangle {$x1},{$y1} {$x2},{$y2}";
		}
	}
	$argv[] = '-fill';
	$argv[] = 'none';
	$argv[] = '-stroke';
	$argv[] = 'rgba(255,255,255,0.12)';
	$argv[] = '-strokewidth';
	$argv[] = '1';
	for ($x = 0; $x <= $w; $x += $cw) {
		$argv[] = '-draw';
		$argv[] = "line {$x},0 {$x},{$h}";
	}
	for ($y = 0; $y <= $h; $y += $ch) {
		$argv[] = '-draw';
		$argv[] = "line 0,{$y} {$w},{$y}";
	}
	$argv[] = 'PNG32:' . $out;
	run_argv($argv);
}

function make_plasma_png(string $out, int $seed): void
{
	$cmd = sprintf(
		'convert -seed %d -size 256x128 plasma:fractal -blur 0x0.5 -colors 48 -type TrueColorAlpha PNG32:%s',
		$seed,
		escapeshellarg($out)
	);
	sh($cmd);
}

$convert = which('convert');
if ($convert === null) {
	fwrite(STDERR, "build_test_files61: ImageMagick `convert` not found; install imagemagick.\n");
	exit(1);
}

if (is_dir($root)) {
	rrmdir($root);
}
mkdir($root, 0755, true);

$d0 = $root . DIRECTORY_SEPARATOR . '00_source_png';
$d1 = $root . DIRECTORY_SEPARATOR . '01_raster_formats';
$d2 = $root . DIRECTORY_SEPARATOR . '02_gzip_members';
$d3 = $root . DIRECTORY_SEPARATOR . '03_tarballs';
$d4 = $root . DIRECTORY_SEPARATOR . '04_nested';
$d5 = $root . DIRECTORY_SEPARATOR . '05_fzc_inner_payload';
foreach ([$d0, $d1, $d2, $d3, $d4, $d5] as $d) {
	mkdir($d, 0755, true);
}

$readme = <<<'TXT'
test_files61 — synthetic "sprite sheet" stress corpus
======================================================

Raster grids and plasma textures only (no game rips). Style reference only for
community sprite archives such as The Spriters Resource:
https://www.spriters-resource.com/

Each PNG is re-encoded to GIF, WebP, JPEG, and BMP via ImageMagick, then some
paths are wrapped again in gzip, tar, zip, 7z (store), zstd, brotli, or arc where
the build host had the tool. Optional .tar.xz / .tar.bz2 (TF61_SLOW_CODECS=1), .arc (TF61_ARC=1).
A small .fz is produced with fractal_zip::zip_folder (single-pass).

Regenerate (light): php benchmarks/build_test_files61.php
TXT;
file_put_contents($root . DIRECTORY_SEPARATOR . 'README.txt', $readme);

// Source PNGs: 4 grids + 3 plasma
for ($i = 1; $i <= 4; $i++) {
	make_grid_png($d0 . DIRECTORY_SEPARATOR . sprintf('grid_%02d.png', $i), $i * 997);
}
foreach ([1 => 42, 2 => 404, 3 => 9001] as $j => $seed) {
	make_plasma_png($d0 . DIRECTORY_SEPARATOR . sprintf('plasma_%02d.png', $j), $seed);
}

$pngs = glob($d0 . DIRECTORY_SEPARATOR . '*.png') ?: [];
sort($pngs, SORT_STRING);

foreach ($pngs as $png) {
	$base = pathinfo($png, PATHINFO_FILENAME);
	$gif = $d1 . DIRECTORY_SEPARATOR . $base . '.gif';
	$webp = $d1 . DIRECTORY_SEPARATOR . $base . '.webp';
	$jpg = $d1 . DIRECTORY_SEPARATOR . $base . '.jpg';
	$bmp = $d1 . DIRECTORY_SEPARATOR . $base . '.bmp';
	sh(sprintf(
		'convert %s -colors 256 %s',
		escapeshellarg($png),
		escapeshellarg($gif)
	));
	sh(sprintf(
		'convert %s -quality 82 %s',
		escapeshellarg($png),
		escapeshellarg($webp)
	));
	sh(sprintf(
		'convert %s -quality 88 %s',
		escapeshellarg($png),
		escapeshellarg($jpg)
	));
	sh(sprintf(
		'convert %s BMP3:%s',
		escapeshellarg($png),
		escapeshellarg($bmp)
	));
	copy($png, $d1 . DIRECTORY_SEPARATOR . $base . '.png');
}

// gzip -9 each raster in 02 (copy then gzip -k behavior via shell)
$allRaster = glob($d1 . DIRECTORY_SEPARATOR . '*') ?: [];
foreach ($allRaster as $f) {
	if (!is_file($f)) {
		continue;
	}
	$bn = basename($f);
	copy($f, $d2 . DIRECTORY_SEPARATOR . $bn);
	sh('gzip -2 -n -c ' . escapeshellarg($f) . ' > ' . escapeshellarg($d2 . DIRECTORY_SEPARATOR . $bn . '.gz'));
}

// Tarballs / zip / 7z / zstd / brotli / xz / bzip2 in 03
sh('tar -cf ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.tar') . ' -C ' . escapeshellarg($d1) . ' .');
sh('gzip -2 -n -c ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.tar') . ' > ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.tar.gz'));
unlink($d3 . DIRECTORY_SEPARATOR . 'raster_formats.tar');

if (which('zstd') !== null) {
	sh('tar -cf ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'source_only.tar') . ' -C ' . escapeshellarg($d0) . ' .');
	sh('zstd -3 -f ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'source_only.tar') . ' -o ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'source_only.tar.zst'));
	unlink($d3 . DIRECTORY_SEPARATOR . 'source_only.tar');
} else {
	sh('tar -czf ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'source_only.tar.gz') . ' -C ' . escapeshellarg($d0) . ' .');
}

if ($slowCodecs && which('xz') !== null) {
	putenv('XZ_OPT=-0');
	sh('tar -cJf ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.tar.xz') . ' -C ' . escapeshellarg($d1) . ' .');
	putenv('XZ_OPT');
}
if ($slowCodecs && which('bzip2') !== null) {
	sh('tar --use-compress-program=' . escapeshellarg('bzip2 -1') . ' -cf ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.tar.bz2') . ' -C ' . escapeshellarg($d1) . ' .');
}

if (which('brotli') !== null) {
	sh('tar -cf ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'br_in.tar') . ' -C ' . escapeshellarg($d1) . ' .');
	sh('brotli -q 4 -f -o ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.tar.br') . ' ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'br_in.tar'));
	unlink($d3 . DIRECTORY_SEPARATOR . 'br_in.tar');
}

sh('zip -2 -r -q ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.zip') . ' .', $d1);

if (which('7z') !== null) {
	// Store mode: valid 7z container without multi‑minute ultra compression on photos/BMPs.
	sh(
		'7z a -mx=0 -t7z ' . escapeshellarg($d3 . DIRECTORY_SEPARATOR . 'raster_formats.7z') . ' .',
		$d1
	);
}

if ($wantArc && which('arc') !== null) {
	$arcPath = $d3 . DIRECTORY_SEPARATOR . 'raster_formats.arc';
	sh('arc a ' . escapeshellarg($arcPath) . ' .', $d1);
}

// Nested: inner zip of PNG sources, outer .tar.gz (repo-local temp: /tmp may be tiny tmpfs)
$nestedWork = $d4 . DIRECTORY_SEPARATOR . '_nested_build_' . bin2hex(random_bytes(4));
mkdir($nestedWork, 0755, true);
try {
	sh('zip -2 -r -q ' . escapeshellarg($nestedWork . DIRECTORY_SEPARATOR . 'inner_pngs.zip') . ' .', $d0);
	sh('tar -czf ' . escapeshellarg($d4 . DIRECTORY_SEPARATOR . 'outer_tar_gz_contains_inner_zip.tar.gz') . ' -C ' . escapeshellarg($nestedWork) . ' inner_pngs.zip');
} finally {
	rrmdir($nestedWork);
}

// Second nest: .gz member that is itself a small text blob (gzip literal chain)
file_put_contents($d4 . DIRECTORY_SEPARATOR . 'payload.txt', str_repeat("sprite sheet benchmark padding\n", 400));
sh('gzip -2 -n ' . escapeshellarg($d4 . DIRECTORY_SEPARATOR . 'payload.txt'));

// fzc: copy subset into 05 and zip_folder
foreach (array_slice($pngs, 0, 4) as $p) {
	copy($p, $d5 . DIRECTORY_SEPARATOR . basename($p));
}
$oneGif = $d1 . DIRECTORY_SEPARATOR . pathinfo($pngs[0], PATHINFO_FILENAME) . '.gif';
if (is_file($oneGif)) {
	copy($oneGif, $d5 . DIRECTORY_SEPARATOR . basename($oneGif));
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
// Single-pass, fixed segment: keeps zip_folder cheap on this tiny payload.
$fzp = new fractal_zip(300, false, false, null, false);
ob_start();
$fzp->zip_folder($d5, false);
ob_end_clean();
$fzcSrc = $d5 . '.fz';
if (!is_file($fzcSrc)) {
	throw new RuntimeException('Expected .fz next to payload dir: ' . $fzcSrc);
}
$fzcDest = $root . DIRECTORY_SEPARATOR . '06_fractal_container' . DIRECTORY_SEPARATOR . 'mini_raster_bundle.fz';
mkdir(dirname($fzcDest), 0755, true);
if (!rename($fzcSrc, $fzcDest)) {
	copy($fzcSrc, $fzcDest);
	unlink($fzcSrc);
}

$bytes = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
	if ($f->isFile()) {
		$bytes += $f->getSize();
	}
}
fwrite(STDOUT, "test_files61 built under {$root} (" . number_format($bytes) . " bytes total).\n");
