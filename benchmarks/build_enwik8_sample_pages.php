#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build a 5-page enwik8 sample with substantial article text for codec experiments.
 *
 * Output: benchmarks/corpus/enwik8_sample5/
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$count = 5;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--count=')) {
		$count = max(1, (int) substr($arg, 8));
	}
}

$blob = file_get_contents($src);
if (!is_string($blob) || $blob === '') {
	exit(1);
}
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "enwik split failed\n");
	exit(1);
}

$scored = array();
foreach ($split['pages'] as $ref) {
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$text = fractal_zip_enwik_extract_page_preserve_text($page);
	$textLen = strlen($text);
	if ($textLen < 8000) {
		continue;
	}
	$scored[] = array(
		'title' => (string) $ref['title'],
		'origIndex' => (int) $ref['origIndex'],
		'textLen' => $textLen,
		'pageLen' => (int) $ref['len'],
		'start' => (int) $ref['start'],
		'len' => (int) $ref['len'],
	);
}
usort($scored, static fn (array $a, array $b): int => $b['textLen'] <=> $a['textLen']);
$pick = array_slice($scored, 0, $count);
if ($pick === array()) {
	fwrite(STDERR, "No pages met text length threshold\n");
	exit(1);
}

$outDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
@mkdir($outDir . DIRECTORY_SEPARATOR . 'pages', 0755, true);
@mkdir($outDir . DIRECTORY_SEPARATOR . 'text', 0755, true);

$manifest = array(
	'generated' => date('c'),
	'source' => $src,
	'page_count' => count($pick),
	'pages' => array(),
);

foreach ($pick as $i => $row) {
	$page = substr($blob, $row['start'], $row['len']);
	$text = fractal_zip_enwik_extract_page_preserve_text($page);
	$slug = sprintf('%02d_%s', $i, preg_replace('/[^A-Za-z0-9._-]+/', '_', substr($row['title'], 0, 40)) ?: 'page');
	$pagePath = 'pages/' . $slug . '.xml';
	$textPath = 'text/' . $slug . '.txt';
	file_put_contents($outDir . DIRECTORY_SEPARATOR . $pagePath, $page);
	file_put_contents($outDir . DIRECTORY_SEPARATOR . $textPath, $text);
	$manifest['pages'][] = array(
		'id' => $i,
		'title' => $row['title'],
		'origIndex' => $row['origIndex'],
		'page_bytes' => $row['pageLen'],
		'text_bytes' => strlen($text),
		'page_path' => $pagePath,
		'text_path' => $textPath,
	);
}

file_put_contents(
	$outDir . DIRECTORY_SEPARATOR . 'manifest.json',
	json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);

echo "enwik8_sample5: " . count($pick) . " pages → {$outDir}\n";
foreach ($manifest['pages'] as $p) {
	printf("  [%02d] %s  text=%s B  page=%s B\n", $p['id'], $p['title'], number_format($p['text_bytes']), number_format($p['page_bytes']));
}
