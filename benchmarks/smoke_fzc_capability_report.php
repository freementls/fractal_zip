#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Smoke: fzc_capability_report.php emits valid JSON with library + outer_wire_shapes.
 */

$root = dirname(__DIR__);
$reportPhp = $root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fzc_capability_report.php';
if (!is_file($reportPhp)) {
	fwrite(STDERR, "missing {$reportPhp}\n");
	exit(1);
}

$php = defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '' ? PHP_BINARY : 'php';
$cmd = escapeshellarg($php) . ' -d opcache.enable_cli=0 ' . escapeshellarg($reportPhp) . ' --label=smoke --json';
$descReport = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$p = proc_open($cmd, $descReport, $pipes, $root);
if (!is_resource($p)) {
	fwrite(STDERR, "proc_open failed\n");
	exit(1);
}
$out = stream_get_contents($pipes[1]);
$err = stream_get_contents($pipes[2]);
fclose($pipes[1]);
fclose($pipes[2]);
$code = proc_close($p);
if ($code !== 0) {
	fwrite(STDERR, "report exit={$code} stderr={$err}\n");
	exit(1);
}

$d = json_decode(trim((string) $out), true);
if (!is_array($d)) {
	fwrite(STDERR, "invalid json from capability report\n");
	exit(1);
}
foreach (array('profile', 'external_tools', 'outer_wire_shapes', 'parity_gaps') as $k) {
	if (!array_key_exists($k, $d)) {
		fwrite(STDERR, "missing key: {$k}\n");
		exit(1);
	}
}
if (empty($d['library']['loadable'])) {
	fwrite(STDERR, "fractal_zip not loadable\n");
	exit(1);
}
if (!isset($d['outer_wire_shapes']['fzhm_store'], $d['outer_wire_shapes']['fzpa_zpaq'])) {
	fwrite(STDERR, "outer_wire_shapes missing expected ids\n");
	exit(1);
}

$webCmd = escapeshellarg($php) . ' -d opcache.enable_cli=0 ' . escapeshellarg($reportPhp) . ' --label=smoke-web --as-web --json';
$descWeb = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$p2 = proc_open($webCmd, $descWeb, $pipes2, $root);
if (!is_resource($p2)) {
	fwrite(STDERR, "proc_open web-sim failed\n");
	exit(1);
}
$out2 = stream_get_contents($pipes2[1]);
fclose($pipes2[1]);
fclose($pipes2[2]);
$code2 = proc_close($p2);
if ($code2 !== 0) {
	fwrite(STDERR, "web-sim report exit={$code2}\n");
	exit(1);
}
$d2 = json_decode(trim((string) $out2), true);
if (!is_array($d2) || empty($d2['profile']['as_web_simulation'])) {
	fwrite(STDERR, "web-sim report invalid\n");
	exit(1);
}

$comparePhp = $root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fzc_capability_compare.php';
$tmpA = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzcap_smoke_a_' . bin2hex(random_bytes(4)) . '.json';
$tmpB = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzcap_smoke_b_' . bin2hex(random_bytes(4)) . '.json';
file_put_contents($tmpA, $out);
file_put_contents($tmpB, $out);
$cmpCmd = escapeshellarg($php) . ' ' . escapeshellarg($comparePhp) . ' ' . escapeshellarg($tmpA) . ' ' . escapeshellarg($tmpB);
$descCmp = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$p3 = proc_open($cmpCmd, $descCmp, $pipes3, $root);
if (!is_resource($p3)) {
	@unlink($tmpA);
	@unlink($tmpB);
	fwrite(STDERR, "proc_open compare failed\n");
	exit(1);
}
fclose($pipes3[1]);
fclose($pipes3[2]);
$cmpExit = proc_close($p3);
if ($cmpExit !== 0) {
	@unlink($tmpA);
	@unlink($tmpB);
	fwrite(STDERR, "compare identical reports should exit 0\n");
	exit(1);
}

$descCmp2 = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$cmpJsonCmd = escapeshellarg($php) . ' ' . escapeshellarg($comparePhp) . ' --json ' . escapeshellarg($tmpA) . ' ' . escapeshellarg($tmpB);
$p4 = proc_open($cmpJsonCmd, $descCmp2, $pipes4, $root);
if (!is_resource($p4)) {
	@unlink($tmpA);
	@unlink($tmpB);
	fwrite(STDERR, "proc_open compare --json failed\n");
	exit(1);
}
$cmpJs = stream_get_contents($pipes4[1]);
fclose($pipes4[1]);
fclose($pipes4[2]);
$cmpJsonExit = proc_close($p4);
@unlink($tmpA);
@unlink($tmpB);
if ($cmpJsonExit !== 0) {
	fwrite(STDERR, "compare --json should exit 0\n");
	exit(1);
}
$cmpDec = json_decode(trim((string) $cmpJs), true);
if (!is_array($cmpDec) || empty($cmpDec['ok'])) {
	fwrite(STDERR, "compare --json ok=false\n");
	exit(1);
}

require_once $root . DIRECTORY_SEPARATOR . 'examples' . DIRECTORY_SEPARATOR . 'fzc_parity_hints.php';
$hint = fzc_parity_hint_for_extract_error('Container uses zpaq outer format but `zpaq` was not found on PATH (set FRACTAL_ZIP_ZPAQ).');
if ($hint === null || ($hint['id'] ?? '') !== 'zpaq_missing') {
	fwrite(STDERR, "fzc_parity_hints zpaq pattern failed\n");
	exit(1);
}
$attached = fzc_web_attach_parity_hint(array('ok' => false, 'error' => 'Container uses zpaq outer format but `zpaq` was not found on PATH'));
if (!isset($attached['parity_hint']['id']) || $attached['parity_hint']['id'] !== 'zpaq_missing') {
	fwrite(STDERR, "fzc_web_attach_parity_hint failed\n");
	exit(1);
}

fwrite(STDOUT, "OK smoke_fzc_capability_report\n");
exit(0);
