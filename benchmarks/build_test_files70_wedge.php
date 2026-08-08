#!/usr/bin/env php
<?php
/**
 * Builds test_files70: non-BMP literal where gzip-1 ratio is just above FRACTAL_ZIP_LITERAL_SKIP_TRANSFORMS_MAX_GZIP1_RATIO
 * but gzip-9 ratio falls below it — exercises deflate-1 ratio gates vs level-9 tournament scoring (see choose_best_literal_bundle_transform).
 */
declare(strict_types=1);

$root = dirname(__DIR__);
$dir = $root . DIRECTORY_SEPARATOR . 'test_files70';
if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
	fwrite(STDERR, "mkdir failed: $dir\n");
	exit(1);
}

mt_srand(2);
$z = 110;
$r = '';
for ($i = 0; strlen($r) < 40000; $i++) {
	$r .= sprintf('%04d', $i % 1000) . bin2hex(random_bytes(2)) . str_repeat('Z', $z) . "\n";
}
$r = substr($r, 0, 30000);
$path = $dir . DIRECTORY_SEPARATOR . 'probe_wedge.txt';
if (file_put_contents($path, $r) === false) {
	fwrite(STDERR, "write failed: $path\n");
	exit(1);
}

$n = strlen($r);
$l1 = strlen(gzdeflate($r, 1));
$l9 = strlen(gzdeflate($r, 9));
echo "Wrote $path ($n bytes). gzip1 ratio=" . round($l1 / $n, 5) . ' gzip9 ratio=' . round($l9 / $n, 5) . "\n";
