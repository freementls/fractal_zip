<?php
declare(strict_types=1);

/**
 * Wikipedia enwik8 entry sort: split <page> blocks, sort alphabetically by title, zip as virtual
 * multi-member folder, append FZEP trailer for bit-exact restore on extract.
 *
 * Enable with FRACTAL_ZIP_ENWIK_ENTRY_SORT=1 on a folder containing a single extensionless enwik-shaped blob.
 */

const FRACTAL_ZIP_ENWIK_MAGIC = 'FZEP';
const FRACTAL_ZIP_ENWIK_VERSION = 2;
/** FZEP v3: v2 fields + 1-byte flags (see FRACTAL_ZIP_ENWIK_FZEP_FLAG_*). */
const FRACTAL_ZIP_ENWIK_VERSION_RAW_PAQ = 3;
/** FZEP v4: v3 + gzipped EZTB text-codec region table after flags. */
const FRACTAL_ZIP_ENWIK_VERSION_TEXT_CODEC = 4;
const FRACTAL_ZIP_ENWIK_FZEP_FLAG_RAW_PAQ = 1;
const FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_CODEC = 2;
const FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_INNER = 4;
/** FZEP v5: v4 + sortedTextLens when {@see FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_INNER}. */
const FRACTAL_ZIP_ENWIK_VERSION_TEXT_INNER = 5;

/** enwik8 = 100_000_000 B; enwik9 = 1_000_000_000 B (decimal). */
const FRACTAL_ZIP_ENWIK8_CORPUS_BYTES = 100_000_000;
const FRACTAL_ZIP_ENWIK9_CORPUS_BYTES = 1_000_000_000;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_integrated_model.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_member_shootout.php';

/** 8 or 9 from FRACTAL_ZIP_ENWIK_CORPUS (default 8). */
function fractal_zip_enwik_corpus_id(): int
{
	$v = getenv('FRACTAL_ZIP_ENWIK_CORPUS');
	if ($v !== false && trim((string) $v) !== '') {
		$n = (int) trim((string) $v);
		if ($n === 8 || $n === 9) {
			return $n;
		}
	}
	return 8;
}

function fractal_zip_enwik_corpus_name(?int $id = null): string
{
	$id = $id ?? fractal_zip_enwik_corpus_id();
	return 'enwik' . (string) $id;
}

function fractal_zip_enwik_corpus_expected_bytes(?int $id = null): int
{
	$id = $id ?? fractal_zip_enwik_corpus_id();
	return $id === 9 ? FRACTAL_ZIP_ENWIK9_CORPUS_BYTES : FRACTAL_ZIP_ENWIK8_CORPUS_BYTES;
}

/**
 * Resolve enwik8/enwik9 blob path: test_files109|200, then repo-root fallback.
 */
function fractal_zip_enwik_corpus_path(?string $repoRoot = null, ?int $id = null): string
{
	$id = $id ?? fractal_zip_enwik_corpus_id();
	$name = fractal_zip_enwik_corpus_name($id);
	$repo = $repoRoot ?? dirname(__FILE__);
	$benchDir = $id === 9 ? 'test_files200' : 'test_files109';
	$candidates = array(
		$repo . DIRECTORY_SEPARATOR . $benchDir . DIRECTORY_SEPARATOR . $name,
		$repo . DIRECTORY_SEPARATOR . $name,
	);
	foreach ($candidates as $path) {
		if (is_file($path)) {
			return $path;
		}
	}
	return $candidates[0];
}

/** Count &lt;page&gt; open tags without loading the full blob. */
function fractal_zip_enwik_count_pages_in_file(string $path): int
{
	$fp = fopen($path, 'rb');
	if ($fp === false) {
		return 0;
	}
	$count = 0;
	$carry = '';
	while (!feof($fp)) {
		$chunk = fread($fp, 1 << 20);
		if ($chunk === false || $chunk === '') {
			break;
		}
		$hay = $carry . $chunk;
		if (preg_match_all('/<page\\b/i', $hay, $m)) {
			$count += count($m[0]);
		}
		$carry = strlen($hay) > 16 ? substr($hay, -16) : $hay;
	}
	fclose($fp);
	return $count;
}

function fractal_zip_enwik_ensure_syllable_codec(): void
{
	static $loaded = false;
	if ($loaded) {
		return;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
	$loaded = true;
}

function fractal_zip_enwik_lom_path(): string
{
	$env = getenv('LOM_O_PHP');
	if ($env !== false && trim((string) $env) !== '') {
		return (string) $env;
	}
	return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'LOM' . DIRECTORY_SEPARATOR . 'O.php';
}

function fractal_zip_enwik_ensure_lom(): void
{
	static $loaded = false;
	if ($loaded) {
		return;
	}
	$path = fractal_zip_enwik_lom_path();
	if (!is_readable($path)) {
		throw new RuntimeException('LOM not found at ' . $path . ' (set LOM_O_PHP)');
	}
	require_once $path;
	$loaded = true;
}

/**
 * Shallowest-depth &lt;tagName&gt; nodes in an XML fragment (LOM respects nesting / CDATA).
 *
 * @return list<array{0: string, 1: int}>
 */
function fractal_zip_enwik_lom_shallow_tag_nodes(string $xml, string $tagName): array
{
	fractal_zip_enwik_ensure_lom();
	$lom = new O($xml, false);
	$matches = $lom->get($tagName, false, false, true, false, true);
	if (!is_array($matches) || $matches === array()) {
		return array();
	}
	if (isset($matches[0]) && is_string($matches[0])) {
		$matches = array($matches);
	}
	$minDepth = null;
	$rows = array();
	foreach ($matches as $row) {
		if (!is_array($row) || !isset($row[0], $row[1])) {
			continue;
		}
		$off = (int) $row[1];
		$depth = (int) $lom->depth($off);
		$rows[] = array('depth' => $depth, 'off' => $off, 'bytes' => (string) $row[0]);
	}
	if ($rows === array()) {
		return array();
	}
	foreach ($rows as $r) {
		if ($minDepth === null || $r['depth'] < $minDepth) {
			$minDepth = $r['depth'];
		}
	}
	$out = array();
	foreach ($rows as $r) {
		if ($r['depth'] === $minDepth) {
			$out[] = array($r['bytes'], $r['off']);
		}
	}
	usort($out, static fn (array $a, array $b): int => $a[1] <=> $b[1]);
	return $out;
}

/**
 * Exact source byte spans for shallow &lt;tagName&gt; records (includes whitespace between records).
 *
 * @return list<string>
 */
function fractal_zip_enwik_lom_shallow_tag_byte_spans(string $xml, string $tagName, ?int $expectedCount = null): array
{
	$nodes = fractal_zip_enwik_lom_shallow_tag_nodes($xml, $tagName);
	$n = count($nodes);
	if ($n === 0) {
		if ($expectedCount === 0) {
			return array();
		}
		throw new RuntimeException('enwik LOM split: no <' . $tagName . '> at shallow depth');
	}
	$xmlLen = strlen($xml);
	$spans = array();
	for ($i = 0; $i < $n; $i++) {
		$start = (int) $nodes[$i][1];
		$end = ($i + 1 < $n) ? (int) $nodes[$i + 1][1] : $xmlLen;
		$spans[] = substr($xml, $start, $end - $start);
	}
	if ($expectedCount !== null && count($spans) !== $expectedCount) {
		throw new RuntimeException(
			'enwik LOM split: found ' . count($spans) . ' <' . $tagName . '> at shallow depth, need ' . $expectedCount
		);
	}
	return $spans;
}

function fractal_zip_enwik_entry_sort_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** Restore legacy stat_pred_inner behavior: prune bigrams during mining/encode model build. */
function fractal_zip_enwik_stat_pred_inner_prune_encode_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_STAT_PRED_INNER_PRUNE_ENCODE');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

function fractal_zip_enwik_pages_per_member(): int
{
	$raw = getenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER');
	if ($raw === false || trim((string) $raw) === '') {
		return 1;
	}
	$n = (int) trim((string) $raw);
	return max(1, $n);
}

/** One phda9 job over all pages (15M-squash-style context; sorted XML wire). */
function fractal_zip_enwik_phda9_single_stream_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
}

/**
 * Whether mono phda9_xml should use one blob for all pages (vs FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER chunks).
 * Raw preserve-text tolerates single-stream; preprocessed streams can crash phda9 on large slices.
 */
function fractal_zip_enwik_phda9_single_stream_wanted(string $preprocessId = ''): bool
{
	if (!fractal_zip_enwik_phda9_single_stream_enabled()) {
		return false;
	}
	$force = getenv('FRACTAL_ZIP_PHDA9_SINGLE_STREAM_FORCE');
	if ($force === '1' || strtolower(trim((string) $force)) === 'true') {
		return true;
	}
	$pre = strtolower(trim($preprocessId !== '' ? $preprocessId : (string) (getenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS') ?: 'none')));
	if ($pre === 'none') {
		return true;
	}
	$allowPre = getenv('FRACTAL_ZIP_PHDA9_SINGLE_STREAM_PREPROCESS');
	if ($allowPre === '1' || strtolower(trim((string) $allowPre)) === 'true') {
		return true;
	}
	fractal_zip_enwik_ensure_syllable_codec();
	if (fractal_zip_enwik_consonant_hybrid_family_preprocess($pre)) {
		return false;
	}
	if (fractal_zip_enwik_stat_pred_preprocess_family($pre)) {
		return false;
	}
	if (in_array($pre, array('dict_inner', 'dict_phda9_inner', 'dict_nncp', 'cycle_inner', 'cycle_delta'), true)) {
		return false;
	}
	return true;
}

/**
 * Mono fztx inner defaults to one blob (all pages); member-codec shootout needs smaller
 * chunks so paq8px runs per sorted-English slice. When shootout is on and
 * FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER < pageCount, honor that chunk size.
 */
function fractal_zip_enwik_text_inner_mono_pages_per_member(int $pageCount): int
{
	if ($pageCount <= 0) {
		return 1;
	}
	$fmt = strtolower(trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_FORMAT') ?: 'dual')));
	if ($fmt === 'phda9_xml' || $fmt === 'phda9' || $fmt === 'phda9_article') {
		if (fractal_zip_enwik_phda9_single_stream_wanted()) {
			return $pageCount;
		}
		$ppm = fractal_zip_enwik_pages_per_member();
		$want = $ppm >= $pageCount ? $pageCount : $ppm;
		if (PHP_SAPI === 'cli' && is_resource(STDERR) && $want < $pageCount) {
			$pre = strtolower(trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS') ?: 'none')));
			@fwrite(STDERR, '[enwik] phda9_xml chunked preprocess=' . $pre . ' pages_per_member=' . $want
				. ' pages=' . $pageCount . "\n");
		}
		return $want;
	}
	$e = getenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
	if ($e === false || trim((string) $e) === '') {
		return $pageCount;
	}
	$v = strtolower(trim((string) $e));
	if (!($v === '1' || $v === 'true' || $v === 'on' || $v === 'yes')) {
		return $pageCount;
	}
	$ppm = fractal_zip_enwik_pages_per_member();
	return $ppm >= $pageCount ? $pageCount : $ppm;
}

/** Max virtual members for FZBM gzip-1 prefilter random shuffles on enwik chunk paths (default 512). */
function fractal_zip_enwik_semantic_pack_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_SEMANTIC_PACK');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** Integrated enwik profile: native mix of dictionary + sort + fractal (not external PAQ wrap). */
function fractal_zip_enwik_harmony_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_HARMONY');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** Reversible MediaWiki/XML boilerplate dictionary on the **full blob** (legacy; hurts article fractal). */
function fractal_zip_enwik_boilerplate_pack_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** Pack only FZEP header/footer siteinfo (tier B); article pages stay untouched for fractal inner. */
function fractal_zip_enwik_siteinfo_pack_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_SITEINFO_PACK');
	if ($v === '0' || strtolower(trim((string) $v)) === 'false' || strtolower(trim((string) $v)) === 'no') {
		return false;
	}
	if ($v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes') {
		return true;
	}
	return fractal_zip_enwik_harmony_enabled();
}

/** Mine repeated lines/tags from the enwik blob at encode time (harmony tier A+). */
function fractal_zip_enwik_corpus_phrases_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES');
	if ($v === '0' || strtolower(trim((string) $v)) === 'false' || strtolower(trim((string) $v)) === 'no') {
		return false;
	}
	if ($v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes') {
		return true;
	}
	return fractal_zip_enwik_harmony_enabled();
}

/** High-frequency article tokens (phda9 word-dict idea; fz-native). Opt-in — not in default harmony. */
function fractal_zip_enwik_word_pack_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_WORD_PACK');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** Tier C: pack mined/wiki tokens only inside &lt;text xml:space="preserve"&gt; (XML shell unchanged). */
function fractal_zip_enwik_text_pack_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_TEXT_PACK');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

function fractal_zip_enwik_raw_paq_on_source_enabled(): bool
{
	if (!function_exists('fractal_zip_paq_native_compare_enabled') || !fractal_zip_paq_native_compare_enabled()) {
		return false;
	}
	$v = getenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE');
	if ($v === '0' || strtolower(trim((string) $v)) === 'false' || strtolower(trim((string) $v)) === 'no') {
		return false;
	}
	return true;
}

function fractal_zip_enwik_raw_paq_prefer_squash_cache(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_USE_SQUASH_CACHE');
	if ($v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes') {
		return true;
	}
	foreach (array('SKIP_LIVE_PAQ', 'FRACTAL_ZIP_ENWIK_RAW_PAQ_SKIP_LIVE_PAQ') as $k) {
		$skip = getenv($k);
		if ($skip === '1' || strtolower(trim((string) $skip)) === 'true' || strtolower(trim((string) $skip)) === 'yes') {
			return true;
		}
	}
	return false;
}

function fractal_zip_enwik_raw_paq_force_live(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_FORCE_LIVE');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/**
 * @return array{json: string, wire: string}
 */
function fractal_zip_enwik_paq_squash_cache_paths(?string $repoRoot = null): array
{
	$override = getenv('FRACTAL_ZIP_ENWIK_PAQ_SQUASH_CACHE_DIR');
	if ($override !== false && trim((string) $override) !== '') {
		$dir = rtrim(trim((string) $override), DIRECTORY_SEPARATOR);
		return array(
			'json' => $dir . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json',
			'wire' => $dir . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.fzpq',
		);
	}
	if ($repoRoot === null) {
		$repoRoot = __DIR__;
	}
	$bench = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks';
	return array(
		'json' => $bench . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json',
		'wire' => $bench . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.fzpq',
	);
}

/**
 * Load persisted PAQ squash wire when JSON metadata matches payload size.
 *
 * @return array{wire: string, tool: string, archive_bytes: int}|null
 */
function fractal_zip_enwik_try_load_paq_squash_wire(?string $repoRoot = null): ?array
{
	$paths = fractal_zip_enwik_paq_squash_cache_paths($repoRoot);
	if (!is_file($paths['wire']) || !is_file($paths['json'])) {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$wire = file_get_contents($paths['wire']);
	if (!is_string($wire) || $wire === '') {
		return null;
	}
	$meta = json_decode((string) file_get_contents($paths['json']), true);
	if (!is_array($meta) || !isset($meta['bytes'], $meta['tool'])) {
		return null;
	}
	$unwrapped = fractal_zip_paq_unwrap_wire($wire);
	if ($unwrapped === null) {
		return null;
	}
	$tool = (string) $meta['tool'];
	$archiveBytes = (int) $meta['bytes'];
	if ($unwrapped['tool'] !== $tool || strlen($unwrapped['payload']) !== $archiveBytes) {
		return null;
	}
	return array(
		'wire' => $wire,
		'tool' => $tool,
		'archive_bytes' => $archiveBytes,
	);
}

function fractal_zip_enwik_fzbm_path_order_random_max_members(): int
{
	$raw = getenv('FRACTAL_ZIP_ENWIK_FZBM_PATH_ORDER_RANDOM_MAX_MEMBERS');
	if ($raw === false || trim((string) $raw) === '') {
		return 512;
	}
	return max(16, min(512, (int) trim((string) $raw)));
}

/**
 * True when every path is a virtual enwik chunk member (`pages/chunk_NNNNN.xml`).
 *
 * @param list<string> $paths
 */
function fractal_zip_enwik_all_chunk_member_paths(array $paths): bool
{
	if ($paths === array()) {
		return false;
	}
	foreach ($paths as $p) {
		$p = str_replace('\\', '/', (string) $p);
		if (!preg_match('#^pages/chunk_[0-9]+\\.xml$#', $p)) {
			return false;
		}
	}
	return true;
}

function fractal_zip_enwik_encode_varint_u32(int $n): string
{
	if ($n < 0) {
		$n = 0;
	}
	if ($n < 0x80) {
		return chr($n);
	}
	if ($n < 0x4000) {
		return chr(($n & 0x7F) | 0x80) . chr($n >> 7);
	}
	if ($n < 0x200000) {
		return chr(($n & 0x7F) | 0x80)
			. chr((($n >> 7) & 0x7F) | 0x80)
			. chr($n >> 14);
	}
	if ($n < 0x10000000) {
		return chr(($n & 0x7F) | 0x80)
			. chr((($n >> 7) & 0x7F) | 0x80)
			. chr((($n >> 14) & 0x7F) | 0x80)
			. chr($n >> 21);
	}
	return chr(($n & 0x7F) | 0x80)
		. chr((($n >> 7) & 0x7F) | 0x80)
		. chr((($n >> 14) & 0x7F) | 0x80)
		. chr((($n >> 21) & 0x7F) | 0x80)
		. chr($n >> 28);
}

/**
 * @return array{0: int, 1: int}|null
 */
function fractal_zip_enwik_decode_varint_u32(string $blob, int $offset): ?array
{
	$n = strlen($blob);
	$val = 0;
	$shift = 0;
	for ($i = 0; $i < 5; $i++) {
		if ($offset + $i >= $n) {
			return null;
		}
		$b = ord($blob[$offset + $i]);
		$val |= ($b & 0x7F) << $shift;
		if (($b & 0x80) === 0) {
			return array($val, $offset + $i + 1);
		}
		$shift += 7;
	}
	return null;
}

function fractal_zip_enwik_title_sort_key(string $title): string
{
	if (function_exists('mb_strtolower')) {
		return mb_strtolower($title, 'UTF-8');
	}
	return strtolower($title);
}

function fractal_zip_enwik_extract_title(string $pageBytes): string
{
	if (preg_match('/<title\b[^>]*>(.*?)<\/title>/si', $pageBytes, $m) === 1) {
		return html_entity_decode(strip_tags($m[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
	return '';
}

function fractal_zip_enwik_sanitize_member_slug(string $title, int $sortedIdx): string
{
	$slug = preg_replace('/[^A-Za-z0-9._-]+/', '_', $title) ?? 'page';
	$slug = trim((string) $slug, '._-');
	if ($slug === '') {
		$slug = 'page';
	}
	if (strlen($slug) > 48) {
		$slug = substr($slug, 0, 48);
	}
	return sprintf('%06d_%s', $sortedIdx, $slug);
}

/**
 * Regex page index (low RAM; no LOM). Set FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1 for mining benches.
 *
 * @return array{header: string, footer: string, pagesBlobStart: int, pages: list<array{title: string, origIndex: int, start: int, len: int}>}|null
 */
function enwik_split_page_refs_fast(string $blob): ?array
{
	if ($blob === '' || stripos($blob, '<mediawiki') === false) {
		return null;
	}
	$firstPage = stripos($blob, '<page');
	if ($firstPage === false) {
		return null;
	}
	$footerStart = stripos($blob, '</mediawiki>');
	$footer = $footerStart !== false ? substr($blob, $footerStart) : '';
	$bodyEnd = $footerStart !== false ? $footerStart : strlen($blob);
	$header = substr($blob, 0, $firstPage);
	if (!preg_match_all('/<page\\b/i', $blob, $m, PREG_OFFSET_CAPTURE, $firstPage)) {
		return null;
	}
	$hits = $m[0];
	$pages = array();
	$n = count($hits);
	for ($i = 0; $i < $n; $i++) {
		$start = (int) $hits[$i][1];
		if ($start >= $bodyEnd) {
			break;
		}
		$end = ($i + 1 < $n) ? (int) $hits[$i + 1][1] : $bodyEnd;
		if ($end > $bodyEnd) {
			$end = $bodyEnd;
		}
		$len = $end - $start;
		if ($len <= 0) {
			continue;
		}
		$titleSlice = substr($blob, $start, min(4096, $len));
		$pages[] = array(
			'title' => fractal_zip_enwik_extract_title($titleSlice),
			'origIndex' => $i,
			'start' => $start,
			'len' => $len,
		);
	}
	if ($pages === array()) {
		return null;
	}
	return array(
		'header' => $header,
		'footer' => $footer,
		'pagesBlobStart' => $firstPage,
		'pages' => $pages,
	);
}

/**
 * Lightweight page index into $blob (avoids duplicating every page body in RAM).
 *
 * @return array{header: string, footer: string, pagesBlobStart: int, pages: list<array{title: string, origIndex: int, start: int, len: int}>}|null
 */
function enwik_split_page_refs(string $blob): ?array
{
	if (getenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT') === '1') {
		return enwik_split_page_refs_fast($blob);
	}
	if ($blob === '' || stripos($blob, '<mediawiki') === false) {
		return null;
	}
	$firstPage = stripos($blob, '<page');
	if ($firstPage === false) {
		return null;
	}
	$footerStart = stripos($blob, '</mediawiki>');
	$footer = $footerStart !== false ? substr($blob, $footerStart) : '';
	$bodyEnd = $footerStart !== false ? $footerStart : strlen($blob);
	$header = substr($blob, 0, $firstPage);
	$pagesBlobStart = $firstPage;
	$pagesBlobLen = $bodyEnd - $firstPage;
	if ($pagesBlobLen <= 0) {
		return null;
	}
	$pagesSlice = substr($blob, $pagesBlobStart, $pagesBlobLen);
	$pageNodes = fractal_zip_enwik_lom_shallow_tag_nodes($pagesSlice, 'page');
	if ($pageNodes === array()) {
		return null;
	}
	$pages = array();
	$nodeCount = count($pageNodes);
	for ($i = 0; $i < $nodeCount; $i++) {
		$relStart = (int) $pageNodes[$i][1];
		$relEnd = ($i + 1 < $nodeCount) ? (int) $pageNodes[$i + 1][1] : $pagesBlobLen;
		$len = $relEnd - $relStart;
		$absStart = $pagesBlobStart + $relStart;
		$titleSlice = substr($blob, $absStart, min(4096, $len));
		$pages[] = array(
			'title' => fractal_zip_enwik_extract_title($titleSlice),
			'origIndex' => $i,
			'start' => $absStart,
			'len' => $len,
		);
	}
	return array(
		'header' => $header,
		'footer' => $footer,
		'pagesBlobStart' => $pagesBlobStart,
		'pages' => $pages,
	);
}

/**
 * @return array{header: string, footer: string, pages: list<array{title: string, bytes: string, origIndex: int}>}|null
 */
function enwik_split_pages(string $blob): ?array
{
	$refs = enwik_split_page_refs($blob);
	if ($refs === null) {
		return null;
	}
	$pages = array();
	foreach ($refs['pages'] as $r) {
		$pages[] = array(
			'title' => (string) $r['title'],
			'bytes' => substr($blob, (int) $r['start'], (int) $r['len']),
			'origIndex' => (int) $r['origIndex'],
		);
	}
	return array(
		'header' => (string) $refs['header'],
		'footer' => (string) $refs['footer'],
		'pages' => $pages,
	);
}

/**
 * @param list<array{title: string, bytes: string, origIndex: int}> $pages
 * @return list<array{title: string, bytes: string, origIndex: int, sortedIndex: int}>
 */
function enwik_sort_pages_by_title(array $pages): array
{
	$decorated = array();
	foreach ($pages as $p) {
		$decorated[] = array(
			'sortKey' => fractal_zip_enwik_title_sort_key((string) $p['title']),
			'origIndex' => (int) $p['origIndex'],
			'page' => $p,
		);
	}
	usort($decorated, static function (array $a, array $b): int {
		$c = strcmp($a['sortKey'], $b['sortKey']);
		if ($c !== 0) {
			return $c;
		}
		return $a['origIndex'] <=> $b['origIndex'];
	});
	$out = array();
	$sortedIndex = 0;
	foreach ($decorated as $row) {
		$p = $row['page'];
		$out[] = array(
			'title' => (string) $p['title'],
			'bytes' => (string) $p['bytes'],
			'origIndex' => (int) $p['origIndex'],
			'sortedIndex' => $sortedIndex,
		);
		$sortedIndex++;
	}
	return $out;
}

/**
 * @param list<array{title: string, origIndex: int, start: int, len: int}> $pageRefs
 * @return list<array{title: string, origIndex: int, start: int, len: int, sortedIndex: int}>
 */
function enwik_sort_page_refs_by_title(array $pageRefs): array
{
	$decorated = array();
	foreach ($pageRefs as $p) {
		$decorated[] = array(
			'sortKey' => fractal_zip_enwik_title_sort_key((string) $p['title']),
			'origIndex' => (int) $p['origIndex'],
			'page' => $p,
		);
	}
	usort($decorated, static function (array $a, array $b): int {
		$c = strcmp($a['sortKey'], $b['sortKey']);
		if ($c !== 0) {
			return $c;
		}
		return $a['origIndex'] <=> $b['origIndex'];
	});
	$out = array();
	$sortedIndex = 0;
	foreach ($decorated as $row) {
		$p = $row['page'];
		$out[] = array(
			'title' => (string) $p['title'],
			'origIndex' => (int) $p['origIndex'],
			'start' => (int) $p['start'],
			'len' => (int) $p['len'],
			'sortedIndex' => $sortedIndex,
		);
		$sortedIndex++;
	}
	return $out;
}

/**
 * Stream page slices from $blob into $destPath (no intermediate concat buffer).
 *
 * @param list<array{start: int, len: int}> $pageRefs
 */
/**
 * @param list<string> $textPackPhrases when non-empty, pack only inside article &lt;text&gt; regions
 */
/**
 * @return list<int> byte length per page written (after phrase/text-codec transforms)
 */
function enwik_stream_page_refs_to_file(
	string $blob,
	array $pageRefs,
	string $destPath,
	array $textPackPhrases = array(),
	?array $textCodecCfg = null,
	?array &$textCodecRegionSink = null,
	?int &$textCodecNextTokenId = null
): array {
	$fh = fopen($destPath, 'wb');
	if ($fh === false) {
		throw new RuntimeException('enwik virtual folder: cannot write ' . $destPath);
	}
	$writtenLens = array();
	try {
		if ($textCodecCfg !== null) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
		}
		foreach ($pageRefs as $r) {
			$slice = substr($blob, (int) $r['start'], (int) $r['len']);
			if ($slice === false) {
				throw new RuntimeException('enwik virtual folder: slice read failed ' . $destPath);
			}
			if ($textPackPhrases !== array()) {
				$slice = fractal_zip_enwik_pack_text_regions_in_page($slice, $textPackPhrases);
			}
			if ($textCodecCfg !== null && $textCodecRegionSink !== null && $textCodecNextTokenId !== null) {
				$slice = fractal_zip_enwik_pack_text_regions_with_codec(
					$slice,
					$textCodecCfg,
					$textCodecRegionSink,
					$textCodecNextTokenId
				);
			}
			$writtenLens[] = strlen($slice);
			if (fwrite($fh, $slice) !== strlen($slice)) {
				throw new RuntimeException('enwik virtual folder: write failed ' . $destPath);
			}
		}
	} finally {
		fclose($fh);
	}
	return $writtenLens;
}

/**
 * @param list<array{title: string, bytes: string, origIndex: int, sortedIndex: int}> $sortedPages
 * @return array{virtualDir: string, memberRelPaths: list<string>, origIndexBySorted: list<int>, header: string, footer: string, outputFile: string, outputDir: string, pagesPerMember: int}
 */
function enwik_build_virtual_folder(string $virtualDir, array $sortedPages, string $header, string $footer, string $outputFile, string $outputDir): array
{
	return enwik_build_virtual_folder_impl($virtualDir, $sortedPages, null, $header, $footer, $outputFile, $outputDir, array());
}

/**
 * Memory-efficient build: stream slices from $blob using sorted page refs.
 *
 * @param list<array{title: string, origIndex: int, start: int, len: int, sortedIndex: int}> $sortedRefs
 * @return array{virtualDir: string, memberRelPaths: list<string>, origIndexBySorted: list<int>, header: string, footer: string, outputFile: string, outputDir: string, pagesPerMember: int}
 */
/**
 * @param list<string> $textPackPhrases
 */
function enwik_build_virtual_folder_from_refs(
	string $virtualDir,
	array $sortedRefs,
	string $blob,
	string $header,
	string $footer,
	string $outputFile,
	string $outputDir,
	array $textPackPhrases = array(),
	int $textCodecTokenBase = 0
): array {
	return enwik_build_virtual_folder_impl(
		$virtualDir,
		$sortedRefs,
		$blob,
		$header,
		$footer,
		$outputFile,
		$outputDir,
		$textPackPhrases,
		$textCodecTokenBase
	);
}

/**
 * @param list<array<string, mixed>> $sortedChunk
 */
/**
 * @param list<string> $textPackPhrases
 */
function enwik_build_virtual_folder_impl(
	string $virtualDir,
	array $sortedChunk,
	?string $blob,
	string $header,
	string $footer,
	string $outputFile,
	string $outputDir,
	array $textPackPhrases = array(),
	int $textCodecTokenBase = 0
): array {
	if (is_dir($virtualDir)) {
		fractal_zip_enwik_recursive_remove($virtualDir);
	}
	if (!mkdir($virtualDir, 0755, true) && !is_dir($virtualDir)) {
		throw new RuntimeException('enwik virtual folder: cannot create ' . $virtualDir);
	}
	$pagesDir = $virtualDir . DIRECTORY_SEPARATOR . 'pages';
	if (!mkdir($pagesDir, 0755, true) && !is_dir($pagesDir)) {
		throw new RuntimeException('enwik virtual folder: cannot create pages dir');
	}

	$pagesPerMember = fractal_zip_enwik_pages_per_member();
	$memberRelPaths = array();
	$origIndexBySorted = array();
	$sortedPageLens = array();
	$textCodecCfg = null;
	$textCodecDictPatterns = array();
	$textCodecRegionEntries = array();
	$textCodecNextId = 0;
	if ($blob !== null) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
		$textCodecCfg = fractal_zip_enwik_text_codec_config_from_env();
		if ($textCodecCfg !== null && str_starts_with((string) $textCodecCfg['scheme'], 'words_')) {
			$sharedVocab = fractal_zip_enwik_text_codec_build_shared_vocab_from_blob($blob);
			$textCodecDictPatterns[] = fractal_zip_enwik_text_codec_vocab_dict_entry($sharedVocab);
			$textCodecCfg['vocab'] = $sharedVocab;
			$textCodecCfg['vocab_index'] = fractal_zip_enwik_text_vocab_index($sharedVocab);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] text codec shared vocab: ' . count($sharedVocab) . " words\n");
			}
		}
	}
	$chunkIdx = 0;
	$pageCount = count($sortedChunk);
	for ($i = 0; $i < $pageCount; $i += $pagesPerMember) {
		$chunk = array_slice($sortedChunk, $i, $pagesPerMember);
		foreach ($chunk as $p) {
			$origIndexBySorted[] = (int) $p['origIndex'];
		}
		if ($pagesPerMember === 1) {
			$p0 = $chunk[0];
			$rel = 'pages/' . fractal_zip_enwik_sanitize_member_slug((string) $p0['title'], (int) $p0['sortedIndex']) . '.xml';
		} else {
			$rel = sprintf('pages/chunk_%05d.xml', $chunkIdx);
		}
		$full = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$parent = dirname($full);
		if (!is_dir($parent) && !mkdir($parent, 0755, true) && !is_dir($parent)) {
			throw new RuntimeException('enwik virtual folder: cannot create ' . $parent);
		}
		if ($blob !== null) {
			$refs = array();
			foreach ($chunk as $p) {
				$refs[] = array('start' => (int) $p['start'], 'len' => (int) $p['len']);
			}
			$pageLens = enwik_stream_page_refs_to_file(
				$blob,
				$refs,
				$full,
				$textPackPhrases,
				$textCodecCfg,
				$textCodecRegionEntries,
				$textCodecNextId
			);
			foreach ($pageLens as $wl) {
				$sortedPageLens[] = (int) $wl;
			}
		} else {
			$chunkBytes = '';
			foreach ($chunk as $p) {
				$chunkBytes .= (string) $p['bytes'];
				$sortedPageLens[] = strlen((string) ($p['bytes'] ?? ''));
			}
			if (file_put_contents($full, $chunkBytes) === false) {
				throw new RuntimeException('enwik virtual folder: cannot write ' . $full);
			}
		}
		$memberRelPaths[] = $rel;
		$chunkIdx++;
	}

	if ($textCodecRegionEntries !== array()) {
		$metaDir = $virtualDir . DIRECTORY_SEPARATOR . 'meta';
		if (!is_dir($metaDir) && !mkdir($metaDir, 0755, true) && !is_dir($metaDir)) {
			throw new RuntimeException('enwik virtual folder: cannot create meta dir');
		}
		$metaRel = 'meta/text_codec.eztb';
		$metaFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $metaRel);
		$bulk = fractal_zip_enwik_text_codec_pack_region_bulk($textCodecRegionEntries);
		if (file_put_contents($metaFull, $bulk) === false) {
			throw new RuntimeException('enwik virtual folder: cannot write text codec bulk');
		}
		$memberRelPaths[] = $metaRel;
	}

	$extra = array();
	if ($textCodecRegionEntries !== array()) {
		$extra['textCodecRegionCount'] = count($textCodecRegionEntries);
	}
	if ($textCodecDictPatterns !== array()) {
		$extra['textCodecDictPatternCount'] = count($textCodecDictPatterns);
	}
	return array_merge(array(
		'virtualDir' => $virtualDir,
		'memberRelPaths' => $memberRelPaths,
		'origIndexBySorted' => $origIndexBySorted,
		'header' => $header,
		'footer' => $footer,
		'outputFile' => $outputFile,
		'outputDir' => $outputDir,
		'pagesPerMember' => $pagesPerMember,
		'sortedPageLens' => $sortedPageLens,
		'textCodecDictPatterns' => $textCodecDictPatterns,
		'textCodecRegionEntries' => $textCodecRegionEntries,
		'textCodecMetaMember' => $textCodecRegionEntries !== array(),
	), $extra);
}

/**
 * Recompute FZEP sortedPageLens from on-disk virtual members (LOM page spans).
 *
 * @param array<string, mixed> $ctx
 * @return array<string, mixed>
 */
function fractal_zip_enwik_refresh_sorted_page_lens_from_virtual(array $ctx): array
{
	// Encode-time lens are sequential post-codec page spans in each chunk; LOM re-split on ~EZT XML mis-measures.
	if (!empty($ctx['textCodecMetaMember']) || !empty($ctx['textCodecRegionEntries'])
		|| !empty($ctx['textInnerDualMembers'])) {
		return $ctx;
	}
	$virtualDir = (string) ($ctx['virtualDir'] ?? '');
	$memberRelPaths = $ctx['memberRelPaths'] ?? array();
	$pageCount = count($ctx['origIndexBySorted'] ?? array());
	$pagesPerMember = max(1, (int) ($ctx['pagesPerMember'] ?? 1));
	if ($virtualDir === '' || !is_dir($virtualDir) || $pageCount === 0 || !is_array($memberRelPaths)) {
		return $ctx;
	}
	$newLens = array();
	$pageIdx = 0;
	foreach ($memberRelPaths as $rel) {
		$rel = (string) $rel;
		if (str_starts_with($rel, 'meta/') || str_ends_with($rel, '.text') || str_ends_with($rel, '.shell.xml')
			|| str_ends_with($rel, '.inner')) {
			continue;
		}
		$full = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $rel);
		if (!is_file($full)) {
			continue;
		}
		$raw = file_get_contents($full);
		if (!is_string($raw) || $raw === '') {
			continue;
		}
		if ($pagesPerMember === 1) {
			$newLens[] = strlen($raw);
			$pageIdx++;
			continue;
		}
		$pagesInMember = min($pagesPerMember, $pageCount - $pageIdx);
		if ($pagesInMember <= 0) {
			break;
		}
		foreach (enwik_split_member_chunk_pages($raw, $pagesInMember, (string) $rel) as $pageBytes) {
			$newLens[] = strlen($pageBytes);
			$pageIdx++;
		}
	}
	if (count($newLens) === $pageCount) {
		$ctx['sortedPageLens'] = $newLens;
	}
	return $ctx;
}

/**
 * @param array<string, mixed> $ctx
 */
function enwik_pack_permutation_meta(array $ctx): string
{
	$header = (string) ($ctx['header'] ?? '');
	$footer = (string) ($ctx['footer'] ?? '');
	$outputFile = (string) ($ctx['outputFile'] ?? 'enwik8');
	$origIndexBySorted = $ctx['origIndexBySorted'] ?? array();
	$memberRelPaths = $ctx['memberRelPaths'] ?? array();
	$pagesPerMember = (int) ($ctx['pagesPerMember'] ?? 1);
	if (!is_array($origIndexBySorted) || !is_array($memberRelPaths)) {
		throw new RuntimeException('enwik_pack_permutation_meta: corrupt ctx');
	}

	$parts = array(FRACTAL_ZIP_ENWIK_MAGIC, chr(FRACTAL_ZIP_ENWIK_VERSION));
	$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($header)) . $header;
	$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($footer)) . $footer;
	$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($outputFile)) . $outputFile;
	$parts[] = fractal_zip_enwik_encode_varint_u32($pagesPerMember);
	$parts[] = fractal_zip_enwik_encode_varint_u32(count($origIndexBySorted));
	$parts[] = fractal_zip_enwik_encode_varint_u32(count($memberRelPaths));
	foreach ($memberRelPaths as $rel) {
		$rel = (string) $rel;
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($rel)) . $rel;
	}
	foreach ($origIndexBySorted as $orig) {
		$parts[] = fractal_zip_enwik_encode_varint_u32((int) $orig);
	}
	$sortedPageLens = $ctx['sortedPageLens'] ?? array();
	if (!is_array($sortedPageLens) || count($sortedPageLens) !== count($origIndexBySorted)) {
		throw new RuntimeException('enwik_pack_permutation_meta: sortedPageLens mismatch');
	}
	foreach ($sortedPageLens as $len) {
		$parts[] = fractal_zip_enwik_encode_varint_u32((int) $len);
	}
	$flags = 0;
	if (!empty($ctx['rawPaqPassthrough'])) {
		$flags |= FRACTAL_ZIP_ENWIK_FZEP_FLAG_RAW_PAQ;
	}
	if (!empty($ctx['textCodecMetaMember'])) {
		$flags |= FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_CODEC;
	}
	if (!empty($ctx['textInnerDualMembers'])) {
		$flags |= FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_INNER;
	}
	$statSidecar = (string) ($ctx['statSidecarBlob'] ?? '');
	if ($statSidecar !== '') {
		$flags |= FRACTAL_ZIP_ENWIK_FZEP_FLAG_STAT_SIDECAR;
	}
	$innerFoldBlob = (string) ($ctx['innerFoldBlob'] ?? '');
	if ($innerFoldBlob !== '') {
		$flags |= FRACTAL_ZIP_ENWIK_FZEP_FLAG_INNER_FOLD;
	}
	$semanticDict = (string) ($ctx['semanticPackDict'] ?? '');
	if ($flags !== 0 || $semanticDict !== '' || !empty($ctx['textCodecMetaMember']) || !empty($ctx['textInnerDualMembers'])) {
		if (($flags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_INNER_FOLD) !== 0) {
			$parts[1] = chr(FRACTAL_ZIP_ENWIK_VERSION_INNER_FOLD);
		} elseif (($flags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_STAT_SIDECAR) !== 0) {
			$parts[1] = chr(FRACTAL_ZIP_ENWIK_VERSION_STAT_SIDECAR);
		} elseif (!empty($ctx['textInnerDualMembers'])) {
			$parts[1] = chr(FRACTAL_ZIP_ENWIK_VERSION_TEXT_INNER);
		} elseif (!empty($ctx['textCodecMetaMember'])) {
			$parts[1] = chr(FRACTAL_ZIP_ENWIK_VERSION_TEXT_CODEC);
		} else {
			$parts[1] = chr(FRACTAL_ZIP_ENWIK_VERSION_RAW_PAQ);
		}
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($semanticDict)) . $semanticDict;
		$parts[] = chr($flags & 0xFF);
		if (($flags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_INNER) !== 0) {
			$sortedTextLens = $ctx['sortedTextLens'] ?? array();
			if (!is_array($sortedTextLens) || count($sortedTextLens) !== count($origIndexBySorted)) {
				throw new RuntimeException('enwik_pack_permutation_meta: sortedTextLens mismatch');
			}
			foreach ($sortedTextLens as $len) {
				$parts[] = fractal_zip_enwik_encode_varint_u32((int) $len);
			}
		}
		if (($flags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_STAT_SIDECAR) !== 0) {
			$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($statSidecar)) . $statSidecar;
		}
		if (($flags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_INNER_FOLD) !== 0) {
			$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($innerFoldBlob)) . $innerFoldBlob;
		}
	}
	return implode('', $parts);
}

/**
 * @return array<string, mixed>|null meta including payloadLen (strip FZEP suffix before decompress)
 */
function fractal_zip_enwik_peel_fzep_from_blob(string $blob): ?array
{
	if ($blob === '' || strlen($blob) < 8) {
		return null;
	}
	$magicLen = strlen(FRACTAL_ZIP_ENWIK_MAGIC);
	$pos = strrpos($blob, FRACTAL_ZIP_ENWIK_MAGIC);
	if ($pos === false || $pos + $magicLen + 1 >= strlen($blob)) {
		return null;
	}
	$trailer = substr($blob, $pos);
	if (substr($trailer, 0, $magicLen) !== FRACTAL_ZIP_ENWIK_MAGIC) {
		return null;
	}
	$off = $magicLen;
	if ($off >= strlen($trailer)) {
		return null;
	}
	$version = ord($trailer[$off]);
	$off++;
	if ($version !== 1 && $version !== 2 && $version !== FRACTAL_ZIP_ENWIK_VERSION_RAW_PAQ
		&& $version !== FRACTAL_ZIP_ENWIK_VERSION_TEXT_CODEC
		&& $version !== FRACTAL_ZIP_ENWIK_VERSION_TEXT_INNER
		&& $version !== FRACTAL_ZIP_ENWIK_VERSION_STAT_SIDECAR
		&& $version !== FRACTAL_ZIP_ENWIK_VERSION_INNER_FOLD) {
		return null;
	}

	$readBlob = static function (string $t, int &$o): ?string {
		$dv = fractal_zip_enwik_decode_varint_u32($t, $o);
		if ($dv === null) {
			return null;
		}
		$len = $dv[0];
		$o = $dv[1];
		if ($len < 0 || $o + $len > strlen($t)) {
			return null;
		}
		$s = substr($t, $o, $len);
		$o += $len;
		return $s;
	};

	$header = $readBlob($trailer, $off);
	$footer = $readBlob($trailer, $off);
	$outputFile = $readBlob($trailer, $off);
	if ($header === null || $footer === null || $outputFile === null) {
		return null;
	}
	$dv = fractal_zip_enwik_decode_varint_u32($trailer, $off);
	if ($dv === null) {
		return null;
	}
	$pagesPerMember = $dv[0];
	$off = $dv[1];
	$dv = fractal_zip_enwik_decode_varint_u32($trailer, $off);
	if ($dv === null) {
		return null;
	}
	$pageCount = $dv[0];
	$off = $dv[1];
	$dv = fractal_zip_enwik_decode_varint_u32($trailer, $off);
	if ($dv === null) {
		return null;
	}
	$memberCount = $dv[0];
	$off = $dv[1];

	$memberRelPaths = array();
	for ($i = 0; $i < $memberCount; $i++) {
		$rel = $readBlob($trailer, $off);
		if ($rel === null) {
			return null;
		}
		$memberRelPaths[] = $rel;
	}
	$origIndexBySorted = array();
	for ($i = 0; $i < $pageCount; $i++) {
		$dv = fractal_zip_enwik_decode_varint_u32($trailer, $off);
		if ($dv === null) {
			return null;
		}
		$origIndexBySorted[] = $dv[0];
		$off = $dv[1];
	}
	$sortedPageLens = array();
	if ($version >= 2) {
		for ($i = 0; $i < $pageCount; $i++) {
			$dv = fractal_zip_enwik_decode_varint_u32($trailer, $off);
			if ($dv === null) {
				return null;
			}
			$sortedPageLens[] = $dv[0];
			$off = $dv[1];
		}
	}
	$semanticPackDict = '';
	$fzepFlags = 0;
	$textCodecBulk = '';
	if ($version >= FRACTAL_ZIP_ENWIK_VERSION_RAW_PAQ) {
		$dict = $readBlob($trailer, $off);
		if ($dict === null) {
			return null;
		}
		$semanticPackDict = $dict;
		if ($off >= strlen($trailer)) {
			return null;
		}
		$fzepFlags = ord($trailer[$off]);
		$off++;
	}
	$sortedTextLens = array();
	if (($fzepFlags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_INNER) !== 0) {
		for ($i = 0; $i < $pageCount; $i++) {
			$dv = fractal_zip_enwik_decode_varint_u32($trailer, $off);
			if ($dv === null) {
				return null;
			}
			$sortedTextLens[] = $dv[0];
			$off = $dv[1];
		}
	}
	$statSidecarBlob = '';
	if (($fzepFlags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_STAT_SIDECAR) !== 0) {
		$side = $readBlob($trailer, $off);
		if ($side === null) {
			return null;
		}
		$statSidecarBlob = $side;
	}
	$innerFoldBlob = '';
	if (($fzepFlags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_INNER_FOLD) !== 0) {
		$fold = $readBlob($trailer, $off);
		if ($fold === null) {
			return null;
		}
		$innerFoldBlob = $fold;
	}
	if ($off !== strlen($trailer)) {
		return null;
	}

	return array(
		'payloadLen' => $pos,
		'header' => $header,
		'footer' => $footer,
		'outputFile' => $outputFile,
		'pagesPerMember' => $pagesPerMember,
		'origIndexBySorted' => $origIndexBySorted,
		'memberRelPaths' => $memberRelPaths,
		'sortedPageLens' => $sortedPageLens,
		'sortedTextLens' => $sortedTextLens,
		'fzepVersion' => $version,
		'fzepFlags' => $fzepFlags,
		'semanticPackDict' => $semanticPackDict,
		'textCodecMetaMember' => ($fzepFlags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_CODEC) !== 0,
		'textInnerDualMembers' => ($fzepFlags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_INNER) !== 0,
		'statSidecarBlob' => $statSidecarBlob,
		'innerFoldBlob' => $innerFoldBlob,
	);
}

function fractal_zip_enwik_strip_fzep_suffix(string $blob): string
{
	$meta = fractal_zip_enwik_peel_fzep_from_blob($blob);
	if ($meta === null) {
		return $blob;
	}
	return substr($blob, 0, (int) $meta['payloadLen']);
}

/**
 * @return array<string, mixed>|null
 */
function fractal_zip_enwik_peel_trailer_from_fzc(string $fzcPath): ?array
{
	if (!is_file($fzcPath)) {
		return null;
	}
	$blob = file_get_contents($fzcPath);
	if (!is_string($blob)) {
		return null;
	}
	if (function_exists('fractal_zip_web_ref_strip_fzwr_suffix')) {
		$blob = fractal_zip_web_ref_strip_fzwr_suffix($blob);
	}
	return fractal_zip_enwik_peel_fzep_from_blob($blob);
}

/**
 * Record-boundary split: &lt;/page&gt; then whitespace then next &lt;page&gt; (preserves bytes).
 *
 * @return list<string>|null null when boundary count does not match $count
 */
function enwik_split_member_chunk_pages_by_boundary(string $bytes, int $count): ?array
{
	if ($count <= 0) {
		return array();
	}
	if ($count === 1) {
		return array($bytes);
	}
	$starts = array(0);
	$scan = 0;
	$bytesLen = strlen($bytes);
	while ($scan < $bytesLen) {
		$close = stripos($bytes, '</page>', $scan);
		if ($close === false) {
			break;
		}
		$afterClose = $close + 7;
		$next = stripos($bytes, '<page', $afterClose);
		if ($next === false) {
			break;
		}
		$gap = substr($bytes, $afterClose, $next - $afterClose);
		if ($gap !== '' && trim($gap) !== '') {
			break;
		}
		$starts[] = $next;
		$scan = $next;
	}
	if (count($starts) !== $count) {
		return null;
	}
	$pages = array();
	for ($i = 0; $i < $count; $i++) {
		$relEnd = ($i + 1 < $count) ? $starts[$i + 1] : $bytesLen;
		$pages[] = substr($bytes, $starts[$i], $relEnd - $starts[$i]);
	}
	return $pages;
}

/**
 * Split one virtual member chunk into exactly $count page blobs.
 *
 * @return list<string>
 */
function enwik_split_member_chunk_pages(string $bytes, int $count, string $memberRel = ''): array
{
	if ($count <= 0) {
		return array();
	}
	if ($count === 1) {
		return array($bytes);
	}
	try {
		return fractal_zip_enwik_lom_shallow_tag_byte_spans($bytes, 'page', $count);
	} catch (RuntimeException $e) {
		// LOM may under-count when &lt;page&gt; appears inside article &lt;text&gt; at the same depth.
	}
	$byBoundary = enwik_split_member_chunk_pages_by_boundary($bytes, $count);
	if ($byBoundary !== null) {
		return $byBoundary;
	}
	if (!preg_match_all('/<page(\s|>)/i', $bytes, $pageMatches, PREG_OFFSET_CAPTURE)) {
		$hint = $memberRel !== '' ? ' (' . $memberRel . ', ' . strlen($bytes) . ' B)' : '';
		throw new RuntimeException('enwik restore: chunk split failed: no <page markers' . $hint);
	}
	$markers = array();
	foreach ($pageMatches[0] as $row) {
		$markers[] = (int) $row[1];
	}
	$markerCount = count($markers);
	if ($markerCount < $count) {
		$hint = $memberRel !== '' ? ' (' . $memberRel . ', ' . strlen($bytes) . ' B)' : '';
		throw new RuntimeException(
			'enwik restore: chunk split failed: found ' . $markerCount . ' <page markers, need ' . $count . $hint
		);
	}
	if ($markerCount > $count) {
		$markers = array_slice($markers, 0, $count);
	}
	$bytesLen = strlen($bytes);
	$pages = array();
	for ($i = 0; $i < $count; $i++) {
		$relEnd = ($i + 1 < $count) ? $markers[$i + 1] : $bytesLen;
		$pages[] = substr($bytes, $markers[$i], $relEnd - $markers[$i]);
	}
	return $pages;
}

/**
 * Sequential page split using FZEP sortedPageLens (encode-time spans in each virtual chunk).
 *
 * @param list<int> $lens
 * @return list<string>|null null when lens do not cover the chunk exactly
 */
function enwik_split_member_chunk_pages_by_lens(string $bytes, array $lens): ?array
{
	if ($lens === array()) {
		return null;
	}
	$bytesLen = strlen($bytes);
	$sum = 0;
	foreach ($lens as $len) {
		if ($len < 0) {
			return null;
		}
		$sum += (int) $len;
	}
	if ($sum !== $bytesLen) {
		return null;
	}
	$pages = array();
	$off = 0;
	foreach ($lens as $len) {
		$len = (int) $len;
		$pages[] = substr($bytes, $off, $len);
		$off += $len;
	}
	return $pages;
}

/**
 * Merge shared preprocess meta into a per-page sidecar (restore path).
 *
 * @param array<string, mixed> $sidecar
 * @param array<string, mixed> $preprocessMeta
 * @return array<string, mixed>
 */
function fractal_zip_enwik_merge_preprocess_sidecar(string $preprocessId, array $sidecar, array $preprocessMeta): array
{
	if ($preprocessId === 'dict_nncp' && is_array($preprocessMeta['vocab'] ?? null)) {
		$sidecar['vocab'] = $preprocessMeta['vocab'];
		$sidecar['codec'] = (string) ($preprocessMeta['codec'] ?? 'segment_v2');
	}
	if ($preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		$foldRaw = (string) ($preprocessMeta['fold_dict_inner_blob'] ?? '');
		if ($foldRaw !== '') {
			$sidecar['vocab'] = fractal_zip_enwik_inner_fold_decode_vocab($foldRaw);
			$sidecar['codec'] = 'segment_v2';
		}
	}
	if (($preprocessId === 'stat_wrt' || $preprocessId === 'wrt_xwrt')
		&& is_array($preprocessMeta['codes'] ?? null)) {
		$sidecar['codes'] = $preprocessMeta['codes'];
	}
	if ($preprocessId === 'stat_isp' && is_array($preprocessMeta['vocab'] ?? null)) {
		$sidecar['vocab'] = $preprocessMeta['vocab'];
	}
	if ($preprocessId === 'stat_syllable_isp' && is_array($preprocessMeta['vocab'] ?? null)) {
		$sidecar['vocab'] = $preprocessMeta['vocab'];
	}
	fractal_zip_enwik_ensure_syllable_codec();
	if (fractal_zip_enwik_consonant_hybrid_family_preprocess($preprocessId)) {
		$sidecar = fractal_zip_enwik_merge_consonant_hybrid_sidecar($sidecar, $preprocessMeta);
		if (isset($preprocessMeta['payload_codec'])) {
			$sidecar['payload_codec'] = $preprocessMeta['payload_codec'];
		}
	}
	if ($preprocessId === 'stat_syllable_isp' && isset($preprocessMeta['payload_codec'])) {
		$sidecar['payload_codec'] = $preprocessMeta['payload_codec'];
	}
	if (fractal_zip_enwik_stat_pred_preprocess_family($preprocessId) && is_array($preprocessMeta['vocab'] ?? null)) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
		$sidecar['vocab'] = $preprocessMeta['vocab'];
		if (is_array($preprocessMeta['bigram_succ_ids'] ?? null)) {
			$sidecar['bigram_succ'] = fractal_zip_enwik_stat_pred_expand_bigram_ids(
				$preprocessMeta['bigram_succ_ids'],
				$preprocessMeta['vocab']
			);
		} else {
			$sidecar['bigram_succ'] = $preprocessMeta['bigram_succ'] ?? array();
		}
	}
	if ($preprocessId === 'qg_hybrid_root' && is_array($preprocessMeta['word_vocab'] ?? null)) {
		$sidecar['word_vocab'] = $preprocessMeta['word_vocab'];
		$sidecar['sub_vocab'] = $preprocessMeta['sub_vocab'] ?? array();
	}
	if (($preprocessId === 'qg_subword_root' || $preprocessId === 'qg_word_root')
		&& is_array($preprocessMeta['vocab'] ?? null)) {
		$sidecar['vocab'] = $preprocessMeta['vocab'];
	}
	if ($preprocessId === 'wiki_lom') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
		$foldRaw = (string) ($preprocessMeta['fold_wiki_lom_blob'] ?? '');
		if ($foldRaw !== '') {
			$tables = fractal_zip_wiki_lom_inner_fold_unpack($foldRaw);
			if (is_array($tables)) {
				if (is_array($tables['title_to_id'] ?? null)) {
					$sidecar['title_to_id'] = $tables['title_to_id'];
				}
				if (is_array($tables['urls'] ?? null)) {
					$sidecar['urls'] = $tables['urls'];
				}
				if (is_array($tables['tags'] ?? null)) {
					$sidecar['tags'] = $tables['tags'];
				}
				if (is_array($tables['abbrevs_list'] ?? null)) {
					$sidecar['abbrevs'] = $tables['abbrevs_list'];
				}
				if (is_array($tables['acronyms_list'] ?? null)) {
					$sidecar['acronyms'] = $tables['acronyms_list'];
				}
				if (is_array($tables['templates_list'] ?? null)) {
					$sidecar['templates'] = $tables['templates_list'];
				}
				$idToTitle = array();
				foreach ($sidecar['title_to_id'] as $title => $id) {
					if (is_int($id) || ctype_digit((string) $id)) {
						$iid = (int) $id;
						if (!isset($idToTitle[$iid])) {
							$idToTitle[$iid] = (string) $title;
						}
					}
				}
				$sidecar['id_to_title'] = $idToTitle;
			}
		}
		if (is_array($preprocessMeta['flags'] ?? null) && !is_array($sidecar['flags'] ?? null)) {
			$sidecar['flags'] = $preprocessMeta['flags'];
		}
		if (!empty($preprocessMeta['cfabb_inline'])) {
			if (empty($sidecar['acronyms'])) {
				require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
				$inline = fractal_zip_cfabb_inline_cache_load();
				if ($inline !== array()) {
					$sidecar['acronyms'] = $inline;
				}
			}
			if (!is_array($sidecar['flags'] ?? null)) {
				$sidecar['flags'] = array();
			}
			$sidecar['flags']['cfabb_inline'] = true;
			$sidecar['flags']['acronyms'] = true;
		}
	}
	return $sidecar;
}

/** Undo text-inner preprocess on one preserve-text payload. */
function fractal_zip_enwik_undo_preprocess_text(
	string $preprocessId,
	string $textPayload,
	array $sidecar,
	array $preprocessMeta
): string {
	if ($preprocessId === 'none') {
		return $textPayload;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$sidecar = fractal_zip_enwik_merge_preprocess_sidecar($preprocessId, $sidecar, $preprocessMeta);
	$undoPreprocessId = fractal_zip_enwik_stat_pred_preprocess_family($preprocessId) ? 'stat_pred'
		: (in_array($preprocessId, array('dict_inner', 'dict_phda9_inner'), true) ? 'dict_nncp' : $preprocessId);
	return fractal_zip_text_preprocess_undo($undoPreprocessId, $textPayload, $sidecar);
}

/** Restore one phda9 page XML blob that carries preprocessed article text. */
function fractal_zip_enwik_restore_phda9_preprocessed_page(
	string $pageXml,
	string $preprocessId,
	array $sidecar,
	array $preprocessMeta,
	?string $skAsciiPart = null
): string {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
	$textPayload = fractal_zip_enwik_extract_page_preserve_text($pageXml);
	if ($preprocessId === 'wiki_lom' && !empty($preprocessMeta['wiki_lom_phda9_wire_escape'])) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
		$textPayload = fractal_zip_wiki_lom_phda9_wire_unescape($textPayload);
	}
	if ($preprocessId === 'consonant_hybrid_split' && $skAsciiPart !== null) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
		$textPayload = fractal_zip_enwik_consonant_sk_phda9_wire_unescape_literal($textPayload);
		$textPayload = fractal_zip_enwik_consonant_hybrid_split_join_payload($textPayload, $skAsciiPart);
	}
	$textPayload = fractal_zip_enwik_undo_preprocess_text($preprocessId, $textPayload, $sidecar, $preprocessMeta);
	return fractal_zip_enwik_inject_text_into_shell_page($pageXml, $textPayload);
}

/**
 * phda9-compress skeleton ascii stream for consonant_hybrid_split dual members.
 *
 * @return array{rel: string, bytes: int, plain_bytes: int}
 */
function fractal_zip_enwik_consonant_sk_dual_write_phda9_member(
	string $virtualDir,
	int $chunkIdx,
	string $skPlain,
	int $shootTimeout = 0
): array {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
	$skRel = sprintf('pages/chunk_%05d.sk.inner', $chunkIdx);
	$skFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $skRel);
	if ($skPlain === '') {
		return array('rel' => '', 'bytes' => 0, 'plain_bytes' => 0);
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
	$skDict = fractal_zip_enwik_consonant_sk_dual_phda9_dict_path();
	$compressOpts = array(
		'wire_wrap' => true,
		'timeout_sec' => $shootTimeout > 0 ? $shootTimeout : 0,
	);
	if ($skDict !== null) {
		$compressOpts['dict_path'] = $skDict;
	}
	$cr = fractal_zip_enwik_phda9_english_compress($skPlain, $compressOpts);
	if (empty($cr['roundtrip_ok']) || !is_string($cr['payload']) || $cr['payload'] === '') {
		throw new RuntimeException('enwik text-inner phda9_xml sk dual compress failed chunk ' . $chunkIdx);
	}
	if (file_put_contents($skFull, (string) $cr['payload']) === false) {
		throw new RuntimeException('enwik text-inner phda9_xml sk dual write failed chunk ' . $chunkIdx);
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[enwik] phda9_xml sk chunk ' . $chunkIdx . ': ' . ($cr['tool'] ?? 'phda9')
			. ' ' . number_format(strlen((string) $cr['payload'])) . ' B plain='
			. number_format(strlen($skPlain)) . ' sec=' . ($cr['seconds'] ?? 0) . "\n");
		@fflush(STDERR);
	}
	return array(
		'rel' => $skRel,
		'bytes' => strlen((string) $cr['payload']),
		'plain_bytes' => strlen($skPlain),
	);
}

/** Inject preserve-text body into a shell-only page XML slice. */
function fractal_zip_enwik_inject_text_into_shell_page(string $shellPage, string $text): string
{
	$marker = FRACTAL_ZIP_ENWIK_TEXT_MARKER;
	$openLen = strlen($marker);
	$open = stripos($shellPage, $marker);
	if ($open === false) {
		return $shellPage;
	}
	$close = stripos($shellPage, '</text>', $open + $openLen);
	if ($close === false) {
		return substr($shellPage, 0, $open + $openLen) . $text;
	}
	return substr($shellPage, 0, $open + $openLen) . $text . substr($shellPage, $close);
}

/**
 * @param list<int> $textLens
 * @return list<string>
 */
function enwik_split_text_chunk_by_lens(string $bytes, array $textLens): ?array
{
	if ($textLens === array()) {
		return null;
	}
	$sum = 0;
	foreach ($textLens as $len) {
		$sum += (int) $len;
	}
	if ($sum !== strlen($bytes)) {
		return null;
	}
	$parts = array();
	$off = 0;
	foreach ($textLens as $len) {
		$len = (int) $len;
		$parts[] = substr($bytes, $off, $len);
		$off += $len;
	}
	return $parts;
}

/** True for virtual enwik/text-inner scaffolding stored under pages/ or meta/. */
function fractal_zip_enwik_is_fzep_scaffolding_rel(string $rel): bool
{
	return str_starts_with($rel, 'pages/') || str_starts_with($rel, 'meta/');
}

/**
 * @param list<string> $memberRelPaths
 * @param list<int> $origIndexBySorted
 * @param list<int> $sortedPageLens FZEP v2: byte length per sorted page (required when pagesPerMember > 1)
 * @param list<int> $sortedTextLens FZEP v5: text body length per sorted page (text-inner wire)
 */
function enwik_restore_blob(
	string $header,
	string $footer,
	array $memberRelPaths,
	array $origIndexBySorted,
	int $pagesPerMember,
	string $extractRoot,
	array $sortedPageLens = array(),
	array $sortedTextLens = array(),
	string $innerFoldBlobGz = ''
): string {
	$sortedPages = array();
	$pageIdx = 0;
	$pageCount = count($origIndexBySorted);
	$textInner = $sortedTextLens !== array() && count($sortedTextLens) === $pageCount;
	$layoutMeta = $textInner ? fractal_zip_enwik_load_text_inner_layout_meta($extractRoot) : null;
	$chunkPerms = is_array($layoutMeta['chunk_perms'] ?? null) ? $layoutMeta['chunk_perms'] : array();
	$layoutIdRestore = is_array($layoutMeta) ? (string) ($layoutMeta['layout_id'] ?? $layoutMeta['layout'] ?? '') : '';
	$implicitAlphaSort = is_array($layoutMeta) && !empty($layoutMeta['implicit_alpha_sort']);
	$textInnerChunkIdx = 0;
	$preprocessMeta = $textInner ? fractal_zip_enwik_load_text_inner_preprocess_meta($extractRoot) : null;
	if ($textInner && $preprocessMeta === null && $innerFoldBlobGz !== '') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		$preprocessMeta = fractal_zip_enwik_inner_fold_preprocess_meta_from_trailer($innerFoldBlobGz);
	}
	$preprocessId = is_array($preprocessMeta) ? (string) ($preprocessMeta['preprocess'] ?? 'none')
		: (is_array($layoutMeta) ? (string) ($layoutMeta['preprocess'] ?? 'none') : 'none');
	if ($textInner && $innerFoldBlobGz !== '' && is_array($preprocessMeta)) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		$fromTrailer = fractal_zip_enwik_inner_fold_preprocess_meta_from_trailer($innerFoldBlobGz);
		if (is_array($fromTrailer)) {
			$tpid = (string) ($fromTrailer['preprocess'] ?? '');
			if ($tpid === $preprocessId && $tpid === 'stat_pred_inner') {
				foreach (array('vocab', 'bigram_succ_ids', 'codec', 'sidecars') as $k) {
					if (!isset($preprocessMeta[$k]) && isset($fromTrailer[$k])) {
						$preprocessMeta[$k] = $fromTrailer[$k];
					}
				}
			} elseif ($tpid === $preprocessId
				&& ($tpid === 'dict_inner' || $tpid === 'dict_phda9_inner')
				&& ($fromTrailer['fold_dict_inner_blob'] ?? '') !== '') {
				$preprocessMeta['fold_dict_inner_blob'] = $fromTrailer['fold_dict_inner_blob'];
			} elseif ($tpid === $preprocessId && $tpid === 'wiki_lom'
				&& ($fromTrailer['fold_wiki_lom_blob'] ?? '') !== '') {
				$preprocessMeta['fold_wiki_lom_blob'] = $fromTrailer['fold_wiki_lom_blob'];
				if (is_array($fromTrailer['flags'] ?? null) && !is_array($preprocessMeta['flags'] ?? null)) {
					$preprocessMeta['flags'] = $fromTrailer['flags'];
				}
			} elseif ($tpid === $preprocessId) {
				fractal_zip_enwik_ensure_syllable_codec();
				if (fractal_zip_enwik_consonant_hybrid_family_preprocess($tpid)) {
					foreach (array('skeleton_unique', 'skeleton_ambig', 'context_unique', 'payload_codec') as $k) {
						if (!isset($preprocessMeta[$k]) && isset($fromTrailer[$k])) {
							$preprocessMeta[$k] = $fromTrailer[$k];
						}
					}
				}
			}
		}
	}
	$preprocessId = is_array($preprocessMeta) ? (string) ($preprocessMeta['preprocess'] ?? 'none')
		: (is_array($layoutMeta) ? (string) ($layoutMeta['preprocess'] ?? 'none') : 'none');
	$preSidecars = is_array($preprocessMeta['sidecars'] ?? null) ? $preprocessMeta['sidecars'] : array();
	$statPredEmbeddedLoaded = is_array($preprocessMeta['vocab'] ?? null);
	if ($preprocessId !== 'none') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	}
	if ($preprocessId === 'dict_phda9_inner') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_paq.php';
		fractal_zip_parallel_paq_restore_dict_from_preprocess_meta(
			is_array($preprocessMeta) ? $preprocessMeta : array(),
			$innerFoldBlobGz
		);
	}
	if ($innerFoldBlobGz !== '') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
		fractal_zip_phda9_dict_restore_from_trailer($innerFoldBlobGz);
	}
	foreach ($memberRelPaths as $rel) {
		$rel = (string) $rel;
		if ($textInner && !fractal_zip_enwik_is_fzep_scaffolding_rel($rel)) {
			continue;
		}
		if (str_starts_with($rel, 'meta/')) {
			continue;
		}
		if ($textInner && str_ends_with($rel, '.shell.xml')) {
			$memberFormatEarly = is_array($layoutMeta) ? (string) ($layoutMeta['member_format'] ?? 'dual') : 'dual';
			if ($memberFormatEarly === 'phda9_article') {
				continue;
			}
		}
		if ($textInner && str_ends_with($rel, '.text')) {
			continue;
		}
		if ($textInner && (str_ends_with($rel, '.shell.xml') || str_ends_with($rel, '.inner'))) {
			if (str_ends_with($rel, '.sk.inner')) {
				continue;
			}
			$stackId = is_array($layoutMeta) ? (string) ($layoutMeta['stack'] ?? 'none') : 'none';
			$memberFormat = is_array($layoutMeta) ? (string) ($layoutMeta['member_format'] ?? 'dual') : 'dual';
			if (str_ends_with($rel, '.inner')) {
				$innerFull = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
				$innerBytes = is_file($innerFull) ? (string) file_get_contents($innerFull) : '';
				if ($innerBytes === '') {
					throw new RuntimeException('enwik restore: missing text-inner fztx ' . $rel);
				}
				if ($stackId !== 'none' || str_starts_with($innerBytes, "FZSO\x01")) {
					require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
					$innerBytes = fractal_zip_text_stacked_outer_undo($innerBytes);
				}
				if (str_starts_with($innerBytes, "FZPA\x01")) {
					require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_tokenize.php';
					fractal_zip_tokenized_zpaq_load_vocab_from_fzpa_wire($innerBytes);
					$innerBytes = fractal_zip_text_paq_wire_undo($innerBytes);
					$innerBytes = fractal_zip_tokenized_zpaq_detokenize_plain(
						$innerBytes,
						is_array($preprocessMeta) ? $preprocessMeta : array()
					);
				}
				if ($memberFormat === 'phda9_xml') {
					$pagesInMember = min($pagesPerMember, $pageCount - $pageIdx);
					$memberLens = array();
					if ($sortedPageLens !== array() && $pageIdx + $pagesInMember <= count($sortedPageLens)) {
						$memberLens = array_slice($sortedPageLens, $pageIdx, $pagesInMember);
					}
					$skPlainPages = null;
					if ($preprocessId === 'consonant_hybrid_split' && !empty($layoutMeta['sk_dual'])) {
						$skRel = preg_replace('/\.inner$/', '.sk.inner', $rel);
						$skFull = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $skRel);
							if (is_file($skFull)) {
								$skWire = (string) file_get_contents($skFull);
								if ($stackId !== 'none' || str_starts_with($skWire, "FZSO\x01")) {
									require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
									$skWire = fractal_zip_text_stacked_outer_undo($skWire);
								}
								if (str_starts_with($skWire, "FZPA\x01")) {
									$skWire = fractal_zip_text_paq_wire_undo($skWire);
									require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_tokenize.php';
									if (fractal_zip_phda9_dict_inline_enabled() && fractal_zip_phda9_dict_inline_mode() === 'tokenize') {
										putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE=1');
										$skWire = fractal_zip_phda9_dict_inline_restore_plain($skWire);
									}
								}
								$chunkSkLens = is_array($layoutMeta['chunk_sk_lens'] ?? null)
									? ($layoutMeta['chunk_sk_lens'][$textInnerChunkIdx] ?? array())
									: array();
								if (!is_array($chunkSkLens) || $chunkSkLens === array()) {
									throw new RuntimeException('enwik restore: sk_dual missing chunk_sk_lens chunk ' . $textInnerChunkIdx);
								}
								$skPlainPages = enwik_split_text_chunk_by_lens($skWire, array_map('intval', $chunkSkLens));
								if ($skPlainPages === null) {
									throw new RuntimeException('enwik restore: sk_dual lens split failed ' . $skRel);
								}
							} else {
								$skPlainPages = array();
								for ($spi = 0; $spi < $pagesInMember; $spi++) {
									$skPlainPages[] = '';
								}
							}
					}
					$chunkPages = ($memberLens !== array())
						? enwik_split_member_chunk_pages_by_lens($innerBytes, $memberLens)
						: enwik_split_member_chunk_pages($innerBytes, $pagesInMember, $rel);
					if ($chunkPages === null) {
						throw new RuntimeException('enwik restore: phda9_xml lens split failed ' . $rel);
					}
					$perm = fractal_zip_enwik_text_inner_chunk_perm(
						$textInnerChunkIdx,
						$pagesInMember,
						$chunkPerms,
						$implicitAlphaSort,
						$layoutIdRestore
					);
					$layoutPosByPage = array();
					foreach ($perm as $layoutPos => $pageInChunk) {
						$layoutPosByPage[(int) $pageInChunk] = (int) $layoutPos;
					}
					$chunkSidecarBase = $pageIdx;
					for ($i = 0; $i < count($chunkPages); $i++) {
						$layoutPos = $layoutPosByPage[$i] ?? $i;
						$pageBytes = (string) ($chunkPages[$layoutPos] ?? '');
						if ($preprocessId !== 'none') {
							$sidecar = $preSidecars[$chunkSidecarBase + $layoutPos] ?? null;
							if (!is_array($sidecar)) {
								throw new RuntimeException('enwik restore: missing phda9_xml preprocess sidecar page '
									. ($chunkSidecarBase + $layoutPos));
							}
							$pageBytes = fractal_zip_enwik_restore_phda9_preprocessed_page(
								$pageBytes,
								$preprocessId,
								$sidecar,
								is_array($preprocessMeta) ? $preprocessMeta : array(),
								is_array($skPlainPages)
									? (string) ($skPlainPages[$layoutPos] ?? '')
									: null
							);
						} elseif (!empty($layoutMeta['cfabb_plain_xml'])) {
							require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
							$entries = fractal_zip_cfabb_inline_cache_load();
							if ($entries === array()) {
								$entries = fractal_zip_cfabb_load_json();
							}
							$pageBytes = fractal_zip_cfabb_plain_xml_undo_page($pageBytes, $entries);
						}
						$sortedPages[] = $pageBytes;
						$pageIdx++;
					}
					$textInnerChunkIdx++;
					continue;
				}
				if ($memberFormat === 'phda9_article') {
					$shellRel = substr($rel, 0, -strlen('.inner')) . '.shell.xml';
					$shellFull = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $shellRel);
					$shellBytes = is_file($shellFull) ? (string) file_get_contents($shellFull) : '';
					if ($shellBytes === '') {
						throw new RuntimeException('enwik restore: missing phda9_article shell ' . $shellRel);
					}
					$pagesInMember = min($pagesPerMember, $pageCount - $pageIdx);
					$shellPages = enwik_split_member_chunk_pages($shellBytes, $pagesInMember, $rel);
					$memberTextLens = array_slice($sortedTextLens, $pageIdx, $pagesInMember);
					$textPages = enwik_split_text_chunk_by_lens($innerBytes, $memberTextLens);
					if ($textPages === null || count($textPages) !== count($shellPages)) {
						throw new RuntimeException('enwik restore: phda9_article lens mismatch ' . $rel);
					}
					$perm = fractal_zip_enwik_text_inner_chunk_perm(
						$textInnerChunkIdx,
						$pagesInMember,
						$chunkPerms,
						$implicitAlphaSort,
						$layoutIdRestore
					);
					$layoutPosByPage = array();
					foreach ($perm as $layoutPos => $pageInChunk) {
						$layoutPosByPage[(int) $pageInChunk] = (int) $layoutPos;
					}
					$chunkSidecarBase = $pageIdx;
					foreach ($shellPages as $i => $shellPage) {
						$layoutPos = $layoutPosByPage[(int) $i] ?? (int) $i;
						$textPayload = (string) ($textPages[$layoutPos] ?? '');
						if ($preprocessId !== 'none') {
							$sidecar = $preSidecars[$chunkSidecarBase + $layoutPos] ?? null;
							if (!is_array($sidecar)) {
								throw new RuntimeException('enwik restore: missing phda9_article preprocess sidecar page '
									. ($chunkSidecarBase + $layoutPos));
							}
							$textPayload = fractal_zip_enwik_undo_preprocess_text(
								$preprocessId,
								$textPayload,
								$sidecar,
								is_array($preprocessMeta) ? $preprocessMeta : array()
							);
						}
						$sortedPages[] = fractal_zip_enwik_inject_text_into_shell_page($shellPage, $textPayload);
						$pageIdx++;
					}
					$textInnerChunkIdx++;
					continue;
				}
				$parsed = fractal_zip_enwik_parse_split_inner_blob($innerBytes);
				$shellBytes = (string) $parsed['shell'];
				$textBytes = (string) $parsed['text'];
				if ($preprocessId === 'stat_pred_inner' && !$statPredEmbeddedLoaded
					&& ($parsed['stat_pred_blob'] ?? '') !== '') {
					require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
					$embedded = fractal_zip_enwik_stat_pred_deserialize_compact_meta((string) $parsed['stat_pred_blob']);
					if (is_array($preprocessMeta)) {
						$preprocessMeta['vocab'] = $embedded['vocab'];
						$preprocessMeta['bigram_succ_ids'] = $embedded['bigram_succ_ids'];
						$preprocessMeta['codec'] = $embedded['codec'];
						if (is_array($embedded['sidecars'] ?? null) && $embedded['sidecars'] !== array()) {
							$preprocessMeta['sidecars'] = $embedded['sidecars'];
							$preSidecars = $embedded['sidecars'];
						}
						$statPredEmbeddedLoaded = true;
					}
				}
			} else {
				$parsed = array('fold_dict' => '', 'stat_pred_blob' => '');
				$textRel = substr($rel, 0, -strlen('.shell.xml')) . '.text';
				$shellFull = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
				$textFull = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $textRel);
				$shellBytes = is_file($shellFull) ? (string) file_get_contents($shellFull) : '';
				$textBytes = is_file($textFull) ? (string) file_get_contents($textFull) : '';
				if ($shellBytes === '' || $textBytes === '') {
					throw new RuntimeException('enwik restore: missing text-inner pair ' . $rel);
				}
				if ($stackId !== 'none' || str_starts_with($textBytes, "FZSO\x01")) {
					require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
					$textBytes = fractal_zip_text_stacked_outer_undo($textBytes);
				}
			}
			$pagesInMember = min($pagesPerMember, $pageCount - $pageIdx);
			$shellPages = enwik_split_member_chunk_pages($shellBytes, $pagesInMember, $rel);
			$memberTextLens = array_slice($sortedTextLens, $pageIdx, $pagesInMember);
			$textPages = enwik_split_text_chunk_by_lens($textBytes, $memberTextLens);
			if ($textPages === null || count($textPages) !== count($shellPages)) {
				throw new RuntimeException('enwik restore: text-inner lens mismatch ' . $rel);
			}
			$perm = fractal_zip_enwik_text_inner_chunk_perm(
				$textInnerChunkIdx,
				$pagesInMember,
				$chunkPerms,
				$implicitAlphaSort,
				$layoutIdRestore
			);
			$layoutPosByPage = array();
			foreach ($perm as $layoutPos => $pageInChunk) {
				$layoutPosByPage[(int) $pageInChunk] = (int) $layoutPos;
			}
			$chunkSidecarBase = $pageIdx;
			foreach ($shellPages as $i => $shellPage) {
				$layoutPos = $layoutPosByPage[(int) $i] ?? (int) $i;
				$textPayload = (string) ($textPages[$layoutPos] ?? '');
				if ($preprocessId !== 'none') {
					$sidecar = $preSidecars[$chunkSidecarBase + $layoutPos] ?? null;
					if (!is_array($sidecar)) {
						throw new RuntimeException('enwik restore: missing text-inner preprocess sidecar page '
							. ($chunkSidecarBase + $layoutPos));
					}
					if ($preprocessId === 'dict_nncp' && is_array($preprocessMeta['vocab'] ?? null)) {
						$sidecar['vocab'] = $preprocessMeta['vocab'];
						$sidecar['codec'] = (string) ($preprocessMeta['codec'] ?? 'segment_v2');
					}
					if ($preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner') {
						require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
						$foldRaw = (string) ($parsed['fold_dict'] ?? '');
						if ($foldRaw === '' && is_array($preprocessMeta)) {
							$foldRaw = (string) ($preprocessMeta['fold_dict_inner_blob'] ?? '');
						}
						if ($foldRaw !== '') {
							$sidecar['vocab'] = fractal_zip_enwik_inner_fold_decode_vocab($foldRaw);
							$sidecar['codec'] = 'segment_v2';
						}
					}
					if (($preprocessId === 'stat_wrt' || $preprocessId === 'wrt_xwrt')
						&& is_array($preprocessMeta['codes'] ?? null)) {
						$sidecar['codes'] = $preprocessMeta['codes'];
					}
					if ($preprocessId === 'stat_isp' && is_array($preprocessMeta['vocab'] ?? null)) {
						$sidecar['vocab'] = $preprocessMeta['vocab'];
					}
					if ($preprocessId === 'stat_syllable_isp' && is_array($preprocessMeta['vocab'] ?? null)) {
						$sidecar['vocab'] = $preprocessMeta['vocab'];
					}
					if ($preprocessId === 'consonant_hybrid') {
						require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
						$sidecar = fractal_zip_enwik_merge_consonant_hybrid_sidecar($sidecar, $preprocessMeta);
					}
					if (fractal_zip_enwik_stat_pred_preprocess_family($preprocessId) && is_array($preprocessMeta['vocab'] ?? null)) {
						require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
						$sidecar['vocab'] = $preprocessMeta['vocab'];
						if (is_array($preprocessMeta['bigram_succ_ids'] ?? null)) {
							$sidecar['bigram_succ'] = fractal_zip_enwik_stat_pred_expand_bigram_ids(
								$preprocessMeta['bigram_succ_ids'],
								$preprocessMeta['vocab']
							);
						} else {
							$sidecar['bigram_succ'] = $preprocessMeta['bigram_succ'] ?? array();
						}
					}
					if ($preprocessId === 'qg_hybrid_root' && is_array($preprocessMeta['word_vocab'] ?? null)) {
						$sidecar['word_vocab'] = $preprocessMeta['word_vocab'];
						$sidecar['sub_vocab'] = $preprocessMeta['sub_vocab'] ?? array();
					}
					if (($preprocessId === 'qg_subword_root' || $preprocessId === 'qg_word_root')
						&& is_array($preprocessMeta['vocab'] ?? null)) {
						$sidecar['vocab'] = $preprocessMeta['vocab'];
					}
					$undoPreprocessId = fractal_zip_enwik_stat_pred_preprocess_family($preprocessId) ? 'stat_pred'
						: (in_array($preprocessId, array('dict_inner', 'dict_phda9_inner'), true) ? 'dict_nncp' : $preprocessId);
					$textPayload = fractal_zip_text_preprocess_undo($undoPreprocessId, $textPayload, $sidecar);
				}
				$sortedPages[] = fractal_zip_enwik_inject_text_into_shell_page($shellPage, $textPayload);
				$pageIdx++;
			}
			$textInnerChunkIdx++;
			continue;
		}
		$full = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$bytes = is_file($full) ? file_get_contents($full) : false;
		if ($bytes === false) {
			throw new RuntimeException('enwik restore: missing member ' . $rel);
		}
		$bytes = (string) $bytes;
		if ($pagesPerMember === 1) {
			$sortedPages[] = $bytes;
			$pageIdx++;
			continue;
		}
		$pagesInMember = min($pagesPerMember, $pageCount - $pageIdx);
		$chunkPages = null;
		if ($sortedPageLens !== array() && $pageIdx + $pagesInMember <= count($sortedPageLens)) {
			$memberLens = array_slice($sortedPageLens, $pageIdx, $pagesInMember);
			if (count($memberLens) === $pagesInMember) {
				$chunkPages = enwik_split_member_chunk_pages_by_lens($bytes, $memberLens);
			}
		}
		if ($chunkPages === null) {
			$chunkPages = enwik_split_member_chunk_pages($bytes, $pagesInMember, $rel);
		}
		foreach ($chunkPages as $pageBytes) {
			$sortedPages[] = $pageBytes;
			$pageIdx++;
		}
	}
	if (count($sortedPages) !== count($origIndexBySorted)) {
		throw new RuntimeException('enwik restore: page count mismatch');
	}
	$origPages = array_fill(0, count($origIndexBySorted), '');
	foreach ($sortedPages as $sortedIdx => $pageBytes) {
		$origIdx = (int) $origIndexBySorted[$sortedIdx];
		if ($origIdx < 0 || $origIdx >= count($origPages)) {
			throw new RuntimeException('enwik restore: orig index out of range');
		}
		$origPages[$origIdx] = $pageBytes;
	}
	foreach ($origPages as $i => $pb) {
		if ($pb === '') {
			throw new RuntimeException('enwik restore: missing page at orig index ' . $i);
		}
	}
	return $header . implode('', $origPages) . $footer;
}

function fractal_zip_enwik_recursive_remove(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $fi) {
		if ($fi->isDir()) {
			@rmdir($fi->getPathname());
		} else {
			@unlink($fi->getPathname());
		}
	}
	@rmdir($dir);
}

/**
 * Detect single extensionless enwik-shaped member in $dir; build virtual sorted folder.
 *
 * @return array<string, mixed>|null ctx for zip_folder / FZEP append
 */
/**
 * Text-inner wire path: dual virtual members (.text + .shell.xml) per chunk.
 *
 * @param list<array{title: string, origIndex: int, start: int, len: int, sortedIndex?: int}> $sortedChunk
 * @return array<string, mixed>
 */
function enwik_build_text_inner_virtual_folder_from_refs(
	string $virtualDir,
	array $sortedChunk,
	string $blob,
	string $header,
	string $footer,
	string $outputFile,
	string $outputDir,
	string $layoutId = 'sort_title'
): array {
	$preprocessId = fractal_zip_enwik_text_inner_preprocess_id();
	if ($preprocessId !== 'none') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
	if (is_dir($virtualDir)) {
		fractal_zip_enwik_recursive_remove($virtualDir);
	}
	if (!mkdir($virtualDir, 0755, true) && !is_dir($virtualDir)) {
		throw new RuntimeException('enwik text-inner virtual folder: cannot create ' . $virtualDir);
	}
	$pagesDir = $virtualDir . DIRECTORY_SEPARATOR . 'pages';
	if (!mkdir($pagesDir, 0755, true) && !is_dir($pagesDir)) {
		throw new RuntimeException('enwik text-inner virtual folder: cannot create pages dir');
	}
	$pageCount = count($sortedChunk);
	$memberFormat = fractal_zip_enwik_text_inner_member_format();
	$monoInner = fractal_zip_enwik_text_inner_mono_enabled()
		&& in_array($memberFormat, array('fztx', 'phda9_xml', 'phda9_article'), true);
	$pagesPerMember = $monoInner
		? fractal_zip_enwik_text_inner_mono_pages_per_member($pageCount)
		: fractal_zip_enwik_pages_per_member();
	$memberRelPaths = array();
	$origIndexBySorted = array();
	$sortedPageLens = array();
	$splitPages = array();
	$buildJobs = fractal_zip_enwik_text_inner_build_jobs();
	$probeMode = false;
	if (!function_exists('fractal_zip_parallel_runtime_probe_mode')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_runtime.php';
	}
	if (function_exists('fractal_zip_parallel_runtime_probe_mode')) {
		$probeMode = fractal_zip_parallel_runtime_probe_mode();
	}
	if ($buildJobs > 1 && PHP_SAPI === 'cli' && !$probeMode) {
		$splitPages = enwik_build_text_inner_split_pages_parallel($sortedChunk, $blob, $buildJobs);
	} else {
		$splitPages = enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
	}
	foreach ($sortedChunk as $p) {
		$origIndexBySorted[] = (int) $p['origIndex'];
	}
	$dictPreOpts = array();
	$sharedDictVocab = array();
	if ($preprocessId === 'dict_nncp' || $preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner' || $preprocessId === 'stat_wrt'
		|| $preprocessId === 'stat_isp' || $preprocessId === 'stat_syllable_isp'
		|| $preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split'
		|| $preprocessId === 'consonant_hybrid_lossy' || $preprocessId === 'wiki_lom'
		|| fractal_zip_enwik_stat_pred_preprocess_family($preprocessId)
		|| in_array($preprocessId, array('qg_hybrid_root', 'qg_subword_root', 'qg_word_root'), true)) {
		$vocabPages = (int) (getenv('FRACTAL_ZIP_TEXT_DICT_VOCAB_PAGES') ?: 0);
		if ($vocabPages <= 0) {
			$vocabPages = ($monoInner || fractal_zip_enwik_inner_fold_is_preprocess($preprocessId))
				? $pageCount
				: min(32, $pageCount);
		} else {
			$vocabPages = min($vocabPages, $pageCount);
		}
		$mineText = '';
		for ($vi = 0; $vi < $vocabPages; $vi++) {
			$mineText .= (string) ($splitPages[$vi]['text'] ?? '');
		}
		if ($preprocessId === 'stat_wrt') {
			$maxCodes = (int) (getenv('FRACTAL_ZIP_ENWIK_STAT_WRT_MAX_CODES') ?: 200);
			$sharedDictVocab = fractal_zip_enwik_stat_wrt_vocab_from_text($mineText, $maxCodes);
			$built = fractal_zip_text_wrt_xwrt_build_map_from_vocab($sharedDictVocab, $maxCodes);
			$dictPreOpts = array('codes' => $built['codes'], 'frozen' => true);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] stat_wrt frozen codes: ' . count($built['codes']) . ' from '
					. $vocabPages . " pages\n");
			}
		} elseif ($preprocessId === 'stat_isp') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			$sharedDictVocab = fractal_zip_enwik_stat_isp_mine_vocab($mineText);
			$tables = fractal_zip_enwik_stat_isp_vocab_tables($sharedDictVocab);
			$dictPreOpts = array(
				'vocab' => $tables['vocab'],
				'vocab_index' => $tables['vocab_index'],
				'frozen' => true,
			);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] stat_isp frozen vocab: ' . count($tables['vocab']) . ' words from '
					. $vocabPages . " pages\n");
			}
		} elseif ($preprocessId === 'stat_syllable_isp') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			$sharedDictVocab = fractal_zip_enwik_stat_syllable_isp_mine_vocab($mineText);
			$tables = fractal_zip_enwik_stat_syllable_isp_vocab_tables($sharedDictVocab);
			$dictPreOpts = array(
				'vocab' => $tables['vocab'],
				'vocab_index' => $tables['vocab_index'],
				'frozen' => true,
			);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] stat_syllable_isp frozen vocab: ' . count($tables['vocab']) . ' syllables from '
					. $vocabPages . " pages\n");
			}
		} elseif ($preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split'
			|| $preprocessId === 'consonant_hybrid_lossy') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			$pageTexts = array();
			for ($vi = 0; $vi < $vocabPages; $vi++) {
				$pageTexts[] = (string) ($splitPages[$vi]['text'] ?? '');
			}
			$consonantModel = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $mineText);
			$dictPreOpts = array(
				'consonant_model' => $consonantModel,
				'frozen' => true,
			);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] ' . $preprocessId . ' frozen model: sk_vocab='
					. count($consonantModel['skeleton_vocab'] ?? array())
					. ' unique=' . count($consonantModel['skeleton_unique'] ?? array())
					. ' ambig=' . count($consonantModel['skeleton_ambig'] ?? array())
					. ' ctx=' . count($consonantModel['context_unique'] ?? array())
					. ' from ' . $vocabPages . " pages\n");
			}
		} elseif (fractal_zip_enwik_stat_pred_preprocess_family($preprocessId)) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			if ($preprocessId === 'stat_pred_inner') {
				$codecEnv = getenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC');
				if ($codecEnv === false || trim((string) $codecEnv) === '') {
					putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC='
						. fractal_zip_enwik_stat_pred_inner_default_payload_codec());
				}
				$model = fractal_zip_enwik_stat_pred_inner_frozen_model($mineText);
			} else {
				$maxWords = null;
				$pruneAtMine = false;
				$model = fractal_zip_enwik_stat_pred_mine_model(
					$mineText,
					$maxWords,
					null,
					$pruneAtMine
				);
			}
			$dictPreOpts = array(
				'stat_model' => $model,
				'vocab' => $model['vocab'],
				'vocab_index' => $model['vocab_index'],
				'bigram_succ' => $model['bigram_succ'],
				'bigram_rank' => $model['bigram_rank'],
				'frozen' => true,
			);
			$sharedDictVocab = $model['vocab'];
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] ' . $preprocessId . ' frozen model: vocab=' . count($model['vocab'])
					. ' bigrams=' . count($model['bigram_succ']) . ' from ' . $vocabPages . " pages\n");
			}
		} elseif ($preprocessId === 'qg_hybrid_root') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
			require_once '/srv/http/quantum_grammar/src/HybridRootCodec.php';
			$qgModel = HybridRootCodec::mineModel($mineText);
			$dictPreOpts = array('qg_model' => $qgModel, 'frozen' => true);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] qg_hybrid_root frozen model: word=' . count($qgModel['word']['vocab'] ?? array())
					. ' sub=' . count($qgModel['sub']['vocab'] ?? array()) . ' from ' . $vocabPages . " pages\n");
			}
		} elseif ($preprocessId === 'qg_subword_root') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
			require_once '/srv/http/quantum_grammar/src/SubWordRootCodec.php';
			$qgModel = SubWordRootCodec::mineVocab($mineText);
			$dictPreOpts = array('qg_model' => $qgModel, 'frozen' => true);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] qg_subword_root frozen vocab: ' . count($qgModel['vocab'] ?? array())
					. ' from ' . $vocabPages . " pages\n");
			}
		} elseif ($preprocessId === 'qg_word_root') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
			require_once '/srv/http/quantum_grammar/src/WordRootCodec.php';
			$qgModel = WordRootCodec::mineVocab($mineText);
			$dictPreOpts = array('qg_model' => $qgModel, 'frozen' => true);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] qg_word_root frozen vocab: ' . count($qgModel['vocab'] ?? array())
					. ' from ' . $vocabPages . " pages\n");
			}
		} elseif ($preprocessId === 'wiki_lom') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
			$pageRefs = array();
			for ($vi = 0; $vi < $vocabPages; $vi++) {
				$pageRefs[] = array(
					'title' => (string) ($sortedChunk[$vi]['title'] ?? ''),
					'start' => (int) ($sortedChunk[$vi]['start'] ?? 0),
					'len' => (int) ($sortedChunk[$vi]['len'] ?? 0),
				);
			}
			$wireProfile = fractal_zip_wiki_lom_phda9_wire_profile_wanted($memberFormat);
			$layerFlags = $wireProfile ? fractal_zip_wiki_lom_layer_flags(true) : array();
			if ($wireProfile && !fractal_zip_wiki_lom_tables_needed($layerFlags)) {
				$tables = fractal_zip_wiki_lom_empty_tables();
			} else {
				$tables = fractal_zip_wiki_lom_mine_tables($mineText, $pageRefs, $blob, array(
					'corpus_pages' => $pageCount,
				), $layerFlags);
			}
			$dictPreOpts = array_merge($tables, array('frozen' => true));
			if ($wireProfile) {
				$dictPreOpts = array_merge($dictPreOpts, $layerFlags);
			}
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] wiki_lom frozen tables: titles=' . count($tables['title_to_id'] ?? array())
					. ' urls=' . count($tables['urls'] ?? array())
					. ' tags=' . count($tables['tags'] ?? array())
					. ' abbrevs=' . count($tables['abbrevs_list'] ?? array())
					. ' cfabb=' . count($tables['acronyms_list'] ?? array())
					. ' templates=' . count($tables['templates_list'] ?? array())
					. ' from ' . $vocabPages . " pages\n");
			}
		} else {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
			if ($preprocessId === 'dict_phda9_inner') {
				require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_dict_phda9_inner.php';
				$sharedDictVocab = fractal_zip_enwik_phda9_inner_frozen_vocab($mineText);
			} else {
				$sharedDictVocab = fractal_zip_text_dict_nncp_mine_vocab($mineText);
			}
			$dictPreOpts = array('vocab' => $sharedDictVocab, 'frozen' => true);
			$dictLabel = $preprocessId === 'dict_phda9_inner' ? 'dict_phda9_inner'
				: ($preprocessId === 'dict_inner' ? 'dict_inner' : 'dict_nncp');
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] ' . $dictLabel . ' frozen vocab: ' . count($sharedDictVocab) . ' words from '
					. $vocabPages . " pages\n");
			}
		}
	}
	if ($preprocessId === 'wrt_xwrt') {
		$dictPreOpts = array('max_codes' => (int) (getenv('FRACTAL_ZIP_ENWIK_WRT_MAX_CODES') ?: 200));
	}
	$statPredBigramHits = 0;
	$statPredLiteralWords = 0;
	$statPredBigramPrevUsed = array();
	/** @var array<string, array<int, true>> */
	$statPredBigramRanksUsed = array();
	$statPredApplyId = fractal_zip_enwik_stat_pred_preprocess_family($preprocessId) ? 'stat_pred'
		: (in_array($preprocessId, array('dict_inner', 'dict_phda9_inner'), true) ? 'dict_nncp' : $preprocessId);
	$statPredInnerBlob = '';
	$statPredMemberBlob = '';
	$statPredMemberFold = $preprocessId === 'stat_pred_inner'
		&& $monoInner
		&& $memberFormat === 'fztx'
		&& fractal_zip_enwik_stat_pred_inner_member_fold_enabled();
	$skDualEnabled = false;
	$wikiLomPhda9Escape = false;
	if ($preprocessId === 'consonant_hybrid_split' && $memberFormat === 'phda9_xml') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
		$skDualEnabled = fractal_zip_enwik_consonant_sk_dual_enabled();
	}
	if ($preprocessId === 'wiki_lom' && $memberFormat === 'phda9_xml') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
		$wikiLomPhda9Escape = true;
	}
	foreach ($splitPages as $idx => $pg) {
		if ($preprocessId !== 'none') {
			$pre = fractal_zip_text_preprocess_apply($statPredApplyId, (string) $pg['text'], $dictPreOpts);
			$splitPages[$idx]['wire_text'] = (string) $pre['payload'];
			if ($wikiLomPhda9Escape) {
				$splitPages[$idx]['wire_text'] = fractal_zip_wiki_lom_phda9_wire_escape(
					(string) $splitPages[$idx]['wire_text']
				);
			}
			if ($preprocessId === 'consonant_hybrid_split') {
				$splitPages[$idx]['wire_sk'] = (string) ($pre['meta']['sk_payload'] ?? '');
				if ($skDualEnabled) {
					$skParts = fractal_zip_enwik_consonant_hybrid_split_part_payload((string) $splitPages[$idx]['wire_text']);
					$splitPages[$idx]['wire_text'] = fractal_zip_enwik_consonant_sk_phda9_wire_escape_literal(
						(string) $skParts['literal']
					);
					$splitPages[$idx]['wire_sk_ascii'] = (string) $skParts['sk'];
				}
			}
			$sidecar = $pre['sidecar'];
			if (fractal_zip_enwik_stat_pred_preprocess_family($preprocessId)) {
				$statPredBigramHits += (int) ($pre['meta']['bigram_hits'] ?? 0);
				$statPredLiteralWords += (int) ($pre['meta']['literal_words'] ?? 0);
				foreach ((array) ($pre['meta']['bigram_prev_used'] ?? array()) as $pw) {
					$pw = (string) $pw;
					if ($pw !== '') {
						$statPredBigramPrevUsed[$pw] = true;
					}
				}
				foreach ((array) ($pre['meta']['bigram_ranks_used'] ?? array()) as $pw => $ranks) {
					$pw = (string) $pw;
					if ($pw === '' || !is_array($ranks)) {
						continue;
					}
					if (!isset($statPredBigramRanksUsed[$pw])) {
						$statPredBigramRanksUsed[$pw] = array();
					}
					foreach (array_keys($ranks) as $r) {
						$statPredBigramRanksUsed[$pw][(int) $r] = true;
					}
				}
			}
			$sidecar['preprocess'] = $preprocessId;
			if ($preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split') {
				require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
				$sidecar['payload_codec'] = $preprocessId === 'consonant_hybrid_split'
					? 'split_ascii'
					: fractal_zip_enwik_consonant_hybrid_payload_codec($dictPreOpts);
			}
			if ($preprocessId === 'stat_syllable_isp') {
				require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
				$sidecar['payload_codec'] = fractal_zip_enwik_stat_syllable_isp_payload_codec($dictPreOpts);
			}
			if (($preprocessId === 'dict_nncp' || $preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner') && $sharedDictVocab !== array()) {
				unset($sidecar['vocab']);
			}
			if (($preprocessId === 'stat_wrt' || $preprocessId === 'wrt_xwrt')
				&& isset($dictPreOpts['codes']) && is_array($dictPreOpts['codes'])) {
				unset($sidecar['codes']);
			}
			$splitPages[$idx]['wire_sidecar'] = $sidecar;
		} else {
			$splitPages[$idx]['wire_text'] = (string) $pg['text'];
			$splitPages[$idx]['wire_sidecar'] = array('preprocess' => 'none');
		}
	}
	if ($statPredMemberFold) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
		$sm = is_array($dictPreOpts['stat_model'] ?? null) ? $dictPreOpts['stat_model'] : array();
		$smTrailer = fractal_zip_enwik_stat_pred_trailer_model(
			$sm,
			array_keys($statPredBigramPrevUsed),
			fractal_zip_enwik_stat_pred_inner_bigram_max_prev(),
			$statPredBigramRanksUsed
		);
		$statPredMemberBlob = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
			'preprocess' => 'stat_pred_inner',
			'vocab' => $smTrailer['vocab'] ?? array(),
			'bigram_succ_ids' => fractal_zip_enwik_stat_pred_compact_bigram_ids(
				is_array($smTrailer['bigram_succ'] ?? null) ? $smTrailer['bigram_succ'] : array(),
				is_array($smTrailer['vocab_index'] ?? null) ? $smTrailer['vocab_index'] : array()
			),
			'bigram_ranks_used' => $statPredBigramRanksUsed,
			'sidecars' => array(),
			'frozen' => true,
			'include_sidecars' => false,
			'bigram_uint16' => true,
		));
		$statPredInnerBlob = $statPredMemberBlob;
	}
	$sortedTextLens = array();
	$allSidecars = array();
	$chunkPerms = array();
	$chunkPermsAllIdentity = ($layoutId === 'sort_title');
	$chunkIdx = 0;
	$stackId = fractal_zip_enwik_text_inner_stack_id();
	$shootoutEnabled = fractal_zip_enwik_member_codec_shootout_enabled();
	if ($stackId !== 'none' || $shootoutEnabled) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
	}
	$wireTextForSidecar = array();
	$pendingShootout = array();
	$pendingPhda9 = array();
	$innerFoldBlob = '';
	$phda9ChunkTotal = (int) max(1, (int) ceil($pageCount / max(1, $pagesPerMember)));
	if ($memberFormat === 'phda9_xml' || $memberFormat === 'phda9_article') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
	}
	$phda9ParallelEncode = ($memberFormat === 'phda9_xml' || $memberFormat === 'phda9_article')
		&& $pageCount > $pagesPerMember
		&& fractal_zip_enwik_phda9_english_fork_pool_allowed()
		&& fractal_zip_enwik_phda9_english_jobs($phda9ChunkTotal) > 1;
	$innerFoldToTrailer = fractal_zip_enwik_inner_fold_is_preprocess($preprocessId) && !$statPredMemberFold;
	$chunkSkLensAll = array();
	$pendingPhda9Sk = array();
	$layoutMeta = array(
		'layout' => $layoutId,
		'per_member' => true,
		'preprocess' => $preprocessId,
		'stack' => $stackId,
		'member_format' => $memberFormat,
		'mono_inner' => $monoInner,
	);
	if ($skDualEnabled) {
		$layoutMeta['sk_dual'] = true;
		$layoutMeta['sk_phda9_wire_escape'] = true;
	}
	if ($wikiLomPhda9Escape) {
		$layoutMeta['wiki_lom_phda9_wire_escape'] = true;
	}
	if ($preprocessId === 'wiki_lom' && fractal_zip_cfabb_inline_enabled()) {
		$layoutMeta['cfabb_inline'] = true;
		$layoutMeta['cfabb_no_fold'] = fractal_zip_cfabb_no_fold_enabled();
	}
	if ($preprocessId === 'none') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
		if (fractal_zip_cfabb_plain_xml_enabled()) {
			$layoutMeta['cfabb_plain_xml'] = true;
			if (fractal_zip_cfabb_inline_enabled()) {
				$layoutMeta['cfabb_inline'] = true;
				$layoutMeta['cfabb_no_fold'] = fractal_zip_cfabb_no_fold_enabled();
			}
		}
	}
	for ($i = 0; $i < $pageCount; $i += $pagesPerMember) {
		$chunk = array_slice($sortedChunk, $i, $pagesPerMember);
		$chunkSplit = array_slice($splitPages, $i, $pagesPerMember);
		$chunkPrePageLens = null;
		$layoutInput = array();
		foreach ($chunkSplit as $pg) {
			$layoutInput[] = array(
				'title' => (string) $pg['title'],
				'origIndex' => (int) $pg['origIndex'],
				'text' => (string) $pg['wire_text'],
			);
		}
		$chunkLayout = fractal_zip_enwik_text_layout_apply($layoutInput, $layoutId, array('seed' => 1));
		$perm = $chunkLayout['meta']['perm'] ?? range(0, count($chunkSplit) - 1);
		$perm = array_values(array_map('intval', $perm));
		if ($layoutId === 'sort_title' && !fractal_zip_enwik_chunk_perm_is_identity($perm)) {
			$chunkPermsAllIdentity = false;
		}
		$chunkPerms[] = $perm;
		$chunkRefsOrdered = array();
		$chunkSplitOrdered = array();
		foreach ($perm as $pidx) {
			$pidx = (int) $pidx;
			$chunkRefsOrdered[] = $chunk[$pidx];
			$chunkSplitOrdered[] = $chunkSplit[$pidx];
		}
		foreach ($perm as $pidx) {
			$pidx = (int) $pidx;
			$sortedTextLens[] = strlen((string) ($chunkSplit[$pidx]['wire_text'] ?? ''));
			$sidecar = $chunkSplit[$pidx]['wire_sidecar'] ?? array('preprocess' => $preprocessId);
			if (($preprocessId === 'dict_nncp' || $preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner') && $sharedDictVocab !== array()) {
				unset($sidecar['vocab']);
			}
			if ($preprocessId === 'stat_wrt' && isset($dictPreOpts['codes'])) {
				unset($sidecar['codes']);
			}
			if ($preprocessId === 'stat_isp' && isset($dictPreOpts['vocab'])) {
				unset($sidecar['vocab']);
			}
			if ($preprocessId === 'stat_syllable_isp' && isset($dictPreOpts['vocab'])) {
				unset($sidecar['vocab']);
			}
			if ($preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split') {
				unset($sidecar['skeleton_vocab'], $sidecar['skeleton_ambig'], $sidecar['skeleton_unique'], $sidecar['context_unique']);
			}
			if ($preprocessId === 'consonant_hybrid_lossy' && !empty($dictPreOpts['frozen'])) {
				unset($sidecar['skeleton_unique'], $sidecar['skeleton_ambig'], $sidecar['context_unique']);
			}
			if (fractal_zip_enwik_stat_pred_preprocess_family($preprocessId)) {
				unset($sidecar['vocab'], $sidecar['bigram_succ']);
			}
			if ($preprocessId === 'qg_hybrid_root') {
				unset($sidecar['word_vocab'], $sidecar['sub_vocab']);
			}
			if ($preprocessId === 'qg_subword_root' || $preprocessId === 'qg_word_root') {
				unset($sidecar['vocab']);
			}
			if ($preprocessId === 'wiki_lom' && !empty($dictPreOpts['frozen'])) {
				unset($sidecar['title_to_id'], $sidecar['id_to_title'], $sidecar['urls'], $sidecar['tags'], $sidecar['abbrevs'], $sidecar['acronyms'], $sidecar['templates']);
				if (!fractal_zip_wiki_lom_tables_needed(array(
					'link_ids' => !empty($dictPreOpts['link_ids']),
					'url_dict' => !empty($dictPreOpts['url_dict']),
					'tag_ids' => !empty($dictPreOpts['tag_ids']),
					'abbrevs' => !empty($dictPreOpts['abbrevs']),
					'acronyms' => !empty($dictPreOpts['acronyms']),
					'templates' => !empty($dictPreOpts['templates']),
				))) {
					unset($sidecar['flags']);
				}
			}
			if ($preprocessId === 'wiki_lom') {
				$sidecar = fractal_zip_wiki_lom_sidecar_json_pack($sidecar);
			}
			$allSidecars[] = $sidecar;
		}
		$shellBuf = '';
		foreach ($chunkSplit as $pg) {
			$shellBuf .= (string) $pg['shell'];
		}
		$textBuf = (string) $chunkLayout['text_blob'];
		if (fractal_zip_enwik_stat_sidecar_enabled()) {
			$wireTextForSidecar[] = $textBuf;
		}
		if ($memberFormat === 'fztx') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
			$dictFoldBlob = '';
			$statPredBlob = '';
			if (!$innerFoldToTrailer && ($preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner')) {
				if ($sharedDictVocab !== array()) {
					$dictFoldBlob = fractal_zip_enwik_inner_fold_encode_vocab($sharedDictVocab);
				}
			}
			if ($statPredMemberFold && $chunkIdx === 0 && $statPredMemberBlob !== '') {
				$statPredBlob = $statPredMemberBlob;
			}
			$skFoldBlob = '';
			if ($preprocessId === 'consonant_hybrid_split') {
				$skFoldBlob = '';
				foreach ($perm as $pidx) {
					$skFoldBlob .= (string) ($chunkSplit[(int) $pidx]['wire_sk'] ?? '');
				}
			}
			$innerBlob = fractal_zip_enwik_build_split_inner_blob(
				$textBuf,
				$shellBuf,
				(array) ($chunkLayout['meta'] ?? array()),
				$statPredBlob,
				$dictFoldBlob,
				$skFoldBlob
			);
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_paq.php';
			$parVocab = ($preprocessId === 'dict_phda9_inner' && $sharedDictVocab !== array())
				? $sharedDictVocab : array();
			$parWire = fractal_zip_parallel_paq_try_wrap_member($innerBlob, $parVocab);
			if ($parWire !== null && strlen($parWire) < strlen($innerBlob)) {
				$innerBlob = $parWire;
				if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
					@fwrite(STDERR, '[enwik] parallel_paq member wrap chunk ' . $chunkIdx
						. ' ' . number_format(strlen($parWire)) . " B\n");
				}
			}
			$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array(
				'preprocess' => $preprocessId,
				'fold_dict_bytes' => strlen($dictFoldBlob),
				'fold_stat_bytes' => strlen($statPredBlob),
				'fold_total_bytes' => strlen($dictFoldBlob) + strlen($statPredBlob),
				'stat_pred_member_fold' => $statPredMemberFold,
			);
			$innerRel = sprintf('pages/chunk_%05d.inner', $chunkIdx);
			$innerFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $innerRel);
			if ($shootoutEnabled && fractal_zip_enwik_member_shootout_jobs() > 1) {
				// Defer pick; parallel worker pool runs after the chunk loop.
				$pendingShootout[] = array(
					'chunkIdx' => $chunkIdx,
					'innerFull' => $innerFull,
					'innerBlob' => $innerBlob,
				);
				$memberRelPaths[] = $innerRel;
				foreach ($chunk as $p) {
					$sortedPageLens[] = (int) $p['len'];
				}
				$chunkIdx++;
				continue;
			}
			if ($shootoutEnabled) {
				$shoot = fractal_zip_enwik_member_shootout_pick($innerBlob);
				$innerBlob = (string) $shoot['payload'];
				if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
					@fwrite(STDERR, '[enwik] member shootout chunk ' . $chunkIdx . ': ' . ($shoot['pick'] ?? '?')
						. ' ' . number_format((int) ($shoot['bytes'] ?? 0)) . " B\n");
					@fflush(STDERR);
				}
			} elseif ($stackId !== 'none') {
				$stackR = fractal_zip_text_stacked_outer_apply($stackId, $innerBlob);
				if (empty($stackR['roundtrip_ok'])) {
					throw new RuntimeException('enwik text-inner fztx stack roundtrip failed chunk ' . $chunkIdx);
				}
				$innerBlob = (string) $stackR['payload'];
			}
			if (file_put_contents($innerFull, $innerBlob) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: fztx write failed chunk ' . $chunkIdx);
			}
			$memberRelPaths[] = $innerRel;
		} elseif ($memberFormat === 'phda9_xml') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
			$skPlainBuf = '';
			$chunkSkLens = array();
			if ($skDualEnabled) {
				foreach ($perm as $pidx) {
					$skPart = (string) ($chunkSplit[(int) $pidx]['wire_sk_ascii'] ?? '');
					$chunkSkLens[] = strlen($skPart);
					$skPlainBuf .= $skPart;
				}
				$chunkSkLensAll[] = $chunkSkLens;
			}
			if ($preprocessId !== 'none') {
				$pre = fractal_zip_enwik_phda9_english_chunk_preprocessed_page_xml($chunkSplitOrdered);
				$pageXmlBuf = (string) $pre['page_xml'];
				$chunkPrePageLens = $pre['page_lens'];
			} else {
				$pageXmlBuf = '';
				$chunkPrePageLens = array();
				if (!empty($layoutMeta['cfabb_plain_xml'])) {
					require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
					$cfabbEntries = fractal_zip_cfabb_plain_xml_prepare_from_chunk($chunk, $blob);
					foreach ($chunkRefsOrdered as $p) {
						$page = substr($blob, (int) $p['start'], (int) $p['len']);
						$page = fractal_zip_cfabb_plain_xml_apply_page($page, $cfabbEntries);
						$chunkPrePageLens[] = strlen($page);
						$pageXmlBuf .= $page;
					}
				} else {
					$pageXmlBuf = fractal_zip_enwik_phda9_english_chunk_page_xml($chunkRefsOrdered, $blob);
					foreach ($chunkRefsOrdered as $p) {
						$chunkPrePageLens[] = (int) $p['len'];
					}
				}
			}
			$innerRel = sprintf('pages/chunk_%05d.inner', $chunkIdx);
			$innerFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $innerRel);
			if ($phda9ParallelEncode) {
				$pendingPhda9[] = array(
					'chunkIdx' => $chunkIdx,
					'innerFull' => $innerFull,
					'plainBuf' => $pageXmlBuf,
					'stackId' => $stackId,
				);
				if ($skDualEnabled) {
					$pendingPhda9Sk[] = array(
						'chunkIdx' => $chunkIdx,
						'skPlain' => $skPlainBuf,
					);
					if ($skPlainBuf !== '') {
						$memberRelPaths[] = sprintf('pages/chunk_%05d.sk.inner', $chunkIdx);
					}
				}
				$memberRelPaths[] = $innerRel;
				if (is_array($chunkPrePageLens) && $chunkPrePageLens !== array()) {
					foreach ($chunkPrePageLens as $pl) {
						$sortedPageLens[] = (int) $pl;
					}
				} else {
					foreach ($chunk as $p) {
						$sortedPageLens[] = (int) $p['len'];
					}
				}
				$chunkIdx++;
				continue;
			}
			$shootTimeout = (int) (getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC') ?: 0);
			if ($shootTimeout <= 0) {
				$shootTimeout = (int) (getenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC') ?: 0);
			}
			$cr = fractal_zip_enwik_phda9_english_compress_auto($pageXmlBuf, array(
				'wire_wrap' => true,
				'timeout_sec' => $shootTimeout > 0 ? $shootTimeout : 0,
			));
			if (empty($cr['roundtrip_ok']) || !is_string($cr['payload']) || $cr['payload'] === '') {
				throw new RuntimeException('enwik text-inner phda9_xml compress failed chunk ' . $chunkIdx);
			}
			$innerBlob = (string) $cr['payload'];
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] phda9_xml chunk ' . $chunkIdx . ': ' . ($cr['tool'] ?? 'phda9')
					. ' ' . number_format(strlen($innerBlob)) . ' B plain='
					. number_format(strlen($pageXmlBuf)) . ' sec=' . ($cr['seconds'] ?? 0) . "\n");
				@fflush(STDERR);
			}
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_member_shootout.php';
			$outerR = fractal_zip_enwik_phda9_inner_outer_apply($innerBlob, $stackId);
			$innerBlob = (string) $outerR['payload'];
			if (PHP_SAPI === 'cli' && is_resource(STDERR) && ($outerR['pick'] ?? 'raw') !== 'raw') {
				@fwrite(STDERR, '[enwik] phda9_xml outer chunk ' . $chunkIdx . ': ' . ($outerR['pick'] ?? '?')
					. ' ' . number_format((int) ($outerR['bytes'] ?? 0)) . " B\n");
				@fflush(STDERR);
			}
			if (file_put_contents($innerFull, $innerBlob) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: phda9_xml write failed chunk ' . $chunkIdx);
			}
			if ($skDualEnabled && $skPlainBuf !== '') {
				fractal_zip_enwik_consonant_sk_dual_write_phda9_member(
					$virtualDir,
					$chunkIdx,
					$skPlainBuf,
					$shootTimeout > 0 ? $shootTimeout : 0
				);
				$memberRelPaths[] = sprintf('pages/chunk_%05d.sk.inner', $chunkIdx);
			}
			$memberRelPaths[] = $innerRel;
		} elseif ($memberFormat === 'phda9_article') {
			$articleBuf = fractal_zip_enwik_phda9_english_chunk_article_text($chunkSplit, $textBuf);
			$shellRel = sprintf('pages/chunk_%05d.shell.xml', $chunkIdx);
			$innerRel = sprintf('pages/chunk_%05d.inner', $chunkIdx);
			$shellFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $shellRel);
			$innerFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $innerRel);
			if (file_put_contents($shellFull, $shellBuf) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: phda9_article shell write failed chunk ' . $chunkIdx);
			}
			if ($phda9ParallelEncode) {
				$pendingPhda9[] = array(
					'chunkIdx' => $chunkIdx,
					'innerFull' => $innerFull,
					'plainBuf' => $articleBuf,
					'stackId' => $stackId,
				);
				$memberRelPaths[] = $shellRel;
				$memberRelPaths[] = $innerRel;
				if (is_array($chunkPrePageLens) && $chunkPrePageLens !== array()) {
					foreach ($chunkPrePageLens as $pl) {
						$sortedPageLens[] = (int) $pl;
					}
				} else {
					foreach ($chunk as $p) {
						$sortedPageLens[] = (int) $p['len'];
					}
				}
				$chunkIdx++;
				continue;
			}
			$shootTimeout = (int) (getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC') ?: 0);
			if ($shootTimeout <= 0) {
				$shootTimeout = (int) (getenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC') ?: 0);
			}
			$cr = fractal_zip_enwik_phda9_english_compress($articleBuf, array(
				'wire_wrap' => true,
				'timeout_sec' => $shootTimeout > 0 ? $shootTimeout : 0,
			));
			if (empty($cr['roundtrip_ok']) || !is_string($cr['payload']) || $cr['payload'] === '') {
				throw new RuntimeException('enwik text-inner phda9_article compress failed chunk ' . $chunkIdx);
			}
			$innerBlob = (string) $cr['payload'];
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] phda9_article chunk ' . $chunkIdx . ': ' . ($cr['tool'] ?? 'phda9')
					. ' ' . number_format(strlen($innerBlob)) . ' B plain='
					. number_format(strlen($articleBuf)) . ' shell='
					. number_format(strlen($shellBuf)) . ' sec=' . ($cr['seconds'] ?? 0) . "\n");
				@fflush(STDERR);
			}
			if ($stackId !== 'none') {
				$stackR = fractal_zip_text_stacked_outer_apply($stackId, $innerBlob);
				if (empty($stackR['roundtrip_ok'])) {
					throw new RuntimeException('enwik text-inner phda9_article stack roundtrip failed chunk ' . $chunkIdx);
				}
				$innerBlob = (string) $stackR['payload'];
			}
			if (file_put_contents($innerFull, $innerBlob) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: phda9_article write failed chunk ' . $chunkIdx);
			}
			$memberRelPaths[] = $shellRel;
			$memberRelPaths[] = $innerRel;
		} else {
			if ($stackId !== 'none') {
				$stackR = fractal_zip_text_stacked_outer_apply($stackId, $textBuf);
				if (empty($stackR['roundtrip_ok'])) {
					throw new RuntimeException('enwik text-inner stack roundtrip failed chunk ' . $chunkIdx);
				}
				$textBuf = (string) $stackR['payload'];
			}
			$textRel = sprintf('pages/chunk_%05d.text', $chunkIdx);
			$shellRel = sprintf('pages/chunk_%05d.shell.xml', $chunkIdx);
			$textFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $textRel);
			$shellFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $shellRel);
			if (file_put_contents($textFull, $textBuf) === false || file_put_contents($shellFull, $shellBuf) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: write failed chunk ' . $chunkIdx);
			}
			$memberRelPaths[] = $textRel;
			$memberRelPaths[] = $shellRel;
		}
		foreach ($chunk as $p) {
			$sortedPageLens[] = (int) $p['len'];
		}
		if ($memberFormat === 'phda9_xml' && is_array($chunkPrePageLens) && $chunkPrePageLens !== array()) {
			$sortedPageLens = array_merge(
				array_slice($sortedPageLens, 0, -count($chunkPrePageLens)),
				array_map('intval', $chunkPrePageLens)
			);
		}
		$chunkIdx++;
	}
	if ($pendingShootout !== array()) {
		$shootJobs = fractal_zip_enwik_member_shootout_jobs();
		if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
			@fwrite(STDERR, '[enwik] member shootout parallel: ' . count($pendingShootout)
				. ' chunks, jobs=' . $shootJobs . "\n");
			@fflush(STDERR);
		}
		fractal_zip_enwik_member_shootout_parallel_apply($pendingShootout, $shootJobs);
		$pendingShootout = array();
	}
	if ($pendingPhda9 !== array()) {
		$phda9Jobs = fractal_zip_enwik_phda9_english_jobs(count($pendingPhda9));
		if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
			@fwrite(STDERR, '[enwik] phda9 parallel encode: ' . count($pendingPhda9)
				. ' chunks, jobs=' . $phda9Jobs . "\n");
			@fflush(STDERR);
		}
		fractal_zip_enwik_phda9_english_parallel_apply($pendingPhda9, $phda9Jobs);
		$pendingPhda9 = array();
	}
	if ($pendingPhda9Sk !== array()) {
		$shootTimeoutSk = (int) (getenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC') ?: 0);
		if ($shootTimeoutSk <= 0) {
			$shootTimeoutSk = (int) (getenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC') ?: 0);
		}
		foreach ($pendingPhda9Sk as $skp) {
			if ((string) ($skp['skPlain'] ?? '') === '') {
				continue;
			}
			fractal_zip_enwik_consonant_sk_dual_write_phda9_member(
				$virtualDir,
				(int) ($skp['chunkIdx'] ?? 0),
				(string) $skp['skPlain'],
				$shootTimeoutSk > 0 ? $shootTimeoutSk : 0
			);
		}
		$pendingPhda9Sk = array();
	}
	$metaDir = $virtualDir . DIRECTORY_SEPARATOR . 'meta';
	if (!is_dir($metaDir) && !mkdir($metaDir, 0755, true) && !is_dir($metaDir)) {
		throw new RuntimeException('enwik text-inner virtual folder: cannot create meta dir');
	}
	$layoutRel = 'meta/text_inner_layout.json';
	$layoutFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $layoutRel);
	$layoutMetaOut = array_merge($layoutMeta, array('layout_id' => $layoutId));
	if ($skDualEnabled && $chunkSkLensAll !== array()) {
		$layoutMetaOut['sk_dual'] = true;
		$layoutMetaOut['sk_phda9_wire_escape'] = true;
		$layoutMetaOut['chunk_sk_lens'] = $chunkSkLensAll;
	}
	if ($layoutId === 'sort_title' && $chunkPermsAllIdentity) {
		$layoutMetaOut['implicit_alpha_sort'] = true;
	} else {
		$layoutMetaOut['chunk_perms'] = $chunkPerms;
	}
	$metaJson = json_encode($layoutMetaOut, JSON_UNESCAPED_UNICODE);
	if (!is_string($metaJson) || file_put_contents($layoutFull, $metaJson) === false) {
		throw new RuntimeException('enwik text-inner virtual folder: layout meta write failed');
	}
	$memberRelPaths[] = $layoutRel;
	$preprocessRel = 'meta/text_inner_preprocess.json';
	$preprocessFull = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $preprocessRel);
	$preMeta = array('preprocess' => $preprocessId, 'sidecars' => $allSidecars);
	$tokenVocabBlob = $GLOBALS['fractal_zip_general_text_token_vocab_blob'] ?? null;
	$vocabSource = $GLOBALS['fractal_zip_general_text_vocab_source'] ?? null;
	if (is_string($vocabSource) && $vocabSource !== '') {
		$preMeta['general_text_inner_codec'] = 'tokenized_zpaq';
		$preMeta['general_text_vocab_source'] = $vocabSource;
		if ($vocabSource === 'frozen' || str_starts_with($vocabSource, 'frozen')) {
			$preMeta['general_text_vocab_frozen'] = true;
		}
	}
	$vocabDelta = $GLOBALS['fractal_zip_general_text_vocab_delta'] ?? null;
	if (!empty($vocabDelta)) {
		$preMeta['general_text_vocab_delta'] = true;
	}
	if (is_string($tokenVocabBlob) && $tokenVocabBlob !== '') {
		$preMeta['general_text_inner_codec'] = 'tokenized_zpaq';
		$preMeta['general_text_token_vocab_b64'] = base64_encode($tokenVocabBlob);
	}
	$gtProfile = $GLOBALS['fractal_zip_general_text_profile'] ?? null;
	if (is_string($gtProfile) && $gtProfile !== '') {
		$preMeta['general_text_profile'] = $gtProfile;
		$preMeta['general_text_inner'] = true;
	}
	if (!empty($GLOBALS['fractal_zip_general_text_lossless_whole'])) {
		$preMeta['general_text_lossless_whole'] = true;
	}
	if ($wikiLomPhda9Escape) {
		$preMeta['wiki_lom_phda9_wire_escape'] = true;
	}
	if ($preprocessId === 'wiki_lom') {
		$preMeta['flags'] = fractal_zip_wiki_lom_layer_flags(
			$wikiLomPhda9Escape && fractal_zip_wiki_lom_phda9_wire_profile_wanted($memberFormat)
		);
	}
	if ($preprocessId === 'dict_nncp' && $sharedDictVocab !== array()) {
		$preMeta['vocab'] = $sharedDictVocab;
		$preMeta['frozen'] = true;
		$preMeta['codec'] = 'segment_v2';
	}
	if ($preprocessId === 'dict_inner' && $sharedDictVocab !== array()) {
		$preMeta['frozen'] = true;
		$preMeta['codec'] = 'segment_v2_inner';
		$foldStats = fractal_zip_enwik_text_inner_last_build_stats();
		if (($foldStats['fold_dict_bytes'] ?? 0) > 0) {
			$preMeta['fold_dict_bytes'] = (int) $foldStats['fold_dict_bytes'];
		}
	}
	if ($preprocessId === 'dict_phda9_inner' && $sharedDictVocab !== array()) {
		$preMeta['frozen'] = true;
		$preMeta['codec'] = 'segment_v2_phda9_inner';
		$preMeta['vocab_size'] = count($sharedDictVocab);
		$foldStats = fractal_zip_enwik_text_inner_last_build_stats();
		if (($foldStats['fold_dict_bytes'] ?? 0) > 0) {
			$preMeta['fold_dict_bytes'] = (int) $foldStats['fold_dict_bytes'];
		}
	}
	if ($preprocessId === 'stat_wrt' && isset($dictPreOpts['codes']) && is_array($dictPreOpts['codes'])) {
		$preMeta['codes'] = $dictPreOpts['codes'];
		$preMeta['frozen'] = true;
	}
	if ($preprocessId === 'stat_isp' && isset($dictPreOpts['vocab']) && is_array($dictPreOpts['vocab'])) {
		$preMeta['vocab'] = $dictPreOpts['vocab'];
		$preMeta['frozen'] = true;
		$preMeta['codec'] = 'words_id_varint_isp';
	}
	if ($preprocessId === 'stat_syllable_isp' && isset($dictPreOpts['vocab']) && is_array($dictPreOpts['vocab'])) {
		$preMeta['vocab'] = $dictPreOpts['vocab'];
		$preMeta['frozen'] = true;
		$preMeta['codec'] = 'syllables_id_varint_isp';
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
		$preMeta['payload_codec'] = fractal_zip_enwik_stat_syllable_isp_payload_codec($dictPreOpts);
	}
	if (($preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split'
			|| $preprocessId === 'consonant_hybrid_lossy')
		&& isset($dictPreOpts['consonant_model']) && is_array($dictPreOpts['consonant_model'])
		&& !fractal_zip_enwik_inner_fold_is_preprocess($preprocessId)) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
		$chCodec = $preprocessId === 'consonant_hybrid_split'
			? 'split_ascii'
			: fractal_zip_enwik_consonant_hybrid_payload_codec($dictPreOpts);
		$chMeta = fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($dictPreOpts['consonant_model']);
		$preMeta = array_merge($preMeta, $chMeta);
		$preMeta['payload_codec'] = $chCodec;
		$preMeta['frozen'] = true;
		$preMeta['codec'] = $preprocessId === 'consonant_hybrid_split'
			? 'consonant_hybrid_split'
			: 'consonant_hybrid_isp';
		if ($preprocessId === 'consonant_hybrid_split') {
			$preMeta['sk_inner_preprocess'] = fractal_zip_enwik_consonant_sk_inner_preprocess_id();
		}
	}
	if (fractal_zip_enwik_stat_pred_preprocess_family($preprocessId) && $preprocessId !== 'stat_pred_inner'
		&& isset($dictPreOpts['stat_model']) && is_array($dictPreOpts['stat_model'])) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
		$sm = $dictPreOpts['stat_model'];
		$preMeta['vocab'] = $sm['vocab'] ?? array();
		$preMeta['bigram_succ_ids'] = fractal_zip_enwik_stat_pred_compact_bigram_ids(
			is_array($sm['bigram_succ'] ?? null) ? $sm['bigram_succ'] : array(),
			is_array($sm['vocab_index'] ?? null) ? $sm['vocab_index'] : array()
		);
		$preMeta['frozen'] = true;
		$preMeta['codec'] = 'stat_pred_bigram_isp';
	}
	if ($preprocessId === 'qg_hybrid_root' && isset($dictPreOpts['qg_model']) && is_array($dictPreOpts['qg_model'])) {
		$preMeta['word_vocab'] = $dictPreOpts['qg_model']['word']['vocab'] ?? array();
		$preMeta['sub_vocab'] = $dictPreOpts['qg_model']['sub']['vocab'] ?? array();
		$preMeta['frozen'] = true;
		$preMeta['codec'] = 'qg_hybrid_root';
	}
	if (($preprocessId === 'qg_subword_root' || $preprocessId === 'qg_word_root')
		&& isset($dictPreOpts['qg_model']) && is_array($dictPreOpts['qg_model'])) {
		$preMeta['vocab'] = $dictPreOpts['qg_model']['vocab'] ?? array();
		$preMeta['frozen'] = true;
		$preMeta['codec'] = $preprocessId;
	}
	$preprocessMetaBytes = 0;
	$preprocessMetaWireBytes = 0;
	if ($preprocessId === 'stat_pred_compact_meta' && isset($preMeta['vocab'])) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
		$preMeta['preprocess'] = 'stat_pred_compact_meta';
		$bin = fractal_zip_enwik_stat_pred_serialize_compact_meta($preMeta);
		$gzPath = $virtualDir . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_inner_preprocess.bin.gz';
		$gz = gzencode($bin, 9);
		if (!is_string($gz) || file_put_contents($gzPath, $gz) === false) {
			throw new RuntimeException('enwik text-inner virtual folder: preprocess compact meta gzip write failed');
		}
		$preprocessMetaBytes = strlen($bin);
		$preprocessMetaWireBytes = strlen($gz);
		$memberRelPaths[] = 'meta/text_inner_preprocess.bin.gz';
	} elseif (in_array($preprocessId, array('cycle_inner', 'cycle_delta'), true)) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';
		$transform = $preprocessId === 'cycle_delta' ? 'delta' : 'none';
		$bin = fractal_zip_text_cycle_sidecar_pack($transform);
		$binPath = $virtualDir . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_inner_preprocess.bin';
		if (file_put_contents($binPath, $bin) === false) {
			throw new RuntimeException('enwik text-inner virtual folder: cycle preprocess meta write failed');
		}
		$preprocessMetaBytes = strlen($bin);
		$preprocessMetaWireBytes = strlen($bin);
		$memberRelPaths[] = 'meta/text_inner_preprocess.bin';
	} elseif ($preprocessId === 'stat_pred_inner' && $statPredMemberFold) {
		$sidecarOnly = array(
			'preprocess' => 'stat_pred_inner',
			'sidecars' => $allSidecars,
			'embedded' => true,
			'frozen' => true,
			'codec' => 'stat_pred_bigram_isp',
			'member_fold_fzpm_bytes' => strlen($statPredMemberBlob),
		);
		$preJson = json_encode($sidecarOnly, JSON_UNESCAPED_UNICODE);
		if (!is_string($preJson)) {
			throw new RuntimeException('enwik text-inner virtual folder: stat_pred member-fold meta json_encode failed');
		}
		if (strlen($preJson) > 262144) {
			$gzPath = $virtualDir . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_inner_preprocess.json.gz';
			$gz = gzencode($preJson, 9);
			if (!is_string($gz) || file_put_contents($gzPath, $gz) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: stat_pred member-fold gzip write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($gz);
			$memberRelPaths[] = 'meta/text_inner_preprocess.json.gz';
		} else {
			if (file_put_contents($preprocessFull, $preJson) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: stat_pred member-fold meta write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($preJson);
			$memberRelPaths[] = $preprocessRel;
		}
	} elseif ($preprocessId === 'stat_pred_inner') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
		$sm = is_array($dictPreOpts['stat_model'] ?? null) ? $dictPreOpts['stat_model'] : array();
		$smTrailer = fractal_zip_enwik_stat_pred_trailer_model(
			$sm,
			array_keys($statPredBigramPrevUsed),
			fractal_zip_enwik_stat_pred_inner_bigram_max_prev(),
			$statPredBigramRanksUsed
		);
		$statPredInnerBlob = fractal_zip_enwik_stat_pred_serialize_compact_meta(array(
			'preprocess' => 'stat_pred_inner',
			'vocab' => $smTrailer['vocab'] ?? array(),
			'bigram_succ_ids' => fractal_zip_enwik_stat_pred_compact_bigram_ids(
				is_array($smTrailer['bigram_succ'] ?? null) ? $smTrailer['bigram_succ'] : array(),
				is_array($smTrailer['vocab_index'] ?? null) ? $smTrailer['vocab_index'] : array()
			),
			'bigram_ranks_used' => $statPredBigramRanksUsed,
			'sidecars' => $allSidecars,
			'frozen' => true,
			'include_sidecars' => true,
			'sidecars_binary' => true,
			'bigram_uint16' => true,
		));
		$preprocessMetaBytes = strlen($statPredInnerBlob);
		$preprocessMetaWireBytes = 0;
	} elseif ($innerFoldToTrailer && ($preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split'
			|| $preprocessId === 'consonant_hybrid_lossy')) {
		$sidecarOnly = array(
			'preprocess' => $preprocessId,
			'sidecars' => $allSidecars,
			'embedded' => true,
			'frozen' => true,
			'codec' => $preprocessId === 'consonant_hybrid_split'
				? 'consonant_hybrid_split'
				: ($preprocessId === 'consonant_hybrid_lossy' ? 'consonant_hybrid_lossy' : 'consonant_hybrid_isp'),
			'payload_codec' => $preprocessId === 'consonant_hybrid_split'
				? 'split_ascii'
				: ($preprocessId === 'consonant_hybrid_lossy' ? 'lossy_ascii' : fractal_zip_enwik_consonant_hybrid_payload_codec($dictPreOpts)),
		);
		$preJson = json_encode($sidecarOnly, JSON_UNESCAPED_UNICODE);
		if (!is_string($preJson)) {
			throw new RuntimeException('enwik text-inner virtual folder: consonant_hybrid sidecar meta json_encode failed');
		}
		if (strlen($preJson) > 262144) {
			$gzPath = $virtualDir . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_inner_preprocess.json.gz';
			$gz = gzencode($preJson, 9);
			if (!is_string($gz) || file_put_contents($gzPath, $gz) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: consonant_hybrid sidecar gzip write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($gz);
			$memberRelPaths[] = 'meta/text_inner_preprocess.json.gz';
		} else {
			if (file_put_contents($preprocessFull, $preJson) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: consonant_hybrid sidecar meta write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($preJson);
			$memberRelPaths[] = $preprocessRel;
		}
	} elseif ($innerFoldToTrailer && in_array($preprocessId, array('dict_inner', 'dict_phda9_inner'), true)) {
		$sidecarOnly = array(
			'preprocess' => $preprocessId,
			'sidecars' => $allSidecars,
			'embedded' => true,
			'frozen' => true,
			'codec' => $preprocessId === 'dict_phda9_inner' ? 'segment_v2_phda9_inner' : 'segment_v2_inner',
			'vocab_size' => count($sharedDictVocab),
		);
		$preJson = json_encode($sidecarOnly, JSON_UNESCAPED_UNICODE);
		if (!is_string($preJson)) {
			throw new RuntimeException('enwik text-inner virtual folder: dict inner sidecar meta json_encode failed');
		}
		if (strlen($preJson) > 262144) {
			$gzPath = $virtualDir . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_inner_preprocess.json.gz';
			$gz = gzencode($preJson, 9);
			if (!is_string($gz) || file_put_contents($gzPath, $gz) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: dict inner sidecar gzip write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($gz);
			$memberRelPaths[] = 'meta/text_inner_preprocess.json.gz';
		} else {
			if (file_put_contents($preprocessFull, $preJson) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: dict inner sidecar meta write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($preJson);
			$memberRelPaths[] = $preprocessRel;
		}
	} else {
		$preJson = json_encode($preMeta, JSON_UNESCAPED_UNICODE);
		if (!is_string($preJson)) {
			throw new RuntimeException('enwik text-inner virtual folder: preprocess meta json_encode failed');
		}
		if (strlen($preJson) > 262144) {
			$gzPath = $virtualDir . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_inner_preprocess.json.gz';
			$gz = gzencode($preJson, 9);
			if (!is_string($gz) || file_put_contents($gzPath, $gz) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: preprocess meta gzip write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($gz);
			$memberRelPaths[] = 'meta/text_inner_preprocess.json.gz';
		} else {
			if (file_put_contents($preprocessFull, $preJson) === false) {
				throw new RuntimeException('enwik text-inner virtual folder: preprocess meta write failed');
			}
			$preprocessMetaBytes = strlen($preJson);
			$preprocessMetaWireBytes = strlen($preJson);
			$memberRelPaths[] = $preprocessRel;
		}
	}
	if (fractal_zip_enwik_stat_pred_preprocess_family($preprocessId)) {
		$innerMetaBytes = $preprocessId === 'stat_pred_inner' ? strlen($statPredInnerBlob) : 0;
		$trailerRowCount = 0;
		if ($preprocessId === 'stat_pred_inner' && $statPredInnerBlob !== '') {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			$trailerDec = fractal_zip_enwik_stat_pred_deserialize_compact_meta($statPredInnerBlob);
			$trailerRowCount = count($trailerDec['bigram_succ_ids'] ?? array());
		}
		$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array_merge(
			is_array($GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] ?? null)
				? $GLOBALS['fractal_zip_enwik_text_inner_last_build_stats']
				: array(),
			array(
				'preprocess' => $preprocessId,
				'meta_bytes' => $preprocessMetaWireBytes,
				'meta_json_bytes' => $preprocessMetaWireBytes,
				'meta_uncompressed_bytes' => $preprocessMetaBytes,
				'stat_pred_inner_bytes' => $innerMetaBytes,
				'fold_stat_bytes' => $innerMetaBytes,
				'bigram_hits' => $statPredBigramHits,
				'literal_words' => $statPredLiteralWords,
				'vocab_size' => count($sharedDictVocab),
				'trailer_bigram_rows' => $trailerRowCount,
				'emitted_prev_count' => count($statPredBigramPrevUsed),
			)
		);
	} elseif (($preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split'
			|| $preprocessId === 'consonant_hybrid_lossy')
		&& $preprocessMetaWireBytes > 0) {
		$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array_merge(
			is_array($GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] ?? null)
				? $GLOBALS['fractal_zip_enwik_text_inner_last_build_stats']
				: array(),
			array(
				'preprocess' => $preprocessId,
				'meta_bytes' => $preprocessMetaWireBytes,
				'meta_json_bytes' => $preprocessMetaWireBytes,
				'meta_uncompressed_bytes' => $preprocessMetaBytes,
			)
		);
	} elseif ($preprocessId === 'stat_syllable_isp' && $preprocessMetaWireBytes > 0) {
		$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array_merge(
			is_array($GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] ?? null)
				? $GLOBALS['fractal_zip_enwik_text_inner_last_build_stats']
				: array(),
			array(
				'preprocess' => $preprocessId,
				'meta_bytes' => $preprocessMetaWireBytes,
				'meta_json_bytes' => $preprocessMetaWireBytes,
				'meta_uncompressed_bytes' => $preprocessMetaBytes,
			)
		);
	}
	if ($innerFoldToTrailer) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		$foldPayload = '';
		if ($preprocessId === 'stat_pred_inner' && $statPredInnerBlob !== '') {
			$foldPayload = $statPredInnerBlob;
		} elseif (($preprocessId === 'consonant_hybrid' || $preprocessId === 'consonant_hybrid_split'
				|| $preprocessId === 'consonant_hybrid_lossy')
			&& isset($dictPreOpts['consonant_model']) && is_array($dictPreOpts['consonant_model'])) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
			$foldCodec = $preprocessId === 'consonant_hybrid_split'
				? 'split_ascii'
				: ($preprocessId === 'consonant_hybrid_lossy'
					? 'lossy_ascii'
					: fractal_zip_enwik_consonant_hybrid_payload_codec($dictPreOpts));
			$foldPayload = fractal_zip_enwik_consonant_hybrid_inner_fold_payload(
				$dictPreOpts['consonant_model'],
				$foldCodec
			);
		} elseif (($preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner') && $sharedDictVocab !== array()) {
			$foldPayload = fractal_zip_enwik_inner_fold_encode_vocab($sharedDictVocab);
		} elseif ($preprocessId === 'wiki_lom' && $dictPreOpts !== array()) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';
			$wlFlags = array(
				'link_ids' => !empty($dictPreOpts['link_ids']),
				'url_dict' => !empty($dictPreOpts['url_dict']),
				'tag_ids' => !empty($dictPreOpts['tag_ids']),
				'abbrevs' => !empty($dictPreOpts['abbrevs']),
				'acronyms' => !empty($dictPreOpts['acronyms']),
				'templates' => !empty($dictPreOpts['templates']),
			);
			if (fractal_zip_wiki_lom_tables_needed($wlFlags)) {
				$foldTables = array(
					'title_to_id' => $dictPreOpts['title_to_id'] ?? array(),
					'urls' => $dictPreOpts['urls'] ?? array(),
					'tags' => $dictPreOpts['tags'] ?? array(),
					'abbrevs_list' => $dictPreOpts['abbrevs_list'] ?? array(),
					'acronyms_list' => fractal_zip_cfabb_no_fold_enabled()
						? array()
						: ($dictPreOpts['acronyms_list'] ?? array()),
					'templates_list' => $dictPreOpts['templates_list'] ?? array(),
				);
				$foldPayload = fractal_zip_wiki_lom_inner_fold_blob($foldTables);
			}
			if (!empty($dictPreOpts['acronyms_list']) && is_array($dictPreOpts['acronyms_list'])) {
				$cfabbFoldMeta = fractal_zip_cfabb_no_fold_enabled()
					? 0
					: fractal_zip_cfabb_fold_meta_bytes($dictPreOpts['acronyms_list']);
				$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array_merge(
					fractal_zip_enwik_text_inner_last_build_stats(),
					array(
						'cfabb_inline' => fractal_zip_cfabb_inline_enabled(),
						'cfabb_inline_entries' => count($dictPreOpts['acronyms_list']),
						'cfabb_fold_meta_bytes' => $cfabbFoldMeta,
					)
				);
			}
		}
		if ($foldPayload !== '') {
			$sealed = fractal_zip_enwik_inner_fold_seal_trailer($preprocessId, $foldPayload);
			$innerFoldBlob = (string) ($sealed['wire'] ?? '');
			if ($innerFoldBlob === '') {
				throw new RuntimeException('enwik text-inner: inner fold trailer seal failed');
			}
			$foldWire = strlen($innerFoldBlob);
			$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array_merge(
				fractal_zip_enwik_text_inner_last_build_stats(),
				array(
					'inner_fold_trailer_bytes' => $foldWire,
					'inner_fold_trailer_codec' => (string) ($sealed['codec'] ?? ''),
					'inner_fold_packed_bytes' => (int) ($sealed['packed_bytes'] ?? 0),
					'meta_bytes' => $foldWire,
					'fold_dict_bytes' => ($preprocessId === 'dict_inner' || $preprocessId === 'dict_phda9_inner')
						? strlen($foldPayload) : ($GLOBALS['fractal_zip_enwik_text_inner_last_build_stats']['fold_dict_bytes'] ?? 0),
					'fold_stat_bytes' => $preprocessId === 'stat_pred_inner' ? strlen($foldPayload) : 0,
					'fold_sk_bytes' => in_array($preprocessId, array('consonant_hybrid', 'consonant_hybrid_split', 'consonant_hybrid_lossy'), true)
						? strlen($foldPayload) : 0,
				)
			);
		}
	}
	if ($innerFoldBlob === '' && fractal_zip_phda9_dict_fold_enabled()) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		$dictFold = fractal_zip_phda9_dict_build_fold_payload();
		if ($dictFold !== null && ($dictFold['payload'] ?? '') !== '') {
			$sealed = fractal_zip_enwik_inner_fold_seal_trailer('phda9_dict_fold', (string) $dictFold['payload']);
			$innerFoldBlob = (string) ($sealed['wire'] ?? '');
			if ($innerFoldBlob === '') {
				throw new RuntimeException('enwik text-inner: phda9_dict_fold trailer seal failed');
			}
			$foldWire = strlen($innerFoldBlob);
			$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array_merge(
				fractal_zip_enwik_text_inner_last_build_stats(),
				array(
					'inner_fold_trailer_bytes' => $foldWire,
					'inner_fold_trailer_codec' => (string) ($sealed['codec'] ?? ''),
					'inner_fold_packed_bytes' => (int) ($sealed['packed_bytes'] ?? 0),
					'meta_bytes' => $foldWire,
					'fold_dict_bytes' => strlen((string) $dictFold['payload']),
					'phda9_dict_fold_words' => (int) ($dictFold['words'] ?? 0),
					'phda9_dict_fold_raw_bytes' => (int) ($dictFold['raw_dict_bytes'] ?? 0),
				)
			);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] phda9_dict_fold: words=' . (int) ($dictFold['words'] ?? 0)
					. ' raw=' . number_format((int) ($dictFold['raw_dict_bytes'] ?? 0))
					. ' packed=' . number_format((int) ($sealed['packed_bytes'] ?? 0))
					. ' wire=' . number_format($foldWire)
					. ' codec=' . (string) ($sealed['codec'] ?? '?') . "\n");
			}
		}
	}
	$statSidecarBlob = '';
	if (fractal_zip_enwik_stat_sidecar_enabled() && $wireTextForSidecar !== array()) {
		$statSidecarBlob = fractal_zip_enwik_build_stat_sidecar_blob($wireTextForSidecar);
	}
	return array(
		'virtualDir' => $virtualDir,
		'memberRelPaths' => $memberRelPaths,
		'statSidecarBlob' => $statSidecarBlob,
		'innerFoldBlob' => $innerFoldBlob,
		'origIndexBySorted' => $origIndexBySorted,
		'header' => $header,
		'footer' => $footer,
		'outputFile' => $outputFile,
		'outputDir' => $outputDir,
		'pagesPerMember' => $pagesPerMember,
		'sortedPageLens' => $sortedPageLens,
		'textInnerLayout' => $layoutId,
		'textInnerPreprocess' => $preprocessId,
		'textInnerPreprocessRel' => $preprocessRel,
		'textInnerStack' => $stackId,
		'textInnerMemberFormat' => $memberFormat,
		'textInnerMono' => $monoInner,
		'textInnerDualMembers' => true,
		'sortedTextLens' => $sortedTextLens,
	);
}

function fractal_zip_enwik_try_prepare_virtual_folder(string $dir): ?array
{
	if (!fractal_zip_enwik_entry_sort_enabled()) {
		return null;
	}
	// World-record / enwik paths must not inherit general-text tokenized_zpaq fast inner.
	putenv('FRACTAL_ZIP_PHDA9_GENERAL_FAST=0');
	$iterRoot = realpath($dir);
	if ($iterRoot === false || !is_dir($iterRoot)) {
		return null;
	}
	$files = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($iterRoot, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$rp = $fi->getRealPath();
		$path = $rp !== false ? $rp : $fi->getPathname();
		$base = basename($path);
		if ($base === '' || str_contains($base, '.')) {
			return null;
		}
		$files[] = $path;
	}
	if (count($files) !== 1) {
		return null;
	}
	$sourcePath = $files[0];
	$blob = file_get_contents($sourcePath);
	if (!is_string($blob) || $blob === '') {
		return null;
	}
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		return null;
	}
	$header = (string) $split['header'];
	$footer = (string) $split['footer'];
	$semanticPackDict = '';
	if (fractal_zip_enwik_siteinfo_pack_enabled()) {
		$hp = fractal_zip_enwik_boilerplate_pack_apply($header);
		$header = (string) $hp['blob'];
		$semanticPackDict = (string) $hp['dict'];
		$fp = fractal_zip_enwik_boilerplate_pack_apply($footer);
		$footer = (string) $fp['blob'];
		$semanticPackDict = fractal_zip_enwik_phrase_pack_chain_dicts($semanticPackDict, (string) $fp['dict']);
		if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
			@fwrite(STDERR, '[enwik] siteinfo pack: header=' . strlen($header) . ' footer=' . strlen($footer) . " B\n");
		}
	}
	if (fractal_zip_enwik_boilerplate_pack_enabled()) {
		$packed = fractal_zip_enwik_boilerplate_pack_apply($blob);
		$blob = (string) $packed['blob'];
		$semanticPackDict = fractal_zip_enwik_phrase_pack_chain_dicts($semanticPackDict, (string) $packed['dict']);
		$split = enwik_split_page_refs($blob);
		if ($split === null) {
			return null;
		}
		$header = (string) $split['header'];
		$footer = (string) $split['footer'];
	}
	if (fractal_zip_enwik_corpus_phrases_enabled()) {
		$minCount = max(8, (int) (getenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_COUNT') ?: 32));
		$maxEntries = max(16, min(512, (int) (getenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX') ?: 192)));
		$minLen = max(8, (int) (getenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MIN_LEN') ?: 10));
		$maxLen = max($minLen, min(160, (int) (getenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASE_MAX_LEN') ?: 96)));
		if (fractal_zip_enwik_corpus_phrases_full_blob_enabled()) {
			$phrases = fractal_zip_enwik_mine_corpus_phrases($blob, $minLen, $maxLen, $minCount, $maxEntries);
			$packed = $phrases !== array() ? fractal_zip_enwik_phrase_pack_apply($blob, $phrases) : array('blob' => $blob, 'dict' => '');
		} else {
			$phrases = fractal_zip_enwik_mine_corpus_phrases_from_text_bodies($blob, $minLen, $maxLen, $minCount, $maxEntries);
			$packed = $phrases !== array() ? fractal_zip_enwik_phrase_pack_in_text_regions_whole_blob($blob, $phrases) : array('blob' => $blob, 'dict' => '');
		}
		if ($phrases !== array()) {
			$blob = (string) $packed['blob'];
			$semanticPackDict = fractal_zip_enwik_phrase_pack_chain_dicts($semanticPackDict, (string) $packed['dict']);
			$split = enwik_split_page_refs($blob);
			if ($split === null) {
				return null;
			}
			$header = (string) $split['header'];
			$footer = (string) $split['footer'];
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				$nPat = count(fractal_zip_enwik_phrase_pack_parse_dict((string) $packed['dict']));
				@fwrite(STDERR, '[enwik] corpus phrases: ' . count($phrases) . ' mined, ' . $nPat . " packed (text regions)\n");
			}
		}
	}
	if (fractal_zip_enwik_word_pack_enabled()) {
		$minW = max(4, (int) (getenv('FRACTAL_ZIP_ENWIK_WORD_MIN_COUNT') ?: 800));
		$maxW = max(32, min(384, (int) (getenv('FRACTAL_ZIP_ENWIK_WORD_MAX') ?: 128)));
		$words = fractal_zip_enwik_mine_article_word_phrases($blob, $minW, $maxW);
		if ($words !== array()) {
			$packed = fractal_zip_enwik_phrase_pack_apply($blob, $words);
			$blob = (string) $packed['blob'];
			$semanticPackDict = fractal_zip_enwik_phrase_pack_chain_dicts($semanticPackDict, (string) $packed['dict']);
			$split = enwik_split_page_refs($blob);
			if ($split === null) {
				return null;
			}
			$header = (string) $split['header'];
			$footer = (string) $split['footer'];
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] word pack: ' . count($words) . " tokens\n");
			}
		}
	}
	if (fractal_zip_enwik_semantic_pack_enabled()) {
		$packed = fractal_zip_enwik_semantic_pack_apply($blob);
		$blob = (string) $packed['blob'];
		$semanticPackDict = fractal_zip_enwik_phrase_pack_chain_dicts($semanticPackDict, (string) $packed['dict']);
		$split = enwik_split_page_refs($blob);
		if ($split === null) {
			return null;
		}
		$header = (string) $split['header'];
		$footer = (string) $split['footer'];
	}
	$textPackPhrases = array();
	if (fractal_zip_enwik_text_pack_enabled()) {
		$textPackPhrases = fractal_zip_enwik_build_text_pack_phrases($blob);
		if ($textPackPhrases !== array()) {
			$semanticPackDict = fractal_zip_enwik_phrase_pack_chain_dicts(
				$semanticPackDict,
				fractal_zip_enwik_phrase_pack_dict_from_phrases($textPackPhrases)
			);
			if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
				@fwrite(STDERR, '[enwik] text pack phrases: ' . count($textPackPhrases) . "\n");
			}
		}
	}
	$sorted = enwik_sort_page_refs_by_title($split['pages']);
	$outputFile = basename($sourcePath);
	$virtualDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzenwik_' . substr(md5($iterRoot . "\0" . $outputFile . "\0" . (string) microtime(true)), 0, 16);
	$layoutId = trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT') ?: 'sort_title'));
	if (fractal_zip_enwik_text_inner_enabled()) {
		$ctx = enwik_build_text_inner_virtual_folder_from_refs(
			$virtualDir,
			$sorted,
			$blob,
			$header,
			$footer,
			$outputFile,
			$iterRoot,
			$layoutId
		);
		if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
			$pre = (string) ($ctx['textInnerPreprocess'] ?? 'none');
			$stk = (string) ($ctx['textInnerStack'] ?? 'none');
			$fmt = (string) ($ctx['textInnerMemberFormat'] ?? 'dual');
			$mono = !empty($ctx['textInnerMono']) ? '1' : '0';
			@fwrite(STDERR, '[enwik] text-inner members: format=' . $fmt . ' mono=' . $mono . ' layout=' . $layoutId
				. ' preprocess=' . $pre . ' stack=' . $stk . ' members=' . count($ctx['memberRelPaths']) . "\n");
		}
	} else {
		$ctx = enwik_build_virtual_folder_from_refs(
			$virtualDir,
			$sorted,
			$blob,
			$header,
			$footer,
			$outputFile,
			$iterRoot,
			$textPackPhrases,
			0
		);
	}
	$ctx['sourcePath'] = $sourcePath;
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
	$regionEntries = $ctx['textCodecRegionEntries'] ?? array();
	if (is_array($regionEntries) && $regionEntries !== array() && PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[enwik] text codec regions: ' . count($regionEntries) . " (~EZT)\n");
	}
	$dictPatterns = $ctx['textCodecDictPatterns'] ?? array();
	if (is_array($dictPatterns) && $dictPatterns !== array()) {
		$codecDict = fractal_zip_enwik_phrase_pack_dict_from_phrases($dictPatterns);
		$semanticPackDict = fractal_zip_enwik_phrase_pack_chain_dicts($semanticPackDict, $codecDict);
	}
	if ($semanticPackDict !== '') {
		$ctx['semanticPackDict'] = $semanticPackDict;
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[enwik] entry sort: ' . count($sorted) . ' pages → ' . count($ctx['memberRelPaths']) . " virtual members (pages/member={$ctx['pagesPerMember']})\n");
	}
	return $ctx;
}

/**
 * @param array<string, mixed> $ctx
 */
function fractal_zip_enwik_append_fzep_trailer(string $fzcPath, array $ctx): void
{
	if (!is_file($fzcPath)) {
		return;
	}
	$ctx = fractal_zip_enwik_refresh_sorted_page_lens_from_virtual($ctx);
	$trailer = enwik_pack_permutation_meta($ctx);
	$existing = file_get_contents($fzcPath);
	if (!is_string($existing)) {
		return;
	}
	if (strrpos($existing, FRACTAL_ZIP_ENWIK_MAGIC) !== false) {
		return;
	}
	file_put_contents($fzcPath, $existing . $trailer);
}

/**
 * @param array<string, mixed> $ctx
 */
function fractal_zip_enwik_cleanup_virtual_folder(?array $ctx): void
{
	if (!is_array($ctx)) {
		return;
	}
	$vd = (string) ($ctx['virtualDir'] ?? '');
	if ($vd !== '' && is_dir($vd)) {
		fractal_zip_enwik_recursive_remove($vd);
	}
}

function fractal_zip_enwik_try_reassemble_after_extract(string $fzcPath, string $extractRoot): bool
{
	if (getenv('FRACTAL_ZIP_ENWIK_SKIP_REASSEMBLE') === '1') {
		return false;
	}
	$meta = fractal_zip_enwik_peel_trailer_from_fzc($fzcPath);
	if ($meta === null) {
		return false;
	}
	$outputFile = (string) $meta['outputFile'];
	$fzepFlags = (int) ($meta['fzepFlags'] ?? 0);
	$outPath = rtrim($extractRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $outputFile;
	if (($fzepFlags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_RAW_PAQ) !== 0 && is_file($outPath)) {
		$restored = (string) file_get_contents($outPath);
	} else {
		$restored = enwik_restore_blob(
			(string) $meta['header'],
			(string) $meta['footer'],
			(array) $meta['memberRelPaths'],
			(array) $meta['origIndexBySorted'],
			(int) $meta['pagesPerMember'],
			$extractRoot,
			(array) ($meta['sortedPageLens'] ?? array()),
			(array) ($meta['sortedTextLens'] ?? array()),
			(string) ($meta['innerFoldBlob'] ?? '')
		);
	}
	$semanticDict = (string) ($meta['semanticPackDict'] ?? '');
	if (($fzepFlags & FRACTAL_ZIP_ENWIK_FZEP_FLAG_TEXT_CODEC) !== 0) {
		$bulkPath = rtrim($extractRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'meta'
			. DIRECTORY_SEPARATOR . 'text_codec.eztb';
		if (is_file($bulkPath)) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
			$sharedVocab = $semanticDict !== ''
				? fractal_zip_enwik_text_codec_vocab_from_dict_patterns(
					fractal_zip_enwik_phrase_pack_parse_dict($semanticDict)
				)
				: null;
			$bulk = (string) file_get_contents($bulkPath);
			$restored = fractal_zip_enwik_text_codec_restore_eztokens_in_blob($restored, $bulk, $sharedVocab);
		}
	}
	if ($semanticDict !== '') {
		$restored = fractal_zip_enwik_semantic_pack_restore($restored, $semanticDict);
	}
	$preMeta = fractal_zip_enwik_load_text_inner_preprocess_meta($extractRoot);
	if (is_array($preMeta)) {
		$gtProfile = (string) ($preMeta['general_text_profile'] ?? '');
		$gtCodec = (string) ($preMeta['general_text_inner_codec'] ?? '');
		if ($gtProfile !== '' || $gtCodec !== '') {
			if ($gtProfile === '') {
				$gtProfile = 'text_plain';
			}
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_general_text.php';
			$restored = fractal_zip_general_text_restore_from_synthetic_blob($restored, $gtProfile, $preMeta);
		}
	}
	if (strpos($restored, '@w{') !== false && function_exists('fractal_zip_web_ref_expand_fractal_string')) {
		$restored = fractal_zip_web_ref_expand_fractal_string($restored);
	}
	if (file_put_contents($outPath, $restored) === false) {
		throw new RuntimeException('enwik restore: cannot write ' . $outPath);
	}
	foreach ($meta['memberRelPaths'] as $rel) {
		$rel = (string) $rel;
		if (!fractal_zip_enwik_is_fzep_scaffolding_rel($rel)) {
			continue;
		}
		$full = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if (is_file($full)) {
			@unlink($full);
		}
	}
	$pagesDir = $extractRoot . DIRECTORY_SEPARATOR . 'pages';
	if (is_dir($pagesDir)) {
		fractal_zip_enwik_recursive_remove($pagesDir);
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[enwik] restored ' . $outputFile . ' (' . strlen($restored) . " bytes)\n");
	}
	return true;
}

/**
 * @param list<string> $phrases
 */
function fractal_zip_enwik_phrase_pack_dict_from_phrases(array $phrases): string
{
	if ($phrases === array()) {
		return '';
	}
	$dictParts = array(fractal_zip_enwik_encode_varint_u32(count($phrases)));
	foreach ($phrases as $pat) {
		$dictParts[] = fractal_zip_enwik_encode_varint_u32(strlen($pat)) . $pat;
	}
	return implode('', $dictParts);
}

/**
 * @param list<string> $phrases
 */
function fractal_zip_enwik_build_text_pack_phrases(string $blob): array
{
	$minCount = max(50, (int) (getenv('FRACTAL_ZIP_ENWIK_TEXT_MIN_COUNT') ?: 500));
	$maxEntries = max(16, min(128, (int) (getenv('FRACTAL_ZIP_ENWIK_TEXT_MAX') ?: 96)));
	$mined = fractal_zip_enwik_mine_article_word_phrases($blob, $minCount, $maxEntries);
	$seen = array();
	$out = array();
	foreach (array_merge(fractal_zip_enwik_wiki_word_phrases(), $mined) as $p) {
		if (isset($seen[$p])) {
			continue;
		}
		$seen[$p] = true;
		$out[] = $p;
		if (count($out) >= $maxEntries) {
			break;
		}
	}
	return $out;
}

/**
 * @param list<string> $phrases
 */
function fractal_zip_enwik_pack_text_regions_in_page(string $pageXml, array $phrases): string
{
	if ($phrases === array() || $pageXml === '') {
		return $pageXml;
	}
	$marker = '<text xml:space="preserve">';
	$openLen = strlen($marker);
	$out = '';
	$pos = 0;
	$n = strlen($pageXml);
	while ($pos < $n) {
		$i = strpos($pageXml, $marker, $pos);
		if ($i === false) {
			$out .= substr($pageXml, $pos);
			break;
		}
		$out .= substr($pageXml, $pos, $i - $pos + $openLen);
		$pos = $i + $openLen;
		$close = strpos($pageXml, '</text>', $pos);
		if ($close === false) {
			$out .= substr($pageXml, $pos);
			break;
		}
		$inner = substr($pageXml, $pos, $close - $pos);
		$packed = fractal_zip_enwik_phrase_pack_apply($inner, $phrases);
		$out .= (string) $packed['blob'];
		$pos = $close;
	}
	return $out;
}

/**
 * @param list<string> $phrases longest-first replacement
 * @return array{blob: string, dict: string}
 */
/** LOM {@see O::validate_syntax()} requires global {@code <} and {@code >} counts to stay equal after pack. */
function fractal_zip_enwik_phrase_pack_lom_bracket_balanced(string $pat): bool
{
	return substr_count($pat, '<') === substr_count($pat, '>');
}

function fractal_zip_enwik_phrase_pack_apply(string $blob, array $phrases, ?array &$sharedDictPatterns = null): array
{
	if ($phrases === array()) {
		return array('blob' => $blob, 'dict' => '');
	}
	usort(
		$phrases,
		static function (string $a, string $b): int {
			return strlen($b) <=> strlen($a);
		}
	);
	$packed = $blob;
	$dictPatterns = array();
	$useShared = $sharedDictPatterns !== null;
	foreach ($phrases as $pat) {
		if ($pat === '' || strlen($pat) < 8 || strpos($packed, $pat) === false) {
			continue;
		}
		if (!fractal_zip_enwik_phrase_pack_lom_bracket_balanced($pat)) {
			continue;
		}
		if ($useShared) {
			$idx = array_search($pat, $sharedDictPatterns, true);
			if ($idx === false) {
				$idx = count($sharedDictPatterns);
				$tok = fractal_zip_enwik_phrase_pack_token($idx);
				if (strlen($tok) >= strlen($pat)) {
					continue;
				}
				$sharedDictPatterns[] = $pat;
			} else {
				$tok = fractal_zip_enwik_phrase_pack_token((int) $idx);
			}
		} else {
			$tok = fractal_zip_enwik_phrase_pack_token(count($dictPatterns));
			if (strlen($tok) >= strlen($pat)) {
				continue;
			}
			$dictPatterns[] = $pat;
		}
		$packed = str_replace($pat, $tok, $packed);
	}
	if ($useShared) {
		return array('blob' => $packed, 'dict' => '');
	}
	if ($dictPatterns === array()) {
		return array('blob' => $blob, 'dict' => '');
	}
	$dictParts = array(fractal_zip_enwik_encode_varint_u32(count($dictPatterns)));
	foreach ($dictPatterns as $pat) {
		$dictParts[] = fractal_zip_enwik_encode_varint_u32(strlen($pat)) . $pat;
	}
	return array('blob' => $packed, 'dict' => implode('', $dictParts));
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_phrase_pack_parse_dict(string $dict): array
{
	if ($dict === '') {
		return array();
	}
	$off = 0;
	$dv = fractal_zip_enwik_decode_varint_u32($dict, $off);
	if ($dv === null) {
		return array();
	}
	$n = $dv[0];
	$off = $dv[1];
	$patterns = array();
	for ($i = 0; $i < $n; $i++) {
		$dv = fractal_zip_enwik_decode_varint_u32($dict, $off);
		if ($dv === null) {
			return array();
		}
		$len = $dv[0];
		$off = $dv[1];
		if ($len < 0 || $off + $len > strlen($dict)) {
			return array();
		}
		$patterns[] = substr($dict, $off, $len);
		$off += $len;
	}
	return $patterns;
}

/** Chain phrase-pack stages into one FZEP dict (restore undoes last stage first). */
function fractal_zip_enwik_phrase_pack_chain_dicts(string ...$dicts): string
{
	$patterns = array();
	foreach ($dicts as $dict) {
		if ($dict !== '') {
			$patterns = array_merge($patterns, fractal_zip_enwik_phrase_pack_parse_dict($dict));
		}
	}
	if ($patterns === array()) {
		return '';
	}
	$dictParts = array(fractal_zip_enwik_encode_varint_u32(count($patterns)));
	foreach ($patterns as $pat) {
		$dictParts[] = fractal_zip_enwik_encode_varint_u32(strlen($pat)) . $pat;
	}
	return implode('', $dictParts);
}

/**
 * Static MediaWiki export boilerplate (inspired by phda9 external dictionary, fz-native reversible tokens).
 *
 * @return array{blob: string, dict: string}
 */
function fractal_zip_enwik_boilerplate_pack_apply(string $blob): array
{
	return fractal_zip_enwik_phrase_pack_apply($blob, fractal_zip_enwik_boilerplate_phrases());
}

/**
 * High-frequency enwik8 / MediaWiki XML fragments (longest match first).
 *
 * @return list<string>
 */
/**
 * Mine frequent tokens inside article &lt;text&gt; lines (English + wiki markers).
 *
 * @return list<string>
 */
function fractal_zip_enwik_mine_article_word_phrases(
	string $blob,
	int $minCount = 800,
	int $maxEntries = 128
): array {
	$counts = array();
	$marker = '<text xml:space="preserve">';
	foreach (explode("\n", $blob) as $line) {
		$pos = strpos($line, $marker);
		if ($pos === false) {
			continue;
		}
		$text = substr($line, $pos + strlen($marker));
		$end = strpos($text, '</text>');
		if ($end !== false) {
			$text = substr($text, 0, $end);
		}
		if ($text === '') {
			continue;
		}
		if (preg_match_all('/[A-Za-z][A-Za-z0-9_]{2,15}/', $text, $wm)) {
			foreach ($wm[0] as $w) {
				if (!isset($counts[$w])) {
					$counts[$w] = 0;
				}
				$counts[$w]++;
			}
		}
	}
	$static = fractal_zip_enwik_wiki_word_phrases();
	foreach ($static as $w) {
		if (!isset($counts[$w])) {
			$counts[$w] = $minCount;
		}
	}
	$entries = array();
	foreach ($counts as $w => $c) {
		if ($c < $minCount || strlen($w) < 4) {
			continue;
		}
		$entries[] = $w;
	}
	usort(
		$entries,
		static function (string $a, string $b) use ($counts): int {
			$ca = $counts[$a];
			$cb = $counts[$b];
			if ($ca !== $cb) {
				return $cb <=> $ca;
			}
			return strlen($b) <=> strlen($a);
		}
	);
	return array_slice($entries, 0, $maxEntries);
}

/**
 * Wiki / English tokens often repeated across articles (space-padded for word boundaries).
 *
 * @return list<string>
 */
function fractal_zip_enwik_wiki_word_phrases(): array
{
	return array(
		' REDIRECT ',
		' Category:',
		' Template:',
		' [[',
		']] ',
		' the ',
		' and ',
		' of ',
		' in ',
		' to ',
		' was ',
		' is ',
		' for ',
		' on ',
		' with ',
		' as ',
		' by ',
		' from ',
		' that ',
		' it ',
		' an ',
		' be ',
		' are ',
		' were ',
		' or ',
		' has ',
		' had ',
		' not ',
		' but ',
		' his ',
		' her ',
		' its ',
		' this ',
		' which ',
		' also ',
		' have ',
		' been ',
		' their ',
		' would ',
		' who ',
		' about ',
	);
}

/**
 * Mine high-frequency lines and XML-ish tokens (cheap O(n); used once per encode).
 *
 * @return list<string>
 */
function fractal_zip_enwik_mine_corpus_phrases(
	string $blob,
	int $minLen = 10,
	int $maxLen = 96,
	int $minCount = 32,
	int $maxEntries = 192
): array {
	$counts = array();
	$lineMin = max($minLen, 12);
	$lineMax = min($maxLen, 120);
	foreach (explode("\n", $blob) as $line) {
		$len = strlen($line);
		if ($len < $lineMin || $len > $lineMax) {
			continue;
		}
		if (!isset($counts[$line])) {
			$counts[$line] = 0;
		}
		$counts[$line]++;
	}
	if (preg_match_all('/<[^>\r\n]{6,120}>/', $blob, $tagMatches)) {
		foreach ($tagMatches[0] as $tag) {
			$len = strlen($tag);
			if ($len < $minLen || $len > $maxLen) {
				continue;
			}
			if (!isset($counts[$tag])) {
				$counts[$tag] = 0;
			}
			$counts[$tag]++;
		}
	}
	$entries = array();
	foreach ($counts as $pat => $c) {
		if ($c < $minCount) {
			continue;
		}
		if (!fractal_zip_enwik_phrase_pack_lom_bracket_balanced($pat)) {
			continue;
		}
		$entries[] = $pat;
	}
	usort(
		$entries,
		static function (string $a, string $b) use ($counts): int {
			$ca = $counts[$a];
			$cb = $counts[$b];
			if ($ca !== $cb) {
				return $cb <=> $ca;
			}
			return strlen($b) <=> strlen($a);
		}
	);
	return array_slice($entries, 0, $maxEntries);
}

/** When true, corpus phrase mine/pack uses full blob (legacy; can break LOM). Default: text bodies only. */
function fractal_zip_enwik_corpus_phrases_full_blob_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES_FULL_BLOB');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/**
 * Concatenate prose inside &lt;text xml:space="preserve"&gt; across all pages (for mining).
 */
function fractal_zip_enwik_collect_text_body_prose_stream(string $blob): string
{
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		return '';
	}
	$parts = array();
	foreach ($split['pages'] as $pref) {
		$page = substr($blob, (int) $pref['start'], (int) $pref['len']);
		$marker = '<text xml:space="preserve">';
		$openLen = strlen($marker);
		$pos = 0;
		$n = strlen($page);
		while ($pos < $n) {
			$i = strpos($page, $marker, $pos);
			if ($i === false) {
				break;
			}
			$pos = $i + $openLen;
			$close = strpos($page, '</text>', $pos);
			if ($close === false) {
				break;
			}
			$parts[] = substr($page, $pos, $close - $pos);
			$pos = $close + 7;
		}
	}
	return $parts === array() ? '' : implode("\n", $parts);
}

/**
 * Mine repeated lines from article &lt;text&gt; bodies only (no XML shell tokens).
 *
 * @return list<string>
 */
function fractal_zip_enwik_mine_corpus_phrases_from_text_bodies(
	string $blob,
	int $minLen = 10,
	int $maxLen = 96,
	int $minCount = 32,
	int $maxEntries = 192
): array {
	$prose = fractal_zip_enwik_collect_text_body_prose_stream($blob);
	if ($prose === '') {
		return array();
	}
	$counts = array();
	$lineMin = max($minLen, 12);
	$lineMax = min($maxLen, 120);
	foreach (explode("\n", $prose) as $line) {
		$len = strlen($line);
		if ($len < $lineMin || $len > $lineMax) {
			continue;
		}
		if (strpbrk($line, '<>') !== false) {
			continue;
		}
		if (!isset($counts[$line])) {
			$counts[$line] = 0;
		}
		$counts[$line]++;
	}
	$entries = array();
	foreach ($counts as $pat => $c) {
		if ($c < $minCount) {
			continue;
		}
		$entries[] = $pat;
	}
	usort(
		$entries,
		static function (string $a, string $b) use ($counts): int {
			$ca = $counts[$a];
			$cb = $counts[$b];
			if ($ca !== $cb) {
				return $cb <=> $ca;
			}
			return strlen($b) <=> strlen($a);
		}
	);
	return array_slice($entries, 0, $maxEntries);
}

/**
 * Apply phrase pack only inside &lt;text&gt; regions; XML shell and tag balance unchanged.
 *
 * @param list<string> $phrases
 * @return array{blob: string, dict: string}
 */
function fractal_zip_enwik_phrase_pack_in_text_regions_whole_blob(string $blob, array $phrases): array
{
	$split = enwik_split_page_refs($blob);
	if ($split === null || $phrases === array()) {
		return array('blob' => $blob, 'dict' => '');
	}
	$sharedDictPatterns = array();
	$out = (string) $split['header'];
	foreach ($split['pages'] as $pref) {
		$page = substr($blob, (int) $pref['start'], (int) $pref['len']);
		$packed = fractal_zip_enwik_phrase_pack_text_regions_in_page_with_dict($page, $phrases, $sharedDictPatterns);
		$out .= (string) $packed['blob'];
	}
	$out .= (string) $split['footer'];
	$dict = $sharedDictPatterns === array()
		? ''
		: fractal_zip_enwik_phrase_pack_dict_from_phrases($sharedDictPatterns);
	return array('blob' => $out, 'dict' => $dict);
}

/**
 * @param list<string> $phrases
 * @param list<string> $sharedDictPatterns global token table (by reference)
 * @return array{blob: string}
 */
function fractal_zip_enwik_phrase_pack_text_regions_in_page_with_dict(string $pageXml, array $phrases, array &$sharedDictPatterns): array
{
	if ($phrases === array() || $pageXml === '') {
		return array('blob' => $pageXml);
	}
	$marker = '<text xml:space="preserve">';
	$openLen = strlen($marker);
	$out = '';
	$pos = 0;
	$n = strlen($pageXml);
	while ($pos < $n) {
		$i = strpos($pageXml, $marker, $pos);
		if ($i === false) {
			$out .= substr($pageXml, $pos);
			break;
		}
		$out .= substr($pageXml, $pos, $i - $pos + $openLen);
		$pos = $i + $openLen;
		$close = strpos($pageXml, '</text>', $pos);
		if ($close === false) {
			$out .= substr($pageXml, $pos);
			break;
		}
		$inner = substr($pageXml, $pos, $close - $pos);
		$packed = fractal_zip_enwik_phrase_pack_apply($inner, $phrases, $sharedDictPatterns);
		$out .= (string) $packed['blob'];
		$pos = $close;
	}
	return array('blob' => $out);
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_boilerplate_phrases(): array
{
	return array(
		'<text xml:space="preserve">',
		'</text>
    </revision>
  </page>',
		'<revision>
      <id>',
		'</id>
      <timestamp>',
		'</timestamp>
      <contributor>
        <username>',
		'</username>
        <id>',
		'</id>
      </contributor>
      <text xml:space="preserve">',
		'<revision>
      <id>',
		'<contributor>
        <ip>',
		'</ip>
      </contributor>
      <text xml:space="preserve">',
		'    <revision>
      <id>',
		'  <page>
    <title>',
		'</title>
    <id>',
		'</id>
    <revision>',
		'<namespace key="0" />',
		'<namespace key="1">Talk</namespace>',
		'<namespace key="2">User</namespace>',
		'<namespace key="3">User talk</namespace>',
		'<namespace key="4">Wikipedia</namespace>',
		'<namespace key="5">Wikipedia talk</namespace>',
		'<namespace key="6">Image</namespace>',
		'<namespace key="7">Image talk</namespace>',
		'<namespace key="10">Template</namespace>',
		'<namespace key="11">Template talk</namespace>',
		'<namespace key="14">Category</namespace>',
		'<namespace key="15">Category talk</namespace>',
		'<generator>MediaWiki 1.6alpha</generator>',
		'<sitename>Wikipedia</sitename>',
		'<base>http://en.wikipedia.org/wiki/Main_Page</base>',
		'<case>first-letter</case>',
	);
}

/**
 * Template-literal semantic pack (corpus-built dictionary, no external refs).
 *
 * @return array{blob: string, dict: string}
 */
function fractal_zip_enwik_semantic_pack_apply(string $blob): array
{
	$minLen = 12;
	$minCount = 4;
	$maxEntries = 4096;
	if (!preg_match_all('/\{\{[^}{]{10,200}\}\}/', $blob, $m)) {
		return array('blob' => $blob, 'dict' => '');
	}
	$counts = array();
	foreach ($m[0] as $pat) {
		if (!isset($counts[$pat])) {
			$counts[$pat] = 0;
		}
		$counts[$pat]++;
	}
	arsort($counts, SORT_NUMERIC);
	$entries = array();
	foreach ($counts as $pat => $c) {
		if ($c < $minCount || strlen($pat) < $minLen) {
			continue;
		}
		$entries[] = $pat;
		if (count($entries) >= $maxEntries) {
			break;
		}
	}
	return fractal_zip_enwik_phrase_pack_apply($blob, $entries);
}

/** Printable ASCII token (avoids literal printable-peel OOM on long XML lines). */
function fractal_zip_enwik_phrase_pack_token(int $n): string
{
	return '~EP' . fractal_zip_enwik_semantic_pack_id($n) . '~';
}

function fractal_zip_enwik_semantic_pack_id(int $n): string
{
	$alphabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz+/';
	$out = '';
	$n = max(0, $n);
	do {
		$out = $alphabet[$n % 64] . $out;
		$n = intdiv($n, 64);
	} while ($n > 0);
	return str_pad($out, 2, '0', STR_PAD_LEFT);
}

function fractal_zip_enwik_semantic_pack_restore(string $blob, string $dictBlob): string
{
	if ($dictBlob === '') {
		return $blob;
	}
	$off = 0;
	$dv = fractal_zip_enwik_decode_varint_u32($dictBlob, $off);
	if ($dv === null) {
		return $blob;
	}
	$n = $dv[0];
	$off = $dv[1];
	$patterns = array();
	for ($i = 0; $i < $n; $i++) {
		$dv = fractal_zip_enwik_decode_varint_u32($dictBlob, $off);
		if ($dv === null) {
			return $blob;
		}
		$len = $dv[0];
		$off = $dv[1];
		if ($len < 0 || $off + $len > strlen($dictBlob)) {
			return $blob;
		}
		$patterns[] = substr($dictBlob, $off, $len);
		$off += $len;
	}
	$sharedTextVocab = null;
	if ($dictBlob !== '') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
		$sharedTextVocab = fractal_zip_enwik_text_codec_vocab_from_dict_patterns($patterns);
	}
	for ($i = count($patterns) - 1; $i >= 0; $i--) {
		$pat = $patterns[$i];
		if (str_starts_with($pat, "EZTV\x01")) {
			continue;
		}
		$replacement = $pat;
		if (str_starts_with($pat, "EZTC\x01")) {
			$replacement = fractal_zip_enwik_text_codec_restore_entry($pat, $sharedTextVocab);
		}
		$tok = fractal_zip_enwik_phrase_pack_token($i);
		$blob = str_replace($tok, $replacement, $blob);
		$legacy = "\x1FEP" . fractal_zip_enwik_semantic_pack_id($i) . "\x1F";
		if ($legacy !== $tok) {
			$blob = str_replace($legacy, $replacement, $blob);
		}
		$legacyCodec = "\x1FEZT" . fractal_zip_enwik_semantic_pack_id($i) . "\x1F";
		if ($legacyCodec !== $tok) {
			$blob = str_replace($legacyCodec, $replacement, $blob);
		}
	}
	return $blob;
}

/**
 * After sorted-folder encode: if raw-order PAQ on source enwik8 beats .fz wire, replace payload.
 *
 * @param array<string, mixed> $ctx
 */
function fractal_zip_enwik_try_raw_paq_replace_fzc_if_smaller(string $fzcPath, array &$ctx): bool
{
	if (!fractal_zip_enwik_raw_paq_on_source_enabled()) {
		return false;
	}
	$src = (string) ($ctx['sourcePath'] ?? '');
	if ($src === '' || !is_file($fzcPath) || !is_file($src)) {
		return false;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$existing = file_get_contents($fzcPath);
	if (!is_string($existing) || $existing === '') {
		return false;
	}
	$tool = null;
	$wire = null;
	$fromCache = false;
	if (!fractal_zip_enwik_raw_paq_force_live()) {
		if (fractal_zip_enwik_raw_paq_prefer_squash_cache()) {
			$cached = fractal_zip_enwik_try_load_paq_squash_wire();
			if ($cached !== null) {
				$wire = $cached['wire'];
				$tool = $cached['tool'];
				$fromCache = true;
			} else {
				return false;
			}
		}
	}
	if ($wire === null) {
		if (fractal_zip_paq_discover_tools() === array()) {
			return false;
		}
		$rel = basename($src);
		$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzenwik_paq_' . substr(md5($src . "\0rawpaq"), 0, 16);
		if (is_dir($box)) {
			fractal_zip_enwik_recursive_remove($box);
		}
		if (!mkdir($box, 0755, true) && !is_dir($box)) {
			return false;
		}
		$target = $box . DIRECTORY_SEPARATOR . $rel;
		if (!@copy($src, $target)) {
			fractal_zip_enwik_recursive_remove($box);
			return false;
		}
		$r = fractal_zip_paq_smallest_single_file_archive($box, $rel);
		fractal_zip_enwik_recursive_remove($box);
		if (!is_string($r['bytes']) || $r['bytes'] === '' || !is_string($r['tool'])) {
			return false;
		}
		$tool = $r['tool'];
		$wire = fractal_zip_paq_wrap_wire($r['tool'], $r['bytes']);
	}
	if ($wire === null || $tool === null || strlen($wire) >= strlen($existing)) {
		return false;
	}
	if (file_put_contents($fzcPath, $wire) === false) {
		return false;
	}
	$ctx['rawPaqPassthrough'] = true;
	$ctx['rawPaqTool'] = $tool;
	$ctx['rawPaqBytes'] = strlen($wire);
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		$via = $fromCache ? 'squash cache' : 'live PAQ';
		@fwrite(STDERR, '[enwik] raw PAQ (' . $tool . ', ' . $via . ') beat fractal wire: ' . number_format(strlen($wire))
			. ' B vs ' . number_format(strlen($existing)) . " B\n");
	}
	return true;
}

/**
 * Synthetic tiny enwik XML for smokes (3 pages, out-of-order titles).
 */
function fractal_zip_enwik_synthetic_blob(): string
{
	return <<<'XML'
<mediawiki xmlns="http://www.mediawiki.org/xml/export-0.3/">
<siteinfo><sitename>Test</sitename></siteinfo>
<page><title>Zebra</title><id>3</id><revision><text>zebra</text></revision></page>
<page><title>Alpha</title><id>1</id><revision><text>alpha</text></revision></page>
<page><title>Middle</title><id>2</id><revision><text>middle</text></revision></page>
</mediawiki>
XML;
}

const FRACTAL_ZIP_ENWIK_TEXT_MARKER = '<text xml:space="preserve">';

/** Page XML with &lt;text&gt; bodies removed (shell only). */
function fractal_zip_enwik_extract_page_shell_only(string $pageXml): string
{
	$marker = FRACTAL_ZIP_ENWIK_TEXT_MARKER;
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
		$close = stripos($pageXml, '</text>', $i + $openLen);
		if ($close === false) {
			$out .= substr($pageXml, $pos);
			break;
		}
		$out .= substr($pageXml, $pos, $i + $openLen - $pos);
		$next = stripos($pageXml, $marker, $close + 7);
		if ($next === false) {
			$out .= substr($pageXml, $close);
			break;
		}
		$out .= substr($pageXml, $close, $next - $close);
		$pos = $next;
	}
	return $out;
}

/**
 * Split one page into shell + text bodies.
 *
 * @return array{shell: string, text: string}
 */
function fractal_zip_enwik_split_page_shell_and_text(string $pageXml): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
	return array(
		'shell' => fractal_zip_enwik_extract_page_shell_only($pageXml),
		'text' => fractal_zip_enwik_extract_page_preserve_text($pageXml),
	);
}

/**
 * Split enwik blob into header/footer and per-page shell+text (probe synthesizer).
 *
 * @return array{
 *   header: string,
 *   footer: string,
 *   pages: list<array{title: string, origIndex: int, shell: string, text: string, text_chars: int}>,
 *   text_total_chars: int,
 *   shell_total_bytes: int
 * }|null
 */
function fractal_zip_enwik_split_shell_and_text(string $blob, ?int $pageLimit = null): ?array
{
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		return null;
	}
	$pages = array();
	$textTotal = 0;
	$shellTotal = 0;
	$n = $pageLimit !== null ? min($pageLimit, count($split['pages'])) : count($split['pages']);
	for ($i = 0; $i < $n; $i++) {
		$ref = $split['pages'][$i];
		$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
		$parts = fractal_zip_enwik_split_page_shell_and_text($page);
		$text = (string) $parts['text'];
		$shell = (string) $parts['shell'];
		$textChars = strlen($text);
		$textTotal += $textChars;
		$shellTotal += strlen($shell);
		$pages[] = array(
			'title' => (string) $ref['title'],
			'origIndex' => (int) $ref['origIndex'],
			'shell' => $shell,
			'text' => $text,
			'text_chars' => $textChars,
		);
	}
	return array(
		'header' => (string) $split['header'],
		'footer' => (string) $split['footer'],
		'pages' => $pages,
		'text_total_chars' => $textTotal,
		'shell_total_bytes' => $shellTotal,
	);
}

/** @return list<string> */
function fractal_zip_enwik_text_layout_catalog(): array
{
	return array(
		'mono_concat',
		'per_page',
		'per_member',
		'sort_title',
		'sort_by_len',
		'perm_shuffle',
		'sort_lines_global',
		'mi_reorder',
		'mi_line_stripe',
		'multishift',
		'multishift_transpose',
		'bio_minhash_reorder',
		'bio_piecewise_shift',
		'bio_progressive',
		'bio_synteny',
		'bio_pangenome',
		'bio_minhash_piecewise',
	);
}

/**
 * Jaccard similarity on word-token sets (starlit-style MI reorder heuristic).
 */
function fractal_zip_enwik_text_page_word_jaccard(string $textA, string $textB): float
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
	$ta = fractal_zip_enwik_text_tokenize_words($textA);
	$tb = fractal_zip_enwik_text_tokenize_words($textB);
	if ($ta === array() && $tb === array()) {
		return 0.0;
	}
	$setA = array_fill_keys($ta, true);
	$setB = array_fill_keys($tb, true);
	$inter = 0;
	foreach ($setA as $w => $_) {
		if (isset($setB[$w])) {
			$inter++;
		}
	}
	$union = count($setA) + count($setB) - $inter;
	return $union > 0 ? $inter / $union : 0.0;
}

/**
 * Greedy MI article reorder: start longest-text page, append highest-Jaccard neighbor.
 *
 * @param list<array{title: string, origIndex: int, text: string, text_chars: int}> $pages
 * @return list<int> permutation over page indices 0..n-1
 */
function fractal_zip_enwik_text_mi_reorder_perm(array $pages): array
{
	$n = count($pages);
	if ($n <= 1) {
		return range(0, max(0, $n - 1));
	}
	$remaining = range(0, $n - 1);
	usort($remaining, static function (int $a, int $b) use ($pages): int {
		$ca = (int) ($pages[$a]['text_chars'] ?? strlen((string) ($pages[$a]['text'] ?? '')));
		$cb = (int) ($pages[$b]['text_chars'] ?? strlen((string) ($pages[$b]['text'] ?? '')));
		return $cb <=> $ca;
	});
	$order = array($remaining[0]);
	unset($remaining[0]);
	$remaining = array_values($remaining);
	while ($remaining !== array()) {
		$lastIdx = $order[count($order) - 1];
		$lastText = (string) ($pages[$lastIdx]['text'] ?? '');
		$bestPos = 0;
		$bestSim = -1.0;
		foreach ($remaining as $pos => $idx) {
			$sim = fractal_zip_enwik_text_page_word_jaccard($lastText, (string) ($pages[$idx]['text'] ?? ''));
			if ($sim > $bestSim) {
				$bestSim = $sim;
				$bestPos = $pos;
			}
		}
		$order[] = $remaining[$bestPos];
		array_splice($remaining, $bestPos, 1);
	}
	return $order;
}

/**
 * After mi_reorder page order, interleave line k across consecutive pages (starlit-style stripe).
 *
 * @param list<array{text: string}> $pagesInOrder pages already in mi_reorder order
 * @return array{lines: list<string>, line_counts: list<int>}
 */
function fractal_zip_enwik_text_mi_line_stripe_apply(array $pagesInOrder): array
{
	$pageLines = array();
	$lineCounts = array();
	foreach ($pagesInOrder as $pg) {
		$lines = explode("\n", (string) ($pg['text'] ?? ''));
		$pageLines[] = $lines;
		$lineCounts[] = count($lines);
	}
	$n = count($pageLines);
	$maxLines = $lineCounts !== array() ? max($lineCounts) : 0;
	$striped = array();
	for ($k = 0; $k < $maxLines; $k++) {
		for ($p = 0; $p < $n; $p++) {
			if ($k < $lineCounts[$p]) {
				$striped[] = $pageLines[$p][$k];
			}
		}
	}
	return array('lines' => $striped, 'line_counts' => $lineCounts);
}

/**
 * Undo {@see fractal_zip_enwik_text_mi_line_stripe_apply()} given perm-ordered line counts.
 *
 * @param list<string> $stripedLines
 * @param list<int> $lineCounts one count per page in perm order
 * @return list<string> page texts in perm order
 */
function fractal_zip_enwik_text_mi_line_stripe_undo(array $stripedLines, array $lineCounts): array
{
	$n = count($lineCounts);
	$maxLines = $lineCounts !== array() ? max($lineCounts) : 0;
	$pageLines = array_fill(0, $n, array());
	$pos = 0;
	for ($k = 0; $k < $maxLines; $k++) {
		for ($p = 0; $p < $n; $p++) {
			if ($k < (int) ($lineCounts[$p] ?? 0)) {
				$pageLines[$p][] = $stripedLines[$pos] ?? '';
				$pos++;
			}
		}
	}
	$out = array();
	foreach ($pageLines as $lines) {
		$out[] = implode("\n", $lines);
	}
	return $out;
}

/**
 * Apply layout variant to page text chunks; returns concat blob + reversible meta.
 *
 * @param list<array{title: string, origIndex: int, text: string, text_chars?: int}> $pages
 * @return array{
 *   text_blob: string,
 *   layout_id: string,
 *   meta: array<string, mixed>,
 *   text_chars: int,
 *   page_order: list<int>
 * }
 */
/**
 * Entry-sorted chunks under sort_title use identity within-chunk perm (alphabetical order is implicit).
 *
 * @param list<int> $perm
 */
function fractal_zip_enwik_chunk_perm_is_identity(array $perm): bool
{
	foreach ($perm as $i => $v) {
		if ((int) $v !== (int) $i) {
			return false;
		}
	}
	return true;
}

/**
 * @param list<int> $chunkPerms stored per-chunk perms (may be empty when implicit)
 * @return list<int>
 */
function fractal_zip_enwik_text_inner_chunk_perm(
	int $chunkIdx,
	int $pagesInMember,
	array $chunkPerms,
	bool $implicitAlphaSort,
	string $layoutId
): array {
	$identity = range(0, max(0, $pagesInMember - 1));
	if ($implicitAlphaSort || ($layoutId === 'sort_title' && $chunkPerms === array())) {
		return $identity;
	}
	return $chunkPerms[$chunkIdx] ?? $identity;
}

function fractal_zip_enwik_text_layout_apply(array $pages, string $layoutId, array $opts = array()): array
{
	$layoutId = strtolower(trim($layoutId));
	if (!in_array($layoutId, fractal_zip_enwik_text_layout_catalog(), true)) {
		throw new InvalidArgumentException('unknown text layout: ' . $layoutId);
	}
	$n = count($pages);
	$indices = range(0, max(0, $n - 1));
	$meta = array('layout' => $layoutId);
	$seed = (int) ($opts['seed'] ?? 1);
	$pagesPerMember = (int) ($opts['pages_per_member'] ?? fractal_zip_enwik_pages_per_member());

	switch ($layoutId) {
		case 'sort_title':
			usort($indices, static function (int $a, int $b) use ($pages): int {
				$ka = fractal_zip_enwik_title_sort_key((string) ($pages[$a]['title'] ?? ''));
				$kb = fractal_zip_enwik_title_sort_key((string) ($pages[$b]['title'] ?? ''));
				$c = strcmp($ka, $kb);
				return $c !== 0 ? $c : ((int) ($pages[$a]['origIndex'] ?? $a) <=> (int) ($pages[$b]['origIndex'] ?? $b));
			});
			$meta['perm'] = $indices;
			break;
		case 'sort_by_len':
			usort($indices, static fn (int $a, int $b): int => strlen((string) ($pages[$b]['text'] ?? ''))
				<=> strlen((string) ($pages[$a]['text'] ?? '')));
			$meta['perm'] = $indices;
			break;
		case 'perm_shuffle':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
			$meta['seed'] = $seed;
			$meta['perm'] = fractal_zip_enwik_text_lcg_shuffle_perm($indices, $seed);
			$indices = $meta['perm'];
			break;
		case 'mi_reorder':
			$meta['perm'] = fractal_zip_enwik_text_mi_reorder_perm($pages);
			$indices = $meta['perm'];
			break;
		case 'mi_line_stripe':
			$meta['perm'] = fractal_zip_enwik_text_mi_reorder_perm($pages);
			$indices = $meta['perm'];
			$orderedPages = array();
			foreach ($indices as $idx) {
				$orderedPages[] = $pages[(int) $idx];
			}
			$stripe = fractal_zip_enwik_text_mi_line_stripe_apply($orderedPages);
			$meta['line_counts'] = $stripe['line_counts'];
			return array(
				'text_blob' => implode("\n", $stripe['lines']),
				'layout_id' => $layoutId,
				'meta' => $meta,
				'text_chars' => array_sum(array_map(static fn (array $pg): int => strlen((string) ($pg['text'] ?? '')), $pages)),
				'page_order' => $indices,
			);
		case 'multishift':
		case 'multishift_transpose':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
			$msOpts = is_array($opts['multishift'] ?? null) ? $opts['multishift'] : array();
			if ($layoutId === 'multishift_transpose') {
				$msOpts['force_transpose'] = true;
			}
			$built = fractal_zip_multishift_build($pages, $msOpts);
			return array(
				'text_blob' => (string) $built['blob'],
				'layout_id' => $layoutId,
				'meta' => array(
					'layout' => $layoutId,
					'fzms_b64' => base64_encode((string) $built['fzms']),
				),
				'text_chars' => array_sum(array_map(static fn (array $pg): int => strlen((string) ($pg['text'] ?? '')), $pages)),
				'page_order' => range(0, max(0, $n - 1)),
			);
		case 'bio_minhash_reorder':
		case 'bio_piecewise_shift':
		case 'bio_progressive':
		case 'bio_synteny':
		case 'bio_pangenome':
		case 'bio_minhash_piecewise':
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bio_align.php';
			$bioOpts = is_array($opts['bio'] ?? null) ? $opts['bio'] : array();
			$bio = fractal_zip_bio_layout_apply($pages, $layoutId, $bioOpts);
			$pageOrder = isset($bio['meta']['perm']) && is_array($bio['meta']['perm'])
				? $bio['meta']['perm']
				: range(0, max(0, $n - 1));
			return array(
				'text_blob' => (string) $bio['text_blob'],
				'layout_id' => $layoutId,
				'meta' => $bio['meta'],
				'text_chars' => array_sum(array_map(static fn (array $pg): int => strlen((string) ($pg['text'] ?? '')), $pages)),
				'page_order' => $pageOrder,
			);
		case 'sort_lines_global':
			$all = '';
			foreach ($pages as $p) {
				$all .= (string) ($p['text'] ?? '');
			}
			$lines = explode("\n", $all);
			$order = range(0, max(0, count($lines) - 1));
			usort($order, static function (int $a, int $b) use ($lines): int {
				return strcmp($lines[$a], $lines[$b]);
			});
			$sorted = array();
			foreach ($order as $oi) {
				$sorted[] = $lines[$oi];
			}
			return array(
				'text_blob' => implode("\n", $sorted),
				'layout_id' => $layoutId,
				'meta' => array('layout' => $layoutId, 'sort_lines_from' => $order),
				'text_chars' => strlen($all),
				'page_order' => range(0, max(0, $n - 1)),
			);
		case 'per_page':
			$chunks = array();
			$boundaries = array();
			$off = 0;
			foreach ($pages as $i => $p) {
				$t = (string) ($p['text'] ?? '');
				$boundaries[] = array('page_idx' => $i, 'off' => $off, 'len' => strlen($t));
				$chunks[] = $t;
				$off += strlen($t);
			}
			return array(
				'text_blob' => implode('', $chunks),
				'layout_id' => $layoutId,
				'meta' => array('layout' => $layoutId, 'boundaries' => $boundaries),
				'text_chars' => $off,
				'page_order' => range(0, max(0, $n - 1)),
			);
		case 'per_member':
			$chunks = array();
			$ranges = array();
			$off = 0;
			$member = 0;
			foreach ($pages as $i => $p) {
				if ($i > 0 && $i % $pagesPerMember === 0) {
					$member++;
				}
				$t = (string) ($p['text'] ?? '');
				if (!isset($ranges[$member])) {
					$ranges[$member] = array('member' => $member, 'off' => $off, 'len' => 0, 'page_idxs' => array());
				}
				$ranges[$member]['page_idxs'][] = $i;
				$ranges[$member]['len'] += strlen($t);
				$chunks[] = $t;
				$off += strlen($t);
			}
			return array(
				'text_blob' => implode('', $chunks),
				'layout_id' => $layoutId,
				'meta' => array('layout' => $layoutId, 'member_ranges' => array_values($ranges), 'pages_per_member' => $pagesPerMember),
				'text_chars' => $off,
				'page_order' => range(0, max(0, $n - 1)),
			);
		case 'mono_concat':
		default:
			$indices = range(0, max(0, $n - 1));
			break;
	}

	$parts = array();
	$chars = 0;
	foreach ($indices as $idx) {
		$t = (string) ($pages[$idx]['text'] ?? '');
		$parts[] = $t;
		$chars += strlen($t);
	}
	if ($layoutId !== 'mono_concat' && !isset($meta['perm'])) {
		$meta['perm'] = $indices;
	}
	return array(
		'text_blob' => implode('', $parts),
		'layout_id' => $layoutId,
		'meta' => $meta,
		'text_chars' => $chars,
		'page_order' => $indices,
	);
}

/**
 * Undo layout meta to recover per-page text chunks in original page order.
 *
 * @param list<array{text: string}> $pages
 * @return list<string>
 */
function fractal_zip_enwik_text_layout_undo_chunks(string $textBlob, array $meta, array $pages): array
{
	$layout = strtolower(trim((string) ($meta['layout'] ?? 'mono_concat')));
	$n = count($pages);
	$out = array_fill(0, $n, '');
	if ($layout === 'mi_line_stripe') {
		$perm = $meta['perm'] ?? null;
		$lineCounts = $meta['line_counts'] ?? null;
		if (!is_array($perm) || count($perm) !== $n || !is_array($lineCounts) || count($lineCounts) !== $n) {
			throw new RuntimeException('layout undo: mi_line_stripe perm/line_counts missing or wrong length');
		}
		$stripedLines = explode("\n", $textBlob);
		$pageTexts = fractal_zip_enwik_text_mi_line_stripe_undo($stripedLines, array_map('intval', $lineCounts));
		foreach ($perm as $p => $origIdx) {
			$out[(int) $origIdx] = $pageTexts[(int) $p] ?? '';
		}
		return $out;
	}
	if ($layout === 'multishift' || $layout === 'multishift_transpose') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_multishift.php';
		$b64 = (string) ($meta['fzms_b64'] ?? '');
		$fzms = base64_decode($b64, true);
		if ($fzms === false) {
			throw new RuntimeException('layout undo: multishift fzms_b64 invalid');
		}
		$restored = fractal_zip_multishift_undo($textBlob, $fzms);
		if (count($restored) !== $n) {
			throw new RuntimeException('layout undo: multishift page count mismatch');
		}
		for ($i = 0; $i < $n; $i++) {
			$out[$i] = (string) ($restored[$i] ?? '');
		}
		return $out;
	}
	if (str_starts_with($layout, 'bio_')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_bio_align.php';
		$restored = fractal_zip_bio_layout_undo_chunks($textBlob, $meta, $pages);
		for ($i = 0; $i < $n; $i++) {
			$out[$i] = (string) ($restored[$i] ?? '');
		}
		return $out;
	}
	if ($layout === 'sort_lines_global') {
		$order = $meta['sort_lines_from'] ?? null;
		if (!is_array($order)) {
			throw new RuntimeException('layout undo: sort_lines_from missing');
		}
		$lines = explode("\n", $textBlob);
		$inv = array_fill(0, count($lines), 0);
		foreach ($order as $newPos => $oldPos) {
			$inv[(int) $oldPos] = (int) $newPos;
		}
		$restored = array();
		foreach ($lines as $i => $line) {
			$restored[$inv[$i] ?? $i] = $line;
		}
		ksort($restored);
		$joined = implode("\n", $restored);
		$off = 0;
		foreach ($pages as $i => $p) {
			$len = strlen((string) ($p['text'] ?? ''));
			$out[$i] = substr($joined, $off, $len);
			$off += $len;
		}
		return $out;
	}
	if ($layout === 'per_page' || $layout === 'per_member') {
		$boundaries = $meta['boundaries'] ?? null;
		if ($layout === 'per_member') {
			$boundaries = array();
			foreach ($meta['member_ranges'] ?? array() as $mr) {
				$off = (int) ($mr['off'] ?? 0);
				foreach ($mr['page_idxs'] ?? array() as $pidx) {
					$len = strlen((string) ($pages[(int) $pidx]['text'] ?? ''));
					$boundaries[] = array('page_idx' => (int) $pidx, 'off' => $off, 'len' => $len);
					$off += $len;
				}
			}
		}
		if (!is_array($boundaries)) {
			throw new RuntimeException('layout undo: boundaries missing');
		}
		foreach ($boundaries as $b) {
			$idx = (int) ($b['page_idx'] ?? -1);
			$off = (int) ($b['off'] ?? 0);
			$len = (int) ($b['len'] ?? 0);
			if ($idx >= 0 && $idx < $n) {
				$out[$idx] = substr($textBlob, $off, $len);
			}
		}
		return $out;
	}
	$perm = $meta['perm'] ?? range(0, max(0, $n - 1));
	if (!is_array($perm) || count($perm) !== $n) {
		throw new RuntimeException('layout undo: perm missing or wrong length');
	}
	$lens = array();
	$total = 0;
	foreach ($perm as $idx) {
		$len = strlen((string) ($pages[(int) $idx]['text'] ?? ''));
		$lens[] = $len;
		$total += $len;
	}
	$off = 0;
	$chunks = array();
	foreach ($lens as $len) {
		$chunks[] = substr($textBlob, $off, $len);
		$off += $len;
	}
	$inv = array_fill(0, $n, 0);
	foreach ($perm as $newPos => $origIdx) {
		$inv[(int) $origIdx] = (int) $newPos;
	}
	for ($i = 0; $i < $n; $i++) {
		$out[$i] = $chunks[$inv[$i]] ?? '';
	}
	return $out;
}

const FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_TEXT = "FZTX\x01";
const FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_SHELL = "FZSH\x01";
const FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_LAYOUT = "FZLY\x01";
const FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_STAT = "FZSP\x01";
const FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_DICT = "FZDI\x01";
const FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_SK = "FZSK\x01";

/** @var array<string, mixed> */
$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array();

/** @return array<string, mixed> */
function fractal_zip_enwik_text_inner_last_build_stats(): array
{
	return is_array($GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] ?? null)
		? $GLOBALS['fractal_zip_enwik_text_inner_last_build_stats']
		: array();
}

function fractal_zip_enwik_stat_pred_preprocess_family(string $preprocessId): bool
{
	return in_array($preprocessId, array('stat_pred', 'stat_pred_compact_meta', 'stat_pred_inner'), true);
}

/**
 * Merge text + shell streams with tagged sections for pre-outer probing.
 *
 * @param array<string, mixed> $layoutMeta
 */
function fractal_zip_enwik_build_split_inner_blob(
	string $textBlob,
	string $shellBlob,
	array $layoutMeta = array(),
	string $statPredBlob = '',
	string $dictFoldBlob = '',
	string $skFoldBlob = ''
): string {
	$parts = array();
	if ($dictFoldBlob !== '') {
		$parts[] = FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_DICT;
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($dictFoldBlob));
		$parts[] = $dictFoldBlob;
	}
	$parts = array_merge($parts, array(
		FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_TEXT,
		fractal_zip_enwik_encode_varint_u32(strlen($textBlob)),
		$textBlob,
	));
	if ($skFoldBlob !== '') {
		$parts[] = FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_SK;
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($skFoldBlob));
		$parts[] = $skFoldBlob;
	}
	$parts = array_merge($parts, array(
		FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_SHELL,
		fractal_zip_enwik_encode_varint_u32(strlen($shellBlob)),
		$shellBlob,
	));
	if ($layoutMeta !== array()) {
		$metaJson = json_encode($layoutMeta, JSON_UNESCAPED_UNICODE);
		if (!is_string($metaJson)) {
			throw new RuntimeException('split inner layout meta json failed');
		}
		$parts[] = FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_LAYOUT;
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($metaJson));
		$parts[] = $metaJson;
	}
	if ($statPredBlob !== '') {
		$parts[] = FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_STAT;
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($statPredBlob));
		$parts[] = $statPredBlob;
	}
	return implode('', $parts);
}

/**
 * Parse split inner blob from {@see fractal_zip_enwik_build_split_inner_blob()}.
 *
 * @return array{text: string, shell: string, layout_meta: array<string, mixed>, stat_pred_blob: string, fold_dict: string}
 */
function fractal_zip_enwik_parse_split_inner_blob(string $blob): array
{
	$off = 0;
	$len = strlen($blob);
	$foldDict = '';
	if (str_starts_with($blob, FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_DICT)) {
		$off += strlen(FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_DICT);
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('split inner: FZDI length missing');
		}
		$segLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($segLen < 0 || $off + $segLen > $len) {
			throw new RuntimeException('split inner: FZDI truncated');
		}
		$foldDict = substr($blob, $off, $segLen);
		$off += $segLen;
	}
	$readSection = static function (string $magic) use ($blob, &$off, $len): string {
		$ml = strlen($magic);
		if ($off + $ml > $len || substr($blob, $off, $ml) !== $magic) {
			throw new RuntimeException('split inner: expected ' . $magic);
		}
		$off += $ml;
		$dv = fractal_zip_enwik_decode_varint_u32($blob, $off);
		if ($dv === null) {
			throw new RuntimeException('split inner: length missing after ' . $magic);
		}
		$segLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($segLen < 0 || $off + $segLen > $len) {
			throw new RuntimeException('split inner: truncated section ' . $magic);
		}
		$seg = substr($blob, $off, $segLen);
		$off += $segLen;
		return $seg;
	};
	$text = $readSection(FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_TEXT);
	$skBlob = '';
	if ($off < $len && str_starts_with(substr($blob, $off), FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_SK)) {
		$skBlob = $readSection(FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_SK);
	}
	$shell = $readSection(FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_SHELL);
	$layoutMeta = array();
	if ($off < $len && str_starts_with(substr($blob, $off), FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_LAYOUT)) {
		$metaJson = $readSection(FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_LAYOUT);
		$decoded = json_decode($metaJson, true);
		if (is_array($decoded)) {
			$layoutMeta = $decoded;
		}
	}
	$statPredBlob = '';
	if ($off < $len && str_starts_with(substr($blob, $off), FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_STAT)) {
		$statPredBlob = $readSection(FRACTAL_ZIP_ENWIK_SPLIT_INNER_MAGIC_STAT);
	}
	return array(
		'text' => $text,
		'shell' => $shell,
		'sk_blob' => $skBlob,
		'layout_meta' => $layoutMeta,
		'stat_pred_blob' => $statPredBlob,
		'fold_dict' => $foldDict,
	);
}

/** bpc = 8 * compressed_bytes / corpus_bytes (Mahoney LTCB convention). */
function fractal_zip_enwik_text_bpc(int $compressedBytes, int $textCharCount, int $corpusBytes = 100000000): float
{
	if ($textCharCount <= 0) {
		return 0.0;
	}
	$fullScaled = 8.0 * $compressedBytes / max(1, $corpusBytes);
	return round($fullScaled * ($corpusBytes / $textCharCount), 6);
}

/** @return array<string, array{bytes: int, bpc: float, label: string}> */
function fractal_zip_enwik_ltcb_anchor_table(): array
{
	return array(
		'nncp32' => array('bytes' => 14915298, 'bpc' => 1.19, 'label' => 'nncp v3.2'),
		'cmix21' => array('bytes' => 14623723, 'bpc' => 1.17, 'label' => 'cmix v21'),
		'phda9' => array('bytes' => 15010414, 'bpc' => 1.20, 'label' => 'phda9 1.8'),
		'starlit' => array('bytes' => 15215107, 'bpc' => 1.22, 'label' => 'starlit'),
		'xwrt' => array('bytes' => 18679742, 'bpc' => 1.49, 'label' => 'xwrt 3.2'),
		'zpaq' => array('bytes' => 17855729, 'bpc' => 1.43, 'label' => 'zpaq 6.42'),
		'pp96' => array('bytes' => 19594333, 'bpc' => 1.57, 'label' => 'fractal pp96'),
		'hutter' => array('bytes' => 15284944, 'bpc' => 1.22, 'label' => 'Hutter phda9'),
		'sub1_text' => array('bytes' => 12500000, 'bpc' => 1.0, 'label' => 'sub-1 text target'),
	);
}

function fractal_zip_enwik_text_inner_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_TEXT_INNER');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

/** @return list<string> */
function fractal_zip_enwik_text_inner_preprocess_wire_allowlist(): array
{
	return array('none', 'dict_nncp', 'dict_inner', 'dict_phda9_inner', 'wrt_xwrt', 'stat_wrt', 'stat_isp', 'stat_syllable_isp', 'consonant_hybrid', 'consonant_hybrid_split', 'consonant_hybrid_lossy', 'stat_pred', 'stat_pred_compact_meta', 'stat_pred_inner', 'isp_varint', 'textcodec_isp', 'cycle_inner', 'cycle_delta', 'qg_word_root', 'qg_subword_root', 'qg_hybrid_root', 'qg_text_normalize', 'wiki_lom');
}

function fractal_zip_enwik_text_inner_preprocess_id(): string
{
	$raw = strtolower(trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS') ?: 'none')));
	if ($raw === '' || $raw === '0' || $raw === 'none') {
		return 'none';
	}
	if (in_array($raw, fractal_zip_enwik_text_inner_preprocess_wire_allowlist(), true)) {
		return $raw;
	}
	if ($raw === 'dict_phda9') {
		if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
			@fwrite(STDERR, '[enwik] text-inner preprocess ' . $raw . ' skipped on wire (not lossless); lab probe only' . "\n");
		}
		return 'none';
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[enwik] text-inner preprocess "' . $raw . '" not enabled on wire; using none' . "\n");
	}
	return 'none';
}

function fractal_zip_enwik_text_inner_member_format(): string
{
	$raw = strtolower(trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_FORMAT') ?: 'dual')));
	if ($raw === 'split_inner' || $raw === 'fztx') {
		return 'fztx';
	}
	if ($raw === 'phda9_xml' || $raw === 'phda9') {
		return 'phda9_xml';
	}
	if ($raw === 'phda9_article') {
		return 'phda9_article';
	}
	return $raw === 'dual' ? 'dual' : 'dual';
}

function fractal_zip_enwik_text_inner_mono_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_TEXT_INNER_MONO');
	return $v === '1' || strtolower(trim((string) $v)) === 'true' || strtolower(trim((string) $v)) === 'yes';
}

function fractal_zip_enwik_text_inner_stack_id(): string
{
	$raw = strtolower(trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_STACK') ?: 'none')));
	if ($raw === '' || $raw === '0' || $raw === 'none') {
		return 'none';
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
	if (in_array($raw, fractal_zip_text_stacked_outer_catalog(), true)) {
		return $raw;
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		@fwrite(STDERR, '[enwik] text-inner stack "' . $raw . '" unknown; using none' . "\n");
	}
	return 'none';
}

function fractal_zip_enwik_text_inner_build_jobs(): int
{
	$e = getenv('FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(1, min(8, (int) trim((string) $e)));
	}
	if (!function_exists('fractal_zip_parallel_runtime_text_inner_build_jobs')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_runtime.php';
	}
	return fractal_zip_parallel_runtime_text_inner_build_jobs();
}

/**
 * @param list<array{title: string, origIndex: int, start: int, len: int, sortedIndex?: int}> $sortedChunk
 * @return list<array{title: string, origIndex: int, shell: string, text: string, text_chars: int}>
 */
function enwik_build_text_inner_split_pages_from_refs(array $sortedChunk, string $blob): array
{
	$splitPages = array();
	foreach ($sortedChunk as $p) {
		$page = substr($blob, (int) $p['start'], (int) $p['len']);
		$parts = fractal_zip_enwik_split_page_shell_and_text($page);
		$splitPages[] = array(
			'title' => (string) $p['title'],
			'origIndex' => (int) $p['origIndex'],
			'shell' => $parts['shell'],
			'text' => $parts['text'],
			'text_chars' => strlen($parts['text']),
		);
	}
	return $splitPages;
}

/**
 * @param list<array{title: string, origIndex: int, start: int, len: int, sortedIndex?: int}> $sortedChunk
 * @return list<array{title: string, origIndex: int, shell: string, text: string, text_chars: int}>
 */
function enwik_build_text_inner_split_pages_parallel(array $sortedChunk, string $blob, int $jobs): array
{
	$pageCount = count($sortedChunk);
	if ($jobs <= 1 || $pageCount < $jobs * 2 || PHP_OS_FAMILY === 'Windows'
		|| !function_exists('pcntl_fork') || !function_exists('pcntl_waitpid')) {
		return enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
	}
	$tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_ti_build_' . bin2hex(random_bytes(6));
	@mkdir($tmpBase, 0700, true);
	$chunkSize = (int) ceil($pageCount / $jobs);
	$children = array();
	for ($w = 0; $w < $jobs; $w++) {
		$startIdx = $w * $chunkSize;
		if ($startIdx >= $pageCount) {
			break;
		}
		$slice = array_slice($sortedChunk, $startIdx, $chunkSize);
		$outPath = $tmpBase . DIRECTORY_SEPARATOR . 'part_' . $w . '.json';
		$pid = pcntl_fork();
		if ($pid === -1) {
			fractal_zip_enwik_recursive_remove($tmpBase);
			return enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
		}
		if ($pid === 0) {
			$part = enwik_build_text_inner_split_pages_from_refs($slice, $blob);
			file_put_contents($outPath, json_encode($part, JSON_UNESCAPED_UNICODE));
			exit(0);
		}
		$children[] = array('pid' => $pid, 'out' => $outPath, 'start' => $startIdx);
	}
	$splitPages = array_fill(0, $pageCount, null);
	foreach ($children as $ch) {
		pcntl_waitpid((int) $ch['pid'], $status);
		if (!is_file($ch['out'])) {
			continue;
		}
		$decoded = json_decode((string) file_get_contents($ch['out']), true);
		if (!is_array($decoded)) {
			continue;
		}
		$base = (int) $ch['start'];
		foreach ($decoded as $i => $row) {
			if (is_array($row)) {
				$splitPages[$base + (int) $i] = $row;
			}
		}
	}
	fractal_zip_enwik_recursive_remove($tmpBase);
	$out = array();
	foreach ($splitPages as $row) {
		if (is_array($row)) {
			$out[] = $row;
		}
	}
	return $out !== array() ? $out : enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
}

/**
 * @return array<string, mixed>|null
 */
function fractal_zip_enwik_load_text_inner_layout_meta(string $extractRoot): ?array
{
	$full = $extractRoot . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_inner_layout.json';
	if (!is_file($full)) {
		return null;
	}
	$decoded = json_decode((string) file_get_contents($full), true);
	return is_array($decoded) ? $decoded : null;
}

/**
 * @return array{preprocess: string, sidecars: list<array<string, mixed>>}|null
 */
function fractal_zip_enwik_load_text_inner_preprocess_meta(string $extractRoot, ?string $fzcPath = null): ?array
{
	fractal_zip_enwik_ensure_syllable_codec();
	$mergeConsonantTrailer = static function (array $meta, string $innerFoldBlobGz): array {
		if ($innerFoldBlobGz === '') {
			return $meta;
		}
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		fractal_zip_enwik_ensure_syllable_codec();
		$fromTrailer = fractal_zip_enwik_inner_fold_preprocess_meta_from_trailer($innerFoldBlobGz);
		if (!is_array($fromTrailer)) {
			return $meta;
		}
		$tpid = (string) ($fromTrailer['preprocess'] ?? '');
		$pid = (string) ($meta['preprocess'] ?? 'none');
		if ($tpid !== $pid || !fractal_zip_enwik_consonant_hybrid_family_preprocess($tpid)) {
			return $meta;
		}
		foreach (array('skeleton_unique', 'skeleton_ambig', 'context_unique', 'payload_codec') as $k) {
			if (!isset($meta[$k]) && isset($fromTrailer[$k])) {
				$meta[$k] = $fromTrailer[$k];
			}
		}
		return $meta;
	};
	$candidates = array(
		'meta/text_inner_preprocess.bin.gz',
		'meta/text_inner_preprocess.bin',
		'meta/text_inner_preprocess.json.gz',
		'meta/text_inner_preprocess.json',
	);
	foreach ($candidates as $rel) {
		$full = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if (!is_file($full)) {
			continue;
		}
		$raw = (string) file_get_contents($full);
		if (str_ends_with($rel, '.gz')) {
			$decoded = @gzdecode($raw);
			if (!is_string($decoded)) {
				throw new RuntimeException('enwik restore: cannot decode ' . $rel);
			}
			$raw = $decoded;
		}
		if (str_ends_with($rel, '.bin.gz') || str_ends_with($rel, '.bin')) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
			return fractal_zip_enwik_stat_pred_deserialize_compact_meta($raw);
		}
		$meta = json_decode($raw, true);
		if (!is_array($meta)) {
			throw new RuntimeException('enwik restore: invalid text_inner_preprocess meta');
		}
		if (fractal_zip_enwik_consonant_hybrid_family_preprocess((string) ($meta['preprocess'] ?? ''))) {
			$skRaw = null;
			if (is_string($meta['sk_meta_b64'] ?? null) && $meta['sk_meta_b64'] !== '') {
				$skRaw = base64_decode((string) $meta['sk_meta_b64'], true);
			} elseif (is_string($meta['sk_meta_gz'] ?? null) && $meta['sk_meta_gz'] !== '') {
				$skRel = (string) $meta['sk_meta_gz'];
				$skFull = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $skRel);
				if (!is_file($skFull)) {
					throw new RuntimeException('enwik restore: missing ' . $skRel);
				}
				$skFile = (string) file_get_contents($skFull);
				if (str_ends_with($skRel, '.gz')) {
					$skDec = @gzdecode($skFile);
					if (!is_string($skDec)) {
						throw new RuntimeException('enwik restore: cannot decode ' . $skRel);
					}
					$skRaw = $skDec;
				} else {
					$skRaw = $skFile;
				}
			}
			if (is_string($skRaw) && $skRaw !== '') {
				$skMeta = fractal_zip_enwik_consonant_hybrid_unpack_compact_meta($skRaw);
				foreach (array('skeleton_unique', 'skeleton_ambig', 'context_unique', 'payload_codec') as $k) {
					if (array_key_exists($k, $skMeta)) {
						$meta[$k] = $skMeta[$k];
					}
				}
			}
		}
		$pid = (string) ($meta['preprocess'] ?? 'none');
		if (($pid === 'stat_pred_inner' || $pid === 'dict_inner' || $pid === 'dict_phda9_inner'
				|| fractal_zip_enwik_consonant_hybrid_family_preprocess($pid))
			&& !empty($meta['embedded'])) {
			if (!isset($meta['sidecars']) || !is_array($meta['sidecars'])) {
				$meta['sidecars'] = array();
			}
			if (fractal_zip_enwik_consonant_hybrid_family_preprocess($pid)
				&& !isset($meta['skeleton_unique']) && $fzcPath !== null) {
				$peel = fractal_zip_enwik_peel_trailer_from_fzc($fzcPath);
				if (is_array($peel)) {
					$meta = $mergeConsonantTrailer($meta, (string) ($peel['innerFoldBlob'] ?? ''));
				}
			}
			return $meta;
		}
		if (!isset($meta['sidecars']) || !is_array($meta['sidecars'])) {
			throw new RuntimeException('enwik restore: invalid text_inner_preprocess meta');
		}
		return $meta;
	}
	if ($fzcPath === null) {
		foreach (glob($extractRoot . DIRECTORY_SEPARATOR . '*.fz') ?: array() as $cand) {
			$fzcPath = (string) $cand;
			break;
		}
	}
	if ($fzcPath !== null && is_file($fzcPath)) {
		$peel = fractal_zip_enwik_peel_trailer_from_fzc($fzcPath);
		if (is_array($peel)) {
			$innerFoldBlobGz = (string) ($peel['innerFoldBlob'] ?? '');
			if ($innerFoldBlobGz !== '') {
				require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
				$fromTrailer = fractal_zip_enwik_inner_fold_preprocess_meta_from_trailer($innerFoldBlobGz);
				if (is_array($fromTrailer)) {
					return $fromTrailer;
				}
			}
		}
	}
	return null;
}
