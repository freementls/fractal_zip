<?php

declare(strict_types=1);

/**
 * Run a small env matrix on FLAC-heavy corpora and print a compact leaderboard.
 *
 * Usage:
 *   php benchmarks/flac_lane_matrix.php
 *   php benchmarks/flac_lane_matrix.php test_files60
 *   php benchmarks/flac_lane_matrix.php test_files60 test_files59
 *   php benchmarks/flac_lane_matrix.php test_files60 --max-rows=2   # smoke: first N matrix rows only
 *
 * Each case calls `benchmarks/run_benchmarks.php` with `--no-verify --no-case-timeout --no-baseline-cache --json --out-json=…`.
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$bench = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php';

$maxRows = null;
$corpora = array();
foreach (array_slice($argv, 1) as $a) {
	if (preg_match('/^--max-rows=(\d+)$/', $a, $m) === 1) {
		$maxRows = max(1, min(64, (int) $m[1]));
		continue;
	}
	if (strncmp($a, '--', 2) === 0) {
		fwrite(STDERR, "Unknown flag: {$a}\n");
		exit(2);
	}
	$corpora[] = $a;
}
if ($corpora === array()) {
	$corpora = array('test_files60');
}
if (in_array('test_files59', $corpora, true) && !is_dir($repo . DIRECTORY_SEPARATOR . 'test_files59')) {
	fwrite(STDERR, "Skip: test_files59 not present\n");
	$corpora2 = array();
	foreach ($corpora as $c) {
		if ($c !== 'test_files59') {
			$corpora2[] = $c;
		}
	}
	$corpora = $corpora2;
}

/** @var list<string> */
$matrixEnvKeys = array(
	'FRACTAL_ZIP_FLACPAC',
	'FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE',
	'FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_DUAL_RANK',
	'FRACTAL_ZIP_FLACPAC_PCM_PRE_FRACTAL_CANDIDATES',
	'FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_RANK_LEVEL',
	'FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_CHUNK_BYTES',
);

function flac_lane_matrix_clear_env(): void {
	global $matrixEnvKeys;
	foreach ($matrixEnvKeys as $k) {
		putenv($k);
	}
}

$matrix60 = array(
	array('id' => 'A_default', 'desc' => 'FLACPAC unset (bit-exact FLAC)', 'unset' => array('FRACTAL_ZIP_FLACPAC'), 'set' => array()),
	array('id' => 'B_flacpac', 'desc' => 'FLACPAC=1 merged FZCD', 'unset' => array(), 'set' => array('FRACTAL_ZIP_FLACPAC' => '1')),
	array('id' => 'C_flac_literal_auto', 'desc' => 'B + literal tournament on .flac (auto)', 'unset' => array(), 'set' => array(
		'FRACTAL_ZIP_FLACPAC' => '1',
		'FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE' => 'auto',
	)),
	array('id' => 'D_flac_literal_on', 'desc' => 'B + literal tournament on .flac (always)', 'unset' => array(), 'set' => array(
		'FRACTAL_ZIP_FLACPAC' => '1',
		'FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE' => '1',
	)),
	array('id' => 'E_pcm_dual_gzip', 'desc' => 'B + PCM pre gzip dual-rank', 'unset' => array(), 'set' => array(
		'FRACTAL_ZIP_FLACPAC' => '1',
		'FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_DUAL_RANK' => '1',
	)),
	array('id' => 'F_pcm_cand16', 'desc' => 'B + PCM fractal candidates=16', 'unset' => array(), 'set' => array(
		'FRACTAL_ZIP_FLACPAC' => '1',
		'FRACTAL_ZIP_FLACPAC_PCM_PRE_FRACTAL_CANDIDATES' => '16',
	)),
	array('id' => 'G_pcm_gzip9', 'desc' => 'B + PCM gzip rank level=9', 'unset' => array(), 'set' => array(
		'FRACTAL_ZIP_FLACPAC' => '1',
		'FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_RANK_LEVEL' => '9',
	)),
	array('id' => 'H_chunk_2m', 'desc' => 'B + merged fractal chunk 2 MiB', 'unset' => array(), 'set' => array(
		'FRACTAL_ZIP_FLACPAC' => '1',
		'FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_CHUNK_BYTES' => '2097152',
	)),
	array('id' => 'I_dual_cand16', 'desc' => 'B + dual-rank + cand16', 'unset' => array(), 'set' => array(
		'FRACTAL_ZIP_FLACPAC' => '1',
		'FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_DUAL_RANK' => '1',
		'FRACTAL_ZIP_FLACPAC_PCM_PRE_FRACTAL_CANDIDATES' => '16',
	)),
);

$matrix59 = array(
	array('id' => 'A_default', 'desc' => 'FLACPAC unset', 'unset' => array('FRACTAL_ZIP_FLACPAC'), 'set' => array()),
	array('id' => 'B_flacpac', 'desc' => 'FLACPAC=1', 'unset' => array(), 'set' => array('FRACTAL_ZIP_FLACPAC' => '1')),
);

/**
 * @param array<int, array{id: string, desc: string, unset: list<string>, set: array<string, string>}> $matrix
 * @return array{fzc: int, best: int, raw: int, zip_s: float, verify: bool}|null
 */
function flac_lane_matrix_run_case(string $repo, string $bench, string $corpus, array $row): ?array {
	flac_lane_matrix_clear_env();
	foreach ($row['unset'] as $k) {
		putenv($k);
	}
	foreach ($row['set'] as $k => $v) {
		putenv($k . '=' . $v);
	}

	$jsonPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_mat_' . bin2hex(random_bytes(8)) . '.json';
	$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($bench)
		. ' --only=' . escapeshellarg($corpus)
		. ' --no-verify --no-case-timeout --no-baseline-cache --json --out-json=' . escapeshellarg($jsonPath);

	$null = '/dev/null';
	// Do not use a pipe for stdout: run_benchmarks prints progress lines; an unread pipe fills and **deadlocks** the child.
	$descSpec = array(
		0 => array('file', $null, 'r'),
		1 => array('file', $null, 'w'),
		2 => array('pipe', 'w'),
	);
	$proc = proc_open($cmd, $descSpec, $pipes, $repo, null);
	if (!is_resource($proc)) {
		flac_lane_matrix_clear_env();
		return null;
	}
	$stderr = stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	$code = proc_close($proc);

	flac_lane_matrix_clear_env();

	if ($code !== 0) {
		$err = trim((string) $stderr);
		fwrite(STDERR, "FAIL {$row['id']} exit={$code} corpus={$corpus}" . ($err !== '' ? "\n{$err}\n" : "\n"));
		return null;
	}
	$rawJ = @file_get_contents($jsonPath);
	@unlink($jsonPath);
	if (!is_string($rawJ) || $rawJ === '') {
		fwrite(STDERR, "FAIL {$row['id']} no json corpus={$corpus}\n");
		return null;
	}
	$j = bench_json_decode_assoc_try($rawJ, 'flac_lane_matrix ' . $corpus);
	if ($j === null || !isset($j['totals']) || !is_array($j['totals'])) {
		fwrite(STDERR, "FAIL {$row['id']} bad json corpus={$corpus}\n");
		return null;
	}
	$t = $j['totals'];
	$fzc = (int) ($t['fzc_bytes'] ?? 0);
	$best = (int) ($t['best_ext_folder_bytes'] ?? 0);
	$raw = (int) ($t['raw_bytes'] ?? 0);
	$zipS = (float) ($t['zip_seconds'] ?? 0.0);
	$verify = null;
	$c0 = isset($j['cases'][0]) && is_array($j['cases'][0]) ? $j['cases'][0] : [];
	if (array_key_exists('verify_ok', $c0)) {
		if ($c0['verify_ok'] === true) {
			$verify = true;
		} elseif ($c0['verify_ok'] === false) {
			$verify = false;
		}
	}
	return array('fzc' => $fzc, 'best' => $best, 'raw' => $raw, 'zip_s' => $zipS, 'verify' => $verify);
}

fwrite(STDOUT, "# flac_lane_matrix " . date('c') . "\n");
if ($maxRows !== null) {
	fwrite(STDOUT, "# --max-rows={$maxRows} (partial matrix)\n");
}
foreach ($corpora as $corpus) {
	$matrix = ($corpus === 'test_files59') ? $matrix59 : $matrix60;
	if ($maxRows !== null) {
		$matrix = array_slice($matrix, 0, $maxRows);
	}
	fwrite(STDOUT, "\n## corpus={$corpus}\n");
	fwrite(STDOUT, "# id\tdesc\tfzc_B\tbest_ext_B\tdelta_vs_best\tzip_s\tverify_ok\n");
	$bestFzc = PHP_INT_MAX;
	$bestRow = '';
	foreach ($matrix as $row) {
		$r = flac_lane_matrix_run_case($repo, $bench, $corpus, $row);
		if ($r === null) {
			continue;
		}
		$delta = $r['best'] > 0 ? ($r['best'] - $r['fzc']) : 0;
		$pct = ($r['best'] > 0) ? (100.0 * $delta / $r['best']) : 0.0;
		$deltaStr = (string) $delta . ' (' . sprintf('%.4f', $pct) . '%)';
		fwrite(STDOUT, $row['id'] . "\t" . $row['desc'] . "\t" . (string) $r['fzc'] . "\t" . (string) $r['best'] . "\t" . $deltaStr . "\t" . sprintf('%.3f', $r['zip_s']) . "\t" . ($r['verify'] === null ? '-' : ($r['verify'] ? '1' : '0')) . "\n");
		if ($r['fzc'] > 0 && $r['fzc'] < $bestFzc) {
			$bestFzc = $r['fzc'];
			$bestRow = $row['id'];
		}
	}
	if ($bestRow !== '') {
		fwrite(STDOUT, "# smallest fzc: {$bestRow} => {$bestFzc} B\n");
	}
}

fwrite(STDOUT, "\n# done\n");
exit(0);
