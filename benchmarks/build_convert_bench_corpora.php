<?php
declare(strict_types=1);

/**
 * Build paired baseline/converted corpora for lossless convert × fractal_zip benchmarks.
 *
 *   php benchmarks/build_convert_bench_corpora.php
 *
 * Creates test_files151 … test_files170 under repo root (canonical names for run_benchmarks.php).
 * Manifest: benchmarks/convert_bench_manifest.json
 */
$repo = dirname(__DIR__);
$convertRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'convert';
if (!is_dir($convertRoot)) {
	fwrite(STDERR, "Convert app not found at {$convertRoot}\n");
	exit(1);
}
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';

/** @param list<string> $paths */
function cb_copy_tree(string $srcDir, string $dstDir, array $paths = []): void
{
	if (is_dir($dstDir)) {
		cb_rmtree($dstDir);
	}
	mkdir($dstDir, 0755, true);
	if ($paths === []) {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($it as $item) {
			$rel = substr($item->getPathname(), strlen($srcDir) + 1);
			$target = $dstDir . DIRECTORY_SEPARATOR . $rel;
			if ($item->isDir()) {
				if (!is_dir($target)) {
					mkdir($target, 0755, true);
				}
			} else {
				$parent = dirname($target);
				if (!is_dir($parent)) {
					mkdir($parent, 0755, true);
				}
				copy($item->getPathname(), $target);
			}
		}
		return;
	}
	foreach ($paths as $rel) {
		$src = $srcDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if (!is_file($src)) {
			throw new RuntimeException('missing source file: ' . $src);
		}
		$dst = $dstDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$parent = dirname($dst);
		if (!is_dir($parent)) {
			mkdir($parent, 0755, true);
		}
		copy($src, $dst);
	}
}

function cb_rmtree(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		if ($item->isDir()) {
			@rmdir($item->getPathname());
		} else {
			@unlink($item->getPathname());
		}
	}
	@rmdir($dir);
}

function cb_put_file(string $dir, string $rel, string $bytes): void
{
	$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	$parent = dirname($path);
	if (!is_dir($parent)) {
		mkdir($parent, 0755, true);
	}
	if (file_put_contents($path, $bytes) === false) {
		throw new RuntimeException('write failed: ' . $path);
	}
}

/** @return array{bytes: string, ext: string} */
function cb_convert(string $action, string $filename, string $bytes): array
{
	$result = ActionRegistry::convert($action, $filename, $bytes);
	return ['bytes' => $result->bytes, 'ext' => $result->extension ?: pathinfo($filename, PATHINFO_EXTENSION)];
}

/** @param list<array<string, mixed>> $files */
function cb_build_converted(string $srcDir, string $dstDir, string $action, array $files): void
{
	if (is_dir($dstDir)) {
		cb_rmtree($dstDir);
	}
	mkdir($dstDir, 0755, true);
	foreach ($files as $spec) {
		$rel = (string) $spec['rel'];
		$srcPath = $srcDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if (!is_file($srcPath)) {
			throw new RuntimeException('missing: ' . $srcPath);
		}
		$bytes = (string) file_get_contents($srcPath);
		$out = cb_convert($action, basename($rel), $bytes);
		$outRel = (string) ($spec['out_rel'] ?? preg_replace('/\.[^.]+$/', '.' . $out['ext'], $rel));
		cb_put_file($dstDir, $outRel, $out['bytes']);
	}
}

/** @param list<string> $globPatterns relative to srcDir */
function cb_glob_files(string $srcDir, array $globPatterns): array
{
	$files = [];
	foreach ($globPatterns as $pat) {
		foreach (glob($srcDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $pat)) ?: [] as $path) {
			if (!is_file($path)) {
				continue;
			}
			$rel = substr($path, strlen($srcDir) + 1);
			$files[] = ['rel' => str_replace(DIRECTORY_SEPARATOR, '/', $rel)];
		}
	}
	return $files;
}

$fzRoot = $repo;
$experiments = [];

function cb_case(string $repo, array &$experiments, int $baselineNum, int $convertedNum, string $action, string $note, ?string $parentLabel = null): void
{
	$experiments[] = [
		'id' => 'test_files' . $baselineNum . '_to_' . $convertedNum,
		'action' => $action,
		'baseline_label' => 'test_files' . $baselineNum,
		'converted_label' => 'test_files' . $convertedNum,
		'parent_label' => $parentLabel,
		'note' => $note,
	];
}

// 151–153: CSV (Silesia-style shop export from test_files53)
$csvSrc = $fzRoot . DIRECTORY_SEPARATOR . 'test_files53' . DIRECTORY_SEPARATOR . 'products_export_1.csv';
$csv151 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files151';
cb_rmtree($csv151);
mkdir($csv151, 0755, true);
copy($csvSrc, $csv151 . DIRECTORY_SEPARATOR . 'products_export_1.csv');
cb_case($repo, $experiments, 151, 152, 'csv_to_json', 'CSV → JSON (large expansion on wide CSV)');
cb_build_converted($csv151, $fzRoot . DIRECTORY_SEPARATOR . 'test_files152', 'csv_to_json', [
	['rel' => 'products_export_1.csv', 'out_rel' => 'products_export_1.json'],
]);
cb_case($repo, $experiments, 151, 153, 'csv_to_xlsx', 'CSV → XLSX (OOXML ZIP container)');
cb_build_converted($csv151, $fzRoot . DIRECTORY_SEPARATOR . 'test_files153', 'csv_to_xlsx', [
	['rel' => 'products_export_1.csv', 'out_rel' => 'products_export_1.xlsx'],
]);

// 154–155: PNG rasters → BMP (test_files61 source PNGs)
$png154 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files154';
cb_copy_tree($fzRoot . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '00_source_png', $png154);
$pngFiles = cb_glob_files($png154, ['*.png']);
cb_case($repo, $experiments, 154, 155, 'png_to_bmp', 'PNG → BMP (BMP delta literal modes)', 'test_files61');
cb_build_converted($png154, $fzRoot . DIRECTORY_SEPARATOR . 'test_files155', 'png_to_bmp', $pngFiles);

// 156–157: SVG icons → BMP
$svg156 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files156';
cb_rmtree($svg156);
mkdir($svg156, 0755, true);
foreach (glob($fzRoot . DIRECTORY_SEPARATOR . 'test_files74' . DIRECTORY_SEPARATOR . 'Pictures' . DIRECTORY_SEPARATOR . 'Icons' . DIRECTORY_SEPARATOR . '*.svg') ?: [] as $path) {
	copy($path, $svg156 . DIRECTORY_SEPARATOR . basename($path));
}
$svgFiles = cb_glob_files($svg156, ['*.svg']);
cb_case($repo, $experiments, 156, 157, 'svg_to_bmp', 'SVG → BMP (vector → raster for BMP transforms)', 'test_files74');
cb_build_converted($svg156, $fzRoot . DIRECTORY_SEPARATOR . 'test_files157', 'svg_to_bmp', $svgFiles);

// 158–159: ZIP → 7z
$zip158 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files158';
cb_rmtree($zip158);
mkdir($zip158, 0755, true);
copy(
	$fzRoot . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '03_tarballs' . DIRECTORY_SEPARATOR . 'raster_formats.zip',
	$zip158 . DIRECTORY_SEPARATOR . 'raster_formats.zip'
);
cb_case($repo, $experiments, 158, 159, 'zip_to_7z', 'ZIP → 7z (container normalize)', 'test_files61');
cb_build_converted($zip158, $fzRoot . DIRECTORY_SEPARATOR . 'test_files159', 'zip_to_7z', [
	['rel' => 'raster_formats.zip', 'out_rel' => 'raster_formats.7z'],
]);

// 160–161: 7z → ZIP
$seven160 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files160';
cb_rmtree($seven160);
mkdir($seven160, 0755, true);
copy(
	$fzRoot . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '03_tarballs' . DIRECTORY_SEPARATOR . 'raster_formats.7z',
	$seven160 . DIRECTORY_SEPARATOR . 'raster_formats.7z'
);
cb_case($repo, $experiments, 160, 161, '7z_to_zip', '7z → ZIP (semantic ZIP peel target)', 'test_files61');
cb_build_converted($seven160, $fzRoot . DIRECTORY_SEPARATOR . 'test_files161', '7z_to_zip', [
	['rel' => 'raster_formats.7z', 'out_rel' => 'raster_formats.zip'],
]);

// 162–163: tar.gz → ZIP
$targz162 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files162';
cb_rmtree($targz162);
mkdir($targz162, 0755, true);
copy(
	$fzRoot . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '03_tarballs' . DIRECTORY_SEPARATOR . 'raster_formats.tar.gz',
	$targz162 . DIRECTORY_SEPARATOR . 'raster_formats.tar.gz'
);
cb_case($repo, $experiments, 162, 163, 'targz_to_zip', 'tar.gz → ZIP', 'test_files61');
cb_build_converted($targz162, $fzRoot . DIRECTORY_SEPARATOR . 'test_files163', 'targz_to_zip', [
	['rel' => 'raster_formats.tar.gz', 'out_rel' => 'raster_formats.zip'],
]);

// 164–165: FLAC → WAV (test_files60 sidecar album)
$flac164 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files164';
cb_copy_tree($fzRoot . DIRECTORY_SEPARATOR . 'test_files60', $flac164);
$flacFiles = [];
$flacIt = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($flac164, FilesystemIterator::SKIP_DOTS)
);
foreach ($flacIt as $item) {
	if (!$item->isFile() || strtolower($item->getExtension()) !== 'flac') {
		continue;
	}
	$rel = substr($item->getPathname(), strlen($flac164) + 1);
	$flacFiles[] = ['rel' => str_replace(DIRECTORY_SEPARATOR, '/', $rel)];
}
if ($flacFiles === []) {
	throw new RuntimeException('no flac files under test_files164');
}
cb_case($repo, $experiments, 164, 165, 'flac_to_wav', 'FLAC → WAV (PCM layout change)', 'test_files60');
cb_build_converted($flac164, $fzRoot . DIRECTORY_SEPARATOR . 'test_files165', 'flac_to_wav', $flacFiles);

// 166–167: XLSX (from CSV) → CSV
$xlsx166 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files166';
cb_rmtree($xlsx166);
mkdir($xlsx166, 0755, true);
$xlsxBytes = (string) file_get_contents($csv151 . DIRECTORY_SEPARATOR . 'products_export_1.csv');
$xlsxOut = cb_convert('csv_to_xlsx', 'products_export_1.csv', $xlsxBytes);
cb_put_file($xlsx166, 'products_export_1.xlsx', $xlsxOut['bytes']);
cb_case($repo, $experiments, 166, 167, 'xlsx_to_csv', 'XLSX → CSV (strip OOXML overhead)', null);
cb_build_converted($xlsx166, $fzRoot . DIRECTORY_SEPARATOR . 'test_files167', 'xlsx_to_csv', [
	['rel' => 'products_export_1.xlsx', 'out_rel' => 'products_export_1.csv'],
]);

// 168–169: BMP → PNG (test_files61 raster BMPs — test_files10 uses nonstandard BMP headers)
$bmp168 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files168';
cb_copy_tree($fzRoot . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '01_raster_formats', $bmp168);
$bmpFiles = cb_glob_files($bmp168, ['*.bmp']);
cb_case($repo, $experiments, 168, 169, 'bmp_to_png', 'BMP → PNG (raster re-pack)', 'test_files61');
cb_build_converted($bmp168, $fzRoot . DIRECTORY_SEPARATOR . 'test_files169', 'bmp_to_png', $bmpFiles);

// 170: WAV → FLAC (from 165) — reverse hop for shrink
$wav170 = $fzRoot . DIRECTORY_SEPARATOR . 'test_files170';
cb_rmtree($wav170);
mkdir($wav170, 0755, true);
$wavFiles = [];
$wavIt = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($fzRoot . DIRECTORY_SEPARATOR . 'test_files165', FilesystemIterator::SKIP_DOTS)
);
foreach ($wavIt as $item) {
	if (!$item->isFile() || strtolower($item->getExtension()) !== 'wav') {
		continue;
	}
	$rel = substr($item->getPathname(), strlen($fzRoot . DIRECTORY_SEPARATOR . 'test_files165') + 1);
	$wavFiles[] = ['rel' => str_replace(DIRECTORY_SEPARATOR, '/', $rel)];
}
if ($wavFiles === []) {
	throw new RuntimeException('no wav files in test_files165');
}
cb_build_converted($fzRoot . DIRECTORY_SEPARATOR . 'test_files165', $wav170, 'wav_to_flac', $wavFiles);
$experiments[] = [
	'id' => 'test_files165_to_170',
	'action' => 'wav_to_flac',
	'baseline_label' => 'test_files165',
	'converted_label' => 'test_files170',
	'parent_label' => 'test_files164',
	'note' => 'WAV → FLAC (PCM re-pack after flac_to_wav)',
];

$manifest = [
	'generated_at' => gmdate('c'),
	'convert_root' => $convertRoot,
	'experiments' => $experiments,
	'corpus_labels' => array_values(array_unique(array_merge(
		array_column($experiments, 'baseline_label'),
		array_column($experiments, 'converted_label')
	))),
];
$manifestPath = __DIR__ . DIRECTORY_SEPARATOR . 'convert_bench_manifest.json';
file_put_contents($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo 'Built ' . count($manifest['corpus_labels']) . ' corpora (test_files151–170)' . "\n";
echo 'Manifest: benchmarks/convert_bench_manifest.json' . "\n";
foreach ($experiments as $e) {
	echo '  ' . $e['action'] . ': ' . $e['baseline_label'] . ' → ' . $e['converted_label'] . "\n";
}
