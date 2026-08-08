#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Hunt mixed phda9 dicts @384p: words + phrases + subwords (tiered + optional phda9 refine).
 *
 * Usage: php benchmarks/bench_phda9_dict_mixed_hunt.php [--pages=384] [--refine-trials=12]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
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
$pageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];

putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');

$phda = static function (string $plain, string $dictPath): array {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => 'phda9_no_lstm',
		'use_dict' => true,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	return array(
		'bytes' => $r['bytes'] ?? null,
		'rt' => !empty($r['roundtrip_ok']),
		'sec' => (float) ($r['seconds'] ?? 0.0),
	);
};

echo "bench_phda9_dict_mixed_hunt | @{$n}p refine_trials={$refineTrials}\n\n";
fwrite(STDERR, "[paq] baseline no-dict …\n");
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
$base = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'tool' => 'phda9_no_lstm',
	'use_dict' => false,
	'timeout_sec' => 0,
	'wire_wrap' => true,
));
$baseBytes = (int) ($base['bytes'] ?? 0);
printf("  baseline FZPA=%s B  RT=%s\n\n", number_format($baseBytes), !empty($base['roundtrip_ok']) ? 'ok' : 'FAIL');
printf("%-14s %10s %8s %8s %8s %s\n", 'mode', 'dict_B', 'FZPA_B', 'Δ', 'sec', 'kind_hist');
printf("%-14s %10s %8s %8s %8s %s\n", '', '', '', '', '', '');

$modes = array(
	'words' => array(),
	'optimal' => array(),
	'mixed' => array('word_budget_pct' => 0.88),
	'mixed_tiered' => array('word_budget_pct' => 0.85, 'max_subwords' => 2048),
	'mixed_refine' => array(
		'word_budget_pct' => 0.90,
		'max_subwords' => 1024,
		'max_phrase_entries' => 2048,
		'refine_plain' => $pageXml,
		'refine_trials' => $refineTrials,
	),
);

$rows = array();
$bestPath = null;
$bestBytes = PHP_INT_MAX;
$bestMode = '';

foreach ($modes as $mode => $mineOpts) {
	$mineOpts['pages'] = $n;
	$mineOpts['mode'] = $mode === 'mixed_tiered' ? 'mixed_tiered' : ($mode === 'mixed_refine' ? 'mixed_refine' : $mode);
	if ($mode === 'mixed') {
		$mineOpts['mode'] = 'mixed';
	}
	try {
		$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, $mineOpts);
	} catch (Throwable $e) {
		fwrite(STDERR, "  {$mode}: mine FAIL " . $e->getMessage() . "\n");
		continue;
	}
	$outPath = $repo . '/benchmarks/.phda9_external_dict_' . $mode . '_' . $n . 'p.txt';
	$written = fractal_zip_phda9_dict_write_file($mined['words'], $outPath);
	fwrite(STDERR, "[paq] compress {$mode} dict …\n");
	$fz = $phda($pageXml, $outPath);
	$fzBytes = $fz['bytes'] !== null ? (int) $fz['bytes'] : null;
	$delta = $fzBytes !== null ? $fzBytes - $baseBytes : null;
	$hist = json_encode($mined['stats']['kind_hist'] ?? array(), JSON_UNESCAPED_SLASHES);
	printf("%-14s %10s %8s %8s %8.1f %s\n",
		$mode,
		number_format((int) $written['bytes']),
		$fzBytes !== null ? number_format($fzBytes) : 'FAIL',
		$delta !== null ? sprintf('%+d', $delta) : '-',
		$fz['sec'],
		$hist
	);
	if ($mode === 'mixed_refine' && isset($mined['stats']['refine_trials'])) {
		printf("  refine: trials=%s tiered=%s best=%s\n",
			(string) ($mined['stats']['refine_trials'] ?? '?'),
			isset($mined['stats']['refine_baseline_bytes']) ? number_format((int) $mined['stats']['refine_baseline_bytes']) : '?',
			isset($mined['stats']['refine_best_bytes']) ? number_format((int) $mined['stats']['refine_best_bytes']) : '?'
		);
	}
	$rows[] = array(
		'mode' => $mode,
		'dict_path' => $outPath,
		'dict_bytes' => (int) $written['bytes'],
		'kind_hist' => $mined['stats']['kind_hist'] ?? array(),
		'fzpa_bytes' => $fzBytes,
		'delta_vs_nodict' => $delta,
		'roundtrip_ok' => $fz['rt'] ?? false,
		'stats' => $mined['stats'],
	);
	if ($fzBytes !== null && $fzBytes < $bestBytes) {
		$bestBytes = $fzBytes;
		$bestPath = $outPath;
		$bestMode = $mode;
	}
}

$outJson = $repo . '/benchmarks/.enwik8_phda9_dict_mixed_hunt.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'baseline_fzpa' => $baseBytes,
	'best' => array('mode' => $bestMode, 'path' => $bestPath, 'fzpa_bytes' => $bestBytes === PHP_INT_MAX ? null : $bestBytes),
	'rows' => $rows,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($bestPath !== null && is_file($bestPath)) {
	$promote = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
	copy($bestPath, $promote);
	echo "\nbest: {$bestMode} FZPA=" . number_format($bestBytes) . " → {$promote}\n";
}
echo "→ {$outJson}\n";
fwrite(STDERR, "OK bench_phda9_dict_mixed_hunt\n");
