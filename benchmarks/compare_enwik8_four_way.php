#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Four-way enwik8 comparison: .fz, zpaq, Hutter record, web-ref track.
 *
 * Usage:
 *   php benchmarks/compare_enwik8_four_way.php
 *   php benchmarks/compare_enwik8_four_way.php benchmarks/.enwik8_world_record.json benchmarks/.enwik8_web_ref_track.json
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$worldPath = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_world_record.json');
$webPath = $argv[2] ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_web_ref_track.json');

function compare_enwik8_load_case(string $path, string $label = 'test_files109'): ?array
{
	if (!is_file($path)) {
		return null;
	}
	$raw = file_get_contents($path);
	if ($raw === false) {
		return null;
	}
	$j = json_decode($raw, true);
	if (!is_array($j)) {
		return null;
	}
	foreach ($j['cases'] ?? array() as $c) {
		if (($c['label'] ?? '') === $label) {
			return $c;
		}
	}
	return null;
}

$world = compare_enwik8_load_case($worldPath);
if ($world === null) {
	fwrite(STDERR, "Missing world-record JSON: {$worldPath}\n");
	fwrite(STDERR, "Run: bash benchmarks/run_enwik8_world_record.sh\n");
	exit(1);
}

$webCase = compare_enwik8_load_case($webPath);
$webProbePath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_web_ref_probe.json';
$webEstimatePath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_web_ref_estimate.json';
$webProbe = is_file($webProbePath) ? json_decode((string) file_get_contents($webProbePath), true) : null;
$webEst = is_file($webEstimatePath) ? json_decode((string) file_get_contents($webEstimatePath), true) : null;

$hutter = 15284944;
$raw = (int) ($world['raw_bytes'] ?? 100000000);
$fzc = (int) ($world['fzc_bytes'] ?? 0);
$zpaq = (int) ($world['best_ext_folder_bytes'] ?? 0);
$zpaqWinner = (string) ($world['best_ext_winner'] ?? 'zpaq_raw');
$rawPaqBytes = null;
$rawPaqTool = null;
$bd = $world['best_ext_breakdown'] ?? array();
if (is_array($bd)) {
	foreach (array('phda9', 'paq8px', 'paq8pxd', 'cmix') as $toolId) {
		if (!isset($bd[$toolId]) || !is_array($bd[$toolId])) {
			continue;
		}
		$b = (int) ($bd[$toolId]['bytes'] ?? 0);
		if ($b > 0 && ($rawPaqBytes === null || $b < $rawPaqBytes)) {
			$rawPaqBytes = $b;
			$rawPaqTool = $toolId;
		}
	}
}
$paqSquashPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_paq_squash.json';
if (is_file($paqSquashPath)) {
	$pj = json_decode((string) file_get_contents($paqSquashPath), true);
	if (is_array($pj) && isset($pj['bytes'])) {
		$b = (int) $pj['bytes'];
		if ($b > 0 && ($rawPaqBytes === null || $b < $rawPaqBytes)) {
			$rawPaqBytes = $b;
			$rawPaqTool = (string) ($pj['tool'] ?? 'phda9');
		}
	}
}
$dualCandidates = array_filter(array($fzc, $zpaq, $rawPaqBytes), static fn ($n): bool => is_int($n) && $n > 0);
$bestAnyDual = $dualCandidates !== array() ? min($dualCandidates) : min(array_filter(array($fzc, $zpaq), static fn (int $n): bool => $n > 0) ?: array(0));

$webBytes = null;
$webSource = 'n/a';
if ($webCase !== null && isset($webCase['fzc_bytes'])) {
	$webBytes = (int) $webCase['fzc_bytes'];
	$webSource = 'encode FRACTAL_ZIP_WEB_REF=1 (' . basename($webPath) . ')';
} elseif (is_array($webProbe) && isset($webProbe['estimated_fzc_with_web_refs'])) {
	$webBytes = (int) $webProbe['estimated_fzc_with_web_refs'];
	$corpusM = (int) ($webProbe['corpus_matches'] ?? 0);
	$urlM = (int) ($webProbe['url_literal_matches'] ?? 0);
	$net = (int) ($webProbe['net_savings_bytes'] ?? 0);
	$webSource = 'piece probe (' . basename($webProbePath) . ", {$corpusM} corpus + {$urlM} url, net {$net} B)";
} elseif (is_array($webEst) && isset($webEst['estimated_fzc_with_web_refs'])) {
	$webBytes = (int) $webEst['estimated_fzc_with_web_refs'];
	$webSource = 'heuristic estimate (' . basename($webEstimatePath) . ')';
}

$fmt = static function (?int $n): string {
	if ($n === null || $n <= 0) {
		return 'n/a';
	}
	return number_format($n) . ' B (' . number_format($n / (1024 * 1024), 2) . ' MiB)';
};

$pctRaw = static function (?int $n) use ($raw): string {
	if ($n === null || $n <= 0 || $raw <= 0) {
		return 'n/a';
	}
	return number_format(100.0 * $n / $raw, 2) . '% of raw';
};

$rows = array(
	array('label' => 'fzc (world-record)', 'bytes' => $fzc, 'note' => 'outer=' . ($world['outer_codec'] ?? 'n/a')),
	array('label' => 'zpaq (' . $zpaqWinner . ')', 'bytes' => $zpaq, 'note' => 'best native passthrough'),
	array('label' => 'raw PAQ squash', 'bytes' => $rawPaqBytes, 'note' => $rawPaqTool !== null ? ('single-file ' . $rawPaqTool) : 'run bench_enwik8_paq_squash.php'),
	array('label' => 'best_any_dual', 'bytes' => $bestAnyDual > 0 ? $bestAnyDual : null, 'note' => 'min(fzc, best_ext, raw PAQ); no web-ref'),
	array('label' => 'Hutter (phda9)', 'bytes' => $hutter, 'note' => '2017 prize record'),
	array('label' => 'web-ref track', 'bytes' => $webBytes, 'note' => $webSource),
);

echo "enwik8 four-way comparison (100,000,000 B raw)\n";
echo "  world-record: {$worldPath}\n";
if ($webCase !== null) {
	echo "  web-ref:      {$webPath}\n";
}
echo "\n";
printf("%-22s %26s %14s %s\n", 'Method', 'Compressed', '% raw', 'Notes');
echo str_repeat('-', 90) . "\n";
foreach ($rows as $r) {
	printf(
		"%-22s %26s %14s %s\n",
		$r['label'],
		$fmt($r['bytes']),
		$pctRaw($r['bytes']),
		$r['note']
	);
}

$valid = array_filter(array_column($rows, 'bytes'), static fn ($b) => is_int($b) && $b > 0);
if ($valid !== array()) {
	$best = min($valid);
	echo "\nSmallest tracked: " . $fmt($best) . "\n";
	foreach ($rows as $r) {
		if ($r['bytes'] === $best) {
			echo "  → " . $r['label'] . "\n";
			break;
		}
	}
}

if ($webBytes === null) {
	echo "\nWeb-ref track missing. Run:\n";
	echo "  php benchmarks/bench_enwik8_web_ref_probe.php\n";
	echo "  php benchmarks/bench_enwik8_web_ref_estimate.php\n";
	echo "  bash benchmarks/run_enwik8_web_ref_track.sh   # optional full encode\n";
}

$verify = $world['verify_ok'] ?? null;
if ($verify !== null) {
	echo "\nworld-record verify_ok: " . ($verify ? 'true' : 'false') . "\n";
}
if ($webCase !== null) {
	$webVerify = $webCase['verify_ok'] ?? null;
	if ($webVerify !== null) {
		echo 'web-ref verify_ok: ' . ($webVerify ? 'true' : 'false') . "\n";
	}
	if ($fzc > 0 && $webBytes !== null && $webBytes > 0 && $webBytes < $fzc) {
		$delta = $fzc - $webBytes;
		echo 'web-ref vs world-record fzc: -' . number_format($delta) . " B (~" . number_format($delta / 1024, 1) . " KiB)\n";
	}
}

exit(0);
