<?php
declare(strict_types=1);

/**
 * Single-member POSIX ustar TAR peel/rebuild for literal deep-unwrap (FZB literal mode 20).
 * Only accepts archives where our rebuild is byte-identical to the input (strict lossless gate).
 */

function fractal_zip_literal_semantic_tar_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_TAR');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_path_looks_tar_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	return str_ends_with($low, '.tar') || str_ends_with($low, '.tar.gz') || str_ends_with($low, '.tgz');
}

function fractal_zip_literal_path_for_raster_pac_after_tar_strip(string $relPath): string {
	$relPath = str_replace('\\', '/', $relPath);
	$low = strtolower($relPath);
	if (str_ends_with($low, '.tar.gz')) {
		return substr($relPath, 0, -7);
	}
	if (str_ends_with($low, '.tgz')) {
		return substr($relPath, 0, -4);
	}
	if (str_ends_with($low, '.tar')) {
		return substr($relPath, 0, -4);
	}
	return $relPath;
}

function fractal_zip_literal_tar_octal_field(string $twelveBytes): int {
	$s = rtrim((string) $twelveBytes, " \0");
	if ($s === '') {
		return 0;
	}
	if (!preg_match('/^[0-7]+$/', $s)) {
		return -1;
	}
	return (int) octdec($s);
}

function fractal_zip_literal_tar_ustar_header_checksum_ok(string $block512): bool {
	if (strlen($block512) !== 512) {
		return false;
	}
	$sum = 0;
	for ($i = 0; $i < 512; $i++) {
		if ($i >= 148 && $i < 156) {
			$sum += 32;
		} else {
			$sum += ord($block512[$i]);
		}
	}
	$field = substr($block512, 148, 8);
	$want = (int) octdec(rtrim(substr($field, 0, 6), " \0"));
	return $sum === $want;
}

/**
 * @return array{0: string, 1: string}|null [8-byte checksum field to write at 148, verified block]
 */
function fractal_zip_literal_tar_ustar_fix_checksum(string $block512): ?array {
	if (strlen($block512) !== 512) {
		return null;
	}
	$sum = 0;
	for ($i = 0; $i < 512; $i++) {
		if ($i >= 148 && $i < 156) {
			$sum += 32;
		} else {
			$sum += ord($block512[$i]);
		}
	}
	$ch = sprintf('%06o', $sum) . "\0 ";
	if (strlen($ch) !== 8) {
		return null;
	}
	$fixed = substr_replace($block512, $ch, 148, 8);
	if (!fractal_zip_literal_tar_ustar_header_checksum_ok($fixed)) {
		return null;
	}
	return [$ch, $fixed];
}

/**
 * Tag: base64(header512) . ':' . base64(suffixAfterPayload) — suffix is verbatim trailer (zero blocks).
 *
 * @return string|null
 */
function fractal_zip_literal_pac_rebuild_tar_single_ustar(string $payload, string $tag): ?string {
	$parts = explode(':', $tag, 2);
	if (count($parts) !== 2) {
		return null;
	}
	$hdr = base64_decode($parts[0], true);
	$sfx = base64_decode($parts[1], true);
	if ($hdr === false || $hdr === '' || strlen($hdr) !== 512 || $sfx === false) {
		return null;
	}
	$size = fractal_zip_literal_tar_octal_field(substr($hdr, 124, 12));
	if ($size < 0 || $size !== strlen($payload)) {
		return null;
	}
	$pad = (512 - ($size % 512)) % 512;
	$body = $payload . str_repeat("\0", $pad);
	$fix = fractal_zip_literal_tar_ustar_fix_checksum($hdr);
	if ($fix === null) {
		return null;
	}
	return $fix[1] . $body . $sfx;
}

/**
 * @return array{0: string, 1: string}|null [payload, tag]
 */
/**
 * Fast sniff for heuristic ordering (no full round-trip rebuild).
 */
function fractal_zip_literal_tar_sniffs_single_ustar_candidate(string $compressed): bool {
	if (!fractal_zip_literal_semantic_tar_enabled()) {
		return false;
	}
	$n = strlen($compressed);
	if ($n < 512 + 1) {
		return false;
	}
	$hdr = substr($compressed, 0, 512);
	if (substr($hdr, 257, 6) !== "ustar\0" || substr($hdr, 263, 2) !== '00') {
		return false;
	}
	$type = $hdr[156] ?? '0';
	if ($type !== '0' && $type !== "\0") {
		return false;
	}
	if (!fractal_zip_literal_tar_ustar_header_checksum_ok($hdr)) {
		return false;
	}
	$size = fractal_zip_literal_tar_octal_field(substr($hdr, 124, 12));
	if ($size < 0 || $size > $n - 512) {
		return false;
	}
	$pad = (512 - ($size % 512)) % 512;
	$endData = 512 + $size + $pad;
	if ($endData > $n) {
		return false;
	}
	$suffix = substr($compressed, $endData);
	return $suffix === '' || str_repeat("\0", strlen($suffix)) === $suffix;
}

function fractal_zip_literal_pac_peel_tar_single_ustar(string $compressed): ?array {
	if (!fractal_zip_literal_semantic_tar_enabled()) {
		return null;
	}
	if (!function_exists('fractal_zip_literal_pac_payload_within_limit') || !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$n = strlen($compressed);
	if ($n < 512 + 1) {
		return null;
	}
	$hdr = substr($compressed, 0, 512);
	if (strlen($hdr) !== 512) {
		return null;
	}
	if (substr($hdr, 257, 6) !== "ustar\0") {
		return null;
	}
	if (substr($hdr, 263, 2) !== '00') {
		return null;
	}
	$type = $hdr[156] ?? '0';
	if ($type !== '0' && $type !== "\0") {
		return null;
	}
	if (!fractal_zip_literal_tar_ustar_header_checksum_ok($hdr)) {
		return null;
	}
	$size = fractal_zip_literal_tar_octal_field(substr($hdr, 124, 12));
	if ($size < 0 || $size > $n - 512) {
		return null;
	}
	$payload = substr($compressed, 512, $size);
	if (strlen($payload) !== $size) {
		return null;
	}
	$pad = (512 - ($size % 512)) % 512;
	$endData = 512 + $size + $pad;
	if ($endData > $n) {
		return null;
	}
	$suffix = substr($compressed, $endData);
	if ($suffix !== '' && str_repeat("\0", strlen($suffix)) !== $suffix) {
		return null;
	}
	$tag = base64_encode($hdr) . ':' . base64_encode($suffix);
	$re = fractal_zip_literal_pac_rebuild_tar_single_ustar($payload, $tag);
	if ($re === null || $re !== $compressed) {
		return null;
	}
	return [$payload, $tag];
}
