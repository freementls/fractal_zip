<?php
declare(strict_types=1);
/**
 * Smoke: run pdf_jbig2 literal-PAC on one PDF; prints saved bytes or “no-op” if tools missing / no shrink.
 *
 *   php benchmarks/pdf_jbig2_pac_smoke.php [path/to.pdf]
 *
 * Requires jbig2dec on PATH; optional jbig2 or jbig2enc encoder for non–no-op runs.
 */
$base = dirname(__DIR__);
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
$def = $base . '/test_files72_sample_micro/Hadland_Davis_-_The_Persian_Mystics_Jami.pdf';
$path = $argv[1] ?? $def;
if (!is_readable($path)) {
	fwrite(STDERR, "not readable: {$path}\n");
	exit(1);
}
$raw = (string) file_get_contents($path);
$n0 = strlen($raw);
$r = fractal_zip_literal_pac_run_registry_handler('pdf_jbig2', $raw);
if (!is_array($r) || !isset($r[0], $r[1]) || (int) $r[1] <= 0) {
	echo "pdf_jbig2: no-op (need jbig2dec + jbig2 or jbig2enc on PATH; or no lossless shrink)  file={$path}  bytes={$n0}\n";
	exit(0);
}
$s = (int) $r[1];
echo "pdf_jbig2: saved {$s} B (" . ($n0 > 0 ? number_format(100.0 * $s / $n0, 3) : '0') . "%)  file={$path}  {$n0} -> " . (string) (strlen((string) $r[0])) . "\n";
exit(0);
