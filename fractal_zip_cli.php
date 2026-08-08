#!/usr/bin/env php
<?php
/**
 * Command-line entry for fractal_zip: create a .fz next to a folder, or extract members beside a .fz.
 *
 * Usage:
 *   php fractal_zip_cli.php zip <directory>
 *       Writes <directory>.fz (sibling of the folder path you pass). Example: zip test_files2 → test_files2.fz
 *
 *   php fractal_zip_cli.php extract <path.fz> [output_directory]
 *       Default: extracts member files into the same directory as the .fz (matches open_container).
 *       If output_directory is set: copies the .fz there first, then extracts next to that copy.
 *       After extract, prints one absolute path per member file (sorted).
 *
 *   php fractal_zip_cli.php member-list [--json] <path.fz>
 *       Lists logical member paths (web-fs API; FZB4-friendly outers without full legacy decode when possible).
 *
 *   php fractal_zip_cli.php member-read [--json] <path.fz> <memberRelPath> [output_file]
 *       Decodes one member: writes binary to stdout if output_file omitted, else to the given path.
 *       With --json: one JSON object on stdout (member-read uses bytes_base64 when writing to stdout).
 *
 *   php fractal_zip_cli.php member-write [--json] <path.fz> <memberRelPath> <input_file>
 *       Replaces one member inside the container (FZHM splice or FZB4 rebuild). Reads new bytes from input_file.
 *       Rewrites the .fz in place when possible; prints JSON summary with --json.
 *
 *   php fractal_zip_cli.php inspect [--json] <path.fz|.fractalzip>
 *       Quick container fingerprint + member-list probe (decode-light).
 *
 * member-list/read on zpaq/7z/arc outers often return native_outer_single_member_unsupported — use extract/open_container.
 * Host tool parity: docs/WEB_LOCAL_PARITY.md, php examples/fzc_capability_report.php --human
 *
 *   php fractal_zip_cli.php help
 *
 *   php fractal_zip_cli.php zip --preprocess=png_to_bmp <directory>
 *       Staging copy: convert matching members (see fractal_zip_preprocess.php), then zip.
 *       Writes <directory>.fz.preprocess.json for extract --reverse-preprocess.
 *       Same as zip, but enables FRACTAL_ZIP_ULTRA=1 and bytes-first “try hardest” defaults (slow; see below).
 *
 *   php fractal_zip_cli.php zip --text-inner <directory>
 *   php fractal_zip_cli.php zip --world-record <directory>
 *       Ultra + enwik8-scale caps + Wikipedia entry sort (see docs/WORLD_RECORD_PRESET.md).
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

// Re-exec with opcache/JIT before requiring fractal_zip.php: otherwise the parent
// compiles the whole ~30k-line library only to throw it away when the bootstrap
// (required from fractal_zip.php line 3) re-execs — ~2.5 s wasted per invocation.
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cli_opcache_bootstrap.php';

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

function fractal_zip_cli_argv_has_text_inner_flag(array $argvFull): bool {
	$n = sizeof($argvFull);
	for($i = 1; $i < $n; $i++) {
		if($argvFull[$i] === '--text-inner') {
			return true;
		}
	}
	return false;
}

function fractal_zip_cli_argv_has_no_text_inner_flag(array $argvFull): bool {
	$n = sizeof($argvFull);
	for($i = 1; $i < $n; $i++) {
		if($argvFull[$i] === '--no-text-inner') {
			return true;
		}
	}
	return false;
}

function fractal_zip_cli_argv_has_world_record_flag(array $argvFull): bool {
	$n = sizeof($argvFull);
	for($i = 1; $i < $n; $i++) {
		if($argvFull[$i] === '--world-record') {
			return true;
		}
	}
	return false;
}

/**
 * @return array{0: list<string>, 1: ?string}
 */
function fractal_zip_cli_strip_zip_flags(array $argvFull): array {
	$preprocess = null;
	$out = array();
	$n = sizeof($argvFull);
	for($i = 1; $i < $n; $i++) {
		$a = $argvFull[$i];
		if($a === '--ultra' || $a === '--world-record' || $a === '--text-inner' || $a === '--no-text-inner') {
			continue;
		}
		if(strncmp($a, '--preprocess=', 13) === 0) {
			$preprocess = substr($a, 13);
			continue;
		}
		$out[] = $a;
	}
	return array($out, $preprocess);
}

function fractal_zip_cli_extract_has_reverse_preprocess(array $argvFull): bool {
	foreach($argvFull as $a) {
		if($a === '--reverse-preprocess') {
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

function fractal_zip_cli_apply_text_inner_env_defaults(): void {
	putenv('FRACTAL_ZIP_GENERAL_TEXT_INNER=1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_FORMAT', 'phda9_xml');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_MONO', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_LAYOUT', 'per_page');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_TEXT_INNER_STACK', 'none');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL', 'phda9_no_lstm');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_PHDA9_DAEMON', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_PHDA9_GENERAL_FAST', '0');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_PHDA9_FAST_PROBE', '0');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS', '1');
	fractal_zip_cli_putenv_if_unset('FRACTAL_ZIP_PAQ_TIMEOUT_SEC', '0');
}

if(fractal_zip_cli_argv_has_ultra_flag($argv) || getenv('FRACTAL_ZIP_ULTRA') === '1') {
	fractal_zip_cli_apply_ultra_env_defaults();
}
$cliIsZip = isset($argv[1]) && $argv[1] === 'zip';
// Text-inner is specialized: opt in with --text-inner (or FRACTAL_ZIP_GENERAL_TEXT_INNER=on).
// Default `zip` stays on the lifestyle/general path without that preset.
if (fractal_zip_cli_argv_has_text_inner_flag($argv)) {
	fractal_zip_cli_apply_text_inner_env_defaults();
}
if(fractal_zip_cli_argv_has_world_record_flag($argv)) {
	require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
	bench_world_record_apply_env_defaults();
}

require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip_preprocess.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fzc_parity_hints.php';

/**
 * @param array<string, mixed> $payload
 */
function fractal_zip_cli_echo_json_line(array $payload): void {
	$js = bench_json_encode_try($payload, false);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (fractal_zip_cli): ' . json_last_error_msg() . "\n");
		echo '{"ok":false,"code":"json_encode_failed"}' . "\n";

		return;
	}
	echo $js . "\n";
}

$verbose = getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1';

/**
 * Strip `--json` from member-* argv tails (flag may appear anywhere among remaining args).
 *
 * @param array<int, string> $args
 * @return array{0: list<string>, 1: bool}
 */
function fractal_zip_cli_strip_member_json_flag(array $args): array {
	$json = false;
	$out = array();
	foreach ($args as $a) {
		if ($a === '--json') {
			$json = true;
			continue;
		}
		$out[] = $a;
	}
	return array($out, $json);
}

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
	return $a !== '--ultra' && $a !== '--world-record' && $a !== '--text-inner' && $a !== '--no-text-inner';
}));
list($zipArgv, $preprocessRecipe) = fractal_zip_cli_strip_zip_flags($argv);
$extractReversePreprocess = fractal_zip_cli_extract_has_reverse_preprocess($argv);
$argvList = array_values(array_filter($zipArgv, static function ($a) {
	return $a !== '--reverse-preprocess';
}));
if ($argvList === [] || $argvList[0] === 'help' || $argvList[0] === '--help' || $argvList[0] === '-h') {
	echo <<<TXT
fractal_zip CLI

  php fractal_zip_cli.php zip <directory>
  php fractal_zip_cli.php zip --preprocess=png_to_bmp <directory>
  php fractal_zip_cli.php zip --text-inner <directory>
  php fractal_zip_cli.php extract --reverse-preprocess <file.fz> [output_directory]
  php fractal_zip_cli.php zip --world-record <directory>
  php fractal_zip_cli.php extract <file.fz> [output_directory]
  php fractal_zip_cli.php extract --ultra <file.fz> [output_directory]
  php fractal_zip_cli.php member-list [--json] <file.fz>
  php fractal_zip_cli.php member-read [--json] <file.fz> <memberRelPath> [output_file]
  php fractal_zip_cli.php inspect [--json] <file.fz>
  php fractal_zip_cli.php fzsx pack <directory|.fz> [--out=file.fzsx]
  php fractal_zip_cli.php fzsx extract <file.fzsx>
  php fractal_zip_cli.php fzsxsd pack <directory|.fz> [--out=file.fzsxsd]
  php fractal_zip_cli.php fzsxsd extract <file.fzsxsd>
  php fractal_zip_cli.php fzsd pack <directory|.fz> [--out=file.fzsd]   # short form of fzsxsd
  php fractal_zip_cli.php fzsd extract <file.fzsd>
  php fractal_zip_cli.php help

--ultra (or FRACTAL_ZIP_ULTRA=1): bytes-first “try hardest” env preset for smallest .fz (slow).
--world-record: ultra + 128 MiB native caps + FRACTAL_ZIP_ENWIK_ENTRY_SORT=1 (enwik8 / Hutter-scale).
--text-inner: general text-inner compression (phda9_xml) for textish/HTML files (default on when unset).
  Default inner: phda9_no_lstm via daemon (FRACTAL_ZIP_PHDA9_DAEMON=1, one ~1 GiB worker; never N concurrent phda9).
  Set FRACTAL_ZIP_PHDA9_GENERAL_FAST=1 for tokenized_zpaq fast inner (shipped dict + zpaq, ~90 KB vs ~82 KB).
  Set FRACTAL_ZIP_PHDA9_DAEMON=0 only for debug direct exec (still file-locked singleton).
  FRACTAL_ZIP_GENERAL_TEXT_INNER_WALL_SEC=N — hard wall for the phda9 inner job; on timeout the zip fails
  loudly (no silent codec switch). Pair with GENERAL_FAST=1 when a speed SLA matters.
  FRACTAL_ZIP_GENERAL_TEXT_SPLIT_BLOCKS=1 — opt-in paragraph/HTML block splitting (single-file folders only);
  default is one lossless whole-file block (bit-exact restore).
  FRACTAL_ZIP_TOKENIZED_ZPAQ_DELTA=auto — supplemental words in FZPA tail when net wire wins (≥512 KiB).
  FRACTAL_ZIP_TOKENIZED_ZPAQ_HIT_GATE=1 — optional slow 65 KiB hit-rate gate (off by default).
  FRACTAL_ZIP_TOKENIZED_ZPAQ_ZPAQ_RT_MAX_BYTES=8388608 — skip zpaq decompress RT above 8 MiB raw on raw-zpaq path.
  FRACTAL_ZIP_TOKENIZED_ZPAQ_MINE=1 — full mined vocab in FZPA wire tail (not preprocess JSON).
  Set FRACTAL_ZIP_GENERAL_TEXT_INNER=off or pass --no-text-inner to disable; auto = only single-file textish folders ≥4 KiB.

Local double-click: register .fzsx once (scripts/fzsx_register_*.sh) then open archive → extracts beside file.
.fzsxsd / .fzsd self-destruct after extract (archive file removed); see docs/FZSX_FORMAT.md.
.fzsd is the preferred short form of .fzsxsd going forward.
Server: upload .fzsx, open URL, click Extract files (see docs/FZSX_FORMAT.md).

Set FRACTAL_ZIP_CLI_VERBOSE=1 to show library HTML/debug output during zip/extract/member-*/inspect.

Web/local extract parity (missing zpaq on server): docs/WEB_LOCAL_PARITY.md
  php examples/fzc_capability_report.php --human

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
	$fzcPath = $abs . '.fz';
	$zipDir = $abs;
	$stageDir = null;
	$preprocessManifest = null;
	$stageVerbatimDir = null;
	if ($preprocessRecipe !== null && $preprocessRecipe !== '') {
		try {
			$built = fractal_zip_preprocess_build_stage($abs, $preprocessRecipe);
			$stageDir = $built['stage_dir'];
			$stageVerbatimDir = $built['verbatim_dir'];
			$zipDir = $stageDir;
			$preprocessManifest = $built['manifest'];
		} catch (Throwable $e) {
			fwrite(STDERR, 'zip: preprocess failed: ' . $e->getMessage() . "\n");
			exit(1);
		}
	}
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, true, null, true);
	$fz->zip_folder($zipDir, false);
	if (!$verbose) {
		ob_end_clean();
	}
	$encodedFzc = $zipDir . '.fz';
	if ($stageDir !== null) {
		if (is_file($encodedFzc)) {
			if (is_file($fzcPath)) {
				@unlink($fzcPath);
			}
			if (!@rename($encodedFzc, $fzcPath)) {
				if (!@copy($encodedFzc, $fzcPath)) {
					fwrite(STDERR, "zip: failed to move staged .fz to {$fzcPath}\n");
					exit(1);
				}
				@unlink($encodedFzc);
			}
		}
		fractal_zip_preprocess_rmtree($stageDir);
	}
	if (!is_file($fzcPath)) {
		fwrite(STDERR, "zip: expected output file missing: {$fzcPath}\n");
		exit(1);
	}
	if (is_array($preprocessManifest)) {
		fractal_zip_preprocess_publish_sidecar(
			$fzcPath,
			$preprocessManifest,
			is_string($stageVerbatimDir) ? $stageVerbatimDir : ''
		);
		if (is_string($stageVerbatimDir) && $stageVerbatimDir !== '' && is_dir($stageVerbatimDir)) {
			fractal_zip_preprocess_rmtree($stageVerbatimDir);
		}
	}
	$sz = filesize($fzcPath);
	echo "Wrote {$fzcPath} (" . ($sz !== false ? (string) $sz : '?') . " bytes)\n";
	exit(0);
}

if ($cmd === 'extract') {
	if ($argvList === []) {
		fwrite(STDERR, "extract: missing .fz path.\n");
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
		$sidecarSrc = fractal_zip_preprocess_sidecar_path($fzcAbs);
		if (is_file($sidecarSrc)) {
			@copy($sidecarSrc, fractal_zip_preprocess_sidecar_path($targetFzc));
		}
		$verbSrc = fractal_zip_preprocess_verbatim_dir($fzcAbs);
		$verbDst = fractal_zip_preprocess_verbatim_dir($targetFzc);
		if (is_dir($verbSrc)) {
			fractal_zip_preprocess_rmtree($verbDst);
			fractal_zip_preprocess_copy_tree($verbSrc, $verbDst);
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
	$sidecar = fractal_zip_preprocess_read_sidecar($targetFzc);
	if ($extractReversePreprocess && is_array($sidecar)) {
		$rev = fractal_zip_preprocess_apply_reverse($targetFzc, $rootDisp);
		if ($rev['restored'] > 0) {
			$via = $rev['via_convert'] > 0 && $rev['via_verbatim'] === 0
				? 'convert-reverse'
				: ($rev['via_verbatim'] > 0 && $rev['via_convert'] === 0
					? 'verbatim sidecar'
					: 'convert-reverse + verbatim');
			echo 'Reverse-preprocess restored ' . $rev['restored'] . ' member(s) byte-identical via ' . $via . '.' . "\n";
		}
		foreach ($rev['errors'] as $err) {
			fwrite(STDERR, 'reverse-preprocess: ' . $err . "\n");
		}
		if (!$rev['byte_identical']) {
			fwrite(STDERR, "reverse-preprocess: byte-identical restore failed.\n");
			exit(1);
		}
	} elseif ($extractReversePreprocess && $sidecar === null) {
		fwrite(STDERR, "extract: --reverse-preprocess but no sidecar at " . fractal_zip_preprocess_sidecar_path($targetFzc) . "\n");
		exit(1);
	}
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

if ($cmd === 'member-list') {
	list($argvList, $jsonList) = fractal_zip_cli_strip_member_json_flag($argvList);
	if ($argvList === []) {
		if ($jsonList) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'missing_path'));
		} else {
			fwrite(STDERR, "member-list: missing .fz path.\n");
		}
		exit(1);
	}
	$fzcIn = $argvList[0];
	$fzcAbs = realpath($fzcIn);
	if ($fzcAbs === false || !is_file($fzcAbs)) {
		if ($jsonList) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'not_found'));
		} else {
			fwrite(STDERR, "member-list: file not found: {$fzcIn}\n");
		}
		exit(1);
	}
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, false, null, false);
	$r = $fz->try_list_container_members_for_web_fs($fzcAbs);
	if (!$verbose) {
		ob_end_clean();
	}
	if (empty($r['ok'])) {
		$fail = fzc_web_fs_bridge_attach_parity_hint(array(
			'ok' => false,
			'code' => (string) ($r['code'] ?? 'failed'),
			'folder_native_wire_kind' => $r['folder_native_wire_kind'] ?? null,
			'hint' => $r['hint'] ?? null,
		));
		if ($jsonList) {
			fractal_zip_cli_echo_json_line($fail);
			fzc_parity_stderr_fix_line($fail, 'member-list: ');
		} else {
			fwrite(STDERR, 'member-list: ' . (string) ($fail['code'] ?? 'failed') . "\n");
			fzc_parity_stderr_fix_line($fail, 'member-list: ');
		}
		exit(1);
	}
	if ($jsonList) {
		fractal_zip_cli_echo_json_line(array(
			'ok' => true,
			'members' => $r['members'] ?? array(),
		));
		exit(0);
	}
	foreach ($r['members'] ?? array() as $m) {
		echo (string) $m . "\n";
	}
	exit(0);
}

if ($cmd === 'member-read') {
	list($argvList, $jsonRead) = fractal_zip_cli_strip_member_json_flag($argvList);
	if ($argvList === [] || !isset($argvList[1])) {
		if ($jsonRead) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'usage'));
		} else {
			fwrite(STDERR, "member-read: usage: member-read [--json] <file.fz> <memberRelPath> [output_file]\n");
		}
		exit(1);
	}
	$fzcIn = $argvList[0];
	$member = (string) $argvList[1];
	$outFile = isset($argvList[2]) ? (string) $argvList[2] : null;
	$fzcAbs = realpath($fzcIn);
	if ($fzcAbs === false || !is_file($fzcAbs)) {
		if ($jsonRead) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'not_found'));
		} else {
			fwrite(STDERR, "member-read: file not found: {$fzcIn}\n");
		}
		exit(1);
	}
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, false, null, false);
	$r = $fz->try_read_container_member_bytes_for_web_fs($fzcAbs, $member);
	if (!$verbose) {
		ob_end_clean();
	}
	if (empty($r['ok'])) {
		$err = array(
			'ok' => false,
			'code' => (string) ($r['code'] ?? 'failed'),
		);
		if (!empty($r['members_preview']) && is_array($r['members_preview'])) {
			$err['members_preview'] = $r['members_preview'];
		}
		if (isset($r['folder_native_wire_kind'])) {
			$err['folder_native_wire_kind'] = $r['folder_native_wire_kind'];
		}
		if (isset($r['hint'])) {
			$err['hint'] = $r['hint'];
		}
		$err = fzc_web_fs_bridge_attach_parity_hint($err);
		if ($jsonRead) {
			fractal_zip_cli_echo_json_line($err);
			fzc_parity_stderr_fix_line($err, 'member-read: ');
		} else {
			fwrite(STDERR, 'member-read: ' . $err['code'] . "\n");
			fzc_parity_stderr_fix_line($err, 'member-read: ');
			if (isset($err['members_preview'])) {
				fwrite(STDERR, 'members_preview: ' . implode(', ', array_slice($err['members_preview'], 0, 16)) . "\n");
			}
		}
		exit(1);
	}
	$bytes = (string) ($r['bytes'] ?? '');
	$lane = isset($r['lane']) ? (string) $r['lane'] : null;
	if ($outFile !== null && $outFile !== '') {
		if (file_put_contents($outFile, $bytes) === false) {
			if ($jsonRead) {
				fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'write_failed'));
			} else {
				fwrite(STDERR, "member-read: cannot write: {$outFile}\n");
			}
			exit(1);
		}
		if ($jsonRead) {
			fractal_zip_cli_echo_json_line(array(
				'ok' => true,
				'path' => $outFile,
				'bytes_written' => strlen($bytes),
				'lane' => $lane,
			));
		} else {
			echo 'Wrote ' . (string) strlen($bytes) . ' bytes to ' . $outFile . "\n";
		}
		exit(0);
	}
	if ($jsonRead) {
		fractal_zip_cli_echo_json_line(array(
			'ok' => true,
			'bytes_base64' => base64_encode($bytes),
			'lane' => $lane,
		));
		exit(0);
	}
	echo $bytes;
	exit(0);
}

if ($cmd === 'member-write') {
	list($argvList, $jsonWrite) = fractal_zip_cli_strip_member_json_flag($argvList);
	if ($argvList === [] || !isset($argvList[1]) || !isset($argvList[2])) {
		if ($jsonWrite) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'usage'));
		} else {
			fwrite(STDERR, "member-write: usage: member-write [--json] <file.fz> <memberRelPath> <input_file>\n");
		}
		exit(1);
	}
	$fzcIn = $argvList[0];
	$member = (string) $argvList[1];
	$inFile = (string) $argvList[2];
	$fzcAbs = realpath($fzcIn);
	if ($fzcAbs === false || !is_file($fzcAbs)) {
		if ($jsonWrite) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'not_found'));
		} else {
			fwrite(STDERR, "member-write: file not found: {$fzcIn}\n");
		}
		exit(1);
	}
	if (!is_file($inFile) || !is_readable($inFile)) {
		if ($jsonWrite) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'input_not_found'));
		} else {
			fwrite(STDERR, "member-write: input not found: {$inFile}\n");
		}
		exit(1);
	}
	$newBytes = file_get_contents($inFile);
	if ($newBytes === false) {
		if ($jsonWrite) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'input_read_failed'));
		} else {
			fwrite(STDERR, "member-write: cannot read: {$inFile}\n");
		}
		exit(1);
	}
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, false, null, false);
	$r = $fz->try_commit_container_member_replace_for_web_fs($fzcAbs, $member, $newBytes);
	if (!$verbose) {
		ob_end_clean();
	}
	if (empty($r['ok'])) {
		$err = array(
			'ok' => false,
			'code' => (string) ($r['code'] ?? 'failed'),
		);
		if (isset($r['hint'])) {
			$err['hint'] = $r['hint'];
		}
		if ($jsonWrite) {
			fractal_zip_cli_echo_json_line($err);
		} else {
			fwrite(STDERR, 'member-write: ' . $err['code'] . "\n");
			if (isset($err['hint'])) {
				fwrite(STDERR, (string) $err['hint'] . "\n");
			}
		}
		exit(1);
	}
	if ($jsonWrite) {
		fractal_zip_cli_echo_json_line(array(
			'ok' => true,
			'lane' => $r['lane'] ?? null,
			'container_bytes_before' => $r['container_bytes_before'] ?? null,
			'container_bytes_after' => $r['container_bytes_after'] ?? null,
			'member_key' => $r['member_key'] ?? null,
		));
	} else {
		echo 'Updated ' . $fzcAbs . ' lane=' . (string) ($r['lane'] ?? '?')
			. ' size ' . (string) ($r['container_bytes_before'] ?? '?') . ' → ' . (string) ($r['container_bytes_after'] ?? '?') . "\n";
	}
	exit(0);
}

if ($cmd === 'fzsx') {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_fzsx.php';
	$sub = array_shift($argvList);
	if ($sub === null || $sub === 'help' || $sub === '--help') {
		echo <<<TXT
fzsx — self-extracting .fzsx (extract always beside the archive)

  php fractal_zip_cli.php fzsx pack <directory|.fz> [--out=file.fzsx] [--auto]
  php fractal_zip_cli.php fzsx extract <file.fzsx>

Double-click (after OS registration) is equivalent to: php file.fzsx

TXT;
		exit(0);
	}
	if ($sub === 'pack') {
		if ($argvList === []) {
			fwrite(STDERR, "fzsx pack: missing directory or .fz path.\n");
			exit(1);
		}
		$src = $argvList[0];
		$outPath = null;
		$autoExtract = false;
		for ($i = 1; $i < count($argvList); $i++) {
			$a = $argvList[$i];
			if ($a === '--out' && isset($argvList[$i + 1])) {
				$outPath = $argvList[$i + 1];
				$i++;
				continue;
			}
			if (str_starts_with($a, '--out=')) {
				$outPath = substr($a, 6);
				continue;
			}
			if ($a === '--auto') {
				$autoExtract = true;
				continue;
			}
			fwrite(STDERR, "fzsx pack: unknown option: {$a}\n");
			exit(1);
		}
		$srcAbs = realpath($src);
		if ($srcAbs === false) {
			fwrite(STDERR, "fzsx pack: not found: {$src}\n");
			exit(1);
		}
		if ($outPath === null || $outPath === '') {
			$base = is_dir($srcAbs) ? basename($srcAbs) : pathinfo($srcAbs, PATHINFO_FILENAME);
			$outPath = $base . '.fzsx';
		}
		$outAbs = $outPath;
		if (!str_contains($outPath, DIRECTORY_SEPARATOR) && !str_starts_with($outPath, '/')) {
			$outAbs = getcwd() . DIRECTORY_SEPARATOR . $outPath;
		}
		$repoRoot = realpath(__DIR__) ?: __DIR__;
		$examplesDir = $repoRoot . DIRECTORY_SEPARATOR . 'examples';
		$outRealDir = realpath(dirname($outAbs)) ?: dirname($outAbs);
		$opts = array(
			'lib_dir' => $repoRoot,
			'auto_extract' => $autoExtract,
		);
		$examplesReal = realpath($examplesDir);
		if ($examplesReal !== false && str_starts_with($outRealDir, $examplesReal)) {
			$outFile = basename($outAbs);
			$relFromExamples = substr($outRealDir, strlen($examplesReal) + 1);
			$opts['source_rel'] = ($relFromExamples !== false && $relFromExamples !== '')
				? str_replace('\\', '/', $relFromExamples) . '/' . $outFile
				: $outFile;
		}
		try {
			if (is_dir($srcAbs)) {
				fractal_zip_fzsx::pack_from_directory($outAbs, $srcAbs, $opts);
			} elseif (is_file($srcAbs) && (str_ends_with(strtolower($srcAbs), '.fz') || str_ends_with(strtolower($srcAbs), '.fzc'))) {
				fractal_zip_fzsx::pack_from_fzc($outAbs, $srcAbs, $opts);
			} else {
				fwrite(STDERR, "fzsx pack: expected directory or .fz file: {$src}\n");
				exit(1);
			}
		} catch (Throwable $e) {
			fwrite(STDERR, 'fzsx pack: ' . $e->getMessage() . "\n");
			exit(1);
		}
		$sz = filesize($outAbs);
		echo 'Wrote ' . $outAbs . ' (' . ($sz !== false ? (string) $sz : '?') . " bytes)\n";
		exit(0);
	}
	if ($sub === 'extract') {
		if ($argvList === []) {
			fwrite(STDERR, "fzsx extract: missing .fzsx path.\n");
			exit(1);
		}
		$fzsxIn = $argvList[0];
		$fzsxAbs = realpath($fzsxIn);
		if ($fzsxAbs === false || !is_file($fzsxAbs)) {
			fwrite(STDERR, "fzsx extract: file not found: {$fzsxIn}\n");
			exit(1);
		}
		exit(fractal_zip_fzsx::polyglot_cli_entry($fzsxAbs));
	}
	fwrite(STDERR, "fzsx: unknown subcommand: {$sub} (try: fzsx help)\n");
	exit(1);
}

if ($cmd === 'fzsxsd' || $cmd === 'fzsd') {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_fzsx.php';
	$preferShort = ($cmd === 'fzsd');
	$label = $preferShort ? 'fzsd' : 'fzsxsd';
	$defaultExt = $preferShort ? 'fzsd' : 'fzsxsd';
	$sub = array_shift($argvList);
	if ($sub === null || $sub === 'help' || $sub === '--help') {
		echo <<<TXT
{$label} — self-extracting .fzsxsd / .fzsd (extract beside archive; archive removed after success)

  php fractal_zip_cli.php {$label} pack <directory|.fz> [--out=file.{$defaultExt}] [--auto]
  php fractal_zip_cli.php {$label} extract <file.fzsxsd|file.fzsd>

.fzsd is the short form of .fzsxsd (same FZSXSD magic). Prefer .fzsd for new packs.

TXT;
		exit(0);
	}
	if ($sub === 'pack') {
		if ($argvList === []) {
			fwrite(STDERR, "{$label} pack: missing directory or .fz path.\n");
			exit(1);
		}
		$src = $argvList[0];
		$outPath = null;
		$autoExtract = false;
		for ($i = 1; $i < count($argvList); $i++) {
			$a = $argvList[$i];
			if ($a === '--out' && isset($argvList[$i + 1])) {
				$outPath = $argvList[$i + 1];
				$i++;
				continue;
			}
			if (str_starts_with($a, '--out=')) {
				$outPath = substr($a, 6);
				continue;
			}
			if ($a === '--auto') {
				$autoExtract = true;
				continue;
			}
			fwrite(STDERR, "{$label} pack: unknown option: {$a}\n");
			exit(1);
		}
		$srcAbs = realpath($src);
		if ($srcAbs === false) {
			fwrite(STDERR, "{$label} pack: not found: {$src}\n");
			exit(1);
		}
		if ($outPath === null || $outPath === '') {
			$base = is_dir($srcAbs) ? basename($srcAbs) : pathinfo($srcAbs, PATHINFO_FILENAME);
			$outPath = $base . '.' . $defaultExt;
		}
		$outAbs = $outPath;
		if (!str_contains($outPath, DIRECTORY_SEPARATOR) && !str_starts_with($outPath, '/')) {
			$outAbs = getcwd() . DIRECTORY_SEPARATOR . $outPath;
		}
		$repoRoot = realpath(__DIR__) ?: __DIR__;
		$examplesDir = $repoRoot . DIRECTORY_SEPARATOR . 'examples';
		$outRealDir = realpath(dirname($outAbs)) ?: dirname($outAbs);
		$opts = array(
			'lib_dir' => $repoRoot,
			'auto_extract' => $autoExtract,
		);
		$examplesReal = realpath($examplesDir);
		if ($examplesReal !== false && str_starts_with($outRealDir, $examplesReal)) {
			$outFile = basename($outAbs);
			$relFromExamples = substr($outRealDir, strlen($examplesReal) + 1);
			$opts['source_rel'] = ($relFromExamples !== false && $relFromExamples !== '')
				? str_replace('\\', '/', $relFromExamples) . '/' . $outFile
				: $outFile;
		}
		try {
			if (is_dir($srcAbs)) {
				fractal_zip_fzsxsd::pack_from_directory($outAbs, $srcAbs, $opts);
			} elseif (is_file($srcAbs) && (str_ends_with(strtolower($srcAbs), '.fz') || str_ends_with(strtolower($srcAbs), '.fzc'))) {
				fractal_zip_fzsxsd::pack_from_fzc($outAbs, $srcAbs, $opts);
			} else {
				fwrite(STDERR, "{$label} pack: expected directory or .fz file: {$src}\n");
				exit(1);
			}
		} catch (Throwable $e) {
			fwrite(STDERR, $label . ' pack: ' . $e->getMessage() . "\n");
			exit(1);
		}
		echo 'Wrote ' . $outAbs . ' (' . filesize($outAbs) . " bytes)\n";
		exit(0);
	}
	if ($sub === 'extract') {
		$fzsxIn = $argvList[0] ?? '';
		if ($fzsxIn === '') {
			fwrite(STDERR, "{$label} extract: missing .fzsxsd / .fzsd path.\n");
			exit(1);
		}
		$fzsxAbs = realpath($fzsxIn);
		if ($fzsxAbs === false || !is_file($fzsxAbs)) {
			fwrite(STDERR, "{$label} extract: file not found: {$fzsxIn}\n");
			exit(1);
		}
		exit(fractal_zip_fzsxsd::polyglot_cli_entry($fzsxAbs));
	}
	fwrite(STDERR, "{$label}: unknown subcommand: {$sub} (try: {$label} help)\n");
	exit(1);
}

if ($cmd === 'inspect') {
	list($argvList, $jsonInspect) = fractal_zip_cli_strip_member_json_flag($argvList);
	if ($argvList === []) {
		if ($jsonInspect) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => 'missing_path'));
		} else {
			fwrite(STDERR, "inspect: missing archive path.\n");
		}
		exit(1);
	}
	$pathIn = $argvList[0];
	if (!$verbose) {
		ob_start();
	}
	$fz = new fractal_zip(null, true, false, null, false);
	$payload = $fz->inspect_container_for_web_fs($pathIn);
	if (!$verbose) {
		ob_end_clean();
	}
	if (empty($payload['ok'])) {
		if ($jsonInspect) {
			fractal_zip_cli_echo_json_line(array('ok' => false, 'code' => (string) ($payload['code'] ?? 'not_found')));
		} else {
			fwrite(STDERR, 'inspect: file not found: ' . $pathIn . "\n");
		}
		exit(1);
	}
	$xzOuter = !empty($payload['xz_outer']);
	$legacyOuter = !empty($payload['outer_needs_legacy_full_read']);
	$nMembers = (int) ($payload['member_count'] ?? 0);
	$magicHex = (string) ($payload['magic_prefix_hex'] ?? '');
	if ($jsonInspect) {
		fractal_zip_cli_echo_json_line($payload);
		exit(0);
	}
	echo 'path: ' . $payload['path'] . "\n";
	echo 'container_bytes: ' . ($payload['container_bytes'] !== null ? (string) $payload['container_bytes'] : '?') . "\n";
	echo 'magic_prefix_hex: ' . $magicHex . "\n";
	echo 'sig4 (printable): ' . $payload['sig4_utf8_fallback'] . "\n";
	echo 'xz_outer: ' . ($xzOuter ? 'yes' : 'no') . "\n";
	echo 'outer_needs_legacy_full_read: ' . ($legacyOuter ? 'yes' : 'no') . "\n";
	echo 'member_list: ' . ($payload['member_list_ok'] ? 'ok' : 'no') . ' (' . (string) $nMembers . ' paths';
	if ($payload['member_list_code'] !== null) {
		echo ', code=' . $payload['member_list_code'];
	}
	echo ")\n";
	if (!$payload['member_list_ok'] && ($payload['member_list_code'] ?? '') === 'native_outer_single_member_unsupported') {
		$hint = fzc_parity_hint_for_web_fs_failure(array(
			'ok' => false,
			'code' => 'native_outer_single_member_unsupported',
			'folder_native_wire_kind' => $payload['folder_native_wire_kind'] ?? null,
		));
		if (is_array($hint) && !empty($hint['fix'])) {
			fwrite(STDERR, 'inspect: parity: ' . (string) $hint['fix'] . "\n");
		}
	}
	foreach ($payload['members_preview'] as $rel) {
		echo '  ' . $rel . "\n";
	}
	exit(0);
}

fwrite(STDERR, "Unknown command: {$cmd} (try: help)\n");
exit(1);
