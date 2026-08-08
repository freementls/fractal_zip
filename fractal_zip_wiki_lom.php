<?php

declare(strict_types=1);

/**
 * wiki_lom lossless preprocess: wiki→HTML, entity decode, link IDs, templates, URLs, abbrev, LOM tags.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_html.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bio_template_ifs.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';

const FRACTAL_ZIP_WIKI_LOM_MAGIC = "FZWL\x01";
const FRACTAL_ZIP_WIKI_LOM_URL_SENT = "\x1E";
const FRACTAL_ZIP_WIKI_LOM_ABBREV_SENT = "\x1D";
const FRACTAL_ZIP_WIKI_LOM_TAG_OPEN = "\x05";
const FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE = "\x06";

/** @return array<string, bool> */
function fractal_zip_wiki_lom_layer_flags(bool $phda9Wire = false): array
{
	if ($phda9Wire) {
		// phda9: entity decode + collision-free abbrevs when table present.
		return array(
			'wiki_html' => false,
			'entity_decode' => true,
			'link_ids' => false,
			'templates' => false,
			'url_dict' => false,
			'abbrevs' => false,
			'acronyms' => fractal_zip_wiki_lom_cfabb_enabled(),
			'tag_ids' => false,
		);
	}
	return array(
		'wiki_html' => true,
		'entity_decode' => true,
		'link_ids' => true,
		'templates' => false,
		'url_dict' => true,
		'abbrevs' => false,
		'acronyms' => false,
		'tag_ids' => false,
	);
}

function fractal_zip_wiki_lom_phda9_wire_profile_wanted(?string $memberFormat = null): bool
{
	if ($memberFormat !== 'phda9_xml') {
		return false;
	}
	$v = getenv('FRACTAL_ZIP_WIKI_LOM_PHDA9_WIRE_PROFILE');
	if ($v === false) {
		return true;
	}
	$v = strtolower(trim((string) $v));
	return $v !== '0' && $v !== 'false' && $v !== 'off';
}

function fractal_zip_wiki_lom_cfabb_enabled(): bool
{
	return fractal_zip_cfabb_enabled();
}

/** @return array<string, mixed> */
function fractal_zip_wiki_lom_empty_tables(): array
{
	return array(
		'title_to_id' => array(),
		'urls' => array(),
		'tags' => array(),
		'abbrevs_list' => array(),
		'acronyms_list' => array(),
		'templates_list' => array(),
	);
}

/** @param array<string, bool> $flags */
function fractal_zip_wiki_lom_tables_needed(array $flags): bool
{
	return !empty($flags['link_ids']) || !empty($flags['url_dict']) || !empty($flags['tag_ids'])
		|| !empty($flags['abbrevs']) || !empty($flags['acronyms']) || !empty($flags['templates']);
}

function fractal_zip_wiki_lom_phda9_utf8_len(string $wire, int $pos): int
{
	$len = strlen($wire);
	if ($pos >= $len) {
		return 0;
	}
	$b = ord($wire[$pos]);
	if ($b < 0x80) {
		return 1;
	}
	if ($b < 0xC0) {
		return 0;
	}
	if ($b < 0xE0) {
		return ($pos + 1 < $len && (ord($wire[$pos + 1]) & 0xC0) === 0x80) ? 2 : 0;
	}
	if ($b < 0xF0) {
		return ($pos + 2 < $len
			&& (ord($wire[$pos + 1]) & 0xC0) === 0x80
			&& (ord($wire[$pos + 2]) & 0xC0) === 0x80) ? 3 : 0;
	}
	if ($b < 0xF8) {
		return ($pos + 3 < $len
			&& (ord($wire[$pos + 1]) & 0xC0) === 0x80
			&& (ord($wire[$pos + 2]) & 0xC0) === 0x80
			&& (ord($wire[$pos + 3]) & 0xC0) === 0x80) ? 4 : 0;
	}
	return 0;
}

/** phda9-safe ASCII tokens for binary wiki_lom sentinels (~u123~ urls, ~zWx5~ stash). */
function fractal_zip_wiki_lom_phda9_wire_ascii_tokens_encode(string $wire): string
{
	$wire = (string) preg_replace_callback(
		'/' . preg_quote(FRACTAL_ZIP_WIKI_HTML_PROTECT, '/') . '([WXTH])([\x80-\xFF])/',
		static function (array $m): string {
			return '~z' . $m[1] . 'x' . (ord($m[2]) & 0x7F) . '~';
		},
		$wire
	);
	$pos = 0;
	$n = strlen($wire);
	$out = '';
	while ($pos < $n) {
		$ch = $wire[$pos];
		if ($ch === FRACTAL_ZIP_WIKI_LOM_TAG_OPEN || $ch === FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE) {
			$dv = fractal_zip_enwik_decode_varint_u32($wire, $pos + 1);
			if ($dv !== null) {
				$out .= ($ch === FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE ? '~T' : '~t') . (int) $dv[0] . '~';
				$pos = (int) $dv[1];
				continue;
			}
		}
		if ($ch === FRACTAL_ZIP_WIKI_LOM_URL_SENT) {
			$dv = fractal_zip_enwik_decode_varint_u32($wire, $pos + 1);
			if ($dv !== null) {
				$out .= '~u' . (int) $dv[0] . '~';
				$pos = (int) $dv[1];
				continue;
			}
		}
		if ($ch === FRACTAL_ZIP_WIKI_LOM_ABBREV_SENT) {
			$next = ord($wire[$pos + 1] ?? "\x00");
			if ($next >= 0x20 && $next < 0x80) {
				$out .= '~a' . ($next - 0x20) . '~';
				$pos += 2;
				continue;
			}
		}
		$out .= $ch;
		$pos++;
	}
	return $out;
}

function fractal_zip_wiki_lom_phda9_wire_ascii_tokens_decode(string $wire): string
{
	$wire = (string) preg_replace_callback(
		'/~z([WXTH])x(\d+)~/',
		static function (array $m): string {
			$idx = (int) $m[2] & 0x7F;
			return FRACTAL_ZIP_WIKI_HTML_PROTECT . $m[1] . chr(0x80 | $idx);
		},
		$wire
	);
	$wire = (string) preg_replace_callback(
		'/~u(\d+)~/',
		static fn (array $m): string => FRACTAL_ZIP_WIKI_LOM_URL_SENT . fractal_zip_enwik_encode_varint_u32((int) $m[1]),
		$wire
	);
	$wire = (string) preg_replace_callback(
		'/~t(\d+)~/',
		static fn (array $m): string => FRACTAL_ZIP_WIKI_LOM_TAG_OPEN . fractal_zip_enwik_encode_varint_u32((int) $m[1]),
		$wire
	);
	$wire = (string) preg_replace_callback(
		'/~T(\d+)~/',
		static fn (array $m): string => FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE . fractal_zip_enwik_encode_varint_u32((int) $m[1]),
		$wire
	);
	return (string) preg_replace_callback(
		'/~a(\d+)~/',
		static fn (array $m): string => FRACTAL_ZIP_WIKI_LOM_ABBREV_SENT . chr(0x20 + ((int) $m[1] % 96)),
		$wire
	);
}

/** Remaining non-UTF-8-safe bytes → valid UTF-8 PUA U+E000..U+E0FF. */
function fractal_zip_wiki_lom_phda9_wire_pua_escape(string $wire): string
{
	$out = '';
	$len = strlen($wire);
	for ($i = 0; $i < $len; $i++) {
		$b = ord($wire[$i]);
		if ($b === 0x09 || $b === 0x0A || $b === 0x0D || ($b >= 0x20 && $b < 0x7F)) {
			$out .= $wire[$i];
			continue;
		}
		if ($b >= 0x80) {
			$charLen = fractal_zip_wiki_lom_phda9_utf8_len($wire, $i);
			if ($charLen > 1) {
				$seq = substr($wire, $i, $charLen);
				if (preg_match('//u', $seq) === 1) {
					$out .= $seq;
					$i += $charLen - 1;
					continue;
				}
			}
		}
		$enc = mb_chr(0xE000 + $b, 'UTF-8');
		if (!is_string($enc) || $enc === '') {
			throw new RuntimeException('wiki_lom phda9 wire escape failed for byte 0x' . sprintf('%02X', $b));
		}
		$out .= $enc;
	}
	return $out;
}

function fractal_zip_wiki_lom_phda9_wire_pua_unescape(string $wire): string
{
	$out = '';
	$len = strlen($wire);
	for ($i = 0; $i < $len; $i++) {
		$b = ord($wire[$i]);
		if ($b < 0x80) {
			$out .= $wire[$i];
			continue;
		}
		$charLen = fractal_zip_wiki_lom_phda9_utf8_len($wire, $i);
		if ($charLen <= 1) {
			$out .= $wire[$i];
			continue;
		}
		$seq = substr($wire, $i, $charLen);
		$cp = mb_ord($seq, 'UTF-8');
		if ($cp >= 0xE000 && $cp <= 0xE0FF) {
			$out .= chr($cp - 0xE000);
			$i += $charLen - 1;
			continue;
		}
		$out .= $seq;
		$i += $charLen - 1;
	}
	return $out;
}

function fractal_zip_wiki_lom_phda9_wire_escape(string $wire): string
{
	return fractal_zip_wiki_lom_phda9_wire_pua_escape(
		fractal_zip_wiki_lom_phda9_wire_ascii_tokens_encode($wire)
	);
}

function fractal_zip_wiki_lom_phda9_wire_unescape(string $wire): string
{
	return fractal_zip_wiki_lom_phda9_wire_ascii_tokens_decode(
		fractal_zip_wiki_lom_phda9_wire_pua_unescape($wire)
	);
}

function fractal_zip_wiki_lom_normalize_title(string $title): string
{
	return str_replace(' ', '_', trim($title));
}

/**
 * @param list<array{title: string, start: int, len: int}> $pageRefs
 * @return array<string, int>
 */
function fractal_zip_wiki_lom_mine_title_to_id(array $pageRefs, string $blob): array
{
	$map = array();
	foreach ($pageRefs as $ref) {
		$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
		if (preg_match('/<title>([^<]*)<\/title>/', $page, $m) !== 1) {
			continue;
		}
		$title = html_entity_decode((string) $m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
		if (preg_match('/<id>(\d+)<\/id>/', $page, $im) !== 1) {
			continue;
		}
		$id = (int) $im[1];
		$map[$title] = $id;
		$map[fractal_zip_wiki_lom_normalize_title($title)] = $id;
	}
	return $map;
}

/** @return list<string> */
function fractal_zip_wiki_lom_mine_urls(string $text, int $maxEntries = 4096): array
{
	$counts = array();
	$patterns = array(
		'/<a\s+href="(https?:\/\/[^"]+)"/i',
		'/\[(https?:\/\/[^\]\s]+)/i',
		'/(https?:\/\/[^\s\]<>"\']+)/i',
	);
	foreach ($patterns as $re) {
		if (preg_match_all($re, $text, $m) < 1) {
			continue;
		}
		foreach ($m[1] as $url) {
			$url = (string) $url;
			if (!isset($counts[$url])) {
				$counts[$url] = 0;
			}
			$counts[$url]++;
		}
	}
	arsort($counts, SORT_NUMERIC);
	return array_slice(array_keys($counts), 0, $maxEntries);
}

/** @return list<string> */
function fractal_zip_wiki_lom_mine_tag_names(string $text, int $maxTags = 64): array
{
	$counts = array();
	if (preg_match_all('/<\/?([a-zA-Z][a-zA-Z0-9-]*)\b/', $text, $m) > 0) {
		foreach ($m[1] as $tag) {
			$tag = strtolower((string) $tag);
			if ($tag === 'wiki-t' || $tag === 'a') {
				continue;
			}
			if (!isset($counts[$tag])) {
				$counts[$tag] = 0;
			}
			$counts[$tag]++;
		}
	}
	arsort($counts, SORT_NUMERIC);
	return array_slice(array_keys($counts), 0, $maxTags);
}

/**
 * Load or mine collision-free abbreviation table for wiki_lom wire.
 *
 * @return list<array{phrase: string, acronym: string, token: string, count?: int}>
 */
function fractal_zip_wiki_lom_load_cfabb_list(string $decodedText, array $opts = array()): array
{
	if (!fractal_zip_wiki_lom_cfabb_enabled()) {
		return array();
	}
	if (fractal_zip_cfabb_inline_enabled()) {
		$cached = array();
	} else {
		$cached = fractal_zip_cfabb_load_json();
	}
	if ($cached === array()) {
		$cached = fractal_zip_cfabb_mine($decodedText, array(
			'min_words' => 2,
			'max_words' => 10,
			'max_chars' => 100,
			'min_count' => (int) ($opts['min_count'] ?? 4),
			'max_entries' => (int) ($opts['max_acronyms'] ?? 4096),
			'corpus_pages' => (int) ($opts['corpus_pages'] ?? 12041),
			'phda9_gate_text' => (string) ($opts['phda9_gate_text'] ?? ''),
		));
	} elseif (!empty($opts['phda9_gate_text']) && fractal_zip_cfabb_phda9_gate_enabled()
		&& fractal_zip_cfabb_phda9_filter_on_load()) {
		$cached = fractal_zip_cfabb_filter_phda9_greedy((string) $opts['phda9_gate_text'], $cached, array_merge($opts, array(
			'phda9_greedy_max' => (int) ($opts['max_acronyms'] ?? 4096),
		)));
	}
	$out = array();
	foreach ($cached as $row) {
		$out[] = array(
			'phrase' => (string) ($row['phrase'] ?? ''),
			'acronym' => (string) ($row['token'] ?? ''),
			'token' => (string) ($row['token'] ?? ''),
			'count' => (int) ($row['count'] ?? 0),
		);
	}
	if (fractal_zip_cfabb_inline_enabled()) {
		fractal_zip_cfabb_inline_cache_store($out);
	}
	return $out;
}

/**
 * Mine collision-free phrase abbreviations (delegates to cfabb; legacy name kept).
 *
 * @return list<array{phrase: string, acronym: string, token: string, count: int}>
 */
function fractal_zip_wiki_lom_mine_acronyms(
	string $text,
	int $maxEntries = 128,
	int $minCount = 4,
	int $minWords = 2,
	int $maxWords = 10
): array {
	return fractal_zip_wiki_lom_load_cfabb_list($text, array(
		'max_acronyms' => $maxEntries,
		'min_count' => $minCount,
		'min_words' => $minWords,
		'max_words' => $maxWords,
	));
}

/** @param list<array{phrase: string, token?: string, acronym?: string}> $entries */
function fractal_zip_wiki_lom_apply_acronyms(string $text, array $entries): string
{
	return fractal_zip_cfabb_apply($text, $entries);
}

/** @param list<array{phrase: string, token?: string, acronym?: string}> $entries */
function fractal_zip_wiki_lom_undo_acronyms(string $text, array $entries): string
{
	return fractal_zip_cfabb_undo($text, $entries);
}

/** @deprecated Use fractal_zip_cfabb_corpus_has_token */
function fractal_zip_wiki_lom_corpus_has_token(string $corpus, string $token): bool
{
	return fractal_zip_cfabb_corpus_has_token($corpus, $token);
}

/** @return list<string> */
function fractal_zip_wiki_lom_mine_abbrevs(string $text, int $maxEntries = 256, int $minCount = 8): array
{
	$counts = array();
	foreach (fractal_zip_enwik_text_segment_implicit_space($text) as $seg) {
		if (($seg['type'] ?? '') !== 'word') {
			continue;
		}
		$w = (string) ($seg['text'] ?? '');
		if (strlen($w) < 4) {
			continue;
		}
		if (!isset($counts[$w])) {
			$counts[$w] = 0;
		}
		$counts[$w]++;
	}
	$rows = array();
	foreach ($counts as $w => $c) {
		if ($c >= $minCount) {
			$rows[] = array('w' => $w, 'c' => $c);
		}
	}
	usort($rows, static fn (array $a, array $b): int => ($b['c'] <=> $a['c']) ?: (strlen($b['w']) <=> strlen($a['w'])));
	$out = array();
	$used = array();
	foreach (array_slice($rows, 0, $maxEntries * 2) as $row) {
		$w = (string) $row['w'];
		for ($n = 1; $n <= 3; $n++) {
			$tok = FRACTAL_ZIP_WIKI_LOM_ABBREV_SENT . chr(0x20 + $n);
			if (!str_contains($text, $tok) && !isset($used[$tok])) {
				$out[] = $w;
				$used[$tok] = true;
				break;
			}
		}
		if (count($out) >= $maxEntries) {
			break;
		}
	}
	return $out;
}

/**
 * Stable up-to-2-pass entity decode for preserve-text / HTML wire.
 *
 * Peels mixed single/double layers: &amp;quot; -> &quot; -> ", &amp;#233; -> &#233; -> é.
 */
function fractal_zip_wiki_lom_protect_skip_len(string $text, int $pos): int
{
	$plen = strlen(FRACTAL_ZIP_WIKI_HTML_PROTECT);
	if ($pos + $plen > strlen($text) || substr($text, $pos, $plen) !== FRACTAL_ZIP_WIKI_HTML_PROTECT) {
		return 0;
	}
	$after = $pos + $plen;
	if ($after >= strlen($text)) {
		return $plen;
	}
	$kind = $text[$after];
	if ($kind === 'W' || $kind === 'X' || $kind === 'T' || $kind === 'H') {
		return $plen + 2;
	}
	return $plen + 1;
}

function fractal_zip_wiki_lom_entity_decode_text(string $text, int $maxPasses = 2): string
{
	$work = $text;
	for ($p = 0; $p < $maxPasses; $p++) {
		$next = html_entity_decode($work, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		if ($next === $work) {
			break;
		}
		$work = $next;
	}
	return $work;
}

/** UTF-8 codepoints > 127 -> &#decimal; (single-& numeric form). */
function fractal_zip_wiki_lom_encode_multibyte_numeric(string $text): string
{
	if ($text === '') {
		return '';
	}
	$out = '';
	$len = strlen($text);
	for ($i = 0; $i < $len; $i++) {
		$b = ord($text[$i]);
		if ($b < 0x80) {
			$out .= $text[$i];
			continue;
		}
		$cp = null;
		$seqLen = 1;
		if (($b & 0xE0) === 0xC0 && $i + 1 < $len) {
			$cp = (($b & 0x1F) << 6) | (ord($text[$i + 1]) & 0x3F);
			$seqLen = 2;
		} elseif (($b & 0xF0) === 0xE0 && $i + 2 < $len) {
			$cp = (($b & 0x0F) << 12) | ((ord($text[$i + 1]) & 0x3F) << 6) | (ord($text[$i + 2]) & 0x3F);
			$seqLen = 3;
		} elseif (($b & 0xF8) === 0xF0 && $i + 3 < $len) {
			$cp = (($b & 0x07) << 18) | ((ord($text[$i + 1]) & 0x3F) << 12)
				| ((ord($text[$i + 2]) & 0x3F) << 6) | (ord($text[$i + 3]) & 0x3F);
			$seqLen = 4;
		}
		if ($cp === null || $cp < 128) {
			$out .= $text[$i];
			continue;
		}
		$out .= '&#' . $cp . ';';
		$i += $seqLen - 1;
	}
	return $out;
}

/** &#…; / &#x…; -> &amp;#…; / &amp;#x…; (double-& numeric layer only). */
function fractal_zip_wiki_lom_double_encode_numeric_entities(string $text): string
{
	return (string) preg_replace('/&(#[0-9]+;|#x[0-9a-fA-F]+;)/', '&amp;$1', $text);
}

/** Single XML encode for literal &, <, >, " (not part of existing &…; tokens). */
function fractal_zip_wiki_lom_entity_encode_html_specials(string $text): string
{
	$out = '';
	$len = strlen($text);
	for ($i = 0; $i < $len; $i++) {
		if ($text[$i] === '&' && preg_match('/^&(?:#\d+|#x[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]*);/', substr($text, $i), $m) === 1) {
			$out .= (string) $m[0];
			$i += strlen($m[0]) - 1;
			continue;
		}
		$byte = $text[$i];
		if ($byte === '&') {
			$out .= '&amp;';
		} elseif ($byte === '<') {
			$out .= '&lt;';
		} elseif ($byte === '>') {
			$out .= '&gt;';
		} elseif ($byte === '"') {
			$out .= '&quot;';
		} else {
			$out .= $byte;
		}
	}
	return $out;
}

/**
 * Stateless asymmetric encode (compress-oriented): multibyte double-numeric, HTML specials single.
 *
 * Byte-identical restore on mixed enwik8 wire requires manifest-based {@see fractal_zip_wiki_lom_entity_encode_text}.
 */
function fractal_zip_wiki_lom_entity_encode_text_stateless(string $text): string
{
	$work = fractal_zip_wiki_lom_encode_multibyte_numeric($text);
	$work = fractal_zip_wiki_lom_double_encode_numeric_entities($work);
	return fractal_zip_wiki_lom_entity_encode_html_specials($work);
}

/**
 * Decode entities and record original wire spans for byte-identical restore.
 *
 * @return array{text: string, manifest: list<array{off: int, wire: string, decoded: string}>}
 */
function fractal_zip_wiki_lom_entity_decode_with_manifest(string $text): array
{
	$manifest = array();
	$out = '';
	$pos = 0;
	$n = strlen($text);
	while ($pos < $n) {
		$skip = fractal_zip_wiki_lom_protect_skip_len($text, $pos);
		if ($skip > 0) {
			$out .= substr($text, $pos, $skip);
			$pos += $skip;
			continue;
		}
		if ($text[$pos] === '&' && preg_match(
			'/^&(?:amp;)?(?:#x?[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]*);/',
			substr($text, $pos),
			$m
		) === 1) {
			$wire = (string) $m[0];
			$decoded = fractal_zip_wiki_lom_entity_decode_text($wire);
			$manifest[] = array('off' => strlen($out), 'wire' => $wire, 'decoded' => $decoded);
			$out .= $decoded;
			$pos += strlen($wire);
			continue;
		}
		$out .= $text[$pos];
		$pos++;
	}
	return array('text' => $out, 'manifest' => $manifest);
}

/**
 * Restore entities using offset manifest (wire-form for peeled tokens) plus single HTML-special encode.
 *
 * Asymmetric rule: manifest restores each entity's original wire (&quot; stays single, &amp;#233; stays double);
 * bare literal &, <, >, " in decoded text get single-encoded only.
 *
 * @param list<array{off: int, wire: string, decoded: string}> $manifest
 */
function fractal_zip_wiki_lom_entity_encode_text(string $text, array $manifest = array()): string
{
	$out = '';
	$pos = 0;
	$n = strlen($text);
	$mi = 0;
	$mc = count($manifest);
	while ($pos < $n) {
		$skip = fractal_zip_wiki_lom_protect_skip_len($text, $pos);
		if ($skip > 0) {
			$out .= substr($text, $pos, $skip);
			$pos += $skip;
			continue;
		}
		if ($mi < $mc && (int) ($manifest[$mi]['off'] ?? -1) === $pos) {
			$entry = $manifest[$mi++];
			$decoded = (string) ($entry['decoded'] ?? '');
			$out .= (string) ($entry['wire'] ?? $decoded);
			$pos += strlen($decoded);
			continue;
		}
		$byte = $text[$pos];
		if ($byte === '&') {
			$out .= '&amp;';
		} elseif ($byte === '<') {
			$out .= '&lt;';
		} elseif ($byte === '>') {
			$out .= '&gt;';
		} elseif ($byte === '"') {
			$out .= '&quot;';
		} else {
			$out .= $byte;
		}
		$pos++;
	}
	return $out;
}

/** @param list<array{wire: string, decoded: string}>|null $manifest */
function fractal_zip_wiki_lom_entity_decode(string $text, ?array &$manifest = null): string
{
	$result = fractal_zip_wiki_lom_entity_decode_with_manifest($text);
	$manifest = $result['manifest'];
	return $result['text'];
}

/** @param list<array{wire: string, decoded: string}> $manifest */
function fractal_zip_wiki_lom_entity_encode(string $text, array $manifest = array()): string
{
	return fractal_zip_wiki_lom_entity_encode_text($text, $manifest);
}

function fractal_zip_wiki_lom_stash_key(string $kind, int $idx): string
{
	// Index byte must not collide with URL (0x1E), abbrev (0x1D), or protect (0x1F) sentinels.
	return FRACTAL_ZIP_WIKI_HTML_PROTECT . $kind . chr(0x80 | ($idx & 0x7F));
}

function fractal_zip_wiki_lom_unstash_map(string $text, array $stash): string
{
	if ($stash === array()) {
		return $text;
	}
	$keys = array_keys($stash);
	usort($keys, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	foreach ($keys as $key) {
		$text = str_replace($key, (string) $stash[$key], $text);
	}
	return $text;
}

function fractal_zip_wiki_lom_stash_wikitables(string $text, array &$stash): string
{
	foreach (fractal_zip_wiki_html_extract_wikitables($text) as $table) {
		$key = fractal_zip_wiki_lom_stash_key('W', count($stash));
		if (!isset($stash[$key]) && str_contains($text, $table)) {
			$stash[$key] = $table;
			$text = str_replace($table, $key, $text);
		}
	}
	return $text;
}

function fractal_zip_wiki_lom_unstash_wikitables(string $text, array $stash): string
{
	return fractal_zip_wiki_lom_unstash_map($text, $stash);
}

/**
 * Pattern A: well-closed entity-encoded HTML table. Pattern B: wiki-style (implicit closes).
 */
function fractal_zip_wiki_lom_classify_entity_html_table(string $wireTable): string
{
	$openTr = preg_match_all('/&lt;tr\b/i', $wireTable);
	$closeTr = substr_count($wireTable, '&lt;/tr&gt;');
	$openTd = preg_match_all('/&lt;td\b/i', $wireTable);
	$closeTd = substr_count($wireTable, '&lt;/td&gt;');
	// Pattern B: implicit row/cell closes (e.g. Acquis — zero close tags).
	if ($closeTr === 0 || ($openTd > 0 && $closeTd === 0)) {
		return 'B';
	}
	return 'A';
}

function fractal_zip_wiki_lom_normalize_entity_table_b(string $wireTable): string
{
	if (!preg_match_all('/&lt;(\/)?(table|tr|td|th)\b([\s\S]*?)&gt;/i', $wireTable, $tags, PREG_OFFSET_CAPTURE)) {
		return $wireTable;
	}
	$out = '';
	$lastPos = 0;
	$cellStack = array();
	$inRow = false;
	$tagCount = count($tags[0]);
	for ($ti = 0; $ti < $tagCount; $ti++) {
		$full = (string) $tags[0][$ti][0];
		$pos = (int) $tags[0][$ti][1];
		$isClose = (string) ($tags[1][$ti][0] ?? '') === '/';
		$name = strtolower((string) ($tags[2][$ti][0] ?? ''));
		$out .= substr($wireTable, $lastPos, $pos - $lastPos);
		$lastPos = $pos + strlen($full);
		if ($isClose) {
			if ($name === 'table') {
				while ($cellStack !== array()) {
					$cell = array_pop($cellStack);
					$out .= '&lt;/' . $cell . '&gt;';
				}
				if ($inRow) {
					$out .= '&lt;/tr&gt;';
					$inRow = false;
				}
			} elseif ($name === 'tr') {
				while ($cellStack !== array()) {
					$cell = array_pop($cellStack);
					$out .= '&lt;/' . $cell . '&gt;';
				}
				$inRow = false;
			} elseif ($name === 'td' || $name === 'th') {
				if ($cellStack !== array()) {
					array_pop($cellStack);
				}
			}
			$out .= $full;
			continue;
		}
		if ($name === 'tr') {
			while ($cellStack !== array()) {
				$cell = array_pop($cellStack);
				$out .= '&lt;/' . $cell . '&gt;';
			}
			if ($inRow) {
				$out .= '&lt;/tr&gt;';
			}
			$inRow = true;
		} elseif ($name === 'td' || $name === 'th') {
			if ($cellStack !== array()) {
				$cell = array_pop($cellStack);
				$out .= '&lt;/' . $cell . '&gt;';
			}
			$cellStack[] = $name;
		} elseif ($name === 'table') {
			// table open — nothing to close yet
		}
		$out .= $full;
	}
	$out .= substr($wireTable, $lastPos);
	return $out;
}

/**
 * Detect entity-encoded HTML tables, normalize pattern B, record reversible fixups.
 *
 * @param list<array{pattern: string, orig: string, normalized: string}> $fixup
 */
function fractal_zip_wiki_lom_prepare_entity_html_tables(string $text, array &$fixup): string
{
	if (preg_match_all('/&lt;table[\s\S]*?&gt;[\s\S]*?&lt;\/table&gt;/i', $text, $matches, PREG_OFFSET_CAPTURE) < 1) {
		return $text;
	}
	$tables = $matches[0];
	usort($tables, static fn (array $a, array $b): int => $b[1] <=> $a[1]);
	foreach ($tables as $hit) {
		$wireTable = (string) $hit[0];
		$offset = (int) $hit[1];
		if ($wireTable === '') {
			continue;
		}
		$pattern = fractal_zip_wiki_lom_classify_entity_html_table($wireTable);
		if ($pattern !== 'B') {
			continue;
		}
		$normalized = fractal_zip_wiki_lom_normalize_entity_table_b($wireTable);
		if ($normalized === $wireTable) {
			continue;
		}
		$fixup[] = array(
			'pattern' => 'B',
			'orig' => $wireTable,
			'normalized' => $normalized,
		);
		$text = substr($text, 0, $offset) . $normalized . substr($text, $offset + strlen($wireTable));
	}
	return $text;
}

/** @param list<array{pattern: string, orig: string, normalized: string}> $fixup */
function fractal_zip_wiki_lom_restore_entity_html_tables(string $text, array $fixup): string
{
	if ($fixup === array()) {
		return $text;
	}
	$keys = array();
	foreach ($fixup as $entry) {
		$norm = (string) ($entry['normalized'] ?? '');
		if ($norm !== '') {
			$keys[$norm] = (string) ($entry['orig'] ?? '');
		}
	}
	uksort($keys, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	foreach ($keys as $norm => $orig) {
		if ($orig !== '' && $norm !== $orig) {
			$text = str_replace($norm, $orig, $text);
		}
	}
	return $text;
}

/** @return list<array{0: int, 1: int}> */
function fractal_zip_wiki_lom_entity_table_spans(string $text): array
{
	$spans = array();
	if (preg_match_all('/&lt;table[\s\S]*?&gt;[\s\S]*?&lt;\/table&gt;/i', $text, $matches, PREG_OFFSET_CAPTURE) < 1) {
		return $spans;
	}
	foreach ($matches[0] as $hit) {
		$start = (int) $hit[1];
		$spans[] = array($start, $start + strlen((string) $hit[0]));
	}
	return $spans;
}

function fractal_zip_wiki_lom_span_contains(array $spans, int $pos, int $len): bool
{
	$end = $pos + $len;
	foreach ($spans as $span) {
		if ($pos >= $span[0] && $end <= $span[1]) {
			return true;
		}
	}
	return false;
}

function fractal_zip_wiki_lom_stash_decoded_html_tables(string $text, array &$stash): string
{
	if (preg_match_all('/<table[\s\S]*?<\/table>/i', $text, $matches, PREG_OFFSET_CAPTURE) < 1) {
		return $text;
	}
	$tables = $matches[0];
	usort($tables, static fn (array $a, array $b): int => $b[1] <=> $a[1]);
	foreach ($tables as $hit) {
		$table = (string) $hit[0];
		$offset = (int) $hit[1];
		if ($table === '') {
			continue;
		}
		$key = fractal_zip_wiki_lom_stash_key('H', count($stash));
		if (!isset($stash[$key])) {
			$stash[$key] = $table;
			$text = substr($text, 0, $offset) . $key . substr($text, $offset + strlen($table));
		}
	}
	return $text;
}

function fractal_zip_wiki_lom_unstash_decoded_html_tables(string $text, array $stash): string
{
	return fractal_zip_wiki_lom_unstash_map($text, $stash);
}

function fractal_zip_wiki_lom_stash_xml_literals(string $text, array &$stash): string
{
	$tableSpans = fractal_zip_wiki_lom_entity_table_spans($text);
	$patterns = array(
		'/&lt;math[\s\S]*?&gt;[\s\S]*?&lt;\/math&gt;/i',
		'/&lt;([a-zA-Z][a-zA-Z0-9-]*)[\s\S]*?&gt;[\s\S]*?&lt;\/\1&gt;/',
	);
	$hits = array();
	foreach ($patterns as $re) {
		if (preg_match_all($re, $text, $m, PREG_OFFSET_CAPTURE) < 1) {
			continue;
		}
		foreach ($m[0] as $hit) {
			$literal = (string) $hit[0];
			$pos = (int) $hit[1];
			if ($literal === '' || fractal_zip_wiki_lom_span_contains($tableSpans, $pos, strlen($literal))) {
				continue;
			}
			$hits[] = array('literal' => $literal, 'pos' => $pos);
		}
	}
	usort($hits, static fn (array $a, array $b): int => strlen($b['literal']) <=> strlen($a['literal']));
	$used = array();
	foreach ($hits as $hit) {
		$literal = $hit['literal'];
		if (isset($used[$literal])) {
			continue;
		}
		$key = fractal_zip_wiki_lom_stash_key('X', count($stash));
		if (!isset($stash[$key]) && str_contains($text, $literal)) {
			$stash[$key] = $literal;
			$text = str_replace($literal, $key, $text);
			$used[$literal] = true;
		}
	}
	return $text;
}

function fractal_zip_wiki_lom_unstash_xml_literals(string $text, array $stash): string
{
	return fractal_zip_wiki_lom_unstash_map($text, $stash);
}

function fractal_zip_wiki_lom_stash_templates(string $text, array &$stash): string
{
	foreach (fractal_zip_wiki_html_extract_templates($text) as $tpl) {
		$key = fractal_zip_wiki_lom_stash_key('T', count($stash));
		if (!isset($stash[$key])) {
			$stash[$key] = $tpl;
			$text = str_replace($tpl, $key, $text);
		}
	}
	return $text;
}

function fractal_zip_wiki_lom_unstash_templates(string $text, array $stash): string
{
	return fractal_zip_wiki_lom_unstash_map($text, $stash);
}

function fractal_zip_wiki_lom_apply_link_ids(string $text, array $titleToId): string
{
	return (string) preg_replace_callback(
		'/<a\s+href="([^"]+)">/',
		static function (array $m) use ($titleToId): string {
			$href = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
			if (str_starts_with($href, 'id:')) {
				return $m[0];
			}
			if (preg_match('#^https?://#i', $href) === 1) {
				return $m[0];
			}
			$id = $titleToId[$href] ?? $titleToId[fractal_zip_wiki_lom_normalize_title($href)] ?? null;
			if ($id === null) {
				return $m[0];
			}
			return '<a href="id:' . $id . '">';
		},
		$text
	);
}

function fractal_zip_wiki_lom_undo_link_ids(string $text, array $idToTitle): string
{
	return (string) preg_replace_callback(
		'/<a\s+href="id:(\d+)">/',
		static function (array $m) use ($idToTitle): string {
			$id = (int) $m[1];
			$title = $idToTitle[$id] ?? ('id:' . $id);
			return '<a href="' . htmlspecialchars($title, ENT_QUOTES, 'UTF-8') . '">';
		},
		$text
	);
}

/** @param list<string> $urls */
function fractal_zip_wiki_lom_apply_url_dict(string $text, array $urls): string
{
	if ($urls === array()) {
		return $text;
	}
	$tokFor = static function (int $i): string {
		return FRACTAL_ZIP_WIKI_LOM_URL_SENT . fractal_zip_enwik_encode_varint_u32($i);
	};
	$lookup = array();
	foreach ($urls as $i => $url) {
		$url = (string) $url;
		if ($url === '') {
			continue;
		}
		$lookup[$url] = $tokFor((int) $i);
		$amp = str_replace('&amp;', '&', $url);
		if ($amp !== $url) {
			$lookup[$amp] = $lookup[$url];
		}
		$ent = str_replace('&', '&amp;', $url);
		if ($ent !== $url) {
			$lookup[$ent] = $lookup[$url];
		}
	}
	uksort($lookup, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	$text = (string) preg_replace_callback(
		'/href="([^"]+)"/',
		static function (array $m) use ($lookup): string {
			$url = (string) $m[1];
			return isset($lookup[$url]) ? 'href="' . $lookup[$url] . '"' : $m[0];
		},
		$text
	);
	return (string) preg_replace_callback(
		'/\[(https?:\/\/[^\]\s]+)\s/',
		static function (array $m) use ($lookup): string {
			$url = (string) $m[1];
			return isset($lookup[$url]) ? '[' . $lookup[$url] . ' ' : $m[0];
		},
		$text
	);
}

/** @param list<string> $urls */
function fractal_zip_wiki_lom_undo_url_dict(string $text, array $urls): string
{
	$pos = 0;
	$n = strlen($text);
	$out = '';
	while ($pos < $n) {
	if ($text[$pos] === FRACTAL_ZIP_WIKI_LOM_URL_SENT) {
		if ($pos >= 5 && substr($text, $pos - 5, 4) === FRACTAL_ZIP_WIKI_HTML_PROTECT) {
			$kind = $text[$pos - 1];
			if ($kind === 'W' || $kind === 'X' || $kind === 'T' || $kind === 'H') {
				$out .= $text[$pos];
				$pos++;
				continue;
			}
		}
		$dv = fractal_zip_enwik_decode_varint_u32($text, $pos + 1);
			if ($dv === null) {
				$out .= $text[$pos++];
				continue;
			}
			$id = (int) $dv[0];
			$pos = (int) $dv[1];
			$out .= (string) ($urls[$id] ?? (FRACTAL_ZIP_WIKI_LOM_URL_SENT . $id));
			continue;
		}
		$out .= $text[$pos++];
	}
	return $out;
}

/** @param list<string> $abbrevs */
function fractal_zip_wiki_lom_apply_abbrevs(string $text, array $abbrevs): string
{
	usort($abbrevs, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));
	foreach ($abbrevs as $i => $word) {
		$tok = FRACTAL_ZIP_WIKI_LOM_ABBREV_SENT . chr(0x20 + ($i % 96));
		if (!str_contains($text, $word)) {
			continue;
		}
		$text = preg_replace('/\b' . preg_quote($word, '/') . '\b/', $tok, $text) ?? $text;
	}
	return $text;
}

/** @param list<string> $abbrevs */
function fractal_zip_wiki_lom_undo_abbrevs(string $text, array $abbrevs): string
{
	foreach ($abbrevs as $i => $word) {
		$tok = FRACTAL_ZIP_WIKI_LOM_ABBREV_SENT . chr(0x20 + ($i % 96));
		$text = str_replace($tok, $word, $text);
	}
	return $text;
}

/** @param list<string> $tags */
function fractal_zip_wiki_lom_apply_tag_ids(string $text, array $tags): string
{
	if ($tags === array()) {
		return $text;
	}
	$tagToId = array();
	foreach ($tags as $i => $tag) {
		$tagToId[strtolower($tag)] = (int) $i;
	}
	return (string) preg_replace_callback(
		'/<(\/?)([a-zA-Z][a-zA-Z0-9-]*)\b([^>]*)>/',
		static function (array $m) use ($tagToId): string {
			$close = $m[1] === '/';
			$name = strtolower((string) $m[2]);
			if ($name === 'wiki-t' || $name === 'a' || !isset($tagToId[$name])) {
				return $m[0];
			}
			$id = (int) $tagToId[$name];
			return ($close ? FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE : FRACTAL_ZIP_WIKI_LOM_TAG_OPEN)
				. fractal_zip_enwik_encode_varint_u32($id)
				. ($close ? '' : (string) $m[3]);
		},
		$text
	);
}

/** @param list<string> $tags */
function fractal_zip_wiki_lom_undo_tag_ids(string $text, array $tags): string
{
	$pos = 0;
	$n = strlen($text);
	$out = '';
	while ($pos < $n) {
		$ch = $text[$pos];
		if ($ch === FRACTAL_ZIP_WIKI_LOM_TAG_OPEN || $ch === FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE) {
			$dv = fractal_zip_enwik_decode_varint_u32($text, $pos + 1);
			if ($dv === null) {
				$out .= $text[$pos++];
				continue;
			}
			$id = (int) $dv[0];
			$pos = (int) $dv[1];
			$name = (string) ($tags[$id] ?? 'span');
			if ($ch === FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE) {
				$out .= '</' . $name . '>';
				continue;
			}
			$rest = '';
			while ($pos < $n && $text[$pos] !== FRACTAL_ZIP_WIKI_LOM_TAG_OPEN && $text[$pos] !== FRACTAL_ZIP_WIKI_LOM_TAG_CLOSE) {
				if ($text[$pos] === '<') {
					break;
				}
				$rest .= $text[$pos++];
			}
			$out .= '<' . $name . $rest . '>';
			continue;
		}
		$out .= $text[$pos++];
	}
	return $out;
}

function fractal_zip_wiki_lom_sweeper_decode_entities(string $html): string
{
	$sweeperPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'sweeper' . DIRECTORY_SEPARATOR . 'retidy.php';
	if (!is_file($sweeperPath)) {
		$sweeperPath = '/srv/http/sweeper/retidy.php';
	}
	if (!is_file($sweeperPath)) {
		return fractal_zip_wiki_lom_entity_decode($html);
	}
	require_once $sweeperPath;
	$rt = new ReTidy('minimal');
	$wrapped = '<html><body>' . $html . '</body></html>';
	$rt->setCode($wrapped);
	if (method_exists($rt, 'decode_character_entities')) {
		$ref = new ReflectionMethod($rt, 'decode_character_entities');
		$ref->setAccessible(true);
		$ref->invoke($rt);
	}
	$code = $rt->getCode();
	if (!is_string($code)) {
		return fractal_zip_wiki_lom_entity_decode($html);
	}
	if (preg_match('/<body[^>]*>([\s\S]*)<\/body>/i', $code, $m) === 1) {
		return (string) $m[1];
	}
	return fractal_zip_wiki_lom_entity_decode($html);
}

/**
 * @param array<string, mixed> $opts
 * @return array{payload: string, sidecar: array<string, mixed>, meta: array<string, mixed>}
 */
function fractal_zip_wiki_lom_preprocess(string $text, array $opts = array()): array
{
	$flags = array(
		'wiki_html' => !array_key_exists('wiki_html', $opts) || !empty($opts['wiki_html']),
		'entity_decode' => !array_key_exists('entity_decode', $opts) || !empty($opts['entity_decode']),
		'link_ids' => !array_key_exists('link_ids', $opts) || !empty($opts['link_ids']),
		'templates' => !empty($opts['templates']),
		'url_dict' => !array_key_exists('url_dict', $opts) || !empty($opts['url_dict']),
		'abbrevs' => !empty($opts['abbrevs']),
		'acronyms' => !empty($opts['acronyms']),
		'tag_ids' => !empty($opts['tag_ids']),
		'sweeper' => !empty($opts['sweeper']),
	);
	$titleToId = is_array($opts['title_to_id'] ?? null) ? $opts['title_to_id'] : array();
	$urls = is_array($opts['urls'] ?? null) ? array_values($opts['urls']) : array();
	$abbrevs = is_array($opts['abbrevs_list'] ?? null) ? array_values($opts['abbrevs_list']) : array();
	$acronyms = is_array($opts['acronyms_list'] ?? null) ? array_values($opts['acronyms_list']) : array();
	$tags = is_array($opts['tags'] ?? null) ? array_values($opts['tags']) : array();
	$templates = $opts['templates_list'] ?? null;

	$wire = $text;
	$entityManifest = array();
	$xmlLiteralStash = array();
	$wikitableStash = array();
	$htmlTableFixup = array();
	$htmlTableDecodedStash = array();
	if ($flags['entity_decode']) {
		$wire = fractal_zip_wiki_lom_stash_wikitables($wire, $wikitableStash);
		$wire = fractal_zip_wiki_lom_prepare_entity_html_tables($wire, $htmlTableFixup);
		$wire = fractal_zip_wiki_lom_stash_xml_literals($wire, $xmlLiteralStash);
		if ($flags['sweeper']) {
			$wire = fractal_zip_wiki_lom_sweeper_decode_entities($wire);
		} else {
			$wire = fractal_zip_wiki_lom_entity_decode($wire, $entityManifest);
		}
	}
	if ($flags['wiki_html'] && $flags['entity_decode']) {
		$wire = fractal_zip_wiki_lom_stash_decoded_html_tables($wire, $htmlTableDecodedStash);
	}
	if ($flags['wiki_html']) {
		$wire = fractal_zip_wiki_html_encode($wire);
	}
	if ($flags['link_ids'] && $titleToId !== array()) {
		$wire = fractal_zip_wiki_lom_apply_link_ids($wire, $titleToId);
	}
	if ($flags['url_dict'] && $urls !== array()) {
		$wire = fractal_zip_wiki_lom_apply_url_dict($wire, $urls);
	}
	if ($flags['tag_ids'] && $tags !== array()) {
		$wire = fractal_zip_wiki_lom_apply_tag_ids($wire, $tags);
	}
	if ($flags['abbrevs'] && $abbrevs !== array()) {
		$wire = fractal_zip_wiki_lom_apply_abbrevs($wire, $abbrevs);
	}
	if ($flags['acronyms'] && $acronyms !== array()) {
		$wire = fractal_zip_wiki_lom_apply_acronyms($wire, $acronyms);
	}
	if ($flags['templates']) {
		$tplOpts = array('templates' => is_array($templates) ? $templates : null);
		$tpl = fractal_zip_bio_template_ifs_v2_build($wire, $tplOpts);
		$wire = (string) $tpl['payload'];
		$templates = is_array($tpl['stats']['templates'] ?? null) ? $tpl['stats']['templates'] : $templates;
	}

	$idToTitle = array();
	foreach ($titleToId as $title => $id) {
		if (!is_int($id) && !ctype_digit((string) $id)) {
			continue;
		}
		$iid = (int) $id;
		if (!isset($idToTitle[$iid])) {
			$idToTitle[$iid] = (string) $title;
		}
	}

	return array(
		'payload' => $wire,
		'sidecar' => array(
			'preprocess' => 'wiki_lom',
			'flags' => $flags,
			'title_to_id' => $titleToId,
			'id_to_title' => $idToTitle,
			'urls' => $urls,
			'abbrevs' => $abbrevs,
			'acronyms' => $acronyms,
			'tags' => $tags,
			'templates' => is_array($templates) ? $templates : array(),
			'entity_manifest' => $entityManifest,
			'xml_literal_stash' => $xmlLiteralStash,
			'wikitable_stash' => $wikitableStash,
			'html_table_fixup' => $htmlTableFixup,
			'html_table_decoded_stash' => $htmlTableDecodedStash,
		),
		'meta' => array(
			'raw_len' => strlen($text),
			'wire_len' => strlen($wire),
		),
	);
}

/** @param array<string, mixed> $sidecar */
function fractal_zip_wiki_lom_undo(string $payload, array $sidecar): string
{
	$sidecar = fractal_zip_wiki_lom_sidecar_json_unpack($sidecar);
	$flags = is_array($sidecar['flags'] ?? null) ? $sidecar['flags'] : array();
	$abbrevs = is_array($sidecar['abbrevs'] ?? null) ? array_values($sidecar['abbrevs']) : array();
	$acronyms = is_array($sidecar['acronyms'] ?? null) ? array_values($sidecar['acronyms']) : array();
	$tags = is_array($sidecar['tags'] ?? null) ? array_values($sidecar['tags']) : array();
	$urls = is_array($sidecar['urls'] ?? null) ? array_values($sidecar['urls']) : array();
	$idToTitle = is_array($sidecar['id_to_title'] ?? null) ? $sidecar['id_to_title'] : array();
	$templates = is_array($sidecar['templates'] ?? null) ? $sidecar['templates'] : array();

	$wire = $payload;
	if (!empty($flags['templates']) && $templates !== array()
		&& strncmp($wire, FRACTAL_ZIP_BIO_TEMPLATE_IFS_MAGIC, 5) === 0) {
		$wire = fractal_zip_bio_template_ifs_restore(
			$wire,
			json_encode(array('templates' => $templates), JSON_UNESCAPED_UNICODE) ?: '{}'
		);
	}
	if (!empty($flags['acronyms']) && $acronyms !== array()) {
		$wire = fractal_zip_wiki_lom_undo_acronyms($wire, $acronyms);
	}
	if (!empty($flags['abbrevs']) && $abbrevs !== array()) {
		$wire = fractal_zip_wiki_lom_undo_abbrevs($wire, $abbrevs);
	}
	if (!empty($flags['tag_ids']) && $tags !== array()) {
		$wire = fractal_zip_wiki_lom_undo_tag_ids($wire, $tags);
	}
	if (!empty($flags['url_dict']) && $urls !== array()) {
		$wire = fractal_zip_wiki_lom_undo_url_dict($wire, $urls);
	}
	if (!empty($flags['link_ids']) && $idToTitle !== array()) {
		$wire = fractal_zip_wiki_lom_undo_link_ids($wire, $idToTitle);
	}
	if (!empty($flags['wiki_html'])) {
		$wire = fractal_zip_wiki_html_decode($wire);
		$decodedTableStash = is_array($sidecar['html_table_decoded_stash'] ?? null)
			? $sidecar['html_table_decoded_stash'] : array();
		$wire = fractal_zip_wiki_lom_unstash_decoded_html_tables($wire, $decodedTableStash);
	}
	if (!empty($flags['entity_decode'])) {
		$manifest = is_array($sidecar['entity_manifest'] ?? null) ? $sidecar['entity_manifest'] : array();
		$wire = fractal_zip_wiki_lom_entity_encode($wire, $manifest);
		$fixup = is_array($sidecar['html_table_fixup'] ?? null) ? $sidecar['html_table_fixup'] : array();
		$wire = fractal_zip_wiki_lom_restore_entity_html_tables($wire, $fixup);
		$xmlStash = is_array($sidecar['xml_literal_stash'] ?? null) ? $sidecar['xml_literal_stash'] : array();
		$wire = fractal_zip_wiki_lom_unstash_xml_literals($wire, $xmlStash);
		$tableStash = is_array($sidecar['wikitable_stash'] ?? null) ? $sidecar['wikitable_stash'] : array();
		$wire = fractal_zip_wiki_lom_unstash_wikitables($wire, $tableStash);
	}
	return $wire;
}

/** JSON-safe sidecar: base64 stash payloads that may hold invalid UTF-8. */
function fractal_zip_wiki_lom_sidecar_json_pack(array $sidecar): array
{
	$out = $sidecar;
	foreach (array('xml_literal_stash', 'wikitable_stash', 'html_table_decoded_stash') as $field) {
		if (!isset($out[$field]) || !is_array($out[$field])) {
			continue;
		}
		$encoded = array();
		foreach ($out[$field] as $k => $v) {
			$encoded[] = array(
				'key_b64' => base64_encode((string) $k),
				'val_b64' => base64_encode((string) $v),
			);
		}
		$out[$field . '_b64'] = $encoded;
		unset($out[$field]);
	}
	if (isset($out['html_table_fixup']) && is_array($out['html_table_fixup'])) {
		$fx = array();
		foreach ($out['html_table_fixup'] as $entry) {
			if (!is_array($entry)) {
				continue;
			}
			$fx[] = array(
				'pattern' => (string) ($entry['pattern'] ?? 'B'),
				'orig_b64' => base64_encode((string) ($entry['orig'] ?? '')),
				'normalized_b64' => base64_encode((string) ($entry['normalized'] ?? '')),
			);
		}
		$out['html_table_fixup_b64'] = $fx;
		unset($out['html_table_fixup']);
	}
	$out['stash_b64'] = true;
	return $out;
}

function fractal_zip_wiki_lom_sidecar_json_unpack(array $sidecar): array
{
	if (empty($sidecar['stash_b64'])) {
		return $sidecar;
	}
	$out = $sidecar;
	unset($out['stash_b64']);
	foreach (array('xml_literal_stash', 'wikitable_stash', 'html_table_decoded_stash') as $field) {
		$b64Field = $field . '_b64';
		if (!isset($out[$b64Field]) || !is_array($out[$b64Field])) {
			continue;
		}
		$decoded = array();
		foreach ($out[$b64Field] as $entry) {
			if (!is_array($entry)) {
				continue;
			}
			$key = base64_decode((string) ($entry['key_b64'] ?? ''), true);
			$val = base64_decode((string) ($entry['val_b64'] ?? ''), true);
			if (!is_string($key) || !is_string($val)) {
				continue;
			}
			$decoded[$key] = $val;
		}
		$out[$field] = $decoded;
		unset($out[$b64Field]);
	}
	if (isset($out['html_table_fixup_b64']) && is_array($out['html_table_fixup_b64'])) {
		$fx = array();
		foreach ($out['html_table_fixup_b64'] as $entry) {
			if (!is_array($entry)) {
				continue;
			}
			$orig = base64_decode((string) ($entry['orig_b64'] ?? ''), true);
			$norm = base64_decode((string) ($entry['normalized_b64'] ?? ''), true);
			$fx[] = array(
				'pattern' => (string) ($entry['pattern'] ?? 'B'),
				'orig' => is_string($orig) ? $orig : '',
				'normalized' => is_string($norm) ? $norm : '',
			);
		}
		$out['html_table_fixup'] = $fx;
		unset($out['html_table_fixup_b64']);
	}
	return $out;
}

/**
 * @param list<array{title: string, start: int, len: int}> $pageRefs
 * @param array<string, bool> $flags
 * @return array<string, mixed>
 */
function fractal_zip_wiki_lom_mine_tables(
	string $corpusText,
	array $pageRefs,
	string $blob,
	array $opts = array(),
	array $flags = array()
): array {
	$decoded = fractal_zip_wiki_lom_entity_decode_text($corpusText);
	$out = fractal_zip_wiki_lom_empty_tables();
	if (!empty($flags['link_ids'])) {
		$out['title_to_id'] = fractal_zip_wiki_lom_mine_title_to_id($pageRefs, $blob);
	}
	if (!empty($flags['url_dict'])) {
		$out['urls'] = fractal_zip_wiki_lom_mine_urls($decoded, (int) ($opts['max_urls'] ?? 2048));
	}
	if (!empty($flags['tag_ids'])) {
		$out['tags'] = fractal_zip_wiki_lom_mine_tag_names($decoded, (int) ($opts['max_tags'] ?? 48));
	}
	if (!empty($flags['abbrevs'])) {
		$out['abbrevs_list'] = fractal_zip_wiki_lom_mine_abbrevs($decoded, (int) ($opts['max_abbrevs'] ?? 128));
	}
	if (!empty($flags['acronyms'])) {
		$gateText = fractal_zip_wiki_lom_phda9_wire_escape($decoded);
		$out['acronyms_list'] = fractal_zip_wiki_lom_load_cfabb_list($decoded, array(
			'max_acronyms' => (int) ($opts['max_acronyms'] ?? 4096),
			'min_count' => (int) ($opts['min_cfabb_count'] ?? 4),
			'corpus_pages' => (int) ($opts['corpus_pages'] ?? 12041),
			'phda9_gate_text' => $gateText,
		));
	}
	if (!empty($flags['templates'])) {
		$html = fractal_zip_wiki_html_encode($decoded);
		$out['templates_list'] = fractal_zip_bio_template_ifs_v2_mine($html, $opts);
	}
	return $out;
}

/** @param array<string, mixed> $tables */
function fractal_zip_wiki_lom_serialize_tables(array $tables): string
{
	$json = json_encode($tables, JSON_UNESCAPED_UNICODE);
	return $json === false ? '{}' : $json;
}

/** @return array<string, mixed> */
function fractal_zip_wiki_lom_deserialize_tables(string $blob): array
{
	$meta = json_decode($blob, true);
	return is_array($meta) ? $meta : array();
}

/** @param array<string, mixed> $meta */
function fractal_zip_wiki_lom_inner_fold_blob(array $meta): string
{
	$pack = $meta;
	if (!empty($pack['acronyms_list']) && is_array($pack['acronyms_list'])) {
		$rows = array();
		foreach ($pack['acronyms_list'] as $row) {
			if (!is_array($row)) {
				continue;
			}
			$rows[] = array(
				'phrase' => (string) ($row['phrase'] ?? ''),
				'token' => (string) ($row['token'] ?? $row['acronym'] ?? ''),
			);
		}
		if ($rows !== array()) {
			$pack['cfabb_blob_b64'] = base64_encode(fractal_zip_cfabb_pack_table($rows));
			unset($pack['acronyms_list']);
		}
	}
	return gzcompress(fractal_zip_wiki_lom_serialize_tables($pack), 9);
}

/** @return array<string, mixed>|null */
function fractal_zip_wiki_lom_inner_fold_unpack(string $gzBlob): ?array
{
	$raw = gzuncompress($gzBlob);
	if ($raw === false) {
		return null;
	}
	$meta = fractal_zip_wiki_lom_deserialize_tables($raw);
	if (!is_array($meta)) {
		return null;
	}
	if (!empty($meta['cfabb_blob_b64'])) {
		$bin = base64_decode((string) $meta['cfabb_blob_b64'], true);
		if (is_string($bin) && $bin !== '') {
			$rows = fractal_zip_cfabb_unpack_table($bin);
			$acronyms = array();
			foreach ($rows as $row) {
				$acronyms[] = array(
					'phrase' => (string) ($row['phrase'] ?? ''),
					'token' => (string) ($row['token'] ?? ''),
					'acronym' => (string) ($row['token'] ?? ''),
				);
			}
			$meta['acronyms_list'] = $acronyms;
		}
		unset($meta['cfabb_blob_b64']);
	}
	return $meta;
}

function fractal_zip_wiki_lom_extract_prose(string $wire): string
{
	$plain = (string) preg_replace('/<[^>]+>/', ' ', $wire);
	$plain = (string) preg_replace('/[\x05\x06\x1D\x1E]/', ' ', $plain);
	return (string) preg_replace('/\s+/', ' ', $plain);
}
