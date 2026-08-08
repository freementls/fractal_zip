#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Entry-sorted enwik8 slice: pre-zpaq inner bytes A/B on real wire path.
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/bench_enwik8_pre_outer_slice_probe.php [--pages=384]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_inner_env.php';

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
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
for ($i = 0; $i < $n; $i++) {
	$slice .= substr($blob, (int) $pages[$i]['start'], (int) $pages[$i]['len']);
}
$slice .= $footer;
$rawBytes = strlen($slice);

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_pre_outer_slice_' . getmypid();
@mkdir($tmp, 0700, true);
$enwikPath = $tmp . DIRECTORY_SEPARATOR . 'enwik8';
file_put_contents($enwikPath, $slice);

$cases = array(
	'pp96_baseline' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	},
	'inner_recursive0' => static function (): void {
		bench_world_record_apply_inner_focus_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	},
	'inner_recursive0_grammar' => static function (): void {
		bench_world_record_apply_inner_focus_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=0');
		putenv('FRACTAL_ZIP_INNER_RUN_GRAMMAR=1');
		putenv('FRACTAL_ZIP_INNER_PEELER_CANDIDATES=1');
		putenv('FRACTAL_ZIP_INNER_RUN_GRAMMAR_MAX_RAW_BYTES=134217728');
		putenv('FRACTAL_ZIP_INNER_PEELER_MAX_TOTAL_RAW_BYTES=134217728');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	},
);

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');

$rows = array();
foreach ($cases as $label => $apply) {
	$apply();
	$work = $tmp . DIRECTORY_SEPARATOR . $label;
	@mkdir($work, 0700, true);
	copy($enwikPath, $work . DIRECTORY_SEPARATOR . 'enwik8');
	$dumpPath = $work . '.inner.bin';
	@unlink($dumpPath);
	putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);
	putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY=1');
	$t0 = microtime(true);
	$fz = new fractal_zip();
	$fz->zip_folder($work, false);
	$sec = microtime(true) - $t0;
	putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
	putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY');
	$inner = is_file($dumpPath) ? (string) file_get_contents($dumpPath) : '';
	$innerBytes = strlen($inner);
	$gz1 = $inner !== '' ? @gzdeflate($inner, 1) : false;
	$innerGz1 = is_string($gz1) ? strlen($gz1) : null;
	@unlink($dumpPath);
	$rows[] = array(
		'label' => $label,
		'raw_bytes' => $rawBytes,
		'pages' => $n,
		'inner_bytes' => $innerBytes,
		'inner_gzip1_bytes' => $innerGz1,
		'zip_seconds' => round($sec, 3),
		'env_snapshot' => bench_world_record_inner_env_snapshot(),
	);
}

fractal_zip_enwik_recursive_remove($tmp);
$out = array('generated' => date('c'), 'pages' => $n, 'rows' => $rows);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_pre_outer_slice_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo "pre_outer slice probe ({$n} pages) → {$path}\n";
$baseInner = (int) ($rows[0]['inner_bytes'] ?? 0);
foreach ($rows as $r) {
	$d = $baseInner > 0 ? (int) $r['inner_bytes'] - $baseInner : 0;
	echo '  ' . $r['label'] . '  inner=' . number_format((int) $r['inner_bytes']) . ' B';
	if ($r['inner_gzip1_bytes'] !== null) {
		echo '  gzip1=' . number_format((int) $r['inner_gzip1_bytes']);
	}
	if ($d !== 0) {
		echo '  (' . ($d > 0 ? '+' : '') . number_format($d) . ' vs pp96_baseline)';
	}
	echo '  ' . $r['zip_seconds'] . "s\n";
}
