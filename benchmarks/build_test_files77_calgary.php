#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Materialize test_files77 from the Calgary compression corpus (14 files, ~3.1 MiB raw).
 *
 * Expects `calgary.zip` at the repository root by default, or:
 *   - first CLI argument: path to the zip, or
 *   - env CALGARY_ZIP
 *
 * Run from repo root:
 *   php benchmarks/build_test_files77_calgary.php
 *
 * Filenames inside the zip are normalized to lowercase (e.g. BOOK1 → book1) for stable paths.
 */
$root = dirname(__DIR__);

function rmtree77(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
	}
	@rmdir($dir);
}

$zipArg = $argv[1] ?? null;
$zipPath = is_string($zipArg) && $zipArg !== ''
	? $zipArg
	: (getenv('CALGARY_ZIP') ?: '');
if ($zipPath === '') {
	$zipPath = $root . DIRECTORY_SEPARATOR . 'calgary.zip';
}
if (!is_file($zipPath) || !is_readable($zipPath)) {
	fwrite(STDERR, "Missing or unreadable Calgary zip: {$zipPath}\n");
	fwrite(STDERR, "Place calgary.zip at the repo root, set CALGARY_ZIP, or pass the path as argv[1].\n");
	exit(1);
}

$dst = $root . DIRECTORY_SEPARATOR . 'test_files77';
rmtree77($dst);
if (!@mkdir($dst, 0755, true) && !is_dir($dst)) {
	fwrite(STDERR, "Cannot create directory: {$dst}\n");
	exit(1);
}

$readme = <<<'TXT'
test_files77 — Calgary compression corpus (14 files):

bib, book1, book2, geo, news, obj1, obj2, paper1, paper2, pic, progc, progl, progp, trans.

Classic whole-file benchmark set (University of Calgary / Canterbury lineage). Materialized from
`calgary.zip` via `php benchmarks/build_test_files77_calgary.php` (filenames lowercased).
TXT;

if (!class_exists(ZipArchive::class)) {
	fwrite(STDERR, "PHP zip extension (ZipArchive) is required.\n");
	exit(1);
}

$written = 0;
$bytesOut = 0;
$z = new ZipArchive();
if ($z->open($zipPath) !== true) {
	fwrite(STDERR, "ZipArchive::open failed: {$zipPath}\n");
	exit(1);
}
for ($i = 0; $i < $z->numFiles; $i++) {
	$name = (string) $z->getNameIndex($i);
	if ($name === '' || str_ends_with($name, '/')) {
		continue;
	}
	$base = basename(str_replace('\\', '/', $name));
	if ($base === '' || $base === '.' || $base === '..') {
		continue;
	}
	$outName = strtolower($base);
	$body = $z->getFromIndex($i);
	if ($body === false) {
		$z->close();
		fwrite(STDERR, "Failed to read zip member: {$name}\n");
		exit(1);
	}
	$target = $dst . DIRECTORY_SEPARATOR . $outName;
	if (file_put_contents($target, $body) === false) {
		$z->close();
		fwrite(STDERR, "Write failed: {$target}\n");
		exit(1);
	}
	$written++;
	$bytesOut += strlen($body);
}
$z->close();

if ($written < 1) {
	fwrite(STDERR, "No files extracted from {$zipPath}\n");
	exit(1);
}

if (file_put_contents($dst . DIRECTORY_SEPARATOR . '00_README_corpus.txt', $readme) === false) {
	fwrite(STDERR, "Failed to write README in {$dst}\n");
	exit(1);
}

echo "test_files77: wrote {$written} members (~{$bytesOut} B) from {$zipPath}\n";
