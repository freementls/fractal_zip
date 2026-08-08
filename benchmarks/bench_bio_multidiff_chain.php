#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Compare multidiff candidate discovery with/without bio colinear chain seeds.
 *
 * Usage: php benchmarks/bench_bio_multidiff_chain.php [bytes=500000]
 */

putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_DEBUG_METRICS=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$readBytes = isset($argv[1]) ? max(50_000, (int) $argv[1]) : 500_000;

$sources = array();
$enwik = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (is_file($enwik)) {
	$split = fractal_zip_enwik_split_shell_and_text(
		(string) file_get_contents($enwik, false, null, 0, min(12_000_000, $readBytes * 24)),
		max(8, (int) ($readBytes / 4000))
	);
	if ($split !== null) {
		$text = '';
		foreach ($split['pages'] as $pg) {
			$text .= (string) $pg['text'];
		}
		if (strlen($text) > 10_000) {
			$sources['enwik_text'] = substr($text, 0, $readBytes);
		}
	}
}
foreach (array('test_files90/similar01.txt', 'test_files31/multifractal.txt') as $rel) {
	$p = $repo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	if (is_file($p)) {
		$raw = file_get_contents($p);
		if (is_string($raw) && $raw !== '') {
			$sources[basename($p)] = substr($raw, 0, $readBytes);
		}
	}
}
if ($sources === array()) {
	fwrite(STDERR, "SKIP bench_bio_multidiff_chain (no inputs)\n");
	exit(0);
}

/**
 * @return array{candidates:int, chain_seeds:int, ms:float, reuse_bytes:int}
 */
function bmc_run(string $label, string $raw, bool $chainOn): array
{
	putenv('FRACTAL_ZIP_BIO_MULTIDIFF_CHAIN=' . ($chainOn ? '1' : '0'));
	$fz = new fractal_zip(20000, false, false, null, false);
	$profile = fractal_zip::substring_reciprocal_profile($raw);
	$fz->recursive_reciprocal_profile_label = (string) $profile[0];
	$fz->recursive_reciprocal_profile_ratio = (float) $profile[1];
	$fz->recursive_fractal_current_depth = 0;
	$t0 = microtime(true);
	$map = $fz->all_substrings_count($raw);
	$ms = (microtime(true) - $t0) * 1000;
	$m = is_array($fz->last_substring_multidiff_metrics) ? $fz->last_substring_multidiff_metrics : array();
	$reuse = 0;
	foreach ($map as $sub => $cnt) {
		$reuse += strlen((string) $sub) * (int) $cnt;
	}
	return array(
		'candidates' => count($map),
		'chain_seeds' => (int) ($m['multidiff_bio_chain_seeds'] ?? 0),
		'chain_merged' => (int) ($m['multidiff_bio_chain_merged'] ?? 0),
		'chain_new' => (int) ($m['multidiff_bio_chain_new'] ?? 0),
		'ms' => $ms,
		'reuse_bytes' => $reuse,
	);
}

printf("bio multidiff chain probe | %s bytes max per source\n\n", number_format($readBytes));

foreach ($sources as $name => $raw) {
	printf("=== %s (%s B) ===\n", $name, number_format(strlen($raw)));
	$off = bmc_run($name, $raw, false);
	$on = bmc_run($name, $raw, true);
	printf(
		"  chain=off  candidates=%5d  reuse_bytes=%12s  %.1f ms\n",
		$off['candidates'],
		number_format($off['reuse_bytes']),
		$off['ms']
	);
	printf(
		"  chain=on   candidates=%5d  reuse_bytes=%12s  chain_seeds=%d  merged=%d  new=%d  %.1f ms  (delta cand %+d, reuse %+d)\n\n",
		$on['candidates'],
		number_format($on['reuse_bytes']),
		$on['chain_seeds'],
		$on['chain_merged'],
		$on['chain_new'],
		$on['ms'],
		$on['candidates'] - $off['candidates'],
		$on['reuse_bytes'] - $off['reuse_bytes']
	);
}

fwrite(STDERR, "OK bench_bio_multidiff_chain\n");
