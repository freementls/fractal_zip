#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Compare latest phda9_xml full encode JSON vs Hutter Prize targets.
 *
 * Usage: php benchmarks/summarize_phda9_encode_result.php
 *
 * @see docs/HUTTER_PRIZE_COMPLIANCE.md
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_hutter_prize.php';

$exp = $repo . '/benchmarks/.enwik8_exp_phda9_xml.json';
if (!is_file($exp)) {
	fwrite(STDERR, "Missing {$exp}\n");
	exit(1);
}
$j = json_decode((string) file_get_contents($exp), true);
$case = $j['cases'][0] ?? array();
$fzc = (int) ($case['fzc_bytes'] ?? 0);
$raw = (int) ($case['raw_bytes'] ?? 0);
$verifyOk = $case['verify_ok'] ?? null;
$verifyBool = $verifyOk === true || $verifyOk === 1;

$gate = 15157164;
$parallel = 18848115;
$mono = 19207083;
$submission = enwik8_hutter_submission_total($fzc, $repo);
$budget = enwik8_hutter_archive_budget_for_total_s(ENWIK8_CMIX_ARCHIVE_LAB, $repo);
$zipSec = isset($case['zip_seconds']) ? (float) $case['zip_seconds'] : null;
$audit = enwik8_hutter_compliance_audit($fzc, $verifyBool ? true : ($verifyOk === false ? false : null), ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE, $repo, array(
	'compress_seconds' => $zipSec,
	'decompress_seconds' => isset($case['decompress_seconds']) ? (float) $case['decompress_seconds'] : null,
	'single_core' => true,
));

printf("phda9_xml single-stream full encode summary\n\n");
printf("  fzc_bytes=%s  raw=%s  zip_s=%s  verify_ok=%s\n",
	number_format($fzc),
	number_format($raw),
	(string) ($case['zip_seconds'] ?? '?'),
	(string) ($verifyOk ?? '?')
);
printf("  total S (prize)    %s B  (decomp %s + dict %s)\n",
	number_format($submission['total_s']),
	number_format($submission['decomp_bytes']),
	number_format($submission['dict_bytes'])
);
printf("  archive budget     %s B (beat 14.6 MiB total S)\n", number_format($budget['archive_budget']));
printf("  vs gate extrap 15,157,164: %s%s B (archive only)\n", ($fzc - $gate) <= 0 ? '' : '+', number_format($fzc - $gate));
printf("  vs parallel phda9 18,848,115: %s%s B\n", ($fzc - $parallel) <= 0 ? '' : '+', number_format($fzc - $parallel));
printf("  vs mono_mi 19,207,083: %s%s B\n", ($fzc - $mono) <= 0 ? '' : '+', number_format($fzc - $mono));
printf("  vs Hutter archive 15,242,496: %s%s B\n", ($fzc - ENWIK8_HUTTER_RECORD_ARCHIVE) <= 0 ? '' : '+', number_format($fzc - ENWIK8_HUTTER_RECORD_ARCHIVE));
printf("  vs Hutter total L 15,284,944: %s%s B\n", ($submission['total_s'] - ENWIK8_HUTTER_RECORD_TOTAL_L) <= 0 ? '' : '+', number_format($submission['total_s'] - ENWIK8_HUTTER_RECORD_TOTAL_L));
printf("  vs paq squash archive 15,010,414: %s%s B\n", ($fzc - ENWIK8_HUTTER_PAQ_SQUASH_ARCHIVE) <= 0 ? '' : '+', number_format($fzc - ENWIK8_HUTTER_PAQ_SQUASH_ARCHIVE));
printf("  vs beat146 total 14,623,723: %s%s B\n", ($submission['total_s'] - ENWIK8_CMIX_ARCHIVE_LAB) <= 0 ? '' : '+', number_format($submission['total_s'] - ENWIK8_CMIX_ARCHIVE_LAB));
if ($zipSec !== null) {
	$extrap = enwik8_hutter_extrapolate_corpus_hours($zipSec);
	printf("  compress enwik8     %.1f h  → enwik9 extrap ~%.1f h (%s @ GB5=%d)\n",
		$zipSec / 3600,
		$extrap['enwik9_hours'],
		$extrap['within_budget'] ? 'within budget' : 'OVER budget',
		ENWIK8_HUTTER_GEEKBENCH5_REF_SINGLE
	);
}
printf("\n  beat146 total S:    %s\n", $audit['beats_record'] ? 'PASS (record!)' : 'no');
printf("  Hutter archive:     %s\n", ($fzc > 0 && $fzc <= ENWIK8_HUTTER_RECORD_ARCHIVE && $verifyBool) ? 'PASS' : 'no');
if ($audit['issues'] !== array()) {
	printf("\n  compliance issues:\n");
	foreach ($audit['issues'] as $issue) {
		printf("    - %s\n", $issue);
	}
}
exit(0);
