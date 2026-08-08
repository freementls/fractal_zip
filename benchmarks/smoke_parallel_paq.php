#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$toolDir = $repo . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'parallel_paq';
$bin = $toolDir . DIRECTORY_SEPARATOR . 'parallel_paq';

if (!is_executable($bin)) {
	$build = $toolDir . DIRECTORY_SEPARATOR . 'build.sh';
	if (!is_file($build)) {
		fwrite(STDERR, "missing build.sh\n");
		exit(1);
	}
	echo "building parallel_paq...\n";
	passthru('bash ' . escapeshellarg($build), $rc);
	if ($rc !== 0 || !is_executable($bin)) {
		fwrite(STDERR, "build failed\n");
		exit(1);
	}
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';

$samplePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_smoke_' . bin2hex(random_bytes(4)) . '.txt';
$sample = str_repeat(
	"The quick brown fox jumps over the lazy dog. Enwik8-style English text for parallel PAQ smoke.\n",
	800
);
file_put_contents($samplePath, $sample);
$rawLen = strlen($sample);
echo 'sample_bytes=' . $rawLen . "\n";

$run = static function (string $binPath, int $jobs, string $in, string $out): array {
	$cmd = escapeshellarg($binPath) . ' compress -j' . (int) $jobs
		. ' -o ' . escapeshellarg($out)
		. ' ' . escapeshellarg($in) . ' 2>&1';
	$t0 = microtime(true);
	exec($cmd, $lines, $ret);
	$sec = microtime(true) - $t0;
	if ($ret !== 0) {
		fwrite(STDERR, implode("\n", $lines) . "\n");
	}
	return array('ret' => $ret, 'sec' => $sec, 'out' => $out);
};

$arc1 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_j1_' . bin2hex(random_bytes(4)) . '.fzpp';
$arc4 = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_j4_' . bin2hex(random_bytes(4)) . '.fzpp';
$r1 = $run($bin, 1, $samplePath, $arc1);
$r4 = $run($bin, 4, $samplePath, $arc4);
if ($r1['ret'] !== 0 || $r4['ret'] !== 0) {
	exit(1);
}
$comp1 = filesize($arc1);
$comp4 = filesize($arc4);
echo 'compress_j1_sec=' . round($r1['sec'], 4) . ' compressed_bytes=' . $comp1 . "\n";
echo 'compress_j4_sec=' . round($r4['sec'], 4) . ' compressed_bytes=' . $comp4 . "\n";
$speedup = ($r4['sec'] > 0.0) ? round($r1['sec'] / $r4['sec'], 2) : 0.0;
echo 'speedup_j4_vs_j1=' . $speedup . "x\n";

$gz = gzencode($sample, 9);
$gzLen = is_string($gz) ? strlen($gz) : 0;
echo 'gzip9_bytes=' . $gzLen . ' ratio_vs_gzip9=' . round($comp1 / max(1, $gzLen), 3) . "x\n";

$decPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_dec_' . bin2hex(random_bytes(4)) . '.txt';
$decCmd = escapeshellarg($bin) . ' decompress -j4 -o ' . escapeshellarg($decPath)
	. ' ' . escapeshellarg($arc4) . ' 2>&1';
exec($decCmd, $dlines, $dret);
if ($dret !== 0) {
	fwrite(STDERR, implode("\n", $dlines) . "\n");
	exit(1);
}
$back = file_get_contents($decPath);
if ($back !== $sample) {
	fwrite(STDERR, "roundtrip mismatch\n");
	exit(1);
}
echo "roundtrip_ok=1\n";

$tools = fractal_zip_paq_discover_tools();
putenv('FRACTAL_ZIP_PAQ_TOOLS=parallel_cmix:parallel_phda9');
$par = fractal_zip_paq_discover_tools();
echo 'php_discovered=' . (count($par) ? implode(',', array_keys($par)) : '(none)') . "\n";
if (!isset($par['parallel_cmix'])) {
	fwrite(STDERR, "parallel_cmix not discovered by PHP\n");
	exit(1);
}

$phpArc = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_php_' . bin2hex(random_bytes(4)) . '.fzpp';
$phpDec = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_php_dec_' . bin2hex(random_bytes(4)) . '.txt';
$cr = fractal_zip_paq_compress_file('parallel_cmix', $par['parallel_cmix'], $samplePath);
if (!is_string($cr['bytes']) || $cr['bytes'] === '') {
	fwrite(STDERR, "PHP compress failed\n");
	exit(1);
}
file_put_contents($phpArc, $cr['bytes']);
$ok = fractal_zip_paq_decompress_to_file('parallel_cmix', $par['parallel_cmix'], $phpArc, $phpDec);
if (!$ok || file_get_contents($phpDec) !== $sample) {
	fwrite(STDERR, "PHP roundtrip failed\n");
	exit(1);
}
echo "php_wire_roundtrip_ok=1\n";

$dict = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.phda9_external_dict.txt';
if (isset($par['parallel_phda9']) && is_file($dict)) {
	$phArc = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_phda_' . bin2hex(random_bytes(4)) . '.fzpp';
	$phDec = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpp_phda_dec_' . bin2hex(random_bytes(4)) . '.txt';
	$jobs = fractal_zip_paq_parallel_jobs();
	$cmd = escapeshellarg($par['parallel_phda9']) . ' compress -j' . $jobs
		. ' --dict ' . escapeshellarg($dict)
		. ' -o ' . escapeshellarg($phArc) . ' ' . escapeshellarg($samplePath);
	exec($cmd, $pl, $pr);
	$dcmd = escapeshellarg($par['parallel_phda9']) . ' decompress -j' . $jobs
		. ' --dict ' . escapeshellarg($dict)
		. ' -o ' . escapeshellarg($phDec) . ' ' . escapeshellarg($phArc);
	exec($dcmd, $dl2, $dr);
	if ($pr !== 0 || $dr !== 0 || file_get_contents($phDec) !== $sample) {
		fwrite(STDERR, "parallel_phda9 dict roundtrip failed\n");
		exit(1);
	}
	echo 'parallel_phda9_dict_bytes=' . filesize($phArc) . " roundtrip_ok=1\n";
	@unlink($phArc);
	@unlink($phDec);
}

echo "smoke_parallel_paq ok\n";

@unlink($samplePath);
@unlink($arc1);
@unlink($arc4);
@unlink($decPath);
@unlink($phpArc);
@unlink($phpDec);
