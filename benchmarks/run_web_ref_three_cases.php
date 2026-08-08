#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Measured web-ref benefit on test_files13, test_files57, test_files58_sample.
 * Usage: php benchmarks/run_web_ref_three_cases.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_web_ref.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_web_ref_env.php';

@ini_set('memory_limit', getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT') ?: '4G');

/** @var list<array{label: string, dir: string, large: bool}> */
$cases = array(
	array('label' => 'test_files13', 'dir' => $repo . DIRECTORY_SEPARATOR . 'test_files13', 'large' => false),
	array('label' => 'test_files57', 'dir' => $repo . DIRECTORY_SEPARATOR . 'test_files57', 'large' => false),
	array('label' => 'test_files58_sample', 'dir' => $repo . DIRECTORY_SEPARATOR . 'test_files58_sample', 'large' => true),
);

/** Tracker / prior bench baselines without web-ref (FRACTAL_ZIP_WEB_REF=0). */
$baselineFzc = array(
	'test_files13' => 6207,
	'test_files57' => 628596,
	'test_files58_sample' => 62554534,
);

function bench_copy_corpus(string $srcDir, string $label): string
{
	$tmpRoot = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzwr3_' . preg_replace('/[^a-zA-Z0-9._-]+/', '_', $label) . '_' . bin2hex(random_bytes(4));
	if (!@mkdir($tmpRoot, 0777, true) && !is_dir($tmpRoot)) {
		throw new RuntimeException('mkdir failed: ' . $tmpRoot);
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($srcDir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	$prefix = strlen(rtrim($srcDir, '/\\')) + 1;
	foreach ($it as $fi) {
		$rel = substr($fi->getPathname(), $prefix);
		$dest = $tmpRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if ($fi->isDir()) {
			if (!is_dir($dest)) {
				@mkdir($dest, 0777, true);
			}
			continue;
		}
		if (!@copy($fi->getPathname(), $dest)) {
			throw new RuntimeException('copy failed: ' . $fi->getPathname());
		}
	}
	return $tmpRoot;
}

function bench_rm_tree(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $fi) {
		if ($fi->isDir()) {
			@rmdir($fi->getPathname());
		} else {
			@unlink($fi->getPathname());
		}
	}
	@rmdir($dir);
}

/** @return array<string, string> rel => sha1 */
function bench_sha1_tree(string $dir): array
{
	$out = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS));
	$prefix = strlen(rtrim($dir, '/\\')) + 1;
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$rel = substr($fi->getPathname(), $prefix);
		$rel = str_replace('\\', '/', $rel);
		if (str_ends_with(strtolower($rel), '.fz')) {
			continue;
		}
		$hash = @sha1_file($fi->getPathname());
		if (is_string($hash)) {
			$out[$rel] = $hash;
		}
	}
	ksort($out);
	return $out;
}

function bench_verify_roundtrip(string $dir, string $fzcPath): bool
{
	if (!is_file($fzcPath)) {
		return false;
	}
	$before = bench_sha1_tree($dir);
	$extractDir = $dir . '_extract_' . bin2hex(random_bytes(3));
	@mkdir($extractDir, 0777, true);
	$ok = false;
	try {
		$fz = new fractal_zip();
		$fz->open_container($fzcPath, $extractDir);
		$after = bench_sha1_tree($extractDir);
		$ok = ($before === $after);
	} catch (Throwable $e) {
		$ok = false;
	} finally {
		bench_rm_tree($extractDir);
	}
	return $ok;
}

function bench_encode_env(bool $webRef): void
{
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
	putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ARC_COMPARE=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=' . ($webRef ? '1' : '0'));
	if ($webRef) {
		bench_web_ref_apply_probe_fast_defaults();
		putenv('FRACTAL_ZIP_WEB_REF_WHOLE_PAGE=0');
		putenv('FRACTAL_ZIP_WEB_REF_URL_LITERAL=1');
		putenv('FRACTAL_ZIP_WEB_REF_FOLDER_RAW_APPLY=1');
		putenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_CHUNKS=80');
		putenv('FRACTAL_ZIP_WEB_REF_PROBE_MAX_URLS=40');
	} else {
		putenv('FRACTAL_ZIP_WEB_REF_PROBE_SKIP_MIRROR=0');
	}
}

function encode_case(string $dir, bool $webRef): array
{
	bench_encode_env($webRef);
	$fzcPath = $dir . '.fz';
	@unlink($fzcPath);
	$fz = new fractal_zip();
	$t0 = microtime(true);
	$fz->zip_folder($dir, false);
	$sec = microtime(true) - $t0;
	if (!is_file($fzcPath)) {
		return array('error' => 'no fzc');
	}
	$blob = (string) file_get_contents($fzcPath);
	$meta = fractal_zip_web_ref_peel_fzwr_from_blob($blob);
	$entries = is_array($meta) ? ($meta['entries'] ?? array()) : array();
	return array(
		'fzc_bytes' => (int) filesize($fzcPath),
		'fzwr_entries' => count($entries),
		'verify_ok' => bench_verify_roundtrip($dir, $fzcPath),
		'seconds' => $sec,
	);
}

$out = array('generated' => date('c'), 'cases' => array());

foreach ($cases as $case) {
	$label = $case['label'];
	$srcDir = $case['dir'];
	if (!is_dir($srcDir)) {
		fwrite(STDERR, "Skip missing $srcDir\n");
		continue;
	}
	$tmpDir = bench_copy_corpus($srcDir, $label);
	try {
		fwrite(STDERR, "=== $label baseline (no web-ref) ===\n");
		$off = encode_case($tmpDir, false);
		fwrite(STDERR, "=== $label web-ref ===\n");
		$on = encode_case($tmpDir, true);
	} finally {
		bench_rm_tree($tmpDir);
		@unlink($tmpDir . '.fz');
	}
	$trackerBase = $baselineFzc[$label] ?? null;
	$offBytes = (int) ($off['fzc_bytes'] ?? 0);
	$onBytes = (int) ($on['fzc_bytes'] ?? 0);
	$baseBytes = $trackerBase ?? $offBytes;
	$out['cases'][] = array(
		'label' => $label,
		'baseline_bytes' => $baseBytes,
		'baseline_tracker' => $trackerBase,
		'fzc_without_web_ref' => $offBytes,
		'fzc_with_web_ref' => $onBytes,
		'delta_vs_tracker' => ($baseBytes > 0 && $onBytes > 0) ? $baseBytes - $onBytes : null,
		'delta_vs_off_encode' => ($offBytes > 0 && $onBytes > 0) ? $offBytes - $onBytes : null,
		'fzwr_entries' => (int) ($on['fzwr_entries'] ?? 0),
		'verify_ok' => (bool) ($on['verify_ok'] ?? false),
		'web_ref' => $on,
		'no_web_ref' => $off,
	);
}

$jsonPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.web_ref_three_cases.json';
file_put_contents($jsonPath, json_encode($out, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");

echo "| corpus | baseline fzc | web-ref fzc | delta | FZWR | verify_ok |\n";
echo "|--------|-------------:|------------:|------:|-----:|----------:|\n";
foreach ($out['cases'] as $c) {
	$delta = $c['delta_vs_tracker'];
	printf(
		"| %s | %s | %s | %s | %d | %s |\n",
		$c['label'],
		number_format((int) $c['baseline_bytes']),
		number_format((int) $c['fzc_with_web_ref']),
		$delta !== null ? number_format((int) $delta) : 'n/a',
		(int) $c['fzwr_entries'],
		($c['verify_ok'] ?? false) ? 'yes' : 'no'
	);
}
echo "\nJSON: $jsonPath\n";
