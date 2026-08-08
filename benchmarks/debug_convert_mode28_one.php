#!/usr/bin/env php
<?php
declare(strict_types=1);
$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
require_once $repo . '/fractal_zip.php';
fractal_zip_ensure_convert_bridge_loaded();
$png = (string) file_get_contents($repo . '/test_files177/grid_01.png');
$bmp = fractal_zip_convert_png_to_bmp_bytes('grid_01.png', $png);
echo 'png=' . strlen($png) . ' bmp=' . ($bmp === null ? 'null' : strlen($bmp)) . "\n";
$fz = new fractal_zip();
putenv('FRACTAL_ZIP_CONVERT_PNG_TO_BMP=0');
list($im, $is) = $fz->choose_best_literal_bundle_transform($bmp, 'grid_01.bmp');
echo "inner bmp mode={$im} store=" . strlen($is) . "\n";
putenv('FRACTAL_ZIP_CONVERT_PNG_TO_BMP=1');
list($pm, $ps) = $fz->choose_best_literal_bundle_transform($png, 'grid_01.png');
echo "png path mode={$pm} store=" . strlen($ps) . "\n";
$dec = $fz->decode_bundle_literal_member($pm, $ps, 0);
$m28 = fractal_zip_convert_encode_mode28('grid_01.png', $im, $is);
$gzInner = $fz->literal_bundle_gzip_deflate_len($is, 9);
$gzM28 = $fz->literal_bundle_gzip_deflate_len($m28, 9);
$gzPng = $fz->literal_bundle_gzip_deflate_len($png, 9);
$gzPng1 = $fz->literal_bundle_gzip_deflate_len($png, 1);
echo 'gz inner=' . ($gzInner === false ? 'x' : $gzInner) . ' gz m28=' . ($gzM28 === false ? 'x' : $gzM28) . ' gz png9=' . ($gzPng === false ? 'x' : $gzPng) . ' gz png1=' . ($gzPng1 === false ? 'x' : $gzPng1) . "\n";

putenv('FRACTAL_ZIP_CONVERT_PNG_TO_BMP=1');
$bestLen = $gzPng1;
$bestMode = 0;
$bestStore = $png;
$probe = 9;
$fz->literal_bundle_consider_convert_png_to_bmp($png, 'grid_01.png', $probe, $bestLen, $bestMode, $bestStore, $png);
echo "after consider mode={$bestMode} bestLen={$bestLen} store=" . strlen($bestStore) . "\n";
$dec28 = $fz->decode_bundle_literal_member(28, $bestStore, 0);
echo 'dec28 len=' . strlen($dec28) . ' match=' . ($dec28 === $png ? 'yes' : 'no') . "\n";
if ($dec28 !== $png) {
	echo 'sha orig=' . hash('sha256', $png) . ' sha dec=' . hash('sha256', $dec28) . "\n";
}
