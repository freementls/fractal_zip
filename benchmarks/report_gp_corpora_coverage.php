#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Report presence/size of public GP corpora test_files202–210.
 *
 *   php benchmarks/report_gp_corpora_coverage.php
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'gp_corpus_lib.php';

$root = gp_repo_root();
$minBytes = 64 * 1024 * 1024; // soft floor per lake
$ids = [];
for ($i = 202; $i <= 210; $i++) {
	$ids[] = 'test_files' . $i;
}

$missing = [];
$small = [];
echo "=== GP corpora coverage (test_files202–210) ===\n";
echo "Note: lakes are gitignored; materialize with build_test_files202_210_gp.php\n\n";

foreach ($ids as $lab) {
	$dir = $root . DIRECTORY_SEPARATOR . $lab;
	$man = $dir . DIRECTORY_SEPARATOR . 'MANIFEST.json';
	if (!is_dir($dir) || !is_file($man)) {
		echo sprintf("%-16s MISSING\n", $lab);
		$missing[] = $lab;
		continue;
	}
	$m = json_decode((string) file_get_contents($man), true) ?: [];
	$bytes = (int) ($m['total_bytes'] ?? gp_dir_total_bytes($dir));
	$files = (int) ($m['file_count'] ?? gp_count_files($dir));
	$title = (string) ($m['title'] ?? '');
	$flag = $bytes < $minBytes ? ' SMALL' : ' OK';
	if ($bytes < $minBytes) {
		$small[] = $lab;
	}
	echo sprintf(
		"%-16s %7.1f MiB  %6d files%s  %s\n",
		$lab,
		$bytes / 1048576,
		$files,
		$flag,
		$title
	);
	$srcs = $m['sources'] ?? [];
	if (is_array($srcs) && $srcs !== []) {
		echo '  sources: ' . count($srcs) . " pinned URL(s)\n";
	}
}

if ($missing !== [] || $small !== []) {
	echo "\nFAIL missing=" . count($missing) . ' small=' . count($small) . "\n";
	exit(1);
}
echo "\nPASS: all nine GP lakes present (≥64 MiB each)\n";
exit(0);
