<?php
declare(strict_types=1);

/**
 * Micro corpus (same-pixels PNG+BMP): baseline tools vs full fractal_zip literal-folder pipeline.
 *
 * FZ “best” path (same as unified stream contest): encode_literal_bundle_payload (auto raster/FZBD
 * policy) → finalize_literal_bundle_inner_for_compress (optional FZWS) → choose_smaller_adaptive_bundle_or_raw_escaped
 * (adaptive_compress on bundle inner vs raw escaped-per-file container).
 *
 * - gzip9: deterministic bundle + zlib (same as run_benchmarks: PHP gzcompress(9), or **pigz -z** when
 *   **FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ=1** and pigz is on PATH; **-p** from **FRACTAL_ZIP_BENCH_PIGZ_P** or **FRACTAL_ZIP_LITERALPAC_PIGZ_P**).
 * - 7z / tar|zstd: same as run_benchmarks folder baselines.
 *
 *   php benchmarks/bench_fzbd_vs_baselines.php
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

putenv('FRACTAL_ZIP_RASTER_CANONICAL=1');
putenv('FRACTAL_ZIP_RASTER_CANONICAL_BUNDLE=1');

/** @param array<string, int> $files rel path => size */
function fzbd_bench_build_deterministic_bundle(string $dir, array $files): string
{
	ksort($files, SORT_STRING);
	$blob = '';
	foreach ($files as $rel => $_size) {
		$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$body = file_get_contents($path);
		if (!is_string($body)) {
			continue;
		}
		$blob .= pack('N', strlen($rel)) . $rel . pack('N', strlen($body)) . $body;
	}
	return $blob;
}

function fzbd_bench_remove_dir(string $dir): void
{
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

function fzbd_bench_gzip_pigz_wanted_by_env(): bool
{
	$e = getenv('FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
}

function fzbd_bench_pigz_path(): ?string
{
	static $cached = null;
	if ($cached !== null) {
		return $cached === false ? null : $cached;
	}
	$line = @shell_exec('command -v pigz 2>/dev/null');
	$t = is_string($line) ? trim($line) : '';
	if ($t === '') {
		$cached = false;
		return null;
	}
	$cached = $t;
	return $t;
}

/**
 * Optional {@code pigz -p} tokens — same precedence as {@code run_benchmarks.php} (bench + literal_pac).
 *
 * @return list<string>
 */
function fzbd_bench_pigz_p_argv(): array
{
	$e = getenv('FRACTAL_ZIP_BENCH_PIGZ_P');
	if ($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_LITERALPAC_PIGZ_P');
	}
	if ($e === false || trim((string) $e) === '') {
		return [];
	}
	$v = trim((string) $e);
	if (strcasecmp($v, 'off') === 0 || strcasecmp($v, 'no') === 0) {
		return [];
	}
	if (strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'on') === 0 || strcasecmp($v, 'all') === 0) {
		$n = trim((string) @shell_exec('command -v nproc >/dev/null 2>&1 && nproc 2>/dev/null'));
		$p = (ctype_digit($n) && (int) $n > 0) ? $n : '8';
		return ['-p', $p];
	}
	if (ctype_digit($v)) {
		return ['-p', $v];
	}
	return [];
}

/**
 * Zlib-wrapped bytes (gzcompress / **pigz -z**), run_benchmarks–compatible.
 */
function fzbd_bench_zlib_compress_blob(string $blob, int $level): string
{
	$level = max(1, min(9, $level));
	if (fzbd_bench_gzip_pigz_wanted_by_env()) {
		$pigz = fzbd_bench_pigz_path();
		if ($pigz !== null) {
			$pfr = '';
			foreach (fzbd_bench_pigz_p_argv() as $t) {
				$pfr .= ' ' . escapeshellarg((string) $t);
			}
			$tmp = tempnam(sys_get_temp_dir(), 'fzbdgz_');
			if ($tmp !== false && file_put_contents($tmp, $blob) !== false) {
				$cmd = escapeshellarg($pigz) . $pfr . ' -' . $level . ' -z -c -n ' . escapeshellarg($tmp) . ' 2>/dev/null';
				$out = shell_exec($cmd);
				unlink($tmp);
				if (is_string($out) && $out !== '') {
					return $out;
				}
			} elseif ($tmp !== false) {
				@unlink($tmp);
			}
		}
	}
	$z = gzcompress($blob, $level);
	return $z === false ? '' : $z;
}

/** gzip9 bundle bytes (run_benchmarks layout). */
function fzbd_bench_gzip9_bundle_bytes(string $dir): int
{
	$files = [];
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		$n = filesize($path);
		if ($n === false) {
			continue;
		}
		$files[$rel] = $n;
	}
	$blob = fzbd_bench_build_deterministic_bundle($dir, $files);
	$z = fzbd_bench_zlib_compress_blob($blob, 9);
	if ($z === '') {
		return strlen($blob);
	}
	return strlen($z);
}

/** @return int|null */
function fzbd_bench_seven_zip_folder_bytes(string $sourceDir): ?int
{
	$seven = fractal_zip::seven_zip_executable();
	if ($seven === null) {
		return null;
	}
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7fzbd_' . bin2hex(random_bytes(8));
	$arc = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7fzbd_' . bin2hex(random_bytes(8)) . '.7z';
	fzbd_bench_remove_dir($box);
	mkdir($box, 0755, true);
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$sub = $it->getSubPathname();
		$target = $box . DIRECTORY_SEPARATOR . $sub;
		if ($item->isDir()) {
			mkdir($target, 0755, true);
		} else {
			$parent = dirname($target);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $target);
		}
	}
	$cwd = getcwd();
	$ret = -1;
	$mmt = fractal_zip::seven_zip_mmt_shell_fragment_for_exec();
	if (chdir($box)) {
		exec(escapeshellarg($seven) . ' a -t7z -mx=9' . $mmt . ' -m0=lzma2 -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' . 2>/dev/null', $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	$n = ($ret === 0 && is_file($arc)) ? filesize($arc) : null;
	if (is_file($arc)) {
		unlink($arc);
	}
	fzbd_bench_remove_dir($box);
	return ($n !== false && $n !== null) ? (int) $n : null;
}

/** @return int|null */
function fzbd_bench_tar_zstd_bytes(string $sourceDir): ?int
{
	if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
		return null;
	}
	$zstd = fractal_zip::zstd_executable();
	if ($zstd === null) {
		return null;
	}
	$zl = getenv('FRACTAL_ZIP_BENCH_ZSTD_LEVEL');
	$lev = ($zl === false || trim((string) $zl) === '') ? 19 : max(1, min(22, (int) $zl));
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzstfzbd_' . bin2hex(random_bytes(8));
	$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzstfzbd_' . bin2hex(random_bytes(8)) . '.zst';
	fzbd_bench_remove_dir($box);
	mkdir($box, 0755, true);
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$sub = $it->getSubPathname();
		$target = $box . DIRECTORY_SEPARATOR . $sub;
		if ($item->isDir()) {
			mkdir($target, 0755, true);
		} else {
			$parent = dirname($target);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			copy($item->getPathname(), $target);
		}
	}
	$cwd = getcwd();
	$ret = -1;
	if (chdir($box)) {
		$cmd = 'tar cf - . 2>/dev/null | ' . escapeshellarg($zstd) . ' -' . $lev . fractal_zip::bench_zstd_thread_shell_fragment_for_exec() . ' -c > ' . escapeshellarg($out) . ' 2>/dev/null';
		exec($cmd, $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	$n = ($ret === 0 && is_file($out)) ? filesize($out) : null;
	if (is_file($out)) {
		unlink($out);
	}
	fzbd_bench_remove_dir($box);
	return ($n !== false && $n !== null) ? (int) $n : null;
}

// --- same corpus as test_fzbd_raster_dedup.php ---
if (!extension_loaded('gd') || !function_exists('imagecreatetruecolor') || !function_exists('imagebmp')) {
	fwrite(STDERR, "SKIP: need PHP GD with imagebmp.\n");
	exit(0);
}

$im = imagecreatetruecolor(8, 8);
if ($im === false) {
	fwrite(STDERR, "FAIL: imagecreatetruecolor\n");
	exit(1);
}
imagesavealpha($im, true);
$bg = imagecolorallocatealpha($im, 40, 80, 120, 0);
imagefill($im, 0, 0, $bg);
ob_start();
imagepng($im);
$pngBytes = ob_get_clean();
ob_start();
imagebmp($im);
$bmpBytes = ob_get_clean();
imagedestroy($im);

$rawTotal = strlen($pngBytes) + strlen($bmpBytes);
$work = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzbdvs_' . bin2hex(random_bytes(6));
mkdir($work, 0755, true);
file_put_contents($work . DIRECTORY_SEPARATOR . 'a.png', $pngBytes);
file_put_contents($work . DIRECTORY_SEPARATOR . 'b.bmp', $bmpBytes);

$gzip9 = fzbd_bench_gzip9_bundle_bytes($work);
$z7 = fzbd_bench_seven_zip_folder_bytes($work);
$zst = fzbd_bench_tar_zstd_bytes($work);

$files = array(
	'a.png' => $pngBytes,
	'b.bmp' => $bmpBytes,
);
$fz = new fractal_zip();
$fzbInner = $fz->encode_literal_bundle_payload($files);
if (!is_string($fzbInner)) {
	fzbd_bench_remove_dir($work);
	fwrite(STDERR, "FAIL: encode_literal_bundle_payload\n");
	exit(1);
}
$fzbInnerLen = strlen($fzbInner);
$fzbFinalized = $fz->finalize_literal_bundle_inner_for_compress($fzbInner);
$fzbFinalLen = strlen($fzbFinalized);

ob_start();
list($fzBestBlob, $fzWhich) = $fz->choose_smaller_adaptive_bundle_or_raw_escaped($fzbInner, $files);
ob_end_clean();
$fzBestLen = strlen($fzBestBlob);
$codec = fractal_zip::$last_written_container_codec;
if (!is_string($codec) || $codec === '') {
	$codec = fractal_zip::$last_outer_codec ?? '?';
}

fzbd_bench_remove_dir($work);

$pct = static function (int $num, int $den): string {
	if ($den <= 0) {
		return '—';
	}
	return sprintf('%+.1f%%', 100.0 * ($num - $den) / $den);
};

fwrite(STDOUT, "Corpus: a.png + b.bmp (same 8×8 raster, distinct encodings)\n\n");
fwrite(STDOUT, "Uncompressed:\n");
fwrite(STDOUT, sprintf("  Raw file bytes (sum):      %8d\n", $rawTotal));
fwrite(STDOUT, sprintf("  FZB inner (auto policy):   %8d  (before finalize/FZWS)\n", $fzbInnerLen));
fwrite(STDOUT, sprintf("  FZB after finalize:        %8d  (+ optional FZWS whole-stream)\n", $fzbFinalLen));

fwrite(STDOUT, "\nCompressed (bytes on wire):\n");
fwrite(STDOUT, sprintf("  Bench gzip9 bundle:        %8d\n", $gzip9));
if ($z7 !== null) {
	fwrite(STDOUT, sprintf("  7z -mx=9 -m0=lzma2:        %8d\n", $z7));
} else {
	fwrite(STDOUT, "  7z -mx=9 -m0=lzma2:        (7z not on PATH)\n");
}
if ($zst !== null) {
	fwrite(STDOUT, sprintf("  tar | zstd (bench level):  %8d\n", $zst));
} else {
	fwrite(STDOUT, "  tar | zstd:                 (zstd not on PATH or not Unix)\n");
}
fwrite(STDOUT, sprintf("  FZ best (bundle vs raw):   %8d  winner=%s outer≈%s\n", $fzBestLen, $fzWhich, $codec));

fwrite(STDOUT, "\nChecks:\n");
if ($fzBestLen < $rawTotal) {
	fwrite(STDOUT, sprintf("  OK: FZ best (%d) < raw sum (%d)  (%s)\n", $fzBestLen, $rawTotal, $pct($fzBestLen, $rawTotal)));
} else {
	fwrite(STDOUT, sprintf("  FAIL: FZ best (%d) should be < raw sum (%d)\n", $fzBestLen, $rawTotal));
	exit(1);
}
if ($fzbInnerLen < $rawTotal) {
	fwrite(STDOUT, sprintf("  OK: FZB inner alone (%d) < raw sum (%d)\n", $fzbInnerLen, $rawTotal));
} else {
	fwrite(STDOUT, sprintf("  Note: FZB inner (%d) ≥ raw (%d) — compression wins on outer path (expected on tiny structured bundles).\n", $fzbInnerLen, $rawTotal));
}

fwrite(STDOUT, "\nVersus bench gzip9 bundle:\n");
fwrite(STDOUT, sprintf("  FZ best:                   %s\n", $pct($fzBestLen, $gzip9)));
if ($z7 !== null) {
	fwrite(STDOUT, sprintf("  FZ best vs 7z:              %s\n", $pct($fzBestLen, $z7)));
}
if ($zst !== null) {
	fwrite(STDOUT, sprintf("  FZ best vs tar|zstd:       %s\n", $pct($fzBestLen, $zst)));
}

exit(0);
