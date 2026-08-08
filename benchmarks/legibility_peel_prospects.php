<?php
declare(strict_types=1);

/**
 * Legibility + peel prospect report: score before/after unwrap, gzip proxy, embedded islands.
 *
 * Usage:
 *   php benchmarks/legibility_peel_prospects.php [dir ...]
 *   FRACTAL_ZIP_LITERAL_LEGIBILITY_UNWRAP=1 php benchmarks/legibility_peel_prospects.php test_files133
 */

$repo = dirname(__DIR__);
require_once $repo . '/fractal_zip_content_format_policy.php';
require_once $repo . '/fractal_zip_literal_legibility.php';
require_once $repo . '/fractal_zip_literal_embedded_islands.php';
require_once $repo . '/fractal_zip_literal_deep_unwrap.php';

$dirs = array_slice($argv, 1);
if ($dirs === []) {
	$dirs = array('test_files133', 'test_files83');
}
$files = array();
foreach ($dirs as $d) {
	$full = $repo . DIRECTORY_SEPARATOR . $d;
	if (!is_dir($full)) {
		continue;
	}
	foreach (scandir($full) ?: array() as $f) {
		if ($f === '.' || $f === '..') {
			continue;
		}
		$p = $d . '/' . $f;
		if (is_file($repo . DIRECTORY_SEPARATOR . $p)) {
			$files[] = $p;
		}
	}
}
sort($files);

$legOn = fractal_zip_literal_legibility_enabled();
echo "# legibility peel prospects (legibility_unwrap=" . ($legOn ? 'on' : 'off') . ")\n";
echo str_pad('file', 36) . str_pad('magic', 12) . str_pad('leg0', 7) . str_pad('leg1', 7)
	. str_pad('dleg', 7) . str_pad('gz_save', 10) . str_pad('islands', 8) . "notes\n";

foreach ($files as $rel) {
	$path = $repo . DIRECTORY_SEPARATOR . $rel;
	$raw = file_get_contents($path);
	if ($raw === false || $raw === '') {
		continue;
	}
	$base = basename($rel);
	$leg0 = fractal_zip_literal_legibility_score($raw);
	list($wStd) = fractal_zip_literal_deep_unwrap_with_layers($base, $raw, false);
	$leg1 = fractal_zip_literal_legibility_score($wStd);
	$hits = fractal_zip_literal_embedded_island_scan($raw, 8);
	$g0 = @gzdeflate($raw, 1);
	$g1 = @gzdeflate($wStd, 1);
	$gzSave = ($g0 !== false && $g1 !== false) ? (strlen($g0) - strlen($g1)) : 0;
	$notes = array();
	if ($wStd !== $raw) {
		$notes[] = 'deep';
	}
	if (count($hits) > 0) {
		$notes[] = $hits[0]['kind'] . '@' . $hits[0]['off'];
	}
	$row = fractal_zip_identify_for_policy($base, substr($raw, 0, 65536));
	if ($row['content_profile'] !== 'text_plain' && $row['content_profile'] !== 'opaque_binary') {
		$notes[] = $row['content_profile'];
	}
	echo str_pad($rel, 36) . str_pad($leg0['magic_label'], 12) . str_pad(sprintf('%.1f', $leg0['score']), 7)
		. str_pad(sprintf('%.1f', $leg1['score']), 7) . str_pad(sprintf('%+.1f', $leg1['score'] - $leg0['score']), 7)
		. str_pad((string) $gzSave, 10) . str_pad((string) count($hits), 8)
		. implode(',', $notes) . "\n";
}
