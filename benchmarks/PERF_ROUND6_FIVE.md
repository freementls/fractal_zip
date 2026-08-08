# Random five — perf round 6

## Selection

Primary draw (seed `20260430`) produced a very heavy mix (`test_files`, `test_files4`, `test_files53`, `test_files65`, `test_files72`) that did not complete in practical time on this host during the round.

Round 6 fallback draw:

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260501
```

This round’s measured five: `test_files52`, `test_files61`, `test_files63`, `test_files74`, `test_files76`.

## `perf_test.php` command

```bash
taskset -c 0 php perf_test.php \
  --only=test_files52,test_files61,test_files63,test_files74,test_files76 \
  --no-zip-time-budget --repeat=2 --no-stack-sample
```

## Result snapshot

- `zip_folder_sum_rep_s` median: **63.0313 s** (min 59.9476, p90 66.1150)
- This still does not reach a 50% speedup target.

## Code changes in this round

- Micro-optimized cache eviction in hot paths by changing queue eviction checks from `while` to `if` (overflow can only be one per insert), in both:
  - `literal_bundle_path_order_brotli_q11_proxy_len` cache queue
  - `outer_brotli_blob` cache queue
- Mirrored in both `fractal_zip.php` and `fractal_zip-undeep-unwrap.php`.

## Guard

- `php benchmarks/guard_sixcase_stable_perf.php` passed:
  - exact six-case `fzc_bytes` remained stable
  - `sum(zip_seconds)=1.1141 <= 1.35`
