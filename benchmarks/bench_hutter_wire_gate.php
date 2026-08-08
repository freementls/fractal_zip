#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Hutter-path wire GATE: mono_mi (fztx+zpaq) vs phda9_xml integrated paths.
 *
 *   Δ = result_wire_bytes − mono_mi_wire_bytes
 *   Δ < 0  WIN (fewer bytes)
 *   Δ > 0  LOSS (more bytes)
 *
 * Usage:
 *   nice -n 19 php -d memory_limit=2048M benchmarks/bench_hutter_wire_gate.php [--pages=96|384] [--with-lstm]
 */

$repo = dirname(__DIR__);
$pages = 96;
$withLstm = in_array('--with-lstm', $argv, true);
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$caseList = array(
	'split_inner_fztx_mono_mi',
	'split_inner_phda9_xml_pp96_parallel',
	'split_inner_phda9_xml_single_stream_pp96',
);
if ($withLstm) {
	$caseList[] = 'split_inner_phda9_xml_single_stream_lstm';
}
$cases = implode(',', $caseList);

$labels = array(
	'split_inner_fztx_mono_mi' => 'mono_mi (fztx+zpaq baseline)',
	'split_inner_phda9_xml_pp96_parallel' => 'phda9_xml pp96 parallel (fallback)',
	'split_inner_phda9_xml_single_stream_pp96' => 'phda9_xml single-stream no_lstm (production)',
	'split_inner_phda9_xml_single_stream_lstm' => 'phda9_xml single-stream LSTM (lab)',
);

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

$data = json_decode((string) file_get_contents($jsonOut), true);
if (!is_array($data) || !is_array($data['rows'] ?? null)) {
	fwrite(STDERR, "bad probe json\n");
	exit(1);
}

$baseWire = null;
$rows = array();
foreach ($data['rows'] as $row) {
	if (($row['label'] ?? '') === 'split_inner_fztx_mono_mi' && empty($row['error'])) {
		$baseWire = (int) ($row['fzc_bytes'] ?? 0);
	}
}
if ($baseWire === null) {
	fwrite(STDERR, "mono_mi baseline missing\n");
	exit(1);
}

foreach ($data['rows'] as $row) {
	$caseId = (string) ($row['label'] ?? '?');
	$label = $labels[$caseId] ?? $caseId;
	if (!empty($row['error'])) {
		$rows[] = array(
			'label' => $label,
			'case_id' => $caseId,
			'wire' => 0,
			'delta' => 0,
			'gate' => 'FAIL',
			'detail' => (string) $row['error'],
		);
		continue;
	}
	$wire = (int) ($row['fzc_bytes'] ?? 0);
	$delta = $wire - $baseWire;
	$rows[] = array(
		'label' => $label,
		'case_id' => $caseId,
		'wire' => $wire,
		'meta' => (int) ($row['meta_bytes'] ?? 0),
		'amort' => (int) ($row['amortized_fzc'] ?? $wire),
		'delta' => $delta,
		'gate' => $caseId === 'split_inner_fztx_mono_mi' ? 'BASE' : ($delta < 0 ? 'PASS' : 'FAIL'),
		'sec' => (float) ($row['zip_seconds'] ?? 0),
		'detail' => sprintf('wire=%s Δ=%+d meta=%s sec=%.1f',
			number_format($wire), $delta, number_format((int) ($row['meta_bytes'] ?? 0)),
			(float) ($row['zip_seconds'] ?? 0)),
	);
}

usort($rows, static fn (array $a, array $b): int => $a['delta'] <=> $b['delta']);

printf("bench_hutter_wire_gate | @%dp | mono_mi wire=%s\n\n", $pages, number_format($baseWire));
printf("  Δ = wire − mono_mi   (negative = fewer bytes = WIN)\n\n");
printf("%-48s %6s %s\n", 'CASE', 'GATE', 'DETAIL');
printf("%'-48s %6s %s\n", '', '', '');

foreach ($rows as $r) {
	printf("%-48s %6s %s\n", $r['label'], $r['gate'], $r['detail']);
}

$best = null;
foreach ($rows as $r) {
	if ($r['gate'] === 'BASE') {
		continue;
	}
	if ($best === null || $r['delta'] < $best['delta']) {
		$best = $r;
	}
}
if ($best !== null) {
	printf("\nbest vs mono_mi: %s Δ=%+d (%s)\n", $best['label'], (int) $best['delta'], $best['gate']);
}

$archive = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.hutter_wire_gate_' . $pages . 'p.json';
file_put_contents($archive, json_encode(array(
	'generated' => date('c'),
	'pages' => $pages,
	'with_lstm' => $withLstm,
	'mono_mi_wire' => $baseWire,
	'rows' => $rows,
), JSON_PRETTY_PRINT));
printf("\n→ %s\n", $archive);
fwrite(STDERR, "OK bench_hutter_wire_gate\n");
