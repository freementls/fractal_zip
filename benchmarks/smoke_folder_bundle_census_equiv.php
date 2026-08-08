#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Regression: folder_bundle_census_from_raw_map must match accumulate+pack (collect path).
 *
 * From repo root: php benchmarks/smoke_folder_bundle_census_equiv.php
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_cli_opcache_bootstrap.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_folder_census.php';
require_once $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

/**
 * @param array<string,string> $out
 */
function smoke_folder_bundle_census_assert_equiv(array $out, string $label): void {
	$c1 = fractal_zip::folder_bundle_census_from_raw_map($out);
	$extBytes = array();
	$textish = 0;
	$totalBytes = 0;
	foreach ($out as $rel => $bytes) {
		fractal_zip::folder_bundle_census_accumulate_raw_file((string) $rel, (string) $bytes, $extBytes, $textish, $totalBytes);
	}
	$c2 = fractal_zip::folder_bundle_census_pack($extBytes, $totalBytes, $textish, count($out));
	foreach (array('files', 'bytes', 'textish_bytes', 'textish_ratio', 'top_ext_bytes') as $k) {
		if (!array_key_exists($k, $c1) || !array_key_exists($k, $c2)) {
			fwrite(STDERR, "smoke_folder_bundle_census_equiv: missing key {$k} ({$label})\n");
			exit(1);
		}
	}
	$j1 = bench_json_encode_try($c1, false);
	$j2 = bench_json_encode_try($c2, false);
	if ($j1 === null || $j2 === null || $j1 !== $j2) {
		fwrite(STDERR, "smoke_folder_bundle_census_equiv: JSON mismatch ({$label})\n");
		fwrite(STDERR, (string) $j1 . "\n" . (string) $j2 . "\n");
		exit(1);
	}
}

smoke_folder_bundle_census_assert_equiv(array(), 'empty');

smoke_folder_bundle_census_assert_equiv(array(
	'only_empty.dat' => '',
), 'single_zero_byte_file');

smoke_folder_bundle_census_assert_equiv(array(
	'docs/π/note.txt' => "pi\n",
), 'utf8_path_segment');

$out = array(
	'readme.txt' => "hello\n",
	'a/b/style.css' => 'x',
	'c/d/page.html' => str_repeat('y', 99),
	'e/noext' => 'z',
);
for ($i = 0; $i < 40; $i++) {
	$out[sprintf('mix/%02d.%03d', $i % 5, $i)] = str_repeat('w', ($i % 17) + 1);
}
smoke_folder_bundle_census_assert_equiv($out, 'mixed_many_ext');

$censusB = array('files' => 2, 'bytes' => 9, 'textish_bytes' => 0, 'textish_ratio' => 0.0, 'top_ext_bytes' => array());
$base = array('folder_bundle_census' => null);
$rows = array(
	array('folder_bundle_census' => null),
	array('folder_bundle_census' => $censusB),
);
bench_folder_bundle_census_merge_into_aggregated_row($rows, $base);
if (!isset($base['folder_bundle_census']['files']) || (int) $base['folder_bundle_census']['files'] !== 2) {
	fwrite(STDERR, "smoke_folder_bundle_census_equiv: merge fallback failed\n");
	exit(1);
}
$baseFirst = array('folder_bundle_census' => array('files' => 1, 'bytes' => 3, 'textish_bytes' => 0, 'textish_ratio' => 0.0, 'top_ext_bytes' => array()));
bench_folder_bundle_census_merge_into_aggregated_row($rows, $baseFirst);
if ((int) $baseFirst['folder_bundle_census']['files'] !== 1) {
	fwrite(STDERR, "smoke_folder_bundle_census_equiv: merge should keep first-row census\n");
	exit(1);
}

$baseNone = array('label' => 'q');
$rowsNone = array(
	array('zip_seconds' => 1.0),
	array('zip_seconds' => 2.0),
);
bench_folder_bundle_census_merge_into_aggregated_row($rowsNone, $baseNone);
if (array_key_exists('folder_bundle_census', $baseNone)) {
	fwrite(STDERR, "smoke_folder_bundle_census_equiv: merge should not add census when no row has it\n");
	exit(1);
}

echo "OK smoke_folder_bundle_census_equiv\n";
