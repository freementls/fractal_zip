# fractal_zip parallelization map

This document complements **`benchmarks/PARALLELISM.md`** (orchestration + compressor threading) and **`docs/ENCODE_PIPELINE.md`** (phased pipeline model). It focuses on **where CPU/GPU parallelism exists today**, **preset behavior**, and **ranked opportunities** for further work.

**Summary:** Outer encode is already heavily parallel via `pcntl_fork` in `fractal_zip_encode_pipeline.php`. Inner fractal search, FZBM path-order scoring, per-member transforms, and PAQ sweeps remain mostly **serial PHP**. Optional inner fork pools (`FZ_INNER_FRONTIER_JOBS`, `FZ_INNER_PIECE_JOBS`) live in `benchmarks/include/fractal_inner_passes.inc.php` but are not wired into production enwik encode paths. Substring scoring: **Python `cpu_parallel`** (ProcessPoolExecutor), optional **Rust/Rayon** (`gpu_substring_score_rs`), and **Numba CUDA** stub — see benchmark table below.

---

## Current state table

| Phase | What it does | Parallel today | Key env flags |
|-------|--------------|----------------|---------------|
| **Enwik entry sort + virtual folder** | Split `<page>`, title-sort, pack ~129 members (pp96) | **Serial** | `FRACTAL_ZIP_ENWIK_ENTRY_SORT`, `FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER`, `FRACTAL_ZIP_FOLDER_UNIFIED_STREAM` |
| **Text-inner build (FZTX/mi_reorder)** | Per-page tokenization + mono layout | **Parallel page-split fork pool** when `FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS>1` (CLI auto) | `FRACTAL_ZIP_TEXT_INNER*`, `FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS` |
| **Literal bundle collection** | FZCD → FZBM → FZBF → FZB chain | **Serial** build | `FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=1` skips FZBM/FZBF on enwik |
| **FZBM/FZB4 path-order scoring** | Up to 512 random concat orders + brotli/gzip proxy | **Parallel fork pool** when `FRACTAL_ZIP_FZBM_PATH_ORDER_PARALLEL=1` (CLI auto) | `FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES`, `FRACTAL_ZIP_FZBM_PATH_ORDER_PARALLEL` |
| **Peeler / run-grammar variants** | Multifractal peel + grammar sweeps | **Parallel fork pool** across members when `FRACTAL_ZIP_PEEL_JOBS>1` (LPT by size/format); GPU substring delegate in workers when `FRACTAL_ZIP_PEEL_GPU=1` | `FRACTAL_ZIP_PEEL_JOBS`, `FRACTAL_ZIP_PEEL_GPU`, `FRACTAL_ZIP_PEEL_PRIORITY`, `FRACTAL_ZIP_PEEL_MIN_MEMBERS`; skipped on enwik unified via `FRACTAL_ZIP_SUBSTRING_MULTIDIFF_RECURSIVE_ONLY` |
| **Multidiff / substring / reciprocal** | Token multidiff in `all_substrings_count`, reciprocal segment exchange | **Serial** slide; **parallel verify** fork when `FRACTAL_ZIP_GPU_SUBSTRING=1` and ≥48 candidates; inner **frontier/piece** forks in production when `FZ_INNER_FRONTIER_JOBS>1` | `FRACTAL_ZIP_GPU_SUBSTRING`, `FZ_INNER_FRONTIER_JOBS`, `FZ_INNER_PIECE_JOBS` |
| **Per-member transforms (phase 3)** | `choose_best_literal_bundle_transform` tournament | **Serial** per member | `FRACTAL_ZIP_LITERAL_TOURNAMENT_STRICT`, `FRACTAL_ZIP_LITERAL_TRANSFORM_MAX_RAW_BYTES` |
| **Pipeline reorder** | Extension-bucket + LPT member order | **Serial** (order only) | `FRACTAL_ZIP_PIPELINE_REORDER`, `FRACTAL_ZIP_PIPELINE_REORDER_EXT` |
| **Literal inner × outer trials** | Fork one process per variant (fast/full tier) | **Parallel** (CLI default on) | `FRACTAL_ZIP_PIPELINE_PARALLEL`, `FRACTAL_ZIP_PIPELINE_JOBS`, `FRACTAL_ZIP_PARALLEL_OUTER_MIN_INNER_BYTES` |
| **Outer `adaptive_compress`** | zstd/brotli/xz/7z/arc/zpaq sweeps | **Parallel waves** (CLI default on) | `FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE`, `FRACTAL_ZIP_PARALLEL_BROTLI_LGWIN_WAVE`, `FRACTAL_ZIP_PARALLEL_ZPAQ_OUTER_METHOD_WAVE`, `FRACTAL_ZIP_PARALLEL_ZSTD_FZB_ALT_WAVE`, `FRACTAL_ZIP_PARALLEL_HUGE_ARC_WAVE`, `FRACTAL_ZIP_PARALLEL_OUTER_PREDICTION` |
| **Compressor threading** | MT inside 7z/xz/zpaq/brotli wrappers | **External tool MT** | `FRACTAL_ZIP_ZPAQ_THREADS`, `FRACTAL_ZIP_7Z_MMT`, `FRACTAL_ZIP_ZSTD_THREADS`, etc. (see `benchmarks/PARALLELISM.md`) |
| **PAQ native compare** | phda9/paq8px/cmix folder sweep at encode end | **Parallel fork per tool** when `FRACTAL_ZIP_PAQ_SWEEP=1` + pcntl | `FRACTAL_ZIP_PAQ_NATIVE_COMPARE`, `FRACTAL_ZIP_PAQ_SWEEP` (hours-scale; off in inner_* presets) |
| **Benchmark orchestration** | Multi-corpus `--jobs` | **Parallel** fork pool | `run_benchmarks.php --jobs=N`, `perf_test.php --jobs=N` |
| **GPU substring spike** | Rolling substring count top-K | **Rust/Rayon** (`gpu_substring_score_rs`); Python ProcessPool fallback; optional Numba CUDA | `benchmarks/bench_fractal_substring_gpu_spike.php`, `FZ_GPU_SUBSTRING_MODE`, `FZ_GPU_SUBSTRING_RUST` |

See also: **`benchmarks/ENWIK8_INNER_CAPS.md`**, **`docs/WORLD_RECORD_PRESET.md`**.

---

## Outer fork pools (already implemented)

`fractal_zip_encode_pipeline.php` documents and implements CLI-default-on fork pools when `parallel_fork_allowed()` is true (Linux + `pcntl_fork`, not FPM):

- Literal-variant outer trials (`parallel_literal_variant_outer_trials`)
- Slow outer wave: 7z / arc / zpaq overlap (`parallel_slow_outer_wave_trials`)
- Brotli lgwin variant wave, zstd FZB alt wave, huge-once 7z ‖ arc wave
- Zpaq outer method sweep, folder native zpaq sweep
- Layered outer prediction forks (`FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER_FORK`)
- Zip-folder fast-tier wire shootouts (`FRACTAL_ZIP_PARALLEL_ZIP_FOLDER_*`)

Pool width is capped by `FRACTAL_ZIP_PIPELINE_JOBS` (CLI default: detected CPU count, max 32). Micro inners below `FRACTAL_ZIP_PARALLEL_OUTER_MIN_INNER_BYTES` (default 8192) skip fork overhead.

---

## Inner fractal fork pools (implemented, not production-default)

In `benchmarks/include/fractal_inner_passes.inc.php`:

| Env | Behavior | Thresholds |
|-----|----------|------------|
| **`FZ_INNER_FRONTIER_JOBS`** | Partition multipass frontier rows across `pcntl_fork` workers (`fz_expand_frontier_one_pass_forked`) | Min rows: `FZ_INNER_FRONTIER_FORK_MIN` (default 48) |
| **`FZ_INNER_PIECE_JOBS`** | Parallelize append trials within very wide piece lists | Min pieces: `FZ_INNER_PIECE_FORK_MIN` (default 2048) |

Workers force `FZ_INNER_FRONTIER_JOBS=1` to avoid nested frontier recursion; piece-order parallelism is preserved from the parent env.

**Exposure:** `benchmarks/smoke_random.php` (`--inner-frontier-jobs=N`, `--inner-piece-jobs=N`). Production `zip_folder` preloads inner algorithms when `FZ_INNER_FRONTIER_JOBS>1` (auto on CLI).

**Dead alias (fixed in pp96 refresh):** Some scripts historically set `FRACTAL_ZIP_INNER_FRONTIER_JOBS`; only `FZ_INNER_FRONTIER_JOBS` is read. Use `FZ_INNER_FRONTIER_JOBS` everywhere, or set both to the same value.

**FZSO stacked outer:** `FRACTAL_ZIP_STACKED_OUTER=1` store-passthrough in `adaptive_compress` when inner contains `FZSO` (avoids double outer on pre-stacked text-inner). Wire probe: `split_inner_fztx_mono_mi_stack_passthrough`.

---

## Top 5 opportunities (ranked)

| Rank | Opportunity | CPU vs GPU | Impact | Effort | Bytes risk |
|------|-------------|------------|--------|--------|------------|
| **1** | **Parallel FZBM/FZB4 path-order candidate scoring** — fork pool over brotli/gzip proxy trials in `literal_bundle_pick_fzbm_concat_key_order` (~512 tries on enwik); reuse `FRACTAL_ZIP_PIPELINE_JOBS` | **CPU** fork | **High** on multi-member folders | **Low–medium** | **Low** (min-score merge; tie-break on sig string) |
| **2** | **Wire inner fractal parallelism into production encode** — bridge `FRACTAL_ZIP_INNER_FRONTIER_JOBS` → `FZ_INNER_FRONTIER_JOBS`; default >1 on CLI for multipass; enable `FZ_INNER_PIECE_JOBS` for wide append lists | **CPU** fork | **High** on multipass/fractal-heavy corpora; **medium** on enwik reciprocal path | **Medium** | **Low** (dedupe merge in `fz_dedupe_frontier_by_encoding`) |
| **3** | **Parallel PAQ native tool sweep** (`phda9` ‖ `paq8px` ‖ `cmix`) when `FRACTAL_ZIP_PAQ_NATIVE_COMPARE=1` | **CPU** processes | **High** wall-time on world-record preset (hours → ~1/N) | **Low** | **None** (smallest output wins) |
| **4** | **Parallel per-member literal transform tournaments** (phase 3 `choose_best_literal_bundle_transform`) | **CPU** fork | **Medium** on heterogeneous folders; lower on unified enwik | **Medium** | **Low** (independent members, path-sorted merge) |
| **5** | **Parallel reciprocal multidiff literal outer jobs** — extend `parallel_literal_variant_outer_trials` when `SUBSTRING_MULTIDIFF_MAX_LITERAL_JOBS>10`; pair with `inner_focus` cap relaxation | **CPU** fork | **Medium** (more inner exploration without serial outer rescoring) | **Low–medium** | **Low** (same merge rules) |

**GPU does not rank in the top 5** for production integration until a CUDA kernel beats Rust on raw ≥1 MiB with ≥90% top-K agreement. **Rust/Rayon helper clears the integrate gate on enwik shell slices** (see benchmark table). **FZBM path-order** remains serial — rank #1 next CPU win.

---

## GPU section (defer)

| Operation | GPU fit | Maturity |
|-----------|---------|----------|
| Rolling-hash substring counting | Good (embarrassingly parallel windows) | **Rust CPU parallel** (`gpu_substring_score_rs`, rayon); optional Numba CUDA stub |
| Multidiff token-pair probes | Moderate | Not implemented |
| Brotli/zpaq outer | Poor (sequential entropy coding) | Use CPU MT in binaries |
| Diff/match encoding | Moderate | Needs custom kernel + PHP IPC |

**Measured spike:** `benchmarks/bench_fractal_substring_gpu_spike.php` — auto prefers Rust when built; **`--mode=cpu_parallel`** forces Python ProcessPool. Deterministic top-K: count desc, len asc, first `i` asc. **`cuda_available=no`** on this host (Numba not installed). On **1 MiB raw enwik** (`--slice=raw1m`), Python **`cpu_parallel`** reaches **4.57×** vs PHP with **100%** top-K overlap.

**Practical path:** Rust scorer JSON IPC → integrate into `all_substrings_count` after review; optional Numba CUDA via `gpu_substring_score_cuda.py` when `cuda.is_available()`.

Neural/GPU text adapters (`llmzip_llama7b`, `nncp32`) are separate from fractal substring search — see **`docs/ENWIK8_TEXT_CODEC_LAB.md`** and `bash benchmarks/run_enwik8_sub1_gpu_matrix.sh`.

---

## Preset notes (pp96 vs world-record)

| Preset | Parallelism often disabled | Best parallel ROI (ratio unchanged) |
|--------|---------------------------|-------------------------------------|
| **pp96_refresh** (`run_enwik8_pp96_refresh.php`) | `FRACTAL_ZIP_PIPELINE_PARALLEL=0`, `FRACTAL_ZIP_SKIP_BROTLI=1`, `FRACTAL_ZIP_OUTER_PREDICT=0`, `FRACTAL_ZIP_PARALLEL_OUTER_PREDICT_LAYER_FORK=0`, `FZ_INNER_FRONTIER_JOBS=0`, `FRACTAL_ZIP_PAQ_SWEEP=0` — for **stable probe timing**, not because parallel paths change wire bytes | Re-enable pipeline parallel + zpaq threads for production encodes; parallel PAQ sweep if compare re-enabled |
| **world-record (full)** (`bench_world_record_apply_env_defaults`) | PAQ compare on (hours); enwik `SUBSTRING_MULTIDIFF_RECURSIVE_ONLY` skips FZBM/FZBF collection | Parallel PAQ tools; parallel literal outer jobs when many variants; zpaq/7z waves already on |
| **pp96_core** (`bench_world_record_apply_pp96_core_env`) | Same topology as WR without `apply_high_env` outer expansion | Same as WR minus high-tier predict/multipass depth |
| **inner_baseline / inner_focus** | PAQ off; outer predict capped | Parallel inner frontier (`FZ_INNER_FRONTIER_JOBS`); raise caps + parallel literal job scoring |
| **ultra** | None by default | Outer waves + strict transform mode benefit from **per-member transform forks** |
| **Probes** (`bench_enwik8_*`) | `FRACTAL_ZIP_PIPELINE_PARALLEL=0` everywhere | Use for A/B **bytes** only; use separate wall-time harness for parallel A/B |

On enwik pp96, dominant wall-time is typically **zpaq outer + inner reciprocal multidiff**, not FZBM path-order (skipped when `SUBSTRING_MULTIDIFF_RECURSIVE_ONLY=1`).

---

## External binaries: parallel vs serial

| Tool / phase | Parallel today? | Difficulty | Notes |
|--------------|-----------------|------------|-------|
| **zpaq** / zpaqfranz | **MT** (`-threads N`) | Easy | Hours-scale outer on WR; use `FRACTAL_ZIP_ZPAQ_THREADS`. Multiple **methods** already fork in `parallel_zpaq_outer_method_variant_trials`. |
| **7z** / **arc** | **MT** (`-mmt`) | Easy | Slow outer wave forks 7z ‖ arc ‖ zpaq families in parallel. |
| **zstd** / **xz** | **MT** (`-T`) | Easy | Outer compress + some literal_pac trials. |
| **brotli** (stock CLI) | **Mostly serial** | Hard | Reference `brotli` is single-threaded; use `FRACTAL_ZIP_BROTLI_EXTRA_ARGS` with a threaded build/wrapper. FZBM path-order fires **hundreds** of brotli/gzip **probes** — now fork-parallel via `FRACTAL_ZIP_FZBM_PATH_ORDER_PARALLEL=1`. |
| **phda9** / **paq8px** / **cmix** | **Serial ladder** | Medium | Each tool is one long-running process; **parallel fork per tool** is rank #3 (not wired). phda9 itself is not multi-threaded. |
| **gzip** / **pigz** | **pigz -p** optional | Easy | `FRACTAL_ZIP_LITERALPAC_PIGZ_P`, bench gzip baseline. |
| **Rust `gpu_substring_score_rs`** | **Rayon** | Easy | 8–23× on fixed-window count vs PHP; peel + benchmark. |
| **Numba CUDA** | **Device kernel** (new) | Medium | `gpu_substring_score_cuda.py`; set `FZ_GPU_SUBSTRING_PREFER_CUDA=1` after `check_gpu_setup.sh` passes. |
| **PHP `all_substrings_count`** | **Parallel verify** (pcntl batch `substr_count`); slide still serial; Rust slide map opt-in via `FRACTAL_ZIP_GPU_SUBSTRING_ALL=1` | Medium | Peel + verify delegate wired; full slide replace still TODO |
| **Text-inner FZTX mono build** | **Parallel page-split** fork pool | Easy | `FRACTAL_ZIP_TEXT_INNER_BUILD_JOBS` (CLI auto) |
| **Neural text codecs** (nncp, LLMZip) | GPU if framework provides | Hard | Separate lab track; not fractal substring. |

**CUDA setup after install:**

```bash
pip install --user -r tools/gpu_substring/requirements-gpu.txt
bash tools/gpu_substring/build.sh
bash tools/gpu_substring/check_gpu_setup.sh
```

---

## Process guard (zombie prevention)

**`fractal_zip_process_guard.php`** registers on CLI when `fractal_zip` loads (via `bootstrap_cli_parallel_defaults_if_cli`):

- **Shutdown / SIGTERM / SIGINT / SIGHUP** — reap tracked fork children and all direct child PIDs (`/proc` PPid scan).
- **`fractal_zip_process_guard_sweep_strays()`** — kill compressors / stuck `rg` / old bench PHP under the repo (default age **1800 s**); called from `run_benchmarks.php` shutdown and probe scripts.

Manual sweep:

```bash
bash tools/fz_sweep_zombies.sh
FRACTAL_ZIP_PROCESS_GUARD_SWEEP_AGE_SEC=300 bash tools/fz_sweep_zombies.sh   # aggressive (5 min)
FRACTAL_ZIP_PROCESS_GUARD=0   # disable
```

---

## Integrated auto-parallelism (`fractal_zip_parallel_runtime.php`)

On **PHP CLI + pcntl**, when env knobs are **unset**, `fractal_zip_encode_pipeline::bootstrap_cli_parallel_defaults_if_cli()` applies:

| Auto-detected | Env set when unset |
|---------------|-------------------|
| CPU count (`nproc` / `/proc/cpuinfo`) | `FRACTAL_ZIP_PIPELINE_JOBS`, `FRACTAL_ZIP_ZPAQ_THREADS`, `FRACTAL_ZIP_ZSTD_THREADS` |
| Fork pool cap `min(8, CPUs)` | `FRACTAL_ZIP_PEEL_JOBS`, `FZ_INNER_FRONTIER_JOBS`, `FZ_INNER_PIECE_JOBS` |
| Rust `gpu_substring_score_rs` built | `FRACTAL_ZIP_GPU_SUBSTRING=1`, `FRACTAL_ZIP_PEEL_GPU=1` |
| pcntl available | `FRACTAL_ZIP_FZBM_PATH_ORDER_PARALLEL=1`, outer fork waves (existing bootstrap) |

**Kill switches:** `FRACTAL_ZIP_PARALLEL_OFF=1` (disable auto defaults). **`FRACTAL_ZIP_PARALLEL_PROBE=1`** (byte probes — `run_enwik8_pp96_refresh.php` without `--text-inner-promotion`).

**Inspect:** `fractal_zip_parallel_runtime_report()` after bootstrap.

```bash
bash tools/gpu_substring/build.sh   # enables Rust substring auto-default
bash benchmarks/run_enwik8_world_record.sh   # auto-parallel on load
php -d memory_limit=4096M benchmarks/run_enwik8_pp96_refresh.php --text-inner-promotion
```

Explicit env values always override auto-detection. Peel workers and **`all_substrings_count` verify batch** use Rust/parallel helpers when built; variable-length slide remains serial (23× spike target for future `FRACTAL_ZIP_GPU_SUBSTRING_ALL=1` slide assist).

---

## Suggested experiments

```bash
# Wall-time A/B: pipeline parallel on vs off (same bytes expected)
FRACTAL_ZIP_PIPELINE_PARALLEL=1 FRACTAL_ZIP_PIPELINE_TIMING=1 php fractal_zip.php ...
FRACTAL_ZIP_PIPELINE_PARALLEL=0 FRACTAL_ZIP_PIPELINE_TIMING=1 php fractal_zip.php ...

# Inner fractal frontier (smoke_random sets FZ_INNER_FRONTIER_JOBS)
php benchmarks/smoke_random.php --inner-frontier-jobs=8 --case=...

# FZBM order scoring stress (world-record tries)
FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES=512 FRACTAL_ZIP_PIPELINE_TIMING=1 ...

# PAQ parallel sweep prototype (fork per tool — not implemented yet)
FRACTAL_ZIP_PAQ_NATIVE_COMPARE=1 FRACTAL_ZIP_PAQ_SWEEP=1 ...

# GPU spike (Rust helper; CUDA when Numba available)
php benchmarks/bench_fractal_substring_gpu_spike.php --pages=384
```

### Benchmark results (2026-06-06)

**CUDA on this host:** Numba not installed → **`cuda_available=no`**. Python helper uses **`mode=cpu_parallel`** (ProcessPoolExecutor over starting positions; serial under 64 KiB). Auto mode prefers **`cpu_parallel_rust`** when built (`cargo build --release` in `tools/gpu_substring/`).

| Benchmark | Metric | Before | After | Pass gate? |
|-----------|--------|--------|-------|------------|
| `bench_fractal_substring_gpu_spike.php` (serial Python stub, ~2 KiB shell) | speedup / top-K overlap | **0.07×** / n/a | — | **FAIL** (integrate gate ≥2×, ≥90%) |
| same (`--slice=raw1m`, 1 MiB, `--mode=cpu_parallel`) | speedup / overlap | 160.7 s PHP CPU | **35.2 s** helper → **4.57×** / **100%** | **PASS** |
| same (default auto, 143 KiB enwik shell) | speedup / overlap | ~3.7 s PHP CPU | **0.60 s** Rust → **6.18×** / **100%** | **PASS** |
| `bench_fractal_parallel_phases.php` (5× sample5 text, `PIPELINE_PARALLEL` 0→1, slow outer wave) | `zip_folder` wall | **37.2 s** | **15.5 s** → **2.40×** (bytes match) | **PASS** (≥1.10×) |
| same (`smoke_random --case=23`, `FZ_INNER_FRONTIER_JOBS` 1→4, `--max-passes=2`) | inner wall | **3.19 s** | **2.95 s** → **1.08×** | **PASS** (≥1.05×) |

**Takeaways:** Use **`FRACTAL_ZIP_PIPELINE_PARALLEL=1`** on multi-member CLI encodes with outer fork pools enabled. Inner frontier speedup is modest on case 23 with two passes — raise **`--max-passes`** or corpus size for larger frontier rows. Wire **`gpu_substring_score`** / Rust into `all_substrings_count` after review; FZBM path-order scoring remains the top serial CPU target.

Run:

```bash
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
php benchmarks/bench_fractal_substring_gpu_spike.php [--slice=raw1m] [--mode=auto|cpu_parallel|rust|cuda]
php benchmarks/bench_fractal_parallel_phases.php [--pages=N]
cd tools/gpu_substring && cargo build --release   # optional Rust helper
```

Output: `benchmarks/.fractal_substring_gpu_spike.json`, `benchmarks/.fractal_parallel_phases.json`.

---

## Safety and determinism

- Parallel workers must merge in **fixed order** (path-sorted, first-seen tie-breakers) so wire bytes stay reproducible.
- PHP model: **processes** (`pcntl_fork`), not threads; pass serializable work specs (paths, argv, JSON IPC).
- Do not fork inside long-lived FPM workers; use CLI job queues for web-scale parallelism (see `benchmarks/PARALLELISM.md`).

---

## Code references

| Area | Primary file |
|------|--------------|
| Outer fork pools | `fractal_zip_encode_pipeline.php` |
| Literal inner × outer trials | `fractal_zip.php` → `choose_smallest_adaptive_literal_inner_or_raw_escaped` |
| Inner frontier/piece forks | `benchmarks/include/fractal_inner_passes.inc.php` |
| FZBM path-order scoring | `fractal_zip.php` → `literal_bundle_pick_fzbm_concat_key_order` |
| PAQ sweep | `fractal_zip_paq.php` |
| GPU spike | `benchmarks/bench_fractal_substring_gpu_spike.php`, `tools/gpu_substring/gpu_substring_score`, `tools/gpu_substring/target/release/gpu_substring_score_rs` |
| Parallel peel pool | `fractal_zip_encode_pipeline.php` → `parallel_member_peeler_views_collect`, `fractal_zip_gpu_substring.php` |
| Presets | `benchmarks/bench_world_record_env.php`, `benchmarks/bench_world_record_inner_env.php` |
