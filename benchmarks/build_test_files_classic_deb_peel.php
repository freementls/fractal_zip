#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_deb_peel`: deterministic .deb (GNU ar of
 * debian-binary + classic ustar control/data) with shared English across
 * packages so CLASSIC AR peel + FZCL beats opaque coding.
 *
 *   php benchmarks/build_test_files_classic_deb_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_deb_peel';

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';

$shared = '';
for ($i = 0; $i < 40; $i++) {
	$shared .= "Shared deb peel prose {$i}: package docs and scripts repeat across debs.\n";
}
$shared = str_repeat($shared, 8);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$control = fractal_zip_classic_rebuild_archive('tar', '', [
		[
			'name' => 'control',
			'data' => "Package: demo-{$tag}\nVersion: 1.0-1\nArchitecture: all\nDescription: peel demo {$tag}\n",
		],
		[
			'name' => 'md5sums',
			'data' => "placeholder {$tag}\n",
		],
	]);
	$dataFiles = [];
	for ($i = 1; $i <= 10; $i++) {
		$dataFiles[] = [
			'name' => sprintf('usr/share/doc/demo-%s/n%02d.txt', $tag, $i),
			'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
		];
	}
	$data = fractal_zip_classic_rebuild_archive('tar', '', $dataFiles);
	if ($control === null || $data === null) {
		fwrite(STDERR, "tar rebuild failed for {$tag}\n");
		exit(1);
	}
	// Uncompressed .tar members keep names ≤16 with GNU trailing slash.
	$deb = fractal_zip_classic_rebuild_archive('ar', '', [
		['name' => 'debian-binary', 'data' => "2.0\n"],
		['name' => 'control.tar', 'data' => $control],
		['name' => 'data.tar', 'data' => $data],
	]);
	if ($deb === null) {
		fwrite(STDERR, "ar/deb rebuild failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("{$dest}/demo-{$tag}.deb", $deb);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_ar("demo-{$tag}.deb", $deb, $m, $r)
		&& (int) ($r["demo-{$tag}.deb"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic deb peel — deterministic ar(ustar) shared English across packages\n");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize("$dest/$e");
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total}\n";
echo "classic={$classic}\n";
