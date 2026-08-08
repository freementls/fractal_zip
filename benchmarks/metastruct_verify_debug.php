#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Debug extract verify for a corpus .fz (lists mismatched paths).
 *
 *   php benchmarks/metastruct_verify_debug.php test_files61 [--peel]
 */
putenv('FRACTAL_ZIP_NO_CLI_OPCACHE_REEXEC=1');
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_metastruct_adapt.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';

$corpus = 'test_files61';
$peel = in_array('--peel', $argv, true);
foreach ($argv as $i => $arg) {
	if ($i === 0 || str_starts_with($arg, '-')) {
		continue;
	}
	$corpus = $arg;
}

bench_metastruct_apply_ultra_env_defaults();
putenv('FRACTAL_ZIP_WEB_REF=0');

$src = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $corpus);
if (!is_dir($src)) {
	fwrite(STDERR, "missing: {$src}\n");
	exit(1);
}

putenv('FRACTAL_ZIP_METastruct');
putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL');
if ($peel) {
	putenv('FRACTAL_ZIP_METastruct=1');
	putenv('FRACTAL_ZIP_METastruct_CONTAINER_PEEL=1');
}

$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_vd_' . bin2hex(random_bytes(4));
$stage = $td . DIRECTORY_SEPARATOR . 'corpus';
@mkdir($stage, 0755, true);
bench_metastruct_copytree($src, $stage);

$filesBefore = bench_metastruct_collect_files($stage);

$fz = new fractal_zip(120, true, false, null, false);
bench_metastruct_ob(static function () use ($fz, $stage): void {
	$fz->zip_folder($stage, false);
});
$fzc = $stage . '.fz';
$fzcBytes = is_file($fzc) ? (int) filesize($fzc) : -1;

$fx = new fractal_zip(120, true, false, null, false);
bench_metastruct_ob(static function () use ($fx, $fzc): void {
	$fx->open_container($fzc, false);
});

$filesA = bench_metastruct_collect_files($stage);
$filesB = bench_metastruct_collect_files($td);
$stageBase = basename($stage);
foreach ($filesB as $rel => $_p) {
	if ($rel === $stageBase . '.fz' || str_starts_with($rel, $stageBase . '/')) {
		unset($filesB[$rel]);
	}
}

echo "corpus={$corpus} peel=" . ($peel ? '1' : '0') . " fzc={$fzcBytes}\n";
echo 'before_zip=' . count($filesBefore) . ' after_open src=' . count($filesA) . ' ext_root=' . count($filesB) . "\n";

$skippedNestedFzc = array_values(array_filter(
	array_keys($filesBefore),
	static fn(string $rel): bool => bench_metastruct_verify_skip_fractal_container_member($rel)
));
if ($skippedNestedFzc !== array()) {
	echo 'skipped_nested_fzc_members=' . count($skippedNestedFzc) . "\n";
	foreach ($skippedNestedFzc as $rel) {
		echo "  skip_fzc {$rel}\n";
	}
}

foreach ($filesA as $rel => $_p) {
	if (bench_metastruct_verify_skip_fractal_container_member($rel)) {
		unset($filesA[$rel]);
	}
}
foreach ($filesB as $rel => $_p) {
	if (bench_metastruct_verify_skip_fractal_container_member($rel)) {
		unset($filesB[$rel]);
	}
}

$onlyA = array_diff(array_keys($filesA), array_keys($filesB));
$onlyB = array_diff(array_keys($filesB), array_keys($filesA));
if ($onlyA !== array()) {
	echo "only_in_source:\n";
	foreach (array_slice($onlyA, 0, 20) as $rel) {
		echo "  {$rel}\n";
	}
	if (count($onlyA) > 20) {
		echo '  ... +' . (count($onlyA) - 20) . " more\n";
	}
}
if ($onlyB !== array()) {
	echo "only_in_extract:\n";
	foreach (array_slice($onlyB, 0, 20) as $rel) {
		echo "  {$rel}\n";
	}
	if (count($onlyB) > 20) {
		echo '  ... +' . (count($onlyB) - 20) . " more\n";
	}
}

$mismatch = 0;
foreach ($filesA as $rel => $pathA) {
	if (!isset($filesB[$rel])) {
		continue;
	}
	$pathB = $filesB[$rel];
	if (@filesize($pathA) !== @filesize($pathB)) {
		$mismatch++;
		if ($mismatch <= 15) {
			echo "size_mismatch {$rel}: " . filesize($pathA) . ' vs ' . filesize($pathB) . "\n";
		}
		continue;
	}
	if (@hash_file('sha256', $pathA) !== @hash_file('sha256', $pathB)) {
		$mismatch++;
		if ($mismatch <= 15) {
			echo "hash_mismatch {$rel}\n";
		}
	}
}
echo "content_mismatches={$mismatch}\n";
echo 'verify_ok=' . (bench_metastruct_trees_equal($stage, $td) ? 'yes' : 'no') . "\n";

@unlink($fzc);
bench_metastruct_rmtree($stage);
bench_metastruct_rmtree($td);
