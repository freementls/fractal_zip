<?php
if (PHP_SAPI === 'cli') {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cli_opcache_bootstrap.php';
}
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_marker_adapt.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_encode_pipeline.php';
fractal_zip_encode_pipeline::bootstrap_cli_parallel_defaults_if_cli();
/**
 * Lazy-load FLAC/FZCD helpers (~450 lines). Most .fzc opens (FZB4 + deflate) never touch FZCD.
 */
function fractal_zip_ensure_flac_pac_loaded(): void {
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_flac_pac.php';
}

/**
 * Lazy-load literal-bundle stack (~2k+ lines: image/raster/stream index + literal_pac + registry + deep_unwrap).
 * Skipping this on simple FZB mode-0 extracts avoids parsing a large dependency tree on every web POST (huge win under Xdebug / no OPcache).
 */
function fractal_zip_ensure_literal_pac_stack_loaded(): void {
	static $done = false;
	if ($done) {
		return;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_image_pac.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_raster_canonical.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_stream_index.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
	$done = true;
}

/**
 * Lazy-load OLE/CFB helpers (structured storage peel for literal mode 19).
 */
function fractal_zip_ensure_ole_cfb_loaded(): void {
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_ole_cfb.php';
}

/**
 * Multi-member ZIP literal mode 18 (semantic peel). Defined near bootstrap so compress works even if literal_pac loads later / out of order.
 * FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP_MULTI=0 disables.
 */
if (!function_exists('fractal_zip_literal_semantic_zip_multimember_enabled')) {
	function fractal_zip_literal_semantic_zip_multimember_enabled(): bool {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP_MULTI');
		if ($e === false || trim((string) $e) === '') {
			return $cached = true;
		}
		$v = strtolower(trim((string) $e));
		return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
}

/**
 * OLE compound (CFB) literal mode 19 peel. FRACTAL_ZIP_LITERAL_SEMANTIC_OLE=0 disables.
 */
if (!function_exists('fractal_zip_literal_semantic_ole_enabled')) {
	function fractal_zip_literal_semantic_ole_enabled(): bool {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_OLE');
		if ($e === false || trim((string) $e) === '') {
			return $cached = true;
		}
		$v = strtolower(trim((string) $e));
		return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
}

// dimensional breaks and other fractal string operations: encode video game map files, fractal_zip self-extractor .fzsx.php, compressed data that has zero size by referring to a point on a stream of compressed data and 
// decompressing a certain length of it; the stream source being? something like run time in reverse decompresses matter from a singularity

// Literal-bundle experiments (bytes-first): mode 12 = full-buffer byte reverse (strrev); mode 13 = BMP BI_RGB
// column-vertical delta; mode 14 = square-block transpose (leading floor(sqrt(n))^2 bytes as row-major s×s, transpose;
// mode 15 = BMP uniform grid: one cell template + per-cell scalar byte added to every channel/byte in the cell (BSS1).
// mode 16 = BMP grid core-only (BSS2): inner (cw−2m)×(ch−2m) template + per-cell scalar or BGR shift on core; cell rims stored raw.
// mode 17 = chained literal transforms (multipass squarebytes / vertical / …); see literal_bundle_greedy_transform_chain.
// mode 18 = multi-member ZIP semantic: per-file literals + rebuild (opens PKZIP so transforms run on inner payloads); FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP_MULTI=0 disables.
// mode 19 = OLE compound (CFB): zeroed full-file template + per-stream ranges and payloads (bit-exact rebuild); FRACTAL_ZIP_LITERAL_SEMANTIC_OLE=0 disables.
// Undecompressed `.flac`: optional full literal tournament on disk bytes — `FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE` (0|1|auto), `FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE_MAX_BYTES`, `FRACTAL_ZIP_LITERAL_FLAC_GZIP_PROBE_LEVEL`, `FRACTAL_ZIP_LITERAL_FLAC_SKIP_TRANSFORMS_MAX_GZIP1_RATIO`.
// Offline “representation lane” tables (raw vs deep-unwrap vs literal vs optional PCM / synthetic gzip nest): `php benchmarks/container_representation_experiment.php`.
// tail unchanged; self-inverse). Literal tournament defaults to bytes-first zlib deflate (BMP: probe 9; non-BMP ≤2 MiB default: probe 9; larger non-BMP: 1 unless FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL / FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES; FRACTAL_ZIP_SPEED clamps default cap to 512 KiB).
// Roadmap (defaults should encode lessons; avoid requiring env for README-class results): (1) Re-run
// benchmarks/run_benchmarks.php + refresh README table from JSON / baseline cache. (2) FLACPAC: default off (bit-exact FLAC);
// set FRACTAL_ZIP_FLACPAC=1 to audit write_fzcd_bundle_if_applicable gates (tools_ok, merged-fractal vs single-FLAC) on album corpora.
// (3) Literal transforms: extend choose_best_literal_bundle_transform with cheap probes; keep OOM guards on huge blobs.

// a really good milestone would be recompressing project 2612 and getting its size down

/*
zip_string = 'sujwnBJWEOFsdncJFND84nvcv*JH4H7hdha7g7f75d6576f8fg8G8g9G7554d67FC9g9G'

<folder>
<folder name="aa">8FFGF8966Ghg75dVjU5ERDCf345UmjHdf347h</folder>
<folder name="ab">
	<folder name="ba">767ggh866fgGu88KKo000PlU5433wDf66</folder>
	<folder name="bb">[3-20]</folder>
	<folder name="bc">443ScCFd444EDfGbh</folder>
	<file name="bd.ext">98h99B6FnK,o974weSD343dfG66Uij99OkBHf6</file>
	<file name="be.ext">this is other text in a file</file>
</folder>
<folder name="ac">[3-20]</folder>
<file name="ad.ext">98h99B6FnK,o974weSD343dfG66Uij99OkBHf6</file>
<file name="ae.ext">this is some text in a file</file>
</folder>

levels of encoding: zipped, marked (with operators), unzipped. and this requires multipass or recursive, whatever you call it
may prefer to use zip markers (or nested arrays, not sure) of file and folder operators (along with other new operators) instead of XML
*/

/*
aabb  ccccdddd
aabb  ccccdddd
bbaa  ccccdddd
bbaa  ccccdddd
      ddddcccc
	  ddddcccc
	  ddddcccc
	  ddddcccc
	  
fractally encoded by the mapping a => cc
									  cc
and b => dd
		 dd
and the original zoom

every c is the whole first pattern? need a way to achieve fixed values at a specific resolution (like assigning a pixel a value when looking at a fractal) does this qualify as lossless? would be pretty good for lossy compression
need an operator for fractal?
steps in the generation of an example 1-D fractal: aa, abbbba, abbccccccbba, abbcccddddddddcccbbaa, etc.
or: aaaa, aabbbbaa, aabbaaaabbaa, aabbaabbbbaabbaa, etc.
or, simple operation amid fractal growth: aaaa, aabbbbaa, aabb{inserted string}bbaa, aabb{inserted string}aaaabbaa, aabb{inserted string}aaaabbaabbaa, etc.
could be quite useful to determine all the equivalences between operators and fractal interactions; for example: zooming in a certain amount is equivalent to rotating or flipping, like on a spiral
test against palette swap of an image. anything else? of course operations like rotation, slide, scale, flip, animation frames? 3d: vector, vertex, mesh...
segways, fade in, fade out, other effects: namely?
*/

class fractal_zip {

/**
 * Primary objective for compression choices (auto-tune, outer codec staging, unified folder path): **smallest lossless
 * compressed output** (`.fzc` bytes) unless an env knob explicitly trades wall time for bytes (e.g. FRACTAL_ZIP_SPEED=1).
 * **`FRACTAL_ZIP_ULTRA=1`** (CLI: `php fractal_zip_cli.php zip --ultra <dir>`) applies the bytes-first “try hardest” preset (`fractal_zip_cli_apply_ultra_env_defaults()`): unlimited multipass while passes gain, no multipass wall unless `FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS`, max zstd/brotli outer levels, full huge-brotli, no gzip-margin early-stop skipping 7z/arc/zpaq, and marginal multipass gates relaxed.
 * Do not merge heuristics that **increase** compressed size on representative corpora unless they are strictly optional
 * (env-gated) or superseded by a default that is **≤** prior size everywhere we measure. Wall-time improvements are fine
 * only when output is **the same size or smaller** for the same inputs and env.
 * Benchmarks may still report a time-weighted J score; library defaults stay bytes-first.
 * FZB literal `choose_best_literal_bundle_transform` on uncompressed BI_RGB BMP defaults to zlib deflate **level 9** probes
 * and exhaustive 2-step grid chains on; for faster iteration set `FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL=1` and/or
 * `FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN=0`.
 * With **`FRACTAL_ZIP_SPEED=1`**, inner mode-17 greedy chain scoring uses zlib **level ≤2** by default (final pick is still
 * scored at the normal BMP probe level) unless **`FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL`** or
 * **`FRACTAL_ZIP_SPEED_LITERAL_CHAIN_PROBE_LEVEL`** (1–9 cap) is set; chain max rounds default to **16** unless
 * **`FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER`** is set.
 */

/** Outer wrapper from the most recent `adaptive_compress` call: `7z`, `gzip`, `arc`, `zstd`, `brotli`, `xz`, or `zpaq`. */
public static $last_outer_codec = null;
/** Digits of the zpaq {@code -method} that produced the winning zpaq outer, when known (e.g. native sweep, outer trial). */
public static $last_zpaq_method = null;
/** Prefix on outer brotli blobs (raw brotli has no stable magic). */
public const OUTER_BROTLI_MAGIC = 'FZb1';
/** Prefix on outer zpaq blobs (raw .zpaq has no stable magic we can rely on for all versions). */
public const OUTER_ZPAQ_MAGIC = 'FZzq';
/** Default inner/raw size cap (bytes) for trying {@code -method 6} before 5/4/3 in zpaq outer/native sweeps (2 MiB). */
public const ZPAQ_OUTER_METHOD_SIX_MAX_INNER_BYTES = 2097152;
/** Highest FZB literal mode allowed nested inside modes 6/7/8/11/20/21/22/23/24/25/26 (keep in sync with decode_bundle_literal_member). */
public const FZB_LITERAL_INNER_MODE_MAX = 26;
/** Second argument to {@see adaptive_compress}: stop after gzip/zstd/brotli merge (no xz / 7z / arc / zpaq). Legacy {@see zip_folder} fast wire shootout. */
public const ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE = 'after_fast_codec_merge';

/** Published during zip/unzip/substring expansion so static helpers see the active delimiter bytes. */
public static $fractal_marker_ctx_left = '<';
public static $fractal_marker_ctx_mid = '"';
public static $fractal_marker_ctx_right = '>';
public static $fractal_marker_ctx_range = '*';
/** When true, adaptive marker tuning is skipped (nested fractal-leg probes). */
public static $fractal_marker_adaptive_probe_guard = false;

/**
 * Outer zstd/xz/brotli (and matching decompress helpers): above this input size, compress/decompress from a temp file
 * so the child never shares a full stdin pipe with a growing stdout buffer (classic proc_open deadlock).
 * Set FRACTAL_ZIP_OUTER_CODEC_TEMP_INPUT_MIN_BYTES=0 to always use temp file; huge value forces stdin pipe only (risky for large payloads).
 */
public static function outer_codec_temp_input_threshold_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_CODEC_TEMP_INPUT_MIN_BYTES');
	if($e === false || trim((string)$e) === '') {
		return $cached = 8192;
	}
	return $cached = max(0, (int)$e);
}

/**
 * Directory for xz/brotli temp files when the repo tree is read-only (common on shared hosting).
 */
public static function outer_codec_temp_dir(): string {
	$sys = rtrim((string) sys_get_temp_dir(), DS);
	if($sys !== '' && @is_dir($sys) && @is_writable($sys)) {
		return $sys;
	}
	return substr(__FILE__, 0, fractal_zip::strpos_last(__FILE__, DS));
}

/**
 * Merged LD_LIBRARY_PATH for child tools when ~/.local/lib exists; null = no override (see command_prefix_with_local_lib).
 */
public static function ld_library_path_merged_for_home_local(): ?string {
	static $cachedDone = false;
	static $cached = null;
	if($cachedDone) {
		return $cached;
	}
	$cachedDone = true;
	$home = getenv('HOME');
	$ld = getenv('LD_LIBRARY_PATH');
	if(!is_string($home) || $home === '') {
		return $cached = null;
	}
	$localLib = $home . '/.local/lib';
	if(!is_dir($localLib)) {
		return $cached = null;
	}
	return $cached = $localLib . (($ld !== false && $ld !== '') ? (':' . $ld) : '');
}

/**
 * Write program_path/fzocin_*.bin at most once for outer zstd/brotli/xz temp-input reuse when innerLen ≥ threshold.
 * Deferred until a codec actually runs so prescreen / min-inner gates skip the disk write entirely when no fast codec tries.
 *
 * @param string|null $reusePath in-out; set to path or null on failure
 * @param bool $reuseEnsureDone in-out; caller initializes false
 */
function outer_codec_reuse_tmp_ensure(string $innerBlob, int $innerLen, &$reusePath, &$reuseEnsureDone): void {
	if($reuseEnsureDone) {
		return;
	}
	$reuseEnsureDone = true;
	$th = fractal_zip::outer_codec_temp_input_threshold_bytes();
	if($th <= 0 || $innerLen < $th) {
		return;
	}
	$reusePath = $this->program_path . DS . 'fzocin_' . substr(md5(fractal_zip::hot_string_digest($innerBlob) . "\0r\0" . (string) spl_object_id($this)), 0, 16) . '.bin';
	if(@file_put_contents($reusePath, $innerBlob) === false) {
		$reusePath = null;
	}
}

/**
 * Prefer writing a disk file before 7z "a" when the inner is large (avoids huge single fwrite to 7z stdin).
 * FRACTAL_ZIP_7Z_OUTER_FILE_FIRST_MIN_BYTES (default 2 MiB, 0 = never skip stdin attempt).
 */
public static function outer_7z_file_first_min_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_7Z_OUTER_FILE_FIRST_MIN_BYTES');
	if($e === false || trim((string)$e) === '') {
		return $cached = 2097152;
	}
	return $cached = max(0, (int)$e);
}

/**
 * Extra argv tokens for 7z outer adds: omit stored file / archive timestamps (payload is an opaque inner stream, not a
 * round-tripped filesystem tree). Shaves a small header cost on tiny archives. Disable with FRACTAL_ZIP_OUTER_7Z_METADATA_STRIP=0.
 *
 * @return list<string>
 */
public static function outer_7z_metadata_strip_argv(): array {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_7Z_METADATA_STRIP');
	if($e !== false && trim((string) $e) !== '') {
		$el = strtolower(trim((string) $e));
		if($el === '0' || $el === 'off' || $el === 'false' || $el === 'no') {
			return $cached = array();
		}
	}
	return $cached = array('-mtc=off', '-mta=off', '-mtm=off');
}

/**
 * Optional multi-threading for 7-Zip / p7zip LZMA2 (`-mmt`) on **add** and **extract**. Reads {@code FRACTAL_ZIP_7Z_MMT}, then {@code FRACTAL_ZIP_BENCH_7Z_MMT}.
 * Empty/unset ⇒ omit (encoder default, often single-thread LZMA on some builds). {@code on}/{@code all}/{@code auto}⇒ {@code -mmt=on};
 * decimal digits ⇒ {@code -mmt=N}; {@code off}/{@code false}/{@code no}⇒ {@code -mmt=off}.
 * Larger benchmark wall with same codec: set e.g. {@code FRACTAL_ZIP_7Z_MMT=on}.
 *
 * @return list<string> e.g. {@code ['-mmt=on']} or {@code []}
 */
public static function seven_zip_mmt_argv_from_env(): array {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_7Z_MMT');
	if($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_BENCH_7Z_MMT');
	}
	if($e === false || trim((string) $e) === '') {
		return $cached = array();
	}
	$v = strtolower(trim((string) $e));
	if($v === 'off' || $v === 'false' || $v === 'no') {
		return $cached = array('-mmt=off');
	}
	if($v === 'on' || $v === 'all' || $v === 'auto') {
		return $cached = array('-mmt=on');
	}
	if(ctype_digit($v)) {
		$n = (int) $v;
		if($n <= 0) {
			return $cached = array('-mmt=on');
		}
		return $cached = array('-mmt=' . (string) $n);
	}
	return $cached = array();
}

/**
 * Space-prefixed, shell-escaped {@see seven_zip_mmt_argv_from_env} for legacy {@code exec($cmd)} 7z lines.
 */
public static function seven_zip_mmt_shell_fragment_for_exec(): string {
	$out = '';
	foreach(fractal_zip::seven_zip_mmt_argv_from_env() as $t) {
		$out .= ' ' . escapeshellarg((string) $t);
	}
	return $out;
}

/**
 * Once per resolved binary path: {@code zpaq 2>&1} banner contains “zpaqfranz” (or project URL) — used to default {@code -threads 0} when no env.
 *
 * @var array<string, bool>
 */
private static $zpaqExecutableIsZpaqfranz = array();

/** @var array<string, string> cached {@see zpaq_executable_probe_banner_aggregate} */
private static $zpaqProbeBannerAggregate = array();

/**
 * Combined stderr/stdout from several lightweight invokes (bare, {@code --help}, {@code -h}, {@code --version}) for zpaq fork detection.
 */
private static function zpaq_executable_probe_banner_aggregate(string $exePath): string {
	if($exePath === '' || !is_file($exePath)) {
		return '';
	}
	if(isset(self::$zpaqProbeBannerAggregate[$exePath])) {
		return self::$zpaqProbeBannerAggregate[$exePath];
	}
	$fragments = array();
	foreach(array(' 2>&1', ' --help 2>&1', ' -h 2>&1', ' --version 2>&1') as $suffix) {
		$s = @shell_exec(escapeshellarg($exePath) . $suffix);
		if(is_string($s) && $s !== '') {
			$fragments[] = $s;
		}
	}

	return self::$zpaqProbeBannerAggregate[$exePath] = implode("\n", $fragments);
}

/**
 * Absolute-path probe for {@code FRACTAL_ZIP_ZPAQ}: resolves relative names against {@see getcwd()} (CLI benchmarks often {@code export FRACTAL_ZIP_ZPAQ=./zpaq} from the wrong directory).
 */
private static function fractal_zip_zpaq_env_resolve_trimmed(string $trimmedEnv, ?string &$resolvedIfExecutable): bool {
	$resolvedIfExecutable = null;
	if($trimmedEnv === '') {
		return false;
	}
	$candidates = array($trimmedEnv);
	if($trimmedEnv[0] !== '/' && !(strlen($trimmedEnv) > 2 && $trimmedEnv[1] === ':')) {
		$cwd = getcwd();
		if($cwd !== false) {
			$candidates[] = $cwd . DIRECTORY_SEPARATOR . $trimmedEnv;
		}
	}
	foreach($candidates as $cand) {
		if(is_string($cand) && is_executable($cand)) {
			$rp = realpath($cand);
			$resolvedIfExecutable = ($rp !== false) ? $rp : $cand;

			return true;
		}
	}

	return false;
}

/**
 * True when {@code $exePath} is almost certainly <strong>zpaqfranz</strong> (or a fork that documents {@code -threads} the same way), not stock Matt Mahoney zpaq.
 * Basename {@code *zpaqfranz*} wins without probing. Otherwise aggregates bare/{@code --help}/{@code -h}/{@code --version} output.
 */
public static function zpaq_executable_help_banner_is_zpaqfranz(string $exePath): bool {
	if($exePath === '' || !is_file($exePath)) {
		return false;
	}
	if(isset(self::$zpaqExecutableIsZpaqfranz[$exePath])) {
		return self::$zpaqExecutableIsZpaqfranz[$exePath];
	}
	$base = strtolower(basename($exePath));
	if(str_contains($base, 'zpaqfranz')) {
		return self::$zpaqExecutableIsZpaqfranz[$exePath] = true;
	}
	$o = self::zpaq_executable_probe_banner_aggregate($exePath);
	if($o === '') {
		return self::$zpaqExecutableIsZpaqfranz[$exePath] = false;
	}
	$ok = (stripos($o, 'zpaqfranz') !== false
		|| stripos($o, 'sourceforge.net/projects/zpaqfranz') !== false
		|| (stripos($o, 'zpaq archiver') !== false && stripos($o, 'franz') !== false)
		|| (preg_match('/-threads\b/i', $o) && preg_match('/\bfranz\b/i', $o)));
	return self::$zpaqExecutableIsZpaqfranz[$exePath] = (bool) $ok;
}

/**
 * Whether {@see zpaq_global_argv_shell_after_exe_from_env} may pass {@code -threads} for this binary.
 * {@code FRACTAL_ZIP_ZPAQ_GLOBAL_THREADS=1} forces on (your build supports {@code -threads} but detection failed); {@code 0}/{@code off} forces off.
 */
public static function zpaq_executable_accepts_global_threads_argv(string $exePath): bool {
	$e = getenv('FRACTAL_ZIP_ZPAQ_GLOBAL_THREADS');
	if($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if($v === '1' || $v === 'yes' || $v === 'on' || $v === 'true' || $v === 'force') {
			return true;
		}
		if($v === '0' || $v === 'no' || $v === 'off' || $v === 'false') {
			return false;
		}
	}

	return self::zpaq_executable_help_banner_is_zpaqfranz($exePath);
}

/**
 * Optional global argv between the {@code zpaq} executable and subcommands {@code add} / {@code x}. zpaqfranz accepts {@code -threads N}
 * (short {@code -tN}); {@code 0} = all cores in Franz docs. Reads {@code FRACTAL_ZIP_ZPAQ_THREADS}, then {@code FRACTAL_ZIP_BENCH_ZPAQ_THREADS}.
 * When both unset: if {@see zpaq_executable_accepts_global_threads_argv} on {@see zpaq_executable}, inserts {@code -threads 0}; stock zpaq omits (unknown flag).
 * Explicit {@code off}/{@code no}/{@code stock}/{@code st}/{@code false} ⇒ omit {@code -threads}.
 * Explicit {@code auto}/{@code 0}/digits are passed only when {@see zpaq_executable_accepts_global_threads_argv} is true (zpaqfranz / detected fork / {@code FRACTAL_ZIP_ZPAQ_GLOBAL_THREADS=1}).
 * Tooling assumes Matt Mahoney **zpaq 7.15+** or **zpaqfranz**; v7.05 is not bundled or documented here.
 */
public static function zpaq_global_argv_shell_after_exe_from_env(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZPAQ_THREADS');
	if($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS');
	}
	if($e !== false && trim((string) $e) !== '') {
		$raw = trim((string) $e);
		$v = strtolower($raw);
		if($v === 'off' || $v === 'no' || $v === 'false' || $v === 'stock' || $v === 'st') {
			return $cached = '';
		}
		$frag = '';
		if($v === 'auto' || $v === 'on' || $v === 'all' || $v === '0') {
			$frag = ' ' . escapeshellarg('-threads') . ' ' . escapeshellarg('0');
		} elseif(ctype_digit($v)) {
			$frag = ' ' . escapeshellarg('-threads') . ' ' . escapeshellarg($raw);
		} else {
			return $cached = '';
		}
		$ex = fractal_zip::zpaq_executable();
		if($ex !== null && !fractal_zip::zpaq_executable_accepts_global_threads_argv((string) $ex)) {
			return $cached = '';
		}
		return $cached = $frag;
	}
	$ex = fractal_zip::zpaq_executable();
	if($ex !== null && fractal_zip::zpaq_executable_accepts_global_threads_argv((string) $ex)) {
		return $cached = ' ' . escapeshellarg('-threads') . ' ' . escapeshellarg('0');
	}
	return $cached = '';
}

/**
 * Extra argv after the brotli executable and before {@code -c} (reference {@code brotli} has no threads; use a parallel-capable CLI
 * or wrapper and pass flags here). {@code FRACTAL_ZIP_BROTLI_EXTRA_ARGS} then {@code FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS} — whitespace-separated tokens, each shell-escaped when building commands.
 */
public static function brotli_compress_extra_argv_shell_fragment(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_EXTRA_ARGS');
	if($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS');
	}
	if($e === false || trim((string) $e) === '') {
		return $cached = '';
	}
	$parts = preg_split('/\s+/', trim((string) $e), -1, PREG_SPLIT_NO_EMPTY);
	if(!is_array($parts) || $parts === array()) {
		return $cached = '';
	}
	$out = '';
	foreach($parts as $p) {
		$out .= ' ' . escapeshellarg((string) $p);
	}
	return $cached = $out;
}

/** Cache-key tag so brotli outer blob cache invalidates when {@see brotli_compress_extra_argv_shell_fragment} changes. */
public static function brotli_compress_extra_argv_cache_tag(): string {
	static $tag = null;
	if($tag !== null) {
		return $tag;
	}
	$f = fractal_zip::brotli_compress_extra_argv_shell_fragment();
	return $tag = ($f === '') ? '' : ('|x:' . md5($f));
}

/**
 * Raw argv tokens after the {@code brotli} executable (same env as {@see brotli_compress_extra_argv_shell_fragment}) for {@code proc_open} argv.
 *
 * @return list<string>
 */
public static function brotli_compress_extra_argv_proc_tokens(): array {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_EXTRA_ARGS');
	if($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS');
	}
	if($e === false || trim((string) $e) === '') {
		return $cached = array();
	}
	$parts = preg_split('/\s+/', trim((string) $e), -1, PREG_SPLIT_NO_EMPTY);
	if(!is_array($parts) || $parts === array()) {
		return $cached = array();
	}
	$out = array();
	foreach($parts as $p) {
		$out[] = (string) $p;
	}
	return $cached = $out;
}

/**
 * Optional {@code xz} {@code -T} flag for benchmark tarball pipelines ({@code FRACTAL_ZIP_BENCH_XZ_THREADS}). Used by {@code benchmarks/run_benchmarks.php}.
 * Unset/empty ⇒ omit. {@code 0}/{@code auto}/{@code all}/{@code on} ⇒ {@code -T0}. Decimal digits ⇒ {@code -TN}.
 */
public static function bench_xz_thread_shell_fragment_for_exec(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_XZ_THREADS');
	if($e === false || trim((string) $e) === '') {
		return $cached = '';
	}
	$v = trim((string) $e);
	if($v === '0' || strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'all') === 0 || strcasecmp($v, 'on') === 0) {
		return $cached = ' -T0';
	}
	if(ctype_digit($v)) {
		return $cached = ' -T' . $v;
	}
	return $cached = '';
}

/**
 * Optional zstd {@code -T} for benchmark tarball pipelines ({@code FRACTAL_ZIP_BENCH_ZSTD_THREADS}; also {@code benchmarks/bench_fzbd_vs_baselines.php}).
 * Unset/empty ⇒ {@code -T0} (matches the prior hard-coded parallel default). {@code 0}/{@code auto}/{@code all}/{@code on} ⇒ {@code -T0}.
 * {@code off}/{@code false}/{@code no} ⇒ omit (single-thread zstd compress). Decimal digits ⇒ {@code -TN}.
 */
public static function bench_zstd_thread_shell_fragment_for_exec(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_ZSTD_THREADS');
	if($e === false || trim((string) $e) === '') {
		return $cached = ' -T0';
	}
	$v = trim((string) $e);
	if($v === '0' || strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'all') === 0 || strcasecmp($v, 'on') === 0) {
		return $cached = ' -T0';
	}
	if(strcasecmp($v, 'off') === 0 || strcasecmp($v, 'false') === 0 || strcasecmp($v, 'no') === 0) {
		return $cached = '';
	}
	if(ctype_digit($v)) {
		return $cached = ' -T' . $v;
	}
	return $cached = '';
}

/**
 * Optional {@code zstd} {@code -T} for library outer compress/decompress ({@code outer_zstd_blob}, {@code outer_zstd_decompress_pipe}).
 * {@code FRACTAL_ZIP_ZSTD_THREADS}: unset/empty ⇒ {@code -T0}. {@code off}/{@code false}/{@code no} ⇒ omit. Digits ⇒ {@code -TN}.
 */
public static function library_zstd_thread_shell_fragment_for_exec(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZSTD_THREADS');
	if($e === false || trim((string) $e) === '') {
		return $cached = ' -T0';
	}
	$v = trim((string) $e);
	if($v === '0' || strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'all') === 0 || strcasecmp($v, 'on') === 0) {
		return $cached = ' -T0';
	}
	if(strcasecmp($v, 'off') === 0 || strcasecmp($v, 'false') === 0 || strcasecmp($v, 'no') === 0) {
		return $cached = '';
	}
	if(ctype_digit($v)) {
		return $cached = ' -T' . $v;
	}
	return $cached = '';
}

/**
 * Optional {@code xz} {@code -T} for outer **compress** only. {@code FRACTAL_ZIP_XZ_THREADS}: unset/empty ⇒ omit (legacy single-thread xz output).
 * {@code 0}/{@code auto}/{@code all}/{@code on} ⇒ {@code -T0}. {@code off}/{@code false}/{@code no} ⇒ omit. Digits ⇒ {@code -TN}.
 */
public static function library_xz_compress_thread_shell_fragment_for_exec(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_XZ_THREADS');
	if($e === false || trim((string) $e) === '') {
		return $cached = '';
	}
	$v = trim((string) $e);
	if(strcasecmp($v, 'off') === 0 || strcasecmp($v, 'false') === 0 || strcasecmp($v, 'no') === 0) {
		return $cached = '';
	}
	if($v === '0' || strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'all') === 0 || strcasecmp($v, 'on') === 0) {
		return $cached = ' -T0';
	}
	if(ctype_digit($v)) {
		return $cached = ' -T' . $v;
	}
	return $cached = '';
}

/**
 * Optional {@code xz} {@code -T} for **decompress** (streaming + container paths). Prefer {@code FRACTAL_ZIP_XZ_DECOMPRESS_THREADS};
 * if unset, {@code FRACTAL_ZIP_XZ_THREADS}; if still unset ⇒ {@code -T0}. {@code off}/{@code false}/{@code no} ⇒ omit.
 */
public static function library_xz_decompress_thread_shell_fragment_for_exec(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_XZ_DECOMPRESS_THREADS');
	if($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_XZ_THREADS');
	}
	if($e === false || trim((string) $e) === '') {
		return $cached = ' -T0';
	}
	$v = trim((string) $e);
	if(strcasecmp($v, 'off') === 0 || strcasecmp($v, 'false') === 0 || strcasecmp($v, 'no') === 0) {
		return $cached = '';
	}
	if($v === '0' || strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'all') === 0 || strcasecmp($v, 'on') === 0) {
		return $cached = ' -T0';
	}
	if(ctype_digit($v)) {
		return $cached = ' -T' . $v;
	}
	return $cached = '';
}

/**
 * Optional lz4 {@code -T#} for literal-pac recompress trials ({@code FRACTAL_ZIP_LZ4_THREADS}). Unset ⇒ omit. {@code 0}/{@code auto}/{@code on}/{@code all} ⇒ {@code -T0} (lz4 uses 0 = auto thread count).
 * {@code off}/{@code false}/{@code no} ⇒ omit. Decimal digits ⇒ {@code -TN}.
 */
public static function library_lz4_thread_shell_fragment_for_exec(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LZ4_THREADS');
	if($e === false || trim((string) $e) === '') {
		return $cached = '';
	}
	$v = trim((string) $e);
	if(strcasecmp($v, 'off') === 0 || strcasecmp($v, 'false') === 0 || strcasecmp($v, 'no') === 0) {
		return $cached = '';
	}
	if($v === '0' || strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'all') === 0 || strcasecmp($v, 'on') === 0) {
		return $cached = ' -T0';
	}
	if(ctype_digit($v)) {
		return $cached = ' -T' . $v;
	}
	return $cached = '';
}

/**
 * Optional FreeArc/DArc {@code -mt N} immediately after the {@code arc} executable on **compress** ({@code arc a …}) and **extract** ({@code arc x …}). Reads {@code FRACTAL_ZIP_ARC_MT}, then {@code FRACTAL_ZIP_BENCH_ARC_MT}.
 *
 * @return list<string> e.g. {@code ['-mt','4']} or {@code []}
 */
public static function library_arc_compress_mt_argv_after_exe(): array {
	static $tok = null;
	if($tok !== null) {
		return $tok;
	}
	$e = getenv('FRACTAL_ZIP_ARC_MT');
	if($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_BENCH_ARC_MT');
	}
	if($e === false || trim((string) $e) === '') {
		return $tok = array();
	}
	$v = trim((string) $e);
	if(strcasecmp($v, 'off') === 0 || strcasecmp($v, 'no') === 0) {
		return $tok = array();
	}
	if(strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'on') === 0 || strcasecmp($v, 'all') === 0) {
		return $tok = array('-mt', '0');
	}
	if(ctype_digit($v)) {
		return $tok = array('-mt', (string) $v);
	}
	return $tok = array();
}

/**
 * Space-prefixed escaped fragment for legacy {@code exec($cmd)} FreeArc lines — {@see library_arc_compress_mt_argv_after_exe}.
 */
public static function library_arc_compress_mt_shell_fragment_for_exec(): string {
	$pts = fractal_zip::library_arc_compress_mt_argv_after_exe();
	if($pts === array()) {
		return '';
	}
	$s = '';
	foreach($pts as $p) {
		$s .= ' ' . escapeshellarg((string) $p);
	}
	return $s;
}

/**
 * Cached FRACTAL_ZIP_ARC_* parsing for outer_arc_blob (one getenv/string pass per process after first Arc tournament).
 *
 * @return array{forced: int|null, explicit_outer_list: bool, explicit_methods: list<int>|null, no_dual: bool, min_dual: int, try_mx: bool}
 */
public static function outer_arc_tournament_env_cached(): array {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$forced = null;
	$custom = getenv('FRACTAL_ZIP_ARC_METHOD');
	if($custom !== false && trim((string) $custom) !== '' && ctype_digit(trim((string) $custom))) {
		$forced = max(1, min(9, (int) trim((string) $custom)));
	}
	$listEnv = getenv('FRACTAL_ZIP_ARC_OUTER_METHODS');
	$explicitOuterList = ($listEnv !== false && trim((string) $listEnv) !== '');
	$explicitMethods = null;
	if($explicitOuterList) {
		$methods = array();
		foreach(explode(',', (string) $listEnv) as $piece) {
			$piece = trim((string) $piece);
			if($piece !== '' && ctype_digit($piece)) {
				$methods[] = max(1, min(9, (int) $piece));
			}
		}
		$methods = array_values(array_unique($methods));
		sort($methods);
		if($methods === array()) {
			$methods = array(5);
		}
		$explicitMethods = $methods;
	}
	$noDual = getenv('FRACTAL_ZIP_ARC_OUTER_DUAL_METHODS') === '0';
	$minDualEnv = getenv('FRACTAL_ZIP_ARC_OUTER_DUAL_MIN_BYTES');
	// Below this inner size, only -m5 is tried (fast). Default 64 KiB so Squash-scale unified inners (e.g. ~100–400 KiB) still run
	// the full -m4..9 sweep; raise (legacy 448 KiB = 458752) if Arc wall time dominates on huge folder trees.
	$minDual = ($minDualEnv === false || trim((string) $minDualEnv) === '') ? 65536 : max(0, (int) $minDualEnv);
	$tryMx = true;
	$mxEnv = getenv('FRACTAL_ZIP_ARC_OUTER_MX');
	if($mxEnv !== false && trim((string) $mxEnv) !== '') {
		$mxL = strtolower(trim((string) $mxEnv));
		if($mxL === '0' || $mxL === 'off' || $mxL === 'false' || $mxL === 'no') {
			$tryMx = false;
		}
	}
	return $cached = array(
		'forced' => $forced,
		'explicit_outer_list' => $explicitOuterList,
		'explicit_methods' => $explicitMethods,
		'no_dual' => $noDual,
		'min_dual' => $minDual,
		'try_mx' => $tryMx,
	);
}

/**
 * Coreutils `timeout` path (Unix), cached — avoids repeated `command -v` during FZB4 path-order scoring and outer codec trials.
 */
public static function outer_timeout_executable_cached(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	if(DIRECTORY_SEPARATOR === '\\') {
		return $cached = '';
	}
	$to = trim((string) @shell_exec('command -v timeout 2>/dev/null'));
	return $cached = ($to !== '' ? $to : '');
}

/**
 * When {@code FRACTAL_ZIP_ZPAQ_TIMEOUT_SEC} is set and coreutils {@code timeout} exists (Unix), zpaq {@code add} is wrapped.
 * Cached at first use — {@see outer_zpaq_blob_with_meth_fragment} may run many method trials per inner.
 */
public static function zpaq_outer_exec_timeout_prefix_cached(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	if(DIRECTORY_SEPARATOR === '\\') {
		return $cached = '';
	}
	$to = fractal_zip::outer_timeout_executable_cached();
	if($to === '') {
		return $cached = '';
	}
	$zto = getenv('FRACTAL_ZIP_ZPAQ_TIMEOUT_SEC');
	if($zto === false || trim((string) $zto) === '' || !is_numeric(trim((string) $zto))) {
		return $cached = '';
	}
	$t = max(0.0, (float) trim((string) $zto));
	if($t <= 0) {
		return $cached = '';
	}
	return $cached = fractal_zip::shell_quote_arg_cached($to) . ' -k 1 ' . fractal_zip::shell_quote_arg_cached((string) $t) . ' ';
}

/**
 * Cached interpretation of FRACTAL_ZIP_ZPAQ_OUTER_SWEEP (read on every zpaq outer trial and native zpaq compare).
 *
 * @return array{env_nonempty: bool, explicit_on: ?bool, is_exactly_one: bool}
 */
public static function zpaq_outer_sweep_env_flags_cached(): array {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZPAQ_OUTER_SWEEP');
	if($e === false || trim((string) $e) === '') {
		return $cached = array(
			'env_nonempty' => false,
			'explicit_on' => null,
			'is_exactly_one' => false,
		);
	}
	$t = trim((string) $e);
	$v = strtolower($t);
	$on = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	return $cached = array(
		'env_nonempty' => true,
		'explicit_on' => $on,
		'is_exactly_one' => ($t === '1'),
	);
}

/**
 * Parsed FRACTAL_ZIP_ZPAQ_OUTER_METHODS (comma-separated argv fragments). Empty ⇒ use default -method ladder.
 *
 * @return list<string>
 */
public static function zpaq_outer_methods_argv_fragments_cached(): array {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$list = getenv('FRACTAL_ZIP_ZPAQ_OUTER_METHODS');
	if($list === false || trim((string) $list) === '') {
		return $cached = array();
	}
	$out = array();
	foreach(explode(',', (string) $list) as $piece) {
		$piece = trim($piece);
		if($piece === '') {
			continue;
		}
		$out[] = ($piece[0] === '-') ? (' ' . $piece) : (' -method ' . $piece);
	}
	return $cached = $out;
}

/**
 * Default upper bound for full zpaq method ladder on native folder compare (same default as getenv branch in maybe_folder_zpaq_*).
 */
public static function zpaq_native_full_sweep_max_raw_bytes_cached(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$fullMaxEnv = getenv('FRACTAL_ZIP_ZPAQ_NATIVE_FULL_SWEEP_MAX_RAW_BYTES');
	return $cached = ($fullMaxEnv !== false && trim((string) $fullMaxEnv) !== '')
		? max(0, (int) trim((string) $fullMaxEnv))
		: 4194304;
}

/**
 * escapeshellarg($s) keyed by $s — outer codec paths rebuild command lines per trial; tool paths repeat identically.
 */
public static function shell_quote_arg_cached(string $s): string {
	static $cache = array();
	if(array_key_exists($s, $cache)) {
		return $cache[$s];
	}
	return $cache[$s] = escapeshellarg($s);
}
/** `proc_open` options: single shared array to avoid per-path allocations. */
public static function proc_open_bypass_shell_array(): array {
	static $a = null;
	if($a === null) {
		$a = array('bypass_shell' => true);
	}
	return $a;
}
/**
 * Cached outer trial gate: getenv($envKey) !== '1' for FRACTAL_ZIP_SKIP_* (unset ⇒ allowed).
 */
public static function outer_skip_env_allows(string $envKey): bool {
	static $cache = array();
	if(!array_key_exists($envKey, $cache)) {
		$cache[$envKey] = getenv($envKey) !== '1';
	}
	return $cache[$envKey];
}

/**
 * Fast digest for in-process cache keys only (xxh128 when available, else md5). Not for on-wire identifiers.
 */
public static function hot_string_digest(string $s): string {
	static $useXxh = null;
	static $s0 = null;
	static $d0 = null;
	static $s1 = null;
	static $d1 = null;
	if($s0 === $s) {
		return (string) $d0;
	}
	if($s1 === $s) {
		$ts = $s0;
		$s0 = $s1;
		$s1 = $ts;
		$td = $d0;
		$d0 = $d1;
		$d1 = $td;
		return (string) $d0;
	}
	if($useXxh === null) {
		$useXxh = function_exists('hash') && in_array('xxh128', hash_algos(), true);
	}
	$nd = $useXxh ? hash('xxh128', $s, false) : md5($s);
	$s1 = $s0;
	$d1 = $d0;
	$s0 = $s;
	$d0 = $nd;
	return (string) $nd;
}

/**
 * Run compressor/decompressor that reads stdin and writes binary to stdout. Small payloads: one fwrite + slurp stdout.
 * Large payloads: write $stdinBlob to a temp file and pass it as the sole trailing path argument (zstd/xz/brotli all accept this).
 * When $reuseTmpInPath points at an existing file holding the same bytes as $stdinBlob, that path is used for the temp-input
 * branch instead of writing a fresh file — adaptive_compress materializes it lazily (outer_codec_reuse_tmp_ensure) on first
 * zstd/brotli/xz attempt to avoid N writes per tournament and skip the write when prescreen skips all fast codecs.
 * Caller must unlink $reuseTmpInPath when finished (this function never deletes a reused path).
 *
 * @return string|null raw stdout bytes or null on failure
 */
public static function outer_codec_run_stdin_or_tmpfile_stdout(string $cmdStdin, string $cmdWithTmpPlaceholder, string $stdinBlob, ?string $cwd = null, ?string $reuseTmpInPath = null): ?string {
	if(!function_exists('proc_open')) {
		return null;
	}
	// Two-slot exact-match cache: outer tournaments (brotli/zstd/xz) often retry identical stdin + command patterns.
	// Bounded: only cache when stdin and stdout sizes stay modest (avoids RAM blowups on huge FLAC outers).
	static $c0s = null;
	static $c0t = null;
	static $c0b = null;
	static $c0r = null;
	static $c0o = null;
	static $c1s = null;
	static $c1t = null;
	static $c1b = null;
	static $c1r = null;
	static $c1o = null;
	static $kcMap = array();
	static $kcRing = null;
	static $kcHead = 0;
	static $kcTail = 0;
	static $kcCount = 0;
	$kcLimit = 128;
	$kcDirectMaxIn = 4096;
	$reuseK = ($reuseTmpInPath !== null && $reuseTmpInPath !== '') ? $reuseTmpInPath : '';
	$n = strlen($stdinBlob);
	$maxCacheIn = 16 * 1024 * 1024;
	$maxCacheOut = 64 * 1024 * 1024;
	$keyedCacheKey = null;
	if($n <= $maxCacheIn) {
		if($n <= $kcDirectMaxIn) {
			$keyedCacheKey = $cmdStdin . "\0" . $cmdWithTmpPlaceholder . "\0" . $reuseK . "\0s\0" . $stdinBlob;
		} else {
			$keyedCacheKey = $cmdStdin . "\0" . $cmdWithTmpPlaceholder . "\0" . $reuseK . "\0m\0" . fractal_zip::hot_string_digest($stdinBlob);
		}
		if(isset($kcMap[$keyedCacheKey])) {
			return $kcMap[$keyedCacheKey];
		}
		if($c0s === $cmdStdin && $c0t === $cmdWithTmpPlaceholder && $c0b === $stdinBlob && $c0r === $reuseK) {
			return $c0o;
		}
		if($c1s === $cmdStdin && $c1t === $cmdWithTmpPlaceholder && $c1b === $stdinBlob && $c1r === $reuseK) {
			return $c1o;
		}
	}
	$tmpMin = fractal_zip::outer_codec_temp_input_threshold_bytes();
	static $procOpenDesc;
	static $procOpenOpts;
	if($procOpenDesc === null) {
		$procOpenDesc = array(
			0 => array('pipe', 'r'),
			1 => array('pipe', 'w'),
			2 => array('pipe', 'w'),
		);
		$procOpenOpts = fractal_zip::proc_open_bypass_shell_array();
	}
	$desc = $procOpenDesc;
	$procOpts = $procOpenOpts;
	if($tmpMin === 0 || $n >= $tmpMin) {
		$ownedTmp = false;
		$tmpIn = null;
		if($reuseTmpInPath !== null && $reuseTmpInPath !== '' && is_file($reuseTmpInPath)) {
			$tmpIn = $reuseTmpInPath;
		} else {
			$tmpIn = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzocin_' . substr(md5($cmdStdin . "\0" . $cmdWithTmpPlaceholder . "\0" . (string) ($cwd ?? '') . "\0" . fractal_zip::hot_string_digest($stdinBlob)), 0, 16) . '.bin';
			if(@file_put_contents($tmpIn, $stdinBlob) === false) {
				@unlink($tmpIn);
				return null;
			}
			$ownedTmp = true;
		}
		$qTmpIn = $ownedTmp ? escapeshellarg($tmpIn) : fractal_zip::shell_quote_arg_cached($tmpIn);
		$cmd = str_replace('__FZOC_TMPIN__', $qTmpIn, $cmdWithTmpPlaceholder);
		$out = null;
		$proc = @proc_open($cmd, $desc, $pipes, $cwd, null, $procOpts);
		if(is_resource($proc)) {
			fclose($pipes[0]);
			if(function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[1], 1048576);
				@stream_set_chunk_size($pipes[2], 1048576);
			}
			$out = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
			if(proc_close($proc) !== 0) {
				$out = null;
			}
		}
		if($ownedTmp) {
			@unlink($tmpIn);
		}
		$ret = ($out !== null && $out !== '') ? $out : null;
		if($ret !== null && $n <= $maxCacheIn && strlen($ret) <= $maxCacheOut) {
			$c1s = $c0s;
			$c1t = $c0t;
			$c1b = $c0b;
			$c1r = $c0r;
			$c1o = $c0o;
			$c0s = $cmdStdin;
			$c0t = $cmdWithTmpPlaceholder;
			$c0b = $stdinBlob;
			$c0r = $reuseK;
			$c0o = $ret;
			if($keyedCacheKey !== null) {
				$kcMap[$keyedCacheKey] = $ret;
				if($kcRing === null) {
					$kcRing = array_fill(0, $kcLimit, null);
				}
				if($kcCount === $kcLimit) {
					$evict = $kcRing[$kcHead];
					$kcRing[$kcHead] = null;
					$kcHead = ($kcHead + 1) % $kcLimit;
					$kcCount--;
					if($evict !== null) {
						unset($kcMap[$evict]);
					}
				}
				$kcRing[$kcTail] = $keyedCacheKey;
				$kcTail = ($kcTail + 1) % $kcLimit;
				$kcCount++;
			}
		}
		return $ret;
	}
	$out = null;
	$proc = @proc_open($cmdStdin, $desc, $pipes, $cwd, null, $procOpts);
	if(is_resource($proc)) {
		fwrite($pipes[0], $stdinBlob);
		fclose($pipes[0]);
		if(function_exists('stream_set_chunk_size')) {
			@stream_set_chunk_size($pipes[1], 1048576);
			@stream_set_chunk_size($pipes[2], 1048576);
		}
		$out = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		if(proc_close($proc) !== 0) {
			$out = null;
		}
	}
	$ret = ($out !== null && $out !== '') ? $out : null;
	if($ret !== null && $n <= $maxCacheIn && strlen($ret) <= $maxCacheOut) {
		$c1s = $c0s;
		$c1t = $c0t;
		$c1b = $c0b;
		$c1r = $c0r;
		$c1o = $c0o;
		$c0s = $cmdStdin;
		$c0t = $cmdWithTmpPlaceholder;
		$c0b = $stdinBlob;
		$c0r = $reuseK;
		$c0o = $ret;
		if($keyedCacheKey !== null) {
			$kcMap[$keyedCacheKey] = $ret;
			if($kcRing === null) {
				$kcRing = array_fill(0, $kcLimit, null);
			}
			if($kcCount === $kcLimit) {
				$evict = $kcRing[$kcHead];
				$kcRing[$kcHead] = null;
				$kcHead = ($kcHead + 1) % $kcLimit;
				$kcCount--;
				if($evict !== null) {
					unset($kcMap[$evict]);
				}
			}
			$kcRing[$kcTail] = $keyedCacheKey;
			$kcTail = ($kcTail + 1) % $kcLimit;
			$kcCount++;
		}
	}
	return $ret;
}
/** Cached pipe r/w pieces for {@see proc_open} layouts where the middle entry is a per-path file (`… file … wb`). */
private static function proc_open_reusable_std_pipe_ends() {
	static $pair = null;
	if($pair === null) {
		$pair = array(
			'r' => array('pipe', 'r'),
			'w' => array('pipe', 'w'),
		);
	}
	return $pair;
}
/** Single synthetic member path for fractal-encoded merged folder bytes (FZBF inner blob). */
public const LITERAL_MERGED_SYNTH_MEMBER = '__fz_lmerged.bin';
/** Outer wrapper used for the payload actually written to the `.fzc` after fractal vs lazy size compare. */
public static $last_written_container_codec = null;
/**
 * Outer codec that achieved the smallest wire bytes among candidates for the payload written by the last {@see zip_folder()} (legacy:
 * fractal vs lazy vs literal; stream paths: adaptive outer after inner pick, then optional native Arc/zpaq swap when smaller).
 * Benchmarks should read this for “which outer won on bytes”, not merely the last outer trial mutating {@see $last_outer_codec}.
 */
public static $folder_zip_wire_best_outer_codec = null;
/** Set by zip_folder when the gzip-fast literal-bundle path runs (streaming FZB4 + zlib outer). */
public static $used_folder_gzip_fast = false;
/** Set by zip_folder when the literal FZB4 store-only path runs (raw FZB4 on disk, no outer compression). */
public static $used_folder_literal_fzb4_store = false;
/** Set by zip_folder when FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1 (merged FZB4 inner + adaptive outer; no per-file fractal tree). */
public static $used_folder_unified_stream = false;
/** Set when open_container extracted files via streaming (no full in-memory payload); extract_container must not re-run. */
public static $open_container_streaming_extract_used = false;
/**
 * Unpacked folder raw bytes for J-curve in auto-tune trials; null ⇒ full time weight (same as w=1).
 * @var int|null
 */
public $j_curve_context_raw_bytes = null;
/**
 * First zip-only probe seconds in this auto-tune session (segment/tuning entry); drives zip-axis of J curve when extract is unknown.
 * @var float|null
 */
public $j_curve_context_probe_zip_sec = null;
/** FZRC1 blobs for FZB literal mode 10 after stripping FZBD in decode_container_payload; empty when not applicable. */
public $fzb_raster_canon_decode_table = array();
/**
 * Root strlen($string) at recursion_counter===0 in recursive_fractal_substring; used to clamp depth on huge blobs.
 * @var int
 */
public $recursive_fractal_entry_strlen = 0;

/**
 * Env FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=1: zip_folder writes a raw FZB4 bundle as the .fzc (mode 0 literals, no per-file transform search, no gzip/deflate outer).
 * adaptive_decompress passes FZB4 through; used by benchmarks under a tight per-case deadline on very large trees.
 */
public static function folder_literal_fzb4_store_only_requested(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$v = getenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
	if($v === false || trim((string) $v) === '') {
		return $cached = false;
	}
	$v = strtolower(trim((string) $v));
	return $cached = ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

/**
 * Stream-first zip_folder (unified path): target pipeline is three independent decisions — (1) semantic decode/merge for
 * known types (unwrap, raster, multi-FLAC FZCD, …), (2) lossless literal transforms + layout when they shrink the inner
 * (FZB*, FZWS, …), (3) outer codec via adaptive_compress vs raw escaped container. Multipass range substitution still
 * lives on the legacy fractal tree path. Multi-file MPQ-family or mixed-extension folders use staged outers by default
 * (FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER auto): fast gzip+zstd+brotli tier, then full adaptive only if a semantic inner
 * beats raw on that tier — single-file corpora skip staging to preserve byte wins. FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS=`1`/`0`
 * forces FZBF on/off. FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS_AUTO_MIN_RAW_BYTES sets a minimum merged-raw size (bytes) when you want
 * auto FZBF without the built-in defaults. When both MULTIPASS and AUTO_MIN are unset, FZBF is tried when merged raw (any member count) is
 * ≥768 KiB and gzip-1 probe ratio is ≤ FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS_AUTO_MAX_GZIP1_RATIO (default 0.11), skipping dense random blobs.
 * With AUTO_MIN set only, the gzip ratio gate is off unless AUTO_MAX_GZIP1_RATIO is also set. Tune FRACTAL_ZIP_UNIFIED_LITERAL_SYNTH_MAX_BYTES
 * (0 = unlimited; default 64 MiB for explicit `=1` / auto runs). Nested FZBF zip runs multipass equivalence passes until no gain; cap wall time with
 * FRACTAL_ZIP_LITERAL_SYNTH_MULTIPASS_MAX_SECONDS (unset = no cap for synth). Optional segment tournament: FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID=1,
 * FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID_MAX_MERGED_BYTES (default 384 KiB).
 * Large-blob fractal substring search: FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_* / FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_* (enumeration caps, top-K, window);
 * FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_DEPTH_MIN_BYTES + FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_MAX_RECURSION_DEPTH (clamp recursion depth on huge roots).
 *
 * Collect files, build one or more literal inners (FZCD when applicable, FZBM merged+index for multi-file raw concat,
 * else FZB4/5/6/FZBD from encode_literal_bundle_payload),
 * pick smallest adaptive-compressed result vs raw escaped-per-file; optional **raw native Arc** passthrough when `arc` folder
 * bytes beat that wire size (see folder_native_freearc_compare_*). When FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP is on (default), phase-3 may
 * compare **both** disk-escaped and deep-unwrapped-escaped raw inners (FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER, default on) and keep the smaller **on-wire**
 * size including the FZG* peel-restore trailer when applicable.
 * FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=0 forces disk-only in that tier. When raw wins from the unwrapped variant, FZG* peel-restore trailers
 * preserve exact on-disk bytes on extract. Env FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 forces legacy zip_folder.
 */
public static function unified_literal_multipass_gzip1_ratio_probe_slice(string $merged): string {
	$merged = (string) $merged;
	$n = strlen($merged);
	if($n === 0) {
		return '';
	}
	$cap = 2 * 1024 * 1024;
	if($n <= $cap) {
		return $merged;
	}
	$half = (int) ($cap / 2);
	return substr($merged, 0, $half) . substr($merged, -$half);
}

/**
 * @param string $mergedForAutoRatioProbe Concatenated member bytes (path-sorted); required for default auto gating and optional ratio env.
 */
public static function unified_literal_multipass_synth_gate_env_cached(): array {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = array(
		'multipass' => getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS'),
		'auto_min' => getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS_AUTO_MIN_RAW_BYTES'),
		'ratio_max' => getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS_AUTO_MAX_GZIP1_RATIO'),
	);
}

public static function unified_literal_multipass_synth_enabled_for_raw_bytes(int $totalRawBytes, string $mergedForAutoRatioProbe = ''): bool {
	$ge = fractal_zip::unified_literal_multipass_synth_gate_env_cached();
	$v = $ge['multipass'];
	if($v !== false && trim((string) $v) !== '') {
		$vl = strtolower(trim((string) $v));
		if($vl === '1' || $vl === 'true' || $vl === 'yes' || $vl === 'on') {
			return true;
		}
		if($vl === '0' || $vl === 'off' || $vl === 'false' || $vl === 'no') {
			return false;
		}
	}
	$e = $ge['auto_min'];
	$autoMinExplicit = ($e !== false && trim((string) $e) !== '');
	$minB = $autoMinExplicit ? max(0, (int) $e) : 0;
	$ratioEnv = $ge['ratio_max'];
	$ratioMaxExplicit = ($ratioEnv !== false && trim((string) $ratioEnv) !== '');
	$ratioCap = $ratioMaxExplicit ? max(0.0, min(1.0, (float) $ratioEnv)) : null;
	if($autoMinExplicit) {
		if($minB <= 0 || $totalRawBytes < $minB) {
			return false;
		}
		if($ratioCap !== null) {
			if($mergedForAutoRatioProbe === '') {
				return false;
			}
			$probe = fractal_zip::unified_literal_multipass_gzip1_ratio_probe_slice($mergedForAutoRatioProbe);
			if($probe === '') {
				return false;
			}
			$gz = @gzdeflate($probe, 1);
			if(!is_string($gz) || $gz === '') {
				return false;
			}
			$ratio = strlen($gz) / strlen($probe);
			return $ratio <= $ratioCap;
		}
		return true;
	}
	$defaultMin = 768 * 1024;
	$defaultRatio = 0.11;
	if($totalRawBytes < $defaultMin) {
		return false;
	}
	if($mergedForAutoRatioProbe === '') {
		return false;
	}
	$probe = fractal_zip::unified_literal_multipass_gzip1_ratio_probe_slice($mergedForAutoRatioProbe);
	if($probe === '') {
		return false;
	}
	$gz = @gzdeflate($probe, 1);
	if(!is_string($gz) || $gz === '') {
		return false;
	}
	$ratio = strlen($gz) / strlen($probe);
	$lim = $ratioCap !== null ? $ratioCap : $defaultRatio;
	return $ratio <= $lim;
}

public static function unified_literal_multipass_synth_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS') === '1';
}

/**
 * Max merged raw size (bytes) for FZBF (fractal-on-merged) trial. 0 = unlimited. Default 64 MiB when env unset.
 */
public static function unified_literal_synth_max_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_UNIFIED_LITERAL_SYNTH_MAX_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 64 * 1024 * 1024;
	}
	$v = (int) trim((string) $e);
	return $cached = ($v <= 0 ? 0 : min(2147483647, $v));
}

public static function folder_unified_stream_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$v = getenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM');
	if($v === false || trim((string) $v) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $v));
	if($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
		return $cached = false;
	}
	return $cached = true;
}

/**
 * After unified-stream pick, compare wire size to a **native FreeArc folder archive** (same `arc a -m5 -ep1 -y` tree pack the
 * benchmarks use for min-ext; modest raw totals also try `-m7` and keep the smaller). When that archive is strictly smaller than the fractal `.fzc` body, the file stores **raw
 * FreeArc bytes** (same size as min-ext Arc) — no extra header. `open_container` distinguishes this from legacy **outer Arc
 * wrapping a single member `i`** by probing with `arc x`: one top-level file named `i` ⇒ fractal path; otherwise ⇒ native
 * folder extract. **Legacy `FZFA\x01` + arc** from older builds is still decoded. FRACTAL_ZIP_FOLDER_NATIVE_ARC_COMPARE=`0`
 * disables. FRACTAL_ZIP_FOLDER_NATIVE_ARC_MAX_RAW_BYTES caps merged raw bytes (default 100 MiB; **0** = no cap). Skipped on
 * Windows or when `arc` is missing.
 */
public static function folder_native_freearc_compare_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_FOLDER_NATIVE_ARC_COMPARE') !== '0';
}

/**
 * @return int 0 means no size cap (always try when enabled and arc exists).
 */
public static function folder_native_freearc_compare_max_raw_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_NATIVE_ARC_MAX_RAW_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		$v = (int) trim((string) $e);
		return $cached = max(0, min(2147483647, $v));
	}
	return $cached = 100 * 1024 * 1024;
}

/**
 * Native folder Arc compare: when total raw bytes are ≤ this value and positive, try <code>arc a -m7 -ep1</code> after <code>-m5</code>
 * on the same temp tree and keep the smaller archive. <code>0</code> disables the extra trial. Override with
 * <code>FRACTAL_ZIP_FOLDER_NATIVE_ARC_DUAL_METHOD_MAX_RAW_BYTES</code>.
 */
public static function folder_native_freearc_dual_method_max_raw_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_NATIVE_ARC_DUAL_METHOD_MAX_RAW_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 2097152 : max(0, (int) trim((string) $e));
}

/**
 * Max merged raw bytes for the **adaptive-zpaq** native-folder compare ({@see maybe_folder_zpaq_native_smaller_than_fzc} when
 * {@see fractal_zip::outer_force_codec_is_zpaq()} is false). Avoids a second full-tree <code>zpaq add</code> on huge corpora;
 * {@code FRACTAL_ZIP_FORCE_OUTER=zpaq} ignores this cap. Unset ⇒ 8388608 (8 MiB). {@code 0} = no cap.
 */
public static function folder_native_zpaq_compare_max_raw_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 8388608;
	}
	$v = (int) trim((string) $e);
	return $cached = max(0, min(2147483647, $v));
}

/**
 * Lowercased, trimmed FRACTAL_ZIP_FOLDER_LITERAL_INNER_PICK, or empty when unset. Cached — unified stream may filter variants often.
 *
 * @return string
 */
public static function folder_literal_inner_pick_normalized_cached(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_LITERAL_INNER_PICK');
	return $cached = ($e !== false && trim((string) $e) !== '') ? strtolower(trim((string) $e)) : '';
}

/**
 * Folder unified stream: which literal inners compete vs raw after adaptive outer.
 * Default **bytes-first**: all collected variants (FZCD, FZBM, FZB, …). FZCD-only was faster but could miss a smaller outer on
 * merged raw (e.g. FLAC album slices: FZBM + brotli vs FZCD + 7z — test_files59_sample).
 * Set FRACTAL_ZIP_FOLDER_LITERAL_INNER_PICK=fzcd to compare **only** FZCD when present (legacy).
 *
 * @param list<array{inner: string, tag: string}> $variants
 * @return list<array{inner: string, tag: string}>
 */
public static function folder_literal_variants_for_unified_stream(array $variants): array {
	if($variants === array()) {
		return $variants;
	}
	$el = fractal_zip::folder_literal_inner_pick_normalized_cached();
	if($el === 'fzcd') {
		$only = array();
		foreach($variants as $v) {
			if(is_array($v) && isset($v['tag']) && $v['tag'] === 'fzcd') {
				$only[] = $v;
			}
		}
		return $only !== array() ? $only : $variants;
	}
	return $variants;
}

/**
 * Auto heuristic for staged literal-folder outers: multi-file MPQ-family trees (several .SC2Replay, .w3replay, …) or any
 * folder with ≥2 distinct file extensions; homogeneous many-file single-extension trees ({@see folder_staged_literal_outer_homogeneous_many_files_eligible});
 * optional large single-file trees ({@see folder_staged_literal_outer_single_file_min_raw_bytes_threshold}). Tiny single-file corpora stay unstaged so full
 * adaptive_compress can maximize byte wins when semantic unwrap helps one archive.
 *
 * @param array<string,string> $rawFilesByPath
 */
public static function folder_staged_literal_outer_auto_heuristic(array $rawFilesByPath): bool {
	$n = sizeof($rawFilesByPath);
	if($n <= 0) {
		return false;
	}
	if($n === 1) {
		$sumOne = 0;
		foreach($rawFilesByPath as $b) {
			$sumOne += strlen((string) $b);
		}
		$sft = fractal_zip::folder_staged_literal_outer_single_file_min_raw_bytes_threshold();

		return $sft > 0 && $sumOne >= $sft;
	}
	$exts = array();
	foreach(array_keys($rawFilesByPath) as $path) {
		$ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
		$exts[$ext] = true;
	}
	unset($exts['']);
	$uniq = array_keys($exts);
	if(sizeof($uniq) >= 2) {
		return true;
	}
	if(fractal_zip::folder_staged_literal_outer_homogeneous_many_files_eligible($rawFilesByPath)) {
		return true;
	}
	$only = sizeof($uniq) === 1 ? $uniq[0] : '';
	$mpqFamily = array('sc2replay', 'w3replay', 'sc2map', 'w3x', 'w3m', 'mpq');
	if($only !== '' && in_array($only, $mpqFamily, true)) {
		return true;
	}
	return false;
}

/**
 * Staged literal outer: fast gzip+zstd+brotli tier, then full adaptive only if a semantic inner beats raw on that tier.
 * FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER: unset = auto (heuristic), 1 = always, 0 = off (legacy all-full-adaptive).
 *
 * @param array<string,string> $rawFilesByPath
 */
public static function folder_staged_literal_outer_enabled(array $rawFilesByPath): bool {
	static $mode = null;
	if($mode === null) {
		$e = getenv('FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER');
		if($e !== false && trim((string) $e) !== '') {
			$el = strtolower(trim((string) $e));
			if($el === '0' || $el === 'off' || $el === 'false' || $el === 'no') {
				$mode = 'off';
			} elseif($el === '1' || $el === 'on' || $el === 'true' || $el === 'yes') {
				$mode = 'on';
			} else {
				$mode = 'heuristic';
			}
		} else {
			$mode = 'heuristic';
		}
	}
	if($mode === 'off') {
		return false;
	}
	if($mode === 'on') {
		return true;
	}
	return fractal_zip::folder_staged_literal_outer_auto_heuristic($rawFilesByPath);
}

/**
 * Staged outer (gzip+zstd+brotli fast tier) can mis-rank FZB vs FZBM vs raw vs FZCD when full adaptive_compress would pick a
 * different winner (7z/arc/xz, brotli Q11, …). Above this threshold, `choose_smallest_adaptive_literal_inner_or_raw_escaped`
 * still runs a **full adaptive_compress** on the raw baseline and on the staged literal winner and prefers raw when it is
 * smaller or tied (never pick a semantic inner larger than raw on the true outer). For folders whose **total unpacked raw
 * bytes** are below this threshold, run the same full outer tournament as unstaged mode so bytes-first results transfer to
 * typical small trees (mixed extensions, micro-corpora). Default 16 MiB. Set FRACTAL_ZIP_STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES=0 to **always** use full outer
 * (disables the fast tier path entirely). Set to 1 to almost always use staged fast path (legacy stress only).
 * Homogeneous multi-file trees ({@see folder_staged_literal_outer_homogeneous_many_files_eligible}) may use the staged fast tier when merged raw ≥
 * {@see staged_literal_homogeneous_fast_outer_min_raw_bytes} (default 512 KiB) even if below this 16 MiB floor.
 * Large single-file trees ({@see folder_staged_literal_outer_single_file_min_raw_bytes_threshold}) use the same lower floor when staged is enabled by heuristic.
 */
public static function staged_literal_fast_outer_min_raw_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 16777216;
	}
	$v = (int) trim((string) $e);
	if($v <= 0) {
		return $cached = 0;
	}
	return $cached = min(2147483647, $v);
}

/**
 * Large homogeneous trees (many files, one extension): allow {@see folder_staged_literal_outer_auto_heuristic} and a lower
 * {@see staged_literal_fast_outer_min_raw_bytes} floor so {@see choose_smallest_adaptive_literal_inner_or_raw_escaped} can use
 * the staged fast tier + full rescoring (same safety net as mixed-extension staged mode). Unset ⇒ {@code 2048}; {@code 0} disables.
 */
public static function folder_staged_literal_outer_homogeneous_min_files_threshold(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER_HOMOGENEOUS_MIN_FILES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 2048;
	}
	$v = (int) trim((string) $e);

	return $cached = max(0, min(2147483647, $v));
}

/**
 * With {@see folder_staged_literal_outer_homogeneous_many_files_eligible}, merged raw may exceed this floor (instead of the
 * global {@see staged_literal_fast_outer_min_raw_bytes} 16 MiB default) before staged fast tier applies. Unset ⇒ 524288 (512 KiB);
 * {@code 0} disables the homogeneous exception (only the global floor applies).
 */
public static function staged_literal_homogeneous_fast_outer_min_raw_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_STAGED_LITERAL_HOMOGENEOUS_FAST_OUTER_MIN_RAW_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 524288;
	}
	$v = (int) trim((string) $e);
	if($v <= 0) {
		return $cached = 0;
	}

	return $cached = min(2147483647, $v);
}

/**
 * True when the tree has ≥{@see folder_staged_literal_outer_homogeneous_min_files_threshold} members (when threshold &gt; 0)
 * and exactly one non-empty extension — typical “many tiny files, same type” corpora that otherwise skipped staged mode.
 *
 * @param array<string,string> $rawFilesByPath
 */
public static function folder_staged_literal_outer_homogeneous_many_files_eligible(array $rawFilesByPath): bool {
	$thr = fractal_zip::folder_staged_literal_outer_homogeneous_min_files_threshold();
	if($thr <= 0) {
		return false;
	}
	$n = sizeof($rawFilesByPath);
	if($n < $thr || $n <= 1) {
		return false;
	}
	$exts = array();
	foreach(array_keys($rawFilesByPath) as $path) {
		$ext = strtolower(pathinfo((string) $path, PATHINFO_EXTENSION));
		$exts[$ext] = true;
	}
	unset($exts['']);

	return sizeof($exts) === 1;
}

/**
 * Single-member folder (one logical file): allow staged fast tier when merged raw ≥ this threshold. Unset ⇒ 524288 (512 KiB);
 * {@code 0} disables (legacy: single-file paths never used staged heuristic).
 */
public static function folder_staged_literal_outer_single_file_min_raw_bytes_threshold(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER_SINGLE_FILE_MIN_RAW_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 524288;
	}
	$v = (int) trim((string) $e);

	return $cached = max(0, min(2147483647, $v));
}

/**
 * FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_BYTES. Unset ⇒ 1.
 */
public static function staged_literal_deep_min_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_BYTES');
	$cached = ($e === false || trim((string) $e) === '') ? 1 : max(0, (int) $e);
	return $cached;
}

/**
 * FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_FAST_MARGIN_BYTES. Unset ⇒ -1 (caller uses scaled default from fast-tier raw size).
 */
public static function staged_literal_deep_fast_margin_bytes_or_negative(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_FAST_MARGIN_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = -1;
	}
	return $cached = (int) trim((string) $e);
}

/**
 * Whole-stream reversible preprocess on FZB4 inner before outer adaptive_compress. FZWS v1: delta or xor-adjacent on full blob.
 * FRACTAL_ZIP_WHOLE_STREAM_FZWS=0 disables. FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES caps input (default 128 MiB; 0 = unlimited).
 */
public static function whole_stream_fzws_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$v = getenv('FRACTAL_ZIP_WHOLE_STREAM_FZWS');
	if($v === false || trim((string) $v) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $v));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

public static function whole_stream_fzws_max_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES');
	if($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 128 * 1024 * 1024;
	}
	$v = (int) trim((string) $e);
	return $cached = ($v <= 0 ? 0 : min(2147483647, $v));
}

/**
 * When {@see outer_force_codec_is_zpaq()} and gzip‑1 picked an FZWS wrap, re-check wire size with zpaq (single
 * {@see zpaq_add_method_argv_fragment} trial) for inners ≤ this many bytes and keep the plain inner if zpaq does not shrink
 * the wrap (gzip often mis-ranks vs zpaq). {@code 0} disables the veto. Unset ⇒ 65536.
 */
public static function fzws_zpaq_veto_max_inner_bytes(): int {
	if(!self::outer_force_codec_is_zpaq()) {
		return 0;
	}
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FZWS_ZPAQ_VETO_MAX_INNER_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 65536;
	}
	return $cached = max(0, (int) trim((string) $e));
}

/** FRACTAL_ZIP_SPEED=1: fast preset (reduced outer trials, tighter literal gzip-9 cap when non-BMP env unset, …). */
public static function speed_mode_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_SPEED') === '1';
}

/**
 * FRACTAL_ZIP_ULTRA=1: bytes-first “try hardest” mode (companion env defaults in `fractal_zip_cli_apply_ultra_env_defaults()`):
 * unlimited fractal multipass passes while each pass improves, no multipass wall unless
 * `FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS` is set; marginal multipass candidates (lower gate / threshold); outer tournament
 * does not gzip-margin early-stop away from 7z/arc/zpaq; zstd-22 + brotli-11 + full huge brotli; `FRACTAL_ZIP_DEEP=1` for remaining deep hooks.
 */
public static function ultra_compression_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_ULTRA') === '1';
}

/** FRACTAL_ZIP_DEEP=1: allow expensive paths that speed mode normally skips (paired with speed_mode_enabled()). */
public static function deep_mode_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DEEP') === '1';
}

/** FRACTAL_ZIP_ALLOW_OUTER_EXPANSION=1: keep best outer even when it does not shrink the inner. */
public static function allow_outer_expansion(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION') === '1';
}

/** FRACTAL_ZIP_FORCE_OUTER=gzip: gzip-only outer path. */
public static function outer_force_codec_is_gzip(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_FORCE_OUTER') === 'gzip';
}

/** FRACTAL_ZIP_FORCE_OUTER=zpaq: zpaq-only outer path (bench / A–B); see <code>try_force_outer_zpaq_payload</code>. */
public static function outer_force_codec_is_zpaq(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_FORCE_OUTER') === 'zpaq';
}

/**
 * One-line label for the winning outer in benches (env + codec family; same spirit as Squash B-best’s plugin/codec/level).
 * Does not list every 7z -m0 trial — summarizes tournament class and the env knobs in play.
 *
 * @param string|null $zpaq_method_digits When set (digits only), used for the <code>zpaq/</code> level instead of
 * {@see fractal_zip::$last_zpaq_method} (bench callers snapshot this before <code>open_container</code> clears statics).
 */
public static function outer_bench_caption_for_codec($codec, ?string $zpaq_method_digits = null): string {
	$c = is_string($codec) ? strtolower(trim($codec)) : '';
	if($c === '' || $c === '—' || $c === '0') {
		return '—';
	}
	$mod = array();
	if(getenv('FRACTAL_ZIP_ULTRA') === '1') {
		$mod[] = 'ultra';
	}
	if(getenv('FRACTAL_ZIP_DEEP') === '1') {
		$mod[] = 'DEEP';
	}
	if(fractal_zip::outer_prescreen_gate_disabled()) {
		$mod[] = 'noPre';
	}
	$modStr = $mod === [] ? '' : (' +' . implode('+', $mod));
	if($c === 'store') {
		return 'store' . $modStr;
	}
	$main = '';
	switch($c) {
		case 'gzip':
			$main = 'gzip/deflate-9';
			break;
		case 'zstd':
			$zl = getenv('FRACTAL_ZIP_ZSTD_LEVEL');
			if($zl !== false && trim((string) $zl) !== '') {
				$main = 'zstd/L' . trim((string) $zl);
			} else {
				$main = fractal_zip::speed_mode_enabled() ? 'zstd/L9+auto' : 'zstd/L16+text/tier';
			}
			break;
		case 'brotli':
			$bq = getenv('FRACTAL_ZIP_BROTLI_QUALITY');
			$lg = getenv('FRACTAL_ZIP_BROTLI_LGWIN');
			$brParts = array('brotli');
			if($bq !== false && trim((string) $bq) !== '') {
				$brParts[] = 'q' . trim((string) $bq);
			} else {
				$brParts[] = 'q10+auto11';
			}
			if($lg !== false && trim((string) $lg) !== '') {
				$brParts[] = 'w' . trim((string) $lg);
			}
			$main = implode('/', $brParts);
			$bh = getenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
			if($bh !== false && trim((string) $bh) !== '') {
				$main .= ' huge=' . trim((string) $bh);
			}
			break;
		case '7z':
			$main = '7z/lzma2+ppmd+m0-sweep+mx-9';
			if (getenv('FRACTAL_ZIP_SKIP_PPMD') === '1') {
				$main .= ' +noPpmd';
			}
			break;
		case 'arc':
			$am = getenv('FRACTAL_ZIP_ARC_METHOD');
			$ad = getenv('FRACTAL_ZIP_ARC_OUTER_METHODS');
			if($am !== false && trim((string) $am) !== '') {
				$main = 'arc/m' . trim((string) $am);
			} elseif($ad !== false && trim((string) $ad) !== '') {
				$main = 'arc/[' . trim((string) $ad) . ']';
			} else {
				$main = 'arc/m4-9+mx+default';
			}
			$du = getenv('FRACTAL_ZIP_ARC_OUTER_DUAL_MIN_BYTES');
			if($du !== false && trim((string) $du) !== '') {
				$main .= ' dualMin=' . trim((string) $du) . 'B';
			}
			break;
		case 'zpaq':
			$zpaqMethod = '5';
			$useLast = $zpaq_method_digits;
			if(!is_string($useLast) || $useLast === '' || preg_match('/^[0-9]+$/', $useLast) !== 1) {
				$useLast = fractal_zip::$last_zpaq_method;
			}
			if(is_string($useLast) && $useLast !== '' && preg_match('/^[0-9]+$/', $useLast) === 1) {
				$zpaqMethod = (string) $useLast;
			} else {
				$frag = ltrim((string) fractal_zip::zpaq_add_method_argv_fragment());
				if($frag !== '' && preg_match('/(?:^|\\s)-method\\s+([0-9]+)/i', $frag, $m) === 1) {
					$zpaqMethod = (string) $m[1];
				} elseif($frag !== '' && preg_match('/^([0-9]+)$/', trim($frag), $m) === 1) {
					$zpaqMethod = (string) $m[1];
				}
			}
			$main = 'zpaq/' . $zpaqMethod;
			if(fractal_zip::zpaq_outer_sweep_env_flags_cached()['is_exactly_one']) {
				$main .= '+sweep';
			}
			$maxZ = getenv('FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES');
			if($maxZ !== false && trim((string) $maxZ) !== '') {
				$main .= '+cap' . trim((string) $maxZ) . 'B';
			}
			break;
		case 'xz':
			$xl = getenv('FRACTAL_ZIP_XZ_LEVEL');
			$main = 'xz' . (($xl !== false && trim((string) $xl) !== '') ? '/L' . trim((string) $xl) : '/default');
			break;
		default:
			$main = $c;
			break;
	}
	return $main . $modStr;
}

/** FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN=1: outer_likely_incompressible is always false. */
public static function outer_prescreen_gate_disabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN') === '1';
}

public static function outer_prescreen_min_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_MIN_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 16384 : max(0, (int) $e);
}

public static function outer_prescreen_sample_target_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_SAMPLE_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 4096 : max(512, min(32768, (int) $e));
}

public static function outer_prescreen_sample_min_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_SAMPLE_MIN_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 1024 : max(256, min(8192, (int) $e));
}

public static function outer_prescreen_unique_min(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_UNIQUE_MIN');
	return $cached = ($e === false || trim((string) $e) === '') ? 245 : max(0, min(256, (int) $e));
}

public static function outer_prescreen_repeat_max(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_REPEAT_MAX');
	return $cached = ($e === false || trim((string) $e) === '') ? 0.01 : max(0.0, min(1.0, (float) $e));
}

/** FRACTAL_ZIP_DISABLE_OUTER_TEXTLIKE=1: outer_likely_textlike always true (no textlike gating). */
public static function outer_textlike_probe_disabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_OUTER_TEXTLIKE') === '1';
}

public static function outer_textlike_min_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_MIN_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 4096 : max(0, (int) $e);
}

public static function outer_textlike_sample_target_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_SAMPLE_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 4096 : max(512, min(32768, (int) $e));
}

public static function outer_textlike_min_ratio(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_MIN_RATIO');
	return $cached = ($e === false || trim((string) $e) === '') ? 0.90 : max(0.0, min(1.0, (float) $e));
}

public static function outer_textlike_max_zero_ratio(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_MAX_ZERO_RATIO');
	return $cached = ($e === false || trim((string) $e) === '') ? 0.01 : max(0.0, min(1.0, (float) $e));
}

public static function speed_try_7z_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_SPEED_TRY_7Z') === '1';
}

public static function speed_try_arc_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_SPEED_TRY_ARC') === '1';
}

public static function speed_try_brotli_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_SPEED_TRY_BROTLI') === '1';
}

public static function speed_skip_zstd_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_SPEED_SKIP_ZSTD') === '1';
}

public static function outer_early_stop_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_EARLY_STOP_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 32 : max(0, (int) $e);
}

public static function outer_early_stop_pct(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_EARLY_STOP_PCT');
	return $cached = ($e === false || trim((string) $e) === '') ? 0.01 : max(0.0, min(1.0, (float) $e));
}

public static function outer_early_stop_dynamic_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC');
	return $cached = ($e === false || trim((string) $e) === '') ? true : ((int) $e !== 0);
}

/**
 * Squash-style layered outer probes before expensive 7z/arc/zpaq ordering; {@code FRACTAL_ZIP_OUTER_PREDICT=0} disables.
 */
public static function outer_prediction_runtime_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_OUTER_PREDICT');
	if($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));

	return !($v === '0' || $v === 'false' || $v === 'off' || $v === 'no');
}

/** Minimum inner size (bytes) to run prediction; 0 = always (subject to early-stop gate). */
public static function outer_prediction_min_inner_bytes(): int {
	$e = getenv('FRACTAL_ZIP_OUTER_PREDICT_MIN_INNER_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}

	return 512;
}

public static function outer_prediction_probe_max_bytes(): int {
	$e = getenv('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return max(4096, (int) trim((string) $e));
	}
	// Bytes-first default samples up to 8 MiB; speed preset caps probe unless overridden — fewer subprocess bytes/time on huge inners.
	if(self::speed_mode_enabled()) {
		return 2 * 1024 * 1024;
	}

	return 8 * 1024 * 1024;
}

public static function outer_prediction_timeout_sec(): float {
	$e = getenv('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC');
	if($e !== false && trim((string) $e) !== '' && is_numeric($e)) {
		return max(1.0, (float) $e);
	}
	// Speed mode: shorter default so speculative prediction join ({@see speculative_outer_prediction_finalize}) stays bounded;
	// set FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC explicitly when layered probes need more wall time.
	if(self::speed_mode_enabled()) {
		return 12.0;
	}

	return 30.0;
}

/**
 * Layer‑3 high‑tier probe on the L2 winner (e.g. 7z mx9, zpaq high method). Unset ⇒ **skip** L3: {@see fractal_zip_outer_predict_mapped_winner}
 * still reads {@code layered.winner_family} from L2 — high tier does not change the mapped family string in {@see benchmarks/predict_outer_layered_probe.php}.
 * Skipping saves substantial wall time on those subprocess calls. Set {@code 1}/{@code on} for full three‑tier probes (benchmarks/predict_outer_benchmarks.php enables this by default).
 */
public static function outer_prediction_layer3_high_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH');
	if($e === false || trim((string) $e) === '') {
		return $cached = false;
	}
	$v = strtolower(trim((string) $e));

	return $cached = ($v === '1' || $v === 'true' || $v === 'yes' || $v === 'on');
}

/** One line to stderr for A/B vs actual outer: {@code FRACTAL_ZIP_OUTER_PREDICT_DEBUG=1}. */
public static function outer_prediction_debug_enabled(): bool {
	return getenv('FRACTAL_ZIP_OUTER_PREDICT_DEBUG') === '1';
}

/**
 * Debug line when {@see decode_container_payload} reaches legacy {@code unserialize} and shape does not match {@code array(map, fractal)}.
 *
 * @param mixed $legacyUnserialized result of {@code unserialize($contents)}
 */
public static function container_payload_unknown_format_diag(string $contents, $legacyUnserialized): string {
	$n = strlen($contents);
	$peek = min(32, $n);
	$hex = bin2hex(substr($contents, 0, $peek));
	$magic4 = ($n >= 4) ? substr($contents, 0, 4) : substr($contents, 0, $n);
	$magicEsc = preg_replace('/[^\x20-\x7e]/', '.', $magic4);
	if(is_array($legacyUnserialized)) {
		$leg = 'array(count=' . count($legacyUnserialized) . ')';
	} elseif(is_object($legacyUnserialized)) {
		$leg = 'object(' . get_class($legacyUnserialized) . ')';
	} elseif($legacyUnserialized === false && $contents !== 'b:0;' && substr($contents, 0, 2) !== 'b:') {
		$leg = 'unserialize_false_or_failed';
	} else {
		$leg = gettype($legacyUnserialized);
	}

	return sprintf('len=%d magic4=%s hex32=%s legacy=%s', $n, $magicEsc, $hex, $leg);
}

/**
 * Outer trial budget after gzip baseline: {@code 0} = maximum (tiny payloads — full slow sweep + rich zpaq ladder);
 * {@code 1} = normal; {@code 2} = minimal (speed mode, or huge near‑incompressible inner when predictor favors very slow outers).
 * Not a gate on layered probes — those run first in {@see adaptive_compress}; ratchet only trims expensive trials afterward.
 */
public static function outer_compression_ratchet_level(int $innerLen, int $gzLen, bool $speedMode, ?string $predictedOuterFamily): int {
	if($speedMode) {
		return 2;
	}
	$tiny = fractal_zip::outer_ratchet_tiny_max_inner_bytes();
	if($innerLen > 0 && $innerLen <= $tiny) {
		return 0;
	}
	$gr = ($innerLen > 0 && $gzLen !== PHP_INT_MAX && $gzLen > 0) ? ((float) $gzLen / (float) $innerLen) : 0.0;
	$hugeMin = fractal_zip::outer_ratchet_huge_compress_min_inner_bytes();
	$cut = fractal_zip::outer_ratchet_incompressible_gzip_ratio();
	if($innerLen >= $hugeMin && $gr >= $cut && $predictedOuterFamily === 'zpaq') {
		return 2;
	}

	return 1;
}

/** Max inner size (bytes) for ratchet level {@code 0} — full slow outer exploration. Default 64 KiB; {@code 0} disables tiny‑max mode. */
public static function outer_ratchet_tiny_max_inner_bytes(): int {
	$e = getenv('FRACTAL_ZIP_OUTER_RATCHET_TINY_MAX_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}

	return 65536;
}

/** Inner size (bytes) above which near‑incompressible gzip + zpaq‑family prediction may ratchet to minimal slow trials. Default 12 MiB. */
public static function outer_ratchet_huge_compress_min_inner_bytes(): int {
	$e = getenv('FRACTAL_ZIP_OUTER_RATCHET_HUGE_MIN_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}

	return 12582912;
}

/** Gzip length / inner length ratio treated as “barely compressible” for ratchet‑2 slow‑outer trimming on huge inners. Default {@code 0.97}. */
public static function outer_ratchet_incompressible_gzip_ratio(): float {
	$e = getenv('FRACTAL_ZIP_OUTER_RATCHET_INCOMPRESSIBLE_GZIP_RATIO');
	if($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		$t = (float) trim((string) $e);

		return max(0.0, min(1.0, $t));
	}

	return 0.97;
}

/**
 * Inners ≤ this many bytes may include zpaq {@code -method 9|8|7} in the default outer sweep (before 6…3). {@code 0} = off.
 * Default 1 MiB — midsize text (e.g. Calgary / phpinfo corpora) often gains vs m6-only; aligns min-ext {@code zpaq_raw} ladder with
 * {@see maybe_folder_zpaq_native_smaller_than_fzc}. Override with {@code FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES}.
 */
public static function zpaq_outer_high_method_max_inner_bytes(): int {
	$e = getenv('FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}

	return 1048576;
}

/**
 * Skip layered outer probes when gzip baseline is barely smaller than raw inner ({@code gzLen/innerLen} ≥ threshold — near‑incompressible).
 * Tunable: {@code FRACTAL_ZIP_OUTER_PREDICT_MAX_GZIP_LEN_FRAC} (default 0.97); set {@code 0}, {@code off}, or {@code false} to disable.
 */
public static function outer_prediction_skip_layered_for_gzip_baseline(int $innerLen, int $gzLen): bool {
	if($innerLen <= 0 || $gzLen === PHP_INT_MAX || $gzLen <= 0) {
		return false;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_PREDICT_MAX_GZIP_LEN_FRAC');
	if($e !== false && trim((string) $e) !== '') {
		$t = strtolower(trim((string) $e));
		if($t === '0' || $t === 'off' || $t === 'false') {
			return false;
		}
		$maxRatio = (float) trim((string) $e);
	} else {
		$maxRatio = 0.97;
	}
	if($maxRatio <= 0.0 || $maxRatio > 1.0) {
		return false;
	}

	return ((float) $gzLen / (float) $innerLen) >= $maxRatio;
}

/**
 * zpaq {@code -method} for layered benchmark probes — high tier tracks {@see outer_zpaq_blob} first ladder trial when env‑driven.
 *
 * @param 'fast'|'medium'|'high' $tier
 */
public static function zpaq_predict_probe_method_for_tier(string $tier, int $innerLen): int {
	$t = strtolower($tier);
	if($t === 'fast') {
		return 1;
	}
	if($t === 'medium') {
		return 3;
	}
	$frags = self::zpaq_outer_methods_argv_fragments_cached();
	if($frags !== []) {
		foreach($frags as $frag) {
			if(preg_match('/-method\s+(\d+)/', (string) $frag, $m)) {
				return max(0, min(9, (int) $m[1]));
			}
		}
	}
	$methEnv = self::zpaq_method_env_trimmed_cached();
	if($methEnv !== null && $methEnv !== '' && isset($methEnv[0]) && $methEnv[0] !== '-' && ctype_digit($methEnv)) {
		return max(0, min(9, (int) $methEnv));
	}
	$sw = self::zpaq_outer_sweep_env_flags_cached();
	$sweepFromEnv = $sw['env_nonempty'];
	$autoSweepMax = self::zpaq_outer_auto_sweep_max_inner_bytes();
	$sixM = self::ZPAQ_OUTER_METHOD_SIX_MAX_INNER_BYTES;
	$sixCap = $sweepFromEnv ? $sixM : (($autoSweepMax > 0) ? min($sixM, $autoSweepMax) : $sixM);
	if($innerLen > 0 && $innerLen <= $sixCap) {
		return 6;
	}

	return 5;
}

/**
 * When two outer candidates tie on compressed size, {@see outer_candidate_beats_current} can prefer faster-decode heuristics.
 * Set {@code FRACTAL_ZIP_OUTER_TIE_BREAK_DECOMPRESS=0} for legacy behavior: tie keeps the incumbent (first seen in the tournament order).
 */
public static function outer_tie_break_decompress_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_OUTER_TIE_BREAK_DECOMPRESS');
	if($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'false' || $v === 'off' || $v === 'no');
}

/** Lower rank ⇒ preferred on a size tie (typical outer **decompress** speed — not compress). */
public static function outer_decompress_tie_rank(string $codec): int {
	switch($codec) {
		case 'gzip':
			return 0;
		case 'zstd':
			return 10;
		case 'brotli':
			return 20;
		case 'xz':
			return 30;
		case '7z':
			return 40;
		case 'arc':
			return 50;
		case 'zpaq':
			return 60;
		case 'store':
			return 100;
		default:
			return 80;
	}
}

/**
 * Smaller outer length wins; on an exact tie, prefer the outer with better (lower) {@see outer_decompress_tie_rank} when enabled.
 */
public static function outer_candidate_beats_current(string $candidateCodec, int $candidateLen, string $bestCodec, int $bestLen): bool {
	if($candidateLen <= 0) {
		return false;
	}
	if($bestLen === PHP_INT_MAX || $bestLen <= 0) {
		return true;
	}
	if($candidateLen < $bestLen) {
		return true;
	}
	if($candidateLen > $bestLen) {
		return false;
	}
	if(!fractal_zip::outer_tie_break_decompress_enabled()) {
		return false;
	}
	$rc = fractal_zip::outer_decompress_tie_rank($candidateCodec);
	$rb = fractal_zip::outer_decompress_tie_rank($bestCodec);
	if($rc !== $rb) {
		return $rc < $rb;
	}
	return $candidateCodec < $bestCodec;
}

public static function min_7z_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MIN_7Z_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 8192 : max(0, (int) $e);
}

/** When >0 and outerTextlike is false, midsize inners (≤ this many bytes) re-open 7z/arc after gzip-margin early-stop. */
public static function outer_midsize_binary_reopen_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OUTER_MIDSIZE_BINARY_REOPEN_MAX_INNER_BYTES');
	// Default 512 KiB: covers larger non–text-like unified inners (e.g. proto / mixed binary) while staying below huge_slow_outer_min_inner_bytes (default 512 KiB).
	return $cached = ($e === false || trim((string) $e) === '') ? 524288 : max(0, (int) $e);
}

public static function min_arc_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MIN_ARC_INNER_BYTES');
	// 8192: align with 7z floor; outer Arc can still help 8–16 KiB inners (small multi-file literate bundles, test_files74–76).
	return $cached = ($e === false || trim((string) $e) === '') ? 8192 : max(0, (int) $e);
}

public static function min_zstd_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MIN_ZSTD_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 2048 : max(0, (int) $e);
}

public static function min_brotli_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MIN_BROTLI_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 1024 : max(0, (int) $e);
}

public static function max_brotli_outer_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MAX_BROTLI_OUTER_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 262144 : max(0, (int) $e);
}

/** Effective cap for text-like huge FZB brotli-full path (whole-stream FZWS max, optional floor, optional max override). */
public static function outer_brotli_textlike_fzb_full_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$maxEnv = getenv('FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MAX_INNER_BYTES');
	$def = fractal_zip::whole_stream_fzws_max_bytes();
	if($def <= 0) {
		$def = 134217728;
	}
	$floorEnv = getenv('FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MIN_FLOOR_BYTES');
	$floor = ($floorEnv === false || trim((string) $floorEnv) === '') ? (512 * 1024 * 1024) : max(0, (int) trim((string) $floorEnv));
	if($floor > 0) {
		$def = max($def, $floor);
	}
	if($maxEnv === false || trim((string) $maxEnv) === '') {
		return $cached = $def;
	}
	return $cached = max(0, (int) $maxEnv);
}

public static function disable_auto_textlike_fzb_brotli(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI') === '1';
}

public static function disable_auto_textlike_nonfzb_huge_brotli(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_NONFZB_HUGE_BROTLI') === '1';
}

public static function disable_auto_merged_flac_bundle_huge_brotli(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_AUTO_MERGED_FLAC_BUNDLE_HUGE_BROTLI') === '1';
}

public static function skip_zstd_literal_huge_min_inner_bytes_effective(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_ZSTD_LITERAL_HUGE_MIN_INNER_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return $cached = max(0, (int) $e);
	}
	if(fractal_zip::speed_mode_enabled()) {
		return $cached = 4000000;
	}
	return $cached = 0;
}

/** When true, zstd may be skipped for text-like FZB when gzip ratio is tiny vs inner size. */
public static function zstd_textlike_fzb_gzip_div_skip_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_SKIP_ZSTD_TEXTLIKE_FZB') !== '1';
}

public static function skip_zstd_textlike_fzb_gzip_div(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_ZSTD_TEXTLIKE_FZB_GZIP_DIV');
	return $cached = ($e === false || trim((string) $e) === '') ? 50 : max(1, (int) $e);
}

public static function force_arc_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_FORCE_ARC') === '1';
}

/** Lowercase trimmed FRACTAL_ZIP_BROTLI_HUGE_MODE, or '' when unset/empty. */
public static function brotli_huge_mode_env_normalized(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
	return $cached = ($e === false || trim((string) $e) === '') ? '' : strtolower(trim((string) $e));
}

public static function force_brotli_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_FORCE_BROTLI') === '1';
}

/** @return false|string FRACTAL_ZIP_ZSTD_LEVEL unset/blank ⇒ false. */
public static function zstd_level_env_raw() {
	static $cached = '__init__';
	if($cached !== '__init__') {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZSTD_LEVEL');
	return $cached = ($e === false || trim((string) $e) === '') ? false : trim((string) $e);
}

public static function disable_zstd_textlike_level(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_ZSTD_TEXTLIKE_LEVEL') === '1';
}

public static function zstd_textlike_min_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZSTD_TEXTLIKE_MIN_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 32768 : max(0, (int) $e);
}

public static function zstd_textlike_level(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZSTD_TEXTLIKE_LEVEL');
	return $cached = ($e === false || trim((string) $e) === '') ? 14 : max(1, min(22, (int) $e));
}

public static function disable_zstd_fzb_huge_level(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_ZSTD_FZB_HUGE_LEVEL') === '1';
}

public static function zstd_fzb_huge_min_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZSTD_FZB_HUGE_MIN_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 3145728 : max(0, (int) $e);
}

public static function zstd_fzb_huge_level(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZSTD_FZB_HUGE_LEVEL');
	return $cached = ($e === false || trim((string) $e) === '') ? 19 : max(1, min(22, (int) $e));
}

public static function disable_zstd_fzb_alt15(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_ZSTD_FZB_ALT15') === '1';
}

public static function always_try_brotli_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_ALWAYS_TRY_BROTLI') === '1';
}

/**
 * When zstd hits the gzip-margin early-stop, brotli is skipped for inners ≥ this many bytes unless auto textlike / huge brotli paths reopen it.
 * Default 1 MiB (was 160 KiB): midsize Squash-class inners often still gain from brotli Q11 vs zstd on the outer wire.
 */
public static function skip_brotli_if_zstd_earlystop_min_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_BROTLI_IF_ZSTD_EARLYSTOP_MIN_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 1048576 : max(0, (int) $e);
}

/** @return false|string FRACTAL_ZIP_BROTLI_QUALITY unset/blank ⇒ false. */
public static function brotli_quality_env_raw() {
	static $cached = '__init__';
	if($cached !== '__init__') {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_QUALITY');
	return $cached = ($e === false || trim((string) $e) === '') ? false : trim((string) $e);
}

public static function disable_auto_textlike_brotli_q11(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_BROTLI_Q11') === '1';
}

public static function disable_auto_textlike_fzb_brotli_q11(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI_Q11') === '1';
}

public static function auto_brotli_q11_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_AUTO_BROTLI_Q11_MAX_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 2097152 : max(0, (int) $e);
}

public static function disable_fzb_literal_brotli_q11(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11') === '1';
}

public static function disable_merged_flac_bundle_brotli_q11(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_MERGED_FLAC_BUNDLE_BROTLI_Q11') === '1';
}

public static function brotli_huge_probe_quality(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_HUGE_PROBE_QUALITY');
	return $cached = ($e === false || trim((string) $e) === '') ? 1 : max(0, min(11, (int) $e));
}

public static function brotli_huge_trigger_slack_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_HUGE_TRIGGER_SLACK_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 8192 : max(0, (int) $e);
}

public static function brotli_huge_full_timeout_sec(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_HUGE_FULL_TIMEOUT_SEC');
	return $cached = ($e === false || trim((string) $e) === '') ? 8.0 : max(0.0, (float) $e);
}

public static function disable_brotli_fzb_q11_lgwin16(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = fractal_zip::lifestyle_speed_profile_enabled() || getenv('FRACTAL_ZIP_DISABLE_BROTLI_FZB_Q11_LGWIN16') === '1';
}

/**
 * Q11 lgwin refinement (w16 / w20–24): normally off under lifestyle ({@see disable_brotli_fzb_q11_lgwin16}) for speed.
 * Embedded-JPEG SOI inners still run it for min-ext brotli parity unless {@code FRACTAL_ZIP_DISABLE_BROTLI_FZB_Q11_LGWIN16=1}.
 */
public static function brotli_fzb_q11_lgwin_refinement_allowed(bool $jpegEmbeddedBrotliLgwinSweep): bool {
	if($jpegEmbeddedBrotliLgwinSweep) {
		return getenv('FRACTAL_ZIP_DISABLE_BROTLI_FZB_Q11_LGWIN16') !== '1';
	}
	return !fractal_zip::disable_brotli_fzb_q11_lgwin16();
}

/** When false (default), Q11 on text-like FZB* inners also tries -w 20 and -w 24 after -w 16 (bytes-first; skipped in FRACTAL_ZIP_SPEED=1). */
public static function disable_brotli_q11_textlike_lgwin_extra_sweep(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_BROTLI_Q11_TEXTLIKE_LGWIN_EXTRA_SWEEP') === '1';
}

public static function skip_slow_outers_after_brotli_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_SLOW_OUTERS_AFTER_BROTLI_MAX_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 67108864 : max(0, (int) $e);
}

public static function skip_slow_outers_after_brotli_inner_div(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_SLOW_OUTERS_AFTER_BROTLI_INNER_DIV');
	return $cached = ($e === false || trim((string) $e) === '') ? 1000 : max(1, (int) $e);
}

/**
 * Max unified-stream inner size (bytes) for {@see adaptive_compress}: when layered prediction names {@code arc} or
 * {@code zpaq} on a text-like payload, still run full brotli Q10/Q11 + lgwin after predict lanes — those families skip the
 * {@code outerPredFamily === 'brotli'} fast path but HTML-ish text often favors brotli over slow-archive winners (e.g.
 * test_files13). Zero disables the fallback.
 */
public static function brotli_full_outer_arc_textlike_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_FULL_OUTER_ARC_TEXTLIKE_MAX_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 524288 : max(0, (int) $e);
}

/**
 * When layered prediction names {@code arc} or {@code zpaq} but {@see outer_likely_textlike} is false (e.g. tiny PNG/JPEG
 * plus SMS/VCF/plist text — {@code test_files76}), the arc/zpaq full-brotli fallback still matters. Allow Q10/Q11 + lgwin when
 * inner length ≤ this cap; zero disables so only {@see outer_likely_textlike} true triggers for arc/zpaq.
 *
 * Env: {@code FRACTAL_ZIP_BROTLI_FULL_OUTER_ARC_ZPAQ_NON_TEXTLIKE_MAX_INNER_BYTES}.
 */
public static function brotli_full_outer_arc_zpaq_non_textlike_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_FULL_OUTER_ARC_ZPAQ_NON_TEXTLIKE_MAX_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 32768 : max(0, (int) $e);
}

/**
 * Tighter cap than {@see brotli_full_outer_arc_textlike_max_inner_bytes}: when prediction names {@code zstd} or {@code xz},
 * full brotli still isn’t implied — medium Q3 only runs for pred brotli / current-best brotli. Small text-like inners
 * (lifestyle/desktop mixes, test_files74–76) may need Q10/Q11 + lgwin after predict lanes. Zero disables.
 */
public static function brotli_full_outer_zstd_xz_textlike_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_FULL_OUTER_ZSTD_XZ_TEXTLIKE_MAX_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 16384 : max(0, (int) $e);
}

public static function xz_trigger_ratio(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_XZ_TRIGGER_RATIO');
	return $cached = ($e === false || trim((string) $e) === '') ? 0.82 : max(0.0, min(1.0, (float) $e));
}

public static function skip_xz_bundle_huge_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_SKIP_XZ_BUNDLE_HUGE') === '1';
}

public static function skip_xz_literal_huge_min_inner_bytes_effective(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_XZ_LITERAL_HUGE_MIN_INNER_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return $cached = max(0, (int) $e);
	}
	if(fractal_zip::speed_mode_enabled()) {
		return $cached = 4000000;
	}
	return $cached = 0;
}

public static function force_xz_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_FORCE_XZ') === '1';
}

public static function xz_level(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_XZ_LEVEL');
	return $cached = ($e === false || trim((string) $e) === '') ? 9 : max(0, min(9, (int) $e));
}

public static function xz_timeout_sec(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_XZ_TIMEOUT_SEC');
	return $cached = ($e === false || trim((string) $e) === '') ? 8.0 : max(0.0, (float) $e);
}

public static function skip_slow_if_fast_win_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_SLOW_IF_FAST_WIN');
	return $cached = ($e === false || trim((string) $e) === '') ? false : ((int) $e !== 0);
}

public static function skip_slow_if_fast_win_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_SLOW_IF_FAST_WIN_MAX_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 1048576 : max(0, (int) $e);
}

public static function skip_slow_if_fast_win_min_pct(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_SKIP_SLOW_IF_FAST_WIN_MIN_PCT');
	return $cached = ($e === false || trim((string) $e) === '') ? 0.0018 : max(0.0, min(1.0, (float) $e));
}

public static function huge_slow_outer_min_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_HUGE_SLOW_OUTER_MIN_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 524288 : max(262144, (int) $e);
}

public static function huge_try_7z_once_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_HUGE_TRY_7Z_ONCE');
	return $cached = ($e === false || trim((string) $e) === '') ? true : ((int) $e !== 0);
}

public static function arc_before_7z_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ARC_BEFORE_7Z');
	return $cached = ($e === false || trim((string) $e) === '') ? false : ((int) $e !== 0);
}

/**
 * When unset (default on): if layered prediction names a slow codec (zpaq / 7z / arc) and {@see fractal_zip_encode_pipeline::parallel_slow_outer_wave_trials}
 * would schedule that codec with ≥1 other slow codec, skip the corresponding predict lane and run it only in the parallel wave
 * (same merge order; avoids serial predict pass + wave-without-that-codec wall time). Reads {@code FRACTAL_ZIP_DEFER_SLOW_PREDICT_LANES_TO_PARALLEL_WAVE},
 * else legacy {@code FRACTAL_ZIP_DEFER_ZPAQ_PREDICT_TO_PARALLEL_WAVE}; unset ⇒ on for both.
 */
public static function defer_slow_predict_lanes_to_parallel_wave_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_DEFER_SLOW_PREDICT_LANES_TO_PARALLEL_WAVE');
	if($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_DEFER_ZPAQ_PREDICT_TO_PARALLEL_WAVE');
	}
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));

	return $cached = !($v === '0' || $v === 'false' || $v === 'no' || $v === 'off');
}

/** @deprecated Use {@see defer_slow_predict_lanes_to_parallel_wave_enabled}; alias for env parity. */
public static function defer_zpaq_predict_lane_to_parallel_wave_enabled(): bool {
	return self::defer_slow_predict_lanes_to_parallel_wave_enabled();
}

public static function fzb_binary_skip_7z_sweep_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_FZB_BINARY_SKIP_7Z_SWEEP') === '1';
}

/**
 * Max inner bytes for brotli Q11 on FZB literal bundles (path-order proxy, staged fast tier, adaptive outer).
 * Unset ⇒ bytes-first 16 MiB; FRACTAL_ZIP_SPEED=1 ⇒ 64 KiB unless FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES is set.
 */
public static function fzb_literal_brotli_q11_max_inner_bytes_effective(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return $cached = max(0, (int) trim((string) $e));
	}
	if(fractal_zip::speed_mode_enabled()) {
		return $cached = 65536;
	}
	return $cached = 16777216;
}

/**
 * Brotli-Q11 proxy cap for FZB4 path-order / layout scoring (many trials per folder). Uses zlib-9 fallback above this to keep
 * bytes-first outer Q11 (see fzb_literal_brotli_q11_max_inner_bytes_effective) without O(trials×huge) brotli work.
 * FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES overrides; default min(effective, 512 KiB).
 */
public static function fzb_literal_brotli_q11_path_order_proxy_max_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return $cached = max(0, (int) trim((string) $e));
	}
	$eff = fractal_zip::fzb_literal_brotli_q11_max_inner_bytes_effective();
	if(fractal_zip::ultra_compression_enabled()) {
		return $cached = $eff;
	}
	return $cached = min($eff, 524288);
}

/**
 * HTML progress/trace lines (Fractal zipping folder, zipping:, timings, green contest lines). Suppressed in CLI unless
 * FRACTAL_ZIP_CLI_VERBOSE=1 (matches fractal_zip_cli.php + benchmarks). FRACTAL_ZIP_SUPPRESS_HTML=1 forces off everywhere.
 */
public static function emit_html_trace(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	if(getenv('FRACTAL_ZIP_SUPPRESS_HTML') === '1') {
		return $cached = false;
	}
	if(getenv('FRACTAL_ZIP_CLI_VERBOSE') === '1') {
		return $cached = true;
	}
	return $cached = (PHP_SAPI !== 'cli');
}

public static function html_trace_print(string $html): void {
	if(!fractal_zip::emit_html_trace()) {
		return;
	}
	print($html);
}

/** Var_dump spam in fractally_process_string (&lt;s skip path). Only when FRACTAL_ZIP_FRACTAL_PROCESS_DEBUG=1. */
public static function emit_fractal_process_string_dumps(): bool {
	return getenv('FRACTAL_ZIP_FRACTAL_PROCESS_DEBUG') === '1';
}

/**
 * Default substring window when the constructor is given no length and FRACTAL_ZIP_SEGMENT_LENGTH is unset.
 * (Larger windows find longer cross-file repeats; tune per corpus with benchmarks/run_benchmarks.php --tune.)
 */
public const DEFAULT_SEGMENT_LENGTH = 300;

//function __construct($improvement_factor_threshold = 10, $segment_length = 20000, $multipass = false) {
/**
 * @param null|int $segment_length
 * @param null|array<string, int|float|null> $compression_tuning_overrides Optional trial overrides: substring_top_k, multipass_gate_mult, improvement_factor_threshold, multipass_max_additional_passes (int≥0 or null=unlimited)
 */
function __construct($segment_length = null, $multipass = true, $allow_auto_segment_selection = true, $compression_tuning_overrides = null, $allow_auto_multipass_selection = true) {
	$this->initial_time = time();
	$this->initial_micro_time = microtime(true);
	define('DS', DIRECTORY_SEPARATOR);
	$this->auto_segment_selection_enabled = false;
	$this->auto_segment_selection_in_progress = false;
	$this->auto_multipass_selection_enabled = false;
	$this->auto_multipass_selection_in_progress = false;
	$this->auto_tune_compression = false;
	/** Set during auto-tune probe: zip wall time ≤ threshold ⇒ prefer smallest .fzc; else prefer J-like score (bytes×time^p). */
	$this->auto_tune_fast_corpus_size_priority = false;
	$this->j_curve_context_raw_bytes = null;
	$this->j_curve_context_probe_zip_sec = null;
	$this->tuning_substring_top_k = null;
	$this->tuning_multipass_gate_mult = null;
	$this->auto_segment_candidates = array(60, 100, 160, 200, 240, 280, 300, 320, 360, 380, 400, 420, 460, 500, 520);
	if($segment_length === null) {
		$envSeg = getenv('FRACTAL_ZIP_SEGMENT_LENGTH');
		if(fractal_zip::lifestyle_speed_profile_enabled() && ($envSeg === false || trim((string) $envSeg) === '')) {
			$envSeg = '1000';
		}
		$autoByName = is_string($envSeg) && strtolower(trim($envSeg)) === 'auto';
		$autoByFlag = getenv('FRACTAL_ZIP_AUTO_SEGMENT') === '1';
		if($allow_auto_segment_selection && ($autoByName || $autoByFlag)) {
			$this->auto_segment_selection_enabled = true;
			$segment_length = fractal_zip::DEFAULT_SEGMENT_LENGTH;
			$cand = getenv('FRACTAL_ZIP_AUTO_SEGMENTS');
			if(is_string($cand) && trim($cand) !== '') {
				$parsed = array();
				foreach(explode(',', $cand) as $piece) {
					$piece = trim($piece);
					if($piece !== '' && is_numeric($piece)) {
						$parsed[] = max(8, min(500000, (int) $piece));
					}
				}
				$parsed = array_values(array_unique($parsed));
				if(sizeof($parsed) > 0) {
					sort($parsed);
					$this->auto_segment_candidates = $parsed;
				}
			}
		} elseif($envSeg !== false && $envSeg !== '' && is_numeric($envSeg)) {
			$segment_length = max(8, min(500000, (int) $envSeg));
		} else {
			$segment_length = fractal_zip::DEFAULT_SEGMENT_LENGTH;
		}
	} else {
		$segment_length = max(8, min(500000, (int) $segment_length));
	}
	$autoMpEnv = getenv('FRACTAL_ZIP_AUTO_MULTIPASS');
	$autoMp = !($autoMpEnv !== false && trim((string)$autoMpEnv) === '0');
	$this->auto_multipass_selection_enabled = $allow_auto_multipass_selection && $autoMp;
	$this->var_display_max_depth = 12;
	$this->var_display_max_depth = 62;
	//$this->var_display_max_children = 8;
	ini_set('xdebug.var_display_max_depth', $this->var_display_max_depth);
	//ini_set('xdebug.var_display_max_children', $this->var_display_max_children);
	ini_set('xdebug.max_nesting_level', $segment_length + 30); // enough room for recursion?
	//error_reporting(E_ALL);
	//include('..' . DS . 'diff' . DS . 'class.Diff.php');
	$this->fractal_zip_marker = 'FZ';
	$this->fractal_zip_file_extension = '.fractalzip';
	$this->fractal_zip_container_file_extension = '.fzc';
	$this->program_path = substr(__FILE__, 0, fractal_zip::strpos_last(__FILE__, DS));
	//print('__FILE__, get_included_files(), $this->program_path: ');var_dump(__FILE__, get_included_files(), $this->program_path);exit(0);
	$this->fractal_strings = array();
	$this->fractal_string = '';
	$this->equivalences = array();
	$this->branch_counter = 0;
	$this->fractal_path_branch_trimming_score = 0.8;
	//$this->fractal_path_branch_trimming_score = 1;
	//$this->fractal_path_branch_trimming_score = 0.8;
	//$this->fractal_path_branch_trimming_score = 0.6;
	//$this->fractal_path_branch_trimming_score = 1.1;
	$this->fractal_path_branch_trimming_multiplier = 0.618 * 2;
	//$this->fractal_path_branch_trimming_multiplier = 1.618 * 2;
	//$this->fractal_path_branch_trimming_multiplier = 1;
	$impEnv = getenv('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD');
	if($impEnv !== false && trim((string) $impEnv) !== '' && is_numeric($impEnv)) {
		$this->improvement_factor_threshold = max(0.01, min(50.0, (float) $impEnv));
	} else {
		// Lower = more multipass range substitutions (better ratio, slower). Gate uses effective_multipass_gate_mult() × this.
		$this->improvement_factor_threshold = 0.1;
	}
	//$this->multipass = false; // https://www.youtube.com/watch?v=JljZbjpvjmE
	$this->multipass = $multipass;
	// Max extra fractal passes after the initial encode; null = unlimited while improvements exist.
	$this->multipass_max_additional_passes = null;
	$mpEnv = getenv('FRACTAL_ZIP_MULTIPASS');
	if($mpEnv === '1' || strtolower(trim((string) $mpEnv)) === 'true' || strtolower(trim((string) $mpEnv)) === 'yes') {
		$this->multipass = true;
	}
	if($mpEnv === '0' || strtolower(trim((string) $mpEnv)) === 'false' || strtolower(trim((string) $mpEnv)) === 'no') {
		$this->multipass = false;
	}
	if(is_array($compression_tuning_overrides)) {
		if(array_key_exists('substring_top_k', $compression_tuning_overrides)) {
			$this->tuning_substring_top_k = max(1, min(12, (int) $compression_tuning_overrides['substring_top_k']));
		}
		if(array_key_exists('multipass_gate_mult', $compression_tuning_overrides)) {
			$this->tuning_multipass_gate_mult = max(1.0, min(4.0, (float) $compression_tuning_overrides['multipass_gate_mult']));
		}
		if(array_key_exists('improvement_factor_threshold', $compression_tuning_overrides)) {
			$this->improvement_factor_threshold = max(0.01, min(50.0, (float) $compression_tuning_overrides['improvement_factor_threshold']));
		}
		if(array_key_exists('multipass_max_additional_passes', $compression_tuning_overrides)) {
			$mpa = $compression_tuning_overrides['multipass_max_additional_passes'];
			if($mpa === null || $mpa === '' || (is_string($mpa) && strtolower(trim($mpa)) === 'unlimited')) {
				$this->multipass_max_additional_passes = null;
			} else {
				$this->multipass_max_additional_passes = max(0, (int) $mpa);
			}
		}
	}
	$mpaEnv = getenv('FRACTAL_ZIP_MULTIPASS_MAX_ADDITIONAL_PASSES');
	if(!is_array($compression_tuning_overrides) || !array_key_exists('multipass_max_additional_passes', $compression_tuning_overrides)) {
		if($mpaEnv !== false && trim((string) $mpaEnv) !== '') {
			$ms = strtolower(trim((string) $mpaEnv));
			if($ms === 'unlimited' || $ms === '-1') {
				$this->multipass_max_additional_passes = null;
			} else {
				$this->multipass_max_additional_passes = max(0, (int) trim((string) $mpaEnv));
			}
		}
	}
	$autoTuneEnv = getenv('FRACTAL_ZIP_AUTO_TUNE');
	$tuneExplicitOn = ($autoTuneEnv === '1' || strtolower(trim((string) $autoTuneEnv)) === 'true' || strtolower(trim((string) $autoTuneEnv)) === 'yes');
	$tuneExplicitOff = ($autoTuneEnv === '0' || strtolower(trim((string) $autoTuneEnv)) === 'false' || strtolower(trim((string) $autoTuneEnv)) === 'no');
	if($allow_auto_segment_selection) {
		if($tuneExplicitOn) {
			$this->auto_tune_compression = true;
		} elseif($this->auto_segment_selection_enabled && !$tuneExplicitOff) {
			$this->auto_tune_compression = true;
		}
	}
	//$this->segment_length = 140; // in honor of twitter
	//$this->segment_length = 20; // heavily impacts performance; smaller is faster
	$this->segment_length = $segment_length;
	// to put into words how multipass and improvement_factor_threshold affect speed and compression: requiring higher improvement means that range information is only substituted for the juicier segments and smaller 
	// duplicated segments for which range information could be substituted are left as is to simply be compressed. the improvement threshold does not signficantly affect speed but whether to use multipass and its 
	// sub-property of a longer segment length to look for matches in makes the process much slower for questionable gain in compression.
	$this->files_counter = 0;
	$this->lazy_fractal_strings = array();
	$this->lazy_fractal_string = '';
	$this->lazy_equivalences = array();
	// it's interesting to consider whether markers could be chosen according to a file's content rather than attempting to choose markers that will never occur in any file ever
	// choosing and managing these dynamic markers would be more complex but offer better compression
	// it would also require changing the program by adding a pre-pass over all the files to be able to find strings to use as markers 
	// also note that markers can never have as their last or first character any character used to express a portion of the fractal_string
	$this->fractal_zipping_pass = 0;
	//$this->left_fractal_zip_marker = 'X';
	//$this->mid_fractal_zip_marker = 'Y';
	//$this->right_fractal_zip_marker = 'Z';
	//$this->left_fractal_zip_marker = 'XXX9o9left9o9XXX';
	//$this->mid_fractal_zip_marker = 'XXX9o9mid9o9XXX';
	//$this->right_fractal_zip_marker = 'XXX9o9right9o9XXX';
	$this->left_fractal_zip_marker = '<';
	$this->mid_fractal_zip_marker = '"';
	$this->right_fractal_zip_marker = '>';
	$this->range_shorthand_marker = '*';
	// effectively all that's been done is intelligently using a short syntax for replacement operations. with more operators more gains could be had but it's worth pointing out that finite programming instructions can generate
	// infinite outputs so that a great compression system could intelligently use programming instructions, but much better AI is needed first
	$this->strings_for_fractal_zip_markers = array();
	//$this->common_limiter_pairs = array(array('(', ')'), array('[', ']'), array('{', '}'), array('<', '>'), );
	$this->common_limiter_pairs = array(array('<', '>'), ); // hey look, we're biased towards HTML again...
	//$this->common_limiters = array(',', ' ', '.', ); // being careful with space...
	$this->common_limiters = array(',', '.', ';', ':', '/', '\\', '|');
	/** Set in zip_folder(): real path (or original $dir) so member keys omit duplicated parent path in .fzc. */
	$this->zip_folder_root_for_members = '';
	/**
	 * After collect_raw_files_for_bundle() for a root, true so Arc compare can materialize from memory instead of re-walking disk.
	 * Reset at zip_folder() entry via reset_folder_bundle_structure_scan_state().
	 */
	$this->folder_structure_was_made = false;
	$this->folder_structure_scan_root = '';
	$this->folder_structure_raw_files_by_path = null;
	/** member path => exact on-disk bytes when fractal zip peeled an outer gzip layer (FZG1 trailer on encode). */
	$this->fractal_member_gzip_disk_restore = array();
	/** Set by decode_container_payload / open_container: paths that unzip to peeled inner but must be written as original gzip bytes. */
	$this->fractal_gzip_peel_restore_map = array();
	/** sha1(canonical raster blob, binary) => [offset, length] into lazy_fractal_string for cross-format dedup. */
	$this->raster_canonical_hash_to_lazy_range = array();
	$this->raster_canonical_reuse_lazy_range = null;
	$this->zip_pending_raster_canonical_hash = null;
	$this->zip_raster_canonical_lazy_only = false;
}

function recursive_copy_directory($src, $dst) {
	if(!is_dir($dst)) {
		mkdir($dst, 0755, true);
	}
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
	foreach($it as $item) {
		$sub = $it->getSubPathname();
		$target = $dst . DS . $sub;
		if($item->isDir()) {
			if(!is_dir($target)) {
				mkdir($target, 0755, true);
			}
		} else {
			$parent = dirname($target);
			if(!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $target);
		}
	}
}

function escape_literal_for_storage($string) {
	$this->fractal_marker_ctx_publish();
	$s = str_replace('&', '&amp;', (string) $string);
	$s = str_replace('<', '&lt;', $s);
	// So literals in the fractal blob cannot mimic the closing bracket of real substring/range ops (differentiation from FZ syntax vs payload).
	$g = getenv('FRACTAL_ZIP_LITERAL_ESCAPE_GT');
	$skipGt = ($g === '0' || strtolower(trim((string) $g)) === 'off' || strtolower(trim((string) $g)) === 'false');
	if(!$skipGt && $this->right_fractal_zip_marker === '>' && strlen($this->right_fractal_zip_marker) === 1) {
		$s = str_replace('>', '&gt;', $s);
	}
	return $s;
}

function unescape_literal_from_storage($string) {
	return htmlspecialchars_decode((string) $string, ENT_QUOTES | ENT_SUBSTITUTE);
}

function should_force_lazy_member($entry_filename, $string) {
	$env = getenv('FRACTAL_ZIP_FORCE_LAZY_BINARY');
	if($env !== false && trim((string)$env) === '0') {
		return false;
	}
	// Must match FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER in fractal_zip_flac_pac.php (constant not loaded until FLAC PAC is).
	if((string) $entry_filename === '__fzcd_merged.pcm') {
		return false;
	}
	$path = strtolower((string)$entry_filename);
	// Only force lazy for .gz/.svgz/.vgz that are still a gzip stream (peeled plaintext uses full fractal path).
	if(preg_match('/\.(gz|svgz|vgz)$/i', $path) === 1) {
		$s = (string)$string;
		if(strlen($s) >= 2 && ord($s[0]) === 0x1f && ord($s[1]) === 0x8b) {
			return true;
		}
		return false;
	}
	return false;
}

function recursive_remove_directory($dir) {
	if(!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
	foreach($it as $item) {
		if($item->isDir()) {
			rmdir($item->getPathname());
		} else {
			unlink($item->getPathname());
		}
	}
	rmdir($dir);
}

/**
 * @param null|array<string, int|float> $overrides
 * @param null|array<string, int|float|null>|null $overrides
 * @param null|bool $multipassOverride
 * @return array{0: int, 1: float} .fzc size (bytes), zip_folder wall time (seconds)
 */
function measure_folder_zip_trial($dir, $segment, $overrides, $multipassOverride = null) {
	$segment = max(8, min(500000, (int) $segment));
	$tag = 'fzauto_' . substr(md5((string) $dir . "\0" . (string) $segment . "\0" . microtime(true) . "\0" . (string) spl_object_id($this)), 0, 8) . '_' . $segment;
	$tmpDir = $this->program_path . DS . $tag;
	$tmpFzc = $tmpDir . $this->fractal_zip_container_file_extension;
	$this->recursive_copy_directory($dir, $tmpDir);
	$trialMultipass = $multipassOverride === null ? $this->multipass : (bool)$multipassOverride;
	$trialOverrides = is_array($overrides) ? $overrides : null;
	$trial = new fractal_zip($segment, $trialMultipass, false, $trialOverrides, false);
	ob_start();
	$t0 = microtime(true);
	$trial->zip_folder($tmpDir, false);
	$dt = microtime(true) - $t0;
	ob_end_clean();
	$size = is_file($tmpFzc) ? (int) filesize($tmpFzc) : PHP_INT_MAX;
	if(is_file($tmpFzc)) {
		unlink($tmpFzc);
	}
	$this->recursive_remove_directory($tmpDir);
	return array($size, $dt);
}

/**
 * Auto-tune trial ordering: **default = smallest .fzc bytes** (tie-break: shorter zip time). Set FRACTAL_ZIP_FAST_CORPUS_ZIP_SEC
 * to a small positive value (e.g. 0.5) to use J_proxy ≈ bytes×zip_time^(p×w) on *slow* probes only; **-1** = always J_proxy (never “fast”).
 * FRACTAL_ZIP_AUTO_TUNE_J_TIME_POWER: exponent on zip seconds in J_proxy (default 2).
 * FRACTAL_ZIP_J_CURVE_* / FRACTAL_ZIP_J_CURVE_W_SCALE: affect J_proxy when not in bytes-only mode and benchmark J scoring.
 */
function auto_tune_trial_is_better($sz, $tm, $bsz, $btm) {
	if($this->auto_tune_fast_corpus_size_priority) {
		return $sz < $bsz || ($sz === $bsz && $tm < $btm);
	}
	$pw = fractal_zip::auto_tune_j_time_power();
	$rb = $this->j_curve_context_raw_bytes;
	$probe = $this->j_curve_context_probe_zip_sec;
	$zipForW = ($probe !== null) ? (float) $probe : 1.0;
	$w = ($rb !== null) ? fractal_zip::j_curve_effective_weight((int) $rb, $zipForW, null, null) : 1.0;
	$te = $pw * $w;
	$eps = 1e-9;
	$j = $sz * pow(max($tm, $eps), $te);
	$bj = $bsz * pow(max($btm, $eps), $te);
	if($j < $bj) {
		return true;
	}
	if($j > $bj) {
		return false;
	}
	return $sz < $bsz || ($sz === $bsz && $tm < $btm);
}

/**
 * @return list<int>
 */
function parse_auto_tune_int_list($envKey, $defaultList, $min, $max) {
	$e = getenv($envKey);
	if(!is_string($e) || trim($e) === '') {
		return $defaultList;
	}
	$out = array();
	foreach(explode(',', $e) as $piece) {
		$piece = trim($piece);
		if($piece !== '' && is_numeric($piece)) {
			$out[] = max($min, min($max, (int) $piece));
		}
	}
	$out = array_values(array_unique($out));
	sort($out, SORT_NUMERIC);
	return sizeof($out) > 0 ? $out : $defaultList;
}

/**
 * @return list<float>
 */
function parse_auto_tune_float_list($envKey, $defaultList, $min, $max) {
	$e = getenv($envKey);
	if(!is_string($e) || trim($e) === '') {
		return $defaultList;
	}
	$out = array();
	foreach(explode(',', $e) as $piece) {
		$piece = trim($piece);
		if($piece !== '' && is_numeric($piece)) {
			$out[] = max($min, min($max, (float) $piece));
		}
	}
	$out = array_values(array_unique($out));
	sort($out);
	return sizeof($out) > 0 ? $out : $defaultList;
}

/**
 * Zip-only probe seconds threshold for “fast corpus” = **bytes-first auto-tune** (smallest .fzc wins trials).
 * Unset ⇒ +∞ (every corpus bytes-first). -1 ⇒ never fast (always J_proxy). Positive finite ⇒ legacy hybrid (e.g. 0.5).
 */
public static function auto_tune_fast_zip_probe_threshold_sec() {
	$e = getenv('FRACTAL_ZIP_FAST_CORPUS_ZIP_SEC');
	if($e === false || trim((string) $e) === '') {
		return INF;
	}
	return (float) trim((string) $e);
}

public static function zip_probe_implies_fast_corpus($probeZipSeconds) {
	$t = fractal_zip::auto_tune_fast_zip_probe_threshold_sec();
	if($t < 0.0) {
		return false;
	}
	return $probeZipSeconds <= $t;
}

/** Base power on zip seconds in J_proxy = bytes × zip^(power×w); w from j_curve_effective_weight (default power 2). */
public static function auto_tune_j_time_power() {
	$e = getenv('FRACTAL_ZIP_AUTO_TUNE_J_TIME_POWER');
	if($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 2.0;
	}
	return max(0.5, min(4.0, (float) $e));
}

/**
 * Raw-size limb w_raw ∈ [0,1]: small unpacked folders → 0 (bytes-first), large → 1. No global OFF check (use j_curve_effective_weight).
 */
public static function j_curve_raw_bytes_weight($rawBytes) {
	$rawBytes = max(0, (int) $rawBytes);
	$rMinE = getenv('FRACTAL_ZIP_J_CURVE_RAW_MIN_BYTES');
	$rMaxE = getenv('FRACTAL_ZIP_J_CURVE_RAW_MAX_BYTES');
	$rMin = ($rMinE === false || trim((string) $rMinE) === '') ? 32768 : max(1, (int) $rMinE);
	$rMax = ($rMaxE === false || trim((string) $rMaxE) === '') ? 12582912 : max($rMin + 1, (int) $rMaxE);
	if($rawBytes <= $rMin) {
		return 0.0;
	}
	if($rawBytes >= $rMax) {
		return 1.0;
	}
	$t = (log((float) $rawBytes) - log((float) $rMin)) / (log((float) $rMax) - log((float) $rMin));
	$t = max(0.0, min(1.0, $t));
	return $t * $t * (3.0 - 2.0 * $t);
}

/**
 * Weight from raw bytes only; FRACTAL_ZIP_J_CURVE_OFF=1 ⇒ 1.0 (legacy). Prefer j_curve_effective_weight for benchmarks.
 */
public static function j_curve_time_weight($rawBytes) {
	if(getenv('FRACTAL_ZIP_J_CURVE_OFF') === '1') {
		return 1.0;
	}
	return fractal_zip::j_curve_raw_bytes_weight($rawBytes);
}

/**
 * @return float smoothstep in [0,1], 0 when value ≤ vMin, 1 when ≥ vMax (log-spaced between).
 */
public static function j_curve_log_smoothstep_scale($value, $vMin, $vMax) {
	$value = (float) $value;
	$vMin = (float) $vMin;
	$vMax = (float) $vMax;
	if($vMax <= $vMin) {
		return $value >= $vMax ? 1.0 : 0.0;
	}
	if($value <= $vMin) {
		return 0.0;
	}
	if($value >= $vMax) {
		return 1.0;
	}
	$t = (log($value) - log($vMin)) / (log($vMax) - log($vMin));
	$t = max(0.0, min(1.0, $t));
	return $t * $t * (3.0 - 2.0 * $t);
}

/**
 * Limb for “how much time matters” from zip×extract: fast round-trip ⇒ near 0 (compress smaller), slow ⇒ 1.
 * Defaults span ~1e-5 s² … 6 s² so typical sub-second benches stay bytes-leaning on large raw corpora (override FRACTAL_ZIP_J_CURVE_RT_PRODUCT_MAX).
 */
public static function j_curve_roundtrip_time_scale($zipSeconds, $extractSeconds) {
	if(getenv('FRACTAL_ZIP_J_CURVE_OFF') === '1' || getenv('FRACTAL_ZIP_J_CURVE_TIME_AXIS_OFF') === '1') {
		return 1.0;
	}
	$eps = 1e-12;
	$p = max((float) $zipSeconds, $eps) * max((float) $extractSeconds, $eps);
	$pMinE = getenv('FRACTAL_ZIP_J_CURVE_RT_PRODUCT_MIN');
	$pMaxE = getenv('FRACTAL_ZIP_J_CURVE_RT_PRODUCT_MAX');
	$pMin = ($pMinE === false || trim((string) $pMinE) === '' || !is_numeric($pMinE)) ? 1e-5 : max($eps, (float) $pMinE);
	$pMax = ($pMaxE === false || trim((string) $pMaxE) === '' || !is_numeric($pMaxE)) ? 6.0 : max($pMin * 1.000001, (float) $pMaxE);
	return fractal_zip::j_curve_log_smoothstep_scale($p, $pMin, $pMax);
}

/**
 * Zip-only limb for auto-tune (no extract in trials). Fast zip probe ⇒ near 0.
 */
public static function j_curve_zip_time_scale($zipSeconds) {
	if(getenv('FRACTAL_ZIP_J_CURVE_OFF') === '1' || getenv('FRACTAL_ZIP_J_CURVE_TIME_AXIS_OFF') === '1') {
		return 1.0;
	}
	$eps = 1e-12;
	$z = max((float) $zipSeconds, $eps);
	$zMinE = getenv('FRACTAL_ZIP_J_CURVE_ZIP_SEC_MIN');
	$zMaxE = getenv('FRACTAL_ZIP_J_CURVE_ZIP_SEC_MAX');
	$zMin = ($zMinE === false || trim((string) $zMinE) === '' || !is_numeric($zMinE)) ? 0.02 : max($eps, (float) $zMinE);
	$zMax = ($zMaxE === false || trim((string) $zMaxE) === '' || !is_numeric($zMaxE)) ? 3.5 : max($zMin * 1.000001, (float) $zMaxE);
	return fractal_zip::j_curve_log_smoothstep_scale($z, $zMin, $zMax);
}

/** Default FRACTAL_ZIP_J_CURVE_W_SCALE when unset: 0 ⇒ benchmark J equals .fzc bytes (bytes-first reporting). */
public const J_CURVE_W_SCALE_DEFAULT_BENCH = 0.0;

/**
 * Global multiplier on J-curve weight (after w_raw×w_time). From env or default; used when $compressionFirstScaleOverride is null.
 */
public static function j_curve_w_scale_global() {
	$e = getenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
	if($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return fractal_zip::J_CURVE_W_SCALE_DEFAULT_BENCH;
	}
	return max(0.0, min(1.0, (float) $e));
}

/**
 * Effective w = min(1, w_raw × w_time × scale); w_time from round-trip if extract given, else zip-only scale.
 * $compressionFirstScaleOverride: 0…1 multiplier for this row/corpus (null ⇒ j_curve_w_scale_global(), i.e. env or default).
 * FRACTAL_ZIP_J_CURVE_OFF=1 ⇒ 1.0 (legacy full time weight in score).
 */
public static function j_curve_effective_weight($rawBytes, $zipSeconds, $extractSeconds = null, $compressionFirstScaleOverride = null) {
	if(getenv('FRACTAL_ZIP_J_CURVE_OFF') === '1') {
		return 1.0;
	}
	$wR = fractal_zip::j_curve_raw_bytes_weight((int) $rawBytes);
	$base = $wR;
	if(getenv('FRACTAL_ZIP_J_CURVE_TIME_AXIS_OFF') !== '1') {
		$z = max((float) $zipSeconds, 1e-12);
		if($extractSeconds !== null && is_finite((float) $extractSeconds) && (float) $extractSeconds > 0.0) {
			$wT = fractal_zip::j_curve_roundtrip_time_scale($z, (float) $extractSeconds);
		} else {
			$wT = fractal_zip::j_curve_zip_time_scale($z);
		}
		$base = $wR * $wT;
	}
	$scale = $compressionFirstScaleOverride !== null
		? max(0.0, min(1.0, (float) $compressionFirstScaleOverride))
		: fractal_zip::j_curve_w_scale_global();
	return min(1.0, $base * $scale);
}

/**
 * Benchmark J: fzc_B × (zip_s × ex_s)^w, w = j_curve_effective_weight(..., $compressionFirstScaleOverride).
 */
public static function bench_j_score($fzcBytes, $zipSeconds, $extractSeconds, $rawBytes, $compressionFirstScaleOverride = null) {
	$eps = 1e-12;
	$b = (float) $fzcBytes;
	$s = max((float) $zipSeconds, $eps);
	$x = max((float) $extractSeconds, $eps);
	$w = fractal_zip::j_curve_effective_weight((int) $rawBytes, $s, $x, $compressionFirstScaleOverride);
	if($w <= 1e-15) {
		return $b;
	}
	return $b * pow($s * $x, $w);
}

function choose_best_segment_length_for_folder($dir, $debug = false) {
	$this->j_curve_context_raw_bytes = $this->folder_raw_total_bytes($dir);
	$candidates = $this->auto_segment_candidates;
	if(!is_array($candidates) || sizeof($candidates) === 0) {
		return $this->segment_length;
	}
	$probeSeg = max(8, min(500000, (int) $this->segment_length));
	list($probeSz, $probeTm) = $this->measure_folder_zip_trial($dir, $probeSeg, null);
	$this->j_curve_context_probe_zip_sec = $probeTm;
	$this->auto_tune_fast_corpus_size_priority = fractal_zip::zip_probe_implies_fast_corpus($probeTm);
	$best_segment = $probeSeg;
	$best_size = $probeSz;
	$best_time = $probeTm;
	foreach($candidates as $candidate) {
		$candidate = max(8, min(500000, (int) $candidate));
		if($candidate === $probeSeg) {
			$size = $probeSz;
			$dt = $probeTm;
		} else {
			list($size, $dt) = $this->measure_folder_zip_trial($dir, $candidate, null);
		}
		if($this->auto_tune_trial_is_better($size, $dt, $best_size, $best_time)) {
			$best_size = $size;
			$best_time = $dt;
			$best_segment = $candidate;
		}
	}
	if($debug) {
		print('auto segment choice, segment/size/time: ');var_dump($best_segment, $best_size, $best_time);
	}
	return $best_segment;
}

/**
 * Auto multipass: **measured**, **bytes-first** — several temp zip_folder runs (multipass off; multipass on with 0 / 1 / unlimited
 * extra fractal passes), then pick the smallest .fzc (tie: shorter zip time). No J_proxy here. Override trial list with
 * FRACTAL_ZIP_AUTO_MULTIPASS_PASS_TRIALS=comma list: `off`, `0`, `1`, `2`, …, `unlimited` (after `off`, entries are multipass-on
 * max-additional-pass counts). Default: `off,0,1,unlimited`. Opt out: FRACTAL_ZIP_AUTO_MULTIPASS_SKIP_TRIALS=1 or
 * FRACTAL_ZIP_AUTO_MULTIPASS_RUN_TRIALS=0. If raw bytes > FRACTAL_ZIP_AUTO_MULTIPASS_PROBE_MAX_BYTES (default 256 MiB), skip
 * trials and set multipass on with unlimited additional passes.
 */
function choose_best_multipass_for_folder($dir, $debug = false) {
	$this->j_curve_context_raw_bytes = $this->folder_raw_total_bytes($dir);
	$totalBytes = (int) $this->j_curve_context_raw_bytes;
	$skipTrialsEnv = getenv('FRACTAL_ZIP_AUTO_MULTIPASS_SKIP_TRIALS');
	$skipTrials = ($skipTrialsEnv !== false && trim((string) $skipTrialsEnv) !== '' && ((int) $skipTrialsEnv === 1 || strtolower(trim((string) $skipTrialsEnv)) === 'true' || strtolower(trim((string) $skipTrialsEnv)) === 'on'));
	$legacyRunEnv = getenv('FRACTAL_ZIP_AUTO_MULTIPASS_RUN_TRIALS');
	if($legacyRunEnv !== false && trim((string) $legacyRunEnv) !== '' && ((int) $legacyRunEnv === 0 || strtolower(trim((string) $legacyRunEnv)) === 'off' || strtolower(trim((string) $legacyRunEnv)) === 'false')) {
		$skipTrials = true;
	}
	if($skipTrials) {
		$this->multipass_max_additional_passes = null;
		if($debug) {
			print('auto multipass: FRACTAL_ZIP_AUTO_MULTIPASS_SKIP_TRIALS=1; leaving multipass off<br>');
		}
		return false;
	}
	$maxTrialEnv = getenv('FRACTAL_ZIP_AUTO_MULTIPASS_PROBE_MAX_BYTES');
	$maxTrialBytes = ($maxTrialEnv === false || trim((string) $maxTrialEnv) === '') ? (256 * 1024 * 1024) : max(0, (int) $maxTrialEnv);
	if($maxTrialBytes > 0 && $totalBytes > $maxTrialBytes) {
		fractal_zip::warning_once('auto multipass: raw bytes exceed FRACTAL_ZIP_AUTO_MULTIPASS_PROBE_MAX_BYTES; skipping pass trials, multipass on unlimited');
		$this->multipass = true;
		$this->multipass_max_additional_passes = null;
		if($debug) {
			print('auto multipass probe skipped over max bytes; defaulting multipass on unlimited: ');
			var_dump($totalBytes, $maxTrialBytes);
		}
		return true;
	}
	$segment = max(8, min(500000, (int)$this->segment_length));
	$trialSpecs = array();
	$gridEnv = getenv('FRACTAL_ZIP_AUTO_MULTIPASS_PASS_TRIALS');
	if($gridEnv !== false && trim((string) $gridEnv) !== '') {
		foreach(explode(',', (string) $gridEnv) as $piece) {
			$piece = strtolower(trim((string) $piece));
			if($piece === '') {
				continue;
			}
			if($piece === 'off' || $piece === 'no' || $piece === 'false') {
				$trialSpecs[] = array(false, null);
			} elseif($piece === 'unlimited' || $piece === 'unlim' || $piece === 'inf') {
				$trialSpecs[] = array(true, null);
			} elseif(ctype_digit($piece)) {
				$trialSpecs[] = array(true, (int) $piece);
			}
		}
	}
	if($trialSpecs === array()) {
		$trialSpecs = array(
			array(false, null),
			array(true, 0),
			array(true, 1),
			array(true, null),
		);
	}
	$bestSize = PHP_INT_MAX;
	$bestTime = INF;
	$winMp = false;
	$winMax = null;
	foreach($trialSpecs as $spec) {
		$useMp = (bool) $spec[0];
		$maxAp = $spec[1];
		$ov = null;
		if($useMp) {
			$ov = array('multipass_max_additional_passes' => $maxAp);
		}
		list($sz, $tm) = $this->measure_folder_zip_trial($dir, $segment, $ov, $useMp);
		if($sz < $bestSize || ($sz === $bestSize && $tm < $bestTime)) {
			$bestSize = $sz;
			$bestTime = $tm;
			$winMp = $useMp;
			$winMax = $useMp ? $maxAp : null;
		}
	}
	$this->multipass = $winMp;
	$this->multipass_max_additional_passes = $winMax;
	if($debug) {
		print('auto multipass bytes-first win: multipass=');
		var_dump($winMp, $winMax, $bestSize, $bestTime);
	}
	return $winMp;
}

/**
 * Pick segment length (if auto-segment), substring_top_k, improvement threshold, and multipass gate by measuring .fzc size on folder copies.
 */
function choose_best_compression_tuning_for_folder($dir, $debug = false) {
	$this->j_curve_context_raw_bytes = $this->folder_raw_total_bytes($dir);
	$topKGrid = $this->parse_auto_tune_int_list('FRACTAL_ZIP_AUTO_TUNE_TOP_K', array(5, 7, 9, 10, 12), 1, 12);
	$impGrid = $this->parse_auto_tune_float_list('FRACTAL_ZIP_AUTO_TUNE_IMPROVEMENT', array(0.35, 0.4, 0.5, 0.65, 0.85, 1.0, 1.2), 0.01, 50.0);
	$gateGrid = $this->parse_auto_tune_float_list('FRACTAL_ZIP_AUTO_TUNE_GATE', array(1.1, 1.15, 1.2, 1.25, 1.5, 1.75, 2.0), 1.0, 4.0);
	$segmentCandidates = $this->auto_segment_selection_enabled ? $this->auto_segment_candidates : array($this->segment_length);
	$probeSeg = max(8, min(500000, (int) $this->segment_length));
	list($probeSz, $probeTm) = $this->measure_folder_zip_trial($dir, $probeSeg, null);
	$this->j_curve_context_probe_zip_sec = $probeTm;
	$this->auto_tune_fast_corpus_size_priority = fractal_zip::zip_probe_implies_fast_corpus($probeTm);
	$best_size = $probeSz;
	$best_time = $probeTm;
	$win = array(
		'segment' => $probeSeg,
		'substring_top_k' => null,
		'improvement' => $this->improvement_factor_threshold,
		'gate_mult' => null,
	);
	foreach($segmentCandidates as $seg) {
		$seg = max(8, min(500000, (int) $seg));
		if($seg === $probeSeg) {
			$sz = $probeSz;
			$tm = $probeTm;
		} else {
			list($sz, $tm) = $this->measure_folder_zip_trial($dir, $seg, null);
		}
		if($this->auto_tune_trial_is_better($sz, $tm, $best_size, $best_time)) {
			$best_size = $sz;
			$best_time = $tm;
			$win['segment'] = $seg;
		}
	}
	$bestSeg = $win['segment'];
	foreach($topKGrid as $k) {
		list($sz, $tm) = $this->measure_folder_zip_trial($dir, $bestSeg, array('substring_top_k' => $k));
		if($this->auto_tune_trial_is_better($sz, $tm, $best_size, $best_time)) {
			$best_size = $sz;
			$best_time = $tm;
			$win['substring_top_k'] = $k;
		}
	}
	$bestTopK = $win['substring_top_k'];
	foreach($impGrid as $imp) {
		foreach($gateGrid as $gate) {
			$ov = array(
				'improvement_factor_threshold' => $imp,
				'multipass_gate_mult' => $gate,
			);
			if($bestTopK !== null) {
				$ov['substring_top_k'] = $bestTopK;
			}
			list($sz, $tm) = $this->measure_folder_zip_trial($dir, $bestSeg, $ov);
			if($this->auto_tune_trial_is_better($sz, $tm, $best_size, $best_time)) {
				$best_size = $sz;
				$best_time = $tm;
				$win['improvement'] = $imp;
				$win['gate_mult'] = $gate;
			}
		}
	}
	$this->segment_length = $win['segment'];
	$this->improvement_factor_threshold = $win['improvement'];
	$this->tuning_substring_top_k = $win['substring_top_k'];
	$this->tuning_multipass_gate_mult = $win['gate_mult'];
	if($debug) {
		print('auto compression tuning win: ');var_dump($win, $best_size, $best_time);
	}
	$kStr = $this->tuning_substring_top_k === null ? 'default' : (string) $this->tuning_substring_top_k;
	$gStr = $this->tuning_multipass_gate_mult === null ? 'default' : (string) $this->tuning_multipass_gate_mult;
	print('Auto-tuned compression: segment=' . $this->segment_length . ', substring_top_k=' . $kStr . ', improvement=' . $this->improvement_factor_threshold . ', gate_mult=' . $gStr . ', best_trial .fzc=' . $best_size . ' B<br>');
}

/**
 * Optional unified-stream literal: POSIX ustar tar of one image member — outer codecs see the same byte layout as {@code tar cf - file | brotli}
 * min-ext lanes (e.g. Squash JPEG singles like test_files111). Disable with {@code FRACTAL_ZIP_LITERAL_OUTER_FZTA_TAR_JPEG=0}.
 */
public static function literal_outer_fzta_tar_jpeg_candidate_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_LITERAL_OUTER_FZTA_TAR_JPEG');
	if($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Build a GNU/POSIX-compatible ustar archive containing one regular file (basename-only path). Uses {@code tar cf -} when available (Unix).
 *
 * @return non-empty-string|false
 */
public static function posix_ustar_single_file_archive_bytes(string $relPath, string $rawBytes) {
	$relPath = str_replace('\\', '/', (string) $relPath);
	if($relPath === '' || strpos($relPath, '/') !== false || strpos($relPath, "\0") !== false || strlen($relPath) > 180) {
		return false;
	}
	if(PHP_OS_FAMILY === 'Windows') {
		return false;
	}
	$tar = trim((string) shell_exec('command -v tar 2>/dev/null'));
	if($tar === '') {
		return false;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzusta_' . bin2hex(random_bytes(8));
	if(!@mkdir($td, 0700, true)) {
		return false;
	}
	$fp = $td . DIRECTORY_SEPARATOR . $relPath;
	if(@file_put_contents($fp, $rawBytes) === false) {
		@rmdir($td);
		return false;
	}
	$out = shell_exec('cd ' . escapeshellarg($td) . ' && ' . escapeshellarg($tar) . ' cf - ' . escapeshellarg($relPath) . ' 2>/dev/null');
	@unlink($fp);
	@rmdir($td);
	return (is_string($out) && $out !== '') ? $out : false;
}

/**
 * Decode first regular-file member from a POSIX ustar stream (single-file archives from {@see posix_ustar_single_file_archive_bytes}).
 *
 * @return array{0: string, 1: string}|null [path, raw bytes]
 */
public static function posix_ustar_decode_first_regular_file(string $tar): ?array {
	$n = strlen($tar);
	if($n < 512) {
		return null;
	}
	$h = substr($tar, 0, 512);
	if(strlen($h) !== 512) {
		return null;
	}
	$type = $h[156] ?? '0';
	if($type !== '0' && $type !== "\0") {
		return null;
	}
	$name = trim(substr($h, 0, 100), "\0 ");
	if($name === '') {
		return null;
	}
	$szOct = trim(substr($h, 124, 12), " \0");
	if($szOct === '') {
		return null;
	}
	$sz = (int) octdec($szOct);
	if($sz < 0 || $sz > 2147483647) {
		return null;
	}
	if(512 + $sz > $n) {
		return null;
	}
	$raw = substr($tar, 512, $sz);

	return array($name, $raw);
}

/**
 * Member path stored in the container: relative to zip_folder root, forward slashes only.
 * When zip_folder_root_for_members is empty, returns $absolutePath unchanged (legacy behavior).
 */
function encode_single_member_raw_payload($path, $rawBytes) {
	$path = (string) $path;
	$rawBytes = (string) $rawBytes;
	return 'FZS1'
		. $this->encode_varint_u32(strlen($path)) . $path
		. $rawBytes;
}

function encode_container_payload($array_fractal_zipped_strings_of_files, $fractal_string) {
	$ordered = $array_fractal_zipped_strings_of_files;
	ksort($ordered, SORT_STRING);
	$fractal_string = (string)$fractal_string;
	$count = sizeof($ordered);
	$frLen = strlen($fractal_string);
	$sharedExt = null;
	$sharedExtLayoutOk = ($count > 0 && $count <= 255);
	if($sharedExtLayoutOk) {
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			// FZC5/FZC6 need flat basename.ext keys only (no directory prefixes); trees like test_files57/… fall through to FZC3/FZC2/FZC1.
			if(strpos($path, '/') !== false || strpos($path, '\\') !== false) {
				$sharedExtLayoutOk = false;
				break;
			}
			$dot = strrpos($path, '.');
			if($dot === false || $dot < 1 || $dot === strlen($path) - 1) {
				$sharedExtLayoutOk = false;
				break;
			}
			$ext = substr($path, $dot);
			$name = substr($path, 0, $dot);
			if(strlen($name) > 255 || strlen($ext) > 32) {
				$sharedExtLayoutOk = false;
				break;
			}
			if($sharedExt === null) {
				$sharedExt = $ext;
			} elseif($sharedExt !== $ext) {
				$sharedExtLayoutOk = false;
				break;
			}
		}
	}
	if($sharedExtLayoutOk && $sharedExt !== null) {
		$allZippedU8 = true;
		foreach($ordered as $path => $zipped) {
			if(strlen((string)$zipped) > 255) {
				$allZippedU8 = false;
				break;
			}
		}
		if($allZippedU8 && $frLen <= 255) {
			$mem = array();
			foreach($ordered as $path => $zipped) {
				$path = (string)$path;
				$zipped = (string)$zipped;
				$dot = strrpos($path, '.');
				$name = substr($path, 0, $dot);
				$mem[] = chr(strlen($name)) . $name;
				$mem[] = chr(strlen($zipped)) . $zipped;
			}
			return 'FZC5' . chr($count) . chr(strlen($sharedExt)) . $sharedExt . implode('', $mem) . chr($frLen) . $fractal_string;
		}
		// FZCT: exactly one member with shared extension ".txt" — omit ext bytes (smaller than FZC6; improves outer ratio on huge text literals).
		if($count === 1 && $sharedExt === '.txt') {
			reset($ordered);
			$pathOnly = (string)key($ordered);
			$zippedOne = (string)current($ordered);
			$dot = strrpos($pathOnly, '.');
			if($dot !== false && $dot >= 1) {
				$nameOnly = substr($pathOnly, 0, $dot);
				$nlen = strlen($nameOnly);
				if($nlen >= 1 && $nlen <= 255) {
					return 'FZCT' . chr($nlen) . $nameOnly
						. $this->encode_varint_u32(strlen($zippedOne)) . $zippedOne
						. $this->encode_varint_u32($frLen) . $fractal_string;
				}
			}
		}
		// FZCH: exactly one member with ".html" or ".htm" — same varint layout as FZCT; ext tag 0=.html / 1=.htm (smaller than FZC6 on cp.html–class corpora).
		if($count === 1 && ($sharedExt === '.html' || $sharedExt === '.htm')) {
			reset($ordered);
			$pathOnly = (string)key($ordered);
			$zippedOne = (string)current($ordered);
			$dot = strrpos($pathOnly, '.');
			if($dot !== false && $dot >= 1) {
				$nameOnly = substr($pathOnly, 0, $dot);
				$nlen = strlen($nameOnly);
				if($nlen >= 1 && $nlen <= 255) {
					$ty = ($sharedExt === '.htm') ? 1 : 0;
					return 'FZCH' . chr($ty) . chr($nlen) . $nameOnly
						. $this->encode_varint_u32(strlen($zippedOne)) . $zippedOne
						. $this->encode_varint_u32($frLen) . $fractal_string;
				}
			}
		}
		// Fixed-magic flat singles: FZC* (when A–Z/0–9 free), FZB7–0 (fonts / .map), FZBL/FZTO/FZYM/FZRS|GO|RB, etc. (same varint tail as FZCT; smaller than FZCX/FZC6).
		if($count === 1) {
			static $fzcFlatSingleExtMagics = null;
			if($fzcFlatSingleExtMagics === null) {
				$fzcFlatSingleExtMagics = array(
					'.c' => 'FZCC',
					'.lsp' => 'FZCL',
					'.protodata' => 'FZCG',
					'.pdf' => 'FZCP',
					'.xls' => 'FZCK',
					'.10K' => 'FZCU',
					'.1' => 'FZCM',
					'.js' => 'FZCB',
					'.json' => 'FZCN',
					'.xml' => 'FZCQ',
					'.css' => 'FZCS',
					'.png' => 'FZCI',
					'.gif' => 'FZCR',
					'.webp' => 'FZCV',
					'.svg' => 'FZCY',
					'.md' => 'FZCW',
					'.php' => 'FZCA',
					'.ts' => 'FZCF',
					'.mjs' => 'FZCZ',
					'.ico' => 'FZCO',
					'.vue' => 'FZC7',
					'.tsx' => 'FZC8',
					'.jsx' => 'FZC9',
					'.py' => 'FZC0',
					'.ttf' => 'FZB7',
					'.otf' => 'FZB8',
					'.eot' => 'FZB9',
					'.map' => 'FZB0',
					'.lock' => 'FZBL',
					'.toml' => 'FZTO',
					'.yml' => 'FZYM',
					'.rs' => 'FZRS',
					'.go' => 'FZGO',
					'.rb' => 'FZRB',
					'.zip' => 'FZZP',
					'.tar' => 'FZTR',
					'.gz' => 'FZGZ',
					'.7z' => 'FZ7Z',
					'.bz2' => 'FZBZ',
					'.lz4' => 'FZL4',
					'.zst' => 'FZZS',
					'.br' => 'FZBR',
					'.xz' => 'FZXZ',
					'.sql' => 'FZQL',
					'.sh' => 'FZSH',
					'.bat' => 'FZBT',
					'.ps1' => 'FZ1P',
					'.java' => 'FZJA',
					'.jar' => 'FZJR',
					'.kt' => 'FZKT',
					'.kts' => 'FZKS',
					'.h' => 'FZHI',
					'.hpp' => 'FZHP',
					'.cc' => 'FZ2C',
					'.cpp' => 'FZPP',
					'.ini' => 'FZNI',
					'.cfg' => 'FZFG',
					'.csv' => 'FZSV',
					'.tsv' => 'FZTV',
					'.diff' => 'FZDF',
					'.log' => 'FZLG',
					'.dart' => 'FZDT',
					'.r' => 'FZRL',
					'.pl' => 'FZPL',
					'.pm' => 'FZPM',
					'.cs' => 'FZSC',
					'.fs' => 'FZFS',
					'.m' => 'FZMO',
					'.mm' => 'FZMM',
					'.jl' => 'FZJL',
					'.scala' => 'FZSL',
					'.gradle' => 'FZGL',
					'.yaml' => 'FZY2',
					'.svelte' => 'FZVT',
					'.astro' => 'FZA4',
					'.mdx' => 'FZMX',
					'.gql' => 'FZGQ',
					'.less' => 'FZLE',
					'.sass' => 'FZSA',
					'.scss' => 'FZS2',
					'.styl' => 'FZST',
					'.tf' => 'FZTF',
					'.tfvars' => 'FZF2',
					'.vbs' => 'FZ3B',
					'.lua' => 'FZ3L',
					'.pug' => 'FZ3P',
					'.ejs' => 'FZ3E',
					'.hbs' => 'FZ3H',
					'.nim' => 'FZ3M',
					'.zig' => 'FZ3G',
					'.xaml' => 'FZ3X',
					'.clj' => 'FZ3C',
					'.cljs' => 'FZ2J',
					'.properties' => 'FZ8P',
					'.cmake' => 'FZ3K',
					'.cjs' => 'FZ0C',
					'.cts' => 'FZ2T',
					'.mts' => 'FZ2M',
					'.ex' => 'FZ2E',
					'.exs' => 'FZ2S',
					'.elm' => 'FZ2L',
					'.sbt' => 'FZ2B',
					'.rst' => 'FZR1',
					'.adoc' => 'FZ2A',
					'.org' => 'FZ2O',
					'.erb' => 'FZR0',
					'.haml' => 'FZ2H',
					'.slim' => 'FZ2I',
					'.groovy' => 'FZ2G',
					'.bzl' => 'FZ2Z',
					'.bazel' => 'FZ0B',
					'.graphql' => 'FZG0',
					'.swift' => 'FZSW',
					'.pod' => 'FZP0',
					'.vb' => 'FZ2V',
				);
			}
			if(isset($fzcFlatSingleExtMagics[$sharedExt])) {
				reset($ordered);
				$pathOnly = (string)key($ordered);
				$zippedOne = (string)current($ordered);
				$dot = strrpos($pathOnly, '.');
				if($dot !== false && $dot >= 1) {
					$nameOnly = substr($pathOnly, 0, $dot);
					$nlen = strlen($nameOnly);
					if($nlen >= 1 && $nlen <= 255) {
						$magic = $fzcFlatSingleExtMagics[$sharedExt];
						return $magic . chr($nlen) . $nameOnly
							. $this->encode_varint_u32(strlen($zippedOne)) . $zippedOne
							. $this->encode_varint_u32($frLen) . $fractal_string;
					}
				}
			}
			if($sharedExt === '.jpg' || $sharedExt === '.jpeg') {
				reset($ordered);
				$pathOnly = (string)key($ordered);
				$zippedOne = (string)current($ordered);
				$dot = strrpos($pathOnly, '.');
				if($dot !== false && $dot >= 1) {
					$nameOnly = substr($pathOnly, 0, $dot);
					$nlen = strlen($nameOnly);
					if($nlen >= 1 && $nlen <= 255) {
						$ty = ($sharedExt === '.jpeg') ? 1 : 0;
						return 'FZCJ' . chr($ty) . chr($nlen) . $nameOnly
							. $this->encode_varint_u32(strlen($zippedOne)) . $zippedOne
							. $this->encode_varint_u32($frLen) . $fractal_string;
					}
				}
			}
		}
		// FZCX: single flat member, u8 ext len + ext after name (2 B shorter header than FZC6 before payload; odd extensions).
		if($count === 1) {
			$xel = strlen($sharedExt);
			if($xel >= 2 && $xel <= 32 && isset($sharedExt[0]) && $sharedExt[0] === '.') {
				reset($ordered);
				$pathOnly = (string)key($ordered);
				$zippedOne = (string)current($ordered);
				$dot = strrpos($pathOnly, '.');
				if($dot !== false && $dot >= 1) {
					$nameOnly = substr($pathOnly, 0, $dot);
					$nlen = strlen($nameOnly);
					if($nlen >= 1 && $nlen <= 255) {
						return 'FZCX' . chr($nlen) . $nameOnly . chr($xel) . $sharedExt
							. $this->encode_varint_u32(strlen($zippedOne)) . $zippedOne
							. $this->encode_varint_u32($frLen) . $fractal_string;
					}
				}
			}
		}
		// FZC6: same flat shared-extension layout as FZC5, but varint value + fractal lengths (large HTML members, etc.).
		$mem = array();
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$dot = strrpos($path, '.');
			$name = substr($path, 0, $dot);
			$mem[] = chr(strlen($name)) . $name;
			$mem[] = $this->encode_varint_u32(strlen($zipped)) . $zipped;
		}
		return 'FZC6' . chr($count) . chr(strlen($sharedExt)) . $sharedExt . implode('', $mem) . $this->encode_varint_u32($frLen) . $fractal_string;
	}
	// FZCE: single flat member whose basename has no '.' (Silesia / enwik8 / ptt5 / sum; same varint tail as FZCT).
	if($count === 1) {
		reset($ordered);
		$pathOnly = (string)key($ordered);
		$zippedOne = (string)current($ordered);
		if(strpos($pathOnly, '/') === false && strpos($pathOnly, '\\') === false && strrpos($pathOnly, '.') === false) {
			$plen = strlen($pathOnly);
			if($plen >= 1 && $plen <= 255) {
				return 'FZCE' . chr($plen) . $pathOnly
					. $this->encode_varint_u32(strlen($zippedOne)) . $zippedOne
					. $this->encode_varint_u32($frLen) . $fractal_string;
			}
		}
	}
	$useUltraTiny = ($count <= 255 && $frLen <= 255);
	if($useUltraTiny) {
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			if(strlen($path) > 255 || strlen($zipped) > 255) {
				$useUltraTiny = false;
				break;
			}
		}
	}
	if($useUltraTiny) {
		$mem = array();
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$mem[] = chr(strlen($path)) . $path;
			$mem[] = chr(strlen($zipped)) . $zipped;
		}
		return 'FZC4' . chr($count) . implode('', $mem) . chr($frLen) . $fractal_string;
	}
	$useTiny = ($count <= 65535);
	if($useTiny) {
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			if(strlen($path) > 255 || strlen($zipped) > 255) {
				$useTiny = false;
				break;
			}
		}
	}
	if($useTiny) {
		$mem = array();
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$mem[] = chr(strlen($path)) . $path;
			$mem[] = chr(strlen($zipped)) . $zipped;
		}
		return 'FZC3' . pack('n', $count) . implode('', $mem) . pack('N', strlen($fractal_string)) . $fractal_string;
	}
	$useCompact = true;
	foreach($ordered as $path => $zipped) {
		$path = (string)$path;
		$zipped = (string)$zipped;
		if(strlen($path) > 65535 || strlen($zipped) > 65535) {
			$useCompact = false;
			break;
		}
	}
	if($useCompact) {
		$mem = array();
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$mem[] = pack('n', strlen($path)) . $path;
			$mem[] = pack('n', strlen($zipped)) . $zipped;
		}
		return 'FZC2' . pack('N', sizeof($ordered)) . implode('', $mem) . pack('N', strlen($fractal_string)) . $fractal_string;
	}
	$mem = array();
	foreach($ordered as $path => $zipped) {
		$path = (string)$path;
		$zipped = (string)$zipped;
		$mem[] = pack('N', strlen($path)) . $path;
		$mem[] = pack('N', strlen($zipped)) . $zipped;
	}
	return 'FZC1' . pack('N', sizeof($ordered)) . implode('', $mem) . pack('N', strlen($fractal_string)) . $fractal_string;
}

function delta_encode_bytes($raw) {
	$raw = (string)$raw;
	$n = strlen($raw);
	if($n === 0) {
		return '';
	}
	$out = str_repeat("\0", $n);
	$out[0] = $raw[0];
	$prev = ord($raw[0]);
	for($i = 1; $i < $n; $i++) {
		$cur = ord($raw[$i]);
		$d = ($cur - $prev + 256) % 256;
		$out[$i] = chr($d);
		$prev = $cur;
	}
	return $out;
}

function delta_decode_bytes($enc) {
	$enc = (string)$enc;
	$n = strlen($enc);
	if($n === 0) {
		return '';
	}
	$out = str_repeat("\0", $n);
	$out[0] = $enc[0];
	$prev = ord($enc[0]);
	for($i = 1; $i < $n; $i++) {
		$d = ord($enc[$i]);
		$cur = ($prev + $d) % 256;
		$out[$i] = chr($cur);
		$prev = $cur;
	}
	return $out;
}

/** Lossless: b[0]=a[0], b[i]=a[i]^a[i-1]. Good when adjacent bytes share bit patterns. */
function xor_adjacent_encode_bytes($raw) {
	$raw = (string)$raw;
	$n = strlen($raw);
	if($n <= 1) {
		return $raw;
	}
	$out = str_repeat("\0", $n);
	$out[0] = $raw[0];
	for($i = 1; $i < $n; $i++) {
		$out[$i] = chr(ord($raw[$i]) ^ ord($raw[$i - 1]));
	}
	return $out;
}

function xor_adjacent_decode_bytes($enc) {
	$enc = (string)$enc;
	$n = strlen($enc);
	if($n <= 1) {
		return $enc;
	}
	$out = str_repeat("\0", $n);
	$out[0] = $enc[0];
	$prev = ord($enc[0]);
	for($i = 1; $i < $n; $i++) {
		$prev = ord($enc[$i]) ^ $prev;
		$out[$i] = chr($prev);
	}
	return $out;
}

/** Lossless involution: bitwise NOT per byte (sometimes helps zlib after other stages). */
function invert_bytes($raw) {
	$raw = (string)$raw;
	$n = strlen($raw);
	$out = str_repeat("\0", $n);
	for($i = 0; $i < $n; $i++) {
		$out[$i] = chr(255 ^ ord($raw[$i]));
	}
	return $out;
}

/** Lossless involution: swap high/low nibble per byte. */
function nibble_swap_bytes($raw) {
	$raw = (string)$raw;
	$n = strlen($raw);
	$out = str_repeat("\0", $n);
	for($i = 0; $i < $n; $i++) {
		$b = ord($raw[$i]);
		$out[$i] = chr((($b & 0x0F) << 4) | (($b & 0xF0) >> 4));
	}
	return $out;
}

/**
 * Parse BMP with uncompressed pixel array (BI_RGB): BITMAPINFOHEADER or extended DIB (V4/V5), palette allowed.
 * Width/height/bpp/planes/compression are read from the standard 40-byte prefix; bfOffBits is authoritative for pixels.
 * Supported bpp: 8 (indexed), 24, 32.
 * @param bool $requireFullPixelBody when false, allow $raw shorter than full bitmap (header + compact literal tail).
 * @return array{w:int,h:int,bpp:int,pixel_off:int,row_stride:int}|null
 */
function bmp_parse_simple_uncompressed_rgb($raw, $requireFullPixelBody = true) {
	$raw = (string)$raw;
	// Four-slot MRU on (buffer identity, requireFull): literal tournaments bounce among a few BMPs.
	static $b0 = null;
	static $q0 = null;
	static $m0 = null;
	static $b1 = null;
	static $q1 = null;
	static $m1 = null;
	static $b2 = null;
	static $q2 = null;
	static $m2 = null;
	static $b3 = null;
	static $q3 = null;
	static $m3 = null;
	if($b0 === $raw && $q0 === $requireFullPixelBody) {
		return $m0;
	}
	if($b1 === $raw && $q1 === $requireFullPixelBody) {
		return $m1;
	}
	if($b2 === $raw && $q2 === $requireFullPixelBody) {
		return $m2;
	}
	if($b3 === $raw && $q3 === $requireFullPixelBody) {
		return $m3;
	}
	$n = strlen($raw);
	$ret = null;
	if($n >= 54 && $raw[0] === 'B' && $raw[1] === 'M') {
		$u = unpack('VpixelOff/VdibSize/Vw/VhSigned/vplanes/vbpp/Vcomp', $raw, 10);
		if($u !== false) {
			$pixelOff = (int)$u['pixelOff'];
			$dibSize = (int)$u['dibSize'];
			if($dibSize >= 40 && $dibSize <= 256 && 14 + $dibSize <= $n) {
				$w = (int)$u['w'];
				if($w >= 1 && $w <= 65535) {
					$hSigned = (int)$u['hSigned'];
					$h = (int)($hSigned > 0x7FFFFFFF ? $hSigned - 0x100000000 : $hSigned);
					$hAbs = $h < 0 ? -$h : $h;
					if($hAbs >= 1 && $hAbs <= 65535) {
						$planes = (int)$u['planes'];
						$bpp = (int)$u['bpp'];
						$comp = (int)$u['comp'];
						if($planes === 1 && $comp === 0 && ($bpp === 8 || $bpp === 24 || $bpp === 32)) {
							if($pixelOff >= 14 + $dibSize && $pixelOff <= $n) {
								$rowStride = (int)((((int)$w * (int)$bpp + 31) >> 5) << 2);
								if($rowStride >= 1) {
									$bodyLen = $n - $pixelOff;
									if($bodyLen >= 0) {
										$expectedBody = $rowStride * $hAbs;
										if(!$requireFullPixelBody || $bodyLen >= $expectedBody) {
											$ret = array('w' => (int)$w, 'h' => $hAbs, 'bpp' => (int)$bpp, 'pixel_off' => (int)$pixelOff, 'row_stride' => $rowStride);
										}
									}
								}
							}
						}
					}
				}
			}
		}
	}
	$b3 = $b2;
	$q3 = $q2;
	$m3 = $m2;
	$b2 = $b1;
	$q2 = $q1;
	$m2 = $m1;
	$b1 = $b0;
	$q1 = $q0;
	$m1 = $m0;
	$b0 = $raw;
	$q0 = $requireFullPixelBody;
	$m0 = $ret;
	return $ret;
}

/** Per-row first-byte anchor, then byte -= previous byte in row (mod 256). */
function delta_encode_rows_horizontal($body, $rowStride) {
	$body = (string)$body;
	$rowStride = (int)$rowStride;
	$n = strlen($body);
	if($rowStride < 2 || $n === 0) {
		return $body;
	}
	$out = str_repeat("\0", $n);
	for($o = 0; $o < $n; $o += $rowStride) {
		$lr = $rowStride;
		if($o + $lr > $n) {
			$lr = $n - $o;
		}
		if($lr === 0) {
			break;
		}
		$out[$o] = $body[$o];
		for($i = 1; $i < $lr; $i++) {
			$out[$o + $i] = chr((ord($body[$o + $i]) - ord($body[$o + $i - 1])) & 255);
		}
	}
	return $out;
}

function delta_decode_rows_horizontal($body, $rowStride) {
	$body = (string)$body;
	$rowStride = (int)$rowStride;
	$n = strlen($body);
	if($rowStride < 2 || $n === 0) {
		return $body;
	}
	$out = str_repeat("\0", $n);
	for($o = 0; $o < $n; $o += $rowStride) {
		$lr = $rowStride;
		if($o + $lr > $n) {
			$lr = $n - $o;
		}
		if($lr === 0) {
			break;
		}
		$prev = ord($body[$o]);
		$out[$o] = $body[$o];
		for($i = 1; $i < $lr; $i++) {
			$prev = ($prev + ord($body[$o + $i])) & 255;
			$out[$o + $i] = chr($prev);
		}
	}
	return $out;
}

/**
 * BMP: keep header through bfOffBits raw; row-wise horizontal delta on pixel bytes (+ optional tail after bitmap).
 * @param array{w:int,h:int,bpp:int,pixel_off:int,row_stride:int}|null $bmpMeta optional bmp_parse_simple_uncompressed_rgb($rawBytes) for same blob (avoids duplicate parse on hot literal path).
 * @return string|null stored form, or null if not a supported BMP
 */
function encode_bmp_row_horizontal_delta_payload($rawBytes, array $bmpMeta = null) {
	$rawBytes = (string)$rawBytes;
	if($bmpMeta === null) {
		$bmpMeta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes);
	}
	$meta = $bmpMeta;
	if($meta === null) {
		return null;
	}
	$po = $meta['pixel_off'];
	$rs = $meta['row_stride'];
	$h = $meta['h'];
	$pixBytes = $rs * $h;
	$head = substr($rawBytes, 0, $po);
	$rest = substr($rawBytes, $po);
	$body = substr($rest, 0, $pixBytes);
	$tail = substr($rest, $pixBytes);
	$encBody = $this->delta_encode_rows_horizontal($body, $rs);
	return $head . $encBody . $tail;
}

function decode_bmp_row_horizontal_delta_payload($rawStored) {
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawStored);
	if($meta === null) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 5): not a supported BMP.');
	}
	$po = $meta['pixel_off'];
	$rs = $meta['row_stride'];
	$h = $meta['h'];
	$pixBytes = $rs * $h;
	$head = substr($rawStored, 0, $po);
	$rest = substr($rawStored, $po);
	$tbody = substr($rest, 0, $pixBytes);
	$tail = substr($rest, $pixBytes);
	$body = $this->delta_decode_rows_horizontal($tbody, $rs);
	return $head . $body . $tail;
}

/**
 * Per-column vertical delta: row 0 is anchor; for r>0, body[r*rs+c] -= body[(r-1)*rs+c] (mod 256).
 */
function delta_encode_columns_vertical($body, $rowStride, $h) {
	$body = (string) $body;
	$rowStride = (int) $rowStride;
	$h = (int) $h;
	$need = $rowStride * $h;
	if($rowStride < 1 || $h < 1 || strlen($body) < $need) {
		return $body;
	}
	$out = str_repeat("\0", $need);
	for($c = 0; $c < $rowStride; $c++) {
		$out[$c] = $body[$c];
	}
	for($r = 1; $r < $h; $r++) {
		$ro = $r * $rowStride;
		$roPrev = $ro - $rowStride;
		for($c = 0; $c < $rowStride; $c++) {
			$out[$ro + $c] = chr((ord($body[$ro + $c]) - ord($body[$roPrev + $c])) & 255);
		}
	}
	return $out;
}

function delta_decode_columns_vertical($body, $rowStride, $h) {
	$body = (string) $body;
	$rowStride = (int) $rowStride;
	$h = (int) $h;
	$need = $rowStride * $h;
	if($rowStride < 1 || $h < 1 || strlen($body) < $need) {
		return $body;
	}
	$out = str_repeat("\0", $need);
	for($c = 0; $c < $rowStride; $c++) {
		$out[$c] = $body[$c];
	}
	for($r = 1; $r < $h; $r++) {
		$ro = $r * $rowStride;
		$roPrev = $ro - $rowStride;
		for($c = 0; $c < $rowStride; $c++) {
			$out[$ro + $c] = chr((ord($out[$roPrev + $c]) + ord($body[$ro + $c])) & 255);
		}
	}
	return $out;
}

/**
 * BMP: same header/tail as mode 5; column-vertical delta on pixel body (complements row-horizontal mode 5).
 * @param array{w:int,h:int,bpp:int,pixel_off:int,row_stride:int}|null $bmpMeta optional pre-parsed BMP meta for same $rawBytes.
 * @return string|null
 */
function encode_bmp_column_vertical_delta_payload($rawBytes, array $bmpMeta = null) {
	$rawBytes = (string)$rawBytes;
	if($bmpMeta === null) {
		$bmpMeta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes);
	}
	$meta = $bmpMeta;
	if($meta === null) {
		return null;
	}
	$po = $meta['pixel_off'];
	$rs = $meta['row_stride'];
	$h = $meta['h'];
	$pixBytes = $rs * $h;
	$head = substr($rawBytes, 0, $po);
	$rest = substr($rawBytes, $po);
	$body = substr($rest, 0, $pixBytes);
	$tail = substr($rest, $pixBytes);
	$encBody = $this->delta_encode_columns_vertical($body, $rs, $h);
	return $head . $encBody . $tail;
}

function decode_bmp_column_vertical_delta_payload($rawStored) {
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawStored);
	if($meta === null) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 13): not a supported BMP.');
	}
	$po = $meta['pixel_off'];
	$rs = $meta['row_stride'];
	$h = $meta['h'];
	$pixBytes = $rs * $h;
	$head = substr($rawStored, 0, $po);
	$rest = substr($rawStored, $po);
	$tbody = substr($rest, 0, $pixBytes);
	$tail = substr($rest, $pixBytes);
	$body = $this->delta_decode_columns_vertical($tbody, $rs, $h);
	return $head . $body . $tail;
}

/**
 * Mode 14: treat the first s×s bytes (s = floor(sqrt(n)), s ≥ 2) as a row-major s×s matrix and transpose; remainder of
 * buffer unchanged. Transpose is self-inverse, so the same operation decodes. Returns null if n < 4.
 * @return string|null
 */
function literal_square_block_transpose_prefix($raw) {
	$raw = (string) $raw;
	$n = strlen($raw);
	if($n < 4) {
		return null;
	}
	$s = (int) floor(sqrt((float) $n));
	if($s < 2) {
		return null;
	}
	$sq = $s * $s;
	$head = substr($raw, 0, $sq);
	$tail = substr($raw, $sq);
	$o = str_repeat("\0", $sq);
	for($r = 0; $r < $s; $r++) {
		$base = $r * $s;
		for($c = 0; $c < $s; $c++) {
			$o[$base + $c] = $head[$c * $s + $r];
		}
	}
	return $o . $tail;
}

/**
 * BMP BSS1: per-cell scalar (same uint8 added mod 256 to every byte in each pixel) vs top-left cell template.
 * @return int|null uint8 delta applied to every byte in the cell, or null
 */
function bmp_bss_cell_scalar_delta($refBlock, $cellBlock, $bppBytes) {
	$refBlock = (string)$refBlock;
	$cellBlock = (string)$cellBlock;
	$nb = strlen($refBlock);
	if($nb !== strlen($cellBlock) || $nb < 1 || ($nb % $bppBytes) !== 0) {
		return null;
	}
	$rb = $refBlock;
	$cb = $cellBlock;
	// Direct byte scan — avoids unpack('C*') allocating two O(nb) arrays per call (hot BSS path).
	$d = (ord($cb[0]) - ord($rb[0])) & 255;
	for($i = 0; $i < $bppBytes; $i++) {
		if(((ord($cb[$i]) - ord($rb[$i])) & 255) !== $d) {
			return null;
		}
	}
	if($bppBytes === 3) {
		for($p = 3; $p + 12 <= $nb; $p += 12) {
			$dp0 = (ord($cb[$p]) - ord($rb[$p])) & 255;
			if($dp0 !== $d) {
				return null;
			}
			if(((ord($cb[$p + 1]) - ord($rb[$p + 1])) & 255) !== $d || ((ord($cb[$p + 2]) - ord($rb[$p + 2])) & 255) !== $d) {
				return null;
			}
			$q = $p + 3;
			$dp0 = (ord($cb[$q]) - ord($rb[$q])) & 255;
			if($dp0 !== $d) {
				return null;
			}
			if(((ord($cb[$q + 1]) - ord($rb[$q + 1])) & 255) !== $d || ((ord($cb[$q + 2]) - ord($rb[$q + 2])) & 255) !== $d) {
				return null;
			}
			$q = $p + 6;
			$dp0 = (ord($cb[$q]) - ord($rb[$q])) & 255;
			if($dp0 !== $d) {
				return null;
			}
			if(((ord($cb[$q + 1]) - ord($rb[$q + 1])) & 255) !== $d || ((ord($cb[$q + 2]) - ord($rb[$q + 2])) & 255) !== $d) {
				return null;
			}
			$q = $p + 9;
			$dp0 = (ord($cb[$q]) - ord($rb[$q])) & 255;
			if($dp0 !== $d) {
				return null;
			}
			if(((ord($cb[$q + 1]) - ord($rb[$q + 1])) & 255) !== $d || ((ord($cb[$q + 2]) - ord($rb[$q + 2])) & 255) !== $d) {
				return null;
			}
		}
		for(; $p < $nb; $p += 3) {
			$dp = (ord($cb[$p]) - ord($rb[$p])) & 255;
			if($dp !== $d) {
				return null;
			}
			if(((ord($cb[$p + 1]) - ord($rb[$p + 1])) & 255) !== $d || ((ord($cb[$p + 2]) - ord($rb[$p + 2])) & 255) !== $d) {
				return null;
			}
		}
	} else {
		for($p = $bppBytes; $p < $nb; $p += $bppBytes) {
			$dp = (ord($cb[$p]) - ord($rb[$p])) & 255;
			if($dp !== $d) {
				return null;
			}
			for($j = 1; $j < $bppBytes; $j++) {
				$pj = $p + $j;
				if(((ord($cb[$pj]) - ord($rb[$pj])) & 255) !== $d) {
					return null;
				}
			}
		}
	}
	return $d;
}

/**
 * Per-cell constant BGR triplet (add mod 256 per channel) vs reference cell; 24 bpp only.
 * Out-params avoid allocating a 3-tuple array on every call (hot BSS fallback path).
 *
 * @param-out int|null $outDb
 * @param-out int|null $outDg
 * @param-out int|null $outDr
 */
function bmp_bss_cell_bgr_triple_delta($refBlock, $cellBlock, &$outDb, &$outDg, &$outDr) {
	$refBlock = (string)$refBlock;
	$cellBlock = (string)$cellBlock;
	$nb = strlen($refBlock);
	if($nb !== strlen($cellBlock) || $nb < 3 || ($nb % 3) !== 0) {
		$outDb = null;
		$outDg = null;
		$outDr = null;
		return;
	}
	$rb = $refBlock;
	$cb = $cellBlock;
	$db = (ord($cb[0]) - ord($rb[0])) & 255;
	$dg = (ord($cb[1]) - ord($rb[1])) & 255;
	$dr = (ord($cb[2]) - ord($rb[2])) & 255;
	// For nb ≥ 12, skip the first triplet — it matches ($db,$dg,$dr) by construction.
	$p0 = ($nb >= 12) ? 3 : 0;
	for($p = $p0; $p + 24 <= $nb; $p += 24) {
		// Eight BGR triplets unrolled (was k=0..21 step 3).
		$t = $p;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$t = $p + 3;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$t = $p + 6;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$t = $p + 9;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$t = $p + 12;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$t = $p + 15;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$t = $p + 18;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$t = $p + 21;
		if(((ord($cb[$t]) - ord($rb[$t])) & 255) !== $db
			|| ((ord($cb[$t + 1]) - ord($rb[$t + 1])) & 255) !== $dg
			|| ((ord($cb[$t + 2]) - ord($rb[$t + 2])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
	}
	for(; $p < $nb; $p += 3) {
		$q = $p + 1;
		$s = $p + 2;
		if(((ord($cb[$p]) - ord($rb[$p])) & 255) !== $db) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		if(((ord($cb[$q]) - ord($rb[$q])) & 255) !== $dg) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		if(((ord($cb[$s]) - ord($rb[$s])) & 255) !== $dr) {
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
	}
	$outDb = $db;
	$outDg = $dg;
	$outDr = $dr;
}

/**
 * 24 bpp only: scalar shift (same uint8 on every byte) and BGR triple shift vs the same reference block.
 * Single scan for all valid sizes (nb ≥ 3); 12-byte unrolled steps on the hot BSS1/BSS2 path (no unpack arrays).
 * Writes results via out-params to avoid allocating pair/triplet arrays on every call (hot BSS path).
 *
 * @param-out int|null $outScalar same-byte delta, or null if not uniform
 * @param-out int|null $outDb
 * @param-out int|null $outDg
 * @param-out int|null $outDr BGR channel deltas when constant triplet holds; any null if not
 */
function bmp_bss_cell_scalar_and_bgr_triple_delta($refBlock, $cellBlock, &$outScalar, &$outDb, &$outDg, &$outDr) {
	$refBlock = (string)$refBlock;
	$cellBlock = (string)$cellBlock;
	$nb = strlen($refBlock);
	if($nb !== strlen($cellBlock) || $nb < 3 || ($nb % 3) !== 0) {
		$outScalar = null;
		$outDb = null;
		$outDg = null;
		$outDr = null;
		return;
	}
	$r = $refBlock;
	$c = $cellBlock;
	$d = (ord($c[0]) - ord($r[0])) & 255;
	$db = $d;
	$dg = (ord($c[1]) - ord($r[1])) & 255;
	$dr = (ord($c[2]) - ord($r[2])) & 255;
	$scalarOk = ($dg === $d && $dr === $d);
	$bgrOk = true;
	// Skip first triplet — already reflected in $d, $scalarOk, $db/$dg/$dr.
	// Two 12-byte chunks per outer step (24 B) cuts loop back-edges vs stepping by 12 only (hot path: test_files64 BMP cores).
	for($p = 3; $p + 24 <= $nb; $p += 24) {
		$b0 = (ord($c[$p]) - ord($r[$p])) & 255;
		$b1 = (ord($c[$p + 1]) - ord($r[$p + 1])) & 255;
		$b2 = (ord($c[$p + 2]) - ord($r[$p + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$q = $p + 3;
		$b0 = (ord($c[$q]) - ord($r[$q])) & 255;
		$b1 = (ord($c[$q + 1]) - ord($r[$q + 1])) & 255;
		$b2 = (ord($c[$q + 2]) - ord($r[$q + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$q = $p + 6;
		$b0 = (ord($c[$q]) - ord($r[$q])) & 255;
		$b1 = (ord($c[$q + 1]) - ord($r[$q + 1])) & 255;
		$b2 = (ord($c[$q + 2]) - ord($r[$q + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$q = $p + 9;
		$b0 = (ord($c[$q]) - ord($r[$q])) & 255;
		$b1 = (ord($c[$q + 1]) - ord($r[$q + 1])) & 255;
		$b2 = (ord($c[$q + 2]) - ord($r[$q + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$q = $p + 12;
		$b0 = (ord($c[$q]) - ord($r[$q])) & 255;
		$b1 = (ord($c[$q + 1]) - ord($r[$q + 1])) & 255;
		$b2 = (ord($c[$q + 2]) - ord($r[$q + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$q = $p + 15;
		$b0 = (ord($c[$q]) - ord($r[$q])) & 255;
		$b1 = (ord($c[$q + 1]) - ord($r[$q + 1])) & 255;
		$b2 = (ord($c[$q + 2]) - ord($r[$q + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$q = $p + 18;
		$b0 = (ord($c[$q]) - ord($r[$q])) & 255;
		$b1 = (ord($c[$q + 1]) - ord($r[$q + 1])) & 255;
		$b2 = (ord($c[$q + 2]) - ord($r[$q + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
		$q = $p + 21;
		$b0 = (ord($c[$q]) - ord($r[$q])) & 255;
		$b1 = (ord($c[$q + 1]) - ord($r[$q + 1])) & 255;
		$b2 = (ord($c[$q + 2]) - ord($r[$q + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			$outScalar = null;
			$outDb = null;
			$outDg = null;
			$outDr = null;
			return;
		}
	}
	for(; $p < $nb; $p += 3) {
		$b0 = (ord($c[$p]) - ord($r[$p])) & 255;
		$b1 = (ord($c[$p + 1]) - ord($r[$p + 1])) & 255;
		$b2 = (ord($c[$p + 2]) - ord($r[$p + 2])) & 255;
		if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
			$scalarOk = false;
		}
		if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
			$bgrOk = false;
		}
		if(!$scalarOk && !$bgrOk) {
			break;
		}
	}
	$outScalar = $scalarOk ? $d : null;
	if($bgrOk) {
		$outDb = $db;
		$outDg = $dg;
		$outDr = $dr;
	} else {
		$outDb = null;
		$outDg = null;
		$outDr = null;
	}
}

/**
 * Extract one cell from BMP pixel body (top-left visual origin), row-major within the cell.
 */
function bmp_bss_extract_cell($pixBody, $hAbs, $rowStride, $bc, $br, $cw, $ch, $bppBytes) {
	$rowSpan = $cw * $bppBytes;
	$xOff = $bc * $cw * $bppBytes;
	$sy = $hAbs - 1 - $br * $ch;
	$o = $sy * $rowStride + $xOff;
	$rows = array();
	for($k = 0; $k < $ch; $k++) {
		$rows[] = substr($pixBody, $o, $rowSpan);
		$o -= $rowStride;
	}
	return implode('', $rows);
}

/**
 * Core sub-rectangle inside one cell: margin pixels inset on each side (e.g. 24×24 cell, margin 1 → 22×22 core).
 * Row-major BGR (or bpp) within the core, same visual order as bmp_bss_extract_cell for those pixels.
 * @return string|null
 */
function bmp_bss_extract_cell_core($pixBody, $hAbs, $rowStride, $bc, $br, $cw, $ch, $bppBytes, $margin) {
	$margin = (int)$margin;
	$iw = $cw - 2 * $margin;
	$ih = $ch - 2 * $margin;
	if($iw < 1 || $ih < 1 || $margin < 1) {
		return null;
	}
	$rowSpan = $iw * $bppBytes;
	$xOff = ($bc * $cw + $margin) * $bppBytes;
	$sy = $hAbs - 1 - $br * $ch - $margin;
	$o = $sy * $rowStride + $xOff;
	$rows = array();
	for($ly = $margin; $ly < $ch - $margin; $ly++) {
		$rows[] = substr($pixBody, $o, $rowSpan);
		$o -= $rowStride;
	}
	return implode('', $rows);
}

/**
 * Compare one BSS2 core directly in pixel buffer against the reference core at cell (0,0), without materializing core strings.
 * @param-out int|null $outScalar
 * @param-out int|null $outDb
 * @param-out int|null $outDg
 * @param-out int|null $outDr
 * @return bool false when geometry is invalid
 */
function bmp_bss_core_deltas_against_ref_from_pix($pixBody, $hAbs, $rowStride, $bc, $br, $cw, $ch, $bppBytes, $margin, &$outScalar, &$outDb, &$outDg, &$outDr, $iwPre = null, $ihPre = null, $rowSpanPre = null, $offRef0Pre = null, $wantScalar = true, $wantBgr = true) {
	$margin = (int)$margin;
	$iw = ($iwPre === null) ? ($cw - 2 * $margin) : (int)$iwPre;
	$ih = ($ihPre === null) ? ($ch - 2 * $margin) : (int)$ihPre;
	if($iw < 1 || $ih < 1 || $margin < 0) {
		$outScalar = null;
		$outDb = null;
		$outDg = null;
		$outDr = null;
		return false;
	}
	$rowSpan = ($rowSpanPre === null) ? ($iw * $bppBytes) : (int)$rowSpanPre;
	$xCur = ($bc * $cw + $margin) * $bppBytes;
	$syCur = $hAbs - 1 - $br * $ch - $margin;
	if($offRef0Pre === null) {
		$xRef = $margin * $bppBytes;
		$syRef = $hAbs - 1 - $margin;
		$offRef0 = $syRef * $rowStride + $xRef;
	} else {
		$offRef0 = (int)$offRef0Pre;
	}
	$offCur0 = $syCur * $rowStride + $xCur;
	$pb = $pixBody;
	$d = (ord($pixBody[$offCur0]) - ord($pixBody[$offRef0])) & 255;
	if($bppBytes === 3) {
		$db = $d;
		$dg = (ord($pb[$offCur0 + 1]) - ord($pb[$offRef0 + 1])) & 255;
		$dr = (ord($pb[$offCur0 + 2]) - ord($pb[$offRef0 + 2])) & 255;
		$scalarOk = $wantScalar && ($dg === $d && $dr === $d);
		$bgrOk = $wantBgr;
		$offRef = $offRef0;
		$offCur = $offCur0;
		for($ry = 0; $ry < $ih; $ry++) {
			for($x = 0; $x < $rowSpan; $x += 3) {
				$oRef = $offRef + $x;
				$oCur = $offCur + $x;
				$b0 = (ord($pb[$oCur]) - ord($pb[$oRef])) & 255;
				$b1 = (ord($pb[$oCur + 1]) - ord($pb[$oRef + 1])) & 255;
				$b2 = (ord($pb[$oCur + 2]) - ord($pb[$oRef + 2])) & 255;
				if($scalarOk && ($b0 !== $d || $b1 !== $d || $b2 !== $d)) {
					$scalarOk = false;
				}
				if($bgrOk && ($b0 !== $db || $b1 !== $dg || $b2 !== $dr)) {
					$bgrOk = false;
				}
				if(!$scalarOk && !$bgrOk) {
					$outScalar = null;
					$outDb = null;
					$outDg = null;
					$outDr = null;
					return true;
				}
			}
			$offRef -= $rowStride;
			$offCur -= $rowStride;
		}
		$outScalar = $scalarOk ? $d : null;
		if($bgrOk) {
			$outDb = $db;
			$outDg = $dg;
			$outDr = $dr;
		} else {
			$outDb = null;
			$outDg = null;
			$outDr = null;
		}
		return true;
	}
	$offRef = $offRef0;
	$offCur = $offCur0;
	for($ry = 0; $ry < $ih; $ry++) {
		for($x = 0; $x < $rowSpan; $x++) {
			if(((ord($pb[$offCur + $x]) - ord($pb[$offRef + $x])) & 255) !== $d) {
				$outScalar = null;
				$outDb = null;
				$outDg = null;
				$outDr = null;
				return true;
			}
		}
		$offRef -= $rowStride;
		$offCur -= $rowStride;
	}
	$outScalar = $d;
	$outDb = null;
	$outDg = null;
	$outDr = null;
	return true;
}

/**
 * All cell-border (non-core) pixels in BMP body scan order: top image row (vy=0) left-to-right, then next row — matches decode.
 */
function bmp_bss_rim_body_raster_concat($pixBody, $hAbs, $rowStride, $w, $cw, $ch, $bppBytes, $margin) {
	$margin = (int)$margin;
	$cwInMax = $cw - $margin;
	$chInMax = $ch - $margin;
	$marginBytes = $margin * $bppBytes;
	$cwBytes = $cw * $bppBytes;
	$cwInMaxBytes = $cwInMax * $bppBytes;
	$out = '';
	$fullCellsPerRow = intdiv($w, $cw);
	$fastPath = ($fullCellsPerRow > 0 && ($fullCellsPerRow * $cw) === $w && $cwInMax > $margin);
	for($vy = 0; $vy < $hAbs; $vy++) {
		$sy = $hAbs - 1 - $vy;
		$rowBase = $sy * $rowStride;
		$br = intdiv($vy, $ch);
		$ly = $vy - $br * $ch;
		$lyInCoreBand = ($ly >= $margin && $ly < $chInMax);
		if($fastPath && $lyInCoreBand) {
			$leftOff = $rowBase;
			for($bc = 0; $bc < $fullCellsPerRow; $bc++) {
				$out .= substr($pixBody, $leftOff, $marginBytes);
				$rightOff = $leftOff + $cwInMaxBytes;
				$out .= substr($pixBody, $rightOff, $marginBytes);
				$leftOff += $cwBytes;
			}
			continue;
		}
		$runVx0 = null;
		$runVx1 = null;
		$lx = 0;
		for($vx = 0; $vx < $w; $vx++) {
			$inCore = $lyInCoreBand && ($lx >= $margin && $lx < $cwInMax);
			if($inCore) {
				if($runVx0 !== null) {
					$len = ($runVx1 - $runVx0 + 1) * $bppBytes;
					$o = $rowBase + $runVx0 * $bppBytes;
					$out .= substr($pixBody, $o, $len);
					$runVx0 = null;
					$runVx1 = null;
				}
				continue;
			}
			if($runVx0 === null) {
				$runVx0 = $vx;
				$runVx1 = $vx;
			} elseif($vx === $runVx1 + 1) {
				$runVx1 = $vx;
			} else {
				if($runVx0 !== null) {
					$len = ($runVx1 - $runVx0 + 1) * $bppBytes;
					$o = $rowBase + $runVx0 * $bppBytes;
					$out .= substr($pixBody, $o, $len);
					$runVx0 = null;
					$runVx1 = null;
				}
				$runVx0 = $vx;
				$runVx1 = $vx;
			}
			$lx++;
			if($lx === $cw) {
				$lx = 0;
			}
		}
		if($runVx0 !== null) {
			$len = ($runVx1 - $runVx0 + 1) * $bppBytes;
			$o = $rowBase + $runVx0 * $bppBytes;
			$out .= substr($pixBody, $o, $len);
			$runVx0 = null;
			$runVx1 = null;
		}
	}
	return $out;
}

/**
 * Mode 15 stored form: original BMP header [0 .. pixel_off-1] + BSS1 + u8 kind + varint ncol + varint nrow + template + deltas.
 * kind=0: ncol×nrow bytes — same uint8 added to every byte in each cell vs the top-left template.
 * kind=1 (24 bpp only): ncol×nrow×3 bytes — constant (dB,dG,dR) per cell vs template (independent channel shifts).
 * Disable probing: FRACTAL_ZIP_LITERAL_BMP_BLOCK_SHIFT=0.
 * @param array{w:int,h:int,bpp:int,pixel_off:int,row_stride:int}|null $bmpMeta optional pre-parsed bmp_parse_simple_uncompressed_rgb($rawBytes, true).
 * @return string|null compact stored blob, or null if no grid factorization fits
 */
function encode_bmp_block_scalar_shift_payload($rawBytes, array $bmpMeta = null) {
	static $bssShiftDisabled = null;
	if($bssShiftDisabled === null) {
		$env = getenv('FRACTAL_ZIP_LITERAL_BMP_BLOCK_SHIFT');
		$bssShiftDisabled = ($env !== false && trim((string)$env) !== '' && (int)$env === 0);
	}
	if($bssShiftDisabled) {
		return null;
	}
	$rawBytes = (string)$rawBytes;
	if($bmpMeta === null) {
		$bmpMeta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
	}
	$meta = $bmpMeta;
	if($meta === null) {
		return null;
	}
	$bpp = $meta['bpp'];
	$bppBytes = (int)(($bpp + 7) >> 3);
	if($bppBytes < 1 || $bppBytes > 4) {
		return null;
	}
	$w = $meta['w'];
	$hAbs = $meta['h'];
	$po = $meta['pixel_off'];
	$rs = $meta['row_stride'];
	$body = substr($rawBytes, $po);
	$needBody = $rs * $hAbs;
	if(strlen($body) < $needBody) {
		return null;
	}
	$tail = substr($body, $needBody);
	$pix = substr($body, 0, $needBody);
	$rawLen = strlen($rawBytes);
	$storedFixedLen = $po + strlen($tail);
	$best = null;
	$bestLen = PHP_INT_MAX;
	for($cw = 2; $cw <= $w; $cw++) {
		if($w % $cw !== 0) {
			continue;
		}
		$ncol = (int)($w / $cw);
		for($ch = 2; $ch <= $hAbs; $ch++) {
			if($hAbs % $ch !== 0) {
				continue;
			}
			$nrow = (int)($hAbs / $ch);
			if($ncol * $nrow < 2) {
				continue;
			}
			$tplSz = $cw * $ch * $bppBytes;
			$ref = $this->bmp_bss_extract_cell($pix, $hAbs, $rs, 0, 0, $cw, $ch, $bppBytes);
			if($ref === null || strlen($ref) !== $tplSz) {
				continue;
			}
			$encNcol = $this->encode_varint_u32($ncol);
			$encNrow = $this->encode_varint_u32($nrow);
			$nCells = $ncol * $nrow;
			$payloadMinScalar = 5 + strlen($encNcol) + strlen($encNrow) + $tplSz + $nCells;
			$scalarViable = ($storedFixedLen + $payloadMinScalar < $bestLen && $storedFixedLen + $payloadMinScalar < $rawLen);
			$bgrViable = false;
			if($bpp === 24) {
				$payloadMinBgr = 5 + strlen($encNcol) + strlen($encNrow) + $tplSz + 3 * $nCells;
				$bgrViable = ($storedFixedLen + $payloadMinBgr < $bestLen && $storedFixedLen + $payloadMinBgr < $rawLen);
			}
			if(!$scalarViable && !$bgrViable) {
				continue;
			}
			$iw = $cw;
			$ih = $ch;
			$rowSpan = $iw * $bppBytes;
			$xRef = 0;
			$syRef = $hAbs - 1;
			$offRef0 = $syRef * $rs + $xRef;
			// Direct pixel-buffer compare per cell (scalar + optional BGR) avoids materializing full-cell strings per candidate.
			$deltas0 = str_repeat("\0", $nCells);
			$ok0 = $scalarViable;
			$ok1 = $bgrViable;
			$deltas1 = $ok1 ? str_repeat("\0", 3 * $nCells) : '';
			// Cell (0,0) is always zero delta vs itself; avoid one helper call per candidate.
			$di = 1;
			for($br = 0; $br < $nrow; $br++) {
				$bcStart = ($br === 0) ? 1 : 0;
				for($bc = $bcStart; $bc < $ncol; $bc++) {
					if(!$this->bmp_bss_core_deltas_against_ref_from_pix($pix, $hAbs, $rs, $bc, $br, $cw, $ch, $bppBytes, 0, $scD, $bgrB, $bgrG, $bgrR, $iw, $ih, $rowSpan, $offRef0, $ok0, $ok1)) {
						$ok0 = false;
						$ok1 = false;
						break 2;
					}
					if($ok0) {
						if($scD === null) {
							$ok0 = false;
						} else {
							$deltas0[$di] = chr($scD);
						}
					}
					if($ok1) {
						if($bgrB === null) {
							$ok1 = false;
						} else {
							$o3 = 3 * $di;
							$deltas1[$o3] = chr($bgrB);
							$deltas1[$o3 + 1] = chr($bgrG);
							$deltas1[$o3 + 2] = chr($bgrR);
						}
					}
					$di++;
					if(!$ok0 && !$ok1) {
						break 2;
					}
				}
			}
			if($ok0) {
				$payload = 'BSS1' . chr(0) . $encNcol . $encNrow . $ref . $deltas0;
				$stored = substr($rawBytes, 0, $po) . $payload . $tail;
				$L = strlen($stored);
				if($L < $bestLen && $L < $rawLen) {
					$bestLen = $L;
					$best = $stored;
				}
			}
			if($ok1) {
				$payload = 'BSS1' . chr(1) . $encNcol . $encNrow . $ref . $deltas1;
				$stored = substr($rawBytes, 0, $po) . $payload . $tail;
				$L = strlen($stored);
				if($L < $bestLen && $L < $rawLen) {
					$bestLen = $L;
					$best = $stored;
				}
			}
		}
	}
	return $best;
}

/**
 * BMP BSS2: like BSS1 (mode 15) but template is only the cell core (cw−2m)×(ch−2m); per-cell scalar or BGR shift applies
 * only there; cell rims (border pixels) are stored verbatim after the deltas.
 * Stored after pixel_off: BSS2 + u8 kind + varint ncol + varint nrow + varint cw + varint ch + varint margin + template + deltas + rim.
 * kind=0: ncol×nrow bytes; kind=1 (24 bpp): ncol×nrow×3 bytes (dB,dG,dR vs template, mod 256). Disable: FRACTAL_ZIP_LITERAL_BMP_BLOCK_SHIFT_CORE=0.
 *
 * Lossless size sketch (24 bpp, ncol×nrow cells, cw×ch px, margin m, iw=cw−2m, ih=ch−2m):
 *   payload ≈ iw×ih×3 + ncol×nrow×(kind?3:1) + ncol×nrow×(cw×ch−iw×ih)×3  (+ small varints).
 * Example 12×8 × 24×24, m=1: 22×22 core → 484/576 ≈ 84% of cell pixels from template+3 B/cell; rims are the other ~16% × 3 B/px, stored raw.
 * Encoder minimizes stored length over (grid, m, kind); a larger m may win when constant shift only holds on a smaller core (e.g. anti-aliased grids).
 * See examples/bss2_byte_math.php.
 * @param array{w:int,h:int,bpp:int,pixel_off:int,row_stride:int}|null $bmpMeta optional pre-parsed bmp_parse_simple_uncompressed_rgb($rawBytes, true).
 * @return string|null
 */
function encode_bmp_block_core_scalar_shift_payload($rawBytes, array $bmpMeta = null) {
	static $bssCoreDisabled = null;
	if($bssCoreDisabled === null) {
		$env = getenv('FRACTAL_ZIP_LITERAL_BMP_BLOCK_SHIFT_CORE');
		$bssCoreDisabled = ($env !== false && trim((string)$env) !== '' && (int)$env === 0);
	}
	if($bssCoreDisabled) {
		return null;
	}
	$rawBytes = (string)$rawBytes;
	if($bmpMeta === null) {
		$bmpMeta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
	}
	$meta = $bmpMeta;
	if($meta === null) {
		return null;
	}
	$bpp = $meta['bpp'];
	$bppBytes = (int)(($bpp + 7) >> 3);
	if($bppBytes < 1 || $bppBytes > 4) {
		return null;
	}
	$w = $meta['w'];
	$hAbs = $meta['h'];
	$po = $meta['pixel_off'];
	$rs = $meta['row_stride'];
	$body = substr($rawBytes, $po);
	$needBody = $rs * $hAbs;
	if(strlen($body) < $needBody) {
		return null;
	}
	$tail = substr($body, $needBody);
	$pix = substr($body, 0, $needBody);
	$rawLen = strlen($rawBytes);
	$storedFixedLen = $po + strlen($tail);
	$best = null;
	$bestLen = PHP_INT_MAX;
	for($cw = 2; $cw <= $w; $cw++) {
		if($w % $cw !== 0) {
			continue;
		}
		$ncol = (int)($w / $cw);
		for($ch = 2; $ch <= $hAbs; $ch++) {
			if($hAbs % $ch !== 0) {
				continue;
			}
			$nrow = (int)($hAbs / $ch);
			if($ncol * $nrow < 2) {
				continue;
			}
			$encNcol = $this->encode_varint_u32($ncol);
			$encNrow = $this->encode_varint_u32($nrow);
			$encCw = $this->encode_varint_u32($cw);
			$encCh = $this->encode_varint_u32($ch);
			$nCells = $ncol * $nrow;
			$maxM = min(intdiv($cw - 1, 2), intdiv($ch - 1, 2));
			if($maxM < 1) {
				continue;
			}
			for($margin = 1; $margin <= $maxM; $margin++) {
				$iw = $cw - 2 * $margin;
				$ih = $ch - 2 * $margin;
				if($iw < 1 || $ih < 1) {
					continue;
				}
				$tplSz = $iw * $ih * $bppBytes;
				$encMargin = $this->encode_varint_u32($margin);
				$corePixelsAllCells = $nCells * $iw * $ih;
				$rimMinLen = ($w * $hAbs - $corePixelsAllCells) * $bppBytes;
				$payloadBase = 5 + strlen($encNcol) + strlen($encNrow) + strlen($encCw) + strlen($encCh) + strlen($encMargin) + $tplSz + $rimMinLen;
				$payloadMinScalar = $payloadBase + $nCells;
				$scalarViable = ($storedFixedLen + $payloadMinScalar < $bestLen && $storedFixedLen + $payloadMinScalar < $rawLen);
				$bgrViable = false;
				if($bpp === 24) {
					$payloadMinBgr = $payloadBase + 3 * $nCells;
					$bgrViable = ($storedFixedLen + $payloadMinBgr < $bestLen && $storedFixedLen + $payloadMinBgr < $rawLen);
				}
				if(!$scalarViable && !$bgrViable) {
					continue;
				}
				$ref = $this->bmp_bss_extract_cell_core($pix, $hAbs, $rs, 0, 0, $cw, $ch, $bppBytes, $margin);
				if($ref === null || strlen($ref) !== $tplSz) {
					continue;
				}
				$rimsCached = null;
				$iw = $cw - 2 * $margin;
				$ih = $ch - 2 * $margin;
				if($iw < 1 || $ih < 1) {
					continue;
				}
				$rowSpan = $iw * $bppBytes;
				$xRef = $margin * $bppBytes;
				$syRef = $hAbs - 1 - $margin;
				$offRef0 = $syRef * $rs + $xRef;
				// Direct pixel-buffer compare per cell (scalar + optional BGR) avoids materializing core strings per candidate.
				$deltas0 = str_repeat("\0", $nCells);
				$ok0 = $scalarViable;
				$ok1 = $bgrViable;
				$deltas1 = $ok1 ? str_repeat("\0", 3 * $nCells) : '';
				// Cell (0,0) is always zero delta vs itself; avoid one helper call per candidate.
				$di = 1;
				for($br = 0; $br < $nrow; $br++) {
					$bcStart = ($br === 0) ? 1 : 0;
					for($bc = $bcStart; $bc < $ncol; $bc++) {
						if(!$this->bmp_bss_core_deltas_against_ref_from_pix($pix, $hAbs, $rs, $bc, $br, $cw, $ch, $bppBytes, $margin, $scD, $bgrB, $bgrG, $bgrR, $iw, $ih, $rowSpan, $offRef0, $ok0, $ok1)) {
							$ok0 = false;
							$ok1 = false;
							break 2;
						}
						if($ok0) {
							if($scD === null) {
								$ok0 = false;
							} else {
								$deltas0[$di] = chr($scD);
							}
						}
						if($ok1) {
							if($bgrB === null) {
								$ok1 = false;
							} else {
								$o3 = 3 * $di;
								$deltas1[$o3] = chr($bgrB);
								$deltas1[$o3 + 1] = chr($bgrG);
								$deltas1[$o3 + 2] = chr($bgrR);
							}
						}
						$di++;
						if(!$ok0 && !$ok1) {
							break 2;
						}
					}
				}
				if(($ok0 || $ok1) && $rimsCached === null) {
					$rimsCached = $this->bmp_bss_rim_body_raster_concat($pix, $hAbs, $rs, $w, $cw, $ch, $bppBytes, $margin);
				}
				if($ok0) {
					$payload = 'BSS2' . chr(0)
						. $encNcol . $encNrow . $encCw . $encCh . $encMargin
						. $ref . $deltas0 . $rimsCached;
					$stored = substr($rawBytes, 0, $po) . $payload . $tail;
					$L = strlen($stored);
					if($L < $bestLen && $L < $rawLen) {
						$bestLen = $L;
						$best = $stored;
					}
				}
				if($ok1) {
					$payload = 'BSS2' . chr(1)
						. $encNcol . $encNrow . $encCw . $encCh . $encMargin
						. $ref . $deltas1 . $rimsCached;
					$stored = substr($rawBytes, 0, $po) . $payload . $tail;
					$L = strlen($stored);
					if($L < $bestLen && $L < $rawLen) {
						$bestLen = $L;
						$best = $stored;
					}
				}
			}
		}
	}
	return $best;
}

function decode_bmp_block_scalar_shift_payload($rawStored) {
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawStored, false);
	if($meta === null) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): invalid BMP header.');
	}
	$po = $meta['pixel_off'];
	$bppBytes = (int)(($meta['bpp'] + 7) >> 3);
	if($bppBytes < 1 || $bppBytes > 4) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): unsupported bpp.');
	}
	$w = $meta['w'];
	$hAbs = $meta['h'];
	$rs = $meta['row_stride'];
	$n = strlen($rawStored);
	if($n < $po + 5 || substr($rawStored, $po, 4) !== 'BSS1') {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): missing BSS1 payload.');
	}
	$kind = ord($rawStored[$po + 4]);
	if($kind !== 0 && $kind !== 1) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): bad kind.');
	}
	if($kind === 1 && $meta['bpp'] !== 24) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): BGR kind requires 24 bpp.');
	}
	$off = $po + 5;
	$ncol = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 15 ncol');
	$nrow = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 15 nrow');
	if($ncol < 1 || $nrow < 1 || $w % $ncol !== 0 || $hAbs % $nrow !== 0) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): bad grid.');
	}
	$cw = (int)($w / $ncol);
	$ch = (int)($hAbs / $nrow);
	$tplSz = $cw * $ch * $bppBytes;
	if($off + $tplSz > $n) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): truncated template.');
	}
	$tpl = substr($rawStored, $off, $tplSz);
	$off += $tplSz;
	$nd = $ncol * $nrow * ($kind === 0 ? 1 : 3);
	if($off + $nd > $n) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 15): truncated deltas.');
	}
	$deltas = substr($rawStored, $off, $nd);
	$off += $nd;
	$tail = substr($rawStored, $off);
	$needBody = $rs * $hAbs;
	$pix = str_repeat("\0", $needBody);
	for($vy = 0; $vy < $hAbs; $vy++) {
		$sy = $hAbs - 1 - $vy;
		$rowOff = $sy * $rs;
		for($vx = 0; $vx < $w; $vx++) {
			$br = intdiv($vy, $ch);
			$bc = intdiv($vx, $cw);
			$cidx = $br * $ncol + $bc;
			$ly = $vy - $br * $ch;
			$lx = $vx - $bc * $cw;
			$tBase = ($ly * $cw + $lx) * $bppBytes;
			$p = $rowOff + $vx * $bppBytes;
			if($kind === 0) {
				$d = ord($deltas[$cidx]);
				for($j = 0; $j < $bppBytes; $j++) {
					$pix[$p + $j] = chr((ord($tpl[$tBase + $j]) + $d) & 255);
				}
			} else {
				$o3 = $cidx * 3;
				$db = ord($deltas[$o3]);
				$dg = ord($deltas[$o3 + 1]);
				$dr = ord($deltas[$o3 + 2]);
				$pix[$p] = chr((ord($tpl[$tBase]) + $db) & 255);
				$pix[$p + 1] = chr((ord($tpl[$tBase + 1]) + $dg) & 255);
				$pix[$p + 2] = chr((ord($tpl[$tBase + 2]) + $dr) & 255);
			}
		}
	}
	return substr($rawStored, 0, $po) . $pix . $tail;
}

function decode_bmp_block_core_scalar_shift_payload($rawStored) {
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawStored, false);
	if($meta === null) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): invalid BMP header.');
	}
	$po = $meta['pixel_off'];
	$bppBytes = (int)(($meta['bpp'] + 7) >> 3);
	if($bppBytes < 1 || $bppBytes > 4) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): unsupported bpp.');
	}
	$w = $meta['w'];
	$hAbs = $meta['h'];
	$rs = $meta['row_stride'];
	$n = strlen($rawStored);
	if($n < $po + 5 || substr($rawStored, $po, 4) !== 'BSS2') {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): missing BSS2 payload.');
	}
	$kind = ord($rawStored[$po + 4]);
	if($kind !== 0 && $kind !== 1) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): bad kind.');
	}
	if($kind === 1 && $meta['bpp'] !== 24) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): BGR kind requires 24 bpp.');
	}
	$off = $po + 5;
	$ncol = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 16 ncol');
	$nrow = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 16 nrow');
	$cw = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 16 cw');
	$ch = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 16 ch');
	$margin = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 16 margin');
	if($ncol < 1 || $nrow < 1 || $cw < 3 || $ch < 3 || $margin < 1) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): bad grid or margin.');
	}
	if($w % $ncol !== 0 || $hAbs % $nrow !== 0 || (int)($w / $ncol) !== $cw || (int)($hAbs / $nrow) !== $ch) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): grid mismatch.');
	}
	if($cw < 2 * $margin || $ch < 2 * $margin) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): margin too large.');
	}
	$iw = $cw - 2 * $margin;
	$ih = $ch - 2 * $margin;
	$tplSz = $iw * $ih * $bppBytes;
	if($off + $tplSz > $n) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): truncated template.');
	}
	$tpl = substr($rawStored, $off, $tplSz);
	$off += $tplSz;
	$nd = $ncol * $nrow * ($kind === 0 ? 1 : 3);
	if($off + $nd > $n) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): truncated deltas.');
	}
	$deltas = substr($rawStored, $off, $nd);
	$off += $nd;
	$rimPerCell = ($cw * $ch - $iw * $ih) * $bppBytes;
	$nrim = $ncol * $nrow * $rimPerCell;
	if($off + $nrim > $n) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): truncated rim.');
	}
	$rim = substr($rawStored, $off, $nrim);
	$off += $nrim;
	$tail = substr($rawStored, $off);
	$rimOff = 0;
	$rimLen = strlen($rim);
	$needBody = $rs * $hAbs;
	$pix = str_repeat("\0", $needBody);
	$cwM = $cw - $margin;
	$chM = $ch - $margin;
	for($vy = 0; $vy < $hAbs; $vy++) {
		$sy = $hAbs - 1 - $vy;
		$rowOff = $sy * $rs;
		for($vx = 0; $vx < $w; $vx++) {
			$br = intdiv($vy, $ch);
			$bc = intdiv($vx, $cw);
			$cidx = $br * $ncol + $bc;
			$ly = $vy - $br * $ch;
			$lx = $vx - $bc * $cw;
			$inCore = ($lx >= $margin && $lx < $cwM && $ly >= $margin && $ly < $chM);
			$p = $rowOff + $vx * $bppBytes;
			if($inCore) {
				$cly = $ly - $margin;
				$clx = $lx - $margin;
				$tBase = ($cly * $iw + $clx) * $bppBytes;
				if($kind === 0) {
					$d = ord($deltas[$cidx]);
					for($j = 0; $j < $bppBytes; $j++) {
						$pix[$p + $j] = chr((ord($tpl[$tBase + $j]) + $d) & 255);
					}
				} else {
					$o3 = $cidx * 3;
					$db = ord($deltas[$o3]);
					$dg = ord($deltas[$o3 + 1]);
					$dr = ord($deltas[$o3 + 2]);
					$pix[$p] = chr((ord($tpl[$tBase]) + $db) & 255);
					$pix[$p + 1] = chr((ord($tpl[$tBase + 1]) + $dg) & 255);
					$pix[$p + 2] = chr((ord($tpl[$tBase + 2]) + $dr) & 255);
				}
			} else {
				if($rimOff + $bppBytes > $rimLen) {
					fractal_zip::fatal_error('Corrupt FZB literal (mode 16): rim underrun.');
				}
				$chunk = substr($rim, $rimOff, $bppBytes);
				for($j = 0; $j < $bppBytes; $j++) {
					$pix[$p + $j] = $chunk[$j];
				}
				$rimOff += $bppBytes;
			}
		}
	}
	if($rimOff !== $rimLen) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): rim length mismatch.');
	}
	return substr($rawStored, 0, $po) . $pix . $tail;
}

/**
 * FZB literal bundle member transforms. mode: 0 raw, 1 delta, 2 xor-adjacent, 3 nibble-swap, 4 invert, 5 BMP row-delta,
 * 6 nested gzip (bit-exact restore): LE orig_len + sha1 + u8 inner_mode + inner_stored (inner_mode may be 6/7/8/9/10/18),
 * 7 single-member ZIP semantic: varint name_len + name + u8 inner_mode + inner_stored,
 * 8 single-file 7z semantic: varint rel_path_len + rel_path + u8 inner_mode + inner_stored,
 * 9 raster semantic: varint disk_len + exact on-disk file bytes + varint fzrc1_len + FZRC1 canonical (decode returns disk bytes).
 * 10 raster dedup: varint disk_len + on-disk bytes + varint canon_index into decode_container_payload FZBD table (decode returns disk bytes).
 * 12 full-buffer byte reverse (strrev). 13 BMP column-vertical delta (BI_RGB body; complements mode 5).
 * 14 square-block transpose on leading s×s prefix (s=floor(sqrt(n))), tail unchanged; self-inverse.
 * 15 BMP uniform grid: BSS1 + varint ncol + varint nrow + cell template + per-cell scalar byte (see encode_bmp_block_scalar_shift_payload).
 * 16 BMP grid core + rim: BSS2 template on cell core + per-cell scalar or BGR shift on core + raw rims (see encode_bmp_block_core_scalar_shift_payload).
 * 17 Chained transforms: u8 n + n×u8 mode + varint len + innermost stored; decode applies modes in order (see encode_literal_transform_chain_payload).
 */
function decode_bundle_literal_member($mode, $rawStored, $depth = 0) {
	$mode = (int)$mode;
	if($mode === 1) {
		return $this->delta_decode_bytes($rawStored);
	}
	if($mode === 2) {
		return $this->xor_adjacent_decode_bytes($rawStored);
	}
	if($mode === 3) {
		return $this->nibble_swap_bytes($rawStored);
	}
	if($mode === 4) {
		return $this->invert_bytes($rawStored);
	}
	if($mode === 5) {
		return $this->decode_bmp_row_horizontal_delta_payload($rawStored);
	}
	if($mode === 12) {
		$s = (string) $rawStored;
		return strrev($s);
	}
	if($mode === 13) {
		return $this->decode_bmp_column_vertical_delta_payload($rawStored);
	}
	if($mode === 14) {
		$out = $this->literal_square_block_transpose_prefix((string) $rawStored);
		if($out === null) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 14): buffer too short.');
		}
		return $out;
	}
	if($mode === 15) {
		return $this->decode_bmp_block_scalar_shift_payload((string) $rawStored);
	}
	if($mode === 16) {
		return $this->decode_bmp_block_core_scalar_shift_payload((string) $rawStored);
	}
	if($mode === 17) {
		return $this->decode_literal_transform_chain_payload((string) $rawStored, $depth);
	}
	if($mode === 10) {
		$n = strlen($rawStored);
		$off = 0;
		$dlen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 10');
		if($dlen < 0 || $off + $dlen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 10): disk length out of bounds.');
		}
		$disk = substr($rawStored, $off, $dlen);
		$off += $dlen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 10): missing canon index.');
		}
		$idx = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 10');
		if($idx < 0) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 10): invalid index.');
		}
		if($off !== $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 10): trailing bytes.');
		}
		$tab = $this->fzb_raster_canon_decode_table;
		if(!is_array($tab) || $idx >= sizeof($tab)) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 10): canon index out of range.');
		}
		return $disk;
	}
	if($mode === 9) {
		$n = strlen($rawStored);
		$off = 0;
		$dlen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 9');
		if($dlen < 0 || $off + $dlen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 9): disk length out of bounds.');
		}
		$disk = substr($rawStored, $off, $dlen);
		$off += $dlen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 9): missing canonical tail.');
		}
		$clen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 9');
		if($clen < 0 || $off + $clen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 9): canonical length out of bounds.');
		}
		$off += $clen;
		if($off !== $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 9): trailing bytes.');
		}
		return $disk;
	}
	if($mode === 7 || $mode === 8 || $mode === 6 || $mode === 11 || $mode === 18 || $mode === 19 || $mode === 20 || $mode === 21 || $mode === 22 || $mode === 23 || $mode === 24 || $mode === 25 || $mode === 26) {
		$maxDepth = fractal_zip::literal_bundle_decode_max_depth();
		if($depth > $maxDepth) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode ' . $mode . '): too many nested literal layers.');
		}
	}
	if($mode === 18) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$mcount = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 18 count');
		if($mcount < 2 || $mcount > 65535) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 18): bad member count.');
		}
		$decoded = array();
		for($mi = 0; $mi < $mcount; $mi++) {
			if($off >= $n) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 18): truncated member.');
			}
			$nlen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 18 name len');
			if($nlen < 1 || $nlen > 65535 || $off + $nlen > $n) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 18): name.');
			}
			$mname = substr($rawStored, $off, $nlen);
			$off += $nlen;
			if($off >= $n) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 18): missing inner mode.');
			}
			$innerMode = ord($rawStored[$off]);
			$off += 1;
			if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 18): invalid inner mode.');
			}
			$ilen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 18 inner len');
			if($ilen < 0 || $off + $ilen > $n) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 18): inner length.');
			}
			$innerSlice = substr($rawStored, $off, $ilen);
			$off += $ilen;
			$payload = $this->decode_bundle_literal_member((int)$innerMode, $innerSlice, $depth + 1);
			$decoded[] = array('name' => $mname, 'data' => $payload);
		}
		if($off !== $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 18): trailing bytes.');
		}
		$zipOut = fractal_zip_literal_pac_rebuild_zip_multi_semantic($decoded);
		if($zipOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 18): cannot rebuild ZIP (semantic).');
		}
		return $zipOut;
	}
	if($mode === 19) {
		fractal_zip_ensure_ole_cfb_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$fileLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 file len');
		if($fileLen < 512 || $fileLen > FRACTAL_ZIP_OLE_MAX_STREAM_BYTES + 512) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 19): bad file length.');
		}
		$mcount = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 stream count');
		if($mcount < 1 || $mcount > 65535) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 19): bad stream count.');
		}
		$streams = array();
		for($si = 0; $si < $mcount; $si++) {
			if($off >= $n) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 19): truncated stream header.');
			}
			$nlen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 name len');
			if($nlen < 1 || $nlen > 65535 || $off + $nlen > $n) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 19): name.');
			}
			$off += $nlen;
			$nrange = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 range count');
			if($nrange < 1 || $nrange > 65535) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 19): bad range count.');
			}
			$ranges = array();
			$sumR = 0;
			for($ri = 0; $ri < $nrange; $ri++) {
				$ro = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 range off');
				$rl = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 range len');
				if($rl < 1 || $ro < 0 || $ro > $fileLen || $ro + $rl > $fileLen) {
					fractal_zip::fatal_error('Corrupt FZB literal (mode 19): range bounds.');
				}
				$ranges[] = array($ro, $rl);
				$sumR += $rl;
			}
			if($off >= $n) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 19): missing inner mode.');
			}
			$innerMode = ord($rawStored[$off]);
			$off += 1;
			if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 19): invalid inner mode.');
			}
			$ilen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 inner len');
			if($ilen < 0 || $off + $ilen > $n) {
				fractal_zip::fatal_error('FZB literal (mode 19): inner length.');
			}
			$innerSlice = substr($rawStored, $off, $ilen);
			$off += $ilen;
			$payload = $this->decode_bundle_literal_member((int) $innerMode, $innerSlice, $depth + 1);
			if(strlen($payload) !== $sumR) {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 19): decoded stream size mismatch.');
			}
			$streams[] = array('data' => $payload, 'ranges' => $ranges);
		}
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 19): missing template inner mode.');
		}
		$tmplMode = ord($rawStored[$off]);
		$off += 1;
		if($tmplMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 19): invalid template inner mode.');
		}
		$tlen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 19 template inner len');
		if($tlen < 0 || $off + $tlen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 19): template inner length.');
		}
		$tmplSlice = substr($rawStored, $off, $tlen);
		$off += $tlen;
		if($off !== $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 19): trailing bytes.');
		}
		$template = $this->decode_bundle_literal_member((int) $tmplMode, $tmplSlice, $depth + 1);
		if(strlen($template) !== $fileLen) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 19): template length mismatch.');
		}
		$out = fractal_zip_ole_rebuild_from_template($template, $streams);
		if($out === null) {
			fractal_zip::fatal_error('FZB literal (mode 19): cannot rebuild OLE compound.');
		}
		return $out;
	}
	if($mode === 11) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 11');
		if($tagLen < 1 || $tagLen > 65535 || $off + $tagLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 11): tag.');
		}
		$tag = substr($rawStored, $off, $tagLen);
		$off += $tagLen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 11): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 11): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$mpqOut = fractal_zip_literal_pac_rebuild_mpq_semantic($innerPayload, $tag);
		if($mpqOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 11): cannot rebuild MPQ (semantic; need python3 + smpq + tools/fractal_zip_mpq_semantic.py pack).');
		}
		return $mpqOut;
	}
	if($mode === 7) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$nameLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 7');
		if($nameLen < 1 || $nameLen > 65535 || $off + $nameLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 7): member name.');
		}
		$name = substr($rawStored, $off, $nameLen);
		$off += $nameLen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 7): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 7): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$zipOut = fractal_zip_literal_pac_rebuild_zip_single_semantic($innerPayload, $name);
		if($zipOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 7): cannot rebuild ZIP (semantic).');
		}
		return $zipOut;
	}
	if($mode === 20) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 20');
		if($tagLen < 1 || $tagLen > 65535 || $off + $tagLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 20): tar tag.');
		}
		$tag = substr($rawStored, $off, $tagLen);
		$off += $tagLen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 20): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 20): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$tarOut = fractal_zip_literal_pac_rebuild_tar_single_ustar($innerPayload, $tag);
		if($tarOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 20): cannot rebuild ustar TAR (semantic).');
		}
		return $tarOut;
	}
	if($mode === 21) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 21');
		if($tagLen < 1 || $tagLen > 65535 || $off + $tagLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 21): ar tag.');
		}
		$tag = substr($rawStored, $off, $tagLen);
		$off += $tagLen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 21): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 21): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$arOut = fractal_zip_literal_pac_rebuild_ar_single_gnu($innerPayload, $tag);
		if($arOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 21): cannot rebuild GNU ar (semantic).');
		}
		return $arOut;
	}
	if($mode === 22) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 22');
		if($tagLen < 1 || $tagLen > 65535 || $off + $tagLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 22): cpio tag.');
		}
		$tag = substr($rawStored, $off, $tagLen);
		$off += $tagLen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 22): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 22): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$cpioOut = fractal_zip_literal_pac_rebuild_cpio_newc($innerPayload, $tag);
		if($cpioOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 22): cannot rebuild cpio newc (semantic).');
		}
		return $cpioOut;
	}
	if($mode === 23) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 23');
		if($tagLen < 0 || $tagLen > 6 || $off + $tagLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 23): git object type tag length.');
		}
		$fzbGitType = $tagLen === 0 ? '' : substr($rawStored, $off, $tagLen);
		$off += $tagLen;
		if($tagLen > 0) {
			if($fzbGitType !== 'blob' && $fzbGitType !== 'tree' && $fzbGitType !== 'commit' && $fzbGitType !== 'tag') {
				fractal_zip::fatal_error('Corrupt FZB literal (mode 23): unknown git object type tag.');
			}
		}
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 23): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 23): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$gitOut = fractal_zip_literal_pac_rebuild_git_loose_object($innerPayload, $fzbGitType);
		if($gitOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 23): cannot rebuild git loose object (semantic).');
		}
		return $gitOut;
	}
	if($mode === 24) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 24');
		if($tagLen !== 0) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 24): v1 bencode str tag must be empty.');
		}
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 24): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 24): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$bout = fractal_zip_literal_pac_rebuild_bencode_str_only($innerPayload, '');
		if($bout === null) {
			fractal_zip::fatal_error('FZB literal (mode 24): cannot rebuild bencode str (semantic).');
		}
		return $bout;
	}
	if($mode === 25) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 25');
		if($tagLen !== 0) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 25): v1 netstring tag must be empty.');
		}
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 25): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 25): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$nout = fractal_zip_literal_pac_rebuild_netstring_file($innerPayload, '');
		if($nout === null) {
			fractal_zip::fatal_error('FZB literal (mode 25): cannot rebuild netstring (semantic).');
		}
		return $nout;
	}
	if($mode === 26) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$tagLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 26');
		if($tagLen !== 8) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 26): SWF tag must be 8 bytes.');
		}
		if($off + $tagLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 26): truncated tag.');
		}
		$tag8 = substr($rawStored, $off, $tagLen);
		$off += $tagLen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 26): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 26): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$swfOut = fractal_zip_literal_pac_rebuild_swf_fws($innerPayload, $tag8);
		if($swfOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 26): cannot rebuild FWS SWF (semantic).');
		}
		return $swfOut;
	}
	if($mode === 8) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		$n = strlen($rawStored);
		$off = 0;
		$relLen = $this->decode_varint_u32($rawStored, $off, $n, 'FZB literal mode 8');
		if($relLen < 1 || $relLen > 65535 || $off + $relLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 8): inner path.');
		}
		$relPath = substr($rawStored, $off, $relLen);
		$off += $relLen;
		if($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 8): missing inner payload.');
		}
		$innerMode = ord($rawStored[$off]);
		$off += 1;
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 8): invalid inner mode.');
		}
		$innerStored = substr($rawStored, $off);
		$innerPayload = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		$szOut = fractal_zip_literal_pac_rebuild_seven_single_semantic($innerPayload, $relPath);
		if($szOut === null) {
			fractal_zip::fatal_error('FZB literal (mode 8): cannot rebuild 7z (semantic; install 7z/p7zip).');
		}
		return $szOut;
	}
	if($mode === 6) {
		fractal_zip_ensure_literal_pac_stack_loaded();
		if(strlen($rawStored) < 25) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 6): truncated.');
		}
		$u = unpack('Vorig_len', substr($rawStored, 0, 4));
		$origLen = (int) ($u['orig_len'] ?? 0);
		$sha1bin = substr($rawStored, 4, 20);
		$innerMode = ord($rawStored[24]);
		if($innerMode > fractal_zip::FZB_LITERAL_INNER_MODE_MAX) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 6): invalid inner mode.');
		}
		$innerStored = substr($rawStored, 25);
		$inner = $this->decode_bundle_literal_member($innerMode, $innerStored, $depth + 1);
		return fractal_zip_literal_restore_gzip_exact($inner, $origLen, $sha1bin);
	}
	return (string)$rawStored;
}

/**
 * Strip one FZWS v1 wrapper (whole-stream transform on FZB4 inner). Magic FZWS + ver1 + tid + BE orig_len + body.
 */
function decode_fzws_v1_layer($wrapped) {
	$wrapped = (string) $wrapped;
	$n = strlen($wrapped);
	if($n < 10) {
		fractal_zip::fatal_error('Corrupt FZWS (truncated).');
	}
	if(substr($wrapped, 0, 4) !== 'FZWS') {
		fractal_zip::fatal_error('Corrupt FZWS (magic).');
	}
	if(ord($wrapped[4]) !== 1) {
		fractal_zip::fatal_error('Corrupt FZWS (unsupported version).');
	}
	$tid = ord($wrapped[5]);
	$u = unpack('NorigLen', substr($wrapped, 6, 4));
	$origLen = (int) ($u['origLen'] ?? 0);
	if($origLen < 0) {
		fractal_zip::fatal_error('Corrupt FZWS (length).');
	}
	$body = substr($wrapped, 10);
	if($tid === 1) {
		$inner = $this->delta_decode_bytes($body);
	} elseif($tid === 2) {
		$inner = $this->xor_adjacent_decode_bytes($body);
	} else {
		fractal_zip::fatal_error('Corrupt FZWS (unknown transform).');
	}
	if(strlen($inner) !== $origLen) {
		fractal_zip::fatal_error('Corrupt FZWS (length mismatch).');
	}
	return $inner;
}

/**
 * True if $inner is FZB4/5/6 or FZBD v1 wrapping one of those (whole-stream FZWS eligibility and outer heuristics).
 * One FZWS v1 layer is peeled first so `finalize_literal_bundle_inner_for_compress` outputs still classify as bundle literals.
 */
function bundle_inner_eligible_for_fzws($inner) {
	$inner = (string) $inner;
	$n = strlen($inner);
	$head4 = $n >= 4 ? substr($inner, 0, 4) : '';
	if($n >= 10 && $head4 === 'FZWS' && ord($inner[4]) === 1) {
		$tid = ord($inner[5]);
		$u = unpack('NorigLen', substr($inner, 6, 4));
		$origLen = (int) ($u['origLen'] ?? -1);
		if($origLen < 0) {
			return false;
		}
		$body = substr($inner, 10);
		if($tid === 1) {
			$unwrapped = $this->delta_decode_bytes($body);
		} elseif($tid === 2) {
			$unwrapped = $this->xor_adjacent_decode_bytes($body);
		} else {
			return false;
		}
		if(strlen($unwrapped) !== $origLen) {
			return false;
		}
		return $this->bundle_inner_eligible_for_fzws($unwrapped);
	}
	if($n < 4) {
		return false;
	}
	$sig = $head4;
	if($sig === 'FZB4' || $sig === 'FZB5' || $sig === 'FZB6') {
		return true;
	}
	if($sig === 'FZBM' && $n >= 5 && ord($inner[4]) === 1) {
		return true;
	}
	if($sig === 'FZBF' && $n >= 5 && ord($inner[4]) === 1) {
		return true;
	}
	if($sig !== 'FZBD' || $n < 10 || ord($inner[4]) !== 1) {
		return false;
	}
	$off = 5;
	$nt = $this->decode_varint_u32($inner, $off, $n, 'FZBD');
	if($nt < 0 || $nt > 65536) {
		return false;
	}
	for($ti = 0; $ti < $nt; $ti++) {
		$bl = $this->decode_varint_u32($inner, $off, $n, 'FZBD');
		if($bl < 0 || $off + $bl > $n) {
			return false;
		}
		$off += $bl;
	}
	if($off + 4 > $n) {
		return false;
	}
	$rest = substr($inner, $off, 4);
	return $rest === 'FZB4' || $rest === 'FZB5' || $rest === 'FZB6';
}

/**
 * Optional FZWS wrap when gzip‑1 probe beats raw inner (bounded by whole_stream_fzws_max_bytes).
 * With {@see outer_force_codec_is_zpaq}, gzip‑1’s winning wrap can be vetoed when zpaq wire size favors the plain inner
 * ({@see fractal_zip::fzws_zpaq_veto_max_inner_bytes}).
 */
function maybe_wrap_fzb_inner_with_fzws($inner) {
	$inner = (string) $inner;
	if(!fractal_zip::whole_stream_fzws_enabled()) {
		return $inner;
	}
	if(!$this->bundle_inner_eligible_for_fzws($inner)) {
		return $inner;
	}
	// FZBM/FZBF merged inners are often multi‑MiB of PCM/FLAC or other incompressible bulk; whole-stream delta/xor builds a second O(n) buffer and rarely wins gzip‑1 probes, but can exhaust RAM (e.g. album-sized FZBM during concat-order scoring).
	$n0 = strlen($inner);
	if($n0 >= 5) {
		$sig0 = substr($inner, 0, 4);
		if(($sig0 === 'FZBM' || $sig0 === 'FZBF') && ord($inner[4]) === 1) {
			return $inner;
		}
	}
	$maxB = fractal_zip::whole_stream_fzws_max_bytes();
	if($maxB > 0 && $n0 > $maxB) {
		return $inner;
	}
	$base = gzdeflate($inner, 1);
	if($base === false) {
		return $inner;
	}
	$bestScore = strlen($base);
	$bestWrap = '';
	foreach(array(1, 2) as $tid) {
		$body = $tid === 1 ? $this->delta_encode_bytes($inner) : $this->xor_adjacent_encode_bytes($inner);
		$wrapped = 'FZWS' . chr(1) . chr($tid) . pack('N', $n0) . $body;
		$z = gzdeflate($wrapped, 1);
		if($z !== false) {
			$zL = strlen($z);
			if($zL < $bestScore) {
				$bestScore = $zL;
				$bestWrap = $wrapped;
			}
		}
	}
	if($bestWrap !== '') {
		$vetoMax = fractal_zip::fzws_zpaq_veto_max_inner_bytes();
		if($vetoMax > 0 && $n0 <= $vetoMax) {
			$zpaqExe = fractal_zip::zpaq_executable();
			if($zpaqExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZPAQ')) {
				$mf = fractal_zip::zpaq_add_method_argv_fragment();
				$wIn = $this->outer_zpaq_blob_with_meth_fragment($zpaqExe, $inner, $mf);
				$wWr = $this->outer_zpaq_blob_with_meth_fragment($zpaqExe, $bestWrap, $mf);
				if($wIn !== null && $wIn !== '' && $wWr !== null && $wWr !== ''
					&& strlen($wIn) <= strlen($wWr)) {
					return $inner;
				}
			}
		}
	}
	return $bestWrap !== '' ? $bestWrap : $inner;
}

function finalize_literal_bundle_inner_for_compress($inner) {
	return $this->maybe_wrap_fzb_inner_with_fzws((string) $inner);
}

/**
 * Fork-pool entry: same baseline instance as dual-raw staged comparators (256, false, false, null, false).
 *
 * @return array{blob: string, codec: string|null, zpaq: string|null}
 */
public static function encode_pipeline_literal_outer_trial_full(string $finBlob): array {
	$fz = new fractal_zip(256, false, false, null, false);
	$blob = $fz->adaptive_compress($finBlob);
	return array(
		'blob' => $blob,
		'codec' => fractal_zip::$last_outer_codec,
		'zpaq' => fractal_zip::$last_zpaq_method,
	);
}

/**
 * @return array{blob: string, codec: string|null, zpaq: string|null}
 */
public static function encode_pipeline_literal_outer_trial_fast(string $finBlob): array {
	$fz = new fractal_zip(256, false, false, null, false);
	$blob = $fz->adaptive_compress_outer_fast_codec_tier($finBlob);
	return array(
		'blob' => $blob,
		'codec' => fractal_zip::$last_outer_codec,
		'zpaq' => fractal_zip::$last_zpaq_method,
	);
}

/**
 * Phase 3: smallest adaptive-compressed blob among literal inners (FZCD, FZB*, …) and raw escaped-per-file container.
 * Raw tier: builds disk-escaped and (when unwrap on) unescaped-escaped inners; if FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER is on (default) and the
 * two inners differ, compresses both and keeps the smaller outer — peel-restore only when the unwrapped variant wins. Literal inners still
 * read disk bytes so choose_best_literal_bundle_transform can record semantic/gzip stacks.
 * When FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER allows (auto: multi-file MPQ-family or mixed extensions), uses a fast
 * gzip+zstd+brotli tier for all candidates, then runs full adaptive_compress only on the best semantic inner if it beats
 * raw on that tier by at least FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_FAST_MARGIN_BYTES (unset ⇒ max(48, min(8192,
 * floor(2.5% of raw’s fast-tier size))) — large trees still cap at 8192 B minimum improvement; small mixed-ext folders
 * can deepen after ~48–200 B. Override with the env var (bytes).
 * Below FRACTAL_ZIP_STAGED_LITERAL_FAST_OUTER_MIN_RAW_BYTES total raw (default 16 MiB), the staged fast path is **skipped**
 * and the full adaptive outer contest runs (same as unstaged) so FZB/FZBM/FZCD vs raw is ordered by true `.fzc` bytes.
 * At or above that threshold, after the fast tier (and optional deepen pass), raw and the literal winner are re-scored with
 * full adaptive_compress so a semantic inner is never kept when raw is smaller or tied on wire bytes.
 *
 * @param list<array{inner: string, tag: string}> $innerVariants
 * @param array<string,string> $rawFilesByPath
 * @return array{0: string, 1: string}
 */
function choose_smallest_adaptive_literal_inner_or_raw_escaped(array $innerVariants, $rawFilesByPath) {
	$this->fractal_member_gzip_disk_restore = array();
	$rawTierPeelRestore = array();
	$rawEscapedDisk = array();
	$rawEscapedUnwrapped = array();
	$rawDirectDisk = array();
	$rawDirectUnwrapped = array();
	foreach($rawFilesByPath as $path => $bytes) {
		$p = (string) $path;
		$disk = (string) $bytes;
		$rawEscapedDisk[$p] = $this->escape_literal_for_storage($disk);
		$rawDirectDisk[$p] = $disk;
		$forUn = $disk;
		if($p !== '' && $disk !== '' && fractal_zip::bundle_raw_tier_deep_unwrap_enabled()) {
			fractal_zip_ensure_literal_pac_stack_loaded();
			list($work) = fractal_zip_literal_deep_unwrap_with_layers($p, $disk);
			if($work !== $disk) {
				$rawTierPeelRestore[$p] = $disk;
				$forUn = $work;
			}
		}
		$rawEscapedUnwrapped[$p] = $this->escape_literal_for_storage($forUn);
		$rawDirectUnwrapped[$p] = $forUn;
	}
	$innerVariantSchedule = fractal_zip_encode_pipeline::schedule_inner_variants_for_literal_outer($innerVariants);
	fractal_zip_encode_pipeline::html_trace_checkpoint(
		fractal_zip_encode_pipeline::PHASE_OUTER_CODECS,
		'choose_smallest: outer codec tournament (raw tier + literal inners)'
	);
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
	$rawInnerDisk = $this->encode_container_payload($rawEscapedDisk, '');
	if($rawEscapedDisk === $rawEscapedUnwrapped) {
		$rawInnerUnwrapped = $rawInnerDisk;
	} else {
		$rawInnerUnwrapped = $this->encode_container_payload($rawEscapedUnwrapped, '');
	}
	$singleRawDirectDisk = null;
	$singleRawDirectUnwrapped = null;
	if(count($rawDirectDisk) === 1) {
		if($rawDirectDisk === $rawDirectUnwrapped) {
			foreach($rawDirectDisk as $sp => $sb) {
				$singleRawDirectDisk = $this->encode_single_member_raw_payload((string) $sp, (string) $sb);
				break;
			}
			$singleRawDirectUnwrapped = $singleRawDirectDisk;
		} else {
			foreach($rawDirectDisk as $sp => $sb) {
				$singleRawDirectDisk = $this->encode_single_member_raw_payload((string) $sp, (string) $sb);
				break;
			}
			foreach($rawDirectUnwrapped as $sp => $sb) {
				$singleRawDirectUnwrapped = $this->encode_single_member_raw_payload((string) $sp, (string) $sb);
				break;
			}
		}
	}
	$rawInnerDiffers = ($rawInnerDisk !== $rawInnerUnwrapped);
	$tryDualRaw = fractal_zip::bundle_raw_tier_deep_unwrap_enabled() && fractal_zip::bundle_raw_tier_dual_compress_pick_enabled()
		&& $rawInnerDiffers;
	$sumRawBytes = 0;
	foreach($rawFilesByPath as $rb) {
		$sumRawBytes += strlen((string) $rb);
	}
	$fastOuterMinRaw = fractal_zip::staged_literal_fast_outer_min_raw_bytes();
	$meetRawFloor = false;
	if($fastOuterMinRaw > 0) {
		if($sumRawBytes >= $fastOuterMinRaw) {
			$meetRawFloor = true;
		} elseif(fractal_zip::folder_staged_literal_outer_homogeneous_many_files_eligible($rawFilesByPath)) {
			$homMin = fractal_zip::staged_literal_homogeneous_fast_outer_min_raw_bytes();
			if($homMin > 0 && $sumRawBytes >= $homMin) {
				$meetRawFloor = true;
			}
		} elseif(sizeof($rawFilesByPath) === 1) {
			$sft = fractal_zip::folder_staged_literal_outer_single_file_min_raw_bytes_threshold();
			if($sft > 0 && $sumRawBytes >= $sft) {
				$meetRawFloor = true;
			}
		}
	}
	$useStagedFastOuterPath = fractal_zip::folder_staged_literal_outer_enabled($rawFilesByPath)
		&& $meetRawFloor;
	$literalJobList = array();
	$literalTieN = 0;
	foreach($innerVariantSchedule as $v) {
		if(!is_array($v) || !isset($v['inner'])) {
			continue;
		}
		$inner = (string) $v['inner'];
		if($inner === '') {
			continue;
		}
		$tag = isset($v['tag']) ? (string) $v['tag'] : 'literal';
		$literalJobList[] = array(
			'fin' => $this->finalize_literal_bundle_inner_for_compress($inner),
			'tag' => $tag,
			'tie' => $literalTieN++,
		);
	}
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('choose_smallest_phase4_encode_raw_and_literal_jobs');
	if(!$useStagedFastOuterPath) {
		$bestZpaqMeth = null;
		if($tryDualRaw) {
			$fzD = new fractal_zip(256, false, false, null, false);
			$rawDiskBlob = $fzD->adaptive_compress($rawInnerDisk);
			$wireDisk = strlen($fzD->append_fractal_gzip_peel_restore_trailer($rawDiskBlob, array()));
			$codecDisk = fractal_zip::$last_outer_codec;
			$zDiskM = ($codecDisk === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
			$fzU = new fractal_zip(256, false, false, null, false);
			$rawUnBlob = $fzU->adaptive_compress($rawInnerUnwrapped);
			$wireUn = strlen($fzU->append_fractal_gzip_peel_restore_trailer($rawUnBlob, $rawTierPeelRestore));
			$codecUn = fractal_zip::$last_outer_codec;
			$zUnM = ($codecUn === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
			if($wireUn < $wireDisk) {
				$rawContents = $rawUnBlob;
				$codecRaw = $codecUn;
				$rawTierRestoreForPick = $rawTierPeelRestore;
				$bestZpaqMeth = $zUnM;
			} else {
				$rawContents = $rawDiskBlob;
				$codecRaw = $codecDisk;
				$rawTierRestoreForPick = array();
				$bestZpaqMeth = $zDiskM;
			}
		} else {
			$rawPickInner = $rawInnerDiffers && fractal_zip::bundle_raw_tier_deep_unwrap_enabled() ? $rawInnerUnwrapped : $rawInnerDisk;
			$rawContents = $this->adaptive_compress($rawPickInner);
			$codecRaw = fractal_zip::$last_outer_codec;
			$bestZpaqMeth = ($codecRaw === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
			$rawTierRestoreForPick = ($rawPickInner === $rawInnerUnwrapped && $rawInnerDiffers) ? $rawTierPeelRestore : array();
		}
		if($singleRawDirectDisk !== null && $singleRawDirectUnwrapped !== null) {
			$rawDirectInner = ($rawTierRestoreForPick !== array()) ? $singleRawDirectUnwrapped : $singleRawDirectDisk;
			$rawDirectContents = $this->adaptive_compress($rawDirectInner);
			$codecDirect = fractal_zip::$last_outer_codec;
			$zDirM = ($codecDirect === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
			if(strlen($rawDirectContents) < strlen($rawContents)) {
				$rawContents = $rawDirectContents;
				$codecRaw = $codecDirect;
				$bestZpaqMeth = $zDirM;
			}
		}
		$bestLen = strlen($rawContents);
		$bestBlob = $rawContents;
		$bestTag = 'raw';
		$bestCodec = $codecRaw;
		$parFull = null;
		if(sizeof($literalJobList) >= 2) {
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
			$parFull = fractal_zip_encode_pipeline::parallel_literal_variant_outer_trials($literalJobList, 'full');
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('choose_smallest_parallel_literal_outer_trials_full');
		}
		if($parFull !== null) {
			list($mergeLen, $litWin) = fractal_zip_encode_pipeline::pick_smallest_literal_outer_vs_raw($bestLen, $parFull);
			$bestLen = $mergeLen;
			if($litWin !== null) {
				$bestBlob = $litWin['blob'];
				$bestTag = $litWin['tag'];
				$bestCodec = $litWin['codec'];
				$bestZpaqMeth = ($litWin['codec'] === 'zpaq') ? $litWin['zpaq'] : null;
			}
		} else {
			foreach($innerVariantSchedule as $v) {
				if(!is_array($v) || !isset($v['inner'])) {
					continue;
				}
				$inner = (string) $v['inner'];
				if($inner === '') {
					continue;
				}
				$tag = isset($v['tag']) ? (string) $v['tag'] : 'literal';
				$bundleContents = $this->adaptive_compress($this->finalize_literal_bundle_inner_for_compress($inner));
				$codecB = fractal_zip::$last_outer_codec;
				$zB = ($codecB === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
				$lb = strlen($bundleContents);
				if($lb < $bestLen) {
					$bestLen = $lb;
					$bestBlob = $bundleContents;
					$bestTag = $tag;
					$bestCodec = $codecB;
					$bestZpaqMeth = $zB;
				}
			}
		}
		fractal_zip::$last_outer_codec = $bestCodec;
		fractal_zip::$last_written_container_codec = $bestCodec;
		if($bestCodec === 'zpaq' && is_string($bestZpaqMeth) && $bestZpaqMeth !== '' && preg_match('/^[0-9]+$/', (string) $bestZpaqMeth) === 1) {
			fractal_zip::$last_zpaq_method = (string) $bestZpaqMeth;
		} else {
			fractal_zip::$last_zpaq_method = null;
		}
		$this->fractal_member_gzip_disk_restore = ($bestTag === 'raw') ? $rawTierRestoreForPick : array();
		return array($bestBlob, $bestTag);
	}
	$bestZpaqMeth = null;
	if($tryDualRaw) {
		$fzSd = new fractal_zip(256, false, false, null, false);
		$rpDisk = $fzSd->adaptive_compress_outer_fast_codec_tier($rawInnerDisk);
		$wireFd = strlen($fzSd->append_fractal_gzip_peel_restore_trailer($rpDisk, array()));
		$codecDiskS = fractal_zip::$last_outer_codec;
		$zDiskS = ($codecDiskS === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
		$fzSu = new fractal_zip(256, false, false, null, false);
		$rpUn = $fzSu->adaptive_compress_outer_fast_codec_tier($rawInnerUnwrapped);
		$wireFu = strlen($fzSu->append_fractal_gzip_peel_restore_trailer($rpUn, $rawTierPeelRestore));
		$codecUnS = fractal_zip::$last_outer_codec;
		$zUnS = ($codecUnS === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
		if($wireFu < $wireFd) {
			$rp = $rpUn;
			$rawTierRestoreForPick = $rawTierPeelRestore;
			$bestCodec = $codecUnS;
			$bestZpaqMeth = $zUnS;
		} else {
			$rp = $rpDisk;
			$rawTierRestoreForPick = array();
			$bestCodec = $codecDiskS;
			$bestZpaqMeth = $zDiskS;
		}
	} else {
		$rawPickInner = $rawInnerDiffers && fractal_zip::bundle_raw_tier_deep_unwrap_enabled() ? $rawInnerUnwrapped : $rawInnerDisk;
		$rp = $this->adaptive_compress_outer_fast_codec_tier($rawPickInner);
		$rawTierRestoreForPick = ($rawPickInner === $rawInnerUnwrapped && $rawInnerDiffers) ? $rawTierPeelRestore : array();
		$bestCodec = fractal_zip::$last_outer_codec;
		$bestZpaqMeth = ($bestCodec === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
	}
	$rawFastLen = strlen($rp);
	$bestLen = $rawFastLen;
	$bestBlob = $rp;
	$bestTag = 'raw';
	$bestChampionFin = null;
	$bestLitFastLen = PHP_INT_MAX;
	$bestLitFin = null;
	$bestLitTag = 'fzb';
	$finByTie = array();
	foreach($literalJobList as $lj) {
		$finByTie[$lj['tie']] = $lj['fin'];
	}
	$parFast = null;
	if(sizeof($literalJobList) >= 2) {
		fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
		$parFast = fractal_zip_encode_pipeline::parallel_literal_variant_outer_trials($literalJobList, 'fast');
		fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('choose_smallest_parallel_literal_outer_trials_fast');
	}
	if($parFast !== null) {
		list($mergeLen, $win) = fractal_zip_encode_pipeline::pick_smallest_literal_outer_vs_raw($rawFastLen, $parFast);
		$bestLen = $mergeLen;
		if($win !== null) {
			$bestBlob = $win['blob'];
			$bestTag = $win['tag'];
			$bestCodec = $win['codec'];
			$bestZpaqMeth = ($win['codec'] === 'zpaq') ? $win['zpaq'] : null;
			$bestChampionFin = $finByTie[$win['tie']];
		}
		$pickFast = fractal_zip_encode_pipeline::pick_smallest_literal_outer_among_literals_only($parFast);
		if($pickFast !== null) {
			$bestLitFastLen = $pickFast['len'];
			$bestLitFin = $finByTie[$pickFast['tie']];
			$bestLitTag = $pickFast['tag'];
		} else {
			$bestLitFastLen = PHP_INT_MAX;
			$bestLitFin = null;
			$bestLitTag = 'fzb';
		}
	} else {
		foreach($innerVariantSchedule as $v) {
			if(!is_array($v) || !isset($v['inner'])) {
				continue;
			}
			$inner = (string) $v['inner'];
			if($inner === '') {
				continue;
			}
			$tag = isset($v['tag']) ? (string) $v['tag'] : 'literal';
			$fin = $this->finalize_literal_bundle_inner_for_compress($inner);
			$blob = $this->adaptive_compress_outer_fast_codec_tier($fin);
			$codecB = fractal_zip::$last_outer_codec;
			$zB = ($codecB === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
			$lb = strlen($blob);
			if($lb < $bestLen) {
				$bestLen = $lb;
				$bestBlob = $blob;
				$bestTag = $tag;
				$bestCodec = $codecB;
				$bestZpaqMeth = $zB;
				$bestChampionFin = $fin;
			}
			if($lb < $bestLitFastLen) {
				$bestLitFastLen = $lb;
				$bestLitFin = $fin;
				$bestLitTag = $tag;
			}
		}
	}
	$deepMinB = fractal_zip::staged_literal_deep_min_bytes();
	$fastMargin = fractal_zip::staged_literal_deep_fast_margin_bytes_or_negative();
	if($fastMargin < 0) {
		// Default margin must scale with fast-tier output size. The legacy max(8192, 0.3% raw) forced ≥8192 B, so when
		// rawFastLen was only a few KiB (mixed extensions → staged outer), shouldDeepen never fired and full
		// adaptive_compress skipped on literal winners.
		$fastMargin = max(48, min(8192, (int) floor(0.025 * (float) $rawFastLen)));
	}
	$fastBeat = ($bestLitFin !== null && $bestLitFastLen + $deepMinB < $rawFastLen) ? ($rawFastLen - $bestLitFastLen) : 0;
	$shouldDeepen = ($bestLitFin !== null && $bestLitFastLen + $deepMinB < $rawFastLen && $fastBeat >= $fastMargin);
	$literalUsesFullAdaptive = false;
	if($shouldDeepen) {
		$deepBlob = $this->adaptive_compress($bestLitFin);
		$deepLen = strlen($deepBlob);
		$deepCodec = fractal_zip::$last_outer_codec;
		$zDeep = ($deepCodec === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
		if($deepLen < $bestLen) {
			$bestLen = $deepLen;
			$bestBlob = $deepBlob;
			$bestTag = $bestLitTag;
			$bestCodec = $deepCodec;
			$bestZpaqMeth = $zDeep;
			$literalUsesFullAdaptive = true;
		}
	}
	// Full outer on raw vs staged literal winner: fast tier can pick a semantic inner that loses once 7z/Arc/brotli11 run.
	$rawFullBlob = null;
	$rawFullCodec = $bestCodec;
	$rawFullRestore = $rawTierRestoreForPick;
	$rawFullZ = $bestZpaqMeth;
	if($tryDualRaw) {
		$fzRd = new fractal_zip(256, false, false, null, false);
		$rawDiskBlobF = $fzRd->adaptive_compress($rawInnerDisk);
		$wireDiskF = strlen($fzRd->append_fractal_gzip_peel_restore_trailer($rawDiskBlobF, array()));
		$codecDiskF = fractal_zip::$last_outer_codec;
		$zDiskF = ($codecDiskF === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
		$fzRu = new fractal_zip(256, false, false, null, false);
		$rawUnBlobF = $fzRu->adaptive_compress($rawInnerUnwrapped);
		$wireUnF = strlen($fzRu->append_fractal_gzip_peel_restore_trailer($rawUnBlobF, $rawTierPeelRestore));
		$codecUnF = fractal_zip::$last_outer_codec;
		$zUnF = ($codecUnF === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
		if($wireUnF < $wireDiskF) {
			$rawFullBlob = $rawUnBlobF;
			$rawFullCodec = $codecUnF;
			$rawFullRestore = $rawTierPeelRestore;
			$rawFullZ = $zUnF;
		} else {
			$rawFullBlob = $rawDiskBlobF;
			$rawFullCodec = $codecDiskF;
			$rawFullRestore = array();
			$rawFullZ = $zDiskF;
		}
	} else {
		$rawPickInnerF = $rawInnerDiffers && fractal_zip::bundle_raw_tier_deep_unwrap_enabled() ? $rawInnerUnwrapped : $rawInnerDisk;
		$rawFullBlob = $this->adaptive_compress($rawPickInnerF);
		$rawFullCodec = fractal_zip::$last_outer_codec;
		$rawFullZ = ($rawFullCodec === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
		$rawFullRestore = ($rawPickInnerF === $rawInnerUnwrapped && $rawInnerDiffers) ? $rawTierPeelRestore : array();
	}
	$rawFullLen = strlen((string) $rawFullBlob);
	if($bestTag === 'raw') {
		if($rawFullLen < $bestLen) {
			$bestBlob = (string) $rawFullBlob;
			$bestCodec = $rawFullCodec;
			$bestZpaqMeth = $rawFullZ;
			$rawTierRestoreForPick = $rawFullRestore;
		}
	} else {
		if($literalUsesFullAdaptive) {
			$litFullLen = $bestLen;
			$zLitF = $bestZpaqMeth;
		} elseif($bestChampionFin !== null) {
			$litFullBlob = $this->adaptive_compress($bestChampionFin);
			$litFullLen = strlen($litFullBlob);
			$litFullCodec = fractal_zip::$last_outer_codec;
			$zLitF = ($litFullCodec === 'zpaq') ? fractal_zip::$last_zpaq_method : null;
		} else {
			$litFullLen = $bestLen;
			$litFullCodec = $bestCodec;
			$zLitF = $bestZpaqMeth;
		}
		if($rawFullLen <= $litFullLen) {
			$bestBlob = (string) $rawFullBlob;
			$bestTag = 'raw';
			$bestCodec = $rawFullCodec;
			$bestZpaqMeth = $rawFullZ;
			$rawTierRestoreForPick = $rawFullRestore;
		} elseif(!$literalUsesFullAdaptive && $bestChampionFin !== null) {
			$bestBlob = (string) $litFullBlob;
			$bestCodec = $litFullCodec;
			$bestZpaqMeth = $zLitF;
		}
	}
	fractal_zip::$last_outer_codec = $bestCodec;
	fractal_zip::$last_written_container_codec = $bestCodec;
	if($bestCodec === 'zpaq' && is_string($bestZpaqMeth) && $bestZpaqMeth !== '' && preg_match('/^[0-9]+$/', (string) $bestZpaqMeth) === 1) {
		fractal_zip::$last_zpaq_method = (string) $bestZpaqMeth;
	} else {
		fractal_zip::$last_zpaq_method = null;
	}
	$this->fractal_member_gzip_disk_restore = ($bestTag === 'raw') ? $rawTierRestoreForPick : array();
	return array($bestBlob, $bestTag);
}

/**
 * Peel-restore map after {@see choose_smallest_adaptive_literal_inner_or_raw_escaped} (literal contest fork reads this into JSON).
 *
 * @return array<string, string>
 */
public function fractal_member_gzip_disk_restore_literal_fork_snapshot(): array {
	return $this->fractal_member_gzip_disk_restore;
}

/**
 * @param array<string,string> $rawFilesByPath
 * @return array{0: string, 1: string}
 */
function choose_smaller_adaptive_bundle_or_raw_escaped($bundlePayload, $rawFilesByPath) {
	return $this->choose_smallest_adaptive_literal_inner_or_raw_escaped(
		array(array('inner' => (string) $bundlePayload, 'tag' => 'bundle')),
		$rawFilesByPath
	);
}

/**
 * Phase 2: FZBF (merged + nested fractal) is built in collect_literal_bundle_inner_variants when
 * FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS / auto gate. Opaque inners (FZBF, FZCD) are left unchanged here.
 */
function maybe_apply_unified_literal_multipass($inner) {
	$inner = (string) $inner;
	if($inner !== '' && strlen($inner) >= 4) {
		$sig = substr($inner, 0, 4);
		if($sig === 'FZBF' || $sig === 'FZCD') {
			return $inner;
		}
	}
	return $inner;
}

/**
 * Phase 1–2: FZCD merged-PCM transmedia when `FRACTAL_ZIP_FLACPAC=1` and applicable; FZBM merged raw (≥2 files); FZBF merged + nested fractal when multipass synth gate passes (merged blob stats);
 * FZB* from encode_literal_bundle_payload (unwrap + transforms + FZBD auto).
 *
 * @param array<string,string> $rawFilesByPath ksorted rel => disk bytes
 * @return list<array{inner: string, tag: string}>
 */
function collect_literal_bundle_inner_variants_for_folder($root, array $rawFilesByPath) {
	$variants = array();
	fractal_zip_encode_pipeline::html_trace_checkpoint(
		fractal_zip_encode_pipeline::PHASE_STREAM_BUILD,
		'collect_literal_bundle_inner_variants: FZCD / FZBM / FZBF / FZB chain'
	);
	$paths = array_keys($rawFilesByPath);
	$fzuniKey = fractal_zip::hot_string_digest((string) $root . "\0" . implode("\n", $paths));
	$tmpFzcd = sys_get_temp_dir() . DS . 'fzuni_' . substr(md5($fzuniKey . "\0fzuni\0" . (string) spl_object_id($this)), 0, 16);
	if($this->write_fzcd_bundle_if_applicable($root, $paths, $tmpFzcd)) {
		$fzcd = file_get_contents($tmpFzcd);
		@unlink($tmpFzcd);
		if($fzcd !== false && $fzcd !== '') {
			$variants[] = array('inner' => $fzcd, 'tag' => 'fzcd');
		}
	} else {
		if(is_file($tmpFzcd)) {
			@unlink($tmpFzcd);
		}
	}
	$fzbmCap = fractal_zip::literal_merged_fzbm_max_raw_bytes_cap();
	$totalRaw = 0;
	foreach($rawFilesByPath as $b) {
		$totalRaw += strlen((string) $b);
	}
	if(sizeof($rawFilesByPath) >= 2 && fractal_zip::literal_merged_fzbm_inner_enabled() && ($fzbmCap <= 0 || $totalRaw <= $fzbmCap)) {
		$fzbmInner = $this->encode_fzbm_v1_merged_literal($rawFilesByPath);
		if($fzbmInner !== '') {
			$fzbmInner = $this->maybe_apply_unified_literal_multipass($fzbmInner);
			$variants[] = array('inner' => $fzbmInner, 'tag' => 'fzbm');
		}
	}
	if(sizeof($rawFilesByPath) >= 1) {
		$fzbfGate = fractal_zip::unified_literal_multipass_synth_enabled_for_raw_bytes($totalRaw, '');
		if(!$fzbfGate) {
			$mergedProbe = $this->merged_raw_concat_for_literal_bundle_map($rawFilesByPath);
			$fzbfGate = fractal_zip::unified_literal_multipass_synth_enabled_for_raw_bytes($totalRaw, $mergedProbe);
		}
		if($fzbfGate) {
			$fzbfInner = $this->encode_fzbf_v1_merged_fractal_bundle($rawFilesByPath);
			if($fzbfInner !== null && $fzbfInner !== '') {
				$variants[] = array('inner' => $fzbfInner, 'tag' => 'fzbf');
			}
		}
	}
	if(fractal_zip::literal_outer_fzta_tar_jpeg_candidate_enabled() && sizeof($rawFilesByPath) === 1) {
		foreach($rawFilesByPath as $relOne => $rawOne) {
			$relOne = (string) $relOne;
			if(strpos($relOne, '/') !== false || strpos($relOne, '\\') !== false) {
				break;
			}
			$low = strtolower($relOne);
			if(preg_match('/\.(jpe?g)$/', $low) !== 1) {
				break;
			}
			$tarBlob = fractal_zip::posix_ustar_single_file_archive_bytes($relOne, (string) $rawOne);
			if(is_string($tarBlob) && $tarBlob !== '') {
				$fztaInner = 'FZTA' . chr(1) . $this->encode_varint_u32(strlen($tarBlob)) . $tarBlob;
				$variants[] = array('inner' => $fztaInner, 'tag' => 'fzta_tar');
			}
			break;
		}
	}
	$bundlePayload = $this->encode_literal_bundle_payload($rawFilesByPath);
	if($bundlePayload !== null && $bundlePayload !== '') {
		$bundlePayload = $this->maybe_apply_unified_literal_multipass($bundlePayload);
		$variants[] = array('inner' => $bundlePayload, 'tag' => 'fzb');
	}
	return $variants;
}

/**
 * Image PAC + stream registry only (no semantic ZIP/7z/MPQ, no gzip peel stack). Prefer calling
 * choose_best_literal_bundle_transform on **disk** bytes: it runs fractal_zip_literal_deep_unwrap_with_layers first
 * (same order as zip_folder), which ends with image_pac + stream when applicable — avoid double-passing members.
 */
function literal_bundle_member_preprocess_for_transform($relPath, $rawBytes) {
	fractal_zip_ensure_literal_pac_stack_loaded();
	return fractal_zip_literal_pac_preprocess_literal_for_bundle((string) $relPath, (string) $rawBytes);
}

/**
 * Pick smallest zlib-deflated representation among lossless transforms (probe level from literal_bundle_gzip_probe_level; BMP default 9).
 * zlib level 1 is measured first for ratio gates; the configured probe level (e.g. 9 for BMP) runs only after those gates—skips an extra deflate when gzip-1 early exit or large-text raw-only shortcut applies.
 * Large mostly-text payloads: skip only when a quick gzip-1 probe shows little redundancy (ratio gate); otherwise try
 * delta/xor (tabular/repetitive text). Full nibble/invert/BMP trials stay off for large text unless
 * FRACTAL_ZIP_LITERAL_LARGE_TEXT_LIGHT_PROBE=0 or FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS=1.
 * FRACTAL_ZIP_LITERAL_LARGE_TEXT_SKIP_PROBE_GZIP1_MIN_RATIO (default 0.88): if gzip-1 output length / raw length >= this, use raw only.
 * FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO (default 0.045): if gzip-1 length / raw length <= this, skip transforms (ratio computed with deflate **level 1** only, independent of literal_bundle_gzip_probe_level so level-9 tournament scoring cannot spuriously trigger this skip). Delta/xor can hurt outer text-detectors on dense prose. Exception: uncompressed BI_RGB BMP
 *   still runs the full transform path so modes 5/13 can beat raw when row/column delta helps.
 * FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT=1: bytes-first — disable gzip-1 early exit, large-text raw-only shortcut, and the 4 MiB non-text tournament cap (still obeys FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES if you set it explicitly). Implies the same large-text / exotic probes as FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS=1; higher CPU and RAM on big literals.
 * Large non-text blobs (default > FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES, 4 MiB): skip delta/xor/nibble/invert
 * (each allocates a full copy; FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS=1 or TOURNAMENT_STRICT bypasses this cap) — avoids OOM on e.g. FLAC sidecars.
 * Below that cap, size ≥512 KiB non–large-text payloads use the same full candidate tournament as smaller bodies (BMP row/column, delta, xor, etc.), not BMP-only early exit.
 * Optional $relPath: when set, literals may be preprocessed (fractal_zip_literal_pac.php: rasters + registry stream types: gz/svgz, bz2, xz, zst, lz4, br, lz/lzip, woff2, zip, 7z, pdf, …).
 * @return array [int mode, string stored]
 */
/**
 * Max raw size (bytes) for which non-BMP literals use zlib **level 9** in literal_bundle_gzip_probe_level (bytes-first; was always 1).
 * FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES: default 2097152 (2 MiB); 0 = keep legacy level-1 probes for all non-BMP sizes.
 * FRACTAL_ZIP_SPEED=1 clamps the effective cap to 524288 unless the env var is set explicitly (preserves fast iteration when the var is unset).
 */
public static function literal_nonbmp_gzip9_max_probe_bytes(): int {
	static $cap = null;
	if($cap === null) {
		$e = getenv('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES');
		$unset = ($e === false || trim((string) $e) === '');
		if($unset) {
			$cap = 2097152;
		} else {
			$v = (int) trim((string) $e);
			$cap = $v <= 0 ? 0 : min(2147483647, $v);
		}
		if(fractal_zip::speed_mode_enabled() && $unset && $cap > 0) {
			$cap = min($cap, 524288);
		}
	}
	return $cap;
}

/**
 * Whether $relPath looks like a terminal `.flac` member (case-insensitive).
 */
public static function literal_bundle_is_flac_rel(?string $relPath): bool {
	if($relPath === null || (string) $relPath === '') {
		return false;
	}
	return str_ends_with(strtolower((string) $relPath), '.flac');
}

/**
 * Whether <code>$relPath</code> is a Microsoft packaged document we treat like other ZIP‑semantic literals for prescreening:
 * Open XML / OPC (Office 2007+ <code>.docx</code>, <code>.xlsx</code>, …), Store/App packages (<code>.appx</code>, <code>.msix</code>, …),
 * and legacy OLE compound <code>.xls</code>. Gzip‑1 byte ratio is a weak proxy for these; literal tournament and higher probe levels apply.
 */
public static function literal_bundle_rel_is_ms_office_open_container(?string $relPath): bool {
	if($relPath === null || (string) $relPath === '') {
		return false;
	}
	$r = strtolower((string) $relPath);
	static $sfx = null;
	if($sfx === null) {
		$sfx = array(
			'.xls',
			'.xlsx', '.xlsm', '.xltx', '.xltm', '.xlam',
			'.docx', '.docm', '.dotx', '.dotm',
			'.pptx', '.pptm', '.potx', '.potm', '.ppsx', '.ppsm', '.ppam',
			'.vsdx', '.vssx', '.vstx',
			'.sldx', '.sldm',
			'.thmx',
			'.appx', '.appxbundle', '.msix', '.msixbundle',
		);
	}
	foreach($sfx as $s) {
		if(str_ends_with($r, $s)) {
			return true;
		}
	}
	return false;
}

/**
 * True when <code>$relPath</code> is semantically “inside” an MS Office–style container path: parent directory ends with
 * an open-container suffix (e.g. mode 19 <code>workbook.xls/Sheet1</code>), or the path is an OLE mode 19 template
 * (<code>…#ole-template</code>) whose base name is such a container. Those paths do not end in <code>.xls</code> / <code>.docx</code>
 * themselves; without this, gzip‑1 ratio gates and zlib‑1 probe scoring treat them like generic binaries.
 */
public static function literal_bundle_rel_is_ms_office_semantic_inner(?string $relPath): bool {
	if($relPath === null || (string) $relPath === '') {
		return false;
	}
	$r = strtolower(str_replace('\\', '/', (string) $relPath));
	$oleTpl = '#ole-template';
	if(str_contains($r, $oleTpl)) {
		$base = rtrim(substr($r, 0, strpos($r, $oleTpl)), '/');
		return $base !== '' && fractal_zip::literal_bundle_rel_is_ms_office_open_container($base);
	}
	$i = strrpos($r, '/');
	if($i === false || $i === 0) {
		return false;
	}
	$parent = substr($r, 0, $i);
	return fractal_zip::literal_bundle_rel_is_ms_office_open_container($parent);
}

/**
 * FRACTAL_ZIP_LITERAL_MERGED_FZBM_MAX_BYTES: max merged raw bytes for FZBM inner when the folder has ≥2 members.
 * Unset, empty, or non-numeric ⇒ 0 (no cap).
 */
public static function literal_merged_fzbm_max_raw_bytes_cap(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_MERGED_FZBM_MAX_BYTES');
	if($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 0;
	}
	return $cached = (int) $e;
}

/** False when FRACTAL_ZIP_DISABLE_LITERAL_MERGED_FZBM=1. */
public static function literal_merged_fzbm_inner_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_DISABLE_LITERAL_MERGED_FZBM') !== '1';
}

/** FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE: speed-default profile (unset => on, set 0/off/false/no => off). */
public static function lifestyle_speed_profile_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/** In lifestyle speed profile, very small merged literals may still do full FZBM order search for bytes. */
public static function lifestyle_fzbm_order_tryhard_max_raw_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LIFESTYLE_FZBM_ORDER_TRYHARD_MAX_RAW_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 0 : max(0, (int) trim((string) $e));
}

/** In speed profile, prefilter FZBM path-order candidates with cheap gzip-1 score and keep top-K for exact scoring. */
public static function lifestyle_fzbm_path_order_prefilter_top_k(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LIFESTYLE_FZBM_PATH_ORDER_PREFILTER_TOP_K');
	return $cached = ($e === false || trim((string) $e) === '') ? 6 : max(0, (int) trim((string) $e));
}

/** In speed profile, prefilter FZB4 path-order candidates with cheap gzip-1 score and keep top-K for exact scoring. */
public static function lifestyle_fzb4_path_order_prefilter_top_k(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LIFESTYLE_FZB4_PATH_ORDER_PREFILTER_TOP_K');
	return $cached = ($e === false || trim((string) $e) === '') ? 6 : max(0, (int) trim((string) $e));
}

/** Global tiny-input shortcut: skip FZBM order search when merged literal payload is very small. */
public static function literal_bundle_fzbm_order_tiny_skip_max_raw_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_TINY_SKIP_MAX_RAW_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 1024 : max(0, (int) trim((string) $e));
}

/** FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER unset ⇒ probe; explicit 0/off/false/no ⇒ off. */
public static function literal_bundle_fzbm_concat_order_probe_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$offEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER');
	if($offEnv === false || trim((string) $offEnv) === '') {
		return $cached = true;
	}
	$ov = strtolower(trim((string) $offEnv));
	return $cached = !($ov === '0' || $ov === 'off' || $ov === 'false' || $ov === 'no');
}

public static function literal_bundle_fzbm_order_exhaustive_max_n(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$exhMaxEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_EXHAUSTIVE_MAX_N');
	if($exhMaxEnv === false || trim((string) $exhMaxEnv) === '') {
		return $cached = fractal_zip::ultra_compression_enabled() ? 8 : 5;
	}
	return $cached = max(2, min(8, (int) trim((string) $exhMaxEnv)));
}

public static function literal_bundle_fzbm_order_random_tries(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$randTriesEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES');
	if($randTriesEnv === false || trim((string) $randTriesEnv) === '') {
		return $cached = fractal_zip::ultra_compression_enabled() ? 512 : 24;
	}
	return $cached = max(0, min(2048, (int) trim((string) $randTriesEnv)));
}

/** True when FRACTAL_ZIP_BROTLI_LGWIN is unset or empty (FZB* path-order / outer Q11: default + w=16, optional w=20/22/24 for textlike when few order candidates). */
public static function literal_bundle_brotli_lgwin_env_unset_or_empty(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BROTLI_LGWIN');
	return $cached = ($e === false || trim((string) $e) === '');
}

/**
 * FZBM/FZB4 path-order: try Brotli w=20/22/24 in the Brotli proxy when there are at most this many order candidates
 * (default 1; larger sets use default + w=16 only to cap CPU). Unset/empty: 1. FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND.
 */
public static function literal_bundle_path_order_lgwin_sweep_max_candidates(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND');
	return $cached = ($e === false || trim((string) $e) === '') ? 1 : max(1, min(10000, (int) trim((string) $e)));
}

/** Path-order scorer cap for non-textlike inners: unset/empty => 7. 0 disables this cap. FRACTAL_ZIP_ULTRA=1 defaults to 0 (score all candidates). */
public static function literal_bundle_path_order_nontextlike_max_candidates(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PATH_ORDER_NONTEXT_MAX_CAND');
	if($e === false || trim((string) $e) === '') {
		return $cached = fractal_zip::ultra_compression_enabled() ? 0 : 7;
	}
	return $cached = max(0, min(10000, (int) trim((string) $e)));
}

/** FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER unset ⇒ probe; explicit 0/off/false/no ⇒ off. */
public static function literal_bundle_fzb4_path_order_probe_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$offEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER');
	if($offEnv === false || trim((string) $offEnv) === '') {
		return $cached = true;
	}
	$ov = strtolower(trim((string) $offEnv));
	return $cached = !($ov === '0' || $ov === 'off' || $ov === 'false' || $ov === 'no');
}

public static function literal_bundle_fzb4_path_order_max_members(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$maxNEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER_MAX_MEMBERS');
	return $cached = ($maxNEnv === false || trim((string) $maxNEnv) === '') ? 512 : max(2, min(512, (int) trim((string) $maxNEnv)));
}

public static function literal_bundle_fzb4_path_exhaustive_max_n(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$exhMaxEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_EXHAUSTIVE_MAX_N');
	if($exhMaxEnv === false || trim((string) $exhMaxEnv) === '') {
		return $cached = fractal_zip::ultra_compression_enabled() ? 8 : 5;
	}
	return $cached = max(2, min(8, (int) trim((string) $exhMaxEnv)));
}

public static function literal_bundle_fzb4_path_random_tries(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$randTriesEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_RANDOM_TRIES');
	if($randTriesEnv === false || trim((string) $randTriesEnv) === '') {
		return $cached = fractal_zip::ultra_compression_enabled() ? 512 : 36;
	}
	return $cached = max(0, min(2048, (int) trim((string) $randTriesEnv)));
}

/** FRACTAL_ZIP_LITERAL_BUNDLE_LAYOUT_USE_PROXY_SCORE: unset ⇒ true. */
public static function literal_bundle_layout_use_proxy_score(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$layoutProxyEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_LAYOUT_USE_PROXY_SCORE');
	return $cached = ($layoutProxyEnv === false || trim((string) $layoutProxyEnv) === '') ? true : ((int) $layoutProxyEnv !== 0);
}

public static function literal_bundle_raster_probe_zlib9_max_combined_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$zlib9MaxEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_RASTER_PROBE_ZLIB9_MAX_COMBINED_BYTES');
	return $cached = ($zlib9MaxEnv === false || trim((string) $zlib9MaxEnv) === '') ? (8 * 1024 * 1024) : max(0, (int) $zlib9MaxEnv);
}

/** FRACTAL_ZIP_LITERAL_ZIP_MULTI_ALWAYS=1: skip deflate compare and force mode 18 as best. */
public static function literal_bundle_zip_multimember_always_mode18(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	return $cached = getenv('FRACTAL_ZIP_LITERAL_ZIP_MULTI_ALWAYS') === '1';
}

/** FRACTAL_ZIP_LITERAL_BUNDLE_DECODE_MAX_DEPTH for nested semantic / gzip / ZIP / MPQ / mode 18 literals. Unset ⇒ 128. */
public static function literal_bundle_decode_max_depth(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$maxDepthEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_DECODE_MAX_DEPTH');
	return $cached = ($maxDepthEnv === false || trim((string) $maxDepthEnv) === '') ? 128 : max(8, min(4096, (int) $maxDepthEnv));
}

/**
 * FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID: unset/empty ⇒ off; 1/true/on/yes ⇒ on (same rules as nested FZBF segment tournament).
 */
public static function literal_synth_segment_grid_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$gridEnv = getenv('FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID');
	if($gridEnv === false || trim((string) $gridEnv) === '') {
		return $cached = false;
	}
	$gl = strtolower(trim((string) $gridEnv));
	return $cached = ($gridEnv === '1' || $gl === 'true' || $gl === 'on' || $gl === 'yes');
}

/** FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID_MAX_MERGED_BYTES; unset ⇒ 393216 (384 KiB). */
public static function literal_synth_segment_grid_max_merged_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$maxEnv = getenv('FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID_MAX_MERGED_BYTES');
	return $cached = ($maxEnv !== false && trim((string) $maxEnv) !== '') ? max(0, (int) $maxEnv) : (384 * 1024);
}

/**
 * Max raw `.flac` size (bytes) for **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE=auto`**. **0** = no cap (full tournament on any size; RAM-heavy).
 * Unset default **16777216** (16 MiB).
 */
public static function literal_flac_transform_probe_max_bytes(): int {
	static $cap = null;
	if($cap === null) {
		$e = getenv('FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE_MAX_BYTES');
		if($e === false || trim((string) $e) === '') {
			$cap = 16777216;
		} else {
			$v = (int) trim((string) $e);
			$cap = $v <= 0 ? 0 : min(2147483647, $v);
		}
	}
	return $cap;
}

/**
 * Run full literal delta/xor/… tournament on **disk** FLAC bytes (not decoded PCM). **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE`**:
 * unset / **0** / off = legacy behaviour; **1** / on = always (any size); **auto** = on when `strlen <=` literal_flac_transform_probe_max_bytes().
 */
public static function literal_flac_want_full_candidate_tournament(?string $relPath, int $rawLen): bool {
	if(!fractal_zip::literal_bundle_is_flac_rel($relPath)) {
		return false;
	}
	static $mode = null;
	if($mode === null) {
		$e = getenv('FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE');
		if($e === false || trim((string) $e) === '') {
			$mode = 'unset';
		} else {
			$v = strtolower(trim((string) $e));
			if($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
				$mode = 'off';
			} elseif($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes') {
				$mode = 'on';
			} elseif($v === 'auto') {
				$mode = 'auto';
			} else {
				$mode = 'invalid';
			}
		}
	}
	if($mode === 'unset' || $mode === 'off' || $mode === 'invalid') {
		return false;
	}
	if($mode === 'on') {
		return true;
	}
	$cap = fractal_zip::literal_flac_transform_probe_max_bytes();
	return $cap === 0 || $rawLen <= $cap;
}

/**
 * zlib deflate level for literal-bundle size probes in choose_best_literal_bundle_transform (candidates + chain scoring).
 * FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL: force 1–9 for all payloads.
 * Else for uncompressed BI_RGB BMP: FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL (default 9 = bytes-first; set 1–8 to trade speed).
 * Else small non-BMP: level 9 up to literal_nonbmp_gzip9_max_probe_bytes() (FRACTAL_ZIP_SPEED=1 clamps default cap to 512 KiB); larger non-BMP uses level 1.
 * VGM/VGZ members always use level 9 for probes (logged chiptune: zlib-1 underrates useful transforms vs outer gzip).
 * OLE mode 19 inner paths (<code>container.xls/stream</code>, <code>…#ole-template</code>) use level 9 via literal_bundle_rel_is_ms_office_semantic_inner.
 * When **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE`** is on / auto (size-gated), `.flac` uses level **9** unless **`FRACTAL_ZIP_LITERAL_FLAC_GZIP_PROBE_LEVEL`** overrides.
 * Chain inner rounds default to the same level; override with FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL.
 *
 * @param string|null $relPath original member path (e.g. ends in .vgz before unwrap inside choose_best)
 */
function literal_bundle_gzip_probe_level($rawBytes, $relPath = null) {
	$rawBytes = (string)$rawBytes;
	static $zipLitGzipProbeForced = null;
	if($zipLitGzipProbeForced === null) {
		$g = getenv('FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL');
		$zipLitGzipProbeForced = ($g !== false && trim((string)$g) !== '') ? max(1, min(9, (int)$g)) : 0;
	}
	if($zipLitGzipProbeForced > 0) {
		return $zipLitGzipProbeForced;
	}
	static $flacGzipProbeLev = null;
	if($flacGzipProbeLev === null) {
		$fe = getenv('FRACTAL_ZIP_LITERAL_FLAC_GZIP_PROBE_LEVEL');
		$flacGzipProbeLev = ($fe !== false && trim((string) $fe) !== '') ? max(1, min(9, (int) $fe)) : 0;
	}
	if($relPath !== null && $relPath !== '') {
		$relLow = strtolower((string) $relPath);
		// VGM/VGZ / .zip / Microsoft OPC–Open XML & packaged Office: level-9 probes (same suffix rules as choose_best_literal_bundle_transform fast-exit exceptions).
		if(str_ends_with($relLow, '.vgm') || str_ends_with($relLow, '.vgz') || str_ends_with($relLow, '.zip')
			|| fractal_zip::literal_bundle_rel_is_ms_office_open_container((string) $relPath)
			|| fractal_zip::literal_bundle_rel_is_ms_office_semantic_inner((string) $relPath)) {
			return 9;
		}
		if(fractal_zip::literal_flac_want_full_candidate_tournament((string) $relPath, strlen($rawBytes))) {
			return $flacGzipProbeLev > 0 ? $flacGzipProbeLev : 9;
		}
	}
	if($this->bmp_parse_simple_uncompressed_rgb($rawBytes, true) === null) {
		$cap = fractal_zip::literal_nonbmp_gzip9_max_probe_bytes();
		$n = strlen($rawBytes);
		if($cap > 0 && $n > 0 && $n <= $cap) {
			return 9;
		}
		return 1;
	}
	static $bmpZipProbeLevel = null;
	if($bmpZipProbeLevel === null) {
		$b = getenv('FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL');
		$bmpZipProbeLevel = ($b === false || trim((string)$b) === '') ? 9 : max(1, min(9, (int)$b));
	}
	return $bmpZipProbeLevel;
}

/** @return int|false */
function literal_bundle_gzip_deflate_len($bytes, $level) {
	$bytes = (string)$bytes;
	$level = (int)$level;
	// 16-slot MRU-ish cache: literal tournaments / greedy chains revisit the same blobs often.
	static $b0 = null;
	static $l0 = null;
	static $r0 = null;
	static $b1 = null;
	static $l1 = null;
	static $r1 = null;
	static $b2 = null;
	static $l2 = null;
	static $r2 = null;
	static $b3 = null;
	static $l3 = null;
	static $r3 = null;
	static $b4 = null;
	static $l4 = null;
	static $r4 = null;
	static $b5 = null;
	static $l5 = null;
	static $r5 = null;
	static $b6 = null;
	static $l6 = null;
	static $r6 = null;
	static $b7 = null;
	static $l7 = null;
	static $r7 = null;
	static $b8 = null;
	static $l8 = null;
	static $r8 = null;
	static $b9 = null;
	static $l9 = null;
	static $r9 = null;
	static $b10 = null;
	static $l10 = null;
	static $r10 = null;
	static $b11 = null;
	static $l11 = null;
	static $r11 = null;
	static $b12 = null;
	static $l12 = null;
	static $r12 = null;
	static $b13 = null;
	static $l13 = null;
	static $r13 = null;
	static $b14 = null;
	static $l14 = null;
	static $r14 = null;
	static $b15 = null;
	static $l15 = null;
	static $r15 = null;
	if($b0 === $bytes && $l0 === $level) {
		return $r0;
	}
	if($b1 === $bytes && $l1 === $level) {
		return $r1;
	}
	if($b2 === $bytes && $l2 === $level) {
		return $r2;
	}
	if($b3 === $bytes && $l3 === $level) {
		return $r3;
	}
	if($b4 === $bytes && $l4 === $level) {
		return $r4;
	}
	if($b5 === $bytes && $l5 === $level) {
		return $r5;
	}
	if($b6 === $bytes && $l6 === $level) {
		return $r6;
	}
	if($b7 === $bytes && $l7 === $level) {
		return $r7;
	}
	if($b8 === $bytes && $l8 === $level) {
		return $r8;
	}
	if($b9 === $bytes && $l9 === $level) {
		return $r9;
	}
	if($b10 === $bytes && $l10 === $level) {
		return $r10;
	}
	if($b11 === $bytes && $l11 === $level) {
		return $r11;
	}
	if($b12 === $bytes && $l12 === $level) {
		return $r12;
	}
	if($b13 === $bytes && $l13 === $level) {
		return $r13;
	}
	if($b14 === $bytes && $l14 === $level) {
		return $r14;
	}
	if($b15 === $bytes && $l15 === $level) {
		return $r15;
	}
	$p = gzdeflate($bytes, $level);
	$r = ($p === false) ? false : strlen($p);
	$b15 = $b14;
	$l15 = $l14;
	$r15 = $r14;
	$b14 = $b13;
	$l14 = $l13;
	$r14 = $r13;
	$b13 = $b12;
	$l13 = $l12;
	$r13 = $r12;
	$b12 = $b11;
	$l12 = $l11;
	$r12 = $r11;
	$b11 = $b10;
	$l11 = $l10;
	$r11 = $r10;
	$b10 = $b9;
	$l10 = $l9;
	$r10 = $r9;
	$b9 = $b8;
	$l9 = $l8;
	$r9 = $r8;
	$b8 = $b7;
	$l8 = $l7;
	$r8 = $r7;
	$b7 = $b6;
	$l7 = $l6;
	$r7 = $r6;
	$b6 = $b5;
	$l6 = $l5;
	$r6 = $r5;
	$b5 = $b4;
	$l5 = $l4;
	$r5 = $r4;
	$b4 = $b3;
	$l4 = $l3;
	$r4 = $r3;
	$b3 = $b2;
	$l3 = $l2;
	$r3 = $r2;
	$b2 = $b1;
	$l2 = $l1;
	$r2 = $r1;
	$b1 = $b0;
	$l1 = $l0;
	$r1 = $r0;
	$b0 = $bytes;
	$l0 = $level;
	$r0 = $r;
	return $r;
}

/**
 * Mode 18 payload: varint member_count + (varint nameLen + name + u8 inner_mode + varint inner_store_len + inner_store)*.
 * Recursively runs choose_best_literal_bundle_transform per ZIP entry (path = container/member) so .vgz/.vgm peels and transforms apply.
 *
 * @return string|null
 */
function literal_bundle_encode_multimember_zip_mode18(string $zipBytes, string $containerRelPath): ?string {
	fractal_zip_ensure_literal_pac_stack_loaded();
	if(!fractal_zip_literal_semantic_zip_multimember_enabled()) {
		return null;
	}
	$members = fractal_zip_literal_pac_list_zip_members_for_mode18($zipBytes);
	if($members === null) {
		return null;
	}
	$base = str_replace('\\', '/', (string) $containerRelPath);
	$base = trim($base, '/');
	if($base === '') {
		return null;
	}
	$parts = array($this->encode_varint_u32(sizeof($members)));
	foreach($members as $m) {
		$mname = (string) $m['name'];
		$data = (string) $m['data'];
		$childPath = $base . '/' . str_replace('\\', '/', $mname);
		list($cm, $cstore) = $this->choose_best_literal_bundle_transform($data, $childPath);
		$cstore = (string) $cstore;
		$parts[] = $this->encode_varint_u32(strlen($mname)) . $mname . chr((int) $cm) . $this->encode_varint_u32(strlen($cstore)) . $cstore;
	}
	return implode('', $parts);
}

/**
 * If multi-member ZIP mode 18 beats current deflate proxy, update best* (used from early-return branches and tail).
 */
function literal_bundle_consider_multimember_zip_mode18(string $rawBytes, ?string $relPath, $gzipProbeLevel, &$bestLen, &$bestMode, &$bestStore): void {
	$cont = ($relPath !== null && (string) $relPath !== '') ? (string) $relPath : 'archive.zip';
	$m = $this->literal_bundle_encode_multimember_zip_mode18($rawBytes, $cont);
	if($m === null) {
		return;
	}
	if(fractal_zip::literal_bundle_zip_multimember_always_mode18()) {
		$bestLen = 0;
		$bestMode = 18;
		$bestStore = $m;
		return;
	}
	$l = $this->literal_bundle_gzip_deflate_len($m, $gzipProbeLevel);
	if($l === false) {
		return;
	}
	if($l < $bestLen || ($l === $bestLen && 18 < $bestMode)) {
		$bestLen = $l;
		$bestMode = 18;
		$bestStore = $m;
	}
}

/**
 * Mode 19: OLE compound (CFB) — full-file zeroed template + per-stream file ranges and inner literals.
 *
 * @return string|null
 */
function literal_bundle_encode_ole_mode19(string $cfbBytes, string $containerRelPath): ?string {
	fractal_zip_ensure_ole_cfb_loaded();
	if(!fractal_zip_literal_semantic_ole_enabled()) {
		return null;
	}
	$parsed = fractal_zip_ole_build_template_and_streams($cfbBytes);
	if($parsed === null) {
		return null;
	}
	$template = (string) $parsed['template'];
	$streams = $parsed['streams'];
	$fileLen = strlen($cfbBytes);
	if(strlen($template) !== $fileLen) {
		return null;
	}
	$base = str_replace('\\', '/', (string) $containerRelPath);
	$base = trim($base, '/');
	if($base === '') {
		return null;
	}
	$parts = array($this->encode_varint_u32($fileLen), $this->encode_varint_u32(sizeof($streams)));
	foreach($streams as $st) {
		$mname = (string) $st['name'];
		$ranges = $st['ranges'];
		$parts[] = $this->encode_varint_u32(strlen($mname)) . $mname;
		$parts[] = $this->encode_varint_u32(sizeof($ranges));
		foreach($ranges as $pair) {
			$parts[] = $this->encode_varint_u32((int) $pair[0]) . $this->encode_varint_u32((int) $pair[1]);
		}
		$data = (string) $st['data'];
		$childPath = $base . '/' . str_replace('\\', '/', $mname);
		list($cm, $cstore) = $this->choose_best_literal_bundle_transform($data, $childPath);
		$cstore = (string) $cstore;
		$parts[] = chr((int) $cm) . $this->encode_varint_u32(strlen($cstore)) . $cstore;
	}
	$tplPath = $base . '#ole-template';
	list($tm, $tstore) = $this->choose_best_literal_bundle_transform($template, $tplPath);
	$tstore = (string) $tstore;
	$parts[] = chr((int) $tm) . $this->encode_varint_u32(strlen($tstore)) . $tstore;
	return implode('', $parts);
}

/**
 * If OLE mode 19 beats current deflate proxy, update best* (mirrors mode 18 consider path).
 */
function literal_bundle_consider_ole_mode19(string $rawBytes, ?string $relPath, $gzipProbeLevel, &$bestLen, &$bestMode, &$bestStore): void {
	if($relPath !== null && str_contains((string) $relPath, '#ole-template')) {
		return;
	}
	if(strlen($rawBytes) < 8 || substr($rawBytes, 0, 8) !== "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
		return;
	}
	fractal_zip_ensure_ole_cfb_loaded();
	if(!fractal_zip_ole_is_compound($rawBytes)) {
		return;
	}
	$cont = ($relPath !== null && (string) $relPath !== '') ? (string) $relPath : 'document.bin';
	$m = $this->literal_bundle_encode_ole_mode19($rawBytes, $cont);
	if($m === null) {
		return;
	}
	$l = $this->literal_bundle_gzip_deflate_len($m, $gzipProbeLevel);
	if($l === false) {
		return;
	}
	if($l < $bestLen || ($l === $bestLen && 19 < $bestMode)) {
		$bestLen = $l;
		$bestMode = 19;
		$bestStore = $m;
	}
}

function literal_bundle_consider_bmp_row_column_deltas($rawBytes, &$bestLen, &$bestMode, &$bestStore, $probeLev = 1) {
	$probeLev = max(1, min(9, (int)$probeLev));
	$m = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
	if($m !== null) {
		$pairs = array(
			array(5, $this->encode_bmp_row_horizontal_delta_payload($rawBytes, $m)),
			array(13, $this->encode_bmp_column_vertical_delta_payload($rawBytes, $m)),
		);
	} else {
		$pairs = array(
			array(5, $this->encode_bmp_row_horizontal_delta_payload($rawBytes)),
			array(13, $this->encode_bmp_column_vertical_delta_payload($rawBytes)),
		);
	}
	foreach($pairs as $pair) {
		list($m, $blob) = $pair;
		if($blob === null) {
			continue;
		}
		$len = $this->literal_bundle_gzip_deflate_len($blob, $probeLev);
		if($len === false) {
			continue;
		}
		if($len < $bestLen || ($len === $bestLen && $m < $bestMode)) {
			$bestLen = $len;
			$bestMode = $m;
			$bestStore = $blob;
		}
	}
}

/**
 * Try one lossless literal transform; returns encoded stored blob or null if N/A / no change.
 */
function literal_bundle_encode_one_transform_mode($mode, $data) {
	$data = (string)$data;
	$mode = (int)$mode;
	if($mode === 1) {
		$o = $this->delta_encode_bytes($data);
		return ($o !== $data) ? $o : null;
	}
	if($mode === 2) {
		if(strlen($data) < 2) {
			return null;
		}
		$o = $this->xor_adjacent_encode_bytes($data);
		return ($o !== $data) ? $o : null;
	}
	if($mode === 3) {
		$o = $this->nibble_swap_bytes($data);
		return ($o !== $data) ? $o : null;
	}
	if($mode === 4) {
		$o = $this->invert_bytes($data);
		return ($o !== $data) ? $o : null;
	}
	if($mode === 5) {
		$o = $this->encode_bmp_row_horizontal_delta_payload($data);
		return ($o !== null && $o !== $data) ? $o : null;
	}
	if($mode === 12) {
		if(strlen($data) < 2) {
			return null;
		}
		$o = strrev($data);
		return ($o !== $data) ? $o : null;
	}
	if($mode === 13) {
		$o = $this->encode_bmp_column_vertical_delta_payload($data);
		return ($o !== null && $o !== $data) ? $o : null;
	}
	if($mode === 14) {
		if(strlen($data) < 4) {
			return null;
		}
		$o = $this->literal_square_block_transpose_prefix($data);
		return ($o !== null && $o !== $data) ? $o : null;
	}
	if($mode === 15) {
		$o = $this->encode_bmp_block_scalar_shift_payload($data);
		return ($o !== null && $o !== $data) ? $o : null;
	}
	if($mode === 16) {
		$o = $this->encode_bmp_block_core_scalar_shift_payload($data);
		return ($o !== null && $o !== $data) ? $o : null;
	}
	return null;
}

/**
 * True if $a should win over $b at equal gzip length (shorter chain or lexicographic mode list).
 * @param list<int> $a
 * @param list<int> $b
 */
function literal_bundle_chain_modes_prefer(array $a, array $b) {
	$na = sizeof($a);
	$nb = sizeof($b);
	if($na !== $nb) {
		return $na < $nb;
	}
	for($i = 0; $i < $na; $i++) {
		if($a[$i] !== $b[$i]) {
			return $a[$i] < $b[$i];
		}
	}
	return false;
}

/**
 * Enumerate all 2-deep chains over BMP grid modes {12,16,15,13,5} (lossless). Catches non-greedy first-step choices
 * (incl. reverse before/after row or column delta). Off by default (set FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN=1 to enable).
 * @return array{len: int, blob: string, modes: list<int>}|null
 */
function literal_bundle_bmp_exhaustive_grid_chains($rawBytes, $probeLev, $lightLargeText) {
	$rawBytes = (string)$rawBytes;
	if($lightLargeText) {
		return null;
	}
	// Greedy chain (same function family) already covers most wins; exhaustive 5×5 mode-2 grid search is O(25×encode×gzip)
	// and dominated BMP literal CPU under XHProf. Skip when FRACTAL_ZIP_SPEED=1 unless FRACTAL_ZIP_DEEP=1 restores it.
	if(fractal_zip::speed_mode_enabled() && !fractal_zip::deep_mode_enabled()) {
		return null;
	}
	static $bmpExhaustiveGate = null;
	if($bmpExhaustiveGate === null) {
		$exEnv = getenv('FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN');
		if($exEnv === false || trim((string) $exEnv) === '') {
			$bmpExhaustiveGate = 'deny';
		} else {
			$ev = strtolower(trim((string) $exEnv));
			$bmpExhaustiveGate = ($ev === '0' || $ev === 'off' || $ev === 'false' || $ev === 'no') ? 'deny' : 'allow';
		}
	}
	if($bmpExhaustiveGate === 'deny') {
		return null;
	}
	$base = array(12, 16, 15, 13, 5);
	$best = null;
	foreach($base as $m1) {
		$e1 = $this->literal_bundle_encode_one_transform_mode($m1, $rawBytes);
		if($e1 === null) {
			continue;
		}
		foreach($base as $m2) {
			$e2 = $this->literal_bundle_encode_one_transform_mode($m2, $e1);
			if($e2 === null || $e2 === $e1) {
				continue;
			}
			$modes2 = array($m2, $m1);
			$blob2 = $this->encode_literal_transform_chain_payload($modes2, $e2);
			if($blob2 === null) {
				continue;
			}
			$len2 = $this->literal_bundle_gzip_deflate_len($blob2, $probeLev);
			if($len2 === false) {
				continue;
			}
			if($best === null || $len2 < $best['len'] || ($len2 === $best['len'] && $this->literal_bundle_chain_modes_prefer($modes2, $best['modes']))) {
				$best = array('len' => $len2, 'blob' => $blob2, 'modes' => $modes2);
			}
		}
	}
	return $best;
}

/**
 * Multipass greedy chain: each round tries all listed modes on the current blob and picks the **smallest gzip** length at $probeLev;
 * ties break by priority order. Repeat until no mode beats the current gzip size.
 * Priority: 16, 13, 5, 15 (squarebytes / vertical / row / full-cell BSS1), then 14, 12, 1, 2, 3, 4.
 * From round 2 onward, generic byte transforms (1,2,3,4,12) are skipped unless FRACTAL_ZIP_LITERAL_CHAIN_DEEP_FULL=1 (BMP/grid modes only).
 * Disable: FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN=0. Max rounds: FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER (default 64 for bytes-first).
 * `choose_best_literal_bundle_transform` runs the chain only for uncompressed BI_RGB BMPs (same detector as other BMP literals) to limit cost.
 * @return array{0: list<int>, 1: string} mode list (decode order: apply modes[0] first to innermost blob), innermost stored bytes
 */
function literal_bundle_greedy_transform_chain($rawBytes, $lightLargeText, $tryExotic, $probeLev) {
	$rawBytes = (string)$rawBytes;
	$probeLev = max(1, min(9, (int)$probeLev));
	if(fractal_zip::speed_mode_enabled()) {
		$capEnv = getenv('FRACTAL_ZIP_SPEED_LITERAL_CHAIN_PROBE_LEVEL');
		if($capEnv !== false && trim((string) $capEnv) !== '') {
			$probeLev = min($probeLev, max(1, min(9, (int) trim((string) $capEnv))));
		} else {
			$se = getenv('FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL');
			if($se === false || trim((string) $se) === '') {
				$probeLev = min($probeLev, 1);
			}
		}
	}
	$priority = array(16, 13, 5, 15, 14, 12, 1, 2, 3, 4);
	$chainModes = array();
	$current = $rawBytes;
	static $chainMaxIterStatic = null;
	static $chainDeepFullStatic = null;
	if($chainMaxIterStatic === null) {
		$maxIterEnv = getenv('FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER');
		$chainMaxIterStatic = ($maxIterEnv === false || trim((string) $maxIterEnv) === '') ? null : max(1, min(512, (int) $maxIterEnv));
	}
	if($chainDeepFullStatic === null) {
		$deepFullEnv = getenv('FRACTAL_ZIP_LITERAL_CHAIN_DEEP_FULL');
		$chainDeepFullStatic = ($deepFullEnv !== false && trim((string) $deepFullEnv) !== '' && ($deepFullEnv === '1' || strtolower(trim((string) $deepFullEnv)) === 'true' || strtolower(trim((string) $deepFullEnv)) === 'on'));
	}
	$maxIter = ($chainMaxIterStatic !== null) ? (int) $chainMaxIterStatic : (fractal_zip::speed_mode_enabled() ? 4 : 64);
	$deepFull = $chainDeepFullStatic;
	$priIdx = array_flip($priority);
	$iter = 0;
	while($iter < $maxIter) {
		$iter++;
		$curLen = $this->literal_bundle_gzip_deflate_len($current, $probeLev);
		if($curLen === false) {
			break;
		}
		$bestRound = null;
		$deepRound = ($iter >= 2);
		foreach($priority as $m) {
			if($deepRound && !$deepFull && ($m === 1 || $m === 2 || $m === 3 || $m === 4 || $m === 12)) {
				continue;
			}
			if($lightLargeText && $m !== 1 && $m !== 2) {
				continue;
			}
			if(!$tryExotic && ($m === 3 || $m === 4)) {
				continue;
			}
			$enc = $this->literal_bundle_encode_one_transform_mode($m, $current);
			if($enc === null) {
				continue;
			}
			$len = $this->literal_bundle_gzip_deflate_len($enc, $probeLev);
			if($len === false) {
				continue;
			}
			if($len >= $curLen) {
				continue;
			}
			$pi = isset($priIdx[$m]) ? (int)$priIdx[$m] : 999;
			if($bestRound === null || $len < $bestRound['len'] || ($len === $bestRound['len'] && $pi < $bestRound['pi'])) {
				$bestRound = array('len' => $len, 'pi' => $pi, 'm' => $m, 'enc' => $enc);
			}
		}
		if($bestRound === null) {
			break;
		}
		array_unshift($chainModes, $bestRound['m']);
		$current = $bestRound['enc'];
	}
	return array($chainModes, $current);
}

/**
 * Mode 17 stored body: u8 n (>=2) + n×u8 mode + varint len + innermost payload. Decode applies modes in listed order to innermost.
 * @param list<int> $modes
 * @return string|null
 */
function encode_literal_transform_chain_payload(array $modes, $innermost) {
	$innermost = (string)$innermost;
	$n = sizeof($modes);
	if($n < 2) {
		return null;
	}
	$modeChrs = array();
	foreach($modes as $m) {
		$mm = (int)$m;
		if($mm === 17 || $mm === 18 || $mm === 19 || $mm < 1 || $mm > 16 || ($mm >= 6 && $mm <= 11)) {
			return null;
		}
		$modeChrs[] = chr($mm);
	}
	return chr($n) . implode('', $modeChrs) . $this->encode_varint_u32(strlen($innermost)) . $innermost;
}

function decode_literal_transform_chain_payload($rawStored, $depth = 0) {
	$rawStored = (string)$rawStored;
	$n0 = strlen($rawStored);
	if($n0 < 3) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 17): truncated.');
	}
	$n = ord($rawStored[0]);
	if($n < 2 || $n > 64) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 17): bad chain length.');
	}
	$off = 1;
	$modes = array();
	for($i = 0; $i < $n; $i++) {
		if($off >= $n0) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 17): truncated modes.');
		}
		$mm = ord($rawStored[$off]);
		$off++;
		if($mm === 17 || $mm === 18 || $mm === 19 || $mm < 1 || $mm > 16 || ($mm >= 6 && $mm <= 11)) {
			fractal_zip::fatal_error('Corrupt FZB literal (mode 17): invalid inner mode in chain.');
		}
		$modes[] = $mm;
	}
	$bodyLen = $this->decode_varint_u32($rawStored, $off, $n0, 'FZB literal mode 17 body');
	if($bodyLen < 0 || $off + $bodyLen > $n0) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 17): body length.');
	}
	$body = substr($rawStored, $off, $bodyLen);
	$off += $bodyLen;
	if($off !== $n0) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 17): trailing bytes.');
	}
	$x = $body;
	foreach($modes as $m) {
		$x = $this->decode_bundle_literal_member((int)$m, $x, $depth);
	}
	return $x;
}

function choose_best_literal_bundle_transform($rawBytes, $relPath = null) {
	fractal_zip_ensure_literal_pac_stack_loaded();
	$rawBytes = (string)$rawBytes;
	$rel = ($relPath !== null && (string)$relPath !== '') ? (string)$relPath : '';
	$gzipStack = [];
	$semanticLayers = [];
	if($rel !== '') {
		list($work, $semanticLayers, $gzipStack) = fractal_zip_literal_deep_unwrap_with_layers($rel, $rawBytes);
		$rawBytes = $work;
	}
	$n = strlen($rawBytes);
	$wrapOut = static function($mode, $store) use ($gzipStack, $semanticLayers) {
		return fractal_zip_literal_bundle_wrap_all_layers($semanticLayers, $gzipStack, (int)$mode, (string)$store);
	};
	if($n === 0) {
		return $wrapOut(0, '');
	}
	$gzipProbeLevel = $this->literal_bundle_gzip_probe_level($rawBytes, $relPath);
	// gzip-1 length first: skip gates + large-text early exit avoid a redundant deflate at $gzipProbeLevel when unused.
	$ratioDeflateLen = $this->literal_bundle_gzip_deflate_len($rawBytes, 1);
	if($ratioDeflateLen === false) {
		return $wrapOut(0, $rawBytes);
	}
	static $skipTransMaxBase = null;
	static $flacSkipTransMaxOverride = null;
	static $tournamentStrictCached = null;
	if($skipTransMaxBase === null) {
		$e = getenv('FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO');
		$skipTransMaxBase = ($e === false || trim((string)$e) === '') ? 0.045 : max(0.0, min(1.0, (float)$e));
	}
	$skipTransMax = $skipTransMaxBase;
	if($rel !== '' && fractal_zip::literal_bundle_is_flac_rel($rel)) {
		if($flacSkipTransMaxOverride === null) {
			$fe = getenv('FRACTAL_ZIP_LITERAL_FLAC_SKIP_TRANSFORMS_MAX_GZIP1_RATIO');
			$flacSkipTransMaxOverride = ($fe !== false && trim((string) $fe) !== '') ? max(0.0, min(1.0, (float) $fe)) : -1.0;
		}
		if($flacSkipTransMaxOverride >= 0.0) {
			$skipTransMax = $flacSkipTransMaxOverride;
		}
	}
	if($tournamentStrictCached === null) {
		$tournamentStrictCached = getenv('FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT') === '1';
	}
	$tournamentStrict = $tournamentStrictCached;
	$flacFullProbe = fractal_zip::literal_flac_want_full_candidate_tournament($rel !== '' ? $rel : null, $n);
	// Uncompressed BI_RGB BMP often beats raw under modes 5/13 even when gzip-1(raw)/n is tiny (photo-like rasters).
	$skipTransformsEarly = ($n > 0 && ($ratioDeflateLen / $n) <= $skipTransMax);
	$bmpRgbOnce = null;
	if($skipTransformsEarly) {
		$bmpRgbOnce = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
		if($bmpRgbOnce !== null) {
			$skipTransformsEarly = false;
		}
	}
	// VGM/VGZ / .zip / Microsoft OPC–Open XML & packaged Office: avoid gzip-1 “incompressible” fast-exit (poor proxy; mode 18 peels ZIP; OOXML is ZIP‑packed structured XML).
	if($skipTransformsEarly && $rel !== '') {
		$relLow = strtolower($rel);
		if(str_ends_with($relLow, '.vgm') || str_ends_with($relLow, '.vgz') || str_ends_with($relLow, '.zip')
			|| fractal_zip::literal_bundle_rel_is_ms_office_open_container($rel)
			|| fractal_zip::literal_bundle_rel_is_ms_office_semantic_inner($rel)) {
			$skipTransformsEarly = false;
		}
	}
	if($skipTransformsEarly && $n >= 8 && substr($rawBytes, 0, 8) === "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1") {
		$skipTransformsEarly = false;
	}
	// FZCD merged-fractal PCM: zlib-1 ratio is a poor gate; always run transform tournament (same spirit as VGM/VGZ).
	if($skipTransformsEarly && $rel === '__fzcd_merged.pcm') {
		$skipTransformsEarly = false;
	}
	// Undecompressed FLAC: optional full literal tournament on container bytes (delta/xor/…); gzip-1 ratio is a weak proxy.
	if($skipTransformsEarly && $flacFullProbe) {
		$skipTransformsEarly = false;
	}
	if($tournamentStrict) {
		$skipTransformsEarly = false;
	}
	if($skipTransformsEarly) {
		return $wrapOut(0, $rawBytes);
	}
	$mostlyText = null;
	if($n >= 262144) {
		$mostlyText = $this->payload_looks_mostly_text($rawBytes);
	}
	$largeText = ($n >= 262144 && $mostlyText);
	static $alwaysProbeTransformsCached = null;
	static $largeTextGzip1MinRatio = null;
	if($alwaysProbeTransformsCached === null) {
		$alwaysProbeTransformsCached = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS') === '1';
	}
	$forceAllProbes = $alwaysProbeTransformsCached || $tournamentStrict;
	if($largeText && !$forceAllProbes) {
		if($largeTextGzip1MinRatio === null) {
			$ratioEnv = getenv('FRACTAL_ZIP_LITERAL_LARGE_TEXT_SKIP_PROBE_GZIP1_MIN_RATIO');
			$largeTextGzip1MinRatio = ($ratioEnv === false || trim((string)$ratioEnv) === '') ? 0.88 : max(0.0, min(1.0, (float)$ratioEnv));
		}
		$ratioMin = $largeTextGzip1MinRatio;
		if($n > 0 && $ratioDeflateLen >= ($n * $ratioMin)) {
			return $wrapOut(0, $rawBytes);
		}
	}
	$bestLen = ($gzipProbeLevel === 1) ? $ratioDeflateLen : $this->literal_bundle_gzip_deflate_len($rawBytes, $gzipProbeLevel);
	if($bestLen === false) {
		return $wrapOut(0, $rawBytes);
	}
	$rawGzLenProbe = $bestLen;
	$bestMode = 0;
	$bestStore = $rawBytes;
	static $maxTransformRawCached = null;
	if($maxTransformRawCached === null) {
		$maxTransformRawEnv = getenv('FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES');
		$maxTransformRawCached = ($maxTransformRawEnv === false || trim((string)$maxTransformRawEnv) === '') ? 4194304 : max(65536, (int)$maxTransformRawEnv);
	}
	$maxTransformRaw = $maxTransformRawCached;
	if(!$tournamentStrict && $maxTransformRaw > 0 && $n > $maxTransformRaw && !$flacFullProbe) {
		if($mostlyText === null) {
			$mostlyText = $this->payload_looks_mostly_text($rawBytes);
		}
		if(!$mostlyText) {
			$this->literal_bundle_consider_multimember_zip_mode18($rawBytes, $relPath, $gzipProbeLevel, $bestLen, $bestMode, $bestStore);
			$this->literal_bundle_consider_ole_mode19($rawBytes, $relPath, $gzipProbeLevel, $bestLen, $bestMode, $bestStore);
			if($n >= 524288) {
				$this->literal_bundle_consider_bmp_row_column_deltas($rawBytes, $bestLen, $bestMode, $bestStore, $gzipProbeLevel);
			}
			return $wrapOut($bestMode, $bestStore);
		}
	}
	$fzcdMergedPcm = ($rel === '__fzcd_merged.pcm');
	$lightLargeText = ($largeText && !$forceAllProbes);
	static $largeTextLightProbeDefaultOn = null;
	if($largeTextLightProbeDefaultOn === null) {
		$lightEnv = getenv('FRACTAL_ZIP_LITERAL_LARGE_TEXT_LIGHT_PROBE');
		$largeTextLightProbeDefaultOn = ($lightEnv === false || trim((string)$lightEnv) === '') ? true : ((int)$lightEnv !== 0);
	}
	if($lightLargeText) {
		$lightOnly = $largeTextLightProbeDefaultOn;
		if(!$lightOnly) {
			$lightLargeText = false;
		}
	}
	// One BMP parse for row/col/BSS candidates when the blob looks like a DIB (avoid repeat parses per encoder).
	if(!$lightLargeText && $bmpRgbOnce === null && $n >= 54 && substr($rawBytes, 0, 2) === 'BM') {
		$bmpRgbOnce = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
	}
	$candidates = array();
	$candidates[] = array(1, $this->delta_encode_bytes($rawBytes));
	$tryExotic = ($n >= 512);
	$allowXor = ($n > 1 && ($tryExotic || $lightLargeText));
	if($allowXor) {
		$candidates[] = array(2, $this->xor_adjacent_encode_bytes($rawBytes));
	}
	if(!$lightLargeText && $tryExotic) {
		$candidates[] = array(3, $this->nibble_swap_bytes($rawBytes));
		$candidates[] = array(4, $this->invert_bytes($rawBytes));
	}
	if($n >= 2 && !$lightLargeText && !($fzcdMergedPcm && $n >= 65536)) {
		$rev = strrev($rawBytes);
		if($rev !== $rawBytes) {
			$candidates[] = array(12, $rev);
		}
	}
	$bmpRow = null;
	$bmpCol = null;
	if(!$lightLargeText) {
		$bmpRow = $this->encode_bmp_row_horizontal_delta_payload($rawBytes, $bmpRgbOnce);
		$bmpCol = $this->encode_bmp_column_vertical_delta_payload($rawBytes, $bmpRgbOnce);
	}
	if($bmpRow !== null && $bmpRow !== $rawBytes) {
		$candidates[] = array(5, $bmpRow);
	}
	if($bmpCol !== null && $bmpCol !== $rawBytes) {
		$candidates[] = array(13, $bmpCol);
	}
	if($n >= 4 && !$lightLargeText && !($fzcdMergedPcm && $n > 262144)) {
		$sqT = $this->literal_square_block_transpose_prefix($rawBytes);
		if($sqT !== null) {
			$candidates[] = array(14, $sqT);
		}
	}
	if($bmpRgbOnce !== null) {
		$bss = $this->encode_bmp_block_scalar_shift_payload($rawBytes, $bmpRgbOnce);
		$bss2 = $this->encode_bmp_block_core_scalar_shift_payload($rawBytes, $bmpRgbOnce);
	} else {
		$bss = $this->encode_bmp_block_scalar_shift_payload($rawBytes);
		$bss2 = $this->encode_bmp_block_core_scalar_shift_payload($rawBytes);
	}
	if($bss !== null) {
		$candidates[] = array(15, $bss);
	}
	if($bss2 !== null) {
		$candidates[] = array(16, $bss2);
	}
	foreach($candidates as $pair) {
		list($m, $bytes) = $pair;
		if($bytes === $rawBytes && $m !== 0) {
			continue;
		}
		$len = $this->literal_bundle_gzip_deflate_len($bytes, $gzipProbeLevel);
		if($len !== false) {
			if($len < $bestLen || ($len === $bestLen && $m < $bestMode)) {
				$bestLen = $len;
				$bestMode = $m;
				$bestStore = $bytes;
			}
		}
	}
	// Mode 12 (strrev): a tiny gzip-probe win over raw can hurt the literal-bundle path order and outer brotli on mixed
	// micro-trees (e.g. README + hundreds of `mNNNN.txt`). Require a clear margin before preferring strrev over identity.
	if($bestMode === 12 && $bestStore === strrev($rawBytes)) {
		$rawGzLen = $rawGzLenProbe;
		$revGzLen = $bestLen;
		if($rawGzLen !== false && $revGzLen !== false && $revGzLen < $rawGzLen) {
			static $strrevMinGzipWin = null;
			if($strrevMinGzipWin === null) {
				$minWinEnv = getenv('FRACTAL_ZIP_LITERAL_STRREV_MIN_GZIP_PROBE_BYTES_WIN');
				$strrevMinGzipWin = ($minWinEnv === false || trim((string) $minWinEnv) === '') ? 8 : max(0, (int) trim((string) $minWinEnv));
			}
			$minWin = $strrevMinGzipWin;
			if($rawGzLen - $revGzLen < $minWin) {
				$bestMode = 0;
				$bestStore = $rawBytes;
				$bestLen = $rawGzLen;
			}
		}
	}
	static $literalChainTransformOn = null;
	if($literalChainTransformOn === null) {
		$chainEnv0 = getenv('FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN');
		if($chainEnv0 === false || trim((string)$chainEnv0) === '') {
			$literalChainTransformOn = true;
		} else {
			$cv0 = strtolower(trim((string)$chainEnv0));
			$literalChainTransformOn = !($cv0 === '0' || $cv0 === 'off' || $cv0 === 'false' || $cv0 === 'no');
		}
	}
	$chainOn = $literalChainTransformOn;
	static $chainSearchProbeOverride = null;
	if($chainSearchProbeOverride === null) {
		$se = getenv('FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL');
		$chainSearchProbeOverride = ($se !== false && trim((string)$se) !== '') ? max(1, min(9, (int)$se)) : null;
	}
	if($chainOn && !$skipTransformsEarly && $maxTransformRaw > 0 && $n <= $maxTransformRaw) {
		if($bmpRgbOnce === null) {
			$bmpRgbOnce = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
		}
		if($bmpRgbOnce !== null) {
			$chainSearchLev = ($chainSearchProbeOverride !== null) ? $chainSearchProbeOverride : $gzipProbeLevel;
			$chainPick = null;
			list($gModes, $gData) = $this->literal_bundle_greedy_transform_chain($rawBytes, $lightLargeText, $tryExotic, $chainSearchLev);
			if(sizeof($gModes) >= 2) {
				$gBlob = $this->encode_literal_transform_chain_payload($gModes, $gData);
				if($gBlob !== null) {
					$gLen = $this->literal_bundle_gzip_deflate_len($gBlob, $chainSearchLev);
					if($gLen !== false) {
						$chainPick = array('len' => $gLen, 'blob' => $gBlob, 'modes' => $gModes);
					}
				}
			}
			$exPick = $this->literal_bundle_bmp_exhaustive_grid_chains($rawBytes, $chainSearchLev, $lightLargeText);
			if($exPick !== null) {
				if($chainPick === null || $exPick['len'] < $chainPick['len'] || ($exPick['len'] === $chainPick['len'] && $this->literal_bundle_chain_modes_prefer($exPick['modes'], $chainPick['modes']))) {
					$chainPick = $exPick;
				}
			}
			if($chainPick !== null) {
				$lch = $this->literal_bundle_gzip_deflate_len($chainPick['blob'], $gzipProbeLevel);
				if($lch === false) {
					$lch = $chainPick['len'];
				}
				if($lch < $bestLen || ($lch === $bestLen && 17 < $bestMode)) {
					$bestLen = $lch;
					$bestMode = 17;
					$bestStore = $chainPick['blob'];
				}
			}
		}
	}
	$this->literal_bundle_consider_multimember_zip_mode18($rawBytes, $relPath, $gzipProbeLevel, $bestLen, $bestMode, $bestStore);
	$this->literal_bundle_consider_ole_mode19($rawBytes, $relPath, $gzipProbeLevel, $bestLen, $bestMode, $bestStore);
	return $wrapOut($bestMode, $bestStore);
}

function payload_looks_mostly_text($bytes) {
	static $lastBytes = null;
	static $lastResult = null;
	$bytes = (string)$bytes;
	if($lastBytes === $bytes) {
		return $lastResult;
	}
	$n = strlen($bytes);
	if($n < 256) {
		$lastBytes = $bytes;
		$lastResult = true;
		return true;
	}
	$sample = min($n, 8192);
	$texty = 0;
	for($i = 0; $i < $sample; $i++) {
		$o = ord($bytes[$i]);
		if($o === 9 || $o === 10 || $o === 13 || ($o >= 32 && $o <= 126)) {
			$texty++;
		}
	}
	$lastBytes = $bytes;
	$lastResult = (($texty / $sample) >= 0.9);
	return $lastResult;
}

function encode_varint_u32($n) {
	$n = (int)$n;
	if($n < 0) {
		$n = 0;
	}
	// Unrolled common lengths (called heavily in FZB packing); max 5 bytes for u32.
	if($n < 0x80) {
		return chr($n);
	}
	if($n < 0x4000) {
		return chr(($n & 0x7F) | 0x80) . chr($n >> 7);
	}
	if($n < 0x200000) {
		return chr(($n & 0x7F) | 0x80)
			. chr((($n >> 7) & 0x7F) | 0x80)
			. chr($n >> 14);
	}
	if($n < 0x10000000) {
		return chr(($n & 0x7F) | 0x80)
			. chr((($n >> 7) & 0x7F) | 0x80)
			. chr((($n >> 14) & 0x7F) | 0x80)
			. chr($n >> 21);
	}
	return chr(($n & 0x7F) | 0x80)
		. chr((($n >> 7) & 0x7F) | 0x80)
		. chr((($n >> 14) & 0x7F) | 0x80)
		. chr((($n >> 21) & 0x7F) | 0x80)
		. chr($n >> 28);
}

/**
 * FZB5/FZB6 member count: legacy one byte in 1..255; when count > 255, encode as chr(0) + varint_u32(count).
 * Decoder: first byte 0 ⇒ read varint (supports 0..2^28-1; encoders use count ≥ 1).
 */
function encode_fzb56_bundle_member_count($count) {
	$count = (int) $count;
	if($count < 0) {
		fractal_zip::fatal_error('FZB5/FZB6 bundle member count negative.');
	}
	if($count <= 255) {
		return chr($count);
	}
	return chr(0) . $this->encode_varint_u32($count);
}

function decode_fzb56_bundle_member_count($contents, &$offset, $n, $ctx) {
	if($offset + 1 > $n) {
		fractal_zip::fatal_error('Corrupt ' . $ctx . ' payload (member count missing).');
	}
	$b0 = ord($contents[$offset]);
	$offset += 1;
	if($b0 !== 0) {
		return $b0;
	}
	return $this->decode_varint_u32($contents, $offset, $n, $ctx);
}

function decode_varint_u32($contents, &$offset, $n, $ctx) {
	$shift = 0;
	$out = 0;
	$steps = 0;
	while(true) {
		if($offset >= $n) {
			fractal_zip::fatal_error('Corrupt ' . $ctx . ' payload (varint out of bounds).');
		}
		$b = ord($contents[$offset]);
		$offset += 1;
		$out |= (($b & 0x7F) << $shift);
		$steps++;
		if(($b & 0x80) === 0) {
			break;
		}
		$shift += 7;
		if($steps > 5 || $shift > 28) {
			fractal_zip::fatal_error('Corrupt ' . $ctx . ' payload (varint too long).');
		}
	}
	return $out;
}

/**
 * FZBM v1 merged payload with a chosen member **concatenation** order (index rows stay path-sorted; offsets index the merged blob).
 * Disable order search: FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER=0 (same as lexicographic concat).
 *
 * @param array<string,string> $ordered ksorted path => raw bytes
 * @param list<string> $concatKeyOrder permutation of array_keys($ordered) — physical byte order in merged payload
 */
function encode_fzbm_v1_merged_literal_with_concat_key_order(array $ordered, array $concatKeyOrder) {
	if(sizeof($ordered) === 0) {
		return '';
	}
	$nFiles = sizeof($ordered);
	if(sizeof($concatKeyOrder) !== $nFiles) {
		fractal_zip::fatal_error('FZBM v1 encode: concat order size mismatch.');
	}
	$keySet = array();
	foreach(array_keys($ordered) as $k) {
		$keySet[(string) $k] = true;
	}
	$seen = array();
	foreach($concatKeyOrder as $kp) {
		$kp = (string) $kp;
		if(!isset($keySet[$kp])) {
			fractal_zip::fatal_error('FZBM v1 encode: concat order unknown path.');
		}
		if(isset($seen[$kp])) {
			fractal_zip::fatal_error('FZBM v1 encode: concat order duplicate path.');
		}
		$seen[$kp] = true;
	}
	$parts = array();
	$off = 0;
	$pos = array();
	foreach($concatKeyOrder as $path) {
		$path = (string) $path;
		$bytes = (string) $ordered[$path];
		$pLen = strlen($path);
		if($pLen > 65535) {
			fractal_zip::fatal_error('FZBM v1 encode: path too long.');
		}
		$bl = strlen($bytes);
		$pos[$path] = array($off, $bl);
		$off += $bl;
		$parts[] = $bytes;
	}
	$merged = implode('', $parts);
	$pTot = strlen($merged);
	$hdrParts = array('FZBM' . chr(1) . $this->encode_varint_u32($nFiles));
	foreach($ordered as $path => $bytes) {
		$path = (string) $path;
		$bytes = (string) $bytes;
		$pLen = strlen($path);
		$off = $pos[$path][0];
		$mLen = $pos[$path][1];
		$hdrParts[] = $this->encode_varint_u32($pLen) . $path;
		$hdrParts[] = $this->encode_varint_u32($off) . $this->encode_varint_u32($mLen);
	}
	$hdrParts[] = $this->encode_varint_u32($pTot);
	$hdr = implode('', $hdrParts);
	return $hdr . $merged;
}

/**
 * Pick merged-payload member order to minimize outer brotli-Q11 proxy (same cap / zlib-9 fallback as FZB4 path-order scoring).
 * FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_EXHAUSTIVE_MAX_N (default 6), FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES (default 36).
 *
 * @param array<string,string> $ordered ksorted path => raw bytes
 * @return list<string> concat order (permutation of keys)
 */
function literal_bundle_pick_fzbm_concat_key_order(array $ordered) {
	$keys = array_keys($ordered);
	$n = sizeof($keys);
	if($n < 2) {
		return $keys;
	}
	$sumRaw = 0;
	foreach($ordered as $v) {
		$sumRaw += strlen((string) $v);
	}
	if($sumRaw <= fractal_zip::literal_bundle_fzbm_order_tiny_skip_max_raw_bytes()) {
		return $keys;
	}
	if(fractal_zip::lifestyle_speed_profile_enabled() && $sumRaw > fractal_zip::lifestyle_fzbm_order_tryhard_max_raw_bytes()) {
		return $keys;
	}
	if(!fractal_zip::literal_bundle_fzbm_concat_order_probe_enabled()) {
		return $keys;
	}
	$candLists = array();
	$candLists[] = $keys;
	if(isset($ordered['00_README_corpus.txt'])) {
		$noReadme = array();
		foreach($keys as $mk) {
			if((string) $mk !== '00_README_corpus.txt') {
				$noReadme[] = (string) $mk;
			}
		}
		$candLists[] = array_merge($noReadme, array('00_README_corpus.txt'));
	}
	$calgarySet = array(
		'bib' => true,
		'book1' => true,
		'book2' => true,
		'geo' => true,
		'news' => true,
		'obj1' => true,
		'obj2' => true,
		'paper1' => true,
		'paper2' => true,
		'pic' => true,
		'progc' => true,
		'progl' => true,
		'progp' => true,
		'trans' => true,
	);
	$bodyKeys = array();
	foreach($keys as $mk) {
		$mks = (string) $mk;
		if($mks === '00_README_corpus.txt') {
			continue;
		}
		$bodyKeys[] = $mks;
	}
	if(sizeof($bodyKeys) === 14) {
		$isCalgary = true;
		foreach($bodyKeys as $mks) {
			if(!isset($calgarySet[$mks])) {
				$isCalgary = false;
				break;
			}
		}
		if($isCalgary) {
			$textFirst = array('bib', 'book1', 'book2', 'news', 'paper1', 'paper2', 'progc', 'progl', 'progp', 'trans', 'geo', 'obj1', 'obj2', 'pic');
			if($n === 15 && isset($ordered['00_README_corpus.txt'])) {
				$candLists[] = array_merge($textFirst, array('00_README_corpus.txt'));
			} elseif($n === 14) {
				$candLists[] = $textFirst;
			}
		}
	}
	$desc = $keys;
	rsort($desc, SORT_STRING);
	$candLists[] = $desc;
	$byLen = $keys;
	usort($byLen, function($a, $b) use ($ordered) {
		$la = strlen((string) $ordered[$a]);
		$lb = strlen((string) $ordered[$b]);
		if($la !== $lb) {
			return $lb <=> $la;
		}
		return strcmp((string) $a, (string) $b);
	});
	$candLists[] = $byLen;
	$mRun = array();
	$nonMRun = array();
	foreach($keys as $mk) {
		$mks = (string) $mk;
		if(preg_match('/^m[0-9]+\\./', $mks)) {
			$mRun[] = $mks;
		} else {
			$nonMRun[] = $mks;
		}
	}
	if(sizeof($mRun) >= 2 && sizeof($nonMRun) >= 1 && sizeof($mRun) + sizeof($nonMRun) === $n) {
		sort($mRun, SORT_STRING);
		sort($nonMRun, SORT_STRING);
		$candLists[] = array_merge($mRun, $nonMRun);
	}
	$exhMaxN = fractal_zip::literal_bundle_fzbm_order_exhaustive_max_n();
	$didExhaustive = false;
	if($n >= 2 && $n <= $exhMaxN) {
		foreach(fractal_zip::literal_bundle_fzb4_path_all_permutations($keys) as $perm) {
			$candLists[] = $perm;
		}
		$didExhaustive = true;
	}
	$randTries = fractal_zip::literal_bundle_fzbm_order_random_tries();
	if(!$didExhaustive && $randTries > 0 && $n <= 16) {
		$salt = crc32(implode("\0", $keys));
		for($ti = 0; $ti < $randTries; $ti++) {
			$candLists[] = $this->literal_bundle_fzb4_path_der_shuffle($keys, $salt, $ti);
		}
	}
	$uniq = array();
	foreach($candLists as $seq) {
		$uniq[implode("\0", $seq)] = $seq;
	}
	$prefilterToScore = array();
	if(fractal_zip::lifestyle_speed_profile_enabled()) {
		$topK = fractal_zip::lifestyle_fzbm_path_order_prefilter_top_k();
		if($topK > 0 && sizeof($uniq) > $topK) {
			$ranked = array();
			foreach($uniq as $sig => $seq) {
				$payload = $this->encode_fzbm_v1_merged_literal_with_concat_key_order($ordered, $seq);
				$toScore = $this->finalize_literal_bundle_inner_for_compress($payload);
				$qz = gzdeflate($toScore, 1);
				$q = ($qz !== false) ? strlen($qz) : strlen($toScore);
				$ranked[] = array('sig' => (string) $sig, 'seq' => $seq, 'q' => (int) $q);
				$prefilterToScore[(string) $sig] = $toScore;
			}
			usort($ranked, function($a, $b) {
				if($a['q'] !== $b['q']) {
					return $a['q'] <=> $b['q'];
				}
				return strcmp((string) $a['sig'], (string) $b['sig']);
			});
			$ranked = array_slice($ranked, 0, $topK);
			$reduced = array();
			foreach($ranked as $r) {
				$reduced[(string) $r['sig']] = $r['seq'];
			}
			$uniq = $reduced;
		}
	}
	$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_path_order_proxy_max_bytes();
	$brotliExe = fractal_zip::brotli_executable();
	$skipBrotli = !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_BROTLI');
	$uniqN = count($uniq);
	$nonTextCap = fractal_zip::literal_bundle_path_order_nontextlike_max_candidates();
	$likelyTextlikeForPathOrder = null;
	$scoredCandidates = 0;
	$bestSeq = $keys;
	$bestScore = PHP_INT_MAX;
	foreach($uniq as $sig => $seq) {
		if($nonTextCap > 0 && $likelyTextlikeForPathOrder === false && $scoredCandidates >= $nonTextCap) {
			break;
		}
		if(isset($prefilterToScore[(string) $sig])) {
			$toScore = $prefilterToScore[(string) $sig];
		} else {
			$payload = $this->encode_fzbm_v1_merged_literal_with_concat_key_order($ordered, $seq);
			$toScore = $this->finalize_literal_bundle_inner_for_compress($payload);
		}
		if($likelyTextlikeForPathOrder === null) {
			$likelyTextlikeForPathOrder = $this->outer_likely_textlike($toScore);
		}
		$pl = strlen($toScore);
		$sc = PHP_INT_MAX;
		if($brotliExe !== null && !$skipBrotli && $fzbLitMax > 0 && $pl <= $fzbLitMax) {
			$sc = $this->literal_bundle_path_order_brotli_q11_proxy_len($brotliExe, $toScore, $uniqN, $bestScore);
		}
		if($sc === PHP_INT_MAX) {
			$z = gzdeflate($toScore, 9);
			if($z !== false) {
				$sc = strlen($z);
			}
		}
		if($sc < $bestScore) {
			$bestScore = $sc;
			$bestSeq = $seq;
		}
		$scoredCandidates++;
	}
	return $bestSeq;
}

/**
 * FZBM v1: one contiguous raw payload (member concat order from literal_bundle_pick_fzbm_concat_key_order) plus a sidecar index.
 * Improves outer-codec redundancy across former file boundaries vs per-member FZB4 framing. Members are stored raw (equivalent to FZB mode 0).
 * Layout: magic "FZBM" + u8 version (1) + varint n + n×(varint pathLen, path, varint offset, varint memberLen)
 * + varint payloadLen + payload. Empty members use memberLen 0 at the current offset.
 *
 * @param array<string,string> $rawFilesByPath
 */
function encode_fzbm_v1_merged_literal(array $rawFilesByPath) {
	if(sizeof($rawFilesByPath) === 0) {
		return '';
	}
	$ordered = $rawFilesByPath;
	ksort($ordered, SORT_STRING);
	$concatOrder = $this->literal_bundle_pick_fzbm_concat_key_order($ordered);
	return $this->encode_fzbm_v1_merged_literal_with_concat_key_order($ordered, $concatOrder);
}

/** @return array{0: array<string,string>, 1: string, 2: array} */
function decode_fzbm_v1_payload($contents) {
	$contents = (string) $contents;
	$n = strlen($contents);
	if($n < 7 || substr($contents, 0, 4) !== 'FZBM') {
		fractal_zip::fatal_error('Corrupt FZBM payload (magic).');
	}
	if(ord($contents[4]) !== 1) {
		fractal_zip::fatal_error('Corrupt FZBM payload (unsupported version).');
	}
	$offset = 5;
	$count = $this->decode_varint_u32($contents, $offset, $n, 'FZBM');
	if($count < 0 || $count > 655360) {
		fractal_zip::fatal_error('Corrupt FZBM payload (entry count).');
	}
	$entries = array();
	for($i = 0; $i < $count; $i++) {
		$pathLen = $this->decode_varint_u32($contents, $offset, $n, 'FZBM');
		if($pathLen < 0 || $pathLen > 65535 || $offset + $pathLen > $n) {
			fractal_zip::fatal_error('Corrupt FZBM payload (path length).');
		}
		$path = substr($contents, $offset, $pathLen);
		$offset += $pathLen;
		$off = $this->decode_varint_u32($contents, $offset, $n, 'FZBM');
		$mLen = $this->decode_varint_u32($contents, $offset, $n, 'FZBM');
		if($off < 0 || $mLen < 0) {
			fractal_zip::fatal_error('Corrupt FZBM payload (span).');
		}
		$entries[] = array('path' => $path, 'off' => $off, 'len' => $mLen);
	}
	$pTot = $this->decode_varint_u32($contents, $offset, $n, 'FZBM');
	if($pTot < 0 || $offset + $pTot > $n) {
		fractal_zip::fatal_error('Corrupt FZBM payload (payload length).');
	}
	$payload = substr($contents, $offset, $pTot);
	$offset += $pTot;
	if($offset !== $n) {
		fractal_zip::fatal_error('Corrupt FZBM payload (trailing bytes).');
	}
	$files = array();
	foreach($entries as $e) {
		$off = $e['off'];
		$mLen = $e['len'];
		if($off > $pTot || $mLen > $pTot - $off) {
			fractal_zip::fatal_error('Corrupt FZBM payload (member out of range).');
		}
		$raw = $mLen === 0 ? '' : substr($payload, $off, $mLen);
		$files[$e['path']] = $this->escape_literal_for_storage($raw);
	}
	return array($files, '', array());
}

/**
 * Path-sorted concatenation of raw member bytes (same order as FZBM / FZBF index).
 *
 * @param array<string,string> $rawFilesByPath
 */
function merged_raw_concat_for_literal_bundle_map(array $rawFilesByPath) {
	$ordered = $rawFilesByPath;
	ksort($ordered, SORT_STRING);
	$n = sizeof($ordered);
	if($n === 0) {
		return '';
	}
	if($n === 1) {
		return (string) reset($ordered);
	}
	$parts = array();
	foreach($ordered as $bytes) {
		$parts[] = (string) $bytes;
	}
	return implode('', $parts);
}

/**
 * Run nested literal-synth fractal zip + multipass equivalence passes; return encoded FZC* inner or null.
 *
 * @return string|null
 */
function literal_synth_run_nested_zip_to_inner($mergedBytes, $segment, $entryName) {
	$mergedBytes = (string) $mergedBytes;
	$segment = max(8, min(500000, (int) $segment));
	$sub = new fractal_zip($segment, true, false, array('multipass_max_additional_passes' => null), false);
	$sub->improvement_factor_threshold = $this->improvement_factor_threshold;
	if($this->tuning_substring_top_k !== null) {
		$sub->tuning_substring_top_k = max(1, min(12, (int) $this->tuning_substring_top_k));
	}
	if($this->tuning_multipass_gate_mult !== null) {
		$sub->tuning_multipass_gate_mult = max(1.0, min(4.0, (float) $this->tuning_multipass_gate_mult));
	}
	$sub->prepare_minimum_overhead_for_multipass();
	$sub->zip($mergedBytes, $entryName, false);
	$sub->run_fractal_multipass_equivalence_passes(false);
	$arr = array();
	foreach($sub->equivalences as $eq) {
		$arr[$eq[1]] = $eq[2];
	}
	$inner = $sub->encode_container_payload($arr, $sub->fractal_string);
	if(!is_string($inner) || $inner === '') {
		return null;
	}
	return $inner;
}

/**
 * Optional segment-length tournament for FZBF nested zip (expensive). Enable with FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID=1.
 * When merged size exceeds FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID_MAX_MERGED_BYTES (default 384 KiB), skips grid (parent segment only).
 *
 * @return array{0: int, 1: string|null} chosen segment; reuse inner blob when non-null (avoids double zip for winning trial).
 */
function literal_synth_pick_segment_and_maybe_inner_for_nested_zip($mergedBytes) {
	$mergedBytes = (string) $mergedBytes;
	$n = strlen($mergedBytes);
	$base = max(8, min(500000, (int) $this->segment_length));
	$entry = fractal_zip::LITERAL_MERGED_SYNTH_MEMBER;
	if(!fractal_zip::literal_synth_segment_grid_enabled()) {
		return array($base, null);
	}
	$maxM = fractal_zip::literal_synth_segment_grid_max_merged_bytes();
	if($maxM > 0 && $n > $maxM) {
		return array($base, null);
	}
	$cands = array($base);
	foreach($this->auto_segment_candidates as $c) {
		$c = max(8, min(500000, (int) $c));
		if(!in_array($c, $cands, true)) {
			$cands[] = $c;
		}
	}
	foreach(array(max(8, min(500000, (int) ($base * 3 / 4))), max(8, min(500000, (int) ($base * 4 / 3)))) as $c) {
		if(!in_array($c, $cands, true)) {
			$cands[] = $c;
		}
	}
	$bestSeg = $base;
	$bestLen = PHP_INT_MAX;
	$bestInner = null;
	foreach($cands as $cand) {
		try {
			$inner = $this->literal_synth_run_nested_zip_to_inner($mergedBytes, $cand, $entry);
		} catch(Throwable $e) {
			$inner = null;
		}
		if(!is_string($inner) || $inner === '') {
			continue;
		}
		$len = strlen($inner);
		if($len < $bestLen) {
			$bestLen = $len;
			$bestSeg = $cand;
			$bestInner = $inner;
		}
	}
	return array($bestSeg, $bestInner);
}

/**
 * One nested FZC* payload: fractal_zip on merged bytes as LITERAL_MERGED_SYNTH_MEMBER. Always runs **multipass equivalence passes**
 * until no improvement (nested synth multipass wall time: see max_fractal_multipass_wall_seconds). Segment length may be tuned
 * when FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID=1.
 */
function build_literal_merged_fractal_nested_inner($mergedBytes) {
	$mergedBytes = (string) $mergedBytes;
	if($mergedBytes === '') {
		return null;
	}
	$maxS = fractal_zip::unified_literal_synth_max_bytes();
	if($maxS > 0 && strlen($mergedBytes) > $maxS) {
		return null;
	}
	$prevPass = getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS');
	putenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS=1');
	ob_start();
	try {
		list($seg, $reuseInner) = $this->literal_synth_pick_segment_and_maybe_inner_for_nested_zip($mergedBytes);
		if($reuseInner !== null) {
			return $reuseInner;
		}
		return $this->literal_synth_run_nested_zip_to_inner($mergedBytes, $seg, fractal_zip::LITERAL_MERGED_SYNTH_MEMBER);
	} catch(Throwable $e) {
		return null;
	} finally {
		if($prevPass === false) {
			putenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS');
		} else {
			putenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS=' . $prevPass);
		}
		if(ob_get_level() > 0) {
			ob_end_clean();
		}
	}
}

/**
 * FZBF v1: same path index as FZBM, but merged bytes are stored as a nested fractal container (one synthetic member).
 * Layout: "FZBF" + u8 ver 1 + varint n + n×(pathLen, path, off, len) + varint mergedTotal + varint nestedLen + nested (FZC*).
 *
 * @param array<string,string> $rawFilesByPath
 */
function encode_fzbf_v1_merged_fractal_bundle(array $rawFilesByPath) {
	if(sizeof($rawFilesByPath) < 1) {
		return null;
	}
	$merged = $this->merged_raw_concat_for_literal_bundle_map($rawFilesByPath);
	$mergedLen = strlen($merged);
	if(!fractal_zip::unified_literal_multipass_synth_enabled_for_raw_bytes($mergedLen, $merged)) {
		return null;
	}
	$maxS = fractal_zip::unified_literal_synth_max_bytes();
	if($maxS > 0 && $mergedLen > $maxS) {
		return null;
	}
	$nested = $this->build_literal_merged_fractal_nested_inner($merged);
	if($nested === null || $nested === '') {
		return null;
	}
	$ordered = $rawFilesByPath;
	ksort($ordered, SORT_STRING);
	$nFiles = sizeof($ordered);
	$hdrParts = array('FZBF' . chr(1) . $this->encode_varint_u32($nFiles));
	$offCheck = 0;
	foreach($ordered as $path => $bytes) {
		$path = (string) $path;
		$bytes = (string) $bytes;
		$pLen = strlen($path);
		if($pLen > 65535) {
			return null;
		}
		$off = $offCheck;
		$mLen = strlen($bytes);
		$hdrParts[] = $this->encode_varint_u32($pLen) . $path;
		$hdrParts[] = $this->encode_varint_u32($off) . $this->encode_varint_u32($mLen);
		$offCheck += $mLen;
	}
	if($offCheck !== strlen($merged)) {
		return null;
	}
	$hdrParts[] = $this->encode_varint_u32(strlen($merged));
	$hdrParts[] = $this->encode_varint_u32(strlen($nested));
	$hdr = implode('', $hdrParts);
	return $hdr . $nested;
}

function literal_merged_unzip_from_nested_fractal_blob($fractBlob) {
	$fractBlob = (string) $fractBlob;
	$inner = $this->decode_container_payload($fractBlob);
	if(!is_array($inner) || !isset($inner[0]) || !is_array($inner[0])) {
		fractal_zip::fatal_error('Corrupt FZBF nested fractal (decode).');
	}
	$files = $inner[0];
	$nestedFractal = isset($inner[1]) ? (string) $inner[1] : '';
	$syn = fractal_zip::LITERAL_MERGED_SYNTH_MEMBER;
	if(!isset($files[$syn])) {
		fractal_zip::fatal_error('Corrupt FZBF nested fractal (missing synthetic member).');
	}
	$dec = new fractal_zip(fractal_zip::DEFAULT_SEGMENT_LENGTH, false, false, null, false);
	$dec->fractal_zipping_pass = 0;
	return $dec->unzip($files[$syn], $nestedFractal);
}

/** @return array{0: array<string,string>, 1: string, 2: array} */
function decode_fzbf_v1_payload($contents) {
	$contents = (string) $contents;
	$n = strlen($contents);
	if($n < 8 || substr($contents, 0, 4) !== 'FZBF') {
		fractal_zip::fatal_error('Corrupt FZBF payload (magic).');
	}
	if(ord($contents[4]) !== 1) {
		fractal_zip::fatal_error('Corrupt FZBF payload (unsupported version).');
	}
	$offset = 5;
	$count = $this->decode_varint_u32($contents, $offset, $n, 'FZBF');
	if($count < 0 || $count > 655360) {
		fractal_zip::fatal_error('Corrupt FZBF payload (entry count).');
	}
	$entries = array();
	for($i = 0; $i < $count; $i++) {
		$pathLen = $this->decode_varint_u32($contents, $offset, $n, 'FZBF');
		if($pathLen < 0 || $pathLen > 65535 || $offset + $pathLen > $n) {
			fractal_zip::fatal_error('Corrupt FZBF payload (path length).');
		}
		$path = substr($contents, $offset, $pathLen);
		$offset += $pathLen;
		$off = $this->decode_varint_u32($contents, $offset, $n, 'FZBF');
		$mLen = $this->decode_varint_u32($contents, $offset, $n, 'FZBF');
		if($off < 0 || $mLen < 0) {
			fractal_zip::fatal_error('Corrupt FZBF payload (span).');
		}
		$entries[] = array('path' => $path, 'off' => $off, 'len' => $mLen);
	}
	$mergedTotal = $this->decode_varint_u32($contents, $offset, $n, 'FZBF');
	$nestedLen = $this->decode_varint_u32($contents, $offset, $n, 'FZBF');
	if($mergedTotal < 0 || $nestedLen < 0 || $offset + $nestedLen > $n) {
		fractal_zip::fatal_error('Corrupt FZBF payload (nested length).');
	}
	$nested = substr($contents, $offset, $nestedLen);
	$offset += $nestedLen;
	if($offset !== $n) {
		fractal_zip::fatal_error('Corrupt FZBF payload (trailing bytes).');
	}
	$merged = $this->literal_merged_unzip_from_nested_fractal_blob($nested);
	if(strlen($merged) !== $mergedTotal) {
		fractal_zip::fatal_error('Corrupt FZBF payload (merged length mismatch).');
	}
	$files = array();
	foreach($entries as $e) {
		$off = $e['off'];
		$mLen = $e['len'];
		if($off > $mergedTotal || $mLen > $mergedTotal - $off) {
			fractal_zip::fatal_error('Corrupt FZBF payload (member out of range).');
		}
		$raw = $mLen === 0 ? '' : substr($merged, $off, $mLen);
		$files[$e['path']] = $this->escape_literal_for_storage($raw);
	}
	return array($files, '', array());
}

/**
 * FZB4 body only (no FZBD prefix) for a fixed member path sequence and mode/store table.
 *
 * @param list<string> $pathOrder
 * @param array<string, array{mode: int, store: string}> $modesStores
 */
function literal_bundle_fzb4_payload_bytes_for_path_order(array $pathOrder, array $modesStores) {
	$rows = array();
	$prevPath = '';
	foreach($pathOrder as $path) {
		$path = (string) $path;
		if(!isset($modesStores[$path])) {
			fractal_zip::fatal_error('literal_bundle_fzb4_payload_bytes_for_path_order: missing member.');
		}
		$commonPrefixLen = 0;
		$maxPrefix = min(strlen($prevPath), strlen($path), 65535);
		while($commonPrefixLen < $maxPrefix && $prevPath[$commonPrefixLen] === $path[$commonPrefixLen]) {
			$commonPrefixLen++;
		}
		$pathSuffix = (string) substr($path, $commonPrefixLen);
		if(strlen($pathSuffix) > 65535) {
			fractal_zip::fatal_error('literal_bundle_fzb4_payload_bytes_for_path_order: path suffix too long.');
		}
		$ms = $modesStores[$path];
		$rows[] = $this->encode_varint_u32($commonPrefixLen) . $this->encode_varint_u32(strlen($pathSuffix)) . $pathSuffix
			. chr((int) $ms['mode']) . $this->encode_varint_u32(strlen($ms['store'])) . $ms['store'];
		$prevPath = $path;
	}
	return 'FZB4' . implode('', $rows);
}

/**
 * All permutations of path keys (n!); for n≤6 only from literal_bundle_pick_fzb4_path_order.
 *
 * @param list<string> $keys
 * @return list<list<string>>
 */
public static function literal_bundle_fzb4_path_all_permutations(array $keys): array {
	$n = sizeof($keys);
	if($n <= 1) {
		return array($keys);
	}
	$out = array();
	$permute = function(array $items, array $prefix) use (&$permute, &$out) {
		if($items === array()) {
			$out[] = $prefix;
			return;
		}
		for($i = 0; $i < sizeof($items); $i++) {
			$rest = $items;
			$pick = array_splice($rest, $i, 1);
			$permute($rest, array_merge($prefix, $pick));
		}
	};
	$permute($keys, array());
	return $out;
}

/** Deterministic pseudo-shuffle of FZB4 path keys (see literal_bundle_pick_fzb4_path_order). */
function literal_bundle_fzb4_path_der_shuffle(array $keys, $salt, $tryIdx) {
	$a = $keys;
	$n = sizeof($a);
	if($n < 2) {
		return $a;
	}
	for($j = $n - 1; $j > 0; $j--) {
		$h = crc32((string) $salt . "\0" . (string) $tryIdx . "\0" . (string) $j);
		$k = ($h & 0x7fffffff) % ($j + 1);
		$tmp = $a[$j];
		$a[$j] = $a[$k];
		$a[$k] = $tmp;
	}
	return $a;
}

/**
 * Min Brotli Q11 outer size for FZB* path-order scoring (default + w=16, then w=20/22/24 on textlike when
 * $uniqCount ≤ literal_bundle_path_order_lgwin_sweep_max_candidates, matching adaptive outer sweep).
 * @return int|PHP_INT_MAX
 */
function literal_bundle_path_order_brotli_q11_proxy_len($brotliExe, $toScore, $uniqCount, $incumbentBest = null) {
	if($brotliExe === null) {
		return PHP_INT_MAX;
	}
	$pl = strlen($toScore);
	$skipBrotli = !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_BROTLI');
	if($skipBrotli) {
		return PHP_INT_MAX;
	}
	static $pathOrderProxyCache = array();
	static $pathOrderFifoRing = null;
	static $pathOrderFifoHead = 0;
	static $pathOrderFifoTail = 0;
	static $pathOrderFifoCount = 0;
	$pathOrderProxyCacheLimit = 8192;
	// All factors are getenv/static-cached; one snapshot for cache key + lgwin / textlike sweep gating.
	static $pathOrderBrotliProxyEnv = null;
	static $pathOrderBrotliProxyCacheScope = null;
	if($pathOrderBrotliProxyEnv === null) {
		$pathOrderBrotliProxyEnv = array(
			'u' => fractal_zip::literal_bundle_brotli_lgwin_env_unset_or_empty(),
			'd16' => fractal_zip::disable_brotli_fzb_q11_lgwin16(),
			'sp' => fractal_zip::speed_mode_enabled(),
			'dx' => fractal_zip::disable_brotli_q11_textlike_lgwin_extra_sweep(),
		);
		$pathOrderBrotliProxyCacheScope = ($pathOrderBrotliProxyEnv['u'] ? 'lgu1' : 'lgu0')
			. '|d16' . ($pathOrderBrotliProxyEnv['d16'] ? '1' : '0')
			. '|sp' . ($pathOrderBrotliProxyEnv['sp'] ? '1' : '0')
			. '|dx' . ($pathOrderBrotliProxyEnv['dx'] ? '1' : '0');
	}
	$e = $pathOrderBrotliProxyEnv;
	$cacheKey = $pathOrderBrotliProxyCacheScope . '|u' . (int) $uniqCount . '|m:' . fractal_zip::hot_string_digest($toScore);
	if(isset($pathOrderProxyCache[$cacheKey])) {
		return (int) $pathOrderProxyCache[$cacheKey];
	}
	$fzocTh = fractal_zip::outer_codec_temp_input_threshold_bytes();
	$fzocReuse = null;
	if($fzocTh > 0 && $pl >= $fzocTh) {
		$rz = $this->program_path . DS . 'fzocp_' . substr(md5(fractal_zip::hot_string_digest($toScore) . "\0ocp\0" . (string) spl_object_id($this)), 0, 16) . '.bin';
		if(@file_put_contents($rz, $toScore) === false) {
			$rz = null;
		}
		$fzocReuse = $rz;
	}
	$best = PHP_INT_MAX;
	$blob = $this->outer_brotli_blob($brotliExe, $toScore, 11, null, null, $fzocReuse);
	if($blob !== null && $blob !== '') {
		$best = strlen($blob);
	} else {
		if($fzocReuse !== null && is_file($fzocReuse)) {
			unlink($fzocReuse);
		}
		return PHP_INT_MAX;
	}
	$maxLateGain = 0;
	if($incumbentBest !== null && $incumbentBest !== PHP_INT_MAX && $best > ((int) $incumbentBest + $maxLateGain)) {
		if($fzocReuse !== null && is_file($fzocReuse)) {
			unlink($fzocReuse);
		}
		return $best;
	}
	$proxyTextlike = $this->outer_likely_textlike($toScore);
	if($e['u'] && !$e['d16']) {
		// Text-like bundles already probe w=20/22/24 below; skip w=16 there to avoid one external brotli call per candidate.
		if(!$proxyTextlike) {
			$b16 = $this->outer_brotli_blob($brotliExe, $toScore, 11, null, 16, $fzocReuse);
			if($b16 !== null && $b16 !== '') {
				$l16 = strlen($b16);
				if($l16 < $best) {
					$best = $l16;
				}
			}
		}
		$textExtra = ($uniqCount <= fractal_zip::literal_bundle_path_order_lgwin_sweep_max_candidates())
			&& $proxyTextlike
			&& $pl > 0 && $pl <= 524288
			&& !$e['sp']
			&& !$e['dx'];
		if($textExtra) {
			foreach(array(20, 22, 24) as $xLw) {
				$brX = $this->outer_brotli_blob($brotliExe, $toScore, 11, null, (int) $xLw, $fzocReuse);
				if($brX !== null && $brX !== '') {
					$lx = strlen($brX);
					if($lx < $best) {
						$best = $lx;
					}
				}
			}
		}
	}
	if($fzocReuse !== null && is_file($fzocReuse)) {
		unlink($fzocReuse);
	}
	$pathOrderProxyCache[$cacheKey] = (int) $best;
	if($pathOrderFifoRing === null) {
		$pathOrderFifoRing = array_fill(0, $pathOrderProxyCacheLimit, null);
	}
	if($pathOrderFifoCount === $pathOrderProxyCacheLimit) {
		$ev = $pathOrderFifoRing[$pathOrderFifoHead];
		$pathOrderFifoRing[$pathOrderFifoHead] = null;
		$pathOrderFifoHead = ($pathOrderFifoHead + 1) % $pathOrderProxyCacheLimit;
		$pathOrderFifoCount--;
		if($ev !== null) {
			unset($pathOrderProxyCache[$ev]);
		}
	}
	$pathOrderFifoRing[$pathOrderFifoTail] = $cacheKey;
	$pathOrderFifoTail = ($pathOrderFifoTail + 1) % $pathOrderProxyCacheLimit;
	$pathOrderFifoCount++;
	return $best;
}

/**
 * Reorder FZB4 members for smaller outer compression (brotli-oriented). Lexicographic order is not always best: e.g.
 * descending stored-byte size can improve whole-bundle ratio. Tries path‑sorted asc/desc, stored‑size desc; when member
 * count ≤ FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_EXHAUSTIVE_MAX_N (default 6), tries **all** permutations (bytes-first;
 * skips redundant random shuffles). Else (when n≤16) FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_RANDOM_TRIES pseudo-shuffles
 * (default 36; 0 = skip). Bounded: ≤FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER_MAX_MEMBERS (default 512) members,
 * ≤256 KiB combined store; Brotli Q11 path-order score matches adaptive outer (default + w=16, then w=20/22/24 for textlike when
 * order candidates ≤FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND) when inner ≤
 * FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES (else zlib-9). Disable all: FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER=0.
 *
 * @param array<string, string> $orderedAssoc ksorted path => raw (values unused; keys matter)
 * @param array<string, array{mode: int, store: string}> $modesStores
 * @param string|null $outWinningRawFzb4 set to the raw FZB4 body for the chosen order when passed by reference (avoids a duplicate literal_bundle_fzb4_payload_bytes_for_path_order in the caller)
 * @return array<string, string> same keys in chosen foreach order
 */
function literal_bundle_pick_fzb4_path_order(array $orderedAssoc, array $modesStores, &$outWinningRawFzb4 = null) {
	if(!fractal_zip::literal_bundle_fzb4_path_order_probe_enabled()) {
		return $orderedAssoc;
	}
	$n = sizeof($orderedAssoc);
	$maxN = fractal_zip::literal_bundle_fzb4_path_order_max_members();
	if($n < 2 || $n > $maxN) {
		return $orderedAssoc;
	}
	$sumStore = 0;
	foreach($modesStores as $ms) {
		$sumStore += strlen($ms['store']);
	}
	if($sumStore > 262144) {
		return $orderedAssoc;
	}
	$keys = array_keys($orderedAssoc);
	$candLists = array();
	$candLists[] = $keys;
	$desc = $keys;
	rsort($desc, SORT_STRING);
	$candLists[] = $desc;
	$byStore = $keys;
	usort($byStore, function($a, $b) use ($modesStores) {
		$la = strlen($modesStores[$a]['store']);
		$lb = strlen($modesStores[$b]['store']);
		if($la !== $lb) {
			return $lb <=> $la;
		}
		return strcmp($a, $b);
	});
	$candLists[] = $byStore;
	// Flat tree with many `mNNNN.ext` slices plus a few root helpers: lexicographic order often places `.gitignore` /
	// `README` / `generate.php` before `m0000.*`, breaking long shared-prefix chains. Try contiguous m-run first.
	$mRun = array();
	$nonMRun = array();
	foreach($keys as $mk) {
		$mks = (string) $mk;
		if(preg_match('/^m[0-9]+\\./', $mks)) {
			$mRun[] = $mks;
		} else {
			$nonMRun[] = $mks;
		}
	}
	if(sizeof($mRun) >= 2 && sizeof($nonMRun) >= 1 && sizeof($mRun) + sizeof($nonMRun) === $n) {
		sort($mRun, SORT_STRING);
		sort($nonMRun, SORT_STRING);
		$candLists[] = array_merge($mRun, $nonMRun);
	}
	$exhMaxN = fractal_zip::literal_bundle_fzb4_path_exhaustive_max_n();
	$didExhaustive = false;
	if($n >= 2 && $n <= $exhMaxN) {
		foreach(fractal_zip::literal_bundle_fzb4_path_all_permutations($keys) as $perm) {
			$candLists[] = $perm;
		}
		$didExhaustive = true;
	}
	$randTries = fractal_zip::literal_bundle_fzb4_path_random_tries();
	if(!$didExhaustive && $randTries > 0 && $n <= 16) {
		$salt = crc32(implode("\0", $keys));
		for($ti = 0; $ti < $randTries; $ti++) {
			$candLists[] = $this->literal_bundle_fzb4_path_der_shuffle($keys, $salt, $ti);
		}
	}
	$uniq = array();
	foreach($candLists as $seq) {
		$uniq[implode("\0", $seq)] = $seq;
	}
	$prefilterToScore = array();
	$prefilterRawPayload = array();
	if(fractal_zip::lifestyle_speed_profile_enabled()) {
		$topK = fractal_zip::lifestyle_fzb4_path_order_prefilter_top_k();
		if($topK > 0 && sizeof($uniq) > $topK) {
			$ranked = array();
			foreach($uniq as $sig => $seq) {
				$payload = $this->literal_bundle_fzb4_payload_bytes_for_path_order($seq, $modesStores);
				$toScore = $this->finalize_literal_bundle_inner_for_compress($payload);
				$qz = gzdeflate($toScore, 1);
				$q = ($qz !== false) ? strlen($qz) : strlen($toScore);
				$ranked[] = array('sig' => (string) $sig, 'seq' => $seq, 'q' => (int) $q);
				$prefilterRawPayload[(string) $sig] = $payload;
				$prefilterToScore[(string) $sig] = $toScore;
			}
			usort($ranked, function($a, $b) {
				if($a['q'] !== $b['q']) {
					return $a['q'] <=> $b['q'];
				}
				return strcmp((string) $a['sig'], (string) $b['sig']);
			});
			$ranked = array_slice($ranked, 0, $topK);
			$reduced = array();
			foreach($ranked as $r) {
				$reduced[(string) $r['sig']] = $r['seq'];
			}
			$uniq = $reduced;
		}
	}
	$uniqN = count($uniq);
	$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_path_order_proxy_max_bytes();
	$brotliExe = fractal_zip::brotli_executable();
	$skipBrotli = !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_BROTLI');
	$nonTextCap = fractal_zip::literal_bundle_path_order_nontextlike_max_candidates();
	$likelyTextlikeForPathOrder = null;
	$scoredCandidates = 0;
	$bestSeq = $keys;
	$bestScore = PHP_INT_MAX;
	$bestRawPayload = null;
	foreach($uniq as $sig => $seq) {
		if($nonTextCap > 0 && $likelyTextlikeForPathOrder === false && $scoredCandidates >= $nonTextCap) {
			break;
		}
		if(isset($prefilterRawPayload[(string) $sig]) && isset($prefilterToScore[(string) $sig])) {
			$payload = $prefilterRawPayload[(string) $sig];
			$toScore = $prefilterToScore[(string) $sig];
		} else {
			$payload = $this->literal_bundle_fzb4_payload_bytes_for_path_order($seq, $modesStores);
			// Match zip_folder / adaptive_compress: outer sees finalize_literal_bundle_inner_for_compress (optional FZWS), not raw FZB4.
			$toScore = $this->finalize_literal_bundle_inner_for_compress($payload);
		}
		if($likelyTextlikeForPathOrder === null) {
			$likelyTextlikeForPathOrder = $this->outer_likely_textlike($toScore);
		}
		$pl = strlen($toScore);
		$sc = PHP_INT_MAX;
		if($brotliExe !== null && !$skipBrotli && $fzbLitMax > 0 && $pl <= $fzbLitMax) {
			$sc = $this->literal_bundle_path_order_brotli_q11_proxy_len($brotliExe, $toScore, $uniqN, $bestScore);
		}
		if($sc === PHP_INT_MAX) {
			$z = gzdeflate($toScore, 9);
			if($z !== false) {
				$sc = strlen($z);
			}
		}
		if($sc < $bestScore) {
			$bestScore = $sc;
			$bestSeq = $seq;
			$bestRawPayload = $payload;
		}
		$scoredCandidates++;
	}
	if(func_num_args() >= 3) {
		if($bestRawPayload === null) {
			$bestRawPayload = $this->literal_bundle_fzb4_payload_bytes_for_path_order($bestSeq, $modesStores);
		}
		$outWinningRawFzb4 = $bestRawPayload;
	}
	$out = array();
	foreach($bestSeq as $k) {
		$out[$k] = $orderedAssoc[$k];
	}
	return $out;
}

/**
 * Binary literal bundle payload for low-overhead no-fractal cases.
 * Format FZB4: magic + repeated [varint common_prefix_len][varint suffix_len][path_suffix][u8 mode][varint data_len][bytes].
 * Format FZB5: flat names sharing one extension — member_count chr(extLen) ext (chr(nameLen) name chr(mode) varint dataLen data)*.
 * member_count: one byte 1..255, or byte 0 + varint when count > 255.
 * Format FZB6: flat names sharing one extension and numeric suffix with shared prefix — chr(count) chr(extLen) ext chr(prefixLen) prefix chr(digitsLen) (chr(mode) varint num varint dataLen data)*.
 * Path is reconstructed as prev_path[0:common_prefix_len] + path_suffix (FZB4), name+ext (FZB5), or prefix+zero-padded number+ext (FZB6).
 * mode 0=raw, 1=delta, 2=xor-adjacent, 3=nibble-swap, 4=invert, 5=BMP row-horizontal delta (8/24/32 bpp BI_RGB, BITMAPINFOHEADER or extended DIB), 12=full-buffer byte reverse, 13=BMP column-vertical delta (same header/tail as 5), 14=square-block transpose (leading floor(sqrt(n))^2 bytes as s×s row-major, transpose, tail unchanged), 15=BMP uniform grid (BSS1: template cell + per-cell scalar or BGR triple vs template), 16=BMP grid core+rim (BSS2: core-only template + per-cell shift on core + raw cell borders), 17=chained literal transforms (multipass; see literal_bundle_greedy_transform_chain), 18=multi-member ZIP semantic (varint count + per entry: varint name + u8 inner_mode + varint inner_store; decode rebuilds ZIP; nested entries recurse choose_best). 6=nested gzip layers (bit-exact restore), 7=single-member ZIP semantic (varint name + inner; extract matches; container bytes may differ), 8=single-file 7z semantic (varint rel path + inner), 9=legacy raster (disk + inline FZRC1), 10=raster dedup (disk + FZBD table index), 11=MoPAQ/MPQ semantic (varint tag + inner FMQ2 blob; decode rebuilds via python3+smpq; default peel accepts extract-equivalent repack, not necessarily byte-identical container), 19=OLE CFB compound, 20=single-file POSIX ustar TAR semantic (varint tag + inner; tag encodes header+trailer for bit-exact rebuild; FRACTAL_ZIP_LITERAL_SEMANTIC_TAR=0 disables peel). When any member uses mode 10, encoder may prefix FZBD v1 (table + FZB4/5/6 body). Gzip: FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER=0 disables. ZIP/7z/MPQ peel: FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP / FRACTAL_ZIP_LITERAL_SEMANTIC_7Z / FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ =0 disables; FRACTAL_ZIP_LITERAL_SEMANTIC_PEEL_MAX_LAYERS caps depth; multi-member ZIP literals: FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP_MULTI / FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_MEMBERS / FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_RAW_BYTES. PNG/GIF/WebP/JPEG and stream types in fractal_zip_literal_pac_registry.php may preprocess. Multi-FLAC merge uses inner FZCD, not a per-member mode.
 * Raster FZBD path is optional: default raster_dedup_policy auto uses FZBD only when at least two members share one canonical blob (member count > table size), then picks plain vs raster by smaller compressed size on finalize_literal_bundle_inner_for_compress: gzcompress(.,9) when combined inner ≤ FRACTAL_ZIP_LITERAL_BUNDLE_RASTER_PROBE_ZLIB9_MAX_COMBINED_BYTES (default 8 MiB), else gzdeflate(.,1). Single-file or all-unique-canonical rasters keep plain (no FZRC1 tax). Tests: raster_dedup_policy always|never. Policy `always` skips the initial plain-only choose_best pass (same packed bytes; faster). Policy `auto` reuses plain per-member modes/stores in the raster pass when a member does not use raster dedup (avoids duplicate choose_best).
 * No fractal_string or operators; decoder maps values to escaped literals.
 *
 * @param array<string,string> $array_raw_files_by_path
 * @param array{raster_dedup_policy?: 'auto'|'always'|'never'}|null $encode_opts
 */
function encode_literal_bundle_payload($array_raw_files_by_path, $encode_opts = null) {
	$ordered = $array_raw_files_by_path;
	if (class_exists('fractal_zip_encode_pipeline', false) && fractal_zip_encode_pipeline::reorder_members_enabled()) {
		$ordered = fractal_zip_encode_pipeline::reorder_raw_members_for_locality($ordered);
		fractal_zip_encode_pipeline::html_trace_checkpoint(
			fractal_zip_encode_pipeline::PHASE_STREAM_REORDER,
			'encode_literal_bundle_payload: locality reorder hook (v1 stable path sort)'
		);
	} else {
		ksort($ordered, SORT_STRING);
	}
	$count = sizeof($ordered);
	if($count === 0) {
		return 'FZB4';
	}
	$sharedExt = null;
	$fzb5LayoutOk = true;
	$fzb6PatternOk = false;
	$fzb6Prefix = null;
	$fzb6DigitsLen = 0;
	$fzb6Nums = array();
	if($fzb5LayoutOk) {
		foreach($ordered as $path => $rawBytes) {
			$path = (string)$path;
			if(strpos($path, '/') !== false || strpos($path, '\\') !== false) {
				$fzb5LayoutOk = false;
				break;
			}
			$dot = strrpos($path, '.');
			if($dot === false || $dot < 1 || $dot === strlen($path) - 1) {
				$fzb5LayoutOk = false;
				break;
			}
			$ext = substr($path, $dot);
			$name = substr($path, 0, $dot);
			if(strlen($name) > 255 || strlen($ext) > 32) {
				$fzb5LayoutOk = false;
				break;
			}
			if($sharedExt === null) {
				$sharedExt = $ext;
			} elseif($sharedExt !== $ext) {
				$fzb5LayoutOk = false;
				break;
			}
		}
		if($fzb5LayoutOk && $sharedExt !== null) {
			$fzb6PatternOk = true;
			foreach($ordered as $path => $rawBytes) {
				$path = (string)$path;
				$dot = strrpos($path, '.');
				$name = substr($path, 0, $dot);
				if(!preg_match('/^(.+?)([0-9]+)$/', $name, $m)) {
					$fzb6PatternOk = false;
					break;
				}
				$prefix = (string)$m[1];
				$digits = (string)$m[2];
				if($fzb6Prefix === null) {
					$fzb6Prefix = $prefix;
					$fzb6DigitsLen = strlen($digits);
					if(strlen($fzb6Prefix) > 255 || $fzb6DigitsLen < 1 || $fzb6DigitsLen > 9) {
						$fzb6PatternOk = false;
						break;
					}
				} elseif($fzb6Prefix !== $prefix || $fzb6DigitsLen !== strlen($digits)) {
					$fzb6PatternOk = false;
					break;
				}
				$num = (int)$digits;
				$reconstructed = str_pad((string)$num, $fzb6DigitsLen, '0', STR_PAD_LEFT);
				if($reconstructed !== $digits) {
					$fzb6PatternOk = false;
					break;
				}
				$fzb6Nums[$path] = $num;
			}
		}
	} else {
		$fzb5LayoutOk = false;
	}
	$rasterDedupPolicy = 'auto';
	if(is_array($encode_opts) && isset($encode_opts['raster_dedup_policy'])) {
		$rp = $encode_opts['raster_dedup_policy'];
		if($rp === 'always' || $rp === 'never' || $rp === 'auto') {
			$rasterDedupPolicy = $rp;
		}
	}
	$packLiteralBundleWithTable = function($modesStores, $rasterCanonTable) use ($ordered, $count, $fzb5LayoutOk, $sharedExt, $fzb6PatternOk, $fzb6Prefix, $fzb6DigitsLen, $fzb6Nums) {
		$winningFzb4Raw = null;
		$orderedFzb4 = $this->literal_bundle_pick_fzb4_path_order($ordered, $modesStores, $winningFzb4Raw);
		$payload4 = $winningFzb4Raw !== null ? $winningFzb4Raw : $this->literal_bundle_fzb4_payload_bytes_for_path_order(array_keys($orderedFzb4), $modesStores);
		$fzbLitMaxPick = fractal_zip::fzb_literal_brotli_q11_path_order_proxy_max_bytes();
		$brotliExePick = fractal_zip::brotli_executable();
		$skipBrotliPick = !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_BROTLI');
		$layoutUseProxy = fractal_zip::literal_bundle_layout_use_proxy_score();
		$literalBundleLayoutProxyScore = function($pl) use ($fzbLitMaxPick, $brotliExePick, $skipBrotliPick) {
			$pl = $this->finalize_literal_bundle_inner_for_compress((string) $pl);
			$plen = strlen($pl);
			$sc = PHP_INT_MAX;
			if($brotliExePick !== null && !$skipBrotliPick && $fzbLitMaxPick > 0 && $plen <= $fzbLitMaxPick) {
				$blob = $this->outer_brotli_blob($brotliExePick, $pl, 11, null);
				if($blob !== null && $blob !== '') {
					$sc = strlen($blob);
				}
			}
			if($sc === PHP_INT_MAX) {
				$z = gzdeflate($pl, 9);
				if($z !== false) {
					$sc = strlen($z);
				}
			}
			return $sc;
		};
		$fzbd_prefix_if_any = function($body) use ($rasterCanonTable) {
			$body = (string) $body;
			if(sizeof($rasterCanonTable) === 0) {
				return $body;
			}
			if(strlen($body) < 4) {
				return $body;
			}
			$sig = substr($body, 0, 4);
			if($sig !== 'FZB4' && $sig !== 'FZB5' && $sig !== 'FZB6') {
				return $body;
			}
			$canonParts = array();
			foreach($rasterCanonTable as $blob) {
				$canonParts[] = $this->encode_varint_u32(strlen($blob)) . $blob;
			}
			return 'FZBD' . chr(1) . $this->encode_varint_u32(sizeof($rasterCanonTable)) . implode('', $canonParts) . $body;
		};
		if($fzb5LayoutOk && $sharedExt !== null) {
			$mem5 = array();
			foreach($ordered as $path => $rawBytes) {
				$path = (string)$path;
				$dot = strrpos($path, '.');
				$name = substr($path, 0, $dot);
				$ms = $modesStores[$path];
				$mem5[] = chr(strlen($name)) . $name . chr($ms['mode']) . $this->encode_varint_u32(strlen($ms['store'])) . $ms['store'];
			}
			$payload5 = 'FZB5' . $this->encode_fzb56_bundle_member_count($count) . chr(strlen($sharedExt)) . $sharedExt . implode('', $mem5);
			$payload6 = null;
			if($fzb6PatternOk && $fzb6Prefix !== null) {
				$mem6 = array();
				foreach($ordered as $path => $rawBytes) {
					$path = (string)$path;
					$ms = $modesStores[$path];
					$mem6[] = chr($ms['mode']) . $this->encode_varint_u32((int)$fzb6Nums[$path]) . $this->encode_varint_u32(strlen($ms['store'])) . $ms['store'];
				}
				$payload6 = 'FZB6' . $this->encode_fzb56_bundle_member_count($count) . chr(strlen($sharedExt)) . $sharedExt
					. chr(strlen($fzb6Prefix)) . $fzb6Prefix . chr($fzb6DigitsLen) . implode('', $mem6);
			}
			$fzb4Len = strlen($payload4);
			if($layoutUseProxy) {
				$bestPl = $payload4;
				$bestSc = $literalBundleLayoutProxyScore($payload4);
				$bestRaw = $fzb4Len;
				$s5 = $literalBundleLayoutProxyScore($payload5);
				$r5 = strlen($payload5);
				if($s5 < $bestSc || ($s5 === $bestSc && $r5 < $bestRaw)) {
					$bestPl = $payload5;
					$bestSc = $s5;
					$bestRaw = $r5;
				}
				if($payload6 !== null) {
					$s6 = $literalBundleLayoutProxyScore($payload6);
					$r6 = strlen($payload6);
					if($s6 < $bestSc || ($s6 === $bestSc && $r6 < $bestRaw)) {
						$bestPl = $payload6;
					}
				}
				return $fzbd_prefix_if_any($bestPl);
			}
			$fzb5Len = strlen($payload5);
			$fzb5Wins = ($fzb5Len <= $fzb4Len);
			if($fzb5Wins && $payload6 !== null) {
				$fzb6Len = strlen($payload6);
				if($fzb6Len < $fzb5Len && $fzb6Len < $fzb4Len) {
					return $fzbd_prefix_if_any($payload6);
				}
			}
			if($fzb5Wins) {
				return $fzbd_prefix_if_any($payload5);
			}
		}
		return $fzbd_prefix_if_any($payload4);
	};
	foreach($ordered as $path => $_raw) {
		if(strlen((string)$path) > 65535) {
			return null;
		}
	}
	fractal_zip_encode_pipeline::html_trace_checkpoint(
		fractal_zip_encode_pipeline::PHASE_TRANSFORMS_AND_MODES,
		'encode_literal_bundle_payload: literal transform/mode tournament (choose_best_literal_bundle_transform, raster dedup pass, pack to FZB*)'
	);
	$modesStoresPlain = array();
	if($rasterDedupPolicy !== 'always') {
		foreach($ordered as $path => $rawBytes) {
			$path = (string)$path;
			$rawBytes = (string)$rawBytes;
			list($mode, $storeBytes) = $this->choose_best_literal_bundle_transform($rawBytes, $path);
			$modesStoresPlain[$path] = array('mode' => $mode, 'store' => $storeBytes);
		}
		if($rasterDedupPolicy === 'never') {
			return $packLiteralBundleWithTable($modesStoresPlain, array());
		}
	}
	$modesStoresRaster = array();
	$rasterCanonTable = array();
	$rasterCanonHashToIdx = array();
	$rasterMemberCount = 0;
	foreach($ordered as $path => $rawBytes) {
		$path = (string)$path;
		$rawBytes = (string)$rawBytes;
		$diskSnapshot = $rawBytes;
		$usedRasterDedup = false;
		if($path !== '') {
			fractal_zip_ensure_literal_pac_stack_loaded();
			list($work, $semanticLayers, $gzipStack) = fractal_zip_literal_deep_unwrap_with_layers($path, $rawBytes);
			$canon = fractal_zip_raster_canonical_try_for_bundle($path, $work, $diskSnapshot);
			if($canon !== null && $canon !== $work) {
				$h = md5($canon, true);
				if(!isset($rasterCanonHashToIdx[$h])) {
					$rasterCanonHashToIdx[$h] = sizeof($rasterCanonTable);
					$rasterCanonTable[] = $canon;
				}
				$idx = (int) $rasterCanonHashToIdx[$h];
				$inner10 = $this->encode_varint_u32(strlen($diskSnapshot)) . $diskSnapshot . $this->encode_varint_u32($idx);
				list($mode, $storeBytes) = fractal_zip_literal_bundle_wrap_all_layers($semanticLayers, $gzipStack, 10, $inner10);
				$modesStoresRaster[$path] = array('mode' => $mode, 'store' => $storeBytes);
				$usedRasterDedup = true;
				$rasterMemberCount++;
			}
		}
		if(!$usedRasterDedup) {
			if(isset($modesStoresPlain[$path])) {
				$modesStoresRaster[$path] = $modesStoresPlain[$path];
			} else {
				list($mode, $storeBytes) = $this->choose_best_literal_bundle_transform($rawBytes, $path);
				$modesStoresRaster[$path] = array('mode' => $mode, 'store' => $storeBytes);
			}
		}
	}
	if($rasterDedupPolicy === 'always') {
		return $packLiteralBundleWithTable($modesStoresRaster, $rasterCanonTable);
	}
	if(sizeof($rasterCanonTable) === 0) {
		return $packLiteralBundleWithTable($modesStoresPlain, array());
	}
	$plainBody = $packLiteralBundleWithTable($modesStoresPlain, array());
	if($plainBody === null) {
		return null;
	}
	if($rasterMemberCount <= sizeof($rasterCanonTable)) {
		return $plainBody;
	}
	$rasterBody = $packLiteralBundleWithTable($modesStoresRaster, $rasterCanonTable);
	if($rasterBody === null) {
		return null;
	}
	$finPlain = $this->finalize_literal_bundle_inner_for_compress($plainBody);
	$finRaster = $this->finalize_literal_bundle_inner_for_compress($rasterBody);
	$probeCombined = strlen($plainBody) + strlen($rasterBody);
	$zlib9Max = fractal_zip::literal_bundle_raster_probe_zlib9_max_combined_bytes();
	if($zlib9Max > 0 && $probeCombined <= $zlib9Max) {
		$cPlain = gzcompress($finPlain, 9);
		$cRaster = gzcompress($finRaster, 9);
		$lenPlain = ($cPlain === false) ? PHP_INT_MAX : strlen($cPlain);
		$lenRaster = ($cRaster === false) ? PHP_INT_MAX : strlen($cRaster);
	} else {
		$dPlain = gzdeflate($finPlain, 1);
		$dRaster = gzdeflate($finRaster, 1);
		$lenPlain = ($dPlain === false) ? PHP_INT_MAX : strlen($dPlain);
		$lenRaster = ($dRaster === false) ? PHP_INT_MAX : strlen($dRaster);
	}
	if($lenRaster < $lenPlain) {
		return $rasterBody;
	}
	return $plainBody;
}

/**
 * Full-buffer decode of FZCD v1 (same layout as extract_fzcd_bundle_streaming_from_path).
 * @return array{array<string, string>, string}
 */
function decode_fzcd_payload_from_string($contents) {
	fractal_zip_ensure_flac_pac_loaded();
	$n = strlen($contents);
	if($n < 6) {
		fractal_zip::fatal_error('Corrupt FZCD (too short).');
	}
	if(substr($contents, 0, 4) !== 'FZCD') {
		fractal_zip::fatal_error('Not FZCD payload.');
	}
	if(ord($contents[4]) !== 1) {
		fractal_zip::fatal_error('Unsupported FZCD version.');
	}
	if((ord($contents[5]) & 1) === 0) {
		fractal_zip::fatal_error('FZCD: merged payload flag missing (flags).');
	}
	$fzcdFlags = ord($contents[5]);
	$fzcdDecBase = fractal_zip::hot_string_digest($contents) . "\0fzcddec\0" . (string) spl_object_id($this);
	$offset = 6;
	$nFl = $this->decode_varint_u32($contents, $offset, $n, 'FZCD');
	$prevPath = '';
	$files = array();
	$entries = array();
	for($fi = 0; $fi < $nFl; $fi++) {
		$prefixLen = $this->decode_varint_u32($contents, $offset, $n, 'FZCD');
		$suffixLen = $this->decode_varint_u32($contents, $offset, $n, 'FZCD');
		if($prefixLen > strlen($prevPath)) {
			fractal_zip::fatal_error('Corrupt FZCD path prefix.');
		}
		if($offset + $suffixLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCD path suffix.');
		}
		$suf = substr($contents, $offset, $suffixLen);
		$offset += $suffixLen;
		$path = substr($prevPath, 0, $prefixLen) . $suf;
		if($offset + 15 > $n) {
			fractal_zip::fatal_error('Corrupt FZCD FLAC meta.');
		}
		$metaB = substr($contents, $offset, 15);
		$offset += 15;
		$um = unpack('Vsr/Cch/Cbps/Cfmt/Pplen', $metaB);
		if(!is_array($um) || !isset($um['plen'])) {
			fractal_zip::fatal_error('Corrupt FZCD FLAC meta.');
		}
		$entries[] = array(
			'path' => $path,
			'sr' => (int) $um['sr'],
			'ch' => (int) $um['ch'],
			'fmt' => (int) $um['fmt'],
			'pcmLen' => (int) $um['plen'],
		);
		$prevPath = $path;
	}
	if(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_CHUNKED) !== 0) {
		if($entries === []) {
			fractal_zip::fatal_error('FZCD: empty FLAC table.');
		}
		if($offset + 12 > $n) {
			fractal_zip::fatal_error('Corrupt FZCD (merged fractal chunked header).');
		}
		$ur = unpack('Praw', substr($contents, $offset, 8));
		$offset += 8;
		$unc = unpack('Vnch', substr($contents, $offset, 4));
		$offset += 4;
		if(!is_array($ur) || !is_array($unc) || !isset($ur['raw'], $unc['nch'])) {
			fractal_zip::fatal_error('Corrupt FZCD merged fractal chunked header unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$nChunks = (int) $unc['nch'];
		if($rawTotal < 1 || $nChunks < 1) {
			fractal_zip::fatal_error('Corrupt FZCD merged fractal chunked counts.');
		}
		$chunkPre = ($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_CHUNK_PCM_PRETRANSFORM) !== 0;
		$pcmFmt0 = (int) $entries[0]['fmt'];
		$ch0 = (int) $entries[0]['ch'];
		$pcmPieces = array();
		$sumChunk = 0;
		for($ci = 0; $ci < $nChunks; $ci++) {
			if($offset + 16 > $n) {
				fractal_zip::fatal_error('Corrupt FZCD merged fractal chunk sizes.');
			}
			$ucp = unpack('Pclen', substr($contents, $offset, 8));
			$offset += 8;
			$uil = unpack('Pilen', substr($contents, $offset, 8));
			$offset += 8;
			if(!is_array($ucp) || !is_array($uil) || !isset($ucp['clen'], $uil['ilen'])) {
				fractal_zip::fatal_error('Corrupt FZCD merged fractal chunk size unpack.');
			}
			$cLen = (int) $ucp['clen'];
			$iLen = (int) $uil['ilen'];
			$preId = FRACTAL_ZIP_PCM_PRE_NONE;
			if($chunkPre) {
				if($offset + 1 > $n) {
					fractal_zip::fatal_error('Corrupt FZCD merged fractal chunk pre id.');
				}
				$preId = ord($contents[$offset]);
				$offset++;
			}
			if($cLen < 1 || $iLen < 1 || $offset + $iLen > $n) {
				fractal_zip::fatal_error('Corrupt FZCD merged fractal chunk body.');
			}
			$frBody = substr($contents, $offset, $iLen);
			$offset += $iLen;
			$innerDec = $this->decode_container_payload($frBody);
			if(!is_array($innerDec) || !isset($innerDec[0], $innerDec[1]) || !is_array($innerDec[0])) {
				fractal_zip::fatal_error('FZCD: merged fractal chunk inner decode.');
			}
			$innerMap = $innerDec[0];
			$innerFr = (string) $innerDec[1];
			if(!isset($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER])) {
				fractal_zip::fatal_error('FZCD: merged fractal chunk member missing.');
			}
			$pcmPiece = $this->unzip($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER], $innerFr, true);
			if(strlen($pcmPiece) !== $cLen) {
				fractal_zip::fatal_error('FZCD: merged fractal chunk PCM length.');
			}
			if($chunkPre) {
				$pcmInv = fractal_zip_fzcd_merge_pcm_piece_maybe_invert($pcmPiece, $preId, $cLen, $pcmFmt0, $ch0);
				if($pcmInv === null) {
					fractal_zip::fatal_error('FZCD: merged fractal chunk PCM pre-transform inverse.');
				}
				$pcmPiece = $pcmInv;
			}
			$pcmPieces[] = $pcmPiece;
			$sumChunk += $cLen;
		}
		$pcmAll = implode('', $pcmPieces);
		if(strlen($pcmAll) !== $rawTotal || $sumChunk !== $rawTotal) {
			fractal_zip::fatal_error('FZCD: merged fractal chunked PCM total length.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfx = substr(md5($fzcdDecBase . "\0mfch_trk\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '_p.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '.flac';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fractal_zip::fatal_error('FZCD memory decode: temp PCM.');
			}
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $flacTmp)) {
				@unlink($pcmTmp);
				fractal_zip::fatal_error('FZCD memory decode: PCM→FLAC.');
			}
			@unlink($pcmTmp);
			$fb = file_get_contents($flacTmp);
			@unlink($flacTmp);
			if($fb === false) {
				fractal_zip::fatal_error('FZCD memory decode: read FLAC.');
			}
			$files[$e['path']] = $this->escape_literal_for_storage($fb);
		}
	} elseif(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_PCM) !== 0) {
		if($entries === []) {
			fractal_zip::fatal_error('FZCD: empty FLAC table.');
		}
		if($offset + 16 > $n) {
			fractal_zip::fatal_error('Corrupt FZCD (merged fractal sizes).');
		}
		$szh = substr($contents, $offset, 16);
		$offset += 16;
		$ur = unpack('Praw', substr($szh, 0, 8));
		$ufr = unpack('Pfrl', substr($szh, 8, 8));
		if(!is_array($ur) || !is_array($ufr) || !isset($ur['raw'], $ufr['frl'])) {
			fractal_zip::fatal_error('Corrupt FZCD merged fractal size unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$frLen = (int) $ufr['frl'];
		if($offset + $frLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCD merged fractal body.');
		}
		$frBody = substr($contents, $offset, $frLen);
		$offset += $frLen;
		$innerDec = $this->decode_container_payload($frBody);
		if(!is_array($innerDec) || !isset($innerDec[0], $innerDec[1]) || !is_array($innerDec[0])) {
			fractal_zip::fatal_error('FZCD: merged fractal inner decode.');
		}
		$innerMap = $innerDec[0];
		$innerFr = (string) $innerDec[1];
		if(!isset($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER])) {
			fractal_zip::fatal_error('FZCD: merged fractal member missing.');
		}
		$pcmAll = $this->unzip($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER], $innerFr, true);
		if(strlen($pcmAll) !== $rawTotal) {
			fractal_zip::fatal_error('FZCD: PCM length after merged fractal.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfx = substr(md5($fzcdDecBase . "\0mf1_trk\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '_p.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '.flac';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fractal_zip::fatal_error('FZCD memory decode: temp PCM.');
			}
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $flacTmp)) {
				@unlink($pcmTmp);
				fractal_zip::fatal_error('FZCD memory decode: PCM→FLAC.');
			}
			@unlink($pcmTmp);
			$fb = file_get_contents($flacTmp);
			@unlink($flacTmp);
			if($fb === false) {
				fractal_zip::fatal_error('FZCD memory decode: read FLAC.');
			}
			$files[$e['path']] = $this->escape_literal_for_storage($fb);
		}
	} elseif(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_FLAC) !== 0) {
		if($entries === []) {
			fractal_zip::fatal_error('FZCD: empty FLAC table.');
		}
		if($offset + 16 > $n) {
			fractal_zip::fatal_error('Corrupt FZCD (merged FLAC sizes).');
		}
		$szh = substr($contents, $offset, 16);
		$offset += 16;
		$ur = unpack('Praw', substr($szh, 0, 8));
		$uf = unpack('Pfll', substr($szh, 8, 8));
		if(!is_array($ur) || !is_array($uf) || !isset($ur['raw'], $uf['fll'])) {
			fractal_zip::fatal_error('Corrupt FZCD merged FLAC size unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$flLen = (int) $uf['fll'];
		if($offset + $flLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCD merged FLAC body.');
		}
		$flBody = substr($contents, $offset, $flLen);
		$offset += $flLen;
		$sfxM = substr(md5($fzcdDecBase . "\0mfl_bulk"), 0, 16);
		$flacTmp = sys_get_temp_dir() . DS . 'fzcdmfl_' . $sfxM . '.flac';
		$pcmAllPath = sys_get_temp_dir() . DS . 'fzcdmpcm_' . substr(md5($fzcdDecBase . "\0mpcm_all"), 0, 16) . '.raw';
		if(file_put_contents($flacTmp, $flBody) === false) {
			fractal_zip::fatal_error('FZCD: temp merged FLAC.');
		}
		$pcmFmt0 = $entries[0]['fmt'];
		if(!fractal_zip_flac_pac_decode_file_to_pcm_path($flacTmp, $pcmFmt0, $pcmAllPath)) {
			@unlink($flacTmp);
			fractal_zip::fatal_error('FZCD: merged FLAC→PCM decode.');
		}
		@unlink($flacTmp);
		$pcmAll = file_get_contents($pcmAllPath);
		@unlink($pcmAllPath);
		if($pcmAll === false) {
			fractal_zip::fatal_error('FZCD: read merged PCM.');
		}
		if(strlen($pcmAll) !== $rawTotal) {
			fractal_zip::fatal_error('FZCD: PCM length mismatch after merged FLAC.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfx = substr(md5($fzcdDecBase . "\0mfl_trk\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '_p.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '.flac';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fractal_zip::fatal_error('FZCD memory decode: temp PCM.');
			}
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $flacTmp)) {
				@unlink($pcmTmp);
				fractal_zip::fatal_error('FZCD memory decode: PCM→FLAC.');
			}
			@unlink($pcmTmp);
			$fb = file_get_contents($flacTmp);
			@unlink($flacTmp);
			if($fb === false) {
				fractal_zip::fatal_error('FZCD memory decode: read FLAC.');
			}
			$files[$e['path']] = $this->escape_literal_for_storage($fb);
		}
	} elseif(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_GZIP) !== 0) {
		if($offset + 16 > $n) {
			fractal_zip::fatal_error('Corrupt FZCD (gzip PCM sizes).');
		}
		$szh = substr($contents, $offset, 16);
		$offset += 16;
		$ur = unpack('Praw', substr($szh, 0, 8));
		$ug = unpack('Pgzl', substr($szh, 8, 8));
		if(!is_array($ur) || !is_array($ug) || !isset($ur['raw'], $ug['gzl'])) {
			fractal_zip::fatal_error('Corrupt FZCD PCM size unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$gzLen = (int) $ug['gzl'];
		if($offset + $gzLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCD gzip PCM.');
		}
		$gzBody = substr($contents, $offset, $gzLen);
		$offset += $gzLen;
		$pcmAll = gzdecode($gzBody);
		if($pcmAll === false) {
			fractal_zip::fatal_error('FZCD: gzip PCM decode failed.');
		}
		if(strlen($pcmAll) !== $rawTotal) {
			fractal_zip::fatal_error('FZCD: PCM length mismatch after gzip.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfx = substr(md5($fzcdDecBase . "\0mgz_trk\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '_p.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '.flac';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fractal_zip::fatal_error('FZCD memory decode: temp PCM.');
			}
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $flacTmp)) {
				@unlink($pcmTmp);
				fractal_zip::fatal_error('FZCD memory decode: PCM→FLAC.');
			}
			@unlink($pcmTmp);
			$fb = file_get_contents($flacTmp);
			@unlink($flacTmp);
			if($fb === false) {
				fractal_zip::fatal_error('FZCD memory decode: read FLAC.');
			}
			$files[$e['path']] = $this->escape_literal_for_storage($fb);
		}
	} else {
		foreach($entries as $ti => $e) {
			if($offset + $e['pcmLen'] > $n) {
				fractal_zip::fatal_error('Corrupt FZCD PCM.');
			}
			$pcm = substr($contents, $offset, $e['pcmLen']);
			$offset += $e['pcmLen'];
			$sfx = substr(md5($fzcdDecBase . "\0min_trk\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '_p.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . $sfx . '.flac';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fractal_zip::fatal_error('FZCD memory decode: temp PCM.');
			}
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $flacTmp)) {
				@unlink($pcmTmp);
				fractal_zip::fatal_error('FZCD memory decode: PCM→FLAC.');
			}
			@unlink($pcmTmp);
			$fb = file_get_contents($flacTmp);
			@unlink($flacTmp);
			if($fb === false) {
				fractal_zip::fatal_error('FZCD memory decode: read FLAC.');
			}
			$files[$e['path']] = $this->escape_literal_for_storage($fb);
		}
	}
	$nOth = $this->decode_varint_u32($contents, $offset, $n, 'FZCD');
	for($oi = 0; $oi < $nOth; $oi++) {
		$prefixLen = $this->decode_varint_u32($contents, $offset, $n, 'FZCD');
		$suffixLen = $this->decode_varint_u32($contents, $offset, $n, 'FZCD');
		if($prefixLen > strlen($prevPath)) {
			fractal_zip::fatal_error('Corrupt FZCD other path.');
		}
		if($offset + $suffixLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCD other suffix.');
		}
		$pathSuffix = substr($contents, $offset, $suffixLen);
		$offset += $suffixLen;
		$path = substr($prevPath, 0, $prefixLen) . $pathSuffix;
		if($offset + 1 > $n) {
			fractal_zip::fatal_error('Corrupt FZCD other mode.');
		}
		$mode = ord($contents[$offset]);
		$offset += 1;
		$dataLen = $this->decode_varint_u32($contents, $offset, $n, 'FZCD');
		if($offset + $dataLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCD other data.');
		}
		$rawStored = substr($contents, $offset, $dataLen);
		$offset += $dataLen;
		$raw = $this->decode_bundle_literal_member($mode, $rawStored);
		$files[$path] = $this->escape_literal_for_storage($raw);
		$prevPath = $path;
	}
	if($offset !== $n) {
		fractal_zip::fatal_error('Corrupt FZCD (trailing bytes).');
	}
	return array($files, '', array());
}

/**
 * Byte offset where gzip deflate stream starts (RFC 1952 member); null if not a simple gzip deflate member.
 */
public static function gzip_member_header_payload_offset($b) {
	$b = (string)$b;
	$n = strlen($b);
	if($n < 18) {
		return null;
	}
	if(ord($b[0]) !== 0x1f || ord($b[1]) !== 0x8b) {
		return null;
	}
	if(ord($b[2]) !== 8) {
		return null;
	}
	$flg = ord($b[3]);
	$pos = 10;
	if(($flg & 4) !== 0) {
		if($n < $pos + 2) {
			return null;
		}
		$xu = unpack('vlen', substr($b, $pos, 2));
		if(!is_array($xu) || !isset($xu['len'])) {
			return null;
		}
		$xlen = (int)$xu['len'];
		$pos += 2 + $xlen;
	}
	if(($flg & 8) !== 0) {
		while($pos < $n && $b[$pos] !== "\0") {
			$pos++;
		}
		if($pos >= $n) {
			return null;
		}
		$pos++;
	}
	if(($flg & 16) !== 0) {
		while($pos < $n && $b[$pos] !== "\0") {
			$pos++;
		}
		if($pos >= $n) {
			return null;
		}
		$pos++;
	}
	if(($flg & 2) !== 0) {
		if($n < $pos + 2) {
			return null;
		}
		$pos += 2;
	}
	if($pos + 8 > $n) {
		return null;
	}
	return $pos;
}

/**
 * Split a gzip member into header / deflate payload / 8-byte trailer for peel-restore packing.
 * @return array{h: string, d: string, t: string}|null
 */
public static function gzip_member_split_for_restore($b) {
	$b = (string)$b;
	$p = fractal_zip::gzip_member_header_payload_offset($b);
	if($p === null) {
		return null;
	}
	$n = strlen($b);
	$t = substr($b, -8);
	$d = substr($b, $p, $n - 8 - $p);
	$h = substr($b, 0, $p);
	if($h . $d . $t !== $b) {
		return null;
	}
	return array('h' => $h, 'd' => $d, 't' => $t);
}

/**
 * Optional trailer after FZC1–FZC6 / FZCT / FZCH / fixed-magic flat singles (FZCB–FZCW etc.) / FZCJ / FZCX / FZCE payloads: exact on-disk bytes for peeled gzip members.
 * Chooses the smallest among: FZG1 (legacy inline), FZG2 (deduped full blobs), FZG3 (one shared trailer + per-path header+deflate), FZG4 (deduped header and deflate tables).
 */
function append_fractal_gzip_peel_restore_trailer($payload, $restoreByPath) {
	$payload = (string)$payload;
	if(!is_array($restoreByPath) || sizeof($restoreByPath) === 0) {
		return $payload;
	}
	ksort($restoreByPath, SORT_STRING);
	$candidates = array();
	$candidates[] = $this->encode_fzg1_peel_trailer($restoreByPath);
	$candidates[] = $this->encode_fzg2_peel_trailer($restoreByPath);
	$f3 = $this->encode_fzg3_peel_trailer_if_applicable($restoreByPath);
	if($f3 !== null) {
		$candidates[] = $f3;
	}
	$f4 = $this->encode_fzg4_peel_trailer_if_applicable($restoreByPath);
	if($f4 !== null) {
		$candidates[] = $f4;
	}
	$best = $candidates[0];
	$bestT = strlen($best);
	foreach($candidates as $c) {
		$cl = strlen($c);
		if($cl < $bestT) {
			$best = $c;
			$bestT = $cl;
		}
	}
	return $payload . $best;
}

function encode_fzg1_peel_trailer($restoreByPath) {
	$parts = array('FZG1', $this->encode_varint_u32(sizeof($restoreByPath)));
	foreach($restoreByPath as $path => $blob) {
		$path = (string)$path;
		$blob = (string)$blob;
		$parts[] = $this->encode_varint_u32(strlen($path)) . $path;
		$parts[] = $this->encode_varint_u32(strlen($blob)) . $blob;
	}
	return implode('', $parts);
}

function encode_fzg2_peel_trailer($restoreByPath) {
	$blobKeyToIdx = array();
	$blobs = array();
	$pathToIdx = array();
	foreach($restoreByPath as $path => $blob) {
		$path = (string)$path;
		$blob = (string)$blob;
		$key = sha1($blob, true);
		if(isset($blobKeyToIdx[$key]) && $blobs[$blobKeyToIdx[$key]] !== $blob) {
			fractal_zip::fatal_error('FZG2 encode: SHA-1 collision in peel restore blob table.');
		}
		if(!isset($blobKeyToIdx[$key])) {
			$blobKeyToIdx[$key] = sizeof($blobs);
			$blobs[] = $blob;
		}
		$pathToIdx[$path] = $blobKeyToIdx[$key];
	}
	ksort($pathToIdx, SORT_STRING);
	$blobParts = array();
	foreach($blobs as $bl) {
		$blobParts[] = $this->encode_varint_u32(strlen($bl)) . $bl;
	}
	$pathParts = array();
	foreach($pathToIdx as $path => $idx) {
		$pathParts[] = $this->encode_varint_u32(strlen($path)) . $path;
		$pathParts[] = $this->encode_varint_u32((int)$idx);
	}
	return 'FZG2' . $this->encode_varint_u32(sizeof($blobs)) . implode('', $blobParts)
		. $this->encode_varint_u32(sizeof($pathToIdx)) . implode('', $pathParts);
}

function encode_fzg3_peel_trailer_if_applicable($restoreByPath) {
	$trailer = null;
	$rows = array();
	foreach($restoreByPath as $path => $blob) {
		$sp = fractal_zip::gzip_member_split_for_restore((string)$blob);
		if($sp === null) {
			return null;
		}
		if($trailer === null) {
			$trailer = $sp['t'];
		} elseif($trailer !== $sp['t']) {
			return null;
		}
		$rows[(string)$path] = $sp;
	}
	ksort($rows, SORT_STRING);
	$rowParts = array();
	foreach($rows as $path => $sp) {
		$h = $sp['h'];
		$d = $sp['d'];
		$rowParts[] = $this->encode_varint_u32(strlen($path)) . $path
			. $this->encode_varint_u32(strlen($h)) . $h
			. $this->encode_varint_u32(strlen($d)) . $d;
	}
	return 'FZG3' . $this->encode_varint_u32(8) . $trailer . $this->encode_varint_u32(sizeof($rows)) . implode('', $rowParts);
}

function encode_fzg4_peel_trailer_if_applicable($restoreByPath) {
	$trailer = null;
	$hdrKeyToIdx = array();
	$hdrList = array();
	$defKeyToIdx = array();
	$defList = array();
	$pairs = array();
	foreach($restoreByPath as $path => $blob) {
		$sp = fractal_zip::gzip_member_split_for_restore((string)$blob);
		if($sp === null) {
			return null;
		}
		if($trailer === null) {
			$trailer = $sp['t'];
		} elseif($trailer !== $sp['t']) {
			return null;
		}
		$hk = sha1($sp['h'], true);
		if(isset($hdrKeyToIdx[$hk]) && $hdrList[$hdrKeyToIdx[$hk]] !== $sp['h']) {
			fractal_zip::fatal_error('FZG4 encode: SHA-1 collision in gzip header table.');
		}
		if(!isset($hdrKeyToIdx[$hk])) {
			$hdrKeyToIdx[$hk] = sizeof($hdrList);
			$hdrList[] = $sp['h'];
		}
		$dk = sha1($sp['d'], true);
		if(isset($defKeyToIdx[$dk]) && $defList[$defKeyToIdx[$dk]] !== $sp['d']) {
			fractal_zip::fatal_error('FZG4 encode: SHA-1 collision in gzip deflate table.');
		}
		if(!isset($defKeyToIdx[$dk])) {
			$defKeyToIdx[$dk] = sizeof($defList);
			$defList[] = $sp['d'];
		}
		$pairs[(string)$path] = array($hdrKeyToIdx[$hk], $defKeyToIdx[$dk]);
	}
	ksort($pairs, SORT_STRING);
	$hdrEnc = array();
	foreach($hdrList as $h) {
		$hdrEnc[] = $this->encode_varint_u32(strlen($h)) . $h;
	}
	$defEnc = array();
	foreach($defList as $d) {
		$defEnc[] = $this->encode_varint_u32(strlen($d)) . $d;
	}
	$pairEnc = array();
	foreach($pairs as $path => $hiDi) {
		$pairEnc[] = $this->encode_varint_u32(strlen($path)) . $path
			. $this->encode_varint_u32((int)$hiDi[0]) . $this->encode_varint_u32((int)$hiDi[1]);
	}
	return 'FZG4' . $this->encode_varint_u32(8) . $trailer
		. $this->encode_varint_u32(sizeof($hdrList)) . implode('', $hdrEnc)
		. $this->encode_varint_u32(sizeof($defList)) . implode('', $defEnc)
		. $this->encode_varint_u32(sizeof($pairs)) . implode('', $pairEnc);
}

/**
 * @param int $offset index after fractal_string bytes; updated to $n on success
 * @return array<string, string> member path => exact bytes to write
 */
function decode_fractal_gzip_peel_restore_trailer($contents, &$offset, $n) {
	$map = array();
	if($offset >= $n) {
		return $map;
	}
	if($offset + 4 > $n) {
		fractal_zip::fatal_error('Corrupt FZC: peel trailer too short.');
	}
	$magic = substr($contents, $offset, 4);
	$offset += 4;
	if($magic === 'FZG1') {
		return $this->decode_fzg1_peel_body($contents, $offset, $n);
	}
	if($magic === 'FZG2') {
		return $this->decode_fzg2_peel_body($contents, $offset, $n);
	}
	if($magic === 'FZG3') {
		return $this->decode_fzg3_peel_body($contents, $offset, $n);
	}
	if($magic === 'FZG4') {
		return $this->decode_fzg4_peel_body($contents, $offset, $n);
	}
	fractal_zip::fatal_error('Corrupt FZC: unknown peel trailer magic.');
	return $map;
}

function decode_fzg1_peel_body($contents, &$offset, $n) {
	$map = array();
	$count = $this->decode_varint_u32($contents, $offset, $n, 'FZG1');
	for($i = 0; $i < $count; $i++) {
		$pathLen = $this->decode_varint_u32($contents, $offset, $n, 'FZG1');
		if($offset + $pathLen > $n) {
			fractal_zip::fatal_error('Corrupt FZG1 (path out of bounds).');
		}
		$path = substr($contents, $offset, $pathLen);
		$offset += $pathLen;
		$blobLen = $this->decode_varint_u32($contents, $offset, $n, 'FZG1');
		if($offset + $blobLen > $n) {
			fractal_zip::fatal_error('Corrupt FZG1 (blob out of bounds).');
		}
		$map[$path] = substr($contents, $offset, $blobLen);
		$offset += $blobLen;
	}
	if($offset !== $n) {
		fractal_zip::fatal_error('Corrupt FZG1 (trailing garbage).');
	}
	return $map;
}

function decode_fzg2_peel_body($contents, &$offset, $n) {
	$map = array();
	$u = $this->decode_varint_u32($contents, $offset, $n, 'FZG2');
	$blobs = array();
	for($i = 0; $i < $u; $i++) {
		$bl = $this->decode_varint_u32($contents, $offset, $n, 'FZG2');
		if($offset + $bl > $n) {
			fractal_zip::fatal_error('Corrupt FZG2 (blob out of bounds).');
		}
		$blobs[] = substr($contents, $offset, $bl);
		$offset += $bl;
	}
	$count = $this->decode_varint_u32($contents, $offset, $n, 'FZG2');
	for($j = 0; $j < $count; $j++) {
		$pathLen = $this->decode_varint_u32($contents, $offset, $n, 'FZG2');
		if($offset + $pathLen > $n) {
			fractal_zip::fatal_error('Corrupt FZG2 (path out of bounds).');
		}
		$path = substr($contents, $offset, $pathLen);
		$offset += $pathLen;
		$bi = $this->decode_varint_u32($contents, $offset, $n, 'FZG2');
		if($bi >= sizeof($blobs)) {
			fractal_zip::fatal_error('Corrupt FZG2 (blob index).');
		}
		$map[$path] = $blobs[$bi];
	}
	if($offset !== $n) {
		fractal_zip::fatal_error('Corrupt FZG2 (trailing garbage).');
	}
	return $map;
}

function decode_fzg3_peel_body($contents, &$offset, $n) {
	$map = array();
	$tlen = $this->decode_varint_u32($contents, $offset, $n, 'FZG3');
	if($tlen !== 8 || $offset + 8 > $n) {
		fractal_zip::fatal_error('Corrupt FZG3 (trailer).');
	}
	$trailer = substr($contents, $offset, 8);
	$offset += 8;
	$count = $this->decode_varint_u32($contents, $offset, $n, 'FZG3');
	for($i = 0; $i < $count; $i++) {
		$pathLen = $this->decode_varint_u32($contents, $offset, $n, 'FZG3');
		if($offset + $pathLen > $n) {
			fractal_zip::fatal_error('Corrupt FZG3 (path out of bounds).');
		}
		$path = substr($contents, $offset, $pathLen);
		$offset += $pathLen;
		$hLen = $this->decode_varint_u32($contents, $offset, $n, 'FZG3');
		if($offset + $hLen > $n) {
			fractal_zip::fatal_error('Corrupt FZG3 (header out of bounds).');
		}
		$h = substr($contents, $offset, $hLen);
		$offset += $hLen;
		$dLen = $this->decode_varint_u32($contents, $offset, $n, 'FZG3');
		if($offset + $dLen > $n) {
			fractal_zip::fatal_error('Corrupt FZG3 (deflate out of bounds).');
		}
		$d = substr($contents, $offset, $dLen);
		$offset += $dLen;
		$map[$path] = $h . $d . $trailer;
	}
	if($offset !== $n) {
		fractal_zip::fatal_error('Corrupt FZG3 (trailing garbage).');
	}
	return $map;
}

function decode_fzg4_peel_body($contents, &$offset, $n) {
	$map = array();
	$tlen = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
	if($tlen !== 8 || $offset + 8 > $n) {
		fractal_zip::fatal_error('Corrupt FZG4 (trailer).');
	}
	$trailer = substr($contents, $offset, 8);
	$offset += 8;
	$hCount = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
	$hdrList = array();
	for($i = 0; $i < $hCount; $i++) {
		$hl = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
		if($offset + $hl > $n) {
			fractal_zip::fatal_error('Corrupt FZG4 (header table).');
		}
		$hdrList[] = substr($contents, $offset, $hl);
		$offset += $hl;
	}
	$dCount = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
	$defList = array();
	for($j = 0; $j < $dCount; $j++) {
		$dl = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
		if($offset + $dl > $n) {
			fractal_zip::fatal_error('Corrupt FZG4 (deflate table).');
		}
		$defList[] = substr($contents, $offset, $dl);
		$offset += $dl;
	}
	$pCount = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
	for($k = 0; $k < $pCount; $k++) {
		$pathLen = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
		if($offset + $pathLen > $n) {
			fractal_zip::fatal_error('Corrupt FZG4 (path out of bounds).');
		}
		$path = substr($contents, $offset, $pathLen);
		$offset += $pathLen;
		$hi = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
		$di = $this->decode_varint_u32($contents, $offset, $n, 'FZG4');
		if($hi >= sizeof($hdrList) || $di >= sizeof($defList)) {
			fractal_zip::fatal_error('Corrupt FZG4 (index).');
		}
		$map[$path] = $hdrList[$hi] . $defList[$di] . $trailer;
	}
	if($offset !== $n) {
		fractal_zip::fatal_error('Corrupt FZG4 (trailing garbage).');
	}
	return $map;
}

/**
 * Single flat basename+fixed-suffix member: magic (4) + u8 nameLen + name + varint zippedLen + zipped + varint fractalLen + fractal + optional FZG* peel trailer.
 *
 * @param string $errTag decode_varint_u32 / fatal labels (e.g. FZCC)
 */
function decode_fzc_single_flat_suffix_payload($contents, $errTag, $pathSuffix) {
	$offset = 4;
	$n = strlen($contents);
	if($offset + 1 > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (name length missing).');
	}
	$nameLen = ord($contents[$offset]);
	$offset += 1;
	if($nameLen < 1 || $offset + $nameLen > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (name bytes out of bounds).');
	}
	$namePart = substr($contents, $offset, $nameLen);
	$offset += $nameLen;
	$valLen = $this->decode_varint_u32($contents, $offset, $n, $errTag);
	if($offset + $valLen > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (value bytes out of bounds).');
	}
	$zipped = substr($contents, $offset, $valLen);
	$offset += $valLen;
	$frLen = $this->decode_varint_u32($contents, $offset, $n, $errTag);
	if($offset + $frLen > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (fractal bytes out of bounds).');
	}
	$fractal = substr($contents, $offset, $frLen);
	$offset += $frLen;
	$files = array($namePart . $pathSuffix => $zipped);
	$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
	return array($files, $fractal, $peel);
}

/**
 * FZCX: magic (4) + u8 nameLen + name + u8 extLen + ext + varint zippedLen + zipped + varint fractalLen + fractal + optional FZG* peel trailer.
 */
function decode_fzc_single_flat_u8_ext_after_name_payload($contents, $errTag) {
	$offset = 4;
	$n = strlen($contents);
	if($offset + 1 > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (name length missing).');
	}
	$nameLen = ord($contents[$offset]);
	$offset += 1;
	if($nameLen < 1 || $offset + $nameLen > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (name bytes out of bounds).');
	}
	$namePart = substr($contents, $offset, $nameLen);
	$offset += $nameLen;
	if($offset + 1 > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (ext length missing).');
	}
	$extLen = ord($contents[$offset]);
	$offset += 1;
	if($extLen < 2 || $extLen > 32 || $offset + $extLen > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (ext bytes out of bounds).');
	}
	$ext = substr($contents, $offset, $extLen);
	$offset += $extLen;
	if(!isset($ext[0]) || $ext[0] !== '.') {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (ext must start with dot).');
	}
	$valLen = $this->decode_varint_u32($contents, $offset, $n, $errTag);
	if($offset + $valLen > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (value bytes out of bounds).');
	}
	$zipped = substr($contents, $offset, $valLen);
	$offset += $valLen;
	$frLen = $this->decode_varint_u32($contents, $offset, $n, $errTag);
	if($offset + $frLen > $n) {
		fractal_zip::fatal_error('Corrupt ' . $errTag . ' payload (fractal bytes out of bounds).');
	}
	$fractal = substr($contents, $offset, $frLen);
	$offset += $frLen;
	$files = array($namePart . $ext => $zipped);
	$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
	return array($files, $fractal, $peel);
}

function decode_container_payload($contents) {
	$contents = (string) $contents;
	$fzwsSteps = 0;
	while(strlen($contents) >= 10 && substr($contents, 0, 4) === 'FZWS') {
		$fzwsSteps++;
		if($fzwsSteps > 8) {
			fractal_zip::fatal_error('Corrupt container (too many FZWS layers).');
		}
		$contents = $this->decode_fzws_v1_layer($contents);
	}
	$this->fzb_raster_canon_decode_table = array();
	if(is_string($contents) && strlen($contents) >= 8 && substr($contents, 0, 4) === 'FZTA' && ord($contents[4]) === 1) {
		$off = 5;
		$n = strlen($contents);
		$tl = $this->decode_varint_u32($contents, $off, $n, 'FZTA');
		if($tl < 0 || $off + $tl > $n) {
			fractal_zip::fatal_error('Corrupt FZTA payload (tar segment length).');
		}
		$tarSeg = substr($contents, $off, $tl);
		$ud = fractal_zip::posix_ustar_decode_first_regular_file($tarSeg);
		if($ud === null) {
			fractal_zip::fatal_error('Corrupt FZTA payload (ustar parse).');
		}
		list($pn, $rb) = $ud;

		return array(array((string) $pn => $this->escape_literal_for_storage((string) $rb)), '', array());
	}
	if(is_string($contents) && strlen($contents) >= 5 && substr($contents, 0, 4) === 'FZS1') {
		$off = 4;
		$n = strlen($contents);
		$pathLen = $this->decode_varint_u32($contents, $off, $n, 'FZS1');
		if($pathLen < 0 || $off + $pathLen > $n) {
			fractal_zip::fatal_error('Corrupt FZS1 payload (path length out of bounds).');
		}
		$path = substr($contents, $off, $pathLen);
		$off += $pathLen;
		$raw = substr($contents, $off);
		return array(array($path => $this->escape_literal_for_storage($raw)), '', array());
	}
	if(is_string($contents) && strlen($contents) >= 5 && substr($contents, 0, 4) === 'FZBD') {
		if(ord($contents[4]) !== 1) {
			fractal_zip::fatal_error('Corrupt FZBD (unsupported version).');
		}
		$off = 5;
		$nFzbd = strlen($contents);
		$nt = $this->decode_varint_u32($contents, $off, $nFzbd, 'FZBD');
		if($nt < 0 || $nt > 65536) {
			fractal_zip::fatal_error('Corrupt FZBD (table count).');
		}
		$tab = array();
		for($ti = 0; $ti < $nt; $ti++) {
			$bl = $this->decode_varint_u32($contents, $off, $nFzbd, 'FZBD');
			if($bl < 0 || $off + $bl > $nFzbd) {
				fractal_zip::fatal_error('Corrupt FZBD (blob length).');
			}
			$tab[] = substr($contents, $off, $bl);
			$off += $bl;
		}
		$this->fzb_raster_canon_decode_table = $tab;
		$contents = substr($contents, $off);
	}
	if(is_string($contents) && strlen($contents) >= 6 && substr($contents, 0, 4) === 'FZCD') {
		return $this->decode_fzcd_payload_from_string($contents);
	}
	if(is_string($contents) && strlen($contents) >= 7 && substr($contents, 0, 4) === 'FZBM') {
		return $this->decode_fzbm_v1_payload($contents);
	}
	if(is_string($contents) && strlen($contents) >= 8 && substr($contents, 0, 4) === 'FZBF') {
		return $this->decode_fzbf_v1_payload($contents);
	}
	if(is_string($contents) && strlen($contents) >= 8 && substr($contents, 0, 4) === 'FZB6') {
		$offset = 4;
		$n = strlen($contents);
		$count = $this->decode_fzb56_bundle_member_count($contents, $offset, $n, 'FZB6');
		if($offset + 1 > $n) {
			fractal_zip::fatal_error('Corrupt FZB6 payload (ext length missing).');
		}
		$extLen = ord($contents[$offset]);
		$offset += 1;
		if($offset + $extLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB6 payload (ext bytes out of bounds).');
		}
		$ext = substr($contents, $offset, $extLen);
		$offset += $extLen;
		if($offset + 1 > $n) {
			fractal_zip::fatal_error('Corrupt FZB6 payload (prefix length missing).');
		}
		$prefixLen = ord($contents[$offset]);
		$offset += 1;
		if($offset + $prefixLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB6 payload (prefix bytes out of bounds).');
		}
		$prefix = substr($contents, $offset, $prefixLen);
		$offset += $prefixLen;
		if($offset + 1 > $n) {
			fractal_zip::fatal_error('Corrupt FZB6 payload (digits length missing).');
		}
		$digitsLen = ord($contents[$offset]);
		$offset += 1;
		if($digitsLen < 1 || $digitsLen > 9) {
			fractal_zip::fatal_error('Corrupt FZB6 payload (invalid digits length).');
		}
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 1 > $n) {
				fractal_zip::fatal_error('Corrupt FZB6 payload (mode out of bounds).');
			}
			$mode = ord($contents[$offset]);
			$offset += 1;
			$num = $this->decode_varint_u32($contents, $offset, $n, 'FZB6');
			$name = $prefix . str_pad((string)$num, $digitsLen, '0', STR_PAD_LEFT);
			$dataLen = $this->decode_varint_u32($contents, $offset, $n, 'FZB6');
			if($offset + $dataLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB6 payload (data bytes out of bounds).');
			}
			$rawStored = substr($contents, $offset, $dataLen);
			$offset += $dataLen;
			$raw = $this->decode_bundle_literal_member($mode, $rawStored);
			$files[$name . $ext] = $this->escape_literal_for_storage($raw);
		}
		if($offset !== $n) {
			fractal_zip::fatal_error('Corrupt FZB6 payload (trailing bytes).');
		}
		return array($files, '', array());
	}
	if(is_string($contents) && strlen($contents) >= 6 && substr($contents, 0, 4) === 'FZB5') {
		$offset = 4;
		$n = strlen($contents);
		$count = $this->decode_fzb56_bundle_member_count($contents, $offset, $n, 'FZB5');
		if($offset + 1 > $n) {
			fractal_zip::fatal_error('Corrupt FZB5 payload (ext length missing).');
		}
		$extLen = ord($contents[$offset]);
		$offset += 1;
		if($offset + $extLen > $n) {
			fractal_zip::fatal_error('Corrupt FZB5 payload (ext bytes out of bounds).');
		}
		$ext = substr($contents, $offset, $extLen);
		$offset += $extLen;
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 1 > $n) {
				fractal_zip::fatal_error('Corrupt FZB5 payload (name length out of bounds).');
			}
			$nameLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $nameLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB5 payload (name bytes out of bounds).');
			}
			$name = substr($contents, $offset, $nameLen);
			$offset += $nameLen;
			if($offset + 1 > $n) {
				fractal_zip::fatal_error('Corrupt FZB5 payload (mode out of bounds).');
			}
			$mode = ord($contents[$offset]);
			$offset += 1;
			$dataLen = $this->decode_varint_u32($contents, $offset, $n, 'FZB5');
			if($offset + $dataLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB5 payload (data bytes out of bounds).');
			}
			$rawStored = substr($contents, $offset, $dataLen);
			$offset += $dataLen;
			$raw = $this->decode_bundle_literal_member($mode, $rawStored);
			$files[$name . $ext] = $this->escape_literal_for_storage($raw);
		}
		if($offset !== $n) {
			fractal_zip::fatal_error('Corrupt FZB5 payload (trailing bytes).');
		}
		return array($files, '', array());
	}
	if(is_string($contents) && strlen($contents) >= 4 && substr($contents, 0, 4) === 'FZB4') {
		$offset = 4;
		$files = array();
		$n = strlen($contents);
		$prevPath = '';
		while($offset < $n) {
			$prefixLen = $this->decode_varint_u32($contents, $offset, $n, 'FZB4');
			$suffixLen = $this->decode_varint_u32($contents, $offset, $n, 'FZB4');
			if($prefixLen > strlen($prevPath)) {
				fractal_zip::fatal_error('Corrupt FZB4 payload (prefix length exceeds previous path).');
			}
			if($offset + $suffixLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB4 payload (path suffix bytes out of bounds).');
			}
			$pathSuffix = substr($contents, $offset, $suffixLen);
			$offset += $suffixLen;
			$path = substr($prevPath, 0, $prefixLen) . $pathSuffix;
			if($offset + 1 > $n) {
				fractal_zip::fatal_error('Corrupt FZB4 payload (mode out of bounds).');
			}
			$mode = ord($contents[$offset]);
			$offset += 1;
			$dataLen = $this->decode_varint_u32($contents, $offset, $n, 'FZB4');
			if($offset + $dataLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB4 payload (data bytes out of bounds).');
			}
			$rawStored = substr($contents, $offset, $dataLen);
			$offset += $dataLen;
			$raw = $this->decode_bundle_literal_member($mode, $rawStored);
			$files[$path] = $this->escape_literal_for_storage($raw);
			$prevPath = $path;
		}
		return array($files, '', array());
	}
	if(is_string($contents) && strlen($contents) >= 4 && substr($contents, 0, 4) === 'FZB3') {
		$offset = 4;
		$files = array();
		$n = strlen($contents);
		$prevPath = '';
		while($offset < $n) {
			if($offset + 4 > $n) {
				fractal_zip::fatal_error('Corrupt FZB3 payload (path prefix/suffix length out of bounds).');
			}
			$prefixLenA = unpack('nlen', substr($contents, $offset, 2));
			$offset += 2;
			$suffixLenA = unpack('nlen', substr($contents, $offset, 2));
			$offset += 2;
			$prefixLen = (int)$prefixLenA['len'];
			$suffixLen = (int)$suffixLenA['len'];
			if($prefixLen > strlen($prevPath)) {
				fractal_zip::fatal_error('Corrupt FZB3 payload (prefix length exceeds previous path).');
			}
			if($offset + $suffixLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB3 payload (path suffix bytes out of bounds).');
			}
			$pathSuffix = substr($contents, $offset, $suffixLen);
			$offset += $suffixLen;
			$path = substr($prevPath, 0, $prefixLen) . $pathSuffix;
			if($offset + 1 > $n) {
				fractal_zip::fatal_error('Corrupt FZB3 payload (mode out of bounds).');
			}
			$mode = ord($contents[$offset]);
			$offset += 1;
			if($offset + 4 > $n) {
				fractal_zip::fatal_error('Corrupt FZB3 payload (data length out of bounds).');
			}
			$dataLen = unpack('Nlen', substr($contents, $offset, 4));
			$offset += 4;
			$dataLen = (int)$dataLen['len'];
			if($offset + $dataLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB3 payload (data bytes out of bounds).');
			}
			$rawStored = substr($contents, $offset, $dataLen);
			$offset += $dataLen;
			$raw = $this->decode_bundle_literal_member($mode, $rawStored);
			$files[$path] = $this->escape_literal_for_storage($raw);
			$prevPath = $path;
		}
		return array($files, '', array());
	}
	if(is_string($contents) && strlen($contents) >= 4 && substr($contents, 0, 4) === 'FZB2') {
		$offset = 4;
		$files = array();
		$n = strlen($contents);
		while($offset < $n) {
			if($offset + 2 > $n) {
				fractal_zip::fatal_error('Corrupt FZB2 payload (path length out of bounds).');
			}
			$pathLen = unpack('nlen', substr($contents, $offset, 2));
			$offset += 2;
			$pathLen = (int)$pathLen['len'];
			if($offset + $pathLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB2 payload (path bytes out of bounds).');
			}
			$path = substr($contents, $offset, $pathLen);
			$offset += $pathLen;
			if($offset + 1 > $n) {
				fractal_zip::fatal_error('Corrupt FZB2 payload (mode out of bounds).');
			}
			$mode = ord($contents[$offset]);
			$offset += 1;
			if($offset + 4 > $n) {
				fractal_zip::fatal_error('Corrupt FZB2 payload (data length out of bounds).');
			}
			$dataLen = unpack('Nlen', substr($contents, $offset, 4));
			$offset += 4;
			$dataLen = (int)$dataLen['len'];
			if($offset + $dataLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB2 payload (data bytes out of bounds).');
			}
			$rawStored = substr($contents, $offset, $dataLen);
			$offset += $dataLen;
			$raw = $this->decode_bundle_literal_member($mode, $rawStored);
			$files[$path] = $this->escape_literal_for_storage($raw);
		}
		return array($files, '', array());
	}
	if(is_string($contents) && strlen($contents) >= 4 && substr($contents, 0, 4) === 'FZB1') {
		$offset = 4;
		$files = array();
		$n = strlen($contents);
		while($offset < $n) {
			if($offset + 2 > $n) {
				fractal_zip::fatal_error('Corrupt FZB1 payload (path length out of bounds).');
			}
			$pathLen = unpack('nlen', substr($contents, $offset, 2));
			$offset += 2;
			$pathLen = (int)$pathLen['len'];
			if($offset + $pathLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB1 payload (path bytes out of bounds).');
			}
			$path = substr($contents, $offset, $pathLen);
			$offset += $pathLen;
			if($offset + 4 > $n) {
				fractal_zip::fatal_error('Corrupt FZB1 payload (data length out of bounds).');
			}
			$dataLen = unpack('Nlen', substr($contents, $offset, 4));
			$offset += 4;
			$dataLen = (int)$dataLen['len'];
			if($offset + $dataLen > $n) {
				fractal_zip::fatal_error('Corrupt FZB1 payload (data bytes out of bounds).');
			}
			$raw = substr($contents, $offset, $dataLen);
			$offset += $dataLen;
			$files[$path] = $this->escape_literal_for_storage($raw);
		}
		return array($files, '', array());
	}
	if(is_string($contents) && strlen($contents) >= 6 && substr($contents, 0, 4) === 'FZCT') {
		$offset = 4;
		$n = strlen($contents);
		if($offset + 1 > $n) {
			fractal_zip::fatal_error('Corrupt FZCT payload (name length missing).');
		}
		$nameLen = ord($contents[$offset]);
		$offset += 1;
		if($nameLen < 1 || $offset + $nameLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCT payload (name bytes out of bounds).');
		}
		$namePart = substr($contents, $offset, $nameLen);
		$offset += $nameLen;
		$valLenT = $this->decode_varint_u32($contents, $offset, $n, 'FZCT');
		if($offset + $valLenT > $n) {
			fractal_zip::fatal_error('Corrupt FZCT payload (value bytes out of bounds).');
		}
		$zippedT = substr($contents, $offset, $valLenT);
		$offset += $valLenT;
		$frLenT = $this->decode_varint_u32($contents, $offset, $n, 'FZCT');
		if($offset + $frLenT > $n) {
			fractal_zip::fatal_error('Corrupt FZCT payload (fractal bytes out of bounds).');
		}
		$fractalT = substr($contents, $offset, $frLenT);
		$offset += $frLenT;
		$filesT = array($namePart . '.txt' => $zippedT);
		$peelT = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($filesT, $fractalT, $peelT);
	}
	if(is_string($contents) && strlen($contents) >= 7 && substr($contents, 0, 4) === 'FZCH') {
		$offset = 4;
		$n = strlen($contents);
		if($offset + 2 > $n) {
			fractal_zip::fatal_error('Corrupt FZCH payload (ext tag or name length missing).');
		}
		$extTag = ord($contents[$offset]);
		$offset += 1;
		if($extTag > 1) {
			fractal_zip::fatal_error('Corrupt FZCH payload (unknown ext tag).');
		}
		$nameLen = ord($contents[$offset]);
		$offset += 1;
		if($nameLen < 1 || $offset + $nameLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCH payload (name bytes out of bounds).');
		}
		$namePart = substr($contents, $offset, $nameLen);
		$offset += $nameLen;
		$valLenH = $this->decode_varint_u32($contents, $offset, $n, 'FZCH');
		if($offset + $valLenH > $n) {
			fractal_zip::fatal_error('Corrupt FZCH payload (value bytes out of bounds).');
		}
		$zippedH = substr($contents, $offset, $valLenH);
		$offset += $valLenH;
		$frLenH = $this->decode_varint_u32($contents, $offset, $n, 'FZCH');
		if($offset + $frLenH > $n) {
			fractal_zip::fatal_error('Corrupt FZCH payload (fractal bytes out of bounds).');
		}
		$fractalH = substr($contents, $offset, $frLenH);
		$offset += $frLenH;
		$sufH = ($extTag === 1) ? '.htm' : '.html';
		$filesH = array($namePart . $sufH => $zippedH);
		$peelH = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($filesH, $fractalH, $peelH);
	}
	if(is_string($contents) && strlen($contents) >= 6) {
		static $fzFlatSingleDecodeMap = null;
		if($fzFlatSingleDecodeMap === null) {
			$fzFlatSingleDecodeMap = array(
				'FZCC' => '.c',
				'FZCL' => '.lsp',
				'FZCG' => '.protodata',
				'FZCP' => '.pdf',
				'FZCK' => '.xls',
				'FZCU' => '.10K',
				'FZCM' => '.1',
				'FZCE' => '',
				'FZCB' => '.js',
				'FZCN' => '.json',
				'FZCQ' => '.xml',
				'FZCS' => '.css',
				'FZCI' => '.png',
				'FZCR' => '.gif',
				'FZCV' => '.webp',
				'FZCY' => '.svg',
				'FZCW' => '.md',
				'FZCA' => '.php',
				'FZCF' => '.ts',
				'FZCZ' => '.mjs',
				'FZCO' => '.ico',
				'FZC7' => '.vue',
				'FZC8' => '.tsx',
				'FZC9' => '.jsx',
				'FZC0' => '.py',
				'FZB7' => '.ttf',
				'FZB8' => '.otf',
				'FZB9' => '.eot',
				'FZB0' => '.map',
				'FZBL' => '.lock',
				'FZTO' => '.toml',
				'FZYM' => '.yml',
				'FZRS' => '.rs',
				'FZGO' => '.go',
				'FZRB' => '.rb',
				'FZZP' => '.zip',
				'FZTR' => '.tar',
				'FZGZ' => '.gz',
				'FZ7Z' => '.7z',
				'FZBZ' => '.bz2',
				'FZL4' => '.lz4',
				'FZZS' => '.zst',
				'FZBR' => '.br',
				'FZXZ' => '.xz',
				'FZQL' => '.sql',
				'FZSH' => '.sh',
				'FZBT' => '.bat',
				'FZ1P' => '.ps1',
				'FZJA' => '.java',
				'FZJR' => '.jar',
				'FZKT' => '.kt',
				'FZKS' => '.kts',
				'FZHI' => '.h',
				'FZHP' => '.hpp',
				'FZ2C' => '.cc',
				'FZPP' => '.cpp',
				'FZNI' => '.ini',
				'FZFG' => '.cfg',
				'FZSV' => '.csv',
				'FZTV' => '.tsv',
				'FZDF' => '.diff',
				'FZLG' => '.log',
				'FZDT' => '.dart',
				'FZRL' => '.r',
				'FZPL' => '.pl',
				'FZPM' => '.pm',
				'FZSC' => '.cs',
				'FZFS' => '.fs',
				'FZMO' => '.m',
				'FZMM' => '.mm',
				'FZJL' => '.jl',
				'FZSL' => '.scala',
				'FZGL' => '.gradle',
				'FZY2' => '.yaml',
				'FZVT' => '.svelte',
				'FZA4' => '.astro',
				'FZMX' => '.mdx',
				'FZGQ' => '.gql',
				'FZLE' => '.less',
				'FZSA' => '.sass',
				'FZS2' => '.scss',
				'FZST' => '.styl',
				'FZTF' => '.tf',
				'FZF2' => '.tfvars',
				'FZ3B' => '.vbs',
				'FZ3L' => '.lua',
				'FZ3P' => '.pug',
				'FZ3E' => '.ejs',
				'FZ3H' => '.hbs',
				'FZ3M' => '.nim',
				'FZ3G' => '.zig',
				'FZ3X' => '.xaml',
				'FZ3C' => '.clj',
				'FZ2J' => '.cljs',
				'FZ8P' => '.properties',
				'FZ3K' => '.cmake',
				'FZ0C' => '.cjs',
				'FZ2T' => '.cts',
				'FZ2M' => '.mts',
				'FZ2E' => '.ex',
				'FZ2S' => '.exs',
				'FZ2L' => '.elm',
				'FZ2B' => '.sbt',
				'FZR1' => '.rst',
				'FZ2A' => '.adoc',
				'FZ2O' => '.org',
				'FZR0' => '.erb',
				'FZ2H' => '.haml',
				'FZ2I' => '.slim',
				'FZ2G' => '.groovy',
				'FZ2Z' => '.bzl',
				'FZ0B' => '.bazel',
				'FZG0' => '.graphql',
				'FZSW' => '.swift',
				'FZP0' => '.pod',
				'FZ2V' => '.vb',
			);
		}
		$hFlat = substr($contents, 0, 4);
		if(isset($fzFlatSingleDecodeMap[$hFlat])) {
			return $this->decode_fzc_single_flat_suffix_payload($contents, $hFlat, $fzFlatSingleDecodeMap[$hFlat]);
		}
	}
	if(is_string($contents) && strlen($contents) >= 7 && substr($contents, 0, 4) === 'FZCJ') {
		$offset = 4;
		$n = strlen($contents);
		if($offset + 2 > $n) {
			fractal_zip::fatal_error('Corrupt FZCJ payload (ext tag or name length missing).');
		}
		$extTag = ord($contents[$offset]);
		$offset += 1;
		if($extTag > 1) {
			fractal_zip::fatal_error('Corrupt FZCJ payload (unknown ext tag).');
		}
		$nameLen = ord($contents[$offset]);
		$offset += 1;
		if($nameLen < 1 || $offset + $nameLen > $n) {
			fractal_zip::fatal_error('Corrupt FZCJ payload (name bytes out of bounds).');
		}
		$namePart = substr($contents, $offset, $nameLen);
		$offset += $nameLen;
		$valLenJ = $this->decode_varint_u32($contents, $offset, $n, 'FZCJ');
		if($offset + $valLenJ > $n) {
			fractal_zip::fatal_error('Corrupt FZCJ payload (value bytes out of bounds).');
		}
		$zippedJ = substr($contents, $offset, $valLenJ);
		$offset += $valLenJ;
		$frLenJ = $this->decode_varint_u32($contents, $offset, $n, 'FZCJ');
		if($offset + $frLenJ > $n) {
			fractal_zip::fatal_error('Corrupt FZCJ payload (fractal bytes out of bounds).');
		}
		$fractalJ = substr($contents, $offset, $frLenJ);
		$offset += $frLenJ;
		$sufJ = ($extTag === 1) ? '.jpeg' : '.jpg';
		$filesJ = array($namePart . $sufJ => $zippedJ);
		$peelJ = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($filesJ, $fractalJ, $peelJ);
	}
	if(is_string($contents) && strlen($contents) >= 10 && substr($contents, 0, 4) === 'FZCX') {
		return $this->decode_fzc_single_flat_u8_ext_after_name_payload($contents, 'FZCX');
	}
	if(is_string($contents) && strlen($contents) >= 6 && substr($contents, 0, 4) === 'FZC6') {
		$offset = 4;
		$n = strlen($contents);
		$count = ord($contents[$offset]);
		$offset += 1;
		if($offset + 1 > $n) {
			fractal_zip::fatal_error('Corrupt FZC6 payload (ext length missing).');
		}
		$extLen = ord($contents[$offset]);
		$offset += 1;
		if($offset + $extLen > $n) {
			fractal_zip::fatal_error('Corrupt FZC6 payload (ext bytes out of bounds).');
		}
		$ext = substr($contents, $offset, $extLen);
		$offset += $extLen;
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 1 > $n) {
				fractal_zip::fatal_error('Corrupt FZC6 payload (name length out of bounds).');
			}
			$nameLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $nameLen > $n) {
				fractal_zip::fatal_error('Corrupt FZC6 payload (name bytes out of bounds).');
			}
			$name = substr($contents, $offset, $nameLen);
			$offset += $nameLen;
			$valLen = $this->decode_varint_u32($contents, $offset, $n, 'FZC6');
			if($offset + $valLen > $n) {
				fractal_zip::fatal_error('Corrupt FZC6 payload (value bytes out of bounds).');
			}
			$zipped = substr($contents, $offset, $valLen);
			$offset += $valLen;
			$files[$name . $ext] = $zipped;
		}
		$frLen = $this->decode_varint_u32($contents, $offset, $n, 'FZC6');
		if($offset + $frLen > $n) {
			fractal_zip::fatal_error('Corrupt FZC6 payload (fractal bytes out of bounds).');
		}
		$fractal_string = substr($contents, $offset, $frLen);
		$offset += $frLen;
		$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($files, $fractal_string, $peel);
	}
	if(is_string($contents) && strlen($contents) >= 6 && substr($contents, 0, 4) === 'FZC5') {
		$n = strlen($contents);
		$offset = 4;
		$count = ord($contents[$offset]);
		$offset += 1;
		if($offset + 1 > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC5 payload (ext length missing).');
		}
		$extLen = ord($contents[$offset]);
		$offset += 1;
		if($offset + $extLen > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC5 payload (ext bytes out of bounds).');
		}
		$ext = substr($contents, $offset, $extLen);
		$offset += $extLen;
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 1 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC5 payload (name length out of bounds).');
			}
			$nameLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $nameLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC5 payload (name bytes out of bounds).');
			}
			$name = substr($contents, $offset, $nameLen);
			$offset += $nameLen;
			if($offset + 1 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC5 payload (value length out of bounds).');
			}
			$valLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $valLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC5 payload (value bytes out of bounds).');
			}
			$zipped = substr($contents, $offset, $valLen);
			$offset += $valLen;
			$files[$name . $ext] = $zipped;
		}
		if($offset + 1 > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC5 payload (fractal length missing).');
		}
		$frLen = ord($contents[$offset]);
		$offset += 1;
		if($offset + $frLen > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC5 payload (fractal bytes out of bounds).');
		}
		$fractal_string = substr($contents, $offset, $frLen);
		$offset += $frLen;
		$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($files, $fractal_string, $peel);
	}
	if(is_string($contents) && strlen($contents) >= 5 && substr($contents, 0, 4) === 'FZC4') {
		$n = strlen($contents);
		$offset = 4;
		$count = ord($contents[$offset]);
		$offset += 1;
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 1 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC4 payload (path length out of bounds).');
			}
			$pathLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $pathLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC4 payload (path bytes out of bounds).');
			}
			$path = substr($contents, $offset, $pathLen);
			$offset += $pathLen;
			if($offset + 1 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC4 payload (value length out of bounds).');
			}
			$valLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $valLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC4 payload (value bytes out of bounds).');
			}
			$zipped = substr($contents, $offset, $valLen);
			$offset += $valLen;
			$files[$path] = $zipped;
		}
		if($offset + 1 > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC4 payload (fractal length missing).');
		}
		$frLen = ord($contents[$offset]);
		$offset += 1;
		if($offset + $frLen > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC4 payload (fractal bytes out of bounds).');
		}
		$fractal_string = substr($contents, $offset, $frLen);
		$offset += $frLen;
		$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($files, $fractal_string, $peel);
	}
	if(is_string($contents) && strlen($contents) >= 6 && substr($contents, 0, 4) === 'FZC3') {
		$n = strlen($contents);
		$offset = 4;
		$unpack = unpack('ncount', substr($contents, $offset, 2));
		$offset += 2;
		$count = (int)$unpack['count'];
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 1 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC3 payload (path length out of bounds).');
			}
			$pathLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $pathLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC3 payload (path bytes out of bounds).');
			}
			$path = substr($contents, $offset, $pathLen);
			$offset += $pathLen;
			if($offset + 1 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC3 payload (value length out of bounds).');
			}
			$valLen = ord($contents[$offset]);
			$offset += 1;
			if($offset + $valLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC3 payload (value bytes out of bounds).');
			}
			$zipped = substr($contents, $offset, $valLen);
			$offset += $valLen;
			$files[$path] = $zipped;
		}
		if($offset + 4 > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC3 payload (fractal length missing).');
		}
		$frLen = unpack('Nlen', substr($contents, $offset, 4));
		$offset += 4;
		$frLen = (int)$frLen['len'];
		if($offset + $frLen > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC3 payload (fractal bytes out of bounds).');
		}
		$fractal_string = substr($contents, $offset, $frLen);
		$offset += $frLen;
		$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($files, $fractal_string, $peel);
	}
	if(is_string($contents) && strlen($contents) >= 8 && substr($contents, 0, 4) === 'FZC2') {
		$n = strlen($contents);
		$offset = 4;
		$unpack = unpack('Ncount', substr($contents, $offset, 4));
		$offset += 4;
		$count = (int)$unpack['count'];
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 2 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC2 payload (path length out of bounds).');
			}
			$pathLen = unpack('nlen', substr($contents, $offset, 2));
			$offset += 2;
			$pathLen = (int)$pathLen['len'];
			if($offset + $pathLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC2 payload (path bytes out of bounds).');
			}
			$path = substr($contents, $offset, $pathLen);
			$offset += $pathLen;
			if($offset + 2 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC2 payload (value length out of bounds).');
			}
			$valLen = unpack('nlen', substr($contents, $offset, 2));
			$offset += 2;
			$valLen = (int)$valLen['len'];
			if($offset + $valLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC2 payload (value bytes out of bounds).');
			}
			$zipped = substr($contents, $offset, $valLen);
			$offset += $valLen;
			$files[$path] = $zipped;
		}
		if($offset + 4 > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC2 payload (fractal length missing).');
		}
		$frLen = unpack('Nlen', substr($contents, $offset, 4));
		$offset += 4;
		$frLen = (int)$frLen['len'];
		if($offset + $frLen > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC2 payload (fractal bytes out of bounds).');
		}
		$fractal_string = substr($contents, $offset, $frLen);
		$offset += $frLen;
		$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($files, $fractal_string, $peel);
	}
	if(is_string($contents) && strlen($contents) >= 8 && substr($contents, 0, 4) === 'FZC1') {
		$n = strlen($contents);
		$offset = 4;
		$unpack = unpack('Ncount', substr($contents, $offset, 4));
		$offset += 4;
		$count = (int)$unpack['count'];
		$files = array();
		for($i = 0; $i < $count; $i++) {
			if($offset + 4 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC1 payload (path length out of bounds).');
			}
			$pathLen = unpack('Nlen', substr($contents, $offset, 4));
			$offset += 4;
			$pathLen = (int)$pathLen['len'];
			if($offset + $pathLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC1 payload (path bytes out of bounds).');
			}
			$path = substr($contents, $offset, $pathLen);
			$offset += $pathLen;
			if($offset + 4 > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC1 payload (value length out of bounds).');
			}
			$valLen = unpack('Nlen', substr($contents, $offset, 4));
			$offset += 4;
			$valLen = (int)$valLen['len'];
			if($offset + $valLen > strlen($contents)) {
				fractal_zip::fatal_error('Corrupt FZC1 payload (value bytes out of bounds).');
			}
			$zipped = substr($contents, $offset, $valLen);
			$offset += $valLen;
			$files[$path] = $zipped;
		}
		if($offset + 4 > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC1 payload (fractal length missing).');
		}
		$frLen = unpack('Nlen', substr($contents, $offset, 4));
		$offset += 4;
		$frLen = (int)$frLen['len'];
		if($offset + $frLen > strlen($contents)) {
			fractal_zip::fatal_error('Corrupt FZC1 payload (fractal bytes out of bounds).');
		}
		$fractal_string = substr($contents, $offset, $frLen);
		$offset += $frLen;
		$peel = $this->decode_fractal_gzip_peel_restore_trailer($contents, $offset, $n);
		return array($files, $fractal_string, $peel);
	}
	if($this->zpaq_native_archive_stream_magic_p($contents)) {
		$zpaqExe = fractal_zip::zpaq_executable();
		if($zpaqExe === null) {
			fractal_zip::fatal_error('Nested container payload uses raw zpaq archive bytes but `zpaq` was not found on PATH (set FRACTAL_ZIP_ZPAQ).');
		}
		$inner = $this->unpack_raw_zpaq_archive_bytes_to_inner_string($contents);
		if($inner === null || $inner === '') {
			fractal_zip::fatal_error('Unknown container payload format (raw zpaq extract failed). ' . fractal_zip::container_payload_unknown_format_diag($contents, false));
		}
		return $this->decode_container_payload($inner);
	}
	// backward compatibility: old serialized array(map, fractal_string)
	$data = @unserialize($contents);
	if(!is_array($data) || sizeof($data) < 2) {
		fractal_zip::fatal_error('Unknown container payload format. ' . fractal_zip::container_payload_unknown_format_diag($contents, $data));
	}
	return array($data[0], $data[1], array());
}

function logical_path_for_zip_member($absolutePath) {
	$root = $this->zip_folder_root_for_members;
	if($root === null || $root === '') {
		return $absolutePath;
	}
	// Per-root cache: zip_folder() keeps one member root for a whole tree walk; skip rtrim+slash norm on every file.
	static $cKey = null;
	static $cRootN = null;
	static $cPrefix = null;
	static $cPrefixLen = 0;
	$r = (string) $root;
	if($cKey !== $r) {
		$cKey = $r;
		$cRootN = rtrim(strtr($r, '\\', '/'), '/');
		$cPrefix = $cRootN . '/';
		$cPrefixLen = strlen($cPrefix);
	}
	$abs = realpath($absolutePath);
	if($abs === false) {
		$abs = $absolutePath;
	}
	$absN = strtr($abs, '\\', '/');
	if(str_starts_with($absN, $cPrefix)) {
		return (string) substr($absN, $cPrefixLen);
	}
	if($absN === $cRootN) {
		return basename($absolutePath);
	}
	fractal_zip::warning_once('logical_path_for_zip_member: file not under zip folder root; using basename.');
	return basename($absolutePath);
}

function recursive_zip_folder($dir, $debug = false) {
	// One ensure per directory visit; inner static makes subsequent calls free (avoids N× per-file dispatch after first load).
	fractal_zip_ensure_literal_pac_stack_loaded();
	$handle = opendir($dir);
	if($handle === false) {
		fractal_zip::warning_once('recursive_zip_folder: unable to open directory, skipping');
		return;
	}
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..') {
			continue;
		}
		$sub = $dir . DS . $entry;
		if(is_dir($sub)) {
			$this->recursive_zip_folder($sub, $debug);
		} else {
			$entry_filename = $sub;
			$contents = file_get_contents($entry_filename);
			$memberKey = $this->logical_path_for_zip_member($entry_filename);
			$diskBytes = (string)$contents;
			$zipBytes = $diskBytes;
			$equivOriginal = null;
			$this->zip_pending_raster_canonical_hash = null;
			$this->zip_raster_canonical_lazy_only = false;
			$this->raster_canonical_reuse_lazy_range = null;
			if(fractal_zip::member_deep_unwrap_enabled()) {
				$mk = (string)$memberKey;
				if($mk !== '') {
					list($unwrapped) = fractal_zip_literal_deep_unwrap_with_layers($mk, $diskBytes);
					if($unwrapped !== $diskBytes) {
						$zipBytes = $unwrapped;
						$this->fractal_member_gzip_disk_restore[$mk] = $diskBytes;
						$equivOriginal = $diskBytes;
					}
				}
			} elseif(fractal_zip_literal_expand_gzip_inner_enabled()) {
				$mkLower = strtolower((string)$memberKey);
				$mkLen = strlen($mkLower);
				if(($mkLen >= 3 && substr($mkLower, -3) === '.gz')
					|| ($mkLen >= 5 && substr($mkLower, -5) === '.svgz')
					|| ($mkLen >= 4 && substr($mkLower, -4) === '.vgz')) {
					$ex = fractal_zip_literal_expand_outer_gzip_once($diskBytes);
					if($ex !== null) {
						$zipBytes = $ex['inner'];
						$this->fractal_member_gzip_disk_restore[(string)$memberKey] = $diskBytes;
						$equivOriginal = $diskBytes;
					}
				}
			}
			$mk = (string)$memberKey;
			if($mk !== '') {
				$canonTry = fractal_zip_raster_canonical_try($mk, $zipBytes);
				if($canonTry !== null && $canonTry !== $zipBytes) {
					$h = sha1($canonTry, true);
					if(isset($this->raster_canonical_hash_to_lazy_range[$h])) {
						$this->raster_canonical_reuse_lazy_range = $this->raster_canonical_hash_to_lazy_range[$h];
					} else {
						$this->zip_pending_raster_canonical_hash = $h;
						$this->zip_raster_canonical_lazy_only = true;
					}
					$zipBytes = $canonTry;
					$this->fractal_member_gzip_disk_restore[$mk] = $diskBytes;
					$equivOriginal = $diskBytes;
				}
			}
			$this->zip($zipBytes, $memberKey, $debug, $equivOriginal);
			if($debug) {
				$this->validate_fractal_zip($memberKey);
			}
			$this->files_counter++;
		}
	}
	closedir($handle);
}

function recursive_get_strings_for_fractal_zip_markers($dir) {
	$handle = opendir($dir);
	if($handle === false) {
		return;
	}
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..') {
			continue;
		}
		$sub = $dir . DS . $entry;
		if(is_dir($sub)) {
			fractal_zip::recursive_get_strings_for_fractal_zip_markers($sub);
		} else {
			$contents = file_get_contents($sub);
			$this->strings_for_fractal_zip_markers[] = $contents;
		}
	}
	closedir($handle);
}

function maximum_substr_expression_length() {
	// assumptions: recursion_counter < 10
	return (5 + (2 * strlen((string)strlen($this->fractal_string))));
}

/**
 * Max input length (bytes) for heavy substring / fractal analysis. Above this, zip uses lazy range markers only.
 * Env FRACTAL_ZIP_MAX_FRACTAL_BYTES: unset = **0** (unlimited; can use huge RAM); positive = cap.
 */
public static function max_fractal_analysis_bytes(): int {
	if(getenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS') === '1' || getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') === '1') {
		return 0;
	}
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MAX_FRACTAL_BYTES');
	if($env === false || trim((string) $env) === '') {
		$cached = 0;
		return $cached;
	}
	$v = (int) trim((string) $env);
	if($v < 0) {
		$cached = 0;
		return $cached;
	}
	$cached = $v;
	return $cached;
}

/**
 * Per-member cap for entering expensive true-fractal analysis in zip().
 * Env FRACTAL_ZIP_MEMBER_FRACTAL_MAX_BYTES: unset = **0** (unlimited); positive = cap.
 */
public static function member_fractal_max_bytes(): int {
	if(getenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS') === '1' || getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') === '1') {
		return 0;
	}
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MEMBER_FRACTAL_MAX_BYTES');
	if($env === false || trim((string) $env) === '') {
		$cached = 0;
		return $cached;
	}
	$v = (int) trim((string) $env);
	$cached = $v <= 0 ? 0 : $v;
	return $cached;
}

/**
 * zip_folder: run the same semantic / gzip / image / stream unwrap as literal bundles before fractal_zip().
 * Env FRACTAL_ZIP_MEMBER_DEEP_UNWRAP: unset = on; 0 = off (legacy .gz/.svgz/.vgz single peel only).
 */
public static function member_deep_unwrap_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MEMBER_DEEP_UNWRAP');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Phase-3 raw escaped-per-file tier: run the same deep unwrap as FZB so outer codecs see peeled payload bytes.
 * Original on-disk bytes are recorded for FZG peel-restore when raw wins (see choose_smallest_adaptive_literal_inner_or_raw_escaped).
 * FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP: unset = on; 0/off/false/no = legacy (store disk bytes in raw tier).
 * FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER: unset = on — when deep unwrap is on and the unwrapped escaped inner differs from disk,
 * evaluate **wire size** adaptive_compress(inner)+FZG* peel trailer (same as zip_folder) for disk vs unwrapped and keep the smaller (peel trailers can dominate on many .gz members).
 * 0/off = always use the unwrapped raw inner only when unwrap is on (no disk-vs-unwrapped race).
 */
public static function bundle_raw_tier_deep_unwrap_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

public static function bundle_raw_tier_dual_compress_pick_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Max total bytes allowed when expanding a fractal substring "tuple" (repeating the same piece) in recursive_substring_replace.
 * Prevents pathological RAM use during unzip / silent_validate. Env FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES: unset = 16777216 (16 MiB); 0 = unlimited.
 */
public static function max_substring_tuple_expand_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES');
	if($env === false || trim((string) $env) === '') {
		$cached = 16777216;
		return $cached;
	}
	$v = (int) trim((string) $env);
	if($v <= 0) {
		$cached = 0;
		return $cached;
	}
	$cached = min($v, 2147483647);
	return $cached;
}

/**
 * Max length (bytes) read from fractal_string for one &lt;off"len"&gt; substring op in recursive_substring_replace. Prevents huge substr + str_replace peaks. Env FRACTAL_ZIP_MAX_SUBSTRING_OPERATION_SLICE_BYTES: unset = 16777216 (16 MiB); 0 = unlimited.
 */
public static function max_substring_operation_slice_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MAX_SUBSTRING_OPERATION_SLICE_BYTES');
	if($env === false || trim((string) $env) === '') {
		$cached = 16777216;
		return $cached;
	}
	$v = (int) trim((string) $env);
	if($v <= 0) {
		$cached = 0;
		return $cached;
	}
	$cached = min($v, 2147483647);
	return $cached;
}

/**
 * Max allowed strlen(equivalence_string) after one substring-op splice in recursive_substring_replace (avoids 200MB+ str_replace-style peaks). Env FRACTAL_ZIP_MAX_EQUIVALENCE_SUBOP_RESULT_BYTES: unset = 201326592 (192 MiB); 0 = unlimited.
 */
public static function max_equivalence_subop_result_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MAX_EQUIVALENCE_SUBOP_RESULT_BYTES');
	if($env === false || trim((string) $env) === '') {
		$cached = 201326592;
		return $cached;
	}
	$v = (int) trim((string) $env);
	if($v <= 0) {
		$cached = 0;
		return $cached;
	}
	$cached = min($v, 2147483647);
	return $cached;
}

/**
 * If any operand exceeds this size, silent_validate skips unzip (returns false). Env FRACTAL_ZIP_SILENT_VALIDATE_MAX_OPERAND_BYTES: unset = 67108864 (64 MiB); 0 = unlimited.
 */
public static function silent_validate_max_operand_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_SILENT_VALIDATE_MAX_OPERAND_BYTES');
	if($env === false || trim((string) $env) === '') {
		$cached = 67108864;
		return $cached;
	}
	$v = (int) trim((string) $env);
	if($v <= 0) {
		$cached = 0;
		return $cached;
	}
	$cached = min($v, 2147483647);
	return $cached;
}

/**
 * How many highest-scoring substrings from all_substrings_count() feed recursive_fractal_substring (wider search).
 * Env FRACTAL_ZIP_SUBSTRING_TOP_K: 1–12, default 12.
 */
public static function substring_top_candidate_count(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_SUBSTRING_TOP_K');
	if($env === false || trim((string) $env) === '') {
		$cached = 12;
		return $cached;
	}
	$v = (int) trim((string) $env);
	$cached = max(1, min(12, $v));
	return $cached;
}

/**
 * Coarse substring enumeration for large inputs: fewer distinct substrings, tighter sliding window, smaller top-K.
 * General tier: FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_MIN_BYTES (default 192 KiB, 0 = off).
 * Stricter tier when FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS=1: FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_MIN_BYTES (default 48 KiB, 0 = disable synth-only coarse).
 *
 * @return array{enabled:bool, max_records:int, top_k_cap:int, hardcap_mult:int, multiple_cap:int}
 */
public static function fractal_substring_coarse_config_for_length(int $slen): array {
	$synthOn = getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') === '1';
	$eCoarse = getenv('FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_MIN_BYTES');
	$coarseMin = ($eCoarse !== false && trim((string) $eCoarse) !== '') ? max(0, (int) $eCoarse) : (192 * 1024);
	$eSynMin = getenv('FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_MIN_BYTES');
	$synthMin = ($eSynMin !== false && trim((string) $eSynMin) !== '') ? max(0, (int) $eSynMin) : (48 * 1024);
	$strictTier = $synthOn && $synthMin > 0 && $slen >= $synthMin;
	$looseTier = $coarseMin > 0 && $slen >= $coarseMin;
	$enabled = $strictTier || $looseTier;
	if(!$enabled) {
		return array(
			'enabled' => false,
			'max_records' => 350000,
			'top_k_cap' => 12,
			'hardcap_mult' => 0,
			'multiple_cap' => 0,
		);
	}
	$strict = $strictTier;
	$eMr = getenv($strict ? 'FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_MAX_RECORDS' : 'FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_MAX_RECORDS');
	$defMr = $strict ? 65000 : 120000;
	$maxRec = ($eMr !== false && trim((string) $eMr) !== '') ? max(5000, min(2000000, (int) $eMr)) : $defMr;
	$eTk = getenv($strict ? 'FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_TOP_K' : 'FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_TOP_K');
	$defTk = $strict ? 5 : 7;
	$topKCap = ($eTk !== false && trim((string) $eTk) !== '') ? max(1, min(12, (int) $eTk)) : $defTk;
	$eHm = getenv($strict ? 'FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_HARD_MULT' : 'FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_HARD_MULT');
	$defHm = $strict ? 12 : 18;
	$hardMult = ($eHm !== false && trim((string) $eHm) !== '') ? max(6, min(48, (int) $eHm)) : $defHm;
	$eMc = getenv($strict ? 'FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_MULTIPLE_CAP' : 'FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_MULTIPLE_CAP');
	$defMc = $strict ? 5 : 7;
	$multCap = ($eMc !== false && trim((string) $eMc) !== '') ? max(3, min(15, (int) $eMc)) : $defMc;
	return array(
		'enabled' => true,
		'max_records' => $maxRec,
		'top_k_cap' => $topKCap,
		'hardcap_mult' => $hardMult,
		'multiple_cap' => $multCap,
	);
}

/**
 * When recursive_fractal_substring starts on a very large root string, clamp depth below max_recursive_fractal_depth().
 * FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_DEPTH_MIN_BYTES: default 512 KiB (262144 when FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS=1); 0 = disable.
 * FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_MAX_RECURSION_DEPTH: 1–64, default 9.
 */
public static function fractal_large_entry_effective_max_depth(int $rootStrlen, int $configuredMaxDepth): int {
	if($rootStrlen <= 0) {
		return $configuredMaxDepth;
	}
	$synthOn = getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') === '1';
	$e = getenv('FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_DEPTH_MIN_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		$thr = max(0, (int) $e);
	} elseif($synthOn) {
		$thr = 262144;
	} else {
		$thr = 524288;
	}
	if($thr <= 0 || $rootStrlen < $thr) {
		return $configuredMaxDepth;
	}
	$ed = getenv('FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_MAX_RECURSION_DEPTH');
	$d = ($ed !== false && trim((string) $ed) !== '') ? max(1, min(64, (int) $ed)) : 9;
	return min($configuredMaxDepth, $d);
}

/**
 * Max recursion depth for recursive_fractal_substring() exploration.
 * Env FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_DEPTH: 1-64, default 12.
 * During FZBF nested zip (FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS=1), optional
 * FRACTAL_ZIP_LITERAL_SYNTH_MAX_RECURSIVE_FRACTAL_DEPTH (1-64); otherwise same default as normal (12).
 */
public static function max_recursive_fractal_depth(): int {
	$synthOn = getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') === '1';
	$sKey = $synthOn ? 's' : 'n';
	static $cached = array();
	if(isset($cached[$sKey])) {
		return $cached[$sKey];
	}
	if($synthOn) {
		$eSyn = getenv('FRACTAL_ZIP_LITERAL_SYNTH_MAX_RECURSIVE_FRACTAL_DEPTH');
		if($eSyn !== false && trim((string) $eSyn) !== '') {
			$v = (int) trim((string) $eSyn);
			$cached[$sKey] = max(1, min(64, $v));
			return $cached[$sKey];
		}
	}
	$env = getenv('FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_DEPTH');
	if($env === false || trim((string) $env) === '') {
		$cached[$sKey] = 12;
		return $cached[$sKey];
	}
	$v = (int) trim((string) $env);
	$cached[$sKey] = max(1, min(64, $v));
	return $cached[$sKey];
}

/**
 * Wall-clock budget (seconds) for recursive_fractal_substring() exploration per zip instance.
 * Env FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_SECONDS: 1-120, default 10.
 * FZBF nested zip sets FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS=1; default wall budget matches the normal 10s unless
 * FRACTAL_ZIP_LITERAL_SYNTH_MAX_RECURSIVE_FRACTAL_SECONDS is set (1-600) for deeper searches (e.g. README 102-byte demos).
 * Cached separately from the normal path so a parent zip cannot pin the nested synth to the wrong budget.
 */
public static function max_recursive_fractal_seconds(): int {
	$synthOn = getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') === '1';
	// Include FRACTAL_ZIP_TIME_BUDGET_MS in the cache key so per-request changes (e.g. perf_test
	// shrinking the remaining wall between corpora) are not pinned to the first value seen.
	$ms = getenv('FRACTAL_ZIP_TIME_BUDGET_MS');
	$msKey = 'noms';
	if($ms !== false && trim((string) $ms) !== '' && is_numeric($ms)) {
		$msKey = 'ms' . (string) (int) (float) trim((string) $ms);
	}
	$sKey = ($synthOn ? 's' : 'n') . '|' . $msKey;
	static $cached = array();
	if(isset($cached[$sKey])) {
		return $cached[$sKey];
	}
	// Global speed budget (milliseconds) overrides recursive search budget.
	if($ms !== false && trim((string)$ms) !== '' && is_numeric($ms)) {
		$vms = (float) trim((string)$ms);
		if($vms > 0) {
			$sec = (int) ceil($vms / 1000.0);
			$cap = $synthOn ? 600 : 120;
			$cached[$sKey] = max(1, min($cap, $sec));
			return $cached[$sKey];
		}
	}
	if($synthOn) {
		$envS = getenv('FRACTAL_ZIP_LITERAL_SYNTH_MAX_RECURSIVE_FRACTAL_SECONDS');
		if($envS !== false && trim((string) $envS) !== '') {
			$v = (int) trim((string) $envS);
			$cached[$sKey] = max(1, min(600, $v));
			return $cached[$sKey];
		}
		$cached[$sKey] = 10;
		return $cached[$sKey];
	}
	$env = getenv('FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_SECONDS');
	if($env === false || trim((string) $env) === '') {
		$cached[$sKey] = 10;
		return $cached[$sKey];
	}
	$v = (int) trim((string) $env);
	$cached[$sKey] = max(1, min(120, $v));
	return $cached[$sKey];
}

/**
 * Wall-clock budget for the zip_folder / literal-synth **multipass equivalence** loop (range substitution passes).
 * Normal folders: same as FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_SECONDS (via max_recursive_fractal_seconds()), except
 * FRACTAL_ZIP_ULTRA=1 defaults to **0 = unlimited** while passes still improve (override with FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS>0).
 * FZBF nested synth: default **0 = unlimited** while passes still improve; set FRACTAL_ZIP_LITERAL_SYNTH_MULTIPASS_MAX_SECONDS>0 to cap.
 */
public static function max_fractal_multipass_wall_seconds(): int {
	if(getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') !== '1') {
		$capEnv = getenv('FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS');
		if($capEnv !== false && trim((string) $capEnv) !== '') {
			$cv = (int) trim((string) $capEnv);
			if($cv <= 0) {
				return 0;
			}
			return max(1, min(86400, $cv));
		}
		if(fractal_zip::ultra_compression_enabled()) {
			return 0;
		}
		return fractal_zip::max_recursive_fractal_seconds();
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SYNTH_MULTIPASS_MAX_SECONDS');
	if($e === false || trim((string) $e) === '') {
		return 0;
	}
	$v = (int) trim((string) $e);
	if($v <= 0) {
		return 0;
	}
	return max(1, min(86400, $v));
}

/**
 * Multipass keeps a candidate when matches×len(piece)/len(range_marker) > this × improvement_factor_threshold.
 * Env FRACTAL_ZIP_MULTIPASS_GATE_MULT: 1.0–4.0, default 1.28 (fewer marginal multipass candidates vs 1.18).
 */
public static function multipass_ratio_gate_multiplier(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MULTIPASS_GATE_MULT');
	if($env === false || trim((string) $env) === '') {
		$cached = 1.28;
		return $cached;
	}
	$v = (float) trim((string) $env);
	$cached = max(1.0, min(4.0, $v));
	return $cached;
}

/** @uses static env default when instance override unset */
function effective_substring_top_k(): int {
	if($this->tuning_substring_top_k !== null) {
		return max(1, min(12, (int) $this->tuning_substring_top_k));
	}
	return fractal_zip::substring_top_candidate_count();
}

function effective_multipass_gate_mult(): float {
	if($this->tuning_multipass_gate_mult !== null) {
		return max(1.0, min(4.0, (float) $this->tuning_multipass_gate_mult));
	}
	return fractal_zip::multipass_ratio_gate_multiplier();
}

/**
 * $minimum_overhead_length drives multipass substring windows; keep consistent with marker widths.
 */
function prepare_minimum_overhead_for_multipass() {
	if($this->multipass) {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 1 + strlen($this->mid_fractal_zip_marker) + 3 + strlen($this->mid_fractal_zip_marker) + 1 + strlen($this->right_fractal_zip_marker);
	} else {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 3 + strlen($this->right_fractal_zip_marker);
	}
}

/**
 * Multipass range substitution on $this->equivalences (same logic as zip_folder tail). Continues while each pass finds improvements
 * or until max additional passes / wall seconds (see max_fractal_multipass_wall_seconds).
 */
function run_fractal_multipass_equivalence_passes($debug = false) {
	if(!$this->multipass) {
		return;
	}
	$maxAdditional = $this->multipass_max_additional_passes;
	$additionalDone = 0;
	$last_pass_made_an_improvement = true;
	while($last_pass_made_an_improvement) {
		if($maxAdditional !== null && $additionalDone >= (int) $maxAdditional) {
			break;
		}
		$maxSeconds = fractal_zip::max_fractal_multipass_wall_seconds();
		if($maxSeconds > 0 && microtime(true) - $this->initial_micro_time > $maxSeconds) {
			fractal_zip::warning_once('multipass time budget reached; stopping additional passes');
			break;
		}
		$last_pass_made_an_improvement = false;
		$improving_replaces = array();
		$this->fractal_zipping_pass++;
		if($debug) {
			print('$this->fractal_zipping_pass: ');
			var_dump($this->fractal_zipping_pass);
		}
		$strings = array();
		foreach($this->equivalences as $equivalence_index => $equivalence) {
			$string = $equivalence[2];
			$strings[] = $string;
		}
		foreach($this->equivalences as $equivalence_index => $equivalence) {
			$string = $equivalence[2];
			$stringLen = strlen($string);
			$counter = 0;
			$last_start_matches = 0;
			while($counter < $stringLen) {
				$length_counter = $stringLen - $counter;
				if($length_counter > $this->segment_length) {
					$length_counter = $this->segment_length;
				}
				$last_end_matches = 0;
				while($length_counter > $this->maximum_substr_expression_length()) {
					$piece = substr($string, $counter, $length_counter);
					$matches = 0;
					foreach($strings as $string2) {
						$matches += substr_count($string2, $piece);
					}
					if($matches > 1 && $matches > $last_start_matches && $matches > $last_end_matches) {
						$position_in_fractal_string = strpos($this->fractal_string, $piece);
						if($position_in_fractal_string !== false) {
							$start_offset = $position_in_fractal_string;
						} else {
							$start_offset = strlen($this->fractal_string);
						}
						$end_offset = strlen($piece) + $start_offset - 1;
						$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
						if($matches * strlen($piece) / strlen($range_string) > $this->effective_multipass_gate_mult() * $this->improvement_factor_threshold) {
							$score = $matches * (strlen($piece) - strlen($range_string));
							$improving_replaces[$piece] = $score;
							$last_pass_made_an_improvement = true;
						}
					}
					$last_end_matches = $matches;
					$length_counter--;
				}
				$last_start_matches = $matches;
				$counter++;
			}
		}
		arsort($improving_replaces);
		foreach($improving_replaces as $search => $score) {
			$would_need_to_add_to_fractal_string = false;
			$position_in_fractal_string = strpos($this->fractal_string, $search);
			if($position_in_fractal_string !== false) {
				$start_offset = $position_in_fractal_string;
			} else {
				$start_offset = strlen($this->fractal_string);
				$would_need_to_add_to_fractal_string = true;
			}
			$end_offset = strlen($search) + $start_offset - 1;
			$range_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . $start_offset . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
			if($would_need_to_add_to_fractal_string) {
				$this->fractal_string .= $search;
			}
			foreach($this->equivalences as $equivalence_index => $equivalence) {
				$this->equivalences[$equivalence_index][2] = str_replace($search, $range_string, $this->equivalences[$equivalence_index][2]);
			}
		}
		$additionalDone++;
	}
}

function maximum_scale_expression_length() {
	// assumptions: recursion_counter < 10
	return (4 + (2 * strlen((string)strlen($this->fractal_string))) + strlen((string)round((1 / 3), 6))); // limiting infinitely expressed fractions (0.3333..., etc.) that arise in decimal due to incompatibilities between the expressed value nature and the limited number of factors in the base (10) choice
}

function fractal_marker_ctx_publish(): void {
	fractal_zip::$fractal_marker_ctx_left = (string) $this->left_fractal_zip_marker;
	fractal_zip::$fractal_marker_ctx_mid = (string) $this->mid_fractal_zip_marker;
	fractal_zip::$fractal_marker_ctx_right = (string) $this->right_fractal_zip_marker;
	fractal_zip::$fractal_marker_ctx_range = (string) $this->range_shorthand_marker;
}

function create_fractal_zip_markers($dir, $debug = false) {
	$this->minimum_overhead_length = 5; // <#"#>
	fractal_zip::warning_once('need the ability to have a recursive fractal zip such that the structure of what is compressed (such as a file structure or data structure) could be selectively decompressed according to what in the fractal zip we are interested in. self-extracting file could also thus be self-navigating');
	fractal_zip_maybe_apply_adaptive_markers($this, $dir);
	if($this->multipass) {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 1 + strlen($this->mid_fractal_zip_marker) + 3 + strlen($this->mid_fractal_zip_marker) + 1 + strlen($this->right_fractal_zip_marker);
	} else {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 3 + strlen($this->right_fractal_zip_marker);
	}
	$this->fractal_marker_ctx_publish();
	return true;
}

/** Sum of file sizes under $dir (bytes); directories only, regular files. */
function folder_raw_total_bytes($dir) {
	if(!is_dir($dir)) {
		return 0;
	}
	$total = 0;
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach($it as $fileInfo) {
		if($fileInfo->isFile()) {
			$total += (int) $fileInfo->getSize();
		}
	}
	return $total;
}

/**
 * Use streaming FZB4 + raw deflate outer for very large folders when enabled.
 * FRACTAL_ZIP_FOLDER_GZIP_FAST: unset = auto if raw bytes ≥ FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES (default 128 MiB; was 48 MiB — 48–120 MiB trees often FLAC/literal-unified and lose badly to deflate-only); 1 = on; 0 = off.
 */
function should_use_large_folder_gzip_fast_path($dir) {
	$v = getenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
	if($v !== false && trim((string) $v) !== '') {
		$v = strtolower(trim((string) $v));
		if($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
			return false;
		}
		if($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes') {
			return true;
		}
	}
	$threshEnv = getenv('FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES');
	$lim = ($threshEnv !== false && trim((string) $threshEnv) !== '') ? (int) $threshEnv : (128 * 1024 * 1024);
	if($lim <= 0) {
		return false;
	}
	return $this->folder_raw_total_bytes($dir) >= $lim;
}

/**
 * Write FZB4 literal bundle to a temp file (one file loaded at a time). Matches encode_literal_bundle_payload FZB4 layout.
 * @param bool $rawLiteralsOnly If true, skip choose_best_literal_bundle_transform (mode 0, raw bytes only) for speed on huge trees.
 * @return bool false on I/O or path-length failure
 */
function fzcd_cleanup_pcm_rows(array $rows) {
	foreach($rows as $r) {
		if(isset($r['pcm']) && is_string($r['pcm']) && $r['pcm'] !== '' && is_file($r['pcm'])) {
			@unlink($r['pcm']);
		}
	}
}

/**
 * Inner bundle FZCD v1: merged raw PCM for one or more compatible .flac (same rate/channels/bps/pcm_fmt). Payload is either
 * one FLAC stream (FRACTAL_ZIP_FZCD_FLAG_MERGED_FLAC), chunked fractal FZC* (FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_CHUNKED),
 * legacy single-blob fractal (FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_PCM),
 * legacy gzip on PCM (bit 2), or raw inline PCM. Manifest keeps per-track byte lengths. Non-FLAC members use FZB4-style
 * records after. Outer raw deflate compresses the whole inner.
 * @param list<string> $paths sorted relative paths
 */
function write_fzcd_bundle_if_applicable($root, array $paths, $outPath) {
	fractal_zip_ensure_flac_pac_loaded();
	if(!fractal_zip_flac_pac_enabled() || !fractal_zip_flac_pac_tools_ok()) {
		return false;
	}
	$flacPaths = array();
	$otherPaths = array();
	foreach($paths as $p) {
		if(preg_match('/\\.flac$/i', (string) $p)) {
			$flacPaths[] = (string) $p;
		} else {
			$otherPaths[] = (string) $p;
		}
	}
	if(count($flacPaths) < 1) {
		return false;
	}
	$fzcdWBase = fractal_zip::hot_string_digest((string) $root . "\0" . implode("\n", $paths) . "\0fzcdw\0" . (string) spl_object_id($this));
	$rows = array();
	$ref = null;
	foreach($flacPaths as $pi => $p) {
		$full = $root . DS . str_replace('/', DS, $p);
		if(!is_file($full)) {
			$this->fzcd_cleanup_pcm_rows($rows);
			return false;
		}
		$meta = fractal_zip_flac_pac_probe($full);
		if($meta === null) {
			$this->fzcd_cleanup_pcm_rows($rows);
			return false;
		}
		if($ref === null) {
			$ref = $meta;
		} elseif($ref['sample_rate'] !== $meta['sample_rate'] || $ref['channels'] !== $meta['channels']
			|| $ref['source_bps'] !== $meta['source_bps'] || $ref['pcm_fmt'] !== $meta['pcm_fmt']) {
			$this->fzcd_cleanup_pcm_rows($rows);
			return false;
		}
		$pcmTmp = sys_get_temp_dir() . DS . 'fzcdpcm_' . substr(md5($fzcdWBase . "\0row\0" . (string) $pi), 0, 16) . '.raw';
		if(!fractal_zip_flac_pac_decode_file_to_pcm_path($full, $meta['pcm_fmt'], $pcmTmp)) {
			@unlink($pcmTmp);
			$this->fzcd_cleanup_pcm_rows($rows);
			return false;
		}
		$sz = filesize($pcmTmp);
		if(!is_int($sz) || $sz < 1) {
			@unlink($pcmTmp);
			$this->fzcd_cleanup_pcm_rows($rows);
			return false;
		}
		$rows[] = array(
			'path' => $p,
			'pcm' => $pcmTmp,
			'len' => $sz,
			'sample_rate' => $meta['sample_rate'],
			'channels' => $meta['channels'],
			'source_bps' => $meta['source_bps'],
			'pcm_fmt' => $meta['pcm_fmt'],
		);
	}
	$plannedRaw = 0;
	foreach($rows as $r) {
		$plannedRaw += (int) $r['len'];
	}
	// Chunked merged-fractal streams PCM from mergePath; no all-in-RAM size cap here (see fractal_zip_flac_pac_merged_fractal_requested).
	$wantMergedFractal = fractal_zip_flac_pac_merged_fractal_requested($plannedRaw);
	$manifestParts = array($this->encode_varint_u32(count($rows)));
	$prevPath = '';
	foreach($rows as $r) {
		$p = $r['path'];
		$cpl = 0;
		$max = min(strlen($prevPath), strlen($p));
		while($cpl < $max && $prevPath[$cpl] === $p[$cpl]) {
			$cpl++;
		}
		$suf = substr($p, $cpl);
		$chunk = $this->encode_varint_u32($cpl) . $this->encode_varint_u32(strlen($suf)) . $suf
			. pack('V', $r['sample_rate'])
			. chr(min(255, $r['channels'])) . chr(min(255, $r['source_bps'])) . chr($r['pcm_fmt'])
			. pack('P', $r['len']);
		$manifestParts[] = $chunk;
		$prevPath = $p;
	}
	$manifestBin = implode('', $manifestParts);
	$mergePath = sys_get_temp_dir() . DS . 'fzcdmrg_' . substr(md5($fzcdWBase . "\0merge"), 0, 16) . '.raw';
	$mergeOut = @fopen($mergePath, 'wb');
	if($mergeOut === false) {
		$this->fzcd_cleanup_pcm_rows($rows);
		return false;
	}
	$ok = true;
	$rawTotal = 0;
	foreach($rows as $r) {
		$pin = @fopen($r['pcm'], 'rb');
		if($pin === false) {
			$ok = false;
			break;
		}
		$copied = stream_copy_to_stream($pin, $mergeOut);
		fclose($pin);
		@unlink($r['pcm']);
		if($copied !== $r['len']) {
			$ok = false;
			break;
		}
		$rawTotal += $r['len'];
	}
	fclose($mergeOut);
	if(!$ok) {
		@unlink($mergePath);
		$this->fzcd_cleanup_pcm_rows($rows);
		return false;
	}
	$fractalInner = null;
	$fzcdReuseMergedFlacPath = null;
	if($wantMergedFractal) {
		$bpf = fractal_zip_flac_pac_pcm_bytes_per_frame_from_ref($ref);
		$chunkSz = fractal_zip_flac_pac_align_chunk_bytes(fractal_zip_flac_pac_merged_fractal_chunk_bytes(), $bpf);
		$mpFr = fractal_zip_flac_pac_merged_fractal_use_multipass();
		$useMp = $mpFr ? (bool) $this->multipass : false;
		$seg = fractal_zip_fzcd_merged_fractal_segment_length((int) $this->segment_length);
		$imp = fractal_zip_fzcd_merged_fractal_improvement_threshold((float) $this->improvement_factor_threshold);
		$topK = $this->tuning_substring_top_k;
		$gate = $this->tuning_multipass_gate_mult;
		$mergeIn = @fopen($mergePath, 'rb');
		if($mergeIn !== false) {
			$pcmPreWire = fractal_zip_pcm_pretransform_enabled();
			$chunkPartList = array();
			$nChunks = 0;
			$sumPcm = 0;
			$allChunksOk = true;
			while($sumPcm < $rawTotal && $allChunksOk) {
				$need = min($chunkSz, $rawTotal - $sumPcm);
				$pcmChunk = stream_get_contents($mergeIn, $need);
				if($pcmChunk === false) {
					$pcmChunk = '';
				}
				$got = strlen($pcmChunk);
				if($got !== $need) {
					$allChunksOk = false;
					break;
				}
				$enc = fractal_zip_fzcd_encode_merged_pcm_chunk_with_pre($pcmChunk, $seg, $useMp, $imp, $topK, $gate, (int) $ref['pcm_fmt'], (int) $ref['channels']);
				if($enc === null || strlen($enc['inner']) >= $got) {
					$allChunksOk = false;
					break;
				}
				$innerBlob = $enc['inner'];
				$segBase = pack('P', $got) . pack('P', strlen($innerBlob));
				$preByte = $pcmPreWire ? chr(max(0, min(255, (int) $enc['pre']))) : '';
				$chunkPartList[] = $segBase . $preByte . $innerBlob;
				$nChunks++;
				$sumPcm += $got;
			}
			fclose($mergeIn);
			if($allChunksOk && $sumPcm === $rawTotal && $nChunks > 0) {
				$fractalInner = pack('P', $rawTotal) . pack('V', $nChunks) . implode('', $chunkPartList);
			} else {
				fractal_zip::warning_once('FZCD merged fractal: chunk encode failed or size mismatch; using merged FLAC.');
			}
		}
	}
	if($fractalInner !== null && is_file($mergePath)) {
		$flacCmp = $mergePath . '.fzcdsz.flac';
		if(fractal_zip_flac_pac_pcm_file_to_flac_file($mergePath, $ref['sample_rate'], $ref['channels'], $ref['pcm_fmt'], $flacCmp)) {
			$cmpLen = @filesize($flacCmp);
			$frPay = strlen($fractalInner);
			if(is_int($cmpLen) && $cmpLen > 0 && $frPay >= $cmpLen) {
				$fractalInner = null;
				$fzcdReuseMergedFlacPath = $flacCmp;
			} elseif(is_file($flacCmp)) {
				@unlink($flacCmp);
			}
		}
	}
	$fh = fopen($outPath, 'wb');
	if($fh === false) {
		@unlink($mergePath);
		return false;
	}
	$fzcdFlags = 1 | (($fractalInner !== null) ? FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_CHUNKED : FRACTAL_ZIP_FZCD_FLAG_MERGED_FLAC);
	if($fractalInner !== null && fractal_zip_pcm_pretransform_enabled()) {
		$fzcdFlags |= FRACTAL_ZIP_FZCD_FLAG_CHUNK_PCM_PRETRANSFORM;
	}
	$ok = fwrite($fh, 'FZCD' . chr(1) . chr($fzcdFlags)) !== false;
	if($ok) {
		$ok = fwrite($fh, $manifestBin) !== false;
	}
	if($ok && $fractalInner !== null) {
		$frLen = strlen($fractalInner);
		$w3 = fwrite($fh, $fractalInner);
		if($w3 === false || $w3 !== $frLen) {
			$ok = false;
		}
		@unlink($mergePath);
	} elseif($ok) {
		if($fzcdReuseMergedFlacPath !== null && is_file($fzcdReuseMergedFlacPath)) {
			$flacPath = $fzcdReuseMergedFlacPath;
			if(is_file($mergePath)) {
				@unlink($mergePath);
			}
		} else {
			$flacPath = $mergePath . '.flac';
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($mergePath, $ref['sample_rate'], $ref['channels'], $ref['pcm_fmt'], $flacPath)) {
				@unlink($mergePath);
				@unlink($flacPath);
				$ok = false;
			} else {
				@unlink($mergePath);
			}
		}
		if($ok) {
			$flLen = filesize($flacPath);
			if(!is_int($flLen) || $flLen < 1) {
				@unlink($flacPath);
				$ok = false;
			} elseif(fwrite($fh, pack('P', $rawTotal)) === false || fwrite($fh, pack('P', $flLen)) === false) {
				@unlink($flacPath);
				$ok = false;
			} else {
				$flIn = @fopen($flacPath, 'rb');
				if($flIn === false) {
					@unlink($flacPath);
					$ok = false;
				} else {
					if(stream_copy_to_stream($flIn, $fh) !== $flLen) {
						$ok = false;
					}
					fclose($flIn);
					@unlink($flacPath);
				}
			}
		}
	}
	if($ok && fwrite($fh, $this->encode_varint_u32(count($otherPaths))) === false) {
		$ok = false;
	}
	$prevPath = '';
	if($ok) {
		foreach($otherPaths as $p) {
			$full = $root . DS . str_replace('/', DS, $p);
			$rawBytes = file_get_contents($full);
			if($rawBytes === false) {
				$rawBytes = '';
			}
			list($mode, $storeBytes) = $this->choose_best_literal_bundle_transform($rawBytes, $p);
			$cpl = 0;
			$max = min(strlen($prevPath), strlen($p));
			while($cpl < $max && $prevPath[$cpl] === $p[$cpl]) {
				$cpl++;
			}
			$suf = substr($p, $cpl);
			$rec = $this->encode_varint_u32($cpl) . $this->encode_varint_u32(strlen($suf)) . $suf
				. chr($mode) . $this->encode_varint_u32(strlen($storeBytes)) . $storeBytes;
			if(fwrite($fh, $rec) === false) {
				$ok = false;
				break;
			}
			$prevPath = $p;
		}
	}
	fclose($fh);
	if(!$ok) {
		@unlink($outPath);
		$this->fzcd_cleanup_pcm_rows($rows);
		return false;
	}
	return true;
}

/**
 * Stream-read FZCD inner from disk; write members under $rootDir (FLACs re-encoded; others literal modes).
 * @return bool false if not FZCD
 */
function extract_fzcd_bundle_streaming_from_path($bundlePath, $rootDir, $debug) {
	fractal_zip_ensure_flac_pac_loaded();
	$fh = @fopen($bundlePath, 'rb');
	if($fh === false) {
		return false;
	}
	$sig = fread($fh, 4);
	if($sig !== 'FZCD') {
		fclose($fh);
		return false;
	}
	$verCh = fread($fh, 1);
	if($verCh === false || strlen($verCh) !== 1 || ord($verCh) !== 1) {
		fclose($fh);
		fractal_zip::fatal_error('Unsupported or corrupt FZCD version.');
	}
	$flCh = fread($fh, 1);
	if($flCh === false || strlen($flCh) !== 1) {
		fclose($fh);
		fractal_zip::fatal_error('Corrupt FZCD (flags).');
	}
	if((ord($flCh) & 1) === 0) {
		fclose($fh);
		return false;
	}
	$this->fractal_string = '';
	$this->array_fractal_zipped_strings_of_files = array();
	fractal_zip::$open_container_streaming_extract_used = true;
	$nFl = $this->read_varint_u32_from_stream($fh, 'FZCD');
	$prevPath = '';
	$entries = array();
	for($fi = 0; $fi < $nFl; $fi++) {
		$prefixLen = $this->read_varint_u32_from_stream($fh, 'FZCD');
		$suffixLen = $this->read_varint_u32_from_stream($fh, 'FZCD');
		if($prefixLen > strlen($prevPath)) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD path prefix.');
		}
		$suf = $suffixLen > 0 ? fread($fh, $suffixLen) : '';
		if(strlen($suf) !== $suffixLen) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD path suffix.');
		}
		$path = substr($prevPath, 0, $prefixLen) . $suf;
		$metaB = fread($fh, 15);
		if(strlen($metaB) !== 15) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD FLAC meta.');
		}
		$um = unpack('Vsr/Cch/Cbps/Cfmt/Pplen', $metaB);
		if(!is_array($um) || !isset($um['plen'])) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD FLAC meta unpack.');
		}
		$entries[] = array(
			'path' => $path,
			'sr' => (int) $um['sr'],
			'ch' => (int) $um['ch'],
			'fmt' => (int) $um['fmt'],
			'pcmLen' => (int) $um['plen'],
		);
		$prevPath = $path;
	}
	$fzcdFlags = ord($flCh);
	$fzcdExBase = fractal_zip::hot_string_digest((string) $bundlePath . "\0fzcdex\0" . (string) spl_object_id($this));
	if(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_CHUNKED) !== 0) {
		if($entries === []) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: empty FLAC table.');
		}
		$hb = fread($fh, 12);
		if(strlen($hb) !== 12) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal chunked header.');
		}
		$ur = unpack('Praw', substr($hb, 0, 8));
		$unc = unpack('Vnch', substr($hb, 8, 4));
		if(!is_array($ur) || !is_array($unc) || !isset($ur['raw'], $unc['nch'])) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal chunked header unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$nChunks = (int) $unc['nch'];
		if($rawTotal < 1 || $nChunks < 1) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal chunked counts.');
		}
		$chunkPre = ($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_CHUNK_PCM_PRETRANSFORM) !== 0;
		$pcmFmt0 = (int) $entries[0]['fmt'];
		$ch0 = (int) $entries[0]['ch'];
		$pcmPieces = array();
		$sumChunk = 0;
		for($ci = 0; $ci < $nChunks; $ci++) {
			$szb = fread($fh, 16);
			if(strlen($szb) !== 16) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: merged fractal chunk sizes.');
			}
			$ucp = unpack('Pclen', substr($szb, 0, 8));
			$uil = unpack('Pilen', substr($szb, 8, 8));
			if(!is_array($ucp) || !is_array($uil) || !isset($ucp['clen'], $uil['ilen'])) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: merged fractal chunk size unpack.');
			}
			$cLen = (int) $ucp['clen'];
			$iLen = (int) $uil['ilen'];
			$preId = FRACTAL_ZIP_PCM_PRE_NONE;
			if($chunkPre) {
				$pb = fread($fh, 1);
				if($pb === false || strlen($pb) !== 1) {
					fclose($fh);
					fractal_zip::fatal_error('FZCD extract: merged fractal chunk pre id.');
				}
				$preId = ord($pb);
			}
			if($cLen < 1 || $iLen < 1) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: merged fractal chunk lengths.');
			}
			$frTmp = sys_get_temp_dir() . DS . 'fzcdexfr_' . substr(md5($fzcdExBase . "\0ch_inner\0" . (string) $ci), 0, 16) . '.fzcinner';
			$fout = @fopen($frTmp, 'wb');
			if($fout === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp merged fractal chunk.');
			}
			$remain = $iLen;
			while($remain > 0) {
				$take = $remain > 1048576 ? 1048576 : $remain;
				$b = fread($fh, $take);
				if($b === false || strlen($b) !== $take) {
					fclose($fout);
					fclose($fh);
					@unlink($frTmp);
					fractal_zip::fatal_error('Corrupt FZCD merged fractal chunk body.');
				}
				fwrite($fout, $b);
				$remain -= $take;
			}
			fclose($fout);
			$frBody = file_get_contents($frTmp);
			@unlink($frTmp);
			if($frBody === false || strlen($frBody) !== $iLen) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: read merged fractal chunk inner.');
			}
			$innerDec = $this->decode_container_payload($frBody);
			if(!is_array($innerDec) || !isset($innerDec[0], $innerDec[1]) || !is_array($innerDec[0])) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: merged fractal chunk inner decode.');
			}
			$innerMap = $innerDec[0];
			$innerFr = (string) $innerDec[1];
			if(!isset($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER])) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: merged fractal chunk member missing.');
			}
			$pcmPiece = $this->unzip($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER], $innerFr, true);
			if(strlen($pcmPiece) !== $cLen) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: merged fractal chunk PCM length.');
			}
			if($chunkPre) {
				$pcmInv = fractal_zip_fzcd_merge_pcm_piece_maybe_invert($pcmPiece, $preId, $cLen, $pcmFmt0, $ch0);
				if($pcmInv === null) {
					fclose($fh);
					fractal_zip::fatal_error('FZCD extract: merged fractal chunk PCM pre-transform inverse.');
				}
				$pcmPiece = $pcmInv;
			}
			$pcmPieces[] = $pcmPiece;
			$sumChunk += $cLen;
		}
		$pcmAll = implode('', $pcmPieces);
		if(strlen($pcmAll) !== $rawTotal || $sumChunk !== $rawTotal) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal chunked PCM total.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfxT = substr(md5($fzcdExBase . "\0ex_trkch\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . $sfxT . '_p.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . $sfxT . '.flac';
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $outFlac)) {
				@unlink($pcmTmp);
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM→FLAC failed.');
			}
			@unlink($pcmTmp);
			$flacData = file_get_contents($outFlac);
			@unlink($outFlac);
			if($flacData === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: read FLAC.');
			}
			$escaped = $this->escape_literal_for_storage($flacData);
			$unzipped = $this->unzip($escaped);
			$outPath = $rootDir . DS . str_replace('/', DS, $e['path']);
			$this->build_directory_structure_for($outPath);
			file_put_contents($outPath, $unzipped);
			if($debug) {
				print('Extracted ' . $outPath . '<br>');
			}
			$this->files_counter++;
			$this->array_fractal_zipped_strings_of_files[$e['path']] = '';
		}
	} elseif(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_PCM) !== 0) {
		if($entries === []) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: empty FLAC table.');
		}
		$nb = fread($fh, 16);
		if(strlen($nb) !== 16) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal sizes.');
		}
		$ur = unpack('Praw', substr($nb, 0, 8));
		$ufr = unpack('Pfrl', substr($nb, 8, 8));
		if(!is_array($ur) || !is_array($ufr) || !isset($ur['raw'], $ufr['frl'])) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal size unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$frLen = (int) $ufr['frl'];
		$frTmp = sys_get_temp_dir() . DS . 'fzcdexfr_' . substr(md5($fzcdExBase . "\0ex_mf_inner"), 0, 16) . '.fzcinner';
		$fout = @fopen($frTmp, 'wb');
		if($fout === false) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: temp merged fractal.');
		}
		$remain = $frLen;
		while($remain > 0) {
			$take = $remain > 1048576 ? 1048576 : $remain;
			$b = fread($fh, $take);
			if($b === false || strlen($b) !== $take) {
				fclose($fout);
				fclose($fh);
				@unlink($frTmp);
				fractal_zip::fatal_error('Corrupt FZCD merged fractal body.');
			}
			fwrite($fout, $b);
			$remain -= $take;
		}
		fclose($fout);
		$frBody = file_get_contents($frTmp);
		@unlink($frTmp);
		if($frBody === false || strlen($frBody) !== $frLen) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: read merged fractal inner.');
		}
		$innerDec = $this->decode_container_payload($frBody);
		if(!is_array($innerDec) || !isset($innerDec[0], $innerDec[1]) || !is_array($innerDec[0])) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal inner decode.');
		}
		$innerMap = $innerDec[0];
		$innerFr = (string) $innerDec[1];
		if(!isset($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER])) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal member missing.');
		}
		$pcmAll = $this->unzip($innerMap[FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER], $innerFr, true);
		if(strlen($pcmAll) !== $rawTotal) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: PCM length after merged fractal.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfxM = substr(md5($fzcdExBase . "\0ex_trkmf\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . $sfxM . '_p.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . $sfxM . '.flac';
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $outFlac)) {
				@unlink($pcmTmp);
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM→FLAC failed.');
			}
			@unlink($pcmTmp);
			$flacData = file_get_contents($outFlac);
			@unlink($outFlac);
			if($flacData === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: read FLAC.');
			}
			$escaped = $this->escape_literal_for_storage($flacData);
			$unzipped = $this->unzip($escaped);
			$outPath = $rootDir . DS . str_replace('/', DS, $e['path']);
			$this->build_directory_structure_for($outPath);
			file_put_contents($outPath, $unzipped);
			if($debug) {
				print('Extracted ' . $outPath . '<br>');
			}
			$this->files_counter++;
			$this->array_fractal_zipped_strings_of_files[$e['path']] = '';
		}
	} elseif(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_FLAC) !== 0) {
		if($entries === []) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: empty FLAC table.');
		}
		$nb = fread($fh, 16);
		if(strlen($nb) !== 16) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged FLAC sizes.');
		}
		$ur = unpack('Praw', substr($nb, 0, 8));
		$uf = unpack('Pfll', substr($nb, 8, 8));
		if(!is_array($ur) || !is_array($uf) || !isset($ur['raw'], $uf['fll'])) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged FLAC size unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$flLen = (int) $uf['fll'];
		$sfxExMfl = substr(md5($fzcdExBase . "\0ex_mfl_in"), 0, 16);
		$flacTmp = sys_get_temp_dir() . DS . 'fzcdexm_' . $sfxExMfl . '.flac';
		$fout = @fopen($flacTmp, 'wb');
		if($fout === false) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: temp merged FLAC.');
		}
		$remain = $flLen;
		while($remain > 0) {
			$take = $remain > 1048576 ? 1048576 : $remain;
			$b = fread($fh, $take);
			if($b === false || strlen($b) !== $take) {
				fclose($fout);
				fclose($fh);
				@unlink($flacTmp);
				fractal_zip::fatal_error('Corrupt FZCD merged FLAC body.');
			}
			fwrite($fout, $b);
			$remain -= $take;
		}
		fclose($fout);
		$pcmAllPath = sys_get_temp_dir() . DS . 'fzcdexpcm_' . substr(md5($fzcdExBase . "\0ex_mpcm_all"), 0, 16) . '.raw';
		if(!fractal_zip_flac_pac_decode_file_to_pcm_path($flacTmp, $entries[0]['fmt'], $pcmAllPath)) {
			@unlink($flacTmp);
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged FLAC→PCM.');
		}
		@unlink($flacTmp);
		$pcmAll = file_get_contents($pcmAllPath);
		@unlink($pcmAllPath);
		if($pcmAll === false) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: read merged PCM.');
		}
		if(strlen($pcmAll) !== $rawTotal) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: PCM length after merged FLAC.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfxTr = substr(md5($fzcdExBase . "\0ex_trkmfl\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . $sfxTr . '_p.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . $sfxTr . '.flac';
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $outFlac)) {
				@unlink($pcmTmp);
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM→FLAC failed.');
			}
			@unlink($pcmTmp);
			$flacData = file_get_contents($outFlac);
			@unlink($outFlac);
			if($flacData === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: read FLAC.');
			}
			$escaped = $this->escape_literal_for_storage($flacData);
			$unzipped = $this->unzip($escaped);
			$outPath = $rootDir . DS . str_replace('/', DS, $e['path']);
			$this->build_directory_structure_for($outPath);
			file_put_contents($outPath, $unzipped);
			if($debug) {
				print('Extracted ' . $outPath . '<br>');
			}
			$this->files_counter++;
			$this->array_fractal_zipped_strings_of_files[$e['path']] = '';
		}
	} elseif(($fzcdFlags & FRACTAL_ZIP_FZCD_FLAG_MERGED_GZIP) !== 0) {
		$nb = fread($fh, 16);
		if(strlen($nb) !== 16) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: gzip PCM sizes.');
		}
		$ur = unpack('Praw', substr($nb, 0, 8));
		$ug = unpack('Pgzl', substr($nb, 8, 8));
		if(!is_array($ur) || !is_array($ug) || !isset($ur['raw'], $ug['gzl'])) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: size unpack.');
		}
		$rawTotal = (int) $ur['raw'];
		$gzLen = (int) $ug['gzl'];
		$gzBody = $gzLen > 0 ? fread($fh, $gzLen) : '';
		if(strlen($gzBody) !== $gzLen) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD gzip body.');
		}
		$pcmAll = gzdecode($gzBody);
		if($pcmAll === false) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: gzdecode PCM.');
		}
		if(strlen($pcmAll) !== $rawTotal) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: PCM length mismatch.');
		}
		$at = 0;
		foreach($entries as $ti => $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$sfxGz = substr(md5($fzcdExBase . "\0ex_trkgz\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . $sfxGz . '_p.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . $sfxGz . '.flac';
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $outFlac)) {
				@unlink($pcmTmp);
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM→FLAC failed.');
			}
			@unlink($pcmTmp);
			$flacData = file_get_contents($outFlac);
			@unlink($outFlac);
			if($flacData === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: read FLAC.');
			}
			$escaped = $this->escape_literal_for_storage($flacData);
			$unzipped = $this->unzip($escaped);
			$outPath = $rootDir . DS . str_replace('/', DS, $e['path']);
			$this->build_directory_structure_for($outPath);
			file_put_contents($outPath, $unzipped);
			if($debug) {
				print('Extracted ' . $outPath . '<br>');
			}
			$this->files_counter++;
			$this->array_fractal_zipped_strings_of_files[$e['path']] = '';
		}
	} else {
		foreach($entries as $ti => $e) {
			$sfxRaw = substr(md5($fzcdExBase . "\0ex_trkraw\0" . (string) $ti), 0, 16);
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . $sfxRaw . '_p.raw';
			$pout = @fopen($pcmTmp, 'wb');
			if($pout === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$remain = $e['pcmLen'];
			while($remain > 0) {
				$take = $remain > 1048576 ? 1048576 : $remain;
				$b = fread($fh, $take);
				if($b === false || strlen($b) !== $take) {
					fclose($pout);
					fclose($fh);
					@unlink($pcmTmp);
					fractal_zip::fatal_error('Corrupt FZCD PCM.');
				}
				fwrite($pout, $b);
				$remain -= $take;
			}
			fclose($pout);
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . $sfxRaw . '.flac';
			if(!fractal_zip_flac_pac_pcm_file_to_flac_file($pcmTmp, $e['sr'], $e['ch'], $e['fmt'], $outFlac)) {
				@unlink($pcmTmp);
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM→FLAC failed.');
			}
			@unlink($pcmTmp);
			$flacData = file_get_contents($outFlac);
			@unlink($outFlac);
			if($flacData === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: read FLAC.');
			}
			$escaped = $this->escape_literal_for_storage($flacData);
			$unzipped = $this->unzip($escaped);
			$outPath = $rootDir . DS . str_replace('/', DS, $e['path']);
			$this->build_directory_structure_for($outPath);
			file_put_contents($outPath, $unzipped);
			if($debug) {
				print('Extracted ' . $outPath . '<br>');
			}
			$this->files_counter++;
			$this->array_fractal_zipped_strings_of_files[$e['path']] = '';
		}
	}
	$nOth = $this->read_varint_u32_from_stream($fh, 'FZCD');
	$prevPath = '';
	for($oi = 0; $oi < $nOth; $oi++) {
		$prefixLen = $this->read_varint_u32_from_stream($fh, 'FZCD');
		$suffixLen = $this->read_varint_u32_from_stream($fh, 'FZCD');
		if($prefixLen > strlen($prevPath)) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD other path prefix.');
		}
		$pathSuffix = $suffixLen > 0 ? fread($fh, $suffixLen) : '';
		if(strlen($pathSuffix) !== $suffixLen) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD other path suffix.');
		}
		$path = substr($prevPath, 0, $prefixLen) . $pathSuffix;
		$modeCh = fread($fh, 1);
		if($modeCh === false || strlen($modeCh) !== 1) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD other mode.');
		}
		$mode = ord($modeCh);
		$dataLen = $this->read_varint_u32_from_stream($fh, 'FZCD');
		$rawStored = $dataLen > 0 ? fread($fh, $dataLen) : '';
		if(strlen($rawStored) !== $dataLen) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZCD other data.');
		}
		$raw = $this->decode_bundle_literal_member($mode, $rawStored);
		$escaped = $this->escape_literal_for_storage($raw);
		$unzipped = $this->unzip($escaped);
		$outPath = $rootDir . DS . str_replace('/', DS, $path);
		$this->build_directory_structure_for($outPath);
		file_put_contents($outPath, $unzipped);
		if($debug) {
			print('Extracted ' . $outPath . '<br>');
		}
		$this->files_counter++;
		$this->array_fractal_zipped_strings_of_files[$path] = '';
		$prevPath = $path;
	}
	$at = ftell($fh);
	fseek($fh, 0, SEEK_END);
	$sz = ftell($fh);
	if($at !== $sz) {
		fclose($fh);
		fractal_zip::fatal_error('Corrupt FZCD (trailing bytes).');
	}
	fclose($fh);
	if($debug) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
	}
	return true;
}

function stream_literal_bundle_fzb4_to_file($dir, $outPath, $rawLiteralsOnly = false) {
	$root = realpath($dir);
	if($root === false) {
		$root = $dir;
	}
	$prefixLen = strlen($root);
	$paths = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach($it as $fileInfo) {
		if(!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, $prefixLen + 1);
		$rel = strtr((string) $rel, '\\', '/');
		if(strlen($rel) > 65535) {
			return false;
		}
		$paths[] = $rel;
	}
	sort($paths, SORT_STRING);
	if(!$rawLiteralsOnly && $this->write_fzcd_bundle_if_applicable($root, $paths, $outPath)) {
		return true;
	}
	$fh = fopen($outPath, 'wb');
	if($fh === false) {
		return false;
	}
	if(fwrite($fh, 'FZB4') === false) {
		fclose($fh);
		return false;
	}
	fractal_zip_ensure_literal_pac_stack_loaded();
	$prevPath = '';
	foreach($paths as $path) {
		$path = (string) $path;
		$full = $root . DS . str_replace('/', DS, $path);
		$rawBytes = file_get_contents($full);
		if($rawBytes === false) {
			$rawBytes = '';
		}
		if($rawLiteralsOnly) {
			list($work, $semanticLayers, $gzipStack) = fractal_zip_literal_deep_unwrap_with_layers($path, $rawBytes);
			list($mode, $storeBytes) = fractal_zip_literal_bundle_wrap_all_layers($semanticLayers, $gzipStack, 0, $work);
		} else {
			list($mode, $storeBytes) = $this->choose_best_literal_bundle_transform($rawBytes, $path);
		}
		$commonPrefixLen = 0;
		$maxPrefix = min(strlen($prevPath), strlen($path), 65535);
		while($commonPrefixLen < $maxPrefix && $prevPath[$commonPrefixLen] === $path[$commonPrefixLen]) {
			$commonPrefixLen++;
		}
		$pathSuffix = (string) substr($path, $commonPrefixLen);
		if(strlen($pathSuffix) > 65535) {
			fclose($fh);
			return false;
		}
		$chunk = $this->encode_varint_u32($commonPrefixLen) . $this->encode_varint_u32(strlen($pathSuffix)) . $pathSuffix
			. chr($mode) . $this->encode_varint_u32(strlen($storeBytes)) . $storeBytes;
		if(fwrite($fh, $chunk) === false) {
			fclose($fh);
			return false;
		}
		$prevPath = $path;
	}
	fclose($fh);
	return true;
}

/**
 * Raw deflate level for large-folder gzip-fast .fzc (streaming FZB4 inner). FRACTAL_ZIP_FOLDER_GZIP_FAST_DEFLATE_LEVEL 1–9 (default 6: faster roundtrip on huge trees; 9 = smallest).
 */
public static function folder_gzip_fast_deflate_level(): int {
	$e = getenv('FRACTAL_ZIP_FOLDER_GZIP_FAST_DEFLATE_LEVEL');
	if($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 6;
	}
	return max(1, min(9, (int) $e));
}

/**
 * When > 0 and streamed FZB4 inner is ≤ this size, gzip-fast uses adaptive_compress (+ raw contest) instead of deflate-only.
 * 0 = always deflate-only. FRACTAL_ZIP_FOLDER_GZIP_FAST_ADAPTIVE_OUTER_MAX_BYTES (default 64 MiB).
 */
public static function folder_gzip_fast_adaptive_outer_max_bytes(): int {
	$e = getenv('FRACTAL_ZIP_FOLDER_GZIP_FAST_ADAPTIVE_OUTER_MAX_BYTES');
	if($e === false || trim((string) $e) === '') {
		return 64 * 1024 * 1024;
	}
	return max(0, (int) $e);
}

/**
 * fread chunk size for streaming deflate/inflate (gzip-fast folder path). FRACTAL_ZIP_STREAM_CHUNK_BYTES (default 1048576; min 65536, max 67108864).
 */
public static function stream_chunk_bytes(): int {
	$e = getenv('FRACTAL_ZIP_STREAM_CHUNK_BYTES');
	if($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 1048576;
	}
	return max(65536, min(67108864, (int) $e));
}

/** Stream raw deflate (ZLIB_ENCODING_RAW) from file to path — level 1–9 or default 9 when $level null (non–folder-fast callers). */
function gzip_deflate_file_to_path($innerPath, $outPath, $level = null) {
	$lev = $level !== null ? max(1, min(9, (int) $level)) : 9;
	if(!function_exists('deflate_init')) {
		$data = file_get_contents($innerPath);
		if($data === false) {
			return false;
		}
		$c = gzdeflate($data, $lev);
		if($c === false) {
			return false;
		}
		return file_put_contents($outPath, $c) !== false;
	}
	$in = fopen($innerPath, 'rb');
	if($in === false) {
		return false;
	}
	$out = fopen($outPath, 'wb');
	if($out === false) {
		fclose($in);
		return false;
	}
	$ctx = deflate_init(ZLIB_ENCODING_RAW, array('level' => $lev));
	if($ctx === false) {
		fclose($in);
		fclose($out);
		return false;
	}
	$readChunk = fractal_zip::stream_chunk_bytes();
	while(true) {
		$chunk = fread($in, $readChunk);
		if($chunk === false) {
			fclose($in);
			fclose($out);
			return false;
		}
		$last = feof($in);
		$fl = $last ? ZLIB_FINISH : ZLIB_NO_FLUSH;
		$enc = deflate_add($ctx, $chunk, $fl);
		if($enc === false) {
			fclose($in);
			fclose($out);
			return false;
		}
		if($enc !== '' && fwrite($out, $enc) === false) {
			fclose($in);
			fclose($out);
			return false;
		}
		if($last) {
			break;
		}
	}
	fclose($in);
	fclose($out);
	return true;
}

/**
 * Write .fzc as raw FZB4 (no outer compression). Fast for huge directories when ratio is secondary.
 * @return bool false on I/O failure
 */
function write_folder_literal_fzb4_store_only_container($dir) {
	fractal_zip::$used_folder_gzip_fast = false;
	fractal_zip::$used_folder_literal_fzb4_store = false;
	$resolvedRoot = realpath($dir);
	$this->zip_folder_root_for_members = $resolvedRoot !== false ? $resolvedRoot : $dir;
	$fzcPath = $dir . $this->fractal_zip_container_file_extension;
	if(!$this->stream_literal_bundle_fzb4_to_file($dir, $fzcPath, true)) {
		$this->zip_folder_root_for_members = '';
		return false;
	}
	fractal_zip::$last_outer_codec = 'store';
	fractal_zip::$last_written_container_codec = 'store';
	fractal_zip::$folder_zip_wire_best_outer_codec = 'store';
	fractal_zip::$used_folder_literal_fzb4_store = true;
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	fractal_zip::html_trace_print('literal FZB4 store-only .fzc (no outer compression).<br>');
	fractal_zip::html_trace_print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
	$this->zip_folder_root_for_members = '';
	return true;
}

function zip_folder($dir, $debug = false) {
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_begin();
	fractal_zip_encode_pipeline::pipeline_wall_timer_zip_folder_begin();
	try {
	fractal_zip::html_trace_print('Fractal zipping folder: ' . $dir . '<br>');
	$this->reset_folder_bundle_structure_scan_state();
	fractal_zip::$used_folder_gzip_fast = false;
	fractal_zip::$used_folder_literal_fzb4_store = false;
	fractal_zip::$used_folder_unified_stream = false;
	fractal_zip::$folder_zip_wire_best_outer_codec = null;
	fractal_zip_encode_pipeline::html_trace_checkpoint(
		fractal_zip_encode_pipeline::PHASE_UNPEEL,
		'zip_folder: begin (member peel/preprocess varies by branch)'
	);
	if(fractal_zip::folder_literal_fzb4_store_only_requested()) {
		if($this->write_folder_literal_fzb4_store_only_container($dir)) {
			return;
		}
		fractal_zip::warning_once('literal FZB4 store-only path failed; falling back to normal zip_folder');
	}
	// Large-folder fast path: literal FZB4 bundle + raw deflate outer like adaptive_compress gzip baseline (no fractal). Benchmarks
	// set FRACTAL_ZIP_FOLDER_GZIP_FAST=1 for heavy corpora; auto also when raw size >= FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES.
	if($this->should_use_large_folder_gzip_fast_path($dir)) {
		fractal_zip_encode_pipeline::html_trace_checkpoint(
			fractal_zip_encode_pipeline::PHASE_STREAM_BUILD,
			'gzip-fast: stream FZB inner + outer (same phase-2 stream build slot as unified)'
		);
		$resolvedRoot = realpath($dir);
		$this->zip_folder_root_for_members = $resolvedRoot !== false ? $resolvedRoot : $dir;
		$fzbTmpRoot = $resolvedRoot !== false ? $resolvedRoot : (string) $dir;
		$tmpInner = sys_get_temp_dir() . DS . 'fzfzb_' . substr(md5(fractal_zip::hot_string_digest($fzbTmpRoot) . "\0fzfzb\0" . (string) spl_object_id($this)), 0, 16);
		if($this->stream_literal_bundle_fzb4_to_file($dir, $tmpInner)) {
			$fzcPath = $dir . $this->fractal_zip_container_file_extension;
			$gzipFastDone = false;
			$sz = @filesize($tmpInner);
			$maxAd = fractal_zip::folder_gzip_fast_adaptive_outer_max_bytes();
			if($maxAd > 0 && is_int($sz) && $sz > 0 && $sz <= $maxAd) {
				$innerBlob = file_get_contents($tmpInner);
				if($innerBlob !== false && $innerBlob !== '') {
					$rawFilesByPath = $this->collect_raw_files_for_bundle($dir);
					list($bestContents, $gfPick) = $this->choose_smallest_adaptive_literal_inner_or_raw_escaped(
						array(array('inner' => $innerBlob, 'tag' => 'stream_literal')),
						$rawFilesByPath
					);
					$fzcBody = $this->append_fractal_gzip_peel_restore_trailer($bestContents, $this->fractal_member_gzip_disk_restore);
					if(file_put_contents($fzcPath, $fzcBody) !== false) {
						@unlink($tmpInner);
						$gzipFastDone = true;
						fractal_zip::$used_folder_gzip_fast = true;
						fractal_zip::$folder_zip_wire_best_outer_codec = fractal_zip::$last_written_container_codec;
						$micro_time_taken = microtime(true) - $this->initial_micro_time;
						fractal_zip::html_trace_print('large-folder gzip-fast path: streamed literal inner + phase-3 adaptive outer (inner ≤ ' . $maxAd . ' B; pick=' . htmlspecialchars($gfPick) . ').<br>');
						fractal_zip::html_trace_print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
						$this->zip_folder_root_for_members = '';
						return;
					}
				}
			}
			if(!$gzipFastDone && is_file($tmpInner)) {
				$dlev = fractal_zip::folder_gzip_fast_deflate_level();
				if($this->gzip_deflate_file_to_path($tmpInner, $fzcPath, $dlev)) {
					@unlink($tmpInner);
					fractal_zip::$last_outer_codec = 'gzip';
					fractal_zip::$last_written_container_codec = 'gzip';
					fractal_zip::$folder_zip_wire_best_outer_codec = 'gzip';
					fractal_zip::$used_folder_gzip_fast = true;
					$micro_time_taken = microtime(true) - $this->initial_micro_time;
					fractal_zip::html_trace_print('large-folder gzip-fast path (streaming literal inner + raw deflate-' . $dlev . ' outer).<br>');
					fractal_zip::html_trace_print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
					$this->zip_folder_root_for_members = '';
					return;
				}
			}
		}
		if(is_file($tmpInner)) {
			@unlink($tmpInner);
		}
		fractal_zip::warning_once('large-folder gzip-fast path failed; falling back to full zip_folder');
	}
	// Unified stream: phase 1–2 literal inners (FZCD when applicable, else FZB* from encode_literal_bundle_payload); phase 3 smallest vs raw.
	if(fractal_zip::folder_unified_stream_enabled()) {
		fractal_zip_encode_pipeline::html_trace_checkpoint(
			fractal_zip_encode_pipeline::PHASE_STREAM_BUILD,
			'unified stream: literal variants + byte stream'
		);
		$resolvedRoot = realpath($dir);
		$this->zip_folder_root_for_members = $resolvedRoot !== false ? $resolvedRoot : $dir;
		$rootForCollect = $resolvedRoot !== false ? $resolvedRoot : $dir;
		$rawFilesByPath = $this->collect_raw_files_for_bundle($dir);
		$variants = fractal_zip::folder_literal_variants_for_unified_stream(
			$this->collect_literal_bundle_inner_variants_for_folder($rootForCollect, $rawFilesByPath)
		);
		if($variants !== array()) {
			list($bestContents, $unifiedPick) = $this->choose_smallest_adaptive_literal_inner_or_raw_escaped($variants, $rawFilesByPath);
			$fzcBody = $this->append_fractal_gzip_peel_restore_trailer($bestContents, $this->fractal_member_gzip_disk_restore);
			$sumRawUnified = 0;
			foreach($rawFilesByPath as $rb) {
				$sumRawUnified += strlen((string) $rb);
			}
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
			$fzcPassthrough = $this->maybe_folder_freearc_native_smaller_than_fzc($rootForCollect, $fzcBody, $sumRawUnified, $rawFilesByPath);
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_unified_stream_maybe_freearc_native_compare');
			if($fzcPassthrough !== null) {
				$fzcBody = $fzcPassthrough;
				$unifiedPick = 'arc_native';
			}
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
			$fzcZpaqNative = $this->maybe_folder_zpaq_native_smaller_than_fzc($rootForCollect, $fzcBody, $rawFilesByPath);
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_unified_stream_maybe_zpaq_native_compare');
			if($fzcZpaqNative !== null) {
				$fzcBody = $fzcZpaqNative;
				$unifiedPick = 'zpaq_native';
			}
			file_put_contents($dir . $this->fractal_zip_container_file_extension, $fzcBody);
			fractal_zip::$used_folder_unified_stream = true;
			fractal_zip::$folder_zip_wire_best_outer_codec = ($unifiedPick === 'arc_native')
				? 'arc'
				: (($unifiedPick === 'zpaq_native')
					? 'zpaq'
					: (string) fractal_zip::$last_written_container_codec);
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			if($unifiedPick === 'arc_native') {
				fractal_zip::html_trace_print('unified stream path: raw native FreeArc folder archive beat fractal inner + outer on wire bytes (same bytes as min-ext Arc).<br>');
			} elseif($unifiedPick === 'raw') {
				fractal_zip::html_trace_print('unified stream path: raw escaped-per-file beat literal candidate(s) after adaptive outer.<br>');
			} elseif($unifiedPick === 'fzcd') {
				fractal_zip::html_trace_print('unified stream path: FZCD merged-FLAC inner won vs raw after adaptive outer.<br>');
			} elseif($unifiedPick === 'fzb') {
				fractal_zip::html_trace_print('unified stream path: FZB literal inner (unwrap + transforms; FZBD auto; optional FZWS) won vs raw after adaptive outer.<br>');
			} elseif($unifiedPick === 'zpaq_native') {
				fractal_zip::html_trace_print('unified stream path: native zpaq folder archive beat wrapped inner on wire bytes.<br>');
			} else {
				fractal_zip::html_trace_print('unified stream path: literal inner tag=' . htmlspecialchars($unifiedPick) . ' after adaptive outer.<br>');
			}
			fractal_zip::html_trace_print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
			$this->zip_folder_root_for_members = '';
			return;
		}
		fractal_zip::warning_once('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM: no literal inner candidates; falling back to standard zip_folder');
	}
	// Single-file / tiny-file-count shapes that use bundle-only below: skip auto segment/multipass probes (same decision
	// as should_use_bundle_only_for_folder; avoids scanning a large tree only to take the literal-bundle path).
	$bundleOnlyByShape = $this->should_use_bundle_only_for_folder($dir);
	// Speed budget mode: prioritize finishing quickly over deep multipass search.
	$budgetMs = getenv('FRACTAL_ZIP_TIME_BUDGET_MS');
	if($budgetMs !== false && trim((string)$budgetMs) !== '' && is_numeric($budgetMs) && (float)$budgetMs > 0) {
		if(getenv('FRACTAL_ZIP_FORCE_MULTIPASS') !== '1') {
			$this->multipass = false;
		}
		// Avoid expensive auto-tune probes under a hard time budget.
		$this->auto_tune_compression = false;
		$this->auto_segment_selection_enabled = false;
		$this->auto_multipass_selection_enabled = false;
	}
	// debug mode should leave the uncompressed fractal-zipped data to be worked upon by other programs (music)
	//$files_created = array();
	if(!$bundleOnlyByShape && !$this->auto_segment_selection_in_progress && ($this->auto_tune_compression || $this->auto_segment_selection_enabled)) {
		$this->auto_segment_selection_in_progress = true;
		if($this->auto_tune_compression) {
			$this->choose_best_compression_tuning_for_folder($dir, $debug);
		} else {
			$this->segment_length = $this->choose_best_segment_length_for_folder($dir, $debug);
			fractal_zip::html_trace_print('Auto-selected segment length: ' . $this->segment_length . '<br>');
		}
		ini_set('xdebug.max_nesting_level', $this->segment_length + 30);
		$this->auto_segment_selection_in_progress = false;
	}
	if(!$bundleOnlyByShape && !$this->auto_multipass_selection_in_progress && $this->auto_multipass_selection_enabled && !$this->auto_segment_selection_in_progress) {
		$this->auto_multipass_selection_in_progress = true;
		$this->multipass = $this->choose_best_multipass_for_folder($dir, $debug);
		$mpMax = $this->multipass_max_additional_passes;
		$mpMaxStr = ($mpMax === null) ? 'unlimited' : (string) (int) $mpMax;
		fractal_zip::html_trace_print('Auto-selected multipass: ' . ($this->multipass ? ('on (max extra passes ' . $mpMaxStr . ')') : 'off') . '<br>');
		$this->auto_multipass_selection_in_progress = false;
	}
	$resolvedRoot = realpath($dir);
	$this->zip_folder_root_for_members = $resolvedRoot !== false ? $resolvedRoot : $dir;
	if($bundleOnlyByShape) {
		fractal_zip_encode_pipeline::html_trace_checkpoint(
			fractal_zip_encode_pipeline::PHASE_STREAM_BUILD,
			'bundle-only shape: literal inner variants + byte stream'
		);
		$rawFilesByPath = $this->collect_raw_files_for_bundle($dir);
		$variants = fractal_zip::folder_literal_variants_for_unified_stream(
			$this->collect_literal_bundle_inner_variants_for_folder($this->zip_folder_root_for_members, $rawFilesByPath)
		);
		if($variants !== array()) {
			list($bestContents, $shapePick) = $this->choose_smallest_adaptive_literal_inner_or_raw_escaped($variants, $rawFilesByPath);
			$fzcBody = $this->append_fractal_gzip_peel_restore_trailer($bestContents, $this->fractal_member_gzip_disk_restore);
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
			$fzcZpaqNative = $this->maybe_folder_zpaq_native_smaller_than_fzc($this->zip_folder_root_for_members, $fzcBody, $rawFilesByPath);
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_bundle_only_maybe_zpaq_native_compare');
			if($fzcZpaqNative !== null) {
				$fzcBody = $fzcZpaqNative;
				$shapePick = 'zpaq_native';
			}
			file_put_contents($dir . $this->fractal_zip_container_file_extension, $fzcBody);
			fractal_zip::$folder_zip_wire_best_outer_codec = ($shapePick === 'zpaq_native')
				? 'zpaq'
				: (string) fractal_zip::$last_written_container_codec;
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			if($shapePick === 'raw') {
				fractal_zip::html_trace_print('bundle-only shape path: raw escaped-per-file beat literal candidate(s) after adaptive outer.<br>');
			} elseif($shapePick === 'fzcd') {
				fractal_zip::html_trace_print('bundle-only shape path: FZCD inner won vs raw after adaptive outer.<br>');
			} elseif($shapePick === 'fzb') {
				fractal_zip::html_trace_print('bundle-only shape path: FZB literal inner won vs raw after adaptive outer.<br>');
			} elseif($shapePick === 'zpaq_native') {
				fractal_zip::html_trace_print('bundle-only shape path: native zpaq folder archive beat wrapped inner on wire bytes.<br>');
			} else {
				fractal_zip::html_trace_print('bundle-only shape path: pick=' . htmlspecialchars($shapePick) . ' after adaptive outer.<br>');
			}
			fractal_zip::html_trace_print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
			$this->zip_folder_root_for_members = '';
			return;
		}
	}
	$this->fractal_member_gzip_disk_restore = array();
	$this->raster_canonical_hash_to_lazy_range = array();
	fractal_zip_encode_pipeline::html_trace_checkpoint(
		fractal_zip_encode_pipeline::PHASE_TRANSFORMS_AND_MODES,
		'legacy path: create_fractal_zip_markers + segment transforms (then outer in adaptive_compress / multipass)'
	);
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
	$this->create_fractal_zip_markers($dir, $debug);
	$this->recursive_zip_folder($dir, $debug);
	
	// more passes on the fractal zipped strings
	// perhaps this code could be adopted for the initial pass as well once we are already going through all the strings before-hand to figure out what would be good markers... or a hybrid? 
	// meh, it comes down to a performance versus compression consideration; a nice balance must be sought and this is done by limiting the scope of the string comparison (segment_length) so that it may take multiple passes
	// and be compressed slightly less but will finish in a reasonable amount of time.
	// currently (2017-05-11) takes twenty times longer and makes a worse result..... always surprising. maybe effort should be redirected to endeavoring to keep "content" parts of templated content together

	$this->run_fractal_multipass_equivalence_passes($debug);
	
	// save the necessary arrays as a fractal_zip file
	if($debug) {
		print('$this->fractal_string, $this->equivalences, $this->branch_counter after zipping all files: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
	}
//	print('debug stop before serialization');exit(0);
//	fractal_zip::clean_arrays_before_serialization();
	$this->array_fractal_zipped_strings_of_files = array();
	//print('$this->equivalences: ');var_dump($this->equivalences);
	foreach($this->equivalences as $equivalence) {
		//print('um34749<br>');
		$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	//$fzc_contents = serialize(array($this->array_fractal_zipped_strings_of_files, $this->fractal_strings));
	/*$fzc_contents = serialize(array(
	$this->array_fractal_zipped_strings_of_files, 
	$this->multipass, 
	$this->branch_counter, 
	$this->left_fractal_zip_marker, 
	$this->mid_fractal_zip_marker, 
	$this->right_fractal_zip_marker, 
	$this->fractal_string
	));*/
	$fzc_contents = $this->append_fractal_gzip_peel_restore_trailer(
		$this->encode_container_payload($this->array_fractal_zipped_strings_of_files, $this->fractal_string),
		$this->fractal_member_gzip_disk_restore
	);
	if($debug) {
		print('$this->array_fractal_zipped_strings_of_files: ');fractal_zip::var_dump_full($this->array_fractal_zipped_strings_of_files);
		print('$fzc_contents before compression: ');fractal_zip::var_dump_full($fzc_contents);
	}
	$lazy_array_fractal_zipped_strings_of_files = array();
	foreach($this->lazy_equivalences as $equivalence) {
		$lazy_array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	//$lazy_fzc_contents = serialize(array($lazy_array_fractal_zipped_strings_of_files, $this->lazy_fractal_strings));
	/*$lazy_fzc_contents = serialize(array(
	$lazy_array_fractal_zipped_strings_of_files, 
	$this->multipass, 
	$this->branch_counter, 
	$this->left_fractal_zip_marker, 
	$this->mid_fractal_zip_marker, 
	$this->right_fractal_zip_marker, 
	$this->lazy_fractal_string
	));*/
	$lazy_fzc_contents = $this->append_fractal_gzip_peel_restore_trailer(
		$this->encode_container_payload($lazy_array_fractal_zipped_strings_of_files, $this->lazy_fractal_string),
		$this->fractal_member_gzip_disk_restore
	);
	$raw_array_fractal_zipped_strings_of_files = array();
	$raw_array_files_by_path = array();
	foreach($this->equivalences as $equivalence) {
		$raw_array_fractal_zipped_strings_of_files[$equivalence[1]] = $this->escape_literal_for_storage($equivalence[0]);
		$raw_array_files_by_path[$equivalence[1]] = (string)$equivalence[0];
	}
	$raw_payload_uncompressed = $this->encode_container_payload($raw_array_fractal_zipped_strings_of_files, '');
	$literal_variants = $this->collect_literal_bundle_inner_variants_for_folder($this->zip_folder_root_for_members, $raw_array_files_by_path);
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_legacy_markers_multipass_encode_collect_literals');
	$literal_best_contents = '';
	$literal_best_tag = '';
	$codecLiteral = fractal_zip::$last_outer_codec;
	$codecFractal = fractal_zip::$last_outer_codec;
	$codecLazy = fractal_zip::$last_outer_codec;
	$wireWinner = 'fractal';
	if(!$debug) {
		$fastStop = fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE;
		$litFastLen = PHP_INT_MAX;
		$overlap = null;
		$do_literal_overlap_stagger = fractal_zip_encode_pipeline::parallel_zip_folder_literal_overlap_stagger_fast_lens_eligible($literal_variants, $fzc_contents, $lazy_fzc_contents);
		if($do_literal_overlap_stagger) {
			$overlap = fractal_zip_encode_pipeline::parallel_zip_folder_overlap_literal_contest_and_wire_fast_lens(
				$this,
				$literal_variants,
				$raw_array_files_by_path,
				$fzc_contents,
				$lazy_fzc_contents
			);
		}
		if($overlap !== null) {
			$literal_best_contents = $overlap['literal_blob'];
			$literal_best_tag = $overlap['literal_tag'];
			$codecLiteral = $overlap['literal_codec'];
			$this->fractal_member_gzip_disk_restore = $overlap['gzip_restore'];
			$litFastLen = $overlap['litFastLen'];
			$frFastLen = $overlap['frFastLen'];
			$lzFastLen = $overlap['lzFastLen'];
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_parallel_overlap_literal_wire_fast_lens');
		} else {
			$stagger = null;
			if($do_literal_overlap_stagger) {
				$stagger = fractal_zip_encode_pipeline::parallel_zip_folder_stagger_literal_contest_fr_lazy_fast_lens(
					$this,
					$literal_variants,
					$raw_array_files_by_path,
					$fzc_contents,
					$lazy_fzc_contents
				);
			}
			if($stagger !== null) {
				$literal_best_contents = $stagger['literal_blob'];
				$literal_best_tag = $stagger['literal_tag'];
				$codecLiteral = $stagger['literal_codec'];
				$this->fractal_member_gzip_disk_restore = $stagger['gzip_restore'];
				$litFastLen = $stagger['litFastLen'];
				$frFastLen = $stagger['frFastLen'];
				$lzFastLen = $stagger['lzFastLen'];
			} elseif($literal_variants !== array()) {
				list($literal_best_contents, $literal_best_tag) = $this->choose_smallest_adaptive_literal_inner_or_raw_escaped($literal_variants, $raw_array_files_by_path);
				$codecLiteral = fractal_zip::$last_written_container_codec;
				fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
				$parLens = fractal_zip_encode_pipeline::parallel_zip_folder_wire_fast_outer_lens(
					$this,
					($literal_best_contents !== '') ? $literal_best_contents : '',
					$fzc_contents,
					$lazy_fzc_contents
				);
				if($parLens !== null) {
					$litFastLen = $parLens['litFastLen'];
					$frFastLen = $parLens['frFastLen'];
					$lzFastLen = $parLens['lzFastLen'];
					fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_parallel_wire_fast_outer_lens_fork_wait');
				} else {
					if($literal_best_contents !== '') {
						$litProbe = $this->adaptive_compress($literal_best_contents, $fastStop);
						$litFastLen = strlen($litProbe);
						fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
					}
					$frProbe = $this->adaptive_compress($fzc_contents, $fastStop);
					$frFastLen = strlen($frProbe);
					fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
					if($fzc_contents === $lazy_fzc_contents) {
						$lzFastLen = $frFastLen;
					} else {
						$lzProbe = $this->adaptive_compress($lazy_fzc_contents, $fastStop);
						$lzFastLen = strlen($lzProbe);
						fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
					}
				}
			} else {
				$parLens = fractal_zip_encode_pipeline::parallel_zip_folder_wire_fast_outer_lens(
					$this,
					'',
					$fzc_contents,
					$lazy_fzc_contents
				);
				if($parLens !== null) {
					$litFastLen = $parLens['litFastLen'];
					$frFastLen = $parLens['frFastLen'];
					$lzFastLen = $parLens['lzFastLen'];
					fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_parallel_wire_fast_outer_lens_fork_wait');
				} else {
					$frProbe = $this->adaptive_compress($fzc_contents, $fastStop);
					$frFastLen = strlen($frProbe);
					fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
					if($fzc_contents === $lazy_fzc_contents) {
						$lzFastLen = $frFastLen;
					} else {
						$lzProbe = $this->adaptive_compress($lazy_fzc_contents, $fastStop);
						$lzFastLen = strlen($lzProbe);
						fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
					}
				}
			}
		}
		$bestFast = $frFastLen;
		if($lzFastLen < $bestFast) {
			$wireWinner = 'lazy';
			$bestFast = $lzFastLen;
		}
		if($literal_variants !== array() && $literal_best_contents !== '' && $litFastLen < $bestFast) {
			$wireWinner = 'literal';
		}
		fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_wire_fast_lens_pick_before_full_outer');
		if($wireWinner === 'fractal') {
			if($fzc_contents === $lazy_fzc_contents) {
				$fzc_contents = $this->adaptive_compress($fzc_contents);
				$codecFractal = fractal_zip::$last_outer_codec;
				$lazy_fzc_contents = $fzc_contents;
				$codecLazy = $codecFractal;
				fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
			} else {
				$fzc_contents = $this->adaptive_compress($fzc_contents);
				$codecFractal = fractal_zip::$last_outer_codec;
				fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
			}
		} elseif($wireWinner === 'lazy') {
			$lazy_fzc_contents = $this->adaptive_compress($lazy_fzc_contents);
			$codecLazy = fractal_zip::$last_outer_codec;
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
		}
	} else {
	if($literal_variants !== array()) {
		list($literal_best_contents, $literal_best_tag) = $this->choose_smallest_adaptive_literal_inner_or_raw_escaped($literal_variants, $raw_array_files_by_path);
		$codecLiteral = fractal_zip::$last_written_container_codec;
	}
	if($debug) {
		print('$lazy_array_fractal_zipped_strings_of_files: ');fractal_zip::var_dump_full($lazy_array_fractal_zipped_strings_of_files);
		print('$lazy_fzc_contents before compression: ');fractal_zip::var_dump_full($lazy_fzc_contents);
		print('$raw_array_fractal_zipped_strings_of_files: ');fractal_zip::var_dump_full($raw_array_fractal_zipped_strings_of_files);
		print('$raw_fzc_contents before compression: ');fractal_zip::var_dump_full($raw_payload_uncompressed);
	}
	// gzip or LZMA?
	// http://us.php.net/manual/en/function.gzdeflate.php
	/* gzcompress produces longer data because it embeds information about the encoding onto the string. If you are compressing data that will only ever be handled on one machine, then you don't need 
	to worry about which of these functions you use. However, if you are passing data compressed with these functions to a different machine you should use gzcompress.	*/
	//$fzc_contents = gzcompress($fzc_contents, 9);
	//$fzc_contents = gzcompress($fzc_contents);
	//$fzc_contents = gzencode($fzc_contents, 9);
	//$fzc_contents = gzdeflate($fzc_contents, 9);
	//$fzc_contents = bzcompress($fzc_contents);
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents, 9);
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents);
	//file_put_contents('php_info_fractal_zip_contents.txt', $fzc_contents);
	if($debug) {
		$fzc_contents = $this->adaptive_compress($fzc_contents);
		$codecFractal = fractal_zip::$last_outer_codec;
		$lazy_fzc_contents = $this->adaptive_compress($lazy_fzc_contents);
		$codecLazy = fractal_zip::$last_outer_codec;
	} elseif($fzc_contents === $lazy_fzc_contents) {
		$fzc_contents = $this->adaptive_compress($fzc_contents);
		$codecFractal = fractal_zip::$last_outer_codec;
		$lazy_fzc_contents = $fzc_contents;
		$codecLazy = $codecFractal;
	} else {
			$fzc_contents = $this->adaptive_compress($fzc_contents);
			$codecFractal = fractal_zip::$last_outer_codec;
			$lazy_fzc_contents = $this->adaptive_compress($lazy_fzc_contents);
			$codecLazy = fractal_zip::$last_outer_codec;
	}
	}
	if($debug) {
		print('$fzc_contents: ');var_dump($fzc_contents);
		print('$lazy_fzc_contents: ');var_dump($lazy_fzc_contents);
		$raw_dbg = $this->adaptive_compress($raw_payload_uncompressed);
		print('$raw_fzc_contents: ');var_dump($raw_dbg);
		print('$literal_best tag, len: ');var_dump($literal_best_tag, strlen($literal_best_contents));
	}
	//$last_folder_name = substr($dir, fractal_zip::strpos_last($dir, DS));
	//print('$dir, $last_folder_name: ');var_dump($dir, $last_folder_name);
	if(!$debug) {
		$bestLabel = $wireWinner;
		if($bestLabel === 'literal') {
			$bestPayload = $literal_best_contents;
			$bestCodec = $codecLiteral;
		} elseif($bestLabel === 'fractal') {
			$bestPayload = $fzc_contents;
			$bestCodec = $codecFractal;
		} else {
			$bestPayload = $lazy_fzc_contents;
			$bestCodec = $codecLazy;
		}
		$fzcLen = strlen($fzc_contents);
		$lazyLen = strlen($lazy_fzc_contents);
		$litTxt = 'n/a';
		if($literal_variants !== array() && $literal_best_contents !== '') {
			$litTxt = (string) strlen($literal_best_contents);
		}
	} else {
	$bestLabel = 'fractal';
	$bestPayload = $fzc_contents;
	$bestCodec = $codecFractal;
	$fzcLen = strlen($fzc_contents);
	$lazyLen = strlen($lazy_fzc_contents);
	$bestLen = $fzcLen;
	if($lazyLen < $bestLen) {
		$bestLabel = 'lazy';
		$bestPayload = $lazy_fzc_contents;
		$bestCodec = $codecLazy;
		$bestLen = $lazyLen;
	}
	$litTxt = 'n/a';
	if($literal_variants !== array() && $literal_best_contents !== '') {
		$litLen = strlen($literal_best_contents);
		if($litLen < $bestLen) {
			$bestLabel = 'literal';
			$bestPayload = $literal_best_contents;
			$bestCodec = $codecLiteral;
			$bestLen = $litLen;
		}
		$litTxt = (string) $litLen;
	}
	}
	fractal_zip::$folder_zip_wire_best_outer_codec = $bestCodec;
	fractal_zip::$last_written_container_codec = $bestCodec;
	if($bestLabel === 'fractal') {
		fractal_zip::html_trace_print('<span style="color: green;">fractal zipping was actually useful (' . $fzcLen . ' &#8804; min(' . $lazyLen . ', literal_best=' . $litTxt . '))!</span><br>');
	} elseif($bestLabel === 'lazy') {
		fractal_zip::html_trace_print('simply compressing the strings made the smallest file among fractal/lazy/literal contest.<br>');
	} else {
		fractal_zip::html_trace_print('literal contest won (FZCD/FZB/FZBM/FZBF/raw vs fractal/lazy); tag=' . htmlspecialchars($literal_best_tag) . '.<br>');
	}
	$writePayload = $bestPayload;
	if($bestLabel === 'literal' && $literal_best_contents !== '') {
		$writePayload = $this->append_fractal_gzip_peel_restore_trailer($literal_best_contents, $this->fractal_member_gzip_disk_restore);
	}
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_finalize_write_payload_trailer');
	file_put_contents($dir . $this->fractal_zip_container_file_extension, $writePayload);
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('zip_folder_fzc_file_put_contents');
	fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	fractal_zip::html_trace_print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
	$this->zip_folder_root_for_members = '';
	} finally {
		fractal_zip_encode_pipeline::pipeline_wall_timer_zip_folder_end();
		fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_end();
	}
}

function should_use_bundle_only_for_folder($dir) {
	$maxFilesEnv = getenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
	$minFilesForBundleOnly = ($maxFilesEnv === false || trim((string)$maxFilesEnv) === '') ? 256 : max(16, (int)$maxFilesEnv);
	$singleFileMinBytesEnv = getenv('FRACTAL_ZIP_BUNDLE_ONLY_SINGLE_FILE_MIN_BYTES');
	// Default 5 MiB (FRACTAL_ZIP_BUNDLE_ONLY_SINGLE_FILE_MIN_BYTES): smaller single-file trees use full fractal_zip; larger ones use literal bundle only.
	$singleFileMinBytes = ($singleFileMinBytesEnv === false || trim((string)$singleFileMinBytesEnv) === '') ? 5242880 : max(0, (int)$singleFileMinBytesEnv);
	$fileCount = 0;
	$totalBytes = 0;
	$largestFileBytes = 0;
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	foreach($it as $fileInfo) {
		if(!$fileInfo->isFile()) {
			continue;
		}
		$fileCount++;
		$sz = $fileInfo->getSize();
		if($sz !== false && $sz > 0) {
			$sz = (int)$sz;
			$totalBytes += $sz;
			if($sz > $largestFileBytes) {
				$largestFileBytes = $sz;
			}
		}
		if($fileCount >= $minFilesForBundleOnly) {
			return true;
		}
	}
	if($fileCount === 1 && $largestFileBytes >= $singleFileMinBytes) {
		return true;
	}
	return false;
}

/**
 * Clears in-memory folder bundle scan cache (one zip_folder() invocation may call collect more than once for the same root).
 */
function reset_folder_bundle_structure_scan_state() {
	$this->folder_structure_was_made = false;
	$this->folder_structure_scan_root = '';
	$this->folder_structure_raw_files_by_path = null;
}

/**
 * @return array<string,string> relative_path => raw_bytes
 */
function collect_raw_files_for_bundle($dir) {
	$iterRoot = realpath($dir);
	if($iterRoot === false) {
		$iterRoot = $dir;
	}
	if($this->folder_structure_was_made && $this->folder_structure_scan_root === $iterRoot && is_array($this->folder_structure_raw_files_by_path)) {
		return $this->folder_structure_raw_files_by_path;
	}
	$out = array();
	$rootN = rtrim(str_replace('\\', '/', $iterRoot), '/');
	$prefix = $rootN . '/';
	$prefixLen = strlen($prefix);
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($iterRoot, FilesystemIterator::SKIP_DOTS));
	foreach($it as $fileInfo) {
		if(!$fileInfo->isFile()) {
			continue;
		}
		$pathName = $fileInfo->getPathname();
		$pathReal = $fileInfo->getRealPath();
		$pathN = strtr($pathReal !== false ? $pathReal : $pathName, '\\', '/');
		if(!str_starts_with($pathN, $prefix)) {
			fractal_zip::warning_once('collect_raw_files_for_bundle: skipping path outside bundle root.');
			continue;
		}
		$rel = substr($pathN, $prefixLen);
		$bytes = file_get_contents($pathName);
		if($bytes === false) {
			$bytes = '';
		}
		$out[$rel] = (string)$bytes;
	}
	ksort($out, SORT_STRING);
	$this->folder_structure_was_made = true;
	$this->folder_structure_scan_root = $iterRoot;
	$this->folder_structure_raw_files_by_path = $out;
	return $out;
}

/**
 * Copy $src tree to $dst for native-Arc boxing, skipping existing fractal container files so a prior `.fzc` beside sources
 * is not packed into the reference archive.
 */
function recursive_copy_directory_for_native_arc_box($src, $dst) {
	if(!is_dir($dst)) {
		mkdir($dst, 0755, true);
	}
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
	foreach($it as $item) {
		$sub = $it->getSubPathname();
		if($item->isFile()) {
			$low = strtolower($item->getFilename());
			if(str_ends_with($low, '.fzc') || str_ends_with($low, '.fractalzip')) {
				continue;
			}
		}
		$target = $dst . DS . $sub;
		if($item->isDir()) {
			if(!is_dir($target)) {
				mkdir($target, 0755, true);
			}
		} else {
			$parent = dirname($target);
			if(!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $target);
		}
	}
}

/**
 * Write collect_raw_files_for_bundle() map into $dst for native-Arc boxing (same skip rules as recursive_copy_directory_for_native_arc_box).
 *
 * @param array<string,string> $rawFilesByPath
 */
function materialize_raw_files_map_to_directory_for_native_arc_box($rawFilesByPath, $dst) {
	if(!is_dir($dst)) {
		mkdir($dst, 0755, true);
	}
	foreach($rawFilesByPath as $rel => $bytes) {
		$rel = (string) $rel;
		if($rel === '') {
			continue;
		}
		$low = strtolower(basename(str_replace('\\', '/', $rel)));
		if(str_ends_with($low, '.fzc') || str_ends_with($low, '.fractalzip')) {
			continue;
		}
		$sub = str_replace('/', DS, $rel);
		$target = $dst . DS . $sub;
		$parent = dirname($target);
		if(!is_dir($parent)) {
			mkdir($parent, 0755, true);
		}
		file_put_contents($target, (string) $bytes);
	}
}

/**
 * Build a FreeArc archive of $sourceDir (real path) using the same flags as benchmarks/run_benchmarks.php benchArcFolderArchive
 * (<code>-m5</code>); when <code>$sumRawBytesHint</code> is within {@see fractal_zip::folder_native_freearc_dual_method_max_raw_bytes}, also tries <code>-m7</code> and returns the smaller blob.
 *
 * @param array<string,string>|null $rawFilesByPath When non-null and matches folder_structure_scan_root, avoids a second full disk tree read.
 * @param int|null $sumRawBytesHint merged raw byte total for dual-method gate; null skips <code>-m7</code>.
 * @return string|null raw ARC file bytes or null on failure / unsupported OS
 */
function try_build_folder_freearc_native_archive_blob($sourceDir, $rawFilesByPathOrNull = null, $sumRawBytesHint = null) {
	if(DIRECTORY_SEPARATOR === '\\') {
		return null;
	}
	$arcExe = fractal_zip::freearc_executable();
	if($arcExe === null) {
		return null;
	}
	$rp = realpath((string) $sourceDir);
	if($rp === false || !is_dir($rp)) {
		return null;
	}
	// Same temp naming pattern as benchmarks/run_benchmarks.php benchArcFolderArchive (path metadata can shift Arc bytes).
	$box = sys_get_temp_dir() . DS . 'fzarcbench_' . substr(md5((string) $rp . "\0fzarcbench"), 0, 16);
	if(is_dir($box)) {
		$this->recursive_remove_directory($box);
	}
	mkdir($box, 0755, true);
	$useMemMap = $rawFilesByPathOrNull !== null && is_array($rawFilesByPathOrNull)
		&& $this->folder_structure_was_made
		&& $this->folder_structure_scan_root === $rp;
	if($useMemMap) {
		$this->materialize_raw_files_map_to_directory_for_native_arc_box($rawFilesByPathOrNull, $box);
	} else {
		$this->recursive_copy_directory_for_native_arc_box($rp, $box);
	}
	$dualMax = fractal_zip::folder_native_freearc_dual_method_max_raw_bytes();
	$sumN = ($sumRawBytesHint !== null) ? (int) $sumRawBytesHint : -1;
	$methods = array(5);
	if($dualMax > 0 && $sumN > 0 && $sumN <= $dualMax) {
		$methods = array(5, 7);
	}
	$prefix = $this->command_prefix_with_local_lib();
	$qArcExe = fractal_zip::shell_quote_arg_cached($arcExe);
	$arcPath = sys_get_temp_dir() . DS . 'fzarcbench_' . md5($box) . '.arc';
	$qArcPath = fractal_zip::shell_quote_arg_cached($arcPath);
	$cwd = getcwd();
	$best = null;
	$bestLen = PHP_INT_MAX;
	if(@chdir($box)) {
		foreach($methods as $methNum) {
			$methNum = max(1, min(9, (int) $methNum));
			if(is_file($arcPath)) {
				@unlink($arcPath);
			}
			$ret = -1;
			$cmd = $prefix . $qArcExe . fractal_zip::library_arc_compress_mt_shell_fragment_for_exec() . ' a -m' . $methNum . ' -ep1 -y ' . $qArcPath . ' . 2>/dev/null';
			exec($cmd, $xo, $ret);
			if($ret === 0 && is_file($arcPath)) {
				$r = @file_get_contents($arcPath);
				if($r !== false && $r !== '') {
					$rLen = strlen($r);
					if($rLen < $bestLen) {
						$best = $r;
						$bestLen = $rLen;
					}
				}
			}
			if(is_file($arcPath)) {
				@unlink($arcPath);
			}
		}
	}
	if($cwd !== false) {
		chdir($cwd);
	}
	if(is_dir($box)) {
		$this->recursive_remove_directory($box);
	}
	return $best;
}

/**
 * @param array<string,string>|null $rawFilesByPath In-memory bundle from collect_raw_files_for_bundle (same root as $dir) to skip re-reading files for Arc.
 * @return string|null raw FreeArc folder archive bytes if smaller than $fzcBody, else null
 */
function maybe_folder_freearc_native_smaller_than_fzc($dir, $fzcBody, $sumRawBytes, $rawFilesByPath = null) {
	$fzcBody = (string) $fzcBody;
	if($fzcBody === '' || !fractal_zip::folder_native_freearc_compare_enabled()) {
		return null;
	}
	// Respect forced outer selection: do not swap final container codec via native Arc compare.
	if(fractal_zip::outer_force_codec_is_gzip() || fractal_zip::outer_force_codec_is_zpaq()) {
		return null;
	}
	$cap = fractal_zip::folder_native_freearc_compare_max_raw_bytes();
	if($cap > 0 && $sumRawBytes > $cap) {
		return null;
	}
	$native = $this->try_build_folder_freearc_native_archive_blob($dir, $rawFilesByPath, $sumRawBytes);
	if($native === null || $native === '') {
		return null;
	}
	if(strlen($native) >= strlen($fzcBody)) {
		return null;
	}
	fractal_zip::$last_outer_codec = 'arc';
	fractal_zip::$last_written_container_codec = 'arc';
	return $native;
}

/**
 * Build a native zpaq folder archive (raw .zpaq bytes, no extra wrapper).
 * Returns archive bytes if smaller than $fzcBody; null otherwise.
 *
 * Runs whenever native zpaq on the raw tree beats the fractal container on wire bytes (not gated on adaptive outer=zpaq).
 * Raw total is capped by {@see fractal_zip::folder_native_zpaq_compare_max_raw_bytes} unless {@code FRACTAL_ZIP_FORCE_OUTER=zpaq}.
 * Disable entirely: {@code FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0}.
 *
 * Default method ladder matches {@see outer_zpaq_blob} (m6 cap via {@see fractal_zip::ZPAQ_OUTER_METHOD_SIX_MAX_INNER_BYTES}
 * and {@see fractal_zip::zpaq_outer_auto_sweep_max_inner_bytes}; {@code FRACTAL_ZIP_ZPAQ_OUTER_SWEEP} non-empty adjusts the cap).
 * {@code FRACTAL_ZIP_ZPAQ_NATIVE_FULL_SWEEP_MAX_RAW_BYTES} (default 4 MiB) bounds the multi-method sweep; above it, a single m5 trial.
 *
 * @param array<string,string>|null $rawFilesByPath
 * @return string|null
 */
function maybe_folder_zpaq_native_smaller_than_fzc($dir, $fzcBody, $rawFilesByPath = null) {
	$fzcBody = (string) $fzcBody;
	if($fzcBody === '') {
		return null;
	}
	// Compare native zpaq folder archive wire bytes vs fractal container regardless of which outer won on the wrapped inner
	// (Squash-style raw tree; overlaps zpaq). Opt out: FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0.
	if(getenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE') === '0') {
		return null;
	}
	if(!fractal_zip::outer_force_codec_is_zpaq()) {
		$sumCap = 0;
		if(is_array($rawFilesByPath)) {
			foreach($rawFilesByPath as $_b) {
				$sumCap += strlen((string) $_b);
			}
		}
		$capZ = fractal_zip::folder_native_zpaq_compare_max_raw_bytes();
		if($capZ > 0 && $sumCap > $capZ) {
			return null;
		}
	}
	if(DIRECTORY_SEPARATOR === '\\') {
		return null;
	}
	$zpaqExe = fractal_zip::zpaq_executable();
	if($zpaqExe === null) {
		return null;
	}
	$rpZp = realpath((string) $dir);
	$boxKey = ($rpZp !== false) ? $rpZp : (string) $dir;
	$box = sys_get_temp_dir() . DS . 'fzzp_native_' . substr(md5($boxKey . "\0fzzp_native"), 0, 16);
	if(is_dir($box)) {
		$this->recursive_remove_directory($box);
	}
	mkdir($box, 0755, true);
	if(is_array($rawFilesByPath)) {
		foreach($rawFilesByPath as $rel => $bytes) {
			$rel = str_replace('\\', '/', (string) $rel);
			if($rel === '' || strpos($rel, '..') !== false) {
				continue;
			}
			$target = $box . DS . str_replace('/', DS, $rel);
			$parent = dirname($target);
			if(!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			file_put_contents($target, (string) $bytes);
		}
	} else {
		$this->recursive_copy_directory($dir, $box);
	}
	$targetArg = '.';
	$sumRaw = 0;
	if(is_array($rawFilesByPath) && sizeof($rawFilesByPath) === 1) {
		foreach($rawFilesByPath as $rel => $_bytes) {
			$oneRel = str_replace('\\', '/', (string) $rel);
			if($oneRel !== '' && strpos($oneRel, '..') === false) {
				$targetArg = $oneRel;
			}
			$sumRaw += strlen((string) $_bytes);
			break;
		}
	} elseif(is_array($rawFilesByPath)) {
		foreach($rawFilesByPath as $_bytes) {
			$sumRaw += strlen((string) $_bytes);
		}
	}
	$candidates = fractal_zip::zpaq_outer_methods_argv_fragments_cached();
	if($candidates === []) {
		$v = fractal_zip::zpaq_method_env_trimmed_cached();
		if($v !== null) {
			$candidates[] = ($v !== '' && $v[0] === '-') ? (' ' . $v) : (' -method ' . $v);
		} else {
			// Align m6 trials with outer_zpaq_blob (sixCap + FRACTAL_ZIP_ZPAQ_OUTER_SWEEP). Optional
			// FRACTAL_ZIP_ZPAQ_NATIVE_FULL_SWEEP_MAX_RAW_BYTES (default 4 MiB) bounds the 6/5/4/3 ladder; above that, m5 only.
			$sw = fractal_zip::zpaq_outer_sweep_env_flags_cached();
			$sweepFromEnv = $sw['env_nonempty'];
			$autoMax = fractal_zip::zpaq_outer_auto_sweep_max_inner_bytes();
			$sixM = fractal_zip::ZPAQ_OUTER_METHOD_SIX_MAX_INNER_BYTES;
			$sixCap = $sweepFromEnv ? $sixM : (($autoMax > 0) ? min($sixM, $autoMax) : $sixM);
			$fullSweepMaxRaw = fractal_zip::zpaq_native_full_sweep_max_raw_bytes_cached();
			if($sumRaw > 0 && $fullSweepMaxRaw > 0 && $sumRaw <= $fullSweepMaxRaw) {
				$highMax = fractal_zip::zpaq_outer_high_method_max_inner_bytes();
				$highTail = ($highMax > 0 && $sumRaw <= $highMax) ? array(' -method 9', ' -method 8', ' -method 7') : array();
				$candidates = ($sumRaw <= $sixCap)
					? array_merge($highTail, array(' -method 6', ' -method 5', ' -method 4', ' -method 3'))
					: array_merge($highTail, array(' -method 5', ' -method 4', ' -method 3'));
			} else {
				$candidates = array(' -method 5');
			}
		}
	}
	$native = null;
	$nativeBestLen = PHP_INT_MAX;
	$winMeth = null;
	$cwd = getcwd();
	$fzcBodyLen = strlen($fzcBody);
	// One output path per call — unlink between method trials (same as a fresh temp name each time; avoids random_bytes + repeated shell-escape work).
	$arcPath = sys_get_temp_dir() . DS . 'fzzp_native_' . md5($box) . '.zpaq';
	if(@chdir($box)) {
		$prefix = $this->command_prefix_with_local_lib();
		$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
		$qArc = fractal_zip::shell_quote_arg_cached($arcPath);
		$qTarg = fractal_zip::shell_quote_arg_cached($targetArg);
		$nativePar = null;
		if(sizeof($candidates) >= 2) {
			$boxAbs = realpath($box);
			if($boxAbs === false) {
				$boxAbs = $box;
			}
			$nativePar = fractal_zip_encode_pipeline::parallel_folder_native_zpaq_method_variant_trials($boxAbs, $candidates, $targetArg, $prefix, $zpaqExe);
		}
		$didParallelMerge = false;
		if(is_array($nativePar)) {
			$anyParBlob = false;
			foreach($nativePar as $_pb) {
				if(is_string($_pb) && $_pb !== '') {
					$anyParBlob = true;
					break;
				}
			}
			if($anyParBlob) {
				$didParallelMerge = true;
				foreach($candidates as $ci => $methArg) {
					if(!is_string($methArg)) {
						continue;
					}
					$r = isset($nativePar[$ci]) ? $nativePar[$ci] : null;
					if(!is_string($r) || $r === '') {
						continue;
					}
					$rLen = strlen($r);
					if($rLen <= $fzcBodyLen && $rLen < $nativeBestLen) {
						$native = $r;
						$nativeBestLen = $rLen;
						$winMeth = $methArg;
					}
				}
			}
		}
		if(!$didParallelMerge) {
			foreach($candidates as $methArg) {
				if(is_file($arcPath)) {
					@unlink($arcPath);
				}
				$cmd = $prefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add ' . $qArc . ' ' . $qTarg . $methArg . ' -force';
				exec($cmd . ' 2>/dev/null', $xo, $ret);
				if($ret === 0 && is_file($arcPath)) {
					$r = @file_get_contents($arcPath);
					if($r !== false && $r !== '') {
						$rLen = strlen($r);
						// <= wire: prefer native .zpaq on ties with wrapped fzc (same bytes, simpler on-disk format).
						if($rLen <= $fzcBodyLen && $rLen < $nativeBestLen) {
							$native = $r;
							$nativeBestLen = $rLen;
							$winMeth = $methArg;
						}
					}
				}
				if(is_file($arcPath)) {
					@unlink($arcPath);
				}
			}
		}
	}
	if($cwd !== false) {
		chdir($cwd);
	}
	if(is_dir($box)) {
		$this->recursive_remove_directory($box);
	}
	if($native === null) {
		return null;
	}
	if($winMeth !== null) {
		fractal_zip::zpaq_set_last_method_from_argv_fragment($winMeth);
	}
	fractal_zip::$last_outer_codec = 'zpaq';
	fractal_zip::$last_written_container_codec = 'zpaq';
	return $native;
}

/**
 * If $fullContents is legacy `FZFA\x01` + arc, or raw native folder Arc (not single-member `i`), extract into $destRootDir
 * and clear fractal member state. Returns true when handled (caller skips adaptive_decompress).
 */
function freearc_try_extract_native_folder_arc_to_directory($fullContents, $destRootDir, $debug) {
	$fullContents = (string) $fullContents;
	if(strlen($fullContents) >= 5 && substr($fullContents, 0, 4) === 'FZFA' && $fullContents[4] === "\x01") {
		$this->extract_freearc_native_archive_blob_to_directory(substr($fullContents, 5), $destRootDir, $debug);
		$this->array_fractal_zipped_strings_of_files = array();
		$this->fractal_string = '';
		$this->fractal_gzip_peel_restore_map = array();
		return true;
	}
	if(strlen($fullContents) < 4 || $fullContents[0] !== 'A' || $fullContents[1] !== 'r' || $fullContents[2] !== 'C' || $fullContents[3] !== chr(1)) {
		return false;
	}
	if(DIRECTORY_SEPARATOR === '\\') {
		return false;
	}
	$arcExe = fractal_zip::freearc_executable();
	if($arcExe === null) {
		return false;
	}
	$u = substr(md5(fractal_zip::hot_string_digest($fullContents) . "\0fzfc_arc\0" . (string) spl_object_id($this)), 0, 16);
	$tmpArc = sys_get_temp_dir() . DS . 'fzfc_arc_' . $u . '.arc';
	$tmpOut = sys_get_temp_dir() . DS . 'fzfc_x_' . $u;
	if(@file_put_contents($tmpArc, $fullContents) === false) {
		return false;
	}
	if(is_dir($tmpOut)) {
		$this->recursive_remove_directory($tmpOut);
	}
	mkdir($tmpOut, 0755, true);
	$cwd = getcwd();
	$ret = -1;
	if(chdir($tmpOut)) {
		$prefix = $this->command_prefix_with_local_lib();
		$cmd = $prefix . fractal_zip::shell_quote_arg_cached($arcExe) . fractal_zip::library_arc_compress_mt_shell_fragment_for_exec() . ' x -y ' . escapeshellarg($tmpArc) . ' 2>/dev/null';
		exec($cmd, $xo, $ret);
	}
	if($cwd !== false) {
		chdir($cwd);
	}
	if($ret !== 0) {
		@unlink($tmpArc);
		if(is_dir($tmpOut)) {
			$this->recursive_remove_directory($tmpOut);
		}
		return false;
	}
	$sd = @scandir($tmpOut);
	$entries = is_array($sd) ? array_values(array_diff($sd, array('.', '..'))) : array();
	$legacySingleI = (sizeof($entries) === 1 && isset($entries[0]) && $entries[0] === 'i' && is_file($tmpOut . DS . 'i'));
	if($legacySingleI) {
		$this->recursive_remove_directory($tmpOut);
		@unlink($tmpArc);
		return false;
	}
	$this->recursive_copy_directory($tmpOut, $destRootDir);
	$this->recursive_remove_directory($tmpOut);
	@unlink($tmpArc);
	$this->array_fractal_zipped_strings_of_files = array();
	$this->fractal_string = '';
	$this->fractal_gzip_peel_restore_map = array();
	if($debug) {
		print('Extracted native FreeArc folder archive (raw ARC bytes in .fzc).<br>');
	}
	return true;
}

/**
 * If payload is raw native folder zpaq archive (or legacy FZPA\x01 wrapper), extract into $destRootDir.
 */
function zpaq_try_extract_native_folder_archive_to_directory($fullContents, $destRootDir, $debug) {
	$fullContents = (string) $fullContents;
	$arc = '';
	if(strlen($fullContents) >= 5 && substr($fullContents, 0, 4) === 'FZPA' && $fullContents[4] === "\x01") {
		$arc = substr($fullContents, 5);
	} elseif(strlen($fullContents) >= 4 && substr($fullContents, 0, 4) === "7kSt") {
		$arc = $fullContents;
	} else {
		return false;
	}
	if(DIRECTORY_SEPARATOR === '\\') {
		return false;
	}
	$zpaqExe = fractal_zip::zpaq_executable();
	if($zpaqExe === null) {
		return false;
	}
	if($arc === '') {
		return false;
	}
	$u = substr(md5(fractal_zip::hot_string_digest($arc) . "\0fzpa_in\0" . (string) spl_object_id($this)), 0, 16);
	$tmpArc = sys_get_temp_dir() . DS . 'fzpa_in_' . $u . '.zpaq';
	$tmpOut = sys_get_temp_dir() . DS . 'fzpa_x_' . $u;
	if(@file_put_contents($tmpArc, $arc) === false) {
		return false;
	}
	if(is_dir($tmpOut)) {
		$this->recursive_remove_directory($tmpOut);
	}
	mkdir($tmpOut, 0755, true);
	$prefix = $this->command_prefix_with_local_lib();
	$cmd = $prefix . fractal_zip::shell_quote_arg_cached($zpaqExe) . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' x ' . escapeshellarg($tmpArc)
		. ' -to ' . escapeshellarg($tmpOut) . ' -force';
	exec($cmd . ' 2>/dev/null', $xo, $ret);
	if((int) $ret !== 0) {
		@unlink($tmpArc);
		if(is_dir($tmpOut)) {
			$this->recursive_remove_directory($tmpOut);
		}
		return false;
	}
	$sd = @scandir($tmpOut);
	$entries = is_array($sd) ? array_values(array_diff($sd, array('.', '..'))) : array();
	$singleInner = (sizeof($entries) === 1
		&& isset($entries[0])
		&& ($entries[0] === 'inner.bin' || $entries[0] === 'i')
		&& is_file($tmpOut . DS . $entries[0]));
	if($singleInner) {
		// This is a normal outer-zpaq payload (single inner archive member), not a native folder archive.
		$this->recursive_remove_directory($tmpOut);
		@unlink($tmpArc);
		return false;
	}
	$this->recursive_copy_directory($tmpOut, $destRootDir);
	$this->recursive_remove_directory($tmpOut);
	@unlink($tmpArc);
	$this->array_fractal_zipped_strings_of_files = array();
	$this->fractal_string = '';
	$this->fractal_gzip_peel_restore_map = array();
	if($debug) {
		print('Extracted native zpaq folder archive (FZPA wrapper).<br>');
	}
	return true;
}

/**
 * Extract native Arc bytes (no fractal outer) into an existing directory tree.
 */
function extract_freearc_native_archive_blob_to_directory($arcBlob, $destRootDir, $debug) {
	if(DIRECTORY_SEPARATOR === '\\') {
		fractal_zip::fatal_error('Native FreeArc folder extract requires Unix (not wired for Windows).');
	}
	$arcExe = fractal_zip::freearc_executable();
	if($arcExe === null) {
		fractal_zip::fatal_error('Native FreeArc extract requires `arc` on PATH.');
	}
	$arcBlob = (string) $arcBlob;
	$destRootDir = rtrim((string) $destRootDir, "\0");
	if(strlen($arcBlob) < 4 || $arcBlob[0] !== 'A' || $arcBlob[1] !== 'r' || $arcBlob[2] !== 'C' || $arcBlob[3] !== chr(1)) {
		fractal_zip::fatal_error('Payload is not a valid FreeArc archive.');
	}
	$u = substr(md5(fractal_zip::hot_string_digest($arcBlob) . "\0fzfa_in\0" . (string) spl_object_id($this)), 0, 16);
	$tmpArc = sys_get_temp_dir() . DS . 'fzfa_in_' . $u . '.arc';
	$tmpOut = sys_get_temp_dir() . DS . 'fzfa_x_' . $u;
	if(file_put_contents($tmpArc, $arcBlob) === false) {
		fractal_zip::fatal_error('Failed to write temp FreeArc archive file.');
	}
	if(is_dir($tmpOut)) {
		$this->recursive_remove_directory($tmpOut);
	}
	mkdir($tmpOut, 0755, true);
	$cwd = getcwd();
	$ret = -1;
	if(chdir($tmpOut)) {
		$prefix = $this->command_prefix_with_local_lib();
		$cmd = $prefix . fractal_zip::shell_quote_arg_cached($arcExe) . fractal_zip::library_arc_compress_mt_shell_fragment_for_exec() . ' x -y ' . escapeshellarg($tmpArc) . ' 2>/dev/null';
		exec($cmd, $xo, $ret);
	}
	if($cwd !== false) {
		chdir($cwd);
	}
	if($ret !== 0) {
		@unlink($tmpArc);
		if(is_dir($tmpOut)) {
			$this->recursive_remove_directory($tmpOut);
		}
		fractal_zip::fatal_error('Failed to extract native FreeArc archive (arc x).');
	}
	$this->recursive_copy_directory($tmpOut, $destRootDir);
	$this->recursive_remove_directory($tmpOut);
	@unlink($tmpArc);
	if($debug) {
		print('Extracted native FreeArc folder archive.<br>');
	}
}

function add_to_fractal_zip($filename) {
	// would like to be able to add single files or folders to an existing fractal_zip
	// how to determine which .fzc to add to??
}

function remove_from_fractal_zip($filename) {
	// maybe this is actually useful?
}

function explore_fractal_zip($filename) {
	// would like to be able to explore the contents of a fractal_zip then subsequently extract individual or all files
	// this is done by open_file in fs if it tries to open a fractal_zip file
}

/**
 * FZB4 varint from an open stream (same encoding as decode_varint_u32).
 * @param resource $fh
 */
function read_varint_u32_from_stream($fh, $ctx) {
	$shift = 0;
	$out = 0;
	$steps = 0;
	while(true) {
		$b = fread($fh, 1);
		if($b === false || strlen($b) !== 1) {
			fractal_zip::fatal_error('Corrupt ' . $ctx . ' payload (varint truncated).');
		}
		$bb = ord($b);
		$out |= (($bb & 0x7F) << $shift);
		$steps++;
		if(($bb & 0x80) === 0) {
			break;
		}
		$shift += 7;
		if($steps > 5 || $shift > 28) {
			fractal_zip::fatal_error('Corrupt ' . $ctx . ' payload (varint too long).');
		}
	}
	return $out;
}

/**
 * Stream-decompress a single zlib/deflate/gzip member from disk to disk (chunked; avoids file_get_contents + gzuncompress OOM).
 * @return bool true if output file is non-empty
 */
function stream_inflate_file_to_path($srcPath, $dstPath, $encoding) {
	if(!function_exists('inflate_init') || !function_exists('inflate_add')) {
		return false;
	}
	$in = @fopen($srcPath, 'rb');
	if($in === false) {
		return false;
	}
	$out = @fopen($dstPath, 'wb');
	if($out === false) {
		fclose($in);
		return false;
	}
	$ctx = @inflate_init($encoding);
	if($ctx === false) {
		fclose($in);
		fclose($out);
		return false;
	}
	$written = 0;
	$readChunk = fractal_zip::stream_chunk_bytes();
	while(!feof($in)) {
		$chunk = fread($in, $readChunk);
		if($chunk === false) {
			$chunk = '';
		}
		$flags = feof($in) ? ZLIB_FINISH : ZLIB_SYNC_FLUSH;
		$dec = @inflate_add($ctx, $chunk, $flags);
		if($dec === false) {
			fclose($in);
			fclose($out);
			return false;
		}
		if($dec !== '' && fwrite($out, $dec) === false) {
			fclose($in);
			fclose($out);
			return false;
		}
		$written += strlen($dec);
	}
	fclose($in);
	fclose($out);
	if($written > 0) {
		return true;
	}
	$sz = @filesize($dstPath);
	return is_int($sz) && $sz > 0;
}

/**
 * Try RAW / zlib / gzip wrappers until inner payload starts with FZB4 or FZCD.
 * @param-out string $tmpPath path to temp inner file (caller must unlink)
 * @return bool
 */
function try_stream_inflate_to_fzb4_temp_file($srcPath, &$tmpPath) {
	$tmpPath = sys_get_temp_dir() . DS . 'fzfzin_' . substr(md5(fractal_zip::hot_string_digest((string) $srcPath) . "\0fzfi\0" . (string) spl_object_id($this)), 0, 16);
	$encodings = array(ZLIB_ENCODING_RAW, ZLIB_ENCODING_DEFLATE, ZLIB_ENCODING_GZIP);
	foreach($encodings as $enc) {
		if(is_file($tmpPath)) {
			@unlink($tmpPath);
		}
		if(!$this->stream_inflate_file_to_path($srcPath, $tmpPath, $enc)) {
			continue;
		}
		$h = @fopen($tmpPath, 'rb');
		if($h === false) {
			@unlink($tmpPath);
			continue;
		}
		$sig = fread($h, 4);
		fclose($h);
		if($sig === 'FZWS' || $sig === 'FZBD') {
			@unlink($tmpPath);
			$tmpPath = '';
			return false;
		}
		if($sig === 'FZB4' || $sig === 'FZCD') {
			return true;
		}
	}
	if(is_file($tmpPath)) {
		@unlink($tmpPath);
	}
	$tmpPath = '';
	return false;
}

/**
 * Stream whole-file xz decompress of $srcPath to a temp file (xz reads the path; stdout → file — no stdin pipe deadlock).
 * Caller inspects magic (FZB4/FZCD stream-extract, else load + decode_container_payload). Caller must unlink $tmpPath.
 *
 * @param-out string $tmpPath
 * @return bool
 */
function try_stream_xz_decompress_to_temp_file($srcPath, &$tmpPath) {
	$tmpPath = fractal_zip::outer_codec_temp_dir() . DS . 'fzXZn_' . substr(md5(fractal_zip::hot_string_digest((string) $srcPath) . "\0xzn\0" . (string) spl_object_id($this)), 0, 16);
	$xzExe = fractal_zip::xz_executable();
	if($xzExe === null || !is_file($srcPath) || !function_exists('proc_open')) {
		$tmpPath = '';
		return false;
	}
	$cmd = fractal_zip::shell_quote_arg_cached($xzExe) . fractal_zip::library_xz_decompress_thread_shell_fragment_for_exec() . ' -d -c ' . fractal_zip::shell_quote_arg_cached($srcPath);
	$pe = fractal_zip::proc_open_reusable_std_pipe_ends();
	$desc = array(0 => $pe['r'], 1 => array('file', $tmpPath, 'wb'), 2 => $pe['w']);
	$proc = @proc_open($cmd, $desc, $pipes);
	if(!is_resource($proc)) {
		@unlink($tmpPath);
		$tmpPath = '';
		return false;
	}
	fclose($pipes[0]);
	stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	if(proc_close($proc) !== 0) {
		@unlink($tmpPath);
		$tmpPath = '';
		return false;
	}
	$sz = @filesize($tmpPath);
	if(!is_int($sz) || $sz < 4) {
		@unlink($tmpPath);
		$tmpPath = '';
		return false;
	}
	return true;
}

/**
 * Write decode_container_payload() output to disk (same layout as open_container legacy path).
 *
 * @param array{0: array<string,string>, 1: string, 2?: array<string,string>} $triple
 */
function write_decoded_container_members_to_disk($rootDir, $triple, $debug, $markStreamingExtractUsed) {
	$this->array_fractal_zipped_strings_of_files = $triple[0];
	$this->fractal_string = $triple[1];
	$this->fractal_gzip_peel_restore_map = isset($triple[2]) && is_array($triple[2]) ? $triple[2] : array();
	fractal_zip::$open_container_streaming_extract_used = $markStreamingExtractUsed;
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		$this->build_directory_structure_for($rootDir . DS . $index);
		$zipped_contents = $value;
		$unzipped_contents = $this->unzip($zipped_contents);
		if(isset($this->fractal_gzip_peel_restore_map[$index])) {
			$unzipped_contents = $this->fractal_gzip_peel_restore_map[$index];
		}
		file_put_contents($rootDir . DS . $index, $unzipped_contents);
		if($debug) {
			print('Extracted ' . $rootDir . DS . $index . '<br>');
		}
		$this->files_counter++;
	}
}

/**
 * Outer formats that need full-file adaptive_decompress (7z, arc, brotli, zstd, xz) — not streamable here.
 */
function container_outer_needs_legacy_full_read($head) {
	if(strlen($head) < 2) {
		return false;
	}
	if(substr($head, 0, 2) === '7z') {
		return true;
	}
	$bMagic = fractal_zip::OUTER_BROTLI_MAGIC;
	$bMagicLen = strlen($bMagic);
	if(strlen($head) >= $bMagicLen && substr($head, 0, $bMagicLen) === $bMagic) {
		return true;
	}
	if(strlen($head) >= 4 && substr($head, 0, 4) === 'ARC' && $head[3] === chr(1)) {
		return true;
	}
	if(strlen($head) >= 4 && substr($head, 0, 4) === "\x28\xB5\x2F\xFD") {
		return true;
	}
	if(strlen($head) >= 6 && substr($head, 0, 6) === "\xFD\x37\x7A\x58\x5A\x00") {
		return true;
	}
	return false;
}

/**
 * Stream-read FZB4 bundle from disk; write each member under $rootDir (same layout as open_container).
 * @return bool false if not attempted / not FZB4
 */
function extract_fzb4_bundle_streaming_from_path($bundlePath, $rootDir, $debug) {
	$fh = @fopen($bundlePath, 'rb');
	if($fh === false) {
		return false;
	}
	$sig = fread($fh, 4);
	if($sig !== 'FZB4') {
		fclose($fh);
		return false;
	}
	$this->fractal_string = '';
	$this->array_fractal_zipped_strings_of_files = array();
	fractal_zip::$open_container_streaming_extract_used = true;
	$prevPath = '';
	while(true) {
		$pos = ftell($fh);
		$probe = fread($fh, 1);
		if($probe === false || $probe === '') {
			break;
		}
		fseek($fh, $pos);
		$prefixLen = $this->read_varint_u32_from_stream($fh, 'FZB4');
		$suffixLen = $this->read_varint_u32_from_stream($fh, 'FZB4');
		if($prefixLen > strlen($prevPath)) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZB4 payload (prefix length exceeds previous path).');
		}
		$pathSuffix = $suffixLen > 0 ? fread($fh, $suffixLen) : '';
		if(strlen($pathSuffix) !== $suffixLen) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZB4 payload (path suffix truncated).');
		}
		$path = substr($prevPath, 0, $prefixLen) . $pathSuffix;
		$modeCh = fread($fh, 1);
		if($modeCh === false || strlen($modeCh) !== 1) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZB4 payload (mode missing).');
		}
		$mode = ord($modeCh);
		$dataLen = $this->read_varint_u32_from_stream($fh, 'FZB4');
		$rawStored = $dataLen > 0 ? fread($fh, $dataLen) : '';
		if(strlen($rawStored) !== $dataLen) {
			fclose($fh);
			fractal_zip::fatal_error('Corrupt FZB4 payload (data truncated).');
		}
		$raw = $this->decode_bundle_literal_member($mode, $rawStored);
		$escaped = $this->escape_literal_for_storage($raw);
		$unzipped = $this->unzip($escaped);
		$outPath = $rootDir . DS . str_replace('/', DS, $path);
		$this->build_directory_structure_for($outPath);
		file_put_contents($outPath, $unzipped);
		if($debug) {
			print('Extracted ' . $outPath . '<br>');
		}
		$this->files_counter++;
		$this->array_fractal_zipped_strings_of_files[$path] = '';
		$prevPath = $path;
	}
	fclose($fh);
	if($debug) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
	}
	return true;
}

/**
 * Avoid loading entire .fzc into memory: plain FZB4, or deflate/zlib/gzip outer wrapping FZB4 (e.g. folder gzip-fast).
 */
function open_container_streaming_if_applicable($filename, $rootDir, $debug) {
	fractal_zip::$open_container_streaming_extract_used = false;
	$head = @file_get_contents($filename, false, null, 0, 32);
	if($head === false || $head === '') {
		return false;
	}
	// xz outer: decompress via xz reading the file (no stdin pipe deadlock); FZB4/FZCD stream-read; FZBM/FZBF/etc. decode then write.
	if(strlen($head) >= 6 && substr($head, 0, 6) === "\xFD\x37\x7A\x58\x5A\x00") {
		$tmpXz = '';
		if($this->try_stream_xz_decompress_to_temp_file($filename, $tmpXz)) {
			$h = @fopen($tmpXz, 'rb');
			if($h === false) {
				@unlink($tmpXz);
				return false;
			}
			$sig = fread($h, 4);
			fclose($h);
			if($sig === 'FZCD') {
				$ok = $this->extract_fzcd_bundle_streaming_from_path($tmpXz, $rootDir, $debug);
				@unlink($tmpXz);
				return $ok;
			}
			if($sig === 'FZB4') {
				$ok = $this->extract_fzb4_bundle_streaming_from_path($tmpXz, $rootDir, $debug);
				@unlink($tmpXz);
				return $ok;
			}
			$inner = @file_get_contents($tmpXz);
			@unlink($tmpXz);
			if($inner === false || $inner === '') {
				return false;
			}
			$triple = $this->decode_container_payload($inner);
			$this->write_decoded_container_members_to_disk($rootDir, $triple, $debug, true);
			if($debug) {
				$micro_time_taken = microtime(true) - $this->initial_micro_time;
				print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
			}
			return true;
		}
	}
	if($this->container_outer_needs_legacy_full_read($head)) {
		return false;
	}
	if(substr($head, 0, 4) === 'FZB4') {
		return $this->extract_fzb4_bundle_streaming_from_path($filename, $rootDir, $debug);
	}
	if(substr($head, 0, 4) === 'FZCD') {
		return $this->extract_fzcd_bundle_streaming_from_path($filename, $rootDir, $debug);
	}
	$tmp = '';
	if($this->try_stream_inflate_to_fzb4_temp_file($filename, $tmp)) {
		$h = @fopen($tmp, 'rb');
		if($h === false) {
			@unlink($tmp);
			return false;
		}
		$sig = fread($h, 4);
		fclose($h);
		if($sig === 'FZCD') {
			$ok = $this->extract_fzcd_bundle_streaming_from_path($tmp, $rootDir, $debug);
		} else {
			$ok = $this->extract_fzb4_bundle_streaming_from_path($tmp, $rootDir, $debug);
		}
		@unlink($tmp);
		return $ok;
	}
	return false;
}

function open_container($filename, $debug = false) {
	if($debug) {
		print('Opening fractal zip container: ' . $filename . '<br>');
	}
	$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DS));
	fractal_zip::$open_container_streaming_extract_used = false;
	if($this->open_container_streaming_if_applicable($filename, $root_directory_of_container_file, $debug)) {
		$this->fractal_gzip_peel_restore_map = array();
		return;
	}
	$contents = file_get_contents($filename);
	if($this->freearc_try_extract_native_folder_arc_to_directory($contents, $root_directory_of_container_file, $debug)) {
		fractal_zip::$open_container_streaming_extract_used = false;
		if($debug) {
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
		}
		return;
	}
	if($this->zpaq_try_extract_native_folder_archive_to_directory($contents, $root_directory_of_container_file, $debug)) {
		fractal_zip::$open_container_streaming_extract_used = false;
		if($debug) {
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
		}
		return;
	}
	// un-gzip or un-LZMA?
	//$contents = gzuncompress($contents);
	$contents = $this->adaptive_decompress($contents);
	//$contents = gzdecode($contents);
	//$contents = gzinflate($contents);
	//$contents = bzdecompress($contents);
	//$array_fractal_strings_and_equivalences = unserialize($contents);
	//$this->array_fractal_zipped_strings_of_files = $array_fractal_strings_and_equivalences[0];
	//$this->fractal_strings = $array_fractal_strings_and_equivalences[1];
	//print('$contents: ');var_dump($contents);
	$array_fractal_string_and_equivalences = $this->decode_container_payload($contents);
	//print('$array_fractal_string_and_equivalences: ');var_dump($array_fractal_string_and_equivalences);
	/*$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	$this->multipass = $array_fractal_string_and_equivalences[1];
	$this->branch_counter = $array_fractal_string_and_equivalences[2];
	$this->left_fractal_zip_marker = $array_fractal_string_and_equivalences[3];
	$this->mid_fractal_zip_marker = $array_fractal_string_and_equivalences[4];
	$this->right_fractal_zip_marker = $array_fractal_string_and_equivalences[5];
	$this->fractal_string = $array_fractal_string_and_equivalences[6];*/
	
	$this->write_decoded_container_members_to_disk($root_directory_of_container_file, $array_fractal_string_and_equivalences, $debug, false);
	if($debug) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken opening container: ' . $micro_time_taken . ' seconds.<br>');
	}
}

function open_container_allowing_individual_extraction($filename, $debug = false) {
	if($debug) {
		print('Opening fractal zip container: ' . $filename . '<br>');
	}
	$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DS));
	fractal_zip::$open_container_streaming_extract_used = false;
	if($this->open_container_streaming_if_applicable($filename, $root_directory_of_container_file, $debug)) {
		$this->fractal_gzip_peel_restore_map = array();
		foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
			print('<a href="do.php?action=extract_file&path=' . fs::query_encode($filename) . '&file_to_extract=' . $index . '">Extract: ' . $index . '</a><br>');
		}
		if($debug) {
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			print('Time taken opening container allowing individual extraction: ' . $micro_time_taken . ' seconds.<br>');
		}
		return;
	}
	$contents = file_get_contents($filename);
	if($this->freearc_try_extract_native_folder_arc_to_directory($contents, $root_directory_of_container_file, $debug)) {
		fractal_zip::$open_container_streaming_extract_used = false;
		if($debug) {
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			print('Time taken opening container allowing individual extraction: ' . $micro_time_taken . ' seconds.<br>');
		}
		return;
	}
	if($this->zpaq_try_extract_native_folder_archive_to_directory($contents, $root_directory_of_container_file, $debug)) {
		fractal_zip::$open_container_streaming_extract_used = false;
		if($debug) {
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			print('Time taken opening container allowing individual extraction: ' . $micro_time_taken . ' seconds.<br>');
		}
		return;
	}
	//$contents = gzuncompress($contents);
	$contents = $this->adaptive_decompress($contents);
	$array_fractal_string_and_equivalences = $this->decode_container_payload($contents);
	/*$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	//print('$this->array_fractal_zipped_strings_of_files: ');var_dump($this->array_fractal_zipped_strings_of_files);
	$this->multipass = $array_fractal_string_and_equivalences[1];
	$this->branch_counter = $array_fractal_string_and_equivalences[2];
	$this->left_fractal_zip_marker = $array_fractal_string_and_equivalences[3];
	$this->mid_fractal_zip_marker = $array_fractal_string_and_equivalences[4];
	$this->right_fractal_zip_marker = $array_fractal_string_and_equivalences[5];
	$this->fractal_string = $array_fractal_string_and_equivalences[6];*/
	
	$this->array_fractal_zipped_strings_of_files = $array_fractal_string_and_equivalences[0];
	$this->fractal_string = $array_fractal_string_and_equivalences[1];
	$this->fractal_gzip_peel_restore_map = isset($array_fractal_string_and_equivalences[2]) && is_array($array_fractal_string_and_equivalences[2]) ? $array_fractal_string_and_equivalences[2] : array();
	
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		print('<a href="do.php?action=extract_file&path=' . fs::query_encode($filename) . '&file_to_extract=' . $index . '">Extract: ' . $index . '</a><br>');
	}
	if($debug) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken opening container allowing individual extraction: ' . $micro_time_taken . ' seconds.<br>');
	}
}

function extract_container($filename) {
	$this->open_container($filename);
	if(fractal_zip::$open_container_streaming_extract_used) {
		$micro_time_taken = microtime(true) - $this->initial_micro_time;
		print('Time taken extracting files from fractal_zip container: ' . $micro_time_taken . ' seconds.<br>');
		return;
	}
	//$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DS));
		foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		//$this->build_directory_structure_for($root_directory_of_container_file . DS . $index);
		$this->build_directory_structure_for($index);
		$zipped_contents = $value;
		$unzipped_contents = $this->unzip($zipped_contents);
		if(isset($this->fractal_gzip_peel_restore_map[$index])) {
			$unzipped_contents = $this->fractal_gzip_peel_restore_map[$index];
		}
		//file_put_contents($root_directory_of_container_file . DS . $index, $unzipped_contents);
		//print('Extracted ' . $root_directory_of_container_file . DS . $index . '<br>');
		file_put_contents($index, $unzipped_contents);
		print('Extracted ' . $index . '<br>');
		$this->files_counter++;
	}
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	print('Time taken extracting files from fractal_zip container: ' . $micro_time_taken . ' seconds.<br>');
}

function extract_file_from_container($filename, $file) {
	$this->open_container($filename);
	if(fractal_zip::$open_container_streaming_extract_used) {
		fractal_zip::fatal_error('extract_file_from_container is not supported for containers opened via the streaming FZB4 path; open the full container with open_container or extract_container.');
	}
	//$root_directory_of_container_file = substr($filename, 0, fractal_zip::strpos_last($filename, DS));
	foreach($this->array_fractal_zipped_strings_of_files as $index => $value) {
		if($file === $index) {
			//$this->build_directory_structure_for($root_directory_of_container_file . DS . $index);
			$this->build_directory_structure_for($index);
			$zipped_contents = $value;
			$unzipped_contents = $this->unzip($zipped_contents);
			if(isset($this->fractal_gzip_peel_restore_map[$index])) {
				$unzipped_contents = $this->fractal_gzip_peel_restore_map[$index];
			}
			//file_put_contents($root_directory_of_container_file . DS . $index, $unzipped_contents);
			//print('Extracted ' . $root_directory_of_container_file . DS . $index . '<br>');
			file_put_contents($index, $unzipped_contents);
			print('Extracted ' . $index . '<br>');
			$this->files_counter++;
		}
	}
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	//print('Time taken extracting ' . $file . ' from fractal_zip container ' . $filename . ': ' . $micro_time_taken . ' seconds.<br>');
	print('Time taken extracting: ' . $micro_time_taken . ' seconds.<br>');
}

function build_directory_structure_for($filename) {
	$filename = str_replace('\\', '/', (string) $filename);
	$dir = dirname($filename);
	if($dir === '' || $dir === '.' || $dir === '/') {
		return;
	}
	$dirNative = str_replace('/', DS, $dir);
	if(is_dir($dirNative)) {
		return;
	}
	if(!@mkdir($dirNative, 0755, true) && !is_dir($dirNative)) {
		fractal_zip::fatal_error('Failed to create directory: ' . $dirNative);
	}
}

function unzip($string, $fractal_string = false, $decode = true) {
	$this->fractal_marker_ctx_publish();
	if($fractal_string === false) {
		$fractal_string = $this->fractal_string;
	}
	/*$found_equivalence = false;
	foreach($this->equivalences as $equivalence) {
		//$equivalence_string = $equivalence[0];
		//$equivalence_filename = $equivalence[1];
		$equivalence_fractal_zipped_expression = $equivalence[2];
		if($equivalence_fractal_zipped_expression === $string) {
			$found_equivalence = true;
			break;
		}
	}
	if(!$found_equivalence) {
		print('Equivalence not found.');exit(0);
	}*/
	/*$unzipped_string = '';
	$branch_ids = fractal_zip::branch_ids_from_zipped_string($string);
	foreach($branch_ids as $branch_id) {
		foreach($this->fractal_strings as $branch_id2 => $fractal_string) {
			//print('$branch_reference, $branch_id: ');var_dump($branch_reference, $branch_id);
			if($branch_id == $branch_id2) {
				$unzipped_string .= $fractal_string;
				break;
			}
		}
	}*/
	/*$unzipped_string = '';
	$character_ranges = explode(',', $string);
	foreach($character_ranges as $character_range) {
		if(strpos($character_range, '-') === false) {
			$start_offset = (int)$character_range;
			$unzipped_string .= $this->fractal_string[$start_offset];
		} else {
			$start_offset = (int)substr($character_range, 0, strpos($character_range, '-'));
			$end_offset = (int)substr($character_range, strpos($character_range, '-') + 1);
			$unzipped_string .= substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
		}
	}*/
	
	// true fractal unzip
	//print('$string, $fractal_string at the start of unzip: ');var_dump($string, $fractal_string);exit(0);
	//if($string === '<0"' . strlen($fractal_string) . '>') {
	//	fractal_zip::warning_once('this hack of allowing unencoded XML in lazy zipped string is probably untenable; especially when considering operations other than substring, such as replace, slide, warp, etc.');
	//	return $fractal_string;
	//}
	//if($string[0] === '<') {
	if(strpos($string, $this->left_fractal_zip_marker) !== false) {
		//print('fractally processing string<br>');
		$unzipped_string = $this->fractally_process_string($string, $fractal_string);
		//print('$string, $fractal_string, $unzipped_string from fractal_processing: ');var_dump($string, $fractal_string, $unzipped_string);
		if($decode) {
			$unzipped_string = $this->unescape_literal_from_storage($unzipped_string);
		}
		return $unzipped_string;
	}
	
	//fractal_zip::warning_once('will have to write a parser rather than using regular expressions... probably faster and more accurate');
	preg_match_all('/' . fractal_zip::preg_escape($this->left_fractal_zip_marker) . $this->fractal_zipping_pass . fractal_zip::preg_escape($this->mid_fractal_zip_marker) . '([0-9]+)\-([0-9]+)' . fractal_zip::preg_escape($this->mid_fractal_zip_marker) . $this->fractal_zipping_pass . fractal_zip::preg_escape($this->right_fractal_zip_marker) . '/is', $string, $fractal_zipped_ranges);
	//print('$fractal_zipped_ranges: ');var_dump($fractal_zipped_ranges);
	foreach($fractal_zipped_ranges[0] as $index => $value) {
		$start_offset = $fractal_zipped_ranges[1][$index];
		$end_offset = $fractal_zipped_ranges[2][$index];
		//print('$value, substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1): ');var_dump($value, substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1));
		$string = str_replace($value, substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1), $string);
	}
	// parser
	$stringLen = strlen($string);
	$leftMLen = strlen($this->left_fractal_zip_marker);
	$midMLen = strlen($this->mid_fractal_zip_marker);
	$rightMLen = strlen($this->right_fractal_zip_marker);
	$rangeMLen = strlen($this->range_shorthand_marker);
	$shorthand_counter = 1;
	$saved_shorthand = array();
	$counter = 0;
	$unzipped_string = '';
	//$string_fragment = '';
	$branch_counter = $this->branch_counter;
	while($branch_counter > -1) {
		while($counter < $stringLen) {
			if(substr($string, $counter, $leftMLen) === $this->left_fractal_zip_marker) {
				$counter += $leftMLen;
				if($this->multipass) {
					$string_fragment = $this->left_fractal_zip_marker;
					$left_branch_counter = '';
					while($counter < $stringLen) {
						if(substr($string, $counter, $midMLen) === $this->mid_fractal_zip_marker) {
							$counter += $midMLen;
							if($left_branch_counter == $this->branch_counter) {
								
							} else {
								$string_fragment .= $this->mid_fractal_zip_marker;
								$unzipped_string .= $string_fragment;
								continue 2;
							}
							$unzipped_string .= $saved_shorthand[$shorthand_number];
							if(substr($string, $counter, $rangeMLen) === $this->range_shorthand_marker) { // short-hand range
								$counter += $rangeMLen;
								$string_fragment .= $this->range_shorthand_marker;
								$shorthand_number = '';
								while($counter < $stringLen) {
									if(substr($string, $counter, $midMLen) === $this->mid_fractal_zip_marker) {
										$counter += $midMLen;
										$string_fragment .= $this->mid_fractal_zip_marker;
										$right_branch_counter = '';
										while($counter < $stringLen) {
											if(substr($string, $counter, $rightMLen) === $this->right_fractal_zip_marker) {
												$counter += $rightMLen;
												$string_fragment .= $this->right_fractal_zip_marker;
												if($left_branch_counter == $right_branch_counter) {
													$unzipped_string .= $saved_shorthand[$shorthand_number];
													continue 4;
												} else {
													$string_fragment .= $this->right_fractal_zip_marker;
													$unzipped_string .= $string_fragment;
													continue 4;
												}
											} else {
												$right_branch_counter .= $string[$counter];
												$string_fragment .= $string[$counter];
											}
											$counter++;
										}
									} else {
										$shorthand_number .= $string[$counter];
										$string_fragment .= $string[$counter];
									}
									$counter++;
								}
							} else {
								$range_string = '';
								while($counter < $stringLen) {
									if(substr($string, $counter, $midMLen) === $this->mid_fractal_zip_marker) {
										$counter += $midMLen;
										$string_fragment .= $this->mid_fractal_zip_marker;
										$right_branch_counter = '';
										while($counter < $stringLen) {
											if(substr($string, $counter, $rightMLen) === $this->right_fractal_zip_marker) {
												$counter += $rightMLen;
												$string_fragment .= $this->right_fractal_zip_marker;
												if($left_branch_counter == $right_branch_counter) {
													$range_string_array = explode('-', $range_string);
													$start_offset = $range_string_array[0];
													$end_offset = $range_string_array[1];
													$unzipped_piece = substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
													$unzipped_string .= $unzipped_piece;
													$saved_shorthand[(string)$shorthand_counter] = $unzipped_piece;
													$shorthand_counter++;
													$counter += $rightMLen;
													continue 4;
												} else {
													$string_fragment .= $this->right_fractal_zip_marker;
													$unzipped_string .= $string_fragment;
													continue 4;
												}
											} else {
												$right_branch_counter .= $string[$counter];
												$string_fragment .= $string[$counter];
											}
											$counter++;
										}
									} else {
										$range_string .= $string[$counter];
										$string_fragment .= $string[$counter];
									}
									$counter++;
								}
							}
						} else {
							$left_branch_counter .= $string[$counter];
							$string_fragment .= $string[$counter];
						}
						$counter++;
					}
					$unzipped_string .= $string_fragment;
				} else { // no multipass
					if(substr($string, $counter, $rangeMLen) === $this->range_shorthand_marker) { // short-hand range
						$counter += $rangeMLen;
						$shorthand_number = '';
						while($counter < $stringLen) {
							if(substr($string, $counter, $rightMLen) === $this->right_fractal_zip_marker) {
								//print('$saved_shorthand, $saved_shorthand[$shorthand_number], $shorthand_number: ');var_dump($saved_shorthand, $saved_shorthand[$shorthand_number], $shorthand_number);
								$shorthand_number = (int)$shorthand_number;
								$unzipped_string .= $saved_shorthand[$shorthand_number];
								$counter += $rightMLen;
								continue 2;
							} else {
								$shorthand_number .= $string[$counter];
							}
							$counter++;
						}
					} else {
						$range_string = '';
						while($counter < $stringLen) {
							if(substr($string, $counter, $rightMLen) === $this->right_fractal_zip_marker) {
								$range_string_array = explode('-', $range_string);
								$start_offset = $range_string_array[0];
								$end_offset = $range_string_array[1];
								$unzipped_piece = substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
								$unzipped_string .= $unzipped_piece;
								$saved_shorthand[$shorthand_counter] = $unzipped_piece;
								$shorthand_counter++;
								$counter += $rightMLen;
								continue 2;
							} else {
								$range_string .= $string[$counter];
							}
							$counter++;
						}
					}
				}
			} else {
				$unzipped_string .= $string[$counter];
			}
			$counter++;
		}
		$branch_counter--;
	}
	if($decode) {
		$unzipped_string = $this->unescape_literal_from_storage($unzipped_string);
	}
	return $unzipped_string;
	//return $string;
}

function create_fractal_file($filename, $equivalence_string, $fractal_string) {
	$fractally_processed_string = $this->fractally_process_string($equivalence_string, $fractal_string);
	$this->build_directory_structure_for($filename);
	file_put_contents($filename, $fractally_processed_string);
	print($filename . ' was created.<br>');
}

function recursive_substring_replace($equivalence_string, $fractal_string) {
	$this->fractal_marker_ctx_publish();
	$initial_equivalence_string = $equivalence_string;
	$Lrx = preg_quote(fractal_zip::$fractal_marker_ctx_left, '/');
	if(preg_match('/' . $Lrx . '[0-9]/s', $equivalence_string) !== 1) {
		return $equivalence_string;
	}
	if(!fractal_zip::is_fractally_clean_for_unzip($equivalence_string)) {
		fractal_zip::warning_once('substring unzip: equivalence string failed bracket balance/order check; skipping substring expansion (may contain literal < > from binary data).');
		return $initial_equivalence_string;
	}
	$flen = strlen($fractal_string);
	$debug_counter = 0;
	$maxSubstrIters = max(2000, min(200000, strlen($equivalence_string) * 16 + 4096));
	while(preg_match('/' . $Lrx . '[0-9]/is', $equivalence_string) === 1) {
		if($debug_counter >= $maxSubstrIters) {
			fractal_zip::warning_once('recursive_substring_replace: iteration cap exceeded (likely binary false positives or very deep markers); returning partial expansion.');
			return $equivalence_string;
		}
		$debug_counter++;
		//preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE);
		if(preg_match('/' . fractal_zip_marker_rx_substring_main() . '/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE) !== 1) {
			fractal_zip::warning_once('substring op: &lt;digit seen but no complete &lt;off&quot;len&quot;...&gt; marker; stopping substring expansion.');
			return $equivalence_string;
		}
		//print('$equivalence_string, $substring_operation_matches: ');var_dump($equivalence_string, $substring_operation_matches);exit(0);
		//$counter = sizeof($substring_operation_matches[0]) - 1;
		//while($counter > -1) {
			if(isset($this->substring_cache[$substring_operation_matches[0][0]])) {
				$substring = $this->substring_cache[$substring_operation_matches[0][0]];
			} else {
				$substring_offset = (int) $substring_operation_matches[1][0];
				$substring_length = (int) $substring_operation_matches[2][0];
				$substring_recursion_counter = $substring_operation_matches[3][0]; // what should be the order of the following markers?
				//print('$substring_offset, $substring_recursion_counter: ');var_dump($substring_offset, $substring_recursion_counter);
				$substring_tuple = $substring_operation_matches[4][0];
				$substring_scale = $substring_operation_matches[5][0];
				$maxSlice = fractal_zip::max_substring_operation_slice_bytes();
				if($maxSlice > 0 && $substring_length > $maxSlice) {
					fractal_zip::warning_once('Substring op length exceeds FRACTAL_ZIP_MAX_SUBSTRING_OPERATION_SLICE_BYTES; cannot expand this marker safely.');
					return $initial_equivalence_string;
				}
				if($substring_offset < 0 || $substring_offset > $flen) {
					fractal_zip::warning_once('Substring op offset out of range for fractal_string.');
					return $equivalence_string;
				}
				if($substring_length < 0 || $substring_offset + $substring_length > $flen) {
					fractal_zip::warning_once('Substring op offset+length out of range for fractal_string (common with BMP/binary false markers).');
					return $equivalence_string;
				}
				$substring = substr($fractal_string, $substring_offset, $substring_length);
				//print('$substring: ');var_dump($substring);
				if($substring_recursion_counter > 1) {
					//print('uhhh0003<br>');
					$rxSub = '/' . preg_quote(fractal_zip::$fractal_marker_ctx_left, '/') . '([0-9]+)' . preg_quote(fractal_zip::$fractal_marker_ctx_mid, '/') . '([0-9]+)' . preg_quote(fractal_zip::$fractal_marker_ctx_right, '/') . '/is';
					$substring = preg_replace($rxSub, fractal_zip::$fractal_marker_ctx_left . '$1' . fractal_zip::$fractal_marker_ctx_mid . '$2' . fractal_zip::$fractal_marker_ctx_mid . ($substring_recursion_counter - 1) . fractal_zip::$fractal_marker_ctx_right, $substring);
					//print('$equivalence_string after processing a substring operation: ');var_dump($equivalence_string);
			//		$processed_a_subtring_operation = true;
				} else {
					//print('uhhh0004<br>');
					$rxSub = '/' . preg_quote(fractal_zip::$fractal_marker_ctx_left, '/') . '([0-9]+)' . preg_quote(fractal_zip::$fractal_marker_ctx_mid, '/') . '([0-9]+)' . preg_quote(fractal_zip::$fractal_marker_ctx_right, '/') . '/is';
					$substring = preg_replace($rxSub, '', $substring);
				}
				$single_substring = $substring;
				$tupleCount = (int)$substring_tuple;
				if($tupleCount < 1) {
					$tupleCount = 1;
				}
				$maxExpand = fractal_zip::max_substring_tuple_expand_bytes();
				if($maxExpand > 0 && $tupleCount > 1 && strlen($single_substring) > 0) {
					$maxRepeats = (int) floor($maxExpand / strlen($single_substring));
					if($maxRepeats < 1) {
						$maxRepeats = 1;
					}
					$maxTuple = $maxRepeats + 1;
					if($tupleCount > $maxTuple) {
						$tupleCount = $maxTuple;
						fractal_zip::warning_once('Substring tuple repetition capped (see FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES).');
					}
				}
				while($tupleCount > 1) {
					$substring = $substring . $single_substring;
					$tupleCount--;
				}
				if($substring_scale !== '') {
					$substring_by_scale = '';
					$offset = 0;
					$counter = 0;
					$scaleAdd = (float) $substring_scale;
					if($scaleAdd <= 0 || $scaleAdd > 1.0e6 || !is_finite($scaleAdd)) {
						fractal_zip::warning_once('substring_scale out of range; skipping scale expansion for this op.');
					} else {
						$maxExpandScale = fractal_zip::max_substring_tuple_expand_bytes();
						$maxOut = ($maxExpandScale > 0) ? $maxExpandScale : PHP_INT_MAX;
						while($offset < strlen($substring)) {
							$counter += $scaleAdd;
							while($counter > 0) {
								if(strlen($substring_by_scale) >= $maxOut) {
									fractal_zip::warning_once('substring_scale output exceeds FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES.');
									return $initial_equivalence_string;
								}
								$substring_by_scale .= $substring[$offset];
								$counter -= 1;
							}
							$offset++;
						}
						$substring = $substring_by_scale;
					}
				}
				$maxExpandPost = fractal_zip::max_substring_tuple_expand_bytes();
				if($maxExpandPost > 0 && strlen($substring) > $maxExpandPost) {
					fractal_zip::warning_once('Expanded substring exceeds FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES before nested recursive_substring_replace.');
					return $initial_equivalence_string;
				}
				$substring = $this->recursive_substring_replace($substring, $fractal_string);
				$this->substring_cache[$substring_operation_matches[0][0]] = $substring;
			}
			$matchStr = $substring_operation_matches[0][0];
			$matchPos = (int) $substring_operation_matches[0][1];
			$matchLen = strlen($matchStr);
			$eqLen = strlen($equivalence_string);
			$newEqLen = $eqLen - $matchLen + strlen($substring);
			$maxEq = fractal_zip::max_equivalence_subop_result_bytes();
			if($maxEq > 0 && $newEqLen > $maxEq) {
				fractal_zip::warning_once('equivalence string after substring op would exceed FRACTAL_ZIP_MAX_EQUIVALENCE_SUBOP_RESULT_BYTES.');
				return $initial_equivalence_string;
			}
			$equivalence_string = substr($equivalence_string, 0, $matchPos) . $substring . substr($equivalence_string, $matchPos + $matchLen);
		//	continue 2; // funny
			//print('$equivalence_string after one substring operators processing loop: ');var_dump($equivalence_string);
		//	$counter--;
		//}
	}
	return $equivalence_string;
}

function fractally_process_string($equivalence_string, $fractal_string = false) {
	$this->fractal_marker_ctx_publish();
	if($fractal_string === false) {
		$fractal_string = $this->fractal_string;
	}
	//print('$equivalence_string, $fractal_string in fractally_process_string: ');var_dump($equivalence_string, $fractal_string);
//	if(!include_once('..' . DS . 'LOM' . DS . 'O.php')) {
//		print('<a href="https://www.phpclasses.org/package/10594-PHP-Extract-information-from-XML-documents.html">LOM</a> is required');exit(0);
//	}
//	$O = new O($string);
	//$changed_something = true;
	// what is the correct way to process; continuing every time something is changed? breaking every time something is changed? doing all fractal operations every loop regardless of if something is changed?
	//while($changed_something) {
	// take care of substring operations first. is this correct?
	//print('$equivalence_string, $fractal_string before substring operations in fractally_process_string: ');var_dump($equivalence_string, $fractal_string);
	//$processed_a_subtring_operation = true;
	//while(strpos($equivalence_string, '<') !== false && $processed_a_subtring_operation) {	
	//	$processed_a_subtring_operation = false;
	//fractal_zip::warning_once('HACK');
	//$equivalence_string = str_replace('<2"5>', '<2"6>', $equivalence_string);
	//$debug_counter = 0;
	
	$this->substring_cache = array();
	$equivalence_string = $this->recursive_substring_replace($equivalence_string, $fractal_string);
	
	/*if(preg_match('/<[0-9]/is', $equivalence_string) === 1) { // do substring operations first
		$substring_offset = -1; // initialization
		//print('uhhh0001<br>');
		while(is_numeric($substring_offset)) {
			//print('uhhh0002<br>');
			if(!fractal_zip::is_fractally_clean_for_unzip($equivalence_string)) {
				fractal_zip::warning('really bad! !fractal_zip::is_fractally_clean_for_unzip($equivalence_string) in fractally_process_string probably because a substring operator is busting into another substring operator! returning an empty string!');
				return '';
			}
			preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE); // would a parser be faster? optimize later
			// this could also be recursive (fractal?) function
			//print('$equivalence_string, $substring_operation_matches: ');var_dump($equivalence_string, $substring_operation_matches);
			$substring_offset = $substring_operation_matches[1][0];
			$substring_length = $substring_operation_matches[2][0];
			$substring_recursion_counter = $substring_operation_matches[3][0]; // what should be the order of the following markers?
			//print('$substring_offset, $substring_recursion_counter: ');var_dump($substring_offset, $substring_recursion_counter);
			$substring_tuple = $substring_operation_matches[4][0];
			$substring_scale = $substring_operation_matches[5][0];
			$substring = substr($fractal_string, $substring_offset, $substring_length);
			//print('$substring: ');var_dump($substring);
			if($substring_recursion_counter > 1) {
				//print('uhhh0003<br>');
				$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '<$1"$2"' . ($substring_recursion_counter - 1) . '>', $substring);
				//print('$equivalence_string after processing a substring operation: ');var_dump($equivalence_string);
		//		$processed_a_subtring_operation = true;
			} else {
				//print('uhhh0004<br>');
				$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '', $substring);
			}
			$single_substring = $substring;
			while($substring_tuple > 1) {
				$substring = $substring . $single_substring;
				$substring_tuple--;
			}
			if($substring_scale !== '') {
				$substring_by_scale = '';
				$offset = 0;
				$counter = 0;
				while($offset < strlen($substring)) {
					$counter += $substring_scale;
					while($counter > 0) {
						$substring_by_scale .= $substring[$offset];
						$counter -= 1;
					}
					$offset++;
				}
				$substring = $substring_by_scale;
			}
			//$equivalence_string = substr($equivalence_string, 0, $substring_operation_matches[0][1]) . $substring . substr($equivalence_string, $substring_operation_matches[0][1] + strlen($substring_operation_matches[0][0]));
			$equivalence_string = str_replace($substring_operation_matches[0][0], $substring, $equivalence_string);
			//print('$equivalence_string after one substring operators processing loop: ');var_dump($equivalence_string);
			//$debug_counter++;
			//if($debug_counter > 500) {
			//	fractal_zip::fatal_error('$debug_counter > 500');
			//}
		}
	}*/
	//print('$equivalence_string, $fractal_string after substring operations in fractally_process_string: ');var_dump($equivalence_string, $fractal_string);
	//print('fractally_process_string001<br>');
	//while(strpos($equivalence_string, '<') !== false) {
	$debug_counter44 = 0;
	$maxFractalProcessPasses = max(256, min(50000, strlen($equivalence_string) * 8 + 512));
	while(preg_match('/<[^r]/is', $equivalence_string) === 1) { // ignore replace operations at this step
		//print('fractally_process_string002<br>');
		$debug_counter44++;
		if($debug_counter44 > $maxFractalProcessPasses) {
			fractal_zip::warning_once('fractally_process_string: iteration cap exceeded on &lt;…&gt; passes (large binary or many operators); stopping.');
			break;
		}
		$eqBeforeThisPass = $equivalence_string;
		$new_string = '';
		//$changed_something = false;
		
		/*
		// <custom>akdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjsls</custom>
		// even prior to generalized processing; just proving the possibility
		// fractal zipping was actually useful (185 ≤ 259)!
		// Time taken zipping folder: 0.0074958801269531 seconds.
		// 8 different tiles, 4x4 tiles, 16x16 map: 185 ≤ 259 71.4%
		// 4 different tiles, 4x4 tiles, 16x16 map: 136 ≤ 150 90.5ish%
		// 8 different tiles, 12x12 tiles, 16x16 map: 181 ≤ 371 48.8%
		// 8 different tiles, 4x4 tiles, 64x64 map: 193 ≤ 312 61.8% just quadrupled the first test which kind of skews...
		// there is more opportunity for compression with more variety, and bigger tiles, and bigger map in the structured content
		$custom = $O->_('custom');
		if(is_string($custom)) {
			$processed_string = '';
			$line = '';
			$counter = 0;
			$line_counter = 0;
			while($counter < strlen($custom)) {
				$line .= $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter];
				//$line .= $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter] . $custom[$counter];
				$line_counter++;
				if($line_counter === 16) {
					$processed_string .= $line . $line . $line . $line;
					//$processed_string .= $line . $line . $line . $line . $line . $line . $line . $line . $line . $line . $line . $line;
					$line = '';
					$line_counter = 0;
				}
				$counter++;
			}
			return $processed_string;
		}
		// <repeat times="960">a</repeat>
		$repeats = $O->get_tagged('repeat');
		$counter = sizeof($repeats) - 1;
		while($counter > -1) {
			$string_to_repeat = $O->tagless($repeats[$counter]);
			$times_to_repeat = $O->get_attribute('times', $repeats[$counter]);
			$O->delete($repeats[$counter]);
			$new_string = '';
			$repeat_counter = 0;
			while($times_to_repeat > 0) {
				$new_string .= $string_to_repeat;
				$times_to_repeat--;
			}
			//print('$new_string, $O->code, $repeats, $O->context: ');var_dump($new_string, $O->code, $repeats, $O->context);
			//$O->new_($new_string, $repeats[$counter][1]);
			$O->code = O::str_insert($O->code, $new_string, $repeats[$counter][1]);
			$changed_something = true;
			$O->reset_context(); // hack
			//print('$new_string, $O->code, $repeats, $O->context after new_: ');var_dump($new_string, $O->code, $repeats, $O->context);
			$counter--;
		}
		// <replace string="abc" with="def" />
		$replaces = $O->get_tagged('replace');
		$counter = sizeof($replaces) - 1;
		while($counter > -1) {
			$string_to_replace = $O->get_attribute('string', $replaces[$counter]);
			$replacement = $O->get_attribute('with', $replaces[$counter]);
			$O->str_replace($string_to_replace, $replacement);
			$changed_something = true;
			$O->reset_context(); // hack
			$counter--;
		}
		// def<insert string="abc" />ghi
		
		*/
		
		//print('gradient1<br>');
		if(strpos($equivalence_string, '<g') !== false) { // gradient
			//print('gradient2<br>');
			fractal_zip::warning_once('excluding * from the end character is not general. maybe swap positions of end character and tuple');
			preg_match_all('/<g([^>]+)"([0-9]+)"([^\*>]+)\**([0-9]{0,})>/is', $equivalence_string, $gradient_operation_matches, PREG_OFFSET_CAPTURE);
			//print('$gradient_operation_matches: ');var_dump($gradient_operation_matches);
			$counter = sizeof($gradient_operation_matches[0]) - 1;
			while($counter > -1) {
				//print('gradient3<br>');
				$start_character = $gradient_operation_matches[1][$counter][0];
				$step = $gradient_operation_matches[2][$counter][0];
				$end_character = $gradient_operation_matches[3][$counter][0];
				$tuple = $gradient_operation_matches[4][$counter][0];
				$gradient_string = '';
				$character_counter = ord($start_character);
				while($character_counter <= ord($end_character)) {
					//print('gradient4<br>');
					//fractal_zip::warning_once('htmlentities usage will have to be generalized');
					//$gradient_string .= htmlentities(chr($character_counter));
					$gradient_string .= chr($character_counter);
					$character_counter += $step;
				}
				$single_gradient_string = $gradient_string;
				while($tuple > 1) {
					$gradient_string = $gradient_string . $single_gradient_string;
					$tuple--;
				}
				$equivalence_string = substr($equivalence_string, 0, $gradient_operation_matches[0][$counter][1]) . $gradient_string . substr($equivalence_string, $gradient_operation_matches[0][$counter][1] + strlen($gradient_operation_matches[0][$counter][0]));
				$counter--;
			}
		}
		//print('$equivalence_string after gradient: ');var_dump($equivalence_string);
		if(preg_match('/<l([0-9]+)>/is', $equivalence_string, $row_length_operation_matches)) {
			//fractal_zip::warning_once('forcing row length of 9');
			//$row_length = 9;
			$row_length = $row_length_operation_matches[1]; // dirty hack?
			//$found_row_length = false;
			$equivalence_string = substr($equivalence_string, strlen($row_length_operation_matches[0]));
			if(fractal_zip::emit_fractal_process_string_dumps()) {
				print('$row_length, $equivalence_string: ');var_dump($row_length, $equivalence_string);
			}
		}
		//print('$equivalence_string after row length: ');var_dump($equivalence_string);
		//preg_match_all('/<[^r][^<>]+>/is', $equivalence_string, $operation_matches, PREG_OFFSET_CAPTURE);
		preg_match_all('/<[s][^<>]+>/is', $equivalence_string, $operation_matches, PREG_OFFSET_CAPTURE); // only skip; substring is handled above?
		//print('$operation_matches: ');var_dump($operation_matches);
		if(sizeof($operation_matches[0]) > 0) {
			$equivalence_offset = 0;
			//$skips_array = array();
			$delayed_strings = array();
			foreach($operation_matches[0] as $index => $value) {
				//print('fractally_process_string003<br>');
				$operation_string = $value[0];
				$operation_offset = $value[1];
				// add any straight text first
				/*if($operation_offset > $equivalence_offset) {
					$raw_text = substr($equivalence_string, $equivalence_offset, $operation_offset - $equivalence_offset);
					//if(isset($skips_array[$parser_offset])) { // ignoring the amount to skip may be ok?
					//if(isset($skips_array[$parser_offset])) {
					//	//unset($skips_array[$parser_offset]);
					//	if($skips_array[$parser_offset] !== true) {
					//		$skips_array[$parser_offset] = true;
					//	} else {
					//		$new_string .= $raw_text;
					//	}
					//} else {
						$new_string .= $raw_text;
					//}
					$equivalence_offset += strlen($raw_text);
					print('$new_string, $delayed_strings after adding text before the operation: ');var_dump($new_string, $delayed_strings);
				}*/
				if($operation_string[1] === 's') { // skip
					// take care of all skip operations then go back to checking for operations
					$equivalence_string = substr($equivalence_string, $equivalence_offset);
					//$equivalence_offset = 0; // would prefer to cleverly step the offset back; but is that possible?
					if(fractal_zip::emit_fractal_process_string_dumps()) {
						print('$new_string, $equivalence_string before skipping: ');var_dump($new_string, $equivalence_string);
					}
					$rows = array();
					while(strlen($equivalence_string) > 0) { // is this correct?
						$position = 0;
						preg_match('/<s([0-9]+)>/is', $equivalence_string, $skip_operation_matches); // would a parser be faster? optimize later
						$skip_counter = $skip_operation_matches[1];
						$tile = '';
						while($position < strlen($equivalence_string) && $skip_counter > 0) {
							if($equivalence_string[$position] === '<') {
								$tile .= substr($equivalence_string, $position, strpos($equivalence_string, '>', $position) + 1 - $position);
								$position = strpos($equivalence_string, '>', $position);
							} else {
								$tile .= $equivalence_string[$position];
								$skip_counter--;
							}
							$position++;
						}
						// also take operations following
					//	if($equivalence_string[$offset] === '<') {
					//		$moved_text .= substr($equivalence_string, $offset, strpos($equivalence_string, '>', $offset) + 1 - $offset);
					//	}
						fractal_zip::warning_once('forcing two-dimensional rectangle for skipping in unzip');
						fractal_zip::warning_once('forcing only uniform skip operations in unzip');
						$tile_pieces = explode($skip_operation_matches[0], $tile);
						$row_index = 0;
						$found_a_row_with_space = false;
						foreach($rows as $row_index => $row) {
							if(strlen($row) < $row_length) {
								$found_a_row_with_space = true;
								break;
							}
						}
						if(!$found_a_row_with_space) {
							$row_index = sizeof($rows);
						}
						foreach($tile_pieces as $tile_piece) {
							$rows[$row_index] .= $tile_piece;
							$row_index++;
						}
						if(fractal_zip::emit_fractal_process_string_dumps()) {
							print('$rows: ');fractal_zip::var_dump_full($rows);
						}
						$equivalence_string = substr($equivalence_string, $position);
					}
					$new_string .= implode('', $rows);
					if(fractal_zip::emit_fractal_process_string_dumps()) {
						print('$new_string at the end of skipping: ');var_dump($new_string);
					}
					break;
				}/* else { // substring (coded as offset-length pairs) is the default operation
					preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $operation_string, $substring_operation_matches); // would a parser be faster? optimize later
					$substring_offset = $substring_operation_matches[1];
					$substring_length = $substring_operation_matches[2];
					$substring_recursion_counter = $substring_operation_matches[3];
					$new_string .= substr($fractal_string, $substring_offset, $substring_length);
				}*/
				$equivalence_offset += strlen($operation_string);
				//$changed_something = true;
			}
			$equivalence_string = $new_string;
		}
		//print('$equivalence_string after one fractally process loop: ');var_dump($equivalence_string);
		if($equivalence_string === $eqBeforeThisPass) {
			fractal_zip::warning_once('fractally_process_string: no progress (literal &lt; in binary or unknown operator); stopping non-&lt;r pass.');
			break;
		}
	}
	// seem to need to do spanning operations later than self-cloing operations
	if(strpos($equivalence_string, '<r') !== false) {
		preg_match_all('/<r([^"]+)"([^>]+)>(.*?)<\/r>/is', $equivalence_string, $replace_operation_matches, PREG_OFFSET_CAPTURE); // do we need LOM since there is nesting structure?
		$counter = sizeof($replace_operation_matches[0]) - 1;
		while($counter > -1) {
			$search = $replace_operation_matches[1][$counter][0];
			$replace = $replace_operation_matches[2][$counter][0];
			$subject = $replace_operation_matches[3][$counter][0];
			$replaced_string = str_replace($search, $replace, $subject);
			$equivalence_string = substr($equivalence_string, 0, $replace_operation_matches[0][$counter][1]) . $replaced_string . substr($equivalence_string, $replace_operation_matches[0][$counter][1] + strlen($replace_operation_matches[0][$counter][0]));
			$counter--;
		}
	}
	
	//return $O->code;
	//return $new_string;
	//fractal_zip::warning_once('html_entity_decode usage will have to be generalized');
	//$equivalence_string = html_entity_decode($equivalence_string);
	//$equivalence_string = htmlspecialchars_decode($equivalence_string);
	return $equivalence_string;
}
	
function zip($string, $entry_filename, $debug = false, $equivalence_original_override = null) {
	//print('$entry_filename: ');var_dump($entry_filename);
	$this->fractal_marker_ctx_publish();
	$equivalence_original = ($equivalence_original_override !== null) ? $equivalence_original_override : $string;
	$this->entry_filename = $entry_filename;
	fractal_zip::html_trace_print('zipping: ' . $entry_filename . '<br>');
	// attempt to section the file
	// if an AI could be called to notice patterns and section the string, here would be where to do it
	if($debug) {
		print('$string: ');var_dump($string);
	}
	//fractal_zip::warning_once('specific hack; test recursion = 7');
	//$this->fractal_string = $fractal_string;
	//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
	//return true;
	
	/*$byte_lengths = array(1, 2, 4); // 8, 16 and 32-bit covers most file formats
	$byte_length_densities = array();
	foreach($byte_lengths as $byte_length) {
		$offset = 0;
		while($offset < $byte_length) {
			$characters = str_split(substr($string, $offset), $byte_length);
			$characters_counts = array();
			foreach($characters as $character) {
				if(isset($characters_counts[$character])) {
					$characters_counts[$character]++;
				} else {
					$characters_counts[$character] = 1;
				}
			}
			//print('$characters: ');var_dump($characters);
			$densities = array();
			foreach($characters_counts as $character => $count) {
				$densities[$character] = $count / strlen($string);
			}
			//print('$densities: ');var_dump($densities);
			if(isset($byte_length_densities[$byte_length])) {
				if(sizeof($densities) < sizeof($byte_length_densities[$byte_length])) {
					$byte_length_densities[$byte_length] = $densities;
				}
			} else {
				$byte_length_densities[$byte_length] = $densities;
			}
			$offset++;
		}
	}
	print('$byte_length_densities: ');var_dump($byte_length_densities);
	$significant_items = array();
	$major_item = false;
	foreach($byte_length_densities as $byte_length => $densities) {
		foreach($densities as $item => $density) {
			if($density > 0.5) {
				$significant_items[$item] = 'major';
				$major_item = $item;
			} elseif($density > 0.02) {
				$significant_items[$item] = 'minor';
			}
		}
	}
	print('$significant_items: ');var_dump($significant_items);
	if($major_item) {
		foreach($significant_items as $significant_item => $item_type) {
			if($item_type === 'minor' && substr_count($significant_item, $major_item) === strlen($significant_item)) {
				unset($significant_items[$significant_item]);
			}
		}
	}
	$significant_items = array_reverse($significant_items, true);
	print('modified $significant_items: ');var_dump($significant_items);
	$sections = array();
	$counter = 0;
	$section = '';
	$section_item_type = false;
	$minor_item = false;
	while($counter < strlen($string)) {
		if($section_item_type === false) {
			foreach($significant_items as $significant_item => $item_type) {
				if(substr($string, $counter, strlen($significant_item)) == $significant_item) {
					if($item_type === 'major') {
						$sections[] = array($section, false);
						$section = '';
						$section_item_type = 'major';
					}
					break;
				}
			}
		} elseif($section_item_type === 'major') {
			foreach($significant_items as $significant_item => $item_type) {
				if(substr($string, $counter, strlen($significant_item)) == $significant_item) {
					if($item_type === 'minor') {
						$sections[] = array($section, 'major');
						$section = $significant_item;
						$section_item_type = 'minor';
						$minor_item = $significant_item;
					} elseif($item_type === 'major') {
						$section .= $significant_item;
					}
					$counter += strlen($significant_item);
					continue 2;
				}
			}
			if($string[$counter] != $major_item) {
				$sections[] = array($section, 'major');
				$section = '';
				$section_item_type = false;
			}
		} elseif($section_item_type === 'minor') {
			foreach($significant_items as $significant_item => $item_type) {
				if(substr($string, $counter, strlen($significant_item)) == $significant_item) {
					if($item_type === 'minor') {
						$found_minor_item = $significant_item;
						if($found_minor_item == $minor_item) {
							$section .= $significant_item;
						} else {
							$sections[] = array($section, 'minor');
							$section = $significant_item;
							$minor_item = $found_minor_item;
						}
					} elseif($item_type === 'major') {
						$sections[] = array($section, 'minor');
						$section = $significant_item;
						$section_item_type = 'major';
					}
					$counter += strlen($significant_item);
					continue 2;
				}
			}
		}
		$section .= $string[$counter];
		$counter++;
	}
	$sections[] = array($section, $section_item_type);
	print('$sections: ');fractal_zip::var_dump_full($sections);*/
	
	// no significant difference in speed between these two
	//$characters_counts = count_chars($string, 1);
	/*$characters = str_split($string, 1);
	$characters_counts = array();
	foreach($characters as $character) {
		if(isset($characters_counts[$character])) {
			$characters_counts[$character]++;
		} else {
			$characters_counts[$character] = 1;
		}
	}*/
	//print('$characters_counts: ');fractal_zip::var_dump_full($characters_counts);
	// could theoretically create a handle on various filetypes this way...
	/*if(strpos($string, '<!DOCTYPE') !== false || strpos($string, '<html') !== false || strpos($string, '</p>') !== false) { // righteous hack
		print('treating this file as HTML<br>');
		$counter = 0;
		$offsets_to_split_at = array();
		while($counter < strlen($string)) {
			if($string[$counter] === '<') {
				$offsets_to_split_at[] = $counter;
			} elseif($string[$counter] === '>') {
				if($string[$counter + 1] === '<') {
					
				} else {
					$offsets_to_split_at[] = $counter + 1;
				}
			}
			$counter++;
		}
	} else {
		$strLen = strlen($string);
		$limiters_sum = 0;
		foreach($this->common_limiters as $this->common_limiter) {
			$limiters_sum += substr_count($string, $this->common_limiter);
		}
		print('$limiters_sum, strlen($string): ');fractal_zip::var_dump_full($limiters_sum, $strLen);
		if($limiters_sum / $strLen > 0.02 && $limiters_sum / $strLen < 0.2) {
			print('handling this file by breaking at the limiters<br>');
			$counter = 0;
			$offsets_to_split_at = array();
			while($counter < $strLen) {
				foreach($this->common_limiters as $this->common_limiter) {
					if($string[$counter] === $this->common_limiter) {
						$offsets_to_split_at[] = $counter;
					}
				}
				$counter++;
			}
		} else {
			$counter = 0;
			//$buffer = '';
			//$max_buffer_length = 10;
			$offsets_to_split_at = array();
			//$last_offset = 0;
			while($counter < $strLen) {
				//if(strlen($buffer) < $max_buffer_length) {
				//	$buffer .= $string[$counter];
				//} else {
					//if((strpos($buffer, $string[$counter]) === false ||
					//fractal_zip::density($string[$counter], $buffer) > 2 * fractal_zip::density($string[$counter], substr($string, 0, $max_buffer_length)) ||
					//fractal_zip::density($string[$counter], $buffer) < 0.5 * fractal_zip::density($string[$counter], substr($string, 0, $max_buffer_length)))
					//&& $counter - $last_offset > $max_buffer_length) {
					if(substr($string, $counter - 4, 4) === substr($string, $counter, 4)) {
						$counter += 4;
						continue;
					} elseif(substr($string, $counter, 4) === substr($string, $counter + 4, 4) && substr($string, $counter - 4, 4) != substr($string, $counter, 4)) {
						$offsets_to_split_at[] = $counter;
						$counter += 4;
						continue;
					} elseif(substr($string, $counter - 2, 2) === substr($string, $counter, 2)) {
						$counter += 2;
						continue;
					} elseif(substr($string, $counter, 2) === substr($string, $counter + 2, 2) && substr($string, $counter - 2, 2) != substr($string, $counter, 2)) {
						$offsets_to_split_at[] = $counter;
						$counter += 2;
						continue;
					} elseif(substr($string, $counter, 1) === substr($string, $counter + 1, 1) && substr($string, $counter - 1, 1) != $string[$counter]) {
						$offsets_to_split_at[] = $counter;
						//$last_offset = $counter;
					}
					//$buffer = substr($buffer, 1) . $string[$counter];
				//}
				$counter++;
			}
		}
	}
	$sections = array();
	$last_offset = 0;
	foreach($offsets_to_split_at as $offset) {
		$sections[] = array(substr($string, $last_offset, $offset - $last_offset), '?');
		$last_offset = $offset;
	}
	print('$sections: ');fractal_zip::var_dump_full($sections);*/
	//print('sizeof($sections): ');fractal_zip::var_dump_full(sizeof($sections));
	// the lazyiest possible fractal string processing; we'll assume that compression will handle the redundancy created rather than trying to craft the fractal string in such a way that useful pieces are prevalent
	
	// making containers containing containers becomes complicated when you consider escaping the data
	// also we have to prevent the trivial solution of referring to the unzipped version of a file being the simplest solution since that solution no longer works when that file is moved or deleted
	// which brings up the point that we'll have to ensure that this doesn't happen for any fractal_zipped files by creating a container (so that the pieces may not be manipulated)
	
	//$string = htmlspecialchars($string); // always; instead of trying to manage when
	//$zipped_string = htmlspecialchars($string); // always; instead of trying to manage when
	// Escape & and < in literals so payload cannot mimic substring/range ops; optionally `>` as &gt; when right marker is '>'
	// (default on — disable with FRACTAL_ZIP_LITERAL_ESCAPE_GT=0 if size regression on HTML-heavy corpora).
	$zipped_string = $this->escape_literal_for_storage($string);
	//print('$string, strlen($string), $zipped_string, strlen($zipped_string): ');var_dump($string, strlen($string), $zipped_string, strlen($zipped_string));exit(0);
	// we could of course get fancy and do various things; including using higher base number to save characters and using replace operations under certain insertion and deletion string length conditions
	//$this->lazy_fractal_strings[$this->files_counter] = $string;
	//if(strlen($string) === 1) {
		//$lazy_fractal_zipped_string = strlen($this->lazy_fractal_string);
		//$lazy_fractal_zipped_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . strlen($this->lazy_fractal_string) . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
	//	$lazy_fractal_zipped_string = fractal_zip::mark_range_string(strlen($this->lazy_fractal_string));
	//} else {		
		//$end_offset = strlen($this->lazy_fractal_string) + strlen($string) - 1;
		//$lazy_fractal_zipped_string = strlen($this->lazy_fractal_string) . '-' . $end_offset;
		//$lazy_fractal_zipped_string = $this->left_fractal_zip_marker . $this->fractal_zipping_pass . $this->mid_fractal_zip_marker . strlen($this->lazy_fractal_string) . '-' . $end_offset . $this->mid_fractal_zip_marker . $this->fractal_zipping_pass . $this->right_fractal_zip_marker;
		//$lazy_fractal_zipped_string = fractal_zip::mark_range_string(strlen($this->lazy_fractal_string) . '-' . $end_offset);
		//$lazy_fractal_zipped_string = '<' . strlen($this->lazy_fractal_string) . '"' . $end_offset . '>';
		//$lazy_fractal_zipped_string = '<' . strlen($this->lazy_fractal_string) . '"' . strlen($string) . '>';
		//$lazy_zipped_string = '<' . strlen($this->lazy_fractal_string) . '"' . strlen($string) . '>';
	//}
	if($this->raster_canonical_reuse_lazy_range !== null) {
		$rr = $this->raster_canonical_reuse_lazy_range;
		$this->raster_canonical_reuse_lazy_range = null;
		if(is_array($rr) && sizeof($rr) === 2) {
			$roff = (int) $rr[0];
			$rlen = (int) $rr[1];
			$lazy_zipped_string = $this->left_fractal_zip_marker . $roff . $this->mid_fractal_zip_marker . $rlen . $this->right_fractal_zip_marker;
			$this->lazy_equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
			$this->fractal_string = $this->lazy_fractal_string;
			$this->equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
			return true;
		}
	}
	$offBefore = strlen($this->lazy_fractal_string);
	$this->lazy_fractal_string .= $zipped_string;
	$lazy_zipped_string = $this->left_fractal_zip_marker . $offBefore . $this->mid_fractal_zip_marker . strlen($zipped_string) . $this->right_fractal_zip_marker;
	if($this->zip_pending_raster_canonical_hash !== null) {
		$this->raster_canonical_hash_to_lazy_range[$this->zip_pending_raster_canonical_hash] = array($offBefore, strlen($zipped_string));
		$this->zip_pending_raster_canonical_hash = null;
	}
	$this->lazy_equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
	if($this->zip_raster_canonical_lazy_only) {
		$this->zip_raster_canonical_lazy_only = false;
		$this->fractal_string = $this->lazy_fractal_string;
		$this->equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
		return true;
	}
	$memberMaxFractal = fractal_zip::member_fractal_max_bytes();
	if($memberMaxFractal > 0 && strlen($zipped_string) > $memberMaxFractal) {
		$this->fractal_string = $this->lazy_fractal_string;
		$this->equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
		return true;
	}
	if($this->should_force_lazy_member($entry_filename, $string)) {
		$this->fractal_string = $this->lazy_fractal_string;
		$this->equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
		return true;
	}
	//if(sizeof($this->fractal_strings) === 2) {
	//	print('debug 1: more than 2 files "zipped"<br>');print('$this->fractal_strings, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_strings, $this->equivalences, $this->branch_counter);exit(0);
	//}
	// straight adding the whole string to fractal_strings
	//$this->fractal_strings[] = array($this->branch_counter, $string); // branch_id, fractal_string
	//$this->equivalences[] = array($string, $entry_filename, $this->branch_counter); // filename, string, fractal zipped expression
	//$this->branch_counter++;
	
	// true fractal zip
	if(true) {
		fractal_zip::warning_once('there\'s code above relating to different file formats and row length');
		fractal_zip::warning_once('$this->improvement_factor_threshold');
		
		/*fractal_zip::warning_once('hard-coded recursive fractal substring');
		$this->fractal_string .= 'a<12"17>aaaabb<0"12>b<0"12>bb';
		//$this->equivalences[] = array($string, $entry_filename, 'a<12"17"4>aaaa');
		$recursion_counter = 0;
		//$zipped_string = $string;
		$did_something = true;
		while($did_something) {
			$did_something = false;
			// hard-code the two options
			$fractal_substring = substr($this->fractal_string, 0, 12);
			if($recursion_counter === 0) {
				$fractal_substring = preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_substring);
			}
			$zipped_string = str_replace($fractal_substring, '<0"12>', $zipped_string, $count);
			if($count > 0) {
				$did_something = true;
			}
			
			$fractal_substring = substr($this->fractal_string, 12, 17);
			if($recursion_counter === 0) {
				$fractal_substring = preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_substring);
			}
			$zipped_string = str_replace($fractal_substring, '<12"17>', $zipped_string, $count);
			if($count > 0) {
				$did_something = true;
			}
			
			print('$this->fractal_string, $zipped_string, $recursion_counter: ');var_dump($this->fractal_string, $zipped_string, $recursion_counter);
			$recursion_counter++;
		}
		// really ugly; assuming that everything fits into a single expression
		$zipped_string = str_replace('>', '"' . $recursion_counter . '>', $zipped_string);
		print('$this->fractal_string, $string, $recursion_counter: ');var_dump($this->fractal_string, $string, $recursion_counter);*/
		
		//$zipped_string = $string;
		//$zipped_string = htmlspecialchars($string);
		//print('$zipped_string before gradient: ');var_dump($zipped_string);
		fractal_zip::warning_once('gradient disabled since it is a real hog! as the code stands, we cannot handle code of any signficant length!');
		/*
		// gradient
		//fractal_zip::warning_once('disabled gradient tuples to get substring tuples');
		//fractal_zip::warning_once('hard gradient hack');
		//$zipped_string = '<g0"1"~>';
		$gradient_expressions = array();
		$steps_array = array(1, 2, 3, 4, 8, 16, 32, 64); // roughly intended to correspond to byte index stepping values arising in common data structures (or a guess)
		$maximum_gradient_expression_length = strlen('<gA"64"B>'); // multibyte gradients?
		$offset = 0;
		while($offset < strlen($zipped_string)) {
			$step_counter = 0;
			while($step_counter < sizeof($steps_array)) {
				$sliding_offset = $offset + 1;
				while(ord($zipped_string[$sliding_offset - 1]) === ord($zipped_string[$sliding_offset]) - $steps_array[$step_counter] && $sliding_offset < strlen($zipped_string)) {
					$sliding_offset++;
				}
				if($sliding_offset - $offset > $maximum_gradient_expression_length) {
					$gradient_expression = '<g' . $zipped_string[$offset] . '"' . $steps_array[$step_counter] . '"' . $zipped_string[$sliding_offset - 1] . '>';
					$gradient_expressions[$gradient_expression] = true;
					$zipped_string = substr($zipped_string, 0, $offset) . $gradient_expression . substr($zipped_string, $sliding_offset);
					$offset += strlen($gradient_expression);
					continue 2;
				}
				$step_counter++;
			}
			$offset++;
		}*/
		//print('$zipped_string before simplifying: ');var_dump($zipped_string);exit(0);
		fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
	//	foreach($gradient_expressions as $gradient_expression => $true) {
	//		$zipped_string = fractal_zip::tuples($zipped_string, $gradient_expression);
	//	}		
		//print('$zipped_string after simplifying: ');var_dump($zipped_string);
		//if(strpos($zipped_string, '<g') !== false) {
		//	
		//}
		//$this->fractal_string = '';
		//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		//return true;
		//print('$this->fractal_string, $zipped_string after gradient: ');var_dump($this->fractal_string, $zipped_string);
		
		//fractal_zip::warning_once('forcing empty $this->fractal_string');
		//$this->fractal_string = '';
		//$fractal_substring_result = fractal_zip::fractal_substring($string);
		$fractal_substring_result = fractal_zip::fractal_substring($zipped_string);
	//	print('$fractal_substring_result: ');var_dump($fractal_substring_result);exit(0);
		$fractal_string = $fractal_substring_result[0];
		$zipped_string = $fractal_substring_result[1];
		//$this->fractal_string .= $fractal_string;
		$this->fractal_string = $fractal_string;
		//print('$fractal_string, $this->fractal_string, $zipped_string after fractal_substring: ');var_dump($fractal_string, $this->fractal_string, $zipped_string);
		
		// scale
		//fractal_zip::warning_once('enable scale zipping without hard-coded fractal_string');
		//fractal_zip::warning_once('hard scale hack. it would be complicated to incorporate scaling into the fractal substring code');
		//fractal_zip::warning_once('h4rd c0d|ng');
		//$this->fractal_string = 'aaabbccbbaaa';
		//$longest_continuous = 'aaaaaaaaaaaaaaaaaaaaaaaa';
		$repeat_chunks = array();
		$offset = 0;
		$did_first = false;
		$chunk = '';
		while($offset < strlen($zipped_string)) {
			if($did_first) {
				if($zipped_string[$offset] === $zipped_string[$offset - 1]) {
					
				} else {
					$repeat_chunks[] = $chunk;
					$chunk = '';
				}
			} else {
				$did_first = true;
			}
			$chunk .= $zipped_string[$offset];
			$offset++;
		}
		$repeat_chunks[] = $chunk;
		//print('$repeat_chunks: ');var_dump($repeat_chunks);
		$counter = 0;
		foreach($repeat_chunks as $chunk) {
			if(strlen($chunk) > $counter) {
				$counter = strlen($chunk);
			}
		}
		$best_counter = false;
		$best_sum = 0;
		while($counter > 0) {
			$sum = 0;
			foreach($repeat_chunks as $chunk) {
				if(strlen($chunk) % $counter === 0) {
					$sum += strlen($chunk);
				}
			}
			if($sum * sqrt($counter) > $best_sum * sqrt($best_counter)) { // pulling sqrt out of my ass
				$best_sum = $sum;
				$best_counter = $counter;
			}
			$counter--;
		}
		//print('$best_sum, $best_counter: ');var_dump($best_sum, $best_counter);
		/*$highest_common_factor = 0;
		$common_factor_counter = 0;
		while($common_factor_counter > -1) {
			$all_satisfied_by_this_factor = true;
			foreach($repeat_chunks as $chunk) {
				if(strlen($chunk) % $best_counter === 0) {
					if(strlen($chunk) % $common_factor_counter === 0) {
						
					} else {
						$all_satisfied_by_this_factor = false;
						break;
					}
				}
			}
			if($all_satisfied_by_this_factor) {
				$highest_common_factor = $common_factor_counter;
			}
			$common_factor_counter--;
		}*/
		$highest_common_factor = $best_counter;
		//print('$highest_common_factor: ');var_dump($highest_common_factor);
		if($highest_common_factor > 1) {
			$string_from_dividing_repeats_by_scale = '';
			foreach($repeat_chunks as $chunk) {
				$character_counter = floor(strlen($chunk) / $highest_common_factor);
				while($character_counter > 0) {
					$string_from_dividing_repeats_by_scale .= $chunk[0];
					$character_counter--;
				}
			}
			$this->fractal_string .= $string_from_dividing_repeats_by_scale;
			//print('$string_from_dividing_repeats_by_scale, $this->fractal_string: ');var_dump($string_from_dividing_repeats_by_scale, $this->fractal_string);
			//$zipped_string = '<0"12s0.25><0"12s0.5><0"12s2><0"12s8>';
			$offset = 0;
			//print('scale zip 1<br>');
			while($offset < strlen($zipped_string)) {
				//print('scale zip offset: ' . $offset . '<br>');
				//$scale = 1;
				$scaled_piece = '';
				$sliding_offset = $offset;
				$fractal_string_offset = 0; // bad
				while($zipped_string[$sliding_offset] === $this->fractal_string[$fractal_string_offset] && $sliding_offset < strlen($zipped_string) && $fractal_string_offset < strlen($this->fractal_string)) {
					//print('scale zip 3<br>');
					$scaled_piece .= $zipped_string[$sliding_offset];
					$sliding_offset++;
					$fractal_string_offset++;
				}
				if($sliding_offset === $offset) {
					//print('scale zip 3.9<br>');
					$offset++;
					continue;
				}
				if($zipped_string[$sliding_offset + 1] === $zipped_string[$sliding_offset] && $sliding_offset < strlen($zipped_string)) { // look to use a greater than 1 scale
					//print('scale zip 4<br>');
					while($zipped_string[$sliding_offset + 1] === $zipped_string[$sliding_offset] && $sliding_offset < strlen($zipped_string)) {
						//print('scale zip 5.5<br>');
						$sliding_offset++;
					}
					//print('$fractal_string_offset, $sliding_offset, $offset: ');var_dump($fractal_string_offset, $sliding_offset, $offset);
					//$scale = ($fractal_string_offset + 2) / ($sliding_offset - $offset);
				} else { // look to use a less than 1 scale (does not seem to work)
					//print('scale zip 5<br>');
					while($this->fractal_string[$fractal_string_offset + 1] === $this->fractal_string[$fractal_string_offset] && $fractal_string_offset < strlen($this->fractal_string)) {
						//print('scale zip 4.5<br>');
						$fractal_string_offset++;
					}
				}
				$scale = ($sliding_offset - $offset + 1) / $fractal_string_offset;
				$scaled_expression = '<0"' . strlen($this->fractal_string) . 's' . round($scale, 4) . '>';
				$scaled_piece = $this->fractally_process_string($scaled_expression); // bad
				//print('$scaled_expression, $scaled_piece before checking if they are good: ');var_dump($scaled_expression, $scaled_piece);
				if(substr($zipped_string, $offset, strlen($scaled_piece)) === $scaled_piece) {
					//print('scale zip 6<br>');
				} else {
					//print('scale zip 7<br>');
					$offset++;
					continue;
				}
				//print('scale zip 8<br>');
				//if(strlen($scaled_piece) > fractal_zip::maximum_scale_expression_length()) {
				if(strlen($scaled_piece) > strlen($scaled_expression)) {
					//print('scale zip 9<br>');
					$zipped_string = substr($zipped_string, 0, $offset) . $scaled_expression . substr($zipped_string, $offset + strlen($scaled_piece));
					$offset += strlen($scaled_expression);
					continue;
				}
				$offset++;
			}
		}
		//print('$this->fractal_string, $zipped_string after scale: ');var_dump($this->fractal_string, $zipped_string);
		//$this->fractal_string = '';
		//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		//return true;
		
		
		
		
		$this->equivalences[] = array($equivalence_original, $entry_filename, $zipped_string);
		return true;
		
		//print('$string: ');var_dump($string);exit(0);
		$scores = array();
		//fractal_zip::warning_once('forcing row length of 9 in zip');
		//$row_length = 9;
		//$row_length = 1;
		//fractal_zip::warning_once('forcing tile width of 3 in zip');
		//$tile_width = 3;
		// need some cleverness...
	//	$debug_counter = 0;
		$tile_width = 2; // has to be more than one to be considered a tile
		while($tile_width < strlen($string)) { // really??
			//fractal_zip::warning_once('forcing tile height of 5 in zip');
			//$tile_height = 5;
			$tile_height = 2;
			while($tile_height < strlen($string)) { // really??
				if(strlen($string) === $tile_width * $tile_height) { // avoid the trivial solution of a single tile for the whole code
					$tile_width++;
					continue 2;
				}
				$row_length = $tile_width;
				//$row_length = $tile_width * $tile_height; // hacky?
				while($row_length < strlen($string)) {
					if($row_length < $tile_width) {
						continue;
					}
					//print('$row_length: ');var_dump($row_length);
					$rows = array();
					$offset = 0;
					$column = 0;
					$row = 0;
					while($offset < strlen($string)) {
						$rows[$row] .= $string[$offset];
						$offset++;
						$column++;
						if($column === $row_length) {
							$column = 0;
							$row++;
						}
					}
					fractal_zip::warning_once('forcing all rows to be the same length (which makes sense for the non-fractal dimension (2) that is forced) but there is no allowance for only analyzing part of the string for 2-dimensionality');
					//foreach($rows as $row_string) {
					//	if(strlen($row_string) !== $row_length) {
					//		$row_length += $tile_width;
					//		continue 2;
					//	}
					//}
					if(strlen($rows[sizeof($rows) - 1]) !== $row_length) {
						$row_length += $tile_width;
						continue;
					}
					if(sizeof($rows) % $tile_height !== 0) { // column height does not make sense with tile height
						$row_length += $tile_width;
						continue;
					}
					//print('$rows: ');fractal_zip::var_dump_full($rows);
					$skipping_string = '';
					$column = 0;
					$width = 0;
					$row = 0;
					$height = 0;
					//$skipping_commands = 0;
					//$debug_counter = 0;
					// need to measure how good row length, tile width, tile height choices are
					while($row < sizeof($rows)) {
						while($height < $tile_height) {
							while($width < $tile_width) {
								$skipping_string .= $rows[$row][$column];
								$column++;
								$width++;
							}
							$row++;
							$height++;
							if($height < $tile_height) {
								if($skipping_string[strlen($skipping_string) - 1] === '>') { // shouldn't have consecutive skip statements
									$row_length += $tile_width;
									continue 3;
								}
								$skipping_string .= '<s' . $tile_width * $tile_height . '>';
								//$skipping_commands++;
								$column -= $tile_width;
								$width = 0;
							} else {
								$height = 0;
								$width = 0;
								if($column === $row_length) {
									$column = 0;
									continue 2;
								}
								$row -= $tile_height;
							}
						}
					}
					//print('$skipping_string: ');fractal_zip::var_dump_full($skipping_string);
						/*fractal_zip::warning_once('forcing skipped tile strlen of 35 just as a simple hack');
						$fractal_string = '';
						$zipped_string = '<l' . $row_length . '>';
						$offset = 0;
						$counter = 0;
						$piece = '';
						while($offset < strlen($skipping_string)) {
							$piece .= $skipping_string[$offset];
							$counter++;
							if($counter === 35) {
								$position = strpos($fractal_string, $piece);
								if($position === false) {
									$position = strlen($fractal_string);
									$fractal_string .= $piece;
								}
								$zipped_string .= '<' . $position . '"35>';
								$counter = 0;
								$piece = '';
							}
							$offset++;
						}*/
					$fractal_substring_result = fractal_zip::fractal_substring($skipping_string);
					print('$rows, $tile_width, $tile_height, $row_length, $skipping_string, $fractal_substring_result: ');var_dump($rows, $tile_width, $tile_height, $row_length, $skipping_string, $fractal_substring_result);
				//	$debug_counter++;
				//	if($debug_counter > 10) {
				//		fractal_zip::fatal_error('$debug_counter > 10');
				//	}
					$fractal_string = $fractal_substring_result[0];
					$zipped_string = $fractal_substring_result[1];
					$zipped_string = '<l' . $row_length . '>' . $zipped_string;
					if(strlen($fractal_string) === 0) {
						
					} else {
						//print('strlen($skipping_string), strlen($fractal_string), strlen($zipped_string): ');var_dump(strlen($skipping_string), strlen($fractal_string), strlen($zipped_string));
						//$scores[(string)(strlen($skipping_string) / (strlen($fractal_string) + strlen($zipped_string)))] = array($row_length, $fractal_string, $zipped_string);
						$scores[(string)(strlen($string) / (strlen($fractal_string) - strlen($this->fractal_string) + strlen($zipped_string)))] = array($tile_width, $tile_height, $row_length, $fractal_string, $zipped_string);
						
						
						//print('zipping $scores ugh: ');var_dump($scores);
						//$row_length++;
					}
					$row_length += $tile_width;
					//break; // hacky
				}
				$tile_height++;
			}
			$tile_width++;
		}
		ksort($scores);
		$scores = array_reverse($scores, true);
		print('zipping $scores: ');var_dump($scores);exit(0);
		
		//$this->fractal_string .= $fractal_string;
		$this->fractal_string = $fractal_string;
		$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		
		//$this->equivalences[] = array('aaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaaaaaakkkkddddllllssss;;;;ffffaaaaaaaa;;;;sssskkkkddddffffkkkkaaaassssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkssssllllffffjjjjaaaallllddddkkkkffffjjjjaaaa;;;;ssssllllddddkkkkaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaa;;;;ssssllllddddkkkkffffffffjjjjddddllllssss;;;;aaaa;;;;ssssaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaaaaaassssddddddddssssffffssssddddffffaaaassssssssddddffffddddaaaajjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjlllllllljjjjkkkkkkkkllll;;;;jjjjlllllllljjjj;;;;kkkkjjjjkkkkjjjjllllllllkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaakkkkffffjjjjddddkkkkssssllllaaaa;;;;ffffkkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjsssskkkkddddllllssss;;;;aaaajjjjffffkkkkdddd;;;;aaaakkkkddddjjjjssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssssllllsssskkkkddddjjjjffff;;;;aaaallllddddkkkkddddjjjjssssllllssss', 
		//$entry_filename, '<custom>akdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjslsakdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjslsakdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjslsakdls;faa;skdfkaslfjaldkfja;sldka;sldkffjdls;a;sasddsfsdfassdfdajkkl;jllj;kjkjllkdls;akfjdksla;fkdls;ajfkd;akdjslskdjf;aldkdjsls</custom>');
		
		return true;
	}
	
	
	if($debug) {
		print('$this->fractal_string, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
	}
	return true;
}

function fractal_replace($search, $replace, $string, $offset = 0) {
	//print('$search, $replace, $string, $offset at the start of fractal_replace: ');var_dump($search, $replace, $string, $offset);
	//$this->fractal_replace_debug_counter++;
	//if($this->fractal_replace_debug_counter === 18) {
	//	fractal_zip::fatal_error('$this->fractal_replace_debug_counter === 18');
	//}
	if($search === $replace) {
		return $string;
	}
	$this->fractal_marker_ctx_publish();
	$rxTag = '/' . fractal_zip_marker_rx_substring_tag_simple() . '/is';
	$L = fractal_zip::$fractal_marker_ctx_left;
	$M = fractal_zip::$fractal_marker_ctx_mid;
	$R = fractal_zip::$fractal_marker_ctx_right;
	$this->final_fractal_replace = $replace;
	//if(substr_count($replace, '<') > 1) {
	//	fractal_zip::fatal_error('substr_count($replace, \'<\') > 1');
	//}
	$initial_string = $string;
	$initial_offset = $offset;
	$initial_post_offset = $offset + strlen($search);
	preg_match_all($rxTag, $replace, $tag_in_replace_matches, PREG_OFFSET_CAPTURE);
	// [4] is offset adjustment
	// [5] is length adjustment
	foreach($tag_in_replace_matches[0] as $index => $value) {
		$tag_in_replace_matches[4][$index] = 0;
		$tag_in_replace_matches[5][$index] = 0;
	}
	//print('$tag_in_replace_matches: ');var_dump($tag_in_replace_matches);
	$pre = substr($string, 0, $offset);
	//print('$string after replace in fractal_replace:');var_dump($string);
	preg_match_all($rxTag, $pre, $tag_in_pre_matches, PREG_OFFSET_CAPTURE);
	//print('$tag_in_pre_matches: ');var_dump($tag_in_pre_matches);
	$counter = sizeof($tag_in_pre_matches[0]) - 1;
	while($counter > -1) { // reverse order
		$new_tag_in_pre_offset = $tag_in_pre_offset = (int)$tag_in_pre_matches[1][$counter][0];
		$new_tag_in_pre_length = $tag_in_pre_length = (int)$tag_in_pre_matches[2][$counter][0];
		if($tag_in_pre_offset >= $offset) {
			$new_tag_in_pre_length = $tag_in_pre_length + strlen($replace) - strlen($search);
		}
		//print('$new_tag_in_pre_offset, $tag_in_pre_offset, $new_tag_in_pre_length, $tag_in_pre_length: ');var_dump($new_tag_in_pre_offset, $tag_in_pre_offset, $new_tag_in_pre_length, $tag_in_pre_length);
		if($new_tag_in_pre_offset === $tag_in_pre_offset && $new_tag_in_pre_length === $tag_in_pre_length) {
			$counter--;
			continue;
		}
		$tag_in_pre_operation = $tag_in_pre_matches[0][$counter][0];
		$tag_in_pre_recursion = $tag_in_pre_matches[3][$counter][0];
		if($tag_in_pre_recursion !== '') {
			$new_tag_in_pre_operation = $L . $new_tag_in_pre_offset . $M . $new_tag_in_pre_length . $M . $tag_in_pre_recursion . $R;
		} else {
			$new_tag_in_pre_operation = $L . $new_tag_in_pre_offset . $M . $new_tag_in_pre_length . $R;
		}
		foreach($tag_in_replace_matches[0] as $index => $value) {
			if($tag_in_replace_matches[1][$index][0] + $offset <= $tag_in_pre_matches[0][$counter][1] && $tag_in_replace_matches[2][$index][0] >= $tag_in_pre_matches[0][$counter][1] + strlen($tag_in_pre_operation)) {
				//print('beep000002<br>');
				$tag_in_replace_matches[5][$index] += strlen($new_tag_in_pre_operation) - strlen($tag_in_pre_operation);
			}
		}
		$string = substr($string, 0, $tag_in_pre_matches[0][$counter][1]) . $new_tag_in_pre_operation . substr($string, $tag_in_pre_matches[0][$counter][1] + strlen($tag_in_pre_operation));
		$counter--;
	}
	$offset = $offset + strlen($initial_string) - strlen($string);
	foreach($tag_in_replace_matches[0] as $index => $value) {
		if($tag_in_replace_matches[1][$index][0] + $offset <= $initial_offset) {
			//print('beep000001<br>');
			$tag_in_replace_matches[4][$index] += $offset - $initial_offset;
		}
	}
	$post_offset = $offset + strlen($replace);
	foreach($tag_in_replace_matches[0] as $index => $value) {
		if($tag_in_replace_matches[1][$index][0] + $offset >= $initial_post_offset) {
			//print('beep000003<br>');
			$tag_in_replace_matches[4][$index] += $post_offset - $initial_post_offset;
		}
	}
	$string_after_replace = $string = substr($string, 0, $offset) . $replace . substr($string, $offset + strlen($search));
	//print('$string_after_replace: ');var_dump($string_after_replace);
	preg_match_all($rxTag, $string, $tag_in_post_matches, PREG_OFFSET_CAPTURE, $post_offset);
	//print('$tag_in_post_matches: ');var_dump($tag_in_post_matches);
	$counter = sizeof($tag_in_post_matches[0]) - 1;
	while($counter > -1) { // reverse order
		$new_tag_in_post_offset = $tag_in_post_offset = (int)$tag_in_post_matches[1][$counter][0];
		$new_tag_in_post_length = $tag_in_post_length = (int)$tag_in_post_matches[2][$counter][0];
		if($tag_in_post_offset >= $offset) {
			$new_tag_in_post_length = $tag_in_post_length + strlen($replace) - strlen($search);
		}
		//print('$new_tag_in_post_offset, $tag_in_post_offset, $new_tag_in_post_length, $tag_in_post_length: ');var_dump($new_tag_in_post_offset, $tag_in_post_offset, $new_tag_in_post_length, $tag_in_post_length);
		if($new_tag_in_post_offset === $tag_in_post_offset && $new_tag_in_post_length === $tag_in_post_length) {
			$counter--;
			continue;
		}
		$tag_in_post_operation = $tag_in_post_matches[0][$counter][0];
		$tag_in_post_recursion = $tag_in_post_matches[3][$counter][0];
		if($tag_in_post_recursion !== '') {
			$new_tag_in_post_operation = $L . $new_tag_in_post_offset . $M . $new_tag_in_post_length . $M . $tag_in_post_recursion . $R;
		} else {
			$new_tag_in_post_operation = $L . $new_tag_in_post_offset . $M . $new_tag_in_post_length . $R;
		}
		foreach($tag_in_replace_matches[0] as $index => $value) {
			//print('$tag_in_replace_matches[1][$index][0] + $offset, $tag_in_post_matches[0][$counter][1], $tag_in_replace_matches[2][$index][0] + $post_offset, $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation): ');var_dump($tag_in_replace_matches[1][$index][0] + $offset, $tag_in_post_matches[0][$counter][1], $tag_in_replace_matches[2][$index][0] + $post_offset, $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation));
			if($tag_in_replace_matches[1][$index][0] + $offset <= $tag_in_post_matches[0][$counter][1] && $tag_in_replace_matches[2][$index][0] + $post_offset >= $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation)) {
				//print('beep000004<br>');
				$tag_in_replace_matches[5][$index] += strlen($new_tag_in_post_operation) - strlen($tag_in_post_operation);
			}
		}
		$string = substr($string, 0, $tag_in_post_matches[0][$counter][1]) . $new_tag_in_post_operation . substr($string, $tag_in_post_matches[0][$counter][1] + strlen($tag_in_post_operation));
		$counter--;
	}
	fractal_zip::warning_once('hackety; is fractal_replace working perfectly for more complex fractal_strings? need to test');
	//$search = substr($string, 0, strpos($string, 'b'));
	//$replace = 'a<' . strpos($string, 'b') . '"' . (strlen($string) - strpos($string, 'b') + 1) . '>aaaa';
	//$replace = 'a<' . strpos($string, 'b') . '"' . (strlen($string) - strpos($string, 'b')) . '>aaaa';
	//$offset = strpos($string, $search);
	
	//print('$offset, $post_offset: ');var_dump($offset, $post_offset);
	$search = substr($string, $offset, $post_offset - $offset); // can we not set search to replace?
	//$replace = 'a<' . $post_offset . '"' . (strlen($string) - $post_offset - $offset) . '>aaaa';
	//$replace = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<' . $post_offset . '"' . (strlen($string) - $post_offset - $offset) . '>', $search);
	//print('should be ' . htmlentities('<' . $post_offset . '"' . (strlen($string) - $post_offset - $offset) . '>') . '<br>');
	//print('$tag_in_replace_matches at the bottom: ');var_dump($tag_in_replace_matches);
	$counter = sizeof($tag_in_replace_matches[0]) - 1;
	while($counter > -1) { // reverse order
		$tag_in_replace_offset = (int)$tag_in_replace_matches[1][$counter][0];
		$tag_in_replace_length = (int)$tag_in_replace_matches[2][$counter][0];
		$new_tag_in_replace_offset = $tag_in_replace_offset + $tag_in_replace_matches[4][$counter];
		$new_tag_in_replace_length = $tag_in_replace_length + $tag_in_replace_matches[5][$counter];
		$tag_in_replace_operation = $tag_in_replace_matches[0][$counter][0];
		$tag_in_replace_recursion = $tag_in_replace_matches[3][$counter][0];
		if($tag_in_replace_recursion !== '') {
			$new_tag_in_replace_operation = $L . $new_tag_in_replace_offset . $M . $new_tag_in_replace_length . $M . $tag_in_replace_recursion . $R;
		} else {
			$new_tag_in_replace_operation = $L . $new_tag_in_replace_offset . $M . $new_tag_in_replace_length . $R;
		}
		$replace = substr($replace, 0, $tag_in_replace_matches[0][$counter][1]) . $new_tag_in_replace_operation . substr($replace, $tag_in_replace_matches[0][$counter][1] + strlen($tag_in_replace_operation));
		$counter--;
	}
	//$offset = $offset;
	//print('new $search, $replace, $string, $offset at the end of the fractal_replace recursion: ');var_dump($search, $replace, $string, $offset);
	return fractal_zip::fractal_replace($search, $replace, $string, $offset);
}

function add_to_fractal_substrings_array($string, $fractal_substrings_array = false) {
	//print('$string in add_to_fractal_substrings_array: ');var_dump($string);
	if(strlen($string) === 1) {
		//print('afs0001<br>');
		if(isset($fractal_substrings_array[$string])) {
			//print('afs0001.3<br>');
			return array($string => $fractal_substrings_array[$string]);
		} else {
			//print('afs0001.6<br>');
			return array($string => array());
		}
	}
	if($fractal_substrings_array === false) {
		//print('afs0002<br>');
		//print('initial $string, $this->fractal_substrings_array: ');var_dump($string, $this->fractal_substrings_array);
		if(isset($this->fractal_substrings_array[$string[0]])) {
			//print('afs0003<br>');
			$this->fractal_substrings_array[$string[0]] = array_merge($this->fractal_substrings_array[$string[0]], fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $this->fractal_substrings_array[$string[0]]));
		} else {
			//print('afs0004<br>');
			if(isset($this->fractal_substrings_array[$string[0]])) {
				//print('afs0004.3<br>');
				$this->fractal_substrings_array[$string[0]] = fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $this->fractal_substrings_array[$string[0]]);
			} else {
				//print('afs0004.6<br>');
				$this->fractal_substrings_array[$string[0]] = fractal_zip::add_to_fractal_substrings_array(substr($string, 1), array());
			}
		}
		// something to consider: there may be some worth in the patterns that become apparent (to the eye (short stuttery lines or long sustained lines)) when dumping $this->fractal_substrings_array but it seems unlikely that a computer could "see" these patterns unfortunately
		//print('$string, $this->fractal_substrings_array: ');var_dump($string, $this->fractal_substrings_array);
	} else {
		//print('afs0005<br>');
		if(isset($fractal_substrings_array[$string[0]])) {
			//print('afs0006<br>');
			return array($string[0] => array_merge($fractal_substrings_array[$string[0]], fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $fractal_substrings_array[$string[0]])));
		} else {
			//print('afs0007<br>');
			//print('$string, $fractal_substrings_array: ');var_dump($string, $fractal_substrings_array);
			if(isset($fractal_substrings_array[$string[0]])) {
				return array($string[0] => fractal_zip::add_to_fractal_substrings_array(substr($string, 1), $fractal_substrings_array[$string[0]]));
			} else {
				return array($string[0] => fractal_zip::add_to_fractal_substrings_array(substr($string, 1), array()));
			}
		}
	}
}

function minimally_new_substr($string) {
	$n = strlen($string);
	$counter = 0;
	if(!isset($this->fractal_substrings_array[$string[0]])) {
		//return $string[0];
		$counter++;
	} else {
		$fractal_substrings_array = $this->fractal_substrings_array;
		//$minimally_new_substr = '';
		while($counter < $n) {
			if(isset($fractal_substrings_array[$string[$counter]])) {
				$fractal_substrings_array = $fractal_substrings_array[$string[$counter]];
			} else {
				break;
			}
			$counter++;
		}
		//print('substr($string, 0, 0): ');var_dump(substr($string, 0, 0));exit(0);
	}
	$minimally_new_substr = substr($string, 0, $counter);
	//print('$minimally_new_substr before checking: ');var_dump($minimally_new_substr);
	//print('mns0001<br>');
	while(!fractal_zip::is_fractally_clean($minimally_new_substr) && $counter < $n) {
		//print('mns0002<br>');exit(0);
		$minimally_new_substr .= $string[$counter];
		$counter++;
	}
	//print('mns0003<br>');
	//print('$minimally_new_substr after checking: ');var_dump($minimally_new_substr);
	return $minimally_new_substr;
}

/*function get_all_substrings($input, $delim = '') {
    if(strlen($delim) === 0) {
		$arr = str_split($input, 1);
	} else {
		$arr = explode($delim, $input);
	}
    $out = array();
    for ($i = 0; $i < count($arr); $i++) {
        for ($j = $i; $j < count($arr); $j++) {
            $out[] = implode($delim, array_slice($arr, $i, $j - $i + 1));
        }       
    }
    return $out;
}*/

//$subs = get_all_substrings("a b c", " ");
//print_r($subs);

function all_substrings_count($string, $minimum_count = 1) { // minimum_count is unused
	$slen = strlen($string);
	$maxFractal = fractal_zip::max_fractal_analysis_bytes();
	if($maxFractal > 0 && $slen > $maxFractal) {
		return array();
	}
	$coarseCfg = fractal_zip::fractal_substring_coarse_config_for_length($slen);
	$counter = 0;
	$minimum_counter_skip = 1;
	$substr_records = array();
	$baseMaxExpr = fractal_zip::maximum_substr_expression_length();
	$minimum_substr_length = $baseMaxExpr;
	$length_string = (string) $slen;
	$multiple = 15 - strlen($length_string);
	if($multiple < 3) {
		$multiple = 3;
	}
	if($coarseCfg['enabled'] && $coarseCfg['multiple_cap'] > 0) {
		$multiple = min($multiple, $coarseCfg['multiple_cap']);
	}
	$maximum_substr_length = $baseMaxExpr * $multiple;
	if($slen > 131072) {
		$hardCap = $baseMaxExpr * 24;
		if($maximum_substr_length > $hardCap) {
			$maximum_substr_length = $hardCap;
		}
	}
	if($coarseCfg['enabled'] && $coarseCfg['hardcap_mult'] > 0) {
		$hardCap2 = $baseMaxExpr * $coarseCfg['hardcap_mult'];
		if($maximum_substr_length > $hardCap2) {
			$maximum_substr_length = $hardCap2;
		}
	}
	$maxSubstrRecordEntries = $coarseCfg['max_records'];
	fractal_zip::warning_once('I believe there is room at this counter_skip to sacrifice compression for speed');
	fractal_zip::warning_once('hacking counter_skip');
	//$highest_substr_count = 0;
	while($counter < $slen - $minimum_substr_length) {
		if(sizeof($substr_records) > $maxSubstrRecordEntries) {
			break;
		}
		//$best_substr = false;
		$counter_skip = 1;
		$sliding_counter = $minimum_substr_length;
		//while(($sliding_counter < $this->segment_length || $string[$counter + $sliding_counter + 1] === $string[$counter + $sliding_counter]) && $counter + $sliding_counter < $slen) {
		// (A || B) && C with A=sliding<max, B=ch[p+1]===ch[p], C=(p<slen) — $p = $counter + $sliding_counter; exit when !((A||B)&&C) (same as original while)
		while(true) {
			$p = $counter + $sliding_counter;
			if($p >= $slen) {
				break;
			}
			if($sliding_counter >= $maximum_substr_length && $string[$p + 1] !== $string[$p]) {
				break;
			}
			if($string[$p + 1] === $string[$p]) {
			} else {
				$substr = substr($string, $counter, $sliding_counter);
				$count = $substr_records[$substr];
				if(isset($count)) {
					//if($count > $highest_substr_count) {
					//	$highest_substr_count = $count;
					//}
					//$counter_skip++; // just crazy enough to work??
					$counter_skip = strlen($substr);
					//$counter_skip = strlen($substr) * $substr_records[$substr];
					//break; // preferring data at the start?
					//if($count === $highest_substr_count) {
					//	break;
					//}
				}
				$substr_records[$substr]++;
				//$best_substr = $substr;
			}
			$sliding_counter++;
			//$sliding_counter += $minimum_substr_length; // balls to the walls
		}
		$substr_records[substr($string, $counter, $sliding_counter)]++;
		//if($best_substr !== false) {
		//	$substr_records[$best_substr]++;
		//}
		//$counter_skip = $sliding_counter;
		if($counter_skip < $minimum_counter_skip) {
			$counter_skip = $minimum_counter_skip;
		}
		//print('$counter_skip: ');var_dump($counter_skip);
		$counter += $counter_skip;
	}
	//print('$substr_records after first order: ');var_dump($substr_records);exit(0);
	// effectively doing second order processing?
	/*$scored_substr_records = array();
	foreach($substr_records as $substr => $count) {
		$scored_substr_records[$substr] = strlen($substr) * $count;
	}
	asort($scored_substr_records, SORT_NUMERIC);
	$scored_substr_records = array_reverse($scored_substr_records);
	$scored_substr_records = array_slice($scored_substr_records, 0, 1); // only keep the top one
	//$string2 = $scored_substr_records[0];
	foreach($scored_substr_records as $string2 => $count2) { break; }
	//print('$scored_substr_records, $string2: ');var_dump($scored_substr_records, $string2);
	$counter = 0;
	$minimum_counter_skip = 1;
	$substr_records = array();
	$minimum_substr_length = fractal_zip::maximum_substr_expression_length();
	$maximum_substr_length = fractal_zip::maximum_substr_expression_length() * 10;
	//$highest_substr_count = 0;
	while($counter < strlen($string2) - $minimum_substr_length) {
		$counter_skip = 1;
		$sliding_counter = $minimum_substr_length;
		while($counter + $sliding_counter < strlen($string2)) {
			$substr = substr($string2, $counter, $sliding_counter);
			$substr_records[$substr] = substr_count($string, $substr);
			$sliding_counter++;
		}
		$counter++;
	}*/
	//$substr_records = fractal_zip::get_all_substrings($string);
	foreach($substr_records as $substr => $count) {
		if($count < 2 || !fractal_zip::is_fractally_clean($substr)) {
			unset($substr_records[$substr]);
		}
	}
	//print('$substr_records: ');var_dump($substr_records);
	fractal_zip::warning_once('sort the substrings according to the most promising and hopefully this in combination with only acting on better than average substrings will save some time');
	// could be smarter here!
	$scored_substr_records = array();
	foreach($substr_records as $substr => $count) {
		$len = strlen($substr);
		// Favor long, repeated chunks (better fractal structure) over tiny high-frequency pieces.
		$scored_substr_records[$substr] = $len * $len * $count;
	}
	asort($scored_substr_records, SORT_NUMERIC);
	$scored_substr_records = array_reverse($scored_substr_records);
	//print('$scored_substr_records: ');var_dump($scored_substr_records);
	$baseTopK = $this->effective_substring_top_k();
	$topK = $coarseCfg['enabled'] ? min($baseTopK, $coarseCfg['top_k_cap']) : $baseTopK;
	$scored_substr_records = array_slice($scored_substr_records, 0, $topK, true);
	//$scored_substr_records = array_slice($scored_substr_records, 0, sizeof($segments_array)); // trying to balance for the fact that a very nice and long duplicated substring would be split of multiple segments
	//print('$scored_substr_records top1: ');var_dump($scored_substr_records);
	$sorted_substr_records = array();
	foreach($scored_substr_records as $substr => $score) {
		$sorted_substr_records[$substr] = $substr_records[$substr];
	}
	//print('$substr_records, $sorted_substr_records: ');var_dump($substr_records, $sorted_substr_records);exit(0);
	//return $substr_records;
	return $sorted_substr_records;
}

//function all_substrings_count($string, $minimum_count = 2) {
function all_substrings_count_old($string, $minimum_count = 1) { // tricky to consider the implications
	/*fractal_zip::warning_once('there is room for optimization here: later instances of substrings could be skipped to save time');
	fractal_zip::warning_once('important question: what is better? chunking strings here (saving lots of time if the chunking is done properly) or degreedying later? degreedying has the advantage of being able to stop 
	when you get a good result. of course, if chunking is generalizable it is preferable but I have my doubts that it is. what is the balance point?');*/
	// I suppose we also need to make this function fractal
	$this->fractal_substrings_array = array();
	//print('asc0001<br>');
	//print('$string in all_substrings_count: ');var_dump($string);
	$segments_array = str_split($string, $this->segment_length);
	//$segments_array = str_split($string, fractal_zip::maximum_substr_expression_length() * 10);
	$all_segments_substr_records = array();
	foreach($segments_array as $string) {
		$segLen = strlen($string);
		$maxExpr = fractal_zip::maximum_substr_expression_length();
		$counter = 0;
		//$saved_substr_count = -1;
		$saved_substr = '';
		$substr_records = array();
		while($counter < $segLen - $maxExpr) {
			//fractal_zip::warning_once('minimally_new_substr processing disabled since we may be creating terribly deep 1000+ dimensional arrays');
			//$minimally_new_substr = fractal_zip::minimally_new_substr(substr($string, $counter));
			//if($minimally_new_substr === '') {
			//	break;
			//}
			$minimum_counter_skip = 1;
			//if($piece[0] === '<') {
			//	$minimum_counter_skip = strpos($piece, '>') + 1;
			//}
			//print('asc0002<br>');
			$added_something_this_slide = false;
			$sliding_counter = $counter;
			$piece = substr($string, $sliding_counter, $maxExpr); // not sure if this shortcut is useful now that we are recursing
			while(strlen(fractal_zip::tagless($piece)) < $maxExpr && $sliding_counter < $segLen) {
				//print('asc0002.3<br>');exit(0);
				$piece .= $string[$sliding_counter];
				$sliding_counter++;
			}
			//print('$piece, $minimally_new_substr: ');var_dump($piece, $minimally_new_substr);
			//print('$piece: ');var_dump($piece);
			$sliding_counter += strlen($piece);
			//$did_first = false;
			// it may be a philosophical point: all this looking for patterns in the substring stage is appealing but in order to apply intelligence later, we need to be open to all possibilities
		//	$count = substr_count($string, $piece);
		//	if($count > 1) {
			//print('asc0002.4<br>');
			while(!fractal_zip::is_fractally_clean($piece) && $sliding_counter < $segLen) {
				//print('asc0002.5<br>');exit(0);
				$piece .= $string[$sliding_counter];
				$sliding_counter++;
			}
			//print('asc0002.6<br>');
			//$last_piece = $piece;
			//$last_count = substr_count($string, $piece);
			if(fractal_zip::is_fractally_clean($piece)) {
				$last_piece = $piece;
				$last_count = substr_count($string, $piece);
			} else {
				$last_piece = false;
				$last_count = false;
			}
			//print('first $piece: ');var_dump($piece);
			//if($last_count >= $minimum_count) {
				//fractal_zip::warning_once('only taking the first and last in all_substrings_count. is the rest of the fractal code flexible enough to handle this?');
				// have to meet the requirement according to $this->fractal_substrings_array for a new substring
				while($sliding_counter < $segLen) {
					//print('asc0003<br>');
					$count = substr_count($string, $piece);
					//if(strlen($minimally_new_substr) > $sliding_counter - $counter) {
					//	$substr_records[$piece] = $count;
					//	$piece .= $string[$sliding_counter];
					//	$sliding_counter++;
					//	continue;
					//}
					if($count >= $minimum_count) {
						//print('asc0003.1<br>');
						if(fractal_zip::is_fractally_clean($piece)) {
							//print('asc0003.2<br>');
							if($string[$sliding_counter] !== $string[$sliding_counter - 1]) {
								//print('adding $piece by different characters: ');var_dump($piece);
								$substr_records[$piece] = $count;
								//fractal_zip::add_to_fractal_substrings_array($piece);
								//$added_something_this_slide = true;
							}
							if($count !== $last_count) {
								//print('adding $last_piece by different count: ');var_dump($last_piece);
								if($last_piece !== false) {
									$substr_records[$last_piece] = $last_count;
									//fractal_zip::add_to_fractal_substrings_array($last_piece);
									//$added_something_this_slide = true;
									$last_piece = false;
									$last_count = false;
								}
							} else {
								$last_piece = $piece;
								$last_count = $count;
							}
						}
		//				if(!$did_first) {
		//					$substr_records[$piece] = $count;
		//					$did_first = true;
		//				} else {
		//					$last_piece = $piece;
		//					$last_count = $count;
		//				}
					} else {
						//print('asc0003.4<br>');
						//print('$substr_records when no count: ');var_dump($substr_records);exit(0);
						break;
					}
					$piece .= $string[$sliding_counter];
					$sliding_counter++;
					//print('lengthened $piece: ');var_dump($piece);
				}
			//}
			//print('asc0003.42<br>');
			//if($last_piece !== false && strlen($last_piece) >= strlen($minimally_new_substr) && $last_count >= $minimum_count) {
			if($last_piece !== false && $last_count >= $minimum_count) {
				//print('asc0003.5<br>');
				$substr_records[$last_piece] = $last_count;
				//fractal_zip::add_to_fractal_substrings_array($last_piece);
				//$added_something_this_slide = true;
			}
			//if(strlen($last_piece) >= strlen($minimally_new_substr) && fractal_zip::is_fractally_clean($piece)) {
			if(fractal_zip::is_fractally_clean($piece)) {
				$count = substr_count($string, $piece);
				if($count >= $minimum_count) {
					//print('asc0003.6<br>');
					$substr_records[$piece] = $count;
					//fractal_zip::add_to_fractal_substrings_array($piece);
					//$added_something_this_slide = true;
				}
			}
			//print('asc0003.62<br>');
			/*$saved_substr_count = $substr_count;
			while($sliding_counter < strlen($string) && $substr_count > 1) {
				$substr_count = substr_count($string, $piece . $string[$sliding_counter]);
				if($substr_count < $saved_substr_count) {
					//$saved_substr = $piece;
					foreach($substr_records as $last_piece => $last_count) {  }
					if($saved_substr_count === $last_count && substr($last_piece, strlen($last_piece) - strlen($piece)) === $piece) {
						$counter++;
						continue 2;
					} elseif(strpos(strrev($piece), '>') === 0) { // throw out strings ending with an operator (questionably)
						$counter++;
						continue 2;
					} elseif(substr_count($piece, '>') !== substr_count($piece, '<')) { // throw out partial operators (questionably)
						$counter++;
						continue 2;
					} else {
						$substr_records[$piece] = $saved_substr_count;
					}
					$saved_substr_count = $substr_count;
				}
				$piece .= $string[$sliding_counter];
				$sliding_counter++;
			}*/
			//$counter++;
			fractal_zip::warning_once('I believe there is room at this counter_skip to sacrifice compression for speed');
			$counter_skip = 1;
			//if($added_something_this_slide) {
			//	while($counter + $counter_skip < strlen($string) && $string[$counter] === $string[$counter + $counter_skip]) { // clumsily treating character changes as generally important (which they are in the test files)
			//		//print('asc0003.71<br>');	
			//		$counter_skip++;
			//	}
			//} else {
			//	// there may be a way to get counter_skip from $minimally_new_substr but I do not see it since we must both look for the substr_count changing while extending the piece from the start as well as 
			//	// look for substr_count changes while shortening the piece from the end. we would have to do something like enter an array's data from one of the outer points; something very strange to a computer program
			//	$counter_skip = strlen($minimally_new_substr) - 1;
			//}
			fractal_zip::warning_once('counter_skip has a HUGE impact to prefer speed over compression');
			fractal_zip::warning_once('hacking counter_skip');
			//break; // !!!
			//$counter_skip = strlen($piece) - 1;
			$left_angle_bracket_position = strpos($piece, '&lt;');
			if($left_angle_bracket_position === 0) {
				$right_angle_bracket_position = strpos($piece, '&gt;');
				$counter_skip = $right_angle_bracket_position + 1;
			} elseif($left_angle_bracket_position !== false) {
				$counter_skip = $left_angle_bracket_position;
			} else {
				$counter_skip = strlen($piece) - 1;
			}
			if($counter_skip < $minimum_counter_skip) {
				$counter_skip = $minimum_counter_skip;
			}
			//print('$counter_skip: ');var_dump($counter_skip);
			$counter += $counter_skip;
		}
		//print('$substr_records at end of segment: ');var_dump($substr_records);
		foreach($substr_records as $substr => $count) {
			if(isset($all_segments_substr_records[$substr])) {
				$all_segments_substr_records[$substr] += $count;
			} else {
				$all_segments_substr_records[$substr] = $count;
			}
		}
	}
	foreach($all_segments_substr_records as $substr => $count) {
		if($count < 2) {
			unset($all_segments_substr_records[$substr]);
		}
	}
	//print('$segments_array, $all_segments_substr_records: ');var_dump($segments_array, $all_segments_substr_records);
	$substr_records = $all_segments_substr_records;
	fractal_zip::warning_once('sort the substrings according to the most promising and hopefully this in combination with only acting on better than average substrings will save some time');
	// have to be smarter here!
	//print('end of all_substrings_count()<br>');
	//if(sizeof($substr_records) === 0) {
	//	$substr_records[$string] = 1; // should this be at the end or the start?
	//}
	$scored_substr_records = array();
	foreach($substr_records as $substr => $count) {
		//$scored_substr_records[$substr] = strlen($substr) * $count;
		// weigh the length more heavily... but according to what formula?
		$scored_substr_records[$substr] = pow(strlen($substr), 2) * $count;
	}
	asort($scored_substr_records, SORT_NUMERIC);
	$scored_substr_records = array_reverse($scored_substr_records);
	print('$scored_substr_records: ');var_dump($scored_substr_records);
	//$scored_substr_records = array_slice($scored_substr_records, 0, 3); // only keep the top 3
	$scored_substr_records = array_slice($scored_substr_records, 0, 1); // only keep the top one
	//$scored_substr_records = array_slice($scored_substr_records, 0, sizeof($segments_array)); // trying to balance for the fact that a very nice and long duplicated substring would be split of multiple segments
	print('$scored_substr_records top1: ');var_dump($scored_substr_records);exit(0);
	$sorted_substr_records = array();
	foreach($scored_substr_records as $substr => $score) {
		$sorted_substr_records[$substr] = $substr_records[$substr];
	}
	//print('$substr_records, $sorted_substr_records: ');var_dump($substr_records, $sorted_substr_records);exit(0);
	//return $substr_records;
	return $sorted_substr_records;
}

function fractal_substring($string) {
	//print('start of fractal_substring');exit(0);
	// adding pieces derived from comparison to fractal_strings
	//if(sizeof($this->fractal_strings) === 0) { // would probably prefer to not hard-code this
	// probably goes in the above function
//	if(strlen($this->fractal_string) === 0) { // would probably prefer to not hard-code this
//		//$this->equivalences[] = array($string, $entry_filename, fractal_zip::mark_range_string('0-' . (strlen($string) - 1)));
//		//$this->fractal_string = $string;
//		//$this->equivalences[] = array($string, $entry_filename, '<' . $start_offset . '"' . (strlen($string) - 1) . '>');
//		$fractal_string = $string;
//		$zipped_string = '<' . $start_offset . '"' . (strlen($string) - 1) . '>';
//	} else {
		// special case of an identical string already having been fractal zipped
//		foreach($this->equivalences as $equivalence) {
//			$equivalence_string = $equivalence[0];
//			if($string === $equivalence_string) {
//				$this->equivalences[] = array($string, $entry_filename, $equivalence[2]);
//				//print('$this->fractal_string, $this->equivalences, $this->branch_counter in zip: ');var_dump($this->fractal_string, $this->equivalences, $this->branch_counter);
//				if($debug) {
//					print('special case of an identical string already having been fractal zipped<br>');
//				}
//				return true;
//			}
//		}
		// what's faster, using strpos character-wise or doing a compare to get the piece that is the same?
		// compare takes longer and results in a bigger file even on short strings, so that would seem to be that.
		//$initial_string = $string;
		$fractal_string = $this->fractal_string;
		$this->fractal_marker_ctx_publish();
		$strLen = strlen($string);
		$maxFractal = fractal_zip::max_fractal_analysis_bytes();
		if($maxFractal > 0 && $strLen > $maxFractal) {
			return array($fractal_string . $string, $this->left_fractal_zip_marker . strlen($fractal_string) . $this->mid_fractal_zip_marker . $strLen . $this->right_fractal_zip_marker);
		}
		
//		$this->shorthand_counter = 1;
//		$this->saved_shorthand = array();
		// alter the scope according to filesize; approximating how the size of a fractal container determines how the scope of the viewer will start
		//$fractal_scope_factor = strlen($string) / 100000;
		//if($fractal_scope_factor < 1) {
		//	$fractal_scope_factor = 1;
		//}
	//$debug_counter = 0;
	//$degreedying_debug_counter = 0;
	// this probably shouldn't be a class variable when going into a recursive function but it may be non-impactful
	$this->recursive_fractal_substring_debug_counter = 0;
	//$this->walk_the_path_debug_counter = 0;
	//$recursion_counter = 0;
	$this->string = $string;
	$this->fractal_path_scores = array();
	$fractal_paths = $this->recursive_fractal_substring($string, $fractal_string);
	//print('final $this->fractal_path_scores, $fractal_paths, $recursion_counter: ');fractal_zip::var_dump_full($this->fractal_path_scores, $fractal_paths, $recursion_counter);
	//print('final $this->fractal_path_scores, $fractal_paths: ');fractal_zip::var_dump_full($this->fractal_path_scores, $fractal_paths);
	if(sizeof($fractal_paths) === 0) {
		return array($this->fractal_string . $string, $this->left_fractal_zip_marker . strlen($this->fractal_string) . $this->mid_fractal_zip_marker . $strLen . $this->right_fractal_zip_marker); // so that subsequent files can work from this file's data
	}
	fractal_zip::warning_once('zipping multiple files (which creates a non-empty fractal_string from the second file on) are not handled correctly here');
	asort($this->fractal_path_scores, SORT_NUMERIC);
	$this->fractal_path_scores = array_reverse($this->fractal_path_scores);
	
	//foreach($this->fractal_path_scores as $serialized_best_path => $best_score) { break; }
	
	//fractal_zip::warning_once('euurrkk! while theoretically useful yet never practically shown to be useful comparing compression-wise rather than linearly is a real hog? 14s > 14s on aaaaa<20"25"5>aaaaaaaa');
	// pretty cumbersome but maybe inevitable given that there are many factors to check at varying times
	//fractal_zip::good_news('we should consider using lazy string compared to other compressed results, not simply the best string-wise (linear compression) result');
	// actually now that it's working properly it is of use!!
	// I have not seen an instance where this is effective
	$lazy_fractal_string = $fractal_string . $string;
	$lazy_zipped_string = $this->left_fractal_zip_marker . strlen($fractal_string) . $this->mid_fractal_zip_marker . $strLen . $this->right_fractal_zip_marker;
	$this->array_fractal_zipped_strings_of_files = array();
	//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
	foreach($this->equivalences as $equivalence) {
		$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	$this->array_fractal_zipped_strings_of_files[$this->entry_filename] = $lazy_zipped_string;
	$lazy_fzc_contents = $this->encode_container_payload($this->array_fractal_zipped_strings_of_files, $lazy_fractal_string);
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents, 9);
	$lazy_fzc_contents = $this->adaptive_compress($lazy_fzc_contents);
	$lazyFzcLen = strlen($lazy_fzc_contents);
	$best_score = 0;
	$serialized_best_path = false;
	foreach($this->fractal_path_scores as $serialized_potential_path => $potential_linear_score) {
		$walk_result = fractal_zip::walk_the_path(unserialize($serialized_potential_path), $fractal_paths);
		$potential_fractal_string = $walk_result[2];
		$potential_zipped_string = $walk_result[1];
		$this->array_fractal_zipped_strings_of_files = array();
		//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
		foreach($this->equivalences as $equivalence) {
			$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
		}
		$this->array_fractal_zipped_strings_of_files[$this->entry_filename] = $potential_zipped_string;
		$potential_fzc_contents = $this->encode_container_payload($this->array_fractal_zipped_strings_of_files, $potential_fractal_string);
		//$potential_fzc_contents = gzcompress($potential_fzc_contents, 9);
		$potential_fzc_contents = $this->adaptive_compress($potential_fzc_contents);
		$compressed_score = $lazyFzcLen / strlen($potential_fzc_contents);
		//print('$this->array_fractal_zipped_strings_of_files, $potential_fractal_string, $lazy_fzc_contents, $potential_fzc_contents, $compressed_score: ');var_dump($this->array_fractal_zipped_strings_of_files, $potential_fractal_string, $lazy_fzc_contents, $potential_fzc_contents, $compressed_score);
		if($compressed_score > $best_score) {
			$best_score = $compressed_score;
			$serialized_best_path = $serialized_potential_path;
		}
	}
	
	//fractal_zip::warning_once('end hack');
	//print('$this->fractal_paths[\'aaaaa\']: ');fractal_zip::var_dump_full($this->fractal_paths['aaaaa']);
	//return array('a<12"17>aaaabb<0"12>b<0"12>bb', '<0"12"5>');
	if($best_score < 1) { // this is logical. only reason not to do it would be tiny gains on tiny files (which do not need to be compresed)
		//return array($fractal_string, $string);
		//return array($fractal_string . $string, '<' . strlen($fractal_string) . '"' . strlen($string) . '>'); // so that subsequent files can work from this file's data
		return array($fractal_string . $string, $this->left_fractal_zip_marker . strlen($fractal_string) . $this->mid_fractal_zip_marker . $strLen . $this->right_fractal_zip_marker); // so that subsequent files can work from this file's data
	} else {
		$best_path = unserialize($serialized_best_path);
		//fractal_zip::warning_once('another deadly hack: instead of fixing some code somewhere we are just pruning the last step in the best path!');
		//$best_path = array_slice($best_path, 1);
		//print('$fractal_paths[\'aaaaabbbbbbbbbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'bbbbbb<0"38>b<0"38>bbbbbbaaaaaaaab\']: ');var_dump($fractal_paths['aaaaabbbbbbbbbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['bbbbbb<0"38>b<0"38>bbbbbbaaaaaaaab']);
		//print('$fractal_paths[\'aaaaabbbbbbbbbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\'][\'aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa\']: ');var_dump($fractal_paths['aaaaabbbbbbbbbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']['aaaaabbbbbb<0"38>b<0"38>bbbbbbaaaaaaaa']);
		//print('$fractal_paths: ');var_dump($fractal_paths);
		$walk_result = fractal_zip::walk_the_path($best_path, $fractal_paths);
		//print('$walk_result: ');var_dump($walk_result);exit(0);
		return array($walk_result[2], $walk_result[1]);
	}
	
	//print('$string, $this->fractal_string, $fractal_string, $zipped_string at the end of fractal_substring(): ');var_dump($string, $this->fractal_string, $fractal_string, $zipped_string);
	//if(strlen($string) / (strlen($fractal_string) - strlen($this->fractal_string) + strlen($zipped_string)) > $this->improvement_factor_threshold) {
	//	return array($fractal_string, $zipped_string);
	//} else {
	//	return array($fractal_string, $initial_string);
	//}	
}

function walk_the_path($path, $fractal_paths) {
	//print('$path, $fractal_paths in walk_the_path: ');var_dump($path, $fractal_paths);
	//$this->walk_the_path_debug_counter++;
	//if($this->walk_the_path_debug_counter > 1) {
	//	fractal_zip::fatal_error('$this->walk_the_path_debug_counter > 1');
	//}
	if(sizeof($path) > 1) {
		return fractal_zip::walk_the_path(array_slice($path, 1), $fractal_paths[$path[0]][0]);
	} else {
		return $fractal_paths[$path[0]];
	}
}

function recursive_fractal_substring($string, $fractal_string, $fractal_paths = array(), $path = array(), $recursion_counter = 0, $last_score = false) {
	if($last_score === false) {
		//$last_score = $this->fractal_path_branch_trimming_score;
		//$last_score = 1 / $this->fractal_path_branch_trimming_multiplier; // (identity)
		$last_score = 1; // (identity)
	}
	$best_score = $last_score;
	//print('$string, $fractal_string, $fractal_paths, $path at the start of recursive_fractal_substring: ');var_dump($string, $fractal_string, $fractal_paths, $path);
	//print('rfs0001<br>');exit(0);
	//$did_something = true;
	//while($did_something) {
	//	$did_something = false;	
		//$zipped_string = '';
		$initial_string = $string;
		$initial_fractal_string = $fractal_string;
		$initial_path = $path;
		if($recursion_counter === 0) {
			$this->recursive_fractal_entry_strlen = strlen($initial_string);
		}
		$maxFractalRec = fractal_zip::max_fractal_analysis_bytes();
		$recStrLen = strlen($initial_string);
		if($maxFractalRec > 0 && $recStrLen > $maxFractalRec) {
			return $fractal_paths;
		}
		$maxDepth = fractal_zip::max_recursive_fractal_depth();
		$maxDepth = fractal_zip::fractal_large_entry_effective_max_depth((int) $this->recursive_fractal_entry_strlen, $maxDepth);
		if($recursion_counter > $maxDepth) {
			fractal_zip::warning_once('recursive_fractal_substring depth limit reached; pruning deep branch');
			return $fractal_paths;
		}
		$maxSeconds = fractal_zip::max_recursive_fractal_seconds();
		if($maxSeconds > 0 && microtime(true) - $this->initial_micro_time > $maxSeconds) {
			fractal_zip::warning_once('recursive_fractal_substring time budget reached; pruning deep branch');
			return $fractal_paths;
		}
		$this->fractal_marker_ctx_publish();
		$maxSubstrExprRfs = fractal_zip::maximum_substr_expression_length();
		$thisStringLenRfs = strlen($this->string);

		// looking to add to fractal_string
		fractal_zip::warning_once('only looking to add to fractal_string of recursion_counter < 2. consider limiting substr record entries to something smaller than the default segment_length (20000). maybe 140?? probably too small? consider stepping forward as good compression results and back when we get stuck. this sort of fractal traversal is probably inevitable to truly achieve fractal compression. you cannot know the whole fractal formula for the compression of code until you fully do; approximating based on only part of the fractal is insufficient.');
		//print('here26475480<br>');
	//	if($recursion_counter < 2) {
	//	if($recursion_counter === 1) {
	//		fractal_zip::warning_once('forcing path step at $recursion_counter === 1');
	//		$substr_records['bbbbbb<0"13>b<0"13>bbbbbb'] = 777;
	//	} else {
	//		$substr_records = array();
	//	}
		$substr_records = fractal_zip::all_substrings_count($string);
		//$substr_records = array();
		/*$substr_records = array();
		print('$recursion_counter, $recursion_counter % 2: ');var_dump($recursion_counter, $recursion_counter % 2);
		if($recursion_counter === 0) {
			$substr_records['aaaaaaaaaaaaa'] = 888;
		} elseif($recursion_counter === 1) {
			$substr_records['bbbbbb<0"13>b<0"13>bbbbbb'] = 888;
		} elseif($recursion_counter === 2) {
			$substr_records['aaaaa<13"25"2>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 === 0) { // even
			$substr_records['aaaaa<20"25"' . $recursion_counter . '>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 !== 0) { // odd
			$substr_records['bbbbbb<0"20"' . $recursion_counter . '>b<0"20"' . $recursion_counter . '>bbbbbb'] = 888;
		}
		fractal_zip::warning_once('hacked $substr_records to show off');*/
	//	$substr_records = array_merge($substr_records, fractal_zip::all_substrings_count($string)); // debug
		//print('$string, $fractal_string, $fractal_paths, $path, $substr_records in recursive_fractal_substring: ');var_dump($string, $fractal_string, $fractal_paths, $path, $substr_records);
		//print('$string, $substr_records, $this->fractal_substrings_array: ');var_dump($string, $substr_records, $this->fractal_substrings_array);
		//print('rfs0002<br>');
	//$this->recursive_fractal_substring_debug_counter++;
	//if($this->recursive_fractal_substring_debug_counter > 10) {
	//	fractal_zip::fatal_error('$this->recursive_fractal_substring_debug_counter > 10');
	//}
	//$micro_time_taken = microtime(true) - $this->initial_micro_time;
	//if($micro_time_taken > 10) {
	//	print('$this->fractal_path_scores: ');fractal_zip::var_dump_full($this->fractal_path_scores);
	//	print('$string, $fractal_string, $fractal_paths, $path: ');var_dump($string, $fractal_string, $fractal_paths, $path);
	//	fractal_zip::fatal_error('$micro_time_taken > 10 in recursive_fractal_substring');
	//}
		//fractal_zip::warning_once('for now, do not attempt any sorting of $substr_records. it is an open question whether any sort of optimization is possible here. is there anything more fundamental to reality to use than fractality?');
		//print('$string, $fractal_string, $substr_records: ');var_dump($string, $fractal_string, $substr_records);
		/*print('$substr_records: ');var_dump($substr_records);
		//fractal_zip::warning_once('doing exactly second order of substring_count seems arbitrary');
		fractal_zip::warning_once('quite cumbersome!');
		while(sizeof($substr_records) > 1) {
			$new_substr_records = array();
			foreach($substr_records as $substr => $count) {
				$found_current_substr = false;
				foreach($substr_records as $substr2 => $count2) {
					if(!$found_current_substr) {
						$found_current_substr = true;
					} else {
						// bleh; not using the same function
						// ignore the counts in substr records?
						$counter = 0;
						while($counter < strlen($substr)) {
							$piece = '';
							$counter2 = 0;
							$counter3 = 0;
							while($counter2 < strlen($substr2)) {
								while($substr[$counter + $counter3] === $substr2[$counter2 + $counter3] && $counter2 + $counter3 < strlen($substr2)) {
									$piece .= $substr[$counter + $counter3];
									$counter3++;
									//print('$piece: ');var_dump($piece);
								}
								if(strlen($piece) > fractal_zip::maximum_substr_expression_length()) {
									if(isset($new_substr_records[$piece])) {
										$new_substr_records[$piece]++;
									} else {
										$new_substr_records[$piece] = 1;
									}
								}
								$piece = '';
								//$counter3 = 0;
								$counter2++;
							}
							$counter++;
						}
					}
				}
			}
			//$substr_records = array();
			//foreach($new_substr_records as $substr => $count) {
			//	if($count > 1) {
			//		$substr_records[$substr] = $count;
			//	}
			//}
			//foreach subterr count as index => value
			//foreach(yomentum count towards infinity) {
			//	choose the most upwards
			//	filter as needed by littlest number
			//	go under the hood
			//	blast from the past until you are done
			//	
			//}
			//foreach($new_substr_records as $substr => $count) {
			//	foreach($new_substr_records as $substr2 => $count2) {
			//		if($substr === $substr2) {
			//			
			//		} elseif(strpos($substr, $substr2) !== false && $count2 > $count) {
			//			$substr_records[$substr2] *= $count;
			//			continue 2;
			//		}
			//	}
			//	$substr_records[$substr] = $count;
			//}
			//print('$new_substr_records: ');var_dump($new_substr_records);
			//fractal_zip::warning_once('beyond understanding');
			//$new_new_substr_records = array();
			//foreach($new_substr_records as $substr => $count) {
			//	foreach($new_substr_records as $substr2 => $count2) {
			//		$substr_count1 = substr_count($substr, $substr2);
			//		$substr_count2 = substr_count($substr2, $substr);
			//		if(isset($new_new_substr_records[$substr])) {
			//			$new_new_substr_records[$substr] += $substr_count1 + $substr_count2;
			//		} else {
			//			$new_new_substr_records[$substr] = $substr_count1 + $substr_count2;
			//		}
			//	}
			//}
			//print('$new_new_substr_records: ');var_dump($new_new_substr_records);
			//$array1 = array();
			//foreach($new_substr_records as $substr => $count) {
			//	$array1[$substr] = $new_new_substr_records[$substr] / $count;
			//}
			//print('$array1: ');var_dump($array1);
			$substr_records = $new_substr_records;
			//$substr_records = array();
			//foreach($array1 as $substr => $count) {
			//	if($count > 1) {
			//		$substr_records[$substr] = 1;
			//	}
			//}
			print('$substr_records after distillation step: ');var_dump($substr_records);
		}
		print('$substr_records: ');var_dump($substr_records);exit(0);
		if(sizeof($substr_records) > 0) {
			$best_score = -1;
			$best_piece = '';
			foreach($substr_records as $piece => $substr_count) {
				//print('$piece, strlen($piece): ');var_dump($piece, strlen($piece));
				//if(strlen($piece) * $substr_count > $best_score) { // this would seem to be the natural approach... but it denies higher order structure and (from a tiny amount of testing) doesn't help compression
				if($substr_count > $best_score) { // this would seem to be the natural approach... but it denies higher order structure and (from a tiny amount of testing) doesn't help compression
					$best_score = $substr_count;
					$best_piece = $piece;
				}
			}
			print('$best_score: ');var_dump($best_score);
			fractal_zip::warning_once('does this best piece selection have ANY sort of general applicability?');
			//foreach($substr_records as $piece => $substr_count) {
			//	//print('looking for one after best score<br>');
			//	if($substr_count === $best_score) {
			//		$best_piece = $piece;
			//		break;
			//	}
			//}
			//fractal_zip::warning_once('forcing hard-coded substr on best_piece');
			*/
			/*$best_piece = false;
			if($recursion_counter === 0) {
				//$best_piece = substr($best_piece, 1);
				$best_piece = 'aaaaa';
				fractal_zip::warning_once('deepening the hack');
				if(strpos($string, $best_piece) === false) {
					$best_piece = 'bbbbb';
				}
			} elseif($recursion_counter === 1) {
				//$best_piece = substr($best_piece, 1, strlen($best_piece) - 6);
				$best_piece = 'bb<0"5>b<0"5>bb';
				if(strpos($string, $best_piece) === false) {
					$best_piece = 'a<0"5>aaaa';
				}
			}
			if($best_piece !== false) {
				print('$best_piece: ');var_dump($best_piece);
				$marked_range_string = '<' . strlen($fractal_string) . '"' . strlen($best_piece) . '>';
				$string = str_replace($best_piece, $marked_range_string, $string);
				$fractal_string .= $best_piece;
				$did_something = true;
			}*/
	//	}
	//	}
		
		
		
		//if($recursion_counter === 3) {
		//	print('$string, $substr_records: ');var_dump($string, $substr_records);exit(0);
		//}
		//print('rfs0003 $recursion_counter: ');var_dump($recursion_counter);
		fractal_zip::warning_once('will need to limit how far into the string fractal_zip looks given the manifold ways we are looking at the string if we want reasonable speed. probably something like fractal_zip::maximum_substr_expression_length() * 100 would be greedy enough when attempting potential compression');
		// looking to match what's already in the fractal_string
		//$scores = array(0); // dummy entry for initialization
		//$scores = array($this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)); // requirements progressively increase, but whether this is a good function to increase by is unknown		
		$scores = array($last_score); // requirements are based on the last step rather than a function
		$substr_records_debug_counter = 0;
		
		//if($recursion_counter === 2) {
		//	fractal_zip::warning_once('forcing path step at $recursion_counter === 2');
		//	$substr_records['aaaaa<13"25"2>aaaaaaaa'] = 888;
		//}
		
		$badInterior = $this->left_fractal_zip_marker . $this->mid_fractal_zip_marker;
		if(strpos($string, $badInterior) !== false) { // debug: malformed "<mid" without offset digits
			print('$string: ');var_dump($string);
			fractal_zip::fatal_error('strpos($string, left+mid without digits) !== false 1');
		}
		
		//print('$string, $fractal_string, $substr_records: ');var_dump($string, $fractal_string, $substr_records);
		foreach($substr_records as $piece => $piece_count) {
			//print('$substr_records_debug_counter, $piece: ');var_dump($substr_records_debug_counter, $piece);
			//print('$piece: ');var_dump($piece);
			//$substr_records_debug_counter++;
			//if($substr_records_debug_counter > 4) {
			//	print('$piece, $fractal_paths, $string, $fractal_string, $path1: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);
			//	fractal_zip::fatal_error('$substr_records_debug_counter > 4');
			//}
			//print('rfs0003.1<br>');
			
			//if($piece === 'bbbbbb<0"13>b<0"13>bbbbbb') {
			//	fractal_zip::warning_once('$piece === bbbbbb<0"13>b<0"13>bbbbbb');
			//	$fractal_paths[$piece] = array($this->recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1), $string, $fractal_string);
			//	$this->fractal_path_scores[serialize($path)] = $score;
			//	$scores[] = $score;
			//	continue;
			//}
			
			/*fractal_zip::warning_once('forcing path steps hack debugging hack');
			if($recursion_counter === 0) {
				//$best_piece = substr($best_piece, 1);
			//	$best_piece = 'aaaaa';
			//	fractal_zip::warning_once('deepening the hack');
			//	if(strpos($string, $best_piece) === false) {
			//		$best_piece = 'bbbbb';
			//	}
				//if($piece !== 'bbbbb') {
				//if($piece !== 'aaaaa') {
				//if($piece !== 'aaaaaaaaaaaaa') {
				if($piece !== 'bbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} elseif($recursion_counter === 1) {
				//$best_piece = substr($best_piece, 1, strlen($best_piece) - 6);
			//	$best_piece = 'bb<0"5>b<0"5>bb';
			//	if(strpos($string, $best_piece) === false) {
			//		$best_piece = 'a<0"5>aaaa';
			//	}
				//if($piece !== 'a<0"5>aaaa') {
				//if($piece !== 'bb<0"5>b<0"5>bb') {
				//if($piece !== 'bbbbbb<0"13>b<0"13>bbbbbb') {
				//if($piece !== 'bbbbbbaaaaa<0"39>aaaaaaaabaaaaa<0"39>aaaaaaaabbbbbb') { // fix improving fractal_string
				//if($piece !== 'bbbbbbaaaaa<0"45>aaaaaaaabaaaaa<0"45>aaaaaaaabbbbbb') { // fix improving fractal_string
				if($piece !== 'bbbbbbaaaaa<0"51>aaaaaaaabaaaaa<0"51>aaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} elseif($recursion_counter === 2) {
				//if($piece !== 'a<5"15>aaaa') {
				//if($piece !== 'aaaaa<13"25"2>aaaaaaaa') {
				if($piece !== 'aaaaa<0"59"2>aaaaaaaa') {
					continue;
				}
			} elseif($recursion_counter === 3) {
				//if($piece !== 'bb<0"12>b<0"12>bb') {
				if($piece !== 'bbbbbb<0"20>b<0"20>bbbbbb') {
					continue;
				}
			}*/
			
			//print('rfs0003.2 $piece, $string, $fractal_string, $path1: ');var_dump($piece, $string, $fractal_string, $path);
			$string = $initial_string;
			$fractal_string = $initial_fractal_string;
			$path = $initial_path;
			
			//$recursion_markerless_piece = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $piece); // remove recursion markers when comparing to fractal_string
			$recursion_markerless_piece = fractal_zip::recursion_markerless($piece); // remove recursion markers when comparing to fractal_string
			
			// looking to improve the fractal_string
		
			//fractal_zip::warning_once('hack of only looking to improve the fractal_string when $recursion_counter === 1');
			//fractal_zip::warning_once('hack of only looking to improve the whole initial_fractal_string');
			//fractal_zip::warning_once('hack of only looking to improve the fractal_string using the first fractal_path step');
			/*fractal_zip::warning_once('improving the fractal_string is not working properly at least as evidenced by when we are getting 
			$this->fractal_string = bbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbb
			$zipped_string = <6"13"3>
			which is supposed to resolve to aaaaabbbbbbaaaaabbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbbaaaaaaaabaaaaabbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbbaaaaaaaabbbbbbaaaaaaaa from the dehacking_fractal_substring.txt with recursion of 4
			this can be gotten by not forcing the path step at recursion_counter = 0');*/
			//if($recursion_counter === 1) {
				//fractal_zip::warning_once('REAL hurly');
				//print('$fractal_string, $string before improving the fractal_string: ');var_dump($fractal_string, $string);
				//$fractal_string = 'a<12"17>aaaabb<0"12>b<0"12>bb';
				//$string = 'abba<12"17>aaaaba<12"17>aaaabbaaaa';
				//$search = 'aaaaa';
				//$search = $initial_fractal_string;
				//foreach($fractal_paths as $search => $the_rest_of_the_path) { break; }
				//$search = $this->first_fractal_path_step;
				//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $search);
				//$string_match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
				//print('$fractal_string before looking to improve the fractal_string: ');var_dump($fractal_string);
				if($recursion_counter === 0) {
					//print('adding $piece to $fractal_string 1<br>');
					$fractal_string .= $piece; // piece should never have any recusion markers (or operators) when $recursion_counter === 0 ?
					//$fractal_string .= $recursion_markerless_piece;
				} else {
					//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $this->first_fractal_path_step);
					$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
					//print('$this->first_fractal_path_step, $string, $string_match_position, $fractal_paths when looking to improve the fractal_string : ');var_dump($this->first_fractal_path_step, $string, $string_match_position, $fractal_paths);
					//print('$fractal_string, $piece, $match_position when looking to improve the fractal_string : ');var_dump($fractal_string, $piece, $match_position);
					if($match_position !== false) {
						//print('improving $fractal_string<br>');
						//$replace = 'a<5"15>aaaa';
						$search = fractal_zip::tagless($piece);
						//$replace = substr($string, $match_position, $this->length_including_operations);
						//$replace = $piece;
						//$replace = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $piece); // remove recursion markers when comparing to fractal_string
						$replace = $recursion_markerless_piece;
						//print('$this->first_fractal_path_step, $replace when looking to improve the fractal_string : ');var_dump($this->first_fractal_path_step, $replace);exit(0);
						//$offset = 0;
						//$offset = strpos($fractal_string, $this->first_fractal_path_step);
						//$this->fractal_replace_debug_counter = 0;
						$this->final_fractal_replace = $replace; // initialization
						//$fractal_string = fractal_zip::fractal_replace($this->first_fractal_path_step, $replace, $fractal_string, $offset);
						//print('$search, $replace, $fractal_string, $match_position before fractal_replace: ');var_dump($search, $replace, $fractal_string, $match_position);
						$fractal_string = fractal_zip::fractal_replace($search, $replace, $fractal_string, $match_position);
						// should be:			
						//print('$fractal_string, $string should be: ');var_dump('a<12"17>aaaabb<0"12>b<0"12>bb', 'abba<12"17>aaaaba<12"17>aaaabbaaaa');
						//print('$piece, $this->final_fractal_replace when improving the whole initial_fractal_string: ');var_dump($piece, $this->final_fractal_replace);
						//print('$string 00: ');var_dump($string);
						$string = str_replace($piece, $this->final_fractal_replace, $string);
						fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
					//	$string = fractal_zip::tuples($string, $this->final_fractal_replace);
						//print('$string 99: ');var_dump($string);
						$piece = $this->final_fractal_replace;
						fractal_zip::warning_once('when improving the fractal string, the piece that ends up in $fractal_paths is not recursion marked; maybe unimportant');
						//print('$fractal_string, $string after improving the fractal_string: ');var_dump($fractal_string, $string);
						//$string = $zipped_string;
						//$did_something = true;
				//		$recursion_counter++;
				//		continue;
					} else {
						//print('$fractal_string, $piece before adding $piece to $fractal_string: ');var_dump($fractal_string, $piece);
						//$fractal_string .= $piece;
						$fractal_string .= $recursion_markerless_piece;
						//print('$fractal_string, $piece after adding $piece to $fractal_string: ');var_dump($fractal_string, $piece);
					}
				}
			//}
			
			
			//if(strpos($fractal_string, $piece) === false) {
			//	$fractal_string .= $piece;
			//}
			
			
			
			
			//print('rfs0003.3<br>');
			if($recursion_counter === 0) {
				//print('rfs0003.4<br>');
				//$match_position = strpos(preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_string), $piece . $string[$sliding_counter]);
				//$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
				$this->first_fractal_path_step = $piece;
			}// else {
				//print('rfs0003.5<br>');
				//$match_position = strpos($fractal_string, $piece);
				//$this->length_including_operations = strlen($piece);
			//}
			//print('rfs0003.51<br>');
			$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $recursion_markerless_piece);
			//print('$fractal_string, $piece, $match_position2: ');var_dump($fractal_string, $piece, $match_position);
			//print('rfs0003.6<br>');exit(0);
			//$substr_records_debug_counter++;
			//if($substr_records_debug_counter > 18) {
			//	print('$piece, $fractal_paths, $string, $fractal_string, $path1: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);
			//	fractal_zip::fatal_error('$substr_records_debug_counter > 18');
			//}
			//print('$fractal_string, $match_position of $piece in $fractal_string: ');var_dump($fractal_string, $match_position);
			if($match_position !== false) {
				//print('found in fractal_string<br>');
				fractal_zip::warning_once('still need to write the code to ensure that marked recursion_counters do not end up in the fractal_string (fractal_string maintains its flexibility and string fundamentally uses that flexibility');
				if($recursion_counter > 0) {
					$marked_range_string = $this->left_fractal_zip_marker . $match_position . $this->mid_fractal_zip_marker . $this->length_including_operations . $this->mid_fractal_zip_marker . ($recursion_counter + 1) . $this->right_fractal_zip_marker;
				} else {
					$marked_range_string = $this->left_fractal_zip_marker . $match_position . $this->mid_fractal_zip_marker . $this->length_including_operations . $this->right_fractal_zip_marker;
				}
				//print('$match_position, $this->length_including_operations, $marked_range_string: ');var_dump($match_position, $this->length_including_operations, $marked_range_string);
				//$zipped_string .= $marked_range_string;
				//print('$string 11: ');var_dump($string);
				$string = str_replace($piece, $marked_range_string, $string);
				fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
			//	$string = fractal_zip::tuples($string, $marked_range_string);
				//print('$string 22: ');var_dump($string);
				//$fractal_paths[$piece] = array(array(), $string, $fractal_string);
				$path[] = $piece;
				//print('$piece, $fractal_paths, $string, $fractal_string, $path2: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);exit(0);
			//if($substr_records_debug_counter > 28) {
			//	print('$piece, $fractal_paths, $string, $fractal_string, $path2: ');var_dump($piece, $fractal_paths, $string, $fractal_string, $path);
			//	fractal_zip::fatal_error('$substr_records_debug_counter > 28');
			//}
				$score = $thisStringLenRfs / (strlen($string) + strlen($fractal_string)); // crude?
				fractal_zip::warning_once('need to tune this fractal_path branch trimming score1 can we progressively determine the fractal dimension?');
				//print('rfs0003.71<br>');exit(0);
				//print('$score, fractal_zip::average($scores), $this->silent_validate($string, $fractal_string, $this->string): ');var_dump($score, fractal_zip::average($scores), $this->silent_validate($string, $fractal_string, $this->string));
				//print('$string, $fractal_string, $this->string: ');var_dump($string, $fractal_string, $this->string);
				//print('$initial_string, $string, fractal_zip::maximum_substr_expression_length(), $score, fractal_zip::average($scores), $this->silent_validate($string, $fractal_string, $this->string): ');var_dump($initial_string, $string, fractal_zip::maximum_substr_expression_length(), $score, fractal_zip::average($scores), $this->silent_validate($string, $fractal_string, $this->string));exit(0);
				//print('rfs0003.72<br>');exit(0);
				//print('$this->string: ');var_dump($this->string);
				//if($score >= $this->fractal_path_branch_trimming_score) {
				//if($score >= $this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)) {
				//if($score > fractal_zip::average($scores)) {
				//if($score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > fractal_zip::average($scores) * $this->fractal_path_branch_trimming_multiplier && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > fractal_zip::average($scores) * 1.05 && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score * $this->fractal_path_branch_trimming_multiplier && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && $score > $last_score && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $recursion_counter && $score > $best_score && $this->silent_validate($string, $fractal_string, $this->string)) {
				//if($score > $last_score && $this->silent_validate($string, $fractal_string, $this->string)) {
				if($recStrLen - strlen($string) > $maxSubstrExprRfs && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
					//print('adding to fractal_paths<br>');
					//$string = preg_replace('/<([0-9]+)"([0-9]+)"1(\**[0-9]*)(s*[0-9]*)>/is', '<$1"$2$3$4>', $string); // unmarked recursion_counter is understood by later code as recursion_counter = 1
					//$fractal_paths[$piece] = array($this->recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score * $this->fractal_path_branch_trimming_multiplier), $string, $fractal_string);
					$fractal_paths[$piece] = array($this->recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score), $string, $fractal_string);
					//print('rfs0003.73<br>');exit(0);
					//fractal_zip::warning_once('it is worth considering whether we could simply use the best score at each recursion level (ignoring $marked_range_string length) to save much time. ');
					$this->fractal_path_scores[serialize($path)] = $score;
					$scores[] = $score;
					$best_score = $score;
				}
			} else {
				//print('not found in fractal_string<br>');
				//$string = preg_replace('/<([0-9]+)"([0-9]+)"1(\**[0-9]*)(s*[0-9]*)>/is', '<$1"$2$3$4>', $string); // unmarked recursion_counter is understood by later code as recursion_counter = 1
				$fractal_paths[$piece] = array(false, $string, $fractal_string);
			}
		}
		//print('rfs0003.9<br>');exit(0);
		
		// looking to use a substring of the fractal_string
		//$string = $initial_string;
		$fractal_string = $initial_fractal_string;
		//print('$string, $fractal_string before looking to use a substring of the fractal_string: ');var_dump($string, $fractal_string);
		//$path = $initial_path;
		$fractal_substr_records = fractal_zip::all_substrings_count($fractal_string, 1);
		/*$fractal_substr_records = array();
		if($recursion_counter === 0) {
			$fractal_substr_records['aaaaaaaaaaaaa'] = 888;
		} elseif($recursion_counter === 1) {
			$fractal_substr_records['bbbbbb<0"13>b<0"13>bbbbbb'] = 888;
		} elseif($recursion_counter === 2) {
			$substr_records['aaaaa<13"25"2>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 === 0) { // even
			$fractal_substr_records['aaaaa<20"25>aaaaaaaa'] = 888;
		} elseif($recursion_counter % 2 !== 0) { // odd
			$fractal_substr_records['bbbbbb<0"20>b<0"20>bbbbbb'] = 888;
		}
		fractal_zip::warning_once('hacked $fractal_substr_records to show off');*/
		//print('$fractal_string, $fractal_substr_records: ');var_dump($fractal_string, $fractal_substr_records);
		//$recursion_markerless_string = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $string); // remove recursion markers when comparing to fractal_string
		//print('$recursion_markerless_string: ');var_dump($recursion_markerless_string);
		$debug_counter45 = 0;
		foreach($fractal_substr_records as $piece => $piece_count) {
			$string = $initial_string;
			$fractal_string = $initial_fractal_string;
			//print('$string, $fractal_string before looking to use a substring of the fractal_string: ');var_dump($string, $fractal_string);
			$path = $initial_path;
			/*fractal_zip::warning_once('hack4729');
			if($recursion_counter === 1) {
				if($piece !== 'bbbbbbaaaaaaaaaaaaabaaaaaaaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} else*//*if($recursion_counter === 2) {
				if($piece !== 'bbbbbbaaaaa<0"51>aaaaaaaabaaaaa<0"51>aaaaaaaabbbbbb') { // fix improving fractal_string
					continue;
				}
			} else*//*if($recursion_counter === 3) {
				//if($piece !== 'bbbbbb<0"20>b<0"20>bbbbbb') {
				if($piece !== 'aaaaa<0"59>aaaaaaaa') {
					continue;
				}
			}*//* elseif($recursion_counter === 4) {
				if($piece !== 'aaaaa<20"25>aaaaaaaa') {
					continue;
				}
			}*/
			
			
			// looking to improve the string (and fractal_string?)
			if(strpos($string, $badInterior) !== false) { // debug
				fractal_zip::fatal_error('strpos($string, left+mid without digits) !== false');
			}
			$string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
			$recursion_marked_piece = substr($string, $string_match_position, $this->length_including_operations);
			$recursion_markerless_piece = fractal_zip::recursion_markerless($piece);
			//$match_position = strpos($fractal_string, $recursion_markerless_piece);
			$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $recursion_markerless_piece);
			//$match_positions = array();
			//$recursion_markerless_piece = preg_replace('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', '<$1"$2>', $piece); // remove recursion markers when comparing to fractal_string
			//print('$string, $piece, $fractal_string before checking if fractal_string should be improved: ');var_dump($string, $piece, $fractal_string);
			if(strpos(fractal_zip::recursion_markerless($string), $piece) === false) { // if we directly find it in the string then we do not have to improve the fractal_string
				//print('$piece, $string, $fractal_string, $recursion_counter before looking to improve the string2: ');var_dump($piece, $string, $fractal_string, $recursion_counter);
			//if($recursion_counter === 0) {
			//	print('adding $piece to $string 3<br>');
			//	//$fractal_string .= $piece;
			//} else {
				//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $this->first_fractal_path_step);
				//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
				//print('$this->first_fractal_path_step, $string, $string_match_position, $fractal_paths when looking to improve the fractal_string : ');var_dump($this->first_fractal_path_step, $string, $string_match_position, $fractal_paths);
				//print('$string, $piece, $string_match_position when looking to improve the string : ');var_dump($string, $piece, $string_match_position);
				if($string_match_position !== false) {
					//print('improving $string2<br>');
					$search = fractal_zip::tagless($piece);
					//$replace = $recursion_markerless_piece;
					//$replace = fractal_zip::tagless($piece);
					$replace = $recursion_marked_piece;
					//$replace = $piece;
					//print('$search, $replace, $recursion_markerless_piece, $match_position, $fractal_string: ');var_dump($search, $replace, $recursion_markerless_piece, $match_position, $fractal_string);
					//fractal_zip::warning_once('hack3498501');
					//$replace = fractal_zip::fractal_replace('<0"39>', '<0"51>', $replace, 11); // ??
					$did_something = true;
					$last_replace = $replace;
					//$debug_counter38 = 0;
					$replace_offset = 0;
					//print('$string, $piece, $fractal_string before looking to improve the string (and fractal_string?): ');var_dump($string, $piece, $fractal_string);
					while($did_something) {
						//print('$search, $replace, $debug_counter38: ');var_dump($search, $replace, $debug_counter38);
						//$debug_counter38++;
						//if($debug_counter38 > 11) {
						//	print('$search, $replace, $string, $piece, $fractal_string: ');var_dump($search, $replace, $string, $piece, $fractal_string);
						//	fractal_zip::fatal_error('$debug_counter38 > 11');
						//}
						$did_something = false;
						if(preg_match('/' . fractal_zip_marker_rx_substring_main() . '/is', $replace, $matches, PREG_OFFSET_CAPTURE, $replace_offset)) { // would a parser be faster? optimize later
							$recursion_markerless_replace = fractal_zip::recursion_markerless($replace);
							//print('$matches: ');var_dump($matches);
						//preg_match_all('/<([0-9]+)"' . strlen($piece) . '>/is', $piece, $matches, PREG_OFFSET_CAPTURE); // ??
						//$counter = sizeof($matches) - 1;
						//while($counter > -1) {
						//foreach($matches[0] as $index => $value) {
							//$new4756 = '<' . $matches[1][$counter][0] . '"' . strlen($replace) . '>';
							//$new4756 = '<' . $matches[1][$counter][0] . '"' . strlen($replace + $matches[0][$counter][0]) . '>';
							//print('$matches[0][$counter][0], $new4756: ');var_dump($matches[0][$counter][0], $new4756);
							//$replace_string_part_after_this_operator = substr($replace, $matches[0][1] + strlen($matches[0][0]));
							//$replace_without_this_operator = substr($replace, 0, $matches[0][1]) . $replace_string_part_after_this_operator;
							//print('$matches[0][0], $replace_without_this_operator, $matches[0][1]: ');var_dump($matches[0][0], $replace_without_this_operator, $matches[0][1]);
							//$replace = fractal_zip::fractal_replace($replace_string_part_after_this_operator, $matches[0][0] . $replace_string_part_after_this_operator, $replace_without_this_operator, $matches[0][1]); // kind of ugly but probably effective
							$new_offset = $matches[1][0]; // hack; maybe okay since we fractal_replace?
							//print('$matches[1][0], $matches[2][0], $match_position: ');var_dump($matches[1][0], $matches[2][0], $match_position);
							//if($matches[1][0] + $matches[2][0] <= $match_position) {
							//$match_positions[] = 
							//if($match_position !== false) {
							$new_length = $matches[2][0];
							if(strlen($recursion_markerless_replace) > $this->length_including_operations) {
							//if(strlen($recursion_markerless_replace) === $this->length_including_operations && $recursion_markerless_replace === $recursion_markerless_piece) {
							//	
							//} else {
								//$new_length = $matches[2][0] + strlen($matches[0][0]); // only if it refers to itself?
								//$new_length = $matches[2][0] + strlen($replace) - strlen($search); // only if it refers to itself?
								$new_length = $matches[2][0] + strlen($recursion_markerless_replace) - $this->length_including_operations; // only if it refers to itself?
							}
							//print('$recursion_markerless_piece, $match_position, $this->length_including_operations, $new_length: ');var_dump($recursion_markerless_piece, $match_position, $this->length_including_operations, $new_length);
							//$substring_offset = $matches[1][0];
							//$substring_length = $matches[2][0];
							$substring_recursion_counter = $matches[3][0]; // what should be the order of the following markers?
							$substring_tuple = $matches[4][0];
							$substring_scale = $matches[5][0];
							if($substring_recursion_counter > 1) {
								$recursion_part = $this->mid_fractal_zip_marker . $substring_recursion_counter;
							} else {
								$recursion_part = '';
							}
							if($substring_tuple > 1) {
								$tuple_part = $this->range_shorthand_marker . $substring_tuple;
							} else {
								$tuple_part = '';
							}
							if($substring_scale > 1) {
								$scale_part = 's' . $substring_scale;
							} else {
								$scale_part = '';
							}
							
							//print('$new_offset, $new_length, $recursion_part, $tuple_part, $scale_part: ');var_dump($new_offset, $new_length, $recursion_part, $tuple_part, $scale_part);
							$new_operation = $this->left_fractal_zip_marker . $new_offset . $this->mid_fractal_zip_marker . $new_length . $recursion_part . $tuple_part . $scale_part . $this->right_fractal_zip_marker;
							//$replace = fractal_zip::fractal_replace($matches[0][0], $new_operation, $replace_without_this_operator, $matches[0][1]); // kind of ugly but probably effective
							//$replace = fractal_zip::fractal_replace('', $new_operation, $replace_without_this_operator, $matches[0][1]); // kind of ugly but probably effective
							$replace = fractal_zip::fractal_replace($matches[0][0], $new_operation, $last_replace, $matches[0][1]); // kind of ugly but probably effective
							//$replace = substr($replace, 0, $matches[0][$counter][1]) . $new4756 . substr($replace, $matches[0][$counter][1]);
							//$counter--;
						//}
							//print('$search, $fractal_string, $last_replace, $replace: ');var_dump($search, $fractal_string, $last_replace, $replace);
							if($last_replace !== $replace) {
								$did_something = true;
								$replace_offset = $matches[0][1] + strlen($matches[0][0]);
								$last_replace = $replace;
							}
						}
					}
					if(strpos($replace, $badInterior) !== false) { // debug
						print('$fractal_string, $piece, $search, $recursion_marked_piece, $replace: ');var_dump($fractal_string, $piece, $search, $recursion_marked_piece, $replace);
						fractal_zip::fatal_error('strpos($replace, left+mid without digits) !== false');
					}
					//$replace = fractal_zip::fractal_replace($search, $replace, $replace); // ??
					//$match_position = strpos($fractal_string, $recursion_markerless_piece);
					//print('$match_position when looking to improve the string : ');var_dump($match_position);
					$this->final_fractal_replace = $replace; // initialization
					//print('$search, $recursion_marked_piece, $replace, fractal_zip::recursion_markerless($replace), $fractal_string, $piece, $match_position before improving the whole string2: ');var_dump($search, $recursion_marked_piece, $replace, fractal_zip::recursion_markerless($replace), $fractal_string, $piece, $match_position);
					//$fractal_string = fractal_zip::fractal_replace($search, $replace, $fractal_string, $match_position);
					//print('$fractal_string before fractal_replace: ');var_dump($fractal_string);
					$fractal_string = fractal_zip::fractal_replace($search, fractal_zip::recursion_markerless($replace), $fractal_string, $match_position);
					//$fractal_string = str_replace($search, fractal_zip::recursion_markerless($replace), $fractal_string);
					/*$last_fractal_string = $fractal_string;
					$fractal_string_offset = 0;
					$did_something = true;
					$debug_counter39 = 0;
					while($did_something) {
						$debug_counter39++;
						if($debug_counter39 > 10) {
							fractal_zip::fatal_error('$debug_counter39 > 10');
						}
						$did_something = false;
						//$match_position = strpos($fractal_string, $recursion_markerless_piece);
						$fractal_string_match_position = strpos($fractal_string, $recursion_markerless_piece, $fractal_string_offset);
						$fractal_string = fractal_zip::fractal_replace($search, fractal_zip::recursion_markerless($replace), $fractal_string, $fractal_string_match_position);
						print('$last_fractal_string, $fractal_string: ');var_dump($last_fractal_string, $fractal_string);
						if($last_fractal_string !== $fractal_string) {
							$did_something = true;
							$fractal_string_offset = $fractal_string_match_position + strlen($recursion_markerless_piece);
							$last_fractal_string = $fractal_string;
						}
					}*/
					//print('$search, $replace, $fractal_string, $piece, $match_position after improving the whole string2: ');var_dump($search, $replace, $fractal_string, $piece, $match_position);
					$rxRecDig = '/' . preg_quote(fractal_zip::$fractal_marker_ctx_left, '/') . '([0-9]+)' . preg_quote(fractal_zip::$fractal_marker_ctx_mid, '/') . '([0-9]+)' . preg_quote(fractal_zip::$fractal_marker_ctx_mid, '/') . '([0-9])/s';
					if(preg_match($rxRecDig, $fractal_string, $recursion_marker_in_fractal_string_matches)) { // debug
						print('$fractal_string: ');var_dump($fractal_string);
						fractal_zip::fatal_error('recursion marker in fractal_string found');
					}
					//print('$string before improving replace: ');var_dump($string);
					$string = str_replace($recursion_marked_piece, $this->final_fractal_replace, $string);
					fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
				//	$string = fractal_zip::tuples($string, $this->final_fractal_replace);
					//print('$string, $this->final_fractal_replace after improving replace: ');var_dump($string, $this->final_fractal_replace);
					//print('$recursion_markerless_piece, $this->final_fractal_replace, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string before improving replace: ');var_dump($recursion_markerless_piece, $this->final_fractal_replace, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string);
					//$fractal_string = str_replace($recursion_markerless_piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string); // ??
					//$fractal_string = str_replace($piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string); // ??
					//print('$recursion_markerless_piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string after improving replace: ');var_dump($recursion_markerless_piece, fractal_zip::recursion_markerless($this->final_fractal_replace), $fractal_string);
					$recursion_marked_piece = $piece = $this->final_fractal_replace;
					//print('$piece, $recursion_marked_piece, $string, $fractal_string after improving the whole string2: ');var_dump($piece, $recursion_marked_piece, $string, $fractal_string);
					fractal_zip::warning_once('when improving the fractal the piece that ends up in $fractal_paths is not recursion marked; maybe unimportant2');
				}// else {
				//	print('adding $piece to $fractal_string 4<br>');
				//	//$fractal_string .= $piece;
				//}
			} else {
				$piece = $recursion_marked_piece;
			}
			
			//print('$search, $replace, $fractal_string, $piece, $match_position after improving the whole string3: ');var_dump($search, $replace, $fractal_string, $piece, $match_position);
			//$string_match_position = strpos($recursion_markerless_string, $piece);
			//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
			//$string_match_position = fractal_zip::strpos_ignoring_operations($string, $recursion_markerless_piece);
			//print('fractal_substr $piece, $string_match_position: ');var_dump($piece, $string_match_position);
			if($string_match_position !== false) {
				
				//print('$fractal_string, $recursion_markerless_piece, $match_position: ');var_dump($fractal_string, $recursion_markerless_piece, $match_position);
				if($match_position !== false) {
					if($recursion_counter > 0) {
						$marked_range_string = $this->left_fractal_zip_marker . $match_position . $this->mid_fractal_zip_marker . strlen($piece) . $this->mid_fractal_zip_marker . ($recursion_counter + 1) . $this->right_fractal_zip_marker;
					} else {
						$marked_range_string = $this->left_fractal_zip_marker . $match_position . $this->mid_fractal_zip_marker . strlen($piece) . $this->right_fractal_zip_marker;
					}
					//$real_string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
					//$recursion_marked_piece = substr($string, $string_match_position, $this->length_including_operations);
					//print('$recursion_marked_piece, $marked_range_string, $string 33: ');var_dump($recursion_marked_piece, $marked_range_string, $string);
					$string = str_replace($recursion_marked_piece, $marked_range_string, $string);
					fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
				//	$string = fractal_zip::tuples($string, $marked_range_string);
					//print('$string 44: ');var_dump($string);
					$path[] = $piece;
					$score = $thisStringLenRfs / (strlen($string) + strlen($fractal_string)); // crude?
					//print('$string, $fractal_string, $this->string for silent_validate when looking to improve the string (and fractal_string?): ');var_dump($string, $fractal_string, $this->string);
					//$debug_counter45++;
					//if($debug_counter45 > 20) {
					//	fractal_zip::fatal_error('$debug_counter45 > 20');
					//}
					//if($score >= $this->fractal_path_branch_trimming_score) {
					//if($score >= $this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)) {
					//if($score > fractal_zip::average($scores)) {
					//if($score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > fractal_zip::average($scores) * $this->fractal_path_branch_trimming_multiplier && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > fractal_zip::average($scores) * 1.14 && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score * $this->fractal_path_branch_trimming_multiplier && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && $score > $last_score && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $recursion_counter && $score > $best_score && $this->silent_validate($string, $fractal_string, $this->string)) {
					//if($score > $last_score && $this->silent_validate($string, $fractal_string, $this->string)) {
					if($recStrLen - strlen($string) > $maxSubstrExprRfs && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
					// recursion_counter clumsily acts for speed? but only for the test_files
						//$fractal_paths[$piece] = array($this->recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score * $this->fractal_path_branch_trimming_multiplier), $string, $fractal_string);
						$fractal_paths[$piece] = array($this->recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1, $score), $string, $fractal_string);
						$this->fractal_path_scores[serialize($path)] = $score;
						$scores[] = $score;
						$best_score = $score;
					}
				}
			} else {
				$fractal_paths[$piece] = array(false, $string, $fractal_string);
			}
		}
		
		//print('rfs0004<br>');exit(0);
		/*
		// looking to use a substring of the fractal_string
		$string = $initial_string;
		$fractal_string = $initial_fractal_string;
		//print('$string, $fractal_string before looking to use a substring of the fractal_string: ');var_dump($string, $fractal_string);
		$path = $initial_path;
		$counter = 0;
		while($counter < strlen($string) - fractal_zip::maximum_substr_expression_length()) {
			// optimizable? or do we have to try every substr?
			$piece = substr($string, $counter, fractal_zip::maximum_substr_expression_length()); // not sure if this shortcut is useful now that we are recursing
			//print('$counter, fractal_zip::maximum_substr_expression_length(), $string, $piece initially: ');var_dump($counter, fractal_zip::maximum_substr_expression_length(), $string, $piece);
			if(isset($fractal_paths[$piece])) {
				$counter++;
				continue;
			}
			//if($recursion_counter === 0) {
			//	//$match_position = strpos(preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_string), $piece . $string[$sliding_counter]);
			//	$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
			//} else {
			//	$match_position = strpos($fractal_string, $piece);
			//	$this->length_including_operations = strlen($piece);
			//}
			print('$match_position of $piece in $fractal_string while looking to use a substring of the fractal_string: ');var_dump($match_position);
			$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
			if($match_position === false) {
				$counter++;
				continue;
			}
			$sliding_counter = $counter + strlen($piece);
			//print('$piece, $match_position, $sliding_counter, $string, $fractal_string when looking to use a substring of the fractal_string: ');var_dump($piece, $match_position, $sliding_counter, $string, $fractal_string);
			//print(' when found a match: ');var_dump($piece);
			//while($sliding_counter < strlen($string) && $string[$sliding_counter] === $fractal_string[$match_position + strlen($piece)] && strlen($piece) < $this->segment_length) {
			fractal_zip::warning_once('probably need chunking here like in all_substrings');
			while($sliding_counter - 1 < strlen($string) && $match_position + strlen($piece) - 1 < strlen($fractal_string) && $string[$sliding_counter - 1] === $fractal_string[$match_position + strlen($piece) - 1] && strlen($piece) < $this->segment_length) {
				if($recursion_counter !== 0) {
					$this->length_including_operations = strlen($piece);
				}
				//fractal_zip::warning_once('atrocious hack');
				//if($piece === 'bb<0"12>b<0"12>bb') {
				if(fractal_zip::is_fractally_clean($piece)) {
					//print('found the magical piece!');exit(0);
					if($recursion_counter > 0) {
						$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '"' . ($recursion_counter + 1) . '>';
					} else {
						$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '>';
					}
					$string = str_replace($piece, $marked_range_string, $string);
					fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
					$string = fractal_zip::tuples($string, $marked_range_string);
					//$string = str_replace($piece, 'XXX9o9placeholder9o9XXX', $string);
					//fractal_zip::warning_once('probably needs more general applicability');
					//$string = str_replace('>', '"' . $recursion_counter . '>', $string);
					//$string = str_replace('XXX9o9placeholder9o9XXX', $marked_range_string, $string);
					fractal_zip::warning_once('assuming that everything fits into a single expression');
				//	$string = str_replace('>', '"' . ($recursion_counter + 1) . '>', $string);
					$path[] = $piece;
					$score = $thisStringLenRfs / (strlen($string) + strlen($fractal_string)); // crude?
					fractal_zip::warning_once('need to tune this fractal_path branch trimming score2 can we progressively determine the fractal dimension?');
					//if($score >= $this->fractal_path_branch_trimming_score) {
					//if($score >= $this->fractal_path_branch_trimming_score * pow($this->fractal_path_branch_trimming_multiplier, $recursion_counter)) {
					print('$score, fractal_zip::average($scores) while looking to use a substring of the fractal_string: ');var_dump($score, fractal_zip::average($scores));
					if($score > fractal_zip::average($scores)) {
						//$string = preg_replace('/<([0-9]+)"([0-9]+)"1(\**[0-9]*)(s*[0-9]*)>/is', '<$1"$2$3$4>', $string); // unmarked recursion_counter is understood by later code as recursion_counter = 1
						$fractal_paths[$piece] = array($this->recursive_fractal_substring($string, $fractal_string, array(), $path, $recursion_counter + 1), $string, $fractal_string);
						$this->fractal_path_scores[serialize($path)] = $score;
						$scores[] = $score;
					}
				}
				$string = $initial_string;
				$path = $initial_path;
				$sliding_counter++;
				//print('$piece, $sliding_counter, $string, $fractal_string before lengthening piece: ');var_dump($piece, $sliding_counter, $string, $fractal_string);
				$piece .= $string[$sliding_counter - 1];
			}
			$counter++;
		}
		*/
		
		/*$counter = 0;
		//$saved_piece = '';
		while($counter < strlen($string) - fractal_zip::maximum_substr_expression_length()) {
			$sliding_counter = $counter;
			$piece = substr($string, $sliding_counter, fractal_zip::maximum_substr_expression_length()); // not sure if this shortcut is useful now that we are recursing
			$sliding_counter += strlen($piece);
			if($recursion_counter === 0) {
				//$match_position = strpos(preg_replace('/<[0-9]+"[0-9]+>/is', '', $fractal_string), $piece . $string[$sliding_counter]);
				$match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece);
			} else {
				$match_position = strpos($fractal_string, $piece);
				$this->length_including_operations = strlen($piece);
			}
			//print('$match_position: ');var_dump($match_position);
			$found_a_better_match = true;
			if($match_position !== false) {
				while($found_a_better_match) {
					//print('$found_a_better_match<br>');
					$found_a_better_match = false;
					//print('$piece when found a match: ');var_dump($piece);
					while($sliding_counter < strlen($string) && $string[$sliding_counter] === $fractal_string[$match_position + strlen($piece)] && strlen($piece) < $this->segment_length) {
						//print('lengthening match<br>');
						$piece .= $string[$sliding_counter];
						$sliding_counter++;
						//$start_offset = $match_position;
						//$matched_piece_exists = true;
					}
					//print('$piece after finding full match: ');var_dump($piece);
					if($recursion_counter !== 0) {
						$this->length_including_operations = strlen($piece);
					}
					if(strlen($piece) === $this->segment_length || $sliding_counter === strlen($string)) {
						$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '>';
						if(strlen($piece) / strlen($marked_range_string) > 1) {
							//$zipped_string = fractal_zip::shorthand_add($zipped_string, $range_string);
							//print('$piece, $marked_range_string when adding 1: ');var_dump($piece, $marked_range_string);
							$zipped_string .= $marked_range_string;
							$did_something = true;
						} else {
							// hack since other operators will end with >?
				//			$piece = str_replace('>', '"' . $recursion_counter . '>', $piece);
							//print('$piece when adding: ');var_dump($piece);
							$zipped_string .= $piece;
						}
						$counter = $sliding_counter;
						continue 2;
					}
					// look for a better match
					if($recursion_counter === 0) {
						$next_match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece, $match_position + 1);
					} else {
						$next_match_position = strpos($fractal_string, $piece, $match_position + 1);
						$this->length_including_operations = strlen($piece);
					}
					while($next_match_position !== false) {
						//print('$next_match_position !== false<br>');
						if($string[$sliding_counter] === $fractal_string[$next_match_position + strlen($piece)]) {
							$piece .= $string[$sliding_counter];
							$sliding_counter++;
							$start_offset = $match_position = $next_match_position;
							$found_a_better_match = true;
							break;
						}
						if($recursion_counter === 0) {
							$next_match_position = fractal_zip::strpos_ignoring_operations($fractal_string, $piece, $next_match_position + 1);
						} else {
							$next_match_position = strpos($fractal_string, $piece, $next_match_position + 1);
							$this->length_including_operations = strlen($piece);
						}
						//print('$fractal_string, $piece, $match_position + 1, $next_match_position __: ');var_dump($fractal_string, $piece, $match_position + 1, $next_match_position);
					}
				}
				//print('$piece, $zipped_string, $next_match_position: ');var_dump($piece, $zipped_string, $next_match_position);exit(0);
			} else {
				$zipped_string .= $string[$counter];
				$counter++;
				continue;
			}
			//print('$match_position, $piece: ');var_dump($match_position, $piece);
			//if($matched_piece_exists) {
			//if($match_position !== false) {
			//if($match_position !== false && $this->length_including_operations > 10) { // hack for debugging
			if($match_position !== false && $this->length_including_operations > fractal_zip::maximum_substr_expression_length()) {
				$zipped_string .= '<' . $match_position . '"' . $this->length_including_operations . '>';
				$did_something = true;
				$counter += strlen($piece);
				//print('$piece, $zipped_string, $next_match_position: ');var_dump($piece, $zipped_string, $next_match_position);
			} else {
				$zipped_string .= $string[$counter];
				$counter++;
			}

		}
		
		//print('$string, $counter after looking for substrings: ');var_dump($string, $counter);
		if($counter < strlen($string)) {
			$zipped_string .= substr($string, $counter);
		}
		*/
		
		/*fractal_zip::warning_once('probably do not have to degreedy anything since piece is provided as is and it\'s usefulness is scored rather than trying to work with a bad piece');
		//break; // hack
		if(!$did_something) {
			//fractal_zip::fatal_error('here is where we would attempt to de-greedy the fractal?');
			//fractal_zip::warning_once('de-greedying fractal substring is currently lame; it only degreedies in one direction "forward" and only works when all substring operators in the string are the same');
			fractal_zip::warning_once('de-greedying fractal substring is currently lame; it forces use of substring operations preexisting in the fractal_string. this may be idealistically appealing but is certainly a constraint on the possible operators...');
			preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $zipped_string, $tag_matches, PREG_OFFSET_CAPTURE);
			foreach($tag_matches[0] as $index => $value) {
				$counter = $index + 1;
				$unset_something = false;
				$size = sizeof($tag_matches[0]);
				while($counter < $size) {
					if($value[0] === $tag_matches[0][$counter][0]) {
						unset($tag_matches[0][$counter]);
						unset($tag_matches[1][$counter]);
						unset($tag_matches[2][$counter]);
						unset($tag_matches[3][$counter]);
						$unset_something = true;
					}
					$counter++;
				}
				if($unset_something) {
					$tag_matches[0] = array_values($tag_matches[0]);
					$tag_matches[1] = array_values($tag_matches[1]);
					$tag_matches[2] = array_values($tag_matches[2]);
					$tag_matches[3] = array_values($tag_matches[3]);
				}
			}
			//print('$tag_matches: ');var_dump($tag_matches);
			//foreach($tag_matches[0] as $index => $value) {
			//	if($tag_matches[0][0][0] === $value[0]) {
			//		
			//	} else {
			//		fractal_zip::fatal_error('how to de-greedy with different tags in the string is not coded yet');
			//	}
			//}
			//preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $tag_matches[0][0][0], $first_tag_matches);
			preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $fractal_string, $fractal_string_substring_operators);
			//print('$fractal_string_substring_operators: ');var_dump($fractal_string_substring_operators);
			$fractal_string_operator_parameters = array();
			foreach($fractal_string_substring_operators[0] as $index => $value) {
				$fractal_string_operator_parameters[] = array($fractal_string_substring_operators[1][$index], $fractal_string_substring_operators[2][$index], $fractal_string_substring_operators[3][$index]);
			}
			foreach($fractal_string_operator_parameters as $index => $value) {
				$counter = $index + 1;
				$unset_something = false;
				$size = sizeof($fractal_string_operator_parameters);
				while($counter < $size) {
					if($fractal_string_operator_parameters[$index][0] === $fractal_string_operator_parameters[$counter][0] && $fractal_string_operator_parameters[$index][1] === $fractal_string_operator_parameters[$counter][1] && $fractal_string_operator_parameters[$index][2] === $fractal_string_operator_parameters[$counter][2]) {
						unset($fractal_string_operator_parameters[$counter]);
						$unset_something = true;
					}
					$counter++;
				}
				if($unset_something) {
					$fractal_string_operator_parameters = array_values($fractal_string_operator_parameters);
				}
			}
		//	$fractal_string_operator_parameters = array_unique($fractal_string_operator_parameters);
		//	$fractal_string_operator_parameters = array_values($fractal_string_operator_parameters);
			//print('$fractal_string_operator_parameters: ');var_dump($fractal_string_operator_parameters);
			$recursion_marked_something = false;
			$degreedied_something = false;
			foreach($tag_matches[0] as $tag_index => $tag) {
				//print('$tag, $zipped_string: ');var_dump($tag, $zipped_string);
				if($tag_matches[3][$tag_index][0] !== '') {
					//print('$tag_matches[3][$tag_index][0]: ');var_dump($tag_matches[3][$tag_index][0]);
					fractal_zip::fatal_error('how to handle recursion counters on fractal substring operators is not coded yet');
				} elseif($tag_matches[2][$tag_index][0] < fractal_zip::maximum_substr_expression_length()) { // then give up
					
				} elseif($tag[0] === $zipped_string) {
					$zipped_string = str_replace('>', '"' . $recursion_counter . '>', $zipped_string);
					$recursion_marked_something = true;
					continue;
				} else {
					print('degreedying<br>');
					$degreedied_backwards = false;
					foreach($fractal_string_operator_parameters as $index => $value) {
						if($value[0] === $tag_matches[1][$tag_index][0]) {
							$degreedied_string = '<' . $value[0] . '"' . $value[1] . '>' . substr($fractal_string, $value[0] + $value[1], $tag_matches[2][$tag_index][0] - $value[1]);
							$degreedied_backwards = true;
							break;
						}
					}
					//print('$degreedied_backwards: ');var_dump($degreedied_backwards);
					if(!$degreedied_backwards) { // take only a preexisting tag
						$counter = 1;
						//$degreedied_string = $fractal_string[$tag_matches[1][$tag_index][0]] . '<' . ($tag_matches[1][$tag_index][0] + 1) . '"' . ($tag_matches[2][$tag_index][0] - 1) . '>';
						//print('um001<br>');
						while(true) {
							//print('um002<br>');
							if($tag_matches[2][$tag_index][0] - $counter < fractal_zip::maximum_substr_expression_length()) { // then give up
								//print('um003<br>');
								continue 2;
							}
							foreach($fractal_string_operator_parameters as $index => $value) {
								//print('um004<br>');
								//print('$value[0], $tag_matches[1][$tag_index][0] + $counter, $value[1], $tag_matches[2][$tag_index][0] - $counter: ');var_dump($value[0], $tag_matches[1][$tag_index][0] + $counter, $value[1], $tag_matches[2][$tag_index][0] - $counter);
								if($value[0] == $tag_matches[1][$tag_index][0] + $counter && $value[1] == $tag_matches[2][$tag_index][0] - $counter) {
									//print('um005<br>');
									$degreedied_string = substr($fractal_string, $tag_matches[1][$tag_index][0], $counter) . '<' . $value[0] . '"' . $value[1] . '>';
									break 2;
								}
							}
							$counter++;
						}
					}
					//print('$tag[0], $degreedied_string, $zipped_string: ');var_dump($tag[0], $degreedied_string, $zipped_string);
					if($tag[0] === $degreedied_string) {
						//fractal_zip::warning_once('questionable whether this code has general utility or just works for the test case');
						$zipped_string = str_replace($tag[0], str_replace('>', '"' . $recursion_counter . '>', $degreedied_string), $zipped_string);
						$recursion_marked_something = true;
					} else {
						//print('$tag[0], $degreedied_string, $zipped_string, $recursion_counter before degreedying: ');var_dump($tag[0], $degreedied_string, $zipped_string, $recursion_counter);
						$zipped_string = str_replace($tag[0], $degreedied_string, $zipped_string);
						//print('$tag[0], $degreedied_string, $zipped_string, $recursion_counter after degreedying: ');var_dump($tag[0], $degreedied_string, $zipped_string, $recursion_counter);
						$degreedied_something = true;
					}
				}
			}
			//$degreedying_debug_counter++;
			//if($degreedying_debug_counter > 20) {
			//	fractal_zip::fatal_error('$degreedying_debug_counter > 20');
			//}
			if($recursion_marked_something) {
				$string = $zipped_string;
				continue;
			}
			if($degreedied_something) {
				$string = $zipped_string;
				$did_something = true;
				continue;
			}
		}

		
		print('$fractal_string, $zipped_string, $recursion_counter: ');var_dump($fractal_string, $zipped_string, $recursion_counter);
		$string = $zipped_string;*/
	//	$recursion_counter++;
		//if($recursion_counter > 4) {
		//	fractal_zip::fatal_error('debug break $recursion_counter > 4');
		//}
	//}
	
	//print('$this->fractal_path_scores, $fractal_paths at the end of recursive_fractal_substring: ');var_dump($this->fractal_path_scores, $fractal_paths);
	//print('end of recursion loop<br>');exit(0);
	return $fractal_paths;
	//return $this->recursive_fractal_substring($string, $fractal_string, $fractal_paths = array(), $path = array());
}

function simple_substring_expressions_only($string) {
	preg_match_all('/' . fractal_zip_marker_rx_substring_main() . '/is', $string, $matches, PREG_OFFSET_CAPTURE); // would a parser be faster? optimize later
	//print('$matches in simple_substring_expressions_only: ');var_dump($matches);
	$counter = sizeof($matches[0]) - 1;
	while($counter > -1) {
		$substring_offset = $matches[1][$counter][0];
		$substring_length = $matches[2][$counter][0];
		//$substring_recursion_counter = $matches[3][0]; // what should be the order of the following markers?
		//$substring_tuple = $matches[4][0];
		//$substring_scale = $matches[5][0];
		$string = substr($string, 0, $matches[0][$counter][1]) . fractal_zip::$fractal_marker_ctx_left . $substring_offset . fractal_zip::$fractal_marker_ctx_mid . $substring_length . fractal_zip::$fractal_marker_ctx_right . substr($string, $matches[0][$counter][1] + strlen($matches[0][$counter][0]));
		$counter--;
	}
	return $string;
}

function recursion_markerless($string) {
	return fractal_zip::simple_substring_expressions_only($string);
}

function simplify($string, $operation) {
	return fractal_zip::tuples($string, $operation);
}

function multiples($string, $operation) {
	return fractal_zip::tuples($string, $operation);
}

function tuples($string, $operation) {
	preg_match_all('/' . $operation . '/is', $string, $matches, PREG_OFFSET_CAPTURE);
	//print('$matches in tuples: ');var_dump($matches);
	$counter = sizeof($matches[0]) - 1;
	$tuple = 1;
	$R = fractal_zip::$fractal_marker_ctx_right;
	//print('tuple1<br>');
	while($counter > -1) {
		//print('tuple2<br>');
		if($matches[0][$counter - 1][1] === $matches[0][$counter][1] - strlen($operation)) {
			//print('tuple3<br>');
			$tuple++;
		} elseif($tuple > 1) {
			//print('tuple4<br>');
			$tupled_operation = ($R !== '') ? str_replace($R, fractal_zip::$fractal_marker_ctx_range . $tuple . $R, $operation) : $operation;
			$string = substr($string, 0, $matches[0][$counter][1]) . $tupled_operation . substr($string, $matches[0][$counter][1] + ($tuple * strlen($operation)));
			$tuple = 1;
		}
		$counter--;
	}
	//print('$tuple after loop: ');var_dump($tuple);
//	if($tuple > 1) {
//		$counter++;
//		$tupled_operation = str_replace('>', '*' . $tuple . '>', $operation);
//		$string = substr($string, 0, $matches[0][$counter][1]) . $tupled_operation . substr($string, $matches[0][$counter][1] + ($tuple * strlen($operation)));
//	}
	return $string;
}

/**
 * True if angle brackets are plausibly balanced and ordered (first `<` before first `>`, last `>` after last `<`).
 * strpos(false) must not be compared as 0 — that wrongly rejected strings with `<` but no `>` (e.g. binary BMP data).
 */
public static function bracket_delimiters_well_ordered($piece) {
	$L = fractal_zip::$fractal_marker_ctx_left;
	$R = fractal_zip::$fractal_marker_ctx_right;
	if(strlen($L) !== 1 || strlen($R) !== 1) {
		return true;
	}
	$firstLt = strpos($piece, $L);
	$firstGt = strpos($piece, $R);
	if($firstLt !== false && $firstGt !== false && $firstGt < $firstLt) {
		return false;
	}
	$lastLt = fractal_zip::strpos_last($piece, $L);
	$lastGt = fractal_zip::strpos_last($piece, $R);
	if($lastLt !== false && $lastGt !== false && $lastGt < $lastLt) {
		return false;
	}
	if(substr_count($piece, $R) !== substr_count($piece, $L)) {
		return false;
	}
	return true;
}

function is_fractally_clean($piece) { // pretty hacky
	if(!fractal_zip::bracket_delimiters_well_ordered($piece)) { // questionably throw out partial operators
		return false;
	}
	$L = fractal_zip::$fractal_marker_ctx_left;
	$R = fractal_zip::$fractal_marker_ctx_right;
	if(strlen($L) !== 1 || strlen($R) !== 1) {
		return true;
	}
	if(substr_count($piece, $R) === 1 && substr_count($piece, $L) === 1 && strpos($piece, $L) === 0 && strpos(strrev($piece), $R) === 0) { // questionably throw out pieces that are only a single operator
		return false;
	}/* elseif(strpos($piece, '<<') !== false || strpos($piece, '>>') !== false) { // ugly but probably effective until something smarter is done
		return false;	
	} elseif(strlen(fractal_zip::tagless($piece)) < fractal_zip::maximum_substr_expression_length()) {
		fractal_zip::warning_once('again, questionable whether this has general applicability');
		return false;
	}*/
	// parse looking for consecutive same bracket
	$offset = 0;
	$current_bracket = false;
	$lch = $L[0];
	$rch = $R[0];
	$pieceLen = strlen($piece);
	//print('ifc0001<br>');
	while($offset < $pieceLen) {
		//print('ifc0002<br>');
		if($piece[$offset] === $lch) {
			//print('ifc0003<br>');exit(0);
			if($current_bracket === $lch) {
				//print('ifc0004<br>');exit(0);
				return false;
			} else {
				//print('ifc0005<br>');exit(0);
				$current_bracket = $lch;
			}
		} elseif($piece[$offset] === $rch) {
			//print('ifc0006<br>');exit(0);
			if($current_bracket === $rch) {
				//print('ifc0007<br>');exit(0);
				return false;
			} else {
				//print('ifc0008<br>');exit(0);
				$current_bracket = $rch;
			}
		}
		$offset++;
	}
	//print('ifc0009<br>');
	/*preg_match('/[0-9"]+/is', $piece, $matches);
	print('$matches, $piece: ');var_dump($matches, $piece);
	if($matches[0] === $piece) {
		return false;	
	}*/
	return true;
}

function is_fractally_clean_for_unzip($piece) { // pretty hacky
	// Do not require alternating < > on every byte: binary payloads (e.g. BMP) may contain << or >> as literals.
	if(!fractal_zip::bracket_delimiters_well_ordered($piece)) {
		return false;
	}
	return true;
}

function strpos_ignoring_operations($haystack, $needle, $offset = 0) {
	//print('$haystack, $needle, $offset in strpos_ignoring_operations: ');var_dump($haystack, $needle, $offset);
	$strpos = strpos($haystack, $needle, $offset);
	if($strpos !== false) {
		$this->length_including_operations = strlen($needle);
		return $strpos;
	}
	$lop = $this->left_fractal_zip_marker;
	$rop = $this->right_fractal_zip_marker;
	$lopLen = strlen($lop);
	$ropLen = strlen($rop);
	$needle = fractal_zip::tagless($needle);
	$needleLen = strlen($needle);
	$haystackLen = strlen($haystack);
	$needle_offset = 0;
	while($offset < $haystackLen) {
		$haystack_offset = $offset;
		$in_operation = false;
		//print('sp001<br>');
		while($haystack_offset < $haystackLen && ($in_operation || ($lopLen === 1 && $haystack[$haystack_offset] === $lop[0]) || ($needle_offset < $needleLen && $haystack[$haystack_offset] === $needle[$needle_offset]))) {
			//print('sp002<br>');
			if($in_operation) {
				//print('sp003<br>');
				if($ropLen === 1 && $haystack[$haystack_offset] === $rop[0]) {
					//print('sp004<br>');
					$in_operation = false;
				} 
			} elseif($lopLen === 1 && $haystack[$haystack_offset] === $lop[0]) {
				//print('sp005<br>');
				$in_operation = true;
			} else {
				//print('sp006<br>');
				$needle_offset++;
				if($needle_offset === $needleLen) {
					//print('sp007<br>');
					$this->length_including_operations = $haystack_offset - $offset + 1;
					return $offset;
				}
			}
			$haystack_offset++;
		}
		$needle_offset = 0;
		$offset++;
	}
	return false;
}

function shorthand_add($fractal_zipped_string, $range_string) {
	$shorthand_exists = false;
	foreach($this->saved_shorthand as $saved_shorthand_index => $saved_shorthand) {
		if($saved_shorthand === $range_string) {
			$marked_range_string = fractal_zip::mark_range_string($this->range_shorthand_marker . $saved_shorthand_index);
			$fractal_zipped_string .= $marked_range_string;
			$shorthand_exists = true;
			break;
		}
	}
	if(!$shorthand_exists) {
		$this->saved_shorthand[$this->shorthand_counter] = $range_string;
		$this->shorthand_counter++;
		$marked_range_string = fractal_zip::mark_range_string($range_string);
		$fractal_zipped_string .= $marked_range_string;
	}
	return $fractal_zipped_string;
}

function fractal_substring_operator($start_offset, $end_offset) {
	return fractal_zip::$fractal_marker_ctx_left . $start_offset . fractal_zip::$fractal_marker_ctx_mid . $end_offset . fractal_zip::$fractal_marker_ctx_right;
}

function mark_range_string($range_string) {
	if($this->multipass) {
		$range_string = $this->left_fractal_zip_marker . $this->branch_counter . $this->mid_fractal_zip_marker . $range_string . $this->mid_fractal_zip_marker . $this->branch_counter . $this->right_fractal_zip_marker;
	} else {
		$range_string = $this->left_fractal_zip_marker . $range_string . $this->right_fractal_zip_marker;
	}
	return $range_string;
}

function add_fractal_string_if($string) {
	foreach($this->fractal_strings as $branch_id => $fractal_string) {
		if($string === $fractal_string) {
			return $branch_id;
		}
	}
	$this->fractal_strings[$this->branch_counter] = $string;
	$this->branch_counter++;
	return $this->branch_counter - 1;
}

function clean_fractal_strings() {
	print('$this->fractal_strings, $this->equivalences at start of clean_fractal_strings: ');var_dump($this->fractal_strings, $this->equivalences);
	// use a more complex fractal zipped string expression to free up a fractal string and take less net length
	/*foreach($this->fractal_strings as $branch_id => $fractal_string) {
		// try to build this fractal string from other fractal strings
		$offset = 0;
		$fractal_string_length = strlen($fractal_string);
		$built_string = '';
		$built_fractal_expression = '';
		foreach($this->fractal_strings as $branch_id2 => $fractal_string2) {
			if($branch_id2 <= $branch_id) {
				continue;
			}
			$fractal_string2_length = strlen($fractal_string2);
			if(substr($fractal_string, $offset, $fractal_string2_length) === $fractal_string2) {
				$offset += $fractal_string2_length;
				$built_string .= $fractal_string2;
				$built_fractal_expression .= $branch_id2 . ',';
				if($offset === $fractal_string_length) {
					$built_fractal_expression = fractal_zip::clean_ending_comma($built_fractal_expression);
					print('built a fractal string from fractal strings: <br>');
					print('fractal expression ' . $branch_id . ' is equivalent to fractal expression ' . $built_fractal_expression . '<br>');
					// now, if this expression saves us space, then use it
					$space_saved = $fractal_string_length;
					$space_added = 0;
					foreach($this->equivalences as $equivalence) {
						$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
						foreach($existing_branch_ids as $existing_branch_id) {
							if($existing_branch_id == $branch_id) {
								$space_added += strlen($built_fractal_expression) - strlen($branch_id);
							}
						}
					}
					if($space_saved > $space_added) {
						unset($this->fractal_strings[$branch_id]);
						$branch_ids_to_add = fractal_zip::branch_ids_from_zipped_string($built_fractal_expression);
						//print('$branch_ids_to_add: ');var_dump($branch_ids_to_add);
						foreach($this->equivalences as $equivalence_index => $equivalence) {
							$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
							$new_branch_ids = array();
							foreach($existing_branch_ids as $existing_branch_id) {
								//print('$existing_branch_id, $branch_id: ');var_dump($existing_branch_id, $branch_id);
								if($existing_branch_id == $branch_id) {
									foreach($branch_ids_to_add as $branch_id_to_add) {
										$new_branch_ids[] = $branch_id_to_add;
									}
								} else {
									$new_branch_ids[] = $existing_branch_id;
								}
							}
							//print('$existing_branch_ids, $new_branch_ids: ');var_dump($existing_branch_ids, $new_branch_ids);
							$this->equivalences[$equivalence_index][2] = fractal_zip::zipped_string_from_branch_ids($new_branch_ids);
						}
						print('this fractionation saves space!<br>');
						print('$space_saved, $space_added: ');var_dump($space_saved, $space_added);
						print('$this->fractal_strings, $this->equivalences to test saving space: ');var_dump($this->fractal_strings, $this->equivalences);
					}
					break;
				}
			}
		}
	}*/
	// should we also do the reverse (check if a fractal expression portion is used frequently enough to merit its agglomeration)? yes:
	
	
	// seems to be no final filesize advantage to using this on short patterned or random strings even though it's more efficient with the fractal strings
	// what about large files?
	/*fractal_zip::remove_duplicated_fractal_strings();
	//print('$this->fractal_strings after remove_duplicated_fractal_strings: ');var_dump($this->fractal_strings);
	// break fractal expressions of same length into pieces
	foreach($this->fractal_strings as $branch_id => $fractal_string) {
		$offset = 0;
		$fractal_string_length = strlen($fractal_string);
		$built_string = '';
		$built_fractal_expression = '';
		foreach($this->fractal_strings as $branch_id2 => $fractal_string2) {
			if($branch_id2 <= $branch_id) {
				continue;
			}
			$fractal_string2_length = strlen($fractal_string2);
			if($fractal_string_length === $fractal_string2_length) {
				//print('$branch_id, $fractal_string, $branch_id2, $fractal_string2: ');var_dump($branch_id, $fractal_string, $branch_id2, $fractal_string2);
				$fractal_strings_to_potentially_add = array();
				$diff_array = Diff::compare($fractal_string, $fractal_string2, true);
				//print('$diff_array: ');var_dump($diff_array);
				//$diff_table = Diff::get_colored_comparison_table_string($diff_array);
				//print($diff_table);
				//unset($this->fractal_strings[$index]);
				$fractal_string_to_add = '';
				$fractal_zipped_string1 = '';
				$fractal_zipped_string2 = '';
				$diff_mode = false;
				$space_saved = 0;
				$space_added = 0;
				foreach($diff_array as $diff_index => $diff_value) {
					if($diff_mode === false) {
						$diff_mode = $diff_value[1];
					}
					$fractal_string_to_add .= $diff_value[0];
					if($diff_mode !== $diff_array[$diff_index + 1][1]) {
						if($diff_mode === 0) {
							$fractal_zipped_string1 .= $this->branch_counter . ',';
							$fractal_zipped_string2 .= $this->branch_counter . ',';
							$space_saved += strlen($fractal_string_to_add);
						} elseif($diff_mode === 1) {
							$fractal_zipped_string2 .= $this->branch_counter . ',';
						} elseif($diff_mode === 2) {
							$fractal_zipped_string1 .= $this->branch_counter . ',';
						} else {
							print('should never get here23658970981-');exit(0);
						}
						$fractal_strings_to_potentially_add[$this->branch_counter] = $fractal_string_to_add;
						$fractal_string_to_add = '';
						$this->branch_counter++;
						$diff_mode = $diff_array[$diff_index + 1][1];
					}
				}
				$fractal_zipped_string1 = fractal_zip::clean_ending_comma($fractal_zipped_string1);
				$fractal_zipped_string2 = fractal_zip::clean_ending_comma($fractal_zipped_string2);
				foreach($this->equivalences as $equivalence) {
					$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
					foreach($existing_branch_ids as $existing_branch_id) {
						if($existing_branch_id == $branch_id) {
							$space_added += strlen($fractal_zipped_string1) - strlen($branch_id);
						}
						if($existing_branch_id == $branch_id) {
							$space_added += strlen($fractal_zipped_string2) - strlen($branch_id2);
						}
					}
				}
				//print('$space_saved, $space_added: ');var_dump($space_saved, $space_added);
				if($space_saved > $space_added) {
					foreach($fractal_strings_to_potentially_add as $fractal_string_to_potentially_add_branch_id => $fractal_string_to_add) {
						$this->fractal_strings[$fractal_string_to_potentially_add_branch_id] = $fractal_string_to_add;
					}					
					//print('$branch_id, $branch_id2, $fractal_zipped_string1, $fractal_zipped_string2, $this->fractal_strings: ');var_dump($branch_id, $branch_id2, $fractal_zipped_string1, $fractal_zipped_string2, $this->fractal_strings);
					unset($this->fractal_strings[$branch_id]);
					unset($this->fractal_strings[$branch_id2]);
					// why are we crossing these over...?
					$branch_ids_to_add = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string2);
					$branch_ids_to_add2 = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string1);
					foreach($this->equivalences as $equivalence_index => $equivalence) {
						$existing_branch_ids = fractal_zip::branch_ids_from_zipped_string($equivalence[2]);
						$new_branch_ids = array();
						foreach($existing_branch_ids as $existing_branch_id) {
							if($existing_branch_id == $branch_id) {
								foreach($branch_ids_to_add as $branch_id_to_add) {
									$new_branch_ids[] = $branch_id_to_add;
								}
							} elseif($existing_branch_id == $branch_id2) {
								foreach($branch_ids_to_add2 as $branch_id_to_add2) {
									$new_branch_ids[] = $branch_id_to_add2;
								}
							} else {
								$new_branch_ids[] = $existing_branch_id;
							}
						}
						$this->equivalences[$equivalence_index][2] = fractal_zip::zipped_string_from_branch_ids($new_branch_ids);
					}
					print('this fractal string intercomparison of ' . $branch_id . ' and ' . $branch_id2 . ' saves space!<br>');
				}
			}
		}
	}*/
	fractal_zip::remove_duplicated_fractal_strings();
	// remove unused strings; is there a situation where we wouldn't want to remove unused strings? or can we safely say that the most useful strings will be generated when needed?
	$used_strings = array();
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$branch_ids = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string);
		foreach($branch_ids as $branch_id) {
			$used_strings[$branch_id] = true;
		}
	}
	foreach($this->fractal_strings as $index => $fractal_string) {
		if(isset($used_strings[$index])) {
			
		} else {
			unset($this->fractal_strings[$index]);
		}
	}
}

function remove_duplicated_fractal_strings() {
	$array_branch_id_reassignments = array();
	foreach($this->fractal_strings as $index => $fractal_string) {
		//$index2 = $index + 1;
		//while($index2 < sizeof($this->fractal_strings)) { // can't use indices since cleaning leaves gaps
		foreach($this->fractal_strings as $index2 => $fractal_string2) {
			if($index2 <= $index) {
				continue;
			}
			if($fractal_string === $fractal_string2) {
				unset($this->fractal_strings[$index2]);
				if(isset($array_branch_id_reassignments[$index2])) {
					
				} else {
					$array_branch_id_reassignments[$index2] = $index;
				}
			}
			//$index2++;
		}
	}
	//print('$array_branch_id_reassignments in clean_fractal_strings: ');var_dump($array_branch_id_reassignments);
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$branch_ids = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string);
		//print('$branch_ids in clean_fractal_strings: ');var_dump($branch_ids);
		foreach($array_branch_id_reassignments as $from_id => $to_id) {
			foreach($branch_ids as $branch_id_index => $branch_id) {
				if($branch_id == $from_id) {
					$branch_ids[$branch_id_index] = $to_id;
				}
			}
		}
		$this->equivalences[$equivalence_index][2] = fractal_zip::branch_ids_to_zipped_string($branch_ids);
		//print('$this->equivalences in clean_fractal_strings: ');var_dump($this->equivalences);
	}
}

function minimize_branch_ids() {
	$array_branch_id_reassignments = array();
	$counter = 0;
	$new_fractal_strings = array();
	foreach($this->fractal_strings as $index => $fractal_string) {
		$array_branch_id_reassignments[$index] = $counter;
		$new_fractal_strings[$counter] = $fractal_string;
		$counter++;
	}
	$this->fractal_strings = $new_fractal_strings;
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$branch_ids = fractal_zip::branch_ids_from_zipped_string($fractal_zipped_string);
		foreach($array_branch_id_reassignments as $from_id => $to_id) {
			foreach($branch_ids as $branch_id_index => $branch_id) {
				if($branch_id == $from_id) {
					$branch_ids[$branch_id_index] = $to_id;
				}
			}
		}
		$this->equivalences[$equivalence_index][2] = fractal_zip::branch_ids_to_zipped_string($branch_ids);
	}
}

function validate_fractal_zip($entry_filename) {
	//print('FORCING Valid fractal_zip.<br>');
	//return true;
	print('$entry_filename, $this->fractal_string, $this->equivalences: ');fractal_zip::var_dump_full($entry_filename, $this->fractal_string, $this->equivalences);
	foreach($this->equivalences as $equivalence) {
		$equivalence_filename = $equivalence[1];
		if($equivalence_filename === $entry_filename) {
			$equivalence_string = $equivalence[0];
			$equivalence_fractal_zipped_expression = $equivalence[2];
			print('$equivalence_filename, $equivalence_string, $equivalence_fractal_zipped_expression: ');fractal_zip::var_dump_full($equivalence_filename, $equivalence_string, $equivalence_fractal_zipped_expression);
			$decoded = $this->unzip($equivalence_fractal_zipped_expression);
			$ok = ($decoded === $equivalence_string);
			if(!$ok && isset($this->fractal_member_gzip_disk_restore[$equivalence_filename]) && $this->fractal_member_gzip_disk_restore[$equivalence_filename] === $equivalence_string) {
				$inner = @gzdecode($equivalence_string);
				$ok = ($inner !== false && $decoded === $inner);
			}
			if($ok) {
				print('Valid fractal_zip.<br>');
				return true;
			} else {
				break;
			}
		}
	}
	print('Invalid fractal_zip.<br>');
	//print('$equivalence_filename, $entry_filename, $equivalence_fractal_zipped_expression, $this->unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string: ');fractal_zip::var_dump_full($equivalence_filename, $entry_filename, $equivalence_fractal_zipped_expression, $this->unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string);
	print('$this->fractal_string: ');var_dump($this->fractal_string);
	print('$equivalence_fractal_zipped_expression, $this->unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string: ');fractal_zip::var_dump_full($equivalence_fractal_zipped_expression, $this->unzip($equivalence_fractal_zipped_expression), $equivalence_string, $this->fractal_string);
	exit(0);
	return false;
}

function silent_validate($string, $fractal_string, $equivalence_string) {
	fractal_zip::warning_once('silent_validate was created to weed out bad results instead of fixing the code that creates these bad results, example: aaaaa<20"25"6>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb ... definate hack but seems to make us go faster since we are eliminating problems. is slower when there are no problems due to buggy code.');
	$result = false;
	$maxOp = fractal_zip::silent_validate_max_operand_bytes();
	if($maxOp > 0) {
		if(strlen((string) $string) > $maxOp || strlen((string) $fractal_string) > $maxOp || strlen((string) $equivalence_string) > $maxOp) {
			return false;
		}
	}
	//$initial_fractal_string = $this->fractal_string;
	//$this->fractal_string = $fractal_string;
	//print('$string, $fractal_string, $this->unzip($string, $fractal_string), $equivalence_string in silent_validate: ');var_dump($string, $fractal_string, $this->unzip($string, $fractal_string), $equivalence_string);
	if($this->unzip($string, $fractal_string, false) === $equivalence_string) {
		$result = true;
	}
	//$this->fractal_string = $initial_fractal_string;
	return $result;
}

function clean_ending_comma($string) {
	if(substr($string, -1) === ',') {
		$string = substr($string, 0, -1);
	}
	return $string;
}

function branch_ids_to_zipped_string($branch_ids) {
	return implode(',', $branch_ids);
}

function zipped_string_from_branch_ids($branch_ids) {
	return fractal_zip::branch_ids_to_zipped_string($branch_ids);
}

function branch_ids_from_zipped_string($string) {
	if(strpos($string, ',') !== false) {
		$branches = explode(',', $string);
	} else {
		$branches = array($string);
	}
	$new_branches = array();
	foreach($branches as $branch) {
		if(strpos($branch, '-') === false) {
			$new_branches[] = $branch;
		} else {
			$branch_range_start = substr($branch, 0, strpos($branch, '-'));
			$branch_range_end = substr($branch, strpos($branch, '-') + 1);
			$counter = $branch_range_start;
			while($counter <= $branch_range_end) {
				$new_branches[] = $counter;
				$counter++;
			}
		}
	}
	return $new_branches;
}

function zipped_string_to_branch_ids($string) {
	return fractal_zip::branch_ids_from_zipped_string($string);
}

function clean_arrays_before_serialization() {
	fractal_zip::minimize_branch_ids();
	fractal_zip::clean_fractal_zipped_strings();
}

function clean_fractal_zipped_strings() {
	foreach($this->equivalences as $equivalence_index => $equivalence) {
		$fractal_zipped_string = $equivalence[2];
		$fractal_zipped_string = fractal_zip::clean_fractal_zipped_string($fractal_zipped_string);
		$this->equivalences[$equivalence_index][2] = $fractal_zipped_string;
	}
}

function clean_fractal_zipped_string($string) {
	$string = fractal_zip::clean_ending_comma($string);
	// what's better; having a range over 2 sequential branch IDs or a comma between them?
	$new_branches = array();
	$last_branch = -2;
	//$branch_range_start_id = false;
	$branch_range_end_id = false;
	$branches = fractal_zip::branch_ids_from_zipped_string($string);
	foreach($branches as $branch) {
		if($branch == $last_branch + 1) {
			//if($branch_range_start_id === false) {
			//	$branch_range_start_id = $last_branch;
			//}
			$branch_range_end_id = $branch;
		} else {
			if($branch_range_end_id !== false) {
				$new_branches[sizeof($new_branches) - 1] .= '-' . $branch_range_end_id;
				$branch_range_end_id = false;
			}
			$new_branches[] = $branch;
		}
		$last_branch = $branch;
	}
	if($branch_range_end_id !== false) {
		$new_branches[sizeof($new_branches) - 1] .= '-' . $branch_range_end_id;
		$branch_range_end_id = false;
	}
	$string = fractal_zip::branch_ids_to_zipped_string($new_branches);
	return $string;
}

/**
 * Path to 7z / 7zz / 7za, or null. Cached per request.
 * Windows: default install path; Unix: common paths and PATH (p7zip).
 * Override: set <code>FRACTAL_ZIP_7Z</code> to a full path when 7z is not on PHP's PATH (shared hosting).
 */
static function seven_zip_executable() {
	static $cache = null;
	if($cache !== null) {
		return $cache === false ? null : $cache;
	}
	$env7z = getenv('FRACTAL_ZIP_7Z');
	if(is_string($env7z) && trim($env7z) !== '') {
		$p = trim($env7z);
		if(is_executable($p)) {
			$cache = $p;
			return $p;
		}
	}
	$found = null;
	if(DIRECTORY_SEPARATOR === '\\') {
		$win = 'C:\\Program Files\\7-Zip\\7z.exe';
		if(is_file($win)) {
			$found = $win;
		}
	} else {
		foreach(array('/usr/bin/7zz', '/usr/bin/7z', '/usr/bin/7za') as $abs) {
			if(is_executable($abs)) {
				$found = $abs;
				break;
			}
		}
		if($found === null) {
			foreach(array('7zz', '7z', '7za') as $cmd) {
				$line = @shell_exec('command -v ' . $cmd . ' 2>/dev/null');
				if(is_string($line)) {
					$p = trim($line);
					if($p !== '' && is_executable($p)) {
						$found = $p;
						break;
					}
				}
			}
		}
	}
	$cache = $found === null ? false : $found;
	return $found;
}

/**
 * Path to FreeArc `arc`, or null if unavailable.
 */
static function freearc_executable() {
	static $cache = null;
	if($cache !== null) {
		return $cache === false ? null : $cache;
	}
	$found = null;
	if(DIRECTORY_SEPARATOR !== '\\') {
		$line = @shell_exec('command -v arc 2>/dev/null');
		if(is_string($line)) {
			$p = trim($line);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	}
	$cache = $found === null ? false : $found;
	return $found;
}

/**
 * Path to `zstd`, or null if unavailable.
 */
static function zstd_executable() {
	static $cache = null;
	if($cache !== null) {
		return $cache === false ? null : $cache;
	}
	$found = null;
	if(DIRECTORY_SEPARATOR === '\\') {
		$line = @shell_exec('where zstd 2>nul');
		if(is_string($line)) {
			$p = trim(explode("\n", $line)[0]);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	} else {
		$line = @shell_exec('command -v zstd 2>/dev/null');
		if(is_string($line)) {
			$p = trim($line);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	}
	$cache = $found === null ? false : $found;
	return $found;
}

/**
 * Path to `brotli`, or null if unavailable.
 */
static function brotli_executable() {
	static $cache = null;
	if($cache !== null) {
		return $cache === false ? null : $cache;
	}
	$found = null;
	if(DIRECTORY_SEPARATOR === '\\') {
		$line = @shell_exec('where brotli 2>nul');
		if(is_string($line)) {
			$p = trim(explode("\n", $line)[0]);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	} else {
		$line = @shell_exec('command -v brotli 2>/dev/null');
		if(is_string($line)) {
			$p = trim($line);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	}
	$cache = $found === null ? false : $found;
	return $found;
}

/**
 * Path to `xz`, or null if unavailable.
 */
static function xz_executable() {
	static $cache = null;
	if($cache !== null) {
		return $cache === false ? null : $cache;
	}
	$found = null;
	if(DIRECTORY_SEPARATOR === '\\') {
		$line = @shell_exec('where xz 2>nul');
		if(is_string($line)) {
			$p = trim(explode("\n", $line)[0]);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	} else {
		$line = @shell_exec('command -v xz 2>/dev/null');
		if(is_string($line)) {
			$p = trim($line);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	}
	$cache = $found === null ? false : $found;
	return $found;
}

/**
 * Path to `zpaq` (http://mattmahoney.net/dc/zpaq.html), or null. Override: <code>FRACTAL_ZIP_ZPAQ</code> full path.
 * Skip trials: <code>FRACTAL_ZIP_SKIP_ZPAQ=1</code>. Inner-size gates: <code>FRACTAL_ZIP_MIN_ZPAQ_INNER_BYTES</code> (default 0),
 * <code>FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES</code> (default 6291456; <code>0</code> = no cap).
 */
static function zpaq_executable() {
	static $cache = null;
	if($cache !== null) {
		return $cache === false ? null : $cache;
	}
	$envZ = getenv('FRACTAL_ZIP_ZPAQ');
	if(is_string($envZ) && trim($envZ) !== '') {
		$t = trim($envZ);
		$res = null;
		if(self::fractal_zip_zpaq_env_resolve_trimmed($t, $res) && $res !== null) {
			$cache = $res;

			return $res;
		}
	}
	$found = null;
	if(DIRECTORY_SEPARATOR === '\\') {
		$line = @shell_exec('where zpaq 2>nul');
		if(is_string($line)) {
			$p = trim(explode("\n", $line)[0]);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	} else {
		$line = @shell_exec('command -v zpaq 2>/dev/null');
		if(is_string($line)) {
			$p = trim($line);
			if($p !== '' && is_executable($p)) {
				$found = $p;
			}
		}
	}
	$cache = $found === null ? false : $found;
	return $found;
}

/** Minimum inner bytes before trying zpaq outer (default 0: bytes-first on small payloads; set higher to skip tiny inners). */
public static function min_zpaq_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MIN_ZPAQ_INNER_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 0;
	}
	return $cached = max(0, (int) trim((string) $e));
}

/**
 * Maximum inner size (bytes) for which zpaq outer is tried; avoids extreme wall time on huge unified inners (default 6 MiB).
 * <code>FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES=0</code> disables the cap (try zpaq at any inner size when other gates allow).
 */
public static function max_zpaq_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES');
	if($e === false || trim((string) $e) === '') {
		return $cached = 6291456;
	}
	return $cached = max(0, (int) trim((string) $e));
}

/**
 * When true (default), <strong>zstd, brotli</strong>, FreeArc, zpaq, and gzip-gated 7z re-opens may still run after the random-like
 * outer prescreen marks <code>outer_likely_incompressible</code> — once gzip shows meaningful shrink vs the inner, prescreen is
 * treated as a fast-codec hint only (structured binary false positives). Set <code>FRACTAL_ZIP_ZPAQ_OUTER_IGNORE_PRESCREEN=0</code>
 * to restore legacy behavior (skip zstd/brotli/arc/zpaq and the prescreen-bypass 7z re-opens when prescreen is positive).
 * Pair with {@see fractal_zip::zpaq_outer_prescreen_override_min_gzip_saving_bytes} / frac so near-incompressible gzip baselines
 * do not pay extra trials (e.g. JPEG literals).
 */
public static function zpaq_outer_ignores_prescreen(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZPAQ_OUTER_IGNORE_PRESCREEN');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/** Minimum gzip shrink (bytes) vs raw inner to allow zpaq when prescreen-positive and {@see fractal_zip::zpaq_outer_ignores_prescreen} is on. */
public static function zpaq_outer_prescreen_override_min_gzip_saving_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZPAQ_OUTER_PRESCREEN_OVERRIDE_MIN_GZIP_SAVING_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? 512 : max(0, (int) trim((string) $e));
}

/** Fraction of inner length: additional floor paired with min bytes for the prescreen zpaq gate. */
public static function zpaq_outer_prescreen_override_min_gzip_saving_frac(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZPAQ_OUTER_PRESCREEN_OVERRIDE_MIN_GZIP_SAVING_FRAC');
	return $cached = ($e === false || trim((string) $e) === '') ? 0.0005 : max(0.0, min(0.5, (float) trim((string) $e)));
}

/**
 * Whether zstd/brotli and slow outers (FreeArc, zpaq) and gzip-gated <strong>7z sweep re-opens</strong> after early-stop may run when
 * <code>$likelyIncompressible</code> is true: when this returns false, fast codecs stay skipped; when true, gzip already showed the
 * payload is not raw-random noise so the full fast tier may run too.
 */
public static function outer_slow_codec_allowed_despite_prescreen(bool $likelyIncompressible, int $innerLen, int $gzLen): bool {
	// Bytes-first (phase 4 outer tournament): never let the random-like prescreen hide zstd/brotli/slow outers — gzip baseline still ran.
	if(!fractal_zip::speed_mode_enabled()) {
		return true;
	}
	if(!$likelyIncompressible) {
		return true;
	}
	if(!fractal_zip::zpaq_outer_ignores_prescreen()) {
		return false;
	}
	if($gzLen === PHP_INT_MAX || $innerLen <= 0 || $gzLen >= $innerLen) {
		return false;
	}
	$sav = $innerLen - $gzLen;
	$minB = fractal_zip::zpaq_outer_prescreen_override_min_gzip_saving_bytes();
	$frac = fractal_zip::zpaq_outer_prescreen_override_min_gzip_saving_frac();
	return $sav >= max($minB, (int) floor((float) $innerLen * $frac));
}

/**
 * Trimmed <code>FRACTAL_ZIP_ZPAQ_METHOD</code>, or <code>null</code> when unset/empty — shared by {@see zpaq_add_method_argv_fragment}
 * and {@see maybe_folder_zpaq_native_smaller_than_fzc} (avoids duplicate getenv on hot zpaq paths).
 */
public static function zpaq_method_env_trimmed_cached(): ?string {
	static $done = false;
	static $value = null;
	if(!$done) {
		$done = true;
		$e = getenv('FRACTAL_ZIP_ZPAQ_METHOD');
		$value = ($e === false || trim((string) $e) === '') ? null : trim((string) $e);
	}
	return $value;
}

/**
 * Extra argv for <code>zpaq add</code> (compression). Unset ⇒ <code>-method 5</code>. If value starts with <code>-</code>, it is appended as-is (leading space added); else <code>-method &lt;value&gt;</code>.
 */
public static function zpaq_add_method_argv_fragment(): string {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$v = fractal_zip::zpaq_method_env_trimmed_cached();
	if($v === null) {
		return $cached = ' -method 5';
	}
	if($v !== '' && $v[0] === '-') {
		return $cached = ' ' . $v;
	}
	return $cached = ' -method ' . $v;
}

/**
 * Record which zpaq <code>-method</code> digits the last <code>zpaq add</code> trial used (for bench outer caption).
 * @param string $frag Fragment like <code> -method 5</code> (leading space) or a trailing full argv piece.
 */
public static function zpaq_set_last_method_from_argv_fragment($frag) {
	$frag = ltrim((string) $frag);
	if($frag !== '' && preg_match('/-method\\s+([0-9]+)/i', ' ' . $frag, $m) === 1) {
		self::$last_zpaq_method = (string) $m[1];
	} elseif($frag !== '' && preg_match('/^([0-9]+)$/', trim($frag), $m) === 1) {
		self::$last_zpaq_method = (string) $m[1];
	} else {
		self::$last_zpaq_method = null;
	}
}

/**
 * When <code>FRACTAL_ZIP_ZPAQ_OUTER_SWEEP</code> is unset, still try multiple <code>-method</code> trials (same default list as
 * explicit sweep) for inners up to this many bytes — Squash-class corpora often pick a non‑5 method. Default 2 MiB reaches
 * mid-size and larger unified inners; above this cap only a single <code>-method</code> trial runs (unless
 * <code>FRACTAL_ZIP_ZPAQ_OUTER_SWEEP=1</code>). <code>0</code> disables auto sweep.
 */
public static function zpaq_outer_auto_sweep_max_inner_bytes(): int {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZPAQ_OUTER_AUTO_SWEEP_MAX_INNER_BYTES');
	return $cached = ($e === false || trim((string) $e) === '') ? self::ZPAQ_OUTER_METHOD_SIX_MAX_INNER_BYTES : max(0, (int) trim((string) $e));
}

/**
 * When {@code 1} (CLI default via {@see fractal_zip_encode_pipeline::bootstrap_cli_parallel_defaults_if_cli}),
 * zpaq outer working dirs ({@code fzzpaq_*}) use {@see sys_get_temp_dir} instead of {@code program_path}.
 * {@code 0}/{@code off}/{@code false} keeps scratch beside the install tree.
 */
public static function zpaq_outer_work_sys_tmp_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_ZPAQ_OUTER_WORK_SYS_TMP');
	if($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));

	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/** @return string Parent directory for {@code fzzpaq_*} outer scratch (encoding path only). */
public static function zpaq_outer_work_dir_root(string $programPath): string {
	if(!self::zpaq_outer_work_sys_tmp_enabled()) {
		return $programPath;
	}

	return sys_get_temp_dir();
}

function command_prefix_with_local_lib() {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$merged = fractal_zip::ld_library_path_merged_for_home_local();
	if($merged === null) {
		return $cached = '';
	}
	return $cached = 'LD_LIBRARY_PATH=' . escapeshellarg($merged) . ' ';
}

/**
 * @return list<string> 7-Zip -m0 methods to try (multiple LZMA2 profiles + PPMd on text-friendly payloads).
 * @param bool $literalBundleHuge true when inner is a large FZB literal bundle (CSV/BMP scale); adds higher-order PPMd trials.
 *   For huge FZB inners, PPMd is listed before LZMA2 so outer_7z_best_blob's early-exit (first 7z result under the zstd/gzip
 *   budget) cannot skip PPMd — on multi-MiB bundles PPMd often beats LZMA2 by a wide margin.
 * Midsize inners (below 256 KiB): try PPMd orders 6–9 before 10 (lower order often packs smaller on short text-like literals;
 * early-exit after the first PPMD under the fast-codec budget would otherwise freeze on o=10 and miss a few bytes).
 * Between 256 KiB and 512 KiB: try o=7–9 before o=10 (same ladder as shorter midsize inners; batched so order does not change early-exit vs single PPMd).
 * Above 384 KiB: add <code>lzma2:fb=192</code> before <code>fb=273</code> (the 49–384 KiB branch already tried both; larger inners used only fb=273 and could miss a smaller LZMA2 profile).
 */
function outer_7z_m0_candidates($innerLen, $literalBundleHuge = false) {
	// Speed-first mode: reduce outer 7z search fanout.
	if(fractal_zip::speed_mode_enabled()) {
		return array('lzma2');
	}
	$lzma = array();
	if($innerLen > 393216) {
		$lzma[] = 'lzma2:fb=192';
		$lzma[] = 'lzma2:fb=273';
		if($innerLen > 786432) {
			$lzma[] = 'lzma2:d=64m:fb=273';
		}
		if($literalBundleHuge && $innerLen > 4000000) {
			$lzma[] = 'lzma2:d=128m:fb=273';
		}
	} elseif($innerLen > 49152) {
		$lzma[] = 'lzma2:fb=192';
		$lzma[] = 'lzma2:fb=273';
	} elseif($innerLen > 12288) {
		$lzma[] = 'lzma2:fb=128';
		$lzma[] = 'lzma2:fb=192';
	} else {
		$lzma[] = 'lzma2';
		if($innerLen > 6144) {
			$lzma[] = 'lzma2:fb=128';
		}
	}
	$ppmd = array();
	if($innerLen >= 4096 && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_PPMD')) {
		if($innerLen < 262144) {
			if($innerLen < 65536) {
				$ppmd[] = 'PPMd:o=6';
			}
			$ppmd[] = 'PPMd:o=7';
			$ppmd[] = 'PPMd:o=8';
			$ppmd[] = 'PPMd:o=9';
		} elseif($innerLen < 524288) {
			$ppmd[] = 'PPMd:o=7';
			$ppmd[] = 'PPMd:o=8';
			$ppmd[] = 'PPMd:o=9';
		}
		$ppmd[] = 'PPMd:o=10';
		if($literalBundleHuge && $innerLen >= 131072) {
			$ppmd[] = 'PPMd:o=12';
		}
	}
	if($literalBundleHuge) {
		static $litHuge7zPpmd = null;
		if($litHuge7zPpmd === null) {
			$ppmdOnlyEnv = getenv('FRACTAL_ZIP_LITERAL_HUGE_7Z_PPMD_ONLY');
			$ppmdOnly = ($ppmdOnlyEnv === false || trim((string) $ppmdOnlyEnv) === '') ? true : ((int) $ppmdOnlyEnv !== 0);
			$ppmdOnlyMinEnv = getenv('FRACTAL_ZIP_LITERAL_HUGE_7Z_PPMD_ONLY_MIN_INNER_BYTES');
			$ppmdOnlyMin = ($ppmdOnlyMinEnv === false || trim((string) $ppmdOnlyMinEnv) === '') ? 4000000 : max(0, (int) $ppmdOnlyMinEnv);
			$litHuge7zPpmd = array('only' => $ppmdOnly, 'min' => $ppmdOnlyMin);
		}
		$ppmdOnly = $litHuge7zPpmd['only'];
		$ppmdOnlyMin = $litHuge7zPpmd['min'];
		if($ppmdOnly && $ppmdOnlyMin > 0 && $innerLen >= $ppmdOnlyMin && sizeof($ppmd) > 0) {
			return array_values(array_unique($ppmd));
		}
		return array_values(array_unique(array_merge($ppmd, $lzma)));
	}
	return array_values(array_unique(array_merge($lzma, $ppmd)));
}

/**
 * 7z a … -m0=…; last arg = input file path or -si. Optional stdin bytes when -si. Uses argv+LD, no sh (PHP 7.4+). Returns true on exit 0.
 * @param string|null $stdIn when $inputPathOrSi is '-si', this string is sent (chunked).
 */
function outer_7z_pack_add_direct($seven, $m0, $arc, $inputPathOrSi, $stdIn) {
	if(PHP_VERSION_ID < 70400) {
		return false;
	}
	$chdir = $this->program_path;
	$isSi = ($inputPathOrSi === '-si');
	$ldMerged = fractal_zip::ld_library_path_merged_for_home_local();
	$oldLd = getenv('LD_LIBRARY_PATH');
	if($ldMerged !== null) {
		putenv('LD_LIBRARY_PATH=' . $ldMerged);
	}
	$argv = array_merge(
		array($seven, 'a', '-t7z', '-mx=9'),
		fractal_zip::seven_zip_mmt_argv_from_env(),
		fractal_zip::outer_7z_metadata_strip_argv(),
		array('-m0=' . (string) $m0, '-bso0', '-bsp0', '-bd', '-y', $arc, $inputPathOrSi)
	);
	static $desc7zSi, $desc7zNoSiUnix, $desc7zNoSiWin;
	if($desc7zSi === null) {
		$desc7zSi = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$desc7zNoSiUnix = array(0 => array('file', '/dev/null', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$desc7zNoSiWin = array(0 => array('file', 'NUL', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
	}
	$desc = $isSi ? $desc7zSi : (DIRECTORY_SEPARATOR === '\\' ? $desc7zNoSiWin : $desc7zNoSiUnix);
	$proc = @proc_open($argv, $desc, $pipes, $chdir, null, fractal_zip::proc_open_bypass_shell_array());
	if($ldMerged !== null) {
		if($oldLd === false) {
			putenv('LD_LIBRARY_PATH');
		} else {
			putenv('LD_LIBRARY_PATH=' . $oldLd);
		}
	}
	if(!is_resource($proc)) {
		return false;
	}
	if($isSi) {
		$ss = $stdIn === null ? '' : (string) $stdIn;
		$n0 = strlen($ss);
		if($n0 > 0) {
			$chunk = 262144;
			for($o = 0; $o < $n0; $o += $chunk) {
				$w = @fwrite($pipes[0], $n0 - $o > $chunk ? substr($ss, $o, $chunk) : substr($ss, $o));
				if($w === false || $w === 0) {
					break;
				}
			}
		}
	}
	if(isset($pipes[0]) && is_resource($pipes[0])) {
		fclose($pipes[0]);
	}
	if(function_exists('stream_set_chunk_size') && isset($pipes[1]) && is_resource($pipes[1])) {
		@stream_set_chunk_size($pipes[1], 1048576);
	}
	if(isset($pipes[1]) && is_resource($pipes[1])) {
		stream_get_contents($pipes[1]);
		fclose($pipes[1]);
	}
	if(isset($pipes[2]) && is_resource($pipes[2])) {
		stream_get_contents($pipes[2]);
		fclose($pipes[2]);
	}
	$ex = proc_close($proc);
	return $ex === 0 && is_file($arc);
}

/**
 * Try one 7z pack; large inners use a temp file first (no huge single fwrite to 7z stdin). Stdin path uses chunked writes.
 * Falls back to temp file + exec if proc_open stdin fails.
 * When $preparedInnerPath is set to an existing file (same bytes as $string), that path is used for file-first and final
 * exec fallbacks instead of writing a fresh temp inner — outer_7z_best_blob uses this to avoid N disk writes per sweep.
 */
function outer_7z_single_attempt($seven, $string, $arc, $m0, $siName, $preparedInnerPath = null) {
	if(is_file($arc)) {
		unlink($arc);
	}
	$n = strlen($string);
	$fziScratch = $this->program_path . DS . 'fzi_' . substr(md5((string) $arc), 0, 16) . $this->fractal_zip_file_extension;
	$fileFirstMin = fractal_zip::outer_7z_file_first_min_bytes();
	$prefix = $this->command_prefix_with_local_lib();
	$meta7z = fractal_zip::outer_7z_metadata_strip_argv();
	$meta7zShell = ($meta7z === array()) ? '' : (' ' . implode(' ', $meta7z));
	$mmt7zShell = fractal_zip::seven_zip_mmt_shell_fragment_for_exec();
	$ok = false;
	$reuseInner = ($preparedInnerPath !== null && is_string($preparedInnerPath) && is_file($preparedInnerPath));
	$qSeven = fractal_zip::shell_quote_arg_cached($seven);
	$qArc = escapeshellarg($arc);
	$qSi = fractal_zip::shell_quote_arg_cached($siName);
	$qPrep = $reuseInner ? fractal_zip::shell_quote_arg_cached($preparedInnerPath) : null;
	if($fileFirstMin > 0 && $n >= $fileFirstMin) {
		if($reuseInner) {
			$ok = $this->outer_7z_pack_add_direct($seven, $m0, $arc, $preparedInnerPath, null);
			if(!$ok) {
				$cmdFile = $qSeven . ' a -t7z -mx=9' . $mmt7zShell . $meta7zShell . ' -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . $qArc . ' ' . $qPrep;
				@exec($prefix . $cmdFile . ' 2>/dev/null', $output, $return);
				$ok = ($return === 0 && is_file($arc));
			}
		} else {
			if(@file_put_contents($fziScratch, $string) !== false) {
				$ok = $this->outer_7z_pack_add_direct($seven, $m0, $arc, $fziScratch, null);
				if(!$ok) {
					$cmdFile = $qSeven . ' a -t7z -mx=9' . $mmt7zShell . $meta7zShell . ' -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . $qArc . ' ' . escapeshellarg($fziScratch);
					@exec($prefix . $cmdFile . ' 2>/dev/null', $output, $return);
					$ok = ($return === 0 && is_file($arc));
				}
				if(is_file($fziScratch)) {
					unlink($fziScratch);
				}
			}
		}
		if(!$ok && is_file($arc)) {
			unlink($arc);
		}
	}
	$cmdStdin = $qSeven . ' a -t7z -mx=9' . $mmt7zShell . $meta7zShell . ' -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . $qArc . ' ' . $qSi;
	static $desc7zStdinPipe;
	if($desc7zStdinPipe === null) {
		$desc7zStdinPipe = array(
		0 => array('pipe', 'r'),
		1 => array('pipe', 'w'),
		2 => array('pipe', 'w'),
		);
	}
	$desc = $desc7zStdinPipe;
	if(!$ok) {
		$ok = $this->outer_7z_pack_add_direct($seven, $m0, $arc, '-si', (string) $string);
	}
	if(!$ok && function_exists('proc_open')) {
		$proc = @proc_open($cmdStdin, $desc, $pipes, $this->program_path);
		if(is_resource($proc)) {
			$chunk = 262144;
			for($o = 0; $o < $n; $o += $chunk) {
				$w = @fwrite($pipes[0], $n - $o > $chunk ? substr($string, $o, $chunk) : substr($string, $o));
				if($w === false || $w === 0) {
					break;
				}
			}
			fclose($pipes[0]);
			if(function_exists('stream_set_chunk_size') && is_resource($pipes[1])) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			if(is_resource($pipes[1])) {
				stream_get_contents($pipes[1]);
				fclose($pipes[1]);
			}
			if(is_resource($pipes[2])) {
				stream_get_contents($pipes[2]);
				fclose($pipes[2]);
			}
			$ok = (proc_close($proc) === 0 && is_file($arc));
		}
	}
	if(!$ok) {
		if(is_file($arc)) {
			unlink($arc);
		}
		if($reuseInner && is_file($preparedInnerPath)) {
			$ok = $this->outer_7z_pack_add_direct($seven, $m0, $arc, $preparedInnerPath, null);
			if(!$ok) {
				$cmdFile = $qSeven . ' a -t7z -mx=9' . $mmt7zShell . $meta7zShell . ' -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . $qArc . ' ' . $qPrep;
				@exec($prefix . $cmdFile . ' 2>/dev/null', $output, $return);
				$ok = ($return === 0 && is_file($arc));
			}
		} else {
			file_put_contents($fziScratch, $string);
			$ok = $this->outer_7z_pack_add_direct($seven, $m0, $arc, $fziScratch, null);
			if(!$ok) {
				$cmdFile = $qSeven . ' a -t7z -mx=9' . $mmt7zShell . $meta7zShell . ' -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . $qArc . ' ' . escapeshellarg($fziScratch);
				@exec($prefix . $cmdFile . ' 2>/dev/null', $output, $return);
				$ok = ($return === 0 && is_file($arc));
			}
			if(is_file($fziScratch)) {
				unlink($fziScratch);
			}
		}
	}
	if(!$ok || !is_file($arc)) {
		return null;
	}
	$blob = file_get_contents($arc);
	if(is_file($arc)) {
		unlink($arc);
	}
	if($blob === false || strlen($blob) === 0) {
		return null;
	}
	return $blob;
}

/**
 * One fast 7z attempt (lzma2 only). Used for huge payloads as a safe fallback.
 */
function outer_7z_lzma2_blob($seven, $string) {
	$u = substr(md5((string) $string . "\0o7l2"), 0, 8);
	$arc = $this->program_path . DS . 'fztmp_' . $u . '_lzma2' . $this->fractal_zip_container_file_extension;
	return $this->outer_7z_single_attempt($seven, $string, $arc, 'lzma2', '-si');
}

/**
 * Try each -m0 method; return smallest .7z payload (or null if all fail).
 * Consecutive PPMd settings are tried as one batch (smallest wins) so early-exit cannot lock a suboptimal order.
 * @param bool $literalBundleHuge pass true for large FZB* inners (richer PPMd grid).
 */
function outer_7z_best_blob($seven, $string, $innerLen, $maxLenToBeat = null, $literalBundleHuge = false) {
	$best = null;
	$bestLen = PHP_INT_MAX;
	$maxLen = $maxLenToBeat === null ? null : max(0, (int)$maxLenToBeat);
	$u = substr(md5((string) $string . "\0o7bb\0" . (string) spl_object_id($this)), 0, 8);
	$idx = 0;
	// Use unnamed stdin member for minimal 7z header overhead.
	$siName = '-si';
	$n = strlen($string);
	$fileFirstMin = fractal_zip::outer_7z_file_first_min_bytes();
	$preparedInnerPath = null;
	if($fileFirstMin > 0 && $n >= $fileFirstMin) {
		$preparedInnerPath = $this->program_path . DS . 'fzi_' . $u . $this->fractal_zip_file_extension;
		if(@file_put_contents($preparedInnerPath, $string) === false) {
			$preparedInnerPath = null;
		}
	}
	$candidates = $this->outer_7z_m0_candidates($innerLen, $literalBundleHuge);
	$ppmdRunBuf = array();
	$flushPpmdBatch = function() use (&$ppmdRunBuf, &$best, &$bestLen, &$idx, $u, $seven, $string, $siName, $preparedInnerPath) {
		if(sizeof($ppmdRunBuf) === 0) {
			return;
		}
		foreach($ppmdRunBuf as $pm) {
			$arc = $this->program_path . DS . 'fztmp_' . $u . '_' . $idx . $this->fractal_zip_container_file_extension;
			$idx++;
			$blob = $this->outer_7z_single_attempt($seven, $string, $arc, $pm, $siName, $preparedInnerPath);
			if($blob !== null) {
				$bl = strlen($blob);
				if($bl < $bestLen) {
					$best = $blob;
					$bestLen = $bl;
				}
			}
		}
		$ppmdRunBuf = array();
	};
	$candsCount = sizeof($candidates);
	for($ci = 0; $ci < $candsCount; $ci++) {
		$m0 = $candidates[$ci];
		if(strncasecmp((string) $m0, 'PPMd', 4) === 0) {
			$ppmdRunBuf[] = $m0;
			continue;
		}
		$flushPpmdBatch();
		if($maxLen !== null && $bestLen <= $maxLen) {
			break;
		}
		$arc = $this->program_path . DS . 'fztmp_' . $u . '_' . $idx . $this->fractal_zip_container_file_extension;
		$idx++;
		$blob = $this->outer_7z_single_attempt($seven, $string, $arc, $m0, $siName, $preparedInnerPath);
		if($blob !== null) {
			$bl = strlen($blob);
			if($bl < $bestLen) {
				$best = $blob;
				$bestLen = $bl;
				// Early exit once we beat the current best from other codecs.
				if($maxLen !== null && $bestLen <= $maxLen) {
					break;
				}
			}
		}
	}
	$flushPpmdBatch();
	if($preparedInnerPath !== null && is_file($preparedInnerPath)) {
		unlink($preparedInnerPath);
	}
	return $best;
}

/**
 * One FreeArc `arc a` with cwd already the temp work dir and $innerBase populated. Deletes $arcBase when present (success or fail).
 * When $qArcExe is set (from outer_arc_blob's method loop), it must be {@see fractal_zip::shell_quote_arg_cached}($arcExe) (same as escapeshellarg) — avoids repeating work per trial.
 * @return string|null
 */
function outer_arc_blob_single_in_cwd($arcExe, $methodNum, $extraArgv, $prefix, $arcBase = 'o.arc', $innerBase = 'i', $qArcExe = null) {
	$methodNum = max(1, min(9, (int) $methodNum));
	$extra = is_string($extraArgv) ? $extraArgv : '';
	static $qArcDefault = null;
	static $qInnerDefault = null;
	if($qArcDefault === null) {
		$qArcDefault = escapeshellarg('o.arc');
		$qInnerDefault = escapeshellarg('i');
	}
	if($arcBase === 'o.arc' && $innerBase === 'i') {
		$qArcB = $qArcDefault;
		$qInnerB = $qInnerDefault;
	} else {
		$qArcB = escapeshellarg($arcBase);
		$qInnerB = escapeshellarg($innerBase);
	}
	$qExe = ($qArcExe !== null && $qArcExe !== '') ? $qArcExe : fractal_zip::shell_quote_arg_cached($arcExe);
	$mtSh = fractal_zip::library_arc_compress_mt_shell_fragment_for_exec();
	$cmd = $prefix . $qExe . $mtSh . ' a -m' . $methodNum . ' -ep1' . $extra . ' -y ' . $qArcB . ' ' . $qInnerB;
	$return = 1;
	$usedProc = false;
	if(PHP_VERSION_ID >= 70400 && DIRECTORY_SEPARATOR !== '\\') {
		$tail = trim($extra) !== '' ? array_values(array_filter(preg_split('/\s+/', trim($extra)), function ($s) {
			return (string) $s !== '';
		})) : array();
		$mtA = fractal_zip::library_arc_compress_mt_argv_after_exe();
		$argv = array_merge(
			array($arcExe),
			$mtA,
			array('a', '-m' . (string) $methodNum, '-ep1'),
			$tail,
			array('-y', $arcBase, $innerBase)
		);
		$ldMerged = fractal_zip::ld_library_path_merged_for_home_local();
		$oldLd = getenv('LD_LIBRARY_PATH');
		if($ldMerged !== null) {
			putenv('LD_LIBRARY_PATH=' . $ldMerged);
		}
		static $descArcProcPipe;
		if($descArcProcPipe === null) {
			$descArcProcPipe = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		}
		$desc = $descArcProcPipe;
		$proc = @proc_open($argv, $desc, $pipes, null, null, fractal_zip::proc_open_bypass_shell_array());
		if($ldMerged !== null) {
			if($oldLd === false) {
				putenv('LD_LIBRARY_PATH');
			} else {
				putenv('LD_LIBRARY_PATH=' . $oldLd);
			}
		}
		if(is_resource($proc)) {
			fclose($pipes[0]);
			if(function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
			$return = proc_close($proc);
			$usedProc = true;
		}
	}
	if(!$usedProc) {
		exec($cmd . ' 2>/dev/null', $output, $return);
	}
	$blob = null;
	if($return === 0 && is_file($arcBase)) {
		$blob = file_get_contents($arcBase);
	}
	if(is_file($arcBase)) {
		unlink($arcBase);
	}
	if($blob === false || $blob === null || strlen((string) $blob) === 0) {
		return null;
	}
	return $blob;
}

/**
 * Single FreeArc outer attempt (-m1..9). Used by outer_arc_blob (may run multiple methods).
 * $extraArgv is appended after -ep1 (e.g. " -mx"). The inner member is stored as a short name ("i") under a per-call temp dir to minimize archive-directory overhead vs long .fractalzip paths.
 * @return string|null
 */
function outer_arc_blob_single($arcExe, $string, $methodNum, $extraArgv = '') {
	$methodNum = max(1, min(9, (int) $methodNum));
	$extra = is_string($extraArgv) ? $extraArgv : '';
	$u = substr(md5((string) $string . "\0arc1\0" . (string) $methodNum), 0, 16);
	$workDir = $this->program_path . DS . 'fzao_' . $u;
	if(!@mkdir($workDir, 0755, true)) {
		return null;
	}
	$arcBase = 'o.arc';
	$innerBase = 'i';
	$innerPath = $workDir . DS . $innerBase;
	$arcPath = $workDir . DS . $arcBase;
	if(@file_put_contents($innerPath, $string) === false) {
		@rmdir($workDir);
		return null;
	}
	$prefix = $this->command_prefix_with_local_lib();
	$cwd = getcwd();
	if(!@chdir($workDir)) {
		if(is_file($innerPath)) {
			unlink($innerPath);
		}
		@rmdir($workDir);
		return null;
	}
	$qArcExe = fractal_zip::shell_quote_arg_cached($arcExe);
	$blob = $this->outer_arc_blob_single_in_cwd($arcExe, $methodNum, $extra, $prefix, $arcBase, $innerBase, $qArcExe);
	if($cwd !== false) {
		chdir($cwd);
	}
	if(is_file($innerPath)) {
		unlink($innerPath);
	}
	if(is_file($arcPath)) {
		unlink($arcPath);
	}
	@rmdir($workDir);
	if($blob === null) {
		return null;
	}
	return $blob;
}

/**
 * FreeArc outer container; picks smallest among candidate -m levels (bytes-first tournament).
 * FRACTAL_ZIP_ARC_METHOD=1..9 forces a single method (bench compatibility).
 * FRACTAL_ZIP_ARC_OUTER_METHODS=5,7,9 optional comma list overrides the default candidate set.
 * FRACTAL_ZIP_ARC_OUTER_DUAL_METHODS=0: large inners use -m5 only (legacy fast path).
 * FRACTAL_ZIP_ARC_OUTER_DUAL_MIN_BYTES: inner length floor for the default -m4..9 sweep (unset ⇒ 64 KiB; set 458752 to restore the old ~448 KiB gate).
 * FRACTAL_ZIP_ARC_OUTER_MX=0: skip extra -mx attempts on -m5 and -m9 (default: after the default method sweep, not when FRACTAL_ZIP_ARC_OUTER_METHODS is set).
 * Default large-inner sweep: -m4..9 (folder min-ext uses -m5 on separate files; one FZB blob may need another -m or -mx to tie).
 */
function outer_arc_blob($arcExe, $string) {
	$n = strlen($string);
	$ev = fractal_zip::outer_arc_tournament_env_cached();
	if($ev['forced'] !== null) {
		return $this->outer_arc_blob_single($arcExe, $string, $ev['forced'], '');
	}
	$explicitOuterList = $ev['explicit_outer_list'];
	if($explicitOuterList) {
		$methods = $ev['explicit_methods'];
	} else {
		$methods = array(5);
		if(!$ev['no_dual'] && $ev['min_dual'] > 0 && $n >= $ev['min_dual']) {
			$methods = array(4, 5, 6, 7, 8, 9);
		}
	}
	$u = substr(md5((string) $string . "\0arcsv"), 0, 16);
	$workDir = $this->program_path . DS . 'fzao_' . $u;
	if(!@mkdir($workDir, 0755, true)) {
		return null;
	}
	$arcBase = 'o.arc';
	$innerBase = 'i';
	$innerPath = $workDir . DS . $innerBase;
	$arcPath = $workDir . DS . $arcBase;
	if(@file_put_contents($innerPath, $string) === false) {
		@rmdir($workDir);
		return null;
	}
	$prefix = $this->command_prefix_with_local_lib();
	$cwd = getcwd();
	if(!@chdir($workDir)) {
		if(is_file($innerPath)) {
			unlink($innerPath);
		}
		@rmdir($workDir);
		return null;
	}
	$qArcExe = fractal_zip::shell_quote_arg_cached($arcExe);
	$best = null;
	$bestLen = PHP_INT_MAX;
	foreach($methods as $m) {
		$b = $this->outer_arc_blob_single_in_cwd($arcExe, $m, '', $prefix, $arcBase, $innerBase, $qArcExe);
		if($b !== null) {
			$bLen = strlen($b);
			if($bLen < $bestLen) {
				$best = $b;
				$bestLen = $bLen;
			}
		}
	}
	if($ev['try_mx'] && !$explicitOuterList) {
		foreach(array(5, 9) as $m) {
			$b = $this->outer_arc_blob_single_in_cwd($arcExe, $m, ' -mx', $prefix, $arcBase, $innerBase, $qArcExe);
			if($b !== null) {
				$bLen = strlen($b);
				if($bLen < $bestLen) {
					$best = $b;
					$bestLen = $bLen;
				}
			}
		}
	}
	if($cwd !== false) {
		chdir($cwd);
	}
	if(is_file($innerPath)) {
		unlink($innerPath);
	}
	if(is_file($arcPath)) {
		unlink($arcPath);
	}
	@rmdir($workDir);
	return $best;
}

/**
 * ZSTD-compress stdin → stdout (level 1–22; default from FRACTAL_ZIP_ZSTD_LEVEL or 19).
 * Uses temp-file input when the payload is large enough to risk proc_open stdin/stdout pipe deadlock (see outer_codec_temp_input_threshold_bytes).
 */
function outer_zstd_blob($zstdExe, $string, $level, $reuseTmpInPath = null) {
	$level = max(1, min(22, (int)$level));
	$base = fractal_zip::shell_quote_arg_cached($zstdExe) . ' -' . $level . fractal_zip::library_zstd_thread_shell_fragment_for_exec() . ' -c --quiet';
	$cmdStdin = $base;
	$cmdTmp = $base . ' -- __FZOC_TMPIN__';
	return fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $string, $this->program_path, $reuseTmpInPath);
}

/**
 * XZ-compress stdin → stdout (level 0–9). Returns raw .xz stream (has magic).
 */
function outer_xz_blob($xzExe, $string, $level, $timeoutSec = null, $reuseTmpInPath = null) {
	$level = max(0, min(9, (int)$level));
	$inner = fractal_zip::shell_quote_arg_cached($xzExe) . ' -' . $level . fractal_zip::library_xz_compress_thread_shell_fragment_for_exec() . ' -c';
	if($timeoutSec !== null && DIRECTORY_SEPARATOR !== '\\') {
		$t = (float)$timeoutSec;
		if($t > 0) {
			$to = fractal_zip::outer_timeout_executable_cached();
			if($to !== '') {
				$inner = fractal_zip::shell_quote_arg_cached($to) . ' -k 1 ' . fractal_zip::shell_quote_arg_cached((string) $t) . ' ' . $inner;
			}
		}
	}
	$cmdStdin = $inner;
	$cmdTmp = $inner . ' __FZOC_TMPIN__';
	return fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $string, $this->program_path, $reuseTmpInPath);
}

/**
 * One zpaq add trial with explicit <code>-method</code> argv fragment (leading space, e.g. <code> -method 5</code>).
 * @return string|null FZzq + .zpaq bytes, or null on failure
 */
function outer_zpaq_blob_with_meth_fragment($zpaqExe, $string, string $methFragment) {
	$workDir = fractal_zip::zpaq_outer_work_dir_root($this->program_path) . DS . 'fzzpaq_' . substr(md5((string) $string . "\0zm\0" . $methFragment), 0, 16);
	if(!@mkdir($workDir, 0755, true)) {
		return null;
	}
	$innerPath = $workDir . DS . 'i';
	$arcPath = $workDir . DS . 'inner.zpaq';
	if(@file_put_contents($innerPath, $string) === false) {
		@rmdir($workDir);
		return null;
	}
	$cwd = getcwd();
	$ok = false;
	if(@chdir($workDir)) {
		$prefix = $this->command_prefix_with_local_lib();
		$meth = $methFragment;
		$cmd = $prefix . fractal_zip::shell_quote_arg_cached($zpaqExe) . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add inner.zpaq i' . $meth;
		$tzPre = fractal_zip::zpaq_outer_exec_timeout_prefix_cached();
		if($tzPre !== '') {
			$cmd = $tzPre . $cmd;
		}
		exec($cmd . ' 2>/dev/null', $out, $ret);
		$ok = ($ret === 0 && is_file($arcPath) && filesize($arcPath) > 0);
	}
	if($cwd !== false) {
		@chdir($cwd);
	}
	$blob = null;
	if($ok) {
		$r = @file_get_contents($arcPath);
		if(is_string($r) && $r !== '') {
			$blob = fractal_zip::OUTER_ZPAQ_MAGIC . $r;
		}
	}
	if(is_file($innerPath)) {
		@unlink($innerPath);
	}
	if(is_file($arcPath)) {
		@unlink($arcPath);
	}
	@rmdir($workDir);
	return $blob;
}

/**
 * zpaq outer: temp dir + <code>zpaq add inner.zpaq i</code>; returns {@see fractal_zip::OUTER_ZPAQ_MAGIC} + archive bytes.
 * <code>FRACTAL_ZIP_ZPAQ_OUTER_SWEEP=1</code>: try several <code>-method</code> settings for any inner size. When unset, modest
 * inners (≤ {@see fractal_zip::zpaq_outer_auto_sweep_max_inner_bytes} bytes, default 2 MiB) still get a method sweep;
 * set <code>FRACTAL_ZIP_ZPAQ_OUTER_SWEEP=0</code> to force a single trial. Default method list adds <code>-method 6</code> when the
 * inner is ≤min(2 MiB, <code>FRACTAL_ZIP_ZPAQ_OUTER_AUTO_SWEEP_MAX_INNER_BYTES</code>) when auto-sweep is env-driven (before 5/4/3);
 * explicit <code>FRACTAL_ZIP_ZPAQ_OUTER_SWEEP=1</code> uses 2 MiB for the m6 cap. Optional full list: <code>FRACTAL_ZIP_ZPAQ_OUTER_METHODS</code>.
 */
function outer_zpaq_blob($zpaqExe, $string) {
	$n = strlen($string);
	$autoSweepMax = fractal_zip::zpaq_outer_auto_sweep_max_inner_bytes();
	$sw = fractal_zip::zpaq_outer_sweep_env_flags_cached();
	$sweepFromEnv = $sw['env_nonempty'];
	$sweep = $sweepFromEnv ? $sw['explicit_on'] : ($autoSweepMax > 0 && $n > 0 && $n <= $autoSweepMax);
	if(!$sweep) {
		$meth = fractal_zip::zpaq_add_method_argv_fragment();
		$ret = $this->outer_zpaq_blob_with_meth_fragment($zpaqExe, $string, $meth);
		if($ret !== null && strlen($ret) > 0) {
			fractal_zip::zpaq_set_last_method_from_argv_fragment($meth);
		}
		return $ret;
	}
	$candidates = fractal_zip::zpaq_outer_methods_argv_fragments_cached();
	if($candidates === []) {
		// -method 6 is skipped above min(2 MiB, auto-sweep cap) so lowering FRACTAL_ZIP_ZPAQ_OUTER_AUTO_SWEEP_MAX_INNER_BYTES
		// also caps expensive m6 trials; explicit FRACTAL_ZIP_ZPAQ_OUTER_SWEEP=1 keeps m6 through 2 MiB.
		$sixM = fractal_zip::ZPAQ_OUTER_METHOD_SIX_MAX_INNER_BYTES;
		$sixCap = $sweepFromEnv ? $sixM : (($autoSweepMax > 0) ? min($sixM, $autoSweepMax) : $sixM);
		$highMax = fractal_zip::zpaq_outer_high_method_max_inner_bytes();
		$highTail = ($highMax > 0 && $n > 0 && $n <= $highMax) ? [' -method 9', ' -method 8', ' -method 7'] : [];
		$candidates = ($n > 0 && $n <= $sixCap)
			? array_merge($highTail, [' -method 6', ' -method 5', ' -method 4', ' -method 3'])
			: array_merge($highTail, [' -method 5', ' -method 4', ' -method 3']);
	}
	$best = null;
	$bestLen = PHP_INT_MAX;
	$winFrag = null;
	$candsPlain = array_values($candidates);
	$forkMinZpaq = fractal_zip_encode_pipeline::parallel_speculative_outer_min_inner_bytes();
	if(count($candsPlain) >= 2 && ($forkMinZpaq <= 0 || $n >= $forkMinZpaq)) {
		$par = fractal_zip_encode_pipeline::parallel_zpaq_outer_method_variant_trials($string, $candsPlain, true);
		if($par !== null) {
			foreach($candsPlain as $i => $frag) {
				$b = $par[$i] ?? null;
				if($b === null || $b === '') {
					continue;
				}
				$bLen = strlen($b);
				if($bLen > 0 && $bLen < $bestLen) {
					$bestLen = $bLen;
					$best = $b;
					$winFrag = $frag;
				}
			}
			if($winFrag !== null) {
				fractal_zip::zpaq_set_last_method_from_argv_fragment($winFrag);
			}

			return $best;
		}
	}
	// One temp dir + one write of `i` for the whole method ladder; remove inner.zpaq before each trial (same state as a fresh dir per trial).
	$workDir = fractal_zip::zpaq_outer_work_dir_root($this->program_path) . DS . 'fzzpaq_' . substr(md5((string) $string . "\0zpsw\0" . (string) spl_object_id($this)), 0, 16);
	if(!@mkdir($workDir, 0755, true)) {
		return null;
	}
	$innerPath = $workDir . DS . 'i';
	$arcPath = $workDir . DS . 'inner.zpaq';
	if(@file_put_contents($innerPath, $string) === false) {
		@rmdir($workDir);
		return null;
	}
	$cwd = getcwd();
	if(@chdir($workDir)) {
		$prefix = $this->command_prefix_with_local_lib();
		$tzPre = fractal_zip::zpaq_outer_exec_timeout_prefix_cached();
		$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
		foreach($candidates as $frag) {
			if(is_file($arcPath)) {
				@unlink($arcPath);
			}
			$cmd = $prefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add inner.zpaq i' . $frag;
			if($tzPre !== '') {
				$cmd = $tzPre . $cmd;
			}
			exec($cmd . ' 2>/dev/null', $out, $ret);
			if($ret === 0 && is_file($arcPath) && filesize($arcPath) > 0) {
				$r = @file_get_contents($arcPath);
				if(is_string($r) && $r !== '') {
					$blob = fractal_zip::OUTER_ZPAQ_MAGIC . $r;
					$bLen = strlen($blob);
					if($bLen > 0 && $bLen < $bestLen) {
						$best = $blob;
						$bestLen = $bLen;
						$winFrag = $frag;
					}
				}
			}
		}
	}
	if($cwd !== false) {
		@chdir($cwd);
	}
	if(is_file($innerPath)) {
		@unlink($innerPath);
	}
	if(is_file($arcPath)) {
		@unlink($arcPath);
	}
	@rmdir($workDir);
	if($winFrag !== null) {
		fractal_zip::zpaq_set_last_method_from_argv_fragment($winFrag);
	}
	return $best;
}

/**
 * <code>FRACTAL_ZIP_FORCE_OUTER=zpaq</code>: compress with zpaq only (honours <code>FRACTAL_ZIP_ZPAQ_OUTER_SWEEP</code> via <code>outer_zpaq_blob</code>).
 * @return string|null null ⇒ caller should run the full outer tournament (only when zpaq is not forced)
 */
function try_force_outer_zpaq_payload($string) {
	if(!fractal_zip::outer_force_codec_is_zpaq()) {
		return null;
	}
	if(!fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZPAQ')) {
		fractal_zip::message_once('FRACTAL_ZIP_FORCE_OUTER=zpaq + FRACTAL_ZIP_SKIP_ZPAQ=1; keeping inner unchanged (no fallback outer tournament).');
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	$zpaqExe = fractal_zip::zpaq_executable();
	if($zpaqExe === null) {
		fractal_zip::message_once('FRACTAL_ZIP_FORCE_OUTER=zpaq but zpaq not on PATH (set FRACTAL_ZIP_ZPAQ); keeping inner unchanged (no fallback outer tournament).');
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	$innerLen = strlen($string);
	if($innerLen === 0) {
		return null;
	}
	$zpaqBlob = $this->outer_zpaq_blob($zpaqExe, $string);
	if($zpaqBlob === null) {
		fractal_zip::message_once('FRACTAL_ZIP_FORCE_OUTER=zpaq: zpaq add failed; keeping inner unchanged (no fallback outer tournament).');
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	$zpaqBlobLen = strlen($zpaqBlob);
	if($zpaqBlobLen === 0) {
		fractal_zip::message_once('FRACTAL_ZIP_FORCE_OUTER=zpaq: zpaq add failed; keeping inner unchanged (no fallback outer tournament).');
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	if(!fractal_zip::allow_outer_expansion() && $zpaqBlobLen >= $innerLen) {
		fractal_zip::message_once('FRACTAL_ZIP_FORCE_OUTER=zpaq: zpaq outer did not shrink inner; keeping zpaq because force mode is enabled.');
	}
	fractal_zip::$last_outer_codec = 'zpaq';
	return $zpaqBlob;
}

/**
 * Brotli-compress stdin → stdout; returns magic prefix + raw brotli (quality 0–11, default FRACTAL_ZIP_BROTLI_QUALITY or 11).
 * Optional $lgwinOverride: 0–24 passed as brotli -w (overrides FRACTAL_ZIP_BROTLI_LGWIN when non-null).
 */
function outer_brotli_blob($brotliExe, $string, $quality, $timeoutSec = null, $lgwinOverride = null, $reuseTmpInPath = null) {
	$quality = max(0, min(11, (int)$quality));
	$cmd = fractal_zip::shell_quote_arg_cached($brotliExe) . fractal_zip::brotli_compress_extra_argv_shell_fragment() . ' -c -q ' . $quality;
	$lwCacheKey = '';
	if($lgwinOverride !== null) {
		$lw = max(0, min(24, (int)$lgwinOverride));
		$cmd .= ' -w ' . $lw;
		$lwCacheKey = 'w' . $lw;
	} else {
		static $lgwinCmdSuffixFromEnvDone = false;
		static $lgwinCmdSuffixFromEnv = '';
		if(!$lgwinCmdSuffixFromEnvDone) {
			$lgwinCmdSuffixFromEnvDone = true;
			$lwEnv = getenv('FRACTAL_ZIP_BROTLI_LGWIN');
			if($lwEnv !== false && trim((string)$lwEnv) !== '' && is_numeric(trim((string)$lwEnv))) {
				$lw = max(0, min(24, (int)trim((string)$lwEnv)));
				$lgwinCmdSuffixFromEnv = ' -w ' . $lw;
			}
		}
		$cmd .= $lgwinCmdSuffixFromEnv;
		$lwCacheKey = ($lgwinCmdSuffixFromEnv === '') ? 'env0' : ('env' . trim((string) $lgwinCmdSuffixFromEnv));
	}
	$cacheKey = null;
	static $brotliOutCache = array();
	static $brotliOutFifoRing = null;
	static $brotliOutFifoHead = 0;
	static $brotliOutFifoTail = 0;
	static $brotliOutFifoCount = 0;
	$cacheLimit = 8192;
	$cacheMaxInputBytes = 131072;
	$cacheDirectKeyMaxBytes = 4096; // Avoid digest work on very small hot-path probes.
	$strLen = strlen($string);
	if($timeoutSec === null && $strLen <= $cacheMaxInputBytes) {
		$xk = fractal_zip::brotli_compress_extra_argv_cache_tag();
		if($strLen <= $cacheDirectKeyMaxBytes) {
			$cacheKey = $quality . '|' . $lwCacheKey . $xk . '|s:' . $string;
		} else {
			$cacheKey = $quality . '|' . $lwCacheKey . $xk . '|m:' . fractal_zip::hot_string_digest((string) $string);
		}
		if(isset($brotliOutCache[$cacheKey])) {
			return $brotliOutCache[$cacheKey];
		}
	}
	// Optional timeout for pathological/huge brotli cases (Unix: uses coreutils `timeout` if present).
	if($timeoutSec !== null && DIRECTORY_SEPARATOR !== '\\') {
		$t = (float)$timeoutSec;
		if($t > 0) {
			$to = fractal_zip::outer_timeout_executable_cached();
			if($to !== '') {
				$cmd = fractal_zip::shell_quote_arg_cached($to) . ' -k 1 ' . fractal_zip::shell_quote_arg_cached((string) $t) . ' ' . $cmd;
			}
		}
	}
	$cmdStdin = $cmd;
	$cmdTmp = $cmd . ' __FZOC_TMPIN__';
	$out = fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $string, $this->program_path, $reuseTmpInPath);
	if($out === null || $out === '') {
		return null;
	}
	$ret = fractal_zip::OUTER_BROTLI_MAGIC . $out;
	if($cacheKey !== null) {
		$brotliOutCache[$cacheKey] = $ret;
		if($brotliOutFifoRing === null) {
			$brotliOutFifoRing = array_fill(0, $cacheLimit, null);
		}
		if($brotliOutFifoCount === $cacheLimit) {
			$evict = $brotliOutFifoRing[$brotliOutFifoHead];
			$brotliOutFifoRing[$brotliOutFifoHead] = null;
			$brotliOutFifoHead = ($brotliOutFifoHead + 1) % $cacheLimit;
			$brotliOutFifoCount--;
			if($evict !== null) {
				unset($brotliOutCache[$evict]);
			}
		}
		$brotliOutFifoRing[$brotliOutFifoTail] = $cacheKey;
		$brotliOutFifoTail = ($brotliOutFifoTail + 1) % $cacheLimit;
		$brotliOutFifoCount++;
	}
	return $ret;
}

/**
 * Decompress zstd stdin → stdout.
 */
function outer_zstd_decompress_pipe($zstdExe, $blob) {
	$base = fractal_zip::shell_quote_arg_cached($zstdExe) . fractal_zip::library_zstd_thread_shell_fragment_for_exec() . ' -d -c --quiet';
	$cmdStdin = $base;
	$cmdTmp = $base . ' -- __FZOC_TMPIN__';
	return fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $blob, $this->program_path);
}

/**
 * Decompress brotli stdin → stdout.
 */
function outer_brotli_decompress_pipe($brotliExe, $blob) {
	$base = fractal_zip::shell_quote_arg_cached($brotliExe) . ' -d -c';
	$cmdStdin = $base;
	$cmdTmp = $base . ' __FZOC_TMPIN__';
	return fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $blob, $this->program_path);
}

/**
 * Fast pre-screen to avoid expensive outer trials on likely incompressible payloads.
 * Uses a small sampled entropy proxy (unique-byte spread + adjacent-byte repeats).
 * `adaptive_compress` clears this for FZB* literal-bundle inners (see bundle_inner_eligible_for_fzws): they often look random but still gain from zstd vs gzip.
 */
function outer_likely_incompressible($string) {
	if(fractal_zip::outer_prescreen_gate_disabled()) {
		return false;
	}
	$n = strlen($string);
	$minProbe = fractal_zip::outer_prescreen_min_bytes();
	if($n < $minProbe) {
		return false;
	}
	$sampleTarget = fractal_zip::outer_prescreen_sample_target_bytes();
	$step = max(1, (int)floor($n / $sampleTarget));
	$sampleMin = fractal_zip::outer_prescreen_sample_min_bytes();
	$sn = 0;
	$repeatAdj = 0;
	static $uniqStamp = null;
	if($uniqStamp === null) {
		$uniqStamp = array_fill(0, 256, 0);
	}
	static $uniqGen = 0;
	$uniqGen++;
	// On huge call counts, reset stamps so generation ids stay unique.
	if($uniqGen > 1073741824) {
		$uniqStamp = array_fill(0, 256, 0);
		$uniqGen = 1;
	}
	$g0 = $uniqGen;
	$unique = 0;
	$prev = null;
	for($i = 0; $i < $n; $i += $step) {
		$b = ord($string[$i]);
		if($prev !== null && $b === $prev) {
			$repeatAdj++;
		}
		$prev = $b;
		if($uniqStamp[$b] !== $g0) {
			$uniqStamp[$b] = $g0;
			$unique++;
		}
		$sn++;
	}
	if($sn < $sampleMin) {
		return false;
	}
	$repeatRate = $repeatAdj / max(1, $sn - 1);
	$uniqueMin = fractal_zip::outer_prescreen_unique_min();
	$repeatMax = fractal_zip::outer_prescreen_repeat_max();
	// Very broad random-ish signature: high symbol spread with little local repetition.
	return $unique >= $uniqueMin && $repeatRate <= $repeatMax;
}

/**
 * Cheap text-likeness probe for deciding if brotli is worth trying as an outer wrapper.
 * Samples bytes across payload and checks ratio of printable ASCII/whitespace.
 */
function outer_likely_textlike($string) {
	static $s0 = null;
	static $r0 = null;
	static $s1 = null;
	static $r1 = null;
	if($s0 === $string) {
		return (bool) $r0;
	}
	if($s1 === $string) {
		$ts = $s0;
		$s0 = $s1;
		$s1 = $ts;
		$tr = $r0;
		$r0 = $r1;
		$r1 = $tr;
		return (bool) $r0;
	}
	$out = false;
	if(fractal_zip::outer_textlike_probe_disabled()) {
		$out = true;
		goto outer_likely_textlike_mru;
	}
	$n = strlen($string);
	if($n === 0) {
		$out = false;
		goto outer_likely_textlike_mru;
	}
	$minProbe = fractal_zip::outer_textlike_min_bytes();
	if($n < $minProbe) {
		// Too small to be worth gating; allow brotli.
		$out = true;
		goto outer_likely_textlike_mru;
	}
	$sampleTarget = fractal_zip::outer_textlike_sample_target_bytes();
	$minRatio = fractal_zip::outer_textlike_min_ratio();
	$maxZero = fractal_zip::outer_textlike_max_zero_ratio();
	$step = max(1, (int)floor($n / $sampleTarget));
	$sn = 0;
	$printable = 0;
	$zeros = 0;
	for($i = 0; $i < $n; $i += $step) {
		$b = ord($string[$i]);
		if($b === 0) {
			$zeros++;
		}
		// tabs/newlines/CR or ASCII printable
		if($b === 9 || $b === 10 || $b === 13 || ($b >= 32 && $b <= 126)) {
			$printable++;
		}
		$sn++;
	}
	if($sn < 512) {
		$out = true;
		goto outer_likely_textlike_mru;
	}
	$ratio = $printable / max(1, $sn);
	$zeroRatio = $zeros / max(1, $sn);
	$out = (bool) (($ratio >= $minRatio) && ($zeroRatio <= $maxZero));
	outer_likely_textlike_mru:
	$s1 = $s0;
	$r1 = $r0;
	$s0 = $string;
	$r0 = $out;
	return (bool) $out;
}

/**
 * gzip-9 + zstd + brotli only (no 7z/arc/xz). Tier-1 outer for staged literal-folder selection (fast codecs only, no LZMA sweeps).
 * Bytes-first ( {@code FRACTAL_ZIP_SPEED} unset): default brotli **Q10**, with auto **Q11** for textlike / FZB-literal inners (same idea as pre–full-outer baselines);
 * {@code FRACTAL_ZIP_SPEED=1}: default **Q3** (no Q11 bump) to cut CPU while iterating. Optional {@code FRACTAL_ZIP_BROTLI_QUALITY} overrides when set.
 * Does not apply outer_likely_incompressible so MPQ-style literal inners still get zstd/brotli. Huge inners: optional timeout via FRACTAL_ZIP_STAGED_OUTER_BROTLI_TIMEOUT_SEC (default 120 s
 * when inner exceeds FRACTAL_ZIP_MAX_BROTLI_OUTER_INNER_BYTES on Unix; null env = that default; 0 = no timeout).
 * Optional {@code FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP} (0–11): cap brotli quality for this tier after resolving defaults/overrides (unset = no extra cap).
 * Full {@see adaptive_compress} rescoring still uses normal outer rules; the cap trades CPU vs staged fast literal-vs-raw ranking fidelity.
 * If no tried outer shrinks the inner, returns the inner unchanged (<code>store</code>) unless FRACTAL_ZIP_ALLOW_OUTER_EXPANSION=1.
 */
function adaptive_compress_outer_fast_codec_tier($string) {
	$rollupFastTier = fractal_zip_encode_pipeline::pipeline_outer_step_rollup_enabled()
		&& fractal_zip_encode_pipeline::pipeline_outer_step_rollup_session_active();
	if($rollupFastTier) {
		fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
	}
	try {
	fractal_zip::$last_outer_codec = null;
	fractal_zip::$last_zpaq_method = null;
	$string = (string) $string;
	$innerLen = strlen($string);
	$df = gzdeflate($string, 9);
	$bestGzipBlob = ($df !== false) ? $df : null;
	if(fractal_zip::outer_force_codec_is_gzip()) {
		$out = $bestGzipBlob !== null ? $bestGzipBlob : $string;
		if(!fractal_zip::allow_outer_expansion() && ($bestGzipBlob === null || strlen($bestGzipBlob) >= $innerLen)) {
			fractal_zip::$last_outer_codec = 'store';
			return $string;
		}
		fractal_zip::$last_outer_codec = 'gzip';
		return $out;
	}
	$forcedZpaqFast = $this->try_force_outer_zpaq_payload($string);
	if($forcedZpaqFast !== null) {
		return $forcedZpaqFast;
	}
	if($innerLen === 0) {
		$out = $bestGzipBlob !== null ? $bestGzipBlob : $string;
		if(!fractal_zip::allow_outer_expansion()) {
			fractal_zip::$last_outer_codec = 'store';
			return $string;
		}
		fractal_zip::$last_outer_codec = 'gzip';
		return $out;
	}
	$fzCodecTmpIn = null;
	$fzCodecTmpEnsureDone = false;
	$zstdExe = fractal_zip::zstd_executable();
	$minZstdInner = fractal_zip::min_zstd_inner_bytes();
	$brotliExe = fractal_zip::brotli_executable();
	$minBrotliInner = fractal_zip::min_brotli_inner_bytes();
	$outerTextlike = $this->outer_likely_textlike($string);
	$zstdBlob = null;
	$canTryZstd = ($zstdExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZSTD') && $innerLen >= $minZstdInner);
	if($canTryZstd) {
		$this->outer_codec_reuse_tmp_ensure($string, $innerLen, $fzCodecTmpIn, $fzCodecTmpEnsureDone);
		$zl = fractal_zip::zstd_level_env_raw();
		$zstdLevel = ($zl === false) ? 16 : max(1, min(22, (int) $zl));
		if($zl === false && !fractal_zip::disable_zstd_textlike_level()) {
			$tlMin = fractal_zip::zstd_textlike_min_inner_bytes();
			if($tlMin > 0 && $innerLen >= $tlMin && $outerTextlike) {
				$zstdLevel = fractal_zip::zstd_textlike_level();
			}
		}
		$zstdBlob = $this->outer_zstd_blob($zstdExe, $string, $zstdLevel, $fzCodecTmpIn);
	}
	$brotliBlob = null;
	$canTryBrotli = ($brotliExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_BROTLI') && $innerLen >= $minBrotliInner);
	if($canTryBrotli) {
		$this->outer_codec_reuse_tmp_ensure($string, $innerLen, $fzCodecTmpIn, $fzCodecTmpEnsureDone);
		$bq = fractal_zip::brotli_quality_env_raw();
		$speedStagedBr = fractal_zip::speed_mode_enabled();
		$brotliQ = ($bq === false) ? ($speedStagedBr ? 3 : 10) : max(0, min(11, (int) $bq));
		$maxBr = fractal_zip::max_brotli_outer_inner_bytes();
		$isHuge = ($maxBr > 0 && $innerLen > $maxBr);
		$brToEnv = getenv('FRACTAL_ZIP_STAGED_OUTER_BROTLI_TIMEOUT_SEC');
		$brTo = null;
		if($brToEnv !== false && trim((string) $brToEnv) !== '' && is_numeric(trim((string) $brToEnv))) {
			$brToParsed = (float) trim((string) $brToEnv);
			$brTo = ($brToParsed > 0) ? $brToParsed : null;
		} elseif($isHuge && DIRECTORY_SEPARATOR !== '\\') {
			$brTo = 120.0;
		}
		if($bq === false && !$speedStagedBr && !fractal_zip::disable_auto_textlike_brotli_q11() && !fractal_zip::disable_auto_textlike_fzb_brotli_q11()) {
			$q11Max = fractal_zip::auto_brotli_q11_max_inner_bytes();
			$useQ11Textlike = ($outerTextlike && $q11Max > 0 && $innerLen <= $q11Max);
			$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_max_inner_bytes_effective();
			$useQ11FzbLiteral = false;
			if($fzbLitMax > 0 && $innerLen <= $fzbLitMax && !fractal_zip::disable_fzb_literal_brotli_q11()) {
				$useQ11FzbLiteral = $this->bundle_inner_eligible_for_fzws($string);
			}
			if($useQ11Textlike || $useQ11FzbLiteral) {
				$brotliQ = 11;
			}
		}
		$capQ = getenv('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP');
		if($capQ !== false && trim((string) $capQ) !== '' && ctype_digit(trim((string) $capQ))) {
			$cq = (int) trim((string) $capQ);
			if($cq >= 0 && $cq <= 11) {
				$brotliQ = min($brotliQ, $cq);
			}
		}
		$brotliBlob = $this->outer_brotli_blob($brotliExe, $string, $brotliQ, $brTo, null, $fzCodecTmpIn);
	}
	$bestCodec = 'gzip';
	$bestBlob = $bestGzipBlob !== null ? $bestGzipBlob : $string;
	$bestLen = $bestGzipBlob !== null ? strlen($bestGzipBlob) : PHP_INT_MAX;
	if($zstdBlob !== null) {
		$zstdL = strlen($zstdBlob);
		if($zstdL > 0 && fractal_zip::outer_candidate_beats_current('zstd', $zstdL, $bestCodec, $bestLen)) {
			$bestCodec = 'zstd';
			$bestBlob = $zstdBlob;
			$bestLen = $zstdL;
		}
	}
	if($brotliBlob !== null) {
		$brL = strlen($brotliBlob);
		if($brL > 0 && fractal_zip::outer_candidate_beats_current('brotli', $brL, $bestCodec, $bestLen)) {
			$bestCodec = 'brotli';
			$bestBlob = $brotliBlob;
			$bestLen = $brL;
		}
	}
	if(!fractal_zip::allow_outer_expansion() && $bestLen >= $innerLen) {
		if($fzCodecTmpIn !== null && is_file($fzCodecTmpIn)) {
			unlink($fzCodecTmpIn);
		}
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	if($fzCodecTmpIn !== null && is_file($fzCodecTmpIn)) {
		unlink($fzCodecTmpIn);
	}
	if($bestCodec !== 'zpaq') {
		fractal_zip::$last_zpaq_method = null;
	}
	fractal_zip::$last_outer_codec = $bestCodec;
	return $bestBlob;
	} finally {
		if($rollupFastTier) {
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('adaptive_compress_outer_fast_codec_tier');
		}
	}
}

/**
 * Target quality for the single full brotli refinement pass in {@see adaptive_compress} (after fast Q1 + medium Q3 tiers). Fast/medium tiers ignore this unless {@code FRACTAL_ZIP_BROTLI_QUALITY} is set (full tier only).
 */
function adaptive_compress_compute_first_brotli_quality(int $innerLen, bool $speedMode, bool $isHuge, bool $outerTextlike, bool $bundleInnerFzws, bool $autoTextlikeFzbBrotliFull, bool $mergedFlacBundleHuge, bool $ultraSingleTxtRawSweep, string $brotliHugeMode): int {
	$bq = fractal_zip::brotli_quality_env_raw();
	$brotliDefault = $speedMode ? 7 : 10;
	$brotliQ = ($bq === false) ? $brotliDefault : max(0, min(11, (int) $bq));
	if($bq === false && !$speedMode && !fractal_zip::disable_auto_textlike_brotli_q11() && !fractal_zip::disable_auto_textlike_fzb_brotli_q11()) {
		$q11Max = fractal_zip::auto_brotli_q11_max_inner_bytes();
		$useQ11Textlike = ($outerTextlike && (!$isHuge || $autoTextlikeFzbBrotliFull) && ($autoTextlikeFzbBrotliFull || ($q11Max > 0 && $innerLen <= $q11Max)));
		$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_max_inner_bytes_effective();
		$useQ11FzbLiteral = ($fzbLitMax > 0 && $innerLen <= $fzbLitMax && $bundleInnerFzws && !fractal_zip::disable_fzb_literal_brotli_q11());
		$useQ11Fzs1SingleRaw = $ultraSingleTxtRawSweep && ($q11Max <= 0 || $innerLen <= $q11Max) && !fractal_zip::disable_fzb_literal_brotli_q11();
		if($useQ11Textlike || $useQ11FzbLiteral || $useQ11Fzs1SingleRaw) {
			$brotliQ = 11;
		}
	}
	if($bq === false && !$speedMode && $brotliHugeMode === 'full'
		&& $bundleInnerFzws && !fractal_zip::disable_fzb_literal_brotli_q11()) {
		$brotliQ = 11;
	}
	if($bq === false && !$speedMode && $mergedFlacBundleHuge
		&& !fractal_zip::disable_merged_flac_bundle_brotli_q11()) {
		$brotliQ = 11;
	}

	return $brotliQ;
}

/**
 * Single “full” brotli outer pass: compress from raw inner at {@code $baseQuality}, then Q11+lgwin refinement (no separate “extra sweep” path).
 *
 * @param string|null $fzCodecTmpIn
 * @return string|null Refined brotli payload, or null on failure
 */
function adaptive_compress_brotli_outer_full_quality_lgwin_refine(
	fractal_zip $fz,
	string $brotliExe,
	string $string,
	int $baseQuality,
	bool $bundleInnerFzws,
	bool $ultraSingleTxtRawSweep,
	bool $outerTextlike,
	bool $speedMode,
	$fzCodecTmpIn
) {
	$brotliQ = max(0, min(11, $baseQuality));
	$brotliBlob = $fz->outer_brotli_blob($brotliExe, $string, $brotliQ, null, null, $fzCodecTmpIn);
	if($brotliBlob === null || $brotliBlob === '') {
		return null;
	}
	$innerLen = strlen($string);
	$jpegEmbeddedBrotliLgwinSweep = false;
	if(!$speedMode && $innerLen >= 64 && $innerLen <= 524288) {
		$p = strpos($string, "\xff\xd8");
		if($p !== false && $p + 64 <= $innerLen && substr($string, $p, 2) === "\xff\xd8") {
			$jpegEmbeddedBrotliLgwinSweep = true;
		}
	}
	if(($bundleInnerFzws || $ultraSingleTxtRawSweep || $jpegEmbeddedBrotliLgwinSweep)
		&& ((int) $brotliQ === 11 || $jpegEmbeddedBrotliLgwinSweep)
		&& fractal_zip::literal_bundle_brotli_lgwin_env_unset_or_empty()
		&& fractal_zip::brotli_fzb_q11_lgwin_refinement_allowed($jpegEmbeddedBrotliLgwinSweep)) {
		$lgwinQual = (int) $brotliQ;
		if($jpegEmbeddedBrotliLgwinSweep && $lgwinQual === 10) {
			$br11up = $fz->outer_brotli_blob($brotliExe, $string, 11, null, null, $fzCodecTmpIn);
			if($br11up !== null) {
				$l11 = strlen($br11up);
				if($l11 > 0 && $l11 < strlen($brotliBlob)) {
					$brotliBlob = $br11up;
				}
			}
			$lgwinQual = 11;
		}
		$brotliLen = strlen($brotliBlob);
		$lgwinTrials = array();
		if(!$outerTextlike) {
			$lgwinTrials[] = array('q' => $lgwinQual, 'w' => 16);
		}
		if(($outerTextlike || $ultraSingleTxtRawSweep || $jpegEmbeddedBrotliLgwinSweep) && !$speedMode && !fractal_zip::disable_brotli_q11_textlike_lgwin_extra_sweep()
			&& $innerLen > 0 && $innerLen <= 524288) {
			foreach(array(20, 22, 24) as $xLw) {
				$lgwinTrials[] = array('q' => $lgwinQual, 'w' => $xLw);
			}
		}
		$forkMinLgwin = fractal_zip_encode_pipeline::parallel_speculative_outer_min_inner_bytes();
		$lgwinPar = (count($lgwinTrials) >= 2)
			&& ($forkMinLgwin <= 0 || $innerLen >= $forkMinLgwin)
			? fractal_zip_encode_pipeline::parallel_brotli_lgwin_variant_trials($string, $lgwinTrials, true)
			: null;
		if($lgwinPar !== null) {
			foreach($lgwinPar as $brX) {
				if($brX !== null && $brX !== '') {
					$brXLen = strlen($brX);
					if($brXLen > 0 && $brXLen < $brotliLen) {
						$brotliBlob = $brX;
						$brotliLen = $brXLen;
					}
				}
			}
		} else {
			foreach($lgwinTrials as $t) {
				$brX = $fz->outer_brotli_blob($brotliExe, $string, (int) $t['q'], null, (int) $t['w'], $fzCodecTmpIn);
				if($brX !== null) {
					$brXLen = strlen($brX);
					if($brXLen > 0 && $brXLen < $brotliLen) {
						$brotliBlob = $brX;
						$brotliLen = $brXLen;
					}
				}
			}
		}
	}

	return $brotliBlob;
}

/**
 * Pipeline: compact FZC2 (16-bit path/value lens when each ≤ 65535) or FZC1 payload (or legacy serialized payload) → then either 7z (LZMA2 and/or PPMd -mx=9) or gzip level 9, whichever is smaller.
 * Set FRACTAL_ZIP_FORCE_OUTER=gzip to skip 7z; FRACTAL_ZIP_SKIP_7Z=1 to never invoke the 7z binary; FRACTAL_ZIP_SKIP_PPMD=1 to skip PPMd trials (inner ≥ 4 KiB; midsize inners otherwise try PPMd orders 7–9 then 10).
 * Outer 7z adds <code>-mtc=off -mta=off -mtm=off</code> by default (no meaningful FS times on stdin/file inners); FRACTAL_ZIP_OUTER_7Z_METADATA_STRIP=0 omits those switches.
 * Optional: zstd (FRACTAL_ZIP_SKIP_ZSTD=1), brotli (FRACTAL_ZIP_SKIP_BROTLI=1); FRACTAL_ZIP_ZSTD_LEVEL (default 16 non-speed). Fast-tier brotli uses quality 1; medium tier quality 3 after ratchet when prediction/best says brotli (omitted in bytes-first mode when full Q10/Q11+lgwin runs next — avoids redundant Q3). Q10/Q11 + lgwin refinement runs **after** zpaq/7z/arc predict lanes when layered prediction’s medium‑tier winner is brotli or layered did not set a family and the fast tier chose zpaq ({@see adaptive_compress_compute_first_brotli_quality}). FRACTAL_ZIP_BROTLI_QUALITY pins the full refinement tier only (when unset, fast stays Q1; medium Q3 applies mainly under {@code FRACTAL_ZIP_SPEED=1} when full tier is skipped). FRACTAL_ZIP_SKIP_BROTLI_IF_ZSTD_EARLYSTOP_MIN_INNER_BYTES (default 1 MiB): below this inner size, brotli still runs after a zstd-triggered early-stop unless huge fast paths skip it.
 * Text-like midsize zstd: FRACTAL_ZIP_ZSTD_TEXTLIKE_LEVEL (default 14), FRACTAL_ZIP_ZSTD_TEXTLIKE_MIN_INNER_BYTES (default 32768), FRACTAL_ZIP_DISABLE_ZSTD_TEXTLIKE_LEVEL=1 to disable.
 * Speed guards for tiny inner payloads: FRACTAL_ZIP_MIN_ARC_INNER_BYTES (default 8192, same order as 7z floor), FRACTAL_ZIP_MIN_ZSTD_INNER_BYTES (default 2048), FRACTAL_ZIP_MIN_BROTLI_INNER_BYTES (default 1024). Parallel fork pools use FRACTAL_ZIP_PARALLEL_OUTER_MIN_INNER_BYTES (default 8192; {@see fractal_zip_encode_pipeline::parallel_speculative_outer_min_inner_bytes}). Midsize text-like: see re-open after FRACTAL_ZIP_HUGE_SLOW_OUTER_PASS (7z/arc when early-stop would have skipped, inner 8 KiB–256 KiB). Non–text-like midsize: FRACTAL_ZIP_OUTER_MIDSIZE_BINARY_REOPEN_MAX_INNER_BYTES (default 512 KiB, still below huge_slow sweep floor).
 * Random-like prescreen can skip arc/zstd/brotli for likely incompressible payloads; disable with FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN=1.
 * zstd/brotli and FreeArc / zpaq outers: by default still run when prescreen is positive if gzip shrinks the inner meaningfully (see FRACTAL_ZIP_ZPAQ_OUTER_IGNORE_PRESCREEN and FRACTAL_ZIP_ZPAQ_OUTER_PRESCREEN_OVERRIDE_MIN_GZIP_SAVING_* — env names kept for compatibility); set FRACTAL_ZIP_ZPAQ_OUTER_IGNORE_PRESCREEN=0 to skip them whenever prescreen fires.
 * Prescreen tuning knobs: FRACTAL_ZIP_OUTER_PRESCREEN_{MIN_BYTES,SAMPLE_BYTES,SAMPLE_MIN_BYTES,UNIQUE_MIN,REPEAT_MAX}.
 * Huge FZB4/5/6 inners: FRACTAL_ZIP_SKIP_XZ_BUNDLE_HUGE=1 skips extra xz try; FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS=1 skips 7z/arc sweep unlock (incl. bytes-first reopen after xz when inner ≥ FRACTAL_ZIP_HUGE_SLOW_OUTER_MIN_INNER_BYTES).
 * Non-text-like huge FZB literals: 7z outer sweep runs by default (better .fzc vs raw+7z benches). FRACTAL_ZIP_FZB_BINARY_SKIP_7Z_SWEEP=1 skips it for speed.
 * Multi-MiB FZB + 7z: FRACTAL_ZIP_SKIP_XZ_LITERAL_HUGE_MIN_INNER_BYTES (unset ⇒ bytes-first: no gate; FRACTAL_ZIP_SPEED=1 ⇒ default 4000000) skips xz when 7-Zip is available (saves time vs 7z/PPMd path).
 * XZ trigger: FRACTAL_ZIP_XZ_TRIGGER_RATIO (default 0.82 vs gzip). Bytes-first applies the ratio gate like FRACTAL_ZIP_DEEP=1; FRACTAL_ZIP_SPEED=1 keeps ratio-gated xz only when DEEP=1 (or huge FZB bundle path / FRACTAL_ZIP_FORCE_XZ=1).
 * Huge non-FZB inners: FreeArc runs in bytes-first (like FRACTAL_ZIP_DEEP=1); FRACTAL_ZIP_SPEED=1 skips unless DEEP=1, FRACTAL_ZIP_FORCE_ARC=1, or FZB-huge inner.
 * FRACTAL_ZIP_BROTLI_HUGE_MODE: unset ⇒ bytes-first default <code>probe</code> (cheap Q1 probe, then full brotli if competitive); FRACTAL_ZIP_SPEED=1 ⇒ default <code>skip</code>. Set <code>full</code> / <code>skip</code> / <code>probe</code> explicitly to override.
 * Text-like huge outer payloads (incl. non-FZB unified-stream inners ≤ cap): same as FZB path — full brotli when auto textlike path applies (mode skip/probe still allow this branch).
 *   FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MAX_INNER_BYTES (default = max(whole_stream_fzws_max_bytes, min floor), else 128 MiB when whole-stream cap unset) caps inner size; FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI=1 disables both. FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MIN_FLOOR_BYTES (default 512 MiB; set 0 to disable floor) raises the default cap for huge unified HTML inners.
 *   FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_NONFZB_HUGE_BROTLI=1 restores legacy behavior (only FZB-tagged huge literals get auto brotli, not other huge text-like inners).
 * Text-like outer payloads (HTML/JSON-like heuristics): brotli Q11 up to FRACTAL_ZIP_AUTO_BROTLI_Q11_MAX_INNER_BYTES (default 2 MiB); disable with
 *   FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_BROTLI_Q11=1 (alias: FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI_Q11 for FZB-only legacy).
 * Small binary literal bundles (FZB4/5/6 prefix): brotli Q11 when inner ≤ FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES (unset ⇒ bytes-first 16 MiB; FRACTAL_ZIP_SPEED=1 ⇒ 64 KiB);
 *   disable with FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11=1. Path-order/layout proxy scoring uses FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES or min(that cap, 512 KiB) so multi‑MiB folders do not run brotli Q11 hundreds of times.
 * When brotli outer is extremely small vs inner (ratio knobs FRACTAL_ZIP_SKIP_SLOW_OUTERS_AFTER_BROTLI_*), skip xz / reopen-7z sweep for speed.
 * Highly gzip-compressible text-like FZB: FRACTAL_ZIP_SKIP_ZSTD_TEXTLIKE_FZB_GZIP_DIV (default 50) skips zstd when gzLen*div < innerLen (brotli usually wins outer).
 * FreeArc outer: FRACTAL_ZIP_ARC_METHOD=1..9 single trial; else FRACTAL_ZIP_ARC_OUTER_METHODS=comma list; else inners ≥ FRACTAL_ZIP_ARC_OUTER_DUAL_MIN_BYTES (unset ⇒ 64 KiB) try -m4..9 and -m5/-m9 -mx (FRACTAL_ZIP_ARC_OUTER_DUAL_METHODS=0 ⇒ -m5 only; FRACTAL_ZIP_ARC_OUTER_MX=0 skips -mx).
 * zpaq outer (optional binary on PATH or FRACTAL_ZIP_ZPAQ): FRACTAL_ZIP_SKIP_ZPAQ=1 disables; FRACTAL_ZIP_MIN_ZPAQ_INNER_BYTES (default 0); FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES (default 6291456, 0 = no cap); FRACTAL_ZIP_ZPAQ_METHOD (default 5, or full argv fragment starting with -); FRACTAL_ZIP_ZPAQ_TIMEOUT_SEC wraps the add on Unix when set.
 * Unified-stream folders: when outer is zpaq, {@see maybe_folder_zpaq_native_smaller_than_fzc} may replace the wrapped inner with a native raw-tree .zpaq if smaller (adaptive runs capped by FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES, default 8 MiB merged raw; no cap when FRACTAL_ZIP_FORCE_OUTER=zpaq).
 * Bench / A–B: <code>FRACTAL_ZIP_FORCE_OUTER=zpaq</code> skips the outer tournament and keeps zpaq-only behavior (no fallback to 7z/arc/zstd; if zpaq is unavailable/fails, inner is stored unchanged). <code>FRACTAL_ZIP_ZPAQ_OUTER_SWEEP=1</code> forces a multi‑<code>-method</code> sweep at any size; when unset, inners ≤ <code>FRACTAL_ZIP_ZPAQ_OUTER_AUTO_SWEEP_MAX_INNER_BYTES</code> (default 2 MiB, <code>0</code>=off) sweep too. Optional comma list: <code>FRACTAL_ZIP_ZPAQ_OUTER_METHODS</code> (else 6,5,4,3 for inners ≤min(2 MiB, auto cap), else 5,4,3).
 * Outer brotli window: FRACTAL_ZIP_BROTLI_LGWIN=0..24 (optional). Full refinement (after fast Q1 / medium Q3): FZB* + Q11 tries -w 16, then on text-like inners ≤512 KiB also -w 20, -w 22, and -w 24 unless FRACTAL_ZIP_DISABLE_BROTLI_Q11_TEXTLIKE_LGWIN_EXTRA_SWEEP=1 or FRACTAL_ZIP_SPEED=1.
 * If every outer candidate is ≥ inner length, the inner bytes are stored unchanged and <code>last_outer_codec</code> is <code>store</code>.
 * Set <code>FRACTAL_ZIP_ALLOW_OUTER_EXPANSION=1</code> to keep legacy behavior (use best outer even when it does not shrink the payload).
 * Verbose slow-branch skips: set {@code FRACTAL_ZIP_PIPELINE_OUTER_STEP_VERBOSE=1} together with {@code FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1} for zero-time {@code skipped_outer_*} lines when xz / 7z / arc / zpaq outer sections do not run.
 * Benchmark outer prediction: <code>FRACTAL_ZIP_DUMP_PRE_OUTER_INNER</code> path ⇒ write inner bytes once; <code>FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY=1</code> ⇒ skip gzip baseline and all outer trials (return identity inner; {@see fractal_zip::$last_outer_codec} <code>store</code>).
 * Runtime outer prediction (Squash-style layered probes): unset/<code>1</code> ⇒ layered ranking runs immediately after the gzip baseline (before zstd/brotli). Gzip-margin hints trim slow outers only after {@code outer_compression_ratchet_level}: tiny inners (ratchet {@code 0}) keep full 7z/arc/zpaq exploration; ratchet {@code 2} trims heavy trials on huge near‑incompressible payloads when zpaq‑like compression would dominate wall time. {@code FRACTAL_ZIP_OUTER_RATCHET_*}, {@code FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES}. Skips probes when gzip baseline ratio is high ({@code FRACTAL_ZIP_OUTER_PREDICT_MAX_GZIP_LEN_FRAC}). <code>FRACTAL_ZIP_OUTER_PREDICT=0</code> disables. Tunables: <code>FRACTAL_ZIP_OUTER_PREDICT_MIN_INNER_BYTES</code>, <code>FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES</code> (unset ⇒ 8 MiB max probe prefix; <code>FRACTAL_ZIP_SPEED=1</code> ⇒ 2 MiB unless set), <code>FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC</code> (unset ⇒ 30 s default; <code>FRACTAL_ZIP_SPEED=1</code> ⇒ 12 s default unless set), <code>FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH</code> (unset ⇒ skip L3 high-tier subprocess on L2 winner — mapped family unchanged; <code>1</code> ⇒ full probes). <code>FRACTAL_ZIP_OUTER_PREDICT_DEBUG=1</code> ⇒ stderr line (family, ratchet, lanes, gzip gate). <code>FRACTAL_ZIP_DEFER_SLOW_PREDICT_LANES_TO_PARALLEL_WAVE</code> (else legacy <code>FRACTAL_ZIP_DEFER_ZPAQ_PREDICT_TO_PARALLEL_WAVE</code>; unset ⇒ on): when prediction names zpaq/7z/arc and the parallel slow outer wave would run that codec with another slow codec, skip the matching early predict lane ({@see fractal_zip::defer_slow_predict_lanes_to_parallel_wave_enabled}); <code>0</code> restores dedicated predict lanes.
 *
 * @param string|null $outerStopAfter When {@see fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE}, return after the gzip/zstd/brotli tournament (used by legacy {@code zip_folder} fast shootout; one full slow stack runs only on the chosen wire).
 */
function adaptive_compress($string, $outerStopAfter = null) {
	fractal_zip::$last_outer_codec = null;
	fractal_zip::$last_zpaq_method = null;
	$innerLen = strlen($string);
	$outerStepLog = getenv('FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG') === '1';
	$rollupOuterSteps = fractal_zip_encode_pipeline::pipeline_outer_step_rollup_enabled()
		&& fractal_zip_encode_pipeline::pipeline_outer_step_rollup_session_active();
	$outerStepCtx = array('t0' => microtime(true), 'last' => microtime(true));
	$outerStep = static function(string $label) use (&$outerStepCtx, $innerLen, $outerStepLog, $rollupOuterSteps): void {
		if(!$outerStepLog && !$rollupOuterSteps) {
			return;
		}
		$now = microtime(true);
		$cum = ($now - $outerStepCtx['t0']) * 1000.0;
		$d = ($now - $outerStepCtx['last']) * 1000.0;
		$outerStepCtx['last'] = $now;
		if($rollupOuterSteps) {
			fractal_zip_encode_pipeline::pipeline_outer_step_rollup_record($label, $d);
		}
		if($outerStepLog) {
			fprintf(STDERR, "[adaptive_outer] inner=%d B cum=%6.1f ms step=%6.1f ms %s\n", $innerLen, $cum, $d, $label);
		}
	};
	$outerSkip = static function(string $label) use (&$outerStepCtx, $innerLen): void {
		if(getenv('FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG') !== '1' || getenv('FRACTAL_ZIP_PIPELINE_OUTER_STEP_VERBOSE') !== '1') {
			return;
		}
		$now = microtime(true);
		$cum = ($now - $outerStepCtx['t0']) * 1000.0;
		$outerStepCtx['last'] = $now;
		fprintf(STDERR, "[adaptive_outer] inner=%d B cum=%6.1f ms step=   0.0 ms %s\n", $innerLen, $cum, $label);
	};
	$outerStep('start');
	// Benchmarks/predict_outer_benchmarks: dump pre-outer inner and optionally skip all outer codecs (returns identity inner).
	$dumpPreOuter = getenv('FRACTAL_ZIP_DUMP_PRE_OUTER_INNER');
	if(is_string($dumpPreOuter) && ($dumpPreOuter = trim($dumpPreOuter)) !== '') {
		@file_put_contents($dumpPreOuter, $string);
	}
	if(getenv('FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY') === '1') {
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	// gzip baseline: gzdeflate is typically smaller than gzcompress (no wrapper),
	// so avoid doing both (saves CPU on large inputs).
	$df = gzdeflate($string, 9);
	$bestGzipBlob = ($df !== false) ? $df : null;
	if(fractal_zip::outer_force_codec_is_gzip()) {
		$out = $bestGzipBlob !== null ? $bestGzipBlob : $string;
		if(!fractal_zip::allow_outer_expansion() && ($bestGzipBlob === null || strlen($bestGzipBlob) >= $innerLen)) {
			fractal_zip::$last_outer_codec = 'store';
			return $string;
		}
		fractal_zip::$last_outer_codec = 'gzip';
		return $out;
	}
	$forcedZpaq = $this->try_force_outer_zpaq_payload($string);
	if($forcedZpaq !== null) {
		return $forcedZpaq;
	}
	$gzLen = $bestGzipBlob !== null ? strlen($bestGzipBlob) : PHP_INT_MAX;
	$speedMode = fractal_zip::speed_mode_enabled();
	$deepMode = fractal_zip::deep_mode_enabled();
	$parallelOuterForkMinInner = fractal_zip_encode_pipeline::parallel_speculative_outer_min_inner_bytes();
	$fzRepoRoot = dirname(__FILE__);
	// Layered outer probes first (benchmarks/predict_outer_*); ratchet trims expensive trials later — not gated on fast-codec margin.
	$outerPredFamily = null;
	$outerPredictSkippedCompressibility = false;
	$predictForkPid = null;
	$predictForkTd = null;
	if(fractal_zip::outer_prediction_runtime_enabled() && $innerLen >= fractal_zip::outer_prediction_min_inner_bytes()) {
		if(fractal_zip::outer_prediction_skip_layered_for_gzip_baseline($innerLen, $gzLen)) {
			$outerPredictSkippedCompressibility = true;
		} elseif(fractal_zip_encode_pipeline::speculative_outer_prediction_overlap_enabled()
			&& ($parallelOuterForkMinInner <= 0 || $innerLen >= $parallelOuterForkMinInner)) {
			$predFork = fractal_zip_encode_pipeline::speculative_outer_prediction_begin($string, $fzRepoRoot, true);
			if(isset($predFork['pid']) && $predFork['pid'] !== null && (int) $predFork['pid'] > 0) {
				$predictForkPid = (int) $predFork['pid'];
				$predictForkTd = $predFork['td'];
			} else {
				try {
					require_once $fzRepoRoot . '/fractal_zip_outer_predict.php';
					$outerPredFamily = fractal_zip_outer_predict_mapped_winner($string, $fzRepoRoot);
				} catch (Throwable $e) {
					$outerPredFamily = null;
				}
			}
		} else {
			try {
				require_once $fzRepoRoot . '/fractal_zip_outer_predict.php';
				$outerPredFamily = fractal_zip_outer_predict_mapped_winner($string, $fzRepoRoot);
			} catch (Throwable $e) {
				$outerPredFamily = null;
			}
		}
	}
	try {
	$outerStep('after_gzip_baseline');
	$fzCodecTmpIn = null;
	$fzCodecTmpEnsureDone = false;
	$sevenBlob = null;
	$arcBlob = null;
	$zstdBlob = null;
	$brotliBlob = null;
	// Speed-first mode reduces expensive outer trials; can be overridden by explicitly un-skipping codecs.
	$try7z = !$speedMode;
	$tryArc = !$speedMode;
	$tryBrotli = !$speedMode;
	$tryZstd = true;
	if($speedMode && fractal_zip::speed_try_7z_enabled()) {
		$try7z = true;
	}
	if($speedMode && fractal_zip::speed_try_arc_enabled()) {
		$tryArc = true;
	}
	if($speedMode && fractal_zip::speed_try_brotli_enabled()) {
		$tryBrotli = true;
	}
	if($speedMode && fractal_zip::speed_skip_zstd_enabled()) {
		$tryZstd = false;
	}
	$seven = fractal_zip::seven_zip_executable();
	// Staged outer tournament: try fast codecs first; only pay for slow ones (7z/arc) if needed.
	$stopBytes = fractal_zip::outer_early_stop_bytes();
	$stopPct = fractal_zip::outer_early_stop_pct();
	// For small inner payloads, be more aggressive about skipping slow outer codecs.
	// (You can disable by setting FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC=0.)
	$dyn = fractal_zip::outer_early_stop_dynamic_enabled();
	if($speedMode && $dyn && $innerLen < 32768) {
		$stopBytes = min($stopBytes, 8);
		$stopPct = min($stopPct, 0.005);
	} elseif($speedMode && $dyn && $innerLen < 131072) {
		$stopBytes = min($stopBytes, 16);
		$stopPct = min($stopPct, 0.008);
	}
	$min7zInner = fractal_zip::min_7z_inner_bytes();
	$arcExe = fractal_zip::freearc_executable();
	$minArcInner = fractal_zip::min_arc_inner_bytes();
	$zstdExe = fractal_zip::zstd_executable();
	$minZstdInner = fractal_zip::min_zstd_inner_bytes();
	$brotliExe = fractal_zip::brotli_executable();
	$xzExe = fractal_zip::xz_executable();
	$zpaqExe = fractal_zip::zpaq_executable();
	$minZpaqInner = fractal_zip::min_zpaq_inner_bytes();
	$maxZpaqInner = fractal_zip::max_zpaq_inner_bytes();
	$zpaqInnerEligible = ($innerLen >= $minZpaqInner) && ($maxZpaqInner <= 0 || $innerLen <= $maxZpaqInner);
	$minBrotliInner = fractal_zip::min_brotli_inner_bytes();

	// Define huge-payload flag early (used by codec gating below).
	$maxBr = fractal_zip::max_brotli_outer_inner_bytes();
	$isHuge = ($maxBr > 0 && $innerLen > $maxBr);
	$bundleInnerFzws = $this->bundle_inner_eligible_for_fzws($string);
	$fzbHuge = ($isHuge && $bundleInnerFzws);
	$outerTextlike = $this->outer_likely_textlike($string);
	// Default aligns with whole-stream FZWS cap: unified-folder inners are often multi‑MiB text-like; a 4 MiB cap caused zstd
	// early-stop to skip brotli entirely while min-ext still ran brotli (bytes regression on e.g. test_files55).
	// Whole-stream FZWS default (128 MiB) fed this gate; unified-folder HTML inners for ~200 MiB raw trees can exceed it
	// while still text-like. Without a higher floor, zstd wins outer early and .fzc loses to min-ext arc (full test_files55).
	// Override with FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MAX_INNER_BYTES; lower floor via FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MIN_FLOOR_BYTES=0 to restore legacy cap.
	$textlikeFzbBrotliMax = fractal_zip::outer_brotli_textlike_fzb_full_max_inner_bytes();
	$textlikeHugeBrotliBase = ($isHuge && $textlikeFzbBrotliMax > 0 && $innerLen <= $textlikeFzbBrotliMax && $outerTextlike && !fractal_zip::disable_auto_textlike_fzb_brotli());
	$allowNonFzbTextlikeHugeBrotli = !fractal_zip::disable_auto_textlike_nonfzb_huge_brotli();
	$sigTop = ($innerLen >= 4) ? substr($string, 0, 4) : '';
	$mergedFlacBundleHuge = ($fzbHuge && $textlikeFzbBrotliMax > 0 && $innerLen <= $textlikeFzbBrotliMax && !fractal_zip::disable_auto_merged_flac_bundle_huge_brotli()
		&& (($sigTop === 'FZCD') || ($sigTop === 'FZBM' && $innerLen >= 5 && ord($string[4]) === 1)));
	$autoTextlikeFzbBrotliFull = ($textlikeHugeBrotliBase && ($fzbHuge || $allowNonFzbTextlikeHugeBrotli)) || $mergedFlacBundleHuge;

	$canTryArc = ($tryArc && $arcExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ARC') && $innerLen >= $minArcInner);
	$canTryZstd = ($tryZstd && $zstdExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZSTD') && $innerLen >= $minZstdInner);
	$canTryBrotli = ($tryBrotli && $brotliExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_BROTLI') && $innerLen >= $minBrotliInner);
	// Huge FZB* inners: min-ext often wins with zstd; bytes-first keeps zstd in the outer tournament (default threshold 0 = never skip here).
	// FRACTAL_ZIP_SPEED=1 restores a 4 MiB skip when 7z is available unless FRACTAL_ZIP_SKIP_ZSTD_LITERAL_HUGE_MIN_INNER_BYTES is set explicitly.
	// Set N>0 to skip zstd for innerLen≥N when fzbHuge && seven_zip present (saves outer CPU on huge literal bundles).
	$skipZstdHugeFzbMin = fractal_zip::skip_zstd_literal_huge_min_inner_bytes_effective();
	if($canTryZstd && $fzbHuge && $seven !== null && $skipZstdHugeFzbMin > 0 && $innerLen >= $skipZstdHugeFzbMin) {
		$canTryZstd = false;
	}
	if($canTryZstd && $autoTextlikeFzbBrotliFull && fractal_zip::zstd_textlike_fzb_gzip_div_skip_enabled()) {
		$gzipDiv = fractal_zip::skip_zstd_textlike_fzb_gzip_div();
		if($gzipDiv > 0 && $gzLen !== PHP_INT_MAX && $gzLen > 0 && $gzLen * $gzipDiv < $innerLen) {
			$canTryZstd = false;
		}
	}
	// Speed: arc is expensive on huge non-FZB; bytes-first keeps it (DEEP behavior). FRACTAL_ZIP_SPEED=1 skips unless DEEP/forced/FZB inner.
	$forceArc = fractal_zip::force_arc_enabled();
	if($canTryArc && $isHuge && $speedMode && !$deepMode && !$forceArc && !$fzbHuge) {
		$canTryArc = false;
	}
	// brotli-on-huge policy:
	// - default: skip (fastest)
	// - mode=probe: try low-quality brotli and keep only if it beats current best by N bytes
	// - mode=full: allow normal brotli quality on huge payloads (slowest; best odds of bytes)
	$brotliHugeMode = fractal_zip::brotli_huge_mode_env_normalized();
	if($brotliHugeMode === '') {
		// Bytes-first: full brotli on huge inners (phase 4 outer tournament). Speed: skip unless probe/full env.
		$brotliHugeMode = $speedMode ? 'skip' : 'full';
	}
	$brotliHugeProbe = false;
	$forceBrotli = fractal_zip::force_brotli_enabled();
	if($canTryBrotli && $isHuge && !$forceBrotli) {
		if($brotliHugeMode === 'full') {
			// keep enabled
		} elseif($autoTextlikeFzbBrotliFull) {
			// Large text-like FZB* inners: brotli outer often beats zstd/7z; do not require env override.
		} elseif($brotliHugeMode === 'probe') {
			$brotliHugeProbe = true;
		} else {
			$canTryBrotli = false;
		}
	}
	$needIncomp = ($canTryArc || $canTryZstd || $canTryBrotli);
	// FZB4/5/6 (and FZBM/FZBF/FZBD-wrapped literals) embed ZIP/deflate/PNG bytes; the prescreen often marks them “incompressible”
	// and skips zstd even when zstd beats gzip by a wide margin (e.g. test_files65: two game ZIPs as literals). For those inners we
	// force $likelyIncompressible=false — skip the O(n) outer_likely_incompressible walk (same outcome as compute-then-clear).
	if($needIncomp && !$bundleInnerFzws) {
		$likelyIncompressible = $this->outer_likely_incompressible($string);
	} else {
		$likelyIncompressible = false;
	}
	// Compact single-member container tiers (FZS1/FZCT/FZCH/FZCC–FZCK/FZCJ/FZCX/FZCE/FZCU/FZCM/FZC1–6): headers bias outer_likely_textlike, so Brotli can stay at
	// Q10 unless we force the same Q11 + lgwin sweep as min-ext under ultra (text + small-path raw corpora).
	$brotliFirstForkPid = null;
	$brotliFirstForkTd = null;
	$brotliProbeForkPid = null;
	$brotliProbeForkTd = null;
	/** Fast outer brotli probe ({@code adaptive_compress} tier before zstd merge): quality 1 only — full Q10/Q11 + lgwin runs later if brotli wins. */
	$fzAdaptiveOuterFastBrotliQ = 1;
	$fzc3Single = false;
	if($innerLen >= 10 && $sigTop === 'FZC3') {
		$u3 = unpack('nctr', substr($string, 4, 2));
		$fzc3Single = ($u3 !== false && (int) ($u3['ctr'] ?? 0) === 1);
	}
	$fzc12Single = false;
	if($innerLen >= 12 && ($sigTop === 'FZC1' || $sigTop === 'FZC2')) {
		$u12 = unpack('Ncnt', substr($string, 4, 4));
		$fzc12Single = ($u12 !== false && (int) ($u12['cnt'] ?? 0) === 1);
	}
	$fzc56Single = false;
	if($innerLen >= 6 && ($sigTop === 'FZC5' || $sigTop === 'FZC6')) {
		$fzc56Single = (ord($string[4]) === 1);
	}
	$hFlatU = ($innerLen >= 6) ? $sigTop : '';
	static $fzcFlatUltraSigSet = null;
	if($fzcFlatUltraSigSet === null) {
		$fzcFlatUltraSigSet = array_flip(array(
			'FZCC', 'FZCL', 'FZCG', 'FZCP', 'FZCK', 'FZCU', 'FZCM', 'FZCE',
			'FZCB', 'FZCN', 'FZCQ', 'FZCS', 'FZCI', 'FZCR', 'FZCV', 'FZCY', 'FZCW',
			'FZCA', 'FZCF', 'FZCZ', 'FZCO',
			'FZC7', 'FZC8', 'FZC9', 'FZC0',
			'FZB7', 'FZB8', 'FZB9', 'FZB0', 'FZBL', 'FZTO', 'FZYM', 'FZRS', 'FZGO', 'FZRB',
			'FZZP', 'FZTR', 'FZGZ', 'FZ7Z', 'FZBZ', 'FZL4', 'FZZS', 'FZBR', 'FZXZ', 'FZQL', 'FZSH', 'FZBT', 'FZ1P',
			'FZJA', 'FZJR', 'FZKT', 'FZKS', 'FZHI', 'FZHP', 'FZ2C', 'FZPP', 'FZNI', 'FZFG', 'FZSV', 'FZTV', 'FZDF', 'FZLG', 'FZDT', 'FZRL', 'FZPL', 'FZPM', 'FZSC', 'FZFS', 'FZMO', 'FZMM', 'FZJL',
			'FZSL', 'FZGL', 'FZY2', 'FZVT', 'FZA4', 'FZMX', 'FZGQ', 'FZLE', 'FZSA', 'FZS2', 'FZST', 'FZTF', 'FZF2', 'FZ3B', 'FZ3L', 'FZ3P', 'FZ3E', 'FZ3H', 'FZ3M', 'FZ3G', 'FZ3X', 'FZ3C', 'FZ2J', 'FZ8P', 'FZ3K',
			'FZ0C', 'FZ2T', 'FZ2M', 'FZ2E', 'FZ2S', 'FZ2L', 'FZ2B', 'FZR1', 'FZ2A', 'FZ2O', 'FZR0', 'FZ2H', 'FZ2I', 'FZ2G', 'FZ2Z', 'FZ0B', 'FZG0', 'FZSW', 'FZP0', 'FZ2V',
		));
	}
	$fzcFlatSquashSingleUltra = ($innerLen >= 6 && isset($fzcFlatUltraSigSet[$hFlatU]));
	$ultraSingleTxtRawSweep = fractal_zip::ultra_compression_enabled() && (
		($innerLen >= 5 && $sigTop === 'FZS1')
		|| ($innerLen >= 8 && $sigTop === 'FZTA' && ord($string[4]) === 1)
		|| ($innerLen >= 8 && $sigTop === 'FZCT')
		|| ($innerLen >= 9 && $sigTop === 'FZCH')
		|| ($innerLen >= 7 && $sigTop === 'FZCJ')
		|| ($innerLen >= 10 && $sigTop === 'FZCX')
		|| $fzcFlatSquashSingleUltra
		|| ($innerLen >= 7 && $sigTop === 'FZC4' && ord($string[4]) === 1)
		|| $fzc3Single
		|| $fzc12Single
		|| $fzc56Single
	);
	$slowOuterAllowed = fractal_zip::outer_slow_codec_allowed_despite_prescreen($likelyIncompressible, $innerLen, $gzLen);
	$parAdaptiveFirstBrotliOn = fractal_zip_encode_pipeline::parallel_adaptive_outer_first_brotli_enabled();
	if($parAdaptiveFirstBrotliOn
		&& !$brotliHugeProbe && $canTryZstd && $canTryBrotli && $slowOuterAllowed
		&& ($parallelOuterForkMinInner <= 0 || $innerLen >= $parallelOuterForkMinInner)) {
		$specFork = fractal_zip_encode_pipeline::speculative_outer_begin_first_brotli($string, $fzAdaptiveOuterFastBrotliQ, true);
		if(isset($specFork['pid']) && $specFork['pid'] !== null && (int) $specFork['pid'] > 0) {
			$brotliFirstForkPid = (int) $specFork['pid'];
			$brotliFirstForkTd = $specFork['td'];
		}
	}
	if($brotliHugeProbe && $parAdaptiveFirstBrotliOn
		&& $canTryZstd && $canTryBrotli && $slowOuterAllowed
		&& ($parallelOuterForkMinInner <= 0 || $innerLen >= $parallelOuterForkMinInner)) {
		$probeFork = fractal_zip_encode_pipeline::speculative_outer_begin_huge_probe_brotli($string, true);
		if(isset($probeFork['pid']) && $probeFork['pid'] !== null && (int) $probeFork['pid'] > 0) {
			$brotliProbeForkPid = (int) $probeFork['pid'];
			$brotliProbeForkTd = $probeFork['td'];
		}
	}
	$outerStep('before_zstd_outer');
	$xzBlob = null;
	$zstdTriggersEarlyStop = false;
	if($canTryZstd && $slowOuterAllowed) {
		$this->outer_codec_reuse_tmp_ensure($string, $innerLen, $fzCodecTmpIn, $fzCodecTmpEnsureDone);
		$zl = fractal_zip::zstd_level_env_raw();
		$zstdDefault = $speedMode ? 9 : 16;
		$zstdLevel = ($zl === false) ? $zstdDefault : max(1, min(22, (int) $zl));
		// CSV / logs: high zstd levels cost a lot of time for little gain; lower level improves bytes×time.
		if($zl === false && !$speedMode && !$isHuge && !fractal_zip::disable_zstd_textlike_level()) {
			$tlMin = fractal_zip::zstd_textlike_min_inner_bytes();
			if($tlMin > 0 && $innerLen >= $tlMin && $outerTextlike) {
				$zstdLevel = fractal_zip::zstd_textlike_level();
			}
		}
		// Multi-megabyte FZB literal bundles (typical single huge binary): nudge zstd toward max when level not pinned by env.
		if($zl === false && !$speedMode && $fzbHuge && !fractal_zip::disable_zstd_fzb_huge_level()) {
			$fzbMin = fractal_zip::zstd_fzb_huge_min_inner_bytes();
			if($fzbMin > 0 && $innerLen >= $fzbMin) {
				$fzbLev = fractal_zip::zstd_fzb_huge_level();
				if($fzbLev > $zstdLevel) {
					$zstdLevel = $fzbLev;
				}
			}
		}
		$didParallelZstd1615 = false;
		if($zl === false && !$speedMode && $zstdLevel === 16 && $bundleInnerFzws && !fractal_zip::disable_zstd_fzb_alt15()
			&& ($parallelOuterForkMinInner <= 0 || $innerLen >= $parallelOuterForkMinInner)
			&& fractal_zip_encode_pipeline::parallel_zstd_fzb_alt_wave_enabled()) {
			$zp1615 = fractal_zip_encode_pipeline::parallel_zstd_fzb_16_and_15_trials($string, true);
			if($zp1615 !== null) {
				$didParallelZstd1615 = true;
				$b16 = $zp1615[16] ?? null;
				$b15 = $zp1615[15] ?? null;
				$len16 = ($b16 !== null && $b16 !== '') ? strlen($b16) : 0;
				$len15 = ($b15 !== null && $b15 !== '') ? strlen($b15) : 0;
				if($len16 <= 0 && $len15 <= 0) {
					$zstdBlob = null;
				} elseif($len16 > 0 && $len15 > 0) {
					$zstdBlob = ($len15 < $len16) ? $b15 : $b16;
				} elseif($len16 > 0) {
					$zstdBlob = $b16;
				} else {
					$zstdBlob = $b15;
				}
			}
		}
		if(!$didParallelZstd1615) {
			$zstdBlob = $this->outer_zstd_blob($zstdExe, $string, $zstdLevel, $fzCodecTmpIn);
			// Default outer level is 16; on some FZB* literal inners (e.g. small BMP bundles) zstd-15 is a few bytes smaller than 16 and can beat brotli Q11.
			if($zstdBlob !== null && $zl === false && !$speedMode && $zstdLevel === 16
				&& $bundleInnerFzws && !fractal_zip::disable_zstd_fzb_alt15()) {
				$zstdAlt = $this->outer_zstd_blob($zstdExe, $string, 15, $fzCodecTmpIn);
				if($zstdAlt !== null) {
					$zAL = strlen($zstdAlt);
					$zBL = strlen($zstdBlob);
					if($zAL > 0 && $zAL < $zBL) {
						$zstdBlob = $zstdAlt;
					}
				}
			}
		}
		if($zstdBlob !== null) {
			fractal_zip::message_once('zstd available (' . $zstdExe . '). Outer container also compares zstd for each .fzc.');
			$zl2 = strlen($zstdBlob);
			if($zl2 > 0 && $zl2 < $gzLen) {
				$improve = $gzLen - $zl2;
				$zstdTriggersEarlyStop = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
			}
		}
	}
	$outerStep('after_zstd_outer');

	if($canTryBrotli && $slowOuterAllowed) {
		$alwaysTryBr = fractal_zip::always_try_brotli_enabled();
		$min = fractal_zip::skip_brotli_if_zstd_earlystop_min_inner_bytes();
		if($speedMode && $zstdTriggersEarlyStop && !$alwaysTryBr && ($min <= 0 || $innerLen >= $min)) {
			// Huge-mode=full explicitly asks for brotli on large inners; do not let zstd early-stop skip it.
			$allowBrotliDespiteZstdEarlyStop = $autoTextlikeFzbBrotliFull || ($brotliHugeMode === 'full');
			if(!$allowBrotliDespiteZstdEarlyStop) {
				$canTryBrotli = false;
			}
		}
	}
	if($brotliFirstForkPid !== null && (!$canTryBrotli || !$slowOuterAllowed)) {
		fractal_zip_encode_pipeline::speculative_outer_finalize_first_brotli($brotliFirstForkPid, $brotliFirstForkTd, true);
		$brotliFirstForkPid = null;
		$brotliFirstForkTd = null;
	}
	if($brotliProbeForkPid !== null && (!$canTryBrotli || !$slowOuterAllowed)) {
		fractal_zip_encode_pipeline::speculative_outer_finalize_huge_probe_brotli($brotliProbeForkPid, $brotliProbeForkTd, true);
		$brotliProbeForkPid = null;
		$brotliProbeForkTd = null;
	}
	$outerStep('after_zstd_brotli_skip_gate');

	if($canTryBrotli && $slowOuterAllowed) {
		$this->outer_codec_reuse_tmp_ensure($string, $innerLen, $fzCodecTmpIn, $fzCodecTmpEnsureDone);
		$brotliQ = $fzAdaptiveOuterFastBrotliQ;
		if(!$brotliHugeProbe) {
			if($brotliFirstForkPid !== null) {
				$brotliBlob = fractal_zip_encode_pipeline::speculative_outer_finalize_first_brotli($brotliFirstForkPid, $brotliFirstForkTd, false);
				$brotliFirstForkPid = null;
				$brotliFirstForkTd = null;
				if($brotliBlob !== null) {
					fractal_zip::message_once('brotli available (' . $brotliExe . '). Outer container also compares brotli for each .fzc.');
				}
			}
			if($brotliBlob === null) {
				$brotliBlob = $this->outer_brotli_blob($brotliExe, $string, $brotliQ, null, null, $fzCodecTmpIn);
				if($brotliBlob !== null) {
					fractal_zip::message_once('brotli available (' . $brotliExe . '). Outer container also compares brotli for each .fzc.');
				}
			}
			$outerStep('after_brotli_first_outer');
		} else {
			// Probe first (very fast), then decide whether full brotli is worth it.
			$probeBlob = null;
			if($brotliProbeForkPid !== null) {
				$probeBlob = fractal_zip_encode_pipeline::speculative_outer_finalize_huge_probe_brotli($brotliProbeForkPid, $brotliProbeForkTd, false);
				$brotliProbeForkPid = null;
				$brotliProbeForkTd = null;
			}
			if($probeBlob === null) {
				$probeQ = fractal_zip::brotli_huge_probe_quality();
				$probeBlob = $this->outer_brotli_blob($brotliExe, $string, $probeQ, 2.5, null, $fzCodecTmpIn);
			}
			if($probeBlob !== null) {
				$probeLen = strlen($probeBlob);
				$slack = fractal_zip::brotli_huge_trigger_slack_bytes();
				// If probe is competitive with current best, run full brotli; else skip.
				if($probeLen < $bestLen + $slack || fractal_zip::force_brotli_enabled()) {
					$hugeTo = fractal_zip::brotli_huge_full_timeout_sec();
					$brotliBlob = $this->outer_brotli_blob($brotliExe, $string, 3, $hugeTo, null, $fzCodecTmpIn);
				} else {
					// Still allow accepting probe directly if it already wins.
					if($probeLen < $bestLen) {
						$brotliBlob = $probeBlob;
					}
				}
			}
			if($brotliBlob !== null) {
				fractal_zip::message_once('brotli available (' . $brotliExe . '). Outer container also compares brotli for each .fzc.');
			}
		}
		$outerStep('after_brotli_outer_complete');
	}
	$bestCodec = 'gzip';
	$bestBlob = $bestGzipBlob !== null ? $bestGzipBlob : $string;
	$bestLen = $bestGzipBlob !== null ? strlen($bestGzipBlob) : PHP_INT_MAX;
	if($zstdBlob !== null) {
		$zstdL = strlen($zstdBlob);
		if($zstdL > 0 && fractal_zip::outer_candidate_beats_current('zstd', $zstdL, $bestCodec, $bestLen)) {
			$bestCodec = 'zstd';
			$bestBlob = $zstdBlob;
			$bestLen = $zstdL;
		}
	}
	if($brotliBlob !== null) {
		$brLen = strlen($brotliBlob);
		$accept = ($brLen > 0 && fractal_zip::outer_candidate_beats_current('brotli', $brLen, $bestCodec, $bestLen));
		// In probe mode, acceptance already handled by competitive trigger; keep normal accept rule.
		if($accept) {
		$bestCodec = 'brotli';
		$bestBlob = $brotliBlob;
			$bestLen = $brLen;
		}
	}
	$outerStep('after_fast_codec_merge');
	if ($outerStopAfter === fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE) {
		if ($predictForkPid !== null) {
			fractal_zip_encode_pipeline::speculative_outer_prediction_finalize($predictForkPid, $predictForkTd, false);
			$predictForkPid = null;
			$predictForkTd = null;
		}
		$outerStep('folder_wire_fast_shootout_complete');
		if (!fractal_zip::allow_outer_expansion() && $bestLen >= $innerLen) {
			if ($fzCodecTmpIn !== null && is_file($fzCodecTmpIn)) {
				unlink($fzCodecTmpIn);
			}
			fractal_zip::$last_outer_codec = 'store';

			return $string;
		}
		if ($fzCodecTmpIn !== null && is_file($fzCodecTmpIn)) {
			unlink($fzCodecTmpIn);
		}
		fractal_zip::$last_zpaq_method = null;
		fractal_zip::$last_outer_codec = $bestCodec;

		return $bestBlob;
	}
	$strongTinyBrotliOuter = false;
	if($speedMode && $bestCodec === 'brotli' && $fzbHuge && $outerTextlike && $innerLen > 0) {
		$skipCap = fractal_zip::skip_slow_outers_after_brotli_max_inner_bytes();
		$ratioNum = fractal_zip::skip_slow_outers_after_brotli_inner_div();
		if($skipCap > 0 && $innerLen <= $skipCap && $bestLen * $ratioNum < $innerLen) {
			$strongTinyBrotliOuter = true;
		}
	}
	// Optional: xz for huge payloads (slow but can win bytes). Balanced trigger: only try if current best is still "close" to gzip.
	$ranOuterXzAttempt = false;
	if($isHuge && $xzExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_XZ') && !$strongTinyBrotliOuter) {
		$ratio = fractal_zip::xz_trigger_ratio();
		$shouldTryRatio = ($gzLen !== PHP_INT_MAX && $gzLen > 0 && ((float)$bestLen / (float)$gzLen) >= $ratio);
		$skipXzBundle = fractal_zip::skip_xz_bundle_huge_enabled();
		$allowBundleXz = ($fzbHuge && !$skipXzBundle);
		// Bytes-first: allow xz on huge FZB* unified inners (LZMA often edges zstd on mixed text, e.g. test_files69).
		// FRACTAL_ZIP_SPEED=1 restores a 4 MiB gate unless FRACTAL_ZIP_SKIP_XZ_LITERAL_HUGE_MIN_INNER_BYTES is set explicitly.
		$skipXzLitHugeMin = fractal_zip::skip_xz_literal_huge_min_inner_bytes_effective();
		if($allowBundleXz && $seven !== null && $skipXzLitHugeMin > 0 && $innerLen >= $skipXzLitHugeMin) {
			$allowBundleXz = false;
		}
		$forceXz = fractal_zip::force_xz_enabled();
		// Bytes-first: ratio-gated xz like FRACTAL_ZIP_DEEP=1; huge FZB* inner: try xz even when zstd crushed gzip (no ratio gate).
		// Speed mode: keep xz behind DEEP or bundle path unless FRACTAL_ZIP_FORCE_XZ=1.
		$tryXz = $forceXz || $allowBundleXz || (($deepMode || !$speedMode) && $shouldTryRatio);
		if($tryXz) {
			$ranOuterXzAttempt = true;
			$outerStep('before_outer_xz');
			$this->outer_codec_reuse_tmp_ensure($string, $innerLen, $fzCodecTmpIn, $fzCodecTmpEnsureDone);
			$xzLevel = fractal_zip::xz_level();
			$xt = fractal_zip::xz_timeout_sec();
			$xzOverlapPid = null;
			$xzOverlapTd = null;
			if($predictForkPid !== null) {
				$ox = array('pid' => null, 'td' => null);
				if(fractal_zip_encode_pipeline::speculative_outer_xz_overlap_predict_enabled()) {
					$ox = fractal_zip_encode_pipeline::speculative_outer_xz_fork_overlap_predict_begin($xzExe, $string, $xzLevel, $xt, $fzCodecTmpIn, true);
				}
				if(isset($ox['pid']) && $ox['pid'] !== null && (int) $ox['pid'] > 0) {
					$xzOverlapPid = (int) $ox['pid'];
					$xzOverlapTd = $ox['td'];
				}
			}
			if($predictForkPid !== null) {
				$predFam = fractal_zip_encode_pipeline::speculative_outer_prediction_finalize($predictForkPid, $predictForkTd, false);
				$predictForkPid = null;
				$predictForkTd = null;
				if($predFam !== null && $predFam !== '') {
					$outerPredFamily = $predFam;
				}
			}
			$xzBlob = null;
			if($xzOverlapPid !== null && $xzOverlapTd !== null) {
				$xzBlob = fractal_zip_encode_pipeline::speculative_outer_xz_fork_overlap_predict_join($xzOverlapPid, $xzOverlapTd);
			}
			if($xzBlob === null || $xzBlob === '') {
				$xzBlob = $this->outer_xz_blob($xzExe, $string, $xzLevel, $xt, $fzCodecTmpIn);
			}
			if($xzBlob !== null) {
				$xzL = strlen($xzBlob);
				if($xzL > 0 && fractal_zip::outer_candidate_beats_current('xz', $xzL, $bestCodec, $bestLen)) {
					$bestCodec = 'xz';
					$bestBlob = $xzBlob;
					$bestLen = $xzL;
				}
			}
			$outerStep('after_outer_xz');
		}
	}
	if(!$ranOuterXzAttempt) {
		$outerSkip('skipped_outer_xz');
	}
	if($predictForkPid !== null) {
		$outerStep('before_outer_prediction_finalize_remainder');
		$predFam = fractal_zip_encode_pipeline::speculative_outer_prediction_finalize($predictForkPid, $predictForkTd, false);
		$predictForkPid = null;
		$predictForkTd = null;
		if($predFam !== null && $predFam !== '') {
			$outerPredFamily = $predFam;
		}
		$outerStep('after_outer_prediction_finalize_remainder');
	}
	$outerStep('before_slow_outer_sweep_gates');
	// Gzip-margin hint trims slow outers only in speed mode. Bytes-first phase 4 runs the full 7z/arc/zpaq tournament.
	$skipSlowOuterSweep = false;
	if($speedMode && $bestLen < $gzLen) {
		$improve = $gzLen - $bestLen;
		$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
	}
	// Optional speed trade: skip 7z/arc when zstd/brotli already beats gzip by a margin. Default off (bytes-first).
	$skipSlowFastWin = fractal_zip::skip_slow_if_fast_win_enabled();
	if($speedMode && !$skipSlowOuterSweep && $skipSlowFastWin && $bestCodec !== 'gzip' && $bestLen < $gzLen) {
		$maxInner = fractal_zip::skip_slow_if_fast_win_max_inner_bytes();
		$minPct = fractal_zip::skip_slow_if_fast_win_min_pct();
		if(($maxInner <= 0 || $innerLen <= $maxInner) && $gzLen > 0) {
			$improvePct = ((float)($gzLen - $bestLen)) / (float)$gzLen;
			if($improvePct >= $minPct) {
				$skipSlowOuterSweep = true;
			}
		}
	}
	// Huge compressible inners: zstd/brotli fast-win early stop skips arc/7z; re-open sweep only for very large inners (CSV/bundle scale), not midsize fractal outputs.
	$minSlowSweep = fractal_zip::huge_slow_outer_min_inner_bytes();
	// Re-open slow 7z/arc exploration when zstd/brotli wins on *text-like* huge literals (FZB or unified-stream non-FZB:
	// CSV / highly repetitive text — PPMd/LZMA sometimes beats brotli by a few bytes). Binary-like FZB keeps separate gates.
	if($autoTextlikeFzbBrotliFull && !$speedMode && $slowOuterAllowed && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') && $innerLen >= $minSlowSweep && ($bestCodec === 'zstd' || $bestCodec === 'brotli') && !$strongTinyBrotliOuter) {
		$skipSlowOuterSweep = false;
	}
	// Bytes-first: xz can crush gzip and trigger early-stop before 7z; LZMA2/PPMd sometimes edges xz on huge FZB/unified inners (e.g. test_files35 BMP).
	if(!$speedMode && $slowOuterAllowed && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') && $innerLen >= $minSlowSweep && $bestCodec === 'xz' && $try7z && $seven !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_7Z') && $innerLen >= $min7zInner) {
		$skipSlowOuterSweep = false;
	}
	// Midsize text-like: zstd/brotli can beat gzip by the small early-stop margin and skip 7z/arc; slow sweep can still help (e.g. test_files74–76). Cap at 256 KiB inner so multi‑MiB HTML inners (test_files55) are not forced through 7z/arc on every zstd win — the ≥512 KiB re-open path above covers those.
	$midsizeTextSlowMax = 262144;
	if($skipSlowOuterSweep && $speedMode && $outerTextlike && $slowOuterAllowed && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') && $innerLen >= $min7zInner && $innerLen < $minSlowSweep && $innerLen < $midsizeTextSlowMax) {
		$skipSlowOuterSweep = false;
	}
	// Midsize non–text-like unified inners (numeric logs, protobuf, etc.): the tiny gzip-improvement early-stop can hide
	// 7z/PPMd wins when outerTextlike is false. Default cap (512 KiB) can exceed text midsize (256 KiB); both stay below huge_slow_outer_min_inner_bytes so huge HTML (test_files55) uses the ≥512 KiB re-open path only.
	$midsizeBinarySlowMax = fractal_zip::outer_midsize_binary_reopen_max_inner_bytes();
	if($skipSlowOuterSweep && $speedMode && !$outerTextlike && $slowOuterAllowed && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') && $midsizeBinarySlowMax > 0 && $innerLen >= $min7zInner && $innerLen <= $midsizeBinarySlowMax && $innerLen < $minSlowSweep) {
		$skipSlowOuterSweep = false;
	}
	$outerStep('after_slow_outer_sweep_gates');
	$outerStep('before_outer_ratchet');
	$outerRatchet = fractal_zip::outer_compression_ratchet_level($innerLen, $gzLen, $speedMode, $outerPredFamily);
	// Ratchet 0 (tiny inners): always run slow outer exploration — do not trim just because fast codecs beat gzip.
	if($skipSlowOuterSweep && $outerRatchet === 0) {
		$skipSlowOuterSweep = false;
	}
	// Layered prediction favors zpaq/7z/arc — try those lanes unless ratchet‑2 explicitly trims heavy slow codecs (huge near‑random + zpaq).
	if($skipSlowOuterSweep && $outerRatchet < 2 && $outerPredFamily !== null && ($outerPredFamily === 'zpaq' || $outerPredFamily === '7z' || $outerPredFamily === 'arc')) {
		$skipSlowOuterSweep = false;
	}
	// Ultra runs the full slow outer tournament (incl. layered predict lanes) — must clear gzip-margin hint before zpaq/7z/arc lanes.
	if(fractal_zip::ultra_compression_enabled()) {
		$skipSlowOuterSweep = false;
	}
	$outerStep('after_outer_ratchet');

	// Full brotli refinement runs after predict lanes (rollup 4.3): Q10/Q11 + lgwin when layered prediction’s #1 family is brotli ({@see fractal_zip_outer_predict_mapped_winner}), or fallbacks when prediction names arc/zpaq (see {@see brotli_full_outer_arc_textlike_max_inner_bytes}, {@see brotli_full_outer_arc_zpaq_non_textlike_max_inner_bytes}) or zstd/xz ({@see brotli_full_outer_zstd_xz_textlike_max_inner_bytes}), or when the fast tier already leads with brotli (Q1) — otherwise we would ship medium Q3 or fast Q1 as the final outer. Medium tier never runs if full tier runs (test_files13, test_files74–76).
	$brArcTxtMax = fractal_zip::brotli_full_outer_arc_textlike_max_inner_bytes();
	$brArcNonTxtMax = fractal_zip::brotli_full_outer_arc_zpaq_non_textlike_max_inner_bytes();
	$brZstdXzTxtMax = fractal_zip::brotli_full_outer_zstd_xz_textlike_max_inner_bytes();
	$arcZpaqFullBrotliOk = ($outerPredFamily === 'arc' || $outerPredFamily === 'zpaq')
		&& $innerLen > 0 && $brArcTxtMax > 0 && $innerLen <= $brArcTxtMax
		&& ($outerTextlike || ($brArcNonTxtMax > 0 && $innerLen <= $brArcNonTxtMax));
	$willRunBrotliFullOuter = !$speedMode && $canTryBrotli && $brotliExe !== null
		&& $slowOuterAllowed
		&& ($outerPredFamily === 'brotli'
			|| $arcZpaqFullBrotliOk
			|| (($outerPredFamily === 'zstd' || $outerPredFamily === 'xz')
				&& $outerTextlike && $innerLen > 0 && $brZstdXzTxtMax > 0 && $innerLen <= $brZstdXzTxtMax)
			|| ($bestCodec === 'brotli' && $innerLen > 0));
	$fzAdaptiveMediumBrotliQ = 3;
	if(!$brotliHugeProbe && $canTryBrotli && $brotliExe !== null
		&& $slowOuterAllowed
		&& ($outerPredFamily === 'brotli' || $bestCodec === 'brotli')
		&& !$willRunBrotliFullOuter) {
		$outerStep('before_brotli_medium_outer');
		$this->outer_codec_reuse_tmp_ensure($string, $innerLen, $fzCodecTmpIn, $fzCodecTmpEnsureDone);
		$brMed = $this->outer_brotli_blob($brotliExe, $string, $fzAdaptiveMediumBrotliQ, null, null, $fzCodecTmpIn);
		if($brMed !== null) {
			$medL = strlen($brMed);
			if($medL > 0 && fractal_zip::outer_candidate_beats_current('brotli', $medL, $bestCodec, $bestLen)) {
				$bestCodec = 'brotli';
				$bestBlob = $brMed;
				$bestLen = $medL;
			}
		}
		$outerStep('after_brotli_medium_outer');
	}

	// Slow candidates only if we didn't already get a clear win.
	$canTry7z = ($try7z && $seven !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_7Z') && $innerLen >= $min7zInner);
	$ranOuterZpaqPredictLane = false;
	$ranOuter7zPredictSweep = false;
	$ranOuterArcPredictLane = false;
	$arcBefore7zForWave = fractal_zip::arc_before_7z_enabled();
	$waveWouldZpaq = !$speedMode && $zpaqExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZPAQ') && $zpaqInnerEligible && $slowOuterAllowed;
	$waveWould7z = !$skipSlowOuterSweep && !$ranOuter7zPredictSweep && $canTry7z;
	$waveWouldArc = !$skipSlowOuterSweep && !$ranOuterArcPredictLane && (!$arcBefore7zForWave) && $canTryArc && $slowOuterAllowed;
	$waveSlowCodecKinds = (int) $waveWould7z + (int) $waveWouldArc + (int) $waveWouldZpaq;
	$parallelSlowOuterWaveOn = fractal_zip_encode_pipeline::parallel_slow_outer_wave_enabled();
	$deferSlowPredictLanesToWave = fractal_zip::defer_slow_predict_lanes_to_parallel_wave_enabled();
	$parallelSlowOuterForkInnerOk = $parallelSlowOuterWaveOn && ($parallelOuterForkMinInner <= 0 || $innerLen >= $parallelOuterForkMinInner);
	$deferSlowOuterForkBundle = $deferSlowPredictLanesToWave && $parallelSlowOuterForkInnerOk;
	$deferZpaqPredictToParallelWave = $deferSlowOuterForkBundle
		&& ($outerPredFamily === 'zpaq')
		&& $waveWouldZpaq
		&& $waveSlowCodecKinds >= 2;
	if(!$deferZpaqPredictToParallelWave && !$skipSlowOuterSweep && $outerPredFamily === 'zpaq' && $waveWouldZpaq) {
		$ranOuterZpaqPredictLane = true;
		$outerStep('before_outer_zpaq_predict_lane');
		$zpaqPredictBlob = $this->outer_zpaq_blob($zpaqExe, $string);
		if($zpaqPredictBlob !== null) {
			$zpPredL = strlen($zpaqPredictBlob);
			if($zpPredL > 0 && fractal_zip::outer_candidate_beats_current('zpaq', $zpPredL, $bestCodec, $bestLen)) {
				$bestCodec = 'zpaq';
				$bestBlob = $zpaqPredictBlob;
				$bestLen = $zpPredL;
			}
		}
		if($speedMode && $bestLen < $gzLen) {
			$improve = $gzLen - $bestLen;
			$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
		}
		$outerStep('after_outer_zpaq_predict_lane');
	}
	// After predict lanes: zpaq still counts toward slow wave iff base eligibility holds and zpaq lane did not already run.
	$waveWouldZpaqAfterPredictLanes = !$ranOuterZpaqPredictLane && $waveWouldZpaq;
	$waveWould7zAfterZpaqLane = !$skipSlowOuterSweep && !$ranOuter7zPredictSweep && $canTry7z;
	$waveWouldArcAfterZpaqLane = !$skipSlowOuterSweep && !$ranOuterArcPredictLane && (!$arcBefore7zForWave) && $canTryArc && $slowOuterAllowed;
	$slowKindsAfterZpaqLane = (int) $waveWould7zAfterZpaqLane + (int) $waveWouldArcAfterZpaqLane + (int) $waveWouldZpaqAfterPredictLanes;
	$defer7zPredictToParallelWave = $deferSlowOuterForkBundle
		&& ($outerPredFamily === '7z')
		&& $waveWould7zAfterZpaqLane
		&& $slowKindsAfterZpaqLane >= 2;
	if(!$defer7zPredictToParallelWave && $outerPredFamily === '7z' && !$speedMode && $slowOuterAllowed && $waveWould7zAfterZpaqLane) {
		$ranOuter7zPredictSweep = true;
		$outerStep('before_outer_7z_predict_lane');
		$sevenPredictBlob = $this->outer_7z_best_blob($seven, $string, $innerLen, $bestLen - 1, $fzbHuge);
		if($sevenPredictBlob !== null && $sevenPredictBlob !== false) {
			$s7pL = strlen($sevenPredictBlob);
			if($s7pL > 0 && fractal_zip::outer_candidate_beats_current('7z', $s7pL, $bestCodec, $bestLen)) {
				$bestCodec = '7z';
				$bestBlob = $sevenPredictBlob;
				$bestLen = $s7pL;
			}
		}
		if($speedMode && $bestLen < $gzLen) {
			$improve = $gzLen - $bestLen;
			$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
		}
		$outerStep('after_outer_7z_predict_lane');
	}
	$waveWould7zAfter7zLane = !$skipSlowOuterSweep && !$ranOuter7zPredictSweep && $canTry7z;
	$waveWouldArcAfter7zLane = !$skipSlowOuterSweep && !$ranOuterArcPredictLane && (!$arcBefore7zForWave) && $canTryArc && $slowOuterAllowed;
	$slowKindsAfter7zLane = (int) $waveWould7zAfter7zLane + (int) $waveWouldArcAfter7zLane + (int) $waveWouldZpaqAfterPredictLanes;
	$deferArcPredictToParallelWave = $deferSlowOuterForkBundle
		&& ($outerPredFamily === 'arc')
		&& $waveWouldArcAfter7zLane
		&& $slowKindsAfter7zLane >= 2;
	if(!$deferArcPredictToParallelWave && !$skipSlowOuterSweep && $outerPredFamily === 'arc' && $canTryArc && $slowOuterAllowed) {
		$ranOuterArcPredictLane = true;
		$outerStep('before_outer_arc_predict_lane');
		$arcPredictBlob = $this->outer_arc_blob($arcExe, $string);
		if($arcPredictBlob !== null) {
			$arcPL = strlen($arcPredictBlob);
			if($arcPL > 0 && fractal_zip::outer_candidate_beats_current('arc', $arcPL, $bestCodec, $bestLen)) {
				$bestCodec = 'arc';
				$bestBlob = $arcPredictBlob;
				$bestLen = $arcPL;
			}
		}
		if($speedMode && $bestLen < $gzLen) {
			$improve = $gzLen - $bestLen;
			$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
		}
		$outerStep('after_outer_arc_predict_lane');
	}
	if($willRunBrotliFullOuter) {
		$outerStep('before_brotli_full_outer');
		$this->outer_codec_reuse_tmp_ensure($string, $innerLen, $fzCodecTmpIn, $fzCodecTmpEnsureDone);
		$fullQ = $this->adaptive_compress_compute_first_brotli_quality($innerLen, $speedMode, $isHuge, $outerTextlike, $bundleInnerFzws, $autoTextlikeFzbBrotliFull, $mergedFlacBundleHuge, $ultraSingleTxtRawSweep, $brotliHugeMode);
		$ref = $this->adaptive_compress_brotli_outer_full_quality_lgwin_refine(
			$this,
			$brotliExe,
			$string,
			$fullQ,
			$bundleInnerFzws,
			$ultraSingleTxtRawSweep,
			$outerTextlike,
			$speedMode,
			$fzCodecTmpIn
		);
		if($ref !== null && $ref !== '') {
			$rL = strlen($ref);
			if($rL > 0 && $rL < $bestLen) {
				$bestCodec = 'brotli';
				$bestBlob = $ref;
				$bestLen = $rL;
			}
		}
		$outerStep('after_brotli_full_outer');
	}
	if(fractal_zip::outer_prediction_debug_enabled()) {
		fprintf(STDERR, "[fz outer_predict] family=%s ratchet=%d inner_len=%d zpaq_early_lane=%s 7z_early_lane=%s arc_early_lane=%s skipped_gzip_gate=%s skip_slow_sweep=%s\n", $outerPredFamily === null ? '—' : $outerPredFamily, $outerRatchet, $innerLen, $ranOuterZpaqPredictLane ? '1' : '0', $ranOuter7zPredictSweep ? '1' : '0', $ranOuterArcPredictLane ? '1' : '0', $outerPredictSkippedCompressibility ? '1' : '0', $skipSlowOuterSweep ? '1' : '0');
	}
	// Predict lanes reset skipSlowOuterSweep from gzip margin vs best bytes; ultra keeps staging trials open until the full 7z sweep.
	if(fractal_zip::ultra_compression_enabled()) {
		$skipSlowOuterSweep = false;
	}
	// Balanced huge-payload fallback (brotli skipped on huge): try ONE fast 7z lzma2 attempt before arc/7z sweeps.
	$hugeTry7zOnce = fractal_zip::huge_try_7z_once_enabled();
	// Optional: try arc before 7z (arc can beat gzip fast enough to skip slower 7z).
	// Default off: arc-before-7z tends to waste time on most corpora.
	$arcBefore7z = fractal_zip::arc_before_7z_enabled();
	$hugeOnceBroOk = ($brotliHugeMode !== 'full' && $forceBrotli === false);
	$eligibleHugeOnce = !$skipSlowOuterSweep && !$ranOuter7zPredictSweep && $hugeTry7zOnce && $isHuge && !$fzbHuge && $seven !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_7Z') && $hugeOnceBroOk;
	$eligibleArcBefore = !$skipSlowOuterSweep && !$ranOuterArcPredictLane && $arcBefore7z && $canTryArc && $slowOuterAllowed;
	$hugeArcWave = null;
	if(!$speedMode && $eligibleHugeOnce && $eligibleArcBefore
		&& ($parallelOuterForkMinInner <= 0 || $innerLen >= $parallelOuterForkMinInner)
		&& fractal_zip_encode_pipeline::parallel_huge_once_arc_before_wave_enabled()) {
		$hugeArcWave = fractal_zip_encode_pipeline::parallel_huge_once_arc_before_wave_trials($string, true);
	}
	$ranOuter7zHugeOnce = false;
	$ranOuterArcBefore7z = false;
	if($hugeArcWave !== null) {
		$ranOuter7zHugeOnce = true;
		$ranOuterArcBefore7z = true;
		$outerStep('before_outer_7z_huge_once');
		$outerStep('before_outer_arc_before_7z');
		$one = $hugeArcWave[0] ?? null;
		if($one !== null) {
			$oneL = strlen($one);
			if($oneL > 0 && fractal_zip::outer_candidate_beats_current('7z', $oneL, $bestCodec, $bestLen)) {
				$bestCodec = '7z';
				$bestBlob = $one;
				$bestLen = $oneL;
			}
		}
		if($speedMode && $bestLen < $gzLen) {
			$improve = $gzLen - $bestLen;
			$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
		}
		$outerStep('after_outer_7z_huge_once');
		$arcBlob = $hugeArcWave[1] ?? null;
		if($arcBlob !== null) {
			$arcL = strlen($arcBlob);
			if($arcL > 0 && fractal_zip::outer_candidate_beats_current('arc', $arcL, $bestCodec, $bestLen)) {
				$bestCodec = 'arc';
				$bestBlob = $arcBlob;
				$bestLen = $arcL;
			}
		}
		if($speedMode && $bestLen < $gzLen) {
			$improve = $gzLen - $bestLen;
			$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
		}
		$outerStep('after_outer_arc_before_7z');
	} else {
		if(!$skipSlowOuterSweep && !$ranOuter7zPredictSweep && $hugeTry7zOnce && $isHuge && !$fzbHuge && $seven !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_7Z')) {
			if($hugeOnceBroOk) {
				$ranOuter7zHugeOnce = true;
				$outerStep('before_outer_7z_huge_once');
				$one = $this->outer_7z_lzma2_blob($seven, $string);
				if($one !== null) {
					$oneL = strlen($one);
					if($oneL > 0 && fractal_zip::outer_candidate_beats_current('7z', $oneL, $bestCodec, $bestLen)) {
						$bestCodec = '7z';
						$bestBlob = $one;
						$bestLen = $oneL;
					}
				}
				if($speedMode && $bestLen < $gzLen) {
					$improve = $gzLen - $bestLen;
					$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
				}
				$outerStep('after_outer_7z_huge_once');
			}
		}
		if(!$skipSlowOuterSweep && !$ranOuterArcPredictLane && $arcBefore7z && $canTryArc && $slowOuterAllowed) {
			$ranOuterArcBefore7z = true;
			$outerStep('before_outer_arc_before_7z');
			$arcBlob = $this->outer_arc_blob($arcExe, $string);
			if($arcBlob !== null) {
				$arcL = strlen($arcBlob);
				if($arcL > 0 && fractal_zip::outer_candidate_beats_current('arc', $arcL, $bestCodec, $bestLen)) {
					$bestCodec = 'arc';
					$bestBlob = $arcBlob;
					$bestLen = $arcL;
				}
			}
			if($speedMode && $bestLen < $gzLen) {
				$improve = $gzLen - $bestLen;
				$skipSlowOuterSweep = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
			}
			$outerStep('after_outer_arc_before_7z');
		}
	}
	if(!$ranOuter7zHugeOnce) {
		$outerSkip('skipped_outer_7z_huge_once');
	}
	if(!$ranOuterArcBefore7z) {
		$outerSkip('skipped_outer_arc_before_7z');
	}
	if($autoTextlikeFzbBrotliFull && !$speedMode && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') && !$strongTinyBrotliOuter) {
		// Re-open slow outers for *text-like* huge literals (7z/PPMd vs brotli); same family as auto brotli-on-huge textlike.
		$skipSlowOuterSweep = false;
	}
	// Binary-like huge FZB: optional 7z skip for speed; bytes-first always runs the sweep.
	if($speedMode && $fzbHuge && !$outerTextlike && !fractal_zip::ultra_compression_enabled() && fractal_zip::fzb_binary_skip_7z_sweep_enabled() && $bestGzipBlob !== null) {
		$skipSlowOuterSweep = true;
	}
	if(fractal_zip::ultra_compression_enabled()) {
		$skipSlowOuterSweep = false;
	}
	$ranOuter7zBestSweep = false;
	$ranOuterArcAfter7z = false;
	$ranOuterZpaq = false;
	$waveKinds = array();
	if(!$skipSlowOuterSweep && !$ranOuter7zPredictSweep && $canTry7z) {
		$waveKinds[] = '7z';
	}
	if(!$skipSlowOuterSweep && !$ranOuterArcPredictLane && (!$arcBefore7z) && $canTryArc && $slowOuterAllowed) {
		$waveKinds[] = 'arc';
	}
	if($waveWouldZpaqAfterPredictLanes) {
		$waveKinds[] = 'zpaq';
	}
	// Layered prediction named a slow outer: run that codec's heavy trial only (not parallel/sequential 7z+arc+zpaq).
	if(!fractal_zip::ultra_compression_enabled()
		&& $outerPredFamily !== null
		&& ($outerPredFamily === 'zpaq' || $outerPredFamily === '7z' || $outerPredFamily === 'arc')
		&& in_array($outerPredFamily, $waveKinds, true)) {
		$waveKinds = array($outerPredFamily);
	}
	$wantSlowWave7z = in_array('7z', $waveKinds, true);
	$wantSlowWaveArc = in_array('arc', $waveKinds, true);
	$wantSlowWaveZpaq = in_array('zpaq', $waveKinds, true);
	$waveOut = null;
	if(count($waveKinds) >= 2
		&& $parallelSlowOuterForkInnerOk) {
		$outerStep('before_parallel_slow_outer_wave');
		$waveOut = fractal_zip_encode_pipeline::parallel_slow_outer_wave_trials($waveKinds, $string, $innerLen, $bestLen, $fzbHuge, true);
		$outerStep('after_parallel_slow_outer_wave_trials');
	}
	if($waveOut !== null) {
		$ranOuter7zBestSweep = in_array('7z', $waveKinds, true);
		$ranOuterArcAfter7z = in_array('arc', $waveKinds, true);
		$ranOuterZpaq = in_array('zpaq', $waveKinds, true);
		foreach(array('7z', 'arc', 'zpaq') as $wk) {
			if(!in_array($wk, $waveKinds, true)) {
				continue;
			}
			$row = $waveOut[$wk] ?? null;
			if($row === null || !is_array($row)) {
				continue;
			}
			$cLen = (int) ($row['len'] ?? 0);
			if($cLen <= 0) {
				continue;
			}
			$cBlob = $row['blob'];
			$cCodec = (string) ($row['codec'] ?? $wk);
			if($wk === 'zpaq' && isset($row['zpaq']) && is_string($row['zpaq'])) {
				fractal_zip::$last_zpaq_method = $row['zpaq'];
			}
			if(fractal_zip::outer_candidate_beats_current($cCodec, $cLen, $bestCodec, $bestLen)) {
				$bestCodec = $cCodec;
				$bestBlob = $cBlob;
				$bestLen = $cLen;
			}
			if($wk === '7z') {
				$sevenBlob = $cBlob;
			}
			if($wk === 'arc') {
				$arcBlob = $cBlob;
			}
		}
		$outerStep('after_parallel_slow_outer_wave');
		if(!$ranOuter7zBestSweep) {
			$outerSkip('skipped_outer_7z_best_sweep');
		}
		if(!$ranOuterArcAfter7z) {
			$outerSkip('skipped_outer_arc_after_7z');
		}
		if(!$ranOuterZpaq) {
			$outerSkip('skipped_outer_zpaq');
		}
	} else {
		if($wantSlowWave7z && !$skipSlowOuterSweep && !$ranOuter7zPredictSweep && $canTry7z) {
			$ranOuter7zBestSweep = true;
			$outerStep('before_outer_7z_best_sweep');
			// Stop 7z method sweep once it beats current best.
			$sevenBlob = $this->outer_7z_best_blob($seven, $string, $innerLen, $bestLen - 1, $fzbHuge);
			if($sevenBlob !== null && $sevenBlob !== false) {
				$s7L = strlen($sevenBlob);
				if($s7L > 0 && fractal_zip::outer_candidate_beats_current('7z', $s7L, $bestCodec, $bestLen)) {
					$bestCodec = '7z';
					$bestBlob = $sevenBlob;
					$bestLen = $s7L;
				}
			}
			$outerStep('after_outer_7z_best_sweep');
		}
		if(!$ranOuter7zBestSweep) {
			$outerSkip('skipped_outer_7z_best_sweep');
		}
		// Outer choice: smallest blob wins among candidates we try — no inner-size gate skipping arc after 7z (that hid wins on
		// large text bundles). FRACTAL_ZIP_SKIP_ARC=1 still disables arc entirely.
		// If we didn't try arc yet, try it last.
		if($wantSlowWaveArc && !$skipSlowOuterSweep && !$ranOuterArcPredictLane && (!$arcBefore7z) && $canTryArc && $slowOuterAllowed) {
			$ranOuterArcAfter7z = true;
			$outerStep('before_outer_arc_after_7z');
			$arcBlob = $this->outer_arc_blob($arcExe, $string);
			if($arcBlob !== null) {
				$arcL2 = strlen($arcBlob);
				if($arcL2 > 0 && fractal_zip::outer_candidate_beats_current('arc', $arcL2, $bestCodec, $bestLen)) {
					$bestCodec = 'arc';
					$bestBlob = $arcBlob;
					$bestLen = $arcL2;
				}
			}
			$outerStep('after_outer_arc_after_7z');
		}
		if(!$ranOuterArcAfter7z) {
			$outerSkip('skipped_outer_arc_after_7z');
		}
		// zpaq outer (slow; strong on mixed/binary inners). After 7z/arc so fast codecs stay preferred unless zpaq wins on bytes.
		// Prescreen skips zstd/brotli on random-like payloads; zpaq can still win (e.g. OLE) — see outer_slow_codec_allowed_despite_prescreen.
		if($wantSlowWaveZpaq && $waveWouldZpaqAfterPredictLanes) {
			$ranOuterZpaq = true;
			$outerStep('before_outer_zpaq');
			$zpaqBlob = $this->outer_zpaq_blob($zpaqExe, $string);
			if($zpaqBlob !== null) {
				$zpL = strlen($zpaqBlob);
				if($zpL > 0 && fractal_zip::outer_candidate_beats_current('zpaq', $zpL, $bestCodec, $bestLen)) {
					$bestCodec = 'zpaq';
					$bestBlob = $zpaqBlob;
					$bestLen = $zpL;
				}
			}
			$outerStep('after_outer_zpaq');
		}
		if(!$ranOuterZpaq) {
			$outerSkip('skipped_outer_zpaq');
		}
	}
	if($seven === null) {
		fractal_zip::message_once('7-Zip not found (install 7-Zip or p7zip; add to PATH on Unix). Using gzip-9 for outer container.');
	} elseif($try7z && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_7Z')) {
		fractal_zip::message_once('7-Zip available (' . $seven . '). Outer container compares staged candidates vs gzip-9 for each .fzc.');
	}
	if($arcExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ARC')) {
		fractal_zip::message_once('FreeArc available (' . $arcExe . '). Outer container can also compare FreeArc for each .fzc.');
	}
	if($zpaqExe !== null && fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZPAQ')) {
		fractal_zip::message_once('zpaq available (' . $zpaqExe . '). Outer tournament uses zpaq on modest inners by default (see FRACTAL_ZIP_MIN_ZPAQ_INNER_BYTES / FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES).');
	}
	$outerStep('after_slow_outer_pass_complete');
	if(!fractal_zip::allow_outer_expansion() && $bestLen >= $innerLen) {
		if($fzCodecTmpIn !== null && is_file($fzCodecTmpIn)) {
			unlink($fzCodecTmpIn);
		}
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	if($fzCodecTmpIn !== null && is_file($fzCodecTmpIn)) {
		unlink($fzCodecTmpIn);
	}
	if($bestCodec !== 'zpaq') {
		fractal_zip::$last_zpaq_method = null;
	}
	fractal_zip::$last_outer_codec = $bestCodec;
	return $bestBlob;
	} finally {
		if($predictForkPid !== null) {
			if($rollupOuterSteps) {
				fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_sync_clock();
			}
			fractal_zip_encode_pipeline::speculative_outer_prediction_finalize($predictForkPid, $predictForkTd, true);
			if($rollupOuterSteps) {
				fractal_zip_encode_pipeline::pipeline_outer_step_rollup_zip_folder_segment('adaptive_compress_prediction_fork_discard_finalize');
			}
			$predictForkPid = null;
			$predictForkTd = null;
		}
	}
}

/**
 * True if bytes begin with a native zpaq stream header ({@code 7kSt} or {@code zPQ} + version byte ≥ 1).
 */
function zpaq_native_archive_stream_magic_p($bytes) {
	$bytes = (string) $bytes;
	$n = strlen($bytes);
	if($n < 4) {
		return false;
	}
	if(substr($bytes, 0, 4) === '7kSt') {
		return true;
	}
	return substr($bytes, 0, 3) === 'zPQ' && ord($bytes[3]) >= 1;
}

/**
 * Extract inner payload from bare zpaq archive bytes (no {@see fractal_zip::OUTER_ZPAQ_MAGIC} prefix).
 * Used when nested fractal blobs embed raw zpaq output (e.g. FZCD merged-fractal chunks).
 *
 * @return string|null inner bytes, or null if zpaq is missing or extraction fails
 */
function unpack_raw_zpaq_archive_bytes_to_inner_string($arcBytes) {
	$arcBytes = (string) $arcBytes;
	if(!$this->zpaq_native_archive_stream_magic_p($arcBytes)) {
		return null;
	}
	$zpaqExe = fractal_zip::zpaq_executable();
	if($zpaqExe === null) {
		return null;
	}
	$u = substr(md5(fractal_zip::hot_string_digest($arcBytes) . "\0zpaqraw\0" . (string) spl_object_id($this)), 0, 8);
	$arcPath = $this->program_path . DS . 'fzzpaqr_' . $u . '.zpaq';
	$outDir = $this->program_path . DS . 'fzzpaqr_' . $u . '_out';
	if(@file_put_contents($arcPath, $arcBytes) === false) {
		return null;
	}
	if(!is_dir($outDir)) {
		mkdir($outDir, 0755, true);
	}
	$prefix = $this->command_prefix_with_local_lib();
	$cmd = $prefix . fractal_zip::shell_quote_arg_cached($zpaqExe) . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' x ' . escapeshellarg($arcPath) . ' -to ' . escapeshellarg($outDir) . ' -force';
	exec($cmd . ' 2>/dev/null', $out, $ret);
	$inner = null;
	$preferred = $outDir . DS . 'inner.bin';
	$preferredAlt = $outDir . DS . 'i';
	if($ret === 0 && is_file($preferred)) {
		$inner = @file_get_contents($preferred);
		@unlink($preferred);
	} elseif($ret === 0 && is_file($preferredAlt)) {
		$inner = @file_get_contents($preferredAlt);
		@unlink($preferredAlt);
	}
	if($inner === null || $inner === '') {
		$dirList = is_dir($outDir) ? (scandir($outDir) ?: array()) : array();
		foreach($dirList as $f) {
			if($f === '.' || $f === '..') {
				continue;
			}
			$p = $outDir . DS . $f;
			if(is_file($p)) {
				$inner = @file_get_contents($p);
				@unlink($p);
				break;
			}
		}
	}
	if(is_file($arcPath)) {
		@unlink($arcPath);
	}
	foreach(is_dir($outDir) ? (scandir($outDir) ?: array()) : array() as $f) {
		if($f === '.' || $f === '..') {
			continue;
		}
		$p = $outDir . DS . $f;
		if(is_file($p)) {
			@unlink($p);
		}
	}
	if(is_dir($outDir)) {
		@rmdir($outDir);
	}
	if(!is_string($inner) || $inner === '') {
		return null;
	}
	return $inner;
}

function adaptive_decompress($string) {
	$zpaqMagic = fractal_zip::OUTER_ZPAQ_MAGIC;
	$zpaqMagicLen = strlen($zpaqMagic);
	if(strlen($string) >= $zpaqMagicLen && substr($string, 0, $zpaqMagicLen) === $zpaqMagic) {
		$zpaqExe = fractal_zip::zpaq_executable();
		if($zpaqExe === null) {
			fractal_zip::fatal_error('Container uses zpaq outer format but `zpaq` was not found on PATH (set FRACTAL_ZIP_ZPAQ).');
		}
		$rest = substr($string, $zpaqMagicLen);
		$inner = $this->unpack_raw_zpaq_archive_bytes_to_inner_string($rest);
		if($inner === null || $inner === '') {
			fractal_zip::fatal_error('Failed to extract inner payload from zpaq fractal_zip container.');
		}
		return $inner;
	}
	if($this->zpaq_native_archive_stream_magic_p($string)) {
		$zpaqExe = fractal_zip::zpaq_executable();
		if($zpaqExe === null) {
			fractal_zip::fatal_error('Container uses raw zpaq archive bytes but `zpaq` was not found on PATH (set FRACTAL_ZIP_ZPAQ).');
		}
		$inner = $this->unpack_raw_zpaq_archive_bytes_to_inner_string($string);
		if($inner === null || $inner === '') {
			fractal_zip::fatal_error('Failed to extract inner payload from raw zpaq fractal_zip container.');
		}
		return $inner;
	}
	if(strlen($string) >= 2 && $string[0] === '7' && $string[1] === 'z') {
		$seven = fractal_zip::seven_zip_executable();
		if($seven === null) {
			fractal_zip::fatal_error('Container is 7z format but no 7-Zip/p7zip binary was found. Install 7z and ensure it is on PATH (or on Windows in Program Files).');
		}
		$u = substr(md5(fractal_zip::hot_string_digest($string) . "\0fz7xd\0" . (string) spl_object_id($this)), 0, 8);
		$temp_container_path = $this->program_path . DS . 'fzext_' . $u . $this->fractal_zip_container_file_extension;
		$outDir = $this->program_path . DS . 'fzext_' . $u . '_out';
		file_put_contents($temp_container_path, $string);
		if(!is_dir($outDir)) {
			mkdir($outDir, 0755, true);
		}
		$cmd = fractal_zip::shell_quote_arg_cached($seven) . fractal_zip::seven_zip_mmt_shell_fragment_for_exec() . ' x ' . escapeshellarg($temp_container_path) . ' -aoa -o' . escapeshellarg($outDir);
		exec($cmd . ' 2>/dev/null', $output, $return);
		$dirList = is_dir($outDir) ? (scandir($outDir) ?: array()) : array();
		$inner = null;
		$fallbackInnerPath = null;
		if($return === 0) {
			foreach($dirList as $f) {
				if($f === '.' || $f === '..') {
					continue;
				}
				$p = $outDir . DS . $f;
				if(is_file($p)) {
					if(str_ends_with($p, $this->fractal_zip_file_extension)) {
						$inner = file_get_contents($p);
						unlink($p);
						break;
					}
					if($fallbackInnerPath === null) {
						$fallbackInnerPath = $p;
					}
				}
			}
			if($inner === null && $fallbackInnerPath !== null && is_file($fallbackInnerPath)) {
				$inner = file_get_contents($fallbackInnerPath);
				unlink($fallbackInnerPath);
			}
		}
		if(is_file($temp_container_path)) {
			unlink($temp_container_path);
		}
		foreach($dirList as $f) {
			if($f === '.' || $f === '..') {
				continue;
			}
			$p = $outDir . DS . $f;
			if(is_file($p)) {
				unlink($p);
			}
		}
		if(is_dir($outDir)) {
			rmdir($outDir);
		}
		if($inner === null) {
			fractal_zip::fatal_error('Failed to extract inner payload from 7z fractal_zip container.');
		}
		return $inner;
	}
	if(strlen($string) >= 4 && $string[0] === 'A' && $string[1] === 'r' && $string[2] === 'C' && $string[3] === chr(1)) {
		$arcExe = fractal_zip::freearc_executable();
		if($arcExe === null) {
			fractal_zip::fatal_error('Container is FreeArc format but `arc` was not found on PATH.');
		}
		$u = substr(md5(fractal_zip::hot_string_digest($string) . "\0fzarcd\0" . (string) spl_object_id($this)), 0, 8);
		$tempArcPath = $this->program_path . DS . 'fzarc_' . $u . '.arc';
		$outDir = $this->program_path . DS . 'fzarc_' . $u . '_out';
		file_put_contents($tempArcPath, $string);
		if(!is_dir($outDir)) {
			mkdir($outDir, 0755, true);
		}
		$cwd = getcwd();
		chdir($outDir);
		$prefix = $this->command_prefix_with_local_lib();
		$cmd = $prefix . fractal_zip::shell_quote_arg_cached($arcExe) . fractal_zip::library_arc_compress_mt_shell_fragment_for_exec() . ' x -y ' . escapeshellarg($tempArcPath);
		exec($cmd . ' 2>/dev/null', $output, $return);
		if($cwd !== false) {
			chdir($cwd);
		}
		$dirList = is_dir($outDir) ? (scandir($outDir) ?: array()) : array();
		$inner = null;
		if($return === 0) {
			foreach($dirList as $f) {
				if($f === '.' || $f === '..') {
					continue;
				}
				$p = $outDir . DS . $f;
				if(is_file($p)) {
					$inner = file_get_contents($p);
					unlink($p);
					break;
				}
			}
		}
		if(is_file($tempArcPath)) {
			unlink($tempArcPath);
		}
		foreach($dirList as $f) {
			if($f === '.' || $f === '..') {
				continue;
			}
			$p = $outDir . DS . $f;
			if(is_file($p)) {
				unlink($p);
			}
		}
		if(is_dir($outDir)) {
			rmdir($outDir);
		}
		if($inner === null) {
			fractal_zip::fatal_error('Failed to extract inner payload from FreeArc fractal_zip container.');
		}
		return $inner;
	}
	$bMagic = fractal_zip::OUTER_BROTLI_MAGIC;
	$bMagicLen = strlen($bMagic);
	if(strlen($string) >= $bMagicLen && substr($string, 0, $bMagicLen) === $bMagic) {
		$brotliExe = fractal_zip::brotli_executable();
		if($brotliExe === null) {
			fractal_zip::fatal_error('Container uses brotli outer format but `brotli` was not found on PATH.');
		}
		$rest = substr($string, $bMagicLen);
		$inner = $this->outer_brotli_decompress_pipe($brotliExe, $rest);
		if($inner === null) {
			fractal_zip::fatal_error('Failed to decompress brotli fractal_zip container.');
		}
		return $inner;
	}
	if(strlen($string) >= 4 && substr($string, 0, 4) === "\x28\xB5\x2F\xFD") {
		$zstdExe = fractal_zip::zstd_executable();
		if($zstdExe === null) {
			fractal_zip::fatal_error('Container is zstd format but `zstd` was not found on PATH.');
		}
		$inner = $this->outer_zstd_decompress_pipe($zstdExe, $string);
		if($inner === null) {
			fractal_zip::fatal_error('Failed to decompress zstd fractal_zip container.');
		}
		return $inner;
	}
	// xz magic: FD 37 7A 58 5A 00 — temp file + xz reading path (same pattern as try_stream_xz_decompress_to_temp_file).
	// Feeding the whole blob on stdin while only draining stdout after the write causes a classic proc_open
	// deadlock once pipe buffers fill (xz blocks on stdout, PHP blocks on stdin).
	if(strlen($string) >= 6 && substr($string, 0, 6) === "\xFD\x37\x7A\x58\x5A\x00") {
		$xzExe = fractal_zip::xz_executable();
		if($xzExe === null) {
			fractal_zip::fatal_error('Container is xz format but `xz` was not found on PATH.');
		}
		$u = substr(md5(fractal_zip::hot_string_digest($string) . "\0xzdd\0" . (string) spl_object_id($this)), 0, 16);
		$tmpBase = fractal_zip::outer_codec_temp_dir();
		$tempIn = $tmpBase . DS . 'fzXZdin_' . $u;
		$tempOut = $tmpBase . DS . 'fzXZdout_' . $u;
		if(@file_put_contents($tempIn, $string) === false) {
			fractal_zip::fatal_error('Failed to write xz container temp file.');
		}
		$cmd = fractal_zip::shell_quote_arg_cached($xzExe) . fractal_zip::library_xz_decompress_thread_shell_fragment_for_exec() . ' -d -c ' . escapeshellarg($tempIn);
		$pe = fractal_zip::proc_open_reusable_std_pipe_ends();
		$desc = array(0 => $pe['r'], 1 => array('file', $tempOut, 'wb'), 2 => $pe['w']);
		$out = null;
		if(function_exists('proc_open')) {
			$proc = @proc_open($cmd, $desc, $pipes, null);
			if(is_resource($proc)) {
				fclose($pipes[0]);
				stream_get_contents($pipes[2]);
				fclose($pipes[2]);
				if(proc_close($proc) === 0 && is_file($tempOut)) {
					$read = @file_get_contents($tempOut);
					if(is_string($read) && $read !== '') {
						$out = $read;
					}
				}
			}
		}
		@unlink($tempIn);
		@unlink($tempOut);
		if($out === null || $out === '') {
			fractal_zip::fatal_error('Failed to decompress xz fractal_zip container.');
		}
		return $out;
	}
	$unz = gzuncompress($string);
	if($unz !== false) {
		return $unz;
	}
	$inf = gzinflate($string);
	if($inf !== false) {
		return $inf;
	}
	return $string;
}

function escape($string) {
	$string = str_replace('{', 'XXX9o9left9o9XXX', $string);
	$string = str_replace('}', 'XXX9o9right9o9XXX', $string);
	$string = str_replace('|', 'XXX9o9mid9o9XXX', $string); // not currently necessary
	return $string;
}

function unescape($string) {
	$string = str_replace('XXX9o9left9o9XXX', '{', $string);
	$string = str_replace('XXX9o9right9o9XXX', '}', $string);
	$string = str_replace('XXX9o9mid9o9XXX', '|', $string); // not currently necessary
	return $string;
}

function filename_minus_extension($string) {
	return substr($string, 0, fractal_zip::strpos_last($string, '.'));
}

function file_extension($string) {
	return substr($string, fractal_zip::strpos_last($string, '.'));
}

public static function strpos_last($haystack, $needle) {
	//print('$haystack, $needle: ');var_dump($haystack, $needle);
	if(strlen($needle) === 0) {
		return false;
	}
	$len_haystack = strlen($haystack);
	$len_needle = strlen($needle);		
	$pos = strpos(strrev($haystack), strrev($needle));
	if($pos === false) {
		return false;
	}
	return $len_haystack - $pos - $len_needle;
}

function tagless($variable) {
	if(is_array($variable)) {
		if(fractal_zip::all_entries_are_arrays($variable)) {
			$tagless_array = array();
			foreach($variable as $index => $value) {
				$tagless_array[] = fractal_zip::tagless($value[0]);
			}
			if(sizeof($tagless_array) === 1) {
				return $tagless_array[0];
			}
			return $tagless_array;
		} else {
			return fractal_zip::tagless($variable[0]);
		}
		//fractal_zip::fatal_error('tagless() expects string input');
	}
	return preg_replace('/<[^<>]*>/is', '', $variable);
}

function var_dump_full() {
	$arguments_array = func_get_args();
	foreach($arguments_array as $index => $value) {
		$data_type = gettype($value);
		if($data_type == 'array') {
			$biggest_array_size = fractal_zip::get_biggest_sizeof($value);
			if($biggest_array_size > 2000) {
				ini_set('xdebug.var_display_max_children', '2000');
			} elseif($biggest_array_size > ini_get('xdebug.var_display_max_children')) {
				ini_set('xdebug.var_display_max_children', $biggest_array_size);
			}
		} elseif($data_type == 'string') {
			$biggest_string_size = strlen($value);
			if($biggest_string_size > 2000) {
				ini_set('xdebug.var_display_max_data', '10000');
			} elseif($biggest_string_size > ini_get('xdebug.var_display_max_data')) {
				ini_set('xdebug.var_display_max_data', $biggest_string_size);
			}
		} elseif($data_type == 'integer' || $data_type == 'float' || $data_type == 'chr' || $data_type == 'boolean' || $data_type == 'NULL') {
			// these are already compact enough
		} else {
			print('<span style="color: orange;">Unhandled data type in var_dump_full: ' . gettype($value) . '</span><br>');
		}
		var_dump($value);
	}
}

function get_biggest_sizeof($array, $biggest = 0) {
	if(sizeof($array) > $biggest) {
		$biggest = sizeof($array);
	}
	foreach($array as $index => $value) {
		if(is_array($value)) {
			$biggest = fractal_zip::get_biggest_sizeof($value, $biggest);
		}
	}
	return $biggest;
}

function density($substring, $string) {
	return substr_count($string, $substring);
}

function average($array) {
	$sum = 0;
	foreach($array as $value) {
		$sum += $value;
	}
	return $sum / sizeof($array);
}

function preg_escape($string) {
	return str_replace('/', '\/', preg_quote($string));
}

function fatal_error($message) {
	if(PHP_SAPI === 'cli' && defined('STDERR') && is_resource(STDERR)) {
		fwrite(STDERR, (string) $message . "\n");
		exit(1);
	}
	if (defined('FRACTAL_ZIP_WEB_JSON_API') && FRACTAL_ZIP_WEB_JSON_API) {
		while (ob_get_level() > 0) {
			@ob_end_clean();
		}
		if (!headers_sent()) {
			http_response_code(500);
			header('Content-Type: application/json; charset=UTF-8');
			header('X-Content-Type-Options: nosniff');
		}
		$out = json_encode(array(
			'ok' => false,
			'error' => (string) $message,
		), JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
		if ($out !== false) {
			echo $out;
		}
		exit;
	}
	print('<span style="color: red;">' . $message . '</span>');
	exit(0);
}

function fatal_error_once($string) {
	static $printed_strings = array();
	if(isset($printed_strings[$string])) {
		return true;
	}
	$printed_strings[$string] = true;
	if(PHP_SAPI === 'cli' && defined('STDERR') && is_resource(STDERR)) {
		fwrite(STDERR, (string) $string . "\n");
		exit(1);
	}
	if (defined('FRACTAL_ZIP_WEB_JSON_API') && FRACTAL_ZIP_WEB_JSON_API) {
		while (ob_get_level() > 0) {
			@ob_end_clean();
		}
		if (!headers_sent()) {
			http_response_code(500);
			header('Content-Type: application/json; charset=UTF-8');
			header('X-Content-Type-Options: nosniff');
		}
		$out = json_encode(array(
			'ok' => false,
			'error' => (string) $string,
		), JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
		if ($out !== false) {
			echo $out;
		}
		exit;
	}
	print('<span style="color: red;">' . $string . '</span>');
	exit(0);
}

function warning($message) {
	if(!fractal_zip::emit_html_trace()) {
		return;
	}
	print('<span style="color: orange;">' . $message . '</span><br>');
}

function warning_if($string, $count) {
	if($count > 1) {
		fractal_zip::warning($string);
	}
}

function warning_once($string) {
	static $printed_strings = array();
	if(isset($printed_strings[$string])) {
		return true;
	}
	$printed_strings[$string] = true;
	if(!fractal_zip::emit_html_trace()) {
		return true;
	}
	print('<span style="color: orange;">' . $string . '</span><br>');
	return true;
}

function message($message) {
	if(!fractal_zip::emit_html_trace()) {
		return;
	}
	print('<span>' . $message . '</span><br>');
}

function message_if($string, $count) {
	if($count > 1) {
		fractal_zip::message($string);
	}
}

function message_once($string) {
	static $printed_strings = array();
	if(isset($printed_strings[$string])) {
		return true;
	}
	$printed_strings[$string] = true;
	if(!fractal_zip::emit_html_trace()) {
		return true;
	}
	print('<span>' . $string . '</span><br>');
	return true;
}

function good_news($message) {
	if(!fractal_zip::emit_html_trace()) {
		return;
	}
	print('<span style="color: green;">' . $message . '</span><br>');
}

function good_news_if($string, $count) {
	if($count > 1) {
		fractal_zip::good_news($string);
	}
}

function good_news_once($string) {
	static $printed_strings = array();
	if(isset($printed_strings[$string])) {
		return true;
	}
	$printed_strings[$string] = true;
	if(!fractal_zip::emit_html_trace()) {
		return true;
	}
	print('<span style="color: green;">' . $string . '</span><br>');
	return true;
}

}

?>