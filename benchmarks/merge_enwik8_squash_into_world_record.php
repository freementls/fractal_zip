#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Merge .enwik8_{zpaq,paq}_squash.json into world-record case breakdown + best_ext.
 */

$repo = dirname(__DIR__);
$wrPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_world_record.json';
if (!is_file($wrPath)) {
	fwrite(STDERR, "Missing {$wrPath}\n");
	exit(1);
}
$j = json_decode((string) file_get_contents($wrPath), true);
if (!is_array($j)) {
	exit(1);
}
$case = null;
$idx = null;
foreach ($j['cases'] ?? array() as $i => $c) {
	if (($c['label'] ?? '') === 'test_files109') {
		$case = $c;
		$idx = $i;
		break;
	}
}
if ($case === null) {
	exit(1);
}
$bd = is_array($case['best_ext_breakdown'] ?? null) ? $case['best_ext_breakdown'] : array();

$zpaqPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_zpaq_squash.json';
if (is_file($zpaqPath)) {
	$z = json_decode((string) file_get_contents($zpaqPath), true);
	if (is_array($z) && isset($z['bytes'])) {
		$bd['zpaq_raw'] = array('bytes' => (int) $z['bytes'], 'seconds' => (float) ($z['seconds'] ?? 0));
	}
}
$paqPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json';
if (is_file($paqPath)) {
	$p = json_decode((string) file_get_contents($paqPath), true);
	if (is_array($p) && isset($p['bytes'], $p['tool'])) {
		$id = (string) $p['tool'];
		$bd[$id] = array('bytes' => (int) $p['bytes'], 'seconds' => (float) ($p['wall_seconds'] ?? $p['seconds'] ?? 0));
	}
}

$minB = PHP_INT_MAX;
$winner = null;
foreach ($bd as $id => $row) {
	if (!is_array($row) || isset($row['note'])) {
		continue;
	}
	$b = (int) ($row['bytes'] ?? 0);
	if ($b > 0 && $b < $minB) {
		$minB = $b;
		$winner = (string) $id;
	}
}
if ($minB !== PHP_INT_MAX) {
	$case['best_ext_folder_bytes'] = $minB;
	$case['best_ext_winner'] = $winner;
	$case['best_ext_breakdown'] = $bd;
	$fzc = (int) ($case['fzc_bytes'] ?? 0);
	$case['winner_compression'] = array($minB < $fzc ? 'ext' : 'fzc');
}
$j['cases'][$idx] = $case;
file_put_contents($wrPath, json_encode($j, JSON_PRETTY_PRINT));
echo "Merged squash into {$wrPath}\n";
echo '  best_ext_winner: ' . ($winner ?? 'n/a') . ' (' . ($minB === PHP_INT_MAX ? 'n/a' : number_format($minB)) . " B)\n";
