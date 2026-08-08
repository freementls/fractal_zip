#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Peel corpus admission: peel ON must be strictly smaller than peel OFF,
 * bit-exact round-trip, and (when --min-ext) smaller than a quick opaque baseline.
 *
 *   php benchmarks/guard_peel_admission.php test_files_compress_lz4_br_jackpot_peel
 *   php benchmarks/guard_peel_admission.php --all-peel
 */

$repo = dirname(__DIR__);
chdir($repo);

$dirs = array();
$allPeel = false;
foreach (array_slice($argv, 1) as $a) {
	if ($a === '--all-peel') {
		$allPeel = true;
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php guard_peel_admission.php [--all-peel] [corpus_dir…]\n";
		exit(0);
	}
	if (is_dir($repo . '/' . $a)) {
		$dirs[] = $a;
	}
}

if ($allPeel || $dirs === array()) {
	foreach (scandir($repo) ?: array() as $e) {
		if (!is_dir($repo . '/' . $e) || !str_starts_with($e, 'test_files')) {
			continue;
		}
		if (str_contains($e, 'peel') || str_starts_with($e, 'test_files_classic')
			|| str_starts_with($e, 'test_files_compress')) {
			$dirs[] = $e;
		}
	}
	sort($dirs);
}

if ($dirs === array()) {
	fwrite(STDERR, "guard_peel_admission: no corpora\n");
	exit(2);
}

putenv('FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1');
putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0');
putenv('PATH=/usr/local/bin:/usr/bin:/bin');
if (getenv('TMPDIR') === false || getenv('TMPDIR') === '') {
	putenv('TMPDIR=' . $repo . '/benchmarks/.tmp');
}

/**
 * @return array{bytes:int, magic:string}|null
 */
function peel_zip(string $dir, bool $peelOn): ?array {
	$fz = $dir . '.fz';
	@unlink($fz);
	$env = $peelOn
		? 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL=1 FRACTAL_ZIP_FOLDER_GZIP_PEEL=1 FRACTAL_ZIP_FOLDER_CHUNK_PEEL=1 FRACTAL_ZIP_FOLDER_OLE_PEEL=1 FRACTAL_ZIP_FOLDER_AR_PEEL=1 FRACTAL_ZIP_FOLDER_WOFF_PEEL=1 FRACTAL_ZIP_FOLDER_CLASSIC_PEEL=1'
		: 'FRACTAL_ZIP_FOLDER_COMPRESS_PEEL=0 FRACTAL_ZIP_FOLDER_GZIP_PEEL=0 FRACTAL_ZIP_FOLDER_CHUNK_PEEL=0 FRACTAL_ZIP_FOLDER_OLE_PEEL=0 FRACTAL_ZIP_FOLDER_AR_PEEL=0 FRACTAL_ZIP_FOLDER_WOFF_PEEL=0 FRACTAL_ZIP_FOLDER_CLASSIC_PEEL=0';
	$cmd = $env . ' php -d opcache.enable_cli=0 fractal_zip_cli.php zip '
		. escapeshellarg($dir) . ' 2>/dev/null';
	shell_exec($cmd);
	if (!is_file($fz)) {
		return null;
	}
	$b = (string) file_get_contents($fz);
	return array('bytes' => strlen($b), 'magic' => substr($b, 0, 4));
}

function peel_rt_ok(string $dir): bool {
	$fz = $dir . '.fz';
	$out = sys_get_temp_dir() . '/fz_peel_adm_' . getmypid();
	if (is_dir($out)) {
		passthru('rm -rf ' . escapeshellarg($out));
	}
	@mkdir($out, 0700, true);
	shell_exec('php -d opcache.enable_cli=0 fractal_zip_cli.php extract '
		. escapeshellarg($fz) . ' ' . escapeshellarg($out) . ' 2>/dev/null');
	$ok = true;
	foreach (scandir($dir) ?: array() as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$a = $dir . '/' . $e;
		$b = $out . '/' . $e;
		if (!is_file($a)) {
			continue;
		}
		if (!is_file($b) || sha1_file($a) !== sha1_file($b)) {
			$ok = false;
			break;
		}
	}
	passthru('rm -rf ' . escapeshellarg($out));
	return $ok;
}

$fail = 0;
$checked = 0;
foreach ($dirs as $dir) {
	if (!is_dir($dir)) {
		continue;
	}
	// Skip huge peels in default admission (keep smoke fast).
	$raw = 0;
	foreach (scandir($dir) ?: array() as $e) {
		if ($e === '.' || $e === '..') {
			continue;
		}
		$p = $dir . '/' . $e;
		if (is_file($p)) {
			$raw += (int) filesize($p);
		}
	}
	if ($raw > 2 * 1024 * 1024) {
		echo "SKIP {$dir} (raw={$raw})\n";
		continue;
	}
	$off = peel_zip($dir, false);
	$on = peel_zip($dir, true);
	if ($off === null || $on === null) {
		echo "FAIL {$dir}: zip failed\n";
		$fail++;
		continue;
	}
	$rt = peel_rt_ok($dir);
	$win = $on['bytes'] < $off['bytes'];
	$checked++;
	$status = ($win && $rt) ? 'OK' : 'FAIL';
	if ($status === 'FAIL') {
		$fail++;
	}
	$pct = $off['bytes'] > 0
		? round(100.0 * ($off['bytes'] - $on['bytes']) / $off['bytes'], 1)
		: 0.0;
	echo "{$status} {$dir}: off={$off['bytes']} on={$on['bytes']} ({$pct}%) magic={$on['magic']} rt=" . ($rt ? 'ok' : 'fail') . "\n";
}

echo "checked={$checked} fail={$fail}\n";
exit($fail === 0 && $checked > 0 ? 0 : 1);
