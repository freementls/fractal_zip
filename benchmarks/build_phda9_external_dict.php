#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build phda9-format external dictionary from enwik8 text vocabulary.
 *
 * Output default: benchmarks/.phda9_external_dict.txt
 *
 * Options:
 *   --pages=N   mine from first N pages (default 4096)
 *   --full      mine full enwik8 text regions (up to 4096 pages)
 *   --mode=words|phrases|subwords|optimal|mixed|mixed_tiered|mixed_refine  token mix (default words)
 *   --out=PATH  output file path
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_dict_phda9_inner.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict_mine.php';

$pageLimit = 4096;
$full = false;
$mode = 'words';
$preprocess = '';
$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';

foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
	if ($arg === '--full') {
		$full = true;
	}
	if (str_starts_with($arg, '--mode=')) {
		$mode = strtolower(trim(substr($arg, 7)));
	}
	if (str_starts_with($arg, '--out=')) {
		$out = substr($arg, 6);
	}
	if (str_starts_with($arg, '--preprocess=')) {
		$preprocess = strtolower(trim(substr($arg, 13)));
	}
}

if ($preprocess !== '' && !in_array($preprocess, array('consonant_hybrid'), true)) {
	fwrite(STDERR, "unsupported --preprocess={$preprocess}\n");
	exit(1);
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

putenv('FRACTAL_ZIP_ENWIK_FAST_PAGE_SPLIT=1');

$nPages = 0;
$stats = array();
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}
$nPages = $full ? min(4096, count($split['pages'])) : min($pageLimit, count($split['pages']));

if ($preprocess !== '' || $mode !== 'words' || $full) {
	if ($full) {
		$pageLimit = $nPages;
	}
	$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
		'pages' => $nPages,
		'mode' => $full ? 'optimal' : $mode,
		'preprocess' => $preprocess !== '' ? $preprocess : null,
	));
	$words = $mined['words'];
	$stats = $mined['stats'];
} else {
	$chunkPages = $nPages > 2048 ? 96 : ($nPages > 768 ? 192 : 384);
	require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
	$maxWords = fractal_zip_enwik_phda9_inner_max_words();
	$words = array();
	$seen = array();
	for ($start = 0; $start < $nPages; $start += $chunkPages) {
		$mineText = '';
		$end = min($start + $chunkPages, $nPages);
		for ($i = $start; $i < $end; $i++) {
			$p = $split['pages'][$i];
			$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
			$text = fractal_zip_enwik_extract_page_preserve_text($pageXml);
			if (is_string($text)) {
				$mineText .= $text;
			}
		}
		if ($mineText === '') {
			continue;
		}
		$batch = fractal_zip_text_dict_nncp_mine_vocab($mineText, array(
			'max_words' => $maxWords,
			'min_word_len' => 2,
		));
		foreach ($batch as $w) {
			if (isset($seen[$w])) {
				continue;
			}
			$seen[$w] = true;
			$words[] = $w;
			if (count($words) >= $maxWords) {
				break 2;
			}
		}
	}
	if ($words === array()) {
		fwrite(STDERR, "no mine text from {$nPages} pages\n");
		exit(1);
	}
}

$written = fractal_zip_phda9_dict_write_file($words, $out);
echo 'pages=' . $nPages
	. ' mode=' . ($stats['mode'] ?? $mode)
	. ' vocab_words=' . count($words)
	. ' dict_words=' . $written['words']
	. ' dict_bytes=' . $written['bytes']
	. ' out=' . $written['path'];
if ($stats !== array()) {
	echo ' kind_hist=' . json_encode($stats['kind_hist'] ?? array(), JSON_UNESCAPED_SLASHES);
}
echo "\n";
