<?php
declare(strict_types=1);

/**
 * Shared “peel everything lossless, then normalize streams/rasters” path used by:
 * - literal-bundle transform selection (FZB modes)
 * - zip_folder per-member input (so fractal_zip sees the same unwrapped bytes as the bundle would)
 *
 * Order matches choose_best_literal_bundle_transform: semantic ZIP/7z/MPQ (while no pending gzip stack),
 * consecutive gzip peels, then image_pac and stream multipass; repeated up to peel-stabilize rounds.
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
			while ($gzipStack === [] && count($semanticLayers) < $maxSem && fractal_zip_literal_semantic_zip_enabled()) {
				$zp = fractal_zip_literal_pac_peel_zip_single_semantic($work);
				if ($zp === null) {
					break;
				}
				$semanticLayers[] = ['zip', $zp[1]];
				$work = $zp[0];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_zip_strip($pathRaster);
				$innerProgress = true;
			}
			while ($gzipStack === [] && count($semanticLayers) < $maxSem && fractal_zip_literal_semantic_7z_enabled()) {
				$sp = fractal_zip_literal_pac_peel_seven_single_semantic($work);
				if ($sp === null) {
					break;
				}
				$semanticLayers[] = ['7z', $sp[1]];
				$work = $sp[0];
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_7z_strip($pathRaster);
				$innerProgress = true;
			}
			while ($gzipStack === [] && count($semanticLayers) < $maxSem && fractal_zip_literal_semantic_mpq_enabled()) {
				if (substr($work, 0, 3) !== 'MPQ' || !fractal_zip_literal_path_looks_mpq_semantic($pathRaster)) {
					break;
				}
				$mp = fractal_zip_literal_pac_peel_mpq_semantic($work);
				if ($mp === null) {
					break;
				}
				$innerCandidate = $mp[0];
				$alwaysPeelMpq = getenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_ALWAYS') === '1';
				if (!$alwaysPeelMpq) {
					$pb = gzdeflate($work, 1);
					$pa = gzdeflate($innerCandidate, 1);
					if ($pb === false || $pa === false || strlen($pa) >= strlen($pb)) {
						break;
					}
				}
				$semanticLayers[] = ['mpq', $mp[1]];
				$work = $innerCandidate;
				$pathRaster = fractal_zip_literal_path_for_raster_pac_after_mpq_strip($pathRaster);
				$innerProgress = true;
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
