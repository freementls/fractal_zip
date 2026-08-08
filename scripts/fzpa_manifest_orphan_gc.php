#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Report-only GC helper for fzpa manifest ingest blobs (record_times_stored=false).
 *
 * Lists sha256 blobs referenced by derived/fzpa/.../members.json with no
 * times_stored.xml entry. Does not delete anything unless --apply is passed
 * (and even then only removes blobs with zero references from all manifests).
 *
 *   php scripts/fzpa_manifest_orphan_gc.php
 *   php scripts/fzpa_manifest_orphan_gc.php --apply
 */

$repo = dirname(__DIR__);
$filesRoot = getenv('FILES_ROOT');
if (!is_string($filesRoot) || trim($filesRoot) === '') {
	$filesRoot = dirname($repo) . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR;
}
$boot = rtrim($filesRoot, '/\\') . DIRECTORY_SEPARATOR . 'bootstrap.php';
if (!is_file($boot)) {
	fwrite(STDERR, "FAIL: bootstrap missing at {$boot}\n");
	exit(1);
}
putenv('FILES_ROOT=' . rtrim($filesRoot, '/\\') . DIRECTORY_SEPARATOR);
require_once $boot;
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_native_extract_cache.php';

$apply = in_array('--apply', $argv, true);
$manifestRoot = fractal_zip_native_extract_cache_files_root()
	. 'derived' . DIRECTORY_SEPARATOR . 'fzpa';
$referenced = array();
if (is_dir($manifestRoot)) {
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($manifestRoot, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fi) {
		if (!$fi->isFile() || $fi->getFilename() !== 'members.json') {
			continue;
		}
		$raw = @file_get_contents($fi->getPathname());
		if (!is_string($raw)) {
			continue;
		}
		$data = json_decode($raw, true);
		if (!is_array($data) || !isset($data['members']) || !is_array($data['members'])) {
			continue;
		}
		foreach ($data['members'] as $hash) {
			if (is_string($hash)) {
				$h = FileStore::normalizeHash($hash);
				if (strlen($h) === 64) {
					$referenced[$h] = ($referenced[$h] ?? 0) + 1;
				}
			}
		}
	}
}

$index = new TimesStoredIndex();
$orphans = array();
foreach (array_keys($referenced) as $hash) {
	if ($index->get($hash) < 1) {
		$orphans[] = $hash;
	}
}
sort($orphans);

fwrite(STDERR, sprintf(
	"fzpa manifest GC: %d referenced hashes, %d without times_stored index entry\n",
	count($referenced),
	count($orphans)
));

if (!$apply) {
	foreach ($orphans as $hash) {
		fwrite(STDOUT, $hash . "\n");
	}
	fwrite(STDERR, "Dry run — pass --apply to remove blobs referenced only from manifests with zero index refs (not implemented: manual review recommended).\n");
	exit(0);
}

$store = new FileStore();
$removed = 0;
foreach ($orphans as $hash) {
	$path = $store->blobPath($hash);
	if ($path !== '' && is_file($path)) {
		if (@unlink($path)) {
			$removed++;
		}
	}
}
fwrite(STDERR, "Removed {$removed} orphan manifest-only blobs (review carefully before relying on --apply in production).\n");
