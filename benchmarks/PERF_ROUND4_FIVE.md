# Random five — perf round 4

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260428
```

**This round’s five:** `test_files13`, `test_files52`, `test_files61`, `test_files63`, `test_files76`  
(Includes the six-proxy-style desktop bundle `test_files76`.)

## `perf_test.php` command

```bash
php perf_test.php \
  --only=test_files13,test_files52,test_files61,test_files63,test_files76 \
  --no-zip-time-budget --repeat=7 --no-stack-sample
```

Use the printed **`zip_folder_sum_rep_s` median** (and min/p90) as the round KPI.

## ~50% speedup (half the previous `zip_folder_sum_rep_s` median)

That target is **not** guaranteed from byte-identical, six-proxy–safe work alone: cutting path-order work or Brotli proxy depth enough for a **2×** win usually changes which literal order / outer scores win, so `fzc_bytes` move unless you re-baseline widely.

## What changed in this round (bytes-stable; guard OK)

- **`fractal_zip::hot_string_digest`:** 1-entry repeat cache → **2-entry mini-MRU** (A/B alternation and similar patterns skip more hashing). Mirrored in `fractal_zip-undeep-unwrap.php`.
- **`outer_likely_textlike`:** 1-entry → **2-entry mini-MRU**; consolidated exits with one store block (mirrored in undeep, distinct `goto` label to avoid name clashes if both file bodies load in one process).
- **`literal_bundle_path_order_brotli_q11_proxy_len` static score table:** 4096 → **8192** (mirrored).
- **`outer_brotli_blob` in-process Brotli output table:** 4096 → **8192** (mirrored).

`php benchmarks/guard_sixcase_stable_perf.php` should stay green on the fixed `fzc_bytes` expectations.
