<?php

declare(strict_types=1);

/**
 * Shared fz-style improvement gate for cfabb, phda9 dict, and related miners.
 *
 * Matches fractal_zip multipass: benefit/cost > gate_mult × improvement_threshold.
 *
 * Env (same as fractal_zip):
 *   FRACTAL_ZIP_IMPROVEMENT_THRESHOLD — default 0.1
 *   FRACTAL_ZIP_MULTIPASS_GATE_MULT — default 1.28
 */

function fractal_zip_score_improvement_threshold(): float
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_IMPROVEMENT_THRESHOLD');
	if ($env === false || trim((string) $env) === '') {
		$cached = 0.1;
		return $cached;
	}
	$cached = max(0.01, min(50.0, (float) trim((string) $env)));
	return $cached;
}

function fractal_zip_score_gate_multiplier(): float
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$env = getenv('FRACTAL_ZIP_MULTIPASS_GATE_MULT');
	if ($env === false || trim((string) $env) === '') {
		$cached = 1.28;
		return $cached;
	}
	$cached = max(1.0, min(4.0, (float) trim((string) $env)));
	return $cached;
}

/** Minimum benefit/cost ratio (same as fz multipass gate). */
function fractal_zip_score_ratio_threshold(): float
{
	return fractal_zip_score_gate_multiplier() * fractal_zip_score_improvement_threshold();
}

/**
 * Passes when benefit/cost exceeds fz ratio threshold (cost must be > 0).
 */
function fractal_zip_score_passes_ratio_gate(float $benefit, float $cost): bool
{
	if ($benefit <= 0.0 || $cost <= 0.0) {
		return false;
	}
	return ($benefit / $cost) > fractal_zip_score_ratio_threshold();
}

/** Score ratio for ranking (benefit per unit cost). */
function fractal_zip_score_benefit_ratio(float $benefit, float $cost): float
{
	if ($cost <= 0.0) {
		return $benefit > 0.0 ? INF : 0.0;
	}
	return $benefit / $cost;
}

/**
 * Content-scaled patience for greedy score scans: sqrt(pool) × gate_mult × 10.
 * Env FRACTAL_ZIP_CFABB_PHDA9_GREEDY_PATIENCE overrides when set.
 */
function fractal_zip_score_greedy_patience(int $candidateCount): int
{
	$env = getenv('FRACTAL_ZIP_CFABB_PHDA9_GREEDY_PATIENCE');
	if ($env !== false && trim((string) $env) !== '') {
		return max(1, (int) $env);
	}
	return max(8, (int) ceil(sqrt(max(1, $candidateCount)) * fractal_zip_score_gate_multiplier() * 10.0));
}
