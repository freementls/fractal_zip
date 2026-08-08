<?php

declare(strict_types=1);

/**
 * Collision-free phrase abbreviations: mine high-count substrings, assign tokens
 * that never appear in the corpus (no sentinels). Prefer shortest natural
 * alphanumeric strings; uppercase acronyms are optional candidates only.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_score_gate.php';

const FRACTAL_ZIP_CFABB_MAGIC = "FZCF\x01";

/** @return array{max_words: int, max_chars: int, min_words: int, min_chars: int, min_count: int, max_entries: int} */
function fractal_zip_cfabb_default_opts(): array
{
	return array(
		'min_words' => 2,
		'max_words' => 10,
		'min_chars' => 8,
		'max_chars' => 100,
		'min_count' => 4,
		'max_entries' => 0,
	);
}

function fractal_zip_cfabb_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_WIKI_LOM_CFABB');
	if ($v !== false) {
		$v = strtolower(trim((string) $v));
		if ($v === '0' || $v === 'false' || $v === 'off') {
			return false;
		}
		if ($v === '1' || $v === 'true' || $v === 'on') {
			return true;
		}
	}
	if (fractal_zip_cfabb_inline_enabled()) {
		return true;
	}
	$table = getenv('FRACTAL_ZIP_CFABB_TABLE');
	return $table !== false && trim((string) $table) !== '' && is_file(trim((string) $table));
}

/** Mine cfabb at encode time; optional zero fold meta (Hutter prize tradeoff probe). */
function fractal_zip_cfabb_inline_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_CFABB_INLINE');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return in_array($v, array('1', 'true', 'on', 'yes'), true);
}

/** When inline: omit cfabb table from inner-fold trailer (meta_bytes → 0 for cfabb). */
function fractal_zip_cfabb_no_fold_enabled(): bool
{
	if (!fractal_zip_cfabb_inline_enabled()) {
		return false;
	}
	$v = getenv('FRACTAL_ZIP_CFABB_NO_FOLD');
	if ($v === false || trim((string) $v) === '') {
		return true;
	}
	$v = strtolower(trim((string) $v));
	return !in_array($v, array('0', 'false', 'off', 'no'), true);
}

/** Apply cfabb on sorted page XML before phda9 (preprocess=none); avoids wiki_lom wire overhead. */
function fractal_zip_cfabb_plain_xml_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_CFABB_PLAIN_XML');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return in_array($v, array('1', 'true', 'on', 'yes'), true);
}

/**
 * Mine (if inline) and apply cfabb to page-XML buffer for phda9_xml encode.
 *
 * @return list<array{phrase: string, token: string, count?: int}>
 */
function fractal_zip_cfabb_plain_xml_prepare(string $pageXmlBuf): array
{
	return fractal_zip_cfabb_plain_xml_prepare_from_chunk(array(), '', $pageXmlBuf);
}

/**
 * Mine on preserve-text; phda9-gate on sorted page-XML stream (real phda9 input).
 *
 * @param list<array{start: int, len: int}> $chunk
 * @return list<array{phrase: string, token: string, count?: int}>
 */
function fractal_zip_cfabb_plain_xml_prepare_from_chunk(array $chunk, string $blob, ?string $mineCorpus = null): array
{
	$mineCorpus = $mineCorpus ?? ($chunk !== array() && $blob !== ''
		? fractal_zip_cfabb_plain_xml_mine_corpus_from_chunk($chunk, $blob)
		: '');
	if (!fractal_zip_cfabb_plain_xml_enabled() || ($mineCorpus === '' && $chunk === array())) {
		return array();
	}
	$entries = array();
	if (fractal_zip_cfabb_inline_enabled()) {
		$entries = fractal_zip_cfabb_inline_cache_load();
		if ($entries === array()) {
			$mineOpts = fractal_zip_cfabb_default_opts();
			if ($chunk !== array()) {
				require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
				$split = enwik_split_page_refs($blob);
				if ($split !== null) {
					$mineOpts['corpus_pages'] = count($split['pages']);
				}
			}
			$entries = fractal_zip_cfabb_mine($mineCorpus, $mineOpts);
			$gate = getenv('FRACTAL_ZIP_CFABB_PLAIN_XML_GATE');
			$gateOn = $gate === false || trim((string) $gate) === ''
				|| !in_array(strtolower(trim((string) $gate)), array('0', 'false', 'off', 'no'), true);
			if ($gateOn && fractal_zip_cfabb_phda9_gate_enabled() && $chunk !== array()) {
				$entries = fractal_zip_cfabb_filter_phda9_greedy($mineCorpus, $entries, array(
					'phda9_gate_chunk' => $chunk,
					'phda9_gate_blob' => $blob,
				));
			} elseif ($gateOn && fractal_zip_cfabb_phda9_gate_enabled()) {
				$entries = fractal_zip_cfabb_filter_phda9_greedy($mineCorpus, $entries);
			}
			fractal_zip_cfabb_inline_cache_store($entries);
		}
	} else {
		$entries = fractal_zip_cfabb_load_json();
		if ($entries !== array()) {
			fractal_zip_cfabb_inline_cache_store($entries);
		}
	}
	return $entries;
}

function fractal_zip_cfabb_plain_xml_apply(string $pageXmlBuf, array $entries): string
{
	if ($entries === array()) {
		return $pageXmlBuf;
	}
	return fractal_zip_cfabb_apply($pageXmlBuf, $entries);
}

function fractal_zip_cfabb_plain_xml_undo(string $pageXmlBuf, array $entries): string
{
	if ($entries === array()) {
		return $pageXmlBuf;
	}
	return fractal_zip_cfabb_undo($pageXmlBuf, $entries);
}

/**
 * @param list<array{start: int, len: int}> $chunk
 */
function fractal_zip_cfabb_plain_xml_mine_corpus_from_chunk(array $chunk, string $blob): string
{
	$mine = '';
	foreach ($chunk as $p) {
		$page = substr($blob, (int) $p['start'], (int) $p['len']);
		$mine .= fractal_zip_enwik_extract_page_preserve_text($page);
	}
	return $mine;
}

function fractal_zip_cfabb_plain_xml_apply_page(string $pageXml, array $entries): string
{
	if ($entries === array()) {
		return $pageXml;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$text = fractal_zip_enwik_extract_page_preserve_text($pageXml);
	$text = fractal_zip_cfabb_apply($text, $entries);
	return fractal_zip_enwik_inject_text_into_shell_page($pageXml, $text);
}

function fractal_zip_cfabb_plain_xml_undo_page(string $pageXml, array $entries): string
{
	if ($entries === array()) {
		return $pageXml;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$text = fractal_zip_enwik_extract_page_preserve_text($pageXml);
	$text = fractal_zip_cfabb_undo($text, $entries);
	return fractal_zip_enwik_inject_text_into_shell_page($pageXml, $text);
}

/** @param list<array{phrase: string, token?: string, acronym?: string, count?: int}> $entries */
function fractal_zip_cfabb_inline_cache_store(array $entries): void
{
	$GLOBALS['fractal_zip_cfabb_inline_cache'] = $entries;
}

/** @return list<array{phrase: string, token?: string, acronym?: string, count?: int}> */
function fractal_zip_cfabb_inline_cache_load(): array
{
	$cached = $GLOBALS['fractal_zip_cfabb_inline_cache'] ?? null;
	return is_array($cached) ? $cached : array();
}

/** Token must not appear anywhere in corpus (substring-safe for lossless undo). */
function fractal_zip_cfabb_corpus_has_token(string $corpus, string|int|float $token): bool
{
	$token = (string) $token;
	if ($token === '') {
		return true;
	}
	return str_contains($corpus, $token);
}

/** @param list<string> $usedTokens */
function fractal_zip_cfabb_token_usable(string $token, string $corpus, array $usedTokens): bool
{
	if ($token === '' || !preg_match('/^[A-Za-z0-9]{2,8}$/', $token)) {
		return false;
	}
	if (fractal_zip_cfabb_corpus_has_token($corpus, $token)) {
		return false;
	}
	foreach ($usedTokens as $other) {
		$other = (string) $other;
		if ($other === $token) {
			return false;
		}
		if (str_contains($other, $token) || str_contains($token, $other)) {
			return false;
		}
	}
	return true;
}

/**
 * Short collision-free alphanumeric strings: aa..zz, a0..z9, 0a..9z, aaa.., …
 *
 * @return list<string>
 */
function fractal_zip_cfabb_token_pool(int $need, string $corpus, array $usedTokens = array()): array
{
	$out = array();
	$try = static function (string $t) use (&$out, $need, $corpus, &$usedTokens): void {
		if (count($out) >= $need) {
			return;
		}
		if (!fractal_zip_cfabb_token_usable($t, $corpus, array_merge($usedTokens, $out))) {
			return;
		}
		$out[] = $t;
	};
	for ($a = 97; $a <= 122; $a++) {
		for ($b = 97; $b <= 122; $b++) {
			$try(chr($a) . chr($b));
		}
	}
	for ($a = 97; $a <= 122; $a++) {
		for ($d = 48; $d <= 57; $d++) {
			$try(chr($a) . chr($d));
		}
	}
	for ($d = 48; $d <= 57; $d++) {
		for ($a = 97; $a <= 122; $a++) {
			$try(chr($d) . chr($a));
		}
	}
	for ($a = 97; $a <= 122 && count($out) < $need; $a++) {
		for ($b = 97; $b <= 122 && count($out) < $need; $b++) {
			for ($c = 97; $c <= 122 && count($out) < $need; $c++) {
				$try(chr($a) . chr($b) . chr($c));
			}
		}
	}
	for ($a = 97; $a <= 122 && count($out) < $need; $a++) {
		for ($b = 97; $b <= 122 && count($out) < $need; $b++) {
			for ($c = 97; $c <= 122 && count($out) < $need; $c++) {
				for ($d = 97; $d <= 122 && count($out) < $need; $d++) {
					$try(chr($a) . chr($b) . chr($c) . chr($d));
				}
			}
		}
	}
	for ($i = 0; $i < 65536 && count($out) < $need; $i++) {
		$try('x' . base_convert((string) $i, 10, 36));
	}
	return $out;
}

/**
 * Optional short forms (uppercase acronym, consonant skeleton) — used only if collision-free.
 *
 * @param list<string> $words
 * @return list<string>
 */
function fractal_zip_cfabb_phrase_token_candidates(string $phrase, array $words): array
{
	$cands = array();
	$initials = '';
	foreach ($words as $w) {
		if (preg_match('/^([A-Za-z])/', (string) $w, $m)) {
			$initials .= strtoupper((string) $m[1]);
		}
	}
	if ($initials !== '' && strlen($initials) <= 12) {
		$cands[] = $initials;
	}
	if (count($words) === 2) {
		$w0 = (string) $words[0];
		$w1 = (string) $words[1];
		if (preg_match('/^([A-Za-z])([A-Za-z])/', $w0, $m0) && preg_match('/^([A-Za-z])/', $w1, $m1)) {
			$cands[] = strtoupper((string) $m0[1]) . strtolower((string) $m0[2]) . strtoupper((string) $m1[1]);
		}
	}
	$sk = '';
	foreach ($words as $w) {
		$lower = strtolower((string) $w);
		for ($i = 0, $len = strlen($lower); $i < $len; $i++) {
			$c = $lower[$i];
			if (!str_contains('aeiou', $c)) {
				$sk .= $c;
			}
		}
	}
	if ($sk !== '' && strlen($sk) <= 16) {
		$cands[] = $sk;
	}
	$out = array();
	foreach ($cands as $c) {
		if ($c !== '' && preg_match('/^[A-Za-z0-9]+$/', $c)) {
			$out[$c] = true;
		}
	}
	return array_keys($out);
}

/** Skip wiki table/CSS noise that compresses poorly after tokenization. */
function fractal_zip_cfabb_phrase_is_boilerplate(string $phrase): bool
{
	if ($phrase === '') {
		return true;
	}
	if (preg_match('/\bstyle\s+background\b|\bbackground\s+color\b|\bcolor\s+[0-9a-f]{3,8}\b/i', $phrase)) {
		return true;
	}
	if (preg_match('/\b(align|valign|width|height|colspan|rowspan)\s*=/i', $phrase)) {
		return true;
	}
	if (preg_match('/\b(\w{2,})\s+\1\b/u', $phrase)) {
		return true;
	}
	return false;
}

/**
 * Top-N by save, then sweeper apply order.
 *
 * @param list<array{phrase: string, token?: string, acronym?: string, save?: int}> $entries
 * @return list<array{phrase: string, token?: string, acronym?: string, save?: int}>
 */
function fractal_zip_cfabb_slice_entries(array $entries, int $maxEntries): array
{
	if ($maxEntries <= 0 || $entries === array()) {
		return array();
	}
	usort($entries, static fn (array $a, array $b): int => ((int) ($b['save'] ?? 0) <=> (int) ($a['save'] ?? 0))
		?: ((int) ($b['count'] ?? 0) <=> (int) ($a['count'] ?? 0))
		?: (strlen((string) ($b['phrase'] ?? '')) <=> strlen((string) ($a['phrase'] ?? ''))));
	$entries = array_slice($entries, 0, $maxEntries);
	return fractal_zip_cfabb_order_apply($entries);
}

/** @return int Max entries from env/table cap, or 0 = unlimited. */
function fractal_zip_cfabb_max_entries_cap(): int
{
	$v = getenv('FRACTAL_ZIP_CFABB_MAX_ENTRIES');
	if ($v === false || trim((string) $v) === '') {
		return 0;
	}
	return max(0, (int) $v);
}

/** True when $inner is a contiguous word-aligned substring of $outer. */
function fractal_zip_cfabb_phrase_is_subphrase(string $inner, string $outer): bool
{
	if ($inner === $outer || $inner === '' || strlen($inner) >= strlen($outer)) {
		return false;
	}
	$pat = '/(?<!\w)' . preg_quote($inner, '/') . '(?!\w)/u';
	return preg_match($pat, $outer) === 1;
}

/**
 * Sweeper order: longest phrases first; superstrings before their sub-phrases.
 *
 * @param list<array{phrase: string, token?: string, acronym?: string}> $entries
 * @return list<array{phrase: string, token?: string, acronym?: string}>
 */
function fractal_zip_cfabb_order_apply(array $entries): array
{
	usort($entries, static function (array $a, array $b): int {
		$pa = (string) ($a['phrase'] ?? '');
		$pb = (string) ($b['phrase'] ?? '');
		$la = strlen($pa);
		$lb = strlen($pb);
		if ($la !== $lb) {
			return $lb <=> $la;
		}
		if (fractal_zip_cfabb_phrase_is_subphrase($pa, $pb)) {
			return 1;
		}
		if (fractal_zip_cfabb_phrase_is_subphrase($pb, $pa)) {
			return -1;
		}
		return $pa <=> $pb;
	});
	return $entries;
}

/**
 * Undo order: longest tokens first (avoid expanding token that is substring of another).
 *
 * @param list<array{phrase: string, token?: string, acronym?: string}> $entries
 * @return list<array{phrase: string, token?: string, acronym?: string}>
 */
function fractal_zip_cfabb_order_undo(array $entries): array
{
	usort($entries, static function (array $a, array $b): int {
		$ta = (string) ($a['token'] ?? $a['acronym'] ?? '');
		$tb = (string) ($b['token'] ?? $b['acronym'] ?? '');
		$la = strlen($ta);
		$lb = strlen($tb);
		if ($la !== $lb) {
			return $lb <=> $la;
		}
		if ($ta !== '' && $tb !== '' && str_contains($ta, $tb)) {
			return -1;
		}
		if ($ta !== '' && $tb !== '' && str_contains($tb, $ta)) {
			return 1;
		}
		return $ta <=> $tb;
	});
	return $entries;
}

function fractal_zip_cfabb_sa_enabled(): bool
{
	if (!function_exists('fractal_zip_gpu_substring_sa_available') || !fractal_zip_gpu_substring_sa_available()) {
		return false;
	}
	$v = getenv('FRACTAL_ZIP_CFABB_SA');
	if ($v !== false && trim((string) $v) !== '') {
		$v = strtolower(trim((string) $v));
		return in_array($v, array('1', 'true', 'on', 'yes'), true);
	}
	return true;
}

function fractal_zip_cfabb_sa_max_single_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_CFABB_SA_MAX_SINGLE_BYTES');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(65536, (int) trim((string) $e));
	}
	return 524288;
}

function fractal_zip_cfabb_sa_chunk_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_CFABB_SA_CHUNK_BYTES');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(65536, (int) trim((string) $e));
	}
	return 262144;
}

/**
 * @param array<string, int> $map
 * @return array<string, int>
 */
function fractal_zip_cfabb_filter_byte_phrase_map(
	array $map,
	int $minChars,
	int $maxChars,
	int $minCount
): array {
	$out = array();
	foreach ($map as $phrase => $c) {
		if ($c < $minCount) {
			continue;
		}
		$len = strlen($phrase);
		if ($len < $minChars || $len > $maxChars) {
			continue;
		}
		if (strpbrk($phrase, "<>\0") !== false) {
			continue;
		}
		if (fractal_zip_cfabb_phrase_is_boilerplate($phrase)) {
			continue;
		}
		$out[$phrase] = $c;
	}
	return $out;
}

/**
 * Byte-level repeats via suffix array (8..max_chars), for cfabb phrase pool enrichment.
 *
 * @return array<string, int>
 */
function fractal_zip_cfabb_count_byte_phrases_sa(
	string $text,
	int $minChars = 8,
	int $maxChars = 100,
	int $minCount = 2
): array {
	if (!fractal_zip_cfabb_sa_enabled()) {
		return array();
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_gpu_substring.php';
	$minLen = max(2, $minChars);
	$maxLen = max($minLen, $maxChars);
	$slen = strlen($text);
	if ($slen < $minLen * 2) {
		return array();
	}
	if ($slen <= fractal_zip_cfabb_sa_max_single_bytes()) {
		$map = fractal_zip_gpu_substring_sa_repeat_map($text, $minLen, $maxLen, $minCount);
		if ($map === null || $map === array()) {
			return array();
		}
		return fractal_zip_cfabb_filter_byte_phrase_map($map, $minChars, $maxChars, $minCount);
	}
	$chunkBytes = fractal_zip_cfabb_sa_chunk_bytes();
	$overlap = max(0, $maxChars - 1);
	$step = max($minLen * 2, $chunkBytes - $overlap);
	$candidates = array();
	for ($off = 0; $off < $slen; $off += $step) {
		$slice = substr($text, $off, $chunkBytes);
		if ($slice === '' || strlen($slice) < $minLen * 2) {
			break;
		}
		$map = fractal_zip_gpu_substring_sa_repeat_map($slice, $minLen, $maxLen, $minCount);
		if ($map === null || $map === array()) {
			continue;
		}
		foreach ($map as $phrase => $_c) {
			if (strlen($phrase) < $minChars || strlen($phrase) > $maxChars) {
				continue;
			}
			if (strpbrk($phrase, "<>\0") !== false) {
				continue;
			}
			if (fractal_zip_cfabb_phrase_is_boilerplate($phrase)) {
				continue;
			}
			$candidates[$phrase] = true;
		}
		if ($off + $chunkBytes >= $slen) {
			break;
		}
	}
	if ($candidates === array()) {
		return array();
	}
	$verified = array();
	foreach (array_keys($candidates) as $phrase) {
		$c = substr_count($text, $phrase);
		if ($c >= $minCount) {
			$verified[$phrase] = $c;
		}
	}
	return fractal_zip_cfabb_filter_byte_phrase_map($verified, $minChars, $maxChars, $minCount);
}

/**
 * Count word n-grams (2..max_words) on implicit-space word stream.
 *
 * @return array<string, int>
 */
function fractal_zip_cfabb_count_word_phrases(
	string $text,
	int $minWords = 2,
	int $maxWords = 10,
	int $minChars = 8,
	int $maxChars = 100
): array {
	$words = array();
	foreach (fractal_zip_enwik_text_segment_implicit_space($text) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$w = (string) ($seg['text'] ?? '');
		if ($w === '' || !preg_match('/^[A-Za-z][A-Za-z0-9\x27-]*$/', $w)) {
			continue;
		}
		$words[] = $w;
	}
	$counts = array();
	$n = count($words);
	$maxWords = max($minWords, $maxWords);
	for ($start = 0; $start < $n; $start++) {
		for ($len = $minWords; $len <= $maxWords && $start + $len <= $n; $len++) {
			$phrase = implode(' ', array_slice($words, $start, $len));
			$plen = strlen($phrase);
			if ($plen < $minChars || $plen > $maxChars) {
				continue;
			}
			$counts[$phrase] = ($counts[$phrase] ?? 0) + 1;
		}
	}
	return $counts;
}

/**
 * Count repeated lines (prose lines, no angle brackets) up to max_chars.
 *
 * @return array<string, int>
 */
function fractal_zip_cfabb_count_line_phrases(string $text, int $minChars = 12, int $maxChars = 100, int $minCount = 2): array
{
	$counts = array();
	foreach (explode("\n", $text) as $line) {
		$len = strlen($line);
		if ($len < $minChars || $len > $maxChars || strpbrk($line, '<>') !== false) {
			continue;
		}
		$counts[$line] = ($counts[$line] ?? 0) + 1;
	}
	foreach ($counts as $line => $c) {
		if ($c < $minCount) {
			unset($counts[$line]);
		}
	}
	return $counts;
}

/**
 * @param array<string, int> $a
 * @param array<string, int> $b
 * @return array<string, int>
 */
function fractal_zip_cfabb_merge_counts(array $a, array $b): array
{
	foreach ($b as $k => $c) {
		if (!isset($a[$k]) || $c > $a[$k]) {
			$a[$k] = $c;
		}
	}
	return $a;
}

function fractal_zip_cfabb_sidecar_row_cost(string $phrase, string $token, int $corpusPages = 100000): int
{
	$row = strlen($phrase) + strlen($token) + 2;
	return max(1, (int) ceil($row / max(1, $corpusPages)));
}

function fractal_zip_cfabb_phda9_gate_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_CFABB_PHDA9_GATE');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return in_array($v, array('1', 'true', 'on', 'yes'), true);
}

function fractal_zip_cfabb_phda9_gate_pool_limit(): int
{
	$v = getenv('FRACTAL_ZIP_CFABB_PHDA9_GREEDY_POOL');
	if ($v === false || trim((string) $v) === '') {
		return 0;
	}
	return max(0, (int) $v);
}

function fractal_zip_cfabb_phda9_gate_max_accept(): int
{
	$v = getenv('FRACTAL_ZIP_CFABB_PHDA9_GREEDY_MAX');
	if ($v === false || trim((string) $v) === '') {
		return 0;
	}
	return max(0, (int) $v);
}

function fractal_zip_cfabb_phda9_gate_patience(int $candidateCount = 512): int
{
	$env = getenv('FRACTAL_ZIP_CFABB_PHDA9_GREEDY_PATIENCE');
	if ($env !== false && trim((string) $env) !== '') {
		return max(1, (int) $env);
	}
	// Real phda9 trials are expensive; keep patience tight (not sqrt×10).
	return max(8, min(96, (int) ceil(sqrt(max(1, $candidateCount)) * 2.0)));
}

/** Marginal gate cost for a mined cfabb row (sidecar line or inline token bytes). */
function fractal_zip_cfabb_entry_gate_cost(string $phrase, string $token, int $corpusPages = 100000, ?bool $inlineZeroMeta = null): float
{
	if ($inlineZeroMeta === null) {
		$inlineZeroMeta = fractal_zip_cfabb_inline_enabled();
	}
	if ($inlineZeroMeta) {
		return max(1.0, (float) strlen($token));
	}
	return (float) max(1, strlen($phrase) + strlen($token) + 2);
}

function fractal_zip_cfabb_mine_passes_gate(int $save, string $phrase, string $token, int $corpusPages, ?bool $inlineZeroMeta = null): bool
{
	if ($save <= 0) {
		return false;
	}
	$cost = fractal_zip_cfabb_entry_gate_cost($phrase, $token, $corpusPages, $inlineZeroMeta);
	return fractal_zip_score_passes_ratio_gate((float) $save, $cost);
}

function fractal_zip_cfabb_phda9_filter_on_load(): bool
{
	$v = getenv('FRACTAL_ZIP_CFABB_PHDA9_FILTER_ON_LOAD');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return in_array($v, array('1', 'true', 'on', 'yes'), true);
}

/** phda9 payload bytes (no FZPA wire wrap); cached per process. */
function fractal_zip_cfabb_phda9_plain_bytes(string $plain): int
{
	static $cache = array();
	$key = md5($plain);
	if (isset($cache[$key])) {
		return $cache[$key];
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => fractal_zip_enwik_phda9_english_tool_id(),
		'use_dict' => getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT') !== false
			&& trim((string) getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT')) !== '',
		'timeout_sec' => 0,
		'wire_wrap' => false,
	));
	$bytes = (int) ($r['bytes'] ?? PHP_INT_MAX);
	$cache[$key] = $bytes;
	return $bytes;
}

/**
 * Sealed inner-fold trailer bytes for wiki_lom cfabb tables (matches wire meta_bytes).
 *
 * @param list<array{phrase: string, token?: string, acronym?: string}> $entries
 */
function fractal_zip_cfabb_fold_meta_bytes(array $entries): int
{
	if ($entries === array()) {
		return 0;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
	$acronyms = array();
	foreach ($entries as $row) {
		$acronyms[] = array(
			'phrase' => (string) ($row['phrase'] ?? ''),
			'token' => (string) ($row['token'] ?? $row['acronym'] ?? ''),
			'acronym' => (string) ($row['token'] ?? $row['acronym'] ?? ''),
		);
	}
	$fold = fractal_zip_wiki_lom_inner_fold_blob(array('acronyms_list' => $acronyms));
	$sealed = fractal_zip_enwik_inner_fold_seal_trailer('wiki_lom', $fold);
	return (int) ($sealed['wire_bytes'] ?? strlen($fold));
}

/**
 * @param list<array{start: int, len: int}> $chunk
 */
function fractal_zip_cfabb_plain_xml_build_page_stream(array $chunk, string $blob, array $entries): string
{
	$out = '';
	foreach ($chunk as $p) {
		$page = substr($blob, (int) $p['start'], (int) $p['len']);
		if ($entries !== array()) {
			$page = fractal_zip_cfabb_plain_xml_apply_page($page, $entries);
		}
		$out .= $page;
	}
	return $out;
}

/**
 * phda9 score on the page-XML stream cfabb actually feeds to phda9 (text regions only).
 *
 * @param list<array{start: int, len: int}> $chunk
 * @param list<array{phrase: string, token?: string, acronym?: string}> $entries
 */
function fractal_zip_cfabb_phda9_wire_score_plain_xml(array $chunk, string $blob, array $entries): int
{
	$stream = fractal_zip_cfabb_plain_xml_build_page_stream($chunk, $blob, $entries);
	return fractal_zip_cfabb_phda9_plain_bytes($stream) + fractal_zip_cfabb_fold_meta_bytes($entries);
}

/**
 * @param list<array{phrase: string, token?: string, acronym?: string}> $entries
 */
function fractal_zip_cfabb_phda9_wire_score(string $plainWireText, array $entries, array $opts = array()): int
{
	if (isset($opts['phda9_gate_chunk'], $opts['phda9_gate_blob']) && is_array($opts['phda9_gate_chunk'])) {
		return fractal_zip_cfabb_phda9_wire_score_plain_xml($opts['phda9_gate_chunk'], (string) $opts['phda9_gate_blob'], $entries);
	}
	$text = $entries === array() ? $plainWireText : fractal_zip_cfabb_apply($plainWireText, $entries);
	return fractal_zip_cfabb_phda9_plain_bytes($text) + fractal_zip_cfabb_fold_meta_bytes($entries);
}

/**
 * Greedy keep: add entry only when phda9+fold beats the current accepted set.
 *
 * @param list<array{phrase: string, token?: string, acronym?: string, save?: int, count?: int}> $entries
 * @param array<string, mixed> $opts
 * @return list<array{phrase: string, token: string, count?: int, save?: int}>
 */
function fractal_zip_cfabb_filter_phda9_greedy(string $plainWireText, array $entries, array $opts = array()): array
{
	if ($plainWireText === '' || $entries === array()) {
		return array();
	}
	usort($entries, static function (array $a, array $b): int {
		return ((int) ($b['save'] ?? 0) <=> (int) ($a['save'] ?? 0))
			?: ((int) ($b['count'] ?? 0) <=> (int) ($a['count'] ?? 0))
			?: (strlen((string) ($b['phrase'] ?? '')) <=> strlen((string) ($a['phrase'] ?? '')));
	});
	$poolLimit = (int) ($opts['phda9_greedy_pool'] ?? fractal_zip_cfabb_phda9_gate_pool_limit());
	$maxAccept = (int) ($opts['phda9_greedy_max'] ?? fractal_zip_cfabb_phda9_gate_max_accept());
	if ($poolLimit > 0 && count($entries) > $poolLimit) {
		$entries = array_slice($entries, 0, $poolLimit);
	}
	$patience = (int) ($opts['phda9_greedy_patience'] ?? fractal_zip_cfabb_phda9_gate_patience(count($entries)));
	$plainPrecheck = isset($opts['phda9_gate_chunk'], $opts['phda9_gate_blob']) && is_array($opts['phda9_gate_chunk']);
	$precheckText = (string) ($opts['phda9_precheck_text'] ?? '');
	$gateChunk = $plainPrecheck ? $opts['phda9_gate_chunk'] : array();
	$gateBlob = $plainPrecheck ? (string) $opts['phda9_gate_blob'] : '';
	/** @var list<array{phrase: string, token: string, count?: int, save?: int}> $accepted */
	$accepted = array();
	$bestScore = fractal_zip_cfabb_phda9_wire_score($plainWireText, array(), $opts);
	$misses = 0;
	$ratioThreshold = fractal_zip_score_ratio_threshold();
	foreach ($entries as $row) {
		if ($maxAccept > 0 && count($accepted) >= $maxAccept) {
			break;
		}
		$phrase = (string) ($row['phrase'] ?? '');
		$token = (string) ($row['token'] ?? $row['acronym'] ?? '');
		if ($phrase === '' || $token === '') {
			continue;
		}
		$estSave = (int) ($row['save'] ?? 0);
		$gateCost = fractal_zip_cfabb_entry_gate_cost($phrase, $token);
		if ($estSave > 0 && ($estSave / max(1.0, $gateCost)) <= $ratioThreshold) {
			break;
		}
		$candidate = array(
			'phrase' => $phrase,
			'token' => $token,
			'count' => (int) ($row['count'] ?? 0),
			'save' => (int) ($row['save'] ?? 0),
		);
		$trial = array_merge($accepted, array($candidate));
		if ($plainPrecheck || $precheckText !== '') {
			$plainDelta = (int) ($row['count'] ?? 0) * (strlen($phrase) - strlen($token));
			if ($plainDelta <= 0) {
				$misses++;
				if ($misses >= $patience) {
					break;
				}
				continue;
			}
		}
		$score = fractal_zip_cfabb_phda9_wire_score($plainWireText, $trial, $opts);
		if ($score < $bestScore) {
			$accepted = $trial;
			$bestScore = $score;
			$misses = 0;
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[cfabb] phda9 gate accept #' . count($accepted)
					. ' score=' . number_format($bestScore) . ' token=' . $token . "\n");
			}
		} else {
			$misses++;
			if ($misses >= $patience) {
				break;
			}
		}
	}
	return fractal_zip_cfabb_order_apply($accepted);
}

/**
 * @param list<string> $usedTokens
 */
function fractal_zip_cfabb_pick_token(
	string $corpus,
	array $usedTokens,
	array $phraseCandidates = array(),
	array $pool = array(),
	int &$poolIdx = 0
): string {
	$best = null;
	$bestLen = PHP_INT_MAX;
	foreach ($phraseCandidates as $cand) {
		if (!fractal_zip_cfabb_token_usable((string) $cand, $corpus, $usedTokens)) {
			continue;
		}
		$len = strlen((string) $cand);
		if ($len < $bestLen) {
			$bestLen = $len;
			$best = (string) $cand;
		}
	}
	if ($best !== null) {
		return $best;
	}
	while ($poolIdx < count($pool)) {
		$cand = (string) $pool[$poolIdx++];
		if (fractal_zip_cfabb_token_usable($cand, $corpus, $usedTokens)) {
			return $cand;
		}
	}
	return '';
}

/**
 * @param array<string, mixed> $opts
 * @return list<array{phrase: string, token: string, count: int, save: int}>
 */
function fractal_zip_cfabb_mine(string $corpusText, array $opts = array()): array
{
	$def = fractal_zip_cfabb_default_opts();
	$minWords = (int) ($opts['min_words'] ?? $def['min_words']);
	$maxWords = (int) ($opts['max_words'] ?? $def['max_words']);
	$minChars = (int) ($opts['min_chars'] ?? $def['min_chars']);
	$maxChars = (int) ($opts['max_chars'] ?? $def['max_chars']);
	$minCount = (int) ($opts['min_count'] ?? $def['min_count']);
	$maxEntries = (int) ($opts['max_entries'] ?? $def['max_entries']);
	$corpusPages = max(1, (int) ($opts['corpus_pages'] ?? 100000));
	$inlineZeroMeta = array_key_exists('inline_zero_meta', $opts)
		? (bool) $opts['inline_zero_meta']
		: fractal_zip_cfabb_inline_enabled();
	$useScoreGate = !array_key_exists('score_gate', $opts) || !empty($opts['score_gate']);

	$wordCounts = fractal_zip_cfabb_count_word_phrases($corpusText, $minWords, $maxWords, $minChars, $maxChars);
	$lineCounts = fractal_zip_cfabb_count_line_phrases($corpusText, max(12, $minChars), $maxChars, $minCount);
	$byteCounts = fractal_zip_cfabb_count_byte_phrases_sa($corpusText, $minChars, $maxChars, $minCount);
	$counts = fractal_zip_cfabb_merge_counts($wordCounts, $lineCounts);
	$counts = fractal_zip_cfabb_merge_counts($counts, $byteCounts);

	$rows = array();
	foreach ($counts as $phrase => $c) {
		if ($c < $minCount || fractal_zip_cfabb_phrase_is_boilerplate($phrase)) {
			continue;
		}
		$words = preg_split('/\s+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY);
		if (!is_array($words)) {
			continue;
		}
		$cands = fractal_zip_cfabb_phrase_token_candidates($phrase, $words);
		$bestLen = PHP_INT_MAX;
		foreach ($cands as $cand) {
			if (fractal_zip_cfabb_corpus_has_token($corpusText, $cand)) {
				continue;
			}
			$bestLen = min($bestLen, strlen($cand));
		}
		$estTokLen = $bestLen < PHP_INT_MAX ? $bestLen : 2;
		$save = $c * (strlen($phrase) - $estTokLen)
			- fractal_zip_cfabb_sidecar_row_cost($phrase, str_repeat('a', $estTokLen), $corpusPages);
		if (!$useScoreGate) {
			if ($save <= 0) {
				continue;
			}
		} elseif (!fractal_zip_cfabb_mine_passes_gate($save, $phrase, str_repeat('a', $estTokLen), $corpusPages, $inlineZeroMeta)) {
			continue;
		}
		$rows[] = array(
			'phrase' => $phrase,
			'count' => $c,
			'save' => $save,
			'cands' => $cands,
		);
	}

	usort($rows, static fn (array $a, array $b): int => ($b['save'] <=> $a['save'])
		?: ($b['count'] <=> $a['count'])
		?: (strlen($b['phrase']) <=> strlen($a['phrase'])));

	$poolNeed = $maxEntries > 0 ? ($maxEntries * 3) : max(512, count($rows) * 2);
	$pool = fractal_zip_cfabb_token_pool($poolNeed, $corpusText);
	$poolIdx = 0;
	$usedTokens = array();
	$usedPhrases = array();
	$out = array();

	foreach ($rows as $row) {
		$phrase = (string) $row['phrase'];
		if (isset($usedPhrases[$phrase])) {
			continue;
		}
		$token = fractal_zip_cfabb_pick_token(
			$corpusText,
			$usedTokens,
			is_array($row['cands'] ?? null) ? $row['cands'] : array(),
			$pool,
			$poolIdx
		);
		if ($token === '') {
			continue;
		}
		$save = (int) $row['count'] * (strlen($phrase) - strlen($token))
			- fractal_zip_cfabb_sidecar_row_cost($phrase, $token, $corpusPages);
		if (!$useScoreGate) {
			if ($save <= 0) {
				continue;
			}
		} elseif (!fractal_zip_cfabb_mine_passes_gate($save, $phrase, $token, $corpusPages, $inlineZeroMeta)) {
			break;
		}
		$out[] = array(
			'phrase' => $phrase,
			'token' => $token,
			'count' => (int) $row['count'],
			'save' => $save,
		);
		$usedTokens[] = $token;
		$usedPhrases[$phrase] = true;
		if ($maxEntries > 0 && count($out) >= $maxEntries) {
			break;
		}
	}
	$gateText = (string) ($opts['phda9_gate_text'] ?? '');
	if ($gateText !== '' && fractal_zip_cfabb_phda9_gate_enabled()) {
		return fractal_zip_cfabb_filter_phda9_greedy($gateText, $out, array_merge($opts, array(
			'phda9_greedy_max' => (int) ($opts['phda9_greedy_max'] ?? ($maxEntries > 0 ? $maxEntries : 0)),
		)));
	}
	return fractal_zip_cfabb_order_apply($out);
}

function fractal_zip_cfabb_mine_enwik_blob(string $enwikBlob, array $opts = array()): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$prose = fractal_zip_enwik_collect_text_body_prose_stream($enwikBlob);
	if ($prose === '') {
		return array();
	}
	$split = enwik_split_page_refs($enwikBlob);
	if ($split !== null && !isset($opts['corpus_pages'])) {
		$opts['corpus_pages'] = count($split['pages']);
	}
	return fractal_zip_cfabb_mine($prose, $opts);
}

/** Mine on raw preserve-text (plain phda9_xml path; no wiki_lom decode). */
function fractal_zip_cfabb_mine_enwik_plain_preserve_text(string $enwikBlob, array $opts = array()): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$split = enwik_split_page_refs($enwikBlob);
	if ($split === null) {
		return array();
	}
	$pages = (int) ($opts['pages'] ?? 0);
	$n = $pages > 0 ? min($pages, count($split['pages'])) : count($split['pages']);
	$chunk = array();
	$text = '';
	for ($i = 0; $i < $n; $i++) {
		$p = $split['pages'][$i];
		$chunk[] = array('start' => (int) $p['start'], 'len' => (int) $p['len']);
		$text .= fractal_zip_enwik_extract_page_preserve_text(substr($enwikBlob, (int) $p['start'], (int) $p['len']));
	}
	if (!isset($opts['corpus_pages'])) {
		$opts['corpus_pages'] = count($split['pages']);
	}
	$out = fractal_zip_cfabb_mine($text, $opts);
	if ($out === array() || !fractal_zip_cfabb_phda9_gate_enabled()) {
		return $out;
	}
	return fractal_zip_cfabb_filter_phda9_greedy($text, $out, array_merge($opts, array(
		'phda9_gate_chunk' => $chunk,
		'phda9_gate_blob' => $enwikBlob,
	)));
}

/** Mine on entity-decoded preserve-text (wiki_lom wire domain). */
function fractal_zip_cfabb_mine_enwik_preserve_text(string $enwikBlob, array $opts = array()): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
	$split = enwik_split_page_refs($enwikBlob);
	if ($split === null) {
		return array();
	}
	$pages = (int) ($opts['pages'] ?? 0);
	$n = $pages > 0 ? min($pages, count($split['pages'])) : count($split['pages']);
	$text = '';
	for ($i = 0; $i < $n; $i++) {
		$p = $split['pages'][$i];
		$text .= fractal_zip_enwik_extract_page_preserve_text(substr($enwikBlob, (int) $p['start'], (int) $p['len']));
	}
	$decoded = fractal_zip_wiki_lom_entity_decode_text($text);
	if (!isset($opts['corpus_pages'])) {
		$opts['corpus_pages'] = count($split['pages']);
	}
	if (!isset($opts['phda9_gate_text']) && fractal_zip_cfabb_phda9_gate_enabled()) {
		$opts['phda9_gate_text'] = fractal_zip_wiki_lom_phda9_wire_escape($decoded);
	}
	return fractal_zip_cfabb_mine($decoded, $opts);
}

/** @param list<array{phrase: string, token: string}> $entries */
function fractal_zip_cfabb_apply(string $text, array $entries): string
{
	foreach (fractal_zip_cfabb_order_apply($entries) as $entry) {
		$phrase = (string) ($entry['phrase'] ?? '');
		$token = (string) ($entry['token'] ?? $entry['acronym'] ?? '');
		if ($phrase === '' || $token === '') {
			continue;
		}
		$pat = '/(?<!\w)' . preg_quote($phrase, '/') . '(?!\w)/u';
		$text = preg_replace($pat, $token, $text) ?? $text;
	}
	return $text;
}

/** @param list<array{phrase: string, token: string}> $entries */
function fractal_zip_cfabb_undo(string $text, array $entries): string
{
	foreach (fractal_zip_cfabb_order_undo($entries) as $entry) {
		$phrase = (string) ($entry['phrase'] ?? '');
		$token = (string) ($entry['token'] ?? $entry['acronym'] ?? '');
		if ($phrase === '' || $token === '') {
			continue;
		}
		$pat = '/(?<!\w)' . preg_quote($token, '/') . '(?!\w)/u';
		$text = preg_replace($pat, $phrase, $text) ?? $text;
	}
	return $text;
}

/** @param list<array{phrase: string, token: string, count?: int, save?: int}> $entries */
function fractal_zip_cfabb_pack_table(array $entries): string
{
	$buf = FRACTAL_ZIP_CFABB_MAGIC;
	$buf .= fractal_zip_enwik_encode_varint_u32(count($entries));
	foreach ($entries as $row) {
		$phrase = (string) ($row['phrase'] ?? '');
		$token = (string) ($row['token'] ?? '');
		$buf .= fractal_zip_enwik_encode_varint_u32(strlen($phrase)) . $phrase;
		$buf .= fractal_zip_enwik_encode_varint_u32(strlen($token)) . $token;
	}
	return $buf;
}

/** @return list<array{phrase: string, token: string}> */
function fractal_zip_cfabb_unpack_table(string $blob): array
{
	if (!str_starts_with($blob, FRACTAL_ZIP_CFABB_MAGIC)) {
		throw new RuntimeException('cfabb: invalid table magic');
	}
	$pos = strlen(FRACTAL_ZIP_CFABB_MAGIC);
	$pair = fractal_zip_enwik_decode_varint_u32($blob, $pos);
	if ($pair === null) {
		throw new RuntimeException('cfabb: truncated table header');
	}
	$n = (int) $pair[0];
	$pos = (int) $pair[1];
	$out = array();
	for ($i = 0; $i < $n; $i++) {
		$pl = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		if ($pl === null) {
			throw new RuntimeException('cfabb: truncated phrase len');
		}
		$phraseLen = (int) $pl[0];
		$pos = (int) $pl[1];
		if ($pos + $phraseLen > strlen($blob)) {
			throw new RuntimeException('cfabb: phrase overrun');
		}
		$phrase = substr($blob, $pos, $phraseLen);
		$pos += $phraseLen;
		$tl = fractal_zip_enwik_decode_varint_u32($blob, $pos);
		if ($tl === null) {
			throw new RuntimeException('cfabb: truncated token len');
		}
		$tokenLen = (int) $tl[0];
		$pos = (int) $tl[1];
		if ($pos + $tokenLen > strlen($blob)) {
			throw new RuntimeException('cfabb: token overrun');
		}
		$token = substr($blob, $pos, $tokenLen);
		$pos += $tokenLen;
		$out[] = array('phrase' => $phrase, 'token' => $token);
	}
	return $out;
}

/** @param list<array{phrase: string, token: string}> $entries */
function fractal_zip_cfabb_save_json(string $path, array $entries): void
{
	$json = json_encode(array('entries' => $entries), JSON_UNESCAPED_UNICODE);
	if (!is_string($json) || file_put_contents($path, $json) === false) {
		throw new RuntimeException('cfabb: json write failed: ' . $path);
	}
}

/** @return list<array{phrase: string, token: string}> */
function fractal_zip_cfabb_load_json(?string $path = null): array
{
	if ($path === null || $path === '') {
		$path = getenv('FRACTAL_ZIP_CFABB_TABLE');
		$path = ($path !== false && trim((string) $path) !== '') ? trim((string) $path) : '';
	}
	if ($path === '' || !is_file($path)) {
		return array();
	}
	$raw = file_get_contents($path);
	if (!is_string($raw)) {
		return array();
	}
	if (str_starts_with($raw, FRACTAL_ZIP_CFABB_MAGIC)) {
		return fractal_zip_cfabb_unpack_table($raw);
	}
	$meta = json_decode($raw, true);
	if (!is_array($meta)) {
		return array();
	}
	$entries = is_array($meta['entries'] ?? null) ? $meta['entries'] : array();
	$out = array();
	foreach ($entries as $row) {
		if (!is_array($row)) {
			continue;
		}
		$phrase = (string) ($row['phrase'] ?? '');
		$token = (string) ($row['token'] ?? $row['acronym'] ?? '');
		if ($phrase !== '' && $token !== '') {
			$out[] = array(
				'phrase' => $phrase,
				'token' => $token,
				'count' => (int) ($row['count'] ?? 0),
				'save' => (int) ($row['save'] ?? 0),
			);
		}
	}
	$cap = fractal_zip_cfabb_max_entries_cap();
	if ($cap > 0 && count($out) > $cap) {
		$out = fractal_zip_cfabb_slice_entries($out, $cap);
	}
	return $out;
}

function fractal_zip_cfabb_default_table_path(int $pages = 0): string
{
	$repo = dirname(__FILE__);
	$suffix = $pages > 0 ? ('_' . $pages . 'p') : '_full';
	return $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_cfabb_table' . $suffix . '.json';
}
