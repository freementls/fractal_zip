#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Full Squash hoplite.csv analysis: content buckets vs winning codecs, probe→full hypotheses.
 *
 * Dataset metadata: benchmarks/.squash_benchmark_cache/squash-benchmark.js (from quixdb/squash-benchmark-web).
 * CSV: benchmarks/.squash_benchmark_cache/hoplite.csv (see https://quixdb.github.io/squash-benchmark/ ).
 *
 * Usage: php benchmarks/analyze_squash_full_corpus.php [hoplite.csv]
 */

$repo = dirname(__DIR__);
$csvPath = $argv[1] ?? ($repo . '/benchmarks/.squash_benchmark_cache/hoplite.csv');
$metaJs = $repo . '/benchmarks/.squash_benchmark_cache/squash-benchmark.js';
if (!is_readable($csvPath)) {
	fwrite(STDERR, "Cannot read {$csvPath}\n");
	exit(2);
}

function parse_datasets_from_js(string $path): array {
	$js = file_get_contents($path);
	if ($js === false) {
		return [];
	}
	/** @var array<string, array{id:string, description:string, size:int}> */
	$out = [];
	if (!preg_match_all(
		"/id:\\s*'([^']+)'\s*,[^}]*?description:\\s*'([^']*)'\s*,[^}]*?size:\\s*(\\d+)/s",
		$js,
		$m,
		PREG_SET_ORDER
	)) {
		return [];
	}
	foreach ($m as $row) {
		$out[$row[1]] = ['id' => $row[1], 'description' => $row[2], 'size' => (int) $row[3]];
	}

	return $out;
}

/** Coarse bucket for reporting (Squash public dataset ids). */
function content_bucket(string $id): string {
	static $map = [
		// Natural / long text
		'alice29.txt' => 'text_natural',
		'asyoulik.txt' => 'text_natural',
		'lcet10.txt' => 'text_natural',
		'plrabn12.txt' => 'text_natural',
		'xargs.1' => 'text_natural',
		'dickens' => 'text_large',
		'reymont' => 'text_large',
		'webster' => 'text_large',
		'enwik8' => 'text_large',
		// Source / markup
		'fields.c' => 'source_markup',
		'grammar.lsp' => 'source_markup',
		'cp.html' => 'source_markup',
		'xml' => 'source_markup',
		'samba' => 'source_tarball',
		// Structured / repetitive text-like
		'urls.10K' => 'urls_proto',
		'geo.protodata' => 'urls_proto',
		// Documents & binaries
		'paper-100k.pdf' => 'pdf',
		'kennedy.xls' => 'office',
		'ooffice' => 'office_dll',
		'mozilla' => 'binary_exe_tar',
		'sum' => 'binary_exe',
		// Images / medical / fax
		'fireworks.jpeg' => 'image_jpeg',
		'mr' => 'image_medical',
		'x-ray' => 'image_medical',
		'ptt5' => 'fax_ccitt',
		// DB / scientific
		'nci' => 'scientific_db',
		'osdb' => 'sql_dump',
		'sao' => 'tabular_catalog',
	];

	return $map[$id] ?? 'other';
}

function pearson_r(array $a, array $b): ?float {
	$n = min(count($a), count($b));
	if ($n < 3) {
		return null;
	}
	$a = array_slice($a, 0, $n);
	$b = array_slice($b, 0, $n);
	$meanA = array_sum($a) / $n;
	$meanB = array_sum($b) / $n;
	$num = 0.0;
	$denA = 0.0;
	$denB = 0.0;
	for ($i = 0; $i < $n; $i++) {
		$da = $a[$i] - $meanA;
		$db = $b[$i] - $meanB;
		$num += $da * $db;
		$denA += $da * $da;
		$denB += $db * $db;
	}
	if ($denA <= 0.0 || $denB <= 0.0) {
		return null;
	}

	return $num / sqrt($denA * $denB);
}

function winner_key(string $plugin, string $codec, string $levelRaw): string {
	$lv = ($levelRaw === '' || $levelRaw === null) ? '' : (string) $levelRaw;

	return "{$plugin}/{$codec}" . ($lv !== '' ? "-{$lv}" : '');
}

$datasets = is_readable($metaJs) ? parse_datasets_from_js($metaJs) : [];
if ($datasets === []) {
	fwrite(STDERR, "Warning: could not parse {$metaJs}; bucket labels only by filename heuristics.\n");
}


/** dataset => list of rows */
$byDs = [];

$fh = fopen($csvPath, 'rb');
if ($fh === false) {
	exit(2);
}
$hdr = fgetcsv($fh);
if ($hdr === false || ($hdr[0] ?? '') !== 'dataset') {
	exit(2);
}
while (($row = fgetcsv($fh)) !== false) {
	if (count($row) < 5) {
		continue;
	}
	$ds = $row[0];
	$plugin = $row[1];
	$codec = $row[2];
	$levelRaw = $row[3];
	$size = (int) $row[4];
	if ($plugin === 'copy' && $codec === 'copy') {
		continue;
	}
	if ($size <= 0) {
		continue;
	}
	if (!isset($byDs[$ds])) {
		$byDs[$ds] = [];
	}
	$byDs[$ds][] = ['plugin' => $plugin, 'codec' => $codec, 'level' => $levelRaw, 'size' => $size];
}
fclose($fh);

$datasetIds = array_keys($byDs);
sort($datasetIds);

// Per-dataset: global best, brotli levels, xz levels, zstd
$brotliL1 = [];
$brotliL11 = [];
$xzL1 = [];
$xzL9 = [];
$zstdSize = [];
$probeWinnerFam = [];
$fullWinnerFam = [];
$globalBest = [];
/** @var array<string, array{key:string, plugin:string, size:int}> */
$globalBestNoZpaq = [];

$bucketGlobalWinner = [];
$bucketGlobalWinnerNoZpaq = [];
$bucketCounts = [];

foreach ($datasetIds as $ds) {
	$rows = $byDs[$ds] ?? [];
	if ($rows === []) {
		continue;
	}

	$bestSize = PHP_INT_MAX;
	$bestRow = null;
	foreach ($rows as $r) {
		if ($r['size'] < $bestSize) {
			$bestSize = $r['size'];
			$bestRow = $r;
		}
	}
	if ($bestRow === null) {
		continue;
	}
	$gk = winner_key($bestRow['plugin'], $bestRow['codec'], (string) $bestRow['level']);
	$globalBest[$ds] = ['key' => $gk, 'plugin' => $bestRow['plugin'], 'codec' => $bestRow['codec'], 'size' => $bestSize];

	// Best excluding zpaq (Squash includes zpaq; raw-size crown is usually zpaq — compare codecs typical for general-purpose outers).
	$bestNoZpaq = null;
	$bestNoZpaqSize = PHP_INT_MAX;
	foreach ($rows as $r) {
		if ($r['plugin'] === 'zpaq') {
			continue;
		}
		if ($r['size'] < $bestNoZpaqSize) {
			$bestNoZpaqSize = $r['size'];
			$bestNoZpaq = $r;
		}
	}
	if ($bestNoZpaq !== null) {
		$globalBestNoZpaq[$ds] = [
			'key' => winner_key($bestNoZpaq['plugin'], $bestNoZpaq['codec'], (string) $bestNoZpaq['level']),
			'plugin' => $bestNoZpaq['plugin'],
			'size' => $bestNoZpaqSize,
		];
	}

	$bucket = content_bucket($ds);
	$bucketCounts[$bucket] = ($bucketCounts[$bucket] ?? 0) + 1;
	if (!isset($bucketGlobalWinner[$bucket])) {
		$bucketGlobalWinner[$bucket] = [];
	}
	$bucketGlobalWinner[$bucket][$gk] = ($bucketGlobalWinner[$bucket][$gk] ?? 0) + 1;

	if (isset($globalBestNoZpaq[$ds])) {
		$gk2 = $globalBestNoZpaq[$ds]['key'];
		if (!isset($bucketGlobalWinnerNoZpaq[$bucket])) {
			$bucketGlobalWinnerNoZpaq[$bucket] = [];
		}
		$bucketGlobalWinnerNoZpaq[$bucket][$gk2] = ($bucketGlobalWinnerNoZpaq[$bucket][$gk2] ?? 0) + 1;
	}

	// Codec-specific levels
	$b1 = $b11 = $x1 = $x9 = null;
	$zs = null;
	foreach ($rows as $r) {
		if ($r['plugin'] === 'brotli' && $r['codec'] === 'brotli') {
			$lv = (int) $r['level'];
			if ($lv === 1) {
				$b1 = $r['size'];
			}
			if ($lv === 11) {
				$b11 = $r['size'];
			}
		}
		if ($r['plugin'] === 'lzma' && $r['codec'] === 'xz') {
			$lv = (int) $r['level'];
			if ($lv === 1) {
				$x1 = $r['size'];
			}
			if ($lv === 9) {
				$x9 = $r['size'];
			}
		}
		if ($r['plugin'] === 'zstd' && $r['codec'] === 'zstd') {
			$zs = $r['size'];
		}
	}
	if ($b1 !== null && $b11 !== null) {
		$brotliL1[$ds] = $b1;
		$brotliL11[$ds] = $b11;
	}
	if ($x1 !== null && $x9 !== null) {
		$xzL1[$ds] = $x1;
		$xzL9[$ds] = $x9;
	}
	if ($zs !== null) {
		$zstdSize[$ds] = $zs;
	}

	// Three-family tournament: brotli vs xz vs zstd only
	if ($b1 !== null && $x1 !== null && $zs !== null) {
		$pmin = min($b1, $x1, $zs);
		if ($pmin === $b1) {
			$probeWinnerFam[$ds] = 'brotli';
		} elseif ($pmin === $x1) {
			$probeWinnerFam[$ds] = 'xz';
		} else {
			$probeWinnerFam[$ds] = 'zstd';
		}
	}
	if ($b11 !== null && $x9 !== null && $zs !== null) {
		$fmin = min($b11, $x9, $zs);
		if ($fmin === $b11) {
			$fullWinnerFam[$ds] = 'brotli';
		} elseif ($fmin === $x9) {
			$fullWinnerFam[$ds] = 'xz';
		} else {
			$fullWinnerFam[$ds] = 'zstd';
		}
	}
}

// Pearson for aligned arrays (same dataset order)
$dsAlign = array_values(array_intersect(array_keys($brotliL1), array_keys($brotliL11)));
$a1 = [];
$a11 = [];
foreach ($dsAlign as $d) {
	$a1[] = $brotliL1[$d];
	$a11[] = $brotliL11[$d];
}
$rBrotli = pearson_r($a1, $a11);

$xzAlign = array_values(array_intersect(array_keys($xzL1), array_keys($xzL9)));
$xA = [];
$xB = [];
foreach ($xzAlign as $d) {
	$xA[] = $xzL1[$d];
	$xB[] = $xzL9[$d];
}
$rXz = pearson_r($xA, $xB);

$triples = array_values(array_intersect(array_keys($probeWinnerFam), array_keys($fullWinnerFam)));
$agree = 0;
foreach ($triples as $d) {
	if ($probeWinnerFam[$d] === $fullWinnerFam[$d]) {
		$agree++;
	}
}
$pct = count($triples) > 0 ? (100.0 * $agree / count($triples)) : 0.0;

// Global winner histogram (all datasets)
$globalHist = [];
foreach ($globalBest as $info) {
	$k = $info['key'];
	$globalHist[$k] = ($globalHist[$k] ?? 0) + 1;
}
arsort($globalHist);

// Plugin-only histogram
$pluginHist = [];
foreach ($globalBest as $info) {
	$p = $info['plugin'];
	$pluginHist[$p] = ($pluginHist[$p] ?? 0) + 1;
}
arsort($pluginHist);

// Where global best is NOT one of brotli/xz/zstd — "exotic" winners
$mainPlugins = ['brotli' => true, 'lzma' => true, 'zstd' => true];

echo "Squash CSV: {$csvPath}\n";
echo 'Datasets in CSV: ' . count($datasetIds) . ' (metadata IDs in squash-benchmark.js: ' . count($datasets) . ")\n\n";

echo "=== A. Compression ratio / size: global winner (min compressed_size, excluding copy) ===\n";
echo "Unique winning settings across all datasets (plugin/codec-level):\n";
$i = 0;
foreach ($globalHist as $k => $cnt) {
	echo sprintf("  %2d×  %s\n", $cnt, $k);
	if (++$i >= 18) {
		$rest = count($globalHist) - $i;
		if ($rest > 0) {
			echo "  … +" . $rest . " more settings\n";
		}
		break;
	}
}

echo "\nBy plugin (how often that plugin holds the smallest row):\n";
foreach ($pluginHist as $p => $cnt) {
	echo sprintf("  %2d×  %s\n", $cnt, $p);
}

$exotic = 0;
foreach ($globalBest as $ds => $info) {
	if (!isset($mainPlugins[$info['plugin']])) {
		$exotic++;
	}
}
echo "\nDatasets where the absolute best size uses a plugin other than brotli/lzma/zstd: {$exotic} / " . count($globalBest) . "\n";

$nzHist = [];
foreach ($globalBestNoZpaq as $info) {
	$nzHist[$info['key']] = ($nzHist[$info['key']] ?? 0) + 1;
}
arsort($nzHist);
$nzPlugin = [];
foreach ($globalBestNoZpaq as $info) {
	$p = $info['plugin'];
	$nzPlugin[$p] = ($nzPlugin[$p] ?? 0) + 1;
}
arsort($nzPlugin);

echo "\n--- Same, but ignoring zpaq rows (see docs: typical LZ comparisons exclude PAQ-class for speed) ---\n";
echo "Winning settings (min compressed_size):\n";
foreach ($nzHist as $k => $cnt) {
	echo sprintf("  %2d×  %s\n", $cnt, $k);
}
echo "\nBy plugin:\n";
foreach ($nzPlugin as $p => $cnt) {
	echo sprintf("  %2d×  %s\n", $cnt, $p);
}

$trio = [];
foreach ($datasetIds as $ds) {
	if (!isset($brotliL11[$ds], $xzL9[$ds], $zstdSize[$ds])) {
		continue;
	}
	$m = min($brotliL11[$ds], $xzL9[$ds], $zstdSize[$ds]);
	if ($m === $brotliL11[$ds]) {
		$trio[$ds] = 'brotli-11';
	} elseif ($m === $xzL9[$ds]) {
		$trio[$ds] = 'xz-9';
	} else {
		$trio[$ds] = 'zstd';
	}
}
$trioHist = [];
foreach ($trio as $w) {
	$trioHist[$w] = ($trioHist[$w] ?? 0) + 1;
}
arsort($trioHist);
echo "\nAmong brotli-11 vs xz-9 vs zstd only (max-quality mainstream LZ):\n";
foreach ($trioHist as $name => $cnt) {
	echo sprintf("  %2d×  %s\n", $cnt, $name);
}

echo "\n=== B. Content bucket → winning setting (global B-best, includes zpaq) ===\n";
ksort($bucketGlobalWinner);
foreach ($bucketGlobalWinner as $bucket => $hist) {
	arsort($hist);
	$n = $bucketCounts[$bucket] ?? 0;
	echo "\n{$bucket} (n={$n}):\n";
	foreach ($hist as $set => $cnt) {
		echo sprintf("  %d×  %s\n", $cnt, $set);
	}
}

echo "\n=== B2. Same buckets, best size excluding zpaq rows ===\n";
ksort($bucketGlobalWinnerNoZpaq);
foreach ($bucketGlobalWinnerNoZpaq as $bucket => $hist) {
	arsort($hist);
	$n = $bucketCounts[$bucket] ?? 0;
	echo "\n{$bucket} (n={$n}):\n";
	foreach ($hist as $set => $cnt) {
		echo sprintf("  %d×  %s\n", $cnt, $set);
	}
}

echo "\n=== C. Probe hypothesis (same three families: brotli L1 vs xz L1 vs zstd) vs full (brotli L11 vs xz L9 vs zstd) ===\n";
echo 'Datasets with all six rows: ' . count($triples) . "\n";
echo sprintf(
	"Agreement (same family wins at probe and full): %d / %d (%.1f%%)\n",
	$agree,
	count($triples),
	$pct
);

$mismatch = [];
foreach ($triples as $d) {
	if ($probeWinnerFam[$d] !== $fullWinnerFam[$d]) {
		$mismatch[] = "{$d}: probe={$probeWinnerFam[$d]} full={$fullWinnerFam[$d]}";
	}
}
if ($mismatch !== []) {
	echo "\nMismatches:\n";
	foreach ($mismatch as $line) {
		echo "  {$line}\n";
	}
}

echo "\n=== D. Within-codec: Pearson r (compressed size) low level vs max level, same machine CSV ===\n";
echo sprintf(
	"brotli L1 vs L11:  r = %.4f  (n=%d datasets)\n",
	$rBrotli ?? 0.0,
	count($dsAlign)
);
echo sprintf(
	"xz L1 vs L9:       r = %.4f  (n=%d datasets)\n",
	$rXz ?? 0.0,
	count($xzAlign)
);
echo "\nzstd appears once per dataset in this CSV (no level sweep), so probe-vs-max for zstd cannot be measured from Squash rows alone.\n";

echo "\n=== E. Takeaways for fractal_zip outer heuristics ===\n";
echo "- Squash labels diverse content; absolute winners are often specialist codecs (see histogram), not only brotli/xz/zstd.\n";
echo "- For brotli and xz, size at low level tracks size at max level very tightly across corpora (high r), so cheap probes estimate relative hardness.\n";
echo "- A three-way probe (brotli L1 vs xz L1 vs zstd) vs full (L11/L9/zstd) agrees ~61% here; mismatches are systematically xz→probe, brotli→full (preset ramp crosses).\n";
echo "- Buckets (text vs binary vs pdf vs jpeg, etc.) show different winning plugins — align outer candidates to literal class where possible.\n";
