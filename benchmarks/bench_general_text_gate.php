#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * GATE: baseline folder zip vs FRACTAL_ZIP_GENERAL_TEXT_INNER=on on Squash literature text files.
 *
 *   Δ = text_inner_fzc − baseline_fzc
 *   Δ < 0  WIN (fewer bytes)
 *
 * Usage:
 *   php -d memory_limit=768M benchmarks/bench_general_text_gate.php --only=115
 *   (one corpus per invocation — do not pass comma-separated lists)
 */

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
putenv('FRACTAL_ZIP_SPEED=1');
putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1');
putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=900');

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip.php';
require_once $repo . '/fractal_zip_general_text.php';

$defaultCorpora = array(
	'test_files108',
	'test_files115',
	'test_files122',
	'test_files124',
	'test_files130',
);
$only = array();
$jsonOut = false;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--only=')) {
		foreach (explode(',', substr($arg, 7)) as $part) {
			$part = trim($part);
			if ($part === '') {
				continue;
			}
			if (str_starts_with($part, 'test_files')) {
				$only[] = $part;
			} else {
				$only[] = 'test_files' . $part;
			}
		}
	} elseif ($arg === '--json') {
		$jsonOut = true;
	}
}
if (count($only) > 1) {
	fwrite(STDERR, "Refusing batch run (memory). Use a single --only=115 per invocation.\n");
	exit(2);
}
$labels = $only !== array() ? $only : array('test_files115');

/**
 * @return array{fzc:int, sec:float, triggered:bool, error:string}
 */
function bench_gt_fzc_path(string $dir): string
{
	return rtrim($dir, DIRECTORY_SEPARATOR) . '.fz';
}

function bench_gt_zip_case(string $dir, string $mode): array
{
	putenv('FRACTAL_ZIP_GENERAL_TEXT_INNER=' . $mode);
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=0');
	$fzcPath = bench_gt_fzc_path($dir);
	@unlink($fzcPath);
	$fz = new fractal_zip();
	$t0 = microtime(true);
	try {
		$fz->zip_folder($dir, false);
	} catch (Throwable $e) {
		return array('fzc' => 0, 'sec' => 0.0, 'triggered' => false, 'error' => $e->getMessage());
	}
	$sec = round(microtime(true) - $t0, 2);
	$fzc = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
	$triggered = is_array($fz->enwik_zip_ctx ?? null)
		&& !empty(($fz->enwik_zip_ctx)['generalTextInner']);
	return array('fzc' => $fzc, 'sec' => $sec, 'triggered' => $triggered, 'error' => '');
}

$rows = array();
foreach ($labels as $label) {
	$dir = $repo . DIRECTORY_SEPARATOR . $label;
	fwrite(STDERR, "[bench_general_text_gate] {$label} baseline…\n");
	if (!is_dir($dir)) {
		$rows[] = array(
			'label' => $label,
			'gate' => 'SKIP',
			'error' => 'missing dir',
		);
		continue;
	}
	$base = bench_gt_zip_case($dir, 'off');
	if ($base['error'] !== '' || $base['fzc'] <= 0) {
		$rows[] = array(
			'label' => $label,
			'gate' => 'FAIL',
			'error' => $base['error'] !== '' ? $base['error'] : 'baseline fzc missing',
		);
		continue;
	}
	fwrite(STDERR, "[bench_general_text_gate] {$label} text-inner…\n");
	$inner = bench_gt_zip_case($dir, 'on');
	if ($inner['error'] !== '' || $inner['fzc'] <= 0) {
		$rows[] = array(
			'label' => $label,
			'gate' => 'FAIL',
			'error' => $inner['error'] !== '' ? $inner['error'] : 'text-inner fzc missing',
		);
		continue;
	}
	$delta = $inner['fzc'] - $base['fzc'];
	$rows[] = array(
		'label' => $label,
		'baseline_fzc' => $base['fzc'],
		'text_inner_fzc' => $inner['fzc'],
		'delta' => $delta,
		'gate' => $delta < 0 ? 'PASS' : ($delta === 0 ? 'TIE' : 'FAIL'),
		'triggered' => $inner['triggered'],
		'sec_base' => $base['sec'],
		'sec_inner' => $inner['sec'],
	);
}

if ($jsonOut) {
	echo json_encode(array('rows' => $rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

printf("bench_general_text_gate | Δ = text_inner − baseline (negative = WIN)\n\n");
printf("%-16s %6s %12s %12s %10s\n", 'CORPUS', 'GATE', 'BASELINE', 'TEXT_INNER', 'Δ');
printf("%'-16s %6s %12s %12s %10s\n", '', '', '', '', '');

foreach ($rows as $r) {
	if (!empty($r['error'])) {
		printf("%-16s %6s %s\n", $r['label'], $r['gate'], $r['error']);
		continue;
	}
	printf(
		"%-16s %6s %12s %12s %+10d\n",
		$r['label'],
		$r['gate'],
		number_format($r['baseline_fzc']),
		number_format($r['text_inner_fzc']),
		$r['delta']
	);
}
