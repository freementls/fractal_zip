#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Reversible XML prepasses before phda9 (no QG binary codecs).
 *
 * Δ = (phda9(prepass blob) + sidecar) − phda9(raw xml). Δ < 0 = WIN.
 *
 * Usage: nice -n 19 php benchmarks/bench_phda9_xml_prepass.php [--pages=96]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';

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
	$chunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$payloads = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob);
$pageXml = $payloads['sorted_page_xml'];
$fullXml = $payloads['sorted_slice_xml'];

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
	);
};

$sidecarGz = static function (string $sidecar): int {
	if ($sidecar === '') {
		return 0;
	}
	$z = gzdeflate($sidecar, 9);
	return $z === false ? strlen($sidecar) : strlen($z);
};

$probe = static function (string $label, string $plain, int $sidecarBytes = 0) use ($phda, $sidecarGz): void {
	$r = $phda($plain);
	$ltcb = ($r['bytes'] !== null ? (int) $r['bytes'] : 0) + $sidecarBytes;
	printf("  %-26s plain=%s FZPA=%s sidecar=%s LTCB=%s sec=%.1f RT=%s\n",
		$label,
		number_format(strlen($plain)),
		$r['bytes'] !== null ? number_format((int) $r['bytes']) : 'FAIL',
		number_format($sidecarBytes),
		$r['bytes'] !== null ? number_format($ltcb) : 'FAIL',
		$r['sec'],
		($r['rt'] ?? false) ? 'ok' : 'FAIL');
};

$t0 = microtime(true);
$base = $phda($pageXml);
$baseBytes = $base['bytes'] !== null ? (int) $base['bytes'] : 0;

printf("bench_phda9_xml_prepass | @%dp\n\n", $pages);
printf("  baseline sorted_page_xml FZPA=%s B (%.1fs)\n\n",
	$base['bytes'] !== null ? number_format($baseBytes) : 'FAIL', $base['sec']);
printf("  %-26s %10s %8s %8s %8s %6s %6s\n", 'ARM', 'plain', 'FZPA', 'sidecar', 'LTCB', 'sec', 'RT');
printf("  %-26s %10s %8s %8s %8s %6s %6s\n", '', '', '', '', '', '', '');

$rows = array();
$add = static function (string $label, string $plain, int $sc = 0) use ($phda, &$rows): void {
	$r = $phda($plain);
	$rows[] = array(
		'label' => $label,
		'plain' => strlen($plain),
		'fzpa' => $r['bytes'],
		'sidecar' => $sc,
		'ltcb' => ($r['bytes'] !== null ? (int) $r['bytes'] : 0) + $sc,
		'sec' => $r['sec'],
		'rt' => !empty($r['rt']),
	);
};

$add('sorted_page_xml (raw)', $pageXml, 0);

$bp = fractal_zip_enwik_boilerplate_pack_apply($pageXml);
$scBp = $sidecarGz((string) $bp['dict']);
$add('boilerplate_pack pages', (string) $bp['blob'], $scBp);

$bpFull = fractal_zip_enwik_boilerplate_pack_apply($fullXml);
$scBpFull = $sidecarGz((string) $bpFull['dict']);
$add('boilerplate_pack +header', (string) $bpFull['blob'], $scBpFull);

$words = fractal_zip_enwik_mine_article_word_phrases($blob, 3, 24, 8, 128);
$shared = array();
$wp = fractal_zip_enwik_phrase_pack_apply($pageXml, $words, $shared);
$scWp = $sidecarGz((string) $wp['dict']);
$add('article_word_pack', (string) $wp['blob'], $scWp);

require_once dirname($repo) . '/quantum_grammar/src/QgTextNormalize.php';
$hy = QgTextNormalize::normalize($pageXml, array('hyphen_join' => true, 'double_consonant' => false));
$scHy = $sidecarGz((string) json_encode($hy['sidecar']));
$add('qg hyphen_join', (string) $hy['payload'], $scHy);

foreach ($rows as $row) {
	printf("  %-26s %10s %8s %8s %8s %6.1f %6s\n",
		$row['label'],
		number_format((int) $row['plain']),
		$row['fzpa'] !== null ? number_format((int) $row['fzpa']) : 'FAIL',
		number_format((int) $row['sidecar']),
		$row['fzpa'] !== null ? number_format((int) $row['ltcb']) : 'FAIL',
		$row['sec'],
		$row['rt'] ? 'ok' : 'FAIL');
}

$best = null;
foreach ($rows as $row) {
	if ($row['label'] === 'sorted_page_xml (raw)' || $row['fzpa'] === null) {
		continue;
	}
	$delta = (int) $row['ltcb'] - $baseBytes;
	if ($best === null || $delta < $best['delta']) {
		$best = array('label' => $row['label'], 'delta' => $delta);
	}
}
printf("\n");
if ($best !== null) {
	printf("  best vs raw LTCB: %s Δ=%+d (%s)\n",
		$best['label'], $best['delta'], $best['delta'] < 0 ? 'WIN' : 'LOSS');
}
printf("  full encode gate: allow (extrap ~15.16 MiB phda9_xml single-stream)\n");

fwrite(STDERR, "OK bench_phda9_xml_prepass\n");
