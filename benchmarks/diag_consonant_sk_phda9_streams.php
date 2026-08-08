#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * phda9 on isolated literal vs skeleton streams (consonant_hybrid_split @Np).
 *
 * Usage: php benchmarks/diag_consonant_sk_phda9_streams.php [64|384]
 */

putenv('FRACTAL_ZIP_CONSONANT_HYBRID_PAYLOAD_CODEC=ascii');
putenv('FRACTAL_ZIP_CONSONANT_SK_FREQ_VOCAB=1');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';

$n = max(8, (int) ($argv[1] ?? 64));
$dict = $repo . '/benchmarks/.phda9_external_dict_mixed_best.txt';
if (is_file($dict)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$pageTexts = array();
for ($i = 0; $i < min($n, count($split['pages'])); $i++) {
	$p = $split['pages'][$i];
	$pageTexts[] = fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
}
$textBuf = implode('', $pageTexts);
$model = fractal_zip_enwik_consonant_hybrid_mine_model_pages($pageTexts, $textBuf);
$shared = fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($model);

$litBuf = '';
$skBuf = '';
$hyBuf = '';
$lossyBuf = '';
foreach ($pageTexts as $t) {
	$sp = fractal_zip_enwik_consonant_hybrid_split_preprocess($t, $model, array('frozen' => true));
	$payload = (string) $sp['payload'];
	$sent = FRACTAL_ZIP_CONSONANT_SK_SPLIT_SENTINEL;
	$pos = strpos($payload, $sent);
	if ($pos !== false) {
		$litBuf .= substr($payload, 0, $pos);
		$skBuf .= substr($payload, $pos + strlen($sent)) . "\n";
	}
	$hy = fractal_zip_enwik_consonant_hybrid_preprocess($t, array('consonant_model' => $model, 'frozen' => true));
	$hyBuf .= (string) $hy['payload'];
	$lo = fractal_zip_enwik_consonant_hybrid_lossy_preprocess($t, array('consonant_model' => $model, 'frozen' => true));
	$lossyBuf .= (string) $lo['payload'];
}

$compress = static function (string $label, string $plain) use ($n): void {
	if ($plain === '') {
		echo "{$label}: empty\n";
		return;
	}
	$t0 = microtime(true);
	$r = fractal_zip_enwik_phda9_english_compress($plain, array('wire_wrap' => true, 'timeout_sec' => 0));
	$sec = round(microtime(true) - $t0, 1);
	echo sprintf(
		"%s plain=%s phda9=%s rt=%s sec=%s\n",
		$label,
		number_format(strlen($plain)),
		number_format((int) ($r['bytes'] ?? 0)),
		!empty($r['roundtrip_ok']) ? 'ok' : 'FAIL',
		$sec
	);
};

echo "diag_consonant_sk_phda9_streams @{$n}p sk_rows=" . count($shared['skeleton_unique'] ?? array()) . "\n\n";
$compress('raw_preserve', $textBuf);
$compress('hybrid_inline', $hyBuf);
$compress('split_literal', $litBuf);
$compress('split_sk_only', trim($skBuf));

$skDictPath = $repo . '/benchmarks/.phda9_external_dict_consonant_split_sk_best.txt';
if (!is_file($skDictPath)) {
	require_once $repo . '/fractal_zip_phda9_dict_mine.php';
	fractal_zip_phda9_dict_build_consonant_split_sk_dict($blob, $n, $skDictPath, array(
		'include_subwords' => true,
	));
}
if (is_file($skDictPath)) {
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $skDictPath);
	$compress('split_sk_only_skdict', trim($skBuf));
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
}

$compress('lossy_inline', $lossyBuf);

require_once $repo . '/benchmarks/enwik8_beat146_gate.php';
$base = enwik_split_page_refs($blob);
$header = (string) $base['header'];
$footer = (string) $base['footer'];
$slice = $header;
for ($i = 0; $i < $n; $i++) {
	$p = $base['pages'][$i];
	$slice .= substr($blob, (int) $p['start'], (int) $p['len']);
}
$slice .= $footer;
$shellBuf = '';
foreach (array_slice($pageTexts, 0, $n) as $i => $t) {
	$p = $base['pages'][$i];
	$pageXml = substr($blob, (int) $p['start'], (int) $p['len']);
	$shellBuf .= fractal_zip_enwik_inject_text_into_shell_page($pageXml, $t);
}
$compress('markup_shell_only', $shellBuf);

echo "\nIf split_sk phda9 << hybrid_inline, dual-member phda9 is the wire win path.\n";
