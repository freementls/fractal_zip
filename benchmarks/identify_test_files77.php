#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Print tiered content-format identification for each file under test_files77 (Calgary corpus).
 *
 *   php benchmarks/identify_test_files77.php
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_identify.php';

$dir = $root . DIRECTORY_SEPARATOR . 'test_files77';
if (!is_dir($dir)) {
	fwrite(STDERR, "Missing {$dir}\n");
	exit(1);
}

$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
	RecursiveIteratorIterator::SELF_FIRST
);
$rows = array();
foreach ($it as $fi) {
	if (!$fi->isFile()) {
		continue;
	}
	$name = $fi->getFilename();
	if ($name[0] === '.' && $name !== '.') {
		continue;
	}
	$rel = 'test_files77/' . $name;
	$raw = @file_get_contents($fi->getPathname());
	if ($raw === false) {
		$raw = '';
	}
	$rows[$name] = fractal_zip_content_format_identify::identify($rel, $raw);
}
ksort($rows, SORT_STRING);

printf("%-16s %6s tr %-14s %-32s %-14s %s\n", 'file', 'bytes', 'delim_profile', 'final_label', 'pairs', 'limiters');
foreach ($rows as $name => $r) {
	$da = $r['delimiter_association'];
	$prof = (string) $da['profile'];
	$pairs = $da['recommendation']['common_limiter_pairs'];
	$pairStr = '';
	foreach ($pairs as $pr) {
		$pairStr .= $pr[0] . $pr[1] . ' ';
	}
	$pairStr = trim($pairStr);
	if (strlen($pairStr) > 14) {
		$pairStr = substr($pairStr, 0, 12) . '…';
	}
	$limStr = implode('', array_slice($da['recommendation']['common_limiters'], 0, 10));
	if (strlen($limStr) > 12) {
		$limStr = substr($limStr, 0, 10) . '…';
	}
	printf(
		"%-16s %6d %d   %-14s %-32s %-14s %-12s\n",
		$name,
		(int) $r['size'],
		(int) $r['tier_resolved'],
		substr($prof, 0, 14),
		substr((string) $r['final_label'], 0, 32),
		$pairStr !== '' ? $pairStr : '—',
		$limStr !== '' ? $limStr : '—'
	);
}
