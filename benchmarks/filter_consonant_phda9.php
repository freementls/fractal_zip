#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Mine consonant hybrid model, apply phda9 greedy gate, save frozen JSON.
 *
 * Usage:
 *   FRACTAL_ZIP_CONSONANT_PHDA9_GATE=1 php benchmarks/filter_consonant_phda9.php [--pages=384]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_CONSONANT_SK_COLLISION_FREE=1');
putenv('FRACTAL_ZIP_CONSONANT_SK_BARE=1');
putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
putenv('FRACTAL_ZIP_CONSONANT_PHDA9_GATE=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';

$pages = 384;
$outPath = '';
$pool = 512;
$maxAccept = 256;
$corpusId = 8;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--out=')) {
		$outPath = substr($arg, 6);
	} elseif (str_starts_with($arg, '--pool=')) {
		$pool = max(16, (int) substr($arg, 7));
	} elseif (str_starts_with($arg, '--max=')) {
		$maxAccept = max(1, (int) substr($arg, 6));
	} elseif (str_starts_with($arg, '--corpus=')) {
		$corpusId = max(8, min(9, (int) substr($arg, 9)));
	}
}
if ($outPath === '') {
	$outPath = fractal_zip_enwik_consonant_hybrid_default_model_json_path($pages);
	$outPath = preg_replace('/\.json$/', '_phda9.json', $outPath) ?? $outPath;
}

$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (!is_file($dict)) {
	$dict = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
}
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$src = fractal_zip_enwik_corpus_path($repo, $corpusId);
if (!is_file($src)) {
	fwrite(STDERR, "missing enwik{$corpusId} at {$src}\n");
	exit(1);
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$pageTexts = array();
$mineText = '';
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$pt = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
	$pageTexts[] = $pt;
	$mineText .= $pt;
}

putenv('FRACTAL_ZIP_CONSONANT_PHDA9_GATE=0');
putenv('FRACTAL_ZIP_CONSONANT_MODEL_JSON');
$rawModel = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);
$baseScore = fractal_zip_enwik_consonant_hybrid_phda9_wire_score($mineText, $rawModel);

$filtered = fractal_zip_enwik_consonant_hybrid_filter_phda9_greedy($pageTexts, $rawModel, array(
	'force_phda9_gate' => true,
	'phda9_greedy_pool' => $pool,
	'phda9_greedy_max' => $maxAccept,
));
$finalScore = fractal_zip_enwik_consonant_hybrid_phda9_wire_score($mineText, $filtered);

fractal_zip_enwik_consonant_hybrid_save_model_json($outPath, $filtered);

echo 'filter_consonant_phda9 pages=' . $n
	. ' raw_unique=' . count($rawModel['skeleton_unique'] ?? array())
	. ' gated_unique=' . count($filtered['skeleton_unique'] ?? array())
	. ' base_score=' . number_format($baseScore)
	. ' final_score=' . number_format($finalScore)
	. ' delta=' . number_format($finalScore - $baseScore)
	. ' out=' . $outPath . "\n";
