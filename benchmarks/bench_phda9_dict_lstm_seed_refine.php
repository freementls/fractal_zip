#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * LSTM phda9 dict refine seeded from words_4096p (add subwords/phrases only).
 *
 * Usage: php benchmarks/bench_phda9_dict_lstm_seed_refine.php [--pages=96] [--trials=6] [--seed=PATH|4096p|mixed_best]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');

require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict.php';

$pages = 96;
$trials = 24;
$seedPath = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
$outDict = '';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--trials=')) {
		$trials = max(1, (int) substr($arg, 9));
	}
	if (str_starts_with($arg, '--out=')) {
		$outDict = substr($arg, 6);
	}
	if (str_starts_with($arg, '--seed=')) {
		$seedArg = substr($arg, 7);
		if ($seedArg === '4096p' || $seedArg === 'words_4096p') {
			$seedPath = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
		} elseif ($seedArg === 'mixed_best') {
			$seedPath = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
		} else {
			$seedPath = $seedArg;
		}
	}
}
if (!is_file($seedPath)) {
	$seedPath = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
}
if (!is_file($seedPath)) {
	$seedPath = $repo . '/benchmarks/.phda9_external_dict.txt';
}
if (!is_file($seedPath)) {
	fwrite(STDERR, "missing seed dict\n");
	exit(1);
}
$seedWords = fractal_zip_phda9_dict_read_words($seedPath);
$seedDictBytes = (int) filesize($seedPath);
$fullPages = (int) (getenv('FRACTAL_ZIP_ENWIK_INNER_FOLD_FULL_PAGES') ?: 12041);
if ($fullPages <= 0) {
	$fullPages = 12041;
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
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

putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $seedPath);
$base = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'tool' => 'phda9',
	'use_dict' => true,
	'timeout_sec' => 0,
	'wire_wrap' => true,
));
$baseBytes = (int) ($base['bytes'] ?? 0);
echo "seed_refine @{$n}p trials={$trials} seed=" . count($seedWords) . " words tool=phda9\n";
if (function_exists('ob_implicit_flush')) {
	ob_implicit_flush(true);
}
printf("  seed FZPA=%s B RT=%s sec=%.1f\n\n",
	number_format($baseBytes),
	!empty($base['roundtrip_ok']) ? 'ok' : 'FAIL',
	(float) ($base['seconds'] ?? 0.0)
);

fwrite(STDERR, "[mine] mixed_refine seed + refine …\n");
$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
	'pages' => $n,
	'mode' => 'mixed_refine',
	'seed_words' => $seedWords,
	'word_budget_pct' => 0.90,
	'max_subwords' => 0,
	'max_phrase_entries' => 0,
	'refine_plain' => $pageXml,
	'refine_trials' => $trials,
	'refine_baseline_bytes' => $baseBytes,
));
$outPath = $outDict !== ''
	? $outDict
	: ($repo . '/benchmarks/.phda9_external_dict_lstm_seed_refine_' . $n . 'p.txt');
fractal_zip_phda9_dict_write_file($mined['words'], $outPath);

putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $outPath);
$ref = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'tool' => 'phda9',
	'use_dict' => true,
	'timeout_sec' => 0,
	'wire_wrap' => true,
));
$refBytes = isset($ref['bytes']) ? (int) $ref['bytes'] : null;
$delta = $refBytes !== null ? $refBytes - $baseBytes : null;
$refinedDictBytes = (int) filesize($outPath);
$dictDelta = $refinedDictBytes - $seedDictBytes;
$amortScale = $n / $fullPages;
$seedDictAmort = (int) round($seedDictBytes * $amortScale);
$refinedDictAmort = (int) round($refinedDictBytes * $amortScale);
$netAmortFzpaDelta = $delta !== null
	? $delta + (int) round($dictDelta * ($amortScale - 1.0))
	: null;

printf("  refined dict=%s B entries=%d (Δ=%+d)\n",
	number_format($refinedDictBytes),
	count($mined['words']),
	$dictDelta
);
printf("  refined FZPA=%s B Δ=%s RT=%s sec=%.1f\n",
	$refBytes !== null ? number_format($refBytes) : 'FAIL',
	$delta !== null ? sprintf('%+d', $delta) : '-',
	!empty($ref['roundtrip_ok']) ? 'ok' : 'FAIL',
	(float) ($ref['seconds'] ?? 0.0)
);
if ($netAmortFzpaDelta !== null) {
	printf("  amortized FZPA Δ=%+d (dict ship @%dp: seed=%s refined=%s B; full=%d)\n",
		$netAmortFzpaDelta,
		$n,
		number_format($seedDictAmort),
		number_format($refinedDictAmort),
		$fullPages
	);
}
if (isset($mined['stats']['refine_trials'])) {
	printf("  refine: trials=%s baseline=%s best=%s\n",
		(string) ($mined['stats']['refine_trials'] ?? '?'),
		isset($mined['stats']['refine_baseline_bytes']) ? number_format((int) $mined['stats']['refine_baseline_bytes']) : '?',
		isset($mined['stats']['refine_best_bytes']) ? number_format((int) $mined['stats']['refine_best_bytes']) : '?'
	);
}

$outJson = $repo . '/benchmarks/.enwik8_phda9_dict_lstm_seed_refine_' . $n . 'p.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'full_pages' => $fullPages,
	'tool' => 'phda9',
	'trials' => $trials,
	'seed_path' => $seedPath,
	'refined_dict' => $outPath,
	'seed_dict_bytes' => $seedDictBytes,
	'refined_dict_bytes' => $refinedDictBytes,
	'dict_delta_bytes' => $dictDelta,
	'amortized_dict_bytes_seed' => $seedDictAmort,
	'amortized_dict_bytes_refined' => $refinedDictAmort,
	'amortized_model' => 'fzpa_delta + dict_delta * (slice_pages/full_pages - 1); dict ships once at full scale',
	'base_fzpa' => $baseBytes,
	'refined_fzpa' => $refBytes,
	'delta' => $delta,
	'net_amortized_fzpa_delta' => $netAmortFzpaDelta,
	'stats' => $mined['stats'],
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
echo "  json → {$outJson}\n";
