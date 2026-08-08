#!/usr/bin/env php
<?php
/**
 * Mode 16 (BSS2): core-only grid template + per-cell BGR/scalar shift + raw cell rims.
 *
 *   php examples/bss2_core_grid_sizes.php
 *
 * Reports stored inner size, gzdeflate-1, per-cell shift payload size (96 B for kind=0
 * on a 12×8 grid; 288 B for kind=1), and a minimal FZB4 wrapper gz size vs raw.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';

/**
 * 288×192, 12×8 × 24×24 cells, margin 1 → 22×22 core. Cores match ref+BGR shift; rims are unique noise.
 */
function bmp_synthetic_bss2_core_grid(): string
{
	$w = 288;
	$h = 192;
	$cw = 24;
	$ch = 24;
	$m = 1;
	$iw = $cw - 2 * $m;
	$ih = $ch - 2 * $m;
	$rowStride = (int) (((($w * 24 + 31) >> 5) << 2));
	$coreTpl = [];
	for ($cly = 0; $cly < $ih; $cly++) {
		for ($clx = 0; $clx < $iw; $clx++) {
			$b = ($cly * 5 + $clx * 3) & 255;
			$g = ($cly * 11 + $clx * 7 + 40) & 255;
			$r = ($cly * 13 + $clx * 17 + 90) & 255;
			$coreTpl[] = array($b, $g, $r);
		}
	}
	$pix = '';
	for ($sy = 0; $sy < $h; $sy++) {
		$vy = $h - 1 - $sy;
		$br = intdiv($vy, $ch);
		$row = '';
		for ($vx = 0; $vx < $w; $vx++) {
			$bc = intdiv($vx, $cw);
			$ly = $vy - $br * $ch;
			$lx = $vx - $bc * $cw;
			$db = ($br * 11 + $bc * 3) & 255;
			$dg = ($br * 7 + $bc * 19) & 255;
			$dr = ($br * 13 + $bc * 5) & 255;
			$inCore = ($lx >= $m && $lx < $cw - $m && $ly >= $m && $ly < $ch - $m);
			if ($inCore) {
				$cly = $ly - $m;
				$clx = $lx - $m;
				$idx = $cly * $iw + $clx;
				list($tb, $tg, $tr) = $coreTpl[$idx];
				$row .= chr(($tb + $db) & 255) . chr(($tg + $dg) & 255) . chr(($tr + $dr) & 255);
			} else {
				$noise = (($br * 97 + $bc * 131 + $lx * 17 + $ly * 23) * 1103515245 + 12345) & 0x7FFFFFFF;
				$row .= chr($noise & 255) . chr(($noise >> 8) & 255) . chr(($noise >> 16) & 255);
			}
		}
		$row .= str_repeat("\0", $rowStride - strlen($row));
		$pix .= $row;
	}
	$fs = 54 + strlen($pix);
	$hdr = 'BM' . pack('V', $fs) . pack('vv', 0, 0) . pack('V', 54);
	$dib = pack('V3vvV6', 40, $w, $h, 1, 24, 0, strlen($pix), 0, 0, 0, 0);
	return $hdr . $dib . $pix;
}

function fzb4_single(string $name, int $mode, string $stored, fractal_zip $fz): string
{
	return 'FZB4'
		. $fz->encode_varint_u32(0)
		. $fz->encode_varint_u32(strlen($name))
		. $name
		. chr($mode)
		. $fz->encode_varint_u32(strlen($stored))
		. $stored;
}

$fz = new fractal_zip(null, true, false, null, false);

$syn = bmp_synthetic_bss2_core_grid();
$m16 = $fz->encode_bmp_block_core_scalar_shift_payload($syn);
$dec = $m16 !== null ? $fz->decode_bmp_block_core_scalar_shift_payload($m16) : null;
list($pickSyn, $stSyn) = $fz->choose_best_literal_bundle_transform($syn, 'syn_bss2.bmp');

echo "Synthetic 288×192 BSS2-eligible BMP (12×8 × 24×24, margin 1 core 22×22)\n";
echo '  round-trip mode 16: ' . ($dec !== null && $dec === $syn ? "OK\n" : "FAIL\n");
if ($m16 !== null) {
	$po = unpack('V', substr($m16, 10, 4))[1];
	$kind = ord($m16[$po + 4]);
	$shiftPayload = 12 * 8 * ($kind === 0 ? 1 : 3);
	echo '  mode 16 kind=' . $kind . " per-cell shift payload: {$shiftPayload} B (96 if scalar, 288 if BGR)\n";
	echo '  stored inner (mode 16 BMP): ' . strlen($m16) . " B\n";
	$z = gzdeflate($m16, 1);
	echo '  gzdeflate-1(stored BMP): ' . ($z === false ? 'err' : strlen($z)) . " B\n";
	$fzb = fzb4_single('syn_bss2.bmp', 16, $m16, $fz);
	$zf = gzdeflate($fzb, 1);
	echo '  gzdeflate-1(FZB4 + member): ' . ($zf === false ? 'err' : strlen($zf)) . " B\n";
}
$zPickSyn = gzdeflate($stSyn, 1);
echo '  choose_best mode=' . $pickSyn . ' gzdeflate-1(member)=' . ($zPickSyn === false ? '?' : (string) strlen($zPickSyn)) . " B\n\n";

$gridPath = dirname(__DIR__) . '/test_files61/01_raster_formats/grid_01.bmp';
if (is_file($gridPath)) {
	$gridRaw = (string) file_get_contents($gridPath);
	$m16g = $fz->encode_bmp_block_core_scalar_shift_payload($gridRaw);
	list($pickG, $stG) = $fz->choose_best_literal_bundle_transform($gridRaw, 'grid_01.bmp');
	$zg = gzdeflate($gridRaw, 1);
	$zp = gzdeflate($stG, 1);
	$z16 = ($m16g !== null) ? gzdeflate($m16g, 1) : false;
	echo "grid_01.bmp (real anti-aliased grid)\n";
	if ($m16g !== null) {
		$decG = $fz->decode_bmp_block_core_scalar_shift_payload($m16g);
		echo '  mode 16 BSS2 round-trip: ' . ($decG === $gridRaw ? "OK\n" : "FAIL\n");
		$po = unpack('V', substr($m16g, 10, 4))[1];
		$kindG = ord($m16g[$po + 4]);
		$off = $po + 5;
		$n16 = strlen($m16g);
		$vars = array();
		for ($vi = 0; $vi < 5; $vi++) {
			$shift = 0;
			$val = 0;
			for ($step = 0; $step < 6; $step++) {
				if ($off >= $n16) {
					break 2;
				}
				$b = ord($m16g[$off]);
				$off++;
				$val |= ($b & 0x7F) << $shift;
				if (($b & 0x80) === 0) {
					break;
				}
				$shift += 7;
			}
			$vars[] = $val;
		}
		$nc = (int) $vars[0];
		$nr = (int) $vars[1];
		$cww = (int) $vars[2];
		$chh = (int) $vars[3];
		$mg = (int) $vars[4];
		$cells = $nc * $nr;
		$shiftB = $cells * ($kindG === 0 ? 1 : 3);
		echo "  BSS2 grid: {$nc}×{$nr} cells × {$cww}×{$chh} px, margin={$mg} (core " . ($cww - 2 * $mg) . '×' . ($chh - 2 * $mg) . ")\n";
		echo "  per-cell shift payload in stored BMP: {$shiftB} B (96 if kind=scalar, 288 if kind=BGR for 96 cells)\n";
		echo '  stored mode-16 BMP: ' . strlen($m16g) . ' B; gzdeflate-1: ' . ($z16 === false ? 'err' : (string) strlen($z16)) . " B\n";
	} else {
		echo "  mode 16 BSS2 encode: null (pattern does not fit)\n";
	}
	echo '  choose_best mode=' . $pickG . "\n";
	echo '  gzdeflate-1 raw BMP: ' . ($zg === false ? 'err' : strlen($zg)) . " B\n";
	echo '  gzdeflate-1 pick:    ' . ($zp === false ? 'err' : strlen($zp)) . " B\n";
	if ($z16 !== false && $zp !== false) {
		echo '  (mode 16 loses tournament vs pick under gzdeflate-1 by ' . (strlen($z16) - strlen($zp)) . " B on this file)\n";
	}
	echo "  Note: ~96 B is only the per-cell scalar delta region (kind=0, 96 cells). BGR shifts are 288 B; gzip+template+rims dominate the bitstream.\n";
}
