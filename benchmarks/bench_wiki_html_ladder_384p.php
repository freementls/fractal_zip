#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Tier 0 wiki→HTML + wiki_lom ladder on enwik preserve-text @384p.
 *
 * Usage: php benchmarks/bench_wiki_html_ladder_384p.php [--pages=384]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_html.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';

$pages = 384;
$skipPhda9 = false;
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wiki_lom_transform_ladder_384p.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--out=')) {
		$outPath = substr($arg, 6);
	} elseif ($arg === '--skip-phda9') {
		$skipPhda9 = true;
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
$text = '';
$pageRefs = array();
for ($i = 0; $i < $n; $i++) {
	$ref = $sorted[$i];
	$pageRefs[] = array('title' => (string) $ref['title'], 'start' => (int) $ref['start'], 'len' => (int) $ref['len']);
	$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $ref['start'], (int) $ref['len']));
}

$gz = static function (string $s): int {
	$z = gzdeflate($s, 9);
	return $z === false ? strlen($s) : strlen($z);
};

$baselineGz = $gz($text);
$baselineFzpa = null;
$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
if (!$skipPhda9 && is_file($dict)) {
	require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
	fwrite(STDERR, "phda9 baseline compress...\n");
	$cr = fractal_zip_enwik_phda9_english_compress($text, array('dict_path' => $dict, 'timeout_sec' => 600));
	if (!empty($cr['roundtrip_ok'])) {
		$baselineFzpa = strlen((string) $cr['payload']);
	}
}

$steps = array();
$probe = static function (string $name, string $wire, bool $rt) use (&$steps, $gz, $baselineGz, $baselineFzpa, $dict, $skipPhda9): void {
	$row = array(
		'step' => $name,
		'raw_bytes' => strlen($wire),
		'gz9' => $gz($wire),
		'rt' => $rt,
		'gz_delta' => $gz($wire) - $baselineGz,
	);
	if (!$skipPhda9 && $baselineFzpa !== null && is_file($dict)) {
		require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_phda9_english.php';
		fwrite(STDERR, "phda9 step {$name}...\n");
		$cr = fractal_zip_enwik_phda9_english_compress($wire, array('dict_path' => $dict, 'timeout_sec' => 600));
		if (!empty($cr['roundtrip_ok'])) {
			$row['phda9_fzpa'] = strlen((string) $cr['payload']);
			$row['phda9_delta'] = $row['phda9_fzpa'] - $baselineFzpa;
		}
	}
	$steps[] = $row;
};

$probe('raw preserve-text', $text, true);

$entityRt = true;
$entityWireParts = array();
for ($i = 0; $i < $n; $i++) {
	$pageText = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $sorted[$i]['start'], (int) $sorted[$i]['len']));
	$pageTables = fractal_zip_wiki_lom_mine_tables($pageText, array($pageRefs[$i]), $blob);
	$pre = fractal_zip_wiki_lom_preprocess($pageText, array_merge($pageTables, array(
		'wiki_html' => false,
		'entity_decode' => true,
		'link_ids' => false,
		'templates' => false,
		'url_dict' => false,
		'abbrevs' => false,
		'tag_ids' => false,
		'frozen' => true,
	)));
	$entityWireParts[] = (string) $pre['payload'];
	if (fractal_zip_wiki_lom_undo((string) $pre['payload'], $pre['sidecar']) !== $pageText) {
		$entityRt = false;
	}
}
$probe('double_entity_decode_only', implode('', $entityWireParts), $entityRt);

$html = fractal_zip_wiki_html_encode($text);
$htmlRt = true;
for ($i = 0; $i < $n; $i++) {
	$pageText = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $sorted[$i]['start'], (int) $sorted[$i]['len']));
	if (fractal_zip_wiki_html_decode(fractal_zip_wiki_html_encode($pageText)) !== $pageText) {
		$htmlRt = false;
		break;
	}
}
$probe('wiki_to_html', $html, $htmlRt);

$tables = fractal_zip_wiki_lom_mine_tables($text, $pageRefs, $blob);
$flags = array(
	'wiki_html' => false,
	'entity_decode' => false,
	'link_ids' => false,
	'templates' => false,
	'url_dict' => false,
	'abbrevs' => false,
	'tag_ids' => false,
);
foreach (array('entity_decode', 'wiki_html', 'link_ids', 'url_dict') as $flag) {
	$flags[$flag] = true;
	$wireParts = array();
	$rtOk = true;
	for ($i = 0; $i < $n; $i++) {
		$pageText = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $sorted[$i]['start'], (int) $sorted[$i]['len']));
		$pageTables = fractal_zip_wiki_lom_mine_tables($pageText, array($pageRefs[$i]), $blob);
		$pre = fractal_zip_wiki_lom_preprocess($pageText, array_merge($pageTables, $flags, array('frozen' => true)));
		$wireParts[] = (string) $pre['payload'];
		if (fractal_zip_wiki_lom_undo((string) $pre['payload'], $pre['sidecar']) !== $pageText) {
			$rtOk = false;
		}
	}
	$probe('wiki_lom+' . $flag, implode('', $wireParts), $rtOk);
}

$report = array(
	'pages' => $n,
	'baseline_gz9' => $baselineGz,
	'baseline_phda9_fzpa' => $baselineFzpa,
	'steps' => $steps,
);
$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false || file_put_contents($outPath, $json) === false) {
	exit(1);
}

echo "Wiki HTML ladder ({$n} pages)\n";
echo 'baseline gz9=' . number_format($baselineGz);
if ($baselineFzpa !== null) {
	echo ' phda9=' . number_format($baselineFzpa);
}
echo "\n";
foreach ($steps as $row) {
	echo sprintf(
		"  %-24s raw=%8s gz9=%8s",
		$row['step'],
		number_format($row['raw_bytes']),
		number_format($row['gz9'])
	);
	if (isset($row['phda9_delta'])) {
		echo ' phda9_d=' . ($row['phda9_delta'] >= 0 ? '+' : '') . number_format($row['phda9_delta']);
	}
	echo ($row['rt'] ? ' RT=ok' : ' RT=FAIL') . "\n";
}
echo 'wrote ' . $outPath . "\n";
