<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_markup_islands.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_printable_wire.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility.php';

/**
 * Parse MZ/PE-style section table in StarOffice shell and return largest on-disk section.
 *
 * @return array{off: int, size: int}|null
 */
function fractal_zip_literal_staroffice_largest_pe_section(string $bytes): ?array {
	if (!str_starts_with($bytes, 'MZ') || strlen($bytes) < 512) {
		return null;
	}
	$peOff = unpack('V', substr($bytes, 0x3C, 4));
	$peOff = is_array($peOff) ? (int) $peOff[1] : 0;
	if ($peOff < 0 || $peOff + 248 > strlen($bytes)) {
		return null;
	}
	if (substr($bytes, $peOff, 4) !== "PE\x00\x00") {
		return null;
	}
	$numSections = unpack('v', substr($bytes, $peOff + 6, 2));
	$optSize = unpack('v', substr($bytes, $peOff + 20, 2));
	$numSections = is_array($numSections) ? (int) $numSections[1] : 0;
	$optSize = is_array($optSize) ? (int) $optSize[1] : 0;
	if ($numSections <= 0 || $numSections > 96) {
		return null;
	}
	$secBase = $peOff + 24 + $optSize;
	$best = null;
	$n = strlen($bytes);
	for ($i = 0; $i < $numSections; $i++) {
		$o = $secBase + $i * 40;
		if ($o + 40 > $n) {
			break;
		}
		$name = rtrim(substr($bytes, $o, 8), "\0");
		$virt = unpack('V', substr($bytes, $o + 12, 4));
		$rawSz = unpack('V', substr($bytes, $o + 16, 4));
		$rawPtr = unpack('V', substr($bytes, $o + 20, 4));
		$virt = is_array($virt) ? (int) $virt[1] : 0;
		$rawSz = is_array($rawSz) ? (int) $rawSz[1] : 0;
		$rawPtr = is_array($rawPtr) ? (int) $rawPtr[1] : 0;
		if ($rawSz < 4096 || $rawPtr <= 0 || $rawPtr + $rawSz > $n) {
			continue;
		}
		if ($best === null || $rawSz > $best['size']) {
			$best = array('name' => $name, 'off' => $rawPtr, 'size' => $rawSz);
		}
	}
	return $best;
}

/**
 * Try peeling largest PE section via zlib/gzip and printable filter.
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_staroffice_section_wire(string $bytes): ?array {
	if (!fractal_zip_literal_sniff_legacy_markup_compound($bytes)) {
		return null;
	}
	$sec = fractal_zip_literal_staroffice_largest_pe_section($bytes);
	if ($sec === null) {
		return null;
	}
	$chunk = substr($bytes, $sec['off'], $sec['size']);
	$candidates = array();
	foreach (array(0, 4, 8, 16) as $skip) {
		if ($skip >= strlen($chunk)) {
			continue;
		}
		$slice = substr($chunk, $skip);
		$z = @zlib_decode($slice);
		if (is_string($z) && strlen($z) >= 4096) {
			$candidates[] = array($z, 'SOZ:' . $sec['name'] . ':' . $skip);
		}
		$g = @gzdecode($slice);
		if (is_string($g) && strlen($g) >= 4096) {
			$candidates[] = array($g, 'SOG:' . $sec['name'] . ':' . $skip);
		}
	}
	$pr = fractal_zip_literal_printable_filter_buffer($chunk, 16);
	if ($pr !== null && strlen($pr) >= 8192) {
		$candidates[] = array($pr, 'SOPR:' . $sec['name']);
	}
	$xml = fractal_zip_literal_pac_peel_markup_islands_concat_wire($bytes);
	if ($xml !== null) {
		$candidates[] = $xml;
	}
	if ($candidates === []) {
		return null;
	}
	$parent = fractal_zip_literal_legibility_score($bytes);
	$best = null;
	$bestScore = -1.0;
	foreach ($candidates as $c) {
		$child = fractal_zip_literal_legibility_score($c[0]);
		if ($child['score'] > $bestScore) {
			$bestScore = $child['score'];
			$best = $c;
		}
	}
	if ($best === null) {
		return null;
	}
	$pb = @gzdeflate($bytes, 1);
	$pa = @gzdeflate($best[0], 1);
	if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	return $best;
}
