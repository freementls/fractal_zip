#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
}

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
putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
$model = fractal_zip_enwik_stat_pred_inner_frozen_model($mineText);
$dictPreOpts = array('stat_model' => $model, 'frozen' => true);
$prevUsed = array();
$ranksUsed = array();
$sidecars = array();
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), $dictPreOpts);
	$sc = $pre['sidecar'];
	$sc['preprocess'] = 'stat_pred_inner';
	$sidecars[] = $sc;
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
$base = array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $trailer['vocab'] ?? array(),
	'bigram_succ_ids' => $biIds,
	'frozen' => true,
	'bigram_uint16' => true,
);
$full = $base + array(
	'sidecars' => $sidecars,
	'include_sidecars' => true,
	'sidecars_binary' => true,
	'bigram_ranks_used' => $ranksUsed,
);
$noSide = $base + array(
	'sidecars' => array(),
	'include_sidecars' => false,
	'bigram_ranks_used' => $ranksUsed,
);
$fzpm = fractal_zip_enwik_stat_pred_serialize_compact_meta($full);
$fzpmNoSide = fractal_zip_enwik_stat_pred_serialize_compact_meta($noSide);
$metaPrefix = fractal_zip_enwik_encode_varint_u32(strlen('stat_pred_inner')) . 'stat_pred_inner' . chr(1);
$vocab = $trailer['vocab'] ?? array();
$prevKeys = array();
foreach ($biIds as $pid => $ids) {
	$prevKeys[] = (int) $pid;
}
sort($prevKeys, SORT_NUMERIC);
$vocabPlain = fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab);
$vocabFront = fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($vocab);
$fzpmNoSideV2 = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(2) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain
	. fractal_zip_enwik_stat_pred_encode_bigram_body_v2($prevKeys, $biIds)
	. fractal_zip_enwik_encode_varint_u32(0);
$fzpmNoSideV3plain = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(3) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab)) . chr(0) . $vocabPlain
	. fractal_zip_enwik_stat_pred_encode_bigram_body_v3($prevKeys, $biIds, 0)
	. fractal_zip_enwik_encode_varint_u32(0);
$fzpmNoSideV3front = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(3) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab)) . chr(1) . $vocabFront
	. fractal_zip_enwik_stat_pred_encode_bigram_body_v3($prevKeys, $biIds, 0)
	. fractal_zip_enwik_encode_varint_u32(0);
$fzpmNoSideV3plainDelta = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(3) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab)) . chr(0) . $vocabPlain
	. fractal_zip_enwik_stat_pred_encode_bigram_body_v3($prevKeys, $biIds, 1)
	. fractal_zip_enwik_encode_varint_u32(0);
$sealed = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpm);
$sealedNoSide = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpmNoSide);
$sealedV2 = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpmNoSideV2);
$sealedV3plain = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpmNoSideV3plain);
$sealedV3front = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpmNoSideV3front);
$sealedV3plainDelta = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpmNoSideV3plainDelta);
$ver = strlen($fzpm) >= 6 ? ord($fzpm[5]) : -1;
echo 'pages=' . $n . ' fzpm_ver=' . $ver . "\n";
echo 'no_side_raw v2=' . strlen($fzpmNoSideV2) . ' v3plain=' . strlen($fzpmNoSideV3plain)
	. ' v3plainDelta=' . strlen($fzpmNoSideV3plainDelta)
	. ' v3front=' . strlen($fzpmNoSideV3front) . ' picked=' . strlen($fzpmNoSide) . "\n";
echo 'fzpm_raw=' . strlen($fzpm) . ' fzpm_no_side=' . strlen($fzpmNoSide)
	. ' prev_no_side=331295 delta=' . (331295 - strlen($fzpmNoSide)) . "\n";
echo 'sealed_no_side v2=' . (int) ($sealedV2['wire_bytes'] ?? 0)
	. ' v3plain=' . (int) ($sealedV3plain['wire_bytes'] ?? 0)
	. ' v3plainDelta=' . (int) ($sealedV3plainDelta['wire_bytes'] ?? 0)
	. ' v3front=' . (int) ($sealedV3front['wire_bytes'] ?? 0)
	. ' picked=' . (int) ($sealedNoSide['wire_bytes'] ?? 0) . "\n";
echo 'sealed=' . (int) ($sealed['wire_bytes'] ?? 0)
	. ' sealed_full prev=251180 delta=' . (251180 - (int) ($sealed['wire_bytes'] ?? 0))
	. ' codec=' . (string) ($sealed['codec'] ?? '') . "\n";
