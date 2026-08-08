#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=64');
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';
bench_web_ref_apply_probe_fast_defaults();
putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX=200');
putenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS=15');
putenv('FRACTAL_ZIP_WEB_REF=1');

$dir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'test_files109';
$prep = fractal_zip_enwik_try_prepare_virtual_folder($dir);
if ($prep === null) {
	fwrite(STDERR, "prep failed\n");
	exit(1);
}
$fz = new fractal_zip();
$fz->enwik_zip_ctx = $prep;
$corpus = fractal_zip_web_ref_probe_corpus_from_zip($fz);
fwrite(STDERR, 'corpus_len=' . strlen($corpus) . "\n");
$cands = fractal_zip_web_ref_collect_whole_page_candidates($corpus);
fwrite(STDERR, 'whole_page_candidates=' . count($cands) . "\n");
if ($cands !== array()) {
	$c = $cands[0];
	fwrite(STDERR, 'first_title=' . $c['title'] . ' len=' . strlen((string) $c['piece_bytes']) . "\n");
}
$res = fractal_zip_web_ref_apply_before_recursive_zip($fz);
if ($res === null) {
	fwrite(STDERR, "apply=null\n");
	fractal_zip_enwik_cleanup_virtual_folder($prep);
	exit(0);
}
fwrite(STDERR, 'entries=' . count($res['entries']) . ' saved=' . (int) $res['saved'] . "\n");

$meta = array(
	'header' => (string) ($prep['header'] ?? ''),
	'footer' => (string) ($prep['footer'] ?? ''),
	'memberRelPaths' => (array) ($prep['memberRelPaths'] ?? array()),
	'origIndexBySorted' => (array) ($prep['origIndexBySorted'] ?? array()),
	'pagesPerMember' => (int) ($prep['pagesPerMember'] ?? 1),
	'sortedPageLens' => (array) ($fz->enwik_zip_ctx['sortedPageLens'] ?? $prep['sortedPageLens'] ?? array()),
	'outputFile' => (string) ($prep['outputFile'] ?? 'enwik8'),
);
$origBlob = file_get_contents($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8');
$restored = enwik_restore_blob(
	$meta['header'],
	$meta['footer'],
	$meta['memberRelPaths'],
	$meta['origIndexBySorted'],
	$meta['pagesPerMember'],
	(string) $prep['virtualDir'],
	$meta['sortedPageLens']
);
if (strpos($restored, '@w{') !== false && function_exists('fractal_zip_web_ref_expand_fractal_string')) {
	$GLOBALS['fractal_zip_fzwr_entries'] = $res['entries'];
	$restored = fractal_zip_web_ref_expand_fractal_string($restored);
}
if (!is_string($origBlob) || $restored !== $origBlob) {
	fwrite(STDERR, 'virtual restore mismatch orig=' . (is_string($origBlob) ? strlen($origBlob) : 0) . ' got=' . strlen($restored) . "\n");
	fractal_zip_enwik_cleanup_virtual_folder($prep);
	exit(1);
}
fwrite(STDERR, "virtual restore ok after raw apply\n");

fractal_zip_enwik_cleanup_virtual_folder($prep);
