#!/usr/bin/env php
<?php
declare(strict_types=1);

$repo = dirname(__DIR__);
$glob = glob($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_*.json') ?: array();
$rows = array();
foreach ($glob as $path) {
	$base = basename($path, '.json');
	$name = str_replace('.enwik8_exp_', '', $base);
	$j = json_decode((string) file_get_contents($path), true);
	if (!is_array($j)) {
		continue;
	}
	$case = null;
	foreach ($j['cases'] ?? array() as $c) {
		if (($c['label'] ?? '') === 'test_files109') {
			$case = $c;
			break;
		}
	}
	if ($case === null) {
		continue;
	}
	$fzc = (int) ($case['fzc_bytes'] ?? 0);
	$ext = (int) ($case['best_ext_folder_bytes'] ?? 0);
	$rows[] = array(
		'name' => $name,
		'fzc_bytes' => $fzc,
		'best_ext_bytes' => $ext,
		'best_any' => ($fzc > 0 && $ext > 0) ? min($fzc, $ext) : max($fzc, $ext),
		'outer_codec' => $case['outer_codec'] ?? null,
		'outer_zpaq_method' => $case['outer_zpaq_method'] ?? null,
		'folder_unified_stream' => $case['folder_unified_stream'] ?? null,
		'folder_unified_stream_env' => $case['folder_unified_stream_env'] ?? null,
		'folder_per_member_best_env' => $case['folder_per_member_best_env'] ?? null,
		'member_count' => $case['member_count'] ?? ($case['folder_bundle_census']['files'] ?? null),
		'json' => $path,
	);
}

$paqPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json';
if (is_file($paqPath)) {
	$p = json_decode((string) file_get_contents($paqPath), true);
	if (is_array($p) && isset($p['bytes'])) {
		$rows[] = array(
			'name' => 'raw_paq_' . ($p['tool'] ?? 'paq'),
			'fzc_bytes' => 0,
			'best_ext_bytes' => (int) $p['bytes'],
			'best_any' => (int) $p['bytes'],
			'note' => 'squash on raw enwik8',
		);
	}
}

usort($rows, static fn ($a, $b) => strcmp($a['name'], $b['name']));
$summaryPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_experiments_summary.json';
file_put_contents($summaryPath, json_encode(array('generated' => date('c'), 'rows' => $rows), JSON_PRETTY_PRINT));

echo "enwik8 experiment summary\n";
foreach ($rows as $r) {
	$uEnv = $r['folder_unified_stream_env'] ?? null;
	printf(
		"  %-12s fzc=%s wire_unified=%s env_unified=%s members=%s\n",
		$r['name'],
		$r['fzc_bytes'] > 0 ? number_format($r['fzc_bytes']) : 'n/a',
		isset($r['folder_unified_stream']) ? ($r['folder_unified_stream'] ? '1' : '0') : '-',
		$uEnv === null ? '-' : (string) $uEnv,
		(string) ($r['member_count'] ?? 'n/a')
	);
}
echo "Wrote {$summaryPath}\n";
