#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Quantify stat_pred wire overhead vs stat_isp / raw text on enwik8 slice.
 *
 * Usage: php benchmarks/bench_stat_pred_overhead.php [--pages=384]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$pages = $split['pages'];
$n = min($pageLimit, count($pages));
$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'origIndex' => $i,
		'start' => (int) $pages[$i]['start'],
		'len' => (int) $pages[$i]['len'],
	);
}
$splitPages = enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
$mineText = '';
$allText = '';
foreach ($splitPages as $pg) {
	$t = (string) ($pg['text'] ?? '');
	$mineText .= $t;
	$allText .= $t;
}

$model = fractal_zip_enwik_stat_pred_mine_model($mineText);
$opts = array('stat_model' => $model, 'frozen' => true);
$pre = fractal_zip_text_stat_pred_preprocess($allText, $opts);
$isp = fractal_zip_text_stat_isp_preprocess($allText, array(
	'vocab' => $model['vocab'],
	'vocab_index' => $model['vocab_index'],
	'frozen' => true,
));
$biIds = fractal_zip_enwik_stat_pred_compact_bigram_ids($model['bigram_succ'], $model['vocab_index']);
$predMetaJson = json_encode(array(
	'vocab' => $model['vocab'],
	'bigram_succ_ids' => $biIds,
	'frozen' => true,
));
$ispMetaJson = json_encode(array('vocab' => $model['vocab'], 'frozen' => true));

$index = $model['vocab_index'];
$br = $model['bigram_rank'];
$segs = fractal_zip_enwik_text_segment_implicit_space($allText);
$prev = '';
$rankOnlyWins = 0;
$redundantPrevWins = 0;
$vocabWords = 0;
foreach ($segs as $i => $seg) {
	if (($seg['type'] ?? '') !== 'word') {
		if (($seg['type'] ?? '') === 'gap') {
			$wordBefore = ($i > 0 && ($segs[$i - 1]['type'] ?? '') === 'word');
			$wordAfter = ($i + 1 < count($segs) && ($segs[$i + 1]['type'] ?? '') === 'word');
			if (!fractal_zip_enwik_text_gap_is_implicit((string) ($seg['text'] ?? ''), $wordBefore, $wordAfter)
				&& !fractal_zip_enwik_text_gap_is_implicit_trailing_period($segs, $i)) {
				$prev = '';
			}
		}
		continue;
	}
	$w = (string) ($seg['text'] ?? '');
	if (!isset($index[$w])) {
		$prev = $w;
		continue;
	}
	$vocabWords++;
	$wid = (int) $index[$w];
	$uniLen = 1 + strlen(fractal_zip_enwik_encode_varint_u32($wid));
	if ($prev !== '' && isset($br[$prev][$w])) {
		$rank = (int) $br[$prev][$w];
		$biOld = 1 + strlen(fractal_zip_enwik_encode_varint_u32((int) $index[$prev]))
			+ strlen(fractal_zip_enwik_encode_varint_u32($rank));
		$biNew = 1 + strlen(fractal_zip_enwik_encode_varint_u32($rank));
		if ($biOld < $uniLen) {
			$redundantPrevWins++;
		}
		if ($biNew < $uniLen) {
			$rankOnlyWins++;
		}
	}
	$prev = $w;
}

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'raw_text_bytes' => strlen($allText),
	'stat_pred_payload_bytes' => strlen((string) $pre['payload']),
	'stat_isp_payload_bytes' => strlen((string) $isp['payload']),
	'payload_delta_pred_minus_isp' => strlen((string) $pre['payload']) - strlen((string) $isp['payload']),
	'bigram_hits' => (int) ($pre['meta']['bigram_hits'] ?? 0),
	'unigram_hits' => (int) ($pre['meta']['unigram_hits'] ?? 0),
	'literal_words' => (int) ($pre['meta']['literal_words'] ?? 0),
	'vocab_size' => count($model['vocab']),
	'bigram_table_entries' => count($biIds),
	'bigram_succ_ids_json_bytes' => strlen($predMetaJson),
	'isp_vocab_json_bytes' => strlen($ispMetaJson),
	'meta_overhead_pred_minus_isp' => strlen($predMetaJson) - strlen($ispMetaJson),
	'hypothetical_rank_only_bigram_wins' => $rankOnlyWins,
	'hypothetical_redundant_prev_bigram_wins' => $redundantPrevWins,
	'vocab_words_in_stream' => $vocabWords,
);
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_stat_pred_overhead.json';
file_put_contents($outPath, json_encode($report, JSON_PRETTY_PRINT));

echo "stat_pred overhead ({$n} pages) → {$outPath}\n";
foreach ($report as $k => $v) {
	if ($k === 'generated') {
		continue;
	}
	echo "  {$k}: {$v}\n";
}
