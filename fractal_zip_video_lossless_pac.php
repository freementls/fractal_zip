<?php
declare(strict_types=1);
/**
 * Lossless video PAC (Lagarith / HuffYUV / Ut Video / FFV1).
 *
 * Default **off** (bit-exact container bytes). Set **FRACTAL_ZIP_VIDEOPAC=1** to allow
 * decode→raw (or decode→FFV1 rewrap) when ffmpeg/ffprobe are available.
 *
 * Lagarith is decode-only in stock ffmpeg; encode peers (ffv1/huffyuv/utvideo) are used
 * for synthetic corpora and for optional storage rewrap after decode.
 *
 * Wire format sketch (FZVL v1) — written when a rewrap beats opaque store:
 *   magic "FZVL" + u8 ver=1 + u8 flags + codec_id u8 + u16 w + u16 h + u8 pix_fmt_id
 *   + u32 fps_num + u32 fps_den + u32 frame_count + u64 raw_bytes + payload…
 * Payload is either raw planar frames (flag RAW) or an FFV1 AVI/MKV blob (flag FFV1).
 */

const FRACTAL_ZIP_FZVL_MAGIC = 'FZVL';
const FRACTAL_ZIP_FZVL_VER = 1;
const FRACTAL_ZIP_FZVL_FLAG_RAW = 1;
const FRACTAL_ZIP_FZVL_FLAG_FFV1 = 2;

/** @var array<string,int> */
const FRACTAL_ZIP_VIDEO_LOSSLESS_CODEC_IDS = array(
	'lagarith' => 1,
	'huffyuv' => 2,
	'ffvhuff' => 3,
	'utvideo' => 4,
	'ffv1' => 5,
);

function fractal_zip_video_lossless_pac_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_VIDEOPAC');
	if ($e === false) {
		return false;
	}
	$t = strtolower(trim((string) $e));
	return in_array($t, array('1', 'on', 'true', 'yes', 'auto'), true);
}

function fractal_zip_video_lossless_pac_ffprobe_bin(): ?string {
	if (function_exists('fractal_zip_flac_pac_ffprobe_bin')) {
		return fractal_zip_flac_pac_ffprobe_bin();
	}
	$e = getenv('FRACTAL_ZIP_FFPROBE');
	if ($e !== false && trim((string) $e) !== '' && is_executable(trim((string) $e))) {
		return trim((string) $e);
	}
	$line = shell_exec('command -v ffprobe 2>/dev/null');
	$p = is_string($line) ? trim($line) : '';
	return ($p !== '' && is_executable($p)) ? $p : null;
}

function fractal_zip_video_lossless_pac_ffmpeg_bin(): ?string {
	if (function_exists('fractal_zip_flac_pac_ffmpeg_bin')) {
		return fractal_zip_flac_pac_ffmpeg_bin();
	}
	$e = getenv('FRACTAL_ZIP_FFMPEG');
	if ($e !== false && trim((string) $e) !== '' && is_executable(trim((string) $e))) {
		return trim((string) $e);
	}
	$line = shell_exec('command -v ffmpeg 2>/dev/null');
	$p = is_string($line) ? trim($line) : '';
	return ($p !== '' && is_executable($p)) ? $p : null;
}

function fractal_zip_video_lossless_pac_tools_ok(): bool {
	static $ok = null;
	if ($ok !== null) {
		return $ok;
	}
	$ok = fractal_zip_video_lossless_pac_ffprobe_bin() !== null
		&& fractal_zip_video_lossless_pac_ffmpeg_bin() !== null;
	return $ok;
}

/**
 * @return list<string>
 */
function fractal_zip_video_lossless_pac_codec_names(): array {
	return array_keys(FRACTAL_ZIP_VIDEO_LOSSLESS_CODEC_IDS);
}

function fractal_zip_video_lossless_pac_is_supported_codec(?string $codec): bool {
	if ($codec === null || $codec === '') {
		return false;
	}
	return isset(FRACTAL_ZIP_VIDEO_LOSSLESS_CODEC_IDS[strtolower($codec)]);
}

/**
 * @return array{
 *   codec: string,
 *   codec_id: int,
 *   width: int,
 *   height: int,
 *   pix_fmt: string,
 *   fps_num: int,
 *   fps_den: int,
 *   nb_frames: int
 * }|null
 */
function fractal_zip_video_lossless_pac_probe(string $absPath): ?array {
	if (!is_file($absPath) || !fractal_zip_video_lossless_pac_tools_ok()) {
		return null;
	}
	$ffp = fractal_zip_video_lossless_pac_ffprobe_bin();
	if ($ffp === null) {
		return null;
	}
	$cmd = escapeshellarg($ffp)
		. ' -v error -select_streams v:0'
		. ' -show_entries stream=codec_name,width,height,pix_fmt,r_frame_rate,nb_frames,avg_frame_rate'
		. ' -of json ' . escapeshellarg($absPath) . ' 2>/dev/null';
	$json = shell_exec($cmd);
	if (!is_string($json) || $json === '') {
		return null;
	}
	$data = json_decode($json, true);
	if (!is_array($data) || !isset($data['streams'][0]) || !is_array($data['streams'][0])) {
		return null;
	}
	$s = $data['streams'][0];
	$codec = strtolower((string) ($s['codec_name'] ?? ''));
	if (!fractal_zip_video_lossless_pac_is_supported_codec($codec)) {
		return null;
	}
	$w = (int) ($s['width'] ?? 0);
	$h = (int) ($s['height'] ?? 0);
	if ($w < 1 || $h < 1 || $w > 16384 || $h > 16384) {
		return null;
	}
	$rate = (string) ($s['r_frame_rate'] ?? ($s['avg_frame_rate'] ?? '0/1'));
	$fpsNum = 0;
	$fpsDen = 1;
	if (preg_match('/^(\d+)\s*\/\s*(\d+)$/', $rate, $m) === 1) {
		$fpsNum = (int) $m[1];
		$fpsDen = max(1, (int) $m[2]);
	}
	$nb = isset($s['nb_frames']) ? (int) $s['nb_frames'] : 0;
	return array(
		'codec' => $codec,
		'codec_id' => (int) FRACTAL_ZIP_VIDEO_LOSSLESS_CODEC_IDS[$codec],
		'width' => $w,
		'height' => $h,
		'pix_fmt' => (string) ($s['pix_fmt'] ?? ''),
		'fps_num' => $fpsNum,
		'fps_den' => $fpsDen,
		'nb_frames' => max(0, $nb),
	);
}

/**
 * Decode lossless video to raw packed frames (ffmpeg -f rawvideo).
 * Uses the stream's native pix_fmt when possible.
 */
function fractal_zip_video_lossless_pac_decode_to_raw(
	string $absPath,
	string $outRawPath,
	?array $meta = null
): bool {
	if (!fractal_zip_video_lossless_pac_tools_ok()) {
		return false;
	}
	$ffm = fractal_zip_video_lossless_pac_ffmpeg_bin();
	if ($ffm === null) {
		return false;
	}
	if ($meta === null) {
		$meta = fractal_zip_video_lossless_pac_probe($absPath);
	}
	if ($meta === null) {
		return false;
	}
	$pix = $meta['pix_fmt'] !== '' ? $meta['pix_fmt'] : 'yuv420p';
	$cmd = escapeshellarg($ffm) . ' -nostdin -hide_banner -loglevel error -y -i ' . escapeshellarg($absPath)
		. ' -map 0:v:0 -an -c:v rawvideo -pix_fmt ' . escapeshellarg($pix)
		. ' -f rawvideo ' . escapeshellarg($outRawPath) . ' 2>/dev/null';
	$code = 0;
	system($cmd, $code);
	return $code === 0 && is_file($outRawPath) && filesize($outRawPath) > 0;
}

/**
 * Rewrap decoded (or source) video as FFV1 in an AVI container (lossless, widely encodable).
 */
function fractal_zip_video_lossless_pac_rewrap_ffv1(string $absPath, string $outAviPath): bool {
	if (!fractal_zip_video_lossless_pac_tools_ok()) {
		return false;
	}
	$ffm = fractal_zip_video_lossless_pac_ffmpeg_bin();
	if ($ffm === null) {
		return false;
	}
	$cmd = escapeshellarg($ffm) . ' -nostdin -hide_banner -loglevel error -y -i ' . escapeshellarg($absPath)
		. ' -map 0:v:0 -an -c:v ffv1 -level 3 -g 1 '
		. escapeshellarg($outAviPath) . ' 2>/dev/null';
	$code = 0;
	system($cmd, $code);
	return $code === 0 && is_file($outAviPath) && filesize($outAviPath) > 0;
}

/**
 * Build FZVL header + payload bytes. Returns null on failure.
 *
 * @param array<string,mixed> $meta from probe()
 */
function fractal_zip_video_lossless_pac_pack_fzvl(array $meta, string $payload, int $flags): ?string {
	if (($flags & (FRACTAL_ZIP_FZVL_FLAG_RAW | FRACTAL_ZIP_FZVL_FLAG_FFV1)) === 0) {
		return null;
	}
	$w = (int) ($meta['width'] ?? 0);
	$h = (int) ($meta['height'] ?? 0);
	$codecId = (int) ($meta['codec_id'] ?? 0);
	if ($w < 1 || $h < 1 || $codecId < 1) {
		return null;
	}
	$pixId = 0; // reserved; pix_fmt kept in side table later if needed
	$fpsNum = (int) ($meta['fps_num'] ?? 0);
	$fpsDen = max(1, (int) ($meta['fps_den'] ?? 1));
	$frames = (int) ($meta['nb_frames'] ?? 0);
	$rawLen = strlen($payload);
	$hdr = FRACTAL_ZIP_FZVL_MAGIC
		. chr(FRACTAL_ZIP_FZVL_VER)
		. chr($flags & 0xff)
		. chr($codecId & 0xff)
		. pack('n', $w)
		. pack('n', $h)
		. chr($pixId)
		. pack('N', $fpsNum)
		. pack('N', $fpsDen)
		. pack('N', $frames)
		. pack('J', $rawLen);
	return $hdr . $payload;
}

/**
 * @return array{meta: array<string,mixed>, flags: int, payload: string}|null
 */
function fractal_zip_video_lossless_pac_unpack_fzvl(string $blob): ?array {
	if (strlen($blob) < 32 || substr($blob, 0, 4) !== FRACTAL_ZIP_FZVL_MAGIC) {
		return null;
	}
	$ver = ord($blob[4]);
	if ($ver !== FRACTAL_ZIP_FZVL_VER) {
		return null;
	}
	$flags = ord($blob[5]);
	$codecId = ord($blob[6]);
	$w = unpack('n', substr($blob, 7, 2))[1] ?? 0;
	$h = unpack('n', substr($blob, 9, 2))[1] ?? 0;
	$fpsNum = unpack('N', substr($blob, 12, 4))[1] ?? 0;
	$fpsDen = unpack('N', substr($blob, 16, 4))[1] ?? 1;
	$frames = unpack('N', substr($blob, 20, 4))[1] ?? 0;
	$rawLen = unpack('J', substr($blob, 24, 8))[1] ?? 0;
	$payload = substr($blob, 32);
	if ($rawLen !== strlen($payload) || $w < 1 || $h < 1) {
		return null;
	}
	$codec = array_search($codecId, FRACTAL_ZIP_VIDEO_LOSSLESS_CODEC_IDS, true);
	if ($codec === false) {
		$codec = 'unknown';
	}
	return array(
		'meta' => array(
			'codec' => (string) $codec,
			'codec_id' => $codecId,
			'width' => (int) $w,
			'height' => (int) $h,
			'pix_fmt' => '',
			'fps_num' => (int) $fpsNum,
			'fps_den' => max(1, (int) $fpsDen),
			'nb_frames' => (int) $frames,
		),
		'flags' => $flags,
		'payload' => $payload,
	);
}

/**
 * Expand FZVL payload to playable container bytes (FFV1 AVI) for extract.
 * Returns null if $bytes is not FZVL or expansion fails (caller keeps opaque bytes).
 */
function fractal_zip_video_lossless_pac_expand_member_bytes(string $bytes): ?string {
	if (strlen($bytes) < 32 || substr($bytes, 0, 4) !== FRACTAL_ZIP_FZVL_MAGIC) {
		return null;
	}
	$u = fractal_zip_video_lossless_pac_unpack_fzvl($bytes);
	if ($u === null) {
		return null;
	}
	$flags = (int) $u['flags'];
	$payload = (string) $u['payload'];
	if (($flags & FRACTAL_ZIP_FZVL_FLAG_FFV1) !== 0) {
		return $payload;
	}
	if (($flags & FRACTAL_ZIP_FZVL_FLAG_RAW) === 0 || !fractal_zip_video_lossless_pac_tools_ok()) {
		return null;
	}
	$meta = $u['meta'];
	$ffm = fractal_zip_video_lossless_pac_ffmpeg_bin();
	if ($ffm === null) {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzvlex_' . getmypid() . '_' . bin2hex(random_bytes(3));
	$raw = $tmp . '.raw';
	$avi = $tmp . '.avi';
	$pix = ($meta['pix_fmt'] ?? '') !== '' ? (string) $meta['pix_fmt'] : 'yuv420p';
	$w = (int) $meta['width'];
	$h = (int) $meta['height'];
	$fpsNum = (int) ($meta['fps_num'] ?? 0);
	$fpsDen = max(1, (int) ($meta['fps_den'] ?? 1));
	$rate = ($fpsNum > 0) ? ($fpsNum . '/' . $fpsDen) : '25/1';
	try {
		if (@file_put_contents($raw, $payload) === false) {
			return null;
		}
		$cmd = escapeshellarg($ffm) . ' -nostdin -hide_banner -loglevel error -y'
			. ' -f rawvideo -pix_fmt ' . escapeshellarg($pix)
			. ' -s:v ' . escapeshellarg($w . 'x' . $h)
			. ' -r ' . escapeshellarg($rate)
			. ' -i ' . escapeshellarg($raw)
			. ' -c:v ffv1 -level 3 -g 1 ' . escapeshellarg($avi) . ' 2>/dev/null';
		$code = 0;
		system($cmd, $code);
		if ($code !== 0 || !is_file($avi)) {
			return null;
		}
		$out = (string) file_get_contents($avi);
		return ($out !== '') ? $out : null;
	} finally {
		@unlink($raw);
		@unlink($avi);
	}
}

/**
 * Choose a smaller representation for a lossless-video member when VIDEOPAC is on.
 * Returns null to keep the original opaque bytes.
 *
 * @return array{kind: string, bytes: string, meta: array<string,mixed>}|null
 */
function fractal_zip_video_lossless_pac_try_shrink_member(string $absPath, string $originalBytes): ?array {
	if (!fractal_zip_video_lossless_pac_enabled() || !fractal_zip_video_lossless_pac_tools_ok()) {
		return null;
	}
	$meta = fractal_zip_video_lossless_pac_probe($absPath);
	if ($meta === null) {
		return null;
	}
	$origLen = strlen($originalBytes);
	if ($origLen < 64) {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzvl_' . getmypid() . '_' . bin2hex(random_bytes(4));
	$ffv1 = $tmp . '.ffv1.avi';
	$raw = $tmp . '.raw';
	$best = null;
	$bestLen = $origLen;
	try {
		if (fractal_zip_video_lossless_pac_rewrap_ffv1($absPath, $ffv1)) {
			$payload = (string) file_get_contents($ffv1);
			$packed = fractal_zip_video_lossless_pac_pack_fzvl($meta, $payload, FRACTAL_ZIP_FZVL_FLAG_FFV1);
			if (is_string($packed) && strlen($packed) < $bestLen) {
				$best = array('kind' => 'fzvl_ffv1', 'bytes' => $packed, 'meta' => $meta);
				$bestLen = strlen($packed);
			}
		}
		// Raw only when it clearly helps (small clips); huge YUV dumps rarely beat FFV1.
		if ($origLen <= 8 * 1024 * 1024 && fractal_zip_video_lossless_pac_decode_to_raw($absPath, $raw, $meta)) {
			$payload = (string) file_get_contents($raw);
			$packed = fractal_zip_video_lossless_pac_pack_fzvl($meta, $payload, FRACTAL_ZIP_FZVL_FLAG_RAW);
			if (is_string($packed) && strlen($packed) < $bestLen) {
				$best = array('kind' => 'fzvl_raw', 'bytes' => $packed, 'meta' => $meta);
				$bestLen = strlen($packed);
			}
		}
	} finally {
		@unlink($ffv1);
		@unlink($raw);
	}
	return $best;
}
