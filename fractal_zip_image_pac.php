<?php
declare(strict_types=1);

/**
 * Image “pac” (future FZ* inner bundle): lossless decode → canonical re-encode for smaller DEFLATE-friendly payloads,
 * analogous in spirit to FLACpac’s decode→FLAC. Chained from fractal_zip_literal_pac_preprocess_literal_for_bundle for raster extensions only.
 *
 * JPEG: only when jpegtran -copy all [ -progressive ] reproduces the file bytes (libjpeg “safe” subset), try
 * jpegtran … -optimize; accept only if smaller and djpeg PPM output matches original (lossless pixels — not bytewise vs
 * original file). FRACTAL_ZIP_IMAGEPAC_JPEG=0 skips JPEG. Benchmark verify hashes the corpus tree; any pac’d literal can
 * differ from pristine files — use --no-verify or disable pac for strict byte round-trip checks. Requires jpegtran + djpeg on PATH.
 * APNG (multi-frame PNG): skipped in Imagick to avoid wrong single-frame output; use ffmpeg path if enabled.
 * Animated GIF (Imagick): coalesceImages + optimizeImageLayers before blob write.
 *
 * Backends: Imagick (preferred), GD, else ffmpeg on PATH when allowed (see fractal_zip_image_pac_ffmpeg_allowed).
 * Set FRACTAL_ZIP_IMAGEPAC=0 to disable. FRACTAL_ZIP_IMAGEPAC_FFMPEG=0 never uses ffmpeg; =1 forces ffmpeg eligible when on PATH.
 * Unset IMAGEPAC_FFMPEG: ffmpeg is used only if neither Imagick nor usable GD is available (typical CLI PHP).
 * FRACTAL_ZIP_IMAGEPAC_MAX_BYTES: skip files larger than this (default **0** = no limit; was 40 MiB).
 */

/** @var array<string, true> */
const FRACTAL_ZIP_IMAGEPAC_RASTER_EXT = ['png' => true, 'gif' => true, 'webp' => true, 'jpg' => true, 'jpeg' => true, 'jpe' => true];

/**
 * Max raw file size (bytes) eligible for decode/re-encode; avoids OOM on huge rasters.
 */
function fractal_zip_image_pac_max_input_bytes(): int {
	$e = getenv('FRACTAL_ZIP_IMAGEPAC_MAX_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 0;
	}
	$v = (int) $e;
	return $v <= 0 ? 0 : $v;
}

/**
 * Apply lossless raster recompress before literal-bundle transform selection (FZB4 / FZCD “other” members).
 *
 * @param string $relPath bundle-relative path (used for extension only)
 */
function fractal_zip_image_pac_preprocess_literal_for_bundle(string $relPath, string $rawBytes): string {
	if (!fractal_zip_image_pac_enabled() || $rawBytes === '') {
		return $rawBytes;
	}
	$max = fractal_zip_image_pac_max_input_bytes();
	if ($max > 0 && strlen($rawBytes) > $max) {
		return $rawBytes;
	}
	$ext = strtolower((string) pathinfo($relPath, PATHINFO_EXTENSION));
	if (!isset(FRACTAL_ZIP_IMAGEPAC_RASTER_EXT[$ext])) {
		return $rawBytes;
	}
	$got = fractal_zip_image_pac_try_lossless_smaller($rawBytes, $ext);
	return $got !== null ? $got[0] : $rawBytes;
}

/** Reserved for future inner-bundle discriminator (align with FZCD flag style). */
const FRACTAL_ZIP_FZIMG_FLAG_CANONICAL_PNG = 1;
const FRACTAL_ZIP_FZIMG_FLAG_CANONICAL_GIF = 2;
const FRACTAL_ZIP_FZIMG_FLAG_CANONICAL_WEBP_LOSSLESS = 4;

function fractal_zip_image_pac_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_IMAGEPAC');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_image_pac_ffmpeg_tools_ok(): bool {
	static $ok = null;
	if ($ok !== null) {
		return $ok;
	}
	$p = shell_exec('command -v ffmpeg 2>/dev/null');
	$ok = is_string($p) && trim($p) !== '';
	return $ok;
}

/**
 * When unset: allow ffmpeg if there is no Imagick and no usable GD (decode+png).
 * FRACTAL_ZIP_IMAGEPAC_FFMPEG=1: allow ffmpeg whenever it is on PATH (still tried after Imagick/GD).
 * FRACTAL_ZIP_IMAGEPAC_FFMPEG=0: never use ffmpeg.
 */
function fractal_zip_image_pac_ffmpeg_allowed(): bool {
	$e = getenv('FRACTAL_ZIP_IMAGEPAC_FFMPEG');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
			return false;
		}
		if ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes') {
			return true;
		}
	}
	$hasImagick = extension_loaded('imagick') && class_exists(Imagick::class);
	return !$hasImagick && !fractal_zip_image_pac_has_usable_gd();
}

/** Same “usable GD” test as fractal_zip_image_pac_backend (keeps ffmpeg auto-fallback aligned). */
function fractal_zip_image_pac_has_usable_gd(): bool {
	return extension_loaded('gd') && function_exists('imagecreatefromstring') && function_exists('imagepng');
}

/** @return ''|'imagick'|'gd'|'ffmpeg' */
function fractal_zip_image_pac_backend(): string {
	static $b = null;
	if ($b !== null) {
		return $b;
	}
	if (extension_loaded('imagick') && class_exists(Imagick::class)) {
		return $b = 'imagick';
	}
	if (fractal_zip_image_pac_has_usable_gd()) {
		return $b = 'gd';
	}
	if (fractal_zip_image_pac_ffmpeg_allowed() && fractal_zip_image_pac_ffmpeg_tools_ok()) {
		return $b = 'ffmpeg';
	}
	return $b = '';
}

/**
 * Lossless recompress raster bytes if a backend can decode and produce strictly smaller output.
 *
 * @return array{0: string, 1: int}|null [new_bytes, bytes_saved]
 */
function fractal_zip_image_pac_try_lossless_smaller(string $bytes, string $extOrMime): ?array {
	if (!fractal_zip_image_pac_enabled() || $bytes === '') {
		return null;
	}
	$ext = strtolower(ltrim($extOrMime, '.'));
	if (str_contains($ext, '/')) {
		$ext = match ($ext) {
			'image/png' => 'png',
			'image/gif' => 'gif',
			'image/webp' => 'webp',
			'image/jpeg', 'image/jpg' => 'jpg',
			default => $ext,
		};
	}
	if ($ext === 'jpeg') {
		$ext = 'jpg';
	}
	if ($ext === 'jpg' || $ext === 'jpe') {
		return fractal_zip_image_pac_try_jpeg_lossless_smaller($bytes);
	}

	$backend = fractal_zip_image_pac_backend();
	if ($backend === '') {
		return null;
	}

	$out = match ($backend) {
		'imagick' => fractal_zip_image_pac_reencode_imagick($bytes, $ext),
		'gd' => fractal_zip_image_pac_reencode_gd($bytes, $ext),
		'ffmpeg' => fractal_zip_image_pac_reencode_ffmpeg($bytes, $ext),
		default => null,
	};
	if ($out === null || $out === $bytes) {
		return null;
	}
	$saved = strlen($bytes) - strlen($out);
	if ($saved <= 0) {
		return null;
	}
	return [$out, $saved];
}

/**
 * @param 'png'|'gif'|'webp' $ext
 */
function fractal_zip_image_pac_reencode_imagick(string $bytes, string $ext): ?string {
	try {
		$im = new Imagick();
		$im->readImageBlob($bytes);
		if ($ext === 'png' && $im->getNumberImages() > 1) {
			$im->destroy();
			return null;
		}
		if ($ext === 'gif' && $im->getNumberImages() > 1) {
			$coalesced = $im->coalesceImages();
			$im->destroy();
			$im = $coalesced;
			$opt = $im->optimizeImageLayers();
			if ($opt instanceof Imagick) {
				$im->destroy();
				$im = $opt;
			}
		}
		switch ($ext) {
			case 'png':
				$im->setImageFormat('png');
				$im->setOption('png:compression-level', '9');
				$im->setOption('png:compression-filter', '5');
				break;
			case 'gif':
				$im->setImageFormat('gif');
				break;
			case 'webp':
				$im->setImageFormat('webp');
				$im->setOption('webp:lossless', 'true');
				$im->setOption('webp:method', '6');
				break;
			default:
				$im->destroy();
				return null;
		}
		foreach ($im as $frame) {
			$frame->stripImage();
		}
		$multi = $im->getNumberImages() > 1;
		$out = $multi ? $im->getImagesBlob() : $im->getImageBlob();
		$im->destroy();
		return is_string($out) && $out !== '' ? $out : null;
	} catch (Throwable) {
		return null;
	}
}

/**
 * Lossless-ish recompress via ffmpeg (temp files). Keeps animation for GIF.
 *
 * @param 'png'|'gif'|'webp' $ext
 */
function fractal_zip_image_pac_reencode_ffmpeg(string $bytes, string $ext): ?string {
	if (!fractal_zip_image_pac_ffmpeg_tools_ok()) {
		return null;
	}
	if ($ext === 'png' && str_contains($bytes, 'acTL')) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	$in = $td . DIRECTORY_SEPARATOR . 'fzimg_' . $id . '_in.' . $ext;
	$out = $td . DIRECTORY_SEPARATOR . 'fzimg_' . $id . '_out.' . $ext;
	if (file_put_contents($in, $bytes) === false) {
		return null;
	}
	$inQ = escapeshellarg($in);
	$outQ = escapeshellarg($out);
	$cmd = match ($ext) {
		'png' => 'ffmpeg -y -hide_banner -loglevel error -i ' . $inQ . ' -frames:v 1 -c:v png -pred mixed ' . $outQ,
		'gif' => 'ffmpeg -y -hide_banner -loglevel error -i ' . $inQ . ' -c:v gif ' . $outQ,
		'webp' => 'ffmpeg -y -hide_banner -loglevel error -i ' . $inQ . ' -c:v libwebp -lossless 1 ' . $outQ,
		default => '',
	};
	if ($cmd === '') {
		@unlink($in);
		return null;
	}
	$ret = 1;
	$null = [];
	@exec($cmd . ' 2>/dev/null', $null, $ret);
	$blob = null;
	if ($ret === 0 && is_file($out)) {
		$r = file_get_contents($out);
		$blob = is_string($r) && $r !== '' ? $r : null;
	}
	@unlink($in);
	@unlink($out);
	return $blob;
}

/**
 * @param 'png'|'gif'|'webp' $ext
 */
function fractal_zip_image_pac_reencode_gd(string $bytes, string $ext): ?string {
	$im = @imagecreatefromstring($bytes);
	if ($im === false) {
		return null;
	}
	try {
		switch ($ext) {
			case 'png':
				ob_start();
				$filters = defined('PNG_ALL_FILTERS') ? PNG_ALL_FILTERS : 0;
				imagepng($im, null, 9, $filters);
				$out = ob_get_clean();
				break;
			case 'gif':
				if (!function_exists('imagegif')) {
					return null;
				}
				ob_start();
				imagegif($im);
				$out = ob_get_clean();
				break;
			case 'webp':
				if (!function_exists('imagewebp')) {
					return null;
				}
				ob_start();
				imagewebp($im, null, 100);
				$out = ob_get_clean();
				break;
			default:
				return null;
		}
	} finally {
		imagedestroy($im);
	}
	return is_string($out) && $out !== '' ? $out : null;
}

function fractal_zip_image_pac_jpegtran_tools_ok(): bool {
	static $ok = null;
	if ($ok !== null) {
		return $ok;
	}
	$j = shell_exec('command -v jpegtran 2>/dev/null');
	$d = shell_exec('command -v djpeg 2>/dev/null');
	$ok = is_string($j) && trim($j) !== '' && is_string($d) && trim($d) !== '';
	return $ok;
}

/** True if first $maxPrefix bytes contain progressive SOF2 (0xFF 0xC2). */
function fractal_zip_image_pac_jpeg_bytes_look_progressive(string $bytes, int $maxPrefix = 65536): bool {
	$head = strlen($bytes) <= $maxPrefix ? $bytes : substr($bytes, 0, $maxPrefix);
	return str_contains($head, "\xFF\xC2");
}

/**
 * Run jpegtran on blob; returns output bytes or null.
 *
 * @param string $extraSwitches space-separated jpegtran options after "-copy all", e.g. "-progressive -optimize" or ""
 */
function fractal_zip_image_pac_jpegtran_pipe(string $bytes, string $extraSwitches): ?string {
	if (!fractal_zip_image_pac_jpegtran_tools_ok()) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	$in = $td . DIRECTORY_SEPARATOR . 'fzjpg_' . $id . '_in.jpg';
	$out = $td . DIRECTORY_SEPARATOR . 'fzjpg_' . $id . '_out.jpg';
	if (file_put_contents($in, $bytes) === false) {
		return null;
	}
	$ex = trim($extraSwitches);
	$mid = $ex === '' ? '' : (' ' . $ex);
	$cmd = 'jpegtran -copy all' . $mid . ' -outfile ' . escapeshellarg($out) . ' ' . escapeshellarg($in) . ' 2>/dev/null';
	$ret = 1;
	$null = [];
	@exec($cmd, $null, $ret);
	$blob = null;
	if ($ret === 0 && is_file($out)) {
		$r = file_get_contents($out);
		$blob = is_string($r) && $r !== '' ? $r : null;
	}
	@unlink($in);
	@unlink($out);
	return $blob;
}

function fractal_zip_image_pac_djpeg_to_ppm(string $jpegBytes): ?string {
	if (!fractal_zip_image_pac_jpegtran_tools_ok()) {
		return null;
	}
	$td = sys_get_temp_dir();
	$id = bin2hex(random_bytes(8));
	$jin = $td . DIRECTORY_SEPARATOR . 'fzjpg_' . $id . '_djin.jpg';
	$pout = $td . DIRECTORY_SEPARATOR . 'fzjpg_' . $id . '.ppm';
	if (file_put_contents($jin, $jpegBytes) === false) {
		return null;
	}
	$ret = 1;
	$null = [];
	@exec('djpeg ' . escapeshellarg($jin) . ' > ' . escapeshellarg($pout) . ' 2>/dev/null', $null, $ret);
	@unlink($jin);
	if ($ret !== 0 || !is_file($pout)) {
		@unlink($pout);
		return null;
	}
	$ppm = file_get_contents($pout);
	@unlink($pout);
	return is_string($ppm) && $ppm !== '' ? $ppm : null;
}

function fractal_zip_image_pac_jpeg_pixels_equal(string $jpegA, string $jpegB): bool {
	$p1 = fractal_zip_image_pac_djpeg_to_ppm($jpegA);
	$p2 = fractal_zip_image_pac_djpeg_to_ppm($jpegB);
	return $p1 !== null && $p2 !== null && $p1 === $p2;
}

/**
 * JPEG may join raster canonical / FZBD dedup only if djpeg pixels match jpegtran -copy all output (libjpeg-safe subset).
 * If tools are missing, IMAGEPAC off, or JPEG branch off, returns false — keep on-disk JPEG bytes in the literal bundle.
 */
function fractal_zip_image_pac_jpeg_semantics_ok_for_canonical_bundle(string $bytes): bool {
	if ($bytes === '' || !fractal_zip_image_pac_enabled()) {
		return false;
	}
	if (!fractal_zip_image_pac_jpeg_branch_enabled() || !fractal_zip_image_pac_jpegtran_tools_ok()) {
		return false;
	}
	$prog = fractal_zip_image_pac_jpeg_bytes_look_progressive($bytes);
	$exCopy = $prog ? '-progressive' : '';
	$round = fractal_zip_image_pac_jpegtran_pipe($bytes, $exCopy);
	if ($round === null) {
		return false;
	}
	return fractal_zip_image_pac_jpeg_pixels_equal($bytes, $round);
}

/**
 * JPEG: require jpegtran -copy all round-trip === input; then try -optimize, keep only if smaller and same pixels (djpeg).
 *
 * @return array{0: string, 1: int}|null
 */
function fractal_zip_image_pac_jpeg_branch_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_IMAGEPAC_JPEG');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_image_pac_try_jpeg_lossless_smaller(string $bytes): ?array {
	if (!fractal_zip_image_pac_enabled() || $bytes === '') {
		return null;
	}
	if (!fractal_zip_image_pac_jpeg_branch_enabled()) {
		return null;
	}
	if (!fractal_zip_image_pac_jpegtran_tools_ok()) {
		return null;
	}
	$prog = fractal_zip_image_pac_jpeg_bytes_look_progressive($bytes);
	$exCopy = $prog ? '-progressive' : '';
	$canonical = fractal_zip_image_pac_jpegtran_pipe($bytes, $exCopy);
	if ($canonical === null || $canonical !== $bytes) {
		return null;
	}
	$exOpt = $prog ? '-progressive -optimize' : '-optimize';
	$opt = fractal_zip_image_pac_jpegtran_pipe($bytes, $exOpt);
	if ($opt === null || $opt === $bytes) {
		return null;
	}
	$n = strlen($bytes);
	$m = strlen($opt);
	if ($m >= $n) {
		return null;
	}
	if (!fractal_zip_image_pac_jpeg_pixels_equal($bytes, $opt)) {
		return null;
	}
	return [$opt, $n - $m];
}
