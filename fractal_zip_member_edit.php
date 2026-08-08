<?php
declare(strict_types=1);

/**
 * In-container member replacement for .fz (no full extract/repack tree).
 *
 * Lanes:
 *   fzhm_store     — FZHM v1 blob in /files (agent per-member bundle)
 *   fzb4_plain     — raw FZB4 on disk
 *   fzb4_gzip      — zlib gzip wrapper around FZB4 inner
 *   fzb4_xz        — xz wrapper around FZB4 inner
 *
 * Returns rebuilt container bytes; caller (ffs) content-addresses the new blob.
 */


/**
 * @return array{ok: bool, lane?: string, code?: string, hint?: string}
 */
function fractal_zip_detect_container_edit_lane(string $containerPath): array {
	if(!is_file($containerPath) || !is_readable($containerPath)) {
		return array('ok' => false, 'code' => 'missing_container');
	}
	$head = @file_get_contents($containerPath, false, null, 0, 64);
	if($head === false || $head === '') {
		return array('ok' => false, 'code' => 'empty_container');
	}
	if(strlen($head) >= 5 && substr($head, 0, 4) === 'FZHM' && $head[4] === "\x01") {
		return array('ok' => true, 'lane' => 'fzhm_store');
	}
	if(substr($head, 0, 4) === 'FZB4') {
		return array('ok' => true, 'lane' => 'fzb4_plain');
	}
	if(strlen($head) >= 2 && substr($head, 0, 2) === "\x1F\x8B") {
		return array('ok' => true, 'lane' => 'fzb4_gzip');
	}
	if(strlen($head) >= 6 && substr($head, 0, 6) === "\xFD\x37\x7A\x58\x5A\x00") {
		return array('ok' => true, 'lane' => 'fzb4_xz');
	}
	return array(
		'ok' => false,
		'code' => 'needs_repack',
		'hint' => 'Outer format needs full repack (native 7z/arc/zstd/brotli or legacy inner).',
	);
}

/**
 * @return list<array{path: string, mode: int, store: string}>|null
 */
function fractal_zip_fzb4_collect_stored_records(fractal_zip $fz, string $bundlePath): ?array {
	$fh = @fopen($bundlePath, 'rb');
	if($fh === false) {
		return null;
	}
	if(fread($fh, 4) !== 'FZB4') {
		fclose($fh);
		return null;
	}
	$out = array();
	$prevPath = '';
	while(true) {
		$pos = ftell($fh);
		if($pos === false) {
			fclose($fh);
			return null;
		}
		$probe = fread($fh, 1);
		if($probe === false || $probe === '') {
			break;
		}
		fseek($fh, $pos);
		$prefixLen = $fz->read_varint_u32_from_stream_soft($fh);
		if($prefixLen === null) {
			fclose($fh);
			return null;
		}
		$suffixLen = $fz->read_varint_u32_from_stream_soft($fh);
		if($suffixLen === null) {
			fclose($fh);
			return null;
		}
		if($prefixLen > strlen($prevPath)) {
			fclose($fh);
			return null;
		}
		$pathSuffix = $suffixLen > 0 ? fread($fh, $suffixLen) : '';
		if(strlen($pathSuffix) !== $suffixLen) {
			fclose($fh);
			return null;
		}
		$path = substr($prevPath, 0, $prefixLen) . $pathSuffix;
		$modeCh = fread($fh, 1);
		if($modeCh === false || strlen($modeCh) !== 1) {
			fclose($fh);
			return null;
		}
		$dataLen = $fz->read_varint_u32_from_stream_soft($fh);
		if($dataLen === null) {
			fclose($fh);
			return null;
		}
		$rawStored = $dataLen > 0 ? fread($fh, $dataLen) : '';
		if(strlen($rawStored) !== $dataLen) {
			fclose($fh);
			return null;
		}
		$out[] = array(
			'path' => $path,
			'mode' => ord($modeCh),
			'store' => $rawStored,
		);
		$prevPath = $path;
	}
	fclose($fh);
	return $out;
}

/**
 * @param list<array{path: string, mode: int, store: string}> $records
 * @param array<string, string> $replaceRaw path => new decoded bytes (unescaped raw)
 */
function fractal_zip_fzb4_build_bytes_from_records(fractal_zip $fz, array $records, array $replaceRaw): ?string {
	if($records === array()) {
		return null;
	}
	fractal_zip_ensure_literal_pac_stack_loaded();
	$pathOrder = array();
	$modesStores = array();
	foreach($records as $rec) {
		$path = (string)$rec['path'];
		$pathOrder[] = $path;
		if(isset($replaceRaw[$path])) {
			list($mode, $store) = $fz->choose_best_literal_bundle_transform((string)$replaceRaw[$path], $path);
		} else {
			$mode = (int)$rec['mode'];
			$store = (string)$rec['store'];
		}
		$modesStores[$path] = array('mode' => $mode, 'store' => $store);
	}
	return $fz->literal_bundle_fzb4_payload_bytes_for_path_order($pathOrder, $modesStores);
}

/**
 * @return array{ok: bool, bytes?: string, code?: string, lane?: string}
 */
function fractal_zip_wrap_fzb4_inner_like_original(fractal_zip $fz, string $fzb4Inner, string $lane): array {
	if($lane === 'fzb4_plain' || $lane === 'fzhm_store') {
		return array('ok' => true, 'bytes' => $fzb4Inner, 'lane' => $lane);
	}
	if($lane === 'fzb4_gzip') {
		$lev = fractal_zip::folder_gzip_fast_deflate_level();
		$out = @gzencode($fzb4Inner, $lev);
		if($out === false) {
			return array('ok' => false, 'code' => 'gzip_wrap_failed');
		}
		return array('ok' => true, 'bytes' => $out, 'lane' => $lane);
	}
	if($lane === 'fzb4_xz') {
		$xzExe = fractal_zip::xz_executable();
		if($xzExe === null) {
			return array('ok' => false, 'code' => 'xz_missing');
		}
		$level = fractal_zip::xz_level();
		$out = $fz->outer_xz_blob($xzExe, $fzb4Inner, $level, null, null);
		if(!is_string($out) || $out === '') {
			return array('ok' => false, 'code' => 'xz_wrap_failed');
		}
		return array('ok' => true, 'bytes' => $out, 'lane' => $lane);
	}
	return array('ok' => false, 'code' => 'unsupported_lane');
}

/**
 * Resolve FZHM member key (flat id "1" or logical relpath).
 *
 * @param array<string, string> $members
 */
function fractal_zip_fzhm_resolve_member_key(array $members, string $memberRel): ?string {
	$norm = fractal_zip::normalize_web_fs_member_relpath($memberRel);
	if($norm === null) {
		return null;
	}
	if(isset($members[$norm])) {
		return $norm;
	}
	$base = basename(str_replace('\\', '/', $norm));
	if($base !== '' && isset($members[$base])) {
		return $base;
	}
	foreach(array_keys($members) as $k) {
		if(basename(str_replace('\\', '/', (string)$k)) === $base && $base !== '') {
			return (string)$k;
		}
	}
	return null;
}

/**
 * @return array{ok: bool, bytes?: string, code?: string, lane?: string, member_key?: string, wire_bytes?: int}
 */
function fractal_zip_replace_fzhm_member_bytes(fractal_zip $fz, string $containerBytes, string $memberRel, string $newBytes): array {
	$decoded = fractal_zip_decode_fzhm_container($containerBytes);
	if($decoded === null) {
		return array('ok' => false, 'code' => 'not_fzhm');
	}
	$members = $decoded['members'];
	$key = fractal_zip_fzhm_resolve_member_key($members, $memberRel);
	if($key === null) {
		return array('ok' => false, 'code' => 'member_not_found');
	}
	$wire = $fz->encode_isolated_single_file_fzc_wire($key, $newBytes);
	if($wire === null || $wire === '') {
		return array('ok' => false, 'code' => 'member_encode_failed');
	}
	$members[$key] = $wire;
	ksort($members, SORT_STRING);
	$restore = is_array($decoded['restore'] ?? null) ? $decoded['restore'] : array();
	$out = $restore !== array()
		? fractal_zip_encode_fzhm_v1_with_restore($members, $restore)
		: fractal_zip_encode_fzhm_v1($members);
	return array(
		'ok' => true,
		'bytes' => $out,
		'lane' => 'fzhm_store',
		'member_key' => $key,
		'wire_bytes' => strlen($wire),
	);
}

/**
 * Peel container to a temp FZB4 path when outer is gzip/xz; plain FZB4/FZHM returns path info.
 *
 * @param list<string> $cleanup paths to unlink
 * @return array{ok: bool, fzb4_path?: string, lane?: string, fzhm_bytes?: string, code?: string}
 */
function fractal_zip_peel_container_for_fzb4_edit(fractal_zip $fz, string $containerPath, string $lane, array &$cleanup): array {
	if($lane === 'fzhm_store') {
		$raw = @file_get_contents($containerPath);
		if($raw === false || $raw === '') {
			return array('ok' => false, 'code' => 'read_failed');
		}
		return array('ok' => true, 'lane' => $lane, 'fzhm_bytes' => $raw);
	}
	if($lane === 'fzb4_plain') {
		return array('ok' => true, 'lane' => $lane, 'fzb4_path' => $containerPath);
	}
	if($lane === 'fzb4_gzip') {
		$tmp = '';
		if(!$fz->try_stream_inflate_to_fzb4_temp_file($containerPath, $tmp)) {
			return array('ok' => false, 'code' => 'gzip_peel_failed');
		}
		$cleanup[] = $tmp;
		return array('ok' => true, 'lane' => $lane, 'fzb4_path' => $tmp);
	}
	if($lane === 'fzb4_xz') {
		$tmp = '';
		if(!$fz->try_stream_xz_decompress_to_temp_file($containerPath, $tmp)) {
			return array('ok' => false, 'code' => 'xz_peel_failed');
		}
		$cleanup[] = $tmp;
		return array('ok' => true, 'lane' => $lane, 'fzb4_path' => $tmp);
	}
	return array('ok' => false, 'code' => 'unsupported_lane');
}

/**
 * @return array{
 *   ok: bool,
 *   bytes?: string,
 *   code?: string,
 *   lane?: string,
 *   hint?: string,
 *   container_bytes_before?: int,
 *   container_bytes_after?: int,
 *   member_key?: string
 * }
 */
function fractal_zip_build_container_with_replaced_member(
	fractal_zip $fz,
	string $containerPath,
	string $memberRel,
	string $newBytes
): array {
	fractal_zip_ensure_member_edit_loaded();
	$norm = fractal_zip::normalize_web_fs_member_relpath($memberRel);
	if($norm === null) {
		return array('ok' => false, 'code' => 'bad_member_path');
	}
	$laneInfo = fractal_zip_detect_container_edit_lane($containerPath);
	if(empty($laneInfo['ok'])) {
		return $laneInfo;
	}
	$lane = (string)$laneInfo['lane'];
	$before = (int)@filesize($containerPath);
	$cleanup = array();

	if($lane === 'fzhm_store') {
		$raw = @file_get_contents($containerPath);
		if($raw === false) {
			return array('ok' => false, 'code' => 'read_failed');
		}
		$rep = fractal_zip_replace_fzhm_member_bytes($fz, $raw, $norm, $newBytes);
		if(empty($rep['ok'])) {
			return $rep;
		}
		return array(
			'ok' => true,
			'bytes' => (string)$rep['bytes'],
			'lane' => 'fzhm_store',
			'container_bytes_before' => $before,
			'container_bytes_after' => strlen((string)$rep['bytes']),
			'member_key' => (string)($rep['member_key'] ?? ''),
		);
	}

	$peel = fractal_zip_peel_container_for_fzb4_edit($fz, $containerPath, $lane, $cleanup);
	if(empty($peel['ok']) || empty($peel['fzb4_path'])) {
		foreach($cleanup as $p) {
			@unlink($p);
		}
		return $peel;
	}
	$fzb4Path = (string)$peel['fzb4_path'];
	$records = fractal_zip_fzb4_collect_stored_records($fz, $fzb4Path);
	if($records === null) {
		foreach($cleanup as $p) {
			@unlink($p);
		}
		return array('ok' => false, 'code' => 'fzb4_parse_failed');
	}
	$found = false;
	foreach($records as $rec) {
		if((string)$rec['path'] === $norm) {
			$found = true;
			break;
		}
	}
	if(!$found) {
		foreach($cleanup as $p) {
			@unlink($p);
		}
		return array('ok' => false, 'code' => 'member_not_found');
	}
	$fzb4Inner = fractal_zip_fzb4_build_bytes_from_records($fz, $records, array($norm => $newBytes));
	if($fzb4Inner === null) {
		foreach($cleanup as $p) {
			@unlink($p);
		}
		return array('ok' => false, 'code' => 'fzb4_build_failed');
	}
	$wrap = fractal_zip_wrap_fzb4_inner_like_original($fz, $fzb4Inner, $lane);
	foreach($cleanup as $p) {
		@unlink($p);
	}
	if(empty($wrap['ok'])) {
		return $wrap;
	}
	$outBytes = (string)$wrap['bytes'];
	return array(
		'ok' => true,
		'bytes' => $outBytes,
		'lane' => (string)($wrap['lane'] ?? $lane),
		'container_bytes_before' => $before,
		'container_bytes_after' => strlen($outBytes),
		'member_key' => $norm,
	);
}

/**
 * Write container atomically (temp + rename).
 */
function fractal_zip_atomic_write_file(string $path, string $bytes): bool {
	$dir = dirname($path);
	if(!is_dir($dir)) {
		@mkdir($dir, 0755, true);
	}
	$tmp = $path . '.fzed.' . bin2hex(random_bytes(4));
	if(@file_put_contents($tmp, $bytes, LOCK_EX) === false) {
		@unlink($tmp);
		return false;
	}
	if(!@rename($tmp, $path)) {
		@unlink($tmp);
		return false;
	}
	return true;
}
