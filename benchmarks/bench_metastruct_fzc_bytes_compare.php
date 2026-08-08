#!/usr/bin/env php
<?php
declare(strict_types=1);
/** Quick: same-size .fz may differ in bytes when fractal leg shrinks. */
$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';

putenv('FRACTAL_ZIP_WEB_REF=0');
fractal_zip_metastruct_force_legacy_fractal_env();

function zip_stage(string $corpus, bool $adaptive): array {
	$td = sys_get_temp_dir() . '/fzcmp_' . bin2hex(random_bytes(4));
	$stage = $td . '/corpus';
	mkdir($td, 0755, true);
	$src = realpath($corpus);
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::SELF_FIRST);
	$srcN = rtrim(str_replace('\\', '/', $src), '/') . '/';
	foreach ($it as $fi) {
		$rel = substr(str_replace('\\', '/', $fi->getPathname()), strlen($srcN));
		$tgt = $stage . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		if ($fi->isDir()) {
			mkdir($tgt, 0755, true);
		} else {
			mkdir(dirname($tgt), 0755, true);
			copy($fi->getPathname(), $tgt);
		}
	}
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS');
	putenv('FRACTAL_ZIP_METastruct');
	if ($adaptive) {
		putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');
	}
	ob_start();
	$fz = new fractal_zip(120, true, false, null, false);
	$fz->zip_folder($stage, false);
	ob_end_clean();
	$fzc = $stage . '.fz';
	$bytes = is_file($fzc) ? file_get_contents($fzc) : '';
	$out = array(
		'size' => strlen($bytes),
		'sha256' => hash('sha256', $bytes),
		'mid' => $fz->mid_fractal_zip_marker,
	);
	@unlink($fzc);
	array_map('unlink', glob($stage . '/*') ?: array());
	@rmdir($stage);
	@rmdir($td);
	return $out;
}

$corpus = $argv[1] ?? ($root . '/test_files75');
$d = zip_stage($corpus, false);
$a = zip_stage($corpus, true);
echo "corpus={$corpus}\n";
echo 'default  size=' . $d['size'] . ' sha=' . substr($d['sha256'], 0, 16) . ' mid="' . $d['mid'] . "\"\n";
echo 'adaptive size=' . $a['size'] . ' sha=' . substr($a['sha256'], 0, 16) . ' mid="' . $a['mid'] . "\"\n";
echo 'same_bytes=' . ($d['sha256'] === $a['sha256'] ? 'yes' : 'no') . "\n";
