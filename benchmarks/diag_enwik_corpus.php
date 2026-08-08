#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Print enwik8/enwik9 corpus path, bytes, and streaming page count.
 *
 * Usage:
 *   php benchmarks/diag_enwik_corpus.php [--corpus=8|9]
 */

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';

$id = 8;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--corpus=')) {
		$id = max(8, min(9, (int) substr($arg, 9)));
	}
}

$path = fractal_zip_enwik_corpus_path($repo, $id);
$name = fractal_zip_enwik_corpus_name($id);
$expect = fractal_zip_enwik_corpus_expected_bytes($id);

if (!is_file($path)) {
	fwrite(STDERR, "missing {$path}\n");
	if ($id === 9) {
		fwrite(STDERR, "run: php benchmarks/build_test_files200_enwik9.php\n");
	}
	exit(1);
}

$sz = (int) filesize($path);
$t0 = microtime(true);
$pages = fractal_zip_enwik_count_pages_in_file($path);
$sec = round(microtime(true) - $t0, 2);

echo "{$name} path={$path}\n";
echo "bytes={$sz} expected={$expect} ok=" . ($sz === $expect ? 'yes' : 'no') . "\n";
echo "pages={$pages} count_sec={$sec}\n";
if ($id === 8) {
	echo "virtual_members@96pp≈" . (int) ceil($pages / 96) . "\n";
} else {
	echo "virtual_members@96pp≈" . (int) ceil($pages / 96) . "\n";
}
