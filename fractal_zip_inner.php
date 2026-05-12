<?php
declare(strict_types=1);

/**
 * Production-facing fractal-inner bridge.
 *
 * This file owns the stable interface used by fractal_zip.php. The current
 * implementation loads the existing detector implementations lazily, then
 * normalizes their rows into verified production candidates. Recipes and
 * fixture metadata are intentionally not loaded here.
 */

if (!function_exists('fractal_zip_inner_include_loaded')) {
	function fractal_zip_inner_include_loaded(fractal_zip $fz): bool {
		$root = rtrim((string) $fz->program_path, DIRECTORY_SEPARATOR);
		$productionInclude = $root . DIRECTORY_SEPARATOR . 'fractal_zip_inner_algorithms.php';
		if (!is_file($productionInclude)) {
			return false;
		}
		require_once $productionInclude;

		return function_exists('fz_inner_recursive_runpair_prefix_successors')
			&& function_exists('fz_inner_multirun_ladder_grammar_successors')
			&& function_exists('fz_inner_operator_verified_successors')
			&& function_exists('fz_inner_pass_search')
			&& function_exists('fz_inner_reference_corpus')
			&& function_exists('fz_inner_linear_fin_lin')
			&& function_exists('fz_row_final_lin')
			&& function_exists('fz_inner_usort_rows_by_final_lin');
	}
}

if (!function_exists('fractal_zip_inner_root_state')) {
	/**
	 * @return array<string,mixed>
	 */
	function fractal_zip_inner_root_state(string $raw): array {
		return array(
			'fractal_ref' => '',
			'dict' => '',
			'equiv' => $raw,
			'linears' => array(fz_inner_linear_fin_lin('', '', $raw)),
			'pieces' => array(),
			'last_occ' => 1,
			'append_depth' => 0,
		);
	}
}

if (!function_exists('fractal_zip_inner_predict_member_lanes')) {
	/**
	 * Cheap, data-derived lane prediction. This does not encode any fixture names
	 * or recipes; it only decides which generic detectors are worth trying.
	 *
	 * @return array{run_grammar:bool, operators:bool}
	 */
	function fractal_zip_inner_predict_member_lanes(string $raw, int $runGrammarMaxRawBytes, int $operatorMaxRawBytes): array {
		$n = strlen($raw);
		$runGrammar = $n >= 64 && ($runGrammarMaxRawBytes <= 0 || $n <= $runGrammarMaxRawBytes);
		if ($runGrammar && fractal_zip_inner_small_low_alphabet_outer_dominates($raw)) {
			$runGrammar = false;
		}
		return array(
			'run_grammar' => $runGrammar,
			'operators' => $operatorMaxRawBytes <= 0 || $n <= $operatorMaxRawBytes,
		);
	}
}

if (!function_exists('fractal_zip_inner_small_low_alphabet_outer_dominates')) {
	function fractal_zip_inner_small_low_alphabet_outer_dominates(string $raw): bool {
		$n = strlen($raw);
		if ($n < 256 || $n > 2048) {
			return false;
		}
		$seen = array();
		for ($i = 0; $i < $n; $i++) {
			$seen[$raw[$i]] = true;
			if (count($seen) > 2) {
				return false;
			}
		}
		if (count($seen) < 2) {
			return false;
		}
		$gz = gzdeflate($raw, 1);
		if ($gz === false) {
			return false;
		}
		return strlen($gz) <= max(64, (int)floor($n * 0.13));
	}
}

if (!function_exists('fractal_zip_inner_folder_small_low_alphabet_outer_dominates')) {
	function fractal_zip_inner_folder_small_low_alphabet_outer_dominates(array $rawFilesByPath): bool {
		if ($rawFilesByPath === array()) {
			return false;
		}
		foreach ($rawFilesByPath as $raw) {
			if (!fractal_zip_inner_small_low_alphabet_outer_dominates((string)$raw)) {
				return false;
			}
		}
		return true;
	}
}

if (!function_exists('fractal_zip_inner_member_looks_compressed_wrapper')) {
	function fractal_zip_inner_member_looks_compressed_wrapper(string $raw): bool {
		$n = strlen($raw);
		if ($n < 18) {
			return false;
		}
		if (substr($raw, 0, 2) === "\x1f\x8b") {
			return true;
		}
		if ($n >= 6 && substr($raw, 0, 6) === "\xfd\x37\x7a\x58\x5a\x00") {
			return true;
		}
		if ($n >= 4 && substr($raw, 0, 4) === "\x28\xb5\x2f\xfd") {
			return true;
		}
		if ($n >= 6 && substr($raw, 0, 6) === "\x37\x7a\xbc\xaf\x27\x1c") {
			return true;
		}
		if ($n >= 4 && substr($raw, 0, 3) === 'BZh' && $raw[3] >= '1' && $raw[3] <= '9') {
			return true;
		}
		if ($n >= 2) {
			$b0 = ord($raw[0]);
			$b1 = ord($raw[1]);
			if (($b0 & 0x0f) === 8 && (($b0 << 8) + $b1) % 31 === 0) {
				return true;
			}
		}
		return false;
	}
}

if (!function_exists('fractal_zip_inner_folder_compressed_wrappers_dominate')) {
	function fractal_zip_inner_folder_compressed_wrappers_dominate(array $rawFilesByPath): bool {
		$count = count($rawFilesByPath);
		if ($count < 4) {
			return false;
		}
		$wrapped = 0;
		foreach ($rawFilesByPath as $raw) {
			if (fractal_zip_inner_member_looks_compressed_wrapper((string)$raw)) {
				$wrapped++;
			}
		}
		return $wrapped > 0 && ($wrapped * 5) >= ($count * 4);
	}
}

if (!function_exists('fractal_zip_inner_reference_for_row')) {
	function fractal_zip_inner_reference_for_row(array $row): string {
		return fz_inner_reference_corpus((string)($row['fractal_ref'] ?? ''), (string)($row['dict'] ?? ''));
	}
}

if (!function_exists('fractal_zip_inner_row_is_production_safe')) {
	function fractal_zip_inner_row_is_production_safe(array $row): bool {
		$pieces = $row['pieces'] ?? array();
		if (!is_array($pieces)) {
			return true;
		}
		foreach ($pieces as $piece) {
			$p = strtolower((string)$piece);
			if (str_contains($p, 'verified-encoding-hint') || str_contains($p, 'recipe-oracle') || str_contains($p, 'oracle')) {
				return false;
			}
		}
		return true;
	}
}

if (!function_exists('fractal_zip_inner_run_grammar_rows')) {
	/**
	 * @return list<array<string,mixed>>
	 */
	function fractal_zip_inner_run_grammar_rows(fractal_zip $fz, string $raw, int $maxRawBytes): array {
		$rawLen = strlen($raw);
		if ($rawLen < 64 || ($maxRawBytes > 0 && $rawLen > $maxRawBytes)) {
			return array();
		}
		if (!fractal_zip_inner_include_loaded($fz)) {
			return array();
		}
		$rootState = fractal_zip_inner_root_state($raw);
		$rows = array();
		foreach (fz_inner_recursive_runpair_prefix_successors($fz, $raw, $rootState) as $row) {
			$rows[] = $row;
		}
		foreach (fz_inner_multirun_ladder_grammar_successors($fz, $raw, $rootState) as $row) {
			$rows[] = $row;
		}
		if ($rows === array()) {
			return array();
		}
		fz_inner_usort_rows_by_final_lin($rows);
		$out = array();
		$seen = array();
		foreach ($rows as $row) {
			if (!fractal_zip_inner_row_is_production_safe($row)) {
				continue;
			}
			$fr = (string)($row['fractal_ref'] ?? '');
			$eq = (string)($row['equiv'] ?? '');
			if ($fr === '' || $eq === '') {
				continue;
			}
			$key = $fr . "\0" . $eq;
			if (isset($seen[$key])) {
				continue;
			}
			$seen[$key] = true;
			try {
				$expanded = $fz->unzip($eq, $fr);
			} catch (Throwable $e) {
				continue;
			}
			if ($expanded !== $raw) {
				continue;
			}
			$out[] = $row;
			if (count($out) >= 4) {
				break;
			}
		}
		return $out;
	}
}

if (!function_exists('fractal_zip_inner_candidate_rows')) {
	/**
	 * @return list<array<string,mixed>>
	 */
	function fractal_zip_inner_candidate_rows(fractal_zip $fz, string $raw, int $runGrammarMaxRawBytes, int $operatorMaxRawBytes, bool $includeShallowPass = true): array {
		if (!fractal_zip_inner_include_loaded($fz)) {
			return array();
		}
		$periodicRows = fractal_zip_inner_periodic_tuple_rows($fz, $raw);
		if ($periodicRows !== array() && fractal_zip_inner_periodic_tuple_is_decisive($periodicRows[0], $raw)) {
			return fractal_zip_inner_verified_rows($fz, $raw, $periodicRows, 8);
		}
		$periodicRunRows = fractal_zip_inner_periodic_run_rows($fz, $raw);
		if ($periodicRunRows !== array() && fractal_zip_inner_periodic_tuple_is_decisive($periodicRunRows[0], $raw)) {
			return fractal_zip_inner_verified_rows($fz, $raw, $periodicRunRows, 8);
		}
		$gradientSpanRows = fractal_zip_inner_gradient_span_rows($fz, $raw);
		if ($gradientSpanRows !== array() && fractal_zip_inner_periodic_tuple_is_decisive($gradientSpanRows[0], $raw)) {
			return fractal_zip_inner_verified_rows($fz, $raw, $gradientSpanRows, 8);
		}
		$lanes = fractal_zip_inner_predict_member_lanes($raw, $runGrammarMaxRawBytes, $operatorMaxRawBytes);
		$rows = $lanes['run_grammar'] ? fractal_zip_inner_run_grammar_rows($fz, $raw, $runGrammarMaxRawBytes) : array();
		foreach ($periodicRows as $row) {
			$rows[] = $row;
		}
		foreach ($periodicRunRows as $row) {
			$rows[] = $row;
		}
		foreach ($gradientSpanRows as $row) {
			$rows[] = $row;
		}
		if ($lanes['operators']) {
			$rootState = fractal_zip_inner_root_state($raw);
			foreach (fz_inner_operator_verified_successors($fz, $raw, $rootState) as $row) {
				$rows[] = $row;
			}
		}
		if ($includeShallowPass) {
			foreach (fractal_zip_inner_shallow_pass_rows($fz, $raw) as $row) {
				$rows[] = $row;
			}
		}
		if ($rows === array()) {
			return array();
		}
		return fractal_zip_inner_verified_rows($fz, $raw, $rows, 8);
	}
}

if (!function_exists('fractal_zip_inner_verified_rows')) {
	/**
	 * Verify and dedupe candidate rows through the production decoder.
	 *
	 * @param list<array<string,mixed>> $rows
	 * @return list<array<string,mixed>>
	 */
	function fractal_zip_inner_verified_rows(fractal_zip $fz, string $raw, array $rows, int $limit): array {
		if ($rows === array() || $limit <= 0) {
			return array();
		}
		fz_inner_usort_rows_by_final_lin($rows);
		$out = array();
		$seen = array();
		foreach ($rows as $row) {
			if (!fractal_zip_inner_row_is_production_safe($row)) {
				continue;
			}
			$eq = (string)($row['equiv'] ?? '');
			if ($eq === '') {
				continue;
			}
			$localRef = fractal_zip_inner_reference_for_row($row);
			$key = $localRef . "\0" . $eq;
			if (isset($seen[$key])) {
				continue;
			}
			$seen[$key] = true;
			try {
				$expanded = $fz->unzip($eq, $localRef);
			} catch (Throwable $e) {
				continue;
			}
			if ($expanded !== $raw) {
				continue;
			}
			$out[] = $row;
			if (count($out) >= $limit) {
				break;
			}
		}
		return $out;
	}
}

if (!function_exists('fractal_zip_inner_periodic_tuple_is_decisive')) {
	function fractal_zip_inner_periodic_tuple_is_decisive(array $row, string $raw): bool {
		$rawLen = strlen($raw);
		if ($rawLen < 64) {
			return false;
		}
		$finalLin = fz_row_final_lin($row);
		return $finalLin > 0 && ($finalLin * 4) <= $rawLen;
	}
}

if (!function_exists('fractal_zip_inner_periodic_tuple_max_raw_bytes')) {
	function fractal_zip_inner_periodic_tuple_max_raw_bytes(): int {
		$e = getenv('FRACTAL_ZIP_INNER_PERIODIC_TUPLE_MAX_RAW_BYTES');
		if ($e === false || trim((string)$e) === '') {
			return 65536;
		}
		return max(0, (int)trim((string)$e));
	}
}

if (!function_exists('fractal_zip_inner_periodic_tuple_rows')) {
	/**
	 * Fast exact-period detector for ordinary repeated byte strings.
	 *
	 * This covers a cheap 1-pass fractal case without invoking the full inner
	 * pass search. It is byte-derived only: no filenames, recipes, or corpus IDs.
	 *
	 * @return list<array<string,mixed>>
	 */
	function fractal_zip_inner_periodic_tuple_rows(fractal_zip $fz, string $raw): array {
		$n = strlen($raw);
		$maxBytes = fractal_zip_inner_periodic_tuple_max_raw_bytes();
		if ($n < 64 || ($maxBytes > 0 && $n > $maxBytes)) {
			return array();
		}
		$maxPeriod = min(256, intdiv($n, 3));
		$out = array();
		$baseLin = fz_inner_linear_fin_lin('', '', $raw);
		for ($period = 1; $period <= $maxPeriod; $period++) {
			if (($n % $period) !== 0) {
				continue;
			}
			$reps = intdiv($n, $period);
			if ($reps < 3) {
				continue;
			}
			$unit = substr($raw, 0, $period);
			if ($unit === '' || str_repeat($unit, $reps) !== $raw) {
				continue;
			}
			$eq = $fz->left_fractal_zip_marker . '0' . $fz->mid_fractal_zip_marker . (string)$period . '*' . (string)$reps . $fz->right_fractal_zip_marker;
			try {
				$expanded = $fz->unzip($eq, $unit);
			} catch (Throwable $e) {
				continue;
			}
			if ($expanded !== $raw) {
				continue;
			}
			$out[] = array(
				'fractal_ref' => $unit,
				'dict' => '',
				'equiv' => $eq,
				'linears' => array($baseLin, fz_inner_linear_fin_lin($unit, '', $eq)),
				'pieces' => array('<periodic-tuple:' . (string)$period . 'x' . (string)$reps . '>'),
				'last_occ' => $reps,
				'append_depth' => 1,
				'last_compute_ns' => 0,
			);
			break;
		}
		return $out;
	}
}

if (!function_exists('fractal_zip_inner_gradient_span_rows')) {
	/**
	 * Compose independent gradient spans in one equivalence string.
	 *
	 * @return list<array<string,mixed>>
	 */
	function fractal_zip_inner_gradient_span_rows(fractal_zip $fz, string $raw): array {
		$n = strlen($raw);
		$maxBytes = fractal_zip_inner_periodic_run_max_raw_bytes();
		if ($n < 16 || ($maxBytes > 0 && $n > $maxBytes)) {
			return array();
		}
		$candidates = array();
		$maxSpan = min(2048, $n);
		for ($start = 0; $start < $n - 3; $start++) {
			$step = ord($raw[$start + 1]) - ord($raw[$start]);
			if ($step <= 0) {
				continue;
			}
			$end = $start + 1;
			while ($end + 1 < $n && ord($raw[$end + 1]) - ord($raw[$end]) === $step && ($end + 1 - $start) <= $maxSpan) {
				$end++;
			}
			$len = $end - $start + 1;
			if ($len >= 4) {
				$lit = substr($raw, $start, $len);
				$tag = fz_inner_gradient_tag_for_literal($fz, $lit);
				if ($tag !== null && strlen($tag) + 2 < $len) {
					$candidates[] = array('start' => $start, 'len' => $len, 'tag' => $tag);
				}
				$start = $end - 1;
			}
		}
		if ($candidates === array()) {
			return array();
		}
		usort($candidates, static function(array $a, array $b): int {
			$scoreA = ((int)$a['len'] - strlen((string)$a['tag']));
			$scoreB = ((int)$b['len'] - strlen((string)$b['tag']));
			return $scoreB <=> $scoreA;
		});
		$picked = array();
		foreach ($candidates as $cand) {
			$start = (int)$cand['start'];
			$end = $start + (int)$cand['len'];
			foreach ($picked as $p) {
				$pStart = (int)$p['start'];
				$pEnd = $pStart + (int)$p['len'];
				if (!($end <= $pStart || $pEnd <= $start)) {
					continue 2;
				}
			}
			$picked[] = $cand;
			if (count($picked) >= 8) {
				break;
			}
		}
		if ($picked === array()) {
			return array();
		}
		usort($picked, static function(array $a, array $b): int {
			return ((int)$a['start']) <=> ((int)$b['start']);
		});
		$eq = '';
		$cursor = 0;
		$pieces = array();
		foreach ($picked as $span) {
			$start = (int)$span['start'];
			$len = (int)$span['len'];
			$eq .= substr($raw, $cursor, $start - $cursor) . (string)$span['tag'];
			$cursor = $start + $len;
			$pieces[] = (string)$len . '@' . (string)$start;
		}
		$eq .= substr($raw, $cursor);
		$baseLin = fz_inner_linear_fin_lin('', '', $raw);
		$fin = fz_inner_linear_fin_lin('', '', $eq);
		if ($fin >= $baseLin) {
			return array();
		}
		return array(array(
			'fractal_ref' => '',
			'dict' => '',
			'equiv' => $eq,
			'linears' => array($baseLin, $fin),
			'pieces' => array('<gradient-spans:' . implode(',', $pieces) . '>'),
			'last_occ' => count($picked),
			'append_depth' => 1,
			'last_compute_ns' => 0,
		));
	}
}

if (!function_exists('fractal_zip_inner_periodic_run_rows')) {
	/**
	 * Fast detector for dominant repeated run(s) with literal gaps around them.
	 *
	 * @return list<array<string,mixed>>
	 */
	function fractal_zip_inner_periodic_run_rows(fractal_zip $fz, string $raw): array {
		$n = strlen($raw);
		$maxBytes = fractal_zip_inner_periodic_run_max_raw_bytes();
		if ($n < 96 || ($maxBytes > 0 && $n > $maxBytes)) {
			return array();
		}
		$minCover = max(64, (int)floor($n * 0.25));
		$maxPeriod = min(256, intdiv($n, 3));
		$best = null;
		$candidates = array();
		$seen = array();
		$baseLin = fz_inner_linear_fin_lin('', '', $raw);
		for ($period = 1; $period <= $maxPeriod; $period++) {
			$matchRun = 0;
			$shiftLimit = $n - $period;
			for ($i = 0; $i <= $shiftLimit; $i++) {
				$matched = ($i < $shiftLimit && $raw[$i] === $raw[$i + $period]);
				if ($matched) {
					$matchRun++;
					continue;
				}
				if ($matchRun >= ($period * 2)) {
					$start = $i - $matchRun;
					$reps = intdiv($matchRun + $period, $period);
					$cover = $reps * $period;
					if ($reps >= 3 && $cover >= $minCover) {
						$unit = substr($raw, $start, $period);
						$key = $start . ':' . $period . ':' . $cover;
						if (isset($seen[$key])) {
							$matchRun = 0;
							continue;
						}
						$seen[$key] = true;
						$prefix = substr($raw, 0, $start);
						$suffix = substr($raw, $start + $cover);
						$eq = $prefix
							. $fz->left_fractal_zip_marker . '0' . $fz->mid_fractal_zip_marker . (string)$period . '*' . (string)$reps . $fz->right_fractal_zip_marker
							. $suffix;
						$fin = fz_inner_linear_fin_lin($unit, '', $eq);
						$candidate = array(
							'start' => $start,
							'period' => $period,
							'reps' => $reps,
							'cover' => $cover,
							'unit' => $unit,
							'fin' => $fin,
						);
						$candidates[] = $candidate;
						if ($best === null || $fin < ($best['fin'] ?? PHP_INT_MAX)) {
							$best = array(
								'fin' => $fin,
								'row' => array(
									'fractal_ref' => $unit,
									'dict' => '',
									'equiv' => $eq,
									'linears' => array($baseLin, $fin),
									'pieces' => array('<periodic-run:' . (string)$period . 'x' . (string)$reps . '@' . (string)$start . '>'),
									'last_occ' => $reps,
									'append_depth' => 1,
									'last_compute_ns' => 0,
								),
							);
						}
					}
				}
				$matchRun = 0;
			}
		}
		if (count($candidates) >= 2) {
			usort($candidates, static function(array $a, array $b): int {
				$scoreA = ((int)$a['cover'] * 8) - (int)$a['fin'];
				$scoreB = ((int)$b['cover'] * 8) - (int)$b['fin'];
				return $scoreB <=> $scoreA;
			});
			$candidates = array_slice($candidates, 0, 24);
			$count = count($candidates);
			for ($i = 0; $i < $count; $i++) {
				for ($j = $i + 1; $j < $count; $j++) {
					$a = $candidates[$i];
					$b = $candidates[$j];
					$aEnd = (int)$a['start'] + (int)$a['cover'];
					$bEnd = (int)$b['start'] + (int)$b['cover'];
					if (!($aEnd <= (int)$b['start'] || $bEnd <= (int)$a['start'])) {
						continue;
					}
					$runs = ((int)$a['start'] <= (int)$b['start']) ? array($a, $b) : array($b, $a);
					$ref = '';
					$unitOffsets = array();
					$eq = '';
					$cursor = 0;
					$pieces = array();
					$totalOcc = 0;
					foreach ($runs as $run) {
						$start = (int)$run['start'];
						$period = (int)$run['period'];
						$reps = (int)$run['reps'];
						$cover = (int)$run['cover'];
						$unit = (string)$run['unit'];
						if (!isset($unitOffsets[$unit])) {
							$unitOffsets[$unit] = strlen($ref);
							$ref .= $unit;
						}
						$eq .= substr($raw, $cursor, $start - $cursor)
							. $fz->left_fractal_zip_marker . (string)$unitOffsets[$unit] . $fz->mid_fractal_zip_marker . (string)$period . '*' . (string)$reps . $fz->right_fractal_zip_marker;
						$cursor = $start + $cover;
						$pieces[] = (string)$period . 'x' . (string)$reps . '@' . (string)$start;
						$totalOcc += $reps;
					}
					$eq .= substr($raw, $cursor);
					$fin = fz_inner_linear_fin_lin($ref, '', $eq);
					if ($best === null || $fin < ($best['fin'] ?? PHP_INT_MAX)) {
						$best = array(
							'fin' => $fin,
							'row' => array(
								'fractal_ref' => $ref,
								'dict' => '',
								'equiv' => $eq,
								'linears' => array($baseLin, $fin),
								'pieces' => array('<periodic-runs:' . implode(',', $pieces) . '>'),
								'last_occ' => $totalOcc,
								'append_depth' => 1,
								'last_compute_ns' => 0,
							),
						);
					}
				}
			}
		}
		return $best === null ? array() : array($best['row']);
	}
}

if (!function_exists('fractal_zip_inner_periodic_run_max_raw_bytes')) {
	function fractal_zip_inner_periodic_run_max_raw_bytes(): int {
		$e = getenv('FRACTAL_ZIP_INNER_PERIODIC_RUN_MAX_RAW_BYTES');
		if ($e === false || trim((string)$e) === '') {
			return 8192;
		}
		return max(0, (int)trim((string)$e));
	}
}

if (!function_exists('fractal_zip_inner_shallow_pass_max_raw_bytes')) {
	function fractal_zip_inner_shallow_pass_max_raw_bytes(): int {
		$e = getenv('FRACTAL_ZIP_INNER_SHALLOW_PASS_MAX_RAW_BYTES');
		if ($e === false || trim((string)$e) === '') {
			return 192;
		}
		return max(0, (int)trim((string)$e));
	}
}

if (!function_exists('fractal_zip_inner_shallow_pass_max_passes')) {
	function fractal_zip_inner_shallow_pass_max_passes(): int {
		$e = getenv('FRACTAL_ZIP_INNER_SHALLOW_PASS_MAX_PASSES');
		if ($e === false || trim((string)$e) === '') {
			return 1;
		}
		return max(0, min(3, (int)trim((string)$e)));
	}
}

if (!function_exists('fractal_zip_inner_shallow_pass_wall_seconds')) {
	function fractal_zip_inner_shallow_pass_wall_seconds(): float {
		$e = getenv('FRACTAL_ZIP_INNER_SHALLOW_PASS_WALL_SECONDS');
		if ($e === false || trim((string)$e) === '') {
			return 0.05;
		}
		return max(0.0, min(5.0, (float)trim((string)$e)));
	}
}

if (!function_exists('fractal_zip_inner_shallow_pass_rows')) {
	/**
	 * Generic shallow inner pass search for ordinary small files. This is intentionally
	 * bounded by size and wall time; it uses the recipe-free append/process machinery
	 * and production verification before returning rows.
	 *
	 * @return list<array<string,mixed>>
	 */
	function fractal_zip_inner_shallow_pass_rows(fractal_zip $fz, string $raw): array {
		$maxBytes = fractal_zip_inner_shallow_pass_max_raw_bytes();
		$n = strlen($raw);
		if ($n < 64 || ($maxBytes > 0 && $n > $maxBytes)) {
			return array();
		}
		$maxPasses = fractal_zip_inner_shallow_pass_max_passes();
		if ($maxPasses < 1 || !fractal_zip_inner_include_loaded($fz)) {
			return array();
		}
		$sr = fz_inner_pass_search(
			$fz,
			$raw,
			$maxPasses,
			8,
			true,
			512,
			null,
			true,
			false,
			false,
			fractal_zip_inner_shallow_pass_wall_seconds(),
			false
		);
		$rows = $sr['rows'] ?? null;
		if (!is_array($rows) || $rows === array()) {
			return array();
		}
		fz_inner_usort_rows_by_final_lin($rows);
		$out = array();
		$seen = array();
		foreach ($rows as $row) {
			if (!is_array($row) || !fractal_zip_inner_row_is_production_safe($row)) {
				continue;
			}
			$eq = (string)($row['equiv'] ?? '');
			if ($eq === '' || $eq === $raw) {
				continue;
			}
			$ref = fractal_zip_inner_reference_for_row($row);
			$key = $ref . "\0" . $eq;
			if (isset($seen[$key])) {
				continue;
			}
			$seen[$key] = true;
			try {
				$expanded = $fz->unzip($eq, $ref);
			} catch (Throwable $e) {
				continue;
			}
			if ($expanded !== $raw) {
				continue;
			}
			$out[] = $row;
			if (count($out) >= 4) {
				break;
			}
		}
		return $out;
	}
}

if (!function_exists('fractal_zip_inner_candidate_from_inner')) {
	/**
	 * @return array{inner:string,tag:string,lane:string,source:string,restore:array<string,string>,cost_hint:int}
	 */
	function fractal_zip_inner_candidate_from_inner(string $inner, string $tag, string $lane, string $source, int $costHint = 0): array {
		return array(
			'inner' => $inner,
			'tag' => $tag,
			'lane' => $lane,
			'source' => $source,
			'restore' => array(),
			'cost_hint' => max(0, $costHint),
		);
	}
}

if (!function_exists('fractal_zip_candidate_from_inner')) {
	/**
	 * Shared inner candidate shape used by literal, legacy, and fractal-inner lanes.
	 *
	 * @return array{inner:string,tag:string,lane:string,source:string,restore:array<string,string>,cost_hint:int}
	 */
	function fractal_zip_candidate_from_inner(string $inner, string $tag, string $lane, string $source, array $restore = array(), int $costHint = 0): array {
		$c = fractal_zip_inner_candidate_from_inner($inner, $tag, $lane, $source, $costHint);
		$c['restore'] = $restore;
		return $c;
	}
}

if (!function_exists('fractal_zip_candidate_normalize')) {
	/**
	 * @return array{inner:string,tag:string,lane:string,source:string,restore:array<string,string>,cost_hint:int}|null
	 */
	function fractal_zip_candidate_normalize($variant, string $defaultLane = 'literal', string $defaultSource = 'legacy'): ?array {
		if (!is_array($variant) || !isset($variant['inner'])) {
			return null;
		}
		$inner = (string)$variant['inner'];
		if ($inner === '') {
			return null;
		}
		$tag = isset($variant['tag']) ? (string)$variant['tag'] : $defaultLane;
		$lane = isset($variant['lane']) ? (string)$variant['lane'] : $defaultLane;
		$source = isset($variant['source']) ? (string)$variant['source'] : $defaultSource;
		$restore = (isset($variant['restore']) && is_array($variant['restore'])) ? $variant['restore'] : array();
		$costHint = isset($variant['cost_hint']) ? (int)$variant['cost_hint'] : 0;
		return fractal_zip_candidate_from_inner($inner, $tag, $lane, $source, $restore, $costHint);
	}
}

if (!function_exists('fractal_zip_candidate_normalize_list')) {
	/**
	 * @param list<array<string,mixed>> $variants
	 * @return list<array{inner:string,tag:string,lane:string,source:string,restore:array<string,string>,cost_hint:int}>
	 */
	function fractal_zip_candidate_normalize_list(array $variants, string $defaultLane = 'literal', string $defaultSource = 'legacy'): array {
		$out = array();
		foreach ($variants as $variant) {
			$c = fractal_zip_candidate_normalize($variant, $defaultLane, $defaultSource);
			if ($c !== null) {
				$out[] = $c;
			}
		}
		return $out;
	}
}

if (!function_exists('fractal_zip_inner_multi_member_literal_escape_cap_bytes')) {
	/**
	 * Max per-member raw size before multi-folder fractal-inner skips building a merged candidate.
	 * Fallback encoding uses escape_literal_for_storage() per member — huge members duplicate memory (str_replace).
	 * When FRACTAL_ZIP_INNER_RUN_GRAMMAR_MAX_RAW_BYTES > 0, that value is used instead (same bound as run-grammar).
	 */
	function fractal_zip_inner_multi_member_literal_escape_cap_bytes(): int {
		$e = getenv('FRACTAL_ZIP_INNER_MULTI_MEMBER_LITERAL_MAX_RAW_BYTES');
		if ($e !== false && trim((string) $e) !== '') {
			return max(0, (int) $e);
		}
		return 4194304;
	}
}

if (!function_exists('fractal_zip_inner_candidates_for_folder')) {
	/**
	 * @param array<string,string> $rawFilesByPath
	 * @return list<array{inner:string,tag:string,lane:string,source:string,restore:array<string,string>,cost_hint:int}>
	 */
	function fractal_zip_inner_candidates_for_folder(fractal_zip $fz, array $rawFilesByPath, int $runGrammarMaxRawBytes, int $operatorMaxRawBytes, bool $includeShallowPass = true): array {
		if ($rawFilesByPath === array()) {
			return array();
		}
		if (fractal_zip_inner_folder_small_low_alphabet_outer_dominates($rawFilesByPath)) {
			return array();
		}
		if (fractal_zip_inner_folder_compressed_wrappers_dominate($rawFilesByPath)) {
			return array();
		}
		$paths = array_keys($rawFilesByPath);
		sort($paths, SORT_STRING);
		if (count($paths) === 1) {
			$path = (string)$paths[0];
			$raw = (string)$rawFilesByPath[$path];
			if ($path === '') {
				return array();
			}
			$out = array();
			foreach (fractal_zip_inner_candidate_rows($fz, $raw, $runGrammarMaxRawBytes, $operatorMaxRawBytes, $includeShallowPass) as $row) {
				$fr = fractal_zip_inner_reference_for_row($row);
				$eq = (string)($row['equiv'] ?? '');
				if ($eq === '') {
					continue;
				}
				$inner = $fz->encode_container_payload(array($path => $eq), $fr);
				$out[] = fractal_zip_inner_candidate_from_inner(
					$inner,
					'inner_verified_ops:' . (string)fz_row_final_lin($row),
					'fractal_inner',
					'single_member',
					(int)($row['last_compute_ns'] ?? 0)
				);
				if (count($out) >= 4) {
					break;
				}
			}
			return $out;
		}

		$literalEscapeCap = $runGrammarMaxRawBytes > 0
			? $runGrammarMaxRawBytes
			: fractal_zip_inner_multi_member_literal_escape_cap_bytes();
		if ($literalEscapeCap > 0) {
			foreach ($paths as $path) {
				$p = (string) $path;
				if (strlen((string) $rawFilesByPath[$p]) > $literalEscapeCap) {
					return array();
				}
			}
		}

		$encoded = array();
		$sharedFr = '';
		$compressedCount = 0;
		$finSum = 0;
		$costHint = 0;
		foreach ($paths as $path) {
			$p = (string)$path;
			$raw = (string)$rawFilesByPath[$p];
			$rows = fractal_zip_inner_candidate_rows($fz, $raw, $runGrammarMaxRawBytes, $operatorMaxRawBytes, false);
			if ($rows === array()) {
				$encoded[$p] = $fz->escape_literal_for_storage($raw);
				continue;
			}
			$row = $rows[0];
			$fr = fractal_zip_inner_reference_for_row($row);
			$eq = (string)($row['equiv'] ?? '');
			if ($eq === '') {
				$encoded[$p] = $fz->escape_literal_for_storage($raw);
				continue;
			}
			$delta = strlen($sharedFr);
			$shiftedEq = $fz->fractal_inner_shift_substring_offsets($eq, $delta);
			$sharedFr .= $fr;
			try {
				$expanded = $fz->unzip($shiftedEq, $sharedFr);
			} catch (Throwable $e) {
				$sharedFr = substr($sharedFr, 0, $delta);
				$encoded[$p] = $fz->escape_literal_for_storage($raw);
				continue;
			}
			if ($expanded !== $raw) {
				$sharedFr = substr($sharedFr, 0, $delta);
				$encoded[$p] = $fz->escape_literal_for_storage($raw);
				continue;
			}
			$encoded[$p] = $shiftedEq;
			$compressedCount++;
			$finSum += fz_row_final_lin($row);
			$costHint += (int)($row['last_compute_ns'] ?? 0);
		}
		if ($compressedCount < 1) {
			return array();
		}
		$inner = $fz->encode_container_payload($encoded, $sharedFr);
		$decoded = $fz->decode_container_payload($inner);
		if (!is_array($decoded) || !isset($decoded[0], $decoded[1]) || !is_array($decoded[0])) {
			return array();
		}
		foreach ($rawFilesByPath as $path => $raw) {
			$p = (string)$path;
			if (!array_key_exists($p, $decoded[0])) {
				return array();
			}
			try {
				$outRaw = $fz->unzip((string)$decoded[0][$p], (string)$decoded[1]);
			} catch (Throwable $e) {
				return array();
			}
			if ($outRaw !== (string)$raw) {
				return array();
			}
		}
		return array(fractal_zip_inner_candidate_from_inner(
			$inner,
			'inner_verified_ops_members:' . (string)$compressedCount . ':' . (string)$finSum,
			'fractal_inner',
			'multi_member',
			$costHint
		));
	}
}

if (!function_exists('fractal_zip_inner_candidates_for_peeled_folder')) {
	/**
	 * Build verified fractal-inner candidates for an already-peeled byte view.
	 *
	 * @param array<string,string> $peeledFilesByPath
	 * @param array<string,string> $restoreByPath exact disk bytes for members whose encoded view is peeled/transformed
	 * @return list<array{inner:string,tag:string,lane:string,source:string,restore:array<string,string>,cost_hint:int}>
	 */
	function fractal_zip_inner_candidates_for_peeled_folder(
		fractal_zip $fz,
		array $peeledFilesByPath,
		array $restoreByPath,
		int $runGrammarMaxRawBytes,
		int $operatorMaxRawBytes,
		string $source
	): array {
		if ($peeledFilesByPath === array() || $restoreByPath === array()) {
			return array();
		}
		$candidates = fractal_zip_inner_candidates_for_folder($fz, $peeledFilesByPath, $runGrammarMaxRawBytes, $operatorMaxRawBytes, false);
		if ($candidates === array()) {
			return array();
		}
		$out = array();
		foreach ($candidates as $candidate) {
			$candidate['restore'] = $restoreByPath;
			$candidate['source'] = $source;
			$candidate['tag'] = (string)$candidate['tag'] . ':' . $source;
			$out[] = $candidate;
		}
		return $out;
	}
}
