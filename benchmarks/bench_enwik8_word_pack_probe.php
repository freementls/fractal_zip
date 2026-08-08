#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * gzip-1 on 8 MiB slice: harmony pre vs +word pack.
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
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
$words = fractal_zip_enwik_mine_article_word_phrases($b2, 32, 128);
$wp = fractal_zip_enwik_phrase_pack_apply($b2, $words);
$b3 = (string) $wp['blob'];

$g0 = strlen(gzdeflate($blob, 1));
$g2 = strlen(gzdeflate($b2, 1));
$g3 = strlen(gzdeflate($b3, 1));

$out = array(
	'generated' => date('c'),
	'gzip1_raw' => $g0,
	'gzip1_harmony_pre' => $g2,
	'gzip1_harmony_words' => $g3,
	'delta_words' => $g2 - $g3,
	'word_tokens' => count($words),
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_word_pack_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo 'word pack probe: tokens=' . $out['word_tokens'] . ' extra_delta=' . number_format($out['delta_words']) . " B\n";
