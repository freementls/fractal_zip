# Bytes-win log (for GitHub / docs)

**Purpose:** one-by-one benchmark passes with **min-ext** enabled. A **bytes win** means `.fz` is **≤** the smallest of: gzip-9 tarball, 7z directory archive, and **min-ext** (arc / zstd / brotli / xz tournament).

**Excluded from this table:** any corpus whose name ends in `_sample` (tuning slices only).

**Wall time / orchestration:** **`benchmarks/LARGE_CORPUS_SPEED.md`** and **`benchmarks/PARALLELISM.md`** cover **`--jobs`**, compressor threading env, **`--bench-profile`** (including **`large-bytes`** and **`bash benchmarks/run_large_corpus_bytes_push.sh`**), and gzip-fast vs **`--large`**. On bench JSON, **`php benchmarks/report_bytes_wins.php --compress-time-audit`** lists rows where **`zip_seconds`** exceeds the fastest baseline compressor. **JSON artifacts:** default **`benchmarks/.last_bench.json`** is **gitignored**; use **`--out-json=`** for a path you archive or attach (**`LARGE_CORPUS_SPEED.md`**, JSON machine output).

## Measurement protocol (one-by-one, full fractal)

For rows intended for public “we win on bytes” documentation:

1. **Always pass `--large`** so the driver does **not** force `FRACTAL_ZIP_FOLDER_GZIP_FAST=1` on heavy-list corpora. Without `--large`, raw trees **≥ `FRACTAL_ZIP_LARGE_FOLDER_FAST_BYTES`** (default 128 MiB, matching library auto when unset) can take the streaming gzip-fast path and **inflate `.fz` vs min-ext** — that is **not** full fractal_zip for ratio claims.

2. **Raise PHP memory** — full unified-stream encoding can build a large inner in RAM. Example:
   ```bash
   FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G php benchmarks/run_benchmarks.php --only=<corpus> --large --json --no-case-timeout
   ```
   A **1 GiB** `memory_limit` can **OOM** on large trees (observed on the full `test_files55` tree during `encode_container_payload`).

3. **Wall time:** full fractal on **~200 MiB** raw trees may run for **many hours** (outer tournament includes **zstd** at high levels on large inners). Plan overnight runs; do not assume the default 20 s case timeout — use **`--no-case-timeout`** (or a large `--case-timeout=`).

4. **`verify_fzc_bytes_winner_each_corpus.php`** (real runs, not **`--dry-run`**): invokes **`run_benchmarks.php`** with **`--large`** when **`bench_corpora_should_pass_large_to_run_benchmarks()`** is true (heavy-folder gzip-fast list + raw ≥ **128 MiB**), matching item **1** for trees such as **`test_files133`**.

5. **JSON `verify_ok`:** when verification runs, **`true`** / **`false`** match strict SHA1 tree compare; **`--no-verify`** leaves **`verify_ok`** and **`verify_mismatch_files`** as **`null`** in **`--json`** output (skipped), not false / zero — re-run **with** verify before pasting a **`verify_ok`** cell into this table.

**Baseline command (copy-paste):**

```bash
FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G php benchmarks/run_benchmarks.php --only=<corpus> --large --json --no-case-timeout
```

Optional: add **`--bench-profile=large-balanced`** or **`large-bytes`** for tiered native-compare caps (see **`benchmarks/LARGE_CORPUS_SPEED.md`** / **`bash benchmarks/run_large_corpus_bytes_push.sh`**). When you paste a row, include JSON top-level **`bench_profile`** (`null` means no **`--bench-profile`** was passed). Do **not** pass **`--no-best-ext`**. After the run, paste JSON `cases[0]` into the table below.

| Corpus | Raw (B) | gzip9 | 7z | ext B | ext winner | .fz B | Bytes win (fzc ≤ best baseline)? | verify_ok | Notes |
|--------|--------:|------:|---:|------:|------------|-------:|:--------------------------------:|:---------:|-------|
| test_files2 | 1 100 | 135 | 291 | 251 | brotli | 122 | yes | yes | `--large` optional; raw &lt; 48 MiB |
| test_files4 | 85 | 113 | 244 | 201 | zstd | 104 | yes | yes | same |
| test_files10 | 29 076 | 274 | 330 | 229 | zstd | 147 | yes | yes | same |
| test_files11 | 29 076 | 280 | 340 | 247 | brotli | 154 | yes | yes | same |
| test_files13 | 62 870 | 7 789 | 7 199 | 6 291 | brotli | 6 207 | yes | yes | same |
| test_files28 | 9 498 | 1 102 | 1 115 | 992 | brotli | 921 | yes | yes | same |
| test_files29 | 1 277 926 | 7 970 | 7 726 | 277 | brotli | 207 | yes | yes | same |
| test_files35 | 4 149 414 | 1 254 372 | 976 020 | 976 628 | xz | 797 611 | yes | yes | Re-verify with `--large` for strict protocol (heavy name, raw &lt; 48 MiB → usually unchanged) |
| test_files49 | 85 083 | 23 821 | 21 849 | 20 393 | brotli | 20 336 | yes | yes | |
| test_files52 | 380 | 766 | 526 | 582 | arc | 92 | yes | yes | gzip tarball &gt; raw (tiny corpus) |
| test_files53 | 940 298 | 121 338 | 93 727 | 80 997 | arc | 80 820 | yes | yes | |
| test_files55_sample | 25 630 833 | 7 763 909 | 2 191 775 | 2 161 817 | brotli | 2 158 962 | yes | yes | **`--large` + 2G+** (4G safe). Every-8th-file subset of full `test_files55` (~25.6 MiB raw); JSON `benchmarks/.test_files55_large_eighth.json`. Rebuild: `php benchmarks/build_test_files55_eighth.php`. Unified stream + brotli Q11 on large text-like inners. |
| test_files55 | 209 994 426 | 64 533 561 | 11 775 260 | 11 700 238 | arc | 11 647 351 | yes | yes | Full StevieGee HTML tree (~200 MiB raw). **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G` `php benchmarks/run_benchmarks.php --only=test_files55 --large --no-case-timeout --json`** (2026-04-17). **`zip_seconds` ~1497**; **`outer_codec` `arc`**; unified stream. Textlike-huge outer gate default floor: **`FRACTAL_ZIP_BROTLI_TEXTLIKE_FZB_FULL_MIN_FLOOR_BYTES`** (see `fractal_zip.php` `adaptive_compress` docblock). |
| test_files60 | 6 767 183 | 6 749 927 | 6 718 841 | 6 717 584 | zstd | 6 717 435 | yes | yes | Two FLACs + sidecars; fast FLAC iteration corpus (default **`FRACTAL_ZIP_FLACPAC` off** = bit-exact `.flac`). `php benchmarks/run_benchmarks.php --only=test_files60 --no-case-timeout --json` (2026-04-16). Set **`FRACTAL_ZIP_FLACPAC=1`** to benchmark FZCD transmedia (may shrink further; bytes may differ from sources). `--large` optional. |
| test_files56 | 6 242 492 | 5 992 745 | 5 912 958 | 5 865 028 | brotli | 4 636 816 | yes | no | Full SC2 MoPAQ tree (~90 `.SC2Replay`, ~6.0 MiB raw). Snapshot (2026-04-24): **`fzc_bytes` 4 636 816** with **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=2G`** and `php benchmarks/run_benchmarks.php --only=test_files56 --no-case-timeout --json` — **same size with or without `--large`** here because raw **&lt;** the bench heavy-folder gzip-fast threshold (`benchHeavyFolderGzipFastMinRawBytes`, default 128 MiB, matching library auto when unset), so **`folder_gzip_fast` stays false** and **unified stream** runs. **`zip_seconds` ~506**; **`outer_codec` `xz`**; **`verify_ok` false** with **`verify_mismatch_files` 0** (SHA tree vs **semantic** MPQ rewrites). Older “min-ext wins” rows were **stale** (different revision/tooling). On **≥128 MiB** raw heavy-list dirs, default bench can still force gzip-fast without `--large` — use **`--large`** there for ratio work. |
| test_files56_sample | 1 025 429 | 1 014 361 | 1 013 176 | 1 007 570 | brotli | 884 184 | yes | yes | Nine flat `.SC2Replay` files (~1.0 MiB raw). Fast MoPAQ / MPQ PAC loop vs full `test_files56`. `php benchmarks/run_benchmarks.php --only=test_files56_sample --no-case-timeout --json` (2026-04-24). |
| test_files57 | 9 566 479 | 1 425 697 | 873 860 | 628 596 | arc | 628 596 | yes | yes | PHP/HTML mirror slice (~9.1 MiB raw). **Native Arc passthrough:** when the benchmark-style **`arc a -m5 -ep1 -y`** tree archive is smaller than the unified fractal `.fz` body, the container stores **raw FreeArc bytes** (same size as min-ext Arc); `open_container` uses **`arc x`** after distinguishing legacy single-member **`i`** outers (`freearc_try_extract_native_folder_arc_to_directory` in `fractal_zip.php`). Legacy **`FZFA\x01`**-prefixed files still decode. **Smoke:** `php benchmarks/fz_arc_native_roundtrip_smoke.php`. Re-bench: `php benchmarks/run_benchmarks.php --only=test_files57 --no-case-timeout --json`. Native Arc probe uses the same **`fzarcbench_*`** temp dir names as `benchArcFolderArchive` so byte sizes align with the min-ext column. |
| test_files114 | 1 029 744 | 210 387 | 50 528 | 16 822 | zpaq_raw | 16 822 | yes | yes | Single **`kennedy.xls`** (~1.0 MiB OLE). `.fz` **ties** min-ext **raw zpaq** tree bytes. Remove stray **`/.~lock.*#`** sidecars before benching (they inflate **Raw**). `FRACTAL_ZIP_BENCH_MEMORY_LIMIT=2G php benchmarks/run_benchmarks.php --only=test_files114 --no-case-timeout --json` (2026-05-12). |
| test_files133 | 211 938 580 | 67 643 440 | 48 688 149 | 39 865 338 | zpaq | 39 112 720 | yes | yes | **FZHM** per-member **zpaq** passthrough (**store** outer). **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G` `php benchmarks/run_benchmarks.php --only=test_files133 --large --no-case-timeout --json --out-json=benchmarks/.silesia133_pmb.json`** (2026-05-19): **`verify_ok` yes**, **`zip_seconds` ~7255**, **`folder_unified_stream` false**. Beats prior monolithic **60 411 616** (**zstd**) and min-ext folder **xz** **48 760 032**. JSON **`bench_profile` `large-balanced`**. |
| test_files78 | 67 633 896 | 67 432 056 | 67 408 375 | 67 182 345 | zpaq_raw | **39 112 813** | **yes** | **yes** | **FZHM store** (peel-first). **`benchmarks/.silesia78_peel_pmb.json`** (2026-05-20): **`fzc_bytes` 39 112 813** ≈ **133** **39 112 720**; **`verify_ok` true** (semantic `.zip` + peel cap fix). **`folder_unified_stream` false**. Re-bench: **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G benchmarks/run_bench_with_cleanup.sh --only=test_files78 --large --no-case-timeout --json`**. |
| test_files69 | — | — | — | — | — | — | — | — | ≤5 MiB stratified slice (see `benchmarks/build_test_files69.php`): flat text, HTML, nested HTML, phpinfo, raster mix, FLAC+m3u, SC2 nested path, gzip peel dupes. |

## Folder verify repro (strict SHA1 tree)

Corpus names ending in **`_sample`** stay **out of the main table** above, but you can still bisect **bench `verify_ok`** vs strict tree replay:

- **`benchmarks/repro_folder_zip_verify.php`** copies up to **`--max-files=N`** smallest regular files (size, then path), runs **`zip_folder` + `open_container`**, and diffs SHA1 trees (skips **`.fz`**). Set **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT`** (script defaults **`memory_limit`** to **2 GiB** if unset) for large slices. CI smoke: **`php benchmarks/smoke_repro_folder_zip_roundtrip.php`** (synthetic `plain.txt` + **`twomem.zip`**).
- **`test_files58_sample`:** smallest **30** files ⇒ **0** mismatches; smallest **500** ⇒ hundreds of **`hash_diff`** rows, overwhelmingly **nested `.zip`** under **`isisunveiled/`** — semantic ZIP modes **7** / **18** rebuilt archives with **ZipArchive** can differ from on-disk bytes while still extracting the same payloads. **`choose_best_literal_bundle_transform`** now **coerces** to raw mode **0** when decode output does not match the original member bytes (set **`FRACTAL_ZIP_LITERAL_DISABLE_DISK_ROUNDTRIP_COERCE=1`** only for diagnostics). Full-tree ratio impact TBD.
- **`test_files59_sample`:** bench JSON has reported a **single** `verify_mismatch_file` on some runs (often FLAC-adjacent); repro needs **high RAM** (try **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G`**) and may take a long wall time on the full copied tree.

## Updating this file

After each `--only=<corpus> --large` run, paste JSON `cases[0]` sizes into the row. Set **Bytes win** to **yes** iff  
`fzc_bytes <= min(gzip9_bundle_bytes, seven_zip_folder_bytes, best_ext_folder_bytes)`  
(using `best_ext_folder_bytes` only when present).

Last updated: Mahoney **x12** per-file (**2026-05-19**, **`.silesia12_perfile_pmb.json`**): **12/12 tie** min-ext **zpaq**, **sum_fzc=39 112 599**; one-folder **`test_files133`** FZHM **39 112 720** (**verify_ok** yes, **`.silesia133_pmb.json`**).
