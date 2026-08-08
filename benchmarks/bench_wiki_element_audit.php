#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Rank wiki element classes by raw bytes on enwik preserve-text.
 *
 * Usage: php benchmarks/bench_wiki_element_audit.php [--pages=384] [--out=path.json]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_html.php';

$pages = 384;
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wiki_element_audit.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(8, (int) substr($arg, 8));
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
	fwrite(STDERR, "split failed\n");
	exit(1);
}
$sorted = enwik_sort_page_refs_by_title($split['pages']);
$n = min($pages, count($sorted));
$text = '';
for ($i = 0; $i < $n; $i++) {
	$ref = $sorted[$i];
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$text .= fractal_zip_enwik_extract_page_preserve_text($page);
}

$classes = array(
	'html_entities' => '/&(?:#x?[0-9a-fA-F]+|[a-zA-Z][a-zA-Z0-9]+);/',
	'internal_links' => '/\[\[(?!Category:|Image:|File:)[^\]]+\]\]/',
	'templates' => '/\{\{(?:[^{}]|\{\{[^{}]*\}\})*\}\}/',
	'external_links' => '/\[(?:https?|ftp):[^\]]+\]/i',
	'wiki_bold' => "/'''/",
	'wiki_italic' => "/''/",
	'headings' => '/^=+[^=].*?=+\s*$/m',
	'list_marks' => '/^[\*#:;]+/m',
	'html_tags' => '/<[^>]+>/',
	'category_links' => '/\[\[Category:[^\]]+\]\]/',
	'image_links' => '/\[\[(?:Image|File):[^\]]+\]\]/',
	'redirects' => '/#(?:REDIRECT|redirect)\s*\[\[/i',
);

$rows = array();
$totalBytes = strlen($text);
foreach ($classes as $name => $re) {
	$count = 0;
	$bytes = 0;
	$tops = array();
	if (preg_match_all($re, $text, $m) > 0) {
		foreach ($m[0] as $hit) {
			$hit = (string) $hit;
			$count++;
			$bytes += strlen($hit);
			if (!isset($tops[$hit])) {
				$tops[$hit] = 0;
			}
			$tops[$hit]++;
		}
	}
	arsort($tops, SORT_NUMERIC);
	$hint = match ($name) {
		'templates', 'internal_links', 'html_entities' => 0.85,
		'html_tags' => 0.75,
		'external_links' => 0.9,
		default => 0.95,
	};
	$rows[] = array(
		'class' => $name,
		'count' => $count,
		'bytes' => $bytes,
		'pct' => $totalBytes > 0 ? round(100 * $bytes / $totalBytes, 3) : 0,
		'roi_score' => (int) round($bytes * $hint),
		'top' => array_slice(array_keys($tops), 0, 8),
	);
}
usort($rows, static fn (array $a, array $b): int => ($b['roi_score'] <=> $a['roi_score']));

$htmlText = fractal_zip_wiki_html_encode($text);
$htmlClasses = array(
	'html_tag_names' => '/<\/?([a-zA-Z][a-zA-Z0-9-]*)\b/',
	'inline_style' => '/\bstyle\s*=\s*"[^"]*"/i',
	'href_attrs' => '/\bhref\s*=\s*"[^"]*"/i',
);
$htmlRows = array();
foreach ($htmlClasses as $name => $re) {
	$count = 0;
	$bytes = 0;
	if (preg_match_all($re, $htmlText, $m) > 0) {
		foreach ($m[0] as $hit) {
			$count++;
			$bytes += strlen((string) $hit);
		}
	}
	$htmlRows[] = array('class' => $name, 'count' => $count, 'bytes' => $bytes);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
$entityCounts = array(
	'html_special_quot' => '/&quot;/',
	'html_special_lt' => '/&lt;/',
	'html_special_gt' => '/&gt;/',
	'html_special_amp' => '/&amp;(?!quot;|lt;|gt;|amp;|#)/',
	'double_html_quot' => '/&amp;quot;/',
	'double_html_lt' => '/&amp;lt;/',
	'double_html_gt' => '/&amp;gt;/',
	'double_html_amp' => '/&amp;amp;(?!quot;|lt;|gt;|amp;|#)/',
	'numeric_single' => '/&(?!amp;)#(?:x[0-9a-fA-F]+|\d+);/',
	'numeric_double' => '/&amp;#(?:x[0-9a-fA-F]+|\d+);/',
	'raw_utf8_mb' => '/[\xC2-\xF4][\x80-\xBF]{1,3}/',
);
$entityRows = array();
$htmlSpecialBytes = 0;
$doubleHtmlBytes = 0;
$numericBytes = 0;
$multibyteBytes = 0;
foreach ($entityCounts as $name => $re) {
	$c = 0;
	$b = 0;
	if (preg_match_all($re, $text, $m) > 0) {
		$c = count($m[0]);
		foreach ($m[0] as $hit) {
			$b += strlen((string) $hit);
		}
	}
	$entityRows[] = array('class' => $name, 'count' => $c, 'bytes' => $b);
	if (str_starts_with($name, 'html_special_')) {
		$htmlSpecialBytes += $b;
	} elseif (str_starts_with($name, 'double_html_')) {
		$doubleHtmlBytes += $b;
	} elseif (str_starts_with($name, 'numeric_')) {
		$numericBytes += $b;
	} elseif ($name === 'raw_utf8_mb') {
		$multibyteBytes += $b;
	}
}
$postDecode = fractal_zip_wiki_lom_entity_decode_text($text);

$report = array(
	'pages' => $n,
	'total_text_bytes' => $totalBytes,
	'post_decode_bytes' => strlen($postDecode),
	'post_decode_delta' => strlen($postDecode) - $totalBytes,
	'html_text_bytes' => strlen($htmlText),
	'html_delta_bytes' => strlen($htmlText) - $totalBytes,
	'entity_encoding' => $entityRows,
	'entity_encoding_summary' => array(
		'html_special_entities_bytes' => $htmlSpecialBytes,
		'double_html_entities_bytes' => $doubleHtmlBytes,
		'numeric_entities_bytes' => $numericBytes,
		'multibyte_utf8_bytes' => $multibyteBytes,
	),
	'classes' => $rows,
	'post_html' => $htmlRows,
);
$json = json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
if ($json === false || file_put_contents($outPath, $json) === false) {
	fwrite(STDERR, "write failed\n");
	exit(1);
}

echo "Wiki element audit ({$n} pages)\n";
echo 'total_text=' . number_format($totalBytes) . ' html=' . number_format(strlen($htmlText))
	. ' delta=' . number_format(strlen($htmlText) - $totalBytes) . "\n";
echo 'entity html_special=' . number_format($htmlSpecialBytes)
	. ' double_html=' . number_format($doubleHtmlBytes)
	. ' numeric=' . number_format($numericBytes)
	. ' utf8_mb=' . number_format($multibyteBytes)
	. ' post_decode_delta=' . number_format(strlen($postDecode) - $totalBytes) . "\n";
foreach (array_slice($rows, 0, 10) as $row) {
	echo sprintf(
		"  %-18s count=%6d bytes=%8s pct=%5.2f%% roi=%8s\n",
		$row['class'],
		$row['count'],
		number_format($row['bytes']),
		$row['pct'],
		number_format($row['roi_score'])
	);
}
echo 'wrote ' . $outPath . "\n";
