<?php
declare(strict_types=1);

/**
 * Inner-fold static dictionary inside FZTX members (FZDI section, not preprocess JSON).
 *
 * Amortization model: dict ships once per archive header/chunk (mono inner = one FZDI).
 * Slice probe fair cost: fold_bytes * (slice_pages / 12041 full enwik8 pages).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

const FRACTAL_ZIP_ENWIK_INNER_FOLD_FULL_PAGES = 12041;

/** @return list<string> */
function fractal_zip_enwik_inner_fold_preprocess_ids(): array
{
	return array('dict_inner', 'dict_phda9_inner', 'stat_pred_inner', 'consonant_hybrid', 'consonant_hybrid_split', 'consonant_hybrid_lossy', 'wiki_lom');
}

function fractal_zip_enwik_inner_fold_is_preprocess(string $preprocessId): bool
{
	return in_array(strtolower(trim($preprocessId)), fractal_zip_enwik_inner_fold_preprocess_ids(), true);
}

const FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC = "FZIF\x01";
/** Fractal+outer wire wrapper around {@see FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC} payload. */
const FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC_FRACTAL = "FZIF\x02";
/** Tagged outer codec wrapper when auto-detect undo is ambiguous (zpaq, stacked). */
const FRACTAL_ZIP_ENWIK_INNER_FOLD_OUTER_CODEC_MAGIC = "FZOC\x01";

function fractal_zip_enwik_inner_fold_fractal_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Raw FZIF\x01 payload: magic + preprocess id + inner blob (FZPM or FZDI body).
 */
function fractal_zip_enwik_inner_fold_pack_trailer(string $preprocessId, string $innerBlob): string
{
	$pid = (string) $preprocessId;
	return FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC
		. fractal_zip_enwik_encode_varint_u32(strlen($pid)) . $pid
		. fractal_zip_enwik_encode_varint_u32(strlen($innerBlob)) . $innerBlob;
}

/**
 * Adaptive outer on FZS1 raw inner (binary-safe). Optional deep path runs fractal zip when self-check passes.
 */
function fractal_zip_enwik_inner_fold_fractal_wire(string $packed): ?string
{
	if ($packed === '') {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	$member = 'meta/inner_fold.bin';
	$seg = (int) (getenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_SEGMENT') ?: 96);
	$seg = max(8, min(500000, $seg));
	$fz = new fractal_zip($seg, false, false, null, false);
	// Default full outer (zpaq/brotli tournament) on trailer — once per archive; FAST=1 for probe speed.
	$fast = getenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_FAST');
	$stopAfter = ($fast !== false && trim((string) $fast) !== ''
		&& in_array(strtolower(trim((string) $fast)), array('1', 'true', 'on', 'yes'), true))
		? fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE : null;
	$deepOff = getenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL_DEEP');
	$tryDeep = $deepOff === false || trim((string) $deepOff) === ''
		|| !in_array(strtolower(trim((string) $deepOff)), array('0', 'off', 'false', 'no'), true);
	$candidates = array();
	$rawInner = $fz->encode_single_member_raw_payload($member, $packed);
	$rawOuter = $fz->adaptive_compress($rawInner, $stopAfter);
	if (is_string($rawOuter) && $rawOuter !== '') {
		$candidates[] = array('wire' => $rawOuter, 'mode' => 'raw');
	}
	if ($tryDeep) {
		$fzDeep = new fractal_zip($seg, false, false, null, false);
		$fzDeep->zip($packed, $member, false);
		$arr = array();
		foreach ($fzDeep->equivalences as $eq) {
			$arr[$eq[1]] = $eq[2];
		}
		$frInner = $fzDeep->encode_container_payload($arr, $fzDeep->fractal_string);
		if (is_string($frInner) && $frInner !== ''
			&& $fzDeep->fractal_string !== ''
			&& fractal_zip_enwik_inner_fold_fractal_unwire_inner($frInner) === $packed) {
			$frOuter = $fzDeep->adaptive_compress($frInner, $stopAfter);
			if (is_string($frOuter) && $frOuter !== '') {
				$candidates[] = array('wire' => $frOuter, 'mode' => 'deep');
			}
		}
	}
	if ($candidates === array()) {
		return null;
	}
	usort($candidates, static function (array $a, array $b): int {
		return strlen((string) $a['wire']) <=> strlen((string) $b['wire']);
	});
	$GLOBALS['fractal_zip_enwik_inner_fold_fractal_wire_mode'] = (string) ($candidates[0]['mode'] ?? 'raw');
	return (string) $candidates[0]['wire'];
}

function fractal_zip_enwik_inner_fold_fractal_unwire_inner(string $inner): ?string
{
	if ($inner === '') {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	$fz = new fractal_zip(96, false, false, null, false);
	$decoded = $fz->decode_container_payload($inner);
	$map = is_array($decoded[0] ?? null) ? $decoded[0] : array();
	foreach ($map as $stored) {
		return $fz->unescape_literal_from_storage((string) $stored);
	}
	return null;
}

function fractal_zip_enwik_inner_fold_fractal_unwire(string $fractalWire): ?string
{
	if ($fractalWire === '') {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	$fz = new fractal_zip(96, false, false, null, false);
	$inner = $fz->adaptive_decompress($fractalWire);
	return fractal_zip_enwik_inner_fold_fractal_unwire_inner($inner);
}

/**
 * Single-layer undo for gzip/brotli/zstd/FZOC/zpaq outer wrappers.
 */
function fractal_zip_enwik_inner_fold_trailer_outer_undo_one(string $wire): string
{
	$magicLen = strlen(FRACTAL_ZIP_ENWIK_INNER_FOLD_OUTER_CODEC_MAGIC);
	if (strlen($wire) >= $magicLen + 1
		&& substr($wire, 0, $magicLen) === FRACTAL_ZIP_ENWIK_INNER_FOLD_OUTER_CODEC_MAGIC) {
		$off = $magicLen;
		$dv = fractal_zip_enwik_decode_varint_u32($wire, $off);
		if ($dv === null) {
			return $wire;
		}
		$idLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($idLen < 0 || $off + $idLen > strlen($wire)) {
			return $wire;
		}
		$codec = substr($wire, $off, $idLen);
		$off += $idLen;
		$dv = fractal_zip_enwik_decode_varint_u32($wire, $off);
		if ($dv === null) {
			return $wire;
		}
		$payloadLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($payloadLen < 0 || $off + $payloadLen > strlen($wire)) {
			return $wire;
		}
		$payload = substr($wire, $off, $payloadLen);
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
		return fractal_zip_text_outer_layer_decompress($codec, $payload);
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	if (str_starts_with($wire, fractal_zip::OUTER_ZPAQ_MAGIC)) {
		$host = new fractal_zip(256, false, false, null, false);
		$inner = $host->adaptive_decompress($wire);
		if (is_string($inner) && $inner !== '') {
			return $inner;
		}
	}
	$raw = @gzdecode($wire);
	if (is_string($raw) && $raw !== '') {
		return $raw;
	}
	if (function_exists('brotli_uncompress')) {
		$u = brotli_uncompress($wire);
		if (is_string($u) && $u !== '') {
			return $u;
		}
	}
	if (str_starts_with($wire, "\x28\xb5\x2f\xfd")) {
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_if_tr_' . getmypid();
		@mkdir($tmp, 0700, true);
		$in = $tmp . DIRECTORY_SEPARATOR . 'in.zst';
		$out = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
		file_put_contents($in, $wire);
		exec('zstd -d -q -f -o ' . escapeshellarg($out) . ' ' . escapeshellarg($in) . ' 2>/dev/null', $xo, $ret);
		if ($ret === 0 && is_file($out)) {
			$payload = (string) file_get_contents($out);
			fractal_zip_enwik_recursive_remove($tmp);
			if ($payload !== '') {
				return $payload;
			}
		}
		fractal_zip_enwik_recursive_remove($tmp);
	}
	return $wire;
}

/**
 * Undo gzip/brotli/zstd/FZOC-tagged/stacked outer wrappers around inner-fold trailer bytes.
 */
function fractal_zip_enwik_inner_fold_trailer_outer_undo(string $wire): string
{
	for ($i = 0; $i < 8; $i++) {
		$next = fractal_zip_enwik_inner_fold_trailer_outer_undo_one($wire);
		if ($next === $wire) {
			break;
		}
		$wire = $next;
	}
	return $wire;
}

function fractal_zip_enwik_inner_fold_outer_codec_wrap(string $codec, string $wire): string
{
	$codec = (string) $codec;
	return FRACTAL_ZIP_ENWIK_INNER_FOLD_OUTER_CODEC_MAGIC
		. fractal_zip_enwik_encode_varint_u32(strlen($codec)) . $codec
		. fractal_zip_enwik_encode_varint_u32(strlen($wire)) . $wire;
}

/** @return list<string> */
function fractal_zip_enwik_inner_fold_trailer_stack_codec_ids(): array
{
	$e = getenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_STACK_CODECS');
	$raw = ($e === false || trim((string) $e) === '')
		? 'zpaq9:zstd22'
		: (string) $e;
	$out = array();
	foreach (preg_split('/[:;,]/', $raw) ?: array() as $p) {
		$p = strtolower(trim((string) $p));
		if ($p !== '') {
			$out[] = $p;
		}
	}
	return $out;
}

/**
 * @param list<array{wire: string, codec: string}> $candidates
 * @return list<array{wire: string, codec: string}>
 */
function fractal_zip_enwik_inner_fold_trailer_expand_candidates(string $packed, array $candidates): array
{
	$seen = array();
	$out = array();
	$push = static function (array $row) use (&$seen, &$out): void {
		$wire = (string) ($row['wire'] ?? '');
		if ($wire === '') {
			return;
		}
		$key = hash('xxh128', $wire);
		if (isset($seen[$key])) {
			return;
		}
		$seen[$key] = true;
		$out[] = array('wire' => $wire, 'codec' => (string) ($row['codec'] ?? '?'));
	};
	foreach ($candidates as $row) {
		$push($row);
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
	$stackCodecs = fractal_zip_enwik_inner_fold_trailer_stack_codec_ids();
	$base = $candidates;
	foreach ($base as $row) {
		$wire = (string) ($row['wire'] ?? '');
		$baseCodec = (string) ($row['codec'] ?? '?');
		if ($wire === '') {
			continue;
		}
		foreach ($stackCodecs as $stackCodec) {
			try {
				$r = fractal_zip_text_outer_layer_compress($stackCodec, $wire);
				$sw = (string) ($r['payload'] ?? '');
				if (empty($r['roundtrip_ok']) || $sw === '') {
					continue;
				}
				$tagged = $stackCodec === 'zpaq9'
					? fractal_zip_enwik_inner_fold_outer_codec_wrap($stackCodec, $sw)
					: $sw;
				$push(array(
					'wire' => $tagged,
					'codec' => $baseCodec . '+' . $stackCodec,
				));
			} catch (Throwable $e) {
				continue;
			}
		}
		if (fractal_zip_enwik_inner_fold_fractal_enabled()) {
			unset($GLOBALS['fractal_zip_enwik_inner_fold_fractal_wire_mode']);
			$fzWire = fractal_zip_enwik_inner_fold_fractal_wire($wire);
			$fzMode = (string) ($GLOBALS['fractal_zip_enwik_inner_fold_fractal_wire_mode'] ?? 'raw');
			if (is_string($fzWire) && $fzWire !== '') {
				$sealed = FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC_FRACTAL
					. fractal_zip_enwik_encode_varint_u32(strlen($fzWire)) . $fzWire;
				$push(array('wire' => $sealed, 'codec' => $baseCodec . '+fractal_' . $fzMode));
				$gzFz = gzencode($sealed, 9);
				if (is_string($gzFz)) {
					$push(array('wire' => $gzFz, 'codec' => $baseCodec . '+fractal_' . $fzMode . '_gzip'));
				}
			}
		}
	}
	if (fractal_zip_enwik_inner_fold_fractal_enabled()) {
		unset($GLOBALS['fractal_zip_enwik_inner_fold_fractal_wire_mode']);
		$fzWire = fractal_zip_enwik_inner_fold_fractal_wire($packed);
		$fzMode = (string) ($GLOBALS['fractal_zip_enwik_inner_fold_fractal_wire_mode'] ?? 'raw');
		if (is_string($fzWire) && $fzWire !== '') {
			$sealed = FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC_FRACTAL
				. fractal_zip_enwik_encode_varint_u32(strlen($fzWire)) . $fzWire;
			$push(array('wire' => $sealed, 'codec' => 'fractal_' . $fzMode));
			$gzFz = gzencode($sealed, 9);
			if (is_string($gzFz)) {
				$push(array('wire' => $gzFz, 'codec' => 'fractal_' . $fzMode . '_gzip'));
			}
		}
	}
	return $out;
}

/** @return list<string> */
function fractal_zip_enwik_inner_fold_trailer_codec_ids(): array
{
	$e = getenv('FRACTAL_ZIP_INNER_FOLD_TRAILER_CODECS');
	$raw = ($e === false || trim((string) $e) === '')
		? 'zpaq9:zstd22:brotli11:gzip9'
		: (string) $e;
	$out = array();
	foreach (preg_split('/[:;,]/', $raw) ?: array() as $p) {
		$p = strtolower(trim((string) $p));
		if ($p !== '') {
			$out[] = $p;
		}
	}
	return $out;
}

/** @return array{wire: string, codec: string}|null */
function fractal_zip_enwik_inner_fold_trailer_codec_seal(string $packed): ?array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
	$best = null;
	$bestLen = PHP_INT_MAX;
	foreach (fractal_zip_enwik_inner_fold_trailer_codec_ids() as $codec) {
		try {
			$r = fractal_zip_text_outer_layer_compress($codec, $packed);
			$wire = (string) ($r['payload'] ?? '');
			$rt = !empty($r['roundtrip_ok']);
			if (!$rt || $wire === '') {
				continue;
			}
			$len = strlen($wire);
			if ($len < $bestLen) {
				$bestLen = $len;
				$tagged = ($codec === 'zpaq9')
					? fractal_zip_enwik_inner_fold_outer_codec_wrap($codec, $wire)
					: $wire;
				$best = array('wire' => $tagged, 'codec' => $codec);
			}
		} catch (Throwable $e) {
			continue;
		}
	}
	return $best;
}

/**
 * Pick smallest wire seal for inner-fold trailer (gzip v1 and/or fractal FZIF\x02).
 *
 * @return array{wire: string, codec: string, packed_bytes: int, wire_bytes: int}
 */
function fractal_zip_enwik_inner_fold_seal_trailer(string $preprocessId, string $innerBlob): array
{
	$packed = fractal_zip_enwik_inner_fold_pack_trailer($preprocessId, $innerBlob);
	$candidates = array();
	$codecSeal = fractal_zip_enwik_inner_fold_trailer_codec_seal($packed);
	if ($codecSeal !== null) {
		$candidates[] = $codecSeal;
	}
	$gz = gzencode($packed, 9);
	if (is_string($gz)) {
		$candidates[] = array('wire' => $gz, 'codec' => 'gzip_v1');
	}
	if (fractal_zip_enwik_inner_fold_fractal_enabled()) {
		unset($GLOBALS['fractal_zip_enwik_inner_fold_fractal_wire_mode']);
		$fzWire = fractal_zip_enwik_inner_fold_fractal_wire($packed);
		$fzMode = (string) ($GLOBALS['fractal_zip_enwik_inner_fold_fractal_wire_mode'] ?? 'raw');
		if (is_string($fzWire) && $fzWire !== '') {
			$sealed = FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC_FRACTAL
				. fractal_zip_enwik_encode_varint_u32(strlen($fzWire)) . $fzWire;
			$candidates[] = array('wire' => $sealed, 'codec' => 'fractal_' . $fzMode);
			$gzFz = gzencode($sealed, 9);
			if (is_string($gzFz)) {
				$candidates[] = array('wire' => $gzFz, 'codec' => 'fractal_' . $fzMode . '_gzip');
			}
		}
	}
	if ($candidates === array()) {
		return array(
			'wire' => $packed,
			'codec' => 'raw',
			'packed_bytes' => strlen($packed),
			'wire_bytes' => strlen($packed),
		);
	}
	$candidates = fractal_zip_enwik_inner_fold_trailer_expand_candidates($packed, $candidates);
	$verified = array();
	foreach ($candidates as $row) {
		$rt = fractal_zip_enwik_inner_fold_unpack_trailer((string) ($row['wire'] ?? ''));
		if ($rt !== null && (string) ($rt['inner_blob'] ?? '') === $innerBlob) {
			$verified[] = $row;
		}
	}
	if ($verified !== array()) {
		$candidates = $verified;
	}
	usort($candidates, static function (array $a, array $b): int {
		return strlen((string) $a['wire']) <=> strlen((string) $b['wire']);
	});
	$best = $candidates[0];
	return array(
		'wire' => (string) $best['wire'],
		'codec' => (string) $best['codec'],
		'packed_bytes' => strlen($packed),
		'wire_bytes' => strlen((string) $best['wire']),
	);
}

/**
 * @return array{preprocess: string, inner_blob: string}|null
 */
function fractal_zip_enwik_inner_fold_unpack_trailer(string $gzBlob): ?array
{
	$raw = fractal_zip_enwik_inner_fold_trailer_outer_undo($gzBlob);
	$fractalMagicLen = strlen(FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC_FRACTAL);
	if (strlen($raw) >= $fractalMagicLen + 1
		&& substr($raw, 0, $fractalMagicLen) === FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC_FRACTAL) {
		$off = $fractalMagicLen;
		$dv = fractal_zip_enwik_decode_varint_u32($raw, $off);
		if ($dv === null) {
			return null;
		}
		$wireLen = (int) $dv[0];
		$off = (int) $dv[1];
		if ($wireLen < 0 || $off + $wireLen > strlen($raw)) {
			return null;
		}
		$fzWire = substr($raw, $off, $wireLen);
		$unpacked = fractal_zip_enwik_inner_fold_fractal_unwire($fzWire);
		if (!is_string($unpacked) || $unpacked === '') {
			return null;
		}
		$raw = $unpacked;
	}
	$magicLen = strlen(FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC);
	if (strlen($raw) < $magicLen || substr($raw, 0, $magicLen) !== FRACTAL_ZIP_ENWIK_INNER_FOLD_TRAILER_MAGIC) {
		return null;
	}
	$off = $magicLen;
	$dv = fractal_zip_enwik_decode_varint_u32($raw, $off);
	if ($dv === null) {
		return null;
	}
	$idLen = (int) $dv[0];
	$off = (int) $dv[1];
	if ($idLen < 0 || $off + $idLen > strlen($raw)) {
		return null;
	}
	$pid = substr($raw, $off, $idLen);
	$off += $idLen;
	$dv = fractal_zip_enwik_decode_varint_u32($raw, $off);
	if ($dv === null) {
		return null;
	}
	$blobLen = (int) $dv[0];
	$off = (int) $dv[1];
	if ($blobLen < 0 || $off + $blobLen > strlen($raw)) {
		return null;
	}
	return array(
		'preprocess' => $pid,
		'inner_blob' => substr($raw, $off, $blobLen),
	);
}

/**
 * Build preprocess meta array from FZEP inner-fold trailer (stat_pred_inner or dict_*).
 *
 * @return array<string, mixed>|null
 */
function fractal_zip_enwik_inner_fold_preprocess_meta_from_trailer(string $trailerGz): ?array
{
	$unpacked = fractal_zip_enwik_inner_fold_unpack_trailer($trailerGz);
	if ($unpacked === null) {
		return null;
	}
	$pid = (string) $unpacked['preprocess'];
	$inner = (string) $unpacked['inner_blob'];
	if ($pid === 'stat_pred_inner') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_stat_predictor.php';
		return fractal_zip_enwik_stat_pred_deserialize_compact_meta($inner);
	}
	if ($pid === 'dict_inner' || $pid === 'dict_phda9_inner') {
		return array(
			'preprocess' => $pid,
			'frozen' => true,
			'codec' => $pid === 'dict_phda9_inner' ? 'segment_v2_phda9_inner' : 'segment_v2_inner',
			'sidecars' => array(),
			'fold_dict_inner_blob' => $inner,
		);
	}
	if ($pid === 'consonant_hybrid' || $pid === 'consonant_hybrid_split' || $pid === 'consonant_hybrid_lossy') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_syllable_codec.php';
		$skMeta = fractal_zip_enwik_consonant_hybrid_unpack_compact_meta($inner);
		$skMeta['preprocess'] = $pid;
		$skMeta['frozen'] = true;
		$skMeta['codec'] = $pid === 'consonant_hybrid_split'
			? 'consonant_hybrid_split'
			: ($pid === 'consonant_hybrid_lossy' ? 'consonant_hybrid_lossy' : 'consonant_hybrid_isp');
		$skMeta['embedded'] = true;
		$skMeta['sidecars'] = array();
		return $skMeta;
	}
	if ($pid === 'wiki_lom') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_wiki_lom.php';
		return array(
			'preprocess' => 'wiki_lom',
			'frozen' => true,
			'codec' => 'wiki_lom',
			'sidecars' => array(),
			'fold_wiki_lom_blob' => $inner,
			'flags' => fractal_zip_wiki_lom_layer_flags(true),
		);
	}
	if ($pid === 'phda9_dict_fold') {
		return array(
			'preprocess' => 'phda9_dict_fold',
			'frozen' => true,
			'codec' => 'phda9_dict_fold',
			'sidecars' => array(),
			'fold_phda9_dict_blob' => $inner,
		);
	}
	return null;
}

function fractal_zip_enwik_inner_fold_amortized_cost(int $foldBytes, int $slicePages, ?int $fullPages = null): int
{
	$fullPages = $fullPages ?? FRACTAL_ZIP_ENWIK_INNER_FOLD_FULL_PAGES;
	if ($foldBytes <= 0 || $slicePages <= 0 || $fullPages <= 0) {
		return 0;
	}
	return (int) round($foldBytes * ($slicePages / $fullPages));
}

/**
 * @param list<string> $vocab
 */
function fractal_zip_enwik_inner_fold_encode_vocab(array $vocab): string
{
	$parts = array(fractal_zip_enwik_encode_varint_u32(count($vocab)));
	foreach ($vocab as $w) {
		$w = (string) $w;
		$parts[] = fractal_zip_enwik_encode_varint_u32(strlen($w));
		$parts[] = $w;
	}
	return implode('', $parts);
}

/**
 * @return list<string>
 */
function fractal_zip_enwik_inner_fold_decode_vocab(string $binary): array
{
	$off = 0;
	$dv = fractal_zip_enwik_decode_varint_u32($binary, $off);
	if ($dv === null) {
		throw new RuntimeException('inner fold dict: bad vocab count');
	}
	$n = (int) $dv[0];
	$off = (int) $dv[1];
	$vocab = array();
	$len = strlen($binary);
	for ($i = 0; $i < $n; $i++) {
		$dl = fractal_zip_enwik_decode_varint_u32($binary, $off);
		if ($dl === null) {
			throw new RuntimeException('inner fold dict: bad word length');
		}
		$wlen = (int) $dl[0];
		$off = (int) $dl[1];
		if ($wlen < 0 || $off + $wlen > $len) {
			throw new RuntimeException('inner fold dict: truncated word');
		}
		$vocab[] = substr($binary, $off, $wlen);
		$off += $wlen;
	}
	return $vocab;
}
