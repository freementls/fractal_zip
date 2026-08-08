#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_debgz_peel`: deterministic .deb with gzipped
 * control.tar.gz + data.tar.gz and shared English across packages.
 *
 *   php benchmarks/build_test_files_classic_debgz_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_debgz_peel';

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
	$shared .= "Shared deb.gz peel prose {$i}: package docs and scripts repeat across debs.\n";
}
$shared = str_repeat($shared, 8);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$payloads = [
		['name' => 'debian-binary', 'data' => "2.0\n"],
		[
			'name' => 'control/control',
			'data' => "Package: demo-{$tag}\nVersion: 1.0-1\nArchitecture: all\nDescription: peel demo {$tag}\n",
		],
		['name' => 'control/md5sums', 'data' => "placeholder {$tag}\n"],
	];
	for ($i = 1; $i <= 10; $i++) {
		$payloads[] = [
			'name' => sprintf('data/usr/share/doc/demo-%s/n%02d.txt', $tag, $i),
			'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
		];
	}
	$deb = fractal_zip_classic_rebuild_archive('debgz', '6', $payloads);
	if ($deb === null) {
		fwrite(STDERR, "debgz rebuild failed for {$tag}\n");
		exit(1);
	}
	file_put_contents("{$dest}/demo-{$tag}.deb", $deb);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_ar("demo-{$tag}.deb", $deb, $m, $r)
		&& (int) ($r["demo-{$tag}.deb"]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
		&& ($r["demo-{$tag}.deb"]['format'] ?? '') === 'debgz') {
		$classic++;
	}
}

file_put_contents("$dest/00_README.txt", "classic deb.gz peel — ar(control.tar.gz,data.tar.gz) nested CLASSIC\n");

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
