#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_sub1_bpc_matrix.json';
if (!is_file($path)) {
	fwrite(STDERR, "Missing {$path}\n");
	exit(1);
}
$data = json_decode((string) file_get_contents($path), true);
$rows = $data['rows'] ?? array();
$anchors = $data['ltcb_anchors'] ?? fractal_zip_enwik_ltcb_anchor_table();
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';

usort($rows, static fn (array $a, array $b): int => ((float) ($a['text_bpc'] ?? 999)) <=> ((float) ($b['text_bpc'] ?? 999)));

echo "sub-1 bpc matrix — " . ($data['pages'] ?? '?') . " (" . ($data['generated'] ?? '') . ")\n";
echo 'GPU: ' . (($data['gpu_available'] ?? false) ? (string) ($data['gpu_device'] ?? 'yes') : 'no') . "\n";
echo str_repeat('-', 100) . "\n";
printf("%-8s %-12s %-14s %-16s %-8s %8s %8s %s\n", 'bpc', 'preprocess', 'layout', 'model', 'stack', 'bytes', 'sec', 'status');
foreach ($rows as $r) {
	printf(
		"%8.3f %-12s %-14s %-16s %-8s %8s %8.1f %s\n",
		(float) ($r['text_bpc'] ?? 0),
		(string) ($r['preprocess'] ?? ''),
		(string) ($r['layout'] ?? ''),
		(string) ($r['model'] ?? ''),
		(string) ($r['stack'] ?? ''),
		isset($r['stack_bytes']) ? number_format((int) $r['stack_bytes']) : '-',
		(float) ($r['wall_seconds'] ?? 0),
		(string) ($r['status'] ?? '?')
	);
}

$sub1 = array_values(array_filter($rows, static fn (array $r): bool => isset($r['text_bpc']) && (float) $r['text_bpc'] < 1.0));
$sub09 = array_values(array_filter($rows, static fn (array $r): bool => isset($r['text_bpc']) && (float) $r['text_bpc'] < 0.90));
echo "\n=== text_bpc < 1.0 (" . count($sub1) . ") ===\n";
foreach ($sub1 as $r) {
	echo '  ' . ($r['path_id'] ?? '') . '  bpc=' . ($r['text_bpc'] ?? '?') . '  status=' . ($r['status'] ?? '?') . "\n";
}
echo "\n=== text_bpc < 0.90 (" . count($sub09) . ") ===\n";
foreach ($sub09 as $r) {
	echo '  ' . ($r['path_id'] ?? '') . '  bpc=' . ($r['text_bpc'] ?? '?') . "\n";
}
echo "\nLTCB anchors:\n";
foreach ($anchors as $id => $a) {
	echo '  ' . $id . ': ' . ($a['label'] ?? $id) . '  ' . ($a['bpc'] ?? '?') . " bpc\n";
}
$phda9Bpc = (float) ($anchors['phda9']['bpc'] ?? 1.20);
if ($rows !== array() && isset($rows[0]['text_bpc'])) {
	$delta = (float) $rows[0]['text_bpc'] - $phda9Bpc;
	echo "\nBest vs phda9: " . sprintf('%+.3f', $delta) . " bpc\n";
}
