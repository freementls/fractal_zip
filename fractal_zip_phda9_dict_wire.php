<?php

declare(strict_types=1);

/**
 * phda9 external dict selection gated on honest .fz wire (wire460 scoreboard stack).
 *
 * Δ_wire = trial_wire_fzc − baseline_wire_fzc; keep edit when Δ_wire < 0.
 * External dict bytes are off-wire (same accounting as wire460 baseline).
 */

require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_phda9_dict_mine.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_score_gate.php';

/** Scoreboard stack: phda9_xml + LSTM + single-stream + sort_title (no dict fold). */
function fractal_zip_phda9_dict_wire_apply_scoreboard_stack(string $repo): void
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
	bench_world_record_apply_pp96_core_env();
	bench_wire_probe_reset_stat_preprocess_env();
	putenv('FRACTAL_ZIP_ENWIK_ENTRY_SORT=1');
	putenv('FRACTAL_ZIP_ENWIK_TEXT_CODEC=0');
	putenv('FRACTAL_ZIP_ENWIK_CORPUS_PHRASES=0');
	putenv('FRACTAL_ZIP_TEXT_INNER=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_FORMAT=phda9_xml');
	putenv('FRACTAL_ZIP_TEXT_INNER_MONO=1');
	putenv('FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title');
	putenv('FRACTAL_ZIP_TEXT_INNER_PREPROCESS=none');
	putenv('FRACTAL_ZIP_TEXT_INNER_STACK=none');
	putenv('FRACTAL_ZIP_PHDA9_XML_SINGLE_STREAM=1');
	putenv('FRACTAL_ZIP_PHDA9_ENGLISH_TOOL=phda9');
	putenv('FRACTAL_ZIP_ENWIK_PHDA9_ENGLISH_JOBS=1');
	putenv('FRACTAL_ZIP_PAQ_TIMEOUT_SEC=0');
	putenv('FRACTAL_ZIP_ENWIK_MEMBER_SHOOTOUT_TIMEOUT_SEC=7200');
	putenv('FRACTAL_ZIP_SKIP_BROTLI=1');
	putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=0');
	putenv('FRACTAL_ZIP_OUTER_PREDICT=0');
	putenv('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE=0');
	putenv('FRACTAL_ZIP_WEB_REF=0');
	putenv('FRACTAL_ZIP_PHDA9_DICT_FOLD=0');
	putenv('FRACTAL_ZIP_INNER_FOLD_FRACTAL=0');
}

/**
 * @param list<string> $words
 */
function fractal_zip_phda9_dict_wire_bind_dict(array $words, ?string $reusePath = null): string
{
	if ($reusePath !== null && is_file($reusePath)) {
		$seedWords = fractal_zip_phda9_dict_read_words($reusePath);
		if ($seedWords === $words) {
			putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $reusePath);
			return $reusePath;
		}
	}
	$path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_phda9_wire_dict_'
		. getmypid() . '_' . bin2hex(random_bytes(4)) . '.txt';
	fractal_zip_phda9_dict_write_file($words, $path);
	putenv('FRACTAL_ZIP_PAQ_PHDA9_DICT=' . $path);
	return $path;
}

/**
 * Build enwik8 slice blob + path under $sessionDir.
 *
 * @return array{slice_path: string, pages: int, raw_bytes: int, session_dir: string}
 */
function fractal_zip_phda9_dict_wire_prepare_slice(string $repo, int $pages, string $sessionDir): array
{
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip_enwik.php';
	$src = $repo . DIRECTORY_SEPARATOR . 'test_files109' . DIRECTORY_SEPARATOR . 'enwik8';
	if (!is_file($src)) {
		$src = $repo . DIRECTORY_SEPARATOR . 'enwik8';
	}
	if (!is_file($src)) {
		throw new RuntimeException('missing enwik8 source');
	}
	@mkdir($sessionDir, 0700, true);
	$blob = (string) file_get_contents($src);
	$split = enwik_split_page_refs($blob);
	if ($split === null) {
		throw new RuntimeException('enwik split failed');
	}
	$n = min($pages, count($split['pages']));
	$slice = (string) $split['header'];
	for ($i = 0; $i < $n; $i++) {
		$p = $split['pages'][$i];
		$slice .= substr($blob, (int) $p['start'], (int) $p['len']);
	}
	$slice .= (string) $split['footer'];
	$slicePath = $sessionDir . DIRECTORY_SEPARATOR . 'enwik8';
	file_put_contents($slicePath, $slice);
	putenv('FRACTAL_ZIP_WIRE_PROBE_PAGES=' . (string) $n);
	return array(
		'slice_path' => $slicePath,
		'pages' => $n,
		'raw_bytes' => strlen($slice),
		'session_dir' => $sessionDir,
	);
}

/**
 * Honest wire encode on the wire460 scoreboard stack.
 *
 * @param list<string> $dictWords
 * @param ?string $seedDictPath reuse when $dictWords unchanged (avoids rewrite)
 * @return array{
 *   wire_fzc: int,
 *   zip_seconds: float,
 *   outer_codec: ?string,
 *   roundtrip_ok: bool,
 *   extract_seconds: float,
 *   dict_path: string
 * }|null
 */
function fractal_zip_phda9_dict_wire_encode(
	string $repo,
	string $slicePath,
	string $runDir,
	array $dictWords,
	bool $verifyRt = false,
	?string $seedDictPath = null
): ?array {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'fractal_zip.php';
	fractal_zip_phda9_dict_wire_apply_scoreboard_stack($repo);
	$dictPath = fractal_zip_phda9_dict_wire_bind_dict($dictWords, $seedDictPath);
	@mkdir($runDir, 0700, true);
	$work = $runDir . DIRECTORY_SEPARATOR . 'work';
	fractal_zip_enwik_recursive_remove($work);
	@mkdir($work, 0700, true);
	copy($slicePath, $work . DIRECTORY_SEPARATOR . 'enwik8');
	$fzc = $work . '.fz';
	@unlink($fzc);
	$err = null;
	$fzcBytes = 0;
	$outerCodec = null;
	$shouldDeleteDict = ($dictPath !== $seedDictPath);
	$t0 = microtime(true);
	try {
		$fz = new fractal_zip();
		$fz->zip_folder($work, false);
		$fzcBytes = is_file($fzc) ? (int) filesize($fzc) : 0;
		$outerCodec = fractal_zip::$last_outer_codec ?? null;
	} catch (Throwable $e) {
		$err = $e->getMessage();
	}
	$zipSec = microtime(true) - $t0;
	if ($err !== null || $fzcBytes <= 0) {
		if ($shouldDeleteDict) {
			@unlink($dictPath);
		}
		return null;
	}
	$rtOk = false;
	$rtSec = 0.0;
	if ($verifyRt) {
		$rtWork = $runDir . DIRECTORY_SEPARATOR . 'rt';
		@mkdir($rtWork, 0700, true);
		copy($fzc, $rtWork . DIRECTORY_SEPARATOR . 't.fz');
		try {
			$tRt = microtime(true);
			$fx = new fractal_zip();
			$fx->open_container($rtWork . DIRECTORY_SEPARATOR . 't.fz', false);
			$rtSec = microtime(true) - $tRt;
			$got = $rtWork . DIRECTORY_SEPARATOR . 'enwik8';
			$rtOk = is_file($got) && (string) file_get_contents($got) === (string) file_get_contents($slicePath);
		} catch (Throwable $e) {
			$rtOk = false;
		}
		fractal_zip_enwik_recursive_remove($rtWork);
	} else {
		$rtOk = true;
	}
	if ($shouldDeleteDict) {
		@unlink($dictPath);
	}
	return array(
		'wire_fzc' => $fzcBytes,
		'zip_seconds' => $zipSec,
		'outer_codec' => $outerCodec,
		'roundtrip_ok' => $rtOk,
		'extract_seconds' => $rtSec,
		'dict_path' => $dictPath,
	);
}

/**
 * Greedy dict edits scored by honest wire_fzc (marginal Δ < 0 keeps edit).
 *
 * @param list<string> $seedWords
 * @param list<array{token: string, kind?: string, est_save?: int, count?: int}> $addCandidatesScored
 * @param list<array{token: string, kind?: string, est_save?: int, count?: int}> $removalCandidatesScored slice-present seed entries (est_save order); empty skips removals
 * @return array{
 *   words: list<string>,
 *   baseline_wire: int,
 *   best_wire: int,
 *   delta_wire: int,
 *   trials: list<array<string, mixed>>,
 *   removals_tried: int,
 *   additions_tried: int
 * }
 */
function fractal_zip_phda9_dict_wire_refine_greedy(
	string $repo,
	string $slicePath,
	string $sessionDir,
	array $seedWords,
	string $scoreCorpusXml,
	array $addCandidatesScored,
	int $maxRemovals = 12,
	int $maxAdditions = 16,
	bool $verifyFinalRt = true,
	?callable $onTrial = null,
	array $removalCandidatesScored = array(),
	?string $seedDictPath = null
): array {
	$runs = $sessionDir . DIRECTORY_SEPARATOR . 'runs';
	@mkdir($runs, 0700, true);
	$trialIdx = 0;
	$nextRun = static function () use (&$trialIdx, $runs): string {
		$dir = $runs . DIRECTORY_SEPARATOR . sprintf('t_%04d', $trialIdx++);
		@mkdir($dir, 0700, true);
		return $dir;
	};

	$baseline = fractal_zip_phda9_dict_wire_encode($repo, $slicePath, $nextRun(), $seedWords, false, $seedDictPath);
	if ($baseline === null) {
		throw new RuntimeException('wire baseline encode failed (no .fz at work.fz)');
	}
	$baselineWire = (int) $baseline['wire_fzc'];
	$bestWords = array_values($seedWords);
	$bestWire = $baselineWire;
	$trials = array();
	$seedSet = array_fill_keys($bestWords, true);

	if ($onTrial !== null) {
		$onTrial(array(
			'op' => 'baseline',
			'token' => '',
			'wire_fzc' => $baselineWire,
			'delta_wire' => 0,
			'accepted' => false,
			'zip_seconds' => (float) ($baseline['zip_seconds'] ?? 0),
		));
	}

	usort($removalCandidatesScored, static fn (array $a, array $b): int =>
		((int) ($a['est_save'] ?? 0)) <=> ((int) ($b['est_save'] ?? 0))
		?: (strlen((string) ($a['token'] ?? '')) <=> strlen((string) ($b['token'] ?? '')))
	);

	$removalsTried = 0;
	foreach ($removalCandidatesScored as $row) {
		if ($removalsTried >= $maxRemovals) {
			break;
		}
		$tok = (string) $row['token'];
		if (!isset($seedSet[$tok])) {
			continue;
		}
		$trialWords = array_values(array_filter($bestWords, static fn (string $w): bool => $w !== $tok));
		$removalsTried++;
		$enc = fractal_zip_phda9_dict_wire_encode($repo, $slicePath, $nextRun(), $trialWords, false, $seedDictPath);
		if ($enc === null) {
			continue;
		}
		$wire = (int) $enc['wire_fzc'];
		$delta = $wire - $bestWire;
		$rowOut = array(
			'op' => 'remove',
			'token' => $tok,
			'est_save' => (int) ($row['est_save'] ?? 0),
			'wire_fzc' => $wire,
			'delta_wire' => $delta,
			'accepted' => $wire < $bestWire,
			'zip_seconds' => (float) ($enc['zip_seconds'] ?? 0),
		);
		$trials[] = $rowOut;
		if ($onTrial !== null) {
			$onTrial($rowOut);
		}
		if ($wire < $bestWire) {
			$bestWire = $wire;
			$bestWords = $trialWords;
			unset($seedSet[$tok]);
		}
	}

	usort($addCandidatesScored, static fn (array $a, array $b): int =>
		((int) ($b['est_save'] ?? 0)) <=> ((int) ($a['est_save'] ?? 0))
		?: (strlen((string) ($b['token'] ?? '')) <=> strlen((string) ($a['token'] ?? '')))
	);
	$present = array_fill_keys($bestWords, true);
	$additionsTried = 0;
	foreach ($addCandidatesScored as $row) {
		if ($additionsTried >= $maxAdditions) {
			break;
		}
		$tok = (string) ($row['token'] ?? '');
		if ($tok === '' || isset($present[$tok])) {
			continue;
		}
		$trialWords = array_merge($bestWords, array($tok));
		$additionsTried++;
		$enc = fractal_zip_phda9_dict_wire_encode($repo, $slicePath, $nextRun(), $trialWords, false, $seedDictPath);
		if ($enc === null) {
			continue;
		}
		$wire = (int) $enc['wire_fzc'];
		$delta = $wire - $bestWire;
		$accepted = $wire < $bestWire;
		$rowOut = array(
			'op' => 'add',
			'token' => $tok,
			'kind' => (string) ($row['kind'] ?? 'token'),
			'est_save' => (int) ($row['est_save'] ?? 0),
			'wire_fzc' => $wire,
			'delta_wire' => $delta,
			'accepted' => $accepted,
			'zip_seconds' => (float) ($enc['zip_seconds'] ?? 0),
		);
		$trials[] = $rowOut;
		if ($onTrial !== null) {
			$onTrial($rowOut);
		}
		if ($accepted) {
			$bestWire = $wire;
			$bestWords = $trialWords;
			$present[$tok] = true;
		}
	}

	if ($verifyFinalRt && $bestWire !== $baselineWire) {
		$final = fractal_zip_phda9_dict_wire_encode($repo, $slicePath, $nextRun(), $bestWords, true, $seedDictPath);
		if ($final !== null && empty($final['roundtrip_ok'])) {
			throw new RuntimeException('wire-refined dict failed final RT');
		}
	} elseif ($verifyFinalRt) {
		$final = fractal_zip_phda9_dict_wire_encode($repo, $slicePath, $nextRun(), $bestWords, true, $seedDictPath);
		if ($final !== null && empty($final['roundtrip_ok'])) {
			throw new RuntimeException('wire baseline dict failed final RT');
		}
	}

	return array(
		'words' => $bestWords,
		'baseline_wire' => $baselineWire,
		'best_wire' => $bestWire,
		'delta_wire' => $bestWire - $baselineWire,
		'trials' => $trials,
		'removals_tried' => $removalsTried,
		'additions_tried' => $additionsTried,
	);
}
