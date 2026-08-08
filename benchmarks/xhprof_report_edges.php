#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

/**
 * Summarize xhprof JSON: sort call-pair edges by inclusive wall time (wt, microseconds).
 *
 *   php benchmarks/xhprof_report_edges.php [/path/to/fz_xhprof_last.json] [limit]
 */

$path = $argv[1] ?? (sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_xhprof_last.json');
$limit = isset($argv[2]) ? max(1, (int) $argv[2]) : 40;

if (!is_readable($path)) {
	fwrite(STDERR, "Cannot read {$path}\n");
	exit(1);
}

/** @var array<string, array{wt?:int, ct?:int, cpu?:int, mu?:int, pmu?:int}>|null $data */
$data = bench_json_decode_file_assoc_try($path, 'xhprof_report_edges', 512, 0, false);
if ($data === null) {
	exit(1);
}

$rows = array();
foreach ($data as $edge => $m) {
	$wt = (int) ($m['wt'] ?? 0);
	if ($wt <= 0) {
		continue;
	}
	$rows[] = array((string) $edge, $wt, (int) ($m['ct'] ?? 0));
}

usort($rows, static fn(array $a, array $b): int => $b[1] <=> $a[1]);
$rows = array_slice($rows, 0, $limit);

$totalWt = 0;
foreach ($data as $m) {
	$totalWt += (int) ($m['wt'] ?? 0);
}

print("xhprof edges by wt (µs), total_edge_wt_sum={$totalWt} (pairs overlap — not process wall)\n");
foreach ($rows as $r) {
	printf("%10d  %8d  %s\n", $r[1], $r[2], $r[0]);
}
