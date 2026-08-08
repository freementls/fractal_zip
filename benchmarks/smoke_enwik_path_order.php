#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: enwik entry sort produces multi-member virtual folder (path-order eligible).
 *
 * Usage: php benchmarks/smoke_enwik_path_order.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=1');
putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
putenv('FRACTAL_ZIP_SPEED=1');

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_po_' . getmypid();
@mkdir($work, 0700, true);
$srcDir = $work . DIRECTORY_SEPARATOR . 'src';
@mkdir($srcDir, 0700, true);
file_put_contents($srcDir . DIRECTORY_SEPARATOR . 'enwik_test', fractal_zip_enwik_synthetic_blob());

$prep = fractal_zip_enwik_try_prepare_virtual_folder($srcDir);
if ($prep === null) {
	fwrite(STDERR, "FAIL: enwik virtual folder prep returned null\n");
	exit(1);
}
$memberCount = count($prep['memberRelPaths'] ?? array());
if ($memberCount < 2) {
	fwrite(STDERR, "FAIL: expected member_count > 1, got {$memberCount}\n");
	exit(1);
}

$raw = (new fractal_zip(256, false, true, null, false))->collect_raw_files_for_bundle((string) $prep['virtualDir']);
if (count($raw) < 2) {
	fwrite(STDERR, 'FAIL: collect_raw_files_for_bundle count ' . count($raw) . " < 2\n");
	exit(1);
}

fractal_zip_enwik_cleanup_virtual_folder($prep);
fractal_zip_enwik_recursive_remove($work);
fwrite(STDERR, "OK smoke_enwik_path_order (members={$memberCount})\n");
exit(0);
