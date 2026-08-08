#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files_classic_vpk_peel`: monolithic VPK v1 with many small unique
 * English lumps so classic peel + FZCL beats opaque container coding.
 *
 *   php benchmarks/build_test_files_classic_vpk_peel.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dest = $repo . DIRECTORY_SEPARATOR . 'test_files_classic_vpk_peel';

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

function classic_blob(string $seed, int $minLen = 80): string
{
	$line = "VPK peel lump {$seed}: materials, scripts, and sound cues stay unique. ";
	$out = '';
	$i = 0;
	while (strlen($out) < $minLen) {
		$out .= $line . 'n=' . $i . ".\n";
		$i++;
	}
	return $out;
}

$files = [];
for ($i = 1; $i <= 36; $i++) {
	$files[] = [
		'name' => sprintf('maps/m%02d.txt', $i),
		'data' => classic_blob('vpk-' . $i, 72),
	];
}
$files[] = ['name' => 'docs/help.txt', 'data' => classic_blob('vpk-help', 120)];
$files[] = ['name' => 'sound/fx.raw', 'data' => random_bytes(96)];

$blob = fractal_zip_classic_vpk_rebuild($files, 'v1');
if ($blob === null) {
	fwrite(STDERR, "vpk rebuild failed\n");
	exit(1);
}
file_put_contents($dest . '/pak01.vpk', $blob);
file_put_contents($dest . '/00_README.txt', "classic VPK peel — monolithic v1 many small unique English lumps\n");

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

$listed = fractal_zip_classic_vpk_list_from_bytes($blob);
echo 'listed=' . (is_array($listed) ? count($listed) : 0) . "\n";
$m = [];
$r = [];
$ok = fractal_zip_folder_try_expand_classic('pak01.vpk', $blob, $m, $r);
echo 'peel=' . ($ok ? '1' : '0') . ' members=' . count($m) . "\n";
if ($ok) {
	$payloads = [];
	foreach ($r['pak01.vpk']['member_names'] as $n) {
		$payloads[] = ['name' => $n, 'data' => $m['pak01.vpk/' . $n]];
	}
	$reb = fractal_zip_classic_rebuild_archive('vpk', 'v1', $payloads);
	echo 'rebuild_ok=' . (($reb === $blob) ? '1' : '0') . "\n";
}
