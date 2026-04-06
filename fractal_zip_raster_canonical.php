<?php
declare(strict_types=1);

/**
 * Lossless semantic raster path for zip_folder: decode BMP/GIF/JPEG/PNG/WebP to a shared canonical
 * RGBA payload (magic FZRC1) so identical pixels across formats share one lazy_fractal_string slice.
 * On-disk originals are preserved via fractal_member_gzip_disk_restore + FZG trailer (same as gzip peel).
 *
 * Unified / literal-bundle path uses FZBD + FZB mode 10 (deduped FZRC1 table + per-file disk + index) when
 * FRACTAL_ZIP_RASTER_CANONICAL_BUNDLE is on (default). Legacy mode 9 containers still decode.
 *
 * Env FRACTAL_ZIP_RASTER_CANONICAL=0 disables try(); bundle raster dedup is also skipped when try is disabled.
 *
 * Bundle path uses FZBD-prefixed FZB* plus mode 10 (disk + canon index) so identical FZRC1 appears once in the table.
 * Legacy mode 9 (inline disk+FZRC1) is still decoded for older containers.
 *
 * JPEG: only if fractal_zip_image_pac_jpeg_semantics_ok_for_canonical_bundle (djpeg pixels match jpegtran -copy all); else
 * the file stays in normal literal transforms (compressed JPEG on disk). FRACTAL_ZIP_RASTER_CANONICAL_BUNDLE=0 disables.
 */

function fractal_zip_raster_canonical_try_for_bundle(string $relPath, string $workBytes, string $diskBytes): ?string {
	if (!fractal_zip_raster_canonical_bundle_inner_enabled() || !fractal_zip_raster_canonical_enabled() || $workBytes === '') {
		return null;
	}
	$ext = strtolower((string) pathinfo(str_replace('\\', '/', $relPath), PATHINFO_EXTENSION));
	if ($ext === 'jpeg') {
		$ext = 'jpg';
	}
	if (!in_array($ext, fractal_zip_raster_canonical_extensions(), true)) {
		return null;
	}
	if ($ext === 'jpg' || $ext === 'jpe') {
		if (!function_exists('fractal_zip_image_pac_jpeg_semantics_ok_for_canonical_bundle')) {
			return null;
		}
		if (!fractal_zip_image_pac_jpeg_semantics_ok_for_canonical_bundle($diskBytes)) {
			return null;
		}
	}
	return fractal_zip_raster_canonical_try($relPath, $workBytes);
}

function fractal_zip_raster_canonical_bundle_inner_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_RASTER_CANONICAL_BUNDLE');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_raster_canonical_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_RASTER_CANONICAL');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/** @var list<string> */
function fractal_zip_raster_canonical_extensions(): array {
	return ['png', 'gif', 'webp', 'jpg', 'jpeg', 'jpe', 'bmp'];
}

/**
 * @return string|null Canonical blob or null if unsupported / decode failed / not a raster path
 */
function fractal_zip_raster_canonical_try(string $relPath, string $bytes): ?string {
	if (!fractal_zip_raster_canonical_enabled() || $bytes === '') {
		return null;
	}
	$ext = strtolower((string) pathinfo(str_replace('\\', '/', $relPath), PATHINFO_EXTENSION));
	if (!in_array($ext, fractal_zip_raster_canonical_extensions(), true)) {
		return null;
	}
	if (extension_loaded('imagick') && class_exists(Imagick::class)) {
		$out = fractal_zip_raster_canonical_imagick($bytes);
		if ($out !== null) {
			return $out;
		}
	}
	return fractal_zip_raster_canonical_gd($bytes, $ext);
}

function fractal_zip_raster_canonical_imagick(string $bytes): ?string {
	try {
		$im = new Imagick();
		$im->readImageBlob($bytes);
		if ($im->getNumberImages() > 1) {
			$co = $im->coalesceImages();
			$im->destroy();
			$im = $co;
		}
		@$im->setIteratorIndex(0);
		$im->setImageAlphaChannel(Imagick::ALPHACHANNEL_ACTIVATE);
		$w = $im->getImageWidth();
		$h = $im->getImageHeight();
		if ($w < 1 || $h < 1 || $w > 65535 || $h > 65535) {
			$im->destroy();
			return null;
		}
		$expected = $w * $h * 4;
		$pixChar = 1;
		if (defined('Imagick::PIXEL_CHAR')) {
			$pixChar = (int) constant('Imagick::PIXEL_CHAR');
		}
		$raw = $im->exportImagePixels(0, 0, $w, $h, 'RGBA', $pixChar);
		$im->destroy();
		if (!is_string($raw) || strlen($raw) !== $expected) {
			return null;
		}
		return fractal_zip_raster_canonical_pack($w, $h, $raw);
	} catch (Throwable) {
		return null;
	}
}

function fractal_zip_raster_canonical_gd(string $bytes, string $ext): ?string {
	if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) {
		return null;
	}
	$im = @imagecreatefromstring($bytes);
	if ($im === false) {
		return null;
	}
	if (function_exists('imagepalettetotruecolor')) {
		@imagepalettetotruecolor($im);
	}
	imagesavealpha($im, true);
	$w = imagesx($im);
	$h = imagesy($im);
	if ($w < 1 || $h < 1 || $w > 65535 || $h > 65535) {
		imagedestroy($im);
		return null;
	}
	$rgba = str_repeat("\x00", $w * $h * 4);
	$idx = 0;
	$tc = function_exists('imageistruecolor') && imageistruecolor($im);
	for ($y = 0; $y < $h; $y++) {
		for ($x = 0; $x < $w; $x++) {
			$c = imagecolorat($im, $x, $y);
			if ($tc) {
				$r = ($c >> 16) & 0xFF;
				$g = ($c >> 8) & 0xFF;
				$b = $c & 0xFF;
				$ai = ($c & 0x7F000000) >> 24;
				$a = $ai >= 127 ? 0 : (int) round(255 * (1.0 - $ai / 127.0));
				if ($a < 0) {
					$a = 0;
				}
				if ($a > 255) {
					$a = 255;
				}
			} else {
				$cols = imagecolorsforindex($im, $c);
				$r = (int) ($cols['red'] ?? 0);
				$g = (int) ($cols['green'] ?? 0);
				$b = (int) ($cols['blue'] ?? 0);
				$a = 255;
				if (isset($cols['alpha'])) {
					$a = $cols['alpha'] >= 127 ? 0 : (int) round(255 * (1.0 - (int) $cols['alpha'] / 127.0));
				}
			}
			$rgba[$idx] = chr($r);
			$rgba[$idx + 1] = chr($g);
			$rgba[$idx + 2] = chr($b);
			$rgba[$idx + 3] = chr($a);
			$idx += 4;
		}
	}
	imagedestroy($im);
	return fractal_zip_raster_canonical_pack($w, $h, $rgba);
}

function fractal_zip_raster_canonical_pack(int $w, int $h, string $rgba): string {
	return 'FZRC1' . pack('N', $w) . pack('N', $h) . $rgba;
}
