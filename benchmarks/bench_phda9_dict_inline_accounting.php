#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * External phda9 dict file vs inline tokenization (zero dict bytes at CLI).
 *
 * Usage: php benchmarks/bench_phda9_dict_inline_accounting.php [--pages=96]
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
require_once $repo . '/fractal_zip_phda9_tokenize.php';

$pages = 96;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	}
}

$dict = $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt';
if (!is_file($dict)) {
	fwrite(STDERR, "missing dict {$dict}\n");
	exit(1);
}
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dict);
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ=' . $repo . '/tools/phda9/phda9_no_LSTM');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');

$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$plain = (string) $split['header'];
$n = min($pages, count($split['pages']));
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$plain .= substr($blob, (int) $p['start'], (int) $p['len']);
}
$plain .= (string) $split['footer'];

$dictBytes = (int) filesize($dict);

$run = static function (string $mode, string $inlineMode = 'temp') use ($plain): array {
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE=' . ($mode === 'inline' ? '1' : '0'));
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_MODE=' . ($mode === 'inline' ? $inlineMode : 'temp'));
	putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_ACTIVE');
	$GLOBALS['fractal_zip_phda9_dict_inline_vocab'] = null;
	$GLOBALS['fractal_zip_phda9_dict_inline_mine_text'] = null;
	$t0 = microtime(true);
	$r = fractal_zip_enwik_phda9_english_compress($plain, array(
		'tool' => 'phda9_no_lstm',
		'use_dict' => true,
		'wire_wrap' => false,
		'no_fallback' => true,
	));
	$sec = microtime(true) - $t0;
	$prep = fractal_zip_phda9_dict_inline_prepare_plain($plain);
	return array(
		'mode' => $mode,
		'inline_mode' => $mode === 'inline' ? $inlineMode : null,
		'bytes' => (int) ($r['bytes'] ?? 0),
		'roundtrip_ok' => !empty($r['roundtrip_ok']),
		'sec' => round($sec, 2),
		'tokenized' => !empty($prep['tokenized']),
		'vocab_words' => (int) ($prep['words'] ?? 0),
		'tool' => (string) ($r['tool'] ?? ''),
	);
};

$external = $run('external');
$inlineTemp = $run('inline', 'temp');
$inlineTok = $run('inline', 'tokenize');

printf("phda9 dict accounting pages=%d plain_len=%d dict_file=%s bytes\n", $n, strlen($plain), number_format($dictBytes));
foreach (array($external, $inlineTemp, $inlineTok) as $r) {
	$label = $r['mode'] === 'inline' ? 'inline_' . ($r['inline_mode'] ?? 'temp') : $r['mode'];
	printf("  %-16s wire=%s sec=%.1f rt=%s tok=%s vocab=%d tool=%s\n",
		$label,
		number_format($r['bytes']),
		$r['sec'],
		$r['roundtrip_ok'] ? 'ok' : 'FAIL',
		$r['tokenized'] ? 'yes' : 'no',
		$r['vocab_words'],
		$r['tool'] ?? '?'
	);
}
if ($external['bytes'] > 0 && $inlineTok['bytes'] > 0) {
	printf("  inline_tokenize Δwire=%+d (dict bytes not passed to CLI: %s saved)\n",
		$inlineTok['bytes'] - $external['bytes'],
		number_format($dictBytes)
	);
}

$out = $repo . '/benchmarks/.phda9_dict_inline_accounting_' . $n . 'p.json';
file_put_contents($out, json_encode(array(
	'generated' => date('c'),
	'pages' => $n,
	'dict_file_bytes' => $dictBytes,
	'external' => $external,
	'inline_temp' => $inlineTemp,
	'inline_tokenize' => $inlineTok,
), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
echo "json → {$out}\n";
