#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_compress_midi_jackpot_peel`: shared SMF tracks wrapped as
 * bz2/xz/zstd/lz4/br/lzma/gzip so nested midibz2/midixz/… CLASSIC peels win.
 *
 *   php benchmarks/build_test_files_compress_midi_jackpot_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_compress_midi_jackpot_peel';

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
require_once $repo . '/fractal_zip_chunk_container_peel.php';

function build_midi_smf(array $tracks): string
{
	$hdr = pack('n', 1) . pack('n', count($tracks)) . pack('n', 480);
	$out = 'MThd' . pack('N', 6) . $hdr;
	foreach ($tracks as $t) {
		$out .= 'MTrk' . pack('N', strlen($t)) . $t;
	}
	return $out;
}

// Large shared tracks so cross-wrapper FZCL beats opaque compress wrappers.
$sharedTrack = str_repeat("\x00\x90\x3c\x40\x00\x80\x3c\x40", 400);
$classic = 0;
$wantFmt = [
	'bz2' => 'midibz2',
	'xz' => 'midixz',
	'zst' => 'midizstd',
	'lz4' => 'midilz4',
	'br' => 'midibr',
	'lzma' => 'midilzma',
	'gz' => 'midigz',
];

foreach (['a', 'b', 'c'] as $tag) {
	$tracks = [
		$sharedTrack . "HDR={$tag}",
		$sharedTrack . "MEL={$tag}",
		$sharedTrack . "BAS={$tag}",
	];
	$midi = build_midi_smf($tracks);
	$wraps = [
		'bz2' => fractal_zip_folder_bzip2_shell_compress($midi, 6),
		'xz' => fractal_zip_folder_xz_shell_compress($midi, 6),
		'zst' => fractal_zip_folder_zstd_shell_compress($midi, 3, true),
		'lz4' => fractal_zip_folder_lz4_shell_compress($midi, 3),
		'br' => fractal_zip_folder_brotli_shell_compress($midi, 6),
		'lzma' => fractal_zip_folder_lzma_shell_compress($midi, 6),
		'gz' => (static function (string $b): ?string {
			$c = @gzencode($b, 6);
			return is_string($c) && $c !== '' ? $c : null;
		})($midi),
	];
	foreach ($wraps as $ext => $blob) {
		if ($blob === null) {
			fwrite(STDERR, "wrap failed song_{$tag}.mid.{$ext}\n");
			exit(1);
		}
		$bn = "song_{$tag}.mid.{$ext}";
		file_put_contents("{$dest}/{$bn}", $blob);
		$m = [];
		$r = [];
		$ok = ($ext === 'gz')
			? fractal_zip_folder_try_expand_gzip($bn, $blob, $m, $r)
			: fractal_zip_folder_try_expand_compress($bn, $blob, $m, $r);
		$fmt = (string) ($r[$bn]['format'] ?? '');
		if ($ok && (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
			&& $fmt === $wantFmt[$ext]) {
			$classic++;
		} else {
			fwrite(STDERR, "peel miss {$bn} ok=" . ($ok ? 1 : 0) . " fmt={$fmt} want={$wantFmt[$ext]}\n");
			exit(1);
		}
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
if ($classic < 21) {
	fwrite(STDERR, "expected 21 nested MIDI CLASSIC peels\n");
	exit(1);
}
