<?php

declare(strict_types=1);

/**
 * Regression: raw-tier dual pick must use on-wire size (outer + FZG peel trailer), not inner compress only.
 * Without that, test_files62-style trees (~96 tiny .gz) regress to multi-KiB .fz while disk-shaped raw stays ~533 B.
 *
 * Run: php benchmarks/fzc_raw_tier_wire_dual_smoke.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$corpus = $repo . DIRECTORY_SEPARATOR . 'test_files62';
if (!is_dir($corpus)) {
	fwrite(STDERR, "Skip: missing test_files62 (build with benchmarks/build_test_files62.php)\n");
	exit(0);
}

foreach ([
	'FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP',
	'FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER',
	'FRACTAL_ZIP_FOLDER_UNIFIED_STREAM',
	'FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER',
] as $k) {
	putenv($k);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_wire_dual_' . bin2hex(random_bytes(6));
if (!@mkdir($work, 0755, true)) {
	fwrite(STDERR, "mkdir failed: {$work}\n");
	exit(1);
}

$fzCopy = new fractal_zip(256, false, false, null, false);
$fzCopy->recursive_copy_directory($corpus, $work);

$fz = new fractal_zip(256, false, false, null, false);
ob_start();
$fz->zip_folder($work, false);
ob_end_clean();

$ext = $fz->fractal_zip_container_file_extension;
$fzc = $work . $ext;
$sz = is_file($fzc) ? (int) filesize($fzc) : -1;

$fzCopy->recursive_remove_directory($work);

// Baseline ~533 B on typical TF62 (96× .gz); tight bound catches raw-tier wire regressions (multi-KiB blowups).
$maxOk = 580;
if ($sz < 1 || $sz > $maxOk) {
	fwrite(STDERR, "FAIL: unified test_files62 .fz size {$sz} B (expected 1..{$maxOk})\n");
	exit(1);
}

fwrite(STDOUT, "OK raw-tier wire dual smoke (test_files62 .fz = {$sz} B).\n");
exit(0);
