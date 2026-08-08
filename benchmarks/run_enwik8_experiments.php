#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Grid enwik8 world-record experiments (no web-ref).
 *
 * Usage:
 *   php benchmarks/run_enwik8_experiments.php [--quick]
 *
 * Writes benchmarks/.enwik8_exp_<name>.json per experiment.
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';

$quick = in_array('--quick', $argv, true);
$onlyRun = null;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--only-exp=')) {
		$onlyRun = substr($arg, 11);
	}
}
$only = 'test_files109';
$php = PHP_BINARY ?: 'php';
$encodeOnly = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_enwik8_encode_only.php';

bench_world_record_apply_env_defaults();

$experiments = array(
	'pp32' => array('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER' => '32'),
	'pp48' => array('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER' => '48'),
	'pp64' => array('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER' => '64'),
	'pp96' => array('FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER' => '96'),
	'unified0' => array('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM' => '0'),
	'unified1' => array('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM' => '1'),
);

if (!$quick) {
	$experiments['semantic1'] = array(
		'FRACTAL_ZIP_ENWIK_SEMANTIC_PACK' => '1',
		'FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER' => '64',
	);
}

// Seed pp64 from last world-record JSON when present (same default pages/member).
$wrSeed = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_world_record.json';
$pp64Out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_pp64.json';
if (is_file($wrSeed) && !is_file($pp64Out)) {
	@copy($wrSeed, $pp64Out);
}

$rows = array();
foreach ($experiments as $name => $overrides) {
	if ($onlyRun !== null && $onlyRun !== '' && $name !== $onlyRun) {
		continue;
	}
	$out = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_exp_' . $name . '.json';
	foreach ($overrides as $k => $v) {
		putenv($k . '=' . $v);
	}
	$cmd = escapeshellarg($php) . ' ' . escapeshellarg($encodeOnly) . ' --name=' . escapeshellarg($name);
	fwrite(STDERR, "[enwik8_exp] {$name} → {$out}\n");
	$t0 = microtime(true);
	passthru($cmd, $code);
	$sec = microtime(true) - $t0;
	$case = null;
	if (is_file($out)) {
		$j = json_decode((string) file_get_contents($out), true);
		foreach ($j['cases'] ?? array() as $c) {
			if (($c['label'] ?? '') === $only) {
				$case = $c;
				break;
			}
		}
	}
	$fzc = (int) ($case['fzc_bytes'] ?? 0);
	$ext = (int) ($case['best_ext_folder_bytes'] ?? 0);
	$bestAny = ($fzc > 0 && $ext > 0) ? min($fzc, $ext) : max($fzc, $ext);
	$rows[] = array(
		'name' => $name,
		'exit' => $code,
		'seconds' => round($sec, 1),
		'fzc_bytes' => $fzc,
		'best_ext_bytes' => $ext,
		'best_any' => $bestAny,
		'outer_codec' => $case['outer_codec'] ?? null,
		'outer_zpaq_method' => $case['outer_zpaq_method'] ?? null,
		'folder_unified_stream' => $case['folder_unified_stream'] ?? null,
		'member_count' => $case['folder_bundle_census']['files'] ?? null,
		'verify_ok' => $case['verify_ok'] ?? null,
		'json' => $out,
	);
}

echo "enwik8 experiment grid\n";
echo str_pad('name', 12) . str_pad('fzc', 14) . str_pad('best_ext', 14) . str_pad('best_any', 14) . "members verify\n";
foreach ($rows as $r) {
	echo str_pad($r['name'], 12)
		. str_pad($r['fzc_bytes'] > 0 ? number_format($r['fzc_bytes']) : 'n/a', 14)
		. str_pad($r['best_ext_bytes'] > 0 ? number_format($r['best_ext_bytes']) : 'n/a', 14)
		. str_pad($r['best_any'] > 0 ? number_format($r['best_any']) : 'n/a', 14)
		. str_pad((string) ($r['member_count'] ?? 'n/a'), 8)
		. (($r['verify_ok'] ?? false) ? 'ok' : 'FAIL')
		. "\n";
}

$summaryPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_experiments_summary.json';
file_put_contents($summaryPath, json_encode(array('generated' => date('c'), 'rows' => $rows), JSON_PRETTY_PRINT));
echo "\nWrote {$summaryPath}\n";
