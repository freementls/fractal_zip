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
 * Web upload cap (compress + multipart guard): FZC_WEB_MAX_UPLOAD_BYTES (default 8 MiB). Set to 0 to disable (private installs).
 * Extract-only cap (single .fz): FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES when set; otherwise 10× FZC_WEB_MAX_UPLOAD_BYTES (so default 80 MiB vs 8 MiB compress tree cap).
 *
 * Job retention: FZC_WEB_JOB_MAX_AGE_SEC (default 86400) + periodic GC via FZC_WEB_GC_INTERVAL_SEC (default 300)
 * — see {@see fzc_web_maybe_run_jobs_gc()}.
 *
 * Optional POST hardening: FZC_WEB_API_SECRET — require `Authorization: Bearer <secret>` on JSON POST bodies.
 * Rate limit: FZC_WEB_RATE_LIMIT_REQUESTS (>0) with FZC_WEB_RATE_LIMIT_WINDOW_SEC (default 60) — per-client IP
 * using state files under jobs_root/.fzc_rate/ (best-effort; place a reverse-proxy limiter for production).
 * Download limit: FZC_WEB_DOWNLOAD_RATE_LIMIT_REQUESTS (+ optional FZC_WEB_DOWNLOAD_RATE_LIMIT_WINDOW_SEC) for `?job=&dl=` handlers.
 * Optional shared secret: FZC_WEB_DOWNLOAD_TOKEN — require `?token=` or `X-FZC-Download-Token` on downloads; JSON `url` fields append `&token=` automatically when set.
 * CORS: FRACTAL_ZIP_WEB_CORS_ORIGIN — e.g. https://app.example.com or * — adds ACAO headers on JSON POST responses and downloads; OPTIONS → 204 when set.
 * Downloads: Accept-Ranges bytes; single Range (no multipart) → 206 + Content-Range; invalid range → 416.
 * If-Range: when present with Range, partial reply only if entity-tag or Last-Modified matches; else full 200.
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

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();

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
 * Max bytes for web demo uploads (compress: total staged tree; multipart Content-Length guard for compress). 0 = no limit.
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
 * Max .fz file bytes for the extract web demo (single archive). 0 = no limit.
 * When FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES is unset, defaults to 10× {@see fzc_web_max_upload_bytes()} so extract
 * can accept larger archives than compress staging without raising the tree upload cap.
 */
function fzc_web_max_extract_archive_bytes(): int {
	$e = getenv('FZC_WEB_MAX_EXTRACT_ARCHIVE_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		$v = trim((string) $e);
		if (ctype_digit($v)) {
			return (int) $v;
		}
	}
	$up = fzc_web_max_upload_bytes();
	if ($up <= 0) {
		return 0;
	}
	return $up * 10;
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
 *
 * @param int $maxBytes 0 = skip check; compress passes {@see fzc_web_max_upload_bytes()}, extract passes {@see fzc_web_max_extract_archive_bytes()}.
 */
function fzc_web_reject_body_too_large(int $maxBytes): void {
	if ($maxBytes <= 0) {
		return;
	}
	$cl = isset($_SERVER['CONTENT_LENGTH']) ? (int) $_SERVER['CONTENT_LENGTH'] : 0;
	if ($cl > $maxBytes) {
		fzc_web_send_json(array(
			'ok' => false,
			'error' => 'Request body exceeds the ' . (string) $maxBytes . '-byte limit for this web demo. Use the CLI or raise the cap / PHP upload limits on your own server.',
		), 413);
	}
}

function fzc_web_client_ip_for_limit(): string {
	$xff = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? trim(explode(',', (string) $_SERVER['HTTP_X_FORWARDED_FOR'])[0]) : '';
	if ($xff !== '' && filter_var($xff, FILTER_VALIDATE_IP)) {
		return $xff;
	}
	return isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '0';
}

/**
 * Sliding-window counter under jobs_root/$subdir (JSON files keyed by client IP + $saltSuffix).
 * Returns false when the window is exhausted; true when allowed (counter incremented).
 * Fail-open if jobs root or mkdir fails. $maxReq <= 0 disables.
 */
function fzc_web_rate_limit_try_consume(string $subdir, string $saltSuffix, int $maxReq, int $winSec): bool {
	if ($maxReq <= 0) {
		return true;
	}
	if ($winSec < 10) {
		$winSec = 10;
	}
	try {
		$root = fzc_web_jobs_root();
	} catch (Throwable $e) {
		return true;
	}
	$dir = $root . DIRECTORY_SEPARATOR . $subdir;
	if (!is_dir($dir) && !@mkdir($dir, 0770, true) && !is_dir($dir)) {
		return true;
	}
	$ip = fzc_web_client_ip_for_limit();
	$fn = $dir . DIRECTORY_SEPARATOR . hash('sha256', $ip . $saltSuffix) . '.json';
	$now = time();
	$state = array('window' => $now, 'count' => 0);
	if (is_file($fn)) {
		$j = bench_json_decode_file_assoc_try($fn, 'fzc_web rate_limit', 512, 0, false);
		if ($j !== null && isset($j['window'], $j['count'])) {
			$state['window'] = (int) $j['window'];
			$state['count'] = (int) $j['count'];
		}
	}
	if ($now - $state['window'] >= $winSec) {
		$state['window'] = $now;
		$state['count'] = 0;
	}
	if ($state['count'] >= $maxReq) {
		return false;
	}
	$state['count']++;
	$jsRl = bench_json_encode_try($state, false);
	if ($jsRl !== null) {
		@file_put_contents($fn, $jsRl);
	}
	return true;
}

/**
 * Append `&token=…` for download URLs when FZC_WEB_DOWNLOAD_TOKEN is set (server-side JSON links).
 */
function fzc_web_download_token_query_suffix(): string {
	$t = getenv('FZC_WEB_DOWNLOAD_TOKEN');
	if ($t === false || trim((string) $t) === '') {
		return '';
	}
	return '&token=' . rawurlencode(trim((string) $t));
}

/**
 * Canonical `fzc_*.php?job=…&dl=…` URL including optional download token query suffix.
 */
function fzc_web_build_download_url(string $handlerPath, string $jobId, string $dlRel): string {
	return $handlerPath . '?job=' . rawurlencode($jobId) . '&dl=' . rawurlencode($dlRel) . fzc_web_download_token_query_suffix();
}

/**
 * True when no download token is configured, or the request presents the same secret (query or header).
 */
function fzc_web_download_token_validates(): bool {
	$t = getenv('FZC_WEB_DOWNLOAD_TOKEN');
	if ($t === false || trim((string) $t) === '') {
		return true;
	}
	$want = trim((string) $t);
	$got = '';
	if (isset($_GET['token']) && is_string($_GET['token'])) {
		$got = trim((string) $_GET['token']);
	}
	if ($got === '' && isset($_SERVER['HTTP_X_FZC_DOWNLOAD_TOKEN'])) {
		$got = trim((string) $_SERVER['HTTP_X_FZC_DOWNLOAD_TOKEN']);
	}
	return $got !== '' && hash_equals($want, $got);
}

function fzc_web_cors_origin_value(): ?string {
	$o = getenv('FRACTAL_ZIP_WEB_CORS_ORIGIN');
	if ($o === false || trim((string) $o) === '') {
		return null;
	}
	$origin = trim((string) $o);
	if (strpbrk($origin, "\r\n") !== false) {
		return null;
	}
	return $origin;
}

function fzc_web_cors_is_enabled(): bool {
	return fzc_web_cors_origin_value() !== null;
}

function fzc_web_maybe_send_cors_headers(): void {
	$origin = fzc_web_cors_origin_value();
	if ($origin === null) {
		return;
	}
	header('Access-Control-Allow-Origin: ' . $origin);
	header('Access-Control-Allow-Methods: GET, HEAD, POST, OPTIONS');
	header('Access-Control-Allow-Headers: Content-Type, Authorization, X-FZC-Download-Token');
	header('Access-Control-Max-Age: 86400');
}

/**
 * When FZC_WEB_API_SECRET is set, require Authorization: Bearer &lt;secret&gt;.
 */
function fzc_web_optional_bearer_authorized(): bool {
	$secret = getenv('FZC_WEB_API_SECRET');
	if ($secret === false || trim((string) $secret) === '') {
		return true;
	}
	$auth = '';
	if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
		$auth = (string) $_SERVER['HTTP_AUTHORIZATION'];
	} elseif (function_exists('apache_request_headers')) {
		foreach (apache_request_headers() as $k => $v) {
			if (strcasecmp((string) $k, 'Authorization') === 0) {
				$auth = is_array($v) ? implode(', ', $v) : (string) $v;
				break;
			}
		}
	}
	$want = 'Bearer ' . trim((string) $secret);
	return $auth !== '' && hash_equals($want, $auth);
}

/**
 * Optional Bearer for diagnostic GET endpoints (capability report) and JSON POST APIs.
 */
function fzc_web_require_optional_bearer_secret(bool $jsonResponse = true): void {
	if (fzc_web_optional_bearer_authorized()) {
		return;
	}
	if ($jsonResponse) {
		fzc_web_send_json(array('ok' => false, 'error' => 'Unauthorized'), 401);
	}
	http_response_code(401);
	header('Content-Type: text/plain; charset=UTF-8');
	echo "Unauthorized\n";
	exit;
}

/**
 * Optional Bearer auth + file-based POST rate limit (compress/extract JSON API).
 */
function fzc_web_require_optional_post_guard(): void {
	fzc_web_require_optional_bearer_secret(true);
	$maxReqRaw = getenv('FZC_WEB_RATE_LIMIT_REQUESTS');
	if ($maxReqRaw === false || trim((string) $maxReqRaw) === '') {
		return;
	}
	$maxReq = (int) $maxReqRaw;
	if ($maxReq <= 0) {
		return;
	}
	$win = (int) (getenv('FZC_WEB_RATE_LIMIT_WINDOW_SEC') ?: 60);
	if (!fzc_web_rate_limit_try_consume('.fzc_rate', "\0fzc_web_rl\v1", $maxReq, $win)) {
		fzc_web_send_json(array(
			'ok' => false,
			'error' => 'Rate limit exceeded; try again later or use the CLI.',
		), 429);
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

/**
 * fractal_zip favicon (fs #fs-icon-fractal — nested fractal squares for .fz).
 */
function fzc_web_render_favicon_links(): string {
	$scriptName = isset($_SERVER['SCRIPT_NAME']) ? str_replace('\\', '/', (string) $_SERVER['SCRIPT_NAME']) : '';
	$dir = $scriptName !== '' ? dirname($scriptName) : '';
	if ($dir === '.' || $dir === '/') {
		$dir = '';
	}
	$base = ($dir === '' || $dir === '/') ? '' : rtrim($dir, '/');
	$iconRel = ($base === '') ? '/icons/fractal-zip-favicon.svg' : $base . '/icons/fractal-zip-favicon.svg';
	$icoRel = ($base === '') ? '/favicon.ico' : $base . '/favicon.ico';
	$iconHref = htmlspecialchars($iconRel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	$icoHref = htmlspecialchars($icoRel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	return '<link rel="icon" type="image/svg+xml" href="' . $iconHref . '" />' . "\n"
		. '<link rel="icon" href="' . $icoHref . '" sizes="32x32" />' . "\n"
		. '<link rel="alternate icon" href="' . $icoHref . '" />' . "\n"
		. '<link rel="apple-touch-icon" href="' . $iconHref . '" />' . "\n";
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
		$jsMissing = bench_json_encode_try(array('ok' => false, 'error' => 'fractal_zip.php not found; set FRACTAL_ZIP_PHP or place fractal_zip.php in the same directory as fzc_web_shared.php (or repo parent for examples/ layout).'), false);
		echo $jsMissing !== null ? $jsMissing : '{"ok":false,"error":"json_encode failed"}';
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
		$jsPeer = bench_json_encode_try(array(
			'ok' => false,
			'error' => 'fractal_zip library in ' . $libDir . ' is incomplete (missing: ' . implode(', ', $peerMissing) . '). Deploy the full repository root (all fractal_zip*.php next to fractal_zip.php) or set FRACTAL_ZIP_PHP to that directory\'s fractal_zip.php.',
		), false);
		echo $jsPeer !== null ? $jsPeer : '{"ok":false,"error":"json_encode failed"}';
		exit;
	}
	if (!defined('FRACTAL_ZIP_WEB_JSON_API')) {
		define('FRACTAL_ZIP_WEB_JSON_API', true);
	}
	require_once $lib;
	$done = true;
}

function fzc_web_extract_compat_gate_enabled(): bool {
	$e = getenv('FZC_WEB_ENFORCE_EXTRACT_COMPAT');
	if ($e === false) {
		return false;
	}
	$v = strtolower(trim((string) $e));
	if ($v === '' || $v === '0' || $v === 'off' || $v === 'false' || $v === 'no') {
		return false;
	}
	return true;
}

/**
 * @return array<string, array{path:?string, env:string}>
 */
function fzc_web_collect_extract_compat_tools(): array {
	return array(
		'zpaq' => array('path' => fractal_zip::zpaq_executable(), 'env' => 'FRACTAL_ZIP_ZPAQ'),
		'7z' => array('path' => fractal_zip::seven_zip_executable(), 'env' => 'FRACTAL_ZIP_7Z'),
		'arc' => array('path' => fractal_zip::freearc_executable(), 'env' => 'FRACTAL_ZIP_ARC'),
		'brotli' => array('path' => fractal_zip::brotli_executable(), 'env' => 'FRACTAL_ZIP_BROTLI'),
		'xz' => array('path' => fractal_zip::xz_executable(), 'env' => 'FRACTAL_ZIP_XZ'),
		'zstd' => array('path' => fractal_zip::zstd_executable(), 'env' => 'FRACTAL_ZIP_ZSTD'),
	);
}

/**
 * @return list<array{id:string, message:string, fix:string}>
 */
function fzc_web_collect_extract_compat_gaps(): array {
	$gaps = array();
	if (!function_exists('proc_open') || !function_exists('shell_exec')) {
		$gaps[] = array(
			'id' => 'php_subprocess_disabled',
			'message' => 'PHP subprocess support is disabled (proc_open/shell_exec). Native outers cannot be extracted.',
			'fix' => 'Enable proc_open and shell_exec in the web SAPI.',
		);
	}
	foreach (fzc_web_collect_extract_compat_tools() as $tool => $meta) {
		$path = isset($meta['path']) && is_string($meta['path']) ? $meta['path'] : null;
		$env = isset($meta['env']) && is_string($meta['env']) ? $meta['env'] : ('FRACTAL_ZIP_' . strtoupper($tool));
		if ($path === null || $path === '') {
			$gaps[] = array(
				'id' => $tool . '_missing',
				'message' => "Required extract tool `{$tool}` is not resolved on this host.",
				'fix' => "Install {$tool} and set {$env} in examples/fz_fractal_local_env.php, then run scripts/fzc_live_setup_check.sh.",
			);
			continue;
		}
		if (!is_executable($path)) {
			$gaps[] = array(
				'id' => $tool . '_not_executable',
				'message' => "Required extract tool `{$tool}` is not executable: {$path}",
				'fix' => "Fix executable permissions/path and re-run scripts/fzc_live_setup_check.sh.",
			);
		}
	}
	return $gaps;
}

function fzc_web_enforce_extract_compat_or_json(): void {
	if (!fzc_web_extract_compat_gate_enabled()) {
		return;
	}
	$gaps = fzc_web_collect_extract_compat_gaps();
	if ($gaps === array()) {
		return;
	}
	$ids = array();
	$parts = array();
	foreach ($gaps as $g) {
		if (!is_array($g)) {
			continue;
		}
		$id = isset($g['id']) ? (string) $g['id'] : '';
		$msg = isset($g['message']) ? (string) $g['message'] : '';
		if ($id !== '') {
			$ids[] = $id;
		}
		if ($msg !== '') {
			$parts[] = $msg;
		}
	}
	$summary = $parts === array() ? '' : (' Details: ' . implode(' | ', $parts));
	fzc_web_send_json(array(
		'ok' => false,
		'error' => 'Live compatibility gate failed: this host is missing required outer-codec extract dependencies. Fix tooling and rerun scripts/fzc_live_setup_check.sh before serving .fz upload/extract.' . $summary,
		'compat_gap_ids' => $ids,
		'compat_gaps' => $gaps,
	), 503);
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

/**
 * Delete finished job folders under the jobs root older than $maxAgeSec (directory mtime). Returns removal count.
 */
function fzc_web_gc_jobs_older_than(int $maxAgeSec): int {
	if ($maxAgeSec < 60) {
		$maxAgeSec = 60;
	}
	$root = fzc_web_jobs_root();
	$now = time();
	$n = 0;
	$dh = @opendir($root);
	if ($dh === false) {
		return 0;
	}
	while (($e = readdir($dh)) !== false) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		if (!preg_match('/^[a-f0-9]{32}$/', $e)) {
			continue;
		}
		$p = $root . DIRECTORY_SEPARATOR . $e;
		if (!is_dir($p)) {
			continue;
		}
		$mt = @filemtime($p);
		if (!is_int($mt) || $mt > $now - $maxAgeSec) {
			continue;
		}
		fzc_web_remove_tree($p);
		$n++;
	}
	closedir($dh);
	return $n;
}

/**
 * Throttled GC (touch `.fzc_gc_last` under jobs root). Disable with FZC_WEB_GC_INTERVAL_SEC=0.
 */
function fzc_web_maybe_run_jobs_gc(): void {
	$ivRaw = getenv('FZC_WEB_GC_INTERVAL_SEC');
	if ($ivRaw !== false && trim((string) $ivRaw) === '0') {
		return;
	}
	$iv = (int) (($ivRaw !== false && trim((string) $ivRaw) !== '') ? $ivRaw : 300);
	if ($iv < 30) {
		$iv = 30;
	}
	$root = fzc_web_jobs_root();
	$stamp = $root . DIRECTORY_SEPARATOR . '.fzc_gc_last';
	if (is_file($stamp) && (time() - (int) @filemtime($stamp)) < $iv) {
		return;
	}
	@touch($stamp);
	$ageEnv = getenv('FZC_WEB_JOB_MAX_AGE_SEC');
	$age = (int) (($ageEnv !== false && trim((string) $ageEnv) !== '') ? $ageEnv : 86400);
	fzc_web_gc_jobs_older_than($age);
}

function fzc_web_new_job_dir(): string {
	fzc_web_maybe_run_jobs_gc();
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
		fzc_web_maybe_send_cors_headers();
		http_response_code(500);
		header('Content-Type: application/json; charset=UTF-8');
		header('X-Content-Type-Options: nosniff');
		$msg = isset($err['message']) ? (string) $err['message'] : 'fatal error';
		$fn = isset($err['file']) ? basename((string) $err['file']) : '';
		$ln = isset($err['line']) ? (string) (int) $err['line'] : '0';
		$payload = array(
			'ok' => false,
			'error' => 'Server error (' . $fn . ':' . $ln . '): ' . $msg,
		);
		$hintsPhp = __DIR__ . DIRECTORY_SEPARATOR . 'fzc_parity_hints.php';
		if (is_file($hintsPhp)) {
			require_once $hintsPhp;
			$payload = fzc_web_attach_parity_hint($payload);
		}
		$out = bench_json_encode_try($payload, false);
		if ($out !== null) {
			echo $out;
		}
	});
}

function fzc_web_send_json(array $payload, int $code = 200): void {
	if (empty($payload['ok'])) {
		$hintsPhp = __DIR__ . DIRECTORY_SEPARATOR . 'fzc_parity_hints.php';
		if (is_file($hintsPhp)) {
			require_once $hintsPhp;
			$payload = fzc_web_attach_parity_hint($payload);
		}
	}
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	fzc_web_maybe_send_cors_headers();
	http_response_code($code);
	header('Content-Type: application/json; charset=UTF-8');
	header('X-Content-Type-Options: nosniff');
	$json = bench_json_encode_try($payload, false);
	if ($json === null) {
		http_response_code(500);
		echo '{"ok":false,"error":"json_encode failed"}';
		exit;
	}
	echo $json;
	exit;
}

/**
 * Parse one RFC 7233 `bytes=` range (no multipart). Returns [start, end] inclusive for 206, null for full-file 200.
 * Sets $err to `range_not_satisfiable` when the range is invalid → caller should respond 416.
 *
 * @param-out ?string $err
 * @return array{0: int, 1: int}|null
 */
function fzc_web_parse_download_range_for_get(int $total, ?string &$err): ?array {
	$err = null;
	if ($total < 1) {
		return null;
	}
	if (!isset($_SERVER['HTTP_RANGE'])) {
		return null;
	}
	$r = trim((string) $_SERVER['HTTP_RANGE']);
	if (!preg_match('/^bytes\s*=\s*(.+)$/i', $r, $rm)) {
		return null;
	}
	$spec = trim($rm[1]);
	if (strpos($spec, ',') !== false) {
		return null;
	}
	if (!preg_match('/^(\d*)-(\d*)$/', $spec, $m)) {
		return null;
	}
	$a = $m[1];
	$b = $m[2];
	if ($a === '' && $b === '') {
		return null;
	}
	if ($a !== '' && $b !== '') {
		$start = (int) $a;
		$end = (int) $b;
	} elseif ($a !== '' && $b === '') {
		$start = (int) $a;
		$end = $total - 1;
	} else {
		$sfx = (int) $b;
		if ($sfx < 1) {
			return null;
		}
		$start = max(0, $total - $sfx);
		$end = $total - 1;
	}
	if ($start > $end || $start >= $total) {
		$err = 'range_not_satisfiable';
		return null;
	}
	$end = min($end, $total - 1);
	return array($start, $end);
}

/**
 * RFC 7232 If-None-Match for GET/HEAD downloads: strong ETag inner token or weak W/"…" in a comma-list.
 * Does not treat field-value `*` as unconditional 304 (avoid incorrect short-circuit on job downloads).
 */
function fzc_web_if_none_match_implies_not_modified(string $etagQuoted, string $ifNoneMatchHeader): bool {
	$inm = trim($ifNoneMatchHeader);
	if ($inm === '') {
		return false;
	}
	if ($inm === '*') {
		return false;
	}
	$inner = trim($etagQuoted, '"');
	foreach (array_map('trim', explode(',', $inm)) as $tok) {
		if ($tok === '' || $tok === '*') {
			continue;
		}
		if (preg_match('/^(?:W\/)?"([^"]*)"$/', $tok, $m) === 1) {
			if (hash_equals($inner, $m[1])) {
				return true;
			}
			continue;
		}
		if (strpos($tok, $inner) !== false) {
			return true;
		}
	}
	return false;
}

/**
 * RFC 7233 If-Range: entity-tag (incl. weak W/"…") or HTTP-date vs Unix mtime (±1s slack).
 */
function fzc_web_download_if_range_matches(string $etagQuoted, int $mtimeUnix, string $ifRangeRaw): bool {
	$s = trim($ifRangeRaw);
	if ($s === '') {
		return false;
	}
	if (preg_match('/^(?:W\/)?"([^"]*)"$/', $s, $m) === 1) {
		return hash_equals(trim($etagQuoted, '"'), $m[1]);
	}
	$t = @strtotime($s);
	if ($t !== false && $mtimeUnix > 0) {
		return abs($t - $mtimeUnix) <= 1;
	}
	return false;
}

function fzc_web_stream_download(string $selfScript, string $jobId, string $relPath): void {
	while (ob_get_level() > 0) {
		ob_end_clean();
	}
	fzc_web_maybe_send_cors_headers();
	if (!fzc_web_download_token_validates()) {
		http_response_code(403);
		header('Content-Type: text/plain; charset=UTF-8');
		echo 'Forbidden';
		return;
	}
	$maxDlRaw = getenv('FZC_WEB_DOWNLOAD_RATE_LIMIT_REQUESTS');
	if ($maxDlRaw !== false && trim((string) $maxDlRaw) !== '') {
		$maxDl = (int) $maxDlRaw;
		if ($maxDl > 0) {
			$winDl = (int) (getenv('FZC_WEB_DOWNLOAD_RATE_LIMIT_WINDOW_SEC') ?: 60);
			if (!fzc_web_rate_limit_try_consume('.fzc_rate_dl', "\0fzc_web_dl\v1", $maxDl, $winDl)) {
				http_response_code(429);
				header('Content-Type: text/plain; charset=UTF-8');
				header('Retry-After: ' . (string) max(1, $winDl));
				echo 'Too many download requests';
				return;
			}
		}
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
	$mtime = @filemtime($abs);
	$mt = is_int($mtime) ? $mtime : 0;
	$etag = '"' . md5($abs . "\0" . (string) $byteSize . "\0" . (string) $mt) . '"';
	header('ETag: ' . $etag);
	if ($mt > 0) {
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $mt) . ' GMT');
	}
	$inm = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? (string) $_SERVER['HTTP_IF_NONE_MATCH'] : '';
	if (fzc_web_if_none_match_implies_not_modified($etag, $inm)) {
		http_response_code(304);
		return;
	}
	$method = strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET'));
	$isHead = ($method === 'HEAD');
	$isGet = ($method === 'GET' || $isHead);
	$rangeErr = null;
	$rangePair = null;
	if ($isGet) {
		$rangePair = fzc_web_parse_download_range_for_get((int) $byteSize, $rangeErr);
	}
	if ($rangePair !== null && isset($_SERVER['HTTP_IF_RANGE'])) {
		if (!fzc_web_download_if_range_matches($etag, $mt, (string) $_SERVER['HTTP_IF_RANGE'])) {
			$rangePair = null;
		}
	}
	if ($rangeErr !== null) {
		http_response_code(416);
		header('Accept-Ranges: bytes');
		header('Content-Range: bytes */' . (string) $byteSize);
		header('Content-Type: text/plain; charset=UTF-8');
		echo 'Range Not Satisfiable';
		return;
	}
	header('Accept-Ranges: bytes');
	$mime = 'application/octet-stream';
	$leaf = basename($abs);
	if (str_ends_with(strtolower($leaf), '.fz') || str_ends_with(strtolower($leaf), '.fzc')) {
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
	header('X-Content-Type-Options: nosniff');
	header('Cache-Control: private, no-transform');
	if ($rangePair !== null) {
		$rStart = $rangePair[0];
		$rEnd = $rangePair[1];
		$rLen = $rEnd - $rStart + 1;
		http_response_code(206);
		header('Content-Range: bytes ' . (string) $rStart . '-' . (string) $rEnd . '/' . (string) $byteSize);
		header('Content-Length: ' . (string) $rLen);
		if (!$isHead) {
			$h = @fopen($abs, 'rb');
			if ($h === false) {
				http_response_code(500);
				header('Content-Type: text/plain; charset=UTF-8');
				echo 'Cannot open file';
				return;
			}
			if ($rStart > 0 && @fseek($h, $rStart, SEEK_SET) !== 0) {
				fclose($h);
				http_response_code(500);
				header('Content-Type: text/plain; charset=UTF-8');
				echo 'Seek failed';
				return;
			}
			$remain = $rLen;
			while ($remain > 0 && !feof($h)) {
				$take = $remain > 1048576 ? 1048576 : $remain;
				$chunk = fread($h, $take);
				if ($chunk === false || $chunk === '') {
					break;
				}
				echo $chunk;
				$remain -= strlen($chunk);
			}
			fclose($h);
		}
		return;
	}
	header('Content-Length: ' . (string) $byteSize);
	if (!$isHead) {
		readfile($abs);
	}
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
 * @return array{rels: list<string>, by_relname: array<string, string>}  rels: unique sorted member relpaths; by_relname: relpath → best client `$_FILES['name']` (longest), for .fz naming
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
 * After fractal_zip::open_container(), build download rows using **member paths stored in the .fz**
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
				'url' => fzc_web_build_download_url($handlerPath, $jobId, $dlRel),
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
				'url' => fzc_web_build_download_url($selfUrl, $jobId, $dlRel),
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
			'url' => fzc_web_build_download_url($selfUrl, $jobId, $dlRel),
			'size' => is_int($sz) ? $sz : 0,
		);
		if ($maxFiles > 0 && count($out) >= $maxFiles) {
			break;
		}
	}
	return $out;
}
