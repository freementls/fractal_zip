<?php
declare(strict_types=1);

/**
 * Serve the HTML portion of a .fzsx / .fzsxsd (skip PHP polyglot prefix and binary trailer).
 * Direct: fzsx_serve.php?f=fzsx_samples/hello.fzsx
 * Apache: examples/.htaccess rewrites *.fzsx / *.fzsxsd here with f=<path>.
 */
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_fzsx.php';

$rel = isset($_GET['f']) ? (string) $_GET['f'] : '';
$rel = str_replace('\\', '/', trim($rel));
if ($rel === '' || str_contains($rel, '..')) {
	http_response_code(400);
	header('Content-Type: text/plain; charset=UTF-8');
	echo "Missing or invalid f=\n";
	exit;
}

$examplesDir = __DIR__;
$path = fractal_zip_fzsx::resolve_archive_path($examplesDir, $rel);
if ($path === null) {
	http_response_code(404);
	header('Content-Type: text/plain; charset=UTF-8');
	echo "Not found\n";
	exit;
}

$readerClass = fractal_zip_fzsx::reader_class_for_path($path);
$range = $readerClass::html_serve_range($path);
if ($range === null) {
	http_response_code(500);
	header('Content-Type: text/plain; charset=UTF-8');
	echo "Invalid archive (no HTML region)\n";
	exit;
}

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
$fh = fopen($path, 'rb');
if ($fh === false) {
	http_response_code(500);
	exit;
}
fseek($fh, $range['start']);
echo fread($fh, $range['length']);
fclose($fh);
