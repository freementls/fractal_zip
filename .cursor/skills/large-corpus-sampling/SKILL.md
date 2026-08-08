---
name: large-corpus-sampling
description: >-
  When optimizing fractal_zip on huge test_files* corpora, build a stratified sample
  first (same content-type mix, spread across large files), tune on the sample, then
  validate on the full corpus. Use benchmarks/sample_large_corpus.php.
---

# Large corpus sampling (fast tuning loop)

## When to use

- A `test_files*` directory is **hundreds of MiB** or minutes per benchmark iteration.
- You need to try **env defaults, deflate levels, I/O chunk sizes**, or code changes repeatedly.

## Wall time + multi-corpus runs

- **`benchmarks/LARGE_CORPUS_SPEED.md`** — `--jobs`, compressor threading env block, **`--bench-profile=large-fast`** vs **`large-balanced`** vs **`large-bytes`** (wider native compare caps for cheap ratio on big merged inners), gzip-fast vs **`--large`**, baseline cache refresh. Wrapper: **`bash benchmarks/run_large_corpus_bytes_push.sh`**.
- **`benchmarks/PARALLELISM.md`** — full threading/orchestration table.
- After **`run_benchmarks.php --json`**, **`php benchmarks/report_bytes_wins.php --compress-time-audit`** surfaces rows where **`zip_seconds`** is slower than **`min(gzip9, seven_zip, best_ext)`** compress times.
- Default **`benchmarks/.last_bench.json`** is **gitignored**; use **`--out-json=`** for JSON you keep, diff, or pass to **`report_bytes_wins`** / guards (see **LARGE_CORPUS_SPEED.md**, JSON machine output).
- **`cases[]`** bench JSON may include **`folder_bundle_census`** (extension mass + **`textish_ratio`**); **`FRACTAL_ZIP_FOLDER_BUNDLE_CENSUS_STDERR=1`** / **`FRACTAL_ZIP_FOLDER_BUNDLE_SCHED_COST_BIAS=1`** — see **`fractal_zip.php`** class docblock. Human table: **`FRACTAL_ZIP_BENCH_SHOW_FOLDER_CENSUS=1`** → **`tx%`** column. Parity smoke: **`php benchmarks/smoke_folder_bundle_census_equiv.php`** (also **`tests/run_php_smokes.sh`** after undeep check). **`--repeat`**: merged rows use **`bench_folder_bundle_census_merge_into_aggregated_row`** in **`benchmarks/bench_folder_census.php`** when the first repeat omits census.

## Workflow

0. **FLAC / merged-folder micro-loop (seconds–minutes, not hundreds of MiB):** use tracked **`test_files60`** (two `.flac` + sidecars). Example: `php benchmarks/run_benchmarks.php --only=test_files60 --no-verify --no-case-timeout --json`. Tune there first, then scale to a generated **`test_files59_sample`** slice or full **`test_files59`**.

0b. **Large HTML / site-tree ratio loop (known good for fz with `--large`):** **`test_files55_sample`** (~25 MiB, every-8th subset) is the documented bytes-win slice; the full tree is **`test_files55`** (~200 MiB). For a **~70+ MiB** stratified slice of the full tree, run `php benchmarks/build_test_files55_sample.php` → **`test_files55_stratified`**. Bench with **`--large`** (see `benchmarks/BYTES_WIN_TRACKER.md` — without `--large`, gzip-fast on heavy-list names hurts ratio).

1. **Generate a sample** (from repo root):

   ```bash
   php benchmarks/sample_large_corpus.php test_files59 test_files59_sample
   ```

   Optional: `--target-mib=N` (default **56**; plan may exceed this if `--min-large-files` spread picks very large members), `--small-max-kib=N` (default **512**, keeps sidecars), `--min-large-files=N` (default **2**), `--dry-run`.

2. **Benchmark only the sample**:

   ```bash
   php benchmarks/run_benchmarks.php --only=test_files59_sample --no-verify --no-case-timeout --no-baseline-cache --no-best-ext
   ```

   `test_files59_sample` is **skipped in default** full benchmark runs (see `run_benchmarks.php` `$skipByDefault`).

3. **Pick settings** on the sample (e.g. `FRACTAL_ZIP_FOLDER_GZIP_FAST_DEFLATE_LEVEL`, `FRACTAL_ZIP_STREAM_CHUNK_BYTES`).

4. **Confirm on the full corpus** with the same flags:

   ```bash
   php benchmarks/run_benchmarks.php --only=test_files59 --no-verify --no-case-timeout --no-baseline-cache --no-best-ext
   ```

## What the sampler preserves

- **All small files** (≤ `--small-max-kib`): text logs, cues, images, etc., so path mix and literal-bundle shape stay realistic.
- **Largest extension by total bytes** (e.g. `.flac`): takes an evenly **spread** subset along sorted paths until the byte target is met (or all files), plus **every** other large extension present.

## PDF (literal-PAC + optional qpdf) — `test_files72` / `test_files72_sample`

- Full tree **`test_files72`**: large, mixed PDFs. Build a **size-stratified, bounded** slice:  
  `php benchmarks/build_test_files72_sample.php` → **`test_files72_sample`** (regenerate: `rm -rf test_files72_sample` first). Tweak `--max-mib`, `--bins`, `--target-total-mib`; optional `--write-manifest=benchmarks/test_files72_sample.json`.
- **Micro slice** (3 files, each ≤4 MiB, ~4 MiB total) for sub-minute to few-minute loops:  
  `rm -rf test_files72_sample_micro && php benchmarks/build_test_files72_sample.php --file-cap=3 --max-mib=4 --out=test_files72_sample_micro --min-files=1`  
  If `test_files72_sample_micro` exists, `pdf_literal_pac_empirical` defaults to it over `test_files72_sample`.
- **Measure** in-process literal PAC (flate → dct → optional qpdf) with:  
  `php benchmarks/pdf_literal_pac_empirical.php [test_files72_sample] [--per-step] [--json] [--limit=N]`.  
  Compare `default` / `with_qpdf` / `qpdf_first` in one go: add **`--all-modes`** (three separate PHP children; avoids static env). Order matters: `qpdf_first` can out-save “qpdf last” on the same file set.
- Then validate on the **full** `test_files72` (or a larger `--max-mib`) if policy changes; extrapolation is still heuristic (producer mix, image vs text, encryption).

## Limits

- Extrapolation is **heuristic**; always do a final full-corpus run.
- gzip-fast / streaming paths **do not** run fractal auto-tune; sample tuning is mostly **I/O and deflate**, not segment grids.

## Silesia (Mahoney corpus)

- Official per-file compressed totals and **`test_files78`** / **`test_files133`** vs uncompressed twelve-file work: **`benchmarks/SILESIA_BENCHMARK.md`**.
- Fast tuning slice: **`php benchmarks/sample_large_corpus.php test_files133 test_files133_sample --target-mib=28`** (see **`SILESIA_BENCHMARK.md`** A/B notes) then **`--only=test_files133_sample --large`**.
- **`bash benchmarks/run_large_corpus_bytes_push.sh --only=test_files133 --large --json --no-case-timeout`** (after **`build_test_files133_silesia12.php`**) — same threading / **`large-bytes`** preset as other huge-folder ratio passes; see **`benchmarks/LARGE_CORPUS_SPEED.md`**.

## FLAC / future “transmedia” packing (FZCD)

- With **≥2** compatible `.flac` files, optional **FZCD** transmedia merge (PCM concat → re-encode / fractal on PCM) targets **semantic lossless** audio (same samples + meaningful tags), not SHA1-identical `.flac` container bytes. It is **off by default** (`FRACTAL_ZIP_FLACPAC` unset) so default benchmark **byte** verify passes; set **`FRACTAL_ZIP_FLACPAC=1`** when tuning that path on large slices (requires **ffmpeg** + **ffprobe**). See `fractal_zip_flac_pac.php` header for PCM/toolbag roadmap. Future types: see sibling `../file_types/types.php` compressed list.
