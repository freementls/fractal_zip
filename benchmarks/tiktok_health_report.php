#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Aggregate TikTok sweep health per corpus:
 * - latest completed winner row (if present)
 * - count and messages for failed sweeps (if any)
 *
 * Usage:
 *   php benchmarks/tiktok_health_report.php
 *   php benchmarks/tiktok_health_report.php --root=benchmarks --json-out=benchmarks/.tiktok_health_report.json --md-out=benchmarks/.tiktok_health_report.md
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$root = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks';
$jsonOut = '';
$mdOut = '';

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
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php benchmarks/tiktok_health_report.php [--root=benchmarks] [--json-out=PATH] [--md-out=PATH]\n";
		exit(0);
	}
}

if (!is_dir($root)) {
	fwrite(STDERR, "tiktok_health_report: root is not a directory: {$root}\n");
	exit(2);
}

/**
 * @return list<string>
 */
function tiktok_health_sweep_dirs(string $root): array
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

function tiktok_health_corpus_from_run_log(string $path): ?string
{
	if (!is_file($path)) {
		return null;
	}
	$fh = @fopen($path, 'r');
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

function tiktok_health_first_failure(string $runLogPath): ?string
{
	if (!is_file($runLogPath)) {
		return null;
	}
	$patterns = array(
		'Corrupt FZB literal',
		'cannot rebuild ZIP',
		'Unknown container payload format',
		'Fatal error',
		'Parse error',
	);
	$fh = @fopen($runLogPath, 'r');
	if (!is_resource($fh)) {
		return null;
	}
	while (($line = fgets($fh)) !== false) {
		$trim = trim($line);
		if ($trim === '') {
			continue;
		}
		foreach ($patterns as $p) {
			if (str_contains($trim, $p)) {
				fclose($fh);
				return $trim;
			}
		}
	}
	fclose($fh);
	return null;
}

$dirs = tiktok_health_sweep_dirs($root);
$winnersByCorpus = array();
$failuresByCorpus = array();

foreach ($dirs as $dir) {
	$sweepName = basename($dir);
	$runLog = $dir . DIRECTORY_SEPARATOR . 'run.log';
	$corpus = tiktok_health_corpus_from_run_log($runLog) ?? '__unknown__';

	$winsPath = $dir . DIRECTORY_SEPARATOR . 'wins.json';
	if (is_file($winsPath)) {
		$wins = bench_json_decode_file_assoc_try($winsPath, 'tiktok_health_report');
		if (is_array($wins) && is_array($wins['tik_speed_winner'] ?? null) && is_array($wins['tok_bytes_winner'] ?? null)) {
			$existing = $winnersByCorpus[$corpus] ?? null;
			if (!is_array($existing) || strcmp((string) $sweepName, (string) ($existing['sweep_name'] ?? '')) > 0) {
				$tik = (array) $wins['tik_speed_winner'];
				$tok = (array) $wins['tok_bytes_winner'];
				$winnersByCorpus[$corpus] = array(
					'sweep_name' => $sweepName,
					'sweep_dir' => $dir,
					'tik_tag' => (string) ($tik['tag'] ?? '?'),
					'tik_zip_seconds' => isset($tik['zip_seconds']) ? (float) $tik['zip_seconds'] : null,
					'tok_tag' => (string) ($tok['tag'] ?? '?'),
					'tok_bytes' => isset($tok['fzc_bytes']) ? (int) $tok['fzc_bytes'] : null,
				);
			}
		}
		continue;
	}

	$failure = tiktok_health_first_failure($runLog);
	if ($failure !== null) {
		if (!isset($failuresByCorpus[$corpus])) {
			$failuresByCorpus[$corpus] = array();
		}
		$failuresByCorpus[$corpus][] = array(
			'sweep_name' => $sweepName,
			'sweep_dir' => $dir,
			'message' => $failure,
		);
	}
}

$allCorpora = array_values(array_unique(array_merge(array_keys($winnersByCorpus), array_keys($failuresByCorpus))));
sort($allCorpora, SORT_STRING);

$rows = array();
foreach ($allCorpora as $corpus) {
	$winner = $winnersByCorpus[$corpus] ?? null;
	$fails = $failuresByCorpus[$corpus] ?? array();
	$rows[] = array(
		'corpus' => $corpus,
		'latest_winner' => $winner,
		'failure_count' => count($fails),
		'failures' => $fails,
	);
}

$payload = array(
	'root' => $root,
	'corpus_count' => count($rows),
	'rows' => $rows,
);
$json = bench_json_encode_try($payload, true);
if ($json === null) {
	fwrite(STDERR, "tiktok_health_report: json_encode failed\n");
	exit(1);
}

if ($jsonOut !== '') {
	file_put_contents($jsonOut, $json . "\n");
}

echo "tiktok_health_report corpus_count=" . (string) count($rows) . "\n";
foreach ($rows as $r) {
	$corpus = (string) ($r['corpus'] ?? '?');
	$winner = $r['latest_winner'] ?? null;
	$failureCount = (int) ($r['failure_count'] ?? 0);
	if (is_array($winner)) {
		$tikTag = (string) ($winner['tik_tag'] ?? '?');
		$tikZip = isset($winner['tik_zip_seconds']) ? sprintf('%.2f', (float) $winner['tik_zip_seconds']) : '?';
		$tokTag = (string) ($winner['tok_tag'] ?? '?');
		$tokBytes = isset($winner['tok_bytes']) ? (string) (int) $winner['tok_bytes'] : '?';
		echo $corpus . " | tik=" . $tikTag . " (" . $tikZip . "s)"
			. " | tok=" . $tokTag . " (" . $tokBytes . " B)"
			. " | failures=" . (string) $failureCount . "\n";
	} else {
		echo $corpus . " | no completed wins | failures=" . (string) $failureCount . "\n";
	}
}

if ($mdOut !== '') {
	$md = "# TikTok Health Report\n\n";
	$md .= "- corpus_count: `" . (string) count($rows) . "`\n\n";
	$md .= "| Corpus | Latest Tik | Latest Tok | Failures |\n";
	$md .= "|---|---|---|---:|\n";
	foreach ($rows as $r) {
		$corpus = (string) ($r['corpus'] ?? '?');
		$winner = $r['latest_winner'] ?? null;
		$failureCount = (int) ($r['failure_count'] ?? 0);
		if (is_array($winner)) {
			$tikTag = (string) ($winner['tik_tag'] ?? '?');
			$tikZip = isset($winner['tik_zip_seconds']) ? sprintf('%.2f', (float) $winner['tik_zip_seconds']) : '?';
			$tokTag = (string) ($winner['tok_tag'] ?? '?');
			$tokBytes = isset($winner['tok_bytes']) ? (string) (int) $winner['tok_bytes'] : '?';
			$md .= "| {$corpus} | {$tikTag} (`{$tikZip}s`) | {$tokTag} (`{$tokBytes} B`) | {$failureCount} |\n";
		} else {
			$md .= "| {$corpus} | (none) | (none) | {$failureCount} |\n";
		}
	}
	$md .= "\n## Failure Details\n\n";
	foreach ($rows as $r) {
		$corpus = (string) ($r['corpus'] ?? '?');
		$fails = is_array($r['failures'] ?? null) ? (array) $r['failures'] : array();
		if ($fails === array()) {
			continue;
		}
		$md .= "### {$corpus}\n\n";
		foreach ($fails as $f) {
			if (!is_array($f)) {
				continue;
			}
			$sweep = (string) ($f['sweep_name'] ?? '?');
			$msg = (string) ($f['message'] ?? '?');
			$md .= "- `{$sweep}`: {$msg}\n";
		}
		$md .= "\n";
	}
	file_put_contents($mdOut, $md);
}

if ($jsonOut !== '') {
	echo "wrote {$jsonOut}\n";
}
if ($mdOut !== '') {
	echo "wrote {$mdOut}\n";
}
