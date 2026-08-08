#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare a lifestyle bench JSON against a pinned reference.
 *
 *   php benchmarks/audit_pareto_vs_ref.php --ref=benchmarks/.pareto_lifestyle_ref.json --json=benchmarks/.last_bench.json
 *   php benchmarks/audit_pareto_vs_ref.php --ref=... --json=... --max-time-ratio=0.3 --max-bytes-ratio=0.9975
 *
 * Exit 0 when time/bytes ratios and sole-win constraints pass; non-zero otherwise.
 */

$repoRoot = dirname(__DIR__);
require_once $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$refPath = '';
$jsonPath = '';
$maxTimeRatio = 0.3;
$maxBytesRatio = 0.9975;
$requireSole = true;
$subsetOk = false;

foreach (array_slice($argv, 1) as $a) {
	if (!is_string($a)) {
		continue;
	}
	if (strncmp($a, '--ref=', 6) === 0) {
		$refPath = trim(substr($a, 6));
		continue;
	}
	if (strncmp($a, '--json=', 7) === 0) {
		$jsonPath = trim(substr($a, 7));
		continue;
	}
	if (strncmp($a, '--max-time-ratio=', 17) === 0) {
		$v = trim(substr($a, 17));
		if ($v !== '' && is_numeric($v)) {
			$maxTimeRatio = max(0.0, (float) $v);
		}
		continue;
	}
	if (strncmp($a, '--max-bytes-ratio=', 18) === 0) {
		$v = trim(substr($a, 18));
		if ($v !== '' && is_numeric($v)) {
			$maxBytesRatio = max(0.0, (float) $v);
		}
		continue;
	}
	if ($a === '--allow-ties') {
		$requireSole = false;
		continue;
	}
	if ($a === '--subset-ok') {
		$subsetOk = true;
		continue;
	}
	if ($a === '--help' || $a === '-h') {
		echo "Usage: php audit_pareto_vs_ref.php --ref=REF.json --json=CUR.json [--max-time-ratio=0.3] [--max-bytes-ratio=0.9975] [--allow-ties] [--subset-ok]\n";
		exit(0);
	}
}

if ($refPath === '' || $jsonPath === '' || !is_readable($refPath) || !is_readable($jsonPath)) {
	fwrite(STDERR, "audit_pareto_vs_ref: need readable --ref= and --json=\n");
	exit(2);
}

$ref = bench_json_decode_file_assoc_try($refPath, 'audit_pareto_vs_ref ref');
$cur = bench_json_decode_file_assoc_try($jsonPath, 'audit_pareto_vs_ref json');
if ($ref === null || $cur === null) {
	exit(2);
}

/**
 * @param array<string,mixed> $payload
 * @return array{by_label: array<string,array<string,mixed>>, zip_s: float, fzc_b: int, sole: int, among: int, n: int, non_sole: list<string>}
 */
function audit_pareto_index_cases(array $payload, bool $requireSole): array
{
	$by = [];
	$zip = 0.0;
	$fzc = 0;
	$sole = 0;
	$among = 0;
	$nonSole = [];
	$rows = isset($payload['cases']) && is_array($payload['cases']) ? $payload['cases'] : [];
	foreach ($rows as $row) {
		if (!is_array($row)) {
			continue;
		}
		$lab = isset($row['label']) ? (string) $row['label'] : '';
		if ($lab === '') {
			continue;
		}
		$by[$lab] = $row;
		$zip += (float) ($row['zip_seconds'] ?? 0.0);
		$fzcB = (int) ($row['fzc_bytes'] ?? 0);
		$fzc += $fzcB;
		$gz = isset($row['gzip9_bundle_bytes']) && $row['gzip9_bundle_bytes'] !== null ? (int) $row['gzip9_bundle_bytes'] : null;
		$z7 = isset($row['seven_zip_folder_bytes']) && $row['seven_zip_folder_bytes'] !== null ? (int) $row['seven_zip_folder_bytes'] : null;
		$ext = isset($row['best_ext_folder_bytes']) && $row['best_ext_folder_bytes'] !== null ? (int) $row['best_ext_folder_bytes'] : null;
		$cands = [];
		foreach ([$gz, $z7, $ext] as $c) {
			if ($c !== null && $c > 0) {
				$cands[] = $c;
			}
		}
		$bestOther = $cands !== [] ? min($cands) : null;
		$wc = $row['winner_compression'] ?? [];
		$fzcAmong = is_array($wc) && in_array('fzc', $wc, true);
		if ($fzcAmong) {
			$among++;
		}
		$isSole = $bestOther !== null && $fzcB > 0 && $fzcB < $bestOther;
		if ($isSole) {
			$sole++;
		} elseif ($requireSole && $fzcB > 0) {
			$nonSole[] = $lab;
		}
	}

	return [
		'by_label' => $by,
		'zip_s' => $zip,
		'fzc_b' => $fzc,
		'sole' => $sole,
		'among' => $among,
		'n' => count($by),
		'non_sole' => $nonSole,
	];
}

$R = audit_pareto_index_cases($ref, false);
$C = audit_pareto_index_cases($cur, $requireSole);

if (!$subsetOk && $C['n'] !== $R['n']) {
	fwrite(STDERR, "audit_pareto_vs_ref: case count mismatch cur={$C['n']} ref={$R['n']} (pass --subset-ok for partial runs)\n");
	exit(1);
}

$timeRatio = ($R['zip_s'] > 0.0) ? ($C['zip_s'] / $R['zip_s']) : (($C['zip_s'] > 0.0) ? INF : 0.0);
$bytesRatio = ($R['fzc_b'] > 0) ? ($C['fzc_b'] / $R['fzc_b']) : (($C['fzc_b'] > 0) ? INF : 0.0);

$byteRegs = [];
$timeRegs = [];
foreach ($C['by_label'] as $lab => $row) {
	if (!isset($R['by_label'][$lab])) {
		continue;
	}
	$rb = (int) ($R['by_label'][$lab]['fzc_bytes'] ?? 0);
	$cb = (int) ($row['fzc_bytes'] ?? 0);
	$rt = (float) ($R['by_label'][$lab]['zip_seconds'] ?? 0.0);
	$ct = (float) ($row['zip_seconds'] ?? 0.0);
	if ($rb > 0 && $cb > $rb) {
		$byteRegs[] = [$cb - $rb, $lab, $rb, $cb];
	}
	if ($rt > 0.0 && $ct > $rt) {
		$timeRegs[] = [$ct - $rt, $lab, $rt, $ct];
	}
}
rsort($byteRegs);
rsort($timeRegs);

printf(
	"audit_pareto_vs_ref: cases=%d/%d zip_s=%.3f→%.3f (ratio=%.3f max=%.3f) fzc_B=%s→%s (ratio=%.4f max=%.4f) sole=%d among=%d\n",
	$C['n'],
	$R['n'],
	$R['zip_s'],
	$C['zip_s'],
	$timeRatio,
	$maxTimeRatio,
	number_format($R['fzc_b']),
	number_format($C['fzc_b']),
	$bytesRatio,
	$maxBytesRatio,
	$C['sole'],
	$C['among']
);

if ($byteRegs !== []) {
	echo "top byte growth vs ref:\n";
	foreach (array_slice($byteRegs, 0, 12) as $r) {
		printf("  %-16s %+d  (%s → %s)\n", $r[1], $r[0], number_format($r[2]), number_format($r[3]));
	}
}
if ($timeRegs !== []) {
	echo "top time growth vs ref:\n";
	foreach (array_slice($timeRegs, 0, 8) as $r) {
		printf("  %-16s %+.3fs  (%.3f → %.3f)\n", $r[1], $r[0], $r[2], $r[3]);
	}
}

$ok = true;
if ($timeRatio > $maxTimeRatio + 1e-9) {
	fwrite(STDERR, "FAIL: time ratio {$timeRatio} > {$maxTimeRatio}\n");
	$ok = false;
}
if ($bytesRatio > $maxBytesRatio + 1e-12) {
	fwrite(STDERR, "FAIL: bytes ratio {$bytesRatio} > {$maxBytesRatio}\n");
	$ok = false;
}
if ($requireSole && $C['non_sole'] !== []) {
	fwrite(STDERR, 'FAIL: non-sole winners: ' . implode(',', array_slice($C['non_sole'], 0, 40)) . "\n");
	$ok = false;
}

exit($ok ? 0 : 1);
