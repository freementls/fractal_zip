#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Sub-30min gate loop before any full enwik8 encode (Beat 15M plan Phase 1).
 *
 * Usage:
 *   bash tools/fz_sweep_zombies.sh
 *   php benchmarks/run_enwik8_beat15m_fast_gate.php
 *   php benchmarks/run_enwik8_beat15m_fast_gate.php --skip-sub1
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');

$skipSub1 = in_array('--skip-sub1', $argv, true);
$skip768 = in_array('--skip-768', $argv, true);

const HUTTER_BYTES = 15284944;
const BEST_FZC_BYTES = 19207083;
const MONO_MI_384P = 651650;

/**
 * @return array{mono_mi: ?int, rows: list<array<string, mixed>>}
 */
function beat15m_read_wire_probe(string $path, int $expectPages): array
{
	$empty = array('mono_mi' => null, 'rows' => array());
	if (!is_file($path)) {
		return $empty;
	}
	$dec = json_decode((string) file_get_contents($path), true);
	if (!is_array($dec) || (int) ($dec['pages'] ?? 0) !== $expectPages) {
		return $empty;
	}
	$rows = is_array($dec['rows'] ?? null) ? $dec['rows'] : array();
	$mono = null;
	foreach ($rows as $r) {
		if (($r['label'] ?? '') === 'split_inner_fztx_mono_mi' && empty($r['error'])) {
			$mono = (int) ($r['fzc_bytes'] ?? 0);
			break;
		}
	}
	return array('mono_mi' => $mono, 'rows' => $rows);
}

/** @return array{ok: bool, label: string, seconds: float, detail: string} */
function beat15m_gate_run(string $label, array $cmd, string $repo): array
{
	$t0 = microtime(true);
	$full = 'cd ' . escapeshellarg($repo) . ' && ' . implode(' ', $cmd) . ' 2>&1';
	exec($full, $out, $code);
	$sec = round(microtime(true) - $t0, 2);
	$tail = implode("\n", array_slice($out, -6));
	return array(
		'ok' => $code === 0,
		'label' => $label,
		'seconds' => $sec,
		'detail' => $tail,
	);
}

$steps = array();
$steps[] = beat15m_gate_run('zombie_sweep', array('bash', 'tools/fz_sweep_zombies.sh'), $repo);
$steps[] = beat15m_gate_run('asc_regression', array('php', 'benchmarks/all_substrings_count_regression.php'), $repo);
$steps[] = beat15m_gate_run('asc_slide_regression', array('php', 'benchmarks/all_substrings_count_slide_regression.php'), $repo);
$wirePath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json';
$wire384Path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe_384p.json';

$steps[] = beat15m_gate_run('wire_384p', array(
	'php', '-d', 'memory_limit=2048M', 'benchmarks/bench_enwik8_wire_slice_probe.php',
	'--pages=384', '--cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_stack_pt_zstd22',
), $repo);
if (is_file($wirePath)) {
	copy($wirePath, $wire384Path);
}
$wire384 = beat15m_read_wire_probe($wire384Path, 384);
$monoMi384 = $wire384['mono_mi'];

$wire768Path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe_768p.json';
$wire768Rows = null;
if (!$skip768) {
	$steps[] = beat15m_gate_run('wire_768p', array(
		'php', '-d', 'memory_limit=2048M', 'benchmarks/bench_enwik8_wire_slice_probe.php',
		'--pages=768',
		'--cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_concat_stat_pred_inner',
	), $repo);
	if (is_file($wirePath)) {
		copy($wirePath, $wire768Path);
	}
	$wire768 = beat15m_read_wire_probe($wire768Path, 768);
	$wire768Rows = $wire768['rows'];
}
$steps[] = beat15m_gate_run('roundtrip_384p', array(
	'php', '-d', 'memory_limit=2048M', 'benchmarks/verify_enwik_slice_roundtrip.php',
	'--pages=384', '--text-inner-promotion',
), $repo);
$steps[] = beat15m_gate_run('parallel_phases', array(
	'php', 'benchmarks/bench_fractal_parallel_phases.php', '--pages=5',
), $repo);
if (!$skipSub1) {
	$steps[] = beat15m_gate_run('sub1_matrix', array(
		'php', 'benchmarks/bench_enwik8_sub1_bpc_matrix.php', '--pages=sample5', '--quick',
	), $repo);
}

$allOk = true;
foreach ($steps as $s) {
	if (!$s['ok']) {
		$allOk = false;
	}
}

$wire768StatPred = null;
if (is_array($wire768Rows ?? null)) {
	foreach ($wire768Rows as $r) {
		if (($r['label'] ?? '') === 'split_inner_fztx_mono_concat_stat_pred_inner' && empty($r['error'])) {
			$mono768Amort = null;
			foreach ($wire768Rows as $m) {
				if (($m['label'] ?? '') === 'split_inner_fztx_mono_mi') {
					$mono768Amort = (int) ($m['amortized_fzc'] ?? $m['wire_fzc'] ?? 0);
					break;
				}
			}
			$wire768StatPred = array(
				'wire_fzc' => (int) ($r['wire_fzc'] ?? $r['fzc_bytes'] ?? 0),
				'meta_bytes' => (int) ($r['meta_bytes'] ?? 0),
				'member_plus_outer' => (int) ($r['member_plus_outer'] ?? 0),
				'amortized_fzc' => (int) ($r['amortized_fzc'] ?? 0),
				'amort_delta_vs_mono_mi' => $mono768Amort !== null
					? (int) ($r['amortized_fzc'] ?? 0) - $mono768Amort : null,
			);
			break;
		}
	}
}

$report = array(
	'generated' => date('c'),
	'target_hutter_bytes' => HUTTER_BYTES,
	'best_fzc_bytes' => BEST_FZC_BYTES,
	'mono_mi_384p_baseline' => MONO_MI_384P,
	'mono_mi_384p_measured' => $monoMi384,
	'stat_pred_768p' => $wire768StatPred,
	'steps' => $steps,
	'all_ok' => $allOk,
	'full_encode_allowed' => false,
);

$gatePath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_beat15m_fast_gate.json';
file_put_contents($gatePath, json_encode($report, JSON_PRETTY_PRINT));

echo "beat15m fast gate → {$gatePath}\n";
foreach ($steps as $s) {
	echo ($s['ok'] ? '  OK ' : ' FAIL ') . $s['label'] . '  ' . $s['seconds'] . "s\n";
}
if ($monoMi384 !== null) {
	$d = $monoMi384 - MONO_MI_384P;
	echo '  mono_mi @384p: ' . number_format($monoMi384) . ' B (' . ($d >= 0 ? '+' : '') . number_format($d) . " vs baseline)\n";
}

require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_enwik8_full_encode_gate.php';
$extrap = enwik8_full_encode_gate_evaluate($monoMi384, $wire768Rows);
$report['extrapolation'] = $extrap;
$report['full_encode_allowed'] = $extrap['allow_full_encode'];
file_put_contents($gatePath, json_encode($report, JSON_PRETTY_PRINT));

echo '  full_encode_allowed: ' . ($extrap['allow_full_encode'] ? 'yes' : 'no') . ' — ' . ($extrap['reason'] ?? '') . "\n";

exit($allOk ? 0 : 1);
