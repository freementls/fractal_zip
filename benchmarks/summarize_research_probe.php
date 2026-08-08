#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Summarize wire slice probe JSON with beat-14.6 gate extrapolation (total S).
 *
 * Usage:
 *   php benchmarks/summarize_research_probe.php
 *   php benchmarks/summarize_research_probe.php --json=benchmarks/.enwik8_wire_slice_probe.json --pages=96
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_beat146_gate.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_hutter_prize.php';

$jsonPath = $repo . '/benchmarks/.enwik8_wire_slice_probe.json';
$pages = 96;
$targetMode = ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--json=')) {
		$jsonPath = substr($arg, 7);
	} elseif (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

if (!is_file($jsonPath)) {
	fwrite(STDERR, "Missing {$jsonPath}\n");
	exit(1);
}
$data = json_decode((string) file_get_contents($jsonPath), true);
$rows = is_array($data) ? ($data['rows'] ?? array()) : array();
$mono = (int) ($data['mono_mi_bytes'] ?? 0);
$budget = enwik8_hutter_archive_budget_for_total_s(ENWIK8_CMIX_ARCHIVE_LAB, $repo);
$wireBudget = enwik8_beat146_wire_budget_384p(ENWIK8_BEAT146_EXTRAP_MARGIN, $targetMode, $repo);
$partial = !empty($data['partial']);

printf("research probe summary | @%dp | mono_mi=%s%s\n", $pages, number_format($mono), $partial ? ' (partial)' : '');
printf("  beat146 total S target %s B | archive budget %s B\n",
	number_format(ENWIK8_CMIX_ARCHIVE_LAB),
	number_format($budget['archive_budget'])
);
printf("  split tax (2×decomp+dict) %s B | @384p wire budget for gate %s B\n\n",
	number_format($wireBudget['split_tax']),
	number_format($wireBudget['wire_budget_384p'])
);
printf("%-42s %10s %10s %10s %10s %12s %5s %4s\n", 'CASE', 'wire', 'wire@384p', 'extrap_arc', 'extrap_S', 'Δ vs tgt', 'GATE', 'RT');
printf("%'-42s %10s %10s %10s %10s %12s %5s %4s\n", '', '', '', '', '', '', '', '');

$ranked = array();
foreach ($rows as $row) {
	if (!empty($row['error'])) {
		continue;
	}
	$wire = (int) ($row['fzc_bytes'] ?? 0);
	if ($wire <= 0) {
		continue;
	}
	$slicePages = max(1, (int) ($row['pages'] ?? $pages));
	$wire384 = $slicePages === 384 ? $wire : (int) round($wire * 384.0 / $slicePages);
	$gate = enwik8_beat146_gate_evaluate($wire384, 384, null, ENWIK8_BEAT146_BEST_INTEGRATED, ENWIK8_BEAT146_BASELINE_384P, null, ENWIK8_BEAT146_EXTRAP_MARGIN, ENWIK8_BEAT146_FULL_PAGES, $targetMode, $repo);
	if ($slicePages < 384) {
		$gate['allow_full_encode'] = false;
		$gate['reason'] = sprintf('@%dp hint only (wire@384p=%s) — need @384p slice for gate', $slicePages, number_format($wire384));
	}
	$ranked[] = array('row' => $row, 'gate' => $gate, 'wire' => $wire, 'wire384' => $wire384, 'slice_pages' => $slicePages);
}
usort($ranked, static fn (array $a, array $b): int => ($a['gate']['compare_bytes'] ?? PHP_INT_MAX) <=> ($b['gate']['compare_bytes'] ?? PHP_INT_MAX));

foreach ($ranked as $item) {
	$row = $item['row'];
	$g = $item['gate'];
	$label = (string) ($row['label'] ?? '?');
	$cmp = (int) ($g['compare_bytes'] ?? 0);
	$delta = (int) ($g['delta_vs_target'] ?? 0);
	$rt = $row['roundtrip_ok'] ?? null;
	$rtCol = $rt === null ? '?' : ($rt ? 'ok' : 'no');
	printf("%-42s %10s %10s %10s %10s %12s %5s %4s\n",
		$label,
		number_format($item['wire']),
		number_format($item['wire384']),
		number_format((int) ($g['extrap_archive'] ?? 0)),
		number_format($cmp),
		($delta <= 0 ? '' : '+') . number_format($delta),
		!empty($g['allow_full_encode']) ? 'PASS' : ($item['slice_pages'] < 384 ? 'hint' : 'FAIL'),
		$rtCol
	);
}

if ($ranked !== array()) {
	$best = $ranked[0];
	printf("\nbest: %s — %s\n", $best['row']['label'] ?? '?', $best['gate']['reason'] ?? '');
	$need = max(0, (int) ($best['gate']['delta_vs_target'] ?? 0) + ENWIK8_BEAT146_EXTRAP_MARGIN);
	$slicePages = max(1, (int) ($best['slice_pages'] ?? $pages));
	$perPage = (int) ceil($need * $slicePages / ENWIK8_BEAT146_FULL_PAGES);
	printf("  need ~%s B more total S (~%s B @%dp slice for margin)\n", number_format($need), number_format($perPage), $slicePages);
	if ($slicePages >= 384) {
		$gap = (int) $best['wire384'] - (int) $wireBudget['wire_budget_384p'];
		if ($gap > 0) {
			printf("  @384p wire %s B is +%s B over gate budget %s B\n",
				number_format($best['wire384']),
				number_format($gap),
				number_format($wireBudget['wire_budget_384p'])
			);
		}
	}
}
exit($ranked !== array() && !empty($ranked[0]['gate']['allow_full_encode']) ? 0 : 1);
