<?php
declare(strict_types=1);

/**
 * Per-member best wire bundle (FZHM v1): each logical folder member encoded as an isolated single-file
 * .fz, concatenated under a store outer. Auto when PHASE_UNPEEL yields ≥2 logical members (peeled
 * PKZIP multi-member or loose multi-file). FZHR trailer restores original disk layout on extract.
 *
 * Env: FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST — unset = auto via logical member count; 1 = force; 0 = off.
 *      FRACTAL_ZIP_FOLDER_BASELINE_TIE — unset/1 = ratchet folder wire vs native xz/arc/7z/brotli/zpaq when smaller.
 */

/**
 * @param array<string,string> $rawFilesByPath disk map (legacy callers; resolves logical bundle internally)
 */
function fractal_zip_folder_per_member_best_enabled(array $rawFilesByPath): bool {
	fractal_zip_ensure_folder_logical_bundle_loaded();
	$logical = fractal_zip_resolve_folder_logical_bundle($rawFilesByPath);
	return fractal_zip_heterogeneous_folder_encode_enabled($logical);
}

function fractal_zip_folder_per_member_best_auto_min_files(): int {
	$e = getenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST_MIN_FILES');
	if ($e === false || trim((string) $e) === '') {
		return 2;
	}
	return max(2, min(65535, (int) trim((string) $e)));
}

function fractal_zip_folder_per_member_best_auto_max_files(): int {
	$e = getenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST_MAX_FILES');
	if ($e === false || trim((string) $e) === '') {
		return 16;
	}
	$v = (int) trim((string) $e);
	if ($v <= 0) {
		return 0;
	}
	return max(2, min(65535, $v));
}

function fractal_zip_folder_baseline_tie_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_FOLDER_BASELINE_TIE');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/** True when FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST is explicitly forced on (not auto/off). */
function fractal_zip_folder_per_member_best_forced(): bool {
	$e = getenv('FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
}

/**
 * Prefer FZHM heterogeneous bundles over unified-stream / baseline ratchet (enables manual FZHM splice reuse).
 * Env: FRACTAL_ZIP_FOLDER_PREFER_FZHM — unset = off; 1 = on.
 */
function fractal_zip_folder_prefer_fzhm_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_FOLDER_PREFER_FZHM');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
}

/**
 * Classify .fz wire bytes for reuse / UI (fzhm, unified_stream, native_outer, unknown).
 */
function fractal_zip_fzc_wire_container_kind(string $wire): string {
	if ($wire === '') {
		return 'unknown';
	}
	$head = strlen($wire) > 64 ? substr($wire, 0, 64) : $wire;
	if (strlen($head) >= 5 && substr($head, 0, 4) === 'FZHM' && $head[4] === "\x01") {
		return 'fzhm';
	}
	if (class_exists('fractal_zip', false)) {
		$native = fractal_zip::native_folder_wire_outer_kind_from_head($head);
		if (is_string($native) && $native !== '') {
			return 'native_outer';
		}
	}
	if (strlen($head) >= 4 && substr($head, 0, 4) === 'FZb1') {
		return 'unified_stream';
	}
	if (function_exists('fractal_zip_decode_fzhm_container') && fractal_zip_decode_fzhm_container($wire) !== null) {
		return 'fzhm';
	}
	return 'unknown';
}

/** User-facing label for {@see fractal_zip_fzc_wire_container_kind}. */
function fractal_zip_fzc_wire_container_kind_label(string $kind): string {
	switch ($kind) {
		case 'fzhm':
			return 'FZHM (per-member)';
		case 'unified_stream':
			return 'unified stream';
		case 'native_outer':
			return 'native folder archive';
		default:
			return 'legacy container';
	}
}

/** When baseline tie is on, native Arc compare ignores the default 100 MiB cap unless this is 0. */
function fractal_zip_folder_baseline_tie_uncap_native_arc(): bool {
	$e = getenv('FRACTAL_ZIP_FOLDER_BASELINE_TIE_UNCAP_NATIVE');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Merged raw bytes below this threshold use uncapped native folder compares when baseline tie is on (default 512 MiB).
 */
function fractal_zip_folder_baseline_tie_native_max_raw_bytes(): int {
	$e = getenv('FRACTAL_ZIP_FOLDER_BASELINE_TIE_NATIVE_MAX_RAW_BYTES');
	if ($e === false || trim((string) $e) === '') {
		return 512 * 1024 * 1024;
	}
	$v = (int) trim((string) $e);
	return max(0, min(2147483647, $v));
}

/**
 * Max summed uncompressed ZIP member bytes for folder PHASE_UNPEEL (default: max of literal cap and baseline-tie native cap).
 */
function fractal_zip_folder_zip_peel_max_raw_bytes(): int {
	$e = getenv('FRACTAL_ZIP_FOLDER_ZIP_PEEL_MAX_RAW_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) trim((string) $e));
	}
	[, $litMax] = fractal_zip_literal_pac_zip_multimember_limits();
	$tieMax = fractal_zip_folder_baseline_tie_native_max_raw_bytes();
	return max($litMax, $tieMax);
}

/** Effective per-codec native cap: baseline tie uncaps when sum raw is within {@see fractal_zip_folder_baseline_tie_native_max_raw_bytes}. */
function fractal_zip_folder_native_compare_cap_effective(int $codecCap, int $sumRawBytes): int {
	if ($codecCap === 0) {
		return 0;
	}
	if (!fractal_zip_folder_baseline_tie_enabled()) {
		return $codecCap;
	}
	$tieMax = fractal_zip_folder_baseline_tie_native_max_raw_bytes();
	if ($tieMax > 0 && $sumRawBytes <= $tieMax) {
		return 0;
	}
	return $codecCap;
}

/**
 * @param array<string,string> $sortedPathToWire path => full single-file .fz wire bytes
 */
function fractal_zip_encode_fzhm_v1(array $sortedPathToWire): string {
	$parts = array('FZHM' . chr(1));
	$parts[] = fractal_zip_varint_u32(count($sortedPathToWire));
	foreach ($sortedPathToWire as $path => $wire) {
		$path = (string) $path;
		$wire = (string) $wire;
		$pl = strlen($path);
		if ($pl < 1 || $pl > 65535) {
			fractal_zip::fatal_error('FZHM encode: invalid member path length.');
		}
		$wl = strlen($wire);
		if ($wl < 1) {
			fractal_zip::fatal_error('FZHM encode: empty member wire.');
		}
		$parts[] = fractal_zip_varint_u32($pl) . $path;
		$parts[] = fractal_zip_varint_u32($wl) . $wire;
	}
	return implode('', $parts);
}

/**
 * @return array<string,string>|null sorted path => wire
 */
function fractal_zip_decode_fzhm_v1_members(string $blob): ?array {
	fractal_zip_ensure_folder_logical_bundle_loaded();
	$decoded = fractal_zip_decode_fzhm_container($blob);
	if ($decoded === null) {
		return null;
	}
	return $decoded['members'];
}

function fractal_zip_varint_u32(int $v): string {
	if ($v < 0) {
		fractal_zip::fatal_error('varint encode: negative.');
	}
	$out = '';
	while (true) {
		$b = $v & 0x7F;
		$v >>= 7;
		if ($v !== 0) {
			$b |= 0x80;
		}
		$out .= chr($b);
		if ($v === 0) {
			break;
		}
	}
	return $out;
}

function fractal_zip_varint_u32_read(string $blob, int &$off, int $n, string $ctx): int {
	$out = 0;
	$shift = 0;
	$steps = 0;
	while ($off < $n) {
		$b = ord($blob[$off]);
		$off += 1;
		$out |= ($b & 0x7F) << $shift;
		$steps++;
		if (($b & 0x80) === 0) {
			return $out;
		}
		$shift += 7;
		if ($steps > 5 || $shift > 28) {
			fractal_zip::fatal_error('Corrupt ' . $ctx . ' (varint).');
		}
	}
	fractal_zip::fatal_error('Corrupt ' . $ctx . ' (truncated varint).');
}
