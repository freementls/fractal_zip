#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Build test_files64: single member — grid_01.bmp only (same asset as test_files61 / test_files63 reference).
 * Benchmark .fz is ~1.3 KiB (BMP literal chain + outer brotli), distinct from multi-file test_files63 (~1.8+ KiB).
 *
 *   php benchmarks/build_test_files64.php
 */
$repo = dirname(__DIR__);
$src = $repo . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '01_raster_formats' . DIRECTORY_SEPARATOR . 'grid_01.bmp';
$dstDir = $repo . DIRECTORY_SEPARATOR . 'test_files64';
$dst = $dstDir . DIRECTORY_SEPARATOR . 'grid_01.bmp';

if (!is_file($src)) {
	fwrite(STDERR, "missing source: {$src}\n");
	exit(1);
}
if (!is_dir($dstDir) && !mkdir($dstDir, 0755, true)) {
	fwrite(STDERR, "mkdir failed: {$dstDir}\n");
	exit(1);
}
if (!copy($src, $dst)) {
	fwrite(STDERR, "copy failed\n");
	exit(1);
}
fwrite(STDOUT, "wrote {$dst}\n");
