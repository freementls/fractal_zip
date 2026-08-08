<?php
declare(strict_types=1);

/**
 * Synthetic folder: plain file + two-member .zip (ZipArchive DEFLATE rebuild often differs from on-disk PKZIP).
 * Runs benchmarks/repro_folder_zip_verify.php and expects exit 0 (verbatim disk coercion path).
 *
 * From repo root: php benchmarks/smoke_repro_folder_zip_roundtrip.php
 *
 * CI: tests/run_php_smokes.sh phase 3b (after benchmarks/all_substrings_count_regression.php).
 */
$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$repro = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'repro_folder_zip_verify.php';
if (!is_file($repro)) {
	fwrite(STDERR, "smoke_repro_folder_zip_roundtrip: missing {$repro}\n");
	exit(2);
}

require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$fail = static function (string $msg) : void {
	fwrite(STDERR, 'smoke_repro_folder_zip_roundtrip: FAIL — ' . $msg . PHP_EOL);
	exit(1);
};

if (!class_exists(ZipArchive::class)) {
	fwrite(STDOUT, "smoke_repro_folder_zip_roundtrip: skip (no ZipArchive)\n");
	exit(0);
}

$base = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_smoke_repro_zip_' . bin2hex(random_bytes(6));
$src = $base . DIRECTORY_SEPARATOR . 'src';
if (!@mkdir($src, 0700, true)) {
	$fail('mkdir src');
}
if (file_put_contents($src . DIRECTORY_SEPARATOR . 'plain.txt', "plain\n") === false) {
	$fail('plain.txt');
}
$zipPath = $src . DIRECTORY_SEPARATOR . 'twomem.zip';
$z = new ZipArchive();
if ($z->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
	$fail('ZipArchive::open twomem.zip');
}
if (!$z->addFromString('m/a.txt', 'aa') || !$z->addFromString('m/b.txt', 'bbb')) {
	$z->close();
	$fail('addFromString');
}
if (!$z->close()) {
	$fail('zip close');
}

$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($repro) . ' ' . escapeshellarg($src) . ' --max-files=10 2>&1';
$lines = [];
$code = 1;
exec($cmd, $lines, $code);

$fz = new fractal_zip(300, true, true, null, true);
$fz->recursive_remove_directory($base);

if ($code !== 0) {
	$fail('repro_folder_zip_verify exit ' . (string) $code . ': ' . implode("\n", $lines));
}
fwrite(STDOUT, "OK smoke_repro_folder_zip_roundtrip (" . count($lines) . " repro lines)\n");
exit(0);
