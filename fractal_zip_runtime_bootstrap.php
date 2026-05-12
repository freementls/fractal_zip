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
