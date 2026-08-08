<?php
declare(strict_types=1);

/**
 * Walk PDF `stream` … `endstream` (direct /Length), decode /Filter in reverse list order
 * (ISO 32000). Flate with /DecodeParms Predictor (1 none, 2 horizontal TIFF, 10–15 PNG).
 * DCT / JPX: pass-through (raw media bitstream). ASCII85, ASCIIHex, RLE, LZW included for common toolchains.
 * FRACTAL_ZIP_PDF_STREAM_DECOMPRESS_MAX_STREAM_BYTES: max **encoded** stream (default: match FRACTAL_ZIP_LITERALPAC_MAX_DECOMPRESSED_BYTES or 128 MiB; set explicit bytes to cap).
 *
 * @package fractal_zip_pdf_stream_decode
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_objects.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_stream_markers.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_native_pac.php';

function fractal_zip_pdf_stream_decode_max_in_bytes(): int {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_PDF_STREAM_DECOMPRESS_MAX_STREAM_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e) || (int) $e === 0) {
		$e2 = getenv('FRACTAL_ZIP_LITERALPAC_MAX_DECOMPRESSED_BYTES');
		$def = ($e2 !== false && is_numeric($e2) && (int) $e2 > 0) ? (int) $e2 : 128 * 1024 * 1024;
		return $c = $def;
	}
	return $c = min(2147483647, (int) $e);
}

function fractal_zip_pdf_stream_flate_inflate(string $b): ?string {
	return fractal_zip_pdf_pac_flate_inflate($b);
}

function fractal_zip_pdf_filter_name_token_byte(int $o): bool {
	return ($o >= 48 && $o <= 57) || ($o >= 65 && $o <= 90) || ($o >= 97 && $o <= 122) || $o === 35;
}

/**
 * Names inside `/Filter [ ... ]` (same tokens as preg `~/([A-Za-z0-9#]+)~` on the bracket slice).
 *
 * @return list<string>
 */
function fractal_zip_pdf_filter_names_from_array_inner(string $inner): array {
	$out = [];
	$ni = strlen($inner);
	$qi = 0;
	while (($qi = strpos($inner, '/', $qi)) !== false) {
		$qq = $qi + 1;
		while ($qq < $ni && fractal_zip_pdf_filter_name_token_byte(ord($inner[$qq]))) {
			$qq++;
		}
		if ($qq > $qi + 1) {
			$out[] = (string) substr($inner, $qi + 1, $qq - $qi - 1);
		}
		$qi = max($qi + 1, $qq);
	}
	return $out;
}

/**
 * @return list<string> names without leading /
 */
function fractal_zip_pdf_dict_parse_filter_names(string $dict): array {
	$n = strlen($dict);
	$needle = '/Filter';
	$nl = strlen($needle);
	$p = 0;
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $p + $nl, $n);
		if ($i < $n && $dict[$i] === '[') {
			$rb = strpos($dict, ']', $i + 1);
			if ($rb !== false) {
				return fractal_zip_pdf_filter_names_from_array_inner((string) substr($dict, $i + 1, $rb - $i - 1));
			}
		}
		$p++;
	}
	$p = 0;
	while (($p = strpos($dict, $needle, $p)) !== false) {
		$i = fractal_zip_pdf_dict_skip_ws_preg_s($dict, $p + $nl, $n);
		if ($i < $n && $dict[$i] === '/') {
			$st = $i + 1;
			$q = $st;
			while ($q < $n && fractal_zip_pdf_filter_name_token_byte(ord($dict[$q]))) {
				$q++;
			}
			if ($q > $st) {
				if ($q >= $n || ! fractal_zip_pdf_dict_byte_is_ascii_word_char(ord($dict[$q]))) {
					return array((string) substr($dict, $st, $q - $st));
				}
			}
		}
		$p++;
	}
	return [];
}

function fractal_zip_pdf_dict_length_direct_int(string $dict): ?int {
	if (fractal_zip_pdf_dict_has_indirect_length_ref($dict)) {
		return null;
	}
	$len = fractal_zip_pdf_dict_first_length_unsigned_1_to_15_digits($dict);
	if ($len === null) {
		return null;
	}
	return $len;
}

/**
 * @return list<?array<string, mixed>> parallel to /Filter (index 0 = first name in /Filter array)
 */
function fractal_zip_pdf_dict_parse_decode_parms_list(string $dict, int $nFilters): array {
	$all = array_fill(0, max(0, $nFilters), null);
	if ($nFilters === 0) {
		return $all;
	}
	$one = fractal_zip_pdf_dict_inline_decode_parms($dict);
	if ($nFilters === 1 && is_array($one)) {
		$all[0] = $one;
		return $all;
	}
	$ap = 0;
	$q = strpos($dict, '/DecodeParms');
	if ($q !== false && ($br = strpos($dict, '[', $q + 10)) !== false) {
		$depthB = 1;
		$ld = strlen($dict);
		for ($j = $br + 1; $j < $ld; $j++) {
			$c2 = $dict[$j] ?? '';
			if ($c2 === '[') {
				$depthB++;
				continue;
			}
			if ($c2 === ']') {
				$depthB--;
				if ($depthB === 0) {
					$inner = (string) substr($dict, $br + 1, $j - $br - 1);
					$j2 = 0;
					$l2 = strlen($inner);
					while ($ap < $nFilters && $j2 < $l2) {
						while ($j2 < $l2 && (ctype_space($inner[$j2] ?? ' ') || ($inner[$j2] ?? '') === ',')) {
							$j2++;
						}
						if ($j2 >= $l2) {
							break;
						}
						if ($j2 + 4 <= $l2 && $inner[$j2] === 'n' && $inner[$j2 + 1] === 'u' && $inner[$j2 + 2] === 'l' && $inner[$j2 + 3] === 'l') {
							$all[$ap++] = null;
							$j2 += 4;
							continue;
						}
						if ($j2 + 1 < $l2 && $inner[$j2] === '<' && $inner[$j2 + 1] === '<') {
							$st = $j2;
							$j2 += 2;
							$dep = 1;
							$j3 = $j2;
							for (; $j3 < $l2; $j3++) {
								if ($j3 + 1 < $l2 && $inner[$j3] === '<' && $inner[$j3 + 1] === '<') {
									$dep++;
									$j3++;
									continue;
								}
								if ($j3 + 1 < $l2 && $inner[$j3] === '>' && $inner[$j3 + 1] === '>') {
									$dep--;
									$j3++;
									if ($dep === 0) {
										$all[$ap++] = fractal_zip_pdf_parse_minimal_dict(
											(string) substr($inner, $st + 2, $j3 - 2 - $st - 2)
										);
										$j2 = $j3 + 1;
										break;
									}
								}
							}
						} else {
							$j2++;
						}
					}
					break;
				}
			}
		}
	} elseif (is_array($one)) {
		$all[0] = $one;
	}
	if ($nFilters > 0 && is_array($one) && $all[0] === null) {
		$all[0] = $one;
	}
	return $all;
}

/**
 * @return ?array<string, int|float|string|bool>
 */
function fractal_zip_pdf_dict_inline_decode_parms(string $dict): ?array {
	$pos = fractal_zip_pdf_dict_find_decode_parms_inline_open_offset($dict);
	if ($pos === null) {
		return null;
	}
	$sub = substr($dict, $pos);
	$q = strpos($sub, '<<');
	if ($q === false) {
		return null;
	}
	$start = $q + 2;
	$depth = 1;
	$lenS = strlen($sub);
	for ($i = $q + 2; $i < $lenS; $i++) {
		$c = $sub[$i];
		if ($c === '<' && $i + 1 < $lenS && $sub[$i + 1] === '<') {
			$depth++;
			$i++;
			continue;
		}
		if ($c === '>' && $i + 1 < $lenS && $sub[$i + 1] === '>') {
			$depth--;
			$i++;
			if ($depth === 0) {
				$body = (string) substr($sub, $start, $i - $start - 1);
				return fractal_zip_pdf_parse_minimal_dict($body);
			}
		}
	}
	return null;
}

function fractal_zip_pdf_minimal_dict_value_is_int_str(string $v): bool {
	$ln = strlen($v);
	if ($ln === 0) {
		return false;
	}
	$i = 0;
	if ($v[0] === '-') {
		if ($ln === 1) {
			return false;
		}
		$i = 1;
	}
	for (; $i < $ln; $i++) {
		$o = ord($v[$i]);
		if ($o < 48 || $o > 57) {
			return false;
		}
	}
	return true;
}

/**
 * Same truth as `^(-?\d+\.?\d*)$` for ASCII (DecodeParms numeric values).
 */
function fractal_zip_pdf_minimal_dict_value_is_float_str(string $v): bool {
	$ln = strlen($v);
	if ($ln === 0 || $v === '-') {
		return false;
	}
	$i = 0;
	if ($v[0] === '-') {
		if ($ln === 1) {
			return false;
		}
		$i = 1;
	}
	$digits = 0;
	for (; $i < $ln; $i++) {
		$o = ord($v[$i]);
		if ($o < 48 || $o > 57) {
			break;
		}
		$digits++;
	}
	if ($digits < 1) {
		return false;
	}
	if ($i >= $ln) {
		return true;
	}
	if ($v[$i] !== '.') {
		return false;
	}
	$i++;
	for (; $i < $ln; $i++) {
		$o = ord($v[$i]);
		if ($o < 48 || $o > 57) {
			return false;
		}
	}
	return true;
}

function fractal_zip_pdf_string_is_ascii_bytes(string $s): bool {
	static $hi = null;
	if ($hi === null) {
		$hi = '';
		for ($b = 128; $b < 256; $b++) {
			$hi .= chr($b);
		}
	}
	return strpbrk($s, $hi) === false;
}

/**
 * Same pairs as `preg_match_all('~/([A-Za-z0-9#]+)\s+([^\s/][^\s/]*)~u', ' ' . $inner . ' ', …)` when $inner has no bytes > 127 (PCRE `\s` on such input is TAB/LF/VT/FF/CR/SPACE only; `~` avoids `#` inside the class clashing with a `#` delimiter).
 *
 * @return array<string, int|float|string|bool>
 */
function fractal_zip_pdf_parse_minimal_dict_ascii_padded(string $s): array {
	$map = [];
	$L = strlen($s);
	$i = 0;
	while ($i < $L) {
		if ($s[$i] !== '/') {
			$i++;
			continue;
		}
		$keyStart = $i + 1;
		$j = $keyStart;
		while ($j < $L && fractal_zip_pdf_filter_name_token_byte(ord($s[$j]))) {
			$j++;
		}
		if ($j === $keyStart) {
			$i++;
			continue;
		}
		$key = (string) substr($s, $keyStart, $j - $keyStart);
		if ($j >= $L || ! fractal_zip_pdf_dict_ws_byte_preg_s(ord($s[$j]))) {
			$i++;
			continue;
		}
		if ($j < $L) {
			$j += strspn($s, "\t\n\x0B\f\r ", $j, $L - $j);
		}
		if ($j >= $L) {
			break;
		}
		if ($s[$j] === '/' || fractal_zip_pdf_dict_ws_byte_preg_s(ord($s[$j]))) {
			$i++;
			continue;
		}
		$v0 = $j;
		$j++;
		if ($j < $L) {
			$j += strcspn($s, "\t\n\x0B\f\r /", $j, $L - $j);
		}
		$v = trim((string) substr($s, $v0, $j - $v0), " \r\n");
		if (str_starts_with($v, '<<') || $v === 'null') {
			$map[$key] = $v;
		} elseif (fractal_zip_pdf_minimal_dict_value_is_int_str($v)) {
			$map[$key] = (int) $v;
		} elseif (fractal_zip_pdf_minimal_dict_value_is_float_str($v)) {
			$map[$key] = (float) $v;
		} elseif (strtolower($v) === 'true' || strtolower($v) === 'false') {
			$map[$key] = strtolower($v) === 'true';
		} else {
			$map[$key] = $v;
		}
		$i = $j;
	}
	return $map;
}

/**
 * @return array<string, int|float|string|bool>
 */
function fractal_zip_pdf_parse_minimal_dict(string $inner): array {
	$map = [];
	$padded = ' ' . $inner . ' ';
	if (fractal_zip_pdf_string_is_ascii_bytes($inner)) {
		return fractal_zip_pdf_parse_minimal_dict_ascii_padded($padded);
	}
	if (preg_match_all('~/([A-Za-z0-9#]+)\s+([^\s/][^\s/]*)~u', $padded, $M, PREG_SET_ORDER) > 0) {
		foreach ($M as $r) {
			$k = $r[1];
			$v = trim($r[2], " \r\n");
			if (str_starts_with($v, '<<') || $v === 'null') {
				$map[$k] = $v;
			} elseif (fractal_zip_pdf_minimal_dict_value_is_int_str($v)) {
				$map[$k] = (int) $v;
			} elseif (fractal_zip_pdf_minimal_dict_value_is_float_str($v)) {
				$map[$k] = (float) $v;
			} elseif (strtolower($v) === 'true' || strtolower($v) === 'false') {
				$map[$k] = strtolower($v) === 'true';
			} else {
				$map[$k] = $v;
			}
		}
	}
	return $map;
}

/**
 * @param list<string> $filterNames
 * @param list<?array<string, mixed>> $decodeParms
 */
function fractal_zip_pdf_filter_chain_decode(string $encoded, array $filterNames, array $decodeParms): ?string {
	$n = count($filterNames);
	if ($n === 0) {
		return $encoded;
	}
	$data = $encoded;
	for ($i = $n - 1; $i >= 0; $i--) {
		$dp = (isset($decodeParms[$i]) && is_array($decodeParms[$i])) ? $decodeParms[$i] : null;
		$next = fractal_zip_pdf_filter_decode_one($filterNames[$i], $data, $dp);
		if ($next === null) {
			return null;
		}
		$data = $next;
	}
	return $data;
}

/**
 * @param array<string, mixed>|null $dp
 */
function fractal_zip_pdf_filter_decode_one(string $filterName, string $data, ?array $dp): ?string {
	$fn = $filterName;
	$fl = ltrim($fn, '/');
	switch (str_replace('#2C', ',', $fl)) {
		case 'Flate':
		case 'FlateDecode':
			$u = fractal_zip_pdf_stream_flate_inflate($data);
			if ($u === null) {
				return null;
			}
			$pr = 1;
			if (is_array($dp) && isset($dp['Predictor'])) {
				$pr = (int) $dp['Predictor'];
			}
			if ($pr === 0 || $pr === 1) {
				return $u;
			}
			if ($pr === 2) {
				return fractal_zip_pdf_tiff2_horizontal_reconstruct($u, is_array($dp) ? $dp : []);
			}
			if ($pr >= 10 && $pr <= 15) {
				return fractal_zip_pdf_predictor_png_rows($u, is_array($dp) ? $dp : []);
			}
			return null;
		case 'DCT':
		case 'DCTDecode':
		case 'JPX':
		case 'JPXDecode':
		case 'CCITTFax':
		case 'CCITTFaxDecode':
		case 'JBIG2':
		case 'JBIG2Decode':
			return $data;
		case 'ASCII85':
		case 'ASCII85Decode':
			$r = fractal_zip_pdf_decode_ascii85($data);
			return $r;
		case 'ASCIIHex':
		case 'ASCIIHexDecode':
			return fractal_zip_pdf_decode_asciihex($data);
		case 'RunLength':
		case 'RunLengthDecode':
			return fractal_zip_pdf_decode_rle($data);
		case 'LZW':
		case 'LZWDecode':
			return fractal_zip_pdf_decode_lzw($data, is_array($dp) ? $dp : []);
		default:
			return null;
	}
}

/**
 * @param array<string, int|float> $p use Columns = samples per row, BitsPerComponent, Colors
 */
function fractal_zip_pdf_tiff2_horizontal_reconstruct(string $d, array $p): ?string {
	$cols = (int) ($p['Columns'] ?? 0);
	$bits = (int) ($p['BitsPerComponent'] ?? 8);
	$colors = (int) ($p['Colors'] ?? 1);
	$bpr = (int) ceil(($cols * $bits * $colors) / 8.0);
	if ($bpr <= 0) {
		return $d;
	}
	$len = strlen($d);
	if ($len % $bpr !== 0) {
		return null;
	}
	$rows = (int) ($len / $bpr);
	$o = $d;
	for ($r = 0; $r < $rows; $r++) {
		$base = $r * $bpr;
		for ($i = 1; $i < $bpr; $i++) {
			$j = $base + $i;
			$prev = ord($o[$j - 1]);
			$o[$j] = (string) chr( (ord($o[$j]) + $prev) & 255);
		}
	}
	return $o;
}

/**
 * PNG-style rows with leading filter type byte (PDF Predictor 10–15, typically 15).
 * @param array<string, int|float> $p
 */
function fractal_zip_pdf_predictor_png_rows(string $d, array $p): ?string {
	$bits = (int) ($p['BitsPerComponent'] ?? 8);
	$colors = (int) ($p['Colors'] ?? 1);
	$colSamples = (int) ($p['Columns'] ?? 0);
	if ($colSamples <= 0) {
		return null;
	}
	$totalBitsPerRow = $colSamples * $bits * $colors;
	$bpr = (int) (($totalBitsPerRow + 7) >> 3);
	if ($bpr <= 0) {
		return null;
	}
	$totalBitsPerPixel = $bits * $colors;
	$bpp = (int) (($totalBitsPerPixel + 7) >> 3);
	if ($bpp < 1) {
		$bpp = 1;
	}
	$len = strlen($d);
	$rowB = 1 + $bpr;
	if ($len < $rowB || $len % $rowB !== 0) {
		return null;
	}
	$h = (int) ($len / $rowB);
	$out = '';
	$prior = str_repeat("\0", $bpr);
	for ($r = 0; $r < $h; $r++) {
		$off = $r * $rowB;
		$ft = ord($d[$off]);
		$row = fractal_zip_pdf_png_unfilter_row_from_data($ft, $d, $off + 1, $bpr, $bpp, $prior);
		$out .= $row;
		$prior = $row;
	}
	return $out;
}

function fractal_zip_pdf_png_unfilter_row_from_data(int $ft, string $src, int $srcOff, int $bpr, int $bpp, string $prior): string {
	$recon = str_repeat("\0", $bpr);
	if ($ft === 0) {
		$j = $srcOff;
		for ($i = 0; $i < $bpr; $i++, $j++) {
			$recon[$i] = $src[$j];
		}
		return $recon;
	}
	if ($ft === 1) {
		$j = $srcOff;
		for ($i = 0; $i < $bpr; $i++, $j++) {
			$left = $i >= $bpp ? ord($recon[$i - $bpp]) : 0;
			$x = ord($src[$j]);
			$recon[$i] = (string) chr(($x + $left) & 255);
		}
		return (string) $recon;
	}
	if ($ft === 2) {
		$j = $srcOff;
		for ($i = 0; $i < $bpr; $i++, $j++) {
			$up = ord($prior[$i]);
			$x = ord($src[$j]);
			$recon[$i] = (string) chr(($x + $up) & 255);
		}
		return (string) $recon;
	}
	if ($ft === 3) {
		$j = $srcOff;
		for ($i = 0; $i < $bpr; $i++, $j++) {
			$left = $i >= $bpp ? ord($recon[$i - $bpp]) : 0;
			$up = ord($prior[$i]);
			$x = ord($src[$j]);
			$recon[$i] = (string) chr(($x + (($left + $up) >> 1)) & 255);
		}
		return (string) $recon;
	}
	if ($ft === 4) {
		$j = $srcOff;
		for ($i = 0; $i < $bpr; $i++, $j++) {
			$left = $i >= $bpp ? ord($recon[$i - $bpp]) : 0;
			$up = ord($prior[$i]);
			$ul2 = $i >= $bpp ? ord($prior[$i - $bpp]) : 0;
			$x = ord($src[$j]);
			$p2 = (int) ($left + $up - $ul2);
			$pa = $p2 - $left;
			if ($pa < 0) {
				$pa = -$pa;
			}
			$pb = $p2 - $up;
			if ($pb < 0) {
				$pb = -$pb;
			}
			$pc = $p2 - $ul2;
			if ($pc < 0) {
				$pc = -$pc;
			}
			$pr = $left;
			if ($pa <= $pb && $pa <= $pc) {
				$pr = $left;
			} elseif ($pb <= $pc) {
				$pr = $up;
			} else {
				$pr = $ul2;
			}
			$recon[$i] = (string) chr(($x + $pr) & 255);
		}
		return (string) $recon;
	}
	$j = $srcOff;
	for ($i = 0; $i < $bpr; $i++, $j++) {
		$recon[$i] = $src[$j];
	}
	return (string) $recon;
}

function fractal_zip_pdf_png_unfilter_row(int $ft, string $line, int $bpr, int $bpp, string $prior): string {
	return fractal_zip_pdf_png_unfilter_row_from_data($ft, $line, 0, $bpr, $bpp, $prior);
}

function fractal_zip_pdf_decode_ascii85(string $s): ?string {
	$s = fractal_zip_pdf_string_strip_preg_s($s);
	$end = strpos($s, '~>');
	if ($end !== false) {
		$s = (string) substr($s, 0, $end);
	}
	$out = '';
	$len = strlen($s);
	$block = 0;
	$buf = array(0, 0, 0, 0, 0);
	for ($i = 0; $i < $len; $i++) {
		$c = $s[$i] ?? 'z';
		if ($c === 'z' && $block === 0) {
			$out .= "\0\0\0\0";
			continue;
		}
		if ($c < '!' || $c > 'u') {
			return null;
		}
		$buf[$block++] = ord($c) - 33;
		if ($block === 5) {
			$v = ($buf[0] * 85 * 85 * 85 * 85) + ($buf[1] * 85 * 85 * 85) + ($buf[2] * 85 * 85) + ($buf[3] * 85) + $buf[4];
			$out .= chr( ($v >> 24) & 255) . chr( ($v >> 16) & 255) . chr( ($v >> 8) & 255) . chr($v & 255);
			$block = 0;
		}
	}
	if ($block === 1) {
		return null;
	}
	if ($block > 1) {
		$origB = $block;
		while ($block < 5) {
			$buf[$block++] = 84;
		}
		$v = ($buf[0] * 85 * 85 * 85 * 85) + ($buf[1] * 85 * 85 * 85) + ($buf[2] * 85 * 85) + ($buf[3] * 85) + $buf[4];
		$bytes = array( ($v >> 24) & 255, ($v >> 16) & 255, ($v >> 8) & 255, $v & 255);
		$nout = $origB - 1;
		$out .= pack('C*', ...array_slice($bytes, 0, $nout));
	}
	return $out;
}

function fractal_zip_pdf_decode_asciihex(string $s): ?string {
	$s2 = fractal_zip_pdf_string_strip_preg_s($s);
	$e = strpos($s2, '>');
	if ($e !== false) {
		$s2 = substr($s2, 0, $e);
	}
	$s2 = fractal_zip_pdf_string_strip_non_hex($s2);
	if (strlen($s2) % 2 === 1) {
		$s2 .= '0';
	}
	$out = (string) @pack('H*', $s2);
	if ($s2 === '') {
		return $out;
	}
	return $out;
}

function fractal_zip_pdf_decode_rle(string $s): ?string {
	$n = strlen($s);
	$o = '';
	$pos = 0;
	for (; $pos < $n; ) {
		$b = ord($s[$pos]);
		if ($b < 0x80) {
			$l = (int) $b + 1;
			$pos++;
			if ($pos + $l > $n) {
				return null;
			}
			$o .= (string) substr($s, $pos, $l);
			$pos += $l;
			continue;
		}
		if ($b === 0x80) {
			$pos = $n;
			break;
		}
		$run = 257 - $b;
		$pos++;
		if ($pos >= $n) {
			return null;
		}
		$ch = $s[$pos];
		$pos++;
		$o .= str_repeat($ch, $run);
	}
	return $o;
}

/**
 * PDF LZW (PostScript) not implemented: stream would need bit-order + EarlyChange handling. Returns null if non-empty.
 * @param array<string, mixed> $dp
 */
function fractal_zip_pdf_decode_lzw(string $s, array $dp): ?string {
	if ($s === '') {
		return '';
	}
	(void) $dp;
	return null;
}

function fractal_zip_pdf_max_decoded_stream_bytes_effective(): int {
	$e = getenv('FRACTAL_ZIP_LITERALPAC_MAX_DECOMPRESSED_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 128 * 1024 * 1024;
	}
	$v = (int) $e;
	return $v <= 0 ? 0 : $v;
}

/**
 * @return array{
 *   file_bytes:int,
 *   stream_count:int,
 *   direct_length_count:int,
 *   indirect_length_skips:int,
 *   decoded_ok:int,
 *   decoded_fail:int,
 *   total_raw_bytes:int,
 *   by_filter: array<string, int>,
 *   failures: list<array{offset:int,err:string,filter_s:string}>
 * }
 */
function fractal_zip_pdf_streams_fully_decompress_report(string $pdf, bool $collectErrors = true): array {
	$out = array(
		'file_bytes' => strlen($pdf),
		'stream_count' => 0,
		'direct_length_count' => 0,
		'indirect_length_skips' => 0,
		'decoded_ok' => 0,
		'decoded_fail' => 0,
		'total_raw_bytes' => 0,
		'by_filter' => array(),
		'failures' => array(),
	);
	$maxIn = fractal_zip_pdf_stream_decode_max_in_bytes();
	$maxD = fractal_zip_pdf_max_decoded_stream_bytes_effective();
	$n0 = strlen($pdf);
	$objMap = fractal_zip_pdf_object_index_offsets( $pdf);
	$M0 = fractal_zip_pdf_pac_stream_token_offsets($pdf);
	$nm = count($M0);
	if ($nm < 1) {
		return $out;
	}
	for ($idx = 0; $idx < $nm; $idx++) {
		$e0 = $M0[$idx] ?? null;
		if ( !is_array( $e0) || (int) $e0[1] < 0) {
			continue;
		}
		$out['stream_count']++;
		$abs0 = (int) $e0[1];
		$tok = (string) $e0[0];
		$closeFirstGT = $abs0;
		$open = fractal_zip_pdf_stream_dict_opening_lt_lt( $pdf, $closeFirstGT);
		if ( $open < 0) {
			$out['decoded_fail']++;
			if ( $collectErrors) {
				$out['failures'][] = array( 'offset' => $abs0, 'err' => 'could not locate matching << for stream dict', 'filter_s' => '?' );
			}
			continue;
		}
		$dict = (string) substr( $pdf, $open, $closeFirstGT - $open + 2);
		$dataStart = $abs0 + strlen( $tok);
		$lenB = fractal_zip_pdf_dict_length_value( $pdf, $dict, $objMap);
		if ( $lenB === null) {
			$out['indirect_length_skips']++;
			continue;
		}
		$out['direct_length_count']++;
		if ( $lenB > $maxIn) {
			if ( $collectErrors) {
				$out['failures'][] = array( 'offset' => $dataStart, 'err' => 'stream encoded length exceeds cap', 'filter_s' => '?' );
			}
			$out['decoded_fail']++;
			continue;
		}
		if ( $dataStart + $lenB > $n0) {
			if ( $collectErrors) {
				$out['failures'][] = array( 'offset' => $dataStart, 'err' => 'length past EOF', 'filter_s' => '?' );
			}
			$out['decoded_fail']++;
			continue;
		}
		$enc = (string) substr( $pdf, $dataStart, $lenB);
		$ft = fractal_zip_pdf_dict_parse_filter_names( $dict);
		$nf = count( $ft);
		$dpL = fractal_zip_pdf_dict_parse_decode_parms_list( $dict, $nf);
		$raw = $nf === 0
			? $enc
			: fractal_zip_pdf_filter_chain_decode( $enc, $ft, is_array( $dpL) ? $dpL : array_fill( 0, $nf, null) );
		$fs = implode( '+', $ft);
		$out['by_filter'][ $fs === '' ? 'none' : $fs] = (int) ( $out['by_filter'][ $fs === '' ? 'none' : $fs] ?? 0) + 1;
		if ( $raw === null) {
			$out['decoded_fail']++;
			if ( $collectErrors) {
				$out['failures'][] = array( 'offset' => $abs0, 'err' => 'decode failed', 'filter_s' => $fs );
			}
			continue;
		}
		$ld = strlen( $raw);
		if ( $maxD > 0 && $ld > $maxD) {
			$out['decoded_fail']++;
			if ( $collectErrors) {
				$out['failures'][] = array( 'offset' => $dataStart, 'err' => 'decoded bytes exceed FRACTAL_ZIP_LITERALPAC_MAX_DECOMPRESSED_BYTES', 'filter_s' => $fs );
			}
			continue;
		}
		$out['decoded_ok']++;
		$out['total_raw_bytes'] += $ld;
	}
	ksort( $out['by_filter'] );
	return $out;
}