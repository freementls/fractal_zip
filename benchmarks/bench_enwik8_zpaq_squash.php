#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Squash-style zpaq on raw test_files109/enwik8 (zpaq_raw baseline).
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

bench_world_record_apply_env_defaults();

if (fractal_zip::zpaq_executable() === null) {
	fwrite(STDERR, "zpaq not found\n");
	exit(1);
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing enwik8\n");
	exit(1);
}

$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpraw_' . bin2hex(random_bytes(8));
mkdir($box, 0755, true);
copy($src, $box . DIRECTORY_SEPARATOR . 'enwik8');

$zpaqExe = (string) fractal_zip::zpaq_executable();
$arcPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpbench_' . bin2hex(random_bytes(8)) . '.zpaq';
@unlink($arcPath);
$t0 = microtime(true);
$cwd = getcwd();
$bestBytes = null;
$sumRaw = (int) filesize($src);
$candidates = fractal_zip::zpaq_outer_methods_argv_fragments_cached();
if ($candidates === array()) {
	$fullSweepMaxRaw = fractal_zip::zpaq_native_full_sweep_max_raw_bytes_cached();
	if ($fullSweepMaxRaw > 0 && $sumRaw <= $fullSweepMaxRaw) {
		$candidates = array(' -method 6', ' -method 5', ' -method 4', ' -method 3');
	} else {
		$candidates = array(' -method 5', ' -method 4', ' -method 3');
	}
}
if (@chdir($box)) {
	$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
	$qArc = fractal_zip::shell_quote_arg_cached($arcPath);
	$qTarg = fractal_zip::shell_quote_arg_cached('enwik8');
	foreach ($candidates as $methArg) {
		if (is_file($arcPath)) {
			@unlink($arcPath);
		}
		$cmd = $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add ' . $qArc . ' ' . $qTarg . $methArg . ' -force 2>/dev/null';
		exec($cmd, $xo, $ret);
		if ($ret === 0 && is_file($arcPath)) {
			$n = filesize($arcPath);
			if ($n !== false && $n > 0 && ($bestBytes === null || $n < $bestBytes)) {
				$bestBytes = (int) $n;
			}
		}
	}
}
if ($cwd !== false) {
	chdir($cwd);
}
$sec = microtime(true) - $t0;
@unlink($arcPath);
@unlink($box . DIRECTORY_SEPARATOR . 'enwik8');
@rmdir($box);

$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_zpaq_squash.json';
file_put_contents($path, json_encode(array(
	'generated' => date('c'),
	'bytes' => $bestBytes,
	'seconds' => round($sec, 6),
), JSON_PRETTY_PRINT));

if ($bestBytes === null) {
	fwrite(STDERR, "zpaq squash failed\n");
	exit(1);
}
echo "zpaq_raw: " . number_format($bestBytes) . " B in " . number_format($sec, 1) . "s\n";
