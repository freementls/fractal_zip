#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Stage sizes for enwik8 textcodec FZEP restore (chunk assembly vs EZT vs semantic).
 *
 * Usage: php benchmarks/diagnose_enwik8_textcodec_restore.php [test_files109.fz]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$fzcPath = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'test_files109.fz');
$srcPath = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($fzcPath) || !is_file($srcPath)) {
	fwrite(STDERR, "Missing fzc or source enwik8\n");
	exit(1);
}

$rawBytes = (int) filesize($srcPath);
$blob = (string) file_get_contents($fzcPath);
$meta = fractal_zip_enwik_peel_fzep_from_blob($blob);
if ($meta === null) {
	fwrite(STDERR, "No FZEP trailer\n");
	exit(1);
}

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_diag109_' . getmypid();
@mkdir($work, 0755, true);
copy($fzcPath, $work . DIRECTORY_SEPARATOR . 't.fz');
// Keep virtual page members on disk for manual staged restore (default reassemble deletes them).
putenv('FRACTAL_ZIP_ENWIK_SKIP_REASSEMBLE=1');
$fz = new fractal_zip();
$fz->open_container($work . DIRECTORY_SEPARATOR . 't.fz', false);
putenv('FRACTAL_ZIP_ENWIK_SKIP_REASSEMBLE');

$extractRoot = $work;
$outPath = $work . DIRECTORY_SEPARATOR . 'enwik8';
$header = (string) $meta['header'];
$footer = (string) $meta['footer'];
$chunked = enwik_restore_blob(
	$header,
	$footer,
	(array) $meta['memberRelPaths'],
	(array) $meta['origIndexBySorted'],
	(int) $meta['pagesPerMember'],
	$extractRoot,
	(array) ($meta['sortedPageLens'] ?? array())
);
$eztCount = substr_count($chunked, '~EZT');
fwrite(STDERR, "[diag] raw={$rawBytes} header=" . strlen($header) . ' footer=' . strlen($footer) . "\n");
fwrite(STDERR, '[diag] after chunks=' . strlen($chunked) . " ezt_tokens={$eztCount}\n");

$restored = $chunked;
$bulkPath = $extractRoot . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_codec.eztb';
$sharedVocab = null;
$semanticDict = (string) ($meta['semanticPackDict'] ?? '');
if ($semanticDict !== '') {
	$sharedVocab = fractal_zip_enwik_text_codec_vocab_from_dict_patterns(
		fractal_zip_enwik_phrase_pack_parse_dict($semanticDict)
	);
}
if (is_file($bulkPath)) {
	$bulk = (string) file_get_contents($bulkPath);
	$restored = fractal_zip_enwik_text_codec_restore_eztokens_in_blob($restored, $bulk, $sharedVocab);
	$eztLeft = substr_count($restored, '~EZT');
	fwrite(STDERR, '[diag] after EZT=' . strlen($restored) . " ezt_left={$eztLeft}\n");
}
if ($semanticDict !== '') {
	$before = strlen($restored);
	$restored = fractal_zip_enwik_semantic_pack_restore($restored, $semanticDict);
	fwrite(STDERR, '[diag] after semantic=' . strlen($restored) . ' delta=' . (strlen($restored) - $before) . "\n");
}

$delta = strlen($restored) - $rawBytes;
fwrite(STDERR, '[diag] final=' . strlen($restored) . " delta_vs_raw={$delta}\n");
if ($delta !== 0) {
	fwrite(STDERR, "[diag] FAIL size mismatch\n");
	fractal_zip_enwik_recursive_remove($work);
	exit(1);
}
fractal_zip_enwik_recursive_remove($work);
exit(0);
