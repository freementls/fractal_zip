<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_objects.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_stream_markers.php';

/**
 * PDF /CCITTFaxDecode literal-PAC (v1): decode fax stream with fax2tiff, optional tiffcp -c g4 shrink,
 * extract the first CCITT strip from the resulting baseline little-endian TIFF, verify with ImageMagick
 * PBM compare (lossless vs decoded pixels).
 *
 * Tools: fax2tiff, tiffcp, convert (ImageMagick) on PATH.
 * FRACTAL_ZIP_LITERALPAC_PDF_CCITT=0 disables.
 * FRACTAL_ZIP_PDF_CCITT_PAC_MAX_STREAM_BYTES (default 8 MiB), FRACTAL_ZIP_PDF_CCITT_PAC_MAX_STREAMS (default 512).
 *
 * v1: single `/Filter /CCITTFaxDecode`, direct /Length only. Parses /Width, /Height, optional /K, /Columns, /Rows
 * from the stream dictionary (DecodeParms not fully modeled; /K and /Columns may appear anywhere in dict).
 * Multi-strip TIFFs are skipped (strip count must be 1).
 */

function fractal_zip_pdf_ccitt_pac_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PDF_CCITT');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_pdf_ccitt_fax2tiff_bin(): ?string {
	static $b = null;
	if ($b !== null) {
		return $b;
	}
	$p = shell_exec('command -v fax2tiff 2>/dev/null');
	return $b = (is_string($p) && trim($p) !== '' ? trim($p) : null);
}

function fractal_zip_pdf_ccitt_tiffcp_bin(): ?string {
	static $b = null;
	if ($b !== null) {
		return $b;
	}
	$p = shell_exec('command -v tiffcp 2>/dev/null');
	return $b = (is_string($p) && trim($p) !== '' ? trim($p) : null);
}

function fractal_zip_pdf_ccitt_convert_bin(): ?string {
	static $b = null;
	if ($b !== null) {
		return $b;
	}
	$p = shell_exec('command -v convert 2>/dev/null');
	return $b = (is_string($p) && trim($p) !== '' ? trim($p) : null);
}

function fractal_zip_pdf_ccitt_pac_max_stream_bytes(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PDF_CCITT_PAC_MAX_STREAM_BYTES');
	if ($e === false || trim((string) $e) === '') {
		return $cached = 8 * 1024 * 1024;
	}
	return $cached = max(4096, (int) trim((string) $e));
}

function fractal_zip_pdf_ccitt_pac_max_streams(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PDF_CCITT_PAC_MAX_STREAMS');
	if ($e === false || trim((string) $e) === '') {
		return $cached = 512;
	}
	return $cached = max(1, (int) trim((string) $e));
}

function fractal_zip_pdf_pac_dict_ccitt_single_filter(string $dict): bool {
	if (substr_count($dict, '/Filter') !== 1) {
		return false;
	}
	if (str_contains($dict, '/FlateDecode') || str_contains($dict, '/LZWDecode') || str_contains($dict, '/ASCII85Decode') || str_contains($dict, '/ASCIIHexDecode')) {
		return false;
	}
	if (str_contains($dict, '/DCTDecode') || str_contains($dict, '/JBIG2Decode') || str_contains($dict, '/JPXDecode')) {
		return false;
	}
	if (! fractal_zip_pdf_dict_has_filter_key_then_slash_name($dict, '/CCITTFaxDecode', true)) {
		return false;
	}
	if (fractal_zip_pdf_dict_has_filter_array_bracket($dict)) {
		return false;
	}
	return true;
}

/**
 * @return array{0:int,1:int}|null width,height for fax2tiff -w/-l
 */
function fractal_zip_pdf_ccitt_dict_dims(string $dict): ?array {
	$w = fractal_zip_pdf_dict_first_slash_key_unsigned_bounded($dict, '/Columns', 6);
	if ($w === null) {
		$w = fractal_zip_pdf_dict_first_slash_key_unsigned_bounded($dict, '/Width', 6);
	}
	$h = fractal_zip_pdf_dict_first_slash_key_unsigned_bounded($dict, '/Rows', 6);
	if ($h === null) {
		$h = fractal_zip_pdf_dict_first_slash_key_unsigned_bounded($dict, '/Height', 6);
	}
	if ($w === null || $h === null || $w < 8 || $h < 8 || $w > 65535 || $h > 65535) {
		return null;
	}
	return [$w, $h];
}

/**
 * @return int|null K param from dict or null if absent
 */
function fractal_zip_pdf_ccitt_dict_k(?string $dict): ?int {
	if ($dict === null || $dict === '') {
		return null;
	}
	$k = fractal_zip_pdf_dict_first_slash_k_signed_six($dict);
	if ($k === null) {
		return null;
	}
	return $k;
}

function fractal_zip_pdf_tiff_le_uint16(string $b, int $o): int {
	$n = strlen($b);
	if ($o >= 0 && $o + 2 <= $n) {
		return ord($b[$o]) | (ord($b[$o + 1]) << 8);
	}
	$a = unpack('v', substr($b, $o, 2));
	return is_array($a) && isset($a[1]) ? (int) $a[1] : 0;
}

function fractal_zip_pdf_tiff_le_uint32(string $b, int $o): int {
	$n = strlen($b);
	if ($o >= 0 && $o + 4 <= $n) {
		return ord($b[$o]) | (ord($b[$o + 1]) << 8) | (ord($b[$o + 2]) << 16) | (ord($b[$o + 3]) << 24);
	}
	$a = unpack('V', substr($b, $o, 4));
	return is_array($a) && isset($a[1]) ? (int) $a[1] : 0;
}

/**
 * Extract first strip bytes from a baseline little-endian TIFF with one strip (Group3/4 fax).
 */
function fractal_zip_pdf_tiff_compression_tag(string $tifPath): ?int {
	$b = (string) @file_get_contents($tifPath);
	$L = strlen($b);
	if ($L < 16 || $b[0] !== 'I' || $b[1] !== 'I' || fractal_zip_pdf_tiff_le_uint16($b, 2) !== 42) {
		return null;
	}
	$ifd0 = fractal_zip_pdf_tiff_le_uint32($b, 4);
	if ($ifd0 <= 0 || $ifd0 + 2 > $L) {
		return null;
	}
	$n = fractal_zip_pdf_tiff_le_uint16($b, $ifd0);
	$p0 = $ifd0 + 2;
	for ($i = 0; $i < $n; $i++) {
		$p = $p0 + $i * 12;
		$tag = fractal_zip_pdf_tiff_le_uint16($b, $p);
		$type = fractal_zip_pdf_tiff_le_uint16($b, $p + 2);
		$cnt = fractal_zip_pdf_tiff_le_uint32($b, $p + 4);
		if ($tag === 259 && $type === 3 && $cnt === 1) {
			return fractal_zip_pdf_tiff_le_uint16($b, $p + 8);
		}
	}
	return null;
}

function fractal_zip_pdf_tiff_first_strip_bytes(string $tifPath): ?string {
	$b = (string) @file_get_contents($tifPath);
	$L = strlen($b);
	if ($L < 16) {
		return null;
	}
	if ($b[0] !== 'I' || $b[1] !== 'I') {
		return null;
	}
	if (fractal_zip_pdf_tiff_le_uint16($b, 2) !== 42) {
		return null;
	}
	$ifd0 = fractal_zip_pdf_tiff_le_uint32($b, 4);
	if ($ifd0 <= 0 || $ifd0 + 2 > $L) {
		return null;
	}
	$n = fractal_zip_pdf_tiff_le_uint16($b, $ifd0);
	if ($n < 4 || $ifd0 + 2 + 12 * $n > $L) {
		return null;
	}
	$compression = null;
	$stripOff = null;
	$stripLen = null;
	$p0 = $ifd0 + 2;
	for ($i = 0; $i < $n; $i++) {
		$p = $p0 + $i * 12;
		$tag = fractal_zip_pdf_tiff_le_uint16($b, $p);
		$type = fractal_zip_pdf_tiff_le_uint16($b, $p + 2);
		$cnt = fractal_zip_pdf_tiff_le_uint32($b, $p + 4);
		if ($tag === 259) {
			if ($type === 3 && $cnt === 1) {
				$compression = fractal_zip_pdf_tiff_le_uint16($b, $p + 8);
			}
		} elseif ($tag === 273) {
			if ($type === 4 && $cnt === 1) {
				$stripOff = fractal_zip_pdf_tiff_le_uint32($b, $p + 8);
			}
		} elseif ($tag === 279) {
			if ($type === 4 && $cnt === 1) {
				$stripLen = fractal_zip_pdf_tiff_le_uint32($b, $p + 8);
			}
		}
	}
	if ($compression === null || $stripOff === null || $stripLen === null) {
		return null;
	}
	if ($compression !== 3 && $compression !== 4) {
		return null;
	}
	if ($stripOff < 0 || $stripLen <= 0 || $stripOff + $stripLen > $L) {
		return null;
	}
	return substr($b, $stripOff, $stripLen);
}

function fractal_zip_pdf_ccitt_tif_to_pbm(string $convert, string $tif, string $outPbm): bool {
	@unlink($outPbm);
	$null = [];
	$ex = 1;
	exec($convert . ' ' . escapeshellarg($tif) . ' ' . escapeshellarg($outPbm) . ' 2>/dev/null', $null, $ex);
	return $ex === 0 && is_file($outPbm) && filesize($outPbm) > 8;
}

/**
 * @return string|null smaller raw fax stream bytes
 */
function fractal_zip_pdf_ccitt_reencode_one_stream(
	string $fax2tiff,
	string $tiffcp,
	string $convert,
	string $raw,
	string $dict,
	string $td,
	string $id
): ?string {
	if (strlen($raw) > fractal_zip_pdf_ccitt_pac_max_stream_bytes()) {
		return null;
	}
	$dims = fractal_zip_pdf_ccitt_dict_dims($dict);
	if ($dims === null) {
		return null;
	}
	[$w, $h] = $dims;
	$k = fractal_zip_pdf_ccitt_dict_k($dict);
	$rawIn = $td . DIRECTORY_SEPARATOR . 'fzcc_' . $id . '.bin';
	if (file_put_contents($rawIn, $raw) === false) {
		return null;
	}
	$baseTif = $td . DIRECTORY_SEPARATOR . 'fzcc_' . $id . '_base.tif';
	@unlink($baseTif);
	$null = [];
	$ex = 1;
	$variants = [];
	if ($k !== null && $k < 0) {
		$variants[] = '-4 -w ' . (string) $w . ' -l ' . (string) $h;
	}
	if ($k !== null && $k === 0) {
		$variants[] = '-3 -1 -w ' . (string) $w . ' -l ' . (string) $h;
		$variants[] = '-3 -2 -w ' . (string) $w . ' -l ' . (string) $h;
	}
	if ($k !== null && $k > 0) {
		$variants[] = '-3 -2 -w ' . (string) $w . ' -l ' . (string) $h;
	}
	if ($variants === array()) {
		$variants = array(
			'-4 -w ' . (string) $w . ' -l ' . (string) $h,
			'-3 -1 -w ' . (string) $w . ' -l ' . (string) $h,
			'-3 -2 -w ' . (string) $w . ' -l ' . (string) $h,
		);
	}
	$okBase = false;
	$winFlags = '';
	foreach ($variants as $flags) {
		@unlink($baseTif);
		exec($fax2tiff . ' ' . $flags . ' -o ' . escapeshellarg($baseTif) . ' ' . escapeshellarg($rawIn) . ' 2>/dev/null', $null, $ex);
		if ($ex === 0 && is_file($baseTif) && filesize($baseTif) > 64) {
			$okBase = true;
			$winFlags = $flags;
			break;
		}
	}
	if (!$okBase || $winFlags === '' || !str_contains($winFlags, '-4')) {
		@unlink($rawIn);
		@unlink($baseTif);
		return null;
	}
	$refPbm = $td . DIRECTORY_SEPARATOR . 'fzcc_' . $id . '_ref.pbm';
	if (!fractal_zip_pdf_ccitt_tif_to_pbm($convert, $baseTif, $refPbm)) {
		@unlink($rawIn);
		@unlink($baseTif);
		@unlink($refPbm);
		return null;
	}
	$ref = (string) @file_get_contents($refPbm);
	@unlink($refPbm);
	if ($ref === '' || !str_starts_with($ref, 'P4')) {
		@unlink($rawIn);
		@unlink($baseTif);
		return null;
	}
	$optTif = $td . DIRECTORY_SEPARATOR . 'fzcc_' . $id . '_opt.tif';
	@unlink($optTif);
	$ex2 = 1;
	exec($tiffcp . ' -c g4 ' . escapeshellarg($baseTif) . ' ' . escapeshellarg($optTif) . ' 2>/dev/null', $null, $ex2);
	if ($ex2 !== 0 || !is_file($optTif)) {
		@unlink($optTif);
		@unlink($rawIn);
		@unlink($baseTif);
		return null;
	}
	$newRaw = fractal_zip_pdf_tiff_first_strip_bytes($optTif);
	@unlink($optTif);
	@unlink($baseTif);
	@unlink($rawIn);
	if ($newRaw === null || $newRaw === '' || strlen($newRaw) >= strlen($raw)) {
		return null;
	}
	$verifyIn = $td . DIRECTORY_SEPARATOR . 'fzcc_' . $id . '_v.bin';
	if (file_put_contents($verifyIn, $newRaw) === false) {
		return null;
	}
	$verTif = $td . DIRECTORY_SEPARATOR . 'fzcc_' . $id . '_v.tif';
	@unlink($verTif);
	@unlink($verTif);
	exec($fax2tiff . ' ' . $winFlags . ' -o ' . escapeshellarg($verTif) . ' ' . escapeshellarg($verifyIn) . ' 2>/dev/null', $null, $ex3);
	if ($ex3 !== 0 || !is_file($verTif) || filesize($verTif) < 64) {
		@unlink($verifyIn);
		@unlink($verTif);
		return null;
	}
	$newPbm = $td . DIRECTORY_SEPARATOR . 'fzcc_' . $id . '_new.pbm';
	if (!fractal_zip_pdf_ccitt_tif_to_pbm($convert, $verTif, $newPbm)) {
		@unlink($verifyIn);
		@unlink($verTif);
		@unlink($newPbm);
		return null;
	}
	$newP = (string) @file_get_contents($newPbm);
	@unlink($newPbm);
	@unlink($verifyIn);
	@unlink($verTif);
	if ($newP === '' || $newP !== $ref) {
		return null;
	}
	return $newRaw;
}

/**
 * @return string|null rewritten PDF or null
 */
function fractal_zip_pdf_pac_recompress_ccitt_all_passes(string $pdf, int $maxR = 3): ?string {
	if (!fractal_zip_pdf_ccitt_pac_enabled()) {
		return null;
	}
	$fax = fractal_zip_pdf_ccitt_fax2tiff_bin();
	$tcp = fractal_zip_pdf_ccitt_tiffcp_bin();
	$cv = fractal_zip_pdf_ccitt_convert_bin();
	if ($fax === null || $tcp === null || $cv === null) {
		return null;
	}
	$work = $pdf;
	$any = false;
	$omap = fractal_zip_pdf_object_index_offsets($work);
	$cap = fractal_zip_pdf_ccitt_pac_max_streams();
	$seen = 0;
	for ($r = 0; $r < $maxR; $r++) {
		$ch = false;
		$M0 = fractal_zip_pdf_pac_stream_token_offsets($work);
		$nm = count($M0);
		if ($nm < 1) {
			break;
		}
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
			if (!fractal_zip_pdf_pac_dict_ccitt_single_filter($dict)) {
				continue;
			}
			$lenB = fractal_zip_pdf_dict_length_value($work, $dict, $omap);
			if ($lenB === null || fractal_zip_pdf_dict_has_indirect_length_ref($dict)) {
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
			$id = bin2hex(random_bytes(6));
			$td = sys_get_temp_dir();
			$newB = fractal_zip_pdf_ccitt_reencode_one_stream($fax, $tcp, $cv, $oldEnc, $dict, $td, $id);
			if ($newB === null) {
				continue;
			}
			$rep = fractal_zip_pdf_dict_replace_first_length_digits_preg_s($dict, strlen($newB));
			if (! $rep[1]) {
				continue;
			}
			$nd2 = $rep[0];
			$suffix = (string) substr($work, $dataStart + $lenB);
			$work = $head . $nd2 . $lineRest . $newB . $suffix;
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
function fractal_zip_pdf_ccitt_pac_recompress_smaller(string $pdf): ?array {
	if (!fractal_zip_pdf_ccitt_pac_enabled()) {
		return null;
	}
	if (fractal_zip_pdf_ccitt_fax2tiff_bin() === null || fractal_zip_pdf_ccitt_tiffcp_bin() === null || fractal_zip_pdf_ccitt_convert_bin() === null) {
		return null;
	}
	$n0 = strlen($pdf);
	if ($n0 < 64 || !str_starts_with($pdf, '%PDF-') || $n0 > 128 * 1024 * 1024) {
		return null;
	}
	$nw = fractal_zip_pdf_pac_recompress_ccitt_all_passes($pdf);
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
