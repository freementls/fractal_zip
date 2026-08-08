#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Measure stat_pred_inner trailer rows / FZPM bytes @ slice pages. */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';

$pageLimit = 384;
$maxPrev = 4096;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--max-prev=')) {
		$maxPrev = max(1, (int) substr($arg, 11));
	}
}

putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV=' . $maxPrev);
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

$prevUsed = array();
$ranksUsed = array();
$hits = 0;
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), array(
		'stat_model' => $model,
		'frozen' => true,
	));
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

$trailer = fractal_zip_enwik_stat_pred_trailer_model(
	$model,
	array_keys($prevUsed),
	$maxPrev,
	$ranksUsed
);
$biIds = fractal_zip_enwik_stat_pred_compact_bigram_ids(
	is_array($trailer['bigram_succ'] ?? null) ? $trailer['bigram_succ'] : array(),
	is_array($trailer['vocab_index'] ?? null) ? $trailer['vocab_index'] : array()
);
$fzpm = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $trailer['vocab'] ?? array(),
	'bigram_succ_ids' => $biIds,
	'sidecars' => array(),
	'frozen' => true,
	'include_sidecars' => false,
	'bigram_uint16' => true,
));
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
$packed = fractal_zip_enwik_inner_fold_pack_trailer('stat_pred_inner', $fzpm);
$sealed = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpm);

echo 'pages=' . $n . ' max_prev=' . $maxPrev . "\n";
echo 'bigram_hits=' . $hits . ' used_prevs=' . count($prevUsed) . ' trailer_rows=' . count($biIds) . "\n";
echo 'fzpm_raw=' . strlen($fzpm) . ' fzpm_gz9=' . strlen((string) gzencode($fzpm, 9)) . "\n";
echo 'fold_packed=' . strlen($packed) . ' sealed_wire=' . (int) ($sealed['wire_bytes'] ?? 0)
	. ' codec=' . (string) ($sealed['codec'] ?? '') . "\n";
