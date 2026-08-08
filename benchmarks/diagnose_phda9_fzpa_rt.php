#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Diagnose FZPA plain_len vs phda9 decompress on phda9_xml inner members.
 *
 * Peels FZEP + outer codec, reads FZB4 .inner members directly (zstd outers are not web-fs listable).
 *
 * Usage: php benchmarks/diagnose_phda9_fzpa_rt.php [test_files109.fz]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_FZPA_DECOMPRESS_PROGRESS=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_low_memory_env.php';
putenv('FRACTAL_ZIP_LOW_MEMORY=1');
bench_low_memory_apply_env();
bench_world_record_apply_pp96_core_env();

$lock = bench_low_memory_try_diagnostic_lock($repo, 'phda9_fzpa_rt');
if ($lock === null) {
	fwrite(STDERR, "Another diagnostic is already running (see benchmarks/logs/.lock_diagnostic.pid). Exiting.\n");
	exit(2);
}
register_shutdown_function(static function () use ($lock): void {
	flock($lock, LOCK_UN);
	fclose($lock);
});

$fzcPath = $argv[1] ?? ($repo . '/test_files109.fz');
$fzcAbs = realpath($fzcPath);
if ($fzcAbs === false || !is_file($fzcAbs)) {
	fwrite(STDERR, "Missing {$fzcPath}\n");
	exit(1);
}

$blob = (string) file_get_contents($fzcAbs);
$meta = fractal_zip_enwik_peel_fzep_from_blob($blob);
if ($meta === null) {
	fwrite(STDERR, "FAIL: no FZEP trailer\n");
	exit(1);
}
$payload = substr($blob, 0, (int) $meta['payloadLen']);
$fz = new fractal_zip();
$inner = $fz->adaptive_decompress($payload);
if ($inner === '' || substr($inner, 0, 4) !== 'FZB4') {
	fwrite(STDERR, 'FAIL: inner not FZB4 after outer decompress (magic=' . bin2hex(substr($inner, 0, 4)) . ")\n");
	exit(1);
}

$tmpInner = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_diag_fzb4_' . getmypid();
file_put_contents($tmpInner, $inner);
$members = $fz->fzb4_try_list_member_paths_from_bundle_path($tmpInner);
if ($members === null) {
	@unlink($tmpInner);
	fwrite(STDERR, "FAIL: FZB4 member list\n");
	exit(1);
}
printf("members=%d fzep_v=%s payload=%s B inner=%s B\n",
	count($members),
	(string) ($meta['fzepVersion'] ?? '?'),
	number_format(strlen($payload)),
	number_format(strlen($inner))
);

$hadFail = false;
foreach ($members as $rel) {
	if (!str_ends_with((string) $rel, '.inner')) {
		continue;
	}
	fwrite(STDERR, "[diag] reading {$rel} …\n");
	$wire = $fz->fzb4_try_read_member_bytes_from_bundle_path($tmpInner, (string) $rel);
	if ($wire === null) {
		printf("  %-40s read FAIL\n", $rel);
		$hadFail = true;
		continue;
	}
	if (!str_starts_with($wire, FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC)) {
		printf("  %-40s not FZPA (%s B)\n", $rel, number_format(strlen($wire)));
		continue;
	}
	$pos = strlen(FRACTAL_ZIP_TEXT_PAQ_WIRE_MAGIC);
	$plainDec = fractal_zip_enwik_decode_varint_u32($wire, $pos);
	if ($plainDec === null) {
		printf("  %-40s bad plain_len varint\n", $rel);
		$hadFail = true;
		continue;
	}
	$storedPlain = (int) $plainDec[0];
	$pos = (int) $plainDec[1];
	$toolDec = fractal_zip_enwik_decode_varint_u32($wire, $pos);
	$toolLen = (int) $toolDec[0];
	$pos = (int) $toolDec[1];
	$toolId = substr($wire, $pos, $toolLen);
	$pos += $toolLen;
	$arcDec = fractal_zip_enwik_decode_varint_u32($wire, $pos);
	$arcLen = (int) $arcDec[0];
	$pos = (int) $arcDec[1];
	$arcBytes = substr($wire, $pos, $arcLen);

	$exe = fractal_zip_paq_discover_executable($toolId);
	$tmp = sys_get_temp_dir() . '/fz_diag_paq_' . getmypid();
	@mkdir($tmp, 0700, true);
	$arcPath = $tmp . '/arc';
	$outPath = $tmp . '/out';
	file_put_contents($arcPath, $arcBytes);
	fwrite(STDERR, "[diag] phda9 decompress tool={$toolId} stored_plain="
		. number_format($storedPlain) . ' arc=' . number_format(strlen($arcBytes)) . " B …\n");
	$ok = $exe !== null && fractal_zip_paq_decompress_to_file($toolId, $exe, $arcPath, $outPath);
	$got = $ok && is_file($outPath) ? strlen((string) file_get_contents($outPath)) : -1;
	$delta = $got >= 0 ? $got - $storedPlain : PHP_INT_MAX;
	$rtOk = ($got === $storedPlain);
	if (!$rtOk) {
		$hadFail = true;
	}
	printf("  %-40s tool=%-12s stored=%s got=%s Δ=%s RT=%s\n",
		$rel,
		$toolId,
		number_format($storedPlain),
		$got >= 0 ? number_format($got) : 'FAIL',
		$got >= 0 ? sprintf('%+d', $delta) : '?',
		$rtOk ? 'ok' : 'FAIL'
	);
	fractal_zip_enwik_recursive_remove($tmp);
}
@unlink($tmpInner);
exit($hadFail ? 1 : 0);
