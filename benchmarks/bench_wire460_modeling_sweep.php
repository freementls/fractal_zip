#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Non-dict modeling sweep on the words4096 + phda9_xml LSTM stack (no dict fold).
 *
 * Usage:
 *   php benchmarks/bench_wire460_modeling_sweep.php [--pages=96|384] [--cases=...]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repo = dirname(__DIR__);
$pages = 96;
$caseFilter = '';
$outJson = '';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--cases=')) {
		$caseFilter = substr($arg, 8);
	} elseif (str_starts_with($arg, '--out=')) {
		$outJson = substr($arg, 6);
	}
}

$defaultCases = array(
	'split_inner_phda9_xml_single_stream_lstm_words4096_dict',
	'split_inner_phda9_xml_single_stream_lstm_words4096_mi_reorder',
	'split_inner_phda9_xml_single_stream_lstm_words4096_mi_line_stripe',
	'split_inner_phda9_xml_single_stream_lstm_words4096_siteinfo',
	'split_inner_phda9_xml_single_stream_lstm_words4096_text_pack',
	'split_inner_phda9_xml_single_stream_lstm_words4096_siteinfo_text_pack',
	'split_inner_phda9_xml_single_stream_lstm_words4096_corpus_phrases',
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain',
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain_table',
	'split_inner_phda9_article_single_stream_lstm_words4096_dict',
	'split_inner_phda9_article_single_stream_lstm_words4096_mi_reorder',
);
$cases = $caseFilter !== '' ? $caseFilter : implode(',', $defaultCases);
if ($outJson === '') {
	$outJson = $repo . '/benchmarks/.enwik8_wire460_modeling_sweep_' . $pages . 'p.json';
}

$baseline384 = 518508;
$target = 460096;

echo "wire460 modeling sweep @{$pages}p\n";
echo "cases: {$cases}\n";
echo "out: {$outJson}\n";

$php = PHP_BINARY;
$cmd = escapeshellarg($php) . ' -d memory_limit=2048M '
	. escapeshellarg($repo . '/benchmarks/bench_enwik8_wire_slice_probe.php')
	. ' --pages=' . $pages
	. ' --cases=' . escapeshellarg($cases)
	. ' --verify-rt'
	. ' --out-json=' . escapeshellarg($outJson);

passthru($cmd, $ret);
if (!is_file($outJson)) {
	exit($ret !== 0 ? $ret : 1);
}

$j = json_decode((string) file_get_contents($outJson), true);
if (!is_array($j)) {
	fwrite(STDERR, "invalid json: {$outJson}\n");
	exit(1);
}
// Probe may exit 143 (SIGTERM) after writing complete JSON when parent shell is interrupted.
if ($ret !== 0 && !empty($j['partial'])) {
	fwrite(STDERR, "wire probe incomplete (exit {$ret})\n");
	exit($ret);
}

$rows = $j['rows'] ?? array();
$baselineWire = null;
$bestWire = PHP_INT_MAX;
$bestLabel = '';
$ranked = array();
foreach ($rows as $row) {
	if (!empty($row['error'])) {
		continue;
	}
	$lab = (string) ($row['label'] ?? '?');
	$wire = (int) ($row['wire_fzc'] ?? $row['fzc_bytes'] ?? 0);
	if ($lab === 'split_inner_phda9_xml_single_stream_lstm_words4096_dict') {
		$baselineWire = $wire;
	}
	if ($wire > 0 && $wire < $bestWire) {
		$bestWire = $wire;
		$bestLabel = $lab;
	}
	$ranked[] = array(
		'label' => $lab,
		'wire' => $wire,
		'delta_baseline' => null,
		'roundtrip_ok' => !empty($row['roundtrip_ok']),
		'seconds' => (float) ($row['seconds'] ?? 0),
	);
}
usort($ranked, static fn(array $a, array $b): int => ($a['wire'] <=> $b['wire']));
foreach ($ranked as &$r) {
	if ($baselineWire !== null && $baselineWire > 0) {
		$r['delta_baseline'] = (int) $r['wire'] - (int) $baselineWire;
	}
}
unset($r);

$summary = array(
	'generated' => date('c'),
	'pages' => $pages,
	'baseline_label' => 'split_inner_phda9_xml_single_stream_lstm_words4096_dict',
	'baseline_wire' => $baselineWire,
	'best_label' => $bestLabel,
	'best_wire' => $bestWire === PHP_INT_MAX ? null : $bestWire,
	'target_wire' => $target,
	'gap_target' => $bestWire === PHP_INT_MAX ? null : $bestWire - $target,
	'gap_baseline384p' => $bestWire === PHP_INT_MAX ? null : $bestWire - $baseline384,
	'ranked' => $ranked,
	'probe_out' => $outJson,
);
$summaryPath = preg_replace('/\.json$/', '_summary.json', $outJson) ?? ($outJson . '_summary.json');
file_put_contents($summaryPath, json_encode($summary, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n--- modeling sweep summary @{$pages}p ---\n";
if ($baselineWire !== null) {
	printf("baseline words4096: %s B\n", number_format((int) $baselineWire));
}
if ($bestWire !== PHP_INT_MAX) {
	printf("best: %s (%s B", $bestLabel, number_format($bestWire));
	if ($baselineWire !== null) {
		printf(", Δbaseline=%+d", $bestWire - (int) $baselineWire);
	}
	if ($pages === 384) {
		printf(", Δ518508=%+d", $bestWire - $baseline384);
	}
	echo ")\n";
}
foreach ($ranked as $i => $r) {
	if ($i >= 12) {
		break;
	}
	printf("  %2d. %-60s %8s B", $i + 1, $r['label'], number_format((int) $r['wire']));
	if ($r['delta_baseline'] !== null) {
		printf(" (%+d)", (int) $r['delta_baseline']);
	}
	echo !empty($r['roundtrip_ok']) ? " rt=ok\n" : " rt=?\n";
}
echo "summary: {$summaryPath}\n";
