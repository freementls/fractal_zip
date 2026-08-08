<?php
declare(strict_types=1);

/**
 * Decode “leaf” container payloads: native codec wires (FZPA/FZpq/zpaq/phda9), fztext, plain text —
 * without requiring a full FZB/FZC fractal wrapper.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';

/** @return list<string> */
function fractal_zip_container_leaf_structured_magics(): array
{
	return array(
		'FZWS', 'FZTA', 'FZS1', 'FZBD', 'FZCD', 'FZBM', 'FZBF', 'FZB6', 'FZB5', 'FZB4',
		'FZC3', 'FZC2', 'FZC1', 'FZHM', 'FZEP',
	);
}

function fractal_zip_container_leaf_looks_structured(string $bytes): bool
{
	if ($bytes === '') {
		return false;
	}
	$m4 = strlen($bytes) >= 4 ? substr($bytes, 0, 4) : $bytes;
	if (in_array($m4, fractal_zip_container_leaf_structured_magics(), true)) {
		return true;
	}
	if (str_starts_with($bytes, FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC)) {
		return false;
	}
	if (function_exists('fractal_zip_paq_wire_magic')) {
		$pq = fractal_zip_paq_wire_magic();
		if ($pq !== '' && str_starts_with($bytes, $pq)) {
			return false;
		}
	}
	if (strlen($bytes) >= 4 && substr($bytes, 0, 4) === '7kSt') {
		return false;
	}
	if (strlen($bytes) >= 2 && $bytes[0] === '7' && $bytes[1] === 'z') {
		return false;
	}
	if (strlen($bytes) >= 4 && substr($bytes, 0, 4) === 'FZ7F') {
		return false;
	}
	if (strlen($bytes) >= 3 && substr($bytes, 0, 3) === 'FZ7') {
		return false;
	}
	if (strlen($bytes) >= 4 && ($m4 === 'FZLB' || $m4 === 'FZFA' || $m4 === 'FZWR')) {
		return false;
	}
	if ($m4 === 'a:' || $m4 === 'O:' || $m4 === 's:' || $m4 === 'i:') {
		return true;
	}
	return false;
}

function fractal_zip_container_leaf_infer_member_name(?string $containerPath): string
{
	if (is_string($containerPath) && $containerPath !== '') {
		$base = basename(str_replace('\\', '/', $containerPath));
		if (preg_match('/^(.+)\.(fz|fzc|fractalzip)$/i', $base, $m)) {
			$candidate = (string) $m[1];
			if ($candidate !== '' && $candidate !== '.' && $candidate !== '..') {
				if (str_contains($candidate, '.')) {
					return $candidate;
				}
				return $candidate . '.txt';
			}
		}
		if ($base !== '' && $base !== '.' && $base !== '..') {
			return $base;
		}
	}
	return 'extracted.txt';
}

/**
 * Unwrap native codec / archive bytes to restored plain (or fztext) when possible.
 */
function fractal_zip_container_leaf_unwrap_to_plain(object $host, string $contents): ?string
{
	$contents = (string) $contents;
	if ($contents === '') {
		return '';
	}
	if (str_starts_with($contents, FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC)) {
		fractal_zip_tokenized_zpaq_load_vocab_from_fzpa_wire($contents);
		$plain = fractal_zip_text_paq_wire_undo($contents);
		if (function_exists('fractal_zip_phda9_tok_stream_has_word_escapes')
			&& fractal_zip_phda9_tok_stream_has_word_escapes($plain)) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_tokenize.php';
			$plain = fractal_zip_tokenized_zpaq_detokenize_plain($plain, array());
		}
		return $plain;
	}
	if (function_exists('fractal_zip_paq_unwrap_wire')) {
		$un = fractal_zip_paq_unwrap_wire($contents);
		if (is_array($un) && isset($un['tool'], $un['payload'])) {
			$toolId = (string) $un['tool'];
			$exe = fractal_zip_paq_discover_executable($toolId);
			if ($exe !== null) {
				$sandbox = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_leaf_' . substr(md5($contents), 0, 12);
				@mkdir($sandbox, 0700, true);
				$arcPath = $sandbox . DIRECTORY_SEPARATOR . 'in.paq';
				$outPath = $sandbox . DIRECTORY_SEPARATOR . 'out.bin';
				if (@file_put_contents($arcPath, (string) $un['payload']) !== false
					&& fractal_zip_paq_decompress_to_file($toolId, $exe, $arcPath, $outPath)) {
					$plain = @file_get_contents($outPath);
					if (is_dir($sandbox)) {
						foreach (glob($sandbox . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
							if (is_file($f)) {
								@unlink($f);
							}
						}
						@rmdir($sandbox);
					}
					if (is_string($plain) && $plain !== '') {
						return $plain;
					}
				}
				if (is_dir($sandbox)) {
					foreach (glob($sandbox . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
						if (is_file($f)) {
							@unlink($f);
						}
					}
					@rmdir($sandbox);
				}
			}
		}
	}
	if (method_exists($host, 'zpaq_native_archive_stream_magic_p')
		&& $host->zpaq_native_archive_stream_magic_p($contents)) {
		if (method_exists($host, 'unpack_raw_zpaq_archive_bytes_to_inner_string')) {
			$inner = $host->unpack_raw_zpaq_archive_bytes_to_inner_string($contents);
			if (is_string($inner) && $inner !== '') {
				return fractal_zip_container_leaf_unwrap_to_plain($host, $inner);
			}
		}
		return null;
	}
	return null;
}

function fractal_zip_container_leaf_restore_text_inner(string $plain): string
{
	if ($plain === '') {
		return $plain;
	}
	if (strpos($plain, FRACTAL_ZIP_GENERAL_TEXT_ROOT) !== false) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_general_text.php';
		return fractal_zip_general_text_restore_from_synthetic_blob($plain, 'text_plain', array());
	}
	if (function_exists('fractal_zip_phda9_tok_stream_has_word_escapes')
		&& fractal_zip_phda9_tok_stream_has_word_escapes($plain)) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_tokenize.php';
		return fractal_zip_tokenized_zpaq_detokenize_plain($plain, array());
	}
	return $plain;
}

/**
 * True when bytes are plausibly a plain-text leaf member (not a compressed outer
 * stream like xz/zstd/brotli/gzip that adaptive_decompress must handle instead).
 */
function fractal_zip_container_leaf_bytes_look_texty(string $bytes): bool
{
	if ($bytes === '') {
		return false;
	}
	foreach (array("\xfd7zXZ\x00", "\x28\xb5\x2f\xfd", "\x1f\x8b", 'BZh', "PK\x03\x04", "7z\xbc\xaf") as $magic) {
		if (str_starts_with($bytes, $magic)) {
			return false;
		}
	}
	$head = substr($bytes, 0, 8192);
	if (strpos($head, "\x00") !== false) {
		return false;
	}
	$printable = preg_match_all('/[\x09\x0a\x0d\x20-\x7e\x80-\xff]/', $head);
	return $printable !== false && $printable >= (int) (strlen($head) * 0.95);
}

/**
 * @return array{0: array<string,string>, 1: string, 2: array<string,string>}|null decode_container_payload triple
 */
function fractal_zip_container_leaf_decode_triple(object $host, string $contents, ?string $containerPath = null): ?array
{
	$contents = (string) $contents;
	if ($contents === '') {
		return null;
	}
	if (fractal_zip_container_leaf_looks_structured($contents)) {
		return null;
	}
	$plain = fractal_zip_container_leaf_unwrap_to_plain($host, $contents);
	if ($plain === null) {
		if (method_exists($host, 'zpaq_native_archive_stream_magic_p')
			&& $host->zpaq_native_archive_stream_magic_p($contents)) {
			return null;
		}
		// Identity fallback only for actual text: compressed outers (xz/zstd/brotli/…)
		// must fall through to adaptive_decompress, not be stored as a garbage member.
		if (!fractal_zip_container_leaf_bytes_look_texty($contents)) {
			return null;
		}
		$plain = $contents;
	}
	$plain = fractal_zip_container_leaf_restore_text_inner($plain);
	if ($plain === '' || fractal_zip_container_leaf_looks_structured($plain)) {
		return null;
	}
	$name = fractal_zip_container_leaf_infer_member_name($containerPath);
	if (!method_exists($host, 'escape_literal_for_storage')) {
		return array(array($name => $plain), '', array());
	}
	return array(array($name => $host->escape_literal_for_storage($plain)), '', array());
}

/**
 * Extract single-member native codec passthrough directly to $destRootDir (open_container fast path).
 */
function fractal_zip_container_leaf_try_extract_passthrough(
	object $host,
	string $fullContents,
	string $destRootDir,
	bool $debug,
	?string $containerPath = null
): bool {
	$triple = fractal_zip_container_leaf_decode_triple($host, $fullContents, $containerPath);
	if ($triple === null) {
		$arc = function_exists('fractal_zip_native_zpaq_arc_from_container_bytes')
			? fractal_zip_native_zpaq_arc_from_container_bytes($fullContents)
			: null;
		if (is_string($arc) && $arc !== '' && method_exists($host, 'unpack_raw_zpaq_archive_bytes_to_inner_string')) {
			$inner = $host->unpack_raw_zpaq_archive_bytes_to_inner_string($arc);
			if (is_string($inner) && $inner !== '') {
				$triple = fractal_zip_container_leaf_decode_triple($host, $inner, $containerPath);
			}
		}
	}
	if ($triple === null) {
		return false;
	}
	if (!method_exists($host, 'write_decoded_container_members_to_disk')) {
		return false;
	}
	$host->write_decoded_container_members_to_disk($destRootDir, $triple, $debug, false);
	return true;
}
