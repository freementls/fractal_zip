#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare perf_test wall time with zpaq **single-thread** vs **all-thread** (zpaqfranz `-threads 1` vs `-threads 0`).
 * Useful for heavy-zpaq corpora (e.g. test_files114). test_files29 is pathological (very deep recursion); use time budgets as needed.
 *
 *   php benchmarks/bench_zpaq_threads_ab.php --corpus=test_files114
 *   php benchmarks/bench_zpaq_threads_ab.php --corpus=test_files114 -- --speed --budget-sec=600
 */

$repoRoot = dirname(__DIR__);
chdir($repoRoot);

$corpus = 'test_files114';
$extra = array();
$argc = (int) ($_SERVER['argc'] ?? 1);
$argv = $_SERVER['argv'] ?? array();
for ($i = 1; $i < $argc; $i++) {
	$a = $argv[$i];
	if ($a === '--') {
		for ($j = $i + 1; $j < $argc; $j++) {
			$extra[] = $argv[$j];
		}
		break;
	}
	if (preg_match('/^--corpus=(.+)$/', $a, $m)) {
		$corpus = $m[1];
	} elseif ($a === '-h' || $a === '--help') {
		fwrite(STDERR, "Usage: php benchmarks/bench_zpaq_threads_ab.php [--corpus=test_files114] [-- <extra perf_test.php args>]\n");
		exit(0);
	}
}

$php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
$perf = $repoRoot . DIRECTORY_SEPARATOR . 'perf_test.php';
if (!is_file($perf)) {
	fwrite(STDERR, "Missing perf_test.php\n");
	exit(2);
}

$run = static function (string $threadsVal) use ($php, $perf, $corpus, $extra): array {
	$oldT = getenv('FRACTAL_ZIP_ZPAQ_THREADS');
	putenv('FRACTAL_ZIP_ZPAQ_THREADS=' . $threadsVal);
	$args = array_merge(
		array(
			$php,
			$perf,
			'--only=' . $corpus,
			'--no-xhprof',
			'--no-stack-sample',
			'--jobs=1',
			'--no-zip-time-budget',
			'--budget-sec=86400',
		),
		$extra
	);
	$line = '';
	foreach ($args as $i => $p) {
		$line .= ($i > 0 ? ' ' : '') . escapeshellarg($p);
	}
	$stdout = shell_exec($line . ' 2>&1');
	if ($oldT === false) {
		putenv('FRACTAL_ZIP_ZPAQ_THREADS');
	} else {
		putenv('FRACTAL_ZIP_ZPAQ_THREADS=' . $oldT);
	}
	$wall = null;
	$zip = null;
	if (is_string($stdout) && preg_match('/wall_clock_total=([0-9.]+)s/', $stdout, $mw)) {
		$wall = (float) $mw[1];
	}
	if (is_string($stdout) && preg_match('/zip_folder_sum=([0-9.]+)s/', $stdout, $mz)) {
		$zip = (float) $mz[1];
	}

	return array('wall' => $wall, 'zip_sum' => $zip, 'out' => is_string($stdout) ? $stdout : '');
};

require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$ex = fractal_zip::zpaq_executable();
fwrite(STDERR, "zpaq exe: " . ($ex ?? '(null)') . "\n");
if ($ex !== null) {
	fwrite(
		STDERR,
		'zpaq -threads eligible: ' . (fractal_zip::zpaq_executable_accepts_global_threads_argv((string) $ex) ? 'yes (auto -threads 0 when threads env unset)' : 'no (stock zpaq: install zpaqfranz + FRACTAL_ZIP_ZPAQ=…; or FRACTAL_ZIP_ZPAQ_GLOBAL_THREADS=1 if your build supports -threads)') . "\n"
	);
}

fwrite(STDERR, "Run A: FRACTAL_ZIP_ZPAQ_THREADS=1 (single-thread zpaq)\n");
$a = $run('1');
fwrite(STDERR, "Run B: FRACTAL_ZIP_ZPAQ_THREADS=0 (zpaqfranz all cores)\n");
$b = $run('0');

printf(
	"corpus=%s  zip_folder_sum_s  st=%s  mt=%s  wall_s  st=%s  mt=%s",
	$corpus,
	$a['zip_sum'] !== null ? sprintf('%.4f', $a['zip_sum']) : 'n/a',
	$b['zip_sum'] !== null ? sprintf('%.4f', $b['zip_sum']) : 'n/a',
	$a['wall'] !== null ? sprintf('%.4f', $a['wall']) : 'n/a',
	$b['wall'] !== null ? sprintf('%.4f', $b['wall']) : 'n/a'
);
	if ($a['wall'] !== null && $b['wall'] !== null && $b['wall'] > 0.0) {
	printf('  st_wall/mt_wall=%.2f (>1 ⇒ MT quicker)', $a['wall'] / $b['wall']);
}
if ($a['zip_sum'] !== null && $b['zip_sum'] !== null && $b['zip_sum'] > 0.0) {
	printf('  zip_s_ratio=%.3f', $a['zip_sum'] / $b['zip_sum']);
}
echo "\n";

if ($a['wall'] === null || $b['wall'] === null) {
	fwrite(STDERR, "Could not parse perf_test summary line (wall_clock_total / zip_folder_sum).\n");
	exit(1);
}

exit(0);
