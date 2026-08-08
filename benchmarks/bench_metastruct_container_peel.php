#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * A/B: whole .fz with vs without FZMS container peel (modes 18/19 priority + tie-break).
 *
 *   php benchmarks/bench_metastruct_container_peel.php test_files61 test_files53
 *   php benchmarks/bench_metastruct_container_peel.php --ultra test_files61
 */
$root = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1');
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_metastruct_adapt.php';

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
	$corpora = array('test_files61', 'test_files53', 'test_files75', 'test_files173');
}

if ($ultra) {
	bench_metastruct_apply_ultra_env_defaults();
}
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');

/** @return array{fzc_bytes: int, verify_ok: bool, unified: bool} */
function bench_container_peel_zip(string $corpusPath, bool $peelOn): array {
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS');
	putenv('FRACTAL_ZIP_METastruct');
	putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL');
	putenv('FRACTAL_ZIP_METastruct_LITERAL_BIAS');
	if ($peelOn) {
		putenv('FRACTAL_ZIP_METastruct=1');
		putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL=1');
	}
	$row = bench_metastruct_run_mode($corpusPath, 'default', $peelOn);
	return array(
		'fzc_bytes' => (int) $row['fzc_bytes'],
		'verify_ok' => (bool) $row['verify_ok'],
		'unified' => !empty($row['path']['unified_stream']),
	);
}

echo 'bench_metastruct_container_peel ultra=' . ($ultra ? '1' : '0') . "\n\n";

foreach ($corpora as $corpus) {
	$path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
	if (!is_dir($path)) {
		fwrite(STDERR, "skip missing: {$corpus}\n");
		continue;
	}
	echo "=== {$corpus} ===\n";
	echo "  baseline...\n";
	$base = bench_container_peel_zip($path, false);
	echo "  fzms container peel...\n";
	$peel = bench_container_peel_zip($path, true);
	$delta = ($base['fzc_bytes'] > 0 && $peel['fzc_bytes'] > 0) ? ($peel['fzc_bytes'] - $base['fzc_bytes']) : null;
	$deltaS = ($delta === null) ? 'n/a' : (($delta >= 0 ? '+' : '') . $delta);
	echo sprintf(
		"  baseline fzc=%8d verify=%s unified=%s\n",
		$base['fzc_bytes'],
		$base['verify_ok'] ? 'OK' : 'FAIL',
		$base['unified'] ? '1' : '0'
	);
	echo sprintf(
		"  peel     fzc=%8d verify=%s unified=%s Δ=%s\n\n",
		$peel['fzc_bytes'],
		$peel['verify_ok'] ? 'OK' : 'FAIL',
		$peel['unified'] ? '1' : '0',
		$deltaS
	);
}
