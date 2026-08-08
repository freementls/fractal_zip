#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: test_files78 (silesia.zip) peels to same logical members as test_files133; FZHM sizes should be close.
 *
 * Usage: php benchmarks/smoke_test_files78_equiv_133.php [--quick]
 *   --quick: logical-bundle resolution only (no encode)
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
fractal_zip_ensure_folder_logical_bundle_loaded();

$quick = in_array('--quick', $argv, true);
$dir78 = $repo . DIRECTORY_SEPARATOR . 'test_files78';
$dir133 = $repo . DIRECTORY_SEPARATOR . 'test_files133';
if (!is_dir($dir78) || !is_dir($dir133)) {
	fwrite(STDERR, "SKIP smoke_test_files78_equiv_133: missing test_files78 or test_files133\n");
	exit(0);
}

$fz = new fractal_zip(256, false, true, null, false);
$disk78 = $fz->collect_raw_files_for_bundle($dir78);
$disk133 = $fz->collect_raw_files_for_bundle($dir133);
$logical78 = fractal_zip_resolve_folder_logical_bundle($disk78);
$logical133 = fractal_zip_resolve_folder_logical_bundle($disk133);
$m78 = $logical78['members'] ?? array();
$m133 = $logical133['members'] ?? array();
if (count($m78) < 2 || empty($logical78['expanded'])) {
	fwrite(STDERR, "FAIL: test_files78 did not peel to multi-member logical bundle (members=" . count($m78) . ")\n");
	exit(1);
}
if (count($m78) !== count($m133)) {
	fwrite(STDERR, "FAIL: member count mismatch 78=" . count($m78) . " 133=" . count($m133) . "\n");
	exit(1);
}
ksort($m78, SORT_STRING);
ksort($m133, SORT_STRING);
foreach (array_keys($m78) as $name) {
	if (!isset($m133[$name])) {
		fwrite(STDERR, "FAIL: member {$name} missing from 133\n");
		exit(1);
	}
	if ($m78[$name] !== $m133[$name]) {
		fwrite(STDERR, "FAIL: payload mismatch for {$name}\n");
		exit(1);
	}
}
fwrite(STDOUT, "OK logical peel: members=" . count($m78) . " expanded=1\n");
if ($quick) {
	exit(0);
}

$work78 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_78smoke_' . getmypid();
@mkdir($work78, 0700, true);
foreach ($disk78 as $p => $b) {
	file_put_contents($work78 . DIRECTORY_SEPARATOR . $p, $b);
}
$enc = new fractal_zip(256, false, true, null, false);
ob_start();
try {
	$enc->zip_folder($work78, false);
} finally {
	ob_end_clean();
}
$fzc78 = $work78 . '.fz';
if (!is_file($fzc78)) {
	fwrite(STDERR, "FAIL: no .fz for peeled 78\n");
	exit(1);
}
$bytes78 = strlen((string) file_get_contents($fzc78));
$refPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.silesia133_pmb.json';
$refBytes = null;
if (is_file($refPath)) {
	$ref = json_decode((string) file_get_contents($refPath), true);
	if (is_array($ref) && isset($ref['fzc_bytes'])) {
		$refBytes = (int) $ref['fzc_bytes'];
	}
}
if ($refBytes !== null) {
	$delta = abs($bytes78 - $refBytes);
	$tol = max(65536, (int) ($refBytes * 0.002));
	if ($delta > $tol) {
		fwrite(STDERR, "FAIL: fzc_bytes {$bytes78} vs ref {$refBytes} (delta {$delta} > tol {$tol})\n");
		exit(1);
	}
	fwrite(STDOUT, "OK fzc_bytes={$bytes78} ref={$refBytes} delta={$delta}\n");
} else {
	fwrite(STDOUT, "OK fzc_bytes={$bytes78} (no .silesia133_pmb.json ref)\n");
}
exit(0);
