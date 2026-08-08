# Random five — perf round 9

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260505
```

This round’s five: `test_files2`, `test_files11`, `test_files13`, `test_files35`, `test_files75`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files2,test_files11,test_files13,test_files35,test_files75 \
  --no-zip-time-budget --repeat=2 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum_rep_s` median: **63.5860 s** (min 58.7757, p90 68.3962)
- 50% round-over-round speedup was not reached.

## Code changes in this round

- Added two-slot per-handler memoization for expensive PDF PAC handlers in `fractal_zip_literal_pac.php`:
  - `native_flate`
  - `dct_jpeg`
  - `jbig2`
  - `jpx`
  - `ccitt`
- Memo key: `(len, md5)` via existing `fractal_zip_literal_pac_static_lru_fp`, bounded to inputs up to 64 MiB.
- Caches both positive and negative outcomes to avoid redundant recompress attempts on identical PDF blobs within a run.

## Guard

`php benchmarks/guard_sixcase_stable_perf.php` — OK (exact six-case `fzc_bytes`; `sum(zip_seconds)` within configured ceiling).
