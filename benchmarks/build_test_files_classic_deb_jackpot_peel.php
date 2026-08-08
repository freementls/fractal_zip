#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_deb_jackpot_peel`: same shared English as
 * debgz / debxz / debzst / debbr packages so CLASSIC nested peel wins hard.
 *
 *   php benchmarks/build_test_files_classic_deb_jackpot_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_deb_jackpot_peel';

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
for ($i = 0; $i < 30; $i++) {
	$shared .= "Shared deb-jackpot peel prose {$i}: docs scripts repeat across packages.\n";
}
$shared = str_repeat($shared, 5);

/**
 * @return list<array{name:string,data:string}>
 */
function deb_jackpot_payloads(string $tag, string $shared): array
{
	$p = [
		['name' => 'debian-binary', 'data' => "2.0\n"],
		[
			'name' => 'control/control',
			'data' => "Package: demo-{$tag}\nVersion: 1.0-1\nArchitecture: all\nDescription: peel {$tag}\n",
		],
		['name' => 'control/md5sums', 'data' => "placeholder {$tag}\n"],
	];
	for ($i = 1; $i <= 4; $i++) {
		$p[] = [
			'name' => sprintf('data/usr/share/doc/demo-%s/n%02d.txt', $tag, $i),
			'data' => "TAG={$tag}\n" . $shared . "U={$tag}-{$i}\n",
		];
	}
	return $p;
}

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$p = deb_jackpot_payloads($tag, $shared);
	foreach ([
		['debgz', '6', 'gz'],
		['debxz', '6', 'xz'],
		['debzst', '3n', 'zst'],
		['debbr', '6', 'br'],
	] as [$fmt, $meta, $label]) {
		$deb = fractal_zip_classic_rebuild_archive($fmt, $meta, $p);
		if ($deb === null) {
			fwrite(STDERR, "fail {$fmt} {$tag}\n");
			exit(1);
		}
		$name = "demo-{$tag}-{$label}.deb";
		file_put_contents("{$dest}/{$name}", $deb);
		$m = [];
		$r = [];
		if (fractal_zip_folder_try_expand_ar($name, $deb, $m, $r)
			&& (int) ($r[$name]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$classic++;
		}
	}
}

file_put_contents("$dest/00_README.txt", "deb jackpot — gz/xz/zst/br CLASSIC shared English\n");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize("$dest/$e");
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total} classic={$classic}\n";
