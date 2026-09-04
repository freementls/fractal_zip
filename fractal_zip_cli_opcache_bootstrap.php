<?php
/**
 * Best-effort: run CLI with opcache.enable_cli=1 when OPcache is on but PHP defaults to
 * not caching scripts in CLI (huge win for fractal_zip.php and bench drivers).
 *
 * When OPcache JIT is off or has no buffer (common defaults: opcache.jit=disable, buffer 0),
 * the child process also gets tracing JIT + 128 MiB buffer — large speedups on hot PHP
 * (BMP literal paths, etc.). Override with FRACTAL_ZIP_CLI_JIT=function|tracing|1255… or opt out
 * with FRACTAL_ZIP_NO_CLI_JIT=1.
 *
 * Opt out of re-exec entirely: FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
 * Child guard (opcache CLI re-exec): FRACTAL_ZIP_INTERNAL_CLI_OPACHE=1 (set before re-exec; prevents loops)
 * Child guard (JIT-only re-exec): FRACTAL_ZIP_INTERNAL_JIT_CLI_REEXEC=1
 */
if (PHP_SAPI !== 'cli') {
	return;
}
if (getenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC') === '1') {
	return;
}

/**
 * -d flags to load libfss PHP extension (preserve across CLI re-execs).
 *
 * @return list<string>
 */
$fractalZipCliFssIniArgs = static function (): array {
	if (getenv('FRACTAL_ZIP_NO_CLI_FSS_REEXEC') === '1' || getenv('FRACTAL_ZIP_FSS') === '0') {
		return array();
	}
	if (extension_loaded('fss') && function_exists('fss_ext_count')) {
		/* Already loaded — still pass -d so grandchild re-execs keep it. */
	}
	$fssSo = null;
	$fssRoot = getenv('FRACTAL_ZIP_FSS_ROOT');
	$fssCands = array();
	if(is_string($fssRoot) && trim($fssRoot) !== '') {
		$fssCands[] = rtrim(trim($fssRoot), "/\\") . '/bindings/php_ext/modules/fss.so';
	}
	$extEnv = getenv('FRACTAL_ZIP_FSS_EXT');
	if(is_string($extEnv) && trim($extEnv) !== '') {
		$fssCands[] = trim($extEnv);
	}
	$fssCands[] = dirname(__DIR__) . '/fractal_substring/bindings/php_ext/modules/fss.so';
	$fssCands[] = __DIR__ . '/../fractal_substring/bindings/php_ext/modules/fss.so';
	$fssCands[] = '/srv/http/fractal_substring/bindings/php_ext/modules/fss.so';
	foreach($fssCands as $cand) {
		if(is_file($cand)) {
			$fssSo = $cand;
			break;
		}
	}
	if($fssSo === null) {
		return array();
	}
	return array('-d', 'extension=' . $fssSo);
};
$fssIniArgs = $fractalZipCliFssIniArgs();

// Load FFI so libfzmem.so can back the PHP intern (fmem/fcache). Independent
// of OPcache; one extra re-exec when the module exists but is not loaded.
if (getenv('FRACTAL_ZIP_NO_CLI_FFI_REEXEC') !== '1'
	&& getenv('FRACTAL_ZIP_INTERNAL_FFI_CLI_REEXEC') !== '1') {
	$ffiOn = extension_loaded('ffi');
	$ffiIni = strtolower((string) ini_get('ffi.enable'));
	$ffiIniOff = in_array($ffiIni, array('0', 'false', 'off'), true);
	$ffiSo = '/usr/lib/php/modules/ffi.so';
	if ((!$ffiOn && is_file($ffiSo)) || ($ffiOn && $ffiIniOff)) {
		$scriptFfi = $_SERVER['SCRIPT_FILENAME'] ?? ($argv[0] ?? '');
		if (is_string($scriptFfi) && $scriptFfi !== '' && @is_file($scriptFfi) && isset($argv) && is_array($argv)) {
			putenv('FRACTAL_ZIP_INTERNAL_FFI_CLI_REEXEC=1');
			$ffiArgv = array(PHP_BINARY);
			if (!$ffiOn) {
				$ffiArgv[] = '-d';
				$ffiArgv[] = 'extension=ffi';
			}
			$ffiArgv[] = '-d';
			$ffiArgv[] = 'ffi.enable=1';
			foreach ($fssIniArgs as $a) {
				$ffiArgv[] = $a;
			}
			$ffiArgv[] = $scriptFfi;
			$ffiArgv = array_merge($ffiArgv, array_slice($argv, 1));
			$procFfi = @proc_open($ffiArgv, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipesFfi, getcwd() !== false ? getcwd() : null, null, array('bypass_shell' => true));
			if (is_resource($procFfi)) {
				exit(proc_close($procFfi));
			}
			putenv('FRACTAL_ZIP_INTERNAL_FFI_CLI_REEXEC');
		}
	}
}
// Load libfss PHP extension when nothing else re-execs (KEEP ~3× COUNT).
if (getenv('FRACTAL_ZIP_NO_CLI_FSS_REEXEC') !== '1'
	&& getenv('FRACTAL_ZIP_INTERNAL_FSS_CLI_REEXEC') !== '1'
	&& getenv('FRACTAL_ZIP_FSS') !== '0'
	&& $fssIniArgs !== array()) {
	$fssOn = extension_loaded('fss') && function_exists('fss_ext_count');
	if (!$fssOn) {
		$argvFss = $GLOBALS['argv'] ?? ($_SERVER['argv'] ?? null);
		$scriptFss = $_SERVER['SCRIPT_FILENAME'] ?? '';
		if ($scriptFss === '' && is_array($argvFss) && isset($argvFss[0])) {
			$scriptFss = (string) $argvFss[0];
		}
		if (is_string($scriptFss) && $scriptFss !== '' && @is_file($scriptFss) && is_array($argvFss)) {
			putenv('FRACTAL_ZIP_INTERNAL_FSS_CLI_REEXEC=1');
			$fssArgv = array_merge(array(PHP_BINARY), $fssIniArgs, array($scriptFss), array_slice($argvFss, 1));
			$procFss = @proc_open($fssArgv, array(0 => STDIN, 1 => STDOUT, 2 => STDERR), $pipesFss, getcwd() !== false ? getcwd() : null, null, array('bypass_shell' => true));
			if (is_resource($procFss)) {
				exit(proc_close($procFss));
			}
			putenv('FRACTAL_ZIP_INTERNAL_FSS_CLI_REEXEC');
		}
	}
}
if (getenv('FRACTAL_ZIP_INTERNAL_CLI_OPACHE') === '1' || getenv('FRACTAL_ZIP_INTERNAL_JIT_CLI_REEXEC') === '1') {
	return;
}
if (!extension_loaded('Zend OPcache')) {
	return;
}
$opcacheOn = filter_var(ini_get('opcache.enable'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
if ($opcacheOn === null) {
	$v = strtolower((string) ini_get('opcache.enable'));
	$opcacheOn = in_array($v, ['1', 'on', 'true', 'yes'], true);
} else {
	$opcacheOn = (bool) $opcacheOn;
}
if (!$opcacheOn) {
	return;
}
$enableCli = filter_var(ini_get('opcache.enable_cli'), FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
if ($enableCli === null) {
	$v2 = strtolower((string) ini_get('opcache.enable_cli'));
	$enableCli = in_array($v2, ['1', 'on', 'true', 'yes'], true);
} else {
	$enableCli = (bool) $enableCli;
}

/**
 * @return list<string> extra -d flags for child PHP, or [] when no JIT boost requested.
 */
$fractalZipCliJitIniArgs = static function (): array {
	if (getenv('FRACTAL_ZIP_NO_CLI_JIT') === '1') {
		return array();
	}
	$jitStr = strtolower((string) ini_get('opcache.jit'));
	$buf = (int) ini_get('opcache.jit_buffer_size');
	$override = getenv('FRACTAL_ZIP_CLI_JIT');
	if ($override !== false && trim((string) $override) !== '') {
		$m = strtolower(trim((string) $override));
		if ($m === '0' || $m === 'off' || $m === 'disable' || $m === 'no') {
			return array();
		}
		$jitVal = ctype_digit($m) ? $m : $m;
		return array('-d', 'opcache.jit_buffer_size=128M', '-d', 'opcache.jit=' . $jitVal);
	}
	$needsBoost = ($buf === 0)
		|| $jitStr === 'disable'
		|| $jitStr === '0'
		|| $jitStr === 'off'
		|| ($jitStr === '' && $buf === 0);
	if (!$needsBoost) {
		return array();
	}
	return array('-d', 'opcache.jit_buffer_size=128M', '-d', 'opcache.jit=tracing');
};
$jitArgs = $fractalZipCliJitIniArgs();

// Persistent compiled-script cache across CLI invocations (the library alone costs
// ~2 s to parse). Timestamp validation stays on, so edits invalidate automatically.
// Opt out: FRACTAL_ZIP_NO_CLI_OPACHE_FILE_CACHE=1.
if (getenv('FRACTAL_ZIP_NO_CLI_OPACHE_FILE_CACHE') !== '1'
	&& trim((string) ini_get('opcache.file_cache')) === '') {
	$uid = function_exists('posix_getuid') ? (int) posix_getuid() : 0;
	$fcDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fz_opcache_' . $uid;
	if (is_dir($fcDir) || @mkdir($fcDir, 0700, true)) {
		$jitArgs[] = '-d';
		$jitArgs[] = 'opcache.file_cache=' . $fcDir;
	}
}

global $argv;
if (!is_array($argv)) {
	return;
}
$script = $_SERVER['SCRIPT_FILENAME'] ?? null;
if (!is_string($script) || $script === '' || !@is_file($script)) {
	if (!isset($argv[0]) || !is_string($argv[0]) || $argv[0] === '' || !@is_file($argv[0])) {
		return;
	}
	$script = $argv[0];
}

if ($enableCli) {
	if ($jitArgs === array()) {
		return;
	}
	putenv('FRACTAL_ZIP_INTERNAL_JIT_CLI_REEXEC=1');
	$childArgv = array_merge(
		array(PHP_BINARY, '-d', 'opcache.enable_cli=1'),
		$jitArgs,
		$fssIniArgs,
		array($script),
		array_slice($argv, 1)
	);
	$descriptors = array(0 => STDIN, 1 => STDOUT, 2 => STDERR);
	$proc = @proc_open(
		$childArgv,
		$descriptors,
		$pipes,
		getcwd() !== false ? getcwd() : null,
		null,
		array('bypass_shell' => true)
	);
	if (!is_resource($proc)) {
		putenv('FRACTAL_ZIP_INTERNAL_JIT_CLI_REEXEC');
		if (is_resource(STDERR)) {
			fwrite(STDERR, "fractal_zip: could not re-exec with OPcache JIT boost; continuing with current JIT settings\n");
		}
		return;
	}
	$exitCode = proc_close($proc);
	exit($exitCode);
}

putenv('FRACTAL_ZIP_INTERNAL_CLI_OPACHE=1');
$childArgv = array_merge(
	array(PHP_BINARY, '-d', 'opcache.enable_cli=1'),
	$jitArgs,
	$fssIniArgs,
	array($script),
	array_slice($argv, 1)
);
// Do not use pcntl_exec: on some PHP builds (e.g. PHP 8.5) it can execve a broken argv and leave the
// new CLI parsing garbage as PHP source. proc_open is reliable (same as `php -d opcache.enable_cli=1` …).
$descriptors = array(0 => STDIN, 1 => STDOUT, 2 => STDERR);
$proc = @proc_open(
	$childArgv,
	$descriptors,
	$pipes,
	getcwd() !== false ? getcwd() : null,
	null,
	array('bypass_shell' => true)
);
if (!is_resource($proc)) {
	putenv('FRACTAL_ZIP_INTERNAL_CLI_OPACHE');
	if (is_resource(STDERR)) {
		fwrite(STDERR, "fractal_zip: could not re-exec with opcache.enable_cli=1; continuing with default CLI OPcache settings\n");
	}
	return;
}
$exitCode = proc_close($proc);
exit($exitCode);
