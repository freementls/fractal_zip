# Random five — perf round 12

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260508
```

This round’s five: `test_files4`, `test_files13`, `test_files62`, `test_files65`, `test_files74`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files4,test_files13,test_files62,test_files65,test_files74 \
  --no-zip-time-budget --repeat=2 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum_rep_s` median: **98.2008 s** (min 94.8259, p90 101.5758)
- 50% speedup target was not reached in this round.

## Code changes in this round

- `fractal_zip_pdf_native_pac.php`:
  - Added `fractal_zip_pdf_pac_starts_with_endstream_after_ws()` (byte-scan helper).
  - Replaced regex `preg_match('/\\A(\\s*endstream\\b)/s', ...)` with the helper in the hot recompress loop.
- Goal: remove regex engine overhead from per-stream tail validation while preserving exact `endstream` boundary semantics.

## Guard

`php benchmarks/guard_sixcase_stable_perf.php` — OK (exact six-case `fzc_bytes`; `sum(zip_seconds)` within configured ceiling).
