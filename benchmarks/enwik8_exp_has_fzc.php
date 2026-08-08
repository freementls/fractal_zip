#!/usr/bin/env php
<?php
declare(strict_types=1);

/** Exit 0 if JSON has test_files109 with fzc_bytes > 1 MiB (real encode-only result). */
$path = $argv[1] ?? '';
if ($path === '' || !is_file($path)) {
	exit(1);
}
$j = json_decode((string) file_get_contents($path), true);
if (!is_array($j)) {
	exit(1);
}
foreach ($j['cases'] ?? array() as $c) {
	if (($c['label'] ?? '') !== 'test_files109') {
		continue;
	}
	if ((int) ($c['fzc_bytes'] ?? 0) > 1000000) {
		exit(0);
	}
}
exit(1);
