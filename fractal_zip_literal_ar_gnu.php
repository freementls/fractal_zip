<?php
declare(strict_types=1);

/**
 * Single-member GNU/BSD `ar` archive peel/rebuild for literal deep-unwrap (FZB literal mode 21).
 * Only accepts archives where a single data member plus optional odd padding fills the file;
 * rebuild is byte-identical to the input (strict lossless gate). Skips symtab, string-table, and thin formats.
 */

function fractal_zip_literal_semantic_ar_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_AR');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_path_looks_ar_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	return str_ends_with($low, '.a') || str_ends_with($low, '.lib');
}

/**
 * @return string path hint for inner member (raster / stream PAC)
 */
function fractal_zip_literal_path_for_raster_pac_after_ar_strip(string $relPath, string $memberName): string {
	$rel = str_replace('\\', '/', $relPath);
	$base = basename($rel);
	$dir = dirname($rel);
	$low = strtolower($base);
	$root = (str_ends_with($low, '.a') && strlen($base) > 2) ? substr($base, 0, -2) : $base;
	$mem = ltrim($memberName, '/');
	if($mem === '') {
		$mem = 'member';
	}
	if($dir === '' || $dir === '.') {
		return $root . '/' . $mem;
	}
	return $dir . '/' . $root . '/' . $mem;
}

function fractal_zip_literal_ar_gnu_header_fmag_ok(string $h60): bool {
	if(strlen($h60) !== 60) {
		return false;
	}
	return substr($h60, 58, 2) === "`\n";
}

function fractal_zip_literal_ar_gnu_header_size(string $h60): int {
	if(strlen($h60) !== 60) {
		return -1;
	}
	$s = rtrim(substr($h60, 48, 10), " \0");
	if($s === '' || !preg_match('/^[0-9]+$/', $s)) {
		return -1;
	}
	return (int) $s;
}

/**
 * Reject symtab, long-name table, and /N long-name indirections.
 */
function fractal_zip_literal_ar_gnu_member_name_ok_for_data(string $name16): bool {
	$n = rtrim($name16, ' ');
	if($n === '' || $n === '//') {
		return false;
	}
	if($n[0] === '/') {
		return false;
	}
	return true;
}

/**
 * @return string|null [payload, tag] tag = base64(60b hdr) : base64(suffix) — suffix always empty for v1
 */
function fractal_zip_literal_pac_rebuild_ar_single_gnu(string $payload, string $tag): ?string {
	$parts = explode(':', $tag, 2);
	if(count($parts) !== 2) {
		return null;
	}
	$hdr = base64_decode($parts[0], true);
	$sfx = base64_decode($parts[1], true);
	if($hdr === false || $hdr === '' || strlen($hdr) !== 60 || $sfx === false) {
		return null;
	}
	if($sfx !== '') {
		return null;
	}
	if(!fractal_zip_literal_ar_gnu_header_fmag_ok($hdr)) {
		return null;
	}
	$sz = fractal_zip_literal_ar_gnu_header_size($hdr);
	if($sz < 0 || $sz !== strlen($payload)) {
		return null;
	}
	$pad = ($sz % 2) ? "\n" : '';
	return "!<arch>\n" . $hdr . $payload . $pad;
}

/**
 * Fast sniff for peel ordering: magic + one member that fills the file.
 */
function fractal_zip_literal_ar_sniffs_single_gnu_candidate(string $work): bool {
	if(!fractal_zip_literal_semantic_ar_enabled()) {
		return false;
	}
	$n = strlen($work);
	if($n < 8 + 60) {
		return false;
	}
	if(substr($work, 0, 8) !== "!<arch>\n") {
		return false;
	}
	$hdr = substr($work, 8, 60);
	if(!fractal_zip_literal_ar_gnu_header_fmag_ok($hdr)) {
		return false;
	}
	if(!fractal_zip_literal_ar_gnu_member_name_ok_for_data(substr($hdr, 0, 16))) {
		return false;
	}
	$sz = fractal_zip_literal_ar_gnu_header_size($hdr);
	if($sz < 0) {
		return false;
	}
	$pad = ($sz % 2) ? 1 : 0;
	$end = 8 + 60 + $sz + $pad;
	if($end !== $n) {
		return false;
	}
	return true;
}

/**
 * @return array{0: string, 1: string}|null [payload, tag]
 */
function fractal_zip_literal_pac_peel_ar_single_gnu(string $compressed): ?array {
	if(!fractal_zip_literal_semantic_ar_enabled()) {
		return null;
	}
	if(!function_exists('fractal_zip_literal_pac_payload_within_limit') || !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$n = strlen($compressed);
	if($n < 8 + 60) {
		return null;
	}
	if(substr($compressed, 0, 8) !== "!<arch>\n") {
		return null;
	}
	$hdr = substr($compressed, 8, 60);
	if(!fractal_zip_literal_ar_gnu_header_fmag_ok($hdr)) {
		return null;
	}
	if(!fractal_zip_literal_ar_gnu_member_name_ok_for_data(substr($hdr, 0, 16))) {
		return null;
	}
	$sz = fractal_zip_literal_ar_gnu_header_size($hdr);
	if($sz < 0) {
		return null;
	}
	$off = 8 + 60;
	if($off + $sz > $n) {
		return null;
	}
	$payload = substr($compressed, $off, $sz);
	if(strlen($payload) !== $sz) {
		return null;
	}
	$padN = $sz & 1;
	$end = $off + $sz + $padN;
	if($end !== $n) {
		return null;
	}
	if($padN === 1 && ($compressed[$off + $sz] ?? "\0") !== "\n") {
		return null;
	}
	$tag = base64_encode($hdr) . ':' . base64_encode('');
	$re = fractal_zip_literal_pac_rebuild_ar_single_gnu($payload, $tag);
	if($re === null || $re !== $compressed) {
		return null;
	}
	return [$payload, $tag];
}
