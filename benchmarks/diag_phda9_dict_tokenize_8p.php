#!/usr/bin/env php
<?php
declare(strict_types=1);
$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt');
putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE=1');
putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE_MODE=tokenize');
putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9_no_lstm');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_phda9_english.php';
$pages = (int) ($argv[1] ?? 8);
$blob = file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$plain = (string) $split['header'];
for ($i = 0; $i < min($pages, count($split['pages'])); $i++) {
	$p = $split['pages'][$i];
	$plain .= substr($blob, (int) $p['start'], (int) $p['len']);
}
$plain .= (string) $split['footer'];
$prep = fractal_zip_phda9_dict_inline_prepare_plain($plain);
$tool = fractal_zip_phda9_dict_inline_resolve_tool('phda9_no_lstm');
echo 'plain=' . strlen($plain) . ' tok=' . strlen($prep['plain']) . ' words=' . $prep['words'] . ' tool=' . $tool . "\n";
$t0 = microtime(true);
$r = fractal_zip_enwik_phda9_english_compress($plain, array(
	'tool' => 'phda9_no_lstm',
	'use_dict' => true,
	'wire_wrap' => false,
	'no_fallback' => true,
));
$sec = round(microtime(true) - $t0, 2);
echo 'rt=' . (!empty($r['roundtrip_ok']) ? 'ok' : 'fail') . ' bytes=' . ($r['bytes'] ?? 0)
	. ' status=' . ($r['status'] ?? '?') . ' tool=' . ($r['tool'] ?? '?') . " sec={$sec}\n";
