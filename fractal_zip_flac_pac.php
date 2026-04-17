<?php
declare(strict_types=1);

/** FZCD flags: merged album PCM is stored as one FLAC stream (same codec as sources; best for PCM). */
const FRACTAL_ZIP_FZCD_FLAG_MERGED_FLAC = 4;
/** Legacy: merged PCM was gzip-9 before outer deflate. */
const FRACTAL_ZIP_FZCD_FLAG_MERGED_GZIP = 2;
/** Merged raw PCM stored as one fractal_zip container payload (FZC*), then per-track FLAC on extract (legacy single blob). */
const FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_PCM = 8;
/** Merged PCM as multiple fractal-encoded chunks (RAM-safe; preferred when FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL=1). */
const FRACTAL_ZIP_FZCD_FLAG_MERGED_FRACTAL_CHUNKED = 16;

/** Synthetic member path for merged PCM inside the nested fractal container (must match encode/decode). */
const FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER = '__fzcd_merged.pcm';

/** FZCD v1: merged-fractal chunks use `clen, ilen, [preId], inner`; preId present when this flag is set in fzcdFlags[5]. */
const FRACTAL_ZIP_FZCD_FLAG_CHUNK_PCM_PRETRANSFORM = 32;

/** Reversible PCM pre-transform ids (applied before fractal `zip()` on each chunk; decode inverses after unzip). */
const FRACTAL_ZIP_PCM_PRE_NONE = 0;
/** Per-channel first-order temporal delta (frame 0 holds raw samples). */
const FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA = 1;
/** Stereo s16le only: L unchanged, R' = R − L with int16 wrap (reversible). */
const FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT16 = 2;
/** Stereo s16le / s32le: swap L and R within each frame (reversible). */
const FRACTAL_ZIP_PCM_PRE_SWAP_STEREO_CHANNELS = 3;
/** Stereo s16le / s32le: planar layout L₀…Lₙ₋₁ R₀…Rₙ₋₁ (reversible). */
const FRACTAL_ZIP_PCM_PRE_PLANAR_STEREO = 4;
/** Whole buffer: b₀ raw, bᵢ = (bᵢ − bᵢ₋₁) mod 256 (reversible; matches literal mode-1 spirit). */
const FRACTAL_ZIP_PCM_PRE_BYTE_FIRST_DIFF = 5;
/** Whole buffer: XOR every byte with 0xFF (reversible). */
const FRACTAL_ZIP_PCM_PRE_INVERT_BYTES = 6;
/** Whole buffer: XOR every byte with 0x80 (reversible; toggles PCM sign bit in LE high bytes). */
const FRACTAL_ZIP_PCM_PRE_XOR_BYTE_0x80 = 7;
/** Whole buffer: swap high/low nibble in each byte (reversible). */
const FRACTAL_ZIP_PCM_PRE_NIBBLE_SWAP_BYTES = 8;
/** Stereo s32le only: L unchanged, R' = R − L with int32 wrap (reversible). */
const FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT32 = 9;
/** s16le: toggle sign bit of every sample (uint16 ^ 0x8000 → signed; reversible). */
const FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S16 = 10;
/** s32le: toggle sign bit of every sample (reversible). */
const FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S32 = 11;
/** Per-channel second-order temporal: d₀=s₀, d₁=s₁−s₀, dₖ=sₖ−2sₖ₋₁+sₖ₋₂ (int ring; reversible). */
const FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA2 = 12;
/** Per frame: ch0 raw; ch j>0 stores sⱼ−sⱼ₋₁ (same fmt ring; reversible). */
const FRACTAL_ZIP_PCM_PRE_INTRAFRAME_CH_DELTA = 13;
/** s16le / s32le: reverse bytes within each sample (LE↔BE per sample; involution). */
const FRACTAL_ZIP_PCM_PRE_SAMPLE_ENDIAN_SWAP = 14;

/**
 * Transmedia packing: merge decoded PCM from multiple compatible FLACs inside an FZCD inner bundle,
 * re-encode the concatenation as one FLAC (cross-track redundancy), then outer raw deflate. See write_fzcd_bundle_if_applicable.
 *
 * FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL: unset = **auto** (always try chunked fractal when FZCD merge runs; write_fzcd_bundle_if_applicable
 * keeps it only if the fractal payload is smaller than one merged-FLAC encode of the same PCM; per-chunk fractal must shrink raw PCM).
 * `1`/`0` force on/off. Chunked fractal streams from disk.
 * Tune FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_CHUNK_BYTES, FRACTAL_ZIP_FZCD_MERGED_FRACTAL_INLINE_BYTES, FRACTAL_ZIP_FZCD_WORKER_MEMORY,
 * FRACTAL_ZIP_FZCD_WORKER_IMPROVEMENT, FRACTAL_ZIP_FZCD_WORKER_SEGMENT_LENGTH, FRACTAL_ZIP_FZCD_WORKER_TOP_K / _GATE_MULT (subprocess reads these when instance tuning is null).
 * Legacy single-blob mode (flag 8) is decode-only for old archives.
 *
 * Future formats: align with sibling ../file_types/types.php ("compressed" category). Per-file stream recompress: fractal_zip_literal_pac.php + fractal_zip_literal_pac_registry.php. Lossy AAC/OGG/MP3 not hooked.
 *
 * Requires ffmpeg + ffprobe on PATH (or set FRACTAL_ZIP_FFMPEG / FRACTAL_ZIP_FFPROBE to full paths for shared hosts).
 *
 * **Policy (what “lossless” means here):**
 * - **Lossless sources (FLAC, PNG, …):** target **semantic identity** — same decoded information (e.g. PCM samples) and the
 *   same **catalogued** metadata that affects meaning (tags, rate, channels, bits-per-sample, etc.), not necessarily the same
 *   on-disk container bytes after re-encode or PAC. FZCD transmedia is in this bucket.
 * - **Strict container bytes:** keep original member bytes end-to-end (no merged PCM path); unset `FRACTAL_ZIP_FLACPAC` is the
 *   safe default when you need bit-identical `.flac` files or SHA1 tree checks without a semantic verifier.
 * - **Already-lossy sources (JPEG, …):** separate modes may allow controlled **visual / container** semantic differences
 *   (see image PAC and format-specific docs).
 *
 * **Default (unset `FRACTAL_ZIP_FLACPAC`): off** — favors bit-identical round-trip for FLAC members (no FZCD merge). Set
 * **`FRACTAL_ZIP_FLACPAC=1`** (`true` / `yes` / `on` / `auto`) to allow FZCD merge when ffmpeg/ffprobe are available.
 * `FRACTAL_ZIP_FLACPAC=0` / `off` / `false` / `no` keeps it disabled. `FRACTAL_ZIP_FLACPAC_FLAC_LEVEL=0–12` controls re-encode
 * level when merge is on.
 *
 * **Merged PCM chunk pipeline:** `fractal_zip_fzcd_encode_merged_pcm_chunk_with_pre` optionally applies a **reversible**
 * **pre-transform** (per `pcm_fmt` / channel count), then `fractal_zip_fzcd_encode_merged_pcm_chunk_raw` runs fractal `zip()` on
 * that domain. Wire format: when **`FRACTAL_ZIP_FLACPAC_PCM_PRETRANSFORM`** is unset or non-off, **`FRACTAL_ZIP_FZCD_FLAG_CHUNK_PCM_PRETRANSFORM`**
 * is set and each chunk is `clen, ilen, preId (u8), inner`. Pre ids: **0** identity, **1** per-channel temporal delta (s16/s32),
 * **2** stereo s16 side `R−L`, **3** stereo channel swap (s16/s32), **4** stereo planar (L block ∥ R block), **5** byte first-difference,
 * **6** invert all bytes (`^ 0xFF`), **7** XOR `0x80` per byte, **8** nibble-swap per byte, **9** stereo s32 side `R−L`,
 * **10** / **11** toggle sample sign bit (s16 / s32), **12** second-order temporal delta, **13** intra-frame channel deltas,
 * **14** per-sample endian byte swap. Gzip **ranking** uses **`fractal_zip_pcm_pre_gzip_rank_level()`** (default **5**) on the full chunk.
 * Fractal stage merges **anchor** presets (temporal / delta2 / byte-diff / stereo helpers) with gzip-ranked ids (see caps below).
 * Disable pre-stage only: **`FRACTAL_ZIP_FLACPAC_PCM_PRETRANSFORM=0`**. Further ideas:
 * mode-17 literal chains with a `.pcm` policy, motif dictionaries; **benchmark gap:** FLAC semantic verify vs SHA1.
 */

/** PCM layout after decode: s16le for ≤16-bit source, s32le for deeper (ffmpeg output). */
const FRACTAL_ZIP_FPCM_FMT_S16LE = 1;
const FRACTAL_ZIP_FPCM_FMT_S32LE = 2;

function fractal_zip_flac_pac_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_FLACPAC');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
		return false;
	}
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes' || $v === 'auto';
}

function fractal_zip_flac_pac_compression_level(): int {
	$e = getenv('FRACTAL_ZIP_FLACPAC_FLAC_LEVEL');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 5;
	}
	return max(0, min(12, (int) $e));
}

/**
 * @param int|null $mergedPcmBytes Planned decoded PCM size for this FZCD merge (sum of track lens). Used when env unset.
 */
function fractal_zip_flac_pac_merged_fractal_requested(?int $mergedPcmBytes = null): bool {
	$e = getenv('FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
			return false;
		}
		if ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes') {
			return true;
		}
	}
	if ($mergedPcmBytes !== null && $mergedPcmBytes > 0) {
		return true;
	}
	return false;
}

/**
 * Legacy knob (currently unused by write_fzcd_bundle_if_applicable; chunked fractal is not capped by total PCM size).
 * FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_MAX_BYTES unset = 128 MiB; 0 = no limit.
 */
function fractal_zip_flac_pac_merged_fractal_max_input_bytes(): int {
	$e = getenv('FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_MAX_BYTES');
	if ($e === false || trim((string) $e) === '') {
		return 134217728;
	}
	$v = (int) trim((string) $e);
	return $v <= 0 ? 0 : $v;
}

/**
 * Whether merged-fractal PCM chunks may use multipass when the parent fractal_zip has multipass enabled.
 * Unset: follow parent (same as other members). Explicit 0/false/no/off: force single-pass on chunks only.
 * Explicit 1/on/true/yes: allow multipass when parent multipass is on (redundant with unset but kept for clarity).
 */
function fractal_zip_flac_pac_merged_fractal_use_multipass(): bool {
	$e = getenv('FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_MULTIPASS');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	if ($v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
		return false;
	}
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
}

/**
 * @return non-empty-string|null
 */
function fractal_zip_flac_pac_ffprobe_bin(): ?string {
	$e = getenv('FRACTAL_ZIP_FFPROBE');
	if ($e !== false && trim((string) $e) !== '') {
		$p = trim((string) $e);
		if ($p !== '' && is_executable($p)) {
			return $p;
		}
	}
	$line = shell_exec('command -v ffprobe 2>/dev/null');
	if (!is_string($line)) {
		return null;
	}
	$p = trim($line);
	return ($p !== '' && is_executable($p)) ? $p : null;
}

/**
 * @return non-empty-string|null
 */
function fractal_zip_flac_pac_ffmpeg_bin(): ?string {
	$e = getenv('FRACTAL_ZIP_FFMPEG');
	if ($e !== false && trim((string) $e) !== '') {
		$p = trim((string) $e);
		if ($p !== '' && is_executable($p)) {
			return $p;
		}
	}
	$line = shell_exec('command -v ffmpeg 2>/dev/null');
	if (!is_string($line)) {
		return null;
	}
	$p = trim($line);
	return ($p !== '' && is_executable($p)) ? $p : null;
}

function fractal_zip_flac_pac_tools_ok(): bool {
	static $ok = null;
	if ($ok !== null) {
		return $ok;
	}
	$ok = fractal_zip_flac_pac_ffprobe_bin() !== null && fractal_zip_flac_pac_ffmpeg_bin() !== null;
	return $ok;
}

/**
 * @return array{sample_rate: int, channels: int, source_bps: int, pcm_fmt: int}|null
 */
function fractal_zip_flac_pac_probe(string $absPath): ?array {
	if (!is_file($absPath) || !fractal_zip_flac_pac_tools_ok()) {
		return null;
	}
	$ffp = fractal_zip_flac_pac_ffprobe_bin();
	if ($ffp === null) {
		return null;
	}
	$cmd = escapeshellarg($ffp) . ' -v error -select_streams a:0 -show_entries stream=sample_rate,channels,bits_per_sample,sample_fmt -of json '
		. escapeshellarg($absPath) . ' 2>/dev/null';
	$json = shell_exec($cmd);
	if (!is_string($json) || $json === '') {
		return null;
	}
	$data = json_decode($json, true);
	if (!is_array($data) || !isset($data['streams'][0]) || !is_array($data['streams'][0])) {
		return null;
	}
	$s = $data['streams'][0];
	$sr = isset($s['sample_rate']) ? (int) $s['sample_rate'] : 0;
	$ch = isset($s['channels']) ? (int) $s['channels'] : 0;
	if ($sr < 1 || $ch < 1 || $ch > 8) {
		return null;
	}
	$bps = isset($s['bits_per_sample']) ? (int) $s['bits_per_sample'] : 0;
	$fmt = isset($s['sample_fmt']) ? (string) $s['sample_fmt'] : '';
	if ($bps <= 0) {
		if (strncmp($fmt, 's16', 3) === 0) {
			$bps = 16;
		} elseif (strncmp($fmt, 's32', 3) === 0 || strncmp($fmt, 's24', 3) === 0) {
			$bps = 24;
		} else {
			$bps = 16;
		}
	}
	$pcmFmt = $bps <= 16 ? FRACTAL_ZIP_FPCM_FMT_S16LE : FRACTAL_ZIP_FPCM_FMT_S32LE;
	return array(
		'sample_rate' => $sr,
		'channels' => $ch,
		'source_bps' => min(32, max(8, $bps)),
		'pcm_fmt' => $pcmFmt,
	);
}

/**
 * Decode FLAC file to raw PCM file (s16le or s32le per $pcmFmt).
 */
function fractal_zip_flac_pac_decode_file_to_pcm_path(string $absPath, int $pcmFmt, string $outPcmPath): bool {
	if (!fractal_zip_flac_pac_tools_ok()) {
		return false;
	}
	$ffm = fractal_zip_flac_pac_ffmpeg_bin();
	if ($ffm === null) {
		return false;
	}
	$acodec = $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE ? 'pcm_s16le' : 'pcm_s32le';
	$fmt = $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE ? 's16le' : 's32le';
	$cmd = escapeshellarg($ffm) . ' -nostdin -hide_banner -loglevel error -y -i ' . escapeshellarg($absPath)
		. ' -map 0:a:0 -acodec ' . $acodec . ' -f ' . $fmt . ' ' . escapeshellarg($outPcmPath) . ' 2>/dev/null';
	$code = 0;
	system($cmd, $code);
	return $code === 0 && is_file($outPcmPath) && filesize($outPcmPath) > 0;
}

/**
 * Encode one PCM file to FLAC (lossless vs PCM).
 */
function fractal_zip_flac_pac_pcm_file_to_flac_file(string $pcmPath, int $sampleRate, int $channels, int $pcmFmt, string $outFlacPath): bool {
	if (!fractal_zip_flac_pac_tools_ok()) {
		return false;
	}
	$ffm = fractal_zip_flac_pac_ffmpeg_bin();
	if ($ffm === null) {
		return false;
	}
	$fmt = $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE ? 's16le' : 's32le';
	$lev = fractal_zip_flac_pac_compression_level();
	$cmd = escapeshellarg($ffm) . ' -nostdin -hide_banner -loglevel error -y -f ' . $fmt
		. ' -ar ' . (string) $sampleRate . ' -ac ' . (string) $channels
		. ' -i ' . escapeshellarg($pcmPath)
		. ' -c:a flac -compression_level ' . (string) $lev . ' ' . escapeshellarg($outFlacPath) . ' 2>/dev/null';
	$ret = 0;
	system($cmd, $ret);
	return $ret === 0 && is_file($outFlacPath) && filesize($outFlacPath) > 0;
}

/**
 * Bytes per interleaved PCM frame (all channels) from FZCD row ref (channels × ceil(source_bps/8)).
 */
function fractal_zip_flac_pac_pcm_bytes_per_frame_from_ref(array $ref): int {
	$bps = (int) $ref['source_bps'];
	$ch = (int) $ref['channels'];
	$bytes = (int) (max(1, (int) ceil($bps / 8)) * max(1, $ch));
	return max(1, $bytes);
}

/** Target raw PCM bytes per fractal chunk; unset env = 4 MiB. Aligned to frame size when writing. */
function fractal_zip_flac_pac_merged_fractal_chunk_bytes(): int {
	$e = getenv('FRACTAL_ZIP_FLACPAC_MERGED_FRACTAL_CHUNK_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 4194304;
	}
	return max(4096, (int) $e);
}

function fractal_zip_flac_pac_align_chunk_bytes(int $chunk, int $bytesPerFrame): int {
	if ($bytesPerFrame < 1) {
		return $chunk;
	}
	$n = intdiv($chunk, $bytesPerFrame) * $bytesPerFrame;
	return $n >= $bytesPerFrame ? $n : $bytesPerFrame;
}

/** Chunks at or below this size run fractal in-process (parent); above uses subprocess worker. Default 256 KiB. */
function fractal_zip_fzcd_merged_fractal_inline_max_bytes(): int {
	$e = getenv('FRACTAL_ZIP_FZCD_MERGED_FRACTAL_INLINE_BYTES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 262144;
	}
	return max(1024, (int) $e);
}

function fractal_zip_fzcd_worker_memory_limit(): string {
	$e = getenv('FRACTAL_ZIP_FZCD_WORKER_MEMORY');
	if ($e === false || trim((string) $e) === '') {
		return '1024M';
	}
	$t = trim((string) $e);
	return $t !== '' ? $t : '1024M';
}

/** Improvement factor for FZCD merged PCM fractal chunks only; unset or empty = use parent instance threshold. */
function fractal_zip_fzcd_merged_fractal_improvement_threshold(float $parent): float {
	$e = getenv('FRACTAL_ZIP_FZCD_WORKER_IMPROVEMENT');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $parent;
	}
	return max(0.25, min(50.0, (float) $e));
}

/** Segment length for merged PCM fractal chunks only; unset = parent segment_length. */
function fractal_zip_fzcd_merged_fractal_segment_length(int $parentSeg): int {
	$e = getenv('FRACTAL_ZIP_FZCD_WORKER_SEGMENT_LENGTH');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return $parentSeg;
	}
	return max(8, min(500000, (int) $e));
}

function fractal_zip_pcm_pretransform_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_FLACPAC_PCM_PRETRANSFORM');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_pcm_frame_bytes_from_fmt_channels(int $pcmFmt, int $channels): ?int {
	if ($channels < 1 || $channels > 8) {
		return null;
	}
	if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
		return 2 * $channels;
	}
	if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
		return 4 * $channels;
	}
	return null;
}

function fractal_zip_pcm_i16_at(string $s, int $off): int {
	$n = strlen($s);
	if ($off + 1 >= $n) {
		return 0;
	}
	$u = ord($s[$off]) | (ord($s[$off + 1]) << 8);
	if ($u >= 0x8000) {
		$u -= 0x10000;
	}
	return $u;
}

function fractal_zip_pcm_pack_i16(int $v): string {
	if ($v < -32768) {
		$v = -32768;
	}
	if ($v > 32767) {
		$v = 32767;
	}
	$u = $v & 0xffff;
	return chr($u & 0xff) . chr(($u >> 8) & 0xff);
}

function fractal_zip_pcm_i16_wrap_add(int $a, int $b): int {
	$sum = (int) $a + (int) $b;
	$r = $sum % 65536;
	if ($r < 0) {
		$r += 65536;
	}
	if ($r > 32767) {
		$r -= 65536;
	}
	return $r;
}

function fractal_zip_pcm_i16_sub_wrap(int $a, int $b): int {
	$d = (int) $a - (int) $b;
	$r = $d % 65536;
	if ($r < 0) {
		$r += 65536;
	}
	if ($r > 32767) {
		$r -= 65536;
	}
	return $r;
}

function fractal_zip_pcm_i32_at(string $s, int $off): int {
	$n = strlen($s);
	if ($off + 3 >= $n) {
		return 0;
	}
	$u = ord($s[$off]) | (ord($s[$off + 1]) << 8) | (ord($s[$off + 2]) << 16) | (ord($s[$off + 3]) << 24);
	if ($u >= 0x80000000) {
		$u = (int) ($u - 0x100000000);
	}
	return $u;
}

function fractal_zip_pcm_pack_i32(int $v): string {
	if ($v < -2147483648) {
		$v = -2147483648;
	}
	if ($v > 2147483647) {
		$v = 2147483647;
	}
	$u = $v & 0xffffffff;
	return chr($u & 0xff) . chr(($u >> 8) & 0xff) . chr(($u >> 16) & 0xff) . chr(($u >> 24) & 0xff);
}

function fractal_zip_pcm_i32_wrap_add(int $a, int $b): int {
	$sum = (int) $a + (int) $b;
	$r = $sum % 4294967296;
	if ($r < 0) {
		$r += 4294967296;
	}
	if ($r > 2147483647) {
		$r -= 4294967296;
	}
	return $r;
}

function fractal_zip_pcm_i32_sub_wrap(int $a, int $b): int {
	$d = (int) $a - (int) $b;
	$r = $d % 4294967296;
	if ($r < 0) {
		$r += 4294967296;
	}
	if ($r > 2147483647) {
		$r -= 4294967296;
	}
	return $r;
}

/** Fold arbitrary integer to int16 two's-complement ring. */
function fractal_zip_pcm_i16_wide_to_ring(int $w): int {
	$r = (int) (($w % 65536 + 65536) % 65536);
	if ($r > 32767) {
		$r -= 65536;
	}
	return $r;
}

/** Fold arbitrary integer to int32 two's-complement ring. */
function fractal_zip_pcm_i32_wide_to_ring(int $w): int {
	$r = (int) (($w % 4294967296 + 4294967296) % 4294967296);
	if ($r > 2147483647) {
		$r -= 4294967296;
	}
	return $r;
}

/**
 * Deflate level used only to **rank** pre-transforms before fractal (not on wire). Higher tracks fractal/deflate better than **1**.
 * Env **`FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_RANK_LEVEL`**: unset = **4**; clamp **1..9**.
 */
function fractal_zip_pcm_pre_gzip_rank_level(): int {
	$e = getenv('FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_RANK_LEVEL');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 5;
	}
	return max(1, min(9, (int) $e));
}

function fractal_zip_pcm_pre_gzip_dual_rank(): bool {
	$e = getenv('FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_DUAL_RANK');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'on' || $v === 'true' || $v === 'yes';
}

/**
 * Pre ids we almost always want in the fractal contest (if applicable to fmt/ch); listed before gzip-ranked fills.
 *
 * @return list<int>
 */
function fractal_zip_pcm_pre_fractal_anchor_strategies(int $pcmFmt, int $channels): array {
	$all = fractal_zip_pcm_pretransform_strategies_for($pcmFmt, $channels);
	$wish = array(
		FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA,
		FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA2,
		FRACTAL_ZIP_PCM_PRE_BYTE_FIRST_DIFF,
	);
	if ($channels === 2) {
		$wish[] = FRACTAL_ZIP_PCM_PRE_PLANAR_STEREO;
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$wish[] = FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT16;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$wish[] = FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT32;
		}
	}
	if ($channels >= 2) {
		$wish[] = FRACTAL_ZIP_PCM_PRE_INTRAFRAME_CH_DELTA;
	}
	$wish[] = FRACTAL_ZIP_PCM_PRE_SAMPLE_ENDIAN_SWAP;
	$out = array();
	foreach ($wish as $sid) {
		if (in_array($sid, $all, true)) {
			$out[] = $sid;
		}
	}
	return $out;
}

/**
 * Max distinct pre-transform ids passed to fractal `zip()` (always seeds **0** first, then anchors, then gzip-ranked).
 * Env **`FRACTAL_ZIP_FLACPAC_PCM_PRE_FRACTAL_CANDIDATES`**: unset = **8**; clamp **2..16**.
 */
function fractal_zip_pcm_pre_fractal_candidate_cap(): int {
	$e = getenv('FRACTAL_ZIP_FLACPAC_PCM_PRE_FRACTAL_CANDIDATES');
	if ($e === false || trim((string) $e) === '' || !is_numeric($e)) {
		return 8;
	}
	return max(2, min(16, (int) $e));
}

/**
 * @return list<int>
 */
function fractal_zip_pcm_pretransform_strategies_for(int $pcmFmt, int $channels): array {
	$out = array(FRACTAL_ZIP_PCM_PRE_NONE);
	$fb = fractal_zip_pcm_frame_bytes_from_fmt_channels($pcmFmt, $channels);
	if ($fb !== null && $fb >= 2) {
		$out[] = FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA;
		$out[] = FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA2;
	}
	if ($channels === 2 && ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE || $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE)) {
		$out[] = FRACTAL_ZIP_PCM_PRE_SWAP_STEREO_CHANNELS;
	}
	if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE && $channels === 2) {
		$out[] = FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT16;
	}
	if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE && $channels === 2) {
		$out[] = FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT32;
	}
	if ($channels === 2 && ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE || $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE)) {
		$out[] = FRACTAL_ZIP_PCM_PRE_PLANAR_STEREO;
	}
	if ($fb !== null && $fb >= 2 && $channels >= 2) {
		$out[] = FRACTAL_ZIP_PCM_PRE_INTRAFRAME_CH_DELTA;
	}
	if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE && $fb !== null && $fb >= 2) {
		$out[] = FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S16;
	}
	if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE && $fb !== null && $fb >= 4) {
		$out[] = FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S32;
	}
	if (($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE && $fb !== null && $fb >= 2)
		|| ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE && $fb !== null && $fb >= 4)) {
		$out[] = FRACTAL_ZIP_PCM_PRE_SAMPLE_ENDIAN_SWAP;
	}
	$out[] = FRACTAL_ZIP_PCM_PRE_BYTE_FIRST_DIFF;
	$out[] = FRACTAL_ZIP_PCM_PRE_INVERT_BYTES;
	$out[] = FRACTAL_ZIP_PCM_PRE_XOR_BYTE_0x80;
	$out[] = FRACTAL_ZIP_PCM_PRE_NIBBLE_SWAP_BYTES;
	return array_values(array_unique($out));
}

function fractal_zip_pcm_pretransform_apply(string $pcm, int $pcmFmt, int $channels, int $strategy): ?string {
	if ($strategy === FRACTAL_ZIP_PCM_PRE_NONE) {
		return $pcm;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_INVERT_BYTES) {
		$n = strlen($pcm);
		if ($n === 0) {
			return '';
		}
		$o = '';
		for ($i = 0; $i < $n; $i++) {
			$o .= chr(ord($pcm[$i]) ^ 0xff);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_BYTE_FIRST_DIFF) {
		$n = strlen($pcm);
		if ($n === 0) {
			return '';
		}
		$o = $pcm[0];
		for ($i = 1; $i < $n; $i++) {
			$o .= chr((ord($pcm[$i]) - ord($pcm[$i - 1]) + 256) % 256);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_XOR_BYTE_0x80) {
		$n = strlen($pcm);
		if ($n === 0) {
			return '';
		}
		$o = '';
		for ($i = 0; $i < $n; $i++) {
			$o .= chr(ord($pcm[$i]) ^ 0x80);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_NIBBLE_SWAP_BYTES) {
		$n = strlen($pcm);
		if ($n === 0) {
			return '';
		}
		$o = '';
		for ($i = 0; $i < $n; $i++) {
			$b = ord($pcm[$i]);
			$o .= chr((($b & 0x0f) << 4) | (($b >> 4) & 0x0f));
		}
		return $o;
	}
	$fb = fractal_zip_pcm_frame_bytes_from_fmt_channels($pcmFmt, $channels);
	if ($fb === null || $fb < 2 || strlen($pcm) % $fb !== 0) {
		return null;
	}
	$n = strlen($pcm);
	$nFrames = intdiv($n, $fb);
	if ($strategy === FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA) {
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				$pbase = $f > 0 ? ($f - 1) * $fb : null;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 2;
					$cur = fractal_zip_pcm_i16_at($pcm, $bo);
					if ($pbase === null) {
						$o .= fractal_zip_pcm_pack_i16($cur);
					} else {
						$pb = $pbase + $c * 2;
						$pr = fractal_zip_pcm_i16_at($pcm, $pb);
						$d = fractal_zip_pcm_i16_sub_wrap($cur, $pr);
						$o .= fractal_zip_pcm_pack_i16($d);
					}
				}
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				$pbase = $f > 0 ? ($f - 1) * $fb : null;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 4;
					$cur = fractal_zip_pcm_i32_at($pcm, $bo);
					if ($pbase === null) {
						$o .= fractal_zip_pcm_pack_i32($cur);
					} else {
						$pb = $pbase + $c * 4;
						$pr = fractal_zip_pcm_i32_at($pcm, $pb);
						$d = fractal_zip_pcm_i32_sub_wrap($cur, $pr);
						$o .= fractal_zip_pcm_pack_i32($d);
					}
				}
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT16) {
		if ($pcmFmt !== FRACTAL_ZIP_FPCM_FMT_S16LE || $channels !== 2 || $fb !== 4) {
			return null;
		}
		$o = '';
		for ($f = 0; $f < $nFrames; $f++) {
			$base = $f * 4;
			$L = fractal_zip_pcm_i16_at($pcm, $base);
			$R = fractal_zip_pcm_i16_at($pcm, $base + 2);
			$Rp = fractal_zip_pcm_i16_sub_wrap($R, $L);
			$o .= fractal_zip_pcm_pack_i16($L) . fractal_zip_pcm_pack_i16($Rp);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT32) {
		if ($pcmFmt !== FRACTAL_ZIP_FPCM_FMT_S32LE || $channels !== 2 || $fb !== 8) {
			return null;
		}
		$o = '';
		for ($f = 0; $f < $nFrames; $f++) {
			$base = $f * 8;
			$L = fractal_zip_pcm_i32_at($pcm, $base);
			$R = fractal_zip_pcm_i32_at($pcm, $base + 4);
			$Rp = fractal_zip_pcm_i32_sub_wrap($R, $L);
			$o .= fractal_zip_pcm_pack_i32($L) . fractal_zip_pcm_pack_i32($Rp);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S16) {
		if ($pcmFmt !== FRACTAL_ZIP_FPCM_FMT_S16LE) {
			return null;
		}
		$o = '';
		for ($f = 0; $f < $nFrames; $f++) {
			$base = $f * $fb;
			for ($c = 0; $c < $channels; $c++) {
				$bo = $base + $c * 2;
				$v = fractal_zip_pcm_i16_at($pcm, $bo);
				$u = ((int) $v & 0xffff) ^ 0x8000;
				$o .= fractal_zip_pcm_pack_i16(fractal_zip_pcm_i16_wide_to_ring($u));
			}
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S32) {
		if ($pcmFmt !== FRACTAL_ZIP_FPCM_FMT_S32LE) {
			return null;
		}
		$o = '';
		for ($f = 0; $f < $nFrames; $f++) {
			$base = $f * $fb;
			for ($c = 0; $c < $channels; $c++) {
				$bo = $base + $c * 4;
				$v = fractal_zip_pcm_i32_at($pcm, $bo);
				$u0 = (int) (($v % 4294967296 + 4294967296) % 4294967296);
				$uu = $u0 ^ 0x80000000;
				$o .= fractal_zip_pcm_pack_i32(fractal_zip_pcm_i32_wide_to_ring($uu));
			}
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA2) {
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 2;
					$sk = fractal_zip_pcm_i16_at($pcm, $bo);
					if ($f === 0) {
						$o .= fractal_zip_pcm_pack_i16($sk);
					} elseif ($f === 1) {
						$sk0 = fractal_zip_pcm_i16_at($pcm, $c * 2);
						$o .= fractal_zip_pcm_pack_i16(fractal_zip_pcm_i16_sub_wrap($sk, $sk0));
					} else {
						$sk1 = fractal_zip_pcm_i16_at($pcm, ($f - 1) * $fb + $c * 2);
						$sk2 = fractal_zip_pcm_i16_at($pcm, ($f - 2) * $fb + $c * 2);
						$d = fractal_zip_pcm_i16_wide_to_ring((int) $sk - 2 * (int) $sk1 + (int) $sk2);
						$o .= fractal_zip_pcm_pack_i16($d);
					}
				}
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 4;
					$sk = fractal_zip_pcm_i32_at($pcm, $bo);
					if ($f === 0) {
						$o .= fractal_zip_pcm_pack_i32($sk);
					} elseif ($f === 1) {
						$sk0 = fractal_zip_pcm_i32_at($pcm, $c * 4);
						$o .= fractal_zip_pcm_pack_i32(fractal_zip_pcm_i32_sub_wrap($sk, $sk0));
					} else {
						$sk1 = fractal_zip_pcm_i32_at($pcm, ($f - 1) * $fb + $c * 4);
						$sk2 = fractal_zip_pcm_i32_at($pcm, ($f - 2) * $fb + $c * 4);
						$d = fractal_zip_pcm_i32_wide_to_ring((int) $sk - 2 * (int) $sk1 + (int) $sk2);
						$o .= fractal_zip_pcm_pack_i32($d);
					}
				}
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_INTRAFRAME_CH_DELTA) {
		if ($channels < 2) {
			return null;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				$o .= fractal_zip_pcm_pack_i16(fractal_zip_pcm_i16_at($pcm, $base));
				for ($c = 1; $c < $channels; $c++) {
					$cur = fractal_zip_pcm_i16_at($pcm, $base + $c * 2);
					$prev = fractal_zip_pcm_i16_at($pcm, $base + ($c - 1) * 2);
					$o .= fractal_zip_pcm_pack_i16(fractal_zip_pcm_i16_sub_wrap($cur, $prev));
				}
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				$o .= fractal_zip_pcm_pack_i32(fractal_zip_pcm_i32_at($pcm, $base));
				for ($c = 1; $c < $channels; $c++) {
					$cur = fractal_zip_pcm_i32_at($pcm, $base + $c * 4);
					$prev = fractal_zip_pcm_i32_at($pcm, $base + ($c - 1) * 4);
					$o .= fractal_zip_pcm_pack_i32(fractal_zip_pcm_i32_sub_wrap($cur, $prev));
				}
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_SAMPLE_ENDIAN_SWAP) {
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 2;
					$o .= $pcm[$bo + 1] . $pcm[$bo];
				}
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 4;
					$o .= $pcm[$bo + 3] . $pcm[$bo + 2] . $pcm[$bo + 1] . $pcm[$bo];
				}
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_SWAP_STEREO_CHANNELS) {
		if ($channels !== 2) {
			return null;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE && $fb === 4) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * 4;
				$o .= substr($pcm, $base + 2, 2) . substr($pcm, $base, 2);
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE && $fb === 8) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * 8;
				$o .= substr($pcm, $base + 4, 4) . substr($pcm, $base, 4);
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_PLANAR_STEREO) {
		if ($channels !== 2) {
			return null;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE && $fb === 4) {
			$L = '';
			$R = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * 4;
				$L .= substr($pcm, $base, 2);
				$R .= substr($pcm, $base + 2, 2);
			}
			return $L . $R;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE && $fb === 8) {
			$L = '';
			$R = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * 8;
				$L .= substr($pcm, $base, 4);
				$R .= substr($pcm, $base + 4, 4);
			}
			return $L . $R;
		}
		return null;
	}
	return null;
}

function fractal_zip_pcm_pretransform_inverse(string $pcm, int $pcmFmt, int $channels, int $strategy): ?string {
	if ($strategy === FRACTAL_ZIP_PCM_PRE_NONE) {
		return $pcm;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_INVERT_BYTES) {
		return fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, FRACTAL_ZIP_PCM_PRE_INVERT_BYTES);
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_BYTE_FIRST_DIFF) {
		$n = strlen($pcm);
		if ($n === 0) {
			return '';
		}
		$o = $pcm[0];
		$p = ord($pcm[0]);
		for ($i = 1; $i < $n; $i++) {
			$p = ($p + ord($pcm[$i])) % 256;
			$o .= chr($p);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_XOR_BYTE_0x80) {
		return fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, FRACTAL_ZIP_PCM_PRE_XOR_BYTE_0x80);
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_NIBBLE_SWAP_BYTES) {
		return fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, FRACTAL_ZIP_PCM_PRE_NIBBLE_SWAP_BYTES);
	}
	$fb = fractal_zip_pcm_frame_bytes_from_fmt_channels($pcmFmt, $channels);
	if ($fb === null || strlen($pcm) % $fb !== 0) {
		return null;
	}
	$n = strlen($pcm);
	$nFrames = intdiv($n, $fb);
	if ($strategy === FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA) {
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 2;
					$d = fractal_zip_pcm_i16_at($pcm, $bo);
					if ($f === 0) {
						$o .= fractal_zip_pcm_pack_i16($d);
					} else {
						$pbo = ($f - 1) * $fb + $c * 2;
						$pr = fractal_zip_pcm_i16_at($o, $pbo);
						$cur = fractal_zip_pcm_i16_wrap_add($pr, $d);
						$o .= fractal_zip_pcm_pack_i16($cur);
					}
				}
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 4;
					$d = fractal_zip_pcm_i32_at($pcm, $bo);
					if ($f === 0) {
						$o .= fractal_zip_pcm_pack_i32($d);
					} else {
						$pbo = ($f - 1) * $fb + $c * 4;
						$pr = fractal_zip_pcm_i32_at($o, $pbo);
						$cur = fractal_zip_pcm_i32_wrap_add($pr, $d);
						$o .= fractal_zip_pcm_pack_i32($cur);
					}
				}
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT16) {
		if ($pcmFmt !== FRACTAL_ZIP_FPCM_FMT_S16LE || $channels !== 2) {
			return null;
		}
		$o = '';
		for ($f = 0; $f < $nFrames; $f++) {
			$base = $f * 4;
			$L = fractal_zip_pcm_i16_at($pcm, $base);
			$Rp = fractal_zip_pcm_i16_at($pcm, $base + 2);
			$R = fractal_zip_pcm_i16_wrap_add($Rp, $L);
			$o .= fractal_zip_pcm_pack_i16($L) . fractal_zip_pcm_pack_i16($R);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_STEREO_SIDE_INT32) {
		if ($pcmFmt !== FRACTAL_ZIP_FPCM_FMT_S32LE || $channels !== 2) {
			return null;
		}
		$o = '';
		for ($f = 0; $f < $nFrames; $f++) {
			$base = $f * 8;
			$L = fractal_zip_pcm_i32_at($pcm, $base);
			$Rp = fractal_zip_pcm_i32_at($pcm, $base + 4);
			$R = fractal_zip_pcm_i32_wrap_add($Rp, $L);
			$o .= fractal_zip_pcm_pack_i32($L) . fractal_zip_pcm_pack_i32($R);
		}
		return $o;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S16) {
		return fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S16);
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S32) {
		return fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, FRACTAL_ZIP_PCM_PRE_TOGGLE_SIGN_S32);
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_CH_TEMPORAL_DELTA2) {
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 2;
					$dk = fractal_zip_pcm_i16_at($pcm, $bo);
					if ($f === 0) {
						$o .= fractal_zip_pcm_pack_i16($dk);
					} elseif ($f === 1) {
						$s0 = fractal_zip_pcm_i16_at($o, $c * 2);
						$o .= fractal_zip_pcm_pack_i16(fractal_zip_pcm_i16_wrap_add($s0, $dk));
					} else {
						$sk1 = fractal_zip_pcm_i16_at($o, ($f - 1) * $fb + $c * 2);
						$sk2 = fractal_zip_pcm_i16_at($o, ($f - 2) * $fb + $c * 2);
						$sk = fractal_zip_pcm_i16_wide_to_ring((int) $dk + 2 * (int) $sk1 - (int) $sk2);
						$o .= fractal_zip_pcm_pack_i16($sk);
					}
				}
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				for ($c = 0; $c < $channels; $c++) {
					$bo = $base + $c * 4;
					$dk = fractal_zip_pcm_i32_at($pcm, $bo);
					if ($f === 0) {
						$o .= fractal_zip_pcm_pack_i32($dk);
					} elseif ($f === 1) {
						$s0 = fractal_zip_pcm_i32_at($o, $c * 4);
						$o .= fractal_zip_pcm_pack_i32(fractal_zip_pcm_i32_wrap_add($s0, $dk));
					} else {
						$sk1 = fractal_zip_pcm_i32_at($o, ($f - 1) * $fb + $c * 4);
						$sk2 = fractal_zip_pcm_i32_at($o, ($f - 2) * $fb + $c * 4);
						$sk = fractal_zip_pcm_i32_wide_to_ring((int) $dk + 2 * (int) $sk1 - (int) $sk2);
						$o .= fractal_zip_pcm_pack_i32($sk);
					}
				}
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_INTRAFRAME_CH_DELTA) {
		if ($channels < 2) {
			return null;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				$s0 = fractal_zip_pcm_i16_at($pcm, $base);
				$o .= fractal_zip_pcm_pack_i16($s0);
				for ($c = 1; $c < $channels; $c++) {
					$d = fractal_zip_pcm_i16_at($pcm, $base + $c * 2);
					$prev = fractal_zip_pcm_i16_at($o, $base + ($c - 1) * 2);
					$o .= fractal_zip_pcm_pack_i16(fractal_zip_pcm_i16_wrap_add($d, $prev));
				}
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE) {
			$o = '';
			for ($f = 0; $f < $nFrames; $f++) {
				$base = $f * $fb;
				$s0 = fractal_zip_pcm_i32_at($pcm, $base);
				$o .= fractal_zip_pcm_pack_i32($s0);
				for ($c = 1; $c < $channels; $c++) {
					$d = fractal_zip_pcm_i32_at($pcm, $base + $c * 4);
					$prev = fractal_zip_pcm_i32_at($o, $base + ($c - 1) * 4);
					$o .= fractal_zip_pcm_pack_i32(fractal_zip_pcm_i32_wrap_add($d, $prev));
				}
			}
			return $o;
		}
		return null;
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_SAMPLE_ENDIAN_SWAP) {
		return fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, FRACTAL_ZIP_PCM_PRE_SAMPLE_ENDIAN_SWAP);
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_SWAP_STEREO_CHANNELS) {
		return fractal_zip_pcm_pretransform_apply($pcm, $pcmFmt, $channels, FRACTAL_ZIP_PCM_PRE_SWAP_STEREO_CHANNELS);
	}
	if ($strategy === FRACTAL_ZIP_PCM_PRE_PLANAR_STEREO) {
		if ($channels !== 2) {
			return null;
		}
		$n = strlen($pcm);
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE && $fb === 4 && $n % 4 === 0) {
			$nS = intdiv($n, 4);
			$half = intdiv($n, 2);
			$L = substr($pcm, 0, $half);
			$R = substr($pcm, $half);
			if (strlen($L) !== $half || strlen($R) !== $half) {
				return null;
			}
			$o = '';
			for ($f = 0; $f < $nS; $f++) {
				$o .= substr($L, $f * 2, 2) . substr($R, $f * 2, 2);
			}
			return $o;
		}
		if ($pcmFmt === FRACTAL_ZIP_FPCM_FMT_S32LE && $fb === 8 && $n % 8 === 0) {
			$nS = intdiv($n, 8);
			$half = intdiv($n, 2);
			$L = substr($pcm, 0, $half);
			$R = substr($pcm, $half);
			if (strlen($L) !== $half || strlen($R) !== $half) {
				return null;
			}
			$o = '';
			for ($f = 0; $f < $nS; $f++) {
				$o .= substr($L, $f * 4, 4) . substr($R, $f * 4, 4);
			}
			return $o;
		}
		return null;
	}
	return null;
}

/**
 * @return array{inner: string, pre: int}|null
 */
function fractal_zip_fzcd_encode_merged_pcm_chunk_with_pre(
	string $pcmChunk,
	int $segmentLen,
	bool $multipass,
	float $improvement,
	?int $topK,
	?float $gateMult,
	int $pcmFmt,
	int $channels
): ?array {
	if ($pcmChunk === '') {
		return null;
	}
	$got = strlen($pcmChunk);
	if (!fractal_zip_pcm_pretransform_enabled()) {
		$inner = fractal_zip_fzcd_encode_merged_pcm_chunk_raw($pcmChunk, $segmentLen, $multipass, $improvement, $topK, $gateMult);
		return $inner === null ? null : array('inner' => $inner, 'pre' => FRACTAL_ZIP_PCM_PRE_NONE);
	}
	$strategies = fractal_zip_pcm_pretransform_strategies_for($pcmFmt, $channels);
	$rankLevel = fractal_zip_pcm_pre_gzip_rank_level();
	$scored = array();
	foreach ($strategies as $sid) {
		$t = fractal_zip_pcm_pretransform_apply($pcmChunk, $pcmFmt, $channels, $sid);
		if ($t === null || $t === '') {
			continue;
		}
		$zd = @gzdeflate($t, $rankLevel);
		if ($zd === false && $rankLevel !== 1) {
			$zd = @gzdeflate($t, 1);
		}
		if ($zd === false) {
			continue;
		}
		$gzMain = (int) strlen($zd);
		$gzScore = $gzMain;
		if (fractal_zip_pcm_pre_gzip_dual_rank()) {
			$z1 = @gzdeflate($t, 1);
			if ($z1 !== false) {
				$gzScore = $gzMain + (int) (strlen($z1) / 32);
			}
		}
		$scored[] = array('sid' => (int) $sid, 'gz' => $gzScore);
	}
	if ($scored === array()) {
		return null;
	}
	usort(
		$scored,
		static function (array $a, array $b): int {
			if ($a['gz'] !== $b['gz']) {
				return $a['gz'] <=> $b['gz'];
			}
			return $a['sid'] <=> $b['sid'];
		}
	);
	$cap = fractal_zip_pcm_pre_fractal_candidate_cap();
	$anchors = fractal_zip_pcm_pre_fractal_anchor_strategies($pcmFmt, $channels);
	$used = array();
	$candidates = array();
	$pushCand = static function (int $sid) use (&$candidates, &$used, $cap, $strategies): void {
		if (count($candidates) >= $cap) {
			return;
		}
		if (!in_array($sid, $strategies, true)) {
			return;
		}
		if (isset($used[$sid])) {
			return;
		}
		$used[$sid] = true;
		$candidates[] = $sid;
	};
	$pushCand(FRACTAL_ZIP_PCM_PRE_NONE);
	foreach ($anchors as $sid) {
		$pushCand((int) $sid);
	}
	foreach ($scored as $row) {
		$pushCand((int) $row['sid']);
	}
	$bestInner = null;
	$bestPre = FRACTAL_ZIP_PCM_PRE_NONE;
	$bestLen = PHP_INT_MAX;
	foreach ($candidates as $sid) {
		$t = fractal_zip_pcm_pretransform_apply($pcmChunk, $pcmFmt, $channels, $sid);
		if ($t === null) {
			continue;
		}
		$inner = fractal_zip_fzcd_encode_merged_pcm_chunk_raw($t, $segmentLen, $multipass, $improvement, $topK, $gateMult);
		if ($inner === null) {
			continue;
		}
		$il = strlen($inner);
		if ($il < $got && ($il < $bestLen || ($il === $bestLen && $sid < $bestPre))) {
			$bestLen = $il;
			$bestInner = $inner;
			$bestPre = $sid;
		}
	}
	if ($bestInner === null) {
		return null;
	}
	return array('inner' => $bestInner, 'pre' => $bestPre);
}

/**
 * After fractal unzip of one merged chunk, map back to raw PCM when a pre-transform was used.
 */
function fractal_zip_fzcd_merge_pcm_piece_maybe_invert(string $piece, int $preId, int $cLen, int $pcmFmt, int $channels): ?string {
	if (strlen($piece) !== $cLen) {
		return null;
	}
	if ($preId === FRACTAL_ZIP_PCM_PRE_NONE) {
		return $piece;
	}
	$inv = fractal_zip_pcm_pretransform_inverse($piece, $pcmFmt, $channels, $preId);
	if ($inv === null || strlen($inv) !== $cLen) {
		return null;
	}
	return $inv;
}

function fractal_zip_fzcd_merged_fractal_worker_path(): string {
	return __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_fzcd_merged_fractal_worker.php';
}

/**
 * @param null|string $prevTop prior getenv value (false = unset)
 */
function fractal_zip_fzcd_restore_worker_tune_env($prevTop, $prevGate): void {
	if ($prevTop === false || (string) $prevTop === '') {
		putenv('FRACTAL_ZIP_FZCD_WORKER_TOP_K');
	} else {
		putenv('FRACTAL_ZIP_FZCD_WORKER_TOP_K=' . (string) $prevTop);
	}
	if ($prevGate === false || (string) $prevGate === '') {
		putenv('FRACTAL_ZIP_FZCD_WORKER_GATE_MULT');
	} else {
		putenv('FRACTAL_ZIP_FZCD_WORKER_GATE_MULT=' . (string) $prevGate);
	}
}

function fractal_zip_fzcd_run_merged_fractal_worker(string $inFile, string $outFile, int $segmentLen, bool $multipass, float $improvement, ?int $topK, ?float $gateMult): bool {
	$worker = fractal_zip_fzcd_merged_fractal_worker_path();
	if (!is_file($worker)) {
		return false;
	}
	$php = PHP_BINARY;
	if ($php === '' || !@is_executable($php)) {
		$php = 'php';
	}
	$mem = fractal_zip_fzcd_worker_memory_limit();
	$prevTop = getenv('FRACTAL_ZIP_FZCD_WORKER_TOP_K');
	$prevGate = getenv('FRACTAL_ZIP_FZCD_WORKER_GATE_MULT');
	if ($topK !== null) {
		putenv('FRACTAL_ZIP_FZCD_WORKER_TOP_K=' . (string) $topK);
	}
	if ($gateMult !== null) {
		putenv('FRACTAL_ZIP_FZCD_WORKER_GATE_MULT=' . (string) $gateMult);
	}
	$cmd = array(
		$php,
		'-d',
		'memory_limit=' . $mem,
		'-d',
		'max_execution_time=0',
		$worker,
		$inFile,
		$outFile,
		(string) $segmentLen,
		$multipass ? '1' : '0',
		(string) $improvement,
	);
	$desc = array(
		0 => array('pipe', 'r'),
		1 => array('pipe', 'w'),
		2 => array('pipe', 'w'),
	);
	$proc = @proc_open($cmd, $desc, $pipes, null, null);
	if (!is_resource($proc)) {
		fractal_zip_fzcd_restore_worker_tune_env($prevTop, $prevGate);
		return false;
	}
	fclose($pipes[0]);
	if (isset($pipes[1]) && is_resource($pipes[1])) {
		stream_get_contents($pipes[1]);
		fclose($pipes[1]);
	}
	if (isset($pipes[2]) && is_resource($pipes[2])) {
		stream_get_contents($pipes[2]);
		fclose($pipes[2]);
	}
	$code = proc_close($proc);
	fractal_zip_fzcd_restore_worker_tune_env($prevTop, $prevGate);
	return $code === 0 && is_file($outFile) && filesize($outFile) > 0;
}

/**
 * Fractal-encode one PCM chunk (already in encode domain) to inner FZC* bytes (inline or subprocess).
 */
function fractal_zip_fzcd_encode_merged_pcm_chunk_raw(string $pcmChunk, int $segmentLen, bool $multipass, float $improvement, ?int $topK, ?float $gateMult): ?string {
	if ($pcmChunk === '') {
		return null;
	}
	$n = strlen($pcmChunk);
	if ($n <= fractal_zip_fzcd_merged_fractal_inline_max_bytes()) {
		$prevPass = getenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS');
		putenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS=1');
		ob_start();
		try {
			$sub = new fractal_zip($segmentLen, $multipass, false, null, false);
			$sub->improvement_factor_threshold = $improvement;
			if ($topK !== null) {
				$sub->tuning_substring_top_k = max(1, min(12, $topK));
			}
			if ($gateMult !== null) {
				$sub->tuning_multipass_gate_mult = max(1.0, min(4.0, $gateMult));
			}
			$sub->zip($pcmChunk, FRACTAL_ZIP_FZCD_MERGED_PCM_MEMBER, false);
			$arr = array();
			foreach ($sub->equivalences as $eq) {
				$arr[$eq[1]] = $eq[2];
			}
			$inner = $sub->encode_container_payload($arr, $sub->fractal_string);
			if (!is_string($inner) || $inner === '') {
				return null;
			}
			return $inner;
		} catch (Throwable $e) {
			return null;
		} finally {
			if ($prevPass === false) {
				putenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS');
			} else {
				putenv('FRACTAL_ZIP_FZCD_MERGED_ZIP_PASS=' . $prevPass);
			}
			if (ob_get_level() > 0) {
				ob_end_clean();
			}
		}
	}
	$tin = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzwk_' . bin2hex(random_bytes(8)) . '.raw';
	$tout = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzwk_' . bin2hex(random_bytes(8)) . '.inner';
	if (file_put_contents($tin, $pcmChunk) === false) {
		return null;
	}
	$ok = fractal_zip_fzcd_run_merged_fractal_worker($tin, $tout, $segmentLen, $multipass, $improvement, $topK, $gateMult);
	@unlink($tin);
	if (!$ok) {
		@unlink($tout);
		return null;
	}
	$inner = file_get_contents($tout);
	@unlink($tout);
	if ($inner === false || $inner === '') {
		return null;
	}
	return $inner;
}
