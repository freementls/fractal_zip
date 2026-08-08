#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Scan enwik8 <text> regions: words_base94_isp + transform=none must roundtrip.
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik_text_codec.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	fwrite(STDERR, "Missing enwik8\n");
	exit(1);
}

$blob = (string) file_get_contents($src);
$cfg = array('scheme' => 'words_base94_isp', 'transform' => 'none', 'seed' => 1);
$sharedVocab = fractal_zip_enwik_text_codec_build_shared_vocab_from_blob($blob);
$cfg['vocab'] = $sharedVocab;
$cfg['vocab_index'] = fractal_zip_enwik_text_vocab_index($sharedVocab);

$marker = '<text xml:space="preserve">';
$openLen = strlen($marker);
$pos = 0;
$n = strlen($blob);
$region = 0;
$bad = 0;
$pageStart = 0;
while ($pos < $n) {
	$pagePos = stripos($blob, '<page', $pos);
	$textPos = stripos($blob, $marker, $pos);
	if ($textPos === false) {
		break;
	}
	if ($pagePos !== false && $pagePos < $textPos) {
		$pageStart = $pagePos;
		$pos = $pagePos + 5;
		continue;
	}
	$innerStart = $textPos + $openLen;
	$close = stripos($blob, '</text>', $innerStart);
	if ($close === false) {
		break;
	}
	$inner = substr($blob, $innerStart, $close - $innerStart);
	$enc = fractal_zip_enwik_text_codec_encode('words_base94_isp', $inner, array(
		'vocab' => $cfg['vocab'],
		'vocab_index' => $cfg['vocab_index'],
	));
	$t = fractal_zip_enwik_text_transform_apply('none', (string) $enc['payload'], array('seed' => 1));
	$entry = fractal_zip_enwik_text_codec_serialize_entry(
		'words_base94_isp',
		'none',
		array_merge($enc['sidecar'], $t['sidecar']),
		(string) $t['payload']
	);
	$restored = fractal_zip_enwik_text_codec_restore_entry($entry, $sharedVocab);
	$delta = strlen($restored) - strlen($inner);
	if ($restored !== $inner) {
		$bad++;
		fwrite(STDERR, "[bad] region={$region} page_off={$pageStart} inner_len=" . strlen($inner)
			. " delta={$delta} trailing=" . (!empty($enc['sidecar']['trailing_implicit_period']) ? '1' : '0') . "\n");
		if ($bad >= 20) {
			fwrite(STDERR, "... stopping after 20\n");
			break;
		}
	}
	$region++;
	$pos = $close + 7;
}
fwrite(STDERR, "[scan] regions={$region} bad={$bad}\n");
exit($bad > 0 ? 1 : 0);
