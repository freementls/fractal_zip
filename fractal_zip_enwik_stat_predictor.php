<?php
declare(strict_types=1);

/**
 * Sorted-order statistical predictors for enwik8 text-inner (tier D).
 *
 * - stat_isp: frozen frequency vocab from title-sorted prefix + words_id_varint_isp
 * - stat_pred: order-1 bigram ranks on sorted-order model (Dasher / PPM shortcut)
 *
 * Wire overhead vs mono_mi @384p (2026-06-06 combo probe, bench_stat_pred_overhead.php):
 * - stat_isp: +345,668 B (.fz); stat_pred: +456,663 B (+111 KiB vs stat_isp)
 * - Payload −71,355 B vs stat_isp when bigram rank shortcuts fire (rank-only tag+rank)
 * - bigram_succ_ids JSON meta: ~792 KiB vs isp vocab-only ~166 KiB (+625 KiB table tax)
 * - Net wire regression vs mono_mi: sidecar/meta + vocab transform dominate; lab-only
 *
 * Env:
 * - FRACTAL_ZIP_ENWIK_STAT_PRED_MAX_SUCC — bigram successors per word (default 48)
 * - FRACTAL_ZIP_ENWIK_STAT_ISP_MAX_WORDS — frozen vocab size (default 16384)
 * - FRACTAL_ZIP_STAT_PRED_MAX_WORDS — alias for stat_pred vocab cap (overrides STAT_ISP_MAX_WORDS)
 * - FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV — cap sparse FZPM rows to top-N prev words by transition mass (stat_pred_inner)
 * - FRACTAL_ZIP_STAT_PRED_INNER_PRUNE_ENCODE=1 — prune bigram at mine time (legacy); default trailer-only prune
 * - FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE — prebuilt FZPM model (no sidecars); see benchmarks/build_stat_pred_inner_model.php
 * - FRACTAL_ZIP_STAT_PRED_INNER_MINE_FULL=1 — mine encode model from full enwik8 sorted text (not slice prefix)
 * - FRACTAL_ZIP_STAT_PRED_INNER_MINE_PATH — corpus for MINE_FULL / model build (default test_files109/enwik8)
 * - FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC — `varint` (default), `delta` (zigzag id deltas), or `base94` (lab)
 * - FRACTAL_ZIP_STAT_PRED_INNER_MEMBER_FOLD=1 — FZPM vocab+bigram in FZTX member FZSP (no FZEP trailer); mono fztx only
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

const FRACTAL_ZIP_STAT_PRED_TAG_BIGRAM = 5;

function fractal_zip_enwik_stat_isp_max_words(): int
{
	$pred = getenv('FRACTAL_ZIP_STAT_PRED_MAX_WORDS');
	if ($pred !== false && trim((string) $pred) !== '' && ctype_digit(trim((string) $pred))) {
		return max(256, min(65536, (int) trim((string) $pred)));
	}
	$e = getenv('FRACTAL_ZIP_ENWIK_STAT_ISP_MAX_WORDS');
	if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
		return 16384;
	}
	return max(256, min(65536, (int) trim((string) $e)));
}

function fractal_zip_enwik_stat_pred_max_succ(): int
{
	$e = getenv('FRACTAL_ZIP_ENWIK_STAT_PRED_MAX_SUCC');
	if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
		return 48;
	}
	return max(4, min(256, (int) trim((string) $e)));
}

/** Default vocab cap for stat_pred_inner inner-fold (16384 matches wire stat_pred cap). */
function fractal_zip_enwik_stat_pred_inner_max_words(): int
{
	$e = getenv('FRACTAL_ZIP_STAT_PRED_INNER_MAX_WORDS');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(256, min(65536, (int) trim((string) $e)));
	}
	return 16384;
}

/** Legacy: prune bigram_succ at mine time (affects encode). Default off — trailer-only prune. */
function fractal_zip_enwik_stat_pred_inner_prune_encode(): bool
{
	$e = getenv('FRACTAL_ZIP_STAT_PRED_INNER_PRUNE_ENCODE');
	return $e !== false && trim((string) $e) !== '' && trim((string) $e) !== '0';
}

/** Top-N prev-word bigram rows for stat_pred_inner FZPM (null = win-only prune, no cap). */
function fractal_zip_enwik_stat_pred_inner_bigram_max_prev(): ?int
{
	$e = getenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV');
	if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
		return null;
	}
	$v = (int) trim((string) $e);
	return $v > 0 ? $v : null;
}

/** Optional prebuilt FZPM model path (null when unset or not a readable file). */
function fractal_zip_enwik_stat_pred_inner_model_file(): ?string
{
	$file = getenv('FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE');
	if ($file === false || trim((string) $file) === '') {
		return null;
	}
	$path = trim((string) $file);
	return is_file($path) ? $path : null;
}

/** Mine stat_pred_inner model from full enwik8 instead of slice prefix text. */
function fractal_zip_enwik_stat_pred_inner_mine_full_enabled(): bool
{
	$mineFull = getenv('FRACTAL_ZIP_STAT_PRED_INNER_MINE_FULL');
	return $mineFull !== false && trim((string) $mineFull) !== '' && trim((string) $mineFull) !== '0';
}

/** Fold FZPM into FZTX member payload (FZSP); skips separate FZEP inner-fold trailer. Mono fztx only. */
function fractal_zip_enwik_stat_pred_inner_member_fold_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_STAT_PRED_INNER_MEMBER_FOLD');
	return $e !== false && trim((string) $e) !== '' && trim((string) $e) !== '0';
}

function fractal_zip_enwik_stat_pred_inner_default_mine_path(): string
{
	$minePath = getenv('FRACTAL_ZIP_STAT_PRED_INNER_MINE_PATH');
	if ($minePath !== false && trim((string) $minePath) !== '') {
		return trim((string) $minePath);
	}
	return __DIR__ . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
}

/**
 * Concat sorted-order page texts from enwik8 for full-corpus stat_pred_inner mining.
 */
function fractal_zip_enwik_stat_pred_inner_mine_full_text(int $pageLimit = 12041): string
{
	$minePath = fractal_zip_enwik_stat_pred_inner_default_mine_path();
	if (!is_file($minePath)) {
		return '';
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$blob = (string) file_get_contents($minePath);
	$split = enwik_split_page_refs($blob);
	if (!is_array($split)) {
		return '';
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
	foreach ($splitPages as $pg) {
		$mineText .= (string) ($pg['text'] ?? '');
	}
	return $mineText;
}

/**
 * Expand compact FZPM meta (vocab + bigram_succ_ids) to full stat_pred model tables.
 *
 * @param array{vocab?: list<string>, bigram_succ_ids?: array<string, list<int>>} $compact
 * @return array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>
 * }
 */
function fractal_zip_enwik_stat_pred_model_from_compact_meta(array $compact): array
{
	$vocab = is_array($compact['vocab'] ?? null) ? array_values($compact['vocab']) : array();
	$index = array();
	foreach ($vocab as $i => $w) {
		$index[(string) $w] = (int) $i;
	}
	$bigramSucc = fractal_zip_enwik_stat_pred_expand_bigram_ids(
		is_array($compact['bigram_succ_ids'] ?? null) ? $compact['bigram_succ_ids'] : array(),
		$vocab
	);
	$bigramRank = array();
	foreach ($bigramSucc as $prev => $succList) {
		$rank = array();
		foreach ($succList as $ri => $sw) {
			$rank[(string) $sw] = (int) $ri;
		}
		$bigramRank[(string) $prev] = $rank;
	}
	return array(
		'vocab' => $vocab,
		'vocab_index' => $index,
		'bigram_succ' => $bigramSucc,
		'bigram_rank' => $bigramRank,
	);
}

/**
 * Load frozen stat_pred_inner model from FZPM binary (vocab + bigram table, no sidecars).
 *
 * @return array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>
 * }
 */
function fractal_zip_enwik_stat_pred_load_frozen_model_file(string $path): array
{
	$raw = (string) file_get_contents($path);
	if ($raw === '' || !str_starts_with($raw, FRACTAL_ZIP_STAT_PRED_META_MAGIC)) {
		throw new RuntimeException('stat_pred_inner: frozen model file is not FZPM: ' . $path);
	}
	$compact = fractal_zip_enwik_stat_pred_deserialize_compact_meta($raw);
	return fractal_zip_enwik_stat_pred_model_from_compact_meta($compact);
}

/**
 * Serialize full stat_pred model to FZPM binary (no sidecars; uint16 bigram ids).
 *
 * @param array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank?: array<string, array<string, int>>
 * } $model
 */
function fractal_zip_enwik_stat_pred_serialize_frozen_model(array $model): string
{
	return fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
		'preprocess' => 'stat_pred_inner',
		'frozen' => true,
		'include_sidecars' => false,
		'bigram_uint16' => true,
		'vocab' => is_array($model['vocab'] ?? null) ? $model['vocab'] : array(),
		'bigram_succ_ids' => fractal_zip_enwik_stat_pred_compact_bigram_ids(
			is_array($model['bigram_succ'] ?? null) ? $model['bigram_succ'] : array(),
			is_array($model['vocab_index'] ?? null) ? $model['vocab_index'] : array()
		),
	));
}

/**
 * Frozen stat_pred_inner model: FZPM file override, else full-corpus mine, else slice mine.
 *
 * @return array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>,
 *   transition_counts?: array<string, array<string, int>>
 * }
 */
function fractal_zip_enwik_stat_pred_inner_frozen_model(string $sliceMineText = ''): array
{
	static $cached = null;
	if (is_array($cached)) {
		return $cached;
	}
	$modelFile = fractal_zip_enwik_stat_pred_inner_model_file();
	if ($modelFile !== null) {
		$cached = fractal_zip_enwik_stat_pred_load_frozen_model_file($modelFile);
		return $cached;
	}
	$maxWords = fractal_zip_enwik_stat_pred_inner_max_words();
	$maxSucc = fractal_zip_enwik_stat_pred_max_succ();
	if (fractal_zip_enwik_stat_pred_inner_mine_full_enabled()) {
		$cached = fractal_zip_enwik_stat_pred_inner_mine_full_sorted_model(null, $maxWords, false);
		return $cached;
	}
	if ($sliceMineText !== '') {
		$pruneAtMine = fractal_zip_enwik_stat_pred_inner_prune_encode();
		$cached = fractal_zip_enwik_stat_pred_mine_model($sliceMineText, $maxWords, $maxSucc, $pruneAtMine);
		return $cached;
	}
	throw new RuntimeException('stat_pred_inner: no mine text (set MODEL_FILE, MINE_FULL=1, or pass slice text)');
}

/**
 * Incremental full-corpus mine (sorted page order) for frozen FZPM build / MINE_FULL.
 *
 * @return array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>,
 *   transition_counts?: array<string, array<string, int>>
 * }
 */
function fractal_zip_enwik_stat_pred_inner_mine_full_sorted_model(?int $pageLimit = null, ?int $maxWords = null, bool $pruneAtMine = false): array
{
	$minePath = fractal_zip_enwik_stat_pred_inner_default_mine_path();
	if (!is_file($minePath)) {
		throw new RuntimeException('stat_pred_inner: mine path missing ' . $minePath);
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$blob = (string) file_get_contents($minePath);
	$split = enwik_split_page_refs($blob);
	if (!is_array($split)) {
		throw new RuntimeException('stat_pred_inner: enwik split failed');
	}
	$pages = $split['pages'];
	$n = $pageLimit !== null ? min($pageLimit, count($pages)) : count($pages);
	$sortedChunk = array();
	for ($i = 0; $i < $n; $i++) {
		$sortedChunk[] = array(
			'origIndex' => $i,
			'start' => (int) $pages[$i]['start'],
			'len' => (int) $pages[$i]['len'],
		);
	}
	$splitPages = enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
	$wordCounts = array();
	foreach ($splitPages as $pg) {
		fractal_zip_enwik_stat_pred_accumulate_word_counts((string) ($pg['text'] ?? ''), $wordCounts);
	}
	$maxWords = $maxWords ?? fractal_zip_enwik_stat_pred_inner_max_words();
	arsort($wordCounts, SORT_NUMERIC);
	$vocab = array_slice(array_keys($wordCounts), 0, max(1, $maxWords));
	$tables = fractal_zip_enwik_stat_isp_vocab_tables($vocab);
	$transitionCounts = array();
	$carryPrev = '';
	foreach ($splitPages as $pg) {
		fractal_zip_enwik_stat_pred_accumulate_transition_counts(
			(string) ($pg['text'] ?? ''),
			$transitionCounts,
			$tables['vocab_index'],
			$carryPrev
		);
	}
	return fractal_zip_enwik_stat_pred_build_model_from_counts(
		$wordCounts,
		$transitionCounts,
		$maxWords,
		null,
		$pruneAtMine
	);
}

/** Wire default for stat_pred_inner when env unset (base94 wins ~12.5 KiB amortized @384p vs varint). */
function fractal_zip_enwik_stat_pred_inner_default_payload_codec(): string
{
	return 'base94';
}

/** `varint`, `delta`, or `base94` line-oriented chunks. */
function fractal_zip_enwik_stat_pred_payload_codec(): string
{
	$e = getenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC');
	if ($e === false || trim((string) $e) === '') {
		return 'varint';
	}
	$v = strtolower(trim((string) $e));
	if ($v === 'base94' || $v === 'delta') {
		return $v;
	}
	return 'varint';
}

function fractal_zip_enwik_stat_pred_scheme_for_codec(string $codec): string
{
	if ($codec === 'base94') {
		return 'stat_pred_bigram_base94_isp';
	}
	if ($codec === 'delta') {
		return 'stat_pred_bigram_delta_isp';
	}
	return 'stat_pred_bigram_isp';
}

function fractal_zip_enwik_encode_varint_zigzag_i32(int $n): string
{
	if ($n < 0) {
		$u = ((- $n) << 1) - 1;
	} else {
		$u = $n << 1;
	}
	return fractal_zip_enwik_encode_varint_u32($u);
}

/** @return array{0: int, 1: int}|null */
function fractal_zip_enwik_decode_varint_zigzag_i32(string $blob, int $offset): ?array
{
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $offset);
	if ($dv === null) {
		return null;
	}
	$u = (int) $dv[0];
	if ($u & 1) {
		$n = - (int) (($u + 1) >> 1);
	} else {
		$n = (int) ($u >> 1);
	}
	return array($n, (int) $dv[1]);
}

function fractal_zip_enwik_stat_pred_uni_chunk_len(int $wid, ?string $codec = null): int
{
	$codec = $codec ?? fractal_zip_enwik_stat_pred_payload_codec();
	if ($codec === 'base94') {
		$ba = fractal_zip_enwik_text_base94_alphabet();
		return strlen('w' . fractal_zip_enwik_text_encode_base_n($wid, $ba['base'], $ba['alphabet']));
	}
	return 1 + strlen(fractal_zip_enwik_encode_varint_u32($wid));
}

function fractal_zip_enwik_stat_pred_bi_chunk_len(int $rank, ?string $codec = null): int
{
	$codec = $codec ?? fractal_zip_enwik_stat_pred_payload_codec();
	if ($codec === 'base94') {
		$ba = fractal_zip_enwik_text_base94_alphabet();
		return strlen('b' . fractal_zip_enwik_text_encode_base_n($rank, $ba['base'], $ba['alphabet']));
	}
	return 1 + strlen(fractal_zip_enwik_encode_varint_u32($rank));
}

function fractal_zip_enwik_stat_pred_bigram_rank_shorter_than_unigram(int $rank, int $wid, ?string $codec = null): bool
{
	return fractal_zip_enwik_stat_pred_bi_chunk_len($rank, $codec) < fractal_zip_enwik_stat_pred_uni_chunk_len($wid, $codec);
}

/**
 * Drop bigram rows/successors that never beat unigram encoding; optionally cap to top-N prev by mass.
 *
 * @param array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>
 * } $model
 * @param array<string, array<string, int>> $transitionCounts
 * @return array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>
 * }
 */
function fractal_zip_enwik_stat_pred_prune_bigram_model(array $model, array $transitionCounts = array(), ?int $maxPrev = null): array
{
	$index = $model['vocab_index'];
	$bigramSucc = is_array($model['bigram_succ'] ?? null) ? $model['bigram_succ'] : array();
	$bigramRank = is_array($model['bigram_rank'] ?? null) ? $model['bigram_rank'] : array();
	$scored = array();
	foreach ($bigramSucc as $prev => $succList) {
		if (!isset($index[$prev]) || !is_array($succList)) {
			continue;
		}
		$winning = array();
		$winningRank = array();
		$prevRanks = is_array($bigramRank[$prev] ?? null) ? $bigramRank[$prev] : array();
		foreach ($succList as $sw) {
			if (!isset($index[$sw])) {
				continue;
			}
			$wid = (int) $index[$sw];
			$rank = (int) ($prevRanks[$sw] ?? 999);
			if (!fractal_zip_enwik_stat_pred_bigram_rank_shorter_than_unigram($rank, $wid)) {
				continue;
			}
			$winning[] = $sw;
			$winningRank[$sw] = count($winning) - 1;
		}
		if ($winning === array()) {
			continue;
		}
		$mass = 0;
		if (isset($transitionCounts[$prev]) && is_array($transitionCounts[$prev])) {
			foreach ($winning as $sw) {
				$mass += (int) ($transitionCounts[$prev][$sw] ?? 0);
			}
		} else {
			$mass = count($winning);
		}
		$scored[] = array(
			'prev' => (string) $prev,
			'succ' => $winning,
			'rank' => $winningRank,
			'mass' => $mass,
		);
	}
	if ($maxPrev !== null && $maxPrev > 0 && count($scored) > $maxPrev) {
		usort($scored, static function (array $a, array $b): int {
			return $b['mass'] <=> $a['mass'];
		});
		$scored = array_slice($scored, 0, $maxPrev);
	}
	$newSucc = array();
	$newRank = array();
	foreach ($scored as $row) {
		$newSucc[$row['prev']] = $row['succ'];
		$newRank[$row['prev']] = $row['rank'];
	}
	return array_merge($model, array(
		'bigram_succ' => $newSucc,
		'bigram_rank' => $newRank,
	));
}

/**
 * FZPM trailer: ship prev rows that emitted bigram tokens; trim succ tails past max emitted rank.
 *
 * @param list<string> $usedPrevs
 * @param array<string, array<int, true>> $ranksUsed prev => emitted rank => true
 */
function fractal_zip_enwik_stat_pred_trailer_model(
	array $model,
	array $usedPrevs = array(),
	?int $maxPrev = null,
	array $ranksUsed = array()
): array {
	if (fractal_zip_enwik_stat_pred_inner_prune_encode()) {
		return $model;
	}
	$counts = is_array($model['transition_counts'] ?? null) ? $model['transition_counts'] : array();
	if ($usedPrevs === array()) {
		return fractal_zip_enwik_stat_pred_prune_bigram_model($model, $counts, $maxPrev);
	}
	$succ = is_array($model['bigram_succ'] ?? null) ? $model['bigram_succ'] : array();
	$rank = is_array($model['bigram_rank'] ?? null) ? $model['bigram_rank'] : array();
	$usedSet = array();
	foreach ($usedPrevs as $p) {
		$p = (string) $p;
		if ($p !== '') {
			$usedSet[$p] = true;
		}
	}
	$rows = array();
	foreach ($succ as $prev => $list) {
		if (!isset($usedSet[$prev]) || !is_array($list)) {
			continue;
		}
		$maxEmitted = -1;
		if (isset($ranksUsed[$prev]) && is_array($ranksUsed[$prev])) {
			foreach (array_keys($ranksUsed[$prev]) as $r) {
				$maxEmitted = max($maxEmitted, (int) $r);
			}
		}
		if ($maxEmitted >= 0 && count($list) > $maxEmitted + 1) {
			$list = array_slice($list, 0, $maxEmitted + 1);
		}
		$mass = 0;
		if (isset($counts[$prev]) && is_array($counts[$prev])) {
			foreach ($counts[$prev] as $c) {
				$mass += (int) $c;
			}
		} else {
			$mass = count($list);
		}
		$rows[] = array(
			'prev' => (string) $prev,
			'succ' => $list,
			'rank' => is_array($rank[$prev] ?? null) ? $rank[$prev] : array(),
			'mass' => $mass,
		);
	}
	if ($maxPrev !== null && $maxPrev > 0 && count($rows) > $maxPrev) {
		usort($rows, static function (array $a, array $b): int {
			return $b['mass'] <=> $a['mass'];
		});
		$top = array_slice($rows, 0, $maxPrev);
		$topPrevs = array();
		foreach ($top as $row) {
			$topPrevs[(string) $row['prev']] = true;
		}
		foreach ($rows as $row) {
			$prev = (string) $row['prev'];
			if (isset($ranksUsed[$prev]) && !isset($topPrevs[$prev])) {
				$top[] = $row;
				$topPrevs[$prev] = true;
			}
		}
		$rows = $top;
	}
	$newSucc = array();
	$newRank = array();
	foreach ($rows as $row) {
		$newSucc[$row['prev']] = $row['succ'];
		$newRank[$row['prev']] = $row['rank'];
	}
	return array_merge($model, array(
		'bigram_succ' => $newSucc,
		'bigram_rank' => $newRank,
	));
}

/**
 * Mine frequency-sorted word vocab from sorted-order text (local shortcut to entry sort).
 *
 * @return list<string>
 */
function fractal_zip_enwik_stat_isp_mine_vocab(string $mineText, ?int $maxWords = null): array
{
	$maxWords = $maxWords ?? fractal_zip_enwik_stat_isp_max_words();
	$counts = array();
	foreach (fractal_zip_enwik_text_segment_implicit_space($mineText) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$tok = (string) ($seg['text'] ?? '');
		if (strlen($tok) < 2) {
			continue;
		}
		if (!isset($counts[$tok])) {
			$counts[$tok] = 0;
		}
		$counts[$tok]++;
	}
	arsort($counts, SORT_NUMERIC);
	return array_slice(array_keys($counts), 0, max(1, $maxWords));
}

/**
 * @return array{vocab: list<string>, vocab_index: array<string, int>}
 */
function fractal_zip_enwik_stat_isp_vocab_tables(array $vocab): array
{
	return array(
		'vocab' => array_values($vocab),
		'vocab_index' => fractal_zip_enwik_text_vocab_index(array_values($vocab)),
	);
}

/**
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_stat_isp_preprocess(string $text, array $opts = array()): array
{
	$vocab = $opts['vocab'] ?? null;
	if (!is_array($vocab) || $vocab === array()) {
		$vocab = fractal_zip_enwik_stat_isp_mine_vocab($text);
	}
	$tables = fractal_zip_enwik_stat_isp_vocab_tables($vocab);
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$packed = fractal_zip_enwik_text_encode_words_stream('words_id_varint_isp', $segments, array(
		'vocab' => $tables['vocab'],
		'vocab_index' => $tables['vocab_index'],
	));
	$sidecar = array_merge($packed['sidecar'], array(
		'preprocess' => 'stat_isp',
		'scheme' => 'words_id_varint_isp',
		'frozen' => !empty($opts['frozen']),
	));
	if (!empty($opts['frozen'])) {
		unset($sidecar['vocab']);
	}
	return array(
		'payload' => (string) $packed['payload'],
		'sidecar' => $sidecar,
		'meta' => $packed['meta'] ?? array(),
	);
}

/** @param array<string, int> $wordCounts */
function fractal_zip_enwik_stat_pred_accumulate_word_counts(string $mineText, array &$wordCounts): void
{
	foreach (fractal_zip_enwik_text_segment_implicit_space($mineText) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$tok = (string) ($seg['text'] ?? '');
		if (strlen($tok) < 2) {
			continue;
		}
		if (!isset($wordCounts[$tok])) {
			$wordCounts[$tok] = 0;
		}
		$wordCounts[$tok]++;
	}
}

/**
 * @param array<string, array<string, int>> $transitionCounts
 * @param array<string, int> $index
 */
function fractal_zip_enwik_stat_pred_accumulate_transition_counts(
	string $mineText,
	array &$transitionCounts,
	array $index,
	string &$carryPrev = ''
): void {
	$prev = $carryPrev;
	foreach (fractal_zip_enwik_text_segment_implicit_space($mineText) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$w = (string) ($seg['text'] ?? '');
		if ($prev !== '' && isset($index[$prev]) && isset($index[$w])) {
			if (!isset($transitionCounts[$prev])) {
				$transitionCounts[$prev] = array();
			}
			if (!isset($transitionCounts[$prev][$w])) {
				$transitionCounts[$prev][$w] = 0;
			}
			$transitionCounts[$prev][$w]++;
		}
		$prev = $w;
	}
	$carryPrev = $prev;
}

/**
 * @param array<string, int> $wordCounts
 * @param array<string, array<string, int>> $transitionCounts
 * @return array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>,
 *   transition_counts: array<string, array<string, int>>
 * }
 */
function fractal_zip_enwik_stat_pred_build_model_from_counts(
	array $wordCounts,
	array $transitionCounts,
	?int $maxWords = null,
	?int $maxSucc = null,
	bool $pruneBigram = false
): array {
	$maxWords = $maxWords ?? fractal_zip_enwik_stat_isp_max_words();
	$maxSucc = $maxSucc ?? fractal_zip_enwik_stat_pred_max_succ();
	arsort($wordCounts, SORT_NUMERIC);
	$vocab = array_slice(array_keys($wordCounts), 0, max(1, $maxWords));
	$tables = fractal_zip_enwik_stat_isp_vocab_tables($vocab);
	$index = $tables['vocab_index'];

	$counts = array();
	foreach ($transitionCounts as $p => $succCounts) {
		if (!isset($index[$p]) || !is_array($succCounts)) {
			continue;
		}
		$filtered = array();
		foreach ($succCounts as $w => $c) {
			if (isset($index[$w])) {
				$filtered[$w] = (int) $c;
			}
		}
		if ($filtered !== array()) {
			$counts[$p] = $filtered;
		}
	}

	$bigramSucc = array();
	$bigramRank = array();
	foreach ($counts as $p => $succCounts) {
		arsort($succCounts, SORT_NUMERIC);
		$succList = array_slice(array_keys($succCounts), 0, $maxSucc);
		$bigramSucc[$p] = $succList;
		$rank = array();
		foreach ($succList as $ri => $sw) {
			$rank[$sw] = $ri;
		}
		$bigramRank[$p] = $rank;
	}

	$model = array(
		'vocab' => $tables['vocab'],
		'vocab_index' => $index,
		'bigram_succ' => $bigramSucc,
		'bigram_rank' => $bigramRank,
		'transition_counts' => $counts,
	);
	if ($pruneBigram) {
		$model = fractal_zip_enwik_stat_pred_prune_bigram_model(
			$model,
			$counts,
			fractal_zip_enwik_stat_pred_inner_bigram_max_prev()
		);
	}
	return $model;
}

/**
 * Order-1 bigram model from sorted-order text.
 *
 * @return array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>,
 *   transition_counts?: array<string, array<string, int>>
 * }
 */
function fractal_zip_enwik_stat_pred_mine_model(string $mineText, ?int $maxWords = null, ?int $maxSucc = null, bool $pruneBigram = false): array
{
	$wordCounts = array();
	fractal_zip_enwik_stat_pred_accumulate_word_counts($mineText, $wordCounts);
	$maxWords = $maxWords ?? fractal_zip_enwik_stat_isp_max_words();
	arsort($wordCounts, SORT_NUMERIC);
	$vocab = array_slice(array_keys($wordCounts), 0, max(1, $maxWords));
	$tables = fractal_zip_enwik_stat_isp_vocab_tables($vocab);
	$transitionCounts = array();
	$carryPrev = '';
	fractal_zip_enwik_stat_pred_accumulate_transition_counts($mineText, $transitionCounts, $tables['vocab_index'], $carryPrev);
	return fractal_zip_enwik_stat_pred_build_model_from_counts(
		$wordCounts,
		$transitionCounts,
		$maxWords,
		$maxSucc,
		$pruneBigram
	);
}

/**
 * Compact bigram table for FZEP preprocess meta (prev_id → list of succ_ids).
 *
 * @param array<string, list<string>> $bigramSucc
 * @param array<string, int> $index
 * @return array<string, list<int>>
 */
function fractal_zip_enwik_stat_pred_compact_bigram_ids(array $bigramSucc, array $index): array
{
	$out = array();
	foreach ($bigramSucc as $prev => $succList) {
		if (!isset($index[$prev])) {
			continue;
		}
		$ids = array();
		foreach ($succList as $sw) {
			if (isset($index[$sw])) {
				$ids[] = (int) $index[$sw];
			}
		}
		if ($ids !== array()) {
			$out[(string) (int) $index[$prev]] = $ids;
		}
	}
	return $out;
}

/**
 * @param array<string, list<int>> $biIds
 * @param array<int, int> $indexMap
 * @return array<string, list<int>>
 */
function fractal_zip_enwik_stat_pred_remap_bigram_ids(array $biIds, array $indexMap): array
{
	$out = array();
	foreach ($biIds as $pid => $ids) {
		$srcPid = (int) $pid;
		$dstPid = (int) ($indexMap[$srcPid] ?? $srcPid);
		$newIds = array();
		foreach ($ids as $sid) {
			$srcSid = (int) $sid;
			$newIds[] = (int) ($indexMap[$srcSid] ?? $srcSid);
		}
		$out[(string) $dstPid] = $newIds;
	}
	return $out;
}

/**
 * @param list<string> $vocab
 * @return array{lex_vocab: list<string>, lex_to_old: list<int>, old_to_new: array<int, int>}
 */
function fractal_zip_enwik_stat_pred_build_lex_vocab_perm(array $vocab): array
{
	$n = count($vocab);
	$order = range(0, $n - 1);
	usort($order, static function (int $a, int $b) use ($vocab): int {
		return strcmp((string) $vocab[$a], (string) $vocab[$b]);
	});
	$lexVocab = array();
	$lexToOld = array();
	$oldToNew = array();
	foreach ($order as $lexIdx => $oldIdx) {
		$oldIdx = (int) $oldIdx;
		$lexVocab[] = (string) $vocab[$oldIdx];
		$lexToOld[] = $oldIdx;
		$oldToNew[$oldIdx] = (int) $lexIdx;
	}
	return array(
		'lex_vocab' => $lexVocab,
		'lex_to_old' => $lexToOld,
		'old_to_new' => $oldToNew,
	);
}

/** @param list<int> $lexToOld */
function fractal_zip_enwik_stat_pred_encode_vocab_perm_uint16(array $lexToOld): string
{
	$buf = '';
	foreach ($lexToOld as $old) {
		$old = (int) $old;
		$buf .= chr($old & 0xff) . chr(($old >> 8) & 0xff);
	}
	return $buf;
}

/**
 * @return array{0: list<int>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_vocab_perm_uint16(string $blob, int $off, int $len, int $count): array
{
	$perm = array();
	for ($i = 0; $i < $count; $i++) {
		if ($off + 2 > $len) {
			throw new RuntimeException('stat_pred compact meta: truncated vocab perm');
		}
		$perm[] = ord($blob[$off]) | (ord($blob[$off + 1]) << 8);
		$off += 2;
	}
	return array($perm, $off);
}

/**
 * @param list<string> $lexVocab
 * @param list<int> $lexToOld
 * @return list<string>
 */
function fractal_zip_enwik_stat_pred_restore_vocab_from_lex_perm(array $lexVocab, array $lexToOld): array
{
	$n = count($lexVocab);
	$vocab = array_fill(0, $n, '');
	foreach ($lexVocab as $lexIdx => $word) {
		$oldIdx = (int) ($lexToOld[$lexIdx] ?? $lexIdx);
		$vocab[$oldIdx] = (string) $word;
	}
	return $vocab;
}

/**
 * @param array<string, list<int>> $compact
 * @param list<string> $vocab
 * @return array<string, list<string>>
 */
function fractal_zip_enwik_stat_pred_expand_bigram_ids(array $compact, array $vocab): array
{
	$out = array();
	foreach ($compact as $pid => $ids) {
		$prev = (string) ($vocab[(int) $pid] ?? '');
		if ($prev === '') {
			continue;
		}
		$list = array();
		foreach ($ids as $sid) {
			$list[] = (string) ($vocab[(int) $sid] ?? '');
		}
		$out[$prev] = $list;
	}
	return $out;
}

const FRACTAL_ZIP_STAT_PRED_META_MAGIC = "FZPM\x01";
const FRACTAL_ZIP_STAT_PRED_META_VERSION = 1;
/** uint16 LE succ ids per row (smaller than varint when vocab > 127). */
const FRACTAL_ZIP_STAT_PRED_META_VERSION_UINT16 = 2;
/** Front-coded vocab + per-row uint16-abs vs zigzag-delta succ pick. */
const FRACTAL_ZIP_STAT_PRED_META_VERSION_PACKED = 3;
/** Sparse rank→succ pairs (only emitted ranks per prev row). */
const FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE = 4;
/** v4 sparse + implicit rank-0 rows (pairCount=0 → single rank-0 succ). */
const FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE_IMPLICIT0 = 6;
/** v4 sparse + lex-sorted front-coded vocab + uint16 lex→old perm. */
const FRACTAL_ZIP_STAT_PRED_META_VERSION_LEX_SPARSE = 5;
const FRACTAL_ZIP_STAT_PRED_META_VOCAB_PLAIN = 0;
const FRACTAL_ZIP_STAT_PRED_META_VOCAB_FRONT = 1;
const FRACTAL_ZIP_STAT_PRED_META_SUCC_ABS_U16 = 0;
const FRACTAL_ZIP_STAT_PRED_META_SUCC_DELTA_ZZ = 1;
const FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC = "FZPS\x01";
/** Global gap pool + per-page indices (dedupes repeated punctuation/HTML gaps). */
const FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL = "FZPS\x02";
/** FZPS v3: v2 pool + 1-byte payload scheme id (all pages share codec). */
const FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V3 = "FZPS\x03";
/** FZPS v4: v3 + front-coded freq-sorted global gap strings. */
const FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V4 = "FZPS\x04";

/**
 * @param list<array<string, mixed>> $sidecars
 * @return array{global_gaps: list<string>, global_index: array<string, int>, page_refs: list<array{flags: int, refs: list<int>}>}
 */
function fractal_zip_enwik_stat_pred_build_gap_pool_from_sidecars(array $sidecars): array
{
	$gapFreq = array();
	foreach ($sidecars as $sc) {
		$gaps = is_array($sc['gaps'] ?? null) ? $sc['gaps'] : array();
		foreach ($gaps as $g) {
			$g = (string) $g;
			if ($g === '') {
				continue;
			}
			if (!isset($gapFreq[$g])) {
				$gapFreq[$g] = 0;
			}
			$gapFreq[$g]++;
		}
	}
	$globalGaps = array_map('strval', array_keys($gapFreq));
	usort($globalGaps, static function (string $a, string $b) use ($gapFreq): int {
		$fa = (int) ($gapFreq[$a] ?? 0);
		$fb = (int) ($gapFreq[$b] ?? 0);
		if ($fa !== $fb) {
			return $fb <=> $fa;
		}
		return strcmp($a, $b);
	});
	$globalIndex = array();
	foreach ($globalGaps as $i => $g) {
		$globalIndex[$g] = (int) $i;
	}
	$pageRefs = array();
	foreach ($sidecars as $sc) {
		$gaps = is_array($sc['gaps'] ?? null) ? $sc['gaps'] : array();
		$refs = array();
		foreach ($gaps as $g) {
			$g = (string) $g;
			if ($g === '') {
				continue;
			}
			$refs[] = (int) ($globalIndex[$g] ?? 0);
		}
		$pageRefs[] = array(
			'flags' => !empty($sc['trailing_implicit_period']) ? 1 : 0,
			'refs' => $refs,
		);
	}
	return array(
		'global_gaps' => $globalGaps,
		'global_index' => $globalIndex,
		'page_refs' => $pageRefs,
	);
}

/** @param list<string> $globalGaps */
function fractal_zip_enwik_stat_pred_encode_gap_pool_plain(array $globalGaps): string
{
	$buf = '';
	foreach ($globalGaps as $g) {
		$g = (string) $g;
		$buf .= fractal_zip_enwik_encode_varint_u32(strlen($g)) . $g;
	}
	return $buf;
}

/**
 * @param list<string> $globalGaps freq-sorted
 */
function fractal_zip_enwik_stat_pred_encode_gap_pool_frontcoded(array $globalGaps): string
{
	$buf = '';
	$prev = '';
	foreach ($globalGaps as $g) {
		$g = (string) $g;
		$pfx = 0;
		$max = min(strlen($prev), strlen($g));
		while ($pfx < $max && $prev[$pfx] === $g[$pfx]) {
			$pfx++;
		}
		$suffix = substr($g, $pfx);
		$buf .= fractal_zip_enwik_encode_varint_u32($pfx)
			. fractal_zip_enwik_encode_varint_u32(strlen($suffix)) . $suffix;
		$prev = $g;
	}
	return $buf;
}

/**
 * @return array{0: list<string>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_gap_pool_plain(string $blob, int $off, int $len, int $globalCount): array
{
	$globalGaps = array();
	for ($gi = 0; $gi < $globalCount; $gi++) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred page sidecars pool: global gap len missing');
		}
		$gLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($gLen < 0 || $off + $gLen > $len) {
			throw new RuntimeException('stat_pred page sidecars pool: truncated global gap');
		}
		$globalGaps[] = substr($blob, $off, $gLen);
		$off += $gLen;
	}
	return array($globalGaps, $off);
}

/**
 * @return array{0: list<string>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_gap_pool_frontcoded(string $blob, int $off, int $len, int $globalCount): array
{
	$globalGaps = array();
	$prev = '';
	for ($gi = 0; $gi < $globalCount; $gi++) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred page sidecars pool: front-coded pfx len missing');
		}
		$pfxLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($pfxLen < 0) {
			throw new RuntimeException('stat_pred page sidecars pool: invalid front-coded pfx len');
		}
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred page sidecars pool: front-coded suffix len missing');
		}
		$suffixLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($suffixLen < 0 || $off + $suffixLen > $len) {
			throw new RuntimeException('stat_pred page sidecars pool: truncated front-coded suffix');
		}
		$suffix = substr($blob, $off, $suffixLen);
		$off += $suffixLen;
		if ($pfxLen > strlen($prev)) {
			throw new RuntimeException('stat_pred page sidecars pool: front-coded pfx exceeds prev');
		}
		$g = substr($prev, 0, $pfxLen) . $suffix;
		$globalGaps[] = $g;
		$prev = $g;
	}
	return array($globalGaps, $off);
}

/** @param list<array{flags: int, refs: list<int>}> $pageRefs */
function fractal_zip_enwik_stat_pred_encode_page_gap_refs(array $pageRefs): string
{
	$buf = '';
	foreach ($pageRefs as $pr) {
		$buf .= chr((int) $pr['flags']);
		$refs = $pr['refs'];
		$buf .= fractal_zip_enwik_encode_varint_u32(count($refs));
		foreach ($refs as $rid) {
			$buf .= fractal_zip_enwik_encode_varint_u32((int) $rid);
		}
	}
	return $buf;
}

function fractal_zip_enwik_stat_pred_scheme_to_codec_id(string $scheme): int
{
	if ($scheme === 'stat_pred_bigram_base94_isp') {
		return 1;
	}
	if ($scheme === 'stat_pred_bigram_delta_isp') {
		return 2;
	}
	return 0;
}

function fractal_zip_enwik_stat_pred_codec_id_to_scheme(int $id): string
{
	if ($id === 1) {
		return 'stat_pred_bigram_base94_isp';
	}
	if ($id === 2) {
		return 'stat_pred_bigram_delta_isp';
	}
	return 'stat_pred_bigram_isp';
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_stat_pred_sidecar_apply_codec_meta(array $sidecar, string $scheme): array
{
	$sidecar['scheme'] = $scheme;
	if ($scheme === 'stat_pred_bigram_base94_isp') {
		$ba = fractal_zip_enwik_text_base94_alphabet();
		$sidecar['token_sep'] = "\n";
		$sidecar['alphabet'] = $ba['alphabet'];
	}
	return $sidecar;
}

/**
 * Compact per-page gap sidecars (isp implicit-space gaps) for inner fold.
 *
 * @param list<array<string, mixed>> $sidecars
 */
function fractal_zip_enwik_stat_pred_serialize_page_sidecars_binary(array $sidecars): string
{
	$built = fractal_zip_enwik_stat_pred_build_gap_pool_from_sidecars($sidecars);
	$globalGaps = $built['global_gaps'];
	$pageRefs = $built['page_refs'];
	$scheme = (string) ($sidecars[0]['scheme'] ?? 'stat_pred_bigram_isp');
	$plainPool = fractal_zip_enwik_stat_pred_encode_gap_pool_plain($globalGaps);
	$frontPool = fractal_zip_enwik_stat_pred_encode_gap_pool_frontcoded($globalGaps);
	$pageBlob = fractal_zip_enwik_stat_pred_encode_page_gap_refs($pageRefs);
	$header = fractal_zip_enwik_encode_varint_u32(count($sidecars))
		. fractal_zip_enwik_encode_varint_u32(count($globalGaps));
	$v3Tail = chr(fractal_zip_enwik_stat_pred_scheme_to_codec_id($scheme)) . $header . $plainPool . $pageBlob;
	$v4Tail = chr(fractal_zip_enwik_stat_pred_scheme_to_codec_id($scheme)) . $header . $frontPool . $pageBlob;
	$v3 = FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V3 . $v3Tail;
	$v4 = FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V4 . $v4Tail;
	return strlen($v4) <= strlen($v3) ? $v4 : $v3;
}

/**
 * @return list<array<string, mixed>>
 */
function fractal_zip_enwik_stat_pred_deserialize_page_sidecars_binary(string $blob, string $preprocessId = 'stat_pred_inner'): array
{
	$len = strlen($blob);
	$poolV4Len = strlen(FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V4);
	$poolV3Len = strlen(FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V3);
	$poolV2Len = strlen(FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL);
	$payloadScheme = 'stat_pred_bigram_isp';
	$off = 0;
	$frontCodedPool = false;
	if ($len >= $poolV4Len && substr($blob, 0, $poolV4Len) === FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V4) {
		$off = $poolV4Len;
		$frontCodedPool = true;
		if ($off >= $len) {
			throw new RuntimeException('stat_pred page sidecars pool: truncated scheme');
		}
		$payloadScheme = fractal_zip_enwik_stat_pred_codec_id_to_scheme(ord($blob[$off]));
		$off++;
	} elseif ($len >= $poolV3Len && substr($blob, 0, $poolV3Len) === FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V3) {
		$off = $poolV3Len;
		if ($off >= $len) {
			throw new RuntimeException('stat_pred page sidecars pool: truncated scheme');
		}
		$payloadScheme = fractal_zip_enwik_stat_pred_codec_id_to_scheme(ord($blob[$off]));
		$off++;
	} elseif ($len >= $poolV2Len && substr($blob, 0, $poolV2Len) === FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL) {
		$off = $poolV2Len;
	}
	if ($off > 0) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred page sidecars pool: page count missing');
		}
		$pageCount = (int) $dv[0];
		$off = (int) $dv[1];
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred page sidecars pool: global gap count missing');
		}
		$globalCount = (int) $dv[0];
		$off = (int) $dv[1];
		if ($frontCodedPool) {
			$decoded = fractal_zip_enwik_stat_pred_decode_gap_pool_frontcoded($blob, $off, $len, $globalCount);
		} else {
			$decoded = fractal_zip_enwik_stat_pred_decode_gap_pool_plain($blob, $off, $len, $globalCount);
		}
		$globalGaps = $decoded[0];
		$off = (int) $decoded[1];
		$out = array();
		for ($p = 0; $p < $pageCount; $p++) {
			if ($off >= $len) {
				throw new RuntimeException('stat_pred page sidecars pool: truncated flags');
			}
			$flags = ord($blob[$off]);
			$off++;
			$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
			if ($dv === null) {
				throw new RuntimeException('stat_pred page sidecars pool: ref count missing');
			}
			$refCount = (int) $dv[0];
			$off = (int) $dv[1];
			$gaps = array();
			for ($r = 0; $r < $refCount; $r++) {
				$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
				if ($dv === null) {
					throw new RuntimeException('stat_pred page sidecars pool: ref id missing');
				}
				$gaps[] = (string) ($globalGaps[(int) $dv[0]] ?? '');
				$off = (int) $dv[1];
			}
			$sc = fractal_zip_enwik_stat_pred_sidecar_apply_codec_meta(array(
				'preprocess' => $preprocessId,
				'frozen' => true,
				'gaps' => $gaps,
				'literal_oov' => true,
				'implicit_space' => true,
			), $payloadScheme);
			if (($flags & 1) !== 0) {
				$sc['trailing_implicit_period'] = true;
			}
			$out[] = $sc;
		}
		return $out;
	}
	$magicLen = strlen(FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC);
	if (strlen($blob) < $magicLen || substr($blob, 0, $magicLen) !== FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC) {
		throw new RuntimeException('stat_pred page sidecars: bad magic');
	}
	$off = $magicLen;
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
	if ($dv === null) {
		throw new RuntimeException('stat_pred page sidecars: page count missing');
	}
	$pageCount = (int) $dv[0];
	$off = (int) $dv[1];
	$len = strlen($blob);
	$out = array();
	for ($p = 0; $p < $pageCount; $p++) {
		if ($off >= $len) {
			throw new RuntimeException('stat_pred page sidecars: truncated flags');
		}
		$flags = ord($blob[$off]);
		$off++;
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred page sidecars: gap count missing');
		}
		$gapCount = (int) $dv[0];
		$off = (int) $dv[1];
		$gaps = array();
		for ($g = 0; $g < $gapCount; $g++) {
			$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
			if ($dv === null) {
				throw new RuntimeException('stat_pred page sidecars: gap len missing');
			}
			$gLen = (int) $dv[0];
			$off = (int) $dv[1];
			if ($gLen < 0 || $off + $gLen > $len) {
				throw new RuntimeException('stat_pred page sidecars: truncated gap');
			}
			$gaps[] = substr($blob, $off, $gLen);
			$off += $gLen;
		}
		$sc = array(
			'preprocess' => $preprocessId,
			'scheme' => 'stat_pred_bigram_isp',
			'frozen' => true,
			'gaps' => $gaps,
			'literal_oov' => true,
			'implicit_space' => true,
		);
		if (($flags & 1) !== 0) {
			$sc['trailing_implicit_period'] = true;
		}
		$out[] = $sc;
	}
	return $out;
}

/** @param list<string> $vocab */
function fractal_zip_enwik_stat_pred_encode_vocab_plain(array $vocab): string
{
	$buf = '';
	foreach ($vocab as $word) {
		$word = (string) $word;
		$buf .= fractal_zip_enwik_encode_varint_u32(strlen($word)) . $word;
	}
	return $buf;
}

/**
 * @param list<string> $vocab
 */
function fractal_zip_enwik_stat_pred_encode_vocab_frontcoded(array $vocab): string
{
	$buf = '';
	$prev = '';
	foreach ($vocab as $word) {
		$word = (string) $word;
		$pfx = 0;
		$max = min(strlen($prev), strlen($word));
		while ($pfx < $max && $prev[$pfx] === $word[$pfx]) {
			$pfx++;
		}
		$suffix = substr($word, $pfx);
		$buf .= fractal_zip_enwik_encode_varint_u32($pfx)
			. fractal_zip_enwik_encode_varint_u32(strlen($suffix)) . $suffix;
		$prev = $word;
	}
	return $buf;
}

/**
 * @return array{0: list<string>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_vocab_plain(string $blob, int $off, int $len, int $vocabCount): array
{
	$vocab = array();
	for ($i = 0; $i < $vocabCount; $i++) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred compact meta: vocab len missing');
		}
		$wLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($wLen < 0 || $off + $wLen > $len) {
			throw new RuntimeException('stat_pred compact meta: truncated vocab word');
		}
		$vocab[] = substr($blob, $off, $wLen);
		$off += $wLen;
	}
	return array($vocab, $off);
}

/**
 * @return array{0: list<string>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_vocab_frontcoded(string $blob, int $off, int $len, int $vocabCount): array
{
	$vocab = array();
	$prev = '';
	for ($i = 0; $i < $vocabCount; $i++) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred compact meta: vocab front pfx len missing');
		}
		$pfxLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($pfxLen < 0) {
			throw new RuntimeException('stat_pred compact meta: invalid vocab front pfx len');
		}
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred compact meta: vocab front suffix len missing');
		}
		$suffixLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($suffixLen < 0 || $off + $suffixLen > $len) {
			throw new RuntimeException('stat_pred compact meta: truncated vocab front suffix');
		}
		$suffix = substr($blob, $off, $suffixLen);
		$off += $suffixLen;
		if ($pfxLen > strlen($prev)) {
			throw new RuntimeException('stat_pred compact meta: vocab front pfx exceeds prev');
		}
		$word = substr($prev, 0, $pfxLen) . $suffix;
		$vocab[] = $word;
		$prev = $word;
	}
	return array($vocab, $off);
}

/** @param list<int> $ids */
function fractal_zip_enwik_stat_pred_encode_succ_ids_abs_u16(array $ids): string
{
	$buf = '';
	foreach ($ids as $sid) {
		$sid = (int) $sid;
		$buf .= chr($sid & 0xff) . chr(($sid >> 8) & 0xff);
	}
	return $buf;
}

/** @param list<int> $ids */
function fractal_zip_enwik_stat_pred_encode_succ_ids_delta_zz(array $ids): string
{
	$buf = '';
	$prev = 0;
	foreach ($ids as $sid) {
		$d = (int) $sid - $prev;
		$buf .= fractal_zip_enwik_encode_varint_zigzag_i32($d);
		$prev = (int) $sid;
	}
	return $buf;
}

/**
 * @return array{0: list<int>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_succ_ids_abs_u16(string $blob, int $off, int $len, int $succCount): array
{
	$ids = array();
	for ($s = 0; $s < $succCount; $s++) {
		if ($off + 2 > $len) {
			throw new RuntimeException('stat_pred compact meta: truncated uint16 succ id');
		}
		$ids[] = ord($blob[$off]) | (ord($blob[$off + 1]) << 8);
		$off += 2;
	}
	return array($ids, $off);
}

/**
 * @return array{0: list<int>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_succ_ids_delta_zz(string $blob, int $off, int $len, int $succCount): array
{
	$ids = array();
	$prev = 0;
	for ($s = 0; $s < $succCount; $s++) {
		$dv = fractal_zip_enwik_decode_varint_zigzag_i32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred compact meta: succ delta missing');
		}
		$prev += (int) $dv[0];
		$ids[] = $prev;
		$off = (int) $dv[1];
		if ($off > $len) {
			throw new RuntimeException('stat_pred compact meta: truncated succ delta');
		}
	}
	return array($ids, $off);
}

/**
 * @param list<int> $prevKeys
 * @param array<string, list<int>> $biIds
 */
function fractal_zip_enwik_stat_pred_encode_bigram_body_v2(array $prevKeys, array $biIds): string
{
	$buf = fractal_zip_enwik_encode_varint_u32(count($prevKeys));
	$prevBase = 0;
	foreach ($prevKeys as $pid) {
		$buf .= fractal_zip_enwik_encode_varint_u32($pid - $prevBase);
		$prevBase = $pid;
		$ids = is_array($biIds[(string) $pid] ?? null) ? $biIds[(string) $pid] : array();
		$buf .= fractal_zip_enwik_encode_varint_u32(count($ids));
		$buf .= fractal_zip_enwik_stat_pred_encode_succ_ids_abs_u16($ids);
	}
	return $buf;
}

/**
 * @param list<int> $prevKeys
 * @param array<string, list<int>> $biIds
 * @param list<string> $vocab
 * @param array<string, array<int, true>> $ranksUsed
 */
function fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse(
	array $prevKeys,
	array $biIds,
	array $vocab,
	array $ranksUsed,
	bool $implicitRank0 = false
): string {
	$buf = fractal_zip_enwik_encode_varint_u32(count($prevKeys));
	$prevBase = 0;
	foreach ($prevKeys as $pid) {
		$buf .= fractal_zip_enwik_encode_varint_u32($pid - $prevBase);
		$prevBase = $pid;
		$ids = is_array($biIds[(string) $pid] ?? null) ? $biIds[(string) $pid] : array();
		$prevWord = (string) ($vocab[(int) $pid] ?? '');
		$rankKeys = array();
		if ($prevWord !== '' && isset($ranksUsed[$prevWord]) && is_array($ranksUsed[$prevWord])) {
			$rankKeys = array_map('intval', array_keys($ranksUsed[$prevWord]));
		} elseif ($ids !== array()) {
			$rankKeys = range(0, count($ids) - 1);
		}
		sort($rankKeys, SORT_NUMERIC);
		if ($implicitRank0 && $rankKeys === array(0)) {
			$sid = (int) ($ids[0] ?? 0);
			$buf .= fractal_zip_enwik_encode_varint_u32(0);
			$buf .= chr($sid & 0xff) . chr(($sid >> 8) & 0xff);
			continue;
		}
		$buf .= fractal_zip_enwik_encode_varint_u32(count($rankKeys));
		foreach ($rankKeys as $r) {
			$r = (int) $r;
			$buf .= fractal_zip_enwik_encode_varint_u32($r);
			$sid = (int) ($ids[$r] ?? 0);
			$buf .= chr($sid & 0xff) . chr(($sid >> 8) & 0xff);
		}
	}
	return $buf;
}

/**
 * @return array{0: list<int>, 1: int}
 */
function fractal_zip_enwik_stat_pred_decode_bigram_row_sparse(
	string $blob,
	int $off,
	int $len,
	bool $implicitRank0 = false
): array {
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
	if ($dv === null) {
		throw new RuntimeException('stat_pred compact meta: sparse pair count missing');
	}
	$pairCount = (int) $dv[0];
	$off = (int) $dv[1];
	if ($implicitRank0 && $pairCount === 0) {
		if ($off + 2 > $len) {
			throw new RuntimeException('stat_pred compact meta: truncated implicit rank-0 succ id');
		}
		$sid = ord($blob[$off]) | (ord($blob[$off + 1]) << 8);
		$off += 2;
		return array(array((int) $sid), $off);
	}
	$map = array();
	$maxR = -1;
	for ($p = 0; $p < $pairCount; $p++) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred compact meta: sparse rank missing');
		}
		$rank = (int) $dv[0];
		$off = (int) $dv[1];
		if ($off + 2 > $len) {
			throw new RuntimeException('stat_pred compact meta: truncated sparse succ id');
		}
		$sid = ord($blob[$off]) | (ord($blob[$off + 1]) << 8);
		$off += 2;
		$map[$rank] = $sid;
		$maxR = max($maxR, $rank);
	}
	$ids = array();
	for ($r = 0; $r <= $maxR; $r++) {
		$ids[] = (int) ($map[$r] ?? 0);
	}
	return array($ids, $off);
}

/**
 * @param list<int> $prevKeys
 * @param array<string, list<int>> $biIds
 */
function fractal_zip_enwik_stat_pred_encode_bigram_body_v3(array $prevKeys, array $biIds, int $succMode): string
{
	$buf = fractal_zip_enwik_encode_varint_u32(count($prevKeys));
	$buf .= chr($succMode);
	$prevBase = 0;
	foreach ($prevKeys as $pid) {
		$buf .= fractal_zip_enwik_encode_varint_u32($pid - $prevBase);
		$prevBase = $pid;
		$ids = is_array($biIds[(string) $pid] ?? null) ? $biIds[(string) $pid] : array();
		$buf .= fractal_zip_enwik_encode_varint_u32(count($ids));
		if ($ids === array()) {
			continue;
		}
		if ($succMode === FRACTAL_ZIP_STAT_PRED_META_SUCC_DELTA_ZZ) {
			$buf .= fractal_zip_enwik_stat_pred_encode_succ_ids_delta_zz($ids);
		} else {
			$buf .= fractal_zip_enwik_stat_pred_encode_succ_ids_abs_u16($ids);
		}
	}
	return $buf;
}

/**
 * @param list<array<string, mixed>> $sidecars
 */
function fractal_zip_enwik_stat_pred_encode_sidecar_block(array $sidecars, bool $sidecarsBinary): string
{
	if ($sidecarsBinary) {
		return fractal_zip_enwik_stat_pred_serialize_page_sidecars_binary($sidecars);
	}
	$sideJson = json_encode($sidecars, JSON_UNESCAPED_UNICODE);
	if (!is_string($sideJson)) {
		throw new RuntimeException('stat_pred compact meta: sidecars json_encode failed');
	}
	return $sideJson;
}

/**
 * Binary stat_pred preprocess meta: vocab + sparse delta-coded bigram succ ids + sidecars tail.
 *
 * @param array{preprocess?: string, vocab?: list<string>, bigram_succ_ids?: array<string, list<int>>, bigram_ranks_used?: array<string, array<int, true>>, sidecars?: list<array<string, mixed>>, frozen?: bool, codec?: string, include_sidecars?: bool, sidecars_binary?: bool, bigram_uint16?: bool} $preMeta
 */
function fractal_zip_enwik_stat_pred_serialize_compact_meta(array $preMeta): string
{
	$vocab = is_array($preMeta['vocab'] ?? null) ? array_values($preMeta['vocab']) : array();
	$biIds = is_array($preMeta['bigram_succ_ids'] ?? null) ? $preMeta['bigram_succ_ids'] : array();
	$ranksUsed = is_array($preMeta['bigram_ranks_used'] ?? null) ? $preMeta['bigram_ranks_used'] : array();
	$sidecars = is_array($preMeta['sidecars'] ?? null) ? $preMeta['sidecars'] : array();
	$preprocessId = (string) ($preMeta['preprocess'] ?? 'stat_pred');
	$frozen = !empty($preMeta['frozen']);
	$includeSidecars = !array_key_exists('include_sidecars', $preMeta) || !empty($preMeta['include_sidecars']);
	$sidecarsBinary = !empty($preMeta['sidecars_binary']);
	$bigramUint16 = !empty($preMeta['bigram_uint16']);

	$prevKeys = array();
	foreach ($biIds as $pid => $ids) {
		$prevKeys[] = (int) $pid;
	}
	sort($prevKeys, SORT_NUMERIC);

	$metaPrefix = fractal_zip_enwik_encode_varint_u32(strlen($preprocessId)) . $preprocessId;
	$metaPrefix .= chr($frozen ? 1 : 0);

	$sideBlock = '';
	if ($includeSidecars) {
		$sideBlock = fractal_zip_enwik_stat_pred_encode_sidecar_block($sidecars, $sidecarsBinary);
	}
	$sideTail = fractal_zip_enwik_encode_varint_u32(strlen($sideBlock)) . $sideBlock;

	if ($bigramUint16) {
		$vocabPlain = fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab);
		$vocabFront = fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($vocab);
		$candidates = array();
		$v2 = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
		$v2 .= chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_UINT16) . $metaPrefix;
		$v2 .= fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain;
		$v2 .= fractal_zip_enwik_stat_pred_encode_bigram_body_v2($prevKeys, $biIds);
		$v2 .= $sideTail;
		$candidates[] = $v2;
		if ($ranksUsed !== array()) {
			$v4 = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
			$v4 .= chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE) . $metaPrefix;
			$v4 .= fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain;
			$v4 .= fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse($prevKeys, $biIds, $vocab, $ranksUsed);
			$v4 .= $sideTail;
			$candidates[] = $v4;
			$v6 = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
			$v6 .= chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE_IMPLICIT0) . $metaPrefix;
			$v6 .= fractal_zip_enwik_encode_varint_u32(count($vocab)) . $vocabPlain;
			$v6 .= fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse(
				$prevKeys,
				$biIds,
				$vocab,
				$ranksUsed,
				true
			);
			$v6 .= $sideTail;
			$candidates[] = $v6;
			$lexPack = fractal_zip_enwik_stat_pred_build_lex_vocab_perm($vocab);
			$lexVocab = $lexPack['lex_vocab'];
			$lexToOld = $lexPack['lex_to_old'];
			$remapped = fractal_zip_enwik_stat_pred_remap_bigram_ids($biIds, $lexPack['old_to_new']);
			$remappedKeys = array();
			foreach ($remapped as $pid => $ids) {
				$remappedKeys[] = (int) $pid;
			}
			sort($remappedKeys, SORT_NUMERIC);
			$v5 = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
			$v5 .= chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_LEX_SPARSE) . $metaPrefix;
			$v5 .= fractal_zip_enwik_encode_varint_u32(count($vocab));
			$v5 .= fractal_zip_enwik_stat_pred_encode_vocab_frontcoded($lexVocab);
			$v5 .= fractal_zip_enwik_stat_pred_encode_vocab_perm_uint16($lexToOld);
			$v5 .= fractal_zip_enwik_stat_pred_encode_bigram_body_v4_sparse(
				$remappedKeys,
				$remapped,
				$lexVocab,
				$ranksUsed
			);
			$v5 .= $sideTail;
			$candidates[] = $v5;
		}
		foreach (array(
			array(FRACTAL_ZIP_STAT_PRED_META_VOCAB_PLAIN, $vocabPlain),
			array(FRACTAL_ZIP_STAT_PRED_META_VOCAB_FRONT, $vocabFront),
		) as $vocabPack) {
			$vocabMode = (int) $vocabPack[0];
			$vocabBlob = (string) $vocabPack[1];
			foreach (array(
				FRACTAL_ZIP_STAT_PRED_META_SUCC_ABS_U16,
				FRACTAL_ZIP_STAT_PRED_META_SUCC_DELTA_ZZ,
			) as $succMode) {
				$v3 = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
				$v3 .= chr(FRACTAL_ZIP_STAT_PRED_META_VERSION_PACKED) . $metaPrefix;
				$v3 .= fractal_zip_enwik_encode_varint_u32(count($vocab));
				$v3 .= chr($vocabMode) . $vocabBlob;
				$v3 .= fractal_zip_enwik_stat_pred_encode_bigram_body_v3($prevKeys, $biIds, $succMode);
				$v3 .= $sideTail;
				$candidates[] = $v3;
			}
		}
		$sideTailLen = strlen($sideTail);
		$pickFn = static function (string $blob) use ($sideTailLen): string {
			$modelLen = strlen($blob) - $sideTailLen;
			if ($modelLen > 0 && $modelLen < strlen($blob)) {
				return substr($blob, 0, $modelLen);
			}
			return $blob;
		};
		$sealedPick = getenv('FRACTAL_ZIP_STAT_PRED_FZPM_SEALED_PICK');
		$useSealedPick = $sealedPick !== false && trim((string) $sealedPick) !== ''
			&& in_array(strtolower(trim((string) $sealedPick)), array('1', 'true', 'on', 'yes'), true);
		if ($useSealedPick) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
			usort($candidates, static function (string $a, string $b) use ($pickFn): int {
				$ga = gzencode($pickFn($a), 9);
				$gb = gzencode($pickFn($b), 9);
				if (is_string($ga) && is_string($gb)) {
					$cmp = strlen($ga) <=> strlen($gb);
					if ($cmp !== 0) {
						return $cmp;
					}
				}
				return strlen($pickFn($a)) <=> strlen($pickFn($b));
			});
			$top = array_slice($candidates, 0, min(2, count($candidates)));
			$sealedBest = null;
			$sealedBytes = PHP_INT_MAX;
			foreach ($top as $cand) {
				$sealed = fractal_zip_enwik_inner_fold_seal_trailer('stat_pred_inner', $cand);
				$wireBytes = (int) ($sealed['wire_bytes'] ?? PHP_INT_MAX);
				if ($wireBytes < $sealedBytes) {
					$sealedBytes = $wireBytes;
					$sealedBest = $cand;
				}
			}
			if (is_string($sealedBest)) {
				return $sealedBest;
			}
		}
		usort($candidates, static function (string $a, string $b) use ($sideTailLen, $pickFn): int {
			$ga = gzencode($pickFn($a), 9);
			$gb = gzencode($pickFn($b), 9);
			if (is_string($ga) && is_string($gb)) {
				$cmp = strlen($ga) <=> strlen($gb);
				if ($cmp !== 0) {
					return $cmp;
				}
			}
			return strlen($pickFn($a)) <=> strlen($pickFn($b));
		});
		return $candidates[0];
	}

	$buf = FRACTAL_ZIP_STAT_PRED_META_MAGIC;
	$buf .= chr(FRACTAL_ZIP_STAT_PRED_META_VERSION) . $metaPrefix;
	$buf .= fractal_zip_enwik_encode_varint_u32(count($vocab));
	$buf .= fractal_zip_enwik_stat_pred_encode_vocab_plain($vocab);
	$buf .= fractal_zip_enwik_encode_varint_u32(count($prevKeys));
	$prevBase = 0;
	foreach ($prevKeys as $pid) {
		$buf .= fractal_zip_enwik_encode_varint_u32($pid - $prevBase);
		$prevBase = $pid;
		$ids = is_array($biIds[(string) $pid] ?? null) ? $biIds[(string) $pid] : array();
		$buf .= fractal_zip_enwik_encode_varint_u32(count($ids));
		foreach ($ids as $sid) {
			$buf .= fractal_zip_enwik_encode_varint_u32((int) $sid);
		}
	}
	$buf .= $sideTail;
	return $buf;
}

/**
 * @return array{preprocess: string, vocab: list<string>, bigram_succ_ids: array<string, list<int>>, sidecars: list<array<string, mixed>>, frozen: bool, codec: string}
 */
function fractal_zip_enwik_stat_pred_deserialize_compact_meta(string $blob): array
{
	$off = 0;
	$len = strlen($blob);
	$magicLen = strlen(FRACTAL_ZIP_STAT_PRED_META_MAGIC);
	if ($len < $magicLen + 2 || substr($blob, 0, $magicLen) !== FRACTAL_ZIP_STAT_PRED_META_MAGIC) {
		throw new RuntimeException('stat_pred compact meta: bad magic');
	}
	$off = $magicLen;
	$version = ord($blob[$off]);
	$off++;
	if ($version !== FRACTAL_ZIP_STAT_PRED_META_VERSION
		&& $version !== FRACTAL_ZIP_STAT_PRED_META_VERSION_UINT16
		&& $version !== FRACTAL_ZIP_STAT_PRED_META_VERSION_PACKED
		&& $version !== FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE
		&& $version !== FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE_IMPLICIT0
		&& $version !== FRACTAL_ZIP_STAT_PRED_META_VERSION_LEX_SPARSE) {
		throw new RuntimeException('stat_pred compact meta: unsupported version ' . $version);
	}
	$packedMeta = $version === FRACTAL_ZIP_STAT_PRED_META_VERSION_PACKED;
	$sparseMeta = $version === FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE
		|| $version === FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE_IMPLICIT0
		|| $version === FRACTAL_ZIP_STAT_PRED_META_VERSION_LEX_SPARSE;
	$implicitRank0 = $version === FRACTAL_ZIP_STAT_PRED_META_VERSION_SPARSE_IMPLICIT0;
	$lexSparseMeta = $version === FRACTAL_ZIP_STAT_PRED_META_VERSION_LEX_SPARSE;
	$bigramUint16 = $version === FRACTAL_ZIP_STAT_PRED_META_VERSION_UINT16 || $packedMeta || $sparseMeta;
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
	if ($dv === null) {
		throw new RuntimeException('stat_pred compact meta: preprocess id missing');
	}
	$idLen = (int) $dv[0];
	$off = (int) $dv[1];
	if ($idLen < 0 || $off + $idLen > $len) {
		throw new RuntimeException('stat_pred compact meta: truncated preprocess id');
	}
	$preprocessId = substr($blob, $off, $idLen);
	$off += $idLen;
	if ($off >= $len) {
		throw new RuntimeException('stat_pred compact meta: truncated flags');
	}
	$frozen = ord($blob[$off]) !== 0;
	$off++;
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
	if ($dv === null) {
		throw new RuntimeException('stat_pred compact meta: vocab count missing');
	}
	$vocabCount = (int) $dv[0];
	$off = (int) $dv[1];
	if ($lexSparseMeta) {
		$decoded = fractal_zip_enwik_stat_pred_decode_vocab_frontcoded($blob, $off, $len, $vocabCount);
		$lexVocab = $decoded[0];
		$off = (int) $decoded[1];
		$decoded = fractal_zip_enwik_stat_pred_decode_vocab_perm_uint16($blob, $off, $len, $vocabCount);
		$lexToOld = $decoded[0];
		$off = (int) $decoded[1];
		$vocab = fractal_zip_enwik_stat_pred_restore_vocab_from_lex_perm($lexVocab, $lexToOld);
	} elseif ($packedMeta) {
		if ($off >= $len) {
			throw new RuntimeException('stat_pred compact meta: truncated vocab mode');
		}
		$vocabMode = ord($blob[$off]);
		$off++;
		if ($vocabMode === FRACTAL_ZIP_STAT_PRED_META_VOCAB_FRONT) {
			$decoded = fractal_zip_enwik_stat_pred_decode_vocab_frontcoded($blob, $off, $len, $vocabCount);
		} else {
			$decoded = fractal_zip_enwik_stat_pred_decode_vocab_plain($blob, $off, $len, $vocabCount);
		}
		$vocab = $decoded[0];
		$off = (int) $decoded[1];
	} else {
		$decoded = fractal_zip_enwik_stat_pred_decode_vocab_plain($blob, $off, $len, $vocabCount);
		$vocab = $decoded[0];
		$off = (int) $decoded[1];
	}
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
	if ($dv === null) {
		throw new RuntimeException('stat_pred compact meta: bigram count missing');
	}
	$entryCount = (int) $dv[0];
	$off = (int) $dv[1];
	$succMode = FRACTAL_ZIP_STAT_PRED_META_SUCC_ABS_U16;
	if ($packedMeta) {
		if ($off >= $len) {
			throw new RuntimeException('stat_pred compact meta: truncated succ mode');
		}
		$succMode = ord($blob[$off]);
		$off++;
	}
	$biIds = array();
	$prevBase = 0;
	for ($e = 0; $e < $entryCount; $e++) {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred compact meta: prev delta missing');
		}
		$prevBase += (int) $dv[0];
		$off = (int) $dv[1];
		if ($bigramUint16 && $sparseMeta) {
			$decoded = fractal_zip_enwik_stat_pred_decode_bigram_row_sparse($blob, $off, $len, $implicitRank0);
			$ids = $decoded[0];
			$off = (int) $decoded[1];
		} else {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('stat_pred compact meta: succ count missing');
		}
		$succCount = (int) $dv[0];
		$off = (int) $dv[1];
		if ($bigramUint16) {
			if ($succCount > 0) {
				if ($packedMeta && $succMode === FRACTAL_ZIP_STAT_PRED_META_SUCC_DELTA_ZZ) {
					$decoded = fractal_zip_enwik_stat_pred_decode_succ_ids_delta_zz($blob, $off, $len, $succCount);
				} else {
					$decoded = fractal_zip_enwik_stat_pred_decode_succ_ids_abs_u16($blob, $off, $len, $succCount);
				}
				$ids = $decoded[0];
				$off = (int) $decoded[1];
			} else {
				$ids = array();
			}
		} else {
			$ids = array();
			for ($s = 0; $s < $succCount; $s++) {
				$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
				if ($dv === null) {
					throw new RuntimeException('stat_pred compact meta: succ id missing');
				}
				$ids[] = (int) $dv[0];
				$off = (int) $dv[1];
			}
		}
		}
		$biIds[(string) $prevBase] = $ids;
	}
	if ($lexSparseMeta && $biIds !== array()) {
		$newToOld = array();
		foreach ($lexToOld as $newIdx => $oldIdx) {
			$newToOld[(int) $newIdx] = (int) $oldIdx;
		}
		$biIds = fractal_zip_enwik_stat_pred_remap_bigram_ids($biIds, $newToOld);
	}
	$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
	if ($dv === null) {
		throw new RuntimeException('stat_pred compact meta: sidecars len missing');
	}
	$sideLen = (int) $dv[0];
	$off = (int) $dv[1];
	if ($sideLen < 0 || $off + $sideLen > $len) {
		throw new RuntimeException('stat_pred compact meta: truncated sidecars');
	}
	$sidecars = array();
	if ($sideLen > 0) {
		$sideBlock = substr($blob, $off, $sideLen);
		if (str_starts_with($sideBlock, FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC)
			|| str_starts_with($sideBlock, FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL)
			|| str_starts_with($sideBlock, FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V3)
			|| str_starts_with($sideBlock, FRACTAL_ZIP_STAT_PRED_SIDECARS_MAGIC_POOL_V4)) {
			$sidecars = fractal_zip_enwik_stat_pred_deserialize_page_sidecars_binary($sideBlock, $preprocessId);
		} else {
			$decoded = json_decode($sideBlock, true);
			if (!is_array($decoded)) {
				throw new RuntimeException('stat_pred compact meta: invalid sidecars json');
			}
			$sidecars = $decoded;
		}
	}
	return array(
		'preprocess' => $preprocessId,
		'vocab' => $vocab,
		'bigram_succ_ids' => $biIds,
		'sidecars' => $sidecars,
		'frozen' => $frozen,
		'codec' => 'stat_pred_bigram_isp',
	);
}

/**
 * @param array{
 *   vocab: list<string>,
 *   vocab_index: array<string, int>,
 *   bigram_succ: array<string, list<string>>,
 *   bigram_rank: array<string, array<string, int>>
 * } $model
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_stat_pred_preprocess_with_model(string $text, array $model, array $opts, string $codec): array
{
	$index = $model['vocab_index'];
	$bigramRank = $model['bigram_rank'];
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$gapTable = fractal_zip_enwik_text_build_gap_table($segments, true);
	$gapStrings = $gapTable['gaps'];
	$gapIndex = $gapTable['index'];
	$scheme = fractal_zip_enwik_stat_pred_scheme_for_codec($codec);
	$useBase94 = ($codec === 'base94');
	$useDelta = ($codec === 'delta');
	$ba = $useBase94 ? fractal_zip_enwik_text_base94_alphabet() : null;

	$buf = '';
	$chunks = array();
	$prevWord = '';
	$prevWid = 0;
	$bigramPrevUsed = array();
	/** @var array<string, array<int, true>> prev => emitted rank => true */
	$bigramRanksUsed = array();
	$bigramHits = 0;
	$uniHits = 0;
	$literalWords = 0;
	$sidecarTrailing = false;
	$n = count($segments);
	for ($i = 0; $i < $n; $i++) {
		$seg = $segments[$i];
		if (($seg['type'] ?? '') === 'word') {
			$w = (string) ($seg['text'] ?? '');
			if (isset($index[$w])) {
				$wid = (int) $index[$w];
				$useBigram = false;
				if ($prevWord !== '' && isset($bigramRank[$prevWord][$w])) {
					$rank = (int) $bigramRank[$prevWord][$w];
					if (fractal_zip_enwik_stat_pred_bigram_rank_shorter_than_unigram($rank, $wid, $codec)) {
						if ($useBase94) {
							$chunk = 'b' . fractal_zip_enwik_text_encode_base_n(
								$rank,
								$ba['base'],
								$ba['alphabet']
							);
							$chunks[] = $chunk;
						} else {
							$buf .= chr(FRACTAL_ZIP_STAT_PRED_TAG_BIGRAM)
								. fractal_zip_enwik_encode_varint_u32($rank);
						}
						$bigramHits++;
						$bigramPrevUsed[$prevWord] = true;
						if (!isset($bigramRanksUsed[$prevWord])) {
							$bigramRanksUsed[$prevWord] = array();
						}
						$bigramRanksUsed[$prevWord][$rank] = true;
						$useBigram = true;
					}
				}
				if (!$useBigram) {
					if ($useBase94) {
						$chunks[] = 'w' . fractal_zip_enwik_text_encode_base_n(
							$wid,
							$ba['base'],
							$ba['alphabet']
						);
					} elseif ($useDelta) {
						$buf .= "\x01" . fractal_zip_enwik_encode_varint_zigzag_i32($wid - $prevWid);
					} else {
						$buf .= "\x01" . fractal_zip_enwik_encode_varint_u32($wid);
					}
					$prevWid = $wid;
					$uniHits++;
				} else {
					$prevWid = $wid;
				}
				$prevWord = $w;
				continue;
			}
			if ($useBase94) {
				$chunks[] = fractal_zip_enwik_text_literal_word_line($w);
			} else {
				$buf .= chr(FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD)
					. fractal_zip_enwik_encode_varint_u32(strlen($w))
					. $w;
			}
			$literalWords++;
			$prevWord = $w;
			$prevWid = 0;
			continue;
		}
		$wordBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'word');
		$wordAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'word');
		if (fractal_zip_enwik_text_gap_is_implicit($seg['text'], $wordBefore, $wordAfter)
			|| fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)) {
			if (fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)) {
				$sidecarTrailing = true;
				$prevWord = '';
				$prevWid = 0;
			}
			continue;
		}
		$gtxt = (string) ($seg['text'] ?? '');
		if (!isset($gapIndex[$gtxt])) {
			$gapIndex[$gtxt] = count($gapStrings);
			$gapStrings[] = $gtxt;
		}
		$gid = (int) $gapIndex[$gtxt];
		if ($useBase94) {
			$chunks[] = 'g' . fractal_zip_enwik_text_encode_base_n($gid, $ba['base'], $ba['alphabet']);
		} else {
			$buf .= "\x02" . fractal_zip_enwik_encode_varint_u32($gid);
		}
		$prevWord = '';
		$prevWid = 0;
	}
	if ($useBase94) {
		$buf = implode("\n", $chunks);
	}

	$sidecar = array(
		'preprocess' => 'stat_pred',
		'scheme' => $scheme,
		'frozen' => !empty($opts['frozen']),
		'gaps' => $gapStrings,
		'literal_oov' => true,
		'implicit_space' => true,
	);
	if ($useBase94) {
		$sidecar['token_sep'] = "\n";
		$sidecar['alphabet'] = $ba['alphabet'];
	}
	if (!empty($sidecarTrailing)) {
		$sidecar['trailing_implicit_period'] = true;
	}
	if (empty($opts['frozen'])) {
		$sidecar['vocab'] = $model['vocab'];
		$sidecar['bigram_succ'] = $model['bigram_succ'];
	}

	return array(
		'payload' => $buf,
		'sidecar' => $sidecar,
		'meta' => array(
			'bigram_hits' => $bigramHits,
			'unigram_hits' => $uniHits,
			'literal_words' => $literalWords,
			'vocab_size' => count($model['vocab']),
			'bigram_prev_used' => array_keys($bigramPrevUsed),
			'bigram_ranks_used' => $bigramRanksUsed,
			'payload_codec' => $codec,
		),
	);
}

/**
 * Encode with bigram rank shortcuts when prev word predicts next; else isp-style word/gap tags.
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_stat_pred_preprocess(string $text, array $opts = array()): array
{
	if (isset($opts['stat_model']) && is_array($opts['stat_model'])) {
		$model = $opts['stat_model'];
	} elseif (isset($opts['vocab'], $opts['bigram_succ'], $opts['bigram_rank']) && is_array($opts['vocab'])) {
		$model = array(
			'vocab' => $opts['vocab'],
			'vocab_index' => $opts['vocab_index'] ?? fractal_zip_enwik_text_vocab_index($opts['vocab']),
			'bigram_succ' => $opts['bigram_succ'],
			'bigram_rank' => $opts['bigram_rank'],
		);
	} else {
		$model = fractal_zip_enwik_stat_pred_mine_model($text);
	}
	$codec = isset($opts['payload_codec']) && is_string($opts['payload_codec'])
		? (string) $opts['payload_codec']
		: fractal_zip_enwik_stat_pred_payload_codec();
	return fractal_zip_text_stat_pred_preprocess_with_model($text, $model, $opts, $codec);
}

function fractal_zip_text_stat_pred_undo_base94(string $payload, array $sidecar): string
{
	$vocab = $sidecar['vocab'] ?? array();
	$bigramSucc = $sidecar['bigram_succ'] ?? array();
	if (!is_array($vocab) || $vocab === array()) {
		throw new RuntimeException('stat_pred undo: vocab missing');
	}
	if (!is_array($bigramSucc)) {
		$bigramSucc = array();
	}
	$gaps = is_array($sidecar['gaps'] ?? null) ? $sidecar['gaps'] : array();
	$ba = fractal_zip_enwik_text_base94_alphabet();
	$base = $ba['base'];
	$alphabet = (string) ($sidecar['alphabet'] ?? $ba['alphabet']);
	$sep = (string) ($sidecar['token_sep'] ?? "\n");
	$chunks = $sep !== '' && $payload !== '' ? explode($sep, $payload) : array($payload);

	$out = '';
	$lastWasWord = false;
	$lastWordEnd = 0;
	$prevWord = '';
	foreach ($chunks as $chunk) {
		if ($chunk === '') {
			continue;
		}
		$lit = fractal_zip_enwik_text_parse_literal_word_line($chunk);
		if ($lit !== null) {
			if ($lastWasWord) {
				$out .= ' ';
			}
			$out .= $lit;
			$prevWord = $lit;
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
			continue;
		}
		$kind = $chunk[0];
		$enc = substr($chunk, 1);
		$id = fractal_zip_enwik_text_decode_base_n($enc, $base, $alphabet);
		if ($kind === 'b') {
			$succList = is_array($bigramSucc[$prevWord] ?? null) ? $bigramSucc[$prevWord] : array();
			$w = (string) ($succList[$id] ?? '');
			if ($w === '') {
				throw new RuntimeException('stat_pred undo: bigram rank out of range');
			}
			if ($lastWasWord) {
				$out .= ' ';
			}
			$out .= $w;
			$prevWord = $w;
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
		} elseif ($kind === 'w') {
			if ($lastWasWord) {
				$out .= ' ';
			}
			$w = fractal_zip_enwik_text_vocab_lookup($vocab, $id, $sidecar);
			$out .= $w;
			$prevWord = $w;
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
		} elseif ($kind === 'g') {
			$out .= (string) ($gaps[$id] ?? '');
			$prevWord = '';
			$lastWasWord = false;
		} else {
			throw new RuntimeException('stat_pred undo: unknown base94 chunk');
		}
	}
	if (!empty($sidecar['trailing_implicit_period'])) {
		$out = fractal_zip_enwik_text_restore_trailing_implicit_period($out, $lastWordEnd);
	}
	return $out;
}

function fractal_zip_text_stat_pred_undo(string $payload, array $sidecar): string
{
	$scheme = (string) ($sidecar['scheme'] ?? 'stat_pred_bigram_isp');
	if ($scheme === 'stat_pred_bigram_base94_isp') {
		return fractal_zip_text_stat_pred_undo_base94($payload, $sidecar);
	}
	$useDelta = ($scheme === 'stat_pred_bigram_delta_isp');
	$vocab = $sidecar['vocab'] ?? array();
	$bigramSucc = $sidecar['bigram_succ'] ?? array();
	if (!is_array($vocab) || $vocab === array()) {
		throw new RuntimeException('stat_pred undo: vocab missing');
	}
	if (!is_array($bigramSucc)) {
		$bigramSucc = array();
	}
	$gaps = is_array($sidecar['gaps'] ?? null) ? $sidecar['gaps'] : array();
	$vocabIndex = fractal_zip_enwik_text_vocab_index($vocab);

	$out = '';
	$lastWasWord = false;
	$lastWordEnd = 0;
	$prevWord = '';
	$prevWid = 0;
	$off = 0;
	$n = strlen($payload);
	while ($off < $n) {
		$tag = ord($payload[$off]);
		$off++;
		if ($tag === FRACTAL_ZIP_STAT_PRED_TAG_BIGRAM) {
			$dv = fractal_zip_enwik_decode_varint_u32($payload, $off);
			if ($dv === null) {
				break;
			}
			$rank = (int) $dv[0];
			$off = (int) $dv[1];
			$succList = is_array($bigramSucc[$prevWord] ?? null) ? $bigramSucc[$prevWord] : array();
			$w = (string) ($succList[$rank] ?? '');
			if ($w === '') {
				throw new RuntimeException('stat_pred undo: bigram rank out of range');
			}
			if ($lastWasWord) {
				$out .= ' ';
			}
			$out .= $w;
			$prevWord = $w;
			$prevWid = (int) ($vocabIndex[$w] ?? $prevWid);
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
			continue;
		}
		if ($tag === 0x01) {
			if ($useDelta) {
				$dv = fractal_zip_enwik_decode_varint_zigzag_i32($payload, $off);
			} else {
				$dv = fractal_zip_enwik_decode_varint_u32($payload, $off);
			}
			if ($dv === null) {
				break;
			}
			$off = (int) $dv[1];
			if ($lastWasWord) {
				$out .= ' ';
			}
			$wid = $useDelta ? ($prevWid + (int) $dv[0]) : (int) $dv[0];
			$w = fractal_zip_enwik_text_vocab_lookup($vocab, $wid, $sidecar);
			$out .= $w;
			$prevWord = $w;
			$prevWid = $wid;
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
			continue;
		}
		if ($tag === FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD) {
			$dv = fractal_zip_enwik_decode_varint_u32($payload, $off);
			if ($dv === null) {
				break;
			}
			$len = (int) $dv[0];
			$off = (int) $dv[1];
			if ($len < 0 || $off + $len > $n) {
				throw new RuntimeException('stat_pred undo: truncated literal');
			}
			if ($lastWasWord) {
				$out .= ' ';
			}
			$w = substr($payload, $off, $len);
			$out .= $w;
			$off += $len;
			$prevWord = $w;
			$prevWid = (int) ($vocabIndex[$w] ?? 0);
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
			continue;
		}
		if ($tag === 0x02) {
			$dv = fractal_zip_enwik_decode_varint_u32($payload, $off);
			if ($dv === null) {
				break;
			}
			$off = (int) $dv[1];
			$out .= (string) ($gaps[(int) $dv[0]] ?? '');
			$prevWord = '';
			$prevWid = 0;
			$lastWasWord = false;
			continue;
		}
		throw new RuntimeException('stat_pred undo: unknown tag ' . $tag);
	}
	if (!empty($sidecar['trailing_implicit_period'])) {
		$out = fractal_zip_enwik_text_restore_trailing_implicit_period($out, $lastWordEnd);
	}
	return $out;
}

function fractal_zip_text_stat_isp_undo(string $payload, array $sidecar): string
{
	return fractal_zip_enwik_text_decode_words_stream('words_id_varint_isp', $payload, $sidecar);
}
