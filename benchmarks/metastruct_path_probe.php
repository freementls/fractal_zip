#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Diagnose which zip_folder path runs and whether adaptive markers apply.
 *
 *   php benchmarks/metastruct_path_probe.php test_files75 [--legacy-fractal] [--metastruct]
 */
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';

$corpus = 'test_files75';
$legacy = in_array('--legacy-fractal', $argv, true);
$metastruct = in_array('--metastruct', $argv, true);
foreach ($argv as $i => $arg) {
	if ($i === 0 || str_starts_with($arg, '-')) {
		continue;
	}
	$corpus = $arg;
}

$dir = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
if (!is_dir($dir)) {
	fwrite(STDERR, "missing: {$dir}\n");
	exit(1);
}

putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');
putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS');
putenv('FRACTAL_ZIP_METastruct');
if ($metastruct) {
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');
	putenv('FRACTAL_ZIP_METastruct=1');
}
if ($legacy) {
	fractal_zip_metastruct_force_legacy_fractal_env();
} else {
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
}

$resolved = realpath($dir) ?: $dir;
$fz = new fractal_zip(120, true, false, null, false);
$raw = $fz->collect_raw_files_for_bundle($resolved);
$lb = fractal_zip_resolve_folder_logical_bundle($raw);
echo "corpus={$corpus} legacy=" . ($legacy ? '1' : '0') . ' metastruct=' . ($metastruct ? '1' : '0') . "\n";
echo 'members=' . count($raw) . ' heterogeneous=' . (fractal_zip_heterogeneous_folder_encode_enabled($lb) ? 'yes' : 'no') . "\n";
echo 'bundle_only=' . ($fz->should_use_bundle_only_for_folder($resolved) ? 'yes' : 'no') . "\n";
echo 'unified_env=' . (fractal_zip::folder_unified_stream_enabled() ? 'yes' : 'no') . "\n";
echo 'multidiff_only=' . (fractal_zip::substring_multidiff_recursive_only_enabled() ? 'yes' : 'no') . "\n";

fractal_zip::$last_metastruct_descriptor = null;
ob_start();
$fz->zip_folder($resolved, false);
ob_end_clean();

echo 'used_unified=' . (fractal_zip::$used_folder_unified_stream ? 'yes' : 'no') . "\n";
echo 'per_member=' . (fractal_zip::$used_folder_per_member_best ? 'yes' : 'no') . "\n";
echo 'gzip_fast=' . (fractal_zip::$used_folder_gzip_fast ? 'yes' : 'no') . "\n";
echo 'mid=' . $fz->mid_fractal_zip_marker . "\n";
$meta = fractal_zip::$last_metastruct_descriptor;
if (is_array($meta)) {
	echo 'fzms_applied=' . (!empty($meta['applied']) ? 'yes' : 'no') . "\n";
	if (isset($meta['probe']['delta'])) {
		echo 'fractal_leg_delta=' . $meta['probe']['delta'] . "\n";
	}
}
$fzc = $resolved . '.fz';
echo 'fzc_bytes=' . (is_file($fzc) ? filesize($fzc) : 'missing') . "\n";
@unlink($fzc);
