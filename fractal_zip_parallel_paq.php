<?php
declare(strict_types=1);

/**
 * In-repo parallel_paq member wrapper for sorted text-inner (FZPA wire).
 *
 * Env:
 *   FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ — parallel_cmix | parallel_phda9 | 0 (off)
 *   FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ_FORCE — 1 to wrap even when FZPA wire ≥ gzip(inner)
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';

function fractal_zip_parallel_paq_member_tool_id(): ?string
{
	$e = getenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ');
	if ($e === false || trim((string) $e) === '' || trim((string) $e) === '0') {
		return null;
	}
	$id = strtolower(trim((string) $e));
	if (!in_array($id, array('parallel_cmix', 'parallel_phda9'), true)) {
		return null;
	}
	return fractal_zip_paq_discover_executable($id) !== null ? $id : null;
}

/** @param list<string> $vocab */
/** @param array<string, mixed> $preprocessMeta */
function fractal_zip_parallel_paq_restore_dict_from_preprocess_meta(array $preprocessMeta, string $innerFoldBlob = ''): ?string
{
	static $cachedKey = null;
	static $cachedPath = null;
	$fold = (string) ($preprocessMeta['fold_dict_inner_blob'] ?? '');
	if ($fold === '' && $innerFoldBlob !== '') {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
		$fromTrailer = fractal_zip_enwik_inner_fold_preprocess_meta_from_trailer($innerFoldBlob);
		if (is_array($fromTrailer)) {
			$fold = (string) ($fromTrailer['fold_dict_inner_blob'] ?? '');
		}
	}
	if ($fold === '') {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_inner_dict.php';
	$vocab = fractal_zip_enwik_inner_fold_decode_vocab($fold);
	if ($vocab === array()) {
		return null;
	}
	$key = hash('xxh128', implode("\0", $vocab));
	if ($cachedKey === $key && $cachedPath !== null && is_file($cachedPath)) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $cachedPath);
		return $cachedPath;
	}
	$cachedPath = fractal_zip_parallel_paq_vocab_dict_path($vocab);
	if ($cachedPath === null) {
		return null;
	}
	$cachedKey = $key;
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $cachedPath);
	return $cachedPath;
}

/** @param list<string> $vocab */
function fractal_zip_parallel_paq_vocab_dict_path(array $vocab): ?string
{
	if ($vocab === array()) {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_vocab_dict_' . getmypid() . '.txt';
	try {
		fractal_zip_phda9_dict_write_file($vocab, $tmp);
	} catch (Throwable $e) {
		return null;
	}
	return is_file($tmp) ? $tmp : null;
}

/**
 * Wrap fztx inner blob with parallel_paq if env enabled and roundtrip-safe.
 *
 * @param list<string> $vocab optional phda9_inner shared vocab for parallel_phda9
 */
function fractal_zip_parallel_paq_try_wrap_member(string $innerBlob, array $vocab = array()): ?string
{
	$toolId = fractal_zip_parallel_paq_member_tool_id();
	if ($toolId === null || $innerBlob === '') {
		return null;
	}
	$exe = fractal_zip_paq_discover_executable($toolId);
	if ($exe === null) {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_wrap_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$inPath = $tmp . DIRECTORY_SEPARATOR . 'in.bin';
	file_put_contents($inPath, $innerBlob);
	$dictPath = null;
	$prevDict = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	if ($toolId === 'parallel_phda9') {
		if ($vocab !== array()) {
			$dictPath = fractal_zip_parallel_paq_vocab_dict_path($vocab);
			if ($dictPath !== null) {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
			}
		} elseif ($prevDict === false || trim((string) $prevDict) === '') {
			$fromEnv = fractal_zip_paq_phda9_dict_path();
			if ($fromEnv !== null) {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $fromEnv);
			}
		}
	}
	$r = fractal_zip_paq_compress_file($toolId, $exe, $inPath);
	if (!is_string($r['bytes'] ?? null) || $r['bytes'] === '') {
		if ($prevDict === false) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
		} else {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prevDict);
		}
		if ($dictPath !== null) {
			@unlink($dictPath);
		}
		fractal_zip_enwik_recursive_remove($tmp);
		return null;
	}
	$arcPath = $tmp . DIRECTORY_SEPARATOR . 'arc.fzpp';
	$outPath = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
	file_put_contents($arcPath, (string) $r['bytes']);
	$rtOk = fractal_zip_paq_decompress_to_file($toolId, $exe, $arcPath, $outPath);
	$plain = $rtOk ? (string) file_get_contents($outPath) : '';
	if ($prevDict === false) {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
	} else {
		putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prevDict);
	}
	if ($dictPath !== null) {
		@unlink($dictPath);
	}
	fractal_zip_enwik_recursive_remove($tmp);
	if (!$rtOk || $plain !== $innerBlob) {
		return null;
	}
	$wire = fractal_zip_text_paq_wire_wrap($toolId, (string) $r['bytes'], strlen($innerBlob));
	if (strlen($wire) >= strlen($innerBlob)) {
		return null;
	}
	$force = getenv('FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ_FORCE');
	$forceOn = $force !== false && trim((string) $force) !== ''
		&& !in_array(strtolower(trim((string) $force)), array('0', 'off', 'false', 'no'), true);
	if (!$forceOn) {
		$gz = @gzencode($innerBlob, 6);
		if (is_string($gz) && strlen($wire) >= strlen($gz)) {
			return null;
		}
	}
	return $wire;
}
