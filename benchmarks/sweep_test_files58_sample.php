#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Env sweep on test_files58_sample (--large). Prints TSV: id, fzc_B, gzip9, z7, zip_s, outer.
 * Uses --no-extract --no-best-ext for faster iteration (bytes same as full bench for .fz).
 *
 *   FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G php benchmarks/sweep_test_files58_sample.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$bench = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php';
$corpus = 'test_files58_sample';

$matrix = array(
	array('id' => 'A_default', 'set' => array()),
	array('id' => 'B_brotli_huge_full', 'set' => array('FRACTAL_ZIP_BROTLI_HUGE_MODE' => 'full')),
	array('id' => 'C_seg240', 'set' => array('FRACTAL_ZIP_SEGMENT_LENGTH' => '240')),
	array('id' => 'D_seg360', 'set' => array('FRACTAL_ZIP_SEGMENT_LENGTH' => '360')),
	array('id' => 'E_full_seg240', 'set' => array(
		'FRACTAL_ZIP_BROTLI_HUGE_MODE' => 'full',
		'FRACTAL_ZIP_SEGMENT_LENGTH' => '240',
	)),
	array('id' => 'F_auto_multipass_off', 'set' => array('FRACTAL_ZIP_AUTO_MULTIPASS' => '0')),
	array('id' => 'G_brotli_full_auto_off', 'set' => array(
		'FRACTAL_ZIP_BROTLI_HUGE_MODE' => 'full',
		'FRACTAL_ZIP_AUTO_MULTIPASS' => '0',
	)),
);

$keys = array(
	'FRACTAL_ZIP_BROTLI_HUGE_MODE',
	'FRACTAL_ZIP_SEGMENT_LENGTH',
	'FRACTAL_ZIP_AUTO_MULTIPASS',
);

function sweep58_clear_env(): void {
	global $keys;
	foreach ($keys as $k) {
		putenv($k);
	}
}

function sweep58_run(string $repo, string $bench, string $corpus, array $row): ?array {
	sweep58_clear_env();
	foreach ($row['set'] as $k => $v) {
		putenv($k . '=' . $v);
	}
	$jsonPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz58_' . bin2hex(random_bytes(8)) . '.json';
	$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($bench)
		. ' --only=' . escapeshellarg($corpus)
		. ' --large --no-verify --no-case-timeout --no-baseline-cache --no-best-ext --no-extract'
		. ' --json --out-json=' . escapeshellarg($jsonPath);
	$null = '/dev/null';
	$descSpec = array(
		0 => array('file', $null, 'r'),
		1 => array('file', $null, 'w'),
		2 => array('pipe', 'w'),
	);
	$proc = proc_open($cmd, $descSpec, $pipes, $repo, null);
	if (!is_resource($proc)) {
		sweep58_clear_env();
		return null;
	}
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	$code = proc_close($proc);
	sweep58_clear_env();
	if ($code !== 0) {
		fwrite(STDERR, "FAIL {$row['id']} exit={$code} " . trim((string) $stderr) . "\n");
		return null;
	}
	$rawJ = @file_get_contents($jsonPath);
	@unlink($jsonPath);
	if (!is_string($rawJ) || $rawJ === '') {
		return null;
	}
	$j = bench_json_decode_assoc_try($rawJ, 'sweep_test_files58_sample');
	if ($j === null || !isset($j['cases'][0]) || !is_array($j['cases'][0])) {
		return null;
	}
	$c = $j['cases'][0];
	return array(
		'fzc' => (int) ($c['fzc_bytes'] ?? 0),
		'gz' => (int) ($c['gzip9_bundle_bytes'] ?? 0),
		'z7' => (int) ($c['seven_zip_folder_bytes'] ?? 0),
		'zip_s' => (float) ($c['zip_seconds'] ?? 0.0),
		'outer' => isset($c['outer_codec']) ? (string) $c['outer_codec'] : '',
		'seg' => (int) ($c['segment_length'] ?? 0),
	);
}

fwrite(STDOUT, "# sweep_test_files58_sample " . date('c') . "\n");
fwrite(STDOUT, "# id\tfzc_B\tgzip9\t7z\tmin_gz_z7\tvs_min_B\tzip_s\touter\tseg\n");
$best = PHP_INT_MAX;
$bestId = '';
foreach ($matrix as $row) {
	$r = sweep58_run($repo, $bench, $corpus, $row);
	if ($r === null) {
		continue;
	}
	$minBase = min($r['gz'], $r['z7']);
	$delta = $minBase - $r['fzc'];
	fwrite(STDOUT, sprintf(
		"%s\t%d\t%d\t%d\t%d\t%d\t%.2f\t%s\t%d\n",
		$row['id'],
		$r['fzc'],
		$r['gz'],
		$r['z7'],
		$minBase,
		$delta,
		$r['zip_s'],
		$r['outer'],
		$r['seg']
	));
	if ($r['fzc'] > 0 && $r['fzc'] < $best) {
		$best = $r['fzc'];
		$bestId = $row['id'];
	}
	fflush(STDOUT);
}
if ($bestId !== '') {
	fwrite(STDOUT, "# best fzc: {$bestId} => {$best} B\n");
}
