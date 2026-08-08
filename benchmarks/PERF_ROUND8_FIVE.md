# Random five — perf round 8

## Selection

Primary draw:

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260503
```

Primary draw included `test_files72` (very large on this host), so measured fallback draw:

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260504
```

This round’s measured five: `test_files`, `test_files35`, `test_files49`, `test_files71`, `test_files74`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files,test_files35,test_files49,test_files71,test_files74 \
  --no-zip-time-budget --repeat=1 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum` (repeat=1): **589.2388 s**
- This round did not achieve a 50% speedup target.

## Code changes in this round

- 24 bpp BSS2 core scan now adds a 24-byte step (two unrolled 12-byte chunks) before the existing 12-byte and scalar tails.
- This reduces loop-control overhead in a hot path while preserving exact byte checks.
- Mirrored in both `fractal_zip.php` and `fractal_zip-undeep-unwrap.php`.

## Guard

`php benchmarks/guard_sixcase_stable_perf.php` passed (exact six-case `fzc_bytes`; timing within configured ceiling).
