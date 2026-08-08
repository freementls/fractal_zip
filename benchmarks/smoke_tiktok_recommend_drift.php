#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke test: recommendation drift detector reports profile/probe flips.
 */

$root = dirname(__DIR__);
$script = $root . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'tiktok_recommend_drift.php';
if (!is_file($script)) {
	fwrite(STDERR, "missing {$script}\n");
	exit(1);
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_tiktok_drift_' . bin2hex(random_bytes(4));
$benchDir = $tmp . DIRECTORY_SEPARATOR . 'benchmarks';
$prevDir = $benchDir . DIRECTORY_SEPARATOR . '.tiktok_sweep_20260101_000001';
$latestDir = $benchDir . DIRECTORY_SEPARATOR . '.tiktok_sweep_20260101_000002';
if (!@mkdir($prevDir, 0777, true) || !@mkdir($latestDir, 0777, true)) {
	fwrite(STDERR, "mkdir failed under {$tmp}\n");
	exit(1);
}

$prev = array(
	'corpus' => 'test_files133_sample',
	'recommend_profile_tag' => 'large_balanced',
	'recommend_profile_arg' => 'large-balanced',
	'recommend_profile_bytes' => 8400000,
	'recommend_profile_zip_seconds' => 320.0,
	'recommend_probe_max_bytes' => 65536,
);
$latest = array(
	'corpus' => 'test_files133_sample',
	'recommend_profile_tag' => 'large_fast',
	'recommend_profile_arg' => 'large-fast',
	'recommend_profile_bytes' => 8450000,
	'recommend_profile_zip_seconds' => 350.0,
	'recommend_probe_max_bytes' => 1048576,
);

$prevJson = json_encode($prev, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
$latestJson = json_encode($latest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if (!is_string($prevJson) || !is_string($latestJson)) {
	fwrite(STDERR, "json_encode failed\n");
	exit(1);
}
file_put_contents($prevDir . DIRECTORY_SEPARATOR . 'recommend_overall.json', $prevJson . "\n");
file_put_contents($latestDir . DIRECTORY_SEPARATOR . 'recommend_overall.json', $latestJson . "\n");

$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($script)
	. ' --latest-out-dir=' . escapeshellarg($latestDir)
	. ' --history-root=' . escapeshellarg($benchDir);
$desc = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$p = proc_open($cmd, $desc, $pipes, $root);
if (!is_resource($p)) {
	fwrite(STDERR, "proc_open failed\n");
	exit(1);
}
$stdout = stream_get_contents($pipes[1]);
$stderr = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$code = proc_close($p);

if ($code !== 0) {
	fwrite(STDERR, "drift script failed: code={$code} out={$stdout} err={$stderr}\n");
	exit(1);
}
$driftPath = $latestDir . DIRECTORY_SEPARATOR . 'recommend_drift.json';
if (!is_file($driftPath)) {
	fwrite(STDERR, "missing recommend_drift.json\n");
	exit(1);
}
$drift = json_decode((string) file_get_contents($driftPath), true);
if (!is_array($drift)) {
	fwrite(STDERR, "invalid recommend_drift.json\n");
	exit(1);
}
if (($drift['profile_flip'] ?? false) !== true) {
	fwrite(STDERR, "expected profile_flip=true\n");
	exit(1);
}
if (($drift['probe_flip'] ?? false) !== true) {
	fwrite(STDERR, "expected probe_flip=true\n");
	exit(1);
}
if (($drift['unexpected_flip'] ?? false) !== false) {
	fwrite(STDERR, "expected unexpected_flip=false for mild deltas\n");
	exit(1);
}

@unlink($prevDir . DIRECTORY_SEPARATOR . 'recommend_overall.json');
@unlink($latestDir . DIRECTORY_SEPARATOR . 'recommend_overall.json');
@unlink($latestDir . DIRECTORY_SEPARATOR . 'recommend_drift.json');
@rmdir($prevDir);
@rmdir($latestDir);
@rmdir($benchDir);
@rmdir($tmp);

fwrite(STDOUT, "OK smoke_tiktok_recommend_drift\n");
exit(0);
