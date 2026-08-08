#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik_stat_predictor.php';
require_once $repo . '/fractal_zip_text_dict_preprocess.php';
require_once $repo . '/fractal_zip_enwik.php';

putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$n = 32;
$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array('origIndex' => $i, 'start' => (int) $split['pages'][$i]['start'], 'len' => (int) $split['pages'][$i]['len']);
}
$splitPages = enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
$model = fractal_zip_enwik_stat_pred_inner_frozen_model(implode('', array_column($splitPages, 'text')));
$dictPreOpts = array('stat_model' => $model, 'frozen' => true);
$ranksUsed = array();
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), $dictPreOpts);
	foreach ((array) ($pre['meta']['bigram_ranks_used'] ?? array()) as $pw => $ranks) {
		if (!is_array($ranks)) {
			continue;
		}
		if (!isset($ranksUsed[(string) $pw])) {
			$ranksUsed[(string) $pw] = array();
		}
		foreach (array_keys($ranks) as $r) {
			$ranksUsed[(string) $pw][(int) $r] = true;
		}
	}
}
$trailer = fractal_zip_enwik_stat_pred_trailer_model($model, array(), null, $ranksUsed);
$biIds = fractal_zip_enwik_stat_pred_compact_bigram_ids($trailer['bigram_succ'], $trailer['vocab_index']);
$vocab = $trailer['vocab'];
$prevKeys = array_map('intval', array_keys($biIds));
sort($prevKeys, SORT_NUMERIC);
$metaPrefix = fractal_zip_enwik_encode_varint_u32(strlen('stat_pred_inner')) . 'stat_pred_inner' . chr(1);
$vocabPlain = fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab);
$sideTail = fractal_zip_enwik_encode_varint_u32(0);
$v6Body = fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse($prevKeys, $biIds, $vocab, $ranksUsed, true);
$v6 = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE_IMPLICIT0) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain . $v6Body . $sideTail;
$loaded = fractal_zip_enwik_stat_pred_deserialize_compact_meta($v6);
$biOk = true;
foreach ($biIds as $k => $v) {
	$lv = $loaded['bigram_succ_ids'][(string) $k] ?? null;
	if ($lv !== $v) {
		echo "diff k=$k exp=" . json_encode($v) . ' got=' . json_encode($lv) . "\n";
		$biOk = false;
		break;
	}
}
echo 'v6_roundtrip=' . ($biOk ? 'OK' : 'FAIL') . "\n";
