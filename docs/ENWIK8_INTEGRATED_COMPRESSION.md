# enwik8 integrated compression (harmony)

Goal: **beat ~19.59 MiB sorted `.fz`** by mixing the best *ideas* from competition compressors **inside** fractal_zip — not by wrapping phda9 in `FZpq` and calling it done.

## What each approach contributes

| Source | ~enwik8 | Technique | Integrated into fz |
|--------|--------:|-----------|-------------------|
| **phda9** | 15.01 MiB | Deep English text model on **raw byte order**; optional ~1.9 MiB **word dictionary** | **Boilerplate pack** (static XML phrases); future: corpus word table, member-level statistical shootout — not whole-file PAQ wrap |
| **zpaq raw** | 19.63 MiB | PPM/ZPAQ on single blob | Native folder compare, method **9** on sorted virtual members |
| **fractal sorted (pp96)** | 19.59 MiB | Title **entry sort**, fractal inner, FZBM path order, unified stream, outer predict | World-record preset (high tier) |

phda9’s ~4 MiB gap vs sorted fz is mostly **order + modeling**, not outer codecs. Wrapping phda9 preserves phda9 bytes but does not teach the sorted path to compress better.

## Harmony profile (`FRACTAL_ZIP_ENWIK_HARMONY=1`)

Enables a coordinated stack (all reversible, no web-ref):

1. **Siteinfo pack** (tier B, harmony default) — boilerplate phrases on **header/footer only**; article `<page>` bodies stay raw for fractal inner.
2. **Full-blob phrase packs** (`BOILERPLATE_PACK` / `CORPUS_PHRASES`) — opt-in; `harmony_light` measured **+31 KiB** vs pp96 (avoid on articles).
3. **Entry sort + pp96** — title order + ~129 virtual members (grid winner).
4. **Fractal inner + run grammar + FZWS** — existing text path.
5. **FZBM path order + zpaq m9 outer** — existing world-record / high env.
6. **Optional** `FRACTAL_ZIP_ENWIK_SEMANTIC_PACK=1` — corpus `{{…}}` templates (grid: small regression alone; measure combined).

Harmony preset sets `FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0`, `FRACTAL_ZIP_PAQ_NATIVE_COMPARE=0`, and `FRACTAL_ZIP_PAQ_SWEEP=0` so encode never launches phda9 squash/compare at the end of `zip_folder`.

## Opt-in external PAQ (benchmark only)

```bash
FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=1 FRACTAL_ZIP_ENWIK_RAW_PAQ_FORCE_LIVE=1 php benchmarks/bench_enwik8_paq_squash.php
```

Use for **reference rows** in compare scripts, not production encode.

## Inner-first (dictionary / fractal inner)

Before outer predict / high tier, tune the **unified ~100 MiB** path. Default pp96 uses **`RECURSIVE_ONLY`** (skips run-grammar / peeler), **multidiff max literal jobs (10)**, and **substring top-K (24)** unless relaxed. See [benchmarks/ENWIK8_INNER_CAPS.md](../benchmarks/ENWIK8_INNER_CAPS.md).

Env: `bench_world_record_apply_inner_baseline_env()` / `bench_world_record_apply_inner_focus_env()` in `benchmarks/bench_world_record_inner_env.php` (pp96 **without** `bench_world_record_apply_high_env()`).

```bash
php benchmarks/smoke_world_record_inner_env.php
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
/usr/bin/php -d memory_limit=4096M -d opcache.enable_cli=0 benchmarks/run_enwik8_inner_experiment.php --case=inner_baseline
bash benchmarks/run_enwik8_inner_experiment_grid.sh
php benchmarks/compare_enwik8_inner_vs_baseline.php
```

`FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1` is required: `fractal_zip.php` otherwise re-execs with CLI opcache/JIT and can OOM on large FZB stores.

## Text codec lab (dictionary / representation experiments)

Reversible word-base, morse, bit, and scrabble-style codecs on a **5-page sample** — see [ENWIK8_TEXT_CODEC_LAB.md](ENWIK8_TEXT_CODEC_LAB.md).

```bash
php benchmarks/build_enwik8_sample_pages.php
php benchmarks/bench_enwik8_text_codec_lab.php --quick
```

## Experiments

```bash
php benchmarks/bench_enwik8_boilerplate_pack_probe.php
php benchmarks/bench_enwik8_corpus_phrases_probe.php
php benchmarks/run_enwik8_harmony_encode.php --name=harmony1
php tests/enwik_boilerplate_pack_smoke.php
```

## Roadmap (bytes-first)

| Tier | Work | Hypothesis |
|------|------|------------|
| **A** | Full-blob phrase packs | Measured +31 KiB regression (`harmony_light`) |
| **B** | Siteinfo-only pack (`FRACTAL_ZIP_ENWIK_SITEINFO_PACK`) | **+116 B** vs pp96 — parity (`harmony_siteinfo`) |
| **C** | Text-region pack (`FRACTAL_ZIP_ENWIK_TEXT_PACK`) | Wiki + mined tokens inside `<text>` only (`harmony_text` encode) |
| **D** | Stronger inner on raw-order sidecar (optional second stream in FZEP) | Dual representation without PAQ passthrough |

### Tier D lab (implemented)

- **`FRACTAL_ZIP_ENWIK_STAT_SIDECAR=1`** — FZEP v6 appends gzipped sorted-order word-frequency sidecar (`fractal_zip_enwik_integrated_model.php`).
- **`FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_isp`** — frozen sorted-order vocab + `words_id_varint_isp` (`fractal_zip_enwik_stat_predictor.php`).
- **`FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred`** — sorted bigram-rank predictor wire (**lab-only**; combo @384p +456 KiB vs mono_mi — see `ENWIK8_BYTES_PROBE.md`).
- **`FRACTAL_ZIP_TEXT_INNER_PREPROCESS=stat_pred_inner`** — rank-only bigram encoding; frozen **FZPM v2** (uint16 sparse bigram) + **FZPS** gap sidecars in **FZEP inner-fold trailer** (once per archive). Default **8192** vocab. @384p wire **910,497 B** (+259 KiB vs mono_mi); **amortized_fzc 650,847 B** (−803 B vs mono_mi); sidecar JSON tax **eliminated** (was ~586 KiB). Roundtrip OK @384p fztx mono. **Lab-only on wire** until honest gap closes.
- **`FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_inner`** — NNCP-style segment_v2 tokens; frozen vocab in **FZEP inner-fold trailer** (FZDI body). @384p wire **748,615 B**; **amortized_fzc 619,265 B** (−32 KiB vs mono_mi); verify OK.
- **`FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_phda9_inner`** — phda9-scale vocab cap (188k); same FZEP trailer fold as dict_inner. @384p wire **748,629 B**; amortized **619,279 B**; roundtrip restore **broken** @32p — fix before promotion.
- **`FRACTAL_ZIP_TEXT_INNER_PREPROCESS=dict_nncp`** — legacy sidecar vocab in `meta/text_inner_preprocess.json` (+68 KiB @384p); prefer **`dict_inner`** for fold experiments.
- **`FRACTAL_ZIP_ENWIK_MEMBER_CODEC_SHOOTOUT=1`** — per-chunk min tournament on fz-transformed inner: FZSO stacks + optional zpaq9/phda9 (`fractal_zip_enwik_member_shootout.php`). Gate: `bench_enwik8_member_codec_shootout_probe.php`.
- **NNCP** — built binary at `tools/nncp-2024-06-05/nncp` (CPU; CUDA needs Ampere+). **LLMZip/LLaMA-7B** impractical on 2 GiB GPU without Meta weights; use `stat_pred` / sub-1 lab instead.

Combo gate: `php benchmarks/bench_enwik8_beat15m_combo_probe.php --pages=384`

**384p stat_pred + inner-fold matrix (2026-06-07)** — artifact: `benchmarks/.enwik8_wire_slice_probe.json`, amortized: `benchmarks/.enwik8_amortized_meta.json`

| Case | wire_fzc | amortized_fzc | Δ mono (amort) |
|------|----------:|--------------:|---------------:|
| `stat_pred_no_sidecar` | 973,159 | **581,843** | **−69,807** |
| `stat_pred_inner` | 910,497 | 650,847 | −803 |
| `mono_mi` | **651,650** | **651,650** | 0 |
| `shootout_phda9` | 652,504 | 652,504 | +854 |

Honest wire: **mono_mi** still wins. Amortized: **stat_pred_no_sidecar** best (−70 KiB) but wire +321 KiB; **stat_pred_inner** within 803 B of mono on amortized basis. Promotion: keep **mono_mi** on wire; **dict_inner** / **stat_pred_inner** candidates for full-corpus amortization study only.

Measure every step on `test_files109/enwik8` with `run_enwik8_encode_only.php` / harmony encode; validate roundtrip. Fast gates: `docs/BEAT_15M_FAST_GATE.md`.
