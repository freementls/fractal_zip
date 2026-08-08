#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Hutter prep scorecard: probe @384p, gate, compliance, verify status, byte ceiling refs.
 *
 * Usage: php benchmarks/run_enwik8_hutter_scorecard.php
 */

$repo = dirname(__DIR__);
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_beat146_gate.php';
require_once $repo . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'enwik8_hutter_prize.php';

$probe384 = $repo . '/benchmarks/.enwik8_wire_slice_probe_384p_phda9.json';
$encodeJson = $repo . '/benchmarks/.enwik8_exp_phda9_xml.json';
$squashJson = $repo . '/benchmarks/.enwik8_paq_squash.json';
$outJson = $repo . '/benchmarks/.enwik8_hutter_scorecard.json';

$wireBudget = enwik8_beat146_wire_budget_384p_safe($repo);
$scorecard = array(
	'generated' => date('c'),
	'target_mode' => ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
	'wire_budget_384p' => $wireBudget,
	'probe_384p' => null,
	'encode_full' => null,
	'raw_squash_full' => null,
	'compliance' => null,
	'verify_running' => is_verify_running($repo),
);

if (is_file($probe384)) {
	$p = json_decode((string) file_get_contents($probe384), true);
	$rows = is_array($p) ? ($p['rows'] ?? array()) : array();
	$probeRows = array();
	foreach ($rows as $row) {
		if (!empty($row['error']) || (int) ($row['fzc_bytes'] ?? 0) <= 0) {
			continue;
		}
		$wire = (int) $row['fzc_bytes'];
		$gate = enwik8_beat146_gate_evaluate($wire, 384);
		$probeRows[] = array(
			'label' => $row['label'] ?? '?',
			'wire_384p' => $wire,
			'roundtrip_ok' => $row['roundtrip_ok'] ?? null,
			'zip_seconds' => $row['zip_seconds'] ?? null,
			'extract_seconds' => $row['extract_seconds'] ?? null,
			'gate_allow' => !empty($gate['allow_full_encode']),
			'extrap_total_s' => (int) ($gate['extrap_total_s'] ?? 0),
			'delta_vs_target' => (int) ($gate['delta_vs_target'] ?? 0),
			'wire_gap_vs_budget' => $wire - (int) ($wireBudget['wire_budget_384p'] ?? 0),
		);
	}
	usort($probeRows, static fn (array $a, array $b): int => ($a['extrap_total_s'] ?? PHP_INT_MAX) <=> ($b['extrap_total_s'] ?? PHP_INT_MAX));
	$scorecard['probe_384p'] = array(
		'path' => $probe384,
		'partial' => !empty($p['partial']),
		'rows' => $probeRows,
		'best' => $probeRows[0] ?? null,
	);
}

if (is_file($encodeJson)) {
	$e = json_decode((string) file_get_contents($encodeJson), true);
	$c = is_array($e) ? ($e['cases'][0] ?? array()) : array();
	$archive = (int) ($c['fzc_bytes'] ?? 0);
	$verifyOk = $c['verify_ok'] ?? null;
	$decompSec = isset($c['decompress_seconds']) ? (float) $c['decompress_seconds'] : null;
	$gateFull = null;
	if ($archive > 0) {
		$sub = enwik8_hutter_submission_total($archive, $repo);
		$compare = (int) $sub['total_s'];
		$target = ENWIK8_CMIX_ARCHIVE_LAB;
		$gateFull = array(
			'extrap_total_s' => $compare,
			'delta_vs_target' => $compare - $target,
			'allow_full_encode' => $compare <= ($target - ENWIK8_BEAT146_EXTRAP_MARGIN),
		);
	}
	$compressExtrap = isset($c['zip_seconds']) ? enwik8_hutter_extrapolate_corpus_hours((float) $c['zip_seconds']) : null;
	$decompExtrap = $decompSec !== null ? enwik8_hutter_extrapolate_corpus_hours($decompSec) : null;
	$scorecard['encode_full'] = array(
		'archive_bytes' => $archive,
		'verify_ok' => $verifyOk,
		'compress_seconds' => $c['zip_seconds'] ?? null,
		'decompress_seconds' => $decompSec,
		'compress_enwik9_h' => $compressExtrap['enwik9_hours'] ?? null,
		'decompress_enwik9_h' => $decompExtrap['enwik9_hours'] ?? null,
		'split_total_s' => $archive > 0 ? enwik8_hutter_submission_total($archive, $repo)['total_s'] : null,
		'gate' => $gateFull,
	);
}

if (is_file($squashJson)) {
	$s = json_decode((string) file_get_contents($squashJson), true);
	$scorecard['raw_squash_full'] = array(
		'archive_bytes' => (int) ($s['bytes'] ?? 0),
		'tool' => $s['tool'] ?? null,
		'note' => 'raw byte-order phda9; dual-order needs .enwik8_paq_squash.fzpq + FZEP integration',
		'vs_hutter_archive' => (int) ($s['bytes'] ?? 0) - ENWIK8_HUTTER_RECORD_ARCHIVE,
	);
}

$archiveForAudit = (int) ($scorecard['encode_full']['archive_bytes'] ?? ENWIK8_BEAT146_BEST_INTEGRATED);
$verifyBool = $scorecard['encode_full']['verify_ok'] ?? null;
$audit = enwik8_hutter_compliance_audit(
	$archiveForAudit,
	$verifyBool === true ? true : ($verifyBool === false ? false : null),
	ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
	$repo,
	array(
		'compress_seconds' => isset($scorecard['encode_full']['compress_seconds']) ? (float) $scorecard['encode_full']['compress_seconds'] : null,
		'decompress_seconds' => $scorecard['encode_full']['decompress_seconds'] ?? null,
		'single_core' => true,
	)
);
$scorecard['compliance'] = array(
	'compliant' => $audit['compliant'],
	'issue_count' => count($audit['issues'] ?? array()),
	'top_issues' => array_slice($audit['issues'] ?? array(), 0, 6),
);

file_put_contents($outJson, json_encode($scorecard, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

printf("Hutter scorecard → %s\n\n", $outJson);
if ($scorecard['probe_384p']['best'] ?? null) {
	$b = $scorecard['probe_384p']['best'];
	printf("  @384p best: %s wire=%s RT=%s gate=%s Δtotal=%s%s wire_gap=%s%s\n",
		$b['label'],
		number_format($b['wire_384p']),
		$b['roundtrip_ok'] === true ? 'ok' : ($b['roundtrip_ok'] === false ? 'FAIL' : '?'),
		!empty($b['gate_allow']) ? 'PASS' : 'FAIL',
		($b['delta_vs_target'] ?? 0) <= 0 ? '' : '+',
		number_format($b['delta_vs_target'] ?? 0),
		($b['wire_gap_vs_budget'] ?? 0) <= 0 ? '' : '+',
		number_format($b['wire_gap_vs_budget'] ?? 0)
	);
}
if ($scorecard['encode_full'] ?? null) {
	$ef = $scorecard['encode_full'];
	printf("  full archive %s B verify=%s decompress=%s\n",
		number_format((int) ($ef['archive_bytes'] ?? 0)),
		$ef['verify_ok'] === null ? 'running/pending' : ($ef['verify_ok'] ? 'ok' : 'FAIL'),
		$ef['decompress_seconds'] !== null ? round((float) $ef['decompress_seconds']) . 's' : '?'
	);
}
if ($scorecard['raw_squash_full'] ?? null) {
	$rs = $scorecard['raw_squash_full'];
	printf("  raw squash ceiling %s B (%s%s vs Hutter archive)\n",
		number_format((int) $rs['archive_bytes']),
		($rs['vs_hutter_archive'] ?? 0) <= 0 ? '' : '+',
		number_format($rs['vs_hutter_archive'] ?? 0)
	);
}
printf("  wire budget @384p %s B | verify_running=%s\n",
	number_format($wireBudget['wire_budget_384p']),
	$scorecard['verify_running'] ? 'yes' : 'no'
);
exit(0);

function enwik8_beat146_wire_budget_384p_safe(string $repo): array
{
	return enwik8_beat146_wire_budget_384p(ENWIK8_BEAT146_EXTRAP_MARGIN, ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE, $repo);
}

function is_verify_running(string $repo): bool
{
	$lock = $repo . '/benchmarks/logs/.lock_enwik8_verify.pid';
	if (!is_file($lock)) {
		return (bool) pgrep('run_enwik8_verify_lowprio');
	}
	$fh = @fopen($lock, 'r');
	if ($fh === false) {
		return false;
	}
	$running = flock($fh, LOCK_EX | LOCK_NB) === false;
	fclose($fh);
	return $running;
}
