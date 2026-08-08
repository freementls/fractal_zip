#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Build test_files133: one folder containing the twelve **uncompressed** Silesia corpus files
 * (same bytes as Matt Mahoney / Squash mirror ids). Total raw size **211 938 580** B.
 *
 * Prerequisite: materialize Squash Silesia singles via:
 *   php benchmarks/build_test_files_squash_corpora.php
 * (needs curl, bzip2; writes test_files108, test_files116–120, test_files124–126, test_files130–132.)
 *
 * Usage (repo root):
 *   php benchmarks/build_test_files133_silesia12.php
 *   php benchmarks/build_test_files133_silesia12.php --force
 *   php benchmarks/build_test_files133_silesia12.php --dry-run   # verify sources only; no writes
 *
 * **Discovery:** `test_files133` is **skipped in default** `run_benchmarks.php` discovery (heavy opt-in).
 * Bench: `php benchmarks/run_benchmarks.php --only=133 --large --no-case-timeout --json` (bare **133** → **test_files133**).
 *
 * See **benchmarks/SILESIA_BENCHMARK.md** for Mahoney scoring vs this folder layout.
 */

$root = dirname(__DIR__);
$dst = $root . DIRECTORY_SEPARATOR . 'test_files133';
$force = in_array('--force', $argv, true);
$dryRun = in_array('--dry-run', $argv, true);

/** @var list<array{0: string, 1: string, 2: int}> corpusDir, member basename, expected bytes (Squash mirror) */
$members = [
	['test_files108', 'dickens', 10192446],
	['test_files116', 'mozilla', 51220480],
	['test_files117', 'mr', 9970564],
	['test_files118', 'nci', 33553445],
	['test_files119', 'ooffice', 6152192],
	['test_files120', 'osdb', 10085684],
	['test_files124', 'reymont', 6627202],
	['test_files125', 'samba', 21606400],
	['test_files126', 'sao', 7251944],
	['test_files130', 'webster', 41458703],
	['test_files131', 'xml', 5345280],
	['test_files132', 'x-ray', 8474240],
];

$expectTotal = 0;
foreach ($members as [, , $sz]) {
	$expectTotal += $sz;
}

if ($expectTotal !== 211938580) {
	fwrite(STDERR, "internal: expected total mismatch ($expectTotal vs 211938580)\n");
	exit(2);
}

if ($dryRun) {
	$bad = false;
	foreach ($members as [$corpus, $base, $sz]) {
		$src = $root . DIRECTORY_SEPARATOR . $corpus . DIRECTORY_SEPARATOR . $base;
		if (!is_file($src)) {
			fwrite(STDERR, "[dry-run] missing: $src\n");
			$bad = true;
			continue;
		}
		$fsz = filesize($src);
		if (!is_int($fsz) || $fsz !== $sz) {
			fwrite(STDERR, "[dry-run] size mismatch $src: got $fsz expected $sz\n");
			$bad = true;
			continue;
		}
		echo "[dry-run] ok $corpus/$base ($sz B)\n";
	}
	if (is_dir($dst)) {
		echo "[dry-run] destination exists: $dst (use --force to replace)\n";
	} else {
		echo "[dry-run] would mkdir $dst and copy " . count($members) . " files ($expectTotal B)\n";
	}
	exit($bad ? 1 : 0);
}

if (is_dir($dst) && !$force) {
	$ok = true;
	$sum = 0;
	foreach ($members as [$dir, $base, $sz]) {
		$p = $dst . DIRECTORY_SEPARATOR . $base;
		if (!is_file($p) || filesize($p) !== $sz) {
			$ok = false;
			break;
		}
		$sum += $sz;
	}
	if ($ok && $sum === $expectTotal) {
		echo "OK test_files133 already complete ($expectTotal B). Use --force to rebuild.\n";
		exit(0);
	}
	echo "Replacing incomplete test_files133 (use --force next time to skip this message)…\n";
}

function rrmdir(string $dir): void
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

rrmdir($dst);
if (!@mkdir($dst, 0755, true)) {
	fwrite(STDERR, "mkdir failed: $dst\n");
	exit(1);
}

$total = 0;
foreach ($members as [$corpus, $base, $sz]) {
	$srcDir = $root . DIRECTORY_SEPARATOR . $corpus;
	$src = $srcDir . DIRECTORY_SEPARATOR . $base;
	if (!is_file($src)) {
		fwrite(STDERR, "Missing source file: $src\nRun: php benchmarks/build_test_files_squash_corpora.php\n");
		rrmdir($dst);
		exit(1);
	}
	$fsz = filesize($src);
	if (!is_int($fsz) || $fsz !== $sz) {
		fwrite(STDERR, "Size mismatch for $src: got $fsz expected $sz (re-fetch Squash corpora with --force if needed)\n");
		rrmdir($dst);
		exit(1);
	}
	$out = $dst . DIRECTORY_SEPARATOR . $base;
	if (!@copy($src, $out)) {
		fwrite(STDERR, "copy failed: $src -> $out\n");
		rrmdir($dst);
		exit(1);
	}
	$total += $sz;
	echo "  $base ($sz B)\n";
}

if ($total !== $expectTotal) {
	fwrite(STDERR, "internal: wrote $total B\n");
	exit(2);
}

echo "Wrote test_files133 ($total B = twelve Silesia files). Bench: php benchmarks/run_benchmarks.php --only=133 --large --no-case-timeout --json\n";
