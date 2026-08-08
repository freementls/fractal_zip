# Encode pipeline (fractal_zip folder / literal bundle)

The phased model documents how `fractal_zip` is being refactored: **explicit stages**, **LPT-style scheduling** (start expensive independent work first), and **deterministic merges** when adding parallelism.

## Phases

| Phase | Constant | Meaning (target end state) |
|------|-----------|----------------------------|
| 1 | `PHASE_UNPEEL` | Depth / format unpeeling per member; deterministic, lossless; **gates use `fractal_zip_identify_for_policy()`** (extension = tier-1 only). Deep/large jobs scheduled first when parallel. See **`docs/PEELING_FOLLOW.md`**. Env: `FRACTAL_ZIP_LITERAL_PEEL_AGGRESSIVE=1` interleaves semantic peel with gzip stack expansion. **Folder `zip_folder`:** `fractal_zip_resolve_folder_logical_bundle()` peels multi-member PKZIP on disk into logical members before heterogeneous FZHM tournament; **FZHR** trailer restores original disk layout on extract (verbatim default; **`FRACTAL_ZIP_FOLDER_CONTAINER_SEMANTIC_VERIFY=1`** for bench semantic PKZIP). |
| 2 | `PHASE_STREAM_BUILD` | Build the literal bundle byte stream (FZB\*, FZCD, unified stream variants, gzip-fast inner, etc.). |
| 2.5 | `PHASE_STREAM_REORDER` | Optional reorder of members for locality (similarity clustering is not implemented yet; hook is identity + stable sort). |
| 3 | `PHASE_TRANSFORMS_AND_MODES` | Transform and mode tournament (moving toward priority-queue / bounded tree search vs time). |
| 4 | `PHASE_OUTER_CODECS` | Outer codec trials (gzip, zstd, …); slower candidates first when trials are independent. |

## Quick tests

```bash
php tests/encode_pipeline_smoke.php
```

Checks `path_extension_bucket`, extension-bucket reorder, `schedule_inner_variants_for_literal_outer` (LPT), merge helpers, wall/rollup hooks, and **`pick_smallest_literal_outer_vs_raw`** (loads **`fractal_zip.php`** for `fractal_zip::outer_candidate_beats_current` codec ordering).

**Fork pool (Linux + `pcntl`, loads `fractal_zip.php`):**

```bash
php tests/encode_pipeline_fork_smoke.php
```

Exercises `parallel_literal_variant_outer_trials` with `FRACTAL_ZIP_PIPELINE_PARALLEL=1` and two small fast-tier jobs. On non-Linux or without `pcntl`, prints `skip` and exits 0.

Run pipeline smoke bundle:

```bash
bash tests/run_pipeline_tests.sh
```

(`encode_pipeline_smoke.php` plus **`benchmarks/smoke_repro_folder_zip_roundtrip.php`** and fork / outer-step / outer-parallel parity smokes.)

**PHP smokes** (no compressor binaries): same as `.github/workflows/php-smokes.yml`. The runner is `tests/run_php_smokes.sh` (numbered phases 1–8 in-file: `php -l` bundle → undeep marker sync → all-substrings regression → **`benchmarks/smoke_repro_folder_zip_roundtrip.php`** (synthetic `.zip` strict tree) → recursive substring round-trip → fractal-inner recipes 140–150 → **`tests/encode_pipeline_smoke.php`** → inner pass regression → `smoke_random` case 23 → outer-predict smokes → web-fs hostile/inspect/download-range smokes).

```bash
bash tests/run_php_smokes.sh
```

Also exercises outer timing hooks (`encode_pipeline_outer_step_smoke.php`): subprocess run of `adaptive_compress` with `FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1` (+ optional verbose skips). On Linux + `pcntl`, `encode_pipeline_outer_parallel_parity_smoke.php` checks that **optional** `FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER=1` (zstd ‖ first-brotli overlap) still returns the **same outer blob as the sequential path** — the product goal remains **smallest size**; the test only guards that parallelism does not change the **winner** (or skip work the sequential tournament would still do).

GitHub Actions (`.github/workflows/pipeline-smoke.yml`) runs the same on `ubuntu-latest` when pipeline-related paths change.

**Bytes/time guards** (lifestyle 74/75/76 + six-case proxy; needs compressor CLIs on `PATH`): `.github/workflows/benchmark-guards.yml`. Local quick run: `php benchmarks/guard_lifestyle_bytes_and_time.php --repeat=1` and `php benchmarks/guard_sixcase_stable_perf.php --repeat=1`.

**Large-tree benchmark orchestration** (multi-corpus **`--jobs`**, baseline compressor threading, **`--bench-profile`** including **`large-bytes`**): **`benchmarks/LARGE_CORPUS_SPEED.md`**, **`bash benchmarks/run_large_corpus_bytes_push.sh`**, and **`benchmarks/PARALLELISM.md`**. The runbook’s **JSON machine output** section covers gitignored **`benchmarks/.last_bench.json`** vs **`--out-json=`** snapshots.

## Trace output

With `FRACTAL_ZIP_PIPELINE=1` or `FRACTAL_ZIP_PIPELINE_TRACE=1`, checkpoints appear on the HTML trace path when enabled. Under **CLI**, if normal HTML trace is off, checkpoints are written to **stderr** (unless `FRACTAL_ZIP_SUPPRESS_HTML=1` suppresses them) so benchmarks can still see phase boundaries without `FRACTAL_ZIP_CLI_VERBOSE=1`.

## Environment (all incremental, opt-in)

| Variable | Effect |
|----------|--------|
| `FRACTAL_ZIP_PIPELINE=1` | Enables pipeline helpers and default-on trace for checkpoints (see `fractal_zip_encode_pipeline.php`). |
| `FRACTAL_ZIP_PIPELINE_TRACE=1` | HTML trace checkpoints without requiring `FRACTAL_ZIP_PIPELINE`. |
| `FRACTAL_ZIP_PIPELINE_JOBS=N` | Max concurrent workers for future fork pools (default 4, cap 32). |
| `FRACTAL_ZIP_PIPELINE_PARALLEL` | **Default (unset):** on for **PHP CLI** on Linux (if `pcntl` exists); off for FPM/CGI unless you set `1` / `on`. Set to `0` or `off` to disable on CLI. When allowed and there are **≥2** literal inner candidates, `choose_smallest…` may run full/fast outer trials in child processes; on failure, sequential. |
| `FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER=1` | **Opt-in.** When `parallel_fork_allowed()` is true **and** this is set to `1`/`on`, `adaptive_compress` may **fork one child** to run the **first** outer `brotli` trial while the parent runs `zstd` (non–huge-probe path). Result is merged like sequential; the child is discarded if zstd early-stop would have skipped brotli. Does not parallelize 7z/arc/zpaq sweeps. |
| `FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE` | **Separate from** `PIPELINE_PARALLEL_OUTER`. Documented at the top of **`fractal_zip_encode_pipeline.php`**: when **≥2** slow outer families remain (7z / arc / zpaq), default **on** under CLI may **fork one trial per codec** and merge in fixed order; **`0`**/`off` forces a fully sequential slow sweep. See also **`benchmarks/PARALLELISM.md`** (orchestration vs compressor threading). |
| `FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1` | Prints per-checkpoint stderr lines in `adaptive_compress` (`[adaptive_outer] inner=… cum=… step=… <label>`). Labels include gzip/zstd/brotli gates, `after_fast_codec_merge`, then **only branches that run**: `before_outer_xz` / `after_outer_xz`, optional `*_7z_huge_once`, `before_outer_7z_best_sweep` / `after_outer_…`, arc (`before_outer_arc_*` / `after_outer_arc_*`), `before_outer_zpaq` / `after_outer_zpaq`, and `after_slow_outer_pass_complete`. |
| `FRACTAL_ZIP_PIPELINE_OUTER_STEP_VERBOSE=1` | With **`FRACTAL_ZIP_PIPELINE_OUTER_STEP_LOG=1`**, emits zero-step lines such as `skipped_outer_xz`, `skipped_outer_7z_huge_once`, `skipped_outer_arc_before_7z`, … when those outer sections **did not execute** (same cumulative timer; next real checkpoint still measures elapsed wall time correctly). |
| `FRACTAL_ZIP_PIPELINE_OUTER_STEP_ROLLUP` | When unset: rollup **is enabled together with** **`FRACTAL_ZIP_PIPELINE_TIMING=1`** (sums **`adaptive_compress`** step deltas across all literal passes for one `zip_folder`; **`[fz pipeline outer rollup]`** summary on stderr). Set **`1`**/`on` to rollup **without** pipeline phase timing; **`0`**/`off` disables rollup even when timing is on. Does not require **`PIPELINE_OUTER_STEP_LOG`** — step deltas are still measured inside **`adaptive_compress`** during an active rollup session. |
| `FRACTAL_ZIP_PIPELINE_REORDER=1` | Run `reorder_raw_members_for_locality()` in `encode_literal_bundle_payload` (baseline: same as `ksort` unless REORDER_EXT). |
| `FRACTAL_ZIP_PIPELINE_REORDER_EXT=1` | Requires REORDER: reorder members into **`resolvedContentProfile` buckets** (from `identify()` on member bytes), **largest raw bytes first** within each bucket — improves locality vs mixed-folder path sort; **changes FZB4 member order** vs default. |
| `FRACTAL_ZIP_PIPELINE_INNER_LPT=1` | In `choose_smallest_adaptive_literal_inner_or_raw_escaped`, score literal inner candidates **largest payload first** (helps when overlapping outer work later; on exact output-size ties, first-seen still wins — order can change the tag). |
| `FRACTAL_ZIP_PIPELINE_TIMING=1` | **`zip_folder` segment timers:** wall time from each `html_trace_checkpoint` until the **next** checkpoint or folder return is attributed to that opening phase (`preface`, then 1 / 2 / 2.5 / 3 / 4). Writes **`[fz pipeline timing]`** summary lines on **stderr** when `zip_folder` finishes (CLI). Includes subprocess wait inside the segment (outer codecs). Independent of pipeline trace/HTML. |

Existing env (`FRACTAL_ZIP_TIME_BUDGET_MS`, outer skips, speed profile, folder gzip-fast, unified stream, …) remains authoritative until migrated under one orchestrator. **Literal strict disk verify:** `choose_best_literal_bundle_transform` (phase 3) and the **raster-dedup** branch that wraps mode **10** via `fractal_zip_literal_bundle_wrap_all_layers` both call **`literal_bundle_coerce_verbatim_disk_roundtrip`** so the stored FZB literal decodes to the exact source member bytes; set **`FRACTAL_ZIP_LITERAL_DISABLE_DISK_ROUNDTRIP_COERCE=1`** to skip (diagnostics only — can make **`verify_ok`** false on strict SHA1 tree benches).

## Wiring today

- `fractal_zip.php` loads `fractal_zip_encode_pipeline.php`.
- `fractal_zip-undeep-unwrap.php` also loads it (alternate entry / stripped literal stack): same `choose_smallest_adaptive_literal_inner_or_raw_escaped` parallel literal outers, reorder hook in `encode_literal_bundle_payload`, and fork trial statics duplicated on `fractal_zip`.
- `zip_folder()` emits trace checkpoints at phase boundaries when trace is enabled (`FRACTAL_ZIP_PIPELINE=1` or `FRACTAL_ZIP_PIPELINE_TRACE=1`). With **`FRACTAL_ZIP_PIPELINE_TIMING=1`**, the same checkpoints drive per-phase **wall-clock** segment accounting and a **stderr** summary at return.
- `encode_literal_bundle_payload()` calls the reorder hook only when `FRACTAL_ZIP_PIPELINE_REORDER=1`; default is unchanged (plain `ksort`).
- `choose_smallest_adaptive_literal_inner_or_raw_escaped()` emits a phase-4 trace checkpoint and, when `FRACTAL_ZIP_PIPELINE_INNER_LPT=1`, visits literal variants in descending raw-inner size order.
- `collect_literal_bundle_inner_variants_for_folder()` emits a phase-2 trace line when pipeline tracing is on.
- `encode_literal_bundle_payload()` emits **phase 3** (`PHASE_TRANSFORMS_AND_MODES`) immediately **before** per-member `choose_best_literal_bundle_transform` (after layout/FZB4·5·6 shape probes and member-path validation), so unified-stream / bundle-only paths split stream-layout **phase 2** vs transform tournament **phase 3** in timings (`FRACTAL_ZIP_PIPELINE_TIMING=1`).

## Why a corpus (e.g. `test_files114` / kennedy.xls) may show no `zip_s` win

`run_benchmarks` row **zip_s** is **wall time for the whole folder encode** (fractal markers, literal bundle, **`adaptive_compress`** outer tournament including **zpaq**/7z/brotli trials, **multipass**, verify).

The encode pipeline changes so far mostly add:

1. **Fork pools** only when **`parallel_fork_allowed()`** is true **and** there are **≥2 literal inner variants** (FZCD/FZBM/FZB×…) to score—often **seconds at most** vs **tens of seconds** for zpaq + fractal passes on one inner blob.

2. **PHP does not multithread:** most outer codecs still run **sequentially** in `adaptive_compress` (7z/arc/xz/zpaq sweeps). With `FRACTAL_ZIP_PIPELINE_PARALLEL_OUTER=1`, the **first** zstd and **first** brotli pass can overlap via `pcntl_fork`; multipass and slow outers remain sequential unless redesigned further.

3. **zpaq** can already use **multiple OS threads** when you use **zpaqfranz** and set threads (defaults in `fractal_zip::zpaq_global_argv_shell_after_exe_from_env`; `run_benchmarks` sets `FRACTAL_ZIP_BENCH_ZPAQ_THREADS=0` when unset so Franz uses all cores). That is **separate** from the pipeline fork work.

To see a **real** “significant redesign” on **114-like** cases, the next major step is **parallel or staged-parallel `adaptive_compress` outer trials** (and/or parallel multipass with care), not more literal-fork wiring alone.

## Safety and parallelism

- **Determinism:** parallel workers must return results merged in a **fixed order** (e.g. path-sorted) so wire bytes and winner selection stay reproducible.
- **PHP model:** use processes (`pcntl_fork`), not threads; do not fork closures — pass serializable work specs (paths, argv). Until callers supply those specs, `parallel_or_sequential_map()` stays sequential.

## Migration priorities

1. Parallel independent unpeel / preprocess where merge order is defined.
2. Parallel outer trials with temp files + identical winner rules; **do not** flip sequential `adaptive_compress` to “slow codecs first” without parallelism — today it is staged (fast → early stop → slow) to minimize wall time.
3. Bounded priority-tree search for transforms/modes feeding existing tournament code — not a single-shot rewrite of `adaptive_compress`.
