#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Single enwik8 encode with inner-first env (no full world-record high outer ladder).
 *
 * Usage:
 *   php benchmarks/run_enwik8_inner_experiment.php --case=inner_baseline
 *   php benchmarks/run_enwik8_inner_experiment.php --case=inner_combo
 */

$repo = dirname(__DIR__);
// fractal_zip.php loads opcache bootstrap; without this, -d opcache.enable_cli=0 is undone by re-exec.
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_inner_env.php';

$case = 'inner_baseline';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--case=')) {
		$case = substr($arg, 7);
	} elseif (str_starts_with($arg, '--only=')) {
		$case = substr($arg, 7);
	}
}

$apply = static function (string $c): void {
	switch ($c) {
		case 'inner_baseline':
			bench_world_record_apply_inner_baseline_env();
			break;
		case 'inner_deep':
			bench_world_record_apply_inner_baseline_env();
			putenv('FRACTAL_ZIP_DEEP=1');
			putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=1');
			putenv('FRACTAL_ZIP_MEMBER_DEEP_UNWRAP=1');
			putenv('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD=0.005');
			break;
		case 'inner_allsub':
			bench_world_record_apply_inner_baseline_env();
			putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
			break;
		case 'inner_multidiff_caps':
			bench_world_record_apply_inner_baseline_env();
			bench_world_record_apply_inner_multidiff_caps_env();
			break;
		case 'inner_combo':
			bench_world_record_apply_inner_focus_env();
			break;
		case 'inner_recursive0':
			bench_world_record_apply_inner_baseline_env();
			bench_world_record_apply_inner_combo_recursive0_caps_env();
			break;
		default:
			fwrite(STDERR, "Unknown --case={$c}; use inner_baseline|inner_deep|inner_allsub|inner_multidiff_caps|inner_combo|inner_recursive0\n");
			exit(2);
	}
};

$apply($case);

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
if (!is_dir($dir)) {
	fwrite(STDERR, "Missing {$dir}\n");
	exit(1);
}

$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_' . $case . '.json';
$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
@unlink($fzcPath);

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

$caseRow = array(
	'label' => 'test_files109',
	'raw_bytes' => $rawBytes,
	'fzc_bytes' => $fzcBytes,
	'zip_seconds' => round($sec, 4),
	'outer_codec' => fractal_zip::$last_outer_codec ?? null,
	'outer_zpaq_method' => fractal_zip::$last_zpaq_method ?? null,
	'folder_unified_stream' => fractal_zip::$used_folder_unified_stream,
	'member_count' => $memberCount,
	'verify_ok' => null,
	'bench_profile' => 'world-record-inner',
	'encode_only' => true,
	'inner_case' => $case,
	'env_snapshot' => bench_world_record_inner_env_snapshot(),
);

$payload = array(
	'generated' => date('c'),
	'bench_profile' => 'world-record-inner',
	'cases' => array($caseRow),
);
file_put_contents($outJson, json_encode($payload, JSON_PRETTY_PRINT));

fwrite(STDERR, "[enwik8_inner] {$case}: fzc={$fzcBytes} B in " . number_format($sec, 1) . "s → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\n";
