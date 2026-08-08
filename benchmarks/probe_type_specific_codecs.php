#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Per-type codec probe for remaining ext+fzc ties.
 *
 * Usage:
 *   php benchmarks/probe_type_specific_codecs.php [--fzc] [--fixtures=a,b] [--out=path.json]
 *
 * Codecs: paq8px@{4,6,8}, mcm, lpaq9l, zpaq5, and optional current fzc zip_folder.
 */

$root = dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';

$wantFzc = in_array('--fzc', $argv, true);
$outPath = $root . '/benchmarks/.type_specific_codec_probe.json';
$only = null;
foreach ($argv as $a) {
	if (str_starts_with($a, '--out=')) {
		$outPath = substr($a, 6);
	}
	if (str_starts_with($a, '--fixtures=')) {
		$only = array_filter(array_map('trim', explode(',', substr($a, 11))));
	}
}

$fixtures = array(
	'test_files105' => array('file' => 'alice29.txt', 'kind' => 'prose'),
	'test_files106' => array('file' => 'asyoulik.txt', 'kind' => 'prose'),
	'test_files115' => array('file' => 'lcet10.txt', 'kind' => 'prose'),
	'test_files122' => array('file' => 'plrabn12.txt', 'kind' => 'prose'),
	'test_files49' => array('file' => 'phpinfo.html', 'kind' => 'html'),
	'test_files107' => array('file' => 'cp.html', 'kind' => 'html'),
	'test_files128' => array('file' => 'urls.10K', 'kind' => 'urls'),
	'test_files114' => array('file' => 'kennedy.xls', 'kind' => 'binary'),
	'test_files123' => array('file' => 'ptt5', 'kind' => 'binary'),
	'test_files127' => array('file' => 'sum', 'kind' => 'binary'),
	'test_files53' => array('file' => null, 'kind' => 'folder'),
);

if (is_array($only) && $only !== array()) {
	$fixtures = array_intersect_key($fixtures, array_flip($only));
}

$priorTie = array(
	'test_files105' => 37600,
	'test_files106' => 35379,
	'test_files115' => 90021,
	'test_files122' => 127578,
	'test_files49' => 20301,
	'test_files107' => 6830,
	'test_files128' => 135189,
	'test_files114' => 16822,
	'test_files123' => 28563,
	'test_files127' => 9509,
	'test_files53' => 74167,
);

/**
 * @return array{bytes: ?int, enc_s: float, dec_s: float, rt: bool, err?: string}
 */
function probe_codec_file(string $toolId, string $inputPath, ?int $paqLevel = null): array
{
	$prevLvl = getenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
	if ($paqLevel !== null) {
		putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=' . (string) $paqLevel);
	}
	$exe = fractal_zip_paq_discover_executable($toolId === 'zpaq5' ? 'zpaq5' : $toolId);
	if ($exe === null && $toolId === 'zpaq5') {
		$exe = fractal_zip_paq_zpaq_executable();
	}
	if ($exe === null) {
		if ($prevLvl === false) {
			putenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
		} else {
			putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=' . $prevLvl);
		}
		return array('bytes' => null, 'enc_s' => 0.0, 'dec_s' => 0.0, 'rt' => false, 'err' => 'missing');
	}
	$plain = (string) file_get_contents($inputPath);
	$plainSha = hash('sha256', $plain);
	$t0 = microtime(true);
	$arc = null;
	if ($toolId === 'mcm') {
		$r = fractal_zip_paq_mcm_compress_bytes($plain);
		$arc = is_array($r) ? ($r['bytes'] ?? null) : null;
	} elseif ($toolId === 'lpaq9l') {
		$r = fractal_zip_paq_lpaq_compress_bytes($plain);
		$arc = is_array($r) ? ($r['bytes'] ?? null) : null;
	} elseif ($toolId === 'zpaq5') {
		$r = fractal_zip_paq_zpaq_compress_bytes($plain, 5);
		$arc = is_array($r) ? ($r['bytes'] ?? null) : null;
	} else {
		$r = fractal_zip_paq_compress_file($toolId, $exe, $inputPath);
		$arc = is_array($r) ? ($r['bytes'] ?? null) : null;
	}
	$encS = round(microtime(true) - $t0, 4);
	if ($prevLvl === false) {
		putenv('FRACTAL_ZIP_PAQ8PX_LEVEL');
	} else {
		putenv('FRACTAL_ZIP_PAQ8PX_LEVEL=' . $prevLvl);
	}
	if (!is_string($arc) || $arc === '') {
		return array('bytes' => null, 'enc_s' => $encS, 'dec_s' => 0.0, 'rt' => false, 'err' => 'compress_fail');
	}
	$t1 = microtime(true);
	$dec = null;
	if ($toolId === 'mcm') {
		$dec = fractal_zip_paq_mcm_decompress_bytes($arc);
	} elseif ($toolId === 'lpaq9l') {
		$dec = fractal_zip_paq_lpaq_decompress_bytes($arc);
	} elseif ($toolId === 'zpaq5') {
		$dec = fractal_zip_paq_zpaq_decompress_bytes($arc, 'zpaq5');
	} else {
		$tmp = sys_get_temp_dir() . '/fzprobe_' . bin2hex(random_bytes(4));
		@mkdir($tmp, 0700, true);
		$arcPath = $tmp . '/arc.bin';
		$outPath = $tmp . '/out.bin';
		file_put_contents($arcPath, $arc);
		$ok = fractal_zip_paq_decompress_to_file($toolId, $exe, $arcPath, $outPath);
		$dec = ($ok && is_file($outPath)) ? (string) file_get_contents($outPath) : null;
		@unlink($arcPath);
		@unlink($outPath);
		@rmdir($tmp);
	}
	$decS = round(microtime(true) - $t1, 4);
	$rt = is_string($dec) && hash_equals($plainSha, hash('sha256', $dec));
	return array('bytes' => strlen($arc), 'enc_s' => $encS, 'dec_s' => $decS, 'rt' => $rt);
}

/**
 * @return array{bytes: ?int, enc_s: float, dec_s: float, rt: bool, err?: string}
 */
function probe_fzc_folder(string $dir): array
{
	$root = dirname(__DIR__);
	require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	$fzc = $dir . '.fzc';
	@unlink($fzc);
	$t0 = microtime(true);
	try {
		(new fractal_zip())->zip_folder($dir, false);
	} catch (Throwable $e) {
		return array('bytes' => null, 'enc_s' => round(microtime(true) - $t0, 4), 'dec_s' => 0.0, 'rt' => false, 'err' => $e->getMessage());
	}
	$encS = round(microtime(true) - $t0, 4);
	if (!is_file($fzc)) {
		return array('bytes' => null, 'enc_s' => $encS, 'dec_s' => 0.0, 'rt' => false, 'err' => 'no_fzc');
	}
	$bytes = (int) filesize($fzc);
	$td = sys_get_temp_dir() . '/fzprobe_ext_' . bin2hex(random_bytes(4));
	@mkdir($td, 0700, true);
	$t1 = microtime(true);
	try {
		(new fractal_zip())->unzip($fzc, $td);
	} catch (Throwable $e) {
		return array('bytes' => $bytes, 'enc_s' => $encS, 'dec_s' => round(microtime(true) - $t1, 4), 'rt' => false, 'err' => 'unzip:' . $e->getMessage());
	}
	$decS = round(microtime(true) - $t1, 4);
	$rt = true;
	$srcRoot = realpath($dir);
	$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($srcRoot, FilesystemIterator::SKIP_DOTS));
	foreach ($it as $fi) {
		if (!$fi->isFile()) {
			continue;
		}
		$rel = ltrim(str_replace('\\', '/', substr($fi->getPathname(), strlen($srcRoot))), '/');
		$a = hash_file('sha256', $fi->getPathname());
		$bPath = $td . '/' . $rel;
		$b = is_file($bPath) ? hash_file('sha256', $bPath) : '';
		if (!hash_equals((string) $a, (string) $b)) {
			$rt = false;
			break;
		}
	}
	$rm = static function (string $d) use (&$rm): void {
		foreach (glob($d . '/{,.}[!.,!..]*', GLOB_BRACE) ?: array() as $p) {
			is_dir($p) && !is_link($p) ? $rm($p) : @unlink($p);
		}
		@rmdir($d);
	};
	$rm($td);
	return array('bytes' => $bytes, 'enc_s' => $encS, 'dec_s' => $decS, 'rt' => $rt);
}

$rows = array();
printf("%-16s %-12s %10s %8s %8s %4s %s\n", 'fixture', 'codec', 'bytes', 'enc_s', 'dec_s', 'rt', 'vs_tie');
foreach ($fixtures as $name => $meta) {
	$dir = $root . '/' . $name;
	if (!is_dir($dir)) {
		fwrite(STDERR, "missing $name\n");
		continue;
	}
	$file = $meta['file'];
	$path = $file !== null ? $dir . '/' . $file : null;
	$codecs = array();
	if ($path !== null && is_file($path)) {
		// Levels 1–4 cover the practical wall/bytes frontier; 6/8 are multi-minute
		// on ~1 MiB record/image payloads and are opt-in via --deep.
		$deep = in_array('--deep', $argv, true);
		foreach (($deep ? array(1, 2, 3, 4, 6, 8) : array(1, 2, 3, 4)) as $lvl) {
			$codecs[] = array('id' => 'paq8px' . $lvl, 'tool' => 'paq8px', 'lvl' => $lvl);
		}
		$codecs[] = array('id' => 'mcm', 'tool' => 'mcm', 'lvl' => null);
		$codecs[] = array('id' => 'lpaq9l', 'tool' => 'lpaq9l', 'lvl' => null);
		$codecs[] = array('id' => 'zpaq5', 'tool' => 'zpaq5', 'lvl' => null);
	}
	foreach ($codecs as $c) {
		$r = probe_codec_file($c['tool'], $path, $c['lvl']);
		$tie = $priorTie[$name] ?? null;
		$delta = ($r['bytes'] !== null && $tie !== null) ? ($r['bytes'] - $tie) : null;
		$tag = $delta === null ? '' : sprintf('%+d', $delta);
		printf("%-16s %-12s %10s %8.3f %8.3f %4s %s\n",
			$name, $c['id'],
			$r['bytes'] !== null ? number_format($r['bytes']) : '—',
			$r['enc_s'], $r['dec_s'], $r['rt'] ? 'ok' : 'fail',
			$tag . (isset($r['err']) ? ' ' . $r['err'] : '')
		);
		@ob_flush();
		@flush();
		$rows[] = array('fixture' => $name, 'kind' => $meta['kind'], 'codec' => $c['id'], 'result' => $r, 'prior_tie' => $tie);
	}
	if ($wantFzc) {
		$r = probe_fzc_folder($dir);
		$tie = $priorTie[$name] ?? null;
		$delta = ($r['bytes'] !== null && $tie !== null) ? ($r['bytes'] - $tie) : null;
		$tag = $delta === null ? '' : sprintf('%+d', $delta);
		printf("%-16s %-12s %10s %8.3f %8.3f %4s %s\n",
			$name, 'fzc',
			$r['bytes'] !== null ? number_format($r['bytes']) : '—',
			$r['enc_s'], $r['dec_s'], $r['rt'] ? 'ok' : 'fail',
			$tag . (isset($r['err']) ? ' ' . $r['err'] : '')
		);
		$rows[] = array('fixture' => $name, 'kind' => $meta['kind'], 'codec' => 'fzc', 'result' => $r, 'prior_tie' => $tie);
	}
}

$payload = array(
	'generated_at' => gmdate('c'),
	'prior_tie_bytes' => $priorTie,
	'rows' => $rows,
);
file_put_contents($outPath, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
fprintf(STDERR, "wrote %s (%d rows)\n", $outPath, count($rows));
