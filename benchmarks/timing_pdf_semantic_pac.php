#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Wall-time the full PDF literal-PAC chain in a **fresh PHP process** per mode
 * (semantic env is read once; subprocess avoids stale static caches).
 * Usage: php timing_pdf_semantic_pac.php [file.pdf]
 */
$base = dirname(__DIR__);
$path = $argv[1] ?? null;
if ($path === null) {
	$g = glob($base . '/test_files72_sample_micro/*.pdf') ?: array();
	$g0 = $g[0] ?? (glob($base . '/test_files71/*.pdf')[0] ?? null);
	$path = is_string($g0) ? $g0 : null;
}
if (!is_string($path) || !is_readable($path)) {
	fwrite(STDERR, "Not found. Pass a .pdf path (e.g. test_files71/…).\n");
	exit(1);
}
$path = (string) $path;
$st = @stat($path);
$n0s = (is_array($st) && isset($st['size'])) ? (string) (int) $st['size'] : '?';
$md = getenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MAX_PPM_CHANNEL_DIFF');
$mdS = (is_string($md) && $md !== '' && is_numeric($md)) ? (string) (int) $md : '4 (default when semantic=1)';

function run_pac_subprocess(string $path, string $base, bool $semantic): array {
	$env = 'FRACTAL_ZIP_PDF_JPEG_SEMANTIC=' . ($semantic ? '1' : '0') . ' ';
	$cmd = 'cd ' . escapeshellarg($base) . ' && ' . $env . escapeshellarg(PHP_BINARY) . ' '
		. escapeshellarg($base . '/benchmarks/pdf_pac_bytes_breakdown.php') . ' ' . escapeshellarg($path) . ' 2>&1';
	$t0 = microtime(true);
	$out = (string) shell_exec($cmd);
	$dt = microtime(true) - $t0;
	$n0 = 0;
	$outB = 0;
	$sav = 0;
	if (preg_match('/\((\d+)\s+B\)\n/', $out, $m)) {
		$n0 = (int) $m[1];
	}
	if (preg_match('/Combined.*save vs file:\s*(\d+)\s+\([^\n]*\)\s*out\s+(\d+)\s+B/', $out, $m)) {
		$sav = (int) $m[1];
		$outB = (int) $m[2];
	} elseif (preg_match('/\s*out\s+(\d+)\s+B\s*$/m', $out, $m)) {
		$outB = (int) $m[1];
		$sav = $n0 > 0 && $outB > 0 ? $n0 - $outB : 0;
	}
	return array('s' => $dt, 'out' => $out, 'n0' => $n0, 'out_b' => $outB, 'sav' => $sav);
}

echo "file: {$path} ({$n0s} B)\n";
echo "PPM channel gate: FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MAX_PPM_CHANNEL_DIFF={$mdS}\n\n";

$a0 = run_pac_subprocess($path, $base, false);
$a1 = run_pac_subprocess($path, $base, true);

printf(
	"FRACTAL_ZIP_PDF_JPEG_SEMANTIC=0  wall_s=%.3f  out=%d B  saved~=%d\n",
	$a0['s'],
	(int) $a0['out_b'],
	(int) $a0['sav'],
);
printf(
	"FRACTAL_ZIP_PDF_JPEG_SEMANTIC=1  wall_s=%.3f  out=%d B  saved~=%d\n",
	$a1['s'],
	(int) $a1['out_b'],
	(int) $a1['sav'],
);
$ob0 = (int) $a0['out_b'];
$ob1 = (int) $a1['out_b'];
if ($ob0 > 0 && $ob1 > 0) {
	printf("extra_prebundle_bytes(0 minus 1)=%d  semantic_PPM+djpeg_wall_delta_s=%.3f\n", $ob0 - $ob1, $a1['s'] - $a0['s']);
}
