<?php
declare(strict_types=1);

/**
 * Runtime hook for layered outer prediction (benchmarks/predict_outer_*).
 *
 * Called from {@see fractal_zip::adaptive_compress} when slow outers may run. Maps predictor families to fz outers
 * (e.g. bsc probe → try zpaq early — fz has no bsc outer).
 *
 * Encode path mirrors benchmark ranking: zpaq / 7z / arc early lanes with duplicate guards ({@code fractal_zip.php}: {@code $ranOuterZpaqPredictLane}, {@code $ranOuter7zPredictSweep}, {@code $ranOuterArcPredictLane}).
 */

/**
 * @return string|null family among gzip zstd brotli xz 7z arc zpaq, or null on failure / disabled inner
 */
function fractal_zip_outer_predict_mapped_winner(string $inner, string $repoRoot): ?string
{
	if ($inner === '') {
		return null;
	}
	$base = $repoRoot . DIRECTORY_SEPARATOR . 'benchmarks';
	require_once $base . DIRECTORY_SEPARATOR . 'predict_outer_heuristic.php';
	require_once $base . DIRECTORY_SEPARATOR . 'predict_outer_curve_factors.php';
	require_once $base . DIRECTORY_SEPARATOR . 'predict_outer_layered_probe.php';

	$bscExe = predict_outer_resolve_bsc_exe();
	$probeMax = fractal_zip::outer_prediction_probe_max_bytes();
	$tout = fractal_zip::outer_prediction_timeout_sec();
	$n = strlen($inner);
	// One slice here matches {@see predict_outer_rank_outer_candidates} cap; avoids passing megabyte payloads twice.
	$innerProbe = ($n <= $probeMax) ? $inner : substr($inner, 0, $probeMax);

	$rank = predict_outer_rank_outer_candidates(
		$innerProbe,
		$probeMax,
		fractal_zip::zpaq_executable(),
		fractal_zip::brotli_executable(),
		fractal_zip::xz_executable(),
		fractal_zip::zstd_executable(),
		$bscExe,
		$tout,
		$repoRoot,
		fractal_zip::seven_zip_executable(),
		fractal_zip::freearc_executable()
	);
	$w = isset($rank['layered']['winner_family']) ? (string) $rank['layered']['winner_family'] : '';
	if ($w === '') {
		return null;
	}
	if ($w === 'bsc') {
		return 'zpaq';
	}

	return $w;
}
