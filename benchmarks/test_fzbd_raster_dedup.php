<?php
declare(strict_types=1);

/**
 * Regression: same pixels as PNG + BMP → one FZBD FZRC1 table entry, mode 10 members, round-trip restores on-disk bytes.
 *
 *   php benchmarks/test_fzbd_raster_dedup.php
 *
 * Size vs gzip9/7z/tar|zstd (same corpus): php benchmarks/bench_fzbd_vs_baselines.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

putenv('FRACTAL_ZIP_RASTER_CANONICAL=1');
putenv('FRACTAL_ZIP_RASTER_CANONICAL_BUNDLE=1');

if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor')) {
	fwrite(STDERR, "SKIP: PHP GD not available.\n");
	exit(0);
}
if (!function_exists('imagebmp')) {
	fwrite(STDERR, "SKIP: imagebmp() not available (need PHP 7.2+).\n");
	exit(0);
}

$im = imagecreatetruecolor(8, 8);
if ($im === false) {
	fwrite(STDERR, "FAIL: imagecreatetruecolor\n");
	exit(1);
}
imagesavealpha($im, true);
$bg = imagecolorallocatealpha($im, 40, 80, 120, 0);
imagefill($im, 0, 0, $bg);
ob_start();
if (!imagepng($im)) {
	imagedestroy($im);
	fwrite(STDERR, "FAIL: imagepng\n");
	exit(1);
}
$pngBytes = ob_get_clean();
ob_start();
if (!imagebmp($im)) {
	imagedestroy($im);
	fwrite(STDERR, "FAIL: imagebmp\n");
	exit(1);
}
$bmpBytes = ob_get_clean();
imagedestroy($im);

if ($pngBytes === '' || $bmpBytes === '' || $pngBytes === $bmpBytes) {
	fwrite(STDERR, "FAIL: expected distinct PNG and BMP encodings of the same raster.\n");
	exit(1);
}

$c1 = fractal_zip_raster_canonical_try('x.png', $pngBytes);
$c2 = fractal_zip_raster_canonical_try('y.bmp', $bmpBytes);
if ($c1 === null || $c2 === null || $c1 !== $c2) {
	fwrite(STDERR, "FAIL: canonical FZRC1 mismatch or decode unsupported (Imagick/GD path).\n");
	exit(1);
}

$fz = new fractal_zip();
$payload = $fz->encode_literal_bundle_payload(array(
	'a.png' => $pngBytes,
	'b.bmp' => $bmpBytes,
), array('raster_dedup_policy' => 'always'));
if (!is_string($payload) || $payload === '') {
	fwrite(STDERR, "FAIL: encode_literal_bundle_payload returned empty.\n");
	exit(1);
}
if (strlen($payload) < 10 || substr($payload, 0, 4) !== 'FZBD' || ord($payload[4]) !== 1) {
	fwrite(STDERR, "FAIL: expected FZBD v1 prefix, got: " . bin2hex(substr($payload, 0, 8)) . "\n");
	exit(1);
}

$off = 5;
$n = strlen($payload);
$nt = $fz->decode_varint_u32($payload, $off, $n, 'test FZBD');
if ($nt !== 1) {
	fwrite(STDERR, "FAIL: FZBD table count expected 1, got {$nt}.\n");
	exit(1);
}
$bl = $fz->decode_varint_u32($payload, $off, $n, 'test FZBD blob');
if ($bl < 1 || $off + $bl > $n) {
	fwrite(STDERR, "FAIL: FZBD blob length.\n");
	exit(1);
}
$canonBlob = substr($payload, $off, $bl);
$off += $bl;
if (substr($canonBlob, 0, 5) !== 'FZRC1') {
	fwrite(STDERR, "FAIL: table entry should be FZRC1.\n");
	exit(1);
}
$body = substr($payload, $off);
if (substr($body, 0, 4) !== 'FZB4') {
	fwrite(STDERR, "FAIL: expected FZB4 body after FZBD, got " . substr($body, 0, 4) . "\n");
	exit(1);
}

$fzDec = new fractal_zip();
$decoded = $fzDec->decode_container_payload($payload);
if (!is_array($decoded) || !isset($decoded[0]) || !is_array($decoded[0])) {
	fwrite(STDERR, "FAIL: decode_container_payload shape.\n");
	exit(1);
}
$files = $decoded[0];
foreach (array('a.png' => $pngBytes, 'b.bmp' => $bmpBytes) as $rel => $expect) {
	if (!array_key_exists($rel, $files)) {
		fwrite(STDERR, "FAIL: missing path {$rel} in decoded map.\n");
		exit(1);
	}
	$got = $fzDec->unzip($files[$rel]);
	if ($got !== $expect) {
		fwrite(STDERR, "FAIL: round-trip bytes differ for {$rel} (len got " . strlen($got) . " vs " . strlen($expect) . ").\n");
		exit(1);
	}
}

fwrite(STDOUT, "OK: FZBD single FZRC1 entry, a.png and b.bmp round-trip (" . strlen($payload) . " B bundle payload).\n");
exit(0);
