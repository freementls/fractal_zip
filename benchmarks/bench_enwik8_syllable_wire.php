#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Syllable / consonant wire probes. Default --quick runs @64p; omit for @384p.
 *
 * Usage:
 *   php benchmarks/bench_enwik8_syllable_wire.php [--quick] [--verify-rt]
 */

$repo = dirname(__DIR__);
$quick = in_array('--quick', $argv, true);
$verifyRt = in_array('--verify-rt', $argv, true);
$pages = $quick ? 64 : 384;
$fullPages = 12041;

$cases = array(
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict',
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid',
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_skdict',
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_merged_dict',
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_stat_syllable_isp',
);

$probe = $repo . '/benchmarks/bench_enwik8_wire_slice_probe.php';
$out = array('generated' => date('c'), 'pages' => $pages, 'rows' => array());

foreach ($cases as $case) {
	$cmd = sprintf(
		'nice -n 19 php -d memory_limit=768M %s --case=%s --pages=%d%s 2>&1',
		escapeshellarg($probe),
		escapeshellarg($case),
		$pages,
		$verifyRt ? ' --verify-rt' : ''
	);
	echo "→ {$case} @{$pages}p\n";
	$raw = shell_exec($cmd);
	if (!is_string($raw) || $raw === '') {
		$out['rows'][] = array('case' => $case, 'error' => 'empty probe output');
		continue;
	}
	$jsonPath = $repo . '/benchmarks/.enwik8_wire_slice_probe_' . $pages . 'p.json';
	if (is_file($jsonPath)) {
		$probeJson = json_decode((string) file_get_contents($jsonPath), true);
		$row = is_array($probeJson) ? ($probeJson['rows'][0] ?? $probeJson) : array();
		$wire = (int) ($row['wire_bytes'] ?? $row['fzc_bytes'] ?? 0);
		$meta = (int) ($row['meta_bytes'] ?? $row['preprocess_meta_bytes'] ?? 0);
		$amort = (int) ($row['amortized_fzc'] ?? ($wire - $meta + (int) round($meta * ($pages / $fullPages))));
		$out['rows'][] = array(
			'case' => $case,
			'wire_bytes' => $wire,
			'meta_bytes' => $meta,
			'amortized_wire' => $amort,
			'roundtrip_ok' => $row['roundtrip_ok'] ?? null,
			'preprocess' => $row['preprocess'] ?? null,
		);
		echo sprintf("  wire=%s meta=%s amort=%s RT=%s\n",
			number_format($wire),
			number_format($meta),
			number_format($amort),
			($row['roundtrip_ok'] ?? '?') === true ? 'ok' : ($row['roundtrip_ok'] ?? '?')
		);
	} else {
		echo substr($raw, -500) . "\n";
	}
}

$outPath = $repo . '/benchmarks/.enwik8_syllable_wire_' . $pages . 'p.json';
file_put_contents($outPath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "\n→ {$outPath}\n";
