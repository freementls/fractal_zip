#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Epicycle parameter experiment: lag, entropy, oracle size, detect latency.
 *
 * Usage:
 *   php benchmarks/bench_cycle_epicycle_experiment.php [--quick] [--json]
 */

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';

$jsonOut = in_array('--json', $argv, true);
$quick = in_array('--quick', $argv, true);

$rows = array();

foreach(cycle_encoding_expanded_specs() as $case => $spec) {
	$bytes = (string)$spec['bytes'];
	$t0 = microtime(true);
	$detected = cycle_encoding_detect_whole($bytes);
	$detectMs = (microtime(true) - $t0) * 1000.0;
	$enc = cycle_encoding_codec_encode($bytes);
	$encodeMs = (microtime(true) - $t0) * 1000.0;
	$rows[] = array(
		'case' => (int)$case,
		'tier' => (string)$spec['tier'],
		'length' => strlen($bytes),
		'lag38_pct' => round(100.0 * cycle_encoding_lag_match_rate($bytes, 38), 2),
		'lag76_pct' => round(100.0 * cycle_encoding_lag_match_rate($bytes, 76), 2),
		'entropy' => round(cycle_encoding_shannon_entropy($bytes), 3),
		'period' => cycle_encoding_minimal_period($bytes),
		'oracle_B' => cycle_encoding_oracle_bytes($spec),
		'wire_B' => strlen((string)$enc['payload']),
		'detect_ms' => round($detectMs, 1),
		'encode_ms' => round($encodeMs, 1),
		'detect_ok' => $detected !== null,
		'rt_ok' => cycle_encoding_codec_decode((string)$enc['payload']) === $bytes,
	);
}

$grid = array();
if(!$quick) {
	$length = 10000;
	$w1Steps = array(5, 7, 11);
	$w2Steps = array(8, 11, 13);
	$spanPairs = array(
		array(48, 122, 48, 123),
		array(48, 124, 49, 126),
		array(35, 129, 36, 131),
	);
	foreach($spanPairs as $pair) {
		foreach($w1Steps as $s1) {
			foreach($w2Steps as $s2) {
				if($s1 === $s2) {
					continue;
				}
				$spec = array(
					'version' => 2,
					'combine' => 'round_robin',
					'byte_map' => 'direct',
					'walkers' => array(
						array('min_ord' => $pair[0], 'max_ord' => $pair[1], 'step' => $s1, 'phase' => $pair[0]),
						array('min_ord' => $pair[2], 'max_ord' => $pair[3], 'step' => $s2, 'phase' => $pair[2]),
					),
					'length' => $length,
					'width' => 1,
				);
				$bytes = cycle_encoding_generate($spec);
				$t0 = microtime(true);
				$det = cycle_encoding_detect_v2_round_robin($bytes);
				$detectMs = (microtime(true) - $t0) * 1000.0;
				$grid[] = array(
					'w1' => $pair[0] . '-' . $pair[1] . ':' . $s1,
					'w2' => $pair[2] . '-' . $pair[3] . ':' . $s2,
					'lag38_pct' => round(100.0 * cycle_encoding_lag_match_rate($bytes, 38), 2),
					'entropy' => round(cycle_encoding_shannon_entropy($bytes), 3),
					'oracle_B' => strlen(cycle_encoding_pack_for_spec($spec)),
					'generic_detect_ms' => round($detectMs, 1),
					'generic_detect_ok' => $det !== null,
				);
			}
		}
	}
	usort($grid, static function (array $a, array $b): int {
		$scoreA = $a['entropy'] - $a['lag38_pct'] * 0.01;
		$scoreB = $b['entropy'] - $b['lag38_pct'] * 0.01;
		return $scoreB <=> $scoreA;
	});
	$grid = array_slice($grid, 0, 8);
}

$payload = array(
	'metric' => 'cycle_epicycle_experiment',
	'corpus' => $rows,
	'rr_grid_top' => $grid,
);

if($jsonOut) {
	echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

printf("\nCycle epicycle experiment — corpus + RR parameter grid\n\n");
printf("%4s %-14s %8s %7s %7s %7s %8s %7s %8s %8s %s\n",
	'case', 'tier', 'length', 'lag38', 'lag76', 'entropy', 'period', 'oracle', 'detect', 'encode', 'RT');
printf("%s\n", str_repeat('-', 105));
foreach($rows as $r) {
	printf("%4d %-14s %8d %6.1f%% %6.1f%% %7.3f %8d %7d %7.1fms %7.1fms %s\n",
		$r['case'],
		$r['tier'],
		$r['length'],
		$r['lag38_pct'],
		$r['lag76_pct'],
		$r['entropy'],
		$r['period'],
		$r['oracle_B'],
		$r['detect_ms'],
		$r['encode_ms'],
		$r['rt_ok'] ? 'ok' : 'FAIL'
	);
}

if($grid !== array()) {
	printf("\nTop RR grid configs @10KiB (entropy − lag38 penalty, generic detect):\n\n");
	printf("%-16s %-16s %7s %7s %7s %10s %s\n", 'walker1', 'walker2', 'lag38', 'entropy', 'oracle', 'detect_ms', 'det');
	printf("%s\n", str_repeat('-', 90));
	foreach($grid as $g) {
		printf("%-16s %-16s %6.1f%% %7.3f %7d %9.1fms %s\n",
			$g['w1'],
			$g['w2'],
			$g['lag38_pct'],
			$g['entropy'],
			$g['oracle_B'],
			$g['generic_detect_ms'],
			$g['generic_detect_ok'] ? 'ok' : 'FAIL'
		);
	}
}

$maxDetect = max(array_column($rows, 'detect_ms'));
printf("\nMax corpus detect_ms=%.1f (target: catalog probe-first + generic v2)\n", $maxDetect);
fwrite(STDERR, "OK bench_cycle_epicycle_experiment\n");
