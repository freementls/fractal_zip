#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Sweep cfabb entry caps: phda9(inner) + inner-fold meta vs entity-only baseline.
 *
 * Usage:
 *   php benchmarks/bench_wiki_lom_cfabb_cap_sweep.php [--pages=384] [--caps=0,256,512,1024,2048,4096]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_collision_free_abbrevs.php';

$pages = 384;
$caps = array(0, 128, 256, 512, 1024, 2048, 4096);
$tablePath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_cfabb_table_384p.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--caps=')) {
		$caps = array();
		foreach (explode(',', substr($arg, 7)) as $c) {
			$c = trim($c);
			if ($c !== '' && ctype_digit($c)) {
				$caps[] = (int) $c;
			}
		}
	} elseif (str_starts_with($arg, '--table=')) {
		$tablePath = substr($arg, 8);
	}
}

$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
if (!is_file($dict)) {
	$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
}
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "missing {$src}\n");
	exit(1);
}
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}

$n = min($pages, count($split['pages']));
$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$sortedChunk[] = array(
		'title' => (string) ($p['title'] ?? ''),
		'origIndex' => $i,
		'start' => (int) $p['start'],
		'len' => (int) $p['len'],
	);
}
usort($sortedChunk, static fn (array $a, array $b): int => strcasecmp((string) $a['title'], (string) $b['title']));

$fullTable = array();
if (is_file($tablePath)) {
	putenv('FRACTAL_ZIP_CFABB_TABLE=' . $tablePath);
	$fullTable = fractal_zip_cfabb_load_json($tablePath);
}

$phda = static function (string $plain): array {
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => 'phda9_no_lstm',
		'use_dict' => getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT') !== false,
		'timeout_sec' => 0,
		'wire_wrap' => true,
	));
	return array(
		'bytes' => (int) ($r['bytes'] ?? 0),
		'rt' => !empty($r['roundtrip_ok']),
		'sec' => (float) ($r['seconds'] ?? 0.0),
	);
};

$buildPlain = static function (array $cfabbEntries) use ($sortedChunk, $blob): array {
	$flags = fractal_zip_wiki_lom_layer_flags(true);
	$opts = array_merge(fractal_zip_wiki_lom_empty_tables(), $flags, array('frozen' => true));
	if ($cfabbEntries !== array()) {
		$acronyms = array();
		foreach ($cfabbEntries as $row) {
			$acronyms[] = array(
				'phrase' => (string) ($row['phrase'] ?? ''),
				'token' => (string) ($row['token'] ?? ''),
				'acronym' => (string) ($row['token'] ?? ''),
			);
		}
		$opts['acronyms_list'] = $acronyms;
	}
	$layoutInput = array();
	foreach ($sortedChunk as $pg) {
		$pageText = fractal_zip_enwik_extract_page_preserve_text(
			substr($blob, (int) $pg['start'], (int) $pg['len'])
		);
		$pre = fractal_zip_wiki_lom_preprocess($pageText, $opts);
		$wire = fractal_zip_wiki_lom_phda9_wire_escape((string) $pre['payload']);
		$layoutInput[] = array(
			'title' => (string) $pg['title'],
			'origIndex' => (int) $pg['origIndex'],
			'text' => $wire,
		);
	}
	$layout = fractal_zip_enwik_text_layout_apply($layoutInput, 'sort_title', array('seed' => 1));
	$plain = (string) ($layout['text_blob'] ?? '');
	$metaBytes = 0;
	if ($cfabbEntries !== array()) {
		$acronyms = array();
		foreach ($cfabbEntries as $row) {
			$acronyms[] = array(
				'phrase' => (string) ($row['phrase'] ?? ''),
				'token' => (string) ($row['token'] ?? ''),
				'acronym' => (string) ($row['token'] ?? ''),
			);
		}
		$fold = fractal_zip_wiki_lom_inner_fold_blob(array('acronyms_list' => $acronyms));
		$metaBytes = strlen($fold);
	}
	return array('plain' => $plain, 'meta_bytes' => $metaBytes);
};

$fullPages = count($split['pages']);
$baselinePlain = $buildPlain(array());
$basePhda = $phda($baselinePlain['plain']);
if (!$basePhda['rt']) {
	fwrite(STDERR, "FAIL baseline phda9 RT\n");
	exit(1);
}
$baseScore = $basePhda['bytes'];

echo "wiki_lom cfabb cap sweep pages={$n} table=" . basename($tablePath)
	. " entries_full=" . count($fullTable) . "\n";
echo sprintf(
	"baseline entity-only: plain=%s phda9=%s B (%.1fs)\n",
	number_format(strlen($baselinePlain['plain'])),
	number_format($baseScore),
	$basePhda['sec']
);
echo str_pad('cap', 6) . str_pad('entries', 9) . str_pad('plain', 12)
	. str_pad('phda9', 10) . str_pad('meta', 8) . str_pad('slice', 10)
	. str_pad('amort', 10) . str_pad('Δ slice', 10) . "RT\n";

$bestCap = null;
$bestScore = PHP_INT_MAX;
foreach ($caps as $cap) {
	$entries = $cap <= 0 ? array() : fractal_zip_cfabb_slice_entries($fullTable, $cap);
	$built = $buildPlain($entries);
	$ph = $phda($built['plain']);
	if (!$ph['rt']) {
		echo str_pad((string) $cap, 6) . " RT FAIL\n";
		continue;
	}
	$sliceScore = $ph['bytes'] + $built['meta_bytes'];
	$amortMeta = (int) round($built['meta_bytes'] * ($n / max(1, $fullPages)));
	$amortScore = $ph['bytes'] + $amortMeta;
	$delta = $sliceScore - ($baseScore + $baselinePlain['meta_bytes']);
	echo str_pad((string) $cap, 6)
		. str_pad((string) count($entries), 9)
		. str_pad(number_format(strlen($built['plain'])), 12)
		. str_pad(number_format($ph['bytes']), 10)
		. str_pad(number_format($built['meta_bytes']), 8)
		. str_pad(number_format($sliceScore), 10)
		. str_pad(number_format($amortScore), 10)
		. str_pad(($delta <= 0 ? '' : '+') . number_format($delta), 10)
		. ($ph['rt'] ? 'ok' : 'FAIL') . "\n";
	if ($sliceScore < $bestScore) {
		$bestScore = $sliceScore;
		$bestCap = $cap;
	}
}

if ($bestCap !== null) {
	echo "best_slice_cap={$bestCap} score=" . number_format($bestScore)
		. ' Δ=' . number_format($bestScore - $baseScore) . " vs baseline\n";
}
