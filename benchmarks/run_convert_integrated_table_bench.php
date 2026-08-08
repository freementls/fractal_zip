#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Integrated convert preprocess × fractal_zip — same corpus, verified round-trip.
 *
 * Pipeline: originals → forward convert (copy tree) → zip_folder → .fz
 *           → open_container extract → reverse convert → compare to originals.
 * KPI: converted .fz vs baseline .fz and vs published table reference.
 *
 *   php benchmarks/run_convert_integrated_table_bench.php
 *   php benchmarks/run_convert_integrated_table_bench.php --json
 */
$repo = dirname(__DIR__);
$convertRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'convert';
$jsonOut = in_array('--json', $argv ?? [], true);

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
@ini_set('memory_limit', '2G');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'RoundTripLossiness.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'RoundTripCompare.php';

/** Reference fzc B from standard 59-case bench table. */
$tableFzc = [
	'test_files10' => 147,
	'test_files11' => 158,
	'test_files34' => 921,
	'test_files64' => 1255,
	'test_files53' => 74167,
	'test_files149' => 1064,
	'test_files61_png' => null,
];

/**
 * @return list<array{
 *   id: string,
 *   corpus: string,
 *   rel_path?: string,
 *   action: string,
 *   reverse: string,
 *   glob: string,
 *   forward_ext: string,
 *   compare_ext: string,
 *   note: string
 * }>
 */
function integrated_cases(): array
{
	return [
		// BMP table corpora — bmp_to_png hurts on 34/64; included for completeness
		['id' => 'bmp10', 'corpus' => 'test_files10', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'BMP pair stress'],
		['id' => 'bmp34', 'corpus' => 'test_files34', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'single sf.bmp'],
		['id' => 'bmp64', 'corpus' => 'test_files64', 'action' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'glob' => '*.bmp', 'forward_ext' => 'png', 'compare_ext' => 'bmp', 'note' => 'single grid bmp'],
		// PNG grids (test_files61 subset — not in table; documents png→bmp direction on same files)
		['id' => 'png61', 'corpus' => 'test_files61', 'rel_path' => '00_source_png', 'action' => 'png_to_bmp', 'reverse' => 'bmp_to_png', 'glob' => '*.png', 'forward_ext' => 'bmp', 'compare_ext' => 'png', 'note' => 'test_files61 PNG grids'],
		// CSV table corpus
		['id' => 'csv53', 'corpus' => 'test_files53', 'action' => 'csv_to_json', 'reverse' => 'json_to_csv', 'glob' => '*.csv', 'forward_ext' => 'json', 'compare_ext' => 'csv', 'note' => 'products_export CSV'],
		// ZIP in table corpus
		['id' => 'zip149', 'corpus' => 'test_files149', 'action' => 'zip_to_7z', 'reverse' => '7z_to_zip', 'glob' => '*.zip', 'forward_ext' => '7z', 'compare_ext' => 'zip', 'note' => 'fake_member.zip only'],
	];
}

function ic_rmtree(string $dir): void
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

/** @return array{fzc: int, sec: float, fzc_path: string} */
function ic_encode_folder(string $dir): array
{
	$fzc = $dir . '.fz';
	@unlink($fzc);
	$fz = new fractal_zip();
	$t0 = microtime(true);
	$fz->zip_folder($dir, false);
	return [
		'fzc' => is_file($fzc) ? (int) filesize($fzc) : 0,
		'sec' => microtime(true) - $t0,
		'fzc_path' => $fzc,
	];
}

function ic_swap_ext(string $rel, string $newExt): string
{
	return preg_replace('/\.[^.]+$/', '.' . $newExt, $rel) ?? ($rel . '.' . $newExt);
}

/** @return array{converted: int, skipped: int, errors: list<string>} */
function ic_build_converted_tree(string $srcDir, string $dstDir, string $action, string $globPat, string $forwardExt): array
{
	ic_rmtree($dstDir);
	mkdir($dstDir, 0755, true);
	$converted = 0;
	$skipped = 0;
	$errors = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $item) {
		if (!$item->isFile()) {
			continue;
		}
		$rel = substr($item->getPathname(), strlen($srcDir) + 1);
		$base = basename($rel);
		$dstRelPath = str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if (!fnmatch($globPat, $base)) {
			$dstPath = $dstDir . DIRECTORY_SEPARATOR . $dstRelPath;
			$parent = dirname($dstPath);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $dstPath);
			continue;
		}
		try {
			$bytes = (string) file_get_contents($item->getPathname());
			$r = ActionRegistry::convert($action, $base, $bytes);
			$ext = $r->extension ?: $forwardExt;
			$newRel = ic_swap_ext($rel, $ext);
			$outPath = $dstDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $newRel);
			$parent = dirname($outPath);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			file_put_contents($outPath, $r->bytes);
			$converted++;
		} catch (Throwable $e) {
			$dstPath = $dstDir . DIRECTORY_SEPARATOR . $dstRelPath;
			$parent = dirname($dstPath);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $dstPath);
			$errors[] = $rel . ': ' . $e->getMessage();
			$skipped++;
		}
	}
	return ['converted' => $converted, 'skipped' => $skipped, 'errors' => $errors];
}

/**
 * @return array{ok: bool, files: int, mismatches: list<string>}
 */
function ic_verify_fzc_roundtrip(
	string $fzcPath,
	string $srcDir,
	string $reverseAction,
	string $srcGlob,
	string $forwardExt,
	string $compareExt
): array {
	$extractRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_ic_' . bin2hex(random_bytes(4));
	mkdir($extractRoot, 0755, true);
	$localFzc = $extractRoot . DIRECTORY_SEPARATOR . 'probe.fz';
	copy($fzcPath, $localFzc);

	$cwd = getcwd();
	chdir($extractRoot);
	try {
		$fz = new fractal_zip();
		$fz->open_container($localFzc, false);
	} finally {
		if ($cwd !== false) {
			chdir($cwd);
		}
	}

	$mismatches = [];
	$checked = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $item) {
		if (!$item->isFile()) {
			continue;
		}
		$rel = substr($item->getPathname(), strlen($srcDir) + 1);
		if (!fnmatch($srcGlob, basename($rel))) {
			continue;
		}
		$orig = (string) file_get_contents($item->getPathname());
		$extractRel = ic_swap_ext($rel, $forwardExt);
		$extractPath = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $extractRel);
		if (!is_file($extractPath)) {
			$mismatches[] = $rel . ': missing extracted ' . $extractRel;
			continue;
		}
		$mid = (string) file_get_contents($extractPath);
		try {
			$restored = ActionRegistry::convert($reverseAction, basename($extractPath), $mid);
			$cmp = RoundTripCompare::compare($orig, $restored->bytes, $compareExt);
			if (!$cmp['match']) {
				$mismatches[] = $rel . ': ' . $cmp['method'] . ' — ' . $cmp['detail'];
			}
			$checked++;
		} catch (Throwable $e) {
			$mismatches[] = $rel . ': reverse — ' . $e->getMessage();
		}
	}
	ic_rmtree($extractRoot);
	return ['ok' => $mismatches === [] && $checked > 0, 'files' => $checked, 'mismatches' => $mismatches];
}

$workBase = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.work_convert_integrated';
ic_rmtree($workBase);
mkdir($workBase, 0755, true);

/** @var list<array<string, mixed>> $rows */
$rows = [];

foreach (integrated_cases() as $case) {
	$corpus = $case['corpus'];
	$srcDir = $repo . DIRECTORY_SEPARATOR . $corpus;
	if (isset($case['rel_path'])) {
		$srcDir .= DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $case['rel_path']);
	}
	if (!is_dir($srcDir)) {
		continue;
	}
	$rowId = $case['id'];
	$tableKey = $case['rel_path'] ?? null ? ($corpus . '_' . explode('/', $case['rel_path'])[0]) : $corpus;
	if ($case['id'] === 'png61') {
		$tableKey = 'test_files61_png';
	}

	$convDir = $workBase . DIRECTORY_SEPARATOR . $rowId . '_converted';

	$baseEnc = ic_encode_folder($srcDir);
	$build = ic_build_converted_tree($srcDir, $convDir, $case['action'], $case['glob'], $case['forward_ext']);
	$convEnc = ['fzc' => 0, 'sec' => 0.0, 'fzc_path' => ''];
	if ($build['converted'] > 0) {
		$convEnc = ic_encode_folder($convDir);
	}

	$verify = ['ok' => false, 'files' => 0, 'mismatches' => ['no converted files']];
	if ($build['converted'] > 0 && $convEnc['fzc'] > 0 && is_file($convEnc['fzc_path'])) {
		$verify = ic_verify_fzc_roundtrip(
			$convEnc['fzc_path'],
			$srcDir,
			$case['reverse'],
			$case['glob'],
			$case['forward_ext'],
			$case['compare_ext']
		);
	}

	$tableRef = $tableFzc[$tableKey] ?? $tableFzc[$corpus] ?? null;
	$baseFzc = $baseEnc['fzc'];
	$convFzc = $convEnc['fzc'];
	$savedVsBase = $baseFzc > 0 && $convFzc > 0 ? $baseFzc - $convFzc : null;
	$savedVsTable = $tableRef !== null && $convFzc > 0 ? $tableRef - $convFzc : null;
	$speedWin = $convEnc['sec'] > 0 && $baseEnc['sec'] > 0 && $convEnc['sec'] < $baseEnc['sec'];

	$rows[] = [
		'id' => $rowId,
		'corpus' => $corpus,
		'src_path' => isset($case['rel_path']) ? $corpus . '/' . $case['rel_path'] : $corpus,
		'note' => $case['note'],
		'forward' => $case['action'],
		'reverse' => $case['reverse'],
		'files_converted' => $build['converted'],
		'convert_errors' => $build['errors'],
		'baseline_fzc' => $baseFzc,
		'baseline_sec' => round($baseEnc['sec'], 3),
		'converted_fzc' => $convFzc,
		'converted_sec' => round($convEnc['sec'], 3),
		'fzc_saved_vs_baseline' => $savedVsBase,
		'table_fzc_ref' => $tableRef,
		'fzc_saved_vs_table' => $savedVsTable,
		'roundtrip_ok' => $verify['ok'],
		'roundtrip_files' => $verify['files'],
		'roundtrip_mismatches' => $verify['mismatches'],
		'speed_win' => $speedWin,
		'win_bytes' => $savedVsBase !== null && $savedVsBase > 0 && $verify['ok'],
		'win' => $savedVsBase !== null && $savedVsBase > 0 && $verify['ok'],
	];
}

$wins = array_values(array_filter($rows, static fn (array $r): bool => !empty($r['win'])));
$report = [
	'generated_at' => gmdate('c'),
	'description' => 'Same corpus forward convert → .fz → extract → reverse convert; win = smaller .fz + verified round-trip.',
	'cases' => $rows,
	'wins' => $wins,
	'table_wins' => array_values(array_filter($wins, static fn (array $r): bool => $r['table_fzc_ref'] !== null)),
];
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . '.convert_integrated_table_bench.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$md = [
	'# Integrated convert × fractal_zip (table corpora)',
	'',
	'Generated: ' . $report['generated_at'],
	'',
	'**Win** = converted `.fz` < baseline `.fz` on the **same** corpus, and extract → reverse-convert matches originals.',
	'',
];
if ($wins !== []) {
	$md[] = '## Verified bytes wins';
	$md[] = '';
	$md[] = '| Corpus | Action | Table ref | Base fzc | Conv fzc | Δ | Round-trip |';
	$md[] = '|--------|--------|----------:|---------:|---------:|--:|:------------|';
	foreach ($wins as $r) {
		$md[] = sprintf(
			'| %s | %s | %s | %s | %s | +%s | OK |',
			$r['src_path'],
			$r['forward'],
			$r['table_fzc_ref'] !== null ? number_format((int) $r['table_fzc_ref']) : '—',
			number_format((int) $r['baseline_fzc']),
			number_format((int) $r['converted_fzc']),
			number_format((int) $r['fzc_saved_vs_baseline'])
		);
	}
} else {
	$md[] = '## No verified bytes wins on table corpora in this pass.';
}
$md[] = '';
$md[] = '## All cases';
$md[] = '';
$md[] = '| Corpus | Action | Table | Base fzc | Conv fzc | Δ | RT | sec base→conv |';
$md[] = '|--------|--------|------:|---------:|---------:|--:|:---:|--------------:|';
foreach ($rows as $r) {
	$d = $r['fzc_saved_vs_baseline'];
	$md[] = sprintf(
		'| %s | %s | %s | %s | %s | %s | %s | %.3f→%.3f |',
		$r['src_path'],
		$r['forward'],
		$r['table_fzc_ref'] ?? '—',
		number_format((int) $r['baseline_fzc']),
		$r['converted_fzc'] ? number_format((int) $r['converted_fzc']) : '—',
		$d === null ? '—' : ($d >= 0 ? '+' . $d : (string) $d),
		$r['roundtrip_ok'] ? 'OK' : 'FAIL',
		$r['baseline_sec'],
		$r['converted_sec']
	);
}
file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'CONVERT_INTEGRATED_TABLE_BENCH.md', implode("\n", $md));

if ($jsonOut) {
	echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

echo "Integrated convert × fractal_zip\n\n";
printf("%-28s %-14s %8s %8s %8s %8s %s\n", 'corpus', 'action', 'table', 'base', 'conv', 'Δ', 'RT');
foreach ($rows as $r) {
	$d = $r['fzc_saved_vs_baseline'];
	printf(
		"%-28s %-14s %8s %8s %8s %8s %s\n",
		$r['src_path'],
		$r['forward'],
		$r['table_fzc_ref'] ?? '—',
		$r['baseline_fzc'],
		$r['converted_fzc'] ?: '—',
		$d === null ? '—' : ($d >= 0 ? '+' . $d : (string) $d),
		$r['roundtrip_ok'] ? 'OK' : 'FAIL'
	);
}
echo "\nVerified bytes wins: " . count($wins) . " (table ref: " . count($report['table_wins']) . ")\n";
