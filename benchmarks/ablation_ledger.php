#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Progressive ablation ledger CLI.
 *
 *   php benchmarks/ablation_ledger.php --demo
 *   php benchmarks/ablation_ledger.php --from-json=benchmarks/.foo.json --out=benchmarks/.ablation.json
 *   php benchmarks/ablation_ledger.php --from-tracker
 */

require_once __DIR__ . '/ablation_ledger_lib.php';

$opts = getopt('', array('demo', 'from-json:', 'from-tracker', 'out:', 'stage:', 'help'));
if (isset($opts['help'])) {
	fwrite(STDOUT, "Usage: php ablation_ledger.php --demo | --from-json=PATH | --from-tracker [--out=PATH] [--stage=full]\n");
	exit(0);
}

$stage = (string) ($opts['stage'] ?? 'full');
$rows = array();

if (isset($opts['demo']) || isset($opts['from-tracker']) || (!isset($opts['from-json']) && !isset($opts['demo']))) {
	// Canonical examples from BYTES_WIN_TRACKER.md protocol (synthetic closeness for demo).
	$rows = array(
		array(
			'arm' => 'test_files55',
			'stage' => 'full',
			'fzc_bytes' => 1000,
			'gzip9_bundle_bytes' => 1200,
			'best_ext_folder_bytes' => 1100,
			'verify_ok' => true,
			'verify_mismatch_files' => 0,
			'verify_file_count' => 100,
		),
		array(
			'arm' => 'test_files56',
			'stage' => 'full',
			'fzc_bytes' => 4636816,
			'gzip9_bundle_bytes' => 5992745,
			'best_ext_folder_bytes' => 5865028,
			'verify_ok' => false,
			'verify_mismatch_files' => 0,
			'verify_file_count' => 90,
			'semantic_ok' => true,
			'legibility_delta' => 0.0,
		),
		array(
			'arm' => 'test_files56_broken_sha',
			'stage' => 'full',
			'fzc_bytes' => 4000000,
			'gzip9_bundle_bytes' => 5000000,
			'best_ext_folder_bytes' => 4800000,
			'verify_ok' => false,
			'verify_mismatch_files' => 40,
			'verify_file_count' => 90,
			'semantic_ok' => false,
		),
		array(
			'arm' => 'test_files_unverified',
			'stage' => 'full',
			'fzc_bytes' => 800,
			'gzip9_bundle_bytes' => 1000,
			'verify_ok' => null,
		),
		array(
			'arm' => 'test_files_no_win',
			'stage' => 'full',
			'fzc_bytes' => 2000,
			'gzip9_bundle_bytes' => 1000,
			'verify_ok' => true,
		),
	);
}

if (isset($opts['from-json'])) {
	$path = (string) $opts['from-json'];
	if (!is_file($path)) {
		fwrite(STDERR, "Missing JSON: {$path}\n");
		exit(1);
	}
	$bench = json_decode((string) file_get_contents($path), true);
	if (!is_array($bench)) {
		fwrite(STDERR, "Invalid JSON: {$path}\n");
		exit(1);
	}
	$rows = fractal_zip_ablation_rows_from_bench_json($bench);
}

$result = fractal_zip_ablation_evaluate_rows($rows, $stage);
$ledger = array(
	'generated' => gmdate('c'),
	'source' => isset($opts['from-json']) ? (string) $opts['from-json'] : 'demo_tracker_cases',
	'protocol' => 'sample5→medium→full; soft closeness never replaces SHA for lossless paste',
	'summary' => array(
		'rows' => count($result['evaluated']),
		'promote_count' => $result['promote_count'],
		'block_count' => $result['block_count'],
		'soft_rescues' => $result['soft_rescues'],
		'hard_blocks' => $result['hard_blocks'],
		'naive_bytes_win_and_verify_false_would_paste' => fractal_zip_ablation_count_naive_false_paste($rows),
	),
	'rows' => $result['evaluated'],
);

$out = (string) ($opts['out'] ?? (__DIR__ . '/.ablation.json'));
fractal_zip_ablation_ledger_write($out, $ledger);

printf("Ablation ledger → %s\n", $out);
printf(
	"rows=%d promote=%d block=%d soft_rescues=%d hard_blocks=%d naive_false_paste_blocked=%d\n",
	$ledger['summary']['rows'],
	$ledger['summary']['promote_count'],
	$ledger['summary']['block_count'],
	$ledger['summary']['soft_rescues'],
	$ledger['summary']['hard_blocks'],
	$ledger['summary']['naive_bytes_win_and_verify_false_would_paste'] - $ledger['summary']['soft_rescues']
);

/** Count rows that would naively paste as wins despite verify_ok=false. */
function fractal_zip_ablation_count_naive_false_paste(array $rows): int
{
	$n = 0;
	foreach ($rows as $row) {
		$fzc = isset($row['fzc_bytes']) ? (int) $row['fzc_bytes'] : null;
		$base = fractal_zip_ablation_baseline_best($row);
		$verify = $row['verify_ok'] ?? null;
		if ($fzc !== null && $base !== null && $fzc <= $base && $verify === false) {
			$n++;
		}
	}
	return $n;
}
