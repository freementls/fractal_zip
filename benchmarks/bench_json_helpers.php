<?php
declare(strict_types=1);

/**
 * Shared json_encode/json_decode helpers for benchmarks and web examples.
 * Safe to require from multiple paths (examples + encode_pipeline).
 */
if (function_exists('bench_json_encode_options')) {
	return;
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.impl.php';
