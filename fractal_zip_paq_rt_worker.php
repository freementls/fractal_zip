<?php

declare(strict_types=1);

/**
 * Deferred roundtrip-verify worker for CM arc codecs (lpaq9l/mcm/drt_lpaq9l[p]).
 *
 * Usage: php fractal_zip_paq_rt_worker.php <toolId> <arcPath> <plainSha256Hex> <okMarkerPath>
 *
 * Decodes the archive with the named tool and writes the ok marker only when
 * the decoded bytes hash back to the compress-side plain sha256. Spawned by
 * fractal_zip_paq_deferred_rt_begin() so the ~1 decode-wall verify of a
 * general-text inner overlaps the outer wrap instead of extending it; the
 * parent joins via fractal_zip_paq_deferred_rt_join_or_fail() before the
 * archive is accepted.
 */

if (PHP_SAPI !== 'cli') {
	fwrite(STDERR, "fractal_zip_paq_rt_worker.php is CLI-only.\n");
	exit(2);
}
if (count($argv) !== 5) {
	fwrite(STDERR, "usage: fractal_zip_paq_rt_worker.php <toolId> <arcPath> <plainSha256Hex> <okMarkerPath>\n");
	exit(2);
}
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';

list(, $toolId, $arcPath, $plainSha, $okPath) = $argv;
$arc = is_file($arcPath) ? (string) file_get_contents($arcPath) : '';
if ($arc === '') {
	exit(1);
}
$dec = null;
switch ($toolId) {
	case 'lpaq9l':
		$dec = fractal_zip_paq_lpaq_decompress_bytes($arc);
		break;
	case 'drt_lpaq9l':
		$dec = fractal_zip_paq_drt_lpaq_decompress_bytes($arc, false);
		break;
	case 'drt_lpaq9lp':
		$dec = fractal_zip_paq_drt_lpaq_decompress_bytes($arc, true);
		break;
	case 'drt_lpaq9l_seg':
		$dec = fractal_zip_paq_drt_lpaq_seg_decompress_bytes($arc, false);
		break;
	case 'drt_lpaq9lp_seg':
		$dec = fractal_zip_paq_drt_lpaq_seg_decompress_bytes($arc, true);
		break;
	case 'mcm':
		$dec = fractal_zip_paq_mcm_decompress_bytes($arc);
		break;
	case 'lepton':
		$dec = fractal_zip_paq_lepton_decompress_bytes($arc);
		break;
	case 'brunsli':
		$dec = fractal_zip_paq_brunsli_decompress_bytes($arc);
		break;
	case 'paq8px':
	case 'paq8pxd':
		$exe = fractal_zip_paq_discover_executable($toolId);
		if ($exe === null) {
			exit(1);
		}
		$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzrtw_' . bin2hex(random_bytes(4));
		@mkdir($tmp, 0700, true);
		$ap = $tmp . DIRECTORY_SEPARATOR . 'arc.bin';
		$op = $tmp . DIRECTORY_SEPARATOR . 'out.bin';
		file_put_contents($ap, $arc);
		$ok = fractal_zip_paq_decompress_to_file($toolId, $exe, $ap, $op);
		$dec = ($ok && is_file($op)) ? (string) file_get_contents($op) : null;
		@unlink($ap);
		@unlink($op);
		@rmdir($tmp);
		break;
	default:
		exit(2);
}
if (!is_string($dec) || $dec === '' || !hash_equals($plainSha, hash('sha256', $dec))) {
	exit(1);
}
if (file_put_contents($okPath, "ok\n") === false) {
	exit(1);
}
exit(0);
