<?php
declare(strict_types=1);

/**
 * Curve-informed outer prediction (Squash benchmark aggregate behaviour).
 *
 * Squash-style plots show each codec’s compressed size vs quality setting. Slopes differ:
 * — **zlib/deflate**: diminishing returns almost immediately (fast probes ≈ useful prediction of max quality).
 * — **zpaq**: large gains from low methods toward max — a `-method 1` probe understates eventual ratio badly.
 * — **LZMA-class / brotli / xz / zstd**: intermediate curves (meaningful but bounded gains across presets).
 *
 * We do not ship fitted curves per corpus; we use fixed family multipliers (tunable) mapping probe-tier bytes to an
 * **estimated equivalent at max tier** for *ranking only*: `score = raw_probe_bytes × mult(family, tier)`.
 * Lower score ⇒ predicted stronger outer after settings ramp-up.
 *
 * Multipliers are in (0, 1]: estimated high-tier output size relative to observed probe size at that tier.
 *
 * Tuning checklist (future work):
 * — Fit multipliers from paired runs: {@code benchmarks/.last_predict_outer.json} vs {@code benchmarks/.last_bench.json} outer_codec per corpus; minimise mis-rank at L2 + chosen-high vs tournament winner. Default {@code benchmarks/.last_bench.json} is gitignored when generated in-repo; use {@code php benchmarks/run_benchmarks.php --out-json=…} for inputs you keep (see {@code benchmarks/LARGE_CORPUS_SPEED.md}, JSON machine output).
 * — Split “fast→high” by content class (text vs binary) when FRACTAL_ZIP gains corpus tagging.
 * — Runtime fz ({@see fractal_zip_outer_predict_mapped_winner}) currently only reorders zpaq; curve winners 7z/arc could get parallel early lanes later (avoid duplicate full sweeps).
 * — zpaq probe uses fixed methods 1/3/5; align with FRACTAL_ZIP_ZPAQ_OUTER_METHODS sweep ranges where practical.
 */

/**
 * Typical size(high) / size(fast) when ramping from our fast preset to our high preset (qualitative).
 *
 * @var array<string, float>
 */
function predict_outer_curve_fast_to_high_mult_map(): array
{
	static $m = null;
	if ($m !== null) {
		return $m;
	}

	return $m = [
		'gzip' => 0.988,
		'brotli' => 0.91,
		'xz' => 0.87,
		'zstd' => 0.85,
		'7z' => 0.82,
		'arc' => 0.79,
		'bsc' => 0.76,
		// Steep ladder: low-method probes overshoot final size — rank competitively vs zlib-class flats.
		'zpaq' => 0.52,
	];
}

/**
 * Typical size(high) / size(medium) from our medium preset toward high (qualitative).
 *
 * @var array<string, float>
 */
function predict_outer_curve_medium_to_high_mult_map(): array
{
	static $m = null;
	if ($m !== null) {
		return $m;
	}

	return $m = [
		'gzip' => 0.997,
		'brotli' => 0.96,
		'xz' => 0.93,
		'zstd' => 0.92,
		'7z' => 0.90,
		'arc' => 0.88,
		'bsc' => 0.86,
		// Method 3 → 5 still yields visible gains on Squash-class curves.
		'zpaq' => 0.76,
	];
}

function predict_outer_curve_fast_to_high_mult(string $family): float
{
	$map = predict_outer_curve_fast_to_high_mult_map();

	return $map[$family] ?? 1.0;
}

function predict_outer_curve_medium_to_high_mult(string $family): float
{
	$map = predict_outer_curve_medium_to_high_mult_map();

	return $map[$family] ?? 1.0;
}

/**
 * Estimated competitive score toward max quality (lower = better predicted final outer).
 *
 * @param float|null $multPrecomputed pass {@see predict_outer_curve_fast_to_high_mult} / {@see predict_outer_curve_medium_to_high_mult}
 *        when already computed — avoids duplicate map lookups in {@see predict_outer_enrich_rows_curve_scores}.
 */
function predict_outer_curve_adjusted_score(string $family, int $bytes, string $tier, ?float $multPrecomputed = null): float
{
	$m = $multPrecomputed;
	if ($m === null) {
		$m = $tier === 'fast'
			? predict_outer_curve_fast_to_high_mult($family)
			: predict_outer_curve_medium_to_high_mult($family);
	}

	return max(1.0, (float) $bytes) * $m;
}

/**
 * @param list<array{family: string, bytes: int, label: string}> $rows
 *
 * @return list<array{family: string, bytes: int, label: string, curve_adjusted_score: float, curve_mult: float}>
 */
function predict_outer_enrich_rows_curve_scores(array $rows, string $tier): array
{
	$out = [];
	foreach ($rows as $row) {
		$fam = $row['family'];
		$m = $tier === 'fast'
			? predict_outer_curve_fast_to_high_mult($fam)
			: predict_outer_curve_medium_to_high_mult($fam);
		$out[] = array_merge($row, [
			'curve_adjusted_score' => predict_outer_curve_adjusted_score($fam, $row['bytes'], $tier, $m),
			'curve_mult' => $m,
		]);
	}

	return $out;
}

/**
 * @param list<array{family: string, bytes: int, label: string, curve_adjusted_score?: float}> $rows
 *
 * @return list<array{family: string, bytes: int, label: string, curve_adjusted_score?: float}>
 */
function predict_outer_sort_probe_rows_curve_adjusted(array $rows): array
{
	if ($rows === [] || count($rows) === 1) {
		return $rows;
	}
	usort($rows, static function ($a, $b): int {
		$sa = isset($a['curve_adjusted_score']) ? (float) $a['curve_adjusted_score'] : (float) $a['bytes'];
		$sb = isset($b['curve_adjusted_score']) ? (float) $b['curve_adjusted_score'] : (float) $b['bytes'];
		if ($sa !== $sb) {
			return $sa <=> $sb;
		}

		return strcmp($a['family'], $b['family']);
	});

	return $rows;
}
