#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Refresh best_ext breakdown for test_files109 (zpaq_raw + PAQ squash; no full .fz encode).
 *
 * Usage: php benchmarks/bench_enwik8_best_ext_only.php
 */

$repo = dirname(__DIR__);
$php = PHP_BINARY ?: 'php';
$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_best_ext_refresh.json';

$breakdown = array();
$t0 = microtime(true);

foreach (array('bench_enwik8_zpaq_squash.php' => 'zpaq_raw', 'bench_enwik8_paq_squash.php' => null) as $script => $fixedId) {
	$path = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . $script;
	if (!is_file($path)) {
		continue;
	}
	passthru(escapeshellarg($php) . ' ' . escapeshellarg($path), $code);
	$jsonName = str_replace('.php', '', $script);
	$jsonName = str_replace('bench_enwik8_', '.enwik8_', $jsonName) . '.json';
	$jsonPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . $jsonName;
	if (!is_file($jsonPath)) {
		continue;
	}
	$j = json_decode((string) file_get_contents($jsonPath), true);
	if (!is_array($j) || !isset($j['bytes'])) {
		continue;
	}
	$id = $fixedId ?? (string) ($j['tool'] ?? 'paq');
	$breakdown[$id] = array(
		'bytes' => (int) $j['bytes'],
		'seconds' => (float) ($j['wall_seconds'] ?? $j['seconds'] ?? 0),
	);
}

$minB = PHP_INT_MAX;
$winner = null;
foreach ($breakdown as $id => $row) {
	$b = (int) $row['bytes'];
	if ($b < $minB) {
		$minB = $b;
		$winner = $id;
	}
}

$payload = array(
	'generated' => date('c'),
	'bytes' => $minB === PHP_INT_MAX ? null : $minB,
	'winner' => $winner,
	'seconds' => round(microtime(true) - $t0, 6),
	'breakdown' => $breakdown,
	'hutter_record' => 15284944,
);
file_put_contents($out, json_encode($payload, JSON_PRETTY_PRINT));

echo "enwik8 best_ext refresh (squash only)\n";
foreach ($breakdown as $id => $row) {
	echo '  ' . $id . ': ' . number_format((int) $row['bytes']) . " B\n";
}
echo '  winner: ' . ($winner ?? 'n/a') . "\n";
echo "  json: {$out}\n";
