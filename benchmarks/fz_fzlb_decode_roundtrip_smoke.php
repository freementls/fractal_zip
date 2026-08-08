<?php

declare(strict_types=1);

/**
 * Decode path for native-folder {@code FZLB\x01} + bench-style tar stream compressed with brotli.
 *
 * Run: php benchmarks/fz_fzlb_decode_roundtrip_smoke.php
 *
 * Skips with exit 0 when {@code tar} or {@code brotli} is missing (CI images without compressor deps).
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

if (trim((string) shell_exec('command -v tar 2>/dev/null')) === '') {
	fwrite(STDOUT, "SKIP fz_fzlb_decode_roundtrip_smoke (tar not on PATH)\n");
	exit(0);
}
if (fractal_zip::brotli_executable() === null) {
	fwrite(STDOUT, "SKIP fz_fzlb_decode_roundtrip_smoke (brotli not on PATH)\n");
	exit(0);
}

$tag = 'fz_fzlb_rt_' . bin2hex(random_bytes(6));
$src = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '_src';
$fzClean = new fractal_zip(256, false, false, null, false);
if (!@mkdir($src . DIRECTORY_SEPARATOR . 'sub', 0755, true)) {
	fwrite(STDERR, "mkdir failed: {$src}\n");
	exit(1);
}
$wantA = "fzlb-smoke-a\n";
$wantB = "fzlb-smoke-b\n";
file_put_contents($src . DIRECTORY_SEPARATOR . 'hello.txt', $wantA);
file_put_contents($src . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'deep.txt', $wantB);

$fzTry = new fractal_zip(256, false, false, null, false);
$brBlob = $fzTry->try_build_folder_brotli_native_tar_br_blob($src, null);
$fzClean->recursive_remove_directory($src);
if ($brBlob === null || $brBlob === '') {
	fwrite(STDERR, "FAIL: try_build_folder_brotli_native_tar_br_blob returned empty\n");
	exit(1);
}

$wire = "FZLB\x01" . $brBlob;
$exDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '_ex';
if (!@mkdir($exDir, 0755, true)) {
	fwrite(STDERR, "mkdir failed: {$exDir}\n");
	exit(1);
}
$fzcPath = $exDir . DIRECTORY_SEPARATOR . 'pack.fz';
if (@file_put_contents($fzcPath, $wire) === false) {
	$fzClean->recursive_remove_directory($exDir);
	fwrite(STDERR, "FAIL: write .fz\n");
	exit(1);
}

$fz2 = new fractal_zip(256, false, false, null, false);
ob_start();
$fz2->open_container($fzcPath, false);
ob_end_clean();

$bad = 0;
foreach (array('hello.txt' => $wantA, 'sub/deep.txt' => $wantB) as $rel => $bytes) {
	$p = $exDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	if (!is_file($p)) {
		$bad++;
		fwrite(STDERR, "missing extracted: {$rel}\n");
		continue;
	}
	$got = file_get_contents($p);
	if ($got === false || $got !== $bytes) {
		$bad++;
		fwrite(STDERR, "content mismatch: {$rel}\n");
	}
}

$fzClean->recursive_remove_directory($exDir);

if ($bad !== 0) {
	exit(1);
}

fwrite(STDOUT, 'OK fz_fzlb_decode_roundtrip_smoke (' . (string) strlen($wire) . " B wire).\n");
exit(0);
