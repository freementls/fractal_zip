#!/usr/bin/env php
<?php
declare(strict_types=1);
/**
 * Scoreboard from a filled ultra / bytes-first run_benchmarks JSON.
 *
 * Goals (product bar):
 *   - Untied byte wins on every case (fzc sole-smallest vs gzip/7z/min-ext)
 *   - TOTAL zip_seconds < 10× TOTAL fair min-ext winner compress wall (same
 *     gzip/7z/ext set as sole: time of the byte-minimum candidate; byte ties → min time)
 *   - Prefer best possible bytes within that envelope
 *
 * Usage: php benchmarks/analyze_ultra_full_table.php [path.json]
 */
$path = $argv[1] ?? (__DIR__ . '/.ultra_full_table.json');
$wallMult = 10.0;
if (!is_file($path)) {
	fwrite(STDERR, "missing {$path}\n");
	exit(1);
}
$j = json_decode((string) file_get_contents($path), true);
if (!is_array($j)) {
	fwrite(STDERR, "bad json\n");
	exit(1);
}
$cases = $j['cases'] ?? [];
$s = $j['summary'] ?? [];
$totals = $j['column_totals'] ?? ($j['totals'] ?? []);
echo 'table_complete=' . json_encode($j['table_complete'] ?? null) . "\n";
echo 'cases=' . count($cases) . ' skipped=' . count($j['skipped_cases'] ?? []) . "\n";
// sole/among printed after row walk (summary fields go stale after merge patches).

$sumZip = 0.0;
$sumExt = 0.0;
$seenZip = 0;
$seenExt = 0;
$soleN = 0;
$amongN = 0;
$losses = [];
$tied = [];
$slowVsExt = [];
$slow = [];

/**
 * Compress wall of the sole-baseline byte winner (gzip / 7z / soft best_ext).
 * Matches the same candidate set as untied sole; on a byte tie pick the faster tool.
 *
 * @param array<string,mixed> $r
 */
$fairExtSeconds = static function (array $r): ?float {
	$cands = [];
	$map = [
		'gzip' => ['gzip9_bundle_bytes', 'gzip9_seconds'],
		'7z' => ['seven_zip_folder_bytes', 'seven_zip_seconds'],
		'ext' => ['best_ext_folder_bytes', 'best_ext_seconds'],
	];
	foreach ($map as $tag => [$bk, $sk]) {
		if (!isset($r[$bk]) || $r[$bk] === null) {
			continue;
		}
		if (!isset($r[$sk]) || $r[$sk] === null) {
			continue;
		}
		$cands[] = [(int) $r[$bk], (float) $r[$sk], $tag];
	}
	if ($cands === []) {
		return isset($r['best_ext_seconds']) && $r['best_ext_seconds'] !== null
			? (float) $r['best_ext_seconds']
			: null;
	}
	usort(
		$cands,
		static function (array $a, array $b): int {
			return $a[0] <=> $b[0] ?: $a[1] <=> $b[1];
		}
	);

	return $cands[0][1];
};

foreach ($cases as $r) {
	if (!is_array($r)) {
		continue;
	}
	$name = (string) ($r['label'] ?? '?');
	$fz = $r['fzc_bytes'] ?? null;
	$zs = $r['zip_seconds'] ?? null;
	$es = $fairExtSeconds($r);
	if ($zs !== null) {
		$sumZip += (float) $zs;
		$seenZip++;
	}
	if ($es !== null) {
		$sumExt += (float) $es;
		$seenExt++;
	}
	if ($fz === null) {
		continue;
	}
	$cands = [];
	foreach (['gzip9_bundle_bytes' => 'gzip', 'seven_zip_folder_bytes' => '7z', 'best_ext_folder_bytes' => 'ext'] as $k => $tag) {
		if (isset($r[$k]) && $r[$k] !== null) {
			$cands[$tag] = (int) $r[$k];
		}
	}
	$wc = $r['winner_compression'] ?? [];
	$sole = is_array($wc) && $wc === ['fzc'];
	$among = is_array($wc) && in_array('fzc', $wc, true);
	// Prefer live recomputation from baseline cells (summary goes stale after merges).
	if ($cands !== []) {
		$min = min($cands);
		$delta = (int) $fz - $min;
		if ($delta < 0) {
			$sole = true;
			$among = true;
		} elseif ($delta === 0) {
			$sole = false;
			$among = true;
		} else {
			$sole = false;
			$among = false;
		}
	}
	if ($sole) {
		$soleN++;
	}
	if ($among) {
		$amongN++;
	}
	if ($cands !== []) {
		$min = min($cands);
		$delta = (int) $fz - $min;
		if ($delta > 0) {
			$vs = array_search($min, $cands, true);
			$losses[] = [
				'name' => $name,
				'fz' => (int) $fz,
				'min' => $min,
				'delta' => $delta,
				'vs' => (string) $vs,
				'zip_s' => $zs !== null ? (float) $zs : null,
				'ext_s' => $es !== null ? (float) $es : null,
				'raw' => (int) ($r['raw_bytes'] ?? 0),
			];
		} elseif ($delta === 0 && !$sole) {
			$tied[] = [
				'name' => $name,
				'fz' => (int) $fz,
				'zip_s' => $zs !== null ? (float) $zs : null,
				'ext_s' => $es !== null ? (float) $es : null,
				'winners' => is_array($wc) ? implode(',', $wc) : '?',
			];
		}
	}
	if ($zs !== null && $es !== null && (float) $zs > (float) $es) {
		$slowVsExt[] = [
			'name' => $name,
			'zip_s' => (float) $zs,
			'ext_s' => (float) $es,
			'ratio' => (float) $es > 0.0 ? ((float) $zs / (float) $es) : null,
			'sole' => $sole,
			'among' => $among,
			'fz' => (int) $fz,
			'raw' => (int) ($r['raw_bytes'] ?? 0),
		];
	}
	if ($zs !== null && (float) $zs >= 30.0) {
		$slow[] = [
			'name' => $name,
			'zip_s' => (float) $zs,
			'ext_s' => $es !== null ? (float) $es : null,
			'fz' => (int) $fz,
			'raw' => (int) ($r['raw_bytes'] ?? 0),
			'sole' => $sole,
		];
	}
}
usort($losses, static fn($a, $b) => $b['delta'] <=> $a['delta']);
usort($tied, static fn($a, $b) => $a['name'] <=> $b['name']);
usort($slowVsExt, static fn($a, $b) => ($b['zip_s'] - $b['ext_s']) <=> ($a['zip_s'] - $a['ext_s']));
usort($slow, static fn($a, $b) => $b['zip_s'] <=> $a['zip_s']);

// Always sum from case rows (column_totals goes stale after merge patches).
$totZip = $seenZip > 0 ? $sumZip : null;
$totExt = $seenExt > 0 ? $sumExt : null;

echo 'sole=' . $soleN . ' among=' . $amongN . "\n";

echo "\n=== GOAL: untied bytes + stay within {$wallMult}× fair min-ext encode wall ===\n";
$n = count($cases);
echo "sole_bytes={$soleN}/{$n}  among_bytes={$amongN}/{$n}  byte_losses=" . count($losses)
	. '  byte_ties=' . count($tied) . "\n";
if ($totZip !== null && $totExt !== null) {
	$budget = $wallMult * $totExt;
	$beat = $totZip < $budget;
	printf(
		"TOTAL zip_s=%.2f  TOTAL fair_ext_s=%.2f  budget_%.0f×=%.2f  ratio=%.3f×  within_budget=%s\n",
		$totZip,
		$totExt,
		$wallMult,
		$budget,
		$totExt > 0.0 ? ($totZip / $totExt) : 0.0,
		$beat ? 'YES' : 'NO'
	);
} else {
	echo "TOTAL zip_s/ext_s: incomplete (missing cells)\n";
}
$goalOk = ($n > 0 && $soleN === $n && count($losses) === 0 && count($tied) === 0
	&& $totZip !== null && $totExt !== null && $totZip < ($wallMult * $totExt));
echo 'product_bar=' . ($goalOk ? 'PASS' : 'FAIL') . "\n";

echo "\n=== sole-loss / not-among-smallest (fz > min baseline), largest delta first ===\n";
foreach (array_slice($losses, 0, 40) as $L) {
	printf(
		"%-18s delta=%+8d fz=%d min=%d (%s) zip_s=%s ext_s=%s raw=%d\n",
		$L['name'],
		$L['delta'],
		$L['fz'],
		$L['min'],
		$L['vs'],
		$L['zip_s'] === null ? '—' : sprintf('%.2f', $L['zip_s']),
		$L['ext_s'] === null ? '—' : sprintf('%.2f', $L['ext_s']),
		$L['raw']
	);
}
echo 'loss_rows=' . count($losses) . "\n";

echo "\n=== byte ties (fz == min but not sole) ===\n";
foreach (array_slice($tied, 0, 40) as $L) {
	printf(
		"%-18s fz=%d winners=%s zip_s=%s ext_s=%s\n",
		$L['name'],
		$L['fz'],
		$L['winners'],
		$L['zip_s'] === null ? '—' : sprintf('%.2f', $L['zip_s']),
		$L['ext_s'] === null ? '—' : sprintf('%.2f', $L['ext_s'])
	);
}
echo 'tie_rows=' . count($tied) . "\n";

echo "\n=== zip_s > fair_ext_s (largest absolute gap first) ===\n";
foreach (array_slice($slowVsExt, 0, 40) as $L) {
	printf(
		"%-18s zip_s=%8.2f ext_s=%8.2f gap=%+8.2f ratio=%s sole=%s\n",
		$L['name'],
		$L['zip_s'],
		$L['ext_s'],
		$L['zip_s'] - $L['ext_s'],
		$L['ratio'] === null ? '—' : sprintf('%.2f×', $L['ratio']),
		$L['sole'] ? 'yes' : 'no'
	);
}
echo 'slower_than_ext_rows=' . count($slowVsExt) . "\n";

echo "\n=== slow fzc (zip_s ≥ 30), largest first ===\n";
foreach (array_slice($slow, 0, 25) as $L) {
	printf(
		"%-18s zip_s=%8.2f ext_s=%s fz=%d raw=%d sole_only=%s\n",
		$L['name'],
		$L['zip_s'],
		$L['ext_s'] === null ? '—' : sprintf('%.2f', $L['ext_s']),
		$L['fz'],
		$L['raw'],
		$L['sole'] ? 'yes' : 'no'
	);
}

echo "\n=== spend more time? (loss≥256B and zip_s<15) ===\n";
$spend = array_values(array_filter($losses, static fn($L) => $L['delta'] >= 256 && ($L['zip_s'] ?? 999) < 15.0));
usort($spend, static fn($a, $b) => $b['delta'] <=> $a['delta']);
foreach (array_slice($spend, 0, 15) as $L) {
	printf("%-18s delta=%+d zip_s=%.2f\n", $L['name'], $L['delta'], (float) $L['zip_s']);
}
echo "\n=== spend less time? (sole and zip_s≥60 and zip_s>ext_s) ===\n";
foreach (array_slice($slowVsExt, 0, 20) as $L) {
	if (!$L['sole'] || $L['zip_s'] < 60.0) {
		continue;
	}
	printf("%-18s zip_s=%.2f ext_s=%.2f (sole — trim wall)\n", $L['name'], $L['zip_s'], $L['ext_s']);
}

exit($goalOk ? 0 : 2);
