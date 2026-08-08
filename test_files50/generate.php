#!/usr/bin/env php
<?php
// Optional: populate test_files50 for benchmarks with --with-synthetic-micro
declare(strict_types=1);
$dir = __DIR__;
for ($i = 0; $i < 300; $i++) {
	$name = sprintf('m%04d.txt', $i);
	file_put_contents($dir . DIRECTORY_SEPARATOR . $name, '.');
}
echo "Wrote 300 files in " . $dir . "\n";
