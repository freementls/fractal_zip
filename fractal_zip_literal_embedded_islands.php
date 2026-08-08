<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility.php';

/**
 * Find embedded compression / archive islands in opaque blobs (archaic docs, DB dumps, medical, etc.).
 */

/**
 * @return list<array{off: int, kind: string, len: int}>
 */
function fractal_zip_literal_embedded_island_scan(string $bytes, int $maxHits = 48): array {
	$n = strlen($bytes);
	$hits = array();
	$patterns = array(
		"\x1f\x8b" => 'gzip',
		"\x78\x9c" => 'zlib',
		"\x78\x01" => 'zlib',
		"\x78\xda" => 'zlib',
		"PK\x03\x04" => 'zip',
		'%PDF' => 'pdf',
		"BZh" => 'bzip2',
	);
	foreach ($patterns as $magic => $kind) {
		$ml = strlen($magic);
		$off = 0;
		while ($off < $n && count($hits) < $maxHits) {
			$p = strpos($bytes, $magic, $off);
			if ($p === false) {
				break;
			}
			$hits[] = array('off' => $p, 'kind' => $kind, 'len' => $ml);
			$off = $p + 1;
		}
	}
	usort($hits, static fn ($a, $b) => $a['off'] <=> $b['off']);
	return $hits;
}

/**
 * Try gzip member at offset; return inflated payload or null.
 */
function fractal_zip_literal_embedded_try_gzip_at(string $bytes, int $off, int $maxTry = 16777216): ?string {
	$n = strlen($bytes);
	if ($off < 0 || $off + 2 > $n) {
		return null;
	}
	$slice = substr($bytes, $off, min($maxTry, $n - $off));
	$out = @gzdecode($slice);
	return (is_string($out) && $out !== '') ? $out : null;
}

/**
 * Try raw zlib at offset (no gzip header).
 */
function fractal_zip_literal_embedded_try_zlib_at(string $bytes, int $off, int $maxTry = 16777216): ?string {
	$n = strlen($bytes);
	if ($off < 0 || $off + 2 > $n) {
		return null;
	}
	$slice = substr($bytes, $off, min($maxTry, $n - $off));
	$out = @zlib_decode($slice);
	return (is_string($out) && $out !== '') ? $out : null;
}

/**
 * Peel the best embedded island that improves legibility (wire bytes for deep_unwrap).
 *
 * @return array{0: string, 1: string}|null [innerWire, tag]
 */
function fractal_zip_literal_pac_peel_embedded_island_wire(string $bytes): ?array {
	$parentScore = fractal_zip_literal_legibility_score($bytes);
	$hits = fractal_zip_literal_embedded_island_scan($bytes);
	if ($hits === []) {
		return null;
	}
	$best = null;
	$bestDelta = 0.0;
	foreach ($hits as $h) {
		$off = (int) $h['off'];
		$kind = (string) $h['kind'];
		$payload = null;
		if ($kind === 'gzip') {
			$payload = fractal_zip_literal_embedded_try_gzip_at($bytes, $off);
		} elseif ($kind === 'zlib') {
			$payload = fractal_zip_literal_embedded_try_zlib_at($bytes, $off);
		}
		if ($payload === null) {
			continue;
		}
		$plen = strlen($payload);
		$blen = strlen($bytes);
		$minRel = max(4096, (int) ($blen * 0.02));
		if ($plen < 256 || $plen > $blen * 2) {
			continue;
		}
		if ($plen < $minRel && !fractal_zip_literal_embedded_island_trusted_magic($payload)) {
			continue;
		}
		$childScore = fractal_zip_literal_legibility_score($payload);
		if ($childScore['printable_ratio'] < 0.55 && $childScore['markup_hits'] < 1) {
			continue;
		}
		$delta = $childScore['score'] - $parentScore['score'];
		if ($delta < 1.0) {
			continue;
		}
		$pb = @gzdeflate($bytes, 1);
		$pa = @gzdeflate($payload, 1);
		if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
			continue;
		}
		if ($plen < $minRel && strlen($pa) < (int) (strlen($pb) * 0.85)) {
			continue;
		}
		if ($delta > $bestDelta) {
			$bestDelta = $delta;
			$tag = 'EISL:' . $kind . ':' . $off;
			$best = array($payload, $tag);
		}
	}
	return $best;
}

function fractal_zip_literal_embedded_island_trusted_magic(string $payload): bool {
	$m = fractal_zip_literal_legibility_magic_label($payload);
	return in_array($m, array('pdf', 'zip', 'gzip', 'tar', 'ole'), true)
		|| str_starts_with($payload, '<?xml')
		|| str_contains(substr($payload, 0, 4096), '<html');
}
