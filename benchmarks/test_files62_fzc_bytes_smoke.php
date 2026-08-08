<?php

declare(strict_types=1);

/**
 * Floor check: test_files62 .fz should stay competitive with native folder baselines.
 * Current production winner is the internal raw-disk FZC container with 7z outer;
 * allow small tool/path metadata drift while guarding against falling back to ~950 B restored-peeler wire.
 *
 * Run: php benchmarks/test_files62_fzc_bytes_smoke.php
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

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_tf62_floor_' . bin2hex(random_bytes(6));
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

$maxOk = 660;
if ($sz < 1 || $sz > $maxOk) {
	fwrite(STDERR, "FAIL: test_files62 .fz size {$sz} B (expected 1..{$maxOk})\n");
	exit(1);
}

fwrite(STDOUT, "OK test_files62 .fz floor smoke ({$sz} B ≤ {$maxOk} B).\n");
exit(0);
