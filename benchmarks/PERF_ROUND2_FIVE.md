# Random five — perf round 2

## Selection

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260426
```

**This round’s five:** `test_files`, `test_files29`, `test_files35`, `test_files71`, `test_files74`  
(Includes a heavy BMP tree `test_files35` — `perf_test` can take many minutes per repeat.)

## Command

```bash
php perf_test.php \
  --only=test_files,test_files29,test_files35,test_files71,test_files74 \
  --no-zip-time-budget --repeat=7 --no-stack-sample
```

KPI: **`zip_folder_sum_rep_s` median** in the run summary.

## 50% speedup target

**~50%** (half the previous `zip_folder_sum_rep_s` median) is **not** met with **byte-safe** changes that also pass `benchmarks/guard_sixcase_stable_perf.php` (exact `fzc_bytes` on the six proxies). In this round we tried and **reverted**:

- **8-slot** memo in `fractal_zip::outer_codec_run_stdin_or_tmpfile_stdout` — the six-case guard regressed; **restored the original 2-slot** cache.
- **Lifestyle FZB4/FZBM path-order prefilter** default `6` → `2` — `fzc_bytes` moved on `test_files74/75/76`; **reverted to default 6** (guard back to `=6` in the known-good block).

Tighter prefilter and larger outer-codec memos are still valid **local experiments** if you accept re-baselining `fzc_bytes` on a wider matrix.

## Code shipped (bytes-stable)

- `literal_bundle_path_order_brotli_q11_proxy_len`: static path-order score cache `1024` → `2048` (mirrored in `fractal_zip-undeep-unwrap.php`).
- `outer_brotli_blob`: in-process Brotli output cache `1024` → `2048` (mirrored).

`php benchmarks/guard_sixcase_stable_perf.php` should stay **OK** on these.

## Reproduce another random five

```bash
php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=<new>
```

Use `--print-perf-only` to emit a one-line `perf_test.php` command.
