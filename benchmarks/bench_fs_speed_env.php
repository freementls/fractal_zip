<?php
declare(strict_types=1);

/**
 * FS manual-compress speed preset for run_benchmarks.php (--bench-profile=fs).
 *
 * Targets ~10× wall time vs bytes-first default on the 59-case small-bytes corpus
 * (≤2 MiB raw per folder) while staying within ~1.5× baseline compressed bytes.
 *
 * Applied when passing {@code --bench-profile=fs} or setting {@code FRACTAL_ZIP_BENCH_FS_SPEED=1}.
 * Sets {@code FRACTAL_ZIP_PRESET=fs} and throughput env defaults; explicit CLI/env overrides still win.
 */

function bench_fs_speed_putenv_if_unset(string $k, string $v): void
{
	$e = getenv($k);
	if ($e === false || trim((string) $e) === '') {
		putenv($k . '=' . $v);
	}
}

function bench_fs_speed_apply_env_defaults(): void
{
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_PRESET', 'fs');
	// Manual fs uses lifestyle segment 1000; benches default LIFESTYLE=0 unless set.
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '1');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_DISABLE_LITERAL_MERGED_FZBM', '1');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_DISABLE_LIFESTYLE_ABOVE_RAW_BYTES', '524288');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_LIFESTYLE_LITERAL_OUTER_JOB_CAP', '1');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_LIFESTYLE_SINGLE_MEMBER_LITERAL_OUTER_JOB_CAP', '1');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_SPEED_TRY_BROTLI', '0');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_FULL_FRACTAL_MAX_RAW_BYTES', '65536');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_SPEED_FIRST_MIN_RAW_BYTES', '262144');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_TINY_ALWAYS_FRACTAL_MAX_RAW_BYTES', '8192');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_DENSE_RECIPROCAL_MIN_MEMBERS', '64');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_SMALL_MULTIFILE_RECIPROCAL_MIN_MEMBERS', '8');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_SMALL_MULTIFILE_RECIPROCAL_MAX_RAW_BYTES', '262144');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_INNER_PEELER_MAX_TOTAL_RAW_BYTES', '65536');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE', '0');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES', '0');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES', '134217728');
	bench_fs_speed_putenv_if_unset('FRACTAL_ZIP_FS_LARGE_7Z_PASSTHROUGH_MIN_RAW_BYTES', '2097152');
}
