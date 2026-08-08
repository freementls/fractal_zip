#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_woff2_dual_jackpot_peel`: two bit-exact-roundtrip
 * TTFs each wrapped as woff2/bz2/xz/br/gzip for a large CLASSIC FZCL win.
 *
 *   php benchmarks/build_test_files_compress_woff2_dual_jackpot_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_woff2_dual_jackpot_peel';
$fontDir = $repo . '/test_files54_sample/maqiyahtimok/fonts';
$fonts = [
	'a' => $fontDir . '/6NUs8FyLNQOQZAnv9ZwNjucMHVn85Ni7emAe9lKqZTnbB-gzTK0K1ChJdt9vIVYX9G37lvd9mvIiQublWIIkfg.woff2',
	'b' => $fontDir . '/S6u8w4BMUTPHjxsAUi-qNiXg7eU0.woff2',
];

foreach ($fonts as $src) {
	if (!is_file($src)) {
		fwrite(STDERR, "missing {$src}\n");
		exit(1);
	}
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

$dec = trim((string) shell_exec('command -v woff2_decompress 2>/dev/null'));
if ($dec === '') {
	fwrite(STDERR, "woff2_decompress not found\n");
	exit(1);
}

$classic = 0;
foreach ($fonts as $tag => $src) {
	$tmp = sys_get_temp_dir() . '/fz_woff2_dual_' . $tag . '_' . getmypid();
	@unlink($tmp . '.woff2');
	@unlink($tmp . '.ttf');
	file_put_contents($tmp . '.woff2', file_get_contents($src));
	exec(escapeshellarg($dec) . ' ' . escapeshellarg($tmp . '.woff2') . ' 2>/dev/null', $_, $code);
	@unlink($tmp . '.woff2');
	if ($code !== 0 || !is_file($tmp . '.ttf')) {
		fwrite(STDERR, "decompress failed {$tag}\n");
		exit(1);
	}
	$ttf = file_get_contents($tmp . '.ttf');
	@unlink($tmp . '.ttf');
	if (!is_string($ttf) || $ttf === '') {
		fwrite(STDERR, "empty ttf {$tag}\n");
		exit(1);
	}
	$wraps = [
		'woff2' => fractal_zip_folder_woff2_shell_compress($ttf),
		'bz2' => fractal_zip_folder_bzip2_shell_compress($ttf, 6),
		'xz' => fractal_zip_folder_xz_shell_compress($ttf, 6),
		'br' => fractal_zip_folder_brotli_shell_compress($ttf, 6),
		'gz' => (static function (string $b): ?string {
			$c = @gzencode($b, 6);
			return is_string($c) && $c !== '' ? $c : null;
		})($ttf),
	];
	foreach ($wraps as $ext => $blob) {
		if ($blob === null) {
			fwrite(STDERR, "wrap failed {$tag}.{$ext}\n");
			exit(1);
		}
		$path = $ext === 'woff2' ? "{$dest}/{$tag}.woff2" : "{$dest}/{$tag}.ttf.{$ext}";
		file_put_contents($path, $blob);
		$m = [];
		$r = [];
		$bn = basename($path);
		$ok = match ($ext) {
			'woff2' => fractal_zip_folder_try_expand_woff($bn, $blob, $m, $r),
			'gz' => fractal_zip_folder_try_expand_gzip($bn, $blob, $m, $r),
			default => fractal_zip_folder_try_expand_compress($bn, $blob, $m, $r),
		};
		if ($ok && (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$classic++;
		}
	}
}

file_put_contents("$dest/00_README.txt", "woff2 dual-font jackpot — two TTFs × CLASSIC wrappers\n");

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
