#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Decompose honest wire .fz bytes for mono_mi vs stat_pred_inner variants @384p slice.
 *
 * Prints: wire total, inner-fold trailer (meta), implied member+outer, build stats, per-member sizes.
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks/bench_world_record_env.php';

$pageLimit = 384;
$preprocess = 'none';
$layout = 'mi_reorder';
$sparse = false;

foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(1, (int) substr($arg, 8));
	}
	if (str_starts_with($arg, '--preprocess=')) {
		$preprocess = substr($arg, 13);
	}
	if (str_starts_with($arg, '--layout=')) {
		$layout = substr($arg, 9);
	}
	if ($arg === '--sparse') {
		$sparse = true;
	}
}

if ($sparse) {
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV=4096');
}
if ($preprocess === 'stat_pred_inner') {
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred_inner');
	putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
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
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$n = min($pageLimit, count($split['pages']));
$rawBytes = 0;
for ($i = 0; $i < $n; $i++) {
	$rawBytes += (int) $split['pages'][$i]['len'];
}

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_byte_decomp_' . getmypid();
@mkdir($tmp, 0700, true);
$work = $tmp . DIRECTORY_SEPARATOR . 'work';
@mkdir($work, 0700, true);
copy($src, $work . DIRECTORY_SEPARATOR . 'enwik8');
$fzc = $work . '.fz';
@unlink($fzc);

$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array();
$t0 = microtime(true);
$fz = new fractal_zip();
$fz->zip_folder($work, false);
$wire = is_file($fzc) ? (int) filesize($fzc) : 0;
$elapsed = round(microtime(true) - $t0, 2);
$stats = fractal_zip_enwik_text_inner_last_build_stats();

$metaWire = (int) ($stats['meta_bytes'] ?? 0);
$foldRaw = (int) ($stats['fold_stat_bytes'] ?? 0) + (int) ($stats['fold_dict_bytes'] ?? 0);
$memberOuter = $wire - $metaWire;
$fullPages = 12041;
$amortTax = $metaWire > 0 ? $metaWire : $foldRaw;
$amortized = $wire;
if ($amortTax > 0) {
	$amortized = (int) round($wire - $amortTax + ($amortTax * ($n / $fullPages)));
}

// Per-member sizes inside outer payload (literal bundle decode).
$memberSizes = array();
if (is_file($fzc)) {
	$contents = (string) file_get_contents($fzc);
	$decoded = $fz->decode_container_payload($contents);
	if (is_array($decoded) && isset($decoded[0]) && is_array($decoded[0])) {
		foreach ($decoded[0] as $path => $payload) {
			$memberSizes[(string) $path] = strlen((string) $payload);
		}
	}
}

echo "preprocess={$preprocess}" . ($sparse ? ' sparse4096' : '') . " pages={$n} raw_xml={$rawBytes}\n";
echo "wire_fzc={$wire} zip_seconds={$elapsed} outer_codec=" . (fractal_zip::$last_outer_codec ?? '?') . "\n";
echo "meta_wire={$metaWire} fold_raw={$foldRaw} member_plus_outer={$memberOuter}\n";
echo "amortized_fzc={$amortized} amort_tax_slice=" . ($amortTax - (int) round($amortTax * $n / $fullPages)) . "\n";
echo "bigram_hits=" . (int) ($stats['bigram_hits'] ?? 0)
	. " literal_words=" . (int) ($stats['literal_words'] ?? 0)
	. " vocab_size=" . (int) ($stats['vocab_size'] ?? 0) . "\n";
if (isset($stats['inner_fold_trailer_codec'])) {
	echo "trailer_codec=" . (string) $stats['inner_fold_trailer_codec']
		. " fold_packed=" . (int) ($stats['inner_fold_packed_bytes'] ?? 0) . "\n";
}
echo "members (" . count($memberSizes) . "):\n";
arsort($memberSizes, SORT_NUMERIC);
foreach ($memberSizes as $path => $sz) {
	echo '  ' . $path . '=' . number_format($sz) . "\n";
}

@unlink($fzc);
fractal_zip_enwik_recursive_remove($tmp);
