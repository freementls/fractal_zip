#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Full-file gzip-1 probe: raw vs harmony pre-transforms (no 20min encode).
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	exit(1);
}
$blob = file_get_contents($src);
if (!is_string($blob) || $blob === '') {
	exit(1);
}

$raw = $blob;
$bp = fractal_zip_enwik_boilerplate_pack_apply($raw);
$b1 = (string) $bp['blob'];
$mined = fractal_zip_enwik_mine_corpus_phrases($b1, 10, 96, 32, 192);
$cp = fractal_zip_enwik_phrase_pack_apply($b1, $mined);
$b2 = (string) $cp['blob'];

$g0 = strlen(gzdeflate($raw, 1));
$g1 = strlen(gzdeflate($b1, 1));
$g2 = strlen(gzdeflate($b2, 1));

$out = array(
	'generated' => date('c'),
	'raw_bytes' => strlen($raw),
	'gzip1_raw' => $g0,
	'gzip1_harmony_pre' => $g2,
	'delta_bytes' => $g0 - $g2,
	'pct' => round(100.0 * ($g0 - $g2) / max(1, $g0), 4),
	'boilerplate_phrases' => count(fractal_zip_enwik_phrase_pack_parse_dict((string) $bp['dict'])),
	'corpus_phrases' => count($mined),
	'dict_bytes' => strlen((string) $bp['dict']) + strlen((string) $cp['dict']),
	'pp96_fzc_baseline' => 19594333,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_harmony_preencode_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));

echo 'harmony preencode (100MiB gzip-1): delta=' . number_format($out['delta_bytes'])
	. ' B (' . $out['pct'] . "%) corpus_phrases={$out['corpus_phrases']}\n";
echo "  wrote {$path}\n";
