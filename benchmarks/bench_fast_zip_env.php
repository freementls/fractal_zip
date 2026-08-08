<?php
declare(strict_types=1);

/**
 * Optional wall-time preset for folder zip_folder benchmarks (timing-only tuning).
 *
 * Applied when passing {@code --bench-fast-zip} to {@code benchmarks/run_benchmarks.php} or setting {@code FRACTAL_ZIP_BENCH_FAST_ZIP=1}
 * before invoking the driver (same defaults as the flag).
 *
 * Aligns layered outer prediction probe/timeout with {@see fractal_zip::speed_mode_enabled()} defaults (2 MiB probe / 12 s timeout)
 * without setting {@code FRACTAL_ZIP_SPEED=1}, and caps staged literal fast-tier brotli at Q3 when unset (see {@see fractal_zip::adaptive_compress_outer_fast_codec_tier}).
 * May change prediction mapping vs bytes-first 8 MiB / 30 s on unusual inners — compare `.fz` bytes when tuning.
 */

function bench_fast_zip_putenv_if_unset(string $k, string $v): void
{
	$e = getenv($k);
	if ($e === false || trim((string) $e) === '') {
		putenv($k . '=' . $v);
	}
}

function bench_fast_zip_outer_apply_env_defaults(): void
{
	bench_fast_zip_putenv_if_unset('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', '3');
	bench_fast_zip_putenv_if_unset('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES', (string) (2 * 1024 * 1024));
	bench_fast_zip_putenv_if_unset('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC', '12');
}
