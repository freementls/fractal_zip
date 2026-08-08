#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$pageLimit = 32;
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
putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
$model = fractal_zip_enwik_stat_pred_inner_frozen_model(implode('', array_column($splitPages, 'text')));
$dictPreOpts = array('stat_model' => $model, 'frozen' => true);
$ranksUsed = array();
$sidecars = array();
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), $dictPreOpts);
	$sidecars[] = $pre['sidecar'];
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
$biIds = fractal_zip_enwik_stat_pred_compact_bigram_ids($model['bigram_succ'], $model['vocab_index']);
$lexPack = fractal_zip_enwik_stat_pred_build_lex_vocab_perm($model['vocab']);
$remapped = fractal_zip_enwik_stat_pred_remap_bigram_ids($biIds, $lexPack['old_to_new']);
$remappedKeys = array_map('intval', array_keys($remapped));
sort($remappedKeys, SORT_NUMERIC);
$metaPrefix = fractal_zip_enwik_encode_varint_u32(strlen('stat_pred_inner')) . 'stat_pred_inner' . chr(1);
$v5 = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(5) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($model['vocab']))
	. fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($lexPack['lex_vocab'])
	. fractal_zip_enwik_stat_pred_encode_vocab_perm_uint16($lexPack['lex_to_old'])
	. fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse(
		$remappedKeys,
		$remapped,
		$lexPack['lex_vocab'],
		$ranksUsed
	)
	. fractal_zip_enwik_encode_varint_u32(0);
$loaded = fractal_zip_enwik_stat_pred_deserialize_compact_meta($v5);
echo 'bi_counts orig=' . count($biIds) . ' loaded=' . count($loaded['bigram_succ_ids'] ?? array()) . "\n";
$biOk = true;
foreach ($biIds as $k => $v) {
	$lv = $loaded['bigram_succ_ids'][(string) $k] ?? $loaded['bigram_succ_ids'][$k] ?? null;
	if ($lv !== $v) {
		$biOk = false;
		echo 'bi_diff_key=' . $k . ' orig=' . json_encode($v) . ' got=' . json_encode($lv) . "\n";
		break;
	}
}
$ok = ($loaded['vocab'] ?? null) === $model['vocab'] && $biOk;
echo 'v5_roundtrip=' . ($ok ? 'OK' : 'FAIL') . "\n";
