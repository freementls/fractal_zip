<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_ole_cfb.php';

if (!function_exists('fractal_zip_literal_semantic_ole_enabled')) {
	function fractal_zip_literal_semantic_ole_enabled(): bool {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_OLE');
		if ($e === false || trim((string) $e) === '') {
			return $cached = true;
		}
		$v = strtolower(trim((string) $e));
		return $cached = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
	}
}

/**
 * @param list<array{name: string, data: string, ranges?: mixed}> $streams
 */
function fractal_zip_literal_ole_streams_concat_for_wire(array $streams): ?string {
	if ($streams === []) {
		return null;
	}
	usort($streams, static function ($a, $b): int {
		$la = strlen((string) ($a['data'] ?? ''));
		$lb = strlen((string) ($b['data'] ?? ''));
		return $lb <=> $la;
	});
	$parts = array();
	foreach ($streams as $st) {
		$data = (string) ($st['data'] ?? '');
		if ($data === '') {
			continue;
		}
		$name = (string) ($st['name'] ?? 'stream');
		$parts[] = "==={$name}===\n" . $data;
	}
	return $parts === [] ? null : implode("\n", $parts);
}

/**
 * Wire peel: concatenate CFB streams for fractal / legibility path (FZB mode 19 handles bundle encode).
 *
 * @return array{0: string, 1: string}|null
 */
function fractal_zip_literal_pac_peel_ole_wire(string $compressed): ?array {
	if (!fractal_zip_literal_semantic_ole_enabled() || !fractal_zip_ole_is_compound($compressed)) {
		return null;
	}
	$parsed = fractal_zip_ole_build_template_and_streams($compressed);
	if ($parsed === null || !isset($parsed['streams']) || !is_array($parsed['streams'])) {
		return null;
	}
	$inner = fractal_zip_literal_ole_streams_concat_for_wire($parsed['streams']);
	if ($inner === null || $inner === '') {
		return null;
	}
	$pb = @gzdeflate($compressed, 1);
	$pa = @gzdeflate($inner, 1);
	if ($pb !== false && $pa !== false && strlen($pa) >= strlen($pb)) {
		return null;
	}
	return array($inner, 'OLEW:');
}
