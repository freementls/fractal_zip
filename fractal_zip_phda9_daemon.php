<?php
declare(strict_types=1);

/**
 * Long-lived phda9 job server — serializes heavy compress/decompress (one ~1 GiB worker),
 * avoids N forked phda9_no_lstm processes. Startup is still per-job (phda9 CLI); use
 * tokenized_zpaq for general prose when bytes are close and speed matters.
 *
 * Env:
 * - FRACTAL_ZIP_PHDA9_DAEMON — 1 (default on), 0 off for debug direct exec (still singleton-locked)
 * - FRACTAL_ZIP_PHDA9_DAEMON_SOCK — unix socket path (default sys temp)
 * - FRACTAL_ZIP_PHDA9_DAEMON_WARM — 1 warm page-cache at start (default on)
 */

function fractal_zip_phda9_daemon_mode(): string
{
	$e = getenv('FRACTAL_ZIP_PHDA9_DAEMON');
	if ($e === false || trim((string) $e) === '') {
		return '1';
	}
	return strtolower(trim((string) $e));
}

function fractal_zip_phda9_heavy_tool(string $toolId): bool
{
	return $toolId === 'phda9' || $toolId === 'phda9_no_lstm';
}

function fractal_zip_phda9_singleton_lock_path(): string
{
	$uid = function_exists('posix_getuid') ? (int) posix_getuid() : 0;
	return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fz_phda9_singleton_' . $uid . '.lock';
}

/**
 * Serialize direct phda9/phda9_no_lstm exec (~1 GiB RSS each). Blocks until lock held.
 *
 * @template T
 * @param callable(): T $fn
 * @return T|null null when lock unavailable
 */
function fractal_zip_phda9_singleton_with_lock(callable $fn)
{
	$fp = @fopen(fractal_zip_phda9_singleton_lock_path(), 'c');
	if (!is_resource($fp)) {
		return null;
	}
	try {
		if (!@flock($fp, LOCK_EX)) {
			return null;
		}
		return $fn();
	} finally {
		@flock($fp, LOCK_UN);
		@fclose($fp);
	}
}

/** Kill stray phda9_no_LSTM orphans (benchmarks bypassing daemon). */
function fractal_zip_phda9_reap_orphans(): void
{
	$v = getenv('FRACTAL_ZIP_PHDA9_REAP');
	if ($v !== false && in_array(strtolower(trim((string) $v)), array('0', 'off', 'false', 'no'), true)) {
		return;
	}
	if (PHP_OS_FAMILY === 'Windows') {
		return;
	}
	$uid = function_exists('posix_getuid') ? (int) posix_getuid() : 0;
	@exec('pkill -9 -u ' . (int) $uid . ' -f phda9_no_LSTM 2>/dev/null');
	@exec('pkill -9 -u ' . (int) $uid . ' -f phda9_no_lstm 2>/dev/null');
}

function fractal_zip_phda9_daemon_enabled_for_tool(string $toolId): bool
{
	if (!fractal_zip_phda9_heavy_tool($toolId)) {
		return false;
	}
	if (!empty($GLOBALS['fractal_zip_phda9_daemon_in_job'])) {
		return false;
	}
	$mode = fractal_zip_phda9_daemon_mode();
	if (in_array($mode, array('0', 'off', 'false', 'no'), true)) {
		return false;
	}
	return true;
}

function fractal_zip_phda9_daemon_socket_path(): string
{
	$e = getenv('FRACTAL_ZIP_PHDA9_DAEMON_SOCK');
	if (is_string($e) && trim($e) !== '') {
		return trim($e);
	}
	$uid = function_exists('posix_getuid') ? (int) posix_getuid() : 0;
	return rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fz_phda9_daemon_' . $uid . '.sock';
}

function fractal_zip_phda9_daemon_pid_path(): string
{
	return fractal_zip_phda9_daemon_socket_path() . '.pid';
}

/** @return resource|null */
function fractal_zip_phda9_daemon_connect(float $timeoutSec = 2.0)
{
	$sock = fractal_zip_phda9_daemon_socket_path();
	if (!file_exists($sock)) {
		return null;
	}
	$fp = @stream_socket_client('unix://' . $sock, $errno, $errstr, $timeoutSec);
	return is_resource($fp) ? $fp : null;
}

function fractal_zip_phda9_daemon_ensure_running(): bool
{
	if (fractal_zip_phda9_daemon_connect(0.5) !== null) {
		return true;
	}
	$pidFile = fractal_zip_phda9_daemon_pid_path();
	$startLock = @fopen($pidFile . '.start.lock', 'c');
	if (!is_resource($startLock)) {
		return fractal_zip_phda9_daemon_connect(2.0) !== null;
	}
	if (!@flock($startLock, LOCK_EX)) {
		@fclose($startLock);
		return fractal_zip_phda9_daemon_connect(2.0) !== null;
	}
	try {
		if (fractal_zip_phda9_daemon_connect(0.5) !== null) {
			return true;
		}
		if (is_file($pidFile)) {
			$pid = (int) trim((string) file_get_contents($pidFile));
			if ($pid > 0 && function_exists('posix_kill') && @posix_kill($pid, 0)) {
				return fractal_zip_phda9_daemon_connect(2.0) !== null;
			}
			@unlink($pidFile);
		}
		$php = PHP_BINARY;
		$script = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_daemon.php';
		$cmd = escapeshellarg($php) . ' ' . escapeshellarg($script) . ' serve';
		if (PHP_OS_FAMILY === 'Windows') {
			return false;
		}
		$spec = array(
			0 => array('file', '/dev/null', 'r'),
			1 => array('file', '/dev/null', 'w'),
			2 => array('file', '/dev/null', 'w'),
		);
		$proc = @proc_open($cmd, $spec, $pipes, __DIR__, null, array('bypass_shell' => true));
		if (!is_resource($proc)) {
			return false;
		}
		$deadline = microtime(true) + 5.0;
		while (microtime(true) < $deadline) {
			if (fractal_zip_phda9_daemon_connect(0.2) !== null) {
				return true;
			}
			usleep(100000);
		}
		return fractal_zip_phda9_daemon_connect(1.0) !== null;
	} finally {
		@flock($startLock, LOCK_UN);
		@fclose($startLock);
	}
}

/**
 * @return array{ok: bool, bytes: ?string, seconds: float, error: string}
 */
function fractal_zip_phda9_daemon_request(string $cmd, string $toolId, string $payload, ?string $dictPath = null, int $timeoutSec = 0): array
{
	$fail = static function (string $msg): array {
		return array('ok' => false, 'bytes' => null, 'seconds' => 0.0, 'error' => $msg);
	};
	if (!fractal_zip_phda9_daemon_ensure_running()) {
		return $fail('daemon_start_failed');
	}
	$fp = fractal_zip_phda9_daemon_connect(3.0);
	if ($fp === null) {
		return $fail('daemon_connect_failed');
	}
	stream_set_timeout($fp, 7200);
	$req = array(
		'cmd' => $cmd,
		'tool' => $toolId,
		'payload_b64' => base64_encode($payload),
		'dict' => $dictPath,
		// Client env does not reach the daemon process; ship the per-job wall here.
		'timeout_sec' => max(0, $timeoutSec),
	);
	$line = json_encode($req, JSON_UNESCAPED_UNICODE);
	if (!is_string($line)) {
		fclose($fp);
		return $fail('json_encode_failed');
	}
	fwrite($fp, $line . "\n");
	$respLine = fgets($fp);
	fclose($fp);
	if (!is_string($respLine) || trim($respLine) === '') {
		return $fail('daemon_empty_response');
	}
	$resp = json_decode(trim($respLine), true);
	if (!is_array($resp)) {
		return $fail('daemon_bad_json');
	}
	$ok = !empty($resp['ok']);
	$bytes = null;
	if ($ok && is_string($resp['payload_b64'] ?? null) && $resp['payload_b64'] !== '') {
		$dec = base64_decode((string) $resp['payload_b64'], true);
		$bytes = is_string($dec) ? $dec : null;
		if ($bytes === null) {
			return $fail('daemon_b64_decode_failed');
		}
	}
	return array(
		'ok' => $ok && $bytes !== null,
		'bytes' => $bytes,
		'seconds' => (float) ($resp['seconds'] ?? 0.0),
		'error' => (string) ($resp['error'] ?? ''),
	);
}

function fractal_zip_phda9_daemon_compress(string $toolId, string $plain, ?string $dictPath = null, int $timeoutSec = 0): ?array
{
	if ($plain === '') {
		return array('bytes' => '', 'seconds' => 0.0);
	}
	$r = fractal_zip_phda9_daemon_request('compress', $toolId, $plain, $dictPath, $timeoutSec);
	if (!$r['ok'] || !is_string($r['bytes'])) {
		return null;
	}
	return array('bytes' => $r['bytes'], 'seconds' => $r['seconds']);
}

function fractal_zip_phda9_daemon_decompress(string $toolId, string $arcBytes, ?string $dictPath = null): ?string
{
	if ($arcBytes === '') {
		return '';
	}
	$r = fractal_zip_phda9_daemon_request('decompress', $toolId, $arcBytes, $dictPath);
	return $r['ok'] && is_string($r['bytes']) ? $r['bytes'] : null;
}

/**
 * Representative English prewarm payload. Page-caches the tool binary, model alloc
 * path, and external dict on daemon start so the first real job skips cold I/O.
 * Size via FRACTAL_ZIP_PHDA9_DAEMON_WARM_BYTES (default 4096) — phda9 predictor
 * state is per-exec, so a large payload buys nothing and only delays startup.
 */
function fractal_zip_phda9_daemon_warm_payload(): string
{
	$e = getenv('FRACTAL_ZIP_PHDA9_DAEMON_WARM_BYTES');
	$want = ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e)))
		? max(64, min(1048576, (int) trim((string) $e)))
		: 4096;
	foreach (array(
		__DIR__ . DIRECTORY_SEPARATOR . 'test_files115' . DIRECTORY_SEPARATOR . 'lcet10.txt',
		__DIR__ . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt',
	) as $fixture) {
		if (is_file($fixture) && filesize($fixture) >= $want) {
			$head = @file_get_contents($fixture, false, null, 0, $want);
			if (is_string($head) && $head !== '') {
				return $head;
			}
		}
	}
	$para = 'The quick growth of electronic publishing has changed the way in which '
		. 'libraries and archives preserve the written record. Text that once lived '
		. 'only on paper now moves through networks, and the tools that compress it '
		. "must model the statistics of ordinary English prose.\n\n";
	return substr(str_repeat($para, (int) ceil($want / strlen($para))), 0, $want);
}

function fractal_zip_phda9_daemon_warm(string $toolId): void
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$warm = getenv('FRACTAL_ZIP_PHDA9_DAEMON_WARM');
	if ($warm !== false && in_array(strtolower(trim((string) $warm)), array('0', 'off', 'false', 'no'), true)) {
		return;
	}
	$exe = fractal_zip_paq_discover_executable($toolId);
	if ($exe === null) {
		return;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_warm_' . getmypid();
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'w.txt';
	$out = $tmp . DIRECTORY_SEPARATOR . 'w.paq';
	file_put_contents($in, fractal_zip_phda9_daemon_warm_payload());
	$dict = fractal_zip_paq_phda9_dict_path();
	$argv = array($exe, 'C', basename($in), basename($out));
	if ($dict !== null) {
		$argv[] = $dict;
	}
	$cwd = getcwd();
	@chdir($tmp);
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd($argv);
	$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' 2>/dev/null';
	exec($cmd);
	if ($cwd !== false) {
		@chdir($cwd);
	}
	@unlink($in);
	@unlink($out);
	@rmdir($tmp);
}

function fractal_zip_phda9_daemon_handle_job(array $job): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	$cmd = (string) ($job['cmd'] ?? '');
	$toolId = (string) ($job['tool'] ?? 'phda9_no_lstm');
	if (!in_array($toolId, array('phda9', 'phda9_no_lstm'), true)) {
		return array('ok' => false, 'error' => 'bad_tool');
	}
	$payload = base64_decode((string) ($job['payload_b64'] ?? ''), true);
	if (!is_string($payload)) {
		return array('ok' => false, 'error' => 'bad_payload');
	}
	$dictPath = isset($job['dict']) && is_string($job['dict']) && $job['dict'] !== '' ? $job['dict'] : null;
	$exe = fractal_zip_paq_discover_executable($toolId);
	if ($exe === null) {
		return array('ok' => false, 'error' => 'tool_missing');
	}
	$GLOBALS['fractal_zip_phda9_daemon_in_job'] = true;
	$jobTimeout = max(0, (int) ($job['timeout_sec'] ?? 0));
	$prevTimeoutEnv = getenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC');
	if ($jobTimeout > 0) {
		putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . $jobTimeout);
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_job_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$t0 = microtime(true);
	try {
		if ($cmd === 'compress') {
			$in = $tmp . DIRECTORY_SEPARATOR . 'in.txt';
			$out = $tmp . DIRECTORY_SEPARATOR . 'out.paq';
			file_put_contents($in, $payload);
			$prev = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
			if ($dictPath !== null) {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
			}
			$r = fractal_zip_paq_compress_file($toolId, $exe, $in);
			if ($prev === false) {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
			} else {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prev);
			}
			$arc = $r['bytes'] ?? null;
			if (!is_string($arc) || $arc === '') {
				return array('ok' => false, 'error' => 'compress_failed', 'seconds' => microtime(true) - $t0);
			}
			return array(
				'ok' => true,
				'payload_b64' => base64_encode($arc),
				'seconds' => round(microtime(true) - $t0, 3),
			);
		}
		if ($cmd === 'decompress') {
			$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.paq';
			$out = $tmp . DIRECTORY_SEPARATOR . 'plain.out';
			file_put_contents($arc, $payload);
			$prev = getenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
			if ($dictPath !== null) {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $dictPath);
			}
			$ok = fractal_zip_paq_decompress_to_file($toolId, $exe, $arc, $out);
			if ($prev === false) {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT');
			} else {
				putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . (string) $prev);
			}
			if (!$ok || !is_file($out)) {
				return array('ok' => false, 'error' => 'decompress_failed', 'seconds' => microtime(true) - $t0);
			}
			$plain = (string) file_get_contents($out);
			return array(
				'ok' => true,
				'payload_b64' => base64_encode($plain),
				'seconds' => round(microtime(true) - $t0, 3),
			);
		}
		return array('ok' => false, 'error' => 'bad_cmd');
	} finally {
		if ($jobTimeout > 0) {
			if ($prevTimeoutEnv === false) {
				putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC');
			} else {
				putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=' . (string) $prevTimeoutEnv);
			}
		}
		unset($GLOBALS['fractal_zip_phda9_daemon_in_job']);
		if (is_dir($tmp)) {
			foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
				if (is_file($f)) {
					@unlink($f);
				}
			}
			@rmdir($tmp);
		}
	}
}

function fractal_zip_phda9_daemon_serve(): void
{
	if (PHP_OS_FAMILY === 'Windows') {
		fwrite(STDERR, "phda9 daemon: unix sockets unavailable on Windows\n");
		exit(1);
	}
	$sockPath = fractal_zip_phda9_daemon_socket_path();
	@unlink($sockPath);
	$server = stream_socket_server('unix://' . $sockPath, $errno, $errstr);
	if ($server === false) {
		fwrite(STDERR, "phda9 daemon: bind failed: $errstr\n");
		exit(1);
	}
	@chmod($sockPath, 0600);
	file_put_contents(fractal_zip_phda9_daemon_pid_path(), (string) getmypid());
	fractal_zip_phda9_daemon_warm('phda9_no_lstm');
	if (is_resource(STDERR)) {
		@fwrite(STDERR, '[phda9-daemon] listening ' . $sockPath . ' pid=' . getmypid() . "\n");
	}
	while (true) {
		$client = @stream_socket_accept($server, 5);
		if ($client === false) {
			continue;
		}
		$line = fgets($client);
		if (!is_string($line) || trim($line) === '') {
			fclose($client);
			continue;
		}
		$job = json_decode(trim($line), true);
		if (!is_array($job)) {
			fwrite($client, json_encode(array('ok' => false, 'error' => 'bad_json')) . "\n");
			fclose($client);
			continue;
		}
		$resp = fractal_zip_phda9_daemon_handle_job($job);
		fwrite($client, json_encode($resp, JSON_UNESCAPED_UNICODE) . "\n");
		fclose($client);
	}
}

if (PHP_SAPI === 'cli' && realpath((string) ($argv[0] ?? '')) === realpath(__FILE__)) {
	$sub = $argv[1] ?? 'help';
	if ($sub === 'serve') {
		fractal_zip_phda9_daemon_serve();
		exit(0);
	}
	if ($sub === 'stop') {
		$pidFile = fractal_zip_phda9_daemon_pid_path();
		if (is_file($pidFile)) {
			$pid = (int) trim((string) file_get_contents($pidFile));
			if ($pid > 0) {
				@posix_kill($pid, SIGTERM);
			}
			@unlink($pidFile);
		}
		@unlink(fractal_zip_phda9_daemon_socket_path());
		fractal_zip_phda9_reap_orphans();
		echo "stopped\n";
		exit(0);
	}
	fwrite(STDERR, "Usage: php fractal_zip_phda9_daemon.php serve|stop\n");
	exit(1);
}
