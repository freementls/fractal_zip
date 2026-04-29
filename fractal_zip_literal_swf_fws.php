<?php
declare(strict_types=1);

/**
 * Uncompressed Flash (SWF): 8-byte header: 'F' 'W' 'S' (3) + version (1) + file length little-endian (4) = full file size.
 * Payload = bytes after the header. Rebuild is bit-identical if the 8-byte header is preserved in the FZB tag. FZB mode 26.
 * Compressed `CWS` and Zstandard `ZWS` are not accepted. Env: FRACTAL_ZIP_LITERAL_SEMANTIC_SWF_FWS.
 */

function fractal_zip_literal_semantic_swf_fws_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_SWF_FWS');
	if($e === false || trim((string) $e) === '') {
		return $cached = true;
	}
	$v = strtolower(trim((string) $e));
	return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_literal_path_looks_swf_fws_semantic(string $relPath): bool {
	$low = strtolower(str_replace('\\', '/', $relPath));
	return str_ends_with($low, '.swf');
}

function fractal_zip_literal_path_for_raster_pac_after_swf_fws_strip(string $relPath): string {
	$rel = str_replace('\\', '/', (string) $relPath);
	$low = strtolower($rel);
	$b = (string) preg_replace('~[^/]*\.swf$~i', 'swf-inner', $rel);
	if($b !== $rel) {
		return $b;
	}
	return rtrim($rel, '/') . '/swf-payload';
}

const FRACTAL_ZIP_SWF_FWS_HEADER_BYTES = 8;

/**
 * @return array{0: string, 1: string}|null [ body after 8B, 8-byte header ] or null
 */
function fractal_zip_literal_swf_fws_parse(string $w): ?array {
	$n = strlen($w);
	if($n < (int) FRACTAL_ZIP_SWF_FWS_HEADER_BYTES) {
		return null;
	}
	$sig = substr($w, 0, 3);
	if($sig !== 'FWS') {
		return null;
	}
	if($n < 8) {
		return null;
	}
	$wants = pack('V', $n);
	if(substr($w, 4, 4) !== $wants) {
		return null;
	}
	$head = substr($w, 0, (int) FRACTAL_ZIP_SWF_FWS_HEADER_BYTES);
	$body = $n > (int) FRACTAL_ZIP_SWF_FWS_HEADER_BYTES ? substr($w, (int) FRACTAL_ZIP_SWF_FWS_HEADER_BYTES) : '';
	return array($body, $head);
}

/**
 * $tag8 must be the exact 8-byte header; payload is body after offset 8.
 * @return string|null
 */
function fractal_zip_literal_pac_rebuild_swf_fws(string $payload, string $tag8): ?string {
	if(strlen($tag8) !== (int) FRACTAL_ZIP_SWF_FWS_HEADER_BYTES) {
		return null;
	}
	if(substr($tag8, 0, 3) !== 'FWS') {
		return null;
	}
	$tot = 8 + strlen($payload);
	if(pack('V', $tot) !== substr($tag8, 4, 4)) {
		return null;
	}
	return $tag8 . $payload;
}

function fractal_zip_literal_swf_fws_sniffs(string $work): bool {
	if(!fractal_zip_literal_semantic_swf_fws_enabled()) {
		return false;
	}
	return fractal_zip_literal_swf_fws_parse($work) !== null;
}

/**
 * @return array{0: string, 1: string}|null [ post-header payload, 8-byte FWS header for FZB / rebuild ]
 */
function fractal_zip_literal_pac_peel_swf_fws(string $compressed): ?array {
	if(!fractal_zip_literal_semantic_swf_fws_enabled()) {
		return null;
	}
	if(!function_exists('fractal_zip_literal_pac_payload_within_limit') || !fractal_zip_literal_pac_payload_within_limit(strlen($compressed))) {
		return null;
	}
	$got = fractal_zip_literal_swf_fws_parse($compressed);
	if($got === null) {
		return null;
	}
	$pl = (string) $got[0];
	$th = (string) $got[1];
	$re = fractal_zip_literal_pac_rebuild_swf_fws($pl, $th);
	if($re === null || $re !== $compressed) {
		return null;
	}
	// Second element is 8 bytes — wrap layer stores via encode_varint(8).th
	return array($pl, $th);
}
