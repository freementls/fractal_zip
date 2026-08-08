#!/usr/bin/env php
<?php
declare(strict_types=1);
$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_preprocess.php';
require_once dirname($repo) . '/convert/lib/bootstrap.php';
require_once dirname($repo) . '/convert/lib/ActionRegistry.php';

$src = $repo . '/test_files61';
$stage = sys_get_temp_dir() . '/fz61pp_' . bin2hex(random_bytes(3));
$built = fractal_zip_preprocess_build_stage($src, 'png_to_bmp');
$stage = $built['stage_dir'];
$fzcBase = $src . '.fz';
$fzcConv = $repo . '/test_files61_png_bmp_staged.fz';
@unlink($fzcConv);
$t0 = microtime(true);
(new fractal_zip())->zip_folder($stage, false);
$stSec = microtime(true) - $t0;
$stFzc = $stage . '.fz';
if (is_file($stFzc)) {
	rename($stFzc, $fzcConv);
}
fractal_zip_preprocess_rmtree($stage);
$base = is_file($fzcBase) ? (int) filesize($fzcBase) : 0;
$conv = is_file($fzcConv) ? (int) filesize($fzcConv) : 0;
printf("test_files61 baseline=%s converted(png→bmp stage)=%s delta=%s stage_sec=%.1f members=%d\n",
	$base ? number_format($base) : '—',
	$conv ? number_format($conv) : '—',
	$base && $conv ? (($d = $base - $conv) >= 0 ? '+' . number_format($d) : number_format($d)) : '—',
	$stSec,
	count($built['manifest']['members'] ?? [])
);
