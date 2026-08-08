#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Bridge dict_nncp sample5 win → 384p wire probe; abort if regresses vs mono_mi.
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/bench_enwik8_dict_nncp_wire_bridge.php [--pages=384]
 */

$repo = dirname(__DIR__);
putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_PARALLEL_PROBE=1');
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
fractal_zip_process_guard_register_cli();

$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
}

$probe = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_enwik8_wire_slice_probe.php';
$cmd = sprintf(
	'php -d memory_limit=2048M %s --pages=%d --cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_dict 2>&1',
	escapeshellarg($probe),
	$pageLimit
);
exec($cmd, $out, $code);
if ($code !== 0) {
	fwrite(STDERR, implode("\n", $out) . "\n");
	exit(1);
}

$jsonPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json';
$dec = json_decode((string) file_get_contents($jsonPath), true);
if (!is_array($dec) || (int) ($dec['pages'] ?? 0) !== $pageLimit) {
	fwrite(STDERR, "wire probe pages mismatch (expected {$pageLimit})\n");
	exit(1);
}
$rows = is_array($dec['rows'] ?? null) ? $dec['rows'] : array();
$mono = null;
$dict = null;
foreach ($rows as $r) {
	if (($r['label'] ?? '') === 'split_inner_fztx_mono_mi') {
		$mono = (int) ($r['fzc_bytes'] ?? 0);
	}
	if (($r['label'] ?? '') === 'split_inner_fztx_mono_mi_dict') {
		$dict = (int) ($r['fzc_bytes'] ?? 0);
	}
}

$bridge = array(
	'generated' => date('c'),
	'pages' => $pageLimit,
	'mono_mi_bytes' => $mono,
	'dict_nncp_bytes' => $dict,
	'delta_bytes' => ($mono !== null && $dict !== null) ? $dict - $mono : null,
	'pass' => false,
	'note' => 'sample5 lab 0.842 bpc; wire must beat mono_mi',
);
if ($mono !== null && $dict !== null && $dict < $mono) {
	$bridge['pass'] = true;
}
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_dict_nncp_wire_bridge.json';
file_put_contents($outPath, json_encode($bridge, JSON_PRETTY_PRINT));

echo "dict_nncp wire bridge → {$outPath}\n";
if ($mono !== null && $dict !== null) {
	echo '  mono_mi: ' . number_format($mono) . '  dict_nncp: ' . number_format($dict)
		. '  delta: ' . ($dict - $mono >= 0 ? '+' : '') . number_format($dict - $mono) . "\n";
}
echo '  pass: ' . ($bridge['pass'] ? 'yes' : 'no') . "\n";
exit($bridge['pass'] ? 0 : 2);
