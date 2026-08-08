#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: real test_files109/enwik8 splits (100 MB truncated corpus) and lossless unsorted reassemble.
 *
 * Usage: php benchmarks/smoke_enwik8_corpus_parse.php
 */

$repo = dirname(__DIR__);
$corpus = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($corpus)) {
	fwrite(STDERR, "SKIP smoke_enwik8_corpus_parse: missing {$corpus}\n");
	exit(0);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$blob = file_get_contents($corpus);
if (!is_string($blob) || strlen($blob) !== 100000000) {
	fwrite(STDERR, 'FAIL: expected 100000000 B enwik8, got ' . (is_string($blob) ? strlen($blob) : 0) . "\n");
	exit(1);
}

$t0 = microtime(true);
$split = enwik_split_pages($blob);
if ($split === null) {
	fwrite(STDERR, "FAIL: enwik_split_pages returned null on real enwik8\n");
	exit(1);
}
$pageCount = count($split['pages']);
if ($pageCount < 10000) {
	fwrite(STDERR, "FAIL: suspicious page count {$pageCount}\n");
	exit(1);
}

$unsorted = $split['header'];
foreach ($split['pages'] as $p) {
	$unsorted .= (string) $p['bytes'];
}
$unsorted .= (string) $split['footer'];
if ($unsorted !== $blob) {
	fwrite(STDERR, 'FAIL: unsorted reassemble mismatch len ' . strlen($unsorted) . "\n");
	exit(1);
}

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=64');
$prep = fractal_zip_enwik_try_prepare_virtual_folder($repo . DIRECTORY_SEPARATOR . 'test_files109');
if ($prep === null) {
	fwrite(STDERR, "FAIL: virtual folder prep null on test_files109\n");
	exit(1);
}
$members = count($prep['memberRelPaths']);
if ($members < 2) {
	fwrite(STDERR, "FAIL: expected member_count > 1, got {$members}\n");
	exit(1);
}

$packed = enwik_pack_permutation_meta($prep);
$meta = fractal_zip_enwik_peel_fzep_from_blob($packed);
if ($meta === null || count($meta['sortedPageLens'] ?? array()) !== $pageCount) {
	fwrite(STDERR, "FAIL: FZEP v2 pack/peel on real enwik8\n");
	exit(1);
}
$restored = enwik_restore_blob(
	(string) $prep['header'],
	(string) $prep['footer'],
	(array) $prep['memberRelPaths'],
	(array) $prep['origIndexBySorted'],
	(int) $prep['pagesPerMember'],
	(string) $prep['virtualDir'],
	(array) $prep['sortedPageLens']
);
if ($restored !== $blob) {
	fwrite(STDERR, 'FAIL: chunked lens restore mismatch len ' . strlen($restored) . "\n");
	exit(1);
}

fractal_zip_enwik_cleanup_virtual_folder($prep);

$dt = microtime(true) - $t0;
fwrite(STDERR, "OK smoke_enwik8_corpus_parse pages={$pageCount} members={$members} {$dt}s\n");
exit(0);
