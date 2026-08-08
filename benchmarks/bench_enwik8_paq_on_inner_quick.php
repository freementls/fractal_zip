#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Quick PAQ/zpaq on mono_mi inner blob @slice. */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pageLimit = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(16, (int) substr($arg, 8));
	}
}

bench_world_record_apply_pp96_core_env();
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
$n = min($pageLimit, count($split['pages']));
$slice = (string) $split['header'];
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $split['pages'][$i]['start'], (int) $split['pages'][$i]['len']);
}
$slice .= (string) $split['footer'];

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_paq_inner_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . DIRECTORY_SEPARATOR . 'w';
@mkdir($work, 0700, true);
file_put_contents($work . DIRECTORY_SEPARATOR . 'enwik8', $slice);
$dump = $tmp . DIRECTORY_SEPARATOR . 'inner.bin';
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dump);
(new fractal_zip())->zip_folder($work, false);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
$inner = is_file($dump) ? (string) file_get_contents($dump) : '';
if ($inner === '') {
	exit(1);
}

$fz = new fractal_zip(256, false, false, null, false);
$zpaq = fractal_zip::zpaq_executable();
$zpaqB = $zpaq !== null ? $fz->outer_zpaq_blob_with_meth_fragment($zpaq, $inner, ' -method 9') : null;
echo 'pages=' . $n . ' inner=' . strlen($inner) . ' zpaq_m9=' . ($zpaqB !== null ? strlen($zpaqB) : 'n/a') . "\n";

$sortedChunk = array();
for ($i = 0; $i < $n; $i++) {
	$sortedChunk[] = array(
		'origIndex' => $i,
		'start' => (int) $split['pages'][$i]['start'],
		'len' => (int) $split['pages'][$i]['len'],
	);
}
$splitPages = enwik_build_text_inner_split_pages_from_refs($sortedChunk, $blob);
$rawText = '';
foreach ($splitPages as $pg) {
	$rawText .= (string) ($pg['text'] ?? '');
}
echo 'raw_text=' . strlen($rawText) . "\n";

foreach (array('fztx_inner' => $inner, 'raw_sorted_text' => $rawText) as $label => $payload) {
	foreach (array('cmix', 'paq8px', 'phda9') as $model) {
		$t0 = microtime(true);
		$r = fractal_zip_text_compressor_run($model, $payload, array(
			'timeout_sec' => (int) (getenv('FZ_PAQ_QUICK_TIMEOUT') ?: 180),
			'wire_wrap' => true,
		));
		$sec = round(microtime(true) - $t0, 2);
		$bytes = is_string($r['payload'] ?? null) ? strlen((string) $r['payload']) : null;
		echo $label . ' ' . $model . ' bytes=' . ($bytes ?? 'fail') . ' rt=' . (!empty($r['roundtrip_ok']) ? 'ok' : 'no')
			. ' sec=' . $sec . ' status=' . ($r['status'] ?? '?') . "\n";
	}
}

fractal_zip_enwik_recursive_remove($tmp);
