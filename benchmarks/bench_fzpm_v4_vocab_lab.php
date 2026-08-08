#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

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
$vocab = $trailer['vocab'] ?? array();
$plain = fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab);
$front = fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($vocab);
$meta = array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $vocab,
	'bigram_succ_ids' => $biIds,
	'sidecars' => $sidecars,
	'frozen' => true,
	'include_sidecars' => true,
	'sidecars_binary' => true,
	'bigram_uint16' => true,
	'bigram_ranks_used' => $ranksUsed,
);
$picked = fractal_zip_enwik_stat_pred_serialize_compact_meta($meta);
$ver = strlen($picked) >= 6 ? ord($picked[5]) : -1;
echo 'pages=' . $n . ' vocab_words=' . count($vocab) . "\n";
$lex = $vocab;
usort($lex, 'strcmp');
$lexPlain = fractal_zip_enwik_stat_pred_encode_vocab_plain($lex);
$lexFront = fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($lex);
$permCost = count($vocab) * 2;
echo 'vocab_plain=' . strlen($plain) . ' vocab_front=' . strlen($front)
	. ' save=' . (strlen($plain) - strlen($front)) . "\n";
echo 'vocab_lex_plain=' . strlen($lexPlain) . ' lex_front=' . strlen($lexFront)
	. ' lex_front+perm=' . (strlen($lexFront) + $permCost)
	. ' vs_plain_delta=' . (strlen($plain) - strlen($lexFront) - $permCost) . "\n";
$lexPack = fractal_zip_enwik_stat_pred_build_lex_vocab_perm($vocab);
$remapped = fractal_zip_enwik_stat_pred_remap_bigram_ids($biIds, $lexPack['old_to_new']);
$remappedKeys = array();
foreach ($remapped as $pid => $ids) {
	$remappedKeys[] = (int) $pid;
}
sort($remappedKeys, SORT_NUMERIC);
$metaPrefix = fractal_zip_enwik_encode_varint_u32(strlen('stat_pred_inner')) . 'stat_pred_inner' . chr(1);
$sideBlock = fractal_zip_enwik_stat_pred_serialize_page_sidecars_binary($sidecars);
$sideTail = fractal_zip_enwik_encode_varint_u32(strlen($sideBlock)) . $sideBlock;
$v4 = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(4) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab))
	. fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab)
	. fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse($remappedKeys, $biIds, $vocab, $ranksUsed);
$v4full = $v4 . $sideTail;
$v5 = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(5) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab))
	. fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($lexPack['lex_vocab'])
	. fractal_zip_enwik_stat_pred_encode_vocab_perm_uint16($lexPack['lex_to_old'])
	. fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse(
		$remappedKeys,
		$remapped,
		$lexPack['lex_vocab'],
		$ranksUsed
	);
$v5full = $v5 . $sideTail;
$sideLen = strlen($sideTail);
$v4Model = substr($v4full, 0, -$sideLen);
$v5Model = substr($v5full, 0, -$sideLen);
echo 'v4_raw=' . strlen($v4full) . ' v4_gz9_full=' . strlen((string) gzencode($v4full, 9))
	. ' v4_gz9_model=' . strlen((string) gzencode($v4Model, 9)) . "\n";
echo 'v5_raw=' . strlen($v5full) . ' v5_gz9_full=' . strlen((string) gzencode($v5full, 9))
	. ' v5_gz9_model=' . strlen((string) gzencode($v5Model, 9))
	. ' delta_model_raw=' . (strlen($v4Model) - strlen($v5Model)) . "\n";
echo 'fzpm_picked_ver=' . $ver . ' raw=' . strlen($picked)
	. ' gz9=' . strlen((string) gzencode($picked, 9)) . "\n";
