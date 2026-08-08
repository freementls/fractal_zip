#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Run `perf_test.php` once per corpus in the same set as `--maximum-size=…` discovery
 * (see bench_default_corpus_list.php). Prints a one-line summary per corpus; exit 1 if
 * any case fails or hits the per-case wall timeout.
 *
 *   php benchmarks/perf_test_sweep_max_size.php --maximum-size=2M
 *   php benchmarks/perf_test_sweep_max_size.php --maximum-size=2M --timeout-sec=120 -- --speed --jobs=4
 *
 * Tokens after `--` are appended to each `perf_test.php` invocation.
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_corpus_size.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_default_corpus_list.php';

$maxArg = '2M';
$timeoutSec = 900;
$extraAfterDD = [];
$argc = $_SERVER['argc'] ?? 0;
$argv = $_SERVER['argv'] ?? [];
$dd = false;
for ($i = 1; $i < $argc; $i++) {
	$a = $argv[$i];
	if ($a === '--') {
		$dd = true;
		for ($j = $i + 1; $j < $argc; $j++) {
			$extraAfterDD[] = $argv[$j];
		}
		break;
	}
	if (preg_match('/^--maximum-size=(.+)$/', $a, $m)) {
		$maxArg = $m[1];
	} elseif (preg_match('/^--timeout-sec=(\d+)$/', $a, $m)) {
		$timeoutSec = max(1, (int) $m[1]);
	} elseif ($a === '-h' || $a === '--help') {
		fwrite(STDERR, "Usage: php benchmarks/perf_test_sweep_max_size.php [--maximum-size=2M] [--timeout-sec=900] [-- <extra perf_test args>]\n");
		exit(0);
	}
}

try {
	$maxRawBytes = benchParseMaximumSizeBytes($maxArg);
} catch (InvalidArgumentException $e) {
	fwrite(STDERR, "Invalid --maximum-size: {$e->getMessage()}\n");
	exit(2);
}

$corpora = benchDiscoverDefaultRunBenchmarksCorpora($repoRoot, false, false, $maxRawBytes);
if ($corpora === []) {
	fwrite(STDERR, "No corpora under cap (materialize test_files* or adjust --maximum-size).\n");
	exit(2);
}

$php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
$perf = $repoRoot . DIRECTORY_SEPARATOR . 'perf_test.php';
$fail = 0;

$sizePart = '--maximum-size=' . $maxArg;

fwrite(STDERR, "perf_test_sweep_max_size: " . count($corpora) . " corpora, cap={$maxRawBytes} B, timeout/case={$timeoutSec}s\n");

foreach ($corpora as $label) {
	$cmd = [
		'timeout',
		(string) $timeoutSec,
		$php,
		$perf,
		'--only=' . $label,
		$sizePart,
		'--no-zip-time-budget',
		'--no-xhprof',
		'--no-stack-sample',
		'--jobs=1',
		'--budget-sec=86400',
	];
	foreach ($extraAfterDD as $t) {
		$cmd[] = $t;
	}
	$t0 = microtime(true);
	$desc = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
	$p = proc_open($cmd, $desc, $pipes, $repoRoot, array_merge($_ENV, ['FRACTAL_ZIP_SKIP_ARC' => '1']));
	if (!is_resource($p)) {
		echo "{$label}\tPROC_FAIL\n";
		$fail++;
		continue;
	}
	fclose($pipes[0]);
	stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	$code = proc_close($p);
	$wall = round(microtime(true) - $t0, 2);
	$exit = ($code >> 8) & 255;
	if ($exit === 124) {
		echo "{$label}\tTIMEOUT exit=124 wall={$wall}s\n";
		$fail++;
	} elseif ($exit !== 0) {
		echo "{$label}\tFAIL exit={$exit} wall={$wall}s\n";
		$fail++;
	} else {
		echo "{$label}\tOK wall={$wall}s\n";
	}
}

exit($fail > 0 ? 1 : 0);
