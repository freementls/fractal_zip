#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Quick sanity: truncated / garbage containers must not crash the soft FZB4 scanners
 * used by try_*_for_web_fs (returns null / error codes instead of progressing).
 */

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$fz = new fractal_zip();
$fails = 0;

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_smoke_hostile_' . bin2hex(random_bytes(6)) . '.fz';
register_shutdown_function(static function () use ($tmp): void {
	if (is_file($tmp)) {
		@unlink($tmp);
	}
});

$cases = array(
	array('label' => 'empty', 'bytes' => ''),
	array('label' => 'bare_fzb4_magic', 'bytes' => 'FZB4'),
	array('label' => 'truncated_after_magic', 'bytes' => "FZB4\x01"),
);

foreach ($cases as $c) {
	file_put_contents($tmp, $c['bytes']);
	$list = $fz->fzb4_try_list_member_paths_from_bundle_path($tmp);
	$expectListEmpty = ($c['label'] === 'bare_fzb4_magic');
	if ($expectListEmpty) {
		if ($list === null || count($list) !== 0) {
			fwrite(STDERR, "expected empty list for {$c['label']}\n");
			$fails++;
		}
	} elseif ($list !== null) {
		fwrite(STDERR, "unexpected non-null list for {$c['label']}\n");
		$fails++;
	}
	$read = $fz->fzb4_try_read_member_bytes_from_bundle_path($tmp, 'nope.txt');
	if ($read !== null) {
		fwrite(STDERR, "unexpected non-null read for {$c['label']}\n");
		$fails++;
	}
}

$r = $fz->try_read_container_member_bytes_for_web_fs($tmp, '../etc/passwd');
if (($r['code'] ?? '') !== 'bad_member_path') {
	fwrite(STDERR, "expected bad_member_path for traversal attempt\n");
	$fails++;
}

if ($fails > 0) {
	echo "FAIL ($fails)\n";
	exit(1);
}
echo "OK hostile_container_inputs\n";
exit(0);
