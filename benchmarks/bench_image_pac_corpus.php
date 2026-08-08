<?php
declare(strict_types=1);

/**
 * Per-extension byte savings from fractal_zip_literal_pac_preprocess_literal_for_bundle (rasters + stream compressors).
 *
 *   php benchmarks/bench_image_pac_corpus.php [dir]
 * Default dir: test_files54_sample (repo root).
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dir = $argc >= 2 ? $argv[1] : $repo . DIRECTORY_SEPARATOR . 'test_files54_sample';
if ($dir[0] !== '/' && !str_starts_with($dir, $repo)) {
	$dir = $repo . DIRECTORY_SEPARATOR . $dir;
}
if (!is_dir($dir)) {
	fwrite(STDERR, "Not a directory: {$dir}\n");
	exit(2);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_image_pac.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';

$byExt = [];
$filesShrunk = 0;
$totalInShrunk = 0;
$totalSaved = 0;
$rasterFiles = 0;
$rasterBytes = 0;
$streamFiles = 0;
$streamBytes = 0;
$sameOrLarger = 0;

$streamFlip = array_fill_keys(fractal_zip_literal_pac_stream_extensions(), true);

$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $info) {
	if (!$info->isFile()) {
		continue;
	}
	$path = $info->getPathname();
	$raw = file_get_contents($path);
	if ($raw === false || $raw === '') {
		continue;
	}
	$rel = substr($path, strlen($dir) + 1);
	$rel = str_replace('\\', '/', $rel);
	$ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
	if (isset(FRACTAL_ZIP_IMAGEPAC_RASTER_EXT[$ext])) {
		$rasterFiles++;
		$rasterBytes += strlen($raw);
	}
	if (isset($streamFlip[$ext])) {
		$streamFiles++;
		$streamBytes += strlen($raw);
	}
	$before = strlen($raw);
	$after = strlen(fractal_zip_literal_pac_preprocess_literal_for_bundle($rel, $raw));
	$saved = $before - $after;
	if ((isset(FRACTAL_ZIP_IMAGEPAC_RASTER_EXT[$ext]) || isset($streamFlip[$ext])) && $saved <= 0) {
		$sameOrLarger++;
	}
	if ($saved <= 0) {
		continue;
	}
	if ($ext === '') {
		$ext = '(none)';
	}
	if (!isset($byExt[$ext])) {
		$byExt[$ext] = ['n' => 0, 'saved' => 0, 'in' => 0];
	}
	$byExt[$ext]['n']++;
	$byExt[$ext]['saved'] += $saved;
	$byExt[$ext]['in'] += $before;
	$filesShrunk++;
	$totalInShrunk += $before;
	$totalSaved += $saved;
}

$backend = fractal_zip_image_pac_backend();
echo 'backend=' . ($backend !== '' ? $backend : '(none)') . "\n";
echo 'ffmpeg_eligible=' . (fractal_zip_image_pac_ffmpeg_allowed() ? 'yes' : 'no') . "\n";
echo 'literalpac_stream=' . (fractal_zip_literal_pac_stream_enabled() ? 'on' : 'off') . "\n";
echo "dir={$dir}\n";
echo "raster_files={$rasterFiles} raster_bytes={$rasterBytes}\n";
echo "stream_files={$streamFiles} stream_bytes={$streamBytes}\n";
echo "pac_eligible_same_or_larger={$sameOrLarger} (raster or stream ext; no shrink)\n";
echo "files_shrunk={$filesShrunk} total_in_shrunk={$totalInShrunk} total_saved={$totalSaved}\n";
if ($byExt !== []) {
	ksort($byExt, SORT_STRING);
	foreach ($byExt as $ext => $row) {
		printf(
			"  .%s  n=%d  in=%d  saved=%d  pct=%.2f\n",
			$ext,
			$row['n'],
			$row['in'],
			$row['saved'],
			$row['in'] > 0 ? 100.0 * $row['saved'] / $row['in'] : 0.0
		);
	}
}
