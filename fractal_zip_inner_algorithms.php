<?php
declare(strict_types=1);

/**
 * Production-owned loader for generic fractal-inner detector algorithms.
 *
 * The detector implementations are still shared with the benchmark harness while
 * they are being split apart. Keep this file free of fixture recipes, oracle
 * rows, case-specific paths, and benchmark reporting code; production entry
 * points include this file, not benchmark paths.
 */

$fractalZipInnerAlgorithmInclude = __DIR__ . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'include' . DIRECTORY_SEPARATOR . 'fractal_inner_passes.inc.php';
if (is_file($fractalZipInnerAlgorithmInclude)) {
	require_once $fractalZipInnerAlgorithmInclude;
}
unset($fractalZipInnerAlgorithmInclude);
