#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_peel`: classic game archives (WAD/PAK/GRP/HOG) with many
 * small unique English lumps so peel+rebuild beats opaque container coding
 * (directory-table tax dominates after compression).
 *
 *   php benchmarks/build_test_files_classic_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_peel';

if (!is_dir($dest)) {
	mkdir($dest, 0755, true);
}
foreach (scandir($dest) ?: [] as $e) {
	if ($e === '.' || $e === '..') {
		continue;
	}
	@unlink($dest . DIRECTORY_SEPARATOR . $e);
}

/** Distinct short English blob keyed by seed (stable, unique across lumps). */
function classic_blob(string $seed, int $minLen = 96): string
{
	$line = "Classic peel lump {$seed}: wall textures, slipgates, sector effects, and mission briefings keep each entry unique. ";
	$out = '';
	$i = 0;
	while (strlen($out) < $minLen) {
		$out .= $line . 'n=' . $i . ".\n";
		$i++;
	}
	return $out;
}

/** @param list<array{name: string, data: string}> $lumps */
function classic_build_wad(string $path, string $id, array $lumps): void
{
	$payloads = '';
	$entries = [];
	foreach ($lumps as $l) {
		$entries[] = ['name' => $l['name'], 'off' => 12 + strlen($payloads), 'data' => $l['data']];
		$payloads .= $l['data'];
	}
	$tableOff = 12 + strlen($payloads);
	$dir = '';
	foreach ($entries as $e) {
		$dir .= pack('V', $e['off']) . pack('V', strlen($e['data']))
			. substr(str_pad($e['name'], 8, "\0"), 0, 8);
	}
	file_put_contents($path, $id . pack('V', count($entries)) . pack('V', $tableOff) . $payloads . $dir);
}

/** @param list<array{name: string, data: string}> $files */
function classic_build_pak(string $path, array $files): void
{
	$payload = '';
	$dir = '';
	foreach ($files as $f) {
		$off = 12 + strlen($payload);
		$payload .= $f['data'];
		$dir .= substr(str_pad($f['name'], 56, "\0"), 0, 56)
			. pack('V', $off) . pack('V', strlen($f['data']));
	}
	file_put_contents($path, 'PACK' . pack('V', 12 + strlen($payload)) . pack('V', strlen($dir)) . $payload . $dir);
}

/** @param list<array{name: string, data: string}> $files */
function classic_build_grp(string $path, array $files): void
{
	$bin = pack('V', count($files));
	$payload = '';
	foreach ($files as $f) {
		$bin .= pack('V', strlen($f['data'])) . substr(str_pad($f['name'], 12, "\0"), 0, 12);
		$payload .= $f['data'];
	}
	file_put_contents($path, $bin . $payload);
}

/** @param list<array{name: string, data: string}> $files */
function classic_build_hog(string $path, array $files): void
{
	$bin = '';
	foreach ($files as $f) {
		$bin .= substr(str_pad($f['name'], 13, "\0"), 0, 13)
			. pack('V', strlen($f['data']))
			. $f['data'];
	}
	file_put_contents($path, $bin);
}

$wadLumps = [];
for ($i = 1; $i <= 48; $i++) {
	$wadLumps[] = [
		'name' => sprintf('L%02d', $i),
		'data' => classic_blob('wad-' . $i, 80),
	];
}
$wadLumps[] = ['name' => 'NOISE', 'data' => random_bytes(256)];
classic_build_wad($dest . '/demo.wad', 'PWAD', $wadLumps);

$pakFiles = [];
for ($i = 1; $i <= 36; $i++) {
	$pakFiles[] = [
		'name' => sprintf('maps/m%02d.txt', $i),
		'data' => classic_blob('pak-' . $i, 88),
	];
}
$pakFiles[] = ['name' => 'sound/fx.raw', 'data' => random_bytes(192)];
classic_build_pak($dest . '/pak0.pak', $pakFiles);

$grpFiles = [];
for ($i = 1; $i <= 32; $i++) {
	$grpFiles[] = [
		'name' => sprintf('A%02d.CON', $i),
		'data' => classic_blob('grp-' . $i, 72),
	];
}
$grpFiles[] = ['name' => 'LOOKUP.DAT', 'data' => random_bytes(128)];
classic_build_grp($dest . '/duke.grp', $grpFiles);

$hogFiles = [];
for ($i = 1; $i <= 28; $i++) {
	$hogFiles[] = [
		'name' => sprintf('lv%02d.rdl', $i),
		'data' => classic_blob('hog-' . $i, 84),
	];
}
classic_build_hog($dest . '/descent.hog', $hogFiles);

file_put_contents($dest . '/00_README.txt', "classic peel corpus — many small unique English lumps (directory-table tax)\n");

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
