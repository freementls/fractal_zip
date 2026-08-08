#!/usr/bin/env php
<?php
declare(strict_types=1);

ini_set('memory_limit', '4096M');

/**
 * Harmony without corpus phrase mining (less RAM; isolates boilerplate + encode path).
 */
$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$name = 'harmony_light';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--name=')) {
		$name = substr($arg, 7);
	}
}

bench_world_record_apply_harmony_env();
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
$src = $dir . DIRECTORY_SEPARATOR . 'enwik8';
$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
@unlink($fzcPath);
$rawHash = is_file($src) ? hash_file('sha256', $src) : '';

fwrite(STDERR, "[enwik8_harmony_light] encode start\n");
$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($dir, false);
$sec = microtime(true) - $t0;
$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;

$t1 = microtime(true);
$fz2 = new fractal_zip();
$fz2->open_container($fzcPath);
$extSec = microtime(true) - $t1;
$verifyOk = is_file($src) && hash_equals($rawHash, hash_file('sha256', $src));

$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_' . $name . '.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'bench_profile' => 'world-record-harmony-light',
	'cases' => array(array(
		'label' => 'test_files109',
		'raw_bytes' => is_file($src) ? (int) filesize($src) : 100000000,
		'fzc_bytes' => $fzcBytes,
		'zip_seconds' => round($sec, 4),
		'extract_seconds' => round($extSec, 4),
		'verify_ok' => $verifyOk,
		'harmony_light' => true,
	)),
), JSON_PRETTY_PRINT));

if (!$verifyOk) {
	exit(1);
}
fwrite(STDERR, "[enwik8_harmony_light] fzc={$fzcBytes} B " . number_format($sec, 1) . "s → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\n";
