#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
$split = enwik_split_page_refs($blob);
$n = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$n = max(1, (int) substr($arg, 8));
	}
}
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
putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
$model = fractal_zip_enwik_stat_pred_inner_frozen_model($mineText);
$dictPreOpts = array('stat_model' => $model, 'frozen' => true);
$sidecars = array();
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), $dictPreOpts);
	$sc = $pre['sidecar'];
	$sc['preprocess'] = 'stat_pred_inner';
	$sidecars[] = $sc;
}
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
$fzpm = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $trailer['vocab'] ?? array(),
	'bigram_succ_ids' => $biIds,
	'sidecars' => $sidecars,
	'frozen' => true,
	'include_sidecars' => true,
	'sidecars_binary' => true,
	'bigram_uint16' => true,
));
$loaded = fractal_zip_enwik_stat_pred_deserialize_compact_meta($fzpm);
echo 'ver=' . ord($fzpm[5]) . ' raw=' . strlen($fzpm) . "\n";
echo 'vocab_eq=' . (($loaded['vocab'] ?? null) === ($model['vocab'] ?? null) ? 'yes' : 'no') . "\n";
$biEq = true;
foreach ($biIds as $k => $v) {
	$k2 = (string) $k;
	$lv = $loaded['bigram_succ_ids'][$k2] ?? $loaded['bigram_succ_ids'][$k] ?? null;
	if ($lv !== $v) {
		$biEq = false;
		echo 'bi_diff_key=' . $k2 . ' orig=' . json_encode($v) . ' got=' . json_encode($lv) . "\n";
		break;
	}
}
echo 'bi_eq=' . ($biEq ? 'yes' : 'no') . "\n";
$expanded = fractal_zip_enwik_stat_pred_expand_bigram_ids($loaded['bigram_succ_ids'], $loaded['vocab']);
$expandedOrig = fractal_zip_enwik_stat_pred_expand_bigram_ids($biIds, $model['vocab']);
echo 'expand_eq=' . ($expanded === $expandedOrig ? 'yes' : 'no') . "\n";
echo 'side_cnt=' . count($loaded['sidecars'] ?? array()) . ' orig=' . count($sidecars) . "\n";
$rt = fractal_zip_enwik_stat_pred_deserialize_compact_meta($fzpm);
$fzpm2 = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $rt['vocab'],
	'bigram_succ_ids' => $rt['bigram_succ_ids'],
	'sidecars' => $rt['sidecars'],
	'frozen' => true,
	'include_sidecars' => true,
	'sidecars_binary' => true,
	'bigram_uint16' => true,
));
echo 'reencode_eq=' . ($fzpm === $fzpm2 ? 'yes' : 'no') . ' len2=' . strlen($fzpm2) . "\n";
