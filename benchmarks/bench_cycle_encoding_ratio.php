#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare cycle-recipe representation size vs generic compressors on raw payloads.
 *
 * The cycle approach stores a compact recipe (or binary pack) instead of expanded bytes.
 * This bench reports whether any tested compressor on raw bytes beats that oracle.
 *
 * Usage from repo root:
 *   php benchmarks/bench_cycle_encoding_ratio.php
 *   php benchmarks/bench_cycle_encoding_ratio.php --json
 */

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';

$jsonOut = in_array('--json', $argv, true);

/**
 * @param list<int> $steps
 * @deprecated v1 pack; use cycle_encoding_pack_for_spec()
 */
function cycle_bench_pack_binary(int $min_ord, int $max_ord, array $steps, int $length, int $width): string
{
	$buf = pack('CC', $min_ord & 0xff, $max_ord & 0xff);
	$buf .= chr(count($steps));
	foreach($steps as $step) {
		$buf .= chr($step & 0xff);
	}
	$buf .= pack('N', $length);
	$buf .= chr($width & 0xff);
	return $buf;
}

function cycle_bench_compress_len(string $algo, string $bytes): ?int
{
	switch($algo) {
		case 'gz9':
			$z = @gzencode($bytes, 9);
			return $z === false ? null : strlen($z);
		case 'gz6':
			$z = @gzencode($bytes, 6);
			return $z === false ? null : strlen($z);
		case 'deflate9':
			$z = @gzdeflate($bytes, 9);
			return $z === false ? null : strlen($z);
		case 'brotli11':
			if(!function_exists('brotli_compress')) {
				return null;
			}
			$z = @brotli_compress($bytes, 11);
			return $z === false ? null : strlen($z);
		case 'bzip29':
			if(!function_exists('bzcompress')) {
				return null;
			}
			$z = @bzcompress($bytes, 9);
			return $z === false ? null : strlen($z);
		case 'zstd19':
			if(!function_exists('zstd_compress')) {
				$bin = trim((string)shell_exec('command -v zstd 2>/dev/null'));
				if($bin === '') {
					return null;
				}
				$tmpIn = tempnam(sys_get_temp_dir(), 'cyc_in_');
				$tmpOut = tempnam(sys_get_temp_dir(), 'cyc_out_');
				if($tmpIn === false || $tmpOut === false) {
					return null;
				}
				file_put_contents($tmpIn, $bytes);
				$cmd = escapeshellarg($bin) . ' -19 -q -f -o ' . escapeshellarg($tmpOut) . ' ' . escapeshellarg($tmpIn) . ' 2>/dev/null';
				exec($cmd, $_, $code);
				$sz = ($code === 0 && is_file($tmpOut)) ? (int)filesize($tmpOut) : null;
				@unlink($tmpIn);
				@unlink($tmpOut);
				return $sz;
			}
			$z = @zstd_compress($bytes, 19);
			return $z === false ? null : strlen($z);
		case 'xz9':
			$bin = trim((string)shell_exec('command -v xz 2>/dev/null'));
			if($bin === '') {
				return null;
			}
			$tmpIn = tempnam(sys_get_temp_dir(), 'cyc_in_');
			$tmpOut = tempnam(sys_get_temp_dir(), 'cyc_out_');
			if($tmpIn === false || $tmpOut === false) {
				return null;
			}
			file_put_contents($tmpIn, $bytes);
			$cmd = escapeshellarg($bin) . ' -9 -e -q -c ' . escapeshellarg($tmpIn) . ' > ' . escapeshellarg($tmpOut) . ' 2>/dev/null';
			exec($cmd, $_, $code);
			$sz = ($code === 0 && is_file($tmpOut)) ? (int)filesize($tmpOut) : null;
			@unlink($tmpIn);
			@unlink($tmpOut);
			return $sz;
		case '7z9':
			$bin = trim((string)shell_exec('command -v 7z 2>/dev/null'));
			if($bin === '') {
				return null;
			}
			$tmpIn = tempnam(sys_get_temp_dir(), 'cyc_in_');
			$tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'cyc7z_' . bin2hex(random_bytes(4));
			if($tmpIn === false || !@mkdir($tmpDir, 0700, true)) {
				return null;
			}
			$member = $tmpDir . DIRECTORY_SEPARATOR . 'payload.bin';
			file_put_contents($member, $bytes);
			$archive = $tmpDir . DIRECTORY_SEPARATOR . 'out.7z';
			$cmd = escapeshellarg($bin) . ' a -mx=9 -m0=lzma2 -ms=off -bd -y ' . escapeshellarg($archive) . ' '
				. escapeshellarg($member) . ' >/dev/null 2>&1';
			exec($cmd, $_, $code);
			$sz = ($code === 0 && is_file($archive)) ? (int)filesize($archive) : null;
			@unlink($tmpIn);
			@unlink($member);
			@unlink($archive);
			@rmdir($tmpDir);
			return $sz;
		default:
			return null;
	}
}

/**
 * @return array<string,int|null>
 */
function cycle_bench_raw_variants(string $bytes): array
{
	$out = array();
	foreach(array('gz9', 'gz6', 'deflate9', 'brotli11', 'bzip29', 'zstd19', 'xz9', '7z9') as $algo) {
		$out[$algo] = cycle_bench_compress_len($algo, $bytes);
	}
	// Stacked combos sometimes used in practice.
	$gz = @gzencode($bytes, 9);
	if($gz !== false) {
		$out['gz9+brotli11'] = cycle_bench_compress_len('brotli11', $gz);
		$out['gz9+zstd19'] = cycle_bench_compress_len('zstd19', $gz);
	}
	if(function_exists('brotli_compress')) {
		$br = @brotli_compress($bytes, 11);
		if($br !== false) {
			$out['brotli11+gz9'] = cycle_bench_compress_len('gz9', $br);
		}
	}
	return $out;
}

/**
 * @param array<string,int|null> $variants
 */
function cycle_bench_best(array $variants): ?array
{
	$best = null;
	$bestKey = null;
	foreach($variants as $k => $v) {
		if($v === null) {
			continue;
		}
		if($best === null || $v < $best) {
			$best = $v;
			$bestKey = $k;
		}
	}
	if($best === null) {
		return null;
	}
	return array('algo' => (string)$bestKey, 'bytes' => $best);
}

$specs = cycle_encoding_expanded_specs();
$rows = array();
$totals = array(
	'raw' => 0,
	'cycle_oracle' => 0,
	'best_generic' => 0,
);

foreach($specs as $case => $spec) {
	$bytes = (string)$spec['bytes'];
	$raw = strlen($bytes);
	$recipe = (string)$spec['representation'];
	$recipeB = strlen($recipe);
	$binary = cycle_encoding_pack_for_spec($spec);
	$binaryB = strlen($binary);

	$cycleVariants = array(
		'recipe_ascii' => $recipeB,
		'recipe_gz9' => cycle_bench_compress_len('gz9', $recipe),
		'binary_pack' => $binaryB,
		'binary_gz9' => cycle_bench_compress_len('gz9', $binary),
	);
	$cycleOracle = min(array_values(array_filter($cycleVariants, static fn($v) => $v !== null)));
	$cycleOracleKey = null;
	foreach($cycleVariants as $k => $v) {
		if($v === $cycleOracle) {
			$cycleOracleKey = $k;
			break;
		}
	}

	$rawVariants = cycle_bench_raw_variants($bytes);
	$bestGeneric = cycle_bench_best($rawVariants);

	$bestGenericB = $bestGeneric['bytes'] ?? null;
	$wins = ($bestGenericB === null) ? null : ($cycleOracle < $bestGenericB);
	$delta = ($bestGenericB === null) ? null : ($bestGenericB - $cycleOracle);

	$row = array(
		'case' => (int)$case,
		'dir' => (string)$spec['dir'],
		'tier' => (string)$spec['tier'],
		'raw_bytes' => $raw,
		'recipe_ascii' => $recipeB,
		'recipe_gz9' => $cycleVariants['recipe_gz9'],
		'binary_pack' => $binaryB,
		'binary_gz9' => $cycleVariants['binary_gz9'],
		'cycle_oracle_bytes' => $cycleOracle,
		'cycle_oracle_mode' => $cycleOracleKey,
		'raw_variants' => $rawVariants,
		'best_generic_algo' => $bestGeneric['algo'] ?? null,
		'best_generic_bytes' => $bestGenericB,
		'cycle_beats_generic' => $wins,
		'generic_minus_cycle' => $delta,
		'cycle_pct_of_raw' => $raw > 0 ? round(100.0 * $cycleOracle / $raw, 4) : null,
		'best_generic_pct_of_raw' => ($raw > 0 && $bestGenericB !== null) ? round(100.0 * $bestGenericB / $raw, 4) : null,
	);
	$rows[] = $row;
	$totals['raw'] += $raw;
	$totals['cycle_oracle'] += $cycleOracle;
	if($bestGenericB !== null) {
		$totals['best_generic'] += $bestGenericB;
	}
}

$payload = array(
	'metric' => 'cycle_recipe_oracle_vs_generic_compressors',
	'note' => 'cycle_oracle = min(recipe_ascii, recipe_gz9, binary_pack, binary_gz9). best_generic = min tested compressor/combo on raw payload.',
	'cases' => $rows,
	'totals' => $totals,
);

if($jsonOut) {
	echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

printf("\nCycle encoding ratio bench — recipe oracle vs generic compressors on raw payloads\n");
printf("cycle_oracle = min(ascii recipe, gz9(recipe), binary pack, gz9(binary pack))\n\n");
printf("%-6s %-14s %-10s %10s %10s %10s %10s %12s %s\n",
	'case', 'dir', 'tier', 'raw B', 'cycle B', 'best gen B', 'gen−cycle', 'cycle/raw%', 'best algo');
printf("%s\n", str_repeat('-', 110));

$allWin = true;
foreach($rows as $row) {
	$winMark = ($row['cycle_beats_generic'] === true) ? 'WIN' : (($row['cycle_beats_generic'] === false) ? 'LOSS' : '?');
	if($row['cycle_beats_generic'] !== true) {
		$allWin = false;
	}
	printf("%4d   %-14s %-10s %10d %10d %10s %10s %11s%% %-8s %s\n",
		$row['case'],
		$row['dir'],
		$row['tier'],
		$row['raw_bytes'],
		$row['cycle_oracle_bytes'],
		$row['best_generic_bytes'] === null ? 'n/a' : (string)$row['best_generic_bytes'],
		$row['generic_minus_cycle'] === null ? 'n/a' : (string)$row['generic_minus_cycle'],
		$row['cycle_pct_of_raw'] === null ? 'n/a' : number_format($row['cycle_pct_of_raw'], 2),
		$row['best_generic_algo'] ?? 'n/a',
		$winMark
	);
}

printf("%s\n", str_repeat('-', 110));
printf("TOTAL raw=%d cycle_oracle=%d best_generic=%d\n",
	$totals['raw'], $totals['cycle_oracle'], $totals['best_generic']);
printf("\nPer-case compressor breakdown (raw payload):\n\n");

foreach($rows as $row) {
	printf("=== test_files%d (%s) raw=%d cycle_oracle=%d [%s] ===\n",
		$row['case'], $row['tier'], $row['raw_bytes'], $row['cycle_oracle_bytes'], (string)$row['cycle_oracle_mode']);
	printf("  recipe: %s (%d B)\n", (string)$specs[$row['case']]['representation'], $row['recipe_ascii']);
	foreach($row['raw_variants'] as $algo => $sz) {
		if($sz === null) {
			printf("  %-16s n/a\n", $algo);
		} else {
			printf("  %-16s %d B (%.2f%% raw)\n", $algo, $sz, 100.0 * $sz / max(1, $row['raw_bytes']));
		}
	}
	printf("  cycle_oracle_mode  %s\n", (string)$row['cycle_oracle_mode']);
	printf("  recipe_gz9         %s B\n", $row['recipe_gz9'] === null ? 'n/a' : (string)$row['recipe_gz9']);
	printf("  binary_pack        %d B\n", $row['binary_pack']);
	printf("  binary_gz9         %s B\n", $row['binary_gz9'] === null ? 'n/a' : (string)$row['binary_gz9']);
	printf("\n");
}

printf("GATE: cycle oracle beats best generic on all cases? %s\n", $allWin ? 'PASS' : 'FAIL');
