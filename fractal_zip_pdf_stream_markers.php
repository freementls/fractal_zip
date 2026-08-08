<?php
declare(strict_types=1);

/**
 * Locate `>>` … `stream` … linebreak markers without PCRE (hot path for multi-stream PDFs).
 *
 * @package fractal_zip_pdf_stream_markers
 */

/**
 * Same non-overlapping matches as preg_match_all('#(>>\s*stream(\r\n|\n))#', ..., PREG_OFFSET_CAPTURE)[0].
 *
 * @return list<array{0: string, 1: int}>
 */
function fractal_zip_pdf_pac_stream_token_offsets(string $work): array {
	$n = strlen($work);
	$matches = [];
	$pos = 0;
	while ($pos < $n) {
		$p = strpos($work, '>>', $pos);
		if ($p === false) {
			break;
		}
		$i = $p + 2;
		if ($i < $n) {
			$i += strspn($work, "\t\n\x0B\f\r ", $i, $n - $i);
		}
		if ($i + 6 > $n) {
			$pos = $p + 2;
			continue;
		}
		if ($work[$i] !== 's' || $work[$i + 1] !== 't' || $work[$i + 2] !== 'r' || $work[$i + 3] !== 'e' || $work[$i + 4] !== 'a' || $work[$i + 5] !== 'm') {
			$pos = $p + 2;
			continue;
		}
		$nl = $i + 6;
		if ($nl >= $n) {
			$pos = $p + 2;
			continue;
		}
		$ch0 = $work[$nl];
		$tokLen = null;
		if ($ch0 === "\r" && $nl + 1 < $n && $work[$nl + 1] === "\n") {
			$tokLen = ($nl + 2) - $p;
		} elseif ($ch0 === "\n") {
			$tokLen = ($nl + 1) - $p;
		}
		if ($tokLen === null) {
			$pos = $p + 2;
			continue;
		}
		$matches[] = [substr($work, $p, $tokLen), $p];
		$pos = $p + $tokLen;
	}
	return $matches;
}
