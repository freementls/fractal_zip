#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Beat-15M integrated path: mono_mi text-inner + optional corpus phrases (no stat_pred meta tax).
 *
 * Rationale (2026-06-09):
 * - Verified integrated best: phda9_xml @ 18,848,115 B full wire (−359 KiB vs mono_mi).
 * - Prior best: mono_mi @ 19,207,083 B full wire (−387 KiB vs pp96).
 * - stat_pred_inner fails 384p amort gate (+6.7 KiB meta tax) — not promoted.
 * - phda9 ~15.01 MiB is reference-only (raw order), not sorted .fz promotion.
 * - corpus_phrases shrinks pre-zpaq raw ~2.8 MiB (gzip proxy) with ~4.7 KiB dict — zero amort sidecar.
 *
 * Usage:
 *   php -d memory_limit=4096M benchmarks/run_enwik8_beat15m_path.php [--pages=384] [--skip-probe]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_enwik8_full_encode_gate.php';

const BEAT15M_HUTTER = 15284944;
const BEAT15M_BEST_INTEGRATED = 18848115;
const BEAT15M_MONO_MI_384P = 651650;
const BEAT15M_FULL_PAGES = 12041;

$pageLimit = 384;
$skipProbe = false;
$probeJsonOverride = null;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	} elseif ($arg === '--skip-probe') {
		$skipProbe = true;
	} elseif (str_starts_with($arg, '--probe-json=')) {
		$probeJsonOverride = substr($arg, 13);
		$skipProbe = true;
	}
}

$cases = array(
	'no_textcodec',
	'split_inner_fztx_mono_mi',
	'split_inner_fztx_mono_mi_corpus',
	'split_inner_fztx_mono_concat_stat_pred_inner',
);
$caseList = implode(',', $cases);

if (!$skipProbe) {
	$probe = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_enwik8_wire_slice_probe.php';
	$cmd = sprintf(
		'php -d memory_limit=4096M %s --pages=%d --cases=%s 2>&1',
		escapeshellarg($probe),
		$pageLimit,
		$caseList
	);
	echo "Running wire probe ({$pageLimit}p): {$caseList}\n";
	passthru($cmd, $code);
	if ($code !== 0) {
		fwrite(STDERR, "wire probe failed exit={$code}\n");
		exit(1);
	}
}

$jsonPath = $probeJsonOverride !== null && $probeJsonOverride !== ''
	? (str_starts_with($probeJsonOverride, '/') ? $probeJsonOverride : $repo . DIRECTORY_SEPARATOR . $probeJsonOverride)
	: $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json';
$dec = json_decode((string) file_get_contents($jsonPath), true);
if (!is_array($dec) || (int) ($dec['pages'] ?? 0) !== $pageLimit) {
	fwrite(STDERR, "probe JSON missing or pages mismatch ({$jsonPath})\n");
	exit(1);
}

$byLabel = array();
foreach ((array) ($dec['rows'] ?? array()) as $row) {
	if (!is_array($row)) {
		continue;
	}
	$byLabel[(string) ($row['label'] ?? '')] = $row;
}

$baseline = (int) ($byLabel['no_textcodec']['wire_fzc'] ?? $byLabel['no_textcodec']['fzc_bytes'] ?? 0);
$monoMi = (int) ($byLabel['split_inner_fztx_mono_mi']['wire_fzc'] ?? $byLabel['split_inner_fztx_mono_mi']['fzc_bytes'] ?? 0);
$monoCorpus = (int) ($byLabel['split_inner_fztx_mono_mi_corpus']['wire_fzc'] ?? $byLabel['split_inner_fztx_mono_mi_corpus']['fzc_bytes'] ?? 0);

$extrap = static function (int $sliceBytes) use ($pageLimit): int {
	if ($sliceBytes <= 0 || $pageLimit <= 0) {
		return 0;
	}
	return (int) round(BEAT15M_BEST_INTEGRATED * ($sliceBytes / BEAT15M_MONO_MI_384P));
};

$rows = array();
foreach ($cases as $c) {
	if (!isset($byLabel[$c])) {
		continue;
	}
	$r = $byLabel[$c];
	$wire = (int) ($r['wire_fzc'] ?? $r['fzc_bytes'] ?? 0);
	$meta = (int) ($r['meta_bytes'] ?? 0);
	$memberOuter = (int) ($r['member_plus_outer'] ?? ($wire > 0 && $meta > 0 ? $wire - $meta : $wire));
	$amort = (int) ($r['amortized_fzc'] ?? $wire);
	$extrapFull = ($c === 'split_inner_fztx_mono_mi' || $c === 'split_inner_fztx_mono_mi_corpus')
		? $extrap($wire) : null;
	// Full-corpus projection: scale slice amortized delta vs mono_mi (meta ships once at full scale).
	$monoAmort = (int) ($byLabel['split_inner_fztx_mono_mi']['amortized_fzc'] ?? $monoMi);
	$fullWireEst = null;
	$fullAmortEst = null;
	if ($monoMi > 0 && $c === 'split_inner_fztx_mono_mi') {
		$fullWireEst = BEAT15M_BEST_INTEGRATED;
		$fullAmortEst = BEAT15M_BEST_INTEGRATED;
	} elseif ($monoMi > 0 && $monoAmort > 0 && $c !== 'no_textcodec') {
		$amortDelta = $amort - $monoAmort;
		$fullAmortEst = (int) round(BEAT15M_BEST_INTEGRATED + $amortDelta);
		// Honest full wire ≈ mono full member + sealed meta (meta ~ slice meta at scale; sidecars grow sub-linearly in seal).
		if ($meta > 0) {
			$fullWireEst = (int) round(BEAT15M_BEST_INTEGRATED + $meta - ($monoMi - $memberOuter));
		}
	}
	$rows[] = array(
		'label' => $c,
		'wire_fzc' => $wire,
		'meta_bytes' => $meta,
		'member_plus_outer' => $memberOuter,
		'amortized_fzc' => $amort,
		'delta_vs_no_textcodec' => $baseline > 0 ? $wire - $baseline : null,
		'delta_vs_mono_mi' => ($c !== 'split_inner_fztx_mono_mi' && $monoMi > 0) ? $wire - $monoMi : null,
		'amort_delta_vs_mono_mi' => ($c !== 'split_inner_fztx_mono_mi' && $monoMi > 0 && isset($byLabel['split_inner_fztx_mono_mi']))
			? $amort - (int) ($byLabel['split_inner_fztx_mono_mi']['amortized_fzc'] ?? $monoMi) : null,
		'extrapolated_full_fzc' => $extrapFull,
		'estimated_full_wire_fzc' => $fullWireEst,
		'estimated_full_amort_fzc' => $fullAmortEst,
		'extrap_delta_vs_best' => $extrapFull !== null ? BEAT15M_BEST_INTEGRATED - $extrapFull : null,
		'extrap_gap_vs_hutter' => $extrapFull !== null ? $extrapFull - BEAT15M_HUTTER : null,
		'full_wire_gap_vs_hutter' => $fullWireEst !== null ? $fullWireEst - BEAT15M_HUTTER : null,
		'full_amort_gap_vs_hutter' => $fullAmortEst !== null ? $fullAmortEst - BEAT15M_HUTTER : null,
		'zip_seconds' => $r['zip_seconds'] ?? null,
		'outer_codec' => $r['outer_codec'] ?? null,
	);
}

usort($rows, static fn (array $a, array $b): int => ((int) ($a['wire_fzc'] ?? PHP_INT_MAX)) <=> ((int) ($b['wire_fzc'] ?? PHP_INT_MAX)));

$gate = enwik8_full_encode_gate_evaluate($monoMi > 0 ? $monoMi : null);

$best = $rows[0] ?? null;
$report = array(
	'generated' => date('c'),
	'path' => 'mono_mi_text_inner + corpus_phrases_candidate',
	'pages' => $pageLimit,
	'hutter_bytes' => BEAT15M_HUTTER,
	'best_integrated_full_fzc' => BEAT15M_BEST_INTEGRATED,
	'mono_mi_384p_ref' => BEAT15M_MONO_MI_384P,
	'rows' => $rows,
	'best_label' => $best['label'] ?? null,
	'best_wire_fzc' => $best['wire_fzc'] ?? null,
	'gate' => $gate,
	'beats_hutter_extrapolated' => false,
	'recommendation' => '',
);

foreach ($rows as $r) {
	$gap = $r['extrap_gap_vs_hutter'] ?? null;
	if ($gap !== null && $gap < 0) {
		$report['beats_hutter_extrapolated'] = true;
		$report['best_extrap_label'] = $r['label'];
		break;
	}
}

if ($monoCorpus > 0 && $monoMi > 0 && $monoCorpus < $monoMi) {
	$report['recommendation'] = 'corpus_phrases beats mono_mi @' . $pageLimit . 'p — run 768p scale check then full encode';
} elseif ($monoMi > 0 && $monoMi <= BEAT15M_MONO_MI_384P) {
	$report['recommendation'] = 'mono_mi still best integrated @' . $pageLimit . 'p; corpus_phrases did not beat mono_mi — keep mono_mi promotion, need new modeling (not stat_pred meta)';
} else {
	$report['recommendation'] = 'mono_mi regressed vs ref — investigate env';
}

$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_beat15m_path.json';
file_put_contents($outPath, json_encode($report, JSON_PRETTY_PRINT));

echo "\nbeat15m path → {$outPath}\n";
echo '  Hutter target: ' . number_format(BEAT15M_HUTTER) . " B\n";
echo '  Best integrated full: ' . number_format(BEAT15M_BEST_INTEGRATED) . " B (gap " . number_format(BEAT15M_BEST_INTEGRATED - BEAT15M_HUTTER) . ")\n\n";
foreach ($rows as $r) {
	echo '  ' . $r['label'] . '  wire=' . number_format((int) $r['wire_fzc']);
	if ((int) ($r['meta_bytes'] ?? 0) > 0) {
		echo '  meta=' . number_format((int) $r['meta_bytes']);
		echo '  member=' . number_format((int) $r['member_plus_outer']);
		echo '  amort=' . number_format((int) $r['amortized_fzc']);
	}
	if ($r['delta_vs_mono_mi'] !== null) {
		echo '  Δwire_mono=' . ($r['delta_vs_mono_mi'] >= 0 ? '+' : '') . number_format((int) $r['delta_vs_mono_mi']);
	}
	if ($r['amort_delta_vs_mono_mi'] !== null) {
		echo '  Δamort=' . ($r['amort_delta_vs_mono_mi'] >= 0 ? '+' : '') . number_format((int) $r['amort_delta_vs_mono_mi']);
	}
	if ($r['estimated_full_amort_fzc'] !== null) {
		echo '  est_full_amort≈' . number_format((int) $r['estimated_full_amort_fzc']);
	}
	if ($r['estimated_full_wire_fzc'] !== null) {
		echo '  est_full_wire≈' . number_format((int) $r['estimated_full_wire_fzc']);
	}
	if ($r['extrapolated_full_fzc'] !== null) {
		echo '  extrap_full≈' . number_format((int) $r['extrapolated_full_fzc']);
	}
	echo "\n";
}
echo "\n  gate: " . ($gate['allow_full_encode'] ? 'ALLOW' : 'BLOCK') . ' — ' . ($gate['reason'] ?? '') . "\n";
echo '  → ' . $report['recommendation'] . "\n";

exit($report['beats_hutter_extrapolated'] ? 0 : 2);
