<?php
declare(strict_types=1);

/**
 * PAQ-class inner: external phda9 / paq8px / cmix tool integration (encode + bench).
 *
 * Env:
 *   FRACTAL_ZIP_PAQ_TOOLS          colon list override (default phda9:paq8px:paq8pxd:cmix)
 *   FRACTAL_ZIP_PAQ_PHDA9_DICT     phda9 external dictionary file (4th argv to C/D)
 *   FRACTAL_ZIP_PAQ_NATIVE_COMPARE 1 enables native folder compare (default off)
 *   FRACTAL_ZIP_PAQ_NATIVE_MAX_RAW_BYTES (default 134217728)
 *   FRACTAL_ZIP_PAQ_TIMEOUT_SEC    per-tool *compress* timeout (0 = none)
 *   FRACTAL_ZIP_PAQ_EXTRACT_TIMEOUT_SEC  decompress timeout (default 0 = none;
 *                                 compress tip walls must not abort extract)
 *   FRACTAL_ZIP_PAQ_SWEEP          1 try all discovered tools (default 1 when compare on)
 *   FRACTAL_ZIP_PAQ                path override per tool id
 *   FRACTAL_ZIP_PAQ_PHDA9_NO_LSTM  path override for phda9_no_lstm (bundled default)
 *   FRACTAL_ZIP_PAQ_PARALLEL_JOBS  thread count for parallel_cmix / parallel_phda9 (default CPU count)
 *   FRACTAL_ZIP_PAQ_PARALLEL_CMIX    path override for in-repo parallel_cmix binary
 *   FRACTAL_ZIP_PAQ_PARALLEL_PHDA9   path override for in-repo parallel_phda9 binary
 */

/** @var list<string> */
function fractal_zip_paq_default_tool_ids(): array
{
	return array('phda9', 'paq8px', 'paq8pxd', 'cmix');
}

/** @return list<string> */
function fractal_zip_paq_tool_ids_from_env(): array
{
	$e = getenv('FRACTAL_ZIP_PAQ_TOOLS');
	if ($e !== false && trim((string) $e) !== '') {
		$parts = preg_split('/[:;,]/', (string) $e) ?: array();
		$out = array();
		foreach ($parts as $p) {
			$p = strtolower(trim((string) $p));
			if ($p !== '') {
				$out[] = $p;
			}
		}
		return $out !== array() ? $out : fractal_zip_paq_default_tool_ids();
	}
	return fractal_zip_paq_default_tool_ids();
}

function fractal_zip_paq_env_executable(string $toolId): ?string
{
	$key = 'FRACTAL_ZIP_PAQ_' . strtoupper(preg_replace('/[^a-z0-9]/', '_', $toolId) ?: $toolId);
	$e = getenv($key);
	if ($e !== false && trim((string) $e) !== '') {
		$p = trim((string) $e);
		return is_executable($p) ? $p : null;
	}
	$e2 = getenv('FRACTAL_ZIP_PAQ');
	if ($e2 !== false && trim((string) $e2) !== '') {
		$p = trim((string) $e2);
		return is_executable($p) ? $p : null;
	}
	return null;
}

function fractal_zip_paq_discover_executable(string $toolId): ?string
{
	$fromEnv = fractal_zip_paq_env_executable($toolId);
	if ($fromEnv !== null) {
		return $fromEnv;
	}
	$names = array($toolId);
	if ($toolId === 'paq8px') {
		$names[] = 'paq8pxd';
	}
	foreach ($names as $name) {
		$line = trim((string) shell_exec('command -v ' . escapeshellarg($name) . ' 2>/dev/null'));
		if ($line !== '' && is_executable($line)) {
			return $line;
		}
	}
	if ($toolId === 'phda9') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9';
		if (is_executable($bundled)) {
			return $bundled;
		}
	}
	if ($toolId === 'phda9_no_lstm') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9_no_LSTM';
		if (is_executable($bundled)) {
			return $bundled;
		}
	}
	if ($toolId === 'parallel_cmix') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'parallel_paq' . DIRECTORY_SEPARATOR . 'parallel_paq';
		if (is_executable($bundled)) {
			return $bundled;
		}
	}
	if ($toolId === 'parallel_phda9') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'parallel_paq' . DIRECTORY_SEPARATOR . 'parallel_phda9';
		if (is_executable($bundled)) {
			return $bundled;
		}
		$alt = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'parallel_paq' . DIRECTORY_SEPARATOR . 'parallel_paq';
		if (is_executable($alt)) {
			return $alt;
		}
	}
	if ($toolId === 'lpaq9l' || $toolId === 'drt_lpaq9l' || $toolId === 'drt_lpaq9lp'
		|| $toolId === 'drt_lpaq9l_seg' || $toolId === 'drt_lpaq9lp_seg') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'lpaq' . DIRECTORY_SEPARATOR . 'lpaq9l';
		if (is_executable($bundled)) {
			return $bundled;
		}
	}
	if ($toolId === 'lpaq9m') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'lpaq' . DIRECTORY_SEPARATOR . 'lpaq9m';
		if (is_executable($bundled)) {
			return $bundled;
		}
	}
	if ($toolId === 'drt') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'lpaq' . DIRECTORY_SEPARATOR . 'DRT';
		if (is_executable($bundled)) {
			return $bundled;
		}
	}
	if ($toolId === 'mcm') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'mcm' . DIRECTORY_SEPARATOR . 'mcm';
		if (is_executable($bundled)) {
			return $bundled;
		}
	}
	if ($toolId === 'lepton') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'jpeg' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'lepton';
		if (is_executable($bundled) || is_link($bundled)) {
			$real = realpath($bundled);
			if ($real !== false && is_executable($real)) {
				return $real;
			}
		}
		$build = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'jpeg' . DIRECTORY_SEPARATOR . 'lepton' . DIRECTORY_SEPARATOR . 'build' . DIRECTORY_SEPARATOR . 'lepton';
		if (is_executable($build)) {
			return $build;
		}
	}
	if ($toolId === 'brunsli' || $toolId === 'cbrunsli') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'jpeg' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'cbrunsli';
		if (is_link($bundled) || is_executable($bundled)) {
			$real = realpath($bundled);
			if ($real !== false && is_executable($real)) {
				return $real;
			}
		}
	}
	if ($toolId === 'dbrunsli') {
		$bundled = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'jpeg' . DIRECTORY_SEPARATOR . 'bin' . DIRECTORY_SEPARATOR . 'dbrunsli';
		if (is_link($bundled) || is_executable($bundled)) {
			$real = realpath($bundled);
			if ($real !== false && is_executable($real)) {
				return $real;
			}
		}
	}
	if (preg_match('/^zpaq[1-9]$/', $toolId)) {
		return fractal_zip_paq_zpaq_executable();
	}
	return null;
}

function fractal_zip_paq_parallel_jobs(): int
{
	$e = getenv('FRACTAL_ZIP_PAQ_PARALLEL_JOBS');
	if ($e !== false && trim((string) $e) !== '') {
		return max(1, min(32, (int) $e));
	}
	$nproc = trim((string) shell_exec('nproc 2>/dev/null'));
	if ($nproc !== '' && ctype_digit($nproc)) {
		return max(1, min(32, (int) $nproc));
	}
	return 4;
}

function fractal_zip_paq_zpaq_executable(): ?string
{
	$line = trim((string) shell_exec('command -v zpaq 2>/dev/null'));
	if ($line !== '' && is_executable($line)) {
		return $line;
	}
	return null;
}

/**
 * @return array{bytes: string, seconds: float}|null
 */
function fractal_zip_paq_zpaq_compress_bytes(string $payload, int $method = 5): ?array
{
	$exe = fractal_zip_paq_zpaq_executable();
	if ($exe === null || $payload === '') {
		return null;
	}
	$method = max(1, min(9, $method));
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpaq_c_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'in.bin';
	$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.zpaq';
	file_put_contents($in, $payload);
	$t0 = microtime(true);
	$cwd = getcwd();
	@chdir($tmp);
	$cmd = escapeshellarg($exe) . ' a ' . escapeshellarg(basename($arc)) . ' ' . escapeshellarg(basename($in))
		. ' -m' . (string) $method . ' 2>/dev/null';
	exec($cmd, $xo, $ret);
	if ($cwd !== false) {
		@chdir($cwd);
	}
	$sec = round(microtime(true) - $t0, 6);
	if ($ret !== 0 || !is_file($arc)) {
		@unlink($in);
		@unlink($arc);
		@rmdir($tmp);
		return null;
	}
	$raw = (string) file_get_contents($arc);
	@unlink($in);
	@unlink($arc);
	@rmdir($tmp);
	if ($raw === '') {
		return null;
	}
	return array('bytes' => $raw, 'seconds' => $sec);
}

/** lpaq9l memory level 0-9 (usage is 6 + 3*2^N MB); default 7 = ~390 MB. */
function fractal_zip_paq_lpaq_level(): int
{
	$e = getenv('FRACTAL_ZIP_LPAQ_LEVEL');
	if ($e !== false && trim((string) $e) !== '' && ctype_digit(trim((string) $e))) {
		return max(0, min(9, (int) trim((string) $e)));
	}
	return 7;
}

/**
 * @return array{bytes: string, seconds: float}|null
 */
function fractal_zip_paq_lpaq_compress_bytes(string $payload, ?int $level = null, ?string $primerPath = null): ?array
{
	$exe = fractal_zip_paq_discover_executable('lpaq9l');
	if ($exe === null || $payload === '') {
		return null;
	}
	$level = $level === null ? fractal_zip_paq_lpaq_level() : max(0, min(9, $level));
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzlpaq_c_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'in.bin';
	$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.lpq';
	file_put_contents($in, $payload);
	$t0 = microtime(true);
	$args = array($exe, (string) $level, $in, $arc);
	if ($primerPath !== null && is_file($primerPath)) {
		$args[] = $primerPath;
	}
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd($args);
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' 2>/dev/null', $xo, $ret);
	$sec = round(microtime(true) - $t0, 6);
	$raw = ($ret === 0 && is_file($arc)) ? (string) file_get_contents($arc) : '';
	@unlink($in);
	@unlink($arc);
	@rmdir($tmp);
	if ($raw === '') {
		return null;
	}
	return array('bytes' => $raw, 'seconds' => $sec);
}

function fractal_zip_paq_lpaq_decompress_bytes(string $arcBytes, ?string $primerPath = null): ?string
{
	$exe = fractal_zip_paq_discover_executable('lpaq9l');
	if ($exe === null || $arcBytes === '') {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzlpaq_d_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.lpq';
	$out = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
	file_put_contents($arc, $arcBytes);
	if ($primerPath !== null && is_file($primerPath)) {
		$args = array($exe, 'd', $arc, $out, $primerPath);
		list($prefix, $argv) = fractal_zip_paq_wrap_cmd($args);
		exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1', $xo, $ret);
		$ok = $ret === 0 && is_file($out) && filesize($out) > 0;
	} else {
		$ok = fractal_zip_paq_decompress_to_file('lpaq9l', $exe, $arc, $out);
	}
	$plain = $ok ? (string) file_get_contents($out) : '';
	@unlink($arc);
	@unlink($out);
	@rmdir($tmp);
	return $plain !== '' ? $plain : null;
}

/** mcm profile+level (t/f/m/h/x + 0-11); default h6 (~230 MB, ~0.5 s / 400 KB). x levels segfault in v0.84. */
function fractal_zip_paq_mcm_opt(): string
{
	$e = getenv('FRACTAL_ZIP_MCM_OPT');
	if ($e !== false && preg_match('/^[tfmh](?:[0-9]|1[01])$/', trim((string) $e))) {
		return trim((string) $e);
	}
	return 'h6';
}

/**
 * mcm stores the compress-time input name and only restores reliably via the
 * no-output-arg decompress form, so both directions run on relative "in.bin"
 * inside a sandbox dir.
 *
 * @return array{bytes: string, seconds: float}|null
 */
function fractal_zip_paq_mcm_compress_bytes(string $payload): ?array
{
	$exe = fractal_zip_paq_discover_executable('mcm');
	if ($exe === null || $payload === '') {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzmcm_c_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	file_put_contents($tmp . DIRECTORY_SEPARATOR . 'in.bin', $payload);
	$t0 = microtime(true);
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array($exe, '-' . fractal_zip_paq_mcm_opt(), 'in.bin', 'arc.mcm'));
	$cwd = getcwd();
	@chdir($tmp);
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' 2>/dev/null', $xo, $ret);
	if ($cwd !== false) {
		@chdir($cwd);
	}
	$sec = round(microtime(true) - $t0, 6);
	$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.mcm';
	$raw = ($ret === 0 && is_file($arc)) ? (string) file_get_contents($arc) : '';
	foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($tmp);
	if ($raw === '') {
		return null;
	}
	return array('bytes' => $raw, 'seconds' => $sec);
}

/**
 * Run mcm and optionally lpaq9l / DRT+lpaq9l on the same payload concurrently
 * (each is single-threaded; serial trials just multiply the inner wall). The
 * DRT transform runs synchronously first (fast: <1 s / 10 MB); its lpaq9l
 * pass joins the parallel batch. Returns per-tool arc bytes or null.
 *
 * {@code $withLpaq=false} skips plain lpaq9l — required for CM-first mcm-only
 * lanes (ustar/textlike/PE): otherwise proc_close waits on a discarded lpaq
 * trial that dominates wall on 20–40 MiB Squash singles (samba/nci/webster).
 *
 * @return array{lpaq9l: ?string, mcm: ?string, drt_lpaq9l: ?string, drt_lpaq9lp: ?string}
 */
function fractal_zip_paq_cm_compress_bytes_parallel(
	string $payload,
	bool $withDrt = true,
	bool $withLpaq = true
): array {
	$out = array('lpaq9l' => null, 'mcm' => null, 'drt_lpaq9l' => null, 'drt_lpaq9lp' => null);
	if ($payload === '') {
		return $out;
	}
	$lpaqExe = fractal_zip_paq_discover_executable('lpaq9l');
	$mcmExe = fractal_zip_paq_discover_executable('mcm');
	$procs = array();
	$dirs = array();
	// DRT lanes need lpaq; keep plain lpaq when DRT is on unless caller opts out.
	if ($withDrt) {
		$withLpaq = true;
	}
	if ($withLpaq && $lpaqExe !== null) {
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzlpaq_p_' . bin2hex(random_bytes(4));
		@mkdir($tmp, 0700, true);
		file_put_contents($tmp . DIRECTORY_SEPARATOR . 'in.bin', $payload);
		list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array(
			$lpaqExe, (string) fractal_zip_paq_lpaq_level(), 'in.bin', 'arc.out',
		));
		$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1';
		$p = proc_open($cmd, array(), $pipes, $tmp);
		if (is_resource($p)) {
			$procs['lpaq9l'] = $p;
			$dirs['lpaq9l'] = $tmp;
		}
	}
	if ($withDrt && $lpaqExe !== null) {
		$drt = fractal_zip_paq_drt_transform_bytes($payload);
		if ($drt !== null) {
			// Primed lane when the shipped primer exists, plain otherwise. The
			// primed model is a strict superset on prose (~1 KB / 427 KB better);
			// running both would only burn a core for the loser.
			$primer = fractal_zip_paq_lpaq_drt_primer_path();
			$lane = $primer !== null ? 'drt_lpaq9lp' : 'drt_lpaq9l';
			$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzdrtl_p_' . bin2hex(random_bytes(4));
			@mkdir($tmp, 0700, true);
			file_put_contents($tmp . DIRECTORY_SEPARATOR . 'in.bin', $drt);
			$args = array($lpaqExe, (string) fractal_zip_paq_lpaq_level(), 'in.bin', 'arc.out');
			if ($primer !== null) {
				$args[] = $primer;
			}
			list($prefix, $argv) = fractal_zip_paq_wrap_cmd($args);
			$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1';
			$p = proc_open($cmd, array(), $pipes, $tmp);
			if (is_resource($p)) {
				$procs[$lane] = $p;
				$dirs[$lane] = $tmp;
			}
		}
	}
	if ($mcmExe !== null) {
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzmcm_p_' . bin2hex(random_bytes(4));
		@mkdir($tmp, 0700, true);
		file_put_contents($tmp . DIRECTORY_SEPARATOR . 'in.bin', $payload);
		list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array(
			$mcmExe, '-' . fractal_zip_paq_mcm_opt(), 'in.bin', 'arc.out',
		));
		$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1';
		$p = proc_open($cmd, array(), $pipes, $tmp);
		if (is_resource($p)) {
			$procs['mcm'] = $p;
			$dirs['mcm'] = $tmp;
		}
	}
	foreach ($procs as $tool => $p) {
		$rc = proc_close($p);
		$arc = $dirs[$tool] . DIRECTORY_SEPARATOR . 'arc.out';
		if ($rc === 0 && is_file($arc)) {
			$raw = (string) file_get_contents($arc);
			if ($raw !== '') {
				$out[$tool] = $raw;
			}
		}
	}
	foreach ($dirs as $tmp) {
		foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
			@unlink($f);
		}
		@rmdir($tmp);
	}
	return $out;
}

/**
 * Materialize the DRT dictionary (shipped lpaq9m-compressed as lpqdict0.enc;
 * decoded once, cached beside it). DRT requires lpqdict0.dic in its CWD.
 */
function fractal_zip_paq_drt_dict_path(): ?string
{
	$dir = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'lpaq';
	$dic = $dir . DIRECTORY_SEPARATOR . 'lpqdict0.dic';
	if (is_file($dic) && filesize($dic) > 0) {
		return $dic;
	}
	$enc = $dir . DIRECTORY_SEPARATOR . 'lpqdict0.enc';
	$lpaqm = fractal_zip_paq_discover_executable('lpaq9m');
	if (!is_file($enc) || $lpaqm === null) {
		return null;
	}
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array($lpaqm, 'd', $enc, $dic));
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1', $xo, $ret);
	return ($ret === 0 && is_file($dic) && filesize($dic) > 0) ? $dic : null;
}

/**
 * DRT (dictionary replacement transform, from the lpaq9m package): reversible
 * word→code substitution that beats our frozen-vocab tokenizer as a CM
 * front-end (lcet10: 83.2 KB vs 87.6 KB via lpaq9l). $decode=true reverses.
 * DRT only reads lpqdict0.dic from its CWD, so both directions run sandboxed.
 */
function fractal_zip_paq_drt_transform_bytes(string $payload, bool $decode = false): ?string
{
	if ($payload === '') {
		return null;
	}
	$exe = fractal_zip_paq_discover_executable('drt');
	$dict = fractal_zip_paq_drt_dict_path();
	if ($exe === null || $dict === null) {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzdrt_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	if (!@symlink($dict, $tmp . DIRECTORY_SEPARATOR . 'lpqdict0.dic')) {
		@copy($dict, $tmp . DIRECTORY_SEPARATOR . 'lpqdict0.dic');
	}
	file_put_contents($tmp . DIRECTORY_SEPARATOR . 'in.bin', $payload);
	$args = array($exe, 'in.bin', 'out.bin');
	if ($decode) {
		$args[] = 'd';
	}
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd($args);
	$cwd = getcwd();
	@chdir($tmp);
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1', $xo, $ret);
	if ($cwd !== false) {
		@chdir($cwd);
	}
	$out = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
	$raw = ($ret === 0 && is_file($out)) ? (string) file_get_contents($out) : '';
	foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($tmp);
	return $raw !== '' ? $raw : null;
}

/**
 * Shipped English primer (public-domain prose) pre-DRT-transformed and cached.
 * Priming replays this through the lpaq9l model on both sides before the
 * payload (see prime_model in lpaq9l.cpp): ~1 KB / 427 KB saved on prose for
 * ~0.4 s extra wall each way.
 */
function fractal_zip_paq_lpaq_drt_primer_path(): ?string
{
	$dir = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'lpaq';
	$drt = $dir . DIRECTORY_SEPARATOR . 'primer_english.drt';
	if (is_file($drt) && filesize($drt) > 0) {
		return $drt;
	}
	$txt = $dir . DIRECTORY_SEPARATOR . 'primer_english.txt';
	if (!is_file($txt)) {
		return null;
	}
	$t = fractal_zip_paq_drt_transform_bytes((string) file_get_contents($txt));
	if ($t === null) {
		return null;
	}
	$tmp = $drt . '.' . bin2hex(random_bytes(4)) . '.tmp';
	if (file_put_contents($tmp, $t) === false) {
		return null;
	}
	@rename($tmp, $drt);
	return is_file($drt) ? $drt : null;
}

/**
 * drt_lpaq9l / drt_lpaq9lp codec: DRT front-end + (optionally primed) lpaq9l back-end.
 *
 * @return array{bytes: string, seconds: float}|null
 */
function fractal_zip_paq_drt_lpaq_compress_bytes(string $payload, bool $primed = false): ?array
{
	$t0 = microtime(true);
	$drt = fractal_zip_paq_drt_transform_bytes($payload);
	if ($drt === null) {
		return null;
	}
	$primer = $primed ? fractal_zip_paq_lpaq_drt_primer_path() : null;
	if ($primed && $primer === null) {
		return null;
	}
	$r = fractal_zip_paq_lpaq_compress_bytes($drt, null, $primer);
	if (!is_array($r) || !is_string($r['bytes'] ?? null) || $r['bytes'] === '') {
		return null;
	}
	return array('bytes' => (string) $r['bytes'], 'seconds' => round(microtime(true) - $t0, 6));
}

/**
 * Async start of a DRT+lpaq9l compress (for overlapping the raw-plain trial
 * with the tokenized shootout). Returns an opaque handle for _finish, or null.
 *
 * @return array{proc: resource, dir: string, primed: bool}|null
 */
function fractal_zip_paq_drt_lpaq_compress_start(string $payload, bool $primed)
{
	if ($payload === '') {
		return null;
	}
	$lpaqExe = fractal_zip_paq_discover_executable('lpaq9l');
	if ($lpaqExe === null) {
		return null;
	}
	$primer = $primed ? fractal_zip_paq_lpaq_drt_primer_path() : null;
	if ($primed && $primer === null) {
		return null;
	}
	$drt = fractal_zip_paq_drt_transform_bytes($payload);
	if ($drt === null) {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzdrtl_a_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	file_put_contents($tmp . DIRECTORY_SEPARATOR . 'in.bin', $drt);
	$args = array($lpaqExe, (string) fractal_zip_paq_lpaq_level(), 'in.bin', 'arc.out');
	if ($primer !== null) {
		$args[] = $primer;
	}
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd($args);
	$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1';
	$p = proc_open($cmd, array(), $pipes, $tmp);
	if (!is_resource($p)) {
		foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
			@unlink($f);
		}
		@rmdir($tmp);
		return null;
	}
	return array('proc' => $p, 'dir' => $tmp, 'primed' => $primed);
}

/**
 * @param array{proc: resource, dir: string, primed: bool} $handle
 */
function fractal_zip_paq_drt_lpaq_compress_finish(array $handle): ?string
{
	$rc = proc_close($handle['proc']);
	$arc = $handle['dir'] . DIRECTORY_SEPARATOR . 'arc.out';
	$raw = ($rc === 0 && is_file($arc)) ? (string) file_get_contents($arc) : '';
	foreach (glob($handle['dir'] . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($handle['dir']);
	return $raw !== '' ? $raw : null;
}

/**
 * Deferred CM roundtrip verify: the winner's decode (the only serial tail of
 * the fast text inner — ~5 s for a 10 MB payload) runs in a background PHP
 * worker so it overlaps the outer wrap instead of extending it. The archive
 * is only accepted after fractal_zip_paq_deferred_rt_join_or_fail(); a
 * shutdown backstop joins (and exits non-zero on failure) for callers that
 * never reach an explicit join. Opt out: FRACTAL_ZIP_TOKENIZED_DEFERRED_RT=0.
 * Payloads below FRACTAL_ZIP_TOKENIZED_DEFERRED_RT_MIN_BYTES (default 4 MiB)
 * keep the synchronous verify: their decode is ~1 s, and the sync path
 * preserves graceful rt_failed fallback instead of a loud late failure.
 *
 * @return list<array{proc: resource, dir: string, tool: string}>
 */
function &fractal_zip_paq_deferred_rt_registry(): array
{
	if (!isset($GLOBALS['fractal_zip_paq_deferred_rt_handles']) || !is_array($GLOBALS['fractal_zip_paq_deferred_rt_handles'])) {
		$GLOBALS['fractal_zip_paq_deferred_rt_handles'] = array();
	}
	return $GLOBALS['fractal_zip_paq_deferred_rt_handles'];
}

/** Start a background arc-decode verify; false ⇒ caller must verify synchronously. */
function fractal_zip_paq_deferred_rt_begin(string $toolId, string $arcBytes, string $plainSha256, int $plainLen): bool
{
	$e = getenv('FRACTAL_ZIP_TOKENIZED_DEFERRED_RT');
	if ($e !== false && in_array(strtolower(trim((string) $e)), array('0', 'off', 'false', 'no'), true)) {
		return false;
	}
	$minE = getenv('FRACTAL_ZIP_TOKENIZED_DEFERRED_RT_MIN_BYTES');
	$min = ($minE !== false && trim((string) $minE) !== '') ? max(0, (int) trim((string) $minE)) : 4194304;
	if ($plainLen < $min || $arcBytes === '') {
		return false;
	}
	$worker = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq_rt_worker.php';
	if (!is_file($worker)) {
		return false;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzrtd_' . bin2hex(random_bytes(4));
	if (!@mkdir($tmp, 0700, true)) {
		return false;
	}
	$arcPath = $tmp . DIRECTORY_SEPARATOR . 'arc.bin';
	if (file_put_contents($arcPath, $arcBytes) === false) {
		@rmdir($tmp);
		return false;
	}
	$cmd = implode(' ', array_map('escapeshellarg', array(
		PHP_BINARY, $worker, $toolId, $arcPath, $plainSha256, $tmp . DIRECTORY_SEPARATOR . 'rt.ok',
	))) . ' >/dev/null 2>&1';
	$p = proc_open($cmd, array(), $pipes);
	if (!is_resource($p)) {
		@unlink($arcPath);
		@rmdir($tmp);
		return false;
	}
	$reg = &fractal_zip_paq_deferred_rt_registry();
	if ($reg === array()) {
		register_shutdown_function(static function (): void {
			try {
				fractal_zip_paq_deferred_rt_join_or_fail();
			} catch (Throwable $e) {
				if (defined('STDERR') && is_resource(STDERR)) {
					fwrite(STDERR, $e->getMessage() . "\n");
				}
				exit(1);
			}
		});
	}
	// Owner pid: pcntl_fork children (outer pipeline lanes) inherit this
	// registry and the shutdown hook, but cannot wait on the worker (not their
	// child) and must never reap or clean its temp dir — join skips foreign
	// handles.
	$reg[] = array('proc' => $p, 'dir' => $tmp, 'tool' => $toolId, 'pid' => (int) getmypid());
	return true;
}

function fractal_zip_paq_deferred_rt_pending(): bool
{
	$pid = (int) getmypid();
	foreach (fractal_zip_paq_deferred_rt_registry() as $h) {
		if ((int) ($h['pid'] ?? 0) === $pid) {
			return true;
		}
	}
	return false;
}

/**
 * Join pending deferred verifies started by this process (forked children
 * skip handles they do not own). On any failure, deletes $unlinkOnFail
 * (the not-yet-trusted archive) and throws — no silent acceptance.
 */
function fractal_zip_paq_deferred_rt_join_or_fail(?string $unlinkOnFail = null): void
{
	$reg = &fractal_zip_paq_deferred_rt_registry();
	if ($reg === array()) {
		return;
	}
	$pid = (int) getmypid();
	$mine = array();
	$foreign = array();
	foreach ($reg as $h) {
		if ((int) ($h['pid'] ?? 0) === $pid) {
			$mine[] = $h;
		} else {
			$foreign[] = $h;
		}
	}
	$reg = $foreign;
	if ($mine === array()) {
		return;
	}
	$failedTools = array();
	foreach ($mine as $h) {
		proc_close($h['proc']);
		if (!is_file($h['dir'] . DIRECTORY_SEPARATOR . 'rt.ok')) {
			$failedTools[] = (string) $h['tool'];
		}
		foreach (glob($h['dir'] . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
			@unlink($f);
		}
		@rmdir($h['dir']);
	}
	if ($failedTools !== array()) {
		if ($unlinkOnFail !== null && is_file($unlinkOnFail)) {
			@unlink($unlinkOnFail);
		}
		throw new RuntimeException(
			'deferred roundtrip verify failed for codec ' . implode(',', array_unique($failedTools))
			. ' — archive rejected (set FRACTAL_ZIP_TOKENIZED_DEFERRED_RT=0 for synchronous verify)'
		);
	}
}

function fractal_zip_paq_drt_lpaq_decompress_bytes(string $arcBytes, bool $primed = false): ?string
{
	$primer = $primed ? fractal_zip_paq_lpaq_drt_primer_path() : null;
	if ($primed && $primer === null) {
		return null;
	}
	$drt = fractal_zip_paq_lpaq_decompress_bytes($arcBytes, $primer);
	if ($drt === null) {
		return null;
	}
	return fractal_zip_paq_drt_transform_bytes($drt, true);
}

/**
 * Segmented DRT+lpaq9l (tool ids drt_lpaq9l_seg / drt_lpaq9lp_seg): the DRT
 * stream is cut into N contiguous byte slices, each compressed by its own
 * lpaq9l process in parallel. Wall-budget dial for large prose: encode,
 * decode, and the deferred roundtrip verify all divide by N at a measured
 * bytes cost of ~1.9% per doubling on dickens (each segment restarts the CM
 * model; the shared primer softens the cold start). Segment wire:
 * "FZS1" u32le(segCount) u32le(arcLen)*segCount concat(arcs).
 */
const FRACTAL_ZIP_PAQ_SEG_WIRE_MAGIC = 'FZS1';

/** @return list<string>|null per-segment arc bytes */
function fractal_zip_paq_seg_wire_parse(string $wire): ?array
{
	if (!str_starts_with($wire, FRACTAL_ZIP_PAQ_SEG_WIRE_MAGIC)) {
		return null;
	}
	$off = strlen(FRACTAL_ZIP_PAQ_SEG_WIRE_MAGIC);
	if (strlen($wire) < $off + 4) {
		return null;
	}
	$n = (int) unpack('V', substr($wire, $off, 4))[1];
	$off += 4;
	if ($n < 1 || $n > 256 || strlen($wire) < $off + 4 * $n) {
		return null;
	}
	$lens = array();
	for ($i = 0; $i < $n; $i++) {
		$lens[] = (int) unpack('V', substr($wire, $off, 4))[1];
		$off += 4;
	}
	$arcs = array();
	foreach ($lens as $len) {
		if ($len <= 0 || $off + $len > strlen($wire)) {
			return null;
		}
		$arcs[] = substr($wire, $off, $len);
		$off += $len;
	}
	return $off === strlen($wire) ? $arcs : null;
}

/** @param list<string> $arcs */
function fractal_zip_paq_seg_wire_build(array $arcs): string
{
	$wire = FRACTAL_ZIP_PAQ_SEG_WIRE_MAGIC . pack('V', count($arcs));
	foreach ($arcs as $a) {
		$wire .= pack('V', strlen($a));
	}
	return $wire . implode('', $arcs);
}

/**
 * Async start of a segmented DRT+lpaq9l compress: N parallel lpaq9l processes
 * over contiguous slices of the DRT stream. Join with
 * fractal_zip_paq_drt_lpaq_seg_compress_finish().
 *
 * @return array{procs: list<resource>, dirs: list<string>, primed: bool}|null
 */
function fractal_zip_paq_drt_lpaq_seg_compress_start(string $payload, bool $primed, int $segments)
{
	if ($payload === '' || $segments < 2) {
		return null;
	}
	$lpaqExe = fractal_zip_paq_discover_executable('lpaq9l');
	if ($lpaqExe === null) {
		return null;
	}
	$primer = $primed ? fractal_zip_paq_lpaq_drt_primer_path() : null;
	if ($primed && $primer === null) {
		return null;
	}
	$drt = fractal_zip_paq_drt_transform_bytes($payload);
	if ($drt === null) {
		return null;
	}
	$segLen = (int) ceil(strlen($drt) / $segments);
	$procs = array();
	$dirs = array();
	for ($i = 0; $i < $segments; $i++) {
		$slice = substr($drt, $i * $segLen, $segLen);
		if ($slice === '') {
			break;
		}
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzdrts_' . bin2hex(random_bytes(4));
		@mkdir($tmp, 0700, true);
		file_put_contents($tmp . DIRECTORY_SEPARATOR . 'in.bin', $slice);
		$args = array($lpaqExe, (string) fractal_zip_paq_lpaq_level(), 'in.bin', 'arc.out');
		if ($primer !== null) {
			$args[] = $primer;
		}
		list($prefix, $argv) = fractal_zip_paq_wrap_cmd($args);
		$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1';
		$p = proc_open($cmd, array(), $pipes, $tmp);
		if (!is_resource($p)) {
			foreach ($dirs as $d) {
				foreach (glob($d . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
					@unlink($f);
				}
				@rmdir($d);
			}
			return null;
		}
		$procs[] = $p;
		$dirs[] = $tmp;
	}
	return $procs !== array() ? array('procs' => $procs, 'dirs' => $dirs, 'primed' => $primed) : null;
}

/**
 * @param array{procs: list<resource>, dirs: list<string>, primed: bool} $handle
 */
function fractal_zip_paq_drt_lpaq_seg_compress_finish(array $handle): ?string
{
	$arcs = array();
	$ok = true;
	foreach ($handle['procs'] as $i => $p) {
		$rc = proc_close($p);
		$arc = $handle['dirs'][$i] . DIRECTORY_SEPARATOR . 'arc.out';
		$raw = ($rc === 0 && is_file($arc)) ? (string) file_get_contents($arc) : '';
		if ($raw === '') {
			$ok = false;
		}
		$arcs[] = $raw;
	}
	foreach ($handle['dirs'] as $d) {
		foreach (glob($d . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
			@unlink($f);
		}
		@rmdir($d);
	}
	return $ok ? fractal_zip_paq_seg_wire_build($arcs) : null;
}

function fractal_zip_paq_drt_lpaq_seg_decompress_bytes(string $arcBytes, bool $primed): ?string
{
	$arcs = fractal_zip_paq_seg_wire_parse($arcBytes);
	if ($arcs === null) {
		return null;
	}
	$lpaqExe = fractal_zip_paq_discover_executable('lpaq9l');
	if ($lpaqExe === null) {
		return null;
	}
	$primer = $primed ? fractal_zip_paq_lpaq_drt_primer_path() : null;
	if ($primed && $primer === null) {
		return null;
	}
	$procs = array();
	$dirs = array();
	foreach ($arcs as $a) {
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzdrtsd_' . bin2hex(random_bytes(4));
		@mkdir($tmp, 0700, true);
		file_put_contents($tmp . DIRECTORY_SEPARATOR . 'arc.lpq', $a);
		$args = array($lpaqExe, 'd', 'arc.lpq', 'out.bin');
		if ($primer !== null) {
			$args[] = $primer;
		}
		list($prefix, $argv) = fractal_zip_paq_wrap_cmd($args);
		$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1';
		$p = proc_open($cmd, array(), $pipes, $tmp);
		if (!is_resource($p)) {
			foreach ($dirs as $d) {
				foreach (glob($d . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
					@unlink($f);
				}
				@rmdir($d);
			}
			return null;
		}
		$procs[] = $p;
		$dirs[] = $tmp;
	}
	$drt = '';
	$ok = true;
	foreach ($procs as $i => $p) {
		$rc = proc_close($p);
		$out = $dirs[$i] . DIRECTORY_SEPARATOR . 'out.bin';
		$raw = ($rc === 0 && is_file($out)) ? (string) file_get_contents($out) : '';
		if ($raw === '') {
			$ok = false;
		}
		$drt .= $raw;
	}
	foreach ($dirs as $d) {
		foreach (glob($d . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
			@unlink($f);
		}
		@rmdir($d);
	}
	if (!$ok || $drt === '') {
		return null;
	}
	return fractal_zip_paq_drt_transform_bytes($drt, true);
}

function fractal_zip_paq_mcm_decompress_bytes(string $arcBytes): ?string
{
	$exe = fractal_zip_paq_discover_executable('mcm');
	if ($exe === null || $arcBytes === '') {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzmcm_d_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.mcm';
	$out = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
	file_put_contents($arc, $arcBytes);
	$ok = fractal_zip_paq_decompress_to_file('mcm', $exe, $arc, $out);
	$plain = $ok ? (string) file_get_contents($out) : '';
	foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($tmp);
	return $plain !== '' ? $plain : null;
}

/**
 * Dropbox lepton: bit-exact JPEG recompression (tool id `lepton`).
 *
 * @return array{bytes: string, seconds: float}|null
 */
function fractal_zip_paq_lepton_compress_bytes(string $payload): ?array
{
	$exe = fractal_zip_paq_discover_executable('lepton');
	if ($exe === null || $payload === '' || strlen($payload) < 3
		|| $payload[0] !== "\xFF" || $payload[1] !== "\xD8") {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzlep_c_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'in.jpg';
	$arc = $tmp . DIRECTORY_SEPARATOR . 'out.lep';
	file_put_contents($in, $payload);
	$t0 = microtime(true);
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array($exe, '-unjailed', $in, $arc));
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' 2>/dev/null', $xo, $ret);
	$sec = round(microtime(true) - $t0, 6);
	$raw = ($ret === 0 && is_file($arc)) ? (string) file_get_contents($arc) : '';
	foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($tmp);
	if ($raw === '' || strlen($raw) >= strlen($payload)) {
		return null;
	}
	return array('bytes' => $raw, 'seconds' => $sec);
}

function fractal_zip_paq_lepton_decompress_bytes(string $arcBytes): ?string
{
	$exe = fractal_zip_paq_discover_executable('lepton');
	if ($exe === null || $arcBytes === '') {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzlep_d_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.lep';
	$out = $tmp . DIRECTORY_SEPARATOR . 'out.jpg';
	file_put_contents($arc, $arcBytes);
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array($exe, '-unjailed', $arc, $out));
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1', $xo, $ret);
	$plain = ($ret === 0 && is_file($out)) ? (string) file_get_contents($out) : '';
	foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($tmp);
	return $plain !== '' ? $plain : null;
}

/**
 * Google brunsli: bit-exact JPEG ↔ .brn (cbrunsli / dbrunsli pair).
 *
 * @return array{bytes: string, seconds: float}|null
 */
function fractal_zip_paq_brunsli_compress_bytes(string $payload): ?array
{
	$exe = fractal_zip_paq_discover_executable('cbrunsli');
	if ($exe === null || $payload === '' || strlen($payload) < 3
		|| $payload[0] !== "\xFF" || $payload[1] !== "\xD8") {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzbrn_c_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$in = $tmp . DIRECTORY_SEPARATOR . 'in.jpg';
	$out = $tmp . DIRECTORY_SEPARATOR . 'out.brn';
	file_put_contents($in, $payload);
	$t0 = microtime(true);
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array($exe, $in, $out));
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1', $xo, $ret);
	$sec = round(microtime(true) - $t0, 6);
	$raw = ($ret === 0 && is_file($out)) ? (string) file_get_contents($out) : '';
	foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($tmp);
	if ($raw === '' || strlen($raw) >= strlen($payload)) {
		return null;
	}
	return array('bytes' => $raw, 'seconds' => $sec);
}

function fractal_zip_paq_brunsli_decompress_bytes(string $arcBytes): ?string
{
	$exe = fractal_zip_paq_discover_executable('dbrunsli');
	if ($exe === null || $arcBytes === '') {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzbrn_d_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$arc = $tmp . DIRECTORY_SEPARATOR . 'in.brn';
	$out = $tmp . DIRECTORY_SEPARATOR . 'out.jpg';
	file_put_contents($arc, $arcBytes);
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd(array($exe, $arc, $out));
	exec($prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' >/dev/null 2>&1', $xo, $ret);
	$plain = ($ret === 0 && is_file($out)) ? (string) file_get_contents($out) : '';
	foreach (glob($tmp . DIRECTORY_SEPARATOR . '*') ?: array() as $f) {
		@unlink($f);
	}
	@rmdir($tmp);
	return $plain !== '' ? $plain : null;
}

function fractal_zip_paq_zpaq_decompress_bytes(string $arcBytes, string $toolId): ?string
{
	if ($arcBytes === '' || !preg_match('/^zpaq[1-9]$/', $toolId)) {
		return null;
	}
	$exe = fractal_zip_paq_zpaq_executable();
	if ($exe === null) {
		return null;
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpaq_d_' . bin2hex(random_bytes(4));
	@mkdir($tmp, 0700, true);
	$arc = $tmp . DIRECTORY_SEPARATOR . 'arc.zpaq';
	$member = $tmp . DIRECTORY_SEPARATOR . 'in.bin';
	file_put_contents($arc, $arcBytes);
	$cwd = getcwd();
	@chdir($tmp);
	$cmd = escapeshellarg($exe) . ' x ' . escapeshellarg(basename($arc)) . ' -force 2>/dev/null';
	exec($cmd, $xo, $ret);
	if ($cwd !== false) {
		@chdir($cwd);
	}
	$plain = ($ret === 0 && is_file($member)) ? (string) file_get_contents($member) : '';
	@unlink($arc);
	@unlink($member);
	@rmdir($tmp);
	return $plain !== '' ? $plain : null;
}

function fractal_zip_paq_phda9_dict_path(): ?string
{
	if (function_exists('fractal_zip_phda9_dict_inline_active')
		&& function_exists('fractal_zip_phda9_dict_inline_mode')
		&& fractal_zip_phda9_dict_inline_active()
		&& fractal_zip_phda9_dict_inline_mode() === 'tokenize') {
		return null;
	}
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
	$path = fractal_zip_phda9_dict_path_from_env();
	if ($path === null) {
		return null;
	}
	$valid = fractal_zip_phda9_dict_validate_file($path);
	return $valid['ok'] ? $path : null;
}

function fractal_zip_paq_phda9_decompress_executable(string $toolId): ?string
{
	$dec = __DIR__ . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'phda9' . DIRECTORY_SEPARATOR . 'phda9dec';
	if (is_executable($dec)) {
		return $dec;
	}
	return fractal_zip_paq_discover_executable($toolId);
}

/**
 * @return array<string, string> tool_id => executable path
 */
function fractal_zip_paq_discover_tools(): array
{
	$found = array();
	foreach (fractal_zip_paq_tool_ids_from_env() as $id) {
		$exe = fractal_zip_paq_discover_executable($id);
		if ($exe !== null) {
			$found[$id] = $exe;
		}
	}
	return $found;
}

function fractal_zip_paq_native_compare_enabled(): bool
{
	return getenv('FRACTAL_ZIP_PAQ_NATIVE_COMPARE') === '1';
}

function fractal_zip_paq_native_max_raw_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_PAQ_NATIVE_MAX_RAW_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 134217728;
}

function fractal_zip_paq_timeout_sec(): int
{
	$e = getenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC');
	if ($e === false || trim((string) $e) === '') {
		return 0;
	}
	return max(0, (int) $e);
}

/** Decompress wall; defaults to unlimited so encode tip timeouts cannot abort RT. */
function fractal_zip_paq_extract_timeout_sec(): int
{
	$e = getenv('FRACTAL_ZIP_PAQ_EXTRACT_TIMEOUT_SEC');
	if ($e === false || trim((string) $e) === '') {
		return 0;
	}
	return max(0, (int) $e);
}

/**
 * @return array{0: string, 1: list<string>} prefix command + argv tail
 */
function fractal_zip_paq_wrap_cmd(array $argv, ?int $timeoutSec = null): array
{
	$sec = $timeoutSec !== null ? max(0, $timeoutSec) : fractal_zip_paq_timeout_sec();
	if ($sec <= 0) {
		return array('', $argv);
	}
	return array('timeout ' . (int) $sec . ' ', $argv);
}

/**
 * Compress single file with tool; returns archive bytes or null.
 *
 * @return array{bytes: ?string, tool: ?string, seconds: float}
 */
function fractal_zip_paq_compress_file(string $toolId, string $exe, string $inputPath): array
{
	$t0 = microtime(true);
	$inputPath = realpath($inputPath) ?: $inputPath;
	if (!is_file($inputPath)) {
		return array('bytes' => null, 'tool' => null, 'seconds' => 0.0);
	}
	if (!function_exists('fractal_zip_phda9_daemon_enabled_for_tool')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_daemon.php';
	}
	if (fractal_zip_phda9_daemon_enabled_for_tool($toolId)) {
		if (!function_exists('fractal_zip_phda9_daemon_compress')) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_daemon.php';
		}
		$plain = (string) file_get_contents($inputPath);
		$dict = fractal_zip_paq_phda9_dict_path();
		$r = fractal_zip_phda9_daemon_compress($toolId, $plain, $dict, fractal_zip_paq_timeout_sec());
		if (is_array($r) && is_string($r['bytes'])) {
			return array(
				'bytes' => $r['bytes'],
				'tool' => $toolId,
				'seconds' => (float) ($r['seconds'] ?? round(microtime(true) - $t0, 3)),
			);
		}
		// Daemon required — never spawn a second direct phda9 when routing failed.
		return array(
			'bytes' => null,
			'tool' => null,
			'seconds' => round(microtime(true) - $t0, 6),
		);
	}
	$dir = dirname($inputPath);
	$base = basename($inputPath);
	$outPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpaq_' . bin2hex(random_bytes(6)) . '.' . $toolId;
	if (is_file($outPath)) {
		@unlink($outPath);
	}
	$ret = -1;
	$prefix = '';
	$argv = array();
	$phda9Dict = null;
	if ($toolId === 'phda9' || $toolId === 'phda9_no_lstm') {
		$phda9Dict = fractal_zip_paq_phda9_dict_path();
	}
	switch ($toolId) {
		case 'phda9':
		case 'phda9_no_lstm':
			// phda9 v1.8: C|D <input> <output> [dict]  (C9/D only for enwik9-sized prize runs)
			$argv = array($exe, 'C', $base, basename($outPath));
			if ($phda9Dict !== null) {
				$argv[] = $phda9Dict;
			}
			break;
		case 'paq8px':
		case 'paq8pxd':
			$lvl = (int) (getenv('FRACTAL_ZIP_PAQ8PX_LEVEL') ?: 8);
			$lvl = max(1, min(12, $lvl));
			$argv = array($exe, '-' . $lvl, $base, basename($outPath));
			break;
		case 'lepton':
			$argv = array($exe, '-unjailed', $inputPath, $outPath);
			list($prefix, $argv) = fractal_zip_paq_wrap_cmd($argv);
			$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' 2>/dev/null';
			exec($cmd, $xo, $ret);
			$bytes = null;
			if ($ret === 0 && is_file($outPath)) {
				$raw = @file_get_contents($outPath);
				if ($raw !== false && $raw !== '') {
					$bytes = $raw;
				}
				@unlink($outPath);
			}
			return array(
				'bytes' => $bytes,
				'tool' => ($bytes !== null) ? $toolId : null,
				'seconds' => round(microtime(true) - $t0, 6),
			);
		case 'cmix':
			$argv = array($exe, '-c', $base, basename($outPath));
			break;
		case 'parallel_cmix':
		case 'parallel_phda9':
			$jobs = fractal_zip_paq_parallel_jobs();
			$argv = array($exe, 'compress', '-j' . (string) $jobs, '-o', $outPath, $inputPath);
			if ($toolId === 'parallel_phda9') {
				$phda9Dict = fractal_zip_paq_phda9_dict_path();
				if ($phda9Dict !== null) {
					$argv[] = '--dict';
					$argv[] = $phda9Dict;
				}
			}
			list($prefix, $argv) = fractal_zip_paq_wrap_cmd($argv);
			$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . ' 2>/dev/null';
			exec($cmd, $xo, $ret);
			$bytes = null;
			if ($ret === 0 && is_file($outPath)) {
				$raw = @file_get_contents($outPath);
				if ($raw !== false && $raw !== '') {
					$bytes = $raw;
				}
				@unlink($outPath);
			}
			return array(
				'bytes' => $bytes,
				'tool' => ($bytes !== null) ? $toolId : null,
				'seconds' => round(microtime(true) - $t0, 6),
			);
		default:
			return array('bytes' => null, 'tool' => null, 'seconds' => round(microtime(true) - $t0, 6));
	}
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd($argv);
	$cwd = getcwd();
	@chdir($dir);
	$stderrRedirect = ($toolId === 'phda9' || $toolId === 'phda9_no_lstm') ? '' : ' 2>/dev/null';
	$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . $stderrRedirect;
	$runExec = static function () use ($toolId, $base, $cmd, $dir, $outPath, $cwd, $t0): array {
		if (($toolId === 'phda9' || $toolId === 'phda9_no_lstm') && PHP_SAPI === 'cli' && is_resource(STDERR)) {
			@fwrite(STDERR, '[paq] phda9 compress ' . $base . " (hours-scale; progress on stderr)\n");
		}
		exec($cmd, $xo, $ret);
		if ($cwd !== false) {
			@chdir($cwd);
		}
		$arcPath = $dir . DIRECTORY_SEPARATOR . basename($outPath);
		if ($ret !== 0 || !is_file($arcPath)) {
			$arcPath = $outPath;
		}
		$bytes = null;
		if (is_file($arcPath)) {
			$raw = @file_get_contents($arcPath);
			if ($raw !== false && $raw !== '') {
				$bytes = $raw;
			}
			@unlink($arcPath);
		}
		return array(
			'bytes' => $bytes,
			'tool' => ($bytes !== null) ? $toolId : null,
			'seconds' => round(microtime(true) - $t0, 6),
		);
	};
	if ($toolId === 'phda9' || $toolId === 'phda9_no_lstm') {
		if (!function_exists('fractal_zip_phda9_singleton_with_lock')) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_daemon.php';
		}
		$locked = fractal_zip_phda9_singleton_with_lock($runExec);
		if (!is_array($locked)) {
			return array('bytes' => null, 'tool' => null, 'seconds' => round(microtime(true) - $t0, 6));
		}
		return $locked;
	}
	return $runExec();
}

/**
 * Decompress PAQ archive bytes to output file path.
 */
function fractal_zip_paq_decompress_to_file(string $toolId, string $exe, string $arcPath, string $outPath): bool
{
	if (!is_file($arcPath)) {
		return false;
	}
	if (!function_exists('fractal_zip_phda9_daemon_enabled_for_tool')) {
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_daemon.php';
	}
	if (fractal_zip_phda9_daemon_enabled_for_tool($toolId)) {
		$arc = (string) file_get_contents($arcPath);
		$dict = fractal_zip_paq_phda9_dict_path();
		$plain = fractal_zip_phda9_daemon_decompress($toolId, $arc, $dict);
		if (!is_string($plain)) {
			return false;
		}
		$outDir = dirname($outPath);
		if ($outDir !== '' && $outDir !== '.' && !is_dir($outDir)) {
			@mkdir($outDir, 0700, true);
		}
		return file_put_contents($outPath, $plain) !== false;
	}
	if ($toolId === 'drt_lpaq9l_seg' || $toolId === 'drt_lpaq9lp_seg') {
		$plain = fractal_zip_paq_drt_lpaq_seg_decompress_bytes((string) file_get_contents($arcPath), $toolId === 'drt_lpaq9lp_seg');
		if (!is_string($plain) || $plain === '') {
			return false;
		}
		$outDir = dirname($outPath);
		if ($outDir !== '' && $outDir !== '.' && !is_dir($outDir)) {
			@mkdir($outDir, 0700, true);
		}
		return file_put_contents($outPath, $plain) !== false;
	}
	if ($toolId === 'drt_lpaq9l' || $toolId === 'drt_lpaq9lp') {
		$plain = fractal_zip_paq_drt_lpaq_decompress_bytes((string) file_get_contents($arcPath), $toolId === 'drt_lpaq9lp');
		if (!is_string($plain) || $plain === '') {
			return false;
		}
		$outDir = dirname($outPath);
		if ($outDir !== '' && $outDir !== '.' && !is_dir($outDir)) {
			@mkdir($outDir, 0700, true);
		}
		return file_put_contents($outPath, $plain) !== false;
	}
	if ($toolId === 'lepton') {
		$plain = fractal_zip_paq_lepton_decompress_bytes((string) file_get_contents($arcPath));
		if (!is_string($plain) || $plain === '') {
			return false;
		}
		$outDir = dirname($outPath);
		if ($outDir !== '' && $outDir !== '.' && !is_dir($outDir)) {
			@mkdir($outDir, 0700, true);
		}
		return file_put_contents($outPath, $plain) !== false;
	}
	if ($toolId === 'brunsli') {
		$plain = fractal_zip_paq_brunsli_decompress_bytes((string) file_get_contents($arcPath));
		if (!is_string($plain) || $plain === '') {
			return false;
		}
		$outDir = dirname($outPath);
		if ($outDir !== '' && $outDir !== '.' && !is_dir($outDir)) {
			@mkdir($outDir, 0700, true);
		}
		return file_put_contents($outPath, $plain) !== false;
	}
	$dir = dirname($arcPath);
	$arcBase = basename($arcPath);
	$outBase = basename($outPath);
	$ret = -1;
	$prefix = '';
	$argv = array();
	$useAbsParallel = false;
	$phda9Dict = null;
	if ($toolId === 'phda9' || $toolId === 'phda9_no_lstm') {
		$phda9Dict = fractal_zip_paq_phda9_dict_path();
	}
	switch ($toolId) {
		case 'phda9':
		case 'phda9_no_lstm':
			// C-mode archives decompress via phda9 D; C9/enwik9 prize runs use phda9dec.
			$useDec = getenv('FRACTAL_ZIP_PHDA9_DECOMPRESS_MODE');
			$decMode = ($useDec !== false && trim((string) $useDec) !== '')
				? strtoupper(trim((string) $useDec))
				: 'D';
			if ($decMode === 'DEC') {
				$decExe = fractal_zip_paq_phda9_decompress_executable($toolId) ?? $exe;
				$argv = array($decExe, $arcBase, $outBase);
			} else {
				$argv = array($exe, $decMode, $arcBase, $outBase);
			}
			if ($phda9Dict !== null) {
				$argv[] = $phda9Dict;
			}
			break;
		case 'paq8px':
		case 'paq8pxd':
			$argv = array($exe, '-d', $arcBase, $outBase);
			break;
		case 'cmix':
			$argv = array($exe, '-d', $arcBase, $outBase);
			break;
		case 'parallel_cmix':
		case 'parallel_phda9':
			$jobs = fractal_zip_paq_parallel_jobs();
			$argv = array(
				$exe,
				'decompress',
				'-j' . (string) $jobs,
				'-o',
				$outPath,
				$arcPath,
			);
			if ($toolId === 'parallel_phda9') {
				$phda9Dict = fractal_zip_paq_phda9_dict_path();
				if ($phda9Dict !== null) {
					$argv[] = '--dict';
					$argv[] = $phda9Dict;
				}
			}
			break;
		case 'lpaq9l':
			$argv = array($exe, 'd', $arcBase, $outBase);
			break;
		case 'mcm':
			// v0.84 ignores an explicit output arg; it restores to the stored
			// compress-time name ("in.bin", see fractal_zip_paq_mcm_compress_bytes).
			$argv = array($exe, 'd', $arcBase);
			break;
		default:
			if (preg_match('/^zpaq[1-9]$/', $toolId)) {
				$argv = array($exe, 'x', $arcBase, '-force');
				break;
			}
			return false;
	}
	list($prefix, $argv) = fractal_zip_paq_wrap_cmd($argv, fractal_zip_paq_extract_timeout_sec());
	$cwd = getcwd();
	$useAbsParallel = ($useAbsParallel ?? false) || ($toolId === 'parallel_cmix' || $toolId === 'parallel_phda9');
	if (!$useAbsParallel) {
		@chdir($dir);
	}
	$stderrRedirect = ($toolId === 'phda9' || $toolId === 'phda9_no_lstm')
		? (getenv('FRACTAL_ZIP_FZPA_DECOMPRESS_PROGRESS') === '1' ? '' : ' 2>/dev/null')
		: ' 2>/dev/null';
	$cmd = $prefix . implode(' ', array_map('escapeshellarg', $argv)) . $stderrRedirect;
	$arcBase = basename($arcPath);
	$runExec = static function () use ($toolId, $cmd, $cwd, $useAbsParallel, $dir, $outPath, $arcBase): bool {
		$ret = -1;
		exec($cmd, $xo, $ret);
		if ($cwd !== false && !$useAbsParallel) {
			@chdir($cwd);
		}
		if ((preg_match('/^zpaq[1-9]$/', $toolId) || $toolId === 'mcm') && $ret === 0 && !is_file($outPath)) {
			$member = $dir . DIRECTORY_SEPARATOR . 'in.bin';
			if (is_file($member) && $member !== $outPath) {
				@rename($member, $outPath);
			}
		}
		// paq8px restores under the compress-time input basename, ignoring the
		// optional output arg — scoop that file into $outPath.
		if (($toolId === 'paq8px' || $toolId === 'paq8pxd') && $ret === 0 && !is_file($outPath)) {
			foreach (scandir($dir) ?: array() as $name) {
				if ($name === '.' || $name === '..' || $name === $arcBase) {
					continue;
				}
				$cand = $dir . DIRECTORY_SEPARATOR . $name;
				if (is_file($cand) && filesize($cand) > 0) {
					@rename($cand, $outPath);
					break;
				}
			}
		}
		if ($toolId === 'mcm') {
			@unlink($dir . DIRECTORY_SEPARATOR . 'of.txt');
		}
		return $ret === 0 && is_file($outPath) && filesize($outPath) > 0;
	};
	if ($toolId === 'phda9' || $toolId === 'phda9_no_lstm') {
		if (!function_exists('fractal_zip_phda9_singleton_with_lock')) {
			require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_daemon.php';
		}
		$locked = fractal_zip_phda9_singleton_with_lock($runExec);
		return $locked === true;
	}
	return $runExec();
}

/**
 * Smallest PAQ archive among discovered tools for a single-member workdir.
 *
 * @return array{bytes: ?string, tool: ?string, seconds: float}
 */
function fractal_zip_paq_smallest_single_file_archive(string $workDir, string $relPath): array
{
	$tools = fractal_zip_paq_discover_tools();
	if ($tools === array()) {
		return array('bytes' => null, 'tool' => null, 'seconds' => 0.0);
	}
	$inputPath = $workDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relPath);
	$sweep = getenv('FRACTAL_ZIP_PAQ_SWEEP');
	$tryAll = ($sweep === false || $sweep === '' || $sweep === '1');
	$parallel = $tryAll && count($tools) >= 2 && PHP_SAPI === 'cli' && PHP_OS_FAMILY !== 'Windows'
		&& function_exists('pcntl_fork') && function_exists('pcntl_waitpid');
	if (!$parallel) {
		return fractal_zip_paq_smallest_single_file_archive_serial($tools, $inputPath, $tryAll);
	}
	$tmpBase = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_paq_sweep_' . bin2hex(random_bytes(6));
	@mkdir($tmpBase, 0700, true);
	$children = array();
	foreach ($tools as $id => $exe) {
		$outPath = $tmpBase . DIRECTORY_SEPARATOR . $id . '.json';
		$pid = pcntl_fork();
		if ($pid === -1) {
			$parallel = false;
			break;
		}
		if ($pid === 0) {
			if (function_exists('fractal_zip_process_guard_fork_child_prepare')) {
				fractal_zip_process_guard_fork_child_prepare();
			} elseif (class_exists('fractal_zip_encode_pipeline', false)) {
				fractal_zip_encode_pipeline::fork_child_prepare();
			}
			$r = fractal_zip_paq_compress_file($id, $exe, $inputPath);
			file_put_contents($outPath, json_encode($r, JSON_UNESCAPED_UNICODE));
			exit(0);
		}
		$children[] = array('pid' => $pid, 'out' => $outPath);
	}
	if (!$parallel) {
		foreach ($children as $ch) {
			if (isset($ch['pid']) && $ch['pid'] > 0) {
				pcntl_waitpid((int) $ch['pid'], $status);
			}
		}
		fractal_zip_enwik_recursive_remove($tmpBase);
		return fractal_zip_paq_smallest_single_file_archive_serial($tools, $inputPath, $tryAll);
	}
	$best = null;
	$bestTool = null;
	$bestLen = PHP_INT_MAX;
	$secSum = 0.0;
	foreach ($children as $ch) {
		pcntl_waitpid((int) $ch['pid'], $status);
		if (!is_file($ch['out'])) {
			continue;
		}
		$r = json_decode((string) file_get_contents($ch['out']), true);
		if (!is_array($r)) {
			continue;
		}
		$secSum += (float) ($r['seconds'] ?? 0.0);
		if (!is_string($r['bytes'] ?? null) || $r['bytes'] === '') {
			continue;
		}
		$len = strlen((string) $r['bytes']);
		if ($len < $bestLen) {
			$bestLen = $len;
			$best = (string) $r['bytes'];
			$bestTool = isset($r['tool']) ? (string) $r['tool'] : null;
		}
	}
	fractal_zip_enwik_recursive_remove($tmpBase);
	return array('bytes' => $best, 'tool' => $bestTool, 'seconds' => $secSum);
}

/**
 * @param array<string, string> $tools
 * @return array{bytes: ?string, tool: ?string, seconds: float}
 */
function fractal_zip_paq_smallest_single_file_archive_serial(array $tools, string $inputPath, bool $tryAll): array
{
	$best = null;
	$bestTool = null;
	$bestLen = PHP_INT_MAX;
	$secSum = 0.0;
	foreach ($tools as $id => $exe) {
		$r = fractal_zip_paq_compress_file($id, $exe, $inputPath);
		$secSum += (float) $r['seconds'];
		if (!is_string($r['bytes']) || $r['bytes'] === '') {
			continue;
		}
		$len = strlen($r['bytes']);
		if ($len < $bestLen) {
			$bestLen = $len;
			$best = $r['bytes'];
			$bestTool = $id;
		}
		if (!$tryAll && $best !== null) {
			break;
		}
	}
	return array('bytes' => $best, 'tool' => $bestTool, 'seconds' => $secSum);
}

/** Magic prefix for PAQ native passthrough wire. */
function fractal_zip_paq_wire_magic(): string
{
	return 'FZpq';
}

/**
 * Build FZpq wire: magic + tool_id byte + raw archive.
 */
function fractal_zip_paq_wrap_wire(string $toolId, string $archiveBytes): string
{
	$id = substr($toolId, 0, 16);
	return fractal_zip_paq_wire_magic() . "\x01" . chr(strlen($id)) . $id . $archiveBytes;
}

/**
 * FZpq v2: magic + \\x02 + pathLen(u8) + path + toolLen(u8) + toolId + arc.
 * Used by the binary-CM lane so restore keeps the original member name.
 */
function fractal_zip_paq_wrap_wire_v2(string $toolId, string $archiveBytes, string $memberRel): string
{
	$id = substr($toolId, 0, 16);
	$rel = str_replace('\\', '/', $memberRel);
	if (strlen($rel) > 255) {
		$rel = basename($rel);
	}
	if ($rel === '' || strlen($rel) > 255) {
		$rel = 'payload.bin';
	}
	return fractal_zip_paq_wire_magic() . "\x02"
		. chr(strlen($rel)) . $rel
		. chr(strlen($id)) . $id
		. $archiveBytes;
}

/**
 * @return array{tool: string, payload: string, path: string}|null
 */
function fractal_zip_paq_unwrap_wire(string $blob): ?array
{
	$magic = fractal_zip_paq_wire_magic();
	if (strlen($blob) < strlen($magic) + 3 || substr($blob, 0, strlen($magic)) !== $magic) {
		return null;
	}
	$ver = $blob[strlen($magic)];
	$off = strlen($magic) + 1;
	$path = '';
	if ($ver === "\x02") {
		if ($off >= strlen($blob)) {
			return null;
		}
		$pathLen = ord($blob[$off]);
		$off++;
		if ($pathLen <= 0 || $off + $pathLen > strlen($blob)) {
			return null;
		}
		$path = substr($blob, $off, $pathLen);
		$off += $pathLen;
	} elseif ($ver !== "\x01") {
		return null;
	}
	if ($off >= strlen($blob)) {
		return null;
	}
	$idLen = ord($blob[$off]);
	$off++;
	if ($idLen <= 0 || $off + $idLen > strlen($blob)) {
		return null;
	}
	$tool = substr($blob, $off, $idLen);
	return array('tool' => $tool, 'payload' => substr($blob, $off + $idLen), 'path' => $path);
}

function fractal_zip_paq_native_kind_from_head(string $head): ?string
{
	$magic = fractal_zip_paq_wire_magic();
	if (strlen($head) >= strlen($magic) + 2 && substr($head, 0, strlen($magic)) === $magic
		&& ($head[strlen($magic)] === "\x01" || $head[strlen($magic)] === "\x02")) {
		return 'fzpq_paq';
	}
	return null;
}
