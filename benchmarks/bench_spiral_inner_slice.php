#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Spiral inner preprocess slice gate @96p (LTCB vs gz9 baseline).
 *
 * Usage: nice -n 19 php benchmarks/bench_spiral_inner_slice.php [pages=96]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$pages = isset($argv[1]) ? max(8, (int) $argv[1]) : 96;
$enwik = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwik)) {
	fwrite(STDERR, "enwik8 missing\n");
	exit(1);
}

putenv('FRACTAL_ZIP_SPIRAL_INNER=1');
putenv('FRACTAL_ZIP_SPIRAL_REPARSE=1');

$split = fractal_zip_enwik_split_shell_and_text(
	(string) file_get_contents($enwik, false, null, 0, min(12_000_000, $pages * 40_000)),
	$pages
);
if ($split === null) {
	fwrite(STDERR, "split failed\n");
	exit(1);
}
$text = '';
foreach ($split['pages'] as $pg) {
	$text .= (string) $pg['text'];
}

$gz = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

require_once '/srv/http/spiral/src/SpiralSidecar.php';

$base = $gz($text);
$pre = fractal_zip_enwik_spiral_inner_preprocess($text);
if ($pre === null) {
	fwrite(STDERR, "spiral inner unavailable\n");
	exit(1);
}

$restored = fractal_zip_enwik_spiral_inner_restore($pre['payload'], $pre['sidecar']);
if ($restored !== $text) {
	fwrite(STDERR, "RT FAIL len " . strlen($restored) . " vs " . strlen($text) . "\n");
	exit(1);
}

$payload = $gz($pre['payload']);
$sidecar = SpiralSidecar::sidecarBytes($pre['sidecar']);
$ltc = $payload + $sidecar;
$delta = $ltc - $base;
$pass = $delta < 0 ? 'PASS' : 'FAIL';

$mode = getenv('FRACTAL_ZIP_SPIRAL_MEMBER_FOLD') === '1' ? 'fold' : 'split';
printf("bench_spiral_inner_slice | @%dp | %s bytes | codec v%d | %s\n",
	$pages, number_format(strlen($text)), (int) ($pre['meta']['codec_version'] ?? 5), $mode);
printf("  gz9 baseline=%s  spiral_payload=%s  sidecar=%s  LTCB=%s  Δ=%+d  GATE %s\n",
	number_format($base), number_format($payload), number_format($sidecar), number_format($ltc), $delta, $pass);
printf("  spiral_bpc=%.3f  bigram_bpc=%.3f  hit_rate=%.1f%%\n",
	(float) ($pre['meta']['spiral_bpc'] ?? 0),
	(float) ($pre['meta']['bigram_bpc'] ?? 0),
	(float) (($pre['sidecar']['hit_rate'] ?? 0) * 100.0)
);

fwrite(STDERR, "OK bench_spiral_inner_slice\n");
