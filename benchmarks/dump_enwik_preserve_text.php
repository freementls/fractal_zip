#!/usr/bin/env php
<?php
declare(strict_types=1);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_enwik.php';
require_once $repo . '/fractal_zip_wiki_lom.php';
$pages = (int) ($argv[1] ?? 96);
$out = $argv[2] ?? '/tmp/enwik_preserve.txt';
$blob = (string) file_get_contents($repo . '/test_files109/enwik8');
$split = enwik_split_page_refs($blob);
if ($split === null) {
	exit(1);
}
$text = '';
$n = min($pages, count($split['pages']));
for ($i = 0; $i < $n; $i++) {
	$p = $split['pages'][$i];
	$text .= fractal_zip_enwik_extract_page_preserve_text(substr($blob, (int) $p['start'], (int) $p['len']));
}
$decoded = fractal_zip_wiki_lom_entity_decode_text($text);
file_put_contents($out, $decoded);
fwrite(STDOUT, "pages={$n} len=" . strlen($decoded) . " out={$out}\n");
