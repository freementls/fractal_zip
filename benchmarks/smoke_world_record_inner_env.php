#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Assert inner env presets set expected keys (no encode). */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_inner_env.php';

bench_world_record_apply_inner_focus_env();
$fail = 0;
$expect = array(
	'FRACTAL_ZIP_PAQ_NATIVE_COMPARE' => '0',
	'FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES' => '1',
	'FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_LITERAL_JOBS' => '0',
	'FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER' => '96',
	'FRACTAL_ZIP_FOLDER_UNIFIED_STREAM' => '1',
);
foreach ($expect as $k => $want) {
	$got = getenv($k);
	if ($got === false || (string) $got !== $want) {
		fwrite(STDERR, "FAIL {$k}: want {$want}, got " . var_export($got, true) . "\n");
		$fail++;
	}
}
$probe = getenv('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES');
if ($probe !== false && (int) $probe > 8388608) {
	fwrite(STDERR, "WARN outer predict probe > 8 MiB: {$probe} (inner_focus should not call apply_high_env)\n");
}
if ($fail > 0) {
	exit(1);
}
echo "smoke_world_record_inner_env: ok\n";
