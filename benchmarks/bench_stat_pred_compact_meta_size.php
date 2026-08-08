#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';

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

$report = static function (string $label, array $model): void {
	$biIds = fractal_zip_enwik_stat_pred_compact_bigram_ids($model['bigram_succ'], $model['vocab_index']);
	$preMeta = array(
		'preprocess' => 'stat_pred_compact_meta',
		'vocab' => $model['vocab'],
		'bigram_succ_ids' => $biIds,
		'sidecars' => array(array('preprocess' => 'stat_pred')),
		'frozen' => true,
	);
	$bin = fractal_zip_enwik_stat_pred_serialize_compact_meta($preMeta);
	$gz = gzencode($bin, 9);
	echo $label . ' compact_bin=' . strlen($bin) . ' compact_gz=' . strlen((string) $gz)
		. ' bigram_rows=' . count($biIds) . ' vocab_words=' . count($model['vocab']) . "\n";
};

putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV');
$report('full', fractal_zip_enwik_stat_pred_mine_model($mineText, null, null, false));

putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV');
$report('win_only', fractal_zip_enwik_stat_pred_mine_model($mineText, null, null, true));

putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV=4096');
$report('win_only_max4096', fractal_zip_enwik_stat_pred_mine_model($mineText, null, null, true));
