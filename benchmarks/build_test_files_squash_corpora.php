#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Materialize test_files105–test_files132: one directory per dataset from the
 * Squash Compression Benchmark list (same ids and byte sizes as
 * https://quixdb.github.io/squash-benchmark/ — see squash-benchmark-web squash-benchmark.js).
 *
 * Prerequisites: curl, bzip2, unzip in PATH (Silesia files on sun.aei.polsl.pl are bzip2-compressed).
 *
 * Usage (repo root):
 *   php benchmarks/build_test_files_squash_corpora.php
 *   php benchmarks/build_test_files_squash_corpora.php --force   # re-download / replace
 *
 * enwik8 is dataset id enwik8 (test_files109 here); it is fetched from Matt Mahoney’s
 * enwik8.zip (same source as the Large Text Compression Benchmark / text.html).
 *
 * These corpora are gitignored; once materialized, run_benchmarks.php discovers them by default. Run e.g.:
 *   php benchmarks/run_benchmarks.php --only=test_files109 --no-case-timeout --json
 */

$root = dirname(__DIR__);
$cacheDir = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.squash_corpus_cache';
$force = in_array('--force', $argv, true);

/**
 * Same order as var datasets in https://raw.githubusercontent.com/quixdb/squash-benchmark-web/master/squash-benchmark.js
 *
 * @var list<array{id: string, expect: int, kind: 'canterbury'|'silesia'|'snappy'|'enwik8'}>
 */
$rows = [
	['id' => 'alice29.txt', 'expect' => 152089, 'kind' => 'canterbury'],
	['id' => 'asyoulik.txt', 'expect' => 125179, 'kind' => 'canterbury'],
	['id' => 'cp.html', 'expect' => 24603, 'kind' => 'canterbury'],
	['id' => 'dickens', 'expect' => 10192446, 'kind' => 'silesia'],
	['id' => 'enwik8', 'expect' => 100000000, 'kind' => 'enwik8'],
	['id' => 'fields.c', 'expect' => 11150, 'kind' => 'canterbury'],
	['id' => 'fireworks.jpeg', 'expect' => 123093, 'kind' => 'snappy'],
	['id' => 'geo.protodata', 'expect' => 118588, 'kind' => 'snappy'],
	['id' => 'grammar.lsp', 'expect' => 3721, 'kind' => 'canterbury'],
	['id' => 'kennedy.xls', 'expect' => 1029744, 'kind' => 'canterbury'],
	['id' => 'lcet10.txt', 'expect' => 426754, 'kind' => 'canterbury'],
	['id' => 'mozilla', 'expect' => 51220480, 'kind' => 'silesia'],
	['id' => 'mr', 'expect' => 9970564, 'kind' => 'silesia'],
	['id' => 'nci', 'expect' => 33553445, 'kind' => 'silesia'],
	['id' => 'ooffice', 'expect' => 6152192, 'kind' => 'silesia'],
	['id' => 'osdb', 'expect' => 10085684, 'kind' => 'silesia'],
	['id' => 'paper-100k.pdf', 'expect' => 102400, 'kind' => 'snappy'],
	['id' => 'plrabn12.txt', 'expect' => 481861, 'kind' => 'canterbury'],
	['id' => 'ptt5', 'expect' => 513216, 'kind' => 'canterbury'],
	['id' => 'reymont', 'expect' => 6627202, 'kind' => 'silesia'],
	['id' => 'samba', 'expect' => 21606400, 'kind' => 'silesia'],
	['id' => 'sao', 'expect' => 7251944, 'kind' => 'silesia'],
	['id' => 'sum', 'expect' => 38240, 'kind' => 'canterbury'],
	['id' => 'urls.10K', 'expect' => 702087, 'kind' => 'snappy'],
	['id' => 'xargs.1', 'expect' => 4227, 'kind' => 'canterbury'],
	['id' => 'webster', 'expect' => 41458703, 'kind' => 'silesia'],
	['id' => 'xml', 'expect' => 5345280, 'kind' => 'silesia'],
	['id' => 'x-ray', 'expect' => 8474240, 'kind' => 'silesia'],
];

$silesiaBzipBase = 'https://sun.aei.polsl.pl/~sdeor/corpus/';
$canterburyZipUrl = 'https://corpus.canterbury.ac.nz/resources/cantrbry.zip';
$snappyBase = 'https://raw.githubusercontent.com/google/snappy/master/testdata/';
$enwik8ZipUrl = 'https://mattmahoney.net/dc/enwik8.zip';

function run0(string $cmd): void
{
	exec($cmd . ' 2>&1', $out, $code);
	if ($code !== 0) {
		throw new RuntimeException("Command failed ($code): $cmd\n" . implode("\n", $out));
	}
}

function assertSize(string $path, int $expect): void
{
	if (!is_file($path)) {
		throw new RuntimeException("Missing output file: $path");
	}
	$sz = (int) filesize($path);
	if ($sz !== $expect) {
		throw new RuntimeException("Size mismatch for $path: got $sz expected $expect");
	}
}

@mkdir($cacheDir, 0755, true);
$canterburyZipPath = $cacheDir . DIRECTORY_SEPARATOR . 'cantrbry.zip';
if (!is_file($canterburyZipPath) || $force) {
	echo "Downloading Canterbury cantrbry.zip …\n";
	run0('curl -fsSL -o ' . escapeshellarg($canterburyZipPath) . ' ' . escapeshellarg($canterburyZipUrl));
}
$enwik8ZipPath = $cacheDir . DIRECTORY_SEPARATOR . 'enwik8.zip';
if (!is_file($enwik8ZipPath) || $force) {
	echo "Downloading enwik8.zip (Matt Mahoney) …\n";
	run0('curl -fsSL -o ' . escapeshellarg($enwik8ZipPath) . ' ' . escapeshellarg($enwik8ZipUrl));
}

$n = 105;
foreach ($rows as $row) {
	$id = $row['id'];
	$expect = $row['expect'];
	$kind = $row['kind'];
	$dstDir = $root . DIRECTORY_SEPARATOR . 'test_files' . (string) $n;
	$dstFile = $dstDir . DIRECTORY_SEPARATOR . $id;

	if (is_file($dstFile) && !$force) {
		$sz = (int) filesize($dstFile);
		if ($sz === $expect) {
			echo "OK test_files$n ($id) already present.\n";
			$n++;
			continue;
		}
		echo "Replacing test_files$n ($id): size was $sz, expected $expect.\n";
	}

	if (!is_dir($dstDir)) {
		mkdir($dstDir, 0755, true);
	} else {
		foreach (scandir($dstDir) ?: [] as $fn) {
			if ($fn === '.' || $fn === '..') {
				continue;
			}
			@unlink($dstDir . DIRECTORY_SEPARATOR . $fn);
		}
	}

	echo "Building test_files$n ← $id ($kind) …\n";

	if ($kind === 'canterbury') {
		$zip = new ZipArchive();
		if ($zip->open($canterburyZipPath) !== true) {
			throw new RuntimeException('Failed to open Canterbury zip: ' . $canterburyZipPath);
		}
		$idx = $zip->locateName($id, ZipArchive::FL_NOCASE | ZipArchive::FL_NODIR);
		if ($idx === false) {
			$zip->close();
			throw new RuntimeException("Member not found in cantrbry.zip: $id");
		}
		$data = $zip->getFromIndex($idx);
		$zip->close();
		if ($data === false) {
			throw new RuntimeException("Failed to read $id from cantrbry.zip");
		}
		if (strlen($data) !== $expect) {
			throw new RuntimeException("Canterbury $id unexpected zip payload size " . strlen($data));
		}
		if (file_put_contents($dstFile, $data) === false) {
			throw new RuntimeException("write failed: $dstFile");
		}
	} elseif ($kind === 'silesia') {
		$url = $silesiaBzipBase . rawurlencode($id);
		$tmpBz = $cacheDir . DIRECTORY_SEPARATOR . 'tmp_' . $id . '.bz2';
		run0('curl -fsSL -o ' . escapeshellarg($tmpBz) . ' ' . escapeshellarg($url));
		run0('bzip2 -dc ' . escapeshellarg($tmpBz) . ' > ' . escapeshellarg($dstFile));
		@unlink($tmpBz);
	} elseif ($kind === 'snappy') {
		$url = $snappyBase . rawurlencode($id);
		run0('curl -fsSL -o ' . escapeshellarg($dstFile) . ' ' . escapeshellarg($url));
	} elseif ($kind === 'enwik8') {
		$zip = new ZipArchive();
		if ($zip->open($enwik8ZipPath) !== true) {
			throw new RuntimeException('Failed to open enwik8.zip: ' . $enwik8ZipPath);
		}
		$data = $zip->getFromName('enwik8');
		$zip->close();
		if ($data === false) {
			throw new RuntimeException('enwik8 member missing in enwik8.zip');
		}
		if (strlen($data) !== $expect) {
			throw new RuntimeException('enwik8.zip payload size ' . strlen($data) . " expected $expect");
		}
		if (file_put_contents($dstFile, $data) === false) {
			throw new RuntimeException("write failed: $dstFile");
		}
	} else {
		throw new RuntimeException('unknown kind');
	}

	assertSize($dstFile, $expect);
	echo "  wrote $dstFile ($expect bytes)\n";
	$n++;
}

echo "Done. Squash mirror corpora: test_files105 … test_files132 (see run_benchmarks.php skip list). Optional: php benchmarks/build_test_files133_silesia12.php → test_files133/ (twelve Silesia files, one folder; see benchmarks/SILESIA_BENCHMARK.md).\n";
