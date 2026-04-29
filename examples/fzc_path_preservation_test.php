<?php

declare(strict_types=1);

/**
 * CLI check: zip_folder preserves nested relative paths as member keys in the .fzc.
 * Run: php examples/fzc_path_preservation_test.php
 */

$lib = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip.php';
if (!is_file($lib)) {
	fwrite(STDERR, "Missing fractal_zip.php at {$lib}\n");
	exit(1);
}
require_once $lib;

$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzc_path_test_' . bin2hex(random_bytes(4));
$inc = $tmp . DIRECTORY_SEPARATOR . 'includes';
$deep = $tmp . DIRECTORY_SEPARATOR . 'styles' . DIRECTORY_SEPARATOR . 'theme';
if (!@mkdir($inc, 0777, true) || !@mkdir($deep, 0777, true)) {
	fwrite(STDERR, "mkdir failed\n");
	exit(1);
}
file_put_contents($inc . DIRECTORY_SEPARATOR . 'sidebar.php', '<?php //sidebar');
file_put_contents($deep . DIRECTORY_SEPARATOR . 'app.css', 'body{}');
file_put_contents($tmp . DIRECTORY_SEPARATOR . 'index.txt', 'root');

$ob = ob_get_level();
ob_start();
try {
	$fz = new fractal_zip(null, true, true, null, true);
	$fz->zip_folder($tmp, false);
} finally {
	while (ob_get_level() > $ob) {
		ob_end_clean();
	}
}

$fzc = $tmp . '.fzc';
if (!is_file($fzc)) {
	fzc_path_test_remove_tree($tmp);
	fwrite(STDERR, "Expected {$fzc} missing.\n");
	exit(1);
}

$fz2 = new fractal_zip(null, true, false, null, false);
$ob2 = ob_get_level();
ob_start();
try {
	$fz2->open_container($fzc, false);
} finally {
	while (ob_get_level() > $ob2) {
		ob_end_clean();
	}
}

$map = isset($fz2->array_fractal_zipped_strings_of_files) && is_array($fz2->array_fractal_zipped_strings_of_files)
	? $fz2->array_fractal_zipped_strings_of_files
	: array();
$keys = array();
foreach (array_keys($map) as $p) {
	$keys[] = str_replace('\\', '/', (string) $p);
}

$need = array('includes/sidebar.php', 'styles/theme/app.css', 'index.txt');
$missing = array();
foreach ($need as $k) {
	$found = false;
	foreach ($keys as $mk) {
		if ($mk === $k) {
			$found = true;
			break;
		}
	}
	if (!$found) {
		$missing[] = $k;
	}
}

@unlink($fzc);
fzc_path_test_remove_tree($tmp);

if ($missing !== array()) {
	fwrite(STDERR, 'Missing member path(s): ' . implode(', ', $missing) . "\nGot keys: " . implode(', ', $keys) . "\n");
	exit(1);
}

echo "OK: nested paths preserved in container (" . count($keys) . " member key(s)).\n";
exit(0);

function fzc_path_test_remove_tree(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		if ($item->isDir()) {
			@rmdir($item->getPathname());
		} else {
			@unlink($item->getPathname());
		}
	}
	@rmdir($dir);
}
