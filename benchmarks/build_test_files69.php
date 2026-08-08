#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Build test_files69: stratified ≤5 MiB corpus echoing layouts/types from other test_files* corpora.
 * Run from repo root: php benchmarks/build_test_files69.php
 */

$root = dirname(__DIR__);
$dst = $root . DIRECTORY_SEPARATOR . 'test_files69';
$maxBytes = 5 * 1024 * 1024;

$plan = array(
	array('src' => $root . '/test_files52/n0000.txt', 'to' => '01_flat_numeric_series/n0000.txt'),
	array('src' => $root . '/test_files52/n0001.txt', 'to' => '01_flat_numeric_series/n0001.txt'),
	array('src' => $root . '/test_files52/n0002.txt', 'to' => '01_flat_numeric_series/n0002.txt'),
	array('src' => $root . '/test_files2/1.txt', 'to' => '01_flat_numeric_series/from_test_files2_1.txt'),
	array('src' => $root . '/test_files2/2.txt', 'to' => '01_flat_numeric_series/from_test_files2_2.txt'),
	array('src' => $root . '/test_files13/A000017.html', 'to' => '02_html_flat/A000017.html'),
	array('src' => $root . '/test_files13/A034607.html', 'to' => '02_html_flat/A034607.html'),
	array('src' => $root . '/test_files13/A072913.html', 'to' => '02_html_flat/A072913.html'),
	array('src' => $root . '/test_files13/A097738.html', 'to' => '03_nested_html/deep/sub/A097738.html'),
	array('src' => $root . '/test_files49/phpinfo.html', 'to' => '04_single_phpinfo/phpinfo.html'),
	array('src' => $root . '/test_files61/00_source_png/grid_01.png', 'to' => '05_raster_multi_format/grid_01.png'),
	array('src' => $root . '/test_files61/01_raster_formats/grid_01.gif', 'to' => '05_raster_multi_format/grid_01.gif'),
	array('src' => $root . '/test_files61/01_raster_formats/grid_01.webp', 'to' => '05_raster_multi_format/grid_01.webp'),
	array('src' => $root . '/test_files61/01_raster_formats/grid_01.jpg', 'to' => '05_raster_multi_format/grid_01.jpg'),
	array('src' => $root . '/test_files61/01_raster_formats/grid_01.bmp', 'to' => '05_raster_multi_format/grid_01.bmp'),
	array('src' => $root . '/test_files60/Daft Punk - Discovery (2001) [FLAC] 88/14. Too Long.flac', 'to' => '06_flac_with_sidecars/14_Too_Long.flac'),
	array('src' => $root . '/test_files60/Daft Punk - Discovery (2001) [FLAC] 88/Daft Punk - Discovery.m3u', 'to' => '06_flac_with_sidecars/Discovery.m3u'),
	array('src' => $root . '/test_files60/Daft Punk - Discovery (2001) [FLAC] 88/DR10.txt', 'to' => '06_flac_with_sidecars/DR10.txt'),
);

$sc2pick = null;
$sc2dirs = array(
	$root . '/test_files56_sample',
	$root . '/test_files56_sample/SC2Replay Archive',
	$root . '/test_files56/SC2Replay Archive',
);
foreach ($sc2dirs as $sc2dir) {
	if (!is_dir($sc2dir)) {
		continue;
	}
	foreach (scandir($sc2dir) ?: array() as $f) {
		if ($f === '.' || $f === '..') {
			continue;
		}
		$p = $sc2dir . DIRECTORY_SEPARATOR . $f;
		if (is_file($p) && preg_match('/\.sc2replay$/i', $f)) {
			$sc2pick = $p;
			break 2;
		}
	}
}
if ($sc2pick !== null) {
	$plan[] = array('src' => $sc2pick, 'to' => '07_sc2replay_nested/SC2Replay Archive/sample.SC2Replay');
}

$gzDir = $root . '/test_files62/peel_gz_dupes';
if (is_dir($gzDir)) {
	foreach (scandir($gzDir) ?: array() as $f) {
		if ($f === '.' || $f === '..') {
			continue;
		}
		if (!str_ends_with($f, '.gz')) {
			continue;
		}
		$plan[] = array('src' => $gzDir . DIRECTORY_SEPARATOR . $f, 'to' => '08_gzip_peel_dupes/' . $f);
	}
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
mkdir($dst, 0755, true);

$total = 0;
foreach ($plan as $item) {
	$src = $item['src'];
	$rel = $item['to'];
	if (!is_file($src)) {
		fwrite(STDERR, "skip missing: {$src}\n");
		continue;
	}
	$sz = filesize($src);
	if (!is_int($sz)) {
		continue;
	}
	if ($total + $sz > $maxBytes) {
		fwrite(STDERR, "skip (would exceed {$maxBytes} B): {$rel}\n");
		continue;
	}
	$out = $dst . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	$parent = dirname($out);
	if (!is_dir($parent)) {
		mkdir($parent, 0755, true);
	}
	if (!copy($src, $out)) {
		throw new RuntimeException('copy failed: ' . $src);
	}
	$total += $sz;
}

$final = 0;
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dst, FilesystemIterator::SKIP_DOTS));
foreach ($it as $f) {
	if ($f->isFile()) {
		$final += $f->getSize();
	}
}

echo "test_files69: {$dst}\n";
echo "total bytes: {$final} (cap {$maxBytes})\n";
