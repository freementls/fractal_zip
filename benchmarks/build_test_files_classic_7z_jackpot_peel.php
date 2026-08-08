#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_7z_jackpot_peel`: shared English across sibling
 * deterministic 7z archives so CLASSIC peel + FZCL beats opaque.
 *
 *   php benchmarks/build_test_files_classic_7z_jackpot_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_7z_jackpot_peel';

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';
require_once $repo . '/fractal_zip_rpm_7z_peel.php';

$shared = '';
for ($i = 0; $i < 80; $i++) {
	$shared .= "Shared 7z jackpot peel prose {$i}: textures scripts briefings repeat across sibling archives.\n";
}
$shared = str_repeat($shared, 10);

$classic = 0;
$mx = '5';
foreach (['alpha', 'bravo', 'charlie', 'delta'] as $tag) {
	$payloads = [
		['name' => 'doc/a.txt', 'data' => $shared . "TAG={$tag}-a\n"],
		['name' => 'doc/b.txt', 'data' => $shared . "TAG={$tag}-b\n"],
		['name' => 'uniq.txt', 'data' => "unique {$tag} trailer\n"],
	];
	$blob = fractal_zip_classic_rebuild_archive('7z', $mx, $payloads);
	if ($blob === null || $blob === '' || substr($blob, 0, 6) !== "7z\xbc\xaf\x27\x1c") {
		fwrite(STDERR, "7z rebuild failed for {$tag}\n");
		exit(1);
	}
	$bn = "pack-{$tag}.7z";
	file_put_contents("{$dest}/{$bn}", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_7z($bn, $blob, $m, $r)
		&& (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
		$classic++;
	} else {
		fwrite(STDERR, "7z peel admission failed for {$bn}\n");
		exit(1);
	}
}

file_put_contents("$dest/r.txt", "x");

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
if ($classic < 4) {
	fwrite(STDERR, "expected 4 CLASSIC 7z peels\n");
	exit(1);
}
