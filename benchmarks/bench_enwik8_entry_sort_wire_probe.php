#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * 384-page wire A/B: entry sort on vs off (pp96, no textcodec).
 *
 * Usage: php -d memory_limit=2048M benchmarks/bench_enwik8_entry_sort_wire_probe.php
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$pageLimit = 384;
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
	$slice .= substr($blob, (int) $pages[$i]['start'], (int) $pages[$i]['len']);
}
$slice .= $footer;

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_esort_' . getmypid();
@mkdir($tmp, 0700, true);
file_put_contents($tmp . DIRECTORY_SEPARATOR . 'enwik8', $slice);

$cases = array(
	'entry_sort_on' => '1',
	'entry_sort_off' => '0',
);

$rows = array();
foreach ($cases as $label => $es) {
	bench_world_record_apply_pp96_core_env();
	putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
	putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=' . $es);
	putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');
	$work = $tmp . DIRECTORY_SEPARATOR . $label;
	@mkdir($work, 0700, true);
	copy($tmp . DIRECTORY_SEPARATOR . 'enwik8', $work . DIRECTORY_SEPARATOR . 'enwik8');
	$fzc = $work . '.fz';
	@unlink($fzc);
	$t0 = microtime(true);
	$fz = new fractal_zip();
	$fz->zip_folder($work, false);
	$rows[] = array(
		'label' => $label,
		'entry_sort' => $es,
		'fzc_bytes' => is_file($fzc) ? (int) filesize($fzc) : 0,
		'zip_seconds' => round(microtime(true) - $t0, 2),
		'outer_codec' => fractal_zip::$last_outer_codec ?? null,
	);
	@unlink($fzc);
}

fractal_zip_enwik_recursive_remove($tmp);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_entry_sort_wire_probe.json';
file_put_contents($path, json_encode(array('generated' => date('c'), 'pages' => $n, 'rows' => $rows), JSON_PRETTY_PRINT));

echo "entry_sort wire probe → {$path}\n";
foreach ($rows as $r) {
	echo '  ' . $r['label'] . '  ' . number_format((int) $r['fzc_bytes']) . ' B  ' . $r['zip_seconds'] . "s\n";
}
