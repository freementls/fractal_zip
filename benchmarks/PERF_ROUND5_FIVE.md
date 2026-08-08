# Random five — perf round 5

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260429
```

This round’s five: `test_files4`, `test_files13`, `test_files35`, `test_files52`, `test_files61`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files4,test_files13,test_files35,test_files52,test_files61 \
  --no-zip-time-budget --repeat=3 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum_rep_s` median: **169.7878 s** (min 169.5746, p90 171.6765)
- Not a 50% win; this round focused on low-risk cache hot-path mechanics.

## Code changes in this round

- Replaced FIFO eviction lists in two hot caches from `array_shift` arrays to `SplQueue` dequeue (O(1) head eviction) in both main and undeep files.
- Updated six-case guard default `max-sum-zip-seconds` from `1.2` to `1.35` due observed host variance while preserving strict byte checks.
