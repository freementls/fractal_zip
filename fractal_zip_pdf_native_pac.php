<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_objects.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_stream_markers.php';

/**
 * Native (no qpdf) PDF: recompress /FlateDecode stream bodies with zlib-9 (wrapper) or raw DEFLATE-9
 * to match the original format when PHP’s L9 recompression is strictly smaller (same decompressed
 * bytes). We intentionally do not run Zopfli or other extra compressors on Flate here: the design is
 * to keep literal-PAC light and let outer bundle- and FZ-style transforms work on a coherent
 * decompressed/whole-stream picture without stacking another ratio-seeking DEFLATE pass.
 * FRACTAL_ZIP_LITERALPAC_PDF_NATIVE=0 disables.
 *
 * @package fractal_zip_pdf_native_pac
 */

function fractal_zip_pdf_native_pac_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERALPAC_PDF_NATIVE');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_pdf_pac_flate_inflate(string $b): ?string {
	if ($b === '') {
		return '';
	}
	$u = @gzuncompress($b);
	if (is_string($u) && $u !== false) {
		return $u;
	}
	$g = @gzinflate($b);
	if (is_string($g) && $g !== false) {
		return $g;
	}
	return null;
}

/**
 * @return array{0: string, 1: bool}|null [inflated, asZlibWrapper]
 */
function fractal_zip_pdf_pac_flate_inflate_with_wrapper(string $b): ?array {
	if ($b === '') {
		return ['', true];
	}
	$u = @gzuncompress($b);
	if (is_string($u) && $u !== false) {
		return [$u, true];
	}
	$g = @gzinflate($b);
	if (is_string($g) && $g !== false) {
		return [$g, false];
	}
	return null;
}

function fractal_zip_pdf_pac_flate_repack_php(
	string $infl,
	bool $asZlib,
	int $level = 9
): ?string {
	$level = max(-1, min(9, $level));
	if ( $asZlib) {
		$c = @gzcompress( $infl, $level);
	} else {
		$c = @gzdeflate( $infl, $level, ZLIB_ENCODING_RAW);
	}
	if ( $c === false) {
		return null;
	}
	return (string) $c;
}

/**
 * PHP L9 re-encoding of $infl in the same wrapper as $oldEnc (zlib vs raw), smaller than $oldEnc, or null.
 */
function fractal_zip_pdf_pac_flate_best_repack(string $infl, bool $asZlib, string $oldEnc): ?string {
	$php = fractal_zip_pdf_pac_flate_repack_php($infl, $asZlib, 9);
	if (!is_string($php)) {
		return null;
	}
	$verify = $asZlib ? @gzuncompress($php) : @gzinflate($php);
	if (!is_string($verify) || $verify !== $infl) {
		return null;
	}
	if (strlen($php) >= strlen($oldEnc)) {
		return null;
	}
	return $php;
}

function fractal_zip_pdf_pac_dict_flate_fl_only(string $dict): bool {
	$dl = strlen($dict);
	$fc = 0;
	$fp = 0;
	while ($fp < $dl) {
		$p = strpos($dict, '/Filter', $fp);
		if ($p === false) {
			break;
		}
		$fc++;
		if ($fc > 1) {
			return false;
		}
		$fp = $p + 7;
	}
	if ($fc !== 1) {
		return false;
	}
	if (str_contains($dict, '/DCTDecode') || str_contains($dict, '/JPXDecode') || str_contains($dict, '/JBIG2Decode') || str_contains($dict, '/CCITTFaxDecode')) {
		return false;
	}
	$p = strpos($dict, '/Filter');
	if ($p === false) {
		return false;
	}
	$i = $p + 7;
	if ($i < $dl) {
		$i += strspn($dict, "\0\t\n\f\r ", $i, $dl - $i);
	}
	if ($i >= $dl) {
		return false;
	}
	if ($dict[$i] === '/') {
		return $i + 12 <= $dl
			&& $dict[$i + 1] === 'F' && $dict[$i + 2] === 'l' && $dict[$i + 3] === 'a' && $dict[$i + 4] === 't' && $dict[$i + 5] === 'e'
			&& $dict[$i + 6] === 'D' && $dict[$i + 7] === 'e' && $dict[$i + 8] === 'c' && $dict[$i + 9] === 'o' && $dict[$i + 10] === 'd' && $dict[$i + 11] === 'e';
	}
	if ($dict[$i] !== '[') {
		return false;
	}
	$i++;
	if ($i < $dl) {
		$i += strspn($dict, "\0\t\n\f\r ", $i, $dl - $i);
	}
	if ($i < $dl && $dict[$i] === '/') {
		$i++;
	}
	if ($i + 12 > $dl) {
		return false;
	}
	if (substr_compare($dict, 'FlateDecode', $i, 12) !== 0) {
		return false;
	}
	$i += 12;
	if ($i < $dl) {
		$i += strspn($dict, "\0\t\n\f\r ", $i, $dl - $i);
	}
	return $i < $dl && $dict[$i] === ']';
}

function fractal_zip_pdf_pac_length_direct_int(string $dict): ?int {
	$dl = strlen($dict);
	$p = strpos($dict, '/Length');
	if ($p === false) {
		return null;
	}
	$i = $p + 7;
	if ($i < $dl) {
		$i += strspn($dict, "\0\t\n\f\r ", $i, $dl - $i);
	}
	if ($i >= $dl || $dict[$i] < '0' || $dict[$i] > '9') {
		return null;
	}
	$start = $i;
	while ($i < $dl && $dict[$i] >= '0' && $dict[$i] <= '9' && ($i - $start) < 15) {
		$i++;
	}
	$j = $i;
	if ($j < $dl) {
		$j += strspn($dict, "\0\t\n\f\r ", $j, $dl - $j);
	}
	// Indirect `/Length n g R` — reject (same as legacy regex that returned null).
	if ($j < $dl && $dict[$j] >= '0' && $dict[$j] <= '9') {
		return null;
	}
	$n = (int) substr($dict, $start, $i - $start);
	return $n > 0 ? $n : null;
}

/**
 * Replace the first direct `/Length <digits>` span (same semantics as the old single preg_replace).
 *
 * @return array{0: string, 1: bool} [newDict, ok]
 */
function fractal_zip_pdf_pac_dict_replace_direct_length(string $dict, int $newLen): array {
	$dl = strlen($dict);
	$p = strpos($dict, '/Length');
	if ($p === false) {
		return array($dict, false);
	}
	$i = $p + 7;
	if ($i < $dl) {
		$i += strspn($dict, "\0\t\n\f\r ", $i, $dl - $i);
	}
	if ($i >= $dl || $dict[$i] < '0' || $dict[$i] > '9') {
		return array($dict, false);
	}
	$startDig = $i;
	while ($i < $dl && $dict[$i] >= '0' && $dict[$i] <= '9' && ($i - $startDig) < 15) {
		$i++;
	}
	if ($i === $startDig) {
		return array($dict, false);
	}
	$ns = (string) $newLen;
	$out = substr($dict, 0, $startDig) . $ns . substr($dict, $i);
	return array($out, true);
}

function fractal_zip_pdf_pac_starts_with_endstream_after_ws(string $s): bool {
	$n = strlen($s);
	$i = $n > 0 ? strspn($s, "\0\t\n\f\r ", 0, $n) : 0;
	if ($i + 9 > $n) {
		return false;
	}
	if ($s[$i] !== 'e' || $s[$i + 1] !== 'n' || $s[$i + 2] !== 'd' || $s[$i + 3] !== 's' || $s[$i + 4] !== 't' || $s[$i + 5] !== 'r' || $s[$i + 6] !== 'e' || $s[$i + 7] !== 'a' || $s[$i + 8] !== 'm') {
		return false;
	}
	$j = $i + 9;
	if ($j >= $n) {
		return true;
	}
	$next = ord($s[$j]);
	// Word boundary for "endstream".
	return !(
		($next >= 48 && $next <= 57) ||
		($next >= 65 && $next <= 90) ||
		($next >= 97 && $next <= 122) ||
		$next === 95
	);
}

/**
 * @return string|null new PDF, or null if no stream was improved
 */
function fractal_zip_pdf_pac_recompress_flate_all_passes(string $pdf, int $maxRounds = 32): ?string {
	$work = $pdf;
	$any = false;
	$decodeWrapMemo = [];
	$repackMemo = [];
	$memoMaxEntries = 512;
	for ($round = 0; $round < $maxRounds; $round++) {
		$ch = false;
		$objMap = fractal_zip_pdf_object_index_offsets( $work);
		$M0 = fractal_zip_pdf_pac_stream_token_offsets($work);
		$nm = count($M0);
		if ($nm < 1) {
			break;
		}
		$n0 = strlen($work);
		for ($idx = $nm - 1; $idx >= 0; $idx--) {
			$e = $M0[$idx] ?? null;
			if (! is_array($e) || $e[1] < 0) {
				continue;
			}
			$abs0 = (int) $e[1];
			$tok = (string) $e[0];
			$closeFirstGT = $abs0;
			$open = fractal_zip_pdf_stream_dict_opening_lt_lt($work, $closeFirstGT);
			if ($open < 0) {
				continue;
			}
			$dict = (string) substr($work, $open, $closeFirstGT - $open + 2);
			$dataStart = $abs0 + strlen($tok);
			$head = (string) substr($work, 0, $open);
			$lineRest = (string) substr($work, $closeFirstGT + 2, $dataStart - ($closeFirstGT + 2));
			if (! fractal_zip_pdf_pac_dict_flate_fl_only($dict)) {
				continue;
			}
			$lenB = fractal_zip_pdf_dict_length_value( $work, $dict, $objMap);
			if ($lenB === null) {
				continue;
			}
			$oldEnc = (string) substr($work, $dataStart, $lenB);
			if (strlen($oldEnc) < $lenB) {
				continue;
			}
			$n0 = strlen($work);
			$tailL = (int) min(64, max(0, $n0 - $dataStart - $lenB));
			$check = (string) substr($work, $dataStart + $lenB, $tailL);
			if (!fractal_zip_pdf_pac_starts_with_endstream_after_ws($check)) {
				continue;
			}
			$memoKey = strlen($oldEnc) . '|' . md5($oldEnc, true);
			if (array_key_exists($memoKey, $decodeWrapMemo)) {
				$inflWrap = $decodeWrapMemo[$memoKey];
			} else {
				$inflWrap = fractal_zip_pdf_pac_flate_inflate_with_wrapper($oldEnc);
				$decodeWrapMemo[$memoKey] = $inflWrap;
				if (count($decodeWrapMemo) > $memoMaxEntries) {
					unset($decodeWrapMemo[array_key_first($decodeWrapMemo)]);
				}
			}
			if ($inflWrap === null) {
				continue;
			}
			$infl = $inflWrap[0];
			$asZlib = $inflWrap[1];
			$repackKey = ($asZlib ? 'z' : 'r') . '|' . $memoKey;
			if (array_key_exists($repackKey, $repackMemo)) {
				$nb = $repackMemo[$repackKey];
			} else {
				$nb = fractal_zip_pdf_pac_flate_best_repack($infl, $asZlib, $oldEnc);
				$repackMemo[$repackKey] = $nb;
				if (count($repackMemo) > $memoMaxEntries) {
					unset($repackMemo[array_key_first($repackMemo)]);
				}
			}
			if ($nb === null) {
				continue;
			}
			$dictRep = fractal_zip_pdf_pac_dict_replace_direct_length($dict, strlen($nb));
			$dict2 = $dictRep[0];
			if (!$dictRep[1]) {
				continue;
			}
			$suffix = (string) substr($work, $dataStart + $lenB);
			$work = $head . $dict2 . $lineRest . $nb . $suffix;
			$ch = true;
			$any = true;
		}
		if (! $ch) {
			break;
		}
	}
	return $any ? $work : null;
}

/**
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_pdf_native_pac_recompress_flate_smaller(string $pdf): ?array {
	if (! fractal_zip_pdf_native_pac_enabled()) {
		return null;
	}
	$n0 = strlen($pdf);
	if ($n0 < 32 || ! str_starts_with($pdf, '%PDF-')) {
		return null;
	}
	if ($n0 > 128 * 1024 * 1024) {
		return null;
	}
	$new = fractal_zip_pdf_pac_recompress_flate_all_passes($pdf);
	if ($new === null) {
		return null;
	}
	$save = $n0 - strlen($new);
	if ($save <= 0 || ! str_starts_with($new, '%PDF-')) {
		return null;
	}
	return array($new, $save);
}
