<?php
declare(strict_types=1);

/**
 * phda9 / parallel_paq dictionary tokenization (0xFD escape wire).
 *
 * @see tools/parallel_paq/tokenizer.c
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';

const FRACTAL_ZIP_PHDA9_TOK_ESC = "\xFD";
const FRACTAL_ZIP_PHDA9_TOK_ESC_WORD = "\x00";
// Cap-fold escape (dict word with first char uppercased). Never emitted in the
// v1 wire, so decoders handling it stay backward compatible. NOT understood by
// tools/parallel_paq/parallel_phda9 — only use where fz itself detokenizes.
const FRACTAL_ZIP_PHDA9_TOK_ESC_CAPWORD = "\x01";
const FRACTAL_ZIP_PHDA9_TOK_ESC_LITERAL = "\xFF";

function fractal_zip_phda9_tok_is_word_byte(string $c): bool
{
	if ($c === '') {
		return false;
	}
	$b = ord($c[0]);
	return ($b >= ord('a') && $b <= ord('z'))
		|| ($b >= ord('A') && $b <= ord('Z'))
		|| ($b >= ord('0') && $b <= ord('9'))
		|| $c === '_'
		|| $c === '\'';
}

/** @return array{0: int, 1: int}|null */
function fractal_zip_phda9_tok_decode_varint(string $in, int $pos): ?array
{
	$n = strlen($in);
	$v = 0;
	$shift = 0;
	while ($pos < $n) {
		$b = ord($in[$pos]);
		$pos++;
		$v |= ($b & 0x7f) << $shift;
		if (($b & 0x80) === 0) {
			return array($v, $pos);
		}
		$shift += 7;
		if ($shift > 28) {
			return null;
		}
	}
	return null;
}

function fractal_zip_phda9_tok_encode_varint(int $v): string
{
	$out = '';
	while ($v >= 0x80) {
		$out .= chr(($v & 0x7f) | 0x80);
		$v >>= 7;
	}
	$out .= chr($v);
	return $out;
}

/**
 * @param list<string> $words dictionary order = word index
 * @return list<list<int>> bucket[first_byte] = list of word indices, ascending strlen
 */
function fractal_zip_phda9_tok_index_build(array $words): array
{
	$buckets = array();
	for ($i = 0; $i < 256; $i++) {
		$buckets[$i] = array();
	}
	foreach ($words as $wi => $w) {
		$w = (string) $w;
		if ($w === '') {
			continue;
		}
		$fc = ord($w[0]);
		$buckets[$fc][] = $wi;
	}
	foreach ($buckets as $fc => $list) {
		if (count($list) <= 1) {
			continue;
		}
		usort($list, static function (int $a, int $b) use ($words): int {
			$la = strlen((string) ($words[$a] ?? ''));
			$lb = strlen((string) ($words[$b] ?? ''));
			return $la <=> $lb;
		});
		$buckets[$fc] = $list;
	}
	return $buckets;
}

function fractal_zip_phda9_tok_word_match(array $words, int $wi, string $in, int $pos): int
{
	$w = (string) ($words[$wi] ?? '');
	$wl = strlen($w);
	if ($wl === 0 || $pos + $wl > strlen($in)) {
		return 0;
	}
	if (substr($in, $pos, $wl) !== $w) {
		return 0;
	}
	if ($pos + $wl < strlen($in) && fractal_zip_phda9_tok_is_word_byte($in[$pos + $wl])) {
		return 0;
	}
	return $wl;
}

/**
 * @param list<string> $words
 */
/**
 * Native tokenizer helper (tools/parallel_paq/fz_tokenize) — same wire and
 * algorithm as the PHP below, ~50-100x faster. Used transparently for large
 * inputs; PHP remains the reference implementation and the fallback.
 */
function fractal_zip_phda9_tok_native_bin(): ?string
{
	static $bin = false;
	if ($bin !== false) {
		return $bin;
	}
	$e = getenv('FRACTAL_ZIP_TOKENIZE_NATIVE');
	if ($e !== false && in_array(strtolower(trim((string) $e)), array('0', 'off', 'false'), true)) {
		$bin = null;
		return $bin;
	}
	$p = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'parallel_paq' . DIRECTORY_SEPARATOR . 'fz_tokenize';
	$bin = (is_file($p) && is_executable($p)) ? $p : null;
	return $bin;
}

function fractal_zip_phda9_tok_native_min_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZE_NATIVE_MIN_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	// Native fz_tokenize is byte-identical to PHP; use at all sizes when built (0 = always).
	return 0;
}

/**
 * Native path requires the word list to survive the dict-file round trip with
 * identical indices (dict_write_file trims/skips lines and enforces caps).
 * @param list<string> $words
 */
function fractal_zip_phda9_tok_native_words_ok(array $words): bool
{
	if ($words === array() || count($words) > fractal_zip_phda9_dict_max_words()) {
		return false;
	}
	$body = strlen(fractal_zip_phda9_dict_header_bytes());
	foreach ($words as $w) {
		if (!is_string($w) || $w === '' || $w === '80000' || $w !== trim($w)) {
			return false;
		}
		$body += strlen($w) + 2;
	}
	return $body <= fractal_zip_phda9_dict_max_bytes();
}

/**
 * @param 'enc'|'dec' $mode
 * @param list<string> $words
 */
function fractal_zip_phda9_tok_native_run(string $mode, string $in, array $words): ?string
{
	$bin = fractal_zip_phda9_tok_native_bin();
	if ($bin === null || !fractal_zip_phda9_tok_native_words_ok($words)) {
		return null;
	}
	$dictPath = fractal_zip_phda9_dict_inline_temp_path($words);
	$proc = proc_open(
		array($bin, $mode, $dictPath),
		array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('file', '/dev/null', 'w')),
		$pipes
	);
	if (!is_resource($proc)) {
		return null;
	}
	$inFile = null;
	if (strlen($in) > (1 << 22)) {
		// avoid pipe deadlock on large inputs: feed stdin from a temp file
		fclose($pipes[0]);
		proc_close($proc);
		$inFile = tempnam(sys_get_temp_dir(), 'fztokin');
		file_put_contents($inFile, $in);
		$proc = proc_open(
			array($bin, $mode, $dictPath),
			array(0 => array('file', $inFile, 'r'), 1 => array('pipe', 'w'), 2 => array('file', '/dev/null', 'w')),
			$pipes
		);
		if (!is_resource($proc)) {
			@unlink($inFile);
			return null;
		}
	} else {
		fwrite($pipes[0], $in);
		fclose($pipes[0]);
	}
	$out = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	$rc = proc_close($proc);
	if ($inFile !== null) {
		@unlink($inFile);
	}
	if ($rc !== 0 || !is_string($out)) {
		return null;
	}
	return $out;
}

function fractal_zip_phda9_tokenize(string $plain, array $words, bool $caps = false): string
{
	if ($plain === '' || $words === array()) {
		return $plain;
	}
	if (strlen($plain) >= fractal_zip_phda9_tok_native_min_bytes()) {
		$native = fractal_zip_phda9_tok_native_run($caps ? 'encc' : 'enc', $plain, $words);
		if ($native !== null) {
			return $native;
		}
	}
	$index = fractal_zip_phda9_tok_index_build($words);
	$capIndex = null;
	if ($caps) {
		$capIndex = array();
		foreach ($words as $wi => $w) {
			if (!isset($capIndex[$w])) {
				$capIndex[$w] = $wi;
			}
		}
	}
	$n = strlen($plain);
	$out = '';
	for ($i = 0; $i < $n;) {
		$matched = false;
		$atWordStart = ($i === 0) || !fractal_zip_phda9_tok_is_word_byte($plain[$i - 1]);
		if ($atWordStart && fractal_zip_phda9_tok_is_word_byte($plain[$i])) {
			$fc = ord($plain[$i]);
			foreach ($index[$fc] as $wi) {
				$ml = fractal_zip_phda9_tok_word_match($words, $wi, $plain, $i);
				if ($ml > 0) {
					$out .= FRACTAL_ZIP_PHDA9_TOK_ESC . FRACTAL_ZIP_PHDA9_TOK_ESC_WORD
						. fractal_zip_phda9_tok_encode_varint($wi);
					$i += $ml;
					$matched = true;
					break;
				}
			}
			if (!$matched && $capIndex !== null && $fc >= 65 && $fc <= 90) {
				$j = $i + 1;
				while ($j < $n && fractal_zip_phda9_tok_is_word_byte($plain[$j])) {
					$j++;
				}
				$folded = chr($fc + 32) . substr($plain, $i + 1, $j - $i - 1);
				if (isset($capIndex[$folded])) {
					$out .= FRACTAL_ZIP_PHDA9_TOK_ESC . FRACTAL_ZIP_PHDA9_TOK_ESC_CAPWORD
						. fractal_zip_phda9_tok_encode_varint($capIndex[$folded]);
					$i = $j;
					$matched = true;
				}
			}
		}
		if (!$matched) {
			$b = $plain[$i];
			$i++;
			if ($b === FRACTAL_ZIP_PHDA9_TOK_ESC) {
				$out .= FRACTAL_ZIP_PHDA9_TOK_ESC . FRACTAL_ZIP_PHDA9_TOK_ESC_LITERAL;
			} else {
				$out .= $b;
			}
		}
	}
	return $out;
}

/**
 * @param list<string> $words
 */
function fractal_zip_phda9_detokenize(string $tok, array $words): string
{
	if ($tok === '' || $words === array()) {
		return $tok;
	}
	if (strlen($tok) >= fractal_zip_phda9_tok_native_min_bytes()) {
		$native = fractal_zip_phda9_tok_native_run('dec', $tok, $words);
		if ($native !== null) {
			return $native;
		}
	}
	$n = strlen($tok);
	$out = '';
	for ($i = 0; $i < $n;) {
		if ($tok[$i] === FRACTAL_ZIP_PHDA9_TOK_ESC && $i + 1 < $n) {
			if ($tok[$i + 1] === FRACTAL_ZIP_PHDA9_TOK_ESC_WORD
				|| $tok[$i + 1] === FRACTAL_ZIP_PHDA9_TOK_ESC_CAPWORD) {
				$cap = ($tok[$i + 1] === FRACTAL_ZIP_PHDA9_TOK_ESC_CAPWORD);
				$i += 2;
				$dv = fractal_zip_phda9_tok_decode_varint($tok, $i);
				if ($dv === null) {
					throw new RuntimeException('phda9_detokenize: bad varint');
				}
				$wi = (int) $dv[0];
				$i = (int) $dv[1];
				if ($wi < 0 || $wi >= count($words)) {
					throw new RuntimeException('phda9_detokenize: word index OOB');
				}
				$w = (string) $words[$wi];
				$out .= $cap ? ucfirst($w) : $w;
				continue;
			}
			if ($tok[$i + 1] === FRACTAL_ZIP_PHDA9_TOK_ESC_LITERAL) {
				$out .= FRACTAL_ZIP_PHDA9_TOK_ESC;
				$i += 2;
				continue;
			}
		}
		$out .= $tok[$i];
		$i++;
	}
	return $out;
}

function fractal_zip_phda9_dict_inline_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return in_array($v, array('1', 'true', 'on', 'yes'), true);
}

function fractal_zip_phda9_dict_inline_active(): bool
{
	$v = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE');
	return $v !== false && trim((string) $v) !== '' && trim((string) $v) !== '0';
}

/** @param list<string> $words */
function fractal_zip_phda9_dict_inline_cache_store(array $words): void
{
	$GLOBALS['fractal_zip_phda9_dict_inline_vocab'] = $words;
}

/** @return list<string> */
function fractal_zip_phda9_dict_inline_cache_load(): array
{
	$cached = $GLOBALS['fractal_zip_phda9_dict_inline_vocab'] ?? null;
	return is_array($cached) ? $cached : array();
}

function fractal_zip_phda9_dict_inline_mine_text_store(string $plain): void
{
	if ($plain !== '') {
		$GLOBALS['fractal_zip_phda9_dict_inline_mine_text'] = $plain;
	}
}

function fractal_zip_phda9_dict_inline_mine_text_load(): string
{
	$t = $GLOBALS['fractal_zip_phda9_dict_inline_mine_text'] ?? '';
	return is_string($t) ? $t : '';
}

/** Compact tokenized vocab blob (FZPV\\x01 + varint word list). */
function fractal_zip_phda9_dict_inline_vocab_blob(array $words): string
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
	return "FZPV\x01" . fractal_zip_enwik_inner_fold_encode_vocab($words);
}

/** @return list<string> */
function fractal_zip_phda9_dict_inline_vocab_from_blob(string $blob): array
{
	if ($blob === '') {
		return array();
	}
	if (str_starts_with($blob, "FZPV\x01")) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		return fractal_zip_enwik_inner_fold_decode_vocab(substr($blob, 5));
	}
	$out = array();
	$seen = array();
	foreach (preg_split("/\r\n|\n|\r/", $blob) ?: array() as $line) {
		$w = trim((string) $line);
		if ($w === '' || $w === '80000' || isset($seen[$w])) {
			continue;
		}
		$seen[$w] = true;
		$out[] = $w;
	}
	return $out;
}

/**
 * Compressor for pre-tokenized (0xFD) streams — phda9_no_lstm segfaults on these at scale.
 */
function fractal_zip_phda9_dict_inline_resolve_tool(string $requestedTool): string
{
	if (!fractal_zip_phda9_dict_inline_enabled() || fractal_zip_phda9_dict_inline_mode() !== 'tokenize') {
		return $requestedTool;
	}
	$override = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_TOOL');
	if ($override !== false && trim((string) $override) !== '') {
		return strtolower(trim((string) $override));
	}
	$preferPar = getenv('FRACTAL_ZIP_PHDA9_PREFER_PARALLEL');
	if ($preferPar !== false && in_array(strtolower(trim((string) $preferPar)), array('1', 'true', 'on', 'yes'), true)) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
		if (fractal_zip_paq_discover_executable('parallel_phda9') !== null) {
			return 'parallel_phda9';
		}
	}
	return $requestedTool;
}

function fractal_zip_phda9_dict_inline_refine_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_REFINE');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	return !in_array(strtolower(trim((string) $v)), array('0', 'false', 'off', 'no'), true);
}

function fractal_zip_phda9_dict_inline_refine_trials(): int
{
	$v = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_REFINE_TRIALS');
	if ($v === false || trim((string) $v) === '') {
		return 24;
	}
	return max(0, (int) $v);
}

/**
 * Real phda9 compress trials: seed dict + greedy phrase/subword adds.
 *
 * @param list<string> $seedWords
 * @return list<string>
 */
function fractal_zip_phda9_dict_inline_refine_vocab(string $plain, array $seedWords): array
{
	$trials = fractal_zip_phda9_dict_inline_refine_trials();
	if ($trials <= 0 || $plain === '' || $seedWords === array()) {
		return $seedWords;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict_mine.php';
	$candidates = array();
	foreach (fractal_zip_phda9_dict_mine_word_ngrams($plain, 3, 8192) as $c) {
		$candidates[] = $c;
	}
	foreach (fractal_zip_phda9_dict_mine_subword_pieces($plain, 3, 8192) as $c) {
		$candidates[] = $c;
	}
	$scored = fractal_zip_phda9_dict_score_candidates($plain, $candidates);
	$ref = fractal_zip_phda9_dict_refine_with_compress($plain, $seedWords, $scored, $trials);
	return $ref['tokens'];
}

/**
 * Vocabulary for inline tokenization (read-only source; not shipped to phda9 CLI).
 *
 * @return list<string>
 */
function fractal_zip_phda9_dict_inline_vocab(string $mineText = ''): array
{
	$cached = fractal_zip_phda9_dict_inline_cache_load();
	if ($cached !== array()) {
		return $cached;
	}
	/** @var list<string> $seed */
	$seed = array();
	$blobPath = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_VOCAB_BLOB');
	if ($blobPath !== false && trim((string) $blobPath) !== '' && is_file(trim((string) $blobPath))) {
		$seed = fractal_zip_phda9_dict_inline_vocab_from_blob((string) file_get_contents(trim((string) $blobPath)));
	} else {
		$path = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_VOCAB');
		if ($path === false || trim((string) $path) === '') {
			$path = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
		}
		if ($path !== false && trim((string) $path) !== '' && is_file(trim((string) $path))) {
			$seed = fractal_zip_phda9_dict_read_words(trim((string) $path));
		}
	}
	if ($mineText === '') {
		$mineText = fractal_zip_phda9_dict_inline_mine_text_load();
	}
	$mine = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_MINE');
	if ($mine !== false && trim((string) $mine) !== '' && trim((string) $mine) !== '0' && $mineText !== '') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_dict_phda9_inner.php';
		$seed = fractal_zip_enwik_phda9_inner_frozen_vocab($mineText);
	}
	if ($mineText !== '' && fractal_zip_phda9_dict_inline_refine_enabled() && $seed !== array()) {
		$seed = fractal_zip_phda9_dict_inline_refine_vocab($mineText, $seed);
	}
	if ($seed !== array()) {
		fractal_zip_phda9_dict_inline_cache_store($seed);
	}
	return $seed;
}

/** External dict file bytes that inline mode avoids shipping to the compressor CLI. */
function fractal_zip_phda9_dict_external_file_bytes(): int
{
	if (fractal_zip_phda9_dict_inline_enabled()) {
		return 0;
	}
	$path = fractal_zip_phda9_dict_path_from_env();
	if ($path === null) {
		return 0;
	}
	$sz = filesize($path);
	return $sz === false ? 0 : (int) $sz;
}

function fractal_zip_phda9_dict_inline_mode(): string
{
	$v = getenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_MODE');
	if ($v === false || trim((string) $v) === '') {
		return 'temp';
	}
	$v = strtolower(trim((string) $v));
	return in_array($v, array('temp', 'tokenize'), true) ? $v : 'temp';
}

function fractal_zip_phda9_dict_inline_temp_path(array $words): string
{
	$hash = hash('xxh128', implode("\n", $words));
	$path = sys_get_temp_dir() . '/fz_phda9_inline_' . $hash . '.dict';
	if (!is_file($path)) {
		fractal_zip_phda9_dict_write_file($words, $path);
	}
	return $path;
}

/**
 * @return array{plain: string, tokenized: bool, words: int, dict_path: ?string, temp_dict: bool}
 */
function fractal_zip_phda9_dict_inline_prepare_plain(string $plain): array
{
	$empty = array(
		'plain' => $plain,
		'tokenized' => false,
		'words' => 0,
		'dict_path' => null,
		'temp_dict' => false,
	);
	if (!fractal_zip_phda9_dict_inline_enabled()) {
		return $empty;
	}
	$words = fractal_zip_phda9_dict_inline_vocab($plain);
	if ($words === array()) {
		return $empty;
	}
	fractal_zip_phda9_dict_inline_mine_text_store($plain);
	fractal_zip_phda9_dict_inline_cache_store($words);
	if (fractal_zip_phda9_dict_inline_mode() === 'tokenize') {
		$tok = fractal_zip_phda9_tokenize($plain, $words);
		return array(
			'plain' => $tok,
			'tokenized' => true,
			'words' => count($words),
			'dict_path' => null,
			'temp_dict' => false,
		);
	}
	$dictPath = fractal_zip_phda9_dict_inline_temp_path($words);
	return array(
		'plain' => $plain,
		'tokenized' => false,
		'words' => count($words),
		'dict_path' => $dictPath,
		'temp_dict' => true,
	);
}

function fractal_zip_phda9_dict_inline_restore_plain(string $maybePlain): string
{
	if (fractal_zip_phda9_dict_inline_mode() !== 'tokenize') {
		return $maybePlain;
	}
	if (!fractal_zip_phda9_dict_inline_active() && !fractal_zip_phda9_dict_inline_enabled()) {
		return $maybePlain;
	}
	if (!fractal_zip_phda9_tok_stream_has_word_escapes($maybePlain)) {
		return $maybePlain;
	}
	$words = fractal_zip_phda9_dict_inline_cache_load();
	if ($words === array()) {
		$words = fractal_zip_phda9_dict_inline_vocab('');
	}
	if ($words === array()) {
		return $maybePlain;
	}
	try {
		return fractal_zip_phda9_detokenize($maybePlain, $words);
	} catch (Throwable $e) {
		return $maybePlain;
	}
}

/** True when the buffer contains phda9 dict word/cap escape sequences (0xFD 0x00/0x01 + varint). */
function fractal_zip_phda9_tok_stream_has_word_escapes(string $blob): bool
{
	$n = strlen($blob);
	for ($i = 0; $i + 2 < $n; $i++) {
		if ($blob[$i] !== FRACTAL_ZIP_PHDA9_TOK_ESC) {
			continue;
		}
		$tag = $blob[$i + 1];
		if ($tag === FRACTAL_ZIP_PHDA9_TOK_ESC_WORD || $tag === FRACTAL_ZIP_PHDA9_TOK_ESC_CAPWORD) {
			return true;
		}
	}
	return false;
}

/** Fast-tier phda9 dict tokenize (gzip/deflate any size; zstd/brotli/xz ≤512 KiB prose). On by default; FRACTAL_ZIP_TOKENIZE_FAST_TIER=0 disables. */
function fractal_zip_tokenize_fast_tier_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZE_FAST_TIER');
	if ($e !== false && in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true)) {
		return false;
	}
	return true;
}

function fractal_zip_tokenize_fast_tier_hit_rate_threshold(): float
{
	$e = getenv('FRACTAL_ZIP_TOKENIZE_FAST_TIER_HIT_RATE');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(0.0, min(1.0, (float) trim((string) $e) / 100.0));
	}
	return 0.85;
}

function fractal_zip_tokenize_fast_tier_probe_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZE_FAST_TIER_PROBE_BYTES');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(4096, (int) $e);
	}
	return 65536;
}

function fractal_zip_tokenize_fast_tier_zstd_brotli_xz_max_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZE_FAST_TIER_SLOW_OUTER_MAX_BYTES');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(0, (int) $e);
	}
	return 524288;
}

/** @return list<string> */
function fractal_zip_tokenize_fast_tier_frozen_vocab(): array
{
	static $cached = null;
	if (is_array($cached)) {
		return $cached;
	}
	if (fractal_zip_phda9_dict_inline_enabled()) {
		$cached = fractal_zip_phda9_dict_inline_vocab('');
		if ($cached !== array()) {
			return $cached;
		}
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$path = fractal_zip_paq_phda9_dict_path();
	if ($path !== null && is_file($path)) {
		$cached = fractal_zip_phda9_dict_read_words($path);
		return $cached;
	}
	$innerFile = getenv('FRACTAL_ZIP_PHDA9_INNER_VOCAB_FILE');
	if ($innerFile !== false && trim((string) $innerFile) !== '' && is_file(trim((string) $innerFile))) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_dict_phda9_inner.php';
		$cached = fractal_zip_enwik_phda9_inner_load_vocab_file(trim((string) $innerFile));
		return $cached;
	}
	return $cached = array();
}

function fractal_zip_tokenize_fast_tier_plain_prose(string $blob): bool
{
	if ($blob === '' || strlen($blob) < 256) {
		return false;
	}
	if (str_starts_with($blob, 'FZB4') || str_starts_with($blob, 'FZBD') || str_starts_with($blob, 'FZBM')
		|| str_starts_with($blob, 'FZCD') || str_starts_with($blob, 'FZWS')) {
		return false;
	}
	if (str_starts_with($blob, '<?xml') && (stripos($blob, '<page') !== false || stripos($blob, '<mediawiki') !== false)) {
		return false;
	}
	$sample = substr($blob, 0, min(8192, strlen($blob)));
	$print = 0;
	$n = strlen($sample);
	for ($i = 0; $i < $n; $i++) {
		$o = ord($sample[$i]);
		if ($o === 9 || $o === 10 || $o === 13 || ($o >= 32 && $o < 127)) {
			$print++;
		}
	}
	return ($print / max(1, $n)) >= 0.85;
}

function fractal_zip_tokenize_fast_tier_outer_allows(string $outerContext, int $innerBytes): bool
{
	$ctx = strtolower(trim($outerContext));
	if (in_array($ctx, array('gzip', 'gzip_fast', 'deflate', 'adaptive_fast', 'store'), true)) {
		return true;
	}
	if (in_array($ctx, array('zstd', 'brotli', 'xz'), true)) {
		$cap = fractal_zip_tokenize_fast_tier_zstd_brotli_xz_max_bytes();
		return $cap > 0 && $innerBytes <= $cap;
	}
	return false;
}

/** Fraction of probe prefix covered by frozen dict — gate before tokenized_zpaq (zpaq dislikes bad tokenize). */
function fractal_zip_tokenized_zpaq_hit_rate_threshold(): float
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_HIT_RATE');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(0.0, min(1.0, (float) trim((string) $e) / 100.0));
	}
	return 0.70;
}

function fractal_zip_tokenized_zpaq_mine_vocab_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_MINE');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	return in_array(strtolower(trim((string) $e)), array('1', 'true', 'on', 'yes'), true);
}

/** @return 'off'|'on'|'auto' */
function fractal_zip_tokenized_zpaq_delta_mode(): string
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_DELTA');
	if ($e === false || trim((string) $e) === '') {
		return 'auto';
	}
	$v = strtolower(trim((string) $e));
	if (in_array($v, array('0', 'off', 'false', 'no'), true)) {
		return 'off';
	}
	if (in_array($v, array('1', 'on', 'true', 'yes'), true)) {
		return 'on';
	}
	return 'auto';
}

function fractal_zip_tokenized_zpaq_delta_min_plain_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_DELTA_MIN_BYTES');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(0, (int) trim((string) $e));
	}
	return 524288;
}

function fractal_zip_tokenized_zpaq_delta_max_words(): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_DELTA_MAX_WORDS');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(64, (int) trim((string) $e));
	}
	return 8192;
}

function fractal_zip_tokenized_zpaq_delta_max_mine_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_DELTA_MAX_MINE_BYTES');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(65536, (int) trim((string) $e));
	}
	return 4194304;
}

/** Stratified prefix/suffix sample for delta vocab mining (avoids OOM on 40+ MiB prose). */
function fractal_zip_tokenized_zpaq_delta_mine_sample(string $plain): string
{
	$max = fractal_zip_tokenized_zpaq_delta_max_mine_bytes();
	$n = strlen($plain);
	if ($n <= $max) {
		return $plain;
	}
	$half = (int) ($max / 2);
	return substr($plain, 0, $half) . substr($plain, -$half);
}

/**
 * Supplemental words mined from plain that are absent from the shipped frozen dict.
 *
 * @return list<string>
 */
function fractal_zip_tokenized_zpaq_build_delta_words(string $plain, array $frozenWords): array
{
	if ($plain === '' || $frozenWords === array()) {
		return array();
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$frozenSet = array();
	foreach ($frozenWords as $w) {
		$frozenSet[(string) $w] = true;
	}
	$maxDelta = fractal_zip_tokenized_zpaq_delta_max_words();
	$mineText = fractal_zip_tokenized_zpaq_delta_mine_sample($plain);
	$mined = fractal_zip_text_dict_nncp_mine_vocab($mineText, array(
		'max_words' => $maxDelta,
		'min_word_len' => 2,
	));
	$delta = array();
	foreach ($mined as $w) {
		$w = (string) $w;
		if ($w === '' || isset($frozenSet[$w])) {
			continue;
		}
		$delta[] = $w;
		if (count($delta) >= $maxDelta) {
			break;
		}
	}
	return $delta;
}

/**
 * @param list<string> $frozenWords
 * @param list<string> $deltaWords
 * @return list<string>
 */
function fractal_zip_tokenized_zpaq_merge_frozen_delta(array $frozenWords, array $deltaWords): array
{
	if ($deltaWords === array()) {
		return $frozenWords;
	}
	return fractal_zip_phda9_dict_merge_word_lists($frozenWords, $deltaWords);
}

function fractal_zip_tokenized_zpaq_delta_vocab_cache_store(array $deltaWords): void
{
	$GLOBALS['fractal_zip_tokenized_zpaq_fzpa_delta_vocab'] = $deltaWords;
}

/** @return list<string> */
function fractal_zip_tokenized_zpaq_delta_vocab_cache_load(): array
{
	$cached = $GLOBALS['fractal_zip_tokenized_zpaq_fzpa_delta_vocab'] ?? null;
	return is_array($cached) ? $cached : array();
}

/** Opt out of the lpaq9l arc trial inside the fast tokenized inner: FRACTAL_ZIP_TOKENIZED_LPAQ=0. */
function fractal_zip_tokenized_zpaq_lpaq_trial_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_LPAQ');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	return !in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true);
}

/** Opt out of the mcm arc trial inside the fast tokenized inner: FRACTAL_ZIP_TOKENIZED_MCM=0. */
function fractal_zip_tokenized_zpaq_mcm_trial_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_MCM');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	return !in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true);
}

/**
 * Wall-budget segmentation dial for the fast-tier CM lane (opt-in; default 1 =
 * whole-stream, best bytes). FRACTAL_ZIP_TOKENIZED_CM_SEGMENTS forces a
 * segment count; else FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC derives one from the
 * payload size (lpaq9l ≈ 1.7 MB/s of plain per stage, ~6 s fixed overhead:
 * boot, probes, DRT, outer wrap, trailer). Each doubling costs ~1.9% bytes on dickens
 * (per-segment CM model restart), so segments only engage on payloads ≥ 4 MiB
 * where the whole-stream lane would blow the budget.
 */
function fractal_zip_tokenized_cm_segments(int $plainLen): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_CM_SEGMENTS');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(1, min(16, (int) trim((string) $e)));
	}
	$w = getenv('FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC');
	if ($w === false || trim((string) $w) === '' || !is_numeric(trim((string) $w))) {
		return 1;
	}
	$budget = (float) trim((string) $w);
	if ($budget <= 0.0 || $plainLen < 4194304) {
		return 1;
	}
	// Encode and verify-decode are equal-cost serial stages; verify overlaps
	// the outer wrap, so the pair fits when each stage fits in half the
	// post-overhead budget (measured: 10 MB at budget 10 s → 4 segments ≈ 9.5 s
	// wall). The ~7 s fixed floor (boot, probes, wrap, trailer) is structural:
	// budgets below it are treated as "as fast as segmentation can go".
	$stageSec = $plainLen / 1700000.0;
	$usable = max(0.75, ($budget - 7.0) / 2.0);
	return max(1, min(16, (int) ceil($stageSec / $usable)));
}

/** Opt out of the DRT+lpaq9l arc trial inside the fast tokenized inner: FRACTAL_ZIP_TOKENIZED_DRT=0. */
function fractal_zip_tokenized_zpaq_drt_trial_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_DRT');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	return !in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true);
}

/**
 * Arc codec pick for the fast text inner. The bundled CM lanes run in parallel
 * (sub-second each on ~400 KB) and the smaller wire wins; zpaq -m$zpaqMethod
 * only runs as a fallback when none produced bytes, or always under
 * FRACTAL_ZIP_TOKENIZED_ARC_TRIALS=both (bytes-first; adds the ~4 s zpaq trial).
 * Measured on lcet10: drt_lpaq9l 83.2 (raw) / lpaq9l 87.6 (tok) / mcm 89.8 /
 * zpaq m5 89.9 KB; on raw HTML: drt_lpaq9l 25.2 / mcm 26.2 / lpaq9l 27.6 KB —
 * the winner is content-dependent, so all stay in by default.
 *
 * @return array{bytes: string, tool_id: string}|null
 */
function fractal_zip_tokenized_zpaq_arc_shootout(string $payload, int $zpaqMethod, bool $withDrt = true, bool $rawProseCoveredByDrt = false): ?array
{
	if (!function_exists('fractal_zip_paq_zpaq_compress_bytes')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	}
	$mode = strtolower(trim((string) getenv('FRACTAL_ZIP_TOKENIZED_ARC_TRIALS')));
	$both = $mode === 'both';
	$best = null;
	$bestTool = '';
	// Raw prose with a live DRT pre-start: skip the raw-lpaq9l lane. On English
	// prose DRT+lpaq9l dominates plain lpaq9l by ~5-14% (dickens 1,858 vs
	// 2,158 KB; lcet10 82.2 vs 87.6 KB) while the raw lane is the longest
	// shootout lane (6.9 s vs 5.9 s on 10 MB) — it only delays the pick, which
	// in turn delays the deferred roundtrip verify that bounds the wall.
	// Token-stream payloads keep the lane: there lpaq9l is a genuine contender.
	$lpaqOn = !$rawProseCoveredByDrt && fractal_zip_tokenized_zpaq_lpaq_trial_enabled();
	$mcmOn = fractal_zip_tokenized_zpaq_mcm_trial_enabled();
	$drtOn = $withDrt && fractal_zip_tokenized_zpaq_drt_trial_enabled();
	if ($lpaqOn && $mcmOn) {
		$par = fractal_zip_paq_cm_compress_bytes_parallel($payload, $drtOn);
		if (is_string($par['lpaq9l'])) {
			$best = $par['lpaq9l'];
			$bestTool = 'lpaq9l';
		}
		if (is_string($par['mcm']) && ($best === null || strlen($par['mcm']) < strlen($best))) {
			$best = $par['mcm'];
			$bestTool = 'mcm';
		}
		foreach (array('drt_lpaq9l', 'drt_lpaq9lp') as $drtLane) {
			if (is_string($par[$drtLane] ?? null) && ($best === null || strlen($par[$drtLane]) < strlen($best))) {
				$best = $par[$drtLane];
				$bestTool = $drtLane;
			}
		}
	} elseif ($lpaqOn) {
		$l = fractal_zip_paq_lpaq_compress_bytes($payload);
		if (is_array($l) && is_string($l['bytes'] ?? null) && $l['bytes'] !== '') {
			$best = (string) $l['bytes'];
			$bestTool = 'lpaq9l';
		}
		if ($drtOn) {
			$primed = fractal_zip_paq_lpaq_drt_primer_path() !== null;
			$d = fractal_zip_paq_drt_lpaq_compress_bytes($payload, $primed);
			if (is_array($d) && is_string($d['bytes'] ?? null) && $d['bytes'] !== ''
				&& ($best === null || strlen((string) $d['bytes']) < strlen($best))) {
				$best = (string) $d['bytes'];
				$bestTool = $primed ? 'drt_lpaq9lp' : 'drt_lpaq9l';
			}
		}
	} elseif ($mcmOn) {
		$m = fractal_zip_paq_mcm_compress_bytes($payload);
		if (is_array($m) && is_string($m['bytes'] ?? null) && $m['bytes'] !== '') {
			$best = (string) $m['bytes'];
			$bestTool = 'mcm';
		}
	}
	if ($best !== null && !$both) {
		return array('bytes' => $best, 'tool_id' => $bestTool);
	}
	$z = fractal_zip_paq_zpaq_compress_bytes($payload, $zpaqMethod);
	$zb = (is_array($z) && is_string($z['bytes'] ?? null) && $z['bytes'] !== '') ? (string) $z['bytes'] : null;
	if ($zb === null && $best === null) {
		return null;
	}
	if ($best !== null && ($zb === null || strlen($best) < strlen($zb))) {
		return array('bytes' => $best, 'tool_id' => $bestTool);
	}
	return array('bytes' => (string) $zb, 'tool_id' => 'zpaq' . (string) $zpaqMethod);
}

/**
 * Pick tokenized_zpaq path: frozen, optional delta (FZPA tail), or raw zpaq/lpaq.
 *
 * @return array{
 *   compress_plain: string,
 *   words: list<string>,
 *   tokenized: bool,
 *   arc_bytes: string,
 *   tool_id: string,
 *   vocab_tail: ?string,
 *   vocab_source: string,
 *   delta_words: int
 * }
 */
function fractal_zip_tokenized_zpaq_pick_compress(string $plain, int $zpaqMethod): array
{
	// Raw-plain DRT trial starts before the tokenized pick so its ~0.5-1.5 s
	// lpaq9l pass overlaps the shootout instead of extending the wall. Needed
	// because tokenized picks feed the shootout the 0xFD-escaped token stream,
	// where DRT cannot see real words; on raw prose DRT+lpaq9l often beats
	// tokenize+CM (lcet10: 82.2 vs 87.6 KB primed).
	$drtHandle = null;
	$primed = false;
	// Only pre-start for prose: non-prose payloads reach the shootout raw, and
	// its own DRT lane covers them — a pre-start would just duplicate the work.
	if (fractal_zip_tokenized_zpaq_drt_trial_enabled()
		&& fractal_zip_tokenize_fast_tier_plain_prose($plain)) {
		if (!function_exists('fractal_zip_paq_drt_lpaq_compress_start')) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
		}
		$primed = fractal_zip_paq_lpaq_drt_primer_path() !== null;
		// Wall-budget mode: the segmented DRT lane replaces the whole shootout.
		// Alternative lanes are whole-stream and single-threaded (mcm 10 MB ≈
		// 4 s, zpaq m5 far more) — any of them would reintroduce the wall the
		// segments just bought, and on raw prose the DRT lane wins bytes anyway.
		$segments = fractal_zip_tokenized_cm_segments(strlen($plain));
		if ($segments > 1) {
			$segHandle = fractal_zip_paq_drt_lpaq_seg_compress_start($plain, $primed, $segments);
			if ($segHandle !== null) {
				$segArc = fractal_zip_paq_drt_lpaq_seg_compress_finish($segHandle);
				if ($segArc !== null) {
					return array(
						'compress_plain' => $plain,
						'words' => array(),
						'tokenized' => false,
						'arc_bytes' => $segArc,
						'tool_id' => ($primed ? 'drt_lpaq9lp' : 'drt_lpaq9l') . '_seg',
						'vocab_tail' => null,
						'vocab_source' => 'raw_drt_seg' . count($segHandle['procs']),
						'delta_words' => 0,
					);
				}
			}
			// Segmented lane unavailable (no DRT/binary): fall through to the
			// normal pick rather than failing the compress outright.
		}
		$drtHandle = fractal_zip_paq_drt_lpaq_compress_start($plain, $primed);
	}
	// With a live pre-start the shootout skips its own DRT lane entirely: on raw
	// picks it would recompress the identical DRT stream (a duplicated ~6 s
	// lpaq9l pass on 10 MB inputs), on tokenized picks DRT sees only the
	// 0xFD-escaped token stream and never wins. The raw-plain result below
	// covers both.
	$pick = fractal_zip_tokenized_zpaq_pick_compress_inner($plain, $zpaqMethod, $drtHandle === null);
	if ($drtHandle === null) {
		return $pick;
	}
	$drtArc = fractal_zip_paq_drt_lpaq_compress_finish($drtHandle);
	if ($drtArc === null) {
		return $pick;
	}
	$drtResult = array(
		'compress_plain' => $plain,
		'words' => array(),
		'tokenized' => false,
		'arc_bytes' => $drtArc,
		'tool_id' => $primed ? 'drt_lpaq9lp' : 'drt_lpaq9l',
		'vocab_tail' => null,
		'vocab_source' => 'raw_drt',
		'delta_words' => 0,
	);
	if ($pick['arc_bytes'] === '') {
		return $drtResult;
	}
	$pickWire = strlen($pick['arc_bytes']) + strlen((string) ($pick['vocab_tail'] ?? ''));
	return strlen($drtArc) < $pickWire ? $drtResult : $pick;
}

/**
 * @return array{
 *   compress_plain: string,
 *   words: list<string>,
 *   tokenized: bool,
 *   arc_bytes: string,
 *   tool_id: string,
 *   vocab_tail: ?string,
 *   vocab_source: string,
 *   delta_words: int
 * }
 */
function fractal_zip_tokenized_zpaq_pick_compress_inner(string $plain, int $zpaqMethod, bool $withDrt = true): array
{
	$zpaqToolId = 'zpaq' . (string) $zpaqMethod;
	$fail = static function (string $source) use ($zpaqToolId): array {
		return array(
			'compress_plain' => '',
			'words' => array(),
			'tokenized' => false,
			'arc_bytes' => '',
			'tool_id' => $zpaqToolId,
			'vocab_tail' => null,
			'vocab_source' => $source,
			'delta_words' => 0,
		);
	};
	if ($plain === '') {
		return array(
			'compress_plain' => '',
			'words' => array(),
			'tokenized' => false,
			'arc_bytes' => '',
			'tool_id' => $zpaqToolId,
			'vocab_tail' => null,
			'vocab_source' => 'empty',
			'delta_words' => 0,
		);
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$toolId = 'zpaq' . (string) $zpaqMethod;
	$vocabPick = fractal_zip_tokenized_zpaq_resolve_vocab($plain);
	if ($vocabPick['source'] === 'mined') {
		$words = $vocabPick['words'];
		$tok = fractal_zip_phda9_tokenize($plain, $words, true);
		if (!is_string($tok) || $tok === '' || !fractal_zip_phda9_tok_stream_has_word_escapes($tok)) {
			return $fail('mine_tokenize_failed');
		}
		$arc = fractal_zip_tokenized_zpaq_arc_shootout($tok, $zpaqMethod, $withDrt);
		if ($arc === null) {
			return $fail('mine_zpaq_failed');
		}
		return array(
			'compress_plain' => $tok,
			'words' => $words,
			'tokenized' => true,
			'arc_bytes' => (string) $arc['bytes'],
			'tool_id' => (string) $arc['tool_id'],
			'vocab_tail' => $vocabPick['vocab_blob'] !== '' ? $vocabPick['vocab_blob'] : null,
			'vocab_source' => 'mined',
			'delta_words' => 0,
		);
	}
	if ($vocabPick['words'] === array()) {
		$arc = fractal_zip_tokenized_zpaq_arc_shootout($plain, $zpaqMethod, $withDrt, !$withDrt);
		if ($arc === null) {
			return $fail((string) $vocabPick['source']);
		}
		return array(
			'compress_plain' => $plain,
			'words' => array(),
			'tokenized' => false,
			'arc_bytes' => (string) $arc['bytes'],
			'tool_id' => (string) $arc['tool_id'],
			'vocab_tail' => null,
			'vocab_source' => (string) $vocabPick['source'],
			'delta_words' => 0,
		);
	}
	$frozen = $vocabPick['words'];
	$tokFrozen = fractal_zip_phda9_tokenize($plain, $frozen, true);
	if (!is_string($tokFrozen) || $tokFrozen === '' || !fractal_zip_phda9_tok_stream_has_word_escapes($tokFrozen)) {
		$arc = fractal_zip_tokenized_zpaq_arc_shootout($plain, $zpaqMethod, $withDrt, !$withDrt);
		if ($arc === null) {
			return $fail('raw_zpaq_failed');
		}
		return array(
			'compress_plain' => $plain,
			'words' => array(),
			'tokenized' => false,
			'arc_bytes' => (string) $arc['bytes'],
			'tool_id' => (string) $arc['tool_id'],
			'vocab_tail' => null,
			'vocab_source' => 'raw_zpaq',
			'delta_words' => 0,
		);
	}
	$gzPlain = @gzdeflate($plain, 1);
	$gzTok = @gzdeflate($tokFrozen, 1);
	$tokenizeHelps = is_string($gzPlain) && is_string($gzTok)
		&& strlen($gzTok) < (int) ceil(strlen($gzPlain) * fractal_zip_tokenized_zpaq_raw_probe_gzip_ratio_max());
	if (!$tokenizeHelps) {
		$arc = fractal_zip_tokenized_zpaq_arc_shootout($plain, $zpaqMethod, $withDrt, !$withDrt);
		if ($arc === null) {
			return $fail('raw_zpaq_failed');
		}
		return array(
			'compress_plain' => $plain,
			'words' => array(),
			'tokenized' => false,
			'arc_bytes' => (string) $arc['bytes'],
			'tool_id' => (string) $arc['tool_id'],
			'vocab_tail' => null,
			'vocab_source' => 'raw_zpaq',
			'delta_words' => 0,
		);
	}
	$arcFrozen = fractal_zip_tokenized_zpaq_arc_shootout($tokFrozen, $zpaqMethod, $withDrt);
	if ($arcFrozen === null) {
		return $fail('frozen_zpaq_failed');
	}
	$best = array(
		'compress_plain' => $tokFrozen,
		'words' => $frozen,
		'tokenized' => true,
		'arc_bytes' => (string) $arcFrozen['bytes'],
		'tool_id' => (string) $arcFrozen['tool_id'],
		'vocab_tail' => null,
		'vocab_source' => 'frozen',
		'delta_words' => 0,
	);
	$bestWire = strlen($best['arc_bytes']);
	$deltaMode = fractal_zip_tokenized_zpaq_delta_mode();
	$tryDelta = $deltaMode === 'on'
		|| ($deltaMode === 'auto' && strlen($plain) >= fractal_zip_tokenized_zpaq_delta_min_plain_bytes());
	if (!$tryDelta) {
		return $best;
	}
	$delta = fractal_zip_tokenized_zpaq_build_delta_words($plain, $frozen);
	if ($delta === array()) {
		return $best;
	}
	$merged = fractal_zip_tokenized_zpaq_merge_frozen_delta($frozen, $delta);
	$tokDelta = fractal_zip_phda9_tokenize($plain, $merged, true);
	if (!is_string($tokDelta) || $tokDelta === '') {
		return $best;
	}
	$deltaBlob = fractal_zip_phda9_dict_inline_vocab_blob($delta);
	$gzFrozen = @gzdeflate($tokFrozen, 1);
	$gzDelta = @gzdeflate($tokDelta, 1);
	if (is_string($gzFrozen) && is_string($gzDelta)
		&& strlen($gzDelta) + strlen($deltaBlob) >= strlen($gzFrozen)) {
		return $best;
	}
	$arcDelta = fractal_zip_tokenized_zpaq_arc_shootout($tokDelta, $zpaqMethod, $withDrt);
	if ($arcDelta === null) {
		return $best;
	}
	$deltaWire = strlen((string) $arcDelta['bytes']) + strlen($deltaBlob);
	if ($deltaMode === 'on' || $deltaWire < $bestWire) {
		return array(
			'compress_plain' => $tokDelta,
			'words' => $merged,
			'tokenized' => true,
			'arc_bytes' => (string) $arcDelta['bytes'],
			'tool_id' => (string) $arcDelta['tool_id'],
			'vocab_tail' => $deltaBlob,
			'vocab_source' => 'frozen+delta',
			'delta_words' => count($delta),
		);
	}
	return $best;
}

/**
 * @param list<string> $words
 * @return list<list<int>>
 */
function fractal_zip_phda9_tok_index_cached(array $words): array
{
	static $cachedSig = '';
	static $cachedIndex = array();
	$sig = (string) count($words) . ':' . (string) ($words[0] ?? '') . ':' . (string) ($words[count($words) - 1] ?? '');
	if ($cachedSig !== $sig || $cachedIndex === array()) {
		$cachedSig = $sig;
		$cachedIndex = fractal_zip_phda9_tok_index_build($words);
	}
	return $cachedIndex;
}

function fractal_zip_tokenized_zpaq_hit_gate_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_HIT_GATE');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	return in_array(strtolower(trim((string) $e)), array('1', 'true', 'on', 'yes'), true);
}

function fractal_zip_tokenized_zpaq_raw_probe_gzip_ratio_max(): float
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_RAW_PROBE_GZIP_RATIO');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return max(0.5, min(1.0, (float) trim((string) $e)));
	}
	return 0.92;
}

/** Skip zpaq decompress RT on large raw-zpaq inners (zpaq integrity + outer .fz verify elsewhere). */
function fractal_zip_tokenized_zpaq_zpaq_rt_max_raw_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_ZPAQ_RT_MAX_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		if (in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true)) {
			return PHP_INT_MAX;
		}
		return max(0, (int) trim((string) $e));
	}
	return 8388608;
}

/**
 * Tokenized inners: verify detokenize(tok)==plain on the pre-compression token
 * stream instead of zpaq-decompressing the archive. Tokenization is the only
 * lossy-risk step; zpaq decode is checksummed by zpaq itself. Saves a full
 * zpaq decompress (~equal to compress time). FRACTAL_ZIP_TOKENIZED_ZPAQ_FULL_RT=1
 * restores the archive-decompress roundtrip.
 */
function fractal_zip_tokenized_zpaq_tok_rt_only(): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_ZPAQ_FULL_RT');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	return in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true);
}

function fractal_zip_tokenized_zpaq_skip_zpaq_rt(string $plain, bool $tokenized): bool
{
	if ($tokenized) {
		return false;
	}
	$max = fractal_zip_tokenized_zpaq_zpaq_rt_max_raw_bytes();
	return $max < PHP_INT_MAX && strlen($plain) > $max;
}

/**
 * Vocab for tokenized_zpaq: shipped frozen dict (0 wire bytes) by default; optional per-corpus mine.
 *
 * @return array{words: list<string>, source: string, vocab_blob: string, store_in_preprocess: bool, store_in_fzpa: bool}
 */
function fractal_zip_tokenized_zpaq_resolve_vocab(string $plain): array
{
	$empty = static function (string $source): array {
		return array(
			'words' => array(),
			'source' => $source,
			'vocab_blob' => '',
			'store_in_preprocess' => false,
			'store_in_fzpa' => false,
		);
	};
	if ($plain === '') {
		return $empty('empty');
	}
	if (fractal_zip_tokenized_zpaq_mine_vocab_enabled()) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_dict_phda9_inner.php';
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
		$words = fractal_zip_text_dict_nncp_mine_vocab($plain, array(
			'max_words' => fractal_zip_enwik_phda9_inner_max_words(),
			'min_word_len' => 2,
		));
		if ($words === array()) {
			return $empty('mine_failed');
		}
		$blob = fractal_zip_phda9_dict_inline_vocab_blob($words);
		return array(
			'words' => $words,
			'source' => 'mined',
			'vocab_blob' => $blob,
			'store_in_preprocess' => false,
			'store_in_fzpa' => true,
		);
	}
	$words = fractal_zip_tokenize_fast_tier_frozen_vocab();
	if ($words === array()) {
		return $empty('no_frozen_dict');
	}
	if (!fractal_zip_tokenize_fast_tier_plain_prose($plain)) {
		return $empty('not_prose');
	}
	if (fractal_zip_tokenized_zpaq_hit_gate_enabled()
		&& fractal_zip_phda9_tok_hit_rate_estimate($plain, $words) < fractal_zip_tokenized_zpaq_hit_rate_threshold()) {
		return $empty('hit_rate_gate');
	}
	return array(
		'words' => $words,
		'source' => 'frozen',
		'vocab_blob' => '',
		'store_in_preprocess' => false,
		'store_in_fzpa' => false,
	);
}

/**
 * Restore hook: load vocab embedded in FZPA wire (optional tail) into dict-inline cache.
 */
function fractal_zip_tokenized_zpaq_load_vocab_from_fzpa_wire(string $wire): bool
{
	if (!function_exists('fractal_zip_text_paq_wire_parse')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
	}
	$parsed = fractal_zip_text_paq_wire_parse($wire);
	if ($parsed === null) {
		return false;
	}
	$blob = (string) ($parsed['vocab_blob'] ?? '');
	if ($blob === '') {
		return false;
	}
	$words = fractal_zip_phda9_dict_inline_vocab_from_blob($blob);
	if ($words === array()) {
		return false;
	}
	fractal_zip_tokenized_zpaq_delta_vocab_cache_store($words);
	return true;
}

/**
 * Detokenize after FZPA undo when token stream has dict escapes.
 */
function fractal_zip_tokenized_zpaq_detokenize_plain(string $maybeTokPlain, array $preprocessMeta = array()): string
{
	if ($maybeTokPlain === '' || !fractal_zip_phda9_tok_stream_has_word_escapes($maybeTokPlain)) {
		return $maybeTokPlain;
	}
	$delta = fractal_zip_tokenized_zpaq_delta_vocab_cache_load();
	$words = fractal_zip_phda9_dict_inline_cache_load();
	if ($words === array() && $delta !== array()) {
		if (is_array($preprocessMeta) && !empty($preprocessMeta['general_text_vocab_delta'])) {
			$frozen = fractal_zip_tokenize_fast_tier_frozen_vocab();
			if ($frozen !== array()) {
				$words = fractal_zip_tokenized_zpaq_merge_frozen_delta($frozen, $delta);
			}
		} else {
			$words = $delta;
		}
	}
	if ($words === array() && is_array($preprocessMeta)) {
		if (is_string($preprocessMeta['general_text_token_vocab_b64'] ?? null)
			&& $preprocessMeta['general_text_token_vocab_b64'] !== '') {
			$vb = base64_decode((string) $preprocessMeta['general_text_token_vocab_b64'], true);
			if (is_string($vb) && $vb !== '') {
				$words = fractal_zip_phda9_dict_inline_vocab_from_blob($vb);
			}
		}
	}
	if ($words === array() && is_array($preprocessMeta)
		&& !empty($preprocessMeta['general_text_vocab_frozen'])) {
		$words = fractal_zip_tokenize_fast_tier_frozen_vocab();
	}
	if ($words === array()) {
		$words = fractal_zip_tokenize_fast_tier_frozen_vocab();
	}
	if ($words === array()) {
		return $maybeTokPlain;
	}
	fractal_zip_phda9_dict_inline_cache_store($words);
	try {
		return fractal_zip_phda9_detokenize($maybeTokPlain, $words);
	} catch (Throwable $e) {
		return $maybeTokPlain;
	}
}

/**
 * Fraction of probe prefix bytes covered by longest-prefix dict matches.
 * @param list<string> $words
 */
function fractal_zip_phda9_tok_hit_rate_estimate(string $plain, array $words, int $probeLen = 0): float
{
	if ($plain === '' || $words === array()) {
		return 0.0;
	}
	if ($probeLen <= 0) {
		$probeLen = fractal_zip_tokenize_fast_tier_probe_bytes();
	}
	$probe = substr($plain, 0, min($probeLen, strlen($plain)));
	$index = fractal_zip_phda9_tok_index_cached($words);
	$n = strlen($probe);
	$matched = 0;
	for ($i = 0; $i < $n;) {
		$atWordStart = ($i === 0) || !fractal_zip_phda9_tok_is_word_byte($probe[$i - 1]);
		if ($atWordStart && fractal_zip_phda9_tok_is_word_byte($probe[$i])) {
			$fc = ord($probe[$i]);
			$found = false;
			foreach ($index[$fc] as $wi) {
				$ml = fractal_zip_phda9_tok_word_match($words, $wi, $probe, $i);
				if ($ml > 0) {
					$matched += $ml;
					$i += $ml;
					$found = true;
					break;
				}
			}
			if (!$found) {
				$i++;
			}
		} else {
			$i++;
		}
	}
	return $matched / max(1, $n);
}

/**
 * Tokenize plain prose for fast outer tiers when hit-rate gate passes. Returns null when skipped.
 */
function fractal_zip_tokenize_fast_tier_maybe_transform(string $plain, string $outerContext = 'adaptive_fast'): ?string
{
	if (!fractal_zip_tokenize_fast_tier_enabled() || $plain === '') {
		return null;
	}
	if (!fractal_zip_tokenize_fast_tier_plain_prose($plain)) {
		return null;
	}
	$innerLen = strlen($plain);
	if (!fractal_zip_tokenize_fast_tier_outer_allows($outerContext, $innerLen)) {
		return null;
	}
	$words = fractal_zip_tokenize_fast_tier_frozen_vocab();
	if ($words === array()) {
		return null;
	}
	if (fractal_zip_phda9_tok_hit_rate_estimate($plain, $words) < fractal_zip_tokenize_fast_tier_hit_rate_threshold()) {
		return null;
	}
	$tok = fractal_zip_phda9_tokenize($plain, $words, true);
	if ($tok === $plain) {
		return null;
	}
	$rt = fractal_zip_phda9_detokenize($tok, $words);
	if (!hash_equals($plain, $rt)) {
		return null;
	}
	return $tok;
}

/** gzip-fast / raw literal bundle: tokenize textish members in place (mode 0 wire). */
function fractal_zip_tokenize_fast_tier_preprocess_literal(string $relPath, string $rawBytes): string
{
	if ($rawBytes === '') {
		return $rawBytes;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_policy.php';
	$row = fractal_zip_identify_for_policy($relPath, $rawBytes);
	$profile = (string) ($row['content_profile'] ?? '');
	if ($profile !== '' && !fractal_zip_content_format_is_textish_profile($profile)
		&& !fractal_zip_tokenize_fast_tier_plain_prose($rawBytes)) {
		return $rawBytes;
	}
	$tok = fractal_zip_tokenize_fast_tier_maybe_transform($rawBytes, 'gzip_fast');
	return $tok ?? $rawBytes;
}

/** Decode hook for FZB literal mode 0 when fast-tier tokenize was applied at encode time. */
function fractal_zip_tokenize_fast_tier_restore_if_tokenized(string $bytes): string
{
	if ($bytes === '' || strpos($bytes, FRACTAL_ZIP_PHDA9_TOK_ESC) === false) {
		return $bytes;
	}
	$words = fractal_zip_tokenize_fast_tier_frozen_vocab();
	if ($words === array()) {
		return $bytes;
	}
	try {
		$plain = fractal_zip_phda9_detokenize($bytes, $words);
	} catch (Throwable $e) {
		return $bytes;
	}
	// Mode 0 carries no "was tokenized" flag, so this hook fires on any member with
	// 0xFD-escape-looking bytes — including binary members that were stored raw
	// (blind detokenize silently corrupted those). Tokenize is deterministic with
	// the frozen vocab: genuine fast-tier members re-tokenize to the exact stored
	// bytes; incidental binary matches do not, and must pass through untouched.
	$rt = fractal_zip_phda9_tokenize($plain, $words, true);
	if (!is_string($rt) || !hash_equals($bytes, $rt)) {
		return $bytes;
	}
	return $plain;
}
