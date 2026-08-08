#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Build `test_files72_sample`: a size-stratified, PDF-diverse slice of `test_files72` for
 * short pdf_literal_pac_empirical / PDF iteration (flat directory of .pdf copies).
 *
 * Design:
 *  - All PDFs under the source tree are read; leading bytes must look like a PDF; optional version tag
 *    from the header (%PDF-1.x) for a printed summary and light variety checks.
 *  - Files are sorted by size, split into B equal-count bins. From each bin we take the **smallest**
 *    and **largest** in that bin (up to 2*B picks) so the collection spans the full size spectrum
 *    (tiny "pamphlet" to huge scans) without taking everything.
 *  - --max-mib limits monster files (default: keep iteration wall time reasonable for jpegtran pass).
 *  - --target-total-mib trims from the largest picked files until under the total budget.
 *
 * From repo root (full corpus; remove dest before re-run):
 *   php benchmarks/build_test_files72_sample.php --dry-run
 *   rm -rf test_files72_sample && php benchmarks/build_test_files72_sample.php
 *   php benchmarks/build_test_files72_sample.php --write-manifest=benchmarks/test_files72_sample.json
 *
 *   Micro (≤3 files, e.g. each ≤4 MiB) for sub-minute PAC trials:
 *   rm -rf test_files72_sample_micro && php benchmarks/build_test_files72_sample.php --file-cap=3 --max-mib=4 --out=test_files72_sample_micro --min-files=1
 *
 * Then (iterate on PDF literal-PAC; quick vs full):
 *   php benchmarks/pdf_literal_pac_empirical.php test_files72_sample --limit=2 --per-step
 *   php benchmarks/pdf_literal_pac_empirical.php test_files72_sample --all-modes
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$src = $repo . DIRECTORY_SEPARATOR . 'test_files72';
$outName = 'test_files72_sample';
$maxMib = 14.0;
$bins = 6;
$targetTotalMib = 45.0;
$minFiles = 6;
$fileCap = null;
$dryRun = false;
$writeManifest = null;

for ($i = 1; $i < $argc; $i++) {
	$a = $argv[$i];
	if (preg_match('/^--out=([\w.\-]+)$/', $a, $m) === 1) {
		$outName = (string) $m[1];
	} elseif (preg_match('/^--max-mib=([\d.]+)$/', $a, $m)) {
		$maxMib = max(0.5, (float) $m[1]);
	} elseif (preg_match('/^--bins=(\d+)$/', $a, $m)) {
		$bins = max(2, min(24, (int) $m[1]));
	} elseif (preg_match('/^--target-total-mib=([\d.]+)$/', $a, $m)) {
		$targetTotalMib = max(1.0, (float) $m[1]);
	} elseif (preg_match('/^--min-files=(\d+)$/', $a, $m)) {
		$minFiles = max(1, (int) $m[1]);
	} elseif (preg_match('/^--file-cap=(\d+)$/', $a, $m) === 1) {
		$fileCap = max(1, (int) $m[1]);
	} elseif ($a === '--dry-run') {
		$dryRun = true;
	} elseif (preg_match('/^--write-manifest=(.+)$/', $a, $m) === 1) {
		$writeManifest = (string) $m[1];
	} elseif ($a === '--help' || $a === '-h') {
		echo "Usage: php build_test_files72_sample.php [--out=dirname] [--max-mib=N] [--file-cap=K] [--bins=N] [--target-total-mib=N] [--min-files=N] [--dry-run] [--write-manifest=path.json]\n"
			. "  --file-cap=K  take K PDFs at size percentiles 0%…100% (skips --bins; good with --max-mib=4 for a tiny set).\n";
		exit(0);
	} else {
		fwrite(STDERR, "Unknown arg: {$a}\n");
		exit(2);
	}
}

$dst = $repo . DIRECTORY_SEPARATOR . $outName;
if ($fileCap !== null) {
	$minFiles = min($minFiles, 1);
}

$maxBytes = (int) round($maxMib * 1024 * 1024);
$targetBytes = (int) round($targetTotalMib * 1024 * 1024);

$srcReal = realpath($src);
if ($srcReal === false || !is_dir($srcReal)) {
	fwrite(STDERR, "Source directory missing: {$src} (put the full test_files72 tree here)\n");
	exit(1);
}

/** @var list<array{rel: string, full: string, size: int, ver: string}> $cands */
$cands = [];
$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($srcReal, FilesystemIterator::SKIP_DOTS)
);
$prefixLen = strlen($srcReal);
foreach ($it as $fileInfo) {
	if (!$fileInfo->isFile()) {
		continue;
	}
	$full = $fileInfo->getPathname();
	$baseName = $fileInfo->getBasename();
	if (! str_ends_with(strtolower($baseName), '.pdf')) {
		continue;
	}
	$sz = $fileInfo->getSize();
	if ($sz === false) {
		continue;
	}
	$zi = (int) $sz;
	if ($zi > $maxBytes) {
		continue;
	}
	$head = (string) @file_get_contents($full, false, null, 0, 8192);
	if ($head === '' || strncmp($head, '%PDF-', 5) !== 0) {
		continue;
	}
	$ver = '?';
	if (preg_match('/%PDF-(\d\.\d)/', $head, $mv) === 1) {
		$ver = (string) $mv[1];
	}
	$rel = substr($full, $prefixLen);
	if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
		$rel = substr($rel, 1);
	}
	$rel = str_replace('\\', '/', $rel);
	$cands[] = ['rel' => $rel, 'full' => $full, 'size' => $zi, 'ver' => $ver];
}

if (count($cands) < 1) {
	fwrite(STDERR, 'No PDFs (after %PDF- + max-mib): 0' . "\n");
	exit(1);
}

usort($cands, static function (array $a, array $b): int {
	return $a['size'] <=> $b['size'] ?: strcmp($a['rel'], $b['rel']);
});
$n = count($cands);

$chosen = [];
$chosenRel = [];
$add = static function (array $e) use (&$chosen, &$chosenRel): void {
	if (isset($chosenRel[$e['rel']])) {
		return;
	}
	$chosen[] = $e;
	$chosenRel[$e['rel']] = true;
};

if ($fileCap !== null) {
	$k = min($fileCap, $n);
	for ($i = 0; $i < $k; $i++) {
		$ix = $k === 1 ? 0 : (int) round($i * ( $n - 1) / ( $k - 1) );
		$add($cands[ $ix]);
	}
} else {
	if ($n < $minFiles) {
		fwrite(STDERR, 'Not enough PDFs (after %PDF- + max-mib): ' . (string) $n . "\n");
		exit(1);
	}
	for ($b = 0; $b < $bins; $b++) {
		$lo = (int) floor($b * $n / $bins);
		$hi = (int) floor(( $b + 1) * $n / $bins) - 1;
		if ($hi < $lo) {
			continue;
		}
		$add($cands[ $lo]);
		if ($hi > $lo) {
			$add($cands[ $hi]);
		}
	}
}
$trimMin = $fileCap !== null ? 1 : $minFiles;

$sumB = 0;
foreach ($chosen as $c) {
	$sumB += $c['size'];
}
$trimRounds = 0;
while ($sumB > $targetBytes && count($chosen) > $trimMin) {
	$largestI = 0;
	for ($i = 1, $c = count($chosen); $i < $c; $i++) {
		if ($chosen[$i]['size'] > $chosen[$largestI]['size']) {
			$largestI = $i;
		}
	}
	$rem = $chosen[$largestI];
	unset($chosenRel[$rem['rel']]);
	array_splice($chosen, $largestI, 1);
	$sumB -= $rem['size'];
	$trimRounds++;
}

usort($chosen, static fn (array $a, array $b) => sprintf('%010d', $a['size']) . $a['rel'] <=> sprintf('%010d', $b['size']) . $b['rel']);

$verCount = [];
foreach ($chosen as $c) {
	$v = $c['ver'];
	$verCount[$v] = ($verCount[$v] ?? 0) + 1;
}
ksort($verCount, SORT_STRING);

$planB = 0;
foreach ($chosen as $c) {
	$planB += $c['size'];
}

$dstName = basename($dst);
fwrite(STDERR, "Source: test_files72 ({$n} ≤ max-mib PDFs) → `{$dstName}`: " . count($chosen) . " files, " . number_format($planB / 1048576, 2) . " MiB");
if ($trimRounds > 0) {
	fwrite(STDERR, "  (dropped LARGEST to meet target-total-mib; rounds={$trimRounds})");
}
fwrite(STDERR, "\n" . 'PDF-1.x header counts: ' . (bench_json_encode_try($verCount, false) ?? '<json_encode failed>') . "\n");
if ($fileCap !== null) {
	fwrite(STDERR, "  file_cap={$fileCap}  (quantile pick)  max_mib={$maxMib}  target_total_mib={$targetTotalMib}\n");
} else {
	fwrite(STDERR, "  bins={$bins} max_mib={$maxMib} target_total_mib={$targetTotalMib} min_files={$minFiles}\n");
}

if ($dryRun) {
	foreach ($chosen as $c) {
		printf("%8d  %3s  %s\n", $c['size'], $c['ver'], $c['rel']);
	}
	exit(0);
}

if (is_dir($dst)) {
	fwrite(STDERR, "Destination already exists; remove and retry: rm -rf {$dstName}\n");
	exit(1);
}
if (!@mkdir($dst, 0755) && !is_dir($dst)) {
	fwrite(STDERR, "mkdir failed: {$dst}\n");
	exit(1);
}

$seenNames = [];
$manifestList = array();
foreach ($chosen as $c) {
	$leaf = basename($c['rel']);
	if (isset($seenNames[$leaf])) {
		$h = substr(sha1($c['rel']), 0, 8);
		$leaf = pathinfo($leaf, PATHINFO_FILENAME) . '_' . $h . '.pdf';
	}
	$seenNames[$leaf] = true;
	$out = $dst . DIRECTORY_SEPARATOR . $leaf;
	if (!@copy($c['full'], $out)) {
		fwrite(STDERR, "copy failed: {$c['rel']}\n");
		exit(1);
	}
	$manifestList[] = array(
		'source_rel' => $c['rel'],
		'sample_name' => $leaf,
		'bytes' => $c['size'],
		'header_pdf_version' => $c['ver'],
	);
}
fwrite(STDERR, "Wrote {$dst}\n");
if (is_string($writeManifest) && $writeManifest !== '') {
	$mp = ($writeManifest[0] === '/' || (PHP_OS_FAMILY === 'Windows' && preg_match('/^([A-Za-z]:[\\\\/]|\\\\)/', $writeManifest) === 1))
		? $writeManifest
		: $repo . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $writeManifest);
	$manifest = array(
		'generator' => 'build_test_files72_sample.php',
		'created' => gmdate('c'),
		'params' => array(
			'out' => $outName,
			'max_mib' => $maxMib,
			'file_cap' => $fileCap,
			'bins' => $bins,
			'target_total_mib' => $targetTotalMib,
			'min_files' => $minFiles,
		),
		'source_pdf_count' => $n,
		'output_dir' => basename($dst),
		'files' => $manifestList,
	);
	$mj = bench_json_encode_try($manifest, true);
	if ($mj === null) {
		fwrite(STDERR, 'json_encode manifest failed: ' . json_last_error_msg() . "\n");
		exit(1);
	}
	if (file_put_contents($mp, $mj . "\n") === false) {
		fwrite(STDERR, "write manifest failed: {$mp}\n");
		exit(1);
	}
	fwrite(STDERR, "Wrote manifest {$mp}\n");
}
