#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * List corpora where heterogeneous FZHM auto-enables after PHASE_UNPEEL (logical member count).
 *
 * Usage:
 *   php benchmarks/prospect_fzhm_flat_corpora.php [--encode] [--only=test_files13,test_files78]
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
fractal_zip_ensure_folder_logical_bundle_loaded();

$doEncode = in_array('--encode', $argv, true);
$only = null;
foreach ($argv as $a) {
	if (is_string($a) && strncmp($a, '--only=', 7) === 0) {
		$only = array_fill_keys(array_values(array_filter(array_map('trim', explode(',', substr($a, 7))))), true);
	}
}

$rows = [];
foreach (glob($repo . DIRECTORY_SEPARATOR . 'test_files*', GLOB_ONLYDIR) ?: [] as $d) {
	$name = basename($d);
	if ($only !== null && !isset($only[$name])) {
		continue;
	}
	$raw = [];
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($d, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $f) {
		if (!$f->isFile() || $f->getFilename() === '.fz') {
			continue;
		}
		$rel = str_replace('\\', '/', substr($f->getPathname(), strlen($d) + 1));
		$raw[$rel] = (string) file_get_contents($f->getPathname());
	}
	if ($raw === []) {
		continue;
	}
	$logical = fractal_zip_resolve_folder_logical_bundle($raw);
	$members = $logical['members'] ?? [];
	$enabled = fractal_zip_heterogeneous_folder_encode_enabled($logical);
	$sumRaw = 0;
	foreach ($raw as $b) {
		$sumRaw += strlen($b);
	}
	$row = [
		'label' => $name,
		'disk_files' => count($raw),
		'logical_members' => count($members),
		'peeled' => !empty($logical['expanded']),
		'raw_bytes' => $sumRaw,
		'fzhm_auto' => $enabled,
	];
	if ($doEncode && $enabled) {
		$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzhm_prospect_' . $name . '_' . getmypid();
		if (is_dir($work)) {
			$fz = new fractal_zip();
			$fz->recursive_remove_directory($work);
		}
		@mkdir($work, 0700, true);
		foreach ($raw as $rel => $bytes) {
			$dest = $work . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
			$fzMk = new fractal_zip();
			$fzMk->build_directory_structure_for($dest);
			file_put_contents($dest, $bytes);
		}
		$fz = new fractal_zip(300, false, true, null, false);
		ob_start();
		try {
			$fz->zip_folder($work, false);
		} finally {
			ob_end_clean();
		}
		$fzc = $work . '.fz';
		$row['fzc_bytes'] = is_file($fzc) ? (int) filesize($fzc) : null;
		$row['outer_codec'] = fractal_zip::$last_outer_codec;
		$row['fzhm_used'] = fractal_zip::$used_folder_per_member_best;
		$row['unified_stream'] = fractal_zip::$used_folder_unified_stream;
		@unlink($fzc);
		$fz->recursive_remove_directory($work);
	}
	$rows[] = $row;
}
usort($rows, static fn($a, $b) => strcmp($a['label'], $b['label']));
echo json_encode(['prospect' => $rows], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
