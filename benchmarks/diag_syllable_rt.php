#!/usr/bin/env php
<?php
declare(strict_types=1);

require dirname(__DIR__) . '/fractal_zip_enwik.php';
require dirname(__DIR__) . '/fractal_zip_enwik_syllable_codec.php';

$src = dirname(__DIR__) . '/test_files109/enwik8';
$blob = (string) file_get_contents($src);
$split = enwik_split_page_refs($blob);
$text = '';
for ($i = 0; $i < 64; $i++) {
	$p = $split['pages'][$i];
	$page = substr($blob, (int) $p['start'], (int) $p['len']);
	$text .= fractal_zip_enwik_extract_page_preserve_text($page);
}

$r = fractal_zip_enwik_consonant_ec_preprocess($text);
$back = fractal_zip_enwik_consonant_ec_undo($r['payload'], $r['sidecar'], $text);
echo 'consonant_ec RT: ' . ($back === $text ? 'ok' : 'FAIL') . "\n";
if ($back !== $text) {
	for ($i = 0, $n = min(strlen($text), strlen($back)); $i < $n; $i++) {
		if ($text[$i] !== $back[$i]) {
			echo "first diff @{$i}\n";
			echo substr($text, max(0, $i - 30), 80) . "\n";
			echo substr($back, max(0, $i - 30), 80) . "\n";
			break;
		}
	}
}

$s = fractal_zip_enwik_syllable_token_preprocess($text);
$back2 = fractal_zip_enwik_syllable_token_undo($s['payload'], $s['sidecar']);
echo 'syllable_tokens RT: ' . ($back2 === $text ? 'ok' : 'FAIL') . "\n";
