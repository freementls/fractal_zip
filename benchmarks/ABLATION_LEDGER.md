# Ablation ledger protocol

GAIA-inspired progressive promotion for bytes-win claims.

## Stages

1. **sample5 / `_sample`** — tiny slice
2. **medium** — stratified / mid corpus
3. **full** — tracker-eligible corpus

An arm may be pasted into `BYTES_WIN_TRACKER.md` only when **full** evaluation returns `promote: true`.

## Row fields

| Field | Meaning |
|-------|---------|
| `verify_ok` | Strict SHA1 tree compare (`true`/`false`/`null` skipped) |
| `closeness.sha_mismatch_frac` | Soft residual when SHA fails |
| `closeness.semantic_ok` | Semantic equality (e.g. SC2 MPQ) |
| `closeness.legibility_delta` | Literal legibility delta when relevant |
| `promote` | Allowed for tracker paste / next stage |

## Soft closeness rule

- Soft metrics **never** replace SHA for lossless claims.
- `verify_ok: false` may still `promote` only when `semantic_ok: true` and mismatch fraction ≤ 5%.
- `verify_ok: null` (skipped) always blocks tracker paste.

## CLI

```bash
php benchmarks/ablation_ledger.php --demo --out=benchmarks/.ablation.json
php benchmarks/ablation_ledger.php --from-json=benchmarks/.some.json
```

Hook from the main harness (writes `benchmarks/.ablation.json` by default):

```bash
php benchmarks/run_benchmarks.php --only=test_files10 --json --ablation-ledger
# or: FRACTAL_ZIP_BENCH_ABLATION_LEDGER=1 php benchmarks/run_benchmarks.php …
```
