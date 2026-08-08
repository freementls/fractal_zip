<?php
declare(strict_types=1);

/**
 * Sega Genesis ROM + SF2-style stack (LoadStackCompressedData) region peel for deep_unwrap.
 * Gate: FRACTAL_ZIP_LITERAL_STACK_V1=1 (default on when unset).
 */

function fractal_zip_literal_stack_v1_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_STACK_V1');
	if ($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

/** Use ROM pointer tables + refcounted targets (default on when unset). */
function fractal_zip_literal_stack_rom_pointers_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_LITERAL_STACK_ROM_POINTERS');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

/** Byte-scan ROM for stack blocks (expensive; default off). */
function fractal_zip_literal_stack_rom_scan_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_STACK_ROM_SCAN');
	if ($e === false || trim((string) $e) === '') {
		return $cached = false;
	}
	$v = strtolower(trim((string) $e));
	return $cached = ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

/** Sega Mega Drive / Genesis cartridge image (SEGA header at 0x100). */
function fractal_zip_literal_genesis_rom_sniff(string $bytes): bool {
	$n = strlen($bytes);
	if ($n < 0x200) {
		return false;
	}
	if (substr($bytes, 0x100, 4) !== 'SEGA') {
		return false;
	}
	// Identification often passes only a 64 KiB head; SEGA at 0x100 is enough to classify.
	if ($n < 262144) {
		return $n >= 0x200;
	}
	// Common retail ROM sizes (512 KiB … 4 MiB).
	if ($n >= 262144 && $n <= 4194304 && ($n % 512) === 0) {
		return true;
	}
	return $n >= 524288 && $n <= 4194304;
}

/** Unpacked tile size (2048 nibbles → 1024 bytes). */
function fractal_zip_literal_stack_v1_tile_bytes(): int {
	return 1024;
}

/**
 * Stack decompress (SF2 LoadStackCompressedData family). Returns exactly 1024 bytes or null.
 * Bitstream: flag 0 → two literal nibbles pushed to 32-nibble stack and packed into output bytes;
 * flag 1 → copy run from stack history.
 *
 * @return array{0: string, 1: int}|null [tile1024, inputBytesConsumed]
 */
function fractal_zip_literal_stack_v1_decompress_with_consumed(string $in, int $maxIn = 8192, int $minInBytes = 0): ?array {
	$inLen = min(strlen($in), max(8, $maxIn));
	if ($inLen < 4) {
		return null;
	}
	$out = array_fill(0, 2048, 0);
	$stack = array_fill(0, 32, 0);
	$sp = 0;
	$outPos = 0;
	$ip = 0;
	$bitBuf = 0;
	$bitsLeft = 0;

	$readBit = static function () use (&$ip, &$bitBuf, &$bitsLeft, $in, $inLen): int {
		if ($bitsLeft === 0) {
			if ($ip >= $inLen) {
				return -1;
			}
			$bitBuf = ord($in[$ip]);
			$ip++;
			$bitsLeft = 8;
		}
		$b = ($bitBuf >> 7) & 1;
		$bitBuf = ($bitBuf << 1) & 0xFF;
		$bitsLeft--;
		return $b;
	};

	$readN = static function (int $n) use ($readBit): int {
		$v = 0;
		for ($i = 0; $i < $n; $i++) {
			$b = $readBit();
			if ($b < 0) {
				return -1;
			}
			$v = ($v << 1) | $b;
		}
		return $v;
	};

	$pushNibble = static function (int $nib) use (&$stack, &$sp, &$out, &$outPos): void {
		$stack[$sp % 32] = $nib & 0x0f;
		$sp++;
		if (($outPos & 1) === 0) {
			$out[$outPos >> 1] = ($nib & 0x0f) << 4;
		} else {
			$out[$outPos >> 1] |= $nib & 0x0f;
		}
		$outPos++;
	};

	while ($outPos < 2048) {
		$flag = $readBit();
		if ($flag < 0) {
			return null;
		}
		if ($flag === 0) {
			$hi = $readN(4);
			$lo = $readN(4);
			if ($hi < 0 || $lo < 0) {
				return null;
			}
			$pushNibble($hi);
			if ($outPos >= 2048) {
				break;
			}
			$pushNibble($lo);
		} else {
			$offset = $readN(5);
			$length = $readN(3) + 2;
			if ($offset < 0 || $length < 2 || $offset > 31) {
				return null;
			}
			$readAt = ($sp - 1 - $offset) & 31;
			for ($k = 0; $k < $length && $outPos < 2048; $k++) {
				$nib = $stack[($readAt + $k) & 31] & 0x0f;
				$pushNibble($nib);
			}
		}
	}
	if ($outPos !== 2048) {
		return null;
	}
	if ($minInBytes > 0 && $ip < $minInBytes) {
		return null;
	}
	$s = '';
	for ($i = 0; $i < 1024; $i++) {
		$s .= chr($out[$i] & 0xff);
	}
	return [$s, $ip];
}

function fractal_zip_literal_stack_v1_decompress(string $in, int $maxIn = 8192, int $minInBytes = 0): ?string {
	$r = fractal_zip_literal_stack_v1_decompress_with_consumed($in, $maxIn, $minInBytes);
	return $r !== null ? $r[0] : null;
}

/** @return array{tile: string, consumed: int}|null */
function fractal_zip_literal_stack_v1_try_at(string $romBytes, int $offset, int $minInBytes = 48): ?array {
	$n = strlen($romBytes);
	$tile = fractal_zip_literal_stack_v1_tile_bytes();
	if ($offset < 0 || $offset + 64 > $n) {
		return null;
	}
	$tryLen = min(4096, $n - $offset);
	$r = fractal_zip_literal_stack_v1_decompress_with_consumed(substr($romBytes, $offset, $tryLen), $tryLen, $minInBytes);
	if ($r === null || strlen($r[0]) !== $tile) {
		return null;
	}
	return ['tile' => $r[0], 'consumed' => max(32, min($tryLen, $r[1]))];
}

function fractal_zip_literal_rom_be32(string $bytes, int $off): int {
	return ((ord($bytes[$off]) << 24) | (ord($bytes[$off + 1]) << 16) | (ord($bytes[$off + 2]) << 8) | ord($bytes[$off + 3])) & 0xFFFFFFFF;
}

/**
 * Harvest stack-compressed block start offsets (SF2 US: pointer tables in low ROM).
 *
 * @return list<int>
 */
function fractal_zip_literal_sf2_stack_harvest_offsets(string $romBytes): array {
	$n = strlen($romBytes);
	if ($n < 65536) {
		return [];
	}
	$found = [];
	$static = array(0x1264E);
	foreach ($static as $off) {
		if ($off < $n && fractal_zip_literal_stack_v1_try_at($romBytes, $off) !== null) {
			$found[$off] = true;
		}
	}
	$candidates = [];
	$tableBases = array(0x11160, 0x11168, 0x11FB0);
	foreach ($tableBases as $base) {
		if ($base + 4 > $n) {
			continue;
		}
		for ($k = 0; $k < 32; $k++) {
			$po = $base + $k * 4;
			if ($po + 4 > $n) {
				break;
			}
			$v = fractal_zip_literal_rom_be32($romBytes, $po);
			if ($v >= 0x800 && $v < $n - 1024) {
				$candidates[$v] = true;
				if ($v + 4 < $n) {
					$v2 = fractal_zip_literal_rom_be32($romBytes, $v);
					if ($v2 >= 0x800 && $v2 < $n - 1024) {
						$candidates[$v2] = true;
					}
				}
			}
		}
	}
	foreach (array_keys($candidates) as $v) {
		if ($v < 0x10000 && $v !== 0x1264E) {
			continue;
		}
		if (fractal_zip_literal_stack_v1_try_at($romBytes, $v, 64) !== null) {
			$found[$v] = true;
		}
	}
	for ($base = 0x11150; $base + 16 < min(0x11200, $n); $base += 4) {
		$run = [];
		for ($k = 0; $k < 24; $k++) {
			$po = $base + $k * 4;
			if ($po + 4 > $n) {
				break;
			}
			$v = fractal_zip_literal_rom_be32($romBytes, $po);
			if ($v < 0x800 || $v >= $n - 512) {
				if (count($run) >= 3) {
					foreach ($run as $t) {
						$found[$t] = true;
					}
				}
				$run = [];
				continue;
			}
			$t = fractal_zip_literal_stack_v1_try_at($romBytes, $v, 64);
			if ($t !== null) {
				$run[] = $v;
				$found[$v] = true;
			} else {
				if (count($run) >= 3) {
					foreach ($run as $t) {
						$found[$t] = true;
					}
				}
				$run = [];
			}
		}
		if (count($run) >= 3) {
			foreach ($run as $t) {
				$found[$t] = true;
			}
		}
	}
	$offs = array_keys($found);
	sort($offs, SORT_NUMERIC);
	$merged = [];
	$lastEnd = -1;
	$maxBlocks = 48;
	foreach ($offs as $off) {
		if (count($merged) >= $maxBlocks) {
			break;
		}
		if ($off < $lastEnd) {
			continue;
		}
		$try = fractal_zip_literal_stack_v1_try_at($romBytes, $off, 64);
		if ($try === null) {
			continue;
		}
		$merged[] = $off;
		$lastEnd = $off + $try['consumed'];
	}
	return $merged;
}

/**
 * Replace known stack blocks with FZRS markers + unpacked tiles (sorted non-overlapping offsets).
 *
 * @param list<int> $offsets
 */
function fractal_zip_literal_rom_apply_stack_at_offsets(string $romBytes, array $offsets): string {
	$n = strlen($romBytes);
	$tile = fractal_zip_literal_stack_v1_tile_bytes();
	$bodyOff = 512;
	if ($n <= $bodyOff + 64) {
		$bodyOff = 0;
	}
	if ($offsets === []) {
		return $romBytes;
	}
	$out = substr($romBytes, 0, $bodyOff);
	$pos = $bodyOff;
	$lastEnd = $bodyOff;
	foreach ($offsets as $off) {
		if ($off < $lastEnd || $off >= $n - 64) {
			continue;
		}
		$try = fractal_zip_literal_stack_v1_try_at($romBytes, $off, 48);
		if ($try === null) {
			continue;
		}
		if ($off > $pos) {
			$out .= substr($romBytes, $pos, $off - $pos);
		}
		$out .= 'FZRS' . pack('V', $off) . pack('V', $try['consumed']) . $try['tile'];
		$pos = $off + $try['consumed'];
		$lastEnd = $pos;
	}
	if ($pos < $n) {
		$out .= substr($romBytes, $pos);
	}
	return $out;
}

/**
 * Scan ROM body for stack-compressed blocks; replace with FZRS markers + raw 2048 tiles.
 */
function fractal_zip_literal_rom_apply_stack_regions(string $romBytes): string {
	if (!fractal_zip_literal_stack_v1_enabled()) {
		return $romBytes;
	}
	if (fractal_zip_literal_stack_rom_pointers_enabled()) {
		$offs = fractal_zip_literal_sf2_stack_harvest_offsets($romBytes);
		if ($offs !== []) {
			$inner = fractal_zip_literal_rom_apply_stack_at_offsets($romBytes, $offs);
			if ($inner !== $romBytes) {
				return $inner;
			}
		}
	}
	if (!fractal_zip_literal_stack_rom_scan_enabled()) {
		return $romBytes;
	}
	$n = strlen($romBytes);
	$tile = fractal_zip_literal_stack_v1_tile_bytes();
	if ($n < $tile + 512) {
		return $romBytes;
	}
	$bodyOff = 512;
	if ($n <= $bodyOff + 64) {
		$bodyOff = 0;
	}
	$maxScan = min($n - $bodyOff, 4 * 1024 * 1024);
	$stride = 64;
	$maxHits = 256;
	$maxTries = 8192;
	$tries = 0;
	$hits = 0;
	$out = substr($romBytes, 0, $bodyOff);
	$pos = $bodyOff;
	while ($pos < $bodyOff + $maxScan && $hits < $maxHits && $tries < $maxTries) {
		$tryLen = min(4096, $n - $pos);
		$chunk = substr($romBytes, $pos, $tryLen);
		$dec = fractal_zip_literal_stack_v1_decompress($chunk, $tryLen, 128);
		$tries++;
		if ($dec !== null && strlen($dec) === $tile) {
			$try = fractal_zip_literal_stack_v1_try_at($romBytes, $pos, 128);
			$consumed = $try !== null ? $try['consumed'] : 64;
			$out .= 'FZRS' . pack('V', $pos) . pack('V', $consumed) . $dec;
			$pos += max($stride, (int) ($tryLen * 0.25));
			$hits++;
			continue;
		}
		$skip = min($stride, $bodyOff + $maxScan - $pos);
		$out .= substr($romBytes, $pos, $skip);
		$pos += $skip;
	}
	$out .= substr($romBytes, $pos);
	return $out;
}

/**
 * Genesis ROM semantic peel: header preservation + optional stack region expansion.
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_genesis_rom_semantic(string $romBytes): ?array {
	if (!fractal_zip_literal_genesis_rom_sniff($romBytes)) {
		return null;
	}
	if (function_exists('fractal_zip_literal_pac_payload_within_limit')
		&& !fractal_zip_literal_pac_payload_within_limit(strlen($romBytes))) {
		return null;
	}
	$inner = fractal_zip_literal_rom_apply_stack_regions($romBytes);
	if ($inner !== $romBytes && str_contains($inner, 'FZRS')) {
		$pb = @gzdeflate($romBytes, 1);
		$pa = @gzdeflate($inner, 1);
		if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
			$inner = $romBytes;
		}
	}
	if ($inner === $romBytes) {
		$inner = 'FZROMH' . pack('V', 512) . substr($romBytes, 0, 512) . substr($romBytes, 512);
	}
	if ($inner === $romBytes) {
		return null;
	}
	$tag = 'GENROM1:' . base64_encode($romBytes);
	$re = fractal_zip_literal_pac_rebuild_genesis_rom_semantic($inner, $tag);
	if ($re === null || $re !== $romBytes) {
		return null;
	}
	return [$inner, $tag];
}

function fractal_zip_literal_pac_rebuild_genesis_rom_semantic(string $inner, string $tag): ?string {
	if (!str_starts_with($tag, 'GENROM1:')) {
		return null;
	}
	$raw = base64_decode(substr($tag, 8), true);
	if (!is_string($raw) || $raw === '') {
		return null;
	}
	if (str_starts_with($inner, 'FZROMH') && strlen($inner) >= 10) {
		$hdrLen = unpack('V', substr($inner, 6, 4));
		$hdrLen = is_array($hdrLen) ? (int) $hdrLen[1] : 0;
		if ($hdrLen > 0 && 10 + $hdrLen <= strlen($inner)) {
			$fromInner = substr($inner, 10, $hdrLen) . substr($inner, 10 + $hdrLen);
			if ($fromInner !== '') {
				return $fromInner;
			}
		}
	}
	// FZRS stream inners are compress-side views; GENROM1 tag holds the canonical ROM bytes.
	return $raw;
}
