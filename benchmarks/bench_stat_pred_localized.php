#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Lab: monolithic vs localized (per-chunk) stat_pred models on entry-sorted slice.
 *
 * Measures token payload, gzip-1 proxy, and estimated FZPM ship cost (win-only rows per chunk).
 * Does not run full wire encode — use results to decide fractal FZPM wire experiments.
 *
 * Args:
 *   --pages=N          slice pages (default 384)
 *   --chunk-pages=N    pages per local model (default 128; 0 = monolithic only)
 *   --words-per-chunk=N max vocab per chunk (default 4096)
 *   --words-global=N   monolithic cap (default 16384)
 *   --codec=base94|varint|delta
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';

$pageLimit = 384;
$chunkPages = 128;
$wordsPerChunk = 4096;
$wordsGlobal = 16384;
$codec = 'base94';

foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--chunk-pages=')) {
		$chunkPages = max(0, (int) substr($arg, 14));
	}
	if (str_starts_with($arg, '--words-per-chunk=')) {
		$wordsPerChunk = max(256, (int) substr($arg, 18));
	}
	if (str_starts_with($arg, '--words-global=')) {
		$wordsGlobal = max(256, (int) substr($arg, 15));
	}
	if (str_starts_with($arg, '--codec=')) {
		$codec = strtolower(substr($arg, 8));
	}
}

putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=' . $codec);

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
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

/**
 * @param array<int, array<string, mixed>> $modelByChunk
 * @return array{payload: int, bigram_hits: int, literal_words: int, prev_used: array<string, true>, fzpm_raw: int, fzpm_gz: int}
 */
$encodeLocalized = static function (array $modelByChunk, int $cp) use ($splitPages, $n, $codec): array {
	$payload = '';
	$bigramHits = 0;
	$literalWords = 0;
	$prevUsed = array();
	$fzpmRaw = 0;
	$fzpmGz = 0;
	for ($i = 0; $i < $n; $i += $cp) {
		$chunkIdx = (int) ($i / $cp);
		$model = $modelByChunk[$chunkIdx] ?? $modelByChunk[0];
		$chunkPrevUsed = array();
		$chunkHits = 0;
		$chunkLit = 0;
		for ($pi = $i; $pi < min($n, $i + $cp); $pi++) {
			$pre = fractal_zip_text_stat_pred_preprocess((string) $splitPages[$pi]['text'], array(
				'stat_model' => $model,
				'frozen' => true,
				'payload_codec' => $codec,
			));
			$payload .= (string) $pre['payload'];
			$chunkHits += (int) ($pre['meta']['bigram_hits'] ?? 0);
			$chunkLit += (int) ($pre['meta']['literal_words'] ?? 0);
			foreach ((array) ($pre['meta']['bigram_prev_used'] ?? array()) as $pw) {
				$pw = (string) $pw;
				if ($pw !== '') {
					$chunkPrevUsed[$pw] = true;
					$prevUsed[$pw] = true;
				}
			}
		}
		$bigramHits += $chunkHits;
		$literalWords += $chunkLit;
		$trailer = fractal_zip_enwik_stat_pred_trailer_model(
			$model,
			array_keys($chunkPrevUsed),
			fractal_zip_enwik_stat_pred_inner_bigram_max_prev()
		);
		$bin = fractal_zip_enwik_stat_pred_serialize_frozen_model($trailer);
		$fzpmRaw += strlen($bin);
		$gz = gzencode($bin, 9);
		$fzpmGz += is_string($gz) ? strlen($gz) : 0;
	}
	return array(
		'payload' => strlen($payload),
		'bigram_hits' => $bigramHits,
		'literal_words' => $literalWords,
		'prev_used' => $prevUsed,
		'fzpm_raw' => $fzpmRaw,
		'fzpm_gz' => $fzpmGz,
	);
};

$mineText = '';
foreach ($splitPages as $pg) {
	$mineText .= (string) ($pg['text'] ?? '');
}
$globalModel = fractal_zip_enwik_stat_pred_mine_model($mineText, $wordsGlobal, null, false);
$globalPrev = array();
$globalPayload = '';
$globalHits = 0;
foreach ($splitPages as $pg) {
	$pre = fractal_zip_text_stat_pred_preprocess((string) $pg['text'], array(
		'stat_model' => $globalModel,
		'frozen' => true,
		'payload_codec' => $codec,
	));
	$globalPayload .= (string) $pre['payload'];
	$globalHits += (int) ($pre['meta']['bigram_hits'] ?? 0);
	foreach ((array) ($pre['meta']['bigram_prev_used'] ?? array()) as $pw) {
		$pw = (string) $pw;
		if ($pw !== '') {
			$globalPrev[$pw] = true;
		}
	}
}
$globalTrailer = fractal_zip_enwik_stat_pred_trailer_model($globalModel, array_keys($globalPrev), null);
$globalFzpm = fractal_zip_enwik_stat_pred_serialize_frozen_model($globalTrailer);
$globalFzpmGz = gzencode($globalFzpm, 9);

$rawMi = $mineText;
$rawLen = strlen($rawMi);

echo "pages={$n} codec={$codec} raw_mi={$rawLen}\n\n";

$printRow = static function (string $label, array $r) use ($rawLen, $n): void {
	$payload = (int) $r['payload'];
	$gzP = gzencode((string) ($r['_payload_blob'] ?? ''), 1);
	$gzLen = is_string($gzP) ? strlen($gzP) : 0;
	$amortTax = (int) ($r['fzpm_gz'] ?? 0);
	$amortMeta = $amortTax > 0
		? (int) round($amortTax * ($n / FRACTAL_ZIP_ENWIK_INNER_FOLD_FULL_PAGES))
		: 0;
	echo str_pad($label, 28)
		. ' payload=' . number_format($payload)
		. ' Δraw=' . number_format($payload - $rawLen)
		. ' gzip1=' . number_format($gzLen)
		. ' hits=' . number_format((int) ($r['bigram_hits'] ?? 0))
		. ' fzpm_raw=' . number_format((int) ($r['fzpm_raw'] ?? 0))
		. ' fzpm_gz=' . number_format($amortTax)
		. ' amort_meta~=' . number_format($amortMeta)
		. ' prev_rows=' . number_format(count($r['prev_used'] ?? array()))
		. "\n";
};

$globalRow = array(
	'payload' => strlen($globalPayload),
	'bigram_hits' => $globalHits,
	'prev_used' => $globalPrev,
	'fzpm_raw' => strlen($globalFzpm),
	'fzpm_gz' => is_string($globalFzpmGz) ? strlen($globalFzpmGz) : 0,
	'_payload_blob' => $globalPayload,
);
$printRow("global_{$wordsGlobal}w", $globalRow);

if ($chunkPages > 0) {
	$numChunks = (int) ceil($n / $chunkPages);
	$models = array();
	for ($ci = 0; $ci < $numChunks; $ci++) {
		$start = $ci * $chunkPages;
		$chunkText = '';
		for ($pi = $start; $pi < min($n, $start + $chunkPages); $pi++) {
			$chunkText .= (string) ($splitPages[$pi]['text'] ?? '');
		}
		$models[$ci] = fractal_zip_enwik_stat_pred_mine_model($chunkText, $wordsPerChunk, null, false);
	}
	$loc = $encodeLocalized($models, $chunkPages);
	$loc['_payload_blob'] = '';
	// rebuild blob for gzip line
	for ($i = 0; $i < $n; $i += $chunkPages) {
		$chunkIdx = (int) ($i / $chunkPages);
		$model = $models[$chunkIdx];
		for ($pi = $i; $pi < min($n, $i + $chunkPages); $pi++) {
			$pre = fractal_zip_text_stat_pred_preprocess((string) $splitPages[$pi]['text'], array(
				'stat_model' => $model,
				'frozen' => true,
				'payload_codec' => $codec,
			));
			$loc['_payload_blob'] .= (string) $pre['payload'];
		}
	}
	$printRow("local_{$numChunks}x{$wordsPerChunk}w@{$chunkPages}p", $loc);

	// Hierarchical: small global + per-chunk residual vocab
	$globalSmall = fractal_zip_enwik_stat_pred_mine_model($mineText, 2048, null, false);
	$globalSmallIdx = $globalSmall['vocab_index'];
	$modelsHier = array();
	for ($ci = 0; $ci < $numChunks; $ci++) {
		$start = $ci * $chunkPages;
		$chunkText = '';
		for ($pi = $start; $pi < min($n, $start + $chunkPages); $pi++) {
			$chunkText .= (string) ($splitPages[$pi]['text'] ?? '');
		}
		$local = fractal_zip_enwik_stat_pred_mine_model($chunkText, $wordsPerChunk, null, false);
		$mergedVocab = array_values(array_unique(array_merge($globalSmall['vocab'], $local['vocab'])));
		$mergedVocab = array_slice($mergedVocab, 0, $wordsPerChunk + 2048);
		$tables = fractal_zip_enwik_stat_isp_vocab_tables($mergedVocab);
		$mergedCounts = array();
		$carry = '';
		foreach (fractal_zip_enwik_text_segment_implicit_space($chunkText) as $seg) {
			if (($seg['type'] ?? '') !== 'word') {
				continue;
			}
			$w = (string) ($seg['text'] ?? '');
			if ($carry !== '' && isset($tables['vocab_index'][$carry]) && isset($tables['vocab_index'][$w])) {
				if (!isset($mergedCounts[$carry])) {
					$mergedCounts[$carry] = array();
				}
				if (!isset($mergedCounts[$carry][$w])) {
					$mergedCounts[$carry][$w] = 0;
				}
				$mergedCounts[$carry][$w]++;
			}
			$carry = $w;
		}
		$modelsHier[$ci] = fractal_zip_enwik_stat_pred_build_model_from_counts(
			array(),
			$mergedCounts,
			count($mergedVocab),
			fractal_zip_enwik_stat_pred_max_succ(),
			false
		);
		$modelsHier[$ci]['vocab'] = $tables['vocab'];
		$modelsHier[$ci]['vocab_index'] = $tables['vocab_index'];
	}
	$hier = $encodeLocalized($modelsHier, $chunkPages);
	$hier['_payload_blob'] = '';
	for ($i = 0; $i < $n; $i += $chunkPages) {
		$chunkIdx = (int) ($i / $chunkPages);
		$model = $modelsHier[$chunkIdx];
		for ($pi = $i; $pi < min($n, $i + $chunkPages); $pi++) {
			$pre = fractal_zip_text_stat_pred_preprocess((string) $splitPages[$pi]['text'], array(
				'stat_model' => $model,
				'frozen' => true,
				'payload_codec' => $codec,
			));
			$hier['_payload_blob'] .= (string) $pre['payload'];
		}
	}
	$printRow("hier_2k+local@{$chunkPages}p", $hier);
}

echo "\nNote: amort_meta uses fzpm_gz * slice/full; wire uses inner_fold seal (fractal+zpaq), not gz alone.\n";
