#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Verify test_files140-150 against their deterministic generator recipes.
 *
 * This smoke proves only fixture integrity. It does not call fz_inner_pass_search,
 * does not set verified-hint env vars, and does not expose recipes to compression.
 *
 * Usage from repo root:
 *   php benchmarks/smoke_fractal_inner_recipes_140_150.php
 *
 * CI: tests/run_php_smokes.sh phase 4b.
 */

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');
putenv('FZ_INNER_VERIFIED_ENCODING_HINTS=0');
putenv('FZ_INNER_VERIFIED_ENCODING_HINT_FILES');

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_inner_recipes_140_150.php';

$fail = static function (string $msg): void {
	fwrite(STDERR, "FAIL smoke_fractal_inner_recipes_140_150: {$msg}\n");
	exit(1);
};

$fz = new fractal_zip(64, false, false, null, false);
$specs = fz_inner_fixture_expanded_specs_140_150($fz);
$checks = 0;
$bytesTotal = 0;

foreach($specs as $case => $spec) {
	$dir = $root . DIRECTORY_SEPARATOR . $spec['dir'];
	if(!is_dir($dir)) {
		$fail($spec['dir'] . ' missing; run php benchmarks/build_test_files140_150.php');
	}
	foreach($spec['files'] as $file) {
		$rel = (string)$file['path'];
		$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if(!is_file($path)) {
			$fail($spec['dir'] . '/' . $rel . ' missing');
		}
		$disk = file_get_contents($path);
		if($disk === false) {
			$fail($spec['dir'] . '/' . $rel . ' unreadable');
		}
		$expected = fz_inner_fixture_expand_recipe($fz, $file['recipe']);
		$expectedHash = hash('xxh128', $expected, false);
		if($disk !== $expected) {
			$fail($spec['dir'] . '/' . $rel . ' recipe expansion mismatch');
		}
		if(strlen($disk) !== (int)$file['expected_size'] || $expectedHash !== (string)$file['expected_xxh128']) {
			$fail($spec['dir'] . '/' . $rel . ' expected metadata mismatch');
		}
		$checks++;
		$bytesTotal += strlen($disk);
	}
	fwrite(STDOUT, 'OK ' . $spec['dir'] . ' files=' . (string)count($spec['files']) . "\n");
}

fwrite(STDOUT, 'OK smoke_fractal_inner_recipes_140_150 checks=' . (string)$checks . ' bytes=' . (string)$bytesTotal . "\n");
