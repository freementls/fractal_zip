#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Decode FZHM bundle, extract, compare to source corpus.
 *
 * Usage:
 *   php benchmarks/inspect_fzhm_extract.php [path/to.corpus.fz] [source_dir]
 *
 * Defaults: first existing among benchmarks/.work/test_files78.fz, test_files133.fz paths;
 * source_dir inferred (test_files78 → silesia.zip semantic; test_files133 → loose members SHA1).
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
fractal_zip_ensure_folder_logical_bundle_loaded();

$candidates = array(
	$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.work' . DIRECTORY_SEPARATOR . 'test_files78.fz',
	$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.work' . DIRECTORY_SEPARATOR . 'test_files133.fz',
	$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.work' . DIRECTORY_SEPARATOR . 'test_files133_extracted' . DIRECTORY_SEPARATOR . 'test_files133.fz',
);
$fzc = isset($argv[1]) && $argv[1] !== '' && $argv[1][0] !== '-' ? $argv[1] : null;
if ($fzc === null) {
	foreach ($candidates as $c) {
		if (is_file($c)) {
			$fzc = $c;
			break;
		}
	}
}
if ($fzc === null || !is_file($fzc)) {
	fwrite(STDERR, "Usage: php benchmarks/inspect_fzhm_extract.php [corpus.fz] [source_dir]\n");
	fwrite(STDERR, "No default .fz found under benchmarks/.work/ — pass path from a bench run.\n");
	exit(1);
}
$fzc = realpath($fzc) ?: $fzc;

$srcDir = isset($argv[2]) && $argv[2] !== '' ? $argv[2] : null;
if ($srcDir === null) {
	if (strpos($fzc, 'test_files78') !== false) {
		$srcDir = $repo . DIRECTORY_SEPARATOR . 'test_files78';
	} else {
		$srcDir = $repo . DIRECTORY_SEPARATOR . 'test_files133';
	}
}
$srcDir = realpath($srcDir) ?: $srcDir;
if (!is_dir($srcDir)) {
	fwrite(STDERR, "missing source dir: {$srcDir}\n");
	exit(1);
}

$raw = file_get_contents($fzc);
if ($raw === false) {
	fwrite(STDERR, "cannot read {$fzc}\n");
	exit(1);
}
fwrite(STDOUT, 'fzc=' . $fzc . ' len=' . strlen($raw) . ' magic=' . substr($raw, 0, 4) . "\n");

fractal_zip_ensure_folder_per_member_best_loaded();
$members = fractal_zip_decode_fzhm_v1_members($raw);
if ($members === null) {
	fwrite(STDERR, "decode_fzhm failed\n");
	exit(1);
}
fwrite(STDOUT, 'members=' . count($members) . "\n");
foreach ($members as $path => $wire) {
	fwrite(STDOUT, '  ' . $path . ' wire=' . strlen($wire) . ' magic=' . substr($wire, 0, 4) . "\n");
}

$scratch = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzhm_insp_' . getmypid();
@mkdir($scratch, 0700, true);
$dest = $scratch;
copy($fzc, $scratch . DIRECTORY_SEPARATOR . 'bundle.fz');

$fz = new fractal_zip(256, false, true, null, false);
try {
	$fz->open_container($scratch . DIRECTORY_SEPARATOR . 'bundle.fz', false);
} catch (Throwable $e) {
	fwrite(STDERR, 'open_container FAIL: ' . $e->getMessage() . "\n");
	exit(1);
}

// test_files78: single silesia.zip on disk
$zipOnDisk = null;
foreach (scandir($srcDir) ?: array() as $f) {
	if ($f === '.' || $f === '..') {
		continue;
	}
	if (str_ends_with(strtolower($f), '.zip') && is_file($srcDir . DIRECTORY_SEPARATOR . $f)) {
		$zipOnDisk = $srcDir . DIRECTORY_SEPARATOR . $f;
		break;
	}
}
if ($zipOnDisk !== null) {
	$restored = null;
	foreach (scandir($dest) ?: array() as $f) {
		if ($f === '.' || $f === '..') {
			continue;
		}
		if (str_ends_with(strtolower($f), '.zip') && is_file($dest . DIRECTORY_SEPARATOR . $f)) {
			$restored = $dest . DIRECTORY_SEPARATOR . $f;
			break;
		}
	}
	if ($restored === null) {
		fwrite(STDERR, "FAIL: no .zip restored under extract dir\n");
		exit(1);
	}
	if (file_get_contents($restored) === file_get_contents($zipOnDisk)) {
		fwrite(STDOUT, "OK strict zip round-trip: " . basename($restored) . "\n");
		exit(0);
	}
	if (fractal_zip_folder_container_semantic_files_equal($zipOnDisk, $restored)) {
		fwrite(STDOUT, "OK semantic zip round-trip: " . basename($restored) . "\n");
		exit(0);
	}
	fwrite(STDERR, "FAIL: zip semantic+strict mismatch\n");
	exit(1);
}

$bad = 0;
foreach (scandir($srcDir) ?: array() as $f) {
	if ($f === '.' || $f === '..' || $f === '.fz') {
		continue;
	}
	if (!is_file($srcDir . DIRECTORY_SEPARATOR . $f)) {
		continue;
	}
	$want = file_get_contents($srcDir . DIRECTORY_SEPARATOR . $f);
	$got = @file_get_contents($dest . DIRECTORY_SEPARATOR . $f);
	if ($want !== $got) {
		fwrite(STDERR, "mismatch: {$f}\n");
		$bad++;
	}
}
if ($bad > 0) {
	fwrite(STDERR, "FAIL {$bad} members\n");
	exit(1);
}
fwrite(STDOUT, "OK round-trip all loose members\n");
exit(0);
