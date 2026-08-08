#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Regression guard for fz_inner_pass_search (pass 1, append/operator path).
 *
 * Usage (repo root):
 *   php benchmarks/smoke_inner_pass_regression.php
 *   php benchmarks/smoke_inner_pass_regression.php --full
 *   php benchmarks/smoke_inner_pass_regression.php --verbose
 *
 * Default: fast fractal fixtures (26–28 + smoke slice 30).
 * --full: also runs cases 21–25 and 29 (~25s extra substring enumeration plus generated-corpus checks).
 *
 * Exits 1 if any present member exceeds its maxFin ceiling; missing files are SKIP.
 */

putenv('FRACTAL_ZIP_ALL_SUBSTRING_CANDIDATES=1');
putenv('FRACTAL_ZIP_SUPPRESS_HTML=1');

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'fractal_inner_passes.inc.php';

$runFull = false;
$verbose = false;
if (isset($argv)) {
	foreach (array_slice($argv, 1) as $a) {
		if ($a === '--help' || $a === '-h') {
			fwrite(
				STDOUT,
				<<<HELP
Inner pass regression (fz_inner_pass_search pass 1).

  php benchmarks/smoke_inner_pass_regression.php [options]

CI (tests/run_php_smokes.sh): phase 5 = this script (26–28 + 30 by default).

Options:
  --full       Include heavier cases 21–25 and 29 (2D grid / replace / generated corpora).
  --verbose    Print baseLin, candidates, innerSoftByteCapped per row.
  --help, -h   This message.

HELP
			);
			exit(0);
		}
		if ($a === '--full') {
			$runFull = true;
			continue;
		}
		if ($a === '--verbose' || $a === '-v') {
			$verbose = true;
			continue;
		}
		fwrite(STDERR, "Unknown argument: {$a} (try --help)\n");
		exit(2);
	}
}

/** @var list<array{rel:string, maxFin:int, note:string}> */
$checksQuick = array(
	array(
		'rel' => 'test_files26/gradient_operation.txt',
		'maxFin' => 8,
		'note' => 'gradient',
	),
	array(
		'rel' => 'test_files27/tuple_operation.txt',
		'maxFin' => 21,
		'note' => 'tuple',
	),
	array(
		'rel' => 'test_files28/scale_operation.txt',
		'maxFin' => 120,
		'note' => 'scale greedy vs recipe',
	),
	array(
		'rel' => 'test_files30/test_files2.txt',
		'maxFin' => 68,
		'note' => 'smoke slice recursive run-pair prefix',
	),
	array(
		'rel' => 'test_files31/multifractal.txt',
		'maxFin' => 113,
		'note' => 'multirun ladder grammar',
	),
	array(
		'rel' => 'test_files32/harder_multifractal.txt',
		'maxFin' => 120,
		'note' => 'multirun ladder grammar',
	),
);

/** Heavier corpora; ceilings slack vs bench pass1 (greedy intros enabled). */
$checksFull = array(
	array(
		'rel' => 'test_files21/2Dfractal.txt',
		'maxFin' => 170,
		'note' => '2D grid',
	),
	array(
		'rel' => 'test_files22/2Dfractal.txt',
		'maxFin' => 296,
		'note' => '2D extended',
	),
	array(
		'rel' => 'test_files23/potentially_infinite_string.txt',
		'maxFin' => 45,
		'note' => 'recursion substring',
	),
	array(
		'rel' => 'test_files24/dehacking_fractal_substring.txt',
		'maxFin' => 68,
		'note' => 'recursive run-pair exact',
	),
	array(
		'rel' => 'test_files25/replace_operation.txt',
		'maxFin' => 95,
		'note' => 'replace ops',
	),
	array(
		'rel' => 'test_files29/showing_off.txt',
		'maxFin' => 68,
		'note' => 'recursive run-pair exact',
	),
);

$checks = $runFull ? array_merge($checksQuick, $checksFull) : $checksQuick;

$segment = 64;
$failed = 0;

foreach ($checks as $c) {
	$path = $repoRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $c['rel']);
	if (!is_file($path)) {
		fwrite(STDERR, "SKIP missing file {$c['rel']}\n");
		continue;
	}
	$raw = file_get_contents($path);
	if ($raw === false) {
		fwrite(STDERR, "FAIL cannot read {$c['rel']}\n");
		$failed++;
		continue;
	}
	$slice30 = ($c['rel'] === 'test_files30/test_files2.txt');
	$prevRawPrefix = getenv('FZ_INNER_VERIFY_RAW_PREFIX');
	if ($slice30) {
		putenv('FZ_INNER_VERIFY_RAW_PREFIX=1');
	}
	$fz = new fractal_zip($segment, false, false, null, false);
	$sr = fz_inner_pass_search($fz, $raw, 1, 0, true, 5000000, null, true, false, false);
	if ($slice30) {
		if ($prevRawPrefix === false) {
			putenv('FZ_INNER_VERIFY_RAW_PREFIX');
		} else {
			putenv('FZ_INNER_VERIFY_RAW_PREFIX=' . $prevRawPrefix);
		}
	}
	$baseLin = $sr['baseLin'];
	$best = $baseLin;
	foreach ($sr['rows'] ?? array() as $row) {
		$fin = fz_row_final_lin($row);
		if ($fin < $best) {
			$best = $fin;
		}
	}
	$capped = ($sr['innerSoftByteCapped'] ?? false) ? '1' : '0';
	$pc = (int) ($sr['pass1CandCount'] ?? -1);
	$diag = $verbose ? " baseLin={$baseLin} cand={$pc} innerSoftCap={$capped}" : '';
	if ($best > $c['maxFin']) {
		fwrite(STDERR, "FAIL {$c['rel']} ({$c['note']}) bestFin={$best} ceiling={$c['maxFin']}{$diag}\n");
		$failed++;
	} else {
		fwrite(STDOUT, "ok {$c['rel']} bestFin={$best} <= {$c['maxFin']} ({$c['note']}){$diag}\n");
	}
}

exit($failed > 0 ? 1 : 0);
