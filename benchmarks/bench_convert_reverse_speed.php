#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Bytes + speed with STRICT byte-identical restore via convert reverse only (no sidecar).
 *
 *   php benchmarks/bench_convert_reverse_speed.php --corpus=test_files53 --recipe=csv_to_json
 *   php benchmarks/bench_convert_reverse_speed.php --corpus=test_files177 --recipe=png_to_bmp
 */
$repo = dirname(__DIR__);
$convertRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'convert';

$corpus = 'test_files53';
$recipeId = 'csv_to_json';

foreach ($argv ?? [] as $arg) {
	if (str_starts_with($arg, '--corpus=')) {
		$corpus = substr($arg, 9);
	}
	if (str_starts_with($arg, '--recipe=')) {
		$recipeId = substr($arg, 9);
	}
}

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_preprocess.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
require_once $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';

$recipe = fractal_zip_preprocess_parse_recipe($recipeId);
if ($recipe === null) {
	// Allow csv_to_json etc. not in preprocess recipes yet.
	$extra = [
		'csv_to_json' => ['id' => 'csv_to_json', 'glob' => '*.csv', 'forward' => 'csv_to_json', 'reverse' => 'json_to_csv', 'forward_ext' => 'json'],
		'bmp_to_png' => ['id' => 'bmp_to_png', 'glob' => '*.bmp', 'forward' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'forward_ext' => 'png'],
	];
	$recipe = $extra[$recipeId] ?? null;
}
if ($recipe === null) {
	fwrite(STDERR, "Unknown recipe: {$recipeId}\n");
	exit(1);
}

$srcDir = $repo . DIRECTORY_SEPARATOR . $corpus;
if (!is_dir($srcDir)) {
	fwrite(STDERR, "missing corpus {$srcDir}\n");
	exit(1);
}

/** @return array<string, string> rel => sha256 */
function cr_corpus_sha256(string $dir): array
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

/** @return array{stage_dir: string, manifest: array<string, mixed>} */
function cr_build_stage(string $sourceDir, array $recipe): array
{
	if (!fractal_zip_preprocess_ensure_convert()) {
		throw new RuntimeException('Convert app not found');
	}
	$src = realpath($sourceDir);
	if ($src === false || !is_dir($src)) {
		throw new InvalidArgumentException('Not a directory');
	}
	$stage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_cr_' . bin2hex(random_bytes(4));
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
	return [
		'stage_dir' => $stage,
		'manifest' => ['members' => $members, 'recipe' => $recipe['id']],
	];
}

/** Strict byte-identical restore via convert reverse (no sidecar). */
function cr_apply_convert_reverse(string $fzcPath, string $extractRoot, array $manifest): array
{
	$restored = 0;
	$errors = [];
	foreach ($manifest['members'] ?? [] as $origRel => $info) {
		if (!is_array($info)) {
			continue;
		}
		$storedRel = (string) ($info['stored_as'] ?? '');
		$reverse = (string) ($info['reverse'] ?? '');
		$expectSha = (string) ($info['orig_sha256'] ?? '');
		if ($storedRel === '' || $reverse === '') {
			continue;
		}
		$storedPath = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedRel);
		if (!is_file($storedPath)) {
			$errors[] = $origRel . ': missing extracted ' . $storedRel;
			continue;
		}
		try {
			$mid = (string) file_get_contents($storedPath);
			$restoredConv = ActionRegistry::convert($reverse, basename($storedPath), $mid);
			$origBytes = $restoredConv->bytes;
		} catch (Throwable $e) {
			$errors[] = $origRel . ': ' . $e->getMessage();
			continue;
		}
		if ($expectSha !== '' && hash('sha256', $origBytes) !== $expectSha) {
			$errors[] = $origRel . ': convert-reverse sha256 mismatch (not byte-identical)';
			continue;
		}
		$origPath = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, (string) $origRel);
		$parent = dirname($origPath);
		if (!is_dir($parent)) {
			mkdir($parent, 0755, true);
		}
		file_put_contents($origPath, $origBytes);
		if ($storedPath !== $origPath && is_file($storedPath)) {
			@unlink($storedPath);
		}
		$restored++;
	}
	return ['restored' => $restored, 'errors' => $errors, 'byte_identical' => $errors === [] && $restored > 0];
}

/** @return array{fzc: int, stage_sec: float, encode_sec: float, byte_identical: bool, mismatches: list<string>, convert_reverse_errors: list<string>, label: string} */
function cr_run(string $label, callable $encode, callable $extractRestore, array $origSha, ?array $manifest, ?array $verifyRels): array
{
	$r = $encode();
	$convertReverseErrors = [];
	try {
		$extractRestore($r['fzc_path'], $r['extract_root'], $manifest);
	} catch (RuntimeException $e) {
		$convertReverseErrors[] = $e->getMessage();
	}
	$after = cr_corpus_sha256($r['extract_root']);
	$mismatches = [];
	foreach ($verifyRels as $rel) {
		if (!isset($origSha[$rel])) {
			continue;
		}
		$sha = $origSha[$rel];
		if (!isset($after[$rel])) {
			$mismatches[] = "$rel: missing after extract+reverse";
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
		'byte_identical' => $mismatches === [] && $convertReverseErrors === [],
		'mismatches' => $mismatches,
		'convert_reverse_errors' => $convertReverseErrors,
	];
}

$origSha = cr_corpus_sha256($srcDir);
$work = sys_get_temp_dir() . '/fz_cr_bench_' . bin2hex(random_bytes(3));
$fzcBase = $srcDir . '.cr_baseline.fz';
$fzcConv = $srcDir . '.cr_convert.fz';
$verifyAll = array_keys($origSha);

$baseline = cr_run('baseline', static function () use ($srcDir, $work, $fzcBase): array {
	$extractRoot = $work . '_base_ex';
	fractal_zip_preprocess_rmtree($extractRoot);
	mkdir($extractRoot, 0755, true);
	@unlink($fzcBase);
	$t0 = microtime(true);
	(new fractal_zip())->zip_folder($srcDir, false);
	$encodeSec = microtime(true) - $t0;
	$defaultFzc = $srcDir . '.fz';
	if (is_file($defaultFzc)) {
		rename($defaultFzc, $fzcBase);
	}
	if (!is_file($fzcBase)) {
		throw new RuntimeException('baseline encode failed');
	}
	return [
		'fzc_path' => $fzcBase,
		'fzc_bytes' => (int) filesize($fzcBase),
		'stage_sec' => 0.0,
		'encode_sec' => $encodeSec,
		'extract_root' => $extractRoot,
	];
}, static function (string $fzc, string $extractRoot, ?array $manifest): void {
	$copy = $extractRoot . DIRECTORY_SEPARATOR . 'p.fz';
	copy($fzc, $copy);
	$cwd = getcwd();
	chdir($extractRoot);
	try {
		(new fractal_zip())->open_container($copy, false);
	} finally {
		if ($cwd !== false) {
			chdir($cwd);
		}
	}
}, $origSha, null, $verifyAll);

$built = cr_build_stage($srcDir, $recipe);
$manifest = $built['manifest'];
$stage = $built['stage_dir'];
$convertedKeys = array_keys($manifest['members'] ?? []);
$storedAsSet = [];
foreach ($manifest['members'] ?? [] as $info) {
	if (is_array($info) && isset($info['stored_as'])) {
		$storedAsSet[(string) $info['stored_as']] = true;
	}
}
$verifyConverted = $convertedKeys;
foreach ($origSha as $rel => $_sha) {
	if (isset($manifest['members'][$rel])) {
		continue;
	}
	$base = basename($rel);
	if (fractal_zip_preprocess_matches_glob($base, $recipe['glob'])) {
		continue;
	}
	if (isset($storedAsSet[$rel])) {
		continue;
	}
	$verifyConverted[] = $rel;
}
sort($verifyConverted);

$converted = cr_run($recipe['id'], static function () use ($stage, $work, $fzcConv): array {
	$extractRoot = $work . '_conv_ex';
	fractal_zip_preprocess_rmtree($extractRoot);
	mkdir($extractRoot, 0755, true);
	@unlink($fzcConv);
	$t0 = microtime(true);
	(new fractal_zip())->zip_folder($stage, false);
	$encodeSec = microtime(true) - $t0;
	$stageFzc = $stage . '.fz';
	if (!is_file($stageFzc)) {
		throw new RuntimeException('convert encode failed');
	}
	rename($stageFzc, $fzcConv);
	return [
		'fzc_path' => $fzcConv,
		'fzc_bytes' => (int) filesize($fzcConv),
		'stage_sec' => 0.0,
		'encode_sec' => $encodeSec,
		'extract_root' => $extractRoot,
	];
}, static function (string $fzc, string $extractRoot, ?array $manifest): void {
	$copy = $extractRoot . DIRECTORY_SEPARATOR . 'p.fz';
	copy($fzc, $copy);
	$cwd = getcwd();
	chdir($extractRoot);
	try {
		(new fractal_zip())->open_container($copy, false);
	} finally {
		if ($cwd !== false) {
			chdir($cwd);
		}
	}
	if (!is_array($manifest)) {
		throw new RuntimeException('missing manifest');
	}
	$rev = cr_apply_convert_reverse($fzc, $extractRoot, $manifest);
	if (!$rev['byte_identical']) {
		// Surface errors via exception caught by cr_run wrapper.
		throw new RuntimeException('convert-reverse failed: ' . implode('; ', $rev['errors']));
	}
}, $origSha, $manifest, $verifyConverted);

// Re-run stage timing separately (stage was built before converted run).
$tStage0 = microtime(true);
$built2 = cr_build_stage($srcDir, $recipe);
$stageSec = microtime(true) - $tStage0;
fractal_zip_preprocess_rmtree($built2['stage_dir']);
$converted['stage_sec'] = $stageSec;
fractal_zip_preprocess_rmtree($stage);

$memberCount = count($manifest['members'] ?? []);

printf("Corpus: %s (%d files, %d converted by %s)\n\n", $corpus, count($origSha), $memberCount, $recipe['id']);
printf("%-22s %10s %8s %8s %s\n", 'case', 'fzc', 'stage s', 'zip s', 'strict bytes RT');
printf("%-22s %10s %8s %8s %s\n",
	$baseline['label'],
	number_format($baseline['fzc']),
	'—',
	number_format($baseline['encode_sec'], 2),
	$baseline['byte_identical'] ? 'OK' : 'FAIL'
);
printf("%-22s %10s %8s %8s %s\n",
	$converted['label'],
	number_format($converted['fzc']),
	number_format($converted['stage_sec'], 2),
	number_format($converted['encode_sec'], 2),
	$converted['byte_identical'] ? 'OK' : 'FAIL'
);

$fzcDelta = $baseline['fzc'] - $converted['fzc'];
$zipSpeedDelta = $baseline['encode_sec'] - $converted['encode_sec'];
$wallSpeedDelta = $baseline['encode_sec'] - ($converted['stage_sec'] + $converted['encode_sec']);

echo "\n";
printf("fzc delta (convert smaller): %s bytes\n", $fzcDelta >= 0 ? '+' . number_format($fzcDelta) : number_format($fzcDelta));
printf("zip_folder: convert %s sec vs baseline %s sec (%s%.1f%%)\n",
	number_format($converted['encode_sec'], 2),
	number_format($baseline['encode_sec'], 2),
	$zipSpeedDelta >= 0 ? 'faster by ' : 'slower by ',
	$baseline['encode_sec'] > 0 ? abs(100.0 * $zipSpeedDelta / $baseline['encode_sec']) : 0.0
);
printf("wall (stage+zip): convert %s sec vs baseline %s sec (%s%.1f%%)\n",
	number_format($converted['stage_sec'] + $converted['encode_sec'], 2),
	number_format($baseline['encode_sec'], 2),
	$wallSpeedDelta >= 0 ? 'faster by ' : 'slower by ',
	$baseline['encode_sec'] > 0 ? abs(100.0 * $wallSpeedDelta / $baseline['encode_sec']) : 0.0
);
echo "\nRestore path: convert reverse only (strict === bytes). No sidecar.\n";

if (!$converted['byte_identical']) {
	if ($converted['convert_reverse_errors'] !== []) {
		echo "Convert-reverse errors:\n";
		foreach ($converted['convert_reverse_errors'] as $m) {
			echo "  - {$m}\n";
		}
	}
	if ($converted['mismatches'] !== []) {
		echo "Mismatches:\n";
		foreach ($converted['mismatches'] as $m) {
			echo "  - {$m}\n";
		}
	}
}

@unlink($fzcBase);
@unlink($fzcConv);
fractal_zip_preprocess_rmtree($work . '_base_ex');
fractal_zip_preprocess_rmtree($work . '_conv_ex');

exit($baseline['byte_identical'] && $converted['byte_identical'] ? 0 : 1);
