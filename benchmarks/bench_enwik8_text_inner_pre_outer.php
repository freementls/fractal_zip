#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Pre-zpaq inner bytes for text-inner mono_mi @slice (honest wire path).
 *
 * Usage:
 *   php -d memory_limit=4096M benchmarks/bench_enwik8_text_inner_pre_outer.php [--pages=384]
 *     [--layout=mi_reorder] [--preprocess=none]
 *     [--out=benchmarks/.enwik8_text_inner_pre_outer.json]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_WEB_REF=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$pageLimit = 384;
$layout = 'mi_reorder';
$preprocess = 'none';
$outRel = 'benchmarks/.enwik8_text_inner_pre_outer.json';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--layout=')) {
		$layout = substr($arg, 9);
	} elseif (str_starts_with($arg, '--preprocess=')) {
		$preprocess = substr($arg, 13);
	} elseif (str_starts_with($arg, '--out=')) {
		$outRel = substr($arg, 6);
	}
}
if ($preprocess === 'stat_pred_inner') {
	putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
}

bench_world_record_apply_pp96_core_env();
putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
putenv('FRACTAL_ZIP_TEXT_INNER=1');
putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=' . $layout);
putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=' . $preprocess);
putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
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

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_ti_pre_outer_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . DIRECTORY_SEPARATOR . 'work';
@mkdir($work, 0700, true);
file_put_contents($work . DIRECTORY_SEPARATOR . 'enwik8', $slice);
$dumpPath = $tmp . DIRECTORY_SEPARATOR . 'inner.bin';
@unlink($dumpPath);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);
$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array();

$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($work, false);
$elapsed = round(microtime(true) - $t0, 2);
putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');

$innerBytes = is_file($dumpPath) ? (int) filesize($dumpPath) : 0;
$innerGz = 0;
if ($innerBytes > 0) {
	$inner = (string) file_get_contents($dumpPath);
	$gz = gzencode($inner, 1);
	$innerGz = is_string($gz) ? strlen($gz) : 0;
}

$fzc = $work . '.fz';
$wire = is_file($fzc) ? (int) filesize($fzc) : 0;

$report = array(
	'generated' => date('c'),
	'pages' => $n,
	'raw_xml_bytes' => $rawBytes,
	'inner_bytes' => $innerBytes,
	'inner_gzip1_bytes' => $innerGz,
	'wire_fzc' => $wire,
	'outer_codec' => fractal_zip::$last_outer_codec ?? null,
	'zip_seconds' => $elapsed,
	'inner_delta_vs_raw' => $innerBytes - $rawBytes,
	'meta_bytes' => (int) ($stats['meta_bytes'] ?? 0),
	'member_plus_outer' => $wire - (int) ($stats['meta_bytes'] ?? 0),
	'bigram_hits' => (int) ($stats['bigram_hits'] ?? 0),
);
$out = $repo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $outRel);
file_put_contents($out, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "layout={$layout} preprocess={$preprocess} pages={$n} raw_xml={$rawBytes} inner={$innerBytes} inner_gzip1={$innerGz} wire={$wire} meta=" . ($report['meta_bytes'] ?? 0) . " member_outer=" . ($report['member_plus_outer'] ?? 0) . " outer=" . ($report['outer_codec'] ?? '?') . " zip_seconds={$elapsed}\n";
echo "→ {$out}\n";

@unlink($dumpPath);
@unlink($fzc);
fractal_zip_enwik_recursive_remove($tmp);
