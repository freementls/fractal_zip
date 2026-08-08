#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * CLI member-list on native outer magic emits parity_hint (not a false "unsupported format").
 */

$root = dirname(__DIR__);
$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzcli_ml_' . bin2hex(random_bytes(4)) . '.bin';
file_put_contents($tmp, "FZLB\x01\x00");
register_shutdown_function(static function () use ($tmp): void {
	@unlink($tmp);
});

$php = defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '' ? PHP_BINARY : 'php';
$cli = $root . DIRECTORY_SEPARATOR . 'fractal_zip_cli.php';
$cmd = escapeshellarg($php) . ' ' . escapeshellarg($cli) . ' member-list --json ' . escapeshellarg($tmp);
$desc = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$p = proc_open($cmd, $desc, $pipes, $root);
if (!is_resource($p)) {
	fwrite(STDERR, "proc_open failed\n");
	exit(1);
}
$out = stream_get_contents($pipes[1]);
$err = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$code = proc_close($p);
if ($code === 0) {
	fwrite(STDERR, "expected member-list failure on FZLB stub\n");
	exit(1);
}
$d = json_decode(trim((string) $out), true);
if (!is_array($d) || ($d['code'] ?? '') !== 'native_outer_single_member_unsupported') {
	fwrite(STDERR, "unexpected json: " . $out . "\nstderr: " . $err . "\n");
	exit(1);
}
if (($d['parity_hint']['id'] ?? '') !== 'web_fs_selective_not_full_extract') {
	fwrite(STDERR, "missing parity_hint on CLI member-list\n");
	exit(1);
}
if (strpos($err, 'parity:') === false) {
	fwrite(STDERR, "stderr should include parity fix line\n");
	exit(1);
}
fwrite(STDOUT, "OK smoke_cli_member_list_parity_hint\n");
exit(0);
