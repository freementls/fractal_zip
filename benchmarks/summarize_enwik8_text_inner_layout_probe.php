#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_text_inner_layout_probe.json';
if (!is_file($path)) {
	fwrite(STDERR, "Missing {$path}\n");
	exit(1);
}
$data = json_decode((string) file_get_contents($path), true);
$rows = $data['rows'] ?? array();
usort($rows, static fn (array $a, array $b): int => ((float) ($a['text_bpc'] ?? 999)) <=> ((float) ($b['text_bpc'] ?? 999)));
echo "enwik8 text inner layout probe — " . ($data['pages'] ?? '?') . " (" . ($data['generated'] ?? '') . ")\n";
echo str_repeat('-', 88) . "\n";
printf("%-16s %-22s %8s %10s %10s %6s\n", 'layout', 'codec', 'text_bpc', 'split_inner', 'stack', 'rt');
foreach ($rows as $r) {
	if (isset($r['error'])) {
		continue;
	}
	printf(
		"%-16s %-22s %8.3f %10s %10s %s\n",
		(string) ($r['layout'] ?? ''),
		(string) ($r['text_codec'] ?? ''),
		(float) ($r['text_bpc'] ?? 0),
		number_format((int) ($r['split_inner_bytes'] ?? 0)),
		number_format((int) ($r['split_best_stack'] ?? 0)),
		!empty($r['roundtrip_ok']) ? 'OK' : 'FAIL'
	);
}
$sub1 = array_filter($rows, static fn (array $r): bool => isset($r['text_bpc']) && (float) $r['text_bpc'] < 1.0);
echo "\nSub-1 bpc rows: " . count($sub1) . "\n";
$refs = $data['baseline_refs'] ?? array();
echo 'Wire slice baseline: ' . number_format((int) ($refs['wire_slice_no_textcodec'] ?? 0)) . " B\n";
