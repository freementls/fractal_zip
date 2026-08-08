<?php
declare(strict_types=1);
/**
 * Sweep FRACTAL_ZIP_PDF_JBIG2_ENCODE_FLAG_SETS on test_files72_sample_micro (PDF bytes + % of corpus raw).
 *
 *   php benchmarks/jbig2_pac_variant_sweep_micro.php
 *   FRACTAL_ZIP_PDF_JBIG2_ENCODE_FLAG_SETS='-d||-s' php benchmarks/jbig2_pac_variant_sweep_micro.php
 *
 * Rows default to a small grid of pipe-separated flag lists; unset env between runs (static cache in PAC).
 */
$base = dirname(__DIR__);
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_jbig2_pac.php';

$microRaw = 4360977;
$pdf = $base . DIRECTORY_SEPARATOR . 'test_files72_sample_micro' . DIRECTORY_SEPARATOR . 'Hadland_Davis_-_The_Persian_Mystics_Jami.pdf';
if (!is_readable($pdf)) {
	fwrite(STDERR, "missing {$pdf}\n");
	exit(1);
}

$rows = array(
	'-d||-s|-s -a|-d -a|-a',
	'-d||-s',
	'||-s',
	'-d',
	'',
);

$had = (string) file_get_contents($pdf);
$n0 = strlen($had);
echo "Hadland raw={$n0}  micro_corpus_raw={$microRaw}  (10% target raw={$microRaw}*0.1=" . (int) round($microRaw * 0.1) . " B — not reachable via lossless JBIG2 alone)\n\n";

foreach ($rows as $spec) {
	putenv('FRACTAL_ZIP_PDF_JBIG2_ENCODE_FLAG_SETS=' . $spec);
	$r = fractal_zip_pdf_jbig2_pac_recompress_smaller($had);
	$s = is_array($r) ? (int) $r[1] : 0;
	$pctCorpus = $microRaw > 0 ? 100.0 * $s / $microRaw : 0.0;
	$pctHad = $n0 > 0 ? 100.0 * $s / $n0 : 0.0;
	echo "FLAGS {$spec}\n  hadland_jbig2_saved={$s} B  ({pctHad}% of Hadland)  ({pctCorpus}% of micro raw)\n\n";
}

putenv('FRACTAL_ZIP_PDF_JBIG2_ENCODE_FLAG_SETS');
