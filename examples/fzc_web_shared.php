<?php

/**
 * Shared helpers for fzc_compress.php / fzc_extract.php web mode (drag-drop UI).
 * Not used by CLI.
 *
 * Jobs directory: set FRACTAL_ZIP_WEB_JOBS to an absolute writable path, or ensure
 * web_jobs/ next to these scripts is writable; otherwise a hashed folder under sys_get_temp_dir() is used.
 *
 * If the page URL is a static *.html file but fzc_compress.php (same stem) exists beside it,
 * API/download URLs use the .php handler. Override basename with FRACTAL_ZIP_WEB_HANDLER_BASENAME.
 *
 * Web upload cap: FZC_WEB_MAX_UPLOAD_BYTES (default 8 MiB). Set to 0 to disable (private installs).
 *
 * JSON POST handlers: {@see fzc_web_load_fractal_zip()} defines FRACTAL_ZIP_WEB_JSON_API so
 * {@code fractal_zip::fatal_error()} returns {@code application/json} instead of HTML.
 *
 * **Storage safety:** uploads and extracted members are kept under an opaque job id with
 * `fzc_web_sanitize_rel_path()` (no `..`, no empty segments) and `fzc_web_job_path()` resolves paths with
 * `realpath` so nothing escapes the job tree. Filenames are **not** blocked by extension (e.g. `.php` is
 * allowed). Direct HTTP access to the jobs directory must be denied (`fzc_web_install_jobs_htaccess_if_absent`
 * or equivalent nginx rule); downloads go through `fzc_*.php?job=&dl=` only. Set `FZC_WEB_JOBS_NO_HTACCESS=1`
 * to skip auto-installing deny-all `.htaccess` under the jobs root.
 */

final class fzc_web_UnsafePathException extends RuntimeException {
}

/**
 * True if a single path component is structurally invalid (traversal / embedded slash / NUL), not a filename blacklist.
 */
function fzc_web_path_component_is_unsafe(string $segment): bool {
	$segment = str_replace('\\', '/', $segment);
	if ($segment === '' || strpos($segment, '/') !== false) {
		return true;
	}
	if (strpos($segment, "\0") !== false) {
		return true;
	}
	$low = strtolower($segment);
	return $low === '.' || $low === '..';
}

/**
 * True if any path segment in a forward-slash relative path is structurally invalid.
 */
function fzc_web_rel_path_has_unsafe_segment(string $rel): bool {
	$rel = str_replace('\\', '/', $rel);
	foreach (explode('/', $rel) as $seg) {
		if ($seg === '') {
			continue;
		}
		if (fzc_web_path_component_is_unsafe($seg)) {
			return true;
		}
	}
	return false;
}

/**
 * Delete files under $dir whose basename fails structural path validation. Normally zero; defense in depth.
 */
function fzc_web_scrub_unsafe_files_under(string $dir): int {
	if (!is_dir($dir)) {
		return 0;
	}
	$n = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		if (!$item->isFile()) {
			continue;
		}
		if (fzc_web_path_component_is_unsafe($item->getBasename())) {
			@unlink($item->getPathname());
			$n++;
		}
	}
	return $n;
}

/**
 * Install Apache deny-all .htaccess at jobs root if missing (direct web access to uploads is unsafe).
 */
function fzc_web_install_jobs_htaccess_if_absent(string $root): void {
	static $done = array();
	if (isset($done[$root])) {
		return;
	}
	$done[$root] = true;
	if (getenv('FZC_WEB_JOBS_NO_HTACCESS') === '1') {
		return;
	}
	if ($root === '' || !is_dir($root) || !is_writable($root)) {
		return;
	}
	$p = $root . DIRECTORY_SEPARATOR . '.htaccess';
	if (is_file($p)) {
		return;
	}
	$c = <<<'HTA'
# fractal_zip web_jobs: deny direct HTTP access (downloads use fzc_*.php ?job=&dl=)
<IfModule mod_authz_core.c>
	Require all denied
</IfModule>
<IfModule !mod_authz_core.c>
	Order deny,allow
	Deny from all
</IfModule>

HTA;
	@file_put_contents($p, $c);
}

/**
 * Max bytes for web demo uploads (compress: total staged; extract: archive file). 0 = no limit.
 */
function fzc_web_max_upload_bytes(): int {
	$e = getenv('FZC_WEB_MAX_UPLOAD_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		$v = trim((string) $e);
		if (ctype_digit($v)) {
			return (int) $v;
		}
	}
	return 8 * 1024 * 1024;
}

/**
 * Max number of extracted files to include in JSON/list links. 0 = no cap.
 * Keeps web responses bounded for huge trees.
 */
function fzc_web_max_list_files(): int {
	$e = getenv('FZC_WEB_MAX_LIST_FILES');
	if ($e !== false && trim((string) $e) !== '') {
		$v = trim((string) $e);
		if (ctype_digit($v)) {
			return (int) $v;
		}
	}
	return 2000;
}

/**
 * Parse php.ini size strings (e.g. "8M", "512K") to bytes. Returns 0 if unset or invalid.
 */
function fzc_web_ini_value_to_bytes(string $ini): int {
	$ini = trim($ini);
	if ($ini === '' || $ini === '0') {
		return 0;
	}
	$last = strtolower($ini[strlen($ini) - 1]);
	$n = (float) $ini;
	switch ($last) {
		case 'g':
			return (int) round($n * 1024 * 1024 * 1024);
		case 'm':
			return (int) round($n * 1024 * 1024);
		case 'k':
			return (int) round($n * 1024);
		default:
			return max(0, (int) round($n));
	}
}

/**
 * When the raw body exceeds PHP post_max_size, PHP discards $_POST and $_FILES (multipart becomes empty).
 * Call early in JSON POST handlers that rely on multipart uploads.
 */
function fzc_web_reject_truncated_multipart_post(): void {
	$ct = isset($_SERVER['CONTENT_TYPE']) ? (string) $_SERVER['CONTENT_TYPE'] : '';
	if (stripos($ct, 'multipart/form-data') === false) {
		return;
	}
	$cl = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
	if ($cl <= 0 || !empty($_POST) || !empty($_FILES)) {
		return;
	}
	$pms = fzc_web_ini_value_to_bytes((string) ini_get('post_max_size'));
	if ($pms <= 0 || $cl <= $pms) {
		return;
	}
	fzc_web_send_json(array(
		'ok' => false,
		'error' => 'Request body (' . (string) $cl . ' bytes) exceeded PHP post_max_size (' . ini_get('post_max_size') . '). PHP discarded the upload — increase post_max_size and upload_max_filesize, and nginx client_max_body_size if applicable.',
	), 413);
}

/**
 * Human-readable summary of PHP per-part upload error codes for files[] field.
 */
function fzc_web_upload_err_string(int $code): string {
	switch ($code) {
		case UPLOAD_ERR_OK:
			return 'OK';
		case UPLOAD_ERR_INI_SIZE:
			return 'exceeds upload_max_filesize (' . ini_get('upload_max_filesize') . ')';
		case UPLOAD_ERR_FORM_SIZE:
			return 'exceeds HTML MAX_FILE_SIZE';
		case UPLOAD_ERR_PARTIAL:
			return 'partial upload (network or server cut off)';
		case UPLOAD_ERR_NO_FILE:
			return 'no file';
		case UPLOAD_ERR_NO_TMP_DIR:
			return 'missing temp directory (see upload_tmp_dir)';
		case UPLOAD_ERR_CANT_WRITE:
			return 'failed to write to disk';
		case UPLOAD_ERR_EXTENSION:
			return 'blocked by a PHP extension';
		default:
			return 'error code ' . (string) $code;
	}
}

/**
 * @return string Short summary for JSON errors when no parts were stored.
 */
function fzc_web_summarize_files_field_errors(?array $filesField): string {
	if ($filesField === null || !isset($filesField['error'])) {
		return 'no upload details';
	}
	$errs = $filesField['error'];
	$names = $filesField['name'] ?? array();
	if (!is_array($errs)) {
		$errs = array($errs);
		$names = is_array($names) ? $names : array($names);
	}
	$parts = array();
	for ($i = 0; $i < count($errs); $i++) {
		$code = (int) ($errs[$i] ?? UPLOAD_ERR_NO_FILE);
		$label = isset($names[$i]) ? basename((string) $names[$i]) : ('#' . (string) $i);
		$parts[] = $label . ': ' . fzc_web_upload_err_string($code);
	}
	return implode('; ', $parts);
}

/**
 * Exit with JSON 413 if Content-Length exceeds cap (best-effort before reading body).
 */
function fzc_web_reject_body_too_large(): void {
	$max = fzc_web_max_upload_bytes();
	if ($max <= 0) {
		return;
	}
	$cl = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
	if ($cl > $max) {
		fzc_web_send_json(array(
			'ok' => false,
			'error' => 'Request body exceeds the ' . (string) $max . '-byte limit for this web demo. Use the CLI or set FZC_WEB_MAX_UPLOAD_BYTES on your own server.',
		), 413);
	}
}

/** Sum sizes of all regular files under $dir (recursive). */
function fzc_web_total_bytes_under(string $dir): int {
	if (!is_dir($dir)) {
		return 0;
	}
	$sum = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $item) {
		if ($item->isFile()) {
			$s = $item->getSize();
			if (is_int($s)) {
				$sum += $s;
			}
		}
	}
	return $sum;
}

/**
 * If staged files exceed cap, remove job tree and send JSON 413.
 */
function fzc_web_enforce_staging_limit(string $jobRoot, string $staging): void {
	$max = fzc_web_max_upload_bytes();
	if ($max <= 0) {
		return;
	}
	$total = fzc_web_total_bytes_under($staging);
	if ($total > $max) {
		fzc_web_remove_tree($jobRoot);
		fzc_web_send_json(array(
			'ok' => false,
			'error' => 'Total upload exceeds the ' . (string) $max . '-byte limit for this web demo. Use the CLI for larger trees.',
		), 413);
	}
}

/**
 * @return array{0: string, 1: string} handler basename (relative URL), path from site root (e.g. /fractal_zip/fzc_compress.php)
 */
function fzc_web_handler_identity(string $defaultBasename): array {
	$defaultBasename = basename(str_replace('\\', '/', $defaultBasename));
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '';
	$dir = $scriptName !== '' ? dirname($scriptName) : '';
	if ($dir === '.' || $dir === '') {
		$dir = '';
	}
	$env = getenv('FRACTAL_ZIP_WEB_HANDLER_BASENAME');
	if ($env !== false && trim((string) $env) !== '') {
		$b = basename(str_replace('\\', '/', trim((string) $env)));
		if ($b === '') {
			$b = $defaultBasename;
		}
	} else {
		$b = $scriptName !== '' ? basename($scriptName) : $defaultBasename;
		if (preg_match('/\.html?$/i', $b)) {
			$stem = preg_replace('/\.html?$/i', '', $b);
			$phpB = $stem . '.php';
			$wantStem = preg_replace('/\.php$/i', '', $defaultBasename);
			$stemMatchesDefault = strcasecmp($stem, $wantStem) === 0;
			if ($stemMatchesDefault || is_file(__DIR__ . DIRECTORY_SEPARATOR . $phpB)) {
				$b = $phpB;
			}
		}
	}
	if ($dir === '' || $dir === '/') {
		$path = '/' . $b;
	} else {
		$path = $dir . '/' . $b;
	}
	return array($b, $path);
}

function fzc_web_load_fractal_zip(): void {
	static $done = false;
	if ($done) {
		return;
	}
	$lib = getenv('FRACTAL_ZIP_PHP');
	if ($lib === false || trim((string) $lib) === '') {
		$lib = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	}
	if (!is_file($lib)) {
		$lib = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	}
	if (!is_file($lib)) {
		http_response_code(500);
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode(array('ok' => false, 'error' => 'fractal_zip.php not found; set FRACTAL_ZIP_PHP or place fractal_zip.php in the same directory as fzc_web_shared.php (or repo parent for examples/ layout).'));
		exit;
	}
	$libDir = (string) dirname($lib);
	/* `fractal_zip.php` pulls in `fractal_zip_literal_pac.php` and peers; a partial copy causes fatal "Failed opening required …pdf_native_pac…". */
	$fzcPeer = array(
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
	$peerMissing = array();
	for ($i = 0, $c = count($fzcPeer); $i < $c; $i++) {
		$bn = (string) $fzcPeer[$i];
		if (!is_file($libDir . DIRECTORY_SEPARATOR . $bn)) {
			$peerMissing[] = $bn;
		}
	}
	if (count($peerMissing) > 0) {
		http_response_code(500);
		header('Content-Type: application/json; charset=UTF-8');
		echo json_encode(array(
			'ok' => false,
			'error' => 'fractal_zip library in ' . $libDir . ' is incomplete (missing: ' . implode(', ', $peerMissing) . '). Deploy the full repository root (all fractal_zip*.php next to fractal_zip.php) or set FRACTAL_ZIP_PHP to that directory\'s fractal_zip.php.',
		), JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
		exit;
	}
	if (!defined('FRACTAL_ZIP_WEB_JSON_API')) {
		define('FRACTAL_ZIP_WEB_JSON_API', true);
	}
	require_once $lib;
	$done = true;
}

function fzc_web_is_absolute_path(string $path): bool {
	if ($path === '') {
		return false;
	}
	if ($path[0] === '/' || $path[0] === '\\') {
		return true;
	}
	return strlen($path) >= 3 && ctype_alpha($path[0]) && $path[1] === ':' && ($path[2] === '\\' || $path[2] === '/');
}

function fzc_web_resolve_jobs_path(string $path): string {
	$path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($path));
	$path = rtrim($path, DIRECTORY_SEPARATOR);
	if ($path === '') {
		return __DIR__ . DIRECTORY_SEPARATOR . 'web_jobs';
	}
	if (fzc_web_is_absolute_path($path)) {
		return $path;
	}
	return __DIR__ . DIRECTORY_SEPARATOR . $path;
}

/**
 * @return bool true if $root exists, is a directory, and is writable
 */
function fzc_web_prepare_jobs_root(string $root): bool {
	if ($root === '') {
		return false;
	}
	if (!is_dir($root) && !@mkdir($root, 0775, true) && !is_dir($root)) {
		return false;
	}
	if (is_dir($root) && is_writable($root)) {
		fzc_web_install_jobs_htaccess_if_absent($root);
	}
	return is_dir($root) && is_writable($root);
}

/**
 * Writable directory for per-request job folders. First match wins:
 * 1) FRACTAL_ZIP_WEB_JOBS (absolute or relative to the directory containing fzc_web_shared.php)
 * 2) web_jobs/ next to fzc_web_shared.php
 * 3) sys_get_temp_dir()/fzc_web_jobs_{hash} (when that tree is not writable, e.g. http user)
 */
function fzc_web_jobs_root(): string {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$tried = array();
	$candidates = array();
	$env = getenv('FRACTAL_ZIP_WEB_JOBS');
	if ($env !== false && trim((string) $env) !== '') {
		$candidates[] = fzc_web_resolve_jobs_path((string) $env);
	}
	$candidates[] = __DIR__ . DIRECTORY_SEPARATOR . 'web_jobs';
	$candidates[] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzc_web_jobs_' . substr(hash('sha256', __DIR__), 0, 14);

	foreach ($candidates as $root) {
		$tried[] = $root;
		if (fzc_web_prepare_jobs_root($root)) {
			$cached = $root;
			return $cached;
		}
	}
	throw new RuntimeException(
		'Cannot create or write job directory. Tried: ' . implode(' | ', $tried) .
		'. Fix permissions on web_jobs next to these scripts, or set env FRACTAL_ZIP_WEB_JOBS to a writable absolute path.'
	);
}

function fzc_web_new_job_dir(): string {
	$id = bin2hex(random_bytes(16));
	$root = fzc_web_jobs_root();
	$path = $root . DIRECTORY_SEPARATOR . $id;
	if (!@mkdir($path, 0775, true) && !is_dir($path)) {
		throw new RuntimeException('Cannot create job directory: ' . $path);
	}
	return $id;
}

/** @return null if path escapes or is empty */
function fzc_web_sanitize_rel_path(string $rel): ?string {
	$rel = str_replace('\\', '/', $rel);
	$rel = ltrim($rel, '/');
	if ($rel === '') {
		return null;
	}
	foreach (explode('/', $rel) as $seg) {
		if ($seg === '' || $seg === '.' || $seg === '..') {
			return null;
		}
		if (strpos($seg, "\0") !== false) {
			return null;
		}
	}
	return $rel;
}

/**
 * @param string $jobId job folder name under web_jobs
 * @param string $relPath relative path inside job (forward slashes)
 */
function fzc_web_job_path(string $jobId, string $relPath): ?string {
	if (!preg_match('/^[a-f0-9]{32}$/', $jobId)) {
		return null;
	}
	$rel = fzc_web_sanitize_rel_path($relPath);
	if ($rel === null) {
		return null;
	}
	$base = realpath(fzc_web_jobs_root() . DIRECTORY_SEPARATOR . $jobId);
	if ($base === false || !is_dir($base)) {
		return null;
	}
	$full = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	$real = realpath($full);
	if ($real === false || !is_file($real)) {
		return null;
	}
	if (strpos($real, $base) !== 0) {
		return null;
	}
	return $real;
}

/**
 * Turn off HTML error output so warnings/notices do not break JSON POST handlers.
 * Call at the start of each API POST branch (before require fractal_zip).
 */
function fzc_web_silence_php_errors_for_json_api(): void {
	@ini_set('display_errors', '0');
	@ini_set('display_startup_errors', '0');
}

/**
 * If PHP fatals before fzc_web_send_json, emit JSON so the web UI can show a message instead of a generic HTML 500 page.
 * Register once per request at the beginning of the POST handler.
 */
function fzc_web_register_fatal_json_shutdown(): void {
	static $registered = false;
	if ($registered) {
		return;
	}
	$registered = true;
	register_shutdown_function(static function (): void {
		$err = error_get_last();
		if ($err === null) {
			return;
		}
		$fatalTypes = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);
		if (!in_array($err['type'], $fatalTypes, true)) {
			return;
		}
		/* Drop buffered noise so we can send JSON; some hosts would otherwise return HTML 500 with empty body. */
		while (ob_get_level() > 0) {
			@ob_end_clean();
		}
		if (headers_sent()) {
			return;
		}
		http_response_code(500);
		header('Content-Type: application/json; charset=UTF-8');
		header('X-Content-Type-Options: nosniff');
		$msg = isset($err['message']) ? (string) $err['message'] : 'fatal error';
		$fn = isset($err['file']) ? basename((string) $err['file']) : '';
		$ln = isset($err['line']) ? (string) (int) $err['line'] : '0';
		$out = json_encode(array(
			'ok' => false,
			'error' => 'Server error (' . $fn . ':' . $ln . '): ' . $msg,
		), JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
		if ($out !== false) {
			echo $out;
		}
	});
}

function fzc_web_send_json(array $payload, int $code = 200): void {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	http_response_code($code);
	header('Content-Type: application/json; charset=UTF-8');
	header('X-Content-Type-Options: nosniff');
	$json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
	if ($json === false) {
		http_response_code(500);
		echo '{"ok":false,"error":"json_encode failed"}';
		exit;
	}
	echo $json;
	exit;
}

function fzc_web_stream_download(string $selfScript, string $jobId, string $relPath): void {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	$abs = fzc_web_job_path($jobId, $relPath);
	if ($abs === null) {
		http_response_code(404);
		header('Content-Type: text/plain; charset=UTF-8');
		echo 'Not found';
		return;
	}
	clearstatcache(true, $abs);
	$byteSize = filesize($abs);
	if ($byteSize === false) {
		http_response_code(500);
		header('Content-Type: text/plain; charset=UTF-8');
		echo 'Cannot read file size';
		return;
	}
	$mime = 'application/octet-stream';
	$leaf = basename($abs);
	if (strlen($leaf) >= 4 && strtolower(substr($leaf, -4)) === '.fzc') {
		$mime = 'application/octet-stream';
	}
	header('Content-Type: ' . $mime);
	$noQuote = str_replace('"', '', $leaf);
	$cd = 'attachment; filename="' . $noQuote . '"';
	/* Browsers that support RFC 5987 show the true UTF-8 name (e.g. CJK) from filename* */
	if (preg_match('/[\x80-\xFF]/', $leaf) === 1) {
		$cd .= "; filename*=UTF-8''" . rawurlencode($leaf);
	}
	header('Content-Disposition: ' . $cd);
	header('Content-Length: ' . (string) $byteSize);
	header('X-Content-Type-Options: nosniff');
	header('Cache-Control: private, no-transform');
	readfile($abs);
}

/**
 * Number of parts in a multipart files[] field (before filtering by upload error).
 */
function fzc_web_upload_files_field_count(array $filesField): int {
	if (!isset($filesField['name'])) {
		return 0;
	}
	$n = $filesField['name'];
	return is_array($n) ? count($n) : 1;
}

/**
 * Normalize $_POST list to list<string> (single string becomes one element).
 *
 * @param mixed $v
 * @return list<string>
 */
function fzc_web_post_string_list($v): array {
	if ($v === null) {
		return array();
	}
	if (is_string($v)) {
		return array($v);
	}
	if (!is_array($v)) {
		return array();
	}
	$out = array();
	foreach ($v as $x) {
		$out[] = (string) $x;
	}
	return $out;
}

/**
 * Save uploaded tree into $destDir (flat staging root).
 *
 * When $explicitRelPaths is non-null, it must have the same length as $_FILES['files'] parts; each entry is the
 * relative path for that index (used instead of multipart filename). The web compress UI sends this because many
 * PHP/SAPI stacks strip directory segments from $_FILES['files']['name'], which would flatten the tree.
 *
 * @param list<string>|null  $explicitRelPaths
 * @param string|null       $jobRootForManifest If set, append each successful rel + client name to
 *                        jobRoot/fzc_relpath_manifest.txt (do not rely on dirname($destDir); required on some hosts)
 * @return list<string> saved relative paths
 */
function fzc_web_save_upload_tree(string $destDir, ?array $explicitRelPaths = null, ?string $jobRootForManifest = null): array {
	if (!isset($_FILES['files'])) {
		return array();
	}
	$names = $_FILES['files']['name'];
	$tmps = $_FILES['files']['tmp_name'];
	$errs = $_FILES['files']['error'];
	if (!is_array($names)) {
		$names = array($names);
		$tmps = array($tmps);
		$errs = array($errs);
	}
	$saved = array();
	$n = count($names);
	if ($explicitRelPaths !== null && count($explicitRelPaths) !== $n) {
		throw new fzc_web_UnsafePathException(
			'fzc_relpath[] count (' . (string) count($explicitRelPaths) . ') does not match files[] count (' . (string) $n . ').'
		);
	}
	for ($i = 0; $i < $n; $i++) {
		if (($errs[$i] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
			continue;
		}
		$relSource = $explicitRelPaths !== null ? (string) ($explicitRelPaths[$i] ?? '') : (string) $names[$i];
		$rel = fzc_web_sanitize_rel_path($relSource);
		if ($rel === null) {
			if ($explicitRelPaths !== null) {
				throw new fzc_web_UnsafePathException('Invalid or empty relative path rejected: ' . $relSource);
			}
			continue;
		}
		if (fzc_web_rel_path_has_unsafe_segment($rel)) {
			throw new fzc_web_UnsafePathException('Invalid file path rejected: ' . $rel);
		}
		$target = $destDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$parent = dirname($target);
		if (!is_dir($parent) && !@mkdir($parent, 0755, true) && !is_dir($parent)) {
			throw new RuntimeException('Cannot create path: ' . $rel);
		}
		if (!@move_uploaded_file((string) $tmps[$i], $target)) {
			throw new RuntimeException('Failed to store: ' . $rel);
		}
		$saved[] = $rel;
		$manifestRoot = ($jobRootForManifest !== null && (string) $jobRootForManifest !== '')
			? (string) $jobRootForManifest
			: (string) dirname($destDir);
		$stagingOk = ($jobRootForManifest !== null && (string) $jobRootForManifest !== '')
			|| (strtolower((string) basename($destDir)) === 'staging');
		if ($manifestRoot !== '' && $stagingOk) {
			$clientName = (string) ($names[$i] ?? '');
			fzc_web_relpath_append_to_job_manifest($manifestRoot, $rel, $clientName);
		}
	}
	return $saved;
}

/**
 * Map fzc_relpath[] order to $_FILES['files']['name'] (same index) for download naming.
 *
 * @param list<string>   $relList
 * @param array<string, mixed> $filesField
 * @return array<string, string> relpath => client name
 */
function fzc_web_relpath_map_from_post_files(array $relList, array $filesField): array {
	$names = $filesField['name'] ?? null;
	if ($names === null) {
		return array();
	}
	if (!is_array($names)) {
		$names = array($names);
	}
	$out = array();
	$n = min(count($relList), count($names));
	for ($i = 0; $i < $n; $i++) {
		$r = (string) $relList[$i];
		$cn = (string) ($names[$i] ?? '');
		$r = str_replace(array("\r", "\n", "\0"), '', $r);
		$cn = (string) str_replace(array("\r", "\n", "\t", "\0"), ' ', (string) $cn);
		if ($r === '' || $cn === '') {
			continue;
		}
		$old = (string) ($out[$r] ?? '');
		if ($old === '' || strlen($cn) > strlen($old)) {
			$out[$r] = $cn;
		}
	}
	return $out;
}

/**
 * Append a successful upload path: rel from fzc_relpath[] and the matching multipart `files[]` client filename
 * (per $_FILES) so the download name can use the **original** long name even if the on-disk relpath differs.
 * Format: relpath TAB client_name newline (client may be empty).
 */
function fzc_web_relpath_append_to_job_manifest(string $jobRoot, string $rel, string $clientName = ''): void {
	$rel = str_replace(array("\r", "\n", "\0"), '', $rel);
	if ($rel === '') {
		return;
	}
	$clientName = (string) str_replace(array("\r", "\n", "\t", "\0"), ' ', (string) $clientName);
	$clientName = trim($clientName);
	$p = $jobRoot . DIRECTORY_SEPARATOR . 'fzc_relpath_manifest.txt';
	$line = $clientName === '' ? ($rel . "\n") : ($rel . "\t" . $clientName . "\n");
	@file_put_contents($p, $line, FILE_APPEND | LOCK_EX);
}

/**
 * @return array{rels: list<string>, by_relname: array<string, string>}  rels: unique sorted member relpaths; by_relname: relpath → best client `$_FILES['name']` (longest), for .fzc naming
 */
function fzc_web_relpath_read_job_manifest_parsed(string $jobRoot): array {
	$p = $jobRoot . DIRECTORY_SEPARATOR . 'fzc_relpath_manifest.txt';
	$byRel = array();
	if (!is_file($p)) {
		return array('rels' => array(), 'by_relname' => array());
	}
	$raw = @file_get_contents($p);
	if ($raw === false || $raw === '') {
		return array('rels' => array(), 'by_relname' => array());
	}
	$lines = preg_split('/\r\n|\n|\r/', (string) $raw);
	if (!is_array($lines)) {
		$lines = array();
	}
	$set = array();
	for ($i = 0, $c = count($lines); $i < $c; $i++) {
		$ln = (string) str_replace("\0", '', (string) ($lines[$i] ?? ''));
		$ln = trim($ln, "\0");
		$ln = (string) preg_replace('/[[:cntrl:]]+/', ' ', (string) $ln);
		$ln = trim($ln, ' ');
		if ($ln === '') {
			continue;
		}
		$tab = strpos($ln, "\t");
		$rel = $tab === false ? $ln : (string) substr($ln, 0, (int) $tab);
		$client = $tab === false ? '' : (string) trim((string) substr($ln, (int) $tab + 1));
		$rel = str_replace(array("\r", "\n"), '', $rel);
		if ($rel === '') {
			continue;
		}
		$set[$rel] = true;
		/* Longest client name wins (last write or longer), so picking by strlen is a tie-breaker */
		if ($client !== '') {
			$old = (string) ($byRel[$rel] ?? '');
			if ($old === '' || strlen($client) > strlen($old)) {
				$byRel[$rel] = $client;
			}
		}
	}
	$rels = array_values(array_keys($set));
	sort($rels, SORT_STRING);
	return array('rels' => $rels, 'by_relname' => $byRel);
}

/**
 * Save a single uploaded field to an exact path (parent dirs created).
 *
 * @throws RuntimeException on missing/invalid upload
 */
function fzc_web_save_single_upload(string $field, string $destAbsolutePath): void {
	if (!isset($_FILES[$field])) {
		throw new RuntimeException('No file uploaded (expected field: ' . $field . ')');
	}
	$err = (int) ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE);
	if ($err !== UPLOAD_ERR_OK) {
		throw new RuntimeException('Upload error code: ' . (string) $err);
	}
	$tmp = (string) ($_FILES[$field]['tmp_name'] ?? '');
	if ($tmp === '' || !is_uploaded_file($tmp)) {
		throw new RuntimeException('Invalid upload temp file');
	}
	$parent = dirname($destAbsolutePath);
	if (!is_dir($parent) && !@mkdir($parent, 0755, true) && !is_dir($parent)) {
		throw new RuntimeException('Cannot create destination directory');
	}
	if (!@move_uploaded_file($tmp, $destAbsolutePath)) {
		throw new RuntimeException('Failed to store uploaded file');
	}
}

function fzc_web_remove_tree(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		if ($item->isDir()) {
			@rmdir($item->getPathname());
		} else {
			@unlink($item->getPathname());
		}
	}
	@rmdir($dir);
}

/** Count regular files under $dir (recursive). */
function fzc_web_count_files_under(string $dir): int {
	if (!is_dir($dir)) {
		return 0;
	}
	$n = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $item) {
		if ($item->isFile()) {
			$n++;
		}
	}
	return $n;
}

/**
 * Member path as shown to the user (path inside the archive when packed), not the web job’s `staging/` prefix.
 * If a key wrongly starts with the same segment as $stripPrefix (e.g. "staging/…"), strip one level for display only.
 */
function fzc_web_archive_member_display_path(string $memberPath, string $stripPrefix): string {
	$p = str_replace('\\', '/', $memberPath);
	$sp = str_replace('\\', '/', $stripPrefix);
	if ($sp !== '' && strncmp($p, $sp . '/', strlen($sp) + 1) === 0) {
		return substr($p, strlen($sp) + 1);
	}
	return $p;
}

/**
 * After fractal_zip::open_container(), build download rows using **member paths stored in the .fzc**
 * (`array_fractal_zipped_strings_of_files` keys) so the UI matches the archive. Falls back to a directory
 * scan if the map is empty.
 *
 * Each row: path/rel = member path for display (archive layout); dl = path for ?dl= under the job (includes staging/ when used).
 *
 * @param object $fz fractal_zip instance after successful open_container()
 * @return array{
 *   files:list<array{path:string,rel:string,dl:string,url:string,size:int}>,
 *   scrubbed:int,
 *   safe_files_total:int,
 *   list_truncated:bool,
 *   paths_from_container:bool
 * }
 */
function fzc_web_extract_file_rows_after_open_container(
	object $fz,
	string $stagingDir,
	string $handlerPath,
	string $jobId,
	string $stripPrefix,
	string $archiveLeaf,
	int $maxList
): array {
	$map = (isset($fz->array_fractal_zipped_strings_of_files) && is_array($fz->array_fractal_zipped_strings_of_files))
		? $fz->array_fractal_zipped_strings_of_files
		: array();
	$leafNorm = str_replace('\\', '/', $archiveLeaf);
	if ($map !== array()) {
		$rows = array();
		foreach (array_keys($map) as $p) {
			$norm = str_replace('\\', '/', (string) $p);
			if ($norm === '' || $norm === $leafNorm) {
				continue;
			}
			$full = $stagingDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $norm);
			if (!is_file($full)) {
				continue;
			}
			$dlRel = $stripPrefix === '' ? $norm : $stripPrefix . '/' . $norm;
			$displayPath = fzc_web_archive_member_display_path($norm, $stripPrefix);
			$sz = filesize($full);
			$rows[] = array(
				'member_path' => $displayPath,
				'path' => $displayPath,
				'rel' => $displayPath,
				'dl' => $dlRel,
				'url' => $handlerPath . '?job=' . rawurlencode($jobId) . '&dl=' . rawurlencode($dlRel),
				'size' => is_int($sz) ? $sz : 0,
			);
		}
		$mapKeyCount = count($map);
		if ($rows === array() && $mapKeyCount > 0) {
			// Member keys did not match extracted files on disk; fall through to directory scan below.
		} else {
			usort($rows, static function (array $a, array $b): int {
				return strcmp($a['path'], $b['path']);
			});
			$total = count($rows);
			$truncated = $maxList > 0 && $total > $maxList;
			if ($truncated) {
				$rows = array_slice($rows, 0, $maxList);
			}
			return array(
				'files' => $rows,
				'scrubbed' => 0,
				'safe_files_total' => $total,
				'list_truncated' => $truncated,
				'paths_from_container' => true,
			);
		}
	}
	$post = fzc_web_scrub_and_list_extracted_files($stagingDir, $handlerPath, $jobId, $stripPrefix, $maxList);
	$files = array();
	foreach ($post['files'] as $row) {
		$r = (string) ($row['rel'] ?? '');
		$pn = str_replace('\\', '/', $r);
		$dl = $stripPrefix === '' ? $pn : $stripPrefix . '/' . $pn;
		$displayPath = fzc_web_archive_member_display_path($pn, $stripPrefix);
		$files[] = array(
			'member_path' => $displayPath,
			'path' => $displayPath,
			'rel' => $displayPath,
			'dl' => $dl,
			'url' => (string) ($row['url'] ?? ''),
			'size' => (int) ($row['size'] ?? 0),
		);
	}
	usort($files, static function (array $a, array $b): int {
		return strcmp($a['path'], $b['path']);
	});
	return array(
		'files' => $files,
		'scrubbed' => $post['scrubbed'],
		'safe_files_total' => $post['safe_files_total'],
		'list_truncated' => $post['list_truncated'],
		'paths_from_container' => false,
	);
}

/**
 * One recursive walk over extracted files: drop structurally invalid basenames (rare), count files, build up to $maxList download rows.
 * Avoids a second full tree traversal (separate scrub + list was ~2× directory I/O on large trees like test_files*).
 *
 * @return array{
 *   scrubbed:int,
 *   safe_files_total:int,
 *   files:list<array{rel:string,url:string,size:int}>,
 *   list_truncated:bool
 * }
 */
function fzc_web_scrub_and_list_extracted_files(string $baseDir, string $selfUrl, string $jobId, string $stripPrefix, int $maxList): array {
	$out = array(
		'scrubbed' => 0,
		'safe_files_total' => 0,
		'files' => array(),
		'list_truncated' => false,
	);
	if (!is_dir($baseDir)) {
		return $out;
	}
	$baseReal = realpath($baseDir);
	if ($baseReal === false) {
		return $out;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		if (!$item->isFile()) {
			continue;
		}
		$baseName = $item->getBasename();
		if (fzc_web_path_component_is_unsafe($baseName)) {
			@unlink($item->getPathname());
			$out['scrubbed']++;
			continue;
		}
		$out['safe_files_total']++;
		if ($maxList <= 0 || count($out['files']) < $maxList) {
			$full = $item->getPathname();
			$rel = ltrim(str_replace('\\', '/', substr($full, strlen($baseReal))), '/');
			if ($rel === '') {
				continue;
			}
			$dlRel = $stripPrefix === '' ? $rel : $stripPrefix . '/' . $rel;
			$sz = filesize($full);
			$out['files'][] = array(
				'rel' => $rel,
				'url' => $selfUrl . '?job=' . rawurlencode($jobId) . '&dl=' . rawurlencode($dlRel),
				'size' => is_int($sz) ? $sz : 0,
			);
		}
	}
	if ($maxList > 0 && $out['safe_files_total'] > $maxList) {
		$out['list_truncated'] = true;
	}
	return $out;
}

/**
 * @return list<array{rel:string,url:string,size:int}>
 */
function fzc_web_list_files_for_links(string $baseDir, string $selfUrl, string $jobId, string $stripPrefix, int $maxFiles = 0): array {
	$out = array();
	$baseReal = realpath($baseDir);
	if ($baseReal === false) {
		return $out;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($baseDir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $item) {
		if (!$item->isFile()) {
			continue;
		}
		if (fzc_web_path_component_is_unsafe($item->getBasename())) {
			continue;
		}
		$full = $item->getPathname();
		$rel = ltrim(str_replace('\\', '/', substr($full, strlen($baseReal))), '/');
		if ($rel === '') {
			continue;
		}
		$dlRel = $stripPrefix === '' ? $rel : $stripPrefix . '/' . $rel;
		$sz = filesize($full);
		$out[] = array(
			'rel' => $rel,
			'url' => $selfUrl . '?job=' . rawurlencode($jobId) . '&dl=' . rawurlencode($dlRel),
			'size' => is_int($sz) ? $sz : 0,
		);
		if ($maxFiles > 0 && count($out) >= $maxFiles) {
			break;
		}
	}
	return $out;
}
