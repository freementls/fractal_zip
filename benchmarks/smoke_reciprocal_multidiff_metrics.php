#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Lightweight sanity check for reciprocal multidiff dance metrics on a large repetitive input.
 *
 * Usage (repo root):
 *   FRACTAL_ZIP_SUBSTRING_MULTIDIFF_DEBUG_METRICS=1 php benchmarks/smoke_reciprocal_multidiff_metrics.php
 */

putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_DEBUG_METRICS=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$candidates = array(
	$repoRoot . DIRECTORY_SEPARATOR . 'test_files90' . DIRECTORY_SEPARATOR . 'similar01.txt',
	$repoRoot . DIRECTORY_SEPARATOR . 'test_files31' . DIRECTORY_SEPARATOR . 'multifractal.txt',
);
$path = null;
foreach($candidates as $c) {
	if(is_file($c)) {
		$path = $c;
		break;
	}
}
if($path === null) {
	fwrite(STDOUT, "SKIP smoke_reciprocal_multidiff_metrics (no candidate file)\n");
	exit(0);
}

$raw = file_get_contents($path);
if(!is_string($raw) || $raw === '') {
	fwrite(STDERR, "FAIL read {$path}\n");
	exit(1);
}

$fz = new fractal_zip(20000, false, false, null, false);
$profile = fractal_zip::substring_reciprocal_profile($raw);
$fz->recursive_reciprocal_profile_label = (string) $profile[0];
$fz->recursive_reciprocal_profile_ratio = (float) $profile[1];
$fz->recursive_fractal_current_depth = 0;

$t0 = microtime(true);
$map = $fz->all_substrings_count($raw);
$dt = microtime(true) - $t0;

$m = is_array($fz->last_substring_multidiff_metrics) ? $fz->last_substring_multidiff_metrics : array();
$required = array('multidiff_used', 'reciprocal_seed_candidates_final', 'final_verified_candidates');
foreach($required as $rk) {
	if(!array_key_exists($rk, $m)) {
		fwrite(STDERR, "FAIL missing metric {$rk}\n");
		exit(1);
	}
}

$seedFinal = (int) ($m['reciprocal_seed_candidates_final'] ?? 0);
$verified = (int) ($m['final_verified_candidates'] ?? 0);
if($verified < 1) {
	fwrite(STDERR, "FAIL expected verified candidates on {$path}\n");
	exit(1);
}

fwrite(STDOUT, 'OK smoke_reciprocal_multidiff_metrics file=' . basename($path)
	. ' profile=' . (string) $profile[0]
	. ' seeds=' . (string) $seedFinal
	. ' verified=' . (string) $verified
	. ' time=' . sprintf('%.3f', $dt) . "s\n");
exit(0);
