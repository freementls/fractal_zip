<?php

declare(strict_types=1);

/**
 * Small unified-stream folder: zip_folder → open_container beside .fz → byte-compare sources.
 * Catches regressions in native FreeArc passthrough (`arc_native`) and legacy outers alike.
 *
 * Run: php benchmarks/fz_arc_native_roundtrip_smoke.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

foreach ([
	'FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP',
	'FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER',
	'FRACTAL_ZIP_FOLDER_UNIFIED_STREAM',
	'FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER',
] as $k) {
	putenv($k);
}

$tag = 'fz_arc_rt_' . bin2hex(random_bytes(6));
$src = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '_src';
$fzCopy = new fractal_zip(256, false, false, null, false);
if (!@mkdir($src . DIRECTORY_SEPARATOR . 'sub', 0755, true)) {
	fwrite(STDERR, "mkdir failed: {$src}\n");
	exit(1);
}
file_put_contents($src . DIRECTORY_SEPARATOR . 'hello.txt', "arc-smoke-a\n");
file_put_contents($src . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'deep.txt', "arc-smoke-b\n");

$fz = new fractal_zip(256, false, false, null, false);
ob_start();
$fz->zip_folder($src, false);
ob_end_clean();

$ext = $fz->fractal_zip_container_file_extension;
$fzcPath = $src . $ext;
if (!is_file($fzcPath)) {
	$fzCopy->recursive_remove_directory($src);
	fwrite(STDERR, "FAIL: missing .fz at {$fzcPath}\n");
	exit(1);
}

$containerSize = (int) filesize($fzcPath);
$headProbe = @file_get_contents($fzcPath, false, null, 0, 4);
$headKind = (is_string($headProbe) && strlen($headProbe) === 4 && $headProbe[0] === 'A' && $headProbe[1] === 'r' && $headProbe[2] === 'C' && $headProbe[3] === "\x01")
	? 'raw_arc_magic'
	: 'other_outer';

$exDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '_ex';
if (!@mkdir($exDir, 0755, true)) {
	$fzCopy->recursive_remove_directory($src);
	fwrite(STDERR, "mkdir failed: {$exDir}\n");
	exit(1);
}
$exFzc = $exDir . DIRECTORY_SEPARATOR . 'pack.fz';
if (!@copy($fzcPath, $exFzc)) {
	$fzCopy->recursive_remove_directory($src);
	$fzCopy->recursive_remove_directory($exDir);
	fwrite(STDERR, "copy failed\n");
	exit(1);
}

$fz2 = new fractal_zip(256, false, false, null, false);
ob_start();
$fz2->open_container($exFzc, false);
ob_end_clean();

$want = array(
	'hello.txt' => "arc-smoke-a\n",
	'sub/deep.txt' => "arc-smoke-b\n",
);
$bad = 0;
foreach ($want as $rel => $bytes) {
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

$fzCopy->recursive_remove_directory($src);
$fzCopy->recursive_remove_directory($exDir);

if ($bad !== 0) {
	exit(1);
}

fwrite(STDOUT, "OK fz_arc_native_roundtrip_smoke (container head: {$headKind}, {$containerSize} B).\n");
exit(0);
