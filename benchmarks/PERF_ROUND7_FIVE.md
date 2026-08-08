# Random five — perf round 7

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260502
```

This round’s five: `test_files10`, `test_files11`, `test_files61`, `test_files65`, `test_files75`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files10,test_files11,test_files61,test_files65,test_files75 \
  --no-zip-time-budget --repeat=2 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum_rep_s` median: **151.9747 s** (min 150.0850, p90 153.8644)
- A **50%** cut on this KPI is **not** achieved from byte-stable cache plumbing alone; most wall time remains literal-bundle / BMP paths (see `perf_test` profiler output when enabled).

## Code changes in this round

- Replaced `SplQueue` FIFO eviction with a **fixed-size ring buffer** (8192 slots) + head/tail/count for the same FIFO semantics in:
  - `literal_bundle_path_order_brotli_q11_proxy_len` (path-order Brotli proxy cache)
  - `outer_brotli_blob` (in-process Brotli output cache)
- Ring arrays are allocated on **first insert only** (cache hits skip allocation and queue work).
- Mirrored in `fractal_zip.php` and `fractal_zip-undeep-unwrap.php`.

## Guard

`php benchmarks/guard_sixcase_stable_perf.php` — OK (exact six-case `fzc_bytes`; `sum(zip_seconds)` within configured ceiling).
