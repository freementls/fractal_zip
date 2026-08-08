<?php
declare(strict_types=1);

/**
 * Extrapolation gate toward enwik8 targets (Hutter prize total by default).
 *
 * Δ = extrapolated compare_bytes − target (negative = projected WIN).
 * Full encode only when compare_bytes <= target − margin.
 *
 * @see benchmarks/enwik8_hutter_prize.php
 * @see docs/HUTTER_PRIZE_COMPLIANCE.md
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'enwik8_hutter_prize.php';

const ENWIK8_BEAT146_CMIX_BYTES = ENWIK8_CMIX_ARCHIVE_LAB;
const ENWIK8_BEAT146_HUTTER_PAQ_BYTES = ENWIK8_HUTTER_PAQ_SQUASH_ARCHIVE;
const ENWIK8_BEAT146_HUTTER_TOTAL = ENWIK8_HUTTER_RECORD_TOTAL_L;
const ENWIK8_BEAT146_BEST_INTEGRATED = 15285304;
const ENWIK8_BEAT146_BASELINE_384P = 524040;
const ENWIK8_BEAT146_MONO_MI_384P = 651650;
const ENWIK8_BEAT146_FULL_PAGES = 12347;
const ENWIK8_BEAT146_EXTRAP_MARGIN = 32768;

/**
 * @return array{
 *   allow_full_encode: bool,
 *   reason: string,
 *   target_mode: string,
 *   target_bytes: int,
 *   target_label: string,
 *   wire_384p: int,
 *   extrap_archive: int,
 *   extrap_total_s: int,
 *   compare_bytes: int,
 *   decomp_bytes: int,
 *   dict_bytes: int,
 *   extrap_scale_best: int,
 *   extrap_linear_pages: int,
 *   extrap_scale_mono: int,
 *   extrap_best: int,
 *   delta_vs_target: int,
 *   delta_vs_integrated: int,
 *   margin_bytes: int
 * }
 */
function enwik8_beat146_gate_evaluate(
	int $wire384,
	int $pages384 = 384,
	?int $wire768 = null,
	int $anchorFull = ENWIK8_BEAT146_BEST_INTEGRATED,
	int $anchor384 = ENWIK8_BEAT146_BASELINE_384P,
	?int $target = null,
	int $margin = ENWIK8_BEAT146_EXTRAP_MARGIN,
	int $fullPages = ENWIK8_BEAT146_FULL_PAGES,
	string $targetMode = ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
	?string $repoRoot = null
): array {
	$targetInfo = enwik8_hutter_target_for_mode($targetMode);
	$targetBytes = $target ?? $targetInfo['total'];
	$empty = static function (string $reason) use ($targetBytes, $wire384, $margin, $targetMode, $targetInfo): array {
		return array(
			'allow_full_encode' => false,
			'reason' => $reason,
			'target_mode' => $targetMode,
			'target_bytes' => $targetBytes,
			'target_label' => $targetInfo['label'],
			'wire_384p' => $wire384,
			'extrap_archive' => 0,
			'extrap_total_s' => 0,
			'compare_bytes' => 0,
			'decomp_bytes' => 0,
			'dict_bytes' => 0,
			'extrap_scale_best' => 0,
			'extrap_linear_pages' => 0,
			'extrap_scale_mono' => 0,
			'extrap_best' => 0,
			'delta_vs_target' => PHP_INT_MAX,
			'delta_vs_integrated' => PHP_INT_MAX,
			'margin_bytes' => $margin,
		);
	};

	if ($wire384 <= 0) {
		return $empty('missing 384p wire bytes');
	}

	$extrapScale = (int) round($anchorFull * ($wire384 / max(1, $anchor384)));
	$extrapLinear = (int) round($wire384 * ($fullPages / max(1, $pages384)));
	$extrapMono = (int) round(19207083 * ($wire384 / ENWIK8_BEAT146_MONO_MI_384P));
	$extrapArchive = min($extrapScale, $extrapLinear, $extrapMono);
	$submission = enwik8_hutter_submission_total(
		$extrapArchive,
		$repoRoot,
		null,
		true,
		enwik8_hutter_accounting_mode_for_target($targetMode)
	);
	$compareBytes = enwik8_hutter_uses_total_s_accounting($targetMode)
		? $submission['total_s']
		: $extrapArchive;
	$deltaTarget = $compareBytes - $targetBytes;
	$deltaIntegrated = $extrapArchive - $anchorFull;
	$allow = $compareBytes <= ($targetBytes - $margin);

	$metric = enwik8_hutter_uses_total_s_accounting($targetMode) ? 'total S' : 'archive wire';
	$reason = $allow
		? sprintf(
			'extrap %s %s B ≤ target−margin %s B (Δ %s%s B)',
			$metric,
			number_format($compareBytes),
			number_format($targetBytes - $margin),
			$deltaTarget <= 0 ? '' : '+',
			number_format($deltaTarget)
		)
		: sprintf(
			'extrap %s %s B > target−margin %s B (need %s%s B more slice win)',
			$metric,
			number_format($compareBytes),
			number_format($targetBytes - $margin),
			$deltaTarget <= 0 ? '' : '+',
			number_format(max(0, $compareBytes - ($targetBytes - $margin)))
		);

	if ($wire768 !== null && $wire768 > 0) {
		$per384 = $wire384 / 384.0;
		$per768 = $wire768 / 768.0;
		if ($per768 > $per384 * 1.002) {
			$allow = false;
			$reason = '768p per-page regresses vs 384p (anti-scale)';
		}
	}

	return array(
		'allow_full_encode' => $allow,
		'reason' => $reason,
		'target_mode' => $targetMode,
		'target_bytes' => $targetBytes,
		'target_label' => $targetInfo['label'],
		'wire_384p' => $wire384,
		'extrap_archive' => $extrapArchive,
		'extrap_total_s' => $submission['total_s'],
		'compare_bytes' => $compareBytes,
		'decomp_bytes' => $submission['decomp_bytes'],
		'dict_bytes' => $submission['dict_bytes'],
		'extrap_scale_best' => $extrapScale,
		'extrap_linear_pages' => $extrapLinear,
		'extrap_scale_mono' => $extrapMono,
		'extrap_best' => $extrapArchive,
		'delta_vs_target' => $deltaTarget,
		'delta_vs_integrated' => $deltaIntegrated,
		'margin_bytes' => $margin,
	);
}

/**
 * Max @384p wire bytes so extrapolated split-mode total S ≤ target − margin.
 *
 * @return array{wire_budget_384p: int, archive_budget: int, split_tax: int, target_minus_margin: int}
 */
function enwik8_beat146_wire_budget_384p(
	int $margin = ENWIK8_BEAT146_EXTRAP_MARGIN,
	string $targetMode = ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
	?string $repoRoot = null
): array {
	$targetInfo = enwik8_hutter_target_for_mode($targetMode);
	$targetBytes = $targetInfo['total'];
	$submission = enwik8_hutter_submission_total(0, $repoRoot, null, true, enwik8_hutter_accounting_mode_for_target($targetMode));
	$splitTax = (int) $submission['total_s'];
	$targetMinusMargin = $targetBytes - $margin;
	$archiveBudget = $targetMinusMargin - $splitTax;
	$anchorWire = ENWIK8_BEAT146_BASELINE_384P;
	$anchorFull = ENWIK8_BEAT146_BEST_INTEGRATED;
	$wireFromScale = $anchorWire > 0 && $anchorFull > 0
		? (int) floor($archiveBudget * $anchorWire / $anchorFull)
		: 0;
	$wireFromMono = ENWIK8_BEAT146_MONO_MI_384P > 0
		? (int) floor($archiveBudget * ENWIK8_BEAT146_MONO_MI_384P / 19207083)
		: 0;
	$fullPages = ENWIK8_BEAT146_FULL_PAGES;
	$wireFromLinear = $fullPages > 0
		? (int) floor($archiveBudget * 384 / $fullPages)
		: 0;
	// At phda9 single-stream wires, extrap_archive is scale-anchor-bound (see 524040 gate).
	$wireBudget = $wireFromScale > 0 ? $wireFromScale : max(0, min($wireFromMono, $wireFromLinear));
	return array(
		'wire_budget_384p' => max(0, $wireBudget),
		'wire_budget_scale' => max(0, $wireFromScale),
		'wire_budget_linear' => max(0, $wireFromLinear),
		'archive_budget' => max(0, $archiveBudget),
		'split_tax' => $splitTax,
		'target_minus_margin' => $targetMinusMargin,
	);
}

/**
 * Hutter-prize gate: same as beat146 with official total S target and optional verify requirement.
 *
 * @return array<string, mixed>
 */
function enwik8_hutter_gate_evaluate(
	int $wire384,
	int $pages384 = 384,
	?int $wire768 = null,
	string $targetMode = ENWIK8_HUTTER_TARGET_BEAT146_HARDWARE,
	?bool $verifyOk = null,
	?string $repoRoot = null
): array {
	$gate = enwik8_beat146_gate_evaluate(
		$wire384,
		$pages384,
		$wire768,
		ENWIK8_BEAT146_BEST_INTEGRATED,
		ENWIK8_BEAT146_BASELINE_384P,
		null,
		ENWIK8_BEAT146_EXTRAP_MARGIN,
		ENWIK8_BEAT146_FULL_PAGES,
		$targetMode,
		$repoRoot
	);
	if ($verifyOk === false) {
		$gate['allow_full_encode'] = false;
		$gate['reason'] = 'lossless verify failed — prize rules require byte-identical enwik8';
	}
	$gate['verify_ok'] = $verifyOk;
	$gate['hutter_compliant_launch'] = $gate['allow_full_encode'] && $verifyOk !== false;
	return $gate;
}
