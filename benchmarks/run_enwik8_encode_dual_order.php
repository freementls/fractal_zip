#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * DEPRECATED for preset work: external phda9 FZpq wrap (see docs/ENWIK8_INTEGRATED_COMPRESSION.md).
 * Prefer run_enwik8_harmony_encode.php. Requires benchmarks/.enwik8_paq_squash.fzpq.
 *
 * Usage:
 *   php benchmarks/run_enwik8_encode_dual_order.php
 *   php benchmarks/run_enwik8_encode_dual_order.php --name=dual_order
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$name = 'dual_order';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--name=')) {
		$name = substr($arg, 7);
	}
}

bench_world_record_apply_env_defaults();
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=1');

$paths = fractal_zip_enwik_paq_squash_cache_paths($repo);
if (fractal_zip_enwik_try_load_paq_squash_wire($repo) === null) {
	fwrite(STDERR, "Missing or invalid PAQ squash cache.\n");
	fwrite(STDERR, "  Run: FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_export_wire.php\n");
	fwrite(STDERR, "  json: {$paths['json']}\n");
	fwrite(STDERR, "  wire: {$paths['wire']}\n");
	exit(2);
}

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

fwrite(STDERR, "[enwik8_dual_order] encode start (sorted + squash cache PAQ)\n");
$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($dir, false);
$zipSec = microtime(true) - $t0;
$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;

fwrite(STDERR, "[enwik8_dual_order] fzc={$fzcBytes} B in " . number_format($zipSec, 1) . "s — extract/verify\n");
$t1 = microtime(true);
$fz2 = new fractal_zip();
$fz2->open_container($fzcPath);
$extSec = microtime(true) - $t1;

$restoredHash = is_file($src) ? hash_file('sha256', $src) : '';
$verifyOk = ($restoredHash !== '' && hash_equals($rawHash, $restoredHash));

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
	'dual_order' => true,
	'raw_paq_squash_cache' => true,
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

$cached = fractal_zip_enwik_try_load_paq_squash_wire($repo);
$wireBytes = $cached !== null ? strlen($cached['wire']) : null;
fwrite(STDERR, "OK dual_order fzc={$fzcBytes} wire_cache=" . ($wireBytes !== null ? number_format($wireBytes) : '?') . " B verify_ok=true → {$outJson}\n");
echo "fzc_bytes={$fzcBytes}\nverify_ok=1\n";
