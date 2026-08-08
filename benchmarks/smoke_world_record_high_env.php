#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SPEED=1');
require_once $repo . '/benchmarks/bench_world_record_env.php';
require_once $repo . '/benchmarks/bench_world_record_high_env.php';
bench_world_record_apply_env_defaults();
bench_world_record_apply_high_env();

$checks = array(
	'FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES' => '512',
	'FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES' => '134217728',
	'FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH' => '1',
	'FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC' => '60',
	'FRACTAL_ZIP_ZPAQ_OUTER_METHODS' => '9,8,7,6,5,4,3',
	'FRACTAL_ZIP_IMPROVEMENT_THRESHOLD' => '0.005',
);
foreach ($checks as $k => $expect) {
	$got = getenv($k);
	if ($got === false || (string) $got !== $expect) {
		fwrite(STDERR, "FAIL {$k}: expected {$expect}, got " . var_export($got, true) . "\n");
		exit(1);
	}
}
fwrite(STDERR, "OK smoke_world_record_high_env\n");
