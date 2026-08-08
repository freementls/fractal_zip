#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_ole_jackpot_peel`: shared OLE/CFB docs wrapped as
 * bz2/xz/zst/lz4/br/gzip so CLASSIC compress peel + dedupe wins hard.
 *
 *   php benchmarks/build_test_files_compress_ole_jackpot_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_ole_jackpot_peel';
$basePath = $repo . '/test_files_classic_ole_shared_peel/demo-alpha.doc';
if (!is_file($basePath)) {
	$basePath = $repo . '/test_files72/converting files to pdf-20070424.doc';
}

if (!is_file($basePath)) {
	fwrite(STDERR, "missing base OLE\n");
	exit(1);
}

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
require_once $repo . '/fractal_zip_ole_cfb.php';

$base = file_get_contents($basePath);
$parsed = fractal_zip_ole_build_template_and_streams($base);
if ($parsed === null) {
	fwrite(STDERR, "OLE parse failed\n");
	exit(1);
}

$shared = '';
for ($i = 0; $i < 30; $i++) {
	$shared .= "Shared OLE-compress jackpot prose {$i} repeats.\n";
}
$shared = str_repeat($shared, 6);

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$streams = [];
	foreach ($parsed['streams'] as $st) {
		$need = strlen((string) $st['data']);
		$fill = "TAG={$tag}\n" . $shared . "U={$tag}\n";
		if (strlen($fill) < $need) {
			$fill = str_pad($fill, $need, "\0");
		}
		$streams[] = [
			'name' => (string) $st['name'],
			'data' => substr($fill, 0, $need),
			'ranges' => $st['ranges'],
		];
	}
	$ole = fractal_zip_ole_rebuild_from_template((string) $parsed['template'], $streams);
	if ($ole === null) {
		fwrite(STDERR, "OLE rebuild failed {$tag}\n");
		exit(1);
	}
	$wraps = [
		'bz2' => fractal_zip_folder_bzip2_shell_compress($ole, 6),
		'xz' => fractal_zip_folder_xz_shell_compress($ole, 6),
		'zst' => fractal_zip_folder_zstd_shell_compress($ole, 3, true),
		'lz4' => fractal_zip_folder_lz4_shell_compress($ole, 3),
		'br' => fractal_zip_folder_brotli_shell_compress($ole, 6),
		'gz' => (static function (string $b): ?string {
			$c = @gzencode($b, 6);
			return is_string($c) && $c !== '' ? $c : null;
		})($ole),
	];
	foreach ($wraps as $ext => $blob) {
		if ($blob === null) {
			fwrite(STDERR, "wrap failed {$tag}.{$ext}\n");
			exit(1);
		}
		$path = "{$dest}/{$tag}.doc.{$ext}";
		file_put_contents($path, $blob);
		$m = [];
		$r = [];
		$bn = basename($path);
		$ok = $ext === 'gz'
			? fractal_zip_folder_try_expand_gzip($bn, $blob, $m, $r)
			: fractal_zip_folder_try_expand_compress($bn, $blob, $m, $r);
		if ($ok && (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$classic++;
		}
	}
}

file_put_contents("$dest/00_README.txt", "OLE compress jackpot — doc×bz2/xz/zst/lz4/br/gz CLASSIC\n");

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
