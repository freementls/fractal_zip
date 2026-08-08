<?php
declare(strict_types=1);

/**
 * Shared helpers for {@see fractal_zip::$folder_bundle_census} in benchmark JSON / tables (display cells, `--repeat` census merge).
 */

/**
 * Format **textish_ratio** (0..1) as a 0–100 integer string for fixed-width cells (no trailing %).
 *
 * @param mixed $census **folder_bundle_census** slice from a case row, or null
 */
function bench_folder_census_textish_pct_cell($census): string
{
	if (!is_array($census) || !array_key_exists('textish_ratio', $census)) {
		return '—';
	}
	$t = (float) $census['textish_ratio'];
	if (!is_finite($t)) {
		return '—';
	}
	$p = (int) round(100.0 * $t);

	return (string) max(0, min(100, $p));
}

/**
 * When benchmarks/run_benchmarks.php folds `--repeat` runs, keep `folder_bundle_census` from the first row if present;
 * otherwise copy the first repeat row that carries a non-null census array.
 *
 * @param list<array<string,mixed>> $rows
 * @param array<string,mixed> $base
 */
function bench_folder_bundle_census_merge_into_aggregated_row(array $rows, array &$base): void {
	if (isset($base['folder_bundle_census']) && is_array($base['folder_bundle_census'])) {
		return;
	}
	foreach ($rows as $r) {
		if (isset($r['folder_bundle_census']) && is_array($r['folder_bundle_census'])) {
			$base['folder_bundle_census'] = $r['folder_bundle_census'];
			return;
		}
	}
}
