<?php
declare(strict_types=1);

/**
 * External lossless text compressors for sub-1 bpc probe matrix.
 *
 * Every adapter reports status ok|unavailable|failed — never skipped_by_policy.
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_dict_preprocess.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';

/** @return list<string> */
function fractal_zip_text_compressor_model_catalog(): array
{
	return array(
		'zpaq9',
		'paq_inner',
		'phda9',
		'paq8px',
		'cmix21',
		'parallel_cmix',
		'parallel_phda9',
		'gpt2tc',
		'nncp32',
		'llmzip_llama7b',
		'tensorflow_compress',
		'jax_compress',
		'b64pack',
	);
}

/** @return array<string, array{priority: string, gpu: bool, label: string}> */
function fractal_zip_text_compressor_model_meta(): array
{
	return array(
		'llmzip_llama7b' => array('priority' => 'P0', 'gpu' => true, 'label' => 'LLMZip LLaMA-7B + AC'),
		'nncp32' => array('priority' => 'P0', 'gpu' => true, 'label' => 'nncp v3.2'),
		'tensorflow_compress' => array('priority' => 'P1', 'gpu' => true, 'label' => 'tensorflow-compress'),
		'jax_compress' => array('priority' => 'P1', 'gpu' => true, 'label' => 'jax-compress'),
		'phda9' => array('priority' => 'P1', 'gpu' => false, 'label' => 'phda9'),
		'cmix21' => array('priority' => 'P1', 'gpu' => false, 'label' => 'cmix v21'),
		'paq8px' => array('priority' => 'P1', 'gpu' => false, 'label' => 'paq8px'),
		'paq_inner' => array('priority' => 'P1', 'gpu' => false, 'label' => 'PAQ multi-tool'),
		'parallel_cmix' => array('priority' => 'P1', 'gpu' => false, 'label' => 'parallel_paq cmix-class'),
		'parallel_phda9' => array('priority' => 'P1', 'gpu' => false, 'label' => 'parallel_paq phda9-class'),
		'gpt2tc' => array('priority' => 'P2', 'gpu' => false, 'label' => 'gpt2tc'),
		'b64pack' => array('priority' => 'P3', 'gpu' => false, 'label' => 'b64pack'),
		'zpaq9' => array('priority' => 'baseline', 'gpu' => false, 'label' => 'zpaq m9'),
	);
}

function fractal_zip_text_compressor_discover_executable(string $modelId): ?string
{
	$key = 'FRACTAL_ZIP_TEXT_EXT_' . strtoupper(preg_replace('/[^a-z0-9]/', '_', $modelId) ?: $modelId);
	$e = getenv($key);
	if ($e !== false && trim((string) $e) !== '') {
		$p = trim((string) $e);
		return is_executable($p) ? $p : null;
	}
	$aliases = array(
		'nncp32' => array('nncp', 'nncp32'),
		'llmzip_llama7b' => array('llmzip'),
		'gpt2tc' => array('gpt2tc'),
		'tensorflow_compress' => array('tensorflow-compress', 'tfcompress'),
		'jax_compress' => array('jax-compress'),
		'b64pack' => array('b64pack'),
		'cmix21' => array('cmix', 'cmix21'),
		'paq8px' => array('paq8px', 'paq8pxd'),
		'phda9' => array('phda9'),
		'zpaq9' => array('zpaq', 'zpaq9'),
	);
	foreach ($aliases[$modelId] ?? array($modelId) as $name) {
		if ($modelId === 'phda9') {
			$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9';
			if (is_executable($bundled)) {
				return $bundled;
			}
		}
		if ($modelId === 'nncp32') {
			$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'nncp';
			if (is_executable($bundled)) {
				return $bundled;
			}
			$built = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'nncp-2024-06-05' . DIRECTORY_SEPARATOR . 'nncp';
			if (is_executable($built)) {
				return $built;
			}
		}
		$line = trim((string) shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));
		if ($line !== '' && is_executable($line)) {
			return $line;
		}
	}
	if ($modelId === 'paq_inner') {
		$tools = fractal_zip_paq_discover_tools();
		return $tools !== array() ? reset($tools) : null;
	}
	if ($modelId === 'parallel_cmix' || $modelId === 'parallel_phda9') {
		return fractal_zip_paq_discover_executable($modelId);
	}
	return null;
}

function fractal_zip_text_compressor_gpu_available(): bool
{
	$line = trim((string) shell_exec('command -v nvidia-smi 2>/dev/null'));
	if ($line === '') {
		return false;
	}
	$out = shell_exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null');
	return is_string($out) && trim($out) !== '';
}

function fractal_zip_text_compressor_gpu_name(): ?string
{
	if (!fractal_zip_text_compressor_gpu_available()) {
		return null;
	}
	$out = shell_exec('nvidia-smi --query-gpu=name --format=csv,noheader 2>/dev/null');
	return is_string($out) ? trim(explode("\n", trim($out))[0]) : null;
}

/**
 * Run zpaq m9 on payload bytes (bench ceiling).
 */
function fractal_zip_text_compressor_zpaq9(string $payload, array $opts = array()): array
{
	$meth = (string) ($opts['zpaq_method'] ?? ' -method 9');
	$t0 = microtime(true);
	$zpaq = fractal_zip::zpaq_executable();
	if ($zpaq === '' || !is_executable($zpaq)) {
		return fractal_zip_text_compressor_result_unavailable('zpaq9', 'zpaq binary missing');
	}
	$host = new fractal_zip(256, false, false, null, false);
	$blob = $host->outer_zpaq_blob_with_meth_fragment($zpaq, $payload, $meth);
	$sec = microtime(true) - $t0;
	if (!is_string($blob) || $blob === '') {
		return array(
			'model' => 'zpaq9',
			'status' => 'failed',
			'compressed_bytes' => null,
			'roundtrip_ok' => false,
			'wall_seconds' => round($sec, 6),
			'error' => 'zpaq compress failed',
		);
	}
	return array(
		'model' => 'zpaq9',
		'status' => 'ok',
		'compressed_bytes' => strlen($blob),
		'payload' => $blob,
		'roundtrip_ok' => true,
		'wall_seconds' => round($sec, 6),
		'gpu_device' => null,
		'decompresser_bytes' => strlen($zpaq),
		'zpaq_method' => trim($meth),
	);
}

/**
 * PAQ-family compress via fractal_zip_paq or direct tool invocation.
 */
function fractal_zip_text_compressor_paq_family(string $modelId, string $payload, array $opts = array()): array
{
	$timeout = (int) ($opts['timeout_sec'] ?? 0);
	if ($modelId === 'paq_inner') {
		$tools = fractal_zip_paq_discover_tools();
		if ($tools === array()) {
			return fractal_zip_text_compressor_result_unavailable('paq_inner', 'no PAQ tools');
		}
	} else {
		$exe = fractal_zip_text_compressor_discover_executable($modelId);
		if ($exe === null) {
			return fractal_zip_text_compressor_result_unavailable($modelId, 'binary missing');
		}
		$tools = array($modelId => $exe);
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_txt_paq_' . getmypid();
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'text.in';
	file_put_contents($in, $payload);
	$best = null;
	$bestTool = null;
	$bestExe = null;
	$bestSec = 0.0;
	$parallel = count($tools) >= 2 && PHP_SAPI === 'cli' && PHP_OS_FAMILY !== 'Windows'
		&& function_exists('pcntl_fork') && function_exists('pcntl_waitpid')
		&& (getenv('FRACTAL_ZIP_PAQ_PARALLEL') === '1' || $modelId === 'paq_inner');
	if ($parallel) {
		$tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_paq_par_' . bin2hex(random_bytes(4));
		@mkdir($tmpBase, 0700, true);
		$children = array();
		foreach ($tools as $toolId => $exe) {
			$pid = pcntl_fork();
			if ($pid === -1) {
				$parallel = false;
				break;
			}
			if ($pid === 0) {
				$childIn = $tmpBase . DIRECTORY_SEPARATOR . $toolId . '.in';
				copy($in, $childIn);
				if ($timeout > 0) {
					putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . (string) $timeout);
				}
				$r = fractal_zip_paq_compress_file($toolId, $exe, $childIn);
				file_put_contents($tmpBase . DIRECTORY_SEPARATOR . $toolId . '.json', json_encode($r));
				exit(0);
			}
			$children[] = $pid;
		}
		if ($parallel) {
			foreach ($children as $cpid) {
				pcntl_waitpid($cpid, $st);
			}
			foreach ($tools as $toolId => $exe) {
				$jsonPath = $tmpBase . DIRECTORY_SEPARATOR . $toolId . '.json';
				if (!is_file($jsonPath)) {
					continue;
				}
				$r = json_decode((string) file_get_contents($jsonPath), true);
				$b = is_array($r) ? ($r['bytes'] ?? null) : null;
				if (is_string($b) && ($best === null || strlen($b) < strlen($best))) {
					$best = $b;
					$bestTool = $toolId;
					$bestExe = $exe;
					$bestSec = (float) ($r['seconds'] ?? 0.0);
				}
			}
			fractal_zip_enwik_recursive_remove($tmpBase);
		}
	}
	if (!$parallel) {
		foreach ($tools as $toolId => $exe) {
			if ($timeout > 0) {
				putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . $timeout);
			}
			$r = fractal_zip_paq_compress_file($toolId, $exe, $in);
			$b = $r['bytes'];
			if (is_string($b) && ($best === null || strlen($b) < strlen($best))) {
				$best = $b;
				$bestTool = $toolId;
				$bestExe = $exe;
				$bestSec = (float) $r['seconds'];
			}
		}
	}
	fractal_zip_enwik_recursive_remove($tmp);
	if ($best === null || $bestTool === null) {
		return array('model' => $modelId, 'status' => 'failed', 'compressed_bytes' => null, 'roundtrip_ok' => false, 'wall_seconds' => $bestSec);
	}
	$roundtripOk = false;
	$verifyTmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_paq_rt_' . getmypid();
	@mkdir($verifyTmp, 0700, true);
	$arcPath = $verifyTmp . DIRECTORY_SEPARATOR . 'arc.paq';
	$outPath = $verifyTmp . DIRECTORY_SEPARATOR . 'out.bin';
	file_put_contents($arcPath, $best);
	$exe = $bestExe ?? fractal_zip_text_compressor_discover_executable($bestTool);
	if ($exe !== null && fractal_zip_paq_decompress_to_file($bestTool, $exe, $arcPath, $outPath)) {
		$got = (string) file_get_contents($outPath);
		$roundtripOk = hash_equals($payload, $got);
	}
	fractal_zip_enwik_recursive_remove($verifyTmp);
	$wirePayload = $best;
	if ($roundtripOk && !empty($opts['wire_wrap'])) {
		$wirePayload = fractal_zip_text_paq_wire_wrap($bestTool, $best, strlen($payload));
	}
	return array(
		'model' => $modelId,
		'status' => 'ok',
		'compressed_bytes' => strlen($wirePayload),
		'payload' => $wirePayload,
		'tool' => $bestTool,
		'roundtrip_ok' => $roundtripOk,
		'wall_seconds' => round($bestSec, 6),
		'gpu_device' => null,
		'decompresser_bytes' => null,
	);
}

/** Lossless PAQ member wrapper for shootout / restore (undo via {@see fractal_zip_text_paq_wire_undo()}). */
const FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC = "FZPA\x01";

function fractal_zip_text_paq_wire_wrap(string $toolId, string $arcBytes, int $plainLen, ?string $vocabBlob = null): string
{
	$wire = FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC
		. fractal_zip_enwik_encode_varint_u32($plainLen)
		. fractal_zip_enwik_encode_varint_u32(strlen($toolId))
		. $toolId
		. fractal_zip_enwik_encode_varint_u32(strlen($arcBytes))
		. $arcBytes;
	if (is_string($vocabBlob) && $vocabBlob !== '') {
		$wire .= fractal_zip_enwik_encode_varint_u32(strlen($vocabBlob)) . $vocabBlob;
	}
	return $wire;
}

/**
 * @return array{plain_len: int, tool_id: string, arc_bytes: string, vocab_blob: string, off_end: int}|null
 */
function fractal_zip_text_paq_wire_parse(string $wire): ?array
{
	if (!str_starts_with($wire, FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC)) {
		return null;
	}
	$off = strlen(FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC);
	$plainDec = fractal_zip_enwik_decode_varint_u32($wire, $off);
	if ($plainDec === null) {
		return null;
	}
	$plainLen = (int) $plainDec[0];
	$off = (int) $plainDec[1];
	$toolDec = fractal_zip_enwik_decode_varint_u32($wire, $off);
	if ($toolDec === null) {
		return null;
	}
	$toolLen = (int) $toolDec[0];
	$off = (int) $toolDec[1];
	if ($toolLen < 0 || $off + $toolLen > strlen($wire)) {
		return null;
	}
	$toolId = substr($wire, $off, $toolLen);
	$off += $toolLen;
	$arcDec = fractal_zip_enwik_decode_varint_u32($wire, $off);
	if ($arcDec === null) {
		return null;
	}
	$arcLen = (int) $arcDec[0];
	$off = (int) $arcDec[1];
	if ($arcLen < 0 || $off + $arcLen > strlen($wire)) {
		return null;
	}
	$arcBytes = substr($wire, $off, $arcLen);
	$off += $arcLen;
	$vocabBlob = '';
	if ($off < strlen($wire)) {
		$vocDec = fractal_zip_enwik_decode_varint_u32($wire, $off);
		if ($vocDec === null) {
			return null;
		}
		$vocLen = (int) $vocDec[0];
		$off = (int) $vocDec[1];
		if ($vocLen < 0 || $off + $vocLen > strlen($wire)) {
			return null;
		}
		$vocabBlob = substr($wire, $off, $vocLen);
		$off += $vocLen;
	}
	return array(
		'plain_len' => $plainLen,
		'tool_id' => $toolId,
		'arc_bytes' => $arcBytes,
		'vocab_blob' => $vocabBlob,
		'off_end' => $off,
	);
}

function fractal_zip_text_paq_wire_undo(string $wire): string
{
	$parsed = fractal_zip_text_paq_wire_parse($wire);
	if ($parsed === null) {
		return $wire;
	}
	$plainLen = (int) $parsed['plain_len'];
	$toolId = (string) $parsed['tool_id'];
	$arcBytes = (string) $parsed['arc_bytes'];
	$arcLen = strlen($arcBytes);
	if (PHP_SAPI === 'cli' && is_resource(STDERR) && getenv('FRACTAL_ZIP_FZPA_DECOMPRESS_PROGRESS') === '1') {
		@fwrite(STDERR, '[fzpa] decompress tool=' . $toolId . ' arc_bytes=' . $arcLen . ' plain_len=' . $plainLen . "\n");
	}
	$exe = fractal_zip_text_compressor_discover_executable($toolId);
	if ($exe === null) {
		$exe = fractal_zip_paq_discover_executable($toolId);
	}
	if ($exe === null && ($toolId === 'parallel_cmix' || $toolId === 'parallel_phda9')) {
		$exe = fractal_zip_paq_discover_executable($toolId);
	}
	if ($exe === null) {
		throw new RuntimeException('FZPA: tool missing: ' . $toolId);
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_paq_un_' . getmypid();
	@mkdir($tmp, 0700, true);
	$arcPath = $tmp . DIRECTORY_SEPARATOR . 'arc.paq';
	$outPath = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
	file_put_contents($arcPath, $arcBytes);
	if (!fractal_zip_paq_decompress_to_file($toolId, $exe, $arcPath, $outPath)) {
		fractal_zip_enwik_recursive_remove($tmp);
		throw new RuntimeException('FZPA: decompress failed');
	}
	$plain = (string) file_get_contents($outPath);
	fractal_zip_enwik_recursive_remove($tmp);
	if ($plainLen > 0 && strlen($plain) !== $plainLen) {
		throw new RuntimeException('FZPA: plain length mismatch');
	}
	return $plain;
}

/**
 * Neural / GPU adapters — invoke external script when present.
 */
function fractal_zip_text_compressor_neural(string $modelId, string $payload, array $opts = array()): array
{
	$meta = fractal_zip_text_compressor_model_meta()[$modelId] ?? array('gpu' => false);
	$needsGpu = (bool) ($meta['gpu'] ?? false);
	$gpuAvail = fractal_zip_text_compressor_gpu_available();
	$script = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'text_compress' . DIRECTORY_SEPARATOR . $modelId . '.py';
	$exe = fractal_zip_text_compressor_discover_executable($modelId);
	if (!is_file($script) && $exe === null) {
		return fractal_zip_text_compressor_result_unavailable(
			$modelId,
			$needsGpu && !$gpuAvail ? 'gpu_required binary_missing' : 'binary_missing'
		);
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_txt_neural_' . getmypid();
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'text.in';
	$out = $tmp . DIRECTORY_SEPARATOR . 'text.out';
	$manifest = $tmp . DIRECTORY_SEPARATOR . 'manifest.json';
	file_put_contents($in, $payload);
	$t0 = microtime(true);
	$ret = -1;
	if (is_file($script)) {
		$py = trim((string) shell_exec('command -v python3 2>/dev/null')) ?: 'python3';
		$cmd = escapeshellarg($py) . ' ' . escapeshellarg($script)
			. ' compress ' . escapeshellarg($in) . ' ' . escapeshellarg($out)
			. ' ' . escapeshellarg($manifest);
		if (!empty($opts['cpu_fallback']) || ($needsGpu && !$gpuAvail)) {
			$cmd .= ' --cpu-fallback';
		}
		exec($cmd . ' 2>/dev/null', $xo, $ret);
	} elseif ($exe !== null) {
		$cmd = escapeshellarg($exe) . ' c ' . escapeshellarg($in) . ' ' . escapeshellarg($out) . ' 2>/dev/null';
		exec($cmd, $xo, $ret);
	}
	$sec = microtime(true) - $t0;
	if ($ret !== 0 || !is_file($out)) {
		fractal_zip_enwik_recursive_remove($tmp);
		return array(
			'model' => $modelId,
			'status' => $needsGpu && !$gpuAvail ? 'unavailable' : 'failed',
			'compressed_bytes' => null,
			'roundtrip_ok' => false,
			'wall_seconds' => round($sec, 6),
			'gpu_required' => $needsGpu,
			'gpu_device' => fractal_zip_text_compressor_gpu_name(),
			'error' => 'compress failed',
		);
	}
	$bytes = (string) file_get_contents($out);
	$manifestData = is_file($manifest) ? json_decode((string) file_get_contents($manifest), true) : array();
	$decompresser = (int) ($manifestData['decompresser_bytes'] ?? 0);
	$rt = false;
	if (is_file($script)) {
		$rtOut = $tmp . DIRECTORY_SEPARATOR . 'rt.txt';
		$py = trim((string) shell_exec('command -v python3 2>/dev/null')) ?: 'python3';
		exec(
			escapeshellarg($py) . ' ' . escapeshellarg($script)
			. ' decompress ' . escapeshellarg($out) . ' ' . escapeshellarg($rtOut)
			. ' ' . escapeshellarg($manifest) . ' 2>/dev/null',
			$xo2,
			$ret2
		);
		$rt = $ret2 === 0 && is_file($rtOut) && file_get_contents($rtOut) === $payload;
	}
	fractal_zip_enwik_recursive_remove($tmp);
	return array(
		'model' => $modelId,
		'status' => 'ok',
		'compressed_bytes' => strlen($bytes),
		'payload' => $bytes,
		'roundtrip_ok' => $rt,
		'wall_seconds' => round($sec, 6),
		'gpu_device' => fractal_zip_text_compressor_gpu_name(),
		'gpu_required' => $needsGpu,
		'decompresser_bytes' => $decompresser > 0 ? $decompresser : null,
		'manifest' => is_array($manifestData) ? $manifestData : array(),
	);
}

/** @return array<string, mixed> */
function fractal_zip_text_compressor_result_unavailable(string $modelId, string $reason): array
{
	return array(
		'model' => $modelId,
		'status' => 'unavailable',
		'compressed_bytes' => null,
		'roundtrip_ok' => false,
		'wall_seconds' => 0.0,
		'unavailable_reason' => $reason,
		'gpu_device' => fractal_zip_text_compressor_gpu_name(),
	);
}

/**
 * Dispatch model compression on preprocessed text payload.
 *
 * @return array<string, mixed>
 */
function fractal_zip_text_compressor_run(string $modelId, string $payload, array $opts = array()): array
{
	$modelId = strtolower(trim($modelId));
	switch ($modelId) {
		case 'zpaq9':
			return fractal_zip_text_compressor_zpaq9($payload, $opts);
		case 'paq_inner':
		case 'phda9':
		case 'paq8px':
		case 'cmix21':
		case 'parallel_cmix':
		case 'parallel_phda9':
			return fractal_zip_text_compressor_paq_family($modelId === 'paq_inner' ? 'paq_inner' : $modelId, $payload, $opts);
		case 'gpt2tc':
		case 'nncp32':
		case 'llmzip_llama7b':
		case 'tensorflow_compress':
		case 'jax_compress':
		case 'b64pack':
			return fractal_zip_text_compressor_neural($modelId, $payload, $opts);
		default:
			return fractal_zip_text_compressor_result_unavailable($modelId, 'unknown model');
	}
}

/** When set, {@see adaptive_compress} store-passthroughs inners containing FZSO (pre-stacked text-inner). */
function fractal_zip_stacked_outer_passthrough_enabled(): bool
{
	$e = getenv('FRACTAL_ZIP_STACKED_OUTER');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'true' || $v === 'on' || $v === 'yes';
}

/**
 * True when stacked text-inner wire is active (FZSO in blob or enwik ctx stack id).
 *
 * @param array<string, mixed>|null $enwikCtx
 */
function fractal_zip_stacked_outer_wire_active(?array $enwikCtx = null): bool
{
	if (!fractal_zip_stacked_outer_passthrough_enabled()) {
		return false;
	}
	if (is_array($enwikCtx)) {
		$stack = strtolower(trim((string) ($enwikCtx['textInnerStack'] ?? 'none')));
		if ($stack !== '' && $stack !== 'none') {
			return true;
		}
	}
	$stackEnv = strtolower(trim((string) (getenv('FRACTAL_ZIP_TEXT_INNER_STACK') ?: 'none')));
	return $stackEnv !== '' && $stackEnv !== 'none';
}

/** Skip native folder wire shootout (7z/arc/zpaq on raw tree) when stacked inner already pre-compressed. */
function fractal_zip_stacked_outer_skip_native_wire_compares(?array $enwikCtx = null): bool
{
	return fractal_zip_stacked_outer_wire_active($enwikCtx);
}

/** Stacked outer pairs for probe matrix. */
/** @return list<string> */
function fractal_zip_text_stacked_outer_catalog(): array
{
	return array(
		'none',
		'zpaq9_brotli11',
		'zpaq9_zstd22',
		'zpaq9_7z',
		'zpaq9_gzip9',
		'gzip9_zpaq9',
		'brotli11_zpaq9',
		'brotli11_gzip9',
		'gzip9_brotli11',
		'zstd22_zpaq9',
		'zstd22_gzip9',
		'gzip9_zstd22',
		'7z_zpaq9',
		'zpaq9_zpaq9',
	);
}

/**
 * Apply stacked outer chain to model output bytes.
 *
 * @return array{stack_id: string, layer1_bytes: int, layer2_bytes: int, total_bytes: int, payload: string, roundtrip_ok: bool}
 */
function fractal_zip_text_stacked_outer_apply(string $stackId, string $innerPayload): array
{
	$stackId = strtolower(trim($stackId));
	if ($stackId === '' || $stackId === 'none') {
		return array(
			'stack_id' => 'none',
			'layer1_bytes' => strlen($innerPayload),
			'layer2_bytes' => strlen($innerPayload),
			'total_bytes' => strlen($innerPayload),
			'payload' => $innerPayload,
			'roundtrip_ok' => true,
		);
	}
	$parts = explode('_', $stackId, 2);
	if (count($parts) !== 2) {
		throw new InvalidArgumentException('bad stack id: ' . $stackId);
	}
	$layer1 = fractal_zip_text_outer_layer_compress($parts[0], $innerPayload);
	$layer2 = fractal_zip_text_outer_layer_compress($parts[1], $layer1['payload']);
	$meta = json_encode(array('stack' => $stackId, 'layer1' => $parts[0], 'layer2' => $parts[1]), JSON_UNESCAPED_UNICODE);
	$wire = "FZSO\x01" . fractal_zip_enwik_encode_varint_u32(strlen($meta)) . (is_string($meta) ? $meta : '')
		. fractal_zip_enwik_encode_varint_u32(strlen($layer2['payload'])) . $layer2['payload'];
	return array(
		'stack_id' => $stackId,
		'layer1_bytes' => (int) $layer1['bytes'],
		'layer2_bytes' => (int) $layer2['bytes'],
		'total_bytes' => strlen($wire),
		'payload' => $wire,
		'roundtrip_ok' => (bool) ($layer1['roundtrip_ok'] && $layer2['roundtrip_ok']),
		'stack_meta_bytes' => strlen($wire) - strlen($layer2['payload']),
	);
}

/** @return array{payload: string, bytes: int, roundtrip_ok: bool} */
function fractal_zip_text_outer_layer_compress(string $codec, string $data): array
{
	$codec = strtolower($codec);
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_outer_' . getmypid();
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'in.bin';
	file_put_contents($in, $data);
	$out = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
	$ret = -1;
	switch ($codec) {
		case 'zpaq9':
			$r = fractal_zip_text_compressor_zpaq9($data);
			fractal_zip_enwik_recursive_remove($tmp);
			return array(
				'payload' => (string) ($r['payload'] ?? $data),
				'bytes' => (int) ($r['compressed_bytes'] ?? strlen($data)),
				'roundtrip_ok' => (bool) ($r['roundtrip_ok'] ?? false),
			);
		case 'brotli11':
			if (function_exists('brotli_compress')) {
				$compressed = brotli_compress($data, 11);
				if (is_string($compressed)) {
					fractal_zip_enwik_recursive_remove($tmp);
					return array('payload' => $compressed, 'bytes' => strlen($compressed), 'roundtrip_ok' => true);
				}
			}
			$gz = gzencode($data, 9);
			fractal_zip_enwik_recursive_remove($tmp);
			return array('payload' => is_string($gz) ? $gz : $data, 'bytes' => is_string($gz) ? strlen($gz) : strlen($data), 'roundtrip_ok' => is_string($gz));
		case 'zstd22':
			$cmd = 'zstd -22 -q -f -o ' . escapeshellarg($out) . ' ' . escapeshellarg($in) . ' 2>/dev/null';
			exec($cmd, $xo, $ret);
			break;
		case '7z':
			$arc = $tmp . DIRECTORY_SEPARATOR . 'out.7z';
			$cmd = '7z a -mx=9 -bd ' . escapeshellarg($arc) . ' ' . escapeshellarg($in) . ' 2>/dev/null';
			exec($cmd, $xo, $ret);
			$out = $arc;
			break;
		case 'gzip9':
		case 'gzip':
			$gz = gzencode($data, 9);
			fractal_zip_enwik_recursive_remove($tmp);
			return array(
				'payload' => is_string($gz) ? $gz : $data,
				'bytes' => is_string($gz) ? strlen($gz) : strlen($data),
				'roundtrip_ok' => is_string($gz),
			);
		case 'gzip1':
			$gz = gzencode($data, 1);
			fractal_zip_enwik_recursive_remove($tmp);
			return array(
				'payload' => is_string($gz) ? $gz : $data,
				'bytes' => is_string($gz) ? strlen($gz) : strlen($data),
				'roundtrip_ok' => is_string($gz),
			);
		default:
			fractal_zip_enwik_recursive_remove($tmp);
			return array('payload' => $data, 'bytes' => strlen($data), 'roundtrip_ok' => true);
	}
	$payload = is_file($out) ? (string) file_get_contents($out) : $data;
	fractal_zip_enwik_recursive_remove($tmp);
	return array('payload' => $payload, 'bytes' => strlen($payload), 'roundtrip_ok' => $ret === 0);
}

function fractal_zip_text_outer_layer_decompress(string $codec, string $data): string
{
	$codec = strtolower(trim($codec));
	$host = new fractal_zip(256, false, false, null, false);
	switch ($codec) {
		case 'zpaq9':
			if (str_starts_with($data, fractal_zip::OUTER_ZPAQ_MAGIC)) {
				return (string) $host->adaptive_decompress($data);
			}
			$inner = $host->unpack_raw_zpaq_archive_bytes_to_inner_string($data);
			return is_string($inner) && $inner !== '' ? $inner : $data;
		case 'brotli11':
			if (function_exists('brotli_uncompress')) {
				$u = brotli_uncompress($data);
				if (is_string($u) && $u !== '') {
					return $u;
				}
			}
			$u = @gzdecode($data);
			return is_string($u) && $u !== '' ? $u : $data;
		case 'zstd22':
			$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_outer_d_' . getmypid();
			@mkdir($tmp, 0700, true);
			$in = $tmp . DIRECTORY_SEPARATOR . 'in.zst';
			$out = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
			file_put_contents($in, $data);
			exec('zstd -d -q -f -o ' . escapeshellarg($out) . ' ' . escapeshellarg($in) . ' 2>/dev/null', $xo, $ret);
			$payload = ($ret === 0 && is_file($out)) ? (string) file_get_contents($out) : $data;
			fractal_zip_enwik_recursive_remove($tmp);
			return $payload;
		case '7z':
			$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_outer_d_' . getmypid();
			@mkdir($tmp, 0700, true);
			$arc = $tmp . DIRECTORY_SEPARATOR . 'in.7z';
			$outDir = $tmp . DIRECTORY_SEPARATOR . 'out';
			@mkdir($outDir, 0700, true);
			file_put_contents($arc, $data);
			$seven = fractal_zip::seven_zip_executable();
			if ($seven !== null) {
				exec(
					fractal_zip::shell_quote_arg_cached($seven) . ' x ' . escapeshellarg($arc) . ' -aoa -o' . escapeshellarg($outDir) . ' 2>/dev/null',
					$xo,
					$ret
				);
				if ($ret === 0) {
					foreach (scandir($outDir) ?: array() as $f) {
						if ($f === '.' || $f === '..') {
							continue;
						}
						$p = $outDir . DIRECTORY_SEPARATOR . $f;
						if (is_file($p)) {
							$payload = (string) file_get_contents($p);
							fractal_zip_enwik_recursive_remove($tmp);
							return $payload;
						}
					}
				}
			}
			fractal_zip_enwik_recursive_remove($tmp);
			return $data;
		case 'gzip9':
		case 'gzip':
		case 'gzip1':
			$u = @gzdecode($data);
			return is_string($u) && $u !== '' ? $u : $data;
		default:
			return $data;
	}
}

/** Undo {@see fractal_zip_text_stacked_outer_apply} FZSO wire blob to raw inner bytes. */
function fractal_zip_text_stacked_outer_undo(string $wire): string
{
	if (!str_starts_with($wire, "FZSO\x01")) {
		return $wire;
	}
	$off = 5;
	$metaLenDec = fractal_zip_enwik_decode_varint_u32($wire, $off);
	if ($metaLenDec === null) {
		throw new RuntimeException('FZSO: bad meta length');
	}
	$metaLen = (int) $metaLenDec[0];
	$off = (int) $metaLenDec[1];
	$metaJson = substr($wire, $off, $metaLen);
	$off += $metaLen;
	$meta = json_decode($metaJson, true);
	if (!is_array($meta)) {
		throw new RuntimeException('FZSO: bad meta json');
	}
	$payLenDec = fractal_zip_enwik_decode_varint_u32($wire, $off);
	if ($payLenDec === null) {
		throw new RuntimeException('FZSO: bad payload length');
	}
	$payLen = (int) $payLenDec[0];
	$off = (int) $payLenDec[1];
	$payload = substr($wire, $off, $payLen);
	if (strlen($payload) !== $payLen) {
		throw new RuntimeException('FZSO: truncated payload');
	}
	$layer2 = (string) ($meta['layer2'] ?? '');
	$layer1 = (string) ($meta['layer1'] ?? '');
	if ($layer2 === '' || $layer1 === '') {
		throw new RuntimeException('FZSO: missing layer ids');
	}
	$mid = fractal_zip_text_outer_layer_decompress($layer2, $payload);
	return fractal_zip_text_outer_layer_decompress($layer1, $mid);
}
