#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Materialize test_files200/enwik9 — first 1_000_000_000 bytes of English Wikipedia (Mar 2006).
 *
 * Same source as Matt Mahoney's Large Text Compression Benchmark / Hutter Prize corpus.
 * Mirror layout: test_files109/enwik8 (extensionless member, one file per folder).
 *
 * Usage (repo root):
 *   php benchmarks/build_test_files200_enwik9.php
 *   php benchmarks/build_test_files200_enwik9.php --force
 *
 * Bench (hours-scale; use world-record or text-inner profile):
 *   php benchmarks/run_benchmarks.php --only=200 --no-case-timeout --bench-profile=world-record
 */

$root = dirname(__DIR__);
$cacheDir = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.squash_corpus_cache';
$force = in_array('--force', $argv, true);

const ENWIK9_EXPECT_BYTES = 1_000_000_000;
const ENWIK9_SHA1 = '2996e86fb978f93cca8f566cc56998923e7fe581';
const ENWIK9_MD5 = 'e206c3450ac99950df65bf70ef61a12d';
const ENWIK9_ZIP_URL = 'https://mattmahoney.net/dc/enwik9.zip';

$dstDir = $root . DIRECTORY_SEPARATOR . 'test_files200';
$dstFile = $dstDir . DIRECTORY_SEPARATOR . 'enwik9';
$enwik9ZipPath = $cacheDir . DIRECTORY_SEPARATOR . 'enwik9.zip';

function run0(string $cmd): void
{
	exec($cmd . ' 2>&1', $out, $code);
	if ($code !== 0) {
		throw new RuntimeException("Command failed ($code): $cmd\n" . implode("\n", $out));
	}
}

function assertEnwik9(string $path): void
{
	if (!is_file($path)) {
		throw new RuntimeException('Missing output file: ' . $path);
	}
	$sz = (int) filesize($path);
	if ($sz !== ENWIK9_EXPECT_BYTES) {
		throw new RuntimeException("Size mismatch for $path: got $sz expected " . ENWIK9_EXPECT_BYTES);
	}
	$sha1 = strtolower(hash_file('sha1', $path));
	if ($sha1 !== ENWIK9_SHA1) {
		throw new RuntimeException("SHA-1 mismatch for $path: got $sha1 expected " . ENWIK9_SHA1);
	}
}

@mkdir($cacheDir, 0755, true);

if (is_file($dstFile) && !$force) {
	try {
		assertEnwik9($dstFile);
		echo "OK test_files200/enwik9 already present (" . number_format(ENWIK9_EXPECT_BYTES) . " bytes).\n";
		exit(0);
	} catch (RuntimeException $e) {
		echo 'Replacing test_files200/enwik9: ' . $e->getMessage() . "\n";
	}
}

if (!is_file($enwik9ZipPath) || $force) {
	echo "Downloading enwik9.zip (~323 MiB from Matt Mahoney) …\n";
	run0('curl -fsSL --retry 3 --retry-delay 5 -o ' . escapeshellarg($enwik9ZipPath) . ' ' . escapeshellarg(ENWIK9_ZIP_URL));
}

$zipMd5 = strtolower(hash_file('md5', $enwik9ZipPath) ?: '');
if ($zipMd5 !== '' && $zipMd5 !== '3e773f8a1577fda2e27f871ca17f31fd') {
	fwrite(STDERR, "warning: enwik9.zip md5=$zipMd5 (expected 3e773f8a1577fda2e27f871ca17f31fd)\n");
}

if (!is_dir($dstDir)) {
	mkdir($dstDir, 0755, true);
}

echo "Extracting enwik9 to test_files200/ …\n";
$tmpExtract = $cacheDir . DIRECTORY_SEPARATOR . '_enwik9_extract_' . getmypid();
@mkdir($tmpExtract, 0755, true);
run0('unzip -oj ' . escapeshellarg($enwik9ZipPath) . ' enwik9 -d ' . escapeshellarg($tmpExtract));
$extracted = $tmpExtract . DIRECTORY_SEPARATOR . 'enwik9';
if (!is_file($extracted)) {
	throw new RuntimeException('unzip did not produce enwik9 in ' . $tmpExtract);
}
if (!rename($extracted, $dstFile)) {
	if (!copy($extracted, $dstFile)) {
		throw new RuntimeException('failed to move enwik9 to ' . $dstFile);
	}
	@unlink($extracted);
}
@rmdir($tmpExtract);

assertEnwik9($dstFile);
echo 'wrote ' . $dstFile . ' (' . number_format(ENWIK9_EXPECT_BYTES) . " bytes)\n";
echo "MD5 " . ENWIK9_MD5 . " SHA-1 " . ENWIK9_SHA1 . "\n";
echo "Bench: php benchmarks/run_benchmarks.php --only=200 --no-case-timeout --bench-profile=world-record\n";
