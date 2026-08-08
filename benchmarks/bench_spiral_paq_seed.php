#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Probe spiral bigram / order-2 seed inside parallel_cmix.
 *
 * Usage: nice -n 19 php benchmarks/bench_spiral_paq_seed.php [--pages=96] [--mode=prefix|oracle]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once '/srv/http/spiral/src/SpiralPaqSeed.php';

$pages = 96;
$mode = 'prefix';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--mode=')) {
		$mode = substr($arg, 7);
	}
}

$exe = fractal_zip_paq_discover_executable('parallel_cmix');
if ($exe === null) {
	fwrite(STDERR, "build tools/parallel_paq first (./build.sh)\n");
	exit(1);
}

$enwik = $repo . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($enwik)) {
	fwrite(STDERR, "enwik8 missing\n");
	exit(1);
}

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

$seedText = $mode === 'oracle' ? $text : substr($text, 0, (int) (strlen($text) / 2));
[$o1, $o2] = SpiralPaqSeed::tablesFromText($seedText);
$seedV1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzbg1_' . getmypid() . '.bin';
$seedV2 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzbg2_' . getmypid() . '.bin';
SpiralPaqSeed::writeFile($seedV1, $o1);
SpiralPaqSeed::writeOrder2File($seedV2, $o1, $o2);

$samplePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_spiral_' . getmypid() . '.txt';
file_put_contents($samplePath, $text);
$raw = strlen($text);
$jobs = fractal_zip_paq_parallel_jobs();

$run = static function (string $exe, array $extra, string $in) use ($jobs): array {
	$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_out_' . bin2hex(random_bytes(4)) . '.fzpp';
	$dec = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_dec_' . bin2hex(random_bytes(4)) . '.txt';
	$argv = array_merge(array($exe, 'compress', '-j' . (string) $jobs, '-o', $out), $extra, array($in));
	exec(implode(' ', array_map('escapeshellarg', $argv)) . ' 2>&1', $lines, $ret);
	if ($ret !== 0) {
		return array('ok' => false, 'err' => implode("\n", $lines));
	}
	$comp = is_file($out) ? (int) filesize($out) : 0;
	$dargv = array_merge(array($exe, 'decompress', '-j' . (string) $jobs, '-o', $dec), $extra, array($out));
	exec(implode(' ', array_map('escapeshellarg', $dargv)) . ' 2>&1', $dl, $dret);
	$ok = ($dret === 0 && is_file($dec) && file_get_contents($dec) === file_get_contents($in));
	@unlink($out);
	@unlink($dec);
	return array('ok' => $ok, 'comp' => $comp);
};

$base = $run($exe, array(), $samplePath);
$seed1 = $run($exe, array('--spiral-seed', $seedV1), $samplePath);
$seed2 = $run($exe, array('--spiral-seed', $seedV2), $samplePath);
$gz = strlen((string) gzdeflate($text, 9));

printf("bench_spiral_paq_seed | @%dp | %s bytes | mode=%s | o1=%d o2=%d\n",
	$pages, number_format($raw), $mode, count($o1), count($o2));
printf("  gzip9=%s\n", number_format($gz));

foreach (array(
	'parallel_cmix (baseline)' => $base,
	'cmix+FZBGv1(o1)' => $seed1,
	'cmix+FZBGv2(o1+o2)' => $seed2,
) as $label => $r) {
	if (empty($r['ok'])) {
		printf("  %-24s FAIL %s\n", $label, $r['err'] ?? '');
		continue;
	}
	$comp = (int) $r['comp'];
	printf("  %-24s comp=%s  Δ vs raw=%+d  RT=ok\n", $label, number_format($comp), $comp - $raw);
}

if (!empty($base['ok'])) {
	$cmixBase = (int) $base['comp'];
	printf("  --- vs parallel_cmix baseline (%s B) ---\n", number_format($cmixBase));
	foreach (array(
		'cmix+FZBGv1(o1)' => $seed1,
		'cmix+FZBGv2(o1+o2)' => $seed2,
	) as $label => $r) {
		if (empty($r['ok'])) {
			continue;
		}
		$d = (int) $r['comp'] - $cmixBase;
		printf("  %-24s Δ vs cmix=%+d  %s\n", $label, $d, $d < 0 ? 'WIN' : 'LOSS');
	}
}

@unlink($seedV1);
@unlink($seedV2);
@unlink($samplePath);
fwrite(STDERR, "OK bench_spiral_paq_seed\n");
