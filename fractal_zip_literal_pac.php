<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac_registry.php';

/**
 * Literal-bundle preprocessing for common **single-blob** compressed types (after image_pac rasters).
 * Decompress → recompress at a high level → keep only if smaller and the **decompressed payload** matches (lossless).
 *
 * Aligns with external ../file_types/types.php “compressed” style categories where a CLI exists on PATH.
 * Registry: fractal_zip_literal_pac_registry.php (ids, extensions, magic, tools).
 * Not handled here: tar/cpio archives, multi-member archives, FLAC merge (FZCD), lossy audio/video re-encode.
 *
 * Tools (optional; missing tool = no-op for that extension):
 *   gzip / pigz / zopfli → .gz, .svgz (FRACTAL_ZIP_LITERALPAC_GZIP_ENGINE=auto|gzip|pigz|zopfli; ZOPFLI if FRACTAL_ZIP_LITERALPAC_ZOPFLI=1)
 *   bzip2, xz, zstd, lz4, brotli, lzip, lzma → matching extensions; .lz uses LZIP magic vs raw lzma
 *   woff2_compress / woff2_decompress → .woff2
 *   php-zip → .zip (exactly one member, no encryption; inner payload via getFromIndex; re-packed with max deflate when supported)
 *   7z/7za/7zz → .7z (exactly one file after extract)
 *   qpdf (+ pdfinfo for page check) → .pdf
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
 *   FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_ALWAYS=1 peels to FMQ2 even when a gzip-1 probe favors keeping the container (default: peel only if probe improves).
 *   FRACTAL_ZIP_LITERALPAC_MPQ_REPACK=0 disables automatic repack-smaller (smpq) before the optional MPQ_TOOL path (faster dev runs).
 *   FRACTAL_ZIP_MPQ_SMPQ_FULL_SCAN=1: try all MPQ version × compression combos in tools/fractal_zip_mpq_semantic.py (slower, marginally better repack).
 *   Without smpq, MPQ semantic peel is skipped (optional stream handler FRACTAL_ZIP_LITERALPAC_MPQ_TOOL is separate).
 * FRACTAL_ZIP_LITERALPAC_STREAM_PEEL_MAX_PASSES (default 24): max stream normalize rounds by extension + magic sniff.
 */

function fractal_zip_literal_pac_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_LITERALPAC');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_pac_stream_enabled(): bool {
	if (!fractal_zip_literal_pac_enabled()) {
		return false;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_STREAM');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_pac_max_compressed_bytes(): int {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_MAX_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 0;
	}
	$v = (int) $e;
	return $v <= 0 ? 0 : $v;
}

function fractal_zip_literal_pac_max_decompressed_bytes(): int {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_MAX_DECOMPRESSED_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 128 * 1024 * 1024;
	}
	$v = (int) $e;
	return $v <= 0 ? 0 : $v;
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
	$e = getenv('FRACTAL_ZIP_LITERALPAC_STREAM_PEEL_MAX_PASSES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 24;
	}
	$v = (int) $e;
	return $v <= 0 ? 1 : min(256, $v);
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
	for ($i = 0; $i < $maxR; $i++) {
		$ext = strtolower((string) pathinfo($relPath, PATHINFO_EXTENSION));
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
 * Try registry handlers in order when magic matches (extension-agnostic). Skips brotli (no stable magic).
 */
function fractal_zip_literal_pac_try_stream_smaller_by_magic(string $compressed): ?array {
	foreach (FRACTAL_ZIP_LITERALPAC_REGISTRY as $row) {
		$mp = $row['magic_prefix'] ?? null;
		if ($mp !== null && $mp !== '') {
			if (!str_starts_with($compressed, $mp)) {
				continue;
			}
		} else {
			$h = $row['handler'];
			if ($h !== 'lzma_sniff') {
				continue;
			}
		}
		$r = fractal_zip_literal_pac_run_registry_handler($row['handler'], $compressed);
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
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_literal_pac_try_stream_smaller(string $ext, string $compressed): ?array {
	foreach (FRACTAL_ZIP_LITERALPAC_REGISTRY as $row) {
		if (!in_array($ext, $row['extensions'], true)) {
			continue;
		}
		$mp = $row['magic_prefix'] ?? null;
		if ($mp !== null && $mp !== '' && !str_starts_with($compressed, $mp)) {
			continue;
		}
		return fractal_zip_literal_pac_run_registry_handler($row['handler'], $compressed);
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
		$proc = @proc_open($cmd, $desc, $pipes, null, null);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
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
		$proc = @proc_open($cmd, $desc, $pipes, null, null);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
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
	$p = shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null');
	return $cache[$name] = (is_string($p) && trim($p) !== '');
}

function fractal_zip_literal_pac_payload_within_limit(int $len): bool {
	$max = fractal_zip_literal_pac_max_decompressed_bytes();
	return $max <= 0 || $len <= $max;
}

/**
 * Decompress via shell to string; null on failure or over max decompressed size.
 */
function fractal_zip_literal_pac_shell_decompress(string $tool, array $argsBeforePath, string $filePath): ?string {
	if (!fractal_zip_literal_pac_cmd_ok($tool)) {
		return null;
	}
	$cmd = escapeshellarg($tool);
	foreach ($argsBeforePath as $a) {
		$cmd .= ' ' . escapeshellarg((string) $a);
	}
	$cmd .= ' ' . escapeshellarg($filePath) . ' 2>/dev/null';
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
	$cmd = escapeshellarg($tool);
	foreach ($args as $a) {
		$cmd .= ' ' . escapeshellarg((string) $a);
	}
	$cmd .= ' < ' . escapeshellarg($payloadPath) . ' 2>/dev/null';
	$out = shell_exec($cmd);
	return is_string($out) && $out !== '' ? $out : null;
}

function fractal_zip_literal_pac_temp_pair(string $suffix): array {
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	return [$td . DIRECTORY_SEPARATOR . 'fzl_' . $id . '_in.' . $suffix, $td . DIRECTORY_SEPARATOR . 'fzl_' . $id . '_pay.bin'];
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
		$c = fractal_zip_literal_pac_shell_compress_to_string('pigz', ['-11', '-n', '-c'], $payPath);
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
	return fractal_zip_literal_pac_try_recompress($compressed, 'bz2', 'bzip2', ['-dc'], ['-9', '-c']);
}

function fractal_zip_literal_pac_try_xz_smaller(string $compressed): ?array {
	return fractal_zip_literal_pac_try_recompress($compressed, 'xz', 'xz', ['-dc'], ['-9', '-c']);
}

function fractal_zip_literal_pac_try_lzma_smaller(string $compressed): ?array {
	if (!fractal_zip_literal_pac_cmd_ok('lzma')) {
		return null;
	}
	return fractal_zip_literal_pac_try_recompress($compressed, 'lz', 'lzma', ['-dc'], ['-9', '-c']);
}

function fractal_zip_literal_pac_try_lzip_smaller(string $compressed): ?array {
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
	return fractal_zip_literal_pac_try_recompress($compressed, 'zst', 'zstd', ['-dc'], ['-' . (string) $lv, '-c', '--stdout']);
}

function fractal_zip_literal_pac_try_lz4_smaller(string $compressed): ?array {
	return fractal_zip_literal_pac_try_recompress($compressed, 'lz4', 'lz4', ['-dc'], ['-9', '-c']);
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
	$ret = 1;
	$null = [];
	@exec('brotli -d -i ' . escapeshellarg($brIn) . ' -o ' . escapeshellarg($payPath) . ' 2>/dev/null', $null, $ret);
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
	@exec('brotli -q 11 -i ' . escapeshellarg($payPath) . ' -o ' . escapeshellarg($brOut) . ' 2>/dev/null', $null, $ret);
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
	$ret2 = 1;
	@exec('brotli -d -i ' . escapeshellarg($vBr) . ' -o ' . escapeshellarg($vPay) . ' 2>/dev/null', $null, $ret2);
	@unlink($vBr);
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
		if (preg_match('/^Pages:\s*(\d+)\s*$/i', trim((string) $l), $m)) {
			return (int) $m[1];
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
		if (file_put_contents($arcIn, $compressed) === false) {
			return null;
		}
		if (!@mkdir($exDir, 0700, true)) {
			return null;
		}
		$null = [];
		$ret = 1;
		exec(escapeshellarg($seven) . ' x -o' . escapeshellarg($exDir) . ' ' . escapeshellarg($arcIn) . ' -y 2>/dev/null', $null, $ret);
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
		exec('cd ' . escapeshellarg($exDir) . ' && ' . escapeshellarg($seven) . ' a -t7z -mx=9 -y ' . escapeshellarg($newArc) . ' ' . escapeshellarg($relInner) . ' 2>/dev/null', $null, $ret2);
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
		exec(escapeshellarg($seven) . ' x -o' . escapeshellarg($ex2) . ' ' . escapeshellarg($vArc) . ' -y 2>/dev/null', $null, $ret3);
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

function fractal_zip_literal_pac_try_pdf_qpdf_smaller(string $compressed): ?array {
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
	exec('qpdf ' . escapeshellarg($in) . ' ' . escapeshellarg($out) . ' 2>/dev/null', $null, $ret);
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
	$ret = 1;
	$null = [];
	@exec('woff2_decompress ' . escapeshellarg($w2in) . ' 2>/dev/null', $null, $ret);
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
	@exec('woff2_compress ' . escapeshellarg($ttf) . ' 2>/dev/null', $null, $ret);
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
	$ret2 = 1;
	@exec('woff2_decompress ' . escapeshellarg($vW) . ' 2>/dev/null', $null, $ret2);
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
	$e = getenv('FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
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

/** Strip trailing .gz for raster pac when the gzip layer was peeled. */
function fractal_zip_literal_path_for_raster_pac_after_gzip_strip(string $relPath): string {
	if (preg_match('/\.gz$/i', $relPath) === 1) {
		return (string) preg_replace('/\.gz$/i', '', $relPath);
	}
	return $relPath;
}

function fractal_zip_literal_gzip_peel_max_layers(): int {
	$e = getenv('FRACTAL_ZIP_LITERAL_GZIP_PEEL_MAX_LAYERS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 64;
	}
	$v = (int) $e;
	return $v <= 0 ? 1 : min(1024, $v);
}

function fractal_zip_literal_peel_stabilize_max_rounds(): int {
	$e = getenv('FRACTAL_ZIP_LITERAL_PEEL_STABILIZE_ROUNDS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 48;
	}
	$v = (int) $e;
	return $v <= 0 ? 1 : min(512, $v);
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
	$out = '';
	do {
		$b = $n & 0x7F;
		$n >>= 7;
		$out .= chr($n > 0 ? ($b | 0x80) : $b);
	} while ($n > 0);
	return $out;
}

function fractal_zip_literal_semantic_zip_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_semantic_7z_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_7Z');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_semantic_mpq_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_semantic_peel_max_total(): int {
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_PEEL_MAX_LAYERS');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 48;
	}
	$v = (int) $e;
	return $v <= 0 ? 1 : min(256, $v);
}

function fractal_zip_literal_path_for_raster_pac_after_zip_strip(string $relPath): string {
	if (preg_match('/\.zip$/i', $relPath) === 1) {
		return (string) preg_replace('/\.zip$/i', '', $relPath);
	}
	return $relPath;
}

function fractal_zip_literal_path_for_raster_pac_after_7z_strip(string $relPath): string {
	if (preg_match('/\.7z$/i', $relPath) === 1) {
		return (string) preg_replace('/\.7z$/i', '', $relPath);
	}
	return $relPath;
}

function fractal_zip_literal_path_looks_mpq_semantic(string $relPath): bool {
	$ext = strtolower(pathinfo(str_replace('\\', '/', $relPath), PATHINFO_EXTENSION));
	return $ext === 'sc2replay' || $ext === 'mpq' || $ext === 'w3x';
}

function fractal_zip_literal_path_for_raster_pac_after_mpq_strip(string $relPath): string {
	$relPath = str_replace('\\', '/', $relPath);
	if (preg_match('/\.sc2replay$/i', $relPath) === 1) {
		return (string) preg_replace('/\.sc2replay$/i', '', $relPath);
	}
	if (preg_match('/\.mpq$/i', $relPath) === 1) {
		return (string) preg_replace('/\.mpq$/i', '', $relPath);
	}
	if (preg_match('/\.w3x$/i', $relPath) === 1) {
		return (string) preg_replace('/\.w3x$/i', '', $relPath);
	}
	return $relPath;
}

function fractal_zip_literal_pac_mpq_semantic_script(): ?string {
	$p = realpath(__DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'fractal_zip_mpq_semantic.py');
	return ($p !== false && is_file($p)) ? $p : null;
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
		$proc = @proc_open($cmd, $desc, $pipes, null, null);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
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
		return array($inner, 'v2');
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
	if (!fractal_zip_literal_pac_cmd_ok('python3') || !fractal_zip_literal_pac_cmd_ok('smpq')) {
		return null;
	}
	$script = fractal_zip_literal_pac_mpq_semantic_script();
	if ($script === null) {
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
		$proc = @proc_open($cmd, $desc, $pipes, null, null);
		if (!is_resource($proc)) {
			return null;
		}
		fclose($pipes[0]);
		if (isset($pipes[1]) && is_resource($pipes[1])) {
			stream_get_contents($pipes[1]);
			fclose($pipes[1]);
		}
		if (isset($pipes[2]) && is_resource($pipes[2])) {
			stream_get_contents($pipes[2]);
			fclose($pipes[2]);
		}
		$code = proc_close($proc);
		if ($code !== 0 || !is_file($outPath)) {
			return null;
		}
		$out = file_get_contents($outPath);
		return is_string($out) && $out !== '' ? $out : null;
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
	return [$payload, $name];
}

function fractal_zip_literal_pac_rebuild_zip_single_semantic(string $payload, string $memberName): ?string {
	if ($memberName === '' || str_contains($memberName, "\0") || strlen($memberName) > 65535) {
		return null;
	}
	if (!class_exists(ZipArchive::class)) {
		return null;
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
	return is_string($newC) && $newC !== '' ? $newC : null;
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
	$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7p_' . bin2hex(random_bytes(8));
	if (!@mkdir($td, 0700, true)) {
		return null;
	}
	$arcIn = $td . DIRECTORY_SEPARATOR . 'in.7z';
	$exDir = $td . DIRECTORY_SEPARATOR . 'ex';
	try {
		if (file_put_contents($arcIn, $compressed) === false) {
			return null;
		}
		if (!@mkdir($exDir, 0700, true)) {
			return null;
		}
		$null = [];
		$ret = 1;
		exec(escapeshellarg($seven) . ' x -o' . escapeshellarg($exDir) . ' ' . escapeshellarg($arcIn) . ' -y 2>/dev/null', $null, $ret);
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
		return [$payload, $relInner];
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
		exec('cd ' . escapeshellarg($workDir) . ' && ' . escapeshellarg((string) $seven) . ' a -t7z -mx=9 -y ' . escapeshellarg($newArc) . ' ' . escapeshellarg($relInnerPath) . ' 2>/dev/null', $null, $ret2);
		if ($ret2 !== 0 || !is_file($newArc)) {
			return null;
		}
		$newC = file_get_contents($newArc);
		return is_string($newC) && $newC !== '' ? $newC : null;
	} finally {
		fractal_zip_literal_pac_rmdir_recursive($td);
	}
}

/**
 * @param list<array{0: string, 1: string}> $semanticLayers ('zip', name)|('7z', relPath)|('mpq', tag), outermost peel first
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
			$c = shell_exec('gzip -2 -n -c < ' . escapeshellarg($inPath) . ' 2>/dev/null');
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
			$c = shell_exec('pigz -2 -n -c < ' . escapeshellarg($inPath) . ' 2>/dev/null');
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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_deep_unwrap.php';
