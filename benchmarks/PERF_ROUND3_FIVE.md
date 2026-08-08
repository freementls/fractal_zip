# Random five — perf round 3

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260427
```

**This round’s five:** `test_files`, `test_files4`, `test_files29`, `test_files62`, `test_files72`  
(`test_files72` can be large; wall time may be high.)

## `perf_test.php` command

```bash
php perf_test.php \
  --only=test_files,test_files4,test_files29,test_files62,test_files72 \
  --no-zip-time-budget --repeat=7 --no-stack-sample
```

KPI: **`zip_folder_sum_rep_s` median** in the summary line.

## ~50% speedup

Treating “50% faster” as **half** the previous `zip_folder_sum_rep_s` median is **not** something we can promise from this round alone while keeping **exact** `fzc_bytes` on the six proxy corpora in `guard_sixcase_stable_perf.php`.

We **tried** lowering the lifestyle FZB4/FZBM path-order prefilter default from **6 → 5** (shell test had passed in isolation). With the rest of the round-3 edits, the six-case guard **regressed** on `test_files74` / `test_files76`, so the default stayed **6** and the guard still uses `=6` in the known-good block.

## What shipped (bytes-stable; guard OK)

- **`fractal_zip::hot_string_digest`:** one-entry “same string as last call” fast path to skip repeated xxh128/md5 on identical buffers (in-process cache keys only; mirrored in `fractal_zip-undeep-unwrap.php`).
- **`outer_likely_textlike`:** one-entry last-string memo for the full sampling path (mirrored in undeep).
- **`literal_bundle_path_order_brotli_q11_proxy_len`:** static score table **2048 → 4096** entries.
- **`outer_brotli_blob`:** in-process Brotli output cache **2048 → 4096** entries (mirrored).

```bash
php benchmarks/guard_sixcase_stable_perf.php
```

should remain green on the expected `fzc_bytes` table.
