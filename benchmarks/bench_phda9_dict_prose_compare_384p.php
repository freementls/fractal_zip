#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Mine words vs words_prose_only phda9 dicts @384p and compare corpus gzip + optional phda9 FZPA.
 *
 * Usage: php benchmarks/bench_phda9_dict_prose_compare_384p.php [--pages=384] [--skip-phda9]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict_mine.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_html.php';

$pages = 384;
$skipPhda9 = false;
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_phda9_dict_prose_compare_384p.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif ($arg === '--skip-phda9') {
		$skipPhda9 = true;
	} elseif (str_starts_with($arg, '--out=')) {
		$outPath = substr($arg, 6);
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	$src = $repo . DIRECTORY_SEPARATOR . 'enwik8';
}
if (!is_file($src)) {
	fwrite(STDERR, "enwik8 missing\n");
	exit(1);
}

$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$sorted = enwik_sort_page_refs_by_title($split['pages']);
$n = min($pages, count($sorted));

$rawText = '';
$entityText = '';
$proseText = '';
for ($i = 0; $i < $n; $i++) {
	$page = substr($blob, (int) $sorted[$i]['start'], (int) $sorted[$i]['len']);
	$wire = fractal_zip_enwik_extract_page_preserve_text($page);
	$rawText .= $wire;
	$pre = fractal_zip_wiki_lom_preprocess($wire, array(
		'wiki_html' => false,
		'entity_decode' => true,
		'link_ids' => false,
		'templates' => false,
		'url_dict' => false,
		'abbrevs' => false,
		'tag_ids' => false,
		'frozen' => true,
	));
	$decoded = (string) $pre['payload'];
	$entityText .= $decoded;
	$proseText .= fractal_zip_wiki_lom_extract_prose(fractal_zip_wiki_html_encode($decoded));
}

$gz = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

$rows = array();
foreach (array('words' => $rawText, 'words_prose_only' => $proseText) as $mode => $mineCorpus) {
	$mined = fractal_zip_phda9_dict_mine_from_enwik($blob, array(
		'pages' => $n,
		'mode' => $mode,
		'chunk_pages' => min(96, $n),
	));
	$dictPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_' . $mode . '_' . $n . 'p.txt';
	fractal_zip_phda9_dict_write_file((array) $mined['words'], $dictPath);
	$row = array(
		'mode' => $mode,
		'dict_path' => $dictPath,
		'dict_words' => count((array) $mined['words']),
		'mine_corpus_bytes' => strlen($mineCorpus),
		'mine_corpus_gz9' => $gz($mineCorpus),
	);
	if (!$skipPhda9 && is_file($repo . '/fractal_zip_enwik_phda9_english.php')) {
		require_once $repo . '/fractal_zip_enwik_phda9_english.php';
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		$chunk = array();
		for ($i = 0; $i < $n; $i++) {
			$chunk[] = array(
				'origIndex' => $i,
				'start' => (int) $sorted[$i]['start'],
				'len' => (int) $sorted[$i]['len'],
			);
		}
		$pageXml = fractal_zip_enwik_phda9_english_payloads_from_refs($chunk, $blob)['sorted_page_xml'];
		$cr = fractal_zip_enwik_phda9_english_compress($pageXml, array(
			'tool' => 'phda9',
			'use_dict' => true,
			'timeout_sec' => 120,
			'wire_wrap' => true,
		));
		if (!empty($cr['roundtrip_ok']) && isset($cr['bytes'])) {
			$row['phda9_fzpa'] = (int) $cr['bytes'];
		}
	}
	$rows[] = $row;
}

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'raw_preserve_text_bytes' => strlen($rawText),
	'entity_decoded_bytes' => strlen($entityText),
	'entity_decoded_gz9' => $gz($entityText),
	'prose_mine_bytes' => strlen($proseText),
	'prose_mine_gz9' => $gz($proseText),
	'rows' => $rows,
);
file_put_contents($outPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "dict prose compare @{$n}p\n";
echo '  entity_decoded gz9=' . number_format($report['entity_decoded_gz9']) . "\n";
foreach ($rows as $row) {
	echo sprintf(
		"  %-18s words=%5d mine_gz9=%8s",
		$row['mode'],
		$row['dict_words'],
		number_format($row['mine_corpus_gz9'])
	);
	if (isset($row['phda9_fzpa'])) {
		echo ' fzpa=' . number_format($row['phda9_fzpa']);
	}
	echo "\n";
}
echo 'wrote ' . $outPath . "\n";
