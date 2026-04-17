<?php

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
	$done = true;
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_image_pac.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_raster_canonical.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_stream_index.php';
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
}

// dimensional breaks and other fractal string operations: encode video game map files, fractal_zip self-extractor .fzsx.php, compressed data that has zero size by referring to a point on a stream of compressed data and 
// decompressing a certain length of it; the stream source being? something like run time in reverse decompresses matter from a singularity

// Literal-bundle experiments (bytes-first): mode 12 = full-buffer byte reverse (strrev); mode 13 = BMP BI_RGB
// column-vertical delta; mode 14 = square-block transpose (leading floor(sqrt(n))^2 bytes as row-major s×s, transpose;
// mode 15 = BMP uniform grid: one cell template + per-cell scalar byte added to every channel/byte in the cell (BSS1).
// mode 16 = BMP grid core-only (BSS2): inner (cw−2m)×(ch−2m) template + per-cell scalar or BGR shift on core; cell rims stored raw.
// mode 17 = chained literal transforms (multipass squarebytes / vertical / …); see literal_bundle_greedy_transform_chain.
// mode 18 = multi-member ZIP semantic: per-file literals + rebuild (opens PKZIP so transforms run on inner payloads); FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP_MULTI=0 disables.
// Undecompressed `.flac`: optional full literal tournament on disk bytes — `FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE` (0|1|auto), `FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE_MAX_BYTES`, `FRACTAL_ZIP_LITERAL_FLAC_GZIP_PROBE_LEVEL`, `FRACTAL_ZIP_LITERAL_FLAC_SKIP_TRANSFORMS_MAX_GZIP1_RATIO`.
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
 * Do not merge heuristics that **increase** compressed size on representative corpora unless they are strictly optional
 * (env-gated) or superseded by a default that is **≤** prior size everywhere we measure. Wall-time improvements are fine
 * only when output is **the same size or smaller** for the same inputs and env.
 * Benchmarks may still report a time-weighted J score; library defaults stay bytes-first.
 * FZB literal `choose_best_literal_bundle_transform` on uncompressed BI_RGB BMP defaults to zlib deflate **level 9** probes
 * and exhaustive 2-step grid chains on; for faster iteration set `FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL=1` and/or
 * `FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN=0`.
 */

/** Outer wrapper from the most recent `adaptive_compress` call: `7z`, `gzip`, `arc`, `zstd`, or `brotli`. */
public static $last_outer_codec = null;
/** Prefix on outer brotli blobs (raw brotli has no stable magic). */
public const OUTER_BROTLI_MAGIC = 'FZb1';
/** Highest FZB literal mode allowed nested inside modes 6/7/8/11 (keep in sync with decode_bundle_literal_member). */
public const FZB_LITERAL_INNER_MODE_MAX = 18;

/**
 * Outer zstd/xz/brotli (and matching decompress helpers): above this input size, compress/decompress from a temp file
 * so the child never shares a full stdin pipe with a growing stdout buffer (classic proc_open deadlock).
 * Set FRACTAL_ZIP_OUTER_CODEC_TEMP_INPUT_MIN_BYTES=0 to always use temp file; huge value forces stdin pipe only (risky for large payloads).
 */
public static function outer_codec_temp_input_threshold_bytes(): int {
	$e = getenv('FRACTAL_ZIP_OUTER_CODEC_TEMP_INPUT_MIN_BYTES');
	if($e === false || trim((string)$e) === '') {
		return 8192;
	}
	return max(0, (int)$e);
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
 * Prefer writing a disk file before 7z "a" when the inner is large (avoids huge single fwrite to 7z stdin).
 * FRACTAL_ZIP_7Z_OUTER_FILE_FIRST_MIN_BYTES (default 2 MiB, 0 = never skip stdin attempt).
 */
public static function outer_7z_file_first_min_bytes(): int {
	$e = getenv('FRACTAL_ZIP_7Z_OUTER_FILE_FIRST_MIN_BYTES');
	if($e === false || trim((string)$e) === '') {
		return 2097152;
	}
	return max(0, (int)$e);
}

/**
 * Run compressor/decompressor that reads stdin and writes binary to stdout. Small payloads: one fwrite + slurp stdout.
 * Large payloads: write $stdinBlob to a temp file and pass it as the sole trailing path argument (zstd/xz/brotli all accept this).
 *
 * @return string|null raw stdout bytes or null on failure
 */
public static function outer_codec_run_stdin_or_tmpfile_stdout(string $cmdStdin, string $cmdWithTmpPlaceholder, string $stdinBlob, ?string $cwd = null): ?string {
	if(!function_exists('proc_open')) {
		return null;
	}
	$n = strlen($stdinBlob);
	$tmpMin = fractal_zip::outer_codec_temp_input_threshold_bytes();
	$desc = array(
		0 => array('pipe', 'r'),
		1 => array('pipe', 'w'),
		2 => array('pipe', 'w'),
	);
	if($tmpMin === 0 || $n >= $tmpMin) {
		$tmpIn = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzocin_' . bin2hex(random_bytes(8)) . '.bin';
		if(@file_put_contents($tmpIn, $stdinBlob) === false) {
			@unlink($tmpIn);
			return null;
		}
		$cmd = str_replace('__FZOC_TMPIN__', escapeshellarg($tmpIn), $cmdWithTmpPlaceholder);
		$out = null;
		$proc = @proc_open($cmd, $desc, $pipes, $cwd);
		if(is_resource($proc)) {
			fclose($pipes[0]);
			$out = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
			if(proc_close($proc) !== 0) {
				$out = null;
			}
		}
		@unlink($tmpIn);
		return ($out !== null && $out !== '') ? $out : null;
	}
	$out = null;
	$proc = @proc_open($cmdStdin, $desc, $pipes, $cwd);
	if(is_resource($proc)) {
		fwrite($pipes[0], $stdinBlob);
		fclose($pipes[0]);
		$out = stream_get_contents($pipes[1]);
		fclose($pipes[1]);
		stream_get_contents($pipes[2]);
		fclose($pipes[2]);
		if(proc_close($proc) !== 0) {
			$out = null;
		}
	}
	return ($out !== null && $out !== '') ? $out : null;
}
/** Single synthetic member path for fractal-encoded merged folder bytes (FZBF inner blob). */
public const LITERAL_MERGED_SYNTH_MEMBER = '__fz_lmerged.bin';
/** Outer wrapper used for the payload actually written to the `.fzc` after fractal vs lazy size compare. */
public static $last_written_container_codec = null;
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
	$v = getenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
	if($v === false || trim((string) $v) === '') {
		return false;
	}
	$v = strtolower(trim((string) $v));
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
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
 * ≥512 KiB and gzip-1 probe ratio is ≤ FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS_AUTO_MAX_GZIP1_RATIO (default 0.14), skipping dense random blobs.
 * With AUTO_MIN set only, the gzip ratio gate is off unless AUTO_MAX_GZIP1_RATIO is also set. Tune FRACTAL_ZIP_UNIFIED_LITERAL_SYNTH_MAX_BYTES
 * (0 = unlimited; default 64 MiB for explicit `=1` / auto runs). Nested FZBF zip runs multipass equivalence passes until no gain; cap wall time with
 * FRACTAL_ZIP_LITERAL_SYNTH_MULTIPASS_MAX_SECONDS (unset = no cap for synth). Optional segment tournament: FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID=1,
 * FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID_MAX_MERGED_BYTES (default 384 KiB).
 * Large-blob fractal substring search: FRACTAL_ZIP_FRACTAL_SUBSTRING_COARSE_* / FRACTAL_ZIP_LITERAL_SYNTH_SUBSTRING_COARSE_* (enumeration caps, top-K, window);
 * FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_DEPTH_MIN_BYTES + FRACTAL_ZIP_FRACTAL_LARGE_ENTRY_MAX_RECURSION_DEPTH (clamp recursion depth on huge roots).
 *
 * Collect files, build one or more literal inners (FZCD when applicable, FZBM merged+index for multi-file raw concat,
 * else FZB4/5/6/FZBD from encode_literal_bundle_payload),
 * pick smallest adaptive-compressed result vs raw escaped. Env FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 forces legacy zip_folder.
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
public static function unified_literal_multipass_synth_enabled_for_raw_bytes(int $totalRawBytes, string $mergedForAutoRatioProbe = ''): bool {
	$v = getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS');
	if($v !== false && trim((string) $v) !== '') {
		$vl = strtolower(trim((string) $v));
		if($vl === '1' || $vl === 'true' || $vl === 'yes' || $vl === 'on') {
			return true;
		}
		if($vl === '0' || $vl === 'off' || $vl === 'false' || $vl === 'no') {
			return false;
		}
	}
	$e = getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS_AUTO_MIN_RAW_BYTES');
	$autoMinExplicit = ($e !== false && trim((string) $e) !== '');
	$minB = $autoMinExplicit ? max(0, (int) $e) : 0;
	$ratioEnv = getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS_AUTO_MAX_GZIP1_RATIO');
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
	$defaultMin = 512 * 1024;
	$defaultRatio = 0.14;
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
	return getenv('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS') === '1';
}

/**
 * Max merged raw size (bytes) for FZBF (fractal-on-merged) trial. 0 = unlimited. Default 64 MiB when env unset.
 */
public static function unified_literal_synth_max_bytes(): int {
	$e = getenv('FRACTAL_ZIP_UNIFIED_LITERAL_SYNTH_MAX_BYTES');
	if($e === false || trim((string) $e) === '') {
		return 64 * 1024 * 1024;
	}
	$v = (int) trim((string) $e);
	return $v <= 0 ? 0 : min(2147483647, $v);
}

public static function folder_unified_stream_enabled(): bool {
	$v = getenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM');
	if($v === false || trim((string) $v) === '') {
		return true;
	}
	$v = strtolower(trim((string) $v));
	if($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
		return false;
	}
	return true;
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
	$e = getenv('FRACTAL_ZIP_FOLDER_LITERAL_INNER_PICK');
	$el = ($e !== false && trim((string) $e) !== '') ? strtolower(trim((string) $e)) : '';
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
 * folder with ≥2 distinct file extensions. Single-file corpora stay off so full adaptive_compress (incl. 7z) can maximize
 * byte wins when semantic unwrap helps one archive.
 *
 * @param array<string,string> $rawFilesByPath
 */
public static function folder_staged_literal_outer_auto_heuristic(array $rawFilesByPath): bool {
	$n = sizeof($rawFilesByPath);
	if($n <= 1) {
		return false;
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
	$e = getenv('FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER');
	if($e !== false && trim((string) $e) !== '') {
		$el = strtolower(trim((string) $e));
		if($el === '0' || $el === 'off' || $el === 'false' || $el === 'no') {
			return false;
		}
		if($el === '1' || $el === 'on' || $el === 'true' || $el === 'yes') {
			return true;
		}
	}
	return fractal_zip::folder_staged_literal_outer_auto_heuristic($rawFilesByPath);
}

/**
 * Whole-stream reversible preprocess on FZB4 inner before outer adaptive_compress. FZWS v1: delta or xor-adjacent on full blob.
 * FRACTAL_ZIP_WHOLE_STREAM_FZWS=0 disables. FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES caps input (default 128 MiB; 0 = unlimited).
 */
public static function whole_stream_fzws_enabled(): bool {
	$v = getenv('FRACTAL_ZIP_WHOLE_STREAM_FZWS');
	if($v === false || trim((string) $v) === '') {
		return true;
	}
	$v = strtolower(trim((string) $v));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

public static function whole_stream_fzws_max_bytes(): int {
	$e = getenv('FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES');
	if($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 128 * 1024 * 1024;
	}
	$v = (int) trim((string) $e);
	return $v <= 0 ? 0 : min(2147483647, $v);
}

/**
 * Max inner bytes for brotli Q11 on FZB literal bundles (path-order proxy, staged fast tier, adaptive outer).
 * Unset ⇒ bytes-first 16 MiB; FRACTAL_ZIP_SPEED=1 ⇒ 64 KiB unless FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES is set.
 */
public static function fzb_literal_brotli_q11_max_inner_bytes_effective(): int {
	$e = getenv('FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}
	if(getenv('FRACTAL_ZIP_SPEED') === '1') {
		return 65536;
	}
	return 16777216;
}

/**
 * Brotli-Q11 proxy cap for FZB4 path-order / layout scoring (many trials per folder). Uses zlib-9 fallback above this to keep
 * bytes-first outer Q11 (see fzb_literal_brotli_q11_max_inner_bytes_effective) without O(trials×huge) brotli work.
 * FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES overrides; default min(effective, 512 KiB).
 */
public static function fzb_literal_brotli_q11_path_order_proxy_max_bytes(): int {
	$e = getenv('FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES');
	if($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}
	$eff = fractal_zip::fzb_literal_brotli_q11_max_inner_bytes_effective();
	return min($eff, 524288);
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
		$this->improvement_factor_threshold = max(0.25, min(50.0, (float) $impEnv));
	} else {
		// Lower = more multipass range substitutions (better ratio, slower). Gate uses effective_multipass_gate_mult() × this.
		$this->improvement_factor_threshold = 0.4;
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
			$this->improvement_factor_threshold = max(0.25, min(50.0, (float) $compression_tuning_overrides['improvement_factor_threshold']));
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
	$string = str_replace('&', '&amp;', (string)$string);
	$string = str_replace('<', '&lt;', $string);
	return $string;
}

function unescape_literal_from_storage($string) {
	return htmlspecialchars_decode((string)$string);
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
	$tag = 'fzauto_' . bin2hex(random_bytes(4)) . '_' . $segment;
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
	$impGrid = $this->parse_auto_tune_float_list('FRACTAL_ZIP_AUTO_TUNE_IMPROVEMENT', array(0.35, 0.4, 0.5, 0.65, 0.85, 1.0, 1.2), 0.25, 50.0);
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
 * Member path stored in the container: relative to zip_folder root, forward slashes only.
 * When zip_folder_root_for_members is empty, returns $absolutePath unchanged (legacy behavior).
 */
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
			$payload = 'FZC5';
			$payload .= chr($count);
			$payload .= chr(strlen($sharedExt)) . $sharedExt;
			foreach($ordered as $path => $zipped) {
				$path = (string)$path;
				$zipped = (string)$zipped;
				$dot = strrpos($path, '.');
				$name = substr($path, 0, $dot);
				$payload .= chr(strlen($name)) . $name;
				$payload .= chr(strlen($zipped)) . $zipped;
			}
			$payload .= chr($frLen) . $fractal_string;
			return $payload;
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
					$payload = 'FZCT';
					$payload .= chr($nlen) . $nameOnly;
					$payload .= $this->encode_varint_u32(strlen($zippedOne)) . $zippedOne;
					$payload .= $this->encode_varint_u32($frLen) . $fractal_string;
					return $payload;
				}
			}
		}
		// FZC6: same flat shared-extension layout as FZC5, but varint value + fractal lengths (large HTML members, etc.).
		$payload = 'FZC6';
		$payload .= chr($count);
		$payload .= chr(strlen($sharedExt)) . $sharedExt;
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$dot = strrpos($path, '.');
			$name = substr($path, 0, $dot);
			$payload .= chr(strlen($name)) . $name;
			$payload .= $this->encode_varint_u32(strlen($zipped)) . $zipped;
		}
		$payload .= $this->encode_varint_u32($frLen) . $fractal_string;
		return $payload;
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
		$payload = 'FZC4';
		$payload .= chr($count);
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$payload .= chr(strlen($path)) . $path;
			$payload .= chr(strlen($zipped)) . $zipped;
		}
		$payload .= chr($frLen) . $fractal_string;
		return $payload;
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
		$payload = 'FZC3';
		$payload .= pack('n', $count);
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$payload .= chr(strlen($path)) . $path;
			$payload .= chr(strlen($zipped)) . $zipped;
		}
		$payload .= pack('N', strlen($fractal_string)) . $fractal_string;
		return $payload;
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
		$payload = 'FZC2';
		$payload .= pack('N', sizeof($ordered));
		foreach($ordered as $path => $zipped) {
			$path = (string)$path;
			$zipped = (string)$zipped;
			$payload .= pack('n', strlen($path)) . $path;
			$payload .= pack('n', strlen($zipped)) . $zipped;
		}
		$payload .= pack('N', strlen($fractal_string)) . $fractal_string;
		return $payload;
	}
	$payload = 'FZC1';
	$payload .= pack('N', sizeof($ordered));
	foreach($ordered as $path => $zipped) {
		$path = (string)$path;
		$zipped = (string)$zipped;
		$payload .= pack('N', strlen($path)) . $path;
		$payload .= pack('N', strlen($zipped)) . $zipped;
	}
	$payload .= pack('N', strlen($fractal_string)) . $fractal_string;
	return $payload;
}

function delta_encode_bytes($raw) {
	$raw = (string)$raw;
	$n = strlen($raw);
	if($n === 0) {
		return '';
	}
	$out = $raw[0];
	$prev = ord($raw[0]);
	for($i = 1; $i < $n; $i++) {
		$cur = ord($raw[$i]);
		$d = ($cur - $prev + 256) % 256;
		$out .= chr($d);
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
	$out = $enc[0];
	$prev = ord($enc[0]);
	for($i = 1; $i < $n; $i++) {
		$d = ord($enc[$i]);
		$cur = ($prev + $d) % 256;
		$out .= chr($cur);
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
	$out = $raw[0];
	for($i = 1; $i < $n; $i++) {
		$out .= chr(ord($raw[$i]) ^ ord($raw[$i - 1]));
	}
	return $out;
}

function xor_adjacent_decode_bytes($enc) {
	$enc = (string)$enc;
	$n = strlen($enc);
	if($n <= 1) {
		return $enc;
	}
	$out = $enc[0];
	for($i = 1; $i < $n; $i++) {
		$out .= chr(ord($enc[$i]) ^ ord($out[$i - 1]));
	}
	return $out;
}

/** Lossless involution: bitwise NOT per byte (sometimes helps zlib after other stages). */
function invert_bytes($raw) {
	$raw = (string)$raw;
	$n = strlen($raw);
	$out = '';
	for($i = 0; $i < $n; $i++) {
		$out .= chr(255 ^ ord($raw[$i]));
	}
	return $out;
}

/** Lossless involution: swap high/low nibble per byte. */
function nibble_swap_bytes($raw) {
	$raw = (string)$raw;
	$n = strlen($raw);
	$out = '';
	for($i = 0; $i < $n; $i++) {
		$b = ord($raw[$i]);
		$out .= chr((($b & 0x0F) << 4) | (($b & 0xF0) >> 4));
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
	$n = strlen($raw);
	if($n < 54 || substr($raw, 0, 2) !== 'BM') {
		return null;
	}
	$pixelOff = unpack('V', substr($raw, 10, 4))[1];
	$dibSize = unpack('V', substr($raw, 14, 4))[1];
	if($dibSize < 40 || $dibSize > 256) {
		return null;
	}
	if(14 + $dibSize > $n) {
		return null;
	}
	$w = unpack('V', substr($raw, 18, 4))[1];
	if($w < 1 || $w > 65535) {
		return null;
	}
	$hSigned = unpack('V', substr($raw, 22, 4))[1];
	$h = (int)($hSigned > 0x7FFFFFFF ? $hSigned - 0x100000000 : $hSigned);
	$hAbs = $h < 0 ? -$h : $h;
	if($hAbs < 1 || $hAbs > 65535) {
		return null;
	}
	$planes = unpack('v', substr($raw, 26, 2))[1];
	$bpp = unpack('v', substr($raw, 28, 2))[1];
	$comp = unpack('V', substr($raw, 30, 4))[1];
	if($planes !== 1 || $comp !== 0) {
		return null;
	}
	if($bpp !== 8 && $bpp !== 24 && $bpp !== 32) {
		return null;
	}
	if($pixelOff < 14 + $dibSize || $pixelOff > $n) {
		return null;
	}
	$rowStride = (int)((((int)$w * (int)$bpp + 31) >> 5) << 2);
	if($rowStride < 1) {
		return null;
	}
	$bodyLen = $n - $pixelOff;
	if($bodyLen < 0) {
		return null;
	}
	$expectedBody = $rowStride * $hAbs;
	if($requireFullPixelBody && $bodyLen < $expectedBody) {
		return null;
	}
	return array('w' => (int)$w, 'h' => $hAbs, 'bpp' => (int)$bpp, 'pixel_off' => (int)$pixelOff, 'row_stride' => $rowStride);
}

/** Per-row first-byte anchor, then byte -= previous byte in row (mod 256). */
function delta_encode_rows_horizontal($body, $rowStride) {
	$body = (string)$body;
	$rowStride = (int)$rowStride;
	$n = strlen($body);
	if($rowStride < 2 || $n === 0) {
		return $body;
	}
	$out = '';
	for($o = 0; $o < $n; $o += $rowStride) {
		$row = substr($body, $o, $rowStride);
		$lr = strlen($row);
		if($lr === 0) {
			break;
		}
		$chunk = $row[0];
		for($i = 1; $i < $lr; $i++) {
			$chunk .= chr((ord($row[$i]) - ord($row[$i - 1])) & 255);
		}
		$out .= $chunk;
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
	$out = '';
	for($o = 0; $o < $n; $o += $rowStride) {
		$row = substr($body, $o, $rowStride);
		$lr = strlen($row);
		if($lr === 0) {
			break;
		}
		$prev = ord($row[0]);
		$line = chr($prev);
		for($i = 1; $i < $lr; $i++) {
			$prev = ($prev + ord($row[$i])) & 255;
			$line .= chr($prev);
		}
		$out .= $line;
	}
	return $out;
}

/**
 * BMP: keep header through bfOffBits raw; row-wise horizontal delta on pixel bytes (+ optional tail after bitmap).
 * @return string|null stored form, or null if not a supported BMP
 */
function encode_bmp_row_horizontal_delta_payload($rawBytes) {
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes);
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
	$out = substr($body, 0, $rowStride);
	for($r = 1; $r < $h; $r++) {
		$ro = $r * $rowStride;
		$roPrev = ($r - 1) * $rowStride;
		for($c = 0; $c < $rowStride; $c++) {
			$out .= chr((ord($body[$ro + $c]) - ord($body[$roPrev + $c])) & 255);
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
	$out = substr($body, 0, $rowStride);
	for($r = 1; $r < $h; $r++) {
		$ro = $r * $rowStride;
		$roPrev = ($r - 1) * $rowStride;
		for($c = 0; $c < $rowStride; $c++) {
			$out .= chr((ord($out[$roPrev + $c]) + ord($body[$ro + $c])) & 255);
		}
	}
	return $out;
}

/**
 * BMP: same header/tail as mode 5; column-vertical delta on pixel body (complements row-horizontal mode 5).
 * @return string|null
 */
function encode_bmp_column_vertical_delta_payload($rawBytes) {
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes);
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
	$o = '';
	for($r = 0; $r < $s; $r++) {
		for($c = 0; $c < $s; $c++) {
			$o .= $head[$c * $s + $r];
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
	$d = (ord($cellBlock[0]) - ord($refBlock[0])) & 255;
	for($i = 0; $i < $bppBytes; $i++) {
		if(((ord($cellBlock[$i]) - ord($refBlock[$i])) & 255) !== $d) {
			return null;
		}
	}
	for($p = $bppBytes; $p < $nb; $p += $bppBytes) {
		$dp = (ord($cellBlock[$p]) - ord($refBlock[$p])) & 255;
		if($dp !== $d) {
			return null;
		}
		for($j = 1; $j < $bppBytes; $j++) {
			if(((ord($cellBlock[$p + $j]) - ord($refBlock[$p + $j])) & 255) !== $d) {
				return null;
			}
		}
	}
	return $d;
}

/**
 * Per-cell constant BGR triplet (add mod 256 per channel) vs reference cell; 24 bpp only.
 * @return array{0:int,1:int,2:int}|null
 */
function bmp_bss_cell_bgr_triple_delta($refBlock, $cellBlock) {
	$refBlock = (string)$refBlock;
	$cellBlock = (string)$cellBlock;
	$nb = strlen($refBlock);
	if($nb !== strlen($cellBlock) || $nb < 3 || ($nb % 3) !== 0) {
		return null;
	}
	$db = (ord($cellBlock[0]) - ord($refBlock[0])) & 255;
	$dg = (ord($cellBlock[1]) - ord($refBlock[1])) & 255;
	$dr = (ord($cellBlock[2]) - ord($refBlock[2])) & 255;
	for($p = 0; $p < $nb; $p += 3) {
		if(((ord($cellBlock[$p]) - ord($refBlock[$p])) & 255) !== $db) {
			return null;
		}
		if(((ord($cellBlock[$p + 1]) - ord($refBlock[$p + 1])) & 255) !== $dg) {
			return null;
		}
		if(((ord($cellBlock[$p + 2]) - ord($refBlock[$p + 2])) & 255) !== $dr) {
			return null;
		}
	}
	return array($db, $dg, $dr);
}

/**
 * Extract one cell from BMP pixel body (top-left visual origin), row-major within the cell.
 */
function bmp_bss_extract_cell($pixBody, $hAbs, $rowStride, $bc, $br, $cw, $ch, $bppBytes) {
	$out = '';
	for($vy = $br * $ch; $vy < ($br + 1) * $ch; $vy++) {
		$sy = $hAbs - 1 - $vy;
		for($vx = $bc * $cw; $vx < ($bc + 1) * $cw; $vx++) {
			$o = $sy * $rowStride + $vx * $bppBytes;
			$out .= substr($pixBody, $o, $bppBytes);
		}
	}
	return $out;
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
	$out = '';
	for($ly = $margin; $ly < $ch - $margin; $ly++) {
		$vy = $br * $ch + $ly;
		$sy = $hAbs - 1 - $vy;
		for($lx = $margin; $lx < $cw - $margin; $lx++) {
			$vx = $bc * $cw + $lx;
			$o = $sy * $rowStride + $vx * $bppBytes;
			$out .= substr($pixBody, $o, $bppBytes);
		}
	}
	return $out;
}

/**
 * All cell-border (non-core) pixels in BMP body scan order: top image row (vy=0) left-to-right, then next row — matches decode.
 */
function bmp_bss_rim_body_raster_concat($pixBody, $hAbs, $rowStride, $w, $cw, $ch, $bppBytes, $margin) {
	$margin = (int)$margin;
	$out = '';
	for($vy = 0; $vy < $hAbs; $vy++) {
		for($vx = 0; $vx < $w; $vx++) {
			$br = intdiv($vy, $ch);
			$bc = intdiv($vx, $cw);
			$ly = $vy - $br * $ch;
			$lx = $vx - $bc * $cw;
			$inCore = ($lx >= $margin && $lx < $cw - $margin && $ly >= $margin && $ly < $ch - $margin);
			if($inCore) {
				continue;
			}
			$sy = $hAbs - 1 - $vy;
			$o = $sy * $rowStride + $vx * $bppBytes;
			$out .= substr($pixBody, $o, $bppBytes);
		}
	}
	return $out;
}

/**
 * Mode 15 stored form: original BMP header [0 .. pixel_off-1] + BSS1 + u8 kind + varint ncol + varint nrow + template + deltas.
 * kind=0: ncol×nrow bytes — same uint8 added to every byte in each cell vs the top-left template.
 * kind=1 (24 bpp only): ncol×nrow×3 bytes — constant (dB,dG,dR) per cell vs template (independent channel shifts).
 * Disable probing: FRACTAL_ZIP_LITERAL_BMP_BLOCK_SHIFT=0.
 * @return string|null compact stored blob, or null if no grid factorization fits
 */
function encode_bmp_block_scalar_shift_payload($rawBytes) {
	$env = getenv('FRACTAL_ZIP_LITERAL_BMP_BLOCK_SHIFT');
	if($env !== false && trim((string)$env) !== '' && (int)$env === 0) {
		return null;
	}
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
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
			if(strlen($ref) !== $tplSz) {
				continue;
			}
			foreach(array(0, 1) as $kind) {
				if($kind === 1 && $bpp !== 24) {
					continue;
				}
				$deltas = '';
				$ok = true;
				for($br = 0; $br < $nrow; $br++) {
					for($bc = 0; $bc < $ncol; $bc++) {
						$blk = $this->bmp_bss_extract_cell($pix, $hAbs, $rs, $bc, $br, $cw, $ch, $bppBytes);
						if($kind === 0) {
							$d = $this->bmp_bss_cell_scalar_delta($ref, $blk, $bppBytes);
							if($d === null) {
								$ok = false;
								break 2;
							}
							$deltas .= chr($d);
						} else {
							$tri = $this->bmp_bss_cell_bgr_triple_delta($ref, $blk);
							if($tri === null) {
								$ok = false;
								break 2;
							}
							$deltas .= chr($tri[0]) . chr($tri[1]) . chr($tri[2]);
						}
					}
				}
				if(!$ok) {
					continue;
				}
				$payload = 'BSS1' . chr($kind) . $this->encode_varint_u32($ncol) . $this->encode_varint_u32($nrow) . $ref . $deltas;
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
 * @return string|null
 */
function encode_bmp_block_core_scalar_shift_payload($rawBytes) {
	$env = getenv('FRACTAL_ZIP_LITERAL_BMP_BLOCK_SHIFT_CORE');
	if($env !== false && trim((string)$env) !== '' && (int)$env === 0) {
		return null;
	}
	$meta = $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true);
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
				$ref = $this->bmp_bss_extract_cell_core($pix, $hAbs, $rs, 0, 0, $cw, $ch, $bppBytes, $margin);
				if($ref === null || strlen($ref) !== $tplSz) {
					continue;
				}
				foreach(array(0, 1) as $kind) {
					if($kind === 1 && $bpp !== 24) {
						continue;
					}
					$deltas = '';
					$ok = true;
					for($br = 0; $br < $nrow; $br++) {
						for($bc = 0; $bc < $ncol; $bc++) {
							$core = $this->bmp_bss_extract_cell_core($pix, $hAbs, $rs, $bc, $br, $cw, $ch, $bppBytes, $margin);
							if($core === null || strlen($core) !== $tplSz) {
								$ok = false;
								break 2;
							}
							if($kind === 0) {
								$d = $this->bmp_bss_cell_scalar_delta($ref, $core, $bppBytes);
								if($d === null) {
									$ok = false;
									break 2;
								}
								$deltas .= chr($d);
							} else {
								$tri = $this->bmp_bss_cell_bgr_triple_delta($ref, $core);
								if($tri === null) {
									$ok = false;
									break 2;
								}
								$deltas .= chr($tri[0]) . chr($tri[1]) . chr($tri[2]);
							}
						}
					}
					if(!$ok) {
						continue;
					}
					$rims = $this->bmp_bss_rim_body_raster_concat($pix, $hAbs, $rs, $w, $cw, $ch, $bppBytes, $margin);
					$payload = 'BSS2' . chr($kind)
						. $this->encode_varint_u32($ncol) . $this->encode_varint_u32($nrow)
						. $this->encode_varint_u32($cw) . $this->encode_varint_u32($ch)
						. $this->encode_varint_u32($margin)
						. $ref . $deltas . $rims;
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
	$bySy = array();
	for($vy = 0; $vy < $hAbs; $vy++) {
		$sy = $hAbs - 1 - $vy;
		$row = '';
		for($vx = 0; $vx < $w; $vx++) {
			$br = intdiv($vy, $ch);
			$bc = intdiv($vx, $cw);
			$cidx = $br * $ncol + $bc;
			$ly = $vy - $br * $ch;
			$lx = $vx - $bc * $cw;
			$tBase = ($ly * $cw + $lx) * $bppBytes;
			if($kind === 0) {
				$d = ord($deltas[$cidx]);
				for($j = 0; $j < $bppBytes; $j++) {
					$row .= chr((ord($tpl[$tBase + $j]) + $d) & 255);
				}
			} else {
				$o3 = $cidx * 3;
				$db = ord($deltas[$o3]);
				$dg = ord($deltas[$o3 + 1]);
				$dr = ord($deltas[$o3 + 2]);
				$row .= chr((ord($tpl[$tBase]) + $db) & 255);
				$row .= chr((ord($tpl[$tBase + 1]) + $dg) & 255);
				$row .= chr((ord($tpl[$tBase + 2]) + $dr) & 255);
			}
		}
		$row .= str_repeat("\0", $rs - strlen($row));
		$bySy[$sy] = $row;
	}
	$pix = '';
	for($sy = 0; $sy < $hAbs; $sy++) {
		$pix .= $bySy[$sy];
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
	$bySy = array();
	for($vy = 0; $vy < $hAbs; $vy++) {
		$sy = $hAbs - 1 - $vy;
		$row = '';
		for($vx = 0; $vx < $w; $vx++) {
			$br = intdiv($vy, $ch);
			$bc = intdiv($vx, $cw);
			$cidx = $br * $ncol + $bc;
			$ly = $vy - $br * $ch;
			$lx = $vx - $bc * $cw;
			$inCore = ($lx >= $margin && $lx < $cw - $margin && $ly >= $margin && $ly < $ch - $margin);
			if($inCore) {
				$cly = $ly - $margin;
				$clx = $lx - $margin;
				$tBase = ($cly * $iw + $clx) * $bppBytes;
				if($kind === 0) {
					$d = ord($deltas[$cidx]);
					for($j = 0; $j < $bppBytes; $j++) {
						$row .= chr((ord($tpl[$tBase + $j]) + $d) & 255);
					}
				} else {
					$o3 = $cidx * 3;
					$db = ord($deltas[$o3]);
					$dg = ord($deltas[$o3 + 1]);
					$dr = ord($deltas[$o3 + 2]);
					$row .= chr((ord($tpl[$tBase]) + $db) & 255);
					$row .= chr((ord($tpl[$tBase + 1]) + $dg) & 255);
					$row .= chr((ord($tpl[$tBase + 2]) + $dr) & 255);
				}
			} else {
				if($rimOff + $bppBytes > strlen($rim)) {
					fractal_zip::fatal_error('Corrupt FZB literal (mode 16): rim underrun.');
				}
				$row .= substr($rim, $rimOff, $bppBytes);
				$rimOff += $bppBytes;
			}
		}
		$row .= str_repeat("\0", $rs - strlen($row));
		$bySy[$sy] = $row;
	}
	if($rimOff !== strlen($rim)) {
		fractal_zip::fatal_error('Corrupt FZB literal (mode 16): rim length mismatch.');
	}
	$pix = '';
	for($sy = 0; $sy < $hAbs; $sy++) {
		$pix .= $bySy[$sy];
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
	if($mode === 7 || $mode === 8 || $mode === 6 || $mode === 11 || $mode === 18) {
		$maxDepthEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_DECODE_MAX_DEPTH');
		$maxDepth = ($maxDepthEnv === false || trim((string)$maxDepthEnv) === '') ? 128 : max(8, min(4096, (int)$maxDepthEnv));
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
	if($n >= 10 && substr($inner, 0, 4) === 'FZWS' && ord($inner[4]) === 1) {
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
	$sig = substr($inner, 0, 4);
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

/** Optional FZWS wrap when gzip-1 probe beats raw inner (bounded by whole_stream_fzws_max_bytes). */
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
	if($maxB > 0 && strlen($inner) > $maxB) {
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
		$wrapped = 'FZWS' . chr(1) . chr($tid) . pack('N', strlen($inner)) . $body;
		$z = gzdeflate($wrapped, 1);
		if($z !== false && strlen($z) < $bestScore) {
			$bestScore = strlen($z);
			$bestWrap = $wrapped;
		}
	}
	return $bestWrap !== '' ? $bestWrap : $inner;
}

function finalize_literal_bundle_inner_for_compress($inner) {
	return $this->maybe_wrap_fzb_inner_with_fzws((string) $inner);
}

/**
 * Phase 3: smallest adaptive-compressed blob among literal inners (FZCD, FZB*, …) and raw escaped-per-file container.
 * When FRACTAL_ZIP_FOLDER_STAGED_LITERAL_OUTER allows (auto: multi-file MPQ-family or mixed extensions), uses a fast
 * gzip+zstd+brotli tier for all candidates, then runs full adaptive_compress only on the best semantic inner if it beats
 * raw on that tier by at least FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_FAST_MARGIN_BYTES (unset ⇒ max(48, min(8192,
 * floor(2.5% of raw’s fast-tier size))) — large trees still cap at 8192 B minimum improvement; small mixed-ext folders
 * can deepen after ~48–200 B. Override with the env var (bytes).
 *
 * @param list<array{inner: string, tag: string}> $innerVariants
 * @param array<string,string> $rawFilesByPath
 * @return array{0: string, 1: string}
 */
function choose_smallest_adaptive_literal_inner_or_raw_escaped(array $innerVariants, $rawFilesByPath) {
	$rawEscaped = array();
	foreach($rawFilesByPath as $path => $bytes) {
		$rawEscaped[(string) $path] = $this->escape_literal_for_storage((string) $bytes);
	}
	$rawInner = $this->encode_container_payload($rawEscaped, '');
	if(!fractal_zip::folder_staged_literal_outer_enabled($rawFilesByPath)) {
		$rawContents = $this->adaptive_compress($rawInner);
		$codecRaw = fractal_zip::$last_outer_codec;
		$bestLen = strlen($rawContents);
		$bestBlob = $rawContents;
		$bestTag = 'raw';
		$bestCodec = $codecRaw;
		foreach($innerVariants as $v) {
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
			$lb = strlen($bundleContents);
			if($lb < $bestLen) {
				$bestLen = $lb;
				$bestBlob = $bundleContents;
				$bestTag = $tag;
				$bestCodec = $codecB;
			}
		}
		fractal_zip::$last_outer_codec = $bestCodec;
		fractal_zip::$last_written_container_codec = $bestCodec;
		return array($bestBlob, $bestTag);
	}
	$rp = $this->adaptive_compress_outer_fast_codec_tier($rawInner);
	$rawFastLen = strlen($rp);
	$bestLen = $rawFastLen;
	$bestBlob = $rp;
	$bestTag = 'raw';
	$bestCodec = fractal_zip::$last_outer_codec;
	$bestLitFastLen = PHP_INT_MAX;
	$bestLitFin = null;
	$bestLitTag = 'fzb';
	foreach($innerVariants as $v) {
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
		$lb = strlen($blob);
		if($lb < $bestLen) {
			$bestLen = $lb;
			$bestBlob = $blob;
			$bestTag = $tag;
			$bestCodec = $codecB;
		}
		if($lb < $bestLitFastLen) {
			$bestLitFastLen = $lb;
			$bestLitFin = $fin;
			$bestLitTag = $tag;
		}
	}
	$deepMinEnv = getenv('FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_BYTES');
	$deepMinB = ($deepMinEnv === false || trim((string) $deepMinEnv) === '') ? 1 : max(0, (int) $deepMinEnv);
	$marginEnv = getenv('FRACTAL_ZIP_STAGED_LITERAL_DEEP_MIN_FAST_MARGIN_BYTES');
	$fastMargin = ($marginEnv === false || trim((string) $marginEnv) === '') ? -1 : (int) trim((string) $marginEnv);
	if($fastMargin < 0) {
		// Default margin must scale with fast-tier output size. The legacy max(8192, 0.3% raw) forced ≥8192 B, so when
		// rawFastLen was only a few KiB (mixed extensions → staged outer), shouldDeepen never fired and full
		// adaptive_compress skipped on literal winners.
		$fastMargin = max(48, min(8192, (int) floor(0.025 * (float) $rawFastLen)));
	}
	$fastBeat = ($bestLitFin !== null && $bestLitFastLen + $deepMinB < $rawFastLen) ? ($rawFastLen - $bestLitFastLen) : 0;
	$shouldDeepen = ($bestLitFin !== null && $bestLitFastLen + $deepMinB < $rawFastLen && $fastBeat >= $fastMargin);
	if($shouldDeepen) {
		$deepBlob = $this->adaptive_compress($bestLitFin);
		$deepLen = strlen($deepBlob);
		$deepCodec = fractal_zip::$last_outer_codec;
		if($deepLen < $bestLen) {
			$bestLen = $deepLen;
			$bestBlob = $deepBlob;
			$bestTag = $bestLitTag;
			$bestCodec = $deepCodec;
		}
	}
	fractal_zip::$last_outer_codec = $bestCodec;
	fractal_zip::$last_written_container_codec = $bestCodec;
	return array($bestBlob, $bestTag);
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
	$paths = array_keys($rawFilesByPath);
	$tmpFzcd = sys_get_temp_dir() . DS . 'fzuni_' . bin2hex(random_bytes(8));
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
	$fzbmCapEnv = getenv('FRACTAL_ZIP_LITERAL_MERGED_FZBM_MAX_BYTES');
	$fzbmCap = ($fzbmCapEnv !== false && trim((string) $fzbmCapEnv) !== '' && is_numeric($fzbmCapEnv)) ? (int) $fzbmCapEnv : 0;
	$totalRaw = 0;
	foreach($rawFilesByPath as $b) {
		$totalRaw += strlen((string) $b);
	}
	if(sizeof($rawFilesByPath) >= 2 && getenv('FRACTAL_ZIP_DISABLE_LITERAL_MERGED_FZBM') !== '1' && ($fzbmCap <= 0 || $totalRaw <= $fzbmCap)) {
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
 * Large mostly-text payloads: skip only when a quick gzip-1 probe shows little redundancy (ratio gate); otherwise try
 * delta/xor (tabular/repetitive text). Full nibble/invert/BMP trials stay off for large text unless
 * FRACTAL_ZIP_LITERAL_LARGE_TEXT_LIGHT_PROBE=0 or FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS=1.
 * FRACTAL_ZIP_LITERAL_LARGE_TEXT_SKIP_PROBE_GZIP1_MIN_RATIO (default 0.88): if gzip-1 output length / raw length >= this, use raw only.
 * FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO (default 0.045): if gzip-1 length / raw length <= this, skip transforms (ratio computed with deflate **level 1** only, independent of literal_bundle_gzip_probe_level so level-9 tournament scoring cannot spuriously trigger this skip). Delta/xor can hurt outer text-detectors on dense prose. Exception: uncompressed BI_RGB BMP
 *   still runs the full transform path so modes 5/13 can beat raw when row/column delta helps.
 * Large non-text blobs (default > FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES, 4 MiB): skip delta/xor/nibble/invert
 * (each allocates a full copy; FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS=1 included) — avoids OOM on e.g. FLAC sidecars.
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
	$e = getenv('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES');
	if($e === false || trim((string) $e) === '') {
		return 2097152;
	}
	$v = (int) trim((string) $e);
	return $v <= 0 ? 0 : min(2147483647, $v);
}

/**
 * Whether $relPath looks like a terminal `.flac` member (case-insensitive).
 */
public static function literal_bundle_is_flac_rel(?string $relPath): bool {
	if($relPath === null || (string) $relPath === '') {
		return false;
	}
	return preg_match('/\\.flac$/i', (string) $relPath) === 1;
}

/**
 * Max raw `.flac` size (bytes) for **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE=auto`**. **0** = no cap (full tournament on any size; RAM-heavy).
 * Unset default **16777216** (16 MiB).
 */
public static function literal_flac_transform_probe_max_bytes(): int {
	$e = getenv('FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE_MAX_BYTES');
	if($e === false || trim((string) $e) === '') {
		return 16777216;
	}
	$v = (int) trim((string) $e);
	return $v <= 0 ? 0 : min(2147483647, $v);
}

/**
 * Run full literal delta/xor/… tournament on **disk** FLAC bytes (not decoded PCM). **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE`**:
 * unset / **0** / off = legacy behaviour; **1** / on = always (any size); **auto** = on when `strlen <=` literal_flac_transform_probe_max_bytes().
 */
public static function literal_flac_want_full_candidate_tournament(?string $relPath, int $rawLen): bool {
	if(!fractal_zip::literal_bundle_is_flac_rel($relPath)) {
		return false;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE');
	if($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	if($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
		return false;
	}
	if($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes') {
		return true;
	}
	if($v === 'auto') {
		$cap = fractal_zip::literal_flac_transform_probe_max_bytes();
		return $cap === 0 || $rawLen <= $cap;
	}
	return false;
}

/**
 * zlib deflate level for literal-bundle size probes in choose_best_literal_bundle_transform (candidates + chain scoring).
 * FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL: force 1–9 for all payloads.
 * Else for uncompressed BI_RGB BMP: FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL (default 9 = bytes-first; set 1–8 to trade speed).
 * Else small non-BMP: level 9 up to literal_nonbmp_gzip9_max_probe_bytes() (FRACTAL_ZIP_SPEED=1 clamps default cap to 512 KiB); larger non-BMP uses level 1.
 * VGM/VGZ members always use level 9 for probes (logged chiptune: zlib-1 underrates useful transforms vs outer gzip).
 * When **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE`** is on / auto (size-gated), `.flac` uses level **9** unless **`FRACTAL_ZIP_LITERAL_FLAC_GZIP_PROBE_LEVEL`** overrides.
 * Chain inner rounds default to the same level; override with FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL.
 *
 * @param string|null $relPath original member path (e.g. ends in .vgz before unwrap inside choose_best)
 */
function literal_bundle_gzip_probe_level($rawBytes, $relPath = null) {
	$rawBytes = (string)$rawBytes;
	$g = getenv('FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL');
	if($g !== false && trim((string)$g) !== '') {
		return max(1, min(9, (int)$g));
	}
	if($relPath !== null && $relPath !== '') {
		$ext = strtolower((string) pathinfo((string) $relPath, PATHINFO_EXTENSION));
		if($ext === 'vgm' || $ext === 'vgz') {
			return 9;
		}
		// Multi-member PKZIP literals use mode 18 vs raw; zlib-1 underrates the expanded inner stream (see literal_bundle_encode_multimember_zip_mode18).
		if($ext === 'zip') {
			return 9;
		}
		if(fractal_zip::literal_flac_want_full_candidate_tournament((string) $relPath, strlen($rawBytes))) {
			$fe = getenv('FRACTAL_ZIP_LITERAL_FLAC_GZIP_PROBE_LEVEL');
			if($fe !== false && trim((string) $fe) !== '') {
				return max(1, min(9, (int) $fe));
			}
			return 9;
		}
	}
	if($this->bmp_parse_simple_uncompressed_rgb($rawBytes, true) === null) {
		$cap = fractal_zip::literal_nonbmp_gzip9_max_probe_bytes();
		$capEnv = getenv('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES');
		if(getenv('FRACTAL_ZIP_SPEED') === '1' && ($capEnv === false || trim((string) $capEnv) === '')) {
			$cap = min($cap, 524288);
		}
		$n = strlen($rawBytes);
		if($cap > 0 && $n > 0 && $n <= $cap) {
			return 9;
		}
		return 1;
	}
	$b = getenv('FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL');
	if($b === false || trim((string)$b) === '') {
		return 9;
	}
	return max(1, min(9, (int)$b));
}

/** @return int|false */
function literal_bundle_gzip_deflate_len($bytes, $level) {
	$p = gzdeflate((string)$bytes, (int)$level);
	return ($p === false) ? false : strlen($p);
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
	$out = $this->encode_varint_u32(sizeof($members));
	foreach($members as $m) {
		$mname = (string) $m['name'];
		$data = (string) $m['data'];
		$childPath = $base . '/' . str_replace('\\', '/', $mname);
		list($cm, $cstore) = $this->choose_best_literal_bundle_transform($data, $childPath);
		$out .= $this->encode_varint_u32(strlen($mname)) . $mname;
		$out .= chr((int) $cm);
		$cstore = (string) $cstore;
		$out .= $this->encode_varint_u32(strlen($cstore)) . $cstore;
	}
	return $out;
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
	if(getenv('FRACTAL_ZIP_LITERAL_ZIP_MULTI_ALWAYS') === '1') {
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

function literal_bundle_consider_bmp_row_column_deltas($rawBytes, &$bestLen, &$bestMode, &$bestStore, $probeLev = 1) {
	$probeLev = max(1, min(9, (int)$probeLev));
	foreach(array(
		array(5, $this->encode_bmp_row_horizontal_delta_payload($rawBytes)),
		array(13, $this->encode_bmp_column_vertical_delta_payload($rawBytes)),
	) as $pair) {
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
 * (incl. reverse before/after row or column delta). On by default (bytes-first). Disable: FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN=0.
 * @return array{len: int, blob: string, modes: list<int>}|null
 */
function literal_bundle_bmp_exhaustive_grid_chains($rawBytes, $probeLev, $lightLargeText) {
	$rawBytes = (string)$rawBytes;
	if($lightLargeText) {
		return null;
	}
	$exEnv = getenv('FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN');
	if($exEnv !== false && trim((string)$exEnv) !== '') {
		$ev = strtolower(trim((string)$exEnv));
		if($ev === '0' || $ev === 'off' || $ev === 'false' || $ev === 'no') {
			return null;
		}
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
	$priority = array(16, 13, 5, 15, 14, 12, 1, 2, 3, 4);
	$chainModes = array();
	$current = $rawBytes;
	$maxIterEnv = getenv('FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER');
	$maxIter = ($maxIterEnv === false || trim((string)$maxIterEnv) === '') ? 64 : max(1, min(512, (int)$maxIterEnv));
	$iter = 0;
	$deepFullEnv = getenv('FRACTAL_ZIP_LITERAL_CHAIN_DEEP_FULL');
	$deepFull = ($deepFullEnv !== false && trim((string) $deepFullEnv) !== '' && ($deepFullEnv === '1' || strtolower(trim((string) $deepFullEnv)) === 'true' || strtolower(trim((string) $deepFullEnv)) === 'on'));
	while($iter < $maxIter) {
		$iter++;
		$curLen = $this->literal_bundle_gzip_deflate_len($current, $probeLev);
		if($curLen === false) {
			break;
		}
		$priIdx = array_flip($priority);
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
	$out = chr($n);
	foreach($modes as $m) {
		$mm = (int)$m;
		if($mm === 17 || $mm === 18 || $mm < 1 || $mm > 16 || ($mm >= 6 && $mm <= 11)) {
			return null;
		}
		$out .= chr($mm);
	}
	$out .= $this->encode_varint_u32(strlen($innermost)) . $innermost;
	return $out;
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
		if($mm === 17 || $mm === 18 || $mm < 1 || $mm > 16 || ($mm >= 6 && $mm <= 11)) {
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
	$rawProbe = gzdeflate($rawBytes, $gzipProbeLevel);
	if($rawProbe === false) {
		return $wrapOut(0, $rawBytes);
	}
	$bestLen = strlen($rawProbe);
	$bestMode = 0;
	$bestStore = $rawBytes;
	if($gzipProbeLevel === 1) {
		$ratioDeflateLen = $bestLen;
	} else {
		$rawProbeRatio1 = gzdeflate($rawBytes, 1);
		$ratioDeflateLen = ($rawProbeRatio1 === false) ? $bestLen : strlen($rawProbeRatio1);
	}
	$skipTransMaxEnv = getenv('FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO');
	$skipTransMax = ($skipTransMaxEnv === false || trim((string)$skipTransMaxEnv) === '') ? 0.045 : max(0.0, min(1.0, (float)$skipTransMaxEnv));
	if($rel !== '' && fractal_zip::literal_bundle_is_flac_rel($rel)) {
		$fe = getenv('FRACTAL_ZIP_LITERAL_FLAC_SKIP_TRANSFORMS_MAX_GZIP1_RATIO');
		if($fe !== false && trim((string) $fe) !== '') {
			$skipTransMax = max(0.0, min(1.0, (float) $fe));
		}
	}
	$tournamentStrict = getenv('FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT') === '1';
	$flacFullProbe = fractal_zip::literal_flac_want_full_candidate_tournament($rel !== '' ? $rel : null, $n);
	// Uncompressed BI_RGB BMP often beats raw under modes 5/13 even when gzip-1(raw)/n is tiny (photo-like rasters).
	$skipTransformsEarly = ($n > 0 && ($ratioDeflateLen / $n) <= $skipTransMax);
	if($skipTransformsEarly && $this->bmp_parse_simple_uncompressed_rgb($rawBytes) !== null) {
		$skipTransformsEarly = false;
	}
	// VGM/VGZ: avoid “incompressible” fast-exit; zlib-1 ratio is a poor proxy for delta/chain gains on logged audio.
	if($skipTransformsEarly && $rel !== '' && preg_match('/\.(vgm|vgz)$/i', $rel) === 1) {
		$skipTransformsEarly = false;
	}
	// PKZIP containers: mode 18 expands members so transforms run on inner payloads; do not bail out on gzip-1(zip) ratio.
	if($skipTransformsEarly && $rel !== '' && strtolower((string) pathinfo($rel, PATHINFO_EXTENSION)) === 'zip') {
		$skipTransformsEarly = false;
	}
	// FZCD merged-fractal PCM: zlib-1 ratio is a poor gate; always run transform tournament.
	if($skipTransformsEarly && $rel === '__fzcd_merged.pcm') {
		$skipTransformsEarly = false;
	}
	// Undecompressed FLAC: optional full literal tournament on container bytes.
	if($skipTransformsEarly && $flacFullProbe) {
		$skipTransformsEarly = false;
	}
	if($tournamentStrict) {
		$skipTransformsEarly = false;
	}
	if($skipTransformsEarly) {
		return $wrapOut(0, $rawBytes);
	}
	$largeText = ($n >= 262144 && $this->payload_looks_mostly_text($rawBytes));
	$forceAllProbes = (getenv('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS') === '1') || $tournamentStrict;
	if($largeText && !$forceAllProbes) {
		$ratioEnv = getenv('FRACTAL_ZIP_LITERAL_LARGE_TEXT_SKIP_PROBE_GZIP1_MIN_RATIO');
		$ratioMin = ($ratioEnv === false || trim((string)$ratioEnv) === '') ? 0.88 : max(0.0, min(1.0, (float)$ratioEnv));
		if($n > 0 && $ratioDeflateLen >= ($n * $ratioMin)) {
			return $wrapOut(0, $rawBytes);
		}
	}
	$maxTransformRawEnv = getenv('FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES');
	$maxTransformRaw = ($maxTransformRawEnv === false || trim((string)$maxTransformRawEnv) === '') ? 4194304 : max(65536, (int)$maxTransformRawEnv);
	if(!$tournamentStrict && $maxTransformRaw > 0 && $n > $maxTransformRaw && !$this->payload_looks_mostly_text($rawBytes) && !$flacFullProbe) {
		$this->literal_bundle_consider_multimember_zip_mode18($rawBytes, $relPath, $gzipProbeLevel, $bestLen, $bestMode, $bestStore);
		if($n >= 524288) {
			$this->literal_bundle_consider_bmp_row_column_deltas($rawBytes, $bestLen, $bestMode, $bestStore, $gzipProbeLevel);
		}
		return $wrapOut($bestMode, $bestStore);
	}
	$fzcdMergedPcm = ($rel === '__fzcd_merged.pcm');
	$lightLargeText = ($largeText && !$forceAllProbes);
	if($lightLargeText) {
		$lightEnv = getenv('FRACTAL_ZIP_LITERAL_LARGE_TEXT_LIGHT_PROBE');
		$lightOnly = ($lightEnv === false || trim((string)$lightEnv) === '') ? true : ((int)$lightEnv !== 0);
		if(!$lightOnly) {
			$lightLargeText = false;
		}
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
		$bmpRow = $this->encode_bmp_row_horizontal_delta_payload($rawBytes);
		$bmpCol = $this->encode_bmp_column_vertical_delta_payload($rawBytes);
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
	$bss = $this->encode_bmp_block_scalar_shift_payload($rawBytes);
	if($bss !== null) {
		$candidates[] = array(15, $bss);
	}
	$bss2 = $this->encode_bmp_block_core_scalar_shift_payload($rawBytes);
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
	if($bestMode === 12 && $bestStore === strrev($rawBytes)) {
		$rawGzLen = $this->literal_bundle_gzip_deflate_len($rawBytes, $gzipProbeLevel);
		$revGzLen = $this->literal_bundle_gzip_deflate_len($bestStore, $gzipProbeLevel);
		if($rawGzLen !== false && $revGzLen !== false && $revGzLen < $rawGzLen) {
			$minWinEnv = getenv('FRACTAL_ZIP_LITERAL_STRREV_MIN_GZIP_PROBE_BYTES_WIN');
			$minWin = ($minWinEnv === false || trim((string) $minWinEnv) === '') ? 8 : max(0, (int) trim((string) $minWinEnv));
			if($rawGzLen - $revGzLen < $minWin) {
				$bestMode = 0;
				$bestStore = $rawBytes;
				$bestLen = $rawGzLen;
			}
		}
	}
	$chainEnv = getenv('FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN');
	$chainOn = ($chainEnv === false || trim((string)$chainEnv) === '');
	if($chainEnv !== false && trim((string)$chainEnv) !== '') {
		$cv = strtolower(trim((string)$chainEnv));
		if($cv === '0' || $cv === 'off' || $cv === 'false' || $cv === 'no') {
			$chainOn = false;
		} else {
			$chainOn = true;
		}
	}
	if($chainOn && !$skipTransformsEarly && $maxTransformRaw > 0 && $n <= $maxTransformRaw && $this->bmp_parse_simple_uncompressed_rgb($rawBytes, true) !== null) {
		$chainSearchEnv = getenv('FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL');
		if($chainSearchEnv !== false && trim((string)$chainSearchEnv) !== '') {
			$chainSearchLev = max(1, min(9, (int)$chainSearchEnv));
		} else {
			$chainSearchLev = $gzipProbeLevel;
		}
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
	$this->literal_bundle_consider_multimember_zip_mode18($rawBytes, $relPath, $gzipProbeLevel, $bestLen, $bestMode, $bestStore);
	return $wrapOut($bestMode, $bestStore);
}

function payload_looks_mostly_text($bytes) {
	$bytes = (string)$bytes;
	$n = strlen($bytes);
	if($n < 256) {
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
	return ($texty / $sample) >= 0.9;
}

function encode_varint_u32($n) {
	$n = (int)$n;
	if($n < 0) {
		$n = 0;
	}
	$out = '';
	do {
		$b = $n & 0x7F;
		$n = $n >> 7;
		if($n > 0) {
			$b |= 0x80;
		}
		$out .= chr($b);
	} while($n > 0);
	return $out;
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
	$merged = '';
	$pos = array();
	foreach($concatKeyOrder as $path) {
		$path = (string) $path;
		$bytes = (string) $ordered[$path];
		$pLen = strlen($path);
		if($pLen > 65535) {
			fractal_zip::fatal_error('FZBM v1 encode: path too long.');
		}
		$pos[$path] = array(strlen($merged), strlen($bytes));
		$merged .= $bytes;
	}
	$pTot = strlen($merged);
	$hdr = 'FZBM' . chr(1) . $this->encode_varint_u32($nFiles);
	foreach($ordered as $path => $bytes) {
		$path = (string) $path;
		$bytes = (string) $bytes;
		$pLen = strlen($path);
		$off = $pos[$path][0];
		$mLen = $pos[$path][1];
		$hdr .= $this->encode_varint_u32($pLen) . $path;
		$hdr .= $this->encode_varint_u32($off) . $this->encode_varint_u32($mLen);
	}
	$hdr .= $this->encode_varint_u32($pTot);
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
	$offEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER');
	if($offEnv !== false && trim((string) $offEnv) !== '') {
		$ov = strtolower(trim((string) $offEnv));
		if($ov === '0' || $ov === 'off' || $ov === 'false' || $ov === 'no') {
			return $keys;
		}
	}
	$candLists = array();
	$candLists[] = $keys;
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
	$exhMaxEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_EXHAUSTIVE_MAX_N');
	$exhMaxN = ($exhMaxEnv === false || trim((string) $exhMaxEnv) === '') ? 6 : max(2, min(8, (int) trim((string) $exhMaxEnv)));
	$didExhaustive = false;
	if($n >= 2 && $n <= $exhMaxN) {
		foreach(fractal_zip::literal_bundle_fzb4_path_all_permutations($keys) as $perm) {
			$candLists[] = $perm;
		}
		$didExhaustive = true;
	}
	$randTriesEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES');
	$randTries = ($randTriesEnv === false || trim((string) $randTriesEnv) === '') ? 36 : max(0, min(256, (int) trim((string) $randTriesEnv)));
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
	$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_path_order_proxy_max_bytes();
	$brotliExe = fractal_zip::brotli_executable();
	$bestSeq = $keys;
	$bestScore = PHP_INT_MAX;
	foreach($uniq as $seq) {
		$payload = $this->encode_fzbm_v1_merged_literal_with_concat_key_order($ordered, $seq);
		$toScore = $this->finalize_literal_bundle_inner_for_compress($payload);
		$pl = strlen($toScore);
		$sc = PHP_INT_MAX;
		if($brotliExe !== null && getenv('FRACTAL_ZIP_SKIP_BROTLI') !== '1' && $fzbLitMax > 0 && $pl <= $fzbLitMax) {
			$blob = $this->outer_brotli_blob($brotliExe, $toScore, 11, null);
			if($blob !== null && $blob !== '') {
				$sc = strlen($blob);
				if(getenv('FRACTAL_ZIP_BROTLI_LGWIN') === false || trim((string) getenv('FRACTAL_ZIP_BROTLI_LGWIN')) === '') {
					$b16 = $this->outer_brotli_blob($brotliExe, $toScore, 11, null, 16);
					if($b16 !== null && $b16 !== '' && strlen($b16) < $sc) {
						$sc = strlen($b16);
					}
				}
			}
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
	$merged = '';
	foreach($ordered as $bytes) {
		$merged .= (string) $bytes;
	}
	return $merged;
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
	$gridEnv = getenv('FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID');
	$gridOn = ($gridEnv !== false && trim((string) $gridEnv) !== '' && ($gridEnv === '1' || strtolower(trim((string) $gridEnv)) === 'true' || strtolower(trim((string) $gridEnv)) === 'on' || strtolower(trim((string) $gridEnv)) === 'yes'));
	if(!$gridOn) {
		return array($base, null);
	}
	$maxEnv = getenv('FRACTAL_ZIP_LITERAL_SYNTH_SEGMENT_GRID_MAX_MERGED_BYTES');
	$maxM = ($maxEnv !== false && trim((string) $maxEnv) !== '') ? max(0, (int) $maxEnv) : (384 * 1024);
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
	$hdr = 'FZBF' . chr(1) . $this->encode_varint_u32($nFiles);
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
		$hdr .= $this->encode_varint_u32($pLen) . $path;
		$hdr .= $this->encode_varint_u32($off) . $this->encode_varint_u32($mLen);
		$offCheck += $mLen;
	}
	if($offCheck !== strlen($merged)) {
		return null;
	}
	$hdr .= $this->encode_varint_u32(strlen($merged));
	$hdr .= $this->encode_varint_u32(strlen($nested));
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
	$payload = 'FZB4';
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
		$payload .= $this->encode_varint_u32($commonPrefixLen) . $this->encode_varint_u32(strlen($pathSuffix)) . $pathSuffix;
		$payload .= chr((int) $ms['mode']);
		$payload .= $this->encode_varint_u32(strlen($ms['store'])) . $ms['store'];
		$prevPath = $path;
	}
	return $payload;
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
 * Reorder FZB4 members for smaller outer compression (brotli-oriented). Lexicographic order is not always best: e.g.
 * descending stored-byte size can improve whole-bundle ratio. Tries path‑sorted asc/desc, stored‑size desc; when member
 * count ≤ FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_EXHAUSTIVE_MAX_N (default 6), tries **all** permutations (bytes-first;
 * skips redundant random shuffles). Else (when n≤16) FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_RANDOM_TRIES pseudo-shuffles
 * (default 36; 0 = skip). Bounded: ≤32 members, ≤256 KiB combined store; score with brotli Q11 when inner ≤
 * FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES (else zlib-9). Disable all: FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER=0.
 *
 * @param array<string, string> $orderedAssoc ksorted path => raw (values unused; keys matter)
 * @param array<string, array{mode: int, store: string}> $modesStores
 * @return array<string, string> same keys in chosen foreach order
 */
function literal_bundle_pick_fzb4_path_order(array $orderedAssoc, array $modesStores) {
	$offEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_ORDER');
	if($offEnv !== false && trim((string) $offEnv) !== '') {
		$ov = strtolower(trim((string) $offEnv));
		if($ov === '0' || $ov === 'off' || $ov === 'false' || $ov === 'no') {
			return $orderedAssoc;
		}
	}
	$n = sizeof($orderedAssoc);
	if($n < 2 || $n > 32) {
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
	$exhMaxEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_EXHAUSTIVE_MAX_N');
	$exhMaxN = ($exhMaxEnv === false || trim((string) $exhMaxEnv) === '') ? 6 : max(2, min(8, (int) trim((string) $exhMaxEnv)));
	$didExhaustive = false;
	if($n >= 2 && $n <= $exhMaxN) {
		foreach(fractal_zip::literal_bundle_fzb4_path_all_permutations($keys) as $perm) {
			$candLists[] = $perm;
		}
		$didExhaustive = true;
	}
	$randTriesEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_FZB4_PATH_RANDOM_TRIES');
	$randTries = ($randTriesEnv === false || trim((string) $randTriesEnv) === '') ? 36 : max(0, min(256, (int) trim((string) $randTriesEnv)));
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
	$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_path_order_proxy_max_bytes();
	$brotliExe = fractal_zip::brotli_executable();
	$bestSeq = $keys;
	$bestScore = PHP_INT_MAX;
	foreach($uniq as $seq) {
		$payload = $this->literal_bundle_fzb4_payload_bytes_for_path_order($seq, $modesStores);
		$toScore = $this->finalize_literal_bundle_inner_for_compress($payload);
		$pl = strlen($toScore);
		$sc = PHP_INT_MAX;
		if($brotliExe !== null && getenv('FRACTAL_ZIP_SKIP_BROTLI') !== '1' && $fzbLitMax > 0 && $pl <= $fzbLitMax) {
			$blob = $this->outer_brotli_blob($brotliExe, $toScore, 11, null);
			if($blob !== null && $blob !== '') {
				$sc = strlen($blob);
			}
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
 * mode 0=raw, 1=delta, 2=xor-adjacent, 3=nibble-swap, 4=invert, 5=BMP row-horizontal delta (8/24/32 bpp BI_RGB, BITMAPINFOHEADER or extended DIB), 12=full-buffer byte reverse, 13=BMP column-vertical delta (same header/tail as 5), 14=square-block transpose (leading floor(sqrt(n))^2 bytes as s×s row-major, transpose, tail unchanged), 15=BMP uniform grid (BSS1: template cell + per-cell scalar or BGR triple vs template), 16=BMP grid core+rim (BSS2: core-only template + per-cell shift on core + raw cell borders), 17=chained literal transforms (multipass; see literal_bundle_greedy_transform_chain), 18=multi-member ZIP semantic (varint count + per entry: varint name + u8 inner_mode + varint inner_store; decode rebuilds ZIP; nested entries recurse choose_best). 6=nested gzip layers (bit-exact restore), 7=single-member ZIP semantic (varint name + inner; extract matches; container bytes may differ), 8=single-file 7z semantic (varint rel path + inner), 9=legacy raster (disk + inline FZRC1), 10=raster dedup (disk + FZBD table index), 11=MoPAQ/MPQ semantic (varint tag + inner FMQ2 blob; decode rebuilds via python3+smpq; default peel accepts extract-equivalent repack, not necessarily byte-identical container). When any member uses mode 10, encoder may prefix FZBD v1 (table + FZB4/5/6 body). Gzip: FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER=0 disables. ZIP/7z/MPQ peel: FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP / FRACTAL_ZIP_LITERAL_SEMANTIC_7Z / FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ =0 disables; FRACTAL_ZIP_LITERAL_SEMANTIC_PEEL_MAX_LAYERS caps depth; multi-member ZIP literals: FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP_MULTI / FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_MEMBERS / FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_RAW_BYTES. PNG/GIF/WebP/JPEG and stream types in fractal_zip_literal_pac_registry.php may preprocess. Multi-FLAC merge uses inner FZCD, not a per-member mode.
 * Raster FZBD path is optional: default raster_dedup_policy auto uses FZBD only when at least two members share one canonical blob (member count > table size), then picks plain vs raster by smaller compressed size on finalize_literal_bundle_inner_for_compress: gzcompress(.,9) when combined inner ≤ FRACTAL_ZIP_LITERAL_BUNDLE_RASTER_PROBE_ZLIB9_MAX_COMBINED_BYTES (default 8 MiB), else gzdeflate(.,1). Single-file or all-unique-canonical rasters keep plain (no FZRC1 tax). Tests: raster_dedup_policy always|never. Policy `always` skips the initial plain-only choose_best pass (same packed bytes; faster). Policy `auto` reuses plain per-member modes/stores in the raster pass when a member does not use raster dedup (avoids duplicate choose_best).
 * No fractal_string or operators; decoder maps values to escaped literals.
 *
 * @param array<string,string> $array_raw_files_by_path
 * @param array{raster_dedup_policy?: 'auto'|'always'|'never'}|null $encode_opts
 */
function encode_literal_bundle_payload($array_raw_files_by_path, $encode_opts = null) {
	$ordered = $array_raw_files_by_path;
	ksort($ordered, SORT_STRING);
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
		$orderedFzb4 = $this->literal_bundle_pick_fzb4_path_order($ordered, $modesStores);
		$payload4 = $this->literal_bundle_fzb4_payload_bytes_for_path_order(array_keys($orderedFzb4), $modesStores);
		$fzbLitMaxPick = fractal_zip::fzb_literal_brotli_q11_path_order_proxy_max_bytes();
		$brotliExePick = fractal_zip::brotli_executable();
		$layoutProxyEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_LAYOUT_USE_PROXY_SCORE');
		$layoutUseProxy = ($layoutProxyEnv === false || trim((string) $layoutProxyEnv) === '') ? true : ((int) $layoutProxyEnv !== 0);
		$literalBundleLayoutProxyScore = function($pl) use ($fzbLitMaxPick, $brotliExePick) {
			$pl = $this->finalize_literal_bundle_inner_for_compress((string) $pl);
			$plen = strlen($pl);
			$sc = PHP_INT_MAX;
			if($brotliExePick !== null && getenv('FRACTAL_ZIP_SKIP_BROTLI') !== '1' && $fzbLitMaxPick > 0 && $plen <= $fzbLitMaxPick) {
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
			$out = 'FZBD' . chr(1) . $this->encode_varint_u32(sizeof($rasterCanonTable));
			foreach($rasterCanonTable as $blob) {
				$out .= $this->encode_varint_u32(strlen($blob)) . $blob;
			}
			return $out . $body;
		};
		if($fzb5LayoutOk && $sharedExt !== null) {
			$payload5 = 'FZB5';
			$payload5 .= $this->encode_fzb56_bundle_member_count($count);
			$payload5 .= chr(strlen($sharedExt)) . $sharedExt;
			foreach($ordered as $path => $rawBytes) {
				$path = (string)$path;
				$dot = strrpos($path, '.');
				$name = substr($path, 0, $dot);
				$ms = $modesStores[$path];
				$payload5 .= chr(strlen($name)) . $name;
				$payload5 .= chr($ms['mode']);
				$payload5 .= $this->encode_varint_u32(strlen($ms['store'])) . $ms['store'];
			}
			$payload6 = null;
			if($fzb6PatternOk && $fzb6Prefix !== null) {
				$payload6 = 'FZB6';
				$payload6 .= $this->encode_fzb56_bundle_member_count($count);
				$payload6 .= chr(strlen($sharedExt)) . $sharedExt;
				$payload6 .= chr(strlen($fzb6Prefix)) . $fzb6Prefix;
				$payload6 .= chr($fzb6DigitsLen);
				foreach($ordered as $path => $rawBytes) {
					$path = (string)$path;
					$ms = $modesStores[$path];
					$payload6 .= chr($ms['mode']);
					$payload6 .= $this->encode_varint_u32((int)$fzb6Nums[$path]);
					$payload6 .= $this->encode_varint_u32(strlen($ms['store'])) . $ms['store'];
				}
			}
			if($layoutUseProxy) {
				$bestPl = $payload4;
				$bestSc = $literalBundleLayoutProxyScore($payload4);
				$bestRaw = strlen($payload4);
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
			$fzb5Wins = (strlen($payload5) <= strlen($payload4));
			if($fzb5Wins && $payload6 !== null) {
				if(strlen($payload6) < strlen($payload5) && strlen($payload6) < strlen($payload4)) {
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
	$zlib9MaxEnv = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_RASTER_PROBE_ZLIB9_MAX_COMBINED_BYTES');
	$zlib9Max = ($zlib9MaxEnv === false || trim((string)$zlib9MaxEnv) === '') ? (8 * 1024 * 1024) : max(0, (int)$zlib9MaxEnv);
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
		$pcmAll = '';
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
			$pcmAll .= $pcmPiece;
			$sumChunk += $cLen;
		}
		if(strlen($pcmAll) !== $rawTotal || $sumChunk !== $rawTotal) {
			fractal_zip::fatal_error('FZCD: merged fractal chunked PCM total length.');
		}
		$at = 0;
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.flac';
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
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.flac';
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
		$flacTmp = sys_get_temp_dir() . DS . 'fzcdmfl_' . bin2hex(random_bytes(8)) . '.flac';
		$pcmAllPath = sys_get_temp_dir() . DS . 'fzcdmpcm_' . bin2hex(random_bytes(8)) . '.raw';
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
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.flac';
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
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fractal_zip::fatal_error('FZCD: per-track PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.flac';
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
		foreach($entries as $e) {
			if($offset + $e['pcmLen'] > $n) {
				fractal_zip::fatal_error('Corrupt FZCD PCM.');
			}
			$pcm = substr($contents, $offset, $e['pcmLen']);
			$offset += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.raw';
			$flacTmp = sys_get_temp_dir() . DS . 'fzcdmem_' . bin2hex(random_bytes(8)) . '.flac';
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
 * Optional trailer after FZC1–FZC6 / FZCT payloads: exact on-disk bytes for peeled gzip members.
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
	foreach($candidates as $c) {
		if(strlen($c) < strlen($best)) {
			$best = $c;
		}
	}
	return $payload . $best;
}

function encode_fzg1_peel_trailer($restoreByPath) {
	$out = 'FZG1';
	$out .= $this->encode_varint_u32(sizeof($restoreByPath));
	foreach($restoreByPath as $path => $blob) {
		$path = (string)$path;
		$blob = (string)$blob;
		$out .= $this->encode_varint_u32(strlen($path)) . $path;
		$out .= $this->encode_varint_u32(strlen($blob)) . $blob;
	}
	return $out;
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
	$out = 'FZG2';
	$out .= $this->encode_varint_u32(sizeof($blobs));
	foreach($blobs as $bl) {
		$out .= $this->encode_varint_u32(strlen($bl)) . $bl;
	}
	$out .= $this->encode_varint_u32(sizeof($pathToIdx));
	foreach($pathToIdx as $path => $idx) {
		$out .= $this->encode_varint_u32(strlen($path)) . $path;
		$out .= $this->encode_varint_u32((int)$idx);
	}
	return $out;
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
	$out = 'FZG3';
	$out .= $this->encode_varint_u32(8);
	$out .= $trailer;
	$out .= $this->encode_varint_u32(sizeof($rows));
	foreach($rows as $path => $sp) {
		$out .= $this->encode_varint_u32(strlen($path)) . $path;
		$h = $sp['h'];
		$d = $sp['d'];
		$out .= $this->encode_varint_u32(strlen($h)) . $h;
		$out .= $this->encode_varint_u32(strlen($d)) . $d;
	}
	return $out;
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
	$out = 'FZG4';
	$out .= $this->encode_varint_u32(8);
	$out .= $trailer;
	$out .= $this->encode_varint_u32(sizeof($hdrList));
	foreach($hdrList as $h) {
		$out .= $this->encode_varint_u32(strlen($h)) . $h;
	}
	$out .= $this->encode_varint_u32(sizeof($defList));
	foreach($defList as $d) {
		$out .= $this->encode_varint_u32(strlen($d)) . $d;
	}
	$out .= $this->encode_varint_u32(sizeof($pairs));
	foreach($pairs as $path => $hiDi) {
		$out .= $this->encode_varint_u32(strlen($path)) . $path;
		$out .= $this->encode_varint_u32((int)$hiDi[0]);
		$out .= $this->encode_varint_u32((int)$hiDi[1]);
	}
	return $out;
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
	// backward compatibility: old serialized array(map, fractal_string)
	$data = unserialize($contents);
	if(!is_array($data) || sizeof($data) < 2) {
		fractal_zip::fatal_error('Unknown container payload format.');
	}
	return array($data[0], $data[1], array());
}

function logical_path_for_zip_member($absolutePath) {
	$root = $this->zip_folder_root_for_members;
	if($root === null || $root === '') {
		return $absolutePath;
	}
	$rootN = rtrim(str_replace('\\', '/', (string) $root), '/');
	$abs = realpath($absolutePath);
	if($abs === false) {
		$abs = $absolutePath;
	}
	$absN = str_replace('\\', '/', $abs);
	$prefix = $rootN . '/';
	if(strlen($absN) >= strlen($prefix) && substr($absN, 0, strlen($prefix)) === $prefix) {
		return substr($absN, strlen($prefix));
	}
	if($absN === $rootN) {
		return basename($absolutePath);
	}
	fractal_zip::warning_once('logical_path_for_zip_member: file not under zip folder root; using basename.');
	return basename($absolutePath);
}

function recursive_zip_folder($dir, $debug = false) {
	$handle = opendir($dir);
	if($handle === false) {
		fractal_zip::warning_once('recursive_zip_folder: unable to open directory, skipping');
		return;
	}
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..') {
			
		} elseif(is_dir($dir . DS . $entry)) {
			$this->recursive_zip_folder($dir . DS . $entry, $debug);
		} else {
			fractal_zip_ensure_literal_pac_stack_loaded();
			$entry_filename = $dir . DS . $entry;
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
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..') {
			
		} elseif(is_dir($dir . DS . $entry)) {
			fractal_zip::recursive_get_strings_for_fractal_zip_markers($dir . DS . $entry);
		} else {
			$entry_filename = $dir . DS . $entry;
			$contents = file_get_contents($entry_filename);
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
	$e = getenv('FRACTAL_ZIP_MEMBER_DEEP_UNWRAP');
	if($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
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
	$sKey = $synthOn ? 's' : 'n';
	static $cached = array();
	if(isset($cached[$sKey])) {
		return $cached[$sKey];
	}
	// Global speed budget (milliseconds) overrides recursive search budget.
	$ms = getenv('FRACTAL_ZIP_TIME_BUDGET_MS');
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
 * Normal folders: same as FRACTAL_ZIP_MAX_RECURSIVE_FRACTAL_SECONDS (via max_recursive_fractal_seconds()).
 * FZBF nested synth: default **0 = unlimited** while passes still improve; set FRACTAL_ZIP_LITERAL_SYNTH_MULTIPASS_MAX_SECONDS>0 to cap.
 */
public static function max_fractal_multipass_wall_seconds(): int {
	if(getenv('FRACTAL_ZIP_LITERAL_SYNTH_ZIP_PASS') !== '1') {
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
 * Env FRACTAL_ZIP_MULTIPASS_GATE_MULT: 1.0–4.0, default 1.18 (more multipass candidates vs 1.5).
 */
public static function multipass_ratio_gate_multiplier(): float {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MULTIPASS_GATE_MULT');
	if($env === false || trim((string) $env) === '') {
		$cached = 1.18;
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
			$counter = 0;
			$last_start_matches = 0;
			while($counter < strlen($string)) {
				$length_counter = strlen($string) - $counter;
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

function create_fractal_zip_markers($dir, $debug = false) {
	$this->minimum_overhead_length = 5; // <#"#>
	fractal_zip::warning_once('need the ability to have a recursive fractal zip such that the structure of what is compressed (such as a file structure or data structure) could be selectively decompressed according to what in the fractal zip we are interested in. self-extracting file could also thus be self-navigating');
	return true; // these are static (<, ", >)
	fractal_zip::recursive_get_strings_for_fractal_zip_markers($dir);
	// we want to avoid characters before 58 since this includes numbers and the dash which are used in expressing ranges
	// making the character range end at 126 is kind of arbitrary but using such a small range of ubiquitously supported characters could help in debugging by easily seeing (these printable) characters as opposed to having a 
	// larger range that could slightly facilitate better compression
	// it's also worth considering that by using rand we could get different levels of compression when doing the same job just by lucky choice of markers. 
	$character_range_start = 58;
	$character_range_end = 126;
	//$character_range_end = 96;
	//fractal_zip::warning_once('hacking the character ranges to avoid an unknown problem using single characters for all markers seems to be less problematic than using more than one character for some; N0ND0-123ND0I
	//problem is when mid marker includes left marker or right marker');
	//fractal_zip::warning_once('what happens when there are no characters in the range that don\'t appear in the files?!');
	// left marker
	$this->left_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
	$found_in_strings = true;
	while($found_in_strings) {
		$found_in_strings = false;
		$left_fractal_zip_marker_characters = str_split($this->left_fractal_zip_marker);
		foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
			if(strpos($string_for_fractal_zip_markers, $this->left_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->left_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
				break;
			}
			/*foreach($left_fractal_zip_marker_characters as $left_fractal_zip_marker_character) {
				if(strpos($string_for_fractal_zip_markers, $left_fractal_zip_marker_character) !== false) {
					$found_in_strings = true;
					$this->left_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
					//$this->left_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
					break 2;
				}
			}*/
		}
	}
	if($debug) {
		print('$this->left_fractal_zip_marker: ');var_dump($this->left_fractal_zip_marker);
	}
	// right marker
	if(strrev($this->left_fractal_zip_marker) === $this->left_fractal_zip_marker) {
		$this->right_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
		$found_in_strings = true;
	} else {
		$this->right_fractal_zip_marker = strrev($this->left_fractal_zip_marker);
		$found_in_strings = false;
		foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
			if(strpos($string_for_fractal_zip_markers, $this->right_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->right_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
				break;
			}
		}
	}
	while($found_in_strings) {
		$found_in_strings = false;
		$right_fractal_zip_marker_characters = str_split($this->right_fractal_zip_marker);
		foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
			if(strpos($string_for_fractal_zip_markers, $this->right_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->right_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
				continue 2;
			}
			/*foreach($right_fractal_zip_marker_characters as $right_fractal_zip_marker_character) {
				if(strpos($string_for_fractal_zip_markers, $right_fractal_zip_marker_character) !== false) {
					$found_in_strings = true;
					$this->right_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
					//$this->right_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
					continue 3;
				}
			}*/
		}
		if(strpos($this->left_fractal_zip_marker, $this->right_fractal_zip_marker) !== false || strpos($this->right_fractal_zip_marker, $this->left_fractal_zip_marker) !== false) {
			$found_in_strings = true;
			$this->right_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
		}
	}
	if($debug) {
		print('$this->right_fractal_zip_marker: ');var_dump($this->right_fractal_zip_marker);
	}
	// mid marker
	if($this->multipass) {
		$this->mid_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
		$found_in_strings = true;
		while($found_in_strings) {
			$found_in_strings = false;
			$mid_fractal_zip_marker_characters = str_split($this->mid_fractal_zip_marker);
			foreach($this->strings_for_fractal_zip_markers as $string_for_fractal_zip_markers) {
				if(strpos($string_for_fractal_zip_markers, $this->mid_fractal_zip_marker) !== false) {
					$found_in_strings = true;
					$this->mid_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
					continue 2;
				}
				/*foreach($mid_fractal_zip_marker_characters as $mid_fractal_zip_marker_character) {
					if(strpos($string_for_fractal_zip_markers, $mid_fractal_zip_marker_character) !== false) {
						$found_in_strings = true;
						$this->mid_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
						//$this->mid_fractal_zip_marker = chr(rand($character_range_start, $character_range_end));
						continue 3;
					}
				}*/
			}
			if(strpos($this->left_fractal_zip_marker, $this->mid_fractal_zip_marker) !== false || strpos($this->right_fractal_zip_marker, $this->mid_fractal_zip_marker) !== false || 
			strpos($this->mid_fractal_zip_marker, $this->left_fractal_zip_marker) !== false || strpos($this->mid_fractal_zip_marker, $this->right_fractal_zip_marker) !== false) {
				$found_in_strings = true;
				$this->mid_fractal_zip_marker .= chr(rand($character_range_start, $character_range_end));
			}
		}
	} else {
		$this->mid_fractal_zip_marker = '';
	}
	if($debug) {
		print('$this->mid_fractal_zip_marker: ');var_dump($this->mid_fractal_zip_marker);
	}
	if($this->multipass) {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 1 + strlen($this->mid_fractal_zip_marker) + 3 + strlen($this->mid_fractal_zip_marker) + 1 + strlen($this->right_fractal_zip_marker);
	} else {
		$this->minimum_overhead_length = strlen($this->left_fractal_zip_marker) + 3 + strlen($this->right_fractal_zip_marker);
	}
	//exit(0);
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
	$rows = array();
	$ref = null;
	foreach($flacPaths as $p) {
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
		$pcmTmp = sys_get_temp_dir() . DS . 'fzcdpcm_' . bin2hex(random_bytes(8)) . '.raw';
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
	$manifestBin = $this->encode_varint_u32(count($rows));
	$prevPath = '';
	foreach($rows as $r) {
		$p = $r['path'];
		$cpl = 0;
		$max = min(strlen($prevPath), strlen($p));
		while($cpl < $max && $prevPath[$cpl] === $p[$cpl]) {
			$cpl++;
		}
		$suf = substr($p, $cpl);
		$chunk = $this->encode_varint_u32($cpl) . $this->encode_varint_u32(strlen($suf)) . $suf;
		$chunk .= pack('V', $r['sample_rate']);
		$chunk .= chr(min(255, $r['channels'])) . chr(min(255, $r['source_bps'])) . chr($r['pcm_fmt']);
		$chunk .= pack('P', $r['len']);
		$manifestBin .= $chunk;
		$prevPath = $p;
	}
	$mergePath = sys_get_temp_dir() . DS . 'fzcdmrg_' . bin2hex(random_bytes(8)) . '.raw';
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
			$chunkParts = '';
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
				$chunkParts .= pack('P', $got) . pack('P', strlen($innerBlob));
				if($pcmPreWire) {
					$chunkParts .= chr(max(0, min(255, (int) $enc['pre'])));
				}
				$chunkParts .= $innerBlob;
				$nChunks++;
				$sumPcm += $got;
			}
			fclose($mergeIn);
			if($allChunksOk && $sumPcm === $rawTotal && $nChunks > 0) {
				$fractalInner = pack('P', $rawTotal) . pack('V', $nChunks) . $chunkParts;
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
			$rec = $this->encode_varint_u32($cpl) . $this->encode_varint_u32(strlen($suf)) . $suf;
			$rec .= chr($mode);
			$rec .= $this->encode_varint_u32(strlen($storeBytes)) . $storeBytes;
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
		$pcmAll = '';
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
			$frTmp = sys_get_temp_dir() . DS . 'fzcdexfr_' . bin2hex(random_bytes(8)) . '.fzcinner';
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
			$pcmAll .= $pcmPiece;
			$sumChunk += $cLen;
		}
		if(strlen($pcmAll) !== $rawTotal || $sumChunk !== $rawTotal) {
			fclose($fh);
			fractal_zip::fatal_error('FZCD extract: merged fractal chunked PCM total.');
		}
		$at = 0;
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . bin2hex(random_bytes(8)) . '.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . bin2hex(random_bytes(8)) . '.flac';
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
		$frTmp = sys_get_temp_dir() . DS . 'fzcdexfr_' . bin2hex(random_bytes(8)) . '.fzcinner';
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
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . bin2hex(random_bytes(8)) . '.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . bin2hex(random_bytes(8)) . '.flac';
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
		$flacTmp = sys_get_temp_dir() . DS . 'fzcdexm_' . bin2hex(random_bytes(8)) . '.flac';
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
		$pcmAllPath = sys_get_temp_dir() . DS . 'fzcdexpcm_' . bin2hex(random_bytes(8)) . '.raw';
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
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . bin2hex(random_bytes(8)) . '.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . bin2hex(random_bytes(8)) . '.flac';
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
		foreach($entries as $e) {
			$pcm = substr($pcmAll, $at, $e['pcmLen']);
			if(strlen($pcm) !== $e['pcmLen']) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: PCM slice.');
			}
			$at += $e['pcmLen'];
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . bin2hex(random_bytes(8)) . '.raw';
			if(file_put_contents($pcmTmp, $pcm) === false) {
				fclose($fh);
				fractal_zip::fatal_error('FZCD extract: temp PCM.');
			}
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . bin2hex(random_bytes(8)) . '.flac';
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
		foreach($entries as $e) {
			$pcmTmp = sys_get_temp_dir() . DS . 'fzcdex_' . bin2hex(random_bytes(8)) . '.raw';
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
			$outFlac = sys_get_temp_dir() . DS . 'fzcdexf_' . bin2hex(random_bytes(8)) . '.flac';
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
		$rel = str_replace('\\', '/', (string) $rel);
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
	$prevPath = '';
	foreach($paths as $path) {
		$path = (string) $path;
		$full = $root . DS . str_replace('/', DS, $path);
		$rawBytes = file_get_contents($full);
		if($rawBytes === false) {
			$rawBytes = '';
		}
		if($rawLiteralsOnly) {
			fractal_zip_ensure_literal_pac_stack_loaded();
			$mode = 0;
			$storeBytes = fractal_zip_literal_pac_preprocess_literal_for_bundle($path, $rawBytes);
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
		$chunk = $this->encode_varint_u32($commonPrefixLen) . $this->encode_varint_u32(strlen($pathSuffix)) . $pathSuffix;
		$chunk .= chr($mode);
		$chunk .= $this->encode_varint_u32(strlen($storeBytes)) . $storeBytes;
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
	fractal_zip::$used_folder_literal_fzb4_store = true;
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	print('literal FZB4 store-only .fzc (no outer compression).<br>');
	print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
	$this->zip_folder_root_for_members = '';
	return true;
}

function zip_folder($dir, $debug = false) {
	print('Fractal zipping folder: ' . $dir . '<br>');
	fractal_zip::$used_folder_gzip_fast = false;
	fractal_zip::$used_folder_literal_fzb4_store = false;
	fractal_zip::$used_folder_unified_stream = false;
	if(fractal_zip::folder_literal_fzb4_store_only_requested()) {
		if($this->write_folder_literal_fzb4_store_only_container($dir)) {
			return;
		}
		fractal_zip::warning_once('literal FZB4 store-only path failed; falling back to normal zip_folder');
	}
	// Large-folder fast path: literal FZB4 bundle + raw deflate outer like adaptive_compress gzip baseline (no fractal). Benchmarks
	// set FRACTAL_ZIP_FOLDER_GZIP_FAST=1 for heavy corpora; auto also when raw size >= FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES.
	if($this->should_use_large_folder_gzip_fast_path($dir)) {
		$resolvedRoot = realpath($dir);
		$this->zip_folder_root_for_members = $resolvedRoot !== false ? $resolvedRoot : $dir;
		$tmpInner = sys_get_temp_dir() . DS . 'fzfzb_' . bin2hex(random_bytes(8));
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
					if(file_put_contents($fzcPath, $bestContents) !== false) {
						@unlink($tmpInner);
						$gzipFastDone = true;
						fractal_zip::$used_folder_gzip_fast = true;
						$micro_time_taken = microtime(true) - $this->initial_micro_time;
						print('large-folder gzip-fast path: streamed literal inner + phase-3 adaptive outer (inner ≤ ' . $maxAd . ' B; pick=' . htmlspecialchars($gfPick) . ').<br>');
						print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
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
					fractal_zip::$used_folder_gzip_fast = true;
					$micro_time_taken = microtime(true) - $this->initial_micro_time;
					print('large-folder gzip-fast path (streaming literal inner + raw deflate-' . $dlev . ' outer).<br>');
					print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
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
		$resolvedRoot = realpath($dir);
		$this->zip_folder_root_for_members = $resolvedRoot !== false ? $resolvedRoot : $dir;
		$rootForCollect = $resolvedRoot !== false ? $resolvedRoot : $dir;
		$rawFilesByPath = $this->collect_raw_files_for_bundle($dir);
		$variants = fractal_zip::folder_literal_variants_for_unified_stream(
			$this->collect_literal_bundle_inner_variants_for_folder($rootForCollect, $rawFilesByPath)
		);
		if($variants !== array()) {
			list($bestContents, $unifiedPick) = $this->choose_smallest_adaptive_literal_inner_or_raw_escaped($variants, $rawFilesByPath);
			file_put_contents($dir . $this->fractal_zip_container_file_extension, $bestContents);
			fractal_zip::$used_folder_unified_stream = true;
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			if($unifiedPick === 'raw') {
				print('unified stream path: raw escaped-per-file beat literal candidate(s) after adaptive outer.<br>');
			} elseif($unifiedPick === 'fzcd') {
				print('unified stream path: FZCD merged-FLAC inner won vs raw after adaptive outer.<br>');
			} elseif($unifiedPick === 'fzb') {
				print('unified stream path: FZB literal inner (unwrap + transforms; FZBD auto; optional FZWS) won vs raw after adaptive outer.<br>');
			} else {
				print('unified stream path: literal inner tag=' . htmlspecialchars($unifiedPick) . ' after adaptive outer.<br>');
			}
			print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
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
			print('Auto-selected segment length: ' . $this->segment_length . '<br>');
		}
		ini_set('xdebug.max_nesting_level', $this->segment_length + 30);
		$this->auto_segment_selection_in_progress = false;
	}
	if(!$bundleOnlyByShape && !$this->auto_multipass_selection_in_progress && $this->auto_multipass_selection_enabled && !$this->auto_segment_selection_in_progress) {
		$this->auto_multipass_selection_in_progress = true;
		$this->multipass = $this->choose_best_multipass_for_folder($dir, $debug);
		$mpMax = $this->multipass_max_additional_passes;
		$mpMaxStr = ($mpMax === null) ? 'unlimited' : (string) (int) $mpMax;
		print('Auto-selected multipass: ' . ($this->multipass ? ('on (max extra passes ' . $mpMaxStr . ')') : 'off') . '<br>');
		$this->auto_multipass_selection_in_progress = false;
	}
	$resolvedRoot = realpath($dir);
	$this->zip_folder_root_for_members = $resolvedRoot !== false ? $resolvedRoot : $dir;
	if($bundleOnlyByShape) {
		$rawFilesByPath = $this->collect_raw_files_for_bundle($dir);
		$variants = fractal_zip::folder_literal_variants_for_unified_stream(
			$this->collect_literal_bundle_inner_variants_for_folder($this->zip_folder_root_for_members, $rawFilesByPath)
		);
		if($variants !== array()) {
			list($bestContents, $shapePick) = $this->choose_smallest_adaptive_literal_inner_or_raw_escaped($variants, $rawFilesByPath);
			file_put_contents($dir . $this->fractal_zip_container_file_extension, $bestContents);
			$micro_time_taken = microtime(true) - $this->initial_micro_time;
			if($shapePick === 'raw') {
				print('bundle-only shape path: raw escaped-per-file beat literal candidate(s) after adaptive outer.<br>');
			} elseif($shapePick === 'fzcd') {
				print('bundle-only shape path: FZCD inner won vs raw after adaptive outer.<br>');
			} elseif($shapePick === 'fzb') {
				print('bundle-only shape path: FZB literal inner won vs raw after adaptive outer.<br>');
			} else {
				print('bundle-only shape path: pick=' . htmlspecialchars($shapePick) . ' after adaptive outer.<br>');
			}
			print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
			$this->zip_folder_root_for_members = '';
			return;
		}
	}
	$this->fractal_member_gzip_disk_restore = array();
	$this->raster_canonical_hash_to_lazy_range = array();
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
	$literal_best_contents = '';
	$literal_best_tag = '';
	$codecLiteral = fractal_zip::$last_outer_codec;
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
	$fzc_contents = $this->adaptive_compress($fzc_contents);
	$codecFractal = fractal_zip::$last_outer_codec;
	$lazy_fzc_contents = $this->adaptive_compress($lazy_fzc_contents);
	$codecLazy = fractal_zip::$last_outer_codec;
	if($debug) {
		print('$fzc_contents: ');var_dump($fzc_contents);
		print('$lazy_fzc_contents: ');var_dump($lazy_fzc_contents);
		$raw_dbg = $this->adaptive_compress($raw_payload_uncompressed);
		print('$raw_fzc_contents: ');var_dump($raw_dbg);
		print('$literal_best tag, len: ');var_dump($literal_best_tag, strlen($literal_best_contents));
	}
	//$last_folder_name = substr($dir, fractal_zip::strpos_last($dir, DS));
	//print('$dir, $last_folder_name: ');var_dump($dir, $last_folder_name);
	$bestLabel = 'fractal';
	$bestPayload = $fzc_contents;
	$bestCodec = $codecFractal;
	$bestLen = strlen($fzc_contents);
	if(strlen($lazy_fzc_contents) < $bestLen) {
		$bestLabel = 'lazy';
		$bestPayload = $lazy_fzc_contents;
		$bestCodec = $codecLazy;
		$bestLen = strlen($lazy_fzc_contents);
	}
	if($literal_variants !== array() && $literal_best_contents !== '' && strlen($literal_best_contents) < $bestLen) {
		$bestLabel = 'literal';
		$bestPayload = $literal_best_contents;
		$bestCodec = $codecLiteral;
		$bestLen = strlen($literal_best_contents);
	}
	fractal_zip::$last_written_container_codec = $bestCodec;
	if($bestLabel === 'fractal') {
		$litTxt = ($literal_variants !== array() && $literal_best_contents !== '') ? (string)strlen($literal_best_contents) : 'n/a';
		print('<span style="color: green;">fractal zipping was actually useful (' . strlen($fzc_contents) . ' &#8804; min(' . strlen($lazy_fzc_contents) . ', literal_best=' . $litTxt . '))!</span><br>');
	} elseif($bestLabel === 'lazy') {
		print('simply compressing the strings made the smallest file among fractal/lazy/literal contest.<br>');
	} else {
		print('literal contest won (FZCD/FZB/FZBM/FZBF/raw vs fractal/lazy); tag=' . htmlspecialchars($literal_best_tag) . '.<br>');
	}
	file_put_contents($dir . $this->fractal_zip_container_file_extension, $bestPayload);
	$micro_time_taken = microtime(true) - $this->initial_micro_time;
	print('Time taken zipping folder: ' . $micro_time_taken . ' seconds.<br>');
	$this->zip_folder_root_for_members = '';
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
 * @return array<string,string> relative_path => raw_bytes
 */
function collect_raw_files_for_bundle($dir) {
	$out = array();
	$iterRoot = realpath($dir);
	if($iterRoot === false) {
		$iterRoot = $dir;
	}
	$rootN = rtrim(str_replace('\\', '/', $iterRoot), '/');
	$prefix = $rootN . '/';
	$prefixLen = strlen($prefix);
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($iterRoot, FilesystemIterator::SKIP_DOTS));
	foreach($it as $fileInfo) {
		if(!$fileInfo->isFile()) {
			continue;
		}
		$pathReal = $fileInfo->getRealPath();
		$pathN = str_replace('\\', '/', $pathReal !== false ? $pathReal : $fileInfo->getPathname());
		if(strlen($pathN) < $prefixLen || substr($pathN, 0, $prefixLen) !== $prefix) {
			fractal_zip::warning_once('collect_raw_files_for_bundle: skipping path outside bundle root.');
			continue;
		}
		$rel = substr($pathN, $prefixLen);
		$bytes = file_get_contents($fileInfo->getPathname());
		if($bytes === false) {
			$bytes = '';
		}
		$out[$rel] = (string)$bytes;
	}
	ksort($out, SORT_STRING);
	return $out;
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
	$tmpPath = sys_get_temp_dir() . DS . 'fzfzin_' . bin2hex(random_bytes(8));
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
	$tmpPath = fractal_zip::outer_codec_temp_dir() . DS . 'fzXZn_' . bin2hex(random_bytes(8));
	$xzExe = fractal_zip::xz_executable();
	if($xzExe === null || !is_file($srcPath) || !function_exists('proc_open')) {
		$tmpPath = '';
		return false;
	}
	$cmd = escapeshellarg($xzExe) . ' -d -c ' . escapeshellarg($srcPath);
	$desc = array(
		0 => array('pipe', 'r'),
		1 => array('file', $tmpPath, 'wb'),
		2 => array('pipe', 'w'),
	);
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
	if(strlen($head) >= strlen($bMagic) && substr($head, 0, strlen($bMagic)) === $bMagic) {
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
	if(strpos($string, '<') !== false) {
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
	$shorthand_counter = 1;
	$saved_shorthand = array();
	$counter = 0;
	$unzipped_string = '';
	//$string_fragment = '';
	$branch_counter = $this->branch_counter;
	while($branch_counter > -1) {
		while($counter < strlen($string)) {
			if(substr($string, $counter, strlen($this->left_fractal_zip_marker)) === $this->left_fractal_zip_marker) {
				$counter += strlen($this->left_fractal_zip_marker);
				if($this->multipass) {
					$string_fragment = $this->left_fractal_zip_marker;
					$left_branch_counter = '';
					while($counter < strlen($string)) {
						if(substr($string, $counter, strlen($this->mid_fractal_zip_marker)) === $this->mid_fractal_zip_marker) {
							$counter += strlen($this->mid_fractal_zip_marker);
							if($left_branch_counter == $this->branch_counter) {
								
							} else {
								$string_fragment .= $this->mid_fractal_zip_marker;
								$unzipped_string .= $string_fragment;
								continue 2;
							}
							$unzipped_string .= $saved_shorthand[$shorthand_number];
							if(substr($string, $counter, strlen($this->range_shorthand_marker)) === $this->range_shorthand_marker) { // short-hand range
								$counter += strlen($this->range_shorthand_marker);
								$string_fragment .= $this->range_shorthand_marker;
								$shorthand_number = '';
								while($counter < strlen($string)) {
									if(substr($string, $counter, strlen($this->mid_fractal_zip_marker)) === $this->mid_fractal_zip_marker) {
										$counter += strlen($this->mid_fractal_zip_marker);
										$string_fragment .= $this->mid_fractal_zip_marker;
										$right_branch_counter = '';
										while($counter < strlen($string)) {
											if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
												$counter += strlen($this->right_fractal_zip_marker);
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
								while($counter < strlen($string)) {
									if(substr($string, $counter, strlen($this->mid_fractal_zip_marker)) === $this->mid_fractal_zip_marker) {
										$counter += strlen($this->mid_fractal_zip_marker);
										$string_fragment .= $this->mid_fractal_zip_marker;
										$right_branch_counter = '';
										while($counter < strlen($string)) {
											if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
												$counter += strlen($this->right_fractal_zip_marker);
												$string_fragment .= $this->right_fractal_zip_marker;
												if($left_branch_counter == $right_branch_counter) {
													$range_string_array = explode('-', $range_string);
													$start_offset = $range_string_array[0];
													$end_offset = $range_string_array[1];
													$unzipped_piece = substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
													$unzipped_string .= $unzipped_piece;
													$saved_shorthand[(string)$shorthand_counter] = $unzipped_piece;
													$shorthand_counter++;
													$counter += strlen($this->right_fractal_zip_marker);
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
					if(substr($string, $counter, strlen($this->range_shorthand_marker)) === $this->range_shorthand_marker) { // short-hand range
						$counter += strlen($this->range_shorthand_marker);
						$shorthand_number = '';
						while($counter < strlen($string)) {
							if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
								//print('$saved_shorthand, $saved_shorthand[$shorthand_number], $shorthand_number: ');var_dump($saved_shorthand, $saved_shorthand[$shorthand_number], $shorthand_number);
								$shorthand_number = (int)$shorthand_number;
								$unzipped_string .= $saved_shorthand[$shorthand_number];
								$counter += strlen($this->right_fractal_zip_marker);
								continue 2;
							} else {
								$shorthand_number .= $string[$counter];
							}
							$counter++;
						}
					} else {
						$range_string = '';
						while($counter < strlen($string)) {
							if(substr($string, $counter, strlen($this->right_fractal_zip_marker)) === $this->right_fractal_zip_marker) {
								$range_string_array = explode('-', $range_string);
								$start_offset = $range_string_array[0];
								$end_offset = $range_string_array[1];
								$unzipped_piece = substr($this->fractal_string, $start_offset, $end_offset - $start_offset + 1);
								$unzipped_string .= $unzipped_piece;
								$saved_shorthand[$shorthand_counter] = $unzipped_piece;
								$shorthand_counter++;
								$counter += strlen($this->right_fractal_zip_marker);
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
	$initial_equivalence_string = $equivalence_string;
	if(preg_match('/<[0-9]/s', $equivalence_string) !== 1) {
		return $equivalence_string;
	}
	if(!fractal_zip::is_fractally_clean_for_unzip($equivalence_string)) {
		fractal_zip::warning_once('substring unzip: equivalence string failed bracket balance/order check; skipping substring expansion (may contain literal < > from binary data).');
		return $initial_equivalence_string;
	}
	$flen = strlen($fractal_string);
	$debug_counter = 0;
	$maxSubstrIters = max(2000, min(200000, strlen($equivalence_string) * 16 + 4096));
	while(preg_match('/<[0-9]/is', $equivalence_string) === 1) {
		if($debug_counter >= $maxSubstrIters) {
			fractal_zip::warning_once('recursive_substring_replace: iteration cap exceeded (likely binary false positives or very deep markers); returning partial expansion.');
			return $equivalence_string;
		}
		$debug_counter++;
		//preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE);
		if(preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $equivalence_string, $substring_operation_matches, PREG_OFFSET_CAPTURE) !== 1) {
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
					$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '<$1"$2"' . ($substring_recursion_counter - 1) . '>', $substring);
					//print('$equivalence_string after processing a substring operation: ');var_dump($equivalence_string);
			//		$processed_a_subtring_operation = true;
				} else {
					//print('uhhh0004<br>');
					$substring = preg_replace('/<([0-9]+)"([0-9]+)>/is', '', $substring);
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
			print('$row_length, $equivalence_string: ');var_dump($row_length, $equivalence_string);
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
					print('$new_string, $equivalence_string before skipping: ');var_dump($new_string, $equivalence_string);
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
						print('$rows: ');fractal_zip::var_dump_full($rows);
						$equivalence_string = substr($equivalence_string, $position);
					}
					$new_string .= implode('', $rows);
					print('$new_string at the end of skipping: ');var_dump($new_string);
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
	$equivalence_original = ($equivalence_original_override !== null) ? $equivalence_original_override : $string;
	$this->entry_filename = $entry_filename;
	print('zipping: ' . $entry_filename . '<br>');
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
		$limiters_sum = 0;
		foreach($this->common_limiters as $this->common_limiter) {
			$limiters_sum += substr_count($string, $this->common_limiter);
		}
		print('$limiters_sum, strlen($string): ');fractal_zip::var_dump_full($limiters_sum, strlen($string));
		if($limiters_sum / strlen($string) > 0.02 && $limiters_sum / strlen($string) < 0.2) {
			print('handling this file by breaking at the limiters<br>');
			$counter = 0;
			$offsets_to_split_at = array();
			while($counter < strlen($string)) {
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
			while($counter < strlen($string)) {
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
	// Escape only characters that can create false range markers or decode ambiguity.
	// We intentionally leave `>` and quotes untouched to reduce HTML-heavy inflation.
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
			$lazy_zipped_string = '<' . $roff . '"' . $rlen . '>';
			$this->lazy_equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
			$this->fractal_string = $this->lazy_fractal_string;
			$this->equivalences[] = array($equivalence_original, $entry_filename, $lazy_zipped_string);
			return true;
		}
	}
	$offBefore = strlen($this->lazy_fractal_string);
	$this->lazy_fractal_string .= $zipped_string;
	$lazy_zipped_string = '<' . $offBefore . '"' . strlen($zipped_string) . '>';
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
	$this->final_fractal_replace = $replace;
	//if(substr_count($replace, '<') > 1) {
	//	fractal_zip::fatal_error('substr_count($replace, \'<\') > 1');
	//}
	$initial_string = $string;
	$initial_offset = $offset;
	$initial_post_offset = $offset + strlen($search);
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $replace, $tag_in_replace_matches, PREG_OFFSET_CAPTURE);
	// [4] is offset adjustment
	// [5] is length adjustment
	foreach($tag_in_replace_matches[0] as $index => $value) {
		$tag_in_replace_matches[4][$index] = 0;
		$tag_in_replace_matches[5][$index] = 0;
	}
	//print('$tag_in_replace_matches: ');var_dump($tag_in_replace_matches);
	$pre = substr($string, 0, $offset);
	//print('$string after replace in fractal_replace:');var_dump($string);
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $pre, $tag_in_pre_matches, PREG_OFFSET_CAPTURE);
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
			$new_tag_in_pre_operation = '<' . $new_tag_in_pre_offset . '"' . $new_tag_in_pre_length . '"' . $tag_in_pre_recursion . '>';
		} else {
			$new_tag_in_pre_operation = '<' . $new_tag_in_pre_offset . '"' . $new_tag_in_pre_length . '>';
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
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)>/is', $string, $tag_in_post_matches, PREG_OFFSET_CAPTURE, $post_offset);
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
			$new_tag_in_post_operation = '<' . $new_tag_in_post_offset . '"' . $new_tag_in_post_length . '"' . $tag_in_post_recursion . '>';
		} else {
			$new_tag_in_post_operation = '<' . $new_tag_in_post_offset . '"' . $new_tag_in_post_length . '>';
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
			$new_tag_in_replace_operation = '<' . $new_tag_in_replace_offset . '"' . $new_tag_in_replace_length . '"' . $tag_in_replace_recursion . '>';
		} else {
			$new_tag_in_replace_operation = '<' . $new_tag_in_replace_offset . '"' . $new_tag_in_replace_length . '>';
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
	$counter = 0;
	if(!isset($this->fractal_substrings_array[$string[0]])) {
		//return $string[0];
		$counter++;
	} else {
		$fractal_substrings_array = $this->fractal_substrings_array;
		//$minimally_new_substr = '';
		while($counter < strlen($string)) {
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
	while(!fractal_zip::is_fractally_clean($minimally_new_substr) && $counter < strlen($string)) {
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
	$maxFractal = fractal_zip::max_fractal_analysis_bytes();
	if($maxFractal > 0 && strlen($string) > $maxFractal) {
		return array();
	}
	$slen = strlen($string);
	$coarseCfg = fractal_zip::fractal_substring_coarse_config_for_length($slen);
	$counter = 0;
	$minimum_counter_skip = 1;
	$substr_records = array();
	$minimum_substr_length = fractal_zip::maximum_substr_expression_length();
	$length_string = (string) $slen;
	$multiple = 15 - strlen($length_string);
	if($multiple < 3) {
		$multiple = 3;
	}
	if($coarseCfg['enabled'] && $coarseCfg['multiple_cap'] > 0) {
		$multiple = min($multiple, $coarseCfg['multiple_cap']);
	}
	$baseMaxExpr = fractal_zip::maximum_substr_expression_length();
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
	while($counter < strlen($string) - $minimum_substr_length) {
		if(sizeof($substr_records) > $maxSubstrRecordEntries) {
			break;
		}
		//$best_substr = false;
		$counter_skip = 1;
		$sliding_counter = $minimum_substr_length;
		//while(($sliding_counter < $this->segment_length || $string[$counter + $sliding_counter + 1] === $string[$counter + $sliding_counter]) && $counter + $sliding_counter < strlen($string)) {
		while(($sliding_counter < $maximum_substr_length || $string[$counter + $sliding_counter + 1] === $string[$counter + $sliding_counter]) && $counter + $sliding_counter < strlen($string)) {
			if($string[$counter + $sliding_counter + 1] === $string[$counter + $sliding_counter]) {
				
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
		$counter = 0;
		//$saved_substr_count = -1;
		$saved_substr = '';
		$substr_records = array();
		while($counter < strlen($string) - fractal_zip::maximum_substr_expression_length()) {
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
			$piece = substr($string, $sliding_counter, fractal_zip::maximum_substr_expression_length()); // not sure if this shortcut is useful now that we are recursing
			while(strlen(fractal_zip::tagless($piece)) < fractal_zip::maximum_substr_expression_length() && $sliding_counter < strlen($string)) {
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
			while(!fractal_zip::is_fractally_clean($piece) && $sliding_counter < strlen($string)) {
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
				while($sliding_counter < strlen($string)) {
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
		$maxFractal = fractal_zip::max_fractal_analysis_bytes();
		if($maxFractal > 0 && strlen($string) > $maxFractal) {
			return array($fractal_string . $string, '<' . strlen($fractal_string) . '"' . strlen($string) . '>');
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
		return array($this->fractal_string . $string, '<' . strlen($this->fractal_string) . '"' . strlen($string) . '>'); // so that subsequent files can work from this file's data
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
	$lazy_zipped_string = '<' . strlen($fractal_string) . '"' . strlen($string) . '>';
	$this->array_fractal_zipped_strings_of_files = array();
	//$this->equivalences[] = array($string, $entry_filename, $zipped_string);
	foreach($this->equivalences as $equivalence) {
		$this->array_fractal_zipped_strings_of_files[$equivalence[1]] = $equivalence[2];
	}
	$this->array_fractal_zipped_strings_of_files[$this->entry_filename] = $lazy_zipped_string;
	$lazy_fzc_contents = $this->encode_container_payload($this->array_fractal_zipped_strings_of_files, $lazy_fractal_string);
	//$lazy_fzc_contents = gzcompress($lazy_fzc_contents, 9);
	$lazy_fzc_contents = $this->adaptive_compress($lazy_fzc_contents);
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
		$compressed_score = strlen($lazy_fzc_contents) / strlen($potential_fzc_contents);
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
		return array($fractal_string . $string, '<' . strlen($fractal_string) . '"' . strlen($string) . '>'); // so that subsequent files can work from this file's data
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
		if($maxFractalRec > 0 && strlen($string) > $maxFractalRec) {
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
		
		if(strpos($string, '<"') !== false) { // debug
			print('$string: ');var_dump($string);
			fractal_zip::fatal_error('strpos($string, \'<"\') !== false 1');
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
					$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '"' . ($recursion_counter + 1) . '>';
				} else {
					$marked_range_string = '<' . $match_position . '"' . $this->length_including_operations . '>';
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
				$score = strlen($this->string) / (strlen($string) + strlen($fractal_string)); // crude?
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
				if(strlen($initial_string) - strlen($string) > fractal_zip::maximum_substr_expression_length() && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
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
			if(strpos($string, '<"') !== false) { // debug
				fractal_zip::fatal_error('strpos($string, \'<"\') !== false');
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
						if(preg_match('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $replace, $matches, PREG_OFFSET_CAPTURE, $replace_offset)) { // would a parser be faster? optimize later
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
								$recursion_part = '"' . $substring_recursion_counter;
							} else {
								$recursion_part = '';
							}
							if($substring_tuple > 1) {
								$tuple_part = '*' . $substring_tuple;
							} else {
								$tuple_part = '';
							}
							if($substring_scale > 1) {
								$scale_part = 's' . $substring_scale;
							} else {
								$scale_part = '';
							}
							
							//print('$new_offset, $new_length, $recursion_part, $tuple_part, $scale_part: ');var_dump($new_offset, $new_length, $recursion_part, $tuple_part, $scale_part);
							$new_operation = '<' . $new_offset . '"' . $new_length . $recursion_part . $tuple_part . $scale_part . '>';
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
					if(strpos($replace, '<"') !== false) { // debug
						print('$fractal_string, $piece, $search, $recursion_marked_piece, $replace: ');var_dump($fractal_string, $piece, $search, $recursion_marked_piece, $replace);
						fractal_zip::fatal_error('strpos($replace, \'<"\') !== false');
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
					if(preg_match('/<([0-9]+)"([0-9]+)"([0-9])/is', $fractal_string, $recursion_marker_in_fractal_string_matches)) { // debug
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
						$marked_range_string = '<' . $match_position . '"' . strlen($piece) . '"' . ($recursion_counter + 1) . '>';
					} else {
						$marked_range_string = '<' . $match_position . '"' . strlen($piece) . '>';
					}
					//$real_string_match_position = fractal_zip::strpos_ignoring_operations($string, $piece);
					//$recursion_marked_piece = substr($string, $string_match_position, $this->length_including_operations);
					//print('$recursion_marked_piece, $marked_range_string, $string 33: ');var_dump($recursion_marked_piece, $marked_range_string, $string);
					$string = str_replace($recursion_marked_piece, $marked_range_string, $string);
					fractal_zip::warning_once('tuples disabled since not all code expects them, example: aaaaa<20"25"4>aaaaaaaa, aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb');
				//	$string = fractal_zip::tuples($string, $marked_range_string);
					//print('$string 44: ');var_dump($string);
					$path[] = $piece;
					$score = strlen($this->string) / (strlen($string) + strlen($fractal_string)); // crude?
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
					if(strlen($initial_string) - strlen($string) > fractal_zip::maximum_substr_expression_length() && $score > fractal_zip::average($scores) && $this->silent_validate($string, $fractal_string, $this->string)) {
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
					$score = strlen($this->string) / (strlen($string) + strlen($fractal_string)); // crude?
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
	preg_match_all('/<([0-9]+)"([0-9]+)"*([0-9]*)\**([0-9]*)s*([0-9\.]*)>/is', $string, $matches, PREG_OFFSET_CAPTURE); // would a parser be faster? optimize later
	//print('$matches in simple_substring_expressions_only: ');var_dump($matches);
	$counter = sizeof($matches[0]) - 1;
	while($counter > -1) {
		$substring_offset = $matches[1][$counter][0];
		$substring_length = $matches[2][$counter][0];
		//$substring_recursion_counter = $matches[3][0]; // what should be the order of the following markers?
		//$substring_tuple = $matches[4][0];
		//$substring_scale = $matches[5][0];
		$string = substr($string, 0, $matches[0][$counter][1]) . '<' . $substring_offset . '"' . $substring_length . '>' . substr($string, $matches[0][$counter][1] + strlen($matches[0][$counter][0]));
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
	//print('tuple1<br>');
	while($counter > -1) {
		//print('tuple2<br>');
		if($matches[0][$counter - 1][1] === $matches[0][$counter][1] - strlen($operation)) {
			//print('tuple3<br>');
			$tuple++;
		} elseif($tuple > 1) {
			//print('tuple4<br>');
			$tupled_operation = str_replace('>', '*' . $tuple . '>', $operation);
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
	$firstLt = strpos($piece, '<');
	$firstGt = strpos($piece, '>');
	if($firstLt !== false && $firstGt !== false && $firstGt < $firstLt) {
		return false;
	}
	$lastLt = fractal_zip::strpos_last($piece, '<');
	$lastGt = fractal_zip::strpos_last($piece, '>');
	if($lastLt !== false && $lastGt !== false && $lastGt < $lastLt) {
		return false;
	}
	if(substr_count($piece, '>') !== substr_count($piece, '<')) {
		return false;
	}
	return true;
}

function is_fractally_clean($piece) { // pretty hacky
	if(!fractal_zip::bracket_delimiters_well_ordered($piece)) { // questionably throw out partial operators
		return false;
	} elseif(substr_count($piece, '>') === 1 && substr_count($piece, '<') === 1 && strpos($piece, '<') === 0 && strpos(strrev($piece), '>') === 0) { // questionably throw out pieces that are only a single operator
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
	//print('ifc0001<br>');
	while($offset < strlen($piece)) {
		//print('ifc0002<br>');
		if($piece[$offset] === '<') {
			//print('ifc0003<br>');exit(0);
			if($current_bracket === '<') {
				//print('ifc0004<br>');exit(0);
				return false;
			} else {
				//print('ifc0005<br>');exit(0);
				$current_bracket = '<';
			}
		} elseif($piece[$offset] === '>') {
			//print('ifc0006<br>');exit(0);
			if($current_bracket === '>') {
				//print('ifc0007<br>');exit(0);
				return false;
			} else {
				//print('ifc0008<br>');exit(0);
				$current_bracket = '>';
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
	$needle = fractal_zip::tagless($needle);
	$needle_offset = 0;
	while($offset < strlen($haystack)) {
		$haystack_offset = $offset;
		$in_operation = false;
		//print('sp001<br>');
		while($haystack_offset < strlen($haystack) && ($in_operation || $haystack[$haystack_offset] === '<' || ($needle_offset < strlen($needle) && $haystack[$haystack_offset] === $needle[$needle_offset]))) {
			//print('sp002<br>');
			if($in_operation) {
				//print('sp003<br>');
				if($haystack[$haystack_offset] === '>') {
					//print('sp004<br>');
					$in_operation = false;
				} 
			} elseif($haystack[$haystack_offset] === '<') {
				//print('sp005<br>');
				$in_operation = true;
			} else {
				//print('sp006<br>');
				$needle_offset++;
				if($needle_offset === strlen($needle)) {
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
	return '<' . $start_offset . '"' . $end_offset . '>';
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
	if(substr($string, strlen($string) - 1) === ',') {
		$string = substr($string, 0, strlen($string) - 1);
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

function command_prefix_with_local_lib() {
	$home = getenv('HOME');
	$ld = getenv('LD_LIBRARY_PATH');
	if(!is_string($home) || $home === '') {
		return '';
	}
	$localLib = $home . '/.local/lib';
	if(!is_dir($localLib)) {
		return '';
	}
	$merged = $localLib . (($ld !== false && $ld !== '') ? (':' . $ld) : '');
	return 'LD_LIBRARY_PATH=' . escapeshellarg($merged) . ' ';
}

/**
 * @return list<string> 7-Zip -m0 methods to try (multiple LZMA2 profiles + PPMd on text-friendly payloads).
 * @param bool $literalBundleHuge true when inner is a large FZB literal bundle (CSV/BMP scale); adds higher-order PPMd trials.
 *   For huge FZB inners, PPMd is listed before LZMA2 so outer_7z_best_blob's early-exit (first 7z result under the zstd/gzip
 *   budget) cannot skip PPMd — on multi-MiB bundles PPMd often beats LZMA2 by a wide margin.
 */
function outer_7z_m0_candidates($innerLen, $literalBundleHuge = false) {
	// Speed-first mode: reduce outer 7z search fanout.
	if(getenv('FRACTAL_ZIP_SPEED') === '1') {
		return array('lzma2');
	}
	$lzma = array();
	if($innerLen > 393216) {
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
	if($innerLen >= 4096 && getenv('FRACTAL_ZIP_SKIP_PPMD') !== '1') {
		$ppmd[] = 'PPMd:o=10';
		if($literalBundleHuge && $innerLen >= 131072) {
			$ppmd[] = 'PPMd:o=12';
		}
	}
	if($literalBundleHuge) {
		$ppmdOnlyEnv = getenv('FRACTAL_ZIP_LITERAL_HUGE_7Z_PPMD_ONLY');
		$ppmdOnly = ($ppmdOnlyEnv === false || trim((string)$ppmdOnlyEnv) === '') ? true : ((int)$ppmdOnlyEnv !== 0);
		$ppmdOnlyMinEnv = getenv('FRACTAL_ZIP_LITERAL_HUGE_7Z_PPMD_ONLY_MIN_INNER_BYTES');
		$ppmdOnlyMin = ($ppmdOnlyMinEnv === false || trim((string)$ppmdOnlyMinEnv) === '') ? 4000000 : max(0, (int)$ppmdOnlyMinEnv);
		if($ppmdOnly && $ppmdOnlyMin > 0 && $innerLen >= $ppmdOnlyMin && sizeof($ppmd) > 0) {
			return array_values(array_unique($ppmd));
		}
		return array_values(array_unique(array_merge($ppmd, $lzma)));
	}
	return array_values(array_unique(array_merge($lzma, $ppmd)));
}

/**
 * Try one 7z pack; large inners use a temp file first (no huge single fwrite to 7z stdin). Stdin path uses chunked writes.
 * Falls back to temp file + exec if proc_open stdin fails.
 */
function outer_7z_single_attempt($seven, $string, $arc, $m0, $siName) {
	if(is_file($arc)) {
		unlink($arc);
	}
	$n = strlen($string);
	$fileFirstMin = fractal_zip::outer_7z_file_first_min_bytes();
	$prefix = $this->command_prefix_with_local_lib();
	$ok = false;
	if($fileFirstMin > 0 && $n >= $fileFirstMin) {
		$innerPath = $this->program_path . DS . 'fzi_' . bin2hex(random_bytes(4)) . $this->fractal_zip_file_extension;
		if(@file_put_contents($innerPath, $string) !== false) {
			$cmdFile = escapeshellarg($seven) . ' a -t7z -mx=9 -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' ' . escapeshellarg($innerPath);
			@exec($prefix . $cmdFile . ' 2>/dev/null', $output, $return);
			if(is_file($innerPath)) {
				unlink($innerPath);
			}
			$ok = ($return === 0 && is_file($arc));
		}
		if(!$ok && is_file($arc)) {
			unlink($arc);
		}
	}
	$cmdStdin = escapeshellarg($seven) . ' a -t7z -mx=9 -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' ' . escapeshellarg($siName);
	$desc = array(
		0 => array('pipe', 'r'),
		1 => array('pipe', 'w'),
		2 => array('pipe', 'w'),
	);
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
			fclose($pipes[1]);
			fclose($pipes[2]);
			$ok = (proc_close($proc) === 0 && is_file($arc));
		}
	}
	if(!$ok) {
		if(is_file($arc)) {
			unlink($arc);
		}
		$innerPath = $this->program_path . DS . 'fzi_' . bin2hex(random_bytes(4)) . $this->fractal_zip_file_extension;
		file_put_contents($innerPath, $string);
		$cmdFile = escapeshellarg($seven) . ' a -t7z -mx=9 -m0=' . $m0 . ' -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' ' . escapeshellarg($innerPath);
		@exec($prefix . $cmdFile . ' 2>/dev/null', $output, $return);
		if(is_file($innerPath)) {
			unlink($innerPath);
		}
		$ok = ($return === 0 && is_file($arc));
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
	$u = bin2hex(random_bytes(4));
	$arc = $this->program_path . DS . 'fztmp_' . $u . '_lzma2' . $this->fractal_zip_container_file_extension;
	return $this->outer_7z_single_attempt($seven, $string, $arc, 'lzma2', '-si');
}

/**
 * Try each -m0 method; return smallest .7z payload (or null if all fail).
 * @param bool $literalBundleHuge pass true for large FZB* inners (richer PPMd grid).
 */
function outer_7z_best_blob($seven, $string, $innerLen, $maxLenToBeat = null, $literalBundleHuge = false) {
	$best = null;
	$bestLen = PHP_INT_MAX;
	$maxLen = $maxLenToBeat === null ? null : max(0, (int)$maxLenToBeat);
	$u = bin2hex(random_bytes(4));
	$idx = 0;
	// Use unnamed stdin member for minimal 7z header overhead.
	$siName = '-si';
	foreach($this->outer_7z_m0_candidates($innerLen, $literalBundleHuge) as $m0) {
		$arc = $this->program_path . DS . 'fztmp_' . $u . '_' . $idx . $this->fractal_zip_container_file_extension;
		$idx++;
		$blob = $this->outer_7z_single_attempt($seven, $string, $arc, $m0, $siName);
		if($blob !== null && strlen($blob) < $bestLen) {
			$best = $blob;
			$bestLen = strlen($blob);
			// Early exit once we beat the current best from other codecs.
			if($maxLen !== null && $bestLen <= $maxLen) {
				break;
			}
		}
	}
	return $best;
}

/**
 * Single FreeArc outer attempt (-m1..9). Used by outer_arc_blob (may run multiple methods).
 * $extraArgv is appended after -ep1 (e.g. " -mx"). The inner member is stored as a short name ("i") under a per-call temp dir to minimize archive-directory overhead vs long .fractalzip paths.
 * @return string|null
 */
function outer_arc_blob_single($arcExe, $string, $methodNum, $extraArgv = '') {
	$methodNum = max(1, min(9, (int)$methodNum));
	$extra = is_string($extraArgv) ? $extraArgv : '';
	$u = bin2hex(random_bytes(4));
	$workDir = $this->program_path . DS . 'fzao_' . $u;
	if(!@mkdir($workDir, 0755, true)) {
		return null;
	}
	$arcBase = 'o.arc';
	$innerBase = 'i';
	$innerPath = $workDir . DS . $innerBase;
	$arcPath = $workDir . DS . $arcBase;
	file_put_contents($innerPath, $string);
	$prefix = $this->command_prefix_with_local_lib();
	$cwd = getcwd();
	if(!@chdir($workDir)) {
		if(is_file($innerPath)) {
			unlink($innerPath);
		}
		@rmdir($workDir);
		return null;
	}
	$cmd = $prefix . escapeshellarg($arcExe) . ' a -m' . $methodNum . ' -ep1' . $extra . ' -y ' . escapeshellarg($arcBase) . ' ' . escapeshellarg($innerBase);
	exec($cmd . ' 2>/dev/null', $output, $return);
	if($cwd !== false) {
		chdir($cwd);
	}
	if(is_file($innerPath)) {
		unlink($innerPath);
	}
	$blob = null;
	if($return === 0 && is_file($arcPath)) {
		$blob = file_get_contents($arcPath);
	}
	if(is_file($arcPath)) {
		unlink($arcPath);
	}
	@rmdir($workDir);
	if($blob === false || $blob === null || strlen($blob) === 0) {
		return null;
	}
	return $blob;
}

/**
 * FreeArc outer container; picks smallest among candidate -m levels (bytes-first tournament).
 * FRACTAL_ZIP_ARC_METHOD=1..9 forces a single method (bench compatibility).
 * FRACTAL_ZIP_ARC_OUTER_METHODS=5,7,9 optional comma list overrides the default candidate set.
 * FRACTAL_ZIP_ARC_OUTER_DUAL_METHODS=0: large inners use -m5 only (legacy fast path).
 * FRACTAL_ZIP_ARC_OUTER_MX=0: skip extra -mx attempts on -m5 and -m9 (default: after the default method sweep, not when FRACTAL_ZIP_ARC_OUTER_METHODS is set).
 * Default large-inner sweep: -m4..9 (folder min-ext uses -m5 on separate files; one FZB blob may need another -m or -mx to tie).
 */
function outer_arc_blob($arcExe, $string) {
	$n = strlen($string);
	$custom = getenv('FRACTAL_ZIP_ARC_METHOD');
	if($custom !== false && trim((string)$custom) !== '' && ctype_digit(trim((string)$custom))) {
		return $this->outer_arc_blob_single($arcExe, $string, (int)trim((string)$custom), '');
	}
	$listEnv = getenv('FRACTAL_ZIP_ARC_OUTER_METHODS');
	$explicitOuterList = ($listEnv !== false && trim((string) $listEnv) !== '');
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
	} else {
		$methods = array(5);
		$noDual = (getenv('FRACTAL_ZIP_ARC_OUTER_DUAL_METHODS') === '0');
		$minDualEnv = getenv('FRACTAL_ZIP_ARC_OUTER_DUAL_MIN_BYTES');
		$minDual = ($minDualEnv === false || trim((string)$minDualEnv) === '') ? 458752 : max(0, (int)$minDualEnv);
		if(!$noDual && $minDual > 0 && $n >= $minDual) {
			$methods = array(4, 5, 6, 7, 8, 9);
		}
	}
	$best = null;
	$bestLen = PHP_INT_MAX;
	foreach($methods as $m) {
		$b = $this->outer_arc_blob_single($arcExe, $string, $m, '');
		if($b !== null && strlen($b) < $bestLen) {
			$best = $b;
			$bestLen = strlen($b);
		}
	}
	$tryMx = true;
	$mxEnv = getenv('FRACTAL_ZIP_ARC_OUTER_MX');
	if($mxEnv !== false && trim((string)$mxEnv) !== '') {
		$mxL = strtolower(trim((string)$mxEnv));
		if($mxL === '0' || $mxL === 'off' || $mxL === 'false' || $mxL === 'no') {
			$tryMx = false;
		}
	}
	if($tryMx && !$explicitOuterList) {
		foreach(array(5, 9) as $m) {
			$b = $this->outer_arc_blob_single($arcExe, $string, $m, ' -mx');
			if($b !== null && strlen($b) < $bestLen) {
				$best = $b;
				$bestLen = strlen($b);
			}
		}
	}
	return $best;
}

/**
 * ZSTD-compress stdin → stdout (level 1–22; default from FRACTAL_ZIP_ZSTD_LEVEL or 19).
 * Uses temp-file input when the payload is large enough to risk proc_open stdin/stdout pipe deadlock (see outer_codec_temp_input_threshold_bytes).
 */
function outer_zstd_blob($zstdExe, $string, $level) {
	$level = max(1, min(22, (int)$level));
	$base = escapeshellarg($zstdExe) . ' -' . $level . ' -c -T0 --quiet';
	$cmdStdin = $base;
	$cmdTmp = $base . ' -- __FZOC_TMPIN__';
	return fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $string, $this->program_path);
}

/**
 * XZ-compress stdin → stdout (level 0–9). Returns raw .xz stream (has magic).
 */
function outer_xz_blob($xzExe, $string, $level, $timeoutSec = null) {
	$level = max(0, min(9, (int)$level));
	$inner = escapeshellarg($xzExe) . ' -' . $level . ' -c';
	if($timeoutSec !== null && DIRECTORY_SEPARATOR !== '\\') {
		$t = (float)$timeoutSec;
		if($t > 0) {
			$to = trim((string)@shell_exec('command -v timeout 2>/dev/null'));
			if($to !== '') {
				$inner = escapeshellarg($to) . ' -k 1 ' . escapeshellarg((string)$t) . ' ' . $inner;
			}
		}
	}
	$cmdStdin = $inner;
	$cmdTmp = $inner . ' __FZOC_TMPIN__';
	return fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $string, $this->program_path);
}

/**
 * Brotli-compress stdin → stdout; returns magic prefix + raw brotli (quality 0–11, default FRACTAL_ZIP_BROTLI_QUALITY or 11).
 * Optional $lgwinOverride: 0–24 passed as brotli -w (overrides FRACTAL_ZIP_BROTLI_LGWIN when non-null).
 */
function outer_brotli_blob($brotliExe, $string, $quality, $timeoutSec = null, $lgwinOverride = null) {
	$quality = max(0, min(11, (int)$quality));
	$cmd = escapeshellarg($brotliExe) . ' -c -q ' . $quality;
	if($lgwinOverride !== null) {
		$lw = max(0, min(24, (int)$lgwinOverride));
		$cmd .= ' -w ' . $lw;
	} else {
		$lwEnv = getenv('FRACTAL_ZIP_BROTLI_LGWIN');
		if($lwEnv !== false && trim((string)$lwEnv) !== '' && is_numeric(trim((string)$lwEnv))) {
			$lw = max(0, min(24, (int)trim((string)$lwEnv)));
			$cmd .= ' -w ' . $lw;
		}
	}
	// Optional timeout for pathological/huge brotli cases (Unix: uses coreutils `timeout` if present).
	if($timeoutSec !== null && DIRECTORY_SEPARATOR !== '\\') {
		$t = (float)$timeoutSec;
		if($t > 0) {
			$to = trim((string)@shell_exec('command -v timeout 2>/dev/null'));
			if($to !== '') {
				$cmd = escapeshellarg($to) . ' -k 1 ' . escapeshellarg((string)$t) . ' ' . $cmd;
			}
		}
	}
	$cmdStdin = $cmd;
	$cmdTmp = $cmd . ' __FZOC_TMPIN__';
	$out = fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $string, $this->program_path);
	if($out === null || $out === '') {
		return null;
	}
	return fractal_zip::OUTER_BROTLI_MAGIC . $out;
}

/**
 * Decompress zstd stdin → stdout.
 */
function outer_zstd_decompress_pipe($zstdExe, $blob) {
	$base = escapeshellarg($zstdExe) . ' -d -c --quiet';
	$cmdStdin = $base;
	$cmdTmp = $base . ' -- __FZOC_TMPIN__';
	return fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout($cmdStdin, $cmdTmp, $blob, $this->program_path);
}

/**
 * Decompress brotli stdin → stdout.
 */
function outer_brotli_decompress_pipe($brotliExe, $blob) {
	$base = escapeshellarg($brotliExe) . ' -d -c';
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
	$disable = getenv('FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN');
	if($disable === '1') {
		return false;
	}
	$n = strlen($string);
	$minProbeEnv = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_MIN_BYTES');
	$minProbe = ($minProbeEnv === false || trim((string)$minProbeEnv) === '') ? 16384 : max(0, (int)$minProbeEnv);
	if($n < $minProbe) {
		return false;
	}
	$sampleTargetEnv = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_SAMPLE_BYTES');
	$sampleTarget = ($sampleTargetEnv === false || trim((string)$sampleTargetEnv) === '') ? 4096 : max(512, min(32768, (int)$sampleTargetEnv));
	$step = max(1, (int)floor($n / $sampleTarget));
	$sampleMinEnv = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_SAMPLE_MIN_BYTES');
	$sampleMin = ($sampleMinEnv === false || trim((string)$sampleMinEnv) === '') ? 1024 : max(256, min(8192, (int)$sampleMinEnv));
	$sn = 0;
	$repeatAdj = 0;
	$seen = array_fill(0, 256, false);
	$unique = 0;
	$prev = null;
	for($i = 0; $i < $n; $i += $step) {
		$b = ord($string[$i]);
		if($prev !== null && $b === $prev) {
			$repeatAdj++;
		}
		$prev = $b;
		if($seen[$b] === false) {
			$seen[$b] = true;
			$unique++;
		}
		$sn++;
	}
	if($sn < $sampleMin) {
		return false;
	}
	$repeatRate = $repeatAdj / max(1, $sn - 1);
	$uniqueEnv = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_UNIQUE_MIN');
	$uniqueMin = ($uniqueEnv === false || trim((string)$uniqueEnv) === '') ? 245 : max(0, min(256, (int)$uniqueEnv));
	$repeatEnv = getenv('FRACTAL_ZIP_OUTER_PRESCREEN_REPEAT_MAX');
	$repeatMax = ($repeatEnv === false || trim((string)$repeatEnv) === '') ? 0.01 : max(0.0, min(1.0, (float)$repeatEnv));
	// Very broad random-ish signature: high symbol spread with little local repetition.
	return $unique >= $uniqueMin && $repeatRate <= $repeatMax;
}

/**
 * Cheap text-likeness probe for deciding if brotli is worth trying as an outer wrapper.
 * Samples bytes across payload and checks ratio of printable ASCII/whitespace.
 */
function outer_likely_textlike($string) {
	$disable = getenv('FRACTAL_ZIP_DISABLE_OUTER_TEXTLIKE');
	if($disable === '1') {
		return true;
	}
	$n = strlen($string);
	if($n === 0) {
		return false;
	}
	$minProbeEnv = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_MIN_BYTES');
	$minProbe = ($minProbeEnv === false || trim((string)$minProbeEnv) === '') ? 4096 : max(0, (int)$minProbeEnv);
	if($n < $minProbe) {
		// Too small to be worth gating; allow brotli.
		return true;
	}
	$sampleTargetEnv = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_SAMPLE_BYTES');
	$sampleTarget = ($sampleTargetEnv === false || trim((string)$sampleTargetEnv) === '') ? 4096 : max(512, min(32768, (int)$sampleTargetEnv));
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
		return true;
	}
	$ratio = $printable / max(1, $sn);
	$zeroRatio = $zeros / max(1, $sn);
	$ratioEnv = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_MIN_RATIO');
	$minRatio = ($ratioEnv === false || trim((string)$ratioEnv) === '') ? 0.90 : max(0.0, min(1.0, (float)$ratioEnv));
	$zeroEnv = getenv('FRACTAL_ZIP_OUTER_TEXTLIKE_MAX_ZERO_RATIO');
	$maxZero = ($zeroEnv === false || trim((string)$zeroEnv) === '') ? 0.01 : max(0.0, min(1.0, (float)$zeroEnv));
	return ($ratio >= $minRatio) && ($zeroRatio <= $maxZero);
}

/**
 * gzip-9 + zstd + brotli only (no 7z/arc/xz). Tier-1 outer for staged literal-folder selection: matches brotli-oriented
 * external folder baselines without slow LZMA sweeps. Does not apply outer_likely_incompressible so MPQ-style literal
 * inners still get zstd/brotli. Huge inners: optional timeout via FRACTAL_ZIP_STAGED_OUTER_BROTLI_TIMEOUT_SEC (default 120 s
 * when inner exceeds FRACTAL_ZIP_MAX_BROTLI_OUTER_INNER_BYTES on Unix; null env = that default; 0 = no timeout).
 * If no tried outer shrinks the inner, returns the inner unchanged (<code>store</code>) unless FRACTAL_ZIP_ALLOW_OUTER_EXPANSION=1.
 */
function adaptive_compress_outer_fast_codec_tier($string) {
	fractal_zip::$last_outer_codec = null;
	$string = (string) $string;
	$df = gzdeflate($string, 9);
	$bestGzipBlob = ($df !== false) ? $df : null;
	if(getenv('FRACTAL_ZIP_FORCE_OUTER') === 'gzip') {
		$out = $bestGzipBlob !== null ? $bestGzipBlob : $string;
		if(getenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION') !== '1' && strlen($out) >= strlen($string)) {
			fractal_zip::$last_outer_codec = 'store';
			return $string;
		}
		fractal_zip::$last_outer_codec = 'gzip';
		return $out;
	}
	$innerLen = strlen($string);
	if($innerLen === 0) {
		$out = $bestGzipBlob !== null ? $bestGzipBlob : $string;
		if(getenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION') !== '1' && strlen($out) >= strlen($string)) {
			fractal_zip::$last_outer_codec = 'store';
			return $string;
		}
		fractal_zip::$last_outer_codec = 'gzip';
		return $out;
	}
	$zstdExe = fractal_zip::zstd_executable();
	$minZstdInnerEnv = getenv('FRACTAL_ZIP_MIN_ZSTD_INNER_BYTES');
	$minZstdInner = ($minZstdInnerEnv === false || trim((string) $minZstdInnerEnv) === '') ? 2048 : max(0, (int) $minZstdInnerEnv);
	$brotliExe = fractal_zip::brotli_executable();
	$minBrotliInnerEnv = getenv('FRACTAL_ZIP_MIN_BROTLI_INNER_BYTES');
	$minBrotliInner = ($minBrotliInnerEnv === false || trim((string) $minBrotliInnerEnv) === '') ? 1024 : max(0, (int) $minBrotliInnerEnv);
	$outerTextlike = $this->outer_likely_textlike($string);
	$zstdBlob = null;
	$canTryZstd = ($zstdExe !== null && getenv('FRACTAL_ZIP_SKIP_ZSTD') !== '1' && $innerLen >= $minZstdInner);
	if($canTryZstd) {
		$zl = getenv('FRACTAL_ZIP_ZSTD_LEVEL');
		$zstdLevel = ($zl === false || trim((string) $zl) === '') ? 16 : max(1, min(22, (int) $zl));
		if(($zl === false || trim((string) $zl) === '') && getenv('FRACTAL_ZIP_DISABLE_ZSTD_TEXTLIKE_LEVEL') !== '1') {
			$tlMinEnv = getenv('FRACTAL_ZIP_ZSTD_TEXTLIKE_MIN_INNER_BYTES');
			$tlMin = ($tlMinEnv === false || trim((string) $tlMinEnv) === '') ? 32768 : max(0, (int) $tlMinEnv);
			if($tlMin > 0 && $innerLen >= $tlMin && $outerTextlike) {
				$tlLevEnv = getenv('FRACTAL_ZIP_ZSTD_TEXTLIKE_LEVEL');
				$zstdLevel = ($tlLevEnv === false || trim((string) $tlLevEnv) === '') ? 14 : max(1, min(22, (int) $tlLevEnv));
			}
		}
		$zstdBlob = $this->outer_zstd_blob($zstdExe, $string, $zstdLevel);
	}
	$brotliBlob = null;
	$canTryBrotli = ($brotliExe !== null && getenv('FRACTAL_ZIP_SKIP_BROTLI') !== '1' && $innerLen >= $minBrotliInner);
	if($canTryBrotli) {
		$bq = getenv('FRACTAL_ZIP_BROTLI_QUALITY');
		$brotliQ = ($bq === false || trim((string) $bq) === '') ? 10 : max(0, min(11, (int) $bq));
		$maxBrEnv = getenv('FRACTAL_ZIP_MAX_BROTLI_OUTER_INNER_BYTES');
		$maxBr = ($maxBrEnv === false || trim((string) $maxBrEnv) === '') ? 262144 : max(0, (int) $maxBrEnv);
		$isHuge = ($maxBr > 0 && $innerLen > $maxBr);
		$brToEnv = getenv('FRACTAL_ZIP_STAGED_OUTER_BROTLI_TIMEOUT_SEC');
		$brTo = null;
		if($brToEnv !== false && trim((string) $brToEnv) !== '' && is_numeric(trim((string) $brToEnv))) {
			$brToParsed = (float) trim((string) $brToEnv);
			$brTo = ($brToParsed > 0) ? $brToParsed : null;
		} elseif($isHuge && DIRECTORY_SEPARATOR !== '\\') {
			$brTo = 120.0;
		}
		if(($bq === false || trim((string) $bq) === '') && getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_BROTLI_Q11') !== '1' && getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI_Q11') !== '1') {
			$q11MaxEnv = getenv('FRACTAL_ZIP_AUTO_BROTLI_Q11_MAX_INNER_BYTES');
			$q11Max = ($q11MaxEnv === false || trim((string) $q11MaxEnv) === '') ? 2097152 : max(0, (int) $q11MaxEnv);
			$useQ11Textlike = ($outerTextlike && $q11Max > 0 && $innerLen <= $q11Max);
			$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_max_inner_bytes_effective();
			$useQ11FzbLiteral = ($fzbLitMax > 0 && $innerLen <= $fzbLitMax && $this->bundle_inner_eligible_for_fzws($string) && getenv('FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11') !== '1');
			if($useQ11Textlike || $useQ11FzbLiteral) {
				$brotliQ = 11;
			}
		}
		$brotliBlob = $this->outer_brotli_blob($brotliExe, $string, $brotliQ, $brTo);
	}
	$bestCodec = 'gzip';
	$bestBlob = $bestGzipBlob !== null ? $bestGzipBlob : $string;
	$bestLen = $bestGzipBlob !== null ? strlen($bestGzipBlob) : PHP_INT_MAX;
	if($zstdBlob !== null && strlen($zstdBlob) > 0 && strlen($zstdBlob) < $bestLen) {
		$bestCodec = 'zstd';
		$bestBlob = $zstdBlob;
		$bestLen = strlen($zstdBlob);
	}
	if($brotliBlob !== null && strlen($brotliBlob) > 0 && strlen($brotliBlob) < $bestLen) {
		$bestCodec = 'brotli';
		$bestBlob = $brotliBlob;
		$bestLen = strlen($brotliBlob);
	}
	$allowOuterExpansion = getenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION') === '1';
	if(!$allowOuterExpansion && $bestLen >= $innerLen) {
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	fractal_zip::$last_outer_codec = $bestCodec;
	return $bestBlob;
}

/**
 * Pipeline: compact FZC2 (16-bit path/value lens when each ≤ 65535) or FZC1 payload (or legacy serialized payload) → then either 7z (LZMA2 and/or PPMd -mx=9) or gzip level 9, whichever is smaller.
 * Set FRACTAL_ZIP_FORCE_OUTER=gzip to skip 7z; FRACTAL_ZIP_SKIP_7Z=1 to never invoke the 7z binary; FRACTAL_ZIP_SKIP_PPMD=1 to skip the extra PPMd attempt (inner ≥ 4 KiB).
 * Optional: zstd (FRACTAL_ZIP_SKIP_ZSTD=1), brotli (FRACTAL_ZIP_SKIP_BROTLI=1); FRACTAL_ZIP_ZSTD_LEVEL (default 16 non-speed), FRACTAL_ZIP_BROTLI_QUALITY (default 10 non-speed).
 * Text-like midsize zstd: FRACTAL_ZIP_ZSTD_TEXTLIKE_LEVEL (default 14), FRACTAL_ZIP_ZSTD_TEXTLIKE_MIN_INNER_BYTES (default 32768), FRACTAL_ZIP_DISABLE_ZSTD_TEXTLIKE_LEVEL=1 to disable.
 * Speed guards for tiny inner payloads: FRACTAL_ZIP_MIN_ARC_INNER_BYTES (default 16384), FRACTAL_ZIP_MIN_ZSTD_INNER_BYTES (default 2048), FRACTAL_ZIP_MIN_BROTLI_INNER_BYTES (default 1024).
 * Random-like prescreen can skip arc/zstd/brotli for likely incompressible payloads; disable with FRACTAL_ZIP_DISABLE_OUTER_PRESCREEN=1.
 * Prescreen tuning knobs: FRACTAL_ZIP_OUTER_PRESCREEN_{MIN_BYTES,SAMPLE_BYTES,SAMPLE_MIN_BYTES,UNIQUE_MIN,REPEAT_MAX}.
 * Huge FZB4/5/6 inners: FRACTAL_ZIP_SKIP_XZ_BUNDLE_HUGE=1 skips extra xz try; FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS=1 skips 7z/arc sweep unlock (incl. bytes-first reopen after xz when inner ≥ FRACTAL_ZIP_HUGE_SLOW_OUTER_MIN_INNER_BYTES).
 * Non-text-like huge FZB literals: 7z outer sweep runs by default (better .fzc vs raw+7z benches). FRACTAL_ZIP_FZB_BINARY_SKIP_7Z_SWEEP=1 skips it for speed.
 * Multi-MiB FZB + 7z: FRACTAL_ZIP_SKIP_XZ_LITERAL_HUGE_MIN_INNER_BYTES (unset ⇒ bytes-first: no gate; FRACTAL_ZIP_SPEED=1 ⇒ default 4000000) skips xz when 7-Zip is available (saves time vs 7z/PPMd path).
 * XZ trigger: FRACTAL_ZIP_XZ_TRIGGER_RATIO (default 0.82 vs gzip). Bytes-first applies the ratio gate like FRACTAL_ZIP_DEEP=1; FRACTAL_ZIP_SPEED=1 keeps ratio-gated xz only when DEEP=1 (or huge FZB bundle path / FRACTAL_ZIP_FORCE_XZ=1).
 * Huge non-FZB inners: FreeArc runs in bytes-first (like FRACTAL_ZIP_DEEP=1); FRACTAL_ZIP_SPEED=1 skips unless DEEP=1, FRACTAL_ZIP_FORCE_ARC=1, or FZB-huge inner.
 * FRACTAL_ZIP_BROTLI_HUGE_MODE: unset ⇒ bytes-first default <code>probe</code> (cheap Q1 probe, then full brotli if competitive); FRACTAL_ZIP_SPEED=1 ⇒ default <code>skip</code>. Set <code>full</code> / <code>skip</code> / <code>probe</code> explicitly to override.
 * Text-like huge outer payloads (incl. non-FZB unified-stream inners ≤ cap): same as FZB path — full brotli when auto textlike path applies (mode skip/probe still allow this branch).
 *   FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MAX_INNER_BYTES (default = whole_stream_fzws_max_bytes, else 128 MiB) caps inner size; FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI=1 disables both.
 *   FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_NONFZB_HUGE_BROTLI=1 restores legacy behavior (only FZB-tagged huge literals get auto brotli, not other huge text-like inners).
 * Text-like outer payloads (HTML/JSON-like heuristics): brotli Q11 up to FRACTAL_ZIP_AUTO_BROTLI_Q11_MAX_INNER_BYTES (default 2 MiB); disable with
 *   FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_BROTLI_Q11=1 (alias: FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI_Q11 for FZB-only legacy).
 * Small binary literal bundles (FZB4/5/6 prefix): brotli Q11 when inner ≤ FRACTAL_ZIP_FZB_LITERAL_BROTLI_Q11_MAX_INNER_BYTES (unset ⇒ bytes-first 16 MiB; FRACTAL_ZIP_SPEED=1 ⇒ 64 KiB);
 *   disable with FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11=1. Path-order/layout proxy scoring uses FRACTAL_ZIP_FZB_PATH_ORDER_BROTLI_Q11_MAX_BYTES or min(that cap, 512 KiB) so multi‑MiB folders do not run brotli Q11 hundreds of times.
 * When brotli outer is extremely small vs inner (ratio knobs FRACTAL_ZIP_SKIP_SLOW_OUTERS_AFTER_BROTLI_*), skip xz / reopen-7z sweep for speed.
 * Highly gzip-compressible text-like FZB: FRACTAL_ZIP_SKIP_ZSTD_TEXTLIKE_FZB_GZIP_DIV (default 50) skips zstd when gzLen*div < innerLen (brotli usually wins outer).
 * FreeArc outer: FRACTAL_ZIP_ARC_METHOD=1..9 single trial; else FRACTAL_ZIP_ARC_OUTER_METHODS=comma list; else large inners try -m4..9 and -m5/-m9 -mx (FRACTAL_ZIP_ARC_OUTER_DUAL_METHODS=0 ⇒ -m5 only; FRACTAL_ZIP_ARC_OUTER_MX=0 skips -mx).
 * Outer brotli window: FRACTAL_ZIP_BROTLI_LGWIN=0..24 (optional).
 * If every outer candidate is ≥ inner length, the inner bytes are stored unchanged and <code>last_outer_codec</code> is <code>store</code>.
 * Set <code>FRACTAL_ZIP_ALLOW_OUTER_EXPANSION=1</code> to keep legacy behavior (use best outer even when it does not shrink the payload).
 */
function adaptive_compress($string) {
	fractal_zip::$last_outer_codec = null;
	// gzip baseline: gzdeflate is typically smaller than gzcompress (no wrapper),
	// so avoid doing both (saves CPU on large inputs).
	$df = gzdeflate($string, 9);
	$bestGzipBlob = ($df !== false) ? $df : null;
	if(getenv('FRACTAL_ZIP_FORCE_OUTER') === 'gzip') {
		$out = $bestGzipBlob !== null ? $bestGzipBlob : $string;
		if(getenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION') !== '1' && strlen($out) >= strlen($string)) {
			fractal_zip::$last_outer_codec = 'store';
			return $string;
		}
		fractal_zip::$last_outer_codec = 'gzip';
		return $out;
	}
	$gzLen = $bestGzipBlob !== null ? strlen($bestGzipBlob) : PHP_INT_MAX;
	$innerLen = strlen($string);
	$sevenBlob = null;
	$arcBlob = null;
	$zstdBlob = null;
	$brotliBlob = null;
	$speedMode = (getenv('FRACTAL_ZIP_SPEED') === '1');
	$deepMode = (getenv('FRACTAL_ZIP_DEEP') === '1');
	// Speed-first mode reduces expensive outer trials; can be overridden by explicitly un-skipping codecs.
	$try7z = !$speedMode;
	$tryArc = !$speedMode;
	$tryBrotli = !$speedMode;
	$tryZstd = true;
	if($speedMode && getenv('FRACTAL_ZIP_SPEED_TRY_7Z') === '1') {
		$try7z = true;
	}
	if($speedMode && getenv('FRACTAL_ZIP_SPEED_TRY_ARC') === '1') {
		$tryArc = true;
	}
	if($speedMode && getenv('FRACTAL_ZIP_SPEED_TRY_BROTLI') === '1') {
		$tryBrotli = true;
	}
	if($speedMode && getenv('FRACTAL_ZIP_SPEED_SKIP_ZSTD') === '1') {
		$tryZstd = false;
	}
	$seven = fractal_zip::seven_zip_executable();
	// Staged outer tournament: try fast codecs first; only pay for slow ones (7z/arc) if needed.
	$stopBytesEnv = getenv('FRACTAL_ZIP_OUTER_EARLY_STOP_BYTES');
	$stopBytes = ($stopBytesEnv === false || trim((string)$stopBytesEnv) === '') ? 32 : max(0, (int)$stopBytesEnv);
	$stopPctEnv = getenv('FRACTAL_ZIP_OUTER_EARLY_STOP_PCT');
	$stopPct = ($stopPctEnv === false || trim((string)$stopPctEnv) === '') ? 0.01 : max(0.0, min(1.0, (float)$stopPctEnv));
	// For small inner payloads, be more aggressive about skipping slow outer codecs.
	// (You can disable by setting FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC=0.)
	$dynEnv = getenv('FRACTAL_ZIP_OUTER_EARLY_STOP_DYNAMIC');
	$dyn = ($dynEnv === false || trim((string)$dynEnv) === '') ? true : ((int)$dynEnv !== 0);
	if($dyn && $innerLen < 32768) {
		$stopBytes = min($stopBytes, 8);
		$stopPct = min($stopPct, 0.005);
	} elseif($dyn && $innerLen < 131072) {
		$stopBytes = min($stopBytes, 16);
		$stopPct = min($stopPct, 0.008);
	}
	$earlyStop = false;
	$min7zInnerEnv = getenv('FRACTAL_ZIP_MIN_7Z_INNER_BYTES');
	$min7zInner = ($min7zInnerEnv === false || trim((string)$min7zInnerEnv) === '') ? 8192 : max(0, (int)$min7zInnerEnv);
	$arcExe = fractal_zip::freearc_executable();
	$minArcInnerEnv = getenv('FRACTAL_ZIP_MIN_ARC_INNER_BYTES');
	$minArcInner = ($minArcInnerEnv === false || trim((string)$minArcInnerEnv) === '') ? 16384 : max(0, (int)$minArcInnerEnv);
	$zstdExe = fractal_zip::zstd_executable();
	$minZstdInnerEnv = getenv('FRACTAL_ZIP_MIN_ZSTD_INNER_BYTES');
	$minZstdInner = ($minZstdInnerEnv === false || trim((string)$minZstdInnerEnv) === '') ? 2048 : max(0, (int)$minZstdInnerEnv);
	$brotliExe = fractal_zip::brotli_executable();
	$xzExe = fractal_zip::xz_executable();
	$minBrotliInnerEnv = getenv('FRACTAL_ZIP_MIN_BROTLI_INNER_BYTES');
	$minBrotliInner = ($minBrotliInnerEnv === false || trim((string)$minBrotliInnerEnv) === '') ? 1024 : max(0, (int)$minBrotliInnerEnv);

	// Define huge-payload flag early (used by codec gating below).
	$maxBrEnv = getenv('FRACTAL_ZIP_MAX_BROTLI_OUTER_INNER_BYTES');
	$maxBr = ($maxBrEnv === false || trim((string)$maxBrEnv) === '') ? 262144 : max(0, (int)$maxBrEnv);
	$isHuge = ($maxBr > 0 && $innerLen > $maxBr);
	$fzbHuge = ($isHuge && $this->bundle_inner_eligible_for_fzws($string));
	$outerTextlike = $this->outer_likely_textlike($string);
	$textlikeFzbBrotliMaxEnv = getenv('FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MAX_INNER_BYTES');
	// Default aligns with whole-stream FZWS cap: unified-folder inners are often multi‑MiB text-like; a 4 MiB cap caused zstd
	// early-stop to skip brotli entirely while min-ext still ran brotli (bytes regression on e.g. test_files55).
	$textlikeFzbBrotliDefault = fractal_zip::whole_stream_fzws_max_bytes();
	if($textlikeFzbBrotliDefault <= 0) {
		$textlikeFzbBrotliDefault = 134217728;
	}
	$textlikeFloorEnv = getenv('FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MIN_FLOOR_BYTES');
	$textlikeFloor = ($textlikeFloorEnv === false || trim((string)$textlikeFloorEnv) === '') ? (512 * 1024 * 1024) : max(0, (int) trim((string)$textlikeFloorEnv));
	if($textlikeFloor > 0) {
		$textlikeFzbBrotliDefault = max($textlikeFzbBrotliDefault, $textlikeFloor);
	}
	$textlikeFzbBrotliMax = ($textlikeFzbBrotliMaxEnv === false || trim((string)$textlikeFzbBrotliMaxEnv) === '') ? $textlikeFzbBrotliDefault : max(0, (int)$textlikeFzbBrotliMaxEnv);
	$textlikeHugeBrotliBase = ($isHuge && $textlikeFzbBrotliMax > 0 && $innerLen <= $textlikeFzbBrotliMax && $outerTextlike && getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI') !== '1');
	$allowNonFzbTextlikeHugeBrotli = (getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_NONFZB_HUGE_BROTLI') !== '1');
	$sigTop = ($innerLen >= 4) ? substr($string, 0, 4) : '';
	$mergedFlacBundleHuge = ($fzbHuge && $textlikeFzbBrotliMax > 0 && $innerLen <= $textlikeFzbBrotliMax && getenv('FRACTAL_ZIP_DISABLE_AUTO_MERGED_FLAC_BUNDLE_HUGE_BROTLI') !== '1'
		&& (($sigTop === 'FZCD') || ($sigTop === 'FZBM' && $innerLen >= 5 && ord($string[4]) === 1)));
	$autoTextlikeFzbBrotliFull = ($textlikeHugeBrotliBase && ($fzbHuge || $allowNonFzbTextlikeHugeBrotli)) || $mergedFlacBundleHuge;

	$canTryArc = ($tryArc && $arcExe !== null && getenv('FRACTAL_ZIP_SKIP_ARC') !== '1' && $innerLen >= $minArcInner);
	$canTryZstd = ($tryZstd && $zstdExe !== null && getenv('FRACTAL_ZIP_SKIP_ZSTD') !== '1' && $innerLen >= $minZstdInner);
	$canTryBrotli = ($tryBrotli && $brotliExe !== null && getenv('FRACTAL_ZIP_SKIP_BROTLI') !== '1' && $innerLen >= $minBrotliInner);
	// Huge FZB* inners: min-ext often wins with zstd; bytes-first keeps zstd in the outer tournament (default threshold 0 = never skip here).
	// FRACTAL_ZIP_SPEED=1 restores a 4 MiB skip when 7z is available unless FRACTAL_ZIP_SKIP_ZSTD_LITERAL_HUGE_MIN_INNER_BYTES is set explicitly.
	// Set N>0 to skip zstd for innerLen≥N when fzbHuge && seven_zip present (saves outer CPU on huge literal bundles).
	$skipZstdHugeFzbEnv = getenv('FRACTAL_ZIP_SKIP_ZSTD_LITERAL_HUGE_MIN_INNER_BYTES');
	if($skipZstdHugeFzbEnv !== false && trim((string)$skipZstdHugeFzbEnv) !== '') {
		$skipZstdHugeFzbMin = max(0, (int)$skipZstdHugeFzbEnv);
	} elseif(getenv('FRACTAL_ZIP_SPEED') === '1') {
		$skipZstdHugeFzbMin = 4000000;
	} else {
		$skipZstdHugeFzbMin = 0;
	}
	if($canTryZstd && $fzbHuge && $seven !== null && $skipZstdHugeFzbMin > 0 && $innerLen >= $skipZstdHugeFzbMin) {
		$canTryZstd = false;
	}
	if($canTryZstd && $autoTextlikeFzbBrotliFull && getenv('FRACTAL_ZIP_DISABLE_SKIP_ZSTD_TEXTLIKE_FZB') !== '1') {
		$divEnv = getenv('FRACTAL_ZIP_SKIP_ZSTD_TEXTLIKE_FZB_GZIP_DIV');
		$gzipDiv = ($divEnv === false || trim((string)$divEnv) === '') ? 50 : max(1, (int)$divEnv);
		if($gzipDiv > 0 && $gzLen !== PHP_INT_MAX && $gzLen > 0 && $gzLen * $gzipDiv < $innerLen) {
			$canTryZstd = false;
		}
	}
	// Speed: arc is expensive on huge non-FZB; bytes-first keeps it (DEEP behavior). FRACTAL_ZIP_SPEED=1 skips unless DEEP/forced/FZB inner.
	$forceArc = (getenv('FRACTAL_ZIP_FORCE_ARC') === '1');
	if($canTryArc && $isHuge && $speedMode && !$deepMode && !$forceArc && !$fzbHuge) {
		$canTryArc = false;
	}
	// brotli-on-huge policy:
	// - default: skip (fastest)
	// - mode=probe: try low-quality brotli and keep only if it beats current best by N bytes
	// - mode=full: allow normal brotli quality on huge payloads (slowest; best odds of bytes)
	$brotliHugeMode = strtolower((string) getenv('FRACTAL_ZIP_BROTLI_HUGE_MODE'));
	if($brotliHugeMode === '') {
		// Bytes-first: probe (fast Q1, then full brotli only if competitive). Speed mode: skip unless env sets probe/full.
		$brotliHugeMode = $speedMode ? 'skip' : 'probe';
	}
	$brotliHugeProbe = false;
	$forceBrotli = (getenv('FRACTAL_ZIP_FORCE_BROTLI') === '1');
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
	$likelyIncompressible = ($canTryArc || $canTryZstd || $canTryBrotli) ? $this->outer_likely_incompressible($string) : false;
	// FZB4/5/6 (and FZBM/FZBF/FZBD-wrapped literals) embed ZIP/deflate/PNG bytes; the prescreen often marks them “incompressible”
	// and skips zstd even when zstd beats gzip by a wide margin (e.g. test_files65: two game ZIPs as literals).
	if($this->bundle_inner_eligible_for_fzws($string)) {
		$likelyIncompressible = false;
	}
	$xzBlob = null;
	$zstdTriggersEarlyStop = false;
	if($canTryZstd && !$likelyIncompressible) {
		$zl = getenv('FRACTAL_ZIP_ZSTD_LEVEL');
		$zstdDefault = $speedMode ? 9 : 16;
		$zstdLevel = ($zl === false || trim((string)$zl) === '') ? $zstdDefault : max(1, min(22, (int)$zl));
		// CSV / logs: high zstd levels cost a lot of time for little gain; lower level improves bytes×time.
		if(($zl === false || trim((string)$zl) === '') && !$speedMode && !$isHuge && getenv('FRACTAL_ZIP_DISABLE_ZSTD_TEXTLIKE_LEVEL') !== '1') {
			$tlMinEnv = getenv('FRACTAL_ZIP_ZSTD_TEXTLIKE_MIN_INNER_BYTES');
			$tlMin = ($tlMinEnv === false || trim((string)$tlMinEnv) === '') ? 32768 : max(0, (int)$tlMinEnv);
			if($tlMin > 0 && $innerLen >= $tlMin && $this->outer_likely_textlike($string)) {
				$tlLevEnv = getenv('FRACTAL_ZIP_ZSTD_TEXTLIKE_LEVEL');
				$zstdLevel = ($tlLevEnv === false || trim((string)$tlLevEnv) === '') ? 14 : max(1, min(22, (int)$tlLevEnv));
			}
		}
		// Multi-megabyte FZB literal bundles (typical single huge binary): nudge zstd toward max when level not pinned by env.
		if(($zl === false || trim((string)$zl) === '') && !$speedMode && $fzbHuge && getenv('FRACTAL_ZIP_DISABLE_ZSTD_FZB_HUGE_LEVEL') !== '1') {
			$fzbMinEnv = getenv('FRACTAL_ZIP_ZSTD_FZB_HUGE_MIN_INNER_BYTES');
			$fzbMin = ($fzbMinEnv === false || trim((string)$fzbMinEnv) === '') ? 3145728 : max(0, (int)$fzbMinEnv);
			if($fzbMin > 0 && $innerLen >= $fzbMin) {
				$fzbLevEnv = getenv('FRACTAL_ZIP_ZSTD_FZB_HUGE_LEVEL');
				$fzbLev = ($fzbLevEnv === false || trim((string)$fzbLevEnv) === '') ? 19 : max(1, min(22, (int)$fzbLevEnv));
				if($fzbLev > $zstdLevel) {
					$zstdLevel = $fzbLev;
				}
			}
		}
		$zstdBlob = $this->outer_zstd_blob($zstdExe, $string, $zstdLevel);
		// Default outer level is 16; on some FZB* literal inners (e.g. small BMP bundles) zstd-15 is a few bytes smaller than 16 and can beat brotli Q11.
		if($zstdBlob !== null && ($zl === false || trim((string)$zl) === '') && !$speedMode && $zstdLevel === 16
			&& $this->bundle_inner_eligible_for_fzws($string) && getenv('FRACTAL_ZIP_DISABLE_ZSTD_FZB_ALT15') !== '1') {
			$zstdAlt = $this->outer_zstd_blob($zstdExe, $string, 15);
			if($zstdAlt !== null && strlen($zstdAlt) > 0 && strlen($zstdAlt) < strlen($zstdBlob)) {
				$zstdBlob = $zstdAlt;
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

	if($canTryBrotli && !$likelyIncompressible) {
		$alwaysTryBr = (getenv('FRACTAL_ZIP_ALWAYS_TRY_BROTLI') === '1');
		$minEnv = getenv('FRACTAL_ZIP_SKIP_BROTLI_IF_ZSTD_EARLYSTOP_MIN_INNER_BYTES');
		$min = ($minEnv === false || trim((string)$minEnv) === '') ? 163840 : max(0, (int)$minEnv);
		if($zstdTriggersEarlyStop && !$alwaysTryBr && ($min <= 0 || $innerLen >= $min)) {
			// Huge-mode=full explicitly asks for brotli on large inners; do not let zstd early-stop skip it.
			$allowBrotliDespiteZstdEarlyStop = $autoTextlikeFzbBrotliFull || ($brotliHugeMode === 'full');
			if(!$allowBrotliDespiteZstdEarlyStop) {
				$canTryBrotli = false;
			}
		}
	}

	if($canTryBrotli && !$likelyIncompressible) {
		$bq = getenv('FRACTAL_ZIP_BROTLI_QUALITY');
		$brotliDefault = $speedMode ? 7 : 10;
		$brotliQ = ($bq === false || trim((string)$bq) === '') ? $brotliDefault : max(0, min(11, (int)$bq));
		if(($bq === false || trim((string)$bq) === '') && !$speedMode && getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_BROTLI_Q11') !== '1' && getenv('FRACTAL_ZIP_DISABLE_AUTO_TEXTLIKE_FZB_BROTLI_Q11') !== '1') {
			$q11MaxEnv = getenv('FRACTAL_ZIP_AUTO_BROTLI_Q11_MAX_INNER_BYTES');
			$q11Max = ($q11MaxEnv === false || trim((string)$q11MaxEnv) === '') ? 2097152 : max(0, (int)$q11MaxEnv);
			// Mid-size cap (default 2 MiB) avoids Q11 on large binaries; unified text-like huge inners already use the
			// brotli-full path — allow Q11 there too so outer matches min-ext brotli quality on PHP/text trees.
			$useQ11Textlike = ($outerTextlike && (!$isHuge || $autoTextlikeFzbBrotliFull) && ($autoTextlikeFzbBrotliFull || ($q11Max > 0 && $innerLen <= $q11Max)));
			$fzbLitMax = fractal_zip::fzb_literal_brotli_q11_max_inner_bytes_effective();
			$useQ11FzbLiteral = ($fzbLitMax > 0 && $innerLen <= $fzbLitMax && $this->bundle_inner_eligible_for_fzws($string) && getenv('FRACTAL_ZIP_DISABLE_FZB_LITERAL_BROTLI_Q11') !== '1');
			if($useQ11Textlike || $useQ11FzbLiteral) {
				$brotliQ = 11;
			}
		}
		if(($bq === false || trim((string) $bq) === '') && !$speedMode && $mergedFlacBundleHuge
			&& getenv('FRACTAL_ZIP_DISABLE_MERGED_FLAC_BUNDLE_BROTLI_Q11') !== '1') {
			$brotliQ = 11;
		}
		if(!$brotliHugeProbe) {
			$brotliBlob = $this->outer_brotli_blob($brotliExe, $string, $brotliQ);
			if($brotliBlob !== null) {
				fractal_zip::message_once('brotli available (' . $brotliExe . '). Outer container also compares brotli for each .fzc.');
			}
		} else {
			// Probe first (very fast), then decide whether full brotli is worth it.
			$pqEnv = getenv('FRACTAL_ZIP_BROTLI_HUGE_PROBE_QUALITY');
			$probeQ = ($pqEnv === false || trim((string)$pqEnv) === '') ? 1 : max(0, min(11, (int)$pqEnv));
			$probeBlob = $this->outer_brotli_blob($brotliExe, $string, $probeQ, 2.5);
			if($probeBlob !== null) {
				$probeLen = strlen($probeBlob);
				$slackEnv = getenv('FRACTAL_ZIP_BROTLI_HUGE_TRIGGER_SLACK_BYTES');
				$slack = ($slackEnv === false || trim((string)$slackEnv) === '') ? 8192 : max(0, (int)$slackEnv);
				// If probe is competitive with current best, run full brotli; else skip.
				if($probeLen < $bestLen + $slack || getenv('FRACTAL_ZIP_FORCE_BROTLI') === '1') {
					$hugeToEnv = getenv('FRACTAL_ZIP_BROTLI_HUGE_FULL_TIMEOUT_SEC');
					$hugeTo = ($hugeToEnv === false || trim((string)$hugeToEnv) === '') ? 8.0 : max(0.0, (float)$hugeToEnv);
					$brotliBlob = $this->outer_brotli_blob($brotliExe, $string, $brotliQ, $hugeTo);
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
		// FZB* literal inners: brotli’s default lgwin is not always minimal at Q11; a fixed w=16 often saves 1 B on small bitmap bundles (test_files28).
		if($brotliBlob !== null && (int)$brotliQ === 11 && $this->bundle_inner_eligible_for_fzws($string)
			&& (getenv('FRACTAL_ZIP_BROTLI_LGWIN') === false || trim((string)getenv('FRACTAL_ZIP_BROTLI_LGWIN')) === '')
			&& getenv('FRACTAL_ZIP_DISABLE_BROTLI_FZB_Q11_LGWIN16') !== '1') {
			$fromFullBrotli = !$brotliHugeProbe || (isset($probeBlob) && $brotliBlob !== $probeBlob);
			if($fromFullBrotli) {
				$brW16 = $this->outer_brotli_blob($brotliExe, $string, 11, null, 16);
				if($brW16 !== null && strlen($brW16) > 0 && strlen($brW16) < strlen($brotliBlob)) {
					$brotliBlob = $brW16;
				}
			}
		}
	}
	$bestCodec = 'gzip';
	$bestBlob = $bestGzipBlob !== null ? $bestGzipBlob : $string;
	$bestLen = $bestGzipBlob !== null ? strlen($bestGzipBlob) : PHP_INT_MAX;
	if($zstdBlob !== null && strlen($zstdBlob) > 0 && strlen($zstdBlob) < $bestLen) {
		$bestCodec = 'zstd';
		$bestBlob = $zstdBlob;
		$bestLen = strlen($zstdBlob);
	}
	if($brotliBlob !== null && strlen($brotliBlob) > 0) {
		$brLen = strlen($brotliBlob);
		$accept = ($brLen < $bestLen);
		// In probe mode, acceptance already handled by competitive trigger; keep normal accept rule.
		if($accept) {
		$bestCodec = 'brotli';
		$bestBlob = $brotliBlob;
			$bestLen = $brLen;
		}
	}
	$strongTinyBrotliOuter = false;
	if($bestCodec === 'brotli' && $fzbHuge && $outerTextlike && $innerLen > 0) {
		$skipCapEnv = getenv('FRACTAL_ZIP_SKIP_SLOW_OUTERS_AFTER_BROTLI_MAX_INNER_BYTES');
		$skipCap = ($skipCapEnv === false || trim((string)$skipCapEnv) === '') ? 67108864 : max(0, (int)$skipCapEnv);
		$ratioNumEnv = getenv('FRACTAL_ZIP_SKIP_SLOW_OUTERS_AFTER_BROTLI_INNER_DIV');
		$ratioNum = ($ratioNumEnv === false || trim((string)$ratioNumEnv) === '') ? 1000 : max(1, (int)$ratioNumEnv);
		if($skipCap > 0 && $innerLen <= $skipCap && $bestLen * $ratioNum < $innerLen) {
			$strongTinyBrotliOuter = true;
		}
	}
	// Optional: xz for huge payloads (slow but can win bytes). Balanced trigger: only try if current best is still "close" to gzip.
	if($isHuge && $xzExe !== null && getenv('FRACTAL_ZIP_SKIP_XZ') !== '1' && !$strongTinyBrotliOuter) {
		$ratioEnv = getenv('FRACTAL_ZIP_XZ_TRIGGER_RATIO');
		$ratio = ($ratioEnv === false || trim((string)$ratioEnv) === '') ? 0.82 : max(0.0, min(1.0, (float)$ratioEnv));
		$shouldTryRatio = ($gzLen !== PHP_INT_MAX && $gzLen > 0 && ((float)$bestLen / (float)$gzLen) >= $ratio);
		$skipXzBundle = (getenv('FRACTAL_ZIP_SKIP_XZ_BUNDLE_HUGE') === '1');
		$allowBundleXz = ($fzbHuge && !$skipXzBundle);
		// Bytes-first: allow xz on huge FZB* unified inners (LZMA often edges zstd on mixed text, e.g. test_files69).
		// FRACTAL_ZIP_SPEED=1 restores a 4 MiB gate unless FRACTAL_ZIP_SKIP_XZ_LITERAL_HUGE_MIN_INNER_BYTES is set explicitly.
		$skipXzLitHugeEnv = getenv('FRACTAL_ZIP_SKIP_XZ_LITERAL_HUGE_MIN_INNER_BYTES');
		if($skipXzLitHugeEnv !== false && trim((string)$skipXzLitHugeEnv) !== '') {
			$skipXzLitHugeMin = max(0, (int)$skipXzLitHugeEnv);
		} elseif(getenv('FRACTAL_ZIP_SPEED') === '1') {
			$skipXzLitHugeMin = 4000000;
		} else {
			$skipXzLitHugeMin = 0;
		}
		if($allowBundleXz && $seven !== null && $skipXzLitHugeMin > 0 && $innerLen >= $skipXzLitHugeMin) {
			$allowBundleXz = false;
		}
		$forceXz = (getenv('FRACTAL_ZIP_FORCE_XZ') === '1');
		// Bytes-first: ratio-gated xz like FRACTAL_ZIP_DEEP=1; huge FZB* inner: try xz even when zstd crushed gzip (no ratio gate).
		// Speed mode: keep xz behind DEEP or bundle path unless FRACTAL_ZIP_FORCE_XZ=1.
		$tryXz = $forceXz || $allowBundleXz || (($deepMode || !$speedMode) && $shouldTryRatio);
		if($tryXz) {
			$xl = getenv('FRACTAL_ZIP_XZ_LEVEL');
			$xzLevel = ($xl === false || trim((string)$xl) === '') ? 9 : max(0, min(9, (int)$xl));
			$xtEnv = getenv('FRACTAL_ZIP_XZ_TIMEOUT_SEC');
			$xt = ($xtEnv === false || trim((string)$xtEnv) === '') ? 8.0 : max(0.0, (float)$xtEnv);
			$xzBlob = $this->outer_xz_blob($xzExe, $string, $xzLevel, $xt);
			if($xzBlob !== null && strlen($xzBlob) > 0 && strlen($xzBlob) < $bestLen) {
				$bestCodec = 'xz';
				$bestBlob = $xzBlob;
				$bestLen = strlen($xzBlob);
			}
		}
	}
	// Early-stop check relative to gzip baseline.
	if($bestLen < $gzLen) {
		$improve = $gzLen - $bestLen;
		$earlyStop = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
	}
	// Optional speed trade: skip 7z/arc when zstd/brotli already beats gzip by a margin. Default off (bytes-first).
	$skipSlowFastWinEnv = getenv('FRACTAL_ZIP_SKIP_SLOW_IF_FAST_WIN');
	$skipSlowFastWin = ($skipSlowFastWinEnv === false || trim((string)$skipSlowFastWinEnv) === '') ? false : ((int)$skipSlowFastWinEnv !== 0);
	if(!$earlyStop && $skipSlowFastWin && $bestCodec !== 'gzip' && $bestLen < $gzLen) {
		$maxEnv = getenv('FRACTAL_ZIP_SKIP_SLOW_IF_FAST_WIN_MAX_INNER_BYTES');
		$maxInner = ($maxEnv === false || trim((string)$maxEnv) === '') ? 1048576 : max(0, (int)$maxEnv);
		$minPctEnv = getenv('FRACTAL_ZIP_SKIP_SLOW_IF_FAST_WIN_MIN_PCT');
		$minPct = ($minPctEnv === false || trim((string)$minPctEnv) === '') ? 0.0018 : max(0.0, min(1.0, (float)$minPctEnv));
		if(($maxInner <= 0 || $innerLen <= $maxInner) && $gzLen > 0) {
			$improvePct = ((float)($gzLen - $bestLen)) / (float)$gzLen;
			if($improvePct >= $minPct) {
				$earlyStop = true;
			}
		}
	}
	// Huge compressible inners: zstd/brotli fast-win early stop skips arc/7z; re-open sweep only for very large inners (CSV/bundle scale), not midsize fractal outputs.
	$minSlowSweepEnv = getenv('FRACTAL_ZIP_HUGE_SLOW_OUTER_MIN_INNER_BYTES');
	$minSlowSweep = ($minSlowSweepEnv === false || trim((string)$minSlowSweepEnv) === '') ? 524288 : max(262144, (int)$minSlowSweepEnv);
	// Re-open slow 7z/arc exploration when zstd/brotli wins on *text-like* huge literals (FZB or unified-stream non-FZB:
	// CSV / highly repetitive text — PPMd/LZMA sometimes beats brotli by a few bytes). Binary-like FZB keeps separate gates.
	if($autoTextlikeFzbBrotliFull && !$speedMode && !$likelyIncompressible && getenv('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') !== '1' && $innerLen >= $minSlowSweep && ($bestCodec === 'zstd' || $bestCodec === 'brotli') && !$strongTinyBrotliOuter) {
		$earlyStop = false;
	}
	// Bytes-first: xz can crush gzip and trigger early-stop before 7z; LZMA2/PPMd sometimes edges xz on huge FZB/unified inners (e.g. test_files35 BMP).
	if(!$speedMode && !$likelyIncompressible && getenv('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') !== '1' && $innerLen >= $minSlowSweep && $bestCodec === 'xz' && $try7z && $seven !== null && getenv('FRACTAL_ZIP_SKIP_7Z') !== '1' && $innerLen >= $min7zInner) {
		$earlyStop = false;
	}

	// Slow candidates only if we didn't already get a clear win.
	$canTry7z = ($try7z && $seven !== null && getenv('FRACTAL_ZIP_SKIP_7Z') !== '1' && $innerLen >= $min7zInner);
	// Balanced huge-payload fallback (brotli skipped on huge): try ONE fast 7z lzma2 attempt before arc/7z sweeps.
	$hugeTry7zOnceEnv = getenv('FRACTAL_ZIP_HUGE_TRY_7Z_ONCE');
	$hugeTry7zOnce = ($hugeTry7zOnceEnv === false || trim((string)$hugeTry7zOnceEnv) === '') ? true : ((int)$hugeTry7zOnceEnv !== 0);
	if(!$earlyStop && $hugeTry7zOnce && $isHuge && !$fzbHuge && $seven !== null && getenv('FRACTAL_ZIP_SKIP_7Z') !== '1') {
		// Only do this when we're not running brotli full on huge.
		if($brotliHugeMode !== 'full' && $forceBrotli === false) {
			$one = $this->outer_7z_lzma2_blob($seven, $string);
			if($one !== null && strlen($one) > 0 && strlen($one) < $bestLen) {
				$bestCodec = '7z';
				$bestBlob = $one;
				$bestLen = strlen($one);
			}
			if($bestLen < $gzLen) {
				$improve = $gzLen - $bestLen;
				$earlyStop = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
			}
		}
	}
	// Optional: try arc before 7z (arc can beat gzip fast enough to skip slower 7z).
	$arcBefore7zEnv = getenv('FRACTAL_ZIP_ARC_BEFORE_7Z');
	// Default off: arc-before-7z tends to waste time on most corpora.
	$arcBefore7z = ($arcBefore7zEnv === false || trim((string)$arcBefore7zEnv) === '') ? false : ((int)$arcBefore7zEnv !== 0);
	if(!$earlyStop && $arcBefore7z && $canTryArc && !$likelyIncompressible) {
		$arcBlob = $this->outer_arc_blob($arcExe, $string);
		if($arcBlob !== null && strlen($arcBlob) > 0 && strlen($arcBlob) < $bestLen) {
			$bestCodec = 'arc';
			$bestBlob = $arcBlob;
			$bestLen = strlen($arcBlob);
		}
		if($bestLen < $gzLen) {
			$improve = $gzLen - $bestLen;
			$earlyStop = ($improve >= $stopBytes) || ($gzLen > 0 && ((float)$improve / (float)$gzLen) >= $stopPct);
		}
	}
	if($autoTextlikeFzbBrotliFull && !$speedMode && getenv('FRACTAL_ZIP_SKIP_HUGE_SLOW_OUTER_PASS') !== '1' && !$strongTinyBrotliOuter) {
		// Re-open slow outers for *text-like* huge literals (7z/PPMd vs brotli); same family as auto brotli-on-huge textlike.
		$earlyStop = false;
	}
	// Binary-like huge FZB (e.g. single BMP): keep 7z sweep unless explicitly skipped (wall time vs bytes).
	if($fzbHuge && !$outerTextlike && !$speedMode && getenv('FRACTAL_ZIP_FZB_BINARY_SKIP_7Z_SWEEP') === '1' && $bestGzipBlob !== null) {
		$earlyStop = true;
	}
	if(!$earlyStop && $canTry7z) {
		// Stop 7z method sweep once it beats current best.
		$sevenBlob = $this->outer_7z_best_blob($seven, $string, $innerLen, $bestLen - 1, $fzbHuge);
		if($sevenBlob !== null && $sevenBlob !== false && strlen($sevenBlob) > 0 && strlen($sevenBlob) < $bestLen) {
			$bestCodec = '7z';
			$bestBlob = $sevenBlob;
			$bestLen = strlen($sevenBlob);
		}
	}
	// Outer choice: smallest blob wins among candidates we try — no inner-size gate skipping arc after 7z (that hid wins on
	// large text bundles). FRACTAL_ZIP_SKIP_ARC=1 still disables arc entirely.
	// If we didn't try arc yet, try it last.
	if(!$earlyStop && (!$arcBefore7z) && $canTryArc && !$likelyIncompressible) {
		$arcBlob = $this->outer_arc_blob($arcExe, $string);
		if($arcBlob !== null && strlen($arcBlob) > 0 && strlen($arcBlob) < $bestLen) {
			$bestCodec = 'arc';
			$bestBlob = $arcBlob;
			$bestLen = strlen($arcBlob);
		}
	}
	if($seven === null) {
		fractal_zip::message_once('7-Zip not found (install 7-Zip or p7zip; add to PATH on Unix). Using gzip-9 for outer container.');
	} elseif($try7z && getenv('FRACTAL_ZIP_SKIP_7Z') !== '1') {
		fractal_zip::message_once('7-Zip available (' . $seven . '). Outer container compares staged candidates vs gzip-9 for each .fzc.');
	}
	if($arcExe !== null && getenv('FRACTAL_ZIP_SKIP_ARC') !== '1') {
		fractal_zip::message_once('FreeArc available (' . $arcExe . '). Outer container can also compare FreeArc for each .fzc.');
	}
	$allowOuterExpansion = getenv('FRACTAL_ZIP_ALLOW_OUTER_EXPANSION') === '1';
	if(!$allowOuterExpansion && $bestLen >= $innerLen) {
		fractal_zip::$last_outer_codec = 'store';
		return $string;
	}
	fractal_zip::$last_outer_codec = $bestCodec;
	return $bestBlob;
}

function adaptive_decompress($string) {
	if(strlen($string) >= 2 && $string[0] === '7' && $string[1] === 'z') {
		$seven = fractal_zip::seven_zip_executable();
		if($seven === null) {
			fractal_zip::fatal_error('Container is 7z format but no 7-Zip/p7zip binary was found. Install 7z and ensure it is on PATH (or on Windows in Program Files).');
		}
		$u = bin2hex(random_bytes(4));
		$temp_container_path = $this->program_path . DS . 'fzext_' . $u . $this->fractal_zip_container_file_extension;
		$outDir = $this->program_path . DS . 'fzext_' . $u . '_out';
		file_put_contents($temp_container_path, $string);
		if(!is_dir($outDir)) {
			mkdir($outDir, 0755, true);
		}
		$cmd = escapeshellarg($seven) . ' x ' . escapeshellarg($temp_container_path) . ' -aoa -o' . escapeshellarg($outDir);
		exec($cmd . ' 2>/dev/null', $output, $return);
		$inner = null;
		$fallbackInnerPath = null;
		if($return === 0) {
			foreach(scandir($outDir) ?: array() as $f) {
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
		foreach(scandir($outDir) ?: array() as $f) {
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
		$u = bin2hex(random_bytes(4));
		$tempArcPath = $this->program_path . DS . 'fzarc_' . $u . '.arc';
		$outDir = $this->program_path . DS . 'fzarc_' . $u . '_out';
		file_put_contents($tempArcPath, $string);
		if(!is_dir($outDir)) {
			mkdir($outDir, 0755, true);
		}
		$cwd = getcwd();
		chdir($outDir);
		$prefix = $this->command_prefix_with_local_lib();
		$cmd = $prefix . escapeshellarg($arcExe) . ' x -y ' . escapeshellarg($tempArcPath);
		exec($cmd . ' 2>/dev/null', $output, $return);
		if($cwd !== false) {
			chdir($cwd);
		}
		$inner = null;
		if($return === 0) {
			foreach(scandir($outDir) ?: array() as $f) {
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
		foreach(scandir($outDir) ?: array() as $f) {
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
	if(strlen($string) >= strlen($bMagic) && substr($string, 0, strlen($bMagic)) === $bMagic) {
		$brotliExe = fractal_zip::brotli_executable();
		if($brotliExe === null) {
			fractal_zip::fatal_error('Container uses brotli outer format but `brotli` was not found on PATH.');
		}
		$rest = substr($string, strlen($bMagic));
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
		$u = bin2hex(random_bytes(8));
		$tmpBase = fractal_zip::outer_codec_temp_dir();
		$tempIn = $tmpBase . DS . 'fzXZdin_' . $u;
		$tempOut = $tmpBase . DS . 'fzXZdout_' . $u;
		if(@file_put_contents($tempIn, $string) === false) {
			fractal_zip::fatal_error('Failed to write xz container temp file.');
		}
		$cmd = escapeshellarg($xzExe) . ' -d -c ' . escapeshellarg($tempIn);
		$desc = array(
			0 => array('pipe', 'r'),
			1 => array('file', $tempOut, 'wb'),
			2 => array('pipe', 'w'),
		);
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
	foreach($array as $index => $value) {
		$sum += $value;
	}
	return $sum / sizeof($array);
}

function preg_escape($string) {
	return str_replace('/', '\/', preg_quote($string));
}

function fatal_error($message) { 
	print('<span style="color: red;">' . $message . '</span>');exit(0);
}

function fatal_error_once($string) {
	static $printed_strings = array();
	if(isset($printed_strings[$string])) {
		return true;
	}
	$printed_strings[$string] = true;
	print('<span style="color: red;">' . $string . '</span>');exit(0);
}

function warning($message) { 
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
	print('<span style="color: orange;">' . $string . '</span><br>');
	return true;
}

function message($message) { 
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
	print('<span>' . $string . '</span><br>');
	return true;
}

function good_news($message) { 
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
	print('<span style="color: green;">' . $string . '</span><br>');
	return true;
}

}

?>