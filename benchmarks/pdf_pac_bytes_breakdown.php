<?php
declare(strict_types=1);
/**
 * Print per-handler byte savings for a .pdf (native Flate, DCT jpeg, optional qpdf) in production order.
 * qpdf is off unless FRACTAL_ZIP_LITERALPAC_PDF_QPDF=1.
 * Usage: php pdf_pac_bytes_breakdown.php [file.pdf]
 */
$base = dirname( __DIR__);
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';
$gMicro = glob( $base . '/test_files72_sample_micro/*.pdf' ) ?: array();
$g72 = glob( $base . '/test_files72_sample/*.pdf' ) ?: array();
$g71 = glob( $base . '/test_files71/*.pdf' ) ?: array();
$def = ( $gMicro[0] ?? $g72[0] ?? $g71[0] ?? ( $base . '/test_files71/sample.pdf' ) );
$arg = $argv[1] ?? $def;
if ( !is_readable( $arg) ) {
	fwrite( STDERR, "File not found: $arg\n");
	exit(1);
}
$raw = (string) file_get_contents( $arg);
$n0 = strlen( $raw);
$w = $raw;
$steps = array(
	'pdf_native_flate' => 'Native /FlateDecode recompress (PHP)',
	'pdf_dct_jpeg'     => 'jpegtran lossless multi-candidate on /DCTDecode',
	'pdf_jbig2'        => 'JBIG2Decode re-encode (jbig2dec -e + jbig2/jbig2enc when installed)',
	'pdf_jpx'          => 'JPXDecode try-smaller (OpenJPEG)',
	'pdf_ccitt'        => 'CCITTFaxDecode G4 try-smaller (fax2tiff/tiffcp/convert)',
	'pdf_qpdf'         => 'qpdf --object-streams=generate (needs FRACTAL_ZIP_LITERALPAC_PDF_QPDF=1)',
);
$sum = 0;
echo "File: " . $arg . " (" . (string) $n0 . " B)\n";
$qon = getenv( 'FRACTAL_ZIP_LITERALPAC_PDF_QPDF' );
echo "FRACTAL_ZIP_LITERALPAC_PDF_QPDF: " . ( is_string( $qon) && $qon !== '' ? (string) $qon : '(unset, qpdf step no-op)' ) . "\n";
$sem = getenv( 'FRACTAL_ZIP_PDF_JPEG_SEMANTIC' );
echo "FRACTAL_ZIP_PDF_JPEG_SEMANTIC: " . (is_string( $sem) && $sem !== '' ? (string) $sem : '(unset = off)')
	. "  " . 'MAX_PPM_CH=' . (getenv( 'FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MAX_PPM_CHANNEL_DIFF' ) ?: '4 when semantic=1') . "\n\n";
foreach ( $steps as $h => $label) {
	$r = fractal_zip_literal_pac_run_registry_handler( $h, $w);
	$sav = 0;
	if (is_array( $r) && isset( $r[0], $r[1] ) && (int) $r[1] > 0) {
		$w = (string) $r[0];
		$sav = (int) $r[1];
		$sum += $sav;
	}
	$pct = $n0 > 0 ? 100.0 * $sav / $n0 : 0.0;
	printf( "%-16s  saved %7d  (%5.2f %%)  %s\n", (string) $h, (int) $sav, $pct, (string) $label);
}
echo "--------------------------------------------------\n";
printf( "Combined (sequential) save vs file:  %7d  (%5.2f %%)  out %7d B\n", (int) ( $n0 - strlen( $w) ), 100.0 * ( $n0 - strlen( $w) ) / ( $n0 > 0 ? $n0 : 1), (int) strlen( $w) );
echo "Note: literal PAC also applies gzip/brotli/etc. at bundle time; this is the PDF pre-pass only.\n";
