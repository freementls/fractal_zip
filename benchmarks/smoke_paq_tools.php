#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';

$tools = fractal_zip_paq_discover_tools();
echo 'discovered: ' . (count($tools) ? implode(',', array_keys($tools)) : '(none)') . "\n";

putenv('FRACTAL_ZIP_PAQ_TOOLS=phda9:phda9_no_lstm');
$all = fractal_zip_paq_discover_tools();
echo 'phda9_family: ' . (count($all) ? implode(',', array_keys($all)) : '(none)') . "\n";
if (!isset($all['phda9'])) {
	fwrite(STDERR, "phda9 not discovered\n");
	exit(1);
}
if (!isset($all['phda9_no_lstm'])) {
	fwrite(STDERR, "phda9_no_lstm not discovered\n");
	exit(1);
}

$wire = fractal_zip_paq_wrap_wire('phda9', 'fake-payload');
$un = fractal_zip_paq_unwrap_wire($wire);
if ($un === null || $un['tool'] !== 'phda9' || $un['payload'] !== 'fake-payload') {
	fwrite(STDERR, "wire roundtrip failed\n");
	exit(1);
}

if (fractal_zip_paq_native_kind_from_head($wire) !== 'fzpq_paq') {
	fwrite(STDERR, "kind detect failed\n");
	exit(1);
}

echo "smoke_paq_tools ok\n";
