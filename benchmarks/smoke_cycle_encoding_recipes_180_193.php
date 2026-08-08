#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Verify test_files180-193 against deterministic v2 epicycle recipes.
 *
 * This smoke proves fixture integrity and appearance gates (lag/period/entropy).
 * It does not expose recipes to compression.
 *
 * Usage from repo root:
 *   php benchmarks/smoke_cycle_encoding_recipes_180_193.php
 */

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';

$fail = static function (string $msg): void {
	fwrite(STDERR, "FAIL smoke_cycle_encoding_recipes_180_193: {$msg}\n");
	exit(1);
};

$specs = cycle_encoding_expanded_specs();
$checks = 0;
$bytesTotal = 0;

$bytes180 = (string)$specs[180]['bytes'];
$bytes183 = (string)$specs[183]['bytes'];
if($bytes180 === $bytes183) {
	$fail('183 2-walker output must differ from 180 1-circle');
}
$period180 = cycle_encoding_minimal_period($bytes180);
$period183 = cycle_encoding_minimal_period($bytes183);
if($period183 <= $period180 && $period183 < (int)$specs[183]['length'] * 9 / 10) {
	$fail('183 period ' . (string)$period183 . ' should be near full length vs 180 period ' . (string)$period180);
}
fwrite(STDOUT, 'OK appearance spot-check 180 period=' . (string)$period180
	. ' 183 period=' . (string)$period183 . "\n");

foreach(array(184, 187, 182) as $case) {
	$b = (string)$specs[$case]['bytes'];
	$lag38 = cycle_encoding_lag_match_rate($b, 38);
	$lag76 = cycle_encoding_lag_match_rate($b, 76);
	if($lag38 > 0.15) {
		$fail('case ' . (string)$case . ' lag-38 match ' . round(100 * $lag38, 1) . '% exceeds 15%');
	}
	if($lag76 > 0.15) {
		$fail('case ' . (string)$case . ' lag-76 match ' . round(100 * $lag76, 1) . '% exceeds 15%');
	}
	$period = cycle_encoding_minimal_period($b);
	$len = (int)$specs[$case]['length'];
	if($case !== 182 && $period < (int)floor($len * 0.9)) {
		$fail('case ' . (string)$case . ' minimal period ' . (string)$period . ' < 90% of length');
	}
	fwrite(STDOUT, 'OK lag gate case=' . (string)$case
		. ' lag38=' . round(100 * $lag38, 1) . '% lag76=' . round(100 * $lag76, 1)
		. '% period=' . (string)$period . "\n");
}

$b181 = (string)$specs[181]['bytes'];
$lag76_181 = cycle_encoding_lag_match_rate($b181, 76);
if($lag76_181 > 0.20) {
	$fail('181 lag-76 match ' . round(100 * $lag76_181, 1) . '% exceeds 20%');
}
fwrite(STDOUT, 'OK lag gate case=181 lag76=' . round(100 * $lag76_181, 1) . "%\n");

foreach(array(183, 186, 189) as $case) {
	$b = (string)$specs[$case]['bytes'];
	$entropy = cycle_encoding_shannon_entropy($b);
	if($entropy < 4.5) {
		$fail('case ' . (string)$case . ' entropy ' . round($entropy, 2) . ' below 4.5 bits');
	}
}

foreach($specs as $case => $spec) {
	$dir = $root . DIRECTORY_SEPARATOR . (string)$spec['dir'];
	$rel = (string)$spec['file'];
	$path = $dir . DIRECTORY_SEPARATOR . $rel;
	if(!is_dir($dir)) {
		$fail((string)$spec['dir'] . ' missing; run php benchmarks/build_test_files180_193.php');
	}
	if(!is_file($path)) {
		$fail((string)$spec['dir'] . '/' . $rel . ' missing');
	}
	$disk = file_get_contents($path);
	if($disk === false) {
		$fail((string)$spec['dir'] . '/' . $rel . ' unreadable');
	}
	$expected = (string)$spec['bytes'];
	if($disk !== $expected) {
		$fail((string)$spec['dir'] . '/' . $rel . ' recipe expansion mismatch');
	}
	if(strlen($disk) !== (int)$spec['expected_size']) {
		$fail((string)$spec['dir'] . '/' . $rel . ' expected size mismatch');
	}
	if(hash('xxh128', $disk, false) !== (string)$spec['expected_xxh128']) {
		$fail((string)$spec['dir'] . '/' . $rel . ' expected xxh128 mismatch');
	}
	$parsed = cycle_encoding_parse_e_recipe((string)$spec['representation']);
	if($parsed['length'] !== (int)$spec['length']) {
		$fail((string)$spec['dir'] . ' recipe length parse mismatch');
	}
	$checks++;
	$bytesTotal += strlen($disk);
	fwrite(STDOUT, 'OK ' . (string)$spec['dir'] . '/' . $rel . ' bytes=' . (string)strlen($disk) . "\n");
}

fwrite(STDOUT, 'OK smoke_cycle_encoding_recipes_180_193 checks=' . (string)$checks
	. ' bytes=' . (string)$bytesTotal . "\n");
