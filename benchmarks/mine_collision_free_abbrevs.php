#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * One-time mine of collision-free abbreviations on entity-decoded preserve-text.
 *
 * Usage:
 *   php -d memory_limit=4096M benchmarks/mine_collision_free_abbrevs.php [--pages=384] [--out=path.json]
 *   php ... [--plain-xml] [--phda9-gate]   # plain preserve-text + optional real phda9 gate
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';

$pages = 0;
$outPath = '';
$minCount = 4;
$maxEntries = 0;
$phda9Gate = false;
$plainXml = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(0, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--out=')) {
		$outPath = substr($arg, 6);
	} elseif (str_starts_with($arg, '--min-count=')) {
		$minCount = max(2, (int) substr($arg, 12));
	} elseif (str_starts_with($arg, '--max-entries=')) {
		$maxEntries = max(16, (int) substr($arg, 14));
	} elseif ($arg === '--phda9-gate') {
		$phda9Gate = true;
	} elseif ($arg === '--plain-xml') {
		$plainXml = true;
	}
}
if ($outPath === '') {
	$suffix = $pages > 0 ? ('_' . $pages . 'p') : '_full';
	$name = '.enwik8_cfabb_table' . $suffix . ($phda9Gate ? '_phda9' : '') . '.json';
	$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . $name;
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "missing enwik8 at {$src}\n");
	exit(1);
}

$t0 = microtime(true);
$blob = (string) file_get_contents($src);
if ($pages > 0) {
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		fwrite(STDERR, "enwik split failed\n");
		exit(1);
	}
	$slice = (string) $split['header'];
	for ($i = 0; $i < min($pages, count($split['pages'])); $i++) {
		$p = $split['pages'][$i];
		$slice .= substr($blob, (int) $p['start'], (int) $p['len']);
	}
	$slice .= (string) $split['footer'];
	$blob = $slice;
}

if ($phda9Gate) {
	putenv('FRACTAL_ZIP_CFABB_PHDA9_GATE=1');
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
	$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
	if (!is_file($dict)) {
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
	}
	if (is_file($dict)) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
	}
}

$mineOpts = array(
	'pages' => $pages,
	'min_count' => $minCount,
	'max_entries' => $maxEntries,
	'max_words' => 10,
	'max_chars' => 100,
);
if ($plainXml) {
	$entries = fractal_zip_cfabb_mine_enwik_plain_preserve_text($blob, $mineOpts);
} else {
	$entries = fractal_zip_cfabb_mine_enwik_preserve_text($blob, $mineOpts);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
$split = enwik_split_page_refs($blob);
$wireLen = 0;
$rawLen = 0;
if ($split !== null) {
	$n = $pages > 0 ? min($pages, count($split['pages'])) : count($split['pages']);
	$chunk = array();
	$text = '';
	for ($i = 0; $i < $n; $i++) {
		$p = $split['pages'][$i];
		$chunk[] = array('start' => (int) $p['start'], 'len' => (int) $p['len']);
		$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
	}
	if ($plainXml) {
		$rawLen = strlen($text);
		$wireStream = fractal_zip_cfabb_plain_xml_build_page_stream($chunk, $blob, $entries);
		$wireLen = strlen($wireStream);
	} else {
		$decoded = fractal_zip_wiki_lom_entity_decode_text($text);
		$rawLen = strlen($decoded);
		$wireLen = strlen(fractal_zip_cfabb_apply($decoded, $entries));
	}
}

$save = 0;
foreach ($entries as $row) {
	$save += (int) ($row['save'] ?? 0);
}

fractal_zip_cfabb_save_json($outPath, $entries);
$sec = round(microtime(true) - $t0, 1);

echo "mine_collision_free_abbrevs pages=" . ($pages > 0 ? (string) $pages : 'all')
	. " entries=" . count($entries) . " sec={$sec}\n";
echo "preserve_len={$rawLen} wire_len={$wireLen} est_save={$save} table={$outPath}\n";
if ($entries !== array()) {
	$top = $entries[0];
	echo 'top: "' . ($top['phrase'] ?? '') . '" -> ' . ($top['token'] ?? '')
		. ' x' . ($top['count'] ?? 0) . ' save=' . ($top['save'] ?? 0) . "B\n";
	$tokSample = array();
	foreach (array_slice($entries, 0, 8) as $row) {
		$tokSample[] = (string) ($row['token'] ?? '');
	}
	echo 'token_sample: ' . implode(', ', $tokSample) . "\n";
}
