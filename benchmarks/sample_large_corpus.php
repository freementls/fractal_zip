<?php
declare(strict_types=1);

/**
 * Build a smaller corpus from a large test_files* tree by copying a stratified sample:
 * - Keeps every "small" file (default ≤ 512 KiB) so sidecars / manifests stay represented.
 * - From each "large" extension group (default: same extension as the largest total-byte group),
 *   picks files spread evenly across sorted order (stable, reproducible) until a raw-byte target is met.
 *
 * Usage (from repo root):
 *   php benchmarks/sample_large_corpus.php test_files58 test_files58_sample --target-mib=72
 *   php benchmarks/sample_large_corpus.php test_files59 test_files59_sample
 *   php benchmarks/sample_large_corpus.php test_files59 test_files59_sample --target-mib=72
 *   php benchmarks/sample_large_corpus.php test_files59 test_files59_sample --dry-run
 *   php benchmarks/sample_large_corpus.php test_files133 test_files133_sample --target-mib=28   # Silesia x4 slice (see SILESIA_BENCHMARK.md)
 *   php benchmarks/sample_large_corpus.php test_files55 test_files55_stratified --target-mib=72   # HTML site slice (see build_test_files55_sample.php)
 *
 * Then:
 *   php benchmarks/run_benchmarks.php --only=test_files59_sample --no-verify ...
 *   On big slices, pair with **`benchmarks/LARGE_CORPUS_SPEED.md`** (`--jobs`, **`--bench-profile`**, **`bash benchmarks/run_large_corpus_bytes_push.sh`**).
 *
 * Verify note: default bench verify is strict per-file SHA1 (see run_benchmarks.php header). Stratified slices that mix
 * huge HTML trees (e.g. test_files58 → test_files58_sample) or FLAC-heavy trees (test_files59 → test_files59_sample) may
 * still report bytes while `verify_ok=false` (verification ran and failed) until the underlying round-trip issue is understood; use
 * `php benchmarks/sha1_tree_diff.php <corpus_dir> <extract_dir>` on a preserved extract tree to list offending paths
 * (use `php benchmarks/run_benchmarks.php --only=<dir> --keep-verify-extract=/path …` so the driver copies each label’s post-verify tree under `/path/<label>/`).
 * With **`--no-verify`**, machine JSON uses **`verify_ok` / `verify_mismatch_files` null** (verification skipped), not false / 0.
 *
 * For test_files54 (raster-heavy trees), benchmarks/sample_test_files54.php stratifies png/gif/jpg/webp explicitly.
 */

if ($argc < 3) {
	fwrite(STDERR, "Usage: php sample_large_corpus.php <source_dir> <dest_dir> [--target-mib=N] [--small-max-kib=N] [--min-large-files=N] [--dry-run]\n");
	exit(2);
}

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$srcArg = $argv[1];
$dstArg = $argv[2];
$src = $srcArg[0] === '/' || strncmp($srcArg, $repoRoot, strlen($repoRoot)) === 0
	? $srcArg
	: $repoRoot . DIRECTORY_SEPARATOR . $srcArg;
$dst = $dstArg[0] === '/' ? $dstArg : $repoRoot . DIRECTORY_SEPARATOR . $dstArg;

$targetMib = 56.0;
$smallMaxKib = 512;
$minLargeFiles = 2;
$dryRun = false;

for ($i = 3; $i < $argc; $i++) {
	$a = $argv[$i];
	if (preg_match('/^--target-mib=([\d.]+)$/', $a, $m)) {
		$targetMib = max(1.0, (float) $m[1]);
	} elseif (preg_match('/^--small-max-kib=(\d+)$/', $a, $m)) {
		$smallMaxKib = max(0, (int) $m[1]);
	} elseif (preg_match('/^--min-large-files=(\d+)$/', $a, $m)) {
		$minLargeFiles = max(0, (int) $m[1]);
	} elseif ($a === '--dry-run') {
		$dryRun = true;
	}
}

$srcReal = realpath($src);
if ($srcReal === false || !is_dir($srcReal)) {
	fwrite(STDERR, "Source is not a directory: {$src}\n");
	exit(1);
}

$targetBytes = (int) round($targetMib * 1024 * 1024);
$smallMaxBytes = $smallMaxKib * 1024;

/** @var list<array{rel: string, full: string, size: int, ext: string}> $entries */
$entries = [];
$it = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($srcReal, FilesystemIterator::SKIP_DOTS)
);
$prefixLen = strlen($srcReal);
foreach ($it as $fileInfo) {
	if (!$fileInfo->isFile()) {
		continue;
	}
	$full = $fileInfo->getPathname();
	$rel = substr($full, $prefixLen);
	if ($rel !== '' && ($rel[0] === '/' || $rel[0] === '\\')) {
		$rel = substr($rel, 1);
	}
	$rel = str_replace('\\', '/', $rel);
	$sz = $fileInfo->getSize();
	if ($sz === false) {
		continue;
	}
	$ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
	$entries[] = ['rel' => $rel, 'full' => $full, 'size' => (int) $sz, 'ext' => $ext];
}

if ($entries === []) {
	fwrite(STDERR, "No files under source.\n");
	exit(1);
}

$small = [];
$large = [];
foreach ($entries as $e) {
	if ($e['size'] <= $smallMaxBytes) {
		$small[] = $e;
	} else {
		$large[] = $e;
	}
}

/** @var array<string, array{list<array{rel: string, full: string, size: int, ext: string}>, int}> $byExt */
$byExt = [];
foreach ($large as $e) {
	$ext = $e['ext'] !== '' ? $e['ext'] : '_empty_ext_';
	if (!isset($byExt[$ext])) {
		$byExt[$ext] = ['files' => [], 'bytes' => 0];
	}
	$byExt[$ext]['files'][] = $e;
	$byExt[$ext]['bytes'] += $e['size'];
}

if ($byExt === []) {
	$chosenLarge = [];
} else {
	$dominantExt = null;
	$dominantBytes = -1;
	foreach ($byExt as $ext => $info) {
		if ($info['bytes'] > $dominantBytes) {
			$dominantBytes = $info['bytes'];
			$dominantExt = $ext;
		}
	}
	if ($dominantExt === null) {
		fwrite(STDERR, "Internal: no dominant extension bucket.\n");
		exit(1);
	}
	$pool = $byExt[$dominantExt]['files'];
	usort($pool, static fn ($a, $b) => strcmp($a['rel'], $b['rel']));
	$count = count($pool);

	$baseBytes = 0;
	foreach ($small as $e) {
		$baseBytes += $e['size'];
	}

	$n = $minLargeFiles;
	if ($n > $count) {
		$n = $count;
	}

	$spreadIndices = static function (int $c, int $nn): array {
		if ($nn <= 0) {
			return [];
		}
		if ($nn >= $c) {
			return range(0, $c - 1);
		}
		if ($nn === 1) {
			return [0];
		}
		$idx = [];
		for ($i = 0; $i < $nn; $i++) {
			$idx[] = (int) round($i * ($c - 1) / ($nn - 1));
		}
		return array_values(array_unique($idx));
	};

	$sumChosen = static function (array $poolUse, array $indices): int {
		$s = 0;
		foreach ($indices as $i) {
			$s += $poolUse[$i]['size'];
		}
		return $s;
	};

	$indices = $spreadIndices($count, $n);
	while ($baseBytes + $sumChosen($pool, $indices) < $targetBytes && $n < $count) {
		$n++;
		$indices = $spreadIndices($count, $n);
	}

	if ($baseBytes + $sumChosen($pool, $indices) < $targetBytes && $n >= $count) {
		$indices = range(0, $count - 1);
	}

	$chosenLarge = [];
	foreach ($indices as $i) {
		$chosenLarge[] = $pool[$i];
	}

	$otherLarge = [];
	foreach ($large as $e) {
		$bucket = $e['ext'] !== '' ? $e['ext'] : '_empty_ext_';
		if ($bucket === $dominantExt) {
			continue;
		}
		$otherLarge[] = $e;
	}
	foreach ($otherLarge as $e) {
		$chosenLarge[] = $e;
	}
}

$plan = array_merge($small, $chosenLarge);
usort($plan, static fn ($a, $b) => strcmp($a['rel'], $b['rel']));

$planBytes = 0;
foreach ($plan as $e) {
	$planBytes += $e['size'];
}

$byExtPlan = [];
foreach ($plan as $e) {
	$ex = $e['ext'] !== '' ? $e['ext'] : '(no ext)';
	$byExtPlan[$ex] = ($byExtPlan[$ex] ?? 0) + 1;
}
$totalFiles = count($entries);
$rawTotal = 0;
foreach ($entries as $e) {
	$rawTotal += $e['size'];
}

fwrite(STDERR, sprintf(
	"Sample plan: %d of %d files, %s MiB of %s MiB raw (%.1f%% files, %.1f%% bytes)\n",
	count($plan),
	$totalFiles,
	number_format($planBytes / (1024 * 1024), 2),
	number_format($rawTotal / (1024 * 1024), 2),
	100.0 * count($plan) / max(1, $totalFiles),
	100.0 * $planBytes / max(1, $rawTotal)
));
fwrite(STDERR, 'By extension (file count in sample): ' . (bench_json_encode_try($byExtPlan, false) ?? '<json_encode failed>') . "\n");

if ($dryRun) {
	foreach ($plan as $e) {
		echo $e['rel'] . "\t" . $e['size'] . "\n";
	}
	exit(0);
}

if (is_dir($dst)) {
	fwrite(STDERR, "Destination already exists; remove it first: {$dst}\n");
	exit(1);
}

foreach ($plan as $e) {
	$outPath = $dst . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $e['rel']);
	$outDir = dirname($outPath);
	if (!is_dir($outDir) && !mkdir($outDir, 0755, true)) {
		fwrite(STDERR, "mkdir failed: {$outDir}\n");
		exit(1);
	}
	if (!copy($e['full'], $outPath)) {
		fwrite(STDERR, "copy failed: {$e['rel']}\n");
		exit(1);
	}
}

fwrite(STDERR, "Wrote sample to {$dst}\n");
