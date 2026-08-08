#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * enwik8 slice probe + extrapolation gate (beat 14.6 MiB total S + Hutter hardware rules by default).
 *
 * Runs @384p wire cases, extrapolates full total S (archive + decomp + dict), launches full encode only on PASS.
 *
 * Usage:
 *   php benchmarks/run_enwik8_beat146_probe.php
 *   php benchmarks/run_enwik8_beat146_probe.php --full-memory
 *
 * @see docs/HUTTER_PRIZE_COMPLIANCE.md
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_beat146_gate.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_hutter_prize.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_low_memory_env.php';
if (!in_array('--full-memory', $argv, true)) {
	putenv('FRACTAL_ZIP_LOW_MEMORY=1');
	bench_low_memory_apply_env();
}
$phpPrefix = bench_low_memory_shell_php_prefix();

$pages = 384;
$launchEncode = false;
$dryRun = false;
$with768 = false;
$targetMode = ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--pages=')) {
		$pages = max(32, (int) substr($arg, 8));
	} elseif ($arg === '--launch-encode') {
		$launchEncode = true;
	} elseif ($arg === '--dry-run') {
		$dryRun = true;
	} elseif ($arg === '--with-768') {
		$with768 = true;
	} elseif (str_starts_with($arg, '--target=')) {
		$t = substr($arg, 9);
		$targetMode = match ($t) {
			'beat146', 'beat146_hardware', 'hardware', 'default' => ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
			'hutter', 'hutter_total', 'prize' => ENWIK8_HUTTER_TARGET_HUTTER_TOTAL,
			'hutter_archive', 'archive' => ENWIK8_HUTTER_TARGET_HUTTER_ARCHIVE,
			'cmix', 'cmix_lab', 'cmix_archive' => ENWIK8_HUTTER_TARGET_CMIX_LAB,
			default => $t,
		};
	}
}
$targetInfo = enwik8_hutter_target_for_mode($targetMode);
$archiveBudget = enwik8_hutter_archive_budget_for_total_s($targetInfo['total'], $repo);

$cases = array(
	'split_inner_fztx_mono_mi',
	'split_inner_phda9_xml_single_stream_pp96',
	'split_inner_phda9_xml_single_stream_lstm',
	'split_inner_phda9_xml_pp96_parallel',
	'split_inner_phda9_xml_mono_mi_dict',
	'split_inner_phda9_article_pp96_parallel',
);
$caseStr = implode(',', $cases);

$probe = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_enwik8_wire_slice_probe.php';
$jsonOut = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_wire_slice_probe.json';
$reportPath = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . '.enwik8_beat146_probe.json';

$cmd = sprintf(
	'%s %s --pages=%d --cases=%s',
	$phpPrefix,
	escapeshellarg($probe),
	$pages,
	escapeshellarg($caseStr)
);
fwrite(STDERR, '[beat146] low_memory=' . (bench_low_memory_enabled() ? '1' : '0')
	. ' php_limit=' . bench_low_memory_php_memory_limit() . "\n");
fwrite(STDERR, "[beat146] running: {$cmd}\n");
exec($cmd . ' 2>&1', $lines, $code);
if ($code !== 0) {
	fwrite(STDERR, implode("\n", $lines) . "\n");
	exit(1);
}

$data = json_decode((string) file_get_contents($jsonOut), true);
if (!is_array($data) || !is_array($data['rows'] ?? null)) {
	fwrite(STDERR, "bad probe json\n");
	exit(1);
}

$mono384 = null;
$rows = array();
foreach ($data['rows'] as $row) {
	$id = (string) ($row['label'] ?? '');
	$wire = (int) ($row['fzc_bytes'] ?? 0);
	if ($id === 'split_inner_fztx_mono_mi' && empty($row['error'])) {
		$mono384 = $wire;
	}
	if ($wire <= 0 || !empty($row['error'])) {
		continue;
	}
	$gate = enwik8_beat146_gate_evaluate($wire, $pages, null, ENWIK8_BEAT146_BEST_INTEGRATED, ENWIK8_BEAT146_BASELINE_384P, null, ENWIK8_BEAT146_EXTRAP_MARGIN, ENWIK8_BEAT146_FULL_PAGES, $targetMode, $repo);
	$rows[] = array(
		'case_id' => $id,
		'wire_384p' => $wire,
		'delta_mono' => $mono384 !== null ? $wire - $mono384 : null,
		'gate' => $gate,
		'sec' => (float) ($row['seconds'] ?? 0),
	);
}

usort($rows, static function (array $a, array $b): int {
	return ($a['gate']['extrap_best'] ?? PHP_INT_MAX) <=> ($b['gate']['extrap_best'] ?? PHP_INT_MAX);
});

$wire768 = null;
if ($with768 && $pages === 384) {
	$cmd768 = sprintf(
		'%s %s --pages=768 --cases=%s',
		$phpPrefix,
		escapeshellarg($probe),
		escapeshellarg('split_inner_fztx_mono_mi,split_inner_phda9_xml_single_stream_pp96')
	);
	fwrite(STDERR, "[beat146] 768p scale: {$cmd768}\n");
	exec($cmd768 . ' 2>&1', $l768, $c768);
	if ($c768 === 0) {
		$d768 = json_decode((string) file_get_contents($jsonOut), true);
		foreach ($d768['rows'] ?? array() as $row) {
			if (($row['label'] ?? '') === 'split_inner_phda9_xml_single_stream_pp96' && empty($row['error'])) {
				$wire768 = (int) ($row['fzc_bytes'] ?? 0);
			}
		}
		if ($rows !== array() && $wire768 !== null) {
			$bestWire = (int) $rows[0]['wire_384p'];
			$rows[0]['gate'] = enwik8_beat146_gate_evaluate($bestWire, 384, $wire768, ENWIK8_BEAT146_BEST_INTEGRATED, ENWIK8_BEAT146_BASELINE_384P, null, ENWIK8_BEAT146_EXTRAP_MARGIN, ENWIK8_BEAT146_FULL_PAGES, $targetMode, $repo);
		}
	}
}

$best = $rows[0] ?? null;
$decompBytes = enwik8_hutter_decomp_bytes_measured($repo);
$dictInfo = enwik8_hutter_dict_accounting($repo);
$report = array(
	'generated' => date('c'),
	'pages' => $pages,
	'target_mode' => $targetMode,
	'target_label' => $targetInfo['label'],
	'target_bytes' => $targetInfo['total'],
	'target_hutter_total_l' => ENWIK8_HUTTER_RECORD_TOTAL_L,
	'target_hutter_archive' => ENWIK8_HUTTER_RECORD_ARCHIVE,
	'target_cmixin_bytes' => ENWIK8_BEAT146_CMIX_BYTES,
	'target_hutter_paq_bytes' => ENWIK8_BEAT146_HUTTER_PAQ_BYTES,
	'decomp_bytes_measured' => $decompBytes,
	'dict_bytes_external' => $dictInfo['bytes'],
	'archive_budget_for_target' => $archiveBudget,
	'time_budget_hours_ref' => round(enwik8_hutter_hardware_time_budget_seconds() / 3600, 1),
	'best_integrated_full_bytes' => ENWIK8_BEAT146_BEST_INTEGRATED,
	'mono_mi_384p' => $mono384,
	'wire_768p_best' => $wire768,
	'best_case' => $best,
	'ranked' => $rows,
);
file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

printf("enwik8 probe @%dp target=%s → %s\n\n", $pages, $targetMode, $reportPath);
printf("  gate target        %s (%s B)\n", $targetInfo['label'], number_format($targetInfo['total']));
printf("  decomp (measured)  %s B\n", number_format($decompBytes));
printf("  external dict      %s B\n", number_format($dictInfo['bytes']));
printf("  archive budget     %s B (total S target − decomp − dict)\n", number_format($archiveBudget['archive_budget']));
printf("  time budget/phase  %.1f h @ GB5=%d (compress & decompress each)\n", enwik8_hutter_hardware_time_budget_seconds() / 3600, ENWIK8_HUTTER_GEEKBENCH5_REF_SINGLE);
printf("  refs: beat146 %s | hutter L %s | archive %s\n",
	number_format(ENWIK8_CMIX_ARCHIVE_LAB),
	number_format(ENWIK8_HUTTER_RECORD_TOTAL_L),
	number_format(ENWIK8_HUTTER_RECORD_ARCHIVE)
);
printf("  best integrated    %s B archive (single-stream full, unverified RT)\n\n", number_format(ENWIK8_BEAT146_BEST_INTEGRATED));

foreach (array_slice($rows, 0, 6) as $i => $r) {
	$g = $r['gate'];
	$extrapShow = enwik8_hutter_uses_total_s_accounting($targetMode)
		? (int) ($g['compare_bytes'] ?? $g['extrap_total_s'] ?? 0)
		: (int) ($g['extrap_best'] ?? 0);
	printf("  %d) %-42s wire=%s extrap=%s Δtgt=%s%s gate=%s\n",
		$i + 1,
		$r['case_id'],
		number_format((int) $r['wire_384p']),
		number_format($extrapShow),
		($g['delta_vs_target'] ?? 0) <= 0 ? '' : '+',
		number_format((int) $g['delta_vs_target']),
		!empty($g['allow_full_encode']) ? 'PASS' : 'FAIL'
	);
}

if ($best === null) {
	fwrite(STDERR, "no valid probe rows\n");
	exit(1);
}

$allow = !empty($best['gate']['allow_full_encode']);
printf("\n  best extrap: %s — %s\n", $best['case_id'], $best['gate']['reason'] ?? '');
printf("  full encode: %s\n", $allow ? 'ALLOWED' : 'BLOCKED');

if ($launchEncode && $allow && !$dryRun) {
	$encode = $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'run_enwik8_phda9_xml_encode.php';
	$wire = (int) $best['wire_384p'];
	$encCmd = sprintf(
		'%s %s --encode-only --wire384=%d 2>&1 | tee %s',
		$phpPrefix,
		escapeshellarg($encode),
		$wire,
		escapeshellarg($repo . '/benchmarks/logs/beat146_full_encode.log')
	);
	fwrite(STDERR, "\n[beat146] launching full encode:\n{$encCmd}\n");
	passthru($encCmd, $encCode);
	exit($encCode);
}

if ($launchEncode && !$allow) {
	fwrite(STDERR, "\n[probe] --launch-encode blocked by gate (see docs/HUTTER_PRIZE_COMPLIANCE.md; use --force on encode runner to override)\n");
	exit(2);
}

exit($allow ? 0 : 1);
