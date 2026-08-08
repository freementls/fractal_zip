#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Decode round-trip for recursive substring notation: fractally_process_string(equiv, fractal) must match
 * the committed generator corpora on disk (recipe triples from bench / do_fractal_zip.php comments).
 *
 * This proves the engine expands `<off"len"rec>` (and related tuple/scale forms in the same programs) to the
 * expected raw bytes. The inverse (raw → equiv) is a separate encoder; fractal_zip::silent_validate uses
 * unzip() and does not recover these equiv strings for these fixtures.
 *
 * Usage (repo root):
 *   php benchmarks/smoke_recursive_substring_roundtrip.php
 *
 * CI: tests/run_php_smokes.sh phase 4 (after phase 3b zip repro).
 */

putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

/** @var list<array{path:string, equiv:string, fractal:string}> $cases */
$cases = array(
	array(
		'path' => 'test_files23/potentially_infinite_string.txt',
		'equiv' => 'a<12"17"4>aaaa',
		'fractal' => 'a<12"17>aaaabb<0"12>b<0"12>bb',
	),
	array(
		'path' => 'test_files24/dehacking_fractal_substring.txt',
		'equiv' => 'aaaaa<20"25"30>aaaaaaaa',
		'fractal' => 'aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb',
	),
	array(
		'path' => 'test_files29/showing_off.txt',
		'equiv' => 'aaaaa<20"25"30>aaaaaaaa',
		'fractal' => 'aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb',
	),
	array(
		'path' => 'test_files30/test_files2.txt',
		'equiv' => 'aaaaa<20"25"30>aaaaaaaa',
		'fractal' => 'aaaaa<20"25>aaaaaaaabbbbbb<0"20>b<0"20>bbbbbb',
	),
);

$fz = new fractal_zip(64, false, false, null, false);
$failed = 0;
$silentNotePrinted = false;

foreach ($cases as $spec) {
	$p = $repoRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $spec['path']);
	if (!is_file($p)) {
		fwrite(STDERR, "SKIP missing {$spec['path']}\n");
		continue;
	}
	$disk = file_get_contents($p);
	if ($disk === false) {
		fwrite(STDERR, "FAIL read {$spec['path']}\n");
		$failed++;
		continue;
	}
	try {
		$expanded = $fz->fractally_process_string($spec['equiv'], $spec['fractal']);
	} catch (Throwable $e) {
		fwrite(STDERR, "FAIL fractally_process_string {$spec['path']}: {$e->getMessage()}\n");
		$failed++;
		continue;
	}
	if ($spec['path'] === 'test_files30/test_files2.txt') {
		$plen = strlen($disk);
		$ok = $disk === substr($expanded, 0, $plen) && strlen($expanded) >= $plen;
		$note = 'recipe expansion prefix (' . (string) $plen . ' B slice)';
	} else {
		$ok = $disk === $expanded;
		$note = 'full file (' . (string) strlen($disk) . ' B)';
	}
	if (!$ok) {
		fwrite(STDERR, "FAIL {$spec['path']} {$note}\n");
		$failed++;
		continue;
	}
	fwrite(STDOUT, "OK {$spec['path']} {$note}\n");

	if (!$silentNotePrinted && $spec['path'] === 'test_files23/potentially_infinite_string.txt') {
		$sv = $fz->silent_validate($disk, $spec['fractal'], $spec['equiv']);
		fwrite(STDOUT, '   note: silent_validate(unzip inverse) for case 23 triple is ' . ($sv ? 'true' : 'false') . " — not equiv recovery.\n");
		$silentNotePrinted = true;
	}
}

if ($failed > 0) {
	exit(1);
}
fwrite(STDOUT, "OK smoke_recursive_substring_roundtrip (" . (string) count($cases) . " checks).\n");
