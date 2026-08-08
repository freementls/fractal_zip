#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Report peel strategy coverage over file_types catalog (~1766 extensions).
 *
 *   php benchmarks/report_peel_catalog_coverage.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$peel = realpath($repo . '/../peel/peel.php');
if ($peel === false) {
	fwrite(STDERR, "peel hub missing\n");
	exit(1);
}
require_once $peel;
peel_bootstrap();

$cov = peel_strategy_catalog_coverage();
$total = (int) $cov['total'];
$listable = (int) $cov['listable'];
$pct = $total > 0 ? round(100.0 * $listable / $total, 2) : 0.0;
$pctStrat = $total > 0 ? round(100.0 * $cov['with_strategy'] / $total, 2) : 0.0;

echo "=== Peel catalog coverage ===\n";
echo "file_types extensions: {$total}\n";
echo "with strategy:         {$cov['with_strategy']} ({$pctStrat}%)\n";
echo "listable families:     {$listable} ({$pct}%)\n";
echo "hybrid suffixes:       " . count(peel_hybrid_container_suffixes()) . "\n\n";
echo "by family:\n";
foreach ($cov['by_family'] as $fam => $n) {
	printf("  %-12s %5d\n", $fam, $n);
}
if (($cov['gaps'] ?? []) !== []) {
	echo "\nsample opaque/unknown gaps: " . implode(', ', array_slice($cov['gaps'], 0, 20)) . "\n";
}

$ok = $pctStrat >= 80.0 && $pct >= 40.0;
echo "\n" . ($ok ? 'PASS' : 'WARN') . " criteria: strategy≥80% listable≥40%\n";
exit($ok ? 0 : 2);
