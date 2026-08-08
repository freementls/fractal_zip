<?php
declare(strict_types=1);

/**
 * CLASSIC peels for RPM (lead+headers + cpio payload) and multi-member 7z
 * (bit-exact rewrap when 7z -mx ladder matches).
 *
 * Kill: FRACTAL_ZIP_FOLDER_RPM_PEEL=0 / FRACTAL_ZIP_FOLDER_7Z_PEEL=0
 */

function fractal_zip_folder_rpm_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_RPM_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_folder_7z_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_7Z_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Find compressed payload offset in an RPM (after lead + headers).
 * Scans for gzip / xz / zstd / lzma / lzip / bzip2 magics past the 96-byte lead.
 */
function fractal_zip_rpm_find_payload_offset(string $bytes): ?int {
	$n = strlen($bytes);
	if ($n < 120 || substr($bytes, 0, 4) !== "\xed\xab\xee\xdb") {
		return null;
	}
	$magics = array(
		"\x1f\x8b",
		"\xfd7zXZ\x00",
		"\x28\xb5\x2f\xfd",
		'LZIP',
		'BZh',
	);
	for ($i = 96; $i < min($n - 6, 96 + 512 * 1024); $i++) {
		foreach ($magics as $m) {
			if (substr($bytes, $i, strlen($m)) === $m) {
				return $i;
			}
		}
		// raw lzma
		if (ord($bytes[$i]) === 0x5d && $i + 13 < $n) {
			return $i;
		}
	}
	return null;
}

/**
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_rpm(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_rpm_peel_enabled()) {
		return false;
	}
	if (strlen($diskBytes) < 120 || substr($diskBytes, 0, 4) !== "\xed\xab\xee\xdb") {
		return false;
	}
	$base = strtolower(basename($diskPath));
	if ($base !== '' && !str_ends_with($base, '.rpm') && !str_ends_with($base, '.spm')) {
		// Still allow magic-only when path has no extension.
		if (!str_ends_with($base, '.rpm') && str_contains($base, '.')) {
			return false;
		}
	}
	$off = fractal_zip_rpm_find_payload_offset($diskBytes);
	if ($off === null || $off >= strlen($diskBytes)) {
		return false;
	}
	$prefix = substr($diskBytes, 0, $off);
	$payload = substr($diskBytes, $off);
	$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
	if (is_readable($lb)) {
		require_once $lb;
	}
	if (!function_exists('fractal_zip_folder_decode_single_compress')) {
		return false;
	}
	$cpio = null;
	$codec = null;
	$lvl = null;
	if (str_starts_with($payload, "\x1f\x8b")) {
		$decoded = @gzdecode($payload);
		if (is_string($decoded) && $decoded !== '') {
			$cpio = $decoded;
			$codec = 'gzip';
			for ($lev = 1; $lev <= 9; $lev++) {
				$c = @gzencode($cpio, $lev);
				if (is_string($c) && $c === $payload) {
					$lvl = $lev;
					break;
				}
			}
		}
	} else {
		$inner = fractal_zip_folder_decode_single_compress($payload, $diskPath . '.payload');
		if ($inner !== null && $inner !== '') {
			$cpio = $inner;
			if (str_starts_with($payload, "\xfd7zXZ\x00")) {
				$codec = 'xz';
				$lvl = function_exists('fractal_zip_folder_xz_find_rewrap_level')
					? fractal_zip_folder_xz_find_rewrap_level($cpio, $payload) : null;
			} elseif (str_starts_with($payload, "\x28\xb5\x2f\xfd")) {
				$codec = 'zstd';
				$z = function_exists('fractal_zip_folder_zstd_find_rewrap')
					? fractal_zip_folder_zstd_find_rewrap($cpio, $payload) : null;
				if (is_array($z)) {
					$lvl = (string) ((int) $z['level']) . (!empty($z['no_check']) ? 'n' : '');
				}
			} elseif (str_starts_with($payload, 'BZh')) {
				$codec = 'bz2';
				$lvl = function_exists('fractal_zip_folder_bz2_find_rewrap_level')
					? fractal_zip_folder_bz2_find_rewrap_level($cpio, $payload) : null;
			} elseif (str_starts_with($payload, 'LZIP')) {
				$codec = 'lzip';
				$lvl = function_exists('fractal_zip_folder_lzip_find_rewrap_level')
					? fractal_zip_folder_lzip_find_rewrap_level($cpio, $payload) : null;
			}
		}
	}
	if ($cpio === null || $codec === null || $lvl === null) {
		return false;
	}
	if (!function_exists('fractal_zip_literal_cpio_newc_list_file_members')) {
		$cpioLib = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_cpio_newc.php';
		if (is_file($cpioLib)) {
			require_once $cpioLib;
		}
	}
	if (!function_exists('fractal_zip_literal_cpio_newc_list_file_members')) {
		return false;
	}
	$list = fractal_zip_literal_cpio_newc_list_file_members($cpio);
	if (!is_array($list) || count($list) < 1) {
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (!function_exists('fractal_zip_classic_rebuild_archive')) {
		return false;
	}
	$sha = hash('sha256', $prefix);
	$prefixPath = '__rpm_prefix/' . $sha;
	$prefixStore = $prefix;
	$prefixGz = false;
	if (function_exists('gzcompress')) {
		$gz = @gzcompress($prefix, 9);
		if (is_string($gz) && $gz !== '' && strlen($gz) < strlen($prefix)) {
			$prefixStore = $gz;
			$prefixGz = true;
		}
	}
	$addedNew = array();
	$useShare = function_exists('fractal_zip_folder_register_shared_payload');
	if ($useShare) {
		$pfxKey = fractal_zip_folder_register_shared_payload(
			$members, $prefixStore, '_rpm_prefix', $prefixPath, $addedNew
		);
		// Keep registered key (legacy __rpm_prefix/sha or RS-alias); short name would not resolve.
		$names = array($pfxKey);
	} else {
		$members[$prefixPath] = $prefixStore;
		$addedNew[] = $prefixPath;
		$names = array($prefixPath);
	}
	$payloads = array(array('name' => '_rpm_prefix', 'data' => $prefixStore));
	foreach ($list as $m) {
		$n = str_replace('\\', '/', (string) ($m['name'] ?? ''));
		$d = (string) ($m['data'] ?? '');
		if ($n === '' || str_contains($n, '..')) {
			continue;
		}
		$logical = rtrim($diskPath, '/') . '/' . $n;
		if ($useShare) {
			$key = fractal_zip_folder_register_shared_payload($members, $d, $n, $logical, $addedNew);
			$names[] = function_exists('fractal_zip_folder_fzhr_share_name')
				? fractal_zip_folder_fzhr_share_name($key, $n)
				: $key;
		} else {
			$members[$logical] = $d;
			$addedNew[] = $logical;
			$names[] = $logical;
		}
		$payloads[] = array('name' => $n, 'data' => $d);
	}
	if (count($names) < 2) {
		foreach ($addedNew as $nm) {
			unset($members[$nm]);
		}
		return false;
	}
	// meta: codec:level:sha  or  codec:level:gz:sha when prefix member is gzip(raw).
	$meta = $prefixGz
		? ($codec . ':' . (string) $lvl . ':gz:' . $sha)
		: ($codec . ':' . (string) $lvl . ':' . $sha);
	$fmt = 'rpm';
	$re = fractal_zip_classic_rebuild_archive($fmt, $meta, $payloads);
	if ($re === null || $re !== $diskBytes) {
		foreach ($addedNew as $nm) {
			unset($members[$nm]);
		}
		return false;
	}
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
		'format' => $fmt,
		'meta' => $meta,
		'member_names' => $names,
	);
	return true;
}

/**
 * Multi-member 7z CLASSIC when a -mx ladder rewrap matches bit-exactly.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_7z(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_7z_peel_enabled()) {
		return false;
	}
	if (strlen($diskBytes) < 32 || substr($diskBytes, 0, 6) !== "7z\xbc\xaf\x27\x1c") {
		return false;
	}
	$bin = trim((string) shell_exec('command -v 7z 2>/dev/null'));
	if ($bin === '') {
		$bin = trim((string) shell_exec('command -v 7za 2>/dev/null'));
	}
	if ($bin === '') {
		return false;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fz7z_');
	if ($tmp === false) {
		return false;
	}
	$arc = $tmp . '.7z';
	@unlink($tmp);
	if (@file_put_contents($arc, $diskBytes) === false) {
		@unlink($arc);
		return false;
	}
	$outDir = $arc . '_out';
	@mkdir($outDir, 0700, true);
	// 7-Zip wants -oDIR with DIR immediately after -o (no space); trailing sep helps.
	$cmd = escapeshellarg($bin) . ' x -y -o' . escapeshellarg($outDir . DIRECTORY_SEPARATOR)
		. ' ' . escapeshellarg($arc) . ' 2>/dev/null';
	shell_exec($cmd);
	$payloads = array();
	$names = array();
	$addedNew = array();
	$useShare = function_exists('fractal_zip_folder_register_shared_payload');
	if (is_dir($outDir)) {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($outDir, FilesystemIterator::SKIP_DOTS)
		);
		$rootLen = strlen(rtrim($outDir, '/\\'));
		foreach ($it as $fi) {
			/** @var SplFileInfo $fi */
			if (!$fi->isFile()) {
				continue;
			}
			$full = $fi->getPathname();
			$rel = substr($full, $rootLen + 1);
			$rel = str_replace('\\', '/', (string) $rel);
			if ($rel === '' || str_contains($rel, '..')) {
				continue;
			}
			$data = (string) file_get_contents($full);
			$logical = rtrim($diskPath, '/') . '/' . $rel;
			if ($useShare) {
				$key = fractal_zip_folder_register_shared_payload($members, $data, $rel, $logical, $addedNew);
				$names[] = function_exists('fractal_zip_folder_fzhr_share_name')
					? fractal_zip_folder_fzhr_share_name($key, $rel)
					: $key;
			} else {
				$members[$logical] = $data;
				$addedNew[] = $logical;
				$names[] = $logical;
			}
			$payloads[] = array('name' => $rel, 'data' => $data);
		}
	}
	// cleanup extract tree
	$rm = static function (string $d) use (&$rm): void {
		if (!is_dir($d)) {
			return;
		}
		foreach (scandir($d) ?: array() as $e) {
			if ($e === '.' || $e === '..') {
				continue;
			}
			$p = $d . DIRECTORY_SEPARATOR . $e;
			if (is_dir($p)) {
				$rm($p);
			} else {
				@unlink($p);
			}
		}
		@rmdir($d);
	};
	$rm($outDir);
	@unlink($arc);
	if (count($payloads) < 1) {
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (!function_exists('fractal_zip_classic_rebuild_archive')) {
		foreach ($addedNew as $nm) {
			unset($members[$nm]);
		}
		return false;
	}
	// Try store / fast / normal rewrap levels for bit-exact match.
	// Originals built with `7z a -t7z -mx=N -mmt=off -mtc=off -mta=off -mtm=off` match.
	foreach (array('0', '1', '3', '5', '7', '9') as $mx) {
		$re = fractal_zip_classic_rebuild_archive('7z', $mx, $payloads);
		if ($re !== null && $re === $diskBytes) {
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => '7z',
				'meta' => $mx,
				'member_names' => $names,
			);
			return true;
		}
	}
	foreach ($addedNew as $nm) {
		unset($members[$nm]);
	}
	return false;
}
