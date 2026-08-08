#!/usr/bin/env php
<?php
declare(strict_types=1);
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_collision_free_abbrevs.php';
require_once $repo . '/fractal_zip_gpu_substring.php';
$pages = (int) ($argv[1] ?? 384);
$path = $argv[2] ?? '/tmp/enwik384p.txt';
$decoded = (string) file_get_contents($path);
putenv('FRACTAL_ZIP_CFABB_SA=0');
$t0 = microtime(true);
$legacy = fractal_zip_cfabb_mine($decoded, array(
	'corpus_pages' => $pages,
	'min_count' => 4,
	'max_entries' => 4096,
	'max_words' => 10,
	'max_chars' => 100,
));
$legacySec = microtime(true) - $t0;
putenv('FRACTAL_ZIP_CFABB_SA=1');
$t1 = microtime(true);
$sa = fractal_zip_cfabb_mine($decoded, array(
	'corpus_pages' => $pages,
	'min_count' => 4,
	'max_entries' => 4096,
	'max_words' => 10,
	'max_chars' => 100,
));
$saSec = microtime(true) - $t1;
$legacySave = array_sum(array_column($legacy, 'save'));
$saSave = array_sum(array_column($sa, 'save'));
printf("%dp cfabb legacy: entries=%d save=%d sec=%.1f\n", $pages, count($legacy), $legacySave, $legacySec);
printf("%dp cfabb +SA:    entries=%d save=%d sec=%.1f dsave=%+d\n", $pages, count($sa), $saSave, $saSec, $saSave - $legacySave);
