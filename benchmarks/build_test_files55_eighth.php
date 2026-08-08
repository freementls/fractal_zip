#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Rebuild `test_files55_sample` as ~1/8 of files (every 8th path in stable sorted order) from full `test_files55`.
 *
 * Source: `test_files55` (full StevieGee HTML tree, ~200 MiB raw).
 * Output: `test_files55_sample` (tracked subset for fast bytes-win benches).
 *
 * Usage (repo root):
 *   php benchmarks/build_test_files55_eighth.php [--dry-run]
 */

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$full = $repoRoot . DIRECTORY_SEPARATOR . 'test_files55';
$live = $repoRoot . DIRECTORY_SEPARATOR . 'test_files55_sample';

$dryRun = in_array('--dry-run', $argv, true);

function removeDir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
	}
	rmdir($dir);
}

function copyFileMkdir(string $src, string $dst): void
{
	$parent = dirname($dst);
	if (!is_dir($parent)) {
		mkdir($parent, 0755, true);
	}
	if (!copy($src, $dst)) {
		fwrite(STDERR, "copy failed: {$src} -> {$dst}\n");
		exit(1);
	}
}

if (!is_dir($full)) {
	fwrite(STDERR, "Missing full tree directory: test_files55\n");
	exit(1);
}

$srcReal = realpath($full);
if ($srcReal === false || !is_dir($srcReal)) {
	fwrite(STDERR, "Missing test_files55 after setup.\n");
	exit(1);
}

/** @var list<string> $rels */
$rels = [];
$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($srcReal, FilesystemIterator::SKIP_DOTS)
);
$prefixLen = strlen($srcReal);
foreach ($it as $fileInfo) {
	if (!$fileInfo->isFile()) {
		continue;
	}
	$p = $fileInfo->getPathname();
	$rel = substr($p, $prefixLen);
	if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
		$rel = substr($rel, 1);
	}
	$rels[] = str_replace('\\', '/', $rel);
}
sort($rels, SORT_STRING);

$chosen = [];
foreach ($rels as $i => $rel) {
	if ($i % 8 === 0) {
		$chosen[] = $rel;
	}
}

fwrite(STDOUT, 'Source files: ' . (string) count($rels) . ', subset (every 8th): ' . (string) count($chosen) . "\n");

if ($dryRun) {
	foreach ($chosen as $rel) {
		fwrite(STDOUT, "  {$rel}\n");
	}
	exit(0);
}

if (is_dir($live)) {
	removeDir($live);
}
mkdir($live, 0755, true);

$total = 0;
foreach ($chosen as $rel) {
	$from = $srcReal . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	$to = $live . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	if (!is_file($from)) {
		fwrite(STDERR, "missing: {$from}\n");
		exit(1);
	}
	copyFileMkdir($from, $to);
	$total += (int) filesize($from);
}

fwrite(STDOUT, 'Wrote test_files55_sample with ' . (string) count($chosen) . ' files, ' . (string) $total . " raw bytes\n");
