#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Quick measured encode: web-ref on enwik8 unified-stream path.
 */
$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';

bench_web_ref_apply_probe_fast_defaults();
putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX=20');
putenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS=15');

$dir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'test_files109';
$fzcPath = $dir . '.fz';
@unlink($fzcPath);

$fz = new fractal_zip();
$start = microtime(true);
$fz->zip_folder($dir, false);
$elapsed = microtime(true) - $start;

if (!is_file($fzcPath)) {
	fwrite(STDERR, "fzc not found: $fzcPath\n");
	exit(1);
}

$bytes = (int) filesize($fzcPath);
$blob = (string) file_get_contents($fzcPath);
$hasFzwr = str_contains($blob, 'FZWR');
$baseline = 22043397;

fwrite(STDERR, sprintf(
	"fzc_bytes=%d baseline=%d delta=%d fzwr=%s seconds=%.1f\n",
	$bytes,
	$baseline,
	$bytes - $baseline,
	$hasFzwr ? 'yes' : 'no',
	$elapsed
));
echo $bytes . "\n";
