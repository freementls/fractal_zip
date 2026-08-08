#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_midi_shared_peel` and `test_files_classic_riff_shared_peel`:
 * multi-file MIDI / WAV with shared track/PCM so CLASSIC chunk peel beats VERBATIM.
 *
 *   php benchmarks/build_test_files_classic_chunk_shared_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);

require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';
require_once $repo . '/fractal_zip_chunk_container_peel.php';

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
 * @param list<string> $tracks
 */
function build_midi_smf(array $tracks): string
{
	$hdr = pack('n', 1) . pack('n', count($tracks)) . pack('n', 480);
	$out = 'MThd' . pack('N', 6) . $hdr;
	foreach ($tracks as $t) {
		$out .= 'MTrk' . pack('N', strlen($t)) . $t;
	}
	return $out;
}

function build_wav(string $pcm): string
{
	$fmt = pack('v', 1) . pack('v', 1) . pack('V', 8000) . pack('V', 8000) . pack('v', 1) . pack('v', 8);
	$chunks = 'fmt ' . pack('V', strlen($fmt)) . $fmt;
	if (strlen($fmt) & 1) {
		$chunks .= "\0";
	}
	$chunks .= 'data' . pack('V', strlen($pcm)) . $pcm;
	if (strlen($pcm) & 1) {
		$chunks .= "\0";
	}
	return 'RIFF' . pack('V', 4 + strlen($chunks)) . 'WAVE' . $chunks;
}

// --- MIDI ---
$midiDest = $repo . '/test_files_classic_midi_shared_peel';
wipe_dir($midiDest);
$sharedTrack = str_repeat("\x00\x90\x3c\x40\x00\x80\x3c\x40", 60);
$midiClassic = 0;
foreach (['song_a', 'song_b', 'song_c'] as $tag) {
	$tracks = [
		$sharedTrack . "HDR={$tag}",
		$sharedTrack . "MEL={$tag}",
	];
	$blob = build_midi_smf($tracks);
	// Prefer classic rebuild path for bit-exactness check
	$m = [];
	$r = [];
	$bn = "{$tag}.mid";
	file_put_contents("{$midiDest}/{$bn}", $blob);
	if (fractal_zip_folder_try_expand_chunk($bn, $blob, $m, $r)
		&& (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
		&& ($r[$bn]['format'] ?? '') === 'midi') {
		$midiClassic++;
	}
}
file_put_contents("$midiDest/00_README.txt", "classic MIDI shared peel — multi SMF shared tracks\n");

// --- RIFF WAV ---
$wavDest = $repo . '/test_files_classic_riff_shared_peel';
wipe_dir($wavDest);
$sharedPcm = str_repeat('ABCDEFGH', 120);
$riffClassic = 0;
foreach (['tone_a', 'tone_b', 'tone_c'] as $tag) {
	$pcm = $sharedPcm . "U={$tag}";
	$blob = build_wav($pcm);
	$bn = "{$tag}.wav";
	file_put_contents("{$wavDest}/{$bn}", $blob);
	$m = [];
	$r = [];
	if (fractal_zip_folder_try_expand_chunk($bn, $blob, $m, $r)
		&& (int) ($r[$bn]['kind'] ?? -1) === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC
		&& ($r[$bn]['format'] ?? '') === 'riff') {
		$riffClassic++;
	}
}
file_put_contents("$wavDest/00_README.txt", "classic RIFF/WAV shared peel — multi WAVE shared PCM\n");

foreach ([$midiDest => $midiClassic, $wavDest => $riffClassic] as $dir => $classic) {
	$total = 0;
	$name = basename($dir);
	foreach (scandir($dir) ?: [] as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$sz = (int) filesize("$dir/$e");
		$total += $sz;
		echo sprintf("[%s] %8d  %s\n", $name, $sz, $e);
	}
	echo "[{$name}] total_raw={$total} classic={$classic}\n";
}
