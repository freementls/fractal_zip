# Silesia corpus vs fractal_zip benchmarks

This note ties the **Silesia Open Source Compression Benchmark** ([Matt Mahoney — `silesia.html`](https://mattmahoney.net/dc/silesia.html)) to how we bench **`test_files78`**, the opt-in **`test_files133`** twelve-file folder, and Mahoney-style aggregation using the Squash mirror corpora.

## What the Mahoney table measures

- **Input:** the 12 standard Silesia files (uncompressed sizes and MD5s are listed on the page; combined raw size is **211 938 580** bytes).
- **Scoring:** each file is compressed **individually**; the published **Total** column is the **sum** of the 12 compressed output sizes (bytes), not “one archive of the whole tree”.
- **Programs:** open source only; versions and options vary by row (see the page for dates — e.g. snapshot **2026-01-26** on the live site).

Reference **Total** values (first column on the page; useful as “bytes to beat” for a *sum-of-12-files* comparison, not for a single pre-compressed `.zip` blob):

| Approx. total (B) | Compressor (examples from the table) |
|------------------:|----------------------------------------|
| 27 987 907 | paq8px_v210 -12L (strongest listed total at time of writing) |
| 28 025 541 | paq8px_v209 -12L |
| 38 995 519 | zpaq 6.21 -method 7 |
| 48 792 760 | 7zip -mx=9 |

Lower totals are better. Your local copy of the full leaderboard: [`https://mattmahoney.net/dc/silesia.html`](https://mattmahoney.net/dc/silesia.html).

## How that differs from `run_benchmarks.php`

- **Default bench row** for **`test_files78`**: one corpus directory with **raw_bytes ≈ 67 633 896** — a single pre-DEFLATE **`silesia.zip`**. **PHASE_UNPEEL** expands it to twelve logical members (same payloads as **`test_files133`**); validated **FZHM+FZHR** **`fzc_bytes` 39 112 813**, **`verify_ok` true** (2026-05-20). Pre–peel-first rows (~67 MiB opaque zip) are obsolete for ratio work.
- **Uncompressed Silesia as one bench folder:** after **`php benchmarks/build_test_files_squash_corpora.php`**, run **`php benchmarks/build_test_files133_silesia12.php`** → **`test_files133/`** (twelve files, **211 938 580** B). Default discovery **skips** it; use **`--only=test_files133`**. It is on the same **heavy-folder gzip-fast** name list as **`test_files54`** / **`test_files55`** / … in **`run_benchmarks.php`**: without **`--large`**, raw ≥ **128 MiB** can engage **`FRACTAL_ZIP_FOLDER_GZIP_FAST`** before **`zip_folder`** — use **`--large`** for ratio-first Silesia runs (see **`benchmarks/LARGE_CORPUS_SPEED.md`**). **`benchmarks/predict_outer_benchmarks.php`** uses the same list via **`bench_predict_outer_heavy_corpora_folder_gzip_fast_default()`** in **`bench_predict_outer_encode.php`** — pass **`--large`** there too when probing **`test_files133`**. One JSON row = one `.fz` over the **whole** directory (still not identical to Mahoney’s **sum of twelve independent** compressed sizes, but matches our normal folder benchmark model).
- **Mahoney-style sum:** run one JSON with **`--only=test_files108,test_files116,…,test_files132`** and add the twelve **`cases[].fzc_bytes`** values; compare that sum to the **Total** column on [`silesia.html`](https://mattmahoney.net/dc/silesia.html).

## `test_files133` (materialize + bench)

```bash
php benchmarks/build_test_files_squash_corpora.php    # if Squash singles missing
php benchmarks/build_test_files133_silesia12.php --dry-run   # verify sources; no writes
php benchmarks/build_test_files133_silesia12.php [--force]

FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G \
  php benchmarks/run_benchmarks.php --only=test_files133 --large --no-case-timeout --json \
  --out-json=benchmarks/.silesia133_progress.json
# shorthand: --only=133 (bare digits → test_files133)
```

### Quick sanity (no full encode)

```bash
php benchmarks/build_test_files133_silesia12.php --dry-run
test -d test_files133 && du -sb test_files133   # expect 211938580 B total (Mahoney combined raw)
php benchmarks/verify_fzc_bytes_winner_each_corpus.php --only=133 --dry-run
```

### Fast slice + encoder A/B

**`test_files133_sample`** (gitignored): e.g. **`php benchmarks/sample_large_corpus.php test_files133 test_files133_sample --target-mib=28`** → ~43 MiB raw, four members — use **`--only=test_files133_sample --large`** for a multi-minute encode loop vs full **133**.

On one set of runs (**`--no-best-ext`**, **`--no-verify`**), **`fzc_bytes`** stayed **8 435 136** with outer **arc** for: default (no profile), **`FRACTAL_ZIP_OUTER_PREDICT_MAX_INNER_BYTES=0`** vs capped (bytes unchanged), **`--bench-profile=large-bytes`** (bytes unchanged; **`zip_seconds`** dropped sharply vs no profile in that session — tier mostly affects outer/min-ext *work*, not this slice’s final Arc body), and **`--ultra`** (same **`fzc_bytes`**; caption gains **`+ultra+DEEP`**). So this slice already hits the same Arc wire size under several “try harder” presets; further ratio wins likely need **algorithm / literal** changes, a **full twelve-file** run, or corpora where min-ext beats **arc** (then outer ordering matters more). **Repeat (2026-05-13):** **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=2G` `php benchmarks/run_benchmarks.php --only=test_files133_sample --large --no-case-timeout --no-best-ext --no-verify --out-json=/tmp/…`** again produced **`raw_bytes`** 43 296 318, **`fzc_bytes`** 8 435 136, **`outer_codec`** **`arc`**, **`zip_seconds`** ~382 (min-ext column suppressed by **`--no-best-ext`**; wall ~6.4 min on one host).

**Tik-tok (2026-05-18):** **`test_files133_sample`** Arc **8 435 136** (`large-fast` ~48 s). **Full `test_files133` (pre-FZHM):** **`fzc_bytes`** **60 411 616**, outer **zstd**, min-ext **xz** **48 760 032** — **bytes loss** (~11.7 MiB) on both **`large-fast`** (~263 s, **`.silesia133_speed_push.json`**) and **`large-bytes`** (~196 s, **`.silesia133_largebytes.json`**) — **identical `.fz` size**. **Post-FZHM (2026-05-19):** **39 112 720** **store** — **bytes win** vs min-ext **xz** and vs Mahoney x12 **zpaq** sum (**39 112 599**). Guards: **`guard_test_files133_sample_bytes_and_time.php`**, **`guard_test_files57_bytes.php`**.

### Per-member heterogeneous bundle (FZHM v1 + FZHR restore)

**PHASE_UNPEEL** at folder encode: **`fractal_zip_resolve_folder_logical_bundle()`** expands multi-member PKZIP on disk (e.g. one **`silesia.zip`** in **`test_files78`**) into the same logical members as loose **`test_files133`**. When **`FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST`** is unset, **auto** enables when **logical member count ≥ 2** (ZIP multi caps from literal mode 18, not the old 16-file flat heuristic). Each logical member is an isolated single-file `.fz`, bundled under **`FZHM\x01`** **store** outer; **`FZHR\x01`** trailer restores original disk layout on extract (verbatim **`silesia.zip`** by default). Final wire is the smallest of:

- FZHM (+FZHR when peeled) sum of per-member wires
- monolithic unified-stream encode on **disk map**
- native folder archives when **`FRACTAL_ZIP_FOLDER_BASELINE_TIE=1`** (default)

Force on/off: **`FRACTAL_ZIP_FOLDER_PER_MEMBER_BEST=1|0`**. Prospect scan (logical member counts after peel): **`php benchmarks/prospect_fzhm_flat_corpora.php`**. Smokes: **`php benchmarks/smoke_fzhm_per_member_best.php`**, **`php benchmarks/smoke_logical_zip_folder.php`**, **`php benchmarks/smoke_test_files78_equiv_133.php --quick`**, **`php benchmarks/smoke_test_files78_fzhm_encode.php`** (needs **4G** RAM). Bench semantic PKZIP verify: **`FRACTAL_ZIP_FOLDER_CONTAINER_SEMANTIC_VERIFY`** on by default (unset); **`=0`** for strict envelope SHA1. Round-trip inspector: **`php benchmarks/inspect_fzhm_extract.php`**.

**`test_files78` ≡ `test_files133` (post peel-first):** validated **`fzc_bytes` 39 112 813**, **`verify_ok` true** (`benchmarks/.silesia78_peel_pmb.json`, 2026-05-20). FZHM **store**; FZHR metadata-only tournament. Re-bench: **`FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G benchmarks/run_bench_with_cleanup.sh --only=test_files78 --large --no-case-timeout --json`**. After interrupt: **`benchmarks/kill_stray_bench_procs.sh`**.

**`test_files133_sample`** (2026-05-19, FZHM + flat-ext auto): **`fzc_bytes`** **7 242 118** **store** vs min-ext **zpaq** **7 513 671** — **~265 KiB win** (was monolithic **arc** **8 435 136** pre-FZHM). JSON **`benchmarks/.prospect_133sample.json`**, **`folder_unified_stream` false**.

### FZHM prospect results (2026-05-19)

| Corpus | Raw | `.fz` | min-ext | Win? | Final path | JSON |
|--------|----:|-------:|--------:|:----:|------------|------|
| **`test_files133`** | 212 MiB | **39 112 720** | 39 865 338 zpaq | yes | FZHM **store** | **`.silesia133_pmb.json`** |
| **`test_files133_sample`** | 43 MiB | **7 242 118** | 7 513 671 zpaq | yes | FZHM **store** | **`.prospect_133sample.json`** |
| **`test_files77`** (Calgary ×15) | 3.1 MiB | **659 877** | 659 877 zpaq | tie | unified **zpaq** | **`.prospect_77_fzhm.json`** |
| **`test_files56_sample`** | 1.0 MiB | 1 009 231 | 1 007 430 brotli | no | unified **zpaq** | **`.prospect_13_56.json`** |
| **`test_files13`** | 63 KiB | 7 573 | 6 287 brotli | no | unified **zstd** | **`.prospect_13_56.json`** |

**Pattern:** FZHM **wins** when members are **large and heterogeneous** (Silesia-class); **ratchet correctly keeps unified** on small flat sets where monolithic inner + native outer beats per-member sum. Scan: **`php benchmarks/prospect_fzhm_flat_corpora.php`**. **`test_files72_sample`** bench in flight (**`.prospect_72sample_fzhm.json`**).

**Full `test_files133` with FZHM (2026-05-19):** **`fzc_bytes`** **39 112 720**, outer **store** (**FZHM**), all **12** members **native zpaq** passthrough (**`7kSt`** wires — same sizes as Mahoney x12 min-ext **zpaq_raw** per file). **Beats** prior monolithic **60 411 616** (**zstd**) and min-ext folder **xz** **48 760 032** by **~9.65 MiB** vs xz. **`inspect_fzhm_extract.php`**: **OK** round-trip all members (~29 min wall for zpaq extract). Official bench JSON: **`benchmarks/.silesia133_pmb.json`** (`--large --no-case-timeout`, verify enabled).

### Mahoney x12 per-file sum (`benchmarks/.silesia12_perfile_pmb.json`, 2026-05-19)

Twelve Squash singles, **`--large`** (auto **large-balanced**), **`--no-verify`**, baseline tie + per-member zpaq native. Sum **`raw_bytes`** = **211 938 580** (matches Mahoney combined raw).

| Sum column | Bytes | vs Mahoney **7zip -mx=9** total (**48 792 760**) |
|------------|------:|--------------------------------------------------|
| **min-ext** (per-folder) | **39 112 599** | **−9.68 MiB** (better) |
| **`.fz` (sum of 12)** | **39 112 599** | **−9.68 MiB** (tie min-ext) |
| **7z folder** | **48 758 631** | ~tie |
| **gzip9 bundle** | **67 765 779** | worse |

**All 12/12** per-file rows **tie** min-ext (**`report_bytes_wins.php`**); outer **zpaq** on every case (native zpaq passthrough, not 7z). One-folder **`test_files133`** FZHM = **39 112 720** (**+121 B** FZHM container overhead vs x12 sum).

Re-run: **`bash benchmarks/run_silesia12_per_file.sh benchmarks/.silesia12_perfile_pmb.json`**

### Mahoney x12 per-file sum (`benchmarks/.silesia12_progress.json`, 2026-05-18 — pre-FZHM singles)

**Model comparison:** same raw, one **`test_files133`** folder **`.fz`** = **39 112 720** (**FZHM** **store**, 2026-05-19) — **ties** the Mahoney x12 per-file **zpaq** sum within **~121 B** and **beats** folder min-ext **zpaq** (**39 865 338**) by **~753 KiB**. Pre-FZHM monolithic was **60 411 616** (**zstd**).

**Per-file `.fz`:** all **12** rows **lose** vs min-ext (`report_bytes_wins.php`); outers mix **7z** / **zstd** (no **arc** on this run). **`test_files108`** alone with **`--only=108 --large`** once produced **2 222 401** **arc** vs **2 831 111** **7z** in the x12 batch — outer tournament can swing ~608 KiB on dickens; treat Mahoney sums as run-specific.

**Native 7z passthrough (2026-05-18):** **`php benchmarks/analyze_bench_native_passthrough.php benchmarks/.silesia12_progress.json`** → **8/12** cases had **`fzc_bytes === seven_zip_folder_bytes`** (wire is the bench **7z** blob, not fractal+arc). **`FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0`** on an isolated **`--only=test_files108`** run yields **2 222 401** **arc** (~25 s).

**Batch vs isolated encode:** one **`--only=108,…,132`** JSON with **`FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0`** (**`.silesia12_no7znat_progress.json`**) gave **`sum_fzc_bytes`** **60 156 123**, **0** passthrough, but **all** outers **zstd** and **dickens** **3 282 565** (worse than **7z** passthrough **2 831 111**). Same env, **`--only=test_files108`** alone immediately after → **2 222 401** **arc**. For a reproducible Mahoney total, use **`bash benchmarks/run_silesia12_per_file.sh`** (twelve separate runs, merged JSON).

| Artifact | `sum_fzc_bytes` | Passthrough | Notes |
|----------|----------------:|:-----------:|-------|
| `.silesia12_progress.json` | **55 982 120** | **8/12** | Pessimistic (mostly 7z wire) |
| `.silesia12_no7znat_progress.json` | **60 156 123** | **0/12** | Batch drift; all **zstd** |
| Mahoney 7zip -mx=9 ref | **48 792 760** | — | [silesia.html](https://mattmahoney.net/dc/silesia.html) |
| min-ext sum (no7znat JSON) | **39 112 617** | — | zpaq-heavy |

```bash
php benchmarks/silesia_sum_fzc_from_bench_json.php benchmarks/.silesia12_progress.json
php benchmarks/report_bytes_wins.php benchmarks/.silesia12_progress.json
```

## Latest documented `test_files78` snapshot (repo JSON)

From **`benchmarks/.silesia78_peel_pmb.json`** (2026-05-20, **`bench_profile`:** `large-balanced`, default baseline tie):

| Metric | Bytes |
|--------|------:|
| raw | 67 633 896 |
| gzip9 bundle | 67 432 056 |
| 7z folder | 67 408 375 |
| min-ext (winner **zpaq_raw**) | 67 182 345 |
| **.fz** | **39 112 813** (≈ **test_files133** **39 112 720**) |

**`verify_ok`:** true (**`verify_mismatch_files`:** 0). **`folder_unified_stream`:** false (FZHM store). **`zip_seconds`:** ~4800 s (12 isolated encodes + native ratchet). Pre–peel-first **~67 MiB** rows (e.g. **`.gt2mb_batch7.json`**) are obsolete.

## Long-run commands (allowed to be slow)

For **heavy** full-fractal work on large or many corpora, raise PHP memory and drop the case timeout. Examples:

```bash
# Preferred: one folder corpus (build test_files133 first; bare **133** ≡ **`test_files133`**)
FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G \
  php benchmarks/run_benchmarks.php --only=133 --large --no-case-timeout --json \
  --out-json=benchmarks/.silesia133_progress.json

# Precompressed ~67 MiB bundle (Mahoney silesia.zip–class; not per-file totals)
FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G \
  benchmarks/run_bench_with_cleanup.sh --only=test_files78 --large --no-case-timeout --json \
  --out-json=benchmarks/.silesia78_peel_pmb.json

# Twelve separate bench cases in one JSON — sum cases[].fzc_bytes vs silesia.html Total
# Disable native 7z passthrough so .fz keeps fractal+arc when it beats the 7z column (see analyze_bench_native_passthrough.php).
FRACTAL_ZIP_FOLDER_NATIVE_7Z_COMPARE=0 \
FRACTAL_ZIP_BENCH_MEMORY_LIMIT=4G \
  php benchmarks/run_benchmarks.php \
  --only=test_files108,test_files116,test_files117,test_files118,test_files119,test_files120,test_files124,test_files125,test_files126,test_files130,test_files131,test_files132 \
  --large --no-case-timeout --json \
  --out-json=benchmarks/.silesia12_progress.json
```

Add **`--bench-profile=large-bytes`** (or `large-balanced`) if you want the tiered min-ext/native caps from **`benchmarks/LARGE_CORPUS_SPEED.md`**. **`--ultra`** is appropriate when you intentionally want the bytes-first preset and wider outer search (even slower).

After each run, summarize wins vs gzip/7z/min-ext:

```bash
php benchmarks/report_bytes_wins.php benchmarks/.silesia78_progress.json
# optional: --compress-time-audit
```

**Sum twelve `fzc_bytes` from one JSON** (Mahoney-style total vs [`silesia.html`](https://mattmahoney.net/dc/silesia.html) **Total**):

```bash
php benchmarks/silesia_sum_fzc_from_bench_json.php benchmarks/.silesia12_progress.json
# single-folder corpus instead:
php benchmarks/silesia_sum_fzc_from_bench_json.php benchmarks/.silesia133_progress.json --labels=test_files133
```

## Peeling and ratio work on `test_files133`

Full-tree loss vs min-ext **xz** is documented in the tracker; **bench profiles** do not move wire size. For a **peel → inner → outer** plan (per-member tuning, what *not* to build for Silesia, Mahoney x12 measurement): **`docs/PEELING_FOLLOW.md`**.

## “Relayer to .zip” — what exists in-tree

There is **no** separate end-user script named “relayer”, but the **literal PAC / recursive peel** path already implements **semantic peel → inner work → rebuild** for ZIP (and 7z, tar, …) when those transforms win the literal contest:

- **Peel order / ZIP branch:** `fractal_zip_literal_recursive_peel.php` (`fractal_zip_literal_recursive_peel_try_one_semantic`, ZIP when `fractal_zip_literal_semantic_zip_enabled()`).
- **Single-member ZIP:** peel `fractal_zip_literal_pac_peel_zip_single_semantic`, rebuild `fractal_zip_literal_pac_rebuild_zip_single_semantic`.
- **Multi-member ZIP (mode 18):** list `fractal_zip_literal_pac_list_zip_members_for_mode18`, rebuild **`fractal_zip_literal_pac_rebuild_zip_multi_semantic`** (deflate level 9, member order preserved). Toggle / caps: **`FRACTAL_ZIP_LITERAL_SEMANTIC_ZIP_MULTI`**, `FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_MEMBERS`, `FRACTAL_ZIP_LITERAL_ZIP_MULTI_MAX_RAW_BYTES` (see `fractal_zip_literal_pac.php`).

**Extraction:** successful literals round-trip through the normal decode / `open_container` path; the wire format stores the semantic layer metadata so the outer container can restore the original `.zip` bytes when that variant is selected.

**Practical note for Silesia:** if the bench input is a **single** `silesia.zip`, winning strategies may include **peeling** to per-file inners, compressing those with fractal / outer codecs, then **rebuilding** a `.zip` for verify — that is exactly the family of logic above, gated by literal contest + limits. If the corpus is instead **twelve loose files**, relayering to `.zip` is an optional *packaging* experiment (not required for Mahoney parity).

## `verify_fzc_bytes_winner_each_corpus.php`

Full-repo sweeps **omit** the same default-opt-in dirs as **`run_benchmarks.php`** (including **`test_files133`** and stratified samples). Target Silesia explicitly: **`php benchmarks/verify_fzc_bytes_winner_each_corpus.php --only=133`** (slow; not the same as Mahoney byte totals). For real runs (not **`--dry-run`**), each case invokes **`run_benchmarks.php`** with **`--large`** automatically when **`bench_corpora_should_pass_large_to_run_benchmarks()`** in **`bench_predict_outer_encode.php`** is true (heavy-folder gzip-fast list + raw ≥ **128 MiB**), so **`test_files133`** uses full fractal without folder gzip-fast.

## Next steps (engineering)

1. Record any new **`--out-json=`** rows in **`benchmarks/BYTES_WIN_TRACKER.md`** (and bump the “Last updated” line).
2. For **Mahoney totals**, aggregate the twelve `cases[]` `fzc_bytes` values and compare to the **Total** column on [`silesia.html`](https://mattmahoney.net/dc/silesia.html) (remember: their total is sum of per-file outputs, not one `.fz` file).
3. **`test_files133`** is the documented uncompressed twelve-file folder; keep **`test_files78`** as the precompressed bundle row in the tracker unless the on-disk corpus is renamed.
