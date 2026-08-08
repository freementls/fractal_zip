#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$dir = $repo . DIRECTORY_SEPARATOR . 'test_files177';
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

function enc(string $dir, string $cvt): int
{
	putenv('FRACTAL_ZIP_CONVERT_PNG_TO_BMP=' . $cvt);
	$fzc = $dir . '.fz';
	@unlink($fzc);
	$t0 = microtime(true);
	(new fractal_zip())->zip_folder($dir, false);
	$sec = microtime(true) - $t0;
	$bytes = is_file($fzc) ? (int) filesize($fzc) : 0;
	printf("convert=%s fzc=%s sec=%.1f\n", $cvt, number_format($bytes), $sec);
	return $bytes;
}

$base = enc($dir, '0');
$cvt = enc($dir, '1');
$delta = $base - $cvt;
printf("delta=%s%s\n", $delta >= 0 ? '+' : '', number_format($delta));
