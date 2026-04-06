#!/usr/bin/env php
<?php
/**
 * Example: extract all members from a .fzc archive (fractal_zip open_container).
 *
 * Deploy on freement.cloud (or any host): upload this file together with the
 * fractal_zip library — same directory layout as the repo root next to
 * fractal_zip.php (see fzc_compress.php). Optionally set FRACTAL_ZIP_PHP.
 *
 * Usage:
 *   php fzc_extract.php /path/to/archive.fzc
 *       → files appear in the same directory as the .fzc
 *
 *   php fzc_extract.php /path/to/archive.fzc /path/to/output_dir
 *       → copies the .fzc into output_dir, then extracts beside that copy
 *
 * Tuning: library env vars apply. Verbose: FRACTAL_ZIP_CLI_VERBOSE=1
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
	echo "Usage: php fzc_extract.php <file.fzc> [output_directory]\n";
	exit($argvList === [] ? 1 : 0);
}

$fzcIn = $argvList[0];
$fzcAbs = realpath($fzcIn);
if ($fzcAbs === false || !is_file($fzcAbs)) {
	fwrite(STDERR, "File not found: {$fzcIn}\n");
	exit(1);
}

$targetFzc = $fzcAbs;
if (isset($argvList[1]) && $argvList[1] !== '') {
	$outDir = $argvList[1];
	if (!is_dir($outDir)) {
		if (!@mkdir($outDir, 0755, true) && !is_dir($outDir)) {
			fwrite(STDERR, "Cannot create directory: {$outDir}\n");
			exit(1);
		}
	}
	$outAbs = realpath($outDir);
	if ($outAbs === false) {
		fwrite(STDERR, "Bad output directory: {$outDir}\n");
		exit(1);
	}
	$base = basename($fzcAbs);
	$targetFzc = $outAbs . DIRECTORY_SEPARATOR . $base;
	if (!@copy($fzcAbs, $targetFzc)) {
		fwrite(STDERR, "Copy failed to {$targetFzc}\n");
		exit(1);
	}
}

$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';
if (!$verbose) {
	ob_start();
}
$fz = new fractal_zip(null, true, true, null, true);
$fz->open_container($targetFzc, false);
if (!$verbose) {
	ob_end_clean();
}

$root = dirname($targetFzc);
echo "Extracted members under {$root}\n";
