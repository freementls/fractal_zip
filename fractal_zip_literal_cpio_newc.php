<?php
declare(strict_types=1);

/**
 * newc cpio (ASCII, new 070701 + CRC 070702) with exactly one file record + standard TRAILER (FZB literal mode 22).
 * Record layout is identical; trailer may use 070701 or 070702; rebuild is byte-identical.
 */

/** newc name field for end-of-archive (10 bytes: "TRAILER!!!" + NUL). */
const FRACTAL_ZIP_LITERAL_CPIO_TRAILER_NAME = "TRAILER!!!\0";

function fractal_zip_literal_cpio_newc_magic6_ok(string $m6): bool {
	if(strlen($m6) < 6) {
		return false;
	}
	$x = $m6[0] . $m6[1] . $m6[2] . $m6[3] . $m6[4] . $m6[5];
	return $x === '070701' || $x === '070702';
}

function fractal_zip_literal_semantic_cpio_newc_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_CPIO');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_path_looks_cpio_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	if(str_ends_with($low, '.cpio') || str_ends_with($low, '.cpio.zst') || str_contains($low, 'initramfs')) {
		return true;
	}
	return str_ends_with($low, '.initramfs.cpio') || str_ends_with($low, '.cpio.initrd');
}

/**
 * @return string path hint for inner member (raster / stream PAC)
 */
function fractal_zip_literal_path_for_raster_pac_after_cpio_strip(string $relPath, string $memberNameField): string {
	$rel = str_replace('\\', '/', $relPath);
	$base = basename($rel);
	$dir = dirname($rel);
	$low = strtolower($base);
	$root = $base;
	if(str_ends_with($low, '.cpio') && strlen($base) > 5) {
		$root = substr($base, 0, -5);
	}
	$mem = ltrim($memberNameField, '/');
	$p = (string) preg_replace('/\0.*/s', '', $mem);
	if($p === '') {
		$p = 'member';
	}
	if($dir === '' || $dir === '.') {
		return $root . '/' . $p;
	}
	return $dir . '/' . $root . '/' . $p;
}

/**
 * 8-hex-ASCII field in newc header (6-byte magic, then 13*8 = 104 bytes, total 110). Field index 0 = ino, …, 6 = filesize, 11 = namesize, 12 = check.
 */
function fractal_zip_literal_cpio_newc_parse_hex8(string $h110, int $i): int {
	if(strlen($h110) < 110 || $i < 0 || $i > 12) {
		return -1;
	}
	$s = substr($h110, 6 + $i * 8, 8);
	$s = trim($s);
	if($s === '' || $s === str_repeat('0', 8) || $s === str_repeat(' ', 8)) {
		return 0;
	}
	if(!preg_match('/^[0-9A-Fa-f]+$/', $s)) {
		return -1;
	}
	$v = (int) hexdec($s);
	if($v < 0) {
		return -1;
	}
	return $v;
}

function fractal_zip_literal_cpio_newc_align4(int $pos): int {
	return (4 - ($pos % 4)) % 4;
}

/**
 * @return list{int, int, int, int, int}|null [dataStart, fileSize, nameEnd, afterData, endRecord) endRecord = start of next header
 */
function fractal_zip_literal_cpio_newc_one_record_layout(string $compressed, int $hOff): ?array {
	$n = strlen($compressed);
	if($hOff + 110 > $n) {
		return null;
	}
	$h = substr($compressed, $hOff, 110);
	if(!fractal_zip_literal_cpio_newc_magic6_ok(substr($h, 0, 6))) {
		return null;
	}
	$fileSize = fractal_zip_literal_cpio_newc_parse_hex8($h, 6);
	$nameSize = fractal_zip_literal_cpio_newc_parse_hex8($h, 11);
	if($fileSize < 0 || $nameSize < 1) {
		return null;
	}
	$name0 = 110 + $hOff;
	if($name0 + $nameSize > $n) {
		return null;
	}
	$name = substr($compressed, $name0, $nameSize);
	$nameEnd = $name0 + $nameSize;
	$namePad = fractal_zip_literal_cpio_newc_align4($nameEnd);
	if($nameEnd + $namePad > $n) {
		return null;
	}
	$dataStart = $nameEnd + $namePad;
	if($dataStart + $fileSize > $n) {
		return null;
	}
	$afterData = $dataStart + $fileSize;
	$dataPad = fractal_zip_literal_cpio_newc_align4($afterData);
	if($afterData + $dataPad > $n) {
		return null;
	}
	return array($dataStart, $fileSize, $nameEnd, $afterData, $afterData + $dataPad);
}

/**
 * @return true if record at hOff is the newc TRAILER; false if a valid 070701/070702 record that is not the trailer; null if invalid
 */
function fractal_zip_literal_cpio_newc_record_is_trailer(string $compressed, int $hOff): ?bool {
	$n = strlen($compressed);
	if($hOff + 110 > $n) {
		return null;
	}
	$h = substr($compressed, $hOff, 110);
	if(!fractal_zip_literal_cpio_newc_magic6_ok(substr($h, 0, 6))) {
		return null;
	}
	$fs = fractal_zip_literal_cpio_newc_parse_hex8($h, 6);
	$ns = fractal_zip_literal_cpio_newc_parse_hex8($h, 11);
	if($ns < 1) {
		return null;
	}
	$name0 = 110 + $hOff;
	if($name0 + $ns > $n) {
		return null;
	}
	$nm = substr($compressed, $name0, $ns);
	if($fs !== 0) {
		return false;
	}
	if($nm === FRACTAL_ZIP_LITERAL_CPIO_TRAILER_NAME) {
		return true;
	}
	return false;
}

/**
 * @return string|null
 */
function fractal_zip_literal_pac_rebuild_cpio_newc(string $payload, string $tag): ?string {
	$parts = explode(':', $tag, 2);
	if(count($parts) !== 2) {
		return null;
	}
	$prefix = base64_decode($parts[0], true);
	$suffix = base64_decode($parts[1], true);
	if($prefix === false || $suffix === false || $prefix === '') {
		return null;
	}
	if(strlen($prefix) < 110) {
		return null;
	}
	$h = substr($prefix, 0, 110);
	if(substr($h, 0, 6) !== '070701') {
		return null;
	}
	$expectSize = fractal_zip_literal_cpio_newc_parse_hex8($h, 6);
	if($expectSize < 0 || (int) $expectSize !== strlen($payload)) {
		return null;
	}
	return $prefix . $payload . $suffix;
}

/**
 * Fast sniff for peel ordering: 070701/070702 + one record + TRAILER at EOF.
 */
function fractal_zip_literal_cpio_newc_sniffs_single_plus_trailer(string $work): bool {
	if(!fractal_zip_literal_semantic_cpio_newc_enabled()) {
		return false;
	}
	$lay = fractal_zip_literal_cpio_newc_one_record_layout($work, 0);
	if($lay === null) {
		return false;
	}
	list(,,,,$endFirst) = $lay;
	$n = strlen($work);
	if($endFirst + 110 > $n) {
		return false;
	}
	if(fractal_zip_literal_cpio_newc_record_is_trailer($work, $endFirst) !== true) {
		return false;
	}
	$trH = 110;
	$ns = fractal_zip_literal_cpio_newc_parse_hex8(substr($work, $endFirst, 110), 11);
	if($ns < 1) {
		return false;
	}
	$name0 = $endFirst + $trH;
	$nameEnd = $name0 + $ns;
	$nPad = fractal_zip_literal_cpio_newc_align4($nameEnd);
	$eof = $endFirst + $trH + $ns + $nPad;
	if($eof !== $n) {
		return false;
	}
	$ns0 = fractal_zip_literal_cpio_newc_parse_hex8(substr($work, 0, 110), 11);
	if(110 + $ns0 > $n) {
		return false;
	}
	$firstName = substr($work, 110, $ns0);
	$h0 = substr($work, 0, 110);
	if(fractal_zip_literal_cpio_newc_parse_hex8($h0, 6) === 0
		&& $firstName === FRACTAL_ZIP_LITERAL_CPIO_TRAILER_NAME) {
		return false;
	}
	return true;
}

/**
 * @return array{0: string, 1: string}|null [payload, tag] tag = base64(prefix) : base64(suffix) — bytes before/after file data
 */
function fractal_zip_literal_pac_peel_cpio_newc(string $compressed): ?array {
	if(!fractal_zip_literal_semantic_cpio_newc_enabled()) {
		return null;
	}
	if(!function_exists('fractal_zip_literal_pac_payload_within_limit') || !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$lay = fractal_zip_literal_cpio_newc_one_record_layout($compressed, 0);
	if($lay === null) {
		return null;
	}
	list($dStart, $fSize,,,$endFirst) = $lay;
	if(fractal_zip_literal_cpio_newc_record_is_trailer($compressed, $endFirst) !== true) {
		return null;
	}
	$n = strlen($compressed);
	$trH = 110;
	$trNs = fractal_zip_literal_cpio_newc_parse_hex8(substr($compressed, $endFirst, 110), 11);
	if($trNs < 1) {
		return null;
	}
	$name0 = $endFirst + $trH;
	$nameEnd = $name0 + $trNs;
	$nPad = fractal_zip_literal_cpio_newc_align4($nameEnd);
	$eof = $endFirst + $trH + $trNs + $nPad;
	if($eof !== $n) {
		return null;
	}
	$ns0 = fractal_zip_literal_cpio_newc_parse_hex8(substr($compressed, 0, 110), 11);
	if(110 + $ns0 > $n) {
		return null;
	}
	$firstName = substr($compressed, 110, $ns0);
	$h0 = substr($compressed, 0, 110);
	if(fractal_zip_literal_cpio_newc_parse_hex8($h0, 6) === 0
		&& $firstName === FRACTAL_ZIP_LITERAL_CPIO_TRAILER_NAME) {
		return null;
	}
	$prefix = substr($compressed, 0, $dStart);
	$payload = substr($compressed, $dStart, $fSize);
	if(strlen($payload) !== (int) $fSize) {
		return null;
	}
	$suffix = substr($compressed, $endFirst);
	$tag = base64_encode($prefix) . ':' . base64_encode($suffix);
	$re = fractal_zip_literal_pac_rebuild_cpio_newc($payload, $tag);
	if($re === null || $re !== $compressed) {
		return null;
	}
	return [$payload, $tag];
}
