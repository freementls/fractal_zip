<?php
declare(strict_types=1);

/**
 * Thin helpers for a web filesystem that stores blobs inside `.fz` / `.fractalzip`.
 *
 * Library entrypoints:
 *   {@see fractal_zip::try_read_container_member_bytes_for_web_fs()}
 *   {@see fractal_zip::try_list_container_members_for_web_fs()}
 *   {@see fractal_zip::inspect_container_for_web_fs()}
 *
 * CLI (stderr diagnostics):
 *   php examples/fractal_zip_web_fs_bridge.php list /path/to/archive.fz
 *   php examples/fractal_zip_web_fs_bridge.php read /path/to/archive.fz relative/path/inside.txt > out.bin
 *   php examples/fractal_zip_web_fs_bridge.php inspect /path/to/archive.fz [--json]
 *
 * Equivalent (repo CLI): `php fractal_zip_cli.php member-list|member-read|inspect [--json] …`.
 *
 * Optional JSON (for integration tests): append `--json` — emits one JSON object on stdout.
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_deploy_bootstrap.php';
fzc_examples_require_bench_json_helpers();
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fzc_parity_hints.php';

/**
 * @return array<string, mixed>
 */
function fractal_zip_web_fs_bridge_dispatch(fractal_zip $fz, string $cmd, string $containerPath, ?string $memberPath, bool $jsonOut): array {
	if ($cmd === 'inspect') {
		return $fz->inspect_container_for_web_fs($containerPath);
	}
	if (!is_file($containerPath)) {
		return array('ok' => false, 'error' => 'not_found');
	}
	if ($cmd === 'list') {
		$r = $fz->try_list_container_members_for_web_fs($containerPath);
		if (empty($r['ok'])) {
			return fzc_web_fs_bridge_attach_parity_hint(array(
				'ok' => false,
				'error' => (string) ($r['code'] ?? 'list_failed'),
				'code' => isset($r['code']) ? (string) $r['code'] : null,
				'folder_native_wire_kind' => $r['folder_native_wire_kind'] ?? null,
				'hint' => $r['hint'] ?? null,
			));
		}
		return array('ok' => true, 'list' => $r['members'] ?? array());
	}
	if ($cmd === 'read') {
		if ($memberPath === null || $memberPath === '') {
			return array('ok' => false, 'error' => 'member_required');
		}
		$r = $fz->try_read_container_member_bytes_for_web_fs($containerPath, $memberPath);
		if (empty($r['ok'])) {
			$out = array(
				'ok' => false,
				'error' => (string) ($r['code'] ?? 'read_failed'),
				'code' => isset($r['code']) ? (string) $r['code'] : null,
				'folder_native_wire_kind' => $r['folder_native_wire_kind'] ?? null,
				'hint' => $r['hint'] ?? null,
			);
			if (!empty($r['members_preview'])) {
				$out['members_preview'] = $r['members_preview'];
			}
			return fzc_web_fs_bridge_attach_parity_hint($out);
		}
		return array('ok' => true, 'bytes' => (string) ($r['bytes'] ?? ''));
	}
	return array('ok' => false, 'error' => 'bad_command');
}

$argvIn = $argv ?? array();
$wantJson = false;
$args = array();
foreach ($argvIn as $i => $a) {
	if ($i === 0) {
		continue;
	}
	if ($a === '--json') {
		$wantJson = true;
		continue;
	}
	$args[] = $a;
}

if (PHP_SAPI === 'cli') {
	if (count($args) < 2) {
		fwrite(STDERR, "Usage: php fractal_zip_web_fs_bridge.php list <container> [--json]\n");
		fwrite(STDERR, "       php fractal_zip_web_fs_bridge.php read <container> <memberRelPath> [--json]\n");
		fwrite(STDERR, "       php fractal_zip_web_fs_bridge.php inspect <container> [--json]\n");
		exit(2);
	}
	$cmd = $args[0];
	$path = $args[1];
	$member = $args[2] ?? null;
	$fz = new fractal_zip();
	$res = fractal_zip_web_fs_bridge_dispatch($fz, $cmd, $path, $member, $wantJson);
	if ($wantJson) {
		$payload = $res;
		if (isset($payload['bytes'])) {
			$payload['bytes_base64'] = base64_encode((string) $payload['bytes']);
			unset($payload['bytes']);
		}
		$js = bench_json_encode_try($payload, false);
		if ($js === null) {
			fwrite(STDERR, '[bench] json_encode failed (fractal_zip_web_fs_bridge --json): ' . json_last_error_msg() . "\n");
			exit(2);
		}
		echo $js . "\n";
		exit(!empty($res['ok']) ? 0 : 1);
	}
	if (empty($res['ok'])) {
		$msg = $res['error'] ?? ($res['code'] ?? 'failed');
		fwrite(STDERR, 'fractal_zip_web_fs_bridge: ' . (string) $msg . "\n");
		if (!empty($res['parity_hint']['fix'])) {
			fwrite(STDERR, '  parity: ' . (string) $res['parity_hint']['fix'] . "\n");
		}
		exit(1);
	}
	if ($cmd === 'list') {
		foreach ($res['list'] ?? array() as $p) {
			echo $p . "\n";
		}
		exit(0);
	}
	if ($cmd === 'inspect') {
		$xzOuter = !empty($res['xz_outer']);
		$legacyOuter = !empty($res['outer_needs_legacy_full_read']);
		$nMembers = (int) ($res['member_count'] ?? 0);
		$magicHex = (string) ($res['magic_prefix_hex'] ?? '');
		echo 'path: ' . (string) ($res['path'] ?? '') . "\n";
		$cb = $res['container_bytes'] ?? null;
		echo 'container_bytes: ' . ($cb !== null ? (string) $cb : '?') . "\n";
		echo 'magic_prefix_hex: ' . $magicHex . "\n";
		echo 'sig4 (printable): ' . (string) ($res['sig4_utf8_fallback'] ?? '') . "\n";
		echo 'xz_outer: ' . ($xzOuter ? 'yes' : 'no') . "\n";
		echo 'outer_needs_legacy_full_read: ' . ($legacyOuter ? 'yes' : 'no') . "\n";
		echo 'member_list: ' . (!empty($res['member_list_ok']) ? 'ok' : 'no') . ' (' . (string) $nMembers . ' paths';
		if (!empty($res['member_list_code'])) {
			echo ', code=' . (string) $res['member_list_code'];
		}
		echo ")\n";
		foreach ($res['members_preview'] ?? array() as $rel) {
			echo '  ' . $rel . "\n";
		}
		exit(0);
	}
	echo $res['bytes'] ?? '';
	exit(0);
}
