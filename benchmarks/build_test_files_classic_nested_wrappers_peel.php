#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build nested-wrapper peel corpora that historically yield large FZCL wins:
 *   test_files_classic_tarbz2_peel
 *   test_files_classic_cpioxz_peel
 *   test_files_classic_cpiozst_peel
 *   test_files_classic_cpiobz2_peel
 *
 *   php benchmarks/build_test_files_classic_nested_wrappers_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);

require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';

$shared = '';
for ($i = 0; $i < 50; $i++) {
	$shared .= "Shared nested-wrapper peel prose {$i}: configs, scripts, firmware notes repeat across packs.\n";
}
$shared = str_repeat($shared, 12);

/**
 * @return list<array{name:string,data:string}>
 */
function nest_files(string $tag, string $shared, string $prefix = ''): array
{
	$files = [];
	for ($i = 1; $i <= 12; $i++) {
		$name = $prefix === ''
			? sprintf('%s_%02d.conf', $tag, $i)
			: sprintf('%s%s_%02d.conf', $prefix, $tag, $i);
		$files[] = [
			'name' => $name,
			'data' => "TAG={$tag}\n" . $shared . "UNIQUE={$tag}-{$i}\nEND={$tag}\n",
		];
	}
	return $files;
}

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

/** @param array{fmt:string,meta:string,ext:string,expand:callable,gate:string} $spec */
function build_corpus(string $repo, string $name, array $spec, string $shared): void
{
	$dest = $repo . DIRECTORY_SEPARATOR . $name;
	wipe_dir($dest);
	$classic = 0;
	foreach (['alpha', 'bravo', 'charlie'] as $tag) {
		$prefix = str_starts_with($spec['fmt'], 'cpio') ? 'etc/' : '';
		$files = nest_files($tag, $shared, $prefix);
		$blob = fractal_zip_classic_rebuild_archive($spec['fmt'], $spec['meta'], $files);
		if ($blob === null) {
			fwrite(STDERR, "{$spec['fmt']} rebuild failed for {$tag}\n");
			exit(1);
		}
		$file = "{$dest}/{$tag}.{$spec['ext']}";
		file_put_contents($file, $blob);
		$m = [];
		$r = [];
		$bn = basename($file);
		if (($spec['expand'])($bn, $blob, $m, $r)
			&& (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
			&& ($r[$bn]['format'] ?? '') === $spec['fmt']) {
			$classic++;
		}
	}
	file_put_contents("$dest/00_README.txt", "classic {$spec['fmt']} peel — nested shared English\n");
	$total = 0;
	foreach (scandir($dest) ?: [] as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$sz = (int) filesize("$dest/$e");
		$total += $sz;
		echo sprintf("[%s] %8d  %s\n", $name, $sz, $e);
	}
	echo "[{$name}] total_raw={$total} classic={$classic}\n";
}

$specs = [
	'test_files_classic_tarbz2_peel' => [
		'fmt' => 'tarbz2',
		'meta' => '6',
		'ext' => 'tar.bz2',
		'expand' => 'fractal_zip_folder_try_expand_compress',
		'gate' => 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL',
	],
	'test_files_classic_cpioxz_peel' => [
		'fmt' => 'cpioxz',
		'meta' => '6',
		'ext' => 'cpio.xz',
		'expand' => 'fractal_zip_folder_try_expand_compress',
		'gate' => 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL',
	],
	'test_files_classic_cpiozst_peel' => [
		'fmt' => 'cpiozst',
		'meta' => '3n',
		'ext' => 'cpio.zst',
		'expand' => 'fractal_zip_folder_try_expand_compress',
		'gate' => 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL',
	],
	'test_files_classic_cpiobz2_peel' => [
		'fmt' => 'cpiobz2',
		'meta' => '6',
		'ext' => 'cpio.bz2',
		'expand' => 'fractal_zip_folder_try_expand_compress',
		'gate' => 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL',
	],
	'test_files_classic_tarlz4_peel' => [
		'fmt' => 'tarlz4',
		'meta' => '3',
		'ext' => 'tar.lz4',
		'expand' => 'fractal_zip_folder_try_expand_compress',
		'gate' => 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL',
	],
	'test_files_classic_tarbr_peel' => [
		'fmt' => 'tarbr',
		'meta' => '6',
		'ext' => 'tar.br',
		'expand' => 'fractal_zip_folder_try_expand_compress',
		'gate' => 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL',
	],
	'test_files_classic_cpiobr_peel' => [
		'fmt' => 'cpiobr',
		'meta' => '6',
		'ext' => 'cpio.br',
		'expand' => 'fractal_zip_folder_try_expand_compress',
		'gate' => 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL',
	],
];

foreach ($specs as $name => $spec) {
	$spec['expand'] = $spec['expand'];
	build_corpus($repo, $name, $spec, $shared);
}
