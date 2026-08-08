<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';

/**
 * Collect PHP + host facts for fractal_zip / fz tooling. Run on your server:
 *
 *   php fz_server_report.php
 *   php fz_server_report.php --json
 *
 * In a browser: open fz_server_report.php (plain text) or fz_server_report.php?json=1 (JSON).
 * If you see a blank page, PHP may be hiding errors; this script turns on display_errors for non-CLI.
 *
 * Paste the full output when asking how to install missing pieces.
 *
 * For local-vs-live extract parity (zpaq/7z/arc, PHP disable_functions, .fz round-trip):
 *   php examples/fzc_capability_report.php --json
 *   docs/WEB_LOCAL_PARITY.md
 *
 * Optional: copy fz_fractal_local_env.php.example → fz_fractal_local_env.php with putenv() paths
 * so this script sees the same 7z/ffmpeg as compress/extract.
 */
if (PHP_SAPI !== 'cli') {
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
}

$cliArgv = (isset($_SERVER['argv']) && is_array($_SERVER['argv'])) ? $_SERVER['argv'] : array();
$jsonOut = in_array('--json', $cliArgv, true)
	|| (PHP_SAPI !== 'cli' && isset($_GET['json']) && (string) $_GET['json'] !== '' && (string) $_GET['json'] !== '0');

$examplesDir = __DIR__;
$repoRoot = dirname($examplesDir);
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();
$lib = $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
if (!is_file($lib)) {
	$lib = $examplesDir . DIRECTORY_SEPARATOR . 'fractal_zip.php';
}

$exts = array(
	'zlib',
	'mbstring',
	'openssl',
	'fileinfo',
	'json',
	'pcntl',
);

$funcs = array('proc_open', 'proc_close', 'exec', 'shell_exec', 'escapeshellarg', 'escapeshellcmd');

$report = array(
	'generated_at' => gmdate('c'),
	'php' => array(
		'version' => PHP_VERSION,
		'sapi' => PHP_SAPI,
		'os' => PHP_OS_FAMILY,
		'uname' => function_exists('php_uname') ? php_uname('a') : null,
		'memory_limit' => ini_get('memory_limit'),
		'max_execution_time' => ini_get('max_execution_time'),
		'disable_functions' => ini_get('disable_functions'),
		'open_basedir' => ini_get('open_basedir'),
	),
	'extensions' => array(),
	'functions' => array(),
	'fractal_zip_php' => $lib,
	'fractal_zip_loadable' => is_file($lib),
	'external_tools' => array(),
	'fzcd_flac' => array(
		'enabled_fn' => false,
		'tools_ok' => null,
	),
	'fractal_zip_load_error' => null,
	'fz_fractal_local_env_php_present' => is_file(__DIR__ . DIRECTORY_SEPARATOR . 'fz_fractal_local_env.php'),
	'env_putenv_fractal_zip' => array(
		'FRACTAL_ZIP_ZPAQ' => getenv('FRACTAL_ZIP_ZPAQ') === false ? null : (string) getenv('FRACTAL_ZIP_ZPAQ'),
		'FRACTAL_ZIP_7Z' => getenv('FRACTAL_ZIP_7Z') === false ? null : (string) getenv('FRACTAL_ZIP_7Z'),
		'FRACTAL_ZIP_FFMPEG' => getenv('FRACTAL_ZIP_FFMPEG') === false ? null : (string) getenv('FRACTAL_ZIP_FFMPEG'),
		'FRACTAL_ZIP_FFPROBE' => getenv('FRACTAL_ZIP_FFPROBE') === false ? null : (string) getenv('FRACTAL_ZIP_FFPROBE'),
	),
	'env_putenv_checks' => array(),
);

foreach ($exts as $e) {
	$report['extensions'][$e] = extension_loaded($e);
}
foreach ($funcs as $f) {
	$report['functions'][$f] = function_exists($f);
}
foreach ($report['env_putenv_fractal_zip'] as $ek => $ev) {
	$path = is_string($ev) ? trim($ev) : '';
	$report['env_putenv_checks'][$ek] = array(
		'set' => $path !== '',
		'exists' => $path !== '' ? file_exists($path) : false,
		'is_file' => $path !== '' ? is_file($path) : false,
		'is_executable' => $path !== '' ? is_executable($path) : false,
	);
}

if ($report['fractal_zip_loadable']) {
	try {
		require_once $lib;
	} catch (Throwable $e) {
		$report['fractal_zip_load_error'] = $e->getMessage() . ' in ' . $e->getFile() . ':' . (string) $e->getLine();
	}
	if ($report['fractal_zip_load_error'] === null && class_exists('fractal_zip', false)) {
		fractal_zip_ensure_flac_pac_loaded();
		$report['external_tools'] = array(
			'zpaq' => fractal_zip::zpaq_executable(),
			'7z' => fractal_zip::seven_zip_executable(),
			'zstd' => fractal_zip::zstd_executable(),
			'brotli' => fractal_zip::brotli_executable(),
			'xz' => fractal_zip::xz_executable(),
			'freearc_arc' => fractal_zip::freearc_executable(),
		);
		$report['fzcd_flac']['enabled_fn'] = function_exists('fractal_zip_flac_pac_enabled');
		if (function_exists('fractal_zip_flac_pac_enabled') && function_exists('fractal_zip_flac_pac_tools_ok')) {
			$report['fzcd_flac']['enabled'] = fractal_zip_flac_pac_enabled();
			$report['fzcd_flac']['tools_ok'] = fractal_zip_flac_pac_tools_ok();
		}
	}
}

$pathProbe = array('ffmpeg', 'ffprobe', 'gzip', 'zpaq', '7z', '7za', 'p7zip', 'zstd', 'brotli', 'xz', 'arc');
$report['path_which'] = array();
foreach ($pathProbe as $bin) {
	$report['path_which'][$bin] = null;
	if (!function_exists('shell_exec')) {
		continue;
	}
	$cmd = 'command -v ' . escapeshellarg($bin) . ' 2>/dev/null';
	$out = shell_exec($cmd);
	if (is_string($out)) {
		$line = trim($out);
		$report['path_which'][$bin] = $line !== '' ? $line : null;
	}
}

if ($jsonOut) {
	if (PHP_SAPI !== 'cli') {
		header('Content-Type: application/json; charset=UTF-8');
	}
	$js = bench_json_encode_try($report, true);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (fz_server_report): ' . json_last_error_msg() . "\n");
	}
	echo ($js ?? '{}') . "\n";
	exit(0);
}

if (PHP_SAPI !== 'cli') {
	header('Content-Type: text/plain; charset=UTF-8');
}

echo "=== fractal_zip server report ===\n";
echo "Time (UTC): {$report['generated_at']}\n\n";

echo "[Local env]\n";
echo '  fz_fractal_local_env.php: ' . ($report['fz_fractal_local_env_php_present'] ? 'yes (loaded via fz_local_env_bootstrap.php)' : 'no (copy fz_fractal_local_env.php.example → fz_fractal_local_env.php)') . "\n";
foreach ($report['env_putenv_fractal_zip'] as $ek => $ev) {
	echo '  ' . $ek . ': ' . ($ev !== null && $ev !== '' ? $ev : '(not set)') . "\n";
	$chk = $report['env_putenv_checks'][$ek];
	echo '    check: set=' . ($chk['set'] ? 'yes' : 'no')
		. ', exists=' . ($chk['exists'] ? 'yes' : 'no')
		. ', file=' . ($chk['is_file'] ? 'yes' : 'no')
		. ', executable=' . ($chk['is_executable'] ? 'yes' : 'no') . "\n";
}
echo "\n";

echo "[PHP]\n";
foreach ($report['php'] as $k => $v) {
	echo sprintf("  %-22s %s\n", $k . ':', $v === null || $v === '' ? '(empty)' : (string) $v);
}

echo "\n[Extensions]\n";
foreach ($report['extensions'] as $k => $ok) {
	echo '  ' . ($ok ? 'OK  ' : 'MISS') . '  ' . $k . "\n";
}

echo "\n[Functions]\n";
foreach ($report['functions'] as $k => $ok) {
	echo '  ' . ($ok ? 'OK  ' : 'MISS') . '  ' . $k . "\n";
}

echo "\n[fractal_zip.php]\n";
echo '  path: ' . $report['fractal_zip_php'] . "\n";
echo '  loadable: ' . ($report['fractal_zip_loadable'] ? 'yes' : 'no') . "\n";
if ($report['fractal_zip_load_error'] !== null) {
	echo '  load_error: ' . $report['fractal_zip_load_error'] . "\n";
}

echo "\n[Tools resolved by library]\n";
foreach ($report['external_tools'] as $k => $p) {
	echo '  ' . $k . ': ' . ($p !== null ? $p : '(not found)') . "\n";
}

echo "\n[command -v]\n";
foreach ($report['path_which'] as $k => $p) {
	echo '  ' . $k . ': ' . ($p !== null ? $p : '(not found)') . "\n";
}

echo "\n[FZCD / FLAC]\n";
if ($report['fzcd_flac']['tools_ok'] === null) {
	echo "  (fractal_zip not loaded or helpers missing)\n";
} else {
	echo '  fractal_zip_flac_pac_enabled: ' . (($report['fzcd_flac']['enabled'] ?? false) ? 'yes' : 'no') . "\n";
	echo '  fractal_zip_flac_pac_tools_ok: ' . ($report['fzcd_flac']['tools_ok'] ? 'yes' : 'no') . "\n";
}

echo "\n--- End. Paste this entire block for install guidance. ---\n";
