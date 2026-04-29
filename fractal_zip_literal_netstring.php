<?php
declare(strict_types=1);

/**
 * Entire file is a single netstring (DJB / common RPC encoding): <decimal> ':' <N bytes> ',' and nothing else
 * (length in decimal without leading zeros except a lone 0; empty body is the file "0:,") — FZB literal mode 25.
 * Tag is v1 always empty; rebuild is canonical.
 */

function fractal_zip_literal_semantic_netstring_file_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_NETSTRING');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_path_looks_netstring_file_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	return str_ends_with($low, '.netstr') || str_ends_with($low, '.netstring');
}

function fractal_zip_literal_path_for_raster_pac_after_netstring_file_strip(string $relPath): string {
	$rel = str_replace('\\', '/', (string) $relPath);
	$low = strtolower($rel);
	foreach (['.netstr', '.netstring'] as $s) {
		if(str_ends_with($low, $s)) {
			$k = (string) preg_replace('~' . preg_quote($s, '~') . '$~', '', $rel, 1);
			if($k === '') {
				return 'netstr-inner';
			}
			return rtrim($k, '/') . '/netstr-inner';
		}
	}
	return rtrim($rel, '/') . '/netstr-inner';
}

/**
 * @return string|null payload, or null if the buffer is not fully consumed as one netstring
 */
function fractal_zip_literal_netstring_file_parse(string $work): ?string {
	$n = strlen($work);
	if($n < 3) {
		return null;
	}
	$i = 0;
	$ds = '';
	while($i < $n && $work[$i] >= '0' && $work[$i] <= '9') {
		$ds .= $work[$i];
		$i++;
	}
	if($ds === '' || ($ds[0] === '0' && strlen($ds) > 1)) {
		return null;
	}
	if($i >= $n || $work[$i] !== ':') {
		return null;
	}
	if(strlen($ds) > 18) {
		return null;
	}
	$len = (int) $ds;
	if((string) $len !== $ds) {
		return null;
	}
	$body0 = $i + 1;
	$endBody = $body0 + $len;
	if($endBody + 1 > $n) {
		return null;
	}
	if($endBody >= $n || $work[$endBody] !== ',') {
		return null;
	}
	if($endBody + 1 !== $n) {
		return null;
	}
	return $len === 0 ? '' : substr($work, $body0, $len);
}

function fractal_zip_literal_pac_rebuild_netstring_file(string $payload, string $tag): ?string {
	if($tag !== '') {
		return null;
	}
	$s = (string) strlen($payload);
	return $s . ':' . $payload . ',';
}

function fractal_zip_literal_netstring_file_sniffs(string $work): bool {
	if(!fractal_zip_literal_semantic_netstring_file_enabled()) {
		return false;
	}
	return fractal_zip_literal_netstring_file_parse($work) !== null;
}

/**
 * @return array{0: string, 1: string}|null [payload, ""]
 */
function fractal_zip_literal_pac_peel_netstring_file(string $compressed): ?array {
	if(!fractal_zip_literal_semantic_netstring_file_enabled()) {
		return null;
	}
	if(!function_exists('fractal_zip_literal_pac_payload_within_limit') || !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$payload = fractal_zip_literal_netstring_file_parse($compressed);
	if($payload === null) {
		return null;
	}
	$re = fractal_zip_literal_pac_rebuild_netstring_file($payload, '');
	if($re === null || $re !== $compressed) {
		return null;
	}
	return array($payload, '');
}
