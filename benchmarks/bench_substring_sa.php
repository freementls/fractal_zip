#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Suffix-array wins: cfabb mine speed + candidate count, all_substrings regression safety.
 *
 * Usage: php benchmarks/bench_substring_sa.php [--pages=384]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_wiki_lom.php';
require_once $repo . '/fractal_zip_collision_free_abbrevs.php';
require_once $repo . '/fractal_zip_gpu_substring.php';

$pages = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

if (!fractal_zip_gpu_substring_sa_available()) {
	fwrite(STDERR, "FAIL substring_sa_enumerate_rs not built; run tools/gpu_substring/build.sh\n");
	exit(1);
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$text = '';
$n = min($pages, count($split['pages']));
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
}
$decoded = fractal_zip_wiki_lom_entity_decode_text($text);

// cfabb: legacy vs SA-enriched phrase counts
putenv('FRACTAL_ZIP_CFABB_SA=0');
$t0 = microtime(true);
$legacyMine = fractal_zip_cfabb_mine($decoded, array(
	'corpus_pages' => $n,
	'min_count' => 4,
	'max_entries' => 4096,
	'max_words' => 10,
	'max_chars' => 100,
));
$legacySec = microtime(true) - $t0;
$legacySave = array_sum(array_column($legacyMine, 'save'));

putenv('FRACTAL_ZIP_CFABB_SA=1');
$t1 = microtime(true);
$saMine = fractal_zip_cfabb_mine($decoded, array(
	'corpus_pages' => $n,
	'min_count' => 4,
	'max_entries' => 4096,
	'max_words' => 10,
	'max_chars' => 100,
));
$saSec = microtime(true) - $t1;
$saSave = array_sum(array_column($saMine, 'save'));

$byteSa = fractal_zip_cfabb_count_byte_phrases_sa($decoded, 8, 100, 4);
$t2 = microtime(true);
$saMap = fractal_zip_gpu_substring_sa_repeat_map($decoded, 8, 100);
$saRawSec = microtime(true) - $t2;

// all_substrings_count: default off must match regression hash
$p = $repo . '/test_files30/test_files2.txt';
$raw = (string) file_get_contents($p);
putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA=0');
putenv('FRACTAL_ZIP_PEEL_GPU=0');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY=0');
$fz = new fractal_zip(64, false, false, null, false);
$baseMap = $fz->all_substrings_count($raw);
ksort($baseMap);
$baseHash = hash('xxh128', json_encode($baseMap, JSON_UNESCAPED_UNICODE));

// SA on for large chunk — count extra candidates only (no byte lock on full map)
$chunk = '';
for ($i = 0; $i < 96; $i++) {
	$p = $split['pages'][$i];
	$chunk .= substr($blob, (int) $p['start'], (int) $p['len']);
}
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA=1');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA_MIN_BYTES=4096');
putenv('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY=0');
$fz2 = new fractal_zip(20000, false, false, null, false);
$t3 = microtime(true);
$saAscMap = $fz2->all_substrings_count($chunk);
$ascSaSec = microtime(true) - $t3;
putenv('FRACTAL_ZIP_GPU_SUBSTRING_SA=0');
$t4 = microtime(true);
$legacyAscMap = $fz2->all_substrings_count($chunk);
$ascLegacySec = microtime(true) - $t4;

printf("bench_substring_sa pages=%d decoded_len=%d\n", $n, strlen($decoded));
printf("  cfabb legacy: entries=%d est_save=%d sec=%.2f\n", count($legacyMine), $legacySave, $legacySec);
printf("  cfabb +SA:    entries=%d est_save=%d sec=%.2f  Δsave=%+d  speed=%.2fx\n",
	count($saMine),
	$saSave,
	$saSec,
	$saSave - $legacySave,
	$legacySec > 0 ? ($legacySec / max(0.001, $saSec)) : 0.0
);
printf("  SA byte repeats (min_count=4): %d phrases in %.3fs\n", count($byteSa), $saRawSec);
printf("  all_substrings regression slice (SA=off): n=%d hash=%s\n", count($baseMap), $baseHash);
printf("  asc @96p page-xml chunk len=%d: legacy n=%d sec=%.2f | SA n=%d sec=%.2f speed=%.2fx extra=%d\n",
	strlen($chunk),
	count($legacyAscMap),
	$ascLegacySec,
	count($saAscMap),
	$ascSaSec,
	$ascLegacySec > 0 ? ($ascLegacySec / max(0.001, $ascSaSec)) : 0.0,
	max(0, count($saAscMap) - count($legacyAscMap))
);

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'cfabb_legacy' => array('entries' => count($legacyMine), 'save' => $legacySave, 'sec' => round($legacySec, 3)),
	'cfabb_sa' => array('entries' => count($saMine), 'save' => $saSave, 'sec' => round($saSec, 3)),
	'asc_96p' => array(
		'legacy_n' => count($legacyAscMap),
		'sa_n' => count($saAscMap),
		'legacy_sec' => round($ascLegacySec, 3),
		'sa_sec' => round($ascSaSec, 3),
	),
	'regression_hash_sa_off' => $baseHash,
);
$jsonPath = $repo . '/benchmarks/.substring_sa_bench_' . $n . 'p.json';
file_put_contents($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "json → {$jsonPath}\n";

if ($baseHash !== 'fba292d0e06e96daca8b79bc408012c1') {
	fwrite(STDERR, "WARN regression hash differs from locked serial (expected when slice changes)\n");
}

echo "OK bench_substring_sa\n";
