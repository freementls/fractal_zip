#!/usr/bin/env php
<?php
/**
 * Compare literal mode 14 (square prefix transpose) vs BMP row-horizontal (5) + column-vertical (13),
 * including two-step chains {5,13}², on deflate size.
 *
 *   php examples/mode14_vs_row_col_chain_compare.php
 *
 * Non-BMP payloads: modes 5/13 are N/A (encoders return null). Mode 14 can still win on raw bytes
 * (see fzb_literal_transform_win_demo.php).
 *
 * BMP payloads: reports z9 for raw, 14, 5, 13, best single, best 2-chain, and choose_best pick.
 */
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';

/** @return array{best: int, label: string, blob: string|null} */
function best_two_chain_5_13(fractal_zip $fz, string $raw, int $lev): array {
	$pairs = array(
		array(5, 13),
		array(13, 5),
		array(5, 5),
		array(13, 13),
	);
	$best = PHP_INT_MAX;
	$label = '—';
	$bestBlob = null;
	foreach ($pairs as $pair) {
		list($m1, $m2) = $pair;
		$e1 = $fz->literal_bundle_encode_one_transform_mode($m1, $raw);
		if ($e1 === null || $e1 === $raw) {
			continue;
		}
		$e2 = $fz->literal_bundle_encode_one_transform_mode($m2, $e1);
		if ($e2 === null || $e2 === $e1) {
			continue;
		}
		$blob = $fz->encode_literal_transform_chain_payload(array($m2, $m1), $e2);
		if ($blob === null) {
			continue;
		}
		$z = gzdeflate($blob, $lev);
		if ($z === false) {
			continue;
		}
		$len = strlen($z);
		if ($len < $best) {
			$best = $len;
			$label = "[{$m2},{$m1}]";
			$bestBlob = $blob;
		}
	}
	if ($best === PHP_INT_MAX) {
		return array('best' => PHP_INT_MAX, 'label' => '—', 'blob' => null);
	}
	return array('best' => $best, 'label' => $label, 'blob' => $bestBlob);
}

/** @return array<string, int|false> */
function score_transforms(fractal_zip $fz, string $raw, string $pathLabel, int $lev): array {
	$out = array(
		'raw' => false,
		'm14' => false,
		'm5' => false,
		'm13' => false,
		'best_single' => false,
		'best_chain' => false,
		'chain_label' => '',
	);
	$z0 = gzdeflate($raw, $lev);
	$out['raw'] = ($z0 !== false) ? strlen($z0) : false;
	$t14 = $fz->literal_square_block_transpose_prefix($raw);
	if ($t14 !== null && $t14 !== $raw) {
		$z = gzdeflate($t14, $lev);
		$out['m14'] = ($z !== false) ? strlen($z) : false;
	}
	$e5 = $fz->encode_bmp_row_horizontal_delta_payload($raw);
	if ($e5 !== null && $e5 !== $raw) {
		$z = gzdeflate($e5, $lev);
		$out['m5'] = ($z !== false) ? strlen($z) : false;
	}
	$e13 = $fz->encode_bmp_column_vertical_delta_payload($raw);
	if ($e13 !== null && $e13 !== $raw) {
		$z = gzdeflate($e13, $lev);
		$out['m13'] = ($z !== false) ? strlen($z) : false;
	}
	$singles = array_filter(
		array(
			$out['m5'] !== false ? $out['m5'] : PHP_INT_MAX,
			$out['m13'] !== false ? $out['m13'] : PHP_INT_MAX,
		),
		static fn ($x) => $x < PHP_INT_MAX
	);
	$out['best_single'] = $singles !== array() ? min($singles) : false;
	$ch = best_two_chain_5_13($fz, $raw, $lev);
	$out['best_chain'] = ($ch['best'] < PHP_INT_MAX) ? $ch['best'] : false;
	$out['chain_label'] = $ch['label'];
	return $out;
}

function fmt($v): string {
	if ($v === false) {
		return '—';
	}
	return (string) $v;
}

function row(string $label, array $sc, ?int $pickMode, int $lev): void {
	$m14 = $sc['m14'];
	$bestHV = $sc['best_chain'];
	if ($bestHV === false) {
		$bestHV = $sc['best_single'];
	}
	$cmp = 'N/A';
	if ($bestHV === false && $m14 !== false) {
		$cmp = '5/13 N/A (non-BMP): 14 vs raw only';
	}
	if ($m14 !== false && $bestHV !== false) {
		if ($m14 < $bestHV) {
			$cmp = '14 < 5∘13 family';
		} elseif ($m14 > $bestHV) {
			$cmp = '14 > 5∘13 family';
		} else {
			$cmp = '14 = 5∘13 family';
		}
	}
	echo str_pad($label, 36)
		. " lev={$lev}  raw=" . fmt($sc['raw'])
		. "  m14=" . fmt($sc['m14'])
		. "  m5=" . fmt($sc['m5'])
		. "  m13=" . fmt($sc['m13'])
		. "  best1(5|13)=" . fmt($sc['best_single'])
		. '  best2chain=' . fmt($sc['best_chain']) . ' ' . ($sc['chain_label'] !== '' ? $sc['chain_label'] : '')
		. "  pick={$pickMode}"
		. "  => {$cmp}\n";
}

$fz = new fractal_zip(null, true, false, null, false);

echo "=== Non-BMP: mode 14 demo (row/col BMP encoders unavailable) ===\n";
$ascii = '000100000';
$lev1 = 1;
$sc = score_transforms($fz, $ascii, 'demo', $lev1);
list($pick,) = $fz->choose_best_literal_bundle_transform($ascii, 'demo14.txt');
row('ASCII mode-14 classic', $sc, $pick, $lev1);

echo "\n=== BMP fixtures (deflate level 9, same as BMP tournament default) ===\n";
$bmpCases = array(
	dirname(__DIR__) . '/test_files63/03_mode13_bmp/stripes_16x16.bmp',
	dirname(__DIR__) . '/test_files63/03_mode13_bmp/stripes_48x48.bmp',
	dirname(__DIR__) . '/test_files63/04_reference/grid_01.bmp',
	dirname(__DIR__) . '/test_files61/01_raster_formats/grid_01.bmp',
);
$seenBmpSha = array();
foreach ($bmpCases as $p) {
	if (!is_file($p)) {
		echo "(skip missing) {$p}\n";
		continue;
	}
	$raw = (string) file_get_contents($p);
	$bh = sha1($raw);
	if (isset($seenBmpSha[$bh])) {
		continue;
	}
	$seenBmpSha[$bh] = true;
	$lev = $fz->literal_bundle_gzip_probe_level($raw);
	$label = substr($p, strlen(dirname(__DIR__)) + 1);
	$sc = score_transforms($fz, $raw, $label, $lev);
	list($pick,) = $fz->choose_best_literal_bundle_transform($raw, basename($p));
	row($label, $sc, $pick, $lev);
}

echo "\n=== Random small BI_RGB 24bpp BMPs (seeded; 400 tries) ===\n";
$w = 12;
$h = 10;
$rowStride = (int) (((($w * 24 + 31) >> 5) << 2));
$found14Wins = 0;
$found14BeatsChain = 0;
mt_srand(20260408);
for ($t = 0; $t < 400; $t++) {
	$pix = '';
	for ($y = 0; $y < $h; $y++) {
		$rowB = '';
		for ($x = 0; $x < $w; $x++) {
			$rowB .= chr(mt_rand(0, 255)) . chr(mt_rand(0, 255)) . chr(mt_rand(0, 255));
		}
		$rowB .= str_repeat("\0", $rowStride - strlen($rowB));
		$pix .= $rowB;
	}
	$fs = 54 + strlen($pix);
	$hdr = 'BM' . pack('V', $fs) . pack('vv', 0, 0) . pack('V', 54);
	$dib = pack('V3vvV6', 40, $w, $h, 1, 24, 0, strlen($pix), 0, 0, 0, 0);
	$raw = $hdr . $dib . $pix;
	$lev = $fz->literal_bundle_gzip_probe_level($raw);
	$sc = score_transforms($fz, $raw, 'rand', $lev);
	$m14 = $sc['m14'];
	$hv = $sc['best_chain'];
	if ($hv === false) {
		$hv = $sc['best_single'];
	}
	if ($m14 === false || $hv === false) {
		continue;
	}
	if ($m14 < (int) $sc['raw']) {
		$found14Wins++;
	}
	if ($m14 < $hv) {
		$found14BeatsChain++;
		if ($found14BeatsChain <= 3) {
			echo "  hit: m14={$m14} < best_5_13_family={$hv}  raw_z=" . fmt($sc['raw']) . "  trial={$t}\n";
		}
	}
}
echo "  count m14 < raw_z: {$found14Wins} / 400\n";
echo "  count m14 < best(5,13 chains and singles): {$found14BeatsChain} / 400\n";

echo "\n=== Summary ===\n";
echo "- Mode 14 is a generic square-prefix transpose on the whole byte string; BMP modes 5 and 13 rewrite only the pixel payload with header preserved.\n";
echo "- On typical rasters here, row+column deltas (alone or chained) beat mode-14 deflate size; the ASCII demo is where 14 wins and 5/13 do not apply.\n";
echo "- If random search finds no m14 < best_chain on 400 samples, mode 14 is unlikely to beat {5,13}² on random small BMP noise under z9.\n";
