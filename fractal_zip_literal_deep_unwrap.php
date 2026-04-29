<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_recursive_peel.php';

/**
 * Shared “peel everything lossless, then normalize streams/rasters” path used by:
 * - literal-bundle transform selection (FZB modes)
 * - zip_folder per-member input (so fractal_zip sees the same unwrapped bytes as the bundle would)
 *
 * Semantic ZIP / 7z / MPQ / ustar TAR / GNU ar / cpio newc / git / bencode / netstring / FWS SWF peels are chosen in **content-heuristic order** (magic + path +
 * fractal_zip_content_format_identify) and re-evaluated after each successful peel so nested shells are not
 * missed. Gzip stack expansion and image/stream PAC rounds follow the same stabilize loop as before.
 *
 * @return array{0: string, 1: list<array{0: string, 1: string}>, 2: list<array{len: int, sha1: string}>}
 */
function fractal_zip_literal_deep_unwrap_with_layers(string $relPath, string $rawBytes): array {
	$rawBytes = (string) $rawBytes;
	$rel = str_replace('\\', '/', (string) $relPath);
	$rel = trim($rel, '/');
	if ($rel === '' || $rawBytes === '') {
		return [$rawBytes, [], []];
	}
	static $mpqSemanticProxyGate = null;
	if ($mpqSemanticProxyGate === null) {
		$e = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE');
		$mpqSemanticProxyGate = false;
		if ($e !== false && trim((string) $e) !== '') {
			$v = strtolower(trim((string) $e));
			$mpqSemanticProxyGate = ($v !== '0' && $v !== 'off' && $v !== 'false' && $v !== 'no');
		}
	}
	$pathRaster = $rel;
	$work = $rawBytes;
	$gzipStack = [];
	$semanticLayers = [];
	$maxRound = fractal_zip_literal_peel_stabilize_max_rounds();
	$maxSem = fractal_zip_literal_semantic_peel_max_total();
	for ($r = 0; $r < $maxRound; $r++) {
		$snap = $work;
		$innerProgress = true;
		while ($innerProgress) {
			$innerProgress = false;
			$w0 = $work;
			if ($gzipStack === []) {
				list($work, $pathRaster, $semanticLayers, $gzipStack) = fractal_zip_literal_recursive_peel_try_one_semantic(
					$rel,
					$pathRaster,
					$work,
					$semanticLayers,
					$gzipStack,
					$maxSem,
					$mpqSemanticProxyGate
				);
			}
			list($work, $pathRaster, $gzipStack) = fractal_zip_literal_append_consecutive_gzip_peels($pathRaster, $work, $gzipStack);
			if ($work !== $w0) {
				$innerProgress = true;
			}
		}
		if ($gzipStack === []) {
			$work = fractal_zip_image_pac_preprocess_literal_for_bundle($pathRaster, $work);
			if (fractal_zip_literal_pac_stream_enabled()) {
				$maxC = fractal_zip_literal_pac_max_compressed_bytes();
				if ($maxC <= 0 || strlen($work) <= $maxC) {
					$work = fractal_zip_literal_pac_preprocess_streams_multipass($pathRaster, $work);
				}
			}
		}
		if ($work === $snap) {
			break;
		}
	}
	return [$work, $semanticLayers, $gzipStack];
}
