#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * List /DCTDecode (and other) image streams in a PDF with dimensions, bpp, and our semantic
 * lossy policy (fractal_zip_pdf_jpeg_pac). Does not re-encode; uses getimagesizefromstring on DCT bodies.
 *
 *   php pdf_dct_image_report.php [path.pdf]
 *   php pdf_dct_image_report.php test_files71/Mutwa*.pdf
 */
$base = dirname( __DIR__);
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_jpeg_pac.php';
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_objects.php';
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_dict_scan.php';

$path = $argv[1] ?? ($base . DIRECTORY_SEPARATOR . 'test_files71' . DIRECTORY_SEPARATOR);
if (is_dir( $path) ) {
	$g = glob( rtrim( $path, '/\\' ) . '/*.pdf' ) ?: array( );
	$path = (string) ( $g[0] ?? '' );
}
if ( $path === '' || !is_readable( $path) ) {
	fwrite( STDERR, "Pass a .pdf or a directory with one .pdf\n");
	exit( 1);
}

$pdf = (string) @file_get_contents( $path);
if ( $pdf === '' || strncmp( $pdf, '%PDF', 4) !== 0) {
	fwrite( STDERR, "Not a PDF: {$path}\n");
	exit( 1);
}

$omap = fractal_zip_pdf_object_index_offsets( $pdf);
$rowsDct = array( );
$rowsOther = array( );

if ( preg_match_all( '#(>>\s*stream(\r\n|\n))#', $pdf, $M, PREG_OFFSET_CAPTURE) < 1) {
	fwrite( STDERR, "No streams in PDF\n");
	exit( 0);
}

$nm = count( $M[0] );
$idx = 0;
for ( $si = 0; $si < $nm; $si++ ) {
	$e = $M[0][$si] ?? null;
	if ( !is_array( $e) || $e[1] < 0) {
		continue;
	}
	$abs0 = (int) $e[1];
	$tok = (string) $e[0];
	$open = fractal_zip_pdf_stream_dict_opening_lt_lt( $pdf, $abs0);
	if ( $open < 0) {
		continue;
	}
	$dict = (string) substr( $pdf, $open, $abs0 - $open + 2);
	$lenB = fractal_zip_pdf_dict_length_value( $pdf, $dict, $omap);
	if ( $lenB === null) {
		continue;
	}
	$dataStart = $abs0 + strlen( $tok);
	$oldEnc = (string) substr( $pdf, $dataStart, $lenB);
	$filter = 'unknown';
	if ( preg_match( '#/(?:Filter|F)\s+(\[[^\]]+\]|/[-\w#]+|[/\d.\w]+)\s#', (string) preg_replace( "/\r\n?/", "\n", ' ' . $dict . ' '), $fm) ) {
		$filter = trim( (string) $fm[1] );
	} elseif (str_contains( $dict, 'DCTDecode' ) ) {
		$filter = '/DCTDecode (inferred)';
	}
	$st = (string) preg_match( '#/(?:Subtype|/Type)\s*(/[A-Za-z0-9]+|/Image|/XObject)\s#i', ' ' . $dict, $sM) ? ( $sM[1] ?? '' ) : '';
	$idx++;
	$isDct = fractal_zip_pdf_pac_dict_dct_only( $dict);
	if ( ! $isDct) {
		$rowsOther[] = array( 'n' => $idx, 'off' => $dataStart, 'len' => $lenB, 'filter' => $filter, 'st' => $st);
		continue;
	}
	$g = @getimagesizefromstring( $oldEnc);
	if ( !is_array( $g) || !isset( $g[0], $g[1]) ) {
		$rowsDct[] = array( 'n' => $idx, 'w' => 0, 'h' => 0, 'enc' => strlen( $oldEnc), 'err' => 'getimagesize failed', 'filter' => $filter, 'st' => $st);
		continue;
	}
	$w = max( 1, (int) $g[0] );
	$h = max( 1, (int) $g[1] );
	$encB = strlen( $oldEnc);
	$px = (float) $w * (float) $h;
	$bpp = $px > 0 ? 8.0 * (float) $encB / $px : 0.0;
	$mp = $px / 1000000.0;
	$allow = fractal_zip_pdf_pac_cjpeg_semantic_allows_lossy_recompress( $w, $h, $encB);
	$minS = fractal_zip_pdf_pac_cjpeg_semantic_effective_min_savings_pct( $w, $h, $encB);
	$rowsDct[] = array(
		'n' => $idx,
		'w' => $w,
		'h' => $h,
		'enc' => $encB,
		'bpp' => $bpp,
		'mp' => $mp,
		'allow_lossy' => $allow,
		'min_savings_pct' => $minS,
		'err' => '',
		'filter' => $filter,
		'st' => $st,
		'bits' => (int) ( $g[2] ?? 0),
		'mime' => (string) ( $g['mime'] ?? '' ),
	);
}

$bn = basename( $path);
echo "PDF: {$path}\n";
echo "DCT / JPEG streams: " . (string) count( $rowsDct) . "  (pac touches single-filter DCT only; indirect Length skipped in pac.)\n";
echo "Non-DCT (this scan): " . (string) count( $rowsOther) . " streams\n\n";

echo "Embedded text (no OCR): prefer Poppler " . '`pdftotext` ' . "or MuPDF " . '`mutool draw -F text` ' . "on the .pdf when text is selectable; compare with a sidecar such as " . "`hadland.txt` for Hadland. OCR tools are a fallback for bitmap-only text.\n\n";

echo "── DCT streams: lossy per policy (min mp " . (string) fractal_zip_pdf_pac_cjpeg_semantic_lossy_min_megapixels( ) . ", PPM+min-savings+djpeg in actual encode) ──\n";
printf( "%-4s  %6s×%-6s  %8s  %7s  %7s  %-4s  %5s  %s\n", 'no', 'w', 'h', 'bytes', 'Mpix', 'BPP*', 'L?', 'min%', 'note');
printf( "%-4s  %6s  %-6s  %8s  %7s  %7s  %-4s  %5s  %s\n", '----', '------', '------', '--------', '-------', '-------', '----', '-----', '----');
printf( "     %s BPP* = 8*encBytes/(w*h), rough bits per file-byte per pixel; not JPEG 'bpp' in codec sense.\n\n", '');

foreach ( $rowsDct as $r) {
	$l = ( $r['allow_lossy'] ?? false) ? 'yes' : 'no';
	$w = (int) ( $r['w'] ?? 0);
	$h = (int) ( $r['h'] ?? 0);
	$enc = (int) ( $r['enc'] ?? 0);
	$e = (string) ( $r['err'] ?? '' );
	$minS = isset( $r['min_savings_pct'] ) ? sprintf( '%.2f', (float) $r['min_savings_pct'] ) : '—';
	if ( $e !== '' ) {
		printf( "%-4s  %6d×%-6d  %8d  %7s  %7s  %4s  %5s  %s\n", (string) $r['n'], $w, $h, $enc, '—', '—', '—', '—', $e);
	} else {
		$mp = (float) $r['mp'];
		$bp = (float) $r['bpp'];
		printf( "%-4s  %6d×%-6d  %8d  %7.4f  %7.3f  %4s  %5s  L=%s S=%s\n", (string) $r['n'], $w, $h, $enc, $mp, $bp, $l, $minS, ( $r['mime'] ?? ''), ( $r['st'] ?? '') );
	}
}
echo "\n";
echo "Legend: L?=lossy cjpeg allowed (megapixel + BPP 'crush' heuristics). min%=adaptive min savings to accept a candidate (override with FRACTAL_ZIP_PDF_JPEG_SEMANTIC_MIN_SAVINGS_PCT).\n\n";

if ( count( $rowsOther) > 0) {
	echo "── Other streams (non–DCT-only or not handled as plain DCT) — first 20 ──\n";
	$c = 0;
	foreach ( $rowsOther as $r) {
		if ( $c++ > 20) {
			break;
		}
		printf( "  #%3d  len=%-8d  off=%-10d  %s  %s\n", (int) $r['n'], (int) $r['len'], (int) $r['off'], (string) $r['filter'], (string) ( $r['st'] ?? '' ) );
	}
	if ( count( $rowsOther) > 20) {
		echo "  ... " . (string) ( count( $rowsOther) - 20) . " more\n";
	}
}
