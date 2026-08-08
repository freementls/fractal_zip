#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: FZHM per-member best round-trip on a single Squash Silesia member dir.
 *
 * Usage: php benchmarks/smoke_fzhm_per_member_best.php [corpus_dir]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$corpus = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'test_files108');
if (!is_dir($corpus)) {
	fwrite(STDERR, "SKIP smoke_fzhm_per_member_best: missing corpus {$corpus}\n");
	exit(0);
}

putenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST=1');
putenv('FRACTAL_ZIP_FOLDER_BASELINE_TIE=1');

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_fzhm_smoke_' . getmypid();
@mkdir($work, 0700, true);
$payload = str_repeat("The quick brown fox jumps over the lazy dog.\n", 4000);
file_put_contents($work . DIRECTORY_SEPARATOR . 'alpha', $payload);
file_put_contents($work . DIRECTORY_SEPARATOR . 'beta', strrev($payload));
$rawA = $payload;
$rawB = strrev($payload);

$two = $work . '_two';
@mkdir($two, 0700, true);
copy($work . DIRECTORY_SEPARATOR . 'alpha', $two . DIRECTORY_SEPARATOR . 'alpha');
copy($work . DIRECTORY_SEPARATOR . 'beta', $two . DIRECTORY_SEPARATOR . 'beta');

$fz = new fractal_zip(256, false, true, null, false);
ob_start();
try {
	$fz->zip_folder($two, false);
} finally {
	ob_end_clean();
}
$fzc = $two . '.fz';
if (!is_file($fzc)) {
	fwrite(STDERR, "FAIL: no .fz at {$fzc}\n");
	exit(1);
}
$wire = file_get_contents($fzc);
if ($wire === false || $wire === '') {
	fwrite(STDERR, "FAIL: empty .fz\n");
	exit(1);
}

$scratch = $two . '_ex';
@mkdir($scratch, 0700, true);
$extractFzc = $scratch . DIRECTORY_SEPARATOR . 'bundle.fz';
copy($fzc, $extractFzc);

$fx = new fractal_zip(256, false, true, null, false);
ob_start();
try {
	$fx->open_container($extractFzc, false);
} finally {
	ob_end_clean();
}
$gotA = @file_get_contents($scratch . DIRECTORY_SEPARATOR . 'alpha');
$gotB = @file_get_contents($scratch . DIRECTORY_SEPARATOR . 'beta');
if ($gotA !== $rawA || $gotB !== $rawB) {
	fwrite(STDERR, "FAIL: round-trip mismatch\n");
	exit(1);
}

fwrite(STDOUT, "OK smoke_fzhm_per_member_best wire=" . strlen($wire) . " B\n");
exit(0);
