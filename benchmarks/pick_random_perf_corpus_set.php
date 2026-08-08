#!/usr/bin/env php
<?php
/**
 * Reproducibly pick N random corpora from the same pool as run_benchmarks.php (default:
 * all canonical test_files* under the repo, minus huge trees, synthetic/micro defaults, and
 * {@see benchBuildDefaultRunBenchmarksSkipList} opt-in corpora such as **test_files133**).
 *
 * Usage (repo root):
 *   php benchmarks/pick_random_perf_corpus_set.php
 *   php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260425
 *   php benchmarks/pick_random_perf_corpus_set.php --print-perf-only
 */
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_default_corpus_list.php';

$repoRoot = dirname(__DIR__);
$n = 5;
$seed = 20260425;
$printPerfOnly = false;
foreach (array_slice($argv, 1) as $a) {
	if (preg_match('/^--n=(\d+)$/', (string) $a, $m)) {
		$n = max(1, min(32, (int) $m[1]));
	} elseif (preg_match('/^--seed=(\d+)$/', (string) $a, $m)) {
		$seed = (int) $m[1];
	} elseif ($a === '--print-perf-only') {
		$printPerfOnly = true;
	}
}

$skipHuge = [
	'test_files54', 'test_files55', 'test_files55_sample', 'test_files56', 'test_files57',
	'test_files58', 'test_files59',
];
$skipExtra = ['test_files50', 'test_files51', 'test_files60'];
$skip = array_fill_keys(array_merge(
	benchBuildDefaultRunBenchmarksSkipList(false, false, null),
	$skipHuge,
	$skipExtra
), true);
$dirs = glob($repoRoot . DIRECTORY_SEPARATOR . 'test_files*', GLOB_ONLYDIR) ?: [];
$names = [];
foreach ($dirs as $path) {
	$base = basename($path);
	if (!isset($skip[$base]) && preg_match('/^test_files(?:[0-9]+)?$/', $base) === 1) {
		$names[] = $base;
	}
}
sort($names, SORT_NATURAL);
$count = count($names);
if ($count < $n) {
	fwrite(STDERR, "pick_random_perf_corpus_set: pool has only {$count} corpora, need N={$n}\n");
	exit(1);
}
mt_srand($seed);
$shuf = $names;
for ($i = $count - 1; $i > 0; $i--) {
	$j = (int) (mt_rand() / mt_getrandmax() * ($i + 1));
	if ($j !== $i) {
		[$shuf[$i], $shuf[$j]] = [$shuf[$j], $shuf[$i]];
	}
}
$pick = array_slice($shuf, 0, $n);
sort($pick, SORT_NATURAL);
$csv = implode(',', $pick);
if ($printPerfOnly) {
	echo 'php perf_test.php --only=' . $csv . " --no-zip-time-budget --repeat=7 --no-stack-sample\n";
	exit(0);
}
echo "pool={$count} n={$n} seed={$seed}\n";
echo "cases={$csv}\n";
echo "perf: php perf_test.php --only={$csv} --no-zip-time-budget --repeat=7 --no-stack-sample\n";
