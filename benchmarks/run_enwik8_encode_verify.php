#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * World-record encode + bit-exact enwik8 roundtrip verify (merged preset incl. high tier).
 *
 * Usage:
 *   php benchmarks/run_enwik8_encode_verify.php
 *   php benchmarks/run_enwik8_encode_verify.php --name=preset_verify
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$name = 'preset_verify';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--name=')) {
		$name = substr($arg, 7);
	}
}

bench_world_record_apply_env_defaults();
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
$src = $dir . DIRECTORY_SEPARATOR . 'enwik8';
$fzcPath = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

@unlink($fzcPath);
$rawBytes = (int) filesize($src);
$rawHash = hash_file('sha256', $src);

fwrite(STDERR, "[enwik8_encode_verify] encode start (world-record+high env)\n");
$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($dir, false);
$zipSec = microtime(true) - $t0;
$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;

fwrite(STDERR, "[enwik8_encode_verify] fzc={$fzcBytes} B in " . number_format($zipSec, 1) . "s — extract/verify\n");
$t1 = microtime(true);
$fz2 = new fractal_zip();
$fz2->open_container($fzcPath);
$extSec = microtime(true) - $t1;

$restoredHash = is_file($src) ? hash_file('sha256', $src) : '';
$verifyOk = ($restoredHash !== '' && hash_equals($rawHash, $restoredHash));
$restoredBytes = is_file($src) ? (int) filesize($src) : 0;

$outJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_' . $name . '.json';
$case = array(
	'label' => 'test_files109',
	'raw_bytes' => $rawBytes,
	'fzc_bytes' => $fzcBytes,
	'zip_seconds' => round($zipSec, 4),
	'extract_seconds' => round($extSec, 4),
	'outer_codec' => fractal_zip::$last_outer_codec ?? null,
	'outer_zpaq_method' => fractal_zip::$last_zpaq_method ?? null,
	'folder_unified_stream' => fractal_zip::$used_folder_unified_stream,
	'member_count' => $fz->zip_folder_member_count > 0 ? $fz->zip_folder_member_count : null,
	'verify_ok' => $verifyOk,
	'bench_profile' => 'world-record',
	'encode_verify' => true,
);
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'bench_profile' => 'world-record',
	'cases' => array($case),
), JSON_PRETTY_PRINT));

if (!$verifyOk) {
	fwrite(STDERR, "FAIL verify: sha256 mismatch after extract\n");
	exit(1);
}

fwrite(STDERR, "OK enwik8_encode_verify fzc={$fzcBytes} zip=" . number_format($zipSec, 1) . "s verify_ok=true → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\nverify_ok=1\n";
