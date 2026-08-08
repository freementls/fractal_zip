<?php
declare(strict_types=1);

/**
 * Host-aware parallelism defaults for fractal_zip CLI encodes.
 *
 * Detects CPU count, pcntl, and optional helpers (Rust substring scorer), then applies
 * env only when unset. Explicit operator values always win. Non-CLI SAPI is untouched.
 *
 * Kill switches:
 * - {@code FRACTAL_ZIP_PARALLEL_OFF=1} — skip all auto defaults below (fork pools stay off unless set).
 * - {@code FRACTAL_ZIP_PARALLEL_PROBE=1} — byte-probe / stable-timing mode (bench scripts set parallel=0 explicitly).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';

if (!function_exists('fractal_zip_parallel_runtime_putenv_if_unset')) {
	function fractal_zip_parallel_runtime_putenv_if_unset(string $key, string $value): void {
		$cur = getenv($key);
		if ($cur === false || trim((string) $cur) === '') {
			putenv($key . '=' . $value);
		}
	}
}

if (!function_exists('fractal_zip_parallel_runtime_env_truthy')) {
	function fractal_zip_parallel_runtime_env_truthy(string $key): bool {
		$e = getenv($key);
		if ($e === false || trim((string) $e) === '') {
			return false;
		}
		$v = strtolower(trim((string) $e));
		return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
	}
}

if (!function_exists('fractal_zip_parallel_runtime_disabled')) {
	function fractal_zip_parallel_runtime_disabled(): bool {
		return fractal_zip_parallel_runtime_env_truthy('FRACTAL_ZIP_PARALLEL_OFF');
	}
}

if (!function_exists('fractal_zip_parallel_runtime_probe_mode')) {
	function fractal_zip_parallel_runtime_probe_mode(): bool {
		return fractal_zip_parallel_runtime_env_truthy('FRACTAL_ZIP_PARALLEL_PROBE');
	}
}

if (!function_exists('fractal_zip_parallel_runtime_pcntl_ok')) {
	function fractal_zip_parallel_runtime_pcntl_ok(): bool {
		return PHP_OS_FAMILY !== 'Windows'
			&& function_exists('pcntl_fork')
			&& function_exists('pcntl_wait')
			&& function_exists('pcntl_waitpid');
	}
}

if (!function_exists('fractal_zip_parallel_runtime_cpu_count')) {
	/** Logical CPUs (2–32). */
	function fractal_zip_parallel_runtime_cpu_count(): int {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$o = @shell_exec('nproc 2>/dev/null');
		if (is_string($o) && trim($o) !== '' && ctype_digit(trim($o))) {
			$n = (int) trim($o);
			if ($n > 0) {
				return $cached = max(2, min(32, $n));
			}
		}
		if (@is_readable('/proc/cpuinfo')) {
			$s = @file_get_contents('/proc/cpuinfo');
			if (is_string($s) && $s !== '' && preg_match_all('/^processor[\t ]*:/m', $s, $mm) > 0) {
				$n = count($mm[0]);
				if ($n > 0) {
					return $cached = max(2, min(32, $n));
				}
			}
		}
		if (PHP_OS_FAMILY === 'Darwin') {
			$o2 = @shell_exec('/usr/sbin/sysctl -n hw.ncpu 2>/dev/null');
			if (is_string($o2) && ctype_digit(trim($o2))) {
				return $cached = max(2, min(32, max(1, (int) trim($o2))));
			}
		}
		return $cached = 8;
	}
}

if (!function_exists('fractal_zip_parallel_runtime_fork_pool_workers')) {
	/** Concurrent fork-pool width (peel, FZBM, inner frontier); capped to reduce oversubscription. */
	function fractal_zip_parallel_runtime_fork_pool_workers(): int {
		return max(2, min(8, fractal_zip_parallel_runtime_cpu_count()));
	}
}

if (!function_exists('fractal_zip_parallel_runtime_inner_frontier_jobs')) {
	function fractal_zip_parallel_runtime_inner_frontier_jobs(): int {
		return max(1, min(4, fractal_zip_parallel_runtime_fork_pool_workers()));
	}
}

if (!function_exists('fractal_zip_parallel_runtime_text_inner_build_jobs')) {
	function fractal_zip_parallel_runtime_text_inner_build_jobs(): int {
		return max(1, min(8, fractal_zip_parallel_runtime_fork_pool_workers()));
	}
}

if (!function_exists('fractal_zip_parallel_runtime_nvidia_smi_available')) {
	/** True when an NVIDIA GPU is visible to the driver (nvidia-smi). */
	function fractal_zip_parallel_runtime_nvidia_smi_available(): bool {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$out = array();
		exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null', $out, $code);
		return $cached = ($code === 0 && $out !== array() && trim((string) ($out[0] ?? '')) !== '');
	}
}

if (!function_exists('fractal_zip_parallel_runtime_cuda_device_available')) {
	/** True when Numba CUDA can use an NVIDIA device (optional; falls back to CPU/Rust). */
	function fractal_zip_parallel_runtime_cuda_device_available(): bool {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		if (!fractal_zip_parallel_runtime_nvidia_smi_available()) {
			return $cached = false;
		}
		$out = array();
		exec('python3 -c "from numba import cuda; print(1 if cuda.is_available() else 0)" 2>/dev/null', $out, $ret);
		return $cached = ($ret === 0 && isset($out[0]) && trim((string) $out[0]) === '1');
	}
}

if (!function_exists('fractal_zip_parallel_runtime_gpu_substring_mode_default')) {
	/** CUDA when NVIDIA+Numba work; else Rust/CPU parallel (AMD and CPU-only hosts). */
	function fractal_zip_parallel_runtime_gpu_substring_mode_default(): string {
		if (fractal_zip_parallel_runtime_cuda_device_available()) {
			return 'auto';
		}
		return 'cpu_parallel';
	}
}

if (!function_exists('fractal_zip_parallel_runtime_rust_scorer_available')) {
	function fractal_zip_parallel_runtime_rust_scorer_available(): bool {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$root = dirname(__FILE__);
		$bin = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'gpu_substring'
			. DIRECTORY_SEPARATOR . 'target' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'gpu_substring_score_rs';
		return $cached = is_executable($bin);
	}
}

if (!function_exists('fractal_zip_parallel_runtime_zpaq_thread_token')) {
	/** Threads token for zpaqfranz when env unset: 0 = all cores. */
	function fractal_zip_parallel_runtime_zpaq_thread_token(): string {
		$cpu = fractal_zip_parallel_runtime_cpu_count();
		return (string) min(8, max(2, $cpu));
	}
}

if (!function_exists('fractal_zip_parallel_runtime_apply_cli_defaults')) {
	/**
	 * Apply integrated parallelism defaults on CLI when pcntl is available.
	 * Idempotent; safe to call from {@see fractal_zip_encode_pipeline::bootstrap_cli_parallel_defaults_if_cli}.
	 */
	function fractal_zip_parallel_runtime_apply_cli_defaults(): void {
		static $done = false;
		if ($done) {
			return;
		}
		$done = true;
		if (PHP_SAPI !== 'cli') {
			return;
		}
		if (!fractal_zip_parallel_runtime_pcntl_ok()) {
			return;
		}
		if (fractal_zip_parallel_runtime_disabled() || fractal_zip_parallel_runtime_probe_mode()) {
			return;
		}
		$cpu = fractal_zip_parallel_runtime_cpu_count();
		$pool = fractal_zip_parallel_runtime_fork_pool_workers();
		$inner = fractal_zip_parallel_runtime_inner_frontier_jobs();

		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_PIPELINE_JOBS', (string) $cpu);
		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_PEEL_JOBS', (string) $pool);
		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_PEEL_PRIORITY', 'size');
		fractal_zip_parallel_runtime_putenv_if_unset('FZ_INNER_FRONTIER_JOBS', (string) $inner);
		fractal_zip_parallel_runtime_putenv_if_unset('FZ_INNER_PIECE_JOBS', (string) $inner);
		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_FZBM_PATH_ORDER_PARALLEL', '1');
		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER_FORK', '1');
		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_7Z_MMT', 'on');
		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_ZSTD_THREADS', (string) min(4, $cpu));

		$zpaqThreads = fractal_zip_parallel_runtime_zpaq_thread_token();
		fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_ZPAQ_THREADS', $zpaqThreads);

		if (fractal_zip_parallel_runtime_rust_scorer_available()) {
			fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_GPU_SUBSTRING', '1');
			fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY', '1');
			fractal_zip_parallel_runtime_putenv_if_unset('FRACTAL_ZIP_PEEL_GPU', '1');
			fractal_zip_parallel_runtime_putenv_if_unset(
				'FZ_GPU_SUBSTRING_MODE',
				fractal_zip_parallel_runtime_gpu_substring_mode_default()
			);
			if (fractal_zip_parallel_runtime_cuda_device_available()) {
				fractal_zip_parallel_runtime_putenv_if_unset('FZ_GPU_SUBSTRING_PREFER_CUDA', '1');
			}
		}
		fractal_zip_parallel_runtime_putenv_if_unset(
			'FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS',
			(string) fractal_zip_parallel_runtime_text_inner_build_jobs()
		);
	}
}

if (!function_exists('fractal_zip_parallel_runtime_report')) {
	/** @return array<string, mixed> */
	function fractal_zip_parallel_runtime_report(): array {
		return array(
			'sapi' => PHP_SAPI,
			'pcntl_ok' => fractal_zip_parallel_runtime_pcntl_ok(),
			'cpu_count' => fractal_zip_parallel_runtime_cpu_count(),
			'fork_pool_workers' => fractal_zip_parallel_runtime_fork_pool_workers(),
			'inner_frontier_jobs' => fractal_zip_parallel_runtime_inner_frontier_jobs(),
			'rust_scorer' => fractal_zip_parallel_runtime_rust_scorer_available(),
			'nvidia_smi' => fractal_zip_parallel_runtime_nvidia_smi_available(),
			'cuda_device' => fractal_zip_parallel_runtime_cuda_device_available(),
			'gpu_substring_mode_default' => fractal_zip_parallel_runtime_gpu_substring_mode_default(),
			'parallel_off' => fractal_zip_parallel_runtime_disabled(),
			'probe_mode' => fractal_zip_parallel_runtime_probe_mode(),
			'pipeline_parallel' => getenv('FRACTAL_ZIP_PIPELINE_PARALLEL') ?: null,
			'pipeline_jobs' => getenv('FRACTAL_ZIP_PIPELINE_JOBS') ?: null,
			'peel_jobs' => getenv('FRACTAL_ZIP_PEEL_JOBS') ?: null,
			'peel_gpu' => getenv('FRACTAL_ZIP_PEEL_GPU') ?: null,
			'fzbm_parallel' => getenv('FRACTAL_ZIP_FZBM_PATH_ORDER_PARALLEL') ?: null,
			'inner_frontier' => getenv('FZ_INNER_FRONTIER_JOBS') ?: null,
			'zpaq_threads' => getenv('FRACTAL_ZIP_ZPAQ_THREADS') ?: null,
		);
	}
}
