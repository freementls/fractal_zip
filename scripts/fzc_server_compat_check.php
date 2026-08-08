<?php
declare(strict_types=1);

/**
 * fzc_server_compat_check.php
 *
 * Single-file live host readiness check for .fz compatibility.
 * Run either:
 *   CLI: php scripts/fzc_server_compat_check.php [--json]
 *   Web: https://host/.../fzc_server_compat_check.php[?json=1]
 *
 * Exit code (CLI):
 *   0 => all required checks passed
 *   1 => one or more error-level gaps detected
 */

function fzc_sc_is_web(): bool {
	return PHP_SAPI !== 'cli' && PHP_SAPI !== 'phpdbg';
}

function fzc_sc_wants_json(?array $argv = null): bool {
	if (fzc_sc_is_web()) {
		return isset($_GET['json']) && (string) $_GET['json'] !== '0';
	}
	if (!is_array($argv)) {
		$argv = array();
	}
	for ($i = 1, $n = count($argv); $i < $n; $i++) {
		$a = (string) $argv[$i];
		if ($a === '--json' || $a === '-j') {
			return true;
		}
	}
	return false;
}

function fzc_sc_bool_env_enabled(string $name): bool {
	$v = getenv($name);
	if ($v === false) {
		return false;
	}
	$s = strtolower(trim((string) $v));
	return !($s === '' || $s === '0' || $s === 'off' || $s === 'false' || $s === 'no');
}

function fzc_sc_find_fractal_zip(): ?string {
	$env = getenv('FRACTAL_ZIP_PHP');
	if ($env !== false && trim((string) $env) !== '' && is_file((string) $env)) {
		return (string) $env;
	}
	$candidates = array(
		__DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php',
		dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php',
		getcwd() . DIRECTORY_SEPARATOR . 'fractal_zip.php',
	);
	foreach ($candidates as $p) {
		if (is_file($p)) {
			return $p;
		}
	}
	return null;
}

function fzc_sc_try_load_env_bootstrap(?string $fractalZipPath): void {
	$candidates = array();
	if (is_string($fractalZipPath) && $fractalZipPath !== '') {
		$root = dirname($fractalZipPath);
		$candidates[] = $root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
	}
	$candidates[] = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fz_local_env_bootstrap.php';
	for ($i = 0, $n = count($candidates); $i < $n; $i++) {
		$p = (string) $candidates[$i];
		if (is_file($p)) {
			require_once $p;
			return;
		}
	}
}

function fzc_sc_resolve_tool_from_path(string $bin): ?string {
	$path = getenv('PATH');
	if (!is_string($path) || $path === '') {
		return null;
	}
	$dirs = explode(PATH_SEPARATOR, $path);
	for ($i = 0, $n = count($dirs); $i < $n; $i++) {
		$d = (string) $dirs[$i];
		if ($d === '') {
			continue;
		}
		$p = rtrim($d, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $bin;
		if (is_file($p) && is_executable($p)) {
			return $p;
		}
	}
	return null;
}

/**
 * @return array{
 *   ok:bool,
 *   errors:list<string>,
 *   warnings:list<string>,
 *   checks:array<string,mixed>
 * }
 */
function fzc_sc_run(): array {
	$errors = array();
	$warnings = array();
	$checks = array();

	$checks['php'] = array(
		'version' => PHP_VERSION,
		'sapi' => PHP_SAPI,
		'os' => PHP_OS_FAMILY,
	);

	$disableFunctionsRaw = (string) ini_get('disable_functions');
	$disabled = array();
	if (trim($disableFunctionsRaw) !== '') {
		$parts = explode(',', $disableFunctionsRaw);
		for ($i = 0, $n = count($parts); $i < $n; $i++) {
			$fn = strtolower(trim((string) $parts[$i]));
			if ($fn !== '') {
				$disabled[$fn] = true;
			}
		}
	}
	$procOk = function_exists('proc_open') && !isset($disabled['proc_open']);
	$shellExecOk = function_exists('shell_exec') && !isset($disabled['shell_exec']);
	$checks['php_subprocess'] = array(
		'proc_open' => $procOk,
		'shell_exec' => $shellExecOk,
		'disable_functions' => $disableFunctionsRaw,
	);
	if (!$procOk || !$shellExecOk) {
		$errors[] = 'PHP subprocess capability missing (need proc_open + shell_exec in this SAPI).';
	}

	$fzPath = fzc_sc_find_fractal_zip();
	fzc_sc_try_load_env_bootstrap($fzPath);
	// Re-resolve after bootstrap in case FRACTAL_ZIP_PHP was set there.
	$fzPath = fzc_sc_find_fractal_zip();
	$checks['fractal_zip_php'] = array('path' => $fzPath, 'loaded' => false);
	$fzLibDir = null;
	$fractalZipLoadError = null;
	if ($fzPath === null) {
		$errors[] = 'fractal_zip.php not found (set FRACTAL_ZIP_PHP or deploy full repo).';
	} else {
		$fzLibDir = dirname($fzPath);
		try {
			require_once $fzPath;
			$checks['fractal_zip_php']['loaded'] = true;
		} catch (Throwable $e) {
			$checks['fractal_zip_php']['loaded'] = false;
			$fractalZipLoadError = $e->getMessage();
			$errors[] = 'Failed to load fractal_zip.php: ' . $e->getMessage();
		}
	}

	$peerMissing = array();
	if ($fzLibDir !== null) {
		$peerFiles = array(
			'fractal_zip_cli_opcache_bootstrap.php',
			'fractal_zip_flac_pac.php',
			'fractal_zip_image_pac.php',
			'fractal_zip_raster_canonical.php',
			'fractal_zip_literal_stream_index.php',
			'fractal_zip_literal_pac.php',
			'fractal_zip_literal_pac_registry.php',
			'fractal_zip_pdf_native_pac.php',
			'fractal_zip_pdf_jpeg_pac.php',
			'fractal_zip_pdf_jbig2_pac.php',
			'fractal_zip_pdf_jpx_pac.php',
			'fractal_zip_pdf_ccitt_pac.php',
			'fractal_zip_pdf_dict_scan.php',
			'fractal_zip_pdf_objects.php',
			'fractal_zip_pdf_stream_markers.php',
			'fractal_zip_pdf_stream_decode.php',
		);
		for ($i = 0, $n = count($peerFiles); $i < $n; $i++) {
			$bn = (string) $peerFiles[$i];
			if (!is_file($fzLibDir . DIRECTORY_SEPARATOR . $bn)) {
				$peerMissing[] = $bn;
			}
		}
	}
	$checks['library_peers'] = array(
		'missing' => $peerMissing,
		'ok' => $peerMissing === array(),
	);
	if ($fractalZipLoadError !== null) {
		$checks['fractal_zip_php']['load_error'] = $fractalZipLoadError;
	}
	if ($peerMissing !== array()) {
		$errors[] = 'Incomplete library deploy: missing fractal_zip peer files.';
	}

	$toolSpecs = array(
		array('id' => 'zpaq', 'env' => 'FRACTAL_ZIP_ZPAQ', 'path_bin' => 'zpaq', 'resolver' => 'zpaq_executable'),
		array('id' => '7z', 'env' => 'FRACTAL_ZIP_7Z', 'path_bin' => '7z', 'resolver' => 'seven_zip_executable'),
		array('id' => 'arc', 'env' => 'FRACTAL_ZIP_ARC', 'path_bin' => 'arc', 'resolver' => 'freearc_executable'),
		array('id' => 'brotli', 'env' => 'FRACTAL_ZIP_BROTLI', 'path_bin' => 'brotli', 'resolver' => 'brotli_executable'),
		array('id' => 'xz', 'env' => 'FRACTAL_ZIP_XZ', 'path_bin' => 'xz', 'resolver' => 'xz_executable'),
		array('id' => 'zstd', 'env' => 'FRACTAL_ZIP_ZSTD', 'path_bin' => 'zstd', 'resolver' => 'zstd_executable'),
	);

	$tools = array();
	for ($i = 0, $n = count($toolSpecs); $i < $n; $i++) {
		$s = $toolSpecs[$i];
		$id = (string) $s['id'];
		$envName = (string) $s['env'];
		$bin = (string) $s['path_bin'];
		$resolver = (string) $s['resolver'];

		$resolved = null;
		if (class_exists('fractal_zip', false) && method_exists('fractal_zip', $resolver)) {
			/** @phpstan-ignore-next-line */
			$maybe = fractal_zip::{$resolver}();
			if (is_string($maybe) && $maybe !== '') {
				$resolved = $maybe;
			}
		}
		if ($resolved === null) {
			$ev = getenv($envName);
			if (is_string($ev) && trim($ev) !== '') {
				$resolved = trim($ev);
			}
		}
		if ($resolved === null) {
			$resolved = fzc_sc_resolve_tool_from_path($bin);
		}

		$isExec = is_string($resolved) && $resolved !== '' && is_file($resolved) && is_executable($resolved);
		$tools[$id] = array(
			'resolved_path' => $resolved,
			'executable' => $isExec,
			'env_var' => $envName,
		);
		if (!$isExec) {
			$errors[] = "Missing required tool: {$id} (configure {$envName} or PATH).";
		}
	}
	$checks['extract_tools'] = $tools;

	$strictGate = fzc_sc_bool_env_enabled('FZC_WEB_ENFORCE_EXTRACT_COMPAT');
	$checks['strict_web_gate'] = array(
		'enabled' => $strictGate,
		'env' => getenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT') === false ? null : getenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT'),
	);
	if (!$strictGate) {
		$warnings[] = 'FZC_WEB_ENFORCE_EXTRACT_COMPAT is not enabled (recommended for production fail-fast).';
	}

	return array(
		'ok' => $errors === array(),
		'errors' => $errors,
		'warnings' => $warnings,
		'checks' => $checks,
	);
}

function fzc_sc_send_output(array $result, bool $json): void {
	if ($json) {
		if (fzc_sc_is_web()) {
			header('Content-Type: application/json; charset=UTF-8');
		}
		echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
		return;
	}

	if (fzc_sc_is_web()) {
		header('Content-Type: text/plain; charset=UTF-8');
	}
	echo 'FZC SERVER COMPAT CHECK: ' . ($result['ok'] ? 'PASS' : 'FAIL') . "\n";
	echo 'php=' . $result['checks']['php']['version'] . ' sapi=' . $result['checks']['php']['sapi'] . "\n";
	echo "\n";
	echo "Required extract tool status:\n";
	foreach ($result['checks']['extract_tools'] as $id => $row) {
		$st = $row['executable'] ? 'OK' : 'MISSING';
		$p = is_string($row['resolved_path']) ? $row['resolved_path'] : '(not resolved)';
		echo '- ' . $id . ': ' . $st . ' [' . $p . ']' . "\n";
	}
	echo "\n";
	echo 'subprocess: proc_open=' . ($result['checks']['php_subprocess']['proc_open'] ? 'yes' : 'no')
		. ', shell_exec=' . ($result['checks']['php_subprocess']['shell_exec'] ? 'yes' : 'no') . "\n";
	echo 'strict_gate(FZC_WEB_ENFORCE_EXTRACT_COMPAT): ' . ($result['checks']['strict_web_gate']['enabled'] ? 'enabled' : 'disabled') . "\n";
	echo 'library_peers: ' . ($result['checks']['library_peers']['ok'] ? 'complete' : 'incomplete') . "\n";

	if ($result['errors'] !== array()) {
		echo "\nERRORS:\n";
		for ($i = 0, $n = count($result['errors']); $i < $n; $i++) {
			echo '- ' . $result['errors'][$i] . "\n";
		}
	}
	if ($result['warnings'] !== array()) {
		echo "\nWARNINGS:\n";
		for ($i = 0, $n = count($result['warnings']); $i < $n; $i++) {
			echo '- ' . $result['warnings'][$i] . "\n";
		}
	}
	echo "\n";
	echo "Recommended pre-deploy gate (full parity): bash scripts/fzc_live_setup_check.sh\n";
}

$wantsJson = fzc_sc_wants_json(isset($argv) && is_array($argv) ? $argv : null);
$result = fzc_sc_run();
fzc_sc_send_output($result, $wantsJson);

if (!fzc_sc_is_web()) {
	exit($result['ok'] ? 0 : 1);
}

