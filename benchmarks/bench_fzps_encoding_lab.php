#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
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

$built = fractal_zip_enwik_stat_pred_build_gap_pool_from_sidecars($sidecars);
$globalGaps = $built['global_gaps'];
$pageRefs = $built['page_refs'];
$plainPool = fractal_zip_enwik_stat_pred_encode_gap_pool_plain($globalGaps);
$frontPool = fractal_zip_enwik_stat_pred_encode_gap_pool_frontcoded($globalGaps);
$pageBlobAbs = fractal_zip_enwik_stat_pred_encode_page_gap_refs($pageRefs);

$pageBlobDelta = '';
foreach ($pageRefs as $pr) {
	$pageBlobDelta .= chr((int) $pr['flags']);
	$refs = $pr['refs'];
	$pageBlobDelta .= fractal_zip_enwik_encode_varint_u32(count($refs));
	$prev = 0;
	foreach ($refs as $rid) {
		$d = (int) $rid - $prev;
		$pageBlobDelta .= fractal_zip_enwik_encode_varint_zigzag_i32($d);
		$prev = (int) $rid;
	}
}

$fzpsV4 = fractal_zip_enwik_stat_pred_serialize_page_sidecars_binary($sidecars);
$round = fractal_zip_enwik_stat_pred_deserialize_page_sidecars_binary($fzpsV4, 'stat_pred_inner');
$roundOk = count($round) === count($sidecars);

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
$sealed = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpm);
$fzpmLoaded = fractal_zip_enwik_stat_pred_deserialize_compact_meta($fzpm);
$fzpmRe = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $fzpmLoaded['vocab'] ?? array(),
	'bigram_succ_ids' => $fzpmLoaded['bigram_succ_ids'] ?? array(),
	'sidecars' => $fzpmLoaded['sidecars'] ?? array(),
	'frozen' => true,
	'include_sidecars' => true,
	'sidecars_binary' => true,
	'bigram_uint16' => true,
));
$fzpmRoundOk = $fzpmRe === $fzpm
	&& count($fzpmLoaded['sidecars'] ?? array()) === count($sidecars);
$fzpmVersion = strlen($fzpm) >= 6 ? ord($fzpm[5]) : -1;
$prevKeys = array_map('intval', array_keys($biIds));
sort($prevKeys, SORT_NUMERIC);
$vocabPlain = fractal_zip_enwik_stat_pred_encode_vocab_plain($trailer['vocab'] ?? array());
$vocabFront = fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($trailer['vocab'] ?? array());
$biBodyV2 = fractal_zip_enwik_stat_pred_encode_bigram_body_v2($prevKeys, $biIds);
$biBodyV3 = fractal_zip_enwik_stat_pred_encode_bigram_body_v3($prevKeys, $biIds);

echo 'pages=' . $n . ' pool=' . count($globalGaps)
	. ' fzps_rt=' . ($roundOk ? 'OK' : 'FAIL')
	. ' fzpm_rt=' . ($fzpmRoundOk ? 'OK' : 'FAIL')
	. ' fzpm_ver=' . $fzpmVersion . "\n";
echo 'plain_pool=' . strlen($plainPool) . ' front_pool=' . strlen($frontPool)
	. ' front_save=' . (strlen($plainPool) - strlen($frontPool)) . "\n";
echo 'page_refs_abs=' . strlen($pageBlobAbs) . ' page_refs_delta=' . strlen($pageBlobDelta)
	. ' delta_save=' . (strlen($pageBlobAbs) - strlen($pageBlobDelta)) . "\n";
echo 'vocab_plain=' . strlen($vocabPlain) . ' vocab_front=' . strlen($vocabFront)
	. ' vocab_save=' . (strlen($vocabPlain) - strlen($vocabFront)) . "\n";
echo 'bigram_v2=' . strlen($biBodyV2) . ' bigram_v3_per_row=' . strlen($biBodyV3)
	. ' bigram_save=' . (strlen($biBodyV2) - strlen($biBodyV3)) . "\n";
echo 'fzps_v4=' . strlen($fzpsV4) . ' fzps_gz9=' . strlen((string) gzencode($fzpsV4, 9)) . "\n";
$fzpmNoSide = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $trailer['vocab'] ?? array(),
	'bigram_succ_ids' => $biIds,
	'sidecars' => array(),
	'frozen' => true,
	'include_sidecars' => false,
	'bigram_uint16' => true,
));
$sealedNoSide = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $fzpmNoSide);
echo 'fzpm_raw=' . strlen($fzpm) . ' fzpm_no_side=' . strlen($fzpmNoSide)
	. ' fzps_bytes=' . (strlen($fzpm) - strlen($fzpmNoSide)) . "\n";
echo 'sealed=' . (int) ($sealed['wire_bytes'] ?? 0)
	. ' sealed_no_side=' . (int) ($sealedNoSide['wire_bytes'] ?? 0)
	. ' codec=' . (string) ($sealed['codec'] ?? '') . "\n";
