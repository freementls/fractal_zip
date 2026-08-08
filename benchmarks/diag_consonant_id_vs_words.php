#!/usr/bin/env php
<?php
declare(strict_types=1);

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require dirname(__DIR__) . '/fractal_zip_enwik.php';
require dirname(__DIR__) . '/fractal_zip_enwik_syllable_codec.php';
require dirname(__DIR__) . '/fractal_zip_enwik_text_codec.php';

$blob = (string) file_get_contents(dirname(__DIR__) . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$text = '';
for ($i = 0; $i < 64; $i++) {
	$p = $split['pages'][$i];
	$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
}
$w = fractal_zip_enwik_text_codec_encode('words_id_varint_isp', $text);
$c = fractal_zip_enwik_consonant_id_varint_isp_preprocess($text);
echo 'words payload=' . strlen($w['payload']) . ' md5=' . substr(md5($w['payload']), 0, 8) . "\n";
echo 'cons  payload=' . strlen($c['payload']) . ' md5=' . substr(md5($c['payload']), 0, 8) . "\n";
