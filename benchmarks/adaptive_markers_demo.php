#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Demo: FRACTAL_ZIP_ADAPTIVE_MARKERS wire-size probe (fractal leg only) vs defaults on a tiny pipe-heavy text tree.
 *
 *   FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 php benchmarks/adaptive_markers_demo.php
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';

putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');

$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_adapt_demo_' . bin2hex(random_bytes(6));
@mkdir($td, 0755, true);
$payload = str_repeat("a|b|c|d|e;f|g|h|i|j\n", 800);
file_put_contents($td . DIRECTORY_SEPARATOR . 'pipes.txt', $payload);

$fz = new fractal_zip(120, true, false, null, false);
$def = fractal_zip_marker_default_config();
$sample = fractal_zip_marker_collect_layer_stripped_sample($td, 256 * 1024);
$prop = fractal_zip_marker_propose_from_sample($sample, true);

echo "Adaptive markers demo (temp dir " . $td . ")\n";
echo "Sample bytes: " . strlen($sample) . "; default mid=\"" . $def['mid'] . "\", proposed mid=\"" . $prop['mid'] . "\"\n";

$bytesDef = fractal_zip_marker_probe_fractal_outer_len($fz, $td, $def);
$bytesProp = fractal_zip_marker_probe_fractal_outer_len($fz, $td, $prop);
echo "Fractal-leg adaptive_compress output: defaults={$bytesDef} B, proposed={$bytesProp} B";
if ($bytesProp < $bytesDef) {
	echo " → win " . ($bytesDef - $bytesProp) . " B\n";
} elseif ($bytesProp > $bytesDef) {
	echo " → proposed larger by " . ($bytesProp - $bytesDef) . " B (would not apply)\n";
} else {
	echo " → tie\n";
}

@unlink($td . DIRECTORY_SEPARATOR . 'pipes.txt');
@rmdir($td);
