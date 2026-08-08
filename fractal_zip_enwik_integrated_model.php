<?php
declare(strict_types=1);

/**
 * FZEP tier-D: optional statistical sidecar on sorted-order text (lab / 384p gate only).
 *
 * Env:
 * - FRACTAL_ZIP_ENWIK_STAT_SIDECAR — unset=off; 1=build gzipped word-frequency sidecar in FZEP v6
 * - FRACTAL_ZIP_ENWIK_STAT_SIDECAR_MAX_WORDS — cap (default 65536)
 */

const FRACTAL_ZIP_ENWIK_VERSION_STAT_SIDECAR = 6;
/** FZEP v7: v6 + inner-fold blob (FZPM/FZDI) once per archive — fractal+outer or gzip sealed (FZIF). */
const FRACTAL_ZIP_ENWIK_VERSION_INNER_FOLD = 7;
const FRACTAL_ZIP_ENWIK_FZEP_FLAG_STAT_SIDECAR = 8;
const FRACTAL_ZIP_ENWIK_FZEP_FLAG_INNER_FOLD = 16;

function fractal_zip_enwik_stat_sidecar_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
}

function fractal_zip_enwik_stat_sidecar_max_words(): int
{
	$e = getenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR_MAX_WORDS');
	if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
		return 65536;
	}
	return max(1024, min(262144, (int) trim((string) $e)));
}

/**
 * Build reversible gzipped JSON sidecar: top word counts from sorted wire text blobs.
 *
 * @param list<string> $wireTextChunks
 */
function fractal_zip_enwik_build_stat_sidecar_blob(array $wireTextChunks): string
{
	$freq = array();
	$maxWords = fractal_zip_enwik_stat_sidecar_max_words();
	foreach ($wireTextChunks as $chunk) {
		if (!is_string($chunk) || $chunk === '') {
			continue;
		}
		if (preg_match_all('/[A-Za-z]{3,}/', $chunk, $m)) {
			foreach ($m[0] as $w) {
				$lw = strtolower((string) $w);
				if (!isset($freq[$lw])) {
					$freq[$lw] = 0;
				}
				$freq[$lw]++;
			}
		}
	}
	arsort($freq, SORT_NUMERIC);
	$top = array_slice($freq, 0, $maxWords, true);
	$json = json_encode(
		array('v' => 1, 'kind' => 'word_freq_sorted', 'words' => $top),
		JSON_UNESCAPED_UNICODE
	);
	if (!is_string($json)) {
		return '';
	}
	$gz = gzcompress($json, 9);
	return is_string($gz) ? $gz : '';
}

/**
 * Frozen WRT vocab from sorted-order text (tier D preprocess).
 *
 * @return list<string>
 */
function fractal_zip_enwik_stat_wrt_vocab_from_text(string $mineText, int $maxCodes = 200): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$freq = array();
	if (preg_match_all('/[A-Za-z]{3,}/', $mineText, $m)) {
		foreach ($m[0] as $w) {
			$lw = strtolower((string) $w);
			if (!isset($freq[$lw])) {
				$freq[$lw] = 0;
			}
			$freq[$lw]++;
		}
	}
	arsort($freq, SORT_NUMERIC);
	return array_slice(array_keys($freq), 0, max(1, min(254, $maxCodes)));
}

function fractal_zip_enwik_stat_preprocess_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_ENWIK_STAT_PREPROCESS');
	if ($e === false || trim((string) $e) === '') {
		return fractal_zip_enwik_stat_sidecar_enabled();
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
}

/** @return array<string, int>|null */
function fractal_zip_enwik_parse_stat_sidecar_blob(string $gzBlob): ?array
{
	if ($gzBlob === '') {
		return null;
	}
	$json = @gzuncompress($gzBlob);
	if (!is_string($json) || $json === '') {
		return null;
	}
	$dec = json_decode($json, true);
	if (!is_array($dec) || ($dec['kind'] ?? '') !== 'word_freq_sorted') {
		return null;
	}
	$words = $dec['words'] ?? null;
	return is_array($words) ? $words : null;
}
