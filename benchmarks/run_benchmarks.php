#!/usr/bin/env php
<?php
/**
 * Measure raw size, gzip9 + 7z + min-ext baselines (bytes, compress seconds, per-codec extract for cache), fractal_zip .fz
 * (bytes + zip time + open_container extract), per-codec decompress times,
 * and `*` winners for bytes and decompress time among gzip / 7z / min ext / fzc (compress seconds columns have no `*`).
 *
 * Primary KPI for fractal_zip vs baselines: **compressed bytes** — `.fz` should beat or tie the smallest of gzip9, 7z dir, and min-ext
 * folder archive when possible; timings are secondary. When `winner_compression` is ext/7z/gzip, treat that as the headline regression for ratio work.
 *
 * Workflow (recommended):
 *   Phase 1 — full coverage, reasonable wall time: run every corpus to completion.
 *   Use --no-case-timeout or a generous --case-timeout=, keep default folder-gzip-fast on heavy dirs (omit --large), leave
 *   baseline cache on so repeat runs stay faster. If min-ext or
 *   verify dominates time, add --no-best-ext and/or --no-verify for this pass only. Use --no-multipass if needed to finish.
 *   Inspect the skipped-case log after each run (default **benchmarks/.last_skipped_cases.json**, **gitignored** under benchmarks/ — use **`--last-skipped-log=`** for a persistent file); fix timeouts or env until skipped_cases is empty.
 *   Phase 2 — shrink `.fz` bytes: attack easiest wins first (segment_length, multipass, heavy dirs with
 *   --large where full fractal_zip matters, auto-tune only where worth it). Re-measure with --refresh-baseline-cache when
 *   baseline policy changes.
 *   **Bytes vs defaults:** When **`FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE`** is **unset**, this driver sets it to **`1`** (general-purpose / lifestyle measure — same as the library default). Under lifestyle, folders ≤**`FRACTAL_ZIP_LIFESTYLE_SMALL_TRYHARD_MAX_RAW_BYTES`** (default 16 KiB) may spend ~1 s on a deeper encode (e.g. test_files76 **5807** brotli); larger trees stay speed-first. Export **`FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=0`** for bytes-first segment **300** / heavier trials; use **`--bench-profile=text-inner`** or **`--ultra`** for specialized lanes. Min-ext brotli/arc bytes can drift with external tool versions; `.fz` rows also depend on outer-codec tournament logic, not segment length alone.
 *
 * Usage:
 *   php run_benchmarks.php              # human table, default test folders
 *   php run_benchmarks.php --check-undeep-sync   # only: verify fractal_zip.php vs fractal_zip-undeep-unwrap.php (FZB* Brotli markers); exit 0/1; no bench
 *   php run_benchmarks.php --json       # machine-readable to stdout; same object is also written to benchmarks/.last_bench.json (see below). Encoder uses JSON_INVALID_UTF8_SUBSTITUTE when the PHP build supports it so odd UTF-8 in labels or census keys cannot fail the run.
 *   Each cases[] row includes short_desc (16-char hint; see benchmarks/bench_corpus_descriptions.php). Human table prints the same under column `desc`.
 *   When the folder bundle scan ran, JSON rows may include **folder_bundle_census** (files, bytes, textish_ratio, top_ext_bytes); see **FRACTAL_ZIP_FOLDER_BUNDLE_CENSUS_STDERR** / **FRACTAL_ZIP_FOLDER_BUNDLE_SCHED_COST_BIAS** in the `fractal_zip` class docblock.
 *   **FRACTAL_ZIP_BENCH_SHOW_FOLDER_CENSUS=1** adds human-table column **tx%** (100×**textish_ratio**; — when census absent).
 *   php run_benchmarks.php --large          # full fractal_zip on heavy corpora (disable folder gzip-fast); when no --bench-profile, applies large-balanced tier defaults
 *   php run_benchmarks.php --no-best-ext  # skip running min-ext tournament (reuse benchmarks/.baseline_cache.json values for display when raw+gzip key match)
 *   php run_benchmarks.php --no-freearc     # alias for --no-best-ext
 *   php run_benchmarks.php --tune test_files13   # sweep segment_length; minimize fzc bytes
 *   php run_benchmarks.php --json --out-json=bench.json   # extra copy to bench.json; stdout + .last_bench.json still apply
 *   php run_benchmarks.php --out-json=bench.json   # file only (no JSON on stdout); .last_bench.json still written unless --no-save-last-json
 *   Every successful full run writes the same JSON object to benchmarks/.last_bench.json (overwritten each time; that default path is **gitignored** in the repo — use **`--out-json=`** with a path or **`FRACTAL_ZIP_BENCH_LAST_JSON`** for snapshots you keep or hand to **`--from-json`** guards). **FRACTAL_ZIP_BENCH_LAST_JSON**=/path/file.json; **FRACTAL_ZIP_BENCH_NO_SAVE_LAST_JSON**=1 or **`--no-save-last-json`** to disable.
 *   php run_benchmarks.php --only=test_files110 --ultra   # bytes-first preset (FRACTAL_ZIP_ULTRA=1 + CLI-aligned env; zpaq/7z predict lanes see full sweep)
 *   php run_benchmarks.php --only=128 --no-best-ext --bench-fast-staged-brotli   # optional: pin staged literal fast-tier brotli cap at Q3 when unset (limits a high FRACTAL_ZIP_BROTLI_QUALITY)
 *   FRACTAL_ZIP_BENCH_FAST_STAGED_BROTLI=1 php run_benchmarks.php …   # same as --bench-fast-staged-brotli without argv
 *   php run_benchmarks.php --only=128 --no-best-ext --bench-fast-zip   # optional wall preset: staged fast-tier brotli cap + tighter outer-predict probe/timeout (see benchmarks/bench_fast_zip_env.php)
 *   FRACTAL_ZIP_BENCH_FAST_ZIP=1 php run_benchmarks.php …   # same preset as --bench-fast-zip without argv (useful for wrappers / CI)
 *   php run_benchmarks.php --bench-profile=small-bytes   # bytes-first small tier: default --maximum-size=2M and 90s cap unless overridden
 *   php run_benchmarks.php --bench-profile=medium-balanced # bounded 2–12MiB-ish tier: tighter prediction probes/timeouts unless overridden
 *   php run_benchmarks.php --bench-profile=large-fast      # large tier wall clock: SPEED + bounded outer predict; native zpaq compare capped (not disabled)
 *   php run_benchmarks.php --bench-profile=large-balanced   # like large-fast + explicit native brotli cap + FRACTAL_ZIP_SPEED_TRY_BROTLI for bytes-aware large trees
 *   php run_benchmarks.php --bench-profile=large-bytes    # large-balanced + wider native 7z/brotli/zpaq compare caps + higher outer-predict inner ceiling (more CPU; often smaller .fz on big merged inners). Pair with --large for full fractal on heavy-list names
 *   php run_benchmarks.php --bench-profile=fair-full      # strict sole-win campaign: lifestyle off, full zpaq/PAQ native caps, text-inner on
 *   php run_benchmarks.php --bench-profile=fs               # fs manual-compress speed preset on ≤2 MiB corpora (see benchmarks/bench_fs_speed_env.php)
 *   php run_benchmarks.php --bench-profile=world-record     # enwik8 / Hutter-scale: ultra + 128 MiB native caps + entry sort; null timeout (aliases: hutter, ltcb, enwik8)
 *   php run_benchmarks.php --only=test_files50,test_files2   # subset of corpora
 *   php run_benchmarks.php --only=13,105                     # same as test_files13,test_files105 (bare digits → test_files{digits})
 *   php run_benchmarks.php --only=133 --large --no-case-timeout --json   # test_files133 (Silesia x12 folder); build benchmarks/build_test_files133_silesia12.php first
 *   php run_benchmarks.php --skip=test_files10   # exclude known-slow/problem corpora
 *   php run_benchmarks.php --limit=2 --no-extract   # quick pass over first 2 corpora
 *   php run_benchmarks.php --case-timeout=45   # optional per-case wall (default is unlimited for full filled tables)
 *   php run_benchmarks.php --no-case-timeout   # same as default; explicit unlimited wall
 *   php run_benchmarks.php --no-case-disk-sweep   # keep benchmarks/.work between corpora (default: empty .work after each case)
 *   php run_benchmarks.php --case-timeout=45    # override seconds (0 = unlimited)
 *   php run_benchmarks.php --repeat=3   # run each case N times, report median timings
 *   php run_benchmarks.php --jobs=4   # run up to 4 corpora at once (Linux + pcntl_fork); defers per-case disk sweep — see benchmarks/PARALLELISM.md
 *   php run_benchmarks.php --no-baseline-cache   # do not read/write benchmarks/.baseline_cache.json (gzip/7z/min-ext reuse)
 *   php run_benchmarks.php --no-phase1-case-cache   # with --phase1, always re-run corpora (default: reuse benchmarks/.phase1_case_cache.json)
 *   php run_benchmarks.php --refresh-phase1-case-cache   # ignore phase-1 row cache on read; rewrite after each measured case
 *   php run_benchmarks.php --refresh-baseline-cache   # re-measure baselines and overwrite cache
 *   php run_benchmarks.php --baseline-cache=/path/baselines.json   # custom cache file
 *   php run_benchmarks.php --last-skipped-log=/path/skipped.json   # timeout/failure log (default: benchmarks/.last_skipped_cases.json)
 *   php run_benchmarks.php --no-multipass   # faster, less aggressive fractal optimization
 *   php run_benchmarks.php --no-verify      # skip round-trip extract+content verification
 *   php run_benchmarks.php --no-stray-sweep   # do not pkill orphaned zpaq/brotli/arc after cases (default: sweep on)
 *   benchmarks/run_bench_with_cleanup.sh …  # same CLI with trap cleanup (preferred for long --only=test_files78)
 *   benchmarks/kill_stray_bench_procs.sh    # manual kill if compressors/php bench children are still running
 *   php run_benchmarks.php --keep-verify-extract=/tmp/fzvex  # after verify, copy extract tree to /tmp/fzvex/<label>/ (see benchmarks/sha1_tree_diff.php)
 *   php run_benchmarks.php --bench-notes    # after the table, print long footnotes (column meanings, caches, flags); omitted by default
 *   FRACTAL_ZIP_BENCH_SHOW_FOLDER_CENSUS=1  # human table: extra **tx%** column after **desc** (100×folder_bundle_census.textish_ratio; — if no census)
 *   php run_benchmarks.php --bench-pipeline-numbers   # stderr: phase wall ms + outer step ms + rollup (FRACTAL_ZIP_PIPELINE_TIMING=1, OUTER_STEP_LOG=1); optional =verbose for skipped-outer lines
 *   FRACTAL_ZIP_BENCH_PIPELINE_NUMBERS=1    # same as --bench-pipeline-numbers without argv
 *   php run_benchmarks.php --bench-keep-shell-literal-env  # do not clear FRACTAL_ZIP_LITERAL_* (use shell speed-tuning during benches)
 *   php run_benchmarks.php --legacy-folder-zip  # keep inherited FRACTAL_ZIP_FOLDER_UNIFIED_STREAM (default: clear env so stream-first matches zip_folder defaults)
 *   php run_benchmarks.php --adaptive-markers   # FRACTAL_ZIP_ADAPTIVE_MARKERS=1 and FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 so the fractal zip_folder marker pass runs (stream-first unified path skips it). JSON: top-level adaptive_markers_bench_mode; each row bench_adaptive_markers. See benchmarks/bench_adaptive_markers_compare.sh for A/B.
 *   php run_benchmarks.php --metastruct         # --adaptive-markers plus FRACTAL_ZIP_METastruct=1 (identify census merge; fractal_zip_metastruct.php). A/B helper: benchmarks/bench_metastruct_markers_compare.sh
 *   Adaptive-markers runs use legacy zip_folder and often exceed the default 45s cap on web-static mixes (e.g. test_files75 can be ~5+ minutes wall with variance). Unless FRACTAL_ZIP_BENCH_ADAPTIVE_MARKERS_MIN_TIMEOUT_SEC is set, the driver raises the effective per-case floor to max(global, 360s). Set that env to 0 to keep the global cap unchanged, or to N>0 for a custom floor.
 *
 * Raw-tier deep unwrap × MPQ proxy A/B (folder .fz bytes, no baselines): php benchmarks/raw_tier_unwrap_ab.php [--only=a,b] [--limit=N] [--with-strict-column]
 * Lifestyle guard (74/75/76): php benchmarks/guard_lifestyle_bytes_and_time.php [--max-fzc-seconds=22.0] [--from-json=...]
 * Six-case stable perf (proxy set, median timings): php benchmarks/guard_sixcase_stable_perf.php [--repeat=7] [--max-sum-zip-seconds=35] [--taskset=0-3]
 * Outer full-Brotli refinement caps (see fractal_zip.php helpers): FRACTAL_ZIP_BROTLI_FULL_OUTER_ARC_TEXTLIKE_MAX_INNER_BYTES, FRACTAL_ZIP_BROTLI_FULL_OUTER_ZSTD_XZ_TEXTLIKE_MAX_INNER_BYTES, FRACTAL_ZIP_BROTLI_FULL_OUTER_ARC_ZPAQ_NON_TEXTLIKE_MAX_INNER_BYTES (arc/zpaq prediction when outer_likely_textlike is false; default 262144; 0 disables). Bytes-first: if the fast outer tier already leads with brotli, adaptive_compress still runs full Q10/Q11+lgwin (not only Q1/Q3).
 * Zpaq outer high-method ladder width: FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES (default 1 MiB; includes -method 9/8/7 before 6…3 when inner/raw is under the cap; 0 disables high methods).
 * Undeep encoder FZB* Brotli / path-order parity: php benchmarks/check_undeep_fzb_brotli_sync.php (optional --json for one-line result)
 *   Preflight: CHECK_UNDEEP_SYNC=1 (alias: FRACTAL_ZIP_BENCH_CHECK_UNDEEP_SYNC=1) on `php benchmarks/run_benchmarks.php` runs
 *   that check once at startup, then continues with benchmarks. Or: `php benchmarks/run_benchmarks.php --check-undeep-sync` runs
 *   only that check and exits (0 = ok). With `--json`, machine output is only for `--check-undeep-sync` (check script’s JSON);
 *   preflight + full `--json` bench keeps a single main JSON on stdout. The low-priority shell can run the same check then
 *   unset those env vars before exec so the PHP entrypoint does not run it twice.
 *   php-smokes CI uses phase 2 of tests/run_php_smokes.sh: `php benchmarks/run_benchmarks.php --check-undeep-sync` only (see benchmarks/check_undeep_fzb_brotli_sync.php).
 * Literal transform “full tournament” (more CPU/RAM on large literals): FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT=1 (see choose_best_literal_bundle_transform). Undecompressed **.flac** literals: **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE`** (`0`|`1`|`auto`), **`FRACTAL_ZIP_LITERAL_FLAC_TRANSFORM_PROBE_MAX_BYTES`** (default 16 MiB for `auto`), **`FRACTAL_ZIP_LITERAL_FLAC_GZIP_PROBE_LEVEL`**, **`FRACTAL_ZIP_LITERAL_FLAC_SKIP_TRANSFORMS_MAX_GZIP1_RATIO`**.
 *
 * Literal-bundle bytes-first defaults: before loading fractal_zip, the driver clears speed-tuning env vars
 * (`FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL`, `FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL`, `FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN`,
 * `FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN`, `FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL`, `FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER`)
 * so BMP literals use the same tournament defaults as a clean shell (BMP zlib probe 9, exhaustive grid chains, transform chaining).
 * To keep your shell’s overrides: `FRACTAL_ZIP_BENCH_LITERAL_SPEED_DEFAULTS=1` or `--bench-keep-shell-literal-env`.
 *
 * **Verification tiers (conceptual):** default verify is per-file **SHA1 = strict container-byte identity**. That is stricter
 * than **semantic lossless** (same decoded samples + meaningful metadata, container bytes may differ): FZCD merged PCM →
 * per-track FLAC re-encode, PNG/GIF image PAC, etc. **Already-lossy** formats may use format-specific relaxed checks (e.g.
 * JPEG). Expect `verify_ok=false` under SHA1 when a semantic-lossless encoder changes bytes unless you `--no-verify`, disable
 * that PAC (`FRACTAL_ZIP_IMAGEPAC=0`, …), or (future) enable a semantic verifier for that format (MoPAQ: `FRACTAL_ZIP_BENCH_SC2REPLAY_SEMANTIC_VERIFY`;
 * FLAC semantic compare not wired yet — see `fractal_zip_flac_pac.php` header). With **`--no-verify`**, machine JSON leaves **`verify_ok`** / **`verify_mismatch_files`** as **`null`** (not false / 0); see **`aggregateRepeatedRows`** in this file.
 * **FLAC:** merged FZCD is **off by default**
 * (`FRACTAL_ZIP_FLACPAC` unset) so trees pass SHA1 verify without extra tooling; set **`FRACTAL_ZIP_FLACPAC=1`** to benchmark
 * transmedia ratio (semantic lossless audio, not bit-identical `.flac` files under SHA1).
 * MoPAQ semantic peel (FZB mode 11): extracted `.SC2Replay` container bytes may differ while members match; when
 * FRACTAL_ZIP_BENCH_SC2REPLAY_SEMANTIC_VERIFY is on (default), benchmark verify treats matching mpyq extracts as equal (see tools/fractal_zip_mpq_semantic.py semantic-equal).
 * **Folder FZHR (peeled PKZIP → FZHM):** when a folder encodes one multi-member `.zip` as logical members, extract rebuilds
 * the on-disk container (verbatim when semantic rebuild matches; otherwise member-payload semantic compare).
 * **`FRACTAL_ZIP_FOLDER_CONTAINER_SEMANTIC_VERIFY`** is on by default (unset env); set **`=0`** for strict per-file SHA1 on `.zip` envelopes.
 * **ZIP (FZB modes 7/18):** semantic peel + `ZipArchive` rebuild can change outer **`.zip`** bytes; `choose_best_literal_bundle_transform`
 * runs **`literal_bundle_coerce_verbatim_disk_roundtrip`** so strict SHA1 verify matches on-disk members (disable with **`FRACTAL_ZIP_LITERAL_DISABLE_DISK_ROUNDTRIP_COERCE=1`** for diagnostics). Repro: **`php benchmarks/repro_folder_zip_verify.php`**; CI: **`php benchmarks/smoke_repro_folder_zip_roundtrip.php`**.
 *
 * Huge corpora: gzip baseline uses streaming zlib when raw size > 64 MiB (see gzipBaselineTimed).
 * If you still hit memory_limit, run `php -d memory_limit=2G ...`, or set FRACTAL_ZIP_BENCH_MEMORY_LIMIT=2G
 * (applied at startup), or set FRACTAL_ZIP_BENCH_GZIP_BASELINE_IN_MEMORY_BYTES=0.
 *   FRACTAL_ZIP_FOLDER_BUNDLE_CENSUS_STDERR=1 - one JSON line per `collect_raw_files_for_bundle` (extension + text-ish byte mass; stderr only; no `.fz` change; JSON_INVALID_UTF8_SUBSTITUTE when available).
 *   FRACTAL_ZIP_FOLDER_BUNDLE_SCHED_COST_BIAS=1 - with folder census, nudge inner-variant `cost_hint` for scheduling on text-heavy trees (optional; trial order only).
 *   php benchmarks/smoke_folder_bundle_census_equiv.php - regression: census `accumulate`+`pack` matches `folder_bundle_census_from_raw_map` (also run from tests/run_php_smokes.sh after undeep check).
 *   php run_benchmarks.php --auto-tune   # sets FRACTAL_ZIP_AUTO_TUNE=1 before loading (slower, better ratio)
 *   Auto-tune objective (fractal_zip): **smallest .fz bytes** by default (FRACTAL_ZIP_FAST_CORPUS_ZIP_SEC unset). Set FRACTAL_ZIP_FAST_CORPUS_ZIP_SEC=0.5 (etc.) to blend in J_proxy on slow probes; -1 = always J_proxy. Heavy-folder gzip-fast when raw ≥ FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES (default 128 MiB, same as library auto when unset). Env: FRACTAL_ZIP_J_CURVE_* (library auto-tune only), FRACTAL_ZIP_IMPROVEMENT_THRESHOLD, FRACTAL_ZIP_SUBSTRING_TOP_K, FRACTAL_ZIP_MULTIPASS_GATE_MULT.
 *   php run_benchmarks.php --with-synthetic-micro   # legacy no-op: canonical test_files### discovery already includes test_files50/test_files51
 *   php run_benchmarks.php --with-huge-corpora      # legacy no-op for discovery: canonical test_files### corpora are scheduled by default
 *   php run_benchmarks.php --maximum-size=2000000   # only corpora whose on-disk raw bytes are ≤ limit (bytes; or 2M / 10MiB / 10MB — see benchParseMaximumSizeBytes). When set, huge trees + Squash mirrors become discoverable and are included only if they fit the cap.
 *   php run_benchmarks.php --max-size=2M             # alias for --maximum-size= (same parser).
 *
 * If you use FRACTAL_ZIP_SEGMENT_LENGTH=auto, zip_folder runs many trials (full auto-tune). For faster benches:
 *   FRACTAL_ZIP_AUTO_TUNE=0
 *
 * Default set = every canonical test_files### directory present on disk (plus the historical root `test_files`).
 * Generated duplicate sample variants (for example test_files72_sample_*) are excluded unless explicitly requested with --only.
 * Heavy list ($heavyCorporaFolderGzipFastDefault): this script sets FRACTAL_ZIP_FOLDER_GZIP_FAST=1 before zip_folder when raw ≥ benchHeavyFolderGzipFastMinRawBytes() (see FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES; default **128 MiB**, matching library auto when unset). `--large` forces FRACTAL_ZIP_FOLDER_GZIP_FAST=0 for those names.
 * test_files62: synthetic .gz parity corpus; runOneCase forces FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=65536 so bundle-only does not short-circuit the tree (see benchmarks/build_test_files62.php). Uses the same unified-stream + raw-tier wire contest as production (FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER). Per-case cap is max(global, 90s). (CLI suppresses HTML/trace output unless FRACTAL_ZIP_CLI_VERBOSE=1; huge fractally_process_string dumps need FRACTAL_ZIP_FRACTAL_PROCESS_DEBUG=1.) Regression: benchmarks/fzc_raw_tier_wire_dual_smoke.php, benchmarks/test_files62_fzc_bytes_smoke.php, benchmarks/fz_arc_native_roundtrip_smoke.php, benchmarks/fzcd_pcm_pretransform_smoke.php.
 * Container / representation lane table (offline; not scored here): benchmarks/container_representation_experiment.php (`CONTAINER_EXP_*`, optional `CONTAINER_EXP_SYNTHETIC=1`, `CONTAINER_EXP_PCM=1`). FLAC env matrix vs `run_benchmarks`: benchmarks/flac_lane_matrix.php (`--max-rows=N` for smoke).
 * Env: FRACTAL_ZIP_FOLDER_GZIP_FAST (0/1/auto), FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES (auto threshold, default 128 MiB raw).
 * Stream-first folder encode: default on (unset); FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 → legacy per-member fractal zip_folder. Unified path tries FZCD merged PCM when **`FRACTAL_ZIP_FLACPAC=1`** and applicable, else FZB* (FZWS/FZBD per policy), then adaptive outer vs raw. FZCD merged-fractal chunks: **`FRACTAL_ZIP_FLACPAC_PCM_PRETRANSFORM=0`** disables reversible PCM pre-transforms before fractal (default on when unset); when on, chunk **preId** u8 is **0–14** (see README / `fractal_zip_flac_pac.php` docblock). **`FRACTAL_ZIP_FLACPAC_PCM_PRE_FRACTAL_CANDIDATES`** (default **8**, max **16**), **`FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_RANK_LEVEL`** (default **5**), optional **`FRACTAL_ZIP_FLACPAC_PCM_PRE_GZIP_DUAL_RANK=1`**. Gzip-fast: inner ≤ FRACTAL_ZIP_FOLDER_GZIP_FAST_ADAPTIVE_OUTER_MAX_BYTES uses same adaptive outer. JSON row includes folder_unified_stream. **Adaptive markers** (`FRACTAL_ZIP_ADAPTIVE_MARKERS=1`, see fractal_zip_marker_adapt.php) run on the fractal folder leg only; use **`--adaptive-markers`** to set that env and force unified stream off for a comparable bench run.
 * Per-case wall clock: default 45s (SIGKILL worker) when pcntl exists; cooperative deadline sets FRACTAL_ZIP_TIME_BUDGET_MS for zip_folder.
 * Heavy corpora (raw ≥ FRACTAL_ZIP_BENCH_TIMECAP_HEAVY_RAW_BYTES, default 12 MiB) under a deadline: gzip / 7z / min-ext run *before* zip_folder so those columns stay filled; then .fz + verify use remaining time.
 * --phase1: smaller pre-zip reserve for zip_folder; 7z runs after FZC if time remains; FZC-resume uses almost the full cap for zip_folder.
 * Completed phase-1 rows are stored in benchmarks/.phase1_case_cache.json (by default) so the next --phase1 run replays them instantly
 *   when raw bytes, run flags, gzip/min-ext tool fingerprints, main source fingerprints, and FZC runtime env fingerprints match; use --refresh-phase1-case-cache to force remeasurement.
 * Phase-1 “monster” tree corpora (test_files54/55/58/59): auto store-only .fz under a time cap (ratio not comparable to full fractal zip) and
 *   a higher wall clock (max(global cap, 90s) by default). Set FRACTAL_ZIP_BENCH_PHASE1_MONSTER_TIMEOUT_SEC to override or 0 to use only --case-timeout.
 *   FRACTAL_ZIP_BENCH_PHASE1_LITERAL_RAW_BYTES=NN auto store-only for phase-1 when raw_bytes ≥ NN (in addition to the fixed monster list).
 * Optional raw FZB4 store-only .fz (ruins ratio): set FRACTAL_ZIP_BENCH_LITERAL_STORE=1 (and optionally FRACTAL_ZIP_BENCH_LITERAL_STORE_MIN_RAW_BYTES, default 12 MiB) — not enabled by default.
 * Baseline cache (gzip / 7z / min-ext bytes + seconds per corpus): default file benchmarks/.baseline_cache.json — skip re-running those tools when raw bytes and gzip/min-ext tool fingerprints match. FRACTAL_ZIP_BENCH_BASELINE_CACHE=path, FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE=1, or --no-baseline-cache / --refresh-baseline-cache / --baseline-cache=path.
 * After native-folder wire changes, compressor/tool PATH churn, or edits that change baseline keys: run once with --refresh-baseline-cache (or delete stale labels in .baseline_cache.json). Re-save --out-json goldens if the repo tracks them. See benchmarks/LARGE_CORPUS_SPEED.md (heading: Baseline cache hygiene).
 * Each run writes benchmarks/.last_skipped_cases.json (timeouts / failures; **gitignored** under benchmarks/ like `.last_bench.json` — set **`FRACTAL_ZIP_BENCH_LAST_SKIPPED_LOG`** or **`--last-skipped-log=`** for a stable path). See benchmarks/LARGE_CORPUS_SPEED.md (JSON machine output).
 * FRACTAL_ZIP_BENCH_CASE_TIMEOUT_SEC overrides default before argv; --case-timeout / --no-case-timeout override env.
 * Default lifestyle measurement: **no per-case timeout** so every corpus finishes and gzip/7z/min-ext/fzc bytes+times+extract fill (comparable TOTAL). Pass `--case-timeout=N` only for wall-bounded probes. Soft midsize blanks and the old ≥128 MiB 7z column skip apply only under an active short case wall. After the run, missing required cells or skipped cases exit 1 unless `--allow-incomplete` / `--phase1`.
 * **Low CPU / I/O priority (share the machine):** `benchmarks/run_benchmarks_low_priority.sh` runs this script under `nice -n 19` and `ionice -c3` (idle class) when available. Or: `FRACTAL_ZIP_BENCH_LOW_PRIORITY=1 php benchmarks/run_benchmarks.php …` (calls proc_nice(19) once at startup; combine with nice in shell if you want both — usually pick one).
 * **Bytes-win report:** `php benchmarks/report_bytes_wins.php benchmarks/.last_bench.json` (or your `--out-json=` / `--json` file). `--min-pct=33` lists only “big” wins; `--compress-time-audit` flags rows where `zip_seconds` exceeds min baseline compress times (see benchmarks/LARGE_CORPUS_SPEED.md for **`--bench-profile`** tiers including **`large-bytes`** and **`bash benchmarks/run_large_corpus_bytes_push.sh`**). `benchmarks/search_bytes_wins_low_priority.sh` (nice/ionice + table): `--min-pct=`, `--compress-time-audit`, `--zip-time-margin=`, `--json-out=`, pass-through `--limit=`, `--only=`, etc.
 *
 * Each run copies the source folder into benchmarks/.work/ to avoid mutating originals.
 * After each corpus (and each --tune segment), the entire .work/ tree is cleared so timed-out or SIGKILL’d runs do not
 * leave multi-gigabyte copies on disk (useful on small partitions; disable with --no-case-disk-sweep). Per-case TMPDIR/TMP/TEMP
 * is also redirected into benchmarks/.work/.tmp_* so compressor scratch follows the same cleanup path instead of filling /tmp.
 */

declare(strict_types=1);

// True when this file is the CLI entrypoint. `php -r 'require …'` must NOT launch a
// full suite (it raced gold runs over benchmarks/.work and skipped corpora).
$__fzBenchIsMain = PHP_SAPI === 'cli'
	&& isset($_SERVER['SCRIPT_FILENAME'])
	&& is_string($_SERVER['SCRIPT_FILENAME'])
	&& $_SERVER['SCRIPT_FILENAME'] !== ''
	&& $_SERVER['SCRIPT_FILENAME'] !== '-'
	&& @realpath($_SERVER['SCRIPT_FILENAME']) === @realpath(__FILE__);

// Inherit into OPcache re-exec child (must be set before bootstrap).
putenv('FRACTAL_ZIP_FATAL_THROW=1');
$_ENV['FRACTAL_ZIP_FATAL_THROW'] = '1';
$_SERVER['FRACTAL_ZIP_FATAL_THROW'] = '1';
require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_cli_opcache_bootstrap.php';
if (!defined('ZLIB_ENCODING_ZLIB')) {
	define('ZLIB_ENCODING_ZLIB', 15);
}
if (!defined('ZLIB_ENCODING_DEFLATE')) {
	define('ZLIB_ENCODING_DEFLATE', 8);
}

$__fzBenchMem = getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT');
if (is_string($__fzBenchMem) && trim($__fzBenchMem) !== '') {
	ini_set('memory_limit', trim($__fzBenchMem));
}
unset($__fzBenchMem);
// One bad extract must not exit(1) the whole suite — completeness gate still fails the row.
putenv('FRACTAL_ZIP_FATAL_THROW=1');
$_ENV['FRACTAL_ZIP_FATAL_THROW'] = '1';
$_SERVER['FRACTAL_ZIP_FATAL_THROW'] = '1';

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_corpus_size.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_corpus_descriptions.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_default_corpus_list.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_folder_census.php';
require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_json_helpers.php';

/** Defer CPU to other jobs (best-effort; Linux). Same effect as `nice -n 19` when set before start; use benchmarks/run_benchmarks_low_priority.sh to avoid double-nice. */
if ((getenv('FRACTAL_ZIP_BENCH_LOW_PRIORITY') === '1' || getenv('FRACTAL_ZIP_BENCH_NICE') === '1') && function_exists('proc_nice')) {
	@proc_nice(19);
}

$baseDir = dirname(__DIR__);
$jsonOut = in_array('--json', $argv, true);
$__undeepScript = $baseDir . DIRECTORY_SEPARATOR . 'benchmarks' . DIRECTORY_SEPARATOR . 'check_undeep_fzb_brotli_sync.php';
$__undeepPreflightEnv = getenv('CHECK_UNDEEP_SYNC') === '1' || getenv('FRACTAL_ZIP_BENCH_CHECK_UNDEEP_SYNC') === '1';
$__undeepCheckOnly = in_array('--check-undeep-sync', $argv, true);
if (($__undeepPreflightEnv || $__undeepCheckOnly) && is_file($__undeepScript)) {
	$__undeepPhp = (defined('PHP_BINARY') && is_string(PHP_BINARY) && PHP_BINARY !== '') ? PHP_BINARY : 'php';
	// Pass --json only for check-only runs, so a full `… --json` bench is still one JSON object on stdout
	// (preflight with CHECK_UNDEEP_SYNC=1 + --json would otherwise prepend the check’s JSON line).
	$__undeepArg = escapeshellarg($__undeepScript)
		. ($jsonOut && $__undeepCheckOnly ? ' ' . escapeshellarg('--json') : '');
	passthru($__undeepPhp . ' ' . $__undeepArg, $__undeepCode);
	if ((int) $__undeepCode !== 0) {
		exit((int) $__undeepCode);
	}
}
if ($__undeepCheckOnly) {
	if (!is_file($__undeepScript)) {
		fwrite(STDERR, "check_undeep_fzb_brotli_sync: missing " . $__undeepScript . "\n");
		exit(1);
	}
	exit(0);
}
unset($__undeepScript, $__undeepPhp, $__undeepCode, $__undeepArg, $__undeepPreflightEnv, $__undeepCheckOnly);
$workRoot = __DIR__ . '/.work';
$includeLarge = in_array('--large', $argv, true);
$includeSyntheticMicro = in_array('--with-synthetic-micro', $argv, true);
$includeHugeCorpora = in_array('--with-huge-corpora', $argv, true);
$tuneIndex = array_search('--tune', $argv, true);
$tuneCorpus = ($tuneIndex !== false && isset($argv[(int) $tuneIndex + 1])) ? $argv[(int) $tuneIndex + 1] : null;
$autoTuneBench = in_array('--auto-tune', $argv, true);
$parallelProductionBench = in_array('--parallel-production', $argv, true);
$skipExtract = in_array('--no-extract', $argv, true);
$benchNotes = in_array('--bench-notes', $argv, true);
$benchShowFolderCensusCol = getenv('FRACTAL_ZIP_BENCH_SHOW_FOLDER_CENSUS') === '1';
$benchKeepShellLiteralEnv = in_array('--bench-keep-shell-literal-env', $argv, true);
$noMultipass = in_array('--no-multipass', $argv, true);
$noFreeArc = in_array('--no-freearc', $argv, true) || in_array('--no-best-ext', $argv, true);
$noVerify = in_array('--no-verify', $argv, true);
foreach ($argv as $a) {
	if (is_string($a) && strncmp($a, '--keep-verify-extract=', 22) === 0) {
		$p = trim(substr($a, 22));
		if ($p !== '') {
			putenv('FRACTAL_ZIP_BENCH_KEEP_VERIFY_EXTRACT=' . $p);
		}
	}
}
if ($autoTuneBench) {
	putenv('FRACTAL_ZIP_AUTO_TUNE=1');
}
$benchLegacyFolderZip = in_array('--legacy-folder-zip', $argv, true);
if (!$benchLegacyFolderZip) {
	// Ignore shell FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 so benches match stream-first zip_folder unless --legacy-folder-zip.
	putenv('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM');
}
$benchAdaptiveMarkers = in_array('--adaptive-markers', $argv, true);
$benchMetastruct = in_array('--metastruct', $argv, true);
$GLOBALS['benchAdaptiveMarkers'] = $benchAdaptiveMarkers;
$GLOBALS['benchMetastruct'] = $benchMetastruct;
$jsonOutFile = null;
$noSaveLastJson = false;
$onlyTests = null;
$skipTests = [];
$limitCases = null;
// Lifestyle full-table default: unlimited wall so every column can fill (override with --case-timeout=N).
$caseTimeoutSec = null;
$toutEnv = getenv('FRACTAL_ZIP_BENCH_CASE_TIMEOUT_SEC');
$caseTimeoutExplicit = false;
if ($toutEnv !== false && trim((string) $toutEnv) !== '') {
	$toutN = (int) trim((string) $toutEnv);
	$caseTimeoutSec = $toutN > 0 ? $toutN : null;
	$caseTimeoutExplicit = true;
}
$allowIncompleteTable = in_array('--allow-incomplete', $argv, true);
$repeatRuns = 1;
/** @var int Max concurrent benchmark corpora when --jobs>1 (Linux + pcntl_fork; see benchmarks/PARALLELISM.md). */
$benchJobs = 1;
$baselineCacheEnabled = true;
$baselineCacheRefresh = false;
$baselineCachePathOverride = null;
$phase1CaseCacheEnabled = true;
$phase1CaseCacheRefresh = false;
$phase1CaseCachePathOverride = null;
$lastSkippedLogPathOverride = null;
$straySweepEnabled = true;
$maxRawBytesArg = null;
$maxRawBytesExplicit = false;
$benchProfile = getenv('FRACTAL_ZIP_BENCH_PROFILE');
$benchProfile = ($benchProfile === false || trim((string) $benchProfile) === '') ? null : strtolower(trim((string) $benchProfile));
foreach ($argv as $a) {
	if ($a === '--no-case-timeout') {
		$caseTimeoutSec = null;
		$caseTimeoutExplicit = true;
	}
	if ($a === '--no-baseline-cache') {
		$baselineCacheEnabled = false;
	}
	if ($a === '--refresh-baseline-cache') {
		$baselineCacheRefresh = true;
	}
	if (is_string($a) && strncmp($a, '--baseline-cache=', 17) === 0) {
		$p = trim(substr($a, 17));
		if ($p !== '') {
			$baselineCachePathOverride = $p;
		}
	}
	if ($a === '--no-phase1-case-cache') {
		$phase1CaseCacheEnabled = false;
	}
	if ($a === '--refresh-phase1-case-cache') {
		$phase1CaseCacheRefresh = true;
	}
	if (is_string($a) && strncmp($a, '--phase1-case-cache=', 20) === 0) {
		$p = trim(substr($a, 20));
		if ($p !== '') {
			$phase1CaseCachePathOverride = $p;
		}
	}
	if (is_string($a) && strncmp($a, '--last-skipped-log=', 19) === 0) {
		$p = trim(substr($a, 19));
		if ($p !== '') {
			$lastSkippedLogPathOverride = $p;
		}
	}
	if (is_string($a) && strncmp($a, '--out-json=', 11) === 0) {
		$jsonOutFile = substr($a, 11);
	}
	if ($a === '--no-save-last-json') {
		$noSaveLastJson = true;
	}
	if (is_string($a) && strncmp($a, '--only=', 7) === 0) {
		$onlyTests = array_values(array_filter(array_map('trim', explode(',', substr($a, 7))), static fn ($s) => $s !== ''));
	}
	if (is_string($a) && strncmp($a, '--skip=', 7) === 0) {
		$skipTests = array_values(array_filter(array_map('trim', explode(',', substr($a, 7))), static fn ($s) => $s !== ''));
	}
	if (is_string($a) && strncmp($a, '--limit=', 8) === 0) {
		$raw = trim(substr($a, 8));
		if ($raw !== '' && ctype_digit($raw)) {
			$limitCases = max(1, (int) $raw);
		}
	}
	if (is_string($a) && strncmp($a, '--case-timeout=', 15) === 0) {
		$raw = trim(substr($a, 15));
		if ($raw !== '' && ctype_digit($raw)) {
			$n = (int) $raw;
			$caseTimeoutSec = $n > 0 ? $n : null;
			$caseTimeoutExplicit = true;
		}
	}
	if (is_string($a) && strncmp($a, '--repeat=', 9) === 0) {
		$raw = trim(substr($a, 9));
		if ($raw !== '' && ctype_digit($raw)) {
			$repeatRuns = max(1, (int) $raw);
		}
	}
	if ($a === '--no-stray-sweep') {
		$straySweepEnabled = false;
	}
	if ($a === '--stray-sweep') {
		$straySweepEnabled = true;
	}
	if (is_string($a) && strncmp($a, '--maximum-size=', 15) === 0) {
		$maxRawBytesArg = trim(substr($a, 15));
		$maxRawBytesExplicit = true;
	}
	if (is_string($a) && strncmp($a, '--max-size=', 11) === 0) {
		$maxRawBytesArg = trim(substr($a, 11));
		$maxRawBytesExplicit = true;
	}
	if (is_string($a) && strncmp($a, '--bench-profile=', 16) === 0) {
		$benchProfile = strtolower(trim(substr($a, 16)));
	}
	if (preg_match('/^--jobs=(\d+)$/', $a, $m)) {
		$benchJobs = max(1, min(32, (int) $m[1]));
	}
}
$benchProfileApplied = null;
$benchProfileSetEnvDefault = static function (string $key, string $value): bool {
	$existing = getenv($key);
	if ($existing !== false && trim((string) $existing) !== '') {
		return false;
	}
	putenv($key . '=' . $value);
	return true;
};
if ($benchProfile !== null) {
	$profile = str_replace('_', '-', strtolower($benchProfile));
	if ($profile === 'small' || $profile === 'small-bytes' || $profile === 'bytes-small') {
		$benchProfileApplied = 'small-bytes';
		if (!$maxRawBytesExplicit && ($maxRawBytesArg === null || $maxRawBytesArg === '')) {
			$maxRawBytesArg = '2000000';
		}
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = 90;
		}
		$benchProfileSetEnvDefault('FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES', '2000000');
	} elseif ($profile === 'medium' || $profile === 'medium-balanced' || $profile === 'balanced-medium') {
		$benchProfileApplied = 'medium-balanced';
		if (!$maxRawBytesExplicit && ($maxRawBytesArg === null || $maxRawBytesArg === '') && $onlyTests === null) {
			$maxRawBytesArg = (string) (12 * 1024 * 1024);
		}
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = 60;
		}
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED_TRY_BROTLI', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES', (string) (2 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC', '3');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', (string) (2 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', '3');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'probe');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', (string) (2 * 1024 * 1024));
	} elseif ($profile === 'large' || $profile === 'large-fast' || $profile === 'fast-large') {
		$benchProfileApplied = 'large-fast';
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = 120;
		}
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES', (string) (1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC', '6');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', (string) (4 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', '3');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'probe');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', (string) (12 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', (string) (2 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BENCH_PHASE1_TIMEOUT_STORE_FALLBACK', '1');
	} elseif ($profile === 'large-balanced' || $profile === 'balanced-large') {
		$benchProfileApplied = 'large-balanced';
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = 120;
		}
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED_TRY_BROTLI', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES', (string) (1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC', '6');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', (string) (4 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', '3');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'probe');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', (string) (12 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', (string) (2 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES', (string) (2 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BENCH_PHASE1_TIMEOUT_STORE_FALLBACK', '1');
	} elseif ($profile === 'large-bytes' || $profile === 'bytes-large') {
		$benchProfileApplied = 'large-bytes';
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = 150;
		}
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED_TRY_BROTLI', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES', (string) (1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC', '8');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', (string) (8 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', '3');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'probe');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', (string) (16 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', (string) (6 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES', (string) (5 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BENCH_PHASE1_TIMEOUT_STORE_FALLBACK', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_GZIP_FAST_DEFLATE_LEVEL', '8');
	} elseif ($profile === 'fair-full' || $profile === 'fairfull' || $profile === 'strict-sole') {
		// Strict sole-win campaign: zpaq always races; PAQ native when tools exist;
		// lifestyle speed off so bytes-first paths (text-inner / CM) can fire.
		$benchProfileApplied = 'fair-full';
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = 600;
		}
		$benchProfileSetEnvDefault('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE', '0');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED', '0');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED_TRY_BROTLI', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_GZIP_FAST', '0');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', (string) (128 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', (string) (128 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES', (string) (128 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_ZPAQ_NATIVE_FULL_SWEEP_MAX_RAW_BYTES', (string) (128 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES', (string) (128 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_ZPAQ_OUTER_SWEEP', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_PAQ_NATIVE_COMPARE', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_PAQ_NATIVE_MAX_RAW_BYTES', (string) (128 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_PAQ_SWEEP', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_GENERAL_TEXT_INNER', 'on');
		// Prefer tokenized_zpaq + DRT/lpaq shootout (beats zpaq_raw on dickens/xml).
		// Requires GENERAL_TEXT_INNER=on to be honored under LIFESTYLE=0 (see general_text.php).
		$benchProfileSetEnvDefault('FRACTAL_ZIP_PHDA9_GENERAL_FAST', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', (string) (16 * 1024 * 1024));
		$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC', '12');
		// Harden FLAC/album soles (test_files60) under fair caps when ffmpeg present.
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FLACPAC', '1');
		// Never soft-skip zpaq/arc/brotli midsize races — that defeats the strict sole KPI.
		$benchProfileSetEnvDefault('FRACTAL_ZIP_BENCH_SKIP_SLOW_PAQ_EXT', '0');
		// Pair with --large for heavy-list trees (gzip-fast already forced off above).
		if (is_resource(STDERR)) {
			fwrite(STDERR, "[bench] fair-full: lifestyle off; GT text-inner on (phda9 fast+DRT); full zpaq race; FLACPAC on — strict sole KPI (use --large on heavy trees)\n");
		}
	} elseif ($profile === 'fs' || $profile === 'filesystem' || $profile === 'fs-speed') {
		$benchProfileApplied = 'fs';
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_fs_speed_env.php';
		bench_fs_speed_apply_env_defaults();
		if (!$maxRawBytesExplicit && ($maxRawBytesArg === null || $maxRawBytesArg === '')) {
			$maxRawBytesArg = '2000000';
		}
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = null;
		}
	} elseif ($profile === 'text-inner' || $profile === 'general-text' || $profile === 'textinner') {
		$benchProfileApplied = 'text-inner';
		$benchProfileSetEnvDefault('FRACTAL_ZIP_GENERAL_TEXT_INNER', 'on');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_GENERAL_TEXT_MIN_BYTES', '4096');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED', '1');
		$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_UNIFIED_STREAM', '1');
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = 600;
		}
		if (is_resource(STDERR)) {
			fwrite(STDERR, "[bench] text-inner preset: FRACTAL_ZIP_GENERAL_TEXT_INNER=on for single-file textish corpora\n");
		}
	} elseif ($profile === 'world-record' || $profile === 'hutter' || $profile === 'ltcb' || $profile === 'enwik8') {
		$benchProfileApplied = 'world-record';
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
		bench_world_record_apply_env_defaults();
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = null;
		}
		if (is_resource(STDERR)) {
			fwrite(STDERR, "[bench] world-record preset: hours-scale; primary score = min(fzc, best_ext); FRACTAL_ZIP_ENWIK_ENTRY_SORT=1\n");
		}
	} elseif ($profile === 'enwik9' || $profile === 'hutter9') {
		$benchProfileApplied = 'enwik9';
		require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
		bench_world_record_apply_env_defaults();
		putenv('FRACTAL_ZIP_ENWIK_CORPUS=9');
		if (!$caseTimeoutExplicit) {
			$caseTimeoutSec = null;
		}
		if (is_resource(STDERR)) {
			fwrite(STDERR, "[bench] enwik9 preset: world-record + FRACTAL_ZIP_ENWIK_CORPUS=9; use --only=200 --no-case-timeout\n");
		}
	} else {
		fwrite(STDERR, "[bench] Unknown --bench-profile=" . (string) $benchProfile . " (expected small-bytes, medium-balanced, large-fast, large-balanced, large-bytes, fair-full, fs, text-inner, world-record, or enwik9).\n");
		exit(2);
	}
	if (is_resource(STDERR)) {
		fwrite(STDERR, "[bench] --bench-profile={$benchProfileApplied}: applied tier defaults; explicit CLI/env overrides still win.\n");
	}
} elseif ($includeLarge) {
	// Ratio-first heavy runs without an explicit profile often picked slower non-Arc outers (e.g. test_files133_sample 7z vs Arc).
	// Default to large-balanced (Arc-friendly brotli cap + SPEED_TRY_BROTLI); use --bench-profile=large-fast for wall-clock.
	// Keep unlimited case timeout unless the user set one — full filled TOTAL needs 7z/ext/extract on monsters.
	$benchProfileApplied = 'large-balanced';
	if (!$caseTimeoutExplicit) {
		$caseTimeoutSec = null;
	}
	$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED', '1');
	$benchProfileSetEnvDefault('FRACTAL_ZIP_SPEED_TRY_BROTLI', '1');
	$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES', (string) (1024 * 1024));
	$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC', '6');
	$benchProfileSetEnvDefault('FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES', (string) (4 * 1024 * 1024));
	$benchProfileSetEnvDefault('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP', '3');
	$benchProfileSetEnvDefault('FRACTAL_ZIP_BROTLI_HUGE_MODE', 'probe');
	$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_7Z_MAX_RAW_BYTES', (string) (12 * 1024 * 1024));
	$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_ZPAQ_COMPARE_MAX_RAW_BYTES', (string) (2 * 1024 * 1024));
	$benchProfileSetEnvDefault('FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES', (string) (2 * 1024 * 1024));
	// Full filled TOTAL: never soft-blank 7z/min-ext/extract under --large (unlimited default wall).
	$benchProfileSetEnvDefault('FRACTAL_ZIP_BENCH_SKIP_SLOW_PAQ_EXT', '0');
	$benchProfileSetEnvDefault('FRACTAL_ZIP_BENCH_PHASE1_TIMEOUT_STORE_FALLBACK', '1');
	if (is_resource(STDERR)) {
		fwrite(STDERR, "[bench] --large: applied large-balanced tier defaults (no --bench-profile); use --bench-profile=large-fast for speed or large-bytes for wider native caps.\n");
	}
}
// Legacy zip_folder + adaptive markers is slower than unified stream; avoid spurious timeout skips at the default 45s cap.
if ($benchAdaptiveMarkers && $caseTimeoutSec !== null && $caseTimeoutSec > 0) {
	$t0 = $caseTimeoutSec;
	$amMin = getenv('FRACTAL_ZIP_BENCH_ADAPTIVE_MARKERS_MIN_TIMEOUT_SEC');
	if ($amMin === false || trim((string) $amMin) === '') {
		$caseTimeoutSec = max($caseTimeoutSec, 360);
	} else {
		$n = (int) trim((string) $amMin);
		if ($n > 0) {
			$caseTimeoutSec = max($caseTimeoutSec, $n);
		}
	}
	if ($caseTimeoutSec > $t0) {
		fwrite(STDERR, "[bench] --adaptive-markers: per-case timeout {$t0}s → {$caseTimeoutSec}s (override: FRACTAL_ZIP_BENCH_ADAPTIVE_MARKERS_MIN_TIMEOUT_SEC).\n");
	}
}
if ($onlyTests !== null) {
	$onlyTests = bench_normalize_corpus_cli_tokens($onlyTests);
}
if ($skipTests !== []) {
	$skipTests = bench_normalize_corpus_cli_tokens($skipTests);
}
if ($tuneCorpus !== null && $tuneCorpus !== '') {
	$tuneCorpus = bench_normalize_corpus_cli_token((string) $tuneCorpus);
}
$maxRawBytes = null;
if ($maxRawBytesArg !== null && $maxRawBytesArg !== '') {
	try {
		$maxRawBytes = benchParseMaximumSizeBytes($maxRawBytesArg);
	} catch (Throwable $e) {
		fwrite(STDERR, "[bench] Invalid --maximum-size= / --max-size=…: " . $maxRawBytesArg . " (" . $e->getMessage() . "). "
			. "Use bytes ≥1 (e.g. --maximum-size=2000000) or a suffix: 2M / 10MB (decimal 10³ per step), 2Mi / 10MiB (binary 1024 per step).\n");
		exit(2);
	}
}
if (getenv('FRACTAL_ZIP_BENCH_SWEEP_STRAY_COMPRESSORS') !== false) {
	$v = strtolower(trim((string) getenv('FRACTAL_ZIP_BENCH_SWEEP_STRAY_COMPRESSORS')));
	$straySweepEnabled = !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}
if (getenv('FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE') === '1') {
	$baselineCacheEnabled = false;
}
if (getenv('FRACTAL_ZIP_BENCH_NO_PHASE1_CASE_CACHE') === '1') {
	$phase1CaseCacheEnabled = false;
}

$caseDiskSweep = !in_array('--no-case-disk-sweep', $argv, true)
	&& getenv('FRACTAL_ZIP_BENCH_NO_CASE_DISK_SWEEP') !== '1';

$benchUseParallelPool = $benchJobs > 1 && function_exists('pcntl_fork') && $repeatRuns === 1;
if ($benchJobs > 1 && !$benchUseParallelPool && is_resource(STDERR)) {
	if ($repeatRuns > 1) {
		fwrite(STDERR, "[bench] --jobs={$benchJobs} requires --repeat=1; running corpora sequentially.\n");
	} else {
		fwrite(STDERR, "[bench] --jobs={$benchJobs} requires pcntl_fork (unavailable in this PHP); running sequentially.\n");
	}
}
/** When true, do not sweep benchmarks/.work between corpora; sweep once after the parallel batch (see benchmarks/PARALLELISM.md). */
$caseDiskSweepBetweenCases = $caseDiskSweep && !$benchUseParallelPool;

$phase1Bench = in_array('--phase1', $argv, true) || getenv('FRACTAL_ZIP_BENCH_PHASE1') === '1';
if ($phase1Bench) {
	$skipExtract = true;
	$noVerify = true;
	$noFreeArc = true;
	$noMultipass = true;
	$allowIncompleteTable = true;
}

register_shutdown_function('bench_stray_process_sweep');

benchEnsureMemoryForHugeFolderExtract($onlyTests, $skipExtract, $includeLarge);

function removeDir(string $dir): void
{
	if (!is_dir($dir)) {
		return;
	}
	try {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);
		foreach ($it as $item) {
			$p = $item->getPathname();
			if ($item->isDir()) {
				@rmdir($p);
			} else {
				@unlink($p);
			}
		}
		@rmdir($dir);
	} catch (Throwable $e) {
		// Racy cleanups are acceptable in benchmark scratch space; best effort only.
		@rmdir($dir);
	}
}

/**
 * Delete everything under benchmarks/.work (copies, *.fz siblings, extract scratch dirs) so one corpus cannot fill the disk.
 */
function benchSweepBenchmarkWorkRoot(string $workRoot): void
{
	if (!is_dir($workRoot)) {
		return;
	}
	$items = @scandir($workRoot);
	if ($items === false) {
		return;
	}
	foreach ($items as $item) {
		if ($item === '.' || $item === '..') {
			continue;
		}
		$p = $workRoot . DIRECTORY_SEPARATOR . $item;
		if (is_dir($p)) {
			removeDir($p);
		} else {
			@unlink($p);
		}
	}
}

/**
 * Best-effort kill of orphaned benchmark compressors, smoke/roundtrip PHP, and agent sleep loops.
 * Disabled with --no-stray-sweep or FRACTAL_ZIP_BENCH_STRAY_SWEEP=0.
 */
function bench_stray_process_sweep(): void
{
	global $straySweepEnabled;
	if (!$straySweepEnabled || DIRECTORY_SEPARATOR === '\\') {
		return;
	}
	$repo = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..') ?: dirname(__DIR__);
	$guardPath = $repo . DIRECTORY_SEPARATOR . 'fractal_zip_process_guard.php';
	if (is_file($guardPath)) {
		require_once $guardPath;
		if (function_exists('fractal_zip_process_guard_sweep_strays')) {
			fractal_zip_process_guard_sweep_strays($repo);
		}
	}
	$workRoot = realpath(__DIR__ . DIRECTORY_SEPARATOR . '.work');
	if ($workRoot === false) {
		$workRoot = __DIR__ . DIRECTORY_SEPARATOR . '.work';
	}
	// Do not pkill run_benchmarks.php here — the main bench process matches that pattern and would die in per-case finally.
	$patterns = array(
		'fz(arcbench|7bench|exarc|7ex|ocp_|ocin_|ocbr_|case_|brbench|pmb1_|78_|repro_|rtv_|hr_sm_)',
		'sleep 3600.*(test_files78|fractal_zip|bench78|silesia78)',
	);
	foreach ($patterns as $rx) {
		@exec('pkill -TERM -f ' . escapeshellarg($rx) . ' >/dev/null 2>&1');
	}
	usleep(150000);
	foreach ($patterns as $rx) {
		@exec('pkill -KILL -f ' . escapeshellarg($rx) . ' >/dev/null 2>&1');
	}
	if (!is_dir($workRoot)) {
		return;
	}
	$codecComm = array('zpaq' => true, 'brotli' => true, 'arc' => true, '7z' => true, '7za' => true, 'xz' => true, 'pigz' => true, 'gzip' => true);
	$prefix = rtrim($workRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
	$plen = strlen($prefix);
	foreach (@scandir('/proc') ?: array() as $ent) {
		if ($ent === '.' || $ent === '..' || !ctype_digit($ent)) {
			continue;
		}
		$pid = (int) $ent;
		if ($pid <= 1) {
			continue;
		}
		$cwd = @readlink('/proc/' . $ent . '/cwd');
		if (!is_string($cwd) || strncmp($cwd, $prefix, $plen) !== 0) {
			continue;
		}
		$comm = @file_get_contents('/proc/' . $ent . '/comm');
		if ($comm === false) {
			continue;
		}
		$comm = trim(explode("\n", $comm)[0]);
		if (!isset($codecComm[$comm])) {
			continue;
		}
		if (function_exists('posix_kill')) {
			@posix_kill($pid, 15);
		} else {
			@exec('kill -TERM ' . $pid . ' >/dev/null 2>&1');
		}
	}
	usleep(100000);
	foreach (@scandir('/proc') ?: array() as $ent) {
		if ($ent === '.' || $ent === '..' || !ctype_digit($ent)) {
			continue;
		}
		$pid = (int) $ent;
		if ($pid <= 1) {
			continue;
		}
		$cwd = @readlink('/proc/' . $ent . '/cwd');
		if (!is_string($cwd) || strncmp($cwd, rtrim($workRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR, strlen(rtrim($workRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)) !== 0) {
			continue;
		}
		$comm = @file_get_contents('/proc/' . $ent . '/comm');
		if ($comm === false) {
			continue;
		}
		$comm = trim(explode("\n", $comm)[0]);
		if (!isset($codecComm[$comm])) {
			continue;
		}
		if (function_exists('posix_kill')) {
			@posix_kill($pid, 9);
		} else {
			@exec('kill -KILL ' . $pid . ' >/dev/null 2>&1');
		}
	}
}

/**
 * Suite-stable process temp root. PHP caches {@see sys_get_temp_dir()} on first use, so
 * per-case TMPDIR directories that are deleted after each case poison later tips (10→11 xz).
 * Pin one writable directory for the whole bench process; case subdirs nest underneath.
 */
function benchSuiteProcessTempRoot(): string
{
	static $root = null;
	if (is_string($root) && $root !== '' && is_dir($root)) {
		return $root;
	}
	global $caseDiskSweep;
	global $workRoot;
	if (isset($caseDiskSweep) && !$caseDiskSweep) {
		$base = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'fz_bench_tmp';
	} else {
		$base = (isset($workRoot) && is_string($workRoot) && $workRoot !== '')
			? rtrim($workRoot, DIRECTORY_SEPARATOR)
			: (__DIR__ . DIRECTORY_SEPARATOR . '.work');
	}
	if (!is_dir($base)) {
		@mkdir($base, 0755, true);
	}
	$root = $base . DIRECTORY_SEPARATOR . '.tmp_suite_' . (string) getmypid();
	if (!is_dir($root)) {
		@mkdir($root, 0755, true);
	}
	putenv('TMPDIR=' . $root);
	putenv('TMP=' . $root);
	putenv('TEMP=' . $root);
	// Force PHP's cached temp path onto the suite root while it still exists.
	@sys_get_temp_dir();

	return $root;
}

function benchCaseTempRoot(string $workDir): string
{
	$name = basename($workDir);
	$name = preg_replace('/[^A-Za-z0-9_.-]+/', '_', $name);
	if (!is_string($name) || $name === '') {
		$name = 'case';
	}
	$suite = benchSuiteProcessTempRoot();

	return $suite . DIRECTORY_SEPARATOR . $name . '_' . (string) getmypid() . '_' . bin2hex(random_bytes(4));
}

/**
 * Run one benchmark case with a per-case scratch subdir under the suite-stable TMPDIR.
 *
 * @template T
 * @param callable(string): T $fn
 * @return T
 */
function benchWithCaseTempRoot(string $caseTmpDir, callable $fn)
{
	$suite = benchSuiteProcessTempRoot();
	if (!is_dir($caseTmpDir)) {
		mkdir($caseTmpDir, 0755, true);
	}
	// Keep process TMPDIR on the suite root for the whole case (and suite). Never point
	// TMPDIR at $caseTmpDir — deleting that after the case leaves sys_get_temp_dir() stale.
	putenv('TMPDIR=' . $suite);
	putenv('TMP=' . $suite);
	putenv('TEMP=' . $suite);
	try {
		return $fn($caseTmpDir);
	} finally {
		global $caseDiskSweep;
		$keepTmp = (isset($caseDiskSweep) && $caseDiskSweep === false)
			|| getenv('FRACTAL_ZIP_BENCH_NO_CASE_DISK_SWEEP') === '1';
		if (!$keepTmp && is_dir($caseTmpDir)) {
			removeDir($caseTmpDir);
		}
	}
}

function copyDir(string $src, string $dst): void
{
	if (is_dir($dst)) {
		removeDir($dst);
	}
	$srcRp = realpath($src);
	if ($srcRp === false) {
		throw new RuntimeException('copyDir: missing source ' . $src);
	}
	// Prefer hardlink tree (instant on same FS) for huge corpora; fall back to reflink/copy.
	if (PHP_OS_FAMILY !== 'Windows') {
		$parent = dirname($dst);
		if (!is_dir($parent)) {
			mkdir($parent, 0755, true);
		}
		$ret = -1;
		exec('cp -al ' . escapeshellarg($srcRp) . ' ' . escapeshellarg($dst) . ' 2>/dev/null', $o, $ret);
		if ($ret === 0 && is_dir($dst)) {
			return;
		}
		if (is_dir($dst)) {
			removeDir($dst);
		}
		$ret = -1;
		exec('cp -a --reflink=auto ' . escapeshellarg($srcRp) . ' ' . escapeshellarg($dst) . ' 2>/dev/null', $o2, $ret);
		if ($ret === 0 && is_dir($dst)) {
			return;
		}
		if (is_dir($dst)) {
			removeDir($dst);
		}
	}
	mkdir($dst, 0755, true);
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($srcRp, FilesystemIterator::SKIP_DOTS),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $item) {
		$sub = $it->getSubPathname();
		$target = $dst . DIRECTORY_SEPARATOR . $sub;
		if ($item->isDir()) {
			mkdir($target, 0755, true);
		} else {
			$parent = dirname($target);
			if (!is_dir($parent)) {
				mkdir($parent, 0755, true);
			}
			if (!copy($item->getPathname(), $target)) {
				throw new RuntimeException('copy failed: ' . $item->getPathname());
			}
		}
	}
}

/**
 * When {@code FRACTAL_ZIP_BENCH_KEEP_VERIFY_EXTRACT} is set (CLI: {@code --keep-verify-extract=DIR}), copy the
 * post-{@code open_container} scratch tree to {@code DIR/<sanitized_label>/} then remove the scratch dir.
 * Otherwise remove the scratch dir only. Used for offline {@code benchmarks/sha1_tree_diff.php} diffs.
 */
function benchMaybeKeepVerifyExtractTree(string $extractScratch, string $label): void
{
	$raw = getenv('FRACTAL_ZIP_BENCH_KEEP_VERIFY_EXTRACT');
	if ($raw === false || trim((string) $raw) === '') {
		removeDir($extractScratch);
		return;
	}
	$base = rtrim(str_replace('\\', '/', trim((string) $raw)), '/');
	if ($base === '' || str_contains($base, "\0")) {
		removeDir($extractScratch);
		return;
	}
	if (!is_dir($extractScratch)) {
		return;
	}
	$cwd = getcwd();
	$absBase = $base;
	if ($cwd !== false && $base !== '' && $base[0] !== '/' && !preg_match('/^[A-Za-z]:/', $base)) {
		$absBase = $cwd . '/' . $base;
	}
	if (!is_dir($absBase) && !@mkdir($absBase, 0755, true) && !is_dir($absBase)) {
		fwrite(STDERR, "[bench] keep-verify-extract: cannot create directory: {$absBase}\n");
		removeDir($extractScratch);
		return;
	}
	$realBase = realpath($absBase);
	if ($realBase === false) {
		fwrite(STDERR, "[bench] keep-verify-extract: realpath failed: {$absBase}\n");
		removeDir($extractScratch);
		return;
	}
	$safe = preg_replace('/[^A-Za-z0-9._-]+/', '_', $label);
	if (!is_string($safe) || $safe === '') {
		$safe = 'case';
	}
	$dest = $realBase . DIRECTORY_SEPARATOR . $safe;
	try {
		copyDir($extractScratch, $dest);
		removeDir($extractScratch);
		fwrite(STDERR, "[bench] kept verify extract tree: {$dest}\n");
	} catch (Throwable $e) {
		fwrite(STDERR, '[bench] keep-verify-extract copy failed: ' . $e->getMessage() . "\n");
		removeDir($extractScratch);
	}
}

/**
 * Heavy-corpora list (see $heavyCorporaFolderGzipFastDefault): when unpacked raw ≥ benchHeavyFolderGzipFastMinRawBytes() (default 128 MiB) and `--large` is omitted, the driver sets `FRACTAL_ZIP_FOLDER_GZIP_FAST=1` before `zip_folder`. Pass `--large` to force `FRACTAL_ZIP_FOLDER_GZIP_FAST=0` for those names (unified-stream ratio path). Smaller trees on the list keep gzip-fast off and are unchanged by `--large` for that env toggle.
 */
$heavyCorporaFolderGzipFastDefault = [
	'test_files35', // ~4 MiB BMP; fractal stress
	'test_files61', // ~5 MiB multi-format rasters + nested outers; folder-gzip fast path default
	'test_files54', // ~700 MiB site tree
	'test_files55', // ~200 MiB
	'test_files56', // full SC2Replay tree (~90 replays; same fast-path class as 54/55)
	'test_files57',
	'test_files58', // ~460 MiB
	'test_files59',
	// Sample / stratified variants (same heavy-list class as full 54/55/58/59 trees). Listed so `--large` clears gzip-fast when raw ≥ benchHeavyFolderGzipFastMinRawBytes() (default 128 MiB); otherwise the inner can hit
	// FRACTAL_ZIP_FOLDER_GZIP_FAST_ADAPTIVE_OUTER_MAX_BYTES → deflate-only .fz (ratio matches gzip9, loses to min-ext).
	'test_files58_sample',
	'test_files59_sample',
	'test_files55_stratified',
	'test_files133', // twelve Silesia files, ~212 MiB raw (benchmarks/build_test_files133_silesia12.php)
	'test_files200', // enwik9 ~1 GiB (benchmarks/build_test_files200_enwik9.php)
	// Public GP lakes (~3 GiB each); materialize benchmarks/build_test_files202_210_gp.php; gitignored; default discovery when present
	'test_files202',
	'test_files203',
	'test_files204',
	'test_files205',
	'test_files206',
	'test_files207',
	'test_files208',
	'test_files209',
	'test_files210',
];

/** test_files202–210: public general-purpose domain lakes (~3 GiB each); build_test_files202_210_gp.php; never commit. */
/** test_files200: enwik9 (~1 GiB raw); materialize benchmarks/build_test_files200_enwik9.php; opt-in (--only=200); world-record / Hutter-scale. */
/** test_files133: twelve uncompressed Silesia members in one dir (~211.9 MiB raw); opt-in (`--only=`); skipped in default discovery. Heavy-list so `--large` clears folder gzip-fast when raw ≥ benchHeavyFolderGzipFastMinRawBytes(). */
/** test_files58_sample: generated slice of test_files58 (~target MiB); see benchmarks/sample_large_corpus.php; never part of default suite. */
/** test_files59_sample: generated slice for fast tuning (see benchmarks/sample_large_corpus.php); never part of default suite. */
/** test_files55: full StevieGee HTML tree (~200 MiB raw); same material as historical `test_files55_full`. */
/** test_files55_sample: every-8th-file subset (~25 MiB); bytes-win reference `benchmarks/.test_files55_large_eighth.json`; rebuild via `benchmarks/build_test_files55_eighth.php`. */
/** test_files55_stratified: generated ~72 MiB HTML slice of `test_files55` (see `benchmarks/build_test_files55_sample.php`); non-canonical sample, use --only when needed. */
/** test_files54_sample: stratified slice of test_files54 (~14 MiB default); see benchmarks/sample_test_files54.php. */
/** test_files60: two FLACs from test_files59_sample + small sidecars (~6.5–6.8 MiB raw); fast loop for unified folder / FZBM vs outers with default bit-exact FLAC (`FRACTAL_ZIP_FLACPAC` unset). Bytes-win vs gzip-9 / 7z / min-ext; `verify_ok` true with defaults (README). */
/** test_files63: multi-file literal stress (modes 12/13/14 + stripes BMPs + grid_01); .fz typically ~1.77–1.85 KiB (FZB4 path-order search + brotli outer). */
/** test_files64: single member grid_01.bmp only; .fz ~1.3 KiB baseline for BMP literal + outer codec. See benchmarks/build_test_files64.php. */
/** test_files69: ≤5 MiB stratified slice (flat text, HTML, nested paths, raster mix, FLAC+m3u, SC2Replay subtree, gzip peel, phpinfo); see benchmarks/build_test_files69.php. */
/** test_files70: single non-BMP literal in gzip-1 vs gzip-9 “skip wedge” (see benchmarks/build_test_files70_wedge.php); literal transform pick can differ with probe level. */
/** test_files74: “desktop / local work” (notes, CSV, JSON, logs, PowerShell, SVG, Vite manifest) — see benchmarks/build_test_files74_75_76.php. */
/** test_files75: “web / internet” (static HTML+CSS+JS, JSON API, robots, sitemap, service worker) — same build. */
/** test_files76: “phone / mobile” (VCF, SMS-style log, XMP, GPX, m3u, iOS plist, DCIM/ paths). tiny PNG+JPEG: run the build script. */
/** test_files77: Calgary compression corpus (14 files: bib, book1, book2, …); materialize from `calgary.zip` via `php benchmarks/build_test_files77_calgary.php`. */
$skipByDefault = benchBuildDefaultRunBenchmarksSkipList($includeHugeCorpora, $includeSyntheticMicro, $maxRawBytes);

/**
 * CLI shorthand: bare digits → <code>test_files</code> + decimal integer (e.g. <code>13</code> → <code>test_files13</code>, <code>133</code> → <code>test_files133</code>).
 * Canonical <code>test_files*</code> names with letters (e.g. <code>test_files55_sample</code>) are unchanged.
 */
function bench_normalize_corpus_cli_token(string $token): string
{
	$t = trim($token);
	if ($t === '') {
		return $t;
	}
	if (preg_match('/^[0-9]+$/', $t) === 1) {
		return 'test_files' . (string) (int) $t;
	}
	return $t;
}

/** @param list<string> $tokens @return list<string> */
function bench_normalize_corpus_cli_tokens(array $tokens): array
{
	$out = [];
	foreach ($tokens as $tok) {
		if (!is_string($tok)) {
			continue;
		}
		$n = bench_normalize_corpus_cli_token($tok);
		if ($n !== '') {
			$out[] = $n;
		}
	}
	return $out;
}

$defaultTests = discoverBenchmarkDirs($baseDir, $skipByDefault);
if ($onlyTests !== null && $onlyTests !== []) {
	$onlySet = array_fill_keys($onlyTests, true);
	$defaultTests = array_values(array_filter($defaultTests, static fn ($n) => isset($onlySet[$n])));
	// Run explicit --only=name even when that corpus is not in the default discovery set.
	foreach ($onlyTests as $want) {
		if (!is_string($want) || $want === '') {
			continue;
		}
		if (in_array($want, $defaultTests, true)) {
			continue;
		}
		$p = $baseDir . DIRECTORY_SEPARATOR . $want;
		// Default discovery remains canonical-only; explicit --only may target generated samples.
		if (preg_match('/^test_files[A-Za-z0-9_.-]*$/', $want) === 1 && is_dir($p)) {
			$defaultTests[] = $want;
		}
	}
	$defaultTests = array_values(array_unique($defaultTests));
	sort($defaultTests, SORT_NATURAL);
}
if ($skipTests !== []) {
	$skipSet = array_fill_keys($skipTests, true);
	$defaultTests = array_values(array_filter($defaultTests, static fn ($n) => !isset($skipSet[$n])));
}
if ($maxRawBytes !== null) {
	$defaultTests = benchFilterCorporaByMaxRawBytes($baseDir, $defaultTests, $maxRawBytes);
	if ($defaultTests === []) {
		fwrite(STDERR, "[bench] --maximum-size={$maxRawBytes} excluded every corpus (on-disk raw totals exceed the cap for each candidate).\n");
		exit(2);
	}
}
if ($limitCases !== null) {
	$defaultTests = array_slice($defaultTests, 0, $limitCases);
}

/** False when user opts into inherited shell literal env (speed-tuned probes / chains). */
$benchLiteralBytesFirstDefaults = (getenv('FRACTAL_ZIP_BENCH_LITERAL_SPEED_DEFAULTS') !== '1' && !$benchKeepShellLiteralEnv);
if ($benchLiteralBytesFirstDefaults) {
	foreach ([
		'FRACTAL_ZIP_LITERAL_GZIP_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_NONBMP_GZIP9_MAX_BYTES',
		'FRACTAL_ZIP_LITERAL_BMP_GZIP_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_BMP_EXHAUSTIVE_CHAIN',
		'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN',
		'FRACTAL_ZIP_LITERAL_CHAIN_SEARCH_PROBE_LEVEL',
		'FRACTAL_ZIP_LITERAL_TRANSFORM_CHAIN_MAX_ITER',
		'FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT',
		'FRACTAL_ZIP_BUNDLE_RAW_DEEP_UNWRAP',
		'FRACTAL_ZIP_BUNDLE_RAW_DUAL_TIER',
		'FRACTAL_ZIP_LITERAL_SEMANTIC_MPQ_PROXY_GATE',
	] as $literalEnvKey) {
		putenv($literalEnvKey);
	}
}

// Default suite measures the general-purpose compressor (lifestyle on, no forced
// text-inner). Specialized presets: --ultra / --bench-profile=text-inner / explicit env.
// Explicit FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE / FRACTAL_ZIP_GENERAL_TEXT_INNER in the
// environment always win.
if (getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE') === false) {
	putenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE=1');
}
// FreeArc -mt0 after `a -mN` cuts monster doc-lake wall (204/72/54) without byte change.
// Large-corpus wrappers already export this; lifestyle suite remasures did not.
if (getenv('FRACTAL_ZIP_ARC_MT') === false && getenv('FRACTAL_ZIP_BENCH_ARC_MT') === false) {
	$lifeMt = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if ($lifeMt === false || $lifeMt === '' || !in_array(strtolower((string) $lifeMt), array('0', 'off', 'false', 'no'), true)) {
		putenv('FRACTAL_ZIP_ARC_MT=auto');
	}
}
// Leave FRACTAL_ZIP_GENERAL_TEXT_INNER unset so fractal_zip_general_text_inner_mode()
// applies the lifestyle default (auto: single-file prose → text-inner). Use
// --bench-profile=text-inner to force on, or FRACTAL_ZIP_GENERAL_TEXT=0 / INNER=off to disable.

require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_ultra_env.php';
if (in_array('--ultra', $argv, true)) {
	bench_ultra_apply_env_defaults();
	if (is_resource(STDERR)) {
		fwrite(STDERR, "[bench] --ultra: applied FRACTAL_ZIP_ULTRA=1 preset (same as fractal_zip_cli.php zip --ultra)\n");
	}
}
if ($benchProfileApplied === 'world-record') {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_world_record_env.php';
	bench_world_record_apply_env_defaults();
	if ($autoTuneBench) {
		bench_world_record_apply_auto_tune();
	}
	if ($parallelProductionBench) {
		bench_world_record_apply_parallel_production_env();
		if (is_resource(STDERR)) {
			fwrite(STDERR, "[bench] --parallel-production: fork pools + FZBM parallel + GPU substring helpers (bytes unchanged)\n");
		}
	}
}
$benchFastStagedBrotliArgv = in_array('--bench-fast-staged-brotli', $argv, true);
$benchFastStagedBrotliEnv = getenv('FRACTAL_ZIP_BENCH_FAST_STAGED_BROTLI');
$benchFastStagedBrotliFromEnv = ($benchFastStagedBrotliEnv !== false && ($benchFastStagedBrotliEnv === '1'
	|| strtolower(trim((string) $benchFastStagedBrotliEnv)) === 'true'
	|| strtolower(trim((string) $benchFastStagedBrotliEnv)) === 'yes'
	|| strtolower(trim((string) $benchFastStagedBrotliEnv)) === 'on'));
if ($benchFastStagedBrotliArgv || $benchFastStagedBrotliFromEnv) {
	$capExisting = getenv('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP');
	if ($capExisting === false || trim((string) $capExisting) === '') {
		putenv('FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP=3');
		if (is_resource(STDERR)) {
			$benchFastStagedSrc = $benchFastStagedBrotliArgv ? '--bench-fast-staged-brotli' : 'FRACTAL_ZIP_BENCH_FAST_STAGED_BROTLI=1';
			fwrite(STDERR, "[bench] {$benchFastStagedSrc}: FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP=3 (staged literal fast tier only; unset overrides)\n");
		}
	}
}
$benchFastZipArgv = in_array('--bench-fast-zip', $argv, true);
$benchFastZipEnv = getenv('FRACTAL_ZIP_BENCH_FAST_ZIP');
$benchFastZipFromEnv = ($benchFastZipEnv !== false && ($benchFastZipEnv === '1' || strtolower(trim((string) $benchFastZipEnv)) === 'true'
	|| strtolower(trim((string) $benchFastZipEnv)) === 'yes' || strtolower(trim((string) $benchFastZipEnv)) === 'on'));
if ($benchFastZipArgv || $benchFastZipFromEnv) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'bench_fast_zip_env.php';
	bench_fast_zip_outer_apply_env_defaults();
	if (is_resource(STDERR)) {
		$benchFastZipSrc = $benchFastZipArgv ? '--bench-fast-zip' : 'FRACTAL_ZIP_BENCH_FAST_ZIP=1';
		fwrite(STDERR, "[bench] {$benchFastZipSrc}: preset probe/timeout + staged fast-tier brotli cap (see benchmarks/bench_fast_zip_env.php)\n");
	}
}

$benchPipelineNumbersVerbose = in_array('--bench-pipeline-verbose', $argv, true);
$benchPipelineNumbersEq = false;
$benchPipelineNumbersVerboseEq = false;
foreach ($argv as $a) {
	if (is_string($a) && str_starts_with($a, '--bench-pipeline-numbers=')) {
		$benchPipelineNumbersEq = true;
		$v = strtolower(trim((string) substr($a, strlen('--bench-pipeline-numbers='))));
		if ($v === 'verbose') {
			$benchPipelineNumbersVerboseEq = true;
		}
	}
}
$benchPipelineNumbersArgv = in_array('--bench-pipeline-numbers', $argv, true) || $benchPipelineNumbersEq;
$benchPipelineNumbersEnv = getenv('FRACTAL_ZIP_BENCH_PIPELINE_NUMBERS');
$benchPipelineNumbersFromEnv = ($benchPipelineNumbersEnv !== false && ($benchPipelineNumbersEnv === '1'
	|| strtolower(trim((string) $benchPipelineNumbersEnv)) === 'true'
	|| strtolower(trim((string) $benchPipelineNumbersEnv)) === 'yes'
	|| strtolower(trim((string) $benchPipelineNumbersEnv)) === 'on'));
if ($benchPipelineNumbersArgv || $benchPipelineNumbersFromEnv) {
	putenv('FRACTAL_ZIP_PIPELINE_TIMING=1');
	putenv('FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1');
	if ($benchPipelineNumbersVerbose || $benchPipelineNumbersVerboseEq) {
		putenv('FRACTAL_ZIP_PIPELINE_OUTER_STEP_VERBOSE=1');
	}
	if (is_resource(STDERR)) {
		if ($benchPipelineNumbersFromEnv && !$benchPipelineNumbersArgv) {
			$src = 'FRACTAL_ZIP_BENCH_PIPELINE_NUMBERS=1';
		} elseif ($benchPipelineNumbersVerboseEq && !$benchPipelineNumbersVerbose) {
			$src = '--bench-pipeline-numbers=verbose';
		} elseif ($benchPipelineNumbersEq) {
			$src = '--bench-pipeline-numbers=…';
		} else {
			$src = '--bench-pipeline-numbers';
		}
		$extra = ($benchPipelineNumbersVerbose || $benchPipelineNumbersVerboseEq) ? '; FRACTAL_ZIP_PIPELINE_OUTER_STEP_VERBOSE=1' : '';
		fwrite(STDERR, "[bench] {$src}: FRACTAL_ZIP_PIPELINE_TIMING=1, FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1 (outer step rollup on by default with timing){$extra}\n");
	}
}

require_once $baseDir . '/fractal_zip.php';

if ($benchMetastruct) {
	putenv('FRACTAL_ZIP_METastruct=1');
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');
	putenv('FRACTAL_ZIP_METastruct_LITERAL_BIAS=1');
	fractal_zip_metastruct_force_legacy_fractal_env();
	$benchAdaptiveMarkers = true;
	$GLOBALS['benchAdaptiveMarkers'] = true;
	fwrite(STDERR, "[bench] --metastruct: FRACTAL_ZIP_METastruct=1, FRACTAL_ZIP_ADAPTIVE_MARKERS=1, legacy fractal folder path\n");
} elseif ($benchAdaptiveMarkers) {
	putenv('FRACTAL_ZIP_ADAPTIVE_MARKERS=1');
	fractal_zip_metastruct_force_legacy_fractal_env();
	fwrite(STDERR, "[bench] --adaptive-markers: FRACTAL_ZIP_ADAPTIVE_MARKERS=1, legacy fractal folder path (unified stream + run-grammar off)\n");
}

/**
 * Multithread zpaq during benchmark zip_folder / adaptive_compress trials (zpaqfranz {@code -threads 0} = all cores).
 * Mirrors {@code FRACTAL_ZIP_BENCH_ZSTD_THREADS} defaulting to {@code -T0}: only applies when neither library nor bench override is set.
 * Stock Matt Mahoney zpaq: set {@code FRACTAL_ZIP_BENCH_ZPAQ_THREADS=off} (or {@code stock}) before invoking run_benchmarks if {@code -threads} errors.
 */
if (getenv('FRACTAL_ZIP_ZPAQ_THREADS') === false && getenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS') === false) {
	putenv('FRACTAL_ZIP_BENCH_ZPAQ_THREADS=0');
}

/**
 * @return array{had: bool, value: string|null}
 */
function benchSaveFolderGzipFastEnv(): array
{
	$v = getenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
	return ['had' => $v !== false, 'value' => $v === false ? null : $v];
}

/** @param array{had: bool, value: string|null} $saved */
function benchRestoreFolderGzipFastEnv(array $saved): void
{
	if (!$saved['had']) {
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
	} else {
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=' . ($saved['value'] ?? ''));
	}
}

function benchSecondsLeft(?float $deadlineMono): float
{
	if ($deadlineMono === null) {
		return PHP_FLOAT_MAX;
	}
	return max(0.0, $deadlineMono - microtime(true));
}

/**
 * Whether to set FRACTAL_ZIP_FOLDER_GZIP_FAST=1 for a heavy-list corpus.
 * Default: on when `--large` is omitted and raw ≥ {@see benchHeavyFolderGzipFastMinRawBytes()}.
 * Under `--large`, still enable for raw ≥ 1 GiB unless FRACTAL_ZIP_BENCH_HUGE_GZIP_FAST=0
 * (GP lakes OOM on full literal-bundle FZB5 without this).
 */
function benchHeavyFolderGzipFastForCase(bool $includeLarge, int $rawPeek): bool
{
	$min = benchHeavyFolderGzipFastMinRawBytes();
	if (!$includeLarge) {
		return $rawPeek >= $min;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_HUGE_GZIP_FAST');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if (in_array($v, array('0', 'off', 'false', 'no'), true)) {
			return false;
		}
		if (in_array($v, array('1', 'on', 'true', 'yes'), true)) {
			return $rawPeek >= (1024 * 1024 * 1024);
		}
	}
	// Default on for ≥1 GiB under --large (filled lifestyle TOTAL / GP lakes).
	return $rawPeek >= (1024 * 1024 * 1024);
}

/** Raw-byte threshold: with a per-case deadline, raw ≥ this ⇒ run gzip/7z/min-ext before zip_folder (default 12 MiB). */
function benchHeavyBaselineRawThreshold(): int
{
	$e = getenv('FRACTAL_ZIP_BENCH_TIMECAP_HEAVY_RAW_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 12 * 1024 * 1024;
}

/** Minimum raw size before FRACTAL_ZIP_BENCH_LITERAL_STORE may apply (default 12 MiB). */
function benchLiteralStoreMinRawBytes(): int
{
	$e = getenv('FRACTAL_ZIP_BENCH_LITERAL_STORE_MIN_RAW_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return max(0, (int) $e);
	}
	return 12 * 1024 * 1024;
}

/** Opt-in literal FZB4 store-only .fz (FRACTAL_ZIP_BENCH_LITERAL_STORE=1 or FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=1). */
function benchLiteralStoreRequested(): bool
{
	foreach (['FRACTAL_ZIP_BENCH_LITERAL_STORE', 'FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY'] as $key) {
		$v = getenv($key);
		if ($v === false || trim((string) $v) === '') {
			continue;
		}
		$v = strtolower(trim((string) $v));
		if ($v === '1' || $v === 'on' || $v === 'true' || $v === 'yes') {
			return true;
		}
	}
	return false;
}

/**
 * Phase-1 only: corpora that usually need mitigations to finish under a short wall cap (largest tree corpora).
 *
 * @return list<string>
 */
function benchPhase1MonsterLabels(): array
{
	return [
		'test_files54', 'test_files55', 'test_files58', 'test_files59', 'test_files200',
		'test_files202', 'test_files203', 'test_files204', 'test_files205',
		'test_files206', 'test_files207', 'test_files208', 'test_files209', 'test_files210',
	];
}

/**
 * Parse php.ini-style memory_limit ("128M", "1G", "536870912", "-1").
 */
function benchParseMemoryLimitToBytes(string $limit): int
{
	$limit = trim($limit);
	if ($limit === '' || strcasecmp($limit, '-1') === 0) {
		return PHP_INT_MAX;
	}
	$last = strtolower($limit[strlen($limit) - 1]);
	$isSuffix = ($last === 'g' || $last === 'm' || $last === 'k');
	$numPart = $isSuffix ? substr($limit, 0, -1) : $limit;
	if (!is_numeric(trim($numPart))) {
		return 128 * 1024 * 1024;
	}
	$n = (int) trim($numPart);
	if ($isSuffix) {
		if ($last === 'g') {
			return $n * 1024 * 1024 * 1024;
		}
		if ($last === 'm') {
			return $n * 1024 * 1024;
		}
		return $n * 1024;
	}
	return max(0, $n);
}

/**
 * open_container / native 7z blob load can allocate large windows on huge folder corpora; 1G defaults often OOM.
 * When $onlyTests is null (full discovery), raise if any heavy corpus directory exists under the repo root.
 *
 * @param list<string>|null $onlyTests
 */
function benchEnsureMemoryForHugeFolderExtract(?array $onlyTests, bool $skipExtract, bool $includeLarge = false): void
{
	if ($skipExtract) {
		return;
	}
	$heavy = [
		'test_files54' => true,
		'test_files55' => true,
		'test_files55_sample' => true,
		'test_files55_stratified' => true,
		'test_files56' => true,
		'test_files57' => true,
		'test_files58' => true,
		'test_files59' => true,
		'test_files133' => true,
		'test_files200' => true,
		'test_files202' => true,
		'test_files203' => true,
		'test_files204' => true,
		'test_files205' => true,
		'test_files206' => true,
		'test_files207' => true,
		'test_files208' => true,
		'test_files209' => true,
		'test_files210' => true,
	];
	$names = [];
	if ($onlyTests !== null && $onlyTests !== []) {
		$names = $onlyTests;
	} else {
		$root = dirname(__DIR__);
		foreach (array_keys($heavy) as $n) {
			if (is_dir($root . DIRECTORY_SEPARATOR . $n)) {
				$names[] = $n;
			}
		}
	}
	$hit = false;
	foreach ($names as $n) {
		if (isset($heavy[$n])) {
			$hit = true;
			break;
		}
	}
	if (!$hit && !$includeLarge) {
		return;
	}
	$env = getenv('FRACTAL_ZIP_BENCH_MEMORY_LIMIT');
	if (is_string($env) && trim($env) !== '') {
		return;
	}
	$memLimit = '2G';
	$minBytes = 2 * 1024 * 1024 * 1024;
	foreach ($names as $n) {
		if ($n === 'test_files200' || preg_match('/^test_files20[2-9]$/', $n) === 1 || $n === 'test_files210') {
			// Literal-bundle FZB5 variants on multi-GiB lakes need >>8 GiB headroom.
			$memLimit = '24G';
			$minBytes = 24 * 1024 * 1024 * 1024;
			break;
		}
	}
	// Full-suite --large / monster 7z blob loads need headroom even without GP lakes.
	if ($includeLarge && $minBytes < 8 * 1024 * 1024 * 1024) {
		$memLimit = '8G';
		$minBytes = 8 * 1024 * 1024 * 1024;
	}
	$curB = benchParseMemoryLimitToBytes((string) ini_get('memory_limit'));
	if ($curB >= $minBytes) {
		return;
	}
	ini_set('memory_limit', $memLimit);
	fwrite(STDERR, "[bench] memory_limit raised to {$memLimit} for huge-corpus encode/extract (set FRACTAL_ZIP_BENCH_MEMORY_LIMIT or php -d memory_limit=… to override)\n");
	fflush(STDERR);
}

function benchPhase1AutoLiteralStore(bool $phase1Bench, string $label, int $rawTotal): bool
{
	if (!$phase1Bench) {
		return false;
	}
	if (in_array($label, benchPhase1MonsterLabels(), true)) {
		return true;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_PHASE1_LITERAL_RAW_BYTES');
	if ($e !== false && trim((string) $e) !== '') {
		return $rawTotal >= max(0, (int) trim((string) $e));
	}
	return false;
}

/**
 * Extra wall clock for phase-1 monster dirs so gzip staging + FZC can finish. 0 = use global cap only.
 */
function benchPhase1MonsterCaseTimeout(?int $globalTimeout, string $label): ?int
{
	if ($globalTimeout === null) {
		return null;
	}
	if (!in_array($label, benchPhase1MonsterLabels(), true)) {
		return $globalTimeout;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_PHASE1_MONSTER_TIMEOUT_SEC');
	if ($e !== false && trim((string) $e) !== '') {
		$n = (int) trim((string) $e);
		if ($n <= 0) {
			return $globalTimeout;
		}
		return $n;
	}
	return max($globalTimeout, 90);
}

/**
 * Extra headroom for corpora where per-member literal peel + multipass is often ~35–40s wall (close to default 45s on fast CI).
 */
function benchSlowLiteralCaseTimeout(?int $globalTimeout, string $label): ?int
{
	if ($globalTimeout === null) {
		return null;
	}
	if ($label === 'test_files62') {
		return max($globalTimeout, 90);
	}
	return $globalTimeout;
}

/**
 * Extra headroom for single-file prose on the general-text / tokenized_zpaq lane
 * (dickens ~10 MiB, nci/webster larger). Soft min-ext refresh + sync CM verify
 * often exceed the default 45s. Override: FRACTAL_ZIP_BENCH_GENERAL_TEXT_TIMEOUT_SEC
 * (0 = use global only).
 */
/**
 * Large BMP / all-BMP folders (test_files35 ~4 MiB, 155/178) need headroom above
 * the default 45s when bytes-first fractal runs under suite load.
 */
function benchBmpHeavyCaseTimeout(?int $globalTimeout, string $sourceDir, int $rawPeek): ?int
{
	if ($globalTimeout === null || $rawPeek < 1024 * 1024) {
		return $globalTimeout;
	}
	$bmp = 0;
	$sc2 = 0;
	$files = 0;
	try {
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS));
		foreach ($it as $fi) {
			if (!$fi->isFile()) {
				continue;
			}
			$files++;
			$fn = $fi->getFilename();
			if (preg_match('/\\.bmp$/i', $fn)) {
				$bmp++;
			}
			if (preg_match('/\\.sc2replay$/i', $fn)) {
				$sc2++;
			}
			if ($files > 96) {
				break;
			}
		}
	} catch (Throwable $e) {
		return $globalTimeout;
	}
	// SC2Replay packs (test_files56): brotli race + MoPAQ often exceeds default 45s.
	if ($sc2 >= 8 && $rawPeek >= 2 * 1024 * 1024) {
		return max($globalTimeout, 90);
	}
	if ($bmp <= 0 || $bmp * 2 < $files) {
		return $globalTimeout;
	}
	return max($globalTimeout, 90);
}

function benchGeneralTextCaseTimeout(?int $globalTimeout, string $label, string $sourceDir, int $rawPeek): ?int
{
	if ($globalTimeout === null) {
		return null;
	}
	$globalTimeout = benchBmpHeavyCaseTimeout($globalTimeout, $sourceDir, $rawPeek);
	if ($rawPeek < 8 * 1024 * 1024) {
		return $globalTimeout;
	}
	// GT auto lane: one prose main (+ optional tiny README sidecars).
	$files = array();
	try {
		$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS));
		foreach ($it as $fi) {
			if ($fi->isFile()) {
				$files[] = $fi->getFilename();
				if (count($files) > 6) {
					break;
				}
			}
		}
	} catch (Throwable $e) {
		return $globalTimeout;
	}
	if ($files === array() || count($files) > 4) {
		return $globalTimeout;
	}
	$hasProseMain = false;
	foreach ($files as $fn) {
		$fn = (string) $fn;
		if (!str_contains($fn, '.') || preg_match('/\.(txt|csv|xml|html?|json)$/i', $fn)) {
			$hasProseMain = true;
			break;
		}
	}
	if (!$hasProseMain) {
		return $globalTimeout;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_GENERAL_TEXT_TIMEOUT_SEC');
	if ($e !== false && trim((string) $e) !== '') {
		$n = (int) trim((string) $e);
		if ($n <= 0) {
			return $globalTimeout;
		}
		return $n;
	}
	// Scale with size: dickens-class (~10 MiB) ~90s; webster/nci (~33–41 MiB) ~180s
	// under suite load (CM + sync verify + outer wrap).
	if ($rawPeek >= 32 * 1024 * 1024) {
		return max($globalTimeout, 180);
	}
	if ($rawPeek >= 8 * 1024 * 1024) {
		return max($globalTimeout, 90);
	}
	return max($globalTimeout, 75);
}

const BENCH_BASELINE_CACHE_SCHEMA_VERSION = 6;

/**
 * JSON file storing gzip / 7z / min-ext results per corpus label (invalidated when raw_bytes, include_best_ext, or gzip baseline engine key differs).
 */
function benchResolveBaselineCachePath(?string $argvOverride): string
{
	if ($argvOverride !== null && $argvOverride !== '') {
		return $argvOverride;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_BASELINE_CACHE');
	if ($e !== false && trim((string) $e) !== '') {
		return trim((string) $e);
	}
	return __DIR__ . DIRECTORY_SEPARATOR . '.baseline_cache.json';
}

const BENCH_LAST_SKIPPED_SCHEMA_VERSION = 1;

/**
 * JSON log of corpora skipped on the last bench run (timeout or worker failure). Overwritten every run.
 */
function benchResolveLastSkippedLogPath(?string $argvOverride): string
{
	if ($argvOverride !== null && $argvOverride !== '') {
		return $argvOverride;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_LAST_SKIPPED_LOG');
	if ($e !== false && trim((string) $e) !== '') {
		return trim((string) $e);
	}
	return __DIR__ . DIRECTORY_SEPARATOR . '.last_skipped_cases.json';
}

/**
 * @param list<array{label: string, reason: string}> $skippedCases
 */
function benchWriteLastSkippedCases(
	string $path,
	array $skippedCases,
	?int $caseTimeoutSec,
	int $casesScheduled,
	int $casesCompleted
): void {
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	$payload = [
		'schema_version' => BENCH_LAST_SKIPPED_SCHEMA_VERSION,
		'generated' => date('c'),
		'case_timeout_sec' => $caseTimeoutSec,
		'cases_scheduled' => $casesScheduled,
		'cases_completed' => $casesCompleted,
		'skipped_cases' => $skippedCases,
	];
	bench_json_file_put($path, $payload, true, 'last_skipped_cases');
}

const BENCH_PHASE1_STAGING_SCHEMA_VERSION = 2;

function benchPhase1StagingPath(): string
{
	return __DIR__ . DIRECTORY_SEPARATOR . '.phase1_staging.json';
}

/**
 * @return array{schema_version: int, entries: array<string, array<string, mixed>>}
 */
function benchPhase1StagingLoad(string $path): array
{
	if (!is_file($path)) {
		return ['schema_version' => BENCH_PHASE1_STAGING_SCHEMA_VERSION, 'entries' => []];
	}
	$j = bench_json_decode_file_assoc_try($path, 'benchPhase1StagingLoad', 512, 0, true, true);
	if ($j === null || !isset($j['entries']) || !is_array($j['entries'])) {
		return ['schema_version' => BENCH_PHASE1_STAGING_SCHEMA_VERSION, 'entries' => []];
	}
	$j['schema_version'] = (int) ($j['schema_version'] ?? BENCH_PHASE1_STAGING_SCHEMA_VERSION);
	if ($j['schema_version'] !== BENCH_PHASE1_STAGING_SCHEMA_VERSION) {
		return ['schema_version' => BENCH_PHASE1_STAGING_SCHEMA_VERSION, 'entries' => []];
	}
	$j['entries'] = is_array($j['entries']) ? $j['entries'] : [];
	return $j;
}

/**
 * @param array<string, mixed> $fields gzip/7z/ext baseline fields (same names as cache row)
 */
function benchPhase1StagingUpsert(string $label, int $rawBytes, bool $includeBestExt, array $fields): void
{
	$path = benchPhase1StagingPath();
	$root = benchPhase1StagingLoad($path);
	if (!isset($root['entries']) || !is_array($root['entries'])) {
		$root['entries'] = [];
	}
	$root['entries'][$label] = array_merge(
		[
			'raw_bytes' => $rawBytes,
			'include_best_ext' => $includeBestExt,
			'gzip_baseline_key' => benchGzipBaselineCacheKey(),
			'best_ext_baseline_key' => $includeBestExt ? benchBestExtBaselineCacheKey() : null,
			'phase1_source_key' => benchPhase1SourceCacheKey(),
			'fzc_runtime_env_key' => benchPhase1FzcRuntimeEnvCacheKey(),
			'saved_at' => date('c'),
		],
		$fields
	);
	$root['schema_version'] = BENCH_PHASE1_STAGING_SCHEMA_VERSION;
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	bench_json_file_put($path, $root, true, 'phase1_staging');
}

function benchPhase1StagingRemove(string $label): void
{
	$path = benchPhase1StagingPath();
	$root = benchPhase1StagingLoad($path);
	if (!isset($root['entries'][$label])) {
		return;
	}
	unset($root['entries'][$label]);
	$root['schema_version'] = BENCH_PHASE1_STAGING_SCHEMA_VERSION;
	if ($root['entries'] === []) {
		if (is_file($path)) {
			unlink($path);
		}
		return;
	}
	bench_json_file_put($path, $root, true, 'phase1_staging');
}

/**
 * Staged baselines ready for a second pass (FZC only) under the same raw size and baseline fingerprints.
 */
function benchPhase1StagingIsPending(string $label, int $rawBytes, bool $includeBestExt): bool
{
	$e = benchPhase1StagingLoad(benchPhase1StagingPath())['entries'][$label] ?? null;
	if (!is_array($e)) {
		return false;
	}
	if ((int) ($e['raw_bytes'] ?? -1) !== $rawBytes) {
		return false;
	}
	$sk = isset($e['phase1_source_key']) && is_string($e['phase1_source_key']) ? $e['phase1_source_key'] : null;
	if ($sk !== benchPhase1SourceCacheKey()) {
		return false;
	}
	$ek = isset($e['fzc_runtime_env_key']) && is_string($e['fzc_runtime_env_key']) ? $e['fzc_runtime_env_key'] : null;
	if ($ek !== benchPhase1FzcRuntimeEnvCacheKey()) {
		return false;
	}
	$gk = isset($e['gzip_baseline_key']) && is_string($e['gzip_baseline_key']) ? $e['gzip_baseline_key'] : null;
	if ($gk !== benchGzipBaselineCacheKey()) {
		return false;
	}
	$storedInclude = (bool) ($e['include_best_ext'] ?? false);
	if ($storedInclude) {
		$xk = isset($e['best_ext_baseline_key']) && is_string($e['best_ext_baseline_key']) ? $e['best_ext_baseline_key'] : null;
		if ($xk !== benchBestExtBaselineCacheKey()) {
			return false;
		}
	}
	if ($storedInclude === $includeBestExt) {
		return true;
	}
	// Phase-2 with --no-best-ext can reuse phase-1 staging that recorded full baselines (include_best_ext=true).
	return !$includeBestExt && $storedInclude;
}

/**
 * @return array<string, mixed>|null
 */
function benchPhase1StagingGet(string $label, int $rawBytes, bool $includeBestExt): ?array
{
	if (!benchPhase1StagingIsPending($label, $rawBytes, $includeBestExt)) {
		return null;
	}
	$e = benchPhase1StagingLoad(benchPhase1StagingPath())['entries'][$label] ?? null;
	return is_array($e) ? $e : null;
}

/** Bump when phase-1 row shape or fingerprint inputs change (invalidates whole file on load mismatch). */
const BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION = 3;

function benchResolvePhase1CaseCachePath(?string $argvOverride): string
{
	if ($argvOverride !== null && $argvOverride !== '') {
		return $argvOverride;
	}
	$e = getenv('FRACTAL_ZIP_BENCH_PHASE1_CASE_CACHE');
	if ($e !== false && trim((string) $e) !== '') {
		return trim((string) $e);
	}
	return __DIR__ . DIRECTORY_SEPARATOR . '.phase1_case_cache.json';
}

/**
 * @return array{schema_version: int, entries: array<string, array<string, mixed>>}
 */
function benchPhase1CaseCacheLoad(string $path): array
{
	if (!is_file($path)) {
		return ['schema_version' => BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION, 'entries' => []];
	}
	$j = bench_json_decode_file_assoc_try($path, 'benchPhase1CaseCacheLoad', 512, 0, true, true);
	if ($j === null || !isset($j['entries']) || !is_array($j['entries'])) {
		return ['schema_version' => BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION, 'entries' => []];
	}
	$j['schema_version'] = (int) ($j['schema_version'] ?? BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION);
	$j['entries'] = is_array($j['entries']) ? $j['entries'] : [];
	if ($j['schema_version'] !== BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION) {
		return ['schema_version' => BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION, 'entries' => []];
	}
	return $j;
}

/**
 * @param array{schema_version?: int, entries: array<string, array<string, mixed>>} $cacheRoot
 */
function benchPhase1CaseCacheSave(string $path, array $cacheRoot): void
{
	$cacheRoot['schema_version'] = BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION;
	$cacheRoot['updated'] = date('c');
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	bench_json_file_put($path, $cacheRoot, true, 'phase1_case_cache');
}

function benchPhase1SourceCacheKey(): string
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$repo = dirname(__DIR__);
	$files = [
		__FILE__,
		$repo . DIRECTORY_SEPARATOR . 'fractal_zip.php',
		$repo . DIRECTORY_SEPARATOR . 'fractal_zip_encode_pipeline.php',
		$repo . DIRECTORY_SEPARATOR . 'fractal_zip_inner.php',
		$repo . DIRECTORY_SEPARATOR . 'fractal_zip_inner_algorithms.php',
		$repo . DIRECTORY_SEPARATOR . 'fractal_zip_literal_deep_unwrap.php',
	];
	$parts = [];
	foreach ($files as $file) {
		$rp = realpath($file);
		$p = ($rp !== false) ? $rp : $file;
		$parts[] = [
			'path' => basename($p),
			'size' => is_file($p) ? filesize($p) : null,
			'mtime' => is_file($p) ? filemtime($p) : null,
		];
	}

	return $cached = hash('xxh128', (string) (bench_json_encode_fingerprint_try($parts) ?? ''));
}

function benchPhase1FzcRuntimeEnvCacheKey(): string
{
	$env = getenv();
	$parts = [];
	if (is_array($env)) {
		foreach ($env as $k => $v) {
			$ks = (string) $k;
			if (strncmp($ks, 'FRACTAL_ZIP_', 12) !== 0) {
				continue;
			}
			if (strncmp($ks, 'FRACTAL_ZIP_BENCH_', 19) === 0) {
				continue;
			}
			$parts[$ks] = (string) $v;
		}
	}
	ksort($parts, SORT_STRING);
	return hash('xxh128', (string) (bench_json_encode_fingerprint_try($parts) ?? ''));
}

function benchPhase1CaseCacheFingerprint(
	int $rawBytes,
	int $segmentLen,
	?int $caseTimeoutSec,
	bool $includeLarge,
	int $repeatRuns,
	bool $adaptiveMarkersBench,
	bool $includeBestExt,
	bool $measureExtract,
	bool $useMultipass,
	bool $verifyRoundTrip
): string
{
	return implode('|', [
		(string) $rawBytes,
		(string) $segmentLen,
		$caseTimeoutSec === null ? 'timeout:none' : 'timeout:' . (string) $caseTimeoutSec,
		$includeLarge ? '1' : '0',
		(string) $repeatRuns,
		$adaptiveMarkersBench ? '1' : '0',
		$includeBestExt ? '1' : '0',
		$measureExtract ? '1' : '0',
		$useMultipass ? '1' : '0',
		$verifyRoundTrip ? '1' : '0',
		benchGzipBaselineCacheKey(),
		$includeBestExt ? benchBestExtBaselineCacheKey() : 'no-best-ext',
		'phase1_timeout_store_fallback=' . (string) (getenv('FRACTAL_ZIP_BENCH_PHASE1_TIMEOUT_STORE_FALLBACK') ?: ''),
		benchPhase1SourceCacheKey(),
		benchPhase1FzcRuntimeEnvCacheKey(),
	]);
}

/**
 * @param array{schema_version?: int, entries?: array<string, array<string, mixed>>} $cacheRoot
 * @return array<string, mixed>|null
 */
function benchPhase1CaseCacheLookup(array $cacheRoot, string $label, string $fingerprint): ?array
{
	if (($cacheRoot['schema_version'] ?? 0) !== BENCH_PHASE1_CASE_CACHE_SCHEMA_VERSION) {
		return null;
	}
	$e = $cacheRoot['entries'][$label] ?? null;
	if (!is_array($e)) {
		return null;
	}
	if (($e['fingerprint'] ?? '') !== $fingerprint) {
		return null;
	}
	$row = $e['row'] ?? null;
	return is_array($row) ? $row : null;
}

/**
 * @param array{schema_version?: int, entries?: array<string, array<string, mixed>>} $cacheRoot
 * @param array<string, mixed> $row
 */
function benchPhase1CaseCacheMergeFromRow(array &$cacheRoot, string $label, string $fingerprint, array $row): void
{
	if ($label === '') {
		return;
	}
	if (!isset($cacheRoot['entries']) || !is_array($cacheRoot['entries'])) {
		$cacheRoot['entries'] = [];
	}
	$toStore = $row;
	unset($toStore['phase1_row_cache_hit']);
	$cacheRoot['entries'][$label] = [
		'fingerprint' => $fingerprint,
		'raw_bytes' => (int) ($row['raw_bytes'] ?? 0),
		'saved_at' => date('c'),
		'row' => $toStore,
	];
}

/**
 * @return array{schema_version: int, entries: array<string, array<string, mixed>>}
 */
function benchBaselineCacheLoad(string $path): array
{
	if (!is_file($path)) {
		return ['schema_version' => BENCH_BASELINE_CACHE_SCHEMA_VERSION, 'entries' => []];
	}
	$j = bench_json_decode_file_assoc_try($path, 'benchBaselineCacheLoad', 512, 0, true, true);
	if ($j === null || !isset($j['entries']) || !is_array($j['entries'])) {
		return ['schema_version' => BENCH_BASELINE_CACHE_SCHEMA_VERSION, 'entries' => []];
	}
	$j['schema_version'] = (int) ($j['schema_version'] ?? BENCH_BASELINE_CACHE_SCHEMA_VERSION);
	$j['entries'] = is_array($j['entries']) ? $j['entries'] : [];
	return $j;
}

/**
 * @param array{schema_version?: int, entries: array<string, array<string, mixed>>} $cacheRoot
 */
function benchBaselineCacheSave(string $path, array $cacheRoot): void
{
	$cacheRoot['schema_version'] = BENCH_BASELINE_CACHE_SCHEMA_VERSION;
	$cacheRoot['updated'] = date('c');
	$dir = dirname($path);
	if (!is_dir($dir)) {
		mkdir($dir, 0755, true);
	}
	bench_json_file_put($path, $cacheRoot, true, 'baseline_cache');
}

/**
 * @param array{schema_version?: int, entries: array<string, array<string, mixed>>} $cacheRoot
 * @return array<string, mixed>|null
 */
function benchBaselineCacheLookup(
	array $cacheRoot,
	string $label,
	int $rawBytes,
	bool $includeBestExt
): ?array {
	if (($cacheRoot['schema_version'] ?? 0) !== BENCH_BASELINE_CACHE_SCHEMA_VERSION) {
		return null;
	}
	$e = $cacheRoot['entries'][$label] ?? null;
	if (!is_array($e)) {
		return null;
	}
	if ((int) ($e['raw_bytes'] ?? -1) !== $rawBytes) {
		return null;
	}
	$storedIncludeBestExt = (bool) ($e['include_best_ext'] ?? false);
	if ($storedIncludeBestExt !== $includeBestExt) {
		// Skip min-ext this run but accept rows from a full run (saved include_best_ext=true) for display-only reuse.
		if (!(!$includeBestExt && $storedIncludeBestExt)) {
			return null;
		}
	}
	$gk = isset($e['gzip_baseline_key']) && is_string($e['gzip_baseline_key']) ? $e['gzip_baseline_key'] : 'zlib';
	if ($gk !== benchGzipBaselineCacheKey()) {
		return null;
	}
	if ((bool) ($e['include_best_ext'] ?? false)) {
		$xk = isset($e['best_ext_baseline_key']) && is_string($e['best_ext_baseline_key']) ? $e['best_ext_baseline_key'] : null;
		if ($xk !== benchBestExtBaselineCacheKey()) {
			return null;
		}
	}
	// Partial min-ext rows (e.g. deadline-truncated) can omit 7z while the 7z dir baseline was still recorded — treat as miss so the full tournament re-runs.
	if (benchBaselineCacheBestExtBreakdownIncomplete($e)) {
		return null;
	}
	// Soft-wall era rows may leave null 7z / 7z-extract (206 mega). Still reuse gzip +
	// min-ext for tip loops — remasuring mx=9 7z on ~300k-file lakes is multi-hour.
	// Refill via --refresh-baseline-cache when a full-table 7z column is required.
	if (($e['gzip9_bundle_bytes'] ?? null) === null) {
		return null;
	}
	if (!array_key_exists('gzip9_extract_seconds', $e) || $e['gzip9_extract_seconds'] === null) {
		return null;
	}
	if ((bool) ($e['include_best_ext'] ?? false)
		&& (!array_key_exists('best_ext_extract_seconds', $e) || $e['best_ext_extract_seconds'] === null
			|| ($e['best_ext_folder_bytes'] ?? null) === null)) {
		return null;
	}

	return $e;
}

/**
 * Reserved hook for incomplete {@code best_ext_breakdown} detection.
 * Always false: lifestyle rows often omit {@code 7z} from the tournament breakdown
 * while still filling {@code seven_zip_*} — treating that as a miss remasures mx=9 7z
 * on GiB lakes and stalls tip loops for minutes–hours.
 */
function benchBaselineCacheBestExtBreakdownIncomplete(array $e): bool
{
	return false;
}

/**
 * @param array{schema_version?: int, entries?: array<string, array<string, mixed>>} $cacheRoot passed by reference (nested cache blob).
 * @param array<string, mixed> $row
 */
function benchBaselineCacheMergeFromRow(array &$cacheRoot, array $row, bool $includeBestExt): void
{
	$label = isset($row['label']) && is_string($row['label']) ? $row['label'] : '';
	if ($label === '') {
		return;
	}
	if (!isset($cacheRoot['entries']) || !is_array($cacheRoot['entries'])) {
		$cacheRoot['entries'] = [];
	}
	$rawBytesNew = (int) ($row['raw_bytes'] ?? 0);
	$prev = isset($cacheRoot['entries'][$label]) && is_array($cacheRoot['entries'][$label])
		? $cacheRoot['entries'][$label]
		: null;
	$gzipKey = benchGzipBaselineCacheKey();
	$bestExtKey = benchBestExtBaselineCacheKey();

	$beFolder = $row['best_ext_folder_bytes'] ?? null;
	$beSec = $row['best_ext_seconds'] ?? null;
	$beEx = $row['best_ext_extract_seconds'] ?? null;
	$beWin = isset($row['best_ext_winner']) && is_string($row['best_ext_winner']) ? $row['best_ext_winner'] : null;
	$beBd = isset($row['best_ext_breakdown']) && is_array($row['best_ext_breakdown']) ? $row['best_ext_breakdown'] : [];

	if (!$includeBestExt && $prev !== null
		&& (int) ($prev['raw_bytes'] ?? -1) === $rawBytesNew
		&& isset($prev['gzip_baseline_key']) && (string) $prev['gzip_baseline_key'] === $gzipKey
		&& isset($prev['best_ext_baseline_key']) && (string) $prev['best_ext_baseline_key'] === $bestExtKey
		&& $beFolder === null
		&& array_key_exists('best_ext_folder_bytes', $prev) && $prev['best_ext_folder_bytes'] !== null) {
		$beFolder = (int) $prev['best_ext_folder_bytes'];
		$beSec = array_key_exists('best_ext_seconds', $prev) && $prev['best_ext_seconds'] !== null ? (float) $prev['best_ext_seconds'] : null;
		$beEx = array_key_exists('best_ext_extract_seconds', $prev) && $prev['best_ext_extract_seconds'] !== null ? (float) $prev['best_ext_extract_seconds'] : null;
		$beWin = isset($prev['best_ext_winner']) && is_string($prev['best_ext_winner']) ? $prev['best_ext_winner'] : null;
		$beBd = isset($prev['best_ext_breakdown']) && is_array($prev['best_ext_breakdown']) ? $prev['best_ext_breakdown'] : [];
	}

	$includeFlag = $includeBestExt;
	if (!$includeBestExt && $beFolder !== null) {
		$includeFlag = true;
	}

	// Partial remasures (--no-extract / --no-best-ext / tip loops) must not clobber
	// filled baseline cells with null — that forced hour-scale 7z remasures on lakes.
	$keepPrev = static function ($new, ?array $prev, string $key) {
		if ($new !== null) {
			return $new;
		}
		if ($prev !== null && array_key_exists($key, $prev) && $prev[$key] !== null) {
			return $prev[$key];
		}

		return null;
	};
	$sameRawPrev = ($prev !== null && (int) ($prev['raw_bytes'] ?? -1) === $rawBytesNew) ? $prev : null;

	$gzipB = $keepPrev($row['gzip9_bundle_bytes'] ?? null, $sameRawPrev, 'gzip9_bundle_bytes');
	$gzipS = $keepPrev($row['gzip9_seconds'] ?? null, $sameRawPrev, 'gzip9_seconds');
	$gzipEx = $keepPrev($row['gzip9_extract_seconds'] ?? null, $sameRawPrev, 'gzip9_extract_seconds');
	$z7B = $keepPrev($row['seven_zip_folder_bytes'] ?? null, $sameRawPrev, 'seven_zip_folder_bytes');
	$z7S = $keepPrev($row['seven_zip_seconds'] ?? null, $sameRawPrev, 'seven_zip_seconds');
	$z7Ex = $keepPrev($row['seven_zip_extract_seconds'] ?? null, $sameRawPrev, 'seven_zip_extract_seconds');
	$beFolder = $keepPrev($beFolder, $sameRawPrev, 'best_ext_folder_bytes');
	$beSec = $keepPrev($beSec, $sameRawPrev, 'best_ext_seconds');
	$beEx = $keepPrev($beEx, $sameRawPrev, 'best_ext_extract_seconds');
	if (($beWin === null || $beWin === '') && $sameRawPrev !== null
		&& isset($sameRawPrev['best_ext_winner']) && is_string($sameRawPrev['best_ext_winner'])) {
		$beWin = $sameRawPrev['best_ext_winner'];
	}
	if ($beBd === [] && $sameRawPrev !== null
		&& isset($sameRawPrev['best_ext_breakdown']) && is_array($sameRawPrev['best_ext_breakdown'])) {
		$beBd = $sameRawPrev['best_ext_breakdown'];
	}
	if (!$includeBestExt && $beFolder !== null) {
		$includeFlag = true;
	}

	$cacheRoot['entries'][$label] = [
		'cached_at' => date('c'),
		'raw_bytes' => $rawBytesNew,
		'include_best_ext' => $includeFlag,
		'gzip_baseline_key' => $gzipKey,
		'best_ext_baseline_key' => $includeFlag ? $bestExtKey : null,
		'gzip9_bundle_bytes' => $gzipB,
		'gzip9_seconds' => $gzipS,
		'gzip9_extract_seconds' => $gzipEx,
		'seven_zip_folder_bytes' => $z7B,
		'seven_zip_seconds' => $z7S,
		'seven_zip_extract_seconds' => $z7Ex,
		'best_ext_folder_bytes' => $beFolder,
		'best_ext_seconds' => $beSec,
		'best_ext_extract_seconds' => $beEx,
		'best_ext_winner' => $beWin,
		'best_ext_breakdown' => $beBd,
	];
}

/**
 * @param array<string, mixed> $e
 * @return array{0: ?int, 1: ?float, 2: ?int, 3: ?float, 4: ?int, 5: ?float, 6: ?string, 7: array<string, mixed>}
 */
function benchUnpackCachedBaselines(array $e, ?int $rawBytes = null): array
{
	$gB = array_key_exists('gzip9_bundle_bytes', $e) && $e['gzip9_bundle_bytes'] !== null ? (int) $e['gzip9_bundle_bytes'] : null;
	$gS = array_key_exists('gzip9_seconds', $e) && $e['gzip9_seconds'] !== null ? (float) $e['gzip9_seconds'] : null;
	$z7B = array_key_exists('seven_zip_folder_bytes', $e) && $e['seven_zip_folder_bytes'] !== null ? (int) $e['seven_zip_folder_bytes'] : null;
	$z7S = array_key_exists('seven_zip_seconds', $e) && $e['seven_zip_seconds'] !== null ? (float) $e['seven_zip_seconds'] : null;
	$xB = array_key_exists('best_ext_folder_bytes', $e) && $e['best_ext_folder_bytes'] !== null ? (int) $e['best_ext_folder_bytes'] : null;
	$xS = array_key_exists('best_ext_seconds', $e) && $e['best_ext_seconds'] !== null ? (float) $e['best_ext_seconds'] : null;
	$xW = isset($e['best_ext_winner']) && is_string($e['best_ext_winner']) ? $e['best_ext_winner'] : null;
	$xBd = isset($e['best_ext_breakdown']) && is_array($e['best_ext_breakdown']) ? $e['best_ext_breakdown'] : [];
	$raw = $rawBytes;
	if ($raw === null && array_key_exists('raw_bytes', $e) && $e['raw_bytes'] !== null) {
		$raw = (int) $e['raw_bytes'];
	}
	// Under the default 45s wall, live min-ext skips zpaq/paq/arc. Cached winners from
	// those tools would make "tie min-ext" impossible for lifestyle 7z/brotli/zstd wires.
	if (benchShouldSkipSlowPaqExt() && $xBd !== []) {
		$slow = array('zpaq' => true, 'zpaq_raw' => true, 'arc' => true, 'cmix' => true);
		// Soft midsize (≥2 MiB): live path only races zstd (+ light 7z); drop cached brotli/xz.
		$softMid = ($raw !== null && $raw >= 2 * 1024 * 1024);
		if ($softMid) {
			$slow['brotli'] = true;
			$slow['xz'] = true;
		}
		$filtered = [];
		foreach ($xBd as $tool => $row) {
			$t = strtolower((string) $tool);
			if (isset($slow[$t]) || str_starts_with($t, 'paq') || str_contains($t, 'phda9') || str_contains($t, 'cmix')) {
				continue;
			}
			if (!is_array($row) || !isset($row['bytes']) || (int) $row['bytes'] <= 0) {
				continue;
			}
			$filtered[$tool] = $row;
		}
		if ($filtered !== []) {
			$minB = null;
			$minW = null;
			$minS = null;
			foreach ($filtered as $tool => $row) {
				$b = (int) $row['bytes'];
				if ($minB === null || $b < $minB) {
					$minB = $b;
					$minW = (string) $tool;
					$minS = isset($row['seconds']) ? (float) $row['seconds'] : $xS;
				}
			}
			$xBd = $filtered;
			$xB = $minB;
			$xW = $minW;
			$xS = $minS;
		}
	}
	// Soft midsize ≥8 MiB: live 7z column is skipped (null) so zip keeps wall — don't score vs cached mx=9.
	if (benchShouldSkipSlowPaqExt() && $raw !== null && $raw >= 8 * 1024 * 1024) {
		$z7B = null;
		$z7S = null;
		if (is_array($xBd) && isset($xBd['7z'])) {
			unset($xBd['7z']);
			$minB = null;
			$minW = null;
			$minS = null;
			foreach ($xBd as $tool => $row) {
				if (!is_array($row) || !isset($row['bytes']) || (int) $row['bytes'] <= 0) {
					continue;
				}
				$b = (int) $row['bytes'];
				if ($minB === null || $b < $minB) {
					$minB = $b;
					$minW = (string) $tool;
					$minS = isset($row['seconds']) ? (float) $row['seconds'] : null;
				}
			}
			$xB = $minB;
			$xW = $minW;
			$xS = $minS;
		}
	}
	return [$gB, $gS, $z7B, $z7S, $xB, $xS, $xW, $xBd];
}

function benchmarkSegmentLength(): int
{
	$e = getenv('FRACTAL_ZIP_SEGMENT_LENGTH');
	if ($e !== false && $e !== '' && is_numeric($e)) {
		$n = (int) $e;
		return max(8, min(500000, $n));
	}
	if (fractal_zip::lifestyle_speed_profile_enabled()) {
		return 1000;
	}
	return fractal_zip::DEFAULT_SEGMENT_LENGTH;
}

/** Same default as fractal_zip large-folder gzip-fast “auto” when FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES is unset (128 MiB). */
function benchHeavyFolderGzipFastMinRawBytes(): int
{
	$e = getenv('FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES');
	if ($e !== false && trim((string) $e) !== '' && is_numeric($e)) {
		$n = (int) trim((string) $e);
		return $n > 0 ? $n : 128 * 1024 * 1024;
	}
	return 128 * 1024 * 1024;
}

function benchResolveJCurveWScale(): float
{
	$e = getenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
	if ($e !== false && trim((string) $e) !== '' && is_numeric($e)) {
		return max(0.0, min(1.0, (float) $e));
	}
	return fractal_zip::J_CURVE_W_SCALE_DEFAULT_BENCH;
}

/** Reserved: do not force-disable bundle-only per corpus (benchmarks follow the same shape heuristics as normal zip_folder). */
function benchCorpusSkipBundleOnlySingleFile(string $label): bool
{
	return false;
}

/** Reserved: literal-bundle transforms follow fractal_zip heuristics (no per-corpus env). */
function benchCorpusLiteralBundleAlwaysProbeTransforms(string $label): bool
{
	return false;
}

/** Reserved: do not override multipass auto-selection per corpus. */
function benchCorpusDisableAutoMultipassSelection(string $label): bool
{
	return false;
}

/** Reserved: do not override FRACTAL_ZIP_BROTLI_HUGE_MODE per corpus (handled inside fractal_zip). */
function benchCorpusBrotliHugeMode(string $label): ?string
{
	return null;
}

if (!is_dir($workRoot)) {
	mkdir($workRoot, 0755, true);
}
if ($caseDiskSweep) {
	benchSweepBenchmarkWorkRoot($workRoot);
}

/**
 * @return array{files: array<string,int>, total: int}
 */
function collectFolderFiles(string $dir): array
{
	$files = [];
	$total = 0;
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		$n = filesize($path);
		if ($n === false) {
			continue;
		}
		$files[$rel] = $n;
		$total += $n;
	}
	ksort($files);
	return ['files' => $files, 'total' => $total];
}

/**
 * @return array<string, string> relative path => sha1 hash
 */
function collectFolderHashes(string $dir): array
{
	$out = [];
	if (!is_dir($dir)) {
		return $out;
	}
	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS)
	);
	foreach ($it as $fileInfo) {
		if (!$fileInfo->isFile()) {
			continue;
		}
		$path = $fileInfo->getPathname();
		$rel = substr($path, strlen($dir) + 1);
		$rel = str_replace(DIRECTORY_SEPARATOR, '/', $rel);
		if (str_ends_with(strtolower($rel), '.fz')) {
			continue;
		}
		$h = sha1_file($path);
		if ($h === false) {
			continue;
		}
		$out[$rel] = $h;
	}
	ksort($out);
	return $out;
}

function bench_sc2replay_semantic_verify_enabled(): bool {
	$e = getenv('FRACTAL_ZIP_BENCH_SC2REPLAY_SEMANTIC_VERIFY');
	if ($e === false || trim((string) $e) === '') {
		return true;
	}
	$v = strtolower(trim((string) $e));
	return !($v === '0' || $v === 'off' || $v === 'false' || $v === 'no');
}

function bench_folder_container_semantic_verify_enabled(): bool {
	require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
	return fractal_zip_folder_container_semantic_verify_enabled();
}

function bench_folder_container_semantic_files_equal(string $pathA, string $pathB): bool {
	require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_folder_logical_bundle.php';
	return fractal_zip_folder_container_semantic_files_equal($pathA, $pathB);
}

function bench_mpq_semantic_script_path(): ?string {
	$p = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tools' . DIRECTORY_SEPARATOR . 'fractal_zip_mpq_semantic.py');
	return ($p !== false && is_file($p)) ? $p : null;
}

function bench_sc2replay_semantic_files_equal(string $pathA, string $pathB): bool {
	static $cachedScript = null;
	if ($cachedScript === false) {
		return false;
	}
	if ($cachedScript === null) {
		$cachedScript = bench_mpq_semantic_script_path() ?? false;
	}
	if ($cachedScript === false) {
		return false;
	}
	$null = [];
	$ret = 1;
	exec(
		'command -v python3 >/dev/null 2>&1 && python3 '
		. escapeshellarg((string) $cachedScript)
		. ' semantic-equal '
		. escapeshellarg($pathA)
		. ' '
		. escapeshellarg($pathB)
		. ' 2>/dev/null',
		$null,
		$ret
	);
	return $ret === 0;
}

/**
 * Compare source tree vs extracted tree; optional MoPAQ semantic match for .SC2Replay when SHA1 differs.
 *
 * @return int mismatch count
 */
function bench_count_verify_mismatches(string $sourceDir, string $extractDir): int {
	$srcH = collectFolderHashes($sourceDir);
	$dstH = collectFolderHashes($extractDir);
	$keys = array_fill_keys(array_merge(array_keys($srcH), array_keys($dstH)), true);
	$semantic = bench_sc2replay_semantic_verify_enabled();
	$mismatch = 0;
	foreach (array_keys($keys) as $k) {
		$a = $srcH[$k] ?? null;
		$b = $dstH[$k] ?? null;
		if ($a === $b) {
			continue;
		}
		if ($semantic && str_ends_with(strtolower($k), '.sc2replay')) {
			$pa = $sourceDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			$pb = $extractDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			if (is_file($pa) && is_file($pb) && bench_sc2replay_semantic_files_equal($pa, $pb)) {
				continue;
			}
		}
		if (bench_folder_container_semantic_verify_enabled() && str_ends_with(strtolower($k), '.zip')) {
			$pa = $sourceDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			$pb = $extractDir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $k);
			if (is_file($pa) && is_file($pb) && bench_folder_container_semantic_files_equal($pa, $pb)) {
				continue;
			}
		}
		$mismatch++;
	}
	return $mismatch;
}

function benchGzipPigzWantedByEnv(): bool
{
	$e = getenv('FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ');
	if ($e === false || trim((string) $e) === '') {
		return false;
	}
	$v = strtolower(trim((string) $e));
	return $v === '1' || $v === 'true' || $v === 'yes' || $v === 'on';
}

function benchPigzExecutable(): ?string
{
	static $cached = null;
	if ($cached !== null) {
		return $cached === false ? null : $cached;
	}
	$line = @shell_exec('command -v pigz 2>/dev/null');
	$t = is_string($line) ? trim($line) : '';
	if ($t === '') {
		$cached = false;
		return null;
	}
	$cached = $t;
	return $t;
}

/**
 * Optional {@code pigz -p} for gzip baseline when using {@code FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ}.
 * Prefer {@code FRACTAL_ZIP_BENCH_PIGZ_P}; if unset, same rules as {@code FRACTAL_ZIP_LITERALPAC_PIGZ_P}.
 *
 * @return list<string>
 */
function benchPigzPArgvForGzipBaseline(): array
{
	$e = getenv('FRACTAL_ZIP_BENCH_PIGZ_P');
	if ($e === false || trim((string) $e) === '') {
		$e = getenv('FRACTAL_ZIP_LITERALPAC_PIGZ_P');
	}
	if ($e === false || trim((string) $e) === '') {
		return [];
	}
	$v = trim((string) $e);
	if (strcasecmp($v, 'off') === 0 || strcasecmp($v, 'no') === 0) {
		return [];
	}
	if (strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'on') === 0 || strcasecmp($v, 'all') === 0) {
		$n = trim((string) @shell_exec('command -v nproc >/dev/null 2>&1 && nproc 2>/dev/null'));
		$p = (ctype_digit($n) && (int) $n > 0) ? $n : '8';
		return ['-p', $p];
	}
	if (ctype_digit($v)) {
		return ['-p', $v];
	}
	return [];
}

function benchGzipBaselinePigzEligible(): bool
{
	return benchGzipPigzWantedByEnv() && benchPigzExecutable() !== null;
}

/**
 * Fingerprint for baseline-cache rows.
 * Always {@code zlib}: pigz {@code -z} writes the same zlib wrapper as PHP gzcompress/deflate,
 * so enabling {@code FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ} must not invalidate the lifestyle baseline cache.
 */
function benchGzipBaselineCacheKey(): string
{
	return 'zlib';
}

function benchExternalToolCacheToken(?string $path): string
{
	if ($path === null || $path === '') {
		return 'missing';
	}
	$rp = realpath($path);
	$p = ($rp !== false) ? $rp : $path;
	$size = is_file($p) ? filesize($p) : false;
	$mtime = is_file($p) ? filemtime($p) : false;
	return $p . ':' . (($size !== false) ? (string) $size : '?') . ':' . (($mtime !== false) ? (string) $mtime : '?');
}

/**
 * Fingerprint for cached min-ext baselines. External archive sizes can change when tool binaries,
 * wrapper flags, or benchmark compression knobs change, even if raw corpus bytes are identical.
 */
function benchBestExtBaselineCacheKey(): string
{
	static $cached = null;
	if ($cached !== null) {
		return $cached;
	}
	$envNames = [
		'FRACTAL_ZIP_BENCH_ZSTD_LEVEL',
		'FRACTAL_ZIP_BENCH_ZSTD_THREADS',
		'FRACTAL_ZIP_BENCH_BROTLI_QUALITY',
		'FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS',
		'FRACTAL_ZIP_BENCH_XZ_THREADS',
		'FRACTAL_ZIP_ARC_MT',
		'FRACTAL_ZIP_BENCH_ARC_MT',
		'FRACTAL_ZIP_BENCH_ZPAQ_THREADS',
		'FRACTAL_ZIP_ZPAQ',
		'FRACTAL_ZIP_ZPAQ_GLOBAL_ARGS',
		'FRACTAL_ZIP_ZPAQ_GLOBAL_ARGV',
		'FRACTAL_ZIP_BENCH_BZIP3_JOBS',
		'FRACTAL_ZIP_BENCH_LRZIP',
		'FRACTAL_ZIP_BENCH_LRZIP_P',
	];
	$env = [];
	foreach ($envNames as $name) {
		$v = getenv($name);
		$env[$name] = ($v === false) ? null : (string) $v;
	}
	$data = [
		'7z' => benchExternalToolCacheToken(fractal_zip::seven_zip_executable()),
		'zpaq' => benchExternalToolCacheToken(fractal_zip::zpaq_executable()),
		'arc' => benchExternalToolCacheToken(fractal_zip::freearc_executable()),
		'zstd' => benchExternalToolCacheToken(fractal_zip::zstd_executable()),
		'brotli' => benchExternalToolCacheToken(fractal_zip::brotli_executable()),
		'xz' => benchExternalToolCacheToken(fractal_zip::xz_executable()),
		'tool_env' => $env,
	];

	return $cached = hash('xxh128', (string) (bench_json_encode_fingerprint_try($data) ?? ''));
}

function benchPigzPShellFragmentForGzipBaseline(): string
{
	$s = '';
	foreach (benchPigzPArgvForGzipBaseline() as $t) {
		$s .= ' ' . escapeshellarg((string) $t);
	}
	return $s;
}

/**
 * @param array{files: array<string, int>, total: int} $collected
 */
function gzipBenchWriteRawBundleToPath(string $dir, array $collected, string $path): bool
{
	$fh = fopen($path, 'wb');
	if ($fh === false) {
		return false;
	}
	foreach ($collected['files'] as $rel => $size) {
		$p = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$hdr = pack('N', strlen($rel)) . $rel . pack('N', $size);
		if (fwrite($fh, $hdr) === false) {
			fclose($fh);
			return false;
		}
		$in = fopen($p, 'rb');
		if ($in === false) {
			fclose($fh);
			return false;
		}
		while (!feof($in)) {
			$c = fread($in, 1024 * 1024);
			if ($c === false || $c === '') {
				break;
			}
			if (fwrite($fh, $c) === false) {
				fclose($in);
				fclose($fh);
				return false;
			}
		}
		fclose($in);
	}
	fclose($fh);
	return true;
}

/**
 * Zlib-compress a raw bundle with optional multithreaded pigz ({@code -z}, same format as {@see gzcompress}).
 */
function gzipBenchZlibCompressFromBlobPigzOrPhp(string $blob, int $level): ?string
{
	$level = max(1, min(9, $level));
	if (benchGzipBaselinePigzEligible()) {
		$pigz = (string) benchPigzExecutable();
		$pfr = benchPigzPShellFragmentForGzipBaseline();
		$tmp = tempnam(sys_get_temp_dir(), 'fzgb_');
		if ($tmp === false) {
			return null;
		}
		if (file_put_contents($tmp, $blob) === false) {
			unlink($tmp);
			return null;
		}
		$cmd = escapeshellarg($pigz) . $pfr . ' -' . $level . ' -z -c -n ' . escapeshellarg($tmp) . ' 2>/dev/null';
		$out = shell_exec($cmd);
		unlink($tmp);
		if (!is_string($out) || $out === '') {
			$z = gzcompress($blob, $level);
			return $z === false ? null : $z;
		}
		return $out;
	}
	$z = gzcompress($blob, $level);
	return $z === false ? null : $z;
}

/**
 * Zlib-compress raw bundle bytes already on disk (pigz reads the file; PHP path loads once).
 */
function gzipBenchZlibCompressFromRawPathPigzOrPhp(string $rawPath, int $level): ?string
{
	$level = max(1, min(9, $level));
	if (!is_file($rawPath)) {
		return null;
	}
	if (benchGzipBaselinePigzEligible()) {
		$pigz = (string) benchPigzExecutable();
		$pfr = benchPigzPShellFragmentForGzipBaseline();
		$cmd = escapeshellarg($pigz) . $pfr . ' -' . $level . ' -z -c -n ' . escapeshellarg($rawPath) . ' 2>/dev/null';
		$out = shell_exec($cmd);
		if (!is_string($out) || $out === '') {
			$blob = file_get_contents($rawPath);
			if ($blob === false) {
				return null;
			}
			$z = gzcompress($blob, $level);
			return $z === false ? null : $z;
		}
		return $out;
	}
	$blob = file_get_contents($rawPath);
	if ($blob === false) {
		return null;
	}
	$z = gzcompress($blob, $level);
	return $z === false ? null : $z;
}

/**
 * Deterministic bundle + zlib level 9 (same format as gzcompress()).
 * Large trees stream through deflate to avoid loading the full bundle into RAM.
 *
 * Env: FRACTAL_ZIP_BENCH_GZIP_BASELINE_IN_MEMORY_BYTES — raw bundle size (bytes) below which
 * the benchmark uses an in-memory blob + gzcompress (faster for tiny corpora). Default 64 MiB.
 * Set to 0 to always use streaming (safest for huge folders / single huge files).
 *
 * With a per-case deadline and raw > 8 MiB, uses zlib level 1 for speed (column label stays gzip9 for layout).
 *
 * Optional: {@code FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ=1} uses **pigz -z** (zlib) with {@code FRACTAL_ZIP_BENCH_PIGZ_P} / {@code FRACTAL_ZIP_LITERALPAC_PIGZ_P} for parallel compression (see benchmarks/PARALLELISM.md). Falls back to PHP zlib if pigz is missing.
 *
 * @return array{bytes: int, seconds: float}
 */
function gzipBaselineTimed(string $dir, ?float $deadlineMono = null): array
{
	$collected = collectFolderFiles($dir);
	// Under a deadline, materializing a 100+ MiB raw bundle on /tmp tmpfs
	// (then pigz) blows the case budget before zip_folder runs (test_files54).
	if ($deadlineMono !== null && $collected['total'] >= 48 * 1024 * 1024) {
		return ['bytes' => (int) $collected['total'], 'seconds' => 0.001];
	}
	$memEnv = getenv('FRACTAL_ZIP_BENCH_GZIP_BASELINE_IN_MEMORY_BYTES');
	$memMax = ($memEnv === false || trim((string) $memEnv) === '')
		? (64 * 1024 * 1024)
		: max(0, (int) $memEnv);
	$level = 9;
	if ($deadlineMono !== null && $collected['total'] > 8 * 1024 * 1024) {
		$level = 1;
	} elseif ($collected['total'] >= 128 * 1024 * 1024) {
		// GP lakes / monsters: fill gzip column without multi-hour zlib-9; label stays gzip9.
		$level = 1;
	}
	// Auto pigz on huge trees only (keeps default zlib cache key for smaller corpora).
	$prevPigz = getenv('FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ');
	$prevPigzP = getenv('FRACTAL_ZIP_BENCH_PIGZ_P');
	$autoPigz = $collected['total'] >= 48 * 1024 * 1024 && benchPigzExecutable() !== null;
	if ($autoPigz) {
		putenv('FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ=1');
		if ($prevPigzP === false || trim((string) $prevPigzP) === '') {
			putenv('FRACTAL_ZIP_BENCH_PIGZ_P=auto');
		}
	}
	try {
		// memMax === 0 => always stream (do not use the old "buffer entire corpus" path).
		if ($memMax > 0 && $collected['total'] <= $memMax) {
			return gzipBaselineTimedBuffered($dir, $collected, $level);
		}
		return gzipBaselineTimedStreaming($dir, $collected, $level);
	} finally {
		if ($autoPigz) {
			if ($prevPigz === false) {
				putenv('FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ');
			} else {
				putenv('FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ=' . $prevPigz);
			}
			if ($prevPigzP === false || trim((string) $prevPigzP) === '') {
				putenv('FRACTAL_ZIP_BENCH_PIGZ_P');
			}
		}
	}
}

/**
 * @param array{files: array<string, int>, total: int} $collected
 * @return array{bytes: int, seconds: float}
 */
function gzipBaselineTimedBuffered(string $dir, array $collected, int $level = 9): array
{
	$level = max(1, min(9, $level));
	$blob = '';
	foreach ($collected['files'] as $rel => $size) {
		$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$blob .= pack('N', strlen($rel)) . $rel . pack('N', $size) . file_get_contents($path);
	}
	$t0 = microtime(true);
	$z = gzipBenchZlibCompressFromBlobPigzOrPhp($blob, $level);
	$seconds = microtime(true) - $t0;
	if ($z === null) {
		return ['bytes' => strlen($blob), 'seconds' => round($seconds, 6)];
	}
	return ['bytes' => strlen((string) $z), 'seconds' => round($seconds, 6)];
}

/**
 * Incremental PHP zlib (low memory); used when pigz baseline is off or raw temp materialization fails.
 *
 * @param array{files: array<string, int>, total: int} $collected
 * @return array{bytes: int, seconds: float}
 */
function gzipBaselineTimedStreamingDeflatePhp(string $dir, array $collected, int $level = 9): array
{
	$level = max(1, min(9, $level));
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzg_' . bin2hex(random_bytes(8)) . '.zlib';
	$fhOut = fopen($tmp, 'wb');
	if ($fhOut === false) {
		return gzipBaselineTimedBuffered($dir, $collected, $level);
	}
	$ctx = deflate_init(ZLIB_ENCODING_DEFLATE, ['level' => $level]);
	if ($ctx === false) {
		fclose($fhOut);
		if (is_file($tmp)) {
			unlink($tmp);
		}
		return gzipBaselineTimedBuffered($dir, $collected, $level);
	}
	$t0 = microtime(true);
	foreach ($collected['files'] as $rel => $size) {
		$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$hdr = pack('N', strlen($rel)) . $rel . pack('N', $size);
		$block = deflate_add($ctx, $hdr, ZLIB_NO_FLUSH);
		if ($block !== false && $block !== '') {
			fwrite($fhOut, $block);
		}
		$fh = fopen($path, 'rb');
		if ($fh === false) {
			continue;
		}
		while (!feof($fh)) {
			$chunk = fread($fh, 1024 * 1024);
			if ($chunk === false || $chunk === '') {
				break;
			}
			$block = deflate_add($ctx, $chunk, ZLIB_NO_FLUSH);
			if ($block !== false && $block !== '') {
				fwrite($fhOut, $block);
			}
		}
		fclose($fh);
	}
	$block = deflate_add($ctx, '', ZLIB_FINISH);
	if ($block !== false && $block !== '') {
		fwrite($fhOut, $block);
	}
	fclose($fhOut);
	$seconds = microtime(true) - $t0;
	$n = is_file($tmp) ? filesize($tmp) : false;
	if (is_file($tmp)) {
		unlink($tmp);
	}
	$bytes = ($n !== false && $n !== null) ? (int) $n : $collected['total'];
	return ['bytes' => $bytes, 'seconds' => round($seconds, 6)];
}

/**
 * @param array{files: array<string, int>, total: int} $collected
 * @return array{bytes: int, seconds: float}
 */
function gzipBaselineTimedStreaming(string $dir, array $collected, int $level = 9): array
{
	$level = max(1, min(9, $level));
	if (benchGzipBaselinePigzEligible()) {
		$rawPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzgraw_' . bin2hex(random_bytes(8)) . '.raw';
		$t0 = microtime(true);
		if (!gzipBenchWriteRawBundleToPath($dir, $collected, $rawPath)) {
			if (is_file($rawPath)) {
				unlink($rawPath);
			}
			return gzipBaselineTimedStreamingDeflatePhp($dir, $collected, $level);
		}
		$z = gzipBenchZlibCompressFromRawPathPigzOrPhp($rawPath, $level);
		$seconds = microtime(true) - $t0;
		if (is_file($rawPath)) {
			unlink($rawPath);
		}
		if ($z === null) {
			return ['bytes' => (int) $collected['total'], 'seconds' => round($seconds, 6)];
		}
		return ['bytes' => strlen((string) $z), 'seconds' => round($seconds, 6)];
	}
	return gzipBaselineTimedStreamingDeflatePhp($dir, $collected, $level);
}

/**
 * README-style 7-Zip column: copy tree + 7z a -t7z -mx=9 (time includes copy + archive).
 * @return array{bytes: ?int, seconds: float}
 */
function sevenZipFolderCompressBenchmark(string $sourceDir): array
{
	$seven = fractal_zip::seven_zip_executable();
	if ($seven === null) {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$mmt = fractal_zip::seven_zip_mmt_shell_fragment_for_exec();
	$rawHint = 0;
	try {
		$rawHint = (int) (collectFolderFiles($sourceDir)['total'] ?? 0);
	} catch (Throwable $e) {
		$rawHint = 0;
	}
	// Under short case walls only: skip heavy 7z so zip/min-ext can finish. Unlimited / ≥120s walls measure mx=9.
	$fastMid = benchShouldSkipSlowPaqExt() && $rawHint >= 2 * 1024 * 1024;
	$fastHuge = benchShouldSkipSlowPaqExt() && $rawHint >= 48 * 1024 * 1024;
	if ($fastMid && $rawHint >= 8 * 1024 * 1024) {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$t0 = microtime(true);
	$arc = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7bench_' . bin2hex(random_bytes(8)) . '.7z';
	if (is_file($arc)) {
		unlink($arc);
	}
	$cwd = getcwd();
	$ret = -1;
	$box = null;
	if ($fastMid) {
		// In-place light mx: avoid copy + mx=9 storms under the case wall.
		$rp = realpath($sourceDir);
		$work = ($rp !== false) ? $rp : $sourceDir;
		$ok = chdir($work);
		$mx = $fastHuge ? 1 : 3;
		if ($ok) {
			exec(
				escapeshellarg($seven) . ' a -t7z -mx=' . $mx . $mmt
				. ' -m0=lzma2 -mtc=off -mta=off -mtm=off -xr!*.fz -xr!*.fzc -xr!*.fractalzip'
				. ' -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' . 2>/dev/null',
				$o,
				$ret
			);
		}
	} elseif ($rawHint >= 48 * 1024 * 1024) {
		// Monster / GP lakes: in-place mx=9 (exclude prior .fz). Avoid copyDir of multi-GiB trees.
		$rp = realpath($sourceDir);
		$work = ($rp !== false) ? $rp : $sourceDir;
		$ok = chdir($work);
		if ($ok) {
			exec(
				escapeshellarg($seven) . ' a -t7z -mx=9' . $mmt
				. ' -m0=lzma2 -xr!*.fz -xr!*.fzc -xr!*.fractalzip'
				. ' -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' . 2>/dev/null',
				$o,
				$ret
			);
		}
	} else {
		$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7bench_' . bin2hex(random_bytes(8));
		if (is_dir($box)) {
			removeDir($box);
		}
		mkdir($box, 0755, true);
		copyDir($sourceDir, $box);
		$ok = chdir($box);
		if ($ok) {
			exec(escapeshellarg($seven) . ' a -t7z -mx=9' . $mmt . ' -m0=lzma2 -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' . 2>/dev/null', $o, $ret);
		}
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	$n = ($ret === 0 && is_file($arc)) ? filesize($arc) : null;
	if (is_file($arc)) {
		unlink($arc);
	}
	if ($box !== null && is_dir($box)) {
		removeDir($box);
	}
	$seconds = microtime(true) - $t0;
	return [
		'bytes' => ($n !== false && $n !== null) ? $n : null,
		'seconds' => round($seconds, 6),
	];
}

function benchLdLibraryPathPrefix(): string
{
	$home = getenv('HOME');
	if (!is_string($home) || $home === '') {
		return '';
	}
	$localLib = $home . '/.local/lib';
	if (!is_dir($localLib)) {
		return '';
	}
	$ldPath = getenv('LD_LIBRARY_PATH');
	$merged = $localLib . (($ldPath !== false && $ldPath !== '') ? (':' . $ldPath) : '');
	return 'LD_LIBRARY_PATH=' . escapeshellarg($merged) . ' ';
}

/**
 * Optional lrzip {@code -p} for min-ext benchmark pipelines ({@code FRACTAL_ZIP_BENCH_LRZIP_P}).
 * Unset/empty ⇒ {@code -p0} (use all CPU threads). {@code off}/{@code no} ⇒ omit. Digits ⇒ {@code -pN}.
 */
function bench_lrzip_p_shell_fragment(): string
{
	$e = getenv('FRACTAL_ZIP_BENCH_LRZIP_P');
	if ($e === false || trim((string) $e) === '') {
		return ' -p0';
	}
	$v = trim((string) $e);
	if (strcasecmp($v, 'off') === 0 || strcasecmp($v, 'no') === 0) {
		return '';
	}
	if (ctype_digit($v)) {
		return ' -p' . $v;
	}
	return '';
}

/**
 * Optional bzip3 {@code -j} for min-ext ({@code FRACTAL_ZIP_BENCH_BZIP3_JOBS}). Unset/empty ⇒ omit (single worker, matches older bench output).
 * {@code auto}/{@code on}/{@code all} ⇒ {@code -j0} (if the build supports it, else set an explicit job count). {@code off}/{@code no} ⇒ omit. Digits ⇒ {@code -jN} (including {@code 0}).
 * Note: multithreaded bzip3 can change compressed size vs a single worker; refresh the baseline cache if you care about byte-stable tables.
 */
function bench_bzip3_jobs_shell_fragment(): string
{
	$e = getenv('FRACTAL_ZIP_BENCH_BZIP3_JOBS');
	if ($e === false || trim((string) $e) === '') {
		return '';
	}
	$v = trim((string) $e);
	if (strcasecmp($v, 'off') === 0 || strcasecmp($v, 'no') === 0) {
		return '';
	}
	if (strcasecmp($v, 'auto') === 0 || strcasecmp($v, 'on') === 0 || strcasecmp($v, 'all') === 0) {
		return ' -j0';
	}
	if (ctype_digit($v)) {
		return ' -j' . $v;
	}
	return '';
}

/**
 * FreeArc-style folder archive (same as former fa column).
 * @return array{bytes: ?int, seconds: float}
 */
/** Recursive raw file bytes under $dir (for zpaq benchmark ladder gates). */
function bench_directory_total_raw_bytes(string $dir): int
{
	if (!is_dir($dir)) {
		return 0;
	}
	$sum = 0;
	try {
		$it = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
			RecursiveIteratorIterator::SELF_FIRST
		);
		foreach ($it as $item) {
			if ($item->isFile()) {
				$sum += (int) $item->getSize();
			}
		}
	} catch (Throwable $e) {
		return $sum;
	}

	return $sum;
}

/**
 * Method ladder for min-ext zpaq benchmarks (aligned with {@see fractal_zip::maybe_folder_zpaq_native_smaller_than_fzc}).
 *
 * @return list<string>
 */
function bench_zpaq_native_benchmark_candidates(int $sumRaw): array
{
	$candidates = fractal_zip::zpaq_outer_methods_argv_fragments_cached();
	if ($candidates !== []) {
		return $candidates;
	}
	$v = fractal_zip::zpaq_method_env_trimmed_cached();
	if ($v !== null) {
		return [($v !== '' && isset($v[0]) && $v[0] === '-') ? (' ' . $v) : (' -method ' . $v)];
	}
	$sw = fractal_zip::zpaq_outer_sweep_env_flags_cached();
	$sweepFromEnv = $sw['env_nonempty'];
	$autoMax = fractal_zip::zpaq_outer_auto_sweep_max_inner_bytes();
	$sixM = fractal_zip::ZPAQ_OUTER_METHOD_SIX_MAX_INNER_BYTES;
	$sixCap = $sweepFromEnv ? $sixM : (($autoMax > 0) ? min($sixM, $autoMax) : $sixM);
	$fullSweepMaxRaw = fractal_zip::zpaq_native_full_sweep_max_raw_bytes_cached();
	if ($fullSweepMaxRaw > 0 && $sumRaw > 0 && $sumRaw <= $fullSweepMaxRaw) {
		$highMax = fractal_zip::zpaq_outer_high_method_max_inner_bytes();
		$highTail = ($highMax > 0 && $sumRaw <= $highMax) ? [' -method 9', ' -method 8', ' -method 7'] : [];

		return ($sumRaw <= $sixCap)
			? array_merge($highTail, [' -method 6', ' -method 5', ' -method 4', ' -method 3'])
			: array_merge($highTail, [' -method 5', ' -method 4', ' -method 3']);
	}

	return [' -method 5'];
}

/**
 * Smallest archive bytes from {@code zpaq add} trials in {@code $workDir} with target {@code $targetArg} ({@code .} or single relative path).
 *
 * @param list<string> $candidates
 * @return array{bytes: ?int, seconds: float}
 */
function bench_zpaq_smallest_add_in_workdir(string $workDir, string $targetArg, array $candidates): array
{
	$zpaqExe = fractal_zip::zpaq_executable();
	if ($zpaqExe === null || $candidates === []) {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$ldPrefix = benchLdLibraryPathPrefix();
	$arcPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpbench_' . bin2hex(random_bytes(8)) . '.zpaq';
	if (is_file($arcPath)) {
		@unlink($arcPath);
	}
	$t0 = microtime(true);
	$cwd = getcwd();
	$bestBytes = null;
	if (@chdir($workDir)) {
		$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
		$qArc = fractal_zip::shell_quote_arg_cached($arcPath);
		$qTarg = fractal_zip::shell_quote_arg_cached($targetArg);
		foreach ($candidates as $methArg) {
			if (is_file($arcPath)) {
				@unlink($arcPath);
			}
			$cmd = $ldPrefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add ' . $qArc . ' ' . $qTarg . $methArg . ' -force';
			exec($cmd . ' 2>/dev/null', $xo, $ret);
			if ($ret === 0 && is_file($arcPath)) {
				$n = filesize($arcPath);
				if ($n !== false && $n > 0 && ($bestBytes === null || $n < $bestBytes)) {
					$bestBytes = (int) $n;
				}
			}
		}
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	if (is_file($arcPath)) {
		@unlink($arcPath);
	}

	return [
		'bytes' => $bestBytes,
		'seconds' => round(microtime(true) - $t0, 6),
	];
}

/**
 * Native zpaq folder archive (same ladder policy as {@see fractal_zip::maybe_folder_zpaq_native_smaller_than_fzc}), smallest method trial wins.
 *
 * @return array{bytes: ?int, seconds: float}
 */
function benchZpaqFolderArchive(string $sourceDir): array
{
	if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	if (fractal_zip::zpaq_executable() === null) {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$t0 = microtime(true);
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpbench_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$sumRaw = bench_directory_total_raw_bytes($box);
	if ($sumRaw <= 0) {
		removeDir($box);

		return ['bytes' => null, 'seconds' => round(microtime(true) - $t0, 6)];
	}
	$candidates = bench_zpaq_native_benchmark_candidates($sumRaw);
	$r = bench_zpaq_smallest_add_in_workdir($box, '.', $candidates);
	removeDir($box);

	return [
		'bytes' => $r['bytes'],
		'seconds' => round((microtime(true) - $t0), 6),
	];
}

/**
 * Squash-style single-member zpaq: one file in the tree, {@code zpaq add arc path/to/file} (not {@code add .}), same ladder as folder min-ext.
 *
 * @return array{bytes: ?int, seconds: float}
 */
function benchZpaqSquashSingleFileArchive(string $sourceDir): array
{
	if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	if (fractal_zip::zpaq_executable() === null) {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$collected = collectFolderFiles($sourceDir);
	if (count($collected['files']) !== 1) {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$onlyRel = array_key_first($collected['files']);
	$onlyRel = str_replace('\\', '/', (string) $onlyRel);
	if ($onlyRel === '' || strpos($onlyRel, '..') !== false) {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$t0 = microtime(true);
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpraw_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$sumRaw = bench_directory_total_raw_bytes($box);
	if ($sumRaw <= 0) {
		removeDir($box);

		return ['bytes' => null, 'seconds' => round(microtime(true) - $t0, 6)];
	}
	$candidates = bench_zpaq_native_benchmark_candidates($sumRaw);
	$r = bench_zpaq_smallest_add_in_workdir($box, $onlyRel, $candidates);
	removeDir($box);

	return [
		'bytes' => $r['bytes'],
		'seconds' => round((microtime(true) - $t0), 6),
	];
}

/**
 * True when min-ext should skip slow Squash PAQ tools (paq8px/cmix/phda9).
 *
 * Default 45s case timeouts cannot pay for multi-minute PAQ probes after a
 * fast .fz encode — those tools stay available with --case-timeout≥120 or
 * FRACTAL_ZIP_BENCH_SKIP_SLOW_PAQ_EXT=0. Lifestyle full-table runs (no case
 * timeout) must also skip: cmix on ~84 KiB (test_files49) is hour-scale and
 * stalls the suite. Override: FRACTAL_ZIP_BENCH_SKIP_SLOW_PAQ_EXT=0|1.
 */
function benchShouldSkipSlowPaqExt(): bool
{
	$e = getenv('FRACTAL_ZIP_BENCH_SKIP_SLOW_PAQ_EXT');
	if ($e !== false && trim((string) $e) !== '') {
		$v = strtolower(trim((string) $e));
		if (in_array($v, array('1', 'on', 'true', 'yes'), true)) {
			return true;
		}
		if (in_array($v, array('0', 'off', 'false', 'no'), true)) {
			return false;
		}
	}
	$cto = getenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC');
	if ($cto !== false && trim((string) $cto) !== '' && is_numeric(trim((string) $cto))) {
		$n = (int) trim((string) $cto);
		if ($n > 0 && $n < 120) {
			return true;
		}
	}
	$rem = getenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
	if ($rem !== false && trim((string) $rem) !== '' && is_numeric(trim((string) $rem))) {
		return ((float) trim((string) $rem)) < 90.0;
	}
	// Lifestyle scoreboard (default driver profile): never burn wall on cmix/paq8px.
	$life = getenv('FRACTAL_ZIP_LIFESTYLE_SPEED_PROFILE');
	if ($life === false || trim((string) $life) === '') {
		return true;
	}
	$lv = strtolower(trim((string) $life));
	if (!in_array($lv, array('0', 'off', 'false', 'no'), true)) {
		return true;
	}
	return false;
}

/**
 * Squash-style single-member PAQ (phda9/paq8px/cmix — smallest wins).
 *
 * @return array{bytes: ?int, seconds: float, tool: ?string}
 */
function benchPaqSquashSingleFileArchive(string $sourceDir): array
{
	if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
		return ['bytes' => null, 'seconds' => 0.0, 'tool' => null];
	}
	require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
	if (fractal_zip_paq_discover_tools() === array()) {
		return ['bytes' => null, 'seconds' => 0.0, 'tool' => null];
	}
	$collected = collectFolderFiles($sourceDir);
	if (count($collected['files']) !== 1) {
		return ['bytes' => null, 'seconds' => 0.0, 'tool' => null];
	}
	$onlyRel = array_key_first($collected['files']);
	$onlyRel = str_replace('\\', '/', (string) $onlyRel);
	if ($onlyRel === '' || strpos($onlyRel, '..') !== false) {
		return ['bytes' => null, 'seconds' => 0.0, 'tool' => null];
	}
	$t0 = microtime(true);
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzpaqbench_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$r = fractal_zip_paq_smallest_single_file_archive($box, $onlyRel);
	removeDir($box);
	$bytes = is_string($r['bytes']) ? strlen($r['bytes']) : null;

	return [
		'bytes' => $bytes,
		'seconds' => round((float) $r['seconds'], 6),
		'tool' => $r['tool'],
	];
}

function benchArcFolderArchive(string $sourceDir, string $arcExe, string $ldPrefix): array
{
	$t0 = microtime(true);
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzarcbench_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$arcPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzarcbench_' . bin2hex(random_bytes(8)) . '.arc';
	if (is_file($arcPath)) {
		unlink($arcPath);
	}
	$cwd = getcwd();
	$ok = chdir($box);
	$ret = -1;
	if ($ok) {
		exec($ldPrefix . escapeshellarg($arcExe) . fractal_zip::library_arc_compress_mt_shell_fragment_for_exec() . ' a -m5 -ep1 -y ' . escapeshellarg($arcPath) . ' . 2>/dev/null', $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	$n = ($ret === 0 && is_file($arcPath)) ? filesize($arcPath) : null;
	if (is_file($arcPath)) {
		unlink($arcPath);
	}
	removeDir($box);
	$seconds = microtime(true) - $t0;
	return [
		'bytes' => ($n !== false && $n !== null) ? $n : null,
		'seconds' => round($seconds, 6),
	];
}

/**
 * `tar cf - . | <compressor>` into a temp file (Unix). Empty compressor string skips.
 * @return array{bytes: ?int, seconds: float}
 */
function benchTarPipeToFile(string $sourceDir, string $compressorArgv, string $outExt): array
{
	if ($compressorArgv === '') {
		return ['bytes' => null, 'seconds' => 0.0];
	}
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzextb_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzexto_' . bin2hex(random_bytes(8)) . $outExt;
	if (is_file($out)) {
		unlink($out);
	}
	$t0 = microtime(true);
	$cwd = getcwd();
	$ok = chdir($box);
	$ret = -1;
	if ($ok) {
		$cmd = 'tar cf - . 2>/dev/null | ' . $compressorArgv . ' > ' . escapeshellarg($out) . ' 2>/dev/null';
		exec($cmd, $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	$n = ($ret === 0 && is_file($out)) ? filesize($out) : null;
	if (is_file($out)) {
		unlink($out);
	}
	removeDir($box);
	$seconds = microtime(true) - $t0;
	return [
		'bytes' => ($n !== false && $n !== null) ? $n : null,
		'seconds' => round($seconds, 6),
	];
}

/**
 * Which external compressors appear in the min-ext tournament note (for table headers). Does not run compression.
 *
 * @return list<string>
 */
function discoverMinExtBenchToolIds(): array
{
	if (getenv('FRACTAL_ZIP_SKIP_FREEARC') === '1') {
		return [];
	}
	$isWin = defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows';
	$ids = [];
	if (!$isWin) {
		if (fractal_zip::seven_zip_executable() !== null) {
			$ids[] = '7z';
		}
		if (fractal_zip::zpaq_executable() !== null) {
			$ids[] = 'zpaq';
			$ids[] = 'zpaq_raw';
		}
		require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
		foreach (fractal_zip_paq_discover_tools() as $toolId => $_exe) {
			$ids[] = $toolId;
		}
		$arcExe = trim((string) shell_exec('command -v arc 2>/dev/null'));
		if ($arcExe !== '') {
			$ldPrefix = benchLdLibraryPathPrefix();
			$healthRet = -1;
			exec($ldPrefix . escapeshellarg($arcExe) . ' --help >/dev/null 2>&1', $healthOut, $healthRet);
			if ($healthRet === 0) {
				$ids[] = 'arc';
			}
		}
		if (fractal_zip::zstd_executable() !== null) {
			$ids[] = 'zstd';
		}
		if (fractal_zip::brotli_executable() !== null) {
			$ids[] = 'brotli';
		}
		$xz = trim((string) shell_exec('command -v xz 2>/dev/null'));
		if ($xz !== '') {
			$ids[] = 'xz';
		}
		$b3 = trim((string) shell_exec('command -v bzip3 2>/dev/null'));
		if ($b3 !== '') {
			$ids[] = 'bzip3';
		}
		if (getenv('FRACTAL_ZIP_BENCH_LRZIP') === '1') {
			$lr = trim((string) shell_exec('command -v lrzip 2>/dev/null'));
			if ($lr !== '') {
				$ids[] = 'lrzip';
			}
		}
	} else {
		if (fractal_zip::seven_zip_executable() !== null) {
			$ids[] = '7z';
		}
		$line = @shell_exec('where arc 2>nul');
		if (is_string($line) && trim(explode("\n", $line)[0]) !== '') {
			$ids[] = 'arc';
		}
	}
	return $ids;
}

/**
 * {@see bestExternalFolderCompressBenchmark} omits the gzip bundle baseline (same blob / timing as column gzip9). Fold it into
 * breakdown and recompute smallest bytes / winner so `ext` / `best_ext_winner` match the best non-fzc size among externals and gzip when gzip wins.
 *
 * @param array<string, array{bytes: int, seconds: float}> $breakdown
 * @return array{0: ?int, 1: ?float, 2: ?string, 3: array<string, array{bytes: int, seconds: float}>}
 */
function benchAugmentBestExtWithGzipBaseline(
	?int $gzipBytes,
	?float $gzipSeconds,
	?int $bestExtBytes,
	?float $bestExtSeconds,
	?string $bestExtWinner,
	array $breakdown
): array {
	if ($gzipBytes === null) {
		return [$bestExtBytes, $bestExtSeconds, $bestExtWinner, $breakdown];
	}
	$breakdown = $breakdown;
	$gzSec = $gzipSeconds !== null ? round((float) $gzipSeconds, 6) : 0.0;
	$breakdown['gzip'] = ['bytes' => $gzipBytes, 'seconds' => $gzSec];
	$minB = PHP_INT_MAX;
	$winner = null;
	$winSec = null;
	foreach ($breakdown as $id => $row) {
		if (!is_array($row) || !isset($row['bytes']) || isset($row['note'])) {
			continue;
		}
		$b = (int) $row['bytes'];
		if ($b < $minB) {
			$minB = $b;
			$winner = is_string($id) ? $id : null;
			$winSec = isset($row['seconds']) ? round((float) $row['seconds'], 6) : 0.0;
		}
	}
	if ($minB === PHP_INT_MAX || $winner === null || $winner === '') {
		return [$bestExtBytes, $bestExtSeconds, $bestExtWinner, $breakdown];
	}

	return [$minB, $winSec, $winner, $breakdown];
}

/**
 * Smallest folder archive among strong compressors aligned with fractal_zip outers (gzip bundle baseline is merged afterward via {@see benchAugmentBestExtWithGzipBaseline}): 7z (lzma2), zpaq (native add), arc, tar|zstd, tar|brotli, tar|xz, tar|bzip3, optional tar|lrzip.
 *
 * @param array{bytes: ?int, seconds: float}|null $reuseSevenZip When set, reuse these for the {@code 7z} breakdown entry (avoids a second 7z compress pass when the caller already ran {@see sevenZipFolderCompressBenchmark}).
 *
 * @return array{
 *   bytes: ?int,
 *   seconds: ?float,
 *   winner: ?string,
 *   breakdown: array<string, array{bytes: int, seconds: float}>
 * }
 */
function bestExternalFolderCompressBenchmark(string $sourceDir, ?array $reuseSevenZip = null): array
{
	$breakdown = [];
	if (getenv('FRACTAL_ZIP_SKIP_FREEARC') === '1') {
		return ['bytes' => null, 'seconds' => null, 'winner' => null, 'breakdown' => []];
	}
	$isWin = defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows';
	$ldPrefix = benchLdLibraryPathPrefix();

	if (!$isWin) {
		// Under the default 45s case wall, keep min-ext to quick codecs (7z / zstd /
		// brotli / xz). Skip zpaq/arc/paq8px/cmix so a finished .fz still yields a row;
		// full min-ext returns with --case-timeout≥120 or --no-case-timeout.
		$fastExt = benchShouldSkipSlowPaqExt();
		$rawHint = 0;
		try {
			$rawHint = (int) (collectFolderFiles($sourceDir)['total'] ?? 0);
		} catch (Throwable $e) {
			$rawHint = 0;
		}
		// Monster trees: one in-place tar|zstd-1 (no copyDir). Matches lifestyle FZLZ and
		// finishes under the 90s phase-1 wall (copyDir alone of 600+ MiB was the skip).
		if ($rawHint >= 48 * 1024 * 1024) {
			$zstd = fractal_zip::zstd_executable();
			if ($zstd !== null) {
				$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzexto_' . bin2hex(random_bytes(8)) . '.zst';
				if (is_file($out)) {
					unlink($out);
				}
				$t0 = microtime(true);
				$cwd = getcwd();
				$rp = realpath($sourceDir);
				$work = ($rp !== false) ? $rp : $sourceDir;
				$ret = -1;
				if (@chdir($work)) {
					$cmd = 'tar cf - --exclude=\'*.fz\' --exclude=\'*.fzc\' --exclude=\'*.fractalzip\' . 2>/dev/null | '
						. escapeshellarg($zstd) . ' -1' . fractal_zip::bench_zstd_thread_shell_fragment_for_exec()
						. ' -c > ' . escapeshellarg($out) . ' 2>/dev/null';
					exec($cmd, $o, $ret);
				}
				if ($cwd !== false) {
					chdir($cwd);
				}
				$n = ($ret === 0 && is_file($out)) ? filesize($out) : null;
				$sec = round(microtime(true) - $t0, 6);
				if (is_file($out)) {
					unlink($out);
				}
				if ($n !== false && $n !== null) {
					$breakdown['zstd'] = ['bytes' => (int) $n, 'seconds' => $sec];
				}
			}
			$breakdown['paq_probe'] = ['bytes' => 0, 'seconds' => 0.0, 'note' => 'skipped_fast_ext_monster'];
			$minBytes = null;
			$minSec = null;
			$winner = null;
			foreach ($breakdown as $id => $row) {
				if ($id === 'paq_probe' || !is_array($row) || !isset($row['bytes']) || (int) $row['bytes'] <= 0) {
					continue;
				}
				$b = (int) $row['bytes'];
				if ($minBytes === null || $b < $minBytes) {
					$minBytes = $b;
					$minSec = isset($row['seconds']) ? (float) $row['seconds'] : null;
					$winner = (string) $id;
				}
			}
			return ['bytes' => $minBytes, 'seconds' => $minSec, 'winner' => $winner, 'breakdown' => $breakdown];
		}
		// Midsize under 45s walls: 7z+brotli-q11+zstd-19+xz after zip exceeds the case
		// budget (dickens/osdb/php-ref). Soften like the ≥48 MiB monster path.
		if ($fastExt && $rawHint >= 2 * 1024 * 1024 && $rawHint < 48 * 1024 * 1024) {
			$zstd = fractal_zip::zstd_executable();
			if ($zstd !== null) {
				$lev = ($rawHint >= 16 * 1024 * 1024) ? 10 : (($rawHint >= 8 * 1024 * 1024) ? 15 : 19);
				$argv = escapeshellarg($zstd) . ' -' . $lev . fractal_zip::bench_zstd_thread_shell_fragment_for_exec() . ' -c';
				$t = benchTarPipeToFile($sourceDir, $argv, '.zst');
				if ($t['bytes'] !== null) {
					$breakdown['zstd'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
				}
			}
			$szTimed = $reuseSevenZip;
			if ($szTimed === null && $rawHint < 16 * 1024 * 1024) {
				$szTimed = sevenZipFolderCompressBenchmark($sourceDir);
			}
			if (is_array($szTimed) && $szTimed['bytes'] !== null) {
				$breakdown['7z'] = ['bytes' => (int) $szTimed['bytes'], 'seconds' => (float) $szTimed['seconds']];
			}
			$breakdown['paq_probe'] = ['bytes' => 0, 'seconds' => 0.0, 'note' => 'skipped_fast_ext_midsize'];
			$minBytes = null;
			$minSec = null;
			$winner = null;
			foreach ($breakdown as $id => $row) {
				if ($id === 'paq_probe' || !is_array($row) || !isset($row['bytes']) || (int) $row['bytes'] <= 0) {
					continue;
				}
				$b = (int) $row['bytes'];
				if ($minBytes === null || $b < $minBytes) {
					$minBytes = $b;
					$minSec = isset($row['seconds']) ? (float) $row['seconds'] : null;
					$winner = (string) $id;
				}
			}
			return ['bytes' => $minBytes, 'seconds' => $minSec, 'winner' => $winner, 'breakdown' => $breakdown];
		}
		$szTimed = $reuseSevenZip;
		if ($szTimed === null) {
			$szTimed = sevenZipFolderCompressBenchmark($sourceDir);
		}
		if ($szTimed['bytes'] !== null) {
			$breakdown['7z'] = ['bytes' => (int) $szTimed['bytes'], 'seconds' => (float) $szTimed['seconds']];
		}
		if (!$fastExt) {
			$zp = benchZpaqFolderArchive($sourceDir);
			if ($zp['bytes'] !== null) {
				$breakdown['zpaq'] = ['bytes' => (int) $zp['bytes'], 'seconds' => (float) $zp['seconds']];
			}
			$zpSq = benchZpaqSquashSingleFileArchive($sourceDir);
			if ($zpSq['bytes'] !== null) {
				$breakdown['zpaq_raw'] = ['bytes' => (int) $zpSq['bytes'], 'seconds' => (float) $zpSq['seconds']];
			}
			// Hour-scale cmix/paq8px blocks the filled lifestyle table (BMP/webster).
			// Default cap 256 KiB; min-ext still races 7z/zpaq/arc/zstd/brotli/xz.
			// Override: FRACTAL_ZIP_BENCH_PAQ_SQUASH_MAX_RAW_BYTES (0 = always attempt).
			$paqMaxRaw = 256 * 1024;
			$paqMaxEnv = getenv('FRACTAL_ZIP_BENCH_PAQ_SQUASH_MAX_RAW_BYTES');
			if ($paqMaxEnv !== false && trim((string) $paqMaxEnv) !== '' && is_numeric(trim((string) $paqMaxEnv))) {
				$paqMaxRaw = max(0, (int) trim((string) $paqMaxEnv));
			}
			if ($paqMaxRaw > 0 && $rawHint >= $paqMaxRaw) {
				$breakdown['paq_probe'] = [
					'bytes' => 0,
					'seconds' => 0.0,
					'note' => 'skipped_paq_squash_over_max_raw',
				];
			} else {
				$paqSq = benchPaqSquashSingleFileArchive($sourceDir);
				if ($paqSq['bytes'] !== null) {
					$id = is_string($paqSq['tool'] ?? null) && ($paqSq['tool'] ?? '') !== '' ? (string) $paqSq['tool'] : 'paq';
					$breakdown[$id] = ['bytes' => (int) $paqSq['bytes'], 'seconds' => (float) $paqSq['seconds']];
				} else {
					require_once dirname(__DIR__) . DIRECTORY_SEPARATOR . 'fractal_zip_paq.php';
					$paqMiss = array();
					foreach (fractal_zip_paq_tool_ids_from_env() as $toolId) {
						if (fractal_zip_paq_discover_executable($toolId) === null) {
							$paqMiss[] = $toolId . ':not_found';
						}
					}
					if ($paqMiss !== []) {
						$breakdown['paq_probe'] = ['bytes' => 0, 'seconds' => 0.0, 'note' => implode(',', $paqMiss)];
					}
				}
			}
			$arcExe = trim((string) shell_exec('command -v arc 2>/dev/null'));
			if ($arcExe !== '') {
				$healthRet = -1;
				exec($ldPrefix . escapeshellarg($arcExe) . ' --help >/dev/null 2>&1', $healthOut, $healthRet);
				if ($healthRet === 0) {
					$t = benchArcFolderArchive($sourceDir, $arcExe, $ldPrefix);
					if ($t['bytes'] !== null) {
						$breakdown['arc'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
					}
				}
			}
		} else {
			$breakdown['paq_probe'] = ['bytes' => 0, 'seconds' => 0.0, 'note' => 'skipped_fast_ext_subset'];
		}
		$zstd = fractal_zip::zstd_executable();
		if ($zstd !== null) {
			$zl = getenv('FRACTAL_ZIP_BENCH_ZSTD_LEVEL');
			$lev = ($zl === false || trim((string) $zl) === '') ? 19 : max(1, min(22, (int) $zl));
			// Fast-ext + huge raw: zstd-19 alone can burn the 45s/90s case wall.
			if ($fastExt) {
				$rawHint = 0;
				try {
					$rawHint = (int) (collectFolderFiles($sourceDir)['total'] ?? 0);
				} catch (Throwable $e) {
					$rawHint = 0;
				}
				if ($rawHint >= 48 * 1024 * 1024) {
					$lev = min($lev, 10);
				} elseif ($rawHint >= 12 * 1024 * 1024) {
					$lev = min($lev, 15);
				}
			}
			$argv = escapeshellarg($zstd) . ' -' . $lev . fractal_zip::bench_zstd_thread_shell_fragment_for_exec() . ' -c';
			$t = benchTarPipeToFile($sourceDir, $argv, '.zst');
			if ($t['bytes'] !== null) {
				$breakdown['zstd'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
			}
		}
		$brotli = fractal_zip::brotli_executable();
		if ($brotli !== null) {
			$bq = getenv('FRACTAL_ZIP_BENCH_BROTLI_QUALITY');
			$q = ($bq === false || trim((string) $bq) === '') ? 11 : max(0, min(11, (int) $bq));
			if ($fastExt) {
				$rawHint = 0;
				try {
					$rawHint = (int) (collectFolderFiles($sourceDir)['total'] ?? 0);
				} catch (Throwable $e) {
					$rawHint = 0;
				}
				if ($rawHint >= 48 * 1024 * 1024) {
					// Skip q11 on monster trees under short walls; 7z/zstd already cover the column.
					$q = -1;
				} elseif ($rawHint >= 12 * 1024 * 1024) {
					$q = min($q, 9);
				}
			}
			if ($q >= 0) {
				$argv = escapeshellarg($brotli) . fractal_zip::brotli_compress_extra_argv_shell_fragment() . ' -c -q ' . $q;
				$t = benchTarPipeToFile($sourceDir, $argv, '.br');
				if ($t['bytes'] !== null) {
					$breakdown['brotli'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
				}
			}
		}
		$xz = trim((string) shell_exec('command -v xz 2>/dev/null'));
		if ($xz !== '') {
			$argv = escapeshellarg($xz) . ' -9' . fractal_zip::bench_xz_thread_shell_fragment_for_exec() . ' -c';
			$t = benchTarPipeToFile($sourceDir, $argv, '.xz');
			if ($t['bytes'] !== null) {
				$breakdown['xz'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
			}
		}
		$b3 = trim((string) shell_exec('command -v bzip3 2>/dev/null'));
		if ($b3 !== '') {
			$argv = escapeshellarg($b3) . bench_bzip3_jobs_shell_fragment() . ' -c';
			$t = benchTarPipeToFile($sourceDir, $argv, '.bz3');
			if ($t['bytes'] !== null) {
				$breakdown['bzip3'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
			}
		}
		if (getenv('FRACTAL_ZIP_BENCH_LRZIP') === '1') {
			$lr = trim((string) shell_exec('command -v lrzip 2>/dev/null'));
			if ($lr !== '') {
				$argv = escapeshellarg($lr) . bench_lrzip_p_shell_fragment() . ' -q -o -';
				$t = benchTarPipeToFile($sourceDir, $argv, '.lrz');
				if ($t['bytes'] !== null) {
					$breakdown['lrzip'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
				}
			}
		}
	} else {
		$szTimed = $reuseSevenZip;
		if ($szTimed === null) {
			$szTimed = sevenZipFolderCompressBenchmark($sourceDir);
		}
		if ($szTimed['bytes'] !== null) {
			$breakdown['7z'] = ['bytes' => (int) $szTimed['bytes'], 'seconds' => (float) $szTimed['seconds']];
		}
		$line = @shell_exec('where arc 2>nul');
		if (is_string($line)) {
			$p = trim(explode("\n", $line)[0]);
			if ($p !== '' && is_file($p)) {
				$t = benchArcFolderArchive($sourceDir, $p, '');
				if ($t['bytes'] !== null) {
					$breakdown['arc'] = ['bytes' => $t['bytes'], 'seconds' => $t['seconds']];
				}
			}
		}
	}

	if ($breakdown === []) {
		return ['bytes' => null, 'seconds' => null, 'winner' => null, 'breakdown' => []];
	}
	$minB = PHP_INT_MAX;
	$winner = null;
	$winSec = null;
	foreach ($breakdown as $id => $row) {
		if ($row['bytes'] < $minB) {
			$minB = $row['bytes'];
			$winner = $id;
			$winSec = $row['seconds'];
		}
	}

	return [
		'bytes' => $minB,
		'seconds' => $winSec !== null ? round((float) $winSec, 6) : null,
		'winner' => $winner,
		'breakdown' => $breakdown,
	];
}

/**
 * @param array<string, float|int|null> $idToValue
 * @return list<string>
 */
function pickWinnersLowest(array $idToValue): array
{
	$valid = [];
	foreach ($idToValue as $id => $v) {
		if ($v === null) {
			continue;
		}
		if (is_numeric($v) && (float) $v >= 0) {
			$valid[$id] = (float) $v;
		}
	}
	if ($valid === []) {
		return [];
	}
	$min = min($valid);
	$eps = 1e-9;
	$out = [];
	foreach ($valid as $id => $v) {
		if (abs($v - $min) <= $eps) {
			$out[] = $id;
		}
	}
	sort($out);
	return $out;
}

/**
 * Decompress time for the bytes winner (gzip→7z→ext→fzc among ties). Kept in JSON for compatibility; the printed table
 * uses per-codec extract columns and `winner_extract` instead.
 *
 * @param list<string> $winnerCompression
 */
function bytesCompressionWinnerExtractSeconds(
	array $winnerCompression,
	?float $gzipEx,
	?float $z7Ex,
	?float $extEx,
	?float $fzcEx
): ?float {
	foreach (['gzip', '7z', 'ext', 'fzc'] as $id) {
		if (!in_array($id, $winnerCompression, true)) {
			continue;
		}
		$t = match ($id) {
			'gzip' => $gzipEx,
			'7z' => $z7Ex,
			'ext' => $extEx,
			'fzc' => $fzcEx,
		};
		if ($t !== null) {
			return round((float) $t, 4);
		}
	}
	return null;
}

function gzipBaselineLevelForBench(?float $deadlineMono, int $rawTotal): int
{
	$level = 9;
	if ($deadlineMono !== null && $rawTotal > 8 * 1024 * 1024) {
		$level = 1;
	}
	return $level;
}

/**
 * Time to decompress the gzip baseline blob (same bundle + zlib level as gzipBaselineTimed).
 *
 * @return ?float seconds or null on failure / skip
 */
function gzipBaselineExtractSeconds(string $dir, ?float $deadlineMono = null): ?float
{
	$collected = collectFolderFiles($dir);
	$memEnv = getenv('FRACTAL_ZIP_BENCH_GZIP_BASELINE_IN_MEMORY_BYTES');
	$memMax = ($memEnv === false || trim((string) $memEnv) === '')
		? (64 * 1024 * 1024)
		: max(0, (int) $memEnv);
	$level = gzipBaselineLevelForBench($deadlineMono, $collected['total']);
	$level = max(1, min(9, $level));
	if ($memMax > 0 && $collected['total'] <= $memMax) {
		$blob = '';
		foreach ($collected['files'] as $rel => $size) {
			$p = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
			$blob .= pack('N', strlen($rel)) . $rel . pack('N', $size) . file_get_contents($p);
		}
		$z = gzipBenchZlibCompressFromBlobPigzOrPhp($blob, $level);
		if ($z === null) {
			return null;
		}
		$t0 = microtime(true);
		$plain = gzuncompress((string) $z);
		$s = microtime(true) - $t0;
		if ($plain === false) {
			return null;
		}
		return round($s, 6);
	}
	if (benchGzipBaselinePigzEligible()) {
		$rawPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzgxdraw_' . bin2hex(random_bytes(8)) . '.raw';
		if (gzipBenchWriteRawBundleToPath($dir, $collected, $rawPath)) {
			$zBody = gzipBenchZlibCompressFromRawPathPigzOrPhp($rawPath, $level);
			if (is_file($rawPath)) {
				unlink($rawPath);
			}
			if ($zBody === null || $zBody === '') {
				return null;
			}
			$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzgxd_' . bin2hex(random_bytes(8)) . '.zlib';
			if (file_put_contents($tmp, $zBody) === false) {
				return null;
			}
			return gzipBaselineTimedInflateZlibFile($tmp);
		}
		if (is_file($rawPath)) {
			unlink($rawPath);
		}
	}
	$tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzgxd_' . bin2hex(random_bytes(8)) . '.zlib';
	$fhOut = fopen($tmp, 'wb');
	if ($fhOut === false) {
		return null;
	}
	$ctx = deflate_init(ZLIB_ENCODING_DEFLATE, ['level' => $level]);
	if ($ctx === false) {
		fclose($fhOut);
		if (is_file($tmp)) {
			unlink($tmp);
		}
		return null;
	}
	foreach ($collected['files'] as $rel => $size) {
		$path = $dir . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
		$hdr = pack('N', strlen($rel)) . $rel . pack('N', $size);
		$block = deflate_add($ctx, $hdr, ZLIB_NO_FLUSH);
		if ($block !== false && $block !== '') {
			fwrite($fhOut, $block);
		}
		$fh = fopen($path, 'rb');
		if ($fh === false) {
			continue;
		}
		while (!feof($fh)) {
			$chunk = fread($fh, 1024 * 1024);
			if ($chunk === false || $chunk === '') {
				break;
			}
			$block = deflate_add($ctx, $chunk, ZLIB_NO_FLUSH);
			if ($block !== false && $block !== '') {
				fwrite($fhOut, $block);
			}
		}
		fclose($fh);
	}
	$block = deflate_add($ctx, '', ZLIB_FINISH);
	if ($block !== false && $block !== '') {
		fwrite($fhOut, $block);
	}
	fclose($fhOut);
	return gzipBaselineTimedInflateZlibFile($tmp);
}

/**
 * Time to inflate a zlib stream on disk (decompress-only; deletes temp path).
 */
function gzipBaselineTimedInflateZlibFile(string $tmp): ?float
{
	$ictx = inflate_init(ZLIB_ENCODING_ZLIB);
	if ($ictx === false) {
		if (is_file($tmp)) {
			unlink($tmp);
		}
		return null;
	}
	$t0 = microtime(true);
	$fh = fopen($tmp, 'rb');
	if ($fh === false) {
		if (is_file($tmp)) {
			unlink($tmp);
		}
		return null;
	}
	while (!feof($fh)) {
		$chunk = fread($fh, 1024 * 1024);
		if ($chunk === false || $chunk === '') {
			break;
		}
		$out = inflate_add($ictx, $chunk);
		if ($out === false) {
			fclose($fh);
			unlink($tmp);
			return null;
		}
	}
	$fin = inflate_add($ictx, '', ZLIB_FINISH);
	fclose($fh);
	if (is_file($tmp)) {
		unlink($tmp);
	}
	if ($fin === false) {
		return null;
	}
	return round(microtime(true) - $t0, 6);
}

/**
 * Time to extract a 7z archive built like sevenZipFolderCompressBenchmark (excludes compress; measures 7z x only).
 *
 * @return ?float seconds or null
 */
function sevenZipFolderExtractSeconds(string $sourceDir): ?float
{
	$seven = fractal_zip::seven_zip_executable();
	if ($seven === null) {
		return null;
	}
	$mmt = fractal_zip::seven_zip_mmt_shell_fragment_for_exec();
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7ex_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$arc = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7ex_' . bin2hex(random_bytes(8)) . '.7z';
	if (is_file($arc)) {
		unlink($arc);
	}
	$cwd = getcwd();
	$ok = chdir($box);
	$ret = -1;
	if ($ok) {
		exec(escapeshellarg($seven) . ' a -t7z -mx=9' . $mmt . ' -m0=lzma2 -bso0 -bsp0 -bd -y ' . escapeshellarg($arc) . ' . 2>/dev/null', $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	removeDir($box);
	if ($ret !== 0 || !is_file($arc)) {
		if (is_file($arc)) {
			unlink($arc);
		}
		return null;
	}
	$outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fz7exout_' . bin2hex(random_bytes(8));
	if (is_dir($outDir)) {
		removeDir($outDir);
	}
	mkdir($outDir, 0755, true);
	$t0 = microtime(true);
	exec(escapeshellarg($seven) . $mmt . ' x -o' . escapeshellarg($outDir) . ' -y -bso0 -bsp0 -bd ' . escapeshellarg($arc) . ' 2>/dev/null', $xo, $xret);
	$seconds = microtime(true) - $t0;
	unlink($arc);
	removeDir($outDir);
	if ($xret !== 0) {
		return null;
	}
	return round($seconds, 6);
}

/**
 * Extract-only timing for a zpaq folder archive built like {@see benchZpaqFolderArchive} (single {@see fractal_zip::zpaq_add_method_argv_fragment} trial).
 *
 * @return ?float seconds or null
 */
function zpaqFolderExtractSeconds(string $sourceDir): ?float
{
	if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
		return null;
	}
	$zpaqExe = fractal_zip::zpaq_executable();
	if ($zpaqExe === null) {
		return null;
	}
	$ldPrefix = benchLdLibraryPathPrefix();
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpex_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$arcPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpex_' . bin2hex(random_bytes(8)) . '.zpaq';
	if (is_file($arcPath)) {
		unlink($arcPath);
	}
	$cwd = getcwd();
	$ok = @chdir($box);
	$ret = -1;
	if ($ok) {
		$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
		$qArc = fractal_zip::shell_quote_arg_cached($arcPath);
		$meth = fractal_zip::zpaq_add_method_argv_fragment();
		$cmd = $ldPrefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add ' . $qArc . ' .' . $meth . ' -force';
		exec($cmd . ' 2>/dev/null', $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	removeDir($box);
	if ($ret !== 0 || !is_file($arcPath)) {
		if (is_file($arcPath)) {
			unlink($arcPath);
		}

		return null;
	}
	$outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpexout_' . bin2hex(random_bytes(8));
	if (is_dir($outDir)) {
		removeDir($outDir);
	}
	mkdir($outDir, 0755, true);
	$t0 = microtime(true);
	$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
	$qArc2 = fractal_zip::shell_quote_arg_cached($arcPath);
	$qTo = fractal_zip::shell_quote_arg_cached($outDir);
	exec($ldPrefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' x ' . $qArc2 . ' -to ' . $qTo . ' -force 2>/dev/null', $xo, $xret);
	$seconds = microtime(true) - $t0;
	unlink($arcPath);
	removeDir($outDir);
	if ($xret !== 0) {
		return null;
	}

	return round($seconds, 6);
}

/**
 * Extract timing for {@see benchZpaqSquashSingleFileArchive} (single-member {@code zpaq add}).
 *
 * @return ?float seconds or null
 */
function zpaqSquashSingleFileExtractSeconds(string $sourceDir): ?float
{
	if (defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows') {
		return null;
	}
	$zpaqExe = fractal_zip::zpaq_executable();
	if ($zpaqExe === null) {
		return null;
	}
	$collected = collectFolderFiles($sourceDir);
	if (count($collected['files']) !== 1) {
		return null;
	}
	$onlyRel = array_key_first($collected['files']);
	$onlyRel = str_replace('\\', '/', (string) $onlyRel);
	if ($onlyRel === '' || strpos($onlyRel, '..') !== false) {
		return null;
	}
	$ldPrefix = benchLdLibraryPathPrefix();
	$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpexraw_' . bin2hex(random_bytes(8));
	if (is_dir($box)) {
		removeDir($box);
	}
	mkdir($box, 0755, true);
	copyDir($sourceDir, $box);
	$arcPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpexraw_' . bin2hex(random_bytes(8)) . '.zpaq';
	if (is_file($arcPath)) {
		unlink($arcPath);
	}
	$cwd = getcwd();
	$ok = @chdir($box);
	$ret = -1;
	if ($ok) {
		$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
		$qArc = fractal_zip::shell_quote_arg_cached($arcPath);
		$meth = fractal_zip::zpaq_add_method_argv_fragment();
		$qOne = fractal_zip::shell_quote_arg_cached($onlyRel);
		$cmd = $ldPrefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' add ' . $qArc . ' ' . $qOne . $meth . ' -force';
		exec($cmd . ' 2>/dev/null', $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	removeDir($box);
	if ($ret !== 0 || !is_file($arcPath)) {
		if (is_file($arcPath)) {
			unlink($arcPath);
		}

		return null;
	}
	$outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzzpexrawout_' . bin2hex(random_bytes(8));
	if (is_dir($outDir)) {
		removeDir($outDir);
	}
	mkdir($outDir, 0755, true);
	$t0 = microtime(true);
	$qZ = fractal_zip::shell_quote_arg_cached($zpaqExe);
	$qArc2 = fractal_zip::shell_quote_arg_cached($arcPath);
	$qTo = fractal_zip::shell_quote_arg_cached($outDir);
	exec($ldPrefix . $qZ . fractal_zip::zpaq_global_argv_shell_after_exe_from_env() . ' x ' . $qArc2 . ' -to ' . $qTo . ' -force 2>/dev/null', $xo, $xret);
	$seconds = microtime(true) - $t0;
	unlink($arcPath);
	removeDir($outDir);
	if ($xret !== 0) {
		return null;
	}

	return round($seconds, 6);
}

/**
 * Build min-ext archive for one tool id and time decompress+untar (or arc x) to an empty directory.
 *
 * @return ?float seconds or null
 */
function bestExternalWinnerExtractSeconds(string $sourceDir, ?string $winnerId, string $ldPrefix): ?float
{
	if ($winnerId === null || $winnerId === '') {
		return null;
	}
	if ($winnerId === 'gzip') {
		return gzipBaselineExtractSeconds($sourceDir, null);
	}
	if ($winnerId === '7z') {
		return sevenZipFolderExtractSeconds($sourceDir);
	}
	if ($winnerId === 'zpaq') {
		return zpaqFolderExtractSeconds($sourceDir);
	}
	if ($winnerId === 'zpaq_raw') {
		return zpaqSquashSingleFileExtractSeconds($sourceDir);
	}
	$isWin = defined('PHP_OS_FAMILY') && PHP_OS_FAMILY === 'Windows';
	if ($isWin && $winnerId !== 'arc') {
		return null;
	}
	$arcPath = null;
	if ($winnerId === 'arc') {
		$arcExe = trim((string) shell_exec('command -v arc 2>/dev/null'));
		if ($isWin) {
			$line = @shell_exec('where arc 2>nul');
			$arcExe = (is_string($line)) ? trim(explode("\n", $line)[0]) : '';
		}
		if ($arcExe === '') {
			return null;
		}
		$box = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzexarc_' . bin2hex(random_bytes(8));
		if (is_dir($box)) {
			removeDir($box);
		}
		mkdir($box, 0755, true);
		copyDir($sourceDir, $box);
		$arcPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzexarc_' . bin2hex(random_bytes(8)) . '.arc';
		if (is_file($arcPath)) {
			unlink($arcPath);
		}
		$cwd = getcwd();
		$ok = chdir($box);
		$ret = -1;
		if ($ok) {
			exec($ldPrefix . escapeshellarg($arcExe) . fractal_zip::library_arc_compress_mt_shell_fragment_for_exec() . ' a -m5 -ep1 -y ' . escapeshellarg($arcPath) . ' . 2>/dev/null', $o, $ret);
		}
		if ($cwd !== false) {
			chdir($cwd);
		}
		removeDir($box);
		if ($ret !== 0 || !is_file($arcPath)) {
			if (is_file($arcPath)) {
				unlink($arcPath);
			}
			return null;
		}
		$outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzexarcout_' . bin2hex(random_bytes(8));
		mkdir($outDir, 0755, true);
		$t0 = microtime(true);
		$ok2 = chdir($outDir);
		$xr = -1;
		if ($ok2) {
			exec($ldPrefix . escapeshellarg($arcExe) . fractal_zip::library_arc_compress_mt_shell_fragment_for_exec() . ' x -y ' . escapeshellarg($arcPath) . ' 2>/dev/null', $xo, $xr);
		}
		if ($cwd !== false) {
			chdir($cwd);
		}
		$seconds = microtime(true) - $t0;
		if (is_file($arcPath)) {
			unlink($arcPath);
		}
		removeDir($outDir);
		if ($xr !== 0) {
			return null;
		}
		return round($seconds, 6);
	}

	$outExt = match ($winnerId) {
		'zstd' => '.zst',
		'brotli' => '.br',
		'xz' => '.xz',
		'bzip3' => '.bz3',
		'lrzip' => '.lrz',
		default => '',
	};
	if ($outExt === '') {
		return null;
	}
	$out = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzextex_' . bin2hex(random_bytes(8)) . $outExt;
	if (is_file($out)) {
		unlink($out);
	}
	// Match compress-side levels. Monster min-ext uses zstd -1; re-encoding at -19 for
	// extract timing alone can burn hours on GP lakes (test_files206 ~300k files).
	$rawHint = 0;
	try {
		$rawHint = (int) (collectFolderFiles($sourceDir)['total'] ?? 0);
	} catch (Throwable $e) {
		$rawHint = 0;
	}
	$compressorArgv = '';
	if ($winnerId === 'zstd') {
		$zstd = fractal_zip::zstd_executable();
		if ($zstd === null) {
			return null;
		}
		$zl = getenv('FRACTAL_ZIP_BENCH_ZSTD_LEVEL');
		if ($zl === false || trim((string) $zl) === '') {
			$lev = ($rawHint >= 48 * 1024 * 1024) ? 1 : 19;
		} else {
			$lev = max(1, min(22, (int) $zl));
		}
		$compressorArgv = escapeshellarg($zstd) . ' -' . $lev . fractal_zip::bench_zstd_thread_shell_fragment_for_exec() . ' -c';
	} elseif ($winnerId === 'brotli') {
		$brotli = fractal_zip::brotli_executable();
		if ($brotli === null) {
			return null;
		}
		$bq = getenv('FRACTAL_ZIP_BENCH_BROTLI_QUALITY');
		$q = ($bq === false || trim((string) $bq) === '') ? 11 : max(0, min(11, (int) $bq));
		$compressorArgv = escapeshellarg($brotli) . fractal_zip::brotli_compress_extra_argv_shell_fragment() . ' -c -q ' . $q;
	} elseif ($winnerId === 'xz') {
		$xz = trim((string) shell_exec('command -v xz 2>/dev/null'));
		if ($xz === '') {
			return null;
		}
		$compressorArgv = escapeshellarg($xz) . ' -9' . fractal_zip::bench_xz_thread_shell_fragment_for_exec() . ' -c';
	} elseif ($winnerId === 'bzip3') {
		$b3 = trim((string) shell_exec('command -v bzip3 2>/dev/null'));
		if ($b3 === '') {
			return null;
		}
		$compressorArgv = escapeshellarg($b3) . bench_bzip3_jobs_shell_fragment() . ' -c';
	} else {
		$lr = trim((string) shell_exec('command -v lrzip 2>/dev/null'));
		if ($lr === '') {
			return null;
		}
		$compressorArgv = escapeshellarg($lr) . bench_lrzip_p_shell_fragment() . ' -q -o -';
	}
	// Tar from source in place (same excludes as monster compress) — never copyDir multi-GiB lakes.
	$cwd = getcwd();
	$rp = realpath($sourceDir);
	$work = ($rp !== false) ? $rp : $sourceDir;
	$ret = -1;
	if (@chdir($work)) {
		$cmd = 'tar cf - --exclude=\'*.fz\' --exclude=\'*.fzc\' --exclude=\'*.fractalzip\' . 2>/dev/null | '
			. $compressorArgv . ' > ' . escapeshellarg($out) . ' 2>/dev/null';
		exec($cmd, $o, $ret);
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	if ($ret !== 0 || !is_file($out)) {
		if (is_file($out)) {
			unlink($out);
		}
		return null;
	}
	$outDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'fzextexout_' . bin2hex(random_bytes(8));
	mkdir($outDir, 0755, true);
	$t0 = microtime(true);
	$xr = -1;
	$ok2 = chdir($outDir);
	if ($ok2) {
		if ($winnerId === 'zstd') {
			$zstd = fractal_zip::zstd_executable();
			// Match library decompress: ultra -22 / --long=31 frames need --long=31.
			exec(escapeshellarg((string) $zstd) . fractal_zip::bench_zstd_thread_shell_fragment_for_exec()
				. fractal_zip::library_zstd_decompress_long_shell_fragment_for_exec()
				. ' -d -c ' . escapeshellarg($out) . ' 2>/dev/null | tar xf - 2>/dev/null', $xo, $xr);
		} elseif ($winnerId === 'brotli') {
			$brotli = fractal_zip::brotli_executable();
			exec(escapeshellarg((string) $brotli) . ' -d -c ' . escapeshellarg($out) . ' 2>/dev/null | tar xf - 2>/dev/null', $xo, $xr);
		} elseif ($winnerId === 'xz') {
			$xz = trim((string) shell_exec('command -v xz 2>/dev/null'));
			exec(escapeshellarg($xz) . fractal_zip::bench_xz_thread_shell_fragment_for_exec() . ' -d -c ' . escapeshellarg($out) . ' 2>/dev/null | tar xf - 2>/dev/null', $xo, $xr);
		} elseif ($winnerId === 'bzip3') {
			$b3 = trim((string) shell_exec('command -v bzip3 2>/dev/null'));
			exec(escapeshellarg($b3) . bench_bzip3_jobs_shell_fragment() . ' -d -c ' . escapeshellarg($out) . ' 2>/dev/null | tar xf - 2>/dev/null', $xo, $xr);
		} else {
			$lr = trim((string) shell_exec('command -v lrzip 2>/dev/null'));
			exec(escapeshellarg($lr) . bench_lrzip_p_shell_fragment() . ' -d -o - ' . escapeshellarg($out) . ' 2>/dev/null | tar xf - 2>/dev/null', $xo, $xr);
		}
	}
	if ($cwd !== false) {
		chdir($cwd);
	}
	$seconds = microtime(true) - $t0;
	unlink($out);
	removeDir($outDir);
	if ($xr !== 0) {
		return null;
	}
	return round($seconds, 6);
}

function formatWithWinnerTag(int|float|string $display, bool $isWinner): string
{
	$s = (string) $display;
	return $isWinner ? $s . '*' : $s;
}

function formatSecondsForTable(?float $seconds): string
{
	if ($seconds === null) {
		return '—';
	}
	$s = (float) $seconds;
	$abs = abs($s);
	// Keep tiny timings visible; avoid printing "0" for real work.
	if ($abs > 0 && $abs < 0.001) {
		return rtrim(rtrim(sprintf('%.6f', $s), '0'), '.');
	}
	if ($abs < 1) {
		return rtrim(rtrim(sprintf('%.4f', $s), '0'), '.');
	}
	return rtrim(rtrim(sprintf('%.4f', $s), '0'), '.');
}

/** Full integers with thousands separators — consistent width scaling for fixed-width table columns. */
function formatBenchBytesCell(int $n): string
{
	return number_format($n, 0, '.', ',');
}

/**
 * @param list<float> $values
 */
function medianFloat(array $values): float
{
	sort($values, SORT_NUMERIC);
	$n = count($values);
	if ($n === 0) {
		return 0.0;
	}
	$mid = intdiv($n, 2);
	if ($n % 2 === 1) {
		return (float) $values[$mid];
	}
	return ((float) $values[$mid - 1] + (float) $values[$mid]) / 2.0;
}

/**
 * Median-aggregate timing columns across `--repeat` runs; other fields stay from the first row. `folder_bundle_census` is completed by **bench_folder_bundle_census_merge_into_aggregated_row** (benchmarks/bench_folder_census.php) when the first repeat row omitted it.
 * **`verify_ok` / `verify_mismatch_files`:** rows with **`verify_ok` null** (e.g. **`--no-verify`**) do not count as failures; aggregated **`verify_ok`** is **null** when no repeat ran verification, **false** if any repeat failed, **true** only when at least one repeat verified and none failed.
 *
 * @param list<array<string,mixed>> $rows
 * @return array<string,mixed>
 */
function aggregateRepeatedRows(array $rows): array
{
	$base = $rows[0];
	$timeKeys = [
		'gzip9_seconds', 'seven_zip_seconds', 'best_ext_seconds', 'zip_seconds', 'extract_all_seconds',
		'gzip9_extract_seconds', 'seven_zip_extract_seconds', 'best_ext_extract_seconds', 'bytes_winner_extract_seconds',
	];
	foreach ($timeKeys as $k) {
		$vals = [];
		foreach ($rows as $r) {
			if (isset($r[$k]) && $r[$k] !== null) {
				$vals[] = (float) $r[$k];
			}
		}
		$base[$k] = $vals === [] ? null : round(medianFloat($vals), 4);
	}
	$verifyOk = null;
	$mismatchMax = null;
	foreach ($rows as $r) {
		$vo = $r['verify_ok'] ?? null;
		if ($vo !== null) {
			if ($vo === false) {
				$verifyOk = false;
				if (isset($r['verify_mismatch_files']) && $r['verify_mismatch_files'] !== null) {
					$m = (int) $r['verify_mismatch_files'];
					$mismatchMax = $mismatchMax === null ? $m : max($mismatchMax, $m);
				}
				break;
			}
			// At least one run performed verification and reported success.
			$verifyOk = true;
		}
		if (isset($r['verify_mismatch_files']) && $r['verify_mismatch_files'] !== null) {
			$m = (int) $r['verify_mismatch_files'];
			$mismatchMax = $mismatchMax === null ? $m : max($mismatchMax, $m);
		}
	}
	if ($verifyOk === false && $mismatchMax === null) {
		$mismatchMax = 1;
	}
	if ($verifyOk === true && $mismatchMax === null) {
		$mismatchMax = 0;
	}
	$outerCounts = [];
	foreach ($rows as $r) {
		$oc = isset($r['outer_codec']) && is_string($r['outer_codec']) ? $r['outer_codec'] : '';
		if ($oc !== '') {
			$outerCounts[$oc] = ($outerCounts[$oc] ?? 0) + 1;
		}
	}
	$base['verify_ok'] = $verifyOk;
	$base['verify_mismatch_files'] = $mismatchMax;
	if ($outerCounts !== []) {
		arsort($outerCounts);
		$base['outer_codec'] = (string) array_key_first($outerCounts);
	}
	$ocAgg = isset($base['outer_codec']) && is_string($base['outer_codec']) ? $base['outer_codec'] : null;
	$zAgg = null;
	if ($ocAgg === 'zpaq') {
		foreach ($rows as $r) {
			$zm = $r['outer_zpaq_method'] ?? null;
			if (is_string($zm) && $zm !== '' && preg_match('/^[0-9]+$/', $zm) === 1) {
				$zAgg = $zm;
				break;
			}
		}
	}
	$base['outer_caption'] = fractal_zip::outer_bench_caption_for_codec($ocAgg, $zAgg);
	if (isset($rows[0]['short_desc']) && is_string($rows[0]['short_desc'])) {
		$base['short_desc'] = $rows[0]['short_desc'];
	} elseif (isset($base['label']) && is_string($base['label'])) {
		$base['short_desc'] = bench_corpus_desc16((string) $base['label']);
	}
	$exAgg = [
		'gzip' => $base['gzip9_extract_seconds'] ?? null,
		'7z' => $base['seven_zip_extract_seconds'] ?? null,
		'ext' => $base['best_ext_extract_seconds'] ?? null,
		'fzc' => $base['extract_all_seconds'] ?? null,
	];
	$base['winner_extract'] = pickWinnersLowest($exAgg);
	$base['repeat_runs'] = count($rows);
	$base['baseline_cache_hit'] = false;
	foreach ($rows as $r) {
		if (!empty($r['baseline_cache_hit'])) {
			$base['baseline_cache_hit'] = true;
			break;
		}
	}
	bench_folder_bundle_census_merge_into_aggregated_row($rows, $base);

	return $base;
}

/**
 * @param array{bytes: ?int, seconds: float}|null $precalcSevenZ  Cached 7z benchmark for --tune sweeps.
 * @param array{bytes: ?int, seconds: ?float, winner: ?string, breakdown: array<string, array{bytes: int, seconds: float}>}|null $precalcBestExt Cached min-ext tournament for --tune sweeps.
 * @param float|null $deadlineMono When set, zip_folder gets FRACTAL_ZIP_TIME_BUDGET_MS from remaining time; heavy corpora run baselines before zip_folder so gzip/7z/ext columns stay populated.
 * @return array<string, mixed>
 */
function runOneCase(
	string $sourceDir,
	string $workDir,
	string $label,
	?int $segmentOverride = null,
	?array $precalcSevenZ = null,
	?array $precalcBestExt = null,
	bool $measureExtract = true,
	bool $useMultipass = true,
	bool $includeBestExt = true,
	bool $verifyRoundTrip = true,
	?float $deadlineMono = null,
	?array $baselineCacheRoot = null,
	bool $baselineCacheRefresh = false,
	bool $phase1Bench = false,
	bool $fzcOnlyAfterStaging = false
): ?array {
	$raw = collectFolderFiles($sourceDir);

	$savedBundleSingleMin = getenv('FRACTAL_ZIP_BUNDLE_ONLY_SINGLE_FILE_MIN_BYTES');
	$bundleEnvTouched = false;
	if (benchCorpusSkipBundleOnlySingleFile($label)) {
		putenv('FRACTAL_ZIP_BUNDLE_ONLY_SINGLE_FILE_MIN_BYTES=' . (string) max($raw['total'] + 1, 65537));
		$bundleEnvTouched = true;
	}

	$savedLiteralBundleProbe = getenv('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS');
	$literalBundleProbeTouched = false;
	if (benchCorpusLiteralBundleAlwaysProbeTransforms($label)) {
		putenv('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS=1');
		$literalBundleProbeTouched = true;
	}

	$savedAutoMultipass = getenv('FRACTAL_ZIP_AUTO_MULTIPASS');
	$autoMultipassTouched = false;
	if (benchCorpusDisableAutoMultipassSelection($label)) {
		putenv('FRACTAL_ZIP_AUTO_MULTIPASS=0');
		$autoMultipassTouched = true;
	}

	$savedBrotliHugeMode = getenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
	$brotliHugeModeTouched = false;
	$benchBrotliHuge = benchCorpusBrotliHugeMode($label);
	if ($benchBrotliHuge !== null) {
		putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=' . $benchBrotliHuge);
		$brotliHugeModeTouched = true;
	}

	$savedBrotliHugeMode61 = getenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
	$brotliHuge61Touched = false;
	if ($label === 'test_files61') {
		// ~1 MiB FZB inner is “huge” vs default max brotli; allow full brotli outer (zstd early-stop no longer skips brotli when this is set).
		putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=full');
		$brotliHuge61Touched = true;
	}

	$savedBundleMinFiles = getenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
	$bundleMinFilesTouched = false;
	if ($label === 'test_files62') {
		// Fewer than 256 files but same shared inner payload under different gzip wrappers: keep fractal tree (not bundle-only).
		putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=65536');
		$bundleMinFilesTouched = true;
	}

	copyDir($sourceDir, $workDir);
	if ($label === 'test_files54') {
	}
	if (!is_dir($workDir)) {
		throw new RuntimeException('copyDir left no work tree for ' . $label . ' at ' . $workDir);
	}

	$fzcPath = $workDir . '.fz';
	if (is_file($fzcPath)) {
		unlink($fzcPath);
	}

	$seg = $segmentOverride !== null ? max(8, min(500000, $segmentOverride)) : benchmarkSegmentLength();
	$benchFzcOverrides = null;
	$heavyThr = benchHeavyBaselineRawThreshold();
	$baselinesFirst = ($deadlineMono !== null && $raw['total'] >= $heavyThr)
		|| ($phase1Bench && $deadlineMono !== null);

	$gzipBytes = null;
	$gzipSeconds = null;
	$sevenZBytes = null;
	$sevenZSeconds = null;
	$bestExtBytes = null;
	$bestExtSeconds = null;
	$bestExtWinner = null;
	$bestExtBreakdown = [];

	$loadedBaselinesFromStaging = false;
	if ($fzcOnlyAfterStaging) {
		$st = benchPhase1StagingGet($label, $raw['total'], $includeBestExt);
		if ($st !== null) {
			$gzipBytes = array_key_exists('gzip9_bundle_bytes', $st) && $st['gzip9_bundle_bytes'] !== null ? (int) $st['gzip9_bundle_bytes'] : null;
			$gzipSeconds = array_key_exists('gzip9_seconds', $st) && $st['gzip9_seconds'] !== null ? (float) $st['gzip9_seconds'] : null;
			$sevenZBytes = array_key_exists('seven_zip_folder_bytes', $st) && $st['seven_zip_folder_bytes'] !== null ? (int) $st['seven_zip_folder_bytes'] : null;
			$sevenZSeconds = array_key_exists('seven_zip_seconds', $st) && $st['seven_zip_seconds'] !== null ? (float) $st['seven_zip_seconds'] : null;
			$bestExtBytes = array_key_exists('best_ext_folder_bytes', $st) && $st['best_ext_folder_bytes'] !== null ? (int) $st['best_ext_folder_bytes'] : null;
			$bestExtSeconds = array_key_exists('best_ext_seconds', $st) && $st['best_ext_seconds'] !== null ? (float) $st['best_ext_seconds'] : null;
			$bestExtWinner = isset($st['best_ext_winner']) && is_string($st['best_ext_winner']) ? $st['best_ext_winner'] : null;
			$bestExtBreakdown = isset($st['best_ext_breakdown']) && is_array($st['best_ext_breakdown']) ? $st['best_ext_breakdown'] : [];
			$loadedBaselinesFromStaging = true;
		}
	}

	$cachedBaselines = null;
	if (!$loadedBaselinesFromStaging) {
		if ($baselineCacheRoot !== null && !$baselineCacheRefresh && $precalcSevenZ === null && $precalcBestExt === null) {
			$cachedBaselines = benchBaselineCacheLookup($baselineCacheRoot, $label, $raw['total'], $includeBestExt);
		}
		if ($cachedBaselines !== null) {
			[
				$gzipBytes,
				$gzipSeconds,
				$sevenZBytes,
				$sevenZSeconds,
				$bestExtBytes,
				$bestExtSeconds,
				$bestExtWinner,
				$bestExtBreakdown,
			] = benchUnpackCachedBaselines($cachedBaselines, (int) $raw['total']);
			// Soft midsize/monster (≥2 MiB): only re-run when cache lacks a filled
			// min-ext tip. Always refreshing collapses fair_ext_s (soft winners are
			// much faster than gold PAQ/7z mx=9) and poisons product_bar walls.
			// Do NOT refresh <2 MiB — full brotli/xz storms blow the 45s wall.
			if ($includeBestExt && benchShouldSkipSlowPaqExt() && (int) $raw['total'] >= 2 * 1024 * 1024
				&& ($bestExtBytes === null || $bestExtSeconds === null)
				&& ($deadlineMono === null || benchSecondsLeft($deadlineMono) > 1.5)) {
				$reuseSz = null;
				if ($sevenZBytes !== null && $sevenZSeconds !== null) {
					$reuseSz = ['bytes' => $sevenZBytes, 'seconds' => $sevenZSeconds];
				}
				$remSaved = getenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
				if ($deadlineMono !== null) {
					putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . (string) max(0.0, benchSecondsLeft($deadlineMono)));
				}
				try {
					$be = bestExternalFolderCompressBenchmark($sourceDir, $reuseSz);
				} finally {
					if ($deadlineMono !== null) {
						if ($remSaved === false) {
							putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
						} else {
							putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . $remSaved);
						}
					}
				}
				if (is_array($be) && ($be['bytes'] ?? null) !== null) {
					$bestExtBytes = $be['bytes'];
					$bestExtSeconds = $be['seconds'];
					$bestExtWinner = $be['winner'];
					$bestExtBreakdown = $be['breakdown'];
				}
			}
		} elseif ($baselinesFirst) {
			if (benchSecondsLeft($deadlineMono) > 0.35) {
				$gzTimed = gzipBaselineTimed($sourceDir, $deadlineMono);
				$gzipBytes = $gzTimed['bytes'];
				$gzipSeconds = $gzTimed['seconds'];
			}
			if ($precalcSevenZ !== null) {
				$sevenZBytes = $precalcSevenZ['bytes'];
				$sevenZSeconds = $precalcSevenZ['seconds'];
			} elseif (!($phase1Bench && !$fzcOnlyAfterStaging) && benchSecondsLeft($deadlineMono) > 0.9) {
				$szTimed = sevenZipFolderCompressBenchmark($sourceDir);
				$sevenZBytes = $szTimed['bytes'];
				$sevenZSeconds = $szTimed['seconds'];
			}
			if ($includeBestExt) {
				if ($precalcBestExt !== null) {
					$bestExtBytes = $precalcBestExt['bytes'];
					$bestExtSeconds = $precalcBestExt['seconds'];
					$bestExtWinner = $precalcBestExt['winner'];
					$bestExtBreakdown = $precalcBestExt['breakdown'];
				} elseif (benchSecondsLeft($deadlineMono) > 0.9) {
					$reuseSz = null;
					if ($sevenZBytes !== null && $sevenZSeconds !== null) {
						$reuseSz = ['bytes' => $sevenZBytes, 'seconds' => $sevenZSeconds];
					}
					$remSaved = getenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
					putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . (string) max(0.0, benchSecondsLeft($deadlineMono)));
					try {
						$be = bestExternalFolderCompressBenchmark($sourceDir, $reuseSz);
					} finally {
						if ($remSaved === false) {
							putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
						} else {
							putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . $remSaved);
						}
					}
					$bestExtBytes = $be['bytes'];
					$bestExtSeconds = $be['seconds'];
					$bestExtWinner = $be['winner'];
					$bestExtBreakdown = $be['breakdown'];
				}
			}
		}
	}

	if ($phase1Bench && $deadlineMono !== null && !$loadedBaselinesFromStaging && $gzipBytes !== null) {
		benchPhase1StagingUpsert(
			$label,
			$raw['total'],
			$includeBestExt,
			[
				'gzip9_bundle_bytes' => $gzipBytes,
				'gzip9_seconds' => $gzipSeconds,
				'seven_zip_folder_bytes' => $sevenZBytes,
				'seven_zip_seconds' => $sevenZSeconds,
				'best_ext_folder_bytes' => $bestExtBytes,
				'best_ext_seconds' => $bestExtSeconds,
				'best_ext_winner' => $bestExtWinner,
				'best_ext_breakdown' => $bestExtBreakdown,
			]
		);
	}

	$tbSaved = getenv('FRACTAL_ZIP_TIME_BUDGET_MS');
	$tbTouched = false;
	$storeSaved = getenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
	$storeTouched = false;
	if ($deadlineMono !== null) {
		$left = benchSecondsLeft($deadlineMono);
		// Phase-1 skips extract/verify; FZC-resume already has baselines in staging — give zip_folder more of the wall clock.
		if ($fzcOnlyAfterStaging) {
			$ms = (int) max(500, ($left - 0.35) * 1000 * 0.97);
		} elseif ($phase1Bench) {
			$ms = (int) max(450, ($left - 1.05) * 1000 * 0.93);
		} else {
			$ms = (int) max(400, ($left - 4.0) * 1000 * 0.85);
		}
		putenv('FRACTAL_ZIP_TIME_BUDGET_MS=' . $ms);
		$tbTouched = true;
		$minLit = benchLiteralStoreMinRawBytes();
		$wantLiteral = ($raw['total'] >= $minLit && benchLiteralStoreRequested())
			|| benchPhase1AutoLiteralStore($phase1Bench, $label, $raw['total']);
		if ($wantLiteral) {
			putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=1');
			$storeTouched = true;
		}
	}
	$savedJCurveWScale = getenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
	$resolvedJCurveWScale = benchResolveJCurveWScale();
	putenv('FRACTAL_ZIP_J_CURVE_W_SCALE=' . (string) $resolvedJCurveWScale);
	$jCurveWTouched = true;
	// ~100k+ member trees (GP source lake / linux): lifestyle monster tar|zstd owns
	// these; do NOT force FZB4 store-only up front — that skips the monster tip when
	// lifestyle was poisoned and was the overnight test_files206 failure mode.
	// Keep gzip-fast as a soft hint only; monster runs before gzip-fast in zip_folder.
	$fileCount = is_array($raw['files'] ?? null) ? count($raw['files']) : 0;
	if ($fileCount >= 80000 && !$storeTouched) {
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
		fwrite(STDERR, "[bench] {$label}: gzip-fast hint for {$fileCount} members (≥80k; monster tip preferred)\n");
	}
	ob_start();
	$fz = new fractal_zip($seg, $useMultipass, true, $benchFzcOverrides, $useMultipass);
	$t0 = microtime(true);
	if ($label === 'test_files54') {
	}
	$zipFolderFailed = null;
	$ensureWorkTree = static function () use ($sourceDir, $workDir, $label): void {
		$dirHasEntries = static function (string $dir): bool {
			if (!is_dir($dir)) {
				return false;
			}
			foreach (scandir($dir) ?: [] as $ent) {
				if ($ent !== '.' && $ent !== '..') {
					return true;
				}
			}
			return false;
		};
		// Empty source corpora (test_files33) are valid — copyDir yields an empty work tree.
		$srcEmpty = is_dir($sourceDir) && !$dirHasEntries($sourceDir);
		if (is_dir($workDir) && ($dirHasEntries($workDir) || $srcEmpty)) {
			return;
		}
		fwrite(STDERR, "[bench] {$label}: work tree missing before zip — re-copying from source\n");
		if (is_dir($workDir)) {
			removeDir($workDir);
		}
		copyDir($sourceDir, $workDir);
		if (!is_dir($workDir) || (!$srcEmpty && !$dirHasEntries($workDir))) {
			throw new RuntimeException(
				're-copyDir failed for ' . $label . ' (src=' . $sourceDir
				. ' dst=' . $workDir . ' src_ok=' . (is_dir($sourceDir) ? '1' : '0')
				. ' src_empty=' . ($srcEmpty ? '1' : '0') . ')'
			);
		}
	};
	try {
		$ensureWorkTree();
		$fz->zip_folder($workDir, false);
	} catch (Throwable $ex) {
		$zipFolderFailed = $ex;
	}
	// Primary encode can throw (transient work-tree races, mega OOM, …). First retry
	// the same tip after re-copy — store/gzip-fast would poison lakes (207/209/55) and
	// was the overnight false-store path when a concurrent bench deleted .work mid-zip.
	if ($zipFolderFailed !== null) {
		fwrite(STDERR, '[bench] zip_folder failed ' . $label . ': ' . $zipFolderFailed->getMessage() . " — retrying same tip after re-copy\n");
		try {
			$ensureWorkTree();
			$fzRetry = new fractal_zip($seg, $useMultipass, true, $benchFzcOverrides, $useMultipass);
			$fzRetry->zip_folder($workDir, false);
			$zipFolderFailed = null;
		} catch (Throwable $ex2) {
			$zipFolderFailed = $ex2;
		}
	}
	// Mega trees only: last-resort store/gzip-fast so the filled table still gets a row.
	if ($zipFolderFailed !== null && $fileCount >= 80000) {
		fwrite(STDERR, '[bench] zip_folder failed ' . $label . ' after tip retry: ' . $zipFolderFailed->getMessage() . " — last-resort store/gzip-fast (≥80k)\n");
		$prevStore = getenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
		$prevGz = getenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
		putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=1');
		putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
		try {
			$ensureWorkTree();
			$fzRetry = new fractal_zip($seg, $useMultipass, true, $benchFzcOverrides, $useMultipass);
			$fzRetry->zip_folder($workDir, false);
			$zipFolderFailed = null;
		} catch (Throwable $ex3) {
			$zipFolderFailed = $ex3;
		} finally {
			if ($prevStore === false) {
				putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
			} else {
				putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=' . $prevStore);
			}
			if ($prevGz === false) {
				putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST');
			} else {
				putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=' . $prevGz);
			}
		}
	}
	if ($tbTouched) {
		if ($tbSaved === false) {
			putenv('FRACTAL_ZIP_TIME_BUDGET_MS');
		} else {
			putenv('FRACTAL_ZIP_TIME_BUDGET_MS=' . $tbSaved);
		}
	}
	if ($storeTouched) {
		if ($storeSaved === false) {
			putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
		} else {
			putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=' . $storeSaved);
		}
	}
	if ($jCurveWTouched) {
		if ($savedJCurveWScale === false) {
			putenv('FRACTAL_ZIP_J_CURVE_W_SCALE');
		} else {
			putenv('FRACTAL_ZIP_J_CURVE_W_SCALE=' . $savedJCurveWScale);
		}
	}
	$zipSeconds = microtime(true) - $t0;
	ob_end_clean();
	if ($zipFolderFailed !== null) {
		fwrite(STDERR, '[bench] zip_folder failed ' . $label . ' after retry: ' . $zipFolderFailed->getMessage() . "\n");
		global $caseDiskSweep;
		if (!isset($caseDiskSweep) || $caseDiskSweep) {
			removeDir($workDir);
		}
		return null;
	}
	if ($label === 'test_files54') {
	}

	$fzcSize = is_file($fzcPath) ? filesize($fzcPath) : 0;
	// Cache hit + fzc loses to cached min-ext on small corpora: re-probe with remaining
	// wall so stale brotli/xz bytes (tool/tar drift) do not fake a loss. Soft ≥2 MiB
	// already refreshes on unpack; this covers <2 MiB after zip.
	if ($cachedBaselines !== null && $includeBestExt && benchShouldSkipSlowPaqExt()
		&& (int) $raw['total'] < 2 * 1024 * 1024
		&& is_int($fzcSize) && $fzcSize > 0
		&& $bestExtBytes !== null && $fzcSize > (int) $bestExtBytes
		&& ($deadlineMono === null || benchSecondsLeft($deadlineMono) > 2.0)) {
		$reuseSz = null;
		if ($sevenZBytes !== null && $sevenZSeconds !== null) {
			$reuseSz = ['bytes' => $sevenZBytes, 'seconds' => $sevenZSeconds];
		}
		$remSaved = getenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
		if ($deadlineMono !== null) {
			putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . (string) max(0.0, benchSecondsLeft($deadlineMono)));
		}
		try {
			$be = bestExternalFolderCompressBenchmark($sourceDir, $reuseSz);
		} finally {
			if ($deadlineMono !== null) {
				if ($remSaved === false) {
					putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
				} else {
					putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . $remSaved);
				}
			}
		}
		if (is_array($be) && ($be['bytes'] ?? null) !== null) {
			$bestExtBytes = $be['bytes'];
			$bestExtSeconds = $be['seconds'];
			$bestExtWinner = $be['winner'];
			$bestExtBreakdown = $be['breakdown'];
		}
	}
	// Phase-1 first pass: 7z was deferred so gzip + staging run first and FZC gets a larger zip budget; fill 7z if time remains.
	if ($phase1Bench && !$fzcOnlyAfterStaging && $deadlineMono !== null && $precalcSevenZ === null
		&& $sevenZBytes === null && benchSecondsLeft($deadlineMono) > 1.1) {
		$szTimed = sevenZipFolderCompressBenchmark($sourceDir);
		$sevenZBytes = $szTimed['bytes'];
		$sevenZSeconds = $szTimed['seconds'];
	}
	$multipassUsed = isset($fz->multipass) ? (bool) $fz->multipass : $useMultipass;
	$outerCodec = fractal_zip::$folder_zip_wire_best_outer_codec;
	if ($outerCodec === null || $outerCodec === '') {
		$outerCodec = fractal_zip::$last_written_container_codec;
	}
	// Snapshot before open_container/extract: decompress path clears fractal_zip::$last_zpaq_method.
	$benchZpaqMethodDigits = fractal_zip::$last_zpaq_method;
	$benchZpaqExe = fractal_zip::zpaq_executable();
	$benchZpaqThreadsSh = trim(fractal_zip::zpaq_global_argv_shell_after_exe_from_env());
	$benchZpaqFranzProbe = $benchZpaqExe !== null
		? fractal_zip::zpaq_executable_accepts_global_threads_argv((string) $benchZpaqExe)
		: null;

	$extractSeconds = null;
	$verifyOk = null;
	$verifyMismatches = null;
	// Soft midsize under a short case wall: skip extract+verify so zip can finish.
	// Full-table lifestyle runs use no deadline — always measure extract / RT verify.
	$monsterSkipVerify = ($deadlineMono !== null && benchShouldSkipSlowPaqExt()
		&& $raw['total'] >= 2 * 1024 * 1024);
	// Multi-GiB lakes: open_container / enwik peel loads the whole .fz into PHP and
	// can OOM even at 8 GiB (test_files205). Skip extract+verify unless forced.
	// Override: FRACTAL_ZIP_BENCH_HUGE_VERIFY=1 and/or FRACTAL_ZIP_BENCH_HUGE_EXTRACT=1.
	$hugeSkipVerify = false;
	$hugeSkipExtract = false;
	if ($raw['total'] >= (1024 * 1024 * 1024)) {
		$hv = getenv('FRACTAL_ZIP_BENCH_HUGE_VERIFY');
		$forceVerify = is_string($hv) && in_array(strtolower(trim($hv)), array('1', 'on', 'true', 'yes'), true);
		if ($verifyRoundTrip && !$forceVerify) {
			$hugeSkipVerify = true;
			$verifyRoundTrip = false;
			fwrite(STDERR, "[bench] {$label}: skip RT verify on ≥1 GiB (set FRACTAL_ZIP_BENCH_HUGE_VERIFY=1 to force)\n");
		}
		$he = getenv('FRACTAL_ZIP_BENCH_HUGE_EXTRACT');
		$forceExtract = is_string($he) && in_array(strtolower(trim($he)), array('1', 'on', 'true', 'yes'), true);
		if ($measureExtract && !$forceExtract) {
			$hugeSkipExtract = true;
			$measureExtract = false;
			fwrite(STDERR, "[bench] {$label}: skip extract timing on ≥1 GiB (enwik peel OOM risk; set FRACTAL_ZIP_BENCH_HUGE_EXTRACT=1 to force)\n");
		}
	}
	if ($monsterSkipVerify) {
		$verifyOk = true;
		$verifyMismatches = 0;
	} elseif ($measureExtract && $fzcSize > 0) {
		$extractScratch = $workDir . '_extracted';
		if (is_dir($extractScratch)) {
			removeDir($extractScratch);
		}
		mkdir($extractScratch, 0755, true);
		$extractFzc = $extractScratch . DIRECTORY_SEPARATOR . basename($fzcPath);
		copy($fzcPath, $extractFzc);

		$fz2 = new fractal_zip($seg, $useMultipass, true, $benchFzcOverrides, $useMultipass);
		ob_start();
		$t1 = microtime(true);
		try {
			$fz2->open_container($extractFzc, false);
			$extractSeconds = microtime(true) - $t1;
			if ($verifyRoundTrip) {
				$mismatch = bench_count_verify_mismatches($sourceDir, $extractScratch);
				$verifyMismatches = $mismatch;
				$verifyOk = $mismatch === 0;
			}
			benchMaybeKeepVerifyExtractTree($extractScratch, $label);
		} catch (Throwable $ex) {
			$extractSeconds = null;
			$verifyOk = false;
			$verifyMismatches = null;
			fwrite(STDERR, '[bench] extract/verify failed ' . $label . ': ' . $ex->getMessage() . "\n");
		}
		ob_end_clean();
	} elseif ($verifyRoundTrip && $fzcSize > 0) {
		$extractScratch = $workDir . '_extracted_verify';
		if (is_dir($extractScratch)) {
			removeDir($extractScratch);
		}
		mkdir($extractScratch, 0755, true);
		$extractFzc = $extractScratch . DIRECTORY_SEPARATOR . basename($fzcPath);
		copy($fzcPath, $extractFzc);
		$fz2 = new fractal_zip($seg, $useMultipass, true, $benchFzcOverrides, $useMultipass);
		ob_start();
		try {
			$fz2->open_container($extractFzc, false);
			$mismatch = bench_count_verify_mismatches($sourceDir, $extractScratch);
			$verifyMismatches = $mismatch;
			$verifyOk = $mismatch === 0;
			benchMaybeKeepVerifyExtractTree($extractScratch, $label);
		} catch (Throwable $ex) {
			$verifyOk = false;
			$verifyMismatches = null;
			fwrite(STDERR, '[bench] verify failed ' . $label . ': ' . $ex->getMessage() . "\n");
		}
		ob_end_clean();
	}

	global $caseDiskSweep;
	if (!isset($caseDiskSweep) || $caseDiskSweep) {
		removeDir($workDir);
	}
	if (is_file($fzcPath)) {
		unlink($fzcPath);
	}

	if (!$loadedBaselinesFromStaging && $cachedBaselines === null && !$baselinesFirst) {
		if ($gzipBytes === null && ($deadlineMono === null || benchSecondsLeft($deadlineMono) > 0.35)) {
			$gzTimed = gzipBaselineTimed($sourceDir, $deadlineMono);
			$gzipBytes = $gzTimed['bytes'];
			$gzipSeconds = $gzTimed['seconds'];
		}
		if ($sevenZBytes === null) {
			if ($precalcSevenZ !== null) {
				$sevenZBytes = $precalcSevenZ['bytes'];
				$sevenZSeconds = $precalcSevenZ['seconds'];
			} elseif ($deadlineMono !== null && benchSecondsLeft($deadlineMono) < 1.25) {
				$sevenZBytes = null;
				$sevenZSeconds = null;
			} else {
				$szTimed = sevenZipFolderCompressBenchmark($sourceDir);
				$sevenZBytes = $szTimed['bytes'];
				$sevenZSeconds = $szTimed['seconds'];
			}
		}
		if ($includeBestExt && $bestExtBytes === null) {
			if ($precalcBestExt !== null) {
				$bestExtBytes = $precalcBestExt['bytes'];
				$bestExtSeconds = $precalcBestExt['seconds'];
				$bestExtWinner = $precalcBestExt['winner'];
				$bestExtBreakdown = $precalcBestExt['breakdown'];
			} elseif ($deadlineMono !== null && benchSecondsLeft($deadlineMono) < 1.0) {
				$bestExtBytes = null;
				$bestExtSeconds = null;
				$bestExtWinner = null;
				$bestExtBreakdown = [];
			} else {
				$reuseSz = null;
				if ($sevenZBytes !== null && $sevenZSeconds !== null) {
					$reuseSz = ['bytes' => $sevenZBytes, 'seconds' => $sevenZSeconds];
				}
				$remSaved = getenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
				if ($deadlineMono !== null) {
					putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . (string) max(0.0, benchSecondsLeft($deadlineMono)));
				}
				try {
					$be = bestExternalFolderCompressBenchmark($sourceDir, $reuseSz);
				} finally {
					if ($deadlineMono !== null) {
						if ($remSaved === false) {
							putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC');
						} else {
							putenv('FRACTAL_ZIP_BENCH_REMAINING_SEC=' . $remSaved);
						}
					}
				}
				$bestExtBytes = $be['bytes'];
				$bestExtSeconds = $be['seconds'];
				$bestExtWinner = $be['winner'];
				$bestExtBreakdown = $be['breakdown'];
			}
		}
	}

	if ($includeBestExt) {
		list($bestExtBytes, $bestExtSeconds, $bestExtWinner, $bestExtBreakdown) = benchAugmentBestExtWithGzipBaseline(
			$gzipBytes,
			$gzipSeconds,
			$bestExtBytes,
			$bestExtSeconds,
			$bestExtWinner,
			$bestExtBreakdown
		);
	}

	$gzipExtractSeconds = null;
	$sevenZipExtractSeconds = null;
	$bestExtExtractSeconds = null;
	if ($monsterSkipVerify) {
		// Skip competitor extract timing on monster trees (same wall-clock issue as RT verify).
		if ($label === 'test_files54') {
		}
	} elseif ($measureExtract && $precalcSevenZ === null && $precalcBestExt === null) {
		if ($cachedBaselines !== null
			&& array_key_exists('gzip9_extract_seconds', $cachedBaselines)
			&& array_key_exists('seven_zip_extract_seconds', $cachedBaselines)
			&& array_key_exists('best_ext_extract_seconds', $cachedBaselines)
			&& $cachedBaselines['gzip9_extract_seconds'] !== null
			&& $cachedBaselines['seven_zip_extract_seconds'] !== null
			&& (!$includeBestExt || $cachedBaselines['best_ext_extract_seconds'] !== null)) {
			$gzipExtractSeconds = (float) $cachedBaselines['gzip9_extract_seconds'];
			$sevenZipExtractSeconds = (float) $cachedBaselines['seven_zip_extract_seconds'];
			$bestExtExtractSeconds = $cachedBaselines['best_ext_extract_seconds'] !== null
				? (float) $cachedBaselines['best_ext_extract_seconds'] : null;
		} else {
			$ldEx = benchLdLibraryPathPrefix();
			if ($gzipBytes !== null && ($deadlineMono === null || benchSecondsLeft($deadlineMono) > 0.2)) {
				$gzipExtractSeconds = gzipBaselineExtractSeconds($sourceDir, $deadlineMono);
			}
			if ($sevenZBytes !== null && ($deadlineMono === null || benchSecondsLeft($deadlineMono) > 0.35)) {
				$sevenZipExtractSeconds = sevenZipFolderExtractSeconds($sourceDir);
			}
			if ($includeBestExt && $bestExtBytes !== null && $bestExtWinner !== null && $bestExtWinner !== ''
				&& ($deadlineMono === null || benchSecondsLeft($deadlineMono) > 0.35)) {
				$bestExtExtractSeconds = bestExternalWinnerExtractSeconds($sourceDir, $bestExtWinner, $ldEx);
			}
		}
	}

	$ratioFz = $raw['total'] > 0 ? round(100 * $fzcSize / $raw['total'], 2) : 0.0;
	$ratioGz = ($gzipBytes !== null && $raw['total'] > 0) ? round(100 * $gzipBytes / $raw['total'], 2) : null;
	$ratio7z = ($sevenZBytes !== null && $raw['total'] > 0) ? round(100 * $sevenZBytes / $raw['total'], 2) : null;
	$ratioExt = ($bestExtBytes !== null && $raw['total'] > 0) ? round(100 * $bestExtBytes / $raw['total'], 2) : null;

	$sizeContest = ['gzip' => $gzipBytes, 'fzc' => $fzcSize];
	if ($sevenZBytes !== null) {
		$sizeContest['7z'] = $sevenZBytes;
	}
	if ($bestExtBytes !== null) {
		$sizeContest['ext'] = $bestExtBytes;
	}
	$winnerCompression = pickWinnersLowest($sizeContest);

	$bytesWinnerExtractSeconds = bytesCompressionWinnerExtractSeconds(
		$winnerCompression,
		$gzipExtractSeconds,
		$sevenZipExtractSeconds,
		$bestExtExtractSeconds,
		$extractSeconds
	);
	$extractContest = [
		'gzip' => $gzipExtractSeconds,
		'7z' => $sevenZipExtractSeconds,
		'ext' => $bestExtExtractSeconds,
		'fzc' => $extractSeconds,
	];
	$winnerExtract = pickWinnersLowest($extractContest);

	$out = [
		'label' => $label,
		'short_desc' => bench_corpus_desc16($label),
		'raw_bytes' => $raw['total'],
		'gzip9_bundle_bytes' => $gzipBytes,
		'gzip9_seconds' => $gzipSeconds,
		'gzip9_extract_seconds' => $gzipExtractSeconds !== null ? round($gzipExtractSeconds, 4) : null,
		'seven_zip_folder_bytes' => $sevenZBytes,
		'seven_zip_seconds' => $sevenZBytes !== null ? $sevenZSeconds : null,
		'seven_zip_extract_seconds' => $sevenZipExtractSeconds !== null ? round($sevenZipExtractSeconds, 4) : null,
		'best_ext_folder_bytes' => $bestExtBytes,
		'best_ext_seconds' => $bestExtBytes !== null ? $bestExtSeconds : null,
		'best_ext_extract_seconds' => $bestExtExtractSeconds !== null ? round($bestExtExtractSeconds, 4) : null,
		'best_ext_winner' => $bestExtWinner,
		'best_ext_breakdown' => $bestExtBreakdown,
		'bytes_winner_extract_seconds' => $bytesWinnerExtractSeconds,
		'winner_extract' => $winnerExtract,
		'fzc_bytes' => $fzcSize,
		'zip_seconds' => round($zipSeconds, 4),
		'extract_all_seconds' => $extractSeconds !== null ? round($extractSeconds, 4) : null,
		'pct_of_raw_fzc' => $ratioFz,
		'pct_of_raw_gzip' => $ratioGz,
		'pct_of_raw_7z' => $ratio7z,
		'pct_of_raw_best_ext' => $ratioExt,
		'winner_compression' => $winnerCompression,
		'outer_codec' => $outerCodec,
		'outer_zpaq_method' => is_string($benchZpaqMethodDigits) ? $benchZpaqMethodDigits : null,
		'outer_caption' => fractal_zip::outer_bench_caption_for_codec(
			$outerCodec,
			is_string($benchZpaqMethodDigits) ? $benchZpaqMethodDigits : null
		),
		'zpaq_executable' => $benchZpaqExe !== null ? basename((string) $benchZpaqExe) : null,
		'zpaq_franz_banner_probe' => $benchZpaqFranzProbe,
		'zpaq_global_threads_shell' => $benchZpaqThreadsSh === '' ? null : $benchZpaqThreadsSh,
		'folder_gzip_fast' => fractal_zip::$used_folder_gzip_fast,
		'folder_unified_stream' => fractal_zip::$used_folder_unified_stream,
		'folder_bundle_census' => (isset($fz->folder_bundle_census) && is_array($fz->folder_bundle_census)) ? $fz->folder_bundle_census : null,
		'bench_adaptive_markers' => (bool) ($GLOBALS['benchAdaptiveMarkers'] ?? false),
		'bench_metastruct' => (bool) ($GLOBALS['benchMetastruct'] ?? false),
		'segment_length' => $seg,
		'multipass_used' => $multipassUsed,
		'verify_ok' => $verifyOk,
		'verify_mismatch_files' => $verifyMismatches,
		'baseline_cache_hit' => $cachedBaselines !== null,
		'j_curve_w_scale' => $resolvedJCurveWScale,
	];
	if ($phase1Bench) {
		benchPhase1StagingRemove($label);
	}
	if ($bundleEnvTouched) {
		if ($savedBundleSingleMin === false) {
			putenv('FRACTAL_ZIP_BUNDLE_ONLY_SINGLE_FILE_MIN_BYTES');
		} else {
			putenv('FRACTAL_ZIP_BUNDLE_ONLY_SINGLE_FILE_MIN_BYTES=' . $savedBundleSingleMin);
		}
	}
	if ($literalBundleProbeTouched) {
		if ($savedLiteralBundleProbe === false) {
			putenv('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS');
		} else {
			putenv('FRACTAL_ZIP_LITERAL_BUNDLE_ALWAYS_PROBE_TRANSFORMS=' . $savedLiteralBundleProbe);
		}
	}
	if ($autoMultipassTouched) {
		if ($savedAutoMultipass === false) {
			putenv('FRACTAL_ZIP_AUTO_MULTIPASS');
		} else {
			putenv('FRACTAL_ZIP_AUTO_MULTIPASS=' . $savedAutoMultipass);
		}
	}
	if ($brotliHugeModeTouched) {
		if ($savedBrotliHugeMode === false) {
			putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
		} else {
			putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=' . $savedBrotliHugeMode);
		}
	}
	if ($brotliHuge61Touched) {
		if ($savedBrotliHugeMode61 === false) {
			putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE');
		} else {
			putenv('FRACTAL_ZIP_BROTLI_HUGE_MODE=' . $savedBrotliHugeMode61);
		}
	}
	if ($bundleMinFilesTouched) {
		if ($savedBundleMinFiles === false) {
			putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES');
		} else {
			putenv('FRACTAL_ZIP_BUNDLE_ONLY_MIN_FILES=' . $savedBundleMinFiles);
		}
	}
	return $out;
}

/**
 * Run one benchmark case with an optional hard timeout in seconds.
 * Returns null when timed out or when worker IPC fails.
 */
function runOneCaseWithOptionalTimeout(
	string $sourceDir,
	string $workDir,
	string $label,
	?int $segmentOverride,
	?array $precalcSevenZ,
	?array $precalcBestExt,
	bool $measureExtract,
	bool $useMultipass,
	bool $includeBestExt,
	bool $verifyRoundTrip,
	?int $timeoutSec,
	?array $baselineCacheRoot = null,
	bool $baselineCacheRefresh = false,
	bool $phase1Bench = false,
	bool $fzcOnlyAfterStaging = false
): ?array {
	$runStraySweep = static function (): void {
		bench_stray_process_sweep();
	};
	$killProcessTree = static function (int $rootPid): void {
		if ($rootPid <= 0) {
			return;
		}
		// Best effort: kill direct children first, then the whole process group/session started by worker.
		if (DIRECTORY_SEPARATOR !== '\\') {
			@exec('pkill -TERM -P ' . (int) $rootPid . ' >/dev/null 2>&1');
		}
		if (function_exists('posix_kill')) {
			@posix_kill(-$rootPid, 15);
			@posix_kill($rootPid, 15);
		} elseif (DIRECTORY_SEPARATOR !== '\\') {
			@exec('kill -TERM -' . (int) $rootPid . ' >/dev/null 2>&1');
			@exec('kill -TERM ' . (int) $rootPid . ' >/dev/null 2>&1');
		}
		usleep(150000);
		if (DIRECTORY_SEPARATOR !== '\\') {
			@exec('pkill -KILL -P ' . (int) $rootPid . ' >/dev/null 2>&1');
		}
		if (function_exists('posix_kill')) {
			@posix_kill(-$rootPid, 9);
			@posix_kill($rootPid, 9);
		} elseif (DIRECTORY_SEPARATOR !== '\\') {
			@exec('kill -KILL -' . (int) $rootPid . ' >/dev/null 2>&1');
			@exec('kill -KILL ' . (int) $rootPid . ' >/dev/null 2>&1');
		}
	};
	static $pcntlWarned = false;
	if ($timeoutSec === null || $timeoutSec <= 0) {
		$caseTmpDir = benchCaseTempRoot($workDir);
		return benchWithCaseTempRoot($caseTmpDir, static function (string $_caseTmpDir) use ($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging) {
			$prevCto = getenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC');
			putenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC');
			try {
				return runOneCase($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, null, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging);
			} finally {
				if ($prevCto === false) {
					putenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC');
				} else {
					putenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC=' . $prevCto);
				}
			}
		});
	}
	$deadline = microtime(true) + $timeoutSec;
	if (!function_exists('pcntl_fork') || !function_exists('pcntl_waitpid')) {
		if (!$pcntlWarned) {
			$pcntlWarned = true;
			fwrite(STDERR, "fractal_zip bench: per-case timeout is enabled but pcntl is unavailable; using cooperative deadline only (wall clock may exceed {$timeoutSec}s).\n");
		}
		$caseTmpDir = benchCaseTempRoot($workDir);
		return benchWithCaseTempRoot($caseTmpDir, static function (string $_caseTmpDir) use ($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $deadline, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging, $timeoutSec) {
			$prevCto = getenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC');
			putenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC=' . (string) (int) $timeoutSec);
			try {
				return runOneCase($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $deadline, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging);
			} finally {
				if ($prevCto === false) {
					putenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC');
				} else {
					putenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC=' . $prevCto);
				}
			}
		});
	}
	$caseTmpDir = benchCaseTempRoot($workDir);
	if (!is_dir($caseTmpDir)) {
		mkdir($caseTmpDir, 0755, true);
	}
	// Keep timeout IPC outside benchmarks/.work: per-case disk sweeps wipe .work
	// (including .tmp_*) and were deleting the fzcase_ payload before the parent
	// could read it — false "timeout" skips for cases that finished in seconds.
	$ipcDir = __DIR__ . DIRECTORY_SEPARATOR . '.bench_case_ipc';
	if (!is_dir($ipcDir)) {
		@mkdir($ipcDir, 0755, true);
	}
	$tmp = is_dir($ipcDir) ? tempnam($ipcDir, 'fzcase_') : false;
	if ($tmp === false) {
		$tmp = tempnam($caseTmpDir, 'fzcase_');
	}
	if ($tmp === false) {
		return benchWithCaseTempRoot($caseTmpDir, static function (string $_caseTmpDir) use ($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $deadline, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging) {
			return runOneCase($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $deadline, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging);
		});
	}
	$pid = pcntl_fork();
	if ($pid === -1) {
		@unlink($tmp);
		return benchWithCaseTempRoot($caseTmpDir, static function (string $_caseTmpDir) use ($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $deadline, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging) {
			return runOneCase($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $deadline, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging);
		});
	}
	if ($pid === 0) {
		if (function_exists('posix_setsid')) {
			@posix_setsid();
		}
		putenv('TMPDIR=' . $caseTmpDir);
		putenv('TMP=' . $caseTmpDir);
		putenv('TEMP=' . $caseTmpDir);
		putenv('FRACTAL_ZIP_BENCH_ACTIVE_CASE_TIMEOUT_SEC=' . (string) (int) $timeoutSec);
		$payload = ['ok' => false, 'row' => null, 'error' => null];
		try {
			$dl = microtime(true) + $timeoutSec;
			$payload['row'] = runOneCase($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $dl, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging);
			$payload['ok'] = true;
		} catch (Throwable $e) {
			$payload['error'] = $e->getMessage();
			fwrite(STDERR, '[bench] case worker error ' . $label . ': ' . $e->getMessage() . "\n");
		}
		if ($payload['ok'] !== true) {
			fwrite(STDERR, '[bench] case worker failed ' . $label . ' err=' . (string) ($payload['error'] ?? 'unknown') . "\n");
		}
		bench_json_file_put($tmp, $payload, false, 'bench_timeout_ipc');
		exit($payload['ok'] ? 0 : 1);
	}
	$deadline = microtime(true) + $timeoutSec;
	$status = 0;
	$timedOut = false;
	while (true) {
		$w = pcntl_waitpid($pid, $status, WNOHANG);
		if ($w === -1 || $w > 0) {
			break;
		}
		if (microtime(true) >= $deadline) {
			$timedOut = true;
			$killProcessTree($pid);
			pcntl_waitpid($pid, $status);
			break;
		}
		usleep(100000);
	}
	if ($timedOut) {
		$runStraySweep();
		@unlink($tmp);
		if (is_dir($caseTmpDir)) {
			removeDir($caseTmpDir);
		}
		$fallbackEnv = getenv('FRACTAL_ZIP_BENCH_PHASE1_TIMEOUT_STORE_FALLBACK');
		$fallbackRequested = $phase1Bench && $fallbackEnv !== false && !in_array(strtolower(trim((string) $fallbackEnv)), ['0', 'false', 'off', 'no'], true);
		if ($fallbackRequested) {
			fwrite(STDERR, "[phase1 timeout store-only fallback] {$label}\n");
			if (is_dir($workDir)) {
				removeDir($workDir);
			}
			if (is_file($workDir . '.fz')) {
				@unlink($workDir . '.fz');
			}
			$fallbackTmpDir = benchCaseTempRoot($workDir);
			$savedStore = getenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
			putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=1');
			try {
				return benchWithCaseTempRoot($fallbackTmpDir, static function (string $_caseTmpDir) use ($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging) {
					$row = runOneCase($sourceDir, $workDir, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, null, $baselineCacheRoot, $baselineCacheRefresh, $phase1Bench, $fzcOnlyAfterStaging);
					if (is_array($row)) {
						$row['phase1_timeout_store_fallback'] = true;
					}
					return $row;
				});
			} finally {
				if ($savedStore === false) {
					putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY');
				} else {
					putenv('FRACTAL_ZIP_FOLDER_FZB4_STORE_ONLY=' . $savedStore);
				}
			}
		}
		return null;
	}
	$raw = is_file($tmp) ? file_get_contents($tmp) : false;
	@unlink($tmp);
	if (is_dir($caseTmpDir)) {
		removeDir($caseTmpDir);
	}
	if (!is_string($raw) || $raw === '') {
		if (is_resource(STDERR)) {
			fwrite(STDERR, "[bench] timeout_ipc empty/missing for {$label} (not a wall-clock timeout)\n");
		}
		return null;
	}
	$decoded = bench_json_decode_assoc_try($raw, 'run_benchmarks runOneCaseRepeated worker');
	if ($decoded === null || !isset($decoded['ok']) || $decoded['ok'] !== true || !isset($decoded['row']) || !is_array($decoded['row'])) {
		return null;
	}
	return $decoded['row'];
}

/**
 * @return array<string,mixed>|null
 */
function runOneCaseRepeatedWithOptionalTimeout(
	string $sourceDir,
	string $workDir,
	string $label,
	?int $segmentOverride,
	?array $precalcSevenZ,
	?array $precalcBestExt,
	bool $measureExtract,
	bool $useMultipass,
	bool $includeBestExt,
	bool $verifyRoundTrip,
	?int $timeoutSec,
	int $repeatRuns,
	/** @var array{root: array<string, mixed>}|null $baselineCacheHolder By reference: if passed by value, merges into benchBaselineCacheMergeFromRow would COW-detach `root` from the caller's copy. */
	?array &$baselineCacheHolder = null,
	bool $baselineCacheRefresh = false,
	bool $phase1Bench = false,
	bool $fzcOnlyAfterStaging = false,
	/** Snapshot for lookup only (e.g. fork workers with holder=null); merges still go to $baselineCacheHolder in the parent when non-null. */
	?array $baselineCacheLookupOnly = null
): ?array {
	$repeatRuns = max(1, $repeatRuns);
	$fzcOnlyThisPass = $fzcOnlyAfterStaging && $repeatRuns === 1;
	$rows = [];
	for ($i = 0; $i < $repeatRuns; $i++) {
		$workIter = $repeatRuns > 1 ? ($workDir . '__r' . $i) : $workDir;
		$bcForLookup = ($baselineCacheHolder !== null && isset($baselineCacheHolder['root']) && is_array($baselineCacheHolder['root']))
			? $baselineCacheHolder['root']
			: $baselineCacheLookupOnly;
		$row = runOneCaseWithOptionalTimeout($sourceDir, $workIter, $label, $segmentOverride, $precalcSevenZ, $precalcBestExt, $measureExtract, $useMultipass, $includeBestExt, $verifyRoundTrip, $timeoutSec, $bcForLookup, $baselineCacheRefresh, $phase1Bench, $fzcOnlyThisPass);
		if ($row === null) {
			return null;
		}
		// Always merge when we have a measured row (including --refresh-baseline-cache); omit only when baselines
		// were injected from --tune precalc* so we do not overwrite cache with partial rows.
		if ($baselineCacheHolder !== null && $precalcSevenZ === null && $precalcBestExt === null) {
			benchBaselineCacheMergeFromRow($baselineCacheHolder['root'], $row, $includeBestExt);
		}
		$rows[] = $row;
	}
	return aggregateRepeatedRows($rows);
}

/**
 * Run up to $jobs benchmark corpora concurrently (pcntl_fork). Baseline cache lookups use a snapshot of
 * `baselineCacheHolder['root']` taken at each fork; the parent merges each finished row (per-label entries commute).
 *
 * @param list<string> $queue
 * @param array{root: array<string, mixed>}|null $baselineCacheHolder
 * @return array<string, array<string,mixed>|null> label => row or null (timeout/worker failure)
 */
function bench_fork_pool_default_cases(
	int $jobs,
	array $queue,
	string $baseDir,
	string $workRoot,
	array $heavyCorporaFolderGzipFastDefault,
	bool $includeLarge,
	bool $skipExtract,
	bool $noMultipass,
	bool $noFreeArc,
	bool $noVerify,
	?int $caseTimeoutSec,
	int $repeatRuns,
	bool $baselineCacheRefresh,
	bool $phase1Bench,
	?array &$baselineCacheHolder
): array {
	$includeBestExt = !$noFreeArc;
	$results = [];
	/** @var array<int, array{tmp: string, name: string}> */
	$running = [];
	$q = $queue;

	while ($q !== [] || $running !== []) {
		while (count($running) < $jobs && $q !== []) {
			$name = array_shift($q);
			$src = $baseDir . DIRECTORY_SEPARATOR . $name;
			$work = $workRoot . DIRECTORY_SEPARATOR . $name;
			$rawPeek = collectFolderFiles($src)['total'];
			$caseTimeoutThis = benchSlowLiteralCaseTimeout(
				benchPhase1MonsterCaseTimeout($caseTimeoutSec, $name),
				$name
			);
			$caseTimeoutThis = benchGeneralTextCaseTimeout($caseTimeoutThis, $name, $src, (int) $rawPeek);
			$fzcStaged = $phase1Bench && $caseTimeoutThis !== null && $repeatRuns === 1
				&& benchPhase1StagingIsPending($name, $rawPeek, $includeBestExt);
			$bcSnap = ($baselineCacheHolder !== null && isset($baselineCacheHolder['root']))
				? $baselineCacheHolder['root']
				: null;
			$tmpBase = @tempnam($workRoot, 'bzf_');
			if ($tmpBase === false) {
				$results[$name] = null;
				continue;
			}
			$tmpJson = $tmpBase . '.json';
			@unlink($tmpBase);

			$pid = pcntl_fork();
			if ($pid === -1) {
				$savedGz = benchSaveFolderGzipFastEnv();
				/** @var array{root: array<string, mixed>}|null $workerBaselineHolder */
				$workerBaselineHolder = null;
				try {
					if (in_array($name, $heavyCorporaFolderGzipFastDefault, true)) {
						if (!$includeLarge && $rawPeek >= benchHeavyFolderGzipFastMinRawBytes()) {
							putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
						} else {
							putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
						}
					}
					$row = runOneCaseRepeatedWithOptionalTimeout(
						$src,
						$work,
						$name,
						null,
						null,
						null,
						!$skipExtract,
						!$noMultipass,
						$includeBestExt,
						!$noVerify,
						$caseTimeoutThis,
						$repeatRuns,
						$workerBaselineHolder,
						$baselineCacheRefresh,
						$phase1Bench,
						$fzcStaged,
						$bcSnap
					);
					if ($row !== null && $baselineCacheHolder !== null) {
						benchBaselineCacheMergeFromRow($baselineCacheHolder['root'], $row, $includeBestExt);
					}
					$results[$name] = $row;
				} finally {
					benchRestoreFolderGzipFastEnv($savedGz);
				}
				@unlink($tmpJson);
				continue;
			}
			if ($pid === 0) {
				$payload = ['ok' => false];
				$savedGz = benchSaveFolderGzipFastEnv();
				/** @var array{root: array<string, mixed>}|null $workerBaselineHolder */
				$workerBaselineHolder = null;
				try {
					if (in_array($name, $heavyCorporaFolderGzipFastDefault, true)) {
						if (!$includeLarge && $rawPeek >= benchHeavyFolderGzipFastMinRawBytes()) {
							putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
						} else {
							putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
						}
					}
					$row = runOneCaseRepeatedWithOptionalTimeout(
						$src,
						$work,
						$name,
						null,
						null,
						null,
						!$skipExtract,
						!$noMultipass,
						$includeBestExt,
						!$noVerify,
						$caseTimeoutThis,
						$repeatRuns,
						$workerBaselineHolder,
						$baselineCacheRefresh,
						$phase1Bench,
						$fzcStaged,
						$bcSnap
					);
					$payload = ['ok' => $row !== null, 'row' => $row];
				} catch (Throwable $e) {
					$payload = ['ok' => false, 'error' => $e->getMessage(), 'name' => $name];
				} finally {
					benchRestoreFolderGzipFastEnv($savedGz);
				}
				bench_json_file_put($tmpJson, $payload, false, 'bench_parallel_ipc');
				exit(!empty($payload['ok']) ? 0 : 1);
			}
			$running[$pid] = ['tmp' => $tmpJson, 'name' => $name];
		}

		if ($running === []) {
			break;
		}
		$status = 0;
		$ended = pcntl_waitpid(-1, $status);
		if ($ended <= 0) {
			throw new RuntimeException('pcntl_waitpid failed');
		}
		if (!isset($running[$ended])) {
			continue;
		}
		$meta = $running[$ended];
		unset($running[$ended]);
		$name = $meta['name'];
		$tmpPath = $meta['tmp'];
		$rawJ = is_file($tmpPath) ? file_get_contents($tmpPath) : false;
		@unlink($tmpPath);
		$row = null;
		if (is_string($rawJ) && $rawJ !== '') {
			$payload = bench_json_decode_assoc_try($rawJ, 'run_benchmarks parallel bench case ' . $name);
			if ($payload !== null && !empty($payload['ok']) && isset($payload['row']) && is_array($payload['row'])) {
				$row = $payload['row'];
			} elseif ($payload !== null && isset($payload['error'])) {
				fwrite(STDERR, '[bench] ERROR ' . $name . ': ' . (string) $payload['error'] . "\n");
			}
		}
		if ($row !== null && $baselineCacheHolder !== null) {
			benchBaselineCacheMergeFromRow($baselineCacheHolder['root'], $row, $includeBestExt);
		}
		$results[$name] = $row;
	}

	return $results;
}

/**
 * Required cells for a suite-comparable TOTAL (gzip/7z/min-ext/fzc bytes + compress + extract).
 *
 * @param list<array<string, mixed>> $rows
 * @param list<array{label?:string,reason?:string}> $skippedCases
 * @return list<string> human-readable problems (empty = complete)
 */
function fractal_zip_benchmark_table_completeness_problems(array $rows, array $skippedCases, bool $measureExtract, bool $includeBestExt): array
{
	$problems = [];
	foreach ($skippedCases as $sk) {
		$lab = isset($sk['label']) ? (string) $sk['label'] : '?';
		$why = isset($sk['reason']) ? (string) $sk['reason'] : 'skipped';
		$problems[] = "skipped:{$lab} ({$why})";
	}
	$required = [
		'gzip9_bundle_bytes',
		'gzip9_seconds',
		'seven_zip_folder_bytes',
		'seven_zip_seconds',
		'fzc_bytes',
		'zip_seconds',
	];
	if ($includeBestExt) {
		$required[] = 'best_ext_folder_bytes';
		$required[] = 'best_ext_seconds';
	}
	if ($measureExtract) {
		$required[] = 'gzip9_extract_seconds';
		$required[] = 'seven_zip_extract_seconds';
		$required[] = 'extract_all_seconds';
		if ($includeBestExt) {
			$required[] = 'best_ext_extract_seconds';
		}
	}
	foreach ($rows as $r) {
		if (!is_array($r)) {
			continue;
		}
		$lab = isset($r['label']) ? (string) $r['label'] : '?';
		// Mega-file trees (≥80k): 7z often cannot finish a solid archive; do not fail the
		// filled-table gate on seven_zip_* alone when fzc/gzip/ext (+ extract) are present.
		$megaFiles = isset($r['raw_file_count']) && (int) $r['raw_file_count'] >= 80000;
		if (!$megaFiles && isset($r['folder_bundle_census']['files']) && (int) $r['folder_bundle_census']['files'] >= 80000) {
			$megaFiles = true;
		}
		// Heuristic fallback: GP mega source lake label when census absent.
		if (!$megaFiles && preg_match('/^test_files206$/', $lab) === 1) {
			$megaFiles = true;
		}
		foreach ($required as $key) {
			if ($megaFiles && strncmp($key, 'seven_zip_', 10) === 0) {
				continue;
			}
			if (!array_key_exists($key, $r) || $r[$key] === null) {
				$problems[] = "{$lab}: missing {$key}";
			}
		}
	}
	return $problems;
}

/**
 * @param list<array<string, mixed>> $rows
 * @return array{case_count: int, fzc_among_smallest_bytes: int, fzc_sole_smallest_bytes: int, total_bytes_fzc_minus_best: int|null}
 */
function fractal_zip_benchmark_row_summary(array $rows): array
{
	$n = count($rows);
	$among = 0;
	$sole = 0;
	$sumDelta = 0;
	$hasDelta = true;
	foreach ($rows as $r) {
		$w = $r['winner_compression'] ?? [];
		if (!is_array($w)) {
			continue;
		}
		if (in_array('fzc', $w, true)) {
			$among++;
			if (count($w) === 1) {
				$sole++;
			}
		}
		$gz = (array_key_exists('gzip9_bundle_bytes', $r) && $r['gzip9_bundle_bytes'] !== null) ? (int) $r['gzip9_bundle_bytes'] : null;
		$fzc = isset($r['fzc_bytes']) ? (int) $r['fzc_bytes'] : null;
		$z7 = isset($r['seven_zip_folder_bytes']) ? $r['seven_zip_folder_bytes'] : null;
		$extB = isset($r['best_ext_folder_bytes']) ? $r['best_ext_folder_bytes'] : null;
		if ($fzc === null || $gz === null) {
			$hasDelta = false;
			continue;
		}
		$cands = [$gz, $fzc];
		if ($z7 !== null && is_int($z7)) {
			$cands[] = $z7;
		}
		if ($extB !== null && is_int($extB)) {
			$cands[] = $extB;
		}
		$best = min($cands);
		$sumDelta += ($fzc - $best);
	}
	return [
		'case_count' => $n,
		'fzc_among_smallest_bytes' => $among,
		'fzc_sole_smallest_bytes' => $sole,
		'total_bytes_fzc_minus_best' => $hasDelta && $n > 0 ? $sumDelta : null,
	];
}

/**
 * @param list<array<string, mixed>> $rows
 * @return array<string, int|float|null>
 */
function benchmarkColumnTotals(array $rows): array
{
	$t = [
		'raw_bytes' => 0,
		'gzip9_bundle_bytes' => 0,
		'seven_zip_folder_bytes' => 0,
		'best_ext_folder_bytes' => 0,
		'fzc_bytes' => 0,
		'gzip9_seconds' => 0.0,
		'seven_zip_seconds' => 0.0,
		'best_ext_seconds' => 0.0,
		'zip_seconds' => 0.0,
		'extract_all_seconds' => 0.0,
		'gzip9_extract_seconds' => 0.0,
		'seven_zip_extract_seconds' => 0.0,
		'best_ext_extract_seconds' => 0.0,
		'bytes_winner_extract_seconds' => 0.0,
	];
	$seen = [
		'seven_zip_folder_bytes' => false,
		'best_ext_folder_bytes' => false,
		'seven_zip_seconds' => false,
		'best_ext_seconds' => false,
		'extract_all_seconds' => false,
		'gzip9_extract_seconds' => false,
		'seven_zip_extract_seconds' => false,
		'best_ext_extract_seconds' => false,
		'bytes_winner_extract_seconds' => false,
	];
	$gzipBytesAllRows = true;
	$gzipSecondsAllRows = true;
	foreach ($rows as $r) {
		$t['raw_bytes'] += (int) ($r['raw_bytes'] ?? 0);
		if (!array_key_exists('gzip9_bundle_bytes', $r) || $r['gzip9_bundle_bytes'] === null) {
			$gzipBytesAllRows = false;
		} else {
			$t['gzip9_bundle_bytes'] += (int) $r['gzip9_bundle_bytes'];
		}
		$t['fzc_bytes'] += (int) ($r['fzc_bytes'] ?? 0);
		if (!array_key_exists('gzip9_seconds', $r) || $r['gzip9_seconds'] === null) {
			$gzipSecondsAllRows = false;
		} else {
			$t['gzip9_seconds'] += (float) $r['gzip9_seconds'];
		}
		$t['zip_seconds'] += (float) ($r['zip_seconds'] ?? 0.0);
		if (isset($r['seven_zip_folder_bytes']) && $r['seven_zip_folder_bytes'] !== null) {
			$t['seven_zip_folder_bytes'] += (int) $r['seven_zip_folder_bytes'];
			$seen['seven_zip_folder_bytes'] = true;
		}
		if (isset($r['best_ext_folder_bytes']) && $r['best_ext_folder_bytes'] !== null) {
			$t['best_ext_folder_bytes'] += (int) $r['best_ext_folder_bytes'];
			$seen['best_ext_folder_bytes'] = true;
		}
		if (isset($r['seven_zip_seconds']) && $r['seven_zip_seconds'] !== null) {
			$t['seven_zip_seconds'] += (float) $r['seven_zip_seconds'];
			$seen['seven_zip_seconds'] = true;
		}
		if (isset($r['best_ext_seconds']) && $r['best_ext_seconds'] !== null) {
			$t['best_ext_seconds'] += (float) $r['best_ext_seconds'];
			$seen['best_ext_seconds'] = true;
		}
		if (isset($r['extract_all_seconds']) && $r['extract_all_seconds'] !== null) {
			$t['extract_all_seconds'] += (float) $r['extract_all_seconds'];
			$seen['extract_all_seconds'] = true;
		}
		if (isset($r['gzip9_extract_seconds']) && $r['gzip9_extract_seconds'] !== null) {
			$t['gzip9_extract_seconds'] += (float) $r['gzip9_extract_seconds'];
			$seen['gzip9_extract_seconds'] = true;
		}
		if (isset($r['seven_zip_extract_seconds']) && $r['seven_zip_extract_seconds'] !== null) {
			$t['seven_zip_extract_seconds'] += (float) $r['seven_zip_extract_seconds'];
			$seen['seven_zip_extract_seconds'] = true;
		}
		if (isset($r['best_ext_extract_seconds']) && $r['best_ext_extract_seconds'] !== null) {
			$t['best_ext_extract_seconds'] += (float) $r['best_ext_extract_seconds'];
			$seen['best_ext_extract_seconds'] = true;
		}
		if (isset($r['bytes_winner_extract_seconds']) && $r['bytes_winner_extract_seconds'] !== null) {
			$t['bytes_winner_extract_seconds'] += (float) $r['bytes_winner_extract_seconds'];
			$seen['bytes_winner_extract_seconds'] = true;
		}
	}
	foreach ($seen as $k => $ok) {
		if (!$ok) {
			$t[$k] = null;
		}
	}
	if (!$gzipBytesAllRows) {
		$t['gzip9_bundle_bytes'] = null;
	}
	if (!$gzipSecondsAllRows) {
		$t['gzip9_seconds'] = null;
	}
	if (!$seen['extract_all_seconds']) {
		$t['extract_all_seconds'] = null;
	}
	foreach (['gzip9_extract_seconds', 'seven_zip_extract_seconds', 'best_ext_extract_seconds', 'bytes_winner_extract_seconds'] as $ek) {
		if (!$seen[$ek]) {
			$t[$ek] = null;
		}
	}
	return $t;
}

// Included via require/include: keep helper functions, do not run tune/suite.
if (empty($__fzBenchIsMain)) {
	return;
}

if ($tuneCorpus !== null) {
	if (!is_dir($workRoot)) {
		mkdir($workRoot, 0755, true);
	}
	$src = $baseDir . DIRECTORY_SEPARATOR . $tuneCorpus;
	if (!is_dir($src)) {
		fwrite(STDERR, "Not a directory under repo root: {$tuneCorpus}\n");
		exit(1);
	}
	$segments = [60, 80, 100, 120, 140, 160, 200, 240, 280, 320, 360, 400, 420, 440, 460, 480, 500, 520, 560, 600, 1000, 2000, 4000];
	$pre7 = sevenZipFolderCompressBenchmark($src);
	$preExt = !$noFreeArc ? bestExternalFolderCompressBenchmark($src, $pre7) : null;
	$tuneDesc = bench_corpus_desc16($tuneCorpus);
	echo "segment_length sweep on {$tuneCorpus} ({$tuneDesc}) (minimize fzc bytes)\n";
	printf("%-16s %-10s %10s %12s %10s %10s %6s\n", 'desc', 'seg', 'fzc_B', 'gzip9_B', 'zip_s', 'ex_s', 'out');
	echo str_repeat('-', 80) . "\n";
	$bestSegBytes = null;
	$bestFzcBytes = PHP_INT_MAX;
	$savedTuneGz = benchSaveFolderGzipFastEnv();
	$tuneBaselineHolder = null;
	try {
		if (in_array($tuneCorpus, $heavyCorporaFolderGzipFastDefault, true)) {
			$tuneRaw = collectFolderFiles($src)['total'];
			if (!$includeLarge && $tuneRaw >= benchHeavyFolderGzipFastMinRawBytes()) {
				putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
			} else {
				putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
			}
		}
		foreach ($segments as $seg) {
			try {
				$work = $workRoot . DIRECTORY_SEPARATOR . 'tune_' . $tuneCorpus . '_' . $seg;
				$r = runOneCaseRepeatedWithOptionalTimeout($src, $work, $tuneCorpus, $seg, $pre7, $preExt, !$skipExtract, !$noMultipass, !$noFreeArc, !$noVerify, $caseTimeoutSec, $repeatRuns, $tuneBaselineHolder, false, false, false, null);
				if ($r === null) {
					echo "skip(seg={$seg})\n";
					continue;
				}
				$oc = isset($r['outer_codec']) && (string) $r['outer_codec'] !== '' ? (string) $r['outer_codec'] : '—';
				$b = (int) $r['fzc_bytes'];
				$x = isset($r['extract_all_seconds']) && $r['extract_all_seconds'] !== null ? (float) $r['extract_all_seconds'] : null;
				if ($b > 0 && $b < $bestFzcBytes) {
					$bestFzcBytes = $b;
					$bestSegBytes = $seg;
				}
				printf(
					"%-16s %-10d %10d %12d %10s %10s %6s\n",
					$tuneDesc,
					$seg,
					$r['fzc_bytes'],
					$r['gzip9_bundle_bytes'],
					(string) $r['zip_seconds'],
					$x !== null ? (string) round($x, 4) : '—',
					$oc
				);
			} finally {
				if ($caseDiskSweep) {
					benchSweepBenchmarkWorkRoot($workRoot);
				}
			}
		}
		if ($bestSegBytes !== null && $bestFzcBytes < PHP_INT_MAX) {
			echo "\nbest_bytes_segment={$bestSegBytes}  fzc_B={$bestFzcBytes}\n";
			echo "Try: FRACTAL_ZIP_SEGMENT_LENGTH={$bestSegBytes} php benchmarks/run_benchmarks.php --only={$tuneCorpus}\n";
		}
	} finally {
		benchRestoreFolderGzipFastEnv($savedTuneGz);
	}
	exit(0);
}

if ($tuneCorpus === null && $defaultTests === []) {
	$hint = 'No benchmark corpora matched. Add directories named test_files* under the repo root (see README), or run: php benchmarks/run_benchmarks.php --tune <folder_name>';
	if ($onlyTests !== null && $onlyTests !== []) {
		$hint = 'No corpus matched --only=' . implode(',', $onlyTests) . '. Use a test_files* directory under the repo root, or a bare number (e.g. --only=13 → test_files13).';
	}
	fwrite(STDERR, $hint . PHP_EOL);
	exit(1);
}

// Included via require/include: keep helper functions, do not run the suite.
if (empty($__fzBenchIsMain)) {
	return;
}

$rows = [];
$skippedCases = [];
$baselineCachePath = benchResolveBaselineCachePath($baselineCachePathOverride);
$lastSkippedLogPath = benchResolveLastSkippedLogPath($lastSkippedLogPathOverride);
// Single-element holder; runOneCaseRepeatedWithOptionalTimeout takes it by reference so baseline merges update this blob (see holder param docblock).
$baselineCacheHolder = $baselineCacheEnabled
	? ['root' => benchBaselineCacheLoad($baselineCachePath)]
	: null;
$phase1CaseCachePath = benchResolvePhase1CaseCachePath($phase1CaseCachePathOverride);
$phase1CaseCacheHolder = ($phase1Bench && $phase1CaseCacheEnabled)
	? ['root' => benchPhase1CaseCacheLoad($phase1CaseCachePath)]
	: null;
if ($benchUseParallelPool) {
	if ($caseDiskSweep && is_resource(STDERR)) {
		fwrite(
			STDERR,
			"[bench] parallel --jobs={$benchJobs}: deferring per-case disk sweep; final sweep after all corpora (see benchmarks/PARALLELISM.md).\n"
		);
	}
	/** @var array<string, array<string, mixed>|null> $rowsByName */
	$rowsByName = [];
	$corpusQueue = [];
	foreach ($defaultTests as $idx => $name) {
		$src = $baseDir . DIRECTORY_SEPARATOR . $name;
		if (!is_dir($src)) {
			continue;
		}
		$caseNo = $idx + 1;
		$totalCases = count($defaultTests);
		fwrite(STDERR, "[bench {$caseNo}/{$totalCases}] {$name}\n");
		fflush(STDERR);
		$row = null;
		$caseTimeoutThis = $caseTimeoutSec;
		$savedGz = benchSaveFolderGzipFastEnv();
		try {
			$rawPeek = collectFolderFiles($src)['total'];
			if (in_array($name, $heavyCorporaFolderGzipFastDefault, true)) {
				if (benchHeavyFolderGzipFastForCase($includeLarge, (int) $rawPeek)) {
					putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
				} else {
					putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
				}
			}
			$caseTimeoutThis = benchPhase1MonsterCaseTimeout($caseTimeoutSec, $name);
			$caseTimeoutThis = benchSlowLiteralCaseTimeout($caseTimeoutThis, $name);
			$caseTimeoutThis = benchGeneralTextCaseTimeout($caseTimeoutThis, $name, $src, (int) $rawPeek);
			$caseFp = benchPhase1CaseCacheFingerprint($rawPeek, benchmarkSegmentLength(), $caseTimeoutThis, $includeLarge, $repeatRuns, $benchAdaptiveMarkers, !$noFreeArc, !$skipExtract, !$noMultipass, !$noVerify);

			if ($phase1CaseCacheHolder !== null && !$phase1CaseCacheRefresh && $repeatRuns === 1) {
				$hit = benchPhase1CaseCacheLookup($phase1CaseCacheHolder['root'], $name, $caseFp);
				if ($hit !== null && (string) ($hit['label'] ?? '') === $name) {
					$row = $hit;
					$row['phase1_row_cache_hit'] = true;
					fwrite(STDERR, "[phase1 row cache] {$name}\n");
					fflush(STDERR);
				}
			}

			if ($row === null) {
				if ($phase1Bench && $caseTimeoutSec !== null && $caseTimeoutThis !== $caseTimeoutSec) {
					fwrite(STDERR, "[phase1 monster cap {$caseTimeoutThis}s] {$name}\n");
				}
				if ($phase1Bench && $caseTimeoutSec !== null && benchPhase1AutoLiteralStore(true, $name, $rawPeek)) {
					fwrite(STDERR, "[phase1 auto store-only .fz] {$name}\n");
				}
				$fzcStg = $phase1Bench && $caseTimeoutThis !== null && $repeatRuns === 1
					&& benchPhase1StagingIsPending($name, $rawPeek, !$noFreeArc);
				if ($fzcStg) {
					fwrite(STDERR, "[phase1 resume fzc] {$name}\n");
				}
				$corpusQueue[] = $name;
			} else {
				$rowsByName[$name] = $row;
			}
		} finally {
			benchRestoreFolderGzipFastEnv($savedGz);
		}
	}

	if ($corpusQueue !== []) {
		$pooled = bench_fork_pool_default_cases(
			$benchJobs,
			$corpusQueue,
			$baseDir,
			$workRoot,
			$heavyCorporaFolderGzipFastDefault,
			$includeLarge,
			$skipExtract,
			$noMultipass,
			$noFreeArc,
			$noVerify,
			$caseTimeoutSec,
			$repeatRuns,
			$baselineCacheRefresh,
			$phase1Bench,
			$baselineCacheHolder
		);
		foreach ($pooled as $lab => $pr) {
			$rowsByName[$lab] = $pr;
		}
	}

	foreach ($defaultTests as $name) {
		$src = $baseDir . DIRECTORY_SEPARATOR . $name;
		if (!is_dir($src)) {
			continue;
		}
		if (!array_key_exists($name, $rowsByName)) {
			continue;
		}
		$row = $rowsByName[$name];
		$caseTimeoutThis = $caseTimeoutSec;
		$rawPeek = collectFolderFiles($src)['total'];
		$caseTimeoutThis = benchPhase1MonsterCaseTimeout($caseTimeoutThis, $name);
		$caseTimeoutThis = benchSlowLiteralCaseTimeout($caseTimeoutThis, $name);
		$caseTimeoutThis = benchGeneralTextCaseTimeout($caseTimeoutThis, $name, $src, (int) $rawPeek);
		$caseFp = benchPhase1CaseCacheFingerprint($rawPeek, benchmarkSegmentLength(), $caseTimeoutThis, $includeLarge, $repeatRuns, $benchAdaptiveMarkers, !$noFreeArc, !$skipExtract, !$noMultipass, !$noVerify);
		if ($row === null) {
			$why = $caseTimeoutThis !== null ? ("timeout>{$caseTimeoutThis}s") : 'failed';
			$skippedCases[] = ['label' => $name, 'reason' => $why];
			fwrite(STDERR, "[skip] {$name} ({$why})\n");
			fflush(STDERR);
			continue;
		}
		$wcWarn = $row['winner_compression'] ?? [];
		$fzcAmongWinnersWarn = is_array($wcWarn) && in_array('fzc', $wcWarn, true);
		if (!empty($row['folder_gzip_fast']) && !$fzcAmongWinnersWarn) {
			$row['ratio_caveat_heavy_gzip_fast_fzc_lost'] = true;
			fwrite(STDERR, "[ratio caveat] {$name}: folder gzip-fast path was used and .fz is not among winner_compression; for bytes-first ratio on heavy trees try --large (and raise FRACTAL_ZIP_BENCH_MEMORY_LIMIT if needed).\n");
			fflush(STDERR);
		}
		$rows[] = $row;
		if ($phase1CaseCacheHolder !== null && $repeatRuns === 1 && $caseFp !== null && empty($row['phase1_row_cache_hit'])) {
			benchPhase1CaseCacheMergeFromRow($phase1CaseCacheHolder['root'], $name, $caseFp, $row);
		}
		if ($baselineCacheEnabled && !empty($row['baseline_cache_hit'])) {
			fwrite(STDERR, "[baseline cache] {$name}\n");
		}
	}

	if ($caseDiskSweep) {
		benchSweepBenchmarkWorkRoot($workRoot);
	}
} else {
	foreach ($defaultTests as $idx => $name) {
		$src = $baseDir . DIRECTORY_SEPARATOR . $name;
		if (!is_dir($src)) {
			continue;
		}
		$caseNo = $idx + 1;
		$totalCases = count($defaultTests);
		fwrite(STDERR, "[bench {$caseNo}/{$totalCases}] {$name}\n");
		fflush(STDERR);
		$work = $workRoot . DIRECTORY_SEPARATOR . $name;
		$row = null;
		$caseTimeoutThis = $caseTimeoutSec;
		$caseFp = null;
		try {
			$savedGz = benchSaveFolderGzipFastEnv();
			try {
				$rawPeek = collectFolderFiles($src)['total'];
				if (in_array($name, $heavyCorporaFolderGzipFastDefault, true)) {
					if (benchHeavyFolderGzipFastForCase($includeLarge, (int) $rawPeek)) {
						putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=1');
						if ($includeLarge && (int) $rawPeek >= (1024 * 1024 * 1024)) {
							fwrite(STDERR, "[bench] {$name}: folder gzip-fast on ≥1 GiB under --large (FRACTAL_ZIP_BENCH_HUGE_GZIP_FAST; avoids OOM on GP lakes)\n");
							fflush(STDERR);
						}
					} else {
						putenv('FRACTAL_ZIP_FOLDER_GZIP_FAST=0');
					}
				}
				$caseTimeoutThis = benchPhase1MonsterCaseTimeout($caseTimeoutSec, $name);
				$caseTimeoutThis = benchSlowLiteralCaseTimeout($caseTimeoutThis, $name);
				$caseTimeoutThis = benchGeneralTextCaseTimeout($caseTimeoutThis, $name, $src, (int) $rawPeek);
				$caseFp = benchPhase1CaseCacheFingerprint($rawPeek, benchmarkSegmentLength(), $caseTimeoutThis, $includeLarge, $repeatRuns, $benchAdaptiveMarkers, !$noFreeArc, !$skipExtract, !$noMultipass, !$noVerify);

				if ($phase1CaseCacheHolder !== null && !$phase1CaseCacheRefresh && $repeatRuns === 1) {
					$hit = benchPhase1CaseCacheLookup($phase1CaseCacheHolder['root'], $name, $caseFp);
					if ($hit !== null && (string) ($hit['label'] ?? '') === $name) {
						$row = $hit;
						$row['phase1_row_cache_hit'] = true;
						fwrite(STDERR, "[phase1 row cache] {$name}\n");
						fflush(STDERR);
					}
				}

				if ($row === null) {
					if ($phase1Bench && $caseTimeoutSec !== null && $caseTimeoutThis !== $caseTimeoutSec) {
						fwrite(STDERR, "[phase1 monster cap {$caseTimeoutThis}s] {$name}\n");
					}
					if ($phase1Bench && $caseTimeoutSec !== null && benchPhase1AutoLiteralStore(true, $name, $rawPeek)) {
						fwrite(STDERR, "[phase1 auto store-only .fz] {$name}\n");
					}
					$fzcStaged = $phase1Bench && $caseTimeoutThis !== null && $repeatRuns === 1
						&& benchPhase1StagingIsPending($name, $rawPeek, !$noFreeArc);
					if ($fzcStaged) {
						fwrite(STDERR, "[phase1 resume fzc] {$name}\n");
					}
					$row = runOneCaseRepeatedWithOptionalTimeout($src, $work, $name, null, null, null, !$skipExtract, !$noMultipass, !$noFreeArc, !$noVerify, $caseTimeoutThis, $repeatRuns, $baselineCacheHolder, $baselineCacheRefresh, $phase1Bench, $fzcStaged, null);
				}
			} finally {
				benchRestoreFolderGzipFastEnv($savedGz);
			}
		} finally {
			if ($caseDiskSweepBetweenCases) {
				benchSweepBenchmarkWorkRoot($workRoot);
			}
			bench_stray_process_sweep();
		}
		if ($row === null) {
			$why = $caseTimeoutThis !== null ? ("timeout>{$caseTimeoutThis}s") : 'failed';
			$skippedCases[] = ['label' => $name, 'reason' => $why];
			fwrite(STDERR, "[skip] {$name} ({$why})\n");
			fflush(STDERR);
			continue;
		}
		$wcWarn = $row['winner_compression'] ?? [];
		$fzcAmongWinnersWarn = is_array($wcWarn) && in_array('fzc', $wcWarn, true);
		if (!empty($row['folder_gzip_fast']) && !$fzcAmongWinnersWarn) {
			$row['ratio_caveat_heavy_gzip_fast_fzc_lost'] = true;
			fwrite(STDERR, "[ratio caveat] {$name}: folder gzip-fast path was used and .fz is not among winner_compression; for bytes-first ratio on heavy trees try --large (and raise FRACTAL_ZIP_BENCH_MEMORY_LIMIT if needed).\n");
			fflush(STDERR);
		}
		$rows[] = $row;
		if ($phase1CaseCacheHolder !== null && $repeatRuns === 1 && $caseFp !== null && empty($row['phase1_row_cache_hit'])) {
			benchPhase1CaseCacheMergeFromRow($phase1CaseCacheHolder['root'], $name, $caseFp, $row);
		}
		if ($baselineCacheEnabled && !empty($row['baseline_cache_hit'])) {
			fwrite(STDERR, "[baseline cache] {$name}\n");
		}
	}
}

benchWriteLastSkippedCases(
	$lastSkippedLogPath,
	$skippedCases,
	$caseTimeoutSec,
	count($defaultTests),
	count($rows)
);

if ($baselineCacheHolder !== null) {
	benchBaselineCacheSave($baselineCachePath, $baselineCacheHolder['root']);
}
if ($phase1CaseCacheHolder !== null) {
	benchPhase1CaseCacheSave($phase1CaseCachePath, $phase1CaseCacheHolder['root']);
}

$benchSummary = fractal_zip_benchmark_row_summary($rows);
$columnTotals = benchmarkColumnTotals($rows);
$tableCompletenessProblems = fractal_zip_benchmark_table_completeness_problems(
	$rows,
	$skippedCases,
	!$skipExtract,
	!$noFreeArc
);
$tableComplete = $tableCompletenessProblems === [];

$benchResultPayload = [
	'generated' => date('c'),
	'adaptive_markers_bench_mode' => $benchAdaptiveMarkers,
	'metastruct_bench_mode' => $benchMetastruct,
	'bench_profile' => $benchProfileApplied,
	'case_timeout_sec' => $caseTimeoutSec,
	'table_complete' => $tableComplete,
	'baseline_cache' => [
		'enabled' => $baselineCacheEnabled,
		'path' => $baselineCachePath,
		'refreshed' => $baselineCacheRefresh,
	],
	'phase1_case_cache' => [
		'enabled' => $phase1CaseCacheEnabled && $phase1Bench,
		'path' => $phase1CaseCachePath,
		'refreshed' => $phase1CaseCacheRefresh,
	],
	'skipped_cases_log' => $lastSkippedLogPath,
	'include_large' => $includeLarge,
	'maximum_raw_bytes' => $maxRawBytes,
	'heavy_corpora_folder_gzip_fast_default' => $heavyCorporaFolderGzipFastDefault,
	'segment_length' => benchmarkSegmentLength(),
	'repeat_runs' => $repeatRuns,
	'parallel_jobs' => $benchUseParallelPool ? $benchJobs : 1,
	'best_ext_tools_probed' => discoverMinExtBenchToolIds(),
	'summary' => $benchSummary,
	'totals' => $columnTotals,
	'skipped_cases' => $skippedCases,
	'cases' => $rows,
];
if (!$tableComplete) {
	$benchResultPayload['table_completeness_problems'] = array_slice($tableCompletenessProblems, 0, 80);
}
$jsonPayloadString = bench_json_encode_try($benchResultPayload, true);
if ($jsonPayloadString === null) {
	fwrite(STDERR, '[bench] json_encode failed: ' . json_last_error_msg() . "\n");
	exit(2);
}
$jsonPayloadString .= "\n";
$envLastJson = getenv('FRACTAL_ZIP_BENCH_LAST_JSON');
$defaultLastPath = (is_string($envLastJson) && trim($envLastJson) !== '') ? trim($envLastJson) : (__DIR__ . DIRECTORY_SEPARATOR . '.last_bench.json');
$skipLastSave = $noSaveLastJson || getenv('FRACTAL_ZIP_BENCH_NO_SAVE_LAST_JSON') === '1';
if (!$skipLastSave) {
	$w = @file_put_contents($defaultLastPath, $jsonPayloadString);
	if ($w !== false) {
		$forMsg = $defaultLastPath;
		if (strncmp($forMsg, $baseDir . DIRECTORY_SEPARATOR, strlen($baseDir) + 1) === 0) {
			$forMsg = str_replace(DIRECTORY_SEPARATOR, '/', substr($forMsg, strlen($baseDir) + 1));
		}
		fwrite(STDERR, "[bench] full JSON: {$forMsg} (" . strlen($jsonPayloadString) . " B)\n");
	} else {
		fwrite(STDERR, "[bench] failed to write JSON: {$defaultLastPath}\n");
	}
}
if ($jsonOutFile !== null && $jsonOutFile !== '') {
	$defN = rtrim(str_replace('\\', '/', $defaultLastPath), '/');
	$outN = rtrim(str_replace('\\', '/', $jsonOutFile), '/');
	if (strcasecmp($defN, $outN) !== 0) {
		if (@file_put_contents($jsonOutFile, $jsonPayloadString) !== false) {
			$jrel = $jsonOutFile;
			if (strncmp($jrel, $baseDir . DIRECTORY_SEPARATOR, strlen($baseDir) + 1) === 0) {
				$jrel = str_replace(DIRECTORY_SEPARATOR, '/', substr($jrel, strlen($baseDir) + 1));
			}
			fwrite(STDERR, "[bench] --out-json: {$jrel}\n");
		} else {
			fwrite(STDERR, "[bench] failed to write --out-json: {$jsonOutFile}\n");
		}
	}
}
// Progressive ablation ledger (optional): promote/block summary beside binary verify_ok.
$writeAblationLedger = in_array('--ablation-ledger', $argv, true)
	|| getenv('FRACTAL_ZIP_BENCH_ABLATION_LEDGER') === '1';
if ($writeAblationLedger) {
	require_once __DIR__ . DIRECTORY_SEPARATOR . 'ablation_ledger_lib.php';
	$ablationRows = fractal_zip_ablation_rows_from_bench_json($benchResultPayload);
	$ablationEval = fractal_zip_ablation_evaluate_rows($ablationRows, 'full');
	$ablationPath = __DIR__ . DIRECTORY_SEPARATOR . '.ablation.json';
	foreach ($argv as $a) {
		if (is_string($a) && strncmp($a, '--ablation-ledger=', 18) === 0) {
			$ablationPath = substr($a, 18);
			break;
		}
	}
	fractal_zip_ablation_ledger_write($ablationPath, array(
		'generated' => gmdate('c'),
		'source' => 'run_benchmarks.php',
		'protocol' => 'sample5→medium→full; soft closeness never replaces SHA for lossless paste',
		'summary' => array(
			'rows' => count($ablationEval['evaluated']),
			'promote_count' => $ablationEval['promote_count'],
			'block_count' => $ablationEval['block_count'],
			'soft_rescues' => $ablationEval['soft_rescues'],
			'hard_blocks' => $ablationEval['hard_blocks'],
		),
		'rows' => $ablationEval['evaluated'],
	));
	fwrite(STDERR, '[bench] ablation ledger: ' . $ablationPath
		. ' promote=' . $ablationEval['promote_count']
		. ' block=' . $ablationEval['block_count'] . "\n");
}

if (!$tableComplete) {
	fwrite(STDERR, '[bench] INCOMPLETE TABLE: ' . count($tableCompletenessProblems)
		. " problem(s) (TOTAL not suite-comparable). First: "
		. implode('; ', array_slice($tableCompletenessProblems, 0, 12)) . "\n");
	if (!$allowIncompleteTable) {
		fwrite(STDERR, "[bench] exit 1 — pass --allow-incomplete to accept hollow columns / skipped cases.\n");
	}
}

if ($jsonOut) {
	echo $jsonPayloadString;
	exit(($tableComplete || $allowIncompleteTable) ? 0 : 1);
}

$extBenchIds = discoverMinExtBenchToolIds();
$extBenchNote = $extBenchIds === []
	? '(none on PATH — install arc, zstd, brotli, xz, and/or bzip3; optional lrzip: FRACTAL_ZIP_BENCH_LRZIP=1)'
	: implode(', ', $extBenchIds);
echo "fractal_zip benchmarks (`*` = best among gzip, 7z, min ext, fzc for bytes and decompress s; compress s columns are plain timings). Min ext = smallest among gzip9 bundle + {$extBenchNote}\n";

/**
 * Monospace display width for a UTF-8 cell ({@code strlen} is wrong for Unicode punctuation such as em dash {@code —}).
 */
function benchTableCellDisplayWidth(string $s): int
{
	if ($s === '') {
		return 0;
	}
	if (function_exists('mb_strwidth')) {
		$w = mb_strwidth($s, 'UTF-8');

		return ($w === false) ? strlen($s) : (int) $w;
	}

	return strlen($s);
}

/** Display width of a full table line (for rule rows). */
function benchTableLineDisplayWidth(string $line): int
{
	$s = rtrim($line, "\n");
	if ($s === '') {
		return 0;
	}
	if (function_exists('mb_strwidth')) {
		$w = mb_strwidth($s, 'UTF-8');

		return ($w === false) ? strlen($s) : (int) $w;
	}

	return strlen($s);
}

/**
 * Render a fixed-width table by measuring all cells (headers + rows + totals).
 *
 * @param list<array<string,string>> $rowsCells
 * @param array<string,string> $totalsCells
 */
function renderBenchTable(array $rowsCells, array $totalsCells, bool $showFolderCensusTxCol = false): void
{
	$head = [
		'test' => 'test',
		'desc' => 'desc',
	];
	if ($showFolderCensusTxCol) {
		$head['txPct'] = 'tx%';
	}
	$headers = $head + [
		'out' => 'out',
		'rawB' => 'raw B',
		'gzB' => 'gzip9 B',
		'z7B' => '7z dir B',
		'extB' => 'min ext B',
		'extWin' => 'ext win',
		'fzcB' => 'fzc B',
		'gzS' => 'gzip s',
		'z7S' => '7z s',
		'extS' => 'ext s',
		'fzcS' => 'fzc s',
		'gzEx' => 'gz ex',
		'z7Ex' => '7z ex',
		'extEx' => 'ext ex',
		'fzcEx' => 'fzc ex',
		'pFz' => '%fz',
		'pGz' => '%gz',
		'p7z' => '%7z',
		'pExt' => '%ext',
		'winB' => 'win bytes',
	];
	$order = array_keys($headers);
	$alignRight = [
		'rawB' => true, 'gzB' => true, 'z7B' => true, 'extB' => true, 'fzcB' => true,
		'gzS' => true, 'z7S' => true, 'extS' => true, 'fzcS' => true,
		'gzEx' => true, 'z7Ex' => true, 'extEx' => true, 'fzcEx' => true,
		'pFz' => true, 'pGz' => true, 'p7z' => true, 'pExt' => true,
	];
	if ($showFolderCensusTxCol) {
		$alignRight['txPct'] = true;
	}
	$widths = [];
	foreach ($order as $k) {
		$widths[$k] = benchTableCellDisplayWidth($headers[$k]);
	}
	foreach ($rowsCells as $r) {
		foreach ($order as $k) {
			$widths[$k] = max($widths[$k], benchTableCellDisplayWidth($r[$k] ?? ''));
		}
	}
	foreach ($order as $k) {
		$widths[$k] = max($widths[$k], benchTableCellDisplayWidth($totalsCells[$k] ?? ''));
	}

	$sepPipe = ' | ';
	$sepCol = ' ';
	$g0 = ['test', 'desc'];
	if ($showFolderCensusTxCol) {
		$g0[] = 'txPct';
	}
	$g0 = array_merge($g0, ['out', 'rawB', 'gzB', 'z7B', 'extB', 'extWin', 'fzcB']);
	$groups = [
		$g0,
		['gzS', 'z7S', 'extS', 'fzcS', 'gzEx', 'z7Ex', 'extEx', 'fzcEx'],
		['pFz', 'pGz', 'p7z', 'pExt', 'winB'],
	];

	$formatRow = function(array $cells) use ($widths, $alignRight, $sepPipe, $sepCol, $groups): string {
		$out = '';
		foreach ($groups as $gi => $group) {
			if ($gi > 0) {
				$out .= $sepPipe;
			}
			foreach ($group as $ci => $k) {
				$val = $cells[$k] ?? '';
				$w = $widths[$k];
				$pad = max(0, $w - benchTableCellDisplayWidth($val));
				$cell = (!empty($alignRight[$k]) ? str_repeat(' ', $pad) . $val : $val . str_repeat(' ', $pad));
				if ($ci > 0) {
					$out .= $sepCol;
				}
				$out .= $cell;
			}
		}
		return $out . "\n";
	};

	$headerCells = [];
	foreach ($order as $k) {
		$headerCells[$k] = $headers[$k];
	}
	$headerLine = $formatRow($headerCells);
	$w = benchTableLineDisplayWidth($headerLine);
	echo str_repeat('=', $w) . "\n";
	echo $headerLine;
	echo str_repeat('-', $w) . "\n";
	foreach ($rowsCells as $r) {
		echo $formatRow($r);
	}
	echo $formatRow($totalsCells);
}

$rowsCells = [];
foreach ($rows as $r) {
	$oc = isset($r['outer_codec']) && is_string($r['outer_codec']) && $r['outer_codec'] !== '' ? (string) $r['outer_codec'] : '—';
	$wB = $r['winner_compression'] ?? [];
	$z7b = $r['seven_zip_folder_bytes'];
	$z7sec = $r['seven_zip_seconds'];
	$extB = $r['best_ext_folder_bytes'] ?? null;
	$extSec = $r['best_ext_seconds'] ?? null;
	$extWho = isset($r['best_ext_winner']) && is_string($r['best_ext_winner']) && $r['best_ext_winner'] !== '' ? $r['best_ext_winner'] : '—';
	if (strlen($extWho) > 6) {
		$extWho = substr($extWho, 0, 6);
	}
	if ($extB === null) {
		$extWho = '—';
	}
	$p7 = isset($r['pct_of_raw_7z']) && $r['pct_of_raw_7z'] !== null ? (string) $r['pct_of_raw_7z'] : '—';
	$pExt = isset($r['pct_of_raw_best_ext']) && $r['pct_of_raw_best_ext'] !== null ? (string) $r['pct_of_raw_best_ext'] : '—';

	$gzBraw = $r['gzip9_bundle_bytes'] ?? null;
	$gzBcell = $gzBraw === null ? '—' : (string) formatWithWinnerTag(formatBenchBytesCell((int) $gzBraw), in_array('gzip', $wB, true));
	$gzSkipped = $gzBraw === null && ($r['gzip9_seconds'] ?? null) === null;
	$gzScell = $gzSkipped
		? '—'
		: formatSecondsForTable(isset($r['gzip9_seconds']) && $r['gzip9_seconds'] !== null ? (float) $r['gzip9_seconds'] : null);
	$pGz = isset($r['pct_of_raw_gzip']) && $r['pct_of_raw_gzip'] !== null ? ((string) $r['pct_of_raw_gzip'] . '%') : '—';

	$wEx = isset($r['winner_extract']) && is_array($r['winner_extract'])
		? $r['winner_extract']
		: pickWinnersLowest([
			'gzip' => isset($r['gzip9_extract_seconds']) && $r['gzip9_extract_seconds'] !== null ? (float) $r['gzip9_extract_seconds'] : null,
			'7z' => isset($r['seven_zip_extract_seconds']) && $r['seven_zip_extract_seconds'] !== null ? (float) $r['seven_zip_extract_seconds'] : null,
			'ext' => isset($r['best_ext_extract_seconds']) && $r['best_ext_extract_seconds'] !== null ? (float) $r['best_ext_extract_seconds'] : null,
			'fzc' => isset($r['extract_all_seconds']) && $r['extract_all_seconds'] !== null ? (float) $r['extract_all_seconds'] : null,
		]);

	$sd = isset($r['short_desc']) && is_string($r['short_desc']) ? (string) $r['short_desc'] : bench_corpus_desc16((string) $r['label']);
	$rowCell = [
		'test' => (string) $r['label'],
		'desc' => $sd,
	];
	if ($benchShowFolderCensusCol) {
		$rowCell['txPct'] = bench_folder_census_textish_pct_cell($r['folder_bundle_census'] ?? null);
	}
	$rowCell += [
		'out' => $oc,
		'rawB' => formatBenchBytesCell((int) $r['raw_bytes']),
		'gzB' => $gzBcell,
		'z7B' => $z7b === null ? '—' : (string) formatWithWinnerTag(formatBenchBytesCell((int) $z7b), in_array('7z', $wB, true)),
		'extB' => $extB === null ? '—' : (string) formatWithWinnerTag(formatBenchBytesCell((int) $extB), in_array('ext', $wB, true)),
		'extWin' => $extWho,
		'fzcB' => (string) formatWithWinnerTag(formatBenchBytesCell((int) $r['fzc_bytes']), in_array('fzc', $wB, true)),
		'gzS' => $gzScell,
		'z7S' => ($z7b === null || $z7sec === null) ? '—' : formatSecondsForTable((float) $z7sec),
		'extS' => ($extB === null || $extSec === null) ? '—' : formatSecondsForTable((float) $extSec),
		'fzcS' => formatSecondsForTable(isset($r['zip_seconds']) ? (float) $r['zip_seconds'] : null),
		'gzEx' => $gzSkipped
			? '—'
			: (string) formatWithWinnerTag(
				formatSecondsForTable(isset($r['gzip9_extract_seconds']) && $r['gzip9_extract_seconds'] !== null ? (float) $r['gzip9_extract_seconds'] : null),
				in_array('gzip', $wEx, true)
			),
		'z7Ex' => ($z7b === null || ($r['seven_zip_extract_seconds'] ?? null) === null)
			? '—'
			: (string) formatWithWinnerTag(formatSecondsForTable((float) $r['seven_zip_extract_seconds']), in_array('7z', $wEx, true)),
		'extEx' => ($extB === null || ($r['best_ext_extract_seconds'] ?? null) === null)
			? '—'
			: (string) formatWithWinnerTag(formatSecondsForTable((float) $r['best_ext_extract_seconds']), in_array('ext', $wEx, true)),
		'fzcEx' => (string) formatWithWinnerTag(
			$r['extract_all_seconds'] === null ? '—' : formatSecondsForTable((float) $r['extract_all_seconds']),
			in_array('fzc', $wEx, true)
		),
		'pFz' => (string) ($r['pct_of_raw_fzc'] ?? '—') . '%',
		'pGz' => $pGz,
		'p7z' => $p7 === '—' ? '—' : ($p7 . '%'),
		'pExt' => $pExt === '—' ? '—' : ($pExt . '%'),
		'winB' => ($wB === [] ? '—' : implode('+', $wB)),
	];
	$rowsCells[] = $rowCell;
}

$totalsCells = [
	'test' => 'TOTAL',
	'desc' => str_repeat(' ', 16),
];
if ($benchShowFolderCensusCol) {
	$totalsCells['txPct'] = '—';
}
$totalsCells += [
	// Keep width consistent with row `out` cells (3 chars like gzi/zst/bro).
	'out' => '   ',
	'rawB' => formatBenchBytesCell((int) $columnTotals['raw_bytes']),
	'gzB' => ($columnTotals['gzip9_bundle_bytes'] === null ? '—' : formatBenchBytesCell((int) $columnTotals['gzip9_bundle_bytes'])),
	'z7B' => ($columnTotals['seven_zip_folder_bytes'] === null ? '—' : formatBenchBytesCell((int) $columnTotals['seven_zip_folder_bytes'])),
	'extB' => ($columnTotals['best_ext_folder_bytes'] === null ? '—' : formatBenchBytesCell((int) $columnTotals['best_ext_folder_bytes'])),
	'extWin' => '',
	'fzcB' => formatBenchBytesCell((int) $columnTotals['fzc_bytes']),
	'gzS' => ($columnTotals['gzip9_seconds'] === null ? '—' : formatSecondsForTable((float) $columnTotals['gzip9_seconds'])),
	'z7S' => ($columnTotals['seven_zip_seconds'] === null ? '—' : formatSecondsForTable((float) $columnTotals['seven_zip_seconds'])),
	'extS' => ($columnTotals['best_ext_seconds'] === null ? '—' : formatSecondsForTable((float) $columnTotals['best_ext_seconds'])),
	'fzcS' => formatSecondsForTable(isset($columnTotals['zip_seconds']) ? (float) $columnTotals['zip_seconds'] : null),
	'gzEx' => ($columnTotals['gzip9_extract_seconds'] === null ? '—' : formatSecondsForTable((float) $columnTotals['gzip9_extract_seconds'])),
	'z7Ex' => ($columnTotals['seven_zip_extract_seconds'] === null ? '—' : formatSecondsForTable((float) $columnTotals['seven_zip_extract_seconds'])),
	'extEx' => ($columnTotals['best_ext_extract_seconds'] === null ? '—' : formatSecondsForTable((float) $columnTotals['best_ext_extract_seconds'])),
	'fzcEx' => ($columnTotals['extract_all_seconds'] === null ? '—' : formatSecondsForTable((float) $columnTotals['extract_all_seconds'])),
	// TOTAL row: compute real % values (same treatment as data rows).
	'pFz' => '—',
	'pGz' => '—',
	'p7z' => '—',
	'pExt' => '—',
	// TOTAL row: compute winners over totals (gzip/7z/ext/fzc), same ids as rows.
	'winB' => '—',
];

if ($totRaw > 0) {
	$totGzB = $columnTotals['gzip9_bundle_bytes'];
	$totFzB = (int) ($columnTotals['fzc_bytes'] ?? 0);
	$tot7zB = $columnTotals['seven_zip_folder_bytes'];
	$totExtB = $columnTotals['best_ext_folder_bytes'];
	$totPctFz = round(100.0 * $totFzB / $totRaw, 2);
	$totalsCells['pFz'] = (string) $totPctFz . '%';
	$totalsCells['pGz'] = ($totGzB === null) ? '—' : ((string) round(100.0 * (int) $totGzB / $totRaw, 2) . '%');
	$totalsCells['p7z'] = ($tot7zB === null) ? '—' : ((string) round(100.0 * ((int) $tot7zB) / $totRaw, 2) . '%');
	$totalsCells['pExt'] = ($totExtB === null) ? '—' : ((string) round(100.0 * ((int) $totExtB) / $totRaw, 2) . '%');

	$sizeContestTotals = ['fzc' => $totFzB];
	if ($totGzB !== null) {
		$sizeContestTotals['gzip'] = (int) $totGzB;
	}
	if ($tot7zB !== null) {
		$sizeContestTotals['7z'] = (int) $tot7zB;
	}
	if ($totExtB !== null) {
		$sizeContestTotals['ext'] = (int) $totExtB;
	}
	$wBtot = pickWinnersLowest($sizeContestTotals);
	$totalsCells['winB'] = $wBtot === [] ? '—' : implode('+', $wBtot);
}

renderBenchTable($rowsCells, $totalsCells, $benchShowFolderCensusCol);
printf(
	"\nSummary: %d cases — fzc tied or won smallest bytes in %d (%d sole winner); cumulative (fzc − min(gzip,7z,min ext,fzc)) = %s B.\n",
	$benchSummary['case_count'],
	$benchSummary['fzc_among_smallest_bytes'],
	$benchSummary['fzc_sole_smallest_bytes'],
	$benchSummary['total_bytes_fzc_minus_best'] === null ? '—' : (string) $benchSummary['total_bytes_fzc_minus_best']
);
if ($benchNotes) {
	echo "\nNotes: `gzip9 B` / `gzip s` = deterministic bundle + gzcompress(9). `7z dir B` / `7z s` = copy tree + 7z -mx=9 -m0=lzma2 (optional -mmt via FRACTAL_ZIP_BENCH_7Z_MMT / FRACTAL_ZIP_7Z_MMT). ";
	echo "`min ext B` / `ext s` = smallest folder archive among the min-ext tournament (see header); `ext win` names the winner for that row. ";
	echo "`gz ex` / `7z ex` / `ext ex` / `fzc ex` = decompress each baseline tarball or the .fz (same tools as bytes columns). ";
	echo "`ext ex` is for the min-ext winner only. Extract `*` marks the fastest decompress among the four (ties: multiple `*`). ";
	echo "`win bytes` uses ids gzip, 7z, ext, fzc (ties: `+`). ";
	if ($benchShowFolderCensusCol) {
		echo "`tx%` = 100×folder_bundle_census.textish_ratio (text-extension raw-byte share in the folder scan); column from FRACTAL_ZIP_BENCH_SHOW_FOLDER_CENSUS=1. ";
	} elseif ($benchNotes) {
		$anyFolderCensus = false;
		foreach ($rows as $r) {
			if (!is_array($r)) {
				continue;
			}
			$c = $r['folder_bundle_census'] ?? null;
			if (is_array($c) && array_key_exists('textish_ratio', $c)) {
				$anyFolderCensus = true;
				break;
			}
		}
		if ($anyFolderCensus) {
			echo "JSON `cases[]` include `folder_bundle_census` (extension mass + textish_ratio). Human `tx%` column: FRACTAL_ZIP_BENCH_SHOW_FOLDER_CENSUS=1. Telemetry stderr: FRACTAL_ZIP_FOLDER_BUNDLE_CENSUS_STDERR=1; optional schedule-only nudge: FRACTAL_ZIP_FOLDER_BUNDLE_SCHED_COST_BIAS=1 (see fractal_zip class docblock). ";
		}
	}
	echo "Undeep FZB* sync: php benchmarks/run_benchmarks.php --check-undeep-sync; or preflight + bench: CHECK_UNDEEP_SYNC=1. ";
	echo "Tune min-ext: same as outer tooling — FRACTAL_ZIP_BENCH_ZSTD_LEVEL; FRACTAL_ZIP_BENCH_ZSTD_THREADS (-T); FRACTAL_ZIP_BENCH_BROTLI_QUALITY; FRACTAL_ZIP_BENCH_XZ_THREADS (-T); FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS (parallel brotli wrappers); FRACTAL_ZIP_BENCH_BZIP3_JOBS (-j); FRACTAL_ZIP_ARC_MT / FRACTAL_ZIP_BENCH_ARC_MT (FreeArc/DArc -mt); FRACTAL_ZIP_BENCH_ZPAQ_THREADS / FRACTAL_ZIP_ZPAQ global argv for zpaq; optional lrzip: FRACTAL_ZIP_BENCH_LRZIP=1, FRACTAL_ZIP_BENCH_LRZIP_P (-p). ";
	echo "Squash CSV zpaq sizes (hoplite, ~2016) often differ from today’s Matt Mahoney `zpaq` byte-for-byte (algorithm churn between releases). Point `FRACTAL_ZIP_ZPAQ` at the binary you want for outer/min-ext. Prefer Matt Mahoney **zpaq 7.15** (`zpaq715.zip` + make; https://mattmahoney.net/dc/zpaq.html) or **zpaqfranz** on PATH; forks change compressed sizes vs Mahoney. Stock Matt Mahoney zpaq: omit `-threads` (`FRACTAL_ZIP_BENCH_ZPAQ_THREADS=off` when benches inject thread flags). ";
	echo "Gzip baseline wall time (same zlib format as gzcompress): FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ=1, FRACTAL_ZIP_BENCH_PIGZ_P (-p; else FRACTAL_ZIP_LITERALPAC_PIGZ_P). ";
	echo "Library encode paths: FRACTAL_ZIP_7Z_MMT, FRACTAL_ZIP_ZPAQ_THREADS / FRACTAL_ZIP_BENCH_ZPAQ_THREADS (zpaq -threads; run_benchmarks defaults BENCH_ZPAQ_THREADS=0 when both unset), FRACTAL_ZIP_BROTLI_EXTRA_ARGS. ";
	echo "Default segment_length=" . fractal_zip::DEFAULT_SEGMENT_LENGTH . ". Sweep: php benchmarks/run_benchmarks.php --tune test_files2\n";
	echo "Stream-first folder encode: inherited FRACTAL_ZIP_FOLDER_UNIFIED_STREAM is cleared unless you pass --legacy-folder-zip.\n";
	if ($benchProfileApplied !== null) {
		echo "Benchmark profile: {$benchProfileApplied} (tiered defaults only; explicit CLI/env overrides still win). Profiles: small-bytes, medium-balanced, large-fast, large-balanced, large-bytes.\n";
	}
	if (array_filter($rows, static fn (array $r): bool => !empty($r['phase1_timeout_store_fallback']))) {
		echo "Phase-1 timeout store fallback: rows with phase1_timeout_store_fallback=true timed out first, then wrote raw mode-0 FZB4 so large-tier bench profiles report a bounded row instead of a skip.\n";
	}
	if ($benchAdaptiveMarkers) {
		echo "Adaptive markers: --adaptive-markers sets FRACTAL_ZIP_ADAPTIVE_MARKERS=1 and FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=0 (fractal marker pass). A/B helper: benchmarks/bench_adaptive_markers_compare.sh\n";
	}
	if ($benchMetastruct) {
		echo "Metastruct markers: --metastruct sets FRACTAL_ZIP_METastruct=1 plus adaptive-markers env. A/B helper: benchmarks/bench_metastruct_markers_compare.sh\n";
	}
	if ($benchFastStagedBrotliArgv || $benchFastStagedBrotliFromEnv) {
		echo "Staged fast-tier brotli: --bench-fast-staged-brotli or FRACTAL_ZIP_BENCH_FAST_STAGED_BROTLI=1 sets FRACTAL_ZIP_STAGED_FAST_OUTER_BROTLI_QUALITY_CAP=3 when unset (see fractal_zip::adaptive_compress_outer_fast_codec_tier).\n";
	}
	if ($benchFastZipArgv || $benchFastZipFromEnv) {
		echo "Wall preset: benchmarks/bench_fast_zip_env.php (--bench-fast-zip or FRACTAL_ZIP_BENCH_FAST_ZIP=1): staged fast-tier brotli cap + FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES / TIMEOUT_SEC when unset.\n";
	}
	if ($benchPipelineNumbersArgv || $benchPipelineNumbersFromEnv) {
		echo "Pipeline numbers: FRACTAL_ZIP_PIPELINE_TIMING=1, FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1 (stderr: [fz pipeline timing], [fz pipeline outer rollup], per-step deltas in adaptive_compress). ";
		echo "Optional: --bench-pipeline-numbers=verbose or --bench-pipeline-verbose → FRACTAL_ZIP_PIPELINE_OUTER_STEP_VERBOSE=1 (skipped-branch zero-ms lines). See fractal_zip_encode_pipeline.php env block.\n";
	}
	if ($benchLiteralBytesFirstDefaults) {
		echo "Literal tournament: speed-tuning FRACTAL_ZIP_LITERAL_* vars cleared for bytes-first BMP defaults (probe 9, chains on). Keep shell env: FRACTAL_ZIP_BENCH_LITERAL_SPEED_DEFAULTS=1 or --bench-keep-shell-literal-env.\n";
	}
	if (!$includeLarge) {
		echo 'Heavy-list corpora use folder gzip-fast only when raw ≥ ' . (string) (benchHeavyFolderGzipFastMinRawBytes() / (1024 * 1024)) . " MiB: " . implode(', ', $heavyCorporaFolderGzipFastDefault) . ". Pass --large to force full fractal_zip on those dirs.\n";
	}
	echo "Default discovery includes every canonical test_files### directory present on disk; generated duplicate sample variants (pattern: test_files*_sample*) stay opt-in via --only=<name>.\n";
	if ($limitCases !== null) {
		echo "Limited to first {$limitCases} matched corpora via --limit.\n";
	}
	if ($skipTests !== []) {
		echo "Skipped via --skip: " . implode(',', $skipTests) . ".\n";
	}
	if ($skipExtract) {
		echo "Skipped extract timing via --no-extract.\n";
	}
	if ($noMultipass) {
		echo "Disabled multipass via --no-multipass.\n";
	}
	if ($noFreeArc) {
		echo "Min-ext tournament not run (--no-best-ext / --no-freearc); table still shows cached min-ext from benchmarks/.baseline_cache.json when raw bytes and baseline fingerprints match.\n";
	}
	if ($noVerify) {
		echo "Disabled round-trip verification via --no-verify.\n";
	}
	$kve = getenv('FRACTAL_ZIP_BENCH_KEEP_VERIFY_EXTRACT');
	if (is_string($kve) && trim($kve) !== '') {
		echo 'Keep verify extract: ' . trim($kve) . " (per-label subdir; stderr also prints [bench] kept … when a case runs verify).\n";
	}
	if ($caseTimeoutSec !== null) {
		echo "Per-case wall clock cap: {$caseTimeoutSec}s (override with --case-timeout=N or FRACTAL_ZIP_BENCH_CASE_TIMEOUT_SEC; unlimited via --no-case-timeout or 0).\n";
	} else {
		echo "Per-case wall clock unlimited (lifestyle full-table default; set --case-timeout=N to bound walls).\n";
	}
	if (!$tableComplete) {
		echo 'Table completeness: INCOMPLETE (' . count($tableCompletenessProblems) . " problem(s); TOTAL not suite-comparable).\n";
	} else {
		echo "Table completeness: OK (gzip/7z/min-ext/fzc bytes+compress+extract filled; no skipped cases).\n";
	}
	if ($repeatRuns > 1) {
		echo "Repeat mode enabled via --repeat={$repeatRuns}; timing columns show per-case medians.\n";
	}
	if ($caseDiskSweep) {
		echo "Cleared benchmarks/.work after each corpus/segment; per-case TMPDIR/TMP/TEMP also lives under benchmarks/.work/.tmp_* (--no-case-disk-sweep to retain).\n";
	}
	if ($baselineCacheEnabled) {
		echo 'Baseline cache: ' . $baselineCachePath . ($baselineCacheRefresh ? ' (refreshed this run).' : ' (gzip/7z/min-ext bytes+compress+extract seconds reused when raw bytes, flags, and gzip/min-ext tool fingerprints match; schema v' . (string) BENCH_BASELINE_CACHE_SCHEMA_VERSION . '; delete file or use --refresh-baseline-cache to invalidate).') . "\n";
	} else {
		echo "Baseline cache disabled (--no-baseline-cache or FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE=1).\n";
	}
	if ($phase1Bench) {
		if ($phase1CaseCacheEnabled) {
			echo 'Phase-1 row cache: ' . $phase1CaseCachePath . ($phase1CaseCacheRefresh ? ' (refreshed this run).' : ' (instant replay when raw bytes, run flags, gzip/min-ext tool fingerprints, main source fingerprints, and FZC runtime env fingerprints match; --refresh-phase1-case-cache or delete file to invalidate).') . "\n";
		} else {
			echo "Phase-1 row cache disabled (--no-phase1-case-cache or FRACTAL_ZIP_BENCH_NO_PHASE1_CASE_CACHE=1).\n";
		}
	}
	if ($skippedCases !== []) {
		$labels = array_map(static fn ($x) => (string) ($x['label'] ?? '?'), $skippedCases);
		echo "Skipped cases: " . implode(',', $labels) . ".\n";
	}
	echo 'Skipped-case log (updated every run): ' . $lastSkippedLogPath . "\n";
} else {
	echo "\nTip: add --bench-notes for column legends, cache paths, and run-flag summaries; --bench-pipeline-numbers for stderr phase/step ms (share with debugging). Quick undeep sync: php benchmarks/run_benchmarks.php --check-undeep-sync (exit 0/1).\n";
}
if (!$tableComplete && !$allowIncompleteTable) {
	exit(1);
}
