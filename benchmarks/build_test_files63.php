#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Build test_files63: small corpus to stress literal-bundle modes 12 / 13 / 14 (reverse, BMP column-vertical
 * delta, square-prefix transpose). Run from repo root:
 *
 *   php benchmarks/build_test_files63.php
 *
 * Requires test_files61 for one reference BMP (grid); other payloads are generated here.
 * For benchmark .fz ~single-file grid only, use test_files64 (see benchmarks/build_test_files64.php).
 */

$repo = dirname(__DIR__);
$dst = $repo . DIRECTORY_SEPARATOR . 'test_files63';
$maxBytes = 4 * 1024 * 1024;

/**
 * BI_RGB 24bpp BMP, vertical stripes (column-constant BGR); mode 13 favors column-vertical delta.
 */
function bmp24_vertical_stripes(int $w, int $h): string
{
	$rowStride = (int) (((($w * 24 + 31) >> 5) << 2));
	$pix = '';
	$colors = array();
	for ($x = 0; $x < $w; $x++) {
		$colors[] = chr(($x * 17) % 256) . chr(($x * 31) % 256) . chr(($x * 47) % 256);
	}
	for ($y = 0; $y < $h; $y++) {
		$row = '';
		for ($x = 0; $x < $w; $x++) {
			$row .= $colors[$x];
		}
		$row .= str_repeat("\0", $rowStride - strlen($row));
		$pix .= $row;
	}
	$imgSize = strlen($pix);
	$offBits = 54;
	$fileSize = $offBits + $imgSize;
	$hdr = 'BM' . pack('V', $fileSize) . pack('vv', 0, 0) . pack('V', $offBits);
	$dib = pack('V3vvV6', 40, $w, $h, 1, 24, 0, $imgSize, 0, 0, 0, 0);
	return $hdr . $dib . $pix;
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
		$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
	}
	@rmdir($dir);
}

function write_bytes(string $path, string $bytes): void
{
	$parent = dirname($path);
	if (!is_dir($parent)) {
		mkdir($parent, 0755, true);
	}
	if (file_put_contents($path, $bytes) === false) {
		throw new RuntimeException('write failed: ' . $path);
	}
}

rrmdir($dst);
mkdir($dst, 0755, true);

$total = 0;

$plan = array(
	// Sparse row-major squares where gzdeflate-1 favors mode 14 (see examples/fzb_literal_transform_win_demo.php).
	array('rel' => '01_mode14/demo_ascii_3x3.txt', 'bytes' => '000100000'),
	array('rel' => '01_mode14/demo_ascii_4x4.txt', 'bytes' => '0000100000000000'),
	array('rel' => '01_mode14/demo_ascii_5x5.txt', 'bytes' => '0000010000000000000000000'),
	array('rel' => '01_mode14/demo_ascii_3x3_with_tail.bin', 'bytes' => '000100000' . str_repeat("\xff", 120)),
	array(
		'rel' => '02_mode12/reverse_win_25.bin',
		'bytes' => hex2bin('0b3d21ff0b3d2179afaa8e2aa05152b578d23fc0412b626e78'),
	),
	array(
		'rel' => '02_mode12/reverse_win_25_x4.bin',
		'bytes' => str_repeat(hex2bin('0b3d21ff0b3d2179afaa8e2aa05152b578d23fc0412b626e78'), 4),
	),
	array('rel' => '03_mode13_bmp/stripes_16x16.bmp', 'bytes' => bmp24_vertical_stripes(16, 16)),
	array('rel' => '03_mode13_bmp/stripes_24x24.bmp', 'bytes' => bmp24_vertical_stripes(24, 24)),
	array('rel' => '03_mode13_bmp/stripes_32x32.bmp', 'bytes' => bmp24_vertical_stripes(32, 32)),
	array('rel' => '03_mode13_bmp/stripes_48x48.bmp', 'bytes' => bmp24_vertical_stripes(48, 48)),
);

foreach ($plan as $item) {
	$sz = strlen($item['bytes']);
	if ($total + $sz > $maxBytes) {
		fwrite(STDERR, "skip (cap): {$item['rel']}\n");
		continue;
	}
	$out = $dst . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item['rel']);
	write_bytes($out, $item['bytes']);
	$total += $sz;
}

$gridSrc = $repo . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '01_raster_formats' . DIRECTORY_SEPARATOR . 'grid_01.bmp';
if (is_file($gridSrc)) {
	$gridDst = $dst . DIRECTORY_SEPARATOR . '04_reference' . DIRECTORY_SEPARATOR . 'grid_01.bmp';
	if (!is_dir(dirname($gridDst))) {
		mkdir(dirname($gridDst), 0755, true);
	}
	$sz = filesize($gridSrc);
	if (is_int($sz) && $total + $sz <= $maxBytes) {
		if (!copy($gridSrc, $gridDst)) {
			throw new RuntimeException('copy failed: ' . $gridSrc);
		}
		$total += $sz;
	}
} else {
	fwrite(STDERR, "skip missing reference: {$gridSrc}\n");
}

$final = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dst, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
	if ($f->isFile()) {
		$final += $f->getSize();
	}
}

fwrite(STDOUT, "test_files63 built under {$dst}\n");
fwrite(STDOUT, 'total bytes: ' . number_format($final) . " (cap " . number_format($maxBytes) . ")\n");
