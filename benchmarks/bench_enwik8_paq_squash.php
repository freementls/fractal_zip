#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Measure squash-style PAQ on raw test_files109/enwik8 (same as run_benchmarks best_ext phda9 row).
 *
 * Usage: php benchmarks/bench_enwik8_paq_squash.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

bench_world_record_apply_env_defaults();

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
$src = $dir . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$tools = fractal_zip_paq_discover_tools();
echo 'tools: ' . (count($tools) ? implode(',', array_keys($tools)) : '(none)') . "\n";

$rel = 'enwik8';
$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpaqbench_' . bin2hex(random_bytes(8));
if (is_dir($box)) {
	array_map('unlink', glob($box . DIRECTORY_SEPARATOR . '*') ?: array());
	@rmdir($box);
}
mkdir($box, 0755, true);
if (!@copy($src, $box . DIRECTORY_SEPARATOR . $rel)) {
	fwrite(STDERR, "copy failed\n");
	exit(1);
}

$t0 = microtime(true);
$r = fractal_zip_paq_smallest_single_file_archive($box, $rel);
$sec = microtime(true) - $t0;
array_map('unlink', glob($box . DIRECTORY_SEPARATOR . '*') ?: array());
@rmdir($box);

$bytes = is_string($r['bytes']) ? strlen($r['bytes']) : null;
$wirePath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.fzpq';
$wireBytes = null;
if ($bytes !== null && is_string($r['tool'])) {
	$wire = fractal_zip_paq_wrap_wire($r['tool'], $r['bytes']);
	$wireBytes = strlen($wire);
	if (file_put_contents($wirePath, $wire) === false) {
		fwrite(STDERR, "Failed to write {$wirePath}\n");
		exit(1);
	}
}
$out = array(
	'generated' => date('c'),
	'source_dir' => $dir,
	'bytes' => $bytes,
	'tool' => $r['tool'],
	'seconds' => round((float) $r['seconds'], 6),
	'wall_seconds' => round($sec, 6),
	'hutter_record' => 15284944,
	'wire_bytes' => $wireBytes,
	'wire_path' => $wirePath,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

if ($bytes === null) {
	fwrite(STDERR, "PAQ squash failed (no output). See tools list.\n");
	exit(1);
}

echo 'enwik8 PAQ squash (' . ($r['tool'] ?? '?') . ")\n";
echo '  bytes: ' . number_format($bytes) . ' (' . number_format($bytes / (1024 * 1024), 2) . " MiB)\n";
echo '  wire: ' . number_format((int) $wireBytes) . " B → {$wirePath}\n";
echo '  seconds: ' . number_format($sec, 1) . "\n";
echo '  vs Hutter: ' . number_format($bytes - 15284944) . " B delta\n";
echo "  json: {$path}\n";

$merge = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'merge_enwik8_squash_into_world_record.php';
if (is_file($merge)) {
	$phpBin = PHP_BINARY ?: 'php';
	passthru(escapeshellarg($phpBin) . ' ' . escapeshellarg($merge), $mc);
}
