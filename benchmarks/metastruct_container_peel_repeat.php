#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Repeat A/B n times to gauge encode variance (test_files61 peel).
 *
 *   php benchmarks/metastruct_container_peel_repeat.php test_files61 3 --ultra
 */
putenv('FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1');
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_metastruct_adapt.php';

$corpus = 'test_files61';
$runs = 3;
$ultra = in_array('--ultra', $argv, true);
foreach ($argv as $i => $arg) {
	if ($i === 0 || str_starts_with($arg, '-')) {
		continue;
	}
	if (ctype_digit($arg)) {
		$runs = max(1, min(10, (int) $arg));
	} else {
		$corpus = $arg;
	}
}

if ($ultra) {
	bench_metastruct_apply_ultra_env_defaults();
}
putenv('FRACTAL_ZIP_WEB_REF=0');

$path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
if (!is_dir($path)) {
	fwrite(STDERR, "missing: {$path}\n");
	exit(1);
}

echo "repeat peel A/B corpus={$corpus} runs={$runs} ultra=" . ($ultra ? '1' : '0') . "\n\n";

for ($i = 1; $i <= $runs; $i++) {
	foreach (array(false, true) as $peel) {
		putenv('FRACTAL_ZIP_METastruct');
		putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL');
		if ($peel) {
			putenv('FRACTAL_ZIP_METastruct=1');
			putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL=1');
		}
		$row = bench_metastruct_run_mode($path, 'default', $peel);
		echo sprintf(
			"run=%d %-4s fzc=%8d verify=%s\n",
			$i,
			$peel ? 'peel' : 'base',
			(int) $row['fzc_bytes'],
			$row['verify_ok'] ? 'OK' : 'FAIL'
		);
	}
	echo "\n";
}
