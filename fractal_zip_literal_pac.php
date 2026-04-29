<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac_registry.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_native_pac.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_jpeg_pac.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_jbig2_pac.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_jpx_pac.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_ccitt_pac.php';

/**
 * Literal-bundle preprocessing for common **single-blob** compressed types (after image_pac rasters).
 * Decompress → recompress at a high level → keep only if smaller and the **decompressed payload** matches (lossless).
 *
 * Aligns with external ../file_types/types.php “compressed” style categories where a CLI exists on PATH.
 * Registry: fractal_zip_literal_pac_registry.php (ids, extensions, magic, tools).
 * Not handled here: multi-member cpio (other than newc 1+TRAILER), multi-member archives generally, FLAC merge (FZCD), lossy audio/video re-encode.
 * Single-file ustar `.tar` peel/rebuild: `fractal_zip_literal_tar_ustar.php` + FZB mode 20 (`tar`). Single-member GNU `ar` / `.a`: `fractal_zip_literal_ar_gnu.php` + FZB mode 21 (`ar`). newc cpio one file + TRAILER: `fractal_zip_literal_cpio_newc.php` + FZB mode 22 (`cpio`). git loose object (blob|tree|commit|tag + size): `fractal_zip_literal_git_blob.php` + FZB mode 23 (`gitobj`). bencode / netstring / FWS SWF: `fractal_zip_literal_bencode_str.php` (24) / `fractal_zip_literal_netstring.php` (25) / `fractal_zip_literal_swf_fws.php` (26).
 *
 * Tools (optional; missing tool = no-op for that extension):
 *   gzip / pigz / zopfli → .gz, .svgz, .vgz (VGM; FRACTAL_ZIP_LITERALPAC_GZIP_ENGINE=auto|gzip|pigz|zopfli; ZOPFLI if FRACTAL_ZIP_LITERALPAC_ZOPFLI=1); pigz path: optional FRACTAL_ZIP_LITERALPAC_PIGZ_P (-p threads)
 *   bzip2 / pbzip2, xz, zstd, lz4, brotli, lzip / plzip, lzma → matching extensions; .lz uses LZIP magic vs raw lzma (when `fractal_zip` is loaded: **xz**, **lzma**, **zstd**, **lz4** recompress honor **FRACTAL_ZIP_XZ_** / **FRACTAL_ZIP_ZSTD** / **FRACTAL_ZIP_LZ4_THREADS** — raw `lzma`(1) trials insert the same **`-T`** argv tokens as **xz**); brotli smaller-trial Q11 adds **FRACTAL_ZIP_BROTLI_EXTRA_ARGS** / **FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS** before **-q**). **FRACTAL_ZIP_LITERALPAC_PBZIP2_P** (non-empty, not `off`/`bzip2`/`st`) → **pbzip2** with **`-p`**; unset ⇒ stock **bzip2**. **FRACTAL_ZIP_LITERALPAC_PLZIP_THREADS** (not `off`/`lzip`/`st`) and **plzip** on `PATH` → **plzip** with **`-n`**; unset ⇒ **lzip** only.
 *   woff2_compress / woff2_decompress → .woff2
 *   php-zip → .zip (exactly one member, no encryption; inner payload via getFromIndex; re-packed with max deflate when supported)
 *   7z/7za/7zz → .7z (exactly one file after extract)
 *   /FlateDecode recompress (PHP zlib or raw L9 if smaller) + /DCTDecode lossless jpegtran + optional /JBIG2Decode (jbig2dec -e + encoder) + /JPXDecode (OpenJPEG) + /CCITTFaxDecode (fax2tiff/tiffcp/convert); optional qpdf rewrites (default off) → .pdf. No Zopfli on in-PDF Flate (leave ratio to outer FZ/bundle). FRACTAL_ZIP_LITERALPAC_PDF_QPDF=1 enables qpdf; FRACTAL_ZIP_LITERALPAC_PDF_NATIVE / _JPEG / _JBIG2 / _JPX / _CCITT=0 to skip. Outer .gz literal path may still use gzip/pigz/zopfli per FRACTAL_ZIP_LITERALPAC_GZIP_*.
 *
 * FRACTAL_ZIP_LITERALPAC=0 disables only stream recompress (image_pac still runs).
 * FRACTAL_ZIP_LITERALPAC_STREAM=0 same as LITERALPAC=0 for streams; use FRACTAL_ZIP_IMAGEPAC=0 to disable rasters.
 * FRACTAL_ZIP_LITERALPAC_MAX_BYTES: max **compressed** blob (default **0** = no limit; was 40 MiB).
 * FRACTAL_ZIP_LITERALPAC_MAX_DECOMPRESSED_BYTES: max payload after decompress (default 128 MiB).
 *
 * FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER: default on — nested gzip peel (FZB mode 6 chain); exact restore per layer.
 *   FRACTAL_ZIP_LITERAL_GZIP_PEEL_MAX_LAYERS (default 64) caps depth. Set expand to 0 to disable.
 * FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP / FRACTAL_ZIP_LITERAL_SEMANTIC_7Z: default on — peel single-member ZIP / single-file 7z
 *   to inner payload (modes 7–8); extract rebuilds a canonical archive (same extracted bytes; container may differ).
 *   Semantic peels run only while no gzip layer is pending (gzip(zip(...)) cannot peel the inner ZIP without breaking
 *   bit-exact mode-6 gzip restore). zip(gzip(...)) peels fully: semantic first, then gzip; wrap is gzip then semantic.
 *   FRACTAL_ZIP_LITERAL_SEMANTIC_PEEL_MAX_LAYERS (default 48) caps total semantic + interleaved peels per literal.
 * FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ: default on — peel MoPAQ/MPQ (.sc2replay, .mpq, .w3x) to FMQ2 inner bytes (FZB mode 11)
 *   when tools/third_party/mpyq.py + python3 + smpq can repack with the same extracted members (semantic equivalence).
 *   FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_STRICT_BYTES=1 requires byte-identical repack instead (stricter; often fails on SC2 replays).
 *   Default: peel whenever semantic unpack succeeds. FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE=1 restores legacy gzip-1 proxy (skip peel if deflate-1(inner) >= deflate-1(outer)). FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_ALWAYS is legacy alias for “do not use proxy gate” (still recognized in older docs).
 *   FRACTAL_ZIP_LITERALPAC_MPQ_REPACK=0 disables automatic repack-smaller (smpq) before the optional MPQ_TOOL path (faster dev runs).
 *   FRACTAL_ZIP_MPQ_SMPQ_FULL_SCAN=1: try all MPQ version × compression combos in tools/fractal_zip_mpq_semantic.py (slower, marginally better repack).
 *   Without smpq, MPQ semantic peel is skipped (optional stream handler FRACTAL_ZIP_LITERALPAC_MPQ_TOOL is separate).
 * FRACTAL_ZIP_LITERALPAC_STREAM_PEEL_MAX_PASSES (default 24): max stream normalize rounds by extension + magic sniff.
 */

function fractal_zip_literal_pac_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_pac_stream_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	if (!fractal_zip_literal_pac_enabled()) {
		return $cached = false;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_STREAM');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_pac_max_compressed_bytes(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_MAX_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 0;
	}
	$v = (int) $e;
	return $cached = ($v <= 0 ? 0 : $v);
}

function fractal_zip_literal_pac_max_decompressed_bytes(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_MAX_DECOMPRESSED_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 128 * 1024 * 1024;
	}
	$v = (int) $e;
	return $cached = ($v <= 0 ? 0 : $v);
}

/**
 * Full literal preprocess: rasters (image_pac) then stream compressors (this module).
 */
function fractal_zip_literal_pac_preprocess_literal_for_bundle(string $relPath, string $rawBytes): string {
	if ($rawBytes === '') {
		return $rawBytes;
	}
	$rawBytes = fractal_zip_image_pac_preprocess_literal_for_bundle($relPath, $rawBytes);
	return fractal_zip_literal_pac_preprocess_streams_multipass($relPath, $rawBytes);
}

function fractal_zip_literal_pac_stream_peel_max_passes(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_STREAM_PEEL_MAX_PASSES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 24;
	}
	$v = (int) $e;
	return $cached = ($v <= 0 ? 1 : min(256, $v));
}

/**
 * Repeatedly apply stream handlers (path extension first, then magic sniff) while size improves.
 */
function fractal_zip_literal_pac_preprocess_streams_multipass(string $relPath, string $rawBytes): string {
	if (!fractal_zip_literal_pac_stream_enabled()) {
		return $rawBytes;
	}
	$maxC = fractal_zip_literal_pac_max_compressed_bytes();
	if ($maxC > 0 && strlen($rawBytes) > $maxC) {
		return $rawBytes;
	}
	$maxR = fractal_zip_literal_pac_stream_peel_max_passes();
	$ext = strtolower((string) pathinfo($relPath, PATHINFO_EXTENSION));
	for ($i = 0; $i < $maxR; $i++) {
		$got = fractal_zip_literal_pac_try_stream_smaller($ext, $rawBytes);
		if ($got === null) {
			$got = fractal_zip_literal_pac_try_stream_smaller_by_magic($rawBytes);
		}
		if ($got === null) {
			break;
		}
		$rawBytes = $got[0];
	}
	return $rawBytes;
}

/**
 * Raw .lz / LZMA-alone sniff is extension-driven; the magic path must not fork `lzma` on obvious non-LZMA blobs
 * (BMP/PNG/…): the registry row has no magic_prefix, so the old loop always invoked lzma_sniff last — catastrophic on rasters.
 *
 * True ⇒ allow `lzma_sniff` on the extension-agnostic magic path only.
 */
function fractal_zip_literal_pac_magic_lzma_sniff_plausible(string $compressed): bool {
	if (str_starts_with($compressed, 'LZIP')) {
		return true;
	}
	if (strlen($compressed) < 13) {
		return false;
	}
	foreach (FRACTAL_ZIP_LITERALPAC_REGISTRY as $row) {
		if (($row['handler'] ?? '') === 'lzma_sniff') {
			continue;
		}
		$mp = $row['magic_prefix'] ?? null;
		if ($mp !== null && $mp !== '' && str_starts_with($compressed, $mp)) {
			return false;
		}
	}
	if (str_starts_with($compressed, 'BM')) {
		return false;
	}
	if (str_starts_with($compressed, "\x89PNG\r\n\x1a\n")) {
		return false;
	}
	if (str_starts_with($compressed, 'GIF8')) {
		return false;
	}
	if (str_starts_with($compressed, "\xff\xd8\xff")) {
		return false;
	}
	if (str_starts_with($compressed, 'RIFF')) {
		return false;
	}
	if (str_starts_with($compressed, "II*\0") || str_starts_with($compressed, "MM\0*")) {
		return false;
	}
	if (str_starts_with($compressed, '%PDF')) {
		return false;
	}
	if (str_starts_with($compressed, 'MPQ')) {
		return false;
	}
	if (str_starts_with($compressed, 'CWS') || str_starts_with($compressed, 'FWS')) {
		return false;
	}
	return true;
}

/**
 * Try registry handlers in order when magic matches (extension-agnostic). Skips brotli (no stable magic).
 * Hot path: index rows by first magic byte so BMP/PNG/etc. skip gzip/xz/… str_starts_with chains; lzma_sniff is last and gated.
 */
function fractal_zip_literal_pac_try_stream_smaller_by_magic(string $compressed): ?array {
	static $rowsByFirstMagicByte = null;
	static $lzmaSniffRow = null;
	if ($rowsByFirstMagicByte === null) {
		$rowsByFirstMagicByte = [];
		foreach (FRACTAL_ZIP_LITERALPAC_REGISTRY as $row) {
			$h = $row['handler'] ?? '';
			if ($h === 'lzma_sniff') {
				$lzmaSniffRow = $row;
				continue;
			}
			$mp = $row['magic_prefix'] ?? null;
			if ($mp === null || $mp === '') {
				continue;
			}
			$rowsByFirstMagicByte[ord($mp[0])][] = $row;
		}
	}
	$n = strlen($compressed);
	if ($n === 0) {
		return null;
	}
	$c0 = ord($compressed[0]);
	foreach ($rowsByFirstMagicByte[$c0] ?? [] as $row) {
		$mp = $row['magic_prefix'] ?? '';
		if ($mp === '' || !str_starts_with($compressed, $mp)) {
			continue;
		}
		$r = fractal_zip_literal_pac_run_registry_handler($row['handler'], $compressed);
		if ($r !== null) {
			return $r;
		}
	}
	if ($lzmaSniffRow !== null && fractal_zip_literal_pac_magic_lzma_sniff_plausible($compressed)) {
		$r = fractal_zip_literal_pac_run_registry_handler('lzma_sniff', $compressed);
		if ($r !== null) {
			return $r;
		}
	}
	return null;
}

/** @return list<string> */
function fractal_zip_literal_pac_stream_extensions(): array {
	static $exts = null;
	if ($exts !== null) {
		return $exts;
	}
	$set = [];
	foreach (FRACTAL_ZIP_LITERALPAC_REGISTRY as $row) {
		foreach ($row['extensions'] as $e) {
			$set[$e] = true;
		}
	}
	$exts = array_keys($set);
	sort($exts, SORT_STRING);
	return $exts;
}

/**
 * Registry row indices for extension (hot path: avoids scanning the full registry per file in multipass PAC).
 *
 * @return list<int>
 */
function fractal_zip_literal_pac_registry_indices_for_extension(string $ext): array {
	static $map = null;
	if ($map === null) {
		$map = [];
		foreach (FRACTAL_ZIP_LITERALPAC_REGISTRY as $idx => $row) {
			foreach ($row['extensions'] as $e) {
				$map[$e][] = $idx;
			}
		}
	}
	return $map[$ext] ?? [];
}

/**
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_stream_smaller(string $ext, string $compressed): ?array {
	foreach (fractal_zip_literal_pac_registry_indices_for_extension($ext) as $idx) {
		$row = FRACTAL_ZIP_LITERALPAC_REGISTRY[$idx];
		$mp = $row['magic_prefix'] ?? null;
		if ($mp !== null && $mp !== '' && !str_starts_with($compressed, $mp)) {
			continue;
		}
		$r = fractal_zip_literal_pac_run_registry_handler($row['handler'], $compressed);
		if ($r !== null) {
			return $r;
		}
	}
	return null;
}

/**
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_run_registry_handler(string $handler, string $compressed): ?array {
	return match ($handler) {
		'gzip_variants' => fractal_zip_literal_pac_try_gzip_smaller($compressed),
		'bzip2' => fractal_zip_literal_pac_try_bzip2_smaller($compressed),
		'xz' => fractal_zip_literal_pac_try_xz_smaller($compressed),
		'zstd' => fractal_zip_literal_pac_try_zstd_smaller($compressed),
		'lz4' => fractal_zip_literal_pac_try_lz4_smaller($compressed),
		'brotli' => fractal_zip_literal_pac_try_brotli_smaller($compressed),
		'lzip' => fractal_zip_literal_pac_try_lzip_smaller($compressed),
		'lzma_sniff' => fractal_zip_literal_pac_try_lzma_or_lzip_smaller($compressed),
		'woff2' => fractal_zip_literal_pac_try_woff2_smaller($compressed),
		'zip_single' => fractal_zip_literal_pac_try_zip_single_smaller($compressed),
		'seven_single' => fractal_zip_literal_pac_try_seven_single_smaller($compressed),
		'pdf_native_flate' => fractal_zip_literal_pac_try_pdf_native_flate_smaller($compressed),
		'pdf_dct_jpeg' => fractal_zip_literal_pac_try_pdf_dct_jpeg_smaller($compressed),
		'pdf_jbig2' => fractal_zip_literal_pac_try_pdf_jbig2_smaller($compressed),
		'pdf_jpx' => fractal_zip_literal_pac_try_pdf_jpx_smaller($compressed),
		'pdf_ccitt' => fractal_zip_literal_pac_try_pdf_ccitt_smaller($compressed),
		'pdf_qpdf' => fractal_zip_literal_pac_try_pdf_qpdf_smaller($compressed),
		'mpq_optional' => fractal_zip_literal_pac_try_mpq_optional_smaller($compressed),
		default => null,
	};
}

/**
 * Optional MoPAQ/MPQ peel when FRACTAL_ZIP_LITERALPAC_MPQ_TOOL points to an executable or a .py script:
 *   tool <input_file> <output_file>   (python3 <script>.py … when the path ends in .py)
 * Output must be the replacement literal bytes; accepted only if smaller than input and within decompressed limits.
 */
function fractal_zip_literal_pac_mpq_optional_argv(string $toolPath, string $inPath, string $outPath): ?array {
	$toolPath = trim($toolPath);
	if ($toolPath === '' || !is_file($toolPath)) {
		return null;
	}
	$low = strtolower($toolPath);
	if (str_ends_with($low, '.py')) {
		if (!is_readable($toolPath) || !fractal_zip_literal_pac_cmd_ok('python3')) {
			return null;
		}
		return array('python3', $toolPath, $inPath, $outPath);
	}
	if (!is_executable($toolPath)) {
		return null;
	}
	return array($toolPath, $inPath, $outPath);
}

/**
 * Smallest extract-equivalent MPQ via smpq (tools/fractal_zip_mpq_semantic.py repack-smaller).
 * FRACTAL_ZIP_LITERALPAC_MPQ_REPACK=0 disables.
 *
 * @return array{0: string, 1: int}|null [newBlob, bytesSaved]
 */
function fractal_zip_literal_pac_try_mpq_semantic_repack_smaller(string $compressed): ?array {
	if (strlen($compressed) < 16 || substr($compressed, 0, 3) !== 'MPQ') {
		return null;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_MPQ_REPACK');
	if ($e !== false) {
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
			return null;
		}
	}
	if (!fractal_zip_literal_pac_cmd_ok('python3') || !fractal_zip_literal_pac_cmd_ok('smpq')) {
		return null;
	}
	$script = fractal_zip_literal_pac_mpq_semantic_script();
	if ($script === null) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzmpqrs_' . bin2hex(random_bytes(8));
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	$inPath = $td . DIRECTORY_SEPARATOR . 'in.bin';
	$outPath = $td . DIRECTORY_SEPARATOR . 'out.bin';
	try {
		if (file_put_contents($inPath, $compressed) === false) {
			return null;
		}
		$cmd = array('python3', $script, 'repack-smaller', $inPath, $outPath);
		$desc = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$procOpts = array('bypass_shell' => true);
		$proc = @proc_open($cmd, $desc, $pipes, null, null, $procOpts);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[2], 1048576);
			}
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
		}
		$code = proc_close($proc);
		if ($code !== 0 || !is_file($outPath)) {
			return null;
		}
		$out = file_get_contents($outPath);
		if ($out === false || $out === '') {
			return null;
		}
		if (!fractal_zip_literal_pac_payload_within_limit(strlen($out))) {
			return null;
		}
		if (strlen($out) >= strlen($compressed)) {
			return null;
		}
		return array($out, strlen($compressed) - strlen($out));
	} finally {
		@unlink($inPath);
		@unlink($outPath);
		@rmdir($td);
	}
}

function fractal_zip_literal_pac_try_mpq_optional_smaller(string $compressed): ?array {
	if (strlen($compressed) < 16) {
		return null;
	}
	if (substr($compressed, 0, 3) !== 'MPQ') {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$pre = fractal_zip_literal_pac_try_mpq_semantic_repack_smaller($compressed);
	if ($pre !== null) {
		return $pre;
	}
	$tool = getenv('FRACTAL_ZIP_LITERALPAC_MPQ_TOOL');
	if ($tool === false) {
		return null;
	}
	$tool = trim((string) $tool);
	if ($tool === '') {
		return null;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzmpq_' . bin2hex(random_bytes(8));
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	$inPath = $td . DIRECTORY_SEPARATOR . 'in.bin';
	$outPath = $td . DIRECTORY_SEPARATOR . 'out.bin';
	try {
		if (file_put_contents($inPath, $compressed) === false) {
			return null;
		}
		$cmd = fractal_zip_literal_pac_mpq_optional_argv((string) $tool, $inPath, $outPath);
		if ($cmd === null) {
			return null;
		}
		$desc = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$procOpts = array('bypass_shell' => true);
		$proc = @proc_open($cmd, $desc, $pipes, null, null, $procOpts);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[2], 1048576);
			}
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
		}
		$code = proc_close($proc);
		if ($code !== 0 || !is_file($outPath)) {
			return null;
		}
		$out = file_get_contents($outPath);
		if ($out === false || $out === '') {
			return null;
		}
		if (!fractal_zip_literal_pac_payload_within_limit(strlen($out))) {
			return null;
		}
		if (strlen($out) >= strlen($compressed)) {
			return null;
		}
		return array($out, strlen($compressed) - strlen($out));
	} finally {
		@unlink($inPath);
		@unlink($outPath);
		@rmdir($td);
	}
}

function fractal_zip_literal_pac_cmd_ok(string $name): bool {
	static $cache = [];
	if (isset($cache[$name])) {
		return $cache[$name];
	}
	if ($name === '' || strpos($name, "\0") !== false) {
		return $cache[$name] = false;
	}
	// Non-Windows: walk PATH + common bins (no sh); avoids shell_exec on every distinct tool name.
	if (PHP_VERSION_ID >= 70400 && DIRECTORY_SEPARATOR !== '\\') {
		$pathEnv = getenv('PATH');
		if (is_string($pathEnv) && $pathEnv !== '') {
			$start = 0;
			$plen = strlen($pathEnv);
			while ($start <= $plen) {
				$sep = strpos($pathEnv, PATH_SEPARATOR, $start);
				$dir = ($sep === false) ? substr($pathEnv, $start) : substr($pathEnv, $start, $sep - $start);
				$start = ($sep === false) ? $plen + 1 : $sep + 1;
				$dir = str_replace('\\', '/', trim($dir));
				if ($dir === '') {
					continue;
				}
				$candidate = $dir . '/' . $name;
				if (is_file($candidate) && is_executable($candidate)) {
					return $cache[$name] = true;
				}
			}
		}
		foreach (['/usr/local/bin', '/usr/bin', '/bin', '/sbin'] as $dir) {
			if (!is_dir($dir)) {
				continue;
			}
			$candidate = $dir . '/' . $name;
			if (is_file($candidate) && is_executable($candidate)) {
				return $cache[$name] = true;
			}
		}
	}
	$p = shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null');
	return $cache[$name] = (is_string($p) && trim($p) !== '');
}

function fractal_zip_literal_pac_payload_within_limit(int $len): bool {
	$max = fractal_zip_literal_pac_max_decompressed_bytes();
	return $max <= 0 || $len <= $max;
}

/**
 * Fingerprint for two-slot static LRU keys: one O(n) pass, no duplicate pinning of huge blobs vs `===` on cached copies.
 *
 * @return array{0: int, 1: string}
 */
function fractal_zip_literal_pac_static_lru_fp(string $blob): array {
	return [strlen($blob), md5($blob, true)];
}

/**
 * Decompress a gzip file (RFC1952) in-process — matches `gzip -dc` / `pigz -dc` including concatenated gzip members.
 * Falls back to null on zlib errors (caller may use shell). Set FRACTAL_ZIP_LITERALPAC_PHP_GZIP_DC=0 to skip in shell_decompress.
 */
function fractal_zip_literal_pac_php_gzip_read_file(string $filePath): ?string {
	$h = @gzopen($filePath, 'rb');
	if ($h === false) {
		return null;
	}
	$parts = [];
	while (!gzeof($h)) {
		$chunk = @gzread($h, 1048576);
		if ($chunk === false) {
			@gzclose($h);
			return null;
		}
		$parts[] = $chunk;
	}
	@gzclose($h);
	return implode('', $parts);
}

/**
 * Run [tool, ...args] with stdin read from file, capture stdout — no shell (PHP 7.4+ argv proc_open).
 * Falls back to null on failure so callers can use shell_exec.
 */
function fractal_zip_literal_pac_proc_file_stdin_to_string(string $tool, array $args, string $payloadPath): ?string {
	if (PHP_VERSION_ID < 70400) {
		return null;
	}
	if (!is_file($payloadPath) || !is_readable($payloadPath)) {
		return null;
	}
	$cmd = array_merge([(string) $tool], $args);
	$desc = [
		0 => ['file', $payloadPath, 'rb'],
		1 => ['pipe', 'w'],
		2 => ['pipe', 'w'],
	];
	$opts = ['bypass_shell' => true];
	$proc = @proc_open($cmd, $desc, $pipes, null, null, $opts);
	if (!is_resource($proc)) {
		return null;
	}
	if (function_exists('stream_set_chunk_size') && isset($pipes[1]) && is_resource($pipes[1])) {
		@stream_set_chunk_size($pipes[1], 1048576);
	}
	if (function_exists('stream_set_chunk_size') && isset($pipes[2]) && is_resource($pipes[2])) {
		@stream_set_chunk_size($pipes[2], 1048576);
	}
	$out = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	stream_get_contents($pipes[2]);
	fclose($pipes[2]);
	$code = proc_close($proc);
	if ($code !== 0 || !is_string($out) || $out === '') {
		return null;
	}
	return $out;
}

/**
 * One external subcommand: argv0 + path; no shell (then exec fallback for Windows / old PHP).
 */
function fractal_zip_literal_pac_tool_path_exit_code(string $cmdName, string $onePath, ?string $chdir = null): int {
	$ret = 1;
	if (PHP_VERSION_ID >= 70400 && DIRECTORY_SEPARATOR !== '\\') {
		$devNull = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
		// Child reads path from argv; bind stdin to NUL (not a pipe) — same as sh tool <&-
		$desc = [0 => ['file', $devNull, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
		$argv = [$cmdName, $onePath];
		$proc = @proc_open($argv, $desc, $pipes, $chdir, null, ['bypass_shell' => true]);
		if (is_resource($proc)) {
			if (function_exists('stream_set_chunk_size') && isset($pipes[1]) && is_resource($pipes[1])) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			if (isset($pipes[1]) && is_resource($pipes[1])) {
				stream_get_contents($pipes[1]);
				fclose($pipes[1]);
			}
			if (isset($pipes[2]) && is_resource($pipes[2])) {
				stream_get_contents($pipes[2]);
				fclose($pipes[2]);
			}
			$ret = proc_close($proc);
		}
		if ($ret === 0) {
			return 0;
		}
	}
	$null = [];
	@exec($cmdName . ' ' . escapeshellarg($onePath) . ' 2>/dev/null', $null, $ret);
	return (int) $ret;
}

/**
 * Brotli with -i/-o (full argv). Tries direct execve, then sh exec fallback.
 * @param list<string> $argvB
 */
function fractal_zip_literal_pac_brotli_cli(array $argvB): int {
	if (PHP_VERSION_ID >= 70400) {
		$devNull = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
		$desc = [0 => ['file', $devNull, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
		$proc = @proc_open($argvB, $desc, $pipes, null, null, ['bypass_shell' => true]);
		if (is_resource($proc)) {
			if (function_exists('stream_set_chunk_size') && isset($pipes[1]) && is_resource($pipes[1])) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			if (isset($pipes[1]) && is_resource($pipes[1])) {
				stream_get_contents($pipes[1]);
				fclose($pipes[1]);
			}
			if (isset($pipes[2]) && is_resource($pipes[2])) {
				stream_get_contents($pipes[2]);
				fclose($pipes[2]);
			}
			$ex = (int) proc_close($proc);
			if ($ex === 0) {
				return 0;
			}
		}
	}
	$null = [];
	$esc = [];
	foreach ($argvB as $a) {
		$esc[] = escapeshellarg($a);
	}
	$line = implode(' ', $esc);
	$ret = 1;
	@exec($line . ' 2>/dev/null', $null, $ret);
	return (int) $ret;
}

/**
 * `tool` + args + file path → captured stdout via execve (no shell). Used for decompress and similar.
 * Opt out globally: FRACTAL_ZIP_LITERALPAC_SHELL_PROC=0 (then shell_decompress / callers use shell_exec fallback).
 *
 * @param list<string> $argsBeforePath
 */
function fractal_zip_literal_pac_proc_argv_file_stdout(string $tool, array $argsBeforePath, string $filePath): ?string {
	if (PHP_VERSION_ID < 70400 || DIRECTORY_SEPARATOR === '\\' || !is_file($filePath) || !is_readable($filePath)) {
		return null;
	}
	if (strpos($filePath, "\0") !== false || strpos((string) $tool, "\0") !== false) {
		return null;
	}
	foreach ($argsBeforePath as $a) {
		if (!is_string($a) || strpos($a, "\0") !== false) {
			return null;
		}
	}
	$argv = array_merge([(string) $tool], array_values($argsBeforePath), [$filePath]);
	$devN = DIRECTORY_SEPARATOR === '\\' ? 'NUL' : '/dev/null';
	$desc = [0 => ['file', $devN, 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
	$proc = @proc_open($argv, $desc, $pipes, null, null, ['bypass_shell' => true]);
	if (!is_resource($proc)) {
		return null;
	}
	if (function_exists('stream_set_chunk_size') && isset($pipes[1]) && is_resource($pipes[1])) {
		@stream_set_chunk_size($pipes[1], 1048576);
	}
	if (function_exists('stream_set_chunk_size') && isset($pipes[2]) && is_resource($pipes[2])) {
		@stream_set_chunk_size($pipes[2], 1048576);
	}
	$out = is_resource($pipes[1] ?? null) ? stream_get_contents($pipes[1]) : false;
	if (is_resource($pipes[1] ?? null)) {
		fclose($pipes[1]);
	}
	if (is_resource($pipes[2] ?? null)) {
		stream_get_contents($pipes[2]);
		fclose($pipes[2]);
	}
	$code = proc_close($proc);
	if ($code !== 0 || $out === false) {
		return null;
	}
	if (!is_string($out)) {
		return null;
	}
	return $out;
}

/**
 * `tool -dc $file` → captured stdout, no shell (bzip2/xz/zstd/lz4/lzma/lzip/gzip when argv works).
 * Set FRACTAL_ZIP_LITERALPAC_PROC_DC=0 to force shell in shell_decompress for the `-dc` case only.
 */
function fractal_zip_literal_pac_proc_dc_file_to_string(string $tool, string $filePath): ?string {
	return fractal_zip_literal_pac_proc_argv_file_stdout($tool, ['-dc'], $filePath);
}

/**
 * Decompress via shell to string; null on failure or over max decompressed size.
 */
function fractal_zip_literal_pac_shell_decompress(string $tool, array $argsBeforePath, string $filePath): ?string {
	if (!fractal_zip_literal_pac_cmd_ok($tool)) {
		return null;
	}
	// Hot path: avoid fork/exec for gzip/pigz -dc (same semantics as gzip -dc for zlib-wrapped streams).
	if (($tool === 'gzip' || $tool === 'pigz')
		&& $argsBeforePath === ['-dc']
		&& getenv('FRACTAL_ZIP_LITERALPAC_PHP_GZIP_DC') !== '0'
	) {
		$phpOut = fractal_zip_literal_pac_php_gzip_read_file($filePath);
		if ($phpOut !== null) {
			if (!fractal_zip_literal_pac_payload_within_limit(strlen($phpOut))) {
				return null;
			}
			return $phpOut;
		}
	}
	// Any tool/argv: execve (no sh) when allowed — covers `-dc` and future non-shell decompress args.
	$shellProcOff = getenv('FRACTAL_ZIP_LITERALPAC_SHELL_PROC') === '0';
	$procDcOff = getenv('FRACTAL_ZIP_LITERALPAC_PROC_DC') === '0';
	if (!$shellProcOff && PHP_VERSION_ID >= 70400 && DIRECTORY_SEPARATOR !== '\\') {
		if ($argsBeforePath !== ['-dc'] || !$procDcOff) {
			$procOut = fractal_zip_literal_pac_proc_argv_file_stdout($tool, $argsBeforePath, $filePath);
			if ($procOut !== null) {
				if (!fractal_zip_literal_pac_payload_within_limit(strlen($procOut))) {
					return null;
				}
				return $procOut;
			}
		}
	}
	$cmdParts = [escapeshellarg($tool)];
	foreach ($argsBeforePath as $a) {
		$cmdParts[] = escapeshellarg((string) $a);
	}
	$cmdParts[] = escapeshellarg($filePath);
	$cmd = implode(' ', $cmdParts) . ' 2>/dev/null';
	$out = shell_exec($cmd);
	if (!is_string($out)) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit(strlen($out))) {
		return null;
	}
	return $out;
}

/**
 * Compress payload from temp file to stdout capture; returns blob or null.
 */
function fractal_zip_literal_pac_shell_compress_to_string(string $tool, array $args, string $payloadPath): ?string {
	if (!fractal_zip_literal_pac_cmd_ok($tool)) {
		return null;
	}
	$argStrs = [];
	foreach ($args as $a) {
		$argStrs[] = (string) $a;
	}
	$out = fractal_zip_literal_pac_proc_file_stdin_to_string($tool, $argStrs, $payloadPath);
	if (is_string($out) && $out !== '') {
		return $out;
	}
	$cmdParts = [escapeshellarg($tool)];
	foreach ($argStrs as $a) {
		$cmdParts[] = escapeshellarg($a);
	}
	$cmd = implode(' ', $cmdParts) . ' < ' . escapeshellarg($payloadPath) . ' 2>/dev/null';
	$out2 = shell_exec($cmd);
	return is_string($out2) && $out2 !== '' ? $out2 : null;
}

function fractal_zip_literal_pac_temp_pair(string $suffix): array {
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	return [$td . DIRECTORY_SEPARATOR . 'fzl_' . $id . '_in.' . $suffix, $td . DIRECTORY_SEPARATOR . 'fzl_' . $id . '_pay.bin'];
}

/**
 * Split a leading-space thread fragment (from {@code fractal_zip} helpers) into argv tokens for {@code proc_open} / shell.
 *
 * @return list<string>
 */
function fractal_zip_literal_pac_argv_tokens_from_lib_thread_fragment(string $fragment): array {
	$s = trim($fragment);
	if ($s === '') {
		return [];
	}
	$parts = preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY);
	return is_array($parts) ? $parts : [];
}

/**
 * Optional {@code pigz -p} for {@code try_gzip_smaller} when engine is pigz ({@code FRACTAL_ZIP_LITERALPAC_PIGZ_P}).
 * Unset ⇒ omit. {@code auto}/{@code on}/{@code all} ⇒ {@code -p N} from {@code nproc} when available, else 8. Digits ⇒ that thread count. {@code off}/{@code no} ⇒ omit.
 *
 * @return list<string>
 */
function fractal_zip_literal_pac_pigz_p_argv(): array {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PIGZ_P');
	if ($e === false || trim((string) $e) === '') {
		return [];
	}
	$v = trim((string) $e);
	if (strcasecmp($v, 'off') === 0 || strcasecmp($v, 'no') === 0) {
		return [];
	}
	if (strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'on') === 0 || strcasecmp($v, 'all') === 0) {
		$n = trim((string) @shell_exec('command -v nproc >/dev/null 2>&1 && nproc 2>/dev/null'));
		$p = (ctype_digit($n) && (int) $n > 0) ? $n : '8';
		return ['-p', $p];
	}
	if (ctype_digit($v)) {
		return ['-p', $v];
	}
	return [];
}

/**
 * Optional **pbzip2** {@code -p} for bzip2-style smaller-trial recompress when {@code FRACTAL_ZIP_LITERALPAC_PBZIP2_P} opts into **pbzip2** (see {@see fractal_zip_literal_pac_bzip2_wants_pbzip2}).
 * Unset/empty ⇒ omit. {@code off} / {@code 0} / {@code bzip2} / {@code st} ⇒ use stock **bzip2** (ignore this argv). {@code auto} / {@code on} / {@code all} ⇒ {@code -p} from **nproc** (fallback 4). Digits ⇒ **{@code -pN}**.
 *
 * @return list<string>
 */
function fractal_zip_literal_pac_pbzip2_p_argv(): array {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PBZIP2_P');
	if ($e === false || trim((string) $e) === '') {
		return [];
	}
	$v = trim((string) $e);
	$lo = strtolower($v);
	if ($lo === 'off' || $lo === 'no' || $lo === '0' || $lo === 'bzip2' || $lo === 'st') {
		return [];
	}
	if ($lo === 'auto' || $lo === 'on' || $lo === 'all') {
		$n = trim((string) @shell_exec('command -v nproc >/dev/null 2>&1 && nproc 2>/dev/null'));
		$p = (ctype_digit($n) && (int) $n > 0) ? $n : '4';
		return ['-p', $p];
	}
	if (ctype_digit($v)) {
		return ['-p', $v];
	}
	return [];
}

function fractal_zip_literal_pac_bzip2_wants_pbzip2(): bool {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PBZIP2_P');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$lo = strtolower(trim((string) $e));
	if ($lo === 'off' || $lo === 'no' || $lo === '0' || $lo === 'bzip2' || $lo === 'st') {
		return false;
	}
	return fractal_zip_literal_pac_cmd_ok('pbzip2');
}

/**
 * Extra **plzip** argv before compress/decompress flags: **{@code -n}** {@code N} threads (see plzip man: {@code -n}/{@code --threads=}).
 * Only used when {@see fractal_zip_literal_pac_plzip_wants_plzip}. Return **{@code []}** to let plzip use its default thread count (e.g. env **{@code default}** or **{@code plzip}**).
 *
 * @return list<string>
 */
function fractal_zip_literal_pac_plzip_threads_argv(): array {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PLZIP_THREADS');
	if ($e === false || trim((string) $e) === '') {
		return [];
	}
	$v = trim((string) $e);
	$lo = strtolower($v);
	if ($lo === 'default' || $lo === 'plzip') {
		return [];
	}
	if ($lo === 'auto' || $lo === 'on' || $lo === 'all') {
		$n = trim((string) @shell_exec('command -v nproc >/dev/null 2>&1 && nproc 2>/dev/null'));
		$p = (ctype_digit($n) && (int) $n > 0) ? $n : '4';
		return ['-n', $p];
	}
	if (ctype_digit($v)) {
		return ['-n', $v];
	}
	return [];
}

function fractal_zip_literal_pac_plzip_wants_plzip(): bool {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PLZIP_THREADS');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$lo = strtolower(trim((string) $e));
	if ($lo === 'off' || $lo === 'no' || $lo === '0' || $lo === 'lzip' || $lo === 'st') {
		return false;
	}
	return fractal_zip_literal_pac_cmd_ok('plzip');
}

function fractal_zip_literal_pac_try_recompress(string $compressed, string $inSuffix, string $tool, array $decompArgs, array $compArgs): ?array {
	[$inPath, $payPath] = fractal_zip_literal_pac_temp_pair($inSuffix);
	if (file_put_contents($inPath, $compressed) === false) {
		return null;
	}
	$payload = fractal_zip_literal_pac_shell_decompress($tool, $decompArgs, $inPath);
	@unlink($inPath);
	if ($payload === null || $payload === '') {
		@unlink($payPath);
		return null;
	}
	if (file_put_contents($payPath, $payload) === false) {
		return null;
	}
	$newC = fractal_zip_literal_pac_shell_compress_to_string($tool, $compArgs, $payPath);
	@unlink($payPath);
	if ($newC === null) {
		return null;
	}
	$saved = strlen($compressed) - strlen($newC);
	if ($saved <= 0) {
		return null;
	}
	$tmpIn = fractal_zip_literal_pac_temp_pair($inSuffix)[0];
	if (file_put_contents($tmpIn, $newC) === false) {
		return null;
	}
	$v = fractal_zip_literal_pac_shell_decompress($tool, $decompArgs, $tmpIn);
	@unlink($tmpIn);
	if ($v !== $payload) {
		return null;
	}
	return [$newC, $saved];
}

function fractal_zip_literal_pac_gzip_engine(): string {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_GZIP_ENGINE');
	if ($e === false || trim((string) $e) === '') {
		return 'auto';
	}
	return strtolower(trim((string) $e));
}

function fractal_zip_literal_pac_zopfli_wanted(): bool {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_ZOPFLI');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
}

function fractal_zip_literal_pac_zopfli_gzip_blob(string $payloadPath): ?string {
	$null = [];
	$ret = 1;
	@exec('zopfli --gzip ' . escapeshellarg($payloadPath) . ' 2>/dev/null', $null, $ret);
	if ($ret !== 0) {
		return null;
	}
	$gzPath = $payloadPath . '.gz';
	if (!is_file($gzPath)) {
		return null;
	}
	$b = file_get_contents($gzPath);
	@unlink($gzPath);
	return is_string($b) && $b !== '' ? $b : null;
}

function fractal_zip_literal_pac_verify_gzip_payload(string $gzipBlob, string $payload): bool {
	$verify = fractal_zip_literal_pac_cmd_ok('gzip') ? 'gzip' : (fractal_zip_literal_pac_cmd_ok('pigz') ? 'pigz' : null);
	if ($verify === null) {
		return false;
	}
	[$tmp] = fractal_zip_literal_pac_temp_pair('gz');
	if (file_put_contents($tmp, $gzipBlob) === false) {
		return false;
	}
	$v = fractal_zip_literal_pac_shell_decompress($verify, ['-dc'], $tmp);
	@unlink($tmp);
	return $v === $payload;
}

function fractal_zip_literal_pac_try_gzip_smaller(string $compressed): ?array {
	$decTool = fractal_zip_literal_pac_cmd_ok('gzip') ? 'gzip' : (fractal_zip_literal_pac_cmd_ok('pigz') ? 'pigz' : null);
	if ($decTool === null) {
		return null;
	}
	[$inPath, $payPath] = fractal_zip_literal_pac_temp_pair('gz');
	if (file_put_contents($inPath, $compressed) === false) {
		return null;
	}
	$payload = fractal_zip_literal_pac_shell_decompress($decTool, ['-dc'], $inPath);
	@unlink($inPath);
	if ($payload === null || $payload === '') {
		@unlink($payPath);
		return null;
	}
	if (file_put_contents($payPath, $payload) === false) {
		return null;
	}
	$engine = fractal_zip_literal_pac_gzip_engine();
	$candidates = [];
	if (($engine === 'auto' || $engine === 'gzip') && fractal_zip_literal_pac_cmd_ok('gzip')) {
		$c = fractal_zip_literal_pac_shell_compress_to_string('gzip', ['-9cn'], $payPath);
		if ($c !== null) {
			$candidates[] = $c;
		}
	}
	if (($engine === 'auto' || $engine === 'pigz') && fractal_zip_literal_pac_cmd_ok('pigz')) {
		$c = fractal_zip_literal_pac_shell_compress_to_string('pigz', array_merge(fractal_zip_literal_pac_pigz_p_argv(), ['-11', '-n', '-c']), $payPath);
		if ($c !== null) {
			$candidates[] = $c;
		}
	}
	$wantZ = ($engine === 'auto' && fractal_zip_literal_pac_zopfli_wanted()) || $engine === 'zopfli';
	if ($wantZ && fractal_zip_literal_pac_cmd_ok('zopfli')) {
		$z = fractal_zip_literal_pac_zopfli_gzip_blob($payPath);
		if ($z !== null) {
			$candidates[] = $z;
		}
	}
	@unlink($payPath);
	if ($candidates === []) {
		return null;
	}
	$origLen = strlen($compressed);
	$best = null;
	$bestLen = $origLen;
	foreach ($candidates as $blob) {
		$len = strlen($blob);
		if ($len >= $bestLen) {
			continue;
		}
		if (!fractal_zip_literal_pac_verify_gzip_payload($blob, $payload)) {
			continue;
		}
		$best = $blob;
		$bestLen = $len;
	}
	if ($best === null) {
		return null;
	}
	$saved = $origLen - $bestLen;
	if ($saved <= 0) {
		return null;
	}
	return [$best, $saved];
}

function fractal_zip_literal_pac_try_bzip2_smaller(string $compressed): ?array {
	if (fractal_zip_literal_pac_bzip2_wants_pbzip2()) {
		$pa = fractal_zip_literal_pac_pbzip2_p_argv();
		$de = $pa === [] ? ['-dc'] : array_merge($pa, ['-dc']);
		$co = $pa === [] ? ['-9', '-c'] : array_merge($pa, ['-9', '-c']);
		return fractal_zip_literal_pac_try_recompress($compressed, 'bz2', 'pbzip2', $de, $co);
	}
	return fractal_zip_literal_pac_try_recompress($compressed, 'bz2', 'bzip2', ['-dc'], ['-9', '-c']);
}

function fractal_zip_literal_pac_try_xz_smaller(string $compressed): ?array {
	$de = ['-dc'];
	$co = ['-9', '-c'];
	if (class_exists('fractal_zip', false)) {
		$de = array_merge(
			fractal_zip_literal_pac_argv_tokens_from_lib_thread_fragment(fractal_zip::library_xz_decompress_thread_shell_fragment_for_exec()),
			$de
		);
		$ct = fractal_zip_literal_pac_argv_tokens_from_lib_thread_fragment(fractal_zip::library_xz_compress_thread_shell_fragment_for_exec());
		$co = $ct === [] ? $co : array_merge(['-9'], $ct, ['-c']);
	}
	return fractal_zip_literal_pac_try_recompress($compressed, 'xz', 'xz', $de, $co);
}

function fractal_zip_literal_pac_try_lzma_smaller(string $compressed): ?array {
	if (!fractal_zip_literal_pac_cmd_ok('lzma')) {
		return null;
	}
	$de = ['-dc'];
	$co = ['-9', '-c'];
	if (class_exists('fractal_zip', false)) {
		$de = array_merge(
			fractal_zip_literal_pac_argv_tokens_from_lib_thread_fragment(fractal_zip::library_xz_decompress_thread_shell_fragment_for_exec()),
			$de
		);
		$ct = fractal_zip_literal_pac_argv_tokens_from_lib_thread_fragment(fractal_zip::library_xz_compress_thread_shell_fragment_for_exec());
		$co = $ct === [] ? $co : array_merge(['-9'], $ct, ['-c']);
	}
	return fractal_zip_literal_pac_try_recompress($compressed, 'lz', 'lzma', $de, $co);
}

function fractal_zip_literal_pac_try_lzip_smaller(string $compressed): ?array {
	if (fractal_zip_literal_pac_plzip_wants_plzip()) {
		$nt = fractal_zip_literal_pac_plzip_threads_argv();
		$de = $nt === [] ? ['-d', '-c'] : array_merge($nt, ['-d', '-c']);
		$co = $nt === [] ? ['-9', '-c'] : array_merge($nt, ['-9', '-c']);
		return fractal_zip_literal_pac_try_recompress($compressed, 'lz', 'plzip', $de, $co);
	}
	if (!fractal_zip_literal_pac_cmd_ok('lzip')) {
		return null;
	}
	return fractal_zip_literal_pac_try_recompress($compressed, 'lz', 'lzip', ['-dc'], ['-9', '-c']);
}

function fractal_zip_literal_pac_try_lzma_or_lzip_smaller(string $compressed): ?array {
	if (str_starts_with($compressed, 'LZIP')) {
		return fractal_zip_literal_pac_try_lzip_smaller($compressed);
	}
	return fractal_zip_literal_pac_try_lzma_smaller($compressed);
}

function fractal_zip_literal_pac_try_zstd_smaller(string $compressed): ?array {
	$level = getenv('FRACTAL_ZIP_LITERALPAC_ZSTD_LEVEL');
	$lv = ($level !== false && trim((string) $level) !== '' && is_numeric($level)) ? max(1, min(22, (int) $level)) : 19;
	$de = ['-dc'];
	$co = ['-' . (string) $lv, '-c', '--stdout'];
	if (class_exists('fractal_zip', false)) {
		$zt = fractal_zip_literal_pac_argv_tokens_from_lib_thread_fragment(fractal_zip::library_zstd_thread_shell_fragment_for_exec());
		$de = $zt === [] ? $de : array_merge($zt, $de);
		$co = $zt === [] ? $co : array_merge(['-' . (string) $lv], $zt, ['-c', '--stdout']);
	}
	return fractal_zip_literal_pac_try_recompress($compressed, 'zst', 'zstd', $de, $co);
}

function fractal_zip_literal_pac_try_lz4_smaller(string $compressed): ?array {
	$de = ['-dc'];
	$co = ['-9', '-c'];
	if (class_exists('fractal_zip', false)) {
		$lt = fractal_zip_literal_pac_argv_tokens_from_lib_thread_fragment(fractal_zip::library_lz4_thread_shell_fragment_for_exec());
		$de = $lt === [] ? $de : array_merge($lt, $de);
		$co = $lt === [] ? $co : array_merge(['-9'], $lt, ['-c']);
	}
	return fractal_zip_literal_pac_try_recompress($compressed, 'lz4', 'lz4', $de, $co);
}

function fractal_zip_literal_pac_try_brotli_smaller(string $compressed): ?array {
	if (!fractal_zip_literal_pac_cmd_ok('brotli')) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	$brIn = $td . DIRECTORY_SEPARATOR . 'fzbr_' . $id . '.br';
	$payPath = $td . DIRECTORY_SEPARATOR . 'fzbr_' . $id . '.bin';
	$brOut = $td . DIRECTORY_SEPARATOR . 'fzbr_' . $id . '_out.br';
	if (file_put_contents($brIn, $compressed) === false) {
		return null;
	}
	$ret = fractal_zip_literal_pac_brotli_cli(['brotli', '-d', '-i', $brIn, '-o', $payPath]);
	@unlink($brIn);
	if ($ret !== 0 || !is_file($payPath)) {
		@unlink($payPath);
		return null;
	}
	$payload = file_get_contents($payPath);
	if (!is_string($payload) || $payload === '' || !fractal_zip_literal_pac_payload_within_limit(strlen($payload))) {
		@unlink($payPath);
		return null;
	}
	$compressArgv = ['brotli'];
	if (class_exists('fractal_zip', false)) {
		$compressArgv = array_merge($compressArgv, fractal_zip::brotli_compress_extra_argv_proc_tokens());
	}
	$compressArgv = array_merge($compressArgv, ['-q', '11', '-i', $payPath, '-o', $brOut]);
	$ret = fractal_zip_literal_pac_brotli_cli($compressArgv);
	@unlink($payPath);
	if ($ret !== 0 || !is_file($brOut)) {
		@unlink($brOut);
		return null;
	}
	$newC = file_get_contents($brOut);
	@unlink($brOut);
	if (!is_string($newC) || $newC === '') {
		return null;
	}
	$saved = strlen($compressed) - strlen($newC);
	if ($saved <= 0) {
		return null;
	}
	$vBr = $td . DIRECTORY_SEPARATOR . 'fzbr_' . $id . '_v.br';
	$vPay = $td . DIRECTORY_SEPARATOR . 'fzbr_' . $id . '_v.bin';
	file_put_contents($vBr, $newC);
	$ret2 = fractal_zip_literal_pac_brotli_cli(['brotli', '-d', '-i', $vBr, '-o', $vPay]);
	@unlink($vBr);
	if ($ret2 !== 0) {
		return null;
	}
	$v = is_file($vPay) ? file_get_contents($vPay) : false;
	@unlink($vPay);
	if (!is_string($v) || $v !== $payload) {
		return null;
	}
	return [$newC, $saved];
}

function fractal_zip_literal_pac_list_files_recursive(string $dir): array {
	if (!is_dir($dir)) {
		return [];
	}
	$out = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $info) {
		if ($info->isFile()) {
			$out[] = $info->getPathname();
		}
	}
	return $out;
}

function fractal_zip_literal_pac_rmdir_recursive(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $info) {
		$p = $info->getPathname();
		if ($info->isDir()) {
			@rmdir($p);
		} else {
			@unlink($p);
		}
	}
	@rmdir($dir);
}

function fractal_zip_literal_pac_seven_binary(): ?string {
	foreach (['7zz', '7za', '7z'] as $b) {
		if (fractal_zip_literal_pac_cmd_ok($b)) {
			return $b;
		}
	}
	return null;
}

function fractal_zip_literal_pac_pdf_page_count_path(string $path): ?int {
	if (!fractal_zip_literal_pac_cmd_ok('pdfinfo')) {
		return null;
	}
	$lines = [];
	@exec('pdfinfo ' . escapeshellarg($path) . ' 2>/dev/null', $lines);
	foreach ($lines as $l) {
		$t = trim((string) $l);
		if (strlen($t) >= 6 && strncasecmp($t, 'Pages:', 6) === 0) {
			$rest = trim(substr($t, 6));
			if ($rest !== '' && ctype_digit($rest)) {
				return (int) $rest;
			}
		}
	}
	return null;
}

function fractal_zip_literal_pac_try_zip_single_smaller(string $compressed): ?array {
	if (!class_exists(ZipArchive::class)) {
		return null;
	}
	[$inPath] = fractal_zip_literal_pac_temp_pair('zip');
	if (file_put_contents($inPath, $compressed) === false) {
		return null;
	}
	$z = new ZipArchive();
	if ($z->open($inPath) !== true) {
		@unlink($inPath);
		return null;
	}
	if ($z->count() !== 1) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$stat = $z->statIndex(0);
	if ($stat === false) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$name = (string) $stat['name'];
	if ($name === '') {
		$z->close();
		@unlink($inPath);
		return null;
	}
	if (str_ends_with($name, '/')) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$enc = (int) ($stat['encryption_method'] ?? 0);
	if ($enc !== 0) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$payload = $z->getFromIndex(0);
	$z->close();
	@unlink($inPath);
	if (!is_string($payload) || $payload === '' || !fractal_zip_literal_pac_payload_within_limit(strlen($payload))) {
		return null;
	}
	[$outPath] = fractal_zip_literal_pac_temp_pair('zip');
	$z2 = new ZipArchive();
	if ($z2->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
		return null;
	}
	if (!$z2->addFromString($name, $payload)) {
		$z2->close();
		@unlink($outPath);
		return null;
	}
	if (method_exists($z2, 'setCompressionIndex')) {
		$z2->setCompressionIndex(0, ZipArchive::CM_DEFLATE, 9);
	}
	if (!$z2->close()) {
		@unlink($outPath);
		return null;
	}
	$newC = file_get_contents($outPath);
	@unlink($outPath);
	if (!is_string($newC) || $newC === '') {
		return null;
	}
	$saved = strlen($compressed) - strlen($newC);
	if ($saved <= 0) {
		return null;
	}
	[$vPath] = fractal_zip_literal_pac_temp_pair('zip');
	if (file_put_contents($vPath, $newC) === false) {
		return null;
	}
	$zv = new ZipArchive();
	if ($zv->open($vPath) !== true || $zv->count() !== 1) {
		@unlink($vPath);
		return null;
	}
	$vPay = $zv->getFromIndex(0);
	$zv->close();
	@unlink($vPath);
	if ($vPay !== $payload) {
		return null;
	}
	return [$newC, $saved];
}

function fractal_zip_literal_pac_try_seven_single_smaller(string $compressed): ?array {
	$seven = fractal_zip_literal_pac_seven_binary();
	if ($seven === null) {
		return null;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7_' . bin2hex(random_bytes(8));
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	$arcIn = $td . DIRECTORY_SEPARATOR . 'in.7z';
	$exDir = $td . DIRECTORY_SEPARATOR . 'ex';
	$newArc = $td . DIRECTORY_SEPARATOR . 'out.7z';
	$ex2 = $td . DIRECTORY_SEPARATOR . 'ex2';
	$vArc = $td . DIRECTORY_SEPARATOR . 'verify.7z';
	try {
		$mmt7 = class_exists('fractal_zip', false) ? fractal_zip::seven_zip_mmt_shell_fragment_for_exec() : '';
		if (file_put_contents($arcIn, $compressed) === false) {
			return null;
		}
		if (!@mkdir($exDir, 0700, true)) {
			return null;
		}
		$null = [];
		$ret = 1;
		exec(escapeshellarg($seven) . $mmt7 . ' x -o' . escapeshellarg($exDir) . ' ' . escapeshellarg($arcIn) . ' -y 2>/dev/null', $null, $ret);
		@unlink($arcIn);
		if ($ret !== 0) {
			return null;
		}
		$files = fractal_zip_literal_pac_list_files_recursive($exDir);
		if (count($files) !== 1) {
			return null;
		}
		$innerPath = $files[0];
		$payload = file_get_contents($innerPath);
		if (!is_string($payload) || $payload === '' || !fractal_zip_literal_pac_payload_within_limit(strlen($payload))) {
			return null;
		}
		$relInner = substr($innerPath, strlen($exDir) + 1);
		$ret2 = 1;
		exec('cd ' . escapeshellarg($exDir) . ' && ' . escapeshellarg($seven) . ' a -t7z -mx=9' . $mmt7 . ' -y ' . escapeshellarg($newArc) . ' ' . escapeshellarg($relInner) . ' 2>/dev/null', $null, $ret2);
		if ($ret2 !== 0 || !is_file($newArc)) {
			return null;
		}
		$newC = file_get_contents($newArc);
		@unlink($newArc);
		if (!is_string($newC) || $newC === '') {
			return null;
		}
		if (!@mkdir($ex2, 0700, true)) {
			return null;
		}
		if (file_put_contents($vArc, $newC) === false) {
			return null;
		}
		$ret3 = 1;
		exec(escapeshellarg($seven) . $mmt7 . ' x -o' . escapeshellarg($ex2) . ' ' . escapeshellarg($vArc) . ' -y 2>/dev/null', $null, $ret3);
		@unlink($vArc);
		if ($ret3 !== 0) {
			return null;
		}
		$vf = fractal_zip_literal_pac_list_files_recursive($ex2);
		if (count($vf) !== 1) {
			return null;
		}
		$vPayload = file_get_contents($vf[0]);
		if ($vPayload !== $payload) {
			return null;
		}
		$saved = strlen($compressed) - strlen($newC);
		if ($saved <= 0) {
			return null;
		}
		return [$newC, $saved];
	} finally {
		fractal_zip_literal_pac_rmdir_recursive($td);
	}
}

/**
 * Pure-PHP: recompress /FlateDecode (zlib-9 or raw-9) if smaller; first PDF step in registry (optional qpdf after, when enabled). Same magic as .pdf in registry.
 *
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_pdf_native_flate_smaller(string $compressed): ?array {
	$n = strlen($compressed);
	if ($n < 32 || !str_starts_with($compressed, '%PDF-')) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit($n)) {
		return null;
	}
	return fractal_zip_literal_pac_try_pdf_cached_handler('native_flate', $compressed);
}

/**
 * /DCTDecode stream bodies: lossless jpegtran (multi-candidate, multi-pass) when smaller. Needs jpegtran on PATH.
 *
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_pdf_dct_jpeg_smaller(string $compressed): ?array {
	$n = strlen($compressed);
	if ($n < 32 || !str_starts_with($compressed, '%PDF-')) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit($n)) {
		return null;
	}
	return fractal_zip_literal_pac_try_pdf_cached_handler('dct_jpeg', $compressed);
}

/**
 * /JBIG2Decode embedded streams: jbig2dec -e → PBM → jbig2/jbig2enc when on PATH; keep only if smaller and
 * PBM-identical decode (lossless vs decoded pixels). Missing tools ⇒ no-op.
 *
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_pdf_jbig2_smaller(string $compressed): ?array {
	$n = strlen($compressed);
	if ($n < 32 || !str_starts_with($compressed, '%PDF-')) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit($n)) {
		return null;
	}
	return fractal_zip_literal_pac_try_pdf_cached_handler('jbig2', $compressed);
}

/**
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_pdf_jpx_smaller(string $compressed): ?array {
	$n = strlen($compressed);
	if ($n < 32 || !str_starts_with($compressed, '%PDF-')) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit($n)) {
		return null;
	}
	return fractal_zip_literal_pac_try_pdf_cached_handler('jpx', $compressed);
}

/**
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_pdf_ccitt_smaller(string $compressed): ?array {
	$n = strlen($compressed);
	if ($n < 32 || !str_starts_with($compressed, '%PDF-')) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit($n)) {
		return null;
	}
	return fractal_zip_literal_pac_try_pdf_cached_handler('ccitt', $compressed);
}

/**
 * Two-slot per-handler memo for expensive PDF stream transforms.
 *
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_pdf_cached_handler(string $handler, string $compressed): ?array {
	static $k0 = [];
	static $r0 = [];
	static $k1 = [];
	static $r1 = [];
	$maxIn = 64 * 1024 * 1024;
	$n = strlen($compressed);
	$fp = null;
	if ($n <= $maxIn) {
		$fp = fractal_zip_literal_pac_static_lru_fp($compressed);
		if (isset($k0[$handler]) && $k0[$handler][0] === $fp[0] && $k0[$handler][1] === $fp[1]) {
			return $r0[$handler];
		}
		if (isset($k1[$handler]) && $k1[$handler][0] === $fp[0] && $k1[$handler][1] === $fp[1]) {
			return $r1[$handler];
		}
	}
	$res = match ($handler) {
		'native_flate' => fractal_zip_pdf_native_pac_recompress_flate_smaller($compressed),
		'dct_jpeg' => fractal_zip_pdf_jpeg_pac_recompress_smaller($compressed),
		'jbig2' => fractal_zip_pdf_jbig2_pac_recompress_smaller($compressed),
		'jpx' => fractal_zip_pdf_jpx_pac_recompress_smaller($compressed),
		'ccitt' => fractal_zip_pdf_ccitt_pac_recompress_smaller($compressed),
		default => null,
	};
	if ($fp !== null) {
		$k1[$handler] = $k0[$handler] ?? null;
		$r1[$handler] = $r0[$handler] ?? null;
		$k0[$handler] = $fp;
		$r0[$handler] = $res;
	}
	return $res;
}

/**
 * Structural qpdf rewrites (e.g. object streams) are opt-in: default off so the literal PDF pre-pass
 * only touches stream bytes (flate/jpeg), avoiding layout normalization that can interact poorly with
 * later bundle- or FZ-level transforms. Set FRACTAL_ZIP_LITERALPAC_PDF_QPDF=1 to enable.
 */
function fractal_zip_literal_pac_try_pdf_qpdf_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PDF_QPDF');
	if ($e === false || trim((string) $e) === '') {
		return $c = false;
	}
	$v = strtolower(trim((string) $e));
	return $c = ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

function fractal_zip_literal_pac_try_pdf_qpdf_smaller(string $compressed): ?array {
	if (!fractal_zip_literal_pac_try_pdf_qpdf_enabled()) {
		return null;
	}
	if (!fractal_zip_literal_pac_cmd_ok('qpdf')) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	$in = $td . DIRECTORY_SEPARATOR . 'fzpdf_' . $id . '.pdf';
	$out = $td . DIRECTORY_SEPARATOR . 'fzpdf_' . $id . '_o.pdf';
	if (file_put_contents($in, $compressed) === false) {
		return null;
	}
	$null = [];
	$retIn = 1;
	exec('qpdf --check ' . escapeshellarg($in) . ' 2>/dev/null', $null, $retIn);
	if ($retIn !== 0) {
		@unlink($in);
		return null;
	}
	$ret = 1;
	exec('qpdf --object-streams=generate ' . escapeshellarg($in) . ' ' . escapeshellarg($out) . ' 2>/dev/null', $null, $ret);
	@unlink($in);
	if ($ret !== 0 || !is_file($out)) {
		@unlink($out);
		return null;
	}
	$newC = file_get_contents($out);
	@unlink($out);
	if (!is_string($newC) || $newC === '') {
		return null;
	}
	$saved = strlen($compressed) - strlen($newC);
	if ($saved <= 0) {
		return null;
	}
	$vPath = $td . DIRECTORY_SEPARATOR . 'fzpdf_' . $id . '_v.pdf';
	if (file_put_contents($vPath, $newC) === false) {
		return null;
	}
	$ret2 = 1;
	exec('qpdf --check ' . escapeshellarg($vPath) . ' 2>/dev/null', $null, $ret2);
	if ($ret2 !== 0) {
		@unlink($vPath);
		return null;
	}
	if (fractal_zip_literal_pac_cmd_ok('pdfinfo')) {
		$origTmp = $td . DIRECTORY_SEPARATOR . 'fzpdf_' . $id . '_orig.pdf';
		if (file_put_contents($origTmp, $compressed) === false) {
			@unlink($vPath);
			return null;
		}
		$p0 = fractal_zip_literal_pac_pdf_page_count_path($origTmp);
		$p1 = fractal_zip_literal_pac_pdf_page_count_path($vPath);
		@unlink($origTmp);
		@unlink($vPath);
		if ($p0 === null || $p1 === null || $p0 !== $p1) {
			return null;
		}
	} else {
		@unlink($vPath);
	}
	return [$newC, $saved];
}

function fractal_zip_literal_pac_try_woff2_smaller(string $compressed): ?array {
	// Reject before woff2_* probes / shell (registry may still call us for adjacent magic paths).
	if (strlen($compressed) < 12 || !str_starts_with($compressed, 'wOF2')) {
		return null;
	}
	if (!fractal_zip_literal_pac_cmd_ok('woff2_decompress') || !fractal_zip_literal_pac_cmd_ok('woff2_compress')) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	$base = $td . DIRECTORY_SEPARATOR . 'fzw_' . $id;
	$w2in = $base . '.woff2';
	$ttf = $base . '.ttf';
	if (file_put_contents($w2in, $compressed) === false) {
		return null;
	}
	$ret = fractal_zip_literal_pac_tool_path_exit_code('woff2_decompress', $w2in, null);
	@unlink($w2in);
	if ($ret !== 0 || !is_file($ttf)) {
		@unlink($ttf);
		return null;
	}
	$payload = file_get_contents($ttf);
	if (!is_string($payload) || $payload === '' || !fractal_zip_literal_pac_payload_within_limit(strlen($payload))) {
		@unlink($ttf);
		return null;
	}
	$ret = fractal_zip_literal_pac_tool_path_exit_code('woff2_compress', $ttf, null);
	@unlink($ttf);
	$w2out = $base . '.woff2';
	if ($ret !== 0 || !is_file($w2out)) {
		@unlink($w2out);
		return null;
	}
	$newC = file_get_contents($w2out);
	@unlink($w2out);
	if (!is_string($newC) || $newC === '') {
		return null;
	}
	$saved = strlen($compressed) - strlen($newC);
	if ($saved <= 0) {
		return null;
	}
	$vW = $base . '_v.woff2';
	$vT = $base . '_v.ttf';
	file_put_contents($vW, $newC);
	$ret2 = fractal_zip_literal_pac_tool_path_exit_code('woff2_decompress', $vW, null);
	@unlink($vW);
	$v = is_file($vT) ? file_get_contents($vT) : false;
	@unlink($vT);
	if (!is_string($v) || $v !== $payload) {
		return null;
	}
	return [$newC, $saved];
}

// ---- Lossless gzip “peel” for literal bundles (FZB mode 6): compress inner payload; restore exact .gz bytes on extract ----

function fractal_zip_literal_expand_gzip_inner_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * If $b is a single gzip member, return decoded payload + metadata to restore the exact blob.
 * @return array{inner: string, meta: array{len: int, sha1: string}}|null
 */
function fractal_zip_literal_expand_outer_gzip_once(string $b): ?array {
	if (strlen($b) < 10) {
		return null;
	}
	if (ord($b[0]) !== 0x1f || ord($b[1]) !== 0x8b) {
		return null;
	}
	$inner = @gzdecode($b);
	if ($inner === false) {
		return null;
	}
	if ($inner === '' && strlen($b) < 24) {
		return null;
	}
	return [
		'inner' => $inner,
		'meta' => ['len' => strlen($b), 'sha1' => sha1($b, true)],
	];
}

/**
 * After peeling one gzip layer, adjust the logical path for raster/stream PAC (must run before a generic /\.gz$/ strip:
 * e.g. foo.vgz → foo.vgm; foo.svgz → foo.svg; foo.gz → foo).
 */
function fractal_zip_literal_path_for_raster_pac_after_gzip_strip(string $relPath): string {
	$len = strlen($relPath);
	if ($len >= 4 && strcasecmp(substr($relPath, -4), '.vgz') === 0) {
		return substr($relPath, 0, $len - 4) . '.vgm';
	}
	if ($len >= 3 && strcasecmp(substr($relPath, -3), '.gz') === 0) {
		return substr($relPath, 0, $len - 3);
	}
	return $relPath;
}

function fractal_zip_literal_gzip_peel_max_layers(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_GZIP_PEEL_MAX_LAYERS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 64;
	}
	$v = (int) $e;
	return $cached = ($v <= 0 ? 1 : min(1024, $v));
}

function fractal_zip_literal_peel_stabilize_max_rounds(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_PEEL_STABILIZE_ROUNDS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 48;
	}
	$v = (int) $e;
	return $cached = ($v <= 0 ? 1 : min(512, $v));
}

/**
 * Append consecutive gzip layers from the front of $work onto $gzipStack (outermost meta first). Updates path when stripping .gz.
 *
 * @param list<array{len: int, sha1: string}> $gzipStack
 * @return array{0: string, 1: string, 2: list<array{len: int, sha1: string}>}
 */
function fractal_zip_literal_append_consecutive_gzip_peels(string $pathRaster, string $work, array $gzipStack): array {
	$path = $pathRaster;
	$w = $work;
	$stack = $gzipStack;
	$maxGzip = fractal_zip_literal_gzip_peel_max_layers();
	if (!fractal_zip_literal_expand_gzip_inner_enabled()) {
		return [$w, $path, $stack];
	}
	while (count($stack) < $maxGzip) {
		$ex = fractal_zip_literal_expand_outer_gzip_once($w);
		if ($ex === null) {
			break;
		}
		$stack[] = $ex['meta'];
		$w = $ex['inner'];
		$path = fractal_zip_literal_path_for_raster_pac_after_gzip_strip($path);
	}
	return [$w, $path, $stack];
}

function fractal_zip_literal_encode_varint_u32(int $n): string {
	$n = max(0, $n);
	// Unrolled common lengths (FZB packing helpers); max 5 bytes for u32 — matches fractal_zip::encode_varint_u32.
	if ($n < 0x80) {
		return chr($n);
	}
	if ($n < 0x4000) {
		return chr(($n & 0x7F) | 0x80) . chr($n >> 7);
	}
	if ($n < 0x200000) {
		return chr(($n & 0x7F) | 0x80)
			. chr((($n >> 7) & 0x7F) | 0x80)
			. chr($n >> 14);
	}
	if ($n < 0x10000000) {
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

function fractal_zip_literal_semantic_zip_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_semantic_7z_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_7Z');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_semantic_mpq_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_semantic_peel_max_total(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_PEEL_MAX_LAYERS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $cached = 48;
	}
	$v = (int) $e;
	return $cached = ($v <= 0 ? 1 : min(256, $v));
}

function fractal_zip_literal_path_for_raster_pac_after_zip_strip(string $relPath): string {
	$len = strlen($relPath);
	if ($len >= 4 && strcasecmp(substr($relPath, -4), '.zip') === 0) {
		return substr($relPath, 0, $len - 4);
	}
	return $relPath;
}

function fractal_zip_literal_path_for_raster_pac_after_7z_strip(string $relPath): string {
	$len = strlen($relPath);
	if ($len >= 3 && strcasecmp(substr($relPath, -3), '.7z') === 0) {
		return substr($relPath, 0, $len - 3);
	}
	return $relPath;
}

function fractal_zip_literal_path_looks_mpq_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	$mpqFamily = array('sc2replay', 'w3replay', 'sc2map', 'w3x', 'w3m', 'mpq');
	foreach ($mpqFamily as $ext) {
		$suf = '.' . $ext;
		if (strlen($low) >= strlen($suf) && str_ends_with($low, $suf)) {
			return true;
		}
	}
	return false;
}

function fractal_zip_literal_path_for_raster_pac_after_mpq_strip(string $relPath): string {
	$relPath = str_replace('\\', '/', $relPath);
	$len = strlen($relPath);
	$sfx = array(
		array(10, '.sc2replay'),
		array(9, '.w3replay'),
		array(7, '.sc2map'),
		array(4, '.w3x'),
		array(4, '.w3m'),
		array(4, '.mpq'),
	);
	foreach ($sfx as $pair) {
		$l = $pair[0];
		$s = $pair[1];
		if ($len >= $l && strcasecmp(substr($relPath, -$l), $s) === 0) {
			return substr($relPath, 0, $len - $l);
		}
	}
	return $relPath;
}

function fractal_zip_literal_pac_mpq_semantic_script(): ?string {
	static $inited = false;
	static $cached;
	if ($inited) {
		return $cached;
	}
	$inited = true;
	$rp = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'fractal_zip_mpq_semantic.py');
	$cached = ($rp !== false && is_file($rp)) ? $rp : null;
	return $cached;
}

/**
 * @return array{0: string, 1: string}|null [innerPayload, tag]
 */
function fractal_zip_literal_pac_peel_mpq_semantic(string $compressed): ?array {
	if (strlen($compressed) < 16) {
		return null;
	}
	if (substr($compressed, 0, 3) !== 'MPQ') {
		return null;
	}
	if (!fractal_zip_literal_semantic_mpq_enabled()) {
		return null;
	}
	if (!fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	// Two-slot exact-match cache: literal tournaments / deep unwrap can revisit the same MPQ bytes many times;
	// Python peel is pure for a given input. Bounded to avoid pinning huge archives in RAM.
	static $peelK0 = null;
	static $peelR0 = null;
	static $peelK1 = null;
	static $peelR1 = null;
	$peelMaxIn = 64 * 1024 * 1024;
	$peelMaxOut = 128 * 1024 * 1024;
	$peelFp = fractal_zip_literal_pac_static_lru_fp($compressed);
	if ($peelK0 !== null && $peelK0[0] === $peelFp[0] && $peelK0[1] === $peelFp[1] && $peelR0 !== null) {
		return array($peelR0[0], $peelR0[1]);
	}
	if ($peelK1 !== null && $peelK1[0] === $peelFp[0] && $peelK1[1] === $peelFp[1] && $peelR1 !== null) {
		return array($peelR1[0], $peelR1[1]);
	}
	$script = fractal_zip_literal_pac_mpq_semantic_script();
	if ($script === null) {
		return null;
	}
	if (!fractal_zip_literal_pac_cmd_ok('python3') || !fractal_zip_literal_pac_cmd_ok('smpq')) {
		return null;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzmqsem_' . bin2hex(random_bytes(8));
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	$inPath = $td . DIRECTORY_SEPARATOR . 'in.bin';
	$outPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
	try {
		if (file_put_contents($inPath, $compressed) === false) {
			return null;
		}
		$cmd = array('python3', $script, 'peel', $inPath, $outPath);
		$desc = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$procOpts = array('bypass_shell' => true);
		$proc = @proc_open($cmd, $desc, $pipes, null, null, $procOpts);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[2], 1048576);
			}
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
		}
		$code = proc_close($proc);
		if ($code !== 0 || !is_file($outPath)) {
			return null;
		}
		$inner = file_get_contents($outPath);
		if ($inner === false || $inner === '') {
			return null;
		}
		if (!fractal_zip_literal_pac_payload_within_limit(strlen($inner))) {
			return null;
		}
		$ret = array($inner, 'v2');
		if (strlen($compressed) <= $peelMaxIn && strlen($inner) <= $peelMaxOut) {
			$peelK1 = $peelK0;
			$peelR1 = $peelR0;
			$peelK0 = $peelFp;
			$peelR0 = $ret;
		}
		return $ret;
	} finally {
		@unlink($inPath);
		@unlink($outPath);
		@rmdir($td);
	}
}

function fractal_zip_literal_pac_rebuild_mpq_semantic(string $payload, string $tag): ?string {
	if ($tag !== 'v1' && $tag !== 'v2') {
		return null;
	}
	static $rebK0 = null;
	static $rebO0 = null;
	static $rebK1 = null;
	static $rebO1 = null;
	$rebMaxIn = 64 * 1024 * 1024;
	$rebMaxOut = 128 * 1024 * 1024;
	$rebFp = fractal_zip_literal_pac_static_lru_fp($payload);
	if ($rebK0 !== null && $rebK0[0] === $rebFp[0] && $rebK0[1] === $rebFp[1] && $rebK0[2] === $tag) {
		return $rebO0;
	}
	if ($rebK1 !== null && $rebK1[0] === $rebFp[0] && $rebK1[1] === $rebFp[1] && $rebK1[2] === $tag) {
		return $rebO1;
	}
	$script = fractal_zip_literal_pac_mpq_semantic_script();
	if ($script === null) {
		return null;
	}
	if (!fractal_zip_literal_pac_cmd_ok('python3') || !fractal_zip_literal_pac_cmd_ok('smpq')) {
		return null;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzmqreb_' . bin2hex(random_bytes(8));
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	$inPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
	$outPath = $td . DIRECTORY_SEPARATOR . 'out.mpq';
	try {
		if (file_put_contents($inPath, $payload) === false) {
			return null;
		}
		$cmd = array('python3', $script, 'pack', $inPath, $outPath);
		$desc = array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
		$procOpts = array('bypass_shell' => true);
		$proc = @proc_open($cmd, $desc, $pipes, null, null, $procOpts);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[1], 1048576);
			}
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
			if (function_exists('stream_set_chunk_size')) {
				@stream_set_chunk_size($pipes[2], 1048576);
			}
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
		}
		$code = proc_close($proc);
		if ($code !== 0 || !is_file($outPath)) {
			return null;
		}
		$out = file_get_contents($outPath);
		if (!is_string($out) || $out === '') {
			return null;
		}
		if (strlen($payload) <= $rebMaxIn && strlen($out) <= $rebMaxOut) {
			$rebK1 = $rebK0;
			$rebO1 = $rebO0;
			$rebK0 = [$rebFp[0], $rebFp[1], $tag];
			$rebO0 = $out;
		}
		return $out;
	} finally {
		@unlink($inPath);
		@unlink($outPath);
		@rmdir($td);
	}
}

function fractal_zip_literal_pac_archive_inner_path_safe(string $p): bool {
	$p = str_replace('\\', '/', $p);
	if ($p === '' || str_contains($p, "\0") || str_starts_with($p, '/') || str_contains($p, '..')) {
		return false;
	}
	return true;
}

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
 * @return array{0: int, 1: int} maxMembers, maxTotalUncompressedBytes (0 = no limit)
 */
function fractal_zip_literal_pac_zip_multimember_limits(): array {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$me = getenv('FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_MEMBERS');
	$maxM = ($me === false || trim((string) $me) === '') ? 2048 : max(2, min(65535, (int) $me));
	$te = getenv('FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_RAW_BYTES');
	$maxT = ($te === false || trim((string) $te) === '') ? (128 * 1024 * 1024) : max(0, (int) $te);
	return $cached = [$maxM, $maxT];
}

/**
 * Safe member name inside a multi-member ZIP literal (mode 18).
 * Reject path segments `.` / `..` only (allow "foo..bar" and "a...b"; zip listing uses `..` as substring often).
 */
function fractal_zip_literal_pac_zip_entry_name_safe_for_mode18(string $name): bool {
	if ($name === '' || str_contains($name, "\0")) {
		return false;
	}
	$n = str_replace('\\', '/', $name);
	if (str_starts_with($n, '/')) {
		return false;
	}
	foreach (explode('/', $n) as $seg) {
		if ($seg === '' || $seg === '.' || $seg === '..') {
			return false;
		}
	}
	return true;
}

/**
 * List file members (skip directories, encrypted, unsound names) for mode 18. Preserves ZipArchive index order.
 *
 * @return list<array{name: string, data: string}>|null null if not a readable multi-file zip or limits exceeded
 */
function fractal_zip_literal_pac_list_zip_members_for_mode18(string $compressed): ?array {
	if (!fractal_zip_literal_semantic_zip_multimember_enabled()) {
		return null;
	}
	if (!class_exists(ZipArchive::class)) {
		return null;
	}
	if (strlen($compressed) < 4 || !str_starts_with($compressed, "PK\x03\x04")) {
		return null;
	}
	[$maxMembers, $maxTotal] = fractal_zip_literal_pac_zip_multimember_limits();
	[$inPath] = fractal_zip_literal_pac_temp_pair('zip');
	if (file_put_contents($inPath, $compressed) === false) {
		return null;
	}
	$z = new ZipArchive();
	if ($z->open($inPath) !== true) {
		@unlink($inPath);
		return null;
	}
	$cnt = $z->count();
	if ($cnt < 2) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	if ($cnt > $maxMembers) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$out = [];
	$total = 0;
	for ($i = 0; $i < $cnt; $i++) {
		$stat = $z->statIndex($i);
		if ($stat === false) {
			$z->close();
			@unlink($inPath);
			return null;
		}
		$name = (string) $stat['name'];
		if ($name === '' || str_ends_with($name, '/')) {
			continue;
		}
		if (!fractal_zip_literal_pac_zip_entry_name_safe_for_mode18($name)) {
			$z->close();
			@unlink($inPath);
			return null;
		}
		if ((int) ($stat['encryption_method'] ?? 0) !== 0) {
			$z->close();
			@unlink($inPath);
			return null;
		}
		$payload = $z->getFromIndex($i);
		if (!is_string($payload)) {
			$z->close();
			@unlink($inPath);
			return null;
		}
		if (!fractal_zip_literal_pac_payload_within_limit(strlen($payload))) {
			$z->close();
			@unlink($inPath);
			return null;
		}
		$total += strlen($payload);
		if ($maxTotal > 0 && $total > $maxTotal) {
			$z->close();
			@unlink($inPath);
			return null;
		}
		$out[] = ['name' => $name, 'data' => $payload];
	}
	$z->close();
	@unlink($inPath);
	if (count($out) < 2) {
		return null;
	}
	return $out;
}

/**
 * Rebuild a ZIP from decoded member payloads (semantic; deflate level 9, order preserved).
 *
 * @param list<array{name: string, data: string}> $members
 */
function fractal_zip_literal_pac_rebuild_zip_multi_semantic(array $members): ?string {
	if (!class_exists(ZipArchive::class) || count($members) < 2) {
		return null;
	}
	foreach ($members as $m) {
		if (!isset($m['name'], $m['data']) || !is_string($m['name']) || !is_string($m['data'])) {
			return null;
		}
		if (!fractal_zip_literal_pac_zip_entry_name_safe_for_mode18($m['name'])) {
			return null;
		}
	}
	[$outPath] = fractal_zip_literal_pac_temp_pair('zip');
	$z2 = new ZipArchive();
	if ($z2->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
		return null;
	}
	$idx = 0;
	foreach ($members as $m) {
		if (!$z2->addFromString($m['name'], $m['data'])) {
			$z2->close();
			@unlink($outPath);
			return null;
		}
		if (method_exists($z2, 'setCompressionIndex')) {
			$z2->setCompressionIndex($idx, ZipArchive::CM_DEFLATE, 9);
		}
		$idx++;
	}
	if (!$z2->close()) {
		@unlink($outPath);
		return null;
	}
	$newC = file_get_contents($outPath);
	@unlink($outPath);
	return is_string($newC) && $newC !== '' ? $newC : null;
}

/**
 * Single-member, non-encrypted ZIP → inner payload + entry name (semantic equivalence on rebuild).
 *
 * @return array{0: string, 1: string}|null [payload, memberName]
 */
function fractal_zip_literal_pac_peel_zip_single_semantic(string $compressed): ?array {
	if (!class_exists(ZipArchive::class)) {
		return null;
	}
	if (strlen($compressed) < 4 || !str_starts_with($compressed, "PK\x03\x04")) {
		return null;
	}
	static $zipK0 = null;
	static $zipR0 = null;
	static $zipK1 = null;
	static $zipR1 = null;
	$zipMaxIn = 64 * 1024 * 1024;
	$zipMaxPayload = 128 * 1024 * 1024;
	$zipFp = fractal_zip_literal_pac_static_lru_fp($compressed);
	if ($zipK0 !== null && $zipK0[0] === $zipFp[0] && $zipK0[1] === $zipFp[1] && $zipR0 !== null) {
		return [$zipR0[0], $zipR0[1]];
	}
	if ($zipK1 !== null && $zipK1[0] === $zipFp[0] && $zipK1[1] === $zipFp[1] && $zipR1 !== null) {
		return [$zipR1[0], $zipR1[1]];
	}
	[$inPath] = fractal_zip_literal_pac_temp_pair('zip');
	if (file_put_contents($inPath, $compressed) === false) {
		return null;
	}
	$z = new ZipArchive();
	if ($z->open($inPath) !== true) {
		@unlink($inPath);
		return null;
	}
	if ($z->count() !== 1) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$stat = $z->statIndex(0);
	if ($stat === false) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$name = (string) $stat['name'];
	if ($name === '' || str_ends_with($name, '/')) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	if ((int) ($stat['encryption_method'] ?? 0) !== 0) {
		$z->close();
		@unlink($inPath);
		return null;
	}
	$payload = $z->getFromIndex(0);
	$z->close();
	@unlink($inPath);
	if (!is_string($payload) || !fractal_zip_literal_pac_payload_within_limit(strlen($payload))) {
		return null;
	}
	if (strlen($name) > 65535) {
		return null;
	}
	$ret = [$payload, $name];
	if (strlen($compressed) <= $zipMaxIn && strlen($payload) <= $zipMaxPayload) {
		$zipK1 = $zipK0;
		$zipR1 = $zipR0;
		$zipK0 = $zipFp;
		$zipR0 = $ret;
	}
	return $ret;
}

function fractal_zip_literal_pac_rebuild_zip_single_semantic(string $payload, string $memberName): ?string {
	if ($memberName === '' || str_contains($memberName, "\0") || strlen($memberName) > 65535) {
		return null;
	}
	if (!class_exists(ZipArchive::class)) {
		return null;
	}
	static $rbzK0 = null;
	static $rbzO0 = null;
	static $rbzK1 = null;
	static $rbzO1 = null;
	$rbzMaxIn = 64 * 1024 * 1024;
	$rbzMaxOut = 128 * 1024 * 1024;
	$rbzFpP = fractal_zip_literal_pac_static_lru_fp($payload);
	$rbzFpN = fractal_zip_literal_pac_static_lru_fp($memberName);
	if ($rbzK0 !== null
		&& $rbzK0[0] === $rbzFpP[0] && $rbzK0[1] === $rbzFpP[1]
		&& $rbzK0[2] === $rbzFpN[0] && $rbzK0[3] === $rbzFpN[1]
		&& $rbzO0 !== null) {
		return $rbzO0;
	}
	if ($rbzK1 !== null
		&& $rbzK1[0] === $rbzFpP[0] && $rbzK1[1] === $rbzFpP[1]
		&& $rbzK1[2] === $rbzFpN[0] && $rbzK1[3] === $rbzFpN[1]
		&& $rbzO1 !== null) {
		return $rbzO1;
	}
	[$outPath] = fractal_zip_literal_pac_temp_pair('zip');
	$z2 = new ZipArchive();
	if ($z2->open($outPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
		return null;
	}
	if (!$z2->addFromString($memberName, $payload)) {
		$z2->close();
		@unlink($outPath);
		return null;
	}
	if (method_exists($z2, 'setCompressionIndex')) {
		$z2->setCompressionIndex(0, ZipArchive::CM_DEFLATE, 9);
	}
	if (!$z2->close()) {
		@unlink($outPath);
		return null;
	}
	$newC = file_get_contents($outPath);
	@unlink($outPath);
	if (!is_string($newC) || $newC === '') {
		return null;
	}
	if (strlen($payload) <= $rbzMaxIn && strlen($newC) <= $rbzMaxOut) {
		$rbzK1 = $rbzK0;
		$rbzO1 = $rbzO0;
		$rbzK0 = [$rbzFpP[0], $rbzFpP[1], $rbzFpN[0], $rbzFpN[1]];
		$rbzO0 = $newC;
	}
	return $newC;
}

/**
 * 7z archive with exactly one file member → payload + archive-relative path (semantic equivalence on rebuild).
 *
 * @return array{0: string, 1: string}|null [payload, relPath]
 */
function fractal_zip_literal_pac_peel_seven_single_semantic(string $compressed): ?array {
	$seven = fractal_zip_literal_pac_seven_binary();
	if ($seven === null) {
		return null;
	}
	if (strlen($compressed) < 6 || !str_starts_with($compressed, "7z\xBC\xAF\x27\x1C")) {
		return null;
	}
	static $s7K0 = null;
	static $s7R0 = null;
	static $s7K1 = null;
	static $s7R1 = null;
	$s7MaxIn = 64 * 1024 * 1024;
	$s7MaxPayload = 128 * 1024 * 1024;
	$s7Fp = fractal_zip_literal_pac_static_lru_fp($compressed);
	if ($s7K0 !== null && $s7K0[0] === $s7Fp[0] && $s7K0[1] === $s7Fp[1] && $s7R0 !== null) {
		return [$s7R0[0], $s7R0[1]];
	}
	if ($s7K1 !== null && $s7K1[0] === $s7Fp[0] && $s7K1[1] === $s7Fp[1] && $s7R1 !== null) {
		return [$s7R1[0], $s7R1[1]];
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7p_' . bin2hex(random_bytes(8));
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	$arcIn = $td . DIRECTORY_SEPARATOR . 'in.7z';
	$exDir = $td . DIRECTORY_SEPARATOR . 'ex';
	try {
		$mmt7 = class_exists('fractal_zip', false) ? fractal_zip::seven_zip_mmt_shell_fragment_for_exec() : '';
		if (file_put_contents($arcIn, $compressed) === false) {
			return null;
		}
		if (!@mkdir($exDir, 0700, true)) {
			return null;
		}
		$null = [];
		$ret = 1;
		exec(escapeshellarg($seven) . $mmt7 . ' x -o' . escapeshellarg($exDir) . ' ' . escapeshellarg($arcIn) . ' -y 2>/dev/null', $null, $ret);
		@unlink($arcIn);
		if ($ret !== 0) {
			return null;
		}
		$files = fractal_zip_literal_pac_list_files_recursive($exDir);
		if (count($files) !== 1) {
			return null;
		}
		$innerPath = $files[0];
		$payload = file_get_contents($innerPath);
		if (!is_string($payload) || !fractal_zip_literal_pac_payload_within_limit(strlen($payload))) {
			return null;
		}
		$relInner = substr($innerPath, strlen($exDir) + 1);
		$relInner = str_replace('\\', '/', $relInner);
		if (!fractal_zip_literal_pac_archive_inner_path_safe($relInner)) {
			return null;
		}
		$ret = [$payload, $relInner];
		if (strlen($compressed) <= $s7MaxIn && strlen($payload) <= $s7MaxPayload) {
			$s7K1 = $s7K0;
			$s7R1 = $s7R0;
			$s7K0 = $s7Fp;
			$s7R0 = $ret;
		}
		return $ret;
	} finally {
		fractal_zip_literal_pac_rmdir_recursive($td);
	}
}

function fractal_zip_literal_pac_rebuild_seven_single_semantic(string $payload, string $relInnerPath): ?string {
	if (!fractal_zip_literal_pac_archive_inner_path_safe($relInnerPath)) {
		return null;
	}
	$seven = fractal_zip_literal_pac_seven_binary();
	if ($seven === null) {
		return null;
	}
	static $rb7K0 = null;
	static $rb7O0 = null;
	static $rb7K1 = null;
	static $rb7O1 = null;
	$rb7MaxIn = 64 * 1024 * 1024;
	$rb7MaxOut = 128 * 1024 * 1024;
	$rb7FpP = fractal_zip_literal_pac_static_lru_fp($payload);
	$rb7FpR = fractal_zip_literal_pac_static_lru_fp($relInnerPath);
	if ($rb7K0 !== null
		&& $rb7K0[0] === $rb7FpP[0] && $rb7K0[1] === $rb7FpP[1]
		&& $rb7K0[2] === $rb7FpR[0] && $rb7K0[3] === $rb7FpR[1]
		&& $rb7O0 !== null) {
		return $rb7O0;
	}
	if ($rb7K1 !== null
		&& $rb7K1[0] === $rb7FpP[0] && $rb7K1[1] === $rb7FpP[1]
		&& $rb7K1[2] === $rb7FpR[0] && $rb7K1[3] === $rb7FpR[1]
		&& $rb7O1 !== null) {
		return $rb7O1;
	}
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7b_' . bin2hex(random_bytes(8));
	$workDir = $td . DIRECTORY_SEPARATOR . 'work';
	$newArc = $td . DIRECTORY_SEPARATOR . 'out.7z';
	try {
		if (!@mkdir($workDir, 0700, true)) {
			return null;
		}
		$full = $workDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relInnerPath);
		$dir = dirname($full);
		if (!@mkdir($dir, 0700, true) && !is_dir($dir)) {
			return null;
		}
		if (file_put_contents($full, $payload) === false) {
			return null;
		}
		$null = [];
		$ret2 = 1;
		$mmt7 = class_exists('fractal_zip', false) ? fractal_zip::seven_zip_mmt_shell_fragment_for_exec() : '';
		exec('cd ' . escapeshellarg($workDir) . ' && ' . escapeshellarg((string) $seven) . ' a -t7z -mx=9' . $mmt7 . ' -y ' . escapeshellarg($newArc) . ' ' . escapeshellarg($relInnerPath) . ' 2>/dev/null', $null, $ret2);
		if ($ret2 !== 0 || !is_file($newArc)) {
			return null;
		}
		$newC = file_get_contents($newArc);
		if (!is_string($newC) || $newC === '') {
			return null;
		}
		if (strlen($payload) <= $rb7MaxIn && strlen($newC) <= $rb7MaxOut) {
			$rb7K1 = $rb7K0;
			$rb7O1 = $rb7O0;
			$rb7K0 = [$rb7FpP[0], $rb7FpP[1], $rb7FpR[0], $rb7FpR[1]];
			$rb7O0 = $newC;
		}
		return $newC;
	} finally {
		fractal_zip_literal_pac_rmdir_recursive($td);
	}
}

/**
 * @param list<array{0: string, 1: string}> $semanticLayers … |('bstr', '')|('nstr', '')|('fws', 8-byte header)|… outermost peel first; git: type tag; see `fractal_zip_literal_*` for each
 * @param list<array{len: int, sha1: string}> $gzipStack outermost first
 * @return array{0: int, 1: string} [mode, stored]
 */
function fractal_zip_literal_bundle_wrap_all_layers(array $semanticLayers, array $gzipStack, int $innerMode, string $innerStore): array {
	$stored = $innerStore;
	$mode = $innerMode;
	// gzip closer to raw payload; ZIP/7z semantic shells outside (matches zip(gzip(raw)) on disk).
	for ($i = count($gzipStack) - 1; $i >= 0; $i--) {
		$meta = $gzipStack[$i];
		$stored = pack('Va20', $meta['len'], $meta['sha1']) . chr($mode) . $stored;
		$mode = 6;
	}
	for ($i = count($semanticLayers) - 1; $i >= 0; $i--) {
		$L = $semanticLayers[$i];
		$kind = $L[0];
		$pathPart = $L[1];
		if ($kind === 'zip') {
			$stored = fractal_zip_literal_encode_varint_u32(strlen($pathPart)) . $pathPart . chr($mode) . $stored;
			$mode = 7;
		} elseif ($kind === '7z') {
			$stored = fractal_zip_literal_encode_varint_u32(strlen($pathPart)) . $pathPart . chr($mode) . $stored;
			$mode = 8;
		} elseif ($kind === 'mpq') {
			$stored = fractal_zip_literal_encode_varint_u32(strlen($pathPart)) . $pathPart . chr($mode) . $stored;
			$mode = 11;
		} elseif ($kind === 'tar') {
			$stored = fractal_zip_literal_encode_varint_u32(strlen($pathPart)) . $pathPart . chr($mode) . $stored;
			$mode = 20;
		} elseif ($kind === 'ar') {
			$stored = fractal_zip_literal_encode_varint_u32(strlen($pathPart)) . $pathPart . chr($mode) . $stored;
			$mode = 21;
		} elseif ($kind === 'cpio') {
			$stored = fractal_zip_literal_encode_varint_u32(strlen($pathPart)) . $pathPart . chr($mode) . $stored;
			$mode = 22;
		} elseif ($kind === 'gitobj') {
			$g = (string) $pathPart;
			if($g === '' || $g === 'blob') {
				$stored = fractal_zip_literal_encode_varint_u32(0) . '' . chr($mode) . $stored;
			} else {
				$stored = fractal_zip_literal_encode_varint_u32(strlen($g)) . $g . chr($mode) . $stored;
			}
			$mode = 23;
		} elseif ($kind === 'bstr') {
			$stored = fractal_zip_literal_encode_varint_u32(0) . '' . chr($mode) . $stored;
			$mode = 24;
		} elseif ($kind === 'nstr') {
			$stored = fractal_zip_literal_encode_varint_u32(0) . '' . chr($mode) . $stored;
			$mode = 25;
		} elseif ($kind === 'fws') {
			$h8 = (string) $pathPart;
			$stored = fractal_zip_literal_encode_varint_u32(strlen($h8)) . $h8 . chr($mode) . $stored;
			$mode = 26;
		}
	}
	return [$mode, $stored];
}

/**
 * Wrap inner FZB transform with nested mode-6 gzip layers (innermost meta last in $gzipStack).
 *
 * @param list<array{len: int, sha1: string}> $gzipStack outermost first
 * @return array{0: int, 1: string} [mode, stored]
 */
function fractal_zip_literal_bundle_wrap_with_gzip_stack(array $gzipStack, int $innerMode, string $innerStore): array {
	return fractal_zip_literal_bundle_wrap_all_layers([], $gzipStack, $innerMode, $innerStore);
}

/**
 * Rebuild the original gzip member bytes (bit-exact) from the decompressed payload.
 */
function fractal_zip_literal_restore_gzip_exact(string $inner, int $origLen, string $sha1bin): string {
	for ($lev = 1; $lev <= 9; $lev++) {
		$c = @gzencode($inner, $lev);
		if ($c !== false && strlen($c) === $origLen && hash_equals(sha1($c, true), $sha1bin)) {
			return $c;
		}
	}
	if (fractal_zip_literal_pac_cmd_ok('gzip')) {
		[$inPath] = fractal_zip_literal_pac_temp_pair('bin');
		if (file_put_contents($inPath, $inner) !== false) {
			$c = fractal_zip_literal_pac_shell_compress_to_string('gzip', ['-2', '-n', '-c'], $inPath);
			@unlink($inPath);
			if (is_string($c) && strlen($c) === $origLen && hash_equals(sha1($c, true), $sha1bin)) {
				return $c;
			}
		} else {
			@unlink($inPath);
		}
	}
	if (fractal_zip_literal_pac_cmd_ok('pigz')) {
		[$inPath] = fractal_zip_literal_pac_temp_pair('bin');
		if (file_put_contents($inPath, $inner) !== false) {
			$c = fractal_zip_literal_pac_shell_compress_to_string('pigz', ['-2', '-n', '-c'], $inPath);
			@unlink($inPath);
			if (is_string($c) && strlen($c) === $origLen && hash_equals(sha1($c, true), $sha1bin)) {
				return $c;
			}
		} else {
			@unlink($inPath);
		}
	}
	$msg = 'FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER: cannot losslessly restore gzip wrapper (orig_len=' . $origLen . ').';
	if (class_exists('fractal_zip', false)) {
		fractal_zip::fatal_error($msg);
	}
	throw new RuntimeException($msg);
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_ar_gnu.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_cpio_newc.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_git_blob.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_bencode_str.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_netstring.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_swf_fws.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_deep_unwrap.php';
