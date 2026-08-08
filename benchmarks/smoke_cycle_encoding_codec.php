#!/usr/bin/env php
<?php
declare(strict_types=1);

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';

$fail = static function (string $msg): void {
	fwrite(STDERR, "FAIL smoke_cycle_encoding_codec: {$msg}\n");
	exit(1);
};

foreach(cycle_encoding_expanded_specs() as $case => $spec) {
	$bytes = (string)$spec['bytes'];
	$enc = cycle_encoding_codec_encode($bytes);
	$dec = cycle_encoding_codec_decode((string)$enc['payload']);
	if($dec !== $bytes) {
		$fail('RT case ' . (string)$case);
	}
	$pre = fractal_zip_text_cycle_preprocess($bytes);
	$und = fractal_zip_text_cycle_undo((string)$pre['payload'], $pre['sidecar']);
	if($und !== $bytes) {
		$fail('preprocess RT case ' . (string)$case);
	}
}

$delta = cycle_encoding_delta_forward('abcdef');
if(cycle_encoding_delta_inverse($delta) !== 'abcdef') {
	$fail('delta RT');
}

fwrite(STDOUT, "OK smoke_cycle_encoding_codec cases=" . (string)count(cycle_encoding_expanded_specs()) . "\n");

// Hybrid natural prefix + cycle suffix (segmented path).
$natural = @file_get_contents($root . DIRECTORY_SEPARATOR . 'test_files110' . DIRECTORY_SEPARATOR . 'fields.c');
if($natural !== false && strlen($natural) >= 9000) {
	$spec184 = cycle_encoding_recipe_specs()[184];
	$cyclePart = cycle_encoding_generate($spec184);
	$hybrid = substr($natural, 0, 8000) . $cyclePart;
	$enc = cycle_encoding_codec_encode($hybrid);
	$dec = cycle_encoding_codec_decode((string)$enc['payload']);
	if($dec !== $hybrid) {
		$fail('hybrid RT');
	}
	$cycleLen = (int)($spec184['length'] ?? 0);
	if(($enc['meta']['cycle_bytes'] ?? 0) < $cycleLen) {
		$fail('hybrid expected cycle_bytes >= ' . (string)$cycleLen);
	}
}

fwrite(STDOUT, "OK smoke_cycle_encoding_codec hybrid\n");

$hy197 = @file_get_contents($root . DIRECTORY_SEPARATOR . 'test_files197' . DIRECTORY_SEPARATOR . 'hybrid.txt');
if($hy197 !== false && strlen($hy197) >= 1_000_000) {
	$enc = cycle_encoding_codec_encode($hy197);
	$dec = cycle_encoding_codec_decode((string)$enc['payload']);
	if($dec !== $hy197) {
		$fail('hybrid197 RT');
	}
	if(($enc['meta']['cycle_bytes'] ?? 0) < 1_000_000) {
		$fail('hybrid197 expected cycle_bytes >= 1MiB');
	}
	fwrite(STDOUT, "OK smoke_cycle_encoding_codec hybrid197\n");
}
