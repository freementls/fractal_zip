<?php
/**
 * CLI prepend: xhprof wall + memory profile for one PHP process.
 *
 *   FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1 php -d auto_prepend_file=benchmarks/xhprof_prepend.php benchmarks/smoke_random.php …
 *
 * (fractal_zip_cli_opcache_bootstrap.php re-execs PHP by default; without the env var the profile captures only the parent.)
 *
 * Writes JSON to FZ_XHPROF_OUT (default sys_get_temp_dir()/fz_xhprof_last.json).
 */
declare(strict_types=1);

if (PHP_SAPI !== 'cli' || !function_exists('xhprof_enable')) {
	return;
}

xhprof_enable(XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

register_shutdown_function(static function (): void {
	if (!function_exists('xhprof_disable')) {
		return;
	}
	$data = xhprof_disable();
	if (!is_array($data) || $data === []) {
		return;
	}
	$path = getenv('FZ_XHPROF_OUT');
	if ($path === false || trim((string) $path) === '') {
		$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_xhprof_last.json';
	}
	$js = bench_json_encode_try($data, false);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (xhprof): ' . json_last_error_msg() . "\n");

		return;
	}
	@file_put_contents((string) $path, $js);
});
