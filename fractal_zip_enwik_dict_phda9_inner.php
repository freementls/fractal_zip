<?php
declare(strict_types=1);

/**
 * phda9-scale static word vocabulary (~188k words) for inner-fold FZDI dict.
 *
 * Env:
 * - FRACTAL_ZIP_PHDA9_INNER_MAX_WORDS — cap (default 188240, phda9 dict limit)
 * - FRACTAL_ZIP_PHDA9_INNER_VOCAB_FILE — optional prebuilt newline word list
 * - FRACTAL_ZIP_PHDA9_INNER_MINE_PATH — override corpus for frozen vocab (default test_files109/enwik8)
 */

const FRACTAL_ZIP_PHDA9_INNER_DEFAULT_MAX_WORDS = 188240;

function fractal_zip_enwik_phda9_inner_max_words(): int
{
	$e = getenv('FRACTAL_ZIP_PHDA9_INNER_MAX_WORDS');
	if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
		return FRACTAL_ZIP_PHDA9_INNER_DEFAULT_MAX_WORDS;
	}
	return max(4096, min(FRACTAL_ZIP_PHDA9_INNER_DEFAULT_MAX_WORDS, (int) trim((string) $e)));
}

/** @return list<string> */
function fractal_zip_enwik_phda9_inner_load_vocab_file(string $path): array
{
	$raw = (string) file_get_contents($path);
	if ($raw === '') {
		return array();
	}
	if (str_starts_with($raw, "FZPV\x01")) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		return fractal_zip_enwik_inner_fold_decode_vocab(substr($raw, 5));
	}
	$words = array();
	foreach (preg_split('/\r\n|\n|\r/', $raw) ?: array() as $line) {
		$w = trim((string) $line);
		if ($w !== '' && $w !== '80000') {
			$words[] = $w;
		}
		if (count($words) >= fractal_zip_enwik_phda9_inner_max_words()) {
			break;
		}
	}
	return $words;
}

/**
 * Frozen phda9-scale vocab: file override, else mine from full enwik8 text regions.
 *
 * @return list<string>
 */
function fractal_zip_enwik_phda9_inner_frozen_vocab(string $sliceMineText = ''): array
{
	static $cached = null;
	if (is_array($cached)) {
		return $cached;
	}
	$file = getenv('FRACTAL_ZIP_PHDA9_INNER_VOCAB_FILE');
	if ($file !== false && trim((string) $file) !== '' && is_file((string) $file)) {
		$cached = fractal_zip_enwik_phda9_inner_load_vocab_file((string) $file);
		return $cached;
	}
	$maxWords = fractal_zip_enwik_phda9_inner_max_words();
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$mineFull = getenv('FRACTAL_ZIP_PHDA9_INNER_MINE_FULL');
	$allowFullCorpus = $mineFull !== false && trim((string) $mineFull) !== '' && trim((string) $mineFull) !== '0';
	// Wire slices pass sorted prefix text — use it unless explicitly mining full corpus for static dict build.
	if ($sliceMineText !== '' && !$allowFullCorpus) {
		$cached = fractal_zip_text_dict_nncp_mine_vocab($sliceMineText, array(
			'max_words' => $maxWords,
			'min_word_len' => 2,
		));
		return $cached;
	}
	$minePath = getenv('FRACTAL_ZIP_PHDA9_INNER_MINE_PATH');
	if ($minePath === false || trim((string) $minePath) === '') {
		$minePath = __DIR__ . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	}
	$mineText = $sliceMineText;
	if ($allowFullCorpus && is_file((string) $minePath)) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
		$blob = (string) file_get_contents((string) $minePath);
		$split = enwik_split_page_refs($blob);
		if (is_array($split)) {
			$pages = $split['pages'];
			$nPages = min(count($pages), 4096);
			$mineText = '';
			for ($i = 0; $i < $nPages; $i++) {
				$p = $pages[$i];
				$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
				$text = fractal_zip_enwik_extract_page_preserve_text($pageXml);
				if (is_string($text)) {
					$mineText .= $text;
				}
			}
		}
	}
	if ($mineText === '') {
		throw new RuntimeException('phda9_inner: no mine text (set PHDA9_INNER_VOCAB_FILE or pass slice text)');
	}
	$cached = fractal_zip_text_dict_nncp_mine_vocab($mineText, array(
		'max_words' => $maxWords,
		'min_word_len' => 2,
	));
	return $cached;
}
