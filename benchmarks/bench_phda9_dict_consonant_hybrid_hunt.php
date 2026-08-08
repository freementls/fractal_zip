#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Hunt phda9 dicts for consonant_hybrid preprocessed page XML @384p.
 *
 * Compares:
 *   - preprocessed plain, no dict
 *   - preprocessed plain + English mixed dict (mismatch control)
 *   - preprocessed plain + consonant-aware mined dicts
 *
 * Usage:
 *   FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii \
 *     php benchmarks/bench_phda9_dict_consonant_hybrid_hunt.php [--pages=384] [--refine-trials=12]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');

require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict.php';

$pages = 384;
$refineTrials = 12;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--refine-trials=')) {
		$refineTrials = max(0, (int) substr($arg, 16));
	}
}

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$chunk = array();
for ($i = 0; $i < $n; $i++) {
	$chunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$rawPageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];
$corp = fractal_zip_phda9_dict_build_consonant_hybrid_corpus($blob, $n);
$skPageXml = (string) $corp['page_xml'];

putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$phda = static function (string $plain, ?string $dictPath): array {
	if ($dictPath !== null && $dictPath !== '') {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
	} else {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	}
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => 'phda9_no_lstm',
		'use_dict' => $dictPath !== null && $dictPath !== '',
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	return array(
		'bytes' => $r['bytes'] ?? null,
		'rt' => !empty($r['roundtrip_ok']),
		'sec' => (float) ($r['seconds'] ?? 0.0),
	);
};

$englishDict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (!is_file($englishDict)) {
	$englishDict = $repo . '/benchmarks/.phda9_external_dict.txt';
}

echo "bench_phda9_dict_consonant_hybrid_hunt | @{$n}p encoded_plain="
	. number_format(strlen($skPageXml)) . " B\n\n";

$controls = array(
	'raw_xml_nodict' => array('plain' => $rawPageXml, 'dict' => null),
	'raw_xml_english_dict' => array('plain' => $rawPageXml, 'dict' => $englishDict),
	'sk_plain_nodict' => array('plain' => $skPageXml, 'dict' => null),
	'sk_plain_english_dict' => array('plain' => $skPageXml, 'dict' => $englishDict),
);
$mergedPath = $repo . '/benchmarks/.phda9_external_dict_consonant_merged_best.txt';
if (is_file($englishDict)) {
	try {
		$merged = fractal_zip_phda9_dict_build_consonant_merged_dict($blob, $n, $englishDict, $mergedPath);
		$controls['sk_plain_merged_dict'] = array('plain' => $skPageXml, 'dict' => $mergedPath);
		echo 'merged_dict skel=' . ($merged['stats']['skel_selected'] ?? '?')
			. ' words=' . ($merged['stats']['dict_words'] ?? '?') . "\n";
	} catch (Throwable $e) {
		fwrite(STDERR, 'merged_dict build FAIL: ' . $e->getMessage() . "\n");
	}
}
printf("%-24s %10s %8s %s\n", 'control', 'FZPA_B', 'sec', 'note');
foreach ($controls as $label => $cfg) {
	fwrite(STDERR, "[paq] control {$label} …\n");
	$fz = $phda((string) $cfg['plain'], $cfg['dict']);
	printf("%-24s %10s %8.1f %s\n",
		$label,
		$fz['bytes'] !== null ? number_format((int) $fz['bytes']) : 'FAIL',
		$fz['sec'],
		$fz['rt'] ? 'RT ok' : 'RT FAIL'
	);
}
echo "\n";

$modes = array(
	'skel_words' => array('mode' => 'words'),
	'skel_mixed_tiered' => array(
		'mode' => 'mixed_tiered',
		'word_budget_pct' => 0.90,
		'max_subwords' => 2048,
	),
	'skel_mixed_refine' => array(
		'mode' => 'mixed_refine',
		'word_budget_pct' => 0.92,
		'max_subwords' => 1024,
		'max_phrase_entries' => 2048,
		'refine_plain' => $skPageXml,
		'refine_trials' => $refineTrials,
	),
);

printf("%-20s %10s %8s %8s %s\n", 'mode', 'dict_B', 'FZPA_B', 'Δ nodict', 'kind_hist');
$rows = array();
$bestPath = null;
$bestBytes = PHP_INT_MAX;
$bestMode = '';
$skNoDict = $phda($skPageXml, null);
$skNoDictBytes = (int) ($skNoDict['bytes'] ?? 0);

foreach ($modes as $label => $mineOpts) {
	$mineOpts['pages'] = $n;
	$mineOpts['preprocess'] = 'consonant_hybrid';
	try {
		$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, $mineOpts);
	} catch (Throwable $e) {
		fwrite(STDERR, "  {$label}: mine FAIL " . $e->getMessage() . "\n");
		continue;
	}
	$outPath = $repo . '/benchmarks/.phda9_external_dict_consonant_' . $label . '_' . $n . 'p.txt';
	$written = fractal_zip_phda9_dict_write_file($mined['words'], $outPath);
	fwrite(STDERR, "[paq] compress {$label} …\n");
	$fz = $phda($skPageXml, $outPath);
	$fzBytes = $fz['bytes'] !== null ? (int) $fz['bytes'] : null;
	$delta = $fzBytes !== null && $skNoDictBytes > 0 ? $fzBytes - $skNoDictBytes : null;
	$hist = json_encode($mined['stats']['kind_hist'] ?? array(), JSON_UNESCAPED_SLASHES);
	printf("%-20s %10s %8s %8s %s\n",
		$label,
		number_format((int) $written['bytes']),
		$fzBytes !== null ? number_format($fzBytes) : 'FAIL',
		$delta !== null ? sprintf('%+d', $delta) : '-',
		$hist
	);
	if ($label === 'skel_mixed_refine' && isset($mined['stats']['refine_trials'])) {
		printf("  refine trials=%s tiered=%s best=%s\n",
			(string) ($mined['stats']['refine_trials'] ?? '?'),
			isset($mined['stats']['refine_baseline_bytes']) ? number_format((int) $mined['stats']['refine_baseline_bytes']) : '?',
			isset($mined['stats']['refine_best_bytes']) ? number_format((int) $mined['stats']['refine_best_bytes']) : '?'
		);
	}
	$rows[] = array(
		'mode' => $label,
		'dict_path' => $outPath,
		'dict_bytes' => (int) $written['bytes'],
		'fzpa_bytes' => $fzBytes,
		'delta_vs_sk_nodict' => $delta,
		'roundtrip_ok' => $fz['rt'] ?? false,
		'stats' => $mined['stats'],
	);
	if ($fzBytes !== null && $fzBytes < $bestBytes) {
		$bestBytes = $fzBytes;
		$bestPath = $outPath;
		$bestMode = $label;
	}
}

$outJson = $repo . '/benchmarks/.enwik8_phda9_dict_consonant_hybrid_hunt.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'sk_plain_len' => strlen($skPageXml),
	'controls' => array_map(static function (array $cfg) use ($phda): array {
		$fz = $phda((string) $cfg['plain'], $cfg['dict']);
		return array(
			'bytes' => $fz['bytes'],
			'roundtrip_ok' => $fz['rt'],
			'sec' => $fz['sec'],
		);
	}, $controls),
	'best' => array(
		'mode' => $bestMode,
		'path' => $bestPath,
		'fzpa_bytes' => $bestBytes === PHP_INT_MAX ? null : $bestBytes,
	),
	'rows' => $rows,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($bestPath !== null && is_file($bestPath)) {
	$promote = $repo . '/benchmarks/.phda9_external_dict_consonant_hybrid_best.txt';
	copy($bestPath, $promote);
	echo "\nbest: {$bestMode} FZPA=" . number_format($bestBytes) . " → {$promote}\n";
}
echo "→ {$outJson}\n";
fwrite(STDERR, "OK bench_phda9_dict_consonant_hybrid_hunt\n");
