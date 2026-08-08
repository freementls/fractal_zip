#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Single enwik8 encode: world-record preset + high tier (~1–2h typical on pp96 topology).
 *
 * Usage:
 *   php benchmarks/run_enwik8_encode_high.php
 *   php benchmarks/run_enwik8_encode_high.php --name=pp96_high
 *
 * Compare vs baseline:
 *   php benchmarks/compare_enwik8_high_vs_baseline.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_high_env.php';

$name = 'pp96_high';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--name=')) {
		$name = substr($arg, 7);
	}
}

bench_world_record_apply_env_defaults();
bench_world_record_apply_high_env();
// High encode: fz-only wall clock (run phda9 squash separately).
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
if (!is_dir($dir)) {
	fwrite(STDERR, "Missing {$dir}\n");
	exit(1);
}

$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_' . $name . '.json';
$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
@unlink($fzcPath);

fwrite(STDERR, "[enwik8_encode_high] name={$name} FZBM_TRIES=" . getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES')
	. " OUTER_PREDICT_PROBE_MAX=" . getenv('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES') . "\n");

$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($dir, false);
$sec = microtime(true) - $t0;

$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
$rawBytes = is_file($dir . DIRECTORY_SEPARATOR . 'enwik8') ? (int) filesize($dir . DIRECTORY_SEPARATOR . 'enwik8') : 100000000;

$memberCount = $fz->zip_folder_member_count > 0 ? $fz->zip_folder_member_count : null;
if ($memberCount === null && isset($fz->folder_bundle_census['files'])) {
	$memberCount = (int) $fz->folder_bundle_census['files'];
}

$case = array(
	'label' => 'test_files109',
	'raw_bytes' => $rawBytes,
	'fzc_bytes' => $fzcBytes,
	'zip_seconds' => round($sec, 4),
	'outer_codec' => fractal_zip::$last_outer_codec ?? null,
	'outer_zpaq_method' => fractal_zip::$last_zpaq_method ?? null,
	'folder_unified_stream' => fractal_zip::$used_folder_unified_stream,
	'folder_unified_stream_env' => getenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM'),
	'folder_per_member_best_env' => getenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST'),
	'member_count' => $memberCount,
	'bench_profile' => 'world-record-high',
	'encode_only' => true,
);

$payload = array(
	'generated' => date('c'),
	'bench_profile' => 'world-record-high',
	'high_env' => true,
	'cases' => array($case),
);
file_put_contents($outJson, json_encode($payload, JSON_PRETTY_PRINT));

$pp96 = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_pp96.json';
$delta = '';
if (is_file($pp96)) {
	$pj = json_decode((string) file_get_contents($pp96), true);
	foreach ($pj['cases'] ?? array() as $c) {
		if (($c['label'] ?? '') === 'test_files109' && isset($c['fzc_bytes'])) {
			$d = (int) $c['fzc_bytes'] - $fzcBytes;
			$delta = ' (vs pp96 ' . ($d > 0 ? '−' : '+') . number_format(abs($d)) . ' B)';
			break;
		}
	}
}

fwrite(STDERR, "[enwik8_encode_high] {$name}: fzc={$fzcBytes} B in " . number_format($sec, 1) . "s{$delta} → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\n";
