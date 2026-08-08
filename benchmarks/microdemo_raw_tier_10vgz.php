#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Synthetic scaling demo: N files named synth_0.vgz … with gzip-wrapped low-entropy payloads.
 * Compares adaptive outer size of the raw-tier escaped-per-file container using disk bytes vs deep-unwrapped bytes.
 *
 *   php benchmarks/microdemo_raw_tier_10vgz.php [N]
 * Default N=10.
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$n = 10;
if (isset($argv[1]) && ctype_digit((string) $argv[1])) {
	$n = max(1, min(500, (int) $argv[1]));
}

$payload = str_repeat("\x00\x01\x02\x03", 4096); // 16 KiB per member (same as single-file demo)
$onDiskOne = gzencode($payload, 9);

$f = new fractal_zip(256, false, false, null, false);
fractal_zip_ensure_literal_pac_stack_loaded();

$rawDisk = [];
$rawUn = [];
$sumDiskMembers = 0;
$sumUnwrappedMembers = 0;

for ($i = 0; $i < $n; $i++) {
	$rel = 'synth_' . $i . '.vgz';
	$disk = $onDiskOne;
	$sumDiskMembers += strlen($disk);
	list($work) = fractal_zip_literal_deep_unwrap_with_layers($rel, $disk);
	$sumUnwrappedMembers += strlen($work);
	$rawDisk[$rel] = $disk;
	$rawUn[$rel] = $work;
}

$buildEsc = static function (fractal_zip $fz, array $pathToBytes): array {
	$esc = [];
	foreach ($pathToBytes as $rel => $bytes) {
		$esc[(string) $rel] = $fz->escape_literal_for_storage((string) $bytes);
	}
	ksort($esc, SORT_STRING);
	return $esc;
};

$escDisk = $buildEsc($f, $rawDisk);
$escUn = $buildEsc($f, $rawUn);
$pDisk = $f->encode_container_payload($escDisk, '');
$pUn = $f->encode_container_payload($escUn, '');
ob_start();
$cDisk = $f->adaptive_compress($pDisk);
ob_end_clean();
ob_start();
$cUn = $f->adaptive_compress($pUn);
ob_end_clean();
$lenDisk = strlen($cDisk);
$lenUn = strlen($cUn);

echo "microdemo_raw_tier_10vgz: one raw-tier container with N={$n} paths synth_0.vgz … (each gzip wraps 16 KiB low-entropy payload)\n";
echo "Per-member on-disk gzip size: " . strlen($onDiskOne) . " B\n";
echo "Sum of on-disk member bytes:     {$sumDiskMembers} B\n";
echo "Sum of unwrapped member bytes:   {$sumUnwrappedMembers} B\n";
echo "--- Single encode_container_payload + adaptive_compress (folder-style raw inner):\n";
echo "Outer bytes (disk in raw tier):     {$lenDisk} B\n";
echo "Outer bytes (unwrapped in tier):    {$lenUn} B\n";
echo "Delta (unwrapped − disk):           " . ($lenUn - $lenDisk) . " B\n";
echo "Ratio unwrapped/disk:               " . ($lenDisk > 0 ? round($lenUn / $lenDisk, 4) : '—') . "\n";
