#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Greedy phda9 gate on a frozen cfabb table: keep entries only when
 * phda9(substituted wire) + fold meta beats the current set.
 *
 * Usage:
 *   FRACTAL_ZIP_CFABB_PHDA9_GATE=1 php benchmarks/filter_cfabb_phda9.php \
 *     --pages=384 --in=benchmarks/.enwik8_cfabb_table_384p_filtered.json \
 *     --out=benchmarks/.enwik8_cfabb_table_384p_phda9.json
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_CFABB_PHDA9_GATE=1');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
$quickGate = false;

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';

$pages = 384;
$inPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_cfabb_table_384p_filtered.json';
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_cfabb_table_384p_phda9.json';
$pool = 0;
$maxAccept = 0;
$plainXml = false;
$quickGate = false;
$corpusId = 8;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--in=')) {
		$inPath = substr($arg, 5);
	} elseif (str_starts_with($arg, '--out=')) {
		$outPath = substr($arg, 6);
	} elseif (str_starts_with($arg, '--pool=')) {
		$pool = max(0, (int) substr($arg, 7));
	} elseif (str_starts_with($arg, '--max=')) {
		$maxAccept = max(0, (int) substr($arg, 6));
	} elseif (str_starts_with($arg, '--corpus=')) {
		$corpusId = max(8, min(9, (int) substr($arg, 9)));
	} elseif ($arg === '--plain-xml') {
		$plainXml = true;
	} elseif ($arg === '--quick-gate') {
		$quickGate = true;
	}
}
if ($quickGate) {
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
}

$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
if (!is_file($dict)) {
	$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
}
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$src = fractal_zip_enwik_corpus_path($repo, $corpusId);
if (!is_file($src) || !is_file($inPath)) {
	fwrite(STDERR, "missing enwik{$corpusId} or input table\n");
	exit(1);
}

$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pages, count($split['pages']));
$chunk = array();
$text = '';
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$chunk[] = array('start' => (int) $p['start'], 'len' => (int) $p['len']);
	if (!$plainXml) {
		$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
	}
}
if ($plainXml) {
	$gateText = fractal_zip_cfabb_plain_xml_build_page_stream($chunk, $blob, array());
	$precheckText = '';
	foreach ($chunk as $p) {
		$precheckText .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
	}
} else {
	$precheckText = '';
	$decoded = fractal_zip_wiki_lom_entity_decode_text($text);
	$gateText = fractal_zip_wiki_lom_phda9_wire_escape($decoded);
}

$raw = fractal_zip_cfabb_load_json($inPath);
if ($raw === array()) {
	fwrite(STDERR, "empty input table\n");
	exit(1);
}

$gateOpts = array(
	'phda9_greedy_pool' => $pool,
	'phda9_greedy_max' => $maxAccept,
);
if ($plainXml) {
	$gateOpts['phda9_gate_chunk'] = $chunk;
	$gateOpts['phda9_gate_blob'] = $blob;
	$gateOpts['phda9_precheck_text'] = $precheckText;
}

$baseScore = fractal_zip_cfabb_phda9_wire_score($gateText, array(), $gateOpts);
echo 'filter_cfabb_phda9 pages=' . $n . ' in=' . count($raw) . ' base_score=' . number_format($baseScore) . "\n";

$t0 = microtime(true);
$filtered = fractal_zip_cfabb_filter_phda9_greedy($gateText, $raw, $gateOpts);
$finalScore = fractal_zip_cfabb_phda9_wire_score($gateText, $filtered, $gateOpts);
$meta = fractal_zip_cfabb_fold_meta_bytes($filtered);
$sec = round(microtime(true) - $t0, 1);

fractal_zip_cfabb_save_json($outPath, $filtered);
echo 'out=' . count($filtered) . ' score=' . number_format($finalScore)
	. ' meta=' . number_format($meta)
	. ' delta=' . number_format($finalScore - $baseScore)
	. " sec={$sec} table={$outPath}\n";
