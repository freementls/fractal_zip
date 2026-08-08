#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Rewrite files in a tree with fractal_zip_literal_pac_preprocess_literal_for_bundle when the result is smaller (lossless).
 *
 *   php benchmarks/optimize_corpus_pac.php [dir] [--dry-run]
 *
 * Default dir: test_files54_sample (repo root). Requires same tools as image_pac / literal_pac.
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dir = $repo . DIRECTORY_SEPARATOR . 'test_files54_sample';
$dry = false;
$argStart = 1;
if ($argc >= 2 && strncmp($argv[1], '--', 2) !== 0) {
	$dir = $argv[1][0] === '/' || str_starts_with($argv[1], $repo) ? $argv[1] : $repo . DIRECTORY_SEPARATOR . $argv[1];
	$argStart = 2;
}
for ($i = $argStart; $i < $argc; $i++) {
	if ($argv[$i] === '--dry-run') {
		$dry = true;
	}
}
$dir = realpath($dir);
if ($dir === false || !is_dir($dir)) {
	fwrite(STDERR, "Not a directory: {$dir}\n");
	exit(2);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_image_pac.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_literal_pac.php';

$nFiles = 0;
$nChanged = 0;
$saved = 0;
$prefixLen = strlen($dir);

$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $info) {
	if (!$info->isFile()) {
		continue;
	}
	$path = $info->getPathname();
	$raw = file_get_contents($path);
	if ($raw === false || $raw === '') {
		continue;
	}
	$nFiles++;
	$rel = substr($path, $prefixLen);
	if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
		$rel = substr($rel, 1);
	}
	$rel = str_replace('\\', '/', $rel);
	$out = fractal_zip_literal_pac_preprocess_literal_for_bundle($rel, $raw);
	if (strlen($out) >= strlen($raw)) {
		continue;
	}
	$delta = strlen($raw) - strlen($out);
	if ($dry) {
		fwrite(STDOUT, sprintf("would shrink %s  %d -> %d  (-%d)\n", $rel, strlen($raw), strlen($out), $delta));
		$nChanged++;
		$saved += $delta;
		continue;
	}
	$tmp = $path . '.fzpac.' . bin2hex(random_bytes(4)) . '.tmp';
	if (file_put_contents($tmp, $out) !== strlen($out)) {
		@unlink($tmp);
		fwrite(STDERR, "write failed: {$rel}\n");
		continue;
	}
	if (!@rename($tmp, $path)) {
		@unlink($tmp);
		fwrite(STDERR, "rename failed: {$rel}\n");
		continue;
	}
	$nChanged++;
	$saved += $delta;
}

printf(
	"dir=%s  dry_run=%s  files_read=%d  files_shrunk=%d  bytes_saved=%d\n",
	$dir,
	$dry ? 'yes' : 'no',
	$nFiles,
	$nChanged,
	$saved
);
