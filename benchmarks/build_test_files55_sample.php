#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Rebuild `test_files55_stratified` from tracked `test_files55` (full StevieGee HTML tree).
 * Default target ~72 MiB raw — large enough to stress unified-stream + outers, small enough for iteration.
 *
 * From repo root:
 *   php benchmarks/build_test_files55_sample.php
 *   php benchmarks/build_test_files55_sample.php --target-mib=96
 *   php benchmarks/build_test_files55_sample.php --dry-run
 *
 * Then (bytes protocol: always `--large` on this heavy-list name):
 *   FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G php benchmarks/run_benchmarks.php --only=test_files55_stratified --large --no-case-timeout --json
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$sampler = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'sample_large_corpus.php';
$extra = array_slice($argv, 1);
$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($sampler)
	. ' ' . escapeshellarg('test_files55')
	. ' ' . escapeshellarg('test_files55_stratified');
foreach ($extra as $a) {
	$cmd .= ' ' . escapeshellarg($a);
}
passthru($cmd, $code);
exit($code);
