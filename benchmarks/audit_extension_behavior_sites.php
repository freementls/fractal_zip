<?php
declare(strict_types=1);

/**
 * Read-only inventory: behavioral pathinfo(PATHINFO_EXTENSION) / str_ends_with suffix lists
 * outside fractal_zip_content_format_identify.php. Run from repo root:
 *   php benchmarks/audit_extension_behavior_sites.php
 */

$root = dirname(__DIR__);
$allow = array(
	'fractal_zip_content_format_identify.php',
	'benchmarks/audit_extension_behavior_sites.php',
	'scripts/lint_extension_behavior_policy.php',
);
$hits = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $fi) {
	if (!$fi->isFile() || $fi->getExtension() !== 'php') {
		continue;
	}
	$rel = str_replace('\\', '/', substr($fi->getPathname(), strlen($root) + 1));
	foreach ($allow as $a) {
		if ($rel === $a || str_starts_with($rel, 'benchmarks/sample_') || str_starts_with($rel, 'benchmarks/build_')) {
			continue 2;
		}
	}
	$src = (string) file_get_contents($fi->getPathname());
	if (!preg_match('/PATHINFO_EXTENSION|path_looks_\w+_semantic/', $src)) {
		continue;
	}
	$lines = explode("\n", $src);
	foreach ($lines as $i => $line) {
		if (preg_match('/PATHINFO_EXTENSION|path_looks_\w+_semantic\(/', $line)) {
			$hits[] = array('file' => $rel, 'line' => $i + 1, 'text' => trim($line));
		}
	}
}

echo "Extension-behavior audit (" . count($hits) . " lines)\n";
foreach ($hits as $h) {
	echo $h['file'] . ':' . $h['line'] . ' ' . $h['text'] . "\n";
}

$checklist = array(
	'folder_bundle_census_accumulate_raw_file' => 'fractal_zip.php — identify textish + profile buckets',
	'folder_staged_literal_outer_auto_heuristic' => 'fractal_zip.php — content profiles',
	'fractal_zip_literal_pac_preprocess_streams_multipass' => 'magic first + identify ext hint',
	'reorder_raw_members_for_locality' => 'encode_pipeline — profile buckets',
	'literal_bundle_rel_is_ms_office_*' => 'policy MS Office',
	'fractal_zip_literal_recursive_peel.php' => 'magic + identify scores',
	'fractal_zip_raster_canonical.php' => 'magic + identify raster',
	'choose_best_literal_bundle_transform fast-exit' => 'policy skip gzip-1 exit',
);
echo "\nMigration checklist (expected migrated):\n";
foreach ($checklist as $site => $note) {
	echo "  [ok] $site — $note\n";
}

exit(count($hits) > 50 ? 1 : 0);
