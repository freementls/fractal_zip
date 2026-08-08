#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik_stat_predictor.php';
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_text_dict_preprocess.php';
require_once $repo . '/fractal_zip_enwik_text_inner_dict.php';

$n = (int) ($argv[1] ?? 32);
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
$ver = ord($fzpm[5]);
echo 'ver=' . $ver . ' raw=' . strlen($fzpm) . "\n";
if (($loaded['vocab'] ?? array()) !== ($trailer['vocab'] ?? array())) {
	fwrite(STDERR, "FAIL vocab\n");
	exit(1);
}
$biNorm = $loaded['bigram_succ_ids'] ?? array();
ksort($biNorm, SORT_NUMERIC);
$biOrig = $biIds;
ksort($biOrig, SORT_NUMERIC);
if ($biNorm !== $biOrig) {
	fwrite(STDERR, 'FAIL bigram content keys orig=' . count($biIds) . ' loaded=' . count($loaded['bigram_succ_ids'] ?? array()) . "\n");
	$mism = 0;
	foreach ($biOrig as $k => $v) {
		$lv = $biNorm[(string) $k] ?? null;
		if ($lv !== $v) {
			fwrite(STDERR, "row $k orig=" . json_encode($v) . ' loaded=' . json_encode($lv) . "\n");
			if (++$mism >= 3) {
				break;
			}
		}
	}
	exit(1);
}
if (($loaded['bigram_succ_ids'] ?? array()) !== $biIds) {
	echo "note: bigram key order differs (content OK)\n";
}
if (count($loaded['sidecars'] ?? array()) !== count($sidecars)) {
	fwrite(STDERR, 'FAIL sidecar count ' . count($loaded['sidecars'] ?? array()) . ' vs ' . count($sidecars) . "\n");
	exit(1);
}
echo "OK vocab+bigram+sidecar_count\n";
$re = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
	'preprocess' => 'stat_pred_inner',
	'vocab' => $loaded['vocab'] ?? array(),
	'bigram_succ_ids' => $loaded['bigram_succ_ids'] ?? array(),
	'sidecars' => $loaded['sidecars'] ?? array(),
	'frozen' => true,
	'include_sidecars' => true,
	'sidecars_binary' => true,
	'bigram_uint16' => true,
));
echo 're_len=' . strlen($re) . ' orig_len=' . strlen($fzpm) . ' re_eq=' . ($re === $fzpm ? 'yes' : 'no') . "\n";
