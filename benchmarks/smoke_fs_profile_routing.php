#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: FS preset zip_folder routing (stat gate, large 7z passthrough, small fractal keep).
 *
 * php benchmarks/smoke_fs_profile_routing.php
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_fs_speed_env.php';

bench_fs_speed_apply_env_defaults();
fractal_zip::set_runtime_profile_preset('fs');

/** @return array{pass: bool, detail: string} */
function smoke_fs_expect_7z(string $repoRoot, string $label, bool $expectPassthrough): array {
	$dir = $repoRoot . DIRECTORY_SEPARATOR . $label;
	if(!is_dir($dir)) {
		return array('pass' => false, 'detail' => "{$label}: missing dir");
	}
	$stat = fractal_zip::fs_profile_zip_folder_stat_from_dir($dir);
	if($stat === null) {
		return array('pass' => false, 'detail' => "{$label}: stat failed");
	}
	list($mc, $sum, $paths,) = $stat;
	$mode = fractal_zip::fs_profile_early_7z_gate_mode_from_stat($stat);
	if(!$expectPassthrough) {
		if($mode !== null) {
			return array('pass' => false, 'detail' => "{$label}: expected no early gate, got mode={$mode}");
		}
	} else {
		if($mode === null) {
			return array('pass' => false, 'detail' => "{$label}: expected early gate, got mode=null");
		}
		$gate = fractal_zip::fs_profile_zip_folder_early_gate_from_dir($dir);
		if($gate === null) {
			return array('pass' => false, 'detail' => "{$label}: early gate returned null");
		}
		list(, $gateRaw,, , $gatePaths) = $gate;
		if(!fractal_zip::fs_profile_should_use_7z_passthrough(array(), $gateRaw, $mc, null, $gatePaths)) {
			return array('pass' => false, 'detail' => "{$label}: should_use_7z_passthrough false");
		}
	}
	$fzc = $dir . '.fz';
	if(is_file($fzc)) {
		@unlink($fzc);
	}
	$z = new fractal_zip();
	$z->zip_folder($dir);
	$codec = (string) (fractal_zip::$last_written_container_codec ?? '');
	if($expectPassthrough) {
		if($codec !== '7z' || !is_file($fzc)) {
			return array('pass' => false, 'detail' => "{$label}: expected 7z passthrough, got codec={$codec}");
		}
	} elseif($codec === '7z') {
		return array('pass' => false, 'detail' => "{$label}: expected fractal path, got 7z");
	}
	if(is_file($fzc)) {
		@unlink($fzc);
	}

	return array('pass' => true, 'detail' => "{$label}: " . ($expectPassthrough ? '7z' : 'fractal') . " mode={$mode}");
}

$cases = array(
	array('test_files24', false),
	array('test_files150', false),
	array('test_files63', true),
	array('test_files60', true),
	array('test_files90', true),
);

$failed = 0;
foreach($cases as $case) {
	list($label, $expect7z) = $case;
	$r = smoke_fs_expect_7z($repoRoot, $label, $expect7z);
	if(!$r['pass']) {
		fwrite(STDERR, 'FAIL ' . $r['detail'] . "\n");
		$failed++;
	} else {
		fwrite(STDOUT, 'OK ' . $r['detail'] . "\n");
	}
}

if($failed > 0) {
	fwrite(STDERR, "FAIL {$failed} case(s)\n");
	exit(1);
}

fwrite(STDOUT, "OK smoke_fs_profile_routing (" . count($cases) . " cases)\n");
exit(0);
