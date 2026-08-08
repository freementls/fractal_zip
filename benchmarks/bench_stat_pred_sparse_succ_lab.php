#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
}

putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
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
$mineText = '';
foreach ($splitPages as $pg) {
	$mineText .= (string) ($pg['text'] ?? '');
}
$model = fractal_zip_enwik_stat_pred_inner_frozen_model($mineText);
$dictPreOpts = array('stat_model' => $model, 'frozen' => true);
$prevUsed = array();
$ranksUsed = array();
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), $dictPreOpts);
	foreach ((array) ($pre['meta']['bigram_prev_used'] ?? array()) as $pw) {
		$pw = (string) $pw;
		if ($pw !== '') {
			$prevUsed[$pw] = true;
		}
	}
	foreach ((array) ($pre['meta']['bigram_ranks_used'] ?? array()) as $pw => $ranks) {
		$pw = (string) $pw;
		if ($pw === '' || !is_array($ranks)) {
			continue;
		}
		if (!isset($ranksUsed[$pw])) {
			$ranksUsed[$pw] = array();
		}
		foreach (array_keys($ranks) as $r) {
			$ranksUsed[$pw][(int) $r] = true;
		}
	}
}
$trailer = fractal_zip_enwik_stat_pred_trailer_model(
	$model,
	array_keys($prevUsed),
	fractal_zip_enwik_stat_pred_inner_bigram_max_prev(),
	$ranksUsed
);
$biIds = fractal_zip_enwik_stat_pred_compact_bigram_ids(
	is_array($trailer['bigram_succ'] ?? null) ? $trailer['bigram_succ'] : array(),
	is_array($trailer['vocab_index'] ?? null) ? $trailer['vocab_index'] : array()
);

$contigBytes = 0;
$sparseBytes = 0;
$sparseSave = 0;
$rows = 0;
foreach ($biIds as $pid => $ids) {
	$pid = (string) $pid;
	$rows++;
	$contigBytes += 2 * count($ids);
	$prevWord = (string) (($trailer['vocab'] ?? array())[(int) $pid] ?? '');
	$rankSet = $ranksUsed[$prevWord] ?? array();
	$nRanks = count($rankSet);
	$sparseBytes += $nRanks * (1 + 2);
	if (count($ids) > $nRanks) {
		$sparseSave += 2 * (count($ids) - $nRanks);
	}
}

$rank0Only = 0;
$rank01Only = 0;
foreach ($biIds as $pid => $ids) {
	$pid = (string) $pid;
	$prevWord = (string) (($trailer['vocab'] ?? array())[(int) $pid] ?? '');
	$rankSet = $ranksUsed[$prevWord] ?? array();
	$keys = array_map('intval', array_keys($rankSet));
	sort($keys, SORT_NUMERIC);
	if ($keys === array(0)) {
		$rank0Only++;
	}
	if ($keys === array(0, 1) || $keys === array(1)) {
		$rank01Only++;
	}
}
echo 'pages=' . $n . ' trailer_rows=' . $rows . "\n";
echo 'contig_succ_u16_bytes=' . $contigBytes . ' sparse_est_bytes=' . $sparseBytes
	. ' est_save=' . $sparseSave . "\n";
echo 'rank0_only_rows=' . $rank0Only . ' rank01_only_rows=' . $rank01Only . "\n";
