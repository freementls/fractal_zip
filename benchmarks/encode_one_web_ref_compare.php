#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$dir = $argv[1] ?? '';
if ($dir === '' || !is_dir($repo . DIRECTORY_SEPARATOR . $dir)) {
	fwrite(STDERR, "Usage: php benchmarks/encode_one_web_ref_compare.php test_files13\n");
	exit(1);
}
$fullDir = $repo . DIRECTORY_SEPARATOR . $dir;
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';

putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ARC_COMPARE=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0');
@ini_set('memory_limit', getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT') ?: '4G');

$results = array();
foreach (array(false, true) as $webRef) {
	putenv('FRACTAL_ZIP_WEB_REF=' . ($webRef ? '1' : '0'));
	if ($webRef) {
		bench_web_ref_apply_probe_fast_defaults();
		putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE=0');
		putenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL=1');
		putenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS=80');
	}
	$fzc = $fullDir . '.fz';
	@unlink($fzc);
	$fz = new fractal_zip();
	$t0 = microtime(true);
	$fz->zip_folder($fullDir, false);
	$sec = microtime(true) - $t0;
	$bytes = is_file($fzc) ? (int) filesize($fzc) : 0;
	$meta = $bytes > 0 ? fractal_zip_web_ref_peel_fzwr_from_blob((string) file_get_contents($fzc)) : null;
	$results[$webRef ? 'on' : 'off'] = array(
		'fzc_bytes' => $bytes,
		'seconds' => $sec,
		'fzwr_entries' => is_array($meta) ? count($meta['entries']) : 0,
	);
}
$off = (int) $results['off']['fzc_bytes'];
$on = (int) $results['on']['fzc_bytes'];
echo json_encode(array(
	'label' => $dir,
	'off' => $results['off'],
	'on' => $results['on'],
	'saved_bytes' => $off > 0 && $on > 0 ? $off - $on : null,
	'pct' => $off > 0 && $on > 0 ? round(100.0 * ($off - $on) / $off, 2) : null,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
