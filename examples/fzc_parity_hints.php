<?php
declare(strict_types=1);

/**
 * Map extract/decode errors to host-parity fixes (no fractal_zip load).
 *
 * @return array{id: string, message: string, fix: string, doc: string}|null
 */
function fzc_parity_hint_for_extract_error(string $errorMessage): ?array {
	$m = $errorMessage;
	$doc = 'docs/WEB_LOCAL_PARITY.md';
	$patterns = array(
		array(
			'id' => 'zpaq_missing',
			'need' => static function (string $s): bool {
				return stripos($s, 'zpaq') !== false
					&& (stripos($s, 'PATH') !== false || stripos($s, 'not found') !== false || stripos($s, 'FRACTAL_ZIP_ZPAQ') !== false);
			},
			'message' => 'This archive needs the zpaq binary on the extract host (native FZPA/FZzq outer or zpaq passthrough).',
			'fix' => 'Install zpaq and set FRACTAL_ZIP_ZPAQ in examples/fz_fractal_local_env.php; run examples/fzc_capability_report.php?json=1 on the server.',
		),
		array(
			'id' => '7z_missing',
			'need' => static function (string $s): bool {
				return stripos($s, '7z format') !== false
					|| stripos($s, '7-Zip') !== false
					|| stripos($s, 'p7zip') !== false
					|| (stripos($s, '7z') !== false && stripos($s, 'PATH') !== false);
			},
			'message' => 'This archive needs 7-Zip (7z) on the extract host.',
			'fix' => 'Install p7zip/7z and set FRACTAL_ZIP_7Z in fz_fractal_local_env.php.',
		),
		array(
			'id' => 'zstd_missing',
			'need' => static function (string $s): bool {
				return stripos($s, 'zstd') !== false && stripos($s, 'not found') !== false;
			},
			'message' => 'This archive needs zstd on the extract host.',
			'fix' => 'Install zstd and set FRACTAL_ZIP_ZSTD in fz_fractal_local_env.php.',
		),
		array(
			'id' => 'arc_missing',
			'need' => static function (string $s): bool {
				return stripos($s, 'FreeArc') !== false
					|| (stripos($s, 'arc') !== false && stripos($s, 'not found') !== false);
			},
			'message' => 'This archive needs FreeArc (arc) on the extract host.',
			'fix' => 'Install arc and set FRACTAL_ZIP_ARC (or freearc path) in fz_fractal_local_env.php.',
		),
		array(
			'id' => 'brotli_missing',
			'need' => static function (string $s): bool {
				return stripos($s, 'brotli') !== false && (stripos($s, 'PATH') !== false || stripos($s, 'not found') !== false);
			},
			'message' => 'This archive needs brotli on the extract host.',
			'fix' => 'Install brotli and set FRACTAL_ZIP_BROTLI in fz_fractal_local_env.php.',
		),
		array(
			'id' => 'xz_missing',
			'need' => static function (string $s): bool {
				return stripos($s, 'xz') !== false && (stripos($s, 'PATH') !== false || stripos($s, 'not found') !== false);
			},
			'message' => 'This archive needs xz on the extract host.',
			'fix' => 'Install xz and set FRACTAL_ZIP_XZ in fz_fractal_local_env.php.',
		),
		array(
			'id' => 'unknown_container',
			'need' => static function (string $s): bool {
				return stripos($s, 'Unknown container payload format') !== false;
			},
			'message' => 'The outer wrapper could not be decoded (often a truncated upload, wrong file, or missing outer tool).',
			'fix' => 'Verify the .fz uploaded completely; compare local vs live with fzc_capability_report.php; ensure proc_open is enabled.',
		),
		array(
			'id' => 'proc_disabled',
			'need' => static function (string $s): bool {
				return stripos($s, 'proc_open') !== false || stripos($s, 'shell_exec') !== false;
			},
			'message' => 'PHP cannot run external compressors on this host.',
			'fix' => 'Remove proc_open/shell_exec from disable_functions for the web SAPI.',
		),
	);
	foreach ($patterns as $p) {
		if (($p['need'])($m)) {
			return array(
				'id' => $p['id'],
				'message' => $p['message'],
				'fix' => $p['fix'],
				'doc' => $doc,
			);
		}
	}
	return null;
}

/**
 * Attach parity_hint to a failed web JSON payload when the error matches a known host gap.
 *
 * @param array<string, mixed> $payload
 * @return array<string, mixed>
 */
function fzc_web_attach_parity_hint(array $payload): array {
	if (!empty($payload['ok']) || !isset($payload['error']) || !is_string($payload['error']) || isset($payload['parity_hint'])) {
		return $payload;
	}
	$hint = fzc_parity_hint_for_extract_error($payload['error']);
	if ($hint !== null) {
		$payload['parity_hint'] = $hint;
	}
	return $payload;
}

/**
 * Hint when selective web-FS APIs refuse native folder outers (not a missing-format error).
 *
 * @param array<string, mixed> $bridgeResult
 * @return array{id: string, message: string, fix: string, doc: string}|null
 */
function fzc_parity_hint_for_web_fs_failure(array $bridgeResult): ?array {
	if (!empty($bridgeResult['ok'])) {
		return null;
	}
	$code = (string) ($bridgeResult['code'] ?? $bridgeResult['error'] ?? '');
	if ($code === 'native_outer_single_member_unsupported') {
		$kind = isset($bridgeResult['folder_native_wire_kind']) ? (string) $bridgeResult['folder_native_wire_kind'] : '';
		$extra = $kind !== '' ? " ({$kind})" : '';
		return array(
			'id' => 'web_fs_selective_not_full_extract',
			'message' => 'Selective list/read cannot decode this native outer' . $extra . '; use full extract (fzc_extract.php / open_container) with the same tools as encode.',
			'fix' => 'Run examples/fzc_capability_report.php on the server; see docs/WEB_LOCAL_PARITY.md.',
			'doc' => 'docs/WEB_LOCAL_PARITY.md',
		);
	}
	if ($code === 'fzcd_single_member_unsupported') {
		return array(
			'id' => 'web_fs_fzcd_unsupported',
			'message' => 'FZCD merged FLAC bundles need full extract, not selective member read.',
			'fix' => 'Use fzc_extract.php or CLI open_container for this archive.',
			'doc' => 'docs/FRACTAL_ZIP_READINESS.md',
		);
	}
	$err = isset($bridgeResult['error']) && is_string($bridgeResult['error']) ? $bridgeResult['error'] : $code;
	return $err !== '' ? fzc_parity_hint_for_extract_error($err) : null;
}

/**
 * @param array<string, mixed> $bridgeResult
 * @return array<string, mixed>
 */
function fzc_web_fs_bridge_attach_parity_hint(array $bridgeResult): array {
	if (!empty($bridgeResult['ok']) || isset($bridgeResult['parity_hint'])) {
		return $bridgeResult;
	}
	$hint = fzc_parity_hint_for_web_fs_failure($bridgeResult);
	if ($hint !== null) {
		$bridgeResult['parity_hint'] = $hint;
	}
	return $bridgeResult;
}

/**
 * @param array<string, mixed> $row
 */
function fzc_parity_stderr_fix_line(array $row, string $prefix): void {
	if (PHP_SAPI !== 'cli' || !empty($row['ok'])) {
		return;
	}
	$hint = $row['parity_hint'] ?? fzc_parity_hint_for_web_fs_failure($row);
	if (is_array($hint) && !empty($hint['fix'])) {
		fwrite(STDERR, $prefix . 'parity: ' . (string) $hint['fix'] . "\n");
	}
}
