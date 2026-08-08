<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility.php';

/** True when buffer looks like legacy StarOffice / binary Office with embedded XML (not tar/FZTM inners). */
function fractal_zip_literal_sniff_legacy_markup_compound(string $bytes): bool {
	if ($bytes === '') {
		return false;
	}
	if (str_starts_with($bytes, 'FZTM') || fractal_zip_literal_tar_sniffs_gnu_archive($bytes)) {
		return false;
	}
	$head = strlen($bytes) > 65536 ? substr($bytes, 0, 65536) : $bytes;
	if (str_starts_with($bytes, 'MZ') && (str_contains($bytes, 'WordDocument') || str_contains($bytes, 'StarWriter'))) {
		return true;
	}
	if (str_contains($head, 'StarWriter') || str_contains($head, 'StarOffice')) {
		return true;
	}
	return false;
}

/**
 * Extract legible XML/HTML islands from legacy compound binaries (StarOffice .doc, etc.).
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_markup_islands_wire(string $bytes): ?array {
	$n = strlen($bytes);
	if ($n < 128) {
		return null;
	}
	$parent = fractal_zip_literal_legibility_score($bytes);
	$best = null;
	$bestDelta = 0.0;
	$needles = array('<?xml', '<!DOCTYPE html', '<html', '<HTML');
	$off = 0;
	$tries = 0;
	while ($off < $n && $tries < 24) {
		$hitOff = -1;
		$hitLen = 0;
		foreach ($needles as $needle) {
			$p = stripos($bytes, $needle, $off);
			if ($p !== false && ($hitOff < 0 || $p < $hitOff)) {
				$hitOff = $p;
				$hitLen = strlen($needle);
			}
		}
		if ($hitOff < 0) {
			break;
		}
		$tries++;
		$island = fractal_zip_literal_markup_island_slice($bytes, $hitOff);
		$off = $hitOff + max($hitLen, 64);
		if ($island === null || strlen($island) < 512) {
			continue;
		}
		$child = fractal_zip_literal_legibility_score($island);
		$delta = $child['score'] - $parent['score'];
		if ($delta < 2.0) {
			continue;
		}
		$pb = @gzdeflate($bytes, 1);
		$pa = @gzdeflate($island, 1);
		if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
			continue;
		}
		if ($delta > $bestDelta) {
			$bestDelta = $delta;
			$best = array($island, 'XMLI:' . $hitOff);
		}
	}
	return $best;
}

function fractal_zip_literal_markup_island_slice(string $bytes, int $start): ?string {
	$n = strlen($bytes);
	if ($start < 0 || $start >= $n) {
		return null;
	}
	$maxLen = min(4194304, $n - $start);
	$chunk = substr($bytes, $start, $maxLen);
	$end = strlen($chunk);
	if (str_starts_with($chunk, '<?xml')) {
		foreach (array(
			'</office:document-content>',
			'</office:document>',
			'</module-description>',
			'</manifest:manifest>',
		) as $closeTag) {
			$close = stripos($chunk, $closeTag);
			if ($close !== false) {
				$end = min($end, $close + strlen($closeTag));
				break;
			}
		}
	}
	$slice = substr($chunk, 0, $end);
	$nullAt = strpos($slice, "\x00\x00\x00\x00");
	if ($nullAt !== false && $nullAt > 256) {
		$slice = substr($slice, 0, $nullAt);
	}
	$slice = rtrim($slice);
	if (strlen($slice) < 256) {
		return null;
	}
	if (!preg_match('/^<\?xml\s+version/i', $slice)) {
		return null;
	}
	$head = substr($slice, 0, min(8192, strlen($slice)));
	if (substr_count($head, '<') < 4) {
		return null;
	}
	if (substr_count($head, "\x00") > strlen($head) / 8) {
		return null;
	}
	return $slice;
}

/**
 * Concatenate all XML islands (for multi-island StarOffice docs).
 * Prefer {@see fractal_zip_literal_pac_peel_staroffice_wire()} for full StarOffice peel.
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_markup_islands_concat_wire(string $bytes): ?array {
	$n = strlen($bytes);
	$parts = array();
	$off = 0;
	while ($off < $n && count($parts) < 16) {
		$p = stripos($bytes, '<?xml', $off);
		if ($p === false) {
			break;
		}
		$island = fractal_zip_literal_markup_island_slice($bytes, $p);
		$off = $p + 5;
		if ($island !== null && strlen($island) >= 256) {
			$parts[] = $island;
		}
	}
	if ($parts === []) {
		return null;
	}
	$concat = implode("\n\n", $parts);
	$parent = fractal_zip_literal_legibility_score($bytes);
	$child = fractal_zip_literal_legibility_score($concat);
	if ($child['score'] - $parent['score'] < 3.0) {
		return null;
	}
	$pb = @gzdeflate($bytes, 1);
	$pa = @gzdeflate($concat, 1);
	if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	return array($concat, 'XMLM:' . count($parts));
}
