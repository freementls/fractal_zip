#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Inspect FZMS census + marker proposals without full zip (fast).
 *
 *   php benchmarks/metastruct_probe.php test_files53
 *   FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 php benchmarks/metastruct_probe.php test_files177
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$corpus = $argv[1] ?? 'test_files53';
$corpusPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
if (!is_dir($corpusPath)) {
	fwrite(STDERR, "missing: {$corpusPath}\n");
	exit(1);
}

putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');
putenv('FRACTAL_ZIP_WEB_REF=0');
$sample = fractal_zip_marker_collect_layer_stripped_sample($corpusPath, 1024 * 1024);
$freq = fractal_zip_marker_propose_from_sample($sample, true);
list($merged, $desc) = fractal_zip_metastruct_marker_propose($corpusPath, $sample, true);
$block = fractal_zip_metastruct_encode_block($desc);
$round = fractal_zip_metastruct_decode_block($block);

echo "corpus={$corpus} sample_bytes=" . strlen($sample) . "\n";
echo "freq mid=\"" . $freq['mid'] . "\" limiters=" . json_encode($freq['common_limiters']) . "\n";
echo "merged mid=\"" . $merged['mid'] . "\" limiters=" . json_encode(array_slice($merged['common_limiters'], 0, 6)) . "\n";
echo "dominant content=" . ($desc['dominant_content_profile'] ?? '?')
	. " delim=" . ($desc['dominant_delimiter_profile'] ?? '?')
	. " textish_ratio=" . round((float) ($desc['textish_ratio'] ?? 0), 4) . "\n";
echo "FZMS block " . strlen($block) . " B; roundtrip=" . (is_array($round) ? 'OK' : 'FAIL') . "\n";

$fz = new fractal_zip(120, true, false, null, false);
$def = fractal_zip_marker_default_config();
$d = fractal_zip_marker_probe_fractal_outer_len($fz, $corpusPath, $def);
$pFreq = fractal_zip_marker_probe_fractal_outer_len($fz, $corpusPath, $freq);
$pMerged = fractal_zip_marker_probe_fractal_outer_len($fz, $corpusPath, $merged);
echo "fractal-leg probe: default={$d} freq={$pFreq} merged={$pMerged} (Δ merged-default=" . ($pMerged - $d) . ")\n";
