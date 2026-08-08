#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * parallel_cmix on SPRL v5 residuals vs raw text (member codec on residuals).
 *
 * Usage: nice -n 19 php benchmarks/bench_spiral_paq_residual.php [--pages=96]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once '/srv/http/spiral/src/ResidualCodec.php';
require_once '/srv/http/quantum_grammar/src/ParseSyntax.php';

$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
	}
}

$exe = fractal_zip_paq_discover_executable('parallel_cmix');
if ($exe === null) {
	fwrite(STDERR, "build tools/parallel_paq first\n");
	exit(1);
}

$split = fractal_zip_enwik_split_shell_and_text(
	(string) file_get_contents($repo . '/enwik8', false, null, 0, min(12_000_000, $pages * 40_000)),
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

putenv('FRACTAL_ZIP_SPIRAL_CODEC=5');
$parseFn = static fn (string $t): array => ParseSyntax::parse($t);
$spirl = ResidualCodec::encodeText($text, $parseFn, 'spiral');
$rawLen = strlen($text);
$spLen = strlen($spirl);

$tmp = sys_get_temp_dir();
$rawPath = $tmp . '/fzpp_raw_' . getmypid() . '.bin';
$spPath = $tmp . '/fzpp_sp_' . getmypid() . '.bin';
file_put_contents($rawPath, $text);
file_put_contents($spPath, $spirl);
$jobs = fractal_zip_paq_parallel_jobs();

$run = static function (string $exe, string $in) use ($jobs, $tmp): array {
	$out = $tmp . '/fzpp_o_' . bin2hex(random_bytes(4)) . '.fzpp';
	$dec = $tmp . '/fzpp_d_' . bin2hex(random_bytes(4)) . '.bin';
	$argv = array($exe, 'compress', '-j' . (string) $jobs, '-o', $out, $in);
	exec(implode(' ', array_map('escapeshellarg', $argv)) . ' 2>&1', $lines, $ret);
	if ($ret !== 0) {
		return array('ok' => false, 'err' => implode("\n", $lines));
	}
	$comp = is_file($out) ? (int) filesize($out) : 0;
	$dargv = array($exe, 'decompress', '-j' . (string) $jobs, '-o', $dec, $out);
	exec(implode(' ', array_map('escapeshellarg', $dargv)) . ' 2>&1', $dl, $dret);
	$ok = ($dret === 0 && is_file($dec) && file_get_contents($dec) === file_get_contents($in));
	@unlink($out);
	@unlink($dec);
	return array('ok' => $ok, 'comp' => $comp);
};

$gzRaw = strlen((string) gzdeflate($text, 9));
$gzSp = strlen((string) gzdeflate($spirl, 9));
$cmixRaw = $run($exe, $rawPath);
$cmixSp = $run($exe, $spPath);

printf("bench_spiral_paq_residual | @%dp | raw=%s  SPRL=%s\n", $pages, number_format($rawLen), number_format($spLen));
printf("  gz9(raw)=%s  gz9(SPRL)=%s\n", number_format($gzRaw), number_format($gzSp));

foreach (array('cmix(raw)' => $cmixRaw, 'cmix(SPRL)' => $cmixSp) as $label => $r) {
	if (empty($r['ok'])) {
		printf("  %-14s FAIL %s\n", $label, $r['err'] ?? '');
		continue;
	}
	printf("  %-14s comp=%s\n", $label, number_format((int) $r['comp']));
}

if (!empty($cmixSp['ok'])) {
	$vsGz = (int) $cmixSp['comp'] - $gzRaw;
	$vsSplit = (int) $cmixSp['comp'] + $spLen - $gzRaw;
	printf("  cmix(SPRL) Δ vs gz9(raw)=%+d  (split LTCB proxy=%+d)  GATE %s\n",
		$vsGz, $vsSplit, $vsGz < 0 ? 'PASS' : 'FAIL');
}

@unlink($rawPath);
@unlink($spPath);
fwrite(STDERR, "OK bench_spiral_paq_residual\n");
