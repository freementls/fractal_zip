#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: peel multi-member PKZIP at folder level → FZHM+FZHR → extract restores verbatim .zip.
 *
 * Usage: php benchmarks/smoke_logical_zip_folder.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
fractal_zip_ensure_folder_logical_bundle_loaded();

if (!class_exists(ZipArchive::class)) {
	fwrite(STDERR, "SKIP smoke_logical_zip_folder: ZipArchive unavailable\n");
	exit(0);
}

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_logical_zip_' . getmypid();
@mkdir($work, 0700, true);
$a = str_repeat("alpha payload line\n", 500);
$b = str_repeat("beta payload line\n", 400);
$c = str_repeat("gamma payload line\n", 300);
$zipPath = $work . DIRECTORY_SEPARATOR . 'bundle.zip';
$origZip = $work . DIRECTORY_SEPARATOR . 'orig.zip';
$za = new ZipArchive();
if ($za->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
	fwrite(STDERR, "FAIL: cannot create test zip\n");
	exit(1);
}
$za->addFromString('one', $a);
$za->addFromString('two', $b);
$za->addFromString('three', $c);
$za->close();
copy($zipPath, $origZip);
$origBytes = file_get_contents($origZip);
if ($origBytes === false) {
	fwrite(STDERR, "FAIL: read orig zip\n");
	exit(1);
}

$caseDir = $work . DIRECTORY_SEPARATOR . 'case';
@mkdir($caseDir, 0700, true);
copy($zipPath, $caseDir . DIRECTORY_SEPARATOR . 'bundle.zip');

$fz = new fractal_zip(256, false, true, null, false);
$disk = $fz->collect_raw_files_for_bundle($caseDir);
$logical = fractal_zip_resolve_folder_logical_bundle($disk);
if (count($logical['members'] ?? array()) < 2 || empty($logical['expanded'])) {
	fwrite(STDERR, "FAIL: zip did not peel to logical members\n");
	exit(1);
}
$wires = array();
foreach ($logical['members'] as $path => $bytes) {
	$wire = $fz->encode_isolated_single_file_fzc_wire((string) $path, (string) $bytes);
	if ($wire === null || $wire === '') {
		fwrite(STDERR, "FAIL: isolated encode for {$path}\n");
		exit(1);
	}
	$wires[(string) $path] = $wire;
}
$wire = fractal_zip_encode_fzhm_v1_with_restore($wires, $logical['restore'] ?? array());
if (strpos($wire, 'FZHR') === false) {
	fwrite(STDERR, "FAIL: expected FZHR restore trailer\n");
	exit(1);
}

$scratch = $work . DIRECTORY_SEPARATOR . 'ex';
@mkdir($scratch, 0700, true);
$fzcPath = $scratch . DIRECTORY_SEPARATOR . 'bundle.fz';
file_put_contents($fzcPath, $wire);
$fx = new fractal_zip(256, false, true, null, false);
ob_start();
try {
	$fx->open_container($fzcPath, false);
} finally {
	ob_end_clean();
}
$gotZip = $scratch . DIRECTORY_SEPARATOR . 'bundle.zip';
if (!is_file($gotZip)) {
	fwrite(STDERR, "FAIL: bundle.zip not restored\n");
	exit(1);
}
if (is_file($scratch . DIRECTORY_SEPARATOR . 'one') || is_file($scratch . DIRECTORY_SEPARATOR . 'two')) {
	fwrite(STDERR, "FAIL: loose peeled members leaked to extract root\n");
	exit(1);
}
$gotBytes = file_get_contents($gotZip);
if ($gotBytes !== $origBytes) {
	if (!fractal_zip_folder_container_semantic_files_equal($origZip, $gotZip)) {
		fwrite(STDERR, "FAIL: restored zip mismatch (semantic and strict)\n");
		exit(1);
	}
}

fwrite(STDOUT, "OK smoke_logical_zip_folder wire=" . strlen($wire) . " B members=" . count($logical['members']) . "\n");
exit(0);
