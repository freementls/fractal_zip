#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare latest overall TikTok recommendation against previous sweep.
 *
 * Flags profile/probe flips and marks them "unexpected" if the new
 * recommendation regresses both bytes and speed beyond conservative margins.
 *
 * Usage:
 *   php benchmarks/tiktok_recommend_drift.php --latest-out-dir=benchmarks/.tiktok_sweep_YYYYmmdd_HHMMSS
 * Optional:
 *   --history-root=benchmarks
 *   --fail-on-unexpected
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$latestOutDir = '';
$historyRoot = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks';
$failOnUnexpected = false;

foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--latest-out-dir=', 17) === 0) {
		$latestOutDir = trim(substr($a, 17));
		continue;
	}
	if (strncmp($a, '--history-root=', 15) === 0) {
		$historyRoot = trim(substr($a, 15));
		continue;
	}
	if ($a === '--fail-on-unexpected') {
		$failOnUnexpected = true;
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php benchmarks/tiktok_recommend_drift.php --latest-out-dir=PATH [--history-root=benchmarks] [--fail-on-unexpected]\n";
		exit(0);
	}
}

if ($latestOutDir === '' || !is_dir($latestOutDir)) {
	fwrite(STDERR, "tiktok_recommend_drift: pass --latest-out-dir=existing_dir\n");
	exit(2);
}
if (!is_dir($historyRoot)) {
	fwrite(STDERR, "tiktok_recommend_drift: history root not found: {$historyRoot}\n");
	exit(2);
}

/**
 * @return array<string,mixed>|null
 */
function tiktok_load_recommend_json(string $dir): ?array
{
	$path = $dir . DIRECTORY_SEPARATOR . 'recommend_overall.json';
	if (!is_file($path)) {
		return null;
	}
	$j = bench_json_decode_file_assoc_try($path, 'tiktok_recommend_drift');
	return is_array($j) ? $j : null;
}

/**
 * @return list<string>
 */
function tiktok_list_sweep_dirs(string $historyRoot): array
{
	$list = scandir($historyRoot);
	if (!is_array($list)) {
		return array();
	}
	$dirs = array();
	foreach ($list as $name) {
		if (!is_string($name) || $name === '.' || $name === '..') {
			continue;
		}
		if (!str_starts_with($name, '.tiktok_sweep_')) {
			continue;
		}
		$path = $historyRoot . DIRECTORY_SEPARATOR . $name;
		if (!is_dir($path)) {
			continue;
		}
		$dirs[] = $path;
	}
	usort($dirs, static function (string $a, string $b): int {
		return strcmp($b, $a);
	});
	return $dirs;
}

$latestRec = tiktok_load_recommend_json($latestOutDir);
if (!is_array($latestRec)) {
	fwrite(STDERR, "tiktok_recommend_drift: missing/invalid recommend_overall.json under {$latestOutDir}\n");
	exit(1);
}

$latestCorpus = isset($latestRec['corpus']) && is_string($latestRec['corpus']) ? $latestRec['corpus'] : '';
$sweepDirs = tiktok_list_sweep_dirs($historyRoot);
$previousDir = null;
$previousRec = null;
foreach ($sweepDirs as $dir) {
	if ($dir === $latestOutDir) {
		continue;
	}
	$rec = tiktok_load_recommend_json($dir);
	if (!is_array($rec)) {
		continue;
	}
	$corpus = isset($rec['corpus']) && is_string($rec['corpus']) ? $rec['corpus'] : '';
	if ($latestCorpus !== '' && $corpus !== '' && $corpus !== $latestCorpus) {
		continue;
	}
	$previousDir = $dir;
	$previousRec = $rec;
	break;
}

$result = array(
	'latest_out_dir' => $latestOutDir,
	'latest_corpus' => $latestCorpus !== '' ? $latestCorpus : null,
	'previous_out_dir' => $previousDir,
	'has_previous' => is_string($previousDir),
	'profile_flip' => false,
	'probe_flip' => false,
	'unexpected_flip' => false,
	'notes' => array(),
	'latest' => array(
		'profile_tag' => (string) ($latestRec['recommend_profile_tag'] ?? ''),
		'probe_max_bytes' => isset($latestRec['recommend_probe_max_bytes']) ? (int) $latestRec['recommend_probe_max_bytes'] : null,
		'bytes' => isset($latestRec['recommend_profile_bytes']) ? (int) $latestRec['recommend_profile_bytes'] : null,
		'zip_seconds' => isset($latestRec['recommend_profile_zip_seconds']) ? (float) $latestRec['recommend_profile_zip_seconds'] : null,
	),
	'previous' => null,
	'bytes_delta_pct' => null,
	'zip_seconds_delta_pct' => null,
);

if (!is_array($previousRec)) {
	$result['notes'][] = 'no previous comparable sweep with recommend_overall.json';
	$json = bench_json_encode_try($result, true);
	if ($json !== null) {
		file_put_contents($latestOutDir . DIRECTORY_SEPARATOR . 'recommend_drift.json', $json . "\n");
	}
	echo "drift: no previous comparable recommendation (wrote recommend_drift.json)\n";
	exit(0);
}

$latestProfile = (string) ($latestRec['recommend_profile_tag'] ?? '');
$latestProbe = isset($latestRec['recommend_probe_max_bytes']) ? (int) $latestRec['recommend_probe_max_bytes'] : null;
$latestBytes = isset($latestRec['recommend_profile_bytes']) ? (int) $latestRec['recommend_profile_bytes'] : 0;
$latestZip = isset($latestRec['recommend_profile_zip_seconds']) ? (float) $latestRec['recommend_profile_zip_seconds'] : 0.0;

$prevProfile = (string) ($previousRec['recommend_profile_tag'] ?? '');
$prevProbe = isset($previousRec['recommend_probe_max_bytes']) ? (int) $previousRec['recommend_probe_max_bytes'] : null;
$prevBytes = isset($previousRec['recommend_profile_bytes']) ? (int) $previousRec['recommend_profile_bytes'] : 0;
$prevZip = isset($previousRec['recommend_profile_zip_seconds']) ? (float) $previousRec['recommend_profile_zip_seconds'] : 0.0;

$profileFlip = ($latestProfile !== '' && $prevProfile !== '' && $latestProfile !== $prevProfile);
$probeFlip = ($latestProbe !== null && $prevProbe !== null && $latestProbe !== $prevProbe);

$bytesDeltaPct = null;
if ($prevBytes > 0 && $latestBytes > 0) {
	$bytesDeltaPct = ((float) $latestBytes - (float) $prevBytes) / (float) $prevBytes * 100.0;
}
$zipDeltaPct = null;
if ($prevZip > 0.0 && $latestZip > 0.0) {
	$zipDeltaPct = ((float) $latestZip - (float) $prevZip) / (float) $prevZip * 100.0;
}

$unexpected = false;
if ($profileFlip || $probeFlip) {
	$bytesWorse = ($bytesDeltaPct !== null && $bytesDeltaPct > 1.0);
	$zipWorse = ($zipDeltaPct !== null && $zipDeltaPct > 10.0);
	$unexpected = $bytesWorse && $zipWorse;
}

$result['profile_flip'] = $profileFlip;
$result['probe_flip'] = $probeFlip;
$result['unexpected_flip'] = $unexpected;
$result['previous'] = array(
	'profile_tag' => $prevProfile,
	'probe_max_bytes' => $prevProbe,
	'bytes' => $prevBytes > 0 ? $prevBytes : null,
	'zip_seconds' => $prevZip > 0.0 ? $prevZip : null,
);
$result['bytes_delta_pct'] = $bytesDeltaPct;
$result['zip_seconds_delta_pct'] = $zipDeltaPct;
if ($profileFlip) {
	$result['notes'][] = 'profile changed';
}
if ($probeFlip) {
	$result['notes'][] = 'probe changed';
}
if ($unexpected) {
	$result['notes'][] = 'flip regresses both bytes (>1%) and speed (>10%)';
}

$json = bench_json_encode_try($result, true);
if ($json !== null) {
	file_put_contents($latestOutDir . DIRECTORY_SEPARATOR . 'recommend_drift.json', $json . "\n");
}

$line = "drift: profile_flip=" . ($profileFlip ? '1' : '0')
	. " probe_flip=" . ($probeFlip ? '1' : '0')
	. " unexpected=" . ($unexpected ? '1' : '0')
	. " latest=" . ($latestProfile !== '' ? $latestProfile : '?')
	. ($latestProbe !== null ? "/probe{$latestProbe}" : '')
	. " prev=" . ($prevProfile !== '' ? $prevProfile : '?')
	. ($prevProbe !== null ? "/probe{$prevProbe}" : '');
if ($bytesDeltaPct !== null) {
	$line .= " bytes_delta=" . sprintf('%+.2f%%', $bytesDeltaPct);
}
if ($zipDeltaPct !== null) {
	$line .= " zip_delta=" . sprintf('%+.2f%%', $zipDeltaPct);
}
echo $line . "\n";
echo "wrote {$latestOutDir}/recommend_drift.json\n";

if ($unexpected && $failOnUnexpected) {
	exit(1);
}
exit(0);
