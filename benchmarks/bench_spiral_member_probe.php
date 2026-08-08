#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Spiral member-fold probe: split LTCB vs unified blob vs raw gz9.
 *
 * Tests whether folding sidecar+payload into one member stream helps outer gzip
 * (stat_pred-style packaging, no wire integration).
 *
 * Usage: nice -n 19 php benchmarks/bench_spiral_member_probe.php [pages=96]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$pages = isset($argv[1]) ? max(8, (int) $argv[1]) : 96;
$enwik = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwik)) {
	fwrite(STDERR, "enwik8 missing\n");
	exit(1);
}

putenv('FRACTAL_ZIP_SPIRAL_INNER=1');
putenv('FRACTAL_ZIP_SPIRAL_REPARSE=1');
putenv('FRACTAL_ZIP_SPIRAL_CODEC=5');

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

require_once '/srv/http/spiral/src/SpiralMemberFold.php';
require_once '/srv/http/spiral/src/SpiralSidecar.php';

$pre = fractal_zip_enwik_spiral_inner_preprocess($text);
if ($pre === null) {
	fwrite(STDERR, "spiral inner unavailable\n");
	exit(1);
}

$fold = SpiralMemberFold::pack(
	(string) $pre['payload'],
	$pre['sidecar'],
	(int) ($pre['meta']['raw_len'] ?? strlen($text))
);

$restSplit = fractal_zip_enwik_spiral_inner_restore($pre['payload'], $pre['sidecar']);
$restFold = SpiralMemberFold::restoreText($fold['blob']);
if ($restSplit !== $text || $restFold !== $text) {
	fwrite(STDERR, "RT FAIL split=" . ($restSplit === $text ? 'ok' : 'FAIL')
		. ' fold=' . ($restFold === $text ? 'ok' : 'FAIL') . "\n");
	exit(1);
}

$base = $gz($text);
$splitLtc = $gz((string) $pre['payload']) + SpiralSidecar::sidecarBytes($pre['sidecar']);
$foldGz = $gz($fold['blob']);
$foldRaw = strlen($fold['blob']);

$deltaSplit = $splitLtc - $base;
$deltaFold = $foldGz - $base;
$pass = $deltaFold < 0 ? 'PASS' : 'FAIL';

printf("bench_spiral_member_probe | @%dp | %s bytes | codec v%d\n",
	$pages, number_format(strlen($text)), (int) ($pre['meta']['codec_version'] ?? 5));
printf("  gz9 baseline=%s\n", number_format($base));
printf("  split LTCB (gz9 payload + sidecar)=%s  Δ=%+d\n", number_format($splitLtc), $deltaSplit);
printf("  fold raw=%s  gz9(fold)=%s  Δ=%+d  GATE %s\n",
	number_format($foldRaw), number_format($foldGz), $deltaFold, $pass);
printf("  fold saves vs split LTCB: %+d B (gzip cross-stream)\n", $splitLtc - $foldGz);

$ppTool = getenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ');
if ($ppTool === false || trim((string) $ppTool) === '') {
	putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_cmix');
}
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_paq.php';
$ppRaw = fractal_zip_parallel_paq_try_wrap_member($text);
$ppFold = fractal_zip_parallel_paq_try_wrap_member($fold['blob']);
if (is_string($ppRaw) && $ppRaw !== '') {
	printf("  parallel_cmix(raw)=%s  Δ=%+d\n", number_format(strlen($ppRaw)), strlen($ppRaw) - $base);
}
if (is_string($ppFold) && $ppFold !== '') {
	printf("  parallel_cmix(fold)=%s  Δ=%+d\n", number_format(strlen($ppFold)), strlen($ppFold) - $base);
}
if (!is_string($ppRaw) && !is_string($ppFold)) {
	printf("  parallel_cmix: unavailable (binary not built)\n");
}

fwrite(STDERR, "OK bench_spiral_member_probe\n");
