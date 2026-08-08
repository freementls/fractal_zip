<?php
declare(strict_types=1);

/**
 * PHASE_UNPEEL folder logical bundle: disk files → encode-target members + container restore plan.
 *
 * Expands multi-member PKZIP (mode-18 list) so one silesia.zip on disk becomes twelve logical
 * members identical to test_files133 loose layout. Restore metadata (FZHR) rebuilds original
 * disk layout on extract (verbatim default; semantic verify opt-in via bench env).
 */

/** Restore kind: member bytes already match disk path (1:1 loose files). */
const FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH = 0;
/** Rebuild PKZIP from listed logical member names (semantic deflate-9). */
const FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI = 1;
/** Store exact original container bytes on wire. */
const FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM = 2;
/** Rebuild peel-native classic archive (WAD/PAK/GRP/HOG) from logical members. */
const FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC = 3;

/**
 * Content-address shared peel payloads: first occurrence keeps its legacy path
 * (outer-codec locality); later identical payloads alias via
 * {@code existingKey\\x1elogicalName} in FZHR member_names.
 * Kill: FRACTAL_ZIP_FOLDER_SHARE_PAYLOAD=0
 */
function fractal_zip_folder_share_payload_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_SHARE_PAYLOAD');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Near-duplicate leaf share: same-size payloads within a tiny FP patch budget alias as
 * {@code existingKey\\x1elogicalName\\x1epatch} (no second full member). Distinct from
 * compress-wrapper wrapfp (rejected) — applies to peeled logical leaves only.
 * Kill: FRACTAL_ZIP_FOLDER_SHARE_NEAR_DUP=0
 */
function fractal_zip_folder_share_near_dup_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_SHARE_NEAR_DUP');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/** Cap FZHR-embedded near-dup patch so meta stays cheaper than a second leaf. */
function fractal_zip_folder_share_near_dup_patch_cap(int $len): int {
	// Large near-identical leaves often beat FZHR patches via parallel FZCL
	// (ole +3 / lz4_br +5 on batch-14 A/B); keep patches tiny and mid-size only.
	// 0.0025/40: also unlocks zip_shared patch34; still blocks ole patch45.
	return (int) max(16, min(40, (int) floor($len * 0.0025)));
}

/** Last {@code $n} slash-separated components of a member path (for tiny-share gating). */
function fractal_zip_folder_share_path_tail(string $path, int $n): string {
	$path = str_replace('\\', '/', $path);
	$path = trim($path, '/');
	if ($path === '' || $n < 1) {
		return '';
	}
	$parts = explode('/', $path);
	// Drop outer archive/compress/chunk-container filename so sibling wrappers share
	// leaves (foo.deb/control/md5sums, song_a.mid/000_MThd, tone_a.wav/000_fmt_).
	// Skip OPC/docs (.docx/.msapp/.zip).
	if (count($parts) >= 2
		&& preg_match('/\.(deb|rpm|a|gz|br|bz2|xz|zst|lz4|lzma|wav|mid|8svx|iff|ilbm)$/i', $parts[0])) {
		$parts = array_slice($parts, 1);
	}
	if (count($parts) <= $n) {
		return implode('/', $parts);
	}
	return implode('/', array_slice($parts, -$n));
}

/**
 * Register payload bytes, reusing an existing identical member when present.
 * First occurrence keeps {@code $legacyPath} (path locality for the outer codec);
 * later duplicates return {@code existingKey\\x1elogicalName}; near-duplicates may
 * return {@code existingKey\\x1elogicalName\\x1epatch}.
 *
 * @param array<string,string> $members
 * @param list<string> $addedNewKeys append-only list of keys this call newly inserted
 * @return string FZHR/lookup name
 */
function fractal_zip_folder_register_shared_payload(
	array &$members,
	string $data,
	string $logicalName,
	string $legacyPath,
	array &$addedNewKeys
): string {
	$logicalName = str_replace('\\', '/', $logicalName);
	$logicalName = ltrim($logicalName, '/');
	if ($logicalName === '' || str_contains($logicalName, '..')) {
		$members[$legacyPath] = $data;
		$addedNewKeys[] = $legacyPath;
		return $legacyPath;
	}
	if (!fractal_zip_folder_share_payload_enabled() || strlen($data) < 2) {
		$members[$legacyPath] = $data;
		$addedNewKeys[] = $legacyPath;
		return $legacyPath;
	}
	static $minShare = null;
	if ($minShare === null) {
		$e = getenv('FRACTAL_ZIP_FOLDER_SHARE_MIN_BYTES');
		if ($e === false || trim((string) $e) === '') {
			// Mid-size leaves (512–2KiB) often share across peels; 512 held slim≤3160
			// on hybrid/ole/deb/woff2/zip/wad/game_scout (2026-07 batch 13 A/B).
			$minShare = 512;
		} else {
			$minShare = max(16, (int) $e);
		}
	}
	$dataLen = strlen($data);
	// Exact duplicates: always for ≥minShare; mid band [64,minShare) so notes-scale
	// leaves (417) and BIG/PBO (172–174) are not early-returned before near scan
	// (batch 19–20: prior gaps at [112,256) then [256,512)).
	// Tiny [2,64): path-tail match after stripping outer .deb/.br/… wrappers.
	// Floor 2: hybrid `{}` stubs (batch 41).
	$allowIdent = ($dataLen >= $minShare)
		|| ($dataLen >= 64 && $dataLen < $minShare)
		|| ($dataLen >= 2 && $dataLen < 64);
	if (!$allowIdent) {
		$members[$legacyPath] = $data;
		$addedNewKeys[] = $legacyPath;
		return $legacyPath;
	}
	$wantTail = '';
	$tailN = 2;
	if ($dataLen < 64) {
		// ≤2 B: no path-tail (identical `{}` across msapp/pbix/qvf) — batch 41.
		// [16,64): no path-tail — exact share (customizations≡solution) — batch 42.
		// [3,16): 1-component tail (batch 45; was depth-2) so basename-matched tinies share.
		if ($dataLen > 2 && $dataLen < 16) {
			$tailN = 1;
			$wantTail = fractal_zip_folder_share_path_tail($legacyPath, $tailN);
		}
	}
	foreach ($members as $k => $v) {
		if (!is_string($v) || strlen($v) !== $dataLen || $v !== $data) {
			continue;
		}
		$blobKey = $k;
		if (str_contains($k, "\x1e")) {
			$blobKey = explode("\x1e", $k, 2)[0];
		}
		if ($blobKey === '' || !isset($members[$blobKey])) {
			$blobKey = $k;
		}
		// Same leaf re-registered (full path then short logical): keep blob key so
		// fzhr_share_name can emit the short archive name (no FZHR alias tax).
		if ($blobKey === $legacyPath) {
			return $blobKey;
		}
		if ($wantTail !== '' && fractal_zip_folder_share_path_tail($blobKey, $tailN) !== $wantTail) {
			continue;
		}
		return $blobKey . "\x1e" . $logicalName;
	}
	// Same-size near-dup: embed a tiny FP patch in the FZHR name instead of a second leaf.
	// Large leaves (>24KiB): default cap 16 (lz4_br); .a/.dat/.cpio may use 40 (batch 21).
	// Mid band [64,minShare): cap 12 catches PBO/BIG/notes (patch 7–12) without
	// re-enabling deb control near (patch 18) that lost on A/B (batch 18–20).
	$allowNear = ($dataLen >= $minShare)
		|| ($dataLen >= 64 && $dataLen < $minShare);
	if ($allowNear
		&& fractal_zip_folder_share_near_dup_enabled()
		&& function_exists('fractal_zip_folder_zip_patch_bytes')
		&& function_exists('fractal_zip_apply_minimal_patch_bytes')) {
		$cap = fractal_zip_folder_share_near_dup_patch_cap($dataLen);
		if ($dataLen > 24576) {
			// Default 16 keeps lz4_br/br (patch29) on outer cross-match; raise for
			// classic .a/.dat/.cpio and for gzip/xz/zst/bz2/lzma wrappers where
			// same-size inners patch ~26–29 (compress_gzip / xz_zstd) — batch 30.
			$outer = explode('/', str_replace('\\', '/', $legacyPath), 2)[0];
			if (preg_match('/\.(a|dat|cpio)$/i', $outer)) {
				$cap = min($cap, 40);
			} elseif (preg_match('/\.(gz|xz|zst|bz2|lzma)$/i', $outer)) {
				// 40: unlocks tarbz2 patch34; 29 was enough for gzip/xz text (batch 40).
				$cap = min($cap, 40);
			} else {
				$cap = min($cap, 16);
			}
		} elseif ($dataLen < $minShare) {
			$cap = 12;
		}
		foreach ($members as $k => $v) {
			if (!is_string($v) || str_contains($k, "\x1e")) {
				continue;
			}
			$vn = strlen($v);
			// Unequal-length near:
			// - ≥16KiB: batch 23 (zip/sg)
			// - [192,2048): batch 43 (hybrid payload.txt.gz↔bz2); floor 192 blocks
			//   PBO 79↔80 (batch 31 woff2). Mid [2KiB,16KiB) blocked — wad T101↔T011
			//   patch8 lost wad_shared 412→437. [96,192) same-parent (batch 44) lost
			//   classic_peel 1444→1516.
			$dLen = abs($vn - $dataLen);
			if ($dLen > 1) {
				continue;
			}
			if ($dLen > 0) {
				$mn = min($vn, $dataLen);
				if ($mn < 192 || ($mn >= 2048 && $mn < 16384)) {
					continue;
				}
			}
			$patch = fractal_zip_folder_zip_patch_bytes($v, $data);
			if ($patch === '' || strlen($patch) > $cap) {
				continue;
			}
			if (fractal_zip_apply_minimal_patch_bytes($v, $patch) !== $data) {
				continue;
			}
			return $k . "\x1e" . $logicalName . "\x1e" . $patch;
		}
	}
	// First occurrence: keep legacy path so unified/FZCL retains archive locality.
	$members[$legacyPath] = $data;
	$addedNewKeys[] = $legacyPath;
	return $legacyPath;
}

/** Prefer short archive-relative FZHR names; keep RS-alias form when sharing. */
function fractal_zip_folder_fzhr_share_name(string $registeredKey, string $shortName): string {
	return str_contains($registeredKey, "\x1e") ? $registeredKey : $shortName;
}

/**
 * Resolve a restore member name to [absPath, rebuildLogicalName, isTempFile].
 * Near-dup aliases materialize a patched temp; caller unlinks when isTemp.
 *
 * @param array<string,string> $memberFiles rel => abs path
 * @return array{0:string,1:string,2:bool}|null
 */
function fractal_zip_folder_resolve_restore_member(
	array $memberFiles,
	string $diskPath,
	string $name
): ?array {
	$name = str_replace('\\', '/', (string) $name);
	$prefix = rtrim($diskPath, '/') . '/';
	// Alias: blobKey + RS + logicalName [+ RS + FP patch for near-dup share].
	if (str_contains($name, "\x1e")) {
		$parts = explode("\x1e", $name, 3);
		$blob = (string) ($parts[0] ?? '');
		$logical = (string) ($parts[1] ?? '');
		$patch = (string) ($parts[2] ?? '');
		if ($blob === '' || $logical === '' || !isset($memberFiles[$blob])) {
			return null;
		}
		if ($patch === '') {
			return array($memberFiles[$blob], $logical, false);
		}
		if (!function_exists('fractal_zip_apply_minimal_patch_bytes')) {
			return null;
		}
		$base = @file_get_contents($memberFiles[$blob]);
		if ($base === false) {
			return null;
		}
		$out = fractal_zip_apply_minimal_patch_bytes($base, $patch);
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_near_' . getmypid()
			. '_' . bin2hex(random_bytes(6)) . '.bin';
		if (@file_put_contents($tmp, $out) === false) {
			return null;
		}
		return array($tmp, $logical, true);
	}
	if (isset($memberFiles[$name])) {
		$logical = $name;
		if (str_starts_with($name, $prefix)) {
			$logical = substr($name, strlen($prefix));
		}
		return array($memberFiles[$name], $logical, false);
	}
	if (isset($memberFiles[$prefix . $name])) {
		return array($memberFiles[$prefix . $name], $name, false);
	}
	return null;
}

/**
 * Commit a single CLASSIC-rebuild inner under a content-addressed share key.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_commit_shared_classic_inner(
	array &$members,
	array &$restore,
	string $diskPath,
	string $inner,
	string $innerName,
	string $logical,
	string $format,
	string $meta
): void {
	$added = array();
	$key = fractal_zip_folder_register_shared_payload($members, $inner, $innerName, $logical, $added);
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
		'format' => $format,
		'meta' => $meta,
		'member_names' => array($key),
	);
}

/**
 * Commit a VERBATIM wrapper whose peeled inner may still be content-shared.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_commit_shared_verbatim_inner(
	array &$members,
	array &$restore,
	string $diskPath,
	string $diskBytes,
	string $inner,
	string $innerName,
	string $logical
): void {
	$added = array();
	$key = fractal_zip_folder_register_shared_payload($members, $inner, $innerName, $logical, $added);
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
		'verbatim' => $diskBytes,
		'member_names' => array($key),
	);
}

/**
 * Minimal prefix/suffix patch: apply to semantic ZIP rebuild to recover exact original container bytes.
 *
 * @return string empty when $from === $to; else FP\x01 + pre + suf + midTo
 */
function fractal_zip_minimal_patch_bytes(string $from, string $to): string {
	if (function_exists('fractal_zip_ensure_folder_per_member_best_loaded')) {
		fractal_zip_ensure_folder_per_member_best_loaded();
	}
	if ($from === $to) {
		return '';
	}
	$nl = strlen($from);
	$ol = strlen($to);
	$pre = 0;
	while ($pre < $nl && $pre < $ol && $from[$pre] === $to[$pre]) {
		$pre++;
	}
	$suf = 0;
	while ($suf < ($nl - $pre) && $suf < ($ol - $pre) && $from[$nl - 1 - $suf] === $to[$ol - 1 - $suf]) {
		$suf++;
	}
	$midTo = substr($to, $pre, $ol - $pre - $suf);
	return 'FP' . chr(1)
		. fractal_zip_varint_u32($pre)
		. fractal_zip_varint_u32($suf)
		. fractal_zip_varint_u32(strlen($midTo))
		. $midTo;
}

/**
 * Sparse same-length patch for scattered header diffs (ZIP mtime / level).
 * Falls back to prefix/suffix when lengths differ or sparse encoding is larger.
 *
 * @return string empty when $from === $to; FP\x02 runs, or FP\x01 mid patch
 */
function fractal_zip_folder_zip_patch_bytes(string $from, string $to): string {
	if ($from === $to) {
		return '';
	}
	$nl = strlen($from);
	$ol = strlen($to);
	if ($nl === $ol && $nl > 0) {
		$runs = array();
		$i = 0;
		while ($i < $nl) {
			if ($from[$i] === $to[$i]) {
				$i++;
				continue;
			}
			$start = $i;
			while ($i < $nl && $from[$i] !== $to[$i]) {
				$i++;
			}
			$runs[] = array($start, substr($to, $start, $i - $start));
			if (count($runs) > 256) {
				$runs = array();
				break;
			}
		}
		if ($runs !== array()) {
			$parts = array('FP' . chr(2), fractal_zip_varint_u32(count($runs)));
			$payload = 0;
			foreach ($runs as $run) {
				$payload += strlen($run[1]);
				$parts[] = fractal_zip_varint_u32($run[0]);
				$parts[] = fractal_zip_varint_u32(strlen($run[1]));
				$parts[] = $run[1];
			}
			if ($payload <= max(64, (int) ($nl * 0.08)) && count($runs) <= 64) {
				$sparse = implode('', $parts);
				$minimal = fractal_zip_minimal_patch_bytes($from, $to);
				if ($minimal === '' || strlen($sparse) <= strlen($minimal)) {
					return $sparse;
				}
			}
		}
	}
	return fractal_zip_minimal_patch_bytes($from, $to);
}

function fractal_zip_apply_minimal_patch_bytes(string $from, string $patch): string {
	if (function_exists('fractal_zip_ensure_folder_per_member_best_loaded')) {
		fractal_zip_ensure_folder_per_member_best_loaded();
	}
	if ($patch === '') {
		return $from;
	}
	if (strlen($patch) < 3 || substr($patch, 0, 2) !== 'FP') {
		fractal_zip::fatal_error('Corrupt folder ZIP patch.');
	}
	$ver = ord($patch[2]);
	if ($ver === 2) {
		$off = 3;
		$n = strlen($patch);
		$nRuns = fractal_zip_varint_u32_read($patch, $off, $n, 'ZIP sparse run count');
		$out = $from;
		$outLen = strlen($out);
		for ($r = 0; $r < $nRuns; $r++) {
			$at = fractal_zip_varint_u32_read($patch, $off, $n, 'ZIP sparse offset');
			$ml = fractal_zip_varint_u32_read($patch, $off, $n, 'ZIP sparse len');
			if ($ml < 1 || $off + $ml > $n || $at < 0 || $at + $ml > $outLen) {
				fractal_zip::fatal_error('Corrupt folder ZIP sparse patch.');
			}
			$out = substr($out, 0, $at) . substr($patch, $off, $ml) . substr($out, $at + $ml);
			$off += $ml;
		}
		return $out;
	}
	if ($ver !== 1) {
		fractal_zip::fatal_error('Corrupt folder ZIP patch.');
	}
	$off = 3;
	$n = strlen($patch);
	$pre = fractal_zip_varint_u32_read($patch, $off, $n, 'ZIP patch pre');
	$suf = fractal_zip_varint_u32_read($patch, $off, $n, 'ZIP patch suf');
	$ml = fractal_zip_varint_u32_read($patch, $off, $n, 'ZIP patch mid len');
	if ($ml < 0 || $off + $ml > $n) {
		fractal_zip::fatal_error('Corrupt folder ZIP patch (mid).');
	}
	$mid = substr($patch, $off, $ml);
	$nl = strlen($from);
	if ($pre + $suf > $nl) {
		fractal_zip::fatal_error('Corrupt folder ZIP patch (bounds).');
	}
	return substr($from, 0, $pre) . $mid . ($suf > 0 ? substr($from, $nl - $suf) : '');
}

/**
 * True when peeled member payloads match the original PKZIP listing (lossless peel).
 *
 * @param list<array{name: string, data: string}> $zipMembers
 */
function fractal_zip_folder_zip_peel_is_lossless(string $originalZip, array $zipMembers): bool {
	$peelMaxRaw = function_exists('fractal_zip_folder_zip_peel_max_raw_bytes')
		? fractal_zip_folder_zip_peel_max_raw_bytes()
		: null;
	$listed = fractal_zip_literal_pac_list_zip_members_for_mode18($originalZip, $peelMaxRaw);
	if ($listed === null || count($listed) !== count($zipMembers)) {
		return false;
	}
	$byName = array();
	foreach ($zipMembers as $m) {
		$byName[(string) $m['name']] = (string) $m['data'];
	}
	foreach ($listed as $m) {
		$name = (string) $m['name'];
		if (!isset($byName[$name]) || $byName[$name] !== (string) $m['data']) {
			return false;
		}
	}
	return true;
}

/**
 * Expand OLE/CFB into logical stream members for folder encode.
 * Prefer CLASSIC template rebuild (zeroed non-stream bytes + stream payloads);
 * else VERBATIM.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 * @return bool true when expanded
 */
function fractal_zip_folder_ole_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_OLE_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Folder-level OLE near-duplicate peel: when several same-size CFB docs differ by a
 * tiny FP patch, store one PASSTHROUGH base + CLASSIC `fp` restores (patch in FZHR meta).
 * Beats opaque FZb1 once ≥4 near-identical siblings (stream peel alone loses to opaque).
 *
 * @param array<string,string> $diskFilesByPath
 * @param array<string,string> $members
 * @param array<string,array> $restore
 * @param array<string,true> $handledPaths
 */
function fractal_zip_folder_apply_ole_near_dup_patches(
	array $diskFilesByPath,
	array &$members,
	array &$restore,
	array &$handledPaths
): bool {
	if (!fractal_zip_folder_ole_peel_enabled()) {
		return false;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_OLE_NEAR_DUP');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
			return false;
		}
	}
	if (!function_exists('fractal_zip_ole_is_compound')) {
		$ole = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_ole_cfb.php';
		if (is_readable($ole)) {
			require_once $ole;
		}
	}
	if (!function_exists('fractal_zip_ole_is_compound')
		|| !function_exists('fractal_zip_folder_zip_patch_bytes')
		|| !function_exists('fractal_zip_apply_minimal_patch_bytes')
		|| !function_exists('fractal_zip_classic_rebuild_archive')) {
		return false;
	}
	$byLen = array();
	foreach ($diskFilesByPath as $path => $bytes) {
		$path = str_replace('\\', '/', (string) $path);
		$bytes = (string) $bytes;
		if ($path === '' || str_contains($path, '..') || strlen($bytes) < 512) {
			continue;
		}
		if (!fractal_zip_ole_is_compound($bytes)) {
			continue;
		}
		$byLen[strlen($bytes)][$path] = $bytes;
	}
	$changed = false;
	foreach ($byLen as $len => $group) {
		if (count($group) < 2) {
			continue;
		}
		$paths = array_keys($group);
		sort($paths, SORT_STRING);
		$canon = $paths[0];
		$base = $group[$canon];
		// Cap: keep FZHR meta tiny so FZCL can beat opaque cross-file compression.
		$cap = (int) max(32, min(256, (int) floor($len * 0.005)));
		$siblings = array();
		foreach ($paths as $p) {
			if ($p === $canon) {
				continue;
			}
			$patch = fractal_zip_folder_zip_patch_bytes($base, $group[$p]);
			if (strlen($patch) > $cap) {
				continue;
			}
			$check = fractal_zip_apply_minimal_patch_bytes($base, $patch);
			if ($check !== $group[$p]) {
				continue;
			}
			$payloads = array(array('name' => $canon, 'data' => $base));
			$reb = fractal_zip_classic_rebuild_archive('fp', $patch, $payloads);
			if ($reb !== $group[$p]) {
				continue;
			}
			$siblings[$p] = $patch;
		}
		if ($siblings === array()) {
			continue;
		}
		$members[$canon] = $base;
		$restore[$canon] = array('kind' => FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		$handledPaths[$canon] = true;
		foreach ($siblings as $p => $patch) {
			$restore[$p] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'fp',
				'meta' => $patch,
				'member_names' => array($canon),
			);
			$handledPaths[$p] = true;
		}
		$changed = true;
	}
	return $changed;
}

/**
 * Folder-level compress-wrapper near-dup (scoped wrapfp): same-size .lz4 siblings
 * with a tiny FP patch become one PASSTHROUGH base + CLASSIC {@code fp} restores.
 * Peel+leaf share already covers .br/.gz/…; .lz4 frames often differ by tens of
 * bytes while expanded inners stay large (batch 22). Kill: FRACTAL_ZIP_FOLDER_WRAPPER_NEAR_DUP=0
 *
 * @param array<string,string> $diskFilesByPath
 * @param array<string,string> $members
 * @param array<string,array> $restore
 * @param array<string,true> $handledPaths
 */
function fractal_zip_folder_apply_wrapper_near_dup_patches(
	array $diskFilesByPath,
	array &$members,
	array &$restore,
	array &$handledPaths
): bool {
	// Default OFF: A/B lost ole 782→1186, lz4 440→609, midi 391→482 (batch 22).
	// Opt-in: FRACTAL_ZIP_FOLDER_WRAPPER_NEAR_DUP=1
	$e = getenv('FRACTAL_ZIP_FOLDER_WRAPPER_NEAR_DUP');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
		return false;
	}
	if (!($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes')) {
		return false;
	}
	if (!function_exists('fractal_zip_folder_zip_patch_bytes')
		|| !function_exists('fractal_zip_apply_minimal_patch_bytes')
		|| !function_exists('fractal_zip_classic_rebuild_archive')) {
		return false;
	}
	$byLen = array();
	foreach ($diskFilesByPath as $path => $bytes) {
		$path = str_replace('\\', '/', (string) $path);
		$bytes = (string) $bytes;
		if ($path === '' || str_contains($path, '..') || isset($handledPaths[$path])) {
			continue;
		}
		// lz4 only: other codecs' frames usually patch≈full size (batch 22 probe).
		if (!preg_match('/\.lz4$/i', $path)) {
			continue;
		}
		$n = strlen($bytes);
		if ($n < 64 || $n > 256 * 1024) {
			continue;
		}
		$byLen[$n][$path] = $bytes;
	}
	$changed = false;
	foreach ($byLen as $len => $group) {
		if (count($group) < 2) {
			continue;
		}
		$paths = array_keys($group);
		sort($paths, SORT_STRING);
		$canon = $paths[0];
		$base = $group[$canon];
		$cap = (int) max(16, min(32, (int) floor($len * 0.05)));
		$siblings = array();
		foreach ($paths as $p) {
			if ($p === $canon) {
				continue;
			}
			$patch = fractal_zip_folder_zip_patch_bytes($base, $group[$p]);
			if ($patch === '' || strlen($patch) > $cap) {
				continue;
			}
			if (fractal_zip_apply_minimal_patch_bytes($base, $patch) !== $group[$p]) {
				continue;
			}
			$reb = fractal_zip_classic_rebuild_archive('fp', $patch, array(
				array('name' => $canon, 'data' => $base),
			));
			if ($reb !== $group[$p]) {
				continue;
			}
			$siblings[$p] = $patch;
		}
		if ($siblings === array()) {
			continue;
		}
		$members[$canon] = $base;
		$restore[$canon] = array('kind' => FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		$handledPaths[$canon] = true;
		foreach ($siblings as $p => $patch) {
			$restore[$p] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'fp',
				'meta' => $patch,
				'member_names' => array($canon),
			);
			$handledPaths[$p] = true;
		}
		$changed = true;
	}
	return $changed;
}

/**
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_ole(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_ole_peel_enabled()) {
		return false;
	}
	if (!function_exists('fractal_zip_ole_is_compound')) {
		$ole = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_ole_cfb.php';
		if (is_readable($ole)) {
			require_once $ole;
		}
	}
	if (!function_exists('fractal_zip_ole_is_compound') || !fractal_zip_ole_is_compound($diskBytes)) {
		return false;
	}
	if (!function_exists('fractal_zip_ole_build_template_and_streams')) {
		return false;
	}
	$parsed = fractal_zip_ole_build_template_and_streams($diskBytes);
	if ($parsed === null || !isset($parsed['streams']) || !is_array($parsed['streams'])) {
		return false;
	}
	$streams = $parsed['streams'];
	if (count($streams) < 2) {
		return false;
	}
	$cap = 64;
	$eCap = getenv('FRACTAL_ZIP_FOLDER_OLE_MAX_STREAMS');
	if ($eCap !== false && (int) $eCap > 0) {
		$cap = max(2, (int) $eCap);
	}
	$templateRaw = (string) ($parsed['template'] ?? '');
	if ($templateRaw === '' || strlen($templateRaw) !== strlen($diskBytes)) {
		return false;
	}
	// Sparse-pack mostly-zero CFB skeleton so CLASSIC peel beats opaque FZb1.
	$template = function_exists('fractal_zip_ole_pack_sparse_template')
		? fractal_zip_ole_pack_sparse_template($templateRaw)
		: $templateRaw;

	$prefix = rtrim($diskPath, '/') . '/';
	$memberNames = array();
	$rangeMeta = array();
	$rebuildStreams = array();
	$n = 0;
	foreach ($streams as $st) {
		if ($n >= $cap) {
			break;
		}
		$name = str_replace('\\', '/', (string) ($st['name'] ?? ''));
		$data = (string) ($st['data'] ?? '');
		$ranges = $st['ranges'] ?? null;
		if ($name === '' || $data === '' || !is_array($ranges) || $ranges === array()) {
			continue;
		}
		if (str_contains($name, '..')) {
			continue;
		}
		// Logical member path must be filesystem-safe (OLE names often have \x01/\x05 prefixes).
		$safe = preg_replace('/[^A-Za-z0-9._+-]+/', '_', $name) ?: 'stream';
		$safe = sprintf('%03d_%s', $n, $safe);
		// Content-address streams so sibling docs share one blob in FZCL.
		// Prefer 16-hex names (FZHR path tax); fall back to full sha256 on collision.
		$fullSha = hash('sha256', $data);
		$streamPath = '__ole_stream/' . substr($fullSha, 0, 16);
		if (isset($members[$streamPath]) && $members[$streamPath] !== $data) {
			$streamPath = '__ole_stream/' . $fullSha;
		}
		$members[$streamPath] = $data;
		$memberNames[] = $streamPath;
		$rangeMeta[] = array('name' => $name, 'safe' => $safe, 'ranges' => $ranges);
		$rebuildStreams[] = array('name' => $name, 'data' => $data, 'ranges' => $ranges);
		$n++;
	}
	if (count($memberNames) < 2) {
		foreach ($memberNames as $mn) {
			unset($members[$mn]);
		}
		return false;
	}

	// Prefer CLASSIC when template+streams bit-exactly rebuild the container.
	// Sparse template is content-addressed (`__ole_tmpl/<sha256>`) so multi-doc
	// peels share one skeleton member instead of repeating it per file.
	if (function_exists('fractal_zip_ole_rebuild_from_template')) {
		$rebuilt = fractal_zip_ole_rebuild_from_template($template, $rebuildStreams);
		if ($rebuilt !== null && $rebuilt === $diskBytes) {
			$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
			if (is_readable($classic)) {
				require_once $classic;
			}
			$sha = hash('sha256', $template);
			$tmplShared = '__ole_tmpl/' . $sha;
			$tmplStore = $template;
			$tmplGz = false;
			if (function_exists('gzcompress')) {
				$gz = @gzcompress($template, 9);
				if (is_string($gz) && $gz !== '' && strlen($gz) < strlen($template)) {
					$tmplStore = $gz;
					$tmplGz = true;
				}
			}
			$members[$tmplShared] = $tmplStore;
			$namesWithTmpl = $memberNames;
			array_unshift($namesWithTmpl, $tmplShared);
			$meta = '';
			if (function_exists('fractal_zip_classic_ole_fzol_encode')) {
				$meta = fractal_zip_classic_ole_fzol_encode($sha, $rangeMeta, $tmplGz);
			}
			$metaJson = json_encode(array(
				'tmpl_sha' => $sha,
				'streams' => $rangeMeta,
			), JSON_UNESCAPED_SLASHES);
			// Prefer compact FZOL when it wins; keep JSON as fallback for old decoders via size.
			if (!is_string($meta) || $meta === ''
				|| (is_string($metaJson) && strlen($meta) >= strlen($metaJson))) {
				$meta = is_string($metaJson) ? $metaJson : '';
				// JSON path cannot signal gzipped template — store raw.
				if ($tmplGz) {
					$members[$tmplShared] = $template;
					$tmplGz = false;
				}
			}
			if ($meta !== '' && function_exists('fractal_zip_classic_rebuild_archive')) {
				$payloads = array(
					array('name' => $tmplShared, 'data' => $members[$tmplShared]),
				);
				foreach ($memberNames as $mn) {
					// Keep content-addressed paths intact (`__ole_stream/<sha>`).
					$payloads[] = array('name' => $mn, 'data' => $members[$mn]);
				}
				$check = fractal_zip_classic_rebuild_archive('ole', $meta, $payloads);
				if ($check !== null && $check === $diskBytes) {
					$restore[$diskPath] = array(
						'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
						'format' => 'ole',
						'meta' => $meta,
						'member_names' => $namesWithTmpl,
					);
					return true;
				}
			}
			unset($members[$tmplShared]);
			// Legacy fallback: per-doc template member + streams-only meta list.
			$tmplLogical = $prefix . '_ole_template';
			$members[$tmplLogical] = $template;
			array_unshift($memberNames, $tmplLogical);
			$metaLegacy = json_encode($rangeMeta, JSON_UNESCAPED_SLASHES);
			if (is_string($metaLegacy) && $metaLegacy !== '' && function_exists('fractal_zip_classic_rebuild_archive')) {
				$payloads = array();
				foreach ($memberNames as $mn) {
					$rel = str_starts_with($mn, $prefix) ? substr($mn, strlen($prefix)) : basename($mn);
					$payloads[] = array('name' => $rel, 'data' => $members[$mn]);
				}
				$check = fractal_zip_classic_rebuild_archive('ole', $metaLegacy, $payloads);
				if ($check !== null && $check === $diskBytes) {
					$restore[$diskPath] = array(
						'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
						'format' => 'ole',
						'meta' => $metaLegacy,
						'member_names' => $memberNames,
					);
					return true;
				}
			}
			unset($members[$tmplLogical]);
			array_shift($memberNames);
		}
	}

	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
		'verbatim' => $diskBytes,
		'member_names' => $memberNames,
	);
	return true;
}

function fractal_zip_folder_text_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_TEXT_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Tiny stubs: CLASSIC FZHR tax usually loses to PASSTHROUGH on small corpora.
 * Keep peels for denser real-world JSON/CSV/INI/line files.
 */
function fractal_zip_folder_text_classic_worth(int $diskLen, int $nMembers): bool {
	// Absolute floor — FZHR format/meta/names dominate below this.
	if ($diskLen < 1024) {
		return false;
	}
	// Two-member splits (csv header/body) need more substance to beat PASSTHROUGH.
	if ($nMembers < 3 && $diskLen < 4096) {
		return false;
	}
	return true;
}

/**
 * Peel JSON/XML/CSV/INI/line-oriented text. Prefer bit-exact CLASSIC when rebuild matches;
 * VERBATIM peels collapse to PASSTHROUGH anyway, so only keep them for XML/NDJSON locality.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_text(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_text_peel_enabled() || $diskBytes === '' || strlen($diskBytes) > 8 * 1024 * 1024) {
		return false;
	}
	$base = strtolower(basename($diskPath));
	$ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
	$trim = ltrim($diskBytes);
	$names = array();
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}

	// JSON object/array → CLASSIC when json_encode(flags) matches bit-exactly.
	$jsonExt = in_array($ext, array('json', 'har', 'gltf', 'geojson', 'topojson', 'ipynb'), true);
	$looksJson = ($trim[0] ?? '') === '{' || (($trim[0] ?? '') === '[' && $jsonExt);
	if ($jsonExt || $looksJson) {
		$decoded = json_decode($diskBytes, true);
		if (!is_array($decoded)) {
			if ($jsonExt) {
				return false;
			}
			// Non-json ext that looked brace-like — fall through.
		} else {
			$flagSets = array(
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
				JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT,
				JSON_PRETTY_PRINT,
				JSON_UNESCAPED_SLASHES,
				JSON_UNESCAPED_UNICODE,
				0,
			);
			$matchFlags = null;
			foreach ($flagSets as $f) {
				$enc = json_encode($decoded, $f);
				if (is_string($enc) && $enc === $diskBytes) {
					$matchFlags = $f;
					break;
				}
			}
			if (!function_exists('fractal_zip_classic_rebuild_archive')) {
				return false;
			}

			// Jupyter: peel cells individually when dense enough (beats top-level JSON peel).
			// Use object decode so empty JSON objects (`{}`) round-trip (assoc would make `[]`).
			// Runs even when assoc json_encode cannot match (empty `{}` → `[]`).
			if ($ext === 'ipynb') {
				$nbObj = json_decode($diskBytes);
				$ipynbFlags = null;
				if (is_object($nbObj) && isset($nbObj->cells) && is_array($nbObj->cells)) {
					foreach ($flagSets as $f) {
						$enc = json_encode($nbObj, $f);
						if (is_string($enc) && $enc === $diskBytes) {
							$ipynbFlags = $f;
							break;
						}
					}
				}
				if ($ipynbFlags !== null
					&& count($nbObj->cells) >= 4
					&& fractal_zip_folder_text_classic_worth(strlen($diskBytes), count($nbObj->cells) + 2)
				) {
					$keys = array_keys(get_object_vars($nbObj));
					$payloads = array();
					$names = array();
					$addedNew = array();
					$useShare = function_exists('fractal_zip_folder_register_shared_payload');
					$cellCap = 128;
					$ci = 0;
					$payloadFlags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
					foreach ($keys as $key) {
						$key = (string) $key;
						if ($key === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $key)) {
							$payloads = array();
							break;
						}
						if ($key === 'cells') {
							foreach ($nbObj->cells as $cell) {
								if ($ci >= $cellCap) {
									$payloads = array();
									break 2;
								}
								$payload = json_encode($cell, $payloadFlags);
								if (!is_string($payload)) {
									$payloads = array();
									break 2;
								}
								$short = sprintf('cell_%04d', $ci);
								$logical = rtrim($diskPath, '/') . '/' . $short;
								if ($useShare) {
									$reg = fractal_zip_folder_register_shared_payload(
										$members, $payload, $short, $logical, $addedNew
									);
									$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
								} else {
									$members[$logical] = $payload;
									$addedNew[] = $logical;
									$names[] = $logical;
								}
								$payloads[] = array('name' => $short, 'data' => $payload);
								$ci++;
							}
							continue;
						}
						$payload = json_encode($nbObj->{$key}, $payloadFlags);
						if (!is_string($payload)) {
							$payloads = array();
							break;
						}
						$logical = rtrim($diskPath, '/') . '/' . $key;
						if ($useShare) {
							$reg = fractal_zip_folder_register_shared_payload(
								$members, $payload, $key, $logical, $addedNew
							);
							$names[] = fractal_zip_folder_fzhr_share_name($reg, $key);
						} else {
							$members[$logical] = $payload;
							$addedNew[] = $logical;
							$names[] = $logical;
						}
						$payloads[] = array('name' => $key, 'data' => $payload);
					}
					if ($payloads !== array() && $ci >= 4) {
						$meta = (string) (int) $ipynbFlags . "\0" . implode("\0", $keys);
						$rebuilt = fractal_zip_classic_rebuild_archive('ipynb', $meta, $payloads);
						if ($rebuilt !== null && $rebuilt === $diskBytes) {
							$restore[$diskPath] = array(
								'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
								'format' => 'ipynb',
								'meta' => $meta,
								'member_names' => $names,
							);
							return true;
						}
					}
					foreach ($addedNew as $n) {
						unset($members[$n]);
					}
					$names = array();
				}
			}

			if ($matchFlags === null) {
				return false;
			}

			$payloads = array();
			$names = array();
			$addedNew = array();
			$useShare = function_exists('fractal_zip_folder_register_shared_payload');
			$keyCap = 48;
			$i = 0;
			$seen = array();
			foreach ($decoded as $k => $v) {
				if ($i >= $keyCap) {
					foreach ($addedNew as $n) {
						unset($members[$n]);
					}
					return false;
				}
				if (!is_string($k) && !is_int($k)) {
					foreach ($addedNew as $n) {
						unset($members[$n]);
					}
					return false;
				}
				$key = (string) $k;
				if ($key === '' || !preg_match('/^[A-Za-z0-9._-]+$/', $key) || isset($seen[$key])) {
					foreach ($addedNew as $n) {
						unset($members[$n]);
					}
					return false;
				}
				$seen[$key] = true;
				$payload = json_encode($v, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
				if (!is_string($payload)) {
					foreach ($addedNew as $n) {
						unset($members[$n]);
					}
					return false;
				}
				$logical = rtrim($diskPath, '/') . '/' . $key;
				if ($useShare) {
					$reg = fractal_zip_folder_register_shared_payload(
						$members, $payload, $key, $logical, $addedNew
					);
					$names[] = fractal_zip_folder_fzhr_share_name($reg, $key);
				} else {
					$members[$logical] = $payload;
					$addedNew[] = $logical;
					$names[] = $logical;
				}
				$payloads[] = array('name' => $key, 'data' => $payload);
				$i++;
			}
			if ($names === array() || !fractal_zip_folder_text_classic_worth(strlen($diskBytes), count($names))) {
				foreach ($addedNew as $n) {
					unset($members[$n]);
				}
				return false;
			}
			$meta = (string) (int) $matchFlags;
			$rebuilt = fractal_zip_classic_rebuild_archive('json', $meta, $payloads);
			if ($rebuilt === null || $rebuilt !== $diskBytes) {
				foreach ($addedNew as $n) {
					unset($members[$n]);
				}
				return false;
			}
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'json',
				'meta' => $meta,
				'member_names' => $names,
			);
			return true;
		}
	}

	// XML → document.xml + optional root child snippets.
	if (in_array($ext, array('xml', 'svg', 'xhtml', 'plist', 'musicxml', 'drawio', 'xmp'), true)
		|| str_starts_with($trim, '<?xml') || str_starts_with($trim, '<')) {
		if (!str_contains($trim, '<')) {
			return false;
		}
		$names = array();
		$addedNew = array();
		$useShare = function_exists('fractal_zip_folder_register_shared_payload');
		$doc = rtrim($diskPath, '/') . '/document.xml';
		if ($useShare) {
			$reg = fractal_zip_folder_register_shared_payload(
				$members, $diskBytes, 'document.xml', $doc, $addedNew
			);
			$names[] = fractal_zip_folder_fzhr_share_name($reg, 'document.xml');
		} else {
			$members[$doc] = $diskBytes;
			$addedNew[] = $doc;
			$names[] = $doc;
		}
		if (class_exists('DOMDocument')) {
			$prev = libxml_use_internal_errors(true);
			$dom = new DOMDocument();
			$ok = @$dom->loadXML($diskBytes, LIBXML_NONET | LIBXML_COMPACT);
			libxml_clear_errors();
			libxml_use_internal_errors($prev);
			if ($ok && $dom->documentElement !== null) {
				$childCap = 32;
				$ci = 0;
				foreach ($dom->documentElement->childNodes as $child) {
					if ($ci >= $childCap) {
						break;
					}
					if (!($child instanceof DOMElement)) {
						continue;
					}
					$tag = preg_replace('/[^A-Za-z0-9._-]+/', '_', $child->tagName) ?: 'node';
					$frag = $dom->saveXML($child);
					if (!is_string($frag) || $frag === '') {
						continue;
					}
					$short = sprintf('%02d_%s', $ci + 1, $tag) . '.xml';
					$logical = rtrim($diskPath, '/') . '/nodes/' . $short;
					if ($useShare) {
						$reg = fractal_zip_folder_register_shared_payload(
							$members, $frag, $short, $logical, $addedNew
						);
						$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
					} else {
						$members[$logical] = $frag;
						$addedNew[] = $logical;
						$names[] = $logical;
					}
					$ci++;
				}
			}
		}
		$restore[$diskPath] = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
			'verbatim' => $diskBytes,
			'member_names' => $names,
		);
		return count($names) >= 1;
	}

	// NDJSON / JSONL → per-line JSON members.
	if (in_array($ext, array('ndjson', 'jsonl'), true)) {
		$lines = preg_split('/\R/', $diskBytes) ?: array();
		$cap = 128;
		$li = 0;
		$names = array();
		$addedNew = array();
		$useShare = function_exists('fractal_zip_folder_register_shared_payload');
		foreach ($lines as $line) {
			if ($li >= $cap) {
				break;
			}
			$line = trim($line);
			if ($line === '') {
				continue;
			}
			$short = sprintf('%04d.json', $li + 1);
			$logical = rtrim($diskPath, '/') . '/lines/' . $short;
			if ($useShare) {
				$reg = fractal_zip_folder_register_shared_payload(
					$members, $line, $short, $logical, $addedNew
				);
				$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
			} else {
				$members[$logical] = $line;
				$addedNew[] = $logical;
				$names[] = $logical;
			}
			$li++;
		}
		if (count($names) < 2) {
			foreach ($addedNew as $n) {
				unset($members[$n]);
			}
			return false;
		}
		$restore[$diskPath] = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
			'verbatim' => $diskBytes,
			'member_names' => $names,
		);
		return true;
	}

	// CSV / TSV → CLASSIC columnar peel when dense + unquoted; else header+body if worth it.
	if (in_array($ext, array('csv', 'tsv'), true) && function_exists('fractal_zip_classic_rebuild_archive')) {
		$eol = str_contains($diskBytes, "\r\n") ? "\r\n" : "\n";
		$sep = ($ext === 'tsv') ? "\t" : ',';
		// Quoted CSV needs a real parser — skip CLASSIC (PASSTHROUGH).
		if (str_contains($diskBytes, '"')) {
			return false;
		}
		$trimRight = $diskBytes;
		$trailingEol = false;
		if (str_ends_with($trimRight, $eol)) {
			$trailingEol = true;
			$trimRight = substr($trimRight, 0, -strlen($eol));
		}
		$lines = ($trimRight === '') ? array() : explode($eol, $trimRight);
		if (count($lines) < 3) {
			return false;
		}
		$header = (string) $lines[0];
		$hdrCells = explode($sep, $header);
		$ncol = count($hdrCells);
		if ($ncol < 2 || $ncol > 64) {
			return false;
		}
		$cols = array_fill(0, $ncol, array());
		for ($i = 1; $i < count($lines); $i++) {
			$cells = explode($sep, $lines[$i]);
			if (count($cells) !== $ncol) {
				return false;
			}
			for ($c = 0; $c < $ncol; $c++) {
				$cols[$c][] = $cells[$c];
			}
		}
		// Prefer columnar when enough rows; FZHR names cost otherwise.
		// Folder hetero often loses on ~1KB stubs even when single-file is flat/slightly better.
		// Keep ≥2048: 1024 unlocked hybrid rows.csv but slim 3160→3530 (batch 23 reject).
		$nrows = count($lines) - 1;
		if ($nrows >= 48 && strlen($diskBytes) >= 2048) {
			$payloads = array(array('name' => 'header', 'data' => $header));
			$names = array();
			$addedNew = array();
			$useShare = function_exists('fractal_zip_folder_register_shared_payload');
			$hPath = rtrim($diskPath, '/') . '/header';
			if ($useShare) {
				$reg = fractal_zip_folder_register_shared_payload(
					$members, $header, 'header', $hPath, $addedNew
				);
				$names[] = fractal_zip_folder_fzhr_share_name($reg, 'header');
			} else {
				$members[$hPath] = $header;
				$addedNew[] = $hPath;
				$names[] = $hPath;
			}
			for ($c = 0; $c < $ncol; $c++) {
				$safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $hdrCells[$c]) ?: ('c' . $c);
				$short = 'col_' . sprintf('%02d_%s', $c, $safe);
				$blob = implode("\n", $cols[$c]);
				$logical = rtrim($diskPath, '/') . '/' . $short;
				if ($useShare) {
					$reg = fractal_zip_folder_register_shared_payload(
						$members, $blob, $short, $logical, $addedNew
					);
					$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
				} else {
					$members[$logical] = $blob;
					$addedNew[] = $logical;
					$names[] = $logical;
				}
				$payloads[] = array('name' => $short, 'data' => $blob);
			}
			// Rebuild always emits trailing eol after each data row (including last).
			if (!$trailingEol) {
				foreach ($addedNew as $n) {
					unset($members[$n]);
				}
				return false;
			}
			$rebuilt = fractal_zip_classic_rebuild_archive('csvcols', $eol . $sep, $payloads);
			if ($rebuilt !== null && $rebuilt === $diskBytes) {
				$restore[$diskPath] = array(
					'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
					'format' => 'csvcols',
					'meta' => $eol . $sep,
					'member_names' => $names,
				);
				return true;
			}
			foreach ($addedNew as $n) {
				unset($members[$n]);
			}
		}
		// Fallback: header + body CLASSIC when large enough.
		$pos = strpos($diskBytes, $eol);
		if ($pos === false || $pos < 1) {
			return false;
		}
		$h = substr($diskBytes, 0, $pos);
		$body = substr($diskBytes, $pos + strlen($eol));
		if ($h === '' || $body === '' || !fractal_zip_folder_text_classic_worth(strlen($diskBytes), 2)) {
			return false;
		}
		$hPath = rtrim($diskPath, '/') . '/header';
		$bPath = rtrim($diskPath, '/') . '/body';
		$payloads = array(
			array('name' => 'header', 'data' => $h),
			array('name' => 'body', 'data' => $body),
		);
		$rebuilt = fractal_zip_classic_rebuild_archive('csv', $eol, $payloads);
		if ($rebuilt === null || $rebuilt !== $diskBytes) {
			return false;
		}
		$addedNew = array();
		$useShare = function_exists('fractal_zip_folder_register_shared_payload');
		if ($useShare) {
			$hKey = fractal_zip_folder_register_shared_payload(
				$members, $h, 'header', $hPath, $addedNew
			);
			$bKey = fractal_zip_folder_register_shared_payload(
				$members, $body, 'body', $bPath, $addedNew
			);
			$fzhr = array(
				fractal_zip_folder_fzhr_share_name($hKey, 'header'),
				fractal_zip_folder_fzhr_share_name($bKey, 'body'),
			);
		} else {
			$members[$hPath] = $h;
			$members[$bPath] = $body;
			$fzhr = array($hPath, $bPath);
		}
		$restore[$diskPath] = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
			'format' => 'csv',
			'meta' => $eol,
			'member_names' => $fzhr,
		);
		return true;
	}

	// INI / CFG → CLASSIC exact [section] blobs (no trim).
	if (in_array($ext, array('ini', 'cfg', 'conf'), true) && function_exists('fractal_zip_classic_rebuild_archive')) {
		if (!str_contains($diskBytes, '[')) {
			return false;
		}
		$sections = preg_split('/(?=^\[)/m', $diskBytes) ?: array();
		$sections = array_values(array_filter($sections, static fn($s) => $s !== ''));
		if (count($sections) < 2) {
			return false;
		}
		if (!fractal_zip_folder_text_classic_worth(strlen($diskBytes), count($sections))) {
			return false;
		}
		$payloads = array();
		$names = array();
		$addedNew = array();
		$useShare = function_exists('fractal_zip_folder_register_shared_payload');
		$si = 0;
		foreach ($sections as $sec) {
			if ($si >= 48) {
				foreach ($addedNew as $n) {
					unset($members[$n]);
				}
				return false;
			}
			$short = 'sec_' . sprintf('%02d', $si + 1);
			$logical = rtrim($diskPath, '/') . '/' . $short;
			if ($useShare) {
				$reg = fractal_zip_folder_register_shared_payload(
					$members, $sec, $short, $logical, $addedNew
				);
				$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
			} else {
				$members[$logical] = $sec;
				$addedNew[] = $logical;
				$names[] = $logical;
			}
			$payloads[] = array('name' => $short, 'data' => $sec);
			$si++;
		}
		$rebuilt = fractal_zip_classic_rebuild_archive('ini', '', $payloads);
		if ($rebuilt === null || $rebuilt !== $diskBytes) {
			foreach ($addedNew as $n) {
				unset($members[$n]);
			}
			return false;
		}
		$restore[$diskPath] = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
			'format' => 'ini',
			'meta' => '',
			'member_names' => $names,
		);
		return true;
	}

	// YAML / TOML → document + section splits (VERBATIM; collapses to PASSTHROUGH).
	if (in_array($ext, array('yaml', 'yml', 'toml'), true)) {
		$names = array();
		$addedNew = array();
		$useShare = function_exists('fractal_zip_folder_register_shared_payload');
		$docShort = 'document.' . ($ext === 'yml' ? 'yaml' : $ext);
		$doc = rtrim($diskPath, '/') . '/' . $docShort;
		if ($useShare) {
			$reg = fractal_zip_folder_register_shared_payload(
				$members, $diskBytes, $docShort, $doc, $addedNew
			);
			$names[] = fractal_zip_folder_fzhr_share_name($reg, $docShort);
		} else {
			$members[$doc] = $diskBytes;
			$addedNew[] = $doc;
			$names[] = $doc;
		}
		$sections = preg_split('/(?=^\[.+\]\s*$)/m', $diskBytes) ?: array();
		if (count($sections) < 2 && ($ext === 'yaml' || $ext === 'yml')) {
			$sections = preg_split('/(?=^[A-Za-z0-9_.-]+:\s*$)/m', $diskBytes) ?: array($diskBytes);
		}
		$si = 0;
		foreach ($sections as $sec) {
			if ($si >= 48) {
				break;
			}
			$sec = trim($sec);
			if ($sec === '' || strlen($sec) < 3) {
				continue;
			}
			$data = $sec . "\n";
			$short = sprintf('%02d.txt', $si + 1);
			$logical = rtrim($diskPath, '/') . '/sections/' . $short;
			if ($useShare) {
				$reg = fractal_zip_folder_register_shared_payload(
					$members, $data, $short, $logical, $addedNew
				);
				$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
			} else {
				$members[$logical] = $data;
				$addedNew[] = $logical;
				$names[] = $logical;
			}
			$si++;
		}
		$restore[$diskPath] = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
			'verbatim' => $diskBytes,
			'member_names' => $names,
		);
		return count($names) >= 1;
	}

	// BAT / XPM / VMF → CLASSIC line join when dense enough.
	if (in_array($ext, array('bat', 'cmd', 'xpm', 'vmf', 'vmt'), true) && function_exists('fractal_zip_classic_rebuild_archive')) {
		$eol = str_contains($diskBytes, "\r\n") ? "\r\n" : "\n";
		$parts = explode($eol, $diskBytes);
		if (count($parts) < 2) {
			return false;
		}
		if (!fractal_zip_folder_text_classic_worth(strlen($diskBytes), count($parts))) {
			return false;
		}
		$payloads = array();
		$names = array();
		$addedNew = array();
		$useShare = function_exists('fractal_zip_folder_register_shared_payload');
		$li = 0;
		foreach ($parts as $line) {
			if ($li >= 256) {
				foreach ($addedNew as $n) {
					unset($members[$n]);
				}
				return false;
			}
			$short = 'l_' . sprintf('%03d', $li);
			$logical = rtrim($diskPath, '/') . '/' . $short;
			if ($useShare) {
				$reg = fractal_zip_folder_register_shared_payload(
					$members, $line, $short, $logical, $addedNew
				);
				$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
			} else {
				$members[$logical] = $line;
				$addedNew[] = $logical;
				$names[] = $logical;
			}
			$payloads[] = array('name' => $short, 'data' => $line);
			$li++;
		}
		$rebuilt = fractal_zip_classic_rebuild_archive('lines', $eol, $payloads);
		if ($rebuilt === null || $rebuilt !== $diskBytes) {
			foreach ($addedNew as $n) {
				unset($members[$n]);
			}
			return false;
		}
		$restore[$diskPath] = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
			'format' => 'lines',
			'meta' => $eol,
			'member_names' => $names,
		);
		return true;
	}

	return false;
}

function fractal_zip_folder_tar_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_TAR_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Expand ustar TAR into logical file members.
 * Prefer CLASSIC rebuild when a deterministic archive matches bit-exactly.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_tar(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_tar_peel_enabled() || strlen($diskBytes) < 512) {
		return false;
	}
	if (!function_exists('fractal_zip_literal_tar_list_file_members')) {
		$tar = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
		if (is_file($tar)) {
			require_once $tar;
		}
	}
	if (!function_exists('fractal_zip_literal_tar_list_file_members')) {
		return false;
	}
	$sniffOk = function_exists('fractal_zip_literal_tar_sniffs_gnu_archive')
		&& fractal_zip_literal_tar_sniffs_gnu_archive($diskBytes);
	if (!$sniffOk && strpos($diskBytes, 'ustar') === false) {
		return false;
	}
	$list = fractal_zip_literal_tar_list_file_members($diskBytes);
	if ($list === null || count($list) < 1) {
		return false;
	}
	$cap = 256;
	$eCap = getenv('FRACTAL_ZIP_FOLDER_TAR_MAX_MEMBERS');
	if ($eCap !== false && (int) $eCap > 0) {
		$cap = max(2, (int) $eCap);
	}
	$names = array();
	$logicalKeys = array();
	$payloads = array();
	/** @var list<string>|null */
	$headers = array();
	$addedNew = array();
	$n = 0;
	foreach ($list as $m) {
		if ($n >= $cap) {
			break;
		}
		$name = str_replace('\\', '/', (string) ($m['name'] ?? ''));
		$data = (string) ($m['data'] ?? '');
		if ($name === '' || str_contains($name, '..')) {
			continue;
		}
		$ho = (int) ($m['header_offset'] ?? -1);
		if ($headers !== null) {
			if ($ho < 0 || $ho + 512 > strlen($diskBytes)) {
				$headers = null;
			} else {
				$headers[] = substr($diskBytes, $ho, 512);
			}
		}
		$legacy = rtrim($diskPath, '/') . '/' . ltrim($name, '/');
		$key = fractal_zip_folder_register_shared_payload($members, $data, $name, $legacy, $addedNew);
		$logicalKeys[] = $key;
		$names[] = fractal_zip_folder_fzhr_share_name($key, $name);
		$payloads[] = array('name' => $name, 'data' => $data);
		$n++;
	}
	if (count($names) < 1) {
		foreach ($addedNew as $k) {
			unset($members[$k]);
		}
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (count($names) >= 1 && function_exists('fractal_zip_classic_rebuild_archive')) {
		// Prefer lossless rebuild with original ustar headers + trailing zero pad.
		$okHdr = is_array($headers) && count($headers) === count($names);
		$core = '';
		if ($okHdr) {
			foreach ($payloads as $i => $p) {
				$data = (string) ($p['data'] ?? '');
				$pad = (512 - (strlen($data) % 512)) % 512;
				$core .= $headers[$i] . $data . str_repeat("\0", $pad);
			}
			$core .= str_repeat("\0", 1024);
			$trailerPad = 0;
			if ($core === $diskBytes) {
				$trailerPad = 0;
			} elseif (strlen($core) < strlen($diskBytes)
				&& str_starts_with($diskBytes, $core)
				&& trim(substr($diskBytes, strlen($core)), "\0") === '') {
				$trailerPad = strlen($diskBytes) - strlen($core);
			} else {
				$okHdr = false;
			}
			// Huge trailing zero pad + tiny payloads: PASSTHROUGH compresses better than
			// paying ~512 B/header in FZHR meta (synthetic sparse tars).
			$payloadBytes = 0;
			foreach ($payloads as $p) {
				$payloadBytes += strlen((string) ($p['data'] ?? ''));
			}
			if ($okHdr && function_exists('fractal_zip_classic_tar_fzth_encode')) {
				$meta = fractal_zip_classic_tar_fzth_encode($headers, $trailerPad);
				$metaCost = strlen($meta);
				// Huge trailing zero pad + tiny payloads: PASSTHROUGH compresses better than
				// paying header meta in FZHR (synthetic sparse tars).
				if ($meta !== '' && (
					($trailerPad > strlen($core) && $payloadBytes < 4096)
					|| ($metaCost > $payloadBytes && $payloadBytes < 4096)
				)) {
					$okHdr = false;
				}
				if ($okHdr) {
					$rebuilt = fractal_zip_classic_rebuild_archive('tar', $meta, $payloads);
					if ($meta !== '' && $rebuilt !== null && $rebuilt === $diskBytes) {
						$restore[$diskPath] = array(
							'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
							'format' => 'tar',
							'meta' => $meta,
							'member_names' => $names,
						);
						return true;
					}
				}
			}
		}
		$rebuilt = fractal_zip_classic_rebuild_archive('tar', '', $payloads);
		if ($rebuilt !== null && $rebuilt === $diskBytes) {
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'tar',
				'meta' => '',
				'member_names' => $names,
			);
			return true;
		}
	}
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
		'verbatim' => $diskBytes,
		'member_names' => $names,
	);
	return true;
}

function fractal_zip_folder_sg_dat_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_SG_DAT_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Expand Shattered Galaxy .dat into logical members (CLASSIC when bit-exact).
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_sg_dat(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_sg_dat_peel_enabled() || strlen($diskBytes) < 4 + 17) {
		return false;
	}
	$base = strtolower(basename($diskPath));
	if (!str_ends_with($base, '.dat')) {
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (!function_exists('fractal_zip_classic_sg_dat_list_from_bytes')) {
		return false;
	}
	$list = fractal_zip_classic_sg_dat_list_from_bytes($diskBytes);
	if ($list === null || count($list) < 2) {
		return false;
	}
	$names = array();
	$logicalKeys = array();
	$payloads = array();
	$addedNew = array();
	foreach ($list as $m) {
		$name = str_replace('\\', '/', (string) ($m['name'] ?? ''));
		$data = (string) ($m['data'] ?? '');
		if ($name === '' || str_contains($name, '..')) {
			continue;
		}
		$legacy = rtrim($diskPath, '/') . '/' . ltrim($name, '/');
		$key = fractal_zip_folder_register_shared_payload($members, $data, $name, $legacy, $addedNew);
		$logicalKeys[] = $key;
		$names[] = fractal_zip_folder_fzhr_share_name($key, $name);
		$payloads[] = array('name' => $name, 'data' => $data);
	}
	if (count($names) < 2) {
		foreach ($addedNew as $k) {
			unset($members[$k]);
		}
		return false;
	}
	if (function_exists('fractal_zip_classic_rebuild_archive')) {
		$rebuilt = fractal_zip_classic_rebuild_archive('sg_dat', '', $payloads);
		if ($rebuilt !== null && $rebuilt === $diskBytes) {
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'sg_dat',
				'meta' => '',
				'member_names' => $names,
			);
			return true;
		}
	}
	foreach ($addedNew as $k) {
		unset($members[$k]);
	}
	return false;
}

function fractal_zip_folder_ar_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_AR_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Expand GNU ar into logical members.
 * Prefer CLASSIC rebuild when a deterministic archive matches bit-exactly.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_ar(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_ar_peel_enabled() || strlen($diskBytes) < 8 + 60) {
		return false;
	}
	if (substr($diskBytes, 0, 8) !== "!<arch>\n") {
		return false;
	}
	if (!function_exists('fractal_zip_literal_ar_list_file_members')) {
		$ar = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_ar_gnu.php';
		if (is_file($ar)) {
			require_once $ar;
		}
	}
	if (!function_exists('fractal_zip_literal_ar_list_file_members')) {
		return false;
	}
	$list = fractal_zip_literal_ar_list_file_members($diskBytes);
	if ($list === null || count($list) < 2) {
		return false;
	}
	$names = array();
	$logicalKeys = array();
	$payloads = array();
	$headers = array();
	$payloadBytes = 0;
	$addedNew = array();
	foreach ($list as $m) {
		$name = str_replace('\\', '/', (string) ($m['name'] ?? ''));
		$data = (string) ($m['data'] ?? '');
		if ($name === '' || str_contains($name, '..') || $data === '') {
			continue;
		}
		$ho = (int) ($m['header_offset'] ?? -1);
		if ($ho >= 0 && $ho + 60 <= strlen($diskBytes)) {
			$headers[] = substr($diskBytes, $ho, 60);
		}
		$legacy = rtrim($diskPath, '/') . '/' . ltrim($name, '/');
		$key = fractal_zip_folder_register_shared_payload($members, $data, $name, $legacy, $addedNew);
		$logicalKeys[] = $key;
		$names[] = fractal_zip_folder_fzhr_share_name($key, $name);
		$payloads[] = array('name' => $name, 'data' => $data);
		$payloadBytes += strlen($data);
	}
	if (count($names) < 2) {
		foreach ($addedNew as $k) {
			unset($members[$k]);
		}
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (function_exists('fractal_zip_classic_rebuild_archive')) {
		$fzahMeta = null;
		// Prefer lossless rebuild with original 60-byte headers (mode 644 vs 100644, etc.).
		if (count($headers) === count($names)
			&& function_exists('fractal_zip_classic_ar_fzah_encode')) {
			$meta = fractal_zip_classic_ar_fzah_encode($headers);
			$metaCost = strlen($meta);
			// Tiny payloads + full headers: PASSTHROUGH beats paying header meta in FZHR.
			if ($meta !== '' && !($metaCost > $payloadBytes && $payloadBytes < 4096)) {
				$reHdr = fractal_zip_classic_rebuild_archive('ar', $meta, $payloads);
				if ($reHdr !== null && $reHdr === $diskBytes) {
					// .deb layout: prefer nested control/data peel (cross-codec share)
					// over flat FZAH — fall through to the nested attempt below.
					$byProbe = array();
					foreach ($payloads as $p) {
						$byProbe[(string) $p['name']] = true;
					}
					$isDeb = isset($byProbe['debian-binary']) && (
						isset($byProbe['control.tar'], $byProbe['data.tar'])
						|| isset($byProbe['control.tar.gz'], $byProbe['data.tar.gz'])
						|| isset($byProbe['control.tar.xz'], $byProbe['data.tar.xz'])
						|| isset($byProbe['control.tar.zst'], $byProbe['data.tar.zst'])
						|| isset($byProbe['control.tar.br'], $byProbe['data.tar.br'])
						|| isset($byProbe['control.tar.lz4'], $byProbe['data.tar.lz4'])
					);
					if (!$isDeb) {
						$restore[$diskPath] = array(
							'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
							'format' => 'ar',
							'meta' => $meta,
							'member_names' => $names,
						);
						return true;
					}
					$fzahMeta = $meta;
				}
			}
		}
		$rebuilt = fractal_zip_classic_rebuild_archive('ar', '', $payloads);
		if ($rebuilt !== null && $rebuilt === $diskBytes) {
			// Nested classic .deb: peel control.tar[.gz] + data.tar[.gz] into control/* and data/*.
			$byName = array();
			foreach ($payloads as $p) {
				$byName[(string) $p['name']] = (string) $p['data'];
			}
			$ctrlKey = null;
			$dataKey = null;
			$wrapMeta = null;
			$wrapFmt = 'deb';
			if (isset($byName['debian-binary'], $byName['control.tar'], $byName['data.tar'])) {
				$ctrlKey = 'control.tar';
				$dataKey = 'data.tar';
			} elseif (isset($byName['debian-binary'], $byName['control.tar.gz'], $byName['data.tar.gz'])) {
				$ctrlKey = 'control.tar.gz';
				$dataKey = 'data.tar.gz';
				$wrapFmt = 'debgz';
			} elseif (isset($byName['debian-binary'], $byName['control.tar.xz'], $byName['data.tar.xz'])) {
				$ctrlKey = 'control.tar.xz';
				$dataKey = 'data.tar.xz';
				$wrapFmt = 'debxz';
			} elseif (isset($byName['debian-binary'], $byName['control.tar.zst'], $byName['data.tar.zst'])) {
				$ctrlKey = 'control.tar.zst';
				$dataKey = 'data.tar.zst';
				$wrapFmt = 'debzst';
			} elseif (isset($byName['debian-binary'], $byName['control.tar.br'], $byName['data.tar.br'])) {
				$ctrlKey = 'control.tar.br';
				$dataKey = 'data.tar.br';
				$wrapFmt = 'debbr';
			} elseif (isset($byName['debian-binary'], $byName['control.tar.lz4'], $byName['data.tar.lz4'])) {
				$ctrlKey = 'control.tar.lz4';
				$dataKey = 'data.tar.lz4';
				$wrapFmt = 'deblz4';
			}
			if ($ctrlKey !== null && $dataKey !== null) {
				if (!function_exists('fractal_zip_literal_tar_list_file_members')) {
					$tar = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
					if (is_file($tar)) {
						require_once $tar;
					}
				}
				$ctrlBytes = $byName[$ctrlKey];
				$dataBytes = $byName[$dataKey];
				if ($wrapFmt === 'debgz') {
					$ctrlInner = @gzdecode($ctrlBytes);
					$dataInner = @gzdecode($dataBytes);
					if (!is_string($ctrlInner) || $ctrlInner === '' || !is_string($dataInner) || $dataInner === '') {
						$ctrlBytes = null;
						$dataBytes = null;
					} else {
						for ($lev = 1; $lev <= 9; $lev++) {
							$c1 = @gzencode($ctrlInner, $lev);
							$c2 = @gzencode($dataInner, $lev);
							if (is_string($c1) && $c1 === $byName[$ctrlKey]
								&& is_string($c2) && $c2 === $byName[$dataKey]) {
								$wrapMeta = (string) $lev;
								break;
							}
						}
						if ($wrapMeta === null) {
							$ctrlBytes = null;
							$dataBytes = null;
						} else {
							$ctrlBytes = $ctrlInner;
							$dataBytes = $dataInner;
						}
					}
				} elseif ($wrapFmt === 'debxz') {
					$ctrlInner = fractal_zip_folder_decode_single_compress($ctrlBytes);
					$dataInner = fractal_zip_folder_decode_single_compress($dataBytes);
					if (!is_string($ctrlInner) || $ctrlInner === '' || !is_string($dataInner) || $dataInner === '') {
						$ctrlBytes = null;
						$dataBytes = null;
					} else {
						$wrapMeta = null;
						if (function_exists('fractal_zip_folder_xz_find_rewrap_level')) {
							$l1 = fractal_zip_folder_xz_find_rewrap_level($ctrlInner, $byName[$ctrlKey]);
							$l2 = fractal_zip_folder_xz_find_rewrap_level($dataInner, $byName[$dataKey]);
							if ($l1 !== null && $l1 === $l2) {
								$wrapMeta = (string) $l1;
							}
						}
						if ($wrapMeta === null) {
							$ctrlBytes = null;
							$dataBytes = null;
						} else {
							$ctrlBytes = $ctrlInner;
							$dataBytes = $dataInner;
						}
					}
				} elseif ($wrapFmt === 'debzst') {
					$ctrlInner = fractal_zip_folder_decode_single_compress($ctrlBytes);
					$dataInner = fractal_zip_folder_decode_single_compress($dataBytes);
					if (!is_string($ctrlInner) || $ctrlInner === '' || !is_string($dataInner) || $dataInner === '') {
						$ctrlBytes = null;
						$dataBytes = null;
					} else {
						$wrapMeta = null;
						if (function_exists('fractal_zip_folder_zstd_find_rewrap')) {
							$z1 = fractal_zip_folder_zstd_find_rewrap($ctrlInner, $byName[$ctrlKey]);
							$z2 = fractal_zip_folder_zstd_find_rewrap($dataInner, $byName[$dataKey]);
							if ($z1 !== null && $z2 !== null
								&& (int) $z1['level'] === (int) $z2['level']
								&& !empty($z1['no_check']) === !empty($z2['no_check'])) {
								$wrapMeta = (string) ((int) $z1['level']) . (!empty($z1['no_check']) ? 'n' : '');
							}
						}
						if ($wrapMeta === null) {
							$ctrlBytes = null;
							$dataBytes = null;
						} else {
							$ctrlBytes = $ctrlInner;
							$dataBytes = $dataInner;
						}
					}
				} elseif ($wrapFmt === 'debbr') {
					$ctrlInner = fractal_zip_folder_decode_single_compress($ctrlBytes, $ctrlKey);
					$dataInner = fractal_zip_folder_decode_single_compress($dataBytes, $dataKey);
					if (!is_string($ctrlInner) || $ctrlInner === '' || !is_string($dataInner) || $dataInner === '') {
						$ctrlBytes = null;
						$dataBytes = null;
					} else {
						$wrapMeta = null;
						if (function_exists('fractal_zip_folder_brotli_find_rewrap_level')) {
							$l1 = fractal_zip_folder_brotli_find_rewrap_level($ctrlInner, $byName[$ctrlKey]);
							$l2 = fractal_zip_folder_brotli_find_rewrap_level($dataInner, $byName[$dataKey]);
							if ($l1 !== null && $l1 === $l2) {
								$wrapMeta = (string) $l1;
							}
						}
						if ($wrapMeta === null) {
							$ctrlBytes = null;
							$dataBytes = null;
						} else {
							$ctrlBytes = $ctrlInner;
							$dataBytes = $dataInner;
						}
					}
				} elseif ($wrapFmt === 'deblz4') {
					$ctrlInner = fractal_zip_folder_decode_single_compress($ctrlBytes);
					$dataInner = fractal_zip_folder_decode_single_compress($dataBytes);
					if (!is_string($ctrlInner) || $ctrlInner === '' || !is_string($dataInner) || $dataInner === '') {
						$ctrlBytes = null;
						$dataBytes = null;
					} else {
						$wrapMeta = null;
						if (function_exists('fractal_zip_folder_lz4_find_rewrap_level')) {
							$l1 = fractal_zip_folder_lz4_find_rewrap_level($ctrlInner, $byName[$ctrlKey]);
							$l2 = fractal_zip_folder_lz4_find_rewrap_level($dataInner, $byName[$dataKey]);
							if ($l1 !== null && $l1 === $l2) {
								$wrapMeta = (string) $l1;
							}
						}
						if ($wrapMeta === null) {
							$ctrlBytes = null;
							$dataBytes = null;
						} else {
							$ctrlBytes = $ctrlInner;
							$dataBytes = $dataInner;
						}
					}
				}
				if ($ctrlBytes !== null && $dataBytes !== null
					&& function_exists('fractal_zip_literal_tar_list_file_members')) {
				$ctrlList = fractal_zip_literal_tar_list_file_members($ctrlBytes);
				$dataList = fractal_zip_literal_tar_list_file_members($dataBytes);
				if (is_array($ctrlList) && $ctrlList !== [] && is_array($dataList) && $dataList !== []) {
					$ctrlPayloads = array();
					$dataPayloads = array();
					foreach ($ctrlList as $m) {
						$ctrlPayloads[] = array(
							'name' => (string) ($m['name'] ?? ''),
							'data' => (string) ($m['data'] ?? ''),
						);
					}
					foreach ($dataList as $m) {
						$dataPayloads[] = array(
							'name' => (string) ($m['name'] ?? ''),
							'data' => (string) ($m['data'] ?? ''),
						);
					}
					$ctrlTar = fractal_zip_classic_rebuild_archive('tar', '', $ctrlPayloads);
					$dataTar = fractal_zip_classic_rebuild_archive('tar', '', $dataPayloads);
					if ($ctrlTar === $ctrlBytes && $dataTar === $dataBytes) {
						foreach ($addedNew as $k) {
							unset($members[$k]);
						}
						$addedNested = array();
						$nestedNames = array();
						$dbLegacy = rtrim($diskPath, '/') . '/debian-binary';
						$dbKey = fractal_zip_folder_register_shared_payload(
							$members, $byName['debian-binary'], 'debian-binary', $dbLegacy, $addedNested
						);
						$nestedNames[] = fractal_zip_folder_fzhr_share_name($dbKey, 'debian-binary');
						foreach ($ctrlPayloads as $p) {
							$n = 'control/' . ltrim((string) $p['name'], '/');
							$legacy = rtrim($diskPath, '/') . '/' . $n;
							$key = fractal_zip_folder_register_shared_payload(
								$members, (string) $p['data'], $n, $legacy, $addedNested
							);
							$nestedNames[] = fractal_zip_folder_fzhr_share_name($key, $n);
						}
						foreach ($dataPayloads as $p) {
							$n = 'data/' . ltrim((string) $p['name'], '/');
							$legacy = rtrim($diskPath, '/') . '/' . $n;
							$key = fractal_zip_folder_register_shared_payload(
								$members, (string) $p['data'], $n, $legacy, $addedNested
							);
							$nestedNames[] = fractal_zip_folder_fzhr_share_name($key, $n);
						}
						$debPayloads = array(
							array('name' => 'debian-binary', 'data' => $byName['debian-binary']),
						);
						foreach ($ctrlPayloads as $p) {
							$debPayloads[] = array(
								'name' => 'control/' . ltrim((string) $p['name'], '/'),
								'data' => (string) $p['data'],
							);
						}
						foreach ($dataPayloads as $p) {
							$debPayloads[] = array(
								'name' => 'data/' . ltrim((string) $p['name'], '/'),
								'data' => (string) $p['data'],
							);
						}
						$debFmt = $wrapFmt;
						$debMeta = $wrapMeta !== null ? $wrapMeta : '';
						if ($debFmt !== 'deb' && $debMeta === '') {
							// Wrapped without matching rewrap level — fall through to flat AR.
						} else {
						$debRebuilt = fractal_zip_classic_rebuild_archive($debFmt, $debMeta, $debPayloads);
						if ($debRebuilt !== null && $debRebuilt === $diskBytes) {
							$restore[$diskPath] = array(
								'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
								'format' => $debFmt,
								'meta' => $debMeta,
								'member_names' => $nestedNames,
							);
							return true;
						}
						}
						// Nested rebuild failed — fall back to flat AR members.
						foreach (array_keys($members) as $k) {
							if (str_starts_with($k, rtrim($diskPath, '/') . '/')) {
								unset($members[$k]);
							}
						}
						$addedNew = array();
						$names = array();
						foreach ($payloads as $p) {
							$pn = (string) $p['name'];
							$legacy = rtrim($diskPath, '/') . '/' . ltrim($pn, '/');
							$key = fractal_zip_folder_register_shared_payload(
								$members, (string) $p['data'], $pn, $legacy, $addedNew
							);
							$names[] = fractal_zip_folder_fzhr_share_name($key, $pn);
						}
					}
				}
				}
			}
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'ar',
				'meta' => is_string($fzahMeta) ? $fzahMeta : '',
				'member_names' => $names,
			);
			return true;
		}
	}
	foreach ($addedNew as $k) {
		unset($members[$k]);
	}
	// Fall back: re-add with VERBATIM (reset keys — do not append duplicates).
	$addedNew = array();
	$names = array();
	foreach ($payloads as $p) {
		$name = (string) $p['name'];
		$legacy = rtrim($diskPath, '/') . '/' . ltrim($name, '/');
		$key = fractal_zip_folder_register_shared_payload(
			$members, (string) $p['data'], $name, $legacy, $addedNew
		);
		$names[] = fractal_zip_folder_fzhr_share_name($key, $name);
	}
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
		'verbatim' => $diskBytes,
		'member_names' => $names,
	);
	return true;
}

function fractal_zip_folder_cpio_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_CPIO_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Expand newc cpio into logical members.
 * Prefer CLASSIC rebuild when a deterministic archive matches bit-exactly.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_cpio(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_cpio_peel_enabled() || strlen($diskBytes) < 110) {
		return false;
	}
	if (!function_exists('fractal_zip_literal_cpio_newc_list_file_members')
		|| !function_exists('fractal_zip_literal_cpio_newc_magic6_ok')) {
		$cpio = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_cpio_newc.php';
		if (is_file($cpio)) {
			require_once $cpio;
		}
	}
	if (!function_exists('fractal_zip_literal_cpio_newc_magic6_ok')
		|| !fractal_zip_literal_cpio_newc_magic6_ok(substr($diskBytes, 0, 6))) {
		return false;
	}
	if (!function_exists('fractal_zip_literal_cpio_newc_list_file_members')) {
		return false;
	}
	$list = fractal_zip_literal_cpio_newc_list_file_members($diskBytes);
	if ($list === null || count($list) < 1) {
		return false;
	}
	$names = array();
	$logicalKeys = array();
	$payloads = array();
	$prefixes = array();
	$payloadBytes = 0;
	$lastRecordEnd = -1;
	$addedNew = array();
	foreach ($list as $m) {
		$name = str_replace('\\', '/', (string) ($m['name'] ?? ''));
		$data = (string) ($m['data'] ?? '');
		if ($name === '' || str_contains($name, '..')) {
			continue;
		}
		$ho = (int) ($m['header_offset'] ?? -1);
		$dStart = -1;
		if ($ho >= 0 && function_exists('fractal_zip_literal_cpio_newc_one_record_layout')) {
			$lay = fractal_zip_literal_cpio_newc_one_record_layout($diskBytes, $ho);
			if (is_array($lay)) {
				$dStart = (int) $lay[0];
				$lastRecordEnd = (int) $lay[4];
			}
		}
		if ($ho >= 0 && $dStart > $ho) {
			$prefixes[] = substr($diskBytes, $ho, $dStart - $ho);
		}
		$legacy = rtrim($diskPath, '/') . '/' . ltrim($name, '/');
		$key = fractal_zip_folder_register_shared_payload($members, $data, $name, $legacy, $addedNew);
		$logicalKeys[] = $key;
		$names[] = fractal_zip_folder_fzhr_share_name($key, $name);
		$payloads[] = array('name' => $name, 'data' => $data);
		$payloadBytes += strlen($data);
	}
	if (count($names) < 1) {
		foreach ($addedNew as $k) {
			unset($members[$k]);
		}
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (count($names) >= 2 && function_exists('fractal_zip_classic_rebuild_archive')) {
		// Prefer lossless rebuild with original newc headers + trailer (ino/mtime/pad).
		if (count($prefixes) === count($names)
			&& $lastRecordEnd > 0
			&& $lastRecordEnd <= strlen($diskBytes)
			&& function_exists('fractal_zip_classic_cpio_fzch_encode')) {
			$trailer = substr($diskBytes, $lastRecordEnd);
			$meta = fractal_zip_classic_cpio_fzch_encode($prefixes, $trailer);
			$metaCost = strlen($meta);
			// Sparse/tiny: trailer zeros + headers dominate — keep PASSTHROUGH.
			if ($meta !== '' && !(($metaCost > $payloadBytes && $payloadBytes < 4096)
				|| (strlen($trailer) > $payloadBytes && $payloadBytes < 4096))) {
				$reHdr = fractal_zip_classic_rebuild_archive('cpio', $meta, $payloads);
				if ($reHdr !== null && $reHdr === $diskBytes) {
					$restore[$diskPath] = array(
						'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
						'format' => 'cpio',
						'meta' => $meta,
						'member_names' => $names,
					);
					return true;
				}
			}
		}
		$rebuilt = fractal_zip_classic_rebuild_archive('cpio', '', $payloads);
		if ($rebuilt !== null && $rebuilt === $diskBytes) {
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'cpio',
				'meta' => '',
				'member_names' => $names,
			);
			return true;
		}
	}
	// Fall back: VERBATIM
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
		'verbatim' => $diskBytes,
		'member_names' => $names,
	);
	return true;
}

function fractal_zip_folder_gzip_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_GZIP_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Peel a top-level .gz (or gzip-magic) file into its inner payload for the tournament.
 * Prefer CLASSIC rebuild when gzencode(level) matches bit-exactly; otherwise VERBATIM.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_gzip(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_gzip_peel_enabled() || strlen($diskBytes) < 18) {
		return false;
	}
	if (!function_exists('fractal_zip_literal_expand_outer_gzip_once')) {
		$pac = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
		if (is_file($pac)) {
			require_once $pac;
		}
	}
	$inner = null;
	if (function_exists('fractal_zip_literal_expand_outer_gzip_once')) {
		$exp = fractal_zip_literal_expand_outer_gzip_once($diskBytes);
		if ($exp !== null && isset($exp['inner'])) {
			$inner = (string) $exp['inner'];
		}
	}
	if ($inner === null) {
		if (strlen($diskBytes) < 10 || ord($diskBytes[0]) !== 0x1f || ord($diskBytes[1]) !== 0x8b) {
			return false;
		}
		$decoded = @gzdecode($diskBytes);
		if ($decoded === false) {
			return false;
		}
		$inner = $decoded;
	}
	$base = strtolower(basename($diskPath));
	$innerName = $base;
	if (str_ends_with($base, '.svgz')) {
		$innerName = substr($base, 0, -5) . '.svg';
	} elseif (str_ends_with($base, '.vgz')) {
		$innerName = substr($base, 0, -4) . '.vgm';
	} else {
		$innerName = preg_replace('/\.gz$/i', '', $base) ?: 'payload';
	}
	if ($innerName === $base || $innerName === '') {
		$innerName = 'payload.bin';
	}
	$logical = rtrim($diskPath, '/') . '/' . $innerName;

	$lvl = null;
	for ($lev = 1; $lev <= 9; $lev++) {
		$c = @gzencode($inner, $lev);
		if (is_string($c) && $c === $diskBytes) {
			$lvl = $lev;
			break;
		}
	}
	if ($lvl !== null) {
		$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
		if (is_readable($classic)) {
			require_once $classic;
		}
		if (function_exists('fractal_zip_classic_rebuild_archive')) {
			// Nested classic: gzip(ustar|cpio).
			if (fractal_zip_folder_try_nested_archive_classic(
				$diskPath,
				$diskBytes,
				$inner,
				(string) $lvl,
				array('targz', 'cpiogz'),
				$members,
				$restore
			)) {
				return true;
			}
			// Nested classic: gzip(SMF MIDI tracks).
			if (fractal_zip_folder_try_nested_midi_classic(
				$diskPath,
				$diskBytes,
				$inner,
				'gzip',
				(string) $lvl,
				$members,
				$restore
			)) {
				return true;
			}
			$rebuilt = fractal_zip_classic_rebuild_archive('gzip', (string) $lvl, array(
				array('name' => $innerName, 'data' => $inner),
			));
			if ($rebuilt !== null && $rebuilt === $diskBytes) {
				fractal_zip_folder_commit_shared_classic_inner(
					$members, $restore, $diskPath, $inner, $innerName, $logical, 'gzip', (string) $lvl
				);
				return true;
			}
		}
	}

	fractal_zip_folder_commit_shared_verbatim_inner(
		$members, $restore, $diskPath, $diskBytes, $inner, $innerName, $logical
	);
	return true;
}

function fractal_zip_folder_compress_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_COMPRESS_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Decode a single-stream bzip2 / xz / zstd / lz4 / brotli / lzip wrapper into one logical member.
 * Brotli has no fixed magic — pass $hintPath ending in .br / .brotli to try it.
 *
 * @return string|null inner bytes
 */
function fractal_zip_folder_decode_single_compress(string $diskBytes, string $hintPath = ''): ?string {
	$n = strlen($diskBytes);
	if ($n < 4) {
		return null;
	}
	$max = 64 * 1024 * 1024;
	$eMax = getenv('FRACTAL_ZIP_FOLDER_COMPRESS_MAX_INNER');
	if ($eMax !== false && (int) $eMax > 0) {
		$max = max(1024, (int) $eMax);
	}

	// bzip2: "BZh"
	if (str_starts_with($diskBytes, 'BZh') && function_exists('bzdecompress')) {
		$inner = @bzdecompress($diskBytes);
		if (is_string($inner) && $inner !== '' && strlen($inner) <= $max) {
			return $inner;
		}
	}

	$tool = null;
	$suffix = '';
	if ($n >= 6 && str_starts_with($diskBytes, "\xfd7zXZ\x00")) {
		$tool = 'xz';
		$suffix = '.xz';
	} elseif (str_starts_with($diskBytes, "\x28\xb5\x2f\xfd")) {
		$tool = 'zstd';
		$suffix = '.zst';
	} elseif (str_starts_with($diskBytes, "\x04\x22\x4d\x18")) {
		// LZ4 frame magic
		$tool = 'lz4';
		$suffix = '.lz4';
	} elseif (str_starts_with($diskBytes, 'LZIP')) {
		$tool = 'lzip';
		$suffix = '.lz';
	} elseif (str_starts_with($diskBytes, 'BZh')) {
		$tool = 'bzip2';
		$suffix = '.bz2';
	} elseif ($n >= 5 && ord($diskBytes[0]) === 0x5d) {
		// Raw LZMA (xz -F lzma / lzma CLI)
		$tool = 'xz';
		$suffix = '.lzma';
	} else {
		$base = strtolower(basename($hintPath));
		if ($base !== '' && (str_ends_with($base, '.br') || str_ends_with($base, '.brotli'))) {
			$tool = 'brotli';
			$suffix = '.br';
		} elseif ($base !== '' && (str_ends_with($base, '.lz') || str_ends_with($base, '.lzip'))) {
			$tool = 'lzip';
			$suffix = '.lz';
		}
	}
	if ($tool === null) {
		return null;
	}

	$tmp = tempnam(sys_get_temp_dir(), 'fzcmp_');
	if ($tmp === false) {
		return null;
	}
	$path = $tmp . $suffix;
	@unlink($tmp);
	if (@file_put_contents($path, $diskBytes) === false) {
		@unlink($path);
		return null;
	}
	$inner = null;
	if (function_exists('fractal_zip_literal_pac_shell_decompress')) {
		$inner = fractal_zip_literal_pac_shell_decompress($tool, array('-dc'), $path);
	} else {
		$bin = trim((string) shell_exec('command -v ' . escapeshellarg($tool) . ' 2>/dev/null'));
		if ($bin !== '') {
			$cmd = escapeshellarg($bin) . ' -dc ' . escapeshellarg($path) . ' 2>/dev/null';
			$out = shell_exec($cmd);
			if (is_string($out) && $out !== '') {
				$inner = $out;
			}
		}
	}
	@unlink($path);
	if (!is_string($inner) || $inner === '' || strlen($inner) > $max) {
		return null;
	}
	return $inner;
}

/**
 * Compress bytes with shell bzip2 at $level (1–9). Returns null on failure.
 */
function fractal_zip_folder_bzip2_shell_compress(string $inner, int $level): ?string {
	$level = max(1, min(9, $level));
	$bin = trim((string) shell_exec('command -v bzip2 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzbz_');
	if ($tmp === false) {
		return null;
	}
	if (@file_put_contents($tmp, $inner) === false) {
		@unlink($tmp);
		return null;
	}
	$cmd = escapeshellarg($bin) . ' -' . $level . ' -c ' . escapeshellarg($tmp) . ' 2>/dev/null';
	$blob = shell_exec($cmd);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '' || !str_starts_with($blob, 'BZh')) {
		return null;
	}
	return $blob;
}

/**
 * Compress bytes with shell lzip at $level (0–9). Returns null on failure.
 */
function fractal_zip_folder_lzip_shell_compress(string $inner, int $level): ?string {
	$level = max(0, min(9, $level));
	$bin = trim((string) shell_exec('command -v lzip 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzlz_');
	if ($tmp === false) {
		return null;
	}
	if (@file_put_contents($tmp, $inner) === false) {
		@unlink($tmp);
		return null;
	}
	$cmd = escapeshellarg($bin) . ' -' . $level . ' -c ' . escapeshellarg($tmp) . ' 2>/dev/null';
	$blob = shell_exec($cmd);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '' || !str_starts_with($blob, 'LZIP')) {
		return null;
	}
	return $blob;
}

/** @return int|null rewrap level 0–9 when bit-exact */
function fractal_zip_folder_lzip_find_rewrap_level(string $inner, string $diskBytes): ?int {
	for ($lvl = 0; $lvl <= 9; $lvl++) {
		$c = fractal_zip_folder_lzip_shell_compress($inner, $lvl);
		if ($c !== null && $c === $diskBytes) {
			return $lvl;
		}
	}
	return null;
}

/**
 * Compress bytes with shell xz at $level (0–9). Returns null on failure.
 */
function fractal_zip_folder_xz_shell_compress(string $inner, int $level): ?string {
	$level = max(0, min(9, $level));
	$bin = trim((string) shell_exec('command -v xz 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzxz_');
	if ($tmp === false) {
		return null;
	}
	if (@file_put_contents($tmp, $inner) === false) {
		@unlink($tmp);
		return null;
	}
	$cmd = escapeshellarg($bin) . ' -' . $level . ' -c -F xz --check=crc64 ' . escapeshellarg($tmp) . ' 2>/dev/null';
	$blob = shell_exec($cmd);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '' || !str_starts_with($blob, "\xfd7zXZ\x00")) {
		return null;
	}
	return $blob;
}

/**
 * Compress bytes with shell zstd at $level (1–19). Returns null on failure.
 * @param bool $noCheck when true, omit content checksum
 */
function fractal_zip_folder_zstd_shell_compress(string $inner, int $level, bool $noCheck = false): ?string {
	$level = max(1, min(19, $level));
	$bin = trim((string) shell_exec('command -v zstd 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzzs_');
	if ($tmp === false) {
		return null;
	}
	if (@file_put_contents($tmp, $inner) === false) {
		@unlink($tmp);
		return null;
	}
	$flags = $noCheck ? ' --no-check' : '';
	$cmd = escapeshellarg($bin) . ' -' . $level . ' -c' . $flags . ' ' . escapeshellarg($tmp) . ' 2>/dev/null';
	$blob = shell_exec($cmd);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '' || !str_starts_with($blob, "\x28\xb5\x2f\xfd")) {
		return null;
	}
	return $blob;
}

/**
 * Compress bytes with shell xz -F lzma at $level (0–9). Returns null on failure.
 */
function fractal_zip_folder_lzma_shell_compress(string $inner, int $level): ?string {
	$level = max(0, min(9, $level));
	$bin = trim((string) shell_exec('command -v xz 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzlm_');
	if ($tmp === false) {
		return null;
	}
	if (@file_put_contents($tmp, $inner) === false) {
		@unlink($tmp);
		return null;
	}
	$cmd = escapeshellarg($bin) . ' -' . $level . ' -c -F lzma --check=none ' . escapeshellarg($tmp) . ' 2>/dev/null';
	$blob = shell_exec($cmd);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '' || ord($blob[0]) !== 0x5d) {
		return null;
	}
	return $blob;
}

/**
 * Compress bytes with shell lz4 at $level (1–12). Returns null on failure.
 */
function fractal_zip_folder_lz4_shell_compress(string $inner, int $level): ?string {
	$level = max(1, min(12, $level));
	$bin = trim((string) shell_exec('command -v lz4 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzl4_');
	if ($tmp === false) {
		return null;
	}
	if (@file_put_contents($tmp, $inner) === false) {
		@unlink($tmp);
		return null;
	}
	$cmd = escapeshellarg($bin) . ' -' . $level . ' -c ' . escapeshellarg($tmp) . ' 2>/dev/null';
	$blob = shell_exec($cmd);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '' || !str_starts_with($blob, "\x04\x22\x4d\x18")) {
		return null;
	}
	return $blob;
}

/**
 * Compress bytes with shell brotli at quality $q (0–11). Returns null on failure.
 */
function fractal_zip_folder_brotli_shell_compress(string $inner, int $quality): ?string {
	$quality = max(0, min(11, $quality));
	$bin = trim((string) shell_exec('command -v brotli 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzbr_');
	if ($tmp === false) {
		return null;
	}
	if (@file_put_contents($tmp, $inner) === false) {
		@unlink($tmp);
		return null;
	}
	$cmd = escapeshellarg($bin) . ' -q ' . $quality . ' -c ' . escapeshellarg($tmp) . ' 2>/dev/null';
	$blob = shell_exec($cmd);
	@unlink($tmp);
	if (!is_string($blob) || $blob === '') {
		return null;
	}
	return $blob;
}

/**
 * Find lzma level (0–9) that bit-exactly reproduces $original.
 */
function fractal_zip_folder_lzma_find_rewrap_level(string $inner, string $original): ?int {
	for ($lvl = 0; $lvl <= 9; $lvl++) {
		$c = fractal_zip_folder_lzma_shell_compress($inner, $lvl);
		if ($c !== null && $c === $original) {
			return $lvl;
		}
	}
	return null;
}

/**
 * Find lz4 level (1–12) that bit-exactly reproduces $original.
 */
function fractal_zip_folder_lz4_find_rewrap_level(string $inner, string $original): ?int {
	for ($lvl = 1; $lvl <= 12; $lvl++) {
		$c = fractal_zip_folder_lz4_shell_compress($inner, $lvl);
		if ($c !== null && $c === $original) {
			return $lvl;
		}
	}
	return null;
}

/**
 * Find brotli quality (0–11) that bit-exactly reproduces $original.
 */
function fractal_zip_folder_brotli_find_rewrap_level(string $inner, string $original): ?int {
	for ($q = 0; $q <= 11; $q++) {
		$c = fractal_zip_folder_brotli_shell_compress($inner, $q);
		if ($c !== null && $c === $original) {
			return $q;
		}
	}
	return null;
}

/**
 * Find bzip2 level that bit-exactly reproduces $original from $inner.
 */
function fractal_zip_folder_bz2_find_rewrap_level(string $inner, string $original): ?int {
	if (function_exists('bzcompress')) {
		for ($lvl = 1; $lvl <= 9; $lvl++) {
			$c = @bzcompress($inner, $lvl);
			if (is_string($c) && $c === $original) {
				return $lvl;
			}
		}
	}
	for ($lvl = 1; $lvl <= 9; $lvl++) {
		$c = fractal_zip_folder_bzip2_shell_compress($inner, $lvl);
		if ($c !== null && $c === $original) {
			return $lvl;
		}
	}
	return null;
}

/**
 * Find xz level (0–9) that bit-exactly reproduces $original.
 */
function fractal_zip_folder_xz_find_rewrap_level(string $inner, string $original): ?int {
	for ($lvl = 0; $lvl <= 9; $lvl++) {
		$c = fractal_zip_folder_xz_shell_compress($inner, $lvl);
		if ($c !== null && $c === $original) {
			return $lvl;
		}
	}
	return null;
}

/**
 * Find zstd level that bit-exactly reproduces $original.
 * Tries default (with checksum) then --no-check.
 *
 * @return array{level: int, no_check: bool}|null
 */
function fractal_zip_folder_zstd_find_rewrap(string $inner, string $original): ?array {
	$levels = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19);
	foreach (array(false, true) as $noCheck) {
		foreach ($levels as $lvl) {
			$c = fractal_zip_folder_zstd_shell_compress($inner, $lvl, $noCheck);
			if ($c !== null && $c === $original) {
				return array('level' => $lvl, 'no_check' => $noCheck);
			}
		}
	}
	return null;
}

/**
 * @deprecated use fractal_zip_folder_zstd_find_rewrap
 */
function fractal_zip_folder_zstd_find_rewrap_level(string $inner, string $original): ?int {
	$r = fractal_zip_folder_zstd_find_rewrap($inner, $original);
	return $r !== null ? $r['level'] : null;
}

/**
 * If $inner is a classic ustar or newc cpio, try nested CLASSIC formats for the outer wrapper.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 * @return bool true when nested CLASSIC restore was committed
 */
function fractal_zip_folder_try_nested_archive_classic(
	string $diskPath,
	string $diskBytes,
	string $inner,
	string $meta,
	array $formatCandidates,
	array &$members,
	array &$restore
): bool {
	if (!function_exists('fractal_zip_classic_rebuild_archive')) {
		return false;
	}
	if (!function_exists('fractal_zip_literal_tar_list_file_members')) {
		$tar = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
		if (is_file($tar)) {
			require_once $tar;
		}
	}
	if (!function_exists('fractal_zip_literal_cpio_newc_list_file_members')) {
		$cpio = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_cpio_newc.php';
		if (is_file($cpio)) {
			require_once $cpio;
		}
	}
	$tries = array();
	if (function_exists('fractal_zip_literal_tar_list_file_members')) {
		$tl = fractal_zip_literal_tar_list_file_members($inner);
		if (is_array($tl) && count($tl) >= 2) {
			$tries[] = array('list' => $tl, 'kinds' => array_values(array_filter(
				$formatCandidates,
				static fn(string $f): bool => str_starts_with($f, 'tar')
			)));
		}
	}
	if (function_exists('fractal_zip_literal_cpio_newc_list_file_members')
		&& function_exists('fractal_zip_literal_cpio_newc_magic6_ok')
		&& fractal_zip_literal_cpio_newc_magic6_ok(substr($inner, 0, 6))) {
		$cl = fractal_zip_literal_cpio_newc_list_file_members($inner);
		if (is_array($cl) && count($cl) >= 2) {
			$tries[] = array('list' => $cl, 'kinds' => array_values(array_filter(
				$formatCandidates,
				static fn(string $f): bool => str_starts_with($f, 'cpio')
			)));
		}
	}
	foreach ($tries as $try) {
		$nestedPayloads = array();
		$nestedNames = array();
		foreach ($try['list'] as $m) {
			$n = str_replace('\\', '/', (string) ($m['name'] ?? ''));
			$d = (string) ($m['data'] ?? '');
			if ($n === '' || str_contains($n, '..')) {
				continue;
			}
			$nestedPayloads[] = array('name' => $n, 'data' => $d);
			$nestedNames[] = $n;
		}
		if (count($nestedNames) < 2) {
			continue;
		}
		foreach ($try['kinds'] as $fmt) {
			$reNested = fractal_zip_classic_rebuild_archive($fmt, $meta, $nestedPayloads);
			if ($reNested !== null && $reNested === $diskBytes) {
				$addedNew = array();
				$fzhrNames = array();
				foreach ($nestedPayloads as $p) {
					$legacy = rtrim($diskPath, '/') . '/' . $p['name'];
					$fzhrNames[] = fractal_zip_folder_register_shared_payload(
						$members, $p['data'], $p['name'], $legacy, $addedNew
					);
				}
				$restore[$diskPath] = array(
					'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
					'format' => $fmt,
					'meta' => $meta,
					'member_names' => $fzhrNames,
				);
				return true;
			}
		}
	}
	return false;
}

/**
 * When compress-wrapper payload is SMF MIDI, peel tracks as nested CLASSIC
 * (format midibz2 / midixz / midizstd / midilz4 / midibr / midilzma).
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_nested_midi_classic(
	string $diskPath,
	string $diskBytes,
	string $inner,
	string $compressFormat,
	string $meta,
	array &$members,
	array &$restore
): bool {
	if (!function_exists('fractal_zip_classic_rebuild_archive')) {
		return false;
	}
	$chunk = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_chunk_container_peel.php';
	if (is_readable($chunk)) {
		require_once $chunk;
	}
	if (!function_exists('fractal_zip_chunk_list_midi_smf')) {
		return false;
	}
	$list = fractal_zip_chunk_list_midi_smf($inner);
	if ($list === null || count($list) < 2) {
		return false;
	}
	$fmtMap = array(
		'bz2' => 'midibz2',
		'xz' => 'midixz',
		'zstd' => 'midizstd',
		'lz4' => 'midilz4',
		'lzip' => 'midilz',
		'br' => 'midibr',
		'lzma' => 'midilzma',
		'gzip' => 'midigz',
	);
	$fmt = $fmtMap[$compressFormat] ?? null;
	if ($fmt === null) {
		return false;
	}
	$payloads = array();
	$names = array();
	$addedNew = array();
	foreach ($list as $m) {
		$n = (string) ($m['name'] ?? '');
		$d = (string) ($m['data'] ?? '');
		if ($n === '' || str_contains($n, '..')) {
			return false;
		}
		$payloads[] = array('name' => $n, 'data' => $d);
		$legacy = rtrim($diskPath, '/') . '/' . $n;
		$names[] = fractal_zip_folder_register_shared_payload($members, $d, $n, $legacy, $addedNew);
	}
	$re = fractal_zip_classic_rebuild_archive($fmt, $meta, $payloads);
	if ($re === null || $re !== $diskBytes) {
		foreach ($addedNew as $mn) {
			unset($members[$mn]);
		}
		return false;
	}
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
		'format' => $fmt,
		'meta' => $meta,
		'member_names' => $names,
	);
	return true;
}

/**
 * Expand single-stream bzip2/xz/zstd into one logical member.
 * Prefer CLASSIC rebuild when a bit-exact rewrap exists; otherwise VERBATIM.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_compress(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_compress_peel_enabled()) {
		return false;
	}
	$inner = fractal_zip_folder_decode_single_compress($diskBytes, $diskPath);
	if ($inner === null || $inner === '') {
		return false;
	}
	$base = strtolower(basename($diskPath));
	$innerName = preg_replace('/\.(bz2|xz|zst|zstd|lzma|lz4|lz|lzip|br|brotli)$/i', '', $base) ?: 'payload';
	if ($innerName === $base || $innerName === '') {
		$innerName = 'payload.bin';
	}
	$logical = rtrim($diskPath, '/') . '/' . $innerName;

	$format = null;
	$lvl = null;
	if (str_starts_with($diskBytes, 'BZh')) {
		$format = 'bz2';
		$lvl = fractal_zip_folder_bz2_find_rewrap_level($inner, $diskBytes);
	} elseif (str_starts_with($diskBytes, "\xfd7zXZ\x00")) {
		$format = 'xz';
		$lvl = fractal_zip_folder_xz_find_rewrap_level($inner, $diskBytes);
	} elseif (ord($diskBytes[0]) === 0x5d) {
		$format = 'lzma';
		$lvl = fractal_zip_folder_lzma_find_rewrap_level($inner, $diskBytes);
	} elseif (str_starts_with($diskBytes, "\x04\x22\x4d\x18")) {
		$format = 'lz4';
		$lvl = fractal_zip_folder_lz4_find_rewrap_level($inner, $diskBytes);
	} elseif (str_starts_with($diskBytes, 'LZIP')) {
		$format = 'lzip';
		$lvl = fractal_zip_folder_lzip_find_rewrap_level($inner, $diskBytes);
	} elseif (str_ends_with($base, '.br') || str_ends_with($base, '.brotli')) {
		$format = 'br';
		$lvl = fractal_zip_folder_brotli_find_rewrap_level($inner, $diskBytes);
	} elseif (str_starts_with($diskBytes, "\x28\xb5\x2f\xfd")) {
		$format = 'zstd';
		$zpick = fractal_zip_folder_zstd_find_rewrap($inner, $diskBytes);
		if ($zpick !== null) {
			$lvl = (int) $zpick['level'];
			// meta: "3" or "3n" (n = --no-check)
			$metaStr = (string) $lvl . (!empty($zpick['no_check']) ? 'n' : '');
			$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
			if (is_readable($classic)) {
				require_once $classic;
			}
			if (function_exists('fractal_zip_classic_rebuild_archive')) {
				if (fractal_zip_folder_try_nested_archive_classic(
					$diskPath,
					$diskBytes,
					$inner,
					$metaStr,
					array('tarzst', 'cpiozst'),
					$members,
					$restore
				)) {
					return true;
				}
				if (fractal_zip_folder_try_nested_midi_classic(
					$diskPath,
					$diskBytes,
					$inner,
					'zstd',
					$metaStr,
					$members,
					$restore
				)) {
					return true;
				}
				$rebuilt = fractal_zip_classic_rebuild_archive('zstd', $metaStr, array(
					array('name' => $innerName, 'data' => $inner),
				));
				if ($rebuilt !== null && $rebuilt === $diskBytes) {
					fractal_zip_folder_commit_shared_classic_inner(
						$members, $restore, $diskPath, $inner, $innerName, $logical, 'zstd', $metaStr
					);
					return true;
				}
			}
		}
		$lvl = null;
		$format = null;
	}

	if ($format !== null && $lvl !== null) {
		$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
		if (is_readable($classic)) {
			require_once $classic;
		}
		if (function_exists('fractal_zip_classic_rebuild_archive')) {
			$nestedMap = array(
				'xz' => array('tarxz', 'cpioxz'),
				'bz2' => array('tarbz2', 'cpiobz2'),
				'lzma' => array(), // raw lzma rarely wraps multi-member archives in the wild
				'lz4' => array('tarlz4', 'cpiolz4'),
				'lzip' => array('tarlz', 'cpiolz'),
				'br' => array('tarbr', 'cpiobr'),
			);
			$cands = $nestedMap[$format] ?? array();
			if ($cands !== [] && fractal_zip_folder_try_nested_archive_classic(
				$diskPath,
				$diskBytes,
				$inner,
				(string) $lvl,
				$cands,
				$members,
				$restore
			)) {
				return true;
			}
			// MIDI × compress: peel SMF tracks under the wrapper (midibz2 / midixz / …).
			if (fractal_zip_folder_try_nested_midi_classic(
				$diskPath,
				$diskBytes,
				$inner,
				$format,
				(string) $lvl,
				$members,
				$restore
			)) {
				return true;
			}
			$rebuilt = fractal_zip_classic_rebuild_archive($format, (string) $lvl, array(
				array('name' => $innerName, 'data' => $inner),
			));
			if ($rebuilt !== null && $rebuilt === $diskBytes) {
				fractal_zip_folder_commit_shared_classic_inner(
					$members, $restore, $diskPath, $inner, $innerName, $logical, $format, (string) $lvl
				);
				return true;
			}
		}
	}

	fractal_zip_folder_commit_shared_verbatim_inner(
		$members, $restore, $diskPath, $diskBytes, $inner, $innerName, $logical
	);
	return true;
}

function fractal_zip_folder_woff_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_WOFF_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Expand WOFF2 into inner TTF via woff2_decompress.
 * Prefer CLASSIC rebuild when woff2_compress bit-exactly reproduces the original.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_woff(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_woff_peel_enabled() || strlen($diskBytes) < 8) {
		return false;
	}
	// woff2 signature "wOF2"; classic woff "wOFF" left for future (needs different tool).
	if (!str_starts_with($diskBytes, 'wOF2')) {
		return false;
	}
	$bin = trim((string) shell_exec('command -v woff2_decompress 2>/dev/null'));
	if ($bin === '') {
		return false;
	}
	$max = 32 * 1024 * 1024;
	$eMax = getenv('FRACTAL_ZIP_FOLDER_WOFF_MAX_INNER');
	if ($eMax !== false && (int) $eMax > 0) {
		$max = max(1024, (int) $eMax);
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzwoff_');
	if ($tmp === false) {
		return false;
	}
	$woffPath = $tmp . '.woff2';
	@unlink($tmp);
	if (@file_put_contents($woffPath, $diskBytes) === false) {
		@unlink($woffPath);
		return false;
	}
	$ttfPath = preg_replace('/\.woff2$/i', '.ttf', $woffPath) ?: ($woffPath . '.ttf');
	@unlink($ttfPath);
	$cmd = escapeshellarg($bin) . ' ' . escapeshellarg($woffPath) . ' 2>/dev/null';
	exec($cmd, $_, $code);
	@unlink($woffPath);
	if ($code !== 0 || !is_file($ttfPath)) {
		@unlink($ttfPath);
		return false;
	}
	$inner = @file_get_contents($ttfPath);
	@unlink($ttfPath);
	if (!is_string($inner) || $inner === '' || strlen($inner) > $max) {
		return false;
	}
	$base = strtolower(basename($diskPath));
	$innerName = preg_replace('/\.woff2$/i', '.ttf', $base) ?: 'font.ttf';
	if ($innerName === $base) {
		$innerName = 'font.ttf';
	}
	$logical = rtrim($diskPath, '/') . '/' . $innerName;

	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (function_exists('fractal_zip_classic_rebuild_archive')) {
		$rebuilt = fractal_zip_classic_rebuild_archive('woff2', '', array(
			array('name' => $innerName, 'data' => $inner),
		));
		if ($rebuilt !== null && $rebuilt === $diskBytes) {
			fractal_zip_folder_commit_shared_classic_inner(
				$members, $restore, $diskPath, $inner, $innerName, $logical, 'woff2', ''
			);
			return true;
		}
	}

	fractal_zip_folder_commit_shared_verbatim_inner(
		$members, $restore, $diskPath, $diskBytes, $inner, $innerName, $logical
	);
	return true;
}

/**
 * Compress TTF/OTF bytes to WOFF2 via woff2_compress. Returns null on failure.
 */
function fractal_zip_folder_woff2_shell_compress(string $ttfBytes): ?string {
	if ($ttfBytes === '') {
		return null;
	}
	$bin = trim((string) shell_exec('command -v woff2_compress 2>/dev/null'));
	if ($bin === '') {
		return null;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzw2_');
	if ($tmp === false) {
		return null;
	}
	$ttfPath = $tmp . '.ttf';
	@unlink($tmp);
	if (@file_put_contents($ttfPath, $ttfBytes) === false) {
		@unlink($ttfPath);
		return null;
	}
	$cmd = 'cd ' . escapeshellarg(dirname($ttfPath)) . ' && ' . escapeshellarg($bin)
		. ' ' . escapeshellarg(basename($ttfPath)) . ' 2>/dev/null';
	exec($cmd, $_, $code);
	$woffPath = preg_replace('/\.ttf$/i', '.woff2', $ttfPath) ?: ($ttfPath . '.woff2');
	@unlink($ttfPath);
	if ($code !== 0 || !is_file($woffPath)) {
		@unlink($woffPath);
		return null;
	}
	$out = @file_get_contents($woffPath);
	@unlink($woffPath);
	if (!is_string($out) || $out === '' || !str_starts_with($out, 'wOF2')) {
		return null;
	}
	return $out;
}

function fractal_zip_folder_sqlite_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_SQLITE_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Peel SQLite DB into schema.sql + per-table CSV samples (restore VERBATIM).
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_sqlite(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_sqlite_peel_enabled()) {
		return false;
	}
	if (strlen($diskBytes) < 16 || !str_starts_with($diskBytes, 'SQLite format 3')) {
		return false;
	}

	// Prefer bit-exact page CLASSIC when enough pages (schema dump stays VERBATIM→PASS).
	$pageSize = (ord($diskBytes[16]) << 8) | ord($diskBytes[17]);
	if ($pageSize === 1) {
		$pageSize = 65536;
	}
	$n = strlen($diskBytes);
	if (in_array($pageSize, array(512, 1024, 2048, 4096, 8192, 16384, 32768, 65536), true)
		&& ($n % $pageSize) === 0
		&& $n >= $pageSize * 3) {
		$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
		if (is_readable($classic)) {
			require_once $classic;
		}
		if (function_exists('fractal_zip_classic_rebuild_archive')) {
			$names = array();
			$payloads = array();
			$addedNew = array();
			$useShare = function_exists('fractal_zip_folder_register_shared_payload');
			$pages = (int) ($n / $pageSize);
			$pageCap = 256;
			if ($pages <= $pageCap) {
				for ($i = 0; $i < $pages; $i++) {
					$page = substr($diskBytes, $i * $pageSize, $pageSize);
					$pn = 'p_' . sprintf('%04d', $i);
					$logical = rtrim($diskPath, '/') . '/' . $pn;
					if ($useShare) {
						$key = fractal_zip_folder_register_shared_payload(
							$members, $page, $pn, $logical, $addedNew
						);
						$names[] = function_exists('fractal_zip_folder_fzhr_share_name')
							? fractal_zip_folder_fzhr_share_name($key, $pn)
							: $key;
					} else {
						$members[$logical] = $page;
						$addedNew[] = $logical;
						$names[] = $logical;
					}
					$payloads[] = array('name' => $pn, 'data' => $page);
				}
				$meta = (string) $pageSize;
				$rebuilt = fractal_zip_classic_rebuild_archive('sqlitepages', $meta, $payloads);
				if ($rebuilt !== null && $rebuilt === $diskBytes) {
					$restore[$diskPath] = array(
						'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
						'format' => 'sqlitepages',
						'meta' => $meta,
						'member_names' => $names,
					);
					return true;
				}
				foreach ($addedNew as $nm) {
					unset($members[$nm]);
				}
			}
		}
	}

	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_sqlite_' . getmypid() . '_' . md5($diskPath) . '.db';
	if (@file_put_contents($tmp, $diskBytes) === false) {
		return false;
	}
	$names = array();
	$rowCap = 200;
	$eRows = getenv('FRACTAL_ZIP_FOLDER_SQLITE_MAX_ROWS');
	if ($eRows !== false && (int) $eRows > 0) {
		$rowCap = max(10, (int) $eRows);
	}
	$tableCap = 32;

	$finish = static function () use ($tmp, $diskPath, $diskBytes, &$members, &$restore, &$names): bool {
		@unlink($tmp);
		if ($names === array()) {
			return false;
		}
		$restore[$diskPath] = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
			'verbatim' => $diskBytes,
			'member_names' => $names,
		);
		return true;
	};

	if (extension_loaded('pdo_sqlite')) {
		try {
			$pdo = new PDO('sqlite:' . $tmp);
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$schema = '';
			foreach ($pdo->query("SELECT type, name, sql FROM sqlite_master WHERE sql IS NOT NULL ORDER BY type, name") as $row) {
				$schema .= (string) ($row['sql'] ?? '') . ";\n";
			}
			if ($schema === '') {
				@unlink($tmp);
				return false;
			}
			$schemaPath = rtrim($diskPath, '/') . '/schema.sql';
			$members[$schemaPath] = $schema;
			$names[] = $schemaPath;
			$ti = 0;
			foreach ($pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name") as $row) {
				if ($ti >= $tableCap) {
					break;
				}
				$tname = (string) ($row['name'] ?? '');
				if ($tname === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $tname)) {
					continue;
				}
				$csv = '';
				$stmt = $pdo->query('SELECT * FROM "' . str_replace('"', '""', $tname) . '" LIMIT ' . (int) $rowCap);
				$cols = array();
				$first = true;
				while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
					if ($first) {
						$cols = array_keys($r);
						$csv .= implode(',', $cols) . "\n";
						$first = false;
					}
					$line = array();
					foreach ($cols as $c) {
						$v = $r[$c];
						if ($v === null) {
							$line[] = '';
						} elseif (is_numeric($v)) {
							$line[] = (string) $v;
						} else {
							$line[] = '"' . str_replace('"', '""', (string) $v) . '"';
						}
					}
					$csv .= implode(',', $line) . "\n";
				}
				if ($csv === '') {
					$csv = "# empty table {$tname}\n";
				}
				$logical = rtrim($diskPath, '/') . '/tables/' . $tname . '.csv';
				$members[$logical] = $csv;
				$names[] = $logical;
				$ti++;
			}
			return $finish();
		} catch (Throwable $e) {
			@unlink($tmp);
			return false;
		}
	}

	// Fallback: sqlite3 CLI (common when pdo_sqlite is absent).
	$sqlite3 = trim((string) shell_exec('command -v sqlite3 2>/dev/null'));
	if ($sqlite3 === '' || !is_executable($sqlite3)) {
		@unlink($tmp);
		return false;
	}
	$schema = (string) shell_exec(escapeshellarg($sqlite3) . ' ' . escapeshellarg($tmp) . ' .schema 2>/dev/null');
	if (trim($schema) === '') {
		@unlink($tmp);
		return false;
	}
	$schemaPath = rtrim($diskPath, '/') . '/schema.sql';
	$members[$schemaPath] = $schema;
	$names[] = $schemaPath;
	$tablesOut = (string) shell_exec(escapeshellarg($sqlite3) . ' ' . escapeshellarg($tmp)
		. ' "SELECT name FROM sqlite_master WHERE type=\'table\' AND name NOT LIKE \'sqlite_%\' ORDER BY name;" 2>/dev/null');
	$ti = 0;
	foreach (preg_split('/\R/', $tablesOut) ?: array() as $tname) {
		$tname = trim($tname);
		if ($tname === '' || !preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $tname) || $ti >= $tableCap) {
			continue;
		}
		$csv = (string) shell_exec(escapeshellarg($sqlite3) . ' -header -csv ' . escapeshellarg($tmp)
			. ' "SELECT * FROM \\"' . $tname . '\\" LIMIT ' . (int) $rowCap . ';" 2>/dev/null');
		if ($csv === '') {
			$csv = "# empty table {$tname}\n";
		}
		$logical = rtrim($diskPath, '/') . '/tables/' . $tname . '.csv';
		$members[$logical] = $csv;
		$names[] = $logical;
		$ti++;
	}
	return $finish();
}

function fractal_zip_folder_eml_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_EML_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Split .eml into headers + body text members (restore VERBATIM).
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_eml(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_eml_peel_enabled() || $diskBytes === '') {
		return false;
	}
	$base = strtolower(basename($diskPath));
	$looksEml = (bool) preg_match('/\.(eml|emlx|mht|mhtml|mbox)$/', $base)
		|| preg_match('/^(From|Return-Path|Received|MIME-Version|Content-Type):/mi', substr($diskBytes, 0, 4096));
	if (!$looksEml) {
		return false;
	}
	// mbox: peel first N messages as separate members.
	if (str_ends_with($base, '.mbox') || preg_match('/^From /', $diskBytes)) {
		$parts = preg_split('/(?=^From )/m', $diskBytes) ?: array();
		$parts = array_values(array_filter($parts, static fn($p) => trim((string) $p) !== ''));
		if (count($parts) < 2) {
			// single message — fall through to header/body split
		} else {
			$cap = 64;
			$eCap = getenv('FRACTAL_ZIP_FOLDER_MBOX_MAX_MSGS');
			if ($eCap !== false && (int) $eCap > 0) {
				$cap = max(2, (int) $eCap);
			}
			$names = array();
			$addedNew = array();
			$useShare = function_exists('fractal_zip_folder_register_shared_payload');
			$i = 0;
			foreach ($parts as $msg) {
				if ($i >= $cap) {
					break;
				}
				$short = 'msg_' . sprintf('%04d', $i + 1) . '.eml';
				$logical = rtrim($diskPath, '/') . '/' . $short;
				$data = (string) $msg;
				if ($useShare) {
					$reg = fractal_zip_folder_register_shared_payload(
						$members, $data, $short, $logical, $addedNew
					);
					$names[] = fractal_zip_folder_fzhr_share_name($reg, $short);
				} else {
					$members[$logical] = $data;
					$addedNew[] = $logical;
					$names[] = $logical;
				}
				$i++;
			}
			if (count($names) < 2) {
				foreach ($addedNew as $n) {
					unset($members[$n]);
				}
				return false;
			}
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
				'verbatim' => $diskBytes,
				'member_names' => $names,
			);
			return true;
		}
	}
	$chunks = preg_split("/\r\n\r\n|\n\n/", $diskBytes, 2);
	$headers = (string) ($chunks[0] ?? '');
	$body = (string) ($chunks[1] ?? '');
	if ($headers === '' || strlen($headers) < 8) {
		return false;
	}
	$sep = "\n\n";
	if (str_starts_with($diskBytes, $headers . "\r\n\r\n")) {
		$sep = "\r\n\r\n";
	} elseif (!str_starts_with($diskBytes, $headers . "\n\n")) {
		// Ambiguous / multipart boundary — keep VERBATIM.
		$sep = '';
	}
	$hPath = rtrim($diskPath, '/') . '/headers.txt';
	$bPath = rtrim($diskPath, '/') . '/body.txt';
	$addedNew = array();
	$useShare = function_exists('fractal_zip_folder_register_shared_payload');
	if ($useShare) {
		$hKey = fractal_zip_folder_register_shared_payload(
			$members, $headers, 'headers.txt', $hPath, $addedNew
		);
		$bKey = fractal_zip_folder_register_shared_payload(
			$members, $body, 'body.txt', $bPath, $addedNew
		);
		$fzhrNames = array(
			fractal_zip_folder_fzhr_share_name($hKey, 'headers.txt'),
			fractal_zip_folder_fzhr_share_name($bKey, 'body.txt'),
		);
	} else {
		$members[$hPath] = $headers;
		$members[$bPath] = $body;
		$addedNew = array($hPath, $bPath);
		$fzhrNames = array($hPath, $bPath);
	}
	$payloads = array(
		array('name' => 'headers.txt', 'data' => $headers),
		array('name' => 'body.txt', 'data' => $body),
	);
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if ($sep !== '' && is_readable($classic)) {
		require_once $classic;
	}
	if ($sep !== '' && function_exists('fractal_zip_classic_rebuild_archive')) {
		$bodyLen = strlen($body);
		// Tiny stubs: PASSTHROUGH beats forced CLASSIC hetero.
		if (!($bodyLen < 64 && strlen($diskBytes) < 256)) {
			$rebuilt = fractal_zip_classic_rebuild_archive('eml', $sep, $payloads);
			if ($rebuilt !== null && $rebuilt === $diskBytes) {
				$restore[$diskPath] = array(
					'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
					'format' => 'eml',
					'meta' => $sep,
					'member_names' => $fzhrNames,
				);
				return true;
			}
		}
	}
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM,
		'verbatim' => $diskBytes,
		'member_names' => $fzhrNames,
	);
	return true;
}

/**
 * @param array<string,string> $diskFilesByPath
 * @return array{members: array<string,string>, disk_layout: list<string>, restore: array<string,array>, expanded: bool}
 */
function fractal_zip_resolve_folder_logical_bundle(array $diskFilesByPath): array {
	if (function_exists('fractal_zip_ensure_literal_pac_stack_loaded')) {
		fractal_zip_ensure_literal_pac_stack_loaded();
	}
	if (function_exists('fractal_zip_ensure_folder_per_member_best_loaded')) {
		fractal_zip_ensure_folder_per_member_best_loaded();
	}
	if (!function_exists('fractal_zip_folder_try_expand_classic')) {
		$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
		if (is_readable($classic)) {
			require_once $classic;
		}
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	$chunk = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_chunk_container_peel.php';
	if (is_readable($chunk)) {
		require_once $chunk;
	}
	$members = array();
	$diskLayout = array();
	$restore = array();
	$expanded = false;
	$oleNearDupHandled = array();
	if (fractal_zip_folder_apply_ole_near_dup_patches(
		$diskFilesByPath,
		$members,
		$restore,
		$oleNearDupHandled
	)) {
		$expanded = true;
	}
	if (fractal_zip_folder_apply_wrapper_near_dup_patches(
		$diskFilesByPath,
		$members,
		$restore,
		$oleNearDupHandled
	)) {
		$expanded = true;
	}
	$diskPaths = array_keys($diskFilesByPath);
	sort($diskPaths, SORT_STRING);
	foreach ($diskPaths as $diskPath) {
		$diskPath = str_replace('\\', '/', (string) $diskPath);
		if ($diskPath === '' || strpos($diskPath, '..') !== false || strpos($diskPath, "\0") !== false) {
			continue;
		}
		$diskBytes = (string) ($diskFilesByPath[$diskPath] ?? '');
		$diskLayout[] = $diskPath;
		if (isset($oleNearDupHandled[$diskPath])) {
			continue;
		}
		$peelMaxRaw = function_exists('fractal_zip_folder_zip_peel_max_raw_bytes')
			? fractal_zip_folder_zip_peel_max_raw_bytes()
			: null;
		$zipMembersFull = fractal_zip_literal_pac_list_zip_members_for_mode18($diskBytes, $peelMaxRaw);
		$zipMembers = $zipMembersFull;
		$hybridFiltered = false;
		if ($zipMembersFull !== null && count($zipMembersFull) >= 2) {
			if (!function_exists('fractal_zip_literal_pac_list_zip_members_for_mode18_scoped')) {
				$hp = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_hybrid_peel.php';
				if (is_readable($hp)) {
					require_once $hp;
				}
			}
			if (function_exists('fractal_zip_literal_pac_list_zip_members_for_mode18_scoped')
				&& function_exists('fractal_zip_hybrid_peel_enabled')
				&& fractal_zip_hybrid_peel_enabled()) {
				$scoped = fractal_zip_literal_pac_list_zip_members_for_mode18_scoped($diskBytes, $diskPath, $peelMaxRaw);
				// Only adopt scoped list when members were actually dropped — entropy
				// reorder alone would break bit-exact ZIP_MULTI rebuild order.
				if (is_array($scoped) && count($scoped) >= 2 && count($scoped) < count($zipMembersFull)) {
					$zipMembers = $scoped;
					$hybridFiltered = true;
				}
			} elseif (function_exists('fractal_zip_hybrid_is_container')
				&& fractal_zip_hybrid_is_container($diskPath, $diskBytes)
				&& function_exists('fractal_zip_hybrid_filter_zip_members')) {
				$filtered = fractal_zip_hybrid_filter_zip_members($diskPath, $zipMembersFull);
				if (is_array($filtered) && count($filtered) >= 2 && count($filtered) < count($zipMembersFull)) {
					$zipMembers = $filtered;
					$hybridFiltered = true;
				}
			}
		}
		if ($zipMembers !== null && count($zipMembers) >= 2) {
			$expanded = true;
			list($rebuildRows, $memberNames) = fractal_zip_folder_zip_install_members(
				$diskPath, $zipMembers, $members
			);
			if ($memberNames === array()) {
				$members[$diskPath] = $diskBytes;
				$restore[$diskPath] = array('kind' => FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
				continue;
			}
			// Hybrid noise drop: prefer ZIP_MULTI (+ patch) over VERBATIM double-pay.
			if ($hybridFiltered && is_array($zipMembersFull)) {
				$restore[$diskPath] = fractal_zip_folder_hybrid_filtered_zip_restore(
					$diskPath,
					$diskBytes,
					$zipMembersFull,
					$zipMembers,
					$members,
					$memberNames
				);
				continue;
			}
			$spec = fractal_zip_folder_zip_multi_spec_from_members(
				$rebuildRows,
				$memberNames,
				$diskBytes,
				0
			);
			if ($spec !== null) {
				$restore[$diskPath] = $spec;
			} else {
				$restore[$diskPath] = fractal_zip_folder_zip_collapse_to_passthrough(
					$diskPath,
					$diskBytes,
					$members
				);
			}
		} else {
			$rpm7z = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_rpm_7z_peel.php';
			if (is_readable($rpm7z)) {
				require_once $rpm7z;
			}
			$cabIso = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_cab_iso_peel.php';
			if (is_readable($cabIso)) {
				require_once $cabIso;
			}
			$pePeel = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pe_peel.php';
			if (is_readable($pePeel)) {
				require_once $pePeel;
			}
			if (fractal_zip_folder_try_expand_gzip($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_compress($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_woff($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_chunk($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_tar($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_ar($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_cpio($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_sg_dat($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_eml($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_sqlite($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_text($diskPath, $diskBytes, $members, $restore)
				|| fractal_zip_folder_try_expand_ole($diskPath, $diskBytes, $members, $restore)
				|| (function_exists('fractal_zip_folder_try_expand_pe')
					? fractal_zip_folder_try_expand_pe($diskPath, $diskBytes, $members, $restore)
					: false)
				|| (function_exists('fractal_zip_folder_try_expand_rpm')
					? fractal_zip_folder_try_expand_rpm($diskPath, $diskBytes, $members, $restore)
					: false)
				|| (function_exists('fractal_zip_folder_try_expand_7z')
					? fractal_zip_folder_try_expand_7z($diskPath, $diskBytes, $members, $restore)
					: false)
				|| (function_exists('fractal_zip_folder_try_expand_cab')
					? fractal_zip_folder_try_expand_cab($diskPath, $diskBytes, $members, $restore)
					: false)
				|| (function_exists('fractal_zip_folder_try_expand_iso')
					? fractal_zip_folder_try_expand_iso($diskPath, $diskBytes, $members, $restore)
					: false)
				|| (function_exists('fractal_zip_folder_try_expand_classic')
					? fractal_zip_folder_try_expand_classic($diskPath, $diskBytes, $members, $restore)
					: false)) {
				$expanded = true;
			} else {
				$members[$diskPath] = $diskBytes;
				$restore[$diskPath] = array('kind' => FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
			}
		}
	}
	if (fractal_zip_folder_apply_nested_leaf_peel($members, $restore)) {
		$expanded = true;
	}
	$jpegShare = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_jpeg_marker_share.php';
	if (is_readable($jpegShare)) {
		require_once $jpegShare;
	}
	if (function_exists('fractal_zip_folder_apply_jpeg_marker_share')
		&& fractal_zip_folder_apply_jpeg_marker_share($members, $restore, $diskFilesByPath)) {
		$expanded = true;
	}
	// VERBATIM stores the container raw in FZHR; PASSTHROUGH lets the per-member codec compress it.
	if (fractal_zip_folder_collapse_verbatim_restores($members, $restore)) {
		$expanded = true;
	}
	ksort($members, SORT_STRING);
	return array(
		'members' => $members,
		'disk_layout' => $diskLayout,
		'restore' => $restore,
		'expanded' => $expanded,
	);
}

/**
 * Convert VERBATIM restores to PASSTHROUGH of the original container bytes.
 * Peel members under those paths are dropped — they were tournament-only and would
 * be stripped from the final FZHM anyway, while FZHR verbatim paid the blob uncompressed.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_collapse_verbatim_restores(array &$members, array &$restore): bool {
	static $enabled = null;
	if ($enabled === null) {
		$e = getenv('FRACTAL_ZIP_FOLDER_VERBATIM_AS_PASSTHROUGH');
		if ($e === false || trim((string) $e) === '') {
			$enabled = true;
		} else {
			$v = strtolower(trim((string) $e));
			$enabled = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
		}
	}
	if (!$enabled || $restore === array()) {
		return false;
	}
	$did = false;
	foreach ($restore as $diskPath => $spec) {
		if (!is_array($spec)) {
			continue;
		}
		if ((int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH) !== FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			continue;
		}
		$verb = (string) ($spec['verbatim'] ?? '');
		if ($verb === '') {
			continue;
		}
		$diskPath = (string) $diskPath;
		fractal_zip_folder_zip_collapse_to_passthrough($diskPath, $verb, $members);
		$restore[$diskPath] = array('kind' => FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		$did = true;
	}
	return $did;
}

/**
 * Depth-1 nested peel of ZIP/container leaves (gzip / chunk / woff / OLE) when the
 * owning restore is VERBATIM or PASSTHROUGH — never mutate ZIP_MULTI/CLASSIC member sets.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_apply_nested_leaf_peel(array &$members, array $restore): bool {
	static $enabled = null;
	if ($enabled === null) {
		$e = getenv('FRACTAL_ZIP_FOLDER_NESTED_PEEL');
		if ($e === false || trim((string) $e) === '') {
			$enabled = true;
		} else {
			$v = strtolower(trim((string) $e));
			$enabled = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
		}
	}
	if (!$enabled || $members === []) {
		return false;
	}

	$locked = array();
	$verbatimRoots = array();
	foreach ($restore as $diskPath => $spec) {
		$kind = (int) ($spec['kind'] ?? 0);
		$diskPath = str_replace('\\', '/', (string) $diskPath);
		$prefix = rtrim($diskPath, '/') . '/';
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI
			|| $kind === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			foreach ($spec['member_names'] ?? array() as $n) {
				$n = str_replace('\\', '/', (string) $n);
				if ($n === '') {
					continue;
				}
				$locked[$n] = true;
				if (str_contains($n, "\x1e")) {
					$blob = explode("\x1e", $n, 2)[0];
					if ($blob !== '') {
						$locked[$blob] = true;
					}
					$n = (string) (explode("\x1e", $n, 2)[1] ?? '');
					if ($n === '') {
						continue;
					}
				}
				// Compress/gzip/woff CLASSIC often stores short names in FZHR while
				// members live at diskPath/short — lock both forms so nested peel
				// cannot steal the leaf (e.g. OLE inside .doc.bz2).
				if (str_starts_with($n, $prefix)) {
					$rel = substr($n, strlen($prefix));
					if ($rel !== '') {
						$locked[$rel] = true;
					}
				} elseif (!str_contains($n, '/')) {
					$locked[$prefix . $n] = true;
				}
			}
			// Belt: lock every current member under this disk path.
			foreach (array_keys($members) as $mp) {
				$mp = str_replace('\\', '/', (string) $mp);
				if ($mp === $diskPath || str_starts_with($mp, $prefix)) {
					$locked[$mp] = true;
				}
			}
		}
		// VERBATIM peels collapse to PASSTHROUGH next — nested work would be discarded.
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			$verbatimRoots[$prefix] = true;
			foreach ($spec['member_names'] ?? array() as $n) {
				$locked[(string) $n] = true;
			}
		}
	}

	$capLeaves = 64;
	$eCap = getenv('FRACTAL_ZIP_FOLDER_NESTED_MAX_LEAVES');
	if ($eCap !== false && (int) $eCap > 0) {
		$capLeaves = max(4, (int) $eCap);
	}

	$did = false;
	$nTried = 0;
	$paths = array_keys($members);
	sort($paths, SORT_STRING);
	foreach ($paths as $path) {
		if ($nTried >= $capLeaves) {
			break;
		}
		if (isset($locked[$path]) || !isset($members[$path])) {
			continue;
		}
		$underVerb = false;
		foreach ($verbatimRoots as $vp => $_) {
			if ($vp !== '/' && str_starts_with($path, $vp)) {
				$underVerb = true;
				break;
			}
		}
		if ($underVerb) {
			continue;
		}
		$bytes = (string) $members[$path];
		if (strlen($bytes) < 8 || strlen($bytes) > 16 * 1024 * 1024) {
			continue;
		}
		// Cheap magic gate before allocating nested maps.
		$head = substr($bytes, 0, 8);
		$maybe = str_starts_with($head, "\x1f\x8b")
			|| str_starts_with($head, 'BZh')
			|| str_starts_with($head, "\xfd7zXZ")
			|| str_starts_with($head, "\x28\xb5\x2f\xfd")
			|| str_starts_with($head, 'wOF2')
			|| str_starts_with($head, 'RIFF')
			|| str_starts_with($head, 'FORM')
			|| str_starts_with($head, 'MThd')
			|| (strlen($bytes) >= 8 && substr($bytes, 0, 4) === "\xd0\xcf\x11\xe0");
		if (!$maybe) {
			continue;
		}
		$nTried++;
		$nestedMembers = array();
		$nestedRestore = array();
		$ok = fractal_zip_folder_try_expand_gzip($path, $bytes, $nestedMembers, $nestedRestore)
			|| fractal_zip_folder_try_expand_compress($path, $bytes, $nestedMembers, $nestedRestore)
			|| fractal_zip_folder_try_expand_woff($path, $bytes, $nestedMembers, $nestedRestore)
			|| (function_exists('fractal_zip_folder_try_expand_chunk')
				? fractal_zip_folder_try_expand_chunk($path, $bytes, $nestedMembers, $nestedRestore)
				: false)
			|| fractal_zip_folder_try_expand_ole($path, $bytes, $nestedMembers, $nestedRestore);
		if (!$ok || count($nestedMembers) < 1) {
			continue;
		}
		// Tournament-only replace; outer VERBATIM/PASSTHROUGH restore unchanged.
		unset($members[$path]);
		foreach ($nestedMembers as $nk => $nv) {
			$members[$nk] = $nv;
		}
		$did = true;
	}
	return $did;
}

/**
 * @param array{members: array<string,string>, disk_layout?: list<string>, restore?: array<string,array>, expanded?: bool} $logical
 */
function fractal_zip_heterogeneous_folder_encode_enabled(array $logical): bool {
	if (function_exists('fractal_zip_ensure_folder_per_member_best_loaded')) {
		fractal_zip_ensure_folder_per_member_best_loaded();
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
			return false;
		}
		if ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes') {
			return count($logical['members'] ?? array()) >= fractal_zip_folder_per_member_best_auto_min_files();
		}
	}
	$members = $logical['members'] ?? array();
	$n = count($members);
	$minFiles = fractal_zip_folder_per_member_best_auto_min_files();
	if ($n < $minFiles) {
		return false;
	}
	[$maxMembers, ] = fractal_zip_literal_pac_zip_multimember_limits();
	if ($maxMembers > 0 && $n > $maxMembers) {
		return false;
	}
	return true;
}

/**
 * True when restore specs require rebuilding disk containers (not 1:1 loose member paths).
 *
 * @param array<string,array> $restore
 */
function fractal_zip_folder_restore_requires_disk_reassembly(array $restore): bool {
	foreach ($restore as $spec) {
		if (!is_array($spec)) {
			continue;
		}
		$kind = (int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI || $kind === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM
			|| $kind === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			return true;
		}
	}
	return false;
}

/**
 * Drop peeled ZIP members and keep the original container as PASSTHROUGH.
 *
 * @param array<string,string> $members
 */
function fractal_zip_folder_zip_collapse_to_passthrough(string $diskPath, string $diskBytes, array &$members): array {
	$prefix = rtrim($diskPath, '/') . '/';
	foreach (array_keys($members) as $k) {
		if ($k === $diskPath || str_starts_with((string) $k, $prefix)) {
			unset($members[$k]);
		}
	}
	$members[$diskPath] = $diskBytes;
	return array('kind' => FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
}

/**
 * True when a semantic ZIP rebuild patch is cheap enough to beat VERBATIM.
 * Near-full mid patches re-encode kept payloads and lose to orphan-stripped VERBATIM.
 */
function fractal_zip_folder_zip_patch_is_worthwhile(string $patch, int $diskLen, int $droppedBytes): bool {
	$pl = strlen($patch);
	if ($pl === 0) {
		return true;
	}
	if ($diskLen <= 0) {
		return false;
	}
	// Sparse header fixes (mtime/level) — always prefer over collapsing the peel.
	if ($pl >= 3 && substr($patch, 0, 2) === 'FP' && ord($patch[2]) === 2) {
		return $pl < (int) ($diskLen * 0.40);
	}
	// Hard cap: near-full rewrites always lose to paying the container once.
	// Fair ZIP peel: allow mid-size FP patches (was 40%) so noisy ZIPs stay ZIP_MULTI.
	$hardCap = 0.55;
	$eCap = getenv('FRACTAL_ZIP_FOLDER_ZIP_PATCH_HARD_CAP');
	if ($eCap !== false && is_numeric(trim((string) $eCap))) {
		$hardCap = max(0.20, min(0.90, (float) $eCap));
	}
	if ($pl >= (int) ($diskLen * $hardCap)) {
		return false;
	}
	// Prefer patches that fit dropped-noise + framing budget.
	$budget = max(256, $droppedBytes + 512);
	if ($pl <= (int) ($budget * 1.50)) {
		return true;
	}
	// Small structural-only diffs on near-matching full rebuilds (droppedBytes=0).
	return $droppedBytes === 0 && $pl < (int) ($diskLen * 0.40);
}

/**
 * @param list<array{name: string, data: string}> $full
 * @param list<array{name: string, data: string}> $kept
 */
function fractal_zip_folder_zip_dropped_raw_bytes(array $full, array $kept): int {
	$keptSet = array();
	foreach ($kept as $m) {
		$keptSet[(string) ($m['name'] ?? '')] = true;
	}
	$sum = 0;
	foreach ($full as $m) {
		$name = (string) ($m['name'] ?? '');
		if ($name === '' || isset($keptSet[$name])) {
			continue;
		}
		$sum += strlen((string) ($m['data'] ?? ''));
	}
	return $sum;
}

/**
 * Install ZIP members under diskPath/… keys; returns short-name rebuild list + logical names.
 * Preserves mtime/comp_method on rebuild rows for bit-exact ZIP_MULTI.
 *
 * @param list<array{name: string, data: string, mtime?: int, comp_method?: int}> $zipMembers
 * @param array<string,string> $members
 * @return array{0: list<array{name: string, data: string, mtime?: int, comp_method?: int}>, 1: list<string>}
 */
function fractal_zip_folder_zip_install_members(string $diskPath, array $zipMembers, array &$members): array {
	$prefix = rtrim($diskPath, '/') . '/';
	$rebuild = array();
	$logicalNames = array();
	$addedNew = array();
	foreach ($zipMembers as $m) {
		$name = (string) ($m['name'] ?? '');
		$data = (string) ($m['data'] ?? '');
		if ($name === '' || $data === '') {
			continue;
		}
		$archName = ltrim(str_replace('\\', '/', $name), '/');
		$legacy = $prefix . $archName;
		$key = fractal_zip_folder_register_shared_payload($members, $data, $archName, $legacy, $addedNew);
		$logicalNames[] = $key;
		$row = array('name' => $name, 'data' => $data);
		if (isset($m['mtime'])) {
			$row['mtime'] = (int) $m['mtime'];
		}
		if (isset($m['comp_method'])) {
			$row['comp_method'] = (int) $m['comp_method'];
		}
		$rebuild[] = $row;
	}
	return array($rebuild, $logicalNames);
}

/**
 * Prefer archive-relative short names in ZIP_MULTI FZHR (smaller trailer).
 * Kill: FRACTAL_ZIP_FOLDER_ZIP_FZHR_SHORT_NAMES=0 (legacy diskPath/name keys).
 */
function fractal_zip_folder_zip_fzhr_short_names_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_ZIP_FZHR_SHORT_NAMES');
	if ($e === false || trim((string) $e) === '') {
		// Off by default: short FZHR names remove redundancy with member path keys
		// in the unified stream and can grow small hybrid folders (slim 3160→3193).
		// Opt-in: FRACTAL_ZIP_FOLDER_ZIP_FZHR_SHORT_NAMES=1
		return $c = false;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Build ZIP_MULTI restore spec (optional FP\x03 profile + sparse/minimal patch).
 *
 * FZHR stores archive-relative short names (same as CLASSIC), not diskPath/name —
 * decode resolves them under the archive prefix. Saves ~diskPath length per member.
 *
 * @param list<array{name: string, data: string, mtime?: int, comp_method?: int}> $rebuild
 * @param list<string> $logicalNames full member keys (used when short-names disabled)
 * @return array<string,mixed>|null
 */
function fractal_zip_folder_zip_multi_spec_from_members(
	array $rebuild,
	array $logicalNames,
	string $diskBytes,
	int $dropBudget
): ?array {
	if ($rebuild === array()) {
		return null;
	}
	$useShort = fractal_zip_folder_zip_fzhr_short_names_enabled();
	$fzhrNames = array();
	if ($useShort) {
		foreach ($rebuild as $i => $m) {
			// Keep RS-alias keys from shared install; short names only for first-wins.
			$key = (string) ($logicalNames[$i] ?? '');
			if ($key !== '' && str_contains($key, "\x1e")) {
				$fzhrNames[] = $key;
				continue;
			}
			$n = str_replace('\\', '/', (string) ($m['name'] ?? ''));
			$n = ltrim($n, '/');
			if ($n === '' || str_contains($n, '..')) {
				return null;
			}
			$fzhrNames[] = $n;
		}
	} else {
		if ($logicalNames === array()) {
			return null;
		}
		$fzhrNames = $logicalNames;
	}
	if ($fzhrNames === array()) {
		return null;
	}
	$diskLen = strlen($diskBytes);
	$needProfile = false;
	foreach ($rebuild as $m) {
		if (((int) ($m['comp_method'] ?? 8)) === 0 || isset($m['mtime'])) {
			$needProfile = true;
			break;
		}
	}

	$matched = null;
	if (function_exists('fractal_zip_literal_pac_rebuild_zip_multi_matching')) {
		$matched = fractal_zip_literal_pac_rebuild_zip_multi_matching($rebuild, $diskBytes);
	}
	if (is_array($matched) && isset($matched['bytes'], $matched['level'])) {
		$level = (int) $matched['level'];
		if ($level !== 9) {
			$needProfile = true;
		}
		$spec = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI,
			'member_names' => $fzhrNames,
		);
		if ($needProfile && function_exists('fractal_zip_folder_zip_rebuild_profile_patch')) {
			$spec['semantic_patch'] = fractal_zip_folder_zip_rebuild_profile_patch($rebuild, $level, '');
		}
		return $spec;
	}

	$rebuilt = fractal_zip_literal_pac_rebuild_zip_multi_semantic($rebuild);
	if ($rebuilt === null || $rebuilt === '') {
		return null;
	}
	if ($rebuilt === $diskBytes) {
		$spec = array(
			'kind' => FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI,
			'member_names' => $fzhrNames,
		);
		if ($needProfile && function_exists('fractal_zip_folder_zip_rebuild_profile_patch')) {
			$spec['semantic_patch'] = fractal_zip_folder_zip_rebuild_profile_patch($rebuild, 9, '');
		}
		return $spec;
	}
	$patch = fractal_zip_folder_zip_patch_bytes($rebuilt, $diskBytes);
	if (!fractal_zip_folder_zip_patch_is_worthwhile($patch, $diskLen, $dropBudget)) {
		return null;
	}
	if ($needProfile && function_exists('fractal_zip_folder_zip_rebuild_profile_patch')) {
		$patch = fractal_zip_folder_zip_rebuild_profile_patch($rebuild, 9, $patch);
	}
	$spec = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI,
		'member_names' => $fzhrNames,
	);
	if ($patch !== '') {
		$spec['semantic_patch'] = $patch;
	}
	return $spec;
}

/**
 * Hybrid-filtered ZIP restore: prefer lossless / low-patch ZIP_MULTI; else VERBATIM
 * (final FZHM strips VERBATIM orphan wires so the container is paid once).
 *
 * @param list<array{name: string, data: string}> $zipMembersFull
 * @param list<array{name: string, data: string}> $zipMembersKept
 * @param array<string,string> $members
 * @param list<string> $memberNamesKept
 * @return array<string,mixed>
 */
function fractal_zip_folder_hybrid_filtered_zip_restore(
	string $diskPath,
	string $diskBytes,
	array $zipMembersFull,
	array $zipMembersKept,
	array &$members,
	array &$memberNamesKept
): array {
	$droppedBytes = fractal_zip_folder_zip_dropped_raw_bytes($zipMembersFull, $zipMembersKept);
	$hp = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_hybrid_peel.php';
	if (!function_exists('fractal_zip_hybrid_dropped_is_light_noise') && is_readable($hp)) {
		require_once $hp;
	}

	$tryZipMulti = static function (array $rebuild, array $logicalNames, int $dropBudget) use ($diskBytes): ?array {
		return fractal_zip_folder_zip_multi_spec_from_members($rebuild, $logicalNames, $diskBytes, $dropBudget);
	};

	// 1) Light junk only: re-include drops for ZIP_MULTI (exact or small patch).
	$light = function_exists('fractal_zip_hybrid_dropped_is_light_noise')
		&& fractal_zip_hybrid_dropped_is_light_noise($zipMembersFull, $zipMembersKept);
	if ($light) {
		list($fullRebuild, $fullNames) = fractal_zip_folder_zip_install_members($diskPath, $zipMembersFull, $members);
		$spec = $tryZipMulti($fullRebuild, $fullNames, $droppedBytes);
		if ($spec !== null) {
			$memberNamesKept = $fullNames;
			return $spec;
		}
	}

	// 2) Kept members + patch only when patch ≈ dropped noise (not a near-full rewrite).
	$keptRebuild = array();
	foreach ($zipMembersKept as $m) {
		$name = (string) ($m['name'] ?? '');
		$data = (string) ($m['data'] ?? '');
		if ($name === '' || $data === '') {
			continue;
		}
		$keptRebuild[] = array('name' => $name, 'data' => $data);
	}
	$spec = $tryZipMulti($keptRebuild, $memberNamesKept, $droppedBytes);
	if ($spec !== null) {
		return $spec;
	}

	// 3) Abandon filter for ratio: full member set if rebuild is exact / small-patch.
	list($fullRebuild, $fullNames) = fractal_zip_folder_zip_install_members($diskPath, $zipMembersFull, $members);
	$spec = $tryZipMulti($fullRebuild, $fullNames, 0);
	if ($spec !== null) {
		$memberNamesKept = $fullNames;
		return $spec;
	}

	// 4) No cheap ZIP_MULTI — keep the original container as one PASSTHROUGH member
	// so the per-file codec can compress it (VERBATIM FZHR would store it raw).
	$memberNamesKept = array();
	return fractal_zip_folder_zip_collapse_to_passthrough($diskPath, $diskBytes, $members);
}

/**
 * Drop tournament-only members covered by a VERBATIM restore (avoids double-paying the container).
 *
 * @param array<string,string> $wires
 * @param array<string,array> $restoreSpecs
 * @return array<string,string>
 */
function fractal_zip_folder_wires_without_verbatim_orphans(array $wires, array $restoreSpecs): array {
	$exact = array();
	$prefixes = array();
	foreach ($restoreSpecs as $diskPath => $spec) {
		if (!is_array($spec)) {
			continue;
		}
		if ((int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH) !== FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			continue;
		}
		foreach ($spec['member_names'] ?? array() as $n) {
			$exact[(string) $n] = true;
		}
		$prefixes[rtrim((string) $diskPath, '/') . '/'] = true;
	}
	if ($exact === array() && $prefixes === array()) {
		return $wires;
	}
	$out = array();
	foreach ($wires as $path => $wire) {
		$path = (string) $path;
		if (isset($exact[$path])) {
			continue;
		}
		$drop = false;
		foreach ($prefixes as $p => $_) {
			if ($p !== '/' && str_starts_with($path, $p)) {
				$drop = true;
				break;
			}
		}
		if (!$drop) {
			$out[$path] = $wire;
		}
	}
	return $out;
}

/**
 * Restore specs for tournament wire-size compare: zip_multi metadata only (no verbatim blob / patch).
 *
 * @param array<string,array> $restoreSpecs
 * @return array<string,array>
 */
function fractal_zip_folder_restore_specs_for_tournament(array $restoreSpecs): array {
	$out = array();
	foreach ($restoreSpecs as $diskPath => $spec) {
		if (!is_array($spec)) {
			continue;
		}
		$kind = (int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			$names = $spec['member_names'] ?? array();
			if (is_array($names) && $names !== array()) {
				$out[(string) $diskPath] = array(
					'kind' => FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI,
					'member_names' => $names,
				);
			}
			continue;
		}
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$out[(string) $diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => (string) ($spec['format'] ?? ''),
				'meta' => (string) ($spec['meta'] ?? ''),
				'member_names' => $spec['member_names'] ?? array(),
			);
			continue;
		}
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI) {
			$out[(string) $diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI,
				'member_names' => $spec['member_names'] ?? array(),
			);
		}
	}
	return $out;
}

/**
 * @param array<string,array> $restoreSpecs disk_path => spec
 */
function fractal_zip_encode_fzhr_v1(array $restoreSpecs): string {
	if (function_exists('fractal_zip_ensure_folder_per_member_best_loaded')) {
		fractal_zip_ensure_folder_per_member_best_loaded();
	} elseif (!function_exists('fractal_zip_varint_u32')) {
		$pm = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_per_member_best.php';
		if (is_readable($pm)) {
			require_once $pm;
		}
	}
	if (!function_exists('fractal_zip_varint_u32')) {
		return '';
	}
	$needsWrite = false;
	foreach ($restoreSpecs as $spec) {
		if (!is_array($spec)) {
			continue;
		}
		$kind = (int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		if ($kind !== FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH) {
			$needsWrite = true;
			break;
		}
	}
	if (!$needsWrite) {
		return '';
	}
	$entries = array();
	foreach ($restoreSpecs as $diskPath => $spec) {
		if (!is_array($spec)) {
			continue;
		}
		$kind = (int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH) {
			continue;
		}
		$entries[(string) $diskPath] = $spec;
	}
	if ($entries === array()) {
		return '';
	}
	// Folder-level default ZIP mtime: when ≥2 ZIP_MULTI profiles share one shared-mtime,
	// store it once in FZHR v2 and reference it from each profile (flag bit4).
	$mtimeCounts = array();
	foreach ($entries as $spec) {
		if ((int) ($spec['kind'] ?? -1) !== FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI) {
			continue;
		}
		$nMem = count($spec['member_names'] ?? array());
		$mt = fractal_zip_folder_zip_profile_shared_mtime((string) ($spec['semantic_patch'] ?? ''), $nMem);
		if ($mt !== null) {
			$key = (string) $mt;
			$mtimeCounts[$key] = ($mtimeCounts[$key] ?? 0) + 1;
		}
	}
	$defaultMtime = null;
	$bestCount = 0;
	foreach ($mtimeCounts as $key => $c) {
		if ($c > $bestCount) {
			$bestCount = $c;
			$defaultMtime = (int) $key;
		}
	}
	$useV2 = ($defaultMtime !== null && $bestCount >= 2);
	if ($useV2) {
		foreach ($entries as $diskPath => $spec) {
			if ((int) ($spec['kind'] ?? -1) !== FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI) {
				continue;
			}
			$patch = (string) ($spec['semantic_patch'] ?? '');
			$nMem = count($spec['member_names'] ?? array());
			$stripped = fractal_zip_folder_zip_profile_strip_to_default_mtime($patch, $defaultMtime, $nMem);
			if ($stripped !== null) {
				$patch = $stripped;
			}
			// All-deflate level-9 + folder mtime only → empty patch (defaults on decode).
			if (fractal_zip_folder_zip_profile_is_default_mtime_only($patch)) {
				$patch = '';
			} else {
				// FP\x04: store-bitmap + folder mtime (level 9) — shorter than FP\x03+flags.
				$patch = fractal_zip_folder_zip_profile_compact_store_bitmap($patch, $nMem);
			}
			$entries[$diskPath]['semantic_patch'] = $patch;
		}
	}
	ksort($entries, SORT_STRING);
	if ($useV2) {
		$parts = array('FZHR' . chr(2), chr(1), pack('N', $defaultMtime));
	} else {
		$parts = array('FZHR' . chr(1));
	}
	$parts[] = fractal_zip_varint_u32(count($entries));
	foreach ($entries as $diskPath => $spec) {
		$diskPath = (string) $diskPath;
		$pl = strlen($diskPath);
		if ($pl < 1 || $pl > 65535) {
			fractal_zip::fatal_error('FZHR encode: invalid disk path length.');
		}
		$parts[] = fractal_zip_varint_u32($pl) . $diskPath;
		$kind = (int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		$parts[] = chr($kind & 0xFF);
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI) {
			$names = $spec['member_names'] ?? array();
			if (!is_array($names) || $names === array()) {
				fractal_zip::fatal_error('FZHR encode: zip_multi missing member_names.');
			}
			$parts[] = fractal_zip_varint_u32(count($names));
			foreach ($names as $name) {
				$name = (string) $name;
				$nl = strlen($name);
				if ($nl < 1 || $nl > 65535) {
					fractal_zip::fatal_error('FZHR encode: invalid member name length.');
				}
				$parts[] = fractal_zip_varint_u32($nl) . $name;
			}
			$patch = (string) ($spec['semantic_patch'] ?? '');
			$parts[] = fractal_zip_varint_u32(strlen($patch)) . $patch;
		} elseif ($kind === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$fmt = (string) ($spec['format'] ?? '');
			$meta = (string) ($spec['meta'] ?? '');
			$names = $spec['member_names'] ?? array();
			if ($fmt === '' || !is_array($names) || $names === array()) {
				fractal_zip::fatal_error('FZHR encode: classic missing format/members.');
			}
			$parts[] = fractal_zip_varint_u32(strlen($fmt)) . $fmt;
			$parts[] = fractal_zip_varint_u32(strlen($meta)) . $meta;
			$parts[] = fractal_zip_varint_u32(count($names));
			foreach ($names as $name) {
				$name = (string) $name;
				$nl = strlen($name);
				if ($nl < 1 || $nl > 65535) {
					fractal_zip::fatal_error('FZHR encode: invalid classic member name.');
				}
				$parts[] = fractal_zip_varint_u32($nl) . $name;
			}
		} elseif ($kind === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			$verb = (string) ($spec['verbatim'] ?? '');
			$vl = strlen($verb);
			if ($vl < 1) {
				fractal_zip::fatal_error('FZHR encode: empty verbatim blob.');
			}
			$parts[] = fractal_zip_varint_u32($vl) . $verb;
			$names = $spec['member_names'] ?? array();
			if (is_array($names) && $names !== array()) {
				$parts[] = fractal_zip_varint_u32(count($names));
				foreach ($names as $name) {
					$name = (string) $name;
					$parts[] = fractal_zip_varint_u32(strlen($name)) . $name;
				}
			} else {
				$parts[] = fractal_zip_varint_u32(0);
			}
		} else {
			fractal_zip::fatal_error('FZHR encode: unknown restore kind.');
		}
	}
	return implode('', $parts);
}

/**
 * Read shared mtime from an FP\x03 profile (flag bit2), or null.
 */
function fractal_zip_folder_zip_profile_shared_mtime(string $patch, int $nMem = 0): ?int {
	if (strlen($patch) < 5 || substr($patch, 0, 2) !== 'FP' || ord($patch[2]) !== 3) {
		return null;
	}
	$flags = ord($patch[4]);
	if (($flags & 4) === 0) {
		return null;
	}
	$off = 5;
	$n = strlen($patch);
	if (($flags & 2) !== 0) {
		if ($nMem < 1 || $off + $nMem > $n) {
			return null;
		}
		$off += $nMem;
	} elseif (($flags & 8) !== 0) {
		$nb = (int) ceil(max(1, $nMem) / 8);
		if ($nb < 1) {
			$nb = 1;
		}
		if ($off + $nb > $n) {
			return null;
		}
		$off += $nb;
	}
	if ($off + 4 > $n) {
		return null;
	}
	return unpack('N', substr($patch, $off, 4))[1];
}

/**
 * Rewrite FP\x03 shared-mtime (bit2) into folder-default mtime ref (bit4) when it matches.
 */
function fractal_zip_folder_zip_profile_strip_to_default_mtime(string $patch, int $defaultMtime, int $nMem): ?string {
	if (strlen($patch) < 5 || substr($patch, 0, 2) !== 'FP' || ord($patch[2]) !== 3) {
		return null;
	}
	$flags = ord($patch[4]);
	if (($flags & 4) === 0 || ($flags & 16) !== 0) {
		return null;
	}
	$off = 5;
	$n = strlen($patch);
	$meth = '';
	if (($flags & 2) !== 0) {
		if ($nMem < 1 || $off + $nMem > $n) {
			return null;
		}
		$meth = substr($patch, $off, $nMem);
		$off += $nMem;
	} elseif (($flags & 8) !== 0) {
		$nb = (int) ceil(max(1, $nMem) / 8);
		if ($nb < 1) {
			$nb = 1;
		}
		if ($off + $nb > $n) {
			return null;
		}
		$meth = substr($patch, $off, $nb);
		$off += $nb;
	}
	if ($off + 4 > $n) {
		return null;
	}
	$mt = unpack('N', substr($patch, $off, 4))[1];
	if ($mt !== $defaultMtime) {
		return null;
	}
	$inner = substr($patch, $off + 4);
	$newFlags = ($flags & ~4) | 16;
	return 'FP' . chr(3) . $patch[3] . chr($newFlags & 0xFF) . $meth . $inner;
}

/**
 * Expand folder-default mtime refs (flag bit4) back to shared mtime (bit2) using FZHR default.
 * Also expands compact FP\x04 store-bitmap patches.
 */
function fractal_zip_folder_zip_profile_expand_default_mtime(string $patch, int $defaultMtime, int $nMem = 0): string {
	if (strlen($patch) >= 3 && substr($patch, 0, 2) === 'FP' && ord($patch[2]) === 4) {
		// FP\x04 + storeBitmap → FP\x03 level9 flags(bit3|bit4) + bitmap (mtime filled below).
		$bm = substr($patch, 3);
		$patch = 'FP' . chr(3) . chr(9) . chr(8 | 16) . $bm;
	}
	if (strlen($patch) < 5 || substr($patch, 0, 2) !== 'FP' || ord($patch[2]) !== 3) {
		return $patch;
	}
	$flags = ord($patch[4]);
	if (($flags & 16) === 0) {
		return $patch;
	}
	$off = 5;
	$n = strlen($patch);
	$meth = '';
	if (($flags & 2) !== 0) {
		if ($nMem < 1 || $off + $nMem > $n) {
			return $patch;
		}
		$meth = substr($patch, $off, $nMem);
		$off += $nMem;
	} elseif (($flags & 8) !== 0) {
		$nb = (int) ceil(max(1, $nMem) / 8);
		if ($nb < 1) {
			$nb = 1;
		}
		if ($off + $nb > $n) {
			return $patch;
		}
		$meth = substr($patch, $off, $nb);
		$off += $nb;
	}
	$inner = substr($patch, $off);
	$newFlags = ($flags & ~16) | 4;
	return 'FP' . chr(3) . $patch[3] . chr($newFlags & 0xFF) . $meth . pack('N', $defaultMtime) . $inner;
}

/**
 * Compact FP\x03 level-9 + folder-mtime + STORE bitmap → FP\x04 + bitmap.
 */
function fractal_zip_folder_zip_profile_compact_store_bitmap(string $patch, int $nMem): string {
	if (strlen($patch) < 5 || substr($patch, 0, 2) !== 'FP' || ord($patch[2]) !== 3) {
		return $patch;
	}
	if (ord($patch[3]) !== 9) {
		return $patch;
	}
	$flags = ord($patch[4]);
	// Only folder-default mtime (bit4) + STORE bitmap (bit3).
	if (($flags & 16) === 0 || ($flags & 8) === 0 || ($flags & ~24) !== 0) {
		return $patch;
	}
	$nb = (int) ceil(max(1, $nMem) / 8);
	if ($nb < 1) {
		$nb = 1;
	}
	if (strlen($patch) !== 5 + $nb) {
		return $patch;
	}
	return 'FP' . chr(4) . substr($patch, 5, $nb);
}

/** True when patch is only FP\x03 + level 9 + folder-default mtime flag (no methods/inner). */
function fractal_zip_folder_zip_profile_is_default_mtime_only(string $patch): bool {
	return strlen($patch) === 5
		&& substr($patch, 0, 2) === 'FP'
		&& ord($patch[2]) === 3
		&& ord($patch[3]) === 9
		&& ord($patch[4]) === 16;
}

/** Synthesize FP\x03 shared-mtime profile (level 9, all-deflate) for empty ZIP_MULTI patches. */
function fractal_zip_folder_zip_profile_from_folder_mtime(int $mtime): string {
	return 'FP' . chr(3) . chr(9) . chr(4) . pack('N', $mtime);
}

/**
 * @return array<string,array>|null
 */
function fractal_zip_decode_fzhr_v1(string $blob, int $off): ?array {
	if (function_exists('fractal_zip_ensure_folder_per_member_best_loaded')) {
		fractal_zip_ensure_folder_per_member_best_loaded();
	} elseif (!function_exists('fractal_zip_varint_u32_read')) {
		$pm = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_per_member_best.php';
		if (is_readable($pm)) {
			require_once $pm;
		}
	}
	if (!function_exists('fractal_zip_varint_u32_read')) {
		return null;
	}
	$n = strlen($blob);
	if ($off + 5 > $n || substr($blob, $off, 4) !== 'FZHR') {
		return null;
	}
	$ver = ord($blob[$off + 4]);
	if ($ver !== 1 && $ver !== 2) {
		return null;
	}
	$off += 5;
	$defaultMtime = null;
	if ($ver === 2) {
		if ($off + 5 > $n) {
			return null;
		}
		$df = ord($blob[$off]);
		$off += 1;
		if (($df & 1) !== 0) {
			if ($off + 4 > $n) {
				return null;
			}
			$defaultMtime = unpack('N', substr($blob, $off, 4))[1];
			$off += 4;
		}
	}
	$nEntries = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR entry count');
	$out = array();
	for ($i = 0; $i < $nEntries; $i++) {
		$pl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR disk path length');
		if ($pl < 1 || $pl > 65535 || $off + $pl > $n) {
			fractal_zip::fatal_error('Corrupt FZHR (disk path).');
		}
		$diskPath = substr($blob, $off, $pl);
		$off += $pl;
		if ($off >= $n) {
			fractal_zip::fatal_error('Corrupt FZHR (kind).');
		}
		$kind = ord($blob[$off]);
		$off += 1;
		$spec = array('kind' => $kind);
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI) {
			$nNames = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR member count');
			$names = array();
			for ($j = 0; $j < $nNames; $j++) {
				$nl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR member name length');
				if ($nl < 1 || $off + $nl > $n) {
					fractal_zip::fatal_error('Corrupt FZHR (member name).');
				}
				$names[] = substr($blob, $off, $nl);
				$off += $nl;
			}
			$spec['member_names'] = $names;
			$pl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR semantic patch length');
			if ($pl > 0) {
				if ($off + $pl > $n) {
					fractal_zip::fatal_error('Corrupt FZHR (semantic patch).');
				}
				$patch = substr($blob, $off, $pl);
				$off += $pl;
				if ($defaultMtime !== null) {
					$patch = fractal_zip_folder_zip_profile_expand_default_mtime($patch, $defaultMtime, count($names));
				}
				$spec['semantic_patch'] = $patch;
			} elseif ($defaultMtime !== null) {
				// Empty patch + folder default ⇒ level 9, all-deflate, shared mtime.
				$spec['semantic_patch'] = fractal_zip_folder_zip_profile_from_folder_mtime($defaultMtime);
			}
		} elseif ($kind === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$fl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR classic format length');
			if ($fl < 1 || $off + $fl > $n) {
				fractal_zip::fatal_error('Corrupt FZHR (classic format).');
			}
			$spec['format'] = substr($blob, $off, $fl);
			$off += $fl;
			$ml = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR classic meta length');
			if ($ml > 0) {
				if ($off + $ml > $n) {
					fractal_zip::fatal_error('Corrupt FZHR (classic meta).');
				}
				$spec['meta'] = substr($blob, $off, $ml);
				$off += $ml;
			} else {
				$spec['meta'] = '';
			}
			$nNames = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR classic member count');
			$names = array();
			for ($j = 0; $j < $nNames; $j++) {
				$nl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR classic member name');
				if ($nl < 1 || $off + $nl > $n) {
					fractal_zip::fatal_error('Corrupt FZHR (classic member name).');
				}
				$names[] = substr($blob, $off, $nl);
				$off += $nl;
			}
			$spec['member_names'] = $names;
		} elseif ($kind === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			$vl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR verbatim length');
			if ($vl < 1 || $off + $vl > $n) {
				fractal_zip::fatal_error('Corrupt FZHR (verbatim).');
			}
			$spec['verbatim'] = substr($blob, $off, $vl);
			$off += $vl;
			$nNames = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR verbatim member count');
			$names = array();
			for ($j = 0; $j < $nNames; $j++) {
				$nl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHR verbatim member name length');
				if ($nl < 1 || $off + $nl > $n) {
					fractal_zip::fatal_error('Corrupt FZHR (verbatim member name).');
				}
				$names[] = substr($blob, $off, $nl);
				$off += $nl;
			}
			$spec['member_names'] = $names;
		} else {
			fractal_zip::fatal_error('Corrupt FZHR (unknown kind).');
		}
		$out[(string) $diskPath] = $spec;
	}
	if ($off !== $n) {
		fractal_zip::fatal_error('Corrupt FZHR (trailing bytes).');
	}
	return $out;
}

/**
 * @param array<string,string> $sortedPathToWire
 * @param array<string,array> $restoreSpecs
 */
function fractal_zip_encode_fzhm_v1_with_restore(array $sortedPathToWire, array $restoreSpecs): string {
	$body = fractal_zip_encode_fzhm_v1($sortedPathToWire);
	$trailer = fractal_zip_encode_fzhr_v1($restoreSpecs);
	return $body . $trailer;
}

/**
 * @return array{members: array<string,string>, restore: array<string,array>}|null
 */
function fractal_zip_decode_fzhm_container(string $blob): ?array {
	if (function_exists('fractal_zip_ensure_folder_per_member_best_loaded')) {
		fractal_zip_ensure_folder_per_member_best_loaded();
	}
	$blob = (string) $blob;
	if (strlen($blob) < 6 || substr($blob, 0, 4) !== 'FZHM' || $blob[4] !== "\x01") {
		return null;
	}
	$off = 5;
	$n = strlen($blob);
	$nFiles = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHM file count');
	if ($nFiles < 1 || $nFiles > 65536) {
		fractal_zip::fatal_error('Corrupt FZHM (file count).');
	}
	$members = array();
	for ($i = 0; $i < $nFiles; $i++) {
		$pl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHM path length');
		if ($pl < 1 || $pl > 65535 || $off + $pl > $n) {
			fractal_zip::fatal_error('Corrupt FZHM (path).');
		}
		$path = substr($blob, $off, $pl);
		$off += $pl;
		$wl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZHM wire length');
		if ($wl < 1 || $off + $wl > $n) {
			fractal_zip::fatal_error('Corrupt FZHM (wire).');
		}
		$wire = substr($blob, $off, $wl);
		$off += $wl;
		$members[(string) $path] = $wire;
	}
	$restore = array();
	if ($off < $n) {
		if (substr($blob, $off, 4) === 'FZHR' && ($blob[$off + 4] ?? '') === "\x01") {
			$parsed = fractal_zip_decode_fzhr_v1($blob, $off);
			$restore = is_array($parsed) ? $parsed : array();
		} elseif ($off !== $n) {
			fractal_zip::fatal_error('Corrupt FZHM (trailing bytes).');
		}
	}
	ksort($members, SORT_STRING);
	return array('members' => $members, 'restore' => $restore);
}

/**
 * Apply FZHR restore: write disk_layout files into $destRootDir from extracted logical members in $memberFiles.
 *
 * @param array<string,string> $memberFiles logical path => absolute file path
 * @param array<string,array> $restoreSpecs
 */
function fractal_zip_apply_folder_disk_restore(string $destRootDir, array $memberFiles, array $restoreSpecs, fractal_zip $fz): bool {
	if (function_exists('fractal_zip_ensure_literal_pac_stack_loaded')) {
		fractal_zip_ensure_literal_pac_stack_loaded();
	}
	$destRootDir = rtrim((string) $destRootDir, "\0");
	foreach ($restoreSpecs as $diskPath => $spec) {
		if (!is_array($spec)) {
			continue;
		}
		$diskPath = str_replace('\\', '/', (string) $diskPath);
		// Allow nested members (test_files61 raster tree); still reject escapes.
		if ($diskPath === '' || str_starts_with($diskPath, '/')
			|| str_contains($diskPath, "\0")
			|| preg_match('#(^|/)\.\.(/|$)#', $diskPath)) {
			return false;
		}
		$kind = (int) ($spec['kind'] ?? FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH);
		$destPath = $destRootDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $diskPath);
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_PASSTHROUGH) {
			if (!isset($memberFiles[$diskPath])) {
				return false;
			}
			$fz->build_directory_structure_for($destPath);
			if (!@rename($memberFiles[$diskPath], $destPath)) {
				$data = @file_get_contents($memberFiles[$diskPath]);
				if ($data === false || @file_put_contents($destPath, $data) === false) {
					return false;
				}
			}
			continue;
		}
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM) {
			$verb = (string) ($spec['verbatim'] ?? '');
			if ($verb === '') {
				return false;
			}
			$fz->build_directory_structure_for($destPath);
			if (@file_put_contents($destPath, $verb) === false) {
				return false;
			}
			continue;
		}
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_ZIP_MULTI) {
			$names = $spec['member_names'] ?? array();
			if (!is_array($names) || $names === array()) {
				return false;
			}
			$zipMembers = array();
			foreach ($names as $name) {
				$resolved = fractal_zip_folder_resolve_restore_member($memberFiles, $diskPath, (string) $name);
				if ($resolved === null) {
					return false;
				}
				$data = @file_get_contents($resolved[0]);
				if (!empty($resolved[2])) {
					@unlink($resolved[0]);
				}
				if ($data === false) {
					return false;
				}
				$zipMembers[] = array('name' => $resolved[1], 'data' => $data);
			}
			$patch = (string) ($spec['semantic_patch'] ?? '');
			$level = 9;
			if (function_exists('fractal_zip_folder_zip_parse_rebuild_profile')) {
				list($zipMembers, $patch, $level) = fractal_zip_folder_zip_parse_rebuild_profile($zipMembers, $patch);
			}
			$rebuilt = fractal_zip_literal_pac_rebuild_zip_multi_semantic($zipMembers, $level);
			if ($rebuilt === null || $rebuilt === '') {
				return false;
			}
			if ($patch !== '') {
				$rebuilt = fractal_zip_apply_minimal_patch_bytes($rebuilt, $patch);
			}
			// Peel members may have been staged under diskPath/ — clear before writing the archive file.
			if (is_dir($destPath)) {
				$fz->recursive_remove_directory($destPath);
			} elseif (is_file($destPath)) {
				@unlink($destPath);
			}
			$fz->build_directory_structure_for($destPath);
			if (@file_put_contents($destPath, $rebuilt) === false) {
				return false;
			}
			continue;
		}
		if ($kind === FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC) {
			$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
			if (is_readable($classic)) {
				require_once $classic;
			}
			$jpegShare = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_jpeg_marker_share.php';
			if (is_readable($jpegShare)) {
				require_once $jpegShare;
			}
			if (!function_exists('fractal_zip_classic_rebuild_archive')) {
				return false;
			}
			$names = $spec['member_names'] ?? array();
			if (!is_array($names) || $names === array()) {
				return false;
			}
			$payloads = array();
			foreach ($names as $name) {
				$resolved = fractal_zip_folder_resolve_restore_member($memberFiles, $diskPath, (string) $name);
				if ($resolved === null) {
					return false;
				}
				$data = @file_get_contents($resolved[0]);
				if (!empty($resolved[2])) {
					@unlink($resolved[0]);
				}
				if ($data === false) {
					return false;
				}
				if (function_exists('fractal_zip_folder_jpeg_member_payload_for_classic')) {
					$data = fractal_zip_folder_jpeg_member_payload_for_classic($data, $memberFiles);
				}
				$payloads[] = array('name' => $resolved[1], 'data' => $data);
			}
			$rebuilt = fractal_zip_classic_rebuild_archive(
				(string) ($spec['format'] ?? ''),
				(string) ($spec['meta'] ?? ''),
				$payloads
			);
			if ($rebuilt === null || $rebuilt === '') {
				return false;
			}
			// Peel members staged under diskPath/ (e.g. webp → dir of VP8 chunks);
			// clear before writing the rebuilt archive file (same as ZIP_MULTI).
			if (is_dir($destPath)) {
				$fz->recursive_remove_directory($destPath);
			} elseif (is_file($destPath)) {
				@unlink($destPath);
			}
			$fz->build_directory_structure_for($destPath);
			if (@file_put_contents($destPath, $rebuilt) === false) {
				return false;
			}
			continue;
		}
		return false;
	}
	return true;
}

function fractal_zip_folder_container_semantic_verify_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_FOLDER_CONTAINER_SEMANTIC_VERIFY');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Semantic container verify: inner ZIP payloads match (extract both with ZipArchive, compare member bytes).
 */
function fractal_zip_folder_container_semantic_files_equal(string $pathA, string $pathB): bool {
	if (!class_exists(ZipArchive::class)) {
		return false;
	}
	$a = @file_get_contents($pathA);
	$b = @file_get_contents($pathB);
	if ($a === false || $b === false) {
		return false;
	}
	if ($a === $b) {
		return true;
	}
	$peelMax = function_exists('fractal_zip_folder_zip_peel_max_raw_bytes')
		? fractal_zip_folder_zip_peel_max_raw_bytes()
		: null;
	$ma = fractal_zip_literal_pac_list_zip_members_for_mode18($a, $peelMax);
	$mb = fractal_zip_literal_pac_list_zip_members_for_mode18($b, $peelMax);
	if ($ma === null || $mb === null || count($ma) !== count($mb)) {
		return false;
	}
	$byNameA = array();
	foreach ($ma as $m) {
		$byNameA[(string) $m['name']] = (string) $m['data'];
	}
	foreach ($mb as $m) {
		$name = (string) $m['name'];
		if (!isset($byNameA[$name]) || $byNameA[$name] !== (string) $m['data']) {
			return false;
		}
	}
	return true;
}
