<?php
declare(strict_types=1);

/**
 * Background / desktop-friendly preset: serial workers, lower PHP cap, less parallel RAM.
 *
 * Enable with FRACTAL_ZIP_LOW_MEMORY=1 or FRACTAL_ZIP_BACKGROUND=1 (set automatically by probe scripts).
 *
 * Full-speed production encode: unset or FRACTAL_ZIP_LOW_MEMORY=0 before bench_world_record_apply_parallel_production_env().
 */

function bench_low_memory_enabled(): bool
{
	if (getenv('FRACTAL_ZIP_LOW_MEMORY') === '1' || getenv('FRACTAL_ZIP_BACKGROUND') === '1') {
		return true;
	}
	if (getenv('FRACTAL_ZIP_LOW_MEMORY') === '0') {
		return false;
	}
	// Default on when MemAvailable is tight (< 2.5 GiB).
	if (is_readable('/proc/meminfo')) {
		$raw = @file_get_contents('/proc/meminfo');
		if (is_string($raw) && preg_match('/^MemAvailable:\s+(\d+)/m', $raw, $m)) {
			$availMb = (int) floor((int) $m[1] / 1024);
			return $availMb > 0 && $availMb < 2560;
		}
	}
	return false;
}

function bench_low_memory_php_memory_limit(): string
{
	$e = getenv('FRACTAL_ZIP_PHP_MEMORY_LIMIT');
	if (is_string($e) && $e !== '') {
		return $e;
	}
	return bench_low_memory_enabled() ? '768M' : '2048M';
}

/** @return list<string> php -d args for subprocesses */
function bench_low_memory_php_ini_args(): array
{
	return array('-d', 'memory_limit=' . bench_low_memory_php_memory_limit());
}

function bench_low_memory_shell_php_prefix(): string
{
	return 'nice -n 19 php ' . implode(' ', array_map('escapeshellarg', bench_low_memory_php_ini_args()));
}

function bench_low_memory_apply_env(): void
{
	putenv('FRACTAL_ZIP_LOW_MEMORY=1');
	putenv('FRACTAL_ZIP_BACKGROUND=1');
	putenv('FRACTAL_ZIP_PARALLEL_OFF=1');
	putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
	putenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS=1');
	putenv('FRACTAL_ZIP_ZPAQ_THREADS=1');
	putenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS=1');
	// Keep ~1.5 GiB for desktop when auto-sizing phda9 workers.
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_RESERVE_MB=1536');
	putenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=64');
	putenv('FRACTAL_ZIP_ENWIK_FZBM_PATH_ORDER_RANDOM_MAX_MEMBERS=128');
	putenv('FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES=4194304');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_LOW_TEXTISH_SKIP_RUN_GRAMMAR=1');
}

/**
 * Wire probes: serial merge order, no fork pools (bytes unchanged vs parallel probe env).
 */
function bench_low_memory_apply_wire_probe_env(): void
{
	bench_low_memory_apply_env();
	putenv('FRACTAL_ZIP_ZPAQ_THREADS=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS=1');
}

/**
 * Single-instance lock for hours-scale diagnostics (phda9 decompress ~1.5 GiB RSS).
 *
 * @return resource|null flock handle; null if another run holds the lock
 */
function bench_low_memory_try_diagnostic_lock(string $repoRoot, string $name = 'diagnostic')
{
	$dir = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'logs';
	if (!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}
	$path = $dir . DIRECTORY_SEPARATOR . '.lock_' . preg_replace('/[^a-z0-9_]+/', '_', strtolower($name)) . '.pid';
	$fh = @fopen($path, 'c+');
	if ($fh === false) {
		return null;
	}
	if (!flock($fh, LOCK_EX | LOCK_NB)) {
		fclose($fh);
		return null;
	}
	ftruncate($fh, 0);
	fwrite($fh, (string) getmypid());
	fflush($fh);
	return $fh;
}
