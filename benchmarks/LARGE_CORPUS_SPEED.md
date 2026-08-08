# Large corpora: comparable wall time + bytes

Runbook for **`benchmarks/run_benchmarks.php`** on big trees: keep baseline compressor threading aligned, use **`--jobs`** for orchestration, pick a **`--bench-profile`** (**`large-fast`**, **`large-balanced`**, or **`large-bytes`** for modest extra ratio work vs balanced), and validate bytes periodically.

## Orchestration: `--jobs`

- Linux + **`pcntl_fork`** + **`--repeat=1`**: use **`--jobs=$((nproc))`** for parallel corpora. Cap at **8–16** if RAM is tight (each worker holds a full case).
- Parallel mode **defers** the default per-case disk sweep until all workers finish; see **`benchmarks/PARALLELISM.md`** (`run_benchmarks.php --jobs=N`).
- Keep **baseline cache** enabled (do **not** set `FRACTAL_ZIP_BENCH_NO_BASELINE_CACHE`) so gzip / 7z / min-ext columns reuse work across cases and repeats.

## Threading env block (fair baselines)

Set the same block for the whole shell session so **`.fz`** and **gzip / 7z / min-ext** see comparable parallelism. Full variable list and semantics: **`benchmarks/PARALLELISM.md`** (compressor threading table).

Typical starting point (adjust to your machine):

```bash
export FRACTAL_ZIP_7Z_MMT=on
export FRACTAL_ZIP_BENCH_ZSTD_THREADS=0    # zstd -T0-style “use cores” in bench paths
export FRACTAL_ZIP_BENCH_ZPAQ_THREADS=0    # zpaq -threads 0 when bench injects flags
export FRACTAL_ZIP_ARC_MT=auto
export FRACTAL_ZIP_BENCH_ARC_MT=auto
```

Record what you used in **`--bench-notes`** or in commit messages when publishing JSON.

## Profile choice: `large-fast` vs `large-balanced` vs `large-bytes`

All three profiles use **`FRACTAL_ZIP_SPEED=1`**, tighter outer-predict caps, **`FRACTAL_ZIP_BROTLI_HUGE_MODE=probe`**, and phase-1 timeout store fallback. **Native 7z** merged-raw compare cap is **12 MiB** for **`large-fast`** / **`large-balanced`** and **16 MiB** for **`large-bytes`**. **Native zpaq** folder compare cap is **2 MiB** for **`large-fast`** / **`large-balanced`** and **6 MiB** for **`large-bytes`** — enough to skip huge-tree sweeps while still letting small zpaq-heavy folders compete.

| Profile | Extra behavior |
|--------|----------------|
| **`large-fast`** | Maximum wall-clock bias; native **brotli** folder compare follows the **7z** cap (12 MiB) unless you override `FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES`. |
| **`large-balanced`** | Sets **`FRACTAL_ZIP_SPEED_TRY_BROTLI=1`** and **`FRACTAL_ZIP_FOLDER_NATIVE_BROTLI_MAX_RAW_BYTES=2MiB`** so native tournaments stay bounded on very large merged raw trees. |
| **`large-bytes`** | **`large-balanced` + wider caps:** native **7z** folder compare up to **16 MiB** merged raw (vs **12 MiB**), **brotli** native cap **5 MiB** (vs **2 MiB**), **zpaq** compare cap **6 MiB** (vs **2 MiB**), outer-predict inner ceiling **8 MiB** (vs **4 MiB**), slightly longer predict timeout (**8 s**), default per-case timeout **150 s**, and **`FRACTAL_ZIP_FOLDER_GZIP_FAST_DEFLATE_LEVEL=8`** when unset (gzip-fast streaming inner on huge heavy-list trees). Still **`FRACTAL_ZIP_SPEED=1`**. Use when you want **cheap extra ratio** vs **`large-balanced`** and can pay more wall time; validate with **`php benchmarks/report_bytes_wins.php --compress-time-audit`** on JSON. |

**`large-fast`** no longer disables native zpaq compare entirely (avoid the old bytes regression on Squash-style folders).

```bash
php benchmarks/run_benchmarks.php --bench-profile=large-fast --jobs=8 --repeat=1 …
php benchmarks/run_benchmarks.php --bench-profile=large-balanced --jobs=8 --repeat=1 …
php benchmarks/run_benchmarks.php --bench-profile=large-bytes --jobs=8 --repeat=1 --only=test_files55_sample --large --no-case-timeout --json
```

**Wrapper (sets threading block + `JOBS` + `FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G` by default):**

```bash
bash benchmarks/run_large_corpus_bytes_push.sh --only=test_files55_sample --large --json --no-case-timeout
# Silesia x12 folder (~212 MiB raw; materialize test_files133 first — benchmarks/build_test_files133_silesia12.php):
bash benchmarks/run_large_corpus_bytes_push.sh --only=test_files133 --large --json --no-case-timeout --out-json=benchmarks/.silesia133_largebytes.json
```

**Silesia / Mahoney reference totals** (per-file compressed sums vs our corpora): **`benchmarks/SILESIA_BENCHMARK.md`**. Sum twelve **`fzc_bytes`** from bench JSON: **`php benchmarks/silesia_sum_fzc_from_bench_json.php …`**. For fair per-file **`.fz`** sums, set **`FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0`** (otherwise **`fzc_bytes === seven_zip_folder_bytes`** passthrough can dominate — **`php benchmarks/analyze_bench_native_passthrough.php`**).

## Heavy folders: gzip-fast vs `--large`

- Default: corpora in the heavy list get **`FRACTAL_ZIP_FOLDER_GZIP_FAST=1`** when raw ≥ **128 MiB** (see driver header in `run_benchmarks.php` and **`FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES`**). This favors wall time.
- **`--large`** forces **`FRACTAL_ZIP_FOLDER_GZIP_FAST=0`** on those names for a **full fractal** folder path (slower; use for bytes validation).
- Periodically run **`--large`** on subsets that showed **`[ratio caveat]`** (or similar stderr hints) to confirm **`.fz`** still wins when the full path is enabled.

## Stratified sampling before full corpus tuning

For truly huge suites, build a **stratified sample** (content-type mix, spread across large files), tune SPEED / outer-predict knobs on the sample, then validate the full tree with **`--jobs`** and the threading env above. Workflow: **`.cursor/skills/large-corpus-sampling/SKILL.md`** and **`benchmarks/sample_large_corpus.php`**.

**Silesia:** Mahoney totals, **`test_files78`** vs **`test_files133`**, **`run_large_corpus_bytes_push.sh`** (wrapper block above), ZIP peel/rebuild: **`benchmarks/SILESIA_BENCHMARK.md`**.

Optional tuning-only objective: **`FRACTAL_ZIP_FAST_CORPUS_ZIP_SEC`** (see `run_benchmarks.php` docblock). **Unset** for final bytes verification.

## Baseline cache hygiene

**File:** `benchmarks/.baseline_cache.json` (override with **`FRACTAL_ZIP_BENCH_BASELINE_CACHE`** or **`--baseline-cache=`**).

Refresh when:

- Native-folder wire shapes or **`.7z`** integration change.
- Min-ext / gzip / 7z **tool versions** or **`PATH`** change (fingerprints no longer match).
- Threading or pigz/brotli extras change keys (e.g. **`FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ`**, **`FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS`**).
- **Stale `.fz` outer choice after profile / census work:** e.g. **`test_files55_sample`** jumped to **zstd** ~2.86 MiB with cache on, **arc** ~2.18 MiB with **`--no-baseline-cache`** (tracker **2 158 962**). Re-run once with **`--refresh-baseline-cache`** after tier changes, or drop labels: **`php benchmarks/baseline_cache_drop_corpora.php test_files55_sample`**.

**How:** run once with **`--refresh-baseline-cache`**, or delete stale corpus entries from the JSON. If the repo tracks **`--out-json`** goldens, re-save them after a clean full run.

## JSON machine output

Default **`benchmarks/.last_bench.json`** and **`benchmarks/.last_skipped_cases.json`** are **gitignored** in a normal checkout (ephemeral last-run copies). For **`--from-json`** on guard scripts, public tracker rows, or CI, save with **`--out-json=path/to/bench.json`** or set **`FRACTAL_ZIP_BENCH_LAST_JSON`**. Patterns live in **`.gitignore`**. Main **`run_benchmarks.php --json`** payload uses **`JSON_INVALID_UTF8_SUBSTITUTE`** when the PHP build supports it (same idea as census stderr in **`fractal_zip`**).

**`verify_ok` / `verify_mismatch_files`:** **`true`** / **`false`** mean strict SHA1 tree verification ran and passed / failed. With **`--no-verify`**, both fields are **`null`** (skipped), not false / zero — do not treat **`null`** as a verify failure in **`jq`** filters (use **`== false`** for real failures). **`--repeat`** merges repeats via **`aggregateRepeatedRows`** in **`run_benchmarks.php`** with the same semantics.

**Bench JSON:** each **`cases[]`** row may include **`folder_bundle_census`** (extension byte mass + **`textish_ratio`**) after `collect_raw_files_for_bundle`. **`FRACTAL_ZIP_FOLDER_BUNDLE_CENSUS_STDERR=1`** prints the same object to stderr during collect (stderr JSON uses **`JSON_INVALID_UTF8_SUBSTITUTE`** when the PHP build supports it, so odd extension keys from the OS do not break the line); **`FRACTAL_ZIP_FOLDER_BUNDLE_SCHED_COST_BIAS=1`** uses the census only to nudge inner-variant **`cost_hint`** scheduling on text-heavy trees (optional). Human **`run_benchmarks.php`** tables: **`FRACTAL_ZIP_BENCH_SHOW_FOLDER_CENSUS=1`** adds column **`tx%`** (100×**textish_ratio**; — if census missing). Census accumulate vs pack parity: **`php benchmarks/smoke_folder_bundle_census_equiv.php`** (also **`tests/run_php_smokes.sh`** phase 2 after undeep check). With **`--repeat`**, **`bench_folder_bundle_census_merge_into_aggregated_row`** (**`benchmarks/bench_folder_census.php`**) fills **`folder_bundle_census`** when the first repeat row omitted it.

## Compress-time audit vs baselines

After a JSON run:

```bash
php benchmarks/report_bytes_wins.php --compress-time-audit path/to/bench.json
php benchmarks/report_bytes_wins.php --compress-time-audit --zip-time-margin=0.25 path/to/bench.json
```

Lists rows where **`zip_seconds`** exceeds **`min(gzip9_seconds, seven_zip_seconds, best_ext_seconds)`** (nulls skipped), optionally requiring at least **`--zip-time-margin`** seconds of slack. When **`folder_bundle_census`** is present, the table includes **`tx%`** (100×**textish_ratio**).

**Pinned audit targets (2026-05-13, re-run after encoder changes):**

| Corpus | zip_s | baseline_min | excess | Notes |
|--------|------:|-------------:|-------:|-------|
| `test_files78` | ~4800 | 433.9 | ~11× | peel-first FZHM; **fzc** now **39.1 MiB** (was ~67 MiB opaque); use **`run_bench_with_cleanup.sh`** |
| `test_files55_stratified` | 28.5 | 1.8 | 16× | HTML slice; ratio work separate from wall |
| `test_files133_sample` | ~382 | — | — | Arc outer; use **`guard_test_files133_sample_bytes_and_time.php`** |

Full multi-knob sweep: **`bash benchmarks/tiktok_large_corpus_sweep.sh test_files133_sample`**.  
Fast winner-only pass: **`bash benchmarks/tiktok_large_corpus_sweep.sh test_files108 --quick-wins`** (profiles + `tik/tok` banner; skips probe/census stages).  
Sample-first from full corpus: **`bash benchmarks/tiktok_large_corpus_sweep.sh test_files133 --auto-sample --sample-target-mib=28 --validate-full`**.  
Probe grid (with recommendation JSON + env export): **`php benchmarks/tiktok_probe_sweep.php --only=test_files133_sample`** or **`--auto-sample`** on full corpora.  
Guard recommendation quality: **`php benchmarks/guard_tiktok_probe_recommend.php --recommend-json=benchmarks/.tiktok_probe_test_files133_sample/recommend.json --only=test_files133_sample`**.

When probe sweep finds a winner, it also writes **`recommend.env`** with:

```bash
export FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES=<recommended_probe>
```

Apply immediately in your shell:

```bash
source benchmarks/.tiktok_probe_test_files133_sample/recommend.env
```

From a full sweep directory, derive one overall recommendation (profile + probe):

```bash
php benchmarks/tiktok_recommend_from_sweep.php --out-dir=benchmarks/.tiktok_sweep_YYYYmmdd_HHMMSS
```

This writes:

- `recommend_overall.json`
- `recommend_overall.env` (probe export + profile hint)
- `recommend_replay.sh` (one-command rerun with recommended profile/probe)

Detect drift against previous comparable sweep recommendation:

```bash
php benchmarks/tiktok_recommend_drift.php --latest-out-dir=benchmarks/.tiktok_sweep_YYYYmmdd_HHMMSS
```

This writes `recommend_drift.json` and prints a one-line status with:

- `profile_flip` and `probe_flip`
- `unexpected=1` when a flip regresses both bytes (>1%) and speed (>10%)

Show explicit winners:

```bash
php benchmarks/tiktok_wins_report.php --out-dir=benchmarks/.tiktok_sweep_YYYYmmdd_HHMMSS
```

Quick latest winner banner:

```bash
bash benchmarks/tiktok_latest_wins.sh
```

Outputs:

- `tik(speed)` winner = minimum `zip_seconds`
- `tok(bytes)` winner = minimum `fzc_bytes`
- `balanced(<=1% bytes)` winner = fastest near-byte-optimal row
- `wins_banner.txt` with one-line summary: `WINNER tik: ... | tok: ...`

**`--large` without `--bench-profile`:** `run_benchmarks.php` applies **large-balanced** tier defaults (Arc-friendly caps on samples like **`test_files133_sample`**). Use **`--bench-profile=large-fast`** or **`bash benchmarks/run_large_corpus_speed_push.sh`** for minimum wall time; **`large-bytes`** for wider native compare caps.

**Outer-predict probe cap:** on **`test_files133_sample`**, **`FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES`** **4096–1 MiB** keeps **`fzc_bytes`** **8 435 136** (arc). **2 MiB** can skew the outer tournament (**zstd** ~11.1 MiB in one retry — do not raise probe cap for this slice).

**Full `test_files133` profile plateau (2026-05-18):** **`large-fast`** vs **`large-bytes`** — identical **`fzc_bytes`** **60 411 616**, outer **zstd**; only **`zip_seconds`** differs (~263 vs ~196). Compare JSON: **`php benchmarks/compare_bench_json_cases.php benchmarks/.silesia133_speed_push.json benchmarks/.silesia133_largebytes.json`**. Single-file **dickens** (`test_files108`): **2 222 401** B, outer **arc**, ~22 s with **`--large`**.

## Census-gated gzip-fast (auto mode)

When **`FRACTAL_ZIP_FOLDER_GZIP_FAST`** is unset and raw ≥ **128 MiB**, auto gzip-fast is **skipped** if **`folder_bundle_census_from_dir`** reports **`textish_ratio` ≥ `FRACTAL_ZIP_FOLDER_CENSUS_GZIP_FAST_MAX_TEXTISH`** (default **0.35**). Extensionless members count as textish unless **`FRACTAL_ZIP_FOLDER_CENSUS_EXTLESS_AS_TEXTISH=0`** (Mahoney **`test_files133`** basenames). Explicit **`FRACTAL_ZIP_FOLDER_GZIP_FAST=1`** from the bench driver is unchanged unless **`FRACTAL_ZIP_FOLDER_CENSUS_GZIP_FAST_GATE_FORCE=1`**. Smoke: **`php benchmarks/smoke_folder_census_gzip_fast_gate.php`**.

**`FRACTAL_ZIP_FOLDER_LOW_TEXTISH_SKIP_RUN_GRAMMAR=1`** (with **`FRACTAL_ZIP_SPEED=1`**): skip run-grammar inner collection when **`textish_ratio` < `FRACTAL_ZIP_FOLDER_LOW_TEXTISH_THRESHOLD`** (default **0.12**); scheduling only.

**Guard:** **`php benchmarks/guard_test_files133_sample_bytes_and_time.php`** — pins **`fzc_bytes=8435136`**, **`sum(zip_seconds) ≤ 420`** on **`--large`** runs.
