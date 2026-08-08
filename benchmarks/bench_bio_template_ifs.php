#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Template/macro IFS slice harness on enwik text (8/96/384p).
 *
 * LTCB: payload + sidecar JSON. Δ = LTCBtotal − baseline; Δ < 0 = PASS.
 *
 * Usage: nice -n 19 php benchmarks/bench_bio_template_ifs.php [pages=8,96,384]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_bio_template_ifs.php';

$slicesArg = $argv[1] ?? '8,96,384';
$slices = array_values(array_filter(array_map('intval', explode(',', $slicesArg)), static fn (int $v): bool => $v > 0));

$enwikPath = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwikPath)) {
	fwrite(STDERR, "enwik8 not found\n");
	exit(1);
}

$gz = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

printf("bench_bio_template_ifs | slices=%s\n\n", implode(',', array_map('strval', $slices)));

foreach ($slices as $pages) {
	$split = fractal_zip_enwik_split_shell_and_text(
		(string) file_get_contents($enwikPath, false, null, 0, min(12_000_000, $pages * 40_000)),
		$pages
	);
	if ($split === null) {
		fwrite(STDERR, "split failed @{$pages}p\n");
		continue;
	}
	$text = '';
	foreach ($split['pages'] as $pg) {
		$text .= (string) $pg['text'];
	}
	$basePayload = $gz($text);
	$baseLtc = $basePayload;

	$built = fractal_zip_bio_template_ifs_build($text);
	$restored = fractal_zip_bio_template_ifs_restore($built['payload'], $built['sidecar']);
	if ($restored !== $text) {
		fwrite(STDERR, "RT FAIL @{$pages}p len " . strlen($restored) . " vs " . strlen($text) . "\n");
		exit(1);
	}
	$payload = $gz($built['payload']);
	$sidecar = strlen($built['sidecar']);
	$ltc = $payload + $sidecar;
	$delta = $ltc - $baseLtc;
	$pass = $delta < 0 ? 'PASS' : 'FAIL';

	printf(
		"  @%3dp  raw=%8s  base=%8s  ifs_payload=%8s  sidecar=%6s  LTCB=%8s  Δ=%+8d  GATE %s  macros=%d hits=%d\n",
		$pages,
		number_format(strlen($text)),
		number_format($baseLtc),
		number_format($payload),
		number_format($sidecar),
		number_format($ltc),
		$delta,
		$pass,
		(int) ($built['stats']['templates'] ?? 0),
		(int) ($built['stats']['macro_hits'] ?? 0)
	);
}

fwrite(STDERR, "OK bench_bio_template_ifs\n");
