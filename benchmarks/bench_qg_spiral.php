#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * fractal_zip wrapper for qg → spiral Δ probe (delegates to /srv/http/spiral).
 *
 * Usage: nice -n 19 php benchmarks/bench_qg_spiral.php [bytes=200000]
 */

$spiralBench = '/srv/http/spiral/benchmarks/bench_qg_spiral.php';
if (!is_file($spiralBench)) {
	fwrite(STDERR, "spiral bench missing at {$spiralBench}\n");
	exit(1);
}

$args = array_slice($argv, 1);
$cmd = 'nice -n 19 php ' . escapeshellarg($spiralBench);
foreach ($args as $a) {
	$cmd .= ' ' . escapeshellarg($a);
}
passthru($cmd, $rc);
exit((int) $rc);
