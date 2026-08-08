#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Mine full-corpus sorted-order FZPM model for stat_pred_inner and save frozen file.
 *
 * Output: benchmarks/.stat_pred_inner_model.fzpm (FZPM\x01, no sidecars)
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';

$pageLimit = 12041;
$maxWords = fractal_zip_enwik_stat_pred_inner_max_words();
$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.stat_pred_inner_model.fzpm';

foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--max-words=')) {
		$maxWords = max(1, (int) substr($arg, 12));
	}
	if (str_starts_with($arg, '--out=')) {
		$out = (string) substr($arg, 6);
	}
}

$model = fractal_zip_enwik_stat_pred_inner_mine_full_sorted_model($pageLimit, $maxWords, false);
$bin = fractal_zip_enwik_stat_pred_serialize_frozen_model($model);
if (file_put_contents($out, $bin) === false) {
	fwrite(STDERR, "stat_pred_inner model build: write failed {$out}\n");
	exit(1);
}

echo 'pages=' . $pageLimit
	. ' vocab_words=' . count($model['vocab'])
	. ' bigram_rows=' . count($model['bigram_succ'])
	. ' model_bytes=' . strlen($bin)
	. ' out=' . $out . "\n";
