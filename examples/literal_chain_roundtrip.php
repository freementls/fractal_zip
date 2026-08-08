#!/usr/bin/env php
<?php
/**
 * Smoke test: FZB literal mode 17 (greedy transform chain) round-trip.
 *
 *   php examples/literal_chain_roundtrip.php
 *
 * Uses grid_01.bmp: row-delta (5) then column-delta (13) composes to a valid chain [13,5].
 */
declare(strict_types=1);

require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$gridPath = dirname(__DIR__) . '/test_files61/01_raster_formats/grid_01.bmp';
if (!is_file($gridPath)) {
	fwrite(STDERR, "SKIP: missing {$gridPath}\n");
	exit(0);
}

$fz = new fractal_zip(null, true, false, null, false);
$raw = (string) file_get_contents($gridPath);
$s5 = $fz->encode_bmp_row_horizontal_delta_payload($raw);
$s13 = $s5 !== null ? $fz->encode_bmp_column_vertical_delta_payload($s5) : null;
if ($s5 === null || $s13 === null || $s13 === $s5) {
	fwrite(STDERR, "SKIP: row then col delta chain N/A\n");
	exit(0);
}
$blob = $fz->encode_literal_transform_chain_payload(array(13, 5), $s13);
if ($blob === null) {
	fwrite(STDERR, "FAIL: encode chain\n");
	exit(1);
}
$out = $fz->decode_bundle_literal_member(17, $blob);
if ($out !== $raw) {
	fwrite(STDERR, "FAIL: mode 17 round-trip\n");
	exit(1);
}
$z17 = gzdeflate($blob, 1);
$z13only = gzdeflate($s13, 1);
echo 'OK mode 17 [13,5] round-trip; gz(chain)=' . ($z17 === false ? '?' : (string) strlen($z17)) . ' gz(col-after-row)=' . ($z13only === false ? '?' : (string) strlen($z13only)) . "\n";

list($pick, $st) = $fz->choose_best_literal_bundle_transform($raw, 'grid_01.bmp');
$decPick = $fz->decode_bundle_literal_member($pick, $st);
$zp = gzdeflate($st, 1);
echo 'choose_best mode=' . $pick . ' gz=' . ($zp === false ? '?' : (string) strlen($zp)) . ' rt=' . ($decPick === $raw ? 'OK' : 'FAIL') . "\n";
