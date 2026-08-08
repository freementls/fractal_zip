#!/usr/bin/env php
<?php
declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

/**
 * Folder-level A/B: .fz bytes from zip_folder under a 2×2 env grid (plus optional strict-tournament column).
 *
 *   php benchmarks/raw_tier_unwrap_ab.php [--repo=DIR] [--only=a,b] [--limit=N] [--with-strict-column] [--json]
 *
 * Note: default FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER picks disk vs unwrapped raw tier by **on-wire** size (outer + FZG peel trailer),
 * so dd and raw0 often match on corpora like test_files62 (many tiny .gz members).
 *
 * Matrix (defaults = bytes-first deep unwrap on raw tier, MPQ peel without gzip-1 proxy):
 *   dd      — unset FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP, unset FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE
 *   raw0    — FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=0 (legacy raw tier uses disk bytes)
 *   mpq_p   — FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE=1 (legacy MPQ gzip-1 gate)
 *   raw0+mpq — both legacy knobs
 *
 * With --with-strict-column:
 *   strict  — FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT=1 (full inner transform tournament; more CPU/RAM)
 *
 * Clears the same literal / raw-tier env keys as run_benchmarks bytes-first defaults before each run batch,
 * then applies the cell. Copies each corpus into benchmarks/.work_ab/<uniq>/ so originals are untouched.
 *
 * Default discovery skips huge trees, micro synth dirs, and **test_files133** (Silesia aggregate); pass **`--only=`** to include them.
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$only = null;
$limit = null;
$jsonOut = in_array('--json', $argv, true);
$withStrict = in_array('--with-strict-column', $argv, true);

foreach ($argv as $a) {
	if (is_string($a) && strncmp($a, '--repo=', 7) === 0) {
		$p = trim(substr($a, 7));
		if ($p !== '') {
			$rp = realpath($p);
			$repo = $rp !== false ? $rp : $p;
		}
	}
	if (is_string($a) && strncmp($a, '--only=', 7) === 0) {
		$only = array_values(array_filter(array_map('trim', explode(',', substr($a, 7))), static fn ($s) => $s !== ''));
	}
	if (is_string($a) && strncmp($a, '--limit=', 8) === 0) {
		$raw = trim(substr($a, 8));
		if ($raw !== '' && ctype_digit($raw)) {
			$limit = max(1, (int) $raw);
		}
	}
}

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';

$envKeys = [
	'FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP',
	'FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE',
	'FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT',
	'FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL',
	'FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES',
	'FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL',
	'FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN',
	'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN',
	'FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL',
	'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER',
];

function fz_ab_env_snapshot(array $keys): array {
	$out = [];
	foreach ($keys as $k) {
		$out[$k] = getenv($k);
	}
	return $out;
}

/** @param array<string, string|false> $snap */
function fz_ab_env_restore(array $snap): void {
	foreach ($snap as $k => $v) {
		if ($v === false) {
			putenv($k);
		} else {
			putenv($k . '=' . $v);
		}
	}
}

function fz_ab_clear_bench_literal_defaults(array $keys): void {
	foreach ($keys as $k) {
		putenv($k);
	}
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM');
	putenv('FRACTAL_ZIP_AUTO_TUNE');
	putenv('FRACTAL_ZIP_TIME_BUDGET_MS');
}

/** @param list<string> $skip */
function fz_ab_discover(string $repoRoot, array $skip): array {
	$skipSet = array_fill_keys($skip, true);
	$dirs = glob($repoRoot . DIRECTORY_SEPARATOR . 'test_files*', GLOB_ONLYDIR) ?: [];
	$names = [];
	foreach ($dirs as $path) {
		$base = basename($path);
		if (!isset($skipSet[$base])) {
			$names[] = $base;
		}
	}
	sort($names, SORT_NATURAL);
	return $names;
}

function fz_ab_remove_tree(string $dir): void {
	if (!is_dir($dir)) {
		return;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ($it as $item) {
		$item->isDir() ? @rmdir($item->getPathname()) : @unlink($item->getPathname());
	}
	@rmdir($dir);
}

/** @return array{bytes: int, sec: float} */
function fz_ab_zip_folder_case(string $corpusPath, string $workRoot, string $_cellLabel): array {
	$tag = 'ab_' . bin2hex(random_bytes(4));
	$dst = $workRoot . DIRECTORY_SEPARATOR . $tag;
	if (!is_dir($workRoot) && !@mkdir($workRoot, 0755, true)) {
		return ['bytes' => -1, 'sec' => 0.0];
	}
	$fzCopy = new fractal_zip(256, false, false, null, false);
	$fzCopy->recursive_copy_directory($corpusPath, $dst);
	$fzc = $dst . $fzCopy->fractal_zip_container_file_extension;
	$fz = new fractal_zip(256, false, false, null, false);
	ob_start();
	$t0 = microtime(true);
	$fz->zip_folder($dst, false);
	$sec = microtime(true) - $t0;
	ob_end_clean();
	$bytes = is_file($fzc) ? (int) filesize($fzc) : -1;
	fz_ab_remove_tree($dst);
	return ['bytes' => $bytes, 'sec' => $sec];
}

/** @return array<string, callable():void> */
function fz_ab_matrix_cells(bool $withStrict): array {
	$cells = [
		'dd' => static function (): void {
			putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP');
			putenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE');
		},
		'raw0' => static function (): void {
			putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=0');
			putenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE');
		},
		'mpq_p' => static function (): void {
			putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP');
			putenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE=1');
		},
		'raw0+mpq' => static function (): void {
			putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP=0');
			putenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE=1');
		},
	];
	if ($withStrict) {
		$cells['strict'] = static function (): void {
			putenv('FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP');
			putenv('FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE');
			putenv('FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT=1');
		};
	}
	return $cells;
}

$snapOuter = fz_ab_env_snapshot(array_merge($envKeys, ['FRACTAL_ZIP_FOLDER_UNIFIED_STREAM', 'FRACTAL_ZIP_AUTO_TUNE']));

$skipDefault = ['test_files54', 'test_files55', 'test_files56', 'test_files57', 'test_files58', 'test_files59', 'test_files50', 'test_files51', 'test_files133'];
$corpora = fz_ab_discover($repo, $skipDefault);

if ($only !== null && $only !== []) {
	$onlySet = array_fill_keys($only, true);
	$corpora = array_values(array_filter($corpora, static fn ($n) => isset($onlySet[$n])));
	foreach ($only as $want) {
		if ($want !== '' && !in_array($want, $corpora, true)) {
			$p = $repo . DIRECTORY_SEPARATOR . $want;
			if (is_dir($p) && strncmp($want, 'test_files', 10) === 0) {
				$corpora[] = $want;
			}
		}
	}
	$corpora = array_values(array_unique($corpora));
	sort($corpora, SORT_NATURAL);
}

if ($limit !== null) {
	$corpora = array_slice($corpora, 0, $limit);
}

$workRoot = __DIR__ . DIRECTORY_SEPARATOR . '.work_ab';
$cells = fz_ab_matrix_cells($withStrict);
$cellIds = array_keys($cells);

$rows = [];
foreach ($corpora as $name) {
	$path = $repo . DIRECTORY_SEPARATOR . $name;
	if (!is_dir($path)) {
		continue;
	}
	$row = ['corpus' => $name];
	foreach ($cellIds as $cid) {
		fz_ab_env_restore($snapOuter);
		fz_ab_clear_bench_literal_defaults($envKeys);
		putenv('FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT');
		($cells[$cid])();
		$r = fz_ab_zip_folder_case($path, $workRoot, $cid);
		$row[$cid . '_B'] = $r['bytes'];
		$row[$cid . '_s'] = round($r['sec'], 3);
	}
	$rows[] = $row;
}

fz_ab_env_restore($snapOuter);

if ($jsonOut) {
	$js = bench_json_encode_try(['repo' => $repo, 'rows' => $rows], true);
	if ($js === null) {
		fwrite(STDERR, '[bench] json_encode failed (raw_tier_unwrap_ab --json): ' . json_last_error_msg() . "\n");
		exit(2);
	}
	echo $js . "\n";
	exit(0);
}

if ($rows === []) {
	fwrite(STDERR, "No corpora matched (use --only=test_files2 or check --repo).\n");
	exit(1);
}

$hdr = ['corpus'];
foreach ($cellIds as $cid) {
	$hdr[] = $cid . ' B';
	$hdr[] = $cid . ' s';
}
echo implode("\t", $hdr) . "\n";
foreach ($rows as $row) {
	$line = [(string) $row['corpus']];
	foreach ($cellIds as $cid) {
		$line[] = (string) (int) $row[$cid . '_B'];
		$line[] = (string) $row[$cid . '_s'];
	}
	echo implode("\t", $line) . "\n";
}
echo "\nΔ bytes vs dd (negative = smaller than default dd cell): ";
echo "raw0, mpq_p, raw0+mpq";
if ($withStrict) {
	echo ", strict";
}
echo "\n";
foreach ($rows as $row) {
	$base = (int) $row['dd_B'];
	if ($base <= 0) {
		continue;
	}
	$parts = [$row['corpus']];
	foreach ($cellIds as $cid) {
		if ($cid === 'dd') {
			continue;
		}
		$b = (int) $row[$cid . '_B'];
		$parts[] = $cid . '=' . ($b < 0 ? '?' : (string) ($b - $base));
	}
	echo implode(' ', $parts) . "\n";
}
