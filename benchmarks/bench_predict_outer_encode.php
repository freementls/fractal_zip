<?php
declare(strict_types=1);

/**
 * Run fractal_zip zip_folder through literal/transform phases, dump pre-outer inner bytes,
 * then skip outer codecs via FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY (see adaptive_compress in fractal_zip.php).
 *
 * Caller sets FRACTAL_ZIP_FOLDER_GZIP_FAST for heavy corpora like run_benchmarks (see bench_predict_outer_heavy_corpora_folder_gzip_fast_default).
 * Wrappers that spawn **`run_benchmarks.php`** can use **`bench_corpora_should_pass_large_to_run_benchmarks()`** to mirror **`--large`** policy.
 */

/**
 * Same list as run_benchmarks.php ($heavyCorporaFolderGzipFastDefault).
 *
 * @return list<string>
 */
function bench_predict_outer_heavy_corpora_folder_gzip_fast_default(): array
{
	return [
		'test_files35',
		'test_files61',
		'test_files54',
		'test_files55',
		'test_files56',
		'test_files57',
		'test_files58',
		'test_files59',
		'test_files58_sample',
		'test_files59_sample',
		'test_files55_stratified',
		'test_files133',
	];
}

function bench_predict_outer_heavy_folder_gzip_fast_min_raw_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES');
	if ($e !== false && trim((string) $e) !== '' && is_numeric($e)) {
		$n = (int) trim((string) $e);

		return $n > 0 ? $n : 128 * 1024 * 1024;
	}

	return 128 * 1024 * 1024;
}

/**
 * Whether a wrapper invoking **`run_benchmarks.php`** should pass **`--large`** for ratio parity with the main bench
 * driver: **`$corpusBasename`** is on **`bench_predict_outer_heavy_corpora_folder_gzip_fast_default()`** and unpacked
 * raw file bytes under **`$repoRoot/$corpusBasename`** are ≥ **`bench_predict_outer_heavy_folder_gzip_fast_min_raw_bytes()`**.
 *
 * @see bench_predict_outer_heavy_corpora_folder_gzip_fast_default
 * @see bench_predict_outer_heavy_folder_gzip_fast_min_raw_bytes
 */
function bench_corpora_should_pass_large_to_run_benchmarks(string $repoRoot, string $corpusBasename): bool
{
	$corpusDir = $repoRoot . DIRECTORY_SEPARATOR . $corpusBasename;
	if (!is_dir($corpusDir)) {
		return false;
	}
	if (!in_array($corpusBasename, bench_predict_outer_heavy_corpora_folder_gzip_fast_default(), true)) {
		return false;
	}
	$raw = bench_predict_outer_folder_raw_file_bytes_total($corpusDir);

	return $raw >= bench_predict_outer_heavy_folder_gzip_fast_min_raw_bytes();
}

/**
 * Recursive sum of regular-file sizes under {@code $dir} (no per-path map). Prefer over
 * {@see bench_predict_outer_collect_folder_files} when only the total is needed (e.g. heavy-folder gates on huge trees).
 */
function bench_predict_outer_folder_raw_file_bytes_total(string $dir): int
{
	if (!is_dir($dir)) {
		return 0;
	}
	$total = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$n = $fileInfo->getSize();
		if ($n === false) {
			continue;
		}
		$total += (int) $n;
	}

	return $total;
}

/**
 * @return array{files: array<string,int>, total: int}
 */
function bench_predict_outer_collect_folder_files(string $dir): array
{
	$files = [];
	$total = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		$n = filesize($path);
		if ($n === false) {
			continue;
		}
		$files[$rel] = $n;
		$total += $n;
	}

	return ['files' => $files, 'total' => $total];
}

function bench_predict_outer_remove_dir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	try {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $item) {
			$p = $item->getPathname();
			if ($item->isDir()) {
				@rmdir($p);
			} else {
				@unlink($p);
			}
		}
		@rmdir($dir);
	} catch (Throwable $e) {
		@rmdir($dir);
	}
}

/** Delete everything under $workRoot (same role as run_benchmarks benchSweepBenchmarkWorkRoot). */
function bench_predict_outer_sweep_work_root(string $workRoot): void
{
	if (!is_dir($workRoot)) {
		return;
	}
	$items = @scandir($workRoot);
	if ($items === false) {
		return;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		$p = $workRoot . DIRECTORY_SEPARATOR . $item;
		if (is_dir($p)) {
			bench_predict_outer_remove_dir($p);
		} else {
			@unlink($p);
		}
	}
}

function bench_predict_outer_copy_dir(string $src, string $dst): void
{
	if (is_dir($dst)) {
		bench_predict_outer_remove_dir($dst);
	}
	mkdir($dst, 0755, true);
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$sub = $it->getSubPathname();
		$target = $dst . DIRECTORY_SEPARATOR . $sub;
		if ($item->isDir()) {
			mkdir($target, 0755, true);
		} else {
			$parent = dirname($target);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			if (!copy($item->getPathname(), $target)) {
				throw new RuntimeException('copy failed: ' . $item->getPathname());
			}
		}
	}
}

/** @return array{had: bool, value: string|null} */
function bench_predict_outer_save_folder_gzip_fast_env(): array
{
	$v = getenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');

	return ['had' => $v !== false, 'value' => $v === false ? null : $v];
}

/** @param array{had: bool, value: string|null} $saved */
function bench_predict_outer_restore_folder_gzip_fast_env(array $saved): void
{
	if (!$saved['had']) {
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
	} else {
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=' . ($saved['value'] ?? ''));
	}
}

function bench_predict_outer_benchmark_segment_length(): int
{
	$e = getenv('FRACTAL_ZIP_SEGMENT_LENGTH');
	if ($e !== false && $e !== '' && is_numeric($e)) {
		$n = (int) $e;

		return max(8, min(500000, $n));
	}
	if (fractal_zip::lifestyle_speed_profile_enabled()) {
		return 1000;
	}

	return fractal_zip::DEFAULT_SEGMENT_LENGTH;
}

function bench_predict_outer_resolve_j_curve_w_scale(): float
{
	$e = getenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
	if ($e !== false && trim((string) $e) !== '' && is_numeric($e)) {
		return max(0.0, min(1.0, (float) $e));
	}

	return fractal_zip::J_CURVE_W_SCALE_DEFAULT_BENCH;
}

/**
 * Encode-only: zip_folder with outer tournament skipped after inner is finalized (same phases as run_benchmarks encode, no gzip/7z/min-ext).
 *
 * @return array{
 *   ok: bool,
 *   error?: string,
 *   raw_bytes: int,
 *   inner_bytes: int,
 *   zip_seconds: float,
 *   inner_blob?: string,
 * }
 */
function bench_predict_outer_zip_folder_inner_only(
	string $repoRoot,
	string $corpusLabel,
	string $workRoot,
	bool $noMultipass,
	bool $benchAdaptiveMarkers
): array {
	$src = $repoRoot . DIRECTORY_SEPARATOR . $corpusLabel;
	if (!is_dir($src)) {
		return ['ok' => false, 'error' => 'not a directory', 'raw_bytes' => 0, 'inner_bytes' => 0, 'zip_seconds' => 0.0];
	}

	$rawTotal = bench_predict_outer_folder_raw_file_bytes_total($src);
	$work = $workRoot . DIRECTORY_SEPARATOR . $corpusLabel;

	$savedBrotliHugeMode61 = getenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
	$brotliHuge61Touched = false;
	if ($corpusLabel === 'test_files61') {
		putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=full');
		$brotliHuge61Touched = true;
	}

	$savedBundleMinFiles = getenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
	$bundleMinFilesTouched = false;
	if ($corpusLabel === 'test_files62') {
		putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=65536');
		$bundleMinFilesTouched = true;
	}

	$dumpPath = $workRoot . DIRECTORY_SEPARATOR . '_predict_outer_inner_' . $corpusLabel . '.bin';

	try {
		bench_predict_outer_copy_dir($src, $work);
		$fzcPath = $work . '.fz';
		if (is_file($fzcPath)) {
			unlink($fzcPath);
		}
		if (is_file($dumpPath)) {
			unlink($dumpPath);
		}

		$seg = bench_predict_outer_benchmark_segment_length();
		$useMultipass = !$noMultipass;
		$benchFzcOverrides = null;

		$savedJCurveWScale = getenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
		putenv('FRACTAL_ZIP_J_CURVE_W_SCALE=' . (string) bench_predict_outer_resolve_j_curve_w_scale());

		putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER=' . $dumpPath);
		putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY=1');

		if ($benchAdaptiveMarkers) {
			putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');
			putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
		}

		$fz = new fractal_zip($seg, $useMultipass, true, $benchFzcOverrides, $useMultipass);
		$t0 = microtime(true);
		ob_start();
		try {
			$fz->zip_folder($work, false);
		} finally {
			ob_end_clean();
		}
		$zipSeconds = microtime(true) - $t0;

		putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
		putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY');

		if ($savedJCurveWScale === false) {
			putenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
		} else {
			putenv('FRACTAL_ZIP_J_CURVE_W_SCALE=' . $savedJCurveWScale);
		}

		if ($benchAdaptiveMarkers) {
			putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS');
			putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM');
		}

		$innerBlob = is_readable($dumpPath) ? (string) file_get_contents($dumpPath) : '';
		$innerBytes = strlen($innerBlob);

		bench_predict_outer_remove_dir($work);
		if (is_file($fzcPath)) {
			@unlink($fzcPath);
		}
		if (is_file($dumpPath)) {
			@unlink($dumpPath);
		}

		return [
			'ok' => true,
			'raw_bytes' => $rawTotal,
			'inner_bytes' => $innerBytes,
			'zip_seconds' => $zipSeconds,
			'inner_blob' => $innerBlob,
		];
	} catch (Throwable $e) {
		putenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
		putenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY');
		bench_predict_outer_remove_dir($work);
		if (is_file($dumpPath)) {
			@unlink($dumpPath);
		}
		$fzcPath = $work . '.fz';
		if (is_file($fzcPath)) {
			@unlink($fzcPath);
		}

		return ['ok' => false, 'error' => $e->getMessage(), 'raw_bytes' => $rawTotal, 'inner_bytes' => 0, 'zip_seconds' => 0.0];
	} finally {
		if ($brotliHuge61Touched) {
			if ($savedBrotliHugeMode61 === false) {
				putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
			} else {
				putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=' . $savedBrotliHugeMode61);
			}
		}
		if ($bundleMinFilesTouched) {
			if ($savedBundleMinFiles === false) {
				putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
			} else {
				putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=' . $savedBundleMinFiles);
			}
		}
	}
}
