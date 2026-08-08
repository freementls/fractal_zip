#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Wire460 dict refine: include dict entries only when honest .fz wire improves.
 *
 * Stack: phda9_xml + LSTM + single-stream + words4096 external dict (off-wire).
 *
 * Usage:
 *   php benchmarks/bench_wire460_dict_wire_refine.php --pages=96 --removals=8 --additions=12
 *   php benchmarks/bench_wire460_dict_wire_refine.php --pages=384 --removals=12 --additions=16
 *   php benchmarks/bench_wire460_dict_wire_refine.php --pages=96 --validate-pages=384
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_dict.php';
require_once $repo . '/fractal_zip_phda9_dict_mine.php';
require_once $repo . '/fractal_zip_phda9_dict_wire.php';

$pages = 96;
$validatePages = 0;
$removals = 12;
$additions = 16;
$seedPath = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
$outDict = '';
$outJson = '';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--validate-pages=')) {
		$validatePages = max(0, (int) substr($arg, 17));
	} elseif (str_starts_with($arg, '--removals=')) {
		$removals = max(0, (int) substr($arg, 11));
	} elseif (str_starts_with($arg, '--additions=')) {
		$additions = max(0, (int) substr($arg, 12));
	} elseif (str_starts_with($arg, '--seed=')) {
		$seedPath = substr($arg, 7);
	} elseif (str_starts_with($arg, '--out-dict=')) {
		$outDict = substr($arg, 11);
	} elseif (str_starts_with($arg, '--out-json=')) {
		$outJson = substr($arg, 11);
	}
}

if (!is_file($seedPath)) {
	fwrite(STDERR, "missing seed dict: {$seedPath}\n");
	exit(1);
}
if ($outDict === '') {
	$outDict = $repo . '/benchmarks/.phda9_external_dict_wire_refine_' . $pages . 'p.txt';
}
if ($outJson === '') {
	$outJson = $repo . '/benchmarks/.enwik8_wire460_dict_wire_refine_' . $pages . 'p.json';
}

$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	$src = $repo . '/enwik8';
}
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
$articleText = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_article_text'];

$sessionDir = sys_get_temp_dir() . '/fz_wire460_dict_' . getmypid() . '_' . bin2hex(random_bytes(4));
$slice = fractal_zip_phda9_dict_wire_prepare_slice($repo, $pages, $sessionDir);
$seedWords = fractal_zip_phda9_dict_read_words($seedPath);

echo "wire460 dict wire refine @{$pages}p\n";
echo "  seed: " . basename($seedPath) . ' (' . count($seedWords) . " words)\n";
echo "  removals≤{$removals} additions≤{$additions}\n";
echo "  session: {$sessionDir}\n\n";

fwrite(STDERR, "[mine] phrase/subword candidates …\n");
$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
	'pages' => $n,
	'mode' => 'phrases',
));
$addCandidates = array();
$seen = array_fill_keys($seedWords, true);
foreach ($mined['words'] as $w) {
	if (!isset($seen[$w])) {
		$addCandidates[] = array('token' => $w, 'kind' => 'phrase');
	}
}
foreach (fractal_zip_phda9_dict_mine_subword_pieces($articleText, 3, 1024) as $c) {
	$t = (string) ($c['token'] ?? '');
	if ($t !== '' && !isset($seen[$t])) {
		$addCandidates[] = $c;
	}
}
$addScored = fractal_zip_phda9_dict_score_candidates($pageXml, $addCandidates);
fwrite(STDERR, '[mine] scored ' . count($addScored) . " add candidates\n");

$removalCandidates = array();
foreach ($mined['words'] as $w) {
	if (isset($seen[$w])) {
		$removalCandidates[] = array('token' => $w, 'kind' => 'phrase');
	}
}
$removalScored = fractal_zip_phda9_dict_score_candidates($pageXml, $removalCandidates);
fwrite(STDERR, '[mine] scored ' . count($removalScored) . " slice-present removal candidates\n");

$report = array(
	'generated' => date('c'),
	'pages' => $pages,
	'seed_path' => $seedPath,
	'seed_words' => count($seedWords),
	'scoreboard_baseline384p' => 518508,
	'target_wire' => 460096,
	'trials' => array(),
);

$onTrial = static function (array $row) use (&$report, $outJson): void {
	$report['trials'][] = $row;
	file_put_contents($outJson, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
	$acc = !empty($row['accepted']) ? 'ACCEPT' : 'reject';
	printf("  %-6s %-24s wire=%8s Δ=%+6d %s (%.0fs)\n",
		(string) ($row['op'] ?? '?'),
		substr((string) ($row['token'] ?? ''), 0, 24),
		number_format((int) ($row['wire_fzc'] ?? 0)),
		(int) ($row['delta_wire'] ?? 0),
		$acc,
		(float) ($row['zip_seconds'] ?? 0)
	);
};

fwrite(STDERR, "[wire] baseline + greedy refine …\n");
$ref = fractal_zip_phda9_dict_wire_refine_greedy(
	$repo,
	$slice['slice_path'],
	$sessionDir,
	$seedWords,
	$pageXml,
	$addScored,
	$removals,
	$additions,
	true,
	$onTrial,
	$removalScored,
	$seedPath
);

$written = fractal_zip_phda9_dict_write_file($ref['words'], $outDict);
$report['refine'] = array(
	'baseline_wire' => $ref['baseline_wire'],
	'best_wire' => $ref['best_wire'],
	'delta_wire' => $ref['delta_wire'],
	'removals_tried' => $ref['removals_tried'],
	'additions_tried' => $ref['additions_tried'],
	'out_words' => count($ref['words']),
	'out_dict_bytes' => (int) ($written['bytes'] ?? 0),
	'out_dict_path' => $outDict,
);
$report['trials'] = $ref['trials'];

$validate = null;
if ($validatePages > 0 && $validatePages !== $pages) {
	fwrite(STDERR, "\n[wire] validate @{$validatePages}p …\n");
	$valDir = $sessionDir . '_val384';
	$valSlice = fractal_zip_phda9_dict_wire_prepare_slice($repo, $validatePages, $valDir);
	$seedEnc = fractal_zip_phda9_dict_wire_encode(
		$repo,
		$valSlice['slice_path'],
		$valDir . '/baseline',
		$seedWords,
		false,
		$seedPath
	);
	$refEnc = fractal_zip_phda9_dict_wire_encode(
		$repo,
		$valSlice['slice_path'],
		$valDir . '/refined',
		$ref['words'],
		true,
		$outDict
	);
	if ($seedEnc !== null && $refEnc !== null) {
		$validate = array(
			'pages' => $validatePages,
			'seed_wire' => (int) $seedEnc['wire_fzc'],
			'refined_wire' => (int) $refEnc['wire_fzc'],
			'delta_wire' => (int) $refEnc['wire_fzc'] - (int) $seedEnc['wire_fzc'],
			'refined_rt_ok' => !empty($refEnc['roundtrip_ok']),
		);
		$report['validate'] = $validate;
	}
}

file_put_contents($outJson, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "\n--- summary @{$pages}p ---\n";
printf("  baseline wire: %s B\n", number_format((int) $ref['baseline_wire']));
printf("  best wire:     %s B (Δ=%+d)\n", number_format((int) $ref['best_wire']), (int) $ref['delta_wire']);
printf("  dict out:      %s (%d words)\n", $outDict, count($ref['words']));
if ($validate !== null) {
	printf("  validate @%dp: seed=%s refined=%s Δ=%+d RT=%s\n",
		(int) $validate['pages'],
		number_format((int) $validate['seed_wire']),
		number_format((int) $validate['refined_wire']),
		(int) $validate['delta_wire'],
		!empty($validate['refined_rt_ok']) ? 'ok' : 'FAIL'
	);
}
echo "  json → {$outJson}\n";

if ((int) $ref['delta_wire'] < 0) {
	echo "  WIN: wire-refined dict beats seed on honest wire\n";
	exit(0);
}
echo "  no wire win on this slice (Δ=0 or regression)\n";
exit(0);
