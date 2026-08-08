#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Regression: fzc_parity_hint_for_extract_error recognizes live fractal_zip fatal strings.
 */

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fzc_parity_hints.php';

$cases = array(
	array('Container uses zpaq outer format but `zpaq` was not found on PATH (set FRACTAL_ZIP_ZPAQ).', 'zpaq_missing'),
	array('Container is 7z format but no 7-Zip/p7zip binary was found. Install 7z and ensure it is on PATH (or on Windows in Program Files).', '7z_missing'),
	array('Container is FreeArc format but `arc` was not found on PATH.', 'arc_missing'),
	array('Container uses brotli outer format but `brotli` was not found on PATH.', 'brotli_missing'),
	array('Container is zstd format but `zstd` was not found on PATH.', 'zstd_missing'),
	array('Container is xz format but `xz` was not found on PATH.', 'xz_missing'),
	array('Unknown container payload format. len=64 magic4=FZHM', 'unknown_container'),
);

$fails = 0;
foreach ($cases as $c) {
	$h = fzc_parity_hint_for_extract_error($c[0]);
	if ($h === null || ($h['id'] ?? '') !== $c[1]) {
		fwrite(STDERR, "FAIL expected {$c[1]} for: {$c[0]}\n");
		$fails++;
	}
}

$fs = fzc_parity_hint_for_web_fs_failure(array(
	'ok' => false,
	'code' => 'native_outer_single_member_unsupported',
	'folder_native_wire_kind' => 'fzpa_zpaq',
));
if ($fs === null || ($fs['id'] ?? '') !== 'web_fs_selective_not_full_extract') {
	fwrite(STDERR, "FAIL web_fs native_outer hint\n");
	$fails++;
}

if ($fails > 0) {
	exit(1);
}
fwrite(STDOUT, "OK smoke_parity_hint_strings\n");
exit(0);
