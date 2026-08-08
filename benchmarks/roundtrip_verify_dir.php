#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Folder encode → extract → verify (bench rules: SHA1 tree + optional .zip semantic).
 * Does not run run_benchmarks.php.
 *
 * Usage: php benchmarks/roundtrip_verify_dir.php <source_dir> [--segment=300]
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
fractal_zip_ensure_folder_logical_bundle_loaded();

$ml = getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT');
if ($ml !== false && trim((string) $ml) !== '') {
	@ini_set('memory_limit', trim((string) $ml));
} else {
	@ini_set('memory_limit', '4G');
}

$srcArg = $argv[1] ?? '';
if ($srcArg === '' || $srcArg[0] === '-') {
	fwrite(STDERR, "Usage: php benchmarks/roundtrip_verify_dir.php <source_dir> [--segment=300]\n");
	exit(2);
}
$src = $srcArg[0] === '/' ? $srcArg : $repo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $srcArg);
$realSrc = realpath($src);
if ($realSrc === false || !is_dir($realSrc)) {
	fwrite(STDERR, "Not a directory: {$srcArg}\n");
	exit(2);
}
$seg = 300;
foreach (array_slice($argv, 2) as $a) {
	if (preg_match('/^--segment=(\d+)$/', $a, $m)) {
		$seg = max(8, min(500000, (int) $m[1]));
	}
}

function rt_tree_hashes(string $dir): array {
	$out = [];
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	$prefix = strlen($dir);
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$rel = substr($fi->getPathname(), $prefix);
		if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
			$rel = substr($rel, 1);
		}
		$rel = str_replace('\\', '/', $rel);
		if (str_ends_with(strtolower($rel), '.fz')) {
			continue;
		}
		$h = sha1_file($fi->getPathname());
		if ($h !== false) {
			$out[$rel] = $h;
		}
	}
	ksort($out);
	return $out;
}

function rt_count_mismatches(string $sourceDir, string $extractDir): int {
	$srcH = rt_tree_hashes($sourceDir);
	$dstH = rt_tree_hashes($extractDir);
	$keys = array_fill_keys(array_merge(array_keys($srcH), array_keys($dstH)), true);
	$mismatch = 0;
	foreach (array_keys($keys) as $k) {
		if (($srcH[$k] ?? null) === ($dstH[$k] ?? null)) {
			continue;
		}
		if (fractal_zip_folder_container_semantic_verify_enabled() && str_ends_with(strtolower($k), '.zip')) {
			$pa = $sourceDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			$pb = $extractDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			if (is_file($pa) && is_file($pb) && fractal_zip_folder_container_semantic_files_equal($pa, $pb)) {
				continue;
			}
		}
		$mismatch++;
	}
	return $mismatch;
}

$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzrtv_' . bin2hex(random_bytes(4));
$work = $td . DIRECTORY_SEPARATOR . 'src';
mkdir($work, 0755, true);
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($realSrc, FilesystemIterator::SKIP_DOTS));
$prefix = strlen($realSrc);
$n = 0;
foreach ($it as $fi) {
	if (!$fi->isFile()) {
		continue;
	}
	$rel = substr($fi->getPathname(), $prefix);
	if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
		$rel = substr($rel, 1);
	}
	$rel = str_replace('\\', '/', $rel);
	$dst = $work . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	$parent = dirname($dst);
	if (!is_dir($parent)) {
		mkdir($parent, 0755, true);
	}
	copy($fi->getPathname(), $dst);
	$n++;
}
fwrite(STDOUT, "Source: {$realSrc} ({$n} files)\n");
fwrite(STDOUT, 'baseline_tie=' . (fractal_zip_folder_baseline_tie_enabled() ? '1' : '0') . "\n");

$fzc = $work . '.fz';
$fz = new fractal_zip($seg, true, true, null, true);
ob_start();
$fz->zip_folder($work, false);
ob_end_clean();
if (!is_file($fzc)) {
	fwrite(STDERR, "FAIL: no .fz\n");
	exit(1);
}
$bytes = filesize($fzc);
fwrite(STDOUT, 'fzc_bytes=' . ($bytes !== false ? (string) $bytes : '?')
	. ' fzhm=' . (fractal_zip::$used_folder_per_member_best ? '1' : '0')
	. ' unified=' . (fractal_zip::$used_folder_unified_stream ? '1' : '0')
	. ' outer=' . (fractal_zip::$last_outer_codec ?? '?') . "\n");

$ex = $td . DIRECTORY_SEPARATOR . 'extracted';
mkdir($ex, 0755, true);
copy($fzc, $ex . DIRECTORY_SEPARATOR . basename($fzc));
$fz2 = new fractal_zip($seg, true, true, null, true);
ob_start();
$fz2->open_container($ex . DIRECTORY_SEPARATOR . basename($fzc), false);
ob_end_clean();

$mismatch = rt_count_mismatches($work, $ex);
$srcH = rt_tree_hashes($work);
$dstH = rt_tree_hashes($ex);
foreach (array_keys(array_fill_keys(array_merge(array_keys($srcH), array_keys($dstH)), true)) as $k) {
	if (($srcH[$k] ?? null) !== ($dstH[$k] ?? null)) {
		fwrite(STDOUT, "diff: {$k}\n");
	}
}
fwrite(STDOUT, "verify_mismatch_files={$mismatch}\n");
$rm = function (string $dir): void {
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
};
$rm($td);
exit($mismatch === 0 ? 0 : 1);
