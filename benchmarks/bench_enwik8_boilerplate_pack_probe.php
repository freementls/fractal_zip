#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * gzip-1 probe on 8 MiB enwik8 slice: raw vs boilerplate-pack (harmony tier A).
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($src)) {
	exit(1);
}
$sliceLen = 8388608;
$fh = fopen($src, 'rb');
$blob = fread($fh, $sliceLen);
fclose($fh);
if (!is_string($blob) || $blob === '') {
	exit(1);
}

$packed = fractal_zip_enwik_boilerplate_pack_apply($blob);
$g1 = strlen(gzdeflate($blob, 1));
$g2 = strlen(gzdeflate((string) $packed['blob'], 1));

$out = array(
	'generated' => date('c'),
	'slice_bytes' => strlen($blob),
	'gzip1_raw' => $g1,
	'gzip1_packed' => $g2,
	'delta_bytes' => $g1 - $g2,
	'dict_bytes' => strlen((string) $packed['dict']),
	'phrase_count' => count(fractal_zip_enwik_phrase_pack_parse_dict((string) $packed['dict'])),
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_boilerplate_pack_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo 'boilerplate pack probe: delta=' . number_format($out['delta_bytes']) . " B phrases={$out['phrase_count']}\n";
echo "  wrote {$path}\n";
