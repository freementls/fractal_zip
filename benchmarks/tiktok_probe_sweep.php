#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Outer-predict probe byte sweep on one corpus (tik-tok speed pass).
 *
 * Sample-first loop for huge corpora:
 *   php benchmarks/tiktok_probe_sweep.php --only=test_files133 --auto-sample
 *
 * Existing sample:
 *   php benchmarks/tiktok_probe_sweep.php --only=test_files133_sample
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$only = 'test_files133_sample';
$autoSample = false;
$sampleTargetMiB = 28;
$outDir = null;
$writeEnv = true;
$repeat = 1;
$probes = array(4096, 8192, 65536, 1048576, 2097152);
$extraArgs = array('--large', '--no-case-timeout', '--no-verify', '--no-best-ext', '--bench-profile=large-fast');

foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--only=', 7) === 0) {
		$only = trim(substr($a, 7));
		continue;
	}
	if ($a === '--auto-sample') {
		$autoSample = true;
		continue;
	}
	if (strncmp($a, '--sample-target-mib=', 20) === 0) {
		$sampleTargetMiB = max(8, (int) trim(substr($a, 20)));
		continue;
	}
	if (strncmp($a, '--out-dir=', 10) === 0) {
		$outDir = trim(substr($a, 10));
		continue;
	}
	if (strncmp($a, '--repeat=', 9) === 0) {
		$repeat = max(1, (int) trim(substr($a, 9)));
		continue;
	}
	if ($a === '--no-write-env') {
		$writeEnv = false;
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php benchmarks/tiktok_probe_sweep.php [--only=CORPUS] [--auto-sample] [--sample-target-mib=28] [--out-dir=PATH] [--repeat=N] [--no-write-env]\n";
		exit(0);
	}
}

$phpBin = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';

if ($autoSample && is_dir($repoRoot . DIRECTORY_SEPARATOR . $only) && !str_ends_with($only, '_sample')) {
	$sampleName = $only . '_sample';
	$samplePath = $repoRoot . DIRECTORY_SEPARATOR . $sampleName;
	if (!is_dir($samplePath)) {
		$sampleCmd = escapeshellarg($phpBin)
			. ' ' . escapeshellarg($repoRoot . '/benchmarks/sample_large_corpus.php')
			. ' ' . escapeshellarg($only)
			. ' ' . escapeshellarg($sampleName)
			. ' --target-mib=' . escapeshellarg((string) $sampleTargetMiB);
		passthru($sampleCmd, $sampleRc);
		if ((int) $sampleRc !== 0) {
			fwrite(STDERR, "tiktok_probe_sweep: sample generation failed rc={$sampleRc}\n");
			exit(1);
		}
	}
	$only = $sampleName;
}

if ($outDir === null || $outDir === '') {
	$outDir = $repoRoot . '/benchmarks/.tiktok_probe_' . preg_replace('/[^a-zA-Z0-9_]+/', '_', $only);
}
if (!is_dir($outDir) && !@mkdir($outDir, 0755, true)) {
	fwrite(STDERR, "tiktok_probe_sweep: cannot mkdir {$outDir}\n");
	exit(1);
}

$rows = array();
echo "probe_sweep corpus={$only} out={$outDir}\n";
echo str_pad('probe_max_B', 12) . str_pad('fzc_bytes', 12) . str_pad('zip_s', 10) . str_pad('repeats', 10) . "outer\n";

foreach ($probes as $probe) {
	$zipSamples = array();
	$fzcSamples = array();
	$outerFirst = '?';
	for ($ri = 1; $ri <= $repeat; $ri++) {
		$suffix = $repeat > 1 ? ('_r' . $ri) : '';
		$json = $outDir . DIRECTORY_SEPARATOR . 'probe_' . $probe . $suffix . '.json';
		putenv('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES=' . (string) $probe);
		$cmd = escapeshellarg($phpBin)
			. ' ' . escapeshellarg($repoRoot . '/benchmarks/run_benchmarks.php')
			. ' --only=' . escapeshellarg($only)
			. ' --json --no-save-last-json --out-json=' . escapeshellarg($json);
		foreach ($extraArgs as $ea) {
			$cmd .= ' ' . escapeshellarg($ea);
		}
		passthru($cmd, $rc);
		if ((int) $rc !== 0) {
			fwrite(STDERR, "probe {$probe} run {$ri}: bench failed rc={$rc}\n");
			continue;
		}
		$j = bench_json_decode_file_assoc_try($json, 'tiktok_probe_sweep');
		if ($j === null) {
			continue;
		}
		$c = $j['cases'][0] ?? array();
		$fzc = (int) ($c['fzc_bytes'] ?? 0);
		$zip = (float) ($c['zip_seconds'] ?? 0);
		$outer = (string) ($c['outer_codec'] ?? '?');
		if ($outerFirst === '?') {
			$outerFirst = $outer;
		}
		if ($fzc > 0) {
			$fzcSamples[] = $fzc;
		}
		if ($zip > 0.0) {
			$zipSamples[] = $zip;
		}
	}
	if ($fzcSamples === array() || $zipSamples === array()) {
		continue;
	}
	sort($zipSamples, SORT_NUMERIC);
	$mid = (int) floor((count($zipSamples) - 1) / 2);
	$zipMed = (float) $zipSamples[$mid];
	$fzcMin = min($fzcSamples);
	$rows[] = array(
		'probe' => $probe,
		'fzc_bytes' => $fzcMin,
		'zip_seconds' => $zipMed,
		'outer' => $outerFirst,
		'repeat' => $repeat,
		'zip_samples' => $zipSamples,
		'fzc_samples' => $fzcSamples,
	);
	echo str_pad((string) $probe, 12) . str_pad((string) $fzcMin, 12) . str_pad(sprintf('%.2f', $zipMed), 10) . str_pad((string) count($zipSamples), 10) . "{$outerFirst}\n";
}

if ($rows !== array()) {
	$bestBytes = min(array_map(static fn(array $r): int => (int) $r['fzc_bytes'], $rows));
	$bestSpeed = null;
	foreach ($rows as $r) {
		if ((int) $r['fzc_bytes'] <= (int) floor($bestBytes * 1.01)) {
			if ($bestSpeed === null || (float) $r['zip_seconds'] < (float) $bestSpeed['zip_seconds']) {
				$bestSpeed = $r;
			}
		}
	}
	if (is_array($bestSpeed)) {
		echo "\nrecommend: FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES=" . $bestSpeed['probe']
			. " (bytes=" . $bestSpeed['fzc_bytes']
			. ", zip_s=" . sprintf('%.2f', (float) $bestSpeed['zip_seconds'])
			. ", outer=" . $bestSpeed['outer'] . ")\n";
		if ($writeEnv) {
			$envPath = $outDir . DIRECTORY_SEPARATOR . 'recommend.env';
			$envBody = "export FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES=" . (string) $bestSpeed['probe'] . "\n";
			$envBody .= "# generated by tiktok_probe_sweep.php for corpus " . $only . "\n";
			file_put_contents($envPath, $envBody);
			echo "recommend_env: {$envPath}\n";
			echo "apply: source " . escapeshellarg($envPath) . "\n";
		}
	}
	$recJson = bench_json_encode_try(array(
		'corpus' => $only,
		'recommend_probe' => $bestSpeed['probe'] ?? null,
		'repeat' => $repeat,
		'rows' => $rows,
	), true);
	if ($recJson !== null) {
		file_put_contents($outDir . DIRECTORY_SEPARATOR . 'recommend.json', $recJson . "\n");
	}
}

echo "done\n";
