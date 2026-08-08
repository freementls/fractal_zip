<?php
declare(strict_types=1);
/**
 * Shell out to benchmarks/pdf_rebuild_text_from_raster_ocr.py (OCR → vector-text PDF).
 *
 *   php examples/pdf_ocr_text_rebuild.php
 *   php examples/pdf_ocr_text_rebuild.php /path/in.pdf /path/out.pdf -- --max-pages 3 --lang eng
 *
 * Arguments after ``--`` are passed through to the Python script (include leading ``--`` for flags).
 *
 * Requires: Python 3, PyMuPDF, tesseract on PATH.
 */
$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$py = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'pdf_rebuild_text_from_raster_ocr.py';
$defIn = $repo . DIRECTORY_SEPARATOR . 'test_files72_sample_micro' . DIRECTORY_SEPARATOR . 'Hadland_Davis_-_The_Persian_Mystics_Jami.pdf';
$defOut = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'ocr_rebuild_output' . DIRECTORY_SEPARATOR . 'Hadland_ocr_text_v1.pdf';

$argv = $_SERVER['argv'] ?? array();
$dash = array_search('--', $argv, true);
$pre = $dash === false ? array_slice($argv, 1) : array_slice($argv, 1, $dash - 1);
$extra = $dash === false ? array() : array_slice($argv, $dash + 1);

$in = $pre[0] ?? $defIn;
$out = $pre[1] ?? $defOut;

if (!is_file($py)) {
	fwrite(STDERR, "missing script: {$py}\n");
	exit(1);
}
if (!is_readable($in)) {
	fwrite(STDERR, "input not readable: {$in}\n");
	exit(1);
}

$cmd = array_merge(
	array('python3', $py, '--input', $in, '--output', $out),
	$extra
);
passthru(implode(' ', array_map('escapeshellarg', $cmd)), $ex);
exit($ex);
