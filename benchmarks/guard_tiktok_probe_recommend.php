#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Validate TikTok probe recommendation JSON (or run a fresh sweep first).
 *
 * Usage:
 *   php benchmarks/guard_tiktok_probe_recommend.php --recommend-json=benchmarks/.tiktok_probe_test_files133_sample/recommend.json
 *   php benchmarks/guard_tiktok_probe_recommend.php --run-sweep --only=test_files133_sample
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$recommendJson = null;
$runSweep = false;
$only = 'test_files133_sample';
$bytesSlackPct = 1.0;
$maxProbeBytes = null;
$expectedMinBytes = null;
$maxRecommendedZipSeconds = null;

foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--recommend-json=', 17) === 0) {
		$recommendJson = trim(substr($a, 17));
		continue;
	}
	if ($a === '--run-sweep') {
		$runSweep = true;
		continue;
	}
	if (strncmp($a, '--only=', 7) === 0) {
		$only = trim(substr($a, 7));
		continue;
	}
	if (strncmp($a, '--bytes-slack-pct=', 18) === 0) {
		$bytesSlackPct = max(0.0, (float) trim(substr($a, 18)));
		continue;
	}
	if (strncmp($a, '--max-probe-bytes=', 18) === 0) {
		$maxProbeBytes = max(1, (int) trim(substr($a, 18)));
		continue;
	}
	if (strncmp($a, '--expected-min-bytes=', 21) === 0) {
		$expectedMinBytes = max(0, (int) trim(substr($a, 21)));
		continue;
	}
	if (strncmp($a, '--max-rec-zip-seconds=', 22) === 0) {
		$maxRecommendedZipSeconds = max(0.0, (float) trim(substr($a, 22)));
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php benchmarks/guard_tiktok_probe_recommend.php [--recommend-json=PATH] [--run-sweep] [--only=CORPUS]\n";
		echo "  Optional: --bytes-slack-pct=1 --max-probe-bytes=1048576 --expected-min-bytes=8435136 --max-rec-zip-seconds=420\n";
		exit(0);
	}
}

if ($maxProbeBytes === null && $only === 'test_files133_sample') {
	$maxProbeBytes = 1048576;
}
if ($expectedMinBytes === null && $only === 'test_files133_sample') {
	$expectedMinBytes = 8435136;
}
if ($maxRecommendedZipSeconds === null && $only === 'test_files133_sample') {
	$maxRecommendedZipSeconds = 420.0;
}

if ($runSweep) {
	$tmpOut = $repoRoot . '/benchmarks/.guard_tiktok_probe_' . bin2hex(random_bytes(4));
	$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	$cmd = escapeshellarg($phpBin)
		. ' ' . escapeshellarg($repoRoot . '/benchmarks/tiktok_probe_sweep.php')
		. ' --only=' . escapeshellarg($only)
		. ' --out-dir=' . escapeshellarg($tmpOut);
	passthru($cmd, $rc);
	if ((int) $rc !== 0) {
		fwrite(STDERR, "guard_tiktok_probe_recommend: sweep failed rc={$rc}\n");
		exit(1);
	}
	$recommendJson = $tmpOut . '/recommend.json';
}

if ($recommendJson === null || $recommendJson === '') {
	fwrite(STDERR, "guard_tiktok_probe_recommend: pass --recommend-json=PATH or --run-sweep\n");
	exit(2);
}

$data = bench_json_decode_file_assoc_try($recommendJson, 'guard_tiktok_probe_recommend');
if ($data === null) {
	exit(1);
}
$rows = isset($data['rows']) && is_array($data['rows']) ? $data['rows'] : array();
$recProbe = isset($data['recommend_probe']) ? (int) $data['recommend_probe'] : 0;
if ($rows === array() || $recProbe <= 0) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: invalid rows/recommend_probe\n");
	exit(1);
}

$byProbe = array();
$minBytes = PHP_INT_MAX;
foreach ($rows as $r) {
	if (!is_array($r)) {
		continue;
	}
	$probe = (int) ($r['probe'] ?? 0);
	$fzc = (int) ($r['fzc_bytes'] ?? 0);
	$zip = (float) ($r['zip_seconds'] ?? INF);
	$byProbe[$probe] = array('probe' => $probe, 'fzc_bytes' => $fzc, 'zip_seconds' => $zip);
	if ($fzc > 0 && $fzc < $minBytes) {
		$minBytes = $fzc;
	}
}
if (!isset($byProbe[$recProbe])) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: recommend_probe={$recProbe} missing in rows\n");
	exit(1);
}
$rec = $byProbe[$recProbe];
if ($minBytes === PHP_INT_MAX) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: no valid min bytes\n");
	exit(1);
}

$eligibleMax = (int) floor((float) $minBytes * (1.0 + ($bytesSlackPct / 100.0)));
if ((int) $rec['fzc_bytes'] > $eligibleMax) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: recommended bytes {$rec['fzc_bytes']} exceed eligible max {$eligibleMax}\n");
	exit(1);
}

$fastestEligible = null;
foreach ($byProbe as $row) {
	if ((int) $row['fzc_bytes'] <= $eligibleMax) {
		if ($fastestEligible === null || (float) $row['zip_seconds'] < (float) $fastestEligible['zip_seconds']) {
			$fastestEligible = $row;
		}
	}
}
if (is_array($fastestEligible) && (int) $fastestEligible['probe'] !== $recProbe) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: recommend_probe={$recProbe} but fastest eligible probe={$fastestEligible['probe']}\n");
	exit(1);
}

if ($maxProbeBytes !== null && $recProbe > $maxProbeBytes) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: recommend_probe={$recProbe} exceeds max_probe_bytes={$maxProbeBytes}\n");
	exit(1);
}
if ($expectedMinBytes !== null && $minBytes !== $expectedMinBytes) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: min_bytes={$minBytes} expected={$expectedMinBytes}\n");
	exit(1);
}
if ($maxRecommendedZipSeconds !== null && (float) $rec['zip_seconds'] > $maxRecommendedZipSeconds) {
	fwrite(STDERR, "guard_tiktok_probe_recommend: recommended zip_seconds={$rec['zip_seconds']} exceeds {$maxRecommendedZipSeconds}\n");
	exit(1);
}

echo "OK guard_tiktok_probe_recommend corpus=" . (string) ($data['corpus'] ?? $only)
	. " recommend_probe={$recProbe} min_bytes={$minBytes} zip_s=" . sprintf('%.2f', (float) $rec['zip_seconds']) . "\n";
