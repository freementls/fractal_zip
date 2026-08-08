<?php
declare(strict_types=1);

/**
 * Encode-time peel for peel-native classic game archives (WAD/PAK/GRP/HOG/…).
 *
 * Exposes lump/member payloads to the folder tournament (toward entropy). Restore
 * uses FRACTAL_ZIP_FOLDER_RESTORE_VERBATIM (bit-exact container). Aligns with
 * /srv/http/peel formats/classic handlers + file_types native family.
 *
 * Kill: FRACTAL_ZIP_FOLDER_CLASSIC_PEEL=0
 * Caps: FRACTAL_ZIP_FOLDER_CLASSIC_MAX_MEMBERS (default 256),
 *       FRACTAL_ZIP_FOLDER_CLASSIC_MAX_RAW (default 16 MiB)
 */

function fractal_zip_folder_classic_peel_enabled(): bool
{
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_CLASSIC_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = true;
	}
	$v = strtolower(trim((string) $e));
	return $c = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function fractal_zip_folder_classic_max_members(): int
{
	$e = getenv('FRACTAL_ZIP_FOLDER_CLASSIC_MAX_MEMBERS');
	if ($e !== false && trim((string) $e) !== '' && (int) $e > 0) {
		return max(2, (int) $e);
	}
	return 256;
}

function fractal_zip_folder_classic_max_raw_bytes(): int
{
	$e = getenv('FRACTAL_ZIP_FOLDER_CLASSIC_MAX_RAW');
	if ($e !== false && trim((string) $e) !== '' && (int) $e > 0) {
		return max(64 * 1024, (int) $e);
	}
	return 16 * 1024 * 1024;
}

function fractal_zip_classic_peel_ensure(): bool
{
	static $ok = null;
	if ($ok !== null) {
		return $ok;
	}
	$peel = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'peel' . DIRECTORY_SEPARATOR . 'peel.php';
	if (!is_readable($peel)) {
		return $ok = false;
	}
	require_once $peel;
	if (function_exists('peel_bootstrap')) {
		peel_bootstrap();
	}
	return $ok = function_exists('peel_list_members') && function_exists('peel_extract_member');
}

/**
 * Detect peel-native classic container from path + magic.
 */
function fractal_zip_classic_peel_looks_native(string $diskPath, string $diskBytes): bool
{
	if ($diskBytes === '' || strlen($diskBytes) < 12) {
		return false;
	}
	$base = strtolower(basename(str_replace('\\', '/', $diskPath)));
	$ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
	$id4 = substr($diskBytes, 0, 4);
	if ($id4 === 'IWAD' || $id4 === 'PWAD') {
		return true;
	}
	if ($id4 === 'PACK') {
		return true;
	}
	if (strlen($diskBytes) >= 4 && unpack('V', substr($diskBytes, 0, 4))[1] === 0x55AA1234) {
		return true;
	}
	if ($id4 === "BIG\x00" || strncmp($diskBytes, 'BIGF', 4) === 0 || strncmp($diskBytes, 'BIG4', 4) === 0) {
		return true;
	}
	if (in_array($ext, ['wad', 'pak', 'grp', 'hog', 'mvl', 'hog2', 'pbo', 'big', 'vpk'], true)) {
		return true;
	}
	// file_types / peel strategy: native family
	if (fractal_zip_classic_peel_ensure() && function_exists('peel_strategy_for_path')) {
		$strat = peel_strategy_for_path($diskPath !== '' ? $diskPath : 'archive.bin', $diskBytes);
		if (is_array($strat) && ($strat['family'] ?? '') === 'native' && !empty($strat['listable'])) {
			return true;
		}
	}
	return false;
}

/**
 * Expand classic archive bytes into logical members via peel hub (directory order).
 *
 * @return list<array{name: string, data: string}>|null
 */
function fractal_zip_classic_peel_list_members_from_bytes(string $hintPath, string $diskBytes): ?array
{
	if (!fractal_zip_classic_peel_ensure()) {
		return null;
	}
	$maxRaw = fractal_zip_folder_classic_max_raw_bytes();
	if (strlen($diskBytes) > $maxRaw) {
		return null;
	}
	$fmt = fractal_zip_classic_peel_detect_format($hintPath, $diskBytes);
	if ($fmt === null) {
		return null;
	}
	$ext = $fmt['format'] === 'hog' ? 'hog' : $fmt['format'];
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzclassic_'
		. md5($hintPath . "\0" . strlen($diskBytes)) . '.' . $ext;
	if (@file_put_contents($tmp, $diskBytes) === false) {
		return null;
	}
	try {
		$entries = [];
		switch ($fmt['format']) {
			case 'wad':
				if (!function_exists('peel_wad_read_index')) {
					return null;
				}
				$idx = peel_wad_read_index($tmp);
				foreach ($idx['lumps'] as $l) {
					$entries[] = ['name' => (string) $l['name'], 'offset' => (int) $l['offset'], 'size' => (int) $l['size']];
				}
				break;
			case 'pak':
				if (!function_exists('peel_pak_read_index')) {
					return null;
				}
				foreach (peel_pak_read_index($tmp) as $e) {
					$entries[] = ['name' => (string) $e['name'], 'offset' => (int) $e['offset'], 'size' => (int) $e['size']];
				}
				break;
			case 'grp':
				if (!function_exists('peel_grp_read_index')) {
					return null;
				}
				foreach (peel_grp_read_index($tmp) as $e) {
					$entries[] = ['name' => (string) $e['name'], 'offset' => (int) $e['offset'], 'size' => (int) $e['size']];
				}
				break;
			case 'hog':
				if (!function_exists('peel_hog_read_index')) {
					return null;
				}
				foreach (peel_hog_read_index($tmp) as $e) {
					$entries[] = ['name' => (string) $e['name'], 'offset' => (int) $e['offset'], 'size' => (int) $e['size']];
				}
				break;
			case 'big':
				if (!function_exists('peel_big_read_index')) {
					return null;
				}
				foreach (peel_big_read_index($tmp) as $e) {
					$entries[] = ['name' => (string) $e['name'], 'offset' => (int) $e['offset'], 'size' => (int) $e['size']];
				}
				break;
			case 'pbo':
				if (!function_exists('peel_pbo_read_index')) {
					return null;
				}
				foreach (peel_pbo_read_index($tmp) as $e) {
					$entries[] = ['name' => (string) $e['name'], 'offset' => (int) $e['offset'], 'size' => (int) $e['size']];
				}
				break;
			case 'vpk':
				$vpkEntries = fractal_zip_classic_vpk_list_from_bytes($diskBytes);
				if ($vpkEntries === null) {
					return null;
				}
				foreach ($vpkEntries as $e) {
					$entries[] = ['name' => (string) $e['name'], 'offset' => (int) $e['offset'], 'size' => (int) $e['size']];
				}
				break;
			default:
				return null;
		}
		$minEntries = in_array($fmt['format'], ['pak', 'wad', 'grp', 'hog'], true) ? 1 : 2;
		if (count($entries) < $minEntries) {
			return null;
		}
		$cap = fractal_zip_folder_classic_max_members();
		$members = [];
		$total = 0;
		$n = 0;
		foreach ($entries as $e) {
			if ($n >= $cap) {
				break;
			}
			$name = str_replace('\\', '/', (string) $e['name']);
			$size = (int) $e['size'];
			$off = (int) $e['offset'];
			if ($name === '' || $size <= 0 || $off < 0 || $off + $size > strlen($diskBytes)) {
				continue;
			}
			if ($total + $size > $maxRaw) {
				break;
			}
			$members[] = ['name' => $name, 'data' => substr($diskBytes, $off, $size)];
			$total += $size;
			$n++;
		}
		return count($members) >= $minEntries ? $members : null;
	} catch (Throwable $e) {
		return null;
	} finally {
		@unlink($tmp);
	}
}

/**
 * Detect classic format id + rebuild meta from path/bytes.
 *
 * @return array{format: string, meta: string}|null
 */
function fractal_zip_classic_peel_detect_format(string $diskPath, string $diskBytes): ?array
{
	$id4 = substr($diskBytes, 0, 4);
	$ext = strtolower(pathinfo($diskPath, PATHINFO_EXTENSION));
	if ($id4 === 'IWAD' || $id4 === 'PWAD') {
		return ['format' => 'wad', 'meta' => $id4];
	}
	if ($id4 === 'PACK') {
		return ['format' => 'pak', 'meta' => ''];
	}
	$sig = strlen($diskBytes) >= 4 ? unpack('V', substr($diskBytes, 0, 4))[1] : 0;
	if ($sig === 0x55AA1234) {
		$ver = unpack('V', substr($diskBytes, 4, 4))[1];
		return ['format' => 'vpk', 'meta' => 'v' . (string) $ver];
	}
	if ($id4 === "BIG\x00" || strncmp($diskBytes, 'BIGF', 4) === 0 || strncmp($diskBytes, 'BIG4', 4) === 0) {
		$variant = ($id4 === "BIG\x00") ? 'big0' : substr($diskBytes, 0, 4);
		return ['format' => 'big', 'meta' => $variant];
	}
	if ($ext === 'grp') {
		return ['format' => 'grp', 'meta' => ''];
	}
	if (in_array($ext, ['hog', 'mvl', 'hog2'], true)) {
		return ['format' => 'hog', 'meta' => ''];
	}
	if ($ext === 'pbo') {
		return ['format' => 'pbo', 'meta' => ''];
	}
	if ($ext === 'vpk') {
		return ['format' => 'vpk', 'meta' => ''];
	}
	if ($ext === 'wad') {
		return ['format' => 'wad', 'meta' => 'PWAD'];
	}
	if ($ext === 'pak') {
		return ['format' => 'pak', 'meta' => ''];
	}
	return null;
}

/**
 * Deterministic newc (070701) cpio: regular files + TRAILER!!!.
 * Fields: ino sequential from 1, mode=0100644, uid/gid/mtime/devs/check=0, nlink=1.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_cpio_newc_rebuild(array $payloads): ?string
{
	if ($payloads === []) {
		return null;
	}
	$bin = '';
	$ino = 1;
	foreach ($payloads as $p) {
		$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
		$name = ltrim($name, '/');
		$data = (string) ($p['data'] ?? '');
		if ($name === '' || str_contains($name, '..') || $name === 'TRAILER!!!') {
			return null;
		}
		$nameField = $name . "\0";
		$namesize = strlen($nameField);
		$filesize = strlen($data);
		$hdr = '070701'
			. sprintf('%08X', $ino)
			. '000081A4' // S_IFREG | 0644
			. '00000000' // uid
			. '00000000' // gid
			. '00000001' // nlink
			. '00000000' // mtime
			. sprintf('%08X', $filesize)
			. '00000000' // devmajor
			. '00000000' // devminor
			. '00000000' // rdevmajor
			. '00000000' // rdevminor
			. sprintf('%08X', $namesize)
			. '00000000'; // check
		if (strlen($hdr) !== 110) {
			return null;
		}
		$bin .= $hdr . $nameField;
		$namePad = (4 - (strlen($bin) % 4)) % 4;
		$bin .= str_repeat("\0", $namePad);
		$bin .= $data;
		$dataPad = (4 - (strlen($bin) % 4)) % 4;
		$bin .= str_repeat("\0", $dataPad);
		$ino++;
	}
	// TRAILER!!!
	$trName = "TRAILER!!!\0";
	$trNs = strlen($trName);
	$tr = '070701'
		. '00000000'
		. '00000000'
		. '00000000'
		. '00000000'
		. '00000001'
		. '00000000'
		. '00000000'
		. '00000000'
		. '00000000'
		. '00000000'
		. '00000000'
		. sprintf('%08X', $trNs)
		. '00000000';
	if (strlen($tr) !== 110) {
		return null;
	}
	$bin .= $tr . $trName;
	$pad = (4 - (strlen($bin) % 4)) % 4;
	$bin .= str_repeat("\0", $pad);
	return $bin;
}

/**
 * Deterministic .deb: payloads named debian-binary, control/<path>, data/<path>.
 * Rebuilds control.tar + data.tar (classic ustar) then GNU ar.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_deb_rebuild(array $payloads): ?string
{
	$bin = null;
	$control = [];
	$data = [];
	foreach ($payloads as $p) {
		$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
		$bytes = (string) ($p['data'] ?? '');
		if ($name === 'debian-binary') {
			$bin = $bytes;
			continue;
		}
		if (str_starts_with($name, 'control/')) {
			$control[] = ['name' => substr($name, strlen('control/')), 'data' => $bytes];
			continue;
		}
		if (str_starts_with($name, 'data/')) {
			$data[] = ['name' => substr($name, strlen('data/')), 'data' => $bytes];
			continue;
		}
		return null;
	}
	if ($bin === null || $control === [] || $data === []) {
		return null;
	}
	$controlTar = fractal_zip_classic_tar_ustar_rebuild($control);
	$dataTar = fractal_zip_classic_tar_ustar_rebuild($data);
	if ($controlTar === null || $dataTar === null) {
		return null;
	}
	return fractal_zip_classic_rebuild_archive('ar', '', [
		['name' => 'debian-binary', 'data' => $bin],
		['name' => 'control.tar', 'data' => $controlTar],
		['name' => 'data.tar', 'data' => $dataTar],
	]);
}

/**
 * Deterministic .deb with gzipped tars: control.tar.gz + data.tar.gz (same gz level).
 * meta = gzip level 1–9.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_debgz_rebuild(array $payloads, string $meta): ?string
{
	return fractal_zip_classic_deb_wrapped_rebuild($payloads, 'gz', $meta);
}

/**
 * Deterministic .deb with xz-wrapped tars: control.tar.xz + data.tar.xz.
 * meta = xz level 0–9.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_debxz_rebuild(array $payloads, string $meta): ?string
{
	return fractal_zip_classic_deb_wrapped_rebuild($payloads, 'xz', $meta);
}

/**
 * Deterministic .deb with zstd-wrapped tars: control.tar.zst + data.tar.zst.
 * meta = zstd level ["3"|"3n"].
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_debzst_rebuild(array $payloads, string $meta): ?string
{
	return fractal_zip_classic_deb_wrapped_rebuild($payloads, 'zst', $meta);
}

/**
 * Deterministic .deb with brotli-wrapped tars: control.tar.br + data.tar.br.
 * meta = brotli quality 0–11.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_debbr_rebuild(array $payloads, string $meta): ?string
{
	return fractal_zip_classic_deb_wrapped_rebuild($payloads, 'br', $meta);
}

/**
 * Deterministic .deb with lz4-wrapped tars: control.tar.lz4 + data.tar.lz4.
 * meta = lz4 level 1–12.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_deblz4_rebuild(array $payloads, string $meta): ?string
{
	return fractal_zip_classic_deb_wrapped_rebuild($payloads, 'lz4', $meta);
}

/**
 * @param list<array{name: string, data: string}> $payloads
 * @param 'gz'|'xz'|'zst'|'br'|'lz4' $wrap
 */
function fractal_zip_classic_deb_wrapped_rebuild(array $payloads, string $wrap, string $meta): ?string
{
	$bin = null;
	$control = [];
	$data = [];
	foreach ($payloads as $p) {
		$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
		$bytes = (string) ($p['data'] ?? '');
		if ($name === 'debian-binary') {
			$bin = $bytes;
			continue;
		}
		if (str_starts_with($name, 'control/')) {
			$control[] = ['name' => substr($name, strlen('control/')), 'data' => $bytes];
			continue;
		}
		if (str_starts_with($name, 'data/')) {
			$data[] = ['name' => substr($name, strlen('data/')), 'data' => $bytes];
			continue;
		}
		return null;
	}
	if ($bin === null || $control === [] || $data === []) {
		return null;
	}
	$controlTar = fractal_zip_classic_tar_ustar_rebuild($control);
	$dataTar = fractal_zip_classic_tar_ustar_rebuild($data);
	if ($controlTar === null || $dataTar === null) {
		return null;
	}
	$suffix = '';
	$controlWrap = null;
	$dataWrap = null;
	if ($wrap === 'gz') {
		$lvl = max(1, min(9, (int) $meta));
		$controlWrap = @gzencode($controlTar, $lvl);
		$dataWrap = @gzencode($dataTar, $lvl);
		$suffix = '.gz';
	} elseif ($wrap === 'xz') {
		$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
		if (is_readable($lb)) {
			require_once $lb;
		}
		if (!function_exists('fractal_zip_folder_xz_shell_compress')) {
			return null;
		}
		$controlWrap = fractal_zip_folder_xz_shell_compress($controlTar, (int) $meta);
		$dataWrap = fractal_zip_folder_xz_shell_compress($dataTar, (int) $meta);
		$suffix = '.xz';
	} elseif ($wrap === 'zst') {
		$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
		if (is_readable($lb)) {
			require_once $lb;
		}
		if (!function_exists('fractal_zip_folder_zstd_shell_compress')) {
			return null;
		}
		$noCheck = str_ends_with($meta, 'n');
		$lvl = (int) rtrim($meta, 'n');
		$controlWrap = fractal_zip_folder_zstd_shell_compress($controlTar, $lvl, $noCheck);
		$dataWrap = fractal_zip_folder_zstd_shell_compress($dataTar, $lvl, $noCheck);
		$suffix = '.zst';
	} elseif ($wrap === 'br') {
		$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
		if (is_readable($lb)) {
			require_once $lb;
		}
		if (!function_exists('fractal_zip_folder_brotli_shell_compress')) {
			return null;
		}
		$q = max(0, min(11, (int) $meta));
		$controlWrap = fractal_zip_folder_brotli_shell_compress($controlTar, $q);
		$dataWrap = fractal_zip_folder_brotli_shell_compress($dataTar, $q);
		$suffix = '.br';
	} elseif ($wrap === 'lz4') {
		$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
		if (is_readable($lb)) {
			require_once $lb;
		}
		if (!function_exists('fractal_zip_folder_lz4_shell_compress')) {
			return null;
		}
		$lvl = max(1, min(12, (int) $meta));
		$controlWrap = fractal_zip_folder_lz4_shell_compress($controlTar, $lvl);
		$dataWrap = fractal_zip_folder_lz4_shell_compress($dataTar, $lvl);
		$suffix = '.lz4';
	} else {
		return null;
	}
	if (!is_string($controlWrap) || $controlWrap === '' || !is_string($dataWrap) || $dataWrap === '') {
		return null;
	}
	return fractal_zip_classic_rebuild_archive('ar', '', [
		['name' => 'debian-binary', 'data' => $bin],
		['name' => 'control.tar' . $suffix, 'data' => $controlWrap],
		['name' => 'data.tar' . $suffix, 'data' => $dataWrap],
	]);
}

/**
 * Deterministic POSIX ustar: mode 0644, uid/gid/mtime=0, type '0', magic ustar\\0/00.
 * Names must fit in the 100-byte name field (no prefix/GNU longlink).
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_tar_ustar_rebuild(array $payloads): ?string
{
	if ($payloads === []) {
		return null;
	}
	if (!function_exists('fractal_zip_literal_tar_ustar_fix_checksum')) {
		$tar = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_literal_tar_ustar.php';
		if (is_readable($tar)) {
			require_once $tar;
		}
	}
	if (!function_exists('fractal_zip_literal_tar_ustar_fix_checksum')) {
		return null;
	}
	$out = '';
	foreach ($payloads as $p) {
		$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
		$name = ltrim($name, '/');
		$data = (string) ($p['data'] ?? '');
		if ($name === '' || str_contains($name, '..') || strlen($name) > 100) {
			return null;
		}
		$hdr = str_pad($name, 100, "\0");
		$hdr .= "0000644\0"; // mode
		$hdr .= "0000000\0"; // uid
		$hdr .= "0000000\0"; // gid
		$hdr .= sprintf('%11o', strlen($data)) . ' '; // size
		$hdr .= "00000000000 "; // mtime
		$hdr .= '        '; // checksum placeholder
		$hdr .= '0'; // typeflag regular
		$hdr .= str_repeat("\0", 100); // linkname
		$hdr .= "ustar\0"; // magic
		$hdr .= '00'; // version
		$hdr .= str_repeat("\0", 32); // uname
		$hdr .= str_repeat("\0", 32); // gname
		$hdr .= str_repeat("\0", 8); // devmajor
		$hdr .= str_repeat("\0", 8); // devminor
		$hdr .= str_repeat("\0", 155); // prefix
		$hdr .= str_repeat("\0", 12); // pad to 512
		if (strlen($hdr) !== 512) {
			return null;
		}
		$fixed = fractal_zip_literal_tar_ustar_fix_checksum($hdr);
		if ($fixed === null) {
			return null;
		}
		$pad = (512 - (strlen($data) % 512)) % 512;
		$out .= $fixed[1] . $data . str_repeat("\0", $pad);
	}
	$out .= str_repeat("\0", 1024);
	return $out;
}

/**
 * Lossless TAR meta: original 512-byte ustar headers + trailing zero pad after end-of-archive.
 *
 * Wire v1: FZTH\x01 + u32 count + (512 hdr)×count + u32 trailer_pad
 * Wire v2: FZTH\x02 + u32 count + u32 trailer_pad + u32 rawLen + gzip(headers)
 *
 * @param list<string> $headers512
 */
function fractal_zip_classic_tar_fzth_encode(array $headers512, int $trailerPad): string
{
	$parts = array("FZTH\x01", pack('V', count($headers512)));
	$raw = '';
	foreach ($headers512 as $hdr) {
		if (strlen($hdr) !== 512) {
			return '';
		}
		$parts[] = $hdr;
		$raw .= $hdr;
	}
	$parts[] = pack('V', max(0, $trailerPad));
	$v1 = implode('', $parts);
	if ($raw === '' || !function_exists('gzcompress')) {
		return $v1;
	}
	$gz = @gzcompress($raw, 9);
	if (!is_string($gz) || $gz === '') {
		return $v1;
	}
	$v2 = "FZTH\x02" . pack('V', count($headers512)) . pack('V', max(0, $trailerPad))
		. pack('V', strlen($raw)) . $gz;
	return (strlen($v2) < strlen($v1)) ? $v2 : $v1;
}

/**
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_tar_rebuild_from_fzth(array $payloads, string $meta): ?string
{
	if (strlen($meta) < 9 || substr($meta, 0, 4) !== 'FZTH') {
		return null;
	}
	$ver = $meta[4] ?? '';
	$nHdr = unpack('V', substr($meta, 5, 4));
	$nHdr = is_array($nHdr) ? (int) $nHdr[1] : -1;
	if ($nHdr < 1 || $nHdr !== count($payloads)) {
		return null;
	}
	$headers = array();
	$trailerPad = 0;
	if ($ver === "\x01") {
		$off = 9;
		$need = $off + ($nHdr * 512) + 4;
		if ($need > strlen($meta)) {
			return null;
		}
		for ($i = 0; $i < $nHdr; $i++) {
			$headers[] = substr($meta, $off, 512);
			$off += 512;
		}
		$padInfo = unpack('V', substr($meta, $off, 4));
		$trailerPad = is_array($padInfo) ? (int) $padInfo[1] : 0;
	} elseif ($ver === "\x02") {
		if (strlen($meta) < 17 || !function_exists('gzuncompress')) {
			return null;
		}
		$padInfo = unpack('V', substr($meta, 9, 4));
		$trailerPad = is_array($padInfo) ? (int) $padInfo[1] : 0;
		$rawInfo = unpack('V', substr($meta, 13, 4));
		$rawLen = is_array($rawInfo) ? (int) $rawInfo[1] : -1;
		if ($rawLen !== $nHdr * 512) {
			return null;
		}
		$raw = @gzuncompress(substr($meta, 17));
		if (!is_string($raw) || strlen($raw) !== $rawLen) {
			return null;
		}
		for ($i = 0; $i < $nHdr; $i++) {
			$headers[] = substr($raw, $i * 512, 512);
		}
	} else {
		return null;
	}
	if ($trailerPad < 0 || $trailerPad > 64 * 1024 * 1024) {
		return null;
	}
	$out = '';
	foreach ($payloads as $i => $p) {
		$data = (string) ($p['data'] ?? '');
		$hdr = $headers[$i];
		$pad = (512 - (strlen($data) % 512)) % 512;
		$out .= $hdr . $data . str_repeat("\0", $pad);
	}
	$out .= str_repeat("\0", 1024);
	if ($trailerPad > 0) {
		$out .= str_repeat("\0", $trailerPad);
	}
	return $out;
}

/**
 * Lossless GNU ar meta: original 60-byte headers (mode/mtime/uid as on disk).
 *
 * Wire v1: FZAH\x01 + u32 count + (60 hdr)×count
 * Wire v2: FZAH\x02 + u32 count + u32 rawLen + gzip(headers)
 *
 * @param list<string> $headers60
 */
function fractal_zip_classic_ar_fzah_encode(array $headers60): string
{
	$parts = array("FZAH\x01", pack('V', count($headers60)));
	$raw = '';
	foreach ($headers60 as $hdr) {
		if (strlen($hdr) !== 60) {
			return '';
		}
		$parts[] = $hdr;
		$raw .= $hdr;
	}
	$v1 = implode('', $parts);
	if ($raw === '' || !function_exists('gzcompress')) {
		return $v1;
	}
	$gz = @gzcompress($raw, 9);
	if (!is_string($gz) || $gz === '') {
		return $v1;
	}
	$v2 = "FZAH\x02" . pack('V', count($headers60)) . pack('V', strlen($raw)) . $gz;
	return (strlen($v2) < strlen($v1)) ? $v2 : $v1;
}

/**
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_ar_rebuild_from_fzah(array $payloads, string $meta): ?string
{
	if (strlen($meta) < 9 || substr($meta, 0, 4) !== 'FZAH') {
		return null;
	}
	$ver = $meta[4] ?? '';
	$nHdr = unpack('V', substr($meta, 5, 4));
	$nHdr = is_array($nHdr) ? (int) $nHdr[1] : -1;
	if ($nHdr < 1 || $nHdr !== count($payloads)) {
		return null;
	}
	$headers = array();
	if ($ver === "\x01") {
		$off = 9;
		if ($off + ($nHdr * 60) > strlen($meta)) {
			return null;
		}
		for ($i = 0; $i < $nHdr; $i++) {
			$headers[] = substr($meta, $off, 60);
			$off += 60;
		}
	} elseif ($ver === "\x02") {
		if (strlen($meta) < 13 || !function_exists('gzuncompress')) {
			return null;
		}
		$rawInfo = unpack('V', substr($meta, 9, 4));
		$rawLen = is_array($rawInfo) ? (int) $rawInfo[1] : -1;
		if ($rawLen !== $nHdr * 60) {
			return null;
		}
		$raw = @gzuncompress(substr($meta, 13));
		if (!is_string($raw) || strlen($raw) !== $rawLen) {
			return null;
		}
		for ($i = 0; $i < $nHdr; $i++) {
			$headers[] = substr($raw, $i * 60, 60);
		}
	} else {
		return null;
	}
	$out = "!<arch>\n";
	for ($i = 0; $i < $nHdr; $i++) {
		$hdr = $headers[$i];
		$data = (string) ($payloads[$i]['data'] ?? '');
		// Size field must match payload (headers may carry original mode/mtime/uid).
		$sizeField = str_pad((string) strlen($data), 10, ' ');
		$hdr = substr($hdr, 0, 48) . $sizeField . substr($hdr, 58, 2);
		if (strlen($hdr) !== 60) {
			return null;
		}
		$out .= $hdr . $data;
		if ((strlen($data) % 2) === 1) {
			$out .= "\n";
		}
	}
	return $out;
}

/**
 * Lossless newc cpio meta: per-member prefix (110 hdr + name + name pad) + trailer blob.
 *
 * Wire v1: FZCH\x01 + u32 count + (u32 preLen + pre)×count + u32 trailerLen + trailer
 * Wire v2: FZCH\x02 + gzip(v1 body after magic byte) when smaller
 *
 * @param list<string> $prefixes
 */
function fractal_zip_classic_cpio_fzch_encode(array $prefixes, string $trailer): string
{
	if ($trailer === '') {
		return '';
	}
	$parts = array("FZCH\x01", pack('V', count($prefixes)));
	foreach ($prefixes as $pre) {
		if ($pre === '') {
			return '';
		}
		$parts[] = pack('V', strlen($pre));
		$parts[] = $pre;
	}
	$parts[] = pack('V', strlen($trailer));
	$parts[] = $trailer;
	$v1 = implode('', $parts);
	if (!function_exists('gzcompress') || strlen($v1) < 32) {
		return $v1;
	}
	$body = substr($v1, 5); // after FZCH\x01
	$gz = @gzcompress($body, 9);
	if (!is_string($gz) || $gz === '') {
		return $v1;
	}
	$v2 = "FZCH\x02" . $gz;
	return (strlen($v2) < strlen($v1)) ? $v2 : $v1;
}

/**
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_cpio_rebuild_from_fzch(array $payloads, string $meta): ?string
{
	if (strlen($meta) < 5 || substr($meta, 0, 4) !== 'FZCH') {
		return null;
	}
	$ver = $meta[4] ?? '';
	if ($ver === "\x02") {
		if (!function_exists('gzuncompress')) {
			return null;
		}
		$body = @gzuncompress(substr($meta, 5));
		if (!is_string($body) || $body === '') {
			return null;
		}
		$meta = "FZCH\x01" . $body;
		$ver = "\x01";
	}
	if ($ver !== "\x01" || strlen($meta) < 9) {
		return null;
	}
	$nPre = unpack('V', substr($meta, 5, 4));
	$nPre = is_array($nPre) ? (int) $nPre[1] : -1;
	if ($nPre < 1 || $nPre !== count($payloads)) {
		return null;
	}
	$off = 9;
	$nMeta = strlen($meta);
	$out = '';
	for ($i = 0; $i < $nPre; $i++) {
		if ($off + 4 > $nMeta) {
			return null;
		}
		$lp = unpack('V', substr($meta, $off, 4));
		$preLen = is_array($lp) ? (int) $lp[1] : -1;
		$off += 4;
		if ($preLen < 110 || $off + $preLen > $nMeta) {
			return null;
		}
		$pre = substr($meta, $off, $preLen);
		$off += $preLen;
		$data = (string) ($payloads[$i]['data'] ?? '');
		// Keep original ino/mtime/uid; refresh filesize hex (field index 6).
		$fsHex = strtoupper(str_pad(dechex(strlen($data)), 8, '0', STR_PAD_LEFT));
		if (strlen($fsHex) !== 8 || strlen($pre) < 110) {
			return null;
		}
		$hdr = substr($pre, 0, 54) . $fsHex . substr($pre, 62, 48);
		$nameAndPad = substr($pre, 110);
		$out .= $hdr . $nameAndPad . $data;
		$pad = (4 - (strlen($out) % 4)) % 4;
		if ($pad > 0) {
			$out .= str_repeat("\0", $pad);
		}
	}
	if ($off + 4 > $nMeta) {
		return null;
	}
	$lt = unpack('V', substr($meta, $off, 4));
	$trailLen = is_array($lt) ? (int) $lt[1] : -1;
	$off += 4;
	if ($trailLen < 0 || $off + $trailLen > $nMeta) {
		return null;
	}
	$out .= substr($meta, $off, $trailLen);
	return $out;
}


/**
 * Compact OLE CLASSIC meta (replaces JSON when smaller).
 *
 * Wire: FZOL\x02 + u8 flags + sha256(32) + u8 nStreams
 *   flags bit0 = shared template member is gzip(raw sparse/raw template)
 *   each stream: u8 nameLen + name + u8 nItems + items…
 *     item kind 0: u32 off + u32 len
 *     item kind 1: u32 off + u32 blockLen + u16 count  (count contiguous blocks)
 * Safe member basenames: sprintf("%03d_%s", i, sanitized name).
 *
 * @param list<array{name:string,safe?:string,ranges:list<array{0:int,1:int}>}> $streams
 */
function fractal_zip_classic_ole_fzol_encode(string $tmplShaHex, array $streams, bool $tmplGzipped = false): string
{
	if (strlen($tmplShaHex) !== 64 || !ctype_xdigit($tmplShaHex) || $streams === []) {
		return '';
	}
	$sha = @hex2bin($tmplShaHex);
	if (!is_string($sha) || strlen($sha) !== 32) {
		return '';
	}
	if (count($streams) > 255) {
		return '';
	}
	$flags = $tmplGzipped ? 1 : 0;
	$parts = array("FZOL\x02", chr($flags & 0xFF), $sha, chr(count($streams) & 0xFF));
	foreach ($streams as $st) {
		$name = (string) ($st['name'] ?? '');
		$ranges = $st['ranges'] ?? null;
		if ($name === '' || strlen($name) > 255 || !is_array($ranges) || $ranges === []) {
			return '';
		}
		// Coalesce contiguous equal-length ranges into runs.
		$items = array();
		$i = 0;
		$nR = count($ranges);
		while ($i < $nR) {
			$off = (int) ($ranges[$i][0] ?? -1);
			$len = (int) ($ranges[$i][1] ?? -1);
			if ($off < 0 || $len < 1) {
				return '';
			}
			$count = 1;
			while ($i + $count < $nR) {
				$nOff = (int) ($ranges[$i + $count][0] ?? -1);
				$nLen = (int) ($ranges[$i + $count][1] ?? -1);
				if ($nLen !== $len || $nOff !== $off + ($count * $len)) {
					break;
				}
				$count++;
			}
			if ($count >= 2 && $count <= 65535) {
				$items[] = array(1, $off, $len, $count);
				$i += $count;
			} else {
				$items[] = array(0, $off, $len, 1);
				$i += 1;
			}
		}
		if (count($items) > 255) {
			return '';
		}
		$parts[] = chr(strlen($name));
		$parts[] = $name;
		$parts[] = chr(count($items) & 0xFF);
		foreach ($items as $it) {
			$parts[] = chr((int) $it[0]);
			if ((int) $it[0] === 1) {
				$parts[] = pack('V', (int) $it[1]);
				$parts[] = pack('V', (int) $it[2]);
				$parts[] = pack('v', (int) $it[3]);
			} else {
				$parts[] = pack('V', (int) $it[1]);
				$parts[] = pack('V', (int) $it[2]);
			}
		}
	}
	return implode('', $parts);
}

/**
 * @return array{tmpl_sha:string,tmpl_gz:bool,streams:list<array{name:string,safe:string,ranges:list<array{0:int,1:int}>}>}|null
 */
function fractal_zip_classic_ole_fzol_decode(string $meta): ?array
{
	if (strlen($meta) < 5 + 1 + 32 + 1 || substr($meta, 0, 4) !== 'FZOL') {
		return null;
	}
	$ver = $meta[4] ?? '';
	if ($ver !== "\x01" && $ver !== "\x02") {
		return null;
	}
	$flags = ord($meta[5]);
	$sha = substr($meta, 6, 32);
	$n = ord($meta[38]);
	$off = 39;
	$nMeta = strlen($meta);
	$streams = array();
	for ($i = 0; $i < $n; $i++) {
		if ($off >= $nMeta) {
			return null;
		}
		$nl = ord($meta[$off]);
		$off += 1;
		if ($nl < 1 || $off + $nl > $nMeta) {
			return null;
		}
		$name = substr($meta, $off, $nl);
		$off += $nl;
		if ($off >= $nMeta) {
			return null;
		}
		$nItems = ord($meta[$off]);
		$off += 1;
		$ranges = array();
		if ($ver === "\x01") {
			// v1: flat (u32 off + u32 len)×nItems
			if ($nItems < 1 || $off + ($nItems * 8) > $nMeta) {
				return null;
			}
			for ($r = 0; $r < $nItems; $r++) {
				$o = unpack('V', substr($meta, $off, 4));
				$l = unpack('V', substr($meta, $off + 4, 4));
				$off += 8;
				$ranges[] = array(is_array($o) ? (int) $o[1] : -1, is_array($l) ? (int) $l[1] : -1);
			}
		} else {
			for ($r = 0; $r < $nItems; $r++) {
				if ($off >= $nMeta) {
					return null;
				}
				$kind = ord($meta[$off]);
				$off += 1;
				if ($kind === 0) {
					if ($off + 8 > $nMeta) {
						return null;
					}
					$o = unpack('V', substr($meta, $off, 4));
					$l = unpack('V', substr($meta, $off + 4, 4));
					$off += 8;
					$ranges[] = array(is_array($o) ? (int) $o[1] : -1, is_array($l) ? (int) $l[1] : -1);
				} elseif ($kind === 1) {
					if ($off + 10 > $nMeta) {
						return null;
					}
					$o = unpack('V', substr($meta, $off, 4));
					$l = unpack('V', substr($meta, $off + 4, 4));
					$c = unpack('v', substr($meta, $off + 8, 2));
					$off += 10;
					$start = is_array($o) ? (int) $o[1] : -1;
					$blen = is_array($l) ? (int) $l[1] : -1;
					$count = is_array($c) ? (int) $c[1] : -1;
					if ($start < 0 || $blen < 1 || $count < 2) {
						return null;
					}
					for ($k = 0; $k < $count; $k++) {
						$ranges[] = array($start + ($k * $blen), $blen);
					}
				} else {
					return null;
				}
			}
		}
		if ($ranges === array()) {
			return null;
		}
		$safe = sprintf('%03d_%s', $i, preg_replace('/[^A-Za-z0-9._+-]+/', '_', $name) ?: 'stream');
		$streams[] = array('name' => $name, 'safe' => $safe, 'ranges' => $ranges);
	}
	if ($off !== $nMeta) {
		return null;
	}
	return array(
		'tmpl_sha' => bin2hex($sha),
		'tmpl_gz' => (($flags & 1) !== 0),
		'streams' => $streams,
	);
}

/**
 * Deterministic Shattered Galaxy .dat: count + (offset,u32 + name,13) index, then payloads.
 * Names truncated/padded to 13 bytes (NUL-terminated when shorter).
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_sg_dat_rebuild(array $payloads): ?string
{
	if ($payloads === []) {
		return null;
	}
	$n = count($payloads);
	$indexBytes = 4 + $n * 17;
	$offset = $indexBytes;
	$index = pack('V', $n);
	$blob = '';
	foreach ($payloads as $p) {
		$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
		$name = basename($name);
		$data = (string) ($p['data'] ?? '');
		if ($name === '' || str_contains($name, '..')) {
			return null;
		}
		$nameField = substr($name, 0, 12);
		$nameField = str_pad($nameField, 13, "\0");
		$index .= pack('V', $offset) . $nameField;
		$blob .= $data;
		$offset += strlen($data);
	}
	return $index . $blob;
}

/**
 * List SG .dat members (preserves stored name case; size from next offset / EOF).
 *
 * @return list<array{name: string, data: string}>|null
 */
function fractal_zip_classic_sg_dat_list_from_bytes(string $diskBytes): ?array
{
	$n = strlen($diskBytes);
	if ($n < 4 + 17) {
		return null;
	}
	$count = unpack('V', substr($diskBytes, 0, 4))[1];
	if ($count < 1 || $count > 100000) {
		return null;
	}
	$indexEnd = 4 + $count * 17;
	if ($indexEnd > $n) {
		return null;
	}
	$entries = [];
	for ($i = 0; $i < $count; $i++) {
		$base = 4 + $i * 17;
		$offset = unpack('V', substr($diskBytes, $base, 4))[1];
		$rawName = substr($diskBytes, $base + 4, 13);
		$null = strpos($rawName, "\0");
		$name = $null === false ? $rawName : substr($rawName, 0, $null);
		$name = str_replace('\\', '/', $name);
		if ($name === '' || str_contains($name, '..')) {
			return null;
		}
		$entries[] = ['name' => $name, 'offset' => $offset];
	}
	usort($entries, static fn(array $a, array $b): int => $a['offset'] <=> $b['offset']);
	$out = [];
	for ($i = 0; $i < count($entries); $i++) {
		$offset = (int) $entries[$i]['offset'];
		$end = isset($entries[$i + 1]) ? (int) $entries[$i + 1]['offset'] : $n;
		if ($offset < $indexEnd || $end < $offset || $end > $n) {
			return null;
		}
		$out[] = [
			'name' => (string) $entries[$i]['name'],
			'data' => substr($diskBytes, $offset, $end - $offset),
		];
	}
	return $out;
}

/**
 * Rebuild RIFF (LE) or IFF FORM (BE) from flat hex ids or nest1 plan.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_rebuild_riff_family(string $meta, array $payloads, bool $iffBe): ?string
{
	$parts = explode(':', $meta, 4);
	if (count($parts) < 3) {
		return null;
	}
	$magic = $parts[0];
	$form = $parts[1];
	if ($iffBe) {
		if ($magic !== 'FORM' || strlen($form) !== 4) {
			return null;
		}
	} elseif (!in_array($magic, ['RIFF', 'RF64', 'BW64'], true) || strlen($form) !== 4) {
		return null;
	}
	$packSize = static function (int $n) use ($iffBe): string {
		return $iffBe ? pack('N', $n) : pack('V', $n);
	};
	$emitChunk = static function (string $id, string $data) use ($packSize): string {
		$chunk = $id . $packSize(strlen($data)) . $data;
		if (strlen($data) & 1) {
			$chunk .= "\0";
		}
		return $chunk;
	};

	if (($parts[2] ?? '') === 'nest1') {
		$hexPlan = $parts[3] ?? '';
		if ($hexPlan === '' || (strlen($hexPlan) & 1) !== 0 || !ctype_xdigit($hexPlan)) {
			return null;
		}
		$planBin = hex2bin($hexPlan);
		if ($planBin === false || $planBin === '') {
			return null;
		}
		$plan = [];
		$o = 0;
		$plen = strlen($planBin);
		while ($o < $plen) {
			$tag = $planBin[$o];
			if ($tag === 'C') {
				if ($o + 5 > $plen) {
					return null;
				}
				$plan[] = ['C', substr($planBin, $o + 1, 4), '', 0];
				$o += 5;
			} elseif ($tag === 'L') {
				if ($o + 11 > $plen) {
					return null;
				}
				$container = substr($planBin, $o + 1, 4);
				$listType = substr($planBin, $o + 5, 4);
				$nCh = unpack('nch', substr($planBin, $o + 9, 2));
				if ($nCh === false) {
					return null;
				}
				$plan[] = ['L', $container, $listType, (int) $nCh['ch']];
				$o += 11;
			} else {
				return null;
			}
		}
		$payi = 0;
		$pi = 0;
		$nPlan = count($plan);
		$nPay = count($payloads);
		$emitNodes = null;
		$emitNodes = static function (int $count) use (
			&$emitNodes,
			&$pi,
			&$payi,
			$plan,
			$payloads,
			$nPlan,
			$nPay,
			$emitChunk
		): ?string {
			$out = '';
			for ($k = 0; $k < $count; $k++) {
				if ($pi >= $nPlan || $payi >= $nPay) {
					return null;
				}
				$node = $plan[$pi];
				$pi++;
				$data = (string) ($payloads[$payi]['data'] ?? '');
				$payi++;
				if ($node[0] === 'C') {
					$id = (string) $node[1];
					if (strlen($id) !== 4) {
						return null;
					}
					$out .= $emitChunk($id, $data);
				} elseif ($node[0] === 'L') {
					$container = (string) $node[1];
					$listType = (string) $node[2];
					if (strlen($container) !== 4 || strlen($listType) !== 4 || $data !== $listType) {
						return null;
					}
					$body = $emitNodes((int) $node[3]);
					if ($body === null) {
						return null;
					}
					$out .= $emitChunk($container, $listType . $body);
				} else {
					return null;
				}
			}
			return $out;
		};
		$chunks = '';
		while ($pi < $nPlan) {
			$piece = $emitNodes(1);
			if ($piece === null) {
				return null;
			}
			$chunks .= $piece;
		}
		if ($payi !== $nPay) {
			return null;
		}
		return $magic . $packSize(4 + strlen($chunks)) . $form . $chunks;
	}

	$hexIds = $parts[2];
	if (strlen($hexIds) !== count($payloads) * 8 || !ctype_xdigit($hexIds)) {
		return null;
	}
	$chunks = '';
	foreach ($payloads as $i => $p) {
		$id = hex2bin(substr($hexIds, $i * 8, 8));
		if ($id === false || strlen($id) !== 4) {
			return null;
		}
		$data = (string) ($p['data'] ?? '');
		$chunks .= $emitChunk($id, $data);
	}
	return $magic . $packSize(4 + strlen($chunks)) . $form . $chunks;
}

/**
 * Rebuild classic archive bytes from ordered members.
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_rebuild_archive(string $format, string $meta, array $payloads): ?string
{
	if ($payloads === []) {
		return null;
	}
	switch ($format) {
		case 'wad':
			$id = ($meta === 'IWAD' || $meta === 'PWAD') ? $meta : 'PWAD';
			$blob = '';
			$entries = [];
			foreach ($payloads as $p) {
				$entries[] = [
					'name' => (string) ($p['name'] ?? ''),
					'off' => 12 + strlen($blob),
					'data' => (string) ($p['data'] ?? ''),
				];
				$blob .= (string) ($p['data'] ?? '');
			}
			$tableOff = 12 + strlen($blob);
			$dir = '';
			foreach ($entries as $e) {
				$dir .= pack('V', $e['off']) . pack('V', strlen($e['data']))
					. substr(str_pad($e['name'], 8, "\0"), 0, 8);
			}
			return $id . pack('V', count($entries)) . pack('V', $tableOff) . $blob . $dir;
		case 'pak':
			$blob = '';
			$dir = '';
			foreach ($payloads as $p) {
				$off = 12 + strlen($blob);
				$data = (string) ($p['data'] ?? '');
				$blob .= $data;
				$dir .= substr(str_pad((string) ($p['name'] ?? ''), 56, "\0"), 0, 56)
					. pack('V', $off) . pack('V', strlen($data));
			}
			return 'PACK' . pack('V', 12 + strlen($blob)) . pack('V', strlen($dir)) . $blob . $dir;
		case 'grp':
			$n = count($payloads);
			$bin = pack('V', $n);
			$blob = '';
			foreach ($payloads as $p) {
				$data = (string) ($p['data'] ?? '');
				$bin .= pack('V', strlen($data))
					. substr(str_pad((string) ($p['name'] ?? ''), 12, "\0"), 0, 12);
				$blob .= $data;
			}
			return $bin . $blob;
		case 'hog':
			$bin = '';
			foreach ($payloads as $p) {
				$bin .= substr(str_pad((string) ($p['name'] ?? ''), 13, "\0"), 0, 13)
					. pack('V', strlen((string) ($p['data'] ?? '')))
					. (string) ($p['data'] ?? '');
			}
			return $bin;
		case 'big':
			// BIGF/BIG4 little-endian or BIG\0 big-endian — match peel_big_read_index layout.
			$be = ($meta === 'big0' || $meta === "BIG\x00");
			$magic = $be ? "BIG\x00" : (($meta === 'BIG4') ? 'BIG4' : 'BIGF');
			$pack = static function (int $v) use ($be): string {
				return $be ? pack('N', $v) : pack('V', $v);
			};
			$blob = '';
			$dir = '';
			foreach ($payloads as $p) {
				$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
				$data = (string) ($p['data'] ?? '');
				$off = 12 + strlen($blob);
				$blob .= $data;
				$dir .= $pack($off) . $pack(strlen($data)) . $name . "\0";
				$pad = (strlen($name) + 1) % 4;
				if ($pad !== 0) {
					$dir .= str_repeat("\0", 4 - $pad);
				}
			}
			$tableOff = 12 + strlen($blob);
			return $magic . $pack(count($payloads)) . $pack($tableOff) . $blob . $dir;
		case 'pbo':
			// peel_pbo_read_index layout: (name\0 mime\0 u32 size)* + \0 + contiguous payloads.
			// mime is not stored in FZHR; empty mime keeps rebuild bit-exact for our builder.
			$header = '';
			$blob = '';
			foreach ($payloads as $p) {
				$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
				$data = (string) ($p['data'] ?? '');
				if ($name === '') {
					continue;
				}
				$header .= $name . "\0\0" . pack('V', strlen($data));
				$blob .= $data;
			}
			return $header . "\0" . $blob;
		case 'bz2':
			if (count($payloads) < 1) {
				return null;
			}
			$lvl = (int) $meta;
			if ($lvl < 1 || $lvl > 9) {
				$lvl = 9;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			if (function_exists('bzcompress')) {
				$out = @bzcompress($data, $lvl);
				if (is_string($out) && $out !== '') {
					return $out;
				}
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (function_exists('fractal_zip_folder_bzip2_shell_compress')) {
				return fractal_zip_folder_bzip2_shell_compress($data, $lvl);
			}
			return null;
		case 'xz':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_xz_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_xz_shell_compress($data, (int) $meta);
		case 'lzip':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_lzip_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_lzip_shell_compress($data, max(0, min(9, (int) $meta)));
		case 'lzma':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_lzma_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_lzma_shell_compress($data, (int) $meta);
		case 'zstd':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_zstd_shell_compress')) {
				return null;
			}
			$noCheck = str_ends_with($meta, 'n');
			$lvl = (int) rtrim($meta, 'n');
			return fractal_zip_folder_zstd_shell_compress($data, $lvl, $noCheck);
		case 'lz4':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_lz4_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_lz4_shell_compress($data, (int) $meta);
		case 'br':
		case 'brotli':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_brotli_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_brotli_shell_compress($data, (int) $meta);
		case 'gzip':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lvl = max(1, min(9, (int) $meta));
			$c = @gzencode($data, $lvl);
			return is_string($c) && $c !== '' ? $c : null;
		case 'targz':
			// Nested: classic ustar members → gzencode(level).
			$tar = fractal_zip_classic_tar_ustar_rebuild($payloads);
			if ($tar === null) {
				return null;
			}
			$lvl = max(1, min(9, (int) $meta));
			$c = @gzencode($tar, $lvl);
			return is_string($c) && $c !== '' ? $c : null;
		case 'tarbz2':
			$tar = fractal_zip_classic_tar_ustar_rebuild($payloads);
			if ($tar === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_bzip2_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_bzip2_shell_compress($tar, max(1, min(9, (int) $meta)));
		case 'cpiogz':
			$cpio = fractal_zip_classic_cpio_newc_rebuild($payloads);
			if ($cpio === null) {
				return null;
			}
			$lvl = max(1, min(9, (int) $meta));
			$c = @gzencode($cpio, $lvl);
			return is_string($c) && $c !== '' ? $c : null;
		case 'cpioxz':
			$cpio = fractal_zip_classic_cpio_newc_rebuild($payloads);
			if ($cpio === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_xz_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_xz_shell_compress($cpio, (int) $meta);
		case 'cpiozst':
			$cpio = fractal_zip_classic_cpio_newc_rebuild($payloads);
			if ($cpio === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_zstd_shell_compress')) {
				return null;
			}
			$noCheck = str_ends_with($meta, 'n');
			$lvl = (int) rtrim($meta, 'n');
			return fractal_zip_folder_zstd_shell_compress($cpio, $lvl, $noCheck);
		case 'cpiobz2':
			$cpio = fractal_zip_classic_cpio_newc_rebuild($payloads);
			if ($cpio === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_bzip2_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_bzip2_shell_compress($cpio, max(1, min(9, (int) $meta)));
		case 'tarxz':
			$tar = fractal_zip_classic_tar_ustar_rebuild($payloads);
			if ($tar === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_xz_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_xz_shell_compress($tar, (int) $meta);
		case 'tarzst':
			$tar = fractal_zip_classic_tar_ustar_rebuild($payloads);
			if ($tar === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_zstd_shell_compress')) {
				return null;
			}
			$noCheck = str_ends_with($meta, 'n');
			$lvl = (int) rtrim($meta, 'n');
			return fractal_zip_folder_zstd_shell_compress($tar, $lvl, $noCheck);
		case 'tarlz4':
			$tar = fractal_zip_classic_tar_ustar_rebuild($payloads);
			if ($tar === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_lz4_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_lz4_shell_compress($tar, max(1, min(12, (int) $meta)));
		case 'tarlz':
			$tar = fractal_zip_classic_tar_ustar_rebuild($payloads);
			if ($tar === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_lzip_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_lzip_shell_compress($tar, max(0, min(9, (int) $meta)));
		case 'tarbr':
			$tar = fractal_zip_classic_tar_ustar_rebuild($payloads);
			if ($tar === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_brotli_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_brotli_shell_compress($tar, max(0, min(11, (int) $meta)));
		case 'cpiolz4':
			$cpio = fractal_zip_classic_cpio_newc_rebuild($payloads);
			if ($cpio === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_lz4_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_lz4_shell_compress($cpio, max(1, min(12, (int) $meta)));
		case 'cpiolz':
			$cpio = fractal_zip_classic_cpio_newc_rebuild($payloads);
			if ($cpio === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_lzip_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_lzip_shell_compress($cpio, max(0, min(9, (int) $meta)));
		case 'cpiobr':
			$cpio = fractal_zip_classic_cpio_newc_rebuild($payloads);
			if ($cpio === null) {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_brotli_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_brotli_shell_compress($cpio, max(0, min(11, (int) $meta)));
		case 'midi':
		case 'midibz2':
		case 'midixz':
		case 'midizstd':
		case 'midilz4':
		case 'midilz':
		case 'midibr':
		case 'midilzma':
		case 'midigz':
			// SMF: ordered MThd then MTrk* (logical names contain MThd / MTrk).
			$bin = '';
			$sawHdr = false;
			foreach ($payloads as $p) {
				$name = basename(str_replace('\\', '/', (string) ($p['name'] ?? '')));
				$data = (string) ($p['data'] ?? '');
				if (str_contains($name, 'MThd')) {
					$bin .= 'MThd' . pack('N', strlen($data)) . $data;
					$sawHdr = true;
				} elseif (str_contains($name, 'MTrk')) {
					$bin .= 'MTrk' . pack('N', strlen($data)) . $data;
				} else {
					return null;
				}
			}
			if (!$sawHdr || $bin === '') {
				return null;
			}
			if ($format === 'midi') {
				return $bin;
			}
			$wrap = match ($format) {
				'midibz2' => 'bz2',
				'midixz' => 'xz',
				'midizstd' => 'zstd',
				'midilz4' => 'lz4',
				'midilz' => 'lzip',
				'midibr' => 'br',
				'midilzma' => 'lzma',
				'midigz' => 'gzip',
				default => null,
			};
			if ($wrap === null) {
				return null;
			}
			return fractal_zip_classic_rebuild_archive($wrap, $meta, array(
				array('name' => 'payload.mid', 'data' => $bin),
			));
		case 'riff':
			// Flat: meta = "MAGIC:FORM:hex4cc…". Nested: "MAGIC:FORM:nest1:<hex plan>".
			// Payloads are chunk bodies (and LIST type tokens for nest1); no form marker member.
			return fractal_zip_classic_rebuild_riff_family($meta, $payloads, false);
		case 'iff':
			// Flat IFF FORM (big-endian). Nested FORM/LIST/CAT via nest1 plan.
			return fractal_zip_classic_rebuild_riff_family($meta, $payloads, true);
		case 'eml':
			// headers + separator (meta) + body. Exactly two payloads in order.
			if (count($payloads) !== 2) {
				return null;
			}
			$sep = (string) $meta;
			if ($sep !== "\n\n" && $sep !== "\r\n\r\n") {
				return null;
			}
			return (string) ($payloads[0]['data'] ?? '') . $sep . (string) ($payloads[1]['data'] ?? '');
		case 'sqlitepages':
			// meta = page size; payloads are full pages in order.
			$pageSize = (int) $meta;
			if (!in_array($pageSize, array(512, 1024, 2048, 4096, 8192, 16384, 32768, 65536), true)) {
				return null;
			}
			$out = '';
			foreach ($payloads as $p) {
				$page = (string) ($p['data'] ?? '');
				if (strlen($page) !== $pageSize) {
					return null;
				}
				$out .= $page;
			}
			return $out === '' ? null : $out;
		case 'pe_rsrc':
			// meta: "PE1R" + u32 rsrcOff + u32 rsrcSize; payloads before, rsrc, [after].
			if (strlen($meta) < 12 || substr($meta, 0, 4) !== 'PE1R') {
				return null;
			}
			$roff = unpack('V', substr($meta, 4, 4))[1];
			$rsize = unpack('V', substr($meta, 8, 4))[1];
			$by = array();
			foreach ($payloads as $p) {
				$by[(string) ($p['name'] ?? '')] = (string) ($p['data'] ?? '');
			}
			if (!isset($by['before'], $by['rsrc'])) {
				return null;
			}
			if (strlen($by['before']) !== $roff || strlen($by['rsrc']) !== $rsize) {
				return null;
			}
			return $by['before'] . $by['rsrc'] . (string) ($by['after'] ?? '');
		case 'icns':
			// Payloads are full type/size icon entries in order; meta unused.
			$blob = '';
			foreach ($payloads as $p) {
				$chunk = (string) ($p['data'] ?? '');
				if (strlen($chunk) < 8) {
					return null;
				}
				$sz = unpack('N', substr($chunk, 4, 4))[1];
				if ($sz !== strlen($chunk)) {
					return null;
				}
				$blob .= $chunk;
			}
			if ($blob === '') {
				return null;
			}
			return 'icns' . pack('N', 8 + strlen($blob)) . $blob;
		case 'ipynb':
			// meta: decimal json_encode flags + "\0" + top-level keys joined by "\0".
			// Payloads: non-cell keys by name; cells as cell_0000… in order.
			// Decode as objects so `{}` round-trips (assoc arrays would become `[]`).
			$nul = strpos($meta, "\0");
			if ($nul === false) {
				return null;
			}
			$flags = (int) substr($meta, 0, $nul);
			$keys = substr($meta, $nul + 1) === ''
				? array()
				: explode("\0", substr($meta, $nul + 1));
			if ($keys === array()) {
				return null;
			}
			$by = array();
			$cells = array();
			foreach ($payloads as $p) {
				$k = (string) ($p['name'] ?? '');
				$raw = (string) ($p['data'] ?? '');
				if (preg_match('/^cell_(\d{4})$/', $k, $m)) {
					$cells[(int) $m[1]] = $raw;
					continue;
				}
				$by[$k] = $raw;
			}
			ksort($cells, SORT_NUMERIC);
			$obj = new stdClass();
			foreach ($keys as $key) {
				if ($key === 'cells') {
					$arr = array();
					foreach ($cells as $raw) {
						$dec = json_decode($raw);
						if (json_last_error() !== JSON_ERROR_NONE) {
							return null;
						}
						$arr[] = $dec;
					}
					$obj->cells = $arr;
					continue;
				}
				if (!isset($by[$key])) {
					return null;
				}
				$dec = json_decode($by[$key]);
				if (json_last_error() !== JSON_ERROR_NONE) {
					return null;
				}
				$obj->{$key} = $dec;
			}
			$out = json_encode($obj, $flags);
			return is_string($out) ? $out : null;
		case 'isospare':
			// meta: "ISO1" + u16 pageSize + u16 pageCount + bitmap; payloads = nonzero sectors in order.
			if (strlen($meta) < 8 || substr($meta, 0, 4) !== 'ISO1') {
				return null;
			}
			$ps = unpack('v', substr($meta, 4, 2));
			$pc = unpack('v', substr($meta, 6, 2));
			$pageSize = is_array($ps) ? (int) $ps[1] : -1;
			$pageCount = is_array($pc) ? (int) $pc[1] : -1;
			if ($pageSize !== 2048 || $pageCount < 1 || $pageCount > 65535) {
				return null;
			}
			$nb = (int) ceil($pageCount / 8);
			if (strlen($meta) !== 8 + $nb) {
				return null;
			}
			$bm = substr($meta, 8, $nb);
			$out = '';
			$pi = 0;
			for ($i = 0; $i < $pageCount; $i++) {
				$bit = (ord($bm[$i >> 3]) >> ($i & 7)) & 1;
				if ($bit) {
					if ($pi >= count($payloads)) {
						return null;
					}
					$page = (string) ($payloads[$pi]['data'] ?? '');
					$pi++;
					if (strlen($page) !== $pageSize) {
						return null;
					}
					$out .= $page;
				} else {
					$out .= str_repeat("\0", $pageSize);
				}
			}
			if ($pi !== count($payloads)) {
				return null;
			}
			return $out;
		case 'cab':
			// meta = mx level; rebuild via 7z a -tcab -mx=N (often unsupported).
			$bin = trim((string) shell_exec('command -v 7z 2>/dev/null'));
			if ($bin === '') {
				$bin = trim((string) shell_exec('command -v 7za 2>/dev/null'));
			}
			if ($bin === '') {
				return null;
			}
			$mx = max(0, min(9, (int) $meta));
			$tmp = tempnam(sys_get_temp_dir(), 'fzcab_');
			if ($tmp === false) {
				return null;
			}
			$dir = $tmp . '_d';
			@unlink($tmp);
			@mkdir($dir, 0700, true);
			foreach ($payloads as $p) {
				$nm = str_replace('\\', '/', (string) ($p['name'] ?? ''));
				$nm = ltrim($nm, '/');
				if ($nm === '' || str_contains($nm, '..')) {
					continue;
				}
				$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nm);
				$pd = dirname($path);
				if (!is_dir($pd)) {
					@mkdir($pd, 0700, true);
				}
				@file_put_contents($path, (string) ($p['data'] ?? ''));
			}
			$arc = $tmp . '.cab';
			@unlink($arc);
			$cmd = escapeshellarg($bin) . ' a -tcab -mx=' . (int) $mx . ' '
				. escapeshellarg($arc) . ' ' . escapeshellarg($dir . DIRECTORY_SEPARATOR . '*')
				. ' 2>/dev/null';
			shell_exec($cmd);
			$blob = is_file($arc) ? (string) file_get_contents($arc) : '';
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
			$rm($dir);
			@unlink($arc);
			if ($blob === '' || substr($blob, 0, 4) !== 'MSCF') {
				return null;
			}
			return $blob;
		case 'json':
			// Top-level object/array: payloads are JSON-encoded values; names are keys (order matters).
			// meta: decimal json_encode flags (e.g. "320" = UNESCAPED_SLASHES|UNESCAPED_UNICODE).
			$flags = (int) $meta;
			$obj = array();
			$isList = true;
			$i = 0;
			foreach ($payloads as $p) {
				$k = (string) ($p['name'] ?? '');
				$raw = (string) ($p['data'] ?? '');
				$dec = json_decode($raw, true);
				if (json_last_error() !== JSON_ERROR_NONE) {
					return null;
				}
				$obj[$k] = $dec;
				if ($k !== (string) $i) {
					$isList = false;
				}
				$i++;
			}
			if ($isList && $obj !== array()) {
				$obj = array_values($obj);
			}
			$out = json_encode($obj, $flags);
			return is_string($out) ? $out : null;
		case 'csv':
			// header + eol (meta) + body. Exactly two payloads.
			if (count($payloads) !== 2) {
				return null;
			}
			$eol = (string) $meta;
			if ($eol !== "\n" && $eol !== "\r\n") {
				return null;
			}
			return (string) ($payloads[0]['data'] ?? '') . $eol . (string) ($payloads[1]['data'] ?? '');
		case 'csvcols':
			// Columnar CSV/TSV: meta = eol + sep (e.g. "\n," or "\n\t"). Payload0 = header;
			// rest = per-column values joined by "\n". No quoted fields.
			$meta = (string) $meta;
			if (str_starts_with($meta, "\r\n")) {
				$eol = "\r\n";
				$sep = substr($meta, 2);
			} elseif (str_starts_with($meta, "\n")) {
				$eol = "\n";
				$sep = substr($meta, 1);
			} else {
				return null;
			}
			if ($sep === '' || count($payloads) < 2) {
				return null;
			}
			$header = (string) ($payloads[0]['data'] ?? '');
			$cols = array();
			$rows = null;
			for ($i = 1; $i < count($payloads); $i++) {
				$blob = (string) ($payloads[$i]['data'] ?? '');
				$vals = ($blob === '') ? array() : explode("\n", $blob);
				if ($rows === null) {
					$rows = count($vals);
				} elseif (count($vals) !== $rows) {
					return null;
				}
				$cols[] = $vals;
			}
			if ($rows === null || count($cols) < 1) {
				return null;
			}
			$out = $header . $eol;
			for ($r = 0; $r < $rows; $r++) {
				$row = array();
				foreach ($cols as $c) {
					$row[] = $c[$r];
				}
				$out .= implode($sep, $row) . $eol;
			}
			return $out;
		case 'ini':
			// Exact section blobs in order; meta unused. Rebuild = concatenation.
			$out = '';
			foreach ($payloads as $p) {
				$out .= (string) ($p['data'] ?? '');
			}
			return $out;
		case 'lines':
			// Line payloads joined by eol in meta ("\n" or "\r\n").
			$eol = (string) $meta;
			if ($eol !== "\n" && $eol !== "\r\n") {
				return null;
			}
			$parts = array();
			foreach ($payloads as $p) {
				$parts[] = (string) ($p['data'] ?? '');
			}
			return implode($eol, $parts);
		case 'woff2':
			if (count($payloads) < 1) {
				return null;
			}
			$data = (string) ($payloads[0]['data'] ?? '');
			if ($data === '') {
				return null;
			}
			$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
			if (is_readable($lb)) {
				require_once $lb;
			}
			if (!function_exists('fractal_zip_folder_woff2_shell_compress')) {
				return null;
			}
			return fractal_zip_folder_woff2_shell_compress($data);
		case 'rpm':
			// meta = "codec:level:prefix_sha" or "codec:level:gz:prefix_sha"
			// payloads include _rpm_prefix (+ optional gzip) + cpio members.
			$parts = explode(':', $meta);
			if (count($parts) !== 3 && count($parts) !== 4) {
				return null;
			}
			$codec = $parts[0];
			$lvlMeta = $parts[1];
			$prefixGz = (count($parts) === 4 && $parts[2] === 'gz');
			$prefix = null;
			$cpioPayloads = array();
			foreach ($payloads as $p) {
				$nm = str_replace('\\', '/', (string) ($p['name'] ?? ''));
				$nm = ltrim($nm, '/');
				$data = (string) ($p['data'] ?? '');
				if ($nm === '_rpm_prefix' || str_starts_with($nm, '__rpm_prefix/')) {
					$prefix = $data;
					continue;
				}
				if (preg_match('#^[^/]+/(.+)$#', $nm, $mm) && !str_starts_with($nm, '__')) {
					$nm = $mm[1];
				}
				$cpioPayloads[] = array('name' => $nm, 'data' => $data);
			}
			if ($prefix === null || $prefix === '' || $cpioPayloads === array()) {
				return null;
			}
			if ($prefixGz) {
				if (!function_exists('gzuncompress')) {
					return null;
				}
				$raw = @gzuncompress($prefix);
				if (!is_string($raw) || $raw === '') {
					return null;
				}
				$prefix = $raw;
			}
			$cpio = fractal_zip_classic_cpio_newc_rebuild($cpioPayloads);
			if ($cpio === null) {
				return null;
			}
			$wrapFmt = ($codec === 'gzip') ? 'gzip' : $codec;
			$wrapped = fractal_zip_classic_rebuild_archive($wrapFmt, $lvlMeta, array(
				array('name' => 'payload.cpio', 'data' => $cpio),
			));
			if ($wrapped === null) {
				return null;
			}
			return $prefix . $wrapped;
		case '7z':
			// meta = mx level string; rebuild via 7z a -t7z -mx=N
			$bin = trim((string) shell_exec('command -v 7z 2>/dev/null'));
			if ($bin === '') {
				$bin = trim((string) shell_exec('command -v 7za 2>/dev/null'));
			}
			if ($bin === '') {
				return null;
			}
			$mx = max(0, min(9, (int) $meta));
			$tmp = tempnam(sys_get_temp_dir(), 'fz7zb_');
			if ($tmp === false) {
				return null;
			}
			$dir = $tmp . '_d';
			@unlink($tmp);
			@mkdir($dir, 0700, true);
			foreach ($payloads as $p) {
				$nm = str_replace('\\', '/', (string) ($p['name'] ?? ''));
				$nm = ltrim($nm, '/');
				if ($nm === '' || str_contains($nm, '..')) {
					continue;
				}
				$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $nm);
				$pd = dirname($path);
				if (!is_dir($pd)) {
					@mkdir($pd, 0700, true);
				}
				@file_put_contents($path, (string) ($p['data'] ?? ''));
			}
			$arc = $tmp . '.7z';
			// -mtm=off required for bit-exact self-rebuild (mtime otherwise dirties header CRC).
			$cmd = 'cd ' . escapeshellarg($dir) . ' && ' . escapeshellarg($bin)
				. ' a -t7z -mx=' . $mx . ' -mmt=off -mtc=off -mta=off -mtm=off -y '
				. escapeshellarg($arc) . ' . 2>/dev/null';
			shell_exec($cmd);
			$blob = is_file($arc) ? (string) file_get_contents($arc) : '';
			$rm = static function (string $d) use (&$rm): void {
				if (!is_dir($d)) {
					return;
				}
				foreach (scandir($d) ?: array() as $e) {
					if ($e === '.' || $e === '..') {
						continue;
					}
					$p = $d . DIRECTORY_SEPARATOR . $e;
					is_dir($p) ? $rm($p) : @unlink($p);
				}
				@rmdir($d);
			};
			$rm($dir);
			@unlink($arc);
			if ($blob === '' || substr($blob, 0, 6) !== "7z\xbc\xaf\x27\x1c") {
				return null;
			}
			return $blob;
		case 'jpegseg':
			// SOI + ordered marker/tail segment bodies (no SOI in members).
			$out = "\xFF\xD8";
			foreach ($payloads as $p) {
				$out .= (string) ($p['data'] ?? '');
			}
			return $out;
		case 'fp':
		case 'fppatch':
			// Sibling near-duplicate: payloads[0] = base bytes; meta = FP patch (empty = alias).
			if ($payloads === array()) {
				return null;
			}
			$base = (string) ($payloads[0]['data'] ?? '');
			if ($meta === '') {
				return $base;
			}
			if (!function_exists('fractal_zip_apply_minimal_patch_bytes')) {
				$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
				if (is_readable($lb)) {
					require_once $lb;
				}
			}
			if (!function_exists('fractal_zip_apply_minimal_patch_bytes')) {
				return null;
			}
			$out = fractal_zip_apply_minimal_patch_bytes($base, (string) $meta);
			return is_string($out) ? $out : null;
		case 'ole':
			// CFB template + stream payloads.
			// meta FZOL\x01 binary (preferred), or JSON v2/v1 legacy.
			$ole = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_ole_cfb.php';
			if (is_readable($ole)) {
				require_once $ole;
			}
			if (!function_exists('fractal_zip_ole_rebuild_from_template')) {
				return null;
			}
			$template = null;
			$bySafe = array();
			$orderedStreams = array(); // content-addressed `__ole_stream/<sha>` in meta order
			foreach ($payloads as $p) {
				$nm = str_replace('\\', '/', (string) ($p['name'] ?? ''));
				$nm = ltrim($nm, '/');
				$data = (string) ($p['data'] ?? '');
				if ($nm === '_ole_template' || str_ends_with($nm, '/_ole_template')
					|| str_starts_with($nm, '__ole_tmpl/')) {
					$template = $data;
					continue;
				}
				if (str_starts_with($nm, '__ole_stream/')) {
					$orderedStreams[] = $data;
				}
				$base = basename($nm);
				$bySafe[$nm] = $data;
				$bySafe[$base] = $data;
			}
			$fzol = null;
			if (is_string($meta) && str_starts_with($meta, 'FZOL')
				&& function_exists('fractal_zip_classic_ole_fzol_decode')) {
				$fzol = fractal_zip_classic_ole_fzol_decode($meta);
			}
			if (is_array($fzol)) {
				$decoded = $fzol;
				$spec = $fzol['streams'];
				if ($template !== null && !empty($fzol['tmpl_gz']) && function_exists('gzuncompress')) {
					$raw = @gzuncompress($template);
					if (!is_string($raw) || $raw === '') {
						return null;
					}
					$template = $raw;
				}
				if ($template === null && isset($fzol['tmpl_sha']) && is_string($fzol['tmpl_sha'])) {
					$want = '__ole_tmpl/' . $fzol['tmpl_sha'];
					foreach ($payloads as $p) {
						$nm = str_replace('\\', '/', (string) ($p['name'] ?? ''));
						$nm = ltrim($nm, '/');
						if ($nm === $want || basename($nm) === $fzol['tmpl_sha']) {
							$template = (string) ($p['data'] ?? '');
							if (!empty($fzol['tmpl_gz']) && function_exists('gzuncompress')) {
								$raw = @gzuncompress($template);
								if (!is_string($raw) || $raw === '') {
									return null;
								}
								$template = $raw;
							}
							break;
						}
					}
				}
			} else {
				$decoded = json_decode($meta, true);
				if (!is_array($decoded) || $decoded === array()) {
					return null;
				}
				$spec = $decoded;
				if (isset($decoded['streams']) && is_array($decoded['streams'])) {
					$spec = $decoded['streams'];
					if ($template === null) {
						// Content-addressed shared member `__ole_tmpl/<sha>` or legacy b64.
						if (isset($decoded['tmpl_sha']) && is_string($decoded['tmpl_sha']) && $decoded['tmpl_sha'] !== '') {
							$want = '__ole_tmpl/' . $decoded['tmpl_sha'];
							foreach ($payloads as $p) {
								$nm = str_replace('\\', '/', (string) ($p['name'] ?? ''));
								$nm = ltrim($nm, '/');
								if ($nm === $want || $nm === '_ole_template' || str_ends_with($nm, '/_ole_template')
									|| basename($nm) === $decoded['tmpl_sha']) {
									$template = (string) ($p['data'] ?? '');
									break;
								}
							}
						} elseif (isset($decoded['tmpl']) && is_string($decoded['tmpl']) && $decoded['tmpl'] !== '') {
							$bin = base64_decode($decoded['tmpl'], true);
							if (!is_string($bin) || $bin === '') {
								return null;
							}
							$template = $bin;
						}
					}
				}
			}
			if ($template === null || $template === '') {
				return null;
			}
			$streams = array();
			$si = 0;
			foreach ($spec as $i => $entry) {
				if (!is_array($entry)) {
					return null;
				}
				$name = str_replace('\\', '/', (string) ($entry['name'] ?? ''));
				$safe = (string) ($entry['safe'] ?? '');
				$ranges = $entry['ranges'] ?? null;
				if ($name === '' || !is_array($ranges) || $ranges === array()) {
					return null;
				}
				$key = $safe !== '' ? $safe : sprintf('%03d_%s', $i, preg_replace('/[^A-Za-z0-9._+-]+/', '_', $name) ?: 'stream');
				$data = null;
				if (isset($bySafe[$key])) {
					$data = $bySafe[$key];
				} elseif (isset($orderedStreams[$si])) {
					$data = $orderedStreams[$si];
				}
				if ($data === null) {
					return null;
				}
				$si++;
				$streams[] = array(
					'name' => $name,
					'data' => $data,
					'ranges' => $ranges,
				);
			}
			if ($streams === array()) {
				return null;
			}
			return fractal_zip_ole_rebuild_from_template($template, $streams);
		case 'ar':
			if (is_string($meta) && str_starts_with($meta, 'FZAH')) {
				return fractal_zip_classic_ar_rebuild_from_fzah($payloads, $meta);
			}
			// Deterministic GNU ar: mtime/uid/gid=0, mode=100644, GNU trailing slash names.
			$bin = "!<arch>\n";
			foreach ($payloads as $p) {
				$name = str_replace('\\', '/', (string) ($p['name'] ?? ''));
				$data = (string) ($p['data'] ?? '');
				if ($name === '') {
					continue;
				}
				$gnuName = $name;
				if (!str_ends_with($gnuName, '/')) {
					$gnuName .= '/';
				}
				if (strlen($gnuName) > 16) {
					return null; // long names need string table — skip CLASSIC
				}
				$hdr = str_pad($gnuName, 16, ' ');
				$hdr .= str_pad('0', 12, ' '); // mtime left-aligned
				$hdr .= str_pad('0', 6, ' ');
				$hdr .= str_pad('0', 6, ' ');
				$hdr .= str_pad('100644', 8, ' ');
				$hdr .= str_pad((string) strlen($data), 10, ' ');
				$hdr .= "`\n";
				if (strlen($hdr) !== 60) {
					return null;
				}
				$bin .= $hdr . $data;
				if ((strlen($data) % 2) === 1) {
					$bin .= "\n";
				}
			}
			return $bin;
		case 'cpio':
			if (is_string($meta) && str_starts_with($meta, 'FZCH')) {
				return fractal_zip_classic_cpio_rebuild_from_fzch($payloads, $meta);
			}
			// Deterministic newc (070701): uid/gid/mtime/devs=0, mode=0100644, sequential ino.
			return fractal_zip_classic_cpio_newc_rebuild($payloads);
		case 'tar':
			if (is_string($meta) && str_starts_with($meta, 'FZTH')) {
				return fractal_zip_classic_tar_rebuild_from_fzth($payloads, $meta);
			}
			return fractal_zip_classic_tar_ustar_rebuild($payloads);
		case 'sg_dat':
			return fractal_zip_classic_sg_dat_rebuild($payloads);
		case 'deb':
			// Nested classic: debian-binary + control/* + data/* → ar(control.tar, data.tar).
			return fractal_zip_classic_deb_rebuild($payloads);
		case 'debgz':
			return fractal_zip_classic_debgz_rebuild($payloads, $meta);
		case 'debxz':
			return fractal_zip_classic_debxz_rebuild($payloads, $meta);
		case 'debzst':
			return fractal_zip_classic_debzst_rebuild($payloads, $meta);
		case 'debbr':
			return fractal_zip_classic_debbr_rebuild($payloads, $meta);
		case 'deblz4':
			return fractal_zip_classic_deblz4_rebuild($payloads, $meta);
		case 'vpk':
			return fractal_zip_classic_vpk_rebuild($payloads, $meta);
		default:
			return null;
	}
}

/**
 * Read a null-terminated C string from $buf at &$pos.
 */
function fractal_zip_classic_vpk_read_cstring(string $buf, int &$pos): string
{
	$start = $pos;
	$n = strlen($buf);
	while ($pos < $n && $buf[$pos] !== "\0") {
		$pos++;
	}
	$s = substr($buf, $start, $pos - $start);
	if ($pos < $n) {
		$pos++;
	}
	return $s;
}

/**
 * List monolithic VPK v1/v2 members (archiveIndex 0x7FFF only).
 *
 * @return list<array{name: string, offset: int, size: int}>|null
 */
function fractal_zip_classic_vpk_list_from_bytes(string $diskBytes): ?array
{
	if (strlen($diskBytes) < 12) {
		return null;
	}
	$sig = unpack('V', substr($diskBytes, 0, 4))[1];
	if ($sig !== 0x55AA1234) {
		return null;
	}
	$version = unpack('V', substr($diskBytes, 4, 4))[1];
	$treeSize = unpack('V', substr($diskBytes, 8, 4))[1];
	$treeStart = 12;
	if ($version === 2) {
		if (strlen($diskBytes) < 24) {
			return null;
		}
		$treeStart = 24;
	} elseif ($version !== 1) {
		return null;
	}
	if ($treeSize < 1 || $treeStart + $treeSize > strlen($diskBytes)) {
		return null;
	}
	$fileDataOff = $treeStart + $treeSize;
	$tree = substr($diskBytes, $treeStart, $treeSize);
	$pos = 0;
	$len = strlen($tree);
	$out = [];
	while ($pos < $len) {
		$ext = fractal_zip_classic_vpk_read_cstring($tree, $pos);
		if ($ext === '') {
			break;
		}
		while ($pos < $len) {
			$path = fractal_zip_classic_vpk_read_cstring($tree, $pos);
			if ($path === '') {
				break;
			}
			while ($pos < $len) {
				$name = fractal_zip_classic_vpk_read_cstring($tree, $pos);
				if ($name === '') {
					break;
				}
				if ($pos + 18 > $len) {
					return null;
				}
				$crc = unpack('V', substr($tree, $pos, 4))[1];
				$preload = unpack('v', substr($tree, $pos + 4, 2))[1];
				$archiveIndex = unpack('v', substr($tree, $pos + 6, 2))[1];
				$offset = unpack('V', substr($tree, $pos + 8, 4))[1];
				$length = unpack('V', substr($tree, $pos + 12, 4))[1];
				$term = unpack('v', substr($tree, $pos + 16, 2))[1];
				$pos += 18;
				if ($term !== 0xFFFF) {
					return null;
				}
				if ($preload > 0) {
					if ($pos + $preload > $len) {
						return null;
					}
					$pos += $preload; // skip preload (not supported for CLASSIC rebuild)
				}
				// Only monolithic inline payloads.
				if ($archiveIndex !== 0x7FFF || $preload !== 0) {
					return null;
				}
				$dir = ($path === ' ') ? '' : $path;
				$base = ($name === ' ') ? '' : $name;
				$extension = ($ext === ' ') ? '' : $ext;
				$rel = $dir !== '' ? ($dir . '/' . $base) : $base;
				if ($extension !== '') {
					$rel .= '.' . $extension;
				}
				if ($rel === '') {
					return null;
				}
				$absOff = $fileDataOff + $offset;
				if ($length < 1 || $absOff + $length > strlen($diskBytes)) {
					return null;
				}
				// CRC check (optional integrity)
				$data = substr($diskBytes, $absOff, $length);
				if ((crc32($data) & 0xffffffff) !== ($crc & 0xffffffff)) {
					return null;
				}
				$out[] = ['name' => $rel, 'offset' => $absOff, 'size' => $length];
			}
		}
	}
	return count($out) >= 2 ? $out : null;
}

/**
 * Rebuild monolithic VPK v1 from ordered members (preload=0, archiveIndex=0x7FFF).
 *
 * @param list<array{name: string, data: string}> $payloads
 */
function fractal_zip_classic_vpk_rebuild(array $payloads, string $meta): ?string
{
	if (count($payloads) < 2) {
		return null;
	}
	$version = (str_starts_with($meta, 'v2')) ? 2 : 1;
	// Group: ext => path => base => data (stable insertion order)
	$tree = [];
	foreach ($payloads as $p) {
		$rel = str_replace('\\', '/', (string) ($p['name'] ?? ''));
		$data = (string) ($p['data'] ?? '');
		if ($rel === '' || $data === '') {
			return null;
		}
		$ext = pathinfo($rel, PATHINFO_EXTENSION);
		$base = pathinfo($rel, PATHINFO_FILENAME);
		$dir = pathinfo($rel, PATHINFO_DIRNAME);
		if ($dir === '.' || $dir === '') {
			$dir = ' ';
		}
		if ($ext === '') {
			$ext = ' ';
		}
		if ($base === '') {
			$base = ' ';
		}
		$tree[$ext][$dir][$base] = $data;
	}
	$payload = '';
	$metaEntries = [];
	foreach ($tree as $ext => $paths) {
		foreach ($paths as $dir => $names) {
			foreach ($names as $base => $data) {
				$key = $ext . "\0" . $dir . "\0" . $base;
				$metaEntries[$key] = [
					'ext' => $ext,
					'dir' => $dir,
					'base' => $base,
					'data' => $data,
					'off' => strlen($payload),
					'len' => strlen($data),
				];
				$payload .= $data;
			}
		}
	}
	$treeBin = '';
	foreach ($tree as $ext => $paths) {
		$treeBin .= $ext . "\0";
		foreach ($paths as $dir => $names) {
			$treeBin .= $dir . "\0";
			foreach ($names as $base => $data) {
				$key = $ext . "\0" . $dir . "\0" . $base;
				$m = $metaEntries[$key];
				$treeBin .= $base . "\0";
				$crc = crc32($data) & 0xffffffff;
				$treeBin .= pack('V', $crc);
				$treeBin .= pack('v', 0);
				$treeBin .= pack('v', 0x7FFF);
				$treeBin .= pack('V', $m['off']);
				$treeBin .= pack('V', $m['len']);
				$treeBin .= pack('v', 0xFFFF);
			}
			$treeBin .= "\0"; // end files
		}
		$treeBin .= "\0"; // end paths
	}
	$treeBin .= "\0"; // end extensions
	if ($version === 2) {
		// v2 header: sig, ver, treeSize, fileDataSectionSize, archiveMD5SectionSize, otherMD5SectionSize, signatureSectionSize
		$fileDataSize = strlen($payload);
		$hdr = pack('V', 0x55AA1234) . pack('V', 2) . pack('V', strlen($treeBin))
			. pack('V', $fileDataSize) . pack('V', 0) . pack('V', 0) . pack('V', 0);
		return $hdr . $treeBin . $payload;
	}
	$hdr = pack('V', 0x55AA1234) . pack('V', 1) . pack('V', strlen($treeBin));
	return $hdr . $treeBin . $payload;
}

/**
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_classic(
	string $diskPath,
	string $diskBytes,
	array &$members,
	array &$restore
): bool {
	if (!fractal_zip_folder_classic_peel_enabled()) {
		return false;
	}
	if (!fractal_zip_classic_peel_looks_native($diskPath, $diskBytes)) {
		return false;
	}
	$fmt = fractal_zip_classic_peel_detect_format($diskPath, $diskBytes);
	if ($fmt === null) {
		return false;
	}
	// Formats we can rebuild losslessly (CLASSIC restore, no verbatim tax).
	// pak/wad/…: allow a single member when the deterministic rebuild is bit-exact
	// (Quake PAK fixtures are often one lump; dir padding still peels cleanly).
	$minMembers = in_array($fmt['format'], ['pak', 'wad', 'grp', 'hog'], true) ? 1 : 2;
	if (!in_array($fmt['format'], ['wad', 'pak', 'grp', 'hog', 'big', 'pbo', 'vpk'], true)) {
		return false;
	}
	$listed = fractal_zip_classic_peel_list_members_from_bytes($diskPath, $diskBytes);
	if ($listed === null || count($listed) < $minMembers) {
		return false;
	}
	$memberNames = [];
	$logicalKeys = [];
	$payloads = [];
	$addedNew = [];
	foreach ($listed as $zm) {
		$name = (string) ($zm['name'] ?? '');
		$data = (string) ($zm['data'] ?? '');
		if ($name === '' || $data === '') {
			continue;
		}
		// Content-address identical lumps across sibling archives (MAP01/02/03…).
		$legacy = rtrim($diskPath, '/') . '/' . ltrim($name, '/');
		$key = fractal_zip_folder_register_shared_payload($members, $data, $name, $legacy, $addedNew);
		$logicalKeys[] = $key;
		$memberNames[] = $key;
		$payloads[] = ['name' => $name, 'data' => $data];
	}
	if (count($memberNames) < $minMembers) {
		foreach ($addedNew as $mn) {
			unset($members[$mn]);
		}
		return false;
	}
	// Tiny single-lump PAK/WAD stubs: PASSTHROUGH of zero-padded dirs compresses
	// better than paying CLASSIC FZHR + forced peel hetero.
	if (count($memberNames) === 1 && in_array($fmt['format'], ['pak', 'wad'], true)) {
		$payloadBytes = strlen((string) ($payloads[0]['data'] ?? ''));
		if ($payloadBytes < 64 && strlen($diskBytes) < 256) {
			foreach ($addedNew as $mn) {
				unset($members[$mn]);
			}
			return false;
		}
	}
	// Verify bit-exact rebuild before committing to CLASSIC restore.
	$rebuilt = fractal_zip_classic_rebuild_archive($fmt['format'], $fmt['meta'], $payloads);
	if ($rebuilt === null || $rebuilt !== $diskBytes) {
		foreach ($addedNew as $mn) {
			unset($members[$mn]);
		}
		return false;
	}
	$restore[$diskPath] = [
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
		'format' => $fmt['format'],
		'meta' => $fmt['meta'],
		'member_names' => $memberNames,
	];
	return true;
}

/**
 * Prefer FZCL v3 (restore sidecar inside unified body — trailer rides outer codec).
 * Fall back to gzip FZHR (v2) or raw FZHR (v1) when called with a body that already
 * lacks the sidecar (legacy helper).
 *
 * Wire:
 *   FZCL\x03 + unified_body   (body contains ".fzcl_restore" = FZHR bytes)
 *   FZCL\x02 + varint(n) + gz(fzhr) + unified_body
 *   FZCL\x01 + varint(n) + fzhr + unified_body
 */
function fractal_zip_classic_encode_fzcl_v1(string $unifiedBody, array $restoreSpecs): string
{
	if ($unifiedBody === '' || $restoreSpecs === []) {
		return '';
	}
	if (!function_exists('fractal_zip_encode_fzhr_v1')) {
		$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
		if (is_readable($lb)) {
			require_once $lb;
		}
	}
	if (!function_exists('fractal_zip_varint_u32')) {
		$pm = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_per_member_best.php';
		if (is_readable($pm)) {
			require_once $pm;
		}
	}
	if (!function_exists('fractal_zip_encode_fzhr_v1') || !function_exists('fractal_zip_varint_u32')) {
		return '';
	}
	$fzhr = fractal_zip_encode_fzhr_v1($restoreSpecs);
	if ($fzhr === '') {
		return '';
	}
	// Caller may already have embedded ".fzcl_restore" in the unified body (v3).
	// Detect via magic only when body is a finished .fz — use external API instead.
	$gz = function_exists('gzcompress') ? @gzcompress($fzhr, 9) : false;
	if (is_string($gz) && $gz !== '' && strlen($gz) < strlen($fzhr)) {
		return 'FZCL' . "\x02" . fractal_zip_varint_u32(strlen($gz)) . $gz . $unifiedBody;
	}
	return 'FZCL' . "\x01" . fractal_zip_varint_u32(strlen($fzhr)) . $fzhr . $unifiedBody;
}

/** FZCL v3: body already contains ".fzcl_restore"; wire is magic + body only. */
function fractal_zip_classic_encode_fzcl_v3(string $unifiedBodyWithSidecar): string
{
	if ($unifiedBodyWithSidecar === '') {
		return '';
	}
	return 'FZCL' . "\x03" . $unifiedBodyWithSidecar;
}

/**
 * Sidecar member name embedded in the peeled unified stream (FZCL v3).
 */
function fractal_zip_classic_fzcl_restore_sidecar_name(): string
{
	return '.fzcl_restore';
}

/**
 * @return array{body: string, restore: array<string,array>, ver: int}|null
 */
function fractal_zip_classic_decode_fzcl_v1(string $blob): ?array
{
	if (strlen($blob) < 6 || substr($blob, 0, 4) !== 'FZCL') {
		return null;
	}
	$ver = $blob[4];
	if ($ver !== "\x01" && $ver !== "\x02" && $ver !== "\x03") {
		return null;
	}
	if ($ver === "\x03") {
		$body = substr($blob, 5);
		if ($body === '') {
			return null;
		}
		// Restore loaded from sidecar after body extract.
		return ['body' => $body, 'restore' => [], 'ver' => 3];
	}
	$off = 5;
	$n = strlen($blob);
	if (!function_exists('fractal_zip_varint_u32_read')) {
		$pm = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_per_member_best.php';
		if (is_readable($pm)) {
			require_once $pm;
		}
	}
	if (!function_exists('fractal_zip_varint_u32_read')) {
		return null;
	}
	$hl = fractal_zip_varint_u32_read($blob, $off, $n, 'FZCL fzhr length');
	if ($hl < 1 || $off + $hl > $n) {
		return null;
	}
	$trailer = substr($blob, $off, $hl);
	$off += $hl;
	$body = substr($blob, $off);
	if ($body === '') {
		return null;
	}
	$fzhrBlob = $trailer;
	if ($ver === "\x02") {
		if (!function_exists('gzuncompress')) {
			return null;
		}
		$raw = @gzuncompress($trailer);
		if (!is_string($raw) || $raw === '') {
			return null;
		}
		$fzhrBlob = $raw;
	}
	$hrOff = 0;
	$restore = fractal_zip_decode_fzhr_v1($fzhrBlob, $hrOff);
	if (!is_array($restore) || $restore === []) {
		return null;
	}
	return ['body' => $body, 'restore' => $restore, 'ver' => ord($ver)];
}

