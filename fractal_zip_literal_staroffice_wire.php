<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_markup_islands.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility.php';

/**
 * Unified StarOffice / legacy binary Office wire peel (markup islands + length-prefixed XML scan).
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_staroffice_wire(string $bytes): ?array {
	if (!fractal_zip_literal_sniff_legacy_markup_compound($bytes)) {
		return null;
	}
	$candidates = array();
	$xml = fractal_zip_literal_pac_peel_markup_islands_concat_wire($bytes);
	if ($xml !== null) {
		$candidates[] = $xml;
	}
	$prefixed = fractal_zip_literal_staroffice_scan_length_prefixed_xml($bytes);
	if ($prefixed !== null) {
		$candidates[] = $prefixed;
	}
	if ($candidates === []) {
		return null;
	}
	$parent = fractal_zip_literal_legibility_score($bytes);
	$best = null;
	$bestScore = -1.0;
	foreach ($candidates as $c) {
		$child = fractal_zip_literal_legibility_score($c[0]);
		$score = $child['score'];
		if ($score > $bestScore) {
			$bestScore = $score;
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

/**
 * Scan for 32-bit little-endian length followed by <?xml payload.
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_staroffice_scan_length_prefixed_xml(string $bytes): ?array {
	$n = strlen($bytes);
	$parts = array();
	$seen = array();
	for ($i = 4; $i < $n - 8; $i++) {
		if (substr($bytes, $i, 5) !== '<?xml') {
			continue;
		}
		foreach (array(4, 8, 12) as $back) {
			if ($i < $back) {
				continue;
			}
			$len = unpack('V', substr($bytes, $i - $back, 4));
			$len = is_array($len) ? (int) $len[1] : 0;
			if ($len < 256 || $len > 8388608 || $i - $back + 4 + $len > $n) {
				continue;
			}
			$payload = substr($bytes, $i - $back + 4, $len);
			if (!str_starts_with($payload, '<?xml')) {
				continue;
			}
			$island = fractal_zip_literal_markup_island_slice($payload, 0);
			if ($island === null || strlen($island) < 512) {
				continue;
			}
			$h = md5($island, true);
			if (isset($seen[$h])) {
				continue;
			}
			$seen[$h] = true;
			$parts[] = $island;
			break;
		}
	}
	if ($parts === []) {
		return null;
	}
	$concat = implode("\n\n", $parts);
	return array($concat, 'SOXML:' . count($parts));
}
