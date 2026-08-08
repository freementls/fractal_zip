#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Train phda9 dict candidates on Squash plain-text corpora; validate LSTM FZPA on enwik slice.
 *
 * Insight loop: prose files share phda9_xml wire with general-text-inner; mining on prose
 * and scoring/refining against enwik @96p sorted page XML tests cross-corpus transfer.
 *
 * Usage:
 *   php -d memory_limit=768M benchmarks/bench_phda9_squash_train_enwik_validate.php --greedy-only
 *   php -d memory_limit=768M benchmarks/bench_phda9_squash_train_enwik_validate.php --pages=96 --trials=12 --fast --base=prose
 *   php -d memory_limit=768M benchmarks/bench_phda9_squash_train_enwik_validate.php --pages=96 --trials=4 --lstm-validate
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
require_once $repo . '/fractal_zip_text_dict_preprocess.php';

$pages = 64;
$trials = 6;
$fastScreen = false;
$lstmValidate = false;
$greedyOnly = false;
$baseMode = 'seed';
$trainCorpora = array(
	'test_files105/alice29.txt',
	'test_files106/asyoulik.txt',
	'test_files115/lcet10.txt',
);
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--trials=')) {
		$trials = max(1, (int) substr($arg, 9));
	} elseif ($arg === '--fast') {
		$fastScreen = true;
	} elseif ($arg === '--lstm-validate') {
		$lstmValidate = true;
	} elseif ($arg === '--greedy-only') {
		$greedyOnly = true;
		$fastScreen = true;
	} elseif (str_starts_with($arg, '--base=')) {
		$baseMode = strtolower(trim(substr($arg, 7)));
	}
}
if ($greedyOnly) {
	$trials = 0;
} elseif (!$fastScreen && !$lstmValidate) {
	$fastScreen = true;
}
$refineTool = $fastScreen && !$lstmValidate ? 'phda9_no_lstm' : 'phda9';
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $refineTool);

/**
 * @return list<array{token: string, kind: string}>
 */
function bench_squash_train_mine_prose_candidates(string $prose, int $maxCand): array
{
	$candidates = array();
	$seen = array();
	foreach (fractal_zip_text_dict_nncp_mine_vocab($prose, array(
		'max_words' => min(65536, $maxCand),
		'min_word_len' => 2,
	)) as $w) {
		if (!isset($seen[$w])) {
			$seen[$w] = true;
			$candidates[] = array('token' => $w, 'kind' => 'word');
		}
	}
	foreach (fractal_zip_phda9_dict_mine_word_ngrams($prose, 3, min(4096, $maxCand), 3) as $c) {
		$t = (string) $c['token'];
		if (!isset($seen[$t])) {
			$seen[$t] = true;
			$candidates[] = $c;
		}
	}
	foreach (fractal_zip_phda9_dict_mine_subword_pieces($prose, 3, min(2048, $maxCand)) as $c) {
		$t = (string) $c['token'];
		if (!isset($seen[$t])) {
			$seen[$t] = true;
			$candidates[] = $c;
		}
	}
	return $candidates;
}

function bench_squash_train_fzpa(string $pageXml, string $dictPath, string $tool = 'phda9'): ?array
{
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $tool);
	$r = fractal_zip_enwik_phda9_english_compress($pageXml, array(
		'tool' => $tool,
		'use_dict' => true,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	if (empty($r['roundtrip_ok']) || !isset($r['bytes'])) {
		return null;
	}
	return array('bytes' => (int) $r['bytes'], 'sec' => (float) ($r['seconds'] ?? 0.0));
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}
$sorted = enwik_sort_page_refs_by_title($split['pages']);
$n = min($pages, count($sorted));
$chunk = array();
for ($i = 0; $i < $n; $i++) {
	$chunk[] = array(
		'origIndex' => $i,
		'start' => (int) $sorted[$i]['start'],
		'len' => (int) $sorted[$i]['len'],
	);
}
$pageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];

$proseParts = array();
$trainRows = array();
foreach ($trainCorpora as $rel) {
	$path = $repo . '/' . $rel;
	if (!is_file($path)) {
		continue;
	}
	$bytes = (string) file_get_contents($path);
	$proseParts[] = $bytes;
	$trainRows[] = array('path' => $rel, 'bytes' => strlen($bytes));
}
if ($proseParts === array()) {
	fwrite(STDERR, "no squash train corpora found\n");
	exit(1);
}
$trainProse = implode("\n\n", $proseParts);

$seedPath = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
if (!is_file($seedPath)) {
	$seedPath = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
}
$seedWords = is_file($seedPath) ? fractal_zip_phda9_dict_read_words($seedPath) : array();

echo "squash_train → enwik validate | @{$n}p trials={$trials} refine={$refineTool}\n";
printf("  train: %d files %s B prose\n", count($trainRows), number_format(strlen($trainProse)));
printf("  validate plain=%s B seed=%s words\n\n", number_format(strlen($pageXml)), number_format(count($seedWords)));

$baseline = bench_squash_train_fzpa($pageXml, $seedPath, $refineTool);
if ($baseline === null) {
	fwrite(STDERR, "seed baseline compress failed\n");
	exit(1);
}
printf("  seed_4096p FZPA=%s B sec=%.1f\n", number_format($baseline['bytes']), $baseline['sec']);

fwrite(STDERR, "[mine] squash prose candidates …\n");
$candidates = bench_squash_train_mine_prose_candidates($trainProse, 65536);
$scored = fractal_zip_phda9_dict_score_candidates($pageXml, $candidates);
$proseSelected = fractal_zip_phda9_dict_select_mixed_tiered($scored, array(
	'word_budget_pct' => 0.90,
	'max_subwords' => 2048,
	'max_phrase_entries' => 4096,
));
if ($baseMode === 'prose') {
	$selected = $proseSelected;
} elseif (is_array($seedWords) && $seedWords !== array()) {
	$selected = array_values($seedWords);
} else {
	$selected = $proseSelected;
}

$topProseHits = array();
foreach ($scored as $row) {
	if (($row['est_save'] ?? 0) <= 0) {
		continue;
	}
	$topProseHits[] = array(
		'token' => (string) $row['token'],
		'kind' => (string) ($row['kind'] ?? ''),
		'est_save' => (int) $row['est_save'],
	);
}
usort($topProseHits, static fn (array $a, array $b): int => ($b['est_save'] <=> $a['est_save']));
$topProseHits = array_slice($topProseHits, 0, 20);

if ($greedyOnly) {
	$outPath = $repo . '/benchmarks/.phda9_external_dict_squash_greedy_' . $n . 'p.txt';
	fractal_zip_phda9_dict_write_file($proseSelected, $outPath);
	$trained = bench_squash_train_fzpa($pageXml, $outPath, $refineTool);
	$delta = $trained !== null ? $trained['bytes'] - $baseline['bytes'] : null;
	printf(
		"  prose_greedy dict=%s B entries=%d FZPA=%s Δ=%s sec=%.1f\n",
		number_format((int) filesize($outPath)),
		count($proseSelected),
		$trained !== null ? number_format($trained['bytes']) : 'FAIL',
		$delta !== null ? sprintf('%+d', $delta) : '-',
		$trained['sec'] ?? 0.0
	);
	$ref = array('tokens' => $proseSelected, 'trials' => 0, 'best_bytes' => $trained['bytes'] ?? null, 'baseline_bytes' => $baseline['bytes']);
	goto squash_train_report;
}

fwrite(STDERR, "[refine] {$refineTool} trials on enwik @{$n}p …\n");
$ref = fractal_zip_phda9_dict_refine_with_compress(
	$pageXml,
	$selected,
	$scored,
	$trials,
	$baseline['bytes']
);
$outPath = $repo . '/benchmarks/.phda9_external_dict_squash_train_' . $n . 'p.txt';
fractal_zip_phda9_dict_write_file($ref['tokens'], $outPath);

$trained = bench_squash_train_fzpa($pageXml, $outPath, $refineTool);
$delta = $trained !== null ? $trained['bytes'] - $baseline['bytes'] : null;

$lstmRow = null;
if ($lstmValidate && $fastScreen) {
	fwrite(STDERR, "[validate] LSTM confirm on refined dict …\n");
	$lstmBase = bench_squash_train_fzpa($pageXml, $seedPath, 'phda9');
	$lstmTrained = bench_squash_train_fzpa($pageXml, $outPath, 'phda9');
	if ($lstmBase !== null && $lstmTrained !== null) {
		$lstmRow = array(
			'seed_fzpa' => $lstmBase['bytes'],
			'trained_fzpa' => $lstmTrained['bytes'],
			'delta' => $lstmTrained['bytes'] - $lstmBase['bytes'],
		);
		printf(
			"  lstm_validate seed=%s trained=%s Δ=%s\n",
			number_format($lstmBase['bytes']),
			number_format($lstmTrained['bytes']),
			sprintf('%+d', $lstmRow['delta'])
		);
	}
} elseif ($fastScreen && !$lstmValidate) {
	printf("  (skip LSTM validate; re-run with --lstm-validate if fast screen wins)\n");
}

squash_train_report:
printf(
	"  squash_train dict=%s B entries=%d FZPA=%s Δ=%s sec=%.1f\n",
	number_format((int) filesize($outPath)),
	count($ref['tokens']),
	$trained !== null ? number_format($trained['bytes']) : 'FAIL',
	$delta !== null ? sprintf('%+d', $delta) : '-',
	$trained['sec'] ?? 0.0
);
printf(
	"  refine trials=%d baseline=%s best=%s\n",
	(int) ($ref['trials'] ?? 0),
	number_format((int) ($ref['baseline_bytes'] ?? 0)),
	number_format((int) ($ref['best_bytes'] ?? 0))
);

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'trials' => $trials,
	'train_corpora' => $trainRows,
	'train_prose_bytes' => strlen($trainProse),
	'candidates' => count($candidates),
	'scored' => count($scored),
	'base_mode' => $baseMode,
	'greedy_only' => $greedyOnly,
	'top_prose_hits' => $topProseHits,
	'seed_path' => $seedPath,
	'refine_tool' => $refineTool,
	'seed_fzpa' => $baseline['bytes'],
	'trained_dict' => $outPath,
	'trained_fzpa' => $trained['bytes'] ?? null,
	'delta_vs_seed' => $delta,
	'lstm_validate' => $lstmRow,
	'refine' => array(
		'trials' => $ref['trials'] ?? null,
		'baseline_bytes' => $ref['baseline_bytes'] ?? null,
		'best_bytes' => $ref['best_bytes'] ?? null,
	),
);
$jsonPath = $repo . '/benchmarks/.enwik8_phda9_squash_train_' . $n . 'p.json';
file_put_contents($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

if ($delta !== null && $delta < 0 && is_file($outPath)) {
	$promote = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
	$lstmWin = is_array($lstmRow) && (($lstmRow['delta'] ?? 0) < 0);
	$directLstmWin = !$fastScreen && $lstmValidate && $delta < 0;
	if ($lstmWin || $directLstmWin) {
		copy($outPath, $promote);
		echo "\n  promoted → {$promote}\n";
	} elseif ($fastScreen && !$lstmValidate) {
		echo "\n  fast win (not promoted until --lstm-validate)\n";
	}
}

echo "\n  json → {$jsonPath}\n";
