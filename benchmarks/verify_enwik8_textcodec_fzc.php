#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Verify test_files109.fz roundtrip: zpaq payload sanity + FZEP restore → enwik8 SHA-256.
 *
 * Usage:
 *   php benchmarks/verify_enwik8_textcodec_fzc.php [path/to/test_files109.fz]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_FZPA_DECOMPRESS_PROGRESS=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$fzcPath = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'test_files109.fz');
$srcPath = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';

if (!is_file($fzcPath)) {
	fwrite(STDERR, "Missing fzc: {$fzcPath}\n");
	exit(1);
}
if (!is_file($srcPath)) {
	fwrite(STDERR, "Missing source: {$srcPath}\n");
	exit(1);
}

$rawHash = hash_file('sha256', $srcPath);
$rawBytes = (int) filesize($srcPath);
$fzcBytes = (int) filesize($fzcPath);

$blob = (string) file_get_contents($fzcPath);
$meta = fractal_zip_enwik_peel_fzep_from_blob($blob);
if ($meta === null) {
	fwrite(STDERR, "FAIL: no FZEP trailer on {$fzcPath}\n");
	exit(1);
}
$payload = substr($blob, 0, (int) $meta['payloadLen']);
$magic = strlen($payload) >= 4 ? substr($payload, 0, 4) : '';
$payloadLen = strlen($payload);
fwrite(STDERR, "[verify] fzc={$fzcBytes} B payload={$payloadLen} magic={$magic} fzep_v=" . ($meta['fzepVersion'] ?? '?')
	. " flags=" . ($meta['fzepFlags'] ?? 0) . " members=" . count($meta['memberRelPaths'] ?? array()) . "\n");

if ($magic === '7kSt') {
	$tmpArc = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_verify_' . bin2hex(random_bytes(6)) . '.zpaq';
	file_put_contents($tmpArc, $payload);
	$out = array();
	exec('zpaq list ' . escapeshellarg($tmpArc) . ' 2>&1', $out, $ret);
	@unlink($tmpArc);
	$list = implode("\n", $out);
	if (!preg_match('/(\d+)\s+files/', $list, $m) || (int) $m[1] === 0) {
		fwrite(STDERR, "FAIL: native zpaq payload lists 0 files (broken passthrough)\n{$list}\n");
		exit(1);
	}
	fwrite(STDERR, "[verify] zpaq list OK: {$m[1]} files\n");
}

$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_verify109_' . getmypid();
@mkdir($work, 0755, true);
$workFzc = $work . DIRECTORY_SEPARATOR . 'test_files109.fz';
copy($fzcPath, $workFzc);

$t0 = microtime(true);
$fz = new fractal_zip();
try {
	$fz->open_container($workFzc, false);
} catch (Throwable $e) {
	fwrite(STDERR, 'FAIL open_container: ' . $e->getMessage() . "\n");
	fractal_zip_enwik_recursive_remove($work);
	exit(1);
}
$sec = microtime(true) - $t0;

$outPath = $work . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($outPath)) {
	fwrite(STDERR, "FAIL: enwik8 not written under {$work}\n");
	fractal_zip_enwik_recursive_remove($work);
	exit(1);
}

$gotBytes = (int) filesize($outPath);
$gotHash = hash_file('sha256', $outPath);
$ok = ($gotBytes === $rawBytes && hash_equals($rawHash, $gotHash));

fwrite(STDERR, "[verify] extract " . number_format($sec, 1) . "s bytes={$gotBytes} sha256=" . ($ok ? 'OK' : 'MISMATCH') . "\n");
fractal_zip_enwik_recursive_remove($work);

if (!$ok) {
	fwrite(STDERR, "expected {$rawBytes} B {$rawHash}\n");
	fwrite(STDERR, "got      {$gotBytes} B {$gotHash}\n");
	exit(1);
}

echo "verify_ok=1\nfzc_bytes={$fzcBytes}\n";
