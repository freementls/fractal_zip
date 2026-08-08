<?php
declare(strict_types=1);

/**
 * One-shot env wiring before fractal_zip::__construct reads getenv().
 * Invoked from the constructor; safe to call multiple times.
 *
 * FRACTAL_ZIP_PRESET — optional compression preset when individual knobs are unset:
 *   fast      multipass off, auto-tune off, FLACPAC off, modest substring depth
 *   balanced  library-ish defaults via explicit no-ops (documented for operators)
 *   ratio     multipass on, auto-tune on when segment auto (does not force auto-segment)
 *   ffs|fs    ffs / filesystem throughput profile: speed-first manual compress (~5× faster than
 *             bytes-first benches on the 59-case ≤2 MiB corpus; ~20% larger .fz). Skips
 *             peeler/multidiff/zpaq/7z/arc trials; caps lifestyle; FZB4 store-only on dense
 *             micro-member trees; folders ≥2 MiB raw → native 7z passthrough; gzip-fast auto
 *             from 128 MiB raw. Fractal agent keeps bytes-first. (`fs` / `filesystem` remain legacy aliases.)
 *
 * FRACTAL_ZIP_UNTRUSTED_INPUT=1 — tighten substring / validation caps when the matching
 * FRACTAL_ZIP_MAX_* vars are still unset (hostile containers over the network).
 */

function fractal_zip_bootstrap_runtime_env(): void {
	static $done = false;
	if ($done) {
		return;
	}
	$done = true;
	fractal_zip_apply_preset_env();
	fractal_zip_apply_untrusted_input_env();
	fractal_zip_apply_native_accel_env();
}

function fractal_zip_apply_preset_env(): void {
	$p = getenv('FRACTAL_ZIP_PRESET');
	if ($p === false || trim((string) $p) === '') {
		return;
	}
	$key = strtolower(trim((string) $p));
	$setIfUnset = static function (string $k, string $v): void {
		$cur = getenv($k);
		if ($cur === false || trim((string) $cur) === '') {
			putenv($k . '=' . $v);
		}
	};
	if ($key === 'fast') {
		$setIfUnset('FRACTAL_ZIP_MULTIPASS', '0');
		$setIfUnset('FRACTAL_ZIP_AUTO_TUNE', '0');
		$setIfUnset('FRACTAL_ZIP_FLACPAC', '0');
		$setIfUnset('FRACTAL_ZIP_MAX_RECURSIVE_SUBSTRING_DEPTH', '2048');
		return;
	}
	if ($key === 'balanced') {
		// Operator-facing label: do not override user-exported knobs.
		return;
	}
	if ($key === 'ratio' || $key === 'bytes') {
		$setIfUnset('FRACTAL_ZIP_MULTIPASS', '1');
		$setIfUnset('FRACTAL_ZIP_AUTO_TUNE', '1');
		return;
	}
	if ($key === 'ffs' || $key === 'fs' || $key === 'filesystem') {
		$setIfUnset('FRACTAL_ZIP_MULTIPASS', '0');
		$setIfUnset('FRACTAL_ZIP_AUTO_TUNE', '0');
		$setIfUnset('FRACTAL_ZIP_UNIFIED_LITERAL_MULTIPASS', '0');
		$setIfUnset('FRACTAL_ZIP_FLACPAC', '0');
		// Peeler stays on for tiny folders; capped by max total raw below.
		$setIfUnset('FRACTAL_ZIP_INNER_PEELER_MAX_TOTAL_RAW_BYTES', '65536');
		$setIfUnset('FRACTAL_ZIP_SPEED', '1');
		$setIfUnset('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '1');
		$setIfUnset('FRACTAL_ZIP_SPEED_TRY_BROTLI', '0');
		$setIfUnset('FRACTAL_ZIP_FS_FULL_FRACTAL_MAX_RAW_BYTES', '65536');
		$setIfUnset('FRACTAL_ZIP_FS_SPEED_FIRST_MIN_RAW_BYTES', '262144');
		// Manual / filesystem packs: skip expensive substring multidiff (phase 3 dominates wall time on PDFs).
		$setIfUnset('FRACTAL_ZIP_SUBSTRING_MULTIDIFF', '0');
		$setIfUnset('FRACTAL_ZIP_SUBSTRING_RECIPROCAL', '0');
		$setIfUnset('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY', '0');
		$setIfUnset('FRACTAL_ZIP_SUBSTRING_MULTIDIFF_MAX_LITERAL_JOBS', '2');
		$setIfUnset('FRACTAL_ZIP_LIFESTYLE_LITERAL_OUTER_JOB_CAP', '1');
		$setIfUnset('FRACTAL_ZIP_LIFESTYLE_SINGLE_MEMBER_LITERAL_OUTER_JOB_CAP', '1');
		$setIfUnset('FRACTAL_ZIP_FS_DISABLE_LIFESTYLE_ABOVE_RAW_BYTES', '524288');
		$setIfUnset('FRACTAL_ZIP_DISABLE_LITERAL_MERGED_FZBM', '1');
		$setIfUnset('FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES', '0');
		$setIfUnset('FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES', '134217728');
		$setIfUnset('FRACTAL_ZIP_FS_LARGE_7Z_PASSTHROUGH_MIN_RAW_BYTES', '2097152');
		$setIfUnset('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_MIN_WIRE_BYTES', '1048576');
		$setIfUnset('FRACTAL_ZIP_PIPELINE_PARALLEL', '0');
		$setIfUnset('FRACTAL_ZIP_SKIP_7Z', '1');
		$setIfUnset('FRACTAL_ZIP_SKIP_ARC', '1');
		// FS speed-first may emit native 7z; wire compare off — early passthrough + probe routing pick 7z when appropriate.
		$setIfUnset('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE', '0');
		$setIfUnset('FRACTAL_ZIP_FS_TINY_ALWAYS_FRACTAL_MAX_RAW_BYTES', '8192');
		$setIfUnset('FRACTAL_ZIP_FS_DENSE_RECIPROCAL_MIN_MEMBERS', '64');
		$setIfUnset('FRACTAL_ZIP_FS_SMALL_MULTIFILE_RECIPROCAL_MIN_MEMBERS', '8');
		$setIfUnset('FRACTAL_ZIP_FS_SMALL_MULTIFILE_RECIPROCAL_MAX_RAW_BYTES', '262144');
		$setIfUnset('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', '2097152');
		// Throughput: skip arc/brotli/xz/zpaq native compares; keep 7z for speed-first passthrough + wire compare.
		$setIfUnset('FRACTAL_ZIP_FOLDER_NATIVE_ARC_COMPARE', '0');
		$setIfUnset('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_COMPARE', '0');
		$setIfUnset('FRACTAL_ZIP_FOLDER_NATIVE_XZ_COMPARE', '0');
		$setIfUnset('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE', '0');
		$setIfUnset('FRACTAL_ZIP_FOLDER_NATIVE_SKIP_ADAPTIVE_ZPAQ', '1');
		$setIfUnset('FRACTAL_ZIP_SKIP_ZPAQ', '1');
		$setIfUnset('FRACTAL_ZIP_OUTER_PREDICT', '0');
		$setIfUnset('FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE', '0');
		$setIfUnset('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', '3');
		return;
	}
}

function fractal_zip_apply_untrusted_input_env(): void {
	$e = getenv('FRACTAL_ZIP_UNTRUSTED_INPUT');
	if ($e === false || trim((string) $e) === '') {
		return;
	}
	$v = strtolower(trim((string) $e));
	if ($v !== '1' && $v !== 'true' && $v !== 'yes') {
		return;
	}
	$setIfUnset = static function (string $k, string $val): void {
		$cur = getenv($k);
		if ($cur === false || trim((string) $cur) === '') {
			putenv($k . '=' . $val);
		}
	};
	$setIfUnset('FRACTAL_ZIP_MAX_SUBSTRING_OPERATION_SLICE_BYTES', '4194304');
	$setIfUnset('FRACTAL_ZIP_MAX_SUBSTRING_TUPLE_EXPAND_BYTES', '4194304');
	$setIfUnset('FRACTAL_ZIP_MAX_EQUIVALENCE_SUBOP_RESULT_BYTES', '50331648');
	$setIfUnset('FRACTAL_ZIP_SILENT_VALIDATE_MAX_OPERAND_BYTES', '16777216');
	$setIfUnset('FRACTAL_ZIP_MAX_RECURSIVE_SUBSTRING_DEPTH', '1024');
}

/**
 * Wire native C/Rust helpers when built and a frozen phda9 vocab is available.
 * Speed-only: byte-identical tokenize wire; PHP remains fallback. FRACTAL_ZIP_NATIVE_ACCEL=0 disables.
 */
function fractal_zip_apply_native_accel_env(): void {
	$e = getenv('FRACTAL_ZIP_NATIVE_ACCEL');
	if ($e !== false && in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true)) {
		return;
	}
	$setIfUnset = static function (string $k, string $v): void {
		$cur = getenv($k);
		if ($cur === false || trim((string) $cur) === '') {
			putenv($k . '=' . $v);
		}
	};
	$repo = dirname(__FILE__);
	$tokBin = $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'parallel_paq' . DIRECTORY_SEPARATOR . 'fz_tokenize';
	$dictCandidates = array(
		$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict_words_4096p.txt',
		$repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt',
	);
	$dictPath = null;
	foreach ($dictCandidates as $cand) {
		if (is_file($cand) && is_readable($cand)) {
			$dictPath = $cand;
			break;
		}
	}
	if ($dictPath !== null) {
		$setIfUnset('FRACTAL_ZIP_PAQ_PHDA9_DICT', $dictPath);
		// Do not force GENERAL_TEXT_INNER=on — that is a specialized bytes path
		// (CLI --text-inner / explicit env). Default lifestyle leaves it off.
	}
	require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
}
