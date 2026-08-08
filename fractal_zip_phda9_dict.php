<?php
declare(strict_types=1);

/**
 * phda9 external dictionary file format (read_me.txt).
 *
 * Env:
 *   FRACTAL_ZIP_PAQ_PHDA9_DICT — path to dictionary file for phda9 / phda9_no_lstm compress+decompress
 */

function fractal_zip_phda9_dict_max_bytes(): int
{
	return 1930550;
}

function fractal_zip_phda9_dict_max_words(): int
{
	return 188240;
}

function fractal_zip_phda9_dict_header_bytes(): string
{
	$lines = array('80000');
	for ($i = 0; $i < 12; $i++) {
		$lines[] = '';
	}
	return implode("\r\n", $lines) . "\r\n";
}

/**
 * @param list<string> $words
 * @return array{path: string, bytes: int, words: int, trimmed_words: int, trimmed_bytes: bool}
 */
function fractal_zip_phda9_dict_write_file(array $words, string $path): array
{
	$maxWords = fractal_zip_phda9_dict_max_words();
	$maxBytes = fractal_zip_phda9_dict_max_bytes();
	$header = fractal_zip_phda9_dict_header_bytes();
	$outWords = array();
	$trimmedWords = 0;
	$trimmedBytes = false;
	$bodyLen = strlen($header);
	foreach ($words as $w) {
		if (count($outWords) >= $maxWords) {
			$trimmedWords++;
			continue;
		}
		$w = trim((string) $w);
		if ($w === '' || $w === '80000') {
			continue;
		}
		$lineLen = strlen($w) + 2;
		if ($bodyLen + $lineLen > $maxBytes) {
			$trimmedBytes = true;
			break;
		}
		$outWords[] = $w;
		$bodyLen += $lineLen;
	}
	$body = $header;
	if ($outWords !== array()) {
		$body .= implode("\r\n", $outWords) . "\r\n";
	}
	$dir = dirname($path);
	if ($dir !== '' && $dir !== '.' && !is_dir($dir)) {
		if (!@mkdir($dir, 0755, true) && !is_dir($dir)) {
			throw new RuntimeException('phda9_dict: cannot create directory ' . $dir);
		}
	}
	if (file_put_contents($path, $body) === false) {
		throw new RuntimeException('phda9_dict: write failed: ' . $path);
	}
	return array(
		'path' => $path,
		'bytes' => strlen($body),
		'words' => count($outWords),
		'trimmed_words' => $trimmedWords,
		'trimmed_bytes' => $trimmedBytes,
	);
}

function fractal_zip_phda9_dict_path_from_env(): ?string
{
	$e = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	if ($e === false || trim((string) $e) === '') {
		return null;
	}
	$p = realpath(trim((string) $e));
	if ($p === false || !is_file($p)) {
		return null;
	}
	return $p;
}

/** @return array{ok: bool, reason: string} */
function fractal_zip_phda9_dict_validate_file(string $path): array
{
	if (!is_file($path)) {
		return array('ok' => false, 'reason' => 'missing');
	}
	$sz = filesize($path);
	if ($sz === false || $sz > fractal_zip_phda9_dict_max_bytes()) {
		return array('ok' => false, 'reason' => 'size');
	}
	$raw = (string) file_get_contents($path);
	if ($raw === '' || !str_starts_with($raw, "80000\r\n")) {
		return array('ok' => false, 'reason' => 'header');
	}
	return array('ok' => true, 'reason' => 'ok');
}

/**
 * @return list<string>
 */
function fractal_zip_phda9_dict_read_words(string $path): array
{
	$raw = (string) file_get_contents($path);
	if ($raw === '') {
		return array();
	}
	$lines = preg_split("/\r\n|\n|\r/", $raw);
	if (!is_array($lines)) {
		return array();
	}
	$out = array();
	$seen = array();
	foreach ($lines as $line) {
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
 * Deduped union preserving first-seen order.
 *
 * @param list<string> ...$lists
 * @return list<string>
 */
function fractal_zip_phda9_dict_merge_word_lists(array ...$lists): array
{
	$out = array();
	$seen = array();
	foreach ($lists as $list) {
		foreach ($list as $w) {
			$w = trim((string) $w);
			if ($w === '' || $w === '80000' || isset($seen[$w])) {
				continue;
			}
			$seen[$w] = true;
			$out[] = $w;
		}
	}
	return $out;
}

/**
 * Merge base dict with z_* tokens (prepend or append).
 *
 * @param list<string> $skelTokens skeleton tokens, best-first
 * @param 'prepend'|'append' $skelPlacement
 * @return array{path: string, bytes: int, words: int, skel_words: int, base_words: int, trimmed_words: int, trimmed_bytes: bool}
 */
function fractal_zip_phda9_dict_write_merged_consonant(
	string $baseDictPath,
	array $skelTokens,
	string $outPath,
	string $skelPlacement = 'prepend'
): array {
	$baseWords = is_file($baseDictPath) ? fractal_zip_phda9_dict_read_words($baseDictPath) : array();
	$skel = array();
	foreach ($skelTokens as $t) {
		$t = trim((string) $t);
		if ($t !== '' && str_starts_with($t, 'z_')) {
			$skel[] = $t;
		}
	}
	if ($skelPlacement === 'append') {
		$merged = fractal_zip_phda9_dict_merge_word_lists($baseWords, $skel);
	} else {
		$merged = fractal_zip_phda9_dict_merge_word_lists($skel, $baseWords);
	}
	$written = fractal_zip_phda9_dict_write_file($merged, $outPath);
	return array_merge($written, array(
		'skel_words' => count($skel),
		'base_words' => count($baseWords),
		'skel_placement' => $skelPlacement,
	));
}

/** Ship external phda9 dict inside FZEP inner-fold trailer (honest wire). */
function fractal_zip_phda9_dict_fold_enabled(): bool
{
	$v = getenv('FRACTAL_ZIP_PHDA9_DICT_FOLD');
	if ($v === false || trim((string) $v) === '') {
		return false;
	}
	return in_array(strtolower(trim((string) $v)), array('1', 'true', 'on', 'yes'), true);
}

/**
 * Compact FZPV vocab blob for inner-fold trailer (not CRLF phda9 dict file).
 *
 * @return array{payload: string, words: int, raw_dict_bytes: int}|null
 */
function fractal_zip_phda9_dict_build_fold_payload(?string $dictPath = null): ?array
{
	$dictPath = $dictPath ?? fractal_zip_phda9_dict_path_from_env();
	if ($dictPath === null) {
		return null;
	}
	$words = fractal_zip_phda9_dict_read_words($dictPath);
	if ($words === array()) {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_tokenize.php';
	$rawDictBytes = (int) (filesize($dictPath) ?: 0);
	return array(
		'payload' => fractal_zip_phda9_dict_inline_vocab_blob($words),
		'words' => count($words),
		'raw_dict_bytes' => $rawDictBytes,
	);
}

/** Restore phda9 CLI dict from sealed inner-fold trailer; returns temp dict path. */
function fractal_zip_phda9_dict_restore_from_trailer(string $trailerWire): ?string
{
	if ($trailerWire === '') {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_paq.php';
	$unpacked = fractal_zip_enwik_inner_fold_unpack_trailer($trailerWire);
	if ($unpacked === null) {
		return null;
	}
	if ((string) ($unpacked['preprocess'] ?? '') !== 'phda9_dict_fold') {
		return null;
	}
	$inner = (string) ($unpacked['inner_blob'] ?? '');
	if ($inner === '') {
		return null;
	}
	$words = fractal_zip_phda9_dict_inline_vocab_from_blob($inner);
	if ($words === array()) {
		return null;
	}
	$path = fractal_zip_parallel_paq_vocab_dict_path($words);
	if ($path === null) {
		return null;
	}
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $path);
	return $path;
}
