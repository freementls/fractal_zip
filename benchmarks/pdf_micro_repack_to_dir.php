<?php
declare(strict_types=1);
/**
 * Copy each .pdf in srcDir through the literal-PAC chain (qpdf + flate + dct + jbig2 when QPDF=1) into destDir (flat).
 * Used to A/B: smaller PDF bytes before the fractal container vs default multipass on originals.
 *
 *   php pdf_micro_repack_to_dir.php test_files72_sample_micro test_files72_sample_micro_qf --order=qpdf_first
 *   php pdf_micro_repack_to_dir.php test_files72_sample_micro test_files72_sample_micro_qf_sem --order=qpdf_first --semantic-jpeg=1
 *   php pdf_micro_repack_to_dir.php test_files72_sample_micro test_files72_sample_micro_qf_sem_best --order=qpdf_first --semantic-jpeg=1 --semantic-jpeg-bias=-8 --semantic-jpeg-min-savings-pct=2.0
 * After a run, compare container bytes: run_benchmarks.php --only=… on src vs dest; on one micro tree
 * `test_files72_sample_micro_qf` won ~1.9 KiB smaller .fz than the same run on unmolested `test_files72_sample_micro` (fractal outer unchanged; smaller literals compress slightly better).
 * On that slice, `--order=registry` (flate→jpeg→qpdf) shrank PDFs slightly but produced ~16 B larger `.fz` than `qpdf_first` under the same `run_benchmarks` flags (no-multipass smoke).
 */
$base = dirname(__DIR__);
$order = 'registry';
$cjpeg = false;
$semanticJpeg = false;
$semanticJpegBias = null;
$semanticJpegMinPct = null;
for ($i = 1; $i < $argc; $i++) {
	$a = $argv[ $i ];
	if (str_starts_with($a, '--order=')) {
		$order = substr($a, 8);
	} elseif ($a === '--cjpeg=1') {
		$cjpeg = true;
	} elseif ($a === '--semantic-jpeg=1') {
		$semanticJpeg = true;
	} elseif (str_starts_with($a, '--semantic-jpeg-bias=')) {
		$semanticJpegBias = (int) substr($a, 21);
	} elseif (str_starts_with($a, '--semantic-jpeg-min-savings-pct=')) {
		$semanticJpegMinPct = (float) substr($a, 32);
	}
}
$srcA = $argv[1] ?? '';
$dstA = $argv[2] ?? '';
if ($srcA === '' || $dstA === '' || str_starts_with($srcA, '-')) {
	fwrite(
		STDERR,
		"Usage: php pdf_micro_repack_to_dir.php <src_dir> <dest_dir> [--order=registry|qpdf_first] [--cjpeg=1] [--semantic-jpeg=1] [--semantic-jpeg-bias=-8] [--semantic-jpeg-min-savings-pct=2.0]\n" .
		"  --cjpeg=1  set FRACTAL_ZIP_PDF_CJPEG=1 (djpeg PPM+cmp verify; needs cjpeg+djpeg).\n" .
		"  --semantic-jpeg-bias/-min-savings-pct  tune FRACTAL_ZIP_PDF_JPEG_SEMANTIC_* when semantic mode is on.\n"
	);
	exit(2);
}
$src = $base . DIRECTORY_SEPARATOR . $srcA;
$dst = $base . DIRECTORY_SEPARATOR . $dstA;
if (! is_dir($src)) {
	fwrite(STDERR, "not a dir: {$src}\n");
	exit(1);
}
if (is_dir($dst)) {
	fwrite(STDERR, "dest exists, rm first: {$dstA}\n");
	exit(1);
}
putenv('FRACTAL_ZIP_LITERALPAC_PDF_QPDF=1');
if ($cjpeg) {
	putenv('FRACTAL_ZIP_PDF_CJPEG=1');
}
if ($semanticJpeg) {
	putenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC=1');
	if ($semanticJpegBias !== null) {
		putenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_QUALITY_BIAS=' . (string) $semanticJpegBias);
	}
	if ($semanticJpegMinPct !== null) {
		putenv('FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MIN_SAVINGS_PCT=' . (string) $semanticJpegMinPct);
	}
}
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
$st = $order === 'qpdf_first'
	? array('pdf_qpdf', 'pdf_native_flate', 'pdf_dct_jpeg', 'pdf_jbig2', 'pdf_jpx', 'pdf_ccitt')
	: array('pdf_native_flate', 'pdf_dct_jpeg', 'pdf_jbig2', 'pdf_jpx', 'pdf_ccitt', 'pdf_qpdf');
$paths = glob($src . '/*.pdf') ?: array();
sort($paths, SORT_STRING);
if ($paths === array()) {
	fwrite(STDERR, "no pdf in src\n");
	exit(1);
}
@mkdir($dst, 0755, true);
foreach ($paths as $p) {
	$w = (string) file_get_contents($p);
	foreach ($st as $h) {
		$r = fractal_zip_literal_pac_run_registry_handler($h, $w);
		if (is_array($r) && isset($r[0], $r[1]) && (int) $r[1] > 0) {
			$w = (string) $r[0];
		}
	}
	$leaf = basename($p);
	if (file_put_contents($dst . DIRECTORY_SEPARATOR . $leaf, $w) === false) {
		fwrite(STDERR, "write failed: {$leaf}\n");
		exit(1);
	}
}
$sumO = 0;
$sumN = 0;
foreach ($paths as $p) {
	$sumO += (int) @filesize($p);
	$sumN += (int) @filesize($dst . DIRECTORY_SEPARATOR . basename($p));
}
fwrite(
	STDERR,
	sprintf("pdf_micro_repack  order=%s  %d files  in %d B  out %d B  (%.2f%%)\n", $order, count($paths), $sumO, $sumN, $sumO > 0 ? 100.0 * ($sumO - $sumN) / $sumO : 0.0)
);
