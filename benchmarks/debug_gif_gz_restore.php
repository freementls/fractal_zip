#!/usr/bin/env php
<?php
declare(strict_types=1);
$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_literal_pac.php';
$p = $repo . '/test_files61/02_gzip_members/grid_01.gif.gz';
$rel = '02_gzip_members/grid_01.gif.gz';
$orig = (string) file_get_contents($p);
$inner = gzdecode($orig);
echo 'orig=' . strlen($orig) . ' inner=' . strlen($inner) . "\n";
try {
	$r = fractal_zip_literal_restore_gzip_exact($inner, strlen($orig), sha1($orig, true));
	echo 'direct restore ok len=' . strlen($r) . ' match=' . ($r === $orig ? 'yes' : 'no') . "\n";
} catch (Throwable $e) {
	echo 'direct restore fail: ' . $e->getMessage() . "\n";
}
$fz = new fractal_zip();
list($m, $s) = $fz->choose_best_literal_bundle_transform($orig, $rel);
echo "choose mode={$m} store=" . strlen($s) . "\n";
$dec = $fz->decode_bundle_literal_member($m, $s, 0);
echo 'decode len=' . strlen($dec) . ' match=' . ($dec === $orig ? 'yes' : 'no') . "\n";
if ($dec !== $orig && $m === 6) {
	// unwrap mode 6 manually to see inner used for restore
	echo "mode6 decode path\n";
}
