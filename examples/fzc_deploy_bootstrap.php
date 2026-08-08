<?php
declare(strict_types=1);

/**
 * Load bench_json_helpers from examples/ (deploy bundle) or ../benchmarks/.
 * On failure, emit a plain-text error instead of a blank page.
 */
function fzc_examples_require_bench_json_helpers(): void {
	static $loaded = false;
	if ($loaded || function_exists('bench_json_encode_options')) {
		$loaded = true;
		return;
	}
	$candidates = array(
		dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php',
		__DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php',
	);
	foreach ($candidates as $path) {
		if (is_file($path)) {
			require_once $path;
			$loaded = true;
			return;
		}
	}
	if (PHP_SAPI === 'cli') {
		fwrite(STDERR, "bench_json_helpers.php missing (need examples/bench_json_helpers.php or benchmarks/bench_json_helpers.php)\n");
		exit(2);
	}
	header('Content-Type: text/plain; charset=UTF-8');
	header('X-Content-Type-Options: nosniff');
	http_response_code(500);
	echo "fractal_zip web deploy is incomplete: missing bench_json_helpers.php.\n";
	echo "Upload examples/bench_json_helpers.php from the repo (or the benchmarks/ folder).\n";
	exit;
}
