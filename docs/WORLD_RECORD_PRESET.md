# enwik8 world-record preset

Preset for hours-scale, bytes-first compression of [`test_files109/enwik8`](../benchmarks/build_test_files_squash_corpora.php) (100 000 000 B Wikipedia XML). Primary score: **min(`.fz`, best native passthrough)** — not `.fz` alone when zpaq/7z/arc wins on wire bytes.

## Reference sizes

| Source | Compressed | Notes |
|--------|------------|--------|
| [Hutter Prize record](http://prize.hutter1.net/) (phda9, 2017) | **15 284 944 B total** | official enwik8 L |
| **Stretch (enwik9 prep)** | **14 623 723 B total S** | beat 14.6 MiB under [hardware rules](HUTTER_PRIZE_COMPLIANCE.md) |
| Our `large-balanced` + `FRACTAL_ZIP_SPEED=1` baseline | ~19 625 015 B (`zpaq_raw`) | see `benchmarks/.gt2mb_batch8_enwik8.json` |

Realistic first milestone: beat **19.6 MiB** zpaq passthrough; Hutter **15.28 MiB** likely needs inner statistical modeling beyond outer tuning.

## Core technique: Wikipedia entry sort

Raw enwik8 is a **single extensionless member** → FZCE flat wire (no multi-member path-order shuffle). With **`FRACTAL_ZIP_ENWIK_ENTRY_SORT=1`**, encode:

1. Split on `<page>…</page>` and parse `<title>…</title>`
2. Sort pages alphabetically by normalized title (stable tie-break on original index)
3. Pack as a **virtual multi-member folder** (`pages/…`) through normal `zip_folder`
4. Append **FZEP** trailer on `.fz` with header/footer bytes + page permutation map
5. On extract, peel FZEP and reassemble **bit-exact** original enwik8

Implementation: [`fractal_zip_enwik.php`](../fractal_zip_enwik.php). The official enwik8 file is **exactly 100 000 000 B** and ends mid-page (no closing `</mediawiki>`); the splitter uses `<page>` boundary markers and preserves the truncated tail bit-exactly. **FZEP v2** stores per-page byte lengths so chunked members (default 64 pages/member) restore without relying on `</page>` regex; the trailer is stripped before outer decompress on extract.

## Preset env (via `bench_world_record_env.php`)

Extends **ultra** with 128 MiB native-compare caps, unlimited multipass wall, `FRACTAL_ZIP_ENWIK_ENTRY_SORT=1`, pipeline reorder, default **`FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=96`** (encode-only grid: **19 594 333 B** fzc at pp96 vs **19 620 079 B** at pp48 vs **22 043 397 B** at pp64), unified stream on, and **`bench_world_record_apply_enwik_tuned()`** (called automatically; override vars before `bench_world_record_apply_env_defaults()`).

| Variable | Value | Role |
|----------|-------|------|
| `FRACTAL_ZIP_SPEED` | `0` | no fast-preset brotli/multipass clamps |
| `FRACTAL_ZIP_ULTRA` | `1` | full outer tournament |
| `FRACTAL_ZIP_WHOLE_STREAM_FZWS_MAX_BYTES` | `0` | 100 MiB FZWS eligible |
| `FRACTAL_ZIP_FOLDER_NATIVE_*_MAX_RAW_BYTES` | `134217728` | full native zpaq/7z/brotli compare |
| `FRACTAL_ZIP_ZPAQ_OUTER_HIGH_METHOD_MAX_INNER_BYTES` | `134217728` | high zpaq methods on full inner |
| `FRACTAL_ZIP_ZPAQ_NATIVE_FULL_SWEEP_MAX_RAW_BYTES` | `134217728` | full `-method` ladder on native folder compare (default 4 MiB) |
| `FRACTAL_ZIP_ZPAQ_OUTER_SWEEP` | `1` | multi-method zpaq outer at any inner size |
| `FRACTAL_ZIP_ZPAQ_OUTER_AUTO_SWEEP_MAX_INNER_BYTES` | `134217728` | auto-sweep cap raised for 100 MiB inners |
| `FRACTAL_ZIP_MAX_FRACTAL_MULTIPASS_WALL_SECONDS` | `0` | unlimited multipass |
| `FRACTAL_ZIP_ENWIK_ENTRY_SORT` | `1` | entry split + title sort |
| `FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER` | `96` | virtual member chunk size (~129 members on enwik8; pp96 grid winner) |
| `FRACTAL_ZIP_TEXT_INNER` | `1` | fztx mono mi_reorder split-inner (384p wire −6.6 KiB; full **19,207,083 B**) |
| `FRACTAL_ZIP_TEXT_INNER_FORMAT` | `fztx` | single `.inner` member per chunk (mono = one member for full corpus) |
| `FRACTAL_ZIP_TEXT_INNER_MONO` | `1` | one virtual member for entire sorted page list |
| `FRACTAL_ZIP_TEXT_INNER_LAYOUT` | `mi_reorder` | mutual-information page order inside each chunk |
| `FRACTAL_ZIP_TEXT_INNER_PREPROCESS` | `none` | dict_nncp regresses wire at 384p — keep none on production |
| `FRACTAL_ZIP_TEXT_INNER_STACK` | `none` | lab `zpaq9_brotli11` needs `FRACTAL_ZIP_STACKED_OUTER=1` passthrough |
| `FRACTAL_ZIP_FOLDER_UNIFIED_STREAM` | `1` | merged FZB4 inner + adaptive outer (stream-first) |
| `FRACTAL_ZIP_FOLDER_GZIP_FAST` | `0` | skip deflate-only fast path (loses multi-member path-order) |
| `FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES` | `128` | more FZBM concat-order probes on ~200 members |
| `FRACTAL_ZIP_ENWIK_FZBM_PATH_ORDER_RANDOM_MAX_MEMBERS` | `512` | allow gzip-1 prefilter shuffles beyond n≤16 for chunk paths |
| `FRACTAL_ZIP_PATH_ORDER_LGWIN_SWEEP_MAX_CAND` | `32` | Brotli w=20/22/24 on textlike path-order scoring |
| `FRACTAL_ZIP_PIPELINE_REORDER` / `_EXT` | `1` | pipeline reorder for text-heavy virtual folder |
| `FRACTAL_ZIP_MAX_ZPAQ_INNER_BYTES` | `0` | allow zpaq outer on full 100 MiB inner |
| `FRACTAL_ZIP_PAQ_NATIVE_COMPARE` | `1` | native PAQ-class passthrough when phda9/paq8px/cmix installed |
| `FRACTAL_ZIP_PAQ_NATIVE_MAX_RAW_BYTES` | `134217728` | PAQ compare cap |
| `FRACTAL_ZIP_PAQ_SWEEP` | `1` | try all discovered PAQ tools, smallest wins |
| `FRACTAL_ZIP_WEB_REF` | `0` | **disabled** on world-record encode; track web refs separately |
| `FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE` | `1` | after sorted-folder encode, try PAQ on **raw** `enwik8`; FZpq+FZEP v3 if smaller |
| `FRACTAL_ZIP_PAQ_phda9` | `tools/phda9/phda9` when present | Hutter-class raw-order compare |

**Dual-order headline:** `best_any_dual` = min(`.fz`, `best_ext`, raw squash `phda9`/`paq8px` on single-file tree). See `php benchmarks/compare_enwik8_world_record.php`.

**Web-ref track (not used for world-record score):**

```bash
php benchmarks/bench_enwik8_web_ref_estimate.php   # fast heuristic
bash benchmarks/run_enwik8_web_ref_track.sh          # full encode with WEB_REF=1
php benchmarks/compare_enwik8_four_way.php           # fzc / zpaq / Hutter / web-ref table
```

**Encode-path optimizations (code, not env):** virtual folder build streams page slices from the 100 MiB blob (no duplicate page bodies in RAM). When entry sort is active, `zip_folder` skips gzip-fast, FZB4 store-only, and FS-profile early gates that would bypass multi-member benefits. FZBM path-order adds enwik chunk order heuristics (`pages/chunk_*.xml`).

Optional add-on: `--auto-tune` → `FRACTAL_ZIP_AUTO_TUNE=1`. Optional A/B: `FRACTAL_ZIP_FORCE_OUTER=zpaq`, `FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER=32|64|96` (member count vs path-order budget; see `bash benchmarks/run_enwik8_experiment_grid.sh`).

**High tier** (`bench_world_record_apply_high_env`) is **merged into the default world-record preset** after pp96_high A/B: same **19 594 333 B** fzc, ~**22 min** encode vs ~67 min at old caps. Standalone re-run: `php benchmarks/run_enwik8_encode_high.php`. See [`docs/WORLD_RECORD_EXTERNAL_INSIGHTS.md`](WORLD_RECORD_EXTERNAL_INSIGHTS.md).

## Parallelism (auto-detected on CLI)

**`fractal_zip_parallel_runtime.php`** detects CPUs, pcntl, and the Rust substring scorer, then enables fork pools, peel jobs, FZBM parallel, zpaq/7z/zstd MT, and inner frontier jobs when env is unset. No flags required for world-record encodes:

```bash
bash tools/gpu_substring/build.sh
bash benchmarks/run_enwik8_world_record.sh
php -d memory_limit=4096M benchmarks/run_enwik8_pp96_refresh.php --text-inner-promotion
```

Byte probes set `FRACTAL_ZIP_PARALLEL_PROBE=1` (pp96 refresh without promotion). Override with `FRACTAL_ZIP_PARALLEL_OFF=1` or per-knob env.

See **`docs/FRACTAL_ZIP_PARALLELIZATION.md`** for hard-to-parallelize binaries vs auto-enabled paths.

## How to run

Build corpus (once):

```bash
php benchmarks/build_test_files_squash_corpora.php
```

Benchmark (hours; no default case timeout):

```bash
bash benchmarks/run_enwik8_world_record.sh
```

Log (when run via nohup): `benchmarks/logs/enwik8_world_record.log` for stderr progress; JSON at `benchmarks/.enwik8_world_record.json` when complete.

Monitor a background run:

```bash
tail -f benchmarks/logs/enwik8_world_record.log
pgrep -af 'run_benchmarks.php.*test_files109'
```

Or manually:

```bash
FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G \
  php benchmarks/run_benchmarks.php \
    --only=test_files109 \
    --bench-profile=world-record \
    --no-case-timeout \
    --json \
    --out-json=benchmarks/.enwik8_world_record.json
```

CLI zip with same env:

```bash
php fractal_zip_cli.php zip --world-record test_files109
```

Aliases for `--bench-profile=`: `world-record`, `hutter`, `ltcb`, `enwik8`.

## Interpreting results

- **`fzc_bytes`**: fractal `.fz` size
- **`best_ext_folder_bytes`** / **`best_ext_winner`**: smallest native folder archive (often `zpaq_raw`)
- **`best_any`**: min of the above — headline metric for this preset

When entry sort is active, expect **`member_count` ≫ 1** and path-order / FZBM probes in verbose traces (`FRACTAL_ZIP_CLI_VERBOSE=1`).

After the JSON is written:

```bash
php benchmarks/compare_enwik8_world_record.php
```

## Prerequisites

- `zpaq` or `zpaqfranz` on `PATH` for native compare (`FRACTAL_ZIP_BENCH_ZPAQ_THREADS=0` uses all cores on zpaqfranz)
- Optional PAQ tools on `PATH`: `phda9`, `paq8px`/`paq8pxd`, `cmix` (see `php benchmarks/smoke_paq_tools.php`)
- Writable RAM (~4 G+ recommended for 100 MiB inner trials)
- `--no-case-timeout` (preset sets null timeout unless you pass `--case-timeout=`)

## Smokes (no full enwik8 in CI)

```bash
php benchmarks/smoke_world_record_env.php
php tests/enwik_entry_sort_roundtrip_smoke.php
php benchmarks/smoke_enwik_path_order.php
```
