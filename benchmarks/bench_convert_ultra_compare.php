#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare baseline vs convert-preprocess .fz under default bench env vs --ultra preset.
 * Table refs (59-case) were typically produced with bytes-first / ultra-style encodes.
 *
 *   php benchmarks/bench_convert_ultra_compare.php --corpus=test_files177 --recipe=png_to_bmp
 *   php benchmarks/bench_convert_ultra_compare.php --corpus=test_files53 --recipe=csv_to_json --strict-reverse
 */
$repo = dirname(__DIR__);
$convertRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'convert';

$corpus = 'test_files177';
$recipeId = 'png_to_bmp';
$useUltra = false;
$strictReverse = false;

foreach ($argv ?? [] as $arg) {
	if (str_starts_with($arg, '--corpus=')) {
		$corpus = substr($arg, 9);
	}
	if (str_starts_with($arg, '--recipe=')) {
		$recipeId = substr($arg, 9);
	}
	if ($arg === '--ultra') {
		$useUltra = true;
	}
	if ($arg === '--both-presets') {
		$useUltra = false; // handled below
	}
	if ($arg === '--strict-reverse') {
		$strictReverse = true;
	}
}

$bothPresets = in_array('--both-presets', $argv ?? [], true);

/** @var array<string, int|null> */
$tableFzc = [
	'test_files10' => 147,
	'test_files11' => 158,
	'test_files34' => 921,
	'test_files64' => 1255,
	'test_files53' => 74167,
	'test_files74' => 1307,
	'test_files149' => 1064,
];

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_preprocess.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';

function uc_apply_bench_env(bool $ultra): void
{
	putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
	putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
	// Clear ultra so re-apply is deterministic when toggling presets in-process.
	putenv('FRACTAL_ZIP_ULTRA');
	if ($ultra) {
		bench_ultra_apply_env_defaults();
	}
}

function uc_recipe(string $id): ?array
{
	$r = fractal_zip_preprocess_parse_recipe($id);
	if ($r !== null) {
		return $r;
	}
	$extra = [
		'csv_to_json' => ['id' => 'csv_to_json', 'glob' => '*.csv', 'forward' => 'csv_to_json', 'reverse' => 'json_to_csv', 'forward_ext' => 'json'],
		'bmp_to_png' => ['id' => 'bmp_to_png', 'glob' => '*.bmp', 'forward' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'forward_ext' => 'png'],
		'xlsx_to_csv' => ['id' => 'xlsx_to_csv', 'glob' => '*.xlsx', 'forward' => 'xlsx_to_csv', 'reverse' => 'csv_to_xlsx', 'forward_ext' => 'csv'],
	];
	return $extra[$id] ?? null;
}

/** @return array{fzc: int, sec: float, path: string} */
function uc_encode_dir(string $dir): array
{
	require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	$fzc = $dir . '.uc_tmp.fz';
	@unlink($fzc);
	$t0 = microtime(true);
	(new fractal_zip())->zip_folder($dir, false);
	$sec = microtime(true) - $t0;
	$default = $dir . '.fz';
	$bytes = 0;
	if (is_file($default)) {
		$bytes = (int) filesize($default);
		rename($default, $fzc);
	} elseif (is_file($fzc)) {
		$bytes = (int) filesize($fzc);
	}
	return ['fzc' => $bytes, 'sec' => $sec, 'path' => $fzc];
}

/** @return array{stage: string, manifest: array<string, mixed>, verbatim?: string} */
function uc_build_stage(string $srcDir, array $recipe, bool $withSidecar): array
{
	if (!fractal_zip_preprocess_ensure_convert()) {
		throw new RuntimeException('convert app missing');
	}
	$src = realpath($srcDir);
	if ($src === false || !is_dir($src)) {
		throw new InvalidArgumentException('Not a directory: ' . $srcDir);
	}
	$stage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_uc_' . bin2hex(random_bytes(4));
	$verbatimDir = $withSidecar ? ($stage . '.preprocess-verbatim') : '';
	if ($withSidecar) {
		mkdir($verbatimDir, 0755, true);
	}
	$members = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$rel = substr($item->getPathname(), strlen($src) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		$dst = $stage . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if ($item->isDir()) {
			if (!is_dir($dst)) {
				mkdir($dst, 0755, true);
			}
			continue;
		}
		$parent = dirname($dst);
		if (!is_dir($parent)) {
			mkdir($parent, 0755, true);
		}
		$base = basename($rel);
		if (!fractal_zip_preprocess_matches_glob($base, $recipe['glob'])) {
			copy($item->getPathname(), $dst);
			continue;
		}
		$bytes = (string) file_get_contents($item->getPathname());
		if ($withSidecar) {
			$verbPath = fractal_zip_preprocess_verbatim_file($verbatimDir, $rel);
			$verbParent = dirname($verbPath);
			if (!is_dir($verbParent)) {
				mkdir($verbParent, 0755, true);
			}
			file_put_contents($verbPath, $bytes);
		}
		$r = ActionRegistry::convert($recipe['forward'], $base, $bytes);
		$ext = $r->extension ?: $recipe['forward_ext'];
		$newRel = fractal_zip_preprocess_swap_ext($rel, $ext);
		$out = $stage . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $newRel);
		$outParent = dirname($out);
		if (!is_dir($outParent)) {
			mkdir($outParent, 0755, true);
		}
		file_put_contents($out, $r->bytes);
		$members[$rel] = [
			'stored_as' => $newRel,
			'forward' => $recipe['forward'],
			'reverse' => $recipe['reverse'],
			'orig_sha256' => hash('sha256', $bytes),
		];
	}
	$manifest = ['version' => 2, 'recipe' => $recipe['id'], 'members' => $members];
	$out = ['stage' => $stage, 'manifest' => $manifest];
	if ($withSidecar) {
		$out['verbatim'] = $verbatimDir;
	}
	return $out;
}

function uc_sidecar_bytes(string $fzcPath, ?string $verbatimDir): int
{
	$n = 0;
	$sp = fractal_zip_preprocess_sidecar_path($fzcPath);
	if (is_file($sp)) {
		$n += (int) filesize($sp);
	}
	if ($verbatimDir !== null && is_dir($verbatimDir)) {
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($verbatimDir, FilesystemIterator::SKIP_DOTS));
		foreach ($it as $f) {
			if ($f->isFile()) {
				$n += $f->getSize();
			}
		}
	}
	return $n;
}

/** @return array{label: string, baseline_fzc: int, convert_fzc: int, sidecar: int, baseline_sec: float, convert_sec: float, strict_ok: bool|null} */
function uc_run_case(string $repoRoot, string $srcRel, array $recipe, bool $ultra, bool $strictReverse): array
{
	uc_apply_bench_env($ultra);
	$srcDir = $repoRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $srcRel);
	if (!is_dir($srcDir)) {
		throw new InvalidArgumentException("missing corpus {$srcDir}");
	}
	$label = ($ultra ? 'ultra' : 'default') . ($strictReverse ? '+strict' : '+sidecar');

	$base = uc_encode_dir($srcDir);
	$needsSidecar = !$strictReverse && $recipe['id'] === 'png_to_bmp';
	$built = uc_build_stage($srcDir, $recipe, $needsSidecar);
	$conv = uc_encode_dir($built['stage']);
	$sidecar = 0;
	if ($needsSidecar && $conv['fzc'] > 0) {
		fractal_zip_preprocess_publish_sidecar($conv['path'], $built['manifest'], $built['verbatim'] ?? '');
		$sidecar = uc_sidecar_bytes($conv['path'], fractal_zip_preprocess_verbatim_dir($conv['path']));
	}
	fractal_zip_preprocess_rmtree($built['stage']);
	if (isset($built['verbatim'])) {
		fractal_zip_preprocess_rmtree($built['verbatim']);
	}
	@unlink($conv['path']);
	@unlink($conv['path'] . '.preprocess.json');
	fractal_zip_preprocess_rmtree($conv['path'] . '.preprocess-verbatim');
	@unlink($base['path']);

	$strictOk = null;
	if ($strictReverse && $recipe['id'] === 'bmp_to_png') {
		$strictOk = true;
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS));
		foreach ($it as $item) {
			if (!$item->isFile() || !fractal_zip_preprocess_matches_glob(basename($item->getPathname()), $recipe['glob'])) {
				continue;
			}
			$orig = (string) file_get_contents($item->getPathname());
			$mid = ActionRegistry::convert($recipe['forward'], basename($item->getPathname()), $orig);
			$back = ActionRegistry::convert($recipe['reverse'], 'x.' . $mid->extension, $mid->bytes);
			if ($orig !== $back->bytes) {
				$strictOk = false;
				break;
			}
		}
	}

	return [
		'label' => $label,
		'baseline_fzc' => $base['fzc'],
		'convert_fzc' => $conv['fzc'],
		'sidecar' => $sidecar,
		'baseline_sec' => $base['sec'],
		'convert_sec' => $conv['sec'],
		'strict_ok' => $strictOk,
	];
}

$recipe = uc_recipe($recipeId);
if ($recipe === null) {
	fwrite(STDERR, "unknown recipe {$recipeId}\n");
	exit(1);
}

$corpusKey = explode('/', $corpus)[0];
$tableRef = $tableFzc[$corpusKey] ?? null;

$presets = $bothPresets ? [false, true] : [$useUltra];

printf("Corpus: %s  recipe: %s  table ref: %s\n\n", $corpus, $recipeId, $tableRef !== null ? number_format($tableRef) : '—');
printf("%-14s %10s %10s %10s %8s %8s %s\n", 'preset', 'base fzc', 'conv fzc', 'total pkg', 'base s', 'conv s', 'notes');

foreach ($presets as $ultra) {
	try {
		$r = uc_run_case($repo, $corpus, $recipe, $ultra, $strictReverse);
	} catch (Throwable $e) {
		fwrite(STDERR, ($ultra ? 'ultra' : 'default') . ': ' . $e->getMessage() . "\n");
		continue;
	}
	$total = $strictReverse ? $r['convert_fzc'] : ($r['convert_fzc'] + $r['sidecar']);
	$notes = [];
	if ($tableRef !== null) {
		$notes[] = 'Δtable base ' . ($tableRef - $r['baseline_fzc'] >= 0 ? '+' : '') . number_format($tableRef - $r['baseline_fzc']);
		$notes[] = 'Δtable conv ' . ($tableRef - $total >= 0 ? '+' : '') . number_format($tableRef - $total);
	}
	if ($r['strict_ok'] === true) {
		$notes[] = 'strict RT OK';
	} elseif ($r['strict_ok'] === false) {
		$notes[] = 'strict RT FAIL';
	} elseif (!$strictReverse && $recipeId === 'png_to_bmp') {
		$notes[] = 'sidecar restore';
	}
	printf(
		"%-14s %10s %10s %10s %8.2f %8.2f %s\n",
		$ultra ? 'ultra' : 'default',
		number_format($r['baseline_fzc']),
		number_format($r['convert_fzc']),
		number_format($total),
		$r['baseline_sec'],
		$r['convert_sec'],
		implode('; ', $notes)
	);
}

echo "\nPreset: default = bytes-first bench (LIFESTYLE_SPEED_PROFILE=0); ultra = FRACTAL_ZIP_ULTRA=1 (same as run_benchmarks.php --ultra / zip --ultra).\n";
if (!$strictReverse && $recipeId === 'png_to_bmp') {
	echo "total pkg = conv fzc + verbatim sidecar (required for byte-identical PNG restore).\n";
}
