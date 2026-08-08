#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * 384p wire probe: member codec shootout on fz-transformed inner (zpaq/phda9/FZSO stacks).
 *
 * Usage:
 *   php -d memory_limit=2048M benchmarks/bench_enwik8_member_codec_shootout_probe.php [--pages=384]
 */

$repo = dirname(__DIR__);
$pageLimit = 384;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pageLimit = max(32, (int) substr($arg, 8));
	}
}

$probe = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_enwik8_wire_slice_probe.php';
$cmd = sprintf(
	'php -d memory_limit=2048M %s --pages=%d --cases=split_inner_fztx_mono_mi,split_inner_fztx_mono_mi_shootout 2>&1',
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
$shoot = null;
foreach ($rows as $r) {
	if (($r['label'] ?? '') === 'split_inner_fztx_mono_mi') {
		$mono = (int) ($r['fzc_bytes'] ?? 0);
	}
	if (($r['label'] ?? '') === 'split_inner_fztx_mono_mi_shootout') {
		$shoot = (int) ($r['fzc_bytes'] ?? 0);
	}
}

$report = array(
	'generated' => date('c'),
	'pages' => $pageLimit,
	'mono_mi_bytes' => $mono,
	'shootout_bytes' => $shoot,
	'delta_bytes' => ($mono !== null && $shoot !== null) ? $shoot - $mono : null,
	'pass' => ($mono !== null && $shoot !== null && $shoot <= $mono),
);
$outPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_member_codec_shootout_probe.json';
file_put_contents($outPath, json_encode($report, JSON_PRETTY_PRINT));

echo "member codec shootout probe → {$outPath}\n";
if ($mono !== null && $shoot !== null) {
	echo '  mono_mi: ' . number_format($mono) . '  shootout: ' . number_format($shoot)
		. '  delta: ' . ($shoot - $mono >= 0 ? '+' : '') . number_format($shoot - $mono) . "\n";
}
echo '  pass: ' . ($report['pass'] ? 'yes' : 'no') . "\n";
exit($report['pass'] ? 0 : 2);
