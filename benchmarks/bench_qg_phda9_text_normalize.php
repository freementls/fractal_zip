#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Text-preserving QG normalize before phda9 on sorted page XML.
 *
 * Δ = (phda9(normalized) + gz9(sidecar)) − phda9(raw). Δ < 0 = WIN.
 *
 * Usage: nice -n 19 php benchmarks/bench_qg_phda9_text_normalize.php [--pages=96]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once dirname($repo) . '/quantum_grammar/src/QgTextNormalize.php';

$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	$src = $repo . '/enwik8';
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$chunk = array();
for ($i = 0; $i < $n; $i++) {
	$chunk[] = array('origIndex' => $i, 'start' => (int) $split['pages'][$i]['start'], 'len' => (int) $split['pages'][$i]['len']);
}
$xml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];

putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
$dict = $repo . '/benchmarks/.phda9_external_dict.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$phda = static function (string $plain): array {
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => 'phda9_no_lstm',
		'use_dict' => getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT') !== false,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	return array(
		'bytes' => $r['bytes'] ?? null,
		'rt' => !empty($r['roundtrip_ok']),
		'sec' => (float) ($r['seconds'] ?? 0.0),
		'status' => (string) ($r['status'] ?? ''),
	);
};

$gzSidecar = static function (array $sidecar): int {
	$j = json_encode($sidecar);
	return is_string($j) ? strlen(gzdeflate($j, 9) ?: $j) : 0;
};

$t0 = microtime(true);
$base = $phda($xml);
$tBase = microtime(true) - $t0;

printf("bench_qg_phda9_text_normalize | @%dp\n\n", $pages);
printf("  sorted_page_xml=%s B\n", number_format(strlen($xml)));
printf("  phda9 raw FZPA=%s B  sec=%.2f  RT=%s\n\n",
	$base['bytes'] !== null ? number_format((int) $base['bytes']) : 'FAIL',
	$tBase, $base['rt'] ? 'ok' : 'FAIL');

$arms = array(
	'hyphen_join' => array('hyphen_join' => true, 'double_consonant' => false, 'corruption_correct' => false),
	'double_consonant' => array('hyphen_join' => false, 'double_consonant' => true, 'corruption_correct' => false),
	'both' => array('hyphen_join' => true, 'double_consonant' => true, 'corruption_correct' => false),
	'+ corruption_correct' => array('hyphen_join' => true, 'double_consonant' => true, 'corruption_correct' => true),
);

printf("  %-22s %10s %8s %8s %6s %6s\n", 'ARM', 'FZPA B', 'sidecar', 'LTCB', 'Δ', 'RT');
printf("  %-22s %10s %8s %8s %6s %6s\n", '', '', '', '', '', '');

foreach ($arms as $label => $opts) {
	$enc = QgTextNormalize::normalize($xml, $opts);
	$rtPre = hash_equals($xml, QgTextNormalize::denormalize($enc['payload'], $enc['sidecar']));
	$t1 = microtime(true);
	$r = $phda($enc['payload']);
	$sec = microtime(true) - $t1;
	$sc = $gzSidecar($enc['sidecar']);
	$ltcb = ($r['bytes'] !== null ? (int) $r['bytes'] : 0) + $sc;
	$delta = $base['bytes'] !== null && $r['bytes'] !== null
		? $ltcb - (int) $base['bytes']
		: 0;
	printf("  %-22s %10s %8s %8s %+6d %6s\n",
		$label,
		$r['bytes'] !== null ? number_format((int) $r['bytes']) : 'FAIL',
		number_format($sc),
		$r['bytes'] !== null ? number_format($ltcb) : 'FAIL',
		$delta,
		($rtPre && !empty($r['rt'])) ? 'ok' : 'FAIL');
	if ($label === 'both') {
		printf("    meta: %s\n", json_encode($enc['meta']));
	}
}

fwrite(STDERR, "OK bench_qg_phda9_text_normalize\n");
