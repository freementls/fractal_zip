#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * LSTM phda9 dict refine @96p (fast screen) → optional @384p validate.
 *
 * Usage:
 *   php benchmarks/bench_phda9_dict_lstm_refine.php [--pages=96] [--trials=8]
 *   php benchmarks/bench_phda9_dict_lstm_refine.php --pages=384 --trials=4
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
$trials = 8;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--trials=')) {
		$trials = max(1, (int) substr($arg, 9));
	}
}

$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
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

$baseDict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (!is_file($baseDict)) {
	$baseDict = $repo . '/benchmarks/.phda9_external_dict.txt';
}

echo "bench_phda9_dict_lstm_refine | @{$n}p trials={$trials} tool=phda9\n";

putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $baseDict);
$cur = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'tool' => 'phda9',
	'use_dict' => true,
	'timeout_sec' => 0,
	'wire_wrap' => true,
));
$curBytes = (int) ($cur['bytes'] ?? 0);
printf("  current dict FZPA=%s B RT=%s sec=%.1f\n\n",
	number_format($curBytes),
	!empty($cur['roundtrip_ok']) ? 'ok' : 'FAIL',
	(float) ($cur['seconds'] ?? 0.0)
);

fwrite(STDERR, "[mine] mixed_refine @{$n}p …\n");
$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
	'pages' => $n,
	'mode' => 'mixed_refine',
	'word_budget_pct' => 0.90,
	'max_subwords' => 2048,
	'max_phrase_entries' => 4096,
	'refine_plain' => $pageXml,
	'refine_trials' => $trials,
));
$outPath = $repo . '/benchmarks/.phda9_external_dict_lstm_refine_' . $n . 'p.txt';
$written = fractal_zip_phda9_dict_write_file($mined['words'], $outPath);

putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $outPath);
$ref = fractal_zip_enwik_phda9_english_compress($pageXml, array(
	'tool' => 'phda9',
	'use_dict' => true,
	'timeout_sec' => 0,
	'wire_wrap' => true,
));
$refBytes = isset($ref['bytes']) ? (int) $ref['bytes'] : null;
$delta = $refBytes !== null ? $refBytes - $curBytes : null;

printf("  refined dict=%s B entries=%d\n",
	number_format((int) $written['bytes']),
	count($mined['words'])
);
printf("  refined FZPA=%s B Δ=%s RT=%s sec=%.1f\n",
	$refBytes !== null ? number_format($refBytes) : 'FAIL',
	$delta !== null ? sprintf('%+d', $delta) : '-',
	!empty($ref['roundtrip_ok']) ? 'ok' : 'FAIL',
	(float) ($ref['seconds'] ?? 0.0)
);
if (isset($mined['stats']['refine_trials'])) {
	printf("  refine: trials=%s tiered=%s best=%s\n",
		(string) ($mined['stats']['refine_trials'] ?? '?'),
		isset($mined['stats']['refine_baseline_bytes']) ? number_format((int) $mined['stats']['refine_baseline_bytes']) : '?',
		isset($mined['stats']['refine_best_bytes']) ? number_format((int) $mined['stats']['refine_best_bytes']) : '?'
	);
}

$outJson = $repo . '/benchmarks/.enwik8_phda9_dict_lstm_refine_' . $n . 'p.json';
file_put_contents($outJson, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'tool' => 'phda9',
	'trials' => $trials,
	'base_dict' => $baseDict,
	'refined_dict' => $outPath,
	'base_fzpa' => $curBytes,
	'refined_fzpa' => $refBytes,
	'delta' => $delta,
	'stats' => $mined['stats'],
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

if ($refBytes !== null && $refBytes < $curBytes && is_file($outPath)) {
	$promote = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
	copy($outPath, $promote);
	echo "\n  promoted → {$promote}\n";
}

echo "\n  json → {$outJson}\n";
