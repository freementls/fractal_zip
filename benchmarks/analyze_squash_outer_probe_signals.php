#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Analyze Squash hoplite.csv for outer-codec roles + probe→full-setting hypotheses.
 *
 * Usage: php benchmarks/analyze_squash_outer_probe_signals.php [path/to/hoplite.csv]
 *
 * Defaults to benchmarks/.squash_benchmark_cache/hoplite.csv
 */

$repo = dirname(__DIR__);
$csvPath = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.squash_benchmark_cache' . DIRECTORY_SEPARATOR . 'hoplite.csv');
if (!is_readable($csvPath)) {
	fwrite(STDERR, "Cannot read CSV: {$csvPath}\n");
	exit(2);
}

require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_corpus_descriptions.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
$desc = bench_corpus_description_map();
$squashDatasets = [];
foreach ($desc as $label => $hint) {
	if (preg_match('/^test_files10[5-9]\b/', $label) || preg_match('/^test_files1[12][0-9]\b/', $label) || $label === 'test_files132') {
		$squashDatasets[$hint] = $label;
	}
}

/** @var array<string, array{brotli?: array<int,int>, xz?: array<int,int>, zstd?: ?int}> */
$byDataset = [];

$fh = fopen($csvPath, 'rb');
if ($fh === false) {
	exit(2);
}
$hdr = fgetcsv($fh);
if ($hdr === false || $hdr[0] !== 'dataset') {
	exit(2);
}
while (($row = fgetcsv($fh)) !== false) {
	if (count($row) < 5) {
		continue;
	}
	$dataset = $row[0];
	if (!isset($squashDatasets[$dataset])) {
		continue;
	}
	$plugin = $row[1];
	$codec = $row[2];
	$levelRaw = $row[3];
	$size = (int) $row[4];
	if ($size <= 0) {
		continue;
	}
	if ($plugin === 'copy') {
		continue;
	}

	if ($plugin === 'brotli' && $codec === 'brotli') {
		$lv = ($levelRaw === '' || $levelRaw === null) ? -1 : (int) $levelRaw;
		if ($lv >= 1 && $lv <= 11) {
			if (!isset($byDataset[$dataset])) {
				$byDataset[$dataset] = array();
			}
			if (!isset($byDataset[$dataset]['brotli'])) {
				$byDataset[$dataset]['brotli'] = array();
			}
			$byDataset[$dataset]['brotli'][$lv] = $size;
		}
	}
	if ($plugin === 'lzma' && $codec === 'xz') {
		$lv = (int) $levelRaw;
		if ($lv >= 1 && $lv <= 9) {
			if (!isset($byDataset[$dataset])) {
				$byDataset[$dataset] = array();
			}
			if (!isset($byDataset[$dataset]['xz'])) {
				$byDataset[$dataset]['xz'] = array();
			}
			$byDataset[$dataset]['xz'][$lv] = $size;
		}
	}
	if ($plugin === 'zstd' && $codec === 'zstd') {
		if (!isset($byDataset[$dataset])) {
			$byDataset[$dataset] = array();
		}
		$byDataset[$dataset]['zstd'] = $size;
	}
}
fclose($fh);

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

$bL1 = [];
$bL5 = [];
$bL10 = [];
$bL11 = [];
$xzL4 = [];
$xzL6 = [];
$xzL9 = [];
$zstd = [];
$labels = [];

foreach ($squashDatasets as $ds => $tfLabel) {
	if (!isset($byDataset[$ds]['brotli'][1], $byDataset[$ds]['brotli'][11])) {
		continue;
	}
	$labels[] = $ds;
	$br = $byDataset[$ds]['brotli'];
	$bL1[] = $br[1];
	$bL5[] = isset($br[5]) ? $br[5] : ($br[4] ?? $br[3]);
	$bL10[] = $br[10];
	$bL11[] = $br[11];
	$xz = $byDataset[$ds]['xz'] ?? array();
	$xzL4[] = $xz[4] ?? ($xz[3] ?? 0);
	$xzL6[] = $xz[6] ?? 0;
	$xzL9[] = $xz[9] ?? 0;
	$zstd[] = $byDataset[$ds]['zstd'] ?? 0;
}

$n = count($labels);
echo "Squash hoplite datasets with brotli L1+L11: {$n} (from CSV)\n\n";

$rProbe11_b5 = pearson_r($bL5, $bL11);
$rProbe11_b1 = pearson_r($bL1, $bL11);
$rProbe11_b10 = pearson_r($bL10, $bL11);
echo "Brotli compressed-size Pearson r vs brotli-11 (predict full tournament):\n";
echo sprintf("  r(L5, L11)  = %s\n", $rProbe11_b5 === null ? 'n/a' : sprintf('%.4f', $rProbe11_b5));
echo sprintf("  r(L1, L11)  = %s\n", $rProbe11_b1 === null ? 'n/a' : sprintf('%.4f', $rProbe11_b1));
echo sprintf("  r(L10, L11) = %s\n", $rProbe11_b10 === null ? 'n/a' : sprintf('%.4f', $rProbe11_b10));
echo "\nInterpretation: high positive r means ranking/smaller-is-better alignment across corpora holds from cheap levels to max.\n\n";

$xzPairs = array();
foreach ($labels as $i => $_d) {
	if ($xzL9[$i] > 0 && $xzL6[$i] > 0) {
		$xzPairs[] = array($i, $xzL6[$i], $xzL9[$i]);
	}
}
$xzL6s = array_column($xzPairs, 1);
$xzL9s = array_column($xzPairs, 2);
$rXz69 = pearson_r($xzL6s, $xzL9s);
echo "XZ compressed-size Pearson r(L6, L9) [same corpora subset]: ";
echo ($rXz69 === null ? 'n/a' : sprintf('%.4f', $rXz69)) . " (n=" . count($xzPairs) . ")\n\n";

// Cross-codec winner among brotli11 / xz9 / zstd for each dataset (byte-min)
$wBro = 0;
$wXz = 0;
$wZ = 0;
$tie = 0;
foreach ($labels as $i => $_d) {
	$b = $bL11[$i];
	$x = $xzL9[$i];
	$z = $zstd[$i];
	if ($z <= 0 || $x <= 0) {
		continue;
	}
	$m = min($b, $x, $z);
	$c = 0;
	if ($b === $m) {
		$c++;
	}
	if ($x === $m) {
		$c++;
	}
	if ($z === $m) {
		$c++;
	}
	if ($c > 1) {
		$tie++;
		continue;
	}
	if ($b === $m) {
		$wBro++;
	} elseif ($x === $m) {
		$wXz++;
	} else {
		$wZ++;
	}
}
$totalW = $wBro + $wXz + $wZ + $tie;
echo "Winner counts (min bytes among brotli-11, xz-9, zstd default), Squash CSV only:\n";
echo sprintf("  brotli-11: %d (%.1f%%)\n", $wBro, $totalW ? 100 * $wBro / $totalW : 0);
echo sprintf("  xz-9:      %d (%.1f%%)\n", $wXz, $totalW ? 100 * $wXz / $totalW : 0);
echo sprintf("  zstd:      %d (%.1f%%)\n", $wZ, $totalW ? 100 * $wZ / $totalW : 0);
echo sprintf("  ties:      %d\n\n", $tie);

// Per-class heuristic from filename keywords (coarse)
$buckets = array(
	'text' => array('txt', 'html', 'lsp', 'xml', 'urls'),
	'office' => array('xls', 'office', 'pdf'),
	'media' => array('jpeg', 'rgb', 'audio'),
	'binary' => array('geo', 'bin', 'nci', 'osdb', 'samba', 'mozilla'),
);
foreach ($buckets as $name => $needles) {
	$sub = array();
	foreach ($labels as $i => $ds) {
		$low = strtolower($ds);
		foreach ($needles as $nd) {
			if (str_contains($low, $nd)) {
				$sub[] = $i;
				break;
			}
		}
	}
	if ($sub === []) {
		continue;
	}
	$meanRatioBro = 0.0;
	$meanRatioXz = 0.0;
	$c = 0;
	foreach ($sub as $i) {
		$rawGuess = max($bL11[$i], $xzL9[$i], $zstd[$i]) * 2;
		if ($zstd[$i] <= 0) {
			continue;
		}
		$meanRatioBro += $bL11[$i] / max(1, $zstd[$i]);
		$meanRatioXz += $xzL9[$i] / max(1, $zstd[$i]);
		$c++;
	}
	if ($c > 0) {
		echo "Bucket \"{$name}\" (n≈{$c}): mean brotli11/zstd byte ratio=" . sprintf('%.3f', $meanRatioBro / $c)
			. ', mean xz9/zstd=' . sprintf('%.3f', $meanRatioXz / $c) . " (<1 ⇒ zstd larger)\n";
	}
}
echo "\n";

// bench JSON optional
$benchJson = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.suite_default.json';
if (is_readable($benchJson)) {
	$j = bench_json_decode_file_assoc_try($benchJson, 'analyze_squash_outer_probe_signals', 512, 0, false);
	if ($j !== null && isset($j['cases'])) {
		$extW = array('arc' => 0, 'zstd' => 0, 'brotli' => 0, 'xz' => 0, 'other' => 0);
		foreach ($j['cases'] as $case) {
			$w = $case['best_ext_winner'] ?? '';
			if (isset($extW[$w])) {
				$extW[$w]++;
			} else {
				$extW['other']++;
			}
		}
		echo "Note: benchmarks/.suite_default.json min-ext winners (folder tournament incl. 7z, zpaq, arc, tar|zstd|brotli|xz|…):\n";
		foreach ($extW as $k => $v) {
			if ($v > 0) {
				echo "  {$k}: {$v}\n";
			}
		}
		echo "(Arc uses FreeArc CLI — not in Squash CSV the same way; compare cautiously.)\n";
	}
}

echo "\n--- Hypothesis notes ---\n";
echo "- Squash CSV supports multi-level curves for brotli and xz; zstd is typically one row (default preset).\n";
echo "- High r(L5,L11) on brotli supports using mid-Q probes before Q11 for *relative* corpus hardness.\n";
echo "- L10 vs L11 r≈1 often — marginal gains at max brotli on text; binary corpora may show larger L10→L11 gaps.\n";
echo "- Outer tournament uses different containers than raw Squash rows; treat as directional evidence only.\n";
