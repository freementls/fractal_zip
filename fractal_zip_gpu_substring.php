<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_runtime.php';

/**
 * Optional GPU/CPU substring scoring helpers for peel and benchmark paths.
 *
 * Env:
 * - {@code FRACTAL_ZIP_PEEL_GPU=1} — prefer helper when {@see fractal_zip_gpu_substring_peel_enabled()}.
 * - {@code FRACTAL_ZIP_GPU_SUBSTRING=1} — alias for peel + future {@code all_substrings_count} delegate paths.
 * - {@code FRACTAL_ZIP_PEEL_GPU_MIN_BYTES=N} — minimum haystack length (default 65536).
 * - {@code FZ_GPU_SUBSTRING_MODE} — passed to Python helper (cpu_parallel, cuda, auto).
 */

if (!function_exists('fractal_zip_gpu_substring_repo_root')) {
	function fractal_zip_gpu_substring_repo_root(): string {
		return dirname(__FILE__);
	}
}

if (!function_exists('fractal_zip_gpu_substring_peel_enabled')) {
	function fractal_zip_gpu_substring_env_truthy(string $key): bool {
		$e = getenv($key);
		if ($e === false || trim((string) $e) === '') {
			return false;
		}
		$v = strtolower(trim((string) $e));
		return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
	}

	function fractal_zip_gpu_substring_rust_available(): bool {
		return fractal_zip_parallel_runtime_rust_scorer_available();
	}

	function fractal_zip_gpu_substring_peel_enabled(): bool {
		if (fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_PEEL_GPU')
			|| fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_GPU_SUBSTRING')) {
			return true;
		}
		$off = getenv('FRACTAL_ZIP_PEEL_GPU');
		if ($off !== false && trim((string) $off) !== '') {
			$v = strtolower(trim((string) $off));
			if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
				return false;
			}
		}
		if (fractal_zip_parallel_runtime_disabled() || fractal_zip_parallel_runtime_probe_mode()) {
			return false;
		}
		return PHP_SAPI === 'cli' && fractal_zip_gpu_substring_rust_available();
	}
}

if (!function_exists('fractal_zip_gpu_substring_peel_min_bytes')) {
	function fractal_zip_gpu_substring_peel_min_bytes(): int {
		$e = getenv('FRACTAL_ZIP_PEEL_GPU_MIN_BYTES');
		if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
			return 65536;
		}
		return max(4096, (int) trim((string) $e));
	}
}

if (!function_exists('fractal_zip_gpu_substring_helper_paths')) {
	/** @return array{rust: string, python: string, cuda_py: string} */
	function fractal_zip_gpu_substring_helper_paths(): array {
		$root = fractal_zip_gpu_substring_repo_root();
		$tool = $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'gpu_substring';
		return array(
			'rust' => $tool . DIRECTORY_SEPARATOR . 'target' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'gpu_substring_score_rs',
			'python' => $tool . DIRECTORY_SEPARATOR . 'gpu_substring_score',
			'cuda_py' => $tool . DIRECTORY_SEPARATOR . 'gpu_substring_score_cuda.py',
		);
	}
}

if (!function_exists('fractal_zip_gpu_substring_cuda_available')) {
	function fractal_zip_gpu_substring_cuda_available(): bool {
		$out = array();
		exec('python3 -c "from numba import cuda; print(1 if cuda.is_available() else 0)" 2>/dev/null', $out, $ret);
		return $ret === 0 && isset($out[0]) && trim((string) $out[0]) === '1';
	}
}

if (!function_exists('fractal_zip_gpu_substring_pick_helper')) {
	/**
	 * Prefer Rust release binary, then Python wrapper. CUDA mode only when requested and available.
	 *
	 * @return array{path: string, kind: string, mode: string}|null
	 */
	function fractal_zip_gpu_substring_prefer_cuda(): bool {
		if (fractal_zip_gpu_substring_env_truthy('FZ_GPU_SUBSTRING_PREFER_CUDA')) {
			return true;
		}
		$mode = getenv('FZ_GPU_SUBSTRING_MODE');
		return $mode !== false && strtolower(trim((string) $mode)) === 'cuda';
	}

	function fractal_zip_gpu_substring_pick_helper(bool $preferCuda = false): ?array {
		$paths = fractal_zip_gpu_substring_helper_paths();
		$wantCuda = $preferCuda || fractal_zip_gpu_substring_prefer_cuda();
		if ($wantCuda && fractal_zip_gpu_substring_cuda_available() && is_executable($paths['python'])) {
			return array('path' => $paths['python'], 'kind' => 'python', 'mode' => 'cuda_device');
		}
		if (is_executable($paths['rust'])) {
			return array('path' => $paths['rust'], 'kind' => 'rust', 'mode' => 'cpu_parallel_rust');
		}
		$mode = getenv('FZ_GPU_SUBSTRING_MODE');
		$modeStr = ($mode === false || trim((string) $mode) === '') ? 'cpu_parallel' : trim((string) $mode);
		if (is_executable($paths['python'])) {
			return array('path' => $paths['python'], 'kind' => 'python', 'mode' => $modeStr);
		}
		return null;
	}
}

if (!function_exists('fractal_zip_gpu_substring_score_top_k')) {
	/**
	 * Rolling fixed-window substring top-K (count desc, len asc, first index asc).
	 *
	 * @return array{status: string, mode: string, candidates_scored: int, top_k: list<string>, wall_seconds: float, helper: string}|null
	 */
	function fractal_zip_gpu_substring_score_top_k(string $data, int $minLen, int $maxLen, int $topK, bool $preferCuda = false): ?array {
		$helper = fractal_zip_gpu_substring_pick_helper($preferCuda);
		if ($helper === null) {
			return null;
		}
		try {
			$rnd = bin2hex(random_bytes(8));
		} catch (\Throwable $e) {
			return null;
		}
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_gpu_sub_' . $rnd;
		if (!@mkdir($tmp, 0700, true)) {
			return null;
		}
		$in = $tmp . DIRECTORY_SEPARATOR . 'in.bin';
		$outJson = $tmp . DIRECTORY_SEPARATOR . 'out.json';
		if (@file_put_contents($in, $data) === false) {
			@rmdir($tmp);
			return null;
		}
		$t0 = microtime(true);
		if ($helper['kind'] === 'rust') {
			$cmd = escapeshellarg($helper['path']) . ' '
				. escapeshellarg($in) . ' ' . (int) $minLen . ' ' . (int) $maxLen . ' ' . (int) $topK . ' '
				. escapeshellarg($outJson);
		} elseif ($helper['kind'] === 'cuda_py') {
			$cmd = 'python3 ' . escapeshellarg($helper['path']) . ' '
				. escapeshellarg($in) . ' ' . (int) $minLen . ' ' . (int) $maxLen . ' ' . (int) $topK . ' '
				. escapeshellarg($outJson);
		} else {
			$envPrefix = 'FZ_GPU_SUBSTRING_MODE=' . escapeshellarg((string) $helper['mode']) . ' ';
			$cmd = $envPrefix . escapeshellarg($helper['path']) . ' '
				. escapeshellarg($in) . ' ' . (int) $minLen . ' ' . (int) $maxLen . ' ' . (int) $topK . ' '
				. escapeshellarg($outJson);
		}
		exec($cmd, $xo, $ret);
		$wall = microtime(true) - $t0;
		$result = null;
		if ($ret === 0 && is_file($outJson)) {
			$decoded = json_decode((string) file_get_contents($outJson), true);
			if (is_array($decoded)) {
				$top = $decoded['top_k'] ?? array();
				if (!is_array($top)) {
					$top = array();
				}
				$result = array(
					'status' => 'ok',
					'mode' => (string) ($decoded['mode'] ?? $helper['mode']),
					'candidates_scored' => (int) ($decoded['candidates_scored'] ?? 0),
					'top_k' => array_values(array_map('strval', $top)),
					'wall_seconds' => isset($decoded['wall_seconds']) ? (float) $decoded['wall_seconds'] : $wall,
					'helper' => basename((string) $helper['path']),
				);
			}
		}
		foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
			if (is_string($f) && is_file($f)) {
				@unlink($f);
			}
		}
		@rmdir($tmp);
		return $result;
	}
}

if (!function_exists('fractal_zip_gpu_substring_peel_delegate_top_k')) {
	/**
	 * Peel-phase delegate: returns helper top-K when enabled and haystack is large enough; otherwise null (caller uses CPU).
	 *
	 * @return list<string>|null
	 */
	function fractal_zip_gpu_substring_peel_delegate_top_k(string $data, int $minLen, int $maxLen, int $topK): ?array {
		if (!fractal_zip_gpu_substring_peel_enabled()) {
			return null;
		}
		if (strlen($data) < fractal_zip_gpu_substring_peel_min_bytes()) {
			return null;
		}
		$preferCuda = fractal_zip_gpu_substring_cuda_available();
		$scored = fractal_zip_gpu_substring_score_top_k($data, $minLen, $maxLen, $topK, $preferCuda);
		if ($scored === null || ($scored['status'] ?? '') !== 'ok') {
			return null;
		}
		return $scored['top_k'];
	}
}

if (!function_exists('fractal_zip_gpu_substring_availability_report')) {
	/** @return array<string, mixed> */
	function fractal_zip_gpu_substring_availability_report(): array {
		$paths = fractal_zip_gpu_substring_helper_paths();
		$pick = fractal_zip_gpu_substring_pick_helper(fractal_zip_gpu_substring_cuda_available());
		return array(
			'rust_executable' => is_executable($paths['rust']),
			'python_executable' => is_executable($paths['python']),
			'cuda_py_present' => is_file($paths['cuda_py']),
			'cuda_available' => fractal_zip_gpu_substring_cuda_available(),
			'picked_helper' => $pick === null ? null : $pick['path'],
			'picked_mode' => $pick === null ? null : $pick['mode'],
		);
	}
}

if (!function_exists('fractal_zip_gpu_substring_verify_enabled')) {
	/** Parallel duplicate verify in {@see fractal_zip::all_substrings_count()}. */
	function fractal_zip_gpu_substring_verify_enabled(): bool {
		if (fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY')
			|| fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_GPU_SUBSTRING')
			|| fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_GPU_SUBSTRING_ALL')
			|| fractal_zip_gpu_substring_peel_enabled()) {
			return true;
		}
		$e = getenv('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY');
		if ($e !== false && trim((string) $e) !== '') {
			$v = strtolower(trim((string) $e));
			return !($v === '0' || $v === 'false' || $v === 'no' || $v === 'off');
		}
		return false;
	}
}

if (!function_exists('fractal_zip_gpu_substring_slide_seeds_enabled')) {
	/** Rust fixed-window seed merge before verify (opt-in — may change bytes). */
	function fractal_zip_gpu_substring_slide_seeds_enabled(): bool {
		return fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_GPU_SUBSTRING_SLIDE')
			|| fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_GPU_SUBSTRING_ALL');
	}
}

if (!function_exists('fractal_zip_gpu_substring_all_substrings_enabled')) {
	function fractal_zip_gpu_substring_all_substrings_enabled(): bool {
		return fractal_zip_gpu_substring_verify_enabled()
			|| fractal_zip_gpu_substring_slide_seeds_enabled();
	}
}

if (!function_exists('fractal_zip_gpu_substring_verify_min_candidates')) {
	function fractal_zip_gpu_substring_verify_min_candidates(): int {
		$e = getenv('FRACTAL_ZIP_GPU_SUBSTRING_VERIFY_MIN');
		if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
			return 16;
		}
		return max(4, (int) trim((string) $e));
	}
}

if (!function_exists('fractal_zip_gpu_substring_verify_duplicate_records')) {
	/**
	 * Byte-identical duplicate verification for all_substrings_count probe map.
	 *
	 * @param array<string, int> $substrRecords
	 * @return array<string, int>
	 */
	function fractal_zip_gpu_substring_verify_duplicate_records(
		fractal_zip $fz,
		string $string,
		array $substrRecords,
		array $haystackByte,
		string $haystackRejectMask,
		int $minimumSubstrLength,
		int $slen
	): array {
		$keys = array_keys($substrRecords);
		$nKeys = count($keys);
		if ($nKeys === 0) {
			return array();
		}
		$useParallel = $nKeys >= fractal_zip_gpu_substring_verify_min_candidates()
			&& PHP_SAPI === 'cli';
		if ($useParallel && !function_exists('fractal_zip_parallel_runtime_pcntl_ok')) {
			require_once fractal_zip_gpu_substring_repo_root() . DIRECTORY_SEPARATOR . 'fractal_zip_parallel_runtime.php';
		}
		$useParallel = $useParallel
			&& function_exists('fractal_zip_parallel_runtime_pcntl_ok')
			&& fractal_zip_parallel_runtime_pcntl_ok()
			&& !(function_exists('fractal_zip_parallel_runtime_probe_mode') && fractal_zip_parallel_runtime_probe_mode());
		if (!$useParallel) {
			$substrKept = array();
			foreach ($substrRecords as $substr => $_probeCount) {
				$needleLen = strlen($substr);
				if ($needleLen === 0 || $needleLen * 2 > $slen) {
					continue;
				}
				if (strcspn($substr, $haystackRejectMask) !== $needleLen) {
					continue;
				}
				if (!$fz->is_fractally_clean($substr)) {
					continue;
				}
				$realCount = substr_count($string, $substr);
				if ($realCount < 2) {
					continue;
				}
				$substrKept[$substr] = $realCount;
			}
			return $substrKept;
		}
		$jobs = min(8, max(2, fractal_zip_parallel_runtime_fork_pool_workers()));
		$chunkSize = (int) ceil($nKeys / $jobs);
		$children = array();
		$tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_gpu_asc_' . bin2hex(random_bytes(6));
		@mkdir($tmpBase, 0700, true);
		$active = array();
		for ($w = 0; $w < $jobs; $w++) {
			$slice = array_slice($keys, $w * $chunkSize, $chunkSize, true);
			if ($slice === array()) {
				continue;
			}
			$outPath = $tmpBase . DIRECTORY_SEPARATOR . 'part_' . $w . '.json';
			$pid = pcntl_fork();
			if ($pid === -1) {
				if (function_exists('fractal_zip_encode_pipeline')) {
					fractal_zip_encode_pipeline::terminate_fork_children_and_cleanup($active, $tmpBase);
				} elseif (function_exists('fractal_zip_process_guard_reap_direct_children')) {
					fractal_zip_process_guard_reap_direct_children(true);
					fractal_zip_process_guard_rm_tree($tmpBase);
				}
				$useParallel = false;
				break;
			}
			if ($pid === 0) {
				if (function_exists('fractal_zip_process_guard_fork_child_prepare')) {
					fractal_zip_process_guard_fork_child_prepare();
				}
				$childFz = new fractal_zip($fz->segment_length, false, false, null, false);
				$part = array();
				foreach ($slice as $substr) {
					if (!is_string($substr)) {
						continue;
					}
					$needleLen = strlen($substr);
					if ($needleLen === 0 || $needleLen * 2 > $slen) {
						continue;
					}
					if (strcspn($substr, $haystackRejectMask) !== $needleLen) {
						continue;
					}
					if (!$childFz->is_fractally_clean($substr)) {
						continue;
					}
					$realCount = substr_count($string, $substr);
					if ($realCount < 2) {
						continue;
					}
					$part[$substr] = $realCount;
				}
				file_put_contents($outPath, serialize($part));
				exit(0);
			}
			$active[$pid] = $w;
			if (function_exists('fractal_zip_process_guard_track')) {
				fractal_zip_process_guard_track($pid, array($tmpBase));
			}
			$children[] = array('pid' => $pid, 'out' => $outPath);
		}
		if (!$useParallel) {
			if ($active !== array() && function_exists('fractal_zip_encode_pipeline')) {
				fractal_zip_encode_pipeline::terminate_fork_children_and_cleanup($active, $tmpBase);
			} else {
				foreach ($children as $ch) {
					if (isset($ch['pid']) && $ch['pid'] > 0) {
						pcntl_waitpid((int) $ch['pid'], $status);
					}
				}
				fractal_zip_enwik_recursive_remove($tmpBase);
			}
			return fractal_zip_gpu_substring_verify_duplicate_records(
				$fz,
				$string,
				$substrRecords,
				$haystackByte,
				$haystackRejectMask,
				$minimumSubstrLength,
				$slen
			);
		}
		$substrKept = array();
		$status = 0;
		foreach ($children as $ch) {
			$wpid = (int) $ch['pid'];
			pcntl_waitpid($wpid, $status);
			if (function_exists('fractal_zip_process_guard_untrack')) {
				fractal_zip_process_guard_untrack($wpid);
			}
			unset($active[$wpid]);
			$partCount = 0;
			if (is_file($ch['out'])) {
				$rawPart = file_get_contents($ch['out']);
				$decoded = is_string($rawPart) && $rawPart !== '' ? @unserialize($rawPart) : false;
				if (is_array($decoded)) {
					$partCount = count($decoded);
					foreach ($decoded as $sub => $cnt) {
						$substrKept[(string) $sub] = (int) $cnt;
					}
				}
			}
		}
		fractal_zip_enwik_recursive_remove($tmpBase);
		$ordered = array();
		foreach ($keys as $sub) {
			if (isset($substrKept[$sub])) {
				$ordered[$sub] = $substrKept[$sub];
			}
		}
		return $ordered;
	}
}

if (!function_exists('fractal_zip_gpu_substring_all_substrings_rust_slide_map')) {
	/**
	 * Rust fixed-window counter for large haystacks — seeds probe map before legacy slide.
	 * Only counts substrings with verified non-overlapping count ≥ 2 (byte-safe vs PHP substr_count).
	 *
	 * @return array<string, int>|null null when helper unavailable or disabled
	 */
	function fractal_zip_gpu_substring_all_substrings_rust_slide_map(
		fractal_zip $fz,
		string $string,
		int $minLen,
		int $maxLen,
		int $maxRecords
	): ?array {
		if (!fractal_zip_gpu_substring_slide_seeds_enabled()) {
			return null;
		}
		if (strlen($string) < fractal_zip_gpu_substring_peel_min_bytes()) {
			return null;
		}
		$topK = max(64, min(4096, $maxRecords > 0 ? $maxRecords : 512));
		$scored = fractal_zip_gpu_substring_score_top_k($string, $minLen, $maxLen, $topK, false);
		if ($scored === null || ($scored['status'] ?? '') !== 'ok') {
			return null;
		}
		$top = $scored['top_k'] ?? array();
		if (!is_array($top) || $top === array()) {
			return array();
		}
		$out = array();
		$slen = strlen($string);
		foreach ($top as $sub) {
			$sub = (string) $sub;
			$len = strlen($sub);
			if ($len < $minLen || $len * 2 > $slen) {
				continue;
			}
			if (!$fz->is_fractally_clean($sub)) {
				continue;
			}
			$real = substr_count($string, $sub);
			if ($real >= 2) {
				$out[$sub] = $real;
			}
		}
		return $out;
	}
}

if (!function_exists('fractal_zip_gpu_substring_sa_binary_path')) {
	function fractal_zip_gpu_substring_sa_binary_path(): string {
		$root = fractal_zip_gpu_substring_repo_root();
		return $root . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'gpu_substring'
			. DIRECTORY_SEPARATOR . 'target' . DIRECTORY_SEPARATOR . 'release' . DIRECTORY_SEPARATOR . 'substring_sa_enumerate_rs';
	}

	function fractal_zip_gpu_substring_sa_available(): bool {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$bin = fractal_zip_gpu_substring_sa_binary_path();
		$local = fractal_zip_gpu_substring_repo_root() . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'gpu_substring'
			. DIRECTORY_SEPARATOR . 'substring_sa_enumerate_rs';
		return $cached = (is_executable($bin) || is_executable($local));
	}

	/** Opt-in SA duplicate enumeration (default off — preserves legacy all_substrings_count bytes). */
	function fractal_zip_gpu_substring_sa_enabled(): bool {
		if (!fractal_zip_gpu_substring_sa_available()) {
			return false;
		}
		if (fractal_zip_gpu_substring_env_truthy('FRACTAL_ZIP_GPU_SUBSTRING_SA')) {
			return true;
		}
		$e = getenv('FRACTAL_ZIP_GPU_SUBSTRING_SA');
		if ($e !== false && trim((string) $e) !== '') {
			$v = strtolower(trim((string) $e));
			return !($v === '0' || $v === 'false' || $v === 'no' || $v === 'off');
		}
		return false;
	}

	function fractal_zip_gpu_substring_sa_min_bytes(): int {
		$e = getenv('FRACTAL_ZIP_GPU_SUBSTRING_SA_MIN_BYTES');
		if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
			return 4096;
		}
		return max(256, (int) trim((string) $e));
	}

	function fractal_zip_gpu_substring_sa_max_span(): int {
		$e = getenv('FRACTAL_ZIP_GPU_SUBSTRING_SA_MAX_SPAN');
		if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
			return 100;
		}
		return max(8, min(512, (int) trim((string) $e)));
	}

	/**
	 * SA-IS repeat map: substring => non-overlapping substr_count (PHP-compatible).
	 *
	 * @return array<string, int>|null null when helper unavailable
	 */
	function fractal_zip_gpu_substring_sa_repeat_map(string $data, int $minLen, int $maxLen, int $minCount = 2): ?array {
		if (!fractal_zip_gpu_substring_sa_available()) {
			return null;
		}
		$bin = fractal_zip_gpu_substring_sa_binary_path();
		if (!is_executable($bin)) {
			$bin = fractal_zip_gpu_substring_repo_root() . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'gpu_substring'
				. DIRECTORY_SEPARATOR . 'substring_sa_enumerate_rs';
		}
		if (!is_executable($bin)) {
			return null;
		}
		$minLen = max(2, $minLen);
		$maxLen = max($minLen, $maxLen);
		$tmpIn = sys_get_temp_dir() . '/fz_sa_in_' . getmypid() . '_' . bin2hex(random_bytes(4)) . '.bin';
		$tmpOut = sys_get_temp_dir() . '/fz_sa_out_' . getmypid() . '_' . bin2hex(random_bytes(4)) . '.json';
		file_put_contents($tmpIn, $data);
		$cmd = escapeshellarg($bin) . ' ' . escapeshellarg($tmpIn) . ' '
			. (int) $minLen . ' ' . (int) $maxLen . ' ' . escapeshellarg($tmpOut) . ' '
			. max(2, (int) $minCount);
		exec($cmd . ' 2>/dev/null', $_out, $ret);
		@unlink($tmpIn);
		if ($ret !== 0 || !is_file($tmpOut)) {
			@unlink($tmpOut);
			return null;
		}
		$json = json_decode((string) file_get_contents($tmpOut), true);
		@unlink($tmpOut);
		if (!is_array($json) || !isset($json['repeats']) || !is_array($json['repeats'])) {
			return null;
		}
		$map = array();
		foreach ($json['repeats'] as $b64 => $cnt) {
			$raw = base64_decode((string) $b64, true);
			if ($raw === false || $raw === '') {
				continue;
			}
			$map[$raw] = (int) $cnt;
		}
		return $map;
	}

	/**
	 * Merge SA repeats into probe map before verify (all_substrings_count hook).
	 *
	 * @param array<string, int> $substrRecords
	 * @return array<string, int>
	 */
	function fractal_zip_gpu_substring_sa_merge_probe_map(
		fractal_zip $fz,
		string $string,
		array $substrRecords,
		int $minLen,
		int $maxLen
	): array {
		if (!fractal_zip_gpu_substring_sa_enabled()) {
			return $substrRecords;
		}
		$slen = strlen($string);
		if ($slen < fractal_zip_gpu_substring_sa_min_bytes()) {
			return $substrRecords;
		}
		$spanCap = fractal_zip_gpu_substring_sa_max_span();
		$maxLen = min($maxLen, $minLen + $spanCap);
		$saMap = fractal_zip_gpu_substring_sa_repeat_map($string, $minLen, $maxLen);
		if ($saMap === null || $saMap === array()) {
			return $substrRecords;
		}
		foreach ($saMap as $sub => $cnt) {
			if (!$fz->is_fractally_clean($sub)) {
				continue;
			}
			if (!isset($substrRecords[$sub]) || $substrRecords[$sub] < $cnt) {
				$substrRecords[$sub] = $cnt;
			}
		}
		return $substrRecords;
	}
}
