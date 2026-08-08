#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Whole-corpus A/B via run_benchmarks.php (reliable encode + table-aligned env).
 *
 * Builds converted corpus copies under test_files17x, runs baseline vs converted through
 * run_benchmarks --no-best-ext, verifies extract→reverse on converted members.
 *
 *   php benchmarks/run_convert_whole_corpus_via_bench.php
 */
$repo = dirname(__DIR__);
$convertRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'convert';
$benchPhp = __DIR__ . DIRECTORY_SEPARATOR . 'run_benchmarks.php';
$runner = __DIR__ . DIRECTORY_SEPARATOR . 'run_benchmarks_low_priority.sh';

require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'RoundTripLossiness.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'RoundTripCompare.php';

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
@ini_set('memory_limit', '2G');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

/** @var array<string, int> table fzc B */
$tableFzc = [
	'test_files53' => 74167,
	'test_files34' => 921,
	'test_files64' => 1255,
	'test_files74' => 1307,
	'test_files149' => 1064,
];

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

function wc_copy_tree(string $src, string $dst): void
{
	wc_rmtree($dst);
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$rel = substr($item->getPathname(), strlen($src) + 1);
		$target = $dst . DIRECTORY_SEPARATOR . $rel;
		if ($item->isDir()) {
			if (!is_dir($target)) {
				mkdir($target, 0755, true);
			}
		} else {
			$parent = dirname($target);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $target);
		}
	}
}

function wc_swap_ext(string $rel, string $ext): string
{
	return preg_replace('/\.[^.]+$/', '.' . $ext, $rel) ?? ($rel . '.' . $ext);
}

/** @return array{converted: int, errors: list<string>} */
function wc_apply_convert(string $dir, string $action, string $glob, string $outExt): array
{
	$converted = 0;
	$errors = [];
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $item) {
		if (!$item->isFile() || !fnmatch($glob, basename($item->getPathname()), FNM_CASEFOLD)) {
			continue;
		}
		$path = $item->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		try {
			$r = ActionRegistry::convert($action, basename($path), (string) file_get_contents($path));
			$ext = $r->extension ?: $outExt;
			$newPath = dirname($path) . DIRECTORY_SEPARATOR . basename(wc_swap_ext($rel, $ext));
			file_put_contents($newPath, $r->bytes);
			if ($newPath !== $path) {
				unlink($path);
			}
			$converted++;
		} catch (Throwable $e) {
			$errors[] = $rel . ': ' . $e->getMessage();
		}
	}
	return ['converted' => $converted, 'errors' => $errors];
}

/** @return array<string, mixed>|null */
function wc_run_bench(string $repo, string $runner, string $label, int $timeout = 180): ?array
{
	$out = __DIR__ . DIRECTORY_SEPARATOR . '.wc_bench_' . $label . '.json';
	@unlink($out);
	$cmd = 'cd ' . escapeshellarg($repo) . ' && nice -n 19 ' . escapeshellarg($runner)
		. ' --only=' . escapeshellarg($label)
		. ' --no-verify --no-best-ext --no-multipass'
		. ' --case-timeout=' . $timeout
		. ' --out-json=' . escapeshellarg($out)
		. ' --no-save-last-json 2>/dev/null';
	exec($cmd, $lines, $code);
	if (!is_readable($out)) {
		return null;
	}
	$j = json_decode((string) file_get_contents($out), true);
	if (!is_array($j) || empty($j['cases'][0])) {
		return null;
	}
	return $j['cases'][0];
}

/** Encode corpus for extract→reverse verify (run_benchmarks deletes .work/.fz). */
function wc_encode_for_verify(string $dir): ?string
{
	$fzc = $dir . '.fz';
	@unlink($fzc);
	try {
		(new fractal_zip())->zip_folder($dir, false);
	} catch (Throwable $e) {
		return null;
	}
	return is_file($fzc) ? $fzc : null;
}

/**
 * @return list<array<string, mixed>>
 */
function wc_cases(string $repo): array
{
	return [
		[
			'id' => '53_xlsx_csv',
			'baseline_label' => 'test_files173',
			'build' => static function () use ($repo): void {
				$src = $repo . DIRECTORY_SEPARATOR . 'test_files53';
				$dst = $repo . DIRECTORY_SEPARATOR . 'test_files173';
				wc_copy_tree($src, $dst);
				$csv = $dst . DIRECTORY_SEPARATOR . 'products_export_1.csv';
				$b = (string) file_get_contents($csv);
				$x = ActionRegistry::convert('csv_to_xlsx', 'products_export_1.csv', $b);
				unlink($csv);
				file_put_contents($dst . DIRECTORY_SEPARATOR . 'products_export_1.xlsx', $x->bytes);
			},
			'converted_label' => 'test_files174',
			'build_converted' => static function () use ($repo): void {
				$src = $repo . DIRECTORY_SEPARATOR . 'test_files173';
				$dst = $repo . DIRECTORY_SEPARATOR . 'test_files174';
				wc_copy_tree($src, $dst);
				wc_apply_convert($dst, 'xlsx_to_csv', '*.xlsx', 'csv');
			},
			'forward' => 'xlsx_to_csv',
			'reverse' => 'csv_to_xlsx',
			'glob' => '*.xlsx',
			'verify_glob' => '*.csv',
			'forward_ext' => 'csv',
			'compare_ext' => 'xlsx',
			'table_ref_corpus' => 'test_files53',
			'note' => 'Whole corpus: xlsx (from csv53) → strip to csv via convert',
			'timeout_sec' => 180,
		],
		[
			'id' => '61_tarballs_zip7z',
			'baseline_label' => 'test_files175',
			'build' => static function () use ($repo): void {
				wc_copy_tree(
					$repo . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '03_tarballs',
					$repo . DIRECTORY_SEPARATOR . 'test_files175'
				);
			},
			'converted_label' => 'test_files176',
			'build_converted' => static function () use ($repo): void {
				wc_copy_tree($repo . DIRECTORY_SEPARATOR . 'test_files175', $repo . DIRECTORY_SEPARATOR . 'test_files176');
				wc_apply_convert($repo . DIRECTORY_SEPARATOR . 'test_files176', 'zip_to_7z', '*.zip', '7z');
			},
			'forward' => 'zip_to_7z',
			'reverse' => '7z_to_zip',
			'glob' => '*.zip',
			'verify_glob' => '*.zip',
			'forward_ext' => '7z',
			'compare_ext' => 'zip',
			'table_ref_corpus' => null,
			'note' => 'Whole tarball folder; zip→7z on zip member only',
			'timeout_sec' => 300,
		],
		[
			'id' => '61_png_bmp_whole',
			'baseline_label' => 'test_files177',
			'build' => static function () use ($repo): void {
				wc_copy_tree(
					$repo . DIRECTORY_SEPARATOR . 'test_files61' . DIRECTORY_SEPARATOR . '00_source_png',
					$repo . DIRECTORY_SEPARATOR . 'test_files177'
				);
			},
			'converted_label' => 'test_files178',
			'build_converted' => static function () use ($repo): void {
				wc_copy_tree($repo . DIRECTORY_SEPARATOR . 'test_files177', $repo . DIRECTORY_SEPARATOR . 'test_files178');
				wc_apply_convert($repo . DIRECTORY_SEPARATOR . 'test_files178', 'png_to_bmp', '*.png', 'bmp');
			},
			'forward' => 'png_to_bmp',
			'reverse' => 'bmp_to_png',
			'glob' => '*.png',
			'verify_glob' => '*.png',
			'forward_ext' => 'bmp',
			'compare_ext' => 'png',
			'table_ref_corpus' => null,
			'note' => 'Whole PNG sub-corpus (7 files, all converted) — test_files61/00_source_png',
			'timeout_sec' => 120,
		],
	];
}

/** @return array{ok: bool, mismatches: list<string>} */
function wc_verify(string $fzcPath, string $origDir, string $reverse, string $origGlob, string $forwardExt, string $compareExt): array
{
	$extractRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wc_v_' . bin2hex(random_bytes(3));
	mkdir($extractRoot, 0755, true);
	copy($fzcPath, $extractRoot . DIRECTORY_SEPARATOR . 'p.fz');
	$cwd = getcwd();
	chdir($extractRoot);
	try {
		(new fractal_zip())->open_container($extractRoot . DIRECTORY_SEPARATOR . 'p.fz', false);
	} finally {
		if ($cwd !== false) {
			chdir($cwd);
		}
	}
	$mismatches = [];
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($origDir, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $item) {
		if (!$item->isFile() || !fnmatch($origGlob, basename($item->getPathname()), FNM_CASEFOLD)) {
			continue;
		}
		$rel = substr($item->getPathname(), strlen($origDir) + 1);
		$orig = (string) file_get_contents($item->getPathname());
		$ex = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, wc_swap_ext($rel, $forwardExt));
		if (!is_file($ex)) {
			$mismatches[] = "$rel: missing extracted";
			continue;
		}
		try {
			$rest = ActionRegistry::convert($reverse, basename($ex), (string) file_get_contents($ex));
			$cmp = RoundTripCompare::compare($orig, $rest->bytes, $compareExt);
			if (!$cmp['match']) {
				$mismatches[] = "$rel: {$cmp['detail']}";
			}
		} catch (Throwable $e) {
			$mismatches[] = "$rel: {$e->getMessage()}";
		}
	}
	wc_rmtree($extractRoot);
	return ['ok' => $mismatches === [], 'mismatches' => $mismatches];
}

/** @var list<array<string, mixed>> $rows */
$rows = [];
foreach (wc_cases($repo) as $case) {
	($case['build'])();
	$baseLabel = $case['baseline_label'];
	($case['build_converted'])();
	$convLabel = $case['converted_label'];

	$timeout = (int) ($case['timeout_sec'] ?? 180);
	$baseRow = wc_run_bench($repo, $runner, $baseLabel, $timeout);
	$convRow = wc_run_bench($repo, $runner, $convLabel, $timeout);

	$baseFzc = $baseRow ? (int) ($baseRow['fzc_bytes'] ?? 0) : 0;
	$convFzc = $convRow ? (int) ($convRow['fzc_bytes'] ?? 0) : 0;
	$delta = $baseFzc > 0 && $convFzc > 0 ? $baseFzc - $convFzc : null;

	$verify = ['ok' => false, 'mismatches' => ['bench encode failed or skipped']];
	if ($convFzc > 0) {
		$convDir = $repo . DIRECTORY_SEPARATOR . $convLabel;
		$origDir = $repo . DIRECTORY_SEPARATOR . $baseLabel;
		$fzcPath = wc_encode_for_verify($convDir);
		if ($fzcPath !== null) {
			$verify = wc_verify(
				$fzcPath,
				$origDir,
				$case['reverse'],
				$case['glob'],
				$case['forward_ext'],
				$case['compare_ext']
			);
			@unlink($fzcPath);
		} else {
			$verify = ['ok' => false, 'mismatches' => ['verify encode failed']];
		}
	}

	$tableKey = $case['table_ref_corpus'] ?? null;
	$tableRef = $tableKey ? ($tableFzc[$tableKey] ?? null) : null;

	$rows[] = [
		'id' => $case['id'],
		'note' => $case['note'],
		'baseline_corpus' => $baseLabel,
		'converted_corpus' => $convLabel,
		'forward' => $case['forward'],
		'baseline_whole_fzc' => $baseFzc,
		'converted_whole_fzc' => $convFzc,
		'whole_fzc_delta' => $delta,
		'table_fzc_ref' => $tableRef,
		'vs_table_delta' => $tableRef && $convFzc > 0 ? $tableRef - $convFzc : null,
		'roundtrip_ok' => $verify['ok'],
		'roundtrip_mismatches' => $verify['mismatches'],
		'win' => $delta !== null && $delta > 0 && $verify['ok'],
		'table_win' => $tableRef !== null && $convFzc > 0 && $convFzc < $tableRef && $verify['ok'],
	];
}

$wins = array_values(array_filter($rows, static fn ($r) => !empty($r['win'])));
$tableWins = array_values(array_filter($rows, static fn ($r) => !empty($r['table_win'])));
$report = [
	'generated_at' => gmdate('c'),
	'metric' => 'whole_corpus_fzc_via_run_benchmarks',
	'cases' => $rows,
	'wins' => $wins,
	'table_wins' => $tableWins,
];
file_put_contents(__DIR__ . '/.convert_whole_corpus_via_bench.json', json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$md = [
	'# Whole-corpus convert via run_benchmarks',
	'',
	'**Metric:** whole-folder `.fz` from `run_benchmarks --no-best-ext`; round-trip verified by re-encode + extract→reverse.',
	'**Win:** smaller converted whole `.fz` than baseline **and** verified lossless recovery.',
	'',
];
if ($wins !== []) {
	$md[] = '## Verified wins';
	$md[] = '';
	foreach ($wins as $r) {
		$md[] = sprintf(
			'- **%s** `%s`: %s → %s (Δ +%s)%s',
			$r['id'],
			$r['forward'],
			number_format($r['baseline_whole_fzc']),
			number_format($r['converted_whole_fzc']),
			number_format((int) $r['whole_fzc_delta']),
			$r['table_fzc_ref'] !== null ? ', table ref ' . number_format((int) $r['table_fzc_ref']) : ''
		);
	}
} else {
	$md[] = '## No verified wins vs paired baseline in this pass.';
}
if ($tableWins !== []) {
	$md[] = '';
	$md[] = '## Verified table wins (beat published fzc B)';
	$md[] = '';
	foreach ($tableWins as $r) {
		$md[] = sprintf(
			'- **%s** `%s`: conv %s vs table %s (Δ +%s)',
			$r['id'],
			$r['forward'],
			number_format($r['converted_whole_fzc']),
			number_format((int) $r['table_fzc_ref']),
			number_format((int) $r['vs_table_delta'])
		);
	}
} else {
	$md[] = '';
	$md[] = '## No verified table wins (none beat published fzc B with round-trip).';
}
$md[] = '';
$md[] = '| Case | Preprocess | Base fzc | Conv fzc | Δ | Table ref | vs table | RT |';
$md[] = '|------|------------|--------:|---------:|--:|----------:|---------:|:--:|';
foreach ($rows as $r) {
	$d = $r['whole_fzc_delta'];
	$vs = $r['vs_table_delta'];
	$md[] = sprintf(
		'| %s | %s | %s | %s | %s | %s | %s | %s |',
		$r['id'],
		$r['forward'],
		$r['baseline_whole_fzc'] ? number_format($r['baseline_whole_fzc']) : '—',
		$r['converted_whole_fzc'] ? number_format($r['converted_whole_fzc']) : '—',
		$d === null ? '—' : ($d >= 0 ? '+' . number_format($d) : number_format($d)),
		$r['table_fzc_ref'] !== null ? number_format((int) $r['table_fzc_ref']) : '—',
		$vs === null ? '—' : ($vs >= 0 ? '+' . number_format($vs) : number_format($vs)),
		$r['roundtrip_ok'] ? 'OK' : 'FAIL'
	);
}
file_put_contents(__DIR__ . '/CONVERT_WHOLE_CORPUS_VIA_BENCH.md', implode("\n", $md) . "\n");

echo "Whole-corpus via run_benchmarks\n\n";
printf("%-22s %-14s %10s %10s %10s %s\n", 'case', 'preprocess', 'base fzc', 'conv fzc', 'Δ', 'RT');
foreach ($rows as $r) {
	$d = $r['whole_fzc_delta'];
	printf(
		"%-22s %-14s %10s %10s %10s %s\n",
		$r['id'],
		$r['forward'],
		$r['baseline_whole_fzc'] ? number_format($r['baseline_whole_fzc']) : '—',
		$r['converted_whole_fzc'] ? number_format($r['converted_whole_fzc']) : '—',
		$d === null ? '—' : ($d >= 0 ? '+' . number_format($d) : number_format($d)),
		$r['roundtrip_ok'] ? 'OK' : 'FAIL'
	);
}
echo "\nWins (vs paired baseline): " . count($wins) . "\n";
echo "Table wins (beat published fzc B + RT): " . count($tableWins) . "\n";
if ($tableRef = ($rows[0]['table_fzc_ref'] ?? null)) {
	echo "(test_files53 table ref: " . number_format($tableRef) . ")\n";
}
