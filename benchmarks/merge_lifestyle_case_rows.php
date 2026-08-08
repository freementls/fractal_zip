#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Merge case rows from a partial --only= JSON into a full lifestyle table JSON.
 * Usage: php benchmarks/merge_lifestyle_case_rows.php base.json patch.json [out.json]
 */
if ($argc < 3) {
	fwrite(STDERR, "Usage: {$argv[0]} base.json patch.json [out.json]\n");
	exit(1);
}
$basePath = $argv[1];
$patchPath = $argv[2];
$outPath = $argv[3] ?? $basePath;
$base = json_decode((string) file_get_contents($basePath), true);
$patch = json_decode((string) file_get_contents($patchPath), true);
if (!is_array($base) || !is_array($patch)) {
	fwrite(STDERR, "bad json\n");
	exit(1);
}
$by = [];
foreach ($patch['cases'] ?? [] as $r) {
	if (is_array($r) && isset($r['label'])) {
		$by[(string) $r['label']] = $r;
	}
}
$n = 0;
foreach ($base['cases'] ?? [] as $i => $r) {
	if (!is_array($r) || !isset($r['label'])) {
		continue;
	}
	$lab = (string) $r['label'];
	if (isset($by[$lab])) {
		$base['cases'][$i] = $by[$lab];
		$n++;
	}
}
// Recompute completeness via requiring run_benchmarks helpers would pull CLI main;
// leave table_complete for the analyzer / a follow-up full gate.
unset($base['table_completeness_problems']);
$base['table_complete'] = null;
$base['merged_from'] = basename($patchPath);
$base['merged_cases'] = array_keys($by);
$base['merged_at'] = gmdate('c');
$json = json_encode($base, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
if ($json === false || file_put_contents($outPath, $json) === false) {
	fwrite(STDERR, "write failed\n");
	exit(1);
}
echo "merged {$n} case(s) into {$outPath}\n";
