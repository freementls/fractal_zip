#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Count emitted bigram prev words on mi_reorder wire path (matches stat_pred_inner encode).
 */

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
$dictPreOpts = array(
	'stat_model' => $model,
	'frozen' => true,
);

$pagesPerMember = $n;
$chunkSplit = $splitPages;
$layoutInput = array();
foreach ($chunkSplit as $pg) {
	$layoutInput[] = array(
		'title' => (string) $pg['title'],
		'origIndex' => (int) $pg['origIndex'],
		'text' => (string) $pg['text'],
	);
}
$chunkLayout = fractal_zip_enwik_text_layout_apply($layoutInput, 'mi_reorder', array('seed' => 1));
$perm = $chunkLayout['meta']['perm'] ?? range(0, count($chunkSplit) - 1);

$prevUsed = array();
$ranksUsed = array();
$hits = 0;
foreach ($perm as $pidx) {
	$pidx = (int) $pidx;
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($chunkSplit[$pidx]['text'] ?? ''), $dictPreOpts);
	$hits += (int) ($pre['meta']['bigram_hits'] ?? 0);
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

foreach (array(2048, 4096, 4500, 5000, 5500, 6000, 8192, 12000) as $cap) {
	$trailer = fractal_zip_enwik_stat_pred_trailer_model(
		$model,
		array_keys($prevUsed),
		$cap,
		$ranksUsed
	);
	$rows = count($trailer['bigram_succ'] ?? array());
	echo "cap={$cap} trailer_rows={$rows}\n";
}

echo 'pages=' . $n . ' bigram_hits=' . $hits
	. ' prev_used=' . count($prevUsed)
	. ' ranks_used_prevs=' . count($ranksUsed) . "\n";
