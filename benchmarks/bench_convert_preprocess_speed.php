#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Bytes + speed: baseline zip_folder vs staging preprocess (png_to_bmp).
 * Restore must be byte-identical (verbatim sidecar, not convert reverse).
 *
 *   php benchmarks/bench_convert_preprocess_speed.php [--corpus=test_files177]
 *   php benchmarks/bench_convert_preprocess_speed.php --ultra
 */
$repo = dirname(__DIR__);
$corpus = 'test_files177';
foreach ($argv ?? [] as $arg) {
	if (str_starts_with($arg, '--corpus=')) {
		$corpus = substr($arg, 9);
	}
}

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');

require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_preprocess.php';

$srcDir = $repo . DIRECTORY_SEPARATOR . $corpus;
if (!is_dir($srcDir)) {
	fwrite(STDERR, "missing corpus {$srcDir}\n");
	exit(1);
}

/** @return array<string, string> rel => sha256 */
function corpus_sha256_map(string $dir): array
{
	$out = [];
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $item) {
		if (!$item->isFile()) {
			continue;
		}
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', substr($item->getPathname(), strlen($dir) + 1));
		$out[$rel] = hash_file('sha256', $item->getPathname());
	}
	ksort($out);
	return $out;
}

/** @return array{fzc: int, stage_sec: float, encode_sec: float, sidecar_bytes: int, byte_identical: bool, mismatches: list<string>, label: string} */
function run_case(string $label, callable $encode, callable $extractRestore, array $origSha): array
{
	$r = $encode();
	$extractRestore($r['fzc_path'], $r['extract_root']);
	$after = corpus_sha256_map($r['extract_root']);
	$mismatches = [];
	foreach ($origSha as $rel => $sha) {
		if (!isset($after[$rel])) {
			$mismatches[] = "$rel: missing after extract";
			continue;
		}
		if ($after[$rel] !== $sha) {
			$mismatches[] = "$rel: sha mismatch";
		}
	}
	return [
		'label' => $label,
		'fzc' => $r['fzc_bytes'],
		'stage_sec' => $r['stage_sec'],
		'encode_sec' => $r['encode_sec'],
		'sidecar_bytes' => $r['sidecar_bytes'],
		'byte_identical' => $mismatches === [],
		'mismatches' => $mismatches,
	];
}

$origSha = corpus_sha256_map($srcDir);
$work = sys_get_temp_dir() . '/fz_bench_sp_' . bin2hex(random_bytes(3));

$baseline = run_case('baseline', static function () use ($srcDir, $work): array {
	$extractRoot = $work . '_base_ex';
	fractal_zip_preprocess_rmtree($extractRoot);
	mkdir($extractRoot, 0755, true);
	$fzc = $srcDir . '.bench_baseline.fz';
	@unlink($fzc);
	$t0 = microtime(true);
	$fz = new fractal_zip();
	$fz->zip_folder($srcDir, false);
	$encodeSec = microtime(true) - $t0;
	$defaultFzc = $srcDir . '.fz';
	if (is_file($defaultFzc)) {
		rename($defaultFzc, $fzc);
	}
	if (!is_file($fzc)) {
		throw new RuntimeException('baseline encode failed');
	}
	return [
		'fzc_path' => $fzc,
		'fzc_bytes' => (int) filesize($fzc),
		'stage_sec' => 0.0,
		'encode_sec' => $encodeSec,
		'sidecar_bytes' => 0,
		'extract_root' => $extractRoot,
	];
}, static function (string $fzc, string $extractRoot): void {
	$copy = $extractRoot . DIRECTORY_SEPARATOR . 'p.fz';
	copy($fzc, $copy);
	$t0 = microtime(true);
	$cwd = getcwd();
	chdir($extractRoot);
	try {
		(new fractal_zip())->open_container($copy, false);
	} finally {
		if ($cwd !== false) {
			chdir($cwd);
		}
	}
	// baseline: extract writes members directly
}, $origSha);

$preprocess = run_case('preprocess_png_to_bmp', static function () use ($srcDir, $work): array {
	$tStage0 = microtime(true);
	$built = fractal_zip_preprocess_build_stage($srcDir, 'png_to_bmp');
	$stageSec = microtime(true) - $tStage0;
	$stage = $built['stage_dir'];
	$verb = $built['verbatim_dir'];
	$fzc = $srcDir . '.bench_preprocess.fz';
	@unlink($fzc);
	$t0 = microtime(true);
	(new fractal_zip())->zip_folder($stage, false);
	$encodeSec = microtime(true) - $t0;
	$stageFzc = $stage . '.fz';
	if (!is_file($stageFzc)) {
		fractal_zip_preprocess_rmtree($stage);
		fractal_zip_preprocess_rmtree($verb);
		throw new RuntimeException('preprocess encode failed');
	}
	rename($stageFzc, $fzc);
	fractal_zip_preprocess_publish_sidecar($fzc, $built['manifest'], $verb);
	fractal_zip_preprocess_rmtree($stage);
	$sidecar = (int) (@filesize(fractal_zip_preprocess_sidecar_path($fzc)) ?: 0);
	$verbDir = fractal_zip_preprocess_verbatim_dir($fzc);
	$verbBytes = 0;
	if (is_dir($verbDir)) {
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($verbDir, FilesystemIterator::SKIP_DOTS));
		foreach ($it as $f) {
			if ($f->isFile()) {
				$verbBytes += $f->getSize();
			}
		}
	}
	return [
		'fzc_path' => $fzc,
		'fzc_bytes' => (int) filesize($fzc),
		'stage_sec' => $stageSec,
		'encode_sec' => $encodeSec,
		'sidecar_bytes' => $sidecar + $verbBytes,
		'extract_root' => $work . '_pp_ex',
	];
}, static function (string $fzc, string $extractRoot): void {
	fractal_zip_preprocess_rmtree($extractRoot);
	mkdir($extractRoot, 0755, true);
	$copy = $extractRoot . DIRECTORY_SEPARATOR . 'p.fz';
	copy($fzc, $copy);
	$sidecar = fractal_zip_preprocess_sidecar_path($fzc);
	if (is_file($sidecar)) {
		copy($sidecar, fractal_zip_preprocess_sidecar_path($copy));
	}
	$verbSrc = fractal_zip_preprocess_verbatim_dir($fzc);
	$verbDst = fractal_zip_preprocess_verbatim_dir($copy);
	if (is_dir($verbSrc)) {
		fractal_zip_preprocess_rmtree($verbDst);
		fractal_zip_preprocess_copy_tree($verbSrc, $verbDst);
	}
	$cwd = getcwd();
	chdir($extractRoot);
	try {
		(new fractal_zip())->open_container($copy, false);
	} finally {
		if ($cwd !== false) {
			chdir($cwd);
		}
	}
	$rev = fractal_zip_preprocess_apply_reverse($copy, $extractRoot);
	if (!$rev['byte_identical']) {
		throw new RuntimeException('verbatim restore failed: ' . implode('; ', $rev['errors']));
	}
}, $origSha);

$totalBase = $baseline['fzc'];
$totalPp = $preprocess['fzc'] + $preprocess['sidecar_bytes'];

printf("Corpus: %s (%d files)\n\n", $corpus, count($origSha));
printf("%-22s %10s %10s %8s %8s %10s %s\n", 'case', 'fzc', 'sidecar', 'stage s', 'zip s', 'total pkg', 'bytes RT');
printf("%-22s %10s %10s %8s %8s %10s %s\n",
	$baseline['label'],
	number_format($baseline['fzc']),
	'—',
	'—',
	number_format($baseline['encode_sec'], 2),
	number_format($totalBase),
	$baseline['byte_identical'] ? 'OK' : 'FAIL'
);
printf("%-22s %10s %10s %8s %8s %10s %s\n",
	$preprocess['label'],
	number_format($preprocess['fzc']),
	number_format($preprocess['sidecar_bytes']),
	number_format($preprocess['stage_sec'], 2),
	number_format($preprocess['encode_sec'], 2),
	number_format($totalPp),
	$preprocess['byte_identical'] ? 'OK' : 'FAIL'
);

$fzcDelta = $baseline['fzc'] - $preprocess['fzc'];
$pkgDelta = $totalBase - $totalPp;
$zipSpeedDelta = $baseline['encode_sec'] - $preprocess['encode_sec'];
$wallSpeedDelta = $baseline['encode_sec'] - ($preprocess['stage_sec'] + $preprocess['encode_sec']);

echo "\n";
printf("fzc delta (preprocess smaller): %s bytes\n", $fzcDelta >= 0 ? '+' . number_format($fzcDelta) : number_format($fzcDelta));
printf("total package delta (fzc+sidecar): %s bytes\n", $pkgDelta >= 0 ? '+' . number_format($pkgDelta) : number_format($pkgDelta));
printf("zip_folder only: preprocess %s sec vs baseline %s sec (%s%.1f%%)\n",
	number_format($preprocess['encode_sec'], 2),
	number_format($baseline['encode_sec'], 2),
	$zipSpeedDelta >= 0 ? '+' : '−',
	$baseline['encode_sec'] > 0 ? abs(100.0 * $zipSpeedDelta / $baseline['encode_sec']) : 0.0
);
printf("wall clock (stage+zip): preprocess %s sec vs baseline %s sec (%s%.1f%%)\n",
	number_format($preprocess['stage_sec'] + $preprocess['encode_sec'], 2),
	number_format($baseline['encode_sec'], 2),
	$wallSpeedDelta >= 0 ? '+' : '−',
	$baseline['encode_sec'] > 0 ? abs(100.0 * $wallSpeedDelta / $baseline['encode_sec']) : 0.0
);
echo "\nNote: byte-identical restore uses verbatim sidecar (not bmp_to_png). Total package includes sidecar originals.\n";

@unlink($srcDir . '.bench_baseline.fz');
@unlink($srcDir . '.bench_preprocess.fz');
@unlink($srcDir . '.bench_preprocess.fz.preprocess.json');
fractal_zip_preprocess_rmtree($srcDir . '.bench_preprocess.fz.preprocess-verbatim');
fractal_zip_preprocess_rmtree($work . '_base_ex');
fractal_zip_preprocess_rmtree($work . '_pp_ex');

exit($baseline['byte_identical'] && $preprocess['byte_identical'] ? 0 : 1);
