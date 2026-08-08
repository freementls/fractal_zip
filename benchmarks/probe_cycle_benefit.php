#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Probe non-180-193 files for FZCY cycle detect / compression benefit.
 *
 * Usage: php benchmarks/probe_cycle_benefit.php [--json]
 */

$root = realpath(dirname(__DIR__)) ?: dirname(__DIR__);
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_recipes.php';
require_once $root . DIRECTORY_SEPARATOR . 'cycle_encoding_codec.php';
require_once $root . DIRECTORY_SEPARATOR . 'fractal_zip_cycle_preprocess.php';

$jsonOut = in_array('--json', $argv, true);

/** @return list<string> */
function cycle_probe_paths(string $root): array
{
	$paths = array();
	$dirs = array(
		'test_files26', 'test_files29', 'test_files31', 'test_files32',
		'test_files90', 'test_files110', 'test_files115', 'test_files122',
		'test_files140', 'test_files148', 'test_files149', 'test_files150',
		'test_files105', 'test_files107', 'test_files128',
	);
	foreach($dirs as $dir) {
		$full = $root . DIRECTORY_SEPARATOR . $dir;
		if(!is_dir($full)) {
			continue;
		}
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($full, FilesystemIterator::SKIP_DOTS)
		);
		foreach($it as $item) {
			if(!$item->isFile()) {
				continue;
			}
			$sz = (int)$item->getSize();
			if($sz < 80 || $sz > 200_000) {
				continue;
			}
			$paths[] = $item->getPathname();
		}
	}
	sort($paths);
	return $paths;
}

/**
 * @return array<string,mixed>
 */
function cycle_probe_file(string $path): array
{
	$bytes = (string)file_get_contents($path);
	$n = strlen($bytes);
	$t0 = microtime(true);
	$detected = cycle_encoding_detect_whole($bytes, true);
	$detectMs = (microtime(true) - $t0) * 1000.0;
	$t1 = microtime(true);
	$enc = cycle_encoding_codec_encode($bytes);
	$encodeMs = (microtime(true) - $t1) * 1000.0;
	$wire = strlen((string)$enc['payload']);
	$pre = fractal_zip_text_cycle_preprocess($bytes);
	$sidecar = is_array($pre['sidecar']) ? $pre['sidecar'] : array();
	$ltcb = strlen((string)$pre['payload']) + strlen((string)($pre['sidecar']['binary'] ?? json_encode($sidecar, JSON_THROW_ON_ERROR)));
	$gz = strlen((string)gzencode($bytes, 9));
	$zstd = function_exists('zstd_compress') ? strlen((string)zstd_compress($bytes, 19)) : null;
	return array(
		'path' => $path,
		'bytes' => $n,
		'detect' => $detected !== null,
		'combine' => $detected['combine'] ?? null,
		'byte_map' => $detected['byte_map'] ?? null,
		'mode' => (string)($enc['meta']['mode'] ?? ''),
		'wire_B' => $wire,
		'ltcb_B' => $ltcb,
		'gz9_B' => $gz,
		'zstd19_B' => $zstd,
		'delta_gz' => $ltcb - $gz,
		'lag38_pct' => round(100.0 * cycle_encoding_lag_match_rate($bytes, 38), 1),
		'lag76_pct' => round(100.0 * cycle_encoding_lag_match_rate($bytes, 76), 1),
		'entropy' => round(cycle_encoding_shannon_entropy($bytes), 2),
		'detect_ms' => round($detectMs, 1),
	);
}

$rows = array();
foreach(cycle_probe_paths($root) as $path) {
	if(preg_match('/test_files18[0-9]|test_files19[0-3]/', $path)) {
		continue;
	}
	$rows[] = cycle_probe_file($path);
}

$detectHits = array_values(array_filter($rows, static fn(array $r): bool => $r['detect']));
$ltcbWins = array_values(array_filter($rows, static fn(array $r): bool => $r['delta_gz'] < 0));
$cycleLike = array_values(array_filter($rows, static fn(array $r): bool =>
	$r['entropy'] >= 5.5 && $r['lag38_pct'] <= 20.0 && $r['bytes'] >= 500));

usort($ltcbWins, static fn(array $a, array $b): int => $a['delta_gz'] <=> $b['delta_gz']);

if($jsonOut) {
	fwrite(STDOUT, json_encode(array(
		'detect_hits' => $detectHits,
		'ltcb_wins' => $ltcbWins,
		'cycle_like_appearance' => $cycleLike,
	), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
	exit(0);
}

fwrite(STDOUT, "Probed " . count($rows) . " files (excludes test_files180-193)\n\n");

fwrite(STDOUT, "=== detect_whole hits ===\n");
if($detectHits === array()) {
	fwrite(STDOUT, "(none)\n");
} else {
	foreach($detectHits as $r) {
		fwrite(STDOUT, sprintf(
			"%s n=%d combine=%s map=%s wire=%d gz=%d lag38=%.1f%% ent=%.2f\n",
			$r['path'],
			$r['bytes'],
			(string)$r['combine'],
			(string)$r['byte_map'],
			$r['wire_B'],
			$r['gz9_B'],
			$r['lag38_pct'],
			$r['entropy']
		));
	}
}

fwrite(STDOUT, "\n=== cycle_inner LTCB wins vs gz9 ===\n");
if($ltcbWins === array()) {
	fwrite(STDOUT, "(none)\n");
} else {
	foreach($ltcbWins as $r) {
		fwrite(STDOUT, sprintf(
			"%s n=%d ltcb=%d gz=%d delta=%d detect=%s mode=%s\n",
			$r['path'],
			$r['bytes'],
			$r['ltcb_B'],
			$r['gz9_B'],
			$r['delta_gz'],
			$r['detect'] ? 'yes' : 'no',
			$r['mode']
		));
	}
}

fwrite(STDOUT, "\n=== cycle-like appearance (ent>=5.5, lag38<=20%%, n>=500) ===\n");
foreach($cycleLike as $r) {
	fwrite(STDOUT, sprintf(
		"%s n=%d detect=%s lag38=%.1f%% lag76=%.1f%% ent=%.2f wire=%d gz=%d\n",
		$r['path'],
		$r['bytes'],
		$r['detect'] ? 'yes' : 'no',
		$r['lag38_pct'],
		$r['lag76_pct'],
		$r['entropy'],
		$r['wire_B'],
		$r['gz9_B']
	));
}
