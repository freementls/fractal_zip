#!/usr/bin/env php
<?php
/**
 * Example: compress one directory to a sibling <folder>.fzc (fractal_zip).
 *
 * Deploy on freement.cloud (or any host): upload this file together with the
 * fractal_zip library — at minimum the same directory layout as the repo root
 * (fractal_zip.php plus the files it require_once’s next to it). Optionally set
 * FRACTAL_ZIP_PHP to the absolute path of fractal_zip.php.
 *
 * Usage:
 *   php fzc_compress.php /path/to/folder
 *
 * Output: /path/to/folder.fzc
 *
 * Tuning: same env vars as the library (e.g. FRACTAL_ZIP_MULTIPASS, FRACTAL_ZIP_SEGMENT_LENGTH).
 * Verbose HTML from the library: FRACTAL_ZIP_CLI_VERBOSE=1
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "CLI only.\n");
	exit(1);
}

$lib = getenv('FRACTAL_ZIP_PHP');
if ($lib === false || trim((string) $lib) === '') {
	$lib = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}
if (!is_file($lib)) {
	$lib = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}
if (!is_file($lib)) {
	fwrite(STDERR, "Cannot find fractal_zip.php. Copy the library beside this script or set FRACTAL_ZIP_PHP.\n");
	exit(1);
}
require_once $lib;

$argvList = array_slice($argv, 1);
if ($argvList === [] || $argvList[0] === '-h' || $argvList[0] === '--help') {
	echo "Usage: php fzc_compress.php <directory>\nWrites <directory>.fzc next to the folder.\n";
	exit($argvList === [] ? 1 : 0);
}

$dir = $argvList[0];
$abs = realpath($dir);
if ($abs === false || !is_dir($abs)) {
	fwrite(STDERR, "Not a directory: {$dir}\n");
	exit(1);
}

$fzcPath = $abs . '.fzc';
$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';
if (!$verbose) {
	ob_start();
}
$fz = new fractal_zip(null, true, true, null, true);
$fz->zip_folder($abs, false);
if (!$verbose) {
	ob_end_clean();
}

if (!is_file($fzcPath)) {
	fwrite(STDERR, "Expected output missing: {$fzcPath}\n");
	exit(1);
}
$sz = filesize($fzcPath);
echo 'Wrote ' . $fzcPath . ' (' . ($sz !== false ? (string) $sz : '?') . " bytes)\n";
