<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_objects.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_stream_markers.php';

/**
 * PDF /JPXDecode literal-PAC: lossless try-smaller using OpenJPEG (opj_decompress → opj_compress).
 * Accepts replacement only if opj_decompress outputs are byte-identical (same decoded image).
 *
 * Tools: opj_decompress + opj_compress on PATH.
 * FRACTAL_ZIP_LITERALPAC_PDF_JPX=0 disables.
 * FRACTAL_ZIP_PDF_JPX_PAC_MAX_STREAM_BYTES (default 16 MiB), FRACTAL_ZIP_PDF_JPX_PAC_MAX_STREAMS (default 512).
 *
 * v1: single `/Filter /JPXDecode`, direct /Length only (same as JPEG/JBIG2 PAC).
 */

function fractal_zip_pdf_jpx_pac_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PDF_JPX');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_pdf_jpx_opj_decompress_bin(): ?string {
	static $b = null;
	if ($b !== null) {
		return $b;
	}
	$p = shell_exec('command -v opj_decompress 2>/dev/null');
	return $b = (is_string($p) && trim($p) !== '' ? trim($p) : null);
}

function fractal_zip_pdf_jpx_opj_compress_bin(): ?string {
	static $b = null;
	if ($b !== null) {
		return $b;
	}
	$p = shell_exec('command -v opj_compress 2>/dev/null');
	return $b = (is_string($p) && trim($p) !== '' ? trim($p) : null);
}

function fractal_zip_pdf_jpx_pac_max_stream_bytes(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PDF_JPX_PAC_MAX_STREAM_BYTES');
	if ($e === false || trim((string) $e) === '') {
		return $cached = 16 * 1024 * 1024;
	}
	return $cached = max(4096, (int) trim((string) $e));
}

function fractal_zip_pdf_jpx_pac_max_streams(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_PDF_JPX_PAC_MAX_STREAMS');
	if ($e === false || trim((string) $e) === '') {
		return $cached = 512;
	}
	return $cached = max(1, (int) trim((string) $e));
}

function fractal_zip_pdf_pac_dict_jpx_single_filter(string $dict): bool {
	if (substr_count($dict, '/Filter') !== 1) {
		return false;
	}
	if (str_contains($dict, '/FlateDecode') || str_contains($dict, '/LZWDecode') || str_contains($dict, '/ASCII85Decode') || str_contains($dict, '/ASCIIHexDecode')) {
		return false;
	}
	if (str_contains($dict, '/DCTDecode') || str_contains($dict, '/JBIG2Decode') || str_contains($dict, '/CCITTFaxDecode')) {
		return false;
	}
	if (! fractal_zip_pdf_dict_has_filter_key_then_slash_name($dict, '/JPXDecode', true)) {
		return false;
	}
	if (fractal_zip_pdf_dict_has_filter_array_bracket($dict)) {
		return false;
	}
	return true;
}

function fractal_zip_pdf_jpx_decompress_to_tif(string $opjD, string $inPath, string $outTif): bool {
	@unlink($outTif);
	$null = [];
	$ex = 1;
	exec($opjD . ' -i ' . escapeshellarg($inPath) . ' -o ' . escapeshellarg($outTif) . ' 2>/dev/null', $null, $ex);
	return $ex === 0 && is_file($outTif) && filesize($outTif) > 32;
}

/**
 * @param string $extraOpts extra argv tokens placed **before** `-i` (e.g. ` -n 6` or empty)
 */
function fractal_zip_pdf_jpx_compress_try(string $opjC, string $inTif, string $outJp2, string $extraOpts): bool {
	@unlink($outJp2);
	$null = [];
	$ex = 1;
	$pre = $extraOpts === '' ? '' : (' ' . trim($extraOpts));
	exec($opjC . $pre . ' -i ' . escapeshellarg($inTif) . ' -o ' . escapeshellarg($outJp2) . ' 2>/dev/null', $null, $ex);
	return $ex === 0 && is_file($outJp2) && filesize($outJp2) > 16;
}

/**
 * @return string|null smaller JPX stream bytes
 */
function fractal_zip_pdf_jpx_reencode_one_stream(string $opjD, string $opjC, string $jpxBytes, string $td, string $id): ?string {
	if (strlen($jpxBytes) > fractal_zip_pdf_jpx_pac_max_stream_bytes()) {
		return null;
	}
	$in = $td . DIRECTORY_SEPARATOR . 'fzjp_' . $id . '.jp2';
	if (file_put_contents($in, $jpxBytes) === false) {
		return null;
	}
	$dec0 = $td . DIRECTORY_SEPARATOR . 'fzjp_' . $id . '_0.tif';
	if (!fractal_zip_pdf_jpx_decompress_to_tif($opjD, $in, $dec0)) {
		@unlink($in);
		@unlink($dec0);
		return null;
	}
	$refTif = (string) @file_get_contents($dec0);
	if ($refTif === '') {
		@unlink($in);
		@unlink($dec0);
		return null;
	}
	$cands = [
		'',
		'-n 5',
		'-n 6',
		'-p LRCP',
		'-p RLCP',
		'-p RPCL',
		'-p PCRL',
		'-p CPRL',
	];
	$best = null;
	$bestL = strlen($jpxBytes);
	foreach ($cands as $extra) {
		$outJp2 = $td . DIRECTORY_SEPARATOR . 'fzjp_' . $id . '_try.jp2';
		if (!fractal_zip_pdf_jpx_compress_try($opjC, $dec0, $outJp2, $extra)) {
			@unlink($outJp2);
			continue;
		}
		$nb = (string) @file_get_contents($outJp2);
		@unlink($outJp2);
		if ($nb === '' || strlen($nb) >= $bestL) {
			continue;
		}
		$vIn = $td . DIRECTORY_SEPARATOR . 'fzjp_' . $id . '_v.jp2';
		if (file_put_contents($vIn, $nb) === false) {
			continue;
		}
		$dec1 = $td . DIRECTORY_SEPARATOR . 'fzjp_' . $id . '_1.tif';
		if (!fractal_zip_pdf_jpx_decompress_to_tif($opjD, $vIn, $dec1)) {
			@unlink($vIn);
			@unlink($dec1);
			continue;
		}
		$cmp = (string) @file_get_contents($dec1);
		@unlink($vIn);
		@unlink($dec1);
		if ($cmp !== '' && $cmp === $refTif) {
			$best = $nb;
			$bestL = strlen($nb);
		}
	}
	@unlink($in);
	@unlink($dec0);
	return $best;
}

/**
 * @return string|null rewritten PDF or null
 */
function fractal_zip_pdf_pac_recompress_jpx_all_passes(string $pdf, int $maxR = 3): ?string {
	if (!fractal_zip_pdf_jpx_pac_enabled()) {
		return null;
	}
	$opjD = fractal_zip_pdf_jpx_opj_decompress_bin();
	$opjC = fractal_zip_pdf_jpx_opj_compress_bin();
	if ($opjD === null || $opjC === null) {
		return null;
	}
	$work = $pdf;
	$any = false;
	$omap = fractal_zip_pdf_object_index_offsets($work);
	$cap = fractal_zip_pdf_jpx_pac_max_streams();
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
			if (!fractal_zip_pdf_pac_dict_jpx_single_filter($dict)) {
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
			$newB = fractal_zip_pdf_jpx_reencode_one_stream($opjD, $opjC, $oldEnc, $td, $id);
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
function fractal_zip_pdf_jpx_pac_recompress_smaller(string $pdf): ?array {
	if (!fractal_zip_pdf_jpx_pac_enabled()) {
		return null;
	}
	if (fractal_zip_pdf_jpx_opj_decompress_bin() === null || fractal_zip_pdf_jpx_opj_compress_bin() === null) {
		return null;
	}
	$n0 = strlen($pdf);
	if ($n0 < 64 || !str_starts_with($pdf, '%PDF-') || $n0 > 128 * 1024 * 1024) {
		return null;
	}
	$nw = fractal_zip_pdf_pac_recompress_jpx_all_passes($pdf);
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
