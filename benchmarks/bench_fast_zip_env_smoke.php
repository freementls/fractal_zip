#!/usr/bin/env php
<?php
/**
 * Verifies {@see bench_fast_zip_outer_apply_env_defaults} sets expected keys when unset.
 *   php benchmarks/bench_fast_zip_env_smoke.php
 */
declare(strict_types=1);

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_fast_zip_env.php';

foreach (['FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', 'FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES', 'FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC'] as $k) {
	putenv($k);
}

bench_fast_zip_outer_apply_env_defaults();

$fail = false;
if (getenv('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP') !== '3') {
	fwrite(STDERR, "bench_fast_zip_env_smoke: expected STAGED_FAST_OUTER_BROTLI_QUALITY_CAP=3\n");
	$fail = true;
}
if (getenv('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES') !== (string) (2 * 1024 * 1024)) {
	fwrite(STDERR, "bench_fast_zip_env_smoke: expected OUTER_PREDICT_PROBE_MAX_BYTES=2097152\n");
	$fail = true;
}
if (getenv('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC') !== '12') {
	fwrite(STDERR, "bench_fast_zip_env_smoke: expected OUTER_PREDICT_TIMEOUT_SEC=12\n");
	$fail = true;
}

putenv('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC=13');
bench_fast_zip_outer_apply_env_defaults();
if (getenv('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC') !== '13') {
	fwrite(STDERR, "bench_fast_zip_env_smoke: second apply should not override explicit TIMEOUT_SEC\n");
	$fail = true;
}

if ($fail) {
	exit(1);
}

fwrite(STDOUT, "bench_fast_zip_env_smoke: ok\n");
exit(0);
