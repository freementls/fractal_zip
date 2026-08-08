#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Compare fractal_zip `.fz` metrics against **best-of-Squash-benchmark** numbers for the same 28 datasets
 * as https://quixdb.github.io/squash-benchmark/ (CSV from quixdb/squash-benchmark-web `data/*.csv`).
 *
 * Default machine **hoplite** (Intel Core i7-2630QM laptop) is the closest “laptop” profile in the published set;
 * use `--machine=peltast` for Xeon E3-1225 v3 / ThinkServer if you prefer a small-office tower baseline.
 *
 * Squash CSV columns: dataset, plugin, codec, level, compressed_size, compress_cpu, decompress_cpu (seconds CPU).
 * Bests per dataset (excluding identity **copy** rows where compressed_size ≥ raw):
 *   - smallest compressed_size (B-best; table column B-best s = that row’s compress_cpu)
 *   - highest raw÷compress_cpu (compression throughput; enc / sq enc)
 *   - highest raw÷decompress_cpu (decompression throughput)
 * Text table: * on B-best s or fzc enc = faster compress time between Squash B-best CPU seconds and fractal zip_folder seconds.
 *
 * With **`--only=…`** and/or **`--maximum-size=…`**, this script first runs **`benchmarks/run_benchmarks.php`** for the
 * matching Squash mirror corpora (`test_files105`–`132`), writes JSON to **`--bench-json`**, then prints the Squash
 * comparison. **`--maximum-size`** uses the same on-disk raw-byte cap as `run_benchmarks.php` (see `benchParseMaximumSizeBytes`).
 * **`--no-case-timeout`** and **`--case-timeout=N`** are forwarded to that child (same semantics as `run_benchmarks.php`).
 * Use **`--no-bench`** to only read existing bench JSON. With neither **`--only`** nor **`--maximum-size`**, no bench is run.
 * **`--jobs=N`** is forwarded to **`run_benchmarks.php`** (parallel corpora when Linux + `pcntl_fork`; see `benchmarks/PARALLELISM.md`).
 * When **`--bench-json`** points at **`benchmarks/.last_bench.json`**, remember that path is **gitignored** by default; use **`run_benchmarks.php --out-json=…`** for a stable file you check in or pass to **`--no-bench`** runs.
 *
 * Usage:
 *   php benchmarks/squash_benchmarks.php
 *   php benchmarks/squash_benchmarks.php --only=test_files105          # runs run_benchmarks then Squash table
 *   php benchmarks/squash_benchmarks.php --maximum-size=2M         # all Squash corpora on disk whose raw bytes ≤ cap
 *   php benchmarks/squash_benchmarks.php --only=105,106 --no-bench   # Squash only; uses existing --bench-json
 *   php benchmarks/squash_benchmarks.php --machine=peltast --bench-json=benchmarks/.last_bench.json
 *   php benchmarks/squash_benchmarks.php --json --refresh-csv
 *   php benchmarks/squash_benchmarks.php --ultra --maximum-size=2M   # child bench inherits ultra env (see fractal_zip_cli.php)
 *   php benchmarks/squash_benchmarks.php --ultra --force-outer=zpaq --only=105,106   # FRACTAL_ZIP_FORCE_OUTER=zpaq for bench child (zpaq on PATH or FRACTAL_ZIP_ZPAQ)
 *   php benchmarks/squash_benchmarks.php --only=114,128 --no-case-timeout   # forwarded to run_benchmarks.php (same as that script’s flag)
 *   php benchmarks/squash_benchmarks.php --case-timeout=300 --maximum-size=2M
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_corpus_size.php';
$cacheDir = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.squash_benchmark_cache';
$argv = $_SERVER['argv'] ?? [];

$wantJson = in_array('--json', $argv, true);
$refreshCsv = in_array('--refresh-csv', $argv, true);
$noBench = in_array('--no-bench', $argv, true);
$benchUltra = in_array('--ultra', $argv, true);
$benchNoCaseTimeout = in_array('--no-case-timeout', $argv, true);
$benchCaseTimeoutSec = null;
$benchForceOuter = null;
$benchJobs = 1;
$machine = 'hoplite';
$benchJson = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.last_bench.json';
$onlyArg = null;
$maxRawBytesArg = null;
foreach ($argv as $a) {
	if (is_string($a) && strncmp($a, '--machine=', 10) === 0) {
		$machine = trim(substr($a, 10)) ?: 'hoplite';
	}
	if (is_string($a) && strncmp($a, '--bench-json=', 13) === 0) {
		$p = trim(substr($a, 13));
		if ($p !== '') {
			$benchJson = str_starts_with($p, '/') ? $p : $repoRoot . DIRECTORY_SEPARATOR . $p;
		}
	}
	if (is_string($a) && strncmp($a, '--only=', 7) === 0) {
		$onlyArg = substr($a, 7);
	}
	if (is_string($a) && strncmp($a, '--maximum-size=', 15) === 0) {
		$maxRawBytesArg = trim(substr($a, 15));
	}
	if (is_string($a) && strncmp($a, '--force-outer=', 14) === 0) {
		$fo = strtolower(trim(substr($a, 14)));
		if ($fo === 'zpaq' || $fo === 'gzip') {
			$benchForceOuter = $fo;
		} elseif ($fo !== '') {
			fwrite(STDERR, "[squash_benchmarks] --force-outer= must be zpaq or gzip (got: {$fo})\n");
			exit(2);
		}
	}
	if (is_string($a) && strncmp($a, '--case-timeout=', 15) === 0) {
		$rawT = trim(substr($a, 15));
		if ($rawT !== '' && ctype_digit($rawT)) {
			$nT = (int) $rawT;
			if ($nT > 0) {
				$benchCaseTimeoutSec = $nT;
			}
		}
	}
	if (is_string($a) && preg_match('/^--jobs=(\d+)$/', $a, $jm)) {
		$benchJobs = max(1, min(32, (int) $jm[1]));
	}
}
if ($benchNoCaseTimeout) {
	$benchCaseTimeoutSec = null;
}
$maxRawBytes = null;
if ($maxRawBytesArg !== null && $maxRawBytesArg !== '') {
	try {
		$maxRawBytes = benchParseMaximumSizeBytes($maxRawBytesArg);
	} catch (InvalidArgumentException $e) {
		fwrite(STDERR, '[squash_benchmarks] invalid --maximum-size=…: ' . $maxRawBytesArg . ' (' . $e->getMessage() . "). "
			. "Use bytes ≥1 or a suffix (2M, 10MiB, …); same rules as run_benchmarks.php.\n");
		exit(1);
	}
}
if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
	$me = basename($argv[0] ?? 'squash_benchmarks.php');
	fwrite(STDOUT, "Usage: php benchmarks/{$me} [--only=test_files105|105,…] [--maximum-size=2M|2000000|…] [--jobs=N] [--ultra] [--force-outer=zpaq|gzip] [--no-case-timeout] [--case-timeout=N] [--no-bench] [--machine=hoplite|peltast|…] [--bench-json=path] [--refresh-csv] [--json]\n");
	exit(0);
}

/** Same order as squash-benchmark.js datasets + build_test_files_squash_corpora.php (105…132). */
$SQUASH_DATASETS = [
	['id' => 'alice29.txt', 'raw' => 152089, 'dir' => 'test_files105'],
	['id' => 'asyoulik.txt', 'raw' => 125179, 'dir' => 'test_files106'],
	['id' => 'cp.html', 'raw' => 24603, 'dir' => 'test_files107'],
	['id' => 'dickens', 'raw' => 10192446, 'dir' => 'test_files108'],
	['id' => 'enwik8', 'raw' => 100000000, 'dir' => 'test_files109'],
	['id' => 'fields.c', 'raw' => 11150, 'dir' => 'test_files110'],
	['id' => 'fireworks.jpeg', 'raw' => 123093, 'dir' => 'test_files111'],
	['id' => 'geo.protodata', 'raw' => 118588, 'dir' => 'test_files112'],
	['id' => 'grammar.lsp', 'raw' => 3721, 'dir' => 'test_files113'],
	['id' => 'kennedy.xls', 'raw' => 1029744, 'dir' => 'test_files114'],
	['id' => 'lcet10.txt', 'raw' => 426754, 'dir' => 'test_files115'],
	['id' => 'mozilla', 'raw' => 51220480, 'dir' => 'test_files116'],
	['id' => 'mr', 'raw' => 9970564, 'dir' => 'test_files117'],
	['id' => 'nci', 'raw' => 33553445, 'dir' => 'test_files118'],
	['id' => 'ooffice', 'raw' => 6152192, 'dir' => 'test_files119'],
	['id' => 'osdb', 'raw' => 10085684, 'dir' => 'test_files120'],
	['id' => 'paper-100k.pdf', 'raw' => 102400, 'dir' => 'test_files121'],
	['id' => 'plrabn12.txt', 'raw' => 481861, 'dir' => 'test_files122'],
	['id' => 'ptt5', 'raw' => 513216, 'dir' => 'test_files123'],
	['id' => 'reymont', 'raw' => 6627202, 'dir' => 'test_files124'],
	['id' => 'samba', 'raw' => 21606400, 'dir' => 'test_files125'],
	['id' => 'sao', 'raw' => 7251944, 'dir' => 'test_files126'],
	['id' => 'sum', 'raw' => 38240, 'dir' => 'test_files127'],
	['id' => 'urls.10K', 'raw' => 702087, 'dir' => 'test_files128'],
	['id' => 'xargs.1', 'raw' => 4227, 'dir' => 'test_files129'],
	['id' => 'webster', 'raw' => 41458703, 'dir' => 'test_files130'],
	['id' => 'xml', 'raw' => 5345280, 'dir' => 'test_files131'],
	['id' => 'x-ray', 'raw' => 8474240, 'dir' => 'test_files132'],
];

/** Map `--only` token to `test_filesNNN` (numeric 105–132 or full dir name). */
function squashOnlyTokenToDir(string $token): string
{
	$token = trim($token);
	if ($token === '') {
		return '';
	}
	if (preg_match('/^\d+$/', $token) === 1) {
		$n = (int) $token;
		if ($n < 105 || $n > 132) {
			fwrite(STDERR, "[squash_benchmarks] --only numeric id must be in 105…132, got: {$token}\n");
			exit(1);
		}
		return 'test_files' . $n;
	}
	return $token;
}

/**
 * @param list<array{id: string, raw: int, dir: string}> $all
 * @return list<array{id: string, raw: int, dir: string}>
 */
function squashFilterDatasetsByOnly(array $all, ?string $onlyComma): array
{
	if ($onlyComma === null || trim($onlyComma) === '') {
		return $all;
	}
	$valid = [];
	foreach ($all as $spec) {
		$valid[$spec['dir']] = true;
	}
	$tokens = array_values(array_filter(array_map('trim', explode(',', $onlyComma)), static fn (string $s): bool => $s !== ''));
	if ($tokens === []) {
		return $all;
	}
	$want = [];
	foreach ($tokens as $t) {
		$dir = squashOnlyTokenToDir($t);
		if ($dir === '') {
			continue;
		}
		if (!isset($valid[$dir])) {
			fwrite(STDERR, "[squash_benchmarks] unknown corpus for --only: {$t} (use test_files105…test_files132 or 105…132; aggregate Silesia folder is test_files133 → run_benchmarks.php --only=test_files133)\n");
			exit(1);
		}
		$want[$dir] = true;
	}
	$out = array_values(array_filter($all, static fn (array $spec): bool => isset($want[$spec['dir']])));
	if ($out === []) {
		fwrite(STDERR, "[squash_benchmarks] --only matched no Squash corpora\n");
		exit(1);
	}
	return $out;
}

$squashDatasets = squashFilterDatasetsByOnly($SQUASH_DATASETS, $onlyArg);
if ($maxRawBytes !== null) {
	$dirs = array_values(array_map(static fn (array $spec): string => $spec['dir'], $squashDatasets));
	$keepDirs = benchFilterCorporaByMaxRawBytes($repoRoot, $dirs, $maxRawBytes);
	if ($keepDirs === []) {
		fwrite(STDERR, "[squash_benchmarks] --maximum-size={$maxRawBytes} excludes every selected Squash corpus (missing dirs or on-disk raw over cap).\n");
		exit(2);
	}
	$keepSet = array_fill_keys($keepDirs, true);
	$squashDatasets = array_values(array_filter($squashDatasets, static fn (array $spec): bool => isset($keepSet[$spec['dir']])));
}
$fractalBenchDirs = array_values(array_unique(array_map(static fn (array $spec): string => $spec['dir'], $squashDatasets)));
$shouldAutoBench = !$noBench && (($onlyArg !== null && trim($onlyArg) !== '') || $maxRawBytes !== null);
$onlyDirsList = ($onlyArg !== null && trim($onlyArg) !== '') || $maxRawBytes !== null
	? $fractalBenchDirs
	: null;

function squashCsvUrl(string $machine): string
{
	return 'https://raw.githubusercontent.com/quixdb/squash-benchmark-web/master/data/' . rawurlencode($machine) . '.csv';
}

function squashEnsureCsv(string $cacheDir, string $machine, bool $refresh): string
{
	if (!is_dir($cacheDir)) {
		mkdir($cacheDir, 0755, true);
	}
	$path = $cacheDir . DIRECTORY_SEPARATOR . $machine . '.csv';
	if ($refresh || !is_file($path)) {
		$url = squashCsvUrl($machine);
		$ctx = stream_context_create(['http' => ['timeout' => 120]]);
		$data = @file_get_contents($url, false, $ctx);
		if ($data === false || $data === '') {
			fwrite(STDERR, "[squash_benchmarks] failed to download {$url}\n");
			exit(1);
		}
		if (file_put_contents($path, $data) === false) {
			fwrite(STDERR, "[squash_benchmarks] failed to write {$path}\n");
			exit(1);
		}
	}
	return $path;
}

/**
 * @param list<array{dataset: string, plugin: string, codec: string, level: string, compressed_size: int, compress_cpu: float, decompress_cpu: float}> $rows
 * @return array{best_b: array{bytes: int, label: string, compress_cpu: ?float}, best_enc: array{bps: float, label: string, compress_cpu: float}, best_dec: array{bps: float, label: string, decompress_cpu: float}}
 */
function squashBestMetricsForDataset(array $rows, int $raw): array
{
	$meaningful = [];
	foreach ($rows as $row) {
		$sz = $row['compressed_size'];
		if ($sz < $raw) {
			$meaningful[] = $row;
		}
	}
	$pool = $meaningful !== [] ? $meaningful : $rows;

	$bestB = null;
	$bestEnc = null;
	$bestDec = null;
	foreach ($pool as $row) {
		$sz = $row['compressed_size'];
		$label = squashCodecLabel($row['plugin'], $row['codec'], $row['level']);
		$ccRow = (float) $row['compress_cpu'];
		if ($bestB === null || $sz < $bestB['bytes']) {
			$bestB = [
				'bytes' => $sz,
				'label' => $label,
				'compress_cpu' => $ccRow > 0.0 ? $ccRow : null,
			];
		} elseif ($sz === $bestB['bytes']) {
			if ($ccRow > 0.0) {
				$prev = $bestB['compress_cpu'];
				if ($prev === null || $ccRow < $prev) {
					$bestB['compress_cpu'] = $ccRow;
				}
			}
		}
		$cc = $row['compress_cpu'];
		if ($cc > 0.0) {
			$bps = $raw / $cc;
			if ($bestEnc === null || $bps > $bestEnc['bps']) {
				$bestEnc = ['bps' => $bps, 'label' => $label, 'compress_cpu' => $cc];
			}
		}
		$dc = $row['decompress_cpu'];
		if ($dc > 0.0) {
			$dbps = $raw / $dc;
			if ($bestDec === null || $dbps > $bestDec['bps']) {
				$bestDec = ['bps' => $dbps, 'label' => $label, 'decompress_cpu' => $dc];
			}
		}
	}
	if ($bestB === null) {
		$bestB = ['bytes' => 0, 'label' => '—', 'compress_cpu' => null];
	}
	if ($bestEnc === null) {
		$bestEnc = ['bps' => 0.0, 'label' => '—', 'compress_cpu' => 0.0];
	}
	if ($bestDec === null) {
		$bestDec = ['bps' => 0.0, 'label' => '—', 'decompress_cpu' => 0.0];
	}
	return ['best_b' => $bestB, 'best_enc' => $bestEnc, 'best_dec' => $bestDec];
}

function squashCodecLabel(string $plugin, string $codec, string $level): string
{
	$plugin = trim($plugin);
	$codec = trim($codec);
	$level = trim($level);
	$s = ($plugin !== '' && $codec !== '' && strcasecmp($plugin, $codec) === 0)
		? $codec
		: (($plugin !== '' && $codec !== '') ? ($plugin . '/' . $codec) : ($plugin !== '' ? $plugin : $codec));
	if ($level !== '') {
		$s .= '/' . $level;
	}
	return $s !== '' ? $s : '—';
}

/** @return array<string, list<array{dataset: string, plugin: string, codec: string, level: string, compressed_size: int, compress_cpu: float, decompress_cpu: float}>> */
function squashLoadCsvGrouped(string $path): array
{
	$f = fopen($path, 'rb');
	if ($f === false) {
		throw new RuntimeException('open ' . $path);
	}
	$header = fgetcsv($f);
	if ($header === false) {
		fclose($f);
		throw new RuntimeException('empty csv');
	}
	$idx = [];
	foreach ($header as $i => $name) {
		$idx[trim((string) $name)] = $i;
	}
	$need = ['dataset', 'plugin', 'codec', 'level', 'compressed_size', 'compress_cpu', 'decompress_cpu'];
	foreach ($need as $k) {
		if (!isset($idx[$k])) {
			fclose($f);
			throw new RuntimeException('missing column ' . $k);
		}
	}
	$by = [];
	while (($r = fgetcsv($f)) !== false) {
		if (count($r) < count($header)) {
			continue;
		}
		$ds = trim((string) $r[$idx['dataset']]);
		if ($ds === '') {
			continue;
		}
		$row = [
			'dataset' => $ds,
			'plugin' => trim((string) $r[$idx['plugin']]),
			'codec' => trim((string) $r[$idx['codec']]),
			'level' => trim((string) $r[$idx['level']]),
			'compressed_size' => (int) $r[$idx['compressed_size']],
			'compress_cpu' => (float) $r[$idx['compress_cpu']],
			'decompress_cpu' => (float) $r[$idx['decompress_cpu']],
		];
		$by[$ds][] = $row;
	}
	fclose($f);
	return $by;
}

/** @return array<string, array{fzc_bytes: ?int, zip_seconds: ?float, extract_all_seconds: ?float, outer_codec: ?string, outer_caption: ?string}> */
function squashLoadBenchJson(string $path): array
{
	if (!is_file($path)) {
		return [];
	}
	$raw = @file_get_contents($path);
	if ($raw === false || $raw === '') {
		return [];
	}
	$j = bench_json_decode_assoc_try($raw, 'squashLoadBenchJson ' . $path);
	if ($j === null || !isset($j['cases']) || !is_array($j['cases'])) {
		return [];
	}
	$out = [];
	foreach ($j['cases'] as $c) {
		if (!is_array($c)) {
			continue;
		}
		$label = isset($c['label']) ? (string) $c['label'] : '';
		if ($label === '') {
			continue;
		}
		$oc = isset($c['outer_codec']) && is_string($c['outer_codec']) ? trim($c['outer_codec']) : '';
		$ocap = isset($c['outer_caption']) && is_string($c['outer_caption']) ? trim($c['outer_caption']) : '';
		$out[$label] = [
			'fzc_bytes' => isset($c['fzc_bytes']) ? (int) $c['fzc_bytes'] : null,
			'zip_seconds' => isset($c['zip_seconds']) ? (float) $c['zip_seconds'] : null,
			'extract_all_seconds' => isset($c['extract_all_seconds']) ? (float) $c['extract_all_seconds'] : null,
			'outer_codec' => $oc !== '' ? $oc : null,
			'outer_caption' => $ocap !== '' && $ocap !== '—' ? $ocap : null,
		];
	}
	return $out;
}

function squashFmtBytes(int $n): string
{
	return number_format($n);
}

/** Bench `outer_codec` for the table (truncate so one long token does not dominate width). */
function squashFmtOuter(?string $codec, int $maxLen = 24): string
{
	if ($codec === null || $codec === '') {
		return '—';
	}
	if ($maxLen < 8) {
		$maxLen = 8;
	}
	if (strlen($codec) <= $maxLen) {
		return $codec;
	}
	$keep = max(1, $maxLen - 3);
	return substr($codec, 0, $keep) . '…';
}

/**
 * Prefer `outer_caption` (codec + settings, same detail level as B-best labels); else short `outer_codec`.
 */
function squashFmtOuterCell(?string $caption, ?string $codec): string
{
	$cap = $caption !== null && $caption !== '' ? trim($caption) : '';
	if ($cap !== '' && $cap !== '—') {
		return $cap;
	}
	return squashFmtOuter($codec, 24);
}

function squashFmtBps(float $bps): string
{
	if ($bps >= 1e9) {
		return sprintf('%.2f GiB/s', $bps / (1024.0 * 1024.0 * 1024.0));
	}
	if ($bps >= 1e6) {
		return sprintf('%.2f MiB/s', $bps / (1024.0 * 1024.0));
	}
	if ($bps >= 1e3) {
		return sprintf('%.2f KiB/s', $bps / 1024.0);
	}
	return sprintf('%.0f B/s', $bps);
}

/** Wall/CPU seconds for table cells (Squash CSV compress_cpu / decompress_cpu; bench zip_seconds). */
function squashFmtSeconds(?float $sec): string
{
	if ($sec === null || $sec <= 0.0) {
		return '—';
	}
	if ($sec < 1.0) {
		return rtrim(rtrim(sprintf('%.6f', $sec), '0'), '.') ?: '0';
	}
	return sprintf('%.4g', $sec);
}

/** @return array{winner: string, fzc_star: bool} */
function squashWinnerBytes(?int $fzcB, int $squashB): array
{
	if ($fzcB === null) {
		return ['winner' => '—', 'fzc_star' => false];
	}
	if ($fzcB === $squashB) {
		return ['winner' => 'tie', 'fzc_star' => false];
	}
	if ($fzcB < $squashB) {
		return ['winner' => 'fzc', 'fzc_star' => true];
	}
	return ['winner' => 'squash', 'fzc_star' => false];
}

/**
 * Lower wall/CPU seconds wins (fzc zip_folder vs Squash B-best row compress_cpu).
 *
 * @return array{winner: string, fzc_star: bool, squash_star: bool}
 */
function squashWinnerCompressTime(?float $fzcZipSec, ?float $squashBbestCpu): array
{
	if (($fzcZipSec === null || $fzcZipSec <= 0.0) && ($squashBbestCpu === null || $squashBbestCpu <= 0.0)) {
		return ['winner' => '—', 'fzc_star' => false, 'squash_star' => false];
	}
	if ($squashBbestCpu === null || $squashBbestCpu <= 0.0) {
		return ['winner' => 'fzc', 'fzc_star' => true, 'squash_star' => false];
	}
	if ($fzcZipSec === null || $fzcZipSec <= 0.0) {
		return ['winner' => 'squash', 'fzc_star' => false, 'squash_star' => true];
	}
	$eps = 1e-9 * max($fzcZipSec, $squashBbestCpu, 1e-12);
	if (abs($fzcZipSec - $squashBbestCpu) < $eps) {
		return ['winner' => 'tie', 'fzc_star' => false, 'squash_star' => false];
	}
	if ($fzcZipSec < $squashBbestCpu) {
		return ['winner' => 'fzc', 'fzc_star' => true, 'squash_star' => false];
	}
	return ['winner' => 'squash', 'fzc_star' => false, 'squash_star' => true];
}

/** @return array{winner: string, fzc_star: bool} higher B/s is better */
function squashWinnerSpeed(?float $fzcBps, float $squashBps): array
{
	if ($squashBps <= 0.0 || $fzcBps === null) {
		return ['winner' => '—', 'fzc_star' => false];
	}
	$eps = max(1.0, $squashBps * 1e-9);
	if (abs($fzcBps - $squashBps) < $eps) {
		return ['winner' => 'tie', 'fzc_star' => false];
	}
	if ($fzcBps > $squashBps) {
		return ['winner' => 'fzc', 'fzc_star' => true];
	}
	return ['winner' => 'squash', 'fzc_star' => false];
}

function squashCellFzc(?string $val, bool $star): string
{
	$s = $val ?? '—';
	return $star ? ('*' . $s) : $s;
}

/**
 * Run fractal_zip benchmarks for the given corpus dirs (same CLI as `php benchmarks/run_benchmarks.php --only=…`).
 * Writes full bench JSON to $lastJsonPath via FRACTAL_ZIP_BENCH_LAST_JSON.
 * When $discardChildStdout is false, forwards child stdout/stderr (human bench table on stdout).
 * When true (parent `squash_benchmarks.php --json`), runs the child with `--json` and drains stdout so Squash JSON stays clean.
 * When $applyUltra is true, applies the same putenv defaults as `fractal_zip_cli.php --ultra` before spawning the child (child inherits env).
 * When $forceOuter is `zpaq` or `gzip`, sets FRACTAL_ZIP_FORCE_OUTER for the child (see fractal_zip::try_force_outer_zpaq_payload); zpaq needs the binary on PATH or FRACTAL_ZIP_ZPAQ.
 * When $noCaseTimeout is true, passes `--no-case-timeout` to run_benchmarks (overrides $caseTimeoutSec). Else $caseTimeoutSec may pass `--case-timeout=N`.
 * When $benchJobs > 1, passes `--jobs=N` to run_benchmarks (same semantics as that script).
 *
 * @param list<string> $onlyDirsList e.g. ['test_files105']
 */
/** @see bench_ultra_apply_env_defaults() */
function squash_bench_apply_ultra_env_defaults(): void
{
	bench_ultra_apply_env_defaults();
}

function squashRunFractalZipBench(string $repoRoot, array $onlyDirsList, string $lastJsonPath, bool $discardChildStdout, ?int $maxRawBytes, bool $applyUltra, ?string $forceOuter, bool $noCaseTimeout, ?int $caseTimeoutSec, int $benchJobs): int
{
	if ($onlyDirsList === []) {
		return 0;
	}
	if ($applyUltra) {
		squash_bench_apply_ultra_env_defaults();
	}
	if (!function_exists('proc_open')) {
		fwrite(STDERR, "[squash_benchmarks] proc_open unavailable; run: php benchmarks/run_benchmarks.php --only=" . implode(',', $onlyDirsList) . "\n");
		return 1;
	}
	$php = PHP_BINARY;
	$script = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_benchmarks.php';
	$only = implode(',', $onlyDirsList);
	$cmd = [$php, $script, '--only=' . $only];
	if ($maxRawBytes !== null) {
		$cmd[] = '--maximum-size=' . (string) $maxRawBytes;
	}
	if ($noCaseTimeout) {
		$cmd[] = '--no-case-timeout';
	} elseif ($caseTimeoutSec !== null && $caseTimeoutSec > 0) {
		$cmd[] = '--case-timeout=' . (string) $caseTimeoutSec;
	}
	if ($discardChildStdout) {
		$cmd[] = '--json';
	}
	if ($benchJobs > 1) {
		$cmd[] = '--jobs=' . (string) max(1, min(32, $benchJobs));
	}
	$nullIn = PHP_OS_FAMILY === 'Windows' ? 'NUL' : '/dev/null';
	$desc = [
		0 => ['file', $nullIn, 'r'],
		1 => $discardChildStdout ? ['pipe', 'w'] : ['file', 'php://stdout', 'w'],
		2 => ['file', 'php://stderr', 'w'],
	];
	$prevLast = getenv('FRACTAL_ZIP_BENCH_LAST_JSON');
	putenv('FRACTAL_ZIP_BENCH_LAST_JSON=' . $lastJsonPath);
	$prevForceOuter = getenv('FRACTAL_ZIP_FORCE_OUTER');
	if ($forceOuter !== null) {
		putenv('FRACTAL_ZIP_FORCE_OUTER=' . $forceOuter);
	}
	$proc = @proc_open($cmd, $desc, $pipes, $repoRoot, null, ['bypass_shell' => true]);
	if (!is_resource($proc)) {
		fwrite(STDERR, "[squash_benchmarks] failed to start run_benchmarks.php\n");
		if ($prevLast !== false && $prevLast !== '') {
			putenv('FRACTAL_ZIP_BENCH_LAST_JSON=' . $prevLast);
		} else {
			putenv('FRACTAL_ZIP_BENCH_LAST_JSON');
		}
		if ($forceOuter !== null) {
			if ($prevForceOuter !== false && $prevForceOuter !== '') {
				putenv('FRACTAL_ZIP_FORCE_OUTER=' . $prevForceOuter);
			} else {
				putenv('FRACTAL_ZIP_FORCE_OUTER');
			}
		}
		return 1;
	}
	if ($discardChildStdout && isset($pipes[1]) && is_resource($pipes[1])) {
		stream_set_blocking($pipes[1], true);
		stream_get_contents($pipes[1]);
		fclose($pipes[1]);
	}
	$code = proc_close($proc);
	if ($prevLast !== false && $prevLast !== '') {
		putenv('FRACTAL_ZIP_BENCH_LAST_JSON=' . $prevLast);
	} else {
		putenv('FRACTAL_ZIP_BENCH_LAST_JSON');
	}
	if ($forceOuter !== null) {
		if ($prevForceOuter !== false && $prevForceOuter !== '') {
			putenv('FRACTAL_ZIP_FORCE_OUTER=' . $prevForceOuter);
		} else {
			putenv('FRACTAL_ZIP_FORCE_OUTER');
		}
	}
	return $code;
}

$ranFractalBench = false;
if ($shouldAutoBench && $fractalBenchDirs !== []) {
	$onlyCsv = implode(',', $fractalBenchDirs);
	$childMode = $wantJson ? ' (--json, stdout discarded)' : '';
	$maxNote = $maxRawBytes !== null ? " --maximum-size={$maxRawBytes}" : '';
	$ultraNote = $benchUltra ? ' --ultra (env preset)' : '';
	$foNote = $benchForceOuter !== null ? " FRACTAL_ZIP_FORCE_OUTER={$benchForceOuter}" : '';
	$toNote = $benchNoCaseTimeout ? ' --no-case-timeout' : ($benchCaseTimeoutSec !== null ? ' --case-timeout=' . (string) $benchCaseTimeoutSec : '');
	$jobsNote = $benchJobs > 1 ? ' --jobs=' . (string) $benchJobs : '';
	fwrite(STDERR, "[squash_benchmarks] running run_benchmarks.php --only={$onlyCsv}{$maxNote}{$ultraNote}{$foNote}{$toNote}{$jobsNote}{$childMode}; JSON → {$benchJson}\n");
	$benchExit = squashRunFractalZipBench($repoRoot, $fractalBenchDirs, $benchJson, $wantJson, $maxRawBytes, $benchUltra, $benchForceOuter, $benchNoCaseTimeout, $benchCaseTimeoutSec, $benchJobs);
	if ($benchExit !== 0) {
		fwrite(STDERR, "[squash_benchmarks] run_benchmarks.php exited {$benchExit}; aborting Squash report.\n");
		exit($benchExit);
	}
	$ranFractalBench = true;
}

$csvPath = squashEnsureCsv($cacheDir, $machine, $refreshCsv);
try {
	$grouped = squashLoadCsvGrouped($csvPath);
} catch (Throwable $e) {
	fwrite(STDERR, '[squash_benchmarks] CSV: ' . $e->getMessage() . "\n");
	exit(1);
}

$fzcByLabel = squashLoadBenchJson($benchJson);
if ($fzcByLabel === [] && is_file($benchJson) === false) {
	$hint = $shouldAutoBench && $noBench
		? ' Re-run without --no-bench, or: php benchmarks/run_benchmarks.php --maximum-size=… --only=test_files105,…'
		: ' Run: php benchmarks/squash_benchmarks.php --maximum-size=2M  or  --only=test_files105,…';
	fwrite(STDERR, "[squash_benchmarks] bench JSON not found ({$benchJson}); .fz columns will be empty.{$hint}\n");
} elseif ($fzcByLabel === [] && is_file($benchJson)) {
	fwrite(STDERR, "[squash_benchmarks] bench JSON has no cases[]; .fz columns empty.\n");
}

$rowsOut = [];
foreach ($squashDatasets as $spec) {
	$id = $spec['id'];
	$raw = $spec['raw'];
	$dir = $spec['dir'];
	$pool = $grouped[$id] ?? [];
	if ($pool === []) {
		fwrite(STDERR, "[squash_benchmarks] warning: no CSV rows for dataset {$id}\n");
	}
	$best = squashBestMetricsForDataset($pool, $raw);
	$fz = $fzcByLabel[$dir] ?? [
		'fzc_bytes' => null, 'zip_seconds' => null, 'extract_all_seconds' => null,
		'outer_codec' => null, 'outer_caption' => null,
	];
	$fzcB = $fz['fzc_bytes'];
	$outerC = $fz['outer_codec'] ?? null;
	$outerCap = $fz['outer_caption'] ?? null;
	$zipS = $fz['zip_seconds'];
	$exS = $fz['extract_all_seconds'];
	$fzcEncBps = ($fzcB !== null && $zipS !== null && $zipS > 0.0) ? ($raw / $zipS) : null;
	$fzcDecBps = ($fzcB !== null && $exS !== null && $exS > 0.0) ? ($raw / $exS) : null;

	$wB = squashWinnerBytes($fzcB, $best['best_b']['bytes']);
	$wE = squashWinnerSpeed($fzcEncBps, $best['best_enc']['bps']);
	$wD = squashWinnerSpeed($fzcDecBps, $best['best_dec']['bps']);
	$bbestCpu = isset($best['best_b']['compress_cpu']) && $best['best_b']['compress_cpu'] !== null && (float) $best['best_b']['compress_cpu'] > 0.0
		? (float) $best['best_b']['compress_cpu']
		: null;
	$wT = squashWinnerCompressTime($zipS, $bbestCpu);
	$rowsOut[] = [
		'dataset' => $id,
		'corpus_dir' => $dir,
		'raw_bytes' => $raw,
		'squash_best_compressed_bytes' => $best['best_b']['bytes'],
		'squash_best_compressed_label' => $best['best_b']['label'],
		'squash_bbest_compress_cpu' => $bbestCpu,
		'squash_best_compress_bps' => $best['best_enc']['bps'],
		'squash_best_compress_label' => $best['best_enc']['label'],
		'squash_best_decompress_bps' => $best['best_dec']['bps'],
		'squash_best_decompress_label' => $best['best_dec']['label'],
		'fzc_bytes' => $fzcB,
		'outer_codec' => $outerC,
		'outer_caption' => $outerCap,
		'fzc_zip_seconds' => $zipS,
		'fzc_extract_all_seconds' => $exS,
		'fzc_compress_bps' => $fzcEncBps,
		'fzc_decompress_bps' => $fzcDecBps,
		'winner_compressed_bytes' => $wB['winner'],
		'winner_compress_throughput' => $wE['winner'],
		'winner_decompress_throughput' => $wD['winner'],
		'winner_compress_time' => $wT['winner'],
		'fzc_wins_compressed_bytes' => $wB['fzc_star'],
		'fzc_wins_compress_bps' => $wE['fzc_star'],
		'fzc_wins_compress_time' => $wT['fzc_star'],
		'squash_wins_compress_time' => $wT['squash_star'],
		'fzc_wins_decompress_bps' => $wD['fzc_star'],
	];
}

$payload = [
	'generated' => date('c'),
	'squash_csv_url' => squashCsvUrl($machine),
	'squash_csv_cache' => $csvPath,
	'machine' => $machine,
	'machine_note' => $machine === 'hoplite'
		? 'Default: hoplite (Intel Core i7-2630QM laptop) — closest laptop-class host in Squash data.'
		: 'See quixdb/squash-benchmark-web squash-benchmark.js `machines` for CPU details.',
	'bench_json' => $benchJson,
	'only' => $onlyDirsList,
	'maximum_raw_bytes' => $maxRawBytes,
	'fractal_bench_ran' => $ranFractalBench,
	'cases' => $rowsOut,
];

if ($wantJson) {
	$js = bench_json_encode_try($payload, true);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (squash_benchmarks --json): ' . json_last_error_msg() . "\n");
		exit(2);
	}
	echo $js . "\n";
	exit(0);
}

echo "Squash benchmark comparison (best plugin/codec per metric; Squash times are CPU seconds from published CSV)\n";
echo "Machine: {$machine} — " . ($machine === 'hoplite' ? 'laptop-class default (Core i7-2630QM)' : 'see squash-benchmark.js machines[]') . "\n";
if ($onlyDirsList !== null) {
	echo 'Corpora: ' . implode(', ', $onlyDirsList) . "\n";
}
if ($maxRawBytes !== null) {
	echo "On-disk raw cap: {$maxRawBytes} B (same semantics as run_benchmarks.php --maximum-size)\n";
}
if ($ranFractalBench) {
	echo "Fractal bench: ran run_benchmarks.php for the corpora above; JSON saved to {$benchJson}\n";
} elseif ($shouldAutoBench && $noBench) {
	echo "Fractal bench: skipped (--no-bench); using existing {$benchJson}\n";
}
echo "CSV: " . squashCsvUrl($machine) . "\n";
echo "Bench JSON (fzc): {$benchJson}\n";
echo "Squash vs fzc. * on bytes / dec B/s = fractal_zip wins that pair (lower bytes, higher B/s). "
	. "B-best s = Squash CSV compress_cpu for the B-best (smallest compressed_size) row; fzc enc = KiB/s (or similar) and zip_folder wall seconds. "
	. "* on B-best s or fzc enc = faster compress time (lower seconds) for that pair.\n";

$squashRowsCells = [];
foreach ($rowsOut as $r) {
	$fzcBstr = squashCellFzc($r['fzc_bytes'] !== null ? squashFmtBytes((int) $r['fzc_bytes']) : null, (bool) $r['fzc_wins_compressed_bytes']);
	$fzcRate = $r['fzc_compress_bps'] !== null ? squashFmtBps((float) $r['fzc_compress_bps']) : '—';
	$fzcZipPart = squashFmtSeconds(isset($r['fzc_zip_seconds']) ? (float) $r['fzc_zip_seconds'] : null);
	if ($fzcZipPart === '—') {
		$fzcEncBody = $fzcRate;
	} elseif ($fzcRate === '—') {
		$fzcEncBody = $fzcZipPart . ' s';
	} else {
		$fzcEncBody = $fzcRate . ' · ' . $fzcZipPart . ' s';
	}
	$fzcEstr = squashCellFzc($fzcEncBody, (bool) $r['fzc_wins_compress_time']);
	$fzcDstr = squashCellFzc($r['fzc_decompress_bps'] !== null ? squashFmtBps((float) $r['fzc_decompress_bps']) : null, (bool) $r['fzc_wins_decompress_bps']);
	$bbestSec = (isset($r['squash_bbest_compress_cpu']) && $r['squash_bbest_compress_cpu'] !== null && (float) $r['squash_bbest_compress_cpu'] > 0.0)
		? (float) $r['squash_bbest_compress_cpu']
		: null;
	$bBestSstr = squashCellFzc(
		$bbestSec !== null ? (squashFmtSeconds($bbestSec) . ' s') : squashFmtSeconds(null),
		(bool) $r['squash_wins_compress_time']
	);
	$corpusPart = sprintf('%s (%s)', $r['corpus_dir'], $r['dataset']);
	$squashRowsCells[] = [
		'corpus' => $corpusPart,
		'bWho' => $r['squash_best_compressed_label'],
		'rawB' => squashFmtBytes((int) $r['raw_bytes']),
		'sqB' => squashFmtBytes((int) $r['squash_best_compressed_bytes']),
		'fzcB' => $fzcBstr,
		'outer' => squashFmtOuterCell(
			isset($r['outer_caption']) && is_string($r['outer_caption']) ? $r['outer_caption'] : null,
			(isset($r['outer_codec']) && is_string($r['outer_codec']) && $r['outer_codec'] !== '') ? $r['outer_codec'] : null
		),
		'encWho' => $r['squash_best_compress_label'],
		'sqEnc' => squashFmtBps((float) $r['squash_best_compress_bps']),
		'bBestS' => $bBestSstr,
		'fzcEnc' => $fzcEstr,
		'decWho' => $r['squash_best_decompress_label'],
		'sqDec' => squashFmtBps((float) $r['squash_best_decompress_bps']),
		'fzcDec' => $fzcDstr,
	];
}

$squashHeaders = [
	'corpus' => 'corpus',
	'bWho' => 'B-best',
	'rawB' => 'raw B',
	'sqB' => 'sq B',
	'fzcB' => 'fzc B',
	'outer' => 'outer',
	'encWho' => 'enc',
	'sqEnc' => 'sq enc',
	'bBestS' => 'B-best s',
	'fzcEnc' => 'fzc enc',
	'decWho' => 'dec',
	'sqDec' => 'sq dec',
	'fzcDec' => 'fzc dec',
];
$squashOrder = array_keys($squashHeaders);
$squashAlignRight = [
	'rawB' => true, 'sqB' => true, 'fzcB' => true,
	'sqEnc' => true, 'bBestS' => true, 'fzcEnc' => true, 'sqDec' => true, 'fzcDec' => true,
];
$squashWidths = [];
foreach ($squashOrder as $k) {
	$squashWidths[$k] = strlen($squashHeaders[$k]);
}
foreach ($squashRowsCells as $row) {
	foreach ($squashOrder as $k) {
		$squashWidths[$k] = max($squashWidths[$k], strlen($row[$k] ?? ''));
	}
}

$squashGroups = [
	['corpus', 'bWho', 'rawB'],
	['sqB', 'fzcB', 'outer'],
	['encWho', 'sqEnc', 'bBestS', 'fzcEnc'],
	['decWho', 'sqDec', 'fzcDec'],
];
$squashFormatRow = static function (array $cells) use ($squashWidths, $squashAlignRight, $squashGroups): string {
	$sepPipe = ' | ';
	$sepCol = ' ';
	$out = '';
	foreach ($squashGroups as $gi => $group) {
		if ($gi > 0) {
			$out .= $sepPipe;
		}
		foreach ($group as $ci => $k) {
			$val = $cells[$k] ?? '';
			$w = $squashWidths[$k];
			$right = !empty($squashAlignRight[$k]);
			$pad = $w - strlen($val);
			$cell = $right ? (str_repeat(' ', max(0, $pad)) . $val) : ($val . str_repeat(' ', max(0, $pad)));
			if ($ci > 0) {
				$out .= $sepCol;
			}
			$out .= $cell;
		}
	}
	return $out . "\n";
};

$headerCells = [];
foreach ($squashOrder as $k) {
	$headerCells[$k] = $squashHeaders[$k];
}
$headerLine = $squashFormatRow($headerCells);
$ruleW = strlen(rtrim($headerLine, "\n"));
echo str_repeat('=', $ruleW) . "\n";
echo $headerLine;
echo str_repeat('-', $ruleW) . "\n";
foreach ($squashRowsCells as $row) {
	echo $squashFormatRow($row);
}
echo str_repeat('=', $ruleW) . "\n";
echo "Squash bests exclude identity rows (compressed_size ≥ raw). https://quixdb.github.io/squash-benchmark/\n";
