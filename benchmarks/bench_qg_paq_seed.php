#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Probe qg-syntax-normalized bigram seed inside parallel_cmix (FZBG blob, --spiral-seed).
 *
 * Usage: nice -n 19 php benchmarks/bench_qg_paq_seed.php [--pages=96] [--mode=prefix|oracle]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once '/srv/http/quantum_grammar/src/QgPaqSeed.php';
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
[$rawO1, $rawO2] = SpiralPaqSeed::tablesFromText($seedText);
[$qgO1, $qgO2] = QgPaqSeed::tablesFromCorrected($seedText);
[$tagO1, $tagO2] = QgPaqSeed::tablesFromSyntaxTagged($seedText);
[$merO1, $merO2] = QgPaqSeed::tablesMergedBoundary($seedText);

$seeds = array(
	'raw_o1' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzqg_raw_' . getmypid() . '.bin',
	'qg_corrected_o2' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzqg_corr_' . getmypid() . '.bin',
	'qg_syntax_tag_o2' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzqg_tag_' . getmypid() . '.bin',
	'qg_boundary_o2' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzqg_mrg_' . getmypid() . '.bin',
);
SpiralPaqSeed::writeFile($seeds['raw_o1'], $rawO1);
QgPaqSeed::writeOrder2File($seeds['qg_corrected_o2'], $qgO1, $qgO2);
QgPaqSeed::writeOrder2File($seeds['qg_syntax_tag_o2'], $tagO1, $tagO2);
QgPaqSeed::writeOrder2File($seeds['qg_boundary_o2'], $merO1, $merO2);

$samplePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzqg_paq_' . getmypid() . '.txt';
file_put_contents($samplePath, $text);
$raw = strlen($text);
$jobs = fractal_zip_paq_parallel_jobs();

$run = static function (string $exe, array $extra, string $in) use ($jobs): array {
	$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzqg_out_' . bin2hex(random_bytes(4)) . '.fzpp';
	$dec = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzqg_dec_' . bin2hex(random_bytes(4)) . '.txt';
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
$rawSeed = $run($exe, array('--spiral-seed', $seeds['raw_o1']), $samplePath);
$corrSeed = $run($exe, array('--spiral-seed', $seeds['qg_corrected_o2']), $samplePath);
$tagSeed = $run($exe, array('--spiral-seed', $seeds['qg_syntax_tag_o2']), $samplePath);
$mrgSeed = $run($exe, array('--spiral-seed', $seeds['qg_boundary_o2']), $samplePath);
$gz = strlen((string) gzdeflate($text, 9));

printf("bench_qg_paq_seed | @%dp | %s bytes | mode=%s\n", $pages, number_format($raw), $mode);
printf("  corrected_len=%s syntax_tag_len=%s\n",
	number_format(strlen(QgPaqSeed::correctedPlain($text))),
	number_format(strlen(QgPaqSeed::syntaxTaggedPlain($text))));
printf("  gzip9=%s\n", number_format($gz));

$results = array(
	'parallel_cmix (baseline)' => $base,
	'cmix+raw_FZBGv1' => $rawSeed,
	'cmix+qg_corrected_FZBGv2' => $corrSeed,
	'cmix+qg_syntax_tag_FZBGv2' => $tagSeed,
	'cmix+qg_boundary_FZBGv2' => $mrgSeed,
);
foreach ($results as $label => $r) {
	if (empty($r['ok'])) {
		printf("  %-28s FAIL %s\n", $label, $r['err'] ?? '');
		continue;
	}
	$comp = (int) $r['comp'];
	printf("  %-28s comp=%s  Δ vs raw=%+d  RT=ok\n", $label, number_format($comp), $comp - $raw);
}

if (!empty($base['ok'])) {
	$cmixBase = (int) $base['comp'];
	printf("  --- vs parallel_cmix baseline (%s B) ---\n", number_format($cmixBase));
	foreach ($results as $label => $r) {
		if ($label === 'parallel_cmix (baseline)' || empty($r['ok'])) {
			continue;
		}
		$d = (int) $r['comp'] - $cmixBase;
		printf("  %-28s Δ vs cmix=%+d  %s\n", $label, $d, $d < 0 ? 'WIN' : 'LOSS');
	}
}

foreach ($seeds as $path) {
	@unlink($path);
}
@unlink($samplePath);
fwrite(STDERR, "OK bench_qg_paq_seed\n");
