<?php
declare(strict_types=1);
/**
 * Audit PDF stream full decode (direct /Length only) for test_files71 or a given directory.
 * Usage: php pdf_stream_audit_test_files71.php [path-to-dir-or-file]
 */
$base = dirname(__DIR__);
require_once $base . DIRECTORY_SEPARATOR . 'fractal_zip_pdf_stream_decode.php';
$arg = $argv[1] ?? ($base . DIRECTORY_SEPARATOR . 'test_files71');
$paths = array();
if (is_file($arg) && is_readable($arg)) {
	$paths[] = $arg;
} elseif (is_dir($arg)) {
	foreach (glob($arg . '/*.pdf') ?: array() as $p) {
		$paths[] = $p;
	}
} else {
	fwrite(STDERR, "Not found: {$arg}\n");
	exit(1);
}
if (count($paths) === 0) {
	fwrite(STDERR, "No .pdf in {$arg}\n");
	exit(0);
}
foreach ($paths as $path) {
	$pdf = file_get_contents($path);
	if ( !is_string( $pdf) || $pdf === '') {
		echo basename( $path) . ": read failed\n";
		continue;
	}
	$rep = fractal_zip_pdf_streams_fully_decompress_report( $pdf, true);
	echo "=== " . $path . " ===\n";
	echo "file_bytes: " . ( $rep['file_bytes'] ?? 0) . "\n";
	echo "stream_count: " . ( $rep['stream_count'] ?? 0) . " (direct /Length: " . ( $rep['direct_length_count'] ?? 0) . ")\n";
	echo "indirect /Length n 0 R (skipped, not resolved): " . ( $rep['indirect_length_skips'] ?? 0) . "\n";
	echo "decoded_ok: " . ( $rep['decoded_ok'] ?? 0) . "  decoded_fail: " . ( $rep['decoded_fail'] ?? 0) . "\n";
	echo "total_raw_bytes (sum of decoded): " . ( $rep['total_raw_bytes'] ?? 0) . "\n";
	echo "by /Filter key string:\n";
	foreach ( ( $rep['by_filter'] ?? array() ) as $k => $v) {
		echo "  " . $k . ": " . $v . "\n";
	}
	$fl = $rep['failures'] ?? array();
	if (count( $fl) > 0) {
		echo "failures (first 12):\n";
		foreach (array_slice( $fl, 0, 12) as $f) {
			echo "  @{$f['offset']}: {$f['err']} [{$f['filter_s']}]\n";
		}
	}
	echo "\n";
}
