#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * GATE summary: stat_pred_inner on real wire path vs mono_mi baseline.
 *
 * Δ_amort = amortized_fzc − mono_mi_amortized. Δ < 0 = PASS (fewer bytes at full-corpus scale).
 *
 * Usage:
 *   nice -n 19 php -d memory_limit=2048M benchmarks/bench_stat_pred_wire_gate.php [--pages=96|384] [--quick]
 */

$repo = dirname(__DIR__);
$pages = 96;
$quick = in_array('--quick', $argv, true);
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$cases = $quick
	? 'split_inner_fztx_mono_mi,split_inner_fztx_mono_concat_stat_pred_inner'
	: 'split_inner_fztx_mono_mi,split_inner_fztx_mono_concat,split_inner_fztx_mono_concat_stat_pred_inner,split_inner_fztx_mono_concat_stat_pred_inner_sealed_pick';

$model = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.stat_pred_inner_model.fzpm';
if (!is_file($model)) {
	fwrite(STDERR, "building frozen FZPM model...\n");
	exec('php ' . escapeshellarg($repo . '/benchmarks/build_stat_pred_inner_model.php') . ' 2>&1', $buildOut, $buildCode);
	if ($buildCode !== 0) {
		fwrite(STDERR, implode("\n", $buildOut) . "\n");
		exit(1);
	}
}

$probe = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_enwik8_wire_slice_probe.php';
$jsonOut = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json';
$cmd = sprintf(
	'nice -n 19 php -d memory_limit=2048M %s --pages=%d --cases=%s',
	escapeshellarg($probe),
	$pages,
	escapeshellarg($cases)
);

fwrite(STDERR, "running: {$cmd}\n");
exec($cmd . ' 2>&1', $lines, $code);
if ($code !== 0) {
	fwrite(STDERR, implode("\n", $lines) . "\n");
	exit(1);
}

if (!is_file($jsonOut)) {
	fwrite(STDERR, "missing probe json: {$jsonOut}\n");
	exit(1);
}

$data = json_decode((string) file_get_contents($jsonOut), true);
if (!is_array($data) || !is_array($data['rows'] ?? null)) {
	fwrite(STDERR, "bad probe json\n");
	exit(1);
}

$baseAmort = null;
$baseWire = (int) ($data['mono_mi_bytes'] ?? 0);
foreach ($data['rows'] as $row) {
	if (($row['label'] ?? '') === 'split_inner_fztx_mono_mi' && empty($row['error'])) {
		$baseAmort = (int) ($row['amortized_fzc'] ?? $row['fzc_bytes'] ?? 0);
		$baseWire = (int) ($row['fzc_bytes'] ?? $baseWire);
		break;
	}
}
if ($baseAmort === null) {
	fwrite(STDERR, "mono_mi baseline missing\n");
	exit(1);
}

$rows = array();
foreach ($data['rows'] as $row) {
	if (!empty($row['error'])) {
		$rows[] = array(
			'label' => (string) ($row['label'] ?? '?'),
			'gate' => 'FAIL',
			'wire' => 0,
			'amort' => 0,
			'd_amort' => 0,
			'meta' => 0,
			'detail' => (string) $row['error'],
		);
		continue;
	}
	$wire = (int) ($row['fzc_bytes'] ?? 0);
	$amort = (int) ($row['amortized_fzc'] ?? $wire);
	$meta = (int) ($row['meta_bytes'] ?? 0);
	$dAmort = $amort - $baseAmort;
	$dWire = $wire - $baseWire;
	$label = (string) ($row['label'] ?? '?');
	$useAmortGate = $pages >= 384;
	$gateDelta = $useAmortGate ? $dAmort : $dWire;
	$gate = $gateDelta < 0 ? 'PASS' : 'FAIL';
	$rows[] = array(
		'label' => $label,
		'gate' => $label === 'split_inner_fztx_mono_mi' ? 'BASE' : $gate,
		'gate_metric' => $useAmortGate ? 'amort' : 'wire',
		'wire' => $wire,
		'amort' => $amort,
		'd_amort' => $dAmort,
		'd_wire' => $dWire,
		'meta' => $meta,
		'detail' => sprintf(
			'wire=%s amort=%s Δwire=%+d Δamort=%+d meta=%s hits=%s gate=%s',
			number_format($wire),
			number_format($amort),
			$dWire,
			$dAmort,
			number_format($meta),
			number_format((int) ($row['bigram_hits'] ?? 0)),
			$useAmortGate ? 'amort' : 'wire'
		),
	);
}

usort($rows, static fn (array $a, array $b): int => $a['d_amort'] <=> $b['d_amort']);

printf("bench_stat_pred_wire_gate | @%dp | mono_mi wire=%s amort=%s\n\n",
	$pages, number_format($baseWire), number_format($baseAmort));
printf("%-52s %6s %s\n", 'CASE', 'GATE', 'DETAIL');
printf("%'-52s %6s %s\n", '', '', '');

$pass = 0;
$fail = 0;
foreach ($rows as $r) {
	printf("%-52s %6s %s\n", $r['label'], $r['gate'], $r['detail']);
	if ($r['gate'] === 'PASS') {
		$pass++;
	} elseif ($r['gate'] === 'FAIL') {
		$fail++;
	}
}

if ($pages < 256) {
	fwrite(STDERR, "note: amortized GATE at {$pages}p can flip vs 384p+ (meta tax scales with slice/full_pages)\n");
}

$best = null;
foreach ($rows as $r) {
	if ($r['label'] === 'split_inner_fztx_mono_mi') {
		continue;
	}
	if ($best === null || $r['d_amort'] < $best['d_amort']) {
		$best = $r;
	}
}
if ($best !== null) {
	printf("\nbest vs mono_mi: %s Δamort=%+d (%s)\n", $best['label'], (int) $best['d_amort'], $best['gate']);
}

$archive = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.stat_pred_wire_gate_' . $pages . 'p.json';
file_put_contents($archive, json_encode(array(
	'generated' => date('c'),
	'pages' => $pages,
	'mono_mi_amort' => $baseAmort,
	'rows' => $rows,
), JSON_PRETTY_PRINT));

printf("\n→ %s\n", $archive);
fwrite(STDERR, "OK bench_stat_pred_wire_gate (wire FAIL={$fail})\n");
