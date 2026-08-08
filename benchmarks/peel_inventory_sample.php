<?php
declare(strict_types=1);

/**
 * Quick peel inventory: identify profile + deep_unwrap layer kinds for sample corpus files.
 *
 * Usage: php benchmarks/peel_inventory_sample.php [dir ...]
 */

require_once dirname(__DIR__) . '/fractal_zip_content_format_policy.php';
require_once dirname(__DIR__) . '/fractal_zip_literal_deep_unwrap.php';

$dirs = array_slice($argv, 1);
if ($dirs === []) {
	$dirs = ['test_files133', 'test_files83', 'test_files82'];
}
$root = dirname(__DIR__);
$samples = [
	'test_files133/xml',
	'test_files133/mozilla',
	'test_files133/samba',
	'test_files83/SF2-US-caravan-expanded.bin',
];
foreach ($dirs as $d) {
	$full = $root . DIRECTORY_SEPARATOR . $d;
	if (!is_dir($full)) {
		continue;
	}
	foreach (scandir($full) ?: [] as $f) {
		if ($f === '.' || $f === '..') {
			continue;
		}
		$p = $d . '/' . $f;
		if (is_file($root . DIRECTORY_SEPARATOR . $p)) {
			$samples[] = $p;
		}
	}
}
$samples = array_values(array_unique($samples));
echo str_pad('file', 44) . str_pad('profile', 18) . str_pad('in', 12) . str_pad('out', 12) . "layers\n";
foreach ($samples as $rel) {
	$path = $root . DIRECTORY_SEPARATOR . $rel;
	if (!is_file($path)) {
		continue;
	}
	$raw = file_get_contents($path);
	if ($raw === false || $raw === '') {
		continue;
	}
	$peek = strlen($raw) > 65536 ? substr($raw, 0, 65536) : $raw;
	$row = fractal_zip_identify_for_policy(basename($rel), $peek);
	$prof = (string) $row['content_profile'];
	list($w, $layers) = fractal_zip_literal_deep_unwrap_with_layers(basename($rel), $raw);
	$kinds = implode(',', array_column($layers, 0));
	echo str_pad($rel, 44) . str_pad($prof, 18) . str_pad((string) strlen($raw), 12)
		. str_pad((string) strlen($w), 12) . ($kinds !== '' ? $kinds : '-') . "\n";
}
