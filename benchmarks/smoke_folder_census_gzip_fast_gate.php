#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: census textish gate blocks auto gzip-fast on synthetic text-heavy tree (≥128 MiB raw).
 *
 * php benchmarks/smoke_folder_census_gzip_fast_gate.php
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
putenv('FRACTAL_ZIP_FOLDER_CENSUS_GZIP_FAST_AUTO=1');

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_census_gate_' . bin2hex(random_bytes(4));
if (!@mkdir($tmp, 0755, true) && !is_dir($tmp)) {
	fwrite(STDERR, "cannot mkdir {$tmp}\n");
	exit(1);
}

$pad = str_repeat('x', 1024 * 1024);
for ($i = 0; $i < 140; $i++) {
	file_put_contents($tmp . DIRECTORY_SEPARATOR . 'page' . $i . '.html', "<html><body>{$pad}</body></html>");
}

$census = fractal_zip::folder_bundle_census_from_dir($tmp);
$ratio = (float) ($census['textish_ratio'] ?? 0);
$fz = new fractal_zip();
$useFast = $fz->should_use_large_folder_gzip_fast_path($tmp);

foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
	@unlink($f);
}
@rmdir($tmp);

if ($ratio < 0.35) {
	fwrite(STDERR, "FAIL: expected textish_ratio >= 0.35, got {$ratio}\n");
	exit(1);
}
if ($useFast) {
	fwrite(STDERR, "FAIL: text-heavy tree should not use auto gzip-fast (textish gate)\n");
	exit(1);
}

fwrite(STDOUT, "OK smoke_folder_census_gzip_fast_gate (textish_ratio=" . round($ratio, 3) . ")\n");
exit(0);
