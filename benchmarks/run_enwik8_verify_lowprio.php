#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Low-priority full enwik8 verify: records decompress_seconds in encode JSON.
 *
 * Usage:
 *   FRACTAL_ZIP_LOW_MEMORY=1 php benchmarks/run_enwik8_verify_lowprio.php
 *   php benchmarks/run_enwik8_verify_lowprio.php path/to/test_files109.fz
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_LOW_MEMORY=1');
putenv('FRACTAL_ZIP_BACKGROUND=1');
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_low_memory_env.php';
bench_low_memory_apply_env();

$lock = bench_low_memory_try_diagnostic_lock($repo, 'enwik8_verify');
if ($lock === null) {
	fwrite(STDERR, "Another verify/diagnostic holds the lock. Skipping.\n");
	exit(2);
}
register_shutdown_function(static function () use ($lock): void {
	flock($lock, LOCK_UN);
	fclose($lock);
});

$fzcPath = $argv[1] ?? ($repo . '/test_files109.fz');
$fzcAbs = realpath($fzcPath);
if ($fzcAbs === false || !is_file($fzcAbs)) {
	fwrite(STDERR, "Missing {$fzcPath}\n");
	exit(1);
}

$verifyScript = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'verify_enwik8_textcodec_fzc.php';
if (!is_file($verifyScript)) {
	fwrite(STDERR, "Missing {$verifyScript}\n");
	exit(1);
}

$logPath = $repo . '/benchmarks/logs/enwik8_verify_lowprio.log';
@mkdir(dirname($logPath), 0755, true);

fwrite(STDERR, "[verify_lowprio] start " . date('c') . " fzc={$fzcAbs}\n");
$t0 = microtime(true);
$out = array();
$ret = 0;
$ini = bench_low_memory_php_ini_args();
exec(
	escapeshellarg(PHP_BINARY) . ' ' . implode(' ', array_map('escapeshellarg', $ini)) . ' '
	. escapeshellarg($verifyScript) . ' ' . escapeshellarg($fzcAbs) . ' 2>&1',
	$out,
	$ret
);
$sec = round(microtime(true) - $t0, 2);
$text = implode("\n", $out) . "\n";
file_put_contents($logPath, '[' . date('c') . "] exit={$ret} sec={$sec}\n{$text}");
foreach ($out as $line) {
	echo $line . "\n";
}

$ok = ($ret === 0);
$jsonPath = $repo . '/benchmarks/.enwik8_exp_phda9_xml.json';
if (is_file($jsonPath)) {
	$j = json_decode((string) file_get_contents($jsonPath), true);
	if (is_array($j) && isset($j['cases'][0]) && is_array($j['cases'][0])) {
		$j['cases'][0]['verify_ok'] = $ok;
		$j['cases'][0]['decompress_seconds'] = $sec;
		$j['cases'][0]['verify_at'] = date('c');
		file_put_contents($jsonPath, json_encode($j, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
		fwrite(STDERR, "[verify_lowprio] updated {$jsonPath} verify_ok=" . ($ok ? '1' : '0') . " decompress_seconds={$sec}\n");
	}
}

require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_hutter_prize.php';
$extrap = enwik8_hutter_extrapolate_corpus_hours($sec);
fwrite(STDERR, sprintf(
	"[verify_lowprio] decompress %.1f h enwik8 → ~%.1f h enwik9 extrap (%s @ GB5=%d)\n",
	$sec / 3600,
	$extrap['enwik9_hours'],
	$extrap['within_budget'] ? 'within budget' : 'OVER',
	ENWIK8_HUTTER_GEEKBENCH5_REF_SINGLE
));

exit($ok ? 0 : 1);
