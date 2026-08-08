#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Wall-time A/B for pipeline parallel and inner frontier fork pools.
 *
 * Usage: php benchmarks/bench_fractal_parallel_phases.php [--pages=N]
 *
 * Default corpus is smoke_text_triplet (fast). Pass --pages=N to use enwik8_sample5 pages instead.
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();
register_shutdown_function(static function (): void {
	fractal_zip_process_guard_sweep_strays(dirname(__DIR__));
});
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$pageCount = 5;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageCount = max(1, (int) substr($arg, 8));
	}
}

/** @return array{dir: string, page_count: int, source: string} */
function bench_parallel_build_corpus(string $repo, int $pageCount): array {
	$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_parallel_phases_' . getmypid();
	fractal_zip_enwik_recursive_remove($work);
	@mkdir($work, 0755, true);

	if ($pageCount > 0) {
		$sampleDir = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'corpus' . DIRECTORY_SEPARATOR . 'enwik8_sample5';
		$manifestPath = $sampleDir . DIRECTORY_SEPARATOR . 'manifest.json';
		if (is_file($manifestPath)) {
			$manifest = json_decode((string) file_get_contents($manifestPath), true);
			$pages = $manifest['pages'] ?? array();
			$n = min($pageCount, count($pages));
			for ($i = 0; $i < $n; $i++) {
				$p = $pages[$i];
				$from = $sampleDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $p['text_path']);
				copy($from, $work . DIRECTORY_SEPARATOR . basename($from));
			}
			return array('dir' => $work, 'page_count' => $n, 'source' => 'enwik8_sample5_text');
		}
	}

	file_put_contents($work . DIRECTORY_SEPARATOR . 'alpha.txt', str_repeat("alpha payload line\n", 4000));
	file_put_contents($work . DIRECTORY_SEPARATOR . 'beta.txt', str_repeat("beta payload line\n", 3500));
	file_put_contents($work . DIRECTORY_SEPARATOR . 'gamma.txt', str_repeat("gamma payload line\n", 3000));
	return array('dir' => $work, 'page_count' => 3, 'source' => 'smoke_text_triplet');
}

/** @return array{wall_total_ms: float, phase4_ms: float, wall_seconds: float, fzc_bytes: int, FRACTAL_ZIP_PIPELINE_PARALLEL: string} */
function bench_parallel_run_zip_folder_timed(string $repo, string $dir, bool $pipelineParallel): array {
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=' . ($pipelineParallel ? '1' : '0'));
	$fzc = rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
	@unlink($fzc);
	$errFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_pp_err_' . getmypid() . '_' . ($pipelineParallel ? '1' : '0') . '.log';
	$php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
	$worker = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.bench_parallel_zip_worker.php';
	$cmd = escapeshellarg($php) . ' ' . escapeshellarg($worker) . ' ' . escapeshellarg($dir) . ' '
		. ($pipelineParallel ? '1' : '0') . ' 2>' . escapeshellarg($errFile);
	$t0 = microtime(true);
	exec($cmd, $xo, $ret);
	$wallSec = microtime(true) - $t0;
	$stderrBuf = is_file($errFile) ? (string) file_get_contents($errFile) : '';
	@unlink($errFile);
	$wallTotalMs = 0.0;
	$phase4Ms = 0.0;
	$fzcBytes = 0;
	if ($ret === 0 && isset($xo[0])) {
		$decoded = json_decode((string) $xo[0], true);
		if (is_array($decoded)) {
			$fzcBytes = (int) ($decoded['fzc_bytes'] ?? 0);
			$wallSec = (float) ($decoded['wall_seconds'] ?? $wallSec);
		}
	}
	if (preg_match('/\[fz pipeline timing\] wall_total_ms=([0-9.]+)/', $stderrBuf, $m)) {
		$wallTotalMs = (float) $m[1];
	}
	if (preg_match('/phase 4 outer codecs \(checkpoint → zip_folder return[^)]*\): ([0-9.]+) ms/', $stderrBuf, $m)) {
		$phase4Ms = (float) $m[1];
	}
	if ($fzcBytes <= 0 && is_file($fzc)) {
		$fzcBytes = (int) filesize($fzc);
	}
	if ($wallTotalMs <= 0.0) {
		$wallTotalMs = $wallSec * 1000.0;
	}
	return array(
		'wall_total_ms' => round($wallTotalMs, 2),
		'phase4_ms' => round($phase4Ms, 2),
		'wall_seconds' => round($wallSec, 4),
		'fzc_bytes' => $fzcBytes,
		'FRACTAL_ZIP_PIPELINE_PARALLEL' => $pipelineParallel ? '1' : '0',
	);
}

/** @return array<string, mixed> */
function bench_parallel_pipeline_case(string $repo, string $corpusDir): array {
	$serial = bench_parallel_run_zip_folder_timed($repo, $corpusDir, false);
	$parallel = bench_parallel_run_zip_folder_timed($repo, $corpusDir, true);
	$serialMs = (float) ($serial['wall_total_ms'] ?? 0.0);
	$parallelMs = (float) ($parallel['wall_total_ms'] ?? 0.0);
	$speedup = $parallelMs > 0.0 ? round($serialMs / $parallelMs, 2) : 0.0;
	$bytesMatch = ((int) ($serial['fzc_bytes'] ?? 0)) === ((int) ($parallel['fzc_bytes'] ?? 0));
	return array(
		'case' => 'pipeline_parallel',
		'serial' => $serial,
		'parallel' => $parallel,
		'speedup' => $speedup,
		'bytes_match' => $bytesMatch,
		'pass_gate' => $speedup >= 1.10 && $bytesMatch,
	);
}

/** @return array{dir: string, member_count: int} */
function bench_parallel_peel_build_corpus(): array {
	$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_parallel_peel_' . getmypid();
	fractal_zip_enwik_recursive_remove($work);
	@mkdir($work, 0755, true);
	$sizes = array(
		'large_payload.bin' => 180000,
		'medium_payload.bin' => 120000,
		'small_payload.bin' => 80000,
		'tiny_payload.bin' => 40000,
	);
	foreach ($sizes as $name => $n) {
		file_put_contents($work . DIRECTORY_SEPARATOR . $name, str_repeat(chr(65 + (crc32($name) % 26)), $n));
	}
	$zlib = gzcompress(str_repeat("zlib peel line\n", 6000));
	if (is_string($zlib)) {
		file_put_contents($work . DIRECTORY_SEPARATOR . 'wrapped_zlib.bin', $zlib);
	}
	$deflated = gzdeflate(str_repeat("deflate peel line\n", 5000));
	if (is_string($deflated)) {
		file_put_contents($work . DIRECTORY_SEPARATOR . 'wrapped_deflate.bin', $deflated);
	}
	return array('dir' => $work, 'member_count' => count($sizes) + 2);
}

/**
 * @param array<string, string> $rawByPath
 * @return array{wall_seconds: float, variant_count: int, FRACTAL_ZIP_PEEL_JOBS: string}
 */
function bench_parallel_peel_run_collect(fractal_zip $fz, array $rawByPath, int $peelJobs): array {
	putenv('FRACTAL_ZIP_PEEL_JOBS=' . (string) $peelJobs);
	putenv('FRACTAL_ZIP_PEEL_GPU=0');
	$t0 = microtime(true);
	$variants = $fz->collect_fractal_inner_peeler_variants_for_folder($rawByPath);
	$sec = microtime(true) - $t0;
	return array(
		'wall_seconds' => round($sec, 4),
		'variant_count' => is_array($variants) ? count($variants) : 0,
		'FRACTAL_ZIP_PEEL_JOBS' => (string) $peelJobs,
	);
}

/** @return array<string, mixed> */
function bench_parallel_peel_case(string $repo): array {
	$pcntl = function_exists('pcntl_fork') && function_exists('pcntl_wait');
	if (!$pcntl) {
		return array(
			'case' => 'parallel_peel',
			'status' => 'skipped',
			'reason' => 'pcntl unavailable',
			'pass_gate' => null,
		);
	}
	$corpus = bench_parallel_peel_build_corpus();
	$fz = new fractal_zip(256, false, false, null, false);
	$rawByPath = array();
	$it = new DirectoryIterator($corpus['dir']);
	foreach ($it as $item) {
		if ($item->isFile()) {
			$rawByPath[$item->getFilename()] = (string) file_get_contents($item->getPathname());
		}
	}
	ksort($rawByPath, SORT_STRING);
	$serial = bench_parallel_peel_run_collect($fz, $rawByPath, 1);
	$parallel = bench_parallel_peel_run_collect($fz, $rawByPath, 4);
	$serialSec = (float) ($serial['wall_seconds'] ?? 0.0);
	$parallelSec = (float) ($parallel['wall_seconds'] ?? 0.0);
	$speedup = $parallelSec > 0.0 ? round($serialSec / $parallelSec, 2) : 0.0;
	$countMatch = ((int) ($serial['variant_count'] ?? 0)) === ((int) ($parallel['variant_count'] ?? 0));
	fractal_zip_enwik_recursive_remove($corpus['dir']);
	return array(
		'case' => 'parallel_peel',
		'status' => 'ok',
		'member_count' => $corpus['member_count'],
		'serial' => $serial,
		'parallel' => $parallel,
		'speedup' => $speedup,
		'variant_count_match' => $countMatch,
		'pass_gate' => $speedup >= 1.05 && $countMatch,
		'gpu_report' => fractal_zip_gpu_substring_availability_report(),
	);
}

/** @return array<string, mixed> */
function bench_parallel_inner_frontier_case(string $repo): array {
	$pcntl = function_exists('pcntl_fork') && function_exists('pcntl_wait');
	if (!$pcntl) {
		return array(
			'case' => 'inner_frontier_jobs',
			'status' => 'skipped',
			'reason' => 'pcntl unavailable',
			'pass_gate' => null,
		);
	}
	$php = PHP_BINARY !== '' ? PHP_BINARY : 'php';
	$smoke = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'smoke_random.php';
	$run = static function (int $jobs) use ($php, $smoke): array {
		$errFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_if_err_' . getmypid() . "_{$jobs}.log";
		$cmd = escapeshellarg($php)
			. ' ' . escapeshellarg($smoke)
			. ' --case=23 --max-passes=2 --quiet-inner --no-inner-piece-jobs'
			. ' --inner-frontier-jobs=' . (string) $jobs
			. ' 2>' . escapeshellarg($errFile);
		putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
		putenv('FZ_INNER_FRONTIER_JOBS=' . (string) $jobs);
		putenv('FZ_INNER_PIECE_JOBS=1');
		$t0 = microtime(true);
		exec($cmd, $xo, $ret);
		$sec = microtime(true) - $t0;
		$stderr = is_file($errFile) ? (string) file_get_contents($errFile) : '';
		@unlink($errFile);
		$pass1 = 0;
		foreach ($xo as $line) {
			if (str_starts_with($line, 'pass1_candidate_count=')) {
				$pass1 = (int) substr($line, 20);
			}
		}
		return array(
			'wall_seconds' => round($sec, 4),
			'exit_code' => $ret,
			'pass1_candidate_count' => $pass1,
			'FZ_INNER_FRONTIER_JOBS' => (string) $jobs,
			'stderr_tail' => implode("\n", array_slice(explode("\n", trim($stderr)), -8)),
		);
	};
	$serial = $run(1);
	$parallel = $run(4);
	$serialSec = (float) ($serial['wall_seconds'] ?? 0.0);
	$parallelSec = (float) ($parallel['wall_seconds'] ?? 0.0);
	$speedup = $parallelSec > 0.0 ? round($serialSec / $parallelSec, 2) : 0.0;
	return array(
		'case' => 'inner_frontier_jobs',
		'status' => 'ok',
		'serial' => $serial,
		'parallel' => $parallel,
		'speedup' => $speedup,
		'pass_gate' => $speedup >= 1.05
			&& (int) ($serial['exit_code'] ?? 1) === 0
			&& (int) ($parallel['exit_code'] ?? 1) === 0,
	);
}

$corpus = bench_parallel_build_corpus($repo, $pageCount);

$out = array(
	'generated' => date('c'),
	'corpus' => $corpus,
	'pcntl_available' => function_exists('pcntl_fork') && function_exists('pcntl_wait'),
	'cases' => array(
		bench_parallel_pipeline_case($repo, $corpus['dir']),
		bench_parallel_inner_frontier_case($repo),
	),
);

$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.fractal_parallel_phases.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo "fractal parallel phases → {$path}\n";
foreach ($out['cases'] as $case) {
	$name = (string) ($case['case'] ?? '?');
	if (($case['status'] ?? 'ok') === 'skipped') {
		echo "  {$name}: SKIP (" . ($case['reason'] ?? '') . ")\n";
		continue;
	}
	echo "  {$name}: speedup=" . ($case['speedup'] ?? 0)
		. ' pass=' . (($case['pass_gate'] ?? false) ? 'yes' : 'no') . "\n";
}
fractal_zip_enwik_recursive_remove($corpus['dir']);
@unlink(rtrim($corpus['dir'], DIRECTORY_SEPARATOR) . '.fz');
