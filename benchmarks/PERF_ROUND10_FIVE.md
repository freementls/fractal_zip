# Random five — perf round 10

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260506
```

This round’s five: `test_files10`, `test_files13`, `test_files28`, `test_files35`, `test_files53`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files10,test_files13,test_files28,test_files35,test_files53 \
  --no-zip-time-budget --repeat=2 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum_rep_s` median: **68.2543 s** (min 64.8611, p90 71.6474)
- 50% speedup target was not reached in this round.

## Code changes in this round

- Optimized PDF native flate recompress path in `fractal_zip_pdf_native_pac.php`:
  - Added `fractal_zip_pdf_pac_flate_inflate_with_wrapper()` returning both inflated bytes and wrapper type.
  - Changed `fractal_zip_pdf_pac_flate_best_repack()` to accept wrapper type directly, removing redundant wrapper-detection inflates.
  - Caller in `fractal_zip_pdf_pac_recompress_flate_all_passes()` now decodes once and reuses wrapper metadata.
- Net effect: fewer inflate probes per candidate stream while preserving exact output validation.

## Guard

`php benchmarks/guard_sixcase_stable_perf.php` — OK (exact six-case `fzc_bytes`; `sum(zip_seconds)` within configured ceiling).
