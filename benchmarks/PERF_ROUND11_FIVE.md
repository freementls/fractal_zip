# Random five — perf round 11

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260507
```

This round’s five: `test_files`, `test_files64`, `test_files69`, `test_files70`, `test_files76`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files,test_files64,test_files69,test_files70,test_files76 \
  --no-zip-time-budget --repeat=2 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum_rep_s` median: **38.4293 s** (min 32.6689, p90 44.1897)
- 50% speedup target not reached in this round.

## Code changes in this round

- Added bounded memoization inside `fractal_zip_pdf_pac_recompress_flate_all_passes()`:
  - Decode memo for `fractal_zip_pdf_pac_flate_inflate_with_wrapper($oldEnc)` keyed by stream bytes.
  - Repack memo for `fractal_zip_pdf_pac_flate_best_repack(...)` keyed by wrapper + stream bytes.
- Purpose: avoid repeating inflate/repack work when identical `/FlateDecode` streams recur in the same PDF pass sequence.
- Memo size is capped (`512` entries each) to bound memory.

## Guard

`php benchmarks/guard_sixcase_stable_perf.php` — OK (exact six-case `fzc_bytes`; `sum(zip_seconds)` within configured ceiling).
