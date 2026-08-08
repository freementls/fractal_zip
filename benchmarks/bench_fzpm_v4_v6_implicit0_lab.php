#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
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

putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');

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
$sidecars = array();
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_preprocess_apply('stat_pred', (string) ($pg['text'] ?? ''), $dictPreOpts);
	$sidecars[] = $pre['meta'];
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
$vocab = is_array($trailer['vocab'] ?? null) ? $trailer['vocab'] : array();
$prevKeys = array();
foreach ($biIds as $pid => $ids) {
	$prevKeys[] = (int) $pid;
}
sort($prevKeys, SORT_NUMERIC);

$metaPrefix = fractal_zip_enwik_encode_varint_u32(strlen('stat_pred_inner')) . 'stat_pred_inner' . chr(1);
$vocabPlain = fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab);
$sideBlock = fractal_zip_enwik_stat_pred_encode_sidecar_block($sidecars, true);
$sideTail = fractal_zip_enwik_encode_varint_u32(strlen($sideBlock)) . $sideBlock;
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=0');
$sideTailLen = strlen($sideTail);

$build = static function (int $ver, string $body) use ($metaPrefix, $vocabPlain, $sideTail): string {
	$buf = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
	$buf .= chr($ver) . $metaPrefix;
	$buf .= fractal_zip_enwik_encode_varint_u32(count(explode("\n", $vocabPlain))) . $vocabPlain;
	return $buf . $body . $sideTail;
};

$v4Body = fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse($prevKeys, $biIds, $vocab, $ranksUsed, false);
$v6Body = fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse($prevKeys, $biIds, $vocab, $ranksUsed, true);
$v4 = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain . $v4Body . $sideTail;
$v6 = FRACTAL_ZIP_STAT_PRED_META_MAGIC . chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE_IMPLICIT0) . $metaPrefix
	. fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain . $v6Body . $sideTail;

$modelOnly = static function (string $blob) use ($sideTailLen): string {
	$modelLen = strlen($blob) - $sideTailLen;
	return $modelLen > 0 ? substr($blob, 0, $modelLen) : $blob;
};

$report = static function (string $label, string $blob): void {
	global $sideTailLen, $modelOnly;
	$mo = $modelOnly($blob);
	$gz = gzencode($mo, 9);
	$sealed = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $blob);
	echo $label
		. ' raw=' . strlen($blob)
		. ' model=' . strlen($mo)
		. ' gz9_model=' . (is_string($gz) ? strlen($gz) : -1)
		. ' sealed=' . ($sealed['wire_bytes'] ?? -1)
		. ' codec=' . ($sealed['codec'] ?? '?')
		. "\n";
};

echo 'pages=' . $n . ' sideTail=' . $sideTailLen . "\n";
$report('v4_sparse', $v4);
$report('v6_implicit0', $v6);

$preMeta = array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $vocab,
	'bigram_succ_ids' => $biIds,
	'bigram_ranks_used' => $ranksUsed,
	'sidecars' => $sidecars,
	'frozen' => true,
	'bigram_uint16' => true,
);
$metaVerAt = static function (string $blob): int {
	return ord($blob[strlen(FRACTAL_ZIP_STAT_PRED_META_MAGIC)]);
};

putenv('FRACTAL_ZIP_STAT_PRED_FZPM_SEALED_PICK=');
$picked = fractal_zip_enwik_stat_pred_serialize_compact_meta($preMeta);
echo 'picked_default ver=' . $metaVerAt($picked) . ' raw=' . strlen($picked) . "\n";
putenv('FRACTAL_ZIP_STAT_PRED_FZPM_SEALED_PICK=1');
$pickedSealed = fractal_zip_enwik_stat_pred_serialize_compact_meta($preMeta);
echo 'picked_sealed ver=' . $metaVerAt($pickedSealed) . ' raw=' . strlen($pickedSealed) . "\n";
$sealed = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $pickedSealed);
echo 'picked_sealed_wire=' . ($sealed['wire_bytes'] ?? -1)
	. ' codec=' . ($sealed['codec'] ?? '?') . "\n";

$loaded6 = fractal_zip_enwik_stat_pred_deserialize_compact_meta($v6);
$v6VocabOk = ($loaded6['vocab'] ?? null) === $vocab;
$v6SideOk = count($loaded6['sidecars'] ?? array()) === count($sidecars);
echo 'v6_roundtrip_vocab=' . ($v6VocabOk ? 'OK' : 'FAIL')
	. ' sidecars=' . ($v6SideOk ? 'OK' : 'FAIL') . "\n";
