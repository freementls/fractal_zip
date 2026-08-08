<?php

declare(strict_types=1);

/**
 * Folder JPEG marker-share peel: identical APPn/DQT/… segments before SOS become
 * shared members; each JPEG leaf stores an FZJS wrapper + unique tail (SOS+scan
 * stays intact). Kill: FRACTAL_ZIP_FOLDER_JPEG_MARKER_SHARE=0
 */

/** @return list<array{code: int|string, off: int, len: int, data: string}> */
function fractal_zip_jpeg_list_markers_before_sos(string $bytes): array
{
	if (strlen($bytes) < 4 || $bytes[0] !== "\xFF" || $bytes[1] !== "\xD8") {
		return array();
	}
	$pos = 2;
	$markers = array();
	$len = strlen($bytes);
	while ($pos + 1 < $len) {
		if ($bytes[$pos] !== "\xFF") {
			break;
		}
		$m = ord($bytes[$pos + 1]);
		if ($m === 0xD9) {
			break;
		}
		if ($m === 0xDA) {
			break;
		}
		if ($m >= 0xD0 && $m <= 0xD7) {
			$pos += 2;
			continue;
		}
		if ($m === 0x01) {
			$pos += 2;
			continue;
		}
		if ($pos + 3 >= $len) {
			break;
		}
		$segLen = unpack('n', substr($bytes, $pos + 2, 2));
		if (!is_array($segLen)) {
			break;
		}
		$segLen = (int) $segLen[1];
		if ($segLen < 2 || $pos + 2 + $segLen > $len) {
			break;
		}
		$markers[] = array(
			'code' => $m,
			'off' => $pos,
			'len' => $segLen + 2,
			'data' => substr($bytes, $pos, $segLen + 2),
		);
		$pos += $segLen + 2;
	}
	return $markers;
}

function fractal_zip_folder_jpeg_marker_share_enabled(): bool
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_JPEG_MARKER_SHARE');
	// Default OFF: admission gate holds size flat on jpeg_peel (batch 32).
	// Opt-in: FRACTAL_ZIP_FOLDER_JPEG_MARKER_SHARE=1
	if ($e === false || trim((string) $e) === '') {
		return $cached = false;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !in_array($v, array('0', 'off', 'false', 'no'), true);
}

function fractal_zip_folder_jpeg_fzjs_is_wrapper(string $bytes): bool
{
	return strlen($bytes) >= 6 && substr($bytes, 0, 4) === 'FZJS' && $bytes[4] === "\x01";
}

/**
 * @param list<string> $segKeys archive member keys (segment bodies, no SOI)
 * @return string FZJS v1 wrapper blob stored as the JPEG leaf member
 */
function fractal_zip_folder_jpeg_fzjs_encode(array $segKeys, string $tail): string
{
	$tail = (string) $tail;
	if ($segKeys === array() && $tail === '') {
		return '';
	}
	$parts = array('FZJS', "\x01", chr(count($segKeys) & 0xFF));
	foreach ($segKeys as $key) {
		$key = (string) $key;
		$kl = strlen($key);
		if ($kl < 1 || $kl > 65535) {
			return '';
		}
		$parts[] = pack('n', $kl) . $key;
	}
	$parts[] = pack('N', strlen($tail)) . $tail;
	return implode('', $parts);
}

/**
 * Expand FZJS leaf to full JPEG bytes using already-resolved segment payloads.
 *
 * @param list<string> $segPayloads segment bodies in order (no SOI)
 */
function fractal_zip_folder_jpeg_fzjs_rebuild_from_segments(array $segPayloads, string $tail): ?string
{
	$out = "\xFF\xD8";
	foreach ($segPayloads as $seg) {
		$out .= (string) $seg;
	}
	$out .= (string) $tail;
	return $out;
}

/**
 * @param array<string,string> $memberFiles extracted member abs paths
 */
function fractal_zip_folder_jpeg_fzjs_expand(string $blob, array $memberFiles): ?string
{
	if (!fractal_zip_folder_jpeg_fzjs_is_wrapper($blob)) {
		return null;
	}
	$pos = 5;
	$n = ord($blob[5] ?? "\0");
	$pos = 6;
	$segPayloads = array();
	for ($i = 0; $i < $n; $i++) {
		if ($pos + 2 > strlen($blob)) {
			return null;
		}
		$kl = unpack('n', substr($blob, $pos, 2));
		if (!is_array($kl)) {
			return null;
		}
		$kl = (int) $kl[1];
		$pos += 2;
		if ($kl < 1 || $pos + $kl > strlen($blob)) {
			return null;
		}
		$key = substr($blob, $pos, $kl);
		$pos += $kl;
		if (str_contains($key, "\x1e")) {
			$key = explode("\x1e", $key, 2)[0];
		}
		if (!isset($memberFiles[$key])) {
			return null;
		}
		$data = @file_get_contents($memberFiles[$key]);
		if ($data === false) {
			return null;
		}
		$segPayloads[] = $data;
	}
	if ($pos + 4 > strlen($blob)) {
		return null;
	}
	$tl = unpack('N', substr($blob, $pos, 4));
	if (!is_array($tl)) {
		return null;
	}
	$tl = (int) $tl[1];
	$pos += 4;
	if ($tl < 0 || $pos + $tl !== strlen($blob)) {
		return null;
	}
	$tail = substr($blob, $pos, $tl);
	return fractal_zip_folder_jpeg_fzjs_rebuild_from_segments($segPayloads, $tail);
}

/**
 * Expand FZJS using in-memory $members map (encode-time verify).
 *
 * @param array<string,string> $members
 */
function fractal_zip_folder_jpeg_fzjs_expand_members(string $blob, array $members): ?string
{
	if (!fractal_zip_folder_jpeg_fzjs_is_wrapper($blob)) {
		return null;
	}
	$pos = 6;
	$n = ord($blob[5] ?? "\0");
	$segPayloads = array();
	for ($i = 0; $i < $n; $i++) {
		if ($pos + 2 > strlen($blob)) {
			return null;
		}
		$kl = unpack('n', substr($blob, $pos, 2));
		if (!is_array($kl)) {
			return null;
		}
		$kl = (int) $kl[1];
		$pos += 2;
		if ($kl < 1 || $pos + $kl > strlen($blob)) {
			return null;
		}
		$key = substr($blob, $pos, $kl);
		$pos += $kl;
		if (str_contains($key, "\x1e")) {
			$key = explode("\x1e", $key, 2)[0];
		}
		if (!isset($members[$key])) {
			return null;
		}
		$segPayloads[] = (string) $members[$key];
	}
	if ($pos + 4 > strlen($blob)) {
		return null;
	}
	$tl = unpack('N', substr($blob, $pos, 4));
	if (!is_array($tl)) {
		return null;
	}
	$tl = (int) $tl[1];
	$pos += 4;
	if ($pos + $tl !== strlen($blob)) {
		return null;
	}
	$tail = substr($blob, $pos, $tl);
	return fractal_zip_folder_jpeg_fzjs_rebuild_from_segments($segPayloads, $tail);
}

/**
 * Resolve a member payload for CLASSIC restore (expands FZJS JPEG wrappers).
 *
 * @param array<string,string> $memberFiles
 * @param array<string,string> $members in-memory fallback for encode-time verify
 */
function fractal_zip_folder_jpeg_member_payload_for_classic(
	string $data,
	array $memberFiles,
	array $members = array()
): string {
	if (!fractal_zip_folder_jpeg_fzjs_is_wrapper($data)) {
		return $data;
	}
	$expanded = fractal_zip_folder_jpeg_fzjs_expand($data, $memberFiles);
	if ($expanded === null && $members !== array()) {
		$expanded = fractal_zip_folder_jpeg_fzjs_expand_members($data, $members);
	}
	return $expanded !== null ? $expanded : $data;
}

/**
 * Post-pass: peel identical JPEG marker prefixes into shared members.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 * @param array<string,string> $diskFilesByPath optional originals for re-verify
 */
function fractal_zip_folder_apply_jpeg_marker_share(
	array &$members,
	array &$restore,
	array $diskFilesByPath = array()
): bool {
	if (!fractal_zip_folder_jpeg_marker_share_enabled()) {
		return false;
	}
	if (!function_exists('fractal_zip_folder_register_shared_payload')) {
		return false;
	}
	$maxBytes = 512 * 1024;
	$eMax = getenv('FRACTAL_ZIP_FOLDER_JPEG_MARKER_SHARE_MAX_BYTES');
	if ($eMax !== false && trim((string) $eMax) !== '') {
		$maxBytes = max(4096, (int) $eMax);
	}
	$minShare = 2;
	$eMin = getenv('FRACTAL_ZIP_FOLDER_JPEG_MARKER_SHARE_MIN_SIBLINGS');
	if ($eMin !== false && trim((string) $eMin) !== '') {
		$minShare = max(2, (int) $eMin);
	}
	$minSharedBytes = 64;
	$eMinB = getenv('FRACTAL_ZIP_FOLDER_JPEG_MARKER_SHARE_MIN_BYTES');
	if ($eMinB !== false && trim((string) $eMinB) !== '') {
		$minSharedBytes = max(16, (int) $eMinB);
	}

	/** @var list<array{path: string, markers: list<array>, tail: string, orig: string}> */
	$candidates = array();
	foreach ($members as $path => $bytes) {
		$path = str_replace('\\', '/', (string) $path);
		$bytes = (string) $bytes;
		if ($path === '' || str_contains($path, '..') || fractal_zip_folder_jpeg_fzjs_is_wrapper($bytes)) {
			continue;
		}
		if (strlen($bytes) < 32 || strlen($bytes) > $maxBytes) {
			continue;
		}
		if ($bytes[0] !== "\xFF" || $bytes[1] !== "\xD8") {
			continue;
		}
		$markers = fractal_zip_jpeg_list_markers_before_sos($bytes);
		if ($markers === array()) {
			continue;
		}
		$candidates[] = array(
			'path' => $path,
			'markers' => $markers,
			'orig' => $bytes,
		);
	}
	if (count($candidates) < $minShare) {
		return false;
	}

	$membersBefore = $members;

	$maxSegs = 0;
	foreach ($candidates as $c) {
		$maxSegs = max($maxSegs, count($c['markers']));
	}
	/** @var list<string> */
	$sharedSegData = array();
	$prevMatchCount = null;
	for ($si = 0; $si < $maxSegs; $si++) {
		/** @var array<string,int> */
		$freq = array();
		foreach ($candidates as $c) {
			if (!isset($c['markers'][$si])) {
				continue;
			}
			$d = $c['markers'][$si]['data'];
			$freq[$d] = ($freq[$d] ?? 0) + 1;
		}
		if ($freq === array()) {
			break;
		}
		arsort($freq, SORT_NUMERIC);
		$topData = (string) array_key_first($freq);
		$topCount = (int) ($freq[$topData] ?? 0);
		if ($topCount < $minShare) {
			break;
		}
		// Stop when the matching cohort shrinks (e.g. shared E0/DB then divergent SOF).
		if ($prevMatchCount !== null && $topCount < $prevMatchCount) {
			break;
		}
		$prevMatchCount = $topCount;
		$sharedSegData[] = $topData;
	}
	$sharedSegCount = count($sharedSegData);
	if ($sharedSegCount === 0) {
		return false;
	}
	$sharedBytes = 0;
	foreach ($sharedSegData as $seg) {
		$sharedBytes += strlen($seg);
	}
	if ($sharedBytes < $minSharedBytes) {
		return false;
	}

	$addedNew = array();
	/** @var list<string> */
	$sharedKeys = array();
	for ($si = 0; $si < $sharedSegCount; $si++) {
		$seg = $sharedSegData[$si];
		$codeHex = sprintf('%02X', ord($seg[1]));
		$sha = substr(hash('sha256', $seg), 0, 16);
		$logical = '__jpeg_mrk/' . $codeHex . '_' . $sha;
		$key = fractal_zip_folder_register_shared_payload(
			$members,
			$seg,
			$logical,
			$logical,
			$addedNew
		);
		if (str_contains($key, "\x1e")) {
			$key = explode("\x1e", $key, 2)[0];
		}
		$sharedKeys[] = $key;
	}

	$changed = false;
	/** @var list<string> */
	$affectedPaths = array();
	foreach ($candidates as $c) {
		$matches = true;
		for ($si = 0; $si < $sharedSegCount; $si++) {
			if (!isset($c['markers'][$si]) || $c['markers'][$si]['data'] !== $sharedSegData[$si]) {
				$matches = false;
				break;
			}
		}
		if (!$matches) {
			continue;
		}
		$lastShared = $c['markers'][$sharedSegCount - 1];
		$tailOff = $lastShared['off'] + $lastShared['len'];
		$tail = substr($c['orig'], $tailOff);
		$wrapper = fractal_zip_folder_jpeg_fzjs_encode($sharedKeys, $tail);
		if ($wrapper === '') {
			continue;
		}
		$check = fractal_zip_folder_jpeg_fzjs_expand_members($wrapper, $members);
		if ($check !== $c['orig']) {
			continue;
		}
		$members[$c['path']] = $wrapper;
		$affectedPaths[] = $c['path'];
		$changed = true;
	}

	if (!$changed) {
		foreach ($addedNew as $k) {
			unset($members[$k]);
		}
		return false;
	}

	// FZHM+jpeg-cm: expanding FZJS leaves restores brunsli wins; skip when that path loses.
	if (function_exists('fractal_zip_jpeg_cm_enabled') && fractal_zip_jpeg_cm_enabled()
		&& function_exists('fractal_zip_folder_jpeg_marker_share_estimate_fzhm_bytes')) {
		$estAfter = fractal_zip_folder_jpeg_marker_share_estimate_fzhm_bytes($members);
		$estBefore = fractal_zip_folder_jpeg_marker_share_estimate_fzhm_bytes($membersBefore);
		if ($estAfter > $estBefore) {
			foreach ($addedNew as $k) {
				unset($members[$k]);
			}
			foreach ($affectedPaths as $p) {
				foreach ($candidates as $c) {
					if ($c['path'] === $p) {
						$members[$p] = $c['orig'];
					}
				}
			}
			return false;
		}
	}

	if ($diskFilesByPath !== array() && function_exists('fractal_zip_classic_rebuild_archive')) {
		$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
		if (is_readable($classic)) {
			require_once $classic;
		}
		foreach ($restore as $diskPath => $spec) {
			if (!is_array($spec)) {
				continue;
			}
			if ((int) ($spec['kind'] ?? -1) !== FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
				continue;
			}
			$orig = (string) ($diskFilesByPath[(string) $diskPath] ?? '');
			if ($orig === '') {
				continue;
			}
			$names = $spec['member_names'] ?? array();
			if (!is_array($names) || $names === array()) {
				continue;
			}
			$touched = false;
			foreach ($names as $n) {
				$n = (string) $n;
				if (str_contains($n, "\x1e")) {
					$n = explode("\x1e", $n, 2)[0];
				}
				if (in_array($n, $affectedPaths, true)) {
					$touched = true;
					break;
				}
			}
			if (!$touched) {
				continue;
			}
			$payloads = array();
			$prefix = rtrim((string) $diskPath, '/') . '/';
			foreach ($names as $name) {
				$name = (string) $name;
				$blobKey = str_contains($name, "\x1e") ? explode("\x1e", $name, 2)[0] : $name;
				if (!isset($members[$blobKey])) {
					$payloads = array();
					break;
				}
				$data = fractal_zip_folder_jpeg_member_payload_for_classic(
					(string) $members[$blobKey],
					array(),
					$members
				);
				$logical = $name;
				if (str_contains($logical, "\x1e")) {
					$logical = explode("\x1e", $logical, 2)[1];
				}
				if (str_starts_with($logical, $prefix)) {
					$logical = substr($logical, strlen($prefix));
				}
				$payloads[] = array('name' => $logical, 'data' => $data);
			}
			if ($payloads === array()) {
				foreach ($addedNew as $k) {
					unset($members[$k]);
				}
				foreach ($affectedPaths as $p) {
					foreach ($candidates as $c) {
						if ($c['path'] === $p) {
							$members[$p] = $c['orig'];
						}
					}
				}
				return false;
			}
			$rebuilt = fractal_zip_classic_rebuild_archive(
				(string) ($spec['format'] ?? ''),
				(string) ($spec['meta'] ?? ''),
				$payloads
			);
			if ($rebuilt !== $orig) {
				foreach ($addedNew as $k) {
					unset($members[$k]);
				}
				foreach ($affectedPaths as $p) {
					foreach ($candidates as $c) {
						if ($c['path'] === $p) {
							$members[$p] = $c['orig'];
						}
					}
				}
				return false;
			}
		}
	}

	return true;
}

/**
 * Before FZHM per-member encode: expand FZJS JPEG leaves to full SOI bytes so jpeg-cm
 * and raster paths still see normal JPEGs; drop orphan __jpeg_mrk/* (segments live in FZJS).
 *
 * @param array<string,string> $membersForEncode
 * @param array<string,string> $membersFull logical map (includes shared segments)
 * @return array<string,string>
 */
function fractal_zip_folder_jpeg_fzhm_prepare_members(
	array $membersForEncode,
	array $membersFull
): array {
	if (!function_exists('fractal_zip_folder_jpeg_fzjs_expand_members')) {
		return $membersForEncode;
	}
	$out = array();
	foreach ($membersForEncode as $path => $bytes) {
		$path = (string) $path;
		$bytes = (string) $bytes;
		if (str_starts_with($path, '__jpeg_mrk/')) {
			continue;
		}
		if (fractal_zip_folder_jpeg_fzjs_is_wrapper($bytes)) {
			$expanded = fractal_zip_folder_jpeg_fzjs_expand_members($bytes, $membersFull);
			if ($expanded !== null) {
				$bytes = $expanded;
			}
		}
		$out[$path] = $bytes;
	}
	return $out;
}

/**
 * Rough FZHM wire estimate for admission (isolated single-file .fz sum).
 *
 * @param array<string,string> $members
 */
function fractal_zip_folder_jpeg_marker_share_estimate_fzhm_bytes(array $members): int {
	if (!class_exists('fractal_zip', false)) {
		return 0;
	}
	$sum = 0;
	$fz = new fractal_zip(300, true, true, null, true);
	foreach ($members as $path => $bytes) {
		$path = (string) $path;
		$bytes = (string) $bytes;
		if (str_starts_with($path, '__jpeg_mrk/')) {
			continue;
		}
		if (function_exists('fractal_zip_folder_jpeg_fzjs_is_wrapper')
			&& fractal_zip_folder_jpeg_fzjs_is_wrapper($bytes)
			&& function_exists('fractal_zip_folder_jpeg_fzjs_expand_members')) {
			$expanded = fractal_zip_folder_jpeg_fzjs_expand_members($bytes, $members);
			if ($expanded !== null) {
				$bytes = $expanded;
			}
		}
		$wire = $fz->encode_isolated_single_file_fzc_wire($path, $bytes);
		if (is_string($wire) && $wire !== '') {
			$sum += strlen($wire);
		}
	}
	return $sum;
}
