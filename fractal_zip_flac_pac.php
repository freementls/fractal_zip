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
 * Requires ffmpeg + ffprobe on PATH.
 * FRACTAL_ZIP_FLACPAC=0 disables FZCD merge (plain FZB4 only).
 * FRACTAL_ZIP_FLACPAC_FLAC_LEVEL=0–12 for re-encode on extract.
 *
 * Merged-FLAC output is semantically lossless (PCM) but per-file .flac bytes usually differ from the originals; benchmarks
 * that SHA1 the tree byte-for-byte may report verify_ok=false unless FLACPAC is off or verify is skipped.
 */

/** PCM layout after decode: s16le for ≤16-bit source, s32le for deeper (ffmpeg output). */
const FRACTAL_ZIP_FPCM_FMT_S16LE = 1;
const FRACTAL_ZIP_FPCM_FMT_S32LE = 2;

function fractal_zip_flac_pac_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_FLACPAC');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
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

function fractal_zip_flac_pac_tools_ok(): bool {
	static $ok = null;
	if ($ok !== null) {
		return $ok;
	}
	$fp = shell_exec('command -v ffprobe 2>/dev/null');
	$fm = shell_exec('command -v ffmpeg 2>/dev/null');
	$ok = is_string($fp) && trim($fp) !== '' && is_string($fm) && trim($fm) !== '';
	return $ok;
}

/**
 * @return array{sample_rate: int, channels: int, source_bps: int, pcm_fmt: int}|null
 */
function fractal_zip_flac_pac_probe(string $absPath): ?array {
	if (!is_file($absPath) || !fractal_zip_flac_pac_tools_ok()) {
		return null;
	}
	$cmd = 'ffprobe -v error -select_streams a:0 -show_entries stream=sample_rate,channels,bits_per_sample,sample_fmt -of json '
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
	$acodec = $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE ? 'pcm_s16le' : 'pcm_s32le';
	$fmt = $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE ? 's16le' : 's32le';
	$cmd = 'ffmpeg -nostdin -hide_banner -loglevel error -y -i ' . escapeshellarg($absPath)
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
	$fmt = $pcmFmt === FRACTAL_ZIP_FPCM_FMT_S16LE ? 's16le' : 's32le';
	$lev = fractal_zip_flac_pac_compression_level();
	$cmd = 'ffmpeg -nostdin -hide_banner -loglevel error -y -f ' . $fmt
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
 * Fractal-encode one PCM chunk to inner FZC* bytes (inline or subprocess).
 */
function fractal_zip_fzcd_encode_merged_pcm_chunk(string $pcmChunk, int $segmentLen, bool $multipass, float $improvement, ?int $topK, ?float $gateMult): ?string {
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
