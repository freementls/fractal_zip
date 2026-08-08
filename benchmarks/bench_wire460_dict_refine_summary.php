#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Summarize wire460 dict refine @Np with honest wire + amortized metrics.
 *
 * Usage: php benchmarks/bench_wire460_dict_refine_summary.php [--pages=96]
 */

$repo = dirname(__DIR__);
$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$fullPages = 12041;
$refineJson = $repo . '/benchmarks/.enwik8_phda9_dict_lstm_seed_refine_' . $pages . 'p.json';
$baselineLabel = 'split_inner_phda9_xml_single_stream_lstm_words4096_dict';
$refinedLabel = 'split_inner_phda9_xml_single_stream_lstm_refined_dict';

$refine = is_file($refineJson) ? json_decode((string) file_get_contents($refineJson), true) : null;
$outers = array('zstd', 'zpaq', 'auto');
$wireRows = array();
foreach ($outers as $outer) {
	$path = $repo . '/benchmarks/.enwik8_wire460_dict_refine_' . $pages . 'p_' . $outer . '.json';
	if (!is_file($path)) {
		continue;
	}
	$j = json_decode((string) file_get_contents($path), true);
	if (!is_array($j)) {
		continue;
	}
	foreach ($j['rows'] ?? array() as $row) {
		if (!empty($row['error'])) {
			continue;
		}
		$row['_outer'] = $outer;
		$row['_source'] = basename($path);
		$wireRows[] = $row;
	}
}

$pick = static function (string $label) use ($wireRows): ?array {
	$best = null;
	foreach ($wireRows as $row) {
		if ((string) ($row['label'] ?? '') !== $label) {
			continue;
		}
		$w = (int) ($row['fzc_bytes'] ?? 0);
		if ($w <= 0) {
			continue;
		}
		if ($best === null || $w < (int) ($best['fzc_bytes'] ?? PHP_INT_MAX)) {
			$best = $row;
		}
	}
	return $best;
};

$baseline = $pick($baselineLabel);
$refined = $pick($refinedLabel);

$seedDict = (int) ($refine['seed_dict_bytes'] ?? 0);
$refinedDict = (int) ($refine['refined_dict_bytes'] ?? 0);
if ($seedDict <= 0) {
	$seedPath = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
	if (is_file($seedPath)) {
		$seedDict = (int) filesize($seedPath);
	}
}
if ($refinedDict <= 0) {
	$refPath = $repo . '/benchmarks/.phda9_external_dict_lstm_seed_refine_' . $pages . 'p.txt';
	if (is_file($refPath)) {
		$refinedDict = (int) filesize($refPath);
	}
}
$dictDelta = $refinedDict - $seedDict;
$amortScale = $pages / $fullPages;

$summary = array(
	'generated' => date('c'),
	'pages' => $pages,
	'full_pages' => $fullPages,
	'refine_json' => is_file($refineJson) ? $refineJson : null,
	'refine' => $refine,
	'baseline_case' => $baselineLabel,
	'refined_case' => $refinedLabel,
	'baseline_wire' => $baseline,
	'refined_wire' => $refined,
);

if ($baseline !== null && $refined !== null) {
	$wireDelta = (int) ($refined['fzc_bytes'] ?? 0) - (int) ($baseline['fzc_bytes'] ?? 0);
	$amortDelta = (int) ($refined['amortized_fzc'] ?? 0) - (int) ($baseline['amortized_fzc'] ?? 0);
	$netAmortWireDelta = $wireDelta + (int) round($dictDelta * ($amortScale - 1.0));
	$summary['delta'] = array(
		'wire_fzc' => $wireDelta,
		'amortized_fzc' => $amortDelta,
		'net_amortized_wire_fzc' => $netAmortWireDelta,
		'dict_delta_bytes' => $dictDelta,
		'amortized_dict_tax_delta' => (int) round($dictDelta * $amortScale),
		'amortized_model' => 'Δwire + dict_delta*(slice/full-1); probe amortized_fzc uses meta|fold|phda9_dict tax',
		'pass_honest_wire' => $wireDelta < 0,
		'pass_amortized_wire' => $amortDelta < 0,
		'pass_net_amortized' => $netAmortWireDelta < 0,
	);
}

$out = $repo . '/benchmarks/.enwik8_wire460_dict_refine_summary_' . $pages . 'p.json';
file_put_contents($out, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "wire460 dict refine summary @{$pages}p\n";
if (is_array($refine)) {
	printf("  refine FZPA: base=%s refined=%s Δ=%s net_amort_fzpa_Δ=%s\n",
		number_format((int) ($refine['base_fzpa'] ?? 0)),
		number_format((int) ($refine['refined_fzpa'] ?? 0)),
		isset($refine['delta']) ? sprintf('%+d', (int) $refine['delta']) : '?',
		isset($refine['net_amortized_fzpa_delta']) ? sprintf('%+d', (int) $refine['net_amortized_fzpa_delta']) : '?'
	);
	printf("  dict file: seed=%s refined=%s Δ=%+d (amort @%dp=%s)\n",
		number_format($seedDict),
		number_format($refinedDict),
		$dictDelta,
		$pages,
		number_format((int) round($refinedDict * $amortScale))
	);
}
if (isset($summary['delta'])) {
	$d = $summary['delta'];
	printf("  wire probe (best outer each): Δwire=%+d Δamort=%+d net_amort=%+d pass=%s\n",
		(int) $d['wire_fzc'],
		(int) $d['amortized_fzc'],
		(int) $d['net_amortized_wire_fzc'],
		!empty($d['pass_net_amortized']) ? 'YES' : 'no'
	);
} else {
	echo "  wire probe: pending (need baseline + refined rows)\n";
}
echo "  json → {$out}\n";
