<?php
declare(strict_types=1);

/**
 * Inner-first enwik8 presets: relax fractal / multidiff / substring caps without expanding
 * outer-predict sweep (see benchmarks/ENWIK8_INNER_CAPS.md).
 *
 * Use via benchmarks/run_enwik8_inner_experiment.php or run_enwik8_inner_experiment_grid.sh.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

/** pp96 topology + enwik tuned; no high-tier outer predict expansion; no PAQ at encode end. */
function bench_world_record_apply_inner_baseline_env(): void
{
	bench_ultra_apply_env_defaults();
	bench_world_record_apply_enwik_tuned();
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES', '134217728');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_BENCH_ZPAQ_THREADS', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_WEB_REF', '0');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_ENTRY_SORT', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER', '96');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PIPELINE_REORDER', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_PIPELINE_REORDER_EXT', '1');
	putenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0');
	putenv('FRACTAL_ZIP_PAQ_SWEEP=0');
	// Outer: zpaq sweep on unified inner, but not high-tier 128 MiB outer-predict probe ladder.
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ZPAQ_OUTER_SWEEP', '1');
	bench_world_record_putenv_if_unset('FRACTAL_ZIP_ZPAQ_OUTER_AUTO_SWEEP_MAX_INNER_BYTES', '134217728');
}

/** Raised multidiff / substring caps only (still inner_baseline outer). */
function bench_world_record_apply_inner_multidiff_caps_env(): void
{
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_CANDIDATES=2000');
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_TOKENS=500000');
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_PAIR_PROBES=10000');
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_ANCHOR_OCCURS=32');
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_LITERAL_JOBS=0');
	putenv('FRACTAL_ZIP_SUBSTRING_TOP_K=48');
}

/** All inner relaxations on top of inner_baseline (no bench_world_record_apply_high_env). */
function bench_world_record_apply_inner_focus_env(): void
{
	bench_world_record_apply_inner_baseline_env();
	bench_world_record_apply_inner_combo_caps_env();
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=1');
}

/** inner_combo multidiff / allsub / deep caps without baseline (stack on pp96 wire). */
function bench_world_record_apply_inner_combo_caps_env(): void
{
	bench_world_record_apply_inner_multidiff_caps_env();
	putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
	putenv('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD=0.005');
	putenv('FRACTAL_ZIP_MULTIPASS_GATE_MULT=1');
	putenv('FRACTAL_ZIP_DEEP=1');
	putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=1');
	putenv('FRACTAL_ZIP_MEMBER_DEEP_UNWRAP=1');
}

/** inner_combo caps + run-grammar / peeler (inner_recursive0 on full encode). */
function bench_world_record_apply_inner_combo_recursive0_caps_env(): void
{
	bench_world_record_apply_inner_combo_caps_env();
	putenv('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=0');
}

/** Env keys recorded in inner experiment JSON for A/B logs. */
function bench_world_record_inner_env_snapshot(): array
{
	$keys = array(
		'FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES',
		'FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_CANDIDATES',
		'FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_TOKENS',
		'FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_PAIR_PROBES',
		'FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_ANCHOR_OCCURS',
		'FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_LITERAL_JOBS',
		'FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY',
		'FRACTAL_ZIP_SUBSTRING_TOP_K',
		'FRACTAL_ZIP_IMPROVEMENT_THRESHOLD',
		'FRACTAL_ZIP_DEEP',
		'FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP',
		'FRACTAL_ZIP_MEMBER_DEEP_UNWRAP',
		'FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES',
		'FRACTAL_ZIP_FOLDER_UNIFIED_STREAM',
		'FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER',
		'FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES',
		'FRACTAL_ZIP_ZPAQ_METHOD',
		'FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES',
		'FRACTAL_ZIP_FOLDER_LOW_TEXTISH_SKIP_RUN_GRAMMAR',
		'FRACTAL_ZIP_PAQ_NATIVE_COMPARE',
	);
	$out = array();
	foreach ($keys as $k) {
		$v = getenv($k);
		if ($v !== false) {
			$out[$k] = $v;
		}
	}
	return $out;
}
