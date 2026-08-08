<?php
declare(strict_types=1);

/**
 * Entire file is a single bencode *string* value: `<decimal length>:` + exactly that many data bytes
 * (no other root types; no leading zeros on the length, except a lone `0` for length 0, i.e. the file is exactly `0:`).
 * (FZB literal mode 24). Rebuild is canonical. Tag is v1 always empty.
 */

function fractal_zip_literal_semantic_bencode_str_only_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_BENCODE_STR');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_path_looks_bencode_str_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	return str_ends_with($low, '.bencstr') || str_ends_with($low, '.bencode-str');
}

function fractal_zip_literal_path_for_raster_pac_after_bencode_str_only_strip(string $relPath): string {
	$rel = str_replace('\\', '/', (string) $relPath);
	$low = strtolower($rel);
	foreach (['.bencstr', '.bencode-str'] as $s) {
		if(str_ends_with($low, $s)) {
			$k = (string) preg_replace('~' . preg_quote($s, '~') . '$~', '', $rel, 1);
			if($k === '') {
				return 'bencode-inner';
			}
			return rtrim($k, '/') . '/bencode-inner';
		}
	}
	return rtrim($rel, '/') . '/bencode-inner';
}

/**
 * @return string|null payload, or null if the buffer is not exactly one bencode string
 */
function fractal_zip_literal_bencode_str_only_parse(string $work): ?string {
	$n = strlen($work);
	if($n < 2) {
		return null;
	}
	$i = 0;
	$ds = '';
	while($i < $n && $work[$i] >= '0' && $work[$i] <= '9') {
		$ds .= $work[$i];
		$i++;
	}
	if($ds === '' || $ds[0] === '0' && strlen($ds) > 1) {
		return null;
	}
	if($i >= $n || $work[$i] !== ':') {
		return null;
	}
	$len = (int) $ds;
	if((string) $len !== $ds) {
		return null;
	}
	$bodyOff = $i + 1;
	$end = $bodyOff + $len;
	if($end !== $n) {
		return null;
	}
	return $len === 0 ? '' : substr($work, $bodyOff, $len);
}

/**
 * v1: $tag must be ``
 */
function fractal_zip_literal_pac_rebuild_bencode_str_only(string $payload, string $tag): ?string {
	if($tag !== '') {
		return null;
	}
	$s = (string) strlen($payload);
	return $s . ':' . $payload;
}

function fractal_zip_literal_bencode_str_only_sniffs(string $work): bool {
	if(!fractal_zip_literal_semantic_bencode_str_only_enabled()) {
		return false;
	}
	return fractal_zip_literal_bencode_str_only_parse($work) !== null;
}

/**
 * @return array{0: string, 1: string}|null [payload, ""]
 */
function fractal_zip_literal_pac_peel_bencode_str_only(string $compressed): ?array {
	if(!fractal_zip_literal_semantic_bencode_str_only_enabled()) {
		return null;
	}
	if(!function_exists('fractal_zip_literal_pac_payload_within_limit') || !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$payload = fractal_zip_literal_bencode_str_only_parse($compressed);
	if($payload === null) {
		return null;
	}
	$re = fractal_zip_literal_pac_rebuild_bencode_str_only($payload, '');
	if($re === null || $re !== $compressed) {
		return null;
	}
	return array($payload, '');
}
