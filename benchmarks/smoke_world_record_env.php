#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Env-only guard: world-record preset sets key vars (no encode).
 *
 * Usage: php benchmarks/smoke_world_record_env.php
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SPEED=1');
putenv('FRACTAL_ZIP_ULTRA=0');
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
bench_world_record_apply_env_defaults();

$checks = array(
	'FRACTAL_ZIP_SPEED' => '0',
	'FRACTAL_ZIP_ULTRA' => '1',
	'FRACTAL_ZIP_ENWIK_ENTRY_SORT' => '1',
	'FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER' => '96',
	'FRACTAL_ZIP_FOLDER_UNIFIED_STREAM' => '1',
	'FRACTAL_ZIP_FOLDER_GZIP_FAST' => '0',
	'FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES' => '134217728',
	'FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES' => '134217728',
	'FRACTAL_ZIP_ZPAQ_NATIVE_FULL_SWEEP_MAX_RAW_BYTES' => '134217728',
	'FRACTAL_ZIP_ZPAQ_OUTER_SWEEP' => '1',
	'FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS' => '0',
	'FRACTAL_ZIP_ENWIK_FZBM_PATH_ORDER_RANDOM_MAX_MEMBERS' => '512',
	'FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES' => '512',
	'FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES' => '134217728',
	'FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH' => '1',
	'FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES' => '0',
	// phda9_xml promotion forces external-paq compare/sweep OFF (integrated path).
	'FRACTAL_ZIP_PAQ_NATIVE_COMPARE' => '0',
	'FRACTAL_ZIP_PAQ_SWEEP' => '0',
	'FRACTAL_ZIP_PAQ_NATIVE_MAX_RAW_BYTES' => '134217728',
	'FRACTAL_ZIP_WEB_REF' => '0',
);
foreach ($checks as $k => $expect) {
	$got = getenv($k);
	if ($got === false || (string) $got !== $expect) {
		fwrite(STDERR, "FAIL {$k}: expected {$expect}, got " . var_export($got, true) . "\n");
		exit(1);
	}
}
fwrite(STDERR, "OK smoke_world_record_env\n");
exit(0);
