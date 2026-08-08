<?php
/**
 * Reproducible gzip-1 probe wins for literal-bundle modes 12 / 13 / 14 (see fractal_zip.php).
 *
 *   php fzb_literal_transform_win_demo.php
 *
 * Mode 14: 3×3 prefix where row-major is a poor match for LZ, transpose groups runs (ASCII demo).
 * Mode 12: fixed 25-byte payload where strrev improves gzdeflate-1 (found by random search).
 * Mode 13: 24×24 vertical-stripe 24bpp BMP where column-vertical delta beats row-horizontal + raw.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';

function assert_true(bool $ok, string $msg): void {
	if (!$ok) {
		fwrite(STDERR, "FAIL: {$msg}\n");
		exit(1);
	}
}

/** Minimal BI_RGB 24bpp BMP, vertical stripes (same BGR triple per column). */
function bmp24_vertical_stripes(int $w, int $h): string {
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

$fz = new fractal_zip(null, true, false, null, false);

// --- Mode 14 (square-block transpose) ---
$raw14 = '000100000';
$t14 = $fz->literal_square_block_transpose_prefix($raw14);
assert_true($t14 !== null && $t14 === '010000000', 'mode 14 transpose mismatch');
$z14raw = gzdeflate($raw14, 1);
$z14t = gzdeflate($t14, 1);
assert_true($z14raw !== false && $z14t !== false, 'mode 14 gzdeflate');
assert_true(strlen($z14t) < strlen($z14raw), 'mode 14: transposed should deflate smaller than raw');
list($m14,) = $fz->choose_best_literal_bundle_transform($raw14, 'demo14.bin');
assert_true($m14 === 14, 'choose_best should pick mode 14 for demo payload');

// --- Mode 12 (full-buffer reverse) ---
$raw12 = hex2bin('0b3d21ff0b3d2179afaa8e2aa05152b578d23fc0412b626e78');
$rev12 = strrev($raw12);
$z12raw = gzdeflate($raw12, 1);
$z12rev = gzdeflate($rev12, 1);
assert_true($z12raw !== false && $z12rev !== false, 'mode 12 gzdeflate');
assert_true(strlen($z12rev) < strlen($z12raw), 'mode 12: reversed should deflate smaller');
list($m12,) = $fz->choose_best_literal_bundle_transform($raw12, 'demo12.bin');
assert_true($m12 === 12, 'choose_best should pick mode 12 for demo payload');

// --- Mode 13 (BMP column-vertical delta) ---
$bmp = bmp24_vertical_stripes(24, 24);
$enc5 = $fz->encode_bmp_row_horizontal_delta_payload($bmp);
$enc13 = $fz->encode_bmp_column_vertical_delta_payload($bmp);
assert_true($enc5 !== null && $enc13 !== null, 'BMP literal encoders should accept demo image');
$z0 = gzdeflate($bmp, 1);
$z5 = gzdeflate($enc5, 1);
$z13 = gzdeflate($enc13, 1);
assert_true($z0 !== false && $z5 !== false && $z13 !== false, 'mode 13 gzdeflate');
assert_true(strlen($z13) < strlen($z0) && strlen($z13) <= strlen($z5), 'mode 13 should beat or tie raw and beat row-delta on this stripe pattern');
list($mBmp, $stBmp) = $fz->choose_best_literal_bundle_transform($bmp, 'demo13.bmp');
assert_true($mBmp === 13 || $mBmp === 15, 'choose_best should pick mode 13 or 15 for vertical-stripe BMP (15 = uniform grid BSS1 when smaller under gzip-1)');

// Round-trips
$d14 = $fz->decode_bundle_literal_member(14, $t14);
assert_true($d14 === $raw14, 'mode 14 decode');
list(, $st12) = $fz->choose_best_literal_bundle_transform($raw12, 'demo12b.bin');
assert_true($fz->decode_bundle_literal_member(12, $st12) === $raw12, 'mode 12 decode');
assert_true($fz->decode_bundle_literal_member(13, $enc13) === $bmp, 'mode 13 decode');
assert_true($fz->decode_bundle_literal_member($mBmp, $stBmp) === $bmp, 'BMP choose_best decode');

echo "OK — literal transform demos (gzdeflate-1 / choose_best):\n";
echo '  mode 14: raw z=' . strlen($z14raw) . ' transposed z=' . strlen($z14t) . " payload ASCII \"{$raw14}\"\n";
	echo '  mode 12: raw z=' . strlen($z12raw) . ' reversed z=' . strlen($z12rev) . ' hex ' . bin2hex($raw12) . "\n";
	echo '  mode 13: raw z=' . strlen($z0) . ' row-delta z=' . strlen($z5) . ' col-delta z=' . strlen($z13) . ' BMP ' . (string) strlen($bmp) . " B; choose_best mode {$mBmp}\n";
