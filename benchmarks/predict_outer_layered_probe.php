<?php
declare(strict_types=1);

/**
 * Layered outer-size probes: fast (tier ≈1) → medium (≈3) on top-3 families (elided when L1 shortlists only one family — same mapped winner) → optional high (≈9 for 7z/arc; zpaq high uses {@see fractal_zip::zpaq_predict_probe_method_for_tier}) on L2 winner when {@see fractal_zip::outer_prediction_layer3_high_enabled}.
 * L1 shortlist and L2 winner use Squash-style curve factors (see predict_outer_curve_factors.php).
 * Requires fractal_zip.php (uses seven_zip_executable, metadata argv, LD paths).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'predict_outer_heuristic.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'predict_outer_curve_factors.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

if (!class_exists('fractal_zip', false)) {
	require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}

/**
 * Wait for process with timeout; drains stdout/stderr pipes when provided.
 *
 * @param resource $proc
 * @param array<int, resource|null> $pipes
 *
 * @return int exit code or -1 on timeout
 */
function predict_outer_proc_finish(float $timeoutSec, $proc, array $pipes): int
{
	$t0 = microtime(true);
	while (true) {
		$st = proc_get_status($proc);
		if (!$st['running']) {
			foreach ($pipes as $px) {
				if (is_resource($px)) {
					stream_get_contents($px);
					fclose($px);
				}
			}

			return proc_close($proc);
		}
		if (microtime(true) - $t0 > $timeoutSec) {
			proc_terminate($proc, 9);
			foreach ($pipes as $px) {
				if (is_resource($px)) {
					@fclose($px);
				}
			}
			proc_close($proc);

			return -1;
		}
		foreach ([1, 2] as $ix) {
			if (isset($pipes[$ix]) && is_resource($pipes[$ix])) {
				stream_get_contents($pipes[$ix]);
			}
		}
		usleep(10000);
	}
}

/**
 * @return int|null archive byte size or null
 */
function predict_outer_7z_archive_bytes(?string $sevenExe, string $innerPath, int $mx, string $cwd, float $timeoutSec): ?int
{
	if ($sevenExe === null || $sevenExe === '' || !is_file($innerPath)) {
		return null;
	}
	if (PHP_VERSION_ID < 70400) {
		return null;
	}
	$mx = max(0, min(9, $mx));
	$arc = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7z_' . bin2hex(random_bytes(8)) . '.7z';
	if (is_file($arc)) {
		@unlink($arc);
	}
	$ldMerged = fractal_zip::ld_library_path_merged_for_home_local();
	$oldLd = getenv('LD_LIBRARY_PATH');
	if ($ldMerged !== null) {
		putenv('LD_LIBRARY_PATH=' . $ldMerged);
	}
	$argv = array_merge(
		[$sevenExe, 'a', '-t7z', '-mx=' . (string) $mx],
		fractal_zip::seven_zip_mmt_argv_from_env(),
		fractal_zip::outer_7z_metadata_strip_argv(),
		['-m0=lzma2', '-bso0', '-bsp0', '-bd', '-y', $arc, $innerPath]
	);
	static $desc7zNoSiUnix, $desc7zNoSiWin;
	if ($desc7zNoSiUnix === null) {
		$desc7zNoSiUnix = [0 => ['file', '/dev/null', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
		$desc7zNoSiWin = [0 => ['file', 'NUL', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
	}
	$desc = DIRECTORY_SEPARATOR === '\\' ? $desc7zNoSiWin : $desc7zNoSiUnix;
	$proc = @proc_open($argv, $desc, $pipes, $cwd, null, fractal_zip::proc_open_bypass_shell_array());
	if ($ldMerged !== null) {
		if ($oldLd === false) {
			putenv('LD_LIBRARY_PATH');
		} else {
			putenv('LD_LIBRARY_PATH=' . $oldLd);
		}
	}
	if (!is_resource($proc)) {
		return null;
	}
	$ex = predict_outer_proc_finish($timeoutSec, $proc, $pipes);
	if ($ex !== 0 || !is_file($arc)) {
		if (is_file($arc)) {
			@unlink($arc);
		}

		return null;
	}
	$n = filesize($arc);
	@unlink($arc);

	return $n !== false ? (int) $n : null;
}

/**
 * @return int|null
 */
function predict_outer_arc_archive_bytes(?string $arcExe, string $innerPath, int $methodNum, float $timeoutSec): ?int
{
	if ($arcExe === null || $arcExe === '' || !is_file($innerPath)) {
		return null;
	}
	$methodNum = max(1, min(9, $methodNum));
	$wd = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzarc_' . bin2hex(random_bytes(8));
	if (!@mkdir($wd, 0700, true)) {
		return null;
	}
	$innerBase = 'i';
	$arcBase = 'o.arc';
	if (!@copy($innerPath, $wd . DIRECTORY_SEPARATOR . $innerBase)) {
		predict_outer_cleanup_probe_tmp($wd);

		return null;
	}
	$return = -1;
	$usedProc = false;
	if (PHP_VERSION_ID >= 70400 && DIRECTORY_SEPARATOR !== '\\') {
		$mtA = fractal_zip::library_arc_compress_mt_argv_after_exe();
		$argv = array_merge(
			[$arcExe],
			$mtA,
			['a', '-m' . (string) $methodNum, '-ep1', '-y', $arcBase, $innerBase]
		);
		$ldMerged = fractal_zip::ld_library_path_merged_for_home_local();
		$oldLd = getenv('LD_LIBRARY_PATH');
		if ($ldMerged !== null) {
			putenv('LD_LIBRARY_PATH=' . $ldMerged);
		}
		static $descArcProcPipe;
		if ($descArcProcPipe === null) {
			$descArcProcPipe = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
		}
		$proc = @proc_open($argv, $descArcProcPipe, $pipes, $wd, null, fractal_zip::proc_open_bypass_shell_array());
		if ($ldMerged !== null) {
			if ($oldLd === false) {
				putenv('LD_LIBRARY_PATH');
			} else {
				putenv('LD_LIBRARY_PATH=' . $oldLd);
			}
		}
		if (is_resource($proc)) {
			fclose($pipes[0]);
			$return = predict_outer_proc_finish($timeoutSec, $proc, [1 => $pipes[1], 2 => $pipes[2]]);
			$usedProc = true;
		}
	}
	if (!$usedProc) {
		$prefix = '';
		$merged = fractal_zip::ld_library_path_merged_for_home_local();
		if ($merged !== null) {
			$prefix = 'LD_LIBRARY_PATH=' . escapeshellarg($merged) . ' ';
		}
		$qExe = fractal_zip::shell_quote_arg_cached($arcExe);
		$mtSh = fractal_zip::library_arc_compress_mt_shell_fragment_for_exec();
		$cmd = $prefix . $qExe . $mtSh . ' a -m' . $methodNum . ' -ep1 -y ' . escapeshellarg($arcBase) . ' ' . escapeshellarg($innerBase);
		$oldCwd = @getcwd();
		@chdir($wd);
		exec($cmd . ' 2>/dev/null', $output, $return);
		if (is_string($oldCwd) && $oldCwd !== '') {
			@chdir($oldCwd);
		}
	}
	$blob = null;
	if ($return === 0 && is_file($wd . DIRECTORY_SEPARATOR . $arcBase)) {
		$blob = filesize($wd . DIRECTORY_SEPARATOR . $arcBase);
	}
	if (is_file($wd . DIRECTORY_SEPARATOR . $arcBase)) {
		@unlink($wd . DIRECTORY_SEPARATOR . $arcBase);
	}
	@unlink($wd . DIRECTORY_SEPARATOR . $innerBase);
	@rmdir($wd);

	return ($blob !== null && $blob !== false && (int) $blob > 0) ? (int) $blob : null;
}

/**
 * @param list<string> $extraArgs appended after output path
 *
 * @return int|null
 */
function predict_outer_bsc_encode_bytes(?string $bscExe, string $innerPath, string $outPath, array $extraArgs, float $timeoutSec): ?int
{
	if ($bscExe === null || $bscExe === '' || !is_file($innerPath)) {
		return null;
	}
	if (is_file($outPath)) {
		@unlink($outPath);
	}
	$cmd = array_merge([$bscExe, 'e', $innerPath, $outPath], $extraArgs);
	$p = proc_open($cmd, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, null, null);
	if (!is_resource($p)) {
		return null;
	}
	fclose($pipes[0]);
	$ex = predict_outer_proc_finish($timeoutSec, $p, [1 => $pipes[1], 2 => $pipes[2]]);
	if ($ex !== 0 || !is_file($outPath)) {
		if (is_file($outPath)) {
			@unlink($outPath);
		}

		return null;
	}
	$n = filesize($outPath);
	@unlink($outPath);

	return $n !== false ? (int) $n : null;
}

/**
 * Probe one codec family at one tier (fast | medium | high).
 *
 * @return array{bytes: int|null, label: string}
 */
function predict_outer_family_tier_bytes(
	string $family,
	string $tier,
	string $blob,
	string $innerPath,
	float $timeoutSec,
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	?string $bscExe,
	?string $zpaqExe,
	?string $sevenExe,
	?string $arcExe,
	string $repoRoot
): array {
	switch ($family) {
		case 'gzip':
			if ($tier === 'fast') {
				$gz = gzdeflate($blob, 1);

				return ['bytes' => $gz !== false ? strlen($gz) : null, 'label' => 'gzip L1'];
			}
			if ($tier === 'medium') {
				$gz = gzdeflate($blob, 3);

				return ['bytes' => $gz !== false ? strlen($gz) : null, 'label' => 'gzip L3'];
			}
			$gz = gzdeflate($blob, 9);

			return ['bytes' => $gz !== false ? strlen($gz) : null, 'label' => 'gzip L9'];
		case 'brotli':
			if ($brotliExe === null) {
				return ['bytes' => null, 'label' => 'brotli'];
			}
			$q = $tier === 'fast' ? 1 : ($tier === 'medium' ? 3 : 11);
			$b = predict_outer_compress_stdin($brotliExe, ['-c', '-q', (string) $q], $blob, $timeoutSec);

			return ['bytes' => $b, 'label' => 'brotli q=' . (string) $q];
		case 'xz':
			if ($xzExe === null) {
				return ['bytes' => null, 'label' => 'xz'];
			}
			$lvl = $tier === 'fast' ? '-1' : ($tier === 'medium' ? '-3' : '-9');
			$x = predict_outer_compress_stdin($xzExe, [$lvl, '-c'], $blob, $timeoutSec);

			return ['bytes' => $x, 'label' => 'xz ' . $lvl];
		case 'zstd':
			if ($zstdExe === null) {
				return ['bytes' => null, 'label' => 'zstd'];
			}
			$lvl = $tier === 'fast' ? '1' : ($tier === 'medium' ? '3' : '19');
			$z = predict_outer_compress_stdin($zstdExe, ['-' . $lvl, '-c'], $blob, $timeoutSec);

			return ['bytes' => $z, 'label' => 'zstd -' . $lvl];
		case '7z':
			if ($sevenExe === null) {
				return ['bytes' => null, 'label' => '7z'];
			}
			$mx = $tier === 'fast' ? 1 : ($tier === 'medium' ? 3 : 9);
			$n = predict_outer_7z_archive_bytes($sevenExe, $innerPath, $mx, $repoRoot, $timeoutSec);

			return ['bytes' => $n, 'label' => '7z -mx=' . (string) $mx . ' lzma2'];
		case 'arc':
			if ($arcExe === null) {
				return ['bytes' => null, 'label' => 'arc'];
			}
			$m = $tier === 'fast' ? 1 : ($tier === 'medium' ? 3 : 9);
			$n = predict_outer_arc_archive_bytes($arcExe, $innerPath, $m, $timeoutSec);

			return ['bytes' => $n, 'label' => 'arc -m' . (string) $m];
		case 'bsc':
			if ($bscExe === null) {
				return ['bytes' => null, 'label' => 'bsc'];
			}
			$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_bsc_' . bin2hex(random_bytes(8)) . '.bsc';
			if ($tier === 'fast') {
				$extra = ['-e0', '-b1'];
			} elseif ($tier === 'medium') {
				$extra = ['-e1', '-b8'];
			} else {
				$extra = ['-e2', '-b25'];
			}
			$n = predict_outer_bsc_encode_bytes($bscExe, $innerPath, $out, $extra, $timeoutSec);
			$lbl = 'bsc ' . implode(' ', $extra);

			return ['bytes' => $n, 'label' => $lbl];
		case 'zpaq':
			if ($zpaqExe === null) {
				return ['bytes' => null, 'label' => 'zpaq'];
			}
			$meth = fractal_zip::zpaq_predict_probe_method_for_tier($tier, strlen($blob));
			$n = predict_outer_zpaq_archive_bytes($zpaqExe, $innerPath, $timeoutSec, $meth);

			return ['bytes' => $n, 'label' => 'zpaq -method ' . (string) $meth];
		default:
			return ['bytes' => null, 'label' => $family];
	}
}

/**
 * Whether layered prediction should fork workers for multi‑family probes (layer‑1 file codecs + layer‑2 medium tier when ≥2 families).
 * {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER_FORK=0} disables; unset defaults on under CLI when {@code pcntl_fork} exists.
 * Legacy alias: {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_FILE_FORK}.
 */
function predict_outer_parallel_predict_layer_fork_enabled(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	if (!function_exists('pcntl_fork')) {
		return $cached = false;
	}
	$e = getenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER_FORK');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));

		return $cached = !($v === '0' || $v === 'false' || $v === 'no' || $v === 'off');
	}
	$legacy = getenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_FILE_FORK');
	if ($legacy !== false && trim((string) $legacy) !== '') {
		$v = strtolower(trim((string) $legacy));

		return $cached = !($v === '0' || $v === 'false' || $v === 'no' || $v === 'off');
	}

	return $cached = PHP_SAPI === 'cli';
}

/**
 * Whether layer‑1 should overlap the brotli/xz/zstd stdin batch with the 7z/arc/bsc/zpaq fork batch (wall ≈ max of the two, not sum).
 * Requires both fast‑pipe and layer‑fork paths enabled. {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_OVERLAP=0} disables; unset defaults on under CLI.
 */
function predict_outer_parallel_layer1_overlap_pipe_file_enabled(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_OVERLAP');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));

		return $cached = !($v === '0' || $v === 'false' || $v === 'no' || $v === 'off');
	}

	return $cached = PHP_SAPI === 'cli';
}

/**
 * Run multiple {@see predict_outer_family_tier_bytes} probes in parallel child processes (one JSON per family).
 *
 * @param list<string> $families
 * @param 'fast'|'medium'|'high' $tier
 *
 * @return array<string, array{bytes: int|null, label: string}>|null null if forks unavailable or catastrophic failure
 */
function predict_outer_fork_family_tier_batch(
	array $families,
	string $tier,
	string $blob,
	string $tmpBlob,
	float $probeTimeoutSec,
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	?string $bscExe,
	?string $zpaqExe,
	?string $sevenExe,
	?string $arcExe,
	string $rRoot
): ?array {
	if ($families === [] || !predict_outer_parallel_predict_layer_fork_enabled()) {
		return null;
	}
	if (!class_exists('fractal_zip_encode_pipeline', false)) {
		require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_encode_pipeline.php';
	}
	$forkMinOuter = fractal_zip_encode_pipeline::parallel_speculative_outer_min_inner_bytes();
	if ($forkMinOuter > 0 && strlen($blob) < $forkMinOuter) {
		return null;
	}
	try {
		$rnd = random_bytes(16);
	} catch (Throwable $e) {
		return null;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_pred_fftb_' . bin2hex($rnd);
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	/** @var list<int> $pids */
	$pids = [];
	foreach ($families as $fam) {
		$pid = pcntl_fork();
		if ($pid === -1) {
			foreach ($pids as $op) {
				if (function_exists('posix_kill')) {
					@posix_kill($op, defined('SIGKILL') ? SIGKILL : 9);
				}
				$st = 0;
				pcntl_waitpid($op, $st);
			}
			predict_outer_cleanup_probe_tmp($td);

			return null;
		}
		if ($pid === 0) {
			try {
				$r = predict_outer_family_tier_bytes(
					$fam,
					$tier,
					$blob,
					$tmpBlob,
					$probeTimeoutSec,
					$brotliExe,
					$xzExe,
					$zstdExe,
					$bscExe,
					$zpaqExe,
					$sevenExe,
					$arcExe,
					$rRoot
				);
				$jsFam = bench_json_encode_try($r, false);
				if ($jsFam !== null) {
					@file_put_contents($td . DIRECTORY_SEPARATOR . $fam . '.json', $jsFam);
				}
			} catch (Throwable $e) {
				// ignore — parent treats missing JSON as null probe
			}
			exit(0);
		}
		$pids[] = $pid;
	}
	foreach ($pids as $wpid) {
		$st = 0;
		pcntl_waitpid($wpid, $st);
	}
	/** @var array<string, array{bytes: int|null, label: string}> $out */
	$out = [];
	foreach ($families as $fam) {
		$jp = $td . DIRECTORY_SEPARATOR . $fam . '.json';
		if (!is_file($jp)) {
			$out[$fam] = ['bytes' => null, 'label' => $fam];

			continue;
		}
		$jr = bench_json_decode_file_assoc_try($jp, 'predict_outer_layered_probe family ' . $fam);
		if ($jr === null) {
			$out[$fam] = ['bytes' => null, 'label' => $fam];

			continue;
		}
		$b = $jr['bytes'] ?? null;
		if ($b !== null && !is_int($b)) {
			$b = is_numeric($b) ? (int) $b : null;
		}
		$lbl = isset($jr['label']) && is_string($jr['label']) ? $jr['label'] : $fam;
		$out[$fam] = ['bytes' => $b, 'label' => $lbl];
	}
	predict_outer_cleanup_probe_tmp($td);

	return $out;
}

/**
 * Layer‑1: 7z / arc / bsc / zpaq fast probes in parallel (wrapper around {@see predict_outer_fork_family_tier_batch}).
 *
 * @return array<string, array{bytes: int|null, label: string}>|null
 */
function predict_outer_layer1_file_families_fork_batch(
	string $blob,
	string $tmpBlob,
	float $probeTimeoutSec,
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	?string $bscExe,
	?string $zpaqExe,
	?string $sevenExe,
	?string $arcExe,
	string $rRoot
): ?array {
	return predict_outer_fork_family_tier_batch(
		['7z', 'arc', 'bsc', 'zpaq'],
		'fast',
		$blob,
		$tmpBlob,
		$probeTimeoutSec,
		$brotliExe,
		$xzExe,
		$zstdExe,
		$bscExe,
		$zpaqExe,
		$sevenExe,
		$arcExe,
		$rRoot
	);
}

/**
 * Append layer‑1 rows for 7z / arc / bsc / zpaq (fast) from fork batch JSON or sequential probes.
 *
 * @param array<string, array{bytes: int|null, label: string}>|null $fileForkOut
 */
function predict_outer_layer1_merge_file_families_fork_or_seq(
	array &$layer1,
	array &$breakdown,
	$fileForkOut,
	string $blob,
	string $tmpBlob,
	float $probeTimeoutSec,
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	?string $bscExe,
	?string $zpaqExe,
	?string $sevenExe,
	?string $arcExe,
	string $rRoot
): void {
	static $fileFams = ['7z', 'arc', 'bsc', 'zpaq'];
	if ($fileForkOut !== null) {
		foreach ($fileFams as $fam) {
			$r = $fileForkOut[$fam] ?? ['bytes' => null, 'label' => $fam];
			if ($r['bytes'] !== null && $r['bytes'] > 0) {
				$layer1[] = ['family' => $fam, 'bytes' => $r['bytes'], 'label' => $r['label']];
				$breakdown['L1:' . $fam] = $r['bytes'];
			}
		}

		return;
	}
	foreach ($fileFams as $fam) {
		$r = predict_outer_family_tier_bytes(
			$fam,
			'fast',
			$blob,
			$tmpBlob,
			$probeTimeoutSec,
			$brotliExe,
			$xzExe,
			$zstdExe,
			$bscExe,
			$zpaqExe,
			$sevenExe,
			$arcExe,
			$rRoot
		);
		if ($r['bytes'] !== null && $r['bytes'] > 0) {
			$layer1[] = ['family' => $fam, 'bytes' => $r['bytes'], 'label' => $r['label']];
			$breakdown['L1:' . $fam] = $r['bytes'];
		}
	}
}

/**
 * Append layer‑2 medium‑tier rows from fork batch or sequential probes.
 *
 * @param array<string, array{bytes: int|null, label: string}>|null $l2ForkOut
 * @param list<string> $top3Families
 */
function predict_outer_layer2_merge_medium_fork_or_seq(
	array &$layer2,
	array &$breakdown,
	$l2ForkOut,
	array $top3Families,
	string $blob,
	string $tmpBlob,
	float $probeTimeoutSec,
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	?string $bscExe,
	?string $zpaqExe,
	?string $sevenExe,
	?string $arcExe,
	string $rRoot
): void {
	if ($l2ForkOut !== null) {
		foreach ($top3Families as $fam) {
			$r = $l2ForkOut[$fam] ?? ['bytes' => null, 'label' => $fam];
			if ($r['bytes'] !== null && $r['bytes'] > 0) {
				$layer2[] = ['family' => $fam, 'bytes' => $r['bytes'], 'label' => $r['label']];
				$breakdown['L2:' . $fam] = $r['bytes'];
			}
		}

		return;
	}
	foreach ($top3Families as $fam) {
		$r = predict_outer_family_tier_bytes(
			$fam,
			'medium',
			$blob,
			$tmpBlob,
			$probeTimeoutSec,
			$brotliExe,
			$xzExe,
			$zstdExe,
			$bscExe,
			$zpaqExe,
			$sevenExe,
			$arcExe,
			$rRoot
		);
		if ($r['bytes'] !== null && $r['bytes'] > 0) {
			$layer2[] = ['family' => $fam, 'bytes' => $r['bytes'], 'label' => $r['label']];
			$breakdown['L2:' . $fam] = $r['bytes'];
		}
	}
}

/**
 * Three-stage layered ranking of outer codecs by compressed size on a capped inner blob.
 *
 * Returned `top3` is layer 2 (medium tier), best-first by **curve-adjusted** score (predicted high-tier competitiveness).
 *
 * @return array{
 *   top3: list<array{codec:string,bytes:int,settings?:string,stage?:string}>,
 *   breakdown: array<string, int|null>,
 *   probe_sample_bytes: int,
 *   chosen_label: string,
 *   chosen_bytes: int|null,
 *   layered: array<string, mixed>,
 * }
 */
function predict_outer_rank_outer_candidates(
	string $fullInner,
	int $probeInnerMax,
	?string $zpaqExe,
	?string $brotliExe,
	?string $xzExe,
	?string $zstdExe,
	?string $bscExe,
	float $probeTimeoutSec,
	?string $repoRoot = null,
	?string $sevenExe = null,
	?string $arcExe = null
): array {
	if ($fullInner === '') {
		return [
			'top3' => [],
			'breakdown' => [],
			'probe_sample_bytes' => 0,
			'chosen_label' => '',
			'chosen_bytes' => null,
			'layered' => [],
		];
	}
	$rRoot = ($repoRoot !== null && $repoRoot !== '') ? $repoRoot : dirname(__DIR__);
	if ($sevenExe === null || $sevenExe === '') {
		$sevenExe = fractal_zip::seven_zip_executable();
	}
	if ($arcExe === null || $arcExe === '') {
		$arcExe = fractal_zip::freearc_executable();
	}

	$cap = max(256, $probeInnerMax);
	$blob = strlen($fullInner) <= $cap ? $fullInner : substr($fullInner, 0, $cap);
	$nIn = strlen($blob);

	$tmpBlob = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_pred_inner_' . bin2hex(random_bytes(8)) . '.bin';
	file_put_contents($tmpBlob, $blob);

	$breakdown = [];

	try {
		$layer1 = [];
		$gzFast = gzdeflate($blob, 1);
		if ($gzFast !== false) {
			$gzb = strlen($gzFast);
			$layer1[] = ['family' => 'gzip', 'bytes' => $gzb, 'label' => 'gzip L1'];
			$breakdown['L1:gzip'] = $gzb;
		}
		$labelsPipe = ['brotli' => 'brotli q=1', 'xz' => 'xz -1', 'zstd' => 'zstd -1'];
		$forkMinPred = fractal_zip_encode_pipeline::parallel_speculative_outer_min_inner_bytes();
		$wantOverlap = predict_outer_parallel_layer1_overlap_pipe_file_enabled()
			&& predict_outer_parallel_layer1_fast_pipe_enabled()
			&& predict_outer_parallel_predict_layer_fork_enabled()
			&& function_exists('pcntl_fork')
			&& ($forkMinPred <= 0 || $nIn >= $forkMinPred);
		$tdOv = null;
		$pipeOverlapPid = null;
		if ($wantOverlap) {
			try {
				$tdOv = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_pred_l1ov_' . bin2hex(random_bytes(8));
			} catch (Throwable $e) {
				$wantOverlap = false;
			}
			if ($wantOverlap && !@mkdir($tdOv, 0700, true)) {
				$wantOverlap = false;
				$tdOv = null;
			}
			if ($wantOverlap) {
				$pipeOverlapPid = pcntl_fork();
				if ($pipeOverlapPid === -1) {
					$wantOverlap = false;
					predict_outer_cleanup_probe_tmp($tdOv);
					$tdOv = null;
				} elseif ($pipeOverlapPid === 0) {
					$tri = predict_outer_compress_stdin_parallel_fast_batch($brotliExe, $xzExe, $zstdExe, $blob, $probeTimeoutSec);
					$jsPipe = bench_json_encode_try($tri, false);
					if ($jsPipe !== null) {
						@file_put_contents($tdOv . DIRECTORY_SEPARATOR . 'pipe.json', $jsPipe);
					}
					exit(0);
				}
			}
		}
		if ($wantOverlap && $pipeOverlapPid !== null && $pipeOverlapPid > 0) {
			$fileForkOut = predict_outer_layer1_file_families_fork_batch(
				$blob,
				$tmpBlob,
				$probeTimeoutSec,
				$brotliExe,
				$xzExe,
				$zstdExe,
				$bscExe,
				$zpaqExe,
				$sevenExe,
				$arcExe,
				$rRoot
			);
			$stPipe = 0;
			pcntl_waitpid($pipeOverlapPid, $stPipe);
			$pipeTri = null;
			$pj = $tdOv . DIRECTORY_SEPARATOR . 'pipe.json';
			if (is_file($pj)) {
				$dec = bench_json_decode_file_assoc_try($pj, 'predict_outer_layered_probe pipe.json');
				if ($dec !== null) {
					$pipeTri = $dec;
				}
			}
			predict_outer_cleanup_probe_tmp($tdOv);
			if (!is_array($pipeTri)) {
				$pipeTri = predict_outer_compress_stdin_parallel_fast_batch($brotliExe, $xzExe, $zstdExe, $blob, $probeTimeoutSec);
			}
			foreach ($labelsPipe as $fam => $lbl) {
				$bytes = $pipeTri[$fam] ?? null;
				if ($bytes !== null && $bytes > 0) {
					$layer1[] = ['family' => $fam, 'bytes' => $bytes, 'label' => $lbl];
					$breakdown['L1:' . $fam] = $bytes;
				}
			}
			predict_outer_layer1_merge_file_families_fork_or_seq(
				$layer1,
				$breakdown,
				$fileForkOut,
				$blob,
				$tmpBlob,
				$probeTimeoutSec,
				$brotliExe,
				$xzExe,
				$zstdExe,
				$bscExe,
				$zpaqExe,
				$sevenExe,
				$arcExe,
				$rRoot
			);
		} elseif (predict_outer_parallel_layer1_fast_pipe_enabled()) {
			$pipeTri = predict_outer_compress_stdin_parallel_fast_batch($brotliExe, $xzExe, $zstdExe, $blob, $probeTimeoutSec);
			foreach ($labelsPipe as $fam => $lbl) {
				$bytes = $pipeTri[$fam] ?? null;
				if ($bytes !== null && $bytes > 0) {
					$layer1[] = ['family' => $fam, 'bytes' => $bytes, 'label' => $lbl];
					$breakdown['L1:' . $fam] = $bytes;
				}
			}
			$fileForkOut = predict_outer_layer1_file_families_fork_batch(
				$blob,
				$tmpBlob,
				$probeTimeoutSec,
				$brotliExe,
				$xzExe,
				$zstdExe,
				$bscExe,
				$zpaqExe,
				$sevenExe,
				$arcExe,
				$rRoot
			);
			predict_outer_layer1_merge_file_families_fork_or_seq(
				$layer1,
				$breakdown,
				$fileForkOut,
				$blob,
				$tmpBlob,
				$probeTimeoutSec,
				$brotliExe,
				$xzExe,
				$zstdExe,
				$bscExe,
				$zpaqExe,
				$sevenExe,
				$arcExe,
				$rRoot
			);
		} else {
			foreach (['brotli', 'xz', 'zstd'] as $fam) {
				$r = predict_outer_family_tier_bytes(
					$fam,
					'fast',
					$blob,
					$tmpBlob,
					$probeTimeoutSec,
					$brotliExe,
					$xzExe,
					$zstdExe,
					$bscExe,
					$zpaqExe,
					$sevenExe,
					$arcExe,
					$rRoot
				);
				if ($r['bytes'] !== null && $r['bytes'] > 0) {
					$layer1[] = ['family' => $fam, 'bytes' => $r['bytes'], 'label' => $r['label']];
					$breakdown['L1:' . $fam] = $r['bytes'];
				}
			}
			$fileForkOut = predict_outer_layer1_file_families_fork_batch(
				$blob,
				$tmpBlob,
				$probeTimeoutSec,
				$brotliExe,
				$xzExe,
				$zstdExe,
				$bscExe,
				$zpaqExe,
				$sevenExe,
				$arcExe,
				$rRoot
			);
			predict_outer_layer1_merge_file_families_fork_or_seq(
				$layer1,
				$breakdown,
				$fileForkOut,
				$blob,
				$tmpBlob,
				$probeTimeoutSec,
				$brotliExe,
				$xzExe,
				$zstdExe,
				$bscExe,
				$zpaqExe,
				$sevenExe,
				$arcExe,
				$rRoot
			);
		}
		$layer1Ranked = predict_outer_sort_probe_rows_curve_adjusted(predict_outer_enrich_rows_curve_scores($layer1, 'fast'));
		$top3Families = [];
		foreach ($layer1Ranked as $row) {
			if (!in_array($row['family'], $top3Families, true)) {
				$top3Families[] = $row['family'];
			}
			if (count($top3Families) >= 3) {
				break;
			}
		}

		$layer2 = [];
		$l2ForkOut = null;
		// When L1 only has one family in the top-3 shortlist, L2 would run a single medium-tier probe for that same
		// family — the mapped winner_family string is already determined; reuse L1 row bytes and skip subprocesses.
		if (count($top3Families) === 1) {
			$onlyFam = $top3Families[0];
			foreach ($layer1Ranked as $row) {
				if (($row['family'] ?? '') === $onlyFam && isset($row['bytes']) && (int) $row['bytes'] > 0) {
					$b1 = (int) $row['bytes'];
					$layer2[] = [
						'family' => $onlyFam,
						'bytes' => $b1,
						'label' => (string) ($row['label'] ?? $onlyFam),
					];
					$breakdown['L2:' . $onlyFam] = $b1;

					break;
				}
			}
		} else {
			if (count($top3Families) >= 2) {
				$l2ForkOut = predict_outer_fork_family_tier_batch(
					$top3Families,
					'medium',
					$blob,
					$tmpBlob,
					$probeTimeoutSec,
					$brotliExe,
					$xzExe,
					$zstdExe,
					$bscExe,
					$zpaqExe,
					$sevenExe,
					$arcExe,
					$rRoot
				);
			}
			predict_outer_layer2_merge_medium_fork_or_seq(
				$layer2,
				$breakdown,
				$l2ForkOut,
				$top3Families,
				$blob,
				$tmpBlob,
				$probeTimeoutSec,
				$brotliExe,
				$xzExe,
				$zstdExe,
				$bscExe,
				$zpaqExe,
				$sevenExe,
				$arcExe,
				$rRoot
			);
		}
		$layer2Ranked = predict_outer_sort_probe_rows_curve_adjusted(predict_outer_enrich_rows_curve_scores($layer2, 'medium'));
		$winnerFamily = $layer2Ranked[0]['family'] ?? ($layer1Ranked[0]['family'] ?? '');
		$chosenBytes = null;
		$chosenLabel = '';
		if ($winnerFamily !== '') {
			// L3 high tier refines compressed-size estimate only; mapped outer family comes from L2 ranking (unchanged after L3).
			if (fractal_zip::outer_prediction_layer3_high_enabled()) {
				$r = predict_outer_family_tier_bytes(
					$winnerFamily,
					'high',
					$blob,
					$tmpBlob,
					$probeTimeoutSec,
					$brotliExe,
					$xzExe,
					$zstdExe,
					$bscExe,
					$zpaqExe,
					$sevenExe,
					$arcExe,
					$rRoot
				);
				$chosenBytes = $r['bytes'];
				$chosenLabel = $r['label'];
				if ($chosenBytes !== null && $chosenBytes > 0) {
					$breakdown['L3:' . $winnerFamily] = $chosenBytes;
				}
			} else {
				$l2top = $layer2Ranked[0] ?? null;
				if (is_array($l2top) && isset($l2top['bytes']) && (int) $l2top['bytes'] > 0) {
					$chosenBytes = (int) $l2top['bytes'];
					$chosenLabel = isset($l2top['label']) ? (string) $l2top['label'] : '';
				} elseif ($layer2Ranked === [] && isset($layer1Ranked[0]['bytes']) && (int) $layer1Ranked[0]['bytes'] > 0) {
					$chosenBytes = (int) $layer1Ranked[0]['bytes'];
					$chosenLabel = isset($layer1Ranked[0]['label']) ? (string) $layer1Ranked[0]['label'] : '';
				}
			}
		}

		$top3Table = [];
		for ($i = 0; $i < min(3, count($layer2Ranked)); $i++) {
			$row = $layer2Ranked[$i];
			$top3Table[] = [
				'codec' => $row['family'],
				'bytes' => $row['bytes'],
				'settings' => $row['label'],
				'stage' => 'L2',
				'curve_adjusted_score' => $row['curve_adjusted_score'] ?? null,
			];
		}

		return [
			'top3' => $top3Table,
			'breakdown' => $breakdown,
			'probe_sample_bytes' => $nIn,
			'chosen_label' => $chosenLabel,
			'chosen_bytes' => ($chosenBytes !== null && $chosenBytes > 0) ? $chosenBytes : null,
			'layered' => [
				'curve_model' => 'squash_aggregate_v1',
				'layer1' => $layer1Ranked,
				'layer2' => $layer2Ranked,
				'winner_family' => $winnerFamily,
				'chosen_label' => $chosenLabel,
				'chosen_bytes' => $chosenBytes,
			],
		];
	} finally {
		if (is_file($tmpBlob)) {
			@unlink($tmpBlob);
		}
	}
}
