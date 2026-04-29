<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_objects.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_stream_markers.php';

/**
 * PDF /JBIG2Decode literal-PAC: optional lossless re-encode of embedded JBIG2 image streams when an external
 * JBIG2 encoder is available (agl `jbig2` or `jbig2enc` on PATH). Decodes with `jbig2dec`, re-encodes, accepts
 * only if jbig2dec output (PBM body) matches the original stream’s decoded image (pixel-identical).
 * Re-encoded streams are checked with `jbig2dec -e` then, if needed, without `-e` (encoder output is usually standalone).
 *
 * Tools:
 *   - jbig2dec (required; uses **-e** embedded mode for raw `/JBIG2Decode` stream bodies)
 *   - jbig2 OR jbig2enc (encoder; agl jbig2enc often installs the binary as `jbig2`)
 *
 * FRACTAL_ZIP_LITERALPAC_PDF_JBIG2=0 disables. Unset ⇒ enabled when tools exist.
 * FRACTAL_ZIP_PDF_JBIG2_PAC_MAX_STREAM_BYTES (optional): skip streams larger than N bytes (default 8 MiB).
 * FRACTAL_ZIP_PDF_JBIG2_PAC_MAX_IMAGE_STREAMS (optional): max `stream` tokens inspected per PDF (default 1024).
 * FRACTAL_ZIP_PDF_JBIG2_ENCODE_FLAG_SETS (optional): override encoder try-list, pipe-separated flag sets, e.g. `-d||-s|-s -a` (`""` = default generic coder between pipes).
 *
 * Scope (v1): single-filter `/Filter /JBIG2Decode` only (no `/Filter [/FlateDecode /JBIG2Decode]` yet),
 * direct /Length only (same limitation as JPEG PAC: indirect /Length n 0 R is skipped).
 * PDFs that split **JBIG2Globals** into a separate stream are not handled yet (jbig2dec two-argument mode).
 */

function fractal_zip_pdf_jbig2_pac_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PDF_JBIG2');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_pdf_jbig2dec_bin(): ?string {
	static $b = null;
	if ($b !== null) {
		return $b;
	}
	$p = shell_exec('command -v jbig2dec 2>/dev/null');
	return $b = (is_string($p) && trim($p) !== '' ? trim($p) : null);
}

/**
 * @return list<string>
 */
function fractal_zip_pdf_jbig2_encoder_bins(): array {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$out = [];
	foreach (['jbig2', 'jbig2enc'] as $name) {
		$p = shell_exec('command -v ' . $name . ' 2>/dev/null');
		if (is_string($p) && trim($p) !== '') {
			$out[] = trim($p);
		}
	}
	return $cached = $out;
}

function fractal_zip_pdf_jbig2_pac_max_image_streams(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PDF_JBIG2_PAC_MAX_IMAGE_STREAMS');
	if ($e === false || trim((string) $e) === '') {
		return $cached = 1024;
	}
	return $cached = max(1, (int) trim((string) $e));
}

function fractal_zip_pdf_jbig2_pac_max_stream_bytes(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PDF_JBIG2_PAC_MAX_STREAM_BYTES');
	if ($e === false || trim((string) $e) === '') {
		return $cached = 8 * 1024 * 1024;
	}
	return $cached = max(4096, (int) trim((string) $e));
}

function fractal_zip_pdf_pac_dict_jbig2_single_filter(string $dict): bool {
	if (substr_count($dict, '/Filter') !== 1) {
		return false;
	}
	if (str_contains($dict, '/DCTDecode') || str_contains($dict, '/JPXDecode') || str_contains($dict, '/CCITTFaxDecode')) {
		return false;
	}
	if (str_contains($dict, '/FlateDecode') || str_contains($dict, '/LZWDecode') || str_contains($dict, '/ASCII85Decode') || str_contains($dict, '/ASCIIHexDecode')) {
		return false;
	}
	if (! fractal_zip_pdf_dict_has_filter_key_then_slash_name($dict, '/JBIG2Decode', true)) {
		return false;
	}
	if (fractal_zip_pdf_dict_has_filter_array_bracket($dict)) {
		return false;
	}
	return true;
}

/**
 * Decode JBIG2 stream file to PBM path; returns true if PBM written.
 */
function fractal_zip_pdf_jbig2dec_to_pbm(string $jbig2dec, string $inJb2, string $outPbm): bool {
	@unlink($outPbm);
	$null = [];
	$ex = 1;
	exec($jbig2dec . ' -e -o ' . escapeshellarg($outPbm) . ' ' . escapeshellarg($inJb2) . ' 2>/dev/null', $null, $ex);
	return $ex === 0 && is_file($outPbm) && filesize($outPbm) > 16;
}

/**
 * Decode JBIG2 to PBM: try embedded (`-e`, PDF stream body) then standalone (encoder output is usually the latter).
 */
function fractal_zip_pdf_jbig2dec_to_pbm_embedded_or_standalone(string $jbig2dec, string $inJb2, string $outPbm): bool {
	if (fractal_zip_pdf_jbig2dec_to_pbm($jbig2dec, $inJb2, $outPbm)) {
		return true;
	}
	@unlink($outPbm);
	$null = [];
	$ex = 1;
	exec($jbig2dec . ' -o ' . escapeshellarg($outPbm) . ' ' . escapeshellarg($inJb2) . ' 2>/dev/null', $null, $ex);
	return $ex === 0 && is_file($outPbm) && filesize($outPbm) > 16;
}

/**
 * @return string|null raw PBM file contents for comparison (full file including header)
 */
function fractal_zip_pdf_jbig2_pbm_from_jb2_file(string $jbig2dec, string $jb2Path, string $td, string $id, string $suffix): ?string {
	$pbm = $td . DIRECTORY_SEPARATOR . 'fzj2_' . $id . $suffix . '.pbm';
	if (!fractal_zip_pdf_jbig2dec_to_pbm_embedded_or_standalone($jbig2dec, $jb2Path, $pbm)) {
		@unlink($pbm);
		return null;
	}
	$raw = file_get_contents($pbm);
	@unlink($pbm);
	return is_string($raw) && $raw !== '' ? $raw : null;
}

/**
 * Shell argv fragments between encoder path and input PBM (lossless candidates only). `-s -r` is omitted (broken in common agl builds).
 * `-s -p` is omitted (writes multi-file via `-b`, not one stdout stream).
 *
 * @return list<string>
 */
function fractal_zip_pdf_jbig2_encoder_flag_sets(): array {
	static $cached = null;
	static $cachedKey = null;
	$e = getenv('FRACTAL_ZIP_PDF_JBIG2_ENCODE_FLAG_SETS');
	$key = (is_string($e) && trim($e) !== '') ? trim($e) : '';
	if ($cached !== null && $cachedKey === $key) {
		return $cached;
	}
	if ($key !== '') {
		$tok = array_values(array_map('trim', explode('|', $key)));
		if ($tok !== []) {
			$cachedKey = $key;
			return $cached = $tok;
		}
	}
	$cachedKey = '';
	return $cached = fractal_zip_pdf_jbig2_encoder_flag_sets_default();
}

/**
 * @return list<string>
 */
function fractal_zip_pdf_jbig2_encoder_flag_sets_default(): array {
	return array(
		'-d',
		'',
		'-s',
		'-s -a',
		'-d -a',
		'-a',
	);
}

/**
 * Try each flag set; return smallest verified JBIG2 body strictly smaller than $jb2Bytes, or null.
 */
function fractal_zip_pdf_jbig2_try_encode_best(
	string $encBin,
	string $pbmPath,
	string $outJb2,
	string $jbig2dec,
	string $jb2Bytes,
	string $refPbm,
	string $td,
	string $id
): ?string {
	$pbmQ = escapeshellarg($pbmPath);
	$outQ = escapeshellarg($outJb2);
	$encQ = escapeshellarg($encBin);
	$origLen = strlen($jb2Bytes);
	$best = null;
	$null = [];
	foreach (fractal_zip_pdf_jbig2_encoder_flag_sets() as $flags) {
		$mid = trim($flags) === '' ? '' : (' ' . $flags);
		$cmd = $encQ . $mid . ' ' . $pbmQ . ' > ' . $outQ . ' 2>/dev/null';
		@unlink($outJb2);
		$ex = 1;
		exec($cmd, $null, $ex);
		if ($ex !== 0 || !is_file($outJb2)) {
			continue;
		}
		$sz = filesize($outJb2);
		if ($sz < 4 || $sz >= $origLen) {
			continue;
		}
		$newBytes = (string) @file_get_contents($outJb2);
		if ($newBytes === '') {
			continue;
		}
		$verifyIn = $td . DIRECTORY_SEPARATOR . 'fzj2_' . $id . '_chk.jb2';
		if (file_put_contents($verifyIn, $newBytes) === false) {
			continue;
		}
		$newPbm = fractal_zip_pdf_jbig2_pbm_from_jb2_file($jbig2dec, $verifyIn, $td, $id, '_c' . bin2hex(random_bytes(2)));
		@unlink($verifyIn);
		if ($newPbm === null || $newPbm !== $refPbm) {
			continue;
		}
		if ($best === null || strlen($newBytes) < strlen($best)) {
			$best = $newBytes;
		}
	}
	@unlink($outJb2);
	return $best;
}

/**
 * @return string|null smaller JBIG2 bytes, or null
 */
function fractal_zip_pdf_jbig2_reencode_one_stream(
	string $jbig2dec,
	string $encBin,
	string $jb2Bytes,
	string $td,
	string $id
): ?string {
	$maxB = fractal_zip_pdf_jbig2_pac_max_stream_bytes();
	if (strlen($jb2Bytes) > $maxB) {
		return null;
	}
	$in = $td . DIRECTORY_SEPARATOR . 'fzj2_' . $id . '_in.jb2';
	if (file_put_contents($in, $jb2Bytes) === false) {
		return null;
	}
	$pbm = $td . DIRECTORY_SEPARATOR . 'fzj2_' . $id . '.pbm';
	if (!fractal_zip_pdf_jbig2dec_to_pbm($jbig2dec, $in, $pbm)) {
		@unlink($in);
		@unlink($pbm);
		return null;
	}
	$refPbm = (string) @file_get_contents($pbm);
	if ($refPbm === '' || !str_starts_with($refPbm, 'P4')) {
		@unlink($in);
		@unlink($pbm);
		return null;
	}
	$outJb2 = $td . DIRECTORY_SEPARATOR . 'fzj2_' . $id . '_out.jb2';
	$newBytes = fractal_zip_pdf_jbig2_try_encode_best(
		$encBin,
		$pbm,
		$outJb2,
		$jbig2dec,
		$jb2Bytes,
		$refPbm,
		$td,
		$id
	);
	@unlink($pbm);
	@unlink($in);
	if ($newBytes === null) {
		return null;
	}
	return $newBytes;
}

/**
 * @return string|null rewritten PDF or null
 */
function fractal_zip_pdf_pac_recompress_jbig2_all_passes(string $pdf, int $maxR = 4): ?string {
	if (!fractal_zip_pdf_jbig2_pac_enabled()) {
		return null;
	}
	$jbig2dec = fractal_zip_pdf_jbig2dec_bin();
	if ($jbig2dec === null) {
		return null;
	}
	$encList = fractal_zip_pdf_jbig2_encoder_bins();
	if ($encList === []) {
		return null;
	}
	$work = $pdf;
	$any = false;
	$omap = fractal_zip_pdf_object_index_offsets($work);
	for ($r = 0; $r < $maxR; $r++) {
		$ch = false;
		$M0 = fractal_zip_pdf_pac_stream_token_offsets($work);
		$nm = count($M0);
		if ($nm < 1) {
			break;
		}
		$cap = fractal_zip_pdf_jbig2_pac_max_image_streams();
		$seen = 0;
		for ($idx = $nm - 1; $idx >= 0; $idx--) {
			if (++$seen > $cap) {
				break 2;
			}
			$e = $M0[$idx] ?? null;
			if (!is_array($e) || $e[1] < 0) {
				continue;
			}
			$abs0 = (int) $e[1];
			$tok = (string) $e[0];
			$open = fractal_zip_pdf_stream_dict_opening_lt_lt($work, $abs0);
			if ($open < 0) {
				continue;
			}
			$dict = (string) substr($work, $open, $abs0 - $open + 2);
			if (!fractal_zip_pdf_pac_dict_jbig2_single_filter($dict)) {
				continue;
			}
			$lenB = fractal_zip_pdf_dict_length_value($work, $dict, $omap);
			if ($lenB === null) {
				continue;
			}
			if (fractal_zip_pdf_dict_has_indirect_length_ref($dict)) {
				continue;
			}
			$dataStart = $abs0 + strlen($tok);
			$head = (string) substr($work, 0, $open);
			$close = $abs0;
			$lineRest = ($close + 2 <= $dataStart) ? (string) substr($work, $close + 2, $dataStart - ($close + 2)) : '';
			$oldEnc = (string) substr($work, $dataStart, $lenB);
			$n0 = strlen($work);
			$check = (string) substr($work, $dataStart + $lenB, (int) min(32, $n0 - $dataStart - $lenB));
			if (! fractal_zip_pdf_dict_prefix_endstream_after_preg_s_ws($check)) {
				continue;
			}
			$bestNew = null;
			foreach ($encList as $encBin) {
				$id = bin2hex(random_bytes(6));
				$td = sys_get_temp_dir();
				$tried = fractal_zip_pdf_jbig2_reencode_one_stream($jbig2dec, $encBin, $oldEnc, $td, $id);
				if (is_string($tried) && ($bestNew === null || strlen($tried) < strlen($bestNew))) {
					$bestNew = $tried;
				}
			}
			if ($bestNew === null) {
				continue;
			}
			$rep = fractal_zip_pdf_dict_replace_first_length_digits_preg_s($dict, strlen($bestNew));
			if (! $rep[1]) {
				continue;
			}
			$nd2 = $rep[0];
			$suffix = (string) substr($work, $dataStart + $lenB);
			$work = $head . $nd2 . $lineRest . $bestNew . $suffix;
			$ch = $any = true;
		}
		if (!$ch) {
			break;
		}
		$omap = fractal_zip_pdf_object_index_offsets($work);
	}
	return $any ? $work : null;
}

/**
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_pdf_jbig2_pac_recompress_smaller(string $pdf): ?array {
	if (!fractal_zip_pdf_jbig2_pac_enabled()) {
		return null;
	}
	if (fractal_zip_pdf_jbig2dec_bin() === null || fractal_zip_pdf_jbig2_encoder_bins() === []) {
		return null;
	}
	$n0 = strlen($pdf);
	if ($n0 < 64 || !str_starts_with($pdf, '%PDF-') || $n0 > 128 * 1024 * 1024) {
		return null;
	}
	$nw = fractal_zip_pdf_pac_recompress_jbig2_all_passes($pdf);
	if ($nw === null) {
		return null;
	}
	$s = $n0 - strlen($nw);
	if ($s <= 0) {
		return null;
	}
	if (!str_starts_with($nw, '%PDF-')) {
		return null;
	}
	return [$nw, $s];
}
