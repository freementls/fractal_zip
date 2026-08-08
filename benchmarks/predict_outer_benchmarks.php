#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Outer prediction benchmark: same corpus discovery as run_benchmarks.php; runs full fractal_zip zip_folder
 * (literal/transform phases) with FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY=1 so adaptive_compress skips all outer codecs
 * after dumping pre-outer inner bytes. Layered probes (L1 fast shortlist → L2 medium → high on L2 winner); the human
 * table prints L2 pred 3..1 (worst→best among medium tier). gzip/brotli/xz/zstd/7z/arc/bsc/zpaq at comparable tiers.
 *
 * Same discovery flags as run_benchmarks: --only, --skip, --limit, --maximum-size, --with-huge-corpora,
 * --with-synthetic-micro, --large, --no-multipass, --legacy-folder-zip, --adaptive-markers,
 * --bench-keep-shell-literal-env, --case-timeout / --no-case-timeout.
 *
 * Bundled BSC CLI: benchmarks/.tools/bin/bsc (override FRACTAL_ZIP_BSC or PATH).
 *
 * Usage:
 *   php benchmarks/predict_outer_benchmarks.php --maximum-size=2M
 *   php benchmarks/predict_outer_benchmarks.php --only=test_files105 --json
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';
// Full layered probes include expensive L3 high-tier run on the L2 winner; encode path skips L3 by default (see fractal_zip::outer_prediction_layer3_high_enabled).
if (getenv('FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH') === false) {
	putenv('FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH=1');
}
$argv = $_SERVER['argv'] ?? [];

$includeHugeCorpora = in_array('--with-huge-corpora', $argv, true);
$includeSyntheticMicro = in_array('--with-synthetic-micro', $argv, true);
$includeLarge = in_array('--large', $argv, true);
$noMultipass = in_array('--no-multipass', $argv, true);
$benchLegacyFolderZip = in_array('--legacy-folder-zip', $argv, true);
$benchAdaptiveMarkers = in_array('--adaptive-markers', $argv, true);
$benchKeepShellLiteralEnv = in_array('--bench-keep-shell-literal-env', $argv, true);

$jsonOut = in_array('--json', $argv, true);
$jsonOutFile = null;
$noSaveLastJson = in_array('--no-save-last-json', $argv, true);
$speedFirst = in_array('--speed', $argv, true);
$preferLz = in_array('--prefer-lz', $argv, true);

$onlyTests = null;
$skipTests = [];
$limitCases = null;
$maxRawBytesArg = null;
$probeTimeout = 45.0;
$probeInnerMax = 8 * 1024 * 1024;

$caseTimeoutSec = 45;
$toutEnv = getenv('FRACTAL_ZIP_BENCH_CASE_TIMEOUT_SEC');
if ($toutEnv !== false && trim((string) $toutEnv) !== '') {
	$toutN = (int) trim((string) $toutEnv);
	$caseTimeoutSec = $toutN > 0 ? $toutN : null;
}

foreach ($argv as $a) {
	if ($a === '--no-case-timeout') {
		$caseTimeoutSec = null;
	}
	if (is_string($a) && strncmp($a, '--only=', 7) === 0) {
		$onlyTests = array_values(array_filter(array_map('trim', explode(',', substr($a, 7))), static fn ($s) => $s !== ''));
	}
	if (is_string($a) && strncmp($a, '--skip=', 7) === 0) {
		$skipTests = array_values(array_filter(array_map('trim', explode(',', substr($a, 7))), static fn ($s) => $s !== ''));
	}
	if (is_string($a) && strncmp($a, '--limit=', 8) === 0) {
		$raw = trim(substr($a, 8));
		if ($raw !== '' && ctype_digit($raw)) {
			$limitCases = max(1, (int) $raw);
		}
	}
	if (is_string($a) && strncmp($a, '--maximum-size=', 15) === 0) {
		$maxRawBytesArg = trim(substr($a, 15));
	}
	if (is_string($a) && strncmp($a, '--out-json=', 11) === 0) {
		$p = trim(substr($a, 11));
		if ($p !== '') {
			$jsonOutFile = str_starts_with($p, '/') ? $p : $repoRoot . DIRECTORY_SEPARATOR . $p;
		}
	}
	if (is_string($a) && strncmp($a, '--probe-timeout=', 16) === 0) {
		$raw = trim(substr($a, 16));
		if ($raw !== '' && is_numeric($raw)) {
			$probeTimeout = max(1.0, (float) $raw);
		}
	}
	if (is_string($a) && strncmp($a, '--probe-inner-max=', 18) === 0) {
		$raw = trim(substr($a, 18));
		if ($raw !== '') {
			try {
				$probeInnerMax = max(4096, benchParseMaximumSizeBytes($raw));
			} catch (Throwable $e) {
				fwrite(STDERR, "[predict_outer_benchmarks] invalid --probe-inner-max=…: {$raw}\n");
				exit(2);
			}
		}
	}
	if (is_string($a) && strncmp($a, '--case-timeout=', 15) === 0) {
		$raw = trim(substr($a, 15));
		if ($raw !== '' && ctype_digit($raw)) {
			$n = (int) $raw;
			$caseTimeoutSec = $n > 0 ? $n : null;
		}
	}
}

if (in_array('--help', $argv, true) || in_array('-h', $argv, true)) {
	$me = basename($argv[0] ?? 'predict_outer_benchmarks.php');
	fwrite(
		STDOUT,
		"Usage: php benchmarks/{$me} [same corpus flags as run_benchmarks.php]\n"
			. "  [--probe-timeout=sec] [--probe-inner-max=8Mi] [--probe-inner-max=8388608]\n"
			. "  [--json] [--out-json=path] [--no-save-last-json] [--speed] [--prefer-lz]\n"
	);
	exit(0);
}

require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip_cli_opcache_bootstrap.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_corpus_size.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_corpus_descriptions.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_default_corpus_list.php';

/** Same normalize as run_benchmarks.php */
function bench_normalize_corpus_cli_token(string $token): string
{
	$t = trim($token);
	if ($t === '') {
		return $t;
	}
	if (preg_match('/^[0-9]+$/', $t) === 1) {
		return 'test_files' . (string) (int) $t;
	}
	return $t;
}

/** @param list<string> $tokens @return list<string> */
function bench_normalize_corpus_cli_tokens(array $tokens): array
{
	$out = [];
	foreach ($tokens as $tok) {
		if (!is_string($tok)) {
			continue;
		}
		$n = bench_normalize_corpus_cli_token($tok);
		if ($n !== '') {
			$out[] = $n;
		}
	}
	return $out;
}

if ($onlyTests !== null) {
	$onlyTests = bench_normalize_corpus_cli_tokens($onlyTests);
}
if ($skipTests !== []) {
	$skipTests = bench_normalize_corpus_cli_tokens($skipTests);
}

$benchLiteralBytesFirstDefaults = (getenv('FRACTAL_ZIP_BENCH_LITERAL_SPEED_DEFAULTS') !== '1' && !$benchKeepShellLiteralEnv);
if ($benchLiteralBytesFirstDefaults) {
	foreach ([
		'FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES',
		'FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN',
		'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN',
		'FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER',
		'FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT',
		'FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP',
		'FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER',
		'FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE',
	] as $literalEnvKey) {
		putenv($literalEnvKey);
	}
}

require_once $repoRoot . DIRECTORY_SEPARATOR . 'fractal_zip.php';

if (getenv('FRACTAL_ZIP_ZPAQ_THREADS') === false && getenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS') === false) {
	putenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS=0');
}

if (!$benchLegacyFolderZip) {
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM');
}

require_once __DIR__ . DIRECTORY_SEPARATOR . 'predict_outer_heuristic.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'predict_outer_layered_probe.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_predict_outer_encode.php';

if ($benchAdaptiveMarkers) {
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0');
	fwrite(STDERR, "[predict_outer] --adaptive-markers: FRACTAL_ZIP_ADAPTIVE_MARKERS=1, FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0\n");
}

$maxRawBytes = null;
if ($maxRawBytesArg !== null && $maxRawBytesArg !== '') {
	try {
		$maxRawBytes = benchParseMaximumSizeBytes($maxRawBytesArg);
	} catch (Throwable $e) {
		fwrite(STDERR, '[predict_outer_benchmarks] invalid --maximum-size=…: ' . $maxRawBytesArg . "\n");
		exit(2);
	}
}

$skipByDefault = benchBuildDefaultRunBenchmarksSkipList($includeHugeCorpora, $includeSyntheticMicro, $maxRawBytes);
$defaultTests = discoverBenchmarkDirs($repoRoot, $skipByDefault);
if ($maxRawBytes !== null) {
	$defaultTests = benchFilterCorporaByMaxRawBytes($repoRoot, $defaultTests, $maxRawBytes);
	if ($defaultTests === []) {
		fwrite(STDERR, "[predict_outer_benchmarks] --maximum-size={$maxRawBytes} excluded every corpus.\n");
		exit(2);
	}
}

if ($onlyTests !== null && $onlyTests !== []) {
	$onlySet = array_fill_keys($onlyTests, true);
	$defaultTests = array_values(array_filter($defaultTests, static fn ($n) => isset($onlySet[$n])));
	foreach ($onlyTests as $want) {
		if (!is_string($want) || $want === '') {
			continue;
		}
		if (in_array($want, $defaultTests, true)) {
			continue;
		}
		$p = $repoRoot . DIRECTORY_SEPARATOR . $want;
		if (is_dir($p) && isCanonicalBenchmarkCaseName($want)) {
			$defaultTests[] = $want;
		}
	}
	$defaultTests = array_values(array_unique($defaultTests));
	sort($defaultTests, SORT_NATURAL);
}
if ($skipTests !== []) {
	$skipSet = array_fill_keys($skipTests, true);
	$defaultTests = array_values(array_filter($defaultTests, static fn ($n) => !isset($skipSet[$n])));
}

if ($limitCases !== null) {
	$defaultTests = array_slice($defaultTests, 0, $limitCases);
}

if ($defaultTests === []) {
	fwrite(STDERR, "No benchmark corpora matched (see run_benchmarks.php defaults).\n");
	exit(1);
}

$workRoot = __DIR__ . DIRECTORY_SEPARATOR . '.work';
if (!is_dir($workRoot)) {
	mkdir($workRoot, 0755, true);
}
bench_predict_outer_sweep_work_root($workRoot);

$preferLzEnv = $preferLz || getenv('FRACTAL_ZIP_PREDICT_OUTER_LZ') === '1';
$speedEnv = $speedFirst || getenv('FRACTAL_ZIP_PREDICT_OUTER_SPEED') === '1';

$zpaqExe = fractal_zip::zpaq_executable();
$brotliExe = fractal_zip::brotli_executable();
$xzExe = fractal_zip::xz_executable();
$zstdExe = fractal_zip::zstd_executable();
$bscExe = predict_outer_resolve_bsc_exe();
$sevenExe = fractal_zip::seven_zip_executable();
$arcExe = fractal_zip::freearc_executable();

$heavyGzipList = bench_predict_outer_heavy_corpora_folder_gzip_fast_default();

function predict_outer_format_bytes_cell(int $n): string
{
	return number_format($n);
}

/** Map probe codec key → fz outer hint family */
function predict_outer_probe_key_to_outer(string $k): string
{
	static $direct = [
		'gzip' => 'gzip',
		'brotli' => 'brotli',
		'xz' => 'xz',
		'zstd' => 'zstd',
		'zpaq' => 'zpaq',
		'bsc' => 'bsc',
		'7z' => '7z',
		'arc' => 'arc',
	];
	if (isset($direct[$k])) {
		return $direct[$k];
	}
	switch ($k) {
		case 'gzip9':
			return 'gzip';
		case 'brotli_q11':
			return 'brotli';
		case 'xz9':
			return 'xz';
		case 'zstd':
			return 'zstd';
		case 'zpaq':
			return 'zpaq';
		case 'bsc':
			return 'bsc';
		default:
			return 'gzip';
	}
}

$rows = [];
foreach ($defaultTests as $idx => $corpus) {
	$caseNo = $idx + 1;
	$totalCases = count($defaultTests);
	fwrite(STDERR, "[predict_outer {$caseNo}/{$totalCases}] {$corpus}\n");
	fflush(STDERR);

	if ($caseTimeoutSec !== null) {
		@set_time_limit(max(60, $caseTimeoutSec + 120));
	}

	$savedGz = bench_predict_outer_save_folder_gzip_fast_env();
	$enc = null;
	try {
		if (!$includeLarge && bench_corpora_should_pass_large_to_run_benchmarks($repoRoot, $corpus)) {
			putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
		} elseif (in_array($corpus, $heavyGzipList, true)) {
			putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
		}

		$enc = bench_predict_outer_zip_folder_inner_only(
			$repoRoot,
			$corpus,
			$workRoot,
			$noMultipass,
			$benchAdaptiveMarkers
		);
	} finally {
		bench_predict_outer_restore_folder_gzip_fast_env($savedGz);
	}

	if ($enc === null || !$enc['ok']) {
		$rows[] = [
			'corpus' => $corpus,
			'short_desc' => bench_corpus_desc16($corpus),
			'raw_bytes' => (int) ($enc['raw_bytes'] ?? 0),
			'inner_bytes' => 0,
			'zip_seconds' => 0.0,
			'error' => $enc['error'] ?? 'encode failed',
			'top3' => [],
			'breakdown' => [],
			'probe_sample_bytes' => 0,
			'chosen_outer_label' => '',
			'chosen_outer_bytes' => null,
			'layered' => [],
		];
		bench_predict_outer_sweep_work_root($workRoot);
		continue;
	}

	$innerBlob = $enc['inner_blob'] ?? '';
	$rank = predict_outer_rank_outer_candidates(
		$innerBlob,
		$probeInnerMax,
		$zpaqExe,
		$brotliExe,
		$xzExe,
		$zstdExe,
		$bscExe,
		$probeTimeout,
		$repoRoot,
		$sevenExe,
		$arcExe
	);

	$legacyProbes = predict_outer_run_probes(
		substr($innerBlob, 0, min(strlen($innerBlob), $probeInnerMax)),
		$brotliExe,
		$xzExe,
		$zstdExe,
		$probeTimeout
	);
	$legacyProbes['zpaq_available'] = ($zpaqExe !== null && $zpaqExe !== '');
	$legacyProbes['speed_first'] = $speedEnv;
	$legacyProbes['prefer_lz_over_zpaq'] = $preferLzEnv;
	$legacyProbes['sample_bytes'] = $rank['probe_sample_bytes'];
	$decision = predict_outer_decide($legacyProbes);

	$top3 = $rank['top3'];
	$wf = (string) ($rank['layered']['winner_family'] ?? '');
	$chosenOuterFamily = $wf !== '' ? $wf : (string) ($top3[0]['codec'] ?? '');
	$hints = $chosenOuterFamily !== ''
		? predict_outer_env_hints(predict_outer_probe_key_to_outer($chosenOuterFamily))
		: [];

	bench_predict_outer_sweep_work_root($workRoot);

	$rows[] = [
		'corpus' => $corpus,
		'short_desc' => bench_corpus_desc16($corpus),
		'raw_bytes' => (int) $enc['raw_bytes'],
		'inner_bytes' => (int) $enc['inner_bytes'],
		'zip_seconds' => (float) $enc['zip_seconds'],
		'top3' => $top3,
		'breakdown' => $rank['breakdown'],
		'probe_sample_bytes' => $rank['probe_sample_bytes'],
		'legacy_predicted_outer' => $decision['outer'],
		'legacy_confidence' => $decision['confidence'],
		'env_hints_top1' => $hints,
		'chosen_outer_label' => $rank['chosen_label'] ?? '',
		'chosen_outer_bytes' => $rank['chosen_bytes'] ?? null,
		'layered' => $rank['layered'] ?? [],
		'tools' => [
			'zpaq' => $zpaqExe,
			'brotli' => $brotliExe,
			'xz' => $xzExe,
			'zstd' => $zstdExe,
			'bsc' => $bscExe,
			'7z' => $sevenExe,
			'arc' => $arcExe,
		],
	];
}

$payload = [
	'generated_at' => gmdate('c'),
	'settings' => [
		'probe_inner_max_bytes' => $probeInnerMax,
		'probe_timeout_sec' => $probeTimeout,
		'speed_first' => $speedEnv,
		'prefer_lz_legacy' => $preferLzEnv,
		'include_large' => $includeLarge,
		'no_multipass' => $noMultipass,
	],
	'cases' => $rows,
];

$lastPath = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.last_predict_outer.json';
if (!$noSaveLastJson) {
	bench_json_file_put($lastPath, $payload, true, 'predict_outer last JSON');
}

if ($jsonOutFile !== null) {
	bench_json_file_put($jsonOutFile, $payload, true, 'predict_outer --out-json');
}

if ($jsonOut) {
	$js = bench_json_encode_try($payload, true);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (predict_outer --json): ' . json_last_error_msg() . "\n");
		exit(2);
	}
	echo $js . "\n";
	exit(0);
}

$bscNote = $bscExe !== null ? $bscExe : '(none — set FRACTAL_ZIP_BSC or install benchmarks/.tools/bin/bsc)';
$sevenNote = $sevenExe !== null ? $sevenExe : '(none)';
$arcNote = $arcExe !== null ? $arcExe : '(none)';
echo "predict_outer_benchmarks — encode through inner only (FRACTAL_ZIP_PRE_OUTER_PROBE_ONLY), then layered outer-size probes\n";
echo "L1/L2 winners use Squash-style curve factors (not raw bytes); table = L2 medium sizes pred 3..1 worst→best; chosen = high probe on curve winner (inner cap {$probeInnerMax} B). bsc: {$bscNote}  7z: {$sevenNote}  arc: {$arcNote}\n";
echo 'Cases: ' . count($rows) . "  JSON: {$lastPath}\n\n";

printf(
	"%-16s %-14s %10s %10s %8s %22s %22s %22s %36s\n",
	'test',
	'desc',
	'raw B',
	'inner B',
	'zip s',
	'L2 pred 3',
	'L2 pred 2',
	'L2 pred 1',
	'chosen outer (high)'
);
echo str_repeat('-', 190) . "\n";

foreach ($rows as $r) {
	// top3[] is L2 medium tier, best-first (index 0 = smallest bytes). Columns: pred 3 worst → pred 1 best (left → right).
	$p3 = $p2 = $p1 = '—';
	if (isset($r['top3'][2])) {
		$s = $r['top3'][2]['settings'] ?? $r['top3'][2]['codec'];
		$p3 = $s . '=' . predict_outer_format_bytes_cell($r['top3'][2]['bytes']);
	}
	if (isset($r['top3'][1])) {
		$s = $r['top3'][1]['settings'] ?? $r['top3'][1]['codec'];
		$p2 = $s . '=' . predict_outer_format_bytes_cell($r['top3'][1]['bytes']);
	}
	if (isset($r['top3'][0])) {
		$s = $r['top3'][0]['settings'] ?? $r['top3'][0]['codec'];
		$p1 = $s . '=' . predict_outer_format_bytes_cell($r['top3'][0]['bytes']);
	}
	$chosen = '—';
	if (isset($r['chosen_outer_label']) && $r['chosen_outer_label'] !== '' && isset($r['chosen_outer_bytes']) && $r['chosen_outer_bytes'] !== null) {
		$chosen = $r['chosen_outer_label'] . ' (' . predict_outer_format_bytes_cell((int) $r['chosen_outer_bytes']) . ' B)';
	}
	if (isset($r['error'])) {
		$p3 = 'ERROR';
		$chosen = '—';
	}
	printf(
		"%-16s %-14s %10s %10s %8s %22s %22s %22s %36s\n",
		substr($r['corpus'], 0, 16),
		substr($r['short_desc'], 0, 14),
		isset($r['error']) ? '—' : predict_outer_format_bytes_cell((int) $r['raw_bytes']),
		isset($r['error']) ? '—' : predict_outer_format_bytes_cell((int) $r['inner_bytes']),
		isset($r['error']) ? '—' : sprintf('%.3f', (float) $r['zip_seconds']),
		substr($p3, 0, 22),
		substr($p2, 0, 22),
		substr($p1, 0, 22),
		substr($chosen, 0, 36)
	);
}
