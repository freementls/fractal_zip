#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Quick matrix: strict byte-identical convert round-trip on known bench corpora.
 * Does not run fractal_zip encode — convert hop only.
 *
 *   php benchmarks/probe_convert_strict_roundtrips.php
 */
$repo = dirname(__DIR__);
$convertRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'convert';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';

/** @return list<array{corpus: string, glob: string, forward: string, reverse: string}> */
function probe_cases(): array
{
	return [
		['corpus' => 'test_files53', 'glob' => '*.csv', 'forward' => 'csv_to_json', 'reverse' => 'json_to_csv'],
		['corpus' => 'test_files53', 'glob' => '*.csv', 'forward' => 'csv_to_xlsx', 'reverse' => 'xlsx_to_csv'],
		['corpus' => 'test_files166', 'glob' => '*', 'forward' => 'xlsx_to_csv', 'reverse' => 'csv_to_xlsx'],
		['corpus' => 'test_files167', 'glob' => '*', 'forward' => 'csv_to_xlsx', 'reverse' => 'xlsx_to_csv'],
		['corpus' => 'test_files177', 'glob' => '*.png', 'forward' => 'png_to_bmp', 'reverse' => 'bmp_to_png'],
		['corpus' => 'test_files61/01_raster_formats', 'glob' => '*.bmp', 'forward' => 'bmp_to_png', 'reverse' => 'png_to_bmp'],
		['corpus' => 'test_files61/01_raster_formats', 'glob' => '*.gif', 'forward' => 'gif_to_png', 'reverse' => 'png_to_gif'],
		['corpus' => 'test_files61/03_tarballs', 'glob' => '*.zip', 'forward' => 'zip_to_7z', 'reverse' => '7z_to_zip'],
		['corpus' => 'test_files34', 'glob' => '*.bmp', 'forward' => 'bmp_to_png', 'reverse' => 'png_to_bmp'],
		['corpus' => 'test_files149', 'glob' => '*.zip', 'forward' => 'zip_to_7z', 'reverse' => '7z_to_zip'],
	];
}

printf("%-40s %6s %6s %6s %s\n", 'case', 'files', 'ok', 'bad', 'sample fail');
foreach (probe_cases() as $case) {
	$dir = $repo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $case['corpus']);
	if (!is_dir($dir)) {
		printf("%-40s %6s\n", $case['corpus'] . ' ' . $case['forward'], 'MISSING');
		continue;
	}
	$ok = 0;
	$bad = 0;
	$sample = '';
	$files = 0;
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $item) {
		if (!$item->isFile()) {
			continue;
		}
		$base = basename($item->getPathname());
		if ($case['glob'] !== '*' && !fnmatch($case['glob'], $base, FNM_CASEFOLD)) {
			continue;
		}
		$files++;
		$orig = (string) file_get_contents($item->getPathname());
		try {
			$mid = ActionRegistry::convert($case['forward'], $base, $orig);
			$back = ActionRegistry::convert($case['reverse'], 'x.' . $mid->extension, $mid->bytes);
			if ($orig === $back->bytes) {
				$ok++;
			} else {
				$bad++;
				if ($sample === '') {
					$sample = $base . ' (' . strlen($orig) . '→' . strlen($back->bytes) . ')';
				}
			}
		} catch (Throwable $e) {
			$bad++;
			if ($sample === '') {
				$sample = $base . ' ERR';
			}
		}
	}
	printf(
		"%-40s %6d %6d %6d %s\n",
		$case['corpus'] . ' ' . $case['forward'],
		$files,
		$ok,
		$bad,
		$sample
	);
}

echo "\nStrict === bytes only. Semantic RoundTripCompare matches are not counted.\n";
