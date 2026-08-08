<?php
declare(strict_types=1);

/**
 * Web deploy entry for bench JSON helpers. Prefer repo benchmarks/ copy when present.
 */
if (function_exists('bench_json_encode_options')) {
	return;
}

$repoBench = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
if (is_file($repoBench)) {
	require_once $repoBench;
	return;
}

$implBench = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.impl.php';
$implLocal = __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.impl.php';
if (is_file($implBench)) {
	require_once $implBench;
} elseif (is_file($implLocal)) {
	require_once $implLocal;
}
