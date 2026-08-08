<?php
declare(strict_types=1);

/**
 * Progressive ablation ledger (GAIA-inspired): promote arms only after staged gates
 * with soft closeness beside binary verify_ok. Soft metrics never replace SHA for
 * lossless claims — they only rank / block tracker paste.
 */

/**
 * @param array<string, mixed> $row
 * @return array{
 *   arm: string,
 *   stage: string,
 *   fzc_bytes: int|null,
 *   baseline_best: int|null,
 *   verify_ok: bool|null,
 *   closeness: array{sha_mismatch_frac: float|null, legibility_delta: float|null, semantic_ok: bool|null},
 *   promote: bool,
 *   promote_reasons: list<string>,
 *   block_reasons: list<string>
 * }
 */
function fractal_zip_ablation_evaluate_row(array $row, string $stage = 'full'): array
{
	$arm = (string) ($row['arm'] ?? $row['label'] ?? $row['corpus'] ?? 'unknown');
	$fzc = isset($row['fzc_bytes']) ? (int) $row['fzc_bytes'] : null;
	$baseline = fractal_zip_ablation_baseline_best($row);
	$verify = array_key_exists('verify_ok', $row) ? $row['verify_ok'] : null;
	if ($verify !== null && !is_bool($verify)) {
		$verify = filter_var($verify, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	}

	$mismatchFiles = isset($row['verify_mismatch_files']) ? (int) $row['verify_mismatch_files'] : null;
	$fileCount = isset($row['verify_file_count']) ? max(1, (int) $row['verify_file_count']) : null;
	$shaFrac = null;
	if ($mismatchFiles !== null && $fileCount !== null) {
		$shaFrac = $mismatchFiles / $fileCount;
	} elseif ($verify === false) {
		$shaFrac = 1.0;
	} elseif ($verify === true) {
		$shaFrac = 0.0;
	}

	$legibility = isset($row['legibility_delta']) ? (float) $row['legibility_delta'] : null;
	$semantic = array_key_exists('semantic_ok', $row) ? $row['semantic_ok'] : null;
	if ($semantic !== null && !is_bool($semantic)) {
		$semantic = filter_var($semantic, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
	}

	$closeness = array(
		'sha_mismatch_frac' => $shaFrac,
		'legibility_delta' => $legibility,
		'semantic_ok' => $semantic,
	);

	$promoteReasons = array();
	$blockReasons = array();

	$bytesWin = ($fzc !== null && $baseline !== null && $fzc <= $baseline);
	if ($bytesWin) {
		$promoteReasons[] = 'bytes_win';
	} else {
		$blockReasons[] = 'no_bytes_win';
	}

	if ($verify === true) {
		$promoteReasons[] = 'verify_ok';
	} elseif ($verify === false) {
		// Soft path: allow promote only when semantic_ok and sha mismatch is bounded.
		if ($semantic === true && ($shaFrac === null || $shaFrac <= 0.05)) {
			$promoteReasons[] = 'semantic_ok_soft';
		} else {
			$blockReasons[] = 'verify_failed_without_soft_closeness';
		}
	} else {
		$blockReasons[] = 'verify_skipped';
	}

	if ($stage === 'sample5' || $stage === 'medium') {
		// Staging rows may promote to next stage without tracker paste.
		$promote = $bytesWin && ($verify === true || ($semantic === true && ($shaFrac === null || $shaFrac <= 0.05)));
	} else {
		$promote = $bytesWin && empty($blockReasons);
	}

	return array(
		'arm' => $arm,
		'stage' => $stage,
		'fzc_bytes' => $fzc,
		'baseline_best' => $baseline,
		'verify_ok' => $verify,
		'closeness' => $closeness,
		'promote' => $promote,
		'promote_reasons' => $promoteReasons,
		'block_reasons' => $blockReasons,
	);
}

/** @param array<string, mixed> $row */
function fractal_zip_ablation_baseline_best(array $row): ?int
{
	$cands = array();
	foreach (array('gzip9_bundle_bytes', 'gzip_bytes', 'seven_zip_folder_bytes', 'best_ext_folder_bytes', 'best_ext_bytes', 'baseline_best') as $k) {
		if (isset($row[$k]) && is_numeric($row[$k]) && (int) $row[$k] > 0) {
			$cands[] = (int) $row[$k];
		}
	}
	return $cands === array() ? null : min($cands);
}

/**
 * @param list<array<string, mixed>> $rows
 * @return array{evaluated: list<array<string,mixed>>, promote_count: int, block_count: int, soft_rescues: int, hard_blocks: int}
 */
function fractal_zip_ablation_evaluate_rows(array $rows, string $defaultStage = 'full'): array
{
	$evaluated = array();
	$promote = 0;
	$block = 0;
	$soft = 0;
	$hard = 0;
	foreach ($rows as $row) {
		$stage = (string) ($row['stage'] ?? $defaultStage);
		$ev = fractal_zip_ablation_evaluate_row($row, $stage);
		$evaluated[] = $ev;
		if ($ev['promote']) {
			$promote++;
			if (in_array('semantic_ok_soft', $ev['promote_reasons'], true)) {
				$soft++;
			}
		} else {
			$block++;
			if (in_array('verify_failed_without_soft_closeness', $ev['block_reasons'], true)) {
				$hard++;
			}
		}
	}
	return array(
		'evaluated' => $evaluated,
		'promote_count' => $promote,
		'block_count' => $block,
		'soft_rescues' => $soft,
		'hard_blocks' => $hard,
	);
}

/**
 * @param array<string, mixed> $ledger
 */
function fractal_zip_ablation_ledger_write(string $path, array $ledger): void
{
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0775, true);
	}
	$json = json_encode($ledger, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
	if ($json === false) {
		throw new RuntimeException('Failed to encode ablation ledger');
	}
	file_put_contents($path, $json . "\n");
}

/**
 * Extract candidate rows from run_benchmarks.php --json shape or a flat list.
 *
 * @return list<array<string, mixed>>
 */
function fractal_zip_ablation_rows_from_bench_json(array $bench): array
{
	$cases = $bench['cases'] ?? $bench['rows'] ?? null;
	if (!is_array($cases)) {
		if (isset($bench[0]) && is_array($bench[0])) {
			return $bench;
		}
		return array($bench);
	}
	$out = array();
	foreach ($cases as $c) {
		if (!is_array($c)) {
			continue;
		}
		$out[] = $c;
	}
	return $out;
}
