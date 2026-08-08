#!/usr/bin/env php
<?php
declare(strict_types=1);
$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_phda9_tokenize.php';
$pages = (int) ($argv[1] ?? 8);
$blob = file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$plain = (string) $split['header'];
for ($i = 0; $i < min($pages, count($split['pages'])); $i++) {
	$p = $split['pages'][$i];
	$plain .= substr($blob, (int) $p['start'], (int) $p['len']);
}
$plain .= (string) $split['footer'];
putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $repo . '/benchmarks/.phda9_external_dict_words_4096p.txt');
putenv('FRACTAL_ZIP_PHDA9_DICT_INLINE=1');
$prep = fractal_zip_phda9_dict_inline_prepare_plain($plain);
file_put_contents('/tmp/big_raw.in', $plain);
file_put_contents('/tmp/big_tok.in', $prep['plain']);
echo 'raw=' . strlen($plain) . ' tok=' . strlen($prep['plain']) . "\n";
