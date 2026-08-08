#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_woff2_jackpot_peel`: same TTF wrapped as
 * woff2 / bz2 / xz / zstd / lz4 / brotli / gzip so CLASSIC peel + dedupe wins.
 *
 * Source font: test_files54_sample maqiyahtimok woff2 (bit-exact woff2 roundtrip).
 *
 *   php benchmarks/build_test_files_compress_woff2_jackpot_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_woff2_jackpot_peel';
$src = $repo . '/test_files54_sample/maqiyahtimok/fonts/'
	. '6NUs8FyLNQOQZAnv9ZwNjucMHVn85Ni7emAe9lKqZTnbB-gzTK0K1ChJdt9vIVYX9G37lvd9mvIiQublWIIkfg.woff2';

if (!is_file($src)) {
	fwrite(STDERR, "missing source woff2: {$src}\n");
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

$tmpBase = sys_get_temp_dir() . '/fz_woff2_jack_' . getmypid();
@unlink($tmpBase . '.woff2');
@unlink($tmpBase . '.ttf');
file_put_contents($tmpBase . '.woff2', file_get_contents($src));
$dec = trim((string) shell_exec('command -v woff2_decompress 2>/dev/null'));
if ($dec === '') {
	fwrite(STDERR, "woff2_decompress not found\n");
	exit(1);
}
exec(escapeshellarg($dec) . ' ' . escapeshellarg($tmpBase . '.woff2') . ' 2>/dev/null', $_, $code);
@unlink($tmpBase . '.woff2');
if ($code !== 0 || !is_file($tmpBase . '.ttf')) {
	fwrite(STDERR, "woff2 decompress failed\n");
	exit(1);
}
$ttf = file_get_contents($tmpBase . '.ttf');
@unlink($tmpBase . '.ttf');
if (!is_string($ttf) || $ttf === '') {
	fwrite(STDERR, "empty ttf\n");
	exit(1);
}

$classic = 0;
foreach (['alpha', 'bravo', 'charlie'] as $tag) {
	$wraps = [
		'woff2' => fractal_zip_folder_woff2_shell_compress($ttf),
		'bz2' => fractal_zip_folder_bzip2_shell_compress($ttf, 6),
		'xz' => fractal_zip_folder_xz_shell_compress($ttf, 6),
		'zst' => fractal_zip_folder_zstd_shell_compress($ttf, 3, true),
		'lz4' => fractal_zip_folder_lz4_shell_compress($ttf, 3),
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
		$path = $ext === 'woff2'
			? "{$dest}/{$tag}.woff2"
			: "{$dest}/{$tag}.ttf.{$ext}";
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

file_put_contents("$dest/00_README.txt", "woff2+compress jackpot — same TTF many CLASSIC wrappers\n");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize("$dest/$e");
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total} classic={$classic} ttf=" . strlen($ttf) . "\n";
