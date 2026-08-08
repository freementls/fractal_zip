<?php

declare(strict_types=1);

/**
 * Smoke test: VGM/VGZ literal-bundle path uses zlib-9 probes (not speed-capped zlib-1 on large bodies),
 * and encode_literal_bundle_payload feeds disk bytes into choose_best (unwrap runs inside it).
 *
 * Run: php benchmarks/fzc_vgm_bundle_path_smoke.php
 */

$lib = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
if (!is_file($lib)) {
	fwrite(STDERR, "Missing fractal_zip.php\n");
	exit(1);
}
require_once $lib;

$fz = new fractal_zip(null, true, true, null, true);
$ref = new ReflectionClass($fz);
$m = $ref->getMethod('literal_bundle_gzip_probe_level');
$m->setAccessible(true);

$big = str_repeat('Vgm ', 40000);
$levVgm = $m->invoke($fz, $big, 'tracks/01.vgm');
$levAnon = $m->invoke($fz, $big, 'tracks/01.bin');
if ($levVgm !== 9) {
	fwrite(STDERR, "Expected gzip probe level 9 for .vgm path, got {$levVgm}\n");
	exit(1);
}
// Default FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES is 2 MiB; 160 KiB body uses zlib-9 probes.
if ($levAnon !== 9) {
	fwrite(STDERR, "Expected gzip probe level 9 for ~160 KiB non-BMP .bin (under cap), got {$levAnon}\n");
	exit(1);
}
$huge = str_repeat('x', 2200000);
$levHugeBin = $m->invoke($fz, $huge, 'blob.bin');
if ($levHugeBin !== 1) {
	fwrite(STDERR, "Expected gzip probe level 1 for >2 MiB non-BMP .bin, got {$levHugeBin}\n");
	exit(1);
}

$enc = $ref->getMethod('encode_literal_bundle_payload');
$enc->setAccessible(true);
$inner = 'Vgm ' . str_repeat("\x00", 200);
$vgzBody = gzencode($inner, 9);
if ($vgzBody === false || strlen($vgzBody) < 20) {
	fwrite(STDERR, "bad synthetic vgz\n");
	exit(1);
}
$payload = $enc->invoke($fz, array('a.vgz' => $vgzBody), null);
if (!is_string($payload) || strlen($payload) < 8) {
	fwrite(STDERR, "encode_literal_bundle_payload failed\n");
	exit(1);
}
if (strpos($payload, 'FZB') !== 0) {
	fwrite(STDERR, "unexpected payload signature\n");
	exit(1);
}

echo "OK vgm/vgz literal-bundle smoke (probe level + encode path).\n";
exit(0);
