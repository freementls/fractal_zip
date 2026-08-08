<?php
/**
 * Writes min PNG + JPEG under DCIM/ (synthetic, wire bytes). Optional for benchmark size/realism.
 * From repo root: `php test_files76/materialize_rasters.php`
 */
require_once dirname(__DIR__) . '/benchmarks/lifestyle_raster_b64.php';
$root = __DIR__;
$b = lifestyle_raster_blobs();
$png1x1 = $b['png'];
$jpg = $b['jpg'];
$pngP = $root . '/DCIM/Screenshots/Screenshot_20260424-100001.png';
$jpgP = $root . '/DCIM/Camera/IMG_20260424_100002.jpg';
foreach (array(dirname($pngP), dirname($jpgP)) as $d) {
	if (!is_dir($d)) {
		mkdir($d, 0755, true);
	}
}
if (is_string($png1x1) && $png1x1 !== '' && file_put_contents($pngP, $png1x1) !== false) {
	echo "wrote {$pngP} (" . strlen($png1x1) . " B)\n";
}
$doneJpg = false;
if (is_string($jpg) && $jpg !== '' && file_put_contents($jpgP, $jpg) !== false) {
	$doneJpg = true;
	echo "wrote {$jpgP} (" . strlen($jpg) . " B)\n";
}
if (!$doneJpg) {
	$g = dirname($root) . '/test_files61/01_raster_formats/grid_01.jpg';
	if (is_file($g) && @copy($g, $jpgP)) {
		echo "copied {$g} -> {$jpgP}\n";
	}
}
