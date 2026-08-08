#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_enwik_syllable_codec.php';

$n = (int) ($argv[1] ?? 64);
$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
$text = '';
for ($i = 0; $i < min($n, count($split['pages'])); $i++) {
	$p = $split['pages'][$i];
	$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
}
$m = fractal_zip_enwik_consonant_hybrid_mine_model($text);
$meta = fractal_zip_enwik_consonant_hybrid_shared_meta_from_model($m);
$json = json_encode($meta, JSON_UNESCAPED_SLASHES) ?: '{}';
$gz = gzencode($json, 9);
echo "@{$n}p unique=" . count($m['skeleton_unique'])
	. ' ambig=' . count($m['skeleton_ambig'])
	. ' ctx=' . array_sum(array_map('count', $m['context_unique']))
	. ' meta_raw=' . strlen($json)
	. ' meta_gz9=' . (is_string($gz) ? strlen($gz) : '?') . "\n";
