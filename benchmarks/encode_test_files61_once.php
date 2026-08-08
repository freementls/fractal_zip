#!/usr/bin/env php
<?php
declare(strict_types=1);
$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
require_once $repo . '/fractal_zip.php';
$d = $repo . '/test_files61';
$fzc = $d . '.fz';
@unlink($fzc);
$t0 = microtime(true);
try {
	(new fractal_zip())->zip_folder($d, false);
	$sec = microtime(true) - $t0;
	echo 'fzc=' . (is_file($fzc) ? filesize($fzc) : 0) . ' sec=' . round($sec, 1) . "\n";
} catch (Throwable $e) {
	echo 'error: ' . $e->getMessage() . "\n";
	exit(1);
}
