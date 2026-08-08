#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build debxz / debzst peel corpora (control.tar.{xz,zst} + data.tar.{xz,zst}).
 *
 *   php benchmarks/build_test_files_classic_deb_wrapped_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';

$shared = '';
for ($i = 0; $i < 40; $i++) {
	$shared .= "Shared deb-wrapped peel prose {$i}: docs and scripts repeat across packages.\n";
}
$shared = str_repeat($shared, 8);

function wipe_dir(string $dest): void
{
	if (!is_dir($dest)) {
		mkdir($dest, 0755, true);
	}
	foreach (scandir($dest) ?: [] as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		@unlink($dest . DIRECTORY_SEPARATOR . $e);
	}
}

/**
 * @param array{fmt:string,meta:string,readme:string} $spec
 */
function build_deb_wrap(string $repo, string $dirName, array $spec, string $shared): void
{
	$dest = $repo . DIRECTORY_SEPARATOR . $dirName;
	wipe_dir($dest);
	$classic = 0;
	foreach (['alpha', 'bravo', 'charlie'] as $tag) {
		$payloads = [
			['name' => 'debian-binary', 'data' => "2.0\n"],
			[
				'name' => 'control/control',
				'data' => "Package: demo-{$tag}\nVersion: 1.0-1\nArchitecture: all\nDescription: peel {$tag}\n",
			],
			['name' => 'control/md5sums', 'data' => "placeholder {$tag}\n"],
		];
		for ($i = 1; $i <= 6; $i++) {
			$payloads[] = [
				'name' => sprintf('data/usr/share/doc/demo-%s/n%02d.txt', $tag, $i),
				'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
			];
		}
		$deb = fractal_zip_classic_rebuild_archive($spec['fmt'], $spec['meta'], $payloads);
		if ($deb === null) {
			fwrite(STDERR, "{$spec['fmt']} rebuild failed for {$tag}\n");
			exit(1);
		}
		file_put_contents("{$dest}/demo-{$tag}.deb", $deb);
		$m = [];
		$r = [];
		$bn = "demo-{$tag}.deb";
		if (fractal_zip_folder_try_expand_ar($bn, $deb, $m, $r)
			&& (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
			&& ($r[$bn]['format'] ?? '') === $spec['fmt']) {
			$classic++;
		}
	}
	file_put_contents("$dest/00_README.txt", $spec['readme'] . "\n");
	$total = 0;
	foreach (scandir($dest) ?: [] as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$sz = (int) filesize("$dest/$e");
		$total += $sz;
		echo sprintf("[%s] %8d  %s\n", $dirName, $sz, $e);
	}
	echo "[{$dirName}] total_raw={$total} classic={$classic}\n";
}

build_deb_wrap($repo, 'test_files_classic_debxz_peel', [
	'fmt' => 'debxz',
	'meta' => '6',
	'readme' => 'classic deb.xz peel — ar(control.tar.xz,data.tar.xz) nested CLASSIC',
], $shared);

build_deb_wrap($repo, 'test_files_classic_debzst_peel', [
	'fmt' => 'debzst',
	'meta' => '3n',
	'readme' => 'classic deb.zst peel — ar(control.tar.zst,data.tar.zst) nested CLASSIC',
], $shared);

build_deb_wrap($repo, 'test_files_classic_debbr_peel', [
	'fmt' => 'debbr',
	'meta' => '6',
	'readme' => 'classic deb.br peel — ar(control.tar.br,data.tar.br) nested CLASSIC',
], $shared);

build_deb_wrap($repo, 'test_files_classic_deblz4_peel', [
	'fmt' => 'deblz4',
	'meta' => '3',
	'readme' => 'classic deb.lz4 peel — ar(control.tar.lz4,data.tar.lz4) nested CLASSIC',
], $shared);

// Jackpot: mix several already-proven wrappers with the same shared English.
$jack = $repo . '/test_files_classic_wrapper_jackpot_peel';
wipe_dir($jack);
$sharedJ = $shared;
$files = [];
for ($i = 1; $i <= 8; $i++) {
	$files[] = [
		'name' => sprintf('f%02d.txt', $i),
		'data' => "JACK\n" . $sharedJ . "N={$i}\n",
	];
}
$cpioFiles = array_map(
	static fn(array $f): array => ['name' => 'etc/' . $f['name'], 'data' => $f['data']],
	$files
);
$blobs = [
	'pack.tar.bz2' => fractal_zip_classic_rebuild_archive('tarbz2', '6', $files),
	'pack.cpio.xz' => fractal_zip_classic_rebuild_archive('cpioxz', '6', $cpioFiles),
	'pack.tar.gz' => fractal_zip_classic_rebuild_archive('targz', '6', $files),
	'pack.cpio.bz2' => fractal_zip_classic_rebuild_archive('cpiobz2', '6', $cpioFiles),
	'pack.tar.br' => fractal_zip_classic_rebuild_archive('tarbr', '6', $files),
	'pack.cpio.br' => fractal_zip_classic_rebuild_archive('cpiobr', '6', $cpioFiles),
];
foreach ($blobs as $name => $blob) {
	if ($blob === null) {
		fwrite(STDERR, "jackpot rebuild failed: {$name}\n");
		exit(1);
	}
	file_put_contents("$jack/$name", $blob);
}
file_put_contents("$jack/00_README.txt", "wrapper jackpot — mixed nested CLASSIC wrappers shared English (+br)\n");
$total = 0;
foreach (scandir($jack) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize("$jack/$e");
	$total += $sz;
	echo sprintf("[jackpot] %8d  %s\n", $sz, $e);
}
echo "[jackpot] total_raw={$total}\n";
