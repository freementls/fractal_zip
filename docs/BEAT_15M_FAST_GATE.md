# Beat 15M fast gate loop

North star: sorted `.fz` < **15,284,944 B** (Hutter). Use this loop before any hour-scale full encode.

## Run

```bash
bash tools/fz_sweep_zombies.sh
bash benchmarks/run_enwik8_beat15m_fast_gate.sh
# or: php benchmarks/run_enwik8_beat15m_fast_gate.php [--skip-sub1] [--skip-768]
```

Output: `benchmarks/.enwik8_beat15m_fast_gate.json`

**Latest gate (2026-06-06):** `all_ok=true`, `mono_mi_384p=651,650 B` (+0 vs baseline), `full_encode_allowed=false` (extrapolation margin &lt; 32 KiB vs best 19,207,083 B). Combo-ranked 384p wire: `benchmarks/.enwik8_wire_slice_probe_384p.json`.

## Gates (sub-30 min total)

| Step | Script | Pass |
|------|--------|------|
| Zombie sweep | `tools/fz_sweep_zombies.sh` | exit 0 |
| Substring bytes | `all_substrings_count_regression.php` | xxh128 lock |
| Slide bytes | `all_substrings_count_slide_regression.php` | same xxh128 with `FRACTAL_ZIP_GPU_SUBSTRING_SLIDE=1` |
| Wire 384p | `bench_enwik8_wire_slice_probe.php --pages=384` | mono_mi baseline |
| Wire 768p | same `--pages=768` | per-page Δ does not regress |
| Roundtrip | `verify_enwik_slice_roundtrip.php --text-inner-promotion` | OK |
| Parallel | `bench_fractal_parallel_phases.php --pages=5` | speedup ≥1.3× |
| Sub-1 ideas | `bench_enwik8_sub1_bpc_matrix.php --pages=sample5 --quick` | completes |

## Specialized probes

```bash
php benchmarks/bench_enwik8_member_codec_shootout_probe.php --pages=384
php benchmarks/bench_enwik8_dict_nncp_wire_bridge.php --pages=384
php benchmarks/bench_enwik8_promotion_encode_profile.php --pages=96
```

## Full encode gate

```bash
php benchmarks/run_enwik8_pp96_refresh.php --gate-check   # refuses if 384p/768p extrapolation fails
php benchmarks/run_enwik8_pp96_refresh.php --force        # bypass gate (explicit)
```

## Env flags (hybrid path)

| Env | Role |
|-----|------|
| `FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1` | Per-chunk zpaq/FZSO shootout on fz inner |
| `FRACTAL_ZIP_ENWIK_STAT_SIDECAR=1` | FZEP v6 word-freq sidecar (tier D lab) |
| `FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_isp` | Frozen sorted vocab + varint ISP wire |
| `FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred` | Sorted bigram-rank predictor wire |
| `FRACTAL_ZIP_STACKED_OUTER=1` | Store-passthrough when inner has FZSO (skips native 7z/arc wire shootout) |
| `FRACTAL_ZIP_TEXT_INNER_STACK=zpaq9_zstd22` + `STACKED_OUTER=0` | FZSO inner then zpaq outer (~+681 B @384p vs mono_mi; faster encode) |
| `FRACTAL_ZIP_GPU_SUBSTRING_SLIDE=1` | Rust slide seeds in `all_substrings_count` |

See `docs/ENWIK8_INTEGRATED_COMPRESSION.md` for philosophy (integrated, not FZpq wrap).
