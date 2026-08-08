#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Quick wire460 scoreboard: best @384p vs 460,096 B target.
 *
 * Usage: php benchmarks/wire460_status.php
 */

$repo = dirname(__DIR__);
require_once $repo . '/benchmarks/enwik8_beat146_gate.php';

$target = 460096;
$budget = enwik8_beat146_wire_budget_384p();
$candidates = array(
	$repo . '/benchmarks/.enwik8_wire460_parallel_status.json',
	$repo . '/benchmarks/.enwik8_wire_slice_probe_384p_phda9.json',
	$repo . '/benchmarks/.enwik8_wire_slice_probe_384p_baseline_words4096.json',
	$repo . '/benchmarks/.enwik8_wire460_baseline384p_rerun.json',
	$repo . '/benchmarks/.enwik8_wire460_384p_newwork.json',
);

$bestWire = PHP_INT_MAX;
$bestLabel = '';
$bestPath = '';
foreach ($candidates as $path) {
	if (!is_file($path)) {
		continue;
	}
	$j = json_decode((string) file_get_contents($path), true);
	if (!is_array($j)) {
		continue;
	}
	if (isset($j['best']) && is_string($j['best']) && preg_match('/(\d+)\s*$/', $j['best'], $m)) {
		$w = (int) $m[1];
		if ($w > 0 && $w < $bestWire) {
			$bestWire = $w;
			$bestLabel = (string) $j['best'];
			$bestPath = $path;
		}
	}
	foreach ($j['rows'] ?? array() as $row) {
		if (!empty($row['error'])) {
			continue;
		}
		$w = (int) ($row['fzc_bytes'] ?? $row['wire_384p'] ?? 0);
		if ($w > 0 && $w < $bestWire) {
			$bestWire = $w;
			$bestLabel = (string) ($row['label'] ?? '?');
			$bestPath = $path;
		}
	}
}

if ($bestWire === PHP_INT_MAX) {
	echo "wire460_status: no probe data found\n";
	exit(1);
}

$gapTarget = $bestWire - $target;
$gapBudget = $bestWire - (int) $budget['wire_budget_384p'];

printf(
	"wire460 @384p\n  best: %s B (%s)\n  target: %s B (Δ=%+d)\n  budget: %s B (Δ=%+d)\n  source: %s\n",
	number_format($bestWire),
	$bestLabel,
	number_format($target),
	$gapTarget,
	number_format((int) $budget['wire_budget_384p']),
	$gapBudget,
	basename($bestPath)
);
