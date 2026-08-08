<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';

/**
 * PDF object index + indirect /Length n 0 R as a small integer (typical for stream Length).
 */

/**
 * Try `~\\b(\\d{1,9})\\s+0\\s+obj\\b~` at $p0 (PCRE \\s = TAB/LF/VT/FF/CR/SPACE; ASCII \\b).
 *
 * @return array{0: int, 1: int, 2: int}|null [objectNumber, startOffsetOfDigits, endPastObj]
 */
function fractal_zip_pdf_scan_obj_header_at(string $pdf, int $n, int $p0): ?array {
	if ($p0 < 0 || $p0 >= $n) {
		return null;
	}
	if ($p0 > 0 && fractal_zip_pdf_dict_byte_is_ascii_word_char(ord($pdf[$p0 - 1]))) {
		return null;
	}
	if (ord($pdf[$p0]) < 48 || ord($pdf[$p0]) > 57) {
		return null;
	}
	$p = $p0;
	$dig = 0;
	$on = 0;
	while ($p < $n && $dig < 9) {
		$co = ord($pdf[$p]);
		if ($co < 48 || $co > 57) {
			break;
		}
		$on = $on * 10 + ($co - 48);
		$dig++;
		$p++;
	}
	if ($dig < 1) {
		return null;
	}
	if ($p < $n && $pdf[$p] >= '0' && $pdf[$p] <= '9') {
		return null;
	}
	if ($p < $n && fractal_zip_pdf_dict_ws_byte_preg_s(ord($pdf[$p])) === false) {
		return null;
	}
	$p = fractal_zip_pdf_dict_skip_ws_preg_s($pdf, $p, $n);
	if ($p >= $n || $pdf[$p] !== '0') {
		return null;
	}
	$p++;
	if ($p >= $n || fractal_zip_pdf_dict_ws_byte_preg_s(ord($pdf[$p])) === false) {
		return null;
	}
	$p = fractal_zip_pdf_dict_skip_ws_preg_s($pdf, $p, $n);
	if ($p + 2 >= $n || $pdf[$p] !== 'o' || $pdf[$p + 1] !== 'b' || $pdf[$p + 2] !== 'j') {
		return null;
	}
	$end = $p + 3;
	if ($end < $n && fractal_zip_pdf_dict_byte_is_ascii_word_char(ord($pdf[$end]))) {
		return null;
	}
	return array($on, $p0, $end);
}

/**
 * @return array<int, int> object number => byte offset of first digit of "N 0 obj" (same as preg PREG_OFFSET_CAPTURE group 0)
 */
function fractal_zip_pdf_object_index_offsets(string $pdf): array {
	$map = array();
	$n = strlen($pdf);
	$p = 0;
	$digits = '0123456789';
	while ($p < $n) {
		$skip = strcspn($pdf, $digits, $p, $n - $p);
		$p += $skip;
		if ($p >= $n) {
			break;
		}
		if ($p > 0 && fractal_zip_pdf_dict_byte_is_ascii_word_char(ord($pdf[$p - 1]))) {
			$p++;
			continue;
		}
		$t = fractal_zip_pdf_scan_obj_header_at($pdf, $n, $p);
		if ($t !== null) {
			$map[(int) $t[0]] = (int) $t[1];
			$p = (int) $t[2];
			continue;
		}
		$p++;
	}
	return $map;
}

/**
 * Same truth as anchored preg_match on optional PCRE \\s, object number, \\s+, 0, \\s+, obj, optional \\s (preg_s bytes).
 */
function fractal_zip_pdf_body_prefix_obj_n_zero_obj(string $body, int $objN): bool {
	$n = strlen($body);
	$p = fractal_zip_pdf_dict_skip_ws_preg_s($body, 0, $n);
	$expect = (string) (int) $objN;
	$le = strlen($expect);
	if ($p + $le > $n || substr_compare($body, $expect, $p, $le) !== 0) {
		return false;
	}
	$p += $le;
	if ($p >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($body[$p]))) {
		return false;
	}
	$p = fractal_zip_pdf_dict_skip_ws_preg_s($body, $p, $n);
	if ($p >= $n || $body[$p] !== '0') {
		return false;
	}
	$p++;
	if ($p >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($body[$p]))) {
		return false;
	}
	$p = fractal_zip_pdf_dict_skip_ws_preg_s($body, $p, $n);
	return $p + 2 < $n && $body[$p] === 'o' && $body[$p + 1] === 'b' && $body[$p + 2] === 'j';
}

/**
 * After obj + optional preg_s whitespace: read 1–15 digits with greedy backtrack so ASCII word-boundary holds after the last digit.
 *
 * @return int|null positive parsed int, or null
 */
function fractal_zip_pdf_try_digits_1_15_trailing_word_boundary(string $s, int $start, int $n): ?int {
	$q = $start;
	while ($q < $n) {
		$o = ord($s[$q]);
		if ($o < 48 || $o > 57) {
			break;
		}
		$q++;
	}
	$total = $q - $start;
	if ($total < 1) {
		return null;
	}
	$maxL = min(15, $total);
	for ($L = $maxL; $L >= 1; $L--) {
		$after = $start + $L;
		if ($after >= $n) {
			return (int) substr($s, $start, $L);
		}
		$o2 = ord($s[$after]);
		if (! fractal_zip_pdf_dict_byte_is_ascii_word_char($o2)) {
			return (int) substr($s, $start, $L);
		}
	}
	return null;
}

/**
 * Leftmost `obj\s*<<\s*>>\s*(\d{1,15})\b` on $body (same as the old single `preg_match`).
 */
function fractal_zip_pdf_read_int_obj_body_empty_shallow_dict(string $body): ?int {
	$n = strlen($body);
	$p = 0;
	while (($p = strpos($body, 'obj', $p)) !== false) {
		if ($p + 3 <= $n && $body[$p] === 'o' && $body[$p + 1] === 'b' && $body[$p + 2] === 'j') {
			$q = fractal_zip_pdf_dict_skip_ws_preg_s($body, $p + 3, $n);
			if ($q + 2 <= $n && $body[$q] === '<' && $body[$q + 1] === '<') {
				$q += 2;
				$q = fractal_zip_pdf_dict_skip_ws_preg_s($body, $q, $n);
				if ($q + 2 <= $n && $body[$q] === '>' && $body[$q + 1] === '>') {
					$q += 2;
					$q = fractal_zip_pdf_dict_skip_ws_preg_s($body, $q, $n);
					$v = fractal_zip_pdf_try_digits_1_15_trailing_word_boundary($body, $q, $n);
					if ($v !== null) {
						return $v;
					}
				}
			}
		}
		$p++;
	}
	return null;
}

/**
 * Leftmost `obj\s*(\d{1,15})\s*endobj` on $s (length $n), same as old `preg_match` on `$body . 'endobj'`.
 */
function fractal_zip_pdf_read_int_obj_body_before_endobj_token(string $s, int $n): ?int {
	$p = 0;
	while (($p = strpos($s, 'obj', $p)) !== false) {
		if ($p + 3 <= $n && $s[$p] === 'o' && $s[$p + 1] === 'b' && $s[$p + 2] === 'j') {
			$q = fractal_zip_pdf_dict_skip_ws_preg_s($s, $p + 3, $n);
			$dig0 = $q;
			while ($q < $n) {
				$o = ord($s[$q]);
				if ($o < 48 || $o > 57) {
					break;
				}
				$q++;
			}
			$total = $q - $dig0;
			if ($total >= 1) {
				$maxL = min(15, $total);
				for ($L = $maxL; $L >= 1; $L--) {
					$r = fractal_zip_pdf_dict_skip_ws_preg_s($s, $dig0 + $L, $n);
					if ($r + 6 <= $n && $s[$r] === 'e' && $s[$r + 1] === 'n' && $s[$r + 2] === 'd' && $s[$r + 3] === 'o' && $s[$r + 4] === 'b' && $s[$r + 5] === 'j') {
						return (int) substr($s, $dig0, $L);
					}
				}
			}
		}
		$p++;
	}
	return null;
}

/**
 * Leftmost `obj\s*(\d{1,15})(?:\s|)` (second alt empty): up to 15 leading digits of the first digit run after `obj`.
 */
function fractal_zip_pdf_read_int_obj_body_loose(string $body): ?int {
	$n = strlen($body);
	$p = 0;
	while (($p = strpos($body, 'obj', $p)) !== false) {
		if ($p + 3 <= $n && $body[$p] === 'o' && $body[$p + 1] === 'b' && $body[$p + 2] === 'j') {
			$q = fractal_zip_pdf_dict_skip_ws_preg_s($body, $p + 3, $n);
			$dig0 = $q;
			while ($q < $n) {
				$o = ord($body[$q]);
				if ($o < 48 || $o > 57) {
					break;
				}
				$q++;
			}
			$run = $q - $dig0;
			if ($run >= 1) {
				return (int) substr($body, $dig0, min(15, $run));
			}
		}
		$p++;
	}
	return null;
}

/**
 * @return int|null
 */
function fractal_zip_pdf_read_int_object_at( string $pdf, int $objN, int $at): ?int {
	$sl = (string) substr( $pdf, $at, 65536);
	$e = stripos( $sl, 'endobj' );
	if ( $e === false) {
		return null;
	}
	$body = (string) substr( $sl, 0, $e);
	if ( ! fractal_zip_pdf_body_prefix_obj_n_zero_obj($body, $objN) ) {
		$p2 = strpos( $sl, (string) $objN . ' 0 obj' );
		if ( $p2 === false) {
			return null;
		}
		$body = (string) substr( $sl, $p2, $e - $p2);
	}
	$v = fractal_zip_pdf_read_int_obj_body_empty_shallow_dict($body);
	if ($v !== null) {
		return $v > 0 ? $v : null;
	}
	$v = fractal_zip_pdf_read_int_obj_body_before_endobj_token($body . 'endobj', strlen($body) + 6);
	if ($v !== null) {
		return $v > 0 ? $v : null;
	}
	$v = fractal_zip_pdf_read_int_obj_body_loose($body);
	if ($v !== null) {
		return $v > 0 ? $v : null;
	}
	return null;
}

/**
 * Direct or indirect /Length; uses /Length n 0 R when present.
 * @param array<int, int>|null $objMap
 * @return int|null
 */
function fractal_zip_pdf_dict_length_value( string $pdf, string $dict, ?array $objMap = null): ?int {
	$ind = fractal_zip_pdf_dict_first_indirect_length_obj_gen($dict);
	if ($ind !== null) {
		$n = (int) $ind[0];
		if ($objMap === null) {
			$objMap = fractal_zip_pdf_object_index_offsets($pdf);
		}
		$at = $objMap[$n] ?? -1;
		if ($at < 0) {
			return null;
		}
		return fractal_zip_pdf_read_int_object_at($pdf, $n, $at);
	}
	$v = fractal_zip_pdf_dict_first_length_unsigned_1_to_15_digits($dict);
	if ($v === null) {
		return null;
	}
	return $v;
}
