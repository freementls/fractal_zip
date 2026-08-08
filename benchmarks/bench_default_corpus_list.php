<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_corpus_size.php';

/**
 * Default test_files* discovery for run_benchmarks.php and perf_test.php.
 * One source of truth so a plain `php run_benchmarks.php` and `php perf_test.php` enumerate the same
 * `test_files*` set (modulo on-disk materialization: missing dirs are simply absent from the list).
 */

/**
 * Canonical benchmark corpus naming: test_files or test_files<digits>.
 * Underscore suffixes (e.g. _sample) are not canonical and are excluded from default discovery.
 */
function isCanonicalBenchmarkCaseName(string $name): bool
{
	return preg_match('/^test_files(?:[0-9]+)?$/', $name) === 1;
}

/**
 * Squash mirror ids **test_files105** … **test_files132** only (single-file corpora from `build_test_files_squash_corpora.php`).
 * The aggregate Silesia twelve-file folder **`test_files133`** is separate; see **`build_test_files133_silesia12.php`** / **`SILESIA_BENCHMARK.md`**.
 *
 * @return list<string>
 */
function benchSquashBenchmarkMirrorCorpusNames(): array
{
	$out = [];
	for ($i = 105; $i <= 132; $i++) {
		$out[] = 'test_files' . (string) $i;
	}
	return $out;
}

/**
 * @return list<string>
 */
function benchDefaultHugeCorpusNames(): array
{
	return ['test_files54', 'test_files55', 'test_files55_sample', 'test_files56', 'test_files57', 'test_files58', 'test_files59'];
}

/**
 * Build the same skip-basename list as run_benchmarks.php (before per-run --only / --skip / size filter).
 *
 * @return list<string>
 */
function benchBuildDefaultRunBenchmarksSkipList(
	bool $includeHugeCorpora,
	bool $includeSyntheticMicro,
	?int $maxRawBytes
): array {
	// Default discovery should include every canonical numeric corpus (test_files###).
	// Generated suffix variants remain opt-in via --only because they are not canonical
	// test_files### cases and often duplicate a parent corpus.
	$skipByDefault = [
		'test_files54_sample',
		'test_files58_sample',
		'test_files59_sample',
		'test_files55_stratified',
		// Opt-in Mahoney-parity folder (~212 MiB raw); materialize: build_test_files133_silesia12.php
		'test_files133',
		// Opt-in Hutter-scale corpus (~1 GiB); materialize: build_test_files200_enwik9.php
		'test_files200',
		// GP lakes test_files202–210 discover when materialized (gitignored; never commit).
		// Build: php benchmarks/build_test_files202_210_gp.php
	];
	return $skipByDefault;
}

/**
 * @param list<string> $skipBasenames test_files* basenames to omit from discovery
 * @return list<string> sorted natural order
 */
function discoverBenchmarkDirs(string $repoRoot, array $skipBasenames): array
{
	$skip = array_fill_keys($skipBasenames, true);
	$dirs = glob($repoRoot . DIRECTORY_SEPARATOR . 'test_files*', GLOB_ONLYDIR) ?: [];
	$names = [];
	foreach ($dirs as $path) {
		$base = basename($path);
		if (!isset($skip[$base]) && isCanonicalBenchmarkCaseName($base)) {
			$names[] = $base;
		}
	}
	sort($names, SORT_NATURAL);
	return $names;
}

/**
 * Discovered list for the default `run_benchmarks.php` case set and for `perf_test --preset=default`.
 * Mirrors `run_benchmarks.php` when this is the sole discovery call: every canonical test_files### directory is included
 * by default, then (when `$maxRawBytes` is not null) {@see benchFilterCorporaByMaxRawBytes} is applied, same as the bench driver.
 *
 * @return list<string> sorted natural; only corpora with an on-disk `test_files*` directory appear in discovery, then the optional size cap keeps only dirs whose recursive raw file bytes are ≤ the limit (same as run_benchmarks).
 */
function benchDiscoverDefaultRunBenchmarksCorpora(
	string $repoRoot,
	bool $includeHugeCorpora = false,
	bool $includeSyntheticMicro = false,
	?int $maxRawBytes = null
): array {
	$skip = benchBuildDefaultRunBenchmarksSkipList($includeHugeCorpora, $includeSyntheticMicro, $maxRawBytes);
	$names = discoverBenchmarkDirs($repoRoot, $skip);
	if ($maxRawBytes !== null) {
		$names = benchFilterCorporaByMaxRawBytes($repoRoot, $names, $maxRawBytes);
	}
	return $names;
}
