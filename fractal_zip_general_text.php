<?php
declare(strict_types=1);

/**
 * General-purpose text-inner compression for fractal_zip.
 *
 * Detects textish members (via content_format identify), splits prose into blocks
 * (HTML <p>/<div>/<text>/… or plain-text paragraphs), wraps them in the same
 * page + <text xml:space="preserve"> wire used by enwik8 TEXT_INNER, and reuses
 * enwik_build_text_inner_virtual_folder_from_refs() + FZEP restore.
 *
 * Enwik8 MediaWiki corpora stay on the dedicated enwik entry-sort path; this
 * module runs only when that path does not apply.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_policy.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

const FRACTAL_ZIP_GENERAL_TEXT_ROOT = '<fztext>';
const FRACTAL_ZIP_GENERAL_TEXT_ROOT_CLOSE = '</fztext>';

/** @return 'off'|'on'|'auto' */
function fractal_zip_general_text_inner_mode(): string
{
	$v = getenv('FRACTAL_ZIP_GENERAL_TEXT_INNER');
	if ($v === false || trim((string) $v) === '') {
		// Lifestyle default: auto — single-file prose (dickens/nci/webster) gets the
		// same text-inner / phda9 lane as enwik-class corpora; multifile stays off
		// (see fractal_zip_general_text_try_prepare_virtual_folder). Explicit
		// FRACTAL_ZIP_GENERAL_TEXT_INNER=off or FRACTAL_ZIP_GENERAL_TEXT=0 disables.
		if (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
			&& fractal_zip::lifestyle_speed_profile_enabled()) {
			return 'auto';
		}
		return 'on';
	}
	$v = strtolower(trim((string) $v));
	if (in_array($v, array('0', 'off', 'false', 'no'), true)) {
		return 'off';
	}
	if (in_array($v, array('1', 'on', 'true', 'yes'), true)) {
		return 'on';
	}
	return 'auto';
}

function fractal_zip_general_text_inner_enabled(): bool
{
	// FRACTAL_ZIP_GENERAL_TEXT=0 is a hard kill for the whole lane (same as INNER=off).
	$g = getenv('FRACTAL_ZIP_GENERAL_TEXT');
	if ($g !== false && in_array(strtolower(trim((string) $g)), array('0', 'off', 'false', 'no'), true)) {
		return false;
	}
	return fractal_zip_general_text_inner_mode() !== 'off';
}

/**
 * Whether text-inner may rewrite multi-file folders (largest candidate + passthrough siblings).
 * Under the lifestyle/default speed profile this is off: mixed HTML/micro corpora pay seconds of
 * CM startup and often lose bytes vs unified stream. Bytes-first (lifestyle off / --ultra) keeps
 * multifile on. Override: FRACTAL_ZIP_GENERAL_TEXT_MULTIFILE=0|1.
 */
function fractal_zip_general_text_multifile_allowed(): bool
{
	$e = getenv('FRACTAL_ZIP_GENERAL_TEXT_MULTIFILE');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if (in_array($v, array('0', 'off', 'false', 'no'), true)) {
			return false;
		}
		if (in_array($v, array('1', 'on', 'true', 'yes'), true)) {
			return true;
		}
	}
	if (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')) {
		if (fractal_zip::lifestyle_speed_profile_enabled()) {
			return false;
		}
	}
	// Bytes-first / fair-full: keep per-file GT inside FZHM; do not swallow the
	// whole multifile tree into one virtual folder (Calgary lost its .fz write).
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if (is_string($lifeEnv) && in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true)) {
		return false;
	}
	return true;
}

function fractal_zip_general_text_min_file_bytes(): int
{
	$v = getenv('FRACTAL_ZIP_GENERAL_TEXT_MIN_BYTES');
	if ($v === false || trim((string) $v) === '') {
		// Lifestyle auto: skip tiny C/troff (fields.c / xargs.1) — bare tar|brotli
		// beats the phda9_xml wrapper there. Mid prose (≥48 KiB, test_files195)
		// and dickens+ stay on. Markup/plain use lower floors via
		// fractal_zip_general_text_min_file_bytes_for_profile().
		if (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
			&& fractal_zip::lifestyle_speed_profile_enabled()
			&& fractal_zip_general_text_inner_mode() === 'auto') {
			return 49152;
		}
		return 4096;
	}
	return max(256, (int) $v);
}

/**
 * Lifestyle profile floors below the default 48 KiB gate.
 * Markup (cp.html ~24 KiB) sole-wins on text-inner. Plain micros stay on the
 * high floor — epi/synthetic 10 KiB (test_files190) already sole-wins via 7z,
 * and forcing GT there regresses the wire.
 */
function fractal_zip_general_text_min_file_bytes_for_profile(string $profile): int
{
	$base = fractal_zip_general_text_min_file_bytes();
	if (!(class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()
		&& fractal_zip_general_text_inner_mode() === 'auto')) {
		return $base;
	}
	if ($profile === 'text_markup') {
		return min($base, 16384);
	}
	return $base;
}

/**
 * Absolute read floor before identify (profile min applied after).
 */
function fractal_zip_general_text_scan_floor_bytes(): int
{
	$base = fractal_zip_general_text_min_file_bytes();
	if (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()
		&& fractal_zip_general_text_inner_mode() === 'auto') {
		return min($base, 4096);
	}
	return $base;
}

/**
 * True when $allFiles is one main member plus only tiny sidecar(s) (corpus README, etc.).
 * Lets lifestyle/auto text-inner keep passthrough siblings without opening full multifile.
 *
 * @param array<string,string> $allFiles rel => absolute path
 */
function fractal_zip_general_text_tiny_sidecar_only(array $allFiles, string $mainPath): bool
{
	$extra = 0;
	$n = 0;
	foreach ($allFiles as $path) {
		if ((string) $path === $mainPath) {
			continue;
		}
		$sz = @filesize((string) $path);
		if ($sz === false || (int) $sz > 2048) {
			return false;
		}
		$extra += (int) $sz;
		$n++;
	}
	return $n >= 1 && $extra <= 4096;
}

function fractal_zip_general_text_min_block_chars(): int
{
	$v = getenv('FRACTAL_ZIP_GENERAL_TEXT_MIN_BLOCK_CHARS');
	if ($v === false || trim((string) $v) === '') {
		return 32;
	}
	return max(8, (int) $v);
}

/** Fast inner (tokenized_zpaq): one lossless whole-file block — better bytes and exact restore. */
function fractal_zip_general_text_whole_file_block_wanted(): bool
{
	$e = getenv('FRACTAL_ZIP_PHDA9_GENERAL_FAST');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	return !in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true);
}

/**
 * Opt-in block splitting (FRACTAL_ZIP_GENERAL_TEXT_SPLIT_BLOCKS=1). Default is one
 * lossless whole-file block: exact restore (block splitting trims/rejoins paragraphs —
 * lossy for irregular whitespace) and less <page> XML tax; phda9 amortizes over the
 * single contiguous stream either way.
 */
function fractal_zip_general_text_split_blocks_wanted(): bool
{
	$e = getenv('FRACTAL_ZIP_GENERAL_TEXT_SPLIT_BLOCKS');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	return !in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true);
}

/**
 * Wall budget (seconds) for the phda9 general text-inner job; 0 = unlimited.
 * On timeout the inner compress fails loudly (no silent codec fallback) — use
 * FRACTAL_ZIP_PHDA9_GENERAL_FAST=1 when a hard speed SLA matters.
 */
function fractal_zip_general_text_inner_wall_sec(): int
{
	$e = getenv('FRACTAL_ZIP_GENERAL_TEXT_INNER_WALL_SEC');
	if ($e === false || trim((string) $e) === '') {
		return 0;
	}
	return max(0, (int) trim((string) $e));
}

/**
 * Undo fztext synthetic wrapper back to the original source bytes (general text-inner).
 */
function fractal_zip_general_text_restore_from_synthetic_blob(string $blob, string $profile, array $preprocessMeta = array()): string
{
	if ($blob === '' || strpos($blob, FRACTAL_ZIP_GENERAL_TEXT_ROOT) === false) {
		return $blob;
	}
	// Single-block fztext is by construction the whole source file: return the body
	// untrimmed even without lossless_whole meta (container leaf path has no meta).
	$raw = fractal_zip_general_text_extract_preserve_text_blocks($blob, false);
	if (!empty($preprocessMeta['general_text_lossless_whole']) || count($raw) === 1) {
		return $raw !== array() ? (string) $raw[0]['text'] : $blob;
	}
	$blocks = fractal_zip_general_text_extract_preserve_text_blocks($blob, true);
	if ($blocks === array()) {
		return $blob;
	}
	if (count($blocks) === 1) {
		return (string) $blocks[0]['text'];
	}
	if ($profile === 'text_plain') {
		return implode("\n\n", array_map(static fn (array $b): string => (string) $b['text'], $blocks));
	}
	return $blob;
}

/**
 * Speed defaults for general text-inner (not enwik8): tokenized_zpaq inner (no phda9 startup).
 * Under lifestyle: short CM wall. Under ultra / LIFESTYLE=0: do not re-enable lifestyle or SPEED.
 */
function fractal_zip_general_text_apply_speed_env(): void
{
	if (!function_exists('fractal_zip_apply_native_accel_env')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_runtime_bootstrap.php';
	}
	fractal_zip_apply_native_accel_env();
	$set = static function (string $k, string $v): void {
		$e = getenv($k);
		if ($e === false || trim((string) $e) === '') {
			putenv($k . '=' . $v);
		}
	};
	$ultra = getenv('FRACTAL_ZIP_ULTRA') === '1';
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	$lifeExplicitOff = is_string($lifeEnv)
		&& in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true);
	$lifestyleOk = !$ultra && !$lifeExplicitOff;
	$set('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT', '0');
	$set('FRACTAL_ZIP_TEXT_INNER_STACK', 'none');
	$set('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM', '1');
	// Always tokenized_zpaq for general-text auto (phda9 whole-file fails on small HTML).
	// Ultra must not re-enable lifestyle/SPEED — only those stay gated.
	$set('FRACTAL_ZIP_PHDA9_GENERAL_FAST', '1');
	$set('FRACTAL_ZIP_PHDA9_DAEMON', '1');
	$set('FRACTAL_ZIP_PHDA9_FAST_PROBE', '0');
	if ($lifestyleOk) {
		$set('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '1');
		$set('FRACTAL_ZIP_SPEED', '1');
	}
	$set('FRACTAL_ZIP_SPEED_TRY_BROTLI', '1');
	// Virtual-folder members are one high-entropy codec wire + tiny meta JSONs;
	// recursive fractal substring search cannot gain there — cap its wall.
	$set('FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_SECONDS', '2');
	// Native raw-tree zpaq folder sweep (7 parallel `zpaq add`) cannot beat the
	// brotli/store outer on a folder whose main member is already a zpaq wire.
	$set('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE', '0');
	// Wire-only outer wrap is ~1 s, so deferred RT (decode overlaps wrap) loses
	// the race and rejects dickens/nci archives. Sync verify during compress.
	$set('FRACTAL_ZIP_TOKENIZED_DEFERRED_RT', '0');
	// Lifestyle: short CM wall drives auto-segmentation for half-time.
	// Bytes-first / fair-full: leave WALL unset so cm_segments() stays at 1
	// (whole-stream mcm/DRT — nci sole vs zpaq). Explicit WALL/SEGMENTS win.
	if ($lifestyleOk) {
		$set('FRACTAL_ZIP_TOKENIZED_CM_WALL_SEC', '8');
		// DRT: prepare gates — off for &lt;1 MiB CSV (53/151 mcm already soles);
		// on for ≥1 MiB (167/174 ~1.7 KiB each) to fund media-pack br q10.
	}
	// Hard wall for the phda9 inner job (0 = unlimited). Timeout-wrapped exec fails
	// loudly instead of silently switching codecs. Overrides the CLI's default
	// FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0 preset — explicit wall wins.
	$wall = fractal_zip_general_text_inner_wall_sec();
	if ($wall > 0 && !fractal_zip_general_text_whole_file_block_wanted()) {
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . $wall);
	}
}

/**
 * Outer-tournament trim for the pure single-file virtual folder: the wrap body
 * is one incompressible CM/phda9/zpaq wire plus tiny meta JSONs, so store or
 * brotli always wins. The xz/7z/arc/zpaq trials, the layered outer predictor,
 * and the native tar.br / 7z whole-tree benches only add wall (measured
 * 8.3 s -> 3.6 s wrapping a 1.9 MB wire, identical bytes); brotli/zstd/gzip
 * stay on for the meta tail. Not applied in forced multifile mode, whose
 * passthrough members can be arbitrary (xz-friendly) payloads.
 *
 * Env mutations are stacked and must be undone via
 * {@see fractal_zip_general_text_restore_wire_only_outer_env()} at zip_folder
 * finally — otherwise later lifestyle cases permanently lose native brotli/7z
 * compare (dense forests after CSV phda9).
 */
function fractal_zip_general_text_apply_wire_only_outer_env(): void
{
	if (!isset($GLOBALS['fractal_zip_gt_wire_only_env_stack'])
		|| !is_array($GLOBALS['fractal_zip_gt_wire_only_env_stack'])) {
		$GLOBALS['fractal_zip_gt_wire_only_env_stack'] = array();
	}
	$keys = array(
		'FRACTAL_ZIP_SKIP_XZ' => '1',
		'FRACTAL_ZIP_SKIP_7Z' => '1',
		'FRACTAL_ZIP_SKIP_ZPAQ' => '1',
		'FRACTAL_ZIP_SKIP_ARC' => '1',
		'FRACTAL_ZIP_OUTER_PREDICT' => '0',
		'FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_COMPARE' => '0',
		'FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE' => '0',
	);
	$saved = array();
	foreach ($keys as $k => $v) {
		$e = getenv($k);
		$saved[$k] = ($e === false) ? false : (string) $e;
		if ($e === false || trim((string) $e) === '') {
			putenv($k . '=' . $v);
		}
	}
	$GLOBALS['fractal_zip_gt_wire_only_env_stack'][] = $saved;
}

/** Undo {@see fractal_zip_general_text_apply_wire_only_outer_env()} (LIFO). */
function fractal_zip_general_text_restore_wire_only_outer_env(): void
{
	if (!isset($GLOBALS['fractal_zip_gt_wire_only_env_stack'])
		|| !is_array($GLOBALS['fractal_zip_gt_wire_only_env_stack'])
		|| $GLOBALS['fractal_zip_gt_wire_only_env_stack'] === array()) {
		return;
	}
	$saved = array_pop($GLOBALS['fractal_zip_gt_wire_only_env_stack']);
	if (!is_array($saved)) {
		return;
	}
	foreach ($saved as $k => $old) {
		if ($old === false) {
			putenv((string) $k);
		} else {
			putenv((string) $k . '=' . (string) $old);
		}
	}
}

function fractal_zip_general_text_apply_default_env(string $contentProfile): void
{
	fractal_zip_general_text_apply_speed_env();
	$set = static function (string $k, string $v): void {
		$e = getenv($k);
		if ($e === false || trim((string) $e) === '') {
			putenv($k . '=' . $v);
		}
	};
	$set('FRACTAL_ZIP_TEXT_INNER', '1');
	$set('FRACTAL_ZIP_TEXT_INNER_FORMAT', 'phda9_xml');
	$set('FRACTAL_ZIP_TEXT_INNER_MONO', '1');
	$set('FRACTAL_ZIP_TEXT_INNER_LAYOUT', 'per_page');
	$set('FRACTAL_ZIP_TEXT_INNER_STACK', 'none');
	$set('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM', '1');
	// Tokenized fast inner for general-text (phda9 whole-file is unreliable here).
	$set('FRACTAL_ZIP_PHDA9_GENERAL_FAST', '1');
	$set('FRACTAL_ZIP_PHDA9_DAEMON', '1');
	$set('FRACTAL_ZIP_PHDA9_FAST_PROBE', '0');
	$set('FRACTAL_ZIP_PHDA9_DICT_INLINE', '0');
	// Daemon still available if FAST is overridden to 0.
	$set('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS', '1');
	$dict4096 = __DIR__ . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
	$dictPlain = __DIR__ . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
	if (is_file($dict4096)) {
		$set('FRACTAL_ZIP_PAQ_PHDA9_DICT', $dict4096);
	} elseif (is_file($dictPlain)) {
		$set('FRACTAL_ZIP_PAQ_PHDA9_DICT', $dictPlain);
	}
	// No preprocess for general text, including markup: wiki_lom targets MediaWiki
	// dumps (dedicated enwik flows opt in explicitly); on generic HTML it broke the
	// lens-split restore and cost bytes (39.1 KB vs 28.5 KB on phplangref HTML).
	$set('FRACTAL_ZIP_TEXT_INNER_PREPROCESS', 'none');
}

/**
 * @return array{profile: string, row: array<string, mixed>}|null
 */
function fractal_zip_general_text_identify_member(string $relPath, string $bytes): ?array
{
	if ($bytes === '') {
		return null;
	}
	$lower = strtolower(str_replace('\\', '/', $relPath));
	foreach (array('.fzsxsd', '.fzsd', '.fzsx', '.fz', '.arc', '.zpaq', '.7z', '.pdf', '.jpg', '.jpeg', '.jpe', '.png', '.gif', '.webp', '.bmp') as $ext) {
		if (str_ends_with($lower, $ext)) {
			return null;
		}
	}
	$row = fractal_zip_identify_for_policy($relPath, $bytes);
	$profile = (string) $row['content_profile'];
	if (!fractal_zip_content_format_is_textish_profile($profile)) {
		return null;
	}
	if ($profile === 'text_plain' && fractal_zip_general_text_looks_delimiter_structured($bytes)) {
		return null;
	}
	if ($profile === 'text_plain' && fractal_zip_general_text_looks_url_list($bytes)) {
		// URL-per-line lists pay a fatal XML-wrapper tax in text-inner and compress
		// better as a raw CM FZpq wire (mcm/lpaq). See fractal_zip_binary_cm.php.
		return null;
	}
	if (fractal_zip_general_text_looks_hyper_redundant($bytes)) {
		return null;
	}
	return array('profile' => $profile, 'row' => $row);
}

/**
 * Hyper-repetitive text (synthetic/fractal corpora, giant run-length logs) is where
 * the native fractal substring/run-grammar lanes beat the general-text tier by 3×+
 * on bytes at a fraction of the wall — and bytes-first phda9 would burn minutes for
 * a worse result. Cheap deflate probe on a head sample; natural prose lands at
 * ratio ≈ 0.30–0.40, markup ≈ 0.10–0.15, synthetic repetition < 0.05.
 */
function fractal_zip_general_text_looks_hyper_redundant(string $bytes): bool
{
	$sample = strlen($bytes) > 524288 ? substr($bytes, 0, 524288) : $bytes;
	if (strlen($sample) < 4096) {
		return false;
	}
	$z = gzdeflate($sample, 6);
	if ($z === false) {
		return false;
	}
	$max = getenv('FRACTAL_ZIP_GENERAL_TEXT_MIN_DEFLATE_RATIO');
	$threshold = ($max !== false && trim((string) $max) !== '') ? (float) $max : 0.05;
	return strlen($z) < (int) (strlen($sample) * $threshold);
}

/**
 * URL-per-line corpora (Canterbury urls.10K): high density of http(s):// lines with
 * short average line lengths. The general-text XML wrapper balloons the wire;
 * raw mcm/lpaq on the plain beats zpaq by ~10–12 KB (see fractal_zip_binary_cm).
 */
function fractal_zip_general_text_looks_url_list(string $bytes): bool
{
	$sample = substr($bytes, 0, min(65536, strlen($bytes)));
	if (strlen($sample) < 4096) {
		return false;
	}
	$lines = preg_split('/\r\n|\n|\r/', $sample);
	if (!is_array($lines) || count($lines) < 40) {
		return false;
	}
	array_pop($lines);
	$urlish = 0;
	$total = 0;
	$sumLen = 0;
	foreach ($lines as $l) {
		if ($l === '') {
			continue;
		}
		$total++;
		$sumLen += strlen($l);
		if (preg_match('#https?://#i', $l) === 1) {
			$urlish++;
		}
	}
	if ($total < 40) {
		return false;
	}
	$avg = $sumLen / $total;
	return ($urlish / $total) >= 0.70 && $avg <= 200.0;
}

/**
 * CSV/TSV-style tabular data identifies as text_plain but compresses better through
 * the record-transpose / typed fractal-inner lanes than through the prose tier —
 * and the general-text virtual folder REPLACES that tournament rather than
 * competing in it. Uniform per-line delimiter counts across a sample ⇒ structured.
 */
function fractal_zip_general_text_looks_delimiter_structured(string $bytes): bool
{
	$head = substr($bytes, 0, 16384);
	$lines = preg_split('/\r\n|\n|\r/', $head);
	if (!is_array($lines)) {
		return false;
	}
	// Drop the last (possibly truncated) sample line.
	array_pop($lines);
	$lines = array_slice(array_values(array_filter($lines, static fn ($l) => $l !== '')), 0, 50);
	if (count($lines) < 8) {
		return false;
	}
	foreach (array(',', "\t", ';', '|') as $delim) {
		$counts = array();
		foreach ($lines as $l) {
			$counts[] = substr_count($l, $delim);
		}
		$freq = array_count_values($counts);
		arsort($freq);
		$mode = (int) array_key_first($freq);
		if ($mode < 2) {
			continue;
		}
		$match = 0;
		foreach ($counts as $c) {
			if ($c === $mode) {
				$match++;
			}
		}
		if ($match >= (int) ceil(count($lines) * 0.9)) {
			return true;
		}
	}
	return false;
}

function fractal_zip_general_text_is_mediawiki_blob(string $bytes): bool
{
	return stripos($bytes, '<mediawiki') !== false && stripos($bytes, '<page') !== false;
}

function fractal_zip_general_text_strip_html_noise(string $html): string
{
	$out = preg_replace('/<script\b[^>]*>.*?<\/script>/is', '', $html);
	if (!is_string($out)) {
		$out = $html;
	}
	$out2 = preg_replace('/<style\b[^>]*>.*?<\/style>/is', '', $out);
	return is_string($out2) ? $out2 : $out;
}

/**
 * @return list<array{title: string, text: string}>
 */
function fractal_zip_general_text_extract_preserve_text_blocks(string $blob, bool $trimBody = true): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
	$marker = FRACTAL_ZIP_ENWIK_TEXT_MARKER;
	$openLen = strlen($marker);
	$blocks = array();
	$pos = 0;
	$n = strlen($blob);
	$idx = 0;
	while ($pos < $n) {
		$i = stripos($blob, $marker, $pos);
		if ($i === false) {
			break;
		}
		$pos = $i + $openLen;
		$close = stripos($blob, '</text>', $pos);
		if ($close === false) {
			$body = substr($blob, $pos);
			$pos = $n;
		} else {
			$body = substr($blob, $pos, $close - $pos);
			$pos = $close + 7;
		}
		if ($trimBody) {
			$body = trim($body);
		}
		if (strlen($body) >= ($trimBody ? fractal_zip_general_text_min_block_chars() : 1)) {
			$blocks[] = array('title' => 'text_' . $idx, 'text' => $body);
			$idx++;
		}
	}
	return $blocks;
}

/**
 * @return list<array{title: string, text: string}>
 */
function fractal_zip_general_text_extract_html_tag_blocks(string $html): array
{
	$html = fractal_zip_general_text_strip_html_noise($html);
	$tags = array('p', 'div', 'li', 'article', 'section', 'pre', 'blockquote', 'td', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6');
	$minChars = fractal_zip_general_text_min_block_chars();
	$blocks = array();
	$seen = array();
	foreach ($tags as $tag) {
		$pattern = '/<' . preg_quote($tag, '/') . '\b[^>]*>(.*?)<\/' . preg_quote($tag, '/') . '\s*>/is';
		if (preg_match_all($pattern, $html, $m) < 1 || empty($m[1])) {
			continue;
		}
		foreach ($m[1] as $i => $innerRaw) {
			$inner = html_entity_decode(strip_tags((string) $innerRaw), ENT_QUOTES | ENT_HTML5, 'UTF-8');
			$inner = trim(preg_replace('/[ \t\x0B\f\r]+/u', ' ', $inner) ?? $inner);
			if (strlen($inner) < $minChars) {
				continue;
			}
			$key = substr(sha1($inner, true), 0, 8);
			if (isset($seen[$key])) {
				continue;
			}
			$seen[$key] = true;
			$blocks[] = array(
				'title' => $tag . '_' . count($blocks),
				'text' => $inner,
			);
		}
	}
	return $blocks;
}

/**
 * @return list<array{title: string, text: string}>
 */
function fractal_zip_general_text_extract_plain_paragraphs(string $text): array
{
	$parts = preg_split('/\R{2,}/', str_replace("\r\n", "\n", $text)) ?: array($text);
	$blocks = array();
	$minChars = fractal_zip_general_text_min_block_chars();
	foreach ($parts as $i => $part) {
		$part = trim((string) $part);
		if (strlen($part) < $minChars) {
			continue;
		}
		$blocks[] = array('title' => 'para_' . $i, 'text' => $part);
	}
	return $blocks;
}

/**
 * @return list<array{title: string, text: string}>
 */
function fractal_zip_general_text_split_blocks(string $bytes, string $contentProfile): array
{
	$preserve = fractal_zip_general_text_extract_preserve_text_blocks($bytes);
	if ($preserve !== array()) {
		return $preserve;
	}
	if (fractal_zip_general_text_whole_file_block_wanted()
		&& strlen($bytes) >= fractal_zip_general_text_min_file_bytes()) {
		$GLOBALS['fractal_zip_general_text_lossless_whole'] = true;
		return array(array('title' => 'document', 'text' => $bytes));
	}
	if ($contentProfile === 'text_markup' || stripos($bytes, '<html') !== false || stripos($bytes, '<body') !== false
		|| preg_match('/<(p|div|article|section|text)\b/i', $bytes) === 1) {
		$htmlBlocks = fractal_zip_general_text_extract_html_tag_blocks($bytes);
		if ($htmlBlocks !== array()) {
			return $htmlBlocks;
		}
	}
	$plain = fractal_zip_general_text_extract_plain_paragraphs($bytes);
	if ($plain !== array()) {
		return $plain;
	}
	$trim = trim($bytes);
	if (strlen($trim) >= fractal_zip_general_text_min_block_chars()) {
		return array(array('title' => 'document', 'text' => $trim));
	}
	return array();
}

function fractal_zip_general_text_escape_xml(string $s): string
{
	return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
}

function fractal_zip_general_text_wrap_block_page(string $title, string $textBody): string
{
	$titleEsc = fractal_zip_general_text_escape_xml($title);
	return '<page><title>' . $titleEsc . '</title>' . FRACTAL_ZIP_ENWIK_TEXT_MARKER
		. $textBody . '</text></page>';
}

/**
 * Build synthetic fztext blob + page refs for TEXT_INNER.
 *
 * @param list<array{title: string, text: string}> $blocks
 * @return array{header: string, footer: string, blob: string, pages: list<array{title: string, origIndex: int, start: int, len: int}>}
 */
function fractal_zip_general_text_build_synthetic_blob(array $blocks): array
{
	$header = '<?xml version="1.0" encoding="UTF-8"?>' . FRACTAL_ZIP_GENERAL_TEXT_ROOT;
	$footer = FRACTAL_ZIP_GENERAL_TEXT_ROOT_CLOSE;
	$pages = array();
	$body = '';
	foreach ($blocks as $i => $block) {
		$pageXml = fractal_zip_general_text_wrap_block_page((string) $block['title'], (string) $block['text']);
		$start = strlen($header) + strlen($body);
		$body .= $pageXml;
		$pages[] = array(
			'title' => (string) $block['title'],
			'origIndex' => $i,
			'start' => $start,
			'len' => strlen($pageXml),
		);
	}
	$blob = $header . $body . $footer;
	return array(
		'header' => $header,
		'footer' => $footer,
		'blob' => $blob,
		'pages' => $pages,
	);
}

/**
 * @return array<string, mixed>|null ctx compatible with enwik FZEP append/restore
 */
function fractal_zip_general_text_try_prepare_virtual_folder(string $dir): ?array
{
	if (!fractal_zip_general_text_inner_enabled()) {
		return null;
	}
	// Ultra: keep fractal floors on the real tree (no GT virtual folder).
	if (getenv('FRACTAL_ZIP_ULTRA') === '1') {
		return null;
	}
	// Explicit LIFESTYLE=0 historically skipped GT so fractal floors stay on the
	// raw tree. Bytes-first / fair-full set GENERAL_TEXT_INNER=on — honor that.
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if (is_string($lifeEnv) && in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true)) {
		$gtExplicit = getenv('FRACTAL_ZIP_GENERAL_TEXT_INNER');
		$gtForcedOn = is_string($gtExplicit)
			&& in_array(strtolower(trim($gtExplicit)), array('1', 'on', 'true', 'yes'), true);
		if (!$gtForcedOn) {
			return null;
		}
	}
	$iterRoot = realpath($dir);
	if ($iterRoot === false || !is_dir($iterRoot)) {
		return null;
	}
	$candidates = array();
	$allFiles = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($iterRoot, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$rp = $fi->getRealPath();
		$path = $rp !== false ? $rp : $fi->getPathname();
		$rel = ltrim(str_replace('\\', '/', substr($path, strlen($iterRoot))), '/');
		$allFiles[$rel !== '' ? $rel : basename($path)] = $path;
		$sz = $fi->getSize();
		if ($sz === false || $sz < fractal_zip_general_text_scan_floor_bytes()) {
			continue;
		}
		$bytes = file_get_contents($path);
		if (!is_string($bytes) || $bytes === '') {
			continue;
		}
		if (fractal_zip_general_text_is_mediawiki_blob($bytes)) {
			return null;
		}
		$id = fractal_zip_general_text_identify_member($rel !== '' ? $rel : basename($path), $bytes);
		if ($id === null) {
			continue;
		}
		$profile = (string) $id['profile'];
		if ((int) $sz < fractal_zip_general_text_min_file_bytes_for_profile($profile)) {
			continue;
		}
		$candidates[] = array(
			'path' => $path,
			'rel' => $rel,
			'bytes' => $bytes,
			'profile' => $profile,
			'size' => (int) $sz,
		);
	}
	if ($candidates === array()) {
		return null;
	}
	// The virtual folder replaces $dir entirely, so any sibling file that is
	// not part of the synthesized text stream would silently vanish from the
	// archive (data loss). Auto mode and lifestyle/default only accept a pure
	// single-file text corpus (or one candidate + tiny README sidecars);
	// full multifile passthrough is bytes-first / opt-in.
	$passthrough = $allFiles;
	// Sidecar README only for mid-size prose under lifestyle (test_files195).
	// Fair-full (LIFESTYLE=0 + MULTIFILE default off): nearSingle GT wraps
	// inflate wire vs bare Arc/zpaq (test_files187/192) — leave those to
	// normal zip_folder + native ratchet.
	$nearSingle = count($candidates) === 1
		&& count($allFiles) > 1
		&& (int) ($candidates[0]['size'] ?? 0) < 262144
		&& fractal_zip_general_text_tiny_sidecar_only($allFiles, (string) $candidates[0]['path'])
		&& class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled();
	if (count($candidates) !== 1 || count($allFiles) !== 1) {
		if (!$nearSingle
			&& (fractal_zip_general_text_inner_mode() === 'auto'
				|| !fractal_zip_general_text_multifile_allowed())) {
			return null;
		}
		usort($candidates, static fn (array $a, array $b): int => ((int) ($b['size'] ?? 0) <=> (int) ($a['size'] ?? 0)));
	}
	$pick = $candidates[0];
	$sourcePath = (string) $pick['path'];
	$blob = (string) $pick['bytes'];
	$profile = (string) $pick['profile'];
	// Lifestyle: skip GT on large prose EXCEPT 8–12 MiB text_plain (dickens) —
	// store wire reclaims ~400 KiB vs Arc; soft midsize pin covers ~26 s.
	// ≥12 MiB (webster) and non-prose stay native.
	if (class_exists('fractal_zip', false) && method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()) {
		$gtSz = (int) ($pick['size'] ?? 0);
		$gtProf = (string) ($pick['profile'] ?? '');
		if ($gtSz >= 12 * 1024 * 1024
			|| ($gtSz >= 8 * 1024 * 1024 && $gtProf !== 'text_plain')) {
			return null;
		}
	}
	fractal_zip_general_text_apply_speed_env();
	if (count($allFiles) === 1) {
		fractal_zip_general_text_apply_wire_only_outer_env();
	}
	// Scale CM segments with payload under lifestyle only (≥8 MiB → seg4 for
	// wall). Bytes-first / fair-full keep whole-stream DRT/mcm (nci soles vs zpaq).
	$plainHint = (int) ($pick['size'] ?? strlen($blob));
	// Lifestyle: always skip DRT — mcm alone soles soft on CSV; DRT was ~+5 s
	// each on 167/174 for ~1.7 KiB. Monster zstd reclaim covers that headroom.
	// Always rewrite so a prior case cannot leave DRT enabled.
	$lifeEnv = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	$lifeExplicitOff = is_string($lifeEnv)
		&& in_array(strtolower(trim($lifeEnv)), array('0', 'off', 'false', 'no'), true);
	$ultra = getenv('FRACTAL_ZIP_ULTRA') === '1';
	if (!$ultra && !$lifeExplicitOff && $plainHint > 0) {
		putenv('FRACTAL_ZIP_TOKENIZED_DRT=0');
	}
	$segs = 0;
	if ($plainHint >= 8 * 1024 * 1024
		&& class_exists('fractal_zip', false)
		&& method_exists('fractal_zip', 'lifestyle_speed_profile_enabled')
		&& fractal_zip::lifestyle_speed_profile_enabled()) {
		$segs = 4;
	}
	if ($segs > 0) {
		$eSeg = getenv('FRACTAL_ZIP_TOKENIZED_CM_SEGMENTS');
		if ($eSeg === false || trim((string) $eSeg) === '') {
			putenv('FRACTAL_ZIP_TOKENIZED_CM_SEGMENTS=' . (string) $segs);
		}
	}
	// Default: one lossless whole-file block (bit-exact restore, minimal <page> tax).
	// Block splitting is opt-in (FRACTAL_ZIP_GENERAL_TEXT_SPLIT_BLOCKS=1) and only
	// possible for single-file folders — multifile needs exact passthrough restore.
	if (count($allFiles) > 1 || !fractal_zip_general_text_split_blocks_wanted()) {
		$GLOBALS['fractal_zip_general_text_lossless_whole'] = true;
		$blocks = array(array('title' => 'document', 'text' => $blob));
	} else {
		$blocks = fractal_zip_general_text_split_blocks($blob, $profile);
	}
	if ($blocks === array()) {
		fractal_zip_general_text_restore_wire_only_outer_env();
		return null;
	}
	fractal_zip_general_text_apply_default_env($profile);
	if (!fractal_zip_enwik_text_inner_enabled()) {
		return null;
	}
	$GLOBALS['fractal_zip_general_text_profile'] = $profile;
	$GLOBALS['fractal_zip_general_text_lossless_whole'] = !empty($GLOBALS['fractal_zip_general_text_lossless_whole']);
	$synth = fractal_zip_general_text_build_synthetic_blob($blocks);
	$outputRel = (string) ($pick['rel'] ?? '');
	if ($outputRel === '') {
		$outputRel = basename($sourcePath);
	}
	$outputFile = $outputRel;
	$virtualDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fztext_' . substr(md5($iterRoot . "\0" . $outputFile . "\0" . (string) microtime(true)), 0, 16);
	$layoutId = trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT') ?: 'per_page'));
	$sorted = $synth['pages'];
	foreach ($sorted as $si => &$row) {
		$row['sortedIndex'] = $si;
	}
	unset($row);
	try {
		if (!is_dir($virtualDir) && !@mkdir($virtualDir, 0755, true) && !is_dir($virtualDir)) {
			return null;
		}
		$ctx = enwik_build_text_inner_virtual_folder_from_refs(
			$virtualDir,
			$sorted,
			$synth['blob'],
			$synth['header'],
			$synth['footer'],
			$outputFile,
			$iterRoot,
			$layoutId
		);
	} catch (RuntimeException $e) {
		// Ultra / odd HTML: fall back to normal zip_folder rather than aborting the bench.
		$msg = $e->getMessage();
		if (stripos($msg, 'write failed') !== false || stripos($msg, 'compress failed') !== false) {
			if (is_dir($virtualDir)) {
				// best-effort cleanup left to tmp cleaner
			}
			return null;
		}
		$wall = fractal_zip_general_text_inner_wall_sec();
		if ($wall > 0 && !fractal_zip_general_text_whole_file_block_wanted()) {
			throw new RuntimeException(
				'general text-inner: phda9 job failed or exceeded FRACTAL_ZIP_GENERAL_TEXT_INNER_WALL_SEC=' . $wall
				. 's (no silent codec fallback). Raise the wall, unset it, or use FRACTAL_ZIP_PHDA9_GENERAL_FAST=1 '
				. 'for the tokenized_zpaq fast inner. Cause: ' . $msg,
				0,
				$e
			);
		}
		throw $e;
	}
	$ctx['sourcePath'] = $sourcePath;
	$ctx['generalTextInner'] = true;
	$ctx['generalTextProfile'] = $profile;
	$ctx['generalTextBlockCount'] = count($blocks);
	$ctx['passthroughRelPaths'] = array();
	// Forced mode: carry non-candidate siblings into the virtual folder so
	// they stay in the archive as ordinary members (not FZEP inner scaffolding).
	foreach ($passthrough as $rel => $srcPath) {
		if ($srcPath === $sourcePath) {
			continue;
		}
		$dst = $virtualDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $rel);
		$parent = dirname($dst);
		if (!is_dir($parent) && !@mkdir($parent, 0755, true) && !is_dir($parent)) {
			continue;
		}
		if (@copy($srcPath, $dst)) {
			$ctx['passthroughRelPaths'][] = (string) $rel;
		}
	}
	if (PHP_SAPI === 'cli' && is_resource(STDERR)) {
		$pre = (string) ($ctx['textInnerPreprocess'] ?? 'none');
		$fmt = (string) ($ctx['textInnerMemberFormat'] ?? 'dual');
		$passthroughCount = count($ctx['passthroughRelPaths'] ?? array());
		@fwrite(STDERR, '[general-text] ' . $outputFile . ' profile=' . $profile
			. ' blocks=' . count($blocks) . ' format=' . $fmt . ' preprocess=' . $pre
			. ' members=' . count($ctx['memberRelPaths'] ?? array())
			. ($passthroughCount > 0 ? ' passthrough=' . $passthroughCount : '') . "\n");
	}
	return $ctx;
}
