#!/usr/bin/env php
<?php
/**
 * Command-line entry for fractal_zip: create a .fzc next to a folder, or extract members beside a .fzc.
 *
 * Usage:
 *   php fractal_zip_cli.php zip <directory>
 *       Writes <directory>.fzc (sibling of the folder path you pass). Example: zip test_files2 → test_files2.fzc
 *
 *   php fractal_zip_cli.php extract <path.fzc> [output_directory]
 *       Default: extracts member files into the same directory as the .fzc (matches open_container).
 *       If output_directory is set: copies the .fzc there first, then extracts next to that copy.
 *
 *   php fractal_zip_cli.php help
 *
 * Environment variables are passed through (e.g. FRACTAL_ZIP_SEGMENT_LENGTH, FRACTAL_ZIP_MULTIPASS).
 * Output from the library is suppressed unless FRACTAL_ZIP_CLI_VERBOSE=1.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "fractal_zip_cli.php is CLI-only.\n");
	exit(1);
}

$repoRoot = realpath(__DIR__) ?: __DIR__;
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';

$argvList = array_slice($argv, 1);
if ($argvList === [] || $argvList[0] === 'help' || $argvList[0] === '--help' || $argvList[0] === '-h') {
	echo <<<TXT
fractal_zip CLI

  php fractal_zip_cli.php zip <directory>
  php fractal_zip_cli.php extract <file.fzc> [output_directory]
  php fractal_zip_cli.php help

Set FRACTAL_ZIP_CLI_VERBOSE=1 to show library HTML/debug output.

TXT;
	exit(0);
}

$cmd = array_shift($argvList);
if ($cmd === 'zip') {
	if ($argvList === []) {
		fwrite(STDERR, "zip: missing directory argument.\n");
		exit(1);
	}
	$dir = $argvList[0];
	$abs = realpath($dir);
	if ($abs === false || !is_dir($abs)) {
		fwrite(STDERR, "zip: not a directory: {$dir}\n");
		exit(1);
	}
	$fzcPath = $abs . '.fzc';
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, true, null, true);
	$fz->zip_folder($abs, false);
	if (!$verbose) {
		ob_end_clean();
	}
	if (!is_file($fzcPath)) {
		fwrite(STDERR, "zip: expected output file missing: {$fzcPath}\n");
		exit(1);
	}
	$sz = filesize($fzcPath);
	echo "Wrote {$fzcPath} (" . ($sz !== false ? (string) $sz : '?') . " bytes)\n";
	exit(0);
}

if ($cmd === 'extract') {
	if ($argvList === []) {
		fwrite(STDERR, "extract: missing .fzc path.\n");
		exit(1);
	}
	$fzcIn = $argvList[0];
	$fzcAbs = realpath($fzcIn);
	if ($fzcAbs === false || !is_file($fzcAbs)) {
		fwrite(STDERR, "extract: file not found: {$fzcIn}\n");
		exit(1);
	}
	$targetFzc = $fzcAbs;
	if (isset($argvList[1]) && $argvList[1] !== '') {
		$outDir = $argvList[1];
		if (!is_dir($outDir)) {
			if (!@mkdir($outDir, 0755, true) && !is_dir($outDir)) {
				fwrite(STDERR, "extract: cannot create directory: {$outDir}\n");
				exit(1);
			}
		}
		$outAbs = realpath($outDir);
		if ($outAbs === false) {
			fwrite(STDERR, "extract: bad output directory: {$outDir}\n");
			exit(1);
		}
		$base = basename($fzcAbs);
		$targetFzc = $outAbs . DIRECTORY_SEPARATOR . $base;
		if (!@copy($fzcAbs, $targetFzc)) {
			fwrite(STDERR, "extract: copy failed to {$targetFzc}\n");
			exit(1);
		}
	}
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
	exit(0);
}

fwrite(STDERR, "Unknown command: {$cmd} (try: help)\n");
exit(1);
