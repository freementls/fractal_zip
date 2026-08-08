#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik_stat_predictor.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_text_dict_preprocess.php';
require_once $repo . '/fractal_zip_enwik_text_inner_dict.php';

$n = 384;
$src = $repo . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
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
$dictPreOpts = array(
	'stat_model' => $model,
	'vocab' => $model['vocab'],
	'vocab_index' => $model['vocab_index'],
	'bigram_succ' => $model['bigram_succ'],
	'bigram_rank' => $model['bigram_rank'],
	'frozen' => true,
);
$prevUsed = array();
$ranksUsed = array();
$sidecars = array();
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), $dictPreOpts);
	$sc = $pre['sidecar'];
	$sc['preprocess'] = 'stat_pred_inner';
	$sidecars[] = $sc;
	foreach ((array) ($pre['meta']['bigram_prev_used'] ?? array()) as $pw) {
		if ((string) $pw !== '') {
			$prevUsed[(string) $pw] = true;
		}
	}
	foreach ((array) ($pre['meta']['bigram_ranks_used'] ?? array()) as $pw => $ranks) {
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
	$trailer['bigram_succ'] ?? array(),
	$trailer['vocab_index'] ?? array()
);
$prevKeys = array_map('intval', array_keys($biIds));
sort($prevKeys, SORT_NUMERIC);
$vocab = $trailer['vocab'] ?? array();
$preprocessId = 'stat_pred_inner';
$metaPrefix = fractal_zip_enwik_encode_varint_u32(strlen($preprocessId)) . $preprocessId . chr(1);
$sideBlock = fractal_zip_enwik_stat_pred_serialize_page_sidecars_binary($sidecars);
$sideTail = fractal_zip_enwik_encode_varint_u32(strlen($sideBlock)) . $sideBlock;
$vocabPlain = fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab);
$v2 = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
$v2 .= chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_UINT16) . $metaPrefix;
$v2 .= fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain;
$v2 .= fractal_zip_enwik_stat_pred_encode_bigram_body_v2($prevKeys, $biIds);
$v2 .= $sideTail;
$pick = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $vocab,
	'bigram_succ_ids' => $biIds,
	'sidecars' => $sidecars,
	'frozen' => true,
	'include_sidecars' => true,
	'sidecars_binary' => true,
	'bigram_uint16' => true,
));
$sealedV2 = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $v2);
$sealedPick = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $pick);
$gzV2 = (string) gzencode($v2, 9);
$gzPick = (string) gzencode($pick, 9);
echo 'v2_raw=' . strlen($v2) . ' pick_raw=' . strlen($pick) . ' pick_ver=' . ord($pick[5]) . "\n";
echo 'v2_gz9=' . strlen($gzV2) . ' pick_gz9=' . strlen($gzPick) . ' gz_save=' . (strlen($gzV2) - strlen($gzPick)) . "\n";
echo 'v2_sealed=' . (int) ($sealedV2['wire_bytes'] ?? 0) . ' pick_sealed=' . (int) ($sealedPick['wire_bytes'] ?? 0)
	. ' sealed_save=' . ((int) ($sealedV2['wire_bytes'] ?? 0) - (int) ($sealedPick['wire_bytes'] ?? 0))
	. ' codec=' . (string) ($sealedPick['codec'] ?? '') . "\n";
