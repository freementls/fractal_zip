<?php
declare(strict_types=1);

/**
 * Shared bytes-first “try hardest” preset for benchmarks (aligned with {@see fractal_zip_cli_apply_ultra_env_defaults}).
 */

function bench_ultra_putenv_if_unset(string $k, string $v): void
{
	$e = getenv($k);
	if ($e === false || trim((string) $e) === '') {
		putenv($k . '=' . $v);
	}
}

function bench_ultra_apply_env_defaults(): void
{
	putenv('FRACTAL_ZIP_SPEED=0');
	putenv('FRACTAL_ZIP_ULTRA=1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '0');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER', '0');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES', '0');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL', '9');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL', '9');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES', '16777216');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES', '33554432');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO', '0');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_LITERAL_LARGE_TEXT_SKIP_PROBE_GZIP1_MIN_RATIO', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC', '0');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_MULTIPASS', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_MULTIPASS_MAX_ADDITIONAL_PASSES', 'unlimited');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD', '0.01');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_MULTIPASS_GATE_MULT', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_DEEP', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_ZSTD_LEVEL', '22');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_BROTLI_QUALITY', '11');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'full');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_ALWAYS_TRY_BROTLI', '1');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND', '32');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES', '16777216');
	bench_ultra_putenv_if_unset('FRACTAL_ZIP_WHOLE_STREAM_FZWS', '1');
}
