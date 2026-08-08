#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Minimal folder .fz round-trip vs strict SHA1 tree compare (same rules as run_benchmarks verify).
 *
 * Usage (repo root):
 *   php benchmarks/repro_folder_zip_verify.php <source_dir> [--max-files=N] [--segment=300]
 *
 * Copies up to N smallest regular files (by size, then path) into a temp tree, runs zip_folder + open_container,
 * prints mismatch count and first 15 differing relative paths. Exit 0 iff mismatch count is 0.
 *
 * Env: same as normal fractal_zip benches (e.g. FRACTAL_ZIP_SEGMENT_LENGTH, FRACTAL_ZIP_MULTIPASS, FRACTAL_ZIP_SPEED).
 * Does not run gzip/7z/min-ext baselines — verify only.
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$lib = $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
if (!is_file($lib)) {
	fwrite(STDERR, "Missing fractal_zip.php at {$lib}\n");
	exit(2);
}
require_once $lib;

if (function_exists('ini_set')) {
	$ml = getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT');
	if ($ml !== false && trim((string) $ml) !== '') {
		@ini_set('memory_limit', trim((string) $ml));
	} else {
		@ini_set('memory_limit', '2G');
	}
}

$argvRest = array_slice($argv, 1);
if ($argvRest === []) {
	fwrite(STDERR, "Usage: php benchmarks/repro_folder_zip_verify.php <source_dir> [--max-files=N] [--segment=300]\n");
	exit(2);
}
$srcArg = array_shift($argvRest);
$src = $srcArg[0] === '/' ? $srcArg : $repo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $srcArg);
$realSrc = realpath($src);
if ($realSrc === false || !is_dir($realSrc)) {
	fwrite(STDERR, "Not a directory: {$src}\n");
	exit(2);
}
$maxFiles = 0;
$seg = 300;
foreach ($argvRest as $a) {
	if (preg_match('/^--max-files=(\d+)$/', $a, $m)) {
		$maxFiles = max(0, (int) $m[1]);
	} elseif (preg_match('/^--segment=(\d+)$/', $a, $m)) {
		$seg = max(8, min(500000, (int) $m[1]));
	}
}

/** @return list<array{rel: string, full: string, size: int}> */
function repro_collect_files(string $dir): array
{
	$out = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	$prefix = strlen($dir);
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$full = $fi->getPathname();
		$rel = substr($full, $prefix);
		if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
			$rel = substr($rel, 1);
		}
		$rel = str_replace('\\', '/', $rel);
		$sz = (int) $fi->getSize();
		$out[] = ['rel' => $rel, 'full' => $full, 'size' => $sz];
	}
	usort($out, static function ($a, $b) {
		if ($a['size'] !== $b['size']) {
			return $a['size'] <=> $b['size'];
		}
		return strcmp($a['rel'], $b['rel']);
	});
	return $out;
}

function repro_remove_tree(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$p = $item->getPathname();
		if ($item->isDir()) {
			@rmdir($p);
		} else {
			@unlink($p);
		}
	}
	@rmdir($dir);
}

function repro_copy_dir(string $srcDir, string $dstDir, int $maxFiles): int
{
	if (!is_dir($dstDir)) {
		mkdir($dstDir, 0755, true);
	}
	$files = repro_collect_files($srcDir);
	if ($maxFiles > 0) {
		$files = array_slice($files, 0, $maxFiles);
	}
	foreach ($files as $e) {
		$rel = $e['rel'];
		$dst = $dstDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$parent = dirname($dst);
		if (!is_dir($parent)) {
			mkdir($parent, 0755, true);
		}
		if (!@copy($e['full'], $dst)) {
			throw new RuntimeException('copy failed: ' . $e['full']);
		}
	}
	return count($files);
}

/** @return array<string, string> rel => sha1 */
function repro_tree_hashes(string $dir): array
{
	$out = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	$prefix = strlen($dir);
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$full = $fi->getPathname();
		$rel = substr($full, $prefix);
		if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
			$rel = substr($rel, 1);
		}
		$rel = str_replace('\\', '/', $rel);
		if (str_ends_with(strtolower($rel), '.fz')) {
			continue;
		}
		$h = sha1_file($full);
		if ($h !== false) {
			$out[$rel] = $h;
		}
	}
	ksort($out);
	return $out;
}

function repro_mismatch_detail(array $srcH, array $dstH, int $limit): array
{
	$keys = array_fill_keys(array_merge(array_keys($srcH), array_keys($dstH)), true);
	$rows = [];
	foreach (array_keys($keys) as $k) {
		$a = $srcH[$k] ?? null;
		$b = $dstH[$k] ?? null;
		if ($a === $b) {
			continue;
		}
		if (count($rows) >= $limit) {
			break;
		}
		if ($a === null) {
			$rows[] = $k . "\tonly_in_extract";
		} elseif ($b === null) {
			$rows[] = $k . "\tonly_in_source";
		} else {
			$rows[] = $k . "\thash_diff";
		}
	}
	return $rows;
}

$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzrepro_' . bin2hex(random_bytes(6));
$work = $td . DIRECTORY_SEPARATOR . 'src';
try {
	$n = repro_copy_dir($realSrc, $work, $maxFiles);
	fwrite(STDOUT, "Source: {$realSrc}\nCopied files: {$n} (max-files=" . ($maxFiles > 0 ? (string) $maxFiles : 'all') . ")\n");
	fwrite(STDOUT, "segment_length={$seg} multipass=on (pass null,true,true,null,true to fractal_zip)\n");

	$fzc = $work . '.fz';
	if (is_file($fzc)) {
		unlink($fzc);
	}
	$fz = new fractal_zip($seg, true, true, null, true);
	ob_start();
	$fz->zip_folder($work, false);
	ob_end_clean();
	if (!is_file($fzc)) {
		throw new RuntimeException('zip_folder did not create ' . $fzc);
	}
	$fzcSize = filesize($fzc);
	fwrite(STDOUT, '.fz bytes: ' . (string) ($fzcSize !== false ? $fzcSize : -1) . "\n");

	$ex = $td . DIRECTORY_SEPARATOR . 'extracted';
	mkdir($ex, 0755, true);
	$exFzc = $ex . DIRECTORY_SEPARATOR . basename($fzc);
	copy($fzc, $exFzc);
	$fz2 = new fractal_zip($seg, true, true, null, true);
	ob_start();
	$fz2->open_container($exFzc, false);
	ob_end_clean();

	$srcH = repro_tree_hashes($work);
	$dstH = repro_tree_hashes($ex);
	$mismatch = 0;
	foreach (array_merge(array_keys($srcH), array_keys($dstH)) as $k) {
		$a = $srcH[$k] ?? null;
		$b = $dstH[$k] ?? null;
		if ($a !== $b) {
			$mismatch++;
		}
	}
	fwrite(STDOUT, "verify_mismatch_files: {$mismatch}\n");
	foreach (repro_mismatch_detail($srcH, $dstH, 15) as $line) {
		fwrite(STDOUT, $line . "\n");
	}
	exit($mismatch === 0 ? 0 : 1);
} finally {
	repro_remove_tree($td);
}
