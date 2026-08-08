#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build benchmarks/.enwik8_paq_squash.fzpq from enwik8 when JSON exists but wire cache is missing.
 *
 * Runs only the tool named in .enwik8_paq_squash.json (default phda9), not a full PAQ sweep.
 * Wall time is similar to bench_enwik8_paq_squash (~1–8 h for phda9 on 100 MiB).
 *
 * Usage:
 *   php benchmarks/bench_enwik8_paq_export_wire.php
 *   FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_export_wire.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

bench_world_record_apply_env_defaults();

$paths = fractal_zip_enwik_paq_squash_cache_paths($repo);
$cached = fractal_zip_enwik_try_load_paq_squash_wire($repo);
if ($cached !== null) {
	echo "OK wire cache already valid: " . number_format(strlen($cached['wire'])) . " B ({$cached['tool']})\n";
	echo "  {$paths['wire']}\n";
	exit(0);
}

$metaPath = $paths['json'];
if (!is_file($metaPath)) {
	fwrite(STDERR, "Missing {$metaPath} — run bench_enwik8_paq_squash.php first or copy metadata.\n");
	exit(1);
}
$meta = json_decode((string) file_get_contents($metaPath), true);
$tool = is_array($meta) && isset($meta['tool']) ? (string) $meta['tool'] : 'phda9';
$expectBytes = is_array($meta) && isset($meta['bytes']) ? (int) $meta['bytes'] : 0;

$dir = $repo . DIRECTORY_SEPARATOR . 'test_files109';
$src = $dir . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$tools = fractal_zip_paq_discover_tools();
if (!isset($tools[$tool])) {
	fwrite(STDERR, "Tool {$tool} not available. tools: " . (count($tools) ? implode(',', array_keys($tools)) : '(none)') . "\n");
	exit(1);
}

$rel = 'enwik8';
$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpaqexport_' . bin2hex(random_bytes(8));
mkdir($box, 0755, true);
if (!@copy($src, $box . DIRECTORY_SEPARATOR . $rel)) {
	fwrite(STDERR, "copy failed\n");
	exit(1);
}

$inputPath = $box . DIRECTORY_SEPARATOR . $rel;
echo "export wire: tool={$tool} expect_archive_bytes=" . number_format($expectBytes) . "\n";
$t0 = microtime(true);
$r = fractal_zip_paq_compress_file($tool, $tools[$tool], $inputPath);
$sec = microtime(true) - $t0;
array_map('unlink', glob($box . DIRECTORY_SEPARATOR . '*') ?: array());
@rmdir($box);

if (!is_string($r['bytes']) || $r['bytes'] === '') {
	fwrite(STDERR, "compress failed\n");
	exit(1);
}
$archiveBytes = strlen($r['bytes']);
if ($expectBytes > 0 && $archiveBytes !== $expectBytes) {
	fwrite(STDERR, "WARN: archive {$archiveBytes} B != json {$expectBytes} B — updating json on success\n");
}

$wire = fractal_zip_paq_wrap_wire($tool, $r['bytes']);
if (file_put_contents($paths['wire'], $wire) === false) {
	fwrite(STDERR, "write failed: {$paths['wire']}\n");
	exit(1);
}

$meta['generated'] = date('c');
$meta['bytes'] = $archiveBytes;
$meta['tool'] = $tool;
$meta['wire_bytes'] = strlen($wire);
$meta['export_seconds'] = round($sec, 6);
file_put_contents($metaPath, json_encode($meta, JSON_PRETTY_PRINT));

echo '  archive: ' . number_format($archiveBytes) . " B\n";
echo '  wire: ' . number_format(strlen($wire)) . " B in " . number_format($sec, 1) . "s\n";
echo "  {$paths['wire']}\n";
