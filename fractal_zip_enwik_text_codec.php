<?php
declare(strict_types=1);

/**
 * Reversible text representations for enwik8 article experiments (world-record dictionary research).
 *
 * Sidecar JSON holds vocab, permutations, and sort keys — analogous to title-sort living outside raw bytes.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

/** Extract all &lt;text xml:space="preserve"&gt; bodies from one page XML. */
function fractal_zip_enwik_extract_page_preserve_text(string $pageXml): string
{
	$parts = array();
	$marker = '<text xml:space="preserve">';
	$openLen = strlen($marker);
	$pos = 0;
	$n = strlen($pageXml);
	while ($pos < $n) {
		$i = stripos($pageXml, $marker, $pos);
		if ($i === false) {
			break;
		}
		$pos = $i + $openLen;
		$close = stripos($pageXml, '</text>', $pos);
		if ($close === false) {
			$parts[] = substr($pageXml, $pos);
			break;
		}
		$parts[] = substr($pageXml, $pos, $close - $pos);
		$pos = $close + 7;
	}
	return implode("\n", $parts);
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_text_tokenize_words(string $text): array
{
	if ($text === '') {
		return array();
	}
	if (preg_match_all("/[A-Za-z][A-Za-z0-9_\x27]*/", $text, $m) < 1 || empty($m[0])) {
		return array();
	}
	return $m[0];
}

/**
 * Split preserve-text into words and literal gaps (whitespace runs, punctuation).
 *
 * @return list<array{type: string, text: string}>
 */
function fractal_zip_enwik_text_segment_implicit_space(string $text): array
{
	if ($text === '') {
		return array();
	}
	if (preg_match_all("/[A-Za-z][A-Za-z0-9_\x27]*|\s+|[^\sA-Za-z]+/", $text, $m, PREG_SET_ORDER) < 1) {
		return array(array('type' => 'gap', 'text' => $text));
	}
	$segments = array();
	foreach ($m as $match) {
		$s = (string) $match[0];
		if ($s === '') {
			continue;
		}
		if (preg_match('/^[A-Za-z]/', $s) === 1) {
			$segments[] = array('type' => 'word', 'text' => $s);
		} else {
			$segments[] = array('type' => 'gap', 'text' => $s);
		}
	}
	return $segments;
}

/** Single ASCII space between two words is implicit (not stored in the stream). */
function fractal_zip_enwik_text_gap_is_implicit(string $gap, bool $wordBefore, bool $wordAfter): bool
{
	return $wordBefore && $wordAfter && $gap === ' ';
}

/** Re-insert omitted trailing '.' after the last word (before any following gap literals). */
function fractal_zip_enwik_text_restore_trailing_implicit_period(string $out, int $lastWordEnd): string
{
	if ($lastWordEnd <= 0 || $lastWordEnd > strlen($out)) {
		return $out;
	}
	return substr($out, 0, $lastWordEnd) . '.' . substr($out, $lastWordEnd);
}

/**
 * Trailing sentence '.' only when no word appears later in the segment list (not "Dr." before a name).
 *
 * @param list<array{type: string, text: string}> $segments
 */
function fractal_zip_enwik_text_gap_is_implicit_trailing_period(array $segments, int $gapIndex): bool
{
	if (!isset($segments[$gapIndex]) || $segments[$gapIndex]['type'] !== 'gap') {
		return false;
	}
	if ($segments[$gapIndex]['text'] !== '.') {
		return false;
	}
	if ($gapIndex === 0 || $segments[$gapIndex - 1]['type'] !== 'word') {
		return false;
	}
	$n = count($segments);
	for ($j = $gapIndex + 1; $j < $n; $j++) {
		if ($segments[$j]['type'] === 'word') {
			return false;
		}
	}
	return true;
}

/** True when encode/decode must preserve full &lt;text&gt; bytes (gaps + words), not words-only. */
function fractal_zip_enwik_text_codec_preserves_plaintext(string $scheme): bool
{
	$scheme = strtolower(trim($scheme));
	return $scheme === 'words_base94_isp' || $scheme === 'words_id_varint_isp';
}

/**
 * Vocab lookup on decode. literal_oov archives must not silently map missing ids to UNK.
 */
function fractal_zip_enwik_text_vocab_lookup(array $vocab, int $id, array $sidecar): string
{
	if (isset($vocab[$id])) {
		return $vocab[$id];
	}
	if (!empty($sidecar['literal_oov'])) {
		throw new RuntimeException('text codec: missing vocab id ' . $id . ' (no UNK fallback)');
	}
	return (string) ($sidecar['unknown'] ?? 'UNK');
}

/** Out-of-vocabulary word line prefix (token chars never include colon). */
const FRACTAL_ZIP_ENWIK_TEXT_LITERAL_WORD_PREFIX = 'l:';

/** Varint stream tag: literal word bytes follow (length-prefixed). */
const FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD = 3;

function fractal_zip_enwik_text_literal_word_line(string $word): string
{
	return FRACTAL_ZIP_ENWIK_TEXT_LITERAL_WORD_PREFIX . $word;
}

function fractal_zip_enwik_text_parse_literal_word_line(string $chunk): ?string
{
	if (!str_starts_with($chunk, FRACTAL_ZIP_ENWIK_TEXT_LITERAL_WORD_PREFIX)) {
		return null;
	}
	return substr($chunk, strlen(FRACTAL_ZIP_ENWIK_TEXT_LITERAL_WORD_PREFIX));
}

/**
 * Collect gap literals with ids sorted by corpus frequency (smaller ids for common punctuation/newlines).
 *
 * @param list<array{type: string, text: string}> $segments
 * @return array{gaps: list<string>, index: array<string, int>}
 */
function fractal_zip_enwik_text_build_gap_table(array $segments, bool $implicitSpace): array
{
	$counts = array();
	$n = count($segments);
	for ($i = 0; $i < $n; $i++) {
		$seg = $segments[$i];
		if ($seg['type'] !== 'gap') {
			continue;
		}
		$wordBefore = ($i > 0 && $segments[$i - 1]['type'] === 'word');
		$wordAfter = ($i + 1 < $n && $segments[$i + 1]['type'] === 'word');
		if ($implicitSpace && (
			fractal_zip_enwik_text_gap_is_implicit($seg['text'], $wordBefore, $wordAfter)
			|| fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)
		)) {
			continue;
		}
		$g = $seg['text'];
		if (!isset($counts[$g])) {
			$counts[$g] = 0;
		}
		$counts[$g]++;
	}
	$rows = array();
	foreach ($counts as $g => $c) {
		$rows[] = array('g' => $g, 'c' => $c);
	}
	usort($rows, static fn (array $a, array $b): int => $b['c'] <=> $a['c']);
	$gaps = array();
	$index = array();
	foreach ($rows as $row) {
		$index[$row['g']] = count($gaps);
		$gaps[] = $row['g'];
	}
	return array('gaps' => $gaps, 'index' => $index);
}

/**
 * @return array{alphabet: string, base: int}
 */
function fractal_zip_enwik_text_base94_alphabet(): array
{
	$alphabet = '';
	for ($c = 33; $c <= 126; $c++) {
		$alphabet .= chr($c);
	}
	return array('alphabet' => $alphabet, 'base' => strlen($alphabet));
}

/**
 * Encode word stream with optional gap literals (implicit single space between words).
 *
 * @param list<array{type: string, text: string}> $segments
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_text_encode_words_stream(
	string $scheme,
	array $segments,
	array $opts = array()
): array {
	$scheme = strtolower(trim($scheme));
	$implicitSpace = ($scheme === 'words_base94_isp' || $scheme === 'words_id_varint_isp');
	$useVarint = ($scheme === 'words_id_varint' || $scheme === 'words_id_varint_isp');

	$words = array();
	foreach ($segments as $seg) {
		if ($seg['type'] === 'word') {
			$words[] = $seg['text'];
		}
	}
	$shared = isset($opts['vocab'], $opts['vocab_index']) && is_array($opts['vocab']);
	$sidecar = array('scheme' => $scheme);
	if ($shared) {
		$vocab = $opts['vocab'];
		$index = $opts['vocab_index'];
		$sidecar['shared_vocab'] = true;
	} else {
		$vocab = fractal_zip_enwik_text_build_vocab($words, (int) ($opts['max_vocab'] ?? 120000));
		$index = fractal_zip_enwik_text_vocab_index($vocab);
		$sidecar['vocab'] = $vocab;
	}
	$sidecar['literal_oov'] = true;

	$gapTable = $implicitSpace
		? fractal_zip_enwik_text_build_gap_table($segments, true)
		: array('gaps' => array(), 'index' => array());
	$gapStrings = $gapTable['gaps'];
	$gapIndex = $gapTable['index'];

	$n = count($segments);
	$implicitOmitted = 0;
	$implicitPeriodOmitted = 0;
	if ($useVarint) {
		$buf = '';
		for ($i = 0; $i < $n; $i++) {
			$seg = $segments[$i];
			if ($seg['type'] === 'word') {
				$t = $seg['text'];
				if (!isset($index[$t])) {
					$sidecar['literal_words'] = ($sidecar['literal_words'] ?? array()) + array($t => true);
					$lit = $t;
					$buf .= chr(FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD)
						. fractal_zip_enwik_encode_varint_u32(strlen($lit))
						. $lit;
					continue;
				}
				$id = (int) $index[$t];
				$buf .= "\x01" . fractal_zip_enwik_encode_varint_u32($id);
				continue;
			}
			$wordBefore = ($i > 0 && $segments[$i - 1]['type'] === 'word');
			$wordAfter = ($i + 1 < $n && $segments[$i + 1]['type'] === 'word');
			if ($implicitSpace && (
				fractal_zip_enwik_text_gap_is_implicit($seg['text'], $wordBefore, $wordAfter)
				|| fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)
			)) {
				if (fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)) {
					$sidecar['trailing_implicit_period'] = true;
					$implicitPeriodOmitted++;
				} else {
					$implicitOmitted++;
				}
				continue;
			}
			$gtxt = $seg['text'];
			if (!isset($gapIndex[$gtxt])) {
				$gapIndex[$gtxt] = count($gapStrings);
				$gapStrings[] = $gtxt;
			}
			$gid = (int) $gapIndex[$gtxt];
			$buf .= "\x02" . fractal_zip_enwik_encode_varint_u32($gid);
		}
		$payload = $buf;
	} else {
		$ba = fractal_zip_enwik_text_base94_alphabet();
		$alphabet = $ba['alphabet'];
		$base = $ba['base'];
		$sidecar['alphabet'] = $alphabet;
		$chunks = array();
		for ($i = 0; $i < $n; $i++) {
			$seg = $segments[$i];
			if ($seg['type'] === 'word') {
				$t = $seg['text'];
				if (!isset($index[$t])) {
					$sidecar['literal_words'] = ($sidecar['literal_words'] ?? array()) + array($t => true);
					$chunks[] = fractal_zip_enwik_text_literal_word_line($t);
					continue;
				}
				$id = (int) $index[$t];
				$chunks[] = 'w' . fractal_zip_enwik_text_encode_base_n($id, $base, $alphabet);
				continue;
			}
			$wordBefore = ($i > 0 && $segments[$i - 1]['type'] === 'word');
			$wordAfter = ($i + 1 < $n && $segments[$i + 1]['type'] === 'word');
			if ($implicitSpace && (
				fractal_zip_enwik_text_gap_is_implicit($seg['text'], $wordBefore, $wordAfter)
				|| fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)
			)) {
				if (fractal_zip_enwik_text_gap_is_implicit_trailing_period($segments, $i)) {
					$sidecar['trailing_implicit_period'] = true;
					$implicitPeriodOmitted++;
				} else {
					$implicitOmitted++;
				}
				continue;
			}
			$gtxt = $seg['text'];
			if (!isset($gapIndex[$gtxt])) {
				$gapIndex[$gtxt] = count($gapStrings);
				$gapStrings[] = $gtxt;
			}
			$gid = (int) $gapIndex[$gtxt];
			$chunks[] = 'g' . fractal_zip_enwik_text_encode_base_n($gid, $base, $alphabet);
		}
		$payload = implode("\n", $chunks);
		$sidecar['token_sep'] = "\n";
	}

	if ($implicitSpace) {
		$sidecar['implicit_space'] = true;
		$sidecar['implicit_period'] = true;
		$sidecar['gaps'] = $gapStrings;
	}
	$sidecar['token_count'] = count($words);
	$sidecar['vocab_size'] = count($vocab);
	$meta = array(
		'payload_bytes' => strlen($payload),
		'vocab_size' => count($vocab),
		'gap_literals' => count($gapStrings),
		'implicit_spaces_omitted' => $implicitOmitted,
		'implicit_periods_omitted' => $implicitPeriodOmitted,
	);
	return array('payload' => $payload, 'sidecar' => $sidecar, 'meta' => $meta);
}

/**
 * @return array{payload: string, sidecar: array<string, mixed>}
 */
function fractal_zip_enwik_text_decode_words_stream(string $scheme, string $payload, array $sidecar): string
{
	$scheme = strtolower(trim($scheme));
	$implicitSpace = !empty($sidecar['implicit_space']);
	$useVarint = ($scheme === 'words_id_varint' || $scheme === 'words_id_varint_isp');
	$vocab = $sidecar['vocab'] ?? array();
	$gaps = $sidecar['gaps'] ?? array();
	$wordOnlyVarint = $useVarint && !$implicitSpace;

	if ($useVarint) {
		$out = '';
		$lastWasWord = false;
		$lastWordEnd = 0;
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
				if ($lastWasWord && ($implicitSpace || $wordOnlyVarint)) {
					$out .= ' ';
				}
				$out .= fractal_zip_enwik_text_vocab_lookup($vocab, $id, $sidecar);
				$lastWasWord = true;
				$lastWordEnd = strlen($out);
			} elseif ($tag === FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD) {
				$litLen = $id;
				if ($litLen < 0 || $pos + $litLen > $plen) {
					break;
				}
				$lit = substr($payload, $pos, $litLen);
				$pos += $litLen;
				if ($lastWasWord && ($implicitSpace || $wordOnlyVarint)) {
					$out .= ' ';
				}
				$out .= $lit;
				$lastWasWord = true;
				$lastWordEnd = strlen($out);
			} elseif ($tag === 2) {
				$out .= (string) ($gaps[$id] ?? '');
				$lastWasWord = false;
			}
		}
		if ($implicitSpace && !empty($sidecar['trailing_implicit_period'])) {
			$out = fractal_zip_enwik_text_restore_trailing_implicit_period($out, $lastWordEnd);
		}
		return $out;
	}

	$ba = fractal_zip_enwik_text_base94_alphabet();
	$alphabet = (string) ($sidecar['alphabet'] ?? $ba['alphabet']);
	$base = strlen($alphabet);
	$sep = (string) ($sidecar['token_sep'] ?? "\n");
	$out = '';
	$lastWasWord = false;
	$lastWordEnd = 0;
	foreach (explode($sep, trim($payload)) as $chunk) {
		if ($chunk === '' || strlen($chunk) < 2) {
			continue;
		}
		$lit = fractal_zip_enwik_text_parse_literal_word_line($chunk);
		if ($lit !== null) {
			if ($implicitSpace && $lastWasWord) {
				$out .= ' ';
			}
			$out .= $lit;
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
			continue;
		}
		$kind = $chunk[0];
		$enc = substr($chunk, 1);
		$id = fractal_zip_enwik_text_decode_base_n($enc, $base, $alphabet);
		if ($kind === 'w') {
			if ($implicitSpace && $lastWasWord) {
				$out .= ' ';
			}
			$out .= fractal_zip_enwik_text_vocab_lookup($vocab, $id, $sidecar);
			$lastWasWord = true;
			$lastWordEnd = strlen($out);
		} elseif ($kind === 'g') {
			$out .= (string) ($gaps[$id] ?? '');
			$lastWasWord = false;
		}
	}
	if ($implicitSpace && !empty($sidecar['trailing_implicit_period'])) {
		$out = fractal_zip_enwik_text_restore_trailing_implicit_period($out, $lastWordEnd);
	}
	return $out;
}

/**
 * @param list<string> $tokens
 */
function fractal_zip_enwik_text_build_vocab(array $tokens, int $maxWords = 200000): array
{
	$counts = array();
	foreach ($tokens as $t) {
		if (!isset($counts[$t])) {
			$counts[$t] = 0;
		}
		$counts[$t]++;
	}
	arsort($counts, SORT_NUMERIC);
	$vocab = array();
	foreach ($counts as $w => $_c) {
		$vocab[] = $w;
		if (count($vocab) >= $maxWords) {
			break;
		}
	}
	return $vocab;
}

/**
 * @param list<string> $vocab word → id
 * @return array<string, int>
 */
function fractal_zip_enwik_text_vocab_index(array $vocab): array
{
	$idx = array();
	foreach ($vocab as $i => $w) {
		$idx[$w] = $i;
	}
	return $idx;
}

/** Bijective base-N encode (digits 0..alphabet-1). */
function fractal_zip_enwik_text_encode_base_n(int $value, int $alphabetSize, string $digitChars): string
{
	if ($alphabetSize < 2 || strlen($digitChars) < $alphabetSize) {
		throw new InvalidArgumentException('invalid base-N alphabet');
	}
	if ($value < 0) {
		throw new InvalidArgumentException('negative value');
	}
	if ($value === 0) {
		return $digitChars[0];
	}
	$out = '';
	while ($value > 0) {
		$out = $digitChars[$value % $alphabetSize] . $out;
		$value = intdiv($value, $alphabetSize);
	}
	return $out;
}

function fractal_zip_enwik_text_decode_base_n(string $encoded, int $alphabetSize, string $digitChars): int
{
	$map = array();
	for ($i = 0; $i < $alphabetSize; $i++) {
		$map[$digitChars[$i]] = $i;
	}
	$value = 0;
	$len = strlen($encoded);
	for ($i = 0; $i < $len; $i++) {
		$ch = $encoded[$i];
		if (!isset($map[$ch])) {
			throw new RuntimeException('invalid base-N digit');
		}
		$value = $value * $alphabetSize + $map[$ch];
	}
	return $value;
}

/** Standard ITU Morse (A–Z, 0–9); other bytes UTF-8 escaped as \xHH in sidecar escapes. */
function fractal_zip_enwik_text_morse_table(): array
{
	static $fwd = null;
	if ($fwd !== null) {
		return $fwd;
	}
	$raw = array(
		'A' => '.-', 'B' => '-...', 'C' => '-.-.', 'D' => '-..', 'E' => '.', 'F' => '..-.',
		'G' => '--.', 'H' => '....', 'I' => '..', 'J' => '.---', 'K' => '-.-', 'L' => '.-..',
		'M' => '--', 'N' => '-.', 'O' => '---', 'P' => '.--.', 'Q' => '--.-', 'R' => '.-.',
		'S' => '...', 'T' => '-', 'U' => '..-', 'V' => '...-', 'W' => '.--', 'X' => '-..-',
		'Y' => '-.--', 'Z' => '--..', '0' => '-----', '1' => '.----', '2' => '..---',
		'3' => '...--', '4' => '....-', '5' => '.....', '6' => '-....', '7' => '--...',
		'8' => '---..', '9' => '----.',
	);
	return $fwd = $raw;
}

/** Scrabble-style letter costs → shorter binary codes for frequent letters (fixed canonical tree). */
function fractal_zip_enwik_text_scrabble_letter_codes(): array
{
	// Approximate English frequency order → bit strings (prefix-free).
	return array(
		'E' => '0', 'T' => '100', 'A' => '101', 'O' => '1100', 'I' => '1101', 'N' => '11100',
		'S' => '11101', 'H' => '111100', 'R' => '111101', 'D' => '1111100', 'L' => '1111101',
		'C' => '11111100', 'U' => '11111101', 'M' => '111111100', 'W' => '111111101',
		'F' => '1111111100', 'G' => '1111111101', 'Y' => '11111111100', 'P' => '11111111101',
		'B' => '111111111100', 'V' => '111111111101', 'K' => '1111111111100',
		'J' => '1111111111101', 'X' => '11111111111100', 'Q' => '11111111111101',
		'Z' => '111111111111100',
	);
}

function fractal_zip_enwik_text_pack_bits(string $bitString): string
{
	$bitString = preg_replace('/[^01]/', '', $bitString) ?? '';
	$pad = (8 - (strlen($bitString) % 8)) % 8;
	$bitString .= str_repeat('0', $pad);
	$out = chr($pad);
	$len = strlen($bitString);
	for ($i = 0; $i < $len; $i += 8) {
		$out .= chr(bindec(substr($bitString, $i, 8)));
	}
	return $out;
}

function fractal_zip_enwik_text_unpack_bits(string $packed): string
{
	if ($packed === '') {
		return '';
	}
	$pad = ord($packed[0]) & 7;
	$bits = '';
	$len = strlen($packed);
	for ($i = 1; $i < $len; $i++) {
		$bits .= str_pad(decbin(ord($packed[$i])), 8, '0', STR_PAD_LEFT);
	}
	if ($pad > 0 && strlen($bits) >= $pad) {
		$bits = substr($bits, 0, -$pad);
	}
	return $bits;
}

/**
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_enwik_text_codec_encode(string $scheme, string $text, array $opts = array()): array
{
	$scheme = strtolower(trim($scheme));
	$sidecar = array('scheme' => $scheme);
	$meta = array('raw_bytes' => strlen($text));

	switch ($scheme) {
		case 'raw':
			return array('payload' => $text, 'sidecar' => $sidecar, 'meta' => $meta);

		case 'morse':
			$table = fractal_zip_enwik_text_morse_table();
			$rev = array();
			foreach ($table as $k => $v) {
				$rev[$v] = $k;
			}
			$sidecar['morse_rev'] = $rev;
			$out = array();
			$upper = strtoupper($text);
			$len = strlen($upper);
			for ($i = 0; $i < $len; $i++) {
				$ch = $upper[$i];
				if ($ch === ' ') {
					$out[] = '/';
					continue;
				}
				if (isset($table[$ch])) {
					$out[] = $table[$ch];
				} else {
					$out[] = '+';
					$sidecar['extras'] = ($sidecar['extras'] ?? array()) + array((string) $i => $text[$i]);
				}
			}
			$payload = implode(' ', $out);
			$meta['payload_bytes'] = strlen($payload);
			return array('payload' => $payload, 'sidecar' => $sidecar, 'meta' => $meta);

		case 'bits_msb':
			$bits = '';
			$len = strlen($text);
			for ($i = 0; $i < $len; $i++) {
				$bits .= str_pad(decbin(ord($text[$i])), 8, '0', STR_PAD_LEFT);
			}
			$payload = fractal_zip_enwik_text_pack_bits($bits);
			$meta['payload_bytes'] = strlen($payload);
			$meta['bit_count'] = strlen($bits);
			return array('payload' => $payload, 'sidecar' => $sidecar, 'meta' => $meta);

		case 'bytes_decimal':
			$nums = array();
			$len = strlen($text);
			for ($i = 0; $i < $len; $i++) {
				$nums[] = (string) ord($text[$i]);
			}
			$payload = implode(' ', $nums);
			$meta['payload_bytes'] = strlen($payload);
			return array('payload' => $payload, 'sidecar' => $sidecar, 'meta' => $meta);

		case 'letter_scrabble':
			$codes = fractal_zip_enwik_text_scrabble_letter_codes();
			$rev = array();
			foreach ($codes as $k => $bits) {
				$rev[$bits] = $k;
			}
			$sidecar['letter_rev'] = $rev;
			$bitstream = '';
			$upper = strtoupper($text);
			$len = strlen($upper);
			for ($i = 0; $i < $len; $i++) {
				$ch = $upper[$i];
				if (isset($codes[$ch])) {
					$bitstream .= $codes[$ch];
				} elseif ($ch === ' ') {
					$bitstream .= '111111111111101';
				} else {
					$bitstream .= '111111111111110';
					$sidecar['extras'] = ($sidecar['extras'] ?? array()) + array((string) $i => $text[$i]);
				}
			}
			$payload = fractal_zip_enwik_text_pack_bits($bitstream);
			$meta['payload_bytes'] = strlen($payload);
			return array('payload' => $payload, 'sidecar' => $sidecar, 'meta' => $meta);

		case 'words_base94_isp':
		case 'words_id_varint_isp':
			$segments = fractal_zip_enwik_text_segment_implicit_space($text);
			$packed = fractal_zip_enwik_text_encode_words_stream($scheme, $segments, $opts);
			return array(
				'payload' => $packed['payload'],
				'sidecar' => array_merge($sidecar, $packed['sidecar']),
				'meta' => array_merge($meta, $packed['meta']),
			);

		case 'words_base94':
		case 'words_base256':
			$tokens = fractal_zip_enwik_text_tokenize_words($text);
			$shared = isset($opts['vocab'], $opts['vocab_index']) && is_array($opts['vocab']);
			if ($shared) {
				$vocab = $opts['vocab'];
				$index = $opts['vocab_index'];
				$unknown = (string) ($opts['unknown_token'] ?? 'UNK');
				$sidecar['shared_vocab'] = true;
			} else {
				$vocab = fractal_zip_enwik_text_build_vocab($tokens, (int) ($opts['max_vocab'] ?? 120000));
				$index = fractal_zip_enwik_text_vocab_index($vocab);
				$sidecar['vocab'] = $vocab;
			}
			if ($scheme === 'words_base256') {
				$sidecar['literal_oov'] = true;
				$payload = '';
				foreach ($tokens as $t) {
					if (!isset($index[$t])) {
						$sidecar['literal_words'] = ($sidecar['literal_words'] ?? array()) + array($t => true);
						$payload .= chr(FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD)
							. fractal_zip_enwik_encode_varint_u32(strlen($t))
							. $t;
						continue;
					}
					$id = (int) $index[$t] & 0xFFFFFF;
					$payload .= "\x01"
						. chr(($id >> 16) & 0xFF) . chr(($id >> 8) & 0xFF) . chr($id & 0xFF);
				}
			} else {
				$alphabet = '';
				for ($c = 33; $c <= 126; $c++) {
					$alphabet .= chr($c);
				}
				$base = strlen($alphabet);
				$sidecar['alphabet'] = $alphabet;
				$chunks = array();
				$sidecar['literal_oov'] = true;
				foreach ($tokens as $t) {
					if (!isset($index[$t])) {
						$sidecar['literal_words'] = ($sidecar['literal_words'] ?? array()) + array($t => true);
						$chunks[] = fractal_zip_enwik_text_literal_word_line($t);
						continue;
					}
					$chunks[] = fractal_zip_enwik_text_encode_base_n((int) $index[$t], $base, $alphabet);
				}
				$payload = implode("\n", $chunks);
			}
			$sidecar['token_count'] = count($tokens);
			$sidecar['token_sep'] = "\n";
			$sidecar['vocab_size'] = count($vocab);
			$meta['payload_bytes'] = strlen($payload);
			$meta['vocab_size'] = count($vocab);
			return array('payload' => $payload, 'sidecar' => $sidecar, 'meta' => $meta);

		case 'words_id_varint':
			$tokens = fractal_zip_enwik_text_tokenize_words($text);
			$shared = isset($opts['vocab'], $opts['vocab_index']) && is_array($opts['vocab']);
			if ($shared) {
				$vocab = $opts['vocab'];
				$index = $opts['vocab_index'];
				$unknown = (string) ($opts['unknown_token'] ?? 'UNK');
				$sidecar['shared_vocab'] = true;
			} else {
				$vocab = fractal_zip_enwik_text_build_vocab($tokens, (int) ($opts['max_vocab'] ?? 120000));
				$index = fractal_zip_enwik_text_vocab_index($vocab);
				$sidecar['vocab'] = $vocab;
			}
			$sidecar['literal_oov'] = true;
			$buf = '';
			foreach ($tokens as $t) {
				if (!isset($index[$t])) {
					$sidecar['literal_words'] = ($sidecar['literal_words'] ?? array()) + array($t => true);
					$buf .= chr(FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD)
						. fractal_zip_enwik_encode_varint_u32(strlen($t))
						. $t;
					continue;
				}
				$buf .= "\x01" . fractal_zip_enwik_encode_varint_u32((int) $index[$t]);
			}
			$payload = $buf;
			$meta['payload_bytes'] = strlen($payload);
			$meta['vocab_size'] = count($vocab);
			return array('payload' => $payload, 'sidecar' => $sidecar, 'meta' => $meta);

		default:
			throw new InvalidArgumentException('unknown text codec: ' . $scheme);
	}
}

function fractal_zip_enwik_text_codec_decode(string $scheme, string $payload, array $sidecar): string
{
	$scheme = strtolower(trim($scheme));

	switch ($scheme) {
		case 'raw':
			return $payload;

		case 'morse':
			$table = fractal_zip_enwik_text_morse_table();
			$rev = $sidecar['morse_rev'] ?? array();
			if ($rev === array()) {
				foreach ($table as $k => $v) {
					$rev[$v] = $k;
				}
			}
			$extras = $sidecar['extras'] ?? array();
			$words = preg_split('/\s+/', trim($payload)) ?: array();
			$out = '';
			$origIdx = 0;
			foreach ($words as $w) {
				if ($w === '/') {
					$out .= ' ';
					$origIdx++;
					continue;
				}
				if ($w === '+' && isset($extras[(string) $origIdx])) {
					$out .= (string) $extras[(string) $origIdx];
					$origIdx++;
					continue;
				}
				$out .= $rev[$w] ?? '?';
				$origIdx++;
			}
			return $out;

		case 'bits_msb':
			$bits = fractal_zip_enwik_text_unpack_bits($payload);
			$out = '';
			for ($i = 0; $i + 8 <= strlen($bits); $i += 8) {
				$out .= chr(bindec(substr($bits, $i, 8)));
			}
			return $out;

		case 'bytes_decimal':
			$nums = preg_split('/\s+/', trim($payload)) ?: array();
			$out = '';
			foreach ($nums as $n) {
				if ($n === '') {
					continue;
				}
				$out .= chr((int) $n);
			}
			return $out;

		case 'letter_scrabble':
			$rev = $sidecar['letter_rev'] ?? array();
			$bits = fractal_zip_enwik_text_unpack_bits($payload);
			$codes = fractal_zip_enwik_text_scrabble_letter_codes();
			if ($rev === array()) {
				foreach ($codes as $k => $b) {
					$rev[$b] = $k;
				}
			}
			$extras = $sidecar['extras'] ?? array();
			$sorted = array_map('strval', array_keys($rev));
			usort($sorted, static fn (string $a, string $b) => strlen($b) <=> strlen($a));
			$out = '';
			$pos = 0;
			$blen = strlen($bits);
			$extraIdx = 0;
			while ($pos < $blen) {
				$matched = false;
				foreach ($sorted as $code) {
					$clen = strlen($code);
					if ($pos + $clen > $blen) {
						continue;
					}
					if (substr($bits, $pos, $clen) === $code) {
						$ch = $rev[$code];
						$out .= ($ch === ' ') ? ' ' : $ch;
						$pos += $clen;
						$matched = true;
						break;
					}
				}
				if (!$matched) {
					$out .= (string) ($extras[(string) $extraIdx] ?? '?');
					$extraIdx++;
					$pos++;
				}
			}
			return $out;

		case 'words_base94_isp':
		case 'words_id_varint_isp':
			return fractal_zip_enwik_text_decode_words_stream($scheme, $payload, $sidecar);

		case 'words_base94':
			$vocab = $sidecar['vocab'] ?? array();
			$alphabet = (string) ($sidecar['alphabet'] ?? '');
			if ($alphabet === '') {
				for ($c = 33; $c <= 126; $c++) {
					$alphabet .= chr($c);
				}
			}
			$base = strlen($alphabet);
			$sep = (string) ($sidecar['token_sep'] ?? "\n");
			$out = '';
			$lastWasWord = false;
			foreach (explode($sep, trim($payload)) as $chunk) {
				if ($chunk === '') {
					continue;
				}
				$lit = fractal_zip_enwik_text_parse_literal_word_line($chunk);
				if ($lit !== null) {
					if ($lastWasWord) {
						$out .= ' ';
					}
					$out .= $lit;
					$lastWasWord = true;
					continue;
				}
				$id = fractal_zip_enwik_text_decode_base_n($chunk, $base, $alphabet);
				if ($lastWasWord) {
					$out .= ' ';
				}
				$out .= fractal_zip_enwik_text_vocab_lookup($vocab, $id, $sidecar);
				$lastWasWord = true;
			}
			return $out;

		case 'words_base256':
			$vocab = $sidecar['vocab'] ?? array();
			$unknown = (string) ($sidecar['unknown'] ?? 'UNK');
			if (!empty($sidecar['literal_oov'])) {
				$tokens = array();
				$pos = 0;
				$plen = strlen($payload);
				while ($pos < $plen) {
					$tag = ord($payload[$pos]);
					$pos++;
					if ($tag === 1 && $pos + 3 <= $plen) {
						$id = (ord($payload[$pos]) << 16) | (ord($payload[$pos + 1]) << 8) | ord($payload[$pos + 2]);
						$pos += 3;
						$tokens[] = fractal_zip_enwik_text_vocab_lookup($vocab, $id, $sidecar);
						continue;
					}
					if ($tag === FRACTAL_ZIP_ENWIK_TEXT_STREAM_TAG_LITERAL_WORD) {
						$pair = fractal_zip_enwik_decode_varint_u32($payload, $pos);
						if ($pair === null) {
							break;
						}
						$litLen = (int) $pair[0];
						$pos = (int) $pair[1];
						if ($litLen < 0 || $pos + $litLen > $plen) {
							break;
						}
						$tokens[] = substr($payload, $pos, $litLen);
						$pos += $litLen;
					}
				}
				return implode(' ', $tokens);
			}
			$tokens = array();
			$len = strlen($payload);
			for ($i = 0; $i + 3 <= $len; $i += 3) {
				$id = (ord($payload[$i]) << 16) | (ord($payload[$i + 1]) << 8) | ord($payload[$i + 2]);
				$tokens[] = fractal_zip_enwik_text_vocab_lookup($vocab, $id, $sidecar);
			}
			return implode(' ', $tokens);

		case 'words_id_varint':
			return fractal_zip_enwik_text_decode_words_stream('words_id_varint', $payload, $sidecar);

		default:
			throw new InvalidArgumentException('unknown text codec: ' . $scheme);
	}
}

/**
 * Post-transforms on plain text (reversible via sidecar).
 *
 * @return array{payload: string, sidecar: array<string, mixed>}
 */
function fractal_zip_enwik_text_transform_apply(string $transform, string $text, array $sidecar = array()): array
{
	$transform = strtolower(trim($transform));
	if ($transform === '' || $transform === 'none') {
		return array('payload' => $text, 'sidecar' => $sidecar);
	}

	switch ($transform) {
		case 'sort_lines_alpha':
			$lines = explode("\n", $text);
			$n = count($lines);
			$order = range(0, max(0, $n - 1));
			usort($order, static function (int $a, int $b) use ($lines): int {
				return strcmp($lines[$a], $lines[$b]);
			});
			$sorted = array();
			foreach ($order as $oldIdx) {
				$sorted[] = $lines[$oldIdx];
			}
			// Permutation only (not full lines_before_sort) — sidecar was ~2× payload per EZT region.
			$sidecar['sort_lines_from'] = $order;
			$sidecar['transform'] = $transform;
			return array('payload' => implode("\n", $sorted), 'sidecar' => $sidecar);

		case 'reverse_lines':
			$lines = explode("\n", $text);
			$lines = array_reverse($lines);
			$sidecar['transform'] = $transform;
			return array('payload' => implode("\n", $lines), 'sidecar' => $sidecar);

		case 'perm_lines':
			$seed = (int) ($sidecar['seed'] ?? 1);
			$lines = explode("\n", $text);
			$n = count($lines);
			$perm = range(0, $n - 1);
			$sidecar['perm_lines'] = fractal_zip_enwik_text_lcg_shuffle_perm($perm, $seed);
			$sidecar['transform'] = $transform;
			$sidecar['seed'] = $seed;
			$out = array();
			foreach ($sidecar['perm_lines'] as $idx) {
				$out[] = $lines[$idx];
			}
			return array('payload' => implode("\n", $out), 'sidecar' => $sidecar);

		case 'perm_tokens':
			$tokens = fractal_zip_enwik_text_tokenize_words($text);
			$seed = (int) ($sidecar['seed'] ?? 1);
			$n = count($tokens);
			$perm = range(0, max(0, $n - 1));
			$sidecar['perm_tokens'] = fractal_zip_enwik_text_lcg_shuffle_perm($perm, $seed);
			$sidecar['transform'] = $transform;
			$sidecar['seed'] = $seed;
			$out = array();
			foreach ($sidecar['perm_tokens'] as $idx) {
				$out[] = $tokens[$idx];
			}
			return array('payload' => implode(' ', $out), 'sidecar' => $sidecar);

		case 'sort_tokens_alpha':
			$tokens = fractal_zip_enwik_text_tokenize_words($text);
			$sidecar['tokens_before_sort'] = $tokens;
			sort($tokens, SORT_STRING);
			$sidecar['transform'] = $transform;
			return array('payload' => implode(' ', $tokens), 'sidecar' => $sidecar);

		case 'bio_lossy_bigram':
		case 'bio_lossy_trigram':
		case 'bio_lossy_hmm':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bio_lossy.php';
			$model = $transform === 'bio_lossy_trigram' ? 'trigram' : ($transform === 'bio_lossy_hmm' ? 'hmm' : 'bigram');
			$sidecar['model'] = $model;
			$sidecar['original'] = $text;
			$sidecar['ref'] = (string) ($sidecar['ref'] ?? $text);
			return fractal_zip_bio_lossy_transform_apply($text, $sidecar);

		case 'bio_consensus':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bioinformatics.php';
			$lines = explode("\n", $text);
			$consensus = fractal_zip_bio_poa_consensus($lines);
			$sidecar['transform'] = $transform;
			$sidecar['lines_before'] = $lines;
			return array('payload' => $consensus, 'sidecar' => $sidecar);

		default:
			throw new InvalidArgumentException('unknown transform: ' . $transform);
	}
}

/**
 * @param list<int> $perm 0..n-1 indices
 * @return list<int>
 */
function fractal_zip_enwik_text_lcg_shuffle_perm(array $perm, int $seed): array
{
	$n = count($perm);
	for ($i = $n - 1; $i > 0; $i--) {
		$seed = (1103515245 * $seed + 12345) & 0x7fffffff;
		$j = $seed % ($i + 1);
		$tmp = $perm[$i];
		$perm[$i] = $perm[$j];
		$perm[$j] = $tmp;
	}
	return $perm;
}

function fractal_zip_enwik_text_transform_undo(string $transform, string $text, array $sidecar): string
{
	$transform = strtolower(trim((string) ($sidecar['transform'] ?? $transform)));
	if ($transform === '' || $transform === 'none') {
		return $text;
	}

	switch ($transform) {
		case 'reverse_lines':
			$lines = explode("\n", $text);
			return implode("\n", array_reverse($lines));

		case 'sort_lines_alpha':
			if (isset($sidecar['lines_before_sort']) && is_array($sidecar['lines_before_sort'])) {
				return implode("\n", $sidecar['lines_before_sort']);
			}
			$from = $sidecar['sort_lines_from'] ?? null;
			if (!is_array($from)) {
				throw new RuntimeException('sort_lines_alpha missing sort_lines_from in sidecar');
			}
			$lines = explode("\n", $text);
			$n = count($from);
			if (count($lines) !== $n) {
				throw new RuntimeException('sort_lines_alpha line count mismatch on undo');
			}
			$orig = array_fill(0, $n, '');
			foreach ($from as $newIdx => $oldIdx) {
				$orig[(int) $oldIdx] = $lines[(int) $newIdx];
			}
			return implode("\n", $orig);

		case 'perm_lines':
			$perm = $sidecar['perm_lines'] ?? array();
			$lines = explode("\n", $text);
			$n = count($lines);
			$orig = array_fill(0, $n, '');
			foreach ($perm as $newIdx => $oldIdx) {
				$orig[(int) $oldIdx] = $lines[(int) $newIdx];
			}
			return implode("\n", $orig);

		case 'perm_tokens':
			$perm = $sidecar['perm_tokens'] ?? array();
			$tokens = preg_split('/\s+/', trim($text)) ?: array();
			$n = count($tokens);
			$orig = array_fill(0, $n, '');
			foreach ($perm as $newIdx => $oldIdx) {
				$orig[(int) $oldIdx] = $tokens[(int) $newIdx];
			}
			return implode(' ', $orig);

		case 'sort_tokens_alpha':
			if (isset($sidecar['tokens_before_sort']) && is_array($sidecar['tokens_before_sort'])) {
				return implode(' ', $sidecar['tokens_before_sort']);
			}
			throw new RuntimeException('sort_tokens_alpha missing tokens_before_sort in sidecar');

		case 'bio_lossy_bigram':
		case 'bio_lossy_trigram':
		case 'bio_lossy_hmm':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bio_lossy.php';
			return fractal_zip_bio_lossy_transform_undo($text, $sidecar);

		case 'bio_consensus':
			if (isset($sidecar['lines_before']) && is_array($sidecar['lines_before'])) {
				return implode("\n", $sidecar['lines_before']);
			}
			throw new RuntimeException('bio_consensus missing lines_before in sidecar');

		default:
			throw new InvalidArgumentException('unknown transform undo: ' . $transform);
	}
}

/** Suggested outer codecs by representation shape. */
function fractal_zip_enwik_text_outer_affinity(string $scheme): array
{
	$hints = array(
		'raw' => array('zpaq', 'brotli', 'zstd', 'fractal'),
		'morse' => array('brotli', 'zstd', 'gzip', 'fractal'),
		'bits_msb' => array('zpaq', 'xz', 'fractal'),
		'bytes_decimal' => array('brotli', 'zstd', 'gzip'),
		'letter_scrabble' => array('zpaq', 'xz', 'fractal'),
		'words_base94' => array('zpaq', 'fractal', 'brotli'),
		'words_base94_isp' => array('zpaq', 'fractal', 'brotli'),
		'words_base256' => array('zpaq', 'xz', 'fractal'),
		'words_id_varint' => array('zpaq', 'fractal', 'zstd'),
		'words_id_varint_isp' => array('zpaq', 'fractal', 'zstd'),
	);
	return $hints[$scheme] ?? array('fractal', 'zpaq', 'brotli');
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_text_codec_catalog(): array
{
	return array(
		'raw',
		'morse',
		'bits_msb',
		'bytes_decimal',
		'letter_scrabble',
		'words_base94',
		'words_base94_isp',
		'words_base256',
		'words_id_varint',
		'words_id_varint_isp',
	);
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_text_transform_catalog(): array
{
	return array(
		'none',
		'sort_lines_alpha',
		'reverse_lines',
		'perm_lines',
		'perm_tokens',
		'sort_tokens_alpha',
		'bio_lossy_bigram',
		'bio_lossy_trigram',
		'bio_lossy_hmm',
		'bio_consensus',
	);
}

/** gzip-1 size + shannon entropy bits/byte (quick outer hint). */
function fractal_zip_enwik_text_measure_payload(string $payload): array
{
	$raw = strlen($payload);
	$g1 = @gzdeflate($payload, 1);
	$gzip1 = is_string($g1) ? strlen($g1) : null;
	$hist = array_fill(0, 256, 0);
	$len = strlen($payload);
	for ($i = 0; $i < $len; $i++) {
		$hist[ord($payload[$i])]++;
	}
	$entropy = 0.0;
	if ($len > 0) {
		foreach ($hist as $c) {
			if ($c <= 0) {
				continue;
			}
			$p = $c / $len;
			$entropy -= $p * log($p, 2);
		}
	}
	return array(
		'payload_bytes' => $raw,
		'gzip1_bytes' => $gzip1,
		'entropy_bits_per_byte' => round($entropy, 4),
	);
}

const FRACTAL_ZIP_ENWIK_TEXT_CODEC_MAGIC = "EZTC\x01";
const FRACTAL_ZIP_ENWIK_TEXT_CODEC_VOCAB_MAGIC = "EZTV\x01";
const FRACTAL_ZIP_ENWIK_TEXT_CODEC_BULK_MAGIC = "EZTB\x01";

/**
 * Gzip EZTB blob: all per-region EZTC entries (kept out of phrase-pack dict).
 *
 * @param list<string> $entries
 */
function fractal_zip_enwik_text_codec_pack_region_bulk(array $entries): string
{
	$parts = array(
		FRACTAL_ZIP_ENWIK_TEXT_CODEC_BULK_MAGIC,
		fractal_zip_enwik_encode_varint_u32(count($entries)),
	);
	foreach ($entries as $entry) {
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($entry)) . $entry;
	}
	$raw = implode('', $parts);
	$gz = gzcompress($raw, 9);
	if ($gz === false) {
		throw new RuntimeException('text codec region bulk gzip failed');
	}
	return $gz;
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_text_codec_unpack_region_bulk(string $bulkGz): array
{
	$raw = gzuncompress($bulkGz);
	if ($raw === false) {
		throw new RuntimeException('text codec region bulk gzip decompress failed');
	}
	if (!str_starts_with($raw, FRACTAL_ZIP_ENWIK_TEXT_CODEC_BULK_MAGIC)) {
		throw new RuntimeException('text codec region bulk magic missing');
	}
	$off = strlen(FRACTAL_ZIP_ENWIK_TEXT_CODEC_BULK_MAGIC);
	$dv = fractal_zip_enwik_decode_varint_u32($raw, $off);
	if ($dv === null) {
		throw new RuntimeException('text codec region bulk count missing');
	}
	$n = (int) $dv[0];
	$off = (int) $dv[1];
	$entries = array();
	for ($i = 0; $i < $n; $i++) {
		$dv = fractal_zip_enwik_decode_varint_u32($raw, $off);
		if ($dv === null) {
			throw new RuntimeException('text codec region bulk entry len missing');
		}
		$len = (int) $dv[0];
		$off = (int) $dv[1];
		if ($len < 0 || $off + $len > strlen($raw)) {
			throw new RuntimeException('text codec region bulk entry truncated');
		}
		$entries[] = substr($raw, $off, $len);
		$off += $len;
	}
	return $entries;
}

/**
 * Replace ~EZT#~ tokens using bulk region table + optional shared vocab from phrase dict.
 */
function fractal_zip_enwik_text_codec_restore_eztokens_in_blob(string $blob, string $bulkGz, ?array $sharedVocab = null): string
{
	$entries = fractal_zip_enwik_text_codec_unpack_region_bulk($bulkGz);
	foreach ($entries as $i => $entry) {
		$tok = fractal_zip_enwik_text_codec_token($i);
		$replacement = fractal_zip_enwik_text_codec_restore_entry($entry, $sharedVocab);
		$blob = str_replace($tok, $replacement, $blob);
		$legacy = "\x1FEZT" . fractal_zip_enwik_semantic_pack_id($i) . "\x1F";
		if ($legacy !== $tok) {
			$blob = str_replace($legacy, $replacement, $blob);
		}
	}
	return $blob;
}

/**
 * Mine word vocabulary from all &lt;text&gt; regions in an enwik blob (for shared corpus vocab).
 *
 * @return list<string>
 */
function fractal_zip_enwik_text_codec_build_shared_vocab_from_blob(string $blob, int $maxWords = 120000): array
{
	$marker = '<text xml:space="preserve">';
	$openLen = strlen($marker);
	$counts = array();
	$pos = 0;
	$n = strlen($blob);
	while ($pos < $n) {
		$i = stripos($blob, $marker, $pos);
		if ($i === false) {
			break;
		}
		$pos = $i + $openLen;
		$close = stripos($blob, '</text>', $pos);
		if ($close === false) {
			break;
		}
		$inner = substr($blob, $pos, $close - $pos);
		$pos = $close + 7;
		if (preg_match_all("/[A-Za-z][A-Za-z0-9_\x27]*/", $inner, $wm) > 0) {
			foreach ($wm[0] as $w) {
				if (!isset($counts[$w])) {
					$counts[$w] = 0;
				}
				$counts[$w]++;
			}
		}
	}
	$tokens = array();
	foreach ($counts as $w => $c) {
		$tokens[] = array('w' => $w, 'c' => $c);
	}
	usort($tokens, static fn (array $a, array $b): int => $b['c'] <=> $a['c']);
	$vocab = array();
	foreach ($tokens as $row) {
		$vocab[] = $row['w'];
		if (count($vocab) >= $maxWords) {
			break;
		}
	}
	return $vocab;
}

/** Dict pattern: shared vocabulary (no ~EP~ token in XML). */
function fractal_zip_enwik_text_codec_vocab_dict_entry(array $vocab): string
{
	$json = json_encode($vocab, JSON_UNESCAPED_UNICODE);
	if (!is_string($json)) {
		throw new RuntimeException('text codec vocab json failed');
	}
	$gz = gzcompress($json, 6);
	if ($gz === false) {
		throw new RuntimeException('text codec vocab gzip failed');
	}
	return FRACTAL_ZIP_ENWIK_TEXT_CODEC_VOCAB_MAGIC
		. fractal_zip_enwik_encode_varint_u32(strlen($gz)) . $gz;
}

/**
 * @return list<string>|null
 */
function fractal_zip_enwik_text_codec_vocab_from_dict_patterns(array $patterns): ?array
{
	foreach ($patterns as $pat) {
		if (!str_starts_with((string) $pat, FRACTAL_ZIP_ENWIK_TEXT_CODEC_VOCAB_MAGIC)) {
			continue;
		}
		$off = strlen(FRACTAL_ZIP_ENWIK_TEXT_CODEC_VOCAB_MAGIC);
		$dv = fractal_zip_enwik_decode_varint_u32((string) $pat, $off);
		if ($dv === null) {
			return null;
		}
		$len = (int) $dv[0];
		$off = (int) $dv[1];
		$gz = substr((string) $pat, $off, $len);
		$json = gzuncompress($gz);
		if ($json === false) {
			return null;
		}
		$vocab = json_decode($json, true);
		return is_array($vocab) ? $vocab : null;
	}
	return null;
}

function fractal_zip_enwik_text_codec_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * @return array{scheme: string, transform: string, seed: int}|null
 */
function fractal_zip_enwik_text_codec_config_from_env(): ?array
{
	if (!fractal_zip_enwik_text_codec_enabled()) {
		return null;
	}
	$scheme = getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC');
	if ($scheme === false || trim((string) $scheme) === '') {
		return null;
	}
	$transform = getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM');
	if ($transform === false || trim((string) $transform) === '') {
		$transform = 'sort_lines_alpha';
	}
	$seed = (int) (getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED') ?: 1);
	$scheme = strtolower(trim((string) $scheme));
	if (!fractal_zip_enwik_text_codec_preserves_plaintext($scheme)) {
		$allow = getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_WORDS_ONLY');
		if ($allow !== '1' && $allow !== 'true' && $allow !== 'yes') {
			throw new RuntimeException(
				'FRACTAL_ZIP_ENWIK_TEXT_CODEC=' . $scheme
				. ' is words-only (lossy on &lt;text&gt;); use words_base94_isp or set FRACTAL_ZIP_ENWIK_TEXT_CODEC_WORDS_ONLY=1 for lab'
			);
		}
	}
	return array(
		'scheme' => $scheme,
		'transform' => strtolower(trim((string) $transform)),
		'seed' => $seed,
	);
}

function fractal_zip_enwik_text_codec_token(int $n): string
{
	return '~EZT' . fractal_zip_enwik_semantic_pack_id($n) . '~';
}

/**
 * Binary dict entry stored as a semantic-pack "pattern" (restored via {@see fractal_zip_enwik_text_codec_restore_entry()}).
 */
function fractal_zip_enwik_text_codec_serialize_entry(string $scheme, string $transform, array $sidecar, string $payload): string
{
	$sidecarJson = json_encode(
		array_merge($sidecar, array('scheme' => $scheme, 'transform' => $transform)),
		JSON_UNESCAPED_UNICODE
	);
	if (!is_string($sidecarJson)) {
		throw new RuntimeException('text codec sidecar json encode failed');
	}
	$sg = gzcompress($sidecarJson, 6);
	$pg = gzcompress($payload, 6);
	if ($sg === false || $pg === false) {
		throw new RuntimeException('text codec gzip failed');
	}
	$schemeB = $scheme;
	$transformB = $transform;
	return FRACTAL_ZIP_ENWIK_TEXT_CODEC_MAGIC
		. fractal_zip_enwik_encode_varint_u32(strlen($schemeB)) . $schemeB
		. fractal_zip_enwik_encode_varint_u32(strlen($transformB)) . $transformB
		. fractal_zip_enwik_encode_varint_u32(strlen($sg)) . $sg
		. fractal_zip_enwik_encode_varint_u32(strlen($pg)) . $pg;
}

/**
 * @return array{scheme: string, transform: string, sidecar: array<string, mixed>, payload: string}
 */
function fractal_zip_enwik_text_codec_deserialize_entry(string $entry): array
{
	if (!str_starts_with($entry, FRACTAL_ZIP_ENWIK_TEXT_CODEC_MAGIC)) {
		throw new RuntimeException('invalid text codec dict entry');
	}
	$off = strlen(FRACTAL_ZIP_ENWIK_TEXT_CODEC_MAGIC);
	$read = static function (string $blob, int &$o): string {
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $o);
		if ($dv === null) {
			throw new RuntimeException('text codec dict truncated');
		}
		$len = (int) $dv[0];
		$o = (int) $dv[1];
		if ($len < 0 || $o + $len > strlen($blob)) {
			throw new RuntimeException('text codec dict length invalid');
		}
		$s = substr($blob, $o, $len);
		$o += $len;
		return $s;
	};
	$scheme = $read($entry, $off);
	$transform = $read($entry, $off);
	$dv = fractal_zip_enwik_decode_varint_u32($entry, $off);
	if ($dv === null) {
		throw new RuntimeException('text codec sidecar len missing');
	}
	$slen = (int) $dv[0];
	$off = (int) $dv[1];
	if ($off + $slen > strlen($entry)) {
		throw new RuntimeException('text codec sidecar truncated');
	}
	$sg = substr($entry, $off, $slen);
	$off += $slen;
	$dv = fractal_zip_enwik_decode_varint_u32($entry, $off);
	if ($dv === null) {
		throw new RuntimeException('text codec payload len missing');
	}
	$plen = (int) $dv[0];
	$off = (int) $dv[1];
	if ($off + $plen > strlen($entry)) {
		throw new RuntimeException('text codec payload truncated');
	}
	$pg = substr($entry, $off, $plen);
	$sidecarJson = gzuncompress($sg);
	$payload = gzuncompress($pg);
	if ($sidecarJson === false || $payload === false) {
		throw new RuntimeException('text codec gzip decompress failed');
	}
	$sidecar = json_decode($sidecarJson, true);
	if (!is_array($sidecar)) {
		throw new RuntimeException('text codec sidecar json invalid');
	}
	return array(
		'scheme' => $scheme,
		'transform' => $transform,
		'sidecar' => $sidecar,
		'payload' => $payload,
	);
}

function fractal_zip_enwik_text_codec_restore_entry(string $entry, ?array $sharedVocab = null): string
{
	$des = fractal_zip_enwik_text_codec_deserialize_entry($entry);
	$sidecar = $des['sidecar'];
	if (!empty($sidecar['shared_vocab']) && is_array($sharedVocab)) {
		$sidecar['vocab'] = $sharedVocab;
	}
	$undo = fractal_zip_enwik_text_transform_undo(
		$des['transform'],
		$des['payload'],
		$sidecar
	);
	return fractal_zip_enwik_text_codec_decode($des['scheme'], $undo, $sidecar);
}

/**
 * Pack each &lt;text&gt; body as ~EZT#~ token; append EZTC dict patterns to $patternSink.
 *
 * @param array{scheme: string, transform: string, seed: int, vocab?: list<string>, vocab_index?: array<string, int>} $cfg
 * @param list<string> $patternSink
 */
function fractal_zip_enwik_pack_text_regions_with_codec(string $pageXml, array $cfg, array &$patternSink, int &$nextTokenId): string
{
	$encodeOpts = array();
	if (isset($cfg['vocab'], $cfg['vocab_index']) && is_array($cfg['vocab'])) {
		$encodeOpts['vocab'] = $cfg['vocab'];
		$encodeOpts['vocab_index'] = $cfg['vocab_index'];
	}
	$scheme = (string) ($cfg['scheme'] ?? '');
	if ($scheme !== '' && !fractal_zip_enwik_text_codec_preserves_plaintext($scheme)) {
		$allow = getenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_WORDS_ONLY');
		if ($allow !== '1' && $allow !== 'true') {
			throw new RuntimeException(
				'text codec ' . $scheme . ' is words-only (drops punctuation/markup); use words_base94_isp on enwik wire'
			);
		}
	}
	$marker = '<text xml:space="preserve">';
	$openLen = strlen($marker);
	$out = '';
	$pos = 0;
	$n = strlen($pageXml);
	while ($pos < $n) {
		$i = stripos($pageXml, $marker, $pos);
		if ($i === false) {
			$out .= substr($pageXml, $pos);
			break;
		}
		$out .= substr($pageXml, $pos, $i - $pos + $openLen);
		$pos = $i + $openLen;
		$close = stripos($pageXml, '</text>', $pos);
		if ($close === false) {
			$out .= substr($pageXml, $pos);
			break;
		}
		$inner = substr($pageXml, $pos, $close - $pos);
		$enc = fractal_zip_enwik_text_codec_encode((string) $cfg['scheme'], $inner, $encodeOpts);
		$t = fractal_zip_enwik_text_transform_apply(
			(string) $cfg['transform'],
			(string) $enc['payload'],
			array('seed' => (int) ($cfg['seed'] ?? 1))
		);
		$patternSink[] = fractal_zip_enwik_text_codec_serialize_entry(
			(string) $cfg['scheme'],
			(string) $cfg['transform'],
			array_merge($enc['sidecar'], $t['sidecar']),
			(string) $t['payload']
		);
		$tok = fractal_zip_enwik_text_codec_token($nextTokenId);
		$nextTokenId++;
		$out .= $tok;
		$pos = $close;
	}
	return $out;
}

/** True when qg→spiral inner transform is enabled (env-gated). */
function fractal_zip_enwik_spiral_inner_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_SPIRAL_INNER');
	return $v === '1' || $v === 'true';
}

/** Pack sidecar+SPRL into one FZSPL member blob (no outboard sidecar bytes). */
function fractal_zip_enwik_spiral_member_fold_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_SPIRAL_MEMBER_FOLD');
	return $v === '1' || $v === 'true';
}

/**
 * Optional inner transform: quantum_grammar parse → spiral angular residuals.
 *
 * @return array{payload:string, sidecar:array<string,mixed>, meta:array<string,mixed>}|null
 */
function fractal_zip_enwik_spiral_inner_preprocess(string $text): ?array
{
	if (!fractal_zip_enwik_spiral_inner_enabled()) {
		return null;
	}
	$spiralCodec = '/srv/http/spiral/src/ResidualCodec.php';
	$qgParse = '/srv/http/quantum_grammar/src/ParseSyntax.php';
	if (!is_file($spiralCodec) || !is_file($qgParse)) {
		return null;
	}
	require_once $qgParse;
	require_once $spiralCodec;
	require_once '/srv/http/spiral/src/SpiralSidecar.php';
	$parseFn = static fn (string $t): array => ParseSyntax::parse($t);
	$packed = ResidualCodec::encodeTextWithMeta($text, $parseFn, 'spiral');
	$reparse = !empty($packed['reparse']);
	$sidecar = SpiralSidecar::pack(
		$packed['meta'],
		$packed['bigram_table'],
		$packed['order2_table'] ?? array(),
		(float) $packed['hit_rate'],
		$reparse,
		$packed['order3_table'] ?? array()
	);
	$meta = array(
		'preprocess' => 'spiral_inner',
		'raw_len' => (int) $packed['raw_len'],
		'spiral_bpc' => ResidualCodec::residualBpc($text, $parseFn, 'spiral'),
		'bigram_bpc' => ResidualCodec::residualBpc($text, null, 'bigram'),
		'geo_hits' => (int) ($packed['geo_hits'] ?? 0),
		'codec_version' => (int) ($packed['codec_version'] ?? 5),
	);
	if (fractal_zip_enwik_spiral_member_fold_enabled()) {
		$foldPath = '/srv/http/spiral/src/SpiralMemberFold.php';
		if (!is_file($foldPath)) {
			return null;
		}
		require_once $foldPath;
		$fold = SpiralMemberFold::pack((string) $packed['blob'], $sidecar, (int) $packed['raw_len']);
		$meta['member_fold'] = true;
		$meta['fold_raw_bytes'] = strlen($fold['blob']);
		return array(
			'payload' => (string) $fold['blob'],
			'sidecar' => array(
				'preprocess' => 'spiral_inner',
				'member_fold' => true,
				'hit_rate' => (float) $packed['hit_rate'],
			),
			'meta' => $meta,
		);
	}
	return array(
		'payload' => (string) $packed['blob'],
		'sidecar' => $sidecar,
		'meta' => $meta,
	);
}

/**
 * Restore text from spiral inner payload (requires sidecar meta from encode).
 *
 * @param array<string,mixed> $sidecar
 */
function fractal_zip_enwik_spiral_inner_restore(string $payload, array $sidecar): string
{
	$foldPath = '/srv/http/spiral/src/SpiralMemberFold.php';
	if (strncmp($payload, "FZSPL\x01", 6) === 0 || !empty($sidecar['member_fold'])) {
		if (!is_file($foldPath)) {
			throw new RuntimeException('spiral member fold codec missing');
		}
		require_once $foldPath;
		return SpiralMemberFold::restoreText($payload);
	}
	$spiralCodec = '/srv/http/spiral/src/ResidualCodec.php';
	if (!is_file($spiralCodec)) {
		throw new RuntimeException('spiral inner codec missing');
	}
	require_once $spiralCodec;
	require_once '/srv/http/spiral/src/SpiralSidecar.php';
	$unpacked = SpiralSidecar::unpack($sidecar);
	return ResidualCodec::decodeText($payload, $unpacked);
}
