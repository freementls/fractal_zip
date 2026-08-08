#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Wall-time A/B for all_substrings_count verify path (serial vs parallel GPU verify).
 *
 * Usage: php benchmarks/bench_all_substrings_count_timing.php [--repeats=3]
 */

putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$repeats = 3;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--repeats=')) {
		$repeats = max(1, (int) substr($arg, 10));
	}
}

$path = $repo . DIRECTORY_SEPARATOR . 'test_files30' . DIRECTORY_SEPARATOR . 'test_files2.txt';
if (!is_file($path)) {
	fwrite(STDERR, "SKIP missing {$path}\n");
	exit(0);
}
$raw = (string) file_get_contents($path);
if ($raw === '') {
	exit(1);
}

/** @return array{wall: float, n: int, xxh128: string} */
$run = static function (bool $parallelVerify) use ($raw, $repeats): array {
	putenv('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY=' . ($parallelVerify ? '1' : '0'));
	putenv('FRACTAL_ZIP_GPU_SUBSTRING=' . ($parallelVerify ? '1' : '0'));
	putenv('FRACTAL_ZIP_PEEL_GPU=0');
	$best = PHP_FLOAT_MAX;
	$lastMap = array();
	foreach (range(1, $repeats) as $_) {
		$fz = new fractal_zip(64, false, false, null, false);
		$t0 = microtime(true);
		$map = $fz->all_substrings_count($raw);
		$wall = microtime(true) - $t0;
		if ($wall < $best) {
			$best = $wall;
		}
		$lastMap = $map;
	}
	ksort($lastMap);
	$h = hash('xxh128', (string) (bench_json_encode_fingerprint_try($lastMap, JSON_UNESCAPED_UNICODE) ?? ''));
	return array('wall' => $best, 'n' => count($lastMap), 'xxh128' => $h);
};

$serial = $run(false);
$parallel = $run(true);
$speedup = $serial['wall'] > 0 ? round($serial['wall'] / $parallel['wall'], 2) : 0.0;
$bytesMatch = $serial['xxh128'] === $parallel['xxh128'] && $serial['n'] === $parallel['n'];

$out = array(
	'generated' => date('c'),
	'slice' => 'test_files30/test_files2.txt',
	'segment' => 64,
	'repeats' => $repeats,
	'serial_verify' => $serial,
	'parallel_verify' => $parallel,
	'speedup' => $speedup,
	'bytes_match' => $bytesMatch,
);
$jsonPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.all_substrings_count_timing.json';
file_put_contents($jsonPath, json_encode($out, bench_json_encode_options(true)));

fwrite(STDOUT, "all_substrings_count timing → {$jsonPath}\n");
fwrite(STDOUT, sprintf(
	"  serial=%.3fs parallel=%.3fs speedup=%.2f bytes_match=%s n=%d\n",
	$serial['wall'],
	$parallel['wall'],
	$speedup,
	$bytesMatch ? 'yes' : 'NO',
	$serial['n']
));
exit($bytesMatch ? 0 : 1);
