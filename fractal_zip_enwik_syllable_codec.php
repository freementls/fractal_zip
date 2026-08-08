<?php
declare(strict_types=1);

/**
 * Syllable- and consonant-skeleton representations for enwik preserve-text only.
 *
 * Words split into insoluble syllable units (dad-dy, up-per, cam-el). Optional
 * Hebrew-style vowel stripping with dictionary error-correction on restore.
 *
 * Markup must stay outside this layer — callers pass article text from
 * fractal_zip_enwik_extract_page_preserve_text(), not raw page XML.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

/** Syllable boundary inside a word (insoluble unit marker). */
const FRACTAL_ZIP_SYLLABLE_INNER_SEP = "\x1E";

/** Word boundary in consonant EC stream. */
const FRACTAL_ZIP_SYLLABLE_WORD_SEP = "\x1F";

function fractal_zip_enwik_syllable_is_vowel_at(string $word, int $i): bool
{
	$c = strtolower($word[$i] ?? '');
	if ($c === '' || !ctype_alpha($c)) {
		return false;
	}
	if (str_contains('aeiou', $c)) {
		return true;
	}
	if ($c !== 'y') {
		return false;
	}
	$len = strlen($word);
	if ($i === $len - 1) {
		return true;
	}
	if ($i === 0) {
		return false;
	}
	$prev = strtolower($word[$i - 1]);
	return !str_contains('aeiou', $prev) && $prev !== 'y';
}

/**
 * English heuristic syllabifier (onset/coda split tuned for enwik-style tokens).
 *
 * @return list<string>
 */
function fractal_zip_enwik_syllabify_word(string $word): array
{
	if ($word === '' || !preg_match('/^[A-Za-z]+$/', $word)) {
		return array($word);
	}
	$lower = strtolower($word);
	$len = strlen($lower);
	if ($len <= 2) {
		return array($word);
	}
	$nuclei = array();
	for ($i = 0; $i < $len; $i++) {
		if (fractal_zip_enwik_syllable_is_vowel_at($lower, $i)) {
			$nuclei[] = $i;
		}
	}
	if (count($nuclei) <= 1) {
		return array($word);
	}
	$splitAt = array();
	for ($k = 0; $k < count($nuclei) - 1; $k++) {
		$n1 = $nuclei[$k];
		$n2 = $nuclei[$k + 1];
		$between = $n2 - $n1 - 1;
		if ($between <= 0) {
			$splitAt[] = $n1 + 1;
		} elseif ($between === 1) {
			$splitAt[] = $n2;
		} elseif ($between === 2) {
			$splitAt[] = $n1 + 2;
		} else {
			$splitAt[] = max($n1 + 1, $n2 - 1);
		}
	}
	$out = array();
	$start = 0;
	foreach ($splitAt as $sp) {
		$sp = min(max($sp, $start + 1), $len);
		$piece = substr($word, $start, $sp - $start);
		if ($piece !== '') {
			$out[] = $piece;
		}
		$start = $sp;
	}
	$tail = substr($word, $start);
	if ($tail !== '') {
		$out[] = $tail;
	}
	return $out !== array() ? $out : array($word);
}

function fractal_zip_enwik_consonant_skeleton(string $word): string
{
	if ($word === '' || !preg_match('/^[A-Za-z]+$/', $word)) {
		return $word;
	}
	$lower = strtolower($word);
	$out = '';
	$len = strlen($lower);
	for ($i = 0; $i < $len; $i++) {
		if (fractal_zip_enwik_syllable_is_vowel_at($lower, $i)) {
			continue;
		}
		$out .= $lower[$i];
	}
	return $out !== '' ? $out : $lower;
}

/** True when bare skeleton tokens (no z_ prefix) are allowed for collision-free sk. */
function fractal_zip_enwik_consonant_hybrid_bare_sk_wanted(): bool
{
	$v = getenv('FRACTAL_ZIP_CONSONANT_SK_BARE');
	if ($v === false) {
		return true;
	}
	$v = strtolower(trim((string) $v));
	return $v !== '0' && $v !== 'false' && $v !== 'off';
}

/**
 * One word per skeleton string (cfabb-style): condo/canada cannot both use cnd.
 * Losers stay literal or receive a distinguished collision-free sk.
 */
function fractal_zip_enwik_consonant_hybrid_collision_free_wanted(): bool
{
	$v = getenv('FRACTAL_ZIP_CONSONANT_SK_COLLISION_FREE');
	if ($v === false || trim((string) $v) === '') {
		return true;
	}
	$v = strtolower(trim((string) $v));
	return !in_array($v, array('0', 'false', 'off', 'no'), true);
}

function fractal_zip_enwik_consonant_hybrid_phda9_gate_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_CONSONANT_PHDA9_GATE');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return in_array($v, array('1', 'true', 'on', 'yes'), true);
}

/** @param list<string> $words @return array<string, int> */
function fractal_zip_enwik_consonant_hybrid_prose_word_counts(array $words): array
{
	$counts = array();
	foreach ($words as $w) {
		$w = (string) $w;
		if ($w === '') {
			continue;
		}
		$counts[$w] = ($counts[$w] ?? 0) + 1;
	}
	return $counts;
}

/** @param list<string> $cands @param array<string, int> $wordCounts */
function fractal_zip_enwik_consonant_hybrid_pick_collision_free_winner(string $sk, array $cands, array $wordCounts): string
{
	$best = '';
	$bestScore = -1;
	foreach ($cands as $w) {
		$w = (string) $w;
		$score = (int) ($wordCounts[$w] ?? 0) * (strlen($w) - strlen($sk));
		if ($score > $bestScore) {
			$bestScore = $score;
			$best = $w;
		}
	}
	return $bestScore > 0 ? $best : '';
}

/**
 * Extend natural skeleton with trailing consonants until sk is corpus-unique and unused.
 *
 * @param array<string, true> $usedSk
 */
function fractal_zip_enwik_consonant_hybrid_distinguish_sk(
	string $word,
	string $baseSk,
	array $usedSk,
	string $corpus
): string {
	if ($baseSk === '' || !preg_match('/^[A-Za-z]+$/', $word)) {
		return '';
	}
	$lower = strtolower($word);
	$consonants = fractal_zip_enwik_consonant_skeleton($word);
	if ($consonants === '' || !str_starts_with($consonants, $baseSk)) {
		$consonants = $baseSk . substr($consonants, strlen($baseSk));
	}
	$cand = $baseSk;
	$start = strlen($baseSk);
	for ($i = $start; $i < strlen($consonants); $i++) {
		$cand .= $consonants[$i];
		if (strlen($cand) > 16) {
			break;
		}
		if (!preg_match('/^[a-z]+$/', $cand)) {
			continue;
		}
		if (isset($usedSk[$cand])) {
			continue;
		}
		if (fractal_zip_enwik_corpus_has_token($corpus, $cand)) {
			continue;
		}
		return $cand;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
	$pool = fractal_zip_cfabb_token_pool(256, $corpus);
	foreach ($pool as $tok) {
		$tok = strtolower((string) $tok);
		if (!preg_match('/^[a-z]{2,8}$/', $tok)) {
			continue;
		}
		if (isset($usedSk[$tok]) || fractal_zip_enwik_corpus_has_token($corpus, $tok)) {
			continue;
		}
		return $tok;
	}
	return '';
}

/**
 * @param array<string, list<string>> $index
 * @param array<string, int> $wordCounts
 * @return array{skeleton_unique: array<string, string>, skeleton_ambig: array<string, list<string>>, word_to_sk: array<string, string>}
 */
function fractal_zip_enwik_consonant_hybrid_build_collision_free_tables(
	array $index,
	array $wordCounts,
	string $corpus
): array {
	$unique = array();
	$ambig = array();
	$wordToSk = array();
	/** @var array<string, true> $usedSk */
	$usedSk = array();
	foreach ($index as $sk => $cands) {
		$sk = (string) $sk;
		$cands = array_values(array_map('strval', $cands));
		if ($cands === array()) {
			continue;
		}
		if (count($cands) === 1) {
			$w = $cands[0];
			if (!isset($usedSk[$sk]) && !fractal_zip_enwik_corpus_has_token($corpus, $sk)) {
				$unique[$sk] = $w;
				$wordToSk[$w] = $sk;
				$usedSk[$sk] = true;
			}
			continue;
		}
		$winner = fractal_zip_enwik_consonant_hybrid_pick_collision_free_winner($sk, $cands, $wordCounts);
		if ($winner !== '' && !isset($usedSk[$sk]) && !fractal_zip_enwik_corpus_has_token($corpus, $sk)) {
			$unique[$sk] = $winner;
			$wordToSk[$winner] = $sk;
			$usedSk[$sk] = true;
		}
		foreach ($cands as $w) {
			if ($w === $winner) {
				continue;
			}
			$alt = fractal_zip_enwik_consonant_hybrid_distinguish_sk($w, $sk, $usedSk, $corpus);
			if ($alt === '') {
				continue;
			}
			$save = (int) ($wordCounts[$w] ?? 0) * (strlen($w) - strlen($alt));
			if ($save <= fractal_zip_enwik_consonant_hybrid_sidecar_unique_cost($alt, $w)) {
				continue;
			}
			$unique[$alt] = $w;
			$wordToSk[$w] = $alt;
			$usedSk[$alt] = true;
		}
	}
	return array(
		'skeleton_unique' => $unique,
		'skeleton_ambig' => $ambig,
		'word_to_sk' => $wordToSk,
	);
}

/** @param array<string, string> $unique @return array<string, string> */
function fractal_zip_enwik_consonant_hybrid_build_word_to_sk(array $unique): array
{
	$wordToSk = array();
	foreach ($unique as $sk => $word) {
		$wordToSk[(string) $word] = (string) $sk;
	}
	return $wordToSk;
}

/**
 * Apply collision-free skeleton substitutions on preserve-text (prose words only).
 *
 * @param array<string, mixed> $model
 */
function fractal_zip_enwik_consonant_hybrid_apply_word_sk_wire(string $text, array $model): string
{
	$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
	$wordToSk = is_array($model['word_to_sk'] ?? null)
		? $model['word_to_sk']
		: fractal_zip_enwik_consonant_hybrid_build_word_to_sk($unique);
	if ($unique === array()) {
		return $text;
	}
	$encodeUnique = is_array($model['encode_unique'] ?? null) ? $model['encode_unique'] : null;
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$out = '';
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type !== 'word' || !isset($wordToSk[$s])) {
			$out .= $s;
			continue;
		}
		$sk = (string) $wordToSk[$s];
		if (!isset($unique[$sk]) || $unique[$sk] !== $s) {
			$out .= $s;
			continue;
		}
		if (is_array($encodeUnique) && empty($encodeUnique[$sk])) {
			$out .= $s;
			continue;
		}
		$out .= fractal_zip_enwik_consonant_hybrid_format_sk_token($sk, $model);
	}
	return $out;
}

/**
 * Greedy phda9 gate: keep sk assignments only when phda9(wire)+meta improves.
 *
 * @param list<string> $pageTexts
 * @param array<string, mixed> $model
 * @return array<string, mixed>
 */
function fractal_zip_enwik_consonant_hybrid_filter_phda9_greedy(
	array $pageTexts,
	array $model,
	array $opts = array()
): array {
	$gateOn = !empty($opts['force_phda9_gate']) || fractal_zip_enwik_consonant_hybrid_phda9_gate_enabled();
	if (!$gateOn) {
		return $model;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
	$plain = implode('', $pageTexts);
	if ($plain === '') {
		return $model;
	}
	$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
	if ($unique === array()) {
		return $model;
	}
	$rows = array();
	foreach ($unique as $sk => $word) {
		$rows[] = array(
			'phrase' => (string) $word,
			'token' => (string) $sk,
			'sk' => (string) $sk,
			'word' => (string) $word,
			'save' => strlen((string) $word) - strlen((string) $sk),
		);
	}
	usort($rows, static fn (array $a, array $b): int => ((int) ($b['save'] ?? 0) <=> (int) ($a['save'] ?? 0)));
	$poolLimit = (int) ($opts['phda9_greedy_pool'] ?? fractal_zip_cfabb_phda9_gate_pool_limit());
	if ($poolLimit > 0 && count($rows) > $poolLimit) {
		$rows = array_slice($rows, 0, $poolLimit);
	}
	$acceptedUnique = array();
	$bestScore = fractal_zip_enwik_consonant_hybrid_phda9_wire_score($plain, array_merge($model, array(
		'skeleton_unique' => array(),
		'skeleton_ambig' => array(),
		'word_to_sk' => array(),
	)));
	$patience = (int) ($opts['phda9_greedy_patience'] ?? fractal_zip_cfabb_phda9_gate_patience());
	$maxAccept = (int) ($opts['phda9_greedy_max'] ?? fractal_zip_cfabb_phda9_gate_max_accept());
	$misses = 0;
	foreach ($rows as $row) {
		if ($maxAccept > 0 && count($acceptedUnique) >= $maxAccept) {
			break;
		}
		$sk = (string) ($row['sk'] ?? '');
		$word = (string) ($row['word'] ?? '');
		if ($sk === '' || $word === '') {
			continue;
		}
		$trialUnique = $acceptedUnique;
		$trialUnique[$sk] = $word;
		$trial = array_merge($model, array(
			'skeleton_unique' => $trialUnique,
			'skeleton_ambig' => array(),
			'word_to_sk' => fractal_zip_enwik_consonant_hybrid_build_word_to_sk($trialUnique),
		));
		$score = fractal_zip_enwik_consonant_hybrid_phda9_wire_score($plain, $trial);
		if ($score < $bestScore) {
			$acceptedUnique = $trialUnique;
			$bestScore = $score;
			$misses = 0;
		} else {
			$misses++;
			if ($misses >= $patience) {
				break;
			}
		}
	}
	if ($acceptedUnique === array()) {
		return array_merge($model, array(
			'skeleton_unique' => array(),
			'skeleton_ambig' => array(),
			'context_unique' => array(),
			'word_to_sk' => array(),
		));
	}
	$filtered = array_merge($model, array(
		'skeleton_unique' => $acceptedUnique,
		'skeleton_ambig' => array(),
		'context_unique' => array(),
		'word_to_sk' => fractal_zip_enwik_consonant_hybrid_build_word_to_sk($acceptedUnique),
	));
	$skKeys = array_keys($acceptedUnique);
	sort($skKeys, SORT_STRING);
	$filtered['skeleton_vocab'] = $skKeys;
	$filtered['skeleton_vocab_index'] = fractal_zip_enwik_text_vocab_index($skKeys);
	return $filtered;
}

/** phda9(inner wire) + consonant hybrid fold meta bytes. */
function fractal_zip_enwik_consonant_hybrid_phda9_wire_score(string $plainWireText, array $model): int
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
	$wire = fractal_zip_enwik_consonant_hybrid_apply_word_sk_wire($plainWireText, $model);
	$meta = fractal_zip_enwik_consonant_hybrid_meta_wire_bytes(
		fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($model),
		12041,
		12041
	);
	return fractal_zip_cfabb_phda9_plain_bytes($wire) + $meta;
}

/** Token appears as a word segment or raw substring in corpus (must not be used bare). */
function fractal_zip_enwik_corpus_has_token(string $corpus, string $token): bool
{
	if ($token === '') {
		return true;
	}
	if (str_contains($corpus, $token)) {
		return true;
	}
	foreach (fractal_zip_enwik_text_segment_implicit_space($corpus) as $seg) {
		if (($seg['type'] ?? '') === 'word' && (string) ($seg['text'] ?? '') === $token) {
			return true;
		}
	}
	return false;
}

/**
 * Skeleton strings that never appear in $corpus — safe to emit without z_ marker.
 *
 * @param array<string, mixed> $model
 * @return array<string, true>
 */
function fractal_zip_enwik_consonant_hybrid_compute_skeleton_bare(string $corpus, array $model): array
{
	$out = array();
	if (!fractal_zip_enwik_consonant_hybrid_bare_sk_wanted()) {
		return $out;
	}
	$keys = is_array($model['skeleton_vocab'] ?? null) ? $model['skeleton_vocab'] : array();
	if ($keys === array()) {
		$keys = array_keys(array_merge(
			is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array(),
			is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array()
		));
	}
	foreach ($keys as $sk) {
		$sk = (string) $sk;
		if (!preg_match('/^[a-z]+$/', $sk)) {
			continue;
		}
		if (!fractal_zip_enwik_corpus_has_token($corpus, $sk)) {
			$out[$sk] = true;
		}
	}
	return $out;
}

/**
 * @param array<string, mixed> $model
 */
function fractal_zip_enwik_consonant_hybrid_format_sk_token(
	string $sk,
	array $model,
	bool $ambig = false,
	int $rank = 0
): string {
	if ($ambig) {
		return 'z_' . $sk . '_r' . $rank;
	}
	$bare = is_array($model['skeleton_bare'] ?? null) ? $model['skeleton_bare'] : array();
	if (!empty($bare[$sk])) {
		return $sk;
	}
	return 'z_' . $sk;
}

/**
 * @param array<string, mixed> $model
 */
function fractal_zip_enwik_consonant_hybrid_decode_sk_token(string $skTok, array $model): ?string
{
	if (preg_match('/^z_([a-z]+)_r(\d+)$/', $skTok, $m)) {
		return null;
	}
	if (preg_match('/^z_([a-z]+)$/', $skTok, $m)) {
		return (string) $m[1];
	}
	$bare = is_array($model['skeleton_bare'] ?? null) ? $model['skeleton_bare'] : array();
	if (preg_match('/^[a-z]+$/', $skTok) && !empty($bare[$skTok])) {
		return $skTok;
	}
	return null;
}

/**
 * @param list<array{type: string, text: string}> $segments
 * @return list<string>
 */
function fractal_zip_enwik_syllable_collect_words(array $segments): array
{
	$words = array();
	foreach ($segments as $seg) {
		if (($seg['type'] ?? '') === 'word') {
			$words[] = (string) $seg['text'];
		}
	}
	return $words;
}

/**
 * @param list<string> $words
 * @return array<string, list<string>>
 */
function fractal_zip_enwik_consonant_ec_index(array $words): array
{
	$idx = array();
	foreach ($words as $w) {
		if (!preg_match('/^[A-Za-z]+$/', $w)) {
			continue;
		}
		$sk = fractal_zip_enwik_consonant_skeleton($w);
		if (!isset($idx[$sk])) {
			$idx[$sk] = array();
		}
		if (!in_array($w, $idx[$sk], true)) {
			$idx[$sk][] = $w;
		}
	}
	return $idx;
}

/**
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_syllable_token_preprocess(string $text, array $opts = array()): array
{
	unset($opts);
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$out = '';
	$syllableCount = 0;
	$wordCount = 0;
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$out .= $s;
			continue;
		}
		$wordCount++;
		$syls = fractal_zip_enwik_syllabify_word($s);
		$syllableCount += count($syls);
		$out .= implode(FRACTAL_ZIP_SYLLABLE_INNER_SEP, $syls);
	}
	return array(
		'payload' => $out,
		'sidecar' => array('preprocess' => 'syllable_tokens'),
		'meta' => array('words' => $wordCount, 'syllables' => $syllableCount),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_syllable_token_undo(string $payload, array $sidecar): string
{
	unset($sidecar);
	if ($payload === '' || !str_contains($payload, FRACTAL_ZIP_SYLLABLE_INNER_SEP)) {
		return $payload;
	}
	return str_replace(FRACTAL_ZIP_SYLLABLE_INNER_SEP, '', $payload);
}

/**
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_consonant_ec_preprocess(string $text, array $opts = array()): array
{
	unset($opts);
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$words = fractal_zip_enwik_syllable_collect_words($segments);
	$index = fractal_zip_enwik_consonant_ec_index($words);
	$pick = array();
	foreach ($index as $sk => $cands) {
		if (count($cands) > 1) {
			sort($cands);
			foreach ($cands as $i => $w) {
				$pick[$w] = array('sk' => $sk, 'idx' => $i, 'cands' => $cands);
			}
		}
	}
	$out = '';
	$ambiguous = 0;
	$totalSkLen = 0;
	$wordCount = 0;
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$out .= $s;
			continue;
		}
		$wordCount++;
		if (!preg_match('/^[A-Za-z]+$/', $s)) {
			$out .= $s;
			continue;
		}
		$sk = fractal_zip_enwik_consonant_skeleton($s);
		$totalSkLen += strlen($sk);
		if (isset($pick[$s])) {
			$ambiguous++;
			$out .= $sk . ':' . (int) $pick[$s]['idx'];
		} else {
			$out .= $sk;
		}
	}
	return array(
		'payload' => $out,
		'sidecar' => array(
			'preprocess' => 'consonant_ec',
			'pick' => $pick,
			'skeleton_index' => $index,
		),
		'meta' => array(
			'words' => $wordCount,
			'ambiguous_words' => $ambiguous,
			'skeleton_bytes' => $totalSkLen,
			'unique_skeletons' => count($index),
		),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_ec_undo(string $payload, array $sidecar, string $originalText = ''): string
{
	unset($originalText);
	$index = is_array($sidecar['skeleton_index'] ?? null) ? $sidecar['skeleton_index'] : array();
	if ($payload === '') {
		return '';
	}
	$segments = fractal_zip_enwik_text_segment_implicit_space($payload);
	$out = '';
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$out .= $s;
			continue;
		}
		if (preg_match('/^([a-z]+):(\d+)$/', $s, $m)) {
			$sk = $m[1];
			$idx = (int) $m[2];
			$cands = $index[$sk] ?? array();
			$out .= $cands[$idx] ?? $sk;
			continue;
		}
		if (preg_match('/^[a-z]+$/', $s)) {
			$cands = $index[$s] ?? array();
			if (count($cands) === 1) {
				$out .= $cands[0];
				continue;
			}
		}
		$out .= $s;
	}
	return $out;
}

/**
 * Collect syllable tokens from word segments (no expanded segment array).
 *
 * @param list<array{type: string, text: string}> $segments
 * @return list<string>
 */
function fractal_zip_enwik_syllable_collect_tokens(array $segments): array
{
	$tokens = array();
	foreach ($segments as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		foreach (fractal_zip_enwik_syllabify_word((string) $seg['text']) as $sy) {
			$tokens[] = $sy;
		}
	}
	return $tokens;
}

/**
 * @param list<array{type: string, text: string}> $segments
 * @return list<array{type: string, text: string}>
 */
function fractal_zip_enwik_syllable_expand_segments(array $segments): array
{
	$out = array();
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$out[] = array('type' => 'gap', 'text' => $s);
			continue;
		}
		$syls = fractal_zip_enwik_syllabify_word($s);
		foreach ($syls as $i => $sy) {
			if ($i > 0) {
				$out[] = array('type' => 'gap', 'text' => FRACTAL_ZIP_SYLLABLE_INNER_SEP);
			}
			$out[] = array('type' => 'word', 'text' => $sy);
		}
	}
	return $out;
}

/**
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_syllables_id_varint_isp_preprocess(string $text, array $opts = array()): array
{
	unset($opts);
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$syllables = fractal_zip_enwik_syllable_collect_tokens($segments);
	$vocab = fractal_zip_enwik_text_build_vocab($syllables, 120000);
	$index = fractal_zip_enwik_text_vocab_index($vocab);
	$innerSep = FRACTAL_ZIP_SYLLABLE_INNER_SEP;

	$expanded = array();
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$expanded[] = array('type' => 'gap', 'text' => $s);
			continue;
		}
		foreach (fractal_zip_enwik_syllabify_word($s) as $i => $sy) {
			if ($i > 0) {
				$expanded[] = array('type' => 'gap', 'text' => $innerSep);
			}
			$expanded[] = array('type' => 'word', 'text' => $sy);
		}
	}
	$packed = fractal_zip_enwik_text_encode_words_stream(
		'words_id_varint_isp',
		$expanded,
		array('vocab' => $vocab, 'vocab_index' => $index)
	);
	$sidecar = $packed['sidecar'];
	$sidecar['vocab'] = $vocab;
	$sidecar['preprocess'] = 'syllables_id_varint_isp';
	$sidecar['syllable_inner_sep'] = $innerSep;
	return array(
		'payload' => (string) $packed['payload'],
		'sidecar' => $sidecar,
		'meta' => $packed['meta'] ?? array(),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_syllables_id_varint_isp_undo(string $payload, array $sidecar): string
{
	$innerSep = (string) ($sidecar['syllable_inner_sep'] ?? FRACTAL_ZIP_SYLLABLE_INNER_SEP);
	$expanded = fractal_zip_enwik_text_decode_words_stream('words_id_varint_isp', $payload, $sidecar);
	if ($innerSep === '') {
		return $expanded;
	}
	return str_replace($innerSep, '', $expanded);
}

/**
 * Consonant skeletons as vocab tokens (Hebrew-style), lossless via word stream + sidecar vocab.
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_consonant_id_varint_isp_preprocess(string $text, array $opts = array()): array
{
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$words = fractal_zip_enwik_syllable_collect_words($segments);
	$index = fractal_zip_enwik_consonant_ec_index($words);
	$pick = array();
	foreach ($index as $sk => $cands) {
		if (count($cands) > 1) {
			sort($cands);
			foreach ($cands as $i => $w) {
				$pick[$w] = array('sk' => $sk, 'idx' => $i);
			}
		}
	}
	$skSegments = array();
	$ambiguous = 0;
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$skSegments[] = array('type' => 'gap', 'text' => $s);
			continue;
		}
		if (!preg_match('/^[A-Za-z]+$/', $s)) {
			$skSegments[] = array('type' => 'word', 'text' => $s);
			continue;
		}
		$sk = fractal_zip_enwik_consonant_skeleton($s);
		if (isset($pick[$s])) {
			$ambiguous++;
			$skSegments[] = array('type' => 'word', 'text' => $sk . ':' . (int) $pick[$s]['idx']);
		} else {
			$skSegments[] = array('type' => 'word', 'text' => $sk);
		}
	}
	$packed = fractal_zip_enwik_text_encode_words_stream('words_id_varint_isp', $skSegments, $opts);
	$sidecar = $packed['sidecar'];
	$sidecar['preprocess'] = 'consonant_id_varint_isp';
	$sidecar['skeleton_index'] = $index;
	return array(
		'payload' => (string) $packed['payload'],
		'sidecar' => $sidecar,
		'meta' => array_merge(
			is_array($packed['meta'] ?? null) ? $packed['meta'] : array(),
			array('ambiguous_words' => $ambiguous, 'unique_skeletons' => count($index))
		),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_id_varint_isp_undo(string $payload, array $sidecar): string
{
	$index = is_array($sidecar['skeleton_index'] ?? null) ? $sidecar['skeleton_index'] : array();
	$skText = fractal_zip_enwik_text_decode_words_stream('words_id_varint_isp', $payload, $sidecar);
	$segments = fractal_zip_enwik_text_segment_implicit_space($skText);
	$out = '';
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$out .= $s;
			continue;
		}
		if (preg_match('/^([a-z]+):(\d+)$/', $s, $m)) {
			$sk = $m[1];
			$idx = (int) $m[2];
			$cands = $index[$sk] ?? array();
			$out .= $cands[$idx] ?? $sk;
			continue;
		}
		if (preg_match('/^[a-z]+$/', $s)) {
			$cands = $index[$s] ?? array();
			if (count($cands) === 1) {
				$out .= $cands[0];
				continue;
			}
		}
		$out .= $s;
	}
	return $out;
}

/** Ambiguous skeleton: tag + sk_id varint + rank varint. */
const FRACTAL_ZIP_CONSONANT_TAG_SK_AMBIG = 5;

/** Prose-only: lowercase alphabetic words eligible for vowel stripping. */
function fractal_zip_enwik_syllable_markup_depth_delta(string $gap): array
{
	return array(
		'template' => substr_count($gap, '{{') - substr_count($gap, '}}'),
		'link' => substr_count($gap, '[[') - substr_count($gap, ']]'),
	);
}

/**
 * @param array{template?: int, link?: int} $depth
 */
function fractal_zip_enwik_syllable_markup_depth_blocks_consonant(array $depth): bool
{
	return ((int) ($depth['template'] ?? 0)) > 0 || ((int) ($depth['link'] ?? 0)) > 0;
}

function fractal_zip_enwik_syllable_gap_is_wiki_markup(string $gapBefore, string $gapAfter): bool
{
	$ctx = $gapBefore . $gapAfter;
	if ($ctx === '') {
		return false;
	}
	if (preg_match('/\{\{|\}\}|\[\[|\]\]|<!--|<\/?(?:ref|sup|table|div)/i', $ctx)) {
		return true;
	}
	if (str_contains($gapBefore, '|') || str_contains($gapAfter, '|')) {
		return true;
	}
	if (preg_match('/#(?:REDIRECT|redirect)/i', $gapBefore . $gapAfter)) {
		return true;
	}
	return false;
}

function fractal_zip_enwik_syllable_word_consonant_eligible(
	string $word,
	string $gapBefore = '',
	string $gapAfter = '',
	?array $markupDepth = null
): bool {
	if ($word === '' || !preg_match('/^[a-z]+$/', $word)) {
		return false;
	}
	if (strlen($word) < 2) {
		return false;
	}
	if (is_array($markupDepth) && fractal_zip_enwik_syllable_markup_depth_blocks_consonant($markupDepth)) {
		return false;
	}
	return !fractal_zip_enwik_syllable_gap_is_wiki_markup($gapBefore, $gapAfter);
}

/**
 * @param list<array{type: string, text: string}> $segments
 * @return list<string>
 */
function fractal_zip_enwik_syllable_collect_prose_words(array $segments): array
{
	$words = array();
	$n = count($segments);
	$depth = array('template' => 0, 'link' => 0);
	for ($i = 0; $i < $n; $i++) {
		$type = (string) ($segments[$i]['type'] ?? 'gap');
		$s = (string) ($segments[$i]['text'] ?? '');
		if ($type === 'gap') {
			$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
			$depth['template'] += $d['template'];
			$depth['link'] += $d['link'];
			continue;
		}
		if ($type !== 'word') {
			continue;
		}
		$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i - 1]['text'] : '';
		$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i + 1]['text'] : '';
		if (fractal_zip_enwik_syllable_word_consonant_eligible($s, $gapBefore, $gapAfter, $depth)) {
			$words[] = $s;
		}
	}
	return $words;
}

/**
 * @param list<string> $words
 * @return array{
 *   skeleton_vocab: list<string>,
 *   skeleton_vocab_index: array<string, int>,
 *   skeleton_ambig: array<string, list<string>>,
 *   skeleton_unique: array<string, string>,
 *   context_unique: array<string, array<string, string>>
 * }
 */
function fractal_zip_enwik_consonant_hybrid_mine_model(string $mineText): array
{
	return fractal_zip_enwik_consonant_hybrid_mine_model_pages(array($mineText), $mineText);
}

/** Wire bytes for one compact sidecar row (FZ amortization tax). */
function fractal_zip_enwik_consonant_hybrid_sidecar_unique_cost(string $sk, string $word): int
{
	return 4 + strlen($sk) + strlen($word);
}

function fractal_zip_enwik_consonant_hybrid_sidecar_ambig_cost(string $sk, array $cands): int
{
	$c = 4 + strlen($sk);
	foreach ($cands as $w) {
		$c += 1 + strlen((string) $w);
	}
	return $c;
}

function fractal_zip_enwik_consonant_hybrid_sidecar_ctx_cost(string $prev, string $sk, string $word): int
{
	return 4 + strlen($prev) + strlen($sk) + strlen($word);
}

/**
 * FZ amortization: each skeleton token is shorter than the word it replaces, so every
 * replacement saves payload bytes. Ship a sidecar row only when
 *   Σ occurrences × (len(word) − len(token))  >  sidecar_row_wire_cost
 * Context disambiguation is tried before paying rank suffix bytes.
 *
 * @param list<string> $pageTexts
 * @param array<string, mixed> $baseModel
 * @return array<string, mixed>
 */
function fractal_zip_enwik_consonant_hybrid_plan_encoding(
	array $pageTexts,
	array $baseModel,
	string $codec = 'ascii'
): array {
	$unique = is_array($baseModel['skeleton_unique'] ?? null) ? $baseModel['skeleton_unique'] : array();
	$ambig = is_array($baseModel['skeleton_ambig'] ?? null) ? $baseModel['skeleton_ambig'] : array();
	$uniqueSave = array();
	$ctxSave = array();
	$rankSave = array();
	$ctxCounts = array();

	foreach ($pageTexts as $pageText) {
		if ($pageText === '') {
			continue;
		}
		$segments = fractal_zip_enwik_text_segment_implicit_space($pageText);
		$n = count($segments);
		$depth = array('template' => 0, 'link' => 0);
		$prevWord = '';
		for ($i = 0; $i < $n; $i++) {
			$type = (string) ($segments[$i]['type'] ?? 'gap');
			$s = (string) ($segments[$i]['text'] ?? '');
			if ($type === 'gap') {
				$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
				$depth['template'] += $d['template'];
				$depth['link'] += $d['link'];
				continue;
			}
			$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
				? (string) $segments[$i - 1]['text'] : '';
			$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
				? (string) $segments[$i + 1]['text'] : '';
			if (!fractal_zip_enwik_syllable_word_consonant_eligible($s, $gapBefore, $gapAfter, $depth)) {
				$prevWord = $s;
				continue;
			}
			$sk = fractal_zip_enwik_consonant_skeleton($s);
			$prevKey = strtolower($prevWord);
			$lit = strlen($s);
			$saveSk = $lit - fractal_zip_enwik_consonant_hybrid_rep_cost(
				$codec === 'varint' ? 'varint_sk' : 'ascii_sk',
				$s,
				$sk,
				0,
				$codec,
				$baseModel
			);
			if (isset($unique[$sk]) && $unique[$sk] === $s) {
				$uniqueSave[$sk] = ($uniqueSave[$sk] ?? 0) + $saveSk;
			} elseif (isset($ambig[$sk])) {
				$cands = $ambig[$sk];
				$rank = array_search($s, $cands, true);
				if ($rank === false) {
					$prevWord = $s;
					continue;
				}
				if (!isset($ctxCounts[$prevKey])) {
					$ctxCounts[$prevKey] = array();
				}
				if (!isset($ctxCounts[$prevKey][$sk])) {
					$ctxCounts[$prevKey][$sk] = array();
				}
				$ctxCounts[$prevKey][$sk][$s] = ($ctxCounts[$prevKey][$sk][$s] ?? 0) + 1;
				$saveRank = $lit - fractal_zip_enwik_consonant_hybrid_rep_cost(
				$codec === 'varint' ? 'varint_ambig' : 'ascii_ambig',
				$s,
				$sk,
				(int) $rank,
				$codec,
				$baseModel
			);
				if (!isset($rankSave[$sk])) {
					$rankSave[$sk] = 0;
				}
				$rankSave[$sk] += $saveRank;
			}
			$prevWord = $s;
		}
	}

	$outUnique = array();
	$encodeUnique = array();
	foreach ($unique as $sk => $word) {
		$save = (int) ($uniqueSave[$sk] ?? 0);
		$cost = fractal_zip_enwik_consonant_hybrid_sidecar_unique_cost($sk, $word);
		if ($save > $cost) {
			$outUnique[$sk] = $word;
			$encodeUnique[$sk] = true;
		}
	}

	$outCtx = array();
	$encodeCtx = array();
	foreach ($ctxCounts as $prev => $skMap) {
		foreach ($skMap as $sk => $wordCounts) {
			if (!isset($ambig[$sk]) || count($wordCounts) !== 1) {
				continue;
			}
			$word = (string) array_key_first($wordCounts);
			$n = (int) $wordCounts[$word];
			$save = $n * (strlen($word) - fractal_zip_enwik_consonant_hybrid_rep_cost(
				$codec === 'varint' ? 'varint_sk' : 'ascii_sk',
				$word,
				$sk,
				0,
				$codec,
				$baseModel
			));
			$cost = fractal_zip_enwik_consonant_hybrid_sidecar_ctx_cost($prev, $sk, $word);
			if ($save > $cost) {
				if (!isset($outCtx[$prev])) {
					$outCtx[$prev] = array();
				}
				$outCtx[$prev][$sk] = $word;
				if (!isset($encodeCtx[$prev])) {
					$encodeCtx[$prev] = array();
				}
				$encodeCtx[$prev][$sk] = true;
				$ctxSaveKey = $prev . "\0" . $sk;
				$ctxSave[$ctxSaveKey] = $save;
				if (isset($rankSave[$sk])) {
					$rankSave[$sk] -= $save;
				}
			}
		}
	}

	$outAmbig = array();
	$encodeRank = array();
	foreach ($ambig as $sk => $cands) {
		$save = (int) ($rankSave[$sk] ?? 0);
		$cost = fractal_zip_enwik_consonant_hybrid_sidecar_ambig_cost($sk, $cands);
		if ($save > $cost) {
			$outAmbig[$sk] = $cands;
			$encodeRank[$sk] = true;
		}
	}

	$skKeys = array_keys(array_merge($outUnique, $outAmbig));
	sort($skKeys, SORT_STRING);
	return array_merge($baseModel, array(
		'skeleton_vocab' => $skKeys,
		'skeleton_vocab_index' => fractal_zip_enwik_text_vocab_index($skKeys),
		'skeleton_unique' => $outUnique,
		'skeleton_ambig' => $outAmbig,
		'context_unique' => $outCtx,
		'encode_unique' => $encodeUnique,
		'encode_ctx' => $encodeCtx,
		'encode_rank' => $encodeRank,
	));
}

function fractal_zip_enwik_consonant_hybrid_default_model_json_path(int $pages = 0): string
{
	$repo = dirname(__FILE__);
	$suffix = $pages > 0 ? ('_' . $pages . 'p') : '_full';
	return $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_consonant_model' . $suffix . '.json';
}

/** @param array<string, mixed> $model */
function fractal_zip_enwik_consonant_hybrid_save_model_json(string $path, array $model): void
{
	$bare = is_array($model['skeleton_bare'] ?? null) ? $model['skeleton_bare'] : array();
	$bareList = array();
	foreach ($bare as $sk => $flag) {
		if ($flag) {
			$bareList[] = (string) $sk;
		}
	}
	sort($bareList, SORT_STRING);
	$payload = array(
		'generated' => gmdate('c'),
		'skeleton_unique' => is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array(),
		'skeleton_ambig' => is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array(),
		'context_unique' => is_array($model['context_unique'] ?? null) ? $model['context_unique'] : array(),
		'word_to_sk' => is_array($model['word_to_sk'] ?? null) ? $model['word_to_sk'] : array(),
		'skeleton_bare' => $bareList,
		'collision_free' => fractal_zip_enwik_consonant_hybrid_collision_free_wanted(),
		'phda9_gated' => fractal_zip_enwik_consonant_hybrid_phda9_gate_enabled(),
	);
	$json = json_encode($payload, JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		throw new RuntimeException('consonant model json encode failed');
	}
	$dir = dirname($path);
	if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) {
		throw new RuntimeException('consonant model json: cannot create ' . $dir);
	}
	if (file_put_contents($path, $json) === false) {
		throw new RuntimeException('consonant model json write failed: ' . $path);
	}
}

/** @return array<string, mixed>|null */
function fractal_zip_enwik_consonant_hybrid_load_model_json(?string $path = null): ?array
{
	if ($path === null || $path === '') {
		$path = getenv('FRACTAL_ZIP_CONSONANT_MODEL_JSON');
		$path = ($path !== false && trim((string) $path) !== '') ? trim((string) $path) : '';
	}
	if ($path === '' || !is_file($path)) {
		return null;
	}
	$meta = json_decode((string) file_get_contents($path), true);
	if (!is_array($meta)) {
		return null;
	}
	$unique = is_array($meta['skeleton_unique'] ?? null) ? $meta['skeleton_unique'] : array();
	$ambig = is_array($meta['skeleton_ambig'] ?? null) ? $meta['skeleton_ambig'] : array();
	$skKeys = array_keys(array_merge($unique, $ambig));
	sort($skKeys, SORT_STRING);
	$bare = array();
	foreach ((array) ($meta['skeleton_bare'] ?? array()) as $sk) {
		$bare[(string) $sk] = true;
	}
	$wordToSk = is_array($meta['word_to_sk'] ?? null) ? $meta['word_to_sk'] : array();
	if ($wordToSk === array() && $unique !== array()) {
		$wordToSk = fractal_zip_enwik_consonant_hybrid_build_word_to_sk($unique);
	}
	return fractal_zip_enwik_consonant_hybrid_model_with_vocab(array(
		'skeleton_vocab' => $skKeys,
		'skeleton_vocab_index' => fractal_zip_enwik_text_vocab_index($skKeys),
		'skeleton_unique' => $unique,
		'skeleton_ambig' => $ambig,
		'context_unique' => is_array($meta['context_unique'] ?? null) ? $meta['context_unique'] : array(),
		'word_to_sk' => $wordToSk,
		'skeleton_bare' => $bare,
		'encode_unique' => array_fill_keys(array_keys($unique), true),
	));
}

/**
 * @param list<string> $pageTexts
 */
function fractal_zip_enwik_consonant_hybrid_mine_model_pages(
	array $pageTexts,
	string $mineTextForIndex = ''
): array {
	$loaded = fractal_zip_enwik_consonant_hybrid_load_model_json();
	if ($loaded !== null) {
		return $loaded;
	}
	$indexText = $mineTextForIndex !== '' ? $mineTextForIndex : implode('', $pageTexts);
	$segments = fractal_zip_enwik_text_segment_implicit_space($indexText);
	$proseWords = fractal_zip_enwik_syllable_collect_prose_words($segments);
	$index = fractal_zip_enwik_consonant_ec_index($proseWords);
	$ambig = array();
	$unique = array();
	$wordToSk = array();
	if (fractal_zip_enwik_consonant_hybrid_collision_free_wanted()) {
		$wordCounts = fractal_zip_enwik_consonant_hybrid_prose_word_counts($proseWords);
		$cf = fractal_zip_enwik_consonant_hybrid_build_collision_free_tables($index, $wordCounts, $indexText);
		$unique = $cf['skeleton_unique'];
		$ambig = $cf['skeleton_ambig'];
		$wordToSk = $cf['word_to_sk'];
	} else {
		foreach ($index as $sk => $cands) {
			if (count($cands) === 1) {
				$unique[$sk] = $cands[0];
				continue;
			}
			sort($cands);
			$ambig[$sk] = $cands;
		}
		$wordToSk = fractal_zip_enwik_consonant_hybrid_build_word_to_sk($unique);
	}
	$skKeys = array_keys(array_merge($unique, $ambig));
	sort($skKeys, SORT_STRING);
	$base = array(
		'skeleton_vocab' => $skKeys,
		'skeleton_vocab_index' => fractal_zip_enwik_text_vocab_index($skKeys),
		'skeleton_ambig' => $ambig,
		'skeleton_unique' => $unique,
		'word_to_sk' => $wordToSk,
		'context_unique' => array(),
		'skeleton_bare' => fractal_zip_enwik_consonant_hybrid_compute_skeleton_bare($indexText, array(
			'skeleton_vocab' => $skKeys,
		)),
	);
	$codec = fractal_zip_enwik_consonant_hybrid_payload_codec(array());
	$planned = fractal_zip_enwik_consonant_hybrid_plan_encoding($pageTexts, $base, $codec);
	if (fractal_zip_enwik_consonant_hybrid_phda9_gate_enabled()) {
		$planned = fractal_zip_enwik_consonant_hybrid_filter_phda9_greedy($pageTexts, $planned);
	}
	$planned['word_to_sk'] = fractal_zip_enwik_consonant_hybrid_build_word_to_sk(
		is_array($planned['skeleton_unique'] ?? null) ? $planned['skeleton_unique'] : array()
	);
	$planned['skeleton_bare'] = fractal_zip_enwik_consonant_hybrid_compute_skeleton_bare($indexText, $planned);
	if (fractal_zip_enwik_consonant_sk_freq_vocab_enabled()) {
		$planned = fractal_zip_enwik_consonant_hybrid_reorder_vocab_by_frequency($pageTexts, $planned);
	}
	return $planned;
}

/** Payload bytes for one encoded token (negative => expansion vs literal). */
function fractal_zip_enwik_consonant_hybrid_rep_cost(
	string $mode,
	string $word,
	string $sk,
	int $rank = 0,
	string $codec = 'ascii',
	?array $model = null
): int {
	if ($mode === 'literal') {
		return strlen($word);
	}
	if ($codec === 'ascii' || $codec === 'plain') {
		if ($mode === 'ascii_sk') {
			return strlen(fractal_zip_enwik_consonant_hybrid_format_sk_token($sk, is_array($model) ? $model : array()));
		}
		if ($mode === 'ascii_ambig') {
			return strlen(fractal_zip_enwik_consonant_hybrid_format_sk_token($sk, is_array($model) ? $model : array(), true, $rank));
		}
	}
	if ($mode === 'varint_sk') {
		return 2;
	}
	if ($mode === 'varint_ambig') {
		return 3 + ($rank > 127 ? 1 : 0);
	}
	return strlen($word);
}

/** @param array<string, mixed> $model */
function fractal_zip_enwik_consonant_hybrid_encode_allowed(
	string $sk,
	string $word,
	string $prevWord,
	array $model,
	bool $wantRank = false
): bool {
	$hasPlan = isset($model['encode_unique']) || isset($model['encode_ctx']) || isset($model['encode_rank']);
	if (!$hasPlan) {
		if ($wantRank) {
			return isset($model['skeleton_ambig'][$sk]);
		}
		$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
		if (isset($unique[$sk]) && $unique[$sk] === $word) {
			return true;
		}
		return fractal_zip_enwik_consonant_hybrid_context_word($sk, $prevWord, $model) === $word;
	}
	if ($wantRank) {
		$rankAllow = is_array($model['encode_rank'] ?? null) ? $model['encode_rank'] : array();
		return !empty($rankAllow[$sk]);
	}
	$uniqueAllow = is_array($model['encode_unique'] ?? null) ? $model['encode_unique'] : array();
	if (!empty($uniqueAllow[$sk])) {
		$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
		return isset($unique[$sk]) && $unique[$sk] === $word;
	}
	$prevKey = strtolower($prevWord);
	$ctxAllow = is_array($model['encode_ctx'] ?? null) ? $model['encode_ctx'] : array();
	if (!empty($ctxAllow[$prevKey][$sk] ?? false)) {
		$ctxWord = fractal_zip_enwik_consonant_hybrid_context_word($sk, $prevWord, $model);
		return $ctxWord === $word;
	}
	return false;
}

/**
 * @param array<string, mixed> $model
 */
function fractal_zip_enwik_consonant_hybrid_context_word(
	string $sk,
	string $prevWord,
	array $model
): ?string {
	$prevKey = strtolower($prevWord);
	$ctx = is_array($model['context_unique'] ?? null) ? $model['context_unique'] : array();
	if ($prevKey !== '' && isset($ctx[$prevKey][$sk])) {
		return (string) $ctx[$prevKey][$sk];
	}
	if (isset($ctx[''][$sk])) {
		return (string) $ctx[''][$sk];
	}
	return null;
}

/**
 * @param array<string, mixed> $model
 */
function fractal_zip_enwik_consonant_hybrid_decode_word(
	string $sk,
	string $prevWord,
	array $model,
	int $rank = 0,
	bool $hasRank = false
): ?string {
	if ($hasRank) {
		$ambig = is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array();
		$cands = $ambig[$sk] ?? array();
		return isset($cands[$rank]) ? (string) $cands[$rank] : null;
	}
	$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
	if (isset($unique[$sk])) {
		return (string) $unique[$sk];
	}
	$ctxWord = fractal_zip_enwik_consonant_hybrid_context_word($sk, $prevWord, $model);
	if ($ctxWord !== null) {
		return $ctxWord;
	}
	return null;
}

/**
 * @param array<string, mixed> $model
 */
function fractal_zip_enwik_consonant_hybrid_restore_word(string $sk, int $rank, array $model): ?string
{
	$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
	if (isset($unique[$sk])) {
		return (string) $unique[$sk];
	}
	$ambig = is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array();
	$cands = $ambig[$sk] ?? array();
	return isset($cands[$rank]) ? (string) $cands[$rank] : null;
}

function fractal_zip_enwik_consonant_hybrid_payload_codec(array $opts = array()): string
{
	$codec = (string) ($opts['payload_codec'] ?? '');
	if ($codec === '') {
		$env = getenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC');
		$codec = ($env !== false && trim((string) $env) !== '') ? trim((string) $env) : 'varint';
	}
	return strtolower($codec);
}

/**
 * @param array<string, mixed> $model
 * @return array{type: string, text?: string, sk?: string, rank?: int}
 */
function fractal_zip_enwik_consonant_hybrid_encode_token(
	string $word,
	array $model,
	string $gapBefore = '',
	string $gapAfter = '',
	?array $markupDepth = null,
	string $prevWord = '',
	string $codec = 'ascii'
): array {
	if (!fractal_zip_enwik_syllable_word_consonant_eligible($word, $gapBefore, $gapAfter, $markupDepth)) {
		return array('type' => 'literal', 'text' => $word);
	}
	$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
	$wordToSk = is_array($model['word_to_sk'] ?? null)
		? $model['word_to_sk']
		: fractal_zip_enwik_consonant_hybrid_build_word_to_sk($unique);
	if (isset($wordToSk[$word])) {
		$assignedSk = (string) $wordToSk[$word];
		if (isset($unique[$assignedSk]) && $unique[$assignedSk] === $word
			&& fractal_zip_enwik_consonant_hybrid_encode_allowed($assignedSk, $word, $prevWord, $model, false)) {
			return array('type' => 'sk', 'sk' => $assignedSk);
		}
	}
	$sk = fractal_zip_enwik_consonant_skeleton($word);

	if (isset($unique[$sk]) && $unique[$sk] === $word
		&& fractal_zip_enwik_consonant_hybrid_encode_allowed($sk, $word, $prevWord, $model, false)) {
		return array('type' => 'sk', 'sk' => $sk);
	}

	$ambig = is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array();
	if (isset($ambig[$sk])) {
		$cands = $ambig[$sk];
		$rank = array_search($word, $cands, true);
		if ($rank === false) {
			return array('type' => 'literal', 'text' => $word);
		}
		if (fractal_zip_enwik_consonant_hybrid_encode_allowed($sk, $word, $prevWord, $model, false)) {
			return array('type' => 'sk', 'sk' => $sk);
		}
		if (fractal_zip_enwik_consonant_hybrid_encode_allowed($sk, $word, $prevWord, $model, true)) {
			return array('type' => 'ambig', 'sk' => $sk, 'rank' => (int) $rank);
		}
	}
	return array('type' => 'literal', 'text' => $word);
}

/**
 * Lossless consonant hybrid: prose-only skeleton ISP + amortized disambiguation table.
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_consonant_hybrid_preprocess(string $text, array $opts = array()): array
{
	$model = is_array($opts['consonant_model'] ?? null) ? $opts['consonant_model'] : null;
	if ($model === null) {
		$model = fractal_zip_enwik_consonant_hybrid_mine_model($text);
	}
	$codec = fractal_zip_enwik_consonant_hybrid_payload_codec($opts);
	if ($codec === 'ascii' || $codec === 'plain') {
		return fractal_zip_enwik_consonant_hybrid_preprocess_ascii($text, $model, $opts);
	}
	$skIndex = is_array($model['skeleton_vocab_index'] ?? null)
		? $model['skeleton_vocab_index']
		: fractal_zip_enwik_text_vocab_index((array) ($model['skeleton_vocab'] ?? array()));
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$gapTable = fractal_zip_enwik_text_build_gap_table($segments, true);
	$gapStrings = $gapTable['gaps'];
	$gapIndex = $gapTable['index'];

	$buf = '';
	$encodedSk = 0;
	$encodedAmbig = 0;
	$encodedCtx = 0;
	$literalWords = 0;
	$implicitOmitted = 0;
	$n = count($segments);
	$depth = array('template' => 0, 'link' => 0);
	$prevWord = '';
	for ($i = 0; $i < $n; $i++) {
		$seg = $segments[$i];
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
			$depth['template'] += $d['template'];
			$depth['link'] += $d['link'];
			$wordBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'word');
			$wordAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'word');
			if (fractal_zip_enwik_text_gap_is_implicit($s, $wordBefore, $wordAfter)
				|| fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)) {
				$implicitOmitted++;
				continue;
			}
			if (!isset($gapIndex[$s])) {
				$gapIndex[$s] = count($gapStrings);
				$gapStrings[] = $s;
			}
			$buf .= "\x02" . fractal_zip_enwik_encode_varint_u32((int) $gapIndex[$s]);
			continue;
		}
		$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i - 1]['text'] : '';
		$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i + 1]['text'] : '';
		$tok = fractal_zip_enwik_consonant_hybrid_encode_token(
			$s, $model, $gapBefore, $gapAfter, $depth, $prevWord, 'varint'
		);
		if ($tok['type'] === 'literal') {
			$literalWords++;
			$buf .= chr(FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD)
				. fractal_zip_enwik_encode_varint_u32(strlen($s))
				. $s;
			$prevWord = $s;
			continue;
		}
		if ($tok['type'] === 'ambig') {
			$sk = (string) $tok['sk'];
			$rank = (int) $tok['rank'];
			if (!isset($skIndex[$sk])) {
				$literalWords++;
				$buf .= chr(FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD)
					. fractal_zip_enwik_encode_varint_u32(strlen($s))
					. $s;
				$prevWord = $s;
				continue;
			}
			$encodedAmbig++;
			$buf .= chr(FRACTAL_ZIP_CONSONANT_TAG_SK_AMBIG)
				. fractal_zip_enwik_encode_varint_u32((int) $skIndex[$sk])
				. fractal_zip_enwik_encode_varint_u32($rank);
			$prevWord = $s;
			continue;
		}
		$sk = (string) $tok['sk'];
		if (!isset($skIndex[$sk])) {
			$literalWords++;
			$buf .= chr(FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD)
				. fractal_zip_enwik_encode_varint_u32(strlen($s))
				. $s;
			$prevWord = $s;
			continue;
		}
		$encodedSk++;
		if (isset($model['skeleton_ambig'][$sk])
			&& fractal_zip_enwik_consonant_hybrid_context_word($sk, $prevWord, $model) === $s) {
			$encodedCtx++;
		}
		$buf .= "\x01" . fractal_zip_enwik_encode_varint_u32((int) $skIndex[$sk]);
		$prevWord = $s;
	}
	$sidecar = array(
		'preprocess' => 'consonant_hybrid',
		'scheme' => 'consonant_hybrid_isp',
		'payload_codec' => 'varint',
		'implicit_space' => true,
		'implicit_period' => true,
		'gaps' => $gapStrings,
		'frozen' => !empty($opts['frozen']),
	);
	if (empty($opts['frozen'])) {
		$sidecar['skeleton_vocab'] = $model['skeleton_vocab'] ?? array();
		$sidecar['skeleton_ambig'] = $model['skeleton_ambig'] ?? array();
		$sidecar['skeleton_unique'] = $model['skeleton_unique'] ?? array();
		$sidecar['context_unique'] = $model['context_unique'] ?? array();
	}
	return array(
		'payload' => $buf,
		'sidecar' => $sidecar,
		'meta' => array(
			'encoded_skeletons' => $encodedSk,
			'encoded_ambiguous' => $encodedAmbig,
			'encoded_context_resolved' => $encodedCtx,
			'literal_words' => $literalWords,
			'implicit_spaces_omitted' => $implicitOmitted,
			'skeleton_vocab_size' => count($model['skeleton_vocab'] ?? array()),
			'unique_skeletons' => count($model['skeleton_unique'] ?? array()),
			'ambig_skeletons' => count($model['skeleton_ambig'] ?? array()),
		),
	);
}

/**
 * phda9-safe ASCII skeleton stream (preserve gaps, prose-only, lossless).
 *
 * @param array<string, mixed> $model
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_consonant_hybrid_preprocess_ascii(string $text, array $model, array $opts = array()): array
{
	$frozen = !empty($opts['frozen']);
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$out = '';
	$encodedSk = 0;
	$encodedAmbig = 0;
	$encodedCtx = 0;
	$literalWords = 0;
	$n = count($segments);
	$depth = array('template' => 0, 'link' => 0);
	$prevWord = '';
	for ($i = 0; $i < $n; $i++) {
		$seg = $segments[$i];
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
			$depth['template'] += $d['template'];
			$depth['link'] += $d['link'];
			$out .= $s;
			continue;
		}
		$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i - 1]['text'] : '';
		$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i + 1]['text'] : '';
		$tok = fractal_zip_enwik_consonant_hybrid_encode_token(
			$s, $model, $gapBefore, $gapAfter, $depth, $prevWord, 'ascii'
		);
		if ($tok['type'] === 'literal') {
			$literalWords++;
			$out .= (string) $tok['text'];
			$prevWord = $s;
			continue;
		}
		if ($tok['type'] === 'ambig') {
			$encodedAmbig++;
			$out .= fractal_zip_enwik_consonant_hybrid_format_sk_token(
				(string) $tok['sk'],
				$model,
				true,
				(int) $tok['rank']
			);
			$prevWord = $s;
			continue;
		}
		$encodedSk++;
		if (isset($model['skeleton_ambig'][(string) $tok['sk']])
			&& fractal_zip_enwik_consonant_hybrid_context_word((string) $tok['sk'], $prevWord, $model) === $s) {
			$encodedCtx++;
		}
		$out .= fractal_zip_enwik_consonant_hybrid_format_sk_token((string) $tok['sk'], $model);
		$prevWord = $s;
	}
	$sidecar = array(
		'preprocess' => 'consonant_hybrid',
		'scheme' => 'consonant_hybrid_ascii',
		'payload_codec' => 'ascii',
		'frozen' => $frozen,
	);
	if (!$frozen) {
		$sidecar['skeleton_vocab'] = $model['skeleton_vocab'] ?? array();
		$sidecar['skeleton_ambig'] = $model['skeleton_ambig'] ?? array();
		$sidecar['skeleton_unique'] = $model['skeleton_unique'] ?? array();
		$sidecar['context_unique'] = $model['context_unique'] ?? array();
		$sidecar['skeleton_bare'] = array_keys(is_array($model['skeleton_bare'] ?? null) ? $model['skeleton_bare'] : array());
	}
	return array(
		'payload' => $out,
		'sidecar' => $sidecar,
		'meta' => array(
			'encoded_skeletons' => $encodedSk,
			'encoded_ambiguous' => $encodedAmbig,
			'encoded_context_resolved' => $encodedCtx,
			'literal_words' => $literalWords,
			'skeleton_vocab_size' => count($model['skeleton_vocab'] ?? array()),
			'unique_skeletons' => count($model['skeleton_unique'] ?? array()),
			'ambig_skeletons' => count($model['skeleton_ambig'] ?? array()),
			'payload_codec' => 'ascii',
		),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_hybrid_undo(string $payload, array $sidecar): string
{
	$codec = strtolower((string) ($sidecar['payload_codec'] ?? 'varint'));
	if ($codec === 'ascii' || $codec === 'plain') {
		return fractal_zip_enwik_consonant_hybrid_undo_ascii($payload, $sidecar);
	}
	$model = fractal_zip_enwik_consonant_hybrid_model_with_vocab(array(
		'skeleton_vocab' => $sidecar['skeleton_vocab'] ?? array(),
		'skeleton_ambig' => $sidecar['skeleton_ambig'] ?? array(),
		'skeleton_unique' => $sidecar['skeleton_unique'] ?? array(),
		'context_unique' => $sidecar['context_unique'] ?? array(),
	));
	$vocab = is_array($model['skeleton_vocab']) ? $model['skeleton_vocab'] : array();
	$gaps = is_array($sidecar['gaps'] ?? null) ? $sidecar['gaps'] : array();
	$implicitSpace = !empty($sidecar['implicit_space']);
	$out = '';
	$lastWasWord = false;
	$prevWord = '';
	$pos = 0;
	$plen = strlen($payload);
	while ($pos < $plen) {
		$tag = ord($payload[$pos]);
		$pos++;
		$pair = fractal_zip_enwik_decode_varint_u32($payload, $pos);
		if ($pair === null) {
			break;
		}
		$id = (int) $pair[0];
		$pos = (int) $pair[1];
		if ($tag === 1) {
			if ($lastWasWord && $implicitSpace) {
				$out .= ' ';
			}
			$sk = (string) ($vocab[$id] ?? '');
			$word = fractal_zip_enwik_consonant_hybrid_decode_word($sk, $prevWord, $model) ?? $sk;
			$out .= $word;
			$prevWord = $word;
			$lastWasWord = true;
			continue;
		}
		if ($tag === FRACTAL_ZIP_CONSONANT_TAG_SK_AMBIG) {
			$pair2 = fractal_zip_enwik_decode_varint_u32($payload, $pos);
			if ($pair2 === null) {
				break;
			}
			$rank = (int) $pair2[0];
			$pos = (int) $pair2[1];
			if ($lastWasWord && $implicitSpace) {
				$out .= ' ';
			}
			$sk = (string) ($vocab[$id] ?? '');
			$word = fractal_zip_enwik_consonant_hybrid_decode_word($sk, $prevWord, $model, $rank, true) ?? $sk;
			$out .= $word;
			$prevWord = $word;
			$lastWasWord = true;
			continue;
		}
		if ($tag === FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD) {
			$litLen = $id;
			if ($litLen < 0 || $pos + $litLen > $plen) {
				break;
			}
			if ($lastWasWord && $implicitSpace) {
				$out .= ' ';
			}
			$lit = substr($payload, $pos, $litLen);
			$out .= $lit;
			$pos += $litLen;
			$prevWord = $lit;
			$lastWasWord = true;
			continue;
		}
		if ($tag === 2) {
			$gap = (string) ($gaps[$id] ?? ' ');
			$out .= $gap;
			$lastWasWord = false;
		}
	}
	if (!empty($sidecar['trailing_implicit_period'])) {
		$out .= '.';
	}
	return $out;
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_hybrid_sidecar_model(array $sidecar): array
{
	$bareList = is_array($sidecar['skeleton_bare'] ?? null) ? $sidecar['skeleton_bare'] : array();
	$bare = array();
	foreach ($bareList as $sk) {
		$bare[(string) $sk] = true;
	}
	return fractal_zip_enwik_consonant_hybrid_model_with_vocab(array(
		'skeleton_vocab' => $sidecar['skeleton_vocab'] ?? array(),
		'skeleton_ambig' => $sidecar['skeleton_ambig'] ?? array(),
		'skeleton_unique' => $sidecar['skeleton_unique'] ?? array(),
		'context_unique' => $sidecar['context_unique'] ?? array(),
		'skeleton_bare' => $bare,
	));
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_hybrid_undo_ascii(string $payload, array $sidecar): string
{
	$model = fractal_zip_enwik_consonant_hybrid_sidecar_model($sidecar);
	$segments = fractal_zip_enwik_text_segment_implicit_space($payload);
	$out = '';
	$prevWord = '';
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$out .= $s;
			continue;
		}
		if (preg_match('/^z_([a-z]+)_r(\d+)$/', $s, $m)) {
			$sk = (string) $m[1];
			$word = fractal_zip_enwik_consonant_hybrid_decode_word($sk, $prevWord, $model, (int) $m[2], true) ?? $s;
			$out .= $word;
			$prevWord = $word;
			continue;
		}
		if (preg_match('/^z_([a-z]+)$/', $s, $m)) {
			$sk = (string) $m[1];
			$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
			$ambig = is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array();
			if (isset($unique[$sk]) || isset($ambig[$sk])) {
				$word = fractal_zip_enwik_consonant_hybrid_decode_word($sk, $prevWord, $model);
				$out .= $word ?? $s;
				if ($word !== null) {
					$prevWord = $word;
				}
				continue;
			}
		}
		$bareSk = fractal_zip_enwik_consonant_hybrid_decode_sk_token($s, $model);
		if ($bareSk !== null) {
			$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
			$ambig = is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array();
			if (isset($unique[$bareSk]) || isset($ambig[$bareSk])) {
				$word = fractal_zip_enwik_consonant_hybrid_decode_word($bareSk, $prevWord, $model);
				$out .= $word ?? $s;
				if ($word !== null) {
					$prevWord = $word;
				}
				continue;
			}
		}
		$out .= $s;
		$prevWord = $s;
	}
	return $out;
}

/** Placeholder in literal stream where a skeleton token was extracted (unlikely in wiki preserve-text). */
const FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER = "\x0E";

/** Sentinel between literal-only and skeleton-only ascii streams (reversible split for phda9). */
const FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL = "\x0E\x0E\x0E";

/** PUA wire substitute for {@see FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER} (raw \\x0E can segfault phda9). */
const FRACTAL_ZIP_CONSONANT_SK_PHDA9_PLACEHOLDER = "\xEE\x80\x80";

function fractal_zip_enwik_consonant_sk_phda9_placeholder(): string
{
	$e = getenv('FRACTAL_ZIP_CONSONANT_SK_PHDA9_PLACEHOLDER');
	if ($e === false || trim((string) $e) === '') {
		return FRACTAL_ZIP_CONSONANT_SK_PHDA9_PLACEHOLDER;
	}
	$e = trim((string) $e);
	if (str_starts_with($e, '0x') && strlen($e) > 2) {
		$bin = hex2bin(substr($e, 2));
		return is_string($bin) && $bin !== '' ? $bin : FRACTAL_ZIP_CONSONANT_SK_PHDA9_PLACEHOLDER;
	}
	return $e;
}

function fractal_zip_enwik_consonant_sk_phda9_wire_escape_literal(string $literal): string
{
	if (strpos($literal, FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER) === false) {
		return $literal;
	}
	return str_replace(
		FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER,
		fractal_zip_enwik_consonant_sk_phda9_placeholder(),
		$literal
	);
}

function fractal_zip_enwik_consonant_sk_phda9_wire_unescape_literal(string $literal): string
{
	$wire = fractal_zip_enwik_consonant_sk_phda9_placeholder();
	if ($wire === FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER || strpos($literal, $wire) === false) {
		return $literal;
	}
	return str_replace($wire, FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER, $literal);
}

function fractal_zip_enwik_consonant_sk_dual_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_CONSONANT_SK_DUAL');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** Optional phda9 dict path for sk-only dual members (FRACTAL_ZIP_PAQ_PHDA9_DICT_SK). */
function fractal_zip_enwik_consonant_sk_dual_phda9_dict_path(): ?string
{
	$e = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT_SK');
	if ($e === false || trim((string) $e) === '') {
		return null;
	}
	$path = trim((string) $e);
	return is_file($path) ? $path : null;
}

/** @return array{literal: string, sk: string} */
function fractal_zip_enwik_consonant_hybrid_split_part_payload(string $payload): array
{
	$sent = FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL;
	$pos = strpos($payload, $sent);
	if ($pos === false) {
		return array('literal' => $payload, 'sk' => '');
	}
	return array(
		'literal' => substr($payload, 0, $pos),
		'sk' => substr($payload, $pos + strlen($sent)),
	);
}

function fractal_zip_enwik_consonant_hybrid_split_join_payload(string $literal, string $sk): string
{
	if ($sk === '') {
		return $literal;
	}
	return $literal . FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL . $sk;
}

/**
 * Reorder skeleton vocab: high-frequency ids first, then prefix-clustered within ties.
 *
 * @param list<string> $pageTexts
 * @param array<string, mixed> $model
 * @return array<string, mixed>
 */
function fractal_zip_enwik_consonant_hybrid_reorder_vocab_by_frequency(
	array $pageTexts,
	array $model
): array {
	$counts = array();
	foreach ($pageTexts as $pageText) {
		if ($pageText === '') {
			continue;
		}
		$segments = fractal_zip_enwik_text_segment_implicit_space($pageText);
		$n = count($segments);
		$depth = array('template' => 0, 'link' => 0);
		$prevWord = '';
		for ($i = 0; $i < $n; $i++) {
			$type = (string) ($segments[$i]['type'] ?? 'gap');
			$s = (string) ($segments[$i]['text'] ?? '');
			if ($type === 'gap') {
				$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
				$depth['template'] += $d['template'];
				$depth['link'] += $d['link'];
				continue;
			}
			$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
				? (string) $segments[$i - 1]['text'] : '';
			$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
				? (string) $segments[$i + 1]['text'] : '';
			if (!fractal_zip_enwik_syllable_word_consonant_eligible($s, $gapBefore, $gapAfter, $depth)) {
				$prevWord = $s;
				continue;
			}
			$sk = fractal_zip_enwik_consonant_skeleton($s);
			$tok = fractal_zip_enwik_consonant_hybrid_encode_token(
				$s, $model, $gapBefore, $gapAfter, $depth, $prevWord, 'ascii'
			);
			if ($tok['type'] === 'literal') {
				$prevWord = $s;
				continue;
			}
			$counts[$sk] = ($counts[$sk] ?? 0) + 1;
			$prevWord = $s;
		}
	}
	$skKeys = array_keys(array_merge(
		is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array(),
		is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array()
	));
	usort($skKeys, static function (string $a, string $b) use ($counts): int {
		$ca = (int) ($counts[$a] ?? 0);
		$cb = (int) ($counts[$b] ?? 0);
		if ($ca !== $cb) {
			return $cb <=> $ca;
		}
		$la = strlen($a);
		$lb = strlen($b);
		if ($la !== $lb) {
			return $la <=> $lb;
		}
		return strcmp($a, $b);
	});
	$model['skeleton_vocab'] = $skKeys;
	$model['skeleton_vocab_index'] = fractal_zip_enwik_text_vocab_index($skKeys);
	return $model;
}

function fractal_zip_enwik_consonant_sk_freq_vocab_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_CONSONANT_SK_FREQ_VOCAB');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** Optional inner preprocess on isolated skeleton ascii/binary stream only. */
function fractal_zip_enwik_consonant_sk_inner_preprocess_id(): string
{
	$raw = strtolower(trim((string) (getenv('FRACTAL_ZIP_CONSONANT_SK_INNER') ?: 'none')));
	if ($raw === '' || $raw === '0' || $raw === 'none') {
		return 'none';
	}
	return $raw;
}

/**
 * Apply FZ text preprocess to skeleton stream only (lower entropy target).
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_consonant_sk_apply_inner_preprocess(string $skPayload, array $opts = array()): array
{
	$innerId = fractal_zip_enwik_consonant_sk_inner_preprocess_id();
	if ($innerId === 'none' || $skPayload === '') {
		return array(
			'payload' => $skPayload,
			'sidecar' => array('preprocess' => 'none'),
			'meta' => array(),
		);
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$pre = fractal_zip_text_preprocess_apply($innerId, $skPayload, $opts);
	return array(
		'payload' => (string) $pre['payload'],
		'sidecar' => is_array($pre['sidecar'] ?? null) ? $pre['sidecar'] : array(),
		'meta' => is_array($pre['meta'] ?? null) ? $pre['meta'] : array(),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_sk_undo_inner_preprocess(string $payload, array $sidecar): string
{
	$innerId = (string) ($sidecar['sk_inner_preprocess'] ?? fractal_zip_enwik_consonant_sk_inner_preprocess_id());
	if ($innerId === 'none' || $innerId === '') {
		return $payload;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	return fractal_zip_text_preprocess_undo($innerId, $payload, $sidecar);
}

/**
 * Split preserve-text: literal/gap stream + isolated skeleton stream (document order).
 * Wire payload = literal + sentinel + sk_ascii (phda9-friendly); sk_binary in meta for FZSK inner fold.
 *
 * @param array<string, mixed> $model
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_consonant_hybrid_split_preprocess(string $text, array $model, array $opts = array()): array
{
	$frozen = !empty($opts['frozen']);
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$literal = '';
	$skAscii = '';
	$skBin = '';
	$encodedSk = 0;
	$encodedAmbig = 0;
	$encodedCtx = 0;
	$literalWords = 0;
	$n = count($segments);
	$depth = array('template' => 0, 'link' => 0);
	$prevWord = '';
	for ($i = 0; $i < $n; $i++) {
		$seg = $segments[$i];
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
			$depth['template'] += $d['template'];
			$depth['link'] += $d['link'];
			$literal .= $s;
			continue;
		}
		$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i - 1]['text'] : '';
		$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i + 1]['text'] : '';
		$tok = fractal_zip_enwik_consonant_hybrid_encode_token(
			$s, $model, $gapBefore, $gapAfter, $depth, $prevWord, 'ascii'
		);
		if ($tok['type'] === 'literal') {
			$literalWords++;
			$literal .= (string) $tok['text'];
			$prevWord = $s;
			continue;
		}
		$literal .= FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER;
		if ($tok['type'] === 'ambig') {
			$encodedAmbig++;
			$sk = (string) $tok['sk'];
			$rank = (int) $tok['rank'];
			$skTok = fractal_zip_enwik_consonant_hybrid_format_sk_token($sk, $model, true, $rank);
			$skAscii .= ($skAscii === '' ? '' : ' ') . $skTok;
			$skBin .= chr(FRACTAL_ZIP_CONSONANT_TAG_SK_AMBIG)
				. fractal_zip_enwik_encode_varint_u32(strlen($sk))
				. $sk
				. fractal_zip_enwik_encode_varint_u32($rank);
			$prevWord = $s;
			continue;
		}
		$encodedSk++;
		$sk = (string) $tok['sk'];
		if (isset($model['skeleton_ambig'][$sk])
			&& fractal_zip_enwik_consonant_hybrid_context_word($sk, $prevWord, $model) === $s) {
			$encodedCtx++;
		}
		$skAscii .= ($skAscii === '' ? '' : ' ') . fractal_zip_enwik_consonant_hybrid_format_sk_token($sk, $model);
		$skBin .= "\x01" . fractal_zip_enwik_encode_varint_u32(strlen($sk)) . $sk;
		$prevWord = $s;
	}
	$skInner = fractal_zip_enwik_consonant_sk_apply_inner_preprocess($skAscii, $opts);
	$skWire = (string) $skInner['payload'];
	$payload = $literal . FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL . $skWire;
	$sidecar = array(
		'preprocess' => 'consonant_hybrid_split',
		'scheme' => 'consonant_hybrid_split',
		'payload_codec' => 'split_ascii',
		'frozen' => $frozen,
		'sk_inner_preprocess' => fractal_zip_enwik_consonant_sk_inner_preprocess_id(),
	);
	if ($skInner['sidecar'] !== array() && ($sidecar['sk_inner_preprocess'] ?? 'none') !== 'none') {
		$sidecar['sk_inner_sidecar'] = $skInner['sidecar'];
	}
	if (!$frozen) {
		$sidecar['skeleton_vocab'] = $model['skeleton_vocab'] ?? array();
		$sidecar['skeleton_ambig'] = $model['skeleton_ambig'] ?? array();
		$sidecar['skeleton_unique'] = $model['skeleton_unique'] ?? array();
		$sidecar['context_unique'] = $model['context_unique'] ?? array();
		$sidecar['skeleton_bare'] = array_keys(is_array($model['skeleton_bare'] ?? null) ? $model['skeleton_bare'] : array());
	}
	return array(
		'payload' => $payload,
		'sidecar' => $sidecar,
		'meta' => array(
			'encoded_skeletons' => $encodedSk,
			'encoded_ambiguous' => $encodedAmbig,
			'encoded_context_resolved' => $encodedCtx,
			'literal_words' => $literalWords,
			'literal_bytes' => strlen($literal),
			'sk_ascii_bytes' => strlen($skAscii),
			'sk_wire_bytes' => strlen($skWire),
			'sk_binary_bytes' => strlen($skBin),
			'skeleton_vocab_size' => count($model['skeleton_vocab'] ?? array()),
			'unique_skeletons' => count($model['skeleton_unique'] ?? array()),
			'ambig_skeletons' => count($model['skeleton_ambig'] ?? array()),
			'sk_payload' => $skBin,
			'sk_ascii_raw' => $skAscii,
		),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_hybrid_split_undo(string $payload, array $sidecar): string
{
	$sent = FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL;
	$pos = strpos($payload, $sent);
	if ($pos === false) {
		return fractal_zip_enwik_consonant_hybrid_undo_ascii($payload, $sidecar);
	}
	$literal = substr($payload, 0, $pos);
	$skWire = substr($payload, $pos + strlen($sent));
	$skInnerSc = is_array($sidecar['sk_inner_sidecar'] ?? null) ? $sidecar['sk_inner_sidecar'] : array();
	$skAscii = fractal_zip_enwik_consonant_sk_undo_inner_preprocess($skWire, array_merge(
		$skInnerSc,
		array('sk_inner_preprocess' => (string) ($sidecar['sk_inner_preprocess'] ?? 'none'))
	));
	$model = fractal_zip_enwik_consonant_hybrid_sidecar_model($sidecar);
	$skTokens = $skAscii === '' ? array() : preg_split('/\s+/', $skAscii, -1, PREG_SPLIT_NO_EMPTY);
	$skIdx = 0;
	$out = '';
	$prevWord = '';
	$parts = explode(FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER, $literal);
	foreach ($parts as $pi => $chunk) {
		$out .= $chunk;
		if (preg_match_all("/[A-Za-z][A-Za-z0-9_\x27]*/", $chunk, $wm) && !empty($wm[0])) {
			$prevWord = (string) end($wm[0]);
		}
		if ($pi + 1 >= count($parts)) {
			break;
		}
		$skTok = (string) ($skTokens[$skIdx] ?? '');
		$skIdx++;
		if ($skTok === '') {
			$out .= FRACTAL_ZIP_CONSONANT_SK_PLACEHOLDER;
			continue;
		}
		if (preg_match('/^z_([a-z]+)_r(\d+)$/', $skTok, $m)) {
			$word = fractal_zip_enwik_consonant_hybrid_decode_word(
				(string) $m[1], $prevWord, $model, (int) $m[2], true
			) ?? $skTok;
			$out .= $word;
			$prevWord = $word;
			continue;
		}
		if (preg_match('/^z_([a-z]+)$/', $skTok, $m)) {
			$sk = (string) $m[1];
			$word = fractal_zip_enwik_consonant_hybrid_decode_word($sk, $prevWord, $model) ?? $skTok;
			$out .= $word;
			$prevWord = $word;
			continue;
		}
		$bareSk = fractal_zip_enwik_consonant_hybrid_decode_sk_token($skTok, $model);
		if ($bareSk !== null) {
			$word = fractal_zip_enwik_consonant_hybrid_decode_word($bareSk, $prevWord, $model) ?? $skTok;
			$out .= $word;
			$prevWord = $word;
			continue;
		}
		$out .= $skTok;
	}
	return $out;
}

/**
 * Lossy prose skeleton + amortized vowel-reconstruction sidecar (FZ economics).
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_consonant_hybrid_lossy_preprocess(string $text, array $opts = array()): array
{
	$model = is_array($opts['consonant_model'] ?? null) ? $opts['consonant_model'] : null;
	if ($model === null) {
		$model = fractal_zip_enwik_consonant_hybrid_mine_model($text);
	}
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$out = '';
	$encodedSk = 0;
	$literalWords = 0;
	$ambiguous = 0;
	$n = count($segments);
	$depth = array('template' => 0, 'link' => 0);
	$prevWord = '';
	for ($i = 0; $i < $n; $i++) {
		$seg = $segments[$i];
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
			$depth['template'] += $d['template'];
			$depth['link'] += $d['link'];
			$out .= $s;
			continue;
		}
		$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i - 1]['text'] : '';
		$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i + 1]['text'] : '';
		if (!fractal_zip_enwik_syllable_word_consonant_eligible($s, $gapBefore, $gapAfter, $depth)) {
			$literalWords++;
			$out .= $s;
			$prevWord = $s;
			continue;
		}
		$sk = fractal_zip_enwik_consonant_skeleton($s);
		$tok = fractal_zip_enwik_consonant_hybrid_encode_token(
			$s, $model, $gapBefore, $gapAfter, $depth, $prevWord, 'ascii'
		);
		if ($tok['type'] === 'literal') {
			$literalWords++;
			$out .= $s;
			$prevWord = $s;
			continue;
		}
		$encodedSk++;
		if ($tok['type'] === 'ambig') {
			$ambiguous++;
			$out .= $sk . ':' . (int) $tok['rank'];
		} else {
			$out .= $sk;
		}
		$prevWord = $s;
	}
	$sidecar = array(
		'preprocess' => 'consonant_hybrid_lossy',
		'scheme' => 'consonant_hybrid_lossy',
		'frozen' => !empty($opts['frozen']),
		'skeleton_unique' => $model['skeleton_unique'] ?? array(),
		'skeleton_ambig' => $model['skeleton_ambig'] ?? array(),
		'context_unique' => $model['context_unique'] ?? array(),
	);
	return array(
		'payload' => $out,
		'sidecar' => $sidecar,
		'meta' => array(
			'encoded_skeletons' => $encodedSk,
			'literal_words' => $literalWords,
			'ambiguous_words' => $ambiguous,
			'unique_skeletons' => count($model['skeleton_unique'] ?? array()),
			'ambig_skeletons' => count($model['skeleton_ambig'] ?? array()),
		),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_enwik_consonant_hybrid_lossy_undo(string $payload, array $sidecar): string
{
	$model = fractal_zip_enwik_consonant_hybrid_model_with_vocab(array(
		'skeleton_unique' => $sidecar['skeleton_unique'] ?? array(),
		'skeleton_ambig' => $sidecar['skeleton_ambig'] ?? array(),
		'context_unique' => $sidecar['context_unique'] ?? array(),
	));
	$segments = fractal_zip_enwik_text_segment_implicit_space($payload);
	$out = '';
	$prevWord = '';
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$out .= $s;
			continue;
		}
		if (preg_match('/^([a-z]+):(\d+)$/', $s, $m)) {
			$sk = (string) $m[1];
			$word = fractal_zip_enwik_consonant_hybrid_decode_word($sk, $prevWord, $model, (int) $m[2], true)
				?? $sk;
			$out .= $word;
			$prevWord = $word;
			continue;
		}
		if (preg_match('/^[a-z]+$/', $s)) {
			$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
			$ambig = is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array();
			if (isset($unique[$s]) || isset($ambig[$s])) {
				$word = fractal_zip_enwik_consonant_hybrid_decode_word($s, $prevWord, $model) ?? $s;
				$out .= $word;
				$prevWord = $word;
				continue;
			}
		}
		$out .= $s;
		$prevWord = $s;
	}
	return $out;
}

function fractal_zip_enwik_stat_syllable_isp_payload_codec(array $opts = array()): string
{
	$codec = (string) ($opts['payload_codec'] ?? '');
	if ($codec === '') {
		$env = getenv('FRACTAL_ZIP_STAT_SYLLABLE_ISP_CODEC');
		if ($env !== false && trim((string) $env) !== '') {
			return strtolower(trim((string) $env));
		}
		$env2 = getenv('FRACTAL_ZIP_STAT_ISP_PAYLOAD_CODEC');
		if ($env2 !== false && trim((string) $env2) !== '') {
			return strtolower(trim((string) $env2));
		}
		return 'varint';
	}
	return strtolower($codec);
}

function fractal_zip_enwik_stat_syllable_isp_max_tokens(): int
{
	$env = getenv('FRACTAL_ZIP_STAT_SYLLABLE_ISP_MAX_TOKENS');
	if ($env !== false && trim((string) $env) !== '') {
		return max(4096, (int) $env);
	}
	return 120000;
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_stat_syllable_isp_mine_vocab(string $mineText, ?int $maxTokens = null): array
{
	$maxTokens = $maxTokens ?? fractal_zip_enwik_stat_syllable_isp_max_tokens();
	$segments = fractal_zip_enwik_text_segment_implicit_space($mineText);
	$tokens = fractal_zip_enwik_syllable_collect_tokens($segments);
	return fractal_zip_enwik_text_build_vocab($tokens, $maxTokens);
}

/**
 * @return array{vocab: list<string>, vocab_index: array<string, int>}
 */
function fractal_zip_enwik_stat_syllable_isp_vocab_tables(array $vocab): array
{
	return array(
		'vocab' => array_values($vocab),
		'vocab_index' => fractal_zip_enwik_text_vocab_index(array_values($vocab)),
	);
}

/**
 * Syllable ISP (frozen vocab amortized) — insoluble syllable units for phda9 inner path.
 *
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_text_stat_syllable_isp_preprocess(string $text, array $opts = array()): array
{
	$vocab = $opts['vocab'] ?? null;
	if (!is_array($vocab) || $vocab === array()) {
		$vocab = fractal_zip_enwik_stat_syllable_isp_mine_vocab($text);
	}
	$tables = fractal_zip_enwik_stat_syllable_isp_vocab_tables($vocab);
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$expanded = array();
	$innerSep = FRACTAL_ZIP_SYLLABLE_INNER_SEP;
	foreach ($segments as $seg) {
		$type = (string) ($seg['type'] ?? 'gap');
		$s = (string) ($seg['text'] ?? '');
		if ($type === 'gap') {
			$expanded[] = array('type' => 'gap', 'text' => $s);
			continue;
		}
		foreach (fractal_zip_enwik_syllabify_word($s) as $i => $sy) {
			if ($i > 0) {
				$expanded[] = array('type' => 'gap', 'text' => $innerSep);
			}
			$expanded[] = array('type' => 'word', 'text' => $sy);
		}
	}
	$codec = fractal_zip_enwik_stat_syllable_isp_payload_codec($opts);
	$scheme = ($codec === 'base94') ? 'words_base94_isp' : 'words_id_varint_isp';
	$packed = fractal_zip_enwik_text_encode_words_stream($scheme, $expanded, array(
		'vocab' => $tables['vocab'],
		'vocab_index' => $tables['vocab_index'],
	));
	$sidecar = array_merge($packed['sidecar'], array(
		'preprocess' => 'stat_syllable_isp',
		'scheme' => $scheme,
		'payload_codec' => $codec,
		'syllable_inner_sep' => $innerSep,
		'frozen' => !empty($opts['frozen']),
	));
	if (!empty($opts['frozen'])) {
		unset($sidecar['vocab']);
	} else {
		$sidecar['vocab'] = $tables['vocab'];
	}
	return array(
		'payload' => (string) $packed['payload'],
		'sidecar' => $sidecar,
		'meta' => $packed['meta'] ?? array(),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_text_stat_syllable_isp_undo(string $payload, array $sidecar): string
{
	$innerSep = (string) ($sidecar['syllable_inner_sep'] ?? FRACTAL_ZIP_SYLLABLE_INNER_SEP);
	$scheme = (string) ($sidecar['scheme'] ?? 'words_id_varint_isp');
	$expanded = fractal_zip_enwik_text_decode_words_stream($scheme, $payload, $sidecar);
	if ($innerSep === '') {
		return $expanded;
	}
	return str_replace($innerSep, '', $expanded);
}

/** Compact wire meta (model tables only; sidecars stay in preprocess json). */
const FRACTAL_ZIP_CONSONANT_HYBRID_META_MAGIC = "FZSK\x01";

/**
 * Keep only decode tables referenced by an encode pass over $text.
 *
 * @return array{skeleton_unique: array<string, string>, skeleton_ambig: array<string, list<string>>, context_unique: array<string, array<string, string>>}
 */
function fractal_zip_enwik_consonant_hybrid_trim_model_for_corpus(
	string $text,
	array $model,
	string $codec = 'ascii'
): array {
	$uniqueOut = array();
	$ambigOut = array();
	$ctxOut = array();
	$segments = fractal_zip_enwik_text_segment_implicit_space($text);
	$n = count($segments);
	$depth = array('template' => 0, 'link' => 0);
	$prevWord = '';
	for ($i = 0; $i < $n; $i++) {
		$type = (string) ($segments[$i]['type'] ?? 'gap');
		$s = (string) ($segments[$i]['text'] ?? '');
		if ($type === 'gap') {
			$d = fractal_zip_enwik_syllable_markup_depth_delta($s);
			$depth['template'] += $d['template'];
			$depth['link'] += $d['link'];
			continue;
		}
		$gapBefore = ($i > 0 && ($segments[$i - 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i - 1]['text'] : '';
		$gapAfter = ($i + 1 < $n && ($segments[$i + 1]['type'] ?? '') === 'gap')
			? (string) $segments[$i + 1]['text'] : '';
		$tok = fractal_zip_enwik_consonant_hybrid_encode_token(
			$s, $model, $gapBefore, $gapAfter, $depth, $prevWord, $codec
		);
		if ($tok['type'] === 'literal') {
			$prevWord = $s;
			continue;
		}
		$sk = (string) $tok['sk'];
		$unique = is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array();
		$ambig = is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array();
		if ($tok['type'] === 'ambig') {
			if (isset($ambig[$sk])) {
				$ambigOut[$sk] = $ambig[$sk];
			}
		} elseif (isset($unique[$sk])) {
			$uniqueOut[$sk] = (string) $unique[$sk];
		} elseif (isset($ambig[$sk])) {
			$prevKey = strtolower($prevWord);
			if (!isset($ctxOut[$prevKey])) {
				$ctxOut[$prevKey] = array();
			}
			$ctxOut[$prevKey][$sk] = $s;
		}
		$prevWord = $s;
	}
	return array(
		'skeleton_unique' => $uniqueOut,
		'skeleton_ambig' => $ambigOut,
		'context_unique' => $ctxOut,
	);
}

/** @return list<string> */
function fractal_zip_enwik_consonant_hybrid_family_preprocess_ids(): array
{
	return array('consonant_hybrid', 'consonant_hybrid_split', 'consonant_hybrid_lossy');
}

function fractal_zip_enwik_consonant_hybrid_family_preprocess(string $preprocessId): bool
{
	return in_array(strtolower(trim($preprocessId)), fractal_zip_enwik_consonant_hybrid_family_preprocess_ids(), true);
}

function fractal_zip_enwik_consonant_hybrid_payload_codec_byte(string $codec): int
{
	return match (strtolower(trim($codec))) {
		'varint' => 1,
		'split_ascii' => 2,
		'lossy_ascii' => 3,
		default => 0,
	};
}

function fractal_zip_enwik_consonant_hybrid_payload_codec_from_byte(int $byte): string
{
	return match ($byte) {
		1 => 'varint',
		2 => 'split_ascii',
		3 => 'lossy_ascii',
		default => 'ascii',
	};
}

/** @param array<string, mixed> $meta */
function fractal_zip_enwik_consonant_hybrid_pack_compact_meta(array $meta): string
{
	$writeStr = static function (string $s): string {
		return fractal_zip_enwik_encode_varint_u32(strlen($s)) . $s;
	};
	$buf = FRACTAL_ZIP_CONSONANT_HYBRID_META_MAGIC;
	$buf .= chr(fractal_zip_enwik_consonant_hybrid_payload_codec_byte((string) ($meta['payload_codec'] ?? 'ascii')));
	$unique = is_array($meta['skeleton_unique'] ?? null) ? $meta['skeleton_unique'] : array();
	ksort($unique, SORT_STRING);
	$buf .= fractal_zip_enwik_encode_varint_u32(count($unique));
	foreach ($unique as $sk => $word) {
		$buf .= $writeStr((string) $sk) . $writeStr((string) $word);
	}
	$ambig = is_array($meta['skeleton_ambig'] ?? null) ? $meta['skeleton_ambig'] : array();
	ksort($ambig, SORT_STRING);
	$buf .= fractal_zip_enwik_encode_varint_u32(count($ambig));
	foreach ($ambig as $sk => $cands) {
		$buf .= $writeStr((string) $sk);
		$cands = is_array($cands) ? array_values($cands) : array();
		$buf .= fractal_zip_enwik_encode_varint_u32(count($cands));
		foreach ($cands as $w) {
			$buf .= $writeStr((string) $w);
		}
	}
	$ctx = is_array($meta['context_unique'] ?? null) ? $meta['context_unique'] : array();
	ksort($ctx, SORT_STRING);
	$ctxFlat = array();
	foreach ($ctx as $prev => $skMap) {
		if (!is_array($skMap)) {
			continue;
		}
		foreach ($skMap as $sk => $word) {
			$ctxFlat[] = array((string) $prev, (string) $sk, (string) $word);
		}
	}
	$buf .= fractal_zip_enwik_encode_varint_u32(count($ctxFlat));
	foreach ($ctxFlat as $row) {
		$buf .= $writeStr($row[0]) . $writeStr($row[1]) . $writeStr($row[2]);
	}
	return $buf;
}

/** @return array<string, mixed> */
function fractal_zip_enwik_consonant_hybrid_unpack_compact_meta(string $blob): array
{
	$magicLen = strlen(FRACTAL_ZIP_CONSONANT_HYBRID_META_MAGIC);
	if (strlen($blob) < $magicLen + 1 || !str_starts_with($blob, FRACTAL_ZIP_CONSONANT_HYBRID_META_MAGIC)) {
		throw new RuntimeException('consonant_hybrid: invalid compact meta magic');
	}
	$pos = $magicLen;
	$readStr = static function () use ($blob, &$pos): string {
		$pair = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		if ($pair === null) {
			throw new RuntimeException('consonant_hybrid: compact meta truncated');
		}
		$len = (int) $pair[0];
		$pos = (int) $pair[1];
		if ($len < 0 || $pos + $len > strlen($blob)) {
			throw new RuntimeException('consonant_hybrid: compact meta string overrun');
		}
		$s = substr($blob, $pos, $len);
		$pos += $len;
		return $s;
	};
	$codecByte = ord($blob[$pos]);
	$pos++;
	$unique = array();
	$pair = fractal_zip_enwik_decode_varint_u32($blob, $pos);
	if ($pair === null) {
		throw new RuntimeException('consonant_hybrid: compact meta truncated');
	}
	$nUnique = (int) $pair[0];
	$pos = (int) $pair[1];
	for ($i = 0; $i < $nUnique; $i++) {
		$sk = $readStr();
		$word = $readStr();
		$unique[$sk] = $word;
	}
	$ambig = array();
	$pair = fractal_zip_enwik_decode_varint_u32($blob, $pos);
	if ($pair === null) {
		throw new RuntimeException('consonant_hybrid: compact meta truncated');
	}
	$nAmbig = (int) $pair[0];
	$pos = (int) $pair[1];
	for ($i = 0; $i < $nAmbig; $i++) {
		$sk = $readStr();
		$pair2 = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		if ($pair2 === null) {
			throw new RuntimeException('consonant_hybrid: compact meta truncated');
		}
		$nC = (int) $pair2[0];
		$pos = (int) $pair2[1];
		$cands = array();
		for ($j = 0; $j < $nC; $j++) {
			$cands[] = $readStr();
		}
		$ambig[$sk] = $cands;
	}
	$pair = fractal_zip_enwik_decode_varint_u32($blob, $pos);
	if ($pair === null) {
		throw new RuntimeException('consonant_hybrid: compact meta truncated');
	}
	$nCtx = (int) $pair[0];
	$pos = (int) $pair[1];
	$ctx = array();
	for ($i = 0; $i < $nCtx; $i++) {
		$prev = $readStr();
		$sk = $readStr();
		$word = $readStr();
		if (!isset($ctx[$prev])) {
			$ctx[$prev] = array();
		}
		$ctx[$prev][$sk] = $word;
	}
	return array(
		'preprocess' => 'consonant_hybrid',
		'codec' => 'consonant_hybrid_isp',
		'frozen' => true,
		'payload_codec' => fractal_zip_enwik_consonant_hybrid_payload_codec_from_byte($codecByte),
		'skeleton_unique' => $unique,
		'skeleton_ambig' => $ambig,
		'context_unique' => $ctx,
	);
}

/** @param array{skeleton_unique: array<string, string>, skeleton_ambig: array<string, list<string>>, context_unique: array<string, array<string, string>>} $a @param array{skeleton_unique: array<string, string>, skeleton_ambig: array<string, list<string>>, context_unique: array<string, array<string, string>>} $b */
function fractal_zip_enwik_consonant_hybrid_merge_used_tables(array $a, array $b): array
{
	foreach ($b['skeleton_unique'] ?? array() as $sk => $word) {
		$a['skeleton_unique'][$sk] = $word;
	}
	foreach ($b['skeleton_ambig'] ?? array() as $sk => $cands) {
		$a['skeleton_ambig'][$sk] = $cands;
	}
	foreach ($b['context_unique'] ?? array() as $prev => $skMap) {
		if (!is_array($skMap)) {
			continue;
		}
		if (!isset($a['context_unique'][$prev])) {
			$a['context_unique'][$prev] = array();
		}
		foreach ($skMap as $sk => $word) {
			$a['context_unique'][$prev][$sk] = $word;
		}
	}
	return $a;
}

/** @return array<string, mixed> */
function fractal_zip_enwik_consonant_hybrid_shared_meta_from_model(array $model, ?string $trimCorpus = null, string $codec = 'ascii'): array
{
	if ($trimCorpus !== null && $trimCorpus !== '') {
		return fractal_zip_enwik_consonant_hybrid_trim_model_for_corpus($trimCorpus, $model, $codec);
	}
	$meta = array(
		'skeleton_ambig' => $model['skeleton_ambig'] ?? array(),
		'skeleton_unique' => $model['skeleton_unique'] ?? array(),
		'context_unique' => $model['context_unique'] ?? array(),
	);
	if (is_array($model['skeleton_bare'] ?? null) && $model['skeleton_bare'] !== array()) {
		$meta['skeleton_bare'] = array_keys($model['skeleton_bare']);
	}
	if (isset($model['payload_codec'])) {
		$meta['payload_codec'] = $model['payload_codec'];
	}
	return $meta;
}

/** Sidecar-only preprocess JSON + compact meta blob for inner-fold trailer. */
function fractal_zip_enwik_consonant_hybrid_inner_fold_payload(array $model, string $codec): string
{
	return fractal_zip_enwik_consonant_hybrid_pack_compact_meta(array_merge(
		fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($model),
		array('payload_codec' => $codec)
	));
}

/** @param array<string, mixed> $model */
function fractal_zip_enwik_consonant_hybrid_model_with_vocab(array $model): array
{
	if (isset($model['skeleton_vocab']) && is_array($model['skeleton_vocab']) && $model['skeleton_vocab'] !== array()) {
		return $model;
	}
	$keys = array_keys(array_merge(
		is_array($model['skeleton_unique'] ?? null) ? $model['skeleton_unique'] : array(),
		is_array($model['skeleton_ambig'] ?? null) ? $model['skeleton_ambig'] : array()
	));
	sort($keys, SORT_STRING);
	$model['skeleton_vocab'] = $keys;
	$model['skeleton_vocab_index'] = fractal_zip_enwik_text_vocab_index($keys);
	return $model;
}

/** Estimate amortized bytes of consonant hybrid shared meta over full enwik8. */
function fractal_zip_enwik_consonant_hybrid_meta_wire_bytes(array $meta, int $slicePages, ?int $fullPages = null): int
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
	if (isset($meta['skeleton_unique']) || isset($meta['skeleton_ambig'])) {
		$raw = fractal_zip_enwik_consonant_hybrid_pack_compact_meta($meta);
		$gz = gzencode($raw, 9);
		$wire = is_string($gz) ? strlen($gz) : strlen($raw);
	} else {
		$raw = strlen(json_encode($meta, JSON_UNESCAPED_SLASHES) ?: '{}');
		$gz = gzencode(json_encode($meta, JSON_UNESCAPED_SLASHES) ?: '{}', 9);
		$wire = is_string($gz) ? strlen($gz) : $raw;
	}
	return fractal_zip_enwik_inner_fold_amortized_cost($wire, $slicePages, $fullPages);
}

/** @param array<string, mixed> $preprocessMeta */
function fractal_zip_enwik_merge_consonant_hybrid_sidecar(array $sidecar, array $preprocessMeta): array
{
	foreach (array('skeleton_vocab', 'skeleton_ambig', 'skeleton_unique', 'context_unique', 'skeleton_bare') as $k) {
		if (is_array($preprocessMeta[$k] ?? null)) {
			$sidecar[$k] = $preprocessMeta[$k];
		}
	}
	return $sidecar;
}

/**
 * @return array{shell: string, text: string, text_bytes: int, markup_bytes: int}
 */
function fractal_zip_enwik_page_markup_text_split(string $pageXml): array
{
	$text = fractal_zip_enwik_extract_page_preserve_text($pageXml);
	return array(
		'shell' => fractal_zip_enwik_inject_text_into_shell_page($pageXml, ''),
		'text' => $text,
		'text_bytes' => strlen($text),
		'markup_bytes' => strlen($pageXml) - strlen($text),
	);
}
