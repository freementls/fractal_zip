<?php
declare(strict_types=1);

/**
 * Extrapolation gate: allow full pp96 refresh only when 384p+768p project improvement toward 15M.
 *
 * Used by run_enwik8_beat15m_fast_gate.php and run_enwik8_pp96_refresh.php (--gate-check).
 */

const ENWIK8_FULL_ENCODE_GATE_BEST_FZC = 18848115;
const ENWIK8_FULL_ENCODE_GATE_HUTTER = 15284944;
const ENWIK8_FULL_ENCODE_GATE_MONO_MI_384P = 651650;
const ENWIK8_FULL_ENCODE_GATE_FULL_PAGES = 12041;

/**
 * @param list<array<string, mixed>>|null $wire768Rows
 * @return array{allow_full_encode: bool, reason: string, extrapolated_full_bytes: ?int, delta_vs_best: ?int}
 */
function enwik8_full_encode_gate_evaluate(?int $monoMi384, ?array $wire768Rows = null): array
{
	if ($monoMi384 === null || $monoMi384 <= 0) {
		return array(
			'allow_full_encode' => false,
			'reason' => 'missing 384p mono_mi wire probe',
			'extrapolated_full_bytes' => null,
			'delta_vs_best' => null,
		);
	}
	$delta384 = ENWIK8_FULL_ENCODE_GATE_MONO_MI_384P - $monoMi384;
	$extrap = (int) round(ENWIK8_FULL_ENCODE_GATE_BEST_FZC * ($monoMi384 / ENWIK8_FULL_ENCODE_GATE_MONO_MI_384P));
	$deltaVsBest = ENWIK8_FULL_ENCODE_GATE_BEST_FZC - $extrap;

	$allow = $delta384 > 0 && $extrap < ENWIK8_FULL_ENCODE_GATE_BEST_FZC;
	$reason = $allow
		? '384p beats mono_mi baseline; extrapolated full ' . number_format($extrap) . ' B (−' . number_format($deltaVsBest) . ' vs best)'
		: '384p does not beat mono_mi baseline (' . number_format($monoMi384) . ' vs ' . number_format(ENWIK8_FULL_ENCODE_GATE_MONO_MI_384P) . ')';

	if ($wire768Rows !== null) {
		$mono768 = null;
		foreach ($wire768Rows as $r) {
			if (($r['label'] ?? '') === 'split_inner_fztx_mono_mi' && empty($r['error'])) {
				$mono768 = (int) ($r['fzc_bytes'] ?? 0);
				break;
			}
		}
		if ($mono768 !== null && $mono768 > 0) {
			$perPage384 = $monoMi384 / 384.0;
			$perPage768 = $mono768 / 768.0;
			if ($perPage768 > $perPage384 * 1.002) {
				$allow = false;
				$reason = '768p per-page bytes regress vs 384p (scale check failed)';
			}
		}
	}

	// Final target < 15.28M — full encode only when extrapolation shows meaningful progress.
	if ($extrap >= ENWIK8_FULL_ENCODE_GATE_BEST_FZC - 32768) {
		$allow = false;
		$reason = 'extrapolation margin < 32 KiB vs current best — tune more at 384p/768p first';
	}

	return array(
		'allow_full_encode' => $allow,
		'reason' => $reason,
		'extrapolated_full_bytes' => $extrap,
		'delta_vs_best' => $deltaVsBest,
		'delta_384p_bytes' => $delta384,
		'pages_full' => ENWIK8_FULL_ENCODE_GATE_FULL_PAGES,
	);
}

if (PHP_SAPI === 'cli' && realpath($_SERVER['SCRIPT_FILENAME'] ?? '') === __FILE__) {
	$mono = isset($argv[1]) && ctype_digit($argv[1]) ? (int) $argv[1] : null;
	$r = enwik8_full_encode_gate_evaluate($mono);
	echo json_encode($r, JSON_PRETTY_PRINT) . "\n";
	exit($r['allow_full_encode'] ? 0 : 1);
}
