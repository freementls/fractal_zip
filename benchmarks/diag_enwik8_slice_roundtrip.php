#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Entry-sorted enwik8 slice: zip_folder + staged restore vs source bytes.
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/diag_enwik8_slice_roundtrip.php [--pages=384] [--fzc=path]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pageLimit = 384;
$fzcArg = null;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--fzc=')) {
		$fzcArg = substr($arg, 6);
	}
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
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
$pageBytes = 0;
for ($i = 0; $i < $n; $i++) {
	$pb = substr($blob, (int) $pages[$i]['start'], (int) $pages[$i]['len']);
	$pageBytes += strlen($pb);
	$slice .= $pb;
}
$slice .= $footer;
$expectLen = strlen($slice);
fwrite(STDERR, "[slice] pages={$n} header=" . strlen($header) . " footer=" . strlen($footer)
	. " pages_sum={$pageBytes} total={$expectLen}\n");

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_slice_rt_' . getmypid();
@mkdir($tmp, 0700, true);
$enwikPath = $tmp . DIRECTORY_SEPARATOR . 'enwik8';
file_put_contents($enwikPath, $slice);

$fzcPath = $fzcArg ?? ($tmp . '.fz');
if ($fzcArg === null) {
	bench_world_record_apply_pp96_core_env();
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94_isp');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=none');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');
	@unlink($fzcPath);
	$t0 = microtime(true);
	$fz = new fractal_zip();
	$fz->zip_folder($tmp, false);
	fwrite(STDERR, '[slice] zip_folder ' . round(microtime(true) - $t0, 2) . "s fzc=" . (is_file($fzcPath) ? filesize($fzcPath) : 0) . " B\n");
}

$work = $tmp . DIRECTORY_SEPARATOR . 'extract';
@mkdir($work, 0755, true);
copy($fzcPath, $work . DIRECTORY_SEPARATOR . 't.fz');
putenv('FRACTAL_ZIP_ENWIK_SKIP_REASSEMBLE=1');
$fz2 = new fractal_zip();
$fz2->open_container($work . DIRECTORY_SEPARATOR . 't.fz', false);
putenv('FRACTAL_ZIP_ENWIK_SKIP_REASSEMBLE'); // unset

$meta = fractal_zip_enwik_peel_fzep_from_blob((string) file_get_contents($work . DIRECTORY_SEPARATOR . 't.fz'));
if ($meta === null) {
	fwrite(STDERR, "No FZEP\n");
	exit(1);
}

$chunked = enwik_restore_blob(
	(string) $meta['header'],
	(string) $meta['footer'],
	(array) $meta['memberRelPaths'],
	(array) $meta['origIndexBySorted'],
	(int) $meta['pagesPerMember'],
	$work,
	(array) ($meta['sortedPageLens'] ?? array())
);
$eztCount = substr_count($chunked, '~EZT');
$chunkDelta = strlen($chunked) - $expectLen;
fwrite(STDERR, "[stage] chunked=" . strlen($chunked) . " delta={$chunkDelta} ezt={$eztCount}\n");

$restored = $chunked;
$bulkPath = $work . DIRECTORY_SEPARATOR . 'meta' . DIRECTORY_SEPARATOR . 'text_codec.eztb';
$semanticDict = (string) ($meta['semanticPackDict'] ?? '');
$sharedVocab = null;
if ($semanticDict !== '') {
	$sharedVocab = fractal_zip_enwik_text_codec_vocab_from_dict_patterns(
		fractal_zip_enwik_phrase_pack_parse_dict($semanticDict)
	);
}
if (is_file($bulkPath)) {
	$bulk = (string) file_get_contents($bulkPath);
	$restored = fractal_zip_enwik_text_codec_restore_eztokens_in_blob($restored, $bulk, $sharedVocab);
}
if ($semanticDict !== '') {
	$restored = fractal_zip_enwik_semantic_pack_restore($restored, $semanticDict);
}
$finalDelta = strlen($restored) - $expectLen;
$eztLeft = substr_count($restored, '~EZT');
fwrite(STDERR, "[stage] final=" . strlen($restored) . " delta={$finalDelta} ezt_left={$eztLeft}\n");

if ($restored !== $slice) {
	$len = min(strlen($restored), strlen($slice));
	for ($i = 0; $i < $len; $i++) {
		if ($restored[$i] !== $slice[$i]) {
			fwrite(STDERR, "[fail] first diff at offset {$i}\n");
			break;
		}
	}
	if (strlen($restored) !== strlen($slice)) {
		fwrite(STDERR, "[fail] length mismatch restored=" . strlen($restored) . " expect={$expectLen}\n");
	}
	exit(1);
}

fwrite(STDERR, "[slice] OK byte-exact roundtrip ({$n} pages)\n");
fractal_zip_enwik_recursive_remove($tmp);
exit(0);
