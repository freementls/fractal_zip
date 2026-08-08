<?php
declare(strict_types=1);

/**
 * Gzip-proxy sizes for Silesia tar members: raw vs deep_unwrap work bytes.
 *
 * Usage: php benchmarks/peel_silesia133_gzip_proxy.php [file ...]
 */

require_once dirname(__DIR__) . '/fractal_zip_content_format_policy.php';
require_once dirname(__DIR__) . '/fractal_zip_literal_deep_unwrap.php';

$root = dirname(__DIR__);
$files = array_slice($argv, 1);
if ($files === []) {
	$files = array(
		'test_files133/xml',
		'test_files133/mozilla',
		'test_files133/samba',
		'test_files83/SF2-US-caravan-expanded.bin',
	);
}

echo str_pad('file', 40) . str_pad('profile', 16) . str_pad('raw_gz1', 12)
	. str_pad('peel_gz1', 12) . str_pad('delta', 10) . "layers\n";

foreach ($files as $rel) {
	$path = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
	if (!is_file($path)) {
		continue;
	}
	$raw = file_get_contents($path);
	if ($raw === false || $raw === '') {
		continue;
	}
	$peek = strlen($raw) > 65536 ? substr($raw, 0, 65536) : $raw;
	$row = fractal_zip_identify_for_policy(basename($rel), $peek);
	list($work, $layers) = fractal_zip_literal_deep_unwrap_with_layers(basename($rel), $raw);
	$gzRaw = @gzdeflate($raw, 1);
	$gzPeel = @gzdeflate($work, 1);
	$r = ($gzRaw !== false) ? strlen($gzRaw) : -1;
	$p = ($gzPeel !== false) ? strlen($gzPeel) : -1;
	$delta = ($r >= 0 && $p >= 0) ? ($p - $r) : 0;
	$kinds = implode(',', array_column($layers, 0));
	echo str_pad($rel, 40) . str_pad((string) $row['content_profile'], 16)
		. str_pad((string) $r, 12) . str_pad((string) $p, 12)
		. str_pad(($delta <= 0 ? (string) $delta : '+' . $delta), 10)
		. ($kinds !== '' ? $kinds : '-') . "\n";
}
