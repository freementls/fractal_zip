#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare inner-fold trailer seal sizes (gzip vs fractal raw/deep) without full wire encode.
 *
 * Usage: php benchmarks/bench_inner_fold_trailer_seal.php [--pages=384]
 */

$repo = dirname(__DIR__);
$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
}

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred_inner');
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
putenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
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

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_seal_bench_' . getmypid();
@mkdir($tmp, 0700, true);
file_put_contents($tmp . DIRECTORY_SEPARATOR . 'enwik8', $slice);

$configs = array(
	'fast_default' => array(),
	'full_outer' => array('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST' => '0'),
	'deep_off' => array('FRACTAL_ZIP_INNER_FOLD_FRACTAL_DEEP' => '0'),
);

foreach ($configs as $label => $env) {
	foreach ($env as $k => $v) {
		putenv($k . '=' . $v);
	}
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');
	$sortedChunk = array();
	for ($i = 0; $i < $n; $i++) {
		$sortedChunk[] = array(
			'title' => '',
			'origIndex' => $i,
			'start' => (int) $split['pages'][$i]['start'],
			'len' => (int) $split['pages'][$i]['len'],
			'sortedIndex' => $i,
		);
	}
	enwik_build_text_inner_virtual_folder_from_refs(
		$tmp,
		$sortedChunk,
		$blob,
		(string) $split['header'],
		(string) $split['footer'],
		'enwik8',
		$tmp
	);
	$stats = fractal_zip_enwik_text_inner_last_build_stats();
	echo $label
		. ' packed=' . (int) ($stats['inner_fold_packed_bytes'] ?? 0)
		. ' wire=' . (int) ($stats['inner_fold_trailer_bytes'] ?? 0)
		. ' codec=' . (string) ($stats['inner_fold_trailer_codec'] ?? '?')
		. ' stat_raw=' . (int) ($stats['fold_stat_bytes'] ?? 0)
		. ' bigram_hits=' . (int) ($stats['bigram_hits'] ?? 0)
		. "\n";
	fractal_zip_enwik_recursive_remove($tmp);
	@mkdir($tmp, 0700, true);
	file_put_contents($tmp . DIRECTORY_SEPARATOR . 'enwik8', $slice);
}

fractal_zip_enwik_recursive_remove($tmp);
