<?php
declare(strict_types=1);

/**
 * OOXML embedded image lossy preprocess for fractal_zip mode-18 ZIP semantic peel.
 *
 * When FRACTAL_ZIP_OOXML_IMAGE_LOSSY=1 (or unset with FRACTAL_ZIP_OOXML_IMAGE_LOSSY_AUTO=1),
 * resamples word/media/* to display-budget pixels before choose_best_literal_bundle_transform.
 * Reuses FFS ffs.ooxml.images.php when present.
 *
 * Production recommendation: enable for document-heavy corpora; folder restore uses
 * FZHR verbatim when rebuild is non-lossless. Browse listing remains lossless.
 *
 * Irreversible in standalone fractal_zip archives unless FFS retention_map is wired later.
 */

function fractal_zip_ooxml_lossy_enabled(): bool {
	static $cached = null;
	if($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_OOXML_IMAGE_LOSSY');
	if($e === false || trim((string)$e) === '') {
		$auto = getenv('FRACTAL_ZIP_OOXML_IMAGE_LOSSY_AUTO');
		if($auto !== false && in_array(strtolower(trim((string)$auto)), array('1', 'on', 'true', 'yes'), true)) {
			return $cached = true;
		}
		return $cached = false;
	}
	$v = strtolower(trim((string)$e));
	return $cached = ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

function fractal_zip_ooxml_lossy_load_ffs_bridge(): void {
	static $loaded = false;
	if($loaded) {
		return;
	}
	$path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'ffs' . DIRECTORY_SEPARATOR . 'ffs.ooxml.images.php';
	if(!is_file($path)) {
		return;
	}
	if(!function_exists('ffs_archive_normalize_member_path')) {
		function ffs_archive_normalize_member_path(string $member): string {
			return ltrim(str_replace('\\', '/', $member), '/');
		}
	}
	require_once $path;
	$loaded = true;
}

function fractal_zip_ooxml_is_document_container(string $containerRel): bool {
	$base = strtolower(basename(str_replace('\\', '/', $containerRel)));
	return $base !== '' && preg_match('/\\.(docx|pptx|xlsx)$/i', $base) === 1;
}

function fractal_zip_ooxml_is_media_member(string $memberName): bool {
	$m = ltrim(str_replace('\\', '/', $memberName), '/');
	return (bool)preg_match('#^(?:word|ppt|xl)/(?:media|embeddings)/.+\\.(jpe?g|png|webp|gif)$#i', $m);
}

/**
 * @param array<string, array{display_w_px: int, display_h_px: int}>|null $placements
 */
function fractal_zip_ooxml_lossy_preprocess_member(
	string $zipBytes,
	string $containerRel,
	string $memberName,
	string $memberBytes,
	?array &$placements = null
): string {
	if(!fractal_zip_ooxml_lossy_enabled() || $memberBytes === '' || $zipBytes === '') {
		return $memberBytes;
	}
	if(!fractal_zip_ooxml_is_document_container($containerRel) || !fractal_zip_ooxml_is_media_member($memberName)) {
		return $memberBytes;
	}
	fractal_zip_ooxml_lossy_load_ffs_bridge();
	if(!function_exists('ffs_ooxml_collect_image_placements_from_bytes')
		|| !function_exists('ffs_ooxml_image_target_dimensions')) {
		return $memberBytes;
	}
	if($placements === null) {
		$place = ffs_ooxml_collect_image_placements_from_bytes($zipBytes);
		$placements = (!empty($place['ok']) && is_array($place['placements'] ?? null))
			? $place['placements']
			: array();
	}
	$member_norm = function_exists('ffs_archive_normalize_member_path')
		? ffs_archive_normalize_member_path($memberName)
		: ltrim(str_replace('\\', '/', $memberName), '/');
	$media_key = preg_match('#^word/#i', $member_norm) ? $member_norm : 'word/' . $member_norm;
	if(!isset($placements[$media_key])) {
		return $memberBytes;
	}
	$info = @getimagesizefromstring($memberBytes);
	if(!is_array($info) || !isset($info[0], $info[1])) {
		return $memberBytes;
	}
	$pl = $placements[$media_key];
	$defaults = function_exists('ffs_ooxml_image_optimize_defaults')
		? ffs_ooxml_image_optimize_defaults()
		: array('oversample' => 1.5, 'min_area_ratio' => 2.0);
	$oversample = (float)($defaults['oversample'] ?? 1.5);
	$min_ratio = (float)($defaults['min_area_ratio'] ?? 2.0);
	$orig_w = (int)$info[0];
	$orig_h = (int)$info[1];
	$disp_w = (int)($pl['display_w_px'] ?? 0);
	$disp_h = (int)($pl['display_h_px'] ?? 0);
	if($disp_w < 1 || $disp_h < 1) {
		return $memberBytes;
	}
	[$tw, $th] = ffs_ooxml_image_target_dimensions($orig_w, $orig_h, $disp_w, $disp_h, $oversample);
	$orig_area = (float)($orig_w * $orig_h);
	$disp_area = (float)max(1, $disp_w * $disp_h);
	if(($orig_area / $disp_area) < $min_ratio || ($tw >= $orig_w && $th >= $orig_h)) {
		return $memberBytes;
	}
	if(function_exists('ffs_ooxml_get_or_create_optimized_image_bytes')
		&& class_exists('FileStore', false)) {
		$opt = ffs_ooxml_get_or_create_optimized_image_bytes($memberBytes, $tw, $th);
	} elseif(function_exists('ffs_ooxml_optimize_image_ephemeral')) {
		$opt = ffs_ooxml_optimize_image_ephemeral($memberBytes, $tw, $th);
	} else {
		return $memberBytes;
	}
	if(empty($opt['ok']) || !isset($opt['blob']) || (string)$opt['blob'] === '') {
		return $memberBytes;
	}
	$shrunk = (string)$opt['blob'];
	require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fractal_zip_bio_lossy.php';
	if(fractal_zip_bio_lossy_enabled()) {
		$shrunk = fractal_zip_bio_lossy_maybe_shrink_member($shrunk, $memberBytes);
	}
	return $shrunk;
}
