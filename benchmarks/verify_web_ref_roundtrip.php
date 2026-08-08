#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';

bench_web_ref_apply_probe_fast_defaults();
putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE_MAX=20');
putenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS=15');
putenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT=8G');

$src = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'test_files109';
$fzc = $src . '.fz';
$extract = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.web_ref_verify_extract';
@unlink($fzc);
if (is_dir($extract)) {
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($extract, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
	foreach ($it as $fi) {
		$fi->isDir() ? @rmdir($fi->getPathname()) : @unlink($fi->getPathname());
	}
	@rmdir($extract);
}
@mkdir($extract, 0755, true);

$orig = file_get_contents($src . DIRECTORY_SEPARATOR . 'enwik8');
if (!is_string($orig)) {
	fwrite(STDERR, "missing enwik8\n");
	exit(1);
}

$fz = new fractal_zip();
$fz->zip_folder($src, false);
if (!is_file($fzc)) {
	fwrite(STDERR, "missing fzc: $fzc\n");
	exit(1);
}
$hasFzwr = str_contains((string) file_get_contents($fzc), 'FZWR');
fwrite(STDERR, 'fzc_bytes=' . filesize($fzc) . ' fzwr=' . ($hasFzwr ? 'yes' : 'no') . "\n");

copy($fzc, $extract . DIRECTORY_SEPARATOR . basename($fzc));
$fz2 = new fractal_zip();
ob_start();
$fz2->open_container($extract . DIRECTORY_SEPARATOR . basename($fzc), false);
ob_end_clean();

$restored = $extract . DIRECTORY_SEPARATOR . 'enwik8';
if (!is_file($restored)) {
	fwrite(STDERR, "no restored enwik8; extract tree:\n");
	foreach (scandir($extract) ?: array() as $f) {
		if ($f === '.' || $f === '..') {
			continue;
		}
		fwrite(STDERR, "  $f\n");
	}
	exit(1);
}
$got = file_get_contents($restored);
if (!is_string($got)) {
	fwrite(STDERR, "read restored failed\n");
	exit(1);
}
if ($got === $orig) {
	echo "web_ref_roundtrip_ok bytes=" . strlen($got) . "\n";
	exit(0);
}
fwrite(STDERR, 'orig_len=' . strlen($orig) . ' got_len=' . strlen($got) . "\n");
if (strpos($got, '@w{') !== false) {
	fwrite(STDERR, "restored still contains @w tokens\n");
}
exit(1);
