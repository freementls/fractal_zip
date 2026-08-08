#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * phda9 FZPA probe for wiki_lom ladder wires @384p.
 *
 * Usage: php benchmarks/bench_wiki_lom_phda9_probe.php [--pages=384]
 */

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_text_codec.php';
require_once $repo . '/fractal_zip_wiki_lom.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';

$pages = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$src = $repo . '/test_files109/enwik8';
if (!is_file($src)) {
	$src = $repo . '/enwik8';
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
$sorted = enwik_sort_page_refs_by_title($split['pages']);
$n = min($pages, count($sorted));
$raw = '';
$pageRefs = array();
for ($i = 0; $i < $n; $i++) {
	$ref = $sorted[$i];
	$pageRefs[] = array('title' => (string) $ref['title'], 'start' => (int) $ref['start'], 'len' => (int) $ref['len']);
	$raw .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $ref['start'], (int) $ref['len']));
}

$dict = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
$flags = array(
	'raw' => null,
	'entity_only' => array('wiki_html' => false, 'entity_decode' => true, 'link_ids' => false, 'url_dict' => false, 'templates' => false, 'abbrevs' => false, 'tag_ids' => false),
	'entity_html_link_url' => array('wiki_html' => true, 'entity_decode' => true, 'link_ids' => true, 'url_dict' => true, 'templates' => false, 'abbrevs' => false, 'tag_ids' => false),
);
$wires = array('raw' => $raw);
foreach ($flags as $name => $f) {
	if ($f === null) {
		continue;
	}
	$parts = array();
	for ($i = 0; $i < $n; $i++) {
		$pageText = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $sorted[$i]['start'], (int) $sorted[$i]['len']));
		$tables = fractal_zip_wiki_lom_mine_tables($pageText, array($pageRefs[$i]), $blob);
		$pre = fractal_zip_wiki_lom_preprocess($pageText, array_merge($tables, $f, array('frozen' => true)));
		$parts[] = (string) $pre['payload'];
	}
	$wires[$name] = implode('', $parts);
}

$out = $repo . '/benchmarks/.enwik8_wiki_lom_phda9_probe_384p.json';
$rows = array();
$baseline = null;
foreach ($wires as $name => $wire) {
	fwrite(STDERR, "phda9 {$name}...\n");
	$cr = fractal_zip_enwik_phda9_english_compress($wire, array('dict_path' => $dict, 'timeout_sec' => 900));
	$fzpa = !empty($cr['roundtrip_ok']) ? strlen((string) $cr['payload']) : null;
	if ($name === 'raw') {
		$baseline = $fzpa;
	}
	$rows[] = array(
		'step' => $name,
		'raw_bytes' => strlen($wire),
		'phda9_fzpa' => $fzpa,
		'phda9_delta' => ($fzpa !== null && $baseline !== null) ? $fzpa - $baseline : null,
		'rt' => !empty($cr['roundtrip_ok']),
	);
}
file_put_contents($out, json_encode(array('pages' => $n, 'baseline' => $baseline, 'rows' => $rows), JSON_PRETTY_PRINT));
echo "wrote {$out}\n";
foreach ($rows as $row) {
	echo sprintf(
		"  %-22s raw=%8s phda9=%8s delta=%s\n",
		$row['step'],
		number_format((int) $row['raw_bytes']),
		$row['phda9_fzpa'] !== null ? number_format((int) $row['phda9_fzpa']) : 'n/a',
		isset($row['phda9_delta']) && $row['phda9_delta'] !== null
			? (($row['phda9_delta'] >= 0 ? '+' : '') . number_format((int) $row['phda9_delta']))
			: 'n/a'
	);
}
