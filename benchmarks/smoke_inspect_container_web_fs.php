#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Regression: inspect_container_for_web_fs stays aligned with try_list_container_members_for_web_fs
 * on edge containers (no crash, consistent member_list_ok vs list ok).
 */

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$fz = new fractal_zip();
$fails = 0;

$assert = static function (bool $cond, string $msg) use (&$fails): void {
	if (!$cond) {
		fwrite(STDERR, "FAIL: {$msg}\n");
		$fails++;
	}
};

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_smoke_inspect_' . bin2hex(random_bytes(6)) . '.bin';
register_shutdown_function(static function () use ($tmp): void {
	if (is_file($tmp)) {
		@unlink($tmp);
	}
});

$gone = $tmp . '_missing_' . bin2hex(random_bytes(4));
$r0 = $fz->inspect_container_for_web_fs($gone);
$assert(!empty($r0['ok']) === false && ($r0['code'] ?? '') === 'not_found', 'missing path → not_found');

$checkPair = static function (fractal_zip $fz, string $path, string $label) use (&$assert): void {
	$list = $fz->try_list_container_members_for_web_fs($path);
	$insp = $fz->inspect_container_for_web_fs($path);
	$assert(!empty($insp['ok']), "{$label}: inspect ok");
	$listOk = !empty($list['ok']);
	$assert($insp['member_list_ok'] === $listOk, "{$label}: member_list_ok matches try_list ok");
	if ($listOk) {
		$n = isset($list['members']) && is_array($list['members']) ? count($list['members']) : 0;
		$assert((int) ($insp['member_count'] ?? -1) === $n, "{$label}: member_count matches list");
	}
};

file_put_contents($tmp, '');
$checkPair($fz, $tmp, 'empty');

file_put_contents($tmp, 'FZB4');
$checkPair($fz, $tmp, 'bare_fzb4_magic');

file_put_contents($tmp, "FZB4\x01");
$checkPair($fz, $tmp, 'truncated_after_magic');

file_put_contents($tmp, "FZLB\x01\x00");
$inFzlb = $fz->inspect_container_for_web_fs($tmp);
$assert(($inFzlb['folder_native_wire_kind'] ?? null) === 'fzlb_tar_br', 'inspect sets folder_native_wire_kind=fzlb_tar_br');

file_put_contents($tmp, 'FZ' . "7z\xBC\xAF\x27\x1C" . "\x00");
$inFzc7 = $fz->inspect_container_for_web_fs($tmp);
$assert(($inFzc7['folder_native_wire_kind'] ?? null) === 'fz_folder_7z_compact', 'inspect sets folder_native_wire_kind=fz_folder_7z_compact');

file_put_contents($tmp, fractal_zip::NATIVE_FOLDER_7Z_WIRE_BYTE . "7z\xBC\xAF\x27\x1C" . "\x00");
$inN7 = $fz->inspect_container_for_web_fs($tmp);
$assert(($inN7['folder_native_wire_kind'] ?? null) === 'fz_folder_7z_n1', 'inspect sets folder_native_wire_kind=fz_folder_7z_n1');

file_put_contents($tmp, "7z\xBC\xAF\x27\x1C" . "\x00");
$inRaw7 = $fz->inspect_container_for_web_fs($tmp);
$assert(($inRaw7['folder_native_wire_kind'] ?? null) === 'fz_folder_7z_raw', 'inspect sets folder_native_wire_kind=fz_folder_7z_raw');

if ($fails > 0) {
	echo "FAIL ({$fails})\n";
	exit(1);
}
echo "OK inspect_container_web_fs\n";
exit(0);
