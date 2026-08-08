<?php
declare(strict_types=1);

/**
 * CI guard: flag behavioral PATHINFO_EXTENSION outside identify module.
 * Allowlist: identify.php, policy.php, path_extension_bucket (wire metadata / tests),
 * audit/lint scripts, benchmark corpus builders.
 *
 *   php scripts/lint_extension_behavior_policy.php
 */

$root = dirname(__DIR__);
$allowPaths = array(
	'fractal_zip_content_format_identify.php',
	'fractal_zip_content_format_policy.php',
	'fractal_zip_raster_canonical.php',
	'fractal_zip_image_pac.php',
	// Web-ref corpus mining shortlist (which files to scan for URLs) — a scan-cost
	// heuristic, not a format/behavior decision on archive members.
	'fractal_zip_web_ref_apply.php',
	'benchmarks/audit_extension_behavior_sites.php',
	'scripts/lint_extension_behavior_policy.php',
);
$allowPrefixes = array(
	'benchmarks/sample_',
	'benchmarks/build_',
	'benchmarks/container_representation_experiment.php',
	'benchmarks/bench_image_pac_corpus.php',
	'benchmarks/image_semantic_repack_to_dir.php',
);
$behavioralPatterns = array(
	'/pathinfo\s*\([^)]*PATHINFO_EXTENSION/',
	'/str_ends_with\s*\([^,]+,\s*[\'"]\.\w+/',
	'/literal_bundle_rel_is_ms_office_open_container.*str_ends_with/',
);
$violations = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $fi) {
	if (!$fi->isFile() || $fi->getExtension() !== 'php') {
		continue;
	}
	$rel = str_replace('\\', '/', substr($fi->getPathname(), strlen($root) + 1));
	$skip = false;
	foreach ($allowPaths as $a) {
		if ($rel === $a) {
			$skip = true;
			break;
		}
	}
	if (!$skip) {
		foreach ($allowPrefixes as $p) {
			if (str_starts_with($rel, $p)) {
				$skip = true;
				break;
			}
		}
	}
	if ($skip) {
		continue;
	}
	$src = (string) file_get_contents($fi->getPathname());
	if (!str_contains($src, 'PATHINFO_EXTENSION') && !preg_match('/\$textishExt|path_looks_\w+_semantic/', $src)) {
		continue;
	}
	$lines = explode("\n", $src);
	foreach ($lines as $i => $line) {
		if (!preg_match('/PATHINFO_EXTENSION|\$textishExt|path_looks_\w+_semantic\(/', $line)) {
			continue;
		}
		if (str_contains($line, 'path_extension_bucket') && str_contains($rel, 'encode_pipeline')) {
			continue;
		}
		if (preg_match('/function fractal_zip_literal_path_looks_\w+_semantic/', $line)) {
			continue;
		}
		if (preg_match('/path_looks_\w+_semantic\(/', $line) && str_contains($rel, 'fractal_zip_literal_')) {
			continue;
		}
		if (preg_match('/PATHINFO_EXTENSION/', $line) && (
			str_contains($rel, 'encode_pipeline_smoke.php')
			|| str_contains($line, 'path_extension_bucket')
			|| str_contains($line, '@deprecated')
		)) {
			continue;
		}
		$violations[] = $rel . ':' . ($i + 1) . ': ' . trim($line);
	}
}

if ($violations !== []) {
	fwrite(STDERR, "lint_extension_behavior_policy: " . count($violations) . " finding(s)\n");
	foreach ($violations as $v) {
		fwrite(STDERR, $v . "\n");
	}
	exit(1);
}
fwrite(STDERR, "lint_extension_behavior_policy: ok\n");
exit(0);
