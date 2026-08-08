#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Ranked Beat-15M combo wire probes vs mono_mi baseline @384p.
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/bench_enwik8_beat15m_combo_probe.php [--pages=384]
 */

$repo = dirname(__DIR__);
$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
}

$cases = array(
	'split_inner_fztx_mono_mi',
	'split_inner_fztx_mono_mi_wrt',
	'split_inner_fztx_mono_mi_stat_wrt',
	'split_inner_fztx_mono_mi_stat_isp',
	'split_inner_fztx_mono_mi_stat_pred',
	'split_inner_fztx_mono_mi_siteinfo',
	'split_inner_fztx_mono_mi_stack_zpaq_outer',
);
$list = implode(',', $cases);
$probe = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_enwik8_wire_slice_probe.php';
exec(sprintf(
	'php -d memory_limit=2048M %s --pages=%d --cases=%s 2>&1',
	escapeshellarg($probe),
	$pageLimit,
	$list
), $out, $code);
if ($code !== 0) {
	fwrite(STDERR, implode("\n", $out) . "\n");
	exit(1);
}

$jsonPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json';
$dec = json_decode((string) file_get_contents($jsonPath), true);
if (!is_array($dec) || (int) ($dec['pages'] ?? 0) !== $pageLimit) {
	fwrite(STDERR, "wire probe pages mismatch (expected {$pageLimit})\n");
	exit(1);
}
$rows = is_array($dec['rows'] ?? null) ? $dec['rows'] : array();
$byLabel = array();
foreach ($rows as $r) {
	$byLabel[(string) ($r['label'] ?? '')] = $r;
}
$base = (int) ($byLabel['split_inner_fztx_mono_mi']['fzc_bytes'] ?? 0);
$ranked = array();
foreach ($cases as $c) {
	if (!isset($byLabel[$c])) {
		continue;
	}
	$r = $byLabel[$c];
	$bytes = (int) ($r['fzc_bytes'] ?? 0);
	$ranked[] = array(
		'label' => $c,
		'fzc_bytes' => $bytes,
		'delta_vs_mono_mi' => $base > 0 ? $bytes - $base : null,
		'zip_seconds' => $r['zip_seconds'] ?? null,
		'outer_codec' => $r['outer_codec'] ?? null,
		'error' => $r['error'] ?? null,
	);
}
usort($ranked, static fn (array $a, array $b): int => ((int) ($a['fzc_bytes'] ?? PHP_INT_MAX)) <=> ((int) ($b['fzc_bytes'] ?? PHP_INT_MAX)));

$report = array(
	'generated' => date('c'),
	'pages' => $pageLimit,
	'mono_mi_bytes' => $base,
	'ranked' => $ranked,
	'best_label' => $ranked[0]['label'] ?? null,
	'best_beats_mono' => ($base > 0 && isset($ranked[0]['fzc_bytes']) && (int) $ranked[0]['fzc_bytes'] < $base),
);
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_beat15m_combo_probe.json';
file_put_contents($outPath, json_encode($report, JSON_PRETTY_PRINT));

echo "beat15m combo probe → {$outPath}\n";
echo '  mono_mi: ' . number_format($base) . " B\n";
foreach ($ranked as $r) {
	$d = $r['delta_vs_mono_mi'];
	echo '  ' . $r['label'] . '  ' . number_format((int) $r['fzc_bytes'])
		. ($d !== null ? '  (' . ($d >= 0 ? '+' : '') . number_format((int) $d) . ')' : '') . "\n";
}
exit($report['best_beats_mono'] ? 0 : 2);
