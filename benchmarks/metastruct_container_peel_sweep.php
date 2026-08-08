#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Batch A/B: FZMS container peel vs baseline (whole .fz bytes + verify).
 *
 *   php benchmarks/metastruct_container_peel_sweep.php --ultra test_files61 test_files158
 *   php benchmarks/metastruct_container_peel_sweep.php --container-heavy --ultra
 *   php benchmarks/metastruct_container_peel_sweep.php --convert-bench --ultra
 */
putenv('FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1');
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_metastruct_adapt.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$ultra = in_array('--ultra', $argv, true);
$jsonOut = in_array('--json', $argv, true);
$convertBench = in_array('--convert-bench', $argv, true);
$containerHeavy = in_array('--container-heavy', $argv, true);

$corpora = array();
foreach ($argv as $i => $arg) {
	if ($i === 0 || str_starts_with($arg, '-')) {
		continue;
	}
	$corpora[] = $arg;
}

if ($convertBench) {
	$manifest = __DIR__ . DIRECTORY_SEPARATOR . 'convert_bench_manifest.json';
	if (is_file($manifest)) {
		$j = json_decode((string) file_get_contents($manifest), true);
		if (is_array($j) && isset($j['corpus_labels']) && is_array($j['corpus_labels'])) {
			$corpora = array_merge($corpora, $j['corpus_labels']);
		}
	}
	$corpora[] = 'test_files61';
	$corpora[] = 'test_files173';
	$corpora[] = 'test_files53';
}

if ($containerHeavy) {
	$corpora = array_merge($corpora, array(
		'test_files61',
		'test_files158', 'test_files159', 'test_files160', 'test_files161',
		'test_files162', 'test_files163',
		'test_files153', 'test_files166', 'test_files167',
		'test_files173', 'test_files174',
		'test_files53',
	));
}

$corpora = array_values(array_unique($corpora));
if ($corpora === array()) {
	$corpora = array('test_files61', 'test_files158', 'test_files153', 'test_files173', 'test_files53');
}

if ($ultra) {
	bench_metastruct_apply_ultra_env_defaults();
}
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');

/** @return array<string,mixed> */
function mcp_sweep_one(string $corpusPath, string $label, bool $peelOn): array {
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS');
	putenv('FRACTAL_ZIP_METastruct');
	putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL');
	putenv('FRACTAL_ZIP_METastruct_LITERAL_BIAS');
	if ($peelOn) {
		putenv('FRACTAL_ZIP_METastruct=1');
		putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL=1');
	}
	fractal_zip::$last_metastruct_descriptor = null;
	$row = bench_metastruct_run_mode($corpusPath, 'default', $peelOn);
	return array(
		'fzc_bytes' => (int) $row['fzc_bytes'],
		'verify_ok' => (bool) $row['verify_ok'],
		'unified' => !empty($row['path']['unified_stream']),
	);
}

$rows = array();
if (!$jsonOut) {
	echo 'metastruct_container_peel_sweep ultra=' . ($ultra ? '1' : '0') . ' corpora=' . count($corpora) . "\n\n";
}

foreach ($corpora as $corpus) {
	$path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
	if (!is_dir($path)) {
		$rows[] = array('corpus' => $corpus, 'error' => 'missing');
		if (!$jsonOut) {
			echo "{$corpus}: missing\n";
		}
		continue;
	}
	if (!$jsonOut) {
		echo "=== {$corpus} ===\n  baseline...\n";
	}
	$base = mcp_sweep_one($path, $corpus, false);
	if (!$jsonOut) {
		echo "  peel...\n";
	}
	$peel = mcp_sweep_one($path, $corpus, true);
	$delta = ($base['fzc_bytes'] > 0 && $peel['fzc_bytes'] > 0) ? ($peel['fzc_bytes'] - $base['fzc_bytes']) : null;
	$rows[] = array(
		'corpus' => $corpus,
		'baseline_fzc' => $base['fzc_bytes'],
		'peel_fzc' => $peel['fzc_bytes'],
		'delta' => $delta,
		'baseline_verify' => $base['verify_ok'],
		'peel_verify' => $peel['verify_ok'],
		'unified' => $peel['unified'],
		'strict_win' => ($delta !== null && $delta < 0 && $peel['verify_ok']),
	);
	if (!$jsonOut) {
		$dS = ($delta === null) ? 'n/a' : (($delta >= 0 ? '+' : '') . $delta);
		echo sprintf(
			"  base=%8d peel=%8d Δ=%s verify=%s/%s unified=%s\n\n",
			$base['fzc_bytes'],
			$peel['fzc_bytes'],
			$dS,
			$base['verify_ok'] ? 'OK' : 'FAIL',
			$peel['verify_ok'] ? 'OK' : 'FAIL',
			$peel['unified'] ? '1' : '0'
		);
	}
}

$wins = array_values(array_filter($rows, static fn (array $r): bool => isset($r['delta']) && is_int($r['delta']) && $r['delta'] < 0));
usort($wins, static fn (array $a, array $b): int => ($a['delta'] ?? 0) <=> ($b['delta'] ?? 0));

$out = array(
	'ultra' => $ultra,
	'rows' => $rows,
	'wins' => $wins,
	'win_count' => count($wins),
);

if ($jsonOut) {
	echo bench_json_encode_try($out, false) . "\n";
} else {
	echo "--- summary: " . count($wins) . " corpora with smaller peel .fz ---\n";
	foreach ($wins as $w) {
		echo sprintf(
			"  %-20s Δ=%+d  verify=%s\n",
			$w['corpus'],
			(int) $w['delta'],
			!empty($w['peel_verify']) ? 'OK' : 'FAIL'
		);
	}
}
