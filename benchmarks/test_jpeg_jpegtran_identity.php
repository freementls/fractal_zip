<?php
declare(strict_types=1);

/**
 * Test: can jpegtran losslessly re-wrap JPEGs byte-for-byte?
 *
 * Uses: jpegtran -copy all [-progressive] (libjpeg-turbo / IJG jpegtran on PATH).
 * Does NOT use -optimize (that re-Huffmanizes and usually changes bytes).
 *
 * Finding on test_files54_sample: most baseline JPEGs match; some progressive / Exif-heavy
 * files differ after jpegtran (different segment layout or re-entropy).
 *
 *   php benchmarks/test_jpeg_jpegtran_identity.php [dir]
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$dir = $argc >= 2 ? $argv[1] : $repo . DIRECTORY_SEPARATOR . 'test_files54_sample';
if ($dir[0] !== '/' && !str_starts_with($dir, $repo)) {
	$dir = $repo . DIRECTORY_SEPARATOR . $dir;
}
if (!is_dir($dir)) {
	fwrite(STDERR, "Not a directory: {$dir}\n");
	exit(2);
}

$null = shell_exec('command -v jpegtran 2>/dev/null');
if (!is_string($null) || trim($null) === '') {
	fwrite(STDERR, "jpegtran not found on PATH.\n");
	exit(2);
}

$ident = 0;
$diff = 0;
$fail = 0;
$diffPaths = [];

$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $info) {
	if (!$info->isFile()) {
		continue;
	}
	$ext = strtolower($info->getExtension());
	if ($ext !== 'jpg' && $ext !== 'jpeg') {
		continue;
	}
	$path = $info->getPathname();
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzjpeg_id_' . bin2hex(random_bytes(8)) . '.jpg';
	$isProg = fractal_zip_bench_jpeg_file_looks_progressive($path);
	$args = $isProg ? '-copy all -progressive' : '-copy all';
	$cmd = 'jpegtran ' . $args . ' -outfile ' . escapeshellarg($tmp) . ' ' . escapeshellarg($path) . ' 2>/dev/null';
	$ret = 0;
	exec($cmd, $void, $ret);
	if ($ret !== 0 || !is_file($tmp)) {
		$fail++;
		@unlink($tmp);
		continue;
	}
	$a = file_get_contents($path);
	$b = file_get_contents($tmp);
	@unlink($tmp);
	if (!is_string($a) || !is_string($b)) {
		$fail++;
		continue;
	}
	if ($a === $b) {
		$ident++;
	} else {
		$diff++;
		if (count($diffPaths) < 32) {
			$diffPaths[] = array(
				'rel' => str_replace($dir . DIRECTORY_SEPARATOR, '', $path),
				'in' => strlen($a),
				'out' => strlen($b),
				'progressive_guess' => $isProg,
			);
		}
	}
}

echo "dir={$dir}\n";
echo "jpegtran_identity=" . $ident . " different=" . $diff . " fail=" . $fail . "\n";
if ($diffPaths !== []) {
	echo "sample_different (up to 32):\n";
	foreach ($diffPaths as $row) {
		echo "  {$row['rel']} in={$row['in']} out={$row['out']} progressive_probe={$row['progressive_guess']}\n";
	}
}

/**
 * Cheap probe: first 64 KiB contains progressive JPEG SOF2 marker scan (0xFF 0xC2).
 */
function fractal_zip_bench_jpeg_file_looks_progressive(string $path): bool {
	$h = @fopen($path, 'rb');
	if ($h === false) {
		return false;
	}
	$chunk = fread($h, 65536);
	fclose($h);
	if (!is_string($chunk) || strlen($chunk) < 4) {
		return false;
	}
	return str_contains($chunk, "\xFF\xC2");
}
