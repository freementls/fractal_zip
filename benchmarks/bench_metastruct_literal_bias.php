#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * A/B: unified-stream zip with vs without FZMS literal bias (member transform probes + schedule nudges).
 *
 *   php benchmarks/bench_metastruct_literal_bias.php test_files53 test_files75 test_files107
 *   php benchmarks/bench_metastruct_literal_bias.php --ultra test_files53
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_metastruct_adapt.php';

$ultra = in_array('--ultra', $argv, true);
$corpora = array();
foreach ($argv as $i => $arg) {
	if ($i === 0 || $arg === '--ultra') {
		continue;
	}
	if (str_starts_with($arg, '-')) {
		continue;
	}
	$corpora[] = $arg;
}
if ($corpora === array()) {
	$corpora = array('test_files53', 'test_files75', 'test_files107', 'test_files149');
}

if ($ultra) {
	bench_metastruct_apply_ultra_env_defaults();
}
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');

/** @return array{fzc_bytes: int, verify_ok: bool, unified: bool, fzms: ?array} */
function bench_literal_bias_zip(string $corpusPath, bool $biasOn): array {
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS');
	fractal_zip::$last_metastruct_descriptor = null;
	$row = bench_metastruct_run_mode($corpusPath, 'default', $biasOn);
	return array(
		'fzc_bytes' => (int) $row['fzc_bytes'],
		'verify_ok' => (bool) $row['verify_ok'],
		'unified' => !empty($row['path']['unified_stream']),
		'fzms' => is_array($row['metastruct']) ? $row['metastruct'] : fractal_zip::$last_metastruct_descriptor,
	);
}

echo 'bench_metastruct_literal_bias ultra=' . ($ultra ? '1' : '0') . "\n\n";

foreach ($corpora as $corpus) {
	$path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
	if (!is_dir($path)) {
		fwrite(STDERR, "skip missing: {$corpus}\n");
		continue;
	}
	echo "=== {$corpus} ===\n";
	echo "  baseline...\n";
	$base = bench_literal_bias_zip($path, false);
	echo "  fzms literal bias...\n";
	$bias = bench_literal_bias_zip($path, true);
	$delta = ($base['fzc_bytes'] > 0 && $bias['fzc_bytes'] > 0) ? ($bias['fzc_bytes'] - $base['fzc_bytes']) : null;
	$deltaS = ($delta === null) ? 'n/a' : (($delta >= 0 ? '+' : '') . $delta);
	echo sprintf(
		"  baseline fzc=%8d verify=%s unified=%s\n",
		$base['fzc_bytes'],
		$base['verify_ok'] ? 'OK' : 'FAIL',
		$base['unified'] ? '1' : '0'
	);
	echo sprintf(
		"  bias     fzc=%8d verify=%s unified=%s Δ=%s\n",
		$bias['fzc_bytes'],
		$bias['verify_ok'] ? 'OK' : 'FAIL',
		$bias['unified'] ? '1' : '0',
		$deltaS
	);
	if (is_array($bias['fzms'])) {
		echo '    FZMS dom=' . ($bias['fzms']['dominant_content_profile'] ?? '?') . "\n";
	}
	echo "\n";
}
