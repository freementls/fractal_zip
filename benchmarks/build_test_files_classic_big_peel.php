#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_big_peel`: EA BIGF archive with many small unique
 * English lumps so classic peel + FZCL beats opaque container coding.
 *
 *   php benchmarks/build_test_files_classic_big_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_big_peel';

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

function classic_blob(string $seed, int $minLen = 96): string
{
	$line = "EA BIG peel lump {$seed}: art assets, audio cues, and mission scripts stay unique. ";
	$out = '';
	$i = 0;
	while (strlen($out) < $minLen) {
		$out .= $line . 'n=' . $i . ".\n";
		$i++;
	}
	return $out;
}

/** @param list<array{name: string, data: string}> $files */
function classic_build_bigf(string $path, array $files): void
{
	$blob = '';
	$dir = '';
	foreach ($files as $f) {
		$name = str_replace('\\', '/', $f['name']);
		$data = $f['data'];
		$off = 12 + strlen($blob);
		$blob .= $data;
		$dir .= pack('V', $off) . pack('V', strlen($data)) . $name . "\0";
		$pad = (strlen($name) + 1) % 4;
		if ($pad !== 0) {
			$dir .= str_repeat("\0", 4 - $pad);
		}
	}
	$tableOff = 12 + strlen($blob);
	file_put_contents(
		$path,
		'BIGF' . pack('V', count($files)) . pack('V', $tableOff) . $blob . $dir
	);
}

$files = [];
for ($i = 1; $i <= 40; $i++) {
	$files[] = [
		'name' => sprintf('data/lump%02d.txt', $i),
		'data' => classic_blob('big-' . $i, 88),
	];
}
$files[] = ['name' => 'data/noise.bin', 'data' => random_bytes(128)];
classic_build_bigf($dest . '/assets.big', $files);

file_put_contents($dest . '/00_README.txt', "classic BIG peel — many small unique English lumps in BIGF\n");

$total = 0;
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	$sz = (int) filesize($dest . '/' . $e);
	$total += $sz;
	echo sprintf("%8d  %s\n", $sz, $e);
}
echo "total_raw={$total}\n";

// Self-check rebuild
require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';
$bytes = file_get_contents($dest . '/assets.big');
$m = [];
$r = [];
$ok = fractal_zip_folder_try_expand_classic('assets.big', $bytes, $m, $r);
echo 'peel=' . ($ok ? '1' : '0') . ' members=' . count($m) . "\n";
if ($ok) {
	$payloads = [];
	foreach ($r['assets.big']['member_names'] as $n) {
		$payloads[] = ['name' => $n, 'data' => $m['assets.big/' . $n]];
	}
	$reb = fractal_zip_classic_rebuild_archive('big', 'BIGF', $payloads);
	echo 'rebuild_ok=' . (($reb === $bytes) ? '1' : '0') . "\n";
}
