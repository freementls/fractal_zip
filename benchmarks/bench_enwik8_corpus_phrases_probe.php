#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * gzip-1 probe: static boilerplate vs boilerplate + corpus-mined phrases (8 MiB slice).
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	exit(1);
}
$fh = fopen($src, 'rb');
$blob = fread($fh, 8388608);
fclose($fh);
if (!is_string($blob) || $blob === '') {
	exit(1);
}

$bp = fractal_zip_enwik_boilerplate_pack_apply($blob);
$b1 = (string) $bp['blob'];
$mined = fractal_zip_enwik_mine_corpus_phrases($b1, 10, 96, 32, 192);
$cp = fractal_zip_enwik_phrase_pack_apply($b1, $mined);
$b2 = (string) $cp['blob'];

$g0 = strlen(gzdeflate($blob, 1));
$g1 = strlen(gzdeflate($b1, 1));
$g2 = strlen(gzdeflate($b2, 1));

$out = array(
	'generated' => date('c'),
	'slice_bytes' => strlen($blob),
	'gzip1_raw' => $g0,
	'gzip1_boilerplate' => $g1,
	'gzip1_boilerplate_corpus' => $g2,
	'delta_bp' => $g0 - $g1,
	'delta_corpus' => $g0 - $g2,
	'mined_phrases' => count($mined),
	'corpus_dict_bytes' => strlen((string) $cp['dict']),
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_corpus_phrases_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo 'corpus phrases probe: mined=' . $out['mined_phrases'] . ' delta_corpus=' . number_format($out['delta_corpus']) . " B\n";
echo "  wrote {$path}\n";
