#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * A/B/C: default vs adaptive markers vs adaptive+metastruct (full .fz + strict byte verify).
 *
 *   php benchmarks/bench_metastruct_adapt.php --corpus=test_files53 --ultra
 *   php benchmarks/bench_metastruct_adapt.php --corpus=test_files75 --legacy-fractal
 *
 * --legacy-fractal: force create_fractal_zip_markers path (disables unified stream + run-grammar early exit).
 * Without it, adaptive/metastruct env vars are set but production zip_folder often still uses unified stream
 * (FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY defaults on), so marker tuning is a no-op.
 */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';

/** @param array<int,string> $argvFull */
function bench_metastruct_cli_argv_has_ultra(array $argvFull): bool {
	foreach ($argvFull as $a) {
		if ($a === '--ultra') {
			return true;
		}
	}
	return false;
}

function bench_metastruct_putenv_if_unset(string $k, string $v): void {
	$e = getenv($k);
	if ($e === false || trim((string) $e) === '') {
		putenv($k . '=' . $v);
	}
}

function bench_metastruct_apply_ultra_env_defaults(): void {
	putenv('FRACTAL_ZIP_SPEED=0');
	putenv('FRACTAL_ZIP_ULTRA=1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '0');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER', '0');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES', '0');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL', '9');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL', '9');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES', '16777216');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES', '33554432');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO', '0');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_LITERAL_LARGE_TEXT_SKIP_PROBE_GZIP1_MIN_RATIO', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC', '0');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_MULTIPASS', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_MULTIPASS_MAX_ADDITIONAL_PASSES', 'unlimited');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD', '0.01');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_MULTIPASS_GATE_MULT', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_DEEP', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_ZSTD_LEVEL', '22');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_BROTLI_QUALITY', '11');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'full');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_ALWAYS_TRY_BROTLI', '1');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND', '32');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES', '16777216');
	bench_metastruct_putenv_if_unset('FRACTAL_ZIP_WHOLE_STREAM_FZWS', '1');
}

/** Suppress fractal_zip HTML/trace during zip/extract (CLI parity). */
function bench_metastruct_ob(callable $fn): void {
	ob_start();
	try {
		$fn();
	} finally {
		ob_end_clean();
	}
}

/** @return array{label: string, fzc_bytes: int, verify_ok: bool, mid: string, metastruct: ?array, path: array{unified_stream: bool, gzip_fast: bool}} */
function bench_metastruct_run_mode(string $corpusPath, string $mode, bool $literalBiasOnly = false): array {
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_ms_' . bin2hex(random_bytes(5));
	$stage = $td . DIRECTORY_SEPARATOR . 'corpus';
	@mkdir($td, 0755, true);
	bench_metastruct_copytree($corpusPath, $stage);

	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS');
	putenv('FRACTAL_ZIP_METastruct');
	putenv('FRACTAL_ZIP_METastruct_LITERAL_BIAS');
	fractal_zip::$last_metastruct_descriptor = null;

	if ($literalBiasOnly) {
		putenv('FRACTAL_ZIP_METastruct=1');
		putenv('FRACTAL_ZIP_METastruct_LITERAL_BIAS=1');
	} elseif ($mode === 'adaptive' || $mode === 'metastruct') {
		putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');
	}
	if ($mode === 'metastruct') {
		putenv('FRACTAL_ZIP_METastruct=1');
	}

	$fz = new fractal_zip(120, true, false, null, false);
	bench_metastruct_ob(static function () use ($fz, $stage): void {
		$fz->zip_folder($stage, false);
	});
	$fzc = $stage . '.fz';
	$fzcBytes = is_file($fzc) ? (int) filesize($fzc) : -1;
	$mid = (string) $fz->mid_fractal_zip_marker;

	$verifyOk = false;
	if ($fzcBytes > 0) {
		$fx = new fractal_zip(120, true, false, null, false);
		bench_metastruct_ob(static function () use ($fx, $fzc): void {
			$fx->open_container($fzc, false);
		});
		$verifyOk = bench_metastruct_trees_equal($stage, $td);
	}

	$meta = fractal_zip::$last_metastruct_descriptor;
	$pathFlags = array(
		'unified_stream' => fractal_zip::$used_folder_unified_stream,
		'gzip_fast' => fractal_zip::$used_folder_gzip_fast,
	);
	@unlink($fzc);
	bench_metastruct_rmtree($stage);
	bench_metastruct_rmtree($td);

	return array(
		'label' => $mode,
		'fzc_bytes' => $fzcBytes,
		'verify_ok' => $verifyOk,
		'mid' => $mid,
		'metastruct' => is_array($meta) ? $meta : null,
		'path' => $pathFlags,
	);
}

function bench_metastruct_copytree(string $src, string $dst): void {
	$srcR = realpath($src);
	if ($srcR === false) {
		throw new RuntimeException('missing corpus: ' . $src);
	}
	@mkdir($dst, 0755, true);
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($srcR, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	$srcN = rtrim(str_replace('\\', '/', $srcR), '/') . '/';
	$prefixLen = strlen($srcN);
	foreach ($it as $fi) {
		$pathN = str_replace('\\', '/', $fi->getPathname());
		$rel = substr($pathN, $prefixLen);
		$target = $dst . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if ($fi->isDir()) {
			@mkdir($target, 0755, true);
		} else {
			@mkdir(dirname($target), 0755, true);
			@copy($fi->getPathname(), $target);
		}
	}
}

function bench_metastruct_rmtree(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $fi) {
		if ($fi->isDir()) {
			@rmdir($fi->getPathname());
		} else {
			@unlink($fi->getPathname());
		}
	}
	@rmdir($dir);
}

/** Nested member .fz files are inlined on folder extract (same as run_benchmarks collectFolderHashes). */
function bench_metastruct_verify_skip_fractal_container_member(string $rel): bool {
	return str_ends_with(strtolower(str_replace('\\', '/', $rel)), '.fz');
}

function bench_metastruct_trees_equal(string $src, string $extractRoot): bool {
	$rootA = realpath($src);
	$rootB = realpath($extractRoot);
	if ($rootA === false || $rootB === false) {
		return false;
	}
	$filesA = bench_metastruct_collect_files($rootA);
	$filesB = bench_metastruct_collect_files($rootB);
	// open_container writes members beside .fz under extractRoot; ignore staging dir + .fz artifact.
	$stageBase = basename($rootA);
	foreach ($filesB as $rel => $_p) {
		if ($rel === $stageBase . '.fz' || str_starts_with($rel, $stageBase . '/')) {
			unset($filesB[$rel]);
		}
	}
	foreach ($filesA as $rel => $_p) {
		if (bench_metastruct_verify_skip_fractal_container_member($rel)) {
			unset($filesA[$rel]);
		}
	}
	foreach ($filesB as $rel => $_p) {
		if (bench_metastruct_verify_skip_fractal_container_member($rel)) {
			unset($filesB[$rel]);
		}
	}
	if (array_keys($filesA) !== array_keys($filesB)) {
		return false;
	}
	foreach ($filesA as $rel => $pathA) {
		$pathB = $filesB[$rel];
		if (@filesize($pathA) !== @filesize($pathB)) {
			return false;
		}
		if (@hash_file('sha256', $pathA) !== @hash_file('sha256', $pathB)) {
			return false;
		}
	}
	return true;
}

/** @return array<string,string> rel => abs */
function bench_metastruct_collect_files(string $root): array {
	$rootN = rtrim(str_replace('\\', '/', $root), '/') . '/';
	$prefixLen = strlen($rootN);
	$out = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$pathN = str_replace('\\', '/', $fi->getPathname());
		if (strlen($pathN) < $prefixLen || substr($pathN, 0, $prefixLen) !== $rootN) {
			continue;
		}
		$rel = substr($pathN, $prefixLen);
		$out[$rel] = $fi->getPathname();
	}
	ksort($out, SORT_STRING);
	return $out;
}

if (PHP_SAPI === 'cli' && realpath((string) ($argv[0] ?? '')) === realpath(__FILE__)) {
	$corpora = array();
	$ultra = false;
	$legacyFractal = false;
	foreach ($argv as $i => $arg) {
		if ($i === 0) {
			continue;
		}
		if ($arg === '--ultra') {
			$ultra = true;
			continue;
		}
		if ($arg === '--legacy-fractal') {
			$legacyFractal = true;
			continue;
		}
		if (str_starts_with($arg, '--corpus=')) {
			foreach (explode(',', substr($arg, 9)) as $c) {
				$c = trim($c);
				if ($c !== '') {
					$corpora[] = $c;
				}
			}
		}
	}
	if ($corpora === array()) {
		$corpora = array('test_files53');
	}

	if ($ultra) {
		bench_metastruct_apply_ultra_env_defaults();
	}
	if ($legacyFractal) {
		fractal_zip_metastruct_force_legacy_fractal_env();
	} else {
		putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
		putenv('FRACTAL_ZIP_WEB_REF=0');
		putenv('LIVE_BROWSER_WEB_REF_OFFLINE=1');
	}

	echo "bench_metastruct_adapt ultra=" . ($ultra ? '1' : '0')
		. " legacy_fractal=" . ($legacyFractal ? '1' : '0') . "\n\n";

	foreach ($corpora as $corpus) {
		$corpusPath = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
		if (!is_dir($corpusPath)) {
			fwrite(STDERR, "skip missing corpus: {$corpus}\n");
			continue;
		}
		echo "=== {$corpus} ===\n";
		$rows = array();
		foreach (array('default', 'adaptive', 'metastruct') as $mode) {
			echo "  encoding {$mode}...\n";
			$rows[$mode] = bench_metastruct_run_mode($corpusPath, $mode);
		}
		$base = $rows['default']['fzc_bytes'];
		foreach ($rows as $mode => $row) {
			$delta = ($base > 0 && $row['fzc_bytes'] > 0) ? ($row['fzc_bytes'] - $base) : null;
			$deltaS = ($delta === null) ? 'n/a' : (($delta >= 0 ? '+' : '') . $delta);
			echo sprintf(
				"  %-10s fzc=%8d B  Δ=%8s  verify=%s  mid=\"%s\"  path=%s\n",
				$mode,
				$row['fzc_bytes'],
				$deltaS,
				$row['verify_ok'] ? 'OK' : 'FAIL',
				$row['mid'],
				json_encode($row['path'] ?? array(), JSON_UNESCAPED_SLASHES)
			);
			if ($mode === 'metastruct' && is_array($row['metastruct'])) {
				$m = $row['metastruct'];
				echo '    FZMS dominant=' . ($m['dominant_content_profile'] ?? '?')
					. ' / delim=' . ($m['dominant_delimiter_profile'] ?? '?')
					. ' applied=' . (!empty($m['applied']) ? 'yes' : 'no');
				if (isset($m['probe']['delta'])) {
					echo ' fractal_leg_Δ=' . $m['probe']['delta'];
				}
				echo "\n";
			}
		}
		echo "\n";
	}
}
