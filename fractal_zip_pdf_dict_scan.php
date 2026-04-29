<?php
declare(strict_types=1);
/**
 * Balanced '<<' … '>>' discovery for stream dictionaries (nested '<<' inside a dict is common; avoid non-greedy *? to first '>>').
 */

function fractal_zip_pdf_dict_first_gt_of_close(string $s, int $open): int {
	$L = strlen($s);
	if ($open < 0 || $open + 3 > $L || $s[$open] !== '<' || $s[$open + 1] !== '<') {
		return -1;
	}
	$d = 1;
	$i = $open + 2;
	$maxSteps = 262144;
	$steps = 0;
	while ($d > 0 && $i < $L - 1 && $steps < $maxSteps) {
		$steps++;
		if ($s[$i] === '>' && $i + 1 < $L && $s[$i + 1] === '>') {
			$d--;
			if ($d === 0) {
				return $i;
			}
			$i += 2;
			continue;
		}
		if ($s[$i] === '<' && $i + 1 < $L && $s[$i + 1] === '<') {
			$d++;
			$i += 2;
			continue;
		}
		$i++;
	}
	return -1;
}

/**
 * $closeFirstGT = first '>' of the ">>" before "stream".
 * Smallest $k in (closeFirstGT − $searchMaxBack, closeFirstGT) such that balanced "<<" at $k closes at $closeFirstGT.
 * Default 2 MiB back-search (increase if a producer puts a >2 MiB stream dict before the body).
 * @return int first '<' of the outer "<<" for this stream, or -1
 */
function fractal_zip_pdf_stream_dict_opening_lt_lt(string $s, int $closeFirstGT, int $searchMaxBack = 2097152): int {
	$c = (int) $closeFirstGT;
	$from = max(0, (int) $c - (int) $searchMaxBack);
	$minOpen = 1 << 30;
	$p = $from;
	while ( ($q = strpos( $s, '<<', $p)) !== false && $q < $c) {
		if (fractal_zip_pdf_dict_first_gt_of_close( $s, $q) === $c) {
			if ( $q < $minOpen) {
				$minOpen = $q;
			}
		}
		$p = $q + 1;
	}
	return $minOpen < (1 << 30) ? $minOpen : -1;
}

function fractal_zip_pdf_dict_ws_byte_preg_s(int $c): bool {
	return $c === 9 || $c === 10 || $c === 11 || $c === 12 || $c === 13 || $c === 32;
}

function fractal_zip_pdf_dict_skip_ws_preg_s(string $s, int $i, int $n): int {
	if ($i >= $n) {
		return $i;
	}
	return $i + strspn($s, "\t\n\x0B\f\r ", $i, $n - $i);
}

/**
 * Same truth as preg_match('#/Filter\s*\[#', $dict) === 1 (first /Filter…[ anywhere).
 */
function fractal_zip_pdf_dict_has_filter_array_bracket(string $dict): bool {
	$n = strlen($dict);
	$p = 0;
	$needle = '/Filter';
	$nl = strlen($needle);
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $p + $nl, $n);
		if ($i < $n && $dict[$i] === '[') {
			return true;
		}
		$p++;
	}
	return false;
}

/**
 * Same truth as preg_match('#/Length\s+\d+\s+\d+\s+R#', $dict) === 1 (each gap uses `\s+`, not `\s*`).
 */
function fractal_zip_pdf_dict_has_indirect_length_ref(string $dict): bool {
	$n = strlen($dict);
	$p = 0;
	$needle = '/Length';
	$nl = strlen($needle);
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i0 = $p + $nl;
		if ($i0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$i0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i0, $n);
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p++;
			continue;
		}
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9') {
			$i++;
		}
		$j0 = $i;
		if ($j0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$j0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $j0, $n);
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p++;
			continue;
		}
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9') {
			$i++;
		}
		$k0 = $i;
		if ($k0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$k0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $k0, $n);
		if ($i < $n && $dict[$i] === 'R') {
			return true;
		}
		$p++;
	}
	return false;
}

/**
 * First `#/Length\s+(\d{1,9})\s+(\d{1,5})\s+R#` as `[objectNumber, generation]` (PCRE `\s+` gaps).
 */
function fractal_zip_pdf_dict_first_indirect_length_obj_gen(string $dict): ?array {
	$n = strlen($dict);
	$needle = '/Length';
	$nl = strlen($needle);
	$p = 0;
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i0 = $p + $nl;
		if ($i0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$i0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i0, $n);
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p++;
			continue;
		}
		$on = 0;
		$d = 0;
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9' && $d < 9) {
			$on = $on * 10 + (ord($dict[$i]) - 48);
			$d++;
			$i++;
		}
		if ($d < 1) {
			$p++;
			continue;
		}
		if ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9') {
			$p++;
			continue;
		}
		$j0 = $i;
		if ($j0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$j0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $j0, $n);
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p++;
			continue;
		}
		$gen = 0;
		$d2 = 0;
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9' && $d2 < 5) {
			$gen = $gen * 10 + (ord($dict[$i]) - 48);
			$d2++;
			$i++;
		}
		if ($d2 < 1) {
			$p++;
			continue;
		}
		if ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9') {
			$p++;
			continue;
		}
		$k0 = $i;
		if ($k0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$k0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $k0, $n);
		if ($i >= $n || $dict[$i] !== 'R') {
			$p++;
			continue;
		}
		return array($on, $gen);
	}
	return null;
}

/**
 * Same as preg_match('#/Length\s+(\d{1,15})#', $dict, $m) === 1 capture (first match, greedy digit cap 15).
 */
function fractal_zip_pdf_dict_first_length_unsigned_1_to_15_digits(string $dict): ?int {
	$n = strlen($dict);
	$needle = '/Length';
	$nl = strlen($needle);
	$p = 0;
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i0 = $p + $nl;
		if ($i0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$i0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i0, $n);
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p++;
			continue;
		}
		$val = 0;
		$dig = 0;
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9' && $dig < 15) {
			$val = $val * 10 + (ord($dict[$i]) - 48);
			$dig++;
			$i++;
		}
		if ($dig === 0) {
			$p++;
			continue;
		}
		return $val > 0 ? $val : null;
	}
	return null;
}

/**
 * Byte offset of "/DecodeParms" for the first `#/DecodeParms\s*<<#` match, or null.
 */
function fractal_zip_pdf_dict_find_decode_parms_inline_open_offset(string $dict): ?int {
	$n = strlen($dict);
	$needle = '/DecodeParms';
	$nl = strlen($needle);
	$p = 0;
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $p + $nl, $n);
		if ($i + 1 < $n && $dict[$i] === '<' && $dict[$i + 1] === '<') {
			return $p;
		}
		$p++;
	}
	return null;
}

/**
 * Same truth as preg_match('/\A(\s*endstream\b)/s', $check) === 1 (PHP \s*, ASCII word boundary after endstream).
 */
function fractal_zip_pdf_dict_prefix_endstream_after_preg_s_ws(string $check): bool {
	$n = strlen($check);
	$i = $n > 0 ? strspn($check, "\t\n\x0B\f\r ", 0, $n) : 0;
	if ($i + 9 > $n) {
		return false;
	}
	if ($check[$i] !== 'e' || $check[$i + 1] !== 'n' || $check[$i + 2] !== 'd' || $check[$i + 3] !== 's' || $check[$i + 4] !== 't' || $check[$i + 5] !== 'r' || $check[$i + 6] !== 'e' || $check[$i + 7] !== 'a' || $check[$i + 8] !== 'm') {
		return false;
	}
	$j = $i + 9;
	if ($j >= $n) {
		return true;
	}
	$next = ord($check[$j]);
	return ! (($next >= 48 && $next <= 57) || ($next >= 65 && $next <= 90) || ($next >= 97 && $next <= 122) || $next === 95);
}

function fractal_zip_pdf_dict_byte_is_ascii_word_char(int $o): bool {
	return ($o >= 48 && $o <= 57) || ($o >= 65 && $o <= 90) || ($o >= 97 && $o <= 122) || $o === 95;
}

/**
 * True if some `/Filter` is followed (PCRE `\s*`) by $slashDecode (e.g. `/DCTDecode`). If $trailingWordBoundary, same as `\b` after $slashDecode (ASCII word char rule).
 * Matches left-to-right “any occurrence” like preg_match on these substrings.
 */
function fractal_zip_pdf_dict_has_filter_key_then_slash_name(string $dict, string $slashDecode, bool $trailingWordBoundary): bool {
	$n = strlen($dict);
	$needle = '/Filter';
	$nl = strlen($needle);
	$sl = strlen($slashDecode);
	if ($sl < 1) {
		return false;
	}
	$p = 0;
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $p + $nl, $n);
		if ($i + $sl <= $n && substr_compare($dict, $slashDecode, $i, $sl) === 0) {
			if (! $trailingWordBoundary) {
				return true;
			}
			$j = $i + $sl;
			if ($j >= $n) {
				return true;
			}
			if (! fractal_zip_pdf_dict_byte_is_ascii_word_char(ord($dict[$j]))) {
				return true;
			}
		}
		$p++;
	}
	return false;
}

/**
 * Same truth as preg_match on array-form DCT: `/Filter` … `[` … optional `/` … `DCTDecode` … `]` with PCRE `\s*` gaps.
 */
function fractal_zip_pdf_dict_has_filter_array_dct_decode(string $dict): bool {
	$n = strlen($dict);
	$needle = '/Filter';
	$nl = strlen($needle);
	$p = 0;
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $p + $nl, $n);
		if ($i >= $n || $dict[$i] !== '[') {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i + 1, $n);
		if ($i < $n && $dict[$i] === '/') {
			$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i + 1, $n);
		}
		if ($i + 9 > $n || $dict[$i] !== 'D' || $dict[$i + 1] !== 'C' || $dict[$i + 2] !== 'T' || $dict[$i + 3] !== 'D' || $dict[$i + 4] !== 'e' || $dict[$i + 5] !== 'c' || $dict[$i + 6] !== 'o' || $dict[$i + 7] !== 'd' || $dict[$i + 8] !== 'e') {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i + 9, $n);
		if ($i < $n && $dict[$i] === ']') {
			return true;
		}
		$p++;
	}
	return false;
}

/**
 * First match of /Key + PCRE `\s+` + up to $maxDigits digits + ASCII `\b` (same as `#/Key\s+(\d{1,N})\b#`).
 */
function fractal_zip_pdf_dict_first_slash_key_unsigned_bounded(string $dict, string $slashKey, int $maxDigits): ?int {
	if ($maxDigits < 1) {
		return null;
	}
	$n = strlen($dict);
	$kl = strlen($slashKey);
	if ($kl < 1) {
		return null;
	}
	$p = 0;
	while (($p = strpos($dict, $slashKey, $p)) !== false) {
		$i0 = $p + $kl;
		if ($i0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$i0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i0, $n);
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p++;
			continue;
		}
		$v = 0;
		$d = 0;
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9' && $d < $maxDigits) {
			$v = $v * 10 + (ord($dict[$i]) - 48);
			$d++;
			$i++;
		}
		if ($d < 1) {
			$p++;
			continue;
		}
		if ($i < $n && fractal_zip_pdf_dict_byte_is_ascii_word_char(ord($dict[$i]))) {
			$p++;
			continue;
		}
		return $v;
	}
	return null;
}

/**
 * First `#/K\s+(-?\d{1,6})\b#` match (CCITT /K).
 */
function fractal_zip_pdf_dict_first_slash_k_signed_six(string $dict): ?int {
	$n = strlen($dict);
	$key = '/K';
	$kl = strlen($key);
	$p = 0;
	while (($p = strpos($dict, $key, $p)) !== false) {
		$i0 = $p + $kl;
		if ($i0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$i0]))) {
			$p++;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i0, $n);
		$neg = false;
		if ($i < $n && $dict[$i] === '-') {
			$neg = true;
			$i++;
		}
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p++;
			continue;
		}
		$v = 0;
		$d = 0;
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9' && $d < 6) {
			$v = $v * 10 + (ord($dict[$i]) - 48);
			$d++;
			$i++;
		}
		if ($d < 1) {
			$p++;
			continue;
		}
		if ($i < $n && fractal_zip_pdf_dict_byte_is_ascii_word_char(ord($dict[$i]))) {
			$p++;
			continue;
		}
		return $neg ? -$v : $v;
	}
	return null;
}

/**
 * First `preg_replace('#/Length\s+\d{1,15}#', '/Length ' . $newLen, $dict, 1)` (PCRE `\s` only; normalizes to `/Length ` + digits).
 *
 * @return array{0: string, 1: bool}
 */
function fractal_zip_pdf_dict_replace_first_length_digits_preg_s(string $dict, int $newLen): array {
	$n = strlen($dict);
	$needle = '/Length';
	$nl = strlen($needle);
	$p = 0;
	$ns = '/Length ' . (string) $newLen;
	while ($p < $n) {
		$q = strpos($dict, $needle, $p);
		if ($q === false) {
			return array($dict, false);
		}
		$i0 = $q + $nl;
		if ($i0 >= $n || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($dict[$i0]))) {
			$p = $q + 1;
			continue;
		}
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $i0, $n);
		if ($i >= $n || $dict[$i] < '0' || $dict[$i] > '9') {
			$p = $q + 1;
			continue;
		}
		$startDig = $i;
		while ($i < $n && $dict[$i] >= '0' && $dict[$i] <= '9' && ($i - $startDig) < 15) {
			$i++;
		}
		if ($i === $startDig) {
			$p = $q + 1;
			continue;
		}
		$out = substr($dict, 0, $q) . $ns . substr($dict, $i);
		return array($out, true);
	}
	return array($dict, false);
}

/**
 * Remove every PCRE `\s` byte (TAB/LF/VT/FF/CR/SPACE); same result as `preg_replace('/\s+/', '', $s)` on ASCII.
 */
function fractal_zip_pdf_string_strip_preg_s(string $s): string {
	return str_replace(array("\t", "\n", "\x0B", "\f", "\r", ' '), '', $s);
}

/** Strip to hex digits only (same as `preg_replace('/[^0-9A-Fa-f]/', '', $s)` for ASCII). */
function fractal_zip_pdf_string_strip_non_hex(string $s): string {
	static $nonHex = null;
	if ($nonHex === null) {
		$nonHex = '';
		for ($b = 0; $b < 256; $b++) {
			$c = chr($b);
			if (($c >= '0' && $c <= '9') || ($c >= 'A' && $c <= 'F') || ($c >= 'a' && $c <= 'f')) {
				continue;
			}
			$nonHex .= $c;
		}
	}
	$n = strlen($s);
	if ($n === 0) {
		return '';
	}
	$o = '';
	$i = 0;
	while ($i < $n) {
		$run = strcspn($s, $nonHex, $i);
		if ($run > 0) {
			$o .= substr($s, $i, $run);
			$i += $run;
		}
		if ($i >= $n) {
			break;
		}
		$i += strspn($s, $nonHex, $i);
	}
	return $o;
}
