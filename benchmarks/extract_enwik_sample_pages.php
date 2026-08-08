#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$sliceLimit = 0;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--slice=')) {
		$sliceLimit = max(0, (int) substr($arg, 8));
	}
}

require_once $repo . '/fractal_zip_enwik.php';

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	fwrite(STDERR, "split failed\n");
	exit(1);
}
$pages = $split['pages'];
$sorted = enwik_sort_page_refs_by_title($pages);
$n = $sliceLimit > 0 ? min($sliceLimit, count($sorted)) : count($sorted);
$pick = array(0, (int) floor(($n - 1) / 2), $n - 1);
$outDir = $repo . '/benchmarks/.enwik8_sample_pages';
@mkdir($outDir, 0755, true);
$prefix = $sliceLimit > 0 ? 'slice' . $sliceLimit . '_' : '';

foreach ($pick as $i => $si) {
	$ref = $sorted[$si];
	$page = substr($blob, (int) $ref['start'], (int) $ref['len']);
	$title = '?';
	if (preg_match('/<title>([^<]*)<\/title>/', $page, $m)) {
		$title = html_entity_decode($m[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}
	$origIdx = -1;
	foreach ($pages as $k => $pr) {
		if ($pr['start'] === $ref['start'] && $pr['len'] === $ref['len']) {
			$origIdx = $k;
			break;
		}
	}
	$fname = $prefix . 'page_' . ($i + 1) . '.xml';
	file_put_contents($outDir . '/' . $fname, $page);
	echo 'SAMPLE ' . ($i + 1) . ($sliceLimit > 0 ? " (first {$n} title-sorted pages)" : ' (full corpus)') . "\n";
	echo 'title: ' . $title . "\n";
	echo 'sorted_idx=' . $si . ' orig_idx=' . $origIdx . ' bytes=' . strlen($page) . "\n";
	echo 'path: ' . $outDir . '/' . $fname . "\n";
	echo "--- first 600 chars ---\n";
	echo substr($page, 0, 600) . "\n\n";
}
