<?php
declare(strict_types=1);

/**
 * Encode pipeline orchestration — phased model for fractal_zip folder/string compression.
 *
 * **Design goals**
 * 1. **Unpeel / peel all** — deterministic lossless depth peeling per member; schedule expensive jobs first (LPT).
 * 2. **Bytes stream layout** — build unified literal bundle bytes (FZB*, FZCD, …).
 * 3. **Locality reorder** — optional extension buckets + LPT within bucket ({@code FRACTAL_ZIP_PIPELINE_REORDER_EXT}); content fingerprints later.
 * 4. **Transforms & modes** — tournament over transforms/modes under time/size budgets (moving toward priority-queue parallel tree search).
 * 5. **Outer codecs** — gzip/zstd/brotli/7z/arc/zpaq/… tournaments; schedule slower codecs first where trials are independent.
 *
 * PHP uses processes ({@see pcntl_fork}), not threads. Parallel sections must keep **deterministic merge order** for comparable bytes.
 *
 * **Env (incremental rollout)** — all opt-in unless noted:
 * - {@code FRACTAL_ZIP_PIPELINE=1} — enable pipeline orchestration hooks / tracing helpers.
 * - {@code FRACTAL_ZIP_PIPELINE_TRACE=1} — HTML trace checkpoints in zip_folder (implies observability only).
 * - {@code FRACTAL_ZIP_PIPELINE_JOBS=N} — max concurrent workers for fork pools (default = detected CPU count via {@code nproc} / {@code /proc/cpuinfo}, else 8; capped 32). Under CLI, applied automatically when unset (see {@see bootstrap_cli_parallel_defaults_if_cli}).
 * - {@code FRACTAL_ZIP_PIPELINE_PARALLEL} — fork pools for literal-inner outer trials ({@see parallel_fork_allowed}). CLI default **on** when {@code pcntl_fork} exists (unset → {@see bootstrap_cli_parallel_defaults_if_cli} sets {@code 1}); explicit {@code 0}/{@code off} disables. Non-CLI SAPI stays off unless set to {@code 1}.
 * - {@code FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER} — unset (default): **on** when {@see parallel_fork_allowed} (same default-on spirit as {@code FRACTAL_ZIP_PIPELINE_PARALLEL} on CLI Linux). Overlap **first** outer brotli trial with zstd in {@code adaptive_compress} ({@see speculative_outer_begin_first_brotli}), and (when huge brotli is in **probe** mode) overlap the fast probe with zstd ({@see speculative_outer_begin_huge_probe_brotli}); **merge = same smallest-winning outer as sequential**. Set {@code 0}/{@code off} to disable; {@code 1}/{@code on} forces enable when forks are allowed.
 * - {@code FRACTAL_ZIP_PARALLEL_OUTER_MIN_INNER_BYTES} — optional floor for **fork-based outer lanes** in {@code adaptive_compress} (and parallel zpaq method sweep in {@see fractal_zip::outer_zpaq_blob}, {@see parallel_literal_variant_outer_trials}, layered prediction {@code predict_outer_fork_family_tier_batch} / L1 pipe overlap in {@code benchmarks/predict_outer_layered_probe.php}): inner smaller than N bytes skips overlap/speculative forks ({@see speculative_outer_prediction_begin}, {@see speculative_outer_begin_first_brotli}, {@see speculative_outer_begin_huge_probe_brotli}), parallel lgwin/zstd-1615/{@see parallel_huge_once_arc_before_wave_trials}/{@see parallel_slow_outer_wave_trials}/{@see parallel_zpaq_outer_method_variant_trials}, and uses the synchronous/sequential equivalents (same bytes, less fork overhead on micro inners). Unset ⇒ {@code 8192}; {@code 0} ⇒ always fork when each parallel path is enabled.
 * - {@code FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE} — unset (default): **on** when ≥2 slow codecs scheduled (7z sweep / arc-after-7z / zpaq): fork one trial per codec and merge with sequential **7z→arc→zpaq** {@see fractal_zip::outer_candidate_beats_current} order ({@see parallel_slow_outer_wave_trials}). When layered prediction maps a slow family ({@code zpaq}/{@code 7z}/{@code arc}), {@code adaptive_compress} narrows heavy trials to **that** codec only (unless {@see fractal_zip::ultra_compression_enabled}), so the wave often does not fork three lanes. Fork **starts** are ordered longest-job-first (typically **zpaq → 7z → arc**) when multiple kinds remain. {@code 0} restores sequential slow sweep only.
 * - {@code FRACTAL_ZIP_DEFER_SLOW_PREDICT_LANES_TO_PARALLEL_WAVE} — unset (default **on**): when layered prediction’s family is zpaq, 7z, or arc and the parallel slow outer wave would run that codec with another slow codec, skip the matching early predict lane and rely on the wave only ({@see fractal_zip::defer_slow_predict_lanes_to_parallel_wave_enabled}). Legacy: {@code FRACTAL_ZIP_DEFER_ZPAQ_PREDICT_TO_PARALLEL_WAVE} (if the new name is unset). {@code 0} keeps dedicated predict lanes.
 * - {@code FRACTAL_ZIP_PARALLEL_BROTLI_LGWIN_WAVE} — unset (default): **on** when {@see parallel_fork_allowed}: run FZB Q11 extra {@code -w} brotli trials (w=16 and/or 20/22/24) in a fork pool inside {@see fractal_zip::adaptive_compress} ({@see parallel_brotli_lgwin_variant_trials}). {@code 0} forces sequential lgwin refinement only.
 * - {@code FRACTAL_ZIP_PARALLEL_HUGE_ARC_WAVE} — unset (default): **on** when {@see parallel_fork_allowed}: overlap {@code outer_7z_lzma2_blob} “huge once” with {@code outer_arc_blob} arc-before-7z when both schedule (bytes-first only — disabled under {@code FRACTAL_ZIP_SPEED=1}); merge order matches sequential (7z lzma2 attempt then arc). {@code 0} forces sequential mid-tier only.
 * - {@code FRACTAL_ZIP_PARALLEL_ZSTD_FZB_ALT_WAVE} — unset (default): **on** when {@see parallel_fork_allowed}: for FZB* outer zstd-16 + optional zstd-15 compare ({@see fractal_zip::adaptive_compress}), run **both** levels in parallel ({@see parallel_zstd_fzb_16_and_15_trials}); shorter wins; ties keep zstd-16 as sequential. Children avoid shared tmp reuse paths. {@code 0} restores sequential zstd then alt-15.
 * - {@code FRACTAL_ZIP_PARALLEL_ZPAQ_OUTER_METHOD_WAVE} — unset (default): **on** when {@see parallel_fork_allowed}: {@code outer_zpaq_blob} multi-{@code -method} sweep ({@see parallel_zpaq_outer_method_variant_trials}) runs each trial in a fork pool (same smallest output + tie order as sequential). {@code 0} restores sequential method ladder. When this sweep runs inside the slow outer wave’s zpaq child ({@see child_slow_outer_wave_exit}), nested concurrency is capped (~{@code floor(FRACTAL_ZIP_PIPELINE_JOBS / 3)}) so zpaq method trials do not oversubscribe CPUs versus parallel 7z/arc siblings.
 * - {@code FRACTAL_ZIP_PARALLEL_FOLDER_NATIVE_ZPAQ} — unset (default): **same as** {@code FRACTAL_ZIP_PARALLEL_ZPAQ_OUTER_METHOD_WAVE} when unset; {@code 0} forces sequential {@code zpaq add} trials in {@see fractal_zip::maybe_folder_zpaq_native_smaller_than_fzc}. {@code 1} enables parallel native-folder sweep when forks are allowed.
 * - {@code FRACTAL_ZIP_PARALLEL_FOLDER_NATIVE_ZPAQ_JOBS=N} — optional cap on concurrent native-folder {@code zpaq add} workers ({@see parallel_folder_native_zpaq_method_variant_trials}); unset uses {@code FRACTAL_ZIP_PIPELINE_JOBS} via {@see fractal_zip_encode_pipeline::max_workers} (same pool budget as other fork waves). Use to reduce CPU contention when many {@code zpaq} processes already run elsewhere on the host.
 * - {@code FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP=N} — optional 0–11 cap on brotli quality inside {@see fractal_zip::adaptive_compress_outer_fast_codec_tier} only (staged literal-folder fast tier; bytes-first default Q10/Q11, {@code FRACTAL_ZIP_SPEED=1} default Q3). Full {@code adaptive_compress} rescoring is unchanged.
 * - {@code FRACTAL_ZIP_ZPAQ_GLOBAL_THREADS} — optional override for {@see fractal_zip::zpaq_executable_accepts_global_threads_argv}: {@code 1}/{@code on} forces passing {@code -threads …} to the resolved zpaq binary; {@code 0}/{@code off} forces omitting it (even for zpaqfranz). Use when your build supports {@code -threads} but auto-detection fails.
 * - {@code FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_FRACTAL_LAZY} — unset (default): **on** under CLI: legacy {@see fractal_zip::zip_folder} uses fast-tier pools ({@see parallel_zip_folder_overlap_literal_contest_and_wire_fast_lens}, then {@see parallel_zip_folder_stagger_literal_contest_fr_lazy_fast_lens}, then {@see parallel_zip_folder_wire_fast_outer_lens}) before sequential fallback; full slow outer runs once on the fractal/lazy finalist in the parent. {@code 0} forces sequential contest + probes + finalist. Rollup: fork waves omit parent step rollup.
 * - {@code FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_LEGACY_LITERAL_FRACTAL_LAZY} — legacy {@code zip_folder} now uses a **fast-tier wire shootout** then **one full slow outer** on the fractal/lazy finalist ({@see fractal_zip::adaptive_compress} with {@see fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE}); {@see parallel_zip_folder_legacy_literal_fractal_lazy_trials} remains for callers/tests but is not the default zip_folder path. {@code 0} retained for env parity.
 * - {@code FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_MIN_WIRE_BYTES} — optional floor (positive integer): when the combined literal estimate + fractal + lazy wire byte lengths are **below** this threshold, skip fork-based fast-tier pools ({@see parallel_zip_folder_overlap_literal_contest_and_wire_fast_lens}, {@see parallel_zip_folder_stagger_literal_contest_fr_lazy_fast_lens}, {@see parallel_zip_folder_wire_fast_outer_lens}) and use the sequential path (less fork overhead on micro payloads). Unset or {@code 0}: disabled.
 * - {@code FRACTAL_ZIP_ZPAQ_OUTER_WORK_SYS_TMP} — unset (default): **on** under CLI: zpaq outer {@code fzzpaq_*} dirs use {@see sys_get_temp_dir} (via {@see fractal_zip::zpaq_outer_work_dir_root}). {@code 0} keeps scratch under {@code program_path}.
 * - {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICTION} — unset (default): **on** when {@see parallel_fork_allowed}: {@code fractal_zip_outer_predict_mapped_winner} layered probes run in a **child** overlapping zstd/brotli/xz ({@see speculative_outer_prediction_begin}); parent joins before ratchet/predict lanes. {@code 0} restores synchronous probes immediately after gzip.
 * - {@code FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH} — unset ⇒ skip the expensive layer‑3 high‑tier subprocess on the L2 probe winner ({@see fractal_zip::outer_prediction_layer3_high_enabled}); mapped outer family string is already fixed at L2 — skipping reduces {@code after_outer_prediction_finalize_remainder} wall time. {@code 1}/{@code on} restores full three‑tier probes (benchmarks/predict_outer_benchmarks.php forces {@code 1} unless overridden).
 * - {@code FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC} — unset ⇒ default layered‑prediction subprocess timeout 30 s; with {@code FRACTAL_ZIP_SPEED=1} default is 12 s unless overridden (see {@see fractal_zip::outer_prediction_timeout_sec}).
 * - {@code FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES} — unset ⇒ default max layered‑probe prefix 8 MiB (bytes‑first); with {@code FRACTAL_ZIP_SPEED=1} default is 2 MiB unless overridden (see {@see fractal_zip::outer_prediction_probe_max_bytes}).
 * - {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_FAST_PIPE} — unset (default): **on** under CLI: layered prediction layer‑1 runs brotli/xz/zstd fast stdin probes concurrently inside the prediction worker ({@code benchmarks/predict_outer_heuristic.php}); same compressed lengths as sequential. {@code 0} restores sequential stdin probes per codec.
 * - {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER_FORK} — unset (default): **on** under CLI when {@code pcntl_fork} exists: layered prediction runs 7z/arc/bsc/zpaq fast probes in parallel children, and (when ≥2 top families) medium‑tier probes in parallel ({@code benchmarks/predict_outer_layered_probe.php}); same probe lengths as sequential. {@code 0} restores sequential multi‑family probes. Legacy alias: {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_FILE_FORK}.
 * - {@code FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER1_OVERLAP} — unset (default): **on** under CLI: overlaps the brotli/xz/zstd stdin batch with the 7z/arc/bsc/zpaq fork batch inside the prediction worker (wall ≈ max of the two phases). {@code 0} restores sequential pipe batch then file batch.
 * - {@code FRACTAL_ZIP_PARALLEL_OUTER_XZ_OVERLAP_PREDICT} — unset (default): **on** under {@see parallel_fork_allowed}: {@see speculative_outer_xz_fork_overlap_predict_begin} overlaps outer xz with finishing the prediction fork ({@see speculative_outer_prediction_finalize}); join/fallback matches sequential xz bytes. {@code 0}/{@code off} disables (xz stays synchronous).
 * - {@code FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1} — stderr timings inside {@code adaptive_compress} (step deltas vs gzip baseline).
 * - {@code FRACTAL_ZIP_PIPELINE_OUTER_STEP_VERBOSE=1} — with STEP_LOG: {@code skipped_outer_*} zero-time lines when xz / 7z / arc / zpaq branches do not run.
 * - {@code FRACTAL_ZIP_PIPELINE_REORDER=1} — call {@see reorder_raw_members_for_locality} inside literal bundle encode (default off; default is path-ksort only).
 * - {@code FRACTAL_ZIP_PIPELINE_REORDER_EXT=1} — with REORDER, group members by file extension, order groups by extension string, LPT (largest first) within each group (changes FZB4 member order vs plain ksort).
 * - {@code FRACTAL_ZIP_PIPELINE_INNER_LPT=1} — try literal-bundle outer candidates largest-inner-first (scheduling hint for expensive variants; tie-breakers unchanged unless compressed sizes tie). Under CLI, unset defaults to **on** ({@see bootstrap_cli_parallel_defaults_if_cli}); explicit {@code 0} disables.
 * - With fork pool enabled and ≥2 literal inners, {@see parallel_literal_variant_outer_trials} may fork one process per candidate (full or fast outer), then merge like sequential (raw wins wire-length ties vs literals).
 * - {@code FRACTAL_ZIP_PIPELINE_TIMING=1} — wall-clock segments between {@see html_trace_checkpoint} calls; stderr summary after each {@see pipeline_wall_timer_zip_folder_end}. Subprocess wait time (outer zpaq, etc.) is included in the segment opened by the previous checkpoint. With timing on, {@see pipeline_outer_step_rollup_zip_folder_end} prints outerStep + zip_folder rollup sums and compares them to phase 4 wall (gap = phase 4 wall minus rollup **after subtracting** zip_folder labels whose wall time landed in phase 3 checkpoints — e.g. legacy markers prep — so the gap matches outer codec work; remaining gap is unlabeled phase 4 time such as PHP between {@code adaptive_compress} calls). Phase 4 tier lines 4.1–4.3 bucket rollup labels for readability; tier 4.3 is a catch‑all for slow codec tournament steps (including zpaq sweep), zip_folder disk segments, etc., not “only brotli”.
 * - {@code FRACTAL_ZIP_PIPELINE_OUTER_STEP_ROLLUP} — unset defaults rollup **on** when {@code FRACTAL_ZIP_PIPELINE_TIMING=1}; explicit {@code 1}/{@code true} enables rollup without phase timing; {@code 0}/{@code off} disables rollup even with timing. Aggregates {@see adaptive_compress} step deltas across literal variants into stderr ({@see pipeline_outer_step_rollup_zip_folder_end}). Legacy {@see fractal_zip::zip_folder} also records {@see pipeline_outer_step_rollup_zip_folder_segment} labels for non‑{@code adaptive_compress} work (parallel fast‑lens forks, disk write, …); use {@see pipeline_outer_step_rollup_zip_folder_sync_clock} after parent {@code adaptive_compress} so step deltas are not double‑counted.
 * - {@code FRACTAL_ZIP_PIPELINE_OUTER_ROLLUP_BY_PASS=1} — with rollup on: record each step under {@code pN:label} (which {@code adaptive_compress} pass: literal contest vs fractal wire vs lazy wire). {@see pipeline_outer_step_rollup_zip_folder_end} prints a section per pass so phase‑4.1/4.2/4.3‑style costs are visible **per tournament** instead of only summed across passes.
 * - **Outer rollup labels vs work:** {@code after_outer_prediction_finalize_remainder} is mostly wall waiting on the speculative layered‑prediction child ({@see speculative_outer_prediction_finalize}; {@code benchmarks/predict_outer_layered_probe.php} skips L3 high tier unless {@see fractal_zip::outer_prediction_layer3_high_enabled}, and skips redundant L2 medium probes when L1 shortlists only one codec family; probes use {@see fractal_zip::outer_prediction_probe_max_bytes} / {@see fractal_zip::outer_prediction_timeout_sec}). {@code after_parallel_slow_outer_wave_trials} is the slow‑codec fork wave ({@see parallel_slow_outer_wave_trials}), which only runs when **≥2** of 7z / arc / zpaq are scheduled — single‑codec slow paths stay sequential. {@code after_outer_zpaq_predict_lane} is the **early** zpaq predict lane when layered prediction’s family is zpaq and {@see fractal_zip::defer_slow_predict_lanes_to_parallel_wave_enabled} does **not** apply (the parallel wave would not include zpaq **with** another slow codec, e.g. 7z/arc off or skipped). Tuning wall on prediction without changing outer bytes much: lower {@code FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES} (min clamp 4096 in {@see fractal_zip::outer_prediction_probe_max_bytes}) and/or {@code FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC}; to isolate non‑predict phase‑4 cost, {@code FRACTAL_ZIP_OUTER_PREDICT=0} disables runtime family mapping.
 *
 * Existing knobs ({@code FRACTAL_ZIP_TIME_BUDGET_MS}, outer skips, speed profile) remain authoritative until migrated under this orchestrator.
 */

final class fractal_zip_encode_pipeline {
	public const PHASE_UNPEEL = 1;

	public const PHASE_STREAM_BUILD = 2;

	/** Optional clustering pass — not yet semantic clustering (identity hook). */
	public const PHASE_STREAM_REORDER = 25;

	public const PHASE_TRANSFORMS_AND_MODES = 3;

	public const PHASE_OUTER_CODECS = 4;

	/** @var bool Session: {@see pipeline_wall_timer_zip_folder_begin} … end. */
	private static $wallTimerZipFolderActive = false;

	/** @var float */
	private static $wallZipFolderWallStart = 0.0;

	/** @var float */
	private static $wallSegmentWallStart = 0.0;

	/** @var string Pending segment key until the next checkpoint (e.g. preface, 1, 2, 2.5, 3, 4). */
	private static $wallPendingPhaseKey = 'preface';

	/** @var array<string,float> */
	private static $wallAccumMsByPhaseKey = array();

	/** @var array<string,float> Sum of {@code adaptive_compress} step ms by label across a {@code zip_folder} (optional rollup). */
	private static $outerStepRollupMsByLabel = array();

	/** @var bool Session between {@see pipeline_outer_step_rollup_zip_folder_begin} and {@see pipeline_outer_step_rollup_zip_folder_end}. */
	private static $outerStepRollupSessionActive = false;

	/** @var int adaptive_compress passes counted via {@code outerStep('start')} during rollup session. */
	private static $outerStepRollupAdaptivePasses = 0;

	/** @var float|null Wall clock for {@see pipeline_outer_step_rollup_zip_folder_segment} deltas; set in {@see pipeline_outer_step_rollup_zip_folder_begin}. */
	private static $outerStepRollupZipFolderSegmentLastT = null;

	/** @var float Last {@code phase 4} wall ms from {@see pipeline_wall_timer_zip_folder_end} for diagnostic % lines. */
	private static $wallTimerLastPhase4WallMs = 0.0;

	/** @var float Last sum of checkpoint segments from {@see pipeline_wall_timer_zip_folder_end} (same denominator as phase % in timing summary). */
	private static $wallTimerLastCheckpointSegmentsSumMs = 0.0;

	/** @var bool|null Cached: {@see pcntl_fork_supported_for_parallel_pool} does not change during a request. */
	private static $pcntlForkPoolSupportedCache = null;

	/**
	 * When >0, {@code outer_zpaq_blob} runs inside {@see child_slow_outer_wave_exit} — cap nested {@see parallel_zpaq_outer_method_variant_trials} workers.
	 *
	 * @var int
	 */
	private static $slowOuterWaveZpaqChildNestedDepth = 0;

	public static function pipeline_wall_timer_enabled(): bool {
		$e = getenv('FRACTAL_ZIP_PIPELINE_TIMING');
		if ($e === false || trim((string) $e) === '') {
			return false;
		}
		$v = strtolower(trim((string) $e));

		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	public static function pipeline_wall_timer_zip_folder_begin(): void {
		if (!self::pipeline_wall_timer_enabled()) {
			return;
		}
		self::$wallTimerZipFolderActive = true;
		self::$wallAccumMsByPhaseKey = array();
		self::$wallTimerLastPhase4WallMs = 0.0;
		self::$wallTimerLastCheckpointSegmentsSumMs = 0.0;
		self::$wallPendingPhaseKey = 'preface';
		$now = microtime(true);
		self::$wallZipFolderWallStart = $now;
		self::$wallSegmentWallStart = $now;
	}

	/**
	 * Close the last open segment and print per-phase ms to stderr (CLI; {@code FRACTAL_ZIP_SUPPRESS_HTML=1} skips).
	 */
	public static function pipeline_wall_timer_zip_folder_end(): void {
		if (!self::pipeline_wall_timer_enabled() || !self::$wallTimerZipFolderActive) {
			return;
		}
		self::wall_timer_close_open_segment();
		self::$wallTimerZipFolderActive = false;
		$totalWallMs = (microtime(true) - self::$wallZipFolderWallStart) * 1000.0;
		$sum = 0.0;
		foreach (self::$wallAccumMsByPhaseKey as $v) {
			$sum += $v;
		}
		self::$wallTimerLastPhase4WallMs = isset(self::$wallAccumMsByPhaseKey['4']) ? (float) self::$wallAccumMsByPhaseKey['4'] : 0.0;
		self::$wallTimerLastCheckpointSegmentsSumMs = $sum;
		if (PHP_SAPI !== 'cli' || !is_resource(STDERR)) {
			self::$wallAccumMsByPhaseKey = array();

			return;
		}
		$order = array('preface', '1', '2', '2.5', '3', '4');
		$labels = array(
			'preface' => 'preface (zip_folder entry → first checkpoint)',
			'1' => 'phase 1 unpeel (checkpoint → next)',
			'2' => 'phase 2 stream build (checkpoint → next)',
			'2.5' => 'phase 2.5 stream reorder (checkpoint → next)',
			'3' => 'phase 3 transforms & modes (checkpoint → next)',
			'4' => 'phase 4 outer codecs (checkpoint → zip_folder return; includes adaptive_compress + subprocess wait)',
		);
		fprintf(STDERR, "[fz pipeline timing] wall_total_ms=%.2f segments_tracked_sum_ms=%.2f (should match; small FP drift ok)\n", $totalWallMs, $sum);
		foreach ($order as $k) {
			if (!isset(self::$wallAccumMsByPhaseKey[$k]) || self::$wallAccumMsByPhaseKey[$k] <= 0.0) {
				continue;
			}
			$ms = self::$wallAccumMsByPhaseKey[$k];
			$pct = $sum > 0.0 ? ($ms / $sum) * 100.0 : 0.0;
			$lab = isset($labels[$k]) ? $labels[$k] : $k;
			fprintf(STDERR, "[fz pipeline timing] %s: %.2f ms (%.1f%% of checkpoint segments)\n", $lab, $ms, $pct);
		}
		foreach (self::$wallAccumMsByPhaseKey as $k => $ms) {
			$kn = (string) $k;
			if ($ms <= 0.0 || in_array($kn, $order, true)) {
				continue;
			}
			$pct = $sum > 0.0 ? ($ms / $sum) * 100.0 : 0.0;
			fprintf(STDERR, "[fz pipeline timing] phase_key=%s: %.2f ms (%.1f%% of checkpoint segments)\n", $kn, $ms, $pct);
		}
		self::$wallAccumMsByPhaseKey = array();
	}

	/**
	 * @param int $phase 1|2|3|4|25
	 */
	private static function wall_timer_phase_key_from_const($phase): string {
		if ((int) $phase === self::PHASE_STREAM_REORDER) {
			return '2.5';
		}
		switch ((int) $phase) {
			case self::PHASE_UNPEEL:
				return '1';
			case self::PHASE_STREAM_BUILD:
				return '2';
			case self::PHASE_TRANSFORMS_AND_MODES:
				return '3';
			case self::PHASE_OUTER_CODECS:
				return '4';
			default:
				return '?' . (string) (int) $phase;
		}
	}

	private static function wall_timer_close_open_segment(): void {
		if (!self::$wallTimerZipFolderActive) {
			return;
		}
		$now = microtime(true);
		$deltaMs = ($now - self::$wallSegmentWallStart) * 1000.0;
		$k = self::$wallPendingPhaseKey;
		if (!isset(self::$wallAccumMsByPhaseKey[$k])) {
			self::$wallAccumMsByPhaseKey[$k] = 0.0;
		}
		self::$wallAccumMsByPhaseKey[$k] += $deltaMs;
		self::$wallSegmentWallStart = $now;
	}

	/**
	 * @param int $phase 1|2|3|4|25
	 */
	private static function wall_timer_on_checkpoint($phase): void {
		self::wall_timer_close_open_segment();
		self::$wallPendingPhaseKey = self::wall_timer_phase_key_from_const($phase);
	}

	/** True after begin and before end (for tests / future call sites). */
	public static function wall_timer_session_active(): bool {
		return self::$wallTimerZipFolderActive;
	}

	/**
	 * When true, {@code zip_folder} starts a rollup session; step times in {@code adaptive_compress} are accumulated
	 * (independent of {@code FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG} stderr lines — timings still computed when rollup session is active).
	 */
	public static function pipeline_outer_step_rollup_enabled(): bool {
		$e = getenv('FRACTAL_ZIP_PIPELINE_OUTER_STEP_ROLLUP');
		if ($e !== false && trim((string) $e) !== '') {
			$v = strtolower(trim((string) $e));

			return !($v === '0' || $v === 'false' || $v === 'off' || $v === 'no');
		}

		return self::pipeline_wall_timer_enabled();
	}

	public static function pipeline_outer_step_rollup_session_active(): bool {
		return self::$outerStepRollupSessionActive;
	}

	public static function pipeline_outer_step_rollup_zip_folder_begin(): void {
		if (!self::pipeline_outer_step_rollup_enabled()) {
			return;
		}
		self::$outerStepRollupMsByLabel = array();
		self::$outerStepRollupAdaptivePasses = 0;
		self::$outerStepRollupSessionActive = true;
		self::$outerStepRollupZipFolderSegmentLastT = microtime(true);
	}

	/**
	 * Record wall time since the last zip_folder rollup segment or {@see pipeline_outer_step_rollup_zip_folder_sync_clock} as its own rollup label.
	 * Use {@see pipeline_outer_step_rollup_zip_folder_sync_clock} after in-process {@see fractal_zip::adaptive_compress} calls so those step deltas are not double-counted.
	 */
	public static function pipeline_outer_step_rollup_zip_folder_segment(string $label): void {
		if (!self::$outerStepRollupSessionActive || !self::pipeline_outer_step_rollup_enabled()) {
			return;
		}
		if (self::$outerStepRollupZipFolderSegmentLastT === null) {
			self::$outerStepRollupZipFolderSegmentLastT = microtime(true);

			return;
		}
		$now = microtime(true);
		$d = ($now - self::$outerStepRollupZipFolderSegmentLastT) * 1000.0;
		self::$outerStepRollupZipFolderSegmentLastT = $now;
		self::pipeline_outer_step_rollup_record($label, $d);
	}

	/** Advance the zip_folder rollup segment clock without recording (e.g. after {@see fractal_zip::adaptive_compress} in the parent). */
	public static function pipeline_outer_step_rollup_zip_folder_sync_clock(): void {
		if (!self::$outerStepRollupSessionActive || !self::pipeline_outer_step_rollup_enabled()) {
			return;
		}
		self::$outerStepRollupZipFolderSegmentLastT = microtime(true);
	}

	public static function pipeline_outer_step_rollup_zip_folder_end(): void {
		if (!self::$outerStepRollupSessionActive || !self::pipeline_outer_step_rollup_enabled()) {
			self::$outerStepRollupSessionActive = false;

			return;
		}
		self::$outerStepRollupSessionActive = false;
		if (PHP_SAPI !== 'cli' || !is_resource(STDERR)) {
			self::$outerStepRollupMsByLabel = array();

			return;
		}
		/** @var array<string,float> Step ms merged across {@code pN:label} passes (same keys as aggregate mode). */
		$aggLabelMs = array();
		foreach (self::$outerStepRollupMsByLabel as $k => $ms) {
			if ($ms <= 0.0) {
				continue;
			}
			$ks = (string) $k;
			if (preg_match('/^p\d+:(.+)$/', $ks, $mm)) {
				$lb = (string) $mm[1];
			} else {
				$lb = $ks;
			}
			if (!isset($aggLabelMs[$lb])) {
				$aggLabelMs[$lb] = 0.0;
			}
			$aggLabelMs[$lb] += (float) $ms;
		}
		$sum = 0.0;
		foreach (self::$outerStepRollupMsByLabel as $ms) {
			$sum += $ms;
		}
		$sumMerged = 0.0;
		foreach ($aggLabelMs as $msAgg) {
			$sumMerged += (float) $msAgg;
		}
		$rollupMsPhase3ZipFolder = self::rollup_zip_folder_ms_attributed_to_phase3_wall_timer($aggLabelMs);
		$rollupVsPhase4Wall = $sumMerged - $rollupMsPhase3ZipFolder;
		$byPassMode = self::outer_step_rollup_by_pass_enabled();
		fprintf(
			STDERR,
			"[fz pipeline outer rollup] mode=%s adaptive_compress_passes=%d sum_step_ms=%.2f (%s)\n",
			$byPassMode ? 'by_pass' : 'aggregate',
			self::$outerStepRollupAdaptivePasses,
			$sum,
			$byPassMode ? 'per-pass keys pN:label; sections below' : 'aggregate step deltas across passes'
		);
		if (self::pipeline_wall_timer_enabled() && PHP_SAPI === 'cli' && is_resource(STDERR)) {
			$p4Wall = self::$wallTimerLastPhase4WallMs;
			if ($p4Wall > 0.0) {
				$gap = $p4Wall - $rollupVsPhase4Wall;
				$gapNote = (abs($gap) <= max(8.0, 0.015 * $p4Wall))
					? 'rollup≈phase 4 wall (outerStep + zip_folder segments after excluding phase-3 shell labels)'
					: 'gap = phase 4 wall minus rollup (remaining: choose_smallest prep before outerStep, wire shootout PHP between probes and full outer, fork parent wait, PHP after last outerStep until return, or other unlabeled phase 4 gaps)';
				fprintf(
					STDERR,
					"[fz pipeline outer rollup] phase_4_wall_ms=%.2f rollup_sum_for_phase4_gap_ms=%.2f gap_ms=%.2f — %s",
					$p4Wall,
					$rollupVsPhase4Wall,
					$gap,
					$gapNote
				);
				if ($rollupMsPhase3ZipFolder > 0.5) {
					fprintf(
						STDERR,
						" [excluded %.2f ms phase-3 zip_folder rollup; merged rollup_sum=%.2f]",
						$rollupMsPhase3ZipFolder,
						$sumMerged
					);
				}
				fprintf(STDERR, "\n");
			}
		}
		$preferredOrder = array(
			'zip_folder_legacy_markers_multipass_encode_collect_literals',
			'zip_folder_parallel_overlap_literal_wire_fast_lens',
			'zip_folder_parallel_wire_fast_outer_lens_fork_wait',
			'zip_folder_wire_fast_lens_pick_before_full_outer',
			'zip_folder_finalize_write_payload_trailer',
			'zip_folder_fzc_file_put_contents',
			'zip_folder_unified_stream_maybe_freearc_native_compare',
			'zip_folder_unified_stream_maybe_zpaq_native_compare',
			'zip_folder_bundle_only_maybe_zpaq_native_compare',
			'choose_smallest_phase4_encode_raw_and_literal_jobs',
			'choose_smallest_parallel_literal_outer_trials_full',
			'choose_smallest_parallel_literal_outer_trials_fast',
			'adaptive_compress_outer_fast_codec_tier',
			'adaptive_compress_prediction_fork_discard_finalize',
			'after_gzip_baseline',
			'before_zstd_outer',
			'after_zstd_outer',
			'after_zstd_brotli_skip_gate',
			'after_brotli_first_outer',
			'after_brotli_outer_complete',
			'after_fast_codec_merge',
			'folder_wire_fast_shootout_complete',
			'before_outer_xz',
			'after_outer_xz',
			'skipped_outer_xz',
			'before_outer_prediction_finalize_remainder',
			'after_outer_prediction_finalize_remainder',
			'before_slow_outer_sweep_gates',
			'after_slow_outer_sweep_gates',
			'before_outer_ratchet',
			'after_outer_ratchet',
			'before_brotli_medium_outer',
			'after_brotli_medium_outer',
			'before_brotli_full_outer',
			'after_brotli_full_outer',
			'before_outer_zpaq_predict_lane',
			'after_outer_zpaq_predict_lane',
			'before_outer_7z_predict_lane',
			'after_outer_7z_predict_lane',
			'before_outer_arc_predict_lane',
			'after_outer_arc_predict_lane',
			'before_outer_7z_huge_once',
			'after_outer_7z_huge_once',
			'skipped_outer_7z_huge_once',
			'before_outer_arc_before_7z',
			'after_outer_arc_before_7z',
			'skipped_outer_arc_before_7z',
			'before_parallel_slow_outer_wave',
			'after_parallel_slow_outer_wave_trials',
			'after_parallel_slow_outer_wave',
			'before_outer_7z_best_sweep',
			'after_outer_7z_best_sweep',
			'skipped_outer_7z_best_sweep',
			'before_outer_arc_after_7z',
			'after_outer_arc_after_7z',
			'skipped_outer_arc_after_7z',
			'before_outer_zpaq',
			'after_outer_zpaq',
			'skipped_outer_zpaq',
			'after_slow_outer_pass_complete',
		);
		if ($byPassMode) {
			/** @var array<int, array<string, float>> */
			$byPass = array();
			foreach (self::$outerStepRollupMsByLabel as $k => $ms) {
				if ($ms <= 0.0 || !preg_match('/^p(\d+):(.+)$/', (string) $k, $mm)) {
					continue;
				}
				$pn = (int) $mm[1];
				$lb = (string) $mm[2];
				if (!isset($byPass[$pn])) {
					$byPass[$pn] = array();
				}
				$byPass[$pn][$lb] = ($byPass[$pn][$lb] ?? 0.0) + (float) $ms;
			}
			$passes = array_keys($byPass);
			sort($passes, SORT_NUMERIC);
			foreach ($passes as $pn) {
				$row = $byPass[$pn];
				$sub = 0.0;
				foreach ($row as $v) {
					$sub += $v;
				}
				fprintf(STDERR, "[fz pipeline outer rollup] --- adaptive_compress pass %d  sum_step_ms=%.2f ---\n", $pn, $sub);
				$printedPass = array();
				foreach ($preferredOrder as $lb) {
					if (!isset($row[$lb]) || $row[$lb] <= 0.0) {
						continue;
					}
					$m = $row[$lb];
					$pct = $sub > 0.0 ? ($m / $sub) * 100.0 : 0.0;
					fprintf(STDERR, "[fz pipeline outer rollup] %-42s %9.2f ms (%5.1f%%)\n", $lb . ':', $m, $pct);
					$printedPass[$lb] = true;
				}
				$restP = array_keys($row);
				sort($restP, SORT_STRING);
				foreach ($restP as $lb) {
					if (isset($printedPass[$lb])) {
						continue;
					}
					if ($row[$lb] <= 0.0) {
						continue;
					}
					$m = $row[$lb];
					$pct = $sub > 0.0 ? ($m / $sub) * 100.0 : 0.0;
					fprintf(STDERR, "[fz pipeline outer rollup] %-42s %9.2f ms (%5.1f%%)\n", $lb . ':', $m, $pct);
				}
			}
		} else {
			$printed = array();
			foreach ($preferredOrder as $lb) {
				if (!isset(self::$outerStepRollupMsByLabel[$lb]) || self::$outerStepRollupMsByLabel[$lb] <= 0.0) {
					continue;
				}
				$ms = self::$outerStepRollupMsByLabel[$lb];
				$pct = $sum > 0.0 ? ($ms / $sum) * 100.0 : 0.0;
				fprintf(STDERR, "[fz pipeline outer rollup] %-42s %9.2f ms (%5.1f%%)\n", $lb . ':', $ms, $pct);
				$printed[$lb] = true;
			}
			$rest = array_keys(self::$outerStepRollupMsByLabel);
			sort($rest, SORT_STRING);
			foreach ($rest as $lb) {
				if (isset($printed[$lb])) {
					continue;
				}
				$ms = self::$outerStepRollupMsByLabel[$lb];
				if ($ms <= 0.0) {
					continue;
				}
				$pct = $sum > 0.0 ? ($ms / $sum) * 100.0 : 0.0;
				fprintf(STDERR, "[fz pipeline outer rollup] %-42s %9.2f ms (%5.1f%%)\n", $lb . ':', $ms, $pct);
			}
		}
		self::pipeline_outer_phase4_diagnostic_stderr($aggLabelMs, $rollupMsPhase3ZipFolder);
		self::$outerStepRollupMsByLabel = array();
		self::$outerStepRollupAdaptivePasses = 0;
		self::$outerStepRollupZipFolderSegmentLastT = null;
	}

	/**
	 * Rollup labels tied to zip_folder segments that run **before** the phase 4 ({@code PHASE_OUTER_CODECS}) checkpoint in the parent:
	 * their milliseconds are accumulated into phase 3 wall time, not {@code wallAccumMsByPhaseKey['4']}.
	 *
	 * @return list<string>
	 */
	private static function rollup_zip_folder_wall_timer_phase3_only_labels(): array {
		return array(
			'zip_folder_legacy_markers_multipass_encode_collect_literals',
			'zip_folder_parallel_overlap_literal_wire_fast_lens',
		);
	}

	/**
	 * @param array<string,float> $aggLabelMs
	 */
	private static function rollup_zip_folder_ms_attributed_to_phase3_wall_timer(array $aggLabelMs): float {
		$t = 0.0;
		foreach (self::rollup_zip_folder_wall_timer_phase3_only_labels() as $lb) {
			if (isset($aggLabelMs[$lb])) {
				$t += (float) $aggLabelMs[$lb];
			}
		}

		return $t;
	}

	/**
	 * Buckets merged outer-step labels into phase **4.1** (fast codecs + layered prediction overlap / finalize),
	 * **4.2** (sweep gates + ratchet + medium Q3 brotli + early predict lanes),
	 * **4.3** (everything else with rollup ms — slow 7z/arc/zpaq sweeps, parallel slow wave, zip_folder segments, full brotli when run — not “brotli only”).
	 *
	 * @param array<string,float> $aggLabelMs
	 * @param float $rollupMsPhase3ZipFolder ms merged under {@see rollup_zip_folder_wall_timer_phase3_only_labels} (phase 3 wall; subtract when comparing buckets to phase 4 wall)
	 */
	private static function pipeline_outer_phase4_diagnostic_stderr(array $aggLabelMs, $rollupMsPhase3ZipFolder = 0.0): void {
		if (!self::pipeline_wall_timer_enabled() || PHP_SAPI !== 'cli' || !is_resource(STDERR)) {
			return;
		}
		$rollupMsPhase3ZipFolder = (float) $rollupMsPhase3ZipFolder;
		$s41 = 0.0;
		$s42 = 0.0;
		$s43 = 0.0;
		foreach ($aggLabelMs as $lb => $ms) {
			if ($ms <= 0.0) {
				continue;
			}
			switch (self::outer_rollup_phase4_bucket_for_label((string) $lb)) {
				case 1:
					$s41 += $ms;
					break;
				case 2:
					$s42 += $ms;
					break;
				default:
					$s43 += $ms;
			}
		}
		$tierSum = $s41 + $s42 + $s43;
		if ($tierSum <= 0.0) {
			return;
		}
		$wall = self::$wallTimerLastPhase4WallMs;
		$segSum = self::$wallTimerLastCheckpointSegmentsSumMs;
		$pct4ofSeg = $segSum > 0.0 ? ($wall / $segSum) * 100.0 : 0.0;
		fprintf(STDERR, "[fz pipeline phase 4 breakdown] 4 outer codecs (wall): %.2f ms (%.1f%% of checkpoint segments)\n", $wall, $pct4ofSeg);
		$den = $wall > 0.0 ? $wall : $tierSum;
		$tierSumVsPhase4Wall = $tierSum - $rollupMsPhase3ZipFolder;
		$rollupGap = $wall > 0.0 ? ($wall - $tierSumVsPhase4Wall) : 0.0;
		fprintf(STDERR, "[fz pipeline phase 4 breakdown] phase_4_wall_ms=%.2f rollup_bucketed_sum_ms=%.2f", $wall, $tierSum);
		if ($rollupMsPhase3ZipFolder > 0.5) {
			fprintf(STDERR, " (phase-4 comparable: %.2f ms)", $tierSumVsPhase4Wall);
		}
		if ($wall > 0.0 && abs($rollupGap) > max(1.0, 0.02 * $wall)) {
			fprintf(STDERR, " gap_vs_wall_ms=%.2f (wall not fully in rollup labels — see gap line above)", $rollupGap);
		}
		fprintf(STDERR, "\n");
		$zpaqRollupMs = 0.0;
		foreach ($aggLabelMs as $lb => $ms) {
			if ($ms <= 0.0) {
				continue;
			}
			if (strpos((string) $lb, 'zpaq') !== false) {
				$zpaqRollupMs += (float) $ms;
			}
		}
		if ($zpaqRollupMs > 0.0) {
			$pz = ($wall > 0.0) ? ($zpaqRollupMs / $wall) * 100.0 : 0.0;
			fprintf(
				STDERR,
				"[fz pipeline phase 4 breakdown] rollup_ms with 'zpaq' in step label (predict lane + main sweep, etc.): %.2f ms (%.1f%% of phase 4 wall; subset of rollup, not extra wall)\n",
				$zpaqRollupMs,
				$pz
			);
		}
		$p41 = ($s41 / $den) * 100.0;
		$p42 = ($s42 / $den) * 100.0;
		$p43 = ($s43 / $den) * 100.0;
		fprintf(STDERR, "[fz pipeline phase 4 breakdown] 4.1 fast probes (gzip … prediction finalize): %.2f ms (%.1f%% of phase 4 wall)\n", $s41, $p41);
		fprintf(STDERR, "[fz pipeline phase 4 breakdown] 4.2 gates … ratchet … medium brotli Q3 … early predict lanes: %.2f ms (%.1f%% of phase 4 wall)\n", $s42, $p42);
		fprintf(STDERR, "[fz pipeline phase 4 breakdown] 4.3 slow tournament + zip_folder shell (catch‑all: zpaq/7z/arc sweeps, parallel slow wave, full brotli when run, disk): %.2f ms (%.1f%% of phase 4 wall)\n", $s43, $p43);
	}

	/** @return int 1 = tier 4.1, 2 = 4.2, 3 = 4.3 */
	private static function outer_rollup_phase4_bucket_for_label(string $lb): int {
		static $p41 = null;
		static $p42 = null;
		if ($p41 === null) {
			$p41 = array_flip(array(
				'after_gzip_baseline',
				'before_zstd_outer',
				'after_zstd_outer',
				'after_zstd_brotli_skip_gate',
				'after_brotli_first_outer',
				'after_brotli_outer_complete',
				'after_fast_codec_merge',
				'folder_wire_fast_shootout_complete',
				'before_outer_xz',
				'after_outer_xz',
				'skipped_outer_xz',
				'before_outer_prediction_finalize_remainder',
				'after_outer_prediction_finalize_remainder',
			));
			$p42 = array_flip(array(
				'before_slow_outer_sweep_gates',
				'after_slow_outer_sweep_gates',
				'before_outer_ratchet',
				'after_outer_ratchet',
				'before_brotli_medium_outer',
				'after_brotli_medium_outer',
				'before_outer_zpaq_predict_lane',
				'after_outer_zpaq_predict_lane',
				'before_outer_7z_predict_lane',
				'after_outer_7z_predict_lane',
				'before_outer_arc_predict_lane',
				'after_outer_arc_predict_lane',
			));
		}
		if (isset($p41[$lb])) {
			return 1;
		}
		if (isset($p42[$lb])) {
			return 2;
		}

		return 3;
	}

	/**
	 * Called from {@see adaptive_compress} step closure after computing {@code stepMs}.
	 *
	 * @param float $stepMs
	 */
	public static function pipeline_outer_step_rollup_record(string $label, $stepMs): void {
		if (!self::$outerStepRollupSessionActive || !self::pipeline_outer_step_rollup_enabled()) {
			return;
		}
		if ($label === 'start') {
			self::$outerStepRollupAdaptivePasses++;

			return;
		}
		$stepMs = (float) $stepMs;
		$key = $label;
		if (self::outer_step_rollup_by_pass_enabled()) {
			$key = 'p' . (string) self::$outerStepRollupAdaptivePasses . ':' . $label;
		}
		if (!isset(self::$outerStepRollupMsByLabel[$key])) {
			self::$outerStepRollupMsByLabel[$key] = 0.0;
		}
		self::$outerStepRollupMsByLabel[$key] += $stepMs;
	}

	/** When {@code FRACTAL_ZIP_PIPELINE_OUTER_ROLLUP_BY_PASS=1}, rollup keys are {@code pN:step_label} (N = current {@code adaptive_compress} pass). */
	private static function outer_step_rollup_by_pass_enabled(): bool {
		$e = getenv('FRACTAL_ZIP_PIPELINE_OUTER_ROLLUP_BY_PASS');
		if ($e === false || trim((string) $e) === '') {
			return false;
		}
		$v = strtolower(trim((string) $e));

		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	public static function orchestration_enabled(): bool {
		return getenv('FRACTAL_ZIP_PIPELINE') === '1';
	}

	public static function trace_enabled(): bool {
		return getenv('FRACTAL_ZIP_PIPELINE_TRACE') === '1'
			|| getenv('FRACTAL_ZIP_PIPELINE') === '1';
	}

	/** @var bool One-shot CLI env defaults for fork parallelism (jobs count, parallel outer overlap). */
	private static $cliParallelDefaultsApplied = false;

	/**
	 * Under PHP CLI, when {@code pcntl_fork} is available: if parallel-related env vars are unset or empty, set
	 * {@code FRACTAL_ZIP_PIPELINE_PARALLEL=1}, {@code FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER=1}, and
	 * {@code FRACTAL_ZIP_PIPELINE_JOBS} to a detected CPU cap ({@see detect_cpu_worker_default}), and {@code FRACTAL_ZIP_PIPELINE_INNER_LPT=1}
	 * so literal-bundle trials schedule heavier inners first (parallel overlap benefit). Idempotent; does not override explicit user values.
	 */
	public static function bootstrap_cli_parallel_defaults_if_cli(): void {
		if (self::$cliParallelDefaultsApplied) {
			return;
		}
		self::$cliParallelDefaultsApplied = true;
		if (PHP_SAPI !== 'cli') {
			return;
		}
		if (!self::pcntl_fork_supported_for_parallel_pool()) {
			return;
		}
		$p = getenv('FRACTAL_ZIP_PIPELINE_PARALLEL');
		if ($p === false || trim((string) $p) === '') {
			putenv('FRACTAL_ZIP_PIPELINE_PARALLEL=1');
		}
		$j = getenv('FRACTAL_ZIP_PIPELINE_JOBS');
		if ($j === false || trim((string) $j) === '') {
			putenv('FRACTAL_ZIP_PIPELINE_JOBS=' . (string) self::detect_cpu_worker_default());
		}
		$po = getenv('FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER');
		if ($po === false || trim((string) $po) === '') {
			putenv('FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER=1');
		}
		$sw = getenv('FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE');
		if ($sw === false || trim((string) $sw) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE=1');
		}
		$ilp = getenv('FRACTAL_ZIP_PIPELINE_INNER_LPT');
		if ($ilp === false || trim((string) $ilp) === '') {
			putenv('FRACTAL_ZIP_PIPELINE_INNER_LPT=1');
		}
		$blg = getenv('FRACTAL_ZIP_PARALLEL_BROTLI_LGWIN_WAVE');
		if ($blg === false || trim((string) $blg) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_BROTLI_LGWIN_WAVE=1');
		}
		$ph = getenv('FRACTAL_ZIP_PARALLEL_HUGE_ARC_WAVE');
		if ($ph === false || trim((string) $ph) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_HUGE_ARC_WAVE=1');
		}
		$pz = getenv('FRACTAL_ZIP_PARALLEL_ZSTD_FZB_ALT_WAVE');
		if ($pz === false || trim((string) $pz) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_ZSTD_FZB_ALT_WAVE=1');
		}
		$pop = getenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICTION');
		if ($pop === false || trim((string) $pop) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICTION=1');
		}
		$pzm = getenv('FRACTAL_ZIP_PARALLEL_ZPAQ_OUTER_METHOD_WAVE');
		if ($pzm === false || trim((string) $pzm) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_ZPAQ_OUTER_METHOD_WAVE=1');
		}
		$zwt = getenv('FRACTAL_ZIP_ZPAQ_OUTER_WORK_SYS_TMP');
		if ($zwt === false || trim((string) $zwt) === '') {
			putenv('FRACTAL_ZIP_ZPAQ_OUTER_WORK_SYS_TMP=1');
		}
		$pfl = getenv('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_FRACTAL_LAZY');
		if ($pfl === false || trim((string) $pfl) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_FRACTAL_LAZY=1');
		}
		$pll = getenv('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_LEGACY_LITERAL_FRACTAL_LAZY');
		if ($pll === false || trim((string) $pll) === '') {
			putenv('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_LEGACY_LITERAL_FRACTAL_LAZY=1');
		}
	}

	private static function pcntl_fork_supported_for_parallel_pool(): bool {
		if (self::$pcntlForkPoolSupportedCache !== null) {
			return self::$pcntlForkPoolSupportedCache;
		}

		return self::$pcntlForkPoolSupportedCache = (PHP_OS_FAMILY !== 'Windows'
			&& function_exists('pcntl_fork')
			&& function_exists('pcntl_wait')
			&& function_exists('pcntl_waitpid'));
	}

	/**
	 * Best-effort logical CPU count for fork-pool workers (2–32).
	 */
	private static function detect_cpu_worker_default(): int {
		$o = @shell_exec('nproc 2>/dev/null');
		if ($o !== null && $o !== '') {
			$t = trim((string) $o);
			if ($t !== '' && ctype_digit($t)) {
				$n = (int) $t;
				if ($n > 0) {
					return max(2, min(32, $n));
				}
			}
		}
		if (@is_readable('/proc/cpuinfo')) {
			$s = @file_get_contents('/proc/cpuinfo');
			if (is_string($s) && $s !== '' && preg_match_all('/^processor[\t ]*:/m', $s, $mm) > 0) {
				$n = count($mm[0]);
				if ($n > 0) {
					return max(2, min(32, $n));
				}
			}
		}
		if (PHP_OS_FAMILY === 'Darwin') {
			$o2 = @shell_exec('/usr/sbin/sysctl -n hw.ncpu 2>/dev/null');
			if ($o2 !== null && ctype_digit(trim((string) $o2))) {
				$n = (int) trim((string) $o2);

				return max(2, min(32, max(1, $n)));
			}
		}

		return 8;
	}

	public static function parallel_fork_allowed(): bool {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::pcntl_fork_supported_for_parallel_pool()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PIPELINE_PARALLEL');
		if ($e !== false) {
			$v = strtolower(trim((string) $e));
			if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
				return false;
			}
			if ($v === '1' || $v === 'true' || $v === 'yes' || $v === 'on') {
				return true;
			}
			// Unknown non-empty: off (avoid accidental forks in web SAPI).
			return false;
		}
		// Unset: default on for PHP CLI (benchmarks, fractal_zip_cli), off for CGI / FPM / mod_php.
		return PHP_SAPI === 'cli';
	}

	/**
	 * Speculative overlap of first outer brotli vs zstd ({@see adaptive_compress}). Requires {@see parallel_fork_allowed}.
	 * Default **on** when env unset (CLI Linux + pcntl, matching {@code FRACTAL_ZIP_PIPELINE_PARALLEL}); {@code 0}/{@code off} disables.
	 */
	public static function parallel_adaptive_outer_first_brotli_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}
		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Minimum inner size (bytes) for fork-based outer lanes: prediction overlap, first/huge-probe brotli overlap,
	 * parallel lgwin/zstd-1615/huge-once-arc/slow-outer/zpaq-method waves, literal-bundle variant trials,
	 * layered prediction fork batches (see {@code adaptive_compress}, {@see fractal_zip::outer_zpaq_blob}, {@code benchmarks/predict_outer_layered_probe.php});
	 * below this, callers use synchronous prediction or sequential codec trials (same bytes, less fork overhead).
	 */
	public static function parallel_speculative_outer_min_inner_bytes(): int {
		static $cached = null;
		if ($cached !== null) {
			return $cached;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_OUTER_MIN_INNER_BYTES');
		if ($e !== false && trim((string) $e) !== '') {
			return $cached = max(0, (int) trim((string) $e));
		}

		return $cached = 8192;
	}

	/**
	 * Fork wave for slow outer codecs (7z sweep, arc, zpaq) inside {@see fractal_zip::adaptive_compress}.
	 * Default on under CLI when unset; {@code FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE=0} disables.
	 */
	public static function parallel_slow_outer_wave_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}
		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Legacy {@see fractal_zip::zip_folder}: overlap fractal-wire vs lazy-wire {@code adaptive_compress} (same {@code fractal_zip}
	 * instance snapshot in each child — segment length, multipass, env match the sequential path).
	 */
	public static function parallel_zip_folder_fractal_lazy_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_FRACTAL_LAZY');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}

		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/** Legacy {@see fractal_zip::zip_folder}: literal contest ∥ fractal ∥ lazy when all three apply. */
	public static function parallel_zip_folder_legacy_literal_fractal_lazy_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_LEGACY_LITERAL_FRACTAL_LAZY');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}

		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Sum of {@code strlen(inner)} across literal bundle variant rows (already materialized before parallel pools).
	 *
	 * @param array<mixed> $literalVariants rows from {@see collect_literal_bundle_inner_variants_for_folder}
	 */
	private static function parallel_zip_folder_literal_variant_inner_bytes_total(array $literalVariants): int {
		$sum = 0;
		foreach ($literalVariants as $row) {
			if (is_array($row) && isset($row['inner']) && is_string($row['inner'])) {
				$sum += strlen($row['inner']);
			}
		}

		return $sum;
	}

	/**
	 * When {@code FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_MIN_WIRE_BYTES} is a positive integer and the combined estimate is strictly below it, skip fork-based zip_folder fast-tier pools.
	 *
	 * @param int $literalRelatedBytes literal inner length(s): contest winner blob for wire_lens, or summed variant inners for overlap/stagger
	 */
	private static function parallel_zip_folder_fast_tier_skip_fork_for_micro_combined_payload(int $literalRelatedBytes, string $fractalBlob, string $lazyBlob): bool {
		$e = getenv('FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_MIN_WIRE_BYTES');
		if ($e === false || trim((string) $e) === '') {
			return false;
		}
		$min = (int) trim((string) $e);
		if ($min <= 0) {
			return false;
		}
		$sum = $literalRelatedBytes + strlen($fractalBlob) + strlen($lazyBlob);

		return $sum < $min;
	}

	/**
	 * Whether legacy {@see fractal_zip::zip_folder} should invoke overlap then stagger fast-tier pools.
	 * False when forks are off, {@code FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_FRACTAL_LAZY} is off, there are no literal variants, or {@code FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_MIN_WIRE_BYTES} treats the combined payload as too small (same rules as the pools themselves).
	 *
	 * @param array<mixed> $literalVariants rows from {@see collect_literal_bundle_inner_variants_for_folder}
	 */
	public static function parallel_zip_folder_literal_overlap_stagger_fast_lens_eligible(array $literalVariants, string $fractalBlob, string $lazyBlob): bool {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::parallel_fork_allowed() || !self::parallel_zip_folder_fractal_lazy_enabled()) {
			return false;
		}
		if ($literalVariants === array()) {
			return false;
		}

		return !self::parallel_zip_folder_fast_tier_skip_fork_for_micro_combined_payload(
			self::parallel_zip_folder_literal_variant_inner_bytes_total($literalVariants),
			$fractalBlob,
			$lazyBlob
		);
	}

	/**
	 * Parallel fork pool for FZB/ultra brotli {@code -w} refinement (w=16, 20, 22, 24) in {@see fractal_zip::adaptive_compress}.
	 * Default on under CLI when unset; {@code FRACTAL_ZIP_PARALLEL_BROTLI_LGWIN_WAVE=0} disables.
	 */
	public static function parallel_brotli_lgwin_refinement_wave_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_BROTLI_LGWIN_WAVE');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}
		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Parallel {@code zpaq add … -method N} ladder inside {@see fractal_zip::outer_zpaq_blob}.
	 * Default on under CLI when unset; {@code FRACTAL_ZIP_PARALLEL_ZPAQ_OUTER_METHOD_WAVE=0} disables.
	 */
	public static function parallel_zpaq_outer_method_wave_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_ZPAQ_OUTER_METHOD_WAVE');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}
		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Overlap {@code outer_7z_lzma2_blob} huge-once with arc-before-7z ({@see fractal_zip::adaptive_compress} mid tier).
	 * Only used when bytes-first ({@code FRACTAL_ZIP_SPEED} off): speed mode may change {@code skipSlowOuterSweep} between the two sequential arms.
	 */
	public static function parallel_huge_once_arc_before_wave_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_HUGE_ARC_WAVE');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}
		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Fork {@code ji=0} → {@code outer_7z_lzma2_blob}, {@code ji=1} → {@code outer_arc_blob}. Merge in parent: **0 then 1** (matches sequential).
	 *
	 * @param bool $hugeArcWaveGatePassed When **true**, skips {@see parallel_fork_allowed}/{@see parallel_huge_once_arc_before_wave_enabled}
	 *        (caller already proved gates — avoids duplicate getenv/fork checks).
	 *
	 * @return array{0: string|null, 1: string|null}|null null on fork/setup failure or when both workers produce nothing; per-slot failure does not abort the sibling (both children always reaped).
	 */
	public static function parallel_huge_once_arc_before_wave_trials(string $innerBlob, bool $hugeArcWaveGatePassed = false): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!$hugeArcWaveGatePassed && (!self::parallel_fork_allowed() || !self::parallel_huge_once_arc_before_wave_enabled())) {
			return null;
		}
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_huge_arc_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$innerPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
		if (@file_put_contents($innerPath, $innerBlob) === false) {
			@rmdir($td);

			return null;
		}
		if (!function_exists('pcntl_fork')) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$p0 = pcntl_fork();
		if ($p0 === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if ($p0 === 0) {
			self::child_huge_once_arc_before_wave_exit($td, 0, $innerPath);
		}
		$p1 = pcntl_fork();
		if ($p1 === -1) {
			if (function_exists('posix_kill')) {
				@posix_kill($p0, defined('SIGKILL') ? SIGKILL : 9);
			}
			$st = 0;
			pcntl_waitpid($p0, $st);
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if ($p1 === 0) {
			self::child_huge_once_arc_before_wave_exit($td, 1, $innerPath);
		}
		$results = array(0 => null, 1 => null);
		foreach (array($p0, $p1) as $idx => $cpid) {
			$st = 0;
			pcntl_waitpid($cpid, $st);
			$ji = $idx;
			if (!pcntl_wifexited($st) || pcntl_wexitstatus($st) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta)) {
				continue;
			}
			if ((int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			if (!is_file($bpath)) {
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml !== strlen($blob) || $ml <= 0) {
				continue;
			}
			$results[$ji] = $blob;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		if ($results[0] === null && $results[1] === null) {
			return null;
		}

		return $results;
	}

	private static function child_huge_once_arc_before_wave_exit(string $td, int $ji, string $innerPath): void {
		try {
			$inner = (string) @file_get_contents($innerPath);
			if ($ji !== 0 && $ji !== 1) {
				exit(1);
			}
			$fz = new fractal_zip(256, false, false, null, false);
			$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			$kind = ($ji === 0) ? 'huge_7z' : 'arc';
			if ($ji === 0) {
				$seven = fractal_zip::seven_zip_executable();
				if ($seven === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_7Z')) {
					@file_put_contents($jp, (string) json_encode(array('ok' => 0, 'kind' => $kind)));

					exit(0);
				}
				$blob = $fz->outer_7z_lzma2_blob($seven, $inner);
				$codec = '7z';
			} else {
				$arc = fractal_zip::freearc_executable();
				if ($arc === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ARC')) {
					@file_put_contents($jp, (string) json_encode(array('ok' => 0, 'kind' => $kind)));

					exit(0);
				}
				$blob = $fz->outer_arc_blob($arc, $inner);
				$codec = 'arc';
			}
			if ($blob === null || $blob === '') {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0, 'kind' => $kind)));

				exit(0);
			}
			$meta = array(
				'ok' => 1,
				'kind' => $kind,
				'codec' => $codec,
				'len' => strlen($blob),
			);
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	/**
	 * Parallel zstd-16 vs zstd-15 for small FZB alt compare ({@see fractal_zip::adaptive_compress}). {@code 0} disables.
	 */
	public static function parallel_zstd_fzb_alt_wave_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_ZSTD_FZB_ALT_WAVE');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}
		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Fork zstd level 16 (ji 0) and 15 (ji 1). Reuse tmp is omitted in children to avoid races.
	 *
	 * @param bool $forkAltWaveGatePassed When **true**, skips {@see parallel_fork_allowed}/{@see parallel_zstd_fzb_alt_wave_enabled}
	 *        (caller already proved gates — avoids duplicate getenv/fork checks).
	 *
	 * @return array{16: string|null, 15: string|null}|null null on fork/setup failure or when both encodes fail; one lane failing does not abort the sibling (both children always reaped).
	 */
	public static function parallel_zstd_fzb_16_and_15_trials(string $innerBlob, bool $forkAltWaveGatePassed = false): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!$forkAltWaveGatePassed && (!self::parallel_fork_allowed() || !self::parallel_zstd_fzb_alt_wave_enabled())) {
			return null;
		}
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zstd1615_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$innerPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
		if (@file_put_contents($innerPath, $innerBlob) === false) {
			@rmdir($td);

			return null;
		}
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'levels.json', (string) json_encode(array(16, 15))) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if (!function_exists('pcntl_fork')) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$p0 = pcntl_fork();
		if ($p0 === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if ($p0 === 0) {
			self::child_zstd_fzb_alt_levels_exit($td, 0, $innerPath);
		}
		$p1 = pcntl_fork();
		if ($p1 === -1) {
			if (function_exists('posix_kill')) {
				@posix_kill($p0, defined('SIGKILL') ? SIGKILL : 9);
			}
			$st = 0;
			pcntl_waitpid($p0, $st);
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if ($p1 === 0) {
			self::child_zstd_fzb_alt_levels_exit($td, 1, $innerPath);
		}
		$results = array(0 => null, 1 => null);
		foreach (array($p0, $p1) as $idx => $cpid) {
			$st = 0;
			pcntl_waitpid($cpid, $st);
			$ji = $idx;
			if (!pcntl_wifexited($st) || pcntl_wexitstatus($st) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta)) {
				continue;
			}
			if ((int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			if (!is_file($bpath)) {
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml !== strlen($blob) || $ml <= 0) {
				continue;
			}
			$results[$ji] = $blob;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		if ($results[0] === null && $results[1] === null) {
			return null;
		}

		return array(
			16 => $results[0],
			15 => $results[1],
		);
	}

	private static function child_zstd_fzb_alt_levels_exit(string $td, int $ji, string $innerPath): void {
		try {
			$inner = (string) @file_get_contents($innerPath);
			if ($ji !== 0 && $ji !== 1) {
				exit(1);
			}
			$lr = @file_get_contents($td . DIRECTORY_SEPARATOR . 'levels.json');
			$levels = is_string($lr) ? json_decode($lr, true) : null;
			if (!is_array($levels) || !isset($levels[$ji])) {
				exit(1);
			}
			$lev = (int) $levels[$ji];
			if ($lev !== 16 && $lev !== 15) {
				exit(1);
			}
			$fz = new fractal_zip(256, false, false, null, false);
			$exe = fractal_zip::zstd_executable();
			$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			if ($exe === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZSTD')) {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0, 'level' => $lev)));

				exit(0);
			}
			$blob = $fz->outer_zstd_blob($exe, $inner, $lev, null);
			if ($blob === null || $blob === '') {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0, 'level' => $lev)));

				exit(0);
			}
			$bp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			$meta = array(
				'ok' => 1,
				'level' => $lev,
				'len' => strlen($blob),
			);
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	/**
	 * Longest-(expected)-processing-time-first ordering for slow-wave fork starts: zpaq, then 7z, then arc.
	 * Merge order in the parent remains fixed ({@see fractal_zip::outer_candidate_beats_current} semantics).
	 *
	 * @param list<'7z'|'arc'|'zpaq'> $jobs
	 * @return list<'7z'|'arc'|'zpaq'>
	 */
	private static function schedule_slow_outer_wave_jobs_lpt(array $jobs): array {
		$rank = array('zpaq' => 0, '7z' => 1, 'arc' => 2);
		usort($jobs, static function ($a, $b) use ($rank): int {
			return ($rank[$a] ?? 99) <=> ($rank[$b] ?? 99);
		});

		return $jobs;
	}

	/**
	 * Run ≥2 slow outer trials in parallel (7z / arc / zpaq). Each job compresses the full inner independently;
	 * parent merges with the same {@see fractal_zip::outer_candidate_beats_current} order as sequential (7z → arc → zpaq).
	 * Fork **dispatch** uses longest-trial-first (typically zpaq, then 7z, then arc) so limited worker pools start expensive work immediately.
	 *
	 * @param bool $slowWaveForkGatePassed When **true**, skips {@see parallel_fork_allowed}/{@see parallel_slow_outer_wave_enabled}
	 *        (caller already proved gates — avoids duplicate getenv/fork checks).
	 *
	 * @param list<'7z'|'arc'|'zpaq'> $kinds
	 * @return array<string, array{blob: string, codec: string, len: int, zpaq: string|null}|null>|null keyed by kind; null only on catastrophic fork/setup failure; otherwise one entry per scheduled codec (null if that worker failed like sequential skip). A crashed or malformed worker no longer aborts the whole pool.
	 */
	public static function parallel_slow_outer_wave_trials(array $kinds, string $innerBlob, int $innerLen, int $bestLenBeforeWave, bool $fzbHuge, bool $slowWaveForkGatePassed = false): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!$slowWaveForkGatePassed && (!self::parallel_fork_allowed() || !self::parallel_slow_outer_wave_enabled())) {
			return null;
		}
		$uniq = array();
		foreach ($kinds as $k) {
			if ($k === '7z' || $k === 'arc' || $k === 'zpaq') {
				$uniq[$k] = true;
			}
		}
		$jobs = array_keys($uniq);
		if (count($jobs) < 2) {
			return null;
		}
		$jobs = self::schedule_slow_outer_wave_jobs_lpt($jobs);
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_slowwave_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$innerPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
		if (@file_put_contents($innerPath, $innerBlob) === false) {
			@rmdir($td);
			return null;
		}
		$ctx = array(
			'innerLen' => $innerLen,
			'bound7z' => $bestLenBeforeWave === PHP_INT_MAX ? PHP_INT_MAX - 1 : max(0, $bestLenBeforeWave - 1),
			'fzbHuge' => $fzbHuge,
		);
		$ctxPath = $td . DIRECTORY_SEPARATOR . 'wave.ctx.json';
		if (@file_put_contents($ctxPath, (string) json_encode($ctx)) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);
			return null;
		}
		$n = count($jobs);
		$next = 0;
		$active = array();
		$results = array();
		$workers = self::max_workers();
		while ($next < $n || count($active) > 0) {
			while (count($active) < $workers && $next < $n) {
				$ji = $next++;
				$kind = $jobs[$ji];
				$pid = pcntl_fork();
				if ($pid === -1) {
					self::terminate_fork_children_and_cleanup($active, $td);
					return null;
				}
				if ($pid === 0) {
					self::child_slow_outer_wave_exit($td, $ji, $kind, $innerPath, $ctxPath);
				}
				$active[$pid] = $ji;
			}
			if (count($active) === 0) {
				break;
			}
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);
				return null;
			}
			$ji = $active[$pid];
			unset($active[$pid]);
			$kindJob = $jobs[$ji];
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				$results[$kindJob] = null;
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			if (!is_file($jpath)) {
				$results[$kindJob] = null;
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta) || !isset($meta['kind'])) {
				$results[$kindJob] = null;
				continue;
			}
			$kindMeta = (string) $meta['kind'];
			if ($kindMeta !== $kindJob) {
				$results[$kindJob] = null;
				continue;
			}
			if ((int) ($meta['ok'] ?? 0) !== 1) {
				$results[$kindJob] = null;
				continue;
			}
			if (!is_file($bpath)) {
				$results[$kindJob] = null;
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$mlen = (int) ($meta['len'] ?? -1);
			if ($mlen !== strlen($blob) || $mlen <= 0) {
				$results[$kindJob] = null;
				continue;
			}
			$codec = isset($meta['codec']) && is_string($meta['codec']) ? $meta['codec'] : $kindJob;
			$zm = $meta['zpaq'] ?? null;
			$results[$kindJob] = array(
				'blob' => $blob,
				'codec' => $codec,
				'len' => $mlen,
				'zpaq' => ($zm !== null && $zm !== '' && is_string($zm)) ? $zm : null,
			);
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		foreach ($jobs as $jk) {
			if (!array_key_exists($jk, $results)) {
				return null;
			}
		}

		return $results;
	}

	/**
	 * Fork pool: legacy {@see fractal_zip::zip_folder} fractal vs lazy {@code adaptive_compress} when both payloads differ.
	 * Each child inherits the same {@code fractal_zip} snapshot as sequential (multipass/segment/env).
	 * A failed lane does not SIGKILL the sibling; {@code null} if either lane is unusable after all children exit.
	 *
	 * @return array{fractal: array{blob: string, codec: string, zpaq: string|null}, lazy: array{blob: string, codec: string, zpaq: string|null}}|null
	 */
	public static function parallel_zip_folder_fractal_lazy_outer_trials(fractal_zip $fz, string $fractalBlob, string $lazyBlob): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::parallel_fork_allowed() || !self::parallel_zip_folder_fractal_lazy_enabled()) {
			return null;
		}
		if ($fractalBlob === $lazyBlob) {
			return null;
		}
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zffl_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$lanes = array(
			array('key' => 'fractal', 'blob' => $fractalBlob),
			array('key' => 'lazy', 'blob' => $lazyBlob),
		);
		$active = array();
		foreach ($lanes as $lane) {
			$pid = pcntl_fork();
			if ($pid === -1) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			if ($pid === 0) {
				self::child_zip_folder_fractal_lazy_exit($td, $lane['key'], $fz, $lane['blob']);
			}
			$active[$pid] = $lane['key'];
		}
		$results = array();
		while (count($active) > 0) {
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$key = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . $key . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . $key . '.out';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			if (!is_file($bpath)) {
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml !== strlen($blob) || $ml < 0) {
				continue;
			}
			$codec = isset($meta['codec']) && is_string($meta['codec']) ? (string) $meta['codec'] : 'store';
			$zm = $meta['zpaq'] ?? null;
			$results[$key] = array(
				'blob' => $blob,
				'codec' => $codec,
				'zpaq' => ($zm !== null && $zm !== '' && is_string($zm)) ? $zm : null,
			);
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		if (!isset($results['fractal'], $results['lazy'])) {
			return null;
		}

		return array(
			'fractal' => $results['fractal'],
			'lazy' => $results['lazy'],
		);
	}

	/**
	 * Fork pool: legacy {@see fractal_zip::zip_folder} fast outer tier only ({@see fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE}), JSON len metadata only (no .out blobs).
	 * A failed lane does not SIGKILL siblings; merge requires fractal (and lazy when wires differ, literal when provided); otherwise {@code null}.
	 *
	 * @return array{litFastLen: int, frFastLen: int, lzFastLen: int}|null  {@code litFastLen} is {@code PHP_INT_MAX} when {@code $literalInner} is empty.
	 */
	public static function parallel_zip_folder_wire_fast_outer_lens(fractal_zip $fz, string $literalInner, string $fractalBlob, string $lazyBlob): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::parallel_fork_allowed() || !self::parallel_zip_folder_fractal_lazy_enabled()) {
			return null;
		}
		$haveLit = ($literalInner !== '');
		$litBytesForFloor = $haveLit ? strlen($literalInner) : 0;
		if (self::parallel_zip_folder_fast_tier_skip_fork_for_micro_combined_payload($litBytesForFloor, $fractalBlob, $lazyBlob)) {
			return null;
		}
		$sameWire = ($fractalBlob === $lazyBlob);
		$lanes = array();
		if ($haveLit) {
			$lanes[] = array('key' => 'literal', 'blob' => $literalInner);
		}
		$lanes[] = array('key' => 'fractal', 'blob' => $fractalBlob);
		if (!$sameWire) {
			$lanes[] = array('key' => 'lazy', 'blob' => $lazyBlob);
		}
		if (count($lanes) < 2) {
			return null;
		}
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zfwf_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$active = array();
		foreach ($lanes as $lane) {
			$pid = pcntl_fork();
			if ($pid === -1) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			if ($pid === 0) {
				self::child_zip_folder_wire_fast_exit($td, $lane['key'], $fz, $lane['blob']);
			}
			$active[$pid] = $lane['key'];
		}
		$results = array();
		while (count($active) > 0) {
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$key = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . $key . '.json';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml < 0) {
				continue;
			}
			$results[$key] = $ml;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		if (!isset($results['fractal'])) {
			return null;
		}
		$frFastLen = $results['fractal'];
		$lzFastLen = $sameWire ? $frFastLen : ($results['lazy'] ?? -1);
		if (!$sameWire && $lzFastLen < 0) {
			return null;
		}
		if ($haveLit) {
			if (!isset($results['literal'])) {
				return null;
			}
			$litFastLen = $results['literal'];
		} else {
			$litFastLen = PHP_INT_MAX;
		}

		return array(
			'litFastLen' => $litFastLen,
			'frFastLen' => $frFastLen,
			'lzFastLen' => $lzFastLen,
		);
	}

	/**
	 * Fork pool: literal inner contest ({@see child_zip_folder_literal_exit}) overlaps fractal/lazy **fast-merge** outers.
	 * Literal JSON includes {@code fast_outer_len}; fractal/lazy JSON-only lens (no .out). Same merge semantics as sequential contest + {@see parallel_zip_folder_wire_fast_outer_lens}.
	 * A failed lane does not SIGKILL siblings; if any required lane is missing after all children exit, returns {@code null} (caller uses sequential path).
	 *
	 * @param array<mixed> $literalVariants
	 * @param array<string, string> $rawFilesByPath
	 *
	 * @return array{litFastLen: int, frFastLen: int, lzFastLen: int, literal_blob: string, literal_tag: string, literal_codec: string, gzip_restore: array<string, string>}|null
	 */
	public static function parallel_zip_folder_overlap_literal_contest_and_wire_fast_lens(
		fractal_zip $fz,
		array $literalVariants,
		array $rawFilesByPath,
		string $fractalBlob,
		string $lazyBlob
	): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::parallel_fork_allowed() || !self::parallel_zip_folder_fractal_lazy_enabled()) {
			return null;
		}
		if ($literalVariants === array()) {
			return null;
		}
		if (self::parallel_zip_folder_fast_tier_skip_fork_for_micro_combined_payload(
			self::parallel_zip_folder_literal_variant_inner_bytes_total($literalVariants),
			$fractalBlob,
			$lazyBlob
		)) {
			return null;
		}
		$sameWire = ($fractalBlob === $lazyBlob);
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zfol_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$payload = serialize(array('variants' => $literalVariants, 'raw' => $rawFilesByPath));
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'literal_inputs.ser', $payload) === false) {
			@rmdir($td);

			return null;
		}
		$active = array();
		$pLit = pcntl_fork();
		if ($pLit === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if ($pLit === 0) {
			self::child_zip_folder_literal_exit($td, $fz);
		}
		$active[$pLit] = 'literal';

		$pFr = pcntl_fork();
		if ($pFr === -1) {
			self::terminate_fork_children_and_cleanup($active, $td);

			return null;
		}
		if ($pFr === 0) {
			self::child_zip_folder_wire_fast_exit($td, 'fractal', $fz, $fractalBlob);
		}
		$active[$pFr] = 'fractal';

		if (!$sameWire) {
			$pLz = pcntl_fork();
			if ($pLz === -1) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			if ($pLz === 0) {
				self::child_zip_folder_wire_fast_exit($td, 'lazy', $fz, $lazyBlob);
			}
			$active[$pLz] = 'lazy';
		}

		$literalRow = null;
		$frFastLen = null;
		$lzFastLen = null;
		while (count($active) > 0) {
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$kind = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			if ($kind === 'literal') {
				$jpath = $td . DIRECTORY_SEPARATOR . 'literal.json';
				$bpath = $td . DIRECTORY_SEPARATOR . 'literal.out';
				if (!is_file($jpath) || !is_file($bpath)) {
					continue;
				}
				$meta = json_decode((string) @file_get_contents($jpath), true);
				if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
					continue;
				}
				$blob = (string) @file_get_contents($bpath);
				$ml = (int) ($meta['len'] ?? -1);
				if ($ml !== strlen($blob) || $ml < 0) {
					continue;
				}
				$fol = (int) ($meta['fast_outer_len'] ?? -1);
				if ($fol < 0) {
					continue;
				}
				$gb = isset($meta['gzip_restore_b64']) && is_string($meta['gzip_restore_b64']) ? $meta['gzip_restore_b64'] : '';
				$grRaw = @base64_decode($gb, true);
				$gr = ($grRaw !== false && $grRaw !== '') ? @unserialize($grRaw) : array();
				if (!is_array($gr)) {
					$gr = array();
				}
				/** @var array<string, string> $grSafe */
				$grSafe = array();
				foreach ($gr as $gk => $gv) {
					if (is_string($gk) && is_string($gv)) {
						$grSafe[$gk] = $gv;
					}
				}
				$literalRow = array(
					'blob' => $blob,
					'tag' => isset($meta['tag']) && is_string($meta['tag']) ? $meta['tag'] : '',
					'codec' => isset($meta['codec']) && is_string($meta['codec']) ? $meta['codec'] : 'store',
					'gzip_restore' => $grSafe,
					'fast_outer_len' => $fol,
				);
				continue;
			}
			$key = ($kind === 'fractal' || $kind === 'lazy') ? $kind : '';
			if ($key === '') {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . $key . '.json';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			$wl = (int) ($meta['len'] ?? -1);
			if ($wl < 0) {
				continue;
			}
			if ($kind === 'fractal') {
				$frFastLen = $wl;
			} else {
				$lzFastLen = $wl;
			}
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		if ($literalRow === null || $frFastLen === null) {
			return null;
		}
		if ($sameWire) {
			$lzFastLen = $frFastLen;
		} elseif ($lzFastLen === null) {
			return null;
		}

		return array(
			'litFastLen' => $literalRow['fast_outer_len'],
			'frFastLen' => $frFastLen,
			'lzFastLen' => $lzFastLen,
			'literal_blob' => $literalRow['blob'],
			'literal_tag' => $literalRow['tag'],
			'literal_codec' => $literalRow['codec'],
			'gzip_restore' => $literalRow['gzip_restore'],
		);
	}

	/**
	 * Parent runs {@see fractal_zip::choose_smallest_adaptive_literal_inner_or_raw_escaped} while fractal/lazy fast-merge probes run in children; then literal fast-merge in parent.
	 * Used when full overlap ({@see parallel_zip_folder_overlap_literal_contest_and_wire_fast_lens}) is unavailable; beats sequential contest → parallel wire lens when contest wall time overlaps wire probes.
	 * If a wire child fails, recomputes only missing fractal/lazy lens in the parent (same bytes as sequential). Catastrophic wait/pid errors invalidate wire results and recompute both.
	 *
	 * @param array<mixed> $literalVariants
	 * @param array<string, string> $rawFilesByPath
	 *
	 * @return array{litFastLen: int, frFastLen: int, lzFastLen: int, literal_blob: string, literal_tag: string, literal_codec: string, gzip_restore: array<string, string>}|null
	 */
	public static function parallel_zip_folder_stagger_literal_contest_fr_lazy_fast_lens(
		fractal_zip $fz,
		array $literalVariants,
		array $rawFilesByPath,
		string $fractalBlob,
		string $lazyBlob
	): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::parallel_fork_allowed() || !self::parallel_zip_folder_fractal_lazy_enabled()) {
			return null;
		}
		if ($literalVariants === array()) {
			return null;
		}
		if (self::parallel_zip_folder_fast_tier_skip_fork_for_micro_combined_payload(
			self::parallel_zip_folder_literal_variant_inner_bytes_total($literalVariants),
			$fractalBlob,
			$lazyBlob
		)) {
			return null;
		}
		$sameWire = ($fractalBlob === $lazyBlob);
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zfst_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$active = array();
		$pFr = pcntl_fork();
		if ($pFr === -1) {
			@rmdir($td);

			return null;
		}
		if ($pFr === 0) {
			self::child_zip_folder_wire_fast_exit($td, 'fractal', $fz, $fractalBlob);
		}
		$active[$pFr] = 'fractal';

		if (!$sameWire) {
			$pLz = pcntl_fork();
			if ($pLz === -1) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			if ($pLz === 0) {
				self::child_zip_folder_wire_fast_exit($td, 'lazy', $fz, $lazyBlob);
			}
			$active[$pLz] = 'lazy';
		}

		try {
			list($literalBlob, $literalTag) = $fz->choose_smallest_adaptive_literal_inner_or_raw_escaped($literalVariants, $rawFilesByPath);
		} catch (\Throwable $e) {
			self::terminate_fork_children_and_cleanup($active, $td);
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$literalCodec = fractal_zip::$last_written_container_codec;
		$grRaw = $fz->fractal_member_gzip_disk_restore;
		if (!is_array($grRaw)) {
			$grRaw = array();
		}
		/** @var array<string, string> $gzipRestore */
		$gzipRestore = array();
		foreach ($grRaw as $gk => $gv) {
			if (is_string($gk) && is_string($gv)) {
				$gzipRestore[$gk] = $gv;
			}
		}

		$frFastLen = null;
		$lzFastLen = null;
		while (count($active) > 0) {
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);
				$frFastLen = null;
				$lzFastLen = null;
				break;
			}
			$kind = $active[$pid];
			unset($active[$pid]);
			if ($kind !== 'fractal' && $kind !== 'lazy') {
				self::terminate_fork_children_and_cleanup($active, $td);
				$frFastLen = null;
				$lzFastLen = null;
				break;
			}
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . $kind . '.json';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			$wl = (int) ($meta['len'] ?? -1);
			if ($wl < 0) {
				continue;
			}
			if ($kind === 'fractal') {
				$frFastLen = $wl;
			} else {
				$lzFastLen = $wl;
			}
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);

		$fastStop = fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE;
		if ($frFastLen === null) {
			$frFastLen = strlen($fz->adaptive_compress($fractalBlob, $fastStop));
		}
		if ($sameWire) {
			$lzFastLen = $frFastLen;
		} elseif ($lzFastLen === null) {
			$lzFastLen = strlen($fz->adaptive_compress($lazyBlob, $fastStop));
		}

		$litProbe = $fz->adaptive_compress($literalBlob, $fastStop);

		self::pipeline_outer_step_rollup_zip_folder_sync_clock();

		return array(
			'litFastLen' => strlen($litProbe),
			'frFastLen' => $frFastLen,
			'lzFastLen' => $lzFastLen,
			'literal_blob' => $literalBlob,
			'literal_tag' => (string) $literalTag,
			'literal_codec' => $literalCodec !== null ? (string) $literalCodec : 'store',
			'gzip_restore' => $gzipRestore,
		);
	}

	private static function child_zip_folder_wire_fast_exit(string $td, string $key, fractal_zip $fz, string $blob): void {
		try {
			$jp = $td . DIRECTORY_SEPARATOR . $key . '.json';
			$compressed = $fz->adaptive_compress($blob, fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE);
			$codec = fractal_zip::$last_outer_codec;
			$meta = array(
				'ok' => 1,
				'lane' => $key,
				'codec' => $codec !== null ? (string) $codec : 'store',
				'len' => strlen($compressed),
			);
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	private static function child_zip_folder_fractal_lazy_exit(string $td, string $key, fractal_zip $fz, string $blob): void {
		try {
			$jp = $td . DIRECTORY_SEPARATOR . $key . '.json';
			$bp = $td . DIRECTORY_SEPARATOR . $key . '.out';
			$compressed = $fz->adaptive_compress($blob);
			$codec = fractal_zip::$last_outer_codec;
			$zp = fractal_zip::$last_zpaq_method;
			$meta = array(
				'ok' => 1,
				'lane' => $key,
				'codec' => $codec !== null ? (string) $codec : 'store',
				'zpaq' => $zp,
				'len' => strlen($compressed),
			);
			if (@file_put_contents($bp, $compressed) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	private static function child_zip_folder_literal_exit(string $td, fractal_zip $fz): void {
		try {
			$serPath = $td . DIRECTORY_SEPARATOR . 'literal_inputs.ser';
			$rawSer = (string) @file_get_contents($serPath);
			$inputs = @unserialize($rawSer);
			if (!is_array($inputs) || !isset($inputs['variants'], $inputs['raw']) || !is_array($inputs['variants']) || !is_array($inputs['raw'])) {
				exit(1);
			}
			/** @var array<int|string, mixed> $variants */
			$variants = $inputs['variants'];
			/** @var array<string, string> $rawFiles */
			$rawFiles = $inputs['raw'];
			list($blob, $tag) = $fz->choose_smallest_adaptive_literal_inner_or_raw_escaped($variants, $rawFiles);
			$codec = fractal_zip::$last_written_container_codec;
			$gr = $fz->fractal_member_gzip_disk_restore_literal_fork_snapshot();
			if (!is_array($gr)) {
				$gr = array();
			}
			$fastCompressed = $fz->adaptive_compress($blob, fractal_zip::ADAPTIVE_OUTER_STOP_AFTER_FAST_MERGE);
			$fastOuterLen = strlen($fastCompressed);
			$bp = $td . DIRECTORY_SEPARATOR . 'literal.out';
			$jp = $td . DIRECTORY_SEPARATOR . 'literal.json';
			$meta = array(
				'ok' => 1,
				'tag' => (string) $tag,
				'codec' => $codec !== null ? (string) $codec : 'store',
				'len' => strlen($blob),
				'fast_outer_len' => $fastOuterLen,
				'gzip_restore_b64' => base64_encode((string) serialize($gr)),
			);
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	/**
	 * Legacy {@see fractal_zip::zip_folder}: when literal candidates exist, run literal contest + outer {@code adaptive_compress} concurrently: three children when fractal ≠ lazy payloads, two when they match (one outer child; lazy result copied from fractal).
	 * A failed lane does not SIGKILL siblings; {@code null} if any required lane is missing after all children exit.
	 *
	 * @param array<mixed> $literalVariants
	 * @param array<string, string> $rawFilesByPath
	 *
	 * @return array{literal: array{blob: string, tag: string, codec: string, gzip_restore: array<string, string>}, fractal: array{blob: string, codec: string, zpaq: string|null}, lazy: array{blob: string, codec: string, zpaq: string|null}}|null
	 */
	public static function parallel_zip_folder_legacy_literal_fractal_lazy_trials(
		fractal_zip $fz,
		array $literalVariants,
		array $rawFilesByPath,
		string $fractalBlob,
		string $lazyBlob
	): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::parallel_fork_allowed() || !self::parallel_zip_folder_legacy_literal_fractal_lazy_enabled()) {
			return null;
		}
		if ($literalVariants === array()) {
			return null;
		}
		$sameWire = ($fractalBlob === $lazyBlob);
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zfll_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$payload = serialize(array('variants' => $literalVariants, 'raw' => $rawFilesByPath));
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'literal_inputs.ser', $payload) === false) {
			@rmdir($td);

			return null;
		}
		$active = array();
		$pLit = pcntl_fork();
		if ($pLit === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if ($pLit === 0) {
			self::child_zip_folder_literal_exit($td, $fz);
		}
		$active[$pLit] = 'literal';

		$pFr = pcntl_fork();
		if ($pFr === -1) {
			self::terminate_fork_children_and_cleanup($active, $td);

			return null;
		}
		if ($pFr === 0) {
			self::child_zip_folder_fractal_lazy_exit($td, 'fractal', $fz, $fractalBlob);
		}
		$active[$pFr] = 'fractal';

		if (!$sameWire) {
			$pLz = pcntl_fork();
			if ($pLz === -1) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			if ($pLz === 0) {
				self::child_zip_folder_fractal_lazy_exit($td, 'lazy', $fz, $lazyBlob);
			}
			$active[$pLz] = 'lazy';
		}

		$literalOut = null;
		$fractalOut = null;
		$lazyOut = null;
		while (count($active) > 0) {
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$kind = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			if ($kind === 'literal') {
				$jpath = $td . DIRECTORY_SEPARATOR . 'literal.json';
				$bpath = $td . DIRECTORY_SEPARATOR . 'literal.out';
				if (!is_file($jpath) || !is_file($bpath)) {
					continue;
				}
				$meta = json_decode((string) @file_get_contents($jpath), true);
				if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
					continue;
				}
				$blob = (string) @file_get_contents($bpath);
				$ml = (int) ($meta['len'] ?? -1);
				if ($ml !== strlen($blob) || $ml < 0) {
					continue;
				}
				$gb = isset($meta['gzip_restore_b64']) && is_string($meta['gzip_restore_b64']) ? $meta['gzip_restore_b64'] : '';
				$grRaw = @base64_decode($gb, true);
				$gr = ($grRaw !== false && $grRaw !== '') ? @unserialize($grRaw) : array();
				if (!is_array($gr)) {
					$gr = array();
				}
				/** @var array<string, string> $grSafe */
				$grSafe = array();
				foreach ($gr as $gk => $gv) {
					if (is_string($gk) && is_string($gv)) {
						$grSafe[$gk] = $gv;
					}
				}
				$literalOut = array(
					'blob' => $blob,
					'tag' => isset($meta['tag']) && is_string($meta['tag']) ? $meta['tag'] : '',
					'codec' => isset($meta['codec']) && is_string($meta['codec']) ? $meta['codec'] : 'store',
					'gzip_restore' => $grSafe,
				);
				continue;
			}
			$key = $kind === 'fractal' ? 'fractal' : 'lazy';
			$jpath = $td . DIRECTORY_SEPARATOR . $key . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . $key . '.out';
			if (!is_file($jpath) || !is_file($bpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml !== strlen($blob) || $ml < 0) {
				continue;
			}
			$codec = isset($meta['codec']) && is_string($meta['codec']) ? (string) $meta['codec'] : 'store';
			$zm = $meta['zpaq'] ?? null;
			$row = array(
				'blob' => $blob,
				'codec' => $codec,
				'zpaq' => ($zm !== null && $zm !== '' && is_string($zm)) ? $zm : null,
			);
			if ($kind === 'fractal') {
				$fractalOut = $row;
			} else {
				$lazyOut = $row;
			}
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		if ($sameWire) {
			$lazyOut = $fractalOut;
		}
		if ($literalOut === null || $fractalOut === null || $lazyOut === null) {
			return null;
		}

		return array(
			'literal' => $literalOut,
			'fractal' => $fractalOut,
			'lazy' => $lazyOut,
		);
	}

	/**
	 * Child process: run one slow outer codec trial; write {@code ji}.json (+ {@code ji}.out on success); exit 0 always on handled paths.
	 */
	private static function child_slow_outer_wave_exit(string $td, int $ji, string $kind, string $innerPath, string $ctxPath): void {
		try {
			$inner = (string) @file_get_contents($innerPath);
			$ctxRaw = @file_get_contents($ctxPath);
			$ctx = is_string($ctxRaw) ? json_decode($ctxRaw, true) : null;
			if (!is_array($ctx)) {
				exit(1);
			}
			$innerLen = (int) ($ctx['innerLen'] ?? 0);
			if ($innerLen !== strlen($inner)) {
				exit(1);
			}
			$bound7z = (int) ($ctx['bound7z'] ?? 0);
			$fzbHuge = !empty($ctx['fzbHuge']);
			$fz = new fractal_zip(256, false, false, null, false);
			$blob = null;
			$codec = $kind;
			$zpaqM = null;
			switch ($kind) {
				case '7z':
					$seven = fractal_zip::seven_zip_executable();
					if ($seven === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_7Z')) {
						self::slow_outer_wave_write_meta_only($td, $ji, $kind, 0);

						exit(0);
					}
					$b = $fz->outer_7z_best_blob($seven, $inner, $innerLen, $bound7z, $fzbHuge);
					if ($b !== null && $b !== false && $b !== '') {
						$blob = $b;
						$codec = '7z';
					}
					break;
				case 'arc':
					$arc = fractal_zip::freearc_executable();
					if ($arc === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ARC')) {
						self::slow_outer_wave_write_meta_only($td, $ji, $kind, 0);

						exit(0);
					}
					$b = $fz->outer_arc_blob($arc, $inner);
					if ($b !== null && $b !== '') {
						$blob = $b;
						$codec = 'arc';
					}
					break;
				case 'zpaq':
					self::$slowOuterWaveZpaqChildNestedDepth++;
					try {
						$zp = fractal_zip::zpaq_executable();
						if ($zp === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZPAQ')) {
							self::slow_outer_wave_write_meta_only($td, $ji, $kind, 0);

							exit(0);
						}
						$b = $fz->outer_zpaq_blob($zp, $inner);
						if ($b !== null && $b !== '') {
							$blob = $b;
							$codec = 'zpaq';
							$zpaqM = fractal_zip::$last_zpaq_method;
						}
					} finally {
						self::$slowOuterWaveZpaqChildNestedDepth--;
					}
					break;
				default:
					exit(1);
			}
			if ($blob === null || $blob === '') {
				self::slow_outer_wave_write_meta_only($td, $ji, $kind, 0);
				exit(0);
			}
			$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			$meta = array(
				'ok' => 1,
				'kind' => $kind,
				'len' => strlen($blob),
				'codec' => $codec,
				'zpaq' => ($codec === 'zpaq' && is_string($zpaqM)) ? $zpaqM : null,
			);
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	private static function slow_outer_wave_write_meta_only(string $td, int $ji, string $kind, int $ok): void {
		$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
		@file_put_contents($jp, (string) json_encode(array('ok' => $ok, 'kind' => $kind)));
	}

	/**
	 * Fork workers for independent brotli {@code -w} variants (same source inner). Fork starts prefer larger {@code w} first (scheduling hint).
	 *
	 * @param bool $lgwinForkGatePassed When **true**, skips {@see parallel_fork_allowed}/{@see parallel_brotli_lgwin_refinement_wave_enabled}.
	 *
	 * @param list<array{q:int, w:int}> $trials
	 * @return list<string|null>|null null only on catastrophic fork/setup failure; otherwise one entry per trial index (null if that encode failed). A crashed or malformed worker no longer aborts the whole pool (remaining trials still merge like sequential {@code ok=0} skips).
	 */
	public static function parallel_brotli_lgwin_variant_trials(string $innerBlob, array $trials, bool $lgwinForkGatePassed = false): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!$lgwinForkGatePassed && (!self::parallel_fork_allowed() || !self::parallel_brotli_lgwin_refinement_wave_enabled())) {
			return null;
		}
		$n = count($trials);
		if ($n < 2) {
			return null;
		}
		foreach ($trials as $t) {
			if (!is_array($t) || !isset($t['q'], $t['w'])) {
				return null;
			}
		}
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_br_lgw_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$innerPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
		if (@file_put_contents($innerPath, $innerBlob) === false) {
			@rmdir($td);

			return null;
		}
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'trials.json', (string) json_encode($trials)) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$forkOrder = range(0, $n - 1);
		usort(
			$forkOrder,
			static function (int $a, int $b) use ($trials): int {
				$wa = (int) ($trials[$a]['w'] ?? 0);
				$wb = (int) ($trials[$b]['w'] ?? 0);

				return $wb <=> $wa;
			}
		);
		$workers = self::max_workers();
		$fp = 0;
		$active = array();
		$results = array_fill(0, $n, null);
		while ($fp < $n || count($active) > 0) {
			while (count($active) < $workers && $fp < $n) {
				$ji = $forkOrder[$fp++];
				$pid = pcntl_fork();
				if ($pid === -1) {
					self::terminate_fork_children_and_cleanup($active, $td);

					return null;
				}
				if ($pid === 0) {
					self::child_brotli_lgwin_variant_exit($td, $ji, $innerPath);
				}
				$active[$pid] = $ji;
			}
			if (count($active) === 0) {
				break;
			}
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$ji = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta)) {
				continue;
			}
			if ((int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			if (!is_file($bpath)) {
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml !== strlen($blob) || $ml <= 0) {
				continue;
			}
			$results[$ji] = $blob;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);

		return $results;
	}

	/**
	 * Fork workers for independent zpaq {@code -method} fragments ({@see fractal_zip::outer_zpaq_blob_with_meth_fragment}).
	 * Winner matches sequential sweep: first strictly smaller length in original candidate order.
	 * When invoked from {@see child_slow_outer_wave_exit}, concurrent workers are capped so nested forks do not contend with parallel 7z/arc wave siblings.
	 *
	 * @param bool $zpaqMethodForkGatePassed When **true**, skips {@see parallel_fork_allowed}/{@see parallel_zpaq_outer_method_wave_enabled}.
	 *
	 * @param list<string> $methFragments
	 * @return list<string|null>|null null only on catastrophic fork/setup failure; per-trial crashes decode like skipped trials (remaining workers still merge).
	 */
	public static function parallel_zpaq_outer_method_variant_trials(string $innerBlob, array $methFragments, bool $zpaqMethodForkGatePassed = false): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!$zpaqMethodForkGatePassed && (!self::parallel_fork_allowed() || !self::parallel_zpaq_outer_method_wave_enabled())) {
			return null;
		}
		$n = count($methFragments);
		if ($n < 2) {
			return null;
		}
		foreach ($methFragments as $frag) {
			if (!is_string($frag)) {
				return null;
			}
		}
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_zpq_meth_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$innerPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
		if (@file_put_contents($innerPath, $innerBlob) === false) {
			@rmdir($td);

			return null;
		}
		$candsPlain = array_values($methFragments);
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'candidates.json', (string) json_encode($candsPlain)) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$methWeights = array();
		foreach ($candsPlain as $ci => $frag) {
			$methWeights[$ci] = 0;
			if (preg_match('/method\s+(\d+)/i', $frag, $wm)) {
				$methWeights[$ci] = (int) $wm[1];
			}
		}
		$forkOrder = range(0, $n - 1);
		usort(
			$forkOrder,
			static function (int $a, int $b) use ($methWeights): int {
				$wa = $methWeights[$a];
				$wb = $methWeights[$b];
				if ($wa !== $wb) {
					return $wb <=> $wa;
				}

				return $a <=> $b;
			}
		);
		$workers = self::max_workers();
		if (self::$slowOuterWaveZpaqChildNestedDepth > 0) {
			// Slow outer wave already overlaps zpaq with 7z + arc; nested method forks share CPU with those lanes.
			$workers = max(1, (int) floor((float) $workers / 3.0));
		}
		$fp = 0;
		$active = array();
		$results = array_fill(0, $n, null);
		while ($fp < $n || count($active) > 0) {
			while (count($active) < $workers && $fp < $n) {
				$ji = $forkOrder[$fp++];
				$pid = pcntl_fork();
				if ($pid === -1) {
					self::terminate_fork_children_and_cleanup($active, $td);

					return null;
				}
				if ($pid === 0) {
					self::child_zpaq_outer_method_variant_exit($td, $ji, $innerPath);
				}
				$active[$pid] = $ji;
			}
			if (count($active) === 0) {
				break;
			}
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$ji = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta)) {
				continue;
			}
			if ((int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			if (!is_file($bpath)) {
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml !== strlen($blob) || $ml <= 0) {
				continue;
			}
			$results[$ji] = $blob;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);

		return $results;
	}

	/**
	 * When unset: delegate to {@see parallel_zpaq_outer_method_wave_enabled}. Explicit {@code 0}/off disables parallel native-folder zpaq ladder only.
	 */
	public static function parallel_folder_native_zpaq_wave_enabled(): bool {
		$e = getenv('FRACTAL_ZIP_PARALLEL_FOLDER_NATIVE_ZPAQ');
		if ($e === false || trim((string) $e) === '') {
			return self::parallel_zpaq_outer_method_wave_enabled();
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}

		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Fork workers for native-tree {@code zpaq add} trials ({@see fractal_zip::maybe_folder_zpaq_native_smaller_than_fzc}).
	 * Merge rule matches sequential: minimize wire length among trials with {@code len <= fzcLen}; ties keep the first winning index in {@code $methFragments} order.
	 *
	 * @param string $boxAbs Scratch directory populated with the raw tree (same layout as sequential path).
	 * @param list<string> $methFragments argv fragments after target (e.g. {@code ' -method 5'}).
	 * @param string $targetArg Second operand to {@code zpaq add arc target …} (often {@code '.'} or a single relative path).
	 * @param string $prefix {@see fractal_zip::command_prefix_with_local_lib} output from the caller instance.
	 * @param string $zpaqExe Resolved zpaq binary path (same as sequential).
	 *
	 * @return list<string|null>|null null only on catastrophic fork/setup failure.
	 */
	public static function parallel_folder_native_zpaq_method_variant_trials(string $boxAbs, array $methFragments, string $targetArg, string $prefix, string $zpaqExe): ?array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!self::parallel_fork_allowed() || !self::parallel_folder_native_zpaq_wave_enabled()) {
			return null;
		}
		$n = count($methFragments);
		if ($n < 2) {
			return null;
		}
		foreach ($methFragments as $frag) {
			if (!is_string($frag)) {
				return null;
			}
		}
		if ($boxAbs === '' || !is_dir($boxAbs)) {
			return null;
		}
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_fnativ_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'box.txt', $boxAbs) === false
			|| @file_put_contents($td . DIRECTORY_SEPARATOR . 'candidates.json', (string) json_encode(array_values($methFragments))) === false
			|| @file_put_contents($td . DIRECTORY_SEPARATOR . 'zpaq_exe.txt', $zpaqExe) === false
			|| @file_put_contents($td . DIRECTORY_SEPARATOR . 'prefix.txt', $prefix) === false
			|| @file_put_contents($td . DIRECTORY_SEPARATOR . 'target_arg.txt', $targetArg) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$tzPre = fractal_zip::zpaq_outer_exec_timeout_prefix_cached();
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'tz_pre.txt', $tzPre) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$candsPlain = array_values($methFragments);
		$methWeights = array();
		foreach ($candsPlain as $ci => $frag) {
			$methWeights[$ci] = 0;
			if (preg_match('/method\s+(\d+)/i', $frag, $wm)) {
				$methWeights[$ci] = (int) $wm[1];
			}
		}
		$forkOrder = range(0, $n - 1);
		usort(
			$forkOrder,
			static function (int $a, int $b) use ($methWeights): int {
				$wa = $methWeights[$a];
				$wb = $methWeights[$b];
				if ($wa !== $wb) {
					return $wb <=> $wa;
				}

				return $a <=> $b;
			}
		);
		$workers = self::max_workers();
		$fnJobs = getenv('FRACTAL_ZIP_PARALLEL_FOLDER_NATIVE_ZPAQ_JOBS');
		if ($fnJobs !== false && trim((string) $fnJobs) !== '' && ctype_digit(trim((string) $fnJobs))) {
			$workers = max(1, min(32, (int) trim((string) $fnJobs)));
		}
		$fp = 0;
		$active = array();
		$results = array_fill(0, $n, null);
		while ($fp < $n || count($active) > 0) {
			while (count($active) < $workers && $fp < $n) {
				$ji = $forkOrder[$fp++];
				$pid = pcntl_fork();
				if ($pid === -1) {
					self::terminate_fork_children_and_cleanup($active, $td);

					return null;
				}
				if ($pid === 0) {
					self::child_folder_native_zpaq_exit($td, $ji);
				}
				$active[$pid] = $ji;
			}
			if (count($active) === 0) {
				break;
			}
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);

				return null;
			}
			$ji = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			if (!is_file($jpath)) {
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			if (!is_array($meta)) {
				continue;
			}
			if ((int) ($meta['ok'] ?? 0) !== 1) {
				continue;
			}
			if (!is_file($bpath)) {
				continue;
			}
			$blob = (string) @file_get_contents($bpath);
			$ml = (int) ($meta['len'] ?? -1);
			if ($ml !== strlen($blob) || $ml <= 0) {
				continue;
			}
			$results[$ji] = $blob;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);

		return $results;
	}

	private static function child_folder_native_zpaq_exit(string $td, int $ji): void {
		try {
			$box = trim((string) @file_get_contents($td . DIRECTORY_SEPARATOR . 'box.txt'));
			if ($box === '' || !is_dir($box)) {
				exit(1);
			}
			$tr = @file_get_contents($td . DIRECTORY_SEPARATOR . 'candidates.json');
			$cands = is_string($tr) ? json_decode($tr, true) : null;
			if (!is_array($cands) || !isset($cands[$ji]) || !is_string($cands[$ji])) {
				exit(1);
			}
			$methArg = $cands[$ji];
			$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$zp = trim((string) @file_get_contents($td . DIRECTORY_SEPARATOR . 'zpaq_exe.txt'));
			if ($zp === '') {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			$prefix = (string) @file_get_contents($td . DIRECTORY_SEPARATOR . 'prefix.txt');
			$tzPre = (string) @file_get_contents($td . DIRECTORY_SEPARATOR . 'tz_pre.txt');
			$targetArg = (string) @file_get_contents($td . DIRECTORY_SEPARATOR . 'target_arg.txt');
			$arcPath = $td . DIRECTORY_SEPARATOR . 'native_' . (string) $ji . '.zpaq';
			$qZ = fractal_zip::shell_quote_arg_cached($zp);
			$qArc = fractal_zip::shell_quote_arg_cached($arcPath);
			$qTarg = fractal_zip::shell_quote_arg_cached($targetArg);
			$cmd = $prefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add ' . $qArc . ' ' . $qTarg . $methArg . ' -force';
			if ($tzPre !== '') {
				$cmd = $tzPre . $cmd;
			}
			$cwd = getcwd();
			if (!@chdir($box)) {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			exec($cmd . ' 2>/dev/null', $xo, $ret);
			if ($cwd !== false) {
				chdir($cwd);
			}
			if ($ret !== 0 || !is_file($arcPath)) {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));
				if (is_file($arcPath)) {
					@unlink($arcPath);
				}

				exit(0);
			}
			$r = @file_get_contents($arcPath);
			if (is_file($arcPath)) {
				@unlink($arcPath);
			}
			if ($r === false || $r === '') {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			$bp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			$meta = array(
				'ok' => 1,
				'len' => strlen($r),
			);
			if (@file_put_contents($bp, (string) $r) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	private static function child_zpaq_outer_method_variant_exit(string $td, int $ji, string $innerPath): void {
		try {
			$inner = (string) @file_get_contents($innerPath);
			$tr = @file_get_contents($td . DIRECTORY_SEPARATOR . 'candidates.json');
			$cands = is_string($tr) ? json_decode($tr, true) : null;
			if (!is_array($cands) || !isset($cands[$ji]) || !is_string($cands[$ji])) {
				exit(1);
			}
			$frag = $cands[$ji];
			$fz = new fractal_zip(256, false, false, null, false);
			$zp = fractal_zip::zpaq_executable();
			$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			if ($zp === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_ZPAQ')) {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			$blob = $fz->outer_zpaq_blob_with_meth_fragment($zp, $inner, $frag);
			if ($blob === null || $blob === '') {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			$bp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			$meta = array(
				'ok' => 1,
				'len' => strlen($blob),
			);
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	private static function child_brotli_lgwin_variant_exit(string $td, int $ji, string $innerPath): void {
		try {
			$inner = (string) @file_get_contents($innerPath);
			$tr = @file_get_contents($td . DIRECTORY_SEPARATOR . 'trials.json');
			$trials = is_string($tr) ? json_decode($tr, true) : null;
			if (!is_array($trials) || !isset($trials[$ji]) || !is_array($trials[$ji])) {
				exit(1);
			}
			$t = $trials[$ji];
			$q = (int) ($t['q'] ?? -1);
			$w = (int) ($t['w'] ?? -1);
			if ($q < 0 || $q > 11 || $w < 0 || $w > 24) {
				exit(1);
			}
			$fz = new fractal_zip(256, false, false, null, false);
			$exe = fractal_zip::brotli_executable();
			$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			if ($exe === null || !fractal_zip::outer_skip_env_allows('FRACTAL_ZIP_SKIP_BROTLI')) {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			$blob = $fz->outer_brotli_blob($exe, $inner, $q, null, $w, null);
			if ($blob === null || $blob === '') {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			$bp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			$meta = array(
				'ok' => 1,
				'len' => strlen($blob),
			);
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}

	/**
	 * Fork child to run huge-mode brotli **probe** (fast quality + short timeout) while parent runs zstd in {@see fractal_zip::adaptive_compress}.
	 *
	 * @param bool $parallelAdaptiveOuterFirstBrotliGatePassed When **true**, skips {@see parallel_adaptive_outer_first_brotli_enabled}.
	 *
	 * @return array{pid: int|null, td: string|null}
	 */
	public static function speculative_outer_begin_huge_probe_brotli(string $innerBlob, bool $parallelAdaptiveOuterFirstBrotliGatePassed = false): array {
		if (!$parallelAdaptiveOuterFirstBrotliGatePassed && !self::parallel_adaptive_outer_first_brotli_enabled()) {
			return array('pid' => null, 'td' => null);
		}
		try {
			$rnd = random_bytes(8);
		} catch (\Throwable $e) {
			return array('pid' => null, 'td' => null);
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzadapt_brprobe_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return array('pid' => null, 'td' => null);
		}
		if (!function_exists('pcntl_fork')) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		$pid = pcntl_fork();
		if ($pid === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		if ($pid === 0) {
			self::speculative_outer_child_huge_probe_brotli_exit($td, $innerBlob);
		}

		return array('pid' => $pid, 'td' => $td);
	}

	/**
	 * Wait for huge-probe brotli fork; optionally discard without reading payload.
	 */
	public static function speculative_outer_finalize_huge_probe_brotli(?int $pid, ?string $td, bool $discard): ?string {
		if ($pid === null || $pid < 1 || $td === null || !is_dir($td)) {
			return null;
		}
		if ($discard) {
			if (function_exists('posix_kill')) {
				@posix_kill($pid, defined('SIGKILL') ? SIGKILL : 9);
			}
			$st = 0;
			pcntl_waitpid($pid, $st);
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$st = 0;
		pcntl_waitpid($pid, $st);
		$bp = $td . DIRECTORY_SEPARATOR . 'probe.br.out';
		$jp = $td . DIRECTORY_SEPARATOR . 'meta.json';
		if (!pcntl_wifexited($st) || pcntl_wexitstatus($st) !== 0) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if (!is_file($bp) || !is_file($jp)) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$meta = json_decode((string) @file_get_contents($jp), true);
		$blob = (string) @file_get_contents($bp);
		if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$ml = (int) ($meta['len'] ?? -1);
		if ($ml !== strlen($blob)) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);

		return $blob;
	}

	private static function speculative_outer_child_huge_probe_brotli_exit(string $td, string $innerBlob): void {
		try {
			$fz = new fractal_zip(256, false, false, null, false);
			$exe = fractal_zip::brotli_executable();
			if ($exe === null) {
				@file_put_contents($td . DIRECTORY_SEPARATOR . 'meta.json', (string) json_encode(array('ok' => 0)));
				exit(3);
			}
			$pq = fractal_zip::brotli_huge_probe_quality();
			$blob = $fz->outer_brotli_blob($exe, $innerBlob, $pq, 2.5, null, null);
			if ($blob === null || $blob === '') {
				@file_put_contents($td . DIRECTORY_SEPARATOR . 'meta.json', (string) json_encode(array('ok' => 0)));
				exit(4);
			}
			$bp = $td . DIRECTORY_SEPARATOR . 'probe.br.out';
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			$meta = array(
				'ok' => 1,
				'len' => strlen($blob),
			);
			if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'meta.json', (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(2);
		}
	}

	/**
	 * Overlap layered outer prediction probes ({@see fractal_zip_outer_predict_mapped_winner}) with the fast outer tier (zstd/brotli/xz).
	 */
	public static function speculative_outer_prediction_overlap_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_OUTER_PREDICTION');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}

		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	/**
	 * Fork child to run {@code fractal_zip_outer_predict_mapped_winner}; parent joins later ({@see speculative_outer_prediction_finalize}).
	 *
	 * @param bool $overlapGateAlreadySatisfied When **true**, skips {@see speculative_outer_prediction_overlap_enabled}
	 *        (caller already proved overlap + min-inner gates — avoids duplicate getenv / fork checks).
	 *
	 * @return array{pid: int|null, td: string|null}
	 */
	public static function speculative_outer_prediction_begin(string $innerBlob, string $repoRoot, bool $overlapGateAlreadySatisfied = false): array {
		if (!$overlapGateAlreadySatisfied && !self::speculative_outer_prediction_overlap_enabled()) {
			return array('pid' => null, 'td' => null);
		}
		try {
			$rnd = random_bytes(8);
		} catch (\Throwable $e) {
			return array('pid' => null, 'td' => null);
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzadapt_pred_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return array('pid' => null, 'td' => null);
		}
		$innerPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
		if (@file_put_contents($innerPath, $innerBlob) === false) {
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'ctx.json', (string) json_encode(array('repoRoot' => $repoRoot))) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		if (!function_exists('pcntl_fork')) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		$pid = pcntl_fork();
		if ($pid === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		if ($pid === 0) {
			self::speculative_outer_prediction_child_exit($td, $innerPath);
		}

		return array('pid' => $pid, 'td' => $td);
	}

	/**
	 * Wait for prediction fork; discard kills without reading {@code meta.json}.
	 *
	 * @return string|null mapped outer family (zpaq|7z|arc|…), or null
	 */
	public static function speculative_outer_prediction_finalize(?int $pid, ?string $td, bool $discard): ?string {
		if ($pid === null || $pid < 1 || $td === null || !is_dir($td)) {
			return null;
		}
		if ($discard) {
			if (function_exists('posix_kill')) {
				@posix_kill($pid, defined('SIGKILL') ? SIGKILL : 9);
			}
			$st = 0;
			pcntl_waitpid($pid, $st);
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$st = 0;
		pcntl_waitpid($pid, $st);
		$jp = $td . DIRECTORY_SEPARATOR . 'meta.json';
		if (!pcntl_wifexited($st) || pcntl_wexitstatus($st) !== 0) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if (!is_file($jp)) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$meta = json_decode((string) @file_get_contents($jp), true);
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
			return null;
		}
		$f = $meta['family'] ?? null;
		if ($f !== null && !is_string($f)) {
			return null;
		}
		if ($f === '') {
			return null;
		}

		return $f;
	}

	/**
	 * When unset or on: fork {@see fractal_zip::outer_xz_blob} while the layered prediction child may still be running, then
	 * {@see speculative_outer_prediction_finalize} in the parent overlaps wall time with xz — same xz bytes as sequential when join succeeds.
	 */
	public static function speculative_outer_xz_overlap_predict_enabled(): bool {
		if (!self::parallel_fork_allowed()) {
			return false;
		}
		$e = getenv('FRACTAL_ZIP_PARALLEL_OUTER_XZ_OVERLAP_PREDICT');
		if ($e === false || trim((string) $e) === '') {
			return true;
		}
		$v = strtolower(trim((string) $e));
		if ($v === '0' || $v === 'false' || $v === 'no' || $v === 'off') {
			return false;
		}

		return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
	}

	private static function child_outer_xz_overlap_exit(string $td, string $innerPath): void {
		try {
			$cr = @file_get_contents($td . DIRECTORY_SEPARATOR . 'xz_ctx.json');
			$ctx = is_string($cr) ? json_decode($cr, true) : null;
			if (!is_array($ctx) || !isset($ctx['xzExe']) || !is_string($ctx['xzExe'])) {
				exit(1);
			}
			$inner = (string) @file_get_contents($innerPath);
			$xzExe = $ctx['xzExe'];
			$xzLevel = max(0, min(9, (int) ($ctx['xzLevel'] ?? 6)));
			$timeoutSec = null;
			if (array_key_exists('timeoutSec', $ctx)) {
				$timeoutSec = ($ctx['timeoutSec'] === null) ? null : (float) $ctx['timeoutSec'];
			}
			$reuseTmp = isset($ctx['reuseTmpIn']) && is_string($ctx['reuseTmpIn']) && $ctx['reuseTmpIn'] !== '' ? $ctx['reuseTmpIn'] : null;
			$fz = new fractal_zip(256, false, false, null, false);
			$blob = $fz->outer_xz_blob($xzExe, $inner, $xzLevel, $timeoutSec, $reuseTmp);
			$jp = $td . DIRECTORY_SEPARATOR . 'xz_meta.json';
			if ($blob === null || $blob === '') {
				@file_put_contents($jp, (string) json_encode(array('ok' => 0)));

				exit(0);
			}
			$bp = $td . DIRECTORY_SEPARATOR . 'xz.out';
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode(array('ok' => 1, 'len' => strlen($blob)))) === false) {
				@unlink($bp);

				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(2);
		}
	}

	/**
	 * @param bool $xzOverlapEnvGatePassed When **true**, skips {@see speculative_outer_xz_overlap_predict_enabled}
	 *        (caller already evaluated it — avoids duplicate getenv/fork checks).
	 *
	 * @return array{pid: int|null, td: string|null}
	 */
	public static function speculative_outer_xz_fork_overlap_predict_begin(string $xzExe, string $innerBlob, int $xzLevel, ?float $timeoutSec, ?string $reuseTmpIn, bool $xzOverlapEnvGatePassed = false): array {
		self::bootstrap_cli_parallel_defaults_if_cli();
		if (!function_exists('pcntl_fork')) {
			return array('pid' => null, 'td' => null);
		}
		if (!$xzOverlapEnvGatePassed && !self::speculative_outer_xz_overlap_predict_enabled()) {
			return array('pid' => null, 'td' => null);
		}
		try {
			$rnd = random_bytes(8);
		} catch (\Throwable $e) {
			return array('pid' => null, 'td' => null);
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz_xz_pred_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return array('pid' => null, 'td' => null);
		}
		$innerPath = $td . DIRECTORY_SEPARATOR . 'inner.bin';
		if (@file_put_contents($innerPath, $innerBlob) === false) {
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		$ctx = array(
			'xzExe' => $xzExe,
			'xzLevel' => max(0, min(9, $xzLevel)),
			'timeoutSec' => $timeoutSec,
			'reuseTmpIn' => $reuseTmpIn,
		);
		if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'xz_ctx.json', (string) json_encode($ctx)) === false) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		$pid = pcntl_fork();
		if ($pid === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		if ($pid === 0) {
			self::child_outer_xz_overlap_exit($td, $innerPath);
		}

		return array('pid' => $pid, 'td' => $td);
	}

	public static function speculative_outer_xz_fork_overlap_predict_join(?int $pid, ?string $td): ?string {
		if ($pid === null || $pid < 1 || $td === null || !is_dir($td)) {
			return null;
		}
		$st = 0;
		pcntl_waitpid($pid, $st);
		$jp = $td . DIRECTORY_SEPARATOR . 'xz_meta.json';
		$bp = $td . DIRECTORY_SEPARATOR . 'xz.out';
		if (!pcntl_wifexited($st) || pcntl_wexitstatus($st) !== 0) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if (!is_file($jp)) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$meta = json_decode((string) @file_get_contents($jp), true);
		if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if (!is_file($bp)) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$blob = (string) @file_get_contents($bp);
		$ml = (int) ($meta['len'] ?? -1);
		if ($ml !== strlen($blob) || $ml <= 0) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);

		return $blob;
	}

	private static function speculative_outer_prediction_child_exit(string $td, string $innerPath): void {
		try {
			$inner = (string) @file_get_contents($innerPath);
			$cr = @file_get_contents($td . DIRECTORY_SEPARATOR . 'ctx.json');
			$ctx = is_string($cr) ? json_decode($cr, true) : null;
			if (!is_array($ctx) || !isset($ctx['repoRoot']) || !is_string($ctx['repoRoot'])) {
				exit(1);
			}
			$repoRoot = $ctx['repoRoot'];
			require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'fractal_zip_outer_predict.php';
			$fam = fractal_zip_outer_predict_mapped_winner($inner, $repoRoot);
			$meta = array(
				'ok' => 1,
				'family' => ($fam !== null && $fam !== '') ? $fam : null,
			);
			if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'meta.json', (string) json_encode($meta)) === false) {
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(2);
		}
	}

	/**
	 * Fork child to compute first {@code outer_brotli_blob} while parent continues zstd.
	 *
	 * @param bool $parallelAdaptiveOuterFirstBrotliGatePassed When **true**, skips {@see parallel_adaptive_outer_first_brotli_enabled}.
	 *
	 * @return array{pid: int|null, td: string|null}
	 */
	public static function speculative_outer_begin_first_brotli(string $innerBlob, int $quality, bool $parallelAdaptiveOuterFirstBrotliGatePassed = false): array {
		if (!$parallelAdaptiveOuterFirstBrotliGatePassed && !self::parallel_adaptive_outer_first_brotli_enabled()) {
			return array('pid' => null, 'td' => null);
		}
		if ($quality < 0 || $quality > 11) {
			return array('pid' => null, 'td' => null);
		}
		try {
			$rnd = random_bytes(8);
		} catch (\Throwable $e) {
			return array('pid' => null, 'td' => null);
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzadapt_br_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return array('pid' => null, 'td' => null);
		}
		if (!function_exists('pcntl_fork')) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		$pid = pcntl_fork();
		if ($pid === -1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return array('pid' => null, 'td' => null);
		}
		if ($pid === 0) {
			self::speculative_outer_child_first_brotli_exit($td, $innerBlob, $quality);
		}

		return array('pid' => $pid, 'td' => $td);
	}

	/**
	 * Wait for speculative brotli fork; optionally discard (SIGKILL) without reading payload.
	 */
	public static function speculative_outer_finalize_first_brotli(?int $pid, ?string $td, bool $discard): ?string {
		if ($pid === null || $pid < 1 || $td === null || !is_dir($td)) {
			return null;
		}
		if ($discard) {
			if (function_exists('posix_kill')) {
				@posix_kill($pid, defined('SIGKILL') ? SIGKILL : 9);
			}
			$st = 0;
			pcntl_waitpid($pid, $st);
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$st = 0;
		pcntl_waitpid($pid, $st);
		$bp = $td . DIRECTORY_SEPARATOR . 'first.br.out';
		$jp = $td . DIRECTORY_SEPARATOR . 'meta.json';
		if (!pcntl_wifexited($st) || pcntl_wexitstatus($st) !== 0) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		if (!is_file($bp) || !is_file($jp)) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$meta = json_decode((string) @file_get_contents($jp), true);
		$blob = (string) @file_get_contents($bp);
		if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		$ml = (int) ($meta['len'] ?? -1);
		if ($ml !== strlen($blob)) {
			self::unlink_temp_dir_contents($td);
			@rmdir($td);

			return null;
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);

		return $blob;
	}

	private static function speculative_outer_child_first_brotli_exit(string $td, string $innerBlob, int $quality): void {
		try {
			$fz = new fractal_zip(256, false, false, null, false);
			$exe = fractal_zip::brotli_executable();
			if ($exe === null) {
				@file_put_contents($td . DIRECTORY_SEPARATOR . 'meta.json', (string) json_encode(array('ok' => 0)));
				exit(3);
			}
			$blob = $fz->outer_brotli_blob($exe, $innerBlob, $quality, null, null, null);
			if ($blob === null || $blob === '') {
				@file_put_contents($td . DIRECTORY_SEPARATOR . 'meta.json', (string) json_encode(array('ok' => 0)));
				exit(4);
			}
			$bp = $td . DIRECTORY_SEPARATOR . 'first.br.out';
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			$meta = array(
				'ok' => 1,
				'len' => strlen($blob),
			);
			if (@file_put_contents($td . DIRECTORY_SEPARATOR . 'meta.json', (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(2);
		}
	}

	public static function reorder_members_enabled(): bool {
		return getenv('FRACTAL_ZIP_PIPELINE_REORDER') === '1';
	}

	/**
	 * With {@code FRACTAL_ZIP_PIPELINE_REORDER=1}: group by extension, LPT by raw size within group.
	 * Off unless {@code FRACTAL_ZIP_PIPELINE_REORDER_EXT=1}.
	 */
	public static function reorder_extension_clusters_enabled(): bool {
		return getenv('FRACTAL_ZIP_PIPELINE_REORDER_EXT') === '1'
			&& self::reorder_members_enabled();
	}

	/** Largest literal inner first when scoring multiple FZB/FZCD/… candidates (outer work ordering). */
	public static function inner_variants_lpt_enabled(): bool {
		return getenv('FRACTAL_ZIP_PIPELINE_INNER_LPT') === '1';
	}

	/**
	 * Reorder literal inner candidates so heavier payloads are scored first (shortens critical path when trials overlap later).
	 *
	 * @param list<array<string, mixed>> $innerVariants
	 * @return list<array<string, mixed>>
	 */
	public static function schedule_inner_variants_for_literal_outer(array $innerVariants): array {
		if (!self::inner_variants_lpt_enabled()) {
			return $innerVariants;
		}
		$n = count($innerVariants);
		if ($n <= 1) {
			return $innerVariants;
		}
		$ix = range(0, $n - 1);
		usort(
			$ix,
			static function (int $a, int $b) use ($innerVariants): int {
				$va = $innerVariants[$a];
				$vb = $innerVariants[$b];
				$la = (is_array($va) && isset($va['inner'])) ? strlen((string) $va['inner']) : 0;
				$lb = (is_array($vb) && isset($vb['inner'])) ? strlen((string) $vb['inner']) : 0;
				if ($la !== $lb) {
					return $la < $lb ? 1 : -1;
				}
				$ta = (is_array($va) && isset($va['tag'])) ? (string) $va['tag'] : '';
				$tb = (is_array($vb) && isset($vb['tag'])) ? (string) $vb['tag'] : '';
				if ($ta !== $tb) {
					return $ta <=> $tb;
				}

				return $a <=> $b;
			}
		);
		$out = array();
		foreach ($ix as $i) {
			$out[] = $innerVariants[$i];
		}

		return $out;
	}

	public static function max_workers(): int {
		self::bootstrap_cli_parallel_defaults_if_cli();
		static $n = null;
		if ($n !== null) {
			return $n;
		}
		$e = getenv('FRACTAL_ZIP_PIPELINE_JOBS');
		if ($e === false || trim((string) $e) === '' || !ctype_digit(trim((string) $e))) {
			return $n = self::detect_cpu_worker_default();
		}
		$v = (int) trim((string) $e);
		return $n = max(1, min(32, $v));
	}

	/**
	 * Longest-processing-time first: sort by descending weight; stable tie-break by key string.
	 *
	 * @template T
	 * @param array<string, T> $map
	 * @param callable(string, T): int|float $weightBytes
	 * @return array<string, T> new ordered map
	 */
	public static function lpt_sort_map_by_weight(array $map, callable $weightBytes): array {
		$keys = array_keys($map);
		usort(
			$keys,
			static function (string $a, string $b) use ($map, $weightBytes): int {
				$wa = (float) $weightBytes($a, $map[$a]);
				$wb = (float) $weightBytes($b, $map[$b]);
				if ($wa !== $wb) {
					return $wa < $wb ? 1 : -1;
				}
				return $a <=> $b;
			}
		);
		$out = array();
		foreach ($keys as $k) {
			$out[$k] = $map[$k];
		}
		return $out;
	}

	/**
	 * Baseline: stable path sort. Optional {@see reorder_extension_clusters_enabled}: extension buckets + LPT within bucket.
	 * Future: content fingerprints (changes wire bytes — cache keys / tests).
	 *
	 * @param array<string, string> $rawFilesByPath
	 * @return array<string, string>
	 */
	public static function reorder_raw_members_for_locality(array $rawFilesByPath): array {
		$out = $rawFilesByPath;
		ksort($out, SORT_STRING);
		if (!self::reorder_extension_clusters_enabled()) {
			return $out;
		}
		$byExt = array();
		foreach ($out as $path => $bytes) {
			$b = self::path_extension_bucket((string) $path);
			if (!isset($byExt[$b])) {
				$byExt[$b] = array();
			}
			$byExt[$b][(string) $path] = (string) $bytes;
		}
		ksort($byExt, SORT_STRING);
		$merged = array();
		foreach ($byExt as $map) {
			$sorted = self::lpt_sort_map_by_weight(
				$map,
				static function (string $k, string $bytes): int {
					return strlen($bytes);
				}
			);
			foreach ($sorted as $p => $blob) {
				$merged[(string) $p] = $blob;
			}
		}
		self::html_trace_checkpoint(self::PHASE_STREAM_REORDER, 'member order: extension buckets + LPT by raw size within bucket');

		return $merged;
	}

	/** Lowercase extension incl. dot (e.g. `.png`), or '' when none / dotfile-style basename. */
	public static function path_extension_bucket(string $path): string {
		$path = str_replace('\\', '/', $path);
		$base = basename($path);
		$d = strrpos($base, '.');
		if ($d === false || $d === 0) {
			return '';
		}

		return strtolower(substr($base, $d));
	}

	/**
	 * @param positive-int $phase 1|2|3|4 or 25
	 * @param string $note
	 */
	public static function html_trace_checkpoint(int $phase, string $note): void {
		if (self::$wallTimerZipFolderActive && self::pipeline_wall_timer_enabled()) {
			self::wall_timer_on_checkpoint($phase);
		}
		if (!self::trace_enabled()) {
			return;
		}
		$label = (string) $phase;
		if ($phase === self::PHASE_STREAM_REORDER) {
			$label = '2.5';
		}
		$msg = '[fz pipeline phase ' . $label . '] ' . $note;
		if (PHP_SAPI === 'cli' && getenv('FRACTAL_ZIP_SUPPRESS_HTML') !== '1' && ! fractal_zip::emit_html_trace()) {
			fwrite(STDERR, $msg . PHP_EOL);
			return;
		}
		fractal_zip::html_trace_print(htmlspecialchars($msg) . '<br>');
	}

	/**
	 * Deterministic parallel map for independent CPU-heavy units (Linux + pcntl). Merge order matches {@code $units} array order.
	 * Workers must return JSON-serializable payloads; {@code $callable} name must be autoloadable or closure-only within same process — **fork cannot serialize closures**.
	 * Until callers pass serializable specs, this falls back to sequential execution.
	 *
	 * @template T
	 * @param list<T> $units
	 * @param callable(T): mixed $fn
	 * @return list<mixed>
	 */
	public static function parallel_or_sequential_map(array $units, callable $fn): array {
		if ($units === array() || !self::parallel_fork_allowed()) {
			$out = array();
			foreach ($units as $u) {
				$out[] = $fn($u);
			}
			return $out;
		}
		// Fork pool for serializable work only — v1: sequential (closures not fork-safe). Implement when work units are file+argv specs.
		$out = array();
		foreach ($units as $u) {
			$out[] = $fn($u);
		}
		return $out;
	}

	/**
	 * Winner rule matching sequential loop: minimal compressed length; ties prefer smaller schedule tie index among literals;
	 * ties vs raw wire length keep raw.
	 *
	 * @param list<array{len: int, tie: int}|null> $rows (null entries skipped — failed parallel worker)
	 * @return array{0: int, 1: array<string, mixed>|null}
	 */
	public static function pick_smallest_literal_outer_vs_raw(int $rawWireLen, array $rows): array {
		$bestLen = $rawWireLen;
		$bestRow = null;
		foreach ($rows as $row) {
			if ($row === null || !is_array($row)) {
				continue;
			}
			if (($row['len'] ?? PHP_INT_MAX) < $bestLen) {
				$bestLen = (int) $row['len'];
				$bestRow = $row;
			} elseif (($row['len'] ?? PHP_INT_MAX) === $bestLen && $bestRow !== null
				&& (int) ($row['tie'] ?? 0) < (int) ($bestRow['tie'] ?? 0)) {
				$bestRow = $row;
			}
		}

		return array($bestLen, $bestRow);
	}

	/**
	 * Minimum among literals only (fast-tier champion pick).
	 *
	 * @param list<array{len: int, tie: int}|null> $rows (null entries skipped)
	 * @return array<string, mixed>|null
	 */
	public static function pick_smallest_literal_outer_among_literals_only(array $rows): ?array {
		$pick = null;
		foreach ($rows as $row) {
			if ($row === null || !is_array($row)) {
				continue;
			}
			if ($pick === null || ($row['len'] ?? PHP_INT_MAX) < ($pick['len'] ?? PHP_INT_MAX)
				|| (($row['len'] ?? PHP_INT_MAX) === ($pick['len'] ?? PHP_INT_MAX)
					&& (int) ($row['tie'] ?? 0) < (int) ($pick['tie'] ?? 0))) {
				$pick = $row;
			}
		}

		return $pick;
	}

	/**
	 * @param list<array{fin: string, tag: string, tie: int}> $jobs
	 * @return list<array{len: int, blob: string, tag: string, tie: int, codec: string|null, zpaq: string|null}|null>|null null only on catastrophic fork/setup failure or when every worker fails (caller falls back to sequential); otherwise one entry per job index (null if that worker failed).
	 */
	public static function parallel_literal_variant_outer_trials(array $jobs, string $tier): ?array {
		if (!self::parallel_fork_allowed() || count($jobs) < 2) {
			return null;
		}
		$forkMin = self::parallel_speculative_outer_min_inner_bytes();
		if ($forkMin > 0) {
			$maxFin = 0;
			foreach ($jobs as $j) {
				$maxFin = max($maxFin, strlen((string) ($j['fin'] ?? '')));
			}
			if ($maxFin < $forkMin) {
				return null;
			}
		}
		if ($tier !== 'full' && $tier !== 'fast') {
			return null;
		}
		$n = count($jobs);
		try {
			$rnd = random_bytes(16);
		} catch (\Throwable $e) {
			return null;
		}
		$td = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzplvo_' . bin2hex($rnd);
		if (!@mkdir($td, 0700, true)) {
			return null;
		}
		$next = 0;
		$active = array();
		$results = array_fill(0, $n, null);
		$workers = self::max_workers();
		while ($next < $n || count($active) > 0) {
			while (count($active) < $workers && $next < $n) {
				$ji = $next++;
				$pid = pcntl_fork();
				if ($pid === -1) {
					self::terminate_fork_children_and_cleanup($active, $td);
					return null;
				}
				if ($pid === 0) {
					self::child_literal_outer_write_and_exit($td, $ji, $jobs[$ji], $tier);
				}
				$active[$pid] = $ji;
			}
			if (count($active) === 0) {
				break;
			}
			$status = 0;
			$pid = pcntl_wait($status);
			if ($pid < 1 || !isset($active[$pid])) {
				self::terminate_fork_children_and_cleanup($active, $td);
				return null;
			}
			$ji = $active[$pid];
			unset($active[$pid]);
			if (!pcntl_wifexited($status) || pcntl_wexitstatus($status) !== 0) {
				$results[$ji] = null;
				continue;
			}
			$jpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			$bpath = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			if (!is_file($jpath) || !is_file($bpath)) {
				$results[$ji] = null;
				continue;
			}
			$meta = json_decode((string) @file_get_contents($jpath), true);
			$blob = (string) @file_get_contents($bpath);
			if (!is_array($meta) || (int) ($meta['ok'] ?? 0) !== 1) {
				$results[$ji] = null;
				continue;
			}
			$mlen = (int) ($meta['len'] ?? -1);
			if ($mlen !== strlen($blob)) {
				$results[$ji] = null;
				continue;
			}
			$zpaqM = $meta['zpaq'] ?? null;
			$results[$ji] = array(
				'len' => $mlen,
				'blob' => $blob,
				'tag' => (string) ($meta['tag'] ?? ''),
				'tie' => (int) ($meta['tie'] ?? 0),
				'codec' => isset($meta['codec']) && is_string($meta['codec']) ? $meta['codec'] : null,
				'zpaq' => ($zpaqM !== null && $zpaqM !== '' && is_string($zpaqM)) ? $zpaqM : null,
			);
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
		for ($i = 0; $i < $n; $i++) {
			if ($results[$i] !== null) {
				return $results;
			}
		}

		return null;
	}

	/**
	 * @param array<int, int> $active
	 */
	private static function terminate_fork_children_and_cleanup(array $active, string $td): void {
		foreach (array_keys($active) as $cp) {
			$cp = (int) $cp;
			if (function_exists('posix_kill')) {
				@posix_kill($cp, 9);
			}
		}
		$st = 0;
		foreach (array_keys($active) as $cp) {
			pcntl_waitpid((int) $cp, $st);
		}
		self::unlink_temp_dir_contents($td);
		@rmdir($td);
	}

	private static function unlink_temp_dir_contents(string $td): void {
		if (!is_dir($td)) {
			return;
		}
		$g = glob($td . DIRECTORY_SEPARATOR . '*');
		if (is_array($g)) {
			foreach ($g as $f) {
				if (is_string($f) && is_file($f)) {
					@unlink($f);
				}
			}
		}
	}

	/**
	 * @param array{fin: string, tag: string, tie: int} $job
	 */
	private static function child_literal_outer_write_and_exit(string $td, int $ji, array $job, string $tier): void {
		try {
			if ($tier === 'full') {
				$r = fractal_zip::encode_pipeline_literal_outer_trial_full($job['fin']);
			} else {
				$r = fractal_zip::encode_pipeline_literal_outer_trial_fast($job['fin']);
			}
			$blob = $r['blob'];
			$meta = array(
				'ok' => 1,
				'len' => strlen($blob),
				'tag' => $job['tag'],
				'tie' => $job['tie'],
				'codec' => $r['codec'],
				'zpaq' => $r['zpaq'],
			);
			$bp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.out';
			$jp = $td . DIRECTORY_SEPARATOR . (string) $ji . '.json';
			if (@file_put_contents($bp, $blob) === false) {
				exit(1);
			}
			if (@file_put_contents($jp, (string) json_encode($meta)) === false) {
				@unlink($bp);
				exit(1);
			}
			exit(0);
		} catch (\Throwable $e) {
			exit(1);
		}
	}
}
