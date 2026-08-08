<?php

declare(strict_types=1);

/**
 * Round-trip native-folder 7z: bench-identical raw {@code .7z} bytes (default encode when native beats fractal wire), optional legacy {@code N}/{@code FZ} prefixes.
 *
 * Run: php benchmarks/fz_fz7_compact_folder_roundtrip_smoke.php
 * Skips with exit 0 when 7z is missing.
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

if (fractal_zip::seven_zip_executable() === null) {
	fwrite(STDOUT, "SKIP fz_fz7_compact_folder_roundtrip_smoke (7z not on PATH)\n");
	exit(0);
}

$tag = 'fz_fz7c_rt_' . bin2hex(random_bytes(6));
$src = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . '_src';
$fzClean = new fractal_zip(256, false, false, null, false);
if (!@mkdir($src . DIRECTORY_SEPARATOR . 'sub', 0755, true)) {
	fwrite(STDERR, "mkdir failed: {$src}\n");
	exit(1);
}
file_put_contents($src . DIRECTORY_SEPARATOR . 'hello.txt', "7z-compact-a\n");
file_put_contents($src . DIRECTORY_SEPARATOR . 'sub' . DIRECTORY_SEPARATOR . 'deep.txt', "7z-compact-b\n");

$fzTry = new fractal_zip(256, false, false, null, false);
$native = $fzTry->try_build_folder_7z_native_archive_blob($src, null);
$fzClean->recursive_remove_directory($src);
if ($native === null || $native === '' || !fractal_zip::payload_has_raw_seven_zip_signature($native)) {
	fwrite(STDERR, "FAIL: try_build_folder_7z_native_archive_blob\n");
	exit(1);
}

$expect = array('hello.txt' => "7z-compact-a\n", 'sub/deep.txt' => "7z-compact-b\n");
$verifyExtracted = static function (string $exDir, array $expect): int {
	$bad = 0;
	foreach ($expect as $rel => $bytes) {
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
	return $bad;
};

$runOpenAndVerify = static function (
	string $label,
	string $wire,
	string $tagSuffix,
	fractal_zip $fzClean,
	array $expect
) use ($verifyExtracted, $tag): int {
	if ($wire === '') {
		fwrite(STDERR, "FAIL: {$label} empty wire\n");
		return 1;
	}
	$exDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $tag . $tagSuffix;
	if (!@mkdir($exDir, 0755, true)) {
		fwrite(STDERR, "mkdir failed: {$exDir}\n");
		return 1;
	}
	$fzcPath = $exDir . DIRECTORY_SEPARATOR . 'pack.fz';
	if (@file_put_contents($fzcPath, $wire) === false) {
		$fzClean->recursive_remove_directory($exDir);
		fwrite(STDERR, "FAIL: {$label} write .fz\n");
		return 1;
	}
	$fz = new fractal_zip(256, false, false, null, false);
	ob_start();
	$fz->open_container($fzcPath, false);
	ob_end_clean();
	$bad = $verifyExtracted($exDir, $expect);
	$fzClean->recursive_remove_directory($exDir);
	return $bad;
};

if ($runOpenAndVerify('raw', $native, '_ex_raw', $fzClean, $expect) !== 0) {
	exit(1);
}

$b7 = fractal_zip::NATIVE_FOLDER_7Z_WIRE_BYTE;
$wireN = $b7 . $native;
if ($wireN === '' || $wireN[0] !== $b7 || !fractal_zip::payload_has_raw_seven_zip_signature(substr($wireN, 1))) {
	fwrite(STDERR, "FAIL: legacy N-prefixed wire shape\n");
	exit(1);
}
if ($runOpenAndVerify('n1', $wireN, '_ex_n', $fzClean, $expect) !== 0) {
	exit(1);
}

$wireFz = 'FZ' . $native;
if (substr($wireFz, 0, 2) !== 'FZ' || !fractal_zip::payload_has_raw_seven_zip_signature(substr($wireFz, 2))) {
	fwrite(STDERR, "FAIL: legacy FZ-prefixed wire shape\n");
	exit(1);
}
if ($runOpenAndVerify('fz_legacy', $wireFz, '_ex_fz', $fzClean, $expect) !== 0) {
	exit(1);
}

fwrite(
	STDOUT,
	'OK fz_fz7_compact_folder_roundtrip_smoke (raw=' . (string) strlen($native) . ' B, N=' . (string) strlen($wireN) . ' B, FZ=' . (string) strlen($wireFz) . " B).\n"
);
exit(0);
