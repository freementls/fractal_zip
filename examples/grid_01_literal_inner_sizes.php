#!/usr/bin/env php
<?php
/**
 * Report literal inner sizes for grid_01.bmp vs a synthetic uniform-shift grid (mode 15 / BSS1).
 *
 *   php examples/grid_01_literal_inner_sizes.php
 *
 * grid_01 is 12×8 cells of 24×24 px with anti-aliased edges; it does not match template+per-cell
 * scalar/BGR shift vs the top-left cell, so mode 15 does not apply. A procedural test BMP does.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';

function bmp_synthetic_uniform_shift_grid(): string
{
	$w = 120;
	$h = 80;
	$cw = 10;
	$ch = 10;
	$rowStride = (int) (((($w * 24 + 31) >> 5) << 2));
	$pix = '';
	for ($sy = 0; $sy < $h; $sy++) {
		$vy = $h - 1 - $sy;
		$br = intdiv($vy, $ch);
		$row = '';
		for ($vx = 0; $vx < $w; $vx++) {
			$bc = intdiv($vx, $cw);
			$d = ($br + $bc) * 7 & 255;
			$row .= chr((100 + $d) & 255) . chr((50 + $d) & 255) . chr((200 + $d) & 255);
		}
		$row .= str_repeat("\0", $rowStride - strlen($row));
		$pix .= $row;
	}
	$fs = 54 + strlen($pix);
	$hdr = 'BM' . pack('V', $fs) . pack('vv', 0, 0) . pack('V', 54);
	$dib = pack('V3vvV6', 40, $w, $h, 1, 24, 0, strlen($pix), 0, 0, 0, 0);
	return $hdr . $dib . $pix;
}

function row(string $label, int $rawLen, ?string $stored, ?int $mode = null, ?int $probeLev = null): void
{
	$inner = $stored === null ? '—' : (string) strlen($stored);
	$z1 = '—';
	$zProbe = '—';
	if ($stored !== null && $stored !== '') {
		$z = gzdeflate($stored, 1);
		$z1 = $z === false ? 'err' : (string) strlen($z);
		if ($probeLev !== null && $probeLev >= 1 && $probeLev <= 9) {
			$zp = gzdeflate($stored, $probeLev);
			$zProbe = $zp === false ? 'err' : (string) strlen($zp);
		}
	}
	$m = $mode === null ? '' : " choose_best={$mode}";
	$zpCol = ($probeLev !== null) ? "  gzdeflate-{$probeLev}_B={$zProbe}" : '';
	echo str_pad($label, 28) . " raw_B={$rawLen}  inner_B={$inner}  gzdeflate-1_B={$z1}{$zpCol}{$m}\n";
}

$fz = new fractal_zip(null, true, false, null, false);

$gridPath = dirname(__DIR__) . '/test_files61/01_raster_formats/grid_01.bmp';
$gridRaw = is_file($gridPath) ? (string) file_get_contents($gridPath) : '';

if ($gridRaw !== '') {
	$probeLev = $fz->literal_bundle_gzip_probe_level($gridRaw);
	$m5 = $fz->encode_bmp_row_horizontal_delta_payload($gridRaw);
	$m13 = $fz->encode_bmp_column_vertical_delta_payload($gridRaw);
	$m15 = $fz->encode_bmp_block_scalar_shift_payload($gridRaw);
	list($pick, $stPick) = $fz->choose_best_literal_bundle_transform($gridRaw, 'grid_01.bmp');
	row('grid_01.bmp (disk)', strlen($gridRaw), $gridRaw, null, $probeLev);
	row('  mode 5 row-delta', strlen($gridRaw), $m5, null, $probeLev);
	row('  mode 13 col-delta', strlen($gridRaw), $m13, null, $probeLev);
	row('  mode 15 BSS1', strlen($gridRaw), $m15, null, $probeLev);
	row('  pick (tournament)', strlen($gridRaw), $stPick, $pick, $probeLev);
	echo "(BMP literals default to deflate level {$probeLev} in choose_best_literal_bundle_transform; full .fz is smaller after outer adaptive_compress.)\n\n";
}

$syn = bmp_synthetic_uniform_shift_grid();
$m15s = $fz->encode_bmp_block_scalar_shift_payload($syn);
$dec = $m15s !== null ? $fz->decode_bmp_block_scalar_shift_payload($m15s) : null;
list($pickS, $stS) = $fz->choose_best_literal_bundle_transform($syn, 'syn.bmp');
$probeSyn = $fz->literal_bundle_gzip_probe_level($syn);
row('synthetic 120×80 grid', strlen($syn), $syn, null, $probeSyn);
row('  mode 15 BSS1', strlen($syn), $m15s, null, $probeSyn);
row('  pick (tournament)', strlen($syn), $stS, $pickS, $probeSyn);
echo 'synthetic round-trip: ' . ($dec !== null && $dec === $syn ? "OK\n" : "FAIL\n");
