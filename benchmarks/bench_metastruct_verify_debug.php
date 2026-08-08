#!/usr/bin/env php
<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/fractal_zip.php';

function ms_collect(string $root): array {
	$rootN = rtrim(str_replace('\\', '/', realpath($root) ?: $root), '/') . '/';
	$out = array();
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) continue;
		$p = str_replace('\\', '/', $fi->getPathname());
		$out[substr($p, strlen($rootN))] = $p;
	}
	ksort($out);
	return $out;
}

putenv('FRACTAL_ZIP_WEB_REF=0');
fractal_zip_metastruct_force_legacy_fractal_env();
$corpus = realpath($argv[1] ?? dirname(__DIR__) . '/test_files107');
$td = sys_get_temp_dir() . '/msdbg_' . bin2hex(random_bytes(3));
$stage = $td . '/corpus';
mkdir($stage, 0755, true);
exec('cp -a ' . escapeshellarg($corpus) . '/. ' . escapeshellarg($stage));
ob_start();
$fz = new fractal_zip(120, true, false, null, false);
$fz->zip_folder($stage, false);
ob_end_clean();
$fzc = $stage . '.fz';
$fx = new fractal_zip(120, true, false, null, false);
ob_start();
$fx->open_container($fzc, false);
ob_end_clean();
$a = ms_collect($stage);
$b = ms_collect($td);
foreach ($b as $rel => $_) {
	if ($rel === 'corpus.fz' || str_starts_with($rel, 'corpus/')) unset($b[$rel]);
}
echo 'stage_files=' . count($a) . ' extract_files=' . count($b) . "\n";
$onlyA = array_diff_key($a, $b);
$onlyB = array_diff_key($b, $a);
if ($onlyA) echo "only_stage:\n" . implode("\n", array_keys($onlyA)) . "\n";
if ($onlyB) echo "only_extract:\n" . implode("\n", array_keys($onlyB)) . "\n";
foreach (array_intersect_key($a, $b) as $rel => $pa) {
	$pb = $b[$rel];
	if (@hash_file('sha256', $pa) !== @hash_file('sha256', $pb)) {
		echo "hash_mismatch: $rel\n";
	}
}
