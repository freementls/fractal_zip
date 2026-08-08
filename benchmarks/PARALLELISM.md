# Parallel benchmark and tooling (fractal_zip)

This document tracks **orchestration-level** parallelism (fork pools, `--jobs`) and **optional compressor threading** inside external tool invocations. The core `fractal_zip` PHP code path remains single-threaded; multithreading is delegated to **7-Zip**, **xz**, **zpaq** (e.g. zpaqfranz), or a **brotli** binary that accepts extra flags—see the table below.

## What is parallel today

### Orchestration (many processes)

| Tool | Flag | Requirement | Notes |
|------|------|-------------|--------|
| `perf_test.php` | `--jobs=N` | Linux, `pcntl_fork` | Encodes multiple **corpora** concurrently; `zip_folder_sum` = Σ per-row `zip_s`; lowers wall clock. Disables XHProf when `N>1`. |
| `run_benchmarks.php` | `--jobs=N` | Linux, `pcntl_fork`, **`--repeat=1`** | Runs up to **N** full benchmark cases at once (fork pool). Summaries and JSON `totals` match sequential semantics when each row matches; baseline cache merges are **per label** (commutative). |

Tiered **bytes vs wall** bench profiles (**`large-fast`**, **`large-balanced`**, **`large-bytes`**) and the **`bash benchmarks/run_large_corpus_bytes_push.sh`** wrapper: **`benchmarks/LARGE_CORPUS_SPEED.md`**.

### Slow outer “wave” inside one `zip_folder`

| Mechanism | Env | Requirement | Notes |
|-----------|-----|-------------|-------|
| **`adaptive_compress` slow codec overlap** | **`FRACTAL_ZIP_PARALLEL_SLOW_OUTER_WAVE`** | Linux, `pcntl_fork` | Declared in **`fractal_zip_encode_pipeline.php`** header: when **≥2** of **7z / arc / zpaq** remain, **unset (default) ⇒ on** under PHP CLI — one fork per remaining family, merged in deterministic order (`parallel_slow_outer_wave_trials`). **`0`** / **`off`** ⇒ fully sequential slow passes. Layered prediction often leaves only one slow lane, so the wave may not fork. |

### Compressor threading (env vars)

Set these in the shell before `php fractal_zip.php`, `perf_test.php`, or `benchmarks/run_benchmarks.php`. Values are parsed in `fractal_zip.php` unless noted.

| Variable | Effect | Typical use |
|----------|--------|-------------|
| `FRACTAL_ZIP_7Z_MMT` | Adds **7-Zip** `-mmt=…` on **`a`** (pack) and **`x`** (extract); if unset, **`FRACTAL_ZIP_BENCH_7Z_MMT`** is read. | `on` / `all` / `auto` → `-mmt=on`; digits → `-mmt=N`; `off` / `false` / `no` → `-mmt=off`. |
| `FRACTAL_ZIP_ZSTD_THREADS` | **`library_zstd_thread_shell_fragment_for_exec()`** on outer **zstd** compress (`outer_zstd_blob`) and decompress (`outer_zstd_decompress_pipe`). Independent of **`FRACTAL_ZIP_BENCH_ZSTD_THREADS`**. | Unset = **`-T0`**; `off` / `false` / `no` = omit; digits → **`-TN`**. |
| `FRACTAL_ZIP_XZ_THREADS` | **`library_xz_compress_thread_shell_fragment_for_exec()`** on outer **xz** compress only. Same **`-T`** argv tokens are injected into **literal_pac** **lzma(1)** raw-stream smaller trials when `fractal_zip` is loaded (`fractal_zip_literal_pac_try_lzma_smaller`). | Unset = omit (keeps older single-thread compressed size baselines); set `on`/`0`/… for **`-T`** (output may differ from ST xz). |
| `FRACTAL_ZIP_XZ_DECOMPRESS_THREADS` | **`library_xz_decompress_thread_shell_fragment_for_exec()`** on **`xz -d`**: streaming decode, `.fz` xz container path; also **`lzma -dc`** literal trials with **`FRACTAL_ZIP_XZ_*`** when loaded. If unset, **`FRACTAL_ZIP_XZ_THREADS`** is read; if still unset ⇒ **`-T0`**. | `off` / `false` / `no` = omit **`-T`**. |
| `FRACTAL_ZIP_BENCH_XZ_THREADS` | `bench_xz_thread_shell_fragment_for_exec()` on **`xz -9 … -c`** (min-ext compress) and **`xz … -d -c`** (min-ext `ext ex` decompress when the winner is xz). | Empty = omit `-T` (xz default, often ST); `0`, `auto`, `all`, `on` → `-T0`; positive integer → `-TN`. |
| `FRACTAL_ZIP_BENCH_ZSTD_THREADS` | **`zstd -T`** on compress **and** decompress in min-ext winner paths (`tar \| zstd`, `zstd -d \| tar`). Also `bench_fzbd_vs_baselines.php` compress. | Unset = **`-T0`**. `off` / `false` / `no` = omit `-T`. Digits → `-TN`. |
| `FRACTAL_ZIP_BENCH_BZIP3_JOBS` | **`bzip3 -j`** on min-ext **`tar \| bzip3`** and **`bzip3 -d \| tar`** when bzip3 wins. | Unset = omit (single worker). `auto` / `on` / `all` → **`-j0`**. Digits (incl. `0`) → **`-jN`**. Can change compressed bytes vs default; refresh baseline cache if needed. |
| `FRACTAL_ZIP_ARC_MT` / `FRACTAL_ZIP_BENCH_ARC_MT` | **`library_arc_compress_mt_*()`** in `fractal_zip.php` — min-ext **arc** folder `arc a` / `arc x`, arc-winner `ext ex` **compress** + **extract** timing, **and** library FreeArc **compress** / **extract** (`outer_arc_blob…`, native folder `arc a` / `arc x`, container unwrap). | Read **ARC_MT** first, then **BENCH**; if both empty ⇒ omit. `auto` / `on` / `all` → **`-mt0`**. Digits → **`-mtN`**. `off` / `no` ⇒ omit. |
| `FRACTAL_ZIP_BENCH_LRZIP_P` | **`lrzip -p`** with **`FRACTAL_ZIP_BENCH_LRZIP=1`**: min-ext compress, `ext ex` decompress. | Unset = **`-p0`**. `off` / `no` = omit. Digits → **`-pN`**. |
| `FRACTAL_ZIP_LITERALPAC_PIGZ_P` | **`pigz -p`** in **literal_pac** `try_gzip_smaller` when the pigz engine runs (after engine selection). Also the default for **`FRACTAL_ZIP_BENCH_PIGZ_P`** when unset. | Unset = omit. `auto` / `on` / `all` → **`nproc`** (fallback **8**). Digits → **`-pN`**. `off` / `no` = omit. |
| `FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ` | When `1` / `on` / `true` / `yes` and **pigz** is on `PATH`, `run_benchmarks` **gzip9** baseline + **gz ex** use **`pigz -z`** (zlib, same role as PHP `gzcompress`) for compress + timed decompress, with **`FRACTAL_ZIP_BENCH_PIGZ_P`** (else **`FRACTAL_ZIP_LITERALPAC_PIGZ_P`**) as **`-p`**. Also applies to **`benchmarks/bench_fzbd_vs_baselines.php`** gzip9 bundle byte count (no cache key). | No default: off (PHP zlib / `deflate_*`). Main bench: `gzip_baseline_key` in baseline cache (**schema v3**). If **pigz** is missing, falls back to PHP. |
| `FRACTAL_ZIP_BENCH_PIGZ_P` | Optional **`-p`** only for **`FRACTAL_ZIP_BENCH_GZIP_USE_PIGZ`**; if unset, **`FRACTAL_ZIP_LITERALPAC_PIGZ_P`** is read. | Same token rules as literal_pac **`PIGZ_P`** (see row above). |
| `FRACTAL_ZIP_ZPAQ_THREADS` / `FRACTAL_ZIP_BENCH_ZPAQ_THREADS` | Inserts **zpaq** `-threads N` after the executable for **`add`** and **`x`** (outer encode + decode paths; zpaqfranz: same as `-tN` in help). Read **ZPAQ_THREADS** first, then **BENCH**. When both **unset**, **`benchmarks/run_benchmarks.php`** and **`perf_test.php`** default **`FRACTAL_ZIP_BENCH_ZPAQ_THREADS=0`** so benchmarks pass **`-threads 0`** (all cores) without relying on franz banner probe; **library-only** `php fractal_zip.php` still uses the banner probe / omit for stock. | `auto` / `on` / `all` / `0` → `-threads 0`; digit `N` → `-threads N`. `off` / `no` / `stock` / `st` / `false` → omit (for stock or single-thread runs). |
| `FRACTAL_ZIP_BROTLI_EXTRA_ARGS` | Whitespace-split tokens after the brotli exe, before `-c` (cache key includes extras); if unset, **`FRACTAL_ZIP_BENCH_BROTLI_EXTRA_ARGS`**. Also fed as raw argv to **literal_pac** brotli Q11 smaller-trial (before **`-q`**) via `brotli_compress_extra_argv_proc_tokens()`. | Reference **brotli** CLI is single-threaded; use a **wrapper** or alternate binary and pass thread/worker flags here. |
| `FRACTAL_ZIP_LZ4_THREADS` | `library_lz4_thread_shell_fragment_for_exec()` on **literal_pac** `lz4` recompress (`-T#`; lz4: **0** = auto). | Unset = omit. `0` / `auto` / `on` / `all` → **`-T0`**. Digits → **`-TN`**. |
| `FRACTAL_ZIP_LITERALPAC_PBZIP2_P` | **literal_pac** `.bz2` smaller-trial recompress: when set to a “use **pbzip2**” value and **pbzip2** is on `PATH`, compress/decompress use **pbzip2** with **`-p`**; otherwise **bzip2** (default). | Unset/empty ⇒ **bzip2** only. `off` / `0` / `bzip2` / `st` ⇒ **bzip2**. `auto` / `on` / `all` → **`-p`** from **nproc** (fallback 4). Digits → **`-pN`**. |
| `FRACTAL_ZIP_LITERALPAC_PLZIP_THREADS` | **literal_pac** LZIP-format smaller-trial recompress: when not `off`/`no`/`lzip`/`st` and **plzip** is on `PATH`, use **plzip** with **`-n`**; else **lzip**. | Unset ⇒ **lzip**. `default` / `plzip` ⇒ **plzip** with default thread count (omit **`-n`**). `auto` / `on` / `all` ⇒ **`-n`** = **nproc** (fallback 4). Digits ⇒ **`-nN`**. plzip output is standard lzip; ratio can differ slightly vs **lzip**. |

Benchmark **7z** folder columns (`sevenZipFolderCompressBenchmark`, recreate + **`7z x`** in `sevenZipFolderExtractSeconds`) and **`bench_fzbd_vs_baselines.php`** use **`FRACTAL_ZIP_7Z_MMT`** / **`FRACTAL_ZIP_BENCH_7Z_MMT`** (`seven_zip_mmt_shell_fragment_for_exec()`). **7z container open** (`fractal_zip` / `fractal_zip-undeep-unwrap.php`), **literal_pac** semantic 7z **add/x** use the same when `fractal_zip` is loaded (normal encode) or for unwrap (always). **Outer zstd/xz** and **zpaq `x`** use the **`FRACTAL_ZIP_*`** rows above.

**literal_pac** smaller trials when `fractal_zip` is loaded: **xz** / **lzma**(raw `.lzma`) / **zstd** / **lz4** use **`FRACTAL_ZIP_XZ_*`**, **`FRACTAL_ZIP_ZSTD_THREADS`**, **`FRACTAL_ZIP_LZ4_THREADS`**; **brotli** Q11 recompress uses **`brotli_compress_extra_argv_proc_tokens()`** (same env as **BROTLI_EXTRA_ARGS**). **pigz** trials honor **`FRACTAL_ZIP_LITERALPAC_PIGZ_P`**. **bzip2** / **pbzip2** bz2 recompress: **`FRACTAL_ZIP_LITERALPAC_PBZIP2_P`**. **lzip** / **plzip**: **`FRACTAL_ZIP_LITERALPAC_PLZIP_THREADS`**.

## `run_benchmarks.php --jobs=N`

- **Default** (`--jobs=1` or omitted): unchanged sequential driver (one corpus at a time).
- **`N>1`**: builds a queue of corpora that still need measurement (after phase-1 row cache hits in the parent). Workers are **`pcntl_fork`** children; each child runs `runOneCaseRepeatedWithOptionalTimeout` with **`baselineCacheHolder = null`** and a **`baselineCacheLookupOnly`** snapshot of the parent cache **at fork time**, so baselines are merged **only in the parent** (same as merging from returned rows in order).
- **`--repeat>1`**: parallel mode is **disabled**; a line on stderr explains that only single-repeat runs are supported.
- **Disk sweep**: With default `--case-disk-sweep` (sweep `benchmarks/.work` after **each** corpus), parallel mode **defers** the sweep until **after all** corpora finish, so workers do not delete each other’s work trees. A line on stderr documents this. Use `--no-case-disk-sweep` if you need to keep `.work` between benchmark runs regardless.

## Baseline and phase-1 caches

- **Baseline cache** (`benchmarks/.baseline_cache.json`): Lookups are **per corpus label**. Merging each finished row updates that label’s entry; order of completion does not change the final on-disk merge for a full successful run.
- **Phase-1 case cache**: Row merges are keyed by corpus + fingerprint; parent applies them in **default test order** in the finalize loop (same as sequential).

## `perf_test.php` (see also)

`perf_test.php --jobs=N` uses the same fork-pool idea for **wall time** on the lighter perf harness. JSON field `benchmark_config.parallel_jobs` records `N` when the pool is used.

## Web / PHP-FPM

Running many **fork** workers **inside** a long-lived FPM worker is usually a bad fit (memory, extension state, request timeouts). Preferred pattern for a future UI: **one encode per worker process** via a **job queue** (Redis, DB, etc.), or shell out to **`php benchmarks/run_benchmarks.php --only=…`** / **`php perf_test.php --only=…`** from a **CLI** or dedicated worker container. A future wiki section can describe an HTTP API that **enqueues** jobs instead of forking in the request.

## Future work (not implemented here)

Documented direction only:

- Parallel **try variants** (raw unpeel tier, segment length, transform branches) as **separate processes** with explicit merge rules so **best bytes** and determinism stay defined.
- Optional **CLI flag** to opt into experimental inner parallelism once call sites are isolated (env, temp paths, nondeterminism policy).

Large-corpus recipe (**profiles, gzip-fast vs `--large`, baseline refresh, sampling**, **gitignored default bench JSON vs `--out-json=`**): **`benchmarks/LARGE_CORPUS_SPEED.md`**.

## References in code

- `perf_test.php`: `perf_zip_cases_parallel_fork()`, `--jobs=`
- `run_benchmarks.php`: `bench_fork_pool_default_cases()`, `--jobs=`, `runOneCaseRepeatedWithOptionalTimeout(..., $baselineCacheLookupOnly)`
- **zpaq ST vs MT A/B (same corpus, two env passes):** `php benchmarks/bench_zpaq_threads_ab.php --corpus=test_files114` — compares `FRACTAL_ZIP_ZPAQ_THREADS=1` vs `=0` under `perf_test.php` (expect gains with **zpaqfranz** when zpaq dominates `zip_s`).
