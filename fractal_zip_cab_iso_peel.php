<?php
declare(strict_types=1);

/**
 * CAB (via 7z) and ISO9660 sector CLASSIC peels.
 *
 * Kill: FRACTAL_ZIP_FOLDER_CAB_PEEL=0 / FRACTAL_ZIP_FOLDER_ISO_PEEL=0
 */

function fractal_zip_folder_cab_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_CAB_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_folder_iso_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_ISO_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

/**
 * Microsoft CAB: extract with 7z; CLASSIC only when 7z -t* rewrap matches bit-exactly.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_cab(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_cab_peel_enabled()) {
		return false;
	}
	$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
	if (is_readable($lb)) {
		require_once $lb;
	}
	if (!defined('FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC')) {
		return false;
	}
	if (strlen($diskBytes) < 16 || substr($diskBytes, 0, 4) !== 'MSCF') {
		return false;
	}
	$base = strtolower(basename($diskPath));
	if ($base !== '' && !preg_match('/\.(cab|exe)$/', $base) && str_contains($base, '.')) {
		// Allow magic-only for extensionless; skip unrelated exts.
		if (!str_ends_with($base, '.cab')) {
			return false;
		}
	}
	$bin = trim((string) shell_exec('command -v 7z 2>/dev/null'));
	if ($bin === '') {
		$bin = trim((string) shell_exec('command -v 7za 2>/dev/null'));
	}
	if ($bin === '') {
		return false;
	}
	$tmp = tempnam(sys_get_temp_dir(), 'fzcab_');
	if ($tmp === false) {
		return false;
	}
	$arc = $tmp . '.cab';
	@unlink($tmp);
	if (@file_put_contents($arc, $diskBytes) === false) {
		@unlink($arc);
		return false;
	}
	$outDir = $arc . '_out';
	@mkdir($outDir, 0700, true);
	$cmd = escapeshellarg($bin) . ' x -y -o' . escapeshellarg($outDir . DIRECTORY_SEPARATOR)
		. ' ' . escapeshellarg($arc) . ' 2>/dev/null';
	shell_exec($cmd);
	@unlink($arc);
	$names = array();
	$payloads = array();
	$addedNew = array();
	$useShare = function_exists('fractal_zip_folder_register_shared_payload');
	$iter = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($outDir, FilesystemIterator::SKIP_DOTS)
	);
	$cap = 256;
	$n = 0;
	foreach ($iter as $file) {
		if (!$file->isFile() || $n >= $cap) {
			continue;
		}
		$full = $file->getPathname();
		$rel = substr($full, strlen($outDir) + 1);
		$rel = str_replace('\\', '/', (string) $rel);
		if ($rel === '' || str_contains($rel, '..')) {
			continue;
		}
		$data = @file_get_contents($full);
		if (!is_string($data)) {
			continue;
		}
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
		$n++;
	}
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
	if (count($names) < 1) {
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
	// Try a short -mx ladder; CAB create often unsupported — then refuse CLASSIC.
	foreach (array('0', '5', '9') as $mx) {
		$re = fractal_zip_classic_rebuild_archive('cab', $mx, $payloads);
		if ($re !== null && $re === $diskBytes) {
			$restore[$diskPath] = array(
				'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
				'format' => 'cab',
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

/**
 * ISO9660 / raw 2048-byte sector images: CLASSIC page peel for dense discs.
 * Highly sparse images (mostly zero sectors) stay PASSTHROUGH — zeros compress better.
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_iso(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_iso_peel_enabled()) {
		return false;
	}
	$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
	if (is_readable($lb)) {
		require_once $lb;
	}
	if (!defined('FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC')) {
		return false;
	}
	$n = strlen($diskBytes);
	$pageSize = 2048;
	if ($n < $pageSize * 4 || ($n % $pageSize) !== 0) {
		return false;
	}
	// ISO9660 primary volume descriptor at sector 16.
	if ($n < 17 * $pageSize) {
		return false;
	}
	$pvd = substr($diskBytes, 16 * $pageSize, 6);
	if ($pvd !== "\x01CD001") {
		return false;
	}
	$pages = (int) ($n / $pageSize);
	if ($pages > 4096) {
		return false;
	}
	$zero = 0;
	$nonzeroIdx = array();
	for ($i = 0; $i < $pages; $i++) {
		$page = substr($diskBytes, $i * $pageSize, $pageSize);
		if (trim($page, "\0") === '') {
			$zero++;
		} else {
			$nonzeroIdx[] = $i;
		}
	}
	$nz = count($nonzeroIdx);
	// Sparse discs: PASSTHROUGH of zero runs wins. Require denser content.
	if ($nz < 8 || $zero * 2 > $pages) {
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (!function_exists('fractal_zip_classic_rebuild_archive')) {
		return false;
	}
	$names = array();
	$payloads = array();
	$addedNew = array();
	$useShare = function_exists('fractal_zip_folder_register_shared_payload');
	foreach ($nonzeroIdx as $i) {
		$page = substr($diskBytes, $i * $pageSize, $pageSize);
		$pn = 's_' . sprintf('%04d', $i);
		$logical = rtrim($diskPath, '/') . '/' . $pn;
		if ($useShare) {
			$key = fractal_zip_folder_register_shared_payload($members, $page, $pn, $logical, $addedNew);
			$names[] = function_exists('fractal_zip_folder_fzhr_share_name')
				? fractal_zip_folder_fzhr_share_name($key, $pn)
				: $key;
		} else {
			$members[$logical] = $page;
			$addedNew[] = $logical;
			$names[] = $logical;
		}
		$payloads[] = array('name' => $pn, 'data' => $page);
	}
	// meta: pageSize + u16 pageCount + bitmap of present sectors (bit i set => nonzero page stored in order).
	$nb = (int) ceil($pages / 8);
	$bm = str_repeat("\0", max(1, $nb));
	foreach ($nonzeroIdx as $i) {
		$bm[$i >> 3] = chr(ord($bm[$i >> 3]) | (1 << ($i & 7)));
	}
	$meta = 'ISO1' . pack('v', $pageSize) . pack('v', $pages) . $bm;
	$re = fractal_zip_classic_rebuild_archive('isospare', $meta, $payloads);
	if ($re === null || $re !== $diskBytes) {
		foreach ($addedNew as $nm) {
			unset($members[$nm]);
		}
		return false;
	}
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
		'format' => 'isospare',
		'meta' => $meta,
		'member_names' => $names,
	);
	return true;
}
