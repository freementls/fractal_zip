<?php
/**
 * Raw byte totals for test_files74/75/76 (on-disk, includes materialize_rasters.php; run after
 * `php test_files76/materialize_rasters.php` to include DCIM rasters in test_files76).
 *
 *   php benchmarks/lifestyle_corpus_stats.php
 * Full benchmark JSON (fzc, gzip-9, 7z, min-ext, timings):
 *   php test_files76/materialize_rasters.php
 *   FRACTAL_ZIP_BENCH_MEMORY_LIMIT=2G php benchmarks/run_benchmarks.php --only=test_files74,test_files75,test_files76 --no-case-timeout --json
 */
$root = dirname(__DIR__);
echo "corpus\tfiles\traw_bytes\n";
$grand = 0;
foreach (['test_files74', 'test_files75', 'test_files76'] as $name) {
	$dir = $root . '/' . $name;
	$n = 0;
	$fc = 0;
	if (is_dir($dir)) {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
		);
		foreach ($it as $f) {
			if ($f->isFile()) {
				$n += (int) $f->getSize();
				$fc++;
			}
		}
	}
	$grand += $n;
	echo $name . "\t" . $fc . "\t" . $n . "\n";
}
echo "all_three\t \t" . $grand . "\n";
