#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Whole-corpus convert preprocess × fractal_zip.
 *
 * Each case copies the FULL corpus tree; only files matching `glob` are forward-converted,
 * everything else is byte-identical. Compare baseline .fz vs converted .fz for the
 * entire folder. Verify: extract converted .fz → reverse-convert changed files → match originals.
 *
 *   php benchmarks/run_convert_whole_corpus_bench.php
 *   php benchmarks/run_convert_whole_corpus_bench.php --json
 *   php benchmarks/run_convert_whole_corpus_bench.php --ultra
 *   php benchmarks/run_convert_whole_corpus_bench.php --ultra --only=test_files53,test_files34
 */
$repo = dirname(__DIR__);
$convertRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'convert';
$jsonOut = in_array('--json', $argv ?? [], true);
$onlyCorpora = [];
foreach ($argv ?? [] as $arg) {
	if (str_starts_with($arg, '--only=')) {
		foreach (explode(',', substr($arg, 7)) as $c) {
			$c = trim($c);
			if ($c !== '') {
				$onlyCorpora[] = $c;
			}
		}
	}
}

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
@ini_set('memory_limit', '2G');

$useUltra = in_array('--ultra', $argv ?? [], true);
if ($useUltra) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';
	bench_ultra_apply_env_defaults();
	fwrite(STDERR, "[bench] --ultra: FRACTAL_ZIP_ULTRA=1 (table-aligned bytes-first preset)\n");
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'RoundTripLossiness.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'RoundTripCompare.php';

/** Published table fzc B (59-case bench). */
$tableFzc = [
	'test_files10' => 147,
	'test_files11' => 158,
	'test_files34' => 921,
	'test_files64' => 1255,
	'test_files53' => 74167,
	'test_files74' => 1307,
	'test_files149' => 1064,
	'test_files61' => null,
];

/**
 * Whole-corpus cases: `glob` selects members to convert; rest of tree unchanged.
 *
 * @return list<array{id: string, corpus: string, action: string, reverse: string, glob: string, forward_ext: string, compare_ext: string, note: string}>
 */
function whole_corpus_cases(): array
{
	return [
		['id' => '53_csv_json', 'corpus' => 'test_files53', 'action' => 'csv_to_json', 'reverse' => 'json_to_csv', 'glob' => '*.csv', 'forward_ext' => 'json', 'compare_ext' => 'csv', 'note' => 'whole corpus (1 csv + unchanged)'],
		['id' => '10_bmp_png', 'corpus' => 'test_files10', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'whole corpus BMPs'],
		['id' => '11_bmp_png', 'corpus' => 'test_files11', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'whole corpus BMPs'],
		['id' => '34_bmp_png', 'corpus' => 'test_files34', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'whole corpus = 1 bmp'],
		['id' => '64_bmp_png', 'corpus' => 'test_files64', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'whole corpus = 1 bmp'],
		['id' => '61_png_bmp', 'corpus' => 'test_files61', 'action' => 'png_to_bmp', 'reverse' => 'bmp_to_png', 'glob' => '*.png', 'forward_ext' => 'bmp', 'compare_ext' => 'png', 'note' => 'whole corpus: all PNGs→BMP, rest unchanged'],
		['id' => '61_bmp_png', 'corpus' => 'test_files61', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'whole corpus: all BMPs→PNG, rest unchanged'],
		['id' => '74_csv_json', 'corpus' => 'test_files74', 'action' => 'csv_to_json', 'reverse' => 'json_to_csv', 'glob' => '*.csv', 'forward_ext' => 'json', 'compare_ext' => 'csv', 'note' => 'whole desktop mix'],
		['id' => '74_svg_bmp', 'corpus' => 'test_files74', 'action' => 'svg_to_bmp', 'reverse' => 'bmp_to_png', 'glob' => '*.svg', 'forward_ext' => 'bmp', 'compare_ext' => 'svg', 'note' => 'whole desktop mix; svg round-trip via bmp→png raster'],
		['id' => '149_zip_7z', 'corpus' => 'test_files149', 'action' => 'zip_to_7z', 'reverse' => '7z_to_zip', 'glob' => '*.zip', 'forward_ext' => '7z', 'compare_ext' => 'zip', 'note' => 'whole corpus: zip member only'],
	];
}

function wc_rmtree(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
	}
	@rmdir($dir);
}

function wc_swap_ext(string $rel, string $newExt): string
{
	return preg_replace('/\.[^.]+$/', '.' . $newExt, $rel) ?? ($rel . '.' . $newExt);
}

function wc_matches_glob(string $basename, string $glob): bool
{
	return fnmatch($glob, $basename, FNM_CASEFOLD);
}

/** @return array{fzc: int, sec: float, fzc_path: string, files: int, bytes: int, error?: string} */
function wc_encode_whole_corpus(string $dir): array
{
	$fzc = $dir . '.fz';
	@unlink($fzc);
	$files = 0;
	$bytes = 0;
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $item) {
		if ($item->isFile()) {
			$files++;
			$bytes += $item->getSize();
		}
	}
	try {
		$fz = new fractal_zip();
		$t0 = microtime(true);
		$fz->zip_folder($dir, false);
		$sec = microtime(true) - $t0;
	} catch (Throwable $e) {
		return ['fzc' => 0, 'sec' => 0.0, 'fzc_path' => $fzc, 'files' => $files, 'bytes' => $bytes, 'error' => $e->getMessage()];
	}
	return [
		'fzc' => is_file($fzc) ? (int) filesize($fzc) : 0,
		'sec' => $sec,
		'fzc_path' => $fzc,
		'files' => $files,
		'bytes' => $bytes,
	];
}

/** @return array{converted: int, unchanged: int, errors: list<string>} */
function wc_build_whole_corpus_converted(string $srcDir, string $dstDir, string $action, string $glob, string $forwardExt): array
{
	wc_rmtree($dstDir);
	mkdir($dstDir, 0755, true);
	$converted = 0;
	$unchanged = 0;
	$errors = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$rel = substr($item->getPathname(), strlen($srcDir) + 1);
		$dstPath = $dstDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if ($item->isDir()) {
			if (!is_dir($dstPath)) {
				mkdir($dstPath, 0755, true);
			}
			continue;
		}
		$base = basename($rel);
		if (!wc_matches_glob($base, $glob)) {
			$parent = dirname($dstPath);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $dstPath);
			$unchanged++;
			continue;
		}
		try {
			$bytes = (string) file_get_contents($item->getPathname());
			$r = ActionRegistry::convert($action, $base, $bytes);
			$ext = $r->extension ?: $forwardExt;
			$newRel = wc_swap_ext($rel, $ext);
			$outPath = $dstDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $newRel);
			$parent = dirname($outPath);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			file_put_contents($outPath, $r->bytes);
			$converted++;
		} catch (Throwable $e) {
			$parent = dirname($dstPath);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $dstPath);
			$errors[] = $rel . ': ' . $e->getMessage();
			$unchanged++;
		}
	}
	return ['converted' => $converted, 'unchanged' => $unchanged, 'errors' => $errors];
}

/**
 * @return array{ok: bool, strict_ok: bool, files: int, mismatches: list<string>, strict_mismatches: list<string>}
 */
function wc_verify_whole_corpus_roundtrip(
	string $fzcPath,
	string $srcDir,
	string $reverseAction,
	string $glob,
	string $forwardExt,
	string $compareExt
): array {
	$extractRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_wc_' . bin2hex(random_bytes(4));
	mkdir($extractRoot, 0755, true);
	copy($fzcPath, $extractRoot . DIRECTORY_SEPARATOR . 'probe.fz');
	$cwd = getcwd();
	chdir($extractRoot);
	try {
		(new fractal_zip())->open_container($extractRoot . DIRECTORY_SEPARATOR . 'probe.fz', false);
	} finally {
		if ($cwd !== false) {
			chdir($cwd);
		}
	}

	$mismatches = [];
	$strictMismatches = [];
	$checked = 0;
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $item) {
		if (!$item->isFile() || !wc_matches_glob(basename($item->getPathname()), $glob)) {
			continue;
		}
		$rel = substr($item->getPathname(), strlen($srcDir) + 1);
		$orig = (string) file_get_contents($item->getPathname());
		$extractPath = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, wc_swap_ext($rel, $forwardExt));
		if (!is_file($extractPath)) {
			$mismatches[] = $rel . ': missing after extract';
			continue;
		}
		try {
			$mid = (string) file_get_contents($extractPath);
			$restored = ActionRegistry::convert($reverseAction, basename($extractPath), $mid);
			if ($orig !== $restored->bytes) {
				$strictMismatches[] = $rel . ': strict bytes differ (' . strlen($orig) . ' vs ' . strlen($restored->bytes) . ')';
			}
			$cmp = RoundTripCompare::compare($orig, $restored->bytes, $compareExt);
			if (!$cmp['match']) {
				$mismatches[] = $rel . ': ' . $cmp['method'] . ' — ' . $cmp['detail'];
			}
			$checked++;
		} catch (Throwable $e) {
			$mismatches[] = $rel . ': ' . $e->getMessage();
		}
	}
	wc_rmtree($extractRoot);
	return [
		'ok' => $mismatches === [] && $checked > 0,
		'strict_ok' => $strictMismatches === [] && $checked > 0,
		'files' => $checked,
		'mismatches' => $mismatches,
		'strict_mismatches' => $strictMismatches,
	];
}

$workBase = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.work_whole_corpus';
wc_rmtree($workBase);
mkdir($workBase, 0755, true);

/** @var list<array<string, mixed>> $rows */
$rows = [];

foreach (whole_corpus_cases() as $case) {
	$corpus = $case['corpus'];
	if ($onlyCorpora !== [] && !in_array($corpus, $onlyCorpora, true)) {
		continue;
	}
	$srcDir = $repo . DIRECTORY_SEPARATOR . $corpus;
	if (!is_dir($srcDir)) {
		continue;
	}
	$convDir = $workBase . DIRECTORY_SEPARATOR . $case['id'];

	$base = wc_encode_whole_corpus($srcDir);
	$build = wc_build_whole_corpus_converted($srcDir, $convDir, $case['action'], $case['glob'], $case['forward_ext']);
	$conv = ['fzc' => 0, 'sec' => 0.0, 'fzc_path' => '', 'files' => 0, 'bytes' => 0];
	if ($build['converted'] > 0) {
		$conv = wc_encode_whole_corpus($convDir);
	}

	$verify = ['ok' => false, 'strict_ok' => false, 'files' => 0, 'mismatches' => ['nothing converted'], 'strict_mismatches' => []];
	if ($build['converted'] > 0 && $conv['fzc'] > 0) {
		$verify = wc_verify_whole_corpus_roundtrip(
			$conv['fzc_path'],
			$srcDir,
			$case['reverse'],
			$case['glob'],
			$case['forward_ext'],
			$case['compare_ext']
		);
	}

	$tableRef = $tableFzc[$corpus] ?? null;
	$delta = $base['fzc'] > 0 && $conv['fzc'] > 0 ? $base['fzc'] - $conv['fzc'] : null;
	$win = $delta !== null && $delta > 0 && $verify['ok'];
	$strictWin = $delta !== null && $delta > 0 && $verify['strict_ok'];
	$tableWin = $strictWin && $tableRef !== null && $conv['fzc'] > 0 && $conv['fzc'] <= $tableRef;

	$rows[] = [
		'id' => $case['id'],
		'corpus' => $corpus,
		'note' => $case['note'],
		'forward' => $case['action'],
		'reverse' => $case['reverse'],
		'glob' => $case['glob'],
		'corpus_files' => $base['files'],
		'corpus_raw_bytes' => $base['bytes'],
		'files_converted' => $build['converted'],
		'files_unchanged' => $build['unchanged'],
		'convert_errors' => $build['errors'],
		'baseline_whole_fzc' => $base['fzc'],
		'converted_whole_fzc' => $conv['fzc'],
		'whole_fzc_delta' => $delta,
		'table_fzc_ref' => $tableRef,
		'table_fzc_delta' => $tableRef !== null && $conv['fzc'] > 0 ? $tableRef - $conv['fzc'] : null,
		'baseline_sec' => round($base['sec'], 2),
		'converted_sec' => round($conv['sec'], 2),
		'encode_error' => $base['error'] ?? $conv['error'] ?? null,
		'roundtrip_ok' => $verify['ok'],
		'strict_roundtrip_ok' => $verify['strict_ok'],
		'roundtrip_detail' => $verify['mismatches'],
		'strict_roundtrip_detail' => $verify['strict_mismatches'],
		'win' => $win,
		'strict_win' => $strictWin,
		'table_strict_win' => $tableWin,
	];
}

$wins = array_values(array_filter($rows, static fn (array $r): bool => !empty($r['win'])));
$strictWins = array_values(array_filter($rows, static fn (array $r): bool => !empty($r['strict_win'])));
$tableWins = array_values(array_filter($rows, static fn (array $r): bool => !empty($r['table_strict_win'])));

$report = [
	'generated_at' => gmdate('c'),
	'ultra' => $useUltra,
	'metric' => 'whole_corpus_fzc_bytes',
	'description' => 'Full corpus tree encoded; only glob-matched members preprocessed. strict_win = smaller whole .fz + extract→reverse with === bytes on converted members.',
	'cases' => $rows,
	'wins' => $wins,
	'strict_wins' => $strictWins,
	'table_strict_wins' => $tableWins,
];
$jsonPath = __DIR__ . DIRECTORY_SEPARATOR . '.convert_whole_corpus_bench.json';
file_put_contents($jsonPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$lines = [
	'# Whole-corpus convert × fractal_zip',
	'',
	'**Metric:** `.fz` size of the **entire** corpus folder (all files), after in-place preprocess on matching members only.',
	'**Win:** whole `.fz` smaller than baseline **and** extract → reverse-convert restores converted members.',
	'',
];
if ($wins !== []) {
	$lines[] = '## Verified whole-corpus bytes wins';
	$lines[] = '';
	$lines[] = '| Corpus | Preprocess | Conv files | Base whole fzc | Conv whole fzc | Δ | Table ref | RT |';
	$lines[] = '|--------|------------|----------:|---------------:|---------------:|--:|----------:|:--:|';
	foreach ($wins as $r) {
		$lines[] = sprintf(
			'| %s | %s | %d | %s | %s | +%s | %s | OK |',
			$r['corpus'],
			$r['forward'],
			$r['files_converted'],
			number_format($r['baseline_whole_fzc']),
			number_format($r['converted_whole_fzc']),
			number_format((int) $r['whole_fzc_delta']),
			$r['table_fzc_ref'] !== null ? number_format((int) $r['table_fzc_ref']) : '—'
		);
	}
} else {
	$lines[] = '## No verified whole-corpus bytes wins in this pass.';
}
$lines[] = '';
$lines[] = '## All cases';
$lines[] = '';
$lines[] = '| Corpus | Preprocess | conv/ total | Base fzc | Conv fzc | Δ | Table | sem RT | strict RT |';
$lines[] = '|--------|------------|------------:|---------:|---------:|--------:|------:|:-----:|:---------:|';
foreach ($rows as $r) {
	$d = $r['whole_fzc_delta'];
	$lines[] = sprintf(
		'| %s | %s | %d / %d | %s | %s | %s | %s | %s | %s |',
		$r['corpus'],
		$r['forward'],
		$r['files_converted'],
		$r['corpus_files'],
		number_format($r['baseline_whole_fzc']),
		$r['converted_whole_fzc'] ? number_format($r['converted_whole_fzc']) : '—',
		$d === null ? '—' : ($d >= 0 ? '+' . $d : (string) $d),
		$r['table_fzc_ref'] ?? '—',
		$r['roundtrip_ok'] ? 'OK' : 'FAIL',
		$r['strict_roundtrip_ok'] ? 'OK' : 'FAIL'
	);
}
if ($useUltra) {
	$lines[] = '';
	$lines[] = 'Encode preset: **ultra** (`FRACTAL_ZIP_ULTRA=1`).';
}
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'CONVERT_WHOLE_CORPUS_BENCH.md', implode("\n", $lines));

if ($jsonOut) {
	echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

echo "Whole-corpus convert × fractal_zip" . ($useUltra ? ' (ultra)' : '') . "\n\n";
printf("%-16s %-14s %5s %8s %8s %8s %7s %7s\n", 'corpus', 'preprocess', 'conv', 'base fzc', 'conv fzc', 'Δ', 'sem RT', 'strict');
foreach ($rows as $r) {
	$d = $r['whole_fzc_delta'];
	printf(
		"%-16s %-14s %5d %8s %8s %8s %7s %7s\n",
		$r['corpus'],
		$r['forward'],
		$r['files_converted'],
		number_format($r['baseline_whole_fzc']),
		$r['converted_whole_fzc'] ? number_format($r['converted_whole_fzc']) : '—',
		$d === null ? '—' : ($d >= 0 ? '+' . $d : (string) $d),
		$r['roundtrip_ok'] ? 'OK' : 'FAIL',
		$r['strict_roundtrip_ok'] ? 'OK' : 'FAIL'
	);
}
echo "\nSemantic wins: " . count($wins) . " | strict bytes wins: " . count($strictWins) . " | table strict wins: " . count($tableWins) . "\n";
