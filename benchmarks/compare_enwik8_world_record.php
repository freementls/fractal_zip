#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare enwik8 world-record JSON to prior baselines.
 *
 * Usage:
 *   php benchmarks/compare_enwik8_world_record.php
 *   php benchmarks/compare_enwik8_world_record.php benchmarks/.enwik8_world_record.json
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

$path = $argv[1] ?? ($repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_world_record.json');
if (!is_file($path)) {
	fwrite(STDERR, "Missing JSON: {$path}\n");
	fwrite(STDERR, "Run: bash benchmarks/run_enwik8_world_record.sh\n");
	exit(1);
}

$raw = file_get_contents($path);
if ($raw === false) {
	fwrite(STDERR, "Cannot read {$path}\n");
	exit(1);
}
$j = json_decode($raw, true);
if (!is_array($j)) {
	fwrite(STDERR, "Invalid JSON: {$path}\n");
	exit(1);
}

$case = null;
foreach ($j['cases'] ?? array() as $c) {
	if (($c['label'] ?? '') === 'test_files109') {
		$case = $c;
		break;
	}
}
if ($case === null) {
	fwrite(STDERR, "No test_files109 row in {$path}\n");
	exit(1);
}

$baselines = array(
	'hutter_record' => 15284944,
	'gt2mb_zpaq_raw' => 19625015,
	'gt2mb_fzc' => 31127813,
);

$fzc = (int) ($case['fzc_bytes'] ?? 0);
$bestExt = (int) ($case['best_ext_folder_bytes'] ?? 0);
$bestAny = min(array_filter(array($fzc, $bestExt), static fn (int $n): bool => $n > 0) ?: array(0));
$rawPaqBytes = null;
$rawPaqTool = null;
$bd = $case['best_ext_breakdown'] ?? array();
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
$dualCandidates = array_filter(array($fzc, $bestExt, $rawPaqBytes), static fn ($n): bool => is_int($n) && $n > 0);
$bestAnyDual = $dualCandidates !== array() ? min($dualCandidates) : $bestAny;
$winner = $case['best_ext_winner'] ?? 'n/a';
$outer = $case['outer_codec'] ?? 'n/a';
$zipSec = (float) ($case['zip_seconds'] ?? 0);
$members = $case['folder_bundle_census']['files'] ?? $case['member_count'] ?? null;

$fmt = static function (int $n): string {
	return number_format($n) . ' B (' . number_format($n / (1024 * 1024), 2) . ' MiB)';
};

echo "enwik8 world-record result\n";
echo "  source: {$path}\n";
echo '  bench_profile: ' . ($j['bench_profile'] ?? 'n/a') . "\n";
echo '  zip_seconds: ' . number_format($zipSec, 1) . "\n";
echo '  members: ' . ($members === null ? 'n/a' : (string) $members) . "\n";
echo '  fzc_bytes: ' . $fmt($fzc) . " (outer={$outer})\n";
echo '  best_ext: ' . $fmt($bestExt) . " (winner={$winner})\n";
echo '  best_any: ' . $fmt($bestAny) . " (min fzc, best_ext)\n";
if ($rawPaqBytes !== null) {
	echo '  raw_paq:  ' . $fmt((int) $rawPaqBytes) . ' (squash ' . ($rawPaqTool ?? 'paq') . " on single enwik8)\n";
}
echo '  best_any_dual: ' . $fmt((int) $bestAnyDual) . " (min fzc, best_ext, raw PAQ)\n\n";

echo "vs baselines\n";
foreach ($baselines as $label => $bytes) {
	$delta = $bestAny - $bytes;
	$pct = $bytes > 0 ? ($delta / $bytes) * 100.0 : 0.0;
	$sign = $delta <= 0 ? 'better' : 'worse';
	echo sprintf(
		"  %-16s %s  (%+.1f%% %s)\n",
		$label . ':',
		$fmt($bytes),
		$pct,
		$sign
	);
}

$verifyOk = $case['verify_ok'] ?? null;
if ($verifyOk !== null) {
	echo "\nverify_ok: " . ($verifyOk ? 'true' : 'false') . "\n";
}

exit(0);
