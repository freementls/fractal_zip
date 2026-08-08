#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Audit enwik8 encode result against Hutter Prize rules (hrules.htm).
 *
 * Usage:
 *   php benchmarks/run_enwik8_hutter_compliance.php
 *   php benchmarks/run_enwik8_hutter_compliance.php --target=beat146_hardware
 *   php benchmarks/run_enwik8_hutter_compliance.php --target=ltcb_rank
 *   php benchmarks/run_enwik8_hutter_compliance.php --wire=15285304
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_hutter_prize.php';

$jsonPath = $repo . '/benchmarks/.enwik8_exp_phda9_xml.json';
$targetMode = ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE;
$wireOverride = null;
foreach ($argv as $arg) {
	if (str_starts_with($arg, '--json=')) {
		$jsonPath = substr($arg, 7);
	} elseif (str_starts_with($arg, '--target=')) {
		$t = substr($arg, 9);
		$targetMode = match ($t) {
			'beat146', 'beat146_hardware', 'default' => ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
			'hutter', 'hutter_total' => ENWIK8_HUTTER_TARGET_HUTTER_TOTAL,
			'ltcb', 'ltcb_rank' => ENWIK8_HUTTER_TARGET_LTCB_RANK,
			'cmix', 'cmix_archive' => ENWIK8_HUTTER_TARGET_CMIX_LAB,
			default => $t,
		};
	} elseif (str_starts_with($arg, '--wire=')) {
		$wireOverride = (int) substr($arg, 7);
	}
}

$archiveBytes = $wireOverride ?? 0;
$verifyOk = null;
$wallSeconds = null;
$decompressSeconds = null;
if ($wireOverride === null) {
	if (!is_file($jsonPath)) {
		fwrite(STDERR, "Missing {$jsonPath} (use --wire=)\n");
		exit(1);
	}
	$j = json_decode((string) file_get_contents($jsonPath), true);
	$case = is_array($j) ? ($j['cases'][0] ?? array()) : array();
	$archiveBytes = (int) ($case['fzc_bytes'] ?? 0);
	$wallSeconds = isset($case['zip_seconds']) ? (float) $case['zip_seconds'] : null;
	if (isset($case['decompress_seconds'])) {
		$decompressSeconds = (float) $case['decompress_seconds'];
	}
	if (array_key_exists('verify_ok', $case)) {
		$verifyOk = $case['verify_ok'] === true || $case['verify_ok'] === 1;
	}
}

$acctMode = enwik8_hutter_accounting_mode_for_target($targetMode);
$audit = enwik8_hutter_compliance_audit($archiveBytes, $verifyOk, $targetMode, $repo, array(
	'compress_seconds' => $wallSeconds,
	'decompress_seconds' => $decompressSeconds,
	'single_core' => true,
));
$sub = $audit['submission'];
$acct = $sub['accounting'] ?? array();
$target = $audit['target'];
$budget = enwik8_hutter_archive_budget_for_total_s($target['total'], $repo, null, $acctMode);

printf("Hutter Prize compliance (%s)\n", $target['label']);
printf("  rules: http://prize.hutter1.net/hrules.htm\n");
printf("  LTCB:  https://mattmahoney.net/dc/text.html (size ranks; time separate)\n");
printf("  S formula: %s (%s)\n\n", $audit['accounting_formula'] ?? '?', $acctMode);

printf("  archive            %s B\n", number_format($sub['archive_bytes']));
printf("  compressor         %s B\n", number_format($sub['comp_bytes'] ?? 0));
printf("  decompressor       %s B × %d\n",
	number_format($sub['decomp_bytes']),
	(int) ($acct['decomp_multiplier'] ?? 1)
);
printf("  external dict      %s B\n", number_format($sub['dict_bytes']));
printf("  total S            %s B\n", number_format($sub['total_s']));
printf("  target L           %s B\n", number_format($audit['target_bytes']));
printf("  Δ vs L             %s%s B\n",
	$audit['delta_vs_target'] <= 0 ? '' : '+',
	number_format($audit['delta_vs_target'])
);
printf("  archive budget     %s B\n", number_format($budget['archive_budget']));
$hw = $audit['hardware'] ?? array();
printf("  time budget/phase  %.1f h @ GB5=%d (compress AND decompress each)\n",
	($hw['time_budget_hours'] ?? 0),
	ENWIK8_HUTTER_GEEKBENCH5_REF_SINGLE
);
if ($wallSeconds !== null) {
	$compNs = enwik8_hutter_seconds_to_ns_per_byte($wallSeconds);
	$compEx = enwik8_hutter_extrapolate_corpus_hours($wallSeconds);
	printf("  compress (lab)     %.1f h  %s ns/B enwik8  → ~%.1f h enwik9 extrap %s\n",
		$wallSeconds / 3600,
		number_format($compNs),
		$compEx['enwik9_hours'],
		$compEx['within_budget'] ? 'ok' : 'OVER'
	);
}
if ($decompressSeconds !== null) {
	$decNs = enwik8_hutter_seconds_to_ns_per_byte($decompressSeconds);
	$decEx = enwik8_hutter_extrapolate_corpus_hours($decompressSeconds);
	printf("  decompress (lab)   %.1f h  %s ns/B enwik8  → ~%.1f h enwik9 extrap %s\n",
		$decompressSeconds / 3600,
		number_format($decNs),
		$decEx['enwik9_hours'],
		$decEx['within_budget'] ? 'ok' : 'OVER'
	);
} else {
	echo "  decompress (lab)   ? (measure with diagnose_phda9_fzpa_rt.php)\n";
}
printf("  fz asymmetry       compress >> decompress (PAQ/phda9: comp ≈ decomp)\n");
printf("  verify / lossless  %s / %s\n",
	$verifyOk === null ? '?' : ($verifyOk ? 'ok' : 'fail'),
	$audit['lossless'] ? 'yes' : 'no'
);
printf("  award min (~1 MiB) %s\n", !empty($audit['award_eligible_improvement']) ? 'met' : 'not met');
printf("  prize compliant    %s\n\n", $audit['compliant'] ? 'YES' : 'NO');

if ($audit['issues'] !== array()) {
	echo "  issues:\n";
	foreach ($audit['issues'] as $issue) {
		echo "    - {$issue}\n";
	}
	echo "\n";
}

echo "  submission checklist (hrules):\n";
foreach ($audit['submission_checklist'] ?? array() as $row) {
	printf("    [%s] %s — %s\n", !empty($row['ok']) ? 'ok' : '…', $row['id'], $row['lab_status']);
}

echo "\n  LTCB leaders vs Hutter time (why table #1 ≠ prize money):\n";
foreach (enwik8_ltcb_reference_rows() as $ref) {
	$size = isset($ref['enwik8_archive']) && $ref['enwik8_archive'] !== null
		? number_format((int) $ref['enwik8_archive']) . ' B'
		: 'enwik9 SFX';
	$time = isset($ref['enwik9_comp_h'])
		? sprintf('enwik9 ~%.0f/%.0f h comp/decomp', $ref['enwik9_comp_h'], $ref['enwik9_decomp_h'])
		: ($ref['decomp_ns_b'] !== null ? sprintf('decomp %s ns/B', number_format((int) $ref['decomp_ns_b'])) : '—');
	printf("    %-14s  %-12s  %s  %s\n",
		$ref['program'],
		$size,
		$time,
		$ref['hutter_enwik9']
	);
}

echo "\n  see docs/HUTTER_PRIZE_COMPLIANCE.md\n";
exit($audit['compliant'] ? 0 : 1);
