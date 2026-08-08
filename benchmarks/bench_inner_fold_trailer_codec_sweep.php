#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare inner-fold trailer codecs on dict/stat payloads (content-tailored outers).
 *
 * Usage: php benchmarks/bench_inner_fold_trailer_codec_sweep.php [--pages=96]
 */

$repo = dirname(__DIR__);
$pageLimit = 96;
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
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
putenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';

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

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_fold_codec_' . getmypid();
@mkdir($tmp, 0700, true);
file_put_contents($tmp . DIRECTORY_SEPARATOR . 'enwik8', $slice);

$preprocessCases = array(
	'stat_pred_inner' => static function (): void {
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred_inner');
	},
	'dict_phda9_inner' => static function () use ($repo): void {
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_phda9_inner');
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
		if (is_file($dict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
	},
);

$codecSets = array(
	'gzip_only' => 'gzip9',
	'brotli_dict' => 'brotli11:gzip9',
	'zstd_dict' => 'zstd22:gzip9:brotli11',
	'all' => 'gzip9:brotli11:zstd22',
);

$rows = array();
foreach ($preprocessCases as $label => $applyPre) {
	foreach ($codecSets as $codecLabel => $codecs) {
		putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_CODECS=' . $codecs);
		$applyPre();
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
		$rows[] = array(
			'preprocess' => $label,
			'codec_set' => $codecLabel,
			'codecs' => $codecs,
			'packed_bytes' => (int) ($stats['inner_fold_packed_bytes'] ?? 0),
			'wire_bytes' => (int) ($stats['inner_fold_trailer_bytes'] ?? 0),
			'trailer_codec' => (string) ($stats['inner_fold_trailer_codec'] ?? '?'),
			'fold_dict_bytes' => (int) ($stats['fold_dict_bytes'] ?? 0),
			'fold_stat_bytes' => (int) ($stats['fold_stat_bytes'] ?? 0),
		);
		fractal_zip_enwik_recursive_remove($tmp);
		@mkdir($tmp, 0700, true);
		file_put_contents($tmp . DIRECTORY_SEPARATOR . 'enwik8', $slice);
	}
}

usort($rows, static function (array $a, array $b): int {
	return ((int) ($a['wire_bytes'] ?? PHP_INT_MAX)) <=> ((int) ($b['wire_bytes'] ?? PHP_INT_MAX));
});

$report = array('pages' => $n, 'rows' => $rows, 'best' => $rows[0] ?? null);
echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
fractal_zip_enwik_recursive_remove($tmp);
