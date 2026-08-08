#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Compare stat_pred token payload vs raw mi_reorder text on entry-sorted slice. */

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
if ($split === null) {
	exit(1);
}
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
$rawMi = '';
foreach ($splitPages as $pg) {
	$t = (string) ($pg['text'] ?? '');
	$mineText .= $t;
	$rawMi .= $t;
}
$model = fractal_zip_enwik_stat_pred_mine_model($mineText, null, null, false);

$report = static function (string $codec) use ($splitPages, $model, $rawMi): void {
	$tokAll = '';
	$bigramHits = 0;
	foreach ($splitPages as $pg) {
		$pre = fractal_zip_text_stat_pred_preprocess((string) $pg['text'], array(
			'stat_model' => $model,
			'frozen' => true,
			'payload_codec' => $codec,
		));
		$tokAll .= (string) $pre['payload'];
		$bigramHits += (int) ($pre['meta']['bigram_hits'] ?? 0);
	}
	$rawLen = strlen($rawMi);
	$tokLen = strlen($tokAll);
	$gzRaw = gzencode($rawMi, 1);
	$gzTok = gzencode($tokAll, 1);
	echo "codec={$codec} raw_mi={$rawLen} token_payload={$tokLen} delta=" . ($tokLen - $rawLen)
		. " bigram_hits={$bigramHits}\n";
	echo "  gzip1_raw=" . strlen((string) $gzRaw) . ' gzip1_tok=' . strlen((string) $gzTok)
		. ' gzip_delta=' . (strlen((string) $gzTok) - strlen((string) $gzRaw)) . "\n";
};
echo "pages={$n}\n";
foreach (array('varint', 'delta', 'base94') as $codec) {
	$report($codec);
}

// FZTX inner text_blob sizes (mi_reorder per 128p chunk, same as wire).
$pagesPerMember = 128;
$layoutId = 'mi_reorder';
$rawBlob = '';
$tokBlob = '';
for ($i = 0; $i < $n; $i += $pagesPerMember) {
	$chunkSplit = array_slice($splitPages, $i, $pagesPerMember);
	$rawInput = array();
	$tokInput = array();
	foreach ($chunkSplit as $pg) {
		$pre = fractal_zip_text_stat_pred_preprocess((string) $pg['text'], array(
			'stat_model' => $model,
			'frozen' => true,
		));
		$rawInput[] = array('title' => (string) $pg['title'], 'origIndex' => (int) $pg['origIndex'], 'text' => (string) $pg['text']);
		$tokInput[] = array('title' => (string) $pg['title'], 'origIndex' => (int) $pg['origIndex'], 'text' => (string) $pre['payload']);
	}
	$rawLayout = fractal_zip_enwik_text_layout_apply($rawInput, $layoutId, array('seed' => 1));
	$tokLayout = fractal_zip_enwik_text_layout_apply($tokInput, $layoutId, array('seed' => 1));
	$rawBlob .= (string) $rawLayout['text_blob'];
	$tokBlob .= (string) $tokLayout['text_blob'];
}
echo 'fztx_text_blob_raw=' . strlen($rawBlob) . ' fztx_text_blob_tok=' . strlen($tokBlob)
	. ' delta=' . (strlen($tokBlob) - strlen($rawBlob)) . "\n";
echo 'gzip1_fztx_raw=' . strlen((string) gzencode($rawBlob, 1))
	. ' gzip1_fztx_tok=' . strlen((string) gzencode($tokBlob, 1)) . "\n";
