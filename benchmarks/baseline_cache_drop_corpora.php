#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Remove one or more corpus labels from benchmarks/.baseline_cache.json (stale outer/min-ext reuse).
 *
 *   php benchmarks/baseline_cache_drop_corpora.php test_files55_sample test_files133_sample
 */

$repoRoot = dirname(__DIR__);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$labels = array();
foreach (array_slice($argv, 1) as $a) {
	if ($a === '--help' || $a === '-h') {
		fwrite(STDOUT, "Usage: php benchmarks/baseline_cache_drop_corpora.php <corpus>...\n");
		exit(0);
	}
	$labels[] = $a;
}

if ($labels === array()) {
	fwrite(STDERR, "Usage: php benchmarks/baseline_cache_drop_corpora.php <corpus>...\n");
	exit(2);
}

$path = getenv('FRACTAL_ZIP_BENCH_BASELINE_CACHE');
if ($path === false || trim((string) $path) === '') {
	$path = __DIR__ . DIRECTORY_SEPARATOR . '.baseline_cache.json';
}

if (!is_readable($path)) {
	fwrite(STDERR, "baseline_cache_drop: no cache at {$path}\n");
	exit(1);
}

$root = bench_json_decode_file_assoc_try($path, 'baseline_cache_drop');
if ($root === null) {
	exit(1);
}

$entriesKey = isset($root['entries']) && is_array($root['entries']) ? 'entries' : (isset($root['corpora']) && is_array($root['corpora']) ? 'corpora' : null);
$bucket = $entriesKey !== null ? $root[$entriesKey] : $root;
$dropped = array();
foreach ($labels as $lab) {
	if (isset($bucket[$lab])) {
		unset($bucket[$lab]);
		$dropped[] = $lab;
	}
}

if ($entriesKey !== null) {
	$root[$entriesKey] = $bucket;
} else {
	$root = $bucket;
}

bench_json_file_put($path, $root, true, 'baseline_cache_drop');
fwrite(STDOUT, 'baseline_cache_drop: removed ' . (count($dropped) > 0 ? implode(', ', $dropped) : '(none matched)') . " from {$path}\n");
