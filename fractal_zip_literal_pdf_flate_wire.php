<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_native_pac.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_legibility.php';

/**
 * PDF wire peel: concatenate inflated /FlateDecode stream payloads (legibility for fractal inner).
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_pdf_flate_concat_wire(string $pdf): ?array {
	if (!fractal_zip_pdf_native_pac_enabled() || strlen($pdf) < 32 || !str_starts_with($pdf, '%PDF-')) {
		return null;
	}
	if (strlen($pdf) > 64 * 1024 * 1024) {
		return null;
	}
	$parts = fractal_zip_literal_pdf_collect_flate_stream_payloads($pdf);
	if ($parts === []) {
		return null;
	}
	$inner = implode("\n", $parts);
	if (strlen($inner) < 4096) {
		return null;
	}
	$parent = fractal_zip_literal_legibility_score($pdf);
	$child = fractal_zip_literal_legibility_score($inner);
	if ($child['score'] - $parent['score'] < 1.0) {
		return null;
	}
	$pb = @gzdeflate($pdf, 1);
	$pa = @gzdeflate($inner, 1);
	if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	return array($inner, 'PDFF:' . count($parts));
}

/**
 * @return list<string>
 */
function fractal_zip_literal_pdf_collect_flate_stream_payloads(string $pdf): array {
	$parts = array();
	$objMap = fractal_zip_pdf_object_index_offsets($pdf);
	$markers = fractal_zip_pdf_pac_stream_token_offsets($pdf);
	if ($markers === []) {
		return $parts;
	}
	$seen = array();
	foreach ($markers as $e) {
		if (!is_array($e) || ($e[1] ?? -1) < 0) {
			continue;
		}
		$abs0 = (int) $e[1];
		$tok = (string) $e[0];
		$open = fractal_zip_pdf_stream_dict_opening_lt_lt($pdf, $abs0);
		if ($open < 0) {
			continue;
		}
		$dict = (string) substr($pdf, $open, $abs0 - $open + 2);
		if (!fractal_zip_pdf_pac_dict_flate_fl_only($dict)) {
			continue;
		}
		$dataStart = $abs0 + strlen($tok);
		$lenB = fractal_zip_pdf_dict_length_value($pdf, $dict, $objMap);
		if ($lenB === null || $lenB < 8) {
			continue;
		}
		$oldEnc = (string) substr($pdf, $dataStart, $lenB);
		if (strlen($oldEnc) < $lenB) {
			continue;
		}
		$h = md5($oldEnc, true);
		if (isset($seen[$h])) {
			continue;
		}
		$seen[$h] = true;
		$infl = fractal_zip_pdf_pac_flate_inflate($oldEnc);
		if ($infl === null || $infl === '') {
			continue;
		}
		if (strlen($infl) >= 32) {
			$parts[] = $infl;
		}
	}
	return $parts;
}
