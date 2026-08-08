<?php
declare(strict_types=1);

/**
 * World-record / enwik8 preset: ultra + 100 MiB-scale caps + Wikipedia entry sort hooks.
 *
 * Primary metric: min(.fz, best native passthrough). Tuning targets test_files109/enwik8
 * (~12k pages → ~129 virtual members at pages/member=96) or test_files200/enwik9
 * (~243k pages → ~2536 virtual members) when FRACTAL_ZIP_ENWIK_CORPUS=9.
 */
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_high_env.php';

function bench_world_record_putenv_if_unset(string $k, string $v): void
{
	$e = getenv($k);
	if ($e === false || trim((string) $e) === '') {
		putenv($k . '=' . $v);
	}
}

/**
 * Enwik-specific bytes-first overrides (path-order, native zpaq, speed guards).
 * Called automatically from bench_world_record_apply_env_defaults(); override any var before that call.
 */
function bench_world_record_apply_enwik_tuned(): void
{
	$corpus9 = getenv('FRACTAL_ZIP_ENWIK_CORPUS') !== false
		&& trim((string) getenv('FRACTAL_ZIP_ENWIK_CORPUS')) === '9';
	$scaleCap = $corpus9 ? '1073741824' : '134217728';
	$fzbmMembers = $corpus9 ? '4096' : '512';

	// Speed guards: multi-member path-order + native compare must not lose to single-blob fast paths.
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_GZIP_FAST', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PHDA9_GENERAL_FAST', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PHDA9_DAEMON', '1');

	// Native zpaq / outer at full enwik scale (virtual sorted folder + raw single blob).
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ZPAQ_NATIVE_FULL_SWEEP_MAX_RAW_BYTES', $scaleCap);
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ZPAQ_OUTER_SWEEP', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ZPAQ_OUTER_AUTO_SWEEP_MAX_INNER_BYTES', $scaleCap);

	// FZBM merged path-order for ~200 virtual members (title-sorted chunks).
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER', '1');
	// High-tier default: 512 tries matches ultra; pp96_high A/B same fzc, ~3× faster encode vs 128 tries.
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES', '512');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_FZBM_PATH_ORDER_RANDOM_MAX_MEMBERS', $fzbmMembers);
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND', '32');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES', '16777216');

	// Whole-stream FZWS + run-grammar on large text (world-record disables low-textish skip).
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_WHOLE_STREAM_FZWS', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_LOW_TEXTISH_SKIP_RUN_GRAMMAR', '0');
}

/**
 * pp96 world-record topology without bench_world_record_apply_high_env() outer expansion.
 * Inner-first grids use this; production encode_only still uses apply_env_defaults() + high.
 */
function bench_world_record_apply_pp96_core_env(): void
{
	bench_ultra_apply_env_defaults();
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_BENCH_ZPAQ_THREADS', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PAQ_NATIVE_COMPARE', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PAQ_NATIVE_MAX_RAW_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PAQ_SWEEP', '1');
	$phda9Bundled = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9';
	if (is_executable($phda9Bundled)) {
		bench_world_record_putenv_if_unset('FRACTAL_ZIP_PAQ_phda9', $phda9Bundled);
	}
	// Integrated path: techniques inside fz (see docs/ENWIK8_INTEGRATED_COMPRESSION.md).
	// External phda9 FZpq wrap is opt-in only (hours-scale; does not improve sorted inner model).
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE', '0');
	// World-record encode does not use FZWR web refs; track separately (see run_enwik8_web_ref_track.sh).
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_WEB_REF', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_ENTRY_SORT', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER', '96');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PIPELINE_REORDER', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PIPELINE_REORDER_EXT', '1');
	bench_world_record_apply_phda9_xml_promotion_env();
	bench_world_record_apply_enwik_tuned();
}

/**
 * Integrated phda9_xml on sorted page XML (verified full wire 18,848,115 B; −359 KiB vs mono_mi).
 * Wire probes comparing fztx mono_mi override format/layout after {@see bench_world_record_apply_pp96_core_env()}.
 */
function bench_world_record_apply_phda9_xml_promotion_env(): void
{
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_FORMAT', 'phda9_xml');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_MONO', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_LAYOUT', 'sort_title');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_PREPROCESS', 'none');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_STACK', 'none');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL', 'phda9_no_lstm');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PAQ_TIMEOUT_SEC', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC', '7200');
	putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
	putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
	putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
	if (getenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT') !== false) {
		putenv('FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT');
	}
	$dict = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
	if (is_file($dict)) {
		bench_world_record_putenv_if_unset('FRACTAL_ZIP_PAQ_PHDA9_DICT', $dict);
	}
}

function bench_world_record_apply_env_defaults(): void
{
	bench_world_record_apply_pp96_core_env();
	// Outer predict + zpaq ladder + multipass depth (same fzc as base pp96 in high A/B; much faster).
	bench_world_record_apply_high_env();
}

/**
 * Integrated enwik: native reversible dictionaries + existing sort/fractal/zpaq stack.
 * Does not enable external phda9 FZpq wrap (see docs/ENWIK8_INTEGRATED_COMPRESSION.md).
 */
function bench_world_record_apply_harmony_env(): void
{
	bench_world_record_apply_env_defaults();
	putenv('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
	putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
	putenv('FRACTAL_ZIP_ENWIK_HARMONY=1');
	putenv('FRACTAL_ZIP_ENWIK_SITEINFO_PACK=1');
	putenv('FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	// 129 virtual members: keep unified stream (FZHM per-member is wrong topology here).
	putenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST=0');
	putenv('FRACTAL_ZIP_LITERAL_PRINTABLE_MAX_LINE=8192');
}

function bench_world_record_apply_auto_tune(): void
{
	putenv('FRACTAL_ZIP_AUTO_TUNE=1');
}

/**
 * Wall-time parallelism for production encodes (bytes unchanged vs serial merge order).
 * Use on full world-record / textcodec / promotion runs — not on wire byte probes (those force serial).
 *
 * Does not require CUDA/Numba: Rust/Rayon substring helper is used when FRACTAL_ZIP_PEEL_GPU=1.
 */
function bench_world_record_apply_parallel_production_env(): void
{
	require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_runtime.php';
	fractal_zip_parallel_runtime_apply_cli_defaults();
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=1');
}

/**
 * Wire/lab probes: parallelize wall time without changing merge-order bytes.
 * Keeps {@code FRACTAL_ZIP_PIPELINE_PARALLEL=0}; enables zpaq threads, text-inner build forks, GPU/Rust substring.
 */
function bench_wire_probe_apply_parallel_speed_env(): void
{
	require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_runtime.php';
	fractal_zip_parallel_runtime_apply_cli_defaults();
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_ZPAQ_THREADS=' . fractal_zip_parallel_runtime_zpaq_thread_token());
	putenv('FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS=' . (string) fractal_zip_parallel_runtime_text_inner_build_jobs());
}

/** Clear stat sidecar / preprocess bleed between wire probe cases in one PHP process. */
function bench_wire_probe_reset_stat_preprocess_env(): void
{
	putenv('FRACTAL_ZIP_ENWIK_STAT_SIDECAR=0');
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
	putenv('FRACTAL_ZIP_ENWIK_STAT_ISP_MAX_WORDS=');
	putenv('FRACTAL_ZIP_STAT_PRED_MAX_WORDS=');
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_MAX_WORDS=');
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_MODEL_FILE=');
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_MINE_FULL=');
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_MINE_PATH=');
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_BIGRAM_MAX_PREV=');
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_PRUNE_ENCODE=');
	putenv('FRACTAL_ZIP_STAT_PRED_PAYLOAD_CODEC=');
	putenv('FRACTAL_ZIP_STAT_PRED_FZPM_SEALED_PICK=');
	putenv('FRACTAL_ZIP_STAT_PRED_INNER_MEMBER_FOLD=');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST=');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_DEEP=');
	putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_CODECS=');
	putenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_STACK_CODECS=');
	putenv('FRACTAL_ZIP_PHDA9_DICT_FOLD=0');
	putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=');
	putenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ_FORCE=');
	putenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS=');
}

/** pp96 pages/member topology (mono=0) for slice scale checks. */
function bench_wire_probe_apply_pp96_fztx_base_env(): void
{
	bench_world_record_apply_pp96_core_env();
	bench_wire_probe_reset_stat_preprocess_env();
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=fztx');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=0');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=mi_reorder');
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
}

/** Base mono_mi fztx stack env for stat_pred wire probe variations. */
function bench_wire_probe_apply_mono_mi_fztx_base_env(): void
{
	bench_world_record_apply_pp96_core_env();
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
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_inner_env.php';
