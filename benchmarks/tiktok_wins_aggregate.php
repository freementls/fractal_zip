#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Aggregate Tik/Tok winners across completed sweep directories.
 *
 * Usage:
 *   php benchmarks/tiktok_wins_aggregate.php
 *   php benchmarks/tiktok_wins_aggregate.php --root=benchmarks --json-out=benchmarks/.tiktok_wins_aggregate.json
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$root = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks';
$jsonOut = '';
$mdOut = '';
$latestPerCorpus = true;

foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--root=', 7) === 0) {
		$root = trim(substr($a, 7));
		continue;
	}
	if (strncmp($a, '--json-out=', 11) === 0) {
		$jsonOut = trim(substr($a, 11));
		continue;
	}
	if (strncmp($a, '--md-out=', 9) === 0) {
		$mdOut = trim(substr($a, 9));
		continue;
	}
	if ($a === '--all') {
		$latestPerCorpus = false;
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php benchmarks/tiktok_wins_aggregate.php [--root=benchmarks] [--json-out=PATH] [--md-out=PATH] [--all]\n";
		echo "  default mode keeps latest sweep per corpus; pass --all to include every completed sweep.\n";
		exit(0);
	}
}

if (!is_dir($root)) {
	fwrite(STDERR, "tiktok_wins_aggregate: root is not a directory: {$root}\n");
	exit(2);
}

/**
 * @return list<string>
 */
function tiktok_aggregate_sweep_dirs(string $root): array
{
	$list = scandir($root);
	if (!is_array($list)) {
		return array();
	}
	$dirs = array();
	foreach ($list as $name) {
		if (!is_string($name) || $name === '.' || $name === '..') {
			continue;
		}
		if (!str_starts_with($name, '.tiktok_sweep_')) {
			continue;
		}
		$path = $root . DIRECTORY_SEPARATOR . $name;
		if (is_dir($path)) {
			$dirs[] = $path;
		}
	}
	sort($dirs, SORT_STRING);
	return $dirs;
}

function tiktok_aggregate_corpus_from_run_log(string $runLogPath): ?string
{
	if (!is_file($runLogPath)) {
		return null;
	}
	$fh = @fopen($runLogPath, 'r');
	if (!is_resource($fh)) {
		return null;
	}
	$line = fgets($fh);
	fclose($fh);
	if (!is_string($line) || $line === '') {
		return null;
	}
	if (!preg_match('/\bcorpus=([^\s]+)/', $line, $m)) {
		return null;
	}
	$corpus = trim((string) ($m[1] ?? ''));
	return $corpus !== '' ? $corpus : null;
}

$dirs = tiktok_aggregate_sweep_dirs($root);
$rows = array();
foreach ($dirs as $dir) {
	$winsJson = $dir . DIRECTORY_SEPARATOR . 'wins.json';
	if (!is_file($winsJson)) {
		continue;
	}
	$wins = bench_json_decode_file_assoc_try($winsJson, 'tiktok_wins_aggregate');
	if (!is_array($wins)) {
		continue;
	}
	$tik = $wins['tik_speed_winner'] ?? null;
	$tok = $wins['tok_bytes_winner'] ?? null;
	if (!is_array($tik) || !is_array($tok)) {
		continue;
	}
	$corpus = tiktok_aggregate_corpus_from_run_log($dir . DIRECTORY_SEPARATOR . 'run.log');
	$rows[] = array(
		'sweep_dir' => $dir,
		'sweep_name' => basename($dir),
		'corpus' => $corpus,
		'tik_tag' => (string) ($tik['tag'] ?? '?'),
		'tik_zip_seconds' => isset($tik['zip_seconds']) ? (float) $tik['zip_seconds'] : null,
		'tik_bytes' => isset($tik['fzc_bytes']) ? (int) $tik['fzc_bytes'] : null,
		'tok_tag' => (string) ($tok['tag'] ?? '?'),
		'tok_bytes' => isset($tok['fzc_bytes']) ? (int) $tok['fzc_bytes'] : null,
		'tok_zip_seconds' => isset($tok['zip_seconds']) ? (float) $tok['zip_seconds'] : null,
	);
}

if ($rows === array()) {
	fwrite(STDERR, "tiktok_wins_aggregate: no completed sweeps with wins.json under {$root}\n");
	exit(1);
}

if ($latestPerCorpus) {
	$latestByCorpus = array();
	foreach ($rows as $r) {
		$key = (string) ($r['corpus'] ?? '');
		if ($key === '') {
			$key = '__unknown__';
		}
		if (!isset($latestByCorpus[$key]) || strcmp((string) $r['sweep_name'], (string) $latestByCorpus[$key]['sweep_name']) > 0) {
			$latestByCorpus[$key] = $r;
		}
	}
	$rows = array_values($latestByCorpus);
	usort($rows, static function (array $a, array $b): int {
		return strcmp((string) ($a['corpus'] ?? ''), (string) ($b['corpus'] ?? ''));
	});
}

$tikCounts = array();
$tokCounts = array();
foreach ($rows as $r) {
	$tikTag = (string) ($r['tik_tag'] ?? '?');
	$tokTag = (string) ($r['tok_tag'] ?? '?');
	$tikCounts[$tikTag] = (int) ($tikCounts[$tikTag] ?? 0) + 1;
	$tokCounts[$tokTag] = (int) ($tokCounts[$tokTag] ?? 0) + 1;
}
arsort($tikCounts, SORT_NUMERIC);
arsort($tokCounts, SORT_NUMERIC);

$payload = array(
	'root' => $root,
	'mode' => $latestPerCorpus ? 'latest_per_corpus' : 'all_completed_sweeps',
	'corpus_count' => count($rows),
	'tik_profile_counts' => $tikCounts,
	'tok_profile_counts' => $tokCounts,
	'rows' => $rows,
);

$json = bench_json_encode_try($payload, true);
if ($json === null) {
	fwrite(STDERR, "tiktok_wins_aggregate: json_encode failed\n");
	exit(1);
}

if ($jsonOut !== '') {
	file_put_contents($jsonOut, $json . "\n");
}

echo "tiktok_wins_aggregate mode=" . ($latestPerCorpus ? 'latest_per_corpus' : 'all_completed_sweeps')
	. " corpus_count=" . (string) count($rows) . "\n";
echo "tik profile counts: ";
$parts = array();
foreach ($tikCounts as $tag => $n) {
	$parts[] = $tag . '=' . (string) $n;
}
echo implode(', ', $parts) . "\n";
echo "tok profile counts: ";
$parts = array();
foreach ($tokCounts as $tag => $n) {
	$parts[] = $tag . '=' . (string) $n;
}
echo implode(', ', $parts) . "\n";

foreach ($rows as $r) {
	$corpus = (string) ($r['corpus'] ?? '?');
	$sweep = (string) ($r['sweep_name'] ?? '?');
	$tikTag = (string) ($r['tik_tag'] ?? '?');
	$tikZip = isset($r['tik_zip_seconds']) && is_float($r['tik_zip_seconds']) ? sprintf('%.2f', (float) $r['tik_zip_seconds']) : '?';
	$tokTag = (string) ($r['tok_tag'] ?? '?');
	$tokBytes = isset($r['tok_bytes']) && is_int($r['tok_bytes']) ? (string) $r['tok_bytes'] : '?';
	echo $corpus . " | " . $sweep
		. " | tik=" . $tikTag . " (zip_s=" . $tikZip . ")"
		. " | tok=" . $tokTag . " (bytes=" . $tokBytes . ")\n";
}

if ($mdOut !== '') {
	$md = "# TikTok Winners Aggregate\n\n";
	$md .= "- mode: `" . ($latestPerCorpus ? 'latest_per_corpus' : 'all_completed_sweeps') . "`\n";
	$md .= "- corpus_count: `" . (string) count($rows) . "`\n\n";
	$md .= "## Profile Frequency\n\n";
	$md .= "- tik: ";
	$parts = array();
	foreach ($tikCounts as $tag => $n) {
		$parts[] = "`" . $tag . "=" . (string) $n . "`";
	}
	$md .= implode(', ', $parts) . "\n";
	$md .= "- tok: ";
	$parts = array();
	foreach ($tokCounts as $tag => $n) {
		$parts[] = "`" . $tag . "=" . (string) $n . "`";
	}
	$md .= implode(', ', $parts) . "\n\n";
	$md .= "## Per Corpus\n\n";
	$md .= "| Corpus | Sweep | Tik | Tok |\n";
	$md .= "|---|---|---|---|\n";
	foreach ($rows as $r) {
		$corpus = (string) ($r['corpus'] ?? '?');
		$sweep = (string) ($r['sweep_name'] ?? '?');
		$tikTag = (string) ($r['tik_tag'] ?? '?');
		$tikZip = isset($r['tik_zip_seconds']) && is_float($r['tik_zip_seconds']) ? sprintf('%.2f', (float) $r['tik_zip_seconds']) : '?';
		$tokTag = (string) ($r['tok_tag'] ?? '?');
		$tokBytes = isset($r['tok_bytes']) && is_int($r['tok_bytes']) ? (string) $r['tok_bytes'] : '?';
		$md .= "| {$corpus} | {$sweep} | {$tikTag} (`{$tikZip}s`) | {$tokTag} (`{$tokBytes} B`) |\n";
	}
	file_put_contents($mdOut, $md);
}

if ($jsonOut !== '') {
	echo "wrote {$jsonOut}\n";
}
if ($mdOut !== '') {
	echo "wrote {$mdOut}\n";
}
