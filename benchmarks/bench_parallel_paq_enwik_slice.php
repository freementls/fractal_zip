#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';

$pages = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(1, (int) substr($arg, 8));
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}
$n = min($pages, count($split['pages']));
$parts = array();
for ($i = 0; $i < $n; $i++) {
	$parts[] = substr($blob, (int) $split['pages'][$i]['off'], (int) $split['pages'][$i]['len']);
}
$sample = implode('', $parts);
$samplePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_enwik_' . $pages . 'p.txt';
file_put_contents($samplePath, $sample);
$raw = strlen($sample);

$binCmix = fractal_zip_paq_discover_executable('parallel_cmix');
$binPhda = fractal_zip_paq_discover_executable('parallel_phda9');
if ($binCmix === null || $binPhda === null) {
	fwrite(STDERR, "build tools/parallel_paq first\n");
	exit(1);
}

$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
if (!is_file($dict)) {
	fwrite(STDERR, "missing dict; run: php benchmarks/build_phda9_external_dict.php --pages=384\n");
	exit(1);
}

$run = static function (string $exe, array $extraArgv, string $in) use ($raw): array {
	$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_bench_' . bin2hex(random_bytes(4)) . '.fzpp';
	$dec = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_bench_dec_' . bin2hex(random_bytes(4)) . '.txt';
	$jobs = fractal_zip_paq_parallel_jobs();
	$argv = array_merge(array($exe, 'compress', '-j' . (string) $jobs, '-o', $out), $extraArgv, array($in));
	$cmd = implode(' ', array_map('escapeshellarg', $argv));
	$t0 = microtime(true);
	exec($cmd . ' 2>&1', $lines, $ret);
	$sec = microtime(true) - $t0;
	if ($ret !== 0) {
		return array('ok' => false, 'err' => implode("\n", $lines));
	}
	$comp = is_file($out) ? (int) filesize($out) : 0;
	$decArgv = array_merge(array($exe, 'decompress', '-j' . (string) $jobs, '-o', $dec), $extraArgv, array($out));
	$dcmd = implode(' ', array_map('escapeshellarg', $decArgv));
	exec($dcmd . ' 2>&1', $dl, $dret);
	$ok = ($dret === 0 && is_file($dec) && file_get_contents($dec) === file_get_contents($in));
	@unlink($out);
	@unlink($dec);
	return array(
		'ok' => $ok,
		'raw' => $raw,
		'comp' => $comp,
		'sec' => round($sec, 3),
		'ratio_pct' => $raw > 0 ? round(100.0 * $comp / $raw, 2) : 0.0,
	);
};

$cmix = $run($binCmix, array(), $samplePath);
$phda = $run($binPhda, array('--dict', $dict), $samplePath);
$gz = strlen((string) gzencode($sample, 9));

echo "parallel_paq enwik slice pages={$n} raw={$raw}\n";
echo 'gzip9=' . $gz . "\n";
foreach (array('parallel_cmix' => $cmix, 'parallel_phda9+dict' => $phda) as $label => $r) {
	if (empty($r['ok'])) {
		echo "{$label} FAIL " . ($r['err'] ?? '') . "\n";
		continue;
	}
	echo "{$label} comp={$r['comp']} ratio={$r['ratio_pct']}% sec={$r['sec']} verify=OK\n";
}

@unlink($samplePath);
