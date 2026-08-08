<?php
declare(strict_types=1);

/**
 * PE/COFF encode peel: split .rsrc from the image for cross-file resource locality.
 *
 * Full section splits lose to PASSTHROUGH on typical EXEs; .rsrc-heavy GUI/DLL folders
 * win when resource blobs compress together. Single-file tax is ~FZHR names only.
 *
 * Off by default: CLASSIC FZHR tax cancels .rsrc locality vs PASSTHROUGH on probed
 * GUI/DLL folders. Opt-in: FRACTAL_ZIP_FOLDER_PE_PEEL=1
 */

function fractal_zip_folder_pe_peel_enabled(): bool {
	static $c = null;
	if ($c !== null) {
		return $c;
	}
	$e = getenv('FRACTAL_ZIP_FOLDER_PE_PEEL');
	if ($e === false || trim((string) $e) === '') {
		return $c = false;
	}
	$v = strtolower(trim((string) $e));
	return $c = ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes');
}

/**
 * @return array{roff: int, rsize: int}|null
 */
function fractal_zip_pe_find_rsrc(string $pe): ?array {
	$n = strlen($pe);
	if ($n < 0x40 || substr($pe, 0, 2) !== 'MZ') {
		return null;
	}
	$eLfanew = unpack('V', substr($pe, 0x3c, 4))[1];
	if ($eLfanew < 0x40 || $eLfanew + 24 > $n) {
		return null;
	}
	if (substr($pe, $eLfanew, 4) !== "PE\0\0") {
		return null;
	}
	$coff = $eLfanew + 4;
	$nsec = unpack('v', substr($pe, $coff + 2, 2))[1];
	$optSize = unpack('v', substr($pe, $coff + 16, 2))[1];
	if ($nsec < 1 || $nsec > 96 || $optSize < 2 || $optSize > 512) {
		return null;
	}
	$secOff = $coff + 20 + $optSize;
	if ($secOff + $nsec * 40 > $n) {
		return null;
	}
	for ($i = 0; $i < $nsec; $i++) {
		$s = $secOff + $i * 40;
		$name = rtrim(substr($pe, $s, 8), "\0");
		if ($name !== '.rsrc') {
			continue;
		}
		$rsize = unpack('V', substr($pe, $s + 16, 4))[1];
		$roff = unpack('V', substr($pe, $s + 20, 4))[1];
		if ($rsize < 1 || $roff < 1 || $roff + $rsize > $n) {
			return null;
		}
		return array('roff' => $roff, 'rsize' => $rsize);
	}
	return null;
}

/**
 * Worth peeling: substantial .rsrc (icons/dialogs) — not tiny version blobs.
 */
function fractal_zip_folder_pe_rsrc_worth(int $diskLen, int $rsize): bool {
	if ($diskLen < 16384 || $rsize < 8192) {
		return false;
	}
	// At least ~20% resources, or a large absolute .rsrc (wx-style DLLs).
	return $rsize * 5 >= $diskLen || $rsize >= 32768;
}

/**
 * PE/COFF: CLASSIC pe_rsrc = before + .rsrc [+ after].
 *
 * @param array<string,string> $members
 * @param array<string,array> $restore
 */
function fractal_zip_folder_try_expand_pe(string $diskPath, string $diskBytes, array &$members, array &$restore): bool {
	if (!fractal_zip_folder_pe_peel_enabled()) {
		return false;
	}
	$lb = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
	if (is_readable($lb)) {
		require_once $lb;
	}
	if (!defined('FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC')) {
		return false;
	}
	$base = strtolower(basename($diskPath));
	$ext = strtolower(pathinfo($base, PATHINFO_EXTENSION));
	static $peExt = array(
		'exe' => true, 'dll' => true, 'sys' => true, 'ocx' => true,
		'cpl' => true, 'scr' => true, 'drv' => true, 'efi' => true,
		'acm' => true, 'ax' => true, 'mui' => true,
	);
	if ($ext !== '' && !isset($peExt[$ext])) {
		return false;
	}
	$rs = fractal_zip_pe_find_rsrc($diskBytes);
	if ($rs === null || !fractal_zip_folder_pe_rsrc_worth(strlen($diskBytes), $rs['rsize'])) {
		return false;
	}
	$classic = __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_classic_peel.php';
	if (is_readable($classic)) {
		require_once $classic;
	}
	if (!function_exists('fractal_zip_classic_rebuild_archive')) {
		return false;
	}
	$roff = $rs['roff'];
	$rsize = $rs['rsize'];
	$before = substr($diskBytes, 0, $roff);
	$rsrc = substr($diskBytes, $roff, $rsize);
	$after = substr($diskBytes, $roff + $rsize);
	$names = array();
	$payloads = array();
	$addedNew = array();
	$useShare = function_exists('fractal_zip_folder_register_shared_payload');
	$prefix = rtrim($diskPath, '/') . '/';
	foreach (array(
		array('before', $before),
		array('rsrc', $rsrc),
		array('after', $after),
	) as $row) {
		[$short, $data] = $row;
		if ($data === '' && $short === 'after') {
			continue;
		}
		$logical = $prefix . $short;
		if ($useShare) {
			$key = fractal_zip_folder_register_shared_payload($members, $data, $short, $logical, $addedNew);
			$names[] = function_exists('fractal_zip_folder_fzhr_share_name')
				? fractal_zip_folder_fzhr_share_name($key, $short)
				: $key;
		} else {
			$members[$logical] = $data;
			$addedNew[] = $logical;
			$names[] = $logical;
		}
		$payloads[] = array('name' => $short, 'data' => $data);
	}
	if (count($payloads) < 2) {
		foreach ($addedNew as $nm) {
			unset($members[$nm]);
		}
		return false;
	}
	$meta = 'PE1R' . pack('V', $roff) . pack('V', $rsize);
	$re = fractal_zip_classic_rebuild_archive('pe_rsrc', $meta, $payloads);
	if ($re === null || $re !== $diskBytes) {
		foreach ($addedNew as $nm) {
			unset($members[$nm]);
		}
		return false;
	}
	$restore[$diskPath] = array(
		'kind' => FRACTAL_ZIP_FOLDER_RESTORE_CLASSIC,
		'format' => 'pe_rsrc',
		'meta' => $meta,
		'member_names' => $names,
	);
	return true;
}
