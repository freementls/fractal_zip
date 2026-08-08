<?php
declare(strict_types=1);

/**
 * POSIX / GNU ustar TAR peel/rebuild for literal deep-unwrap (FZB literal mode 20).
 * GNU tar uses "ustar  " (two spaces), not "ustar\0" — both are accepted.
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

function fractal_zip_literal_semantic_tar_multimember_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_TAR_MULTI');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/** @return array{0: int, 1: int} maxMembers, maxTotalUncompressed */
function fractal_zip_literal_tar_multimember_limits(): array {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$me = getenv('FRACTAL_ZIP_LITERAL_TAR_MULTI_MAX_MEMBERS');
	$maxM = ($me === false || trim((string) $me) === '') ? 4096 : max(2, min(65535, (int) $me));
	$te = getenv('FRACTAL_ZIP_LITERAL_TAR_MULTI_MAX_RAW_BYTES');
	$maxT = ($te === false || trim((string) $te) === '') ? (256 * 1024 * 1024) : max(0, (int) $te);
	return $cached = [$maxM, $maxT];
}

function fractal_zip_literal_tar_ustar_magic_ok(string $block512): bool {
	return strlen($block512) >= 263 && str_starts_with(substr($block512, 257, 5), 'ustar');
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
	$s = trim((string) $twelveBytes, " \0");
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
 * First 512-byte-aligned ustar header offset within prefix, or -1.
 */
function fractal_zip_literal_tar_find_first_header_offset(string $bytes, int $maxScan = 65536): int {
	$n = strlen($bytes);
	if ($n < 512) {
		return -1;
	}
	$lim = min($n - 512, max(0, $maxScan - 512));
	for ($off = 0; $off <= $lim; $off += 512) {
		$hdr = substr($bytes, $off, 512);
		if ($hdr === str_repeat("\0", 512)) {
			continue;
		}
		if (!fractal_zip_literal_tar_ustar_magic_ok($hdr)) {
			continue;
		}
		if (!fractal_zip_literal_tar_ustar_header_checksum_ok($hdr)) {
			continue;
		}
		return $off;
	}
	return -1;
}

/** True when the buffer begins with (or quickly contains) a valid GNU/POSIX tar archive. */
function fractal_zip_literal_tar_sniffs_gnu_archive(string $bytes): bool {
	return fractal_zip_literal_tar_find_first_header_offset($bytes) >= 0;
}

/**
 * @return list<array{name: string, data: string, header_offset: int}>|null
 */
function fractal_zip_literal_tar_list_file_members(string $bytes): ?array {
	if (!fractal_zip_literal_semantic_tar_enabled()) {
		return null;
	}
	$n = strlen($bytes);
	$off = fractal_zip_literal_tar_find_first_header_offset($bytes, $n);
	if ($off < 0) {
		return null;
	}
	[$maxMembers, $maxTotal] = fractal_zip_literal_tar_multimember_limits();
	$members = [];
	$total = 0;
	while ($off + 512 <= $n) {
		$hdr = substr($bytes, $off, 512);
		if ($hdr === str_repeat("\0", 512)) {
			break;
		}
		if (!fractal_zip_literal_tar_ustar_magic_ok($hdr) || !fractal_zip_literal_tar_ustar_header_checksum_ok($hdr)) {
			break;
		}
		$type = $hdr[156] ?? '0';
		$size = fractal_zip_literal_tar_octal_field(substr($hdr, 124, 12));
		if ($size < 0 && ($type === '5' || $type === '2' || $type === 'x30')) {
			$size = 0;
		}
		if ($size < 0) {
			break;
		}
		$pad = (512 - ($size % 512)) % 512;
		$next = $off + 512 + $size + $pad;
		if ($next > $n) {
			break;
		}
		if ($type === '0' || $type === "\0") {
			$name = rtrim(substr($hdr, 0, 100), "\0");
			if ($name !== '' && $name !== '.' && $name !== '..') {
				$data = substr($bytes, $off + 512, $size);
				if (strlen($data) === $size) {
					$total += $size;
					if ($maxTotal > 0 && $total > $maxTotal) {
						return null;
					}
					$members[] = ['name' => $name, 'data' => $data, 'header_offset' => $off];
					if (count($members) > $maxMembers) {
						return null;
					}
				}
			}
		}
		$off = $next;
	}
	return $members !== [] ? $members : null;
}

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

function fractal_zip_literal_tar_sniffs_single_ustar_candidate(string $compressed): bool {
	if (!fractal_zip_literal_semantic_tar_enabled()) {
		return false;
	}
	$off = fractal_zip_literal_tar_find_first_header_offset($compressed);
	if ($off < 0) {
		return false;
	}
	$members = fractal_zip_literal_tar_list_file_members($compressed);
	return $members !== null && count($members) === 1
		&& $members[0]['header_offset'] === $off
		&& fractal_zip_literal_tar_single_member_covers_archive($compressed, $members[0]);
}

function fractal_zip_literal_tar_single_member_covers_archive(string $bytes, array $member): bool {
	$n = strlen($bytes);
	$off = (int) $member['header_offset'];
	$size = strlen((string) $member['data']);
	$pad = (512 - ($size % 512)) % 512;
	$end = $off + 512 + $size + $pad;
	$suffix = substr($bytes, $end);
	return $suffix === '' || str_repeat("\0", strlen($suffix)) === $suffix;
}

/**
 * @return array{0: string, 1: string}|null [payload, tag]
 */
function fractal_zip_literal_pac_peel_tar_single_ustar(string $compressed): ?array {
	$off = fractal_zip_literal_tar_find_first_header_offset($compressed);
	if ($off < 0) {
		return null;
	}
	return fractal_zip_literal_pac_peel_tar_single_ustar_at($compressed, $off);
}

/**
 * @return array{0: string, 1: string}|null [payload, tag]
 */
function fractal_zip_literal_pac_peel_tar_single_ustar_at(string $compressed, int $headerOffset): ?array {
	if (!fractal_zip_literal_semantic_tar_enabled()) {
		return null;
	}
	if (function_exists('fractal_zip_literal_pac_payload_within_limit')
		&& !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$n = strlen($compressed);
	if ($headerOffset < 0 || $headerOffset + 512 > $n) {
		return null;
	}
	$prefix = $headerOffset > 0 ? substr($compressed, 0, $headerOffset) : '';
	$hdr = substr($compressed, $headerOffset, 512);
	if (!fractal_zip_literal_tar_ustar_magic_ok($hdr)) {
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
	if ($size < 0 || $headerOffset + 512 + $size > $n) {
		return null;
	}
	$payload = substr($compressed, $headerOffset + 512, $size);
	if (strlen($payload) !== $size) {
		return null;
	}
	$pad = (512 - ($size % 512)) % 512;
	$endData = $headerOffset + 512 + $size + $pad;
	if ($endData > $n) {
		return null;
	}
	$suffix = substr($compressed, $endData);
	if ($suffix !== '' && str_repeat("\0", strlen($suffix)) !== $suffix) {
		return null;
	}
	$tag = base64_encode($prefix) . ':' . base64_encode($hdr) . ':' . base64_encode($suffix);
	$re = fractal_zip_literal_pac_rebuild_tar_single_ustar_at($payload, $tag);
	if ($re === null || $re !== $compressed) {
		return null;
	}
	$pb = @gzdeflate($compressed, 1);
	$pa = @gzdeflate($payload, 1);
	if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	return [$payload, $tag];
}

function fractal_zip_literal_pac_rebuild_tar_single_ustar_at(string $payload, string $tag): ?string {
	$parts = explode(':', $tag, 3);
	if (count($parts) !== 3) {
		return fractal_zip_literal_pac_rebuild_tar_single_ustar($payload, $tag);
	}
	$prefix = base64_decode($parts[0], true);
	$hdr = base64_decode($parts[1], true);
	$sfx = base64_decode($parts[2], true);
	if ($prefix === false || $hdr === false || $sfx === false || strlen($hdr) !== 512) {
		return null;
	}
	$size = fractal_zip_literal_tar_octal_field(substr($hdr, 124, 12));
	if ($size < 0 || $size !== strlen($payload)) {
		return null;
	}
	$pad = (512 - ($size % 512)) % 512;
	$fix = fractal_zip_literal_tar_ustar_fix_checksum($hdr);
	if ($fix === null) {
		return null;
	}
	return ($prefix !== '' ? $prefix : '') . $fix[1] . $payload . str_repeat("\0", $pad) . $sfx;
}

/**
 * Multi-member archive peel for deep_unwrap: inner = FZTM stream of member payloads; tag preserves full tar bytes.
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_tar_archive_semantic(string $compressed): ?array {
	if (!fractal_zip_literal_semantic_tar_enabled() || !fractal_zip_literal_semantic_tar_multimember_enabled()) {
		return null;
	}
	$members = fractal_zip_literal_tar_list_file_members($compressed);
	if ($members === null) {
		return null;
	}
	if (count($members) === 1 && fractal_zip_literal_tar_single_member_covers_archive($compressed, $members[0])) {
		return fractal_zip_literal_pac_peel_tar_single_ustar_at($compressed, $members[0]['header_offset']);
	}
	if (count($members) < 2) {
		return null;
	}
	$inner = fractal_zip_literal_tar_build_fztm_inner($members, $compressed);
	if ($inner === null) {
		return null;
	}
	$tagB64 = base64_encode($compressed);
	$tag = (strlen($tagB64) <= 60000) ? ('TARM1:' . $tagB64) : 'TARM0:';
	if (str_starts_with($tag, 'TARM1:')) {
		$re = fractal_zip_literal_pac_rebuild_tar_archive_semantic($inner, $tag);
		if ($re === null || $re !== $compressed) {
			return null;
		}
	} else {
		$re = fractal_zip_literal_pac_rebuild_tar_from_fztm_inner($inner);
		if ($re === null || !fractal_zip_literal_tar_members_payload_equal($compressed, $re)) {
			return null;
		}
	}
	$pb = @gzdeflate($compressed, 1);
	$pa = @gzdeflate($inner, 1);
	if (str_starts_with($tag, 'TARM1:') && $pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	return [$inner, $tag];
}

function fractal_zip_literal_pac_rebuild_tar_archive_semantic(string $inner, string $tag): ?string {
	if (!str_starts_with($tag, 'TARM1:')) {
		return null;
	}
	$raw = base64_decode(substr($tag, 6), true);
	return is_string($raw) && $raw !== '' ? $raw : null;
}

/**
 * FZTM v1: member name + payload only (wire / zip_folder; not decodable without v2 or disk restore).
 *
 * @param list<array{name: string, data: string, header_offset: int}> $members
 */
function fractal_zip_literal_tar_build_fztm_inner_v1(array $members): string {
	$inner = "FZTM\x01";
	foreach ($members as $m) {
		$name = (string) $m['name'];
		$data = (string) $m['data'];
		$inner .= pack('V', strlen($name)) . $name . pack('V', strlen($data)) . $data;
	}
	return $inner;
}

/**
 * FZTM v2: per member name + payload + original 512-byte ustar header (lossless rebuild).
 *
 * @param list<array{name: string, data: string, header_offset: int}> $members
 * @param string $sourceTar original archive bytes
 */
function fractal_zip_literal_tar_build_fztm_inner(array $members, string $sourceTar): ?string {
	$inner = "FZTM\x02";
	foreach ($members as $m) {
		$name = (string) $m['name'];
		$data = (string) $m['data'];
		$ho = (int) $m['header_offset'];
		if ($ho < 0 || $ho + 512 > strlen($sourceTar)) {
			return null;
		}
		$hdr = substr($sourceTar, $ho, 512);
		$inner .= pack('V', strlen($name)) . $name . pack('V', strlen($data)) . $data . $hdr;
	}
	return $inner;
}

/**
 * Multi-member tar peel for zip_folder / deep_unwrap wire: FZTM v1 inner, v2 validation only.
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_tar_archive_wire(string $compressed): ?array {
	if (!fractal_zip_literal_semantic_tar_enabled() || !fractal_zip_literal_semantic_tar_multimember_enabled()) {
		return null;
	}
	$members = fractal_zip_literal_tar_list_file_members($compressed);
	if ($members === null) {
		return null;
	}
	if (count($members) === 1 && fractal_zip_literal_tar_single_member_covers_archive($compressed, $members[0])) {
		return fractal_zip_literal_pac_peel_tar_single_ustar_at($compressed, $members[0]['header_offset']);
	}
	if (count($members) < 2) {
		return null;
	}
	$innerWire = fractal_zip_literal_tar_build_fztm_inner_v1($members);
	$innerValidate = fractal_zip_literal_tar_build_fztm_inner($members, $compressed);
	if ($innerValidate === null) {
		return null;
	}
	$re = fractal_zip_literal_pac_rebuild_tar_from_fztm_inner($innerValidate);
	if ($re === null || !fractal_zip_literal_tar_members_payload_equal($compressed, $re)) {
		return null;
	}
	$pb = @gzdeflate($compressed, 1);
	$pa = @gzdeflate($innerWire, 1);
	if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	return [$innerWire, 'TARM0:'];
}

function fractal_zip_literal_tar_patch_header_size(string $hdr, int $size): ?string {
	if (strlen($hdr) !== 512) {
		return null;
	}
	$hdr = substr_replace($hdr, sprintf('%11o', $size) . ' ', 124, 12);
	$fixed = fractal_zip_literal_tar_ustar_fix_checksum($hdr);
	return $fixed !== null ? $fixed[1] : null;
}

function fractal_zip_literal_pac_rebuild_tar_from_fztm_inner(string $inner): ?string {
	$ver = strlen($inner) >= 5 ? $inner[4] : '';
	if ($ver !== "\x01" && $ver !== "\x02") {
		return null;
	}
	$n = strlen($inner);
	$off = 5;
	$out = '';
	while ($off < $n) {
		if ($off + 8 > $n) {
			break;
		}
		$nameLen = unpack('V', substr($inner, $off, 4));
		$nameLen = is_array($nameLen) ? (int) $nameLen[1] : -1;
		$off += 4;
		if ($nameLen < 0 || $nameLen > 65535 || $off + $nameLen + 4 > $n) {
			return null;
		}
		$name = substr($inner, $off, $nameLen);
		$off += $nameLen;
		$dataLen = unpack('V', substr($inner, $off, 4));
		$dataLen = is_array($dataLen) ? (int) $dataLen[1] : -1;
		$off += 4;
		if ($dataLen < 0 || $off + $dataLen > $n) {
			return null;
		}
		$data = substr($inner, $off, $dataLen);
		$off += $dataLen;
		$hdr = null;
		if ($ver === "\x02") {
			if ($off + 512 > $n) {
				return null;
			}
			$hdr = fractal_zip_literal_tar_patch_header_size(substr($inner, $off, 512), $dataLen);
			$off += 512;
		}
		if ($hdr === null) {
			return null;
		}
		$pad = (512 - ($dataLen % 512)) % 512;
		$out .= $hdr . $data . str_repeat("\0", $pad);
	}
	if ($out === '') {
		return null;
	}
	$out .= str_repeat("\0", 1024);
	return $out;
}

function fractal_zip_literal_tar_members_payload_equal(string $tarA, string $tarB): bool {
	$ma = fractal_zip_literal_tar_list_file_members($tarA);
	$mb = fractal_zip_literal_tar_list_file_members($tarB);
	if ($ma === null || $mb === null || count($ma) !== count($mb)) {
		return false;
	}
	foreach ($ma as $i => $m) {
		if ($m['name'] !== $mb[$i]['name'] || $m['data'] !== $mb[$i]['data']) {
			return false;
		}
	}
	return true;
}

/** Dispatch single-member (2- or 3-part tag) vs multi-member TARM1 rebuild. */
function fractal_zip_literal_pac_rebuild_tar_any_semantic(string $inner, string $tag): ?string {
	if (str_starts_with($tag, 'TARM0:')) {
		return fractal_zip_literal_pac_rebuild_tar_from_fztm_inner($inner);
	}
	if (str_starts_with($tag, 'TARM1:')) {
		return fractal_zip_literal_pac_rebuild_tar_archive_semantic($inner, $tag);
	}
	$parts = explode(':', $tag, 3);
	if (count($parts) === 3) {
		return fractal_zip_literal_pac_rebuild_tar_single_ustar_at($inner, $tag);
	}
	return fractal_zip_literal_pac_rebuild_tar_single_ustar($inner, $tag);
}

/**
 * Entry point for recursive peel: single-member, multi-member, or null.
 *
 * @param bool $forBundleEncode true ⇒ FZTM v2 + semantic tag for FZB mode 20; false ⇒ v1 wire (zip_folder)
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_tar_any_semantic(string $compressed, bool $forBundleEncode = true): ?array {
	$multi = $forBundleEncode
		? fractal_zip_literal_pac_peel_tar_archive_semantic($compressed)
		: fractal_zip_literal_pac_peel_tar_archive_wire($compressed);
	if ($multi !== null) {
		return $multi;
	}
	return fractal_zip_literal_pac_peel_tar_single_ustar($compressed);
}
