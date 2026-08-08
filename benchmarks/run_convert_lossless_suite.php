#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Run lossless convert × fractal_zip benchmark suite via run_benchmarks.php.
 *
 * Builds corpora (build_convert_bench_corpora.php), runs at low CPU/IO priority,
 * skips when enwik8 world-record encode holds test_files109, compares baseline vs converted .fz bytes.
 *
 * Usage:
 *   php benchmarks/run_convert_lossless_suite.php
 *   php benchmarks/run_convert_lossless_suite.php --build-only
 *   php benchmarks/run_convert_lossless_suite.php --report-only
 *   php benchmarks/run_convert_lossless_suite.php --from-json=benchmarks/.convert_lossless_bench_run.json
 *
 * KPI: **baseline .fz bytes vs convert-preprocessed .fz bytes** only.
 * Runs with `--no-best-ext` (no phda9/paq min-ext tournament — that lane is world-record only).
 * Does NOT use --bench-profile=world-record. Does NOT touch test_files109.
 */
$repo = dirname(__DIR__);
$benchDir = __DIR__;
$buildOnly = in_array('--build-only', $argv ?? [], true);
$reportOnly = in_array('--report-only', $argv ?? [], true);
$fromJson = null;
foreach ($argv ?? [] as $arg) {
	if (str_starts_with($arg, '--from-json=')) {
		$fromJson = substr($arg, 12);
	}
}

function cls_run(string $cmd, string $logPath): int
{
	$full = $cmd . ' 2>&1 | tee ' . escapeshellarg($logPath);
	fwrite(STDERR, "[convert-bench] {$cmd}\n");
	return system($full) === false ? 1 : 0;
}

function cls_enwik_busy(string $repo): bool
{
	$script = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_encode_busy_check.sh';
	if (!is_file($script)) {
		return false;
	}
	exec('bash ' . escapeshellarg($script), $out, $code);
	return $code === 0;
}

if (!$reportOnly) {
	$build = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'build_convert_bench_corpora.php';
	if (!is_file($build)) {
		fwrite(STDERR, "Missing {$build}\n");
		exit(1);
	}
	passthru(PHP_BINARY . ' ' . escapeshellarg($build), $buildCode);
	if ($buildCode !== 0) {
		exit($buildCode);
	}
	if ($buildOnly) {
		exit(0);
	}
}

$manifestPath = $benchDir . DIRECTORY_SEPARATOR . 'convert_bench_manifest.json';
$manifest = json_decode((string) file_get_contents($manifestPath), true);
if (!is_array($manifest) || !isset($manifest['experiments'])) {
	fwrite(STDERR, "Invalid manifest\n");
	exit(1);
}

$benchRunJson = $benchDir . DIRECTORY_SEPARATOR . '.convert_lossless_bench_run.json';
if ($fromJson === null && $reportOnly && is_readable($benchRunJson)) {
	$fromJson = $benchRunJson;
}

if ($fromJson !== null && is_readable($fromJson)) {
	$benchJson = json_decode((string) file_get_contents($fromJson), true);
} elseif ($reportOnly) {
	fwrite(STDERR, "Report-only: missing bench JSON (run suite first or pass --from-json=)\n");
	exit(1);
} else {
	if (cls_enwik_busy($repo)) {
		fwrite(STDERR, "[convert-bench] enwik8 encode active — continuing at nice/ionice (will not use test_files109)\n");
	}

	$labels = $manifest['corpus_labels'] ?? [];
	$labels = array_values(array_filter($labels, static fn (string $l): bool => $l !== 'test_files109'));
	sort($labels, SORT_NATURAL);
	$only = implode(',', $labels);

	$outJson = $benchDir . DIRECTORY_SEPARATOR . '.convert_lossless_bench_run.json';
	$log = $benchDir . DIRECTORY_SEPARATOR . 'logs' . DIRECTORY_SEPARATOR . 'convert_lossless_bench.log';
	if (!is_dir(dirname($log))) {
		mkdir(dirname($log), 0755, true);
	}

	$runner = $benchDir . DIRECTORY_SEPARATOR . 'run_benchmarks_low_priority.sh';
	$cmd = escapeshellarg($runner)
		. ' --only=' . escapeshellarg($only)
		. ' --bench-profile=medium-balanced'
		. ' --maximum-size=20M'
		. ' --no-verify'
		. ' --no-best-ext'
		. ' --no-multipass'
		. ' --case-timeout=180'
		. ' --out-json=' . escapeshellarg($outJson)
		. ' --no-save-last-json';

	$code = cls_run('cd ' . escapeshellarg($repo) . ' && ' . $cmd, $log);
	if ($code !== 0) {
		fwrite(STDERR, "[convert-bench] run_benchmarks exited {$code}; see {$log}\n");
	}
	if (!is_readable($outJson)) {
		fwrite(STDERR, "Bench output missing: {$outJson}\n");
		exit(1);
	}
	$benchJson = json_decode((string) file_get_contents($outJson), true);
}

if (!is_array($benchJson) || !isset($benchJson['cases'])) {
	fwrite(STDERR, "Invalid bench JSON\n");
	exit(1);
}

/** @var array<string, array<string, mixed>> $byLabel */
$byLabel = [];
foreach ($benchJson['cases'] as $row) {
	$lab = (string) ($row['label'] ?? '');
	if ($lab !== '') {
		$byLabel[$lab] = $row;
	}
}

/** @var list<array<string, mixed>> $results */
$results = [];
foreach ($manifest['experiments'] as $exp) {
	$baseLab = (string) $exp['baseline_label'];
	$convLab = (string) $exp['converted_label'];
	$base = $byLabel[$baseLab] ?? null;
	$conv = $byLabel[$convLab] ?? null;
	if ($base === null || $conv === null) {
		$results[] = [
			'id' => $exp['id'],
			'action' => $exp['action'],
			'status' => 'missing_bench_row',
			'baseline_label' => $baseLab,
			'converted_label' => $convLab,
		];
		continue;
	}
	$baseFzc = (int) ($base['fzc_bytes'] ?? 0);
	$convFzc = (int) ($conv['fzc_bytes'] ?? 0);
	$savedFzc = $baseFzc > 0 && $convFzc > 0 ? $baseFzc - $convFzc : null;
	$pctFzc = $savedFzc !== null && $baseFzc > 0 ? round(100.0 * $savedFzc / $baseFzc, 2) : null;

	$win = $savedFzc !== null && $savedFzc > 0;
	$results[] = [
		'id' => $exp['id'],
		'action' => $exp['action'],
		'note' => $exp['note'] ?? '',
		'baseline_label' => $baseLab,
		'converted_label' => $convLab,
		'parent_label' => $exp['parent_label'] ?? null,
		'baseline_fzc' => $baseFzc,
		'converted_fzc' => $convFzc,
		'fzc_saved_bytes' => $savedFzc,
		'fzc_saved_pct' => $pctFzc,
		'win' => $win,
		'baseline_raw' => (int) ($base['raw_bytes'] ?? 0),
		'converted_raw' => (int) ($conv['raw_bytes'] ?? 0),
	];
}

usort($results, static fn (array $a, array $b): int => ((int) ($b['fzc_saved_bytes'] ?? 0)) <=> ((int) ($a['fzc_saved_bytes'] ?? 0)));

$wins = array_values(array_filter($results, static fn (array $r): bool => !empty($r['win'])));
$totalSaved = array_sum(array_map(static fn (array $r): int => max(0, (int) ($r['fzc_saved_bytes'] ?? 0)), $wins));

$report = [
	'generated_at' => gmdate('c'),
	'kpi' => 'fzc_bytes_baseline_minus_converted',
	'bench_source' => $fromJson ?? ($benchDir . '/.convert_lossless_bench_run.json'),
	'bench_profile' => $benchJson['bench_profile'] ?? null,
	'no_best_ext' => true,
	'experiments' => $results,
	'wins' => $wins,
	'losses' => array_values(array_filter($results, static fn (array $r): bool => isset($r['win']) && $r['win'] === false && ($r['fzc_saved_bytes'] ?? 0) < 0)),
	'total_fzc_saved_bytes_on_wins' => $totalSaved,
];
$reportJson = $benchDir . DIRECTORY_SEPARATOR . '.convert_lossless_bench.json';
file_put_contents($reportJson, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

$md = [];
$md[] = '# Lossless convert × fractal_zip benchmark';
$md[] = '';
$md[] = 'Generated: ' . $report['generated_at'];
$md[] = '';
$md[] = 'KPI: **`.fz` bytes only** (baseline corpus vs convert-preprocessed corpus).';
$md[] = 'Bench uses `--no-best-ext` — no phda9/paq min-ext lane (world-record preset only).';
$md[] = 'Positive **Δ fzc** = lossless convert helped fractal_zip compress smaller.';
$md[] = '';
$md[] = '**Total saved on wins:** ' . number_format($totalSaved) . ' bytes across ' . count($wins) . ' actions.';
$md[] = '';
$md[] = '## Wins';
$md[] = '';
$md[] = '| Action | Baseline | Converted | Base fzc | Conv fzc | Δ fzc | % |';
$md[] = '|--------|----------|-----------|--------:|---------:|------:|--:|';
foreach ($report['wins'] as $r) {
	$md[] = sprintf(
		'| %s | %s | %s | %s | %s | %s | %s%% |',
		$r['action'],
		$r['baseline_label'],
		$r['converted_label'],
		number_format((int) $r['baseline_fzc']),
		number_format((int) $r['converted_fzc']),
		number_format((int) $r['fzc_saved_bytes']),
		$r['fzc_saved_pct'],
	);
}
$md[] = '';
$md[] = '## All experiments';
$md[] = '';
$md[] = '| Action | Base fzc | Conv fzc | Δ fzc | Win | Raw base → conv |';
$md[] = '|--------|--------:|---------:|------:|:---:|----------------:|';
foreach ($results as $r) {
	if (($r['status'] ?? '') === 'missing_bench_row') {
		$md[] = '| ' . $r['action'] . ' | — | — | missing | — | — |';
		continue;
	}
	$md[] = sprintf(
		'| %s | %s | %s | %s | %s | %s → %s |',
		$r['action'],
		number_format((int) $r['baseline_fzc']),
		number_format((int) $r['converted_fzc']),
		($r['fzc_saved_bytes'] ?? 0) >= 0 ? '+' . number_format((int) $r['fzc_saved_bytes']) : number_format((int) $r['fzc_saved_bytes']),
		!empty($r['win']) ? 'yes' : 'no',
		number_format((int) $r['baseline_raw']),
		number_format((int) $r['converted_raw']),
	);
}
$md[] = '';
$md[] = 'Re-run: `php benchmarks/run_convert_lossless_suite.php`';

$reportMd = $benchDir . DIRECTORY_SEPARATOR . 'CONVERT_LOSSLESS_BENCH.md';
file_put_contents($reportMd, implode("\n", $md));

echo 'Wins: ' . count($report['wins']) . ' / ' . count($results) . "\n";
echo 'Report: benchmarks/CONVERT_LOSSLESS_BENCH.md' . "\n";
echo 'JSON: benchmarks/.convert_lossless_bench.json' . "\n";
foreach (array_slice($report['wins'], 0, 8) as $w) {
	echo '  WIN ' . $w['action'] . ': saved ' . number_format((int) $w['fzc_saved_bytes']) . " bytes ({$w['fzc_saved_pct']}%)\n";
}
