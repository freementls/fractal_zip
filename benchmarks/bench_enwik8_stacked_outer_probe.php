#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Stacked outer layers outer2(outer1(inner)) probe.
 *
 * Usage:
 *   php benchmarks/bench_enwik8_stacked_outer_probe.php
 *   php benchmarks/bench_enwik8_stacked_outer_probe.php --input=path/to/inner.bin
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_text_compressor_external.php';

$inputPath = '';
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--input=')) {
		$inputPath = substr($arg, 8);
	}
}

if ($inputPath === '' || !is_file($inputPath)) {
	$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	$blob = (string) file_get_contents($src);
	$split = fractal_zip_enwik_split_shell_and_text($blob, 384);
	if ($split === null) {
		exit(1);
	}
	$layout = fractal_zip_enwik_text_layout_apply($split['pages'], 'sort_title', array('seed' => 1));
	$shellBuf = '';
	foreach ($split['pages'] as $pg) {
		$shellBuf .= (string) $pg['shell'];
	}
	$inner = fractal_zip_enwik_build_split_inner_blob((string) $layout['text_blob'], $shellBuf, $layout['meta']);
	$label = 'split_inner_384p_sort_title';
} else {
	$inner = (string) file_get_contents($inputPath);
	$label = basename($inputPath);
}

$stacks = fractal_zip_text_stacked_outer_catalog();
$rows = array();
$singleBest = fractal_zip_text_stacked_outer_apply('none', $inner);
$rows[] = array(
	'stack_id' => 'single_best',
	'layer1_bytes' => strlen($inner),
	'layer2_bytes' => strlen($inner),
	'total_bytes' => $singleBest['total_bytes'],
	'delta_vs_single_best' => 0,
	'roundtrip_ok' => true,
);
$baseBytes = (int) $singleBest['total_bytes'];
foreach ($stacks as $stackId) {
	if ($stackId === 'none') {
		continue;
	}
	try {
		$r = fractal_zip_text_stacked_outer_apply($stackId, $inner);
		$rows[] = array(
			'stack_id' => $stackId,
			'layer1_bytes' => $r['layer1_bytes'],
			'layer2_bytes' => $r['layer2_bytes'],
			'total_bytes' => $r['total_bytes'],
			'stack_meta_bytes' => $r['stack_meta_bytes'] ?? 0,
			'delta_vs_single_best' => $baseBytes - (int) $r['total_bytes'],
			'roundtrip_ok' => (bool) $r['roundtrip_ok'],
		);
	} catch (Throwable $e) {
		$rows[] = array('stack_id' => $stackId, 'error' => $e->getMessage());
	}
}
usort($rows, static fn (array $a, array $b): int => ((int) ($a['total_bytes'] ?? PHP_INT_MAX)) <=> ((int) ($b['total_bytes'] ?? PHP_INT_MAX)));

$out = array(
	'generated' => date('c'),
	'input' => $label,
	'inner_bytes' => strlen($inner),
	'rows' => $rows,
);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_stacked_outer_probe.json';
file_put_contents($path, json_encode($out, JSON_PRETTY_PRINT));
echo "stacked outer probe ({$label}) → {$path}\n";
foreach ($rows as $r) {
	if (isset($r['error'])) {
		echo '  ERR ' . $r['stack_id'] . ': ' . $r['error'] . "\n";
		continue;
	}
	echo '  ' . $r['stack_id'] . '  total=' . number_format((int) $r['total_bytes'])
		. '  delta=' . number_format((int) ($r['delta_vs_single_best'] ?? 0)) . "\n";
}
