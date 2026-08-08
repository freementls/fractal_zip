#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Entry-sorted enwik8 slice: .fz with textcodec on vs off (real wire path).
 *
 * Usage:
 *   php benchmarks/bench_enwik8_wire_slice_probe.php [--pages=384]
 *   FRACTAL_ZIP_LOW_MEMORY=1 php benchmarks/bench_enwik8_wire_slice_probe.php
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();
register_shutdown_function(static function () use ($repo): void {
	fractal_zip_process_guard_sweep_strays($repo);
});
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_low_memory_env.php';
if (bench_low_memory_enabled()) {
	bench_low_memory_apply_wire_probe_env();
	putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
} else {
	bench_wire_probe_apply_parallel_speed_env();
}

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
$pageLimit = 384;
$caseFilter = array();
$outJson = null;
$verifyRt = false;
$outerMode = 'auto';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	} elseif (str_starts_with($arg, '--out-json=')) {
		$outJson = substr($arg, 11);
	} elseif ($arg === '--verify-rt') {
		$verifyRt = true;
	} elseif (str_starts_with($arg, '--outer=')) {
		$outerMode = strtolower(trim(substr($arg, 8)));
	} elseif (str_starts_with($arg, '--case=')) {
		$caseFilter[] = substr($arg, 7);
	} elseif (str_starts_with($arg, '--cases=')) {
		foreach (explode(',', substr($arg, 8)) as $c) {
			$c = trim($c);
			if ($c !== '') {
				$caseFilter[] = $c;
			}
		}
	}
}
if (!is_file($src)) {
	fwrite(STDERR, "Missing {$src}\n");
	exit(1);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
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

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_enwik_slice_' . getmypid();
@mkdir($tmp, 0700, true);
$enwikPath = $tmp . DIRECTORY_SEPARATOR . 'enwik8';
file_put_contents($enwikPath, $slice);

$wire_probe_mono_mi_stack_passthrough = static function (string $stackId): void {
	bench_world_record_apply_pp96_core_env();
	bench_wire_probe_reset_stat_preprocess_env();
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=' . $stackId);
	putenv('FRACTAL_ZIP_STACKED_OUTER=1');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
};

$wire_probe_stat_pred_variation = static function (string $preprocessId, array $extra = array()): void {
	bench_wire_probe_apply_mono_mi_fztx_base_env();
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=' . $preprocessId);
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');
	// Full fractal outer on trailer (FAST=1 regresses sealed meta ~47 KiB @384p mono_concat).
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
	putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=base94');
	foreach ($extra as $k => $v) {
		putenv($k . '=' . $v);
	}
};

$wire_probe_phda9_lstm_preprocess = static function (string $preprocessId): void {
	bench_world_record_apply_pp96_core_env();
	bench_wire_probe_reset_stat_preprocess_env();
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=');
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=' . $preprocessId);
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
	putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
	putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
};

$enwik8FullPages = 12041;

/** @param 'phda9'|'phda9_no_lstm' $tool */
function bench_wire_probe_apply_words4096_dict(string $repo): void
{
	$pages = max(32, (int) (getenv('FRACTAL_ZIP_WIRE_PROBE_PAGES') ?: 384));
	$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR
		. '.phda9_external_dict_words_' . $pages . 'p.txt';
	if (!is_file($dict)) {
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR
			. '.phda9_external_dict_words_4096p.txt';
	}
	if (!is_file($dict)) {
		throw new RuntimeException('missing words4096 dict: ' . $dict);
	}
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

function bench_wire_probe_apply_phda9_xml_single_stream(string $repo, string $tool = 'phda9'): void
{
	bench_world_record_apply_pp96_core_env();
	bench_wire_probe_reset_stat_preprocess_env();
	$dict = bench_wire_probe_phda9_dict_path($repo);
	if (is_file($dict)) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
	}
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $tool);
	if ($tool === 'phda9_no_lstm') {
		putenv('FRACTAL_ZIP_PAQ=' . $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9_no_LSTM');
	}
	putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
	putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
}

function bench_wire_probe_apply_cfabb_inline_env(): void
{
	putenv('FRACTAL_ZIP_CFABB_TABLE=');
	putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
	putenv('FRACTAL_ZIP_CFABB_INLINE=1');
	putenv('FRACTAL_ZIP_CFABB_NO_FOLD=1');
	putenv('FRACTAL_ZIP_CFABB_SA=1');
	putenv('FRACTAL_ZIP_CFABB_PHDA9_GATE=1');
}

/** Full outer + fz inner tournament on sidecar / inner-fold trailers. */
function bench_wire_probe_apply_sidecar_push_env(): void
{
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=1');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_DEEP=0');
	putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_CODECS=zpaq9:zstd22:brotli11:gzip9');
	putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_STACK_CODECS=');
}

/** Lab-only: stacked zpaq+zstd + deep fractal (hours-scale on large sidecars). */
function bench_wire_probe_apply_sidecar_push_lab_env(): void
{
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=1');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=0');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_DEEP=1');
	putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_CODECS=zpaq9:zstd22:brotli11:gzip9');
	putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_STACK_CODECS=zpaq9:zstd22');
}

function bench_wire_probe_apply_phda9_dict_fold_env(): void
{
	putenv('FRACTAL_ZIP_PHDA9_DICT_FOLD=1');
	bench_wire_probe_apply_sidecar_push_env();
}

/** @param 'phda9'|'phda9_no_lstm' $tool */
function bench_wire_probe_apply_phda9_article_single_stream(string $repo, string $tool = 'phda9'): void
{
	bench_world_record_apply_pp96_core_env();
	bench_wire_probe_reset_stat_preprocess_env();
	$dict = bench_wire_probe_phda9_dict_path($repo);
	if (is_file($dict)) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
	}
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_article');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=' . $tool);
	if ($tool === 'phda9_no_lstm') {
		putenv('FRACTAL_ZIP_PAQ=' . $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9_no_LSTM');
	}
	putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
	putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
}

function bench_wire_probe_apply_dict_inline_tokenize_env(string $repo): void
{
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE=1');
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_MODE=tokenize');
	$vocab = bench_wire_probe_phda9_dict_path($repo);
	if (is_file($vocab)) {
		putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_VOCAB=' . $vocab);
	}
}

function bench_wire_probe_apply_dict_inline_refine_env(string $repo): void
{
	bench_wire_probe_apply_dict_inline_temp_env($repo);
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_REFINE=1');
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_REFINE_TRIALS=16');
}

function bench_wire_probe_apply_dict_inline_temp_env(string $repo): void
{
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE=1');
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_MODE=temp');
	$vocab = bench_wire_probe_phda9_dict_path($repo);
	if (is_file($vocab)) {
		putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_VOCAB=' . $vocab);
	}
}

/** @param 'auto'|'zstd'|'zpaq'|'7z'|'gzip' $mode */
function bench_wire_probe_apply_outer_mode(string $mode): void
{
	putenv('FRACTAL_ZIP_FORCE_OUTER');
	putenv('FRACTAL_ZIP_SKIP_ZPAQ');
	putenv('FRACTAL_ZIP_SKIP_7Z');
	putenv('FRACTAL_ZIP_SKIP_ARC');
	putenv('FRACTAL_ZIP_SKIP_XZ');
	switch ($mode) {
		case 'zstd':
			putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
			putenv('FRACTAL_ZIP_SKIP_7Z=1');
			putenv('FRACTAL_ZIP_SKIP_ARC=1');
			putenv('FRACTAL_ZIP_SKIP_XZ=1');
			break;
		case 'zpaq':
			putenv('FRACTAL_ZIP_FORCE_OUTER=zpaq');
			putenv('FRACTAL_ZIP_SKIP_ZPAQ=0');
			putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
			break;
		case '7z':
			putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
			putenv('FRACTAL_ZIP_SKIP_7Z=0');
			putenv('FRACTAL_ZIP_SKIP_ARC=1');
			break;
		case 'gzip':
			putenv('FRACTAL_ZIP_FORCE_OUTER=gzip');
			putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
			putenv('FRACTAL_ZIP_SKIP_7Z=1');
			break;
		case 'auto':
		default:
			putenv('FRACTAL_ZIP_SKIP_ZPAQ=0');
			putenv('FRACTAL_ZIP_SKIP_7Z=0');
			putenv('FRACTAL_ZIP_SKIP_ARC=0');
			putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
			break;
	}
}

/** Match words4096 baseline outer (zstd wins on small sorted-xml inners). */
function bench_wire_probe_apply_zstd_outer_bias(): void
{
	putenv('FRACTAL_ZIP_SKIP_ZPAQ=1');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
}

/** Resolve phda9 dict for wire probes; honors FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT override. */
function bench_wire_probe_phda9_dict_path(string $repo): string
{
	$override = getenv('FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT');
	if ($override !== false && trim((string) $override) !== '') {
		$path = trim((string) $override);
		if (is_file($path)) {
			return $path;
		}
	}
	$words4096 = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
	if (is_file($words4096)) {
		return $words4096;
	}
	$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
	if (!is_file($dict)) {
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
	}
	return $dict;
}

/** Prefer phda9-gated frozen consonant model when present. */
function bench_wire_probe_consonant_model_json_path(string $repo): string
{
	$pages = max(32, (int) (getenv('FRACTAL_ZIP_WIRE_PROBE_PAGES') ?: 384));
	$bench = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR;
	foreach (array(
		'.enwik8_consonant_model_' . $pages . 'p_phda9.json',
		'.enwik8_consonant_model_' . $pages . 'p.json',
		'.enwik8_consonant_model_384p_phda9.json',
		'.enwik8_consonant_model_384p.json',
	) as $name) {
		$candidate = $bench . $name;
		if (is_file($candidate)) {
			return $candidate;
		}
	}
	return '';
}

function bench_wire_probe_apply_consonant_model_json(string $repo): void
{
	$path = bench_wire_probe_consonant_model_json_path($repo);
	if ($path !== '') {
		putenv('FRACTAL_ZIP_CONSONANT_MODEL_JSON=' . $path);
	}
}

/** Prefer phda9-gated cfabb table for the probe slice page count. */
function bench_wire_probe_cfabb_table_path(string $repo): string
{
	$pages = max(32, (int) (getenv('FRACTAL_ZIP_WIRE_PROBE_PAGES') ?: 384));
	$bench = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR;
	foreach (array(
		'.enwik8_cfabb_table_' . $pages . 'p_sa_phda9.json',
		'.enwik8_cfabb_table_' . $pages . 'p_phda9.json',
		'.enwik8_cfabb_table_' . $pages . 'p_filtered.json',
		'.enwik8_cfabb_table_' . $pages . 'p.json',
		'.enwik8_cfabb_table_384p_phda9.json',
		'.enwik8_cfabb_table_384p_filtered.json',
		'.enwik8_cfabb_table_384p.json',
		'.enwik8_cfabb_table.json',
	) as $name) {
		$candidate = $bench . $name;
		if (is_file($candidate)) {
			return $candidate;
		}
	}
	return '';
}

$cases = array(
	'no_textcodec' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'no_textcodec_corpus' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
		putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
		putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'isp_none' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94_isp');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=none');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'isp_sort_lines' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94_isp');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=sort_lines_alpha');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_isp' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=isp_varint');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi_isp' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=isp_varint');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi_corpus' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
		putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
		putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi_word' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_ENWIK_WORD_PACK=1');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi_semantic' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_ENWIK_SEMANTIC_PACK=1');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'entry_sort_off_text_inner_mi' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=0');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi_corpus_word' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
		putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
		putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
		putenv('FRACTAL_ZIP_ENWIK_WORD_PACK=1');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi_brotli' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=0');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_text_inner_mi_stack' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=dual');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_brotli11');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mi' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mi_stack' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_brotli11');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stack_passthrough' => static function () use ($wire_probe_mono_mi_stack_passthrough): void {
		$wire_probe_mono_mi_stack_passthrough('zpaq9_brotli11');
	},
	'split_inner_fztx_mono_mi_stack_pt_zstd22' => static function () use ($wire_probe_mono_mi_stack_passthrough): void {
		$wire_probe_mono_mi_stack_passthrough('zpaq9_zstd22');
	},
	'split_inner_fztx_mono_mi_stack_pt_7z' => static function () use ($wire_probe_mono_mi_stack_passthrough): void {
		$wire_probe_mono_mi_stack_passthrough('zpaq9_7z');
	},
	'split_inner_phda9_xml_mono_mi' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=3600');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_pp96_parallel' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=0');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		// jobs: unset = auto from MemAvailable (~1.1 GiB/worker). Override: FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=3
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=3600');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_pp96' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=');
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=3600');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=');
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_dict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt');
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_pp96_mixed_dict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . bench_wire_probe_phda9_dict_path($repo));
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_dict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
		if (!is_file($dict)) {
			throw new RuntimeException('missing words_4096p dict: ' . $dict);
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_refined_dict' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		$pages = max(32, (int) (getenv('FRACTAL_ZIP_WIRE_PROBE_PAGES') ?: 384));
		$refined = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR
			. '.phda9_external_dict_lstm_seed_refine_' . $pages . 'p.txt';
		if (!is_file($refined)) {
			$refined = bench_wire_probe_phda9_dict_path($repo);
		}
		if (!is_file($refined)) {
			throw new RuntimeException('missing refined dict: ' . $refined);
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $refined);
		putenv('FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT=' . $refined);
	},
	'split_inner_phda9_xml_single_stream_lstm_wiki_lom' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		}
		if (is_file($dict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wiki_lom');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_PAQ=' . $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9_no_LSTM');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
		$cfabb = bench_wire_probe_cfabb_table_path($repo);
		if ($cfabb !== '') {
			putenv('FRACTAL_ZIP_CFABB_TABLE=' . $cfabb);
			putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
		}
		putenv('FRACTAL_ZIP_CFABB_INLINE=0');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_wiki_lom_cfabb_inline' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		}
		if (is_file($dict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wiki_lom');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_PAQ=' . $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9_no_LSTM');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
		putenv('FRACTAL_ZIP_CFABB_TABLE=');
		putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
		putenv('FRACTAL_ZIP_CFABB_INLINE=1');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=1');
		putenv('FRACTAL_ZIP_CFABB_SA=1');
	},
	'split_inner_phda9_xml_single_stream_lstm_wiki_lom_prize_inline' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		}
		if (is_file($dict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wiki_lom');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_PAQ=' . $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9_no_LSTM');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
		putenv('FRACTAL_ZIP_CFABB_TABLE=');
		putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
		putenv('FRACTAL_ZIP_CFABB_INLINE=1');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=1');
		putenv('FRACTAL_ZIP_CFABB_SA=1');
		putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE=1');
		putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_MODE=temp');
		bench_wire_probe_apply_dict_inline_temp_env($repo);
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_CFABB_PLAIN_XML=1');
		bench_wire_probe_apply_cfabb_inline_env();
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_plain_table' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_CFABB_PLAIN_XML=1');
		putenv('FRACTAL_ZIP_CFABB_INLINE=0');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=0');
		$cfabb = bench_wire_probe_cfabb_table_path($repo);
		if ($cfabb !== '') {
			putenv('FRACTAL_ZIP_CFABB_TABLE=' . $cfabb);
			putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
		}
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_mi_reorder' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_mi_line_stripe' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_line_stripe');
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_siteinfo' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
		putenv('FRACTAL_ZIP_ENWIK_SITEINFO_PACK=1');
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_text_pack' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
		putenv('FRACTAL_ZIP_ENWIK_TEXT_PACK=1');
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_siteinfo_text_pack' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
		putenv('FRACTAL_ZIP_ENWIK_SITEINFO_PACK=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_PACK=1');
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_corpus_phrases' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
	},
	'split_inner_phda9_article_single_stream_lstm_words4096_dict' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_article_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
	},
	'split_inner_phda9_article_single_stream_lstm_words4096_mi_reorder' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_article_single_stream($repo, 'phda9');
		bench_wire_probe_apply_words4096_dict($repo);
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_fold' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = bench_wire_probe_phda9_dict_path($repo);
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		bench_wire_probe_apply_phda9_dict_fold_env();
	},
	'split_inner_phda9_xml_single_stream_lstm_words384_prose_dict_fold' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		$pages = max(32, (int) (getenv('FRACTAL_ZIP_WIRE_PROBE_PAGES') ?: 384));
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR
			. '.phda9_external_dict_words_' . $pages . 'p.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		bench_wire_probe_apply_phda9_dict_fold_env();
	},
	'split_inner_phda9_article_single_stream_lstm_mixed_dict' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_article_single_stream($repo, 'phda9');
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (is_file($dict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
	},
	'split_inner_phda9_article_single_stream_lstm_mixed_dict_fold' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_article_single_stream($repo, 'phda9');
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		bench_wire_probe_apply_phda9_dict_fold_env();
	},
	'split_inner_phda9_xml_single_stream_lstm_wiki_lom_sidecar_push' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wiki_lom');
		bench_wire_probe_apply_sidecar_push_env();
		$dict = bench_wire_probe_phda9_dict_path($repo);
		if (is_file($dict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
	},
	'split_inner_phda9_xml_single_stream_lstm_wiki_lom_cfabb_inline_sidecar_push' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wiki_lom');
		bench_wire_probe_apply_cfabb_inline_env();
		bench_wire_probe_apply_sidecar_push_env();
		$dict = bench_wire_probe_phda9_dict_path($repo);
		if (is_file($dict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		}
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_cfabb_inline_phda9' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_CFABB_PLAIN_XML=1');
		bench_wire_probe_apply_cfabb_inline_env();
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_phda9_refine' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		bench_wire_probe_apply_dict_inline_refine_env($repo);
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_inline' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wiki_lom');
		bench_wire_probe_apply_cfabb_inline_env();
		bench_wire_probe_apply_zstd_outer_bias();
	},
	'split_inner_phda9_xml_single_stream_lstm_words4096_wiki_lom_cfabb_frozen' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wiki_lom');
		$cfabb = bench_wire_probe_cfabb_table_path($repo);
		if ($cfabb !== '') {
			putenv('FRACTAL_ZIP_CFABB_TABLE=' . $cfabb);
			putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
		}
		putenv('FRACTAL_ZIP_CFABB_INLINE=0');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=0');
		bench_wire_probe_apply_zstd_outer_bias();
	},
	'split_inner_phda9_xml_single_stream_lstm_prize_inline' => static function () use ($repo): void {
		bench_wire_probe_apply_phda9_xml_single_stream($repo, 'phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_CFABB_PLAIN_XML=1');
		putenv('FRACTAL_ZIP_CFABB_INLINE=0');
		putenv('FRACTAL_ZIP_CFABB_NO_FOLD=0');
		$cfabb = bench_wire_probe_cfabb_table_path($repo);
		if ($cfabb !== '') {
			putenv('FRACTAL_ZIP_CFABB_TABLE=' . $cfabb);
			putenv('FRACTAL_ZIP_WIKI_LOM_CFABB=1');
		}
		bench_wire_probe_apply_dict_inline_refine_env($repo);
	},
	'split_inner_phda9_xml_single_stream_lstm_qg_text_normalize' => static function () use ($wire_probe_phda9_lstm_preprocess): void {
		$wire_probe_phda9_lstm_preprocess('qg_text_normalize');
	},
	'split_inner_phda9_xml_single_stream_lstm_qg_subword_root' => static function () use ($wire_probe_phda9_lstm_preprocess): void {
		$wire_probe_phda9_lstm_preprocess('qg_subword_root');
	},
	'split_inner_phda9_xml_single_stream_lstm_qg_hybrid_root' => static function () use ($wire_probe_phda9_lstm_preprocess): void {
		$wire_probe_phda9_lstm_preprocess('qg_hybrid_root');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_stat_syllable_isp' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_syllable_isp');
		putenv('FRACTAL_ZIP_STAT_SYLLABLE_ISP_CODEC=base94');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid');
		putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
		putenv('FRACTAL_ZIP_CONSONANT_SK_COLLISION_FREE=1');
		putenv('FRACTAL_ZIP_CONSONANT_SK_BARE=1');
		bench_wire_probe_apply_consonant_model_json($repo);
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_skdict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_consonant_hybrid_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid');
		putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
		putenv('FRACTAL_ZIP_CONSONANT_SK_COLLISION_FREE=1');
		putenv('FRACTAL_ZIP_CONSONANT_SK_BARE=1');
		bench_wire_probe_apply_consonant_model_json($repo);
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_merged_dict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$base = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($base)) {
			$base = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_consonant_merged_best.txt';
		if (!is_file($dict) && is_file($base)) {
			require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict_mine.php';
			$pages = max(32, (int) (getenv('FRACTAL_ZIP_WIRE_PROBE_PAGES') ?: 384));
			$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
			if (is_file($src)) {
				$blob = (string) file_get_contents($src);
				fractal_zip_phda9_dict_build_consonant_merged_dict($blob, $pages, $base, $dict);
			}
		}
		if (!is_file($dict)) {
			$dict = $base;
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid');
		putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
		putenv('FRACTAL_ZIP_CONSONANT_SK_COLLISION_FREE=1');
		putenv('FRACTAL_ZIP_CONSONANT_SK_BARE=1');
		bench_wire_probe_apply_consonant_model_json($repo);
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_split' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid_split');
		putenv('FRACTAL_ZIP_CONSONANT_SK_FREQ_VOCAB=1');
		putenv('FRACTAL_ZIP_CONSONANT_SK_INNER=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_split_dual' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid_split');
		putenv('FRACTAL_ZIP_CONSONANT_SK_FREQ_VOCAB=1');
		putenv('FRACTAL_ZIP_CONSONANT_SK_INNER=none');
		putenv('FRACTAL_ZIP_CONSONANT_SK_DUAL=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_split_dual_skdict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		$skDict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_consonant_split_sk_best.txt';
		if (!is_file($skDict)) {
			require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict_mine.php';
			$pages = max(32, (int) (getenv('FRACTAL_ZIP_WIRE_PROBE_PAGES') ?: 384));
			$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
			if (is_file($src)) {
				$blob = (string) file_get_contents($src);
				fractal_zip_phda9_dict_build_consonant_split_sk_dict($blob, $pages, $skDict, array(
					'include_subwords' => true,
				));
			}
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		if (is_file($skDict)) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT_SK=' . $skDict);
		}
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid_split');
		putenv('FRACTAL_ZIP_CONSONANT_SK_FREQ_VOCAB=1');
		putenv('FRACTAL_ZIP_CONSONANT_SK_INNER=none');
		putenv('FRACTAL_ZIP_CONSONANT_SK_DUAL=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_mixed_dict_consonant_hybrid_lossy' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_mixed_best.txt';
		if (!is_file($dict)) {
			$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
		}
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid_lossy');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_single_stream_lstm_stat_syllable_isp' => static function () use ($wire_probe_phda9_lstm_preprocess): void {
		$wire_probe_phda9_lstm_preprocess('stat_syllable_isp');
		putenv('FRACTAL_ZIP_STAT_SYLLABLE_ISP_CODEC=base94');
	},
	'split_inner_phda9_xml_single_stream_lstm_consonant_hybrid' => static function () use ($wire_probe_phda9_lstm_preprocess): void {
		$wire_probe_phda9_lstm_preprocess('consonant_hybrid');
		putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
	},
	'split_inner_fztx_mono_mi_dict_phda9_inner_384p' => static function (): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_PHDA9_INNER_MINE_FULL=0');
		putenv('FRACTAL_ZIP_PHDA9_INNER_MAX_WORDS=16384');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_phda9_inner');
	},
	'split_inner_phda9_xml_pp48_parallel' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=48');
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=3600');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_xml_mono_mi_dict' => static function () use ($repo): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=3600');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_phda9_article_pp96_parallel' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_article');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
		putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS');
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=3600');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_cycle_inner' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=cycle_inner');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_corpus' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
		putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
		putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_dict' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_nncp');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_dict_inner' => static function (): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_inner');
	},
	'split_inner_fztx_mono_mi_dict_phda9_inner' => static function (): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_PHDA9_INNER_MINE_FULL=0');
		putenv('FRACTAL_ZIP_PHDA9_INNER_MAX_WORDS=16384');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_phda9_inner');
	},
	'split_inner_fztx_mono_mi_dict_phda9_inner_parallel_paq' => static function (): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_PHDA9_INNER_MINE_FULL=0');
		putenv('FRACTAL_ZIP_PHDA9_INNER_MAX_WORDS=16384');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_phda9_inner');
		putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_phda9');
		putenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS=4');
	},
	'split_inner_fztx_mono_mi_parallel_cmix' => static function () use ($repo): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_cmix');
		putenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS=4');
	},
	'split_inner_fztx_mono_mi_parallel_phda9' => static function () use ($repo): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_phda9');
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt');
		putenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS=4');
	},
	'split_inner_fztx_mono_mi_parallel_cmix_force' => static function (): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_cmix');
		putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ_FORCE=1');
		putenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS=4');
	},
	'split_inner_fztx_mono_mi_parallel_phda9_force' => static function () use ($repo): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_phda9');
		putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ_FORCE=1');
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt');
		putenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS=4');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_parallel_cmix' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ' => 'parallel_cmix',
			'FRACTAL_ZIP_PAQ_PARALLEL_JOBS' => '4',
		));
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'split_inner_fztx_mono_concat' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_shootout_paq8px' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS=paq8px');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS=none');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_PARALLEL=0');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=900');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_JOBS=4');
		putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=6');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
		putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_shootout_phda9' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS=phda9');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_STACKS=');
		putenv('FRACTAL_ZIP_PAQ_PARALLEL=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
		putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_shootout' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1');
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_MODELS=');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
		putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_wrt' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=wrt_xwrt');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stat_wrt' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_wrt');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_siteinfo' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_ENWIK_SITEINFO_PACK=1');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'pp96_siteinfo_no_textinner' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_ENWIK_SITEINFO_PACK=1');
		putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
		putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_harmony' => static function (): void {
		bench_world_record_apply_harmony_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_text_pack' => static function (): void {
		bench_world_record_apply_harmony_env();
		putenv('FRACTAL_ZIP_ENWIK_TEXT_PACK=1');
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stat_syllable_isp' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_syllable_isp');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_consonant_hybrid' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=consonant_hybrid');
		putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=varint');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stat_isp' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_isp');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stat_pred' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stat_pred_no_sidecar' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred');
	},
	'split_inner_fztx_mono_mi_stat_pred_compact_meta' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_compact_meta');
	},
	'split_inner_fztx_mono_mi_stat_pred_inner' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner');
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_base94' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC' => 'base94',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_delta' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC' => 'delta',
		));
	},
	'split_inner_fztx_pp96_mi' => static function (): void {
		bench_wire_probe_apply_pp96_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
	},
	'split_inner_fztx_pp96_stat_pred_inner' => static function (): void {
		bench_wire_probe_apply_pp96_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred_inner');
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_8k' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array('FRACTAL_ZIP_STAT_PRED_INNER_MAX_WORDS' => '8192'));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse4096' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse2048' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '2048',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse8192' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '8192',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse_gzip_trailer' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
			'FRACTAL_ZIP_INNER_FOLD_FRACTAL' => '0',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse_prune_encode' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
			'FRACTAL_ZIP_STAT_PRED_INNER_PRUNE_ENCODE' => '1',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse2048' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '2048',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse8192' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '8192',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse8k' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_MAX_WORDS' => '8192',
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_frozen_file' => static function () use ($wire_probe_stat_pred_variation, $repo): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE' => $repo . DIRECTORY_SEPARATOR . 'benchmarks'
				. DIRECTORY_SEPARATOR . '.stat_pred_inner_model.fzpm',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse_frozen' => static function () use ($wire_probe_stat_pred_variation): void {
		$repo = dirname(__DIR__);
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE' => $repo . DIRECTORY_SEPARATOR . 'benchmarks'
				. DIRECTORY_SEPARATOR . '.stat_pred_inner_model.fzpm',
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
		));
	},
	'split_inner_fztx_mono_mi_stat_pred_inner_sparse_zpaq_trailer' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
			'FRACTAL_ZIP_INNER_FOLD_FRACTAL' => '1',
			'FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST' => '0',
		));
	},
	'split_inner_fztx_mono_concat_stat_pred_inner' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_sparse' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV' => '4096',
		));
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_gzip_trailer' => static function () use ($wire_probe_stat_pred_variation): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred_inner');
		putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV=4096');
		putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=0');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_stack' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_brotli11');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_stack_brotli' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_brotli11');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_stack_brotli_zpaq' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=brotli11_zpaq9');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_varint' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC' => 'varint',
		));
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_delta' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC' => 'delta',
		));
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_stack_zstd' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_zstd22');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_sealed_pick' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_FZPM_SEALED_PICK' => '1',
		));
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'split_inner_fztx_mono_concat_stat_pred_inner_member_fold' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner', array(
			'FRACTAL_ZIP_STAT_PRED_INNER_MEMBER_FOLD' => '1',
		));
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'entry_sort_off_mono_concat_stat_pred_inner' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred_inner');
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=0');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mono_concat');
	},
	'split_inner_fztx_mono_mi_stat_pred_cap4096' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred', array('FRACTAL_ZIP_STAT_PRED_MAX_WORDS' => '4096'));
	},
	'split_inner_fztx_mono_mi_stat_pred_cap8192' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred', array('FRACTAL_ZIP_STAT_PRED_MAX_WORDS' => '8192'));
	},
	'split_inner_fztx_mono_mi_stat_pred_cap16384' => static function () use ($wire_probe_stat_pred_variation): void {
		$wire_probe_stat_pred_variation('stat_pred', array('FRACTAL_ZIP_STAT_PRED_MAX_WORDS' => '16384'));
	},
	'split_inner_fztx_mono_mi_stat_pred_mi_line_stripe' => static function () use ($wire_probe_stat_pred_variation): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_line_stripe');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred');
	},
	'split_inner_fztx_mono_mi_stat_pred_sort_len' => static function () use ($wire_probe_stat_pred_variation): void {
		bench_wire_probe_apply_mono_mi_fztx_base_env();
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_by_len');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred');
	},
	'split_inner_fztx_mono_mi_stat_sidecar' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR=1');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stack_pt_brotli' => static function () use ($wire_probe_mono_mi_stack_passthrough): void {
		$wire_probe_mono_mi_stack_passthrough('zpaq9_brotli11');
	},
	'split_inner_fztx_mono_mi_stack_zpaq_outer' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_zstd22');
		putenv('FRACTAL_ZIP_STACKED_OUTER=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_stack_brotli_outer' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_brotli11');
		putenv('FRACTAL_ZIP_STACKED_OUTER=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_parallel' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=1');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_fzbm2048' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=2048');
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_inner_combo' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_world_record_apply_inner_combo_recursive0_caps_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_sort_len' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_by_len');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'split_inner_fztx_mono_mi_line_stripe' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		bench_wire_probe_reset_stat_preprocess_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
		putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_line_stripe');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'entry_sort_off' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=0');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'entry_sort_off_corpus' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=0');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
		putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
		putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=0');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
	'entry_sort_off_text_inner_mi_corpus' => static function (): void {
		bench_world_record_apply_pp96_core_env();
		putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=0');
		putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
		putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=1');
		putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
		putenv('FRACTAL_ZIP_ENWIK_HARMONY=0');
		putenv('FRACTAL_ZIP_TEXT_INNER=1');
		putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
		putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
		putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
		putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
		putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
		putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
	},
);

putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
putenv('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96');

if ($caseFilter !== array()) {
	$missing = array();
	foreach ($caseFilter as $want) {
		if (!isset($cases[$want])) {
			$missing[] = $want;
		}
	}
	if ($missing !== array()) {
		fwrite(STDERR, 'Unknown --case: ' . implode(', ', $missing) . "\n");
		fwrite(STDERR, 'Known: ' . implode(', ', array_keys($cases)) . "\n");
		exit(1);
	}
	$cases = array_intersect_key($cases, array_flip($caseFilter));
}

$rows = array();
$monoMiBytes = null;
putenv('FRACTAL_ZIP_WIRE_PROBE_PAGES=' . (string) $n);
foreach ($cases as $label => $apply) {
	bench_wire_probe_reset_stat_preprocess_env();
	bench_wire_probe_apply_outer_mode($outerMode);
	$GLOBALS['fractal_zip_enwik_text_inner_last_build_stats'] = array();
	$apply();
	$work = $tmp . DIRECTORY_SEPARATOR . $label;
	@mkdir($work, 0700, true);
	copy($enwikPath, $work . DIRECTORY_SEPARATOR . 'enwik8');
	$fzc = $work . '.fz';
	@unlink($fzc);
	$t0 = microtime(true);
	$fzcBytes = 0;
	$err = null;
	$memberCount = null;
	$outerCodec = null;
	try {
		$fz = new fractal_zip();
		$fz->zip_folder($work, false);
		$fzcBytes = is_file($fzc) ? (int) filesize($fzc) : 0;
		$outerCodec = fractal_zip::$last_outer_codec ?? null;
		$memberCount = $fz->zip_folder_member_count > 0 ? $fz->zip_folder_member_count : null;
	} catch (Throwable $e) {
		$err = $e->getMessage();
	}
	$buildStats = fractal_zip_enwik_text_inner_last_build_stats();
	$metaBytes = (int) ($buildStats['meta_bytes'] ?? 0);
	$innerFoldWire = (int) ($buildStats['inner_fold_trailer_bytes'] ?? 0);
	$foldBytes = (int) ($buildStats['fold_dict_bytes'] ?? 0)
		+ (int) ($buildStats['fold_stat_bytes'] ?? 0)
		+ (int) ($buildStats['fold_sk_bytes'] ?? 0);
	$phda9DictAmort = 0;
	$amortPhda9Dict = getenv('FRACTAL_ZIP_WIRE_PROBE_AMORT_PHDA9_DICT');
	if ($amortPhda9Dict !== false && in_array(strtolower(trim((string) $amortPhda9Dict)), array('1', 'true', 'on', 'yes'), true)) {
		$dictPath = getenv('FRACTAL_ZIP_WIRE_PROBE_PHDA9_DICT');
		if ($dictPath === false || trim((string) $dictPath) === '') {
			$dictPath = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
		}
		if ($dictPath !== false && is_file(trim((string) $dictPath))) {
			$phda9DictAmort = (int) filesize(trim((string) $dictPath));
		}
	}
	$amortizedFzc = $fzcBytes;
	// Sealed FZEP inner-fold trailer: meta_bytes is already compressed wire tax.
	$foldTaxRaw = ($innerFoldWire > 0 && $metaBytes > 0) ? 0 : $foldBytes;
	$dictTaxExternal = ($innerFoldWire > 0) ? 0 : $phda9DictAmort;
	$amortTax = max($metaBytes, $foldTaxRaw, $dictTaxExternal);
	if ($amortTax > 0) {
		$amortizedFzc = (int) round($fzcBytes - $amortTax + ($amortTax * ($n / $enwik8FullPages)));
	}
	$row = array(
		'label' => $label,
		'raw_bytes' => $rawBytes,
		'pages' => $n,
		'fzc_bytes' => $fzcBytes,
		'wire_fzc' => $fzcBytes,
		'member_plus_outer' => $fzcBytes > 0 ? max(0, $fzcBytes - $metaBytes) : 0,
		'meta_bytes' => $metaBytes,
		'static_meta_bytes' => $amortTax,
		'fold_bytes' => $foldBytes,
		'amortized_fzc' => $amortizedFzc,
		'amortized_model' => 'wire_fzc - amort_tax + amort_tax * (slice_pages/full_pages); tax=max(meta_bytes,fold_bytes,phda9_dict_bytes); full_pages=12041',
		'zip_seconds' => round(microtime(true) - $t0, 2),
		'outer_codec' => $outerCodec,
		'member_count' => $memberCount,
	);
	if ($buildStats !== array()) {
		$row['bigram_hits'] = (int) ($buildStats['bigram_hits'] ?? 0);
		$row['literal_words'] = (int) ($buildStats['literal_words'] ?? 0);
		$row['vocab_size'] = (int) ($buildStats['vocab_size'] ?? 0);
		if (!empty($buildStats['stat_pred_inner_bytes'])) {
			$row['stat_pred_inner_bytes'] = (int) $buildStats['stat_pred_inner_bytes'];
		}
		if (isset($buildStats['trailer_bigram_rows'])) {
			$row['trailer_bigram_rows'] = (int) $buildStats['trailer_bigram_rows'];
		}
		if (isset($buildStats['cfabb_inline_entries'])) {
			$row['cfabb_inline_entries'] = (int) $buildStats['cfabb_inline_entries'];
		}
		if (isset($buildStats['cfabb_fold_meta_bytes'])) {
			$row['cfabb_fold_meta_bytes'] = (int) $buildStats['cfabb_fold_meta_bytes'];
		}
		if (!empty($buildStats['cfabb_inline'])) {
			$row['cfabb_inline'] = true;
		}
		if (isset($buildStats['emitted_prev_count'])) {
			$row['emitted_prev_count'] = (int) $buildStats['emitted_prev_count'];
		}
		if (!empty($buildStats['inner_fold_trailer_codec'])) {
			$row['inner_fold_trailer_codec'] = (string) $buildStats['inner_fold_trailer_codec'];
		}
		if (!empty($buildStats['inner_fold_packed_bytes'])) {
			$row['inner_fold_packed_bytes'] = (int) $buildStats['inner_fold_packed_bytes'];
		}
		if (!empty($buildStats['fold_sk_bytes'])) {
			$row['fold_sk_bytes'] = (int) $buildStats['fold_sk_bytes'];
		}
		if (($buildStats['preprocess'] ?? '') === 'consonant_hybrid') {
			$row['preprocess'] = 'consonant_hybrid';
		}
		if ($phda9DictAmort > 0) {
			$row['phda9_dict_bytes'] = $phda9DictAmort;
		}
		if ($foldBytes > 0) {
			$row['fold_dict_bytes'] = (int) ($buildStats['fold_dict_bytes'] ?? 0);
			$row['fold_stat_bytes'] = (int) ($buildStats['fold_stat_bytes'] ?? 0);
		}
		if ($innerFoldWire > 0) {
			$row['inner_fold_trailer_bytes'] = $innerFoldWire;
		}
		if (!empty($buildStats['phda9_dict_fold_words'])) {
			$row['phda9_dict_fold_words'] = (int) $buildStats['phda9_dict_fold_words'];
			$row['phda9_dict_fold_raw_bytes'] = (int) ($buildStats['phda9_dict_fold_raw_bytes'] ?? 0);
		}
	}
	if ($err !== null) {
		$row['error'] = $err;
	}
	if ($verifyRt && $err === null && $fzcBytes > 0 && is_file($fzc)) {
		$rtWork = $work . DIRECTORY_SEPARATOR . 'rt_' . preg_replace('/[^a-z0-9_]+/i', '_', $label);
		@mkdir($rtWork, 0700, true);
		copy($fzc, $rtWork . DIRECTORY_SEPARATOR . 't.fz');
		$rtOk = false;
		$rtSec = 0.0;
		try {
			$tRt = microtime(true);
			$fx = new fractal_zip();
			$fx->open_container($rtWork . DIRECTORY_SEPARATOR . 't.fz', false);
			$rtSec = microtime(true) - $tRt;
			$gotPath = $rtWork . DIRECTORY_SEPARATOR . 'enwik8';
			$rtOk = is_file($gotPath) && (string) file_get_contents($gotPath) === (string) file_get_contents($enwikPath);
		} catch (Throwable $e) {
			$row['roundtrip_error'] = $e->getMessage();
		}
		$row['roundtrip_ok'] = $rtOk;
		$row['extract_seconds'] = round($rtSec, 2);
		fractal_zip_enwik_recursive_remove($rtWork);
	}
	if ($label === 'split_inner_fztx_mono_mi' && empty($row['error'])) {
		$monoMiBytes = (int) $fzcBytes;
	}
	$rows[] = $row;
	@unlink($fzc);
	// Incremental JSON so long probes expose finished arms before the full matrix completes.
	$partial = array(
		'generated' => date('c'),
		'pages' => $n,
		'enwik8_full_pages' => $enwik8FullPages,
		'amortized_model' => 'wire_fzc - meta_bytes + meta_bytes * (slice_pages/full_pages)',
		'mono_mi_bytes' => $monoMiBytes,
		'rows' => $rows,
		'partial' => true,
	);
	$partialPath = $outJson ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json');
	file_put_contents($partialPath, json_encode($partial, JSON_PRETTY_PRINT));
}

fractal_zip_enwik_recursive_remove($tmp);
$out = array(
	'generated' => date('c'),
	'pages' => $n,
	'enwik8_full_pages' => $enwik8FullPages,
	'amortized_model' => 'wire_fzc - meta_bytes + meta_bytes * (slice_pages/full_pages)',
	'mono_mi_bytes' => $monoMiBytes,
	'rows' => $rows,
	'partial' => false,
);
$path = $outJson ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json');
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo "wire slice probe ({$n} pages) → {$path}\n";
$base = $monoMiBytes;
if ($base === null) {
	foreach ($rows as $r) {
		if ($r['label'] === 'split_inner_fztx_mono_mi' && empty($r['error'])) {
			$base = (int) $r['fzc_bytes'];
			break;
		}
	}
}
if ($base === null) {
	foreach ($rows as $r) {
		if ($r['label'] === 'no_textcodec' && empty($r['error'])) {
			$base = (int) $r['fzc_bytes'];
			break;
		}
	}
}
if ($base === null) {
	$base = (int) ($rows[0]['fzc_bytes'] ?? 0);
}
$statRows = array();
foreach ($rows as $r) {
	if (str_contains($r['label'], 'stat_pred') || isset($r['bigram_hits'])) {
		$statRows[] = $r;
	}
}
if ($statRows !== array()) {
	usort($statRows, static fn (array $a, array $b): int => ((int) ($a['amortized_fzc'] ?? $a['fzc_bytes'])) <=> ((int) ($b['amortized_fzc'] ?? $b['fzc_bytes'])));
	echo "\nstat_pred @{$n}p (ranked by amortized_fzc; mono_mi=" . number_format($base) . " B)\n";
	echo str_pad('variation', 52) . str_pad('wire_fzc', 12, ' ', STR_PAD_LEFT)
		. str_pad('meta', 10, ' ', STR_PAD_LEFT)
		. str_pad('amort', 12, ' ', STR_PAD_LEFT)
		. str_pad('bigram', 10, ' ', STR_PAD_LEFT)
		. str_pad('Δ mono', 10, ' ', STR_PAD_LEFT) . "\n";
	foreach ($statRows as $r) {
		$wire = (int) $r['fzc_bytes'];
		$amort = (int) ($r['amortized_fzc'] ?? $wire);
		$dMono = $base > 0 ? $amort - $base : 0;
		echo str_pad($r['label'], 52)
			. str_pad(number_format($wire), 12, ' ', STR_PAD_LEFT)
			. str_pad(number_format((int) ($r['meta_bytes'] ?? 0)), 10, ' ', STR_PAD_LEFT)
			. str_pad(number_format($amort), 12, ' ', STR_PAD_LEFT)
			. str_pad(number_format((int) ($r['bigram_hits'] ?? 0)), 10, ' ', STR_PAD_LEFT)
			. str_pad(($dMono > 0 ? '+' : '') . number_format($dMono), 10, ' ', STR_PAD_LEFT)
			. "\n";
	}
	echo "  accounting: wire_fzc = honest .fz slice; amortized_fzc assumes frozen table ships once at full 12041p scale\n\n";
}
foreach ($rows as $r) {
	$d = $base > 0 ? (int) $r['fzc_bytes'] - $base : 0;
	echo '  ' . $r['label'] . '  ' . number_format((int) $r['fzc_bytes']) . ' B'
		. ($d !== 0 ? '  (' . ($d > 0 ? '+' : '') . number_format($d) . ' vs no_textcodec)' : '')
		. '  ' . $r['zip_seconds'] . "s\n";
}
