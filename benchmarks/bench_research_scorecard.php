#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Unified research scorecard — one sign convention everywhere.
 *
 *   Δ = method_bytes − baseline_bytes
 *   Δ < 0  → fewer bytes (WIN)
 *   Δ > 0  → more bytes (LOSS)
 *
 * Amortized wire (stat_pred): projects slice meta to full 12,041-page corpus:
 *   amort = wire_fzc − meta + meta × (slice_pages / 12041)
 *   Δ_amort = amort − mono_mi_amort
 *
 * Usage: php benchmarks/bench_research_scorecard.php
 */

$repo = dirname(__DIR__);

$row = static function (
	string $group,
	string $method,
	int $baseline,
	int $result,
	string $note = ''
): array {
	$delta = $result - $baseline;
	return array(
		'group' => $group,
		'method' => $method,
		'baseline' => $baseline,
		'result' => $result,
		'delta' => $delta,
		'verdict' => $delta < 0 ? 'WIN' : ($delta === 0 ? 'TIE' : 'LOSS'),
		'note' => $note,
	);
};

$rows = array();

// --- @96p enwik text slice (185,341 B article text) ---

$gz9Text = 70356;
$rows[] = $row('gz9 pre-transform @96p', 'baseline gz9(raw text)', $gz9Text, $gz9Text, '185,341 B text');
$rows[] = $row('gz9 pre-transform @96p', 'template_ifs', $gz9Text, 71388);
$rows[] = $row('gz9 pre-transform @96p', 'stat_pred frozen FZPM (gz9 proxy)', $gz9Text, 73209);
$rows[] = $row('gz9 pre-transform @96p', 'spiral_inner v5 reparse', $gz9Text, 103928);
$rows[] = $row('gz9 pre-transform @96p', 'stat_isp frozen vocab', $gz9Text, 109478);
$rows[] = $row('gz9 pre-transform @96p', 'FZSPL member fold (gz9 only)', $gz9Text, 103884, 'split sidecar+payload was 103,928');

$cmixRaw = 183330;
$rows[] = $row('parallel_cmix @96p', 'baseline parallel_cmix(raw text)', $cmixRaw, $cmixRaw, 'compresses 185,341 B text');
$rows[] = $row('parallel_cmix @96p', 'cmix + FZBG v1 (spiral o1 seed)', $cmixRaw, 183774, 'BAD: +444 B vs cmix');
$rows[] = $row('parallel_cmix @96p', 'cmix + FZBG v2 (spiral o1+o2 seed)', $cmixRaw, 184431);
$rows[] = $row('parallel_cmix @96p', 'cmix + qg-corrected FZBG v2', $cmixRaw, 184115);
$rows[] = $row('parallel_cmix @96p', 'cmix + qg syntax-tag FZBG v2', $cmixRaw, 184261);
$rows[] = $row('parallel_cmix @96p', 'cmix + qg boundary-merge FZBG v2', $cmixRaw, 184432);

$rows[] = $row('parallel_cmix @96p', 'baseline gz9(raw text)', $gz9Text, $gz9Text);
$rows[] = $row('parallel_cmix @96p', 'cmix(raw text)', $gz9Text, 166038, 'vs gz9 baseline');
$rows[] = $row('parallel_cmix @96p', 'cmix(SPRL v5 residuals)', $gz9Text, 166038, 'residual stream; vs gz9(raw)');

$rows[] = $row('parallel_cmix @96p', 'gz9(SPRL v5 stream)', $gz9Text, 94609);

$qgSlice = 48739; // ~130 KiB slice from bench_qg_spiral gate
$rows[] = $row('qg cipher gz9 (~130 KiB slice)', 'baseline gz9(raw text)', $qgSlice, $qgSlice);
$rows[] = $row('qg cipher gz9 (~130 KiB slice)', 'gz9(qg+spiral residual)', $qgSlice, 66410, 'from research gate');

// --- Wire path @384p (real .fz, zpaq outer) ---

$mono384 = 651650;
$rows[] = $row('wire @384p', 'baseline mono_mi (wire .fz)', $mono384, $mono384);
$rows[] = $row('wire @384p', 'mono_concat layout only', $mono384, 657102);
$rows[] = $row('wire @384p', 'mono_concat + stat_pred_inner (wire slice)', $mono384, 863922, 'actual bytes if you encode 384p slice');
$rows[] = $row('wire @384p', 'mono_concat + stat_pred_inner (amortized)', $mono384, 658351, 'projects meta to full enwik8; use for promotion');
$rows[] = $row('wire @384p', 'stat_pred member+outer only (no meta)', $mono384, 651579, '651,650 − 71 B vs mono_mi');
$rows[] = $row('wire @384p', 'phda9_xml pp96 parallel (integrated .fz)', $mono384, 560162, 'RT ok; promoted production path');
$rows[] = $row('wire @384p', 'phda9_xml single-stream (integrated .fz)', $mono384, 524040, 'RT ok; fewer bytes, faster encode');

// --- Wire path @96p (amort trap) ---

$mono96 = 56259;
$rows[] = $row('wire @96p', 'baseline mono_mi (wire .fz)', $mono96, $mono96);
$rows[] = $row('wire @96p', 'phda9_xml pp96 parallel (integrated .fz)', $mono96, 42795, 'RT ok; bench_hutter_wire_gate @96p');
$rows[] = $row('wire @96p', 'phda9_xml single-stream (integrated .fz)', $mono96, 42795, 'RT ok; bench_hutter_wire_gate @96p');
$rows[] = $row('wire @96p', 'phda9_xml single-stream LSTM (lab)', $mono96, 42804, 'Δ +9 vs no_lstm @96p — keep no_lstm');
$rows[] = $row('wire @96p', 'mono_concat + stat_pred_inner (wire slice)', $mono96, 81350, 'LOSS +25,091 on slice');
$rows[] = $row('wire @96p', 'mono_concat + stat_pred_inner (amortized)', $mono96, 39893, 'MISLEADING WIN at small slice — ignore for promotion');

// --- phda9 / codecs @96p ---

$text96 = 185341;
$gzipText = 70356;
$rows[] = $row('codec lab @96p', 'baseline gzip9(sorted article text)', $gzipText, $gzipText, '185,341 B text');
$rows[] = $row('codec lab @96p', 'phda9_no_lstm(sorted article text)', $gzipText, 39234);
$rows[] = $row('codec lab @96p', 'paq8px(raw sorted text)', $gzipText, 44622, 'from paq_on_inner_quick');
$rows[] = $row('codec lab @96p', 'phda9(raw sorted text)', $gzipText, 39221);

$inner96 = 223877;
$zpaqInner = 54350;
$rows[] = $row('codec lab @96p', 'baseline zpaq_m9(fztx_inner blob)', $zpaqInner, $zpaqInner, '223,877 B mono_mi inner');
$rows[] = $row('codec lab @96p', 'paq8px(fztx_inner blob)', $zpaqInner, 46411);
$rows[] = $row('codec lab @96p', 'phda9(fztx_inner blob)', $zpaqInner, PHP_INT_MAX, 'SEGFAULT ~28s — binary FZTX not English');

// --- bpc (informational; not byte Δ) ---

printf("RESEARCH SCORECARD — sign convention\n");
printf("  Δ = result − baseline\n");
printf("  Δ < 0  WIN (fewer bytes)    Δ > 0  LOSS (more bytes)\n");
printf("  Corpus: enwik8 text unless noted. Dates: 2026-06-16 probes.\n\n");

$lastGroup = '';
printf("%-28s %-38s %10s %10s %10s %5s  %s\n",
	'GROUP', 'METHOD', 'BASELINE', 'RESULT', 'Δ', ' ', 'NOTE');
printf("%'-28s %-38s %10s %10s %10s %5s  %s\n", '', '', '', '', '', '', '');

foreach ($rows as $r) {
	if ($r['group'] !== $lastGroup) {
		if ($lastGroup !== '') {
			echo "\n";
		}
		$lastGroup = $r['group'];
	}
	$base = $r['baseline'] === PHP_INT_MAX ? 'n/a' : number_format($r['baseline']);
	$res = $r['result'] === PHP_INT_MAX ? 'CRASH' : number_format($r['result']);
	$delta = $r['result'] === PHP_INT_MAX ? 'n/a' : sprintf('%+d', $r['delta']);
	printf("%-28s %-38s %10s %10s %10s %5s  %s\n",
		$r['group'],
		$r['method'],
		$base,
		$res,
		$delta,
		$r['verdict'],
		$r['note']
	);
}

printf("\n--- FZBG clarified ---\n");
printf("FZBG is NOT good. Seeding parallel_cmix with spiral/qg byte tables adds bytes:\n");
printf("  cmix alone     = 183,330 B\n");
printf("  cmix + FZBG v1 = 183,774 B   Δ = +444 B  (LOSS)\n");
printf("  cmix + FZBG v2 = 184,431 B   Δ = +1,101 B (LOSS)\n");
printf("Older benches printed \"saves vs cmix: -444\" using inverted (baseline−result).\n");
printf("Negative \"saves\" meant the seed made the output LARGER. Confusing — fixed in seed benches.\n\n");

printf("--- stat_pred wire clarified ---\n");
printf("@384p promotion metric (amortized): stat_pred = 658,351 vs mono_mi = 651,650 → Δ = +6,701 B (LOSS)\n");
printf("@96p slice wire (honest):            stat_pred =  81,350 vs mono_mi =  56,259 → Δ = +25,091 B (LOSS)\n");
printf("@96p amortized (do NOT use):         stat_pred =  39,893 vs mono_mi =  56,259 → Δ = −16,366 B (artifact)\n\n");

printf("--- Hutter integrated path (phda9_xml wire @384p) ---\n");
printf("  mono_mi fztx+zpaq     = 651,650 B  (baseline)\n");
printf("  phda9_xml pp96 parallel = 560,162 B  Δ = −91,488 B  (WIN — production promoted path)\n");
printf("  phda9_xml single-stream = 524,040 B  Δ = −127,610 B  (WIN — faster encode, fewer bytes)\n");
printf("  phda9 on fztx inner blob = SEGFAULT (wrong input — use phda9_xml format instead)\n\n");
printf("  phda9 on plain sorted article text vs gzip9 @96p: 39,234 vs 70,356 → Δ = −31,122 B (WIN, lab only)\n");
printf("  stat_pred member+outer @384p vs mono_mi: 651,579 vs 651,650 → Δ = −71 B (tiny WIN, wiped by meta tax)\n");
printf("  multifractal.txt spiral bpc vs bigram: ~+6.4%% reduction (niche corpus only; enwik FAIL)\n\n");

fwrite(STDERR, "OK bench_research_scorecard\n");
