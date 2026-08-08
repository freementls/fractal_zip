<?php
declare(strict_types=1);

/**
 * Quick deploy check for freement.cloud/fractal_zip/examples/.
 * Open in a browser after upload; should not be blank.
 */
header('Content-Type: text/plain; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

echo "fractal_zip examples health\n";
echo 'PHP ' . PHP_VERSION . ' (' . PHP_SAPI . ")\n\n";

$checks = array(
	'bench_json_helpers (examples)' => is_file(__DIR__ . '/bench_json_helpers.php'),
	'bench_json_helpers.impl (examples deploy)' => is_file(__DIR__ . '/bench_json_helpers.impl.php'),
	'bench_json_helpers (benchmarks)' => is_file(dirname(__DIR__) . '/benchmarks/bench_json_helpers.php'),
	'fzc_web_shared.php' => is_file(__DIR__ . '/fzc_web_shared.php'),
	'fractal_zip.php (parent)' => is_file(dirname(__DIR__) . '/fractal_zip.php'),
	'icons/fractal-zip-favicon.svg' => is_file(__DIR__ . '/icons/fractal-zip-favicon.svg'),
	'favicon.ico' => is_file(__DIR__ . '/favicon.ico'),
	'web_jobs writable' => is_dir(__DIR__ . '/web_jobs') && is_writable(__DIR__ . '/web_jobs'),
);

foreach ($checks as $label => $ok) {
	echo ($ok ? '[ok] ' : '[MISSING] ') . $label . "\n";
}

echo "\nNote: browsers always request https://YOUR-DOMAIN/favicon.ico (site root).\n";
echo "Files under /fractal_zip/examples/ do not satisfy that. Copy examples/favicon.ico to the vhost document root.\n";
