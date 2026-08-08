#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Host capability matrix for fractal_zip web vs local parity.
 *
 * CLI:
 *   php examples/fzc_capability_report.php --label=local
 *   php examples/fzc_capability_report.php --label=local-web-sim --as-web
 *   php examples/fzc_capability_report.php --fzc=/path/to/archive.fz --out=local.json
 *
 * Browser / live server (JSON):
 *   examples/fzc_capability_report.php?json=1
 *   examples/fzc_capability_report.php?json=1&fzc=/path/optional.fz
 *
 * Paste live JSON next to local JSON and run:
 *   php examples/fzc_capability_compare.php local.json live.json
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_capability_probes.php';

if (PHP_SAPI !== 'cli') {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_web_shared.php';
	fzc_web_require_optional_bearer_secret(isset($_GET['json']) && (string) $_GET['json'] !== '' && (string) $_GET['json'] !== '0');
}

$label = 'host';
$asWeb = false;
$fzcPath = null;
$outPath = null;
$jsonOut = false;
$humanOut = false;

if (PHP_SAPI === 'cli') {
	$argvIn = $argv ?? array();
	foreach (array_slice($argvIn, 1) as $a) {
		if ($a === '--as-web') {
			$asWeb = true;
		} elseif ($a === '--json') {
			$jsonOut = true;
		} elseif ($a === '--human') {
			$humanOut = true;
		} elseif (strncmp($a, '--label=', 8) === 0) {
			$label = trim(substr($a, 8));
		} elseif (strncmp($a, '--fzc=', 6) === 0) {
			$fzcPath = trim(substr($a, 6));
		} elseif (strncmp($a, '--out=', 6) === 0) {
			$outPath = trim(substr($a, 6));
		} elseif ($a === '--help' || $a === '-h') {
			fwrite(STDOUT, "Usage: php fzc_capability_report.php [--label=NAME] [--as-web] [--fzc=PATH] [--out=file.json] [--json] [--human]\n");
			exit(0);
		}
	}
} else {
	$jsonOut = isset($_GET['json']) && (string) $_GET['json'] !== '' && (string) $_GET['json'] !== '0';
	$humanOut = !$jsonOut;
	if (isset($_GET['fzc']) && is_string($_GET['fzc']) && trim($_GET['fzc']) !== '') {
		$fzcPath = trim($_GET['fzc']);
	}
	$label = 'web-' . (string) ($_SERVER['HTTP_HOST'] ?? 'unknown');
	$asWeb = true;
}

$report = fzc_cap_build_report($label, $asWeb, $fzcPath);
if (PHP_SAPI !== 'cli' && isset($_GET['fzc']) && is_string($_GET['fzc']) && trim($_GET['fzc']) !== '' && ($fzcPath === null || $fzcPath === '')) {
	$report['parity_gaps'][] = array(
		'id' => 'fzc_path_rejected',
		'severity' => 'warn',
		'message' => 'Requested ?fzc= path is not under the repo or FRACTAL_ZIP_WEB_JOBS (ignored for round-trip).',
		'fix' => 'Upload via fzc_extract.php or place the archive under an allowed directory.',
	);
}
$json = bench_json_encode_try($report, true);
if ($json === null) {
	fwrite(STDERR, "json_encode failed\n");
	exit(2);
}

if (PHP_SAPI !== 'cli') {
	header('X-Content-Type-Options: nosniff');
	if ($humanOut) {
		header('Content-Type: text/plain; charset=UTF-8');
		echo fzc_cap_render_human_summary($report);
	} else {
		header('Content-Type: application/json; charset=UTF-8');
		echo $json . "\n";
	}
	exit(fzc_cap_parity_gap_error_count($report['parity_gaps'] ?? array(), fzc_cap_parity_gate_library_only()) > 0 ? 1 : 0);
}

if ($humanOut && !$jsonOut && $outPath === null) {
	echo fzc_cap_render_human_summary($report);
	exit(fzc_cap_parity_gap_error_count($report['parity_gaps'] ?? array(), fzc_cap_parity_gate_library_only()) > 0 ? 1 : 0);
}

if ($outPath !== null && $outPath !== '') {
	file_put_contents($outPath, $json . "\n");
	if (!$jsonOut) {
		fwrite(STDOUT, "Wrote {$outPath}\n");
	}
}

if ($jsonOut && ($outPath === null || $outPath === '')) {
	echo $json . "\n";
}

exit(fzc_cap_parity_gap_error_count($report['parity_gaps'] ?? array(), fzc_cap_parity_gate_library_only()) > 0 ? 1 : 0);
