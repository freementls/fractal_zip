#!/usr/bin/env php
<?php
declare(strict_types=1);
/** Quick: encode test_files78 work copy, report FZHM size (no full bench). */
$mem = getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT');
if ($mem !== false && trim((string) $mem) !== '') {
	ini_set('memory_limit', trim((string) $mem));
} else {
	ini_set('memory_limit', '4G');
}
$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
$src = $repo . DIRECTORY_SEPARATOR . 'test_files78';
if (!is_dir($src)) {
	fwrite(STDERR, "SKIP: no test_files78\n");
	exit(0);
}
$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz78_quick_' . getmypid();
@mkdir($work, 0700, true);
copy($src . DIRECTORY_SEPARATOR . 'silesia.zip', $work . DIRECTORY_SEPARATOR . 'silesia.zip');
$fz = new fractal_zip(300, true, true, null, true);
ob_start();
try {
	$fz->zip_folder($work, false);
} finally {
	ob_end_clean();
}
$fzc = $work . '.fz';
if (!is_file($fzc)) {
	fwrite(STDERR, "FAIL: no .fz\n");
	exit(1);
}
$wire = file_get_contents($fzc);
$head = substr((string) $wire, 0, 4);
$ref = 39112720;
$len = strlen((string) $wire);
$ok = ($head === 'FZHM' && abs($len - $ref) < 65536);
fwrite(STDOUT, "head=$head len=$len fzhm=" . (fractal_zip::$used_folder_per_member_best ? '1' : '0')
	. " unified=" . (fractal_zip::$used_folder_unified_stream ? '1' : '0')
	. " outer=" . (fractal_zip::$last_outer_codec ?? '?') . "\n");
exit($ok ? 0 : 1);
