<?php
declare(strict_types=1);

/**
 * Disk staging preprocess for fractal_zip CLI (lossless convert before zip_folder).
 *
 * Byte-identical restore:
 * - png_to_bmp: verbatim sidecar ({fzc}.preprocess-verbatim/) — convert reverse re-encodes PNG
 * - bmp_to_png: convert reverse only (sidecar JSON lists members; no verbatim dir)
 * Sidecar: {fzc}.preprocess.json
 */

function fractal_zip_preprocess_convert_root(): ?string
{
	$e = getenv('FRACTAL_ZIP_CONVERT_ROOT');
	if (is_string($e) && $e !== '' && is_dir($e)) {
		return rtrim($e, DIRECTORY_SEPARATOR);
	}
	$candidate = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'convert';
	return is_dir($candidate) ? $candidate : null;
}

function fractal_zip_preprocess_ensure_convert(): bool
{
	static $loaded = false;
	if ($loaded) {
		return class_exists('ActionRegistry', false);
	}
	$root = fractal_zip_preprocess_convert_root();
	if ($root === null) {
		return false;
	}
	require_once $root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'bootstrap.php';
	require_once $root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'ActionRegistry.php';
	$loaded = true;
	return class_exists('ActionRegistry', false);
}

/** @return list<string> supported preprocess recipe ids */
function fractal_zip_preprocess_recipe_ids(): array
{
	$ids = ['png_to_bmp', 'bmp_to_png', 'webp_to_png', 'png_to_webp', 'tiff_to_png', 'png_to_tiff', 'gif_to_png', 'svg_to_png', 'svg_to_bmp'];
	$convertRoot = fractal_zip_preprocess_convert_root();
	if ($convertRoot !== null) {
		$bridge = $convertRoot . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'PeelBridge.php';
		if (is_file($bridge)) {
			require_once $bridge;
			if (function_exists('convert_peel_literal_actions')) {
				foreach (convert_peel_literal_actions() as $a) {
					if (!in_array($a, $ids, true)) {
						$ids[] = $a;
					}
				}
			}
		}
	}
	return $ids;
}

function fractal_zip_preprocess_parse_recipe(string $recipe): ?array
{
	$recipe = strtolower(trim($recipe));
	$known = [
		'png_to_bmp' => ['glob' => '*.png', 'forward' => 'png_to_bmp', 'reverse' => 'bmp_to_png', 'forward_ext' => 'bmp', 'restore' => 'verbatim_sidecar'],
		'bmp_to_png' => ['glob' => '*.bmp', 'forward' => 'bmp_to_png', 'reverse' => 'png_to_bmp', 'forward_ext' => 'png', 'restore' => 'convert_reverse'],
		'webp_to_png' => ['glob' => '*.webp', 'forward' => 'webp_to_png', 'reverse' => 'png_to_webp', 'forward_ext' => 'png', 'restore' => 'convert_reverse'],
		'png_to_webp' => ['glob' => '*.png', 'forward' => 'png_to_webp', 'reverse' => 'webp_to_png', 'forward_ext' => 'webp', 'restore' => 'convert_reverse'],
		'tiff_to_png' => ['glob' => '*.tiff', 'forward' => 'tiff_to_png', 'reverse' => 'png_to_tiff', 'forward_ext' => 'png', 'restore' => 'convert_reverse'],
		'png_to_tiff' => ['glob' => '*.png', 'forward' => 'png_to_tiff', 'reverse' => 'tiff_to_png', 'forward_ext' => 'tiff', 'restore' => 'convert_reverse'],
		'gif_to_png' => ['glob' => '*.gif', 'forward' => 'gif_to_png', 'reverse' => 'png_to_gif', 'forward_ext' => 'png', 'restore' => 'convert_reverse'],
		'svg_to_png' => ['glob' => '*.svg', 'forward' => 'svg_to_png', 'reverse' => 'png_to_svg', 'forward_ext' => 'png', 'restore' => 'verbatim_sidecar'],
		'svg_to_bmp' => ['glob' => '*.svg', 'forward' => 'svg_to_bmp', 'reverse' => 'bmp_to_png', 'forward_ext' => 'bmp', 'restore' => 'verbatim_sidecar'],
		'jpg_to_bmp' => ['glob' => '*.jpg', 'forward' => 'jpg_to_bmp', 'reverse' => 'bmp_to_jpg', 'forward_ext' => 'bmp', 'restore' => 'convert_reverse'],
		'bmp_to_jpg' => ['glob' => '*.bmp', 'forward' => 'bmp_to_jpg', 'reverse' => 'jpg_to_bmp', 'forward_ext' => 'jpg', 'restore' => 'convert_reverse'],
	];
	if (!isset($known[$recipe])) {
		return null;
	}
	$row = $known[$recipe];
	$row['id'] = $recipe;
	return $row;
}

function fractal_zip_preprocess_sidecar_path(string $fzcPath): string
{
	return $fzcPath . '.preprocess.json';
}

function fractal_zip_preprocess_verbatim_dir(string $fzcPath): string
{
	return $fzcPath . '.preprocess-verbatim';
}

function fractal_zip_preprocess_verbatim_key(string $memberRel): string
{
	$rel = str_replace('\\', '/', $memberRel);
	return str_replace('/', '__', $rel);
}

function fractal_zip_preprocess_verbatim_file(string $verbatimDir, string $memberRel): string
{
	return rtrim($verbatimDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . fractal_zip_preprocess_verbatim_key($memberRel) . '.orig';
}

function fractal_zip_preprocess_matches_glob(string $basename, string $glob): bool
{
	return fnmatch($glob, $basename, FNM_CASEFOLD);
}

function fractal_zip_preprocess_swap_ext(string $rel, string $newExt): string
{
	return preg_replace('/\.[^.]+$/', '.' . $newExt, $rel) ?? ($rel . '.' . $newExt);
}

/** @return array{stage_dir: string, verbatim_dir: string, manifest: array<string, mixed>} */
function fractal_zip_preprocess_build_stage(string $sourceDir, string $recipeId): array
{
	$recipe = fractal_zip_preprocess_parse_recipe($recipeId);
	if ($recipe === null) {
		throw new InvalidArgumentException('Unknown preprocess recipe: ' . $recipeId);
	}
	if (!fractal_zip_preprocess_ensure_convert()) {
		throw new RuntimeException('Convert app not found (set FRACTAL_ZIP_CONVERT_ROOT)');
	}
	$src = realpath($sourceDir);
	if ($src === false || !is_dir($src)) {
		throw new InvalidArgumentException('Not a directory: ' . $sourceDir);
	}
	$stage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_pp_' . bin2hex(random_bytes(4));
	$useVerbatim = ($recipe['restore'] ?? 'verbatim_sidecar') === 'verbatim_sidecar';
	$verbatimDir = $useVerbatim ? ($stage . '.preprocess-verbatim') : '';
	if ($useVerbatim) {
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
		if ($useVerbatim) {
			$verbPath = fractal_zip_preprocess_verbatim_file($verbatimDir, $rel);
			$verbParent = dirname($verbPath);
			if (!is_dir($verbParent)) {
				mkdir($verbParent, 0755, true);
			}
			if (file_put_contents($verbPath, $bytes) === false) {
				throw new RuntimeException('Failed to write verbatim original: ' . $rel);
			}
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
			'orig_bytes' => strlen($bytes),
		];
	}
	$manifest = [
		'version' => 2,
		'recipe' => $recipe['id'],
		'source_dir' => $src,
		'verbatim_restore' => $useVerbatim ? 'sidecar_files' : 'convert_reverse',
		'members' => $members,
	];
	return ['stage_dir' => $stage, 'verbatim_dir' => $verbatimDir, 'manifest' => $manifest];
}

function fractal_zip_preprocess_publish_sidecar(string $fzcPath, array $manifest, string $stageVerbatimDir): void
{
	if (($manifest['members'] ?? []) === []) {
		return;
	}
	$useVerbatim = ($manifest['verbatim_restore'] ?? 'sidecar_files') === 'sidecar_files';
	if ($useVerbatim && $stageVerbatimDir !== '' && is_dir($stageVerbatimDir)) {
		$destVerb = fractal_zip_preprocess_verbatim_dir($fzcPath);
		fractal_zip_preprocess_rmtree($destVerb);
		if (!@rename($stageVerbatimDir, $destVerb)) {
			fractal_zip_preprocess_copy_tree($stageVerbatimDir, $destVerb);
			fractal_zip_preprocess_rmtree($stageVerbatimDir);
		}
	}
	file_put_contents(
		fractal_zip_preprocess_sidecar_path($fzcPath),
		json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n"
	);
}

function fractal_zip_preprocess_copy_tree(string $src, string $dst): void
{
	if (!is_dir($src)) {
		return;
	}
	if (!is_dir($dst)) {
		mkdir($dst, 0755, true);
	}
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

function fractal_zip_preprocess_read_sidecar(string $fzcPath): ?array
{
	$path = fractal_zip_preprocess_sidecar_path($fzcPath);
	if (!is_readable($path)) {
		return null;
	}
	$j = json_decode((string) file_get_contents($path), true);
	return is_array($j) ? $j : null;
}

/**
 * Restore byte-identical originals: convert reverse when possible, else verbatim sidecar.
 *
 * @return array{restored: int, errors: list<string>, byte_identical: bool, via_convert: int, via_verbatim: int}
 */
function fractal_zip_preprocess_apply_reverse(string $fzcPath, string $extractRoot): array
{
	$manifest = fractal_zip_preprocess_read_sidecar($fzcPath);
	if ($manifest === null) {
		return ['restored' => 0, 'errors' => ['missing preprocess sidecar'], 'byte_identical' => false, 'via_convert' => 0, 'via_verbatim' => 0];
	}
	$verbatimDir = fractal_zip_preprocess_verbatim_dir($fzcPath);
	$hasVerbatim = is_dir($verbatimDir);
	$restored = 0;
	$viaConvert = 0;
	$viaVerbatim = 0;
	$errors = [];
	foreach ($manifest['members'] ?? [] as $origRel => $info) {
		if (!is_array($info)) {
			continue;
		}
		$storedRel = (string) ($info['stored_as'] ?? '');
		$reverse = (string) ($info['reverse'] ?? '');
		$expectSha = (string) ($info['orig_sha256'] ?? '');
		if ($storedRel === '') {
			continue;
		}
		$origBytes = null;
		$storedPath = $extractRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedRel);
		if ($reverse !== '' && is_file($storedPath) && fractal_zip_preprocess_ensure_convert()) {
			try {
				$mid = (string) file_get_contents($storedPath);
				$rev = ActionRegistry::convert($reverse, basename($storedPath), $mid);
				if ($expectSha === '' || hash('sha256', $rev->bytes) === $expectSha) {
					$origBytes = $rev->bytes;
					$viaConvert++;
				}
			} catch (Throwable $e) {
				// fall through to verbatim
			}
		}
		if ($origBytes === null && $hasVerbatim) {
			$verbFile = fractal_zip_preprocess_verbatim_file($verbatimDir, (string) $origRel);
			if (is_file($verbFile)) {
				$origBytes = (string) file_get_contents($verbFile);
				if ($expectSha !== '' && hash('sha256', $origBytes) !== $expectSha) {
					$errors[] = $origRel . ': verbatim sha256 mismatch';
					continue;
				}
				$viaVerbatim++;
			}
		}
		if ($origBytes === null) {
			$errors[] = $origRel . ': could not restore (convert-reverse and verbatim both failed)';
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
	return [
		'restored' => $restored,
		'errors' => $errors,
		'byte_identical' => $errors === [] && $restored > 0,
		'via_convert' => $viaConvert,
		'via_verbatim' => $viaVerbatim,
	];
}

function fractal_zip_preprocess_rmtree(string $dir): void
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
