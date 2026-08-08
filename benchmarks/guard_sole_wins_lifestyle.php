#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Lifestyle sole-win regression gate against pinned reference.
 *
 *   php benchmarks/guard_sole_wins_lifestyle.php
 *   php benchmarks/guard_sole_wins_lifestyle.php --json=benchmarks/.last_bench.json
 *   php benchmarks/guard_sole_wins_lifestyle.php --ref=benchmarks/.pareto_lifestyle_ref.json
 *
 * Exit 0 when current JSON has ≥ ref sole count and no sole→tie/loss regressions
 * on overlapping labels (strict: fzc < best_other).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$repo = dirname(__DIR__);
$refPath = $repo . '/benchmarks/.pareto_lifestyle_ref.json';
$jsonPath = $repo . '/benchmarks/.pareto_lifestyle_half.json';

foreach (array_slice($argv, 1) as $a) {
	if (strncmp($a, '--ref=', 6) === 0) {
		$refPath = trim(substr($a, 6));
		continue;
	}
	if (strncmp($a, '--json=', 7) === 0) {
		$jsonPath = trim(substr($a, 7));
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php guard_sole_wins_lifestyle.php [--ref=REF.json] [--json=CUR.json]\n";
		exit(0);
	}
}

if (!is_readable($refPath) || !is_readable($jsonPath)) {
	fwrite(STDERR, "guard_sole_wins_lifestyle: need readable --ref and --json\n");
	exit(2);
}

/**
 * @return array{sole: int, by: array<string,array{fzc:int,best:int,margin:int}>}
 */
function guard_sole_index(string $path): array {
	$j = bench_json_decode_file_assoc_try($path, 'guard_sole');
	if ($j === null) {
		return array('sole' => 0, 'by' => array());
	}
	$by = array();
	$sole = 0;
	foreach (($j['cases'] ?? []) as $r) {
		if (!is_array($r)) {
			continue;
		}
		$lab = (string) ($r['label'] ?? '');
		$fzc = (int) ($r['fzc_bytes'] ?? 0);
		$cands = array();
		foreach (array('gzip9_bundle_bytes', 'seven_zip_folder_bytes', 'best_ext_folder_bytes') as $k) {
			if (isset($r[$k]) && $r[$k] !== null && (int) $r[$k] > 0) {
				$cands[] = (int) $r[$k];
			}
		}
		if ($lab === '' || $fzc <= 0 || $cands === array()) {
			continue;
		}
		$best = min($cands);
		$margin = $best - $fzc;
		$by[$lab] = array('fzc' => $fzc, 'best' => $best, 'margin' => $margin);
		if ($fzc < $best) {
			$sole++;
		}
	}
	return array('sole' => $sole, 'by' => $by);
}

$ref = guard_sole_index($refPath);
$cur = guard_sole_index($jsonPath);
$fail = array();

if ($cur['sole'] < $ref['sole']) {
	$fail[] = "sole count regressed: cur={$cur['sole']} ref={$ref['sole']}";
}

foreach ($ref['by'] as $lab => $rr) {
	if ($rr['margin'] <= 0) {
		continue; // ref was not a sole
	}
	if (!isset($cur['by'][$lab])) {
		continue; // subset ok
	}
	$cc = $cur['by'][$lab];
	if ($cc['margin'] <= 0) {
		$fail[] = "lost sole: {$lab} (was margin {$rr['margin']}, now fzc={$cc['fzc']} best={$cc['best']})";
	}
}

echo "guard_sole_wins_lifestyle: ref_sole={$ref['sole']} cur_sole={$cur['sole']}\n";
if ($fail !== array()) {
	fwrite(STDERR, "FAIL:\n  " . implode("\n  ", $fail) . "\n");
	exit(1);
}
echo "OK\n";
exit(0);
