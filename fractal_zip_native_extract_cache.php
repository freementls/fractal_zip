<?php
declare(strict_types=1);

/**
 * Persistent on-disk cache for native folder archive extracts (zpaq, etc.).
 * Shared across PHP requests / Apache workers; keyed by payload content digest.
 *
 * Phase 1 hybrid (tree cache + content-addressed member blobs):
 * - Tree cache under FRACTAL_ZIP_NATIVE_EXTRACT_CACHE_DIR (default: FILES_ROOT/derived/fz-native-extract-cache).
 * - After extract, members are ingested into FILES_ROOT/{sha256} via FileStore (record_times_stored=false).
 * - Manifest at FILES_ROOT/derived/fzpa/{digest40}/members.json maps relpath → content hash.
 * - Member read tries manifest+blob first, then tree cache.
 *
 * Digest key is sha256 of raw zpaq arc bytes (7kSt…), not the FZPA\x01 wrapper container.
 *
 * FRACTAL_ZIP_NATIVE_EXTRACT_CACHE=0 — disable cache (legacy temp-only extract).
 * FRACTAL_ZIP_NATIVE_EXTRACT_CACHE_DIR — override tree cache root.
 * FRACTAL_ZIP_NATIVE_EXTRACT_MANIFEST=0 — skip manifest ingest/read (tree cache only).
 */

function fractal_zip_native_extract_cache_manifest_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_NATIVE_EXTRACT_MANIFEST');
	if ($e === '0' || strtolower(trim((string) $e)) === 'off' || strtolower(trim((string) $e)) === 'false') {
		return $cached = false;
	}
	return $cached = true;
}

function fractal_zip_native_extract_cache_files_root(): string {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FILES_ROOT');
	if (is_string($e) && trim($e) !== '') {
		return $cached = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($e)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}
	$e = getenv('FRACTAL_FILES_ROOT');
	if (is_string($e) && trim($e) !== '') {
		return $cached = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($e)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	}
	return $cached = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
}

function fractal_zip_native_extract_cache_bootstrap_filestore(): bool {
	static $loaded = null;
	if ($loaded !== null) {
		return $loaded;
	}
	if (class_exists('FileStore', false)) {
		return $loaded = true;
	}
	$root = fractal_zip_native_extract_cache_files_root();
	if (getenv('FILES_ROOT') === false) {
		putenv('FILES_ROOT=' . $root);
	}
	if (function_exists('ffs_files_require_bootstrap') && ffs_files_require_bootstrap()) {
		return $loaded = class_exists('FileStore', false);
	}
	$boot = $root . 'bootstrap.php';
	if (!is_file($boot)) {
		return $loaded = false;
	}
	require_once $boot;
	return $loaded = class_exists('FileStore', false);
}

function fractal_zip_native_extract_cache_manifest_dir(string $digest): string {
	$digest = strtolower(preg_replace('/[^a-f0-9]/', '', $digest) ?? '');
	if (strlen($digest) < 40) {
		return '';
	}
	return fractal_zip_native_extract_cache_files_root()
		. 'derived' . DIRECTORY_SEPARATOR . 'fzpa'
		. DIRECTORY_SEPARATOR . substr($digest, 0, 40);
}

function fractal_zip_native_extract_cache_manifest_path(string $digest): string {
	$dir = fractal_zip_native_extract_cache_manifest_dir($digest);
	if ($dir === '') {
		return '';
	}
	return $dir . DIRECTORY_SEPARATOR . 'members.json';
}

/**
 * @return array{version?: int, digest?: string, members?: array<string, string>}|null
 */
function fractal_zip_native_extract_cache_load_manifest(string $digest): ?array {
	$path = fractal_zip_native_extract_cache_manifest_path($digest);
	if ($path === '' || !is_file($path) || !is_readable($path)) {
		return null;
	}
	$raw = @file_get_contents($path);
	if (!is_string($raw) || $raw === '') {
		return null;
	}
	$data = json_decode($raw, true);
	if (!is_array($data) || !isset($data['members']) || !is_array($data['members'])) {
		return null;
	}
	$storedDigest = strtolower(preg_replace('/[^a-f0-9]/', '', (string) ($data['digest'] ?? '')) ?? '');
	$expect = strtolower(preg_replace('/[^a-f0-9]/', '', $digest) ?? '');
	if ($storedDigest !== '' && $storedDigest !== $expect) {
		return null;
	}
	return $data;
}

function fractal_zip_native_extract_cache_manifest_ingest_members(string $membersRoot, string $digest): bool {
	if (!fractal_zip_native_extract_cache_manifest_enabled()) {
		return false;
	}
	if (!fractal_zip_native_extract_cache_bootstrap_filestore()) {
		return false;
	}
	$members = fractal_zip_native_extract_cache_list_members($membersRoot);
	if ($members === array()) {
		return false;
	}
	$store = new FileStore();
	$map = array();
	foreach ($members as $rel) {
		$bytes = fractal_zip_native_extract_cache_read_member($membersRoot, $rel);
		if ($bytes === null) {
			return false;
		}
		$result = $store->ingestBytes($bytes, '', false);
		if (!$result->ok || $result->hash === '') {
			return false;
		}
		$map[$rel] = FileStore::normalizeHash($result->hash);
	}
	ksort($map);
	$manifestDir = fractal_zip_native_extract_cache_manifest_dir($digest);
	if ($manifestDir === '') {
		return false;
	}
	if (!is_dir($manifestDir) && !@mkdir($manifestDir, 0777, true) && !is_dir($manifestDir)) {
		return false;
	}
	$payload = array(
		'version' => 1,
		'digest' => strtolower(preg_replace('/[^a-f0-9]/', '', $digest) ?? ''),
		'members' => $map,
	);
	$json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
	if (!is_string($json)) {
		return false;
	}
	$manifestPath = fractal_zip_native_extract_cache_manifest_path($digest);
	return $manifestPath !== '' && @file_put_contents($manifestPath, $json, LOCK_EX) !== false;
}

function fractal_zip_native_extract_cache_read_member_from_manifest(string $digest, string $memberRelPath): ?string {
	if (!fractal_zip_native_extract_cache_manifest_enabled()) {
		return null;
	}
	$norm = fractal_zip::normalize_web_fs_member_relpath($memberRelPath);
	if ($norm === null) {
		return null;
	}
	$manifest = fractal_zip_native_extract_cache_load_manifest($digest);
	if ($manifest === null) {
		return null;
	}
	if (!fractal_zip_native_extract_cache_bootstrap_filestore()) {
		return null;
	}
	$members = $manifest['members'];
	if (!isset($members[$norm]) || !is_string($members[$norm])) {
		return null;
	}
	$hash = FileStore::normalizeHash($members[$norm]);
	if (strlen($hash) !== 64) {
		return null;
	}
	$store = new FileStore();
	$path = $store->blobPath($hash);
	if ($path === '' || !is_file($path) || !is_readable($path)) {
		return null;
	}
	$bytes = @file_get_contents($path);
	if (!is_string($bytes)) {
		return null;
	}
	if (hash('sha256', $bytes) !== $hash) {
		return null;
	}
	return $bytes;
}

/** @return list<string>|null */
function fractal_zip_native_extract_cache_list_members_from_manifest(string $digest): ?array {
	if (!fractal_zip_native_extract_cache_manifest_enabled()) {
		return null;
	}
	$manifest = fractal_zip_native_extract_cache_load_manifest($digest);
	if ($manifest === null) {
		return null;
	}
	$members = array();
	foreach ($manifest['members'] as $rel => $hash) {
		if (!is_string($rel) || $rel === '') {
			continue;
		}
		$norm = fractal_zip::normalize_web_fs_member_relpath($rel);
		if ($norm !== null) {
			$members[] = $norm;
		}
	}
	if ($members === array()) {
		return null;
	}
	sort($members);
	return $members;
}

function fractal_zip_native_extract_cache_enabled(): bool {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_NATIVE_EXTRACT_CACHE');
	if ($e === '0' || strtolower(trim((string) $e)) === 'off' || strtolower(trim((string) $e)) === 'false') {
		return $cached = false;
	}
	return $cached = true;
}

function fractal_zip_native_extract_cache_root(): string {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$candidates = array();
	$e = getenv('FRACTAL_ZIP_NATIVE_EXTRACT_CACHE_DIR');
	if (is_string($e) && trim($e) !== '') {
		$candidates[] = rtrim(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, trim($e)), DIRECTORY_SEPARATOR);
	}
	$filesRoot = fractal_zip_native_extract_cache_files_root();
	if ($filesRoot !== '') {
		$candidates[] = rtrim($filesRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'derived'
			. DIRECTORY_SEPARATOR . 'fz-native-extract-cache';
	}
	$candidates[] = fractal_zip::outer_codec_temp_dir() . DIRECTORY_SEPARATOR . 'fz-native-extract-cache';
	foreach ($candidates as $dir) {
		if ($dir === '') {
			continue;
		}
		if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
			continue;
		}
		if (@is_writable($dir)) {
			return $cached = $dir;
		}
	}
	$fallback = fractal_zip::outer_codec_temp_dir() . DIRECTORY_SEPARATOR . 'fz-native-extract-cache';
	if (!is_dir($fallback)) {
		@mkdir($fallback, 0777, true);
	}
	return $cached = $fallback;
}

function fractal_zip_native_extract_cache_log(string $message): void {
	$e = getenv('FRACTAL_ZIP_ZPAQ_EXTRACT_LOG');
	if ($e !== '1' && strtolower(trim((string) $e)) !== 'on' && strtolower(trim((string) $e)) !== 'true') {
		return;
	}
	$line = '[fzpa-extract] ' . $message . "\n";
	if (defined('STDERR') && is_resource(STDERR)) {
		fwrite(STDERR, $line);
		return;
	}
	error_log(trim($line));
}

function fractal_zip_native_extract_cache_max_concurrent_zpaq(): int {
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$e = getenv('FRACTAL_ZIP_ZPAQ_EXTRACT_MAX');
	if ($e !== false && trim((string) $e) !== '' && is_numeric(trim((string) $e))) {
		return $cached = max(1, (int) trim((string) $e));
	}
	return $cached = 2;
}

function fractal_zip_native_extract_cache_global_zpaq_slots_dir(): string {
	return fractal_zip_native_extract_cache_root() . DIRECTORY_SEPARATOR . '.zpaq-slots';
}

/** @return resource|null slot lock held until release */
function fractal_zip_native_extract_cache_acquire_global_zpaq_slot() {
	$max = fractal_zip_native_extract_cache_max_concurrent_zpaq();
	$semDir = fractal_zip_native_extract_cache_global_zpaq_slots_dir();
	if (!is_dir($semDir) && !@mkdir($semDir, 0777, true) && !is_dir($semDir)) {
		return null;
	}
	$fps = array();
	for ($i = 0; $i < $max; $i++) {
		$fp = @fopen($semDir . DIRECTORY_SEPARATOR . 'slot' . $i, 'c');
		if ($fp === false) {
			foreach ($fps as $f) {
				fclose($f);
			}
			return null;
		}
		$fps[$i] = $fp;
	}
	// Short wait: gold/verify must fall back to direct extract, not stall 120 s on
	// stale slot locks owned by another uid (Permission denied on .lock cleanup).
	$deadline = microtime(true) + 2.0;
	while (microtime(true) < $deadline) {
		for ($i = 0; $i < $max; $i++) {
			if (@flock($fps[$i], LOCK_EX | LOCK_NB)) {
				for ($j = 0; $j < $max; $j++) {
					if ($j !== $i) {
						fclose($fps[$j]);
					}
				}
				fractal_zip_native_extract_cache_log('acquired global zpaq slot ' . $i . '/' . $max);
				return $fps[$i];
			}
		}
		usleep(100000);
	}
	foreach ($fps as $fp) {
		fclose($fp);
	}
	return null;
}

/** @param resource|null $fp */
function fractal_zip_native_extract_cache_release_global_zpaq_slot($fp): void {
	if (!is_resource($fp)) {
		return;
	}
	@flock($fp, LOCK_UN);
	fclose($fp);
}

function fractal_zip_native_extract_cache_stale_temp_sandbox_glob(string $arcBytes): string {
	$digest16 = substr(fractal_zip_native_extract_cache_digest($arcBytes), 0, 16);
	return rtrim((string) sys_get_temp_dir(), DIRECTORY_SEPARATOR)
		. DIRECTORY_SEPARATOR . 'fzpa_s_' . $digest16 . '*';
}

function fractal_zip_native_extract_cache_cleanup_stale_temp_sandboxes(string $arcBytes): void {
	$pattern = fractal_zip_native_extract_cache_stale_temp_sandbox_glob($arcBytes);
	foreach (glob($pattern) ?: array() as $path) {
		if (!is_string($path) || $path === '' || !is_dir($path)) {
			continue;
		}
		fractal_zip_native_extract_cache_log('cleanup stale temp sandbox ' . $path);
		static $rm = null;
		if ($rm === null) {
			$rm = new fractal_zip();
		}
		$rm->recursive_remove_directory($path);
	}
}

/**
 * Poll until cache entry is ready (waiter while another worker extracts under flock).
 */
function fractal_zip_native_extract_cache_break_stale_lock(string $cacheDir): void {
	$lock = fractal_zip_native_extract_cache_lock_path($cacheDir);
	if (!is_file($lock)) {
		return;
	}
	$age = time() - (int) @filemtime($lock);
	if ($age < 300) {
		return;
	}
	@unlink($lock);
	fractal_zip_native_extract_cache_log('broke stale extract lock ' . $lock);
}

/**
 * Poll until cache entry is ready (waiter while another worker extracts under flock).
 */
function fractal_zip_native_extract_cache_wait_ready(string $kind, string $payloadBytes, float $maxWaitSec = 120.0): ?string {
	$digest = fractal_zip_native_extract_cache_digest($payloadBytes);
	$cacheDir = fractal_zip_native_extract_cache_entry_dir($kind, $digest);
	$membersRoot = fractal_zip_native_extract_cache_members_root($cacheDir);
	static $memReady = array();
	$deadline = microtime(true) + max(0.0, $maxWaitSec);
	while (microtime(true) < $deadline) {
		if (fractal_zip_native_extract_cache_is_ready($cacheDir, $digest, $memReady)) {
			fractal_zip_native_extract_cache_cleanup_stale_temp_sandboxes($payloadBytes);
			return $membersRoot;
		}
		usleep(100000);
	}
	fractal_zip_native_extract_cache_break_stale_lock($cacheDir);
	return null;
}

/** Stable sha256 digest for cache keys (cross-process). */
function fractal_zip_native_extract_cache_digest(string $payloadBytes): string {
	return hash('sha256', $payloadBytes);
}

function fractal_zip_native_extract_cache_entry_dir(string $kind, string $digest): string {
	$kind = preg_replace('/[^a-z0-9_]+/i', '', $kind) ?? 'native';
	if ($kind === '') {
		$kind = 'native';
	}
	return fractal_zip_native_extract_cache_root()
		. DIRECTORY_SEPARATOR . $kind
		. DIRECTORY_SEPARATOR . substr($digest, 0, 40);
}

function fractal_zip_native_extract_cache_members_root(string $cacheDir): string {
	return $cacheDir . DIRECTORY_SEPARATOR . 'members';
}

function fractal_zip_native_extract_cache_marker_path(string $cacheDir): string {
	return $cacheDir . DIRECTORY_SEPARATOR . '.complete';
}

function fractal_zip_native_extract_cache_lock_path(string $cacheDir): string {
	return $cacheDir . DIRECTORY_SEPARATOR . '.lock';
}

/**
 * @param array<string, true> $mem
 */
function fractal_zip_native_extract_cache_is_ready(string $cacheDir, string $digest, array &$mem = array()): bool {
	if (isset($mem[$digest])) {
		return true;
	}
	$marker = fractal_zip_native_extract_cache_marker_path($cacheDir);
	if (!is_file($marker)) {
		return false;
	}
	$stored = trim((string) @file_get_contents($marker));
	if ($stored !== $digest) {
		return false;
	}
	$membersRoot = fractal_zip_native_extract_cache_members_root($cacheDir);
	if (!is_dir($membersRoot)) {
		return false;
	}
	$sd = @scandir($membersRoot);
	if (!is_array($sd)) {
		return false;
	}
	foreach ($sd as $name) {
		if ($name !== '.' && $name !== '..') {
			$mem[$digest] = true;
			return true;
		}
	}
	return false;
}

/** @return resource|null per-digest lock (non-blocking flock; never hangs forever) */
function fractal_zip_native_extract_cache_acquire_lock(string $cacheDir) {
	$root = fractal_zip_native_extract_cache_root();
	if (!is_dir($root) && !@mkdir($root, 0777, true) && !is_dir($root)) {
		fractal_zip_native_extract_cache_log('cache root not writable: ' . $root);
		return null;
	}
	// Cap wait: blocking LOCK_EX ignored the deadline (kernel wait with no timeout).
	$deadline = microtime(true) + 120.0;
	while (microtime(true) < $deadline) {
		if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
			usleep(50000);
			continue;
		}
		fractal_zip_native_extract_cache_break_stale_lock($cacheDir);
		$lockPath = fractal_zip_native_extract_cache_lock_path($cacheDir);
		$fp = @fopen($lockPath, 'c');
		if ($fp === false) {
			usleep(50000);
			continue;
		}
		if (@flock($fp, LOCK_EX | LOCK_NB)) {
			return $fp;
		}
		fclose($fp);
		usleep(50000);
	}
	return null;
}

/** @param resource|null $fp */
function fractal_zip_native_extract_cache_release_lock($fp): void {
	if (!is_resource($fp)) {
		return;
	}
	@flock($fp, LOCK_UN);
	fclose($fp);
}

function fractal_zip_native_extract_cache_mark_complete(string $cacheDir, string $digest): bool {
	$marker = fractal_zip_native_extract_cache_marker_path($cacheDir);
	return @file_put_contents($marker, $digest) !== false;
}

function fractal_zip_native_extract_cache_prepare_members_root(string $cacheDir): ?string {
	$membersRoot = fractal_zip_native_extract_cache_members_root($cacheDir);
	if (is_dir($membersRoot)) {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($membersRoot, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $item) {
			if ($item->isDir()) {
				@rmdir($item->getPathname());
			} else {
				@unlink($item->getPathname());
			}
		}
	} elseif (!@mkdir($membersRoot, 0755, true) && !is_dir($membersRoot)) {
		return null;
	}
	return $membersRoot;
}

/**
 * Ensure extracted tree exists in cache; populate on miss under per-digest flock.
 *
 * @param callable(string $membersRoot): bool $extractFn writes members under $membersRoot; false = not a folder archive / extract failed
 * @return string|null absolute members root on success
 */
function fractal_zip_native_extract_cache_ensure(string $kind, string $payloadBytes, callable $extractFn): ?string {
	if (!fractal_zip_native_extract_cache_enabled()) {
		return null;
	}
	static $memReady = array();
	$digest = fractal_zip_native_extract_cache_digest($payloadBytes);
	$cacheDir = fractal_zip_native_extract_cache_entry_dir($kind, $digest);
	$membersRoot = fractal_zip_native_extract_cache_members_root($cacheDir);
	if (fractal_zip_native_extract_cache_is_ready($cacheDir, $digest, $memReady)) {
		fractal_zip_native_extract_cache_cleanup_stale_temp_sandboxes($payloadBytes);
		return $membersRoot;
	}
	$lock = fractal_zip_native_extract_cache_acquire_lock($cacheDir);
	if ($lock === null) {
		// Brief wait only; callers fall back to direct extract on null.
		return fractal_zip_native_extract_cache_wait_ready($kind, $payloadBytes, 2.0);
	}
	$globalSlot = null;
	try {
		if (fractal_zip_native_extract_cache_is_ready($cacheDir, $digest, $memReady)) {
			fractal_zip_native_extract_cache_cleanup_stale_temp_sandboxes($payloadBytes);
			return $membersRoot;
		}
		$globalSlot = fractal_zip_native_extract_cache_acquire_global_zpaq_slot();
		if ($globalSlot === null) {
			fractal_zip_native_extract_cache_log('global zpaq slot unavailable digest=' . substr($digest, 0, 12));
			return fractal_zip_native_extract_cache_wait_ready($kind, $payloadBytes, 2.0);
		}
		if (fractal_zip_native_extract_cache_is_ready($cacheDir, $digest, $memReady)) {
			fractal_zip_native_extract_cache_cleanup_stale_temp_sandboxes($payloadBytes);
			return $membersRoot;
		}
		@unlink(fractal_zip_native_extract_cache_marker_path($cacheDir));
		$workRoot = fractal_zip_native_extract_cache_prepare_members_root($cacheDir);
		if ($workRoot === null) {
			return null;
		}
		fractal_zip_native_extract_cache_log('zpaq extract start digest=' . substr($digest, 0, 12) . ' kind=' . $kind);
		if (!$extractFn($workRoot)) {
			fractal_zip_native_extract_cache_log('zpaq extract failed digest=' . substr($digest, 0, 12));
			return null;
		}
		$sd = @scandir($workRoot);
		$hasMember = false;
		if (is_array($sd)) {
			foreach ($sd as $name) {
				if ($name !== '.' && $name !== '..') {
					$hasMember = true;
					break;
				}
			}
		}
		if (!$hasMember) {
			return null;
		}
		fractal_zip_native_extract_cache_manifest_ingest_members($workRoot, $digest);
		if (!fractal_zip_native_extract_cache_mark_complete($cacheDir, $digest)) {
			return null;
		}
		$memReady[$digest] = true;
		fractal_zip_native_extract_cache_cleanup_stale_temp_sandboxes($payloadBytes);
		fractal_zip_native_extract_cache_log('zpaq extract complete digest=' . substr($digest, 0, 12));
		return $membersRoot;
	} finally {
		fractal_zip_native_extract_cache_release_global_zpaq_slot($globalSlot);
		fractal_zip_native_extract_cache_release_lock($lock);
	}
}

/** Parse zpaq arc bytes from FZPA wrapper or raw 7kSt container payload. */
function fractal_zip_native_zpaq_arc_from_container_bytes(string $fullContents): ?string {
	$fullContents = (string) $fullContents;
	if (strlen($fullContents) >= 5 && substr($fullContents, 0, 4) === 'FZPA' && $fullContents[4] === "\x01") {
		return substr($fullContents, 5);
	}
	if (strlen($fullContents) >= 4 && substr($fullContents, 0, 4) === '7kSt') {
		return $fullContents;
	}
	return null;
}

/**
 * @return list<string> normalized member relpaths (sorted)
 */
function fractal_zip_native_extract_cache_list_members(string $membersRoot): array {
	if (!is_dir($membersRoot)) {
		return array();
	}
	$root = rtrim(str_replace('\\', '/', $membersRoot), '/');
	$rootLen = strlen($root) + 1;
	$members = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($membersRoot, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$path = str_replace('\\', '/', $fi->getPathname());
		if (strlen($path) < $rootLen) {
			continue;
		}
		$rel = substr($path, $rootLen);
		$norm = fractal_zip::normalize_web_fs_member_relpath($rel);
		if ($norm !== null) {
			$members[] = $norm;
		}
	}
	sort($members);
	return $members;
}

function fractal_zip_native_extract_cache_read_member(string $membersRoot, string $memberRelPath): ?string {
	$norm = fractal_zip::normalize_web_fs_member_relpath($memberRelPath);
	if ($norm === null || !is_dir($membersRoot)) {
		return null;
	}
	$path = $membersRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $norm);
	if (!is_file($path) || !is_readable($path)) {
		return null;
	}
	$bytes = @file_get_contents($path);
	return is_string($bytes) ? $bytes : null;
}
