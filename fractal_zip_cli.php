#!/usr/bin/env php
<?php
/**
 * Command-line entry for fractal_zip: create a .fzc next to a folder, or extract members beside a .fzc.
 *
 * Usage:
 *   php fractal_zip_cli.php zip <directory>
 *       Writes <directory>.fzc (sibling of the folder path you pass). Example: zip test_files2 → test_files2.fzc
 *
 *   php fractal_zip_cli.php extract <path.fzc> [output_directory]
 *       Default: extracts member files into the same directory as the .fzc (matches open_container).
 *       If output_directory is set: copies the .fzc there first, then extracts next to that copy.
 *       After extract, prints one absolute path per member file (sorted).
 *
 *   php fractal_zip_cli.php help
 *
 *   php fractal_zip_cli.php zip --ultra <directory>
 *       Same as zip, but enables FRACTAL_ZIP_ULTRA=1 and bytes-first “try hardest” defaults (slow; see below).
 *
 * Environment variables are passed through (e.g. FRACTAL_ZIP_SEGMENT_LENGTH, FRACTAL_ZIP_MULTIPASS).
 * FRACTAL_ZIP_ULTRA=1 (or --ultra) applies additional defaults only for variables that are still unset — except
 * FRACTAL_ZIP_SPEED is forced to 0 so ultra never runs under the fast preset.
 * Preset intent: unlimited fractal multipass while each pass improves (no pass-count cap; multipass wall off unless you set
 * FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS), relaxed multipass ratio gates for small gains, outer tournament at max
 * zstd/brotli levels with full huge-brotli and without gzip-margin early-stop skipping 7z/arc/zpaq (see fractal_zip::ultra_compression_enabled()).
 * Output from the library is suppressed unless FRACTAL_ZIP_CLI_VERBOSE=1.
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "fractal_zip_cli.php is CLI-only.\n");
	exit(1);
}

$repoRoot = realpath(__DIR__) ?: __DIR__;

/**
 * @param array<int, string> $argvFull
 */
function fractal_zip_cli_argv_has_ultra_flag(array $argvFull): bool {
	$n = sizeof($argvFull);
	for($i = 1; $i < $n; $i++) {
		if($argvFull[$i] === '--ultra') {
			return true;
		}
	}
	return false;
}

function fractal_zip_cli_putenv_if_unset(string $k, string $v): void {
	$e = getenv($k);
	if($e === false || trim((string) $e) === '') {
		putenv($k . '=' . $v);
	}
}

/**
 * Bytes-first “try as hard as possible” preset for offline benchmarks (e.g. vs fixed Calgary challenge entries).
 * Only sets env keys that are still empty; always clears FRACTAL_ZIP_SPEED so work is not clamped to the fast preset.
 */
function fractal_zip_cli_apply_ultra_env_defaults(): void {
	putenv('FRACTAL_ZIP_SPEED=0');
	putenv('FRACTAL_ZIP_ULTRA=1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '0');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER', '0');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES', '0');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL', '9');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL', '9');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES', '16777216');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES', '33554432');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO', '0');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_LITERAL_LARGE_TEXT_SKIP_PROBE_GZIP1_MIN_RATIO', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC', '0');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_MULTIPASS', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_MULTIPASS_MAX_ADDITIONAL_PASSES', 'unlimited');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD', '0.01');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_MULTIPASS_GATE_MULT', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_DEEP', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_ZSTD_LEVEL', '22');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_BROTLI_QUALITY', '11');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'full');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_ALWAYS_TRY_BROTLI', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND', '32');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES', '16777216');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_WHOLE_STREAM_FZWS', '1');
}

if(fractal_zip_cli_argv_has_ultra_flag($argv) || getenv('FRACTAL_ZIP_ULTRA') === '1') {
	fractal_zip_cli_apply_ultra_env_defaults();
}

require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';

/**
 * @return list<string> absolute paths (sorted) for members recorded during open_container / streaming extract
 */
function fractal_zip_cli_extracted_member_full_paths(fractal_zip $fz, string $rootDir): array
{
	$map = $fz->array_fractal_zipped_strings_of_files ?? null;
	if (!is_array($map) || $map === []) {
		return [];
	}
	$rootDir = rtrim($rootDir, DIRECTORY_SEPARATOR);
	$out = [];
	foreach (array_keys($map) as $rel) {
		$rel = (string) $rel;
		$full = $rootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$rp = realpath($full);
		$out[] = ($rp !== false && is_file($rp)) ? $rp : $full;
	}
	sort($out, SORT_STRING);
	return $out;
}

$argvList = array_values(array_filter(array_slice($argv, 1), static function ($a) {
	return $a !== '--ultra';
}));
if ($argvList === [] || $argvList[0] === 'help' || $argvList[0] === '--help' || $argvList[0] === '-h') {
	echo <<<TXT
fractal_zip CLI

  php fractal_zip_cli.php zip <directory>
  php fractal_zip_cli.php zip --ultra <directory>
  php fractal_zip_cli.php extract <file.fzc> [output_directory]
  php fractal_zip_cli.php extract --ultra <file.fzc> [output_directory]
  php fractal_zip_cli.php help

--ultra (or FRACTAL_ZIP_ULTRA=1): bytes-first “try hardest” env preset for smallest .fzc (slow).

Set FRACTAL_ZIP_CLI_VERBOSE=1 to show library HTML/debug output during zip/extract.

TXT;
	exit(0);
}

$cmd = array_shift($argvList);
if ($cmd === 'zip') {
	if ($argvList === []) {
		fwrite(STDERR, "zip: missing directory argument.\n");
		exit(1);
	}
	$dir = $argvList[0];
	$abs = realpath($dir);
	if ($abs === false || !is_dir($abs)) {
		fwrite(STDERR, "zip: not a directory: {$dir}\n");
		exit(1);
	}
	$fzcPath = $abs . '.fzc';
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, true, null, true);
	$fz->zip_folder($abs, false);
	if (!$verbose) {
		ob_end_clean();
	}
	if (!is_file($fzcPath)) {
		fwrite(STDERR, "zip: expected output file missing: {$fzcPath}\n");
		exit(1);
	}
	$sz = filesize($fzcPath);
	echo "Wrote {$fzcPath} (" . ($sz !== false ? (string) $sz : '?') . " bytes)\n";
	exit(0);
}

if ($cmd === 'extract') {
	if ($argvList === []) {
		fwrite(STDERR, "extract: missing .fzc path.\n");
		exit(1);
	}
	$fzcIn = $argvList[0];
	$fzcAbs = realpath($fzcIn);
	if ($fzcAbs === false || !is_file($fzcAbs)) {
		fwrite(STDERR, "extract: file not found: {$fzcIn}\n");
		exit(1);
	}
	$targetFzc = $fzcAbs;
	if (isset($argvList[1]) && $argvList[1] !== '') {
		$outDir = $argvList[1];
		if (!is_dir($outDir)) {
			if (!@mkdir($outDir, 0755, true) && !is_dir($outDir)) {
				fwrite(STDERR, "extract: cannot create directory: {$outDir}\n");
				exit(1);
			}
		}
		$outAbs = realpath($outDir);
		if ($outAbs === false) {
			fwrite(STDERR, "extract: bad output directory: {$outDir}\n");
			exit(1);
		}
		$base = basename($fzcAbs);
		$targetFzc = $outAbs . DIRECTORY_SEPARATOR . $base;
		if (!@copy($fzcAbs, $targetFzc)) {
			fwrite(STDERR, "extract: copy failed to {$targetFzc}\n");
			exit(1);
		}
	}
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, true, null, true);
	$fz->open_container($targetFzc, false);
	if (!$verbose) {
		ob_end_clean();
	}
	$root = dirname($targetFzc);
	$rootReal = realpath($root);
	$rootDisp = $rootReal !== false ? $rootReal : $root;
	$paths = fractal_zip_cli_extracted_member_full_paths($fz, $root);
	if ($paths !== []) {
		echo "Extracted " . (string) count($paths) . " file(s) under {$rootDisp}:\n";
		foreach ($paths as $p) {
			echo "  {$p}\n";
		}
	} else {
		echo "Extracted under {$rootDisp} (member path list unavailable; check directory contents).\n";
	}
	exit(0);
}

fwrite(STDERR, "Unknown command: {$cmd} (try: help)\n");
exit(1);
