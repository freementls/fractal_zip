# Experiment archive log

Conservative cleanup on 2026-06-27. **Benchmark JSON rows, wire460 logs, and `benchmarks/` harness outputs were not touched.**

## Removed (safe scratch)

| Item | Reason |
|------|--------|
| `114_perf.stderr`, `114_trace.stderr` | One-off perf/trace stderr captures at repo root |
| `tmp/fzgff_ddb2d303_out/`, `tmp/tf61e/`, `tmp/tf61e2/` | Stale April–May extract scratch dirs |
| `tmp/bmptest.7z` | Ad-hoc 7z bench artifact in `tmp/` |

## Archived → `docs/experiment-archive/root-scratch-20260627/`

| File | Notes |
|------|-------|
| `bench_test_files55.json` | Pasted bench snapshot (Apr 2025) |
| `bench_test_files59_e.json` | Pasted bench snapshot (Apr 2025) |
| `perf.json` | Local perf capture (Apr 2025) |
| `test_files177.fz.preprocess.json` | Preprocess metadata sidecar |
| `_bench_tf29.fz` | Micro ratio demo container (test_files29) |

## Kept intentionally

- `benchmarks/` — all JSON results, logs, wire460 sweeps, `BYTES_WIN_TRACKER.md` source rows
- `enwik8`, `calgary.zip`, `cc580.zip`, corpus GIFs — benchmark inputs
- `test_files*/` corpora trees
- `docs/` design notes and readiness checklists

## Native extract cache integration (same date)

Phase 1 hybrid manifest + blob ingest documented in `docs/NATIVE_EXTRACT_CACHE.md`.
