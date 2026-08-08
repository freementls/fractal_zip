<?php
declare(strict_types=1);

/**
 * Raw FZB4 store-only .fz size: FRACTAL_ZIP_IMAGEPAC=0 vs default (on).
 * Note: zip_folder writes the container as {dir}.fz (sibling filename), not {dir}/.fz.
 *
 *   php benchmarks/bench_imagepac_fzb4_store_compare.php [path_to_folder]
 * Default: test_files54_sample under repo root.
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$src = $argc >= 2 ? $argv[1] : $repo . DIRECTORY_SEPARATOR . 'test_files54_sample';
if ($src[0] !== '/' && !str_starts_with($src, $repo)) {
	$src = $repo . DIRECTORY_SEPARATOR . $src;
}
if (!is_dir($src)) {
	fwrite(STDERR, "Not a directory: {$src}\n");
	exit(2);
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

function fractal_zip_bench_copy_tree(string $src, string $dst): void {
	if (is_dir($dst)) {
		fractal_zip_recursive_rm($dst);
	}
	mkdir($dst, 0755, true);
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($src, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$sub = $it->getSubPathname();
		$t = $dst . DIRECTORY_SEPARATOR . $sub;
		if ($item->isDir()) {
			mkdir($t, 0755, true);
		} else {
			$parent = dirname($t);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $t);
		}
	}
}

function fractal_zip_recursive_rm(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
	}
	rmdir($dir);
}

function fractal_zip_bench_fzb4_bytes(string $dir, string $imagePacEnv): int {
	putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=1');
	putenv('FRACTAL_ZIP_IMAGEPAC=' . $imagePacEnv);
	$fzc = $dir . '.fz';
	if (is_file($fzc)) {
		unlink($fzc);
	}
	ob_start();
	$fz = new fractal_zip(500, false, false, null, false);
	$fz->zip_folder($dir, false);
	ob_end_clean();
	if (!is_file($fzc)) {
		throw new RuntimeException('Missing container: ' . $fzc);
	}
	return (int) filesize($fzc);
}

$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzimgpac_cmp_' . bin2hex(random_bytes(6));
$a = $td . '_a';
$b = $td . '_b';
fractal_zip_bench_copy_tree($src, $a);
fractal_zip_bench_copy_tree($src, $b);

$bytesOff = fractal_zip_bench_fzb4_bytes($a, '0');
$bytesOn = fractal_zip_bench_fzb4_bytes($b, '1');
$saved = $bytesOff - $bytesOn;
$pct = $bytesOff > 0 ? 100.0 * $saved / $bytesOff : 0.0;

fractal_zip_recursive_rm($a);
fractal_zip_recursive_rm($b);
@unlink($a . '.fz');
@unlink($b . '.fz');

echo "source_dir={$src}\n";
echo "fzc_bytes_imagepac_off={$bytesOff}\n";
echo "fzc_bytes_imagepac_on={$bytesOn}\n";
echo "saved_bytes={$saved} saved_pct=" . sprintf('%.2f', $pct) . "\n";

putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=');
putenv('FRACTAL_ZIP_IMAGEPAC=');
