#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Stratified sample of test_files54 for fast iteration: many small sidecars + bounded bytes
 * per raster extension (png, gif, jpg, webp) with even spread on sorted paths.
 *
 * Usage (repo root):
 *   php benchmarks/sample_test_files54.php [dest_name] [--target-mib=14] [--small-max-kib=192] [--dry-run]
 *
 * Default dest: test_files54_sample (under repo root). Remove dest dir before re-running.
 */

if ($argc >= 2 && strncmp($argv[1], '--', 2) === 0) {
	$dstName = 'test_files54_sample';
	$arg0 = 1;
} else {
	$dstName = $argv[1] ?? 'test_files54_sample';
	$arg0 = 2;
}

$repoRoot = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$src = $repoRoot . DIRECTORY_SEPARATOR . 'test_files54';
$dst = $repoRoot . DIRECTORY_SEPARATOR . $dstName;

$targetMib = 14.0;
$smallMaxKib = 192;
$dryRun = false;

for ($i = $arg0; $i < $argc; $i++) {
	$a = $argv[$i];
	if (preg_match('/^--target-mib=([\d.]+)$/', $a, $m)) {
		$targetMib = max(4.0, (float) $m[1]);
	} elseif (preg_match('/^--small-max-kib=(\d+)$/', $a, $m)) {
		$smallMaxKib = max(32, (int) $m[1]);
	} elseif ($a === '--dry-run') {
		$dryRun = true;
	}
}

$targetBytes = (int) round($targetMib * 1024 * 1024);
$smallMaxBytes = $smallMaxKib * 1024;

$rasterExts = ['png', 'gif', 'jpg', 'jpeg', 'webp'];
$budgetPerRaster = (int) floor($targetBytes * 0.22);

$srcReal = realpath($src);
if ($srcReal === false || !is_dir($srcReal)) {
	fwrite(STDERR, "Missing source dir: {$src}\n");
	exit(1);
}

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

$small = [];
$largeRaster = array_fill_keys($rasterExts, []);
$largeOther = [];
foreach ($entries as $e) {
	if ($e['size'] <= $smallMaxBytes) {
		$small[] = $e;
		continue;
	}
	$ex = $e['ext'];
	if ($ex === 'jpeg') {
		$ex = 'jpg';
	}
	if (isset($largeRaster[$ex])) {
		$largeRaster[$ex][] = $e;
	} else {
		$largeOther[] = $e;
	}
}

foreach ($small as &$e) {
	if ($e['ext'] === 'jpeg') {
		$e['ext'] = 'jpg';
	}
}
unset($e);

usort($small, static fn ($a, $b) => $a['size'] <=> $b['size'] ?: strcmp($a['rel'], $b['rel']));

$chosen = [];
$chosenRel = [];
$add = static function (array $e) use (&$chosen, &$chosenRel): void {
	if (isset($chosenRel[$e['rel']])) {
		return;
	}
	$chosen[] = $e;
	$chosenRel[$e['rel']] = true;
};

$smallBudget = (int) round($targetBytes * 0.18);
$sb = 0;
foreach ($small as $e) {
	if ($sb + $e['size'] > $smallBudget && $sb > 0) {
		break;
	}
	$add($e);
	$sb += $e['size'];
}

$spreadPick = static function (array $pool, int $budgetBytes): array {
	if ($pool === [] || $budgetBytes < 1) {
		return [];
	}
	usort($pool, static fn ($a, $b) => strcmp($a['rel'], $b['rel']));
	$c = count($pool);
	$out = [];
	$sum = 0;
	for ($n = 1; $n <= $c; $n++) {
		$idx = [];
		if ($n === 1) {
			$idx = [(int) floor($c / 2)];
		} else {
			for ($i = 0; $i < $n; $i++) {
				$idx[] = (int) round($i * ($c - 1) / max(1, $n - 1));
			}
			$idx = array_values(array_unique($idx));
		}
		$trial = [];
		$trialSum = 0;
		foreach ($idx as $i) {
			$trial[] = $pool[$i];
			$trialSum += $pool[$i]['size'];
		}
		$out = $trial;
		$sum = $trialSum;
		if ($sum >= $budgetBytes || $n === $c) {
			break;
		}
	}
	return $out;
};

foreach (['png', 'gif', 'jpg', 'webp'] as $rex) {
	$pool = $largeRaster[$rex] ?? [];
	foreach ($spreadPick($pool, $budgetPerRaster) as $e) {
		$add($e);
	}
}

$remain = $targetBytes;
foreach ($chosen as $e) {
	$remain -= $e['size'];
}
if ($remain > 512 * 1024) {
	usort($largeOther, static fn ($a, $b) => strcmp($a['rel'], $b['rel']));
	foreach ($largeOther as $e) {
		if ($remain - $e['size'] < 0 && count($chosen) > 50) {
			break;
		}
		$add($e);
		$remain -= $e['size'];
		if ($remain <= 0) {
			break;
		}
	}
}

usort($chosen, static fn ($a, $b) => strcmp($a['rel'], $b['rel']));
$planBytes = 0;
$byExt = [];
foreach ($chosen as $e) {
	$planBytes += $e['size'];
	$ex = $e['ext'] !== '' ? $e['ext'] : '(no ext)';
	$byExt[$ex] = ($byExt[$ex] ?? 0) + 1;
}

fwrite(STDERR, sprintf(
	"test_files54 → %s: %d files, ~%.2f MiB (target ~%.1f MiB)\n",
	$dstName,
	count($chosen),
	$planBytes / (1024 * 1024),
	$targetMib
));
fwrite(STDERR, 'By extension (count): ' . (bench_json_encode_try($byExt, false) ?? '<json_encode failed>') . "\n");

if ($dryRun) {
	foreach ($chosen as $e) {
		echo $e['rel'] . "\t" . $e['size'] . "\n";
	}
	exit(0);
}

if (is_dir($dst)) {
	fwrite(STDERR, "Destination exists; remove first: {$dst}\n");
	exit(1);
}

foreach ($chosen as $e) {
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

fwrite(STDERR, "Wrote {$dst}\n");
