#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_pbo_peel`: Bohemia PBO (peel layout) with many small
 * unique English lumps so classic peel + FZCL beats opaque container coding.
 *
 *   php benchmarks/build_test_files_classic_pbo_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_pbo_peel';

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
	$line = "PBO peel lump {$seed}: mission sqf, configs, and briefing text stay unique. ";
	$out = '';
	$i = 0;
	while (strlen($out) < $minLen) {
		$out .= $line . 'n=' . $i . ".\n";
		$i++;
	}
	return $out;
}

/** @param list<array{name: string, data: string}> $files */
function classic_build_pbo(string $path, array $files): void
{
	$header = '';
	$blob = '';
	foreach ($files as $f) {
		$name = str_replace('\\', '/', $f['name']);
		$data = $f['data'];
		// name\0 + empty mime\0 + u32 size (matches peel_pbo_read_index)
		$header .= $name . "\0\0" . pack('V', strlen($data));
		$blob .= $data;
	}
	file_put_contents($path, $header . "\0" . $blob);
}

$files = [];
for ($i = 1; $i <= 80; $i++) {
	$files[] = [
		'name' => sprintf('mission/script%02d.sqf', $i),
		'data' => classic_blob('pbo-' . $i, 64),
	];
}
$files[] = ['name' => 'mission/noise.bin', 'data' => random_bytes(96)];
classic_build_pbo($dest . '/addon.pbo', $files);

file_put_contents($dest . '/00_README.txt', "classic PBO peel — many small unique English lumps\n");

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

require_once $repo . '/fractal_zip_folder_logical_bundle.php';
require_once $repo . '/fractal_zip_classic_peel.php';
$bytes = file_get_contents($dest . '/addon.pbo');
$m = [];
$r = [];
$ok = fractal_zip_folder_try_expand_classic('addon.pbo', $bytes, $m, $r);
echo 'peel=' . ($ok ? '1' : '0') . ' members=' . count($m) . "\n";
if ($ok) {
	$payloads = [];
	foreach ($r['addon.pbo']['member_names'] as $n) {
		$payloads[] = ['name' => $n, 'data' => $m['addon.pbo/' . $n]];
	}
	$reb = fractal_zip_classic_rebuild_archive('pbo', '', $payloads);
	echo 'rebuild_ok=' . (($reb === $bytes) ? '1' : '0') . "\n";
}
