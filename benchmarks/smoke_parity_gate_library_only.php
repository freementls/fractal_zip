#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * library-only parity gate passes on CI-like hosts even when zpaq is absent from PATH.
 */

$root = dirname(__DIR__);
$gate = $root . DIRECTORY_SEPARATOR . 'scripts' . DIRECTORY_SEPARATOR . 'fzc_parity_gate.sh';
if (!is_file($gate)) {
	fwrite(STDERR, "missing {$gate}\n");
	exit(1);
}

$env = getenv();
$env['FZC_PARITY_GATE_LIBRARY_ONLY'] = '1';
$env['PATH'] = '/usr/bin:/bin';
$desc = array(1 => array('pipe', 'w'), 2 => array('pipe', 'w'));
$cmd = 'bash ' . escapeshellarg($gate) . ' --library-only';
$p = proc_open($cmd, $desc, $pipes, $root, $env);
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
	fwrite(STDERR, "library-only gate failed exit={$code} out={$out} err={$err}\n");
	exit(1);
}
fwrite(STDOUT, "OK smoke_parity_gate_library_only\n");
exit(0);
