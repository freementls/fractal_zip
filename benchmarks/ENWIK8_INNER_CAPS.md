# enwik8 inner caps and gates (unified stream, pp96)

Reference baseline: **19,594,333 B** sorted `.fz` (`pp96`, unified stream, zpaq m9).

Tune **inner** fractal / multidiff / substring / literal bundle before expanding outer-predict (`bench_world_record_apply_high_env`).

## Path on 100 MiB virtual folder

1. `test_files109` → enwik entry sort → **129 members** (~775 KiB each).
2. `FRACTAL_ZIP_FOLDER_UNIFIED_STREAM=1` → one concatenated inner (~100 MiB text).
3. `zip_folder` unified branch (`fractal_zip.php` ~16292+).

## Gates that fire on enwik8 today

| Gate | Default / preset | Effect on enwik unified |
|------|------------------|-------------------------|
| **`SUBSTRING_MULTIDIFF_RECURSIVE_ONLY`** | **on** (unset) | **`integratedMultidiffOnly`**: skips **run-grammar** and **peeler** variant collection; uses reciprocal fast path + literal bundle only. Largest structural limiter for “full” inner search. |
| **`SUBSTRING_MULTIDIFF_MAX_LITERAL_JOBS`** | **10** | Caps phase-4 outer trials from reciprocal multidiff; **0 = unlimited** (inner_focus sets 0). |
| **`SUBSTRING_MULTIDIFF_MAX_CANDIDATES`** | **240** (max env 2000) | Truncates deduped multidiff candidates before scoring. |
| **`SUBSTRING_MULTIDIFF_MAX_TOKENS`** | **180000** | Token budget before legacy slide fallback. |
| **`SUBSTRING_MULTIDIFF_MAX_PAIR_PROBES`** | **2200** | Pair-probe cap in multidiff. |
| **`SUBSTRING_TOP_K`** | **24** (max 48) | Top-K after substring scoring unless `ALL_SUBSTRING_CANDIDATES=1`. |
| **`ALL_SUBSTRING_CANDIDATES`** | off | Research mode: skip top-K truncation (heavy RAM). |
| **`SUBSTRING_MULTIDIFF_MIN_BYTES`** | **65536** | Unified inner qualifies; runtime may force **0** on FS dense reciprocal paths (not default enwik). |
| **`FOLDER_LOW_TEXTISH_SKIP_RUN_GRAMMAR`** | **0** in world-record | Run grammar not skipped for textish census; **but recursive_only still skips collection** (see above). |
| **`WHOLE_STREAM_FZWS_MAX_BYTES`** | **0** in world-record | **0 = unlimited** FZWS on whole stream. |
| **`LITERAL_TRANSFORM_MAX_RAW_BYTES`** | **32 MiB** (ultra) | Non-text transform probes; enwik is text — usually OK. |
| **`FZBM_ORDER_RANDOM_TRIES`** | **512** (enwik_tuned) | Path-order search budget. |
| **`OUTER_PREDICT_PROBE_MAX_BYTES`** | **8 MiB** (ultra); **128 MiB** if `apply_high_env` | Outer CPU; not inner bytes, but adds encode time. **inner_baseline** keeps 8 MiB probe. |
| **`IMPROVEMENT_THRESHOLD`** | **0.01** ultra; **0.005** high | Multipass depth; inner_focus sets 0.005 without full high env. |
| **`PAQ_NATIVE_COMPARE` / `PAQ_SWEEP`** | on in default WR | Hours-scale; **off** in inner_* presets. |
| **`DEEP` / `BUNDLE_RAW_DEEP_UNWRAP`** | on via ultra defaults | Deep unwrap paths enabled unless explicitly cleared. |

## Experiments (inner before outer)

```bash
# Recommended PHP (4 GiB; disable opcache CLI + skip fractal_zip re-exec):
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
/usr/bin/php -d memory_limit=4096M -d opcache.enable_cli=0 benchmarks/run_enwik8_inner_experiment.php --case=inner_baseline
bash benchmarks/run_enwik8_inner_experiment_grid.sh

# High-impact A/B: re-enable run-grammar + peeler on unified stream:
/usr/bin/php -d memory_limit=4096M -d opcache.enable_cli=0 benchmarks/run_enwik8_inner_experiment.php --case=inner_recursive0
```

Cases: `inner_baseline`, `inner_deep`, `inner_allsub`, `inner_multidiff_caps`, `inner_combo`, `inner_recursive0`.

Compare: `php benchmarks/summarize_enwik8_experiments.php` and existing `compare_enwik8_world_record.php`.

## Slice probe (2026-06-03)

`bench_enwik8_inner_unified_probe.php` on **8 MiB** raw (8×1 MiB slices, unified stream):

| Case | fzc (B) | seconds |
|------|--------:|--------:|
| probe_baseline | 1,769,220 | 355 |
| probe_combo | 1,769,220 | 256 |
| probe_recursive0 | 1,769,220 | 258 |

**All cases tied @ 1,769,220 B** (2026-06-11 rerun: baseline, combo, recursive0, fzbm2048, zpaq4, staged0) — caps / `RECURSIVE_ONLY=0` / FZBM2048 / zpaq m4 / staged-fast gate do not change wire bytes on this 8 MiB slice. `probe_staged0` only speeds encode (~134s vs ~257s). Production bytes path: **text-inner mono_mi** full wire **19,207,083 B**; unified inner caps alone will not close the ~4 MiB Hutter gap.
