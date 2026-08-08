# Random five — perf round 1

## Selection

- **Pool:** same default benchmark set as `run_benchmarks.php` (canonical `test_files*`, minus huge / synthetic defaults); 24 cases in this tree.
- **Repro script:** `php benchmarks/pick_random_perf_corpus_set.php --n=5 --seed=20260425`
- **This round’s pick:** `test_files10`, `test_files13`, `test_files49`, `test_files52`, `test_files70`

## `perf_test.php` command (no zip time budget, bytes-first env like default)

```bash
php perf_test.php \
  --only=test_files10,test_files13,test_files49,test_files52,test_files70 \
  --no-zip-time-budget --repeat=9 --no-stack-sample
```

KPI: **`zip_folder_sum_rep_s` median** (and min/p90) printed in the run summary.

## Measured in this worktree (illustrative; desktop noise applies)

| Stage | `zip_folder_sum_rep_s` median (repeat) | Notes |
|--------|----------------------------------------|--------|
| Pre-change baseline (session) | **~1.49 s** | repeat=5, before Brotli/path-order cache tuning |
| After safe memoization | **~1.27 s** | repeat=9, Brotli output LRU 96→**1024**, FZB path-order proxy cache 256→**1024**; six-case guard still OK |

**50% faster** on this sum would be median **≈ 0.75 s** (half of ~1.49 s). The byte-safe cache changes delivered roughly **~15%** less zip time on this five-case slice, not 50%.

Pushing to ~50% on the same **bytes-first** `perf_test` setup would need heavier levers (e.g. stricter FZB4 path prefilter, skipping textlike Brotli `lgwin` sweeps) that we verified can **change** `fzc_bytes` on the six proxy corpora, so they are not enabled as new defaults here.

## Code in this change (bytes-stable)

- `fractal_zip::outer_brotli_blob` / mirror: in-process Brotli output cache `96` → `1024` entries.
- `literal_bundle_path_order_brotli_q11_proxy_len` / mirror: static path-order score cache `256` → `1024` entries.
- `fractal_zip-undeep-unwrap.php`: define `disable_brotli_q11_textlike_lgwin_extra_sweep` (was referenced but missing; parity with `fractal_zip.php`).

## Guard

- `php benchmarks/guard_sixcase_stable_perf.php` — exact expected `fzc_bytes` on the six small proxies, unchanged by the cache edits.
