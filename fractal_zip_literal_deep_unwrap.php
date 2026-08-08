<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_recursive_peel.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_content_format_policy.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_rom_stack.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_ole_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_markup_islands.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_staroffice_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_staroffice_streams.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pdf_flate_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_printable_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility_unwrap.php';
if (!function_exists('fractal_zip_literal_pac_stream_enabled')) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
}
if (!function_exists('fractal_zip_image_pac_preprocess_literal_for_bundle')) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_image_pac.php';
}

/**
 * Shared “peel everything lossless, then normalize streams/rasters” path used by:
 * - literal-bundle transform selection (FZB modes)
 * - zip_folder per-member input (so fractal_zip sees the same unwrapped bytes as the bundle would)
 *
 * Semantic ZIP / 7z / MPQ / … peels use magic + {@see fractal_zip_identify_for_policy()} ordering
 * (re-evaluated after each peel). Gzip stack expansion and image/stream PAC follow a stabilize loop;
 * with {@code FRACTAL_ZIP_LITERAL_PEEL_AGGRESSIVE=1} (default on), semantic peel may run while a gzip stack is non-empty.
 * After stabilize, {@see fractal_zip_literal_deep_unwrap_region_pass} re-applies tar/ROM/PDF-oriented peels on the result.
 *
 * @return array{0: string, 1: list<array{0: string, 1: string}>, 2: list<array{len: int, sha1: string}>}
 */

/** Env {@code FRACTAL_ZIP_LITERAL_PLAIN_SKIP=0} disables the plain-text unwrap fast path (default on). */
function fractal_zip_literal_deep_unwrap_plain_skip_enabled(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_PLAIN_SKIP');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * True when $bytes look like plain text with no peelable container magic.
 * Uses optional native helper {@see fractal_zip_literal_gate_native_probe} when present.
 */
function fractal_zip_literal_looks_plain_unpeelable(string $relPath, string $rawBytes): bool
{
	$n = strlen($rawBytes);
	if ($n < 32) {
		return false;
	}
	$base = strtolower(basename(str_replace('\\', '/', $relPath)));
	$textExt = (bool) preg_match('/\\.(txt|csv|tsv|log|md|c|h|cc|cpp|hpp|java|js|ts|json|xml|html?|css|py|rb|go|rs|php|sh|yml|yaml)$/', $base);
	$native = fractal_zip_literal_gate_native_probe($rawBytes);
	if (is_array($native)) {
		$magic = (int) ($native['magic'] ?? 0);
		$ratio = (float) ($native['printable_ratio'] ?? 0.0);
		if ($magic !== 0) {
			return false;
		}
		return $textExt ? ($ratio >= 0.85) : ($ratio >= 0.995);
	}
	if (fractal_zip_literal_head_has_container_magic($rawBytes)) {
		return false;
	}
	$sample = $n > 131072 ? (substr($rawBytes, 0, 65536) . substr($rawBytes, -65536)) : $rawBytes;
	$sn = strlen($sample);
	$print = 0;
	for ($i = 0; $i < $sn; $i++) {
		$o = ord($sample[$i]);
		if ($o === 9 || $o === 10 || $o === 13 || ($o >= 32 && $o <= 126)) {
			$print++;
		}
	}
	$ratio = $sn > 0 ? ((float) $print / (float) $sn) : 0.0;
	return $textExt ? ($ratio >= 0.85) : ($ratio >= 0.995);
}

/** Gzip / ZIP / 7z / PNG / PDF / OLE / Zstd / XZ / Brotli / RAR magic at head. */
function fractal_zip_literal_head_has_container_magic(string $bytes): bool
{
	if ($bytes === '') {
		return false;
	}
	if (strlen($bytes) >= 2 && $bytes[0] === "\x1f" && $bytes[1] === "\x8b") {
		return true;
	}
	if (strlen($bytes) >= 4 && substr($bytes, 0, 4) === "PK\x03\x04") {
		return true;
	}
	if (strlen($bytes) >= 6 && substr($bytes, 0, 6) === "7z\xbc\xaf\x27\x1c") {
		return true;
	}
	if (strlen($bytes) >= 8 && substr($bytes, 0, 8) === "\x89PNG\r\n\x1a\n") {
		return true;
	}
	if (strlen($bytes) >= 5 && substr($bytes, 0, 5) === '%PDF-') {
		return true;
	}
	if (strlen($bytes) >= 8 && substr($bytes, 0, 8) === "\xd0\xcf\x11\xe0\xa1\xb1\x1a\xe1") {
		return true;
	}
	if (strlen($bytes) >= 4 && substr($bytes, 0, 4) === "\x28\xb5\x2f\xfd") {
		return true;
	}
	if (strlen($bytes) >= 6 && substr($bytes, 0, 6) === "\xfd7zXZ\x00") {
		return true;
	}
	if (strlen($bytes) >= 4 && ($bytes[0] === "\xce" || $bytes[0] === "\xcf") && substr($bytes, 1, 3) === "\xb2\xcf\x81") {
		return true;
	}
	if (strlen($bytes) >= 7 && substr($bytes, 0, 7) === "Rar!\x1a\x07\x00") {
		return true;
	}
	if (strlen($bytes) >= 8 && substr($bytes, 0, 8) === "Rar!\x1a\x07\x01\x00") {
		return true;
	}
	return false;
}

/**
 * Optional C helper: printable ratio + container-magic bitflags (same policy as tokenize).
 *
 * @return array{printable_ratio: float, magic: int}|null
 */
function fractal_zip_literal_gate_native_probe(string $bytes): ?array
{
	static $bin = null;
	static $disabled = false;
	if ($disabled) {
		return null;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_GATE_NATIVE');
	if ($e !== false) {
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
			$disabled = true;
			return null;
		}
	}
	if ($bin === null) {
		$cand = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'parallel_paq' . DIRECTORY_SEPARATOR . 'fz_literal_gate';
		$bin = is_executable($cand) ? $cand : '';
		if ($bin === '') {
			$disabled = true;
			return null;
		}
	}
	if ($bin === '' || $bytes === '') {
		return null;
	}
	$minEnv = getenv('FRACTAL_ZIP_LITERAL_GATE_NATIVE_MIN_BYTES');
	$minBytes = ($minEnv === false || trim((string) $minEnv) === '') ? 4096 : max(0, (int) trim((string) $minEnv));
	if (strlen($bytes) < $minBytes) {
		return null;
	}
	$desc = [
		0 => ['pipe', 'r'],
		1 => ['pipe', 'w'],
		2 => ['pipe', 'w'],
	];
	$proc = @proc_open([$bin, 'probe'], $desc, $pipes, null, null, ['binary_pipes' => true]);
	if (!is_resource($proc)) {
		return null;
	}
	fwrite($pipes[0], $bytes);
	fclose($pipes[0]);
	$out = stream_get_contents($pipes[1]);
	fclose($pipes[1]);
	fclose($pipes[2]);
	$code = proc_close($proc);
	if ($code !== 0 || !is_string($out) || $out === '') {
		return null;
	}
	$j = json_decode(trim($out), true);
	if (!is_array($j) || !isset($j['printable_ratio'], $j['magic'])) {
		return null;
	}
	return [
		'printable_ratio' => (float) $j['printable_ratio'],
		'magic' => (int) $j['magic'],
	];
}

/**
 * Second pass: layout-identified containers (tar, genesis ROM, PDF streams) after semantic+gzip rounds.
 */
function fractal_zip_literal_deep_unwrap_region_pass(
	string $relPath,
	string $pathRaster,
	string $work,
	array $semanticLayers,
	bool $forBundleEncode = true
): array {
	$row = fractal_zip_identify_for_policy($relPath, strlen($work) > 65536 ? substr($work, 0, 65536) : $work);
	$prof = (string) $row['content_profile'];

	if ($prof === 'container_tar' && fractal_zip_literal_semantic_tar_enabled()) {
		$tp = fractal_zip_literal_pac_peel_tar_any_semantic($work, $forBundleEncode);
		if ($tp !== null) {
			if ($forBundleEncode) {
				$semanticLayers[] = ['tar', $tp[1]];
			}
			$work = $tp[0];
			$pathRaster = fractal_zip_literal_path_for_raster_pac_after_tar_strip($pathRaster);
		}
	}
	if ($prof === 'media_rom') {
		$rp = fractal_zip_literal_pac_peel_genesis_rom_semantic($work);
		if ($rp !== null) {
			$semanticLayers[] = ['rom', $rp[1]];
			$work = $rp[0];
		}
	}
	if ($prof === 'container_ole' && fractal_zip_literal_semantic_ole_enabled() && !$forBundleEncode) {
		$op = fractal_zip_literal_pac_peel_ole_wire($work);
		if ($op !== null) {
			$work = $op[0];
		}
	}
	if (fractal_zip_literal_sniff_legacy_markup_compound($work)) {
		$mp = fractal_zip_literal_pac_peel_staroffice_section_wire($work);
		if ($mp === null) {
			$mp = fractal_zip_literal_pac_peel_staroffice_wire($work);
		}
		if ($mp !== null) {
			$work = $mp[0];
		}
	}
	if (fractal_zip_content_format_policy_is_pdf_container($relPath, $work)) {
		$pf = fractal_zip_literal_pac_peel_pdf_flate_concat_wire($work);
		if ($pf !== null) {
			$work = $pf[0];
		}
	}
	if (fractal_zip_literal_sniff_high_printable_opaque($work)
		|| fractal_zip_literal_sniff_dicom_text_meta($work)
		|| fractal_zip_literal_sniff_blkmed_image($work)) {
		$pr = fractal_zip_literal_pac_peel_printable_runs_wire($work);
		if ($pr !== null) {
			$work = $pr[0];
		}
	}
	if (fractal_zip_content_format_policy_is_pdf_container($relPath, $work) && fractal_zip_literal_pac_stream_enabled()) {
		$maxC = fractal_zip_literal_pac_max_compressed_bytes();
		if ($maxC <= 0 || strlen($work) <= $maxC) {
			$got = fractal_zip_literal_pac_try_stream_smaller_by_magic($work);
			if ($got === null) {
				foreach (array('pdf', '') as $extHint) {
					$got = fractal_zip_literal_pac_try_stream_smaller($extHint, $work);
					if ($got !== null) {
						break;
					}
				}
			}
			if ($got !== null) {
				$work = $got[0];
			}
		}
	}
	return [$work, $pathRaster, $semanticLayers];
}

function fractal_zip_literal_deep_unwrap_with_layers(string $relPath, string $rawBytes, bool $forBundleEncode = true): array {
	$rawBytes = (string) $rawBytes;
	$rel = str_replace('\\', '/', (string) $relPath);
	$rel = trim($rel, '/');
	if ($rel === '' || $rawBytes === '') {
		return [$rawBytes, [], []];
	}
	// Plain text / no container magic: skip stabilize+region+stream PAC (test_files90 ~200 ms/file).
	if (fractal_zip_literal_deep_unwrap_plain_skip_enabled()
		&& fractal_zip_literal_looks_plain_unpeelable($rel, $rawBytes)) {
		return [$rawBytes, [], []];
	}
	static $mpqSemanticProxyGate = null;
	if ($mpqSemanticProxyGate === null) {
		$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE');
		$mpqSemanticProxyGate = false;
		if ($e !== false && trim((string) $e) !== '') {
			$v = strtolower(trim((string) $e));
			$mpqSemanticProxyGate = ($v !== '0' && $v !== 'off' && $v !== 'false' && $v !== 'no');
		}
	}
	$pathRaster = $rel;
	$work = $rawBytes;
	$gzipStack = [];
	$semanticLayers = [];
	$maxRound = fractal_zip_literal_peel_stabilize_max_rounds();
	$maxSem = fractal_zip_literal_semantic_peel_max_total();
	$peelAggressive = fractal_zip_content_format_peel_aggressive_enabled();
	for ($r = 0; $r < $maxRound; $r++) {
		$snap = $work;
		$innerProgress = true;
		while ($innerProgress) {
			$innerProgress = false;
			$w0 = $work;
			if ($gzipStack === [] || $peelAggressive) {
				list($work, $pathRaster, $semanticLayers, $gzipStack) = fractal_zip_literal_recursive_peel_try_one_semantic(
					$rel,
					$pathRaster,
					$work,
					$semanticLayers,
					$gzipStack,
					$maxSem,
					$mpqSemanticProxyGate,
					$forBundleEncode
				);
			}
			list($work, $pathRaster, $gzipStack) = fractal_zip_literal_append_consecutive_gzip_peels($pathRaster, $work, $gzipStack);
			if ($work !== $w0) {
				$innerProgress = true;
			}
		}
		if ($gzipStack === [] || $peelAggressive) {
			$work = fractal_zip_image_pac_preprocess_literal_for_bundle($pathRaster, $work);
			if (fractal_zip_literal_pac_stream_enabled()) {
				$maxC = fractal_zip_literal_pac_max_compressed_bytes();
				if ($maxC <= 0 || strlen($work) <= $maxC) {
					$work = fractal_zip_literal_pac_preprocess_streams_multipass($pathRaster, $work);
				}
			}
		}
		if ($work === $snap) {
			break;
		}
	}
	list($work, $pathRaster, $semanticLayers) = fractal_zip_literal_deep_unwrap_region_pass(
		$rel,
		$pathRaster,
		$work,
		$semanticLayers,
		$forBundleEncode
	);
	if (fractal_zip_literal_pac_stream_enabled()) {
		$maxC = fractal_zip_literal_pac_max_compressed_bytes();
		if ($maxC <= 0 || strlen($work) <= $maxC) {
			$work = fractal_zip_literal_pac_preprocess_streams_multipass($pathRaster, $work);
		}
	}
	list($work, $semanticLayers) = fractal_zip_literal_legibility_unwrap_pass($rel, $work, $semanticLayers, $forBundleEncode);
	return [$work, $semanticLayers, $gzipStack];
}
