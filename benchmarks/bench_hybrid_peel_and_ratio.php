#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Hybrid container benchmark: peel list semantics (FFS Tier C) vs fractal_zip encode ratio.
 *
 * Shows that peel hybrid handlers improve browse/list lanes; fractal_zip ratio uses generic
 * PK/OLE peel today (same as before for pbix/msapp unless encode path is extended).
 *
 * Usage:
 *   php benchmarks/build_test_files_hybrid_peel.php
 *   php benchmarks/bench_hybrid_peel_and_ratio.php
 *   php benchmarks/bench_hybrid_peel_and_ratio.php --json
 *   php benchmarks/bench_hybrid_peel_and_ratio.php --ratio-only
 *   php benchmarks/bench_hybrid_peel_and_ratio.php --list-only
 */

$repo = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
$corpus = $repo . DIRECTORY_SEPARATOR . 'test_files_hybrid_peel';
$jsonOut = in_array('--json', $argv, true);
$listOnly = in_array('--list-only', $argv, true);
$ratioOnly = in_array('--ratio-only', $argv, true);

if (!is_dir($corpus)) {
	fwrite(STDERR, "Missing corpus — run: php benchmarks/build_test_files_hybrid_peel.php\n");
	exit(1);
}

$peelRoot = realpath($repo . '/../peel');
if ($peelRoot === false || !is_readable($peelRoot . '/peel.php')) {
	fwrite(STDERR, "peel hub not found beside fractal_zip\n");
	exit(1);
}
require_once $peelRoot . '/peel.php';
peel_bootstrap();

require_once $repo . DIRECTORY_SEPARATOR . 'fractal_zip.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

/** @return array<string, mixed> */
function bench_hybrid_peel_row(string $path): array {
	$base = basename($path);
	$probe = peel_probe('', $path);
	$list = peel_list_members_web($path);
	$profile = (string) (($probe['file_types']['content_profile'] ?? '') ?: '');
	return [
		'file' => $base,
		'handler' => (string) ($probe['handler'] ?? ''),
		'content_profile' => $profile,
		'list_ok' => !empty($list['ok']),
		'list_lane' => (string) ($list['list_lane'] ?? ''),
		'member_count' => is_array($list['members'] ?? null) ? count($list['members']) : 0,
		'code' => (string) ($list['code'] ?? ''),
	];
}

/** @return array{fzc_bytes: int, gzip_bytes: int, verify_ok: bool, seconds: float} */
function bench_hybrid_fzc_folder(string $corpus): array {
	$fzcPath = $corpus . '.fz';
	if (is_file($fzcPath)) {
		@unlink($fzcPath);
	}
	$t0 = microtime(true);
	$fz = new fractal_zip(256, false, true, null, false);
	ob_start();
	try {
		$fz->zip_folder($corpus, false);
	} finally {
		ob_end_clean();
	}
	$seconds = microtime(true) - $t0;
	$fzcBytes = is_file($fzcPath) ? (int) filesize($fzcPath) : 0;
	$verifyOk = false;
	if ($fzcBytes > 0) {
		$repro = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'repro_folder_zip_verify.php';
		if (is_file($repro)) {
			$cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg($repro) . ' '
				. escapeshellarg($corpus) . ' --max-files=32 2>/dev/null';
			exec($cmd, $_, $verifyCode);
			$verifyOk = ($verifyCode === 0);
		}
	}
	$gzipBytes = bench_hybrid_gzip_folder_bytes($corpus);
	if (is_file($fzcPath)) {
		@unlink($fzcPath);
	}
	return [
		'fzc_bytes' => $fzcBytes,
		'gzip_bytes' => $gzipBytes,
		'verify_ok' => $verifyOk,
		'seconds' => $seconds,
	];
}

function bench_hybrid_gzip_folder_bytes(string $dir): int {
	$tar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'hybrid_gzip_' . getmypid() . '.tar';
	$gz = $tar . '.gz';
	@unlink($tar);
	@unlink($gz);
	$cmd = 'tar -cf ' . escapeshellarg($tar) . ' -C ' . escapeshellarg($dir) . ' . 2>/dev/null';
	exec($cmd, $_, $code);
	if ($code !== 0 || !is_file($tar)) {
		return 0;
	}
	exec('gzip -9 -c ' . escapeshellarg($tar), $out, $code2);
	@unlink($tar);
	if ($code2 !== 0) {
		return 0;
	}
	$bytes = implode("\n", $out);
	if ($bytes === '') {
		return 0;
	}
	file_put_contents($gz, $bytes);
	$n = (int) filesize($gz);
	@unlink($gz);
	return $n;
}

$rows = [];
if (!$ratioOnly) {
	foreach (scandir($corpus) ?: [] as $entry) {
		if ($entry === '.' || $entry === '..') {
			continue;
		}
		$path = $corpus . DIRECTORY_SEPARATOR . $entry;
		if (!is_file($path)) {
			continue;
		}
		$rows[] = bench_hybrid_peel_row($path);
	}
	usort($rows, static fn(array $a, array $b): int => strcmp((string) $a['file'], (string) $b['file']));
}

$ratio = null;
$ratioOff = null;
if (!$listOnly) {
	putenv('FRACTAL_ZIP_HYBRID_PEEL=1');
	$ratio = bench_hybrid_fzc_folder($corpus);
	putenv('FRACTAL_ZIP_HYBRID_PEEL=0');
	$ratioOff = bench_hybrid_fzc_folder($corpus);
	putenv('FRACTAL_ZIP_HYBRID_PEEL');
}

$coverage = null;
if (function_exists('peel_strategy_catalog_coverage')) {
	$coverage = peel_strategy_catalog_coverage();
}

$report = [
	'corpus' => 'test_files_hybrid_peel',
	'peel_list' => $rows,
	'folder_ratio_hybrid_on' => $ratio,
	'folder_ratio_hybrid_off' => $ratioOff,
	'catalog_coverage' => $coverage,
	'notes' => [
		'peel_list' => 'Hybrid handlers — FFS browse / peel_list_members_web',
		'folder_ratio' => 'A/B: FRACTAL_ZIP_HYBRID_PEEL=1 vs 0 (msapp noise drop + policy)',
	],
];

if ($jsonOut) {
	echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
	exit(0);
}

echo "=== Hybrid peel list (FFS Tier C / peel hub) ===\n";
if ($ratioOnly) {
	echo "(skipped — --ratio-only)\n";
} else {
	printf("%-16s %-18s %-22s %-6s %s\n", 'file', 'handler', 'content_profile', 'members', 'list_lane');
	foreach ($rows as $r) {
		printf(
			"%-16s %-18s %-22s %6d %s%s\n",
			$r['file'],
			$r['handler'],
			$r['content_profile'],
			$r['member_count'],
			$r['list_lane'],
			$r['list_ok'] ? '' : ' degrade:' . ($r['code'] !== '' ? $r['code'] : 'empty')
		);
	}
}

echo "\n=== fractal_zip folder ratio A/B (HYBRID_PEEL on vs off) ===\n";
if ($listOnly) {
	echo "(skipped — --list-only)\n";
} else {
	foreach ([['ON', $ratio], ['OFF', $ratioOff]] as [$label, $row]) {
		if (!is_array($row)) {
			continue;
		}
		$fzc = (int) ($row['fzc_bytes'] ?? 0);
		$gz = (int) ($row['gzip_bytes'] ?? 0);
		echo sprintf(
			"  hybrid=%-3s fzc=%d gzip9_tar=%d verify=%s zip_s=%.2f\n",
			$label,
			$fzc,
			$gz,
			!empty($row['verify_ok']) ? 'OK' : 'FAIL',
			(float) ($row['seconds'] ?? 0)
		);
	}
	if (is_array($ratio) && is_array($ratioOff)) {
		$d = (int) ($ratio['fzc_bytes'] ?? 0) - (int) ($ratioOff['fzc_bytes'] ?? 0);
		echo sprintf("  Δ fzc (on − off): %+d bytes\n", $d);
	}
}

if (is_array($coverage)) {
	$total = (int) ($coverage['total'] ?? 0);
	$listable = (int) ($coverage['listable'] ?? 0);
	$pct = $total > 0 ? round(100.0 * $listable / $total, 1) : 0.0;
	echo "\n=== Catalog coverage ===\n";
	echo "  extensions={$total} listable={$listable} ({$pct}%)\n";
}

echo "\nImpact summary:\n";
echo "  • Peel hybrid → list lanes + FFS Contents (member counts above).\n";
echo "  • Encode hybrid peel → FRACTAL_ZIP_HYBRID_PEEL filters msapp noise in mode 18 / folder expand.\n";
echo "  • Strategy matrix maps file_types catalog → zip/ole/pdf/… families at scale.\n";
