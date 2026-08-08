<?php
declare(strict_types=1);

/**
 * Fork-child tracking, signal/shutdown cleanup, and stray-process sweeps for CLI encodes/benchmarks.
 *
 * Kill switches:
 * - {@code FRACTAL_ZIP_PROCESS_GUARD=0} — skip registration and sweeps.
 * - {@code FRACTAL_ZIP_PROCESS_GUARD_SWEEP_AGE_SEC=N} — max worker age before stray sweep (default 1800).
 * - {@code FRACTAL_ZIP_PROCESS_GUARD_ORPHAN_GRACE_SEC=N} — orphaned compressor grace (default 120).
 *
 * Stray = orphaned (reparented to PID 1 after its parent crashed) and not a session
 * leader. Processes with a living parent or launched via setsid are never swept.
 */

if (!function_exists('fractal_zip_process_guard_disabled')) {
	function fractal_zip_process_guard_disabled(): bool {
		$e = getenv('FRACTAL_ZIP_PROCESS_GUARD');
		if ($e === false || trim((string) $e) === '') {
			return false;
		}
		$v = strtolower(trim((string) $e));
		return $v === '0' || $v === 'false' || $v === 'no' || $v === 'off';
	}
}

if (!function_exists('fractal_zip_process_guard_sweep_age_sec')) {
	function fractal_zip_process_guard_sweep_age_sec(): int {
		$e = getenv('FRACTAL_ZIP_PROCESS_GUARD_SWEEP_AGE_SEC');
		if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
			return max(60, (int) trim((string) $e));
		}
		return 1800;
	}
}

if (!function_exists('fractal_zip_process_guard_repo_root')) {
	function fractal_zip_process_guard_repo_root(): string {
		return dirname(__FILE__);
	}
}

if (!function_exists('fractal_zip_process_guard_register_cli')) {
	/**
	 * Idempotent: track fork children, reap on shutdown/signals, optional periodic stray sweep.
	 */
	function fractal_zip_process_guard_register_cli(): void {
		static $done = false;
		if ($done || PHP_SAPI !== 'cli' || fractal_zip_process_guard_disabled()) {
			return;
		}
		$done = true;
		if (!function_exists('pcntl_fork')) {
			return;
		}
		$GLOBALS['fz_process_guard_owner_pid'] = getmypid();
		register_shutdown_function('fractal_zip_process_guard_shutdown_reap');
		if (function_exists('pcntl_async_signals')) {
			pcntl_async_signals(true);
		}
		foreach (array(SIGTERM, SIGINT, SIGHUP) as $sig) {
			if (function_exists('pcntl_signal')) {
				pcntl_signal($sig, static function (int $signo): void {
					$owner = $GLOBALS['fz_process_guard_owner_pid'] ?? null;
					if ($owner !== null && getmypid() !== (int) $owner) {
						exit($signo === SIGINT ? 130 : ($signo === SIGTERM ? 143 : 1));
					}
					fractal_zip_process_guard_reap_all(true);
					fractal_zip_process_guard_reap_direct_children(true);
					exit($signo === SIGINT ? 130 : ($signo === SIGTERM ? 143 : 1));
				});
			}
		}
	}
}

if (!function_exists('fractal_zip_process_guard_fork_child_prepare')) {
	/** Clear inherited parent tracking in fork workers (avoid shutdown reap killing siblings). */
	function fractal_zip_process_guard_fork_child_prepare(): void {
		$GLOBALS['fz_process_guard_children'] = array();
		$GLOBALS['fz_process_guard_owner_pid'] = getmypid();
	}
}

if (!function_exists('fractal_zip_process_guard_track')) {
	/** @param list<string> $tmpDirs */
	function fractal_zip_process_guard_track(int $pid, array $tmpDirs = array()): void {
		if ($pid <= 0) {
			return;
		}
		if (!isset($GLOBALS['fz_process_guard_children'])) {
			$GLOBALS['fz_process_guard_children'] = array();
		}
		/** @var array<int, list<string>> $g */
		$g = &$GLOBALS['fz_process_guard_children'];
		$g[$pid] = $tmpDirs;
	}
}

if (!function_exists('fractal_zip_process_guard_untrack')) {
	function fractal_zip_process_guard_untrack(int $pid): void {
		if (!isset($GLOBALS['fz_process_guard_children'])) {
			return;
		}
		unset($GLOBALS['fz_process_guard_children'][$pid]);
	}
}

if (!function_exists('fractal_zip_process_guard_reap_all')) {
	function fractal_zip_process_guard_reap_all(bool $forceKill = false): void {
		if (!isset($GLOBALS['fz_process_guard_children']) || !is_array($GLOBALS['fz_process_guard_children'])) {
			return;
		}
		/** @var array<int, list<string>> $children */
		$children = $GLOBALS['fz_process_guard_children'];
		$sig = $forceKill ? 9 : 15;
		foreach (array_keys($children) as $pid) {
			$pid = (int) $pid;
			if ($pid <= 1) {
				continue;
			}
			if (function_exists('posix_kill')) {
				@posix_kill($pid, $sig);
			} else {
				@exec('kill -' . ($forceKill ? '9' : '15') . ' ' . $pid . ' >/dev/null 2>&1');
			}
		}
		if ($forceKill) {
			usleep(100000);
		}
		$st = 0;
		foreach (array_keys($children) as $pid) {
			if (function_exists('pcntl_waitpid')) {
				pcntl_waitpid((int) $pid, $st, WNOHANG);
				pcntl_waitpid((int) $pid, $st);
			}
		}
		foreach ($children as $pid => $dirs) {
			foreach ($dirs as $td) {
				if (is_string($td) && $td !== '' && is_dir($td)) {
					fractal_zip_process_guard_rm_tree($td);
				}
			}
		}
		$GLOBALS['fz_process_guard_children'] = array();
	}
}

if (!function_exists('fractal_zip_process_guard_direct_child_pids')) {
	/** @return list<int> */
	function fractal_zip_process_guard_direct_child_pids(?int $parentPid = null): array {
		$parentPid = $parentPid ?? getmypid();
		$out = array();
		if (!is_dir('/proc')) {
			return $out;
		}
		foreach (@scandir('/proc') ?: array() as $ent) {
			if ($ent === '.' || $ent === '..' || !ctype_digit($ent)) {
				continue;
			}
			$pid = (int) $ent;
			if ($pid <= 1) {
				continue;
			}
			$status = @file_get_contents('/proc/' . $pid . '/status');
			if (!is_string($status) || !preg_match('/^PPid:\s+(\d+)/m', $status, $m)) {
				continue;
			}
			if ((int) $m[1] === $parentPid) {
				$out[] = $pid;
			}
		}
		return $out;
	}
}

if (!function_exists('fractal_zip_process_guard_reap_direct_children')) {
	function fractal_zip_process_guard_reap_direct_children(bool $forceKill = true): void {
		$sig = $forceKill ? 9 : 15;
		foreach (fractal_zip_process_guard_direct_child_pids() as $pid) {
			if (function_exists('posix_kill')) {
				@posix_kill($pid, $sig);
			} else {
				@exec('kill -' . $sig . ' ' . $pid . ' >/dev/null 2>&1');
			}
		}
		if ($forceKill) {
			usleep(80000);
		}
		$st = 0;
		if (function_exists('pcntl_waitpid')) {
			while (($wp = pcntl_waitpid(-1, $st, WNOHANG)) > 0) {
				// drain exited children
			}
			foreach (fractal_zip_process_guard_direct_child_pids() as $pid) {
				pcntl_waitpid($pid, $st);
			}
		}
	}
}

if (!function_exists('fractal_zip_process_guard_shutdown_reap')) {
	function fractal_zip_process_guard_shutdown_reap(): void {
		$owner = $GLOBALS['fz_process_guard_owner_pid'] ?? null;
		if ($owner !== null && getmypid() !== (int) $owner) {
			return;
		}
		fractal_zip_process_guard_reap_all(true);
		fractal_zip_process_guard_reap_direct_children(true);
	}
}

if (!function_exists('fractal_zip_process_guard_rm_tree')) {
	function fractal_zip_process_guard_rm_tree(string $dir): void {
		if (!is_dir($dir)) {
			return;
		}
		if (function_exists('fractal_zip_enwik_recursive_remove')) {
			fractal_zip_enwik_recursive_remove($dir);
			return;
		}
		$g = glob($dir . DIRECTORY_SEPARATOR . '*');
		if (is_array($g)) {
			foreach ($g as $f) {
				if (is_string($f)) {
					if (is_dir($f)) {
						fractal_zip_process_guard_rm_tree($f);
					} else {
						@unlink($f);
					}
				}
			}
		}
		@rmdir($dir);
	}
}

if (!function_exists('fractal_zip_process_guard_proc_age_sec')) {
	function fractal_zip_process_guard_proc_age_sec(int $pid): ?int {
		if ($pid <= 1 || !is_dir('/proc/' . $pid)) {
			return null;
		}
		// /proc/<pid> mtime is refreshed by some kernels; use stat starttime (field 22).
		$raw = @file_get_contents('/proc/' . $pid . '/stat');
		$up = @file_get_contents('/proc/uptime');
		if (is_string($raw) && is_string($up) && ($close = strrpos($raw, ')')) !== false) {
			$rest = preg_split('/\s+/', trim(substr($raw, $close + 1)));
			if (is_array($rest) && count($rest) >= 20) {
				$hz = 100;
				$startTicks = (float) $rest[19];
				$uptimeSec = (float) explode(' ', trim($up))[0];
				return max(0, (int) round($uptimeSec - $startTicks / $hz));
			}
		}
		$stat = @stat('/proc/' . $pid);
		if (!is_array($stat) || !isset($stat['mtime'])) {
			return null;
		}
		return max(0, time() - (int) $stat['mtime']);
	}
}

if (!function_exists('fractal_zip_process_guard_proc_cmdline')) {
	function fractal_zip_process_guard_proc_cmdline(int $pid): string {
		$raw = @file_get_contents('/proc/' . $pid . '/cmdline');
		if (!is_string($raw) || $raw === '') {
			return '';
		}
		return str_replace("\0", ' ', trim($raw));
	}
}

if (!function_exists('fractal_zip_process_guard_proc_ppid')) {
	function fractal_zip_process_guard_proc_ppid(int $pid): ?int {
		$status = @file_get_contents('/proc/' . $pid . '/status');
		if (!is_string($status) || !preg_match('/^PPid:\s+(\d+)/m', $status, $m)) {
			return null;
		}
		return (int) $m[1];
	}
}

if (!function_exists('fractal_zip_process_guard_proc_is_orphan')) {
	/** True when the process was reparented after its parent died (PPid 1 / vanished parent). */
	function fractal_zip_process_guard_proc_is_orphan(int $pid): bool {
		$ppid = fractal_zip_process_guard_proc_ppid($pid);
		if ($ppid === null) {
			return false;
		}
		return $ppid <= 1 || !is_dir('/proc/' . $ppid);
	}
}

if (!function_exists('fractal_zip_process_guard_has_live_php_ancestor')) {
	/**
	 * True when a living ancestor is an orchestrating repo php/python3 (cwd inside the
	 * repo or repo-marked cmdline). Compressors/helpers are always spawned by such a
	 * process (possibly through an `sh -c` exec wrapper); when it crashes, the leftover
	 * chain has no live php ancestor — that is the stray signal. Subreaper-proof.
	 */
	function fractal_zip_process_guard_has_live_php_ancestor(int $pid, string $repoRoot): bool {
		$markers = array('fractal_zip', 'run_enwik8_', 'bench_fractal_', 'verify_enwik_slice');
		$cur = fractal_zip_process_guard_proc_ppid($pid);
		for ($hop = 0; $hop < 25 && $cur !== null && $cur > 1; $hop++) {
			$comm = @file_get_contents('/proc/' . $cur . '/comm');
			$comm = is_string($comm) ? trim(explode("\n", $comm)[0]) : '';
			if ($comm === 'php' || $comm === 'python3') {
				$cwd = @readlink('/proc/' . $cur . '/cwd');
				if (is_string($cwd) && str_starts_with($cwd, $repoRoot)) {
					return true;
				}
				$cmd = fractal_zip_process_guard_proc_cmdline($cur);
				foreach ($markers as $mk) {
					if ($cmd !== '' && str_contains($cmd, $mk)) {
						return true;
					}
				}
			}
			$cur = fractal_zip_process_guard_proc_ppid($cur);
		}
		return false;
	}
}

if (!function_exists('fractal_zip_process_guard_parent_is_live_shell')) {
	/** Job roots are owned by a living shell/interpreter; crash orphans are reparented to init or a desktop subreaper. */
	function fractal_zip_process_guard_parent_is_live_shell(int $pid): bool {
		$ppid = fractal_zip_process_guard_proc_ppid($pid);
		if ($ppid === null || $ppid <= 1 || !is_dir('/proc/' . $ppid)) {
			return false;
		}
		$comm = @file_get_contents('/proc/' . $ppid . '/comm');
		$comm = is_string($comm) ? trim(explode("\n", $comm)[0]) : '';
		return in_array($comm, array(
			'bash', 'sh', 'zsh', 'dash', 'fish', 'php', 'python3', 'perl', 'make',
			'env', 'nohup', 'setsid', 'timeout', 'script', 'xargs', 'parallel', 'tee',
		), true);
	}
}

if (!function_exists('fractal_zip_process_guard_proc_is_session_leader')) {
	/** Session leaders (setsid) are deliberate detached runs, never crash leftovers. */
	function fractal_zip_process_guard_proc_is_session_leader(int $pid): bool {
		$raw = @file_get_contents('/proc/' . $pid . '/stat');
		if (!is_string($raw) || $raw === '') {
			return false;
		}
		// Field 6 (1-based) is the session id; comm may contain spaces, parse after ')'.
		$close = strrpos($raw, ')');
		if ($close === false) {
			return false;
		}
		$rest = preg_split('/\s+/', trim(substr($raw, $close + 1)));
		if (!is_array($rest) || count($rest) < 4) {
			return false;
		}
		return (int) $rest[3] === $pid;
	}
}

if (!function_exists('fractal_zip_process_guard_orphan_grace_sec')) {
	/** Grace before an orphaned compressor counts as stuck (default 120s). */
	function fractal_zip_process_guard_orphan_grace_sec(): int {
		$e = getenv('FRACTAL_ZIP_PROCESS_GUARD_ORPHAN_GRACE_SEC');
		if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
			return max(10, (int) trim((string) $e));
		}
		return 120;
	}
}

if (!function_exists('fractal_zip_process_guard_sweep_strays')) {
	/**
	 * Kill processes that persisted after a crash: compressors/helpers whose spawning
	 * encode or bench died (no living orchestrating php left in the parent chain).
	 * Never touches session leaders (deliberate setsid runs, interactive shells) or
	 * anything still parented — however deep — to live repo php.
	 *
	 * Tiers:
	 * - compressor/helper binaries (zpaq/7z/paq8px/rg/timeout/...) on repo or fz temp
	 *   data: killed after {@see fractal_zip_process_guard_orphan_grace_sec} (120s);
	 * - orphaned sh/bash exec wrappers with repo-marked cmdlines: same grace;
	 * - repo php/python3 fork workers with no live shell parent: after $maxAgeSec (1800s).
	 *
	 * @return int number of processes signalled
	 */
	function fractal_zip_process_guard_sweep_strays(?string $repoRoot = null, ?int $maxAgeSec = null): int {
		if (fractal_zip_process_guard_disabled() || PHP_OS_FAMILY === 'Windows' || !is_dir('/proc')) {
			return 0;
		}
		$repoRoot = $repoRoot ?? fractal_zip_process_guard_repo_root();
		$repoRoot = realpath($repoRoot) ?: $repoRoot;
		$maxAgeSec = $maxAgeSec ?? fractal_zip_process_guard_sweep_age_sec();
		$graceSec = fractal_zip_process_guard_orphan_grace_sec();
		$self = getmypid();
		$sent = 0;

		$compressorComms = array(
			'zpaq', '7z', '7za', '7zz', 'arc', 'paq8px', 'paq8pxd', 'phda9', 'phda9dec',
			'phda9_no_LSTM', 'cmix', 'brotli', 'zstd', 'xz', 'gpu_substr', 'nncp',
			'rg', 'sleep', 'timeout', 'gzip', 'bzip2',
		);
		$tmpMarkers = array(
			'fz_', 'fzenwik', 'fzpaq', 'fzarcbench', 'fz7bench', '/tmp/enwik',
			'fractal_zip', 'test_files', 'bench78', 'silesia78',
		);
		$needleSubstrings = array(
			'fractal_zip',
			'bench_fractal_',
			'verify_enwik_slice',
			'run_enwik8_',
			'gpu_substring_score',
			'fz_parallel_phases_',
			'fz_gpu_asc_',
			'fz_fzbm_par_',
		);

		foreach (@scandir('/proc') ?: array() as $ent) {
			if ($ent === '.' || $ent === '..' || !ctype_digit($ent)) {
				continue;
			}
			$pid = (int) $ent;
			if ($pid <= 1 || $pid === $self) {
				continue;
			}
			$comm = @file_get_contents('/proc/' . $pid . '/comm');
			$comm = is_string($comm) ? trim(explode("\n", $comm)[0]) : '';
			if ($comm === '') {
				continue;
			}
			$isCompressor = in_array($comm, $compressorComms, true);
			$isWorker = $comm === 'php' || $comm === 'python3';
			$isShellWrap = $comm === 'sh' || $comm === 'bash';
			if (!$isCompressor && !$isWorker && !$isShellWrap) {
				continue;
			}
			if (fractal_zip_process_guard_proc_is_session_leader($pid)) {
				continue; // deliberate detached run (setsid) or interactive shell
			}
			$age = fractal_zip_process_guard_proc_age_sec($pid);
			if ($age === null) {
				continue;
			}
			$cmd = fractal_zip_process_guard_proc_cmdline($pid);
			$cwd = @readlink('/proc/' . $pid . '/cwd');
			$cwd = is_string($cwd) ? $cwd : '';
			$touchesRepo = false;
			if ($cwd !== '' && str_starts_with($cwd, $repoRoot)) {
				$touchesRepo = true;
			} else {
				foreach ($tmpMarkers as $mk) {
					if ($cmd !== '' && str_contains($cmd, $mk)) {
						$touchesRepo = true;
						break;
					}
				}
			}
			$cmdMarkerMatch = false;
			foreach (array_merge($needleSubstrings, $tmpMarkers) as $mk) {
				if ($cmd !== '' && str_contains($cmd, $mk)) {
					$cmdMarkerMatch = true;
					break;
				}
			}

			$kill = false;
			if ($isCompressor) {
				// Stuck outer/compressor/helper whose spawning encode or bench died:
				// no living orchestrating php remains in its parent chain.
				$kill = $age >= $graceSec && $touchesRepo
					&& !fractal_zip_process_guard_has_live_php_ancestor($pid, $repoRoot);
			} elseif ($isShellWrap) {
				// Orphaned exec()/pipeline shell wrapper left behind by a crashed php.
				$kill = $age >= $graceSec && $cmdMarkerMatch
					&& !fractal_zip_process_guard_has_live_php_ancestor($pid, $repoRoot)
					&& !fractal_zip_process_guard_parent_is_live_shell($pid);
			} elseif ($isWorker) {
				// Orphaned fork worker (parent php/bench died). Job roots keep a living
				// shell/harness parent or run as setsid session leaders.
				$nameMatch = false;
				foreach ($needleSubstrings as $needle) {
					if ($cmd !== '' && str_contains($cmd, $needle)) {
						$nameMatch = true;
						break;
					}
				}
				$kill = $nameMatch && $age >= $maxAgeSec
					&& !fractal_zip_process_guard_parent_is_live_shell($pid)
					&& !fractal_zip_process_guard_has_live_php_ancestor($pid, $repoRoot);
			}
			if (!$kill) {
				continue;
			}
			if (function_exists('posix_kill')) {
				@posix_kill($pid, 15);
				usleep(50000);
				@posix_kill($pid, 9);
			} else {
				@exec('kill -9 ' . $pid . ' >/dev/null 2>&1');
			}
			$sent++;
		}
		return $sent;
	}
}
