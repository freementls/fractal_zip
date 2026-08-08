#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Mine top stat_pred_inner vocab from enwik8 pages (incremental counts) for phda9 experiments.
 *
 * Output: benchmarks/.phda9_inner_vocab.txt (one word per line)
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';

$pageLimit = 4096;
$maxWords = fractal_zip_enwik_stat_pred_inner_max_words();
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--max-words=')) {
		$maxWords = max(1, (int) substr($arg, 12));
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}
$n = min($pageLimit, count($split['pages']));
$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$splitPages = enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);

$counts = array();
foreach ($splitPages as $pg) {
	$mineText = (string) ($pg['text'] ?? '');
	foreach (fractal_zip_enwik_text_segment_implicit_space($mineText) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$tok = (string) ($seg['text'] ?? '');
		if (strlen($tok) < 2) {
			continue;
		}
		if (!isset($counts[$tok])) {
			$counts[$tok] = 0;
		}
		$counts[$tok]++;
	}
}

arsort($counts, SORT_NUMERIC);
$vocab = array_slice(array_keys($counts), 0, $maxWords);
$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_inner_vocab.txt';
file_put_contents($out, implode("\n", $vocab) . "\n");
echo "pages={$n} vocab_words=" . count($vocab) . " out={$out}\n";
