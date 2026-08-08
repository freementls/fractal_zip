#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Entry-sorted enwik slice: zip + open_container must match source bytes exactly.
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/verify_enwik_slice_roundtrip.php [--pages=384]
 *   php ... --text-inner --text-inner-promotion   # fztx mono mi_reorder (384p gate config)
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();
register_shutdown_function(static function () use ($repo): void {
	fractal_zip_process_guard_sweep_strays($repo);
});
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
bench_wire_probe_apply_parallel_speed_env();

$pageLimit = 384;
$entrySort = '1';
$textCodec = '0';
$textInner = '0';
$textInnerLayout = 'sort_title';
$textInnerFormat = 'dual';
$textInnerMono = '0';
$textInnerStack = 'none';
$textInnerPreprocess = 'none';
$stackedOuter = '0';
$corpusPhrases = '0';
$wordPack = '0';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(16, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--entry-sort=')) {
		$entrySort = substr($arg, 13) === '0' ? '0' : '1';
	} elseif (str_starts_with($arg, '--text-inner-layout=')) {
		$textInnerLayout = trim(substr($arg, 20));
	} elseif (str_starts_with($arg, '--text-inner-format=')) {
		$textInnerFormat = trim(substr($arg, 20));
	} elseif (str_starts_with($arg, '--text-inner-mono=')) {
		$textInnerMono = substr($arg, 18) === '1' ? '1' : '0';
	} elseif (str_starts_with($arg, '--text-inner-stack=')) {
		$textInnerStack = trim(substr($arg, 19));
	} elseif (str_starts_with($arg, '--text-inner-preprocess=')) {
		$textInnerPreprocess = trim(substr($arg, 24));
	} elseif ($arg === '--text-inner-promotion') {
		$textInner = '1';
		$textInnerLayout = 'mi_reorder';
		$textInnerFormat = 'fztx';
		$textInnerMono = '1';
		$textInnerStack = 'none';
	} elseif ($arg === '--corpus-phrases' || $arg === '--corpus-phrases=1') {
		$corpusPhrases = '1';
	} elseif ($arg === '--word-pack' || $arg === '--word-pack=1') {
		$wordPack = '1';
	} elseif ($arg === '--textcodec' || $arg === '--textcodec=1') {
		$textCodec = '1';
	} elseif ($arg === '--text-inner' || $arg === '--text-inner=1') {
		$textInner = '1';
	} elseif ($arg === '--stacked-outer' || $arg === '--stacked-outer=1') {
		$stackedOuter = '1';
	} elseif ($arg === '--text-inner-stack-passthrough') {
		$textInner = '1';
		$textInnerLayout = 'mi_reorder';
		$textInnerFormat = 'fztx';
		$textInnerMono = '1';
		$textInnerStack = 'zpaq9_brotli11';
		$stackedOuter = '1';
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
$n = min($pageLimit, count($split['pages']));
$slice = (string) $split['header'];
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
}
$slice .= (string) $split['footer'];

bench_world_record_apply_pp96_core_env();
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
if ($textCodec === '1') {
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94_isp');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=none');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1');
} else {
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
}
if ($textInner === '1') {
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=' . ($textInnerLayout !== '' ? $textInnerLayout : 'sort_title'));
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=' . ($textInnerFormat !== '' ? $textInnerFormat : 'dual'));
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=' . $textInnerMono);
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=' . ($textInnerStack !== '' ? $textInnerStack : 'none'));
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=' . ($textInnerPreprocess !== '' ? $textInnerPreprocess : 'none'));
} else {
	putenv('FRACTAL_ZIP_TEXT_INNER=0');
}
putenv('FRACTAL_ZIP_STACKED_OUTER=' . $stackedOuter);
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=' . $corpusPhrases);
putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
putenv('FRACTAL_ZIP_ENWIK_WORD_PACK=' . $wordPack);
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=' . $entrySort);
putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_slice_rt_' . getmypid();
@mkdir($work, 0700, true);
$srcDir = $work . DIRECTORY_SEPARATOR . 'src';
@mkdir($srcDir, 0700, true);
file_put_contents($srcDir . DIRECTORY_SEPARATOR . 'enwik8', $slice);
$fzc = $work . DIRECTORY_SEPARATOR . 'out.fz';

$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($srcDir, false);
$zipSec = microtime(true) - $t0;
if (!is_file($fzc)) {
	$fzc = $srcDir . '.fz';
}
if (!is_file($fzc)) {
	fwrite(STDERR, "FAIL: no fzc produced\n");
	exit(1);
}

$meta = fractal_zip_enwik_peel_trailer_from_fzc($fzc);
$lensSum = is_array($meta['sortedPageLens'] ?? null) ? array_sum($meta['sortedPageLens']) : 0;
if ($entrySort === '1' && $lensSum < $n) {
	fwrite(STDERR, "FAIL: FZEP sortedPageLens empty or missing\n");
	exit(1);
}

$ex = $work . DIRECTORY_SEPARATOR . 'ex';
@mkdir($ex, 0700, true);
copy($fzc, $ex . DIRECTORY_SEPARATOR . 't.fz');
$t1 = microtime(true);
$fx = new fractal_zip();
$fx->open_container($ex . DIRECTORY_SEPARATOR . 't.fz', false);
$extSec = microtime(true) - $t1;

$gotPath = $ex . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($gotPath)) {
	fwrite(STDERR, "FAIL: enwik8 not restored\n");
	exit(1);
}
$got = (string) file_get_contents($gotPath);
$ok = ($got === $slice);
fwrite(STDERR, sprintf(
	"[slice] pages=%d raw=%d fzc=%d zip=%.1fs extract=%.1fs lens_sum=%d %s\n",
	$n,
	strlen($slice),
	(int) filesize($fzc),
	$zipSec,
	$extSec,
	$lensSum,
	$ok ? 'OK' : 'MISMATCH len=' . strlen($got)
));
fractal_zip_enwik_recursive_remove($work);
exit($ok ? 0 : 1);
