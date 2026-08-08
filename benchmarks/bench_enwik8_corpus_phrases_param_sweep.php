#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Fast gzip-1 sweep: corpus phrase mining params on wire path (text bodies only).
 *
 * Usage:
 *   php -d memory_limit=4096M benchmarks/bench_enwik8_corpus_phrases_param_sweep.php [--pages=384]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$header = (string) $split['header'];
$footer = (string) $split['footer'];
$pages = $split['pages'];
$n = min($pageLimit, count($pages));
$slice = $header;
for ($i = 0; $i < $n; $i++) {
	$pref = $pages[$i];
	$slice .= substr($blob, (int) $pref['start'], (int) $pref['len']);
}
$slice .= $footer;

$g = static function (string $b): int {
	$z = gzdeflate($b, 1);
	return is_string($z) ? strlen($z) : 0;
};

$baseGz = $g($slice);
$baseLen = strlen($slice);

$grid = array();
foreach (array(16, 24, 32, 48) as $minCount) {
	foreach (array(192, 256, 384, 512) as $maxEntries) {
		foreach (array(8, 10, 12) as $minLen) {
			foreach (array(96, 120, 160) as $maxLen) {
				if ($maxLen < $minLen) {
					continue;
				}
				$grid[] = array($minCount, $maxEntries, $minLen, $maxLen);
			}
		}
	}
}

$rows = array();
foreach ($grid as [$minCount, $maxEntries, $minLen, $maxLen]) {
	$phrases = fractal_zip_enwik_mine_corpus_phrases_from_text_bodies($slice, $minLen, $maxLen, $minCount, $maxEntries);
	if ($phrases === array()) {
		continue;
	}
	$packed = fractal_zip_enwik_phrase_pack_in_text_regions_whole_blob($slice, $phrases);
	$outBlob = (string) $packed['blob'];
	$dictBytes = strlen((string) $packed['dict']);
	$blobLen = strlen($outBlob);
	$gz = $g($outBlob);
	$rows[] = array(
		'min_count' => $minCount,
		'max_entries' => $maxEntries,
		'min_len' => $minLen,
		'max_len' => $maxLen,
		'phrases_mined' => count($phrases),
		'blob_bytes' => $blobLen,
		'blob_delta' => $baseLen - $blobLen,
		'dict_bytes' => $dictBytes,
		'gzip1_bytes' => $gz,
		'gzip1_delta' => $baseGz - $gz,
		'net_gzip1' => $baseGz - $gz - $dictBytes,
	);
}

usort(
	$rows,
	static function (array $a, array $b): int {
		return ($b['net_gzip1'] ?? 0) <=> ($a['net_gzip1'] ?? 0);
	}
);

$out = array(
	'generated' => date('c'),
	'pages' => $n,
	'slice_bytes' => $baseLen,
	'base_gzip1' => $baseGz,
	'rows' => $rows,
	'top5' => array_slice($rows, 0, 5),
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_corpus_phrases_param_sweep.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo "corpus param sweep @{$n}p → {$path}\n";
echo "  base gzip1=" . number_format($baseGz) . " slice=" . number_format($baseLen) . " B\n";
foreach (array_slice($rows, 0, 8) as $i => $r) {
	echo sprintf(
		"  #%d min=%d max=%d len=%d-%d phrases=%d gzip1Δ=%s net=%s dict=%s\n",
		$i + 1,
		$r['min_count'],
		$r['max_entries'],
		$r['min_len'],
		$r['max_len'],
		$r['phrases_mined'],
		number_format($r['gzip1_delta']),
		number_format($r['net_gzip1']),
		number_format($r['dict_bytes'])
	);
}
