# enwik8 experiment grid — conclusions (no web-ref)

Generated from encode-only runs on `test_files109/enwik8`. Primary fz metric: **sorted `.fz` bytes**. phda9 squash is an external reference (~15.01 MiB), not the default encode path — see `docs/ENWIK8_INTEGRATED_COMPRESSION.md`.

## Preset (current)

| Setting | Value |
|---------|-------|
| `FRACTAL_ZIP_ENWIK_PAGES_PER_MEMBER` | **96** |
| `FRACTAL_ZIP_FOLDER_UNIFIED_STREAM` | **1** |
| Entry sort | on |

## Sorted path (`.fz`)

| Experiment | fzc (B) | zpaq | members | Notes |
|------------|--------:|------|--------:|-------|
| **pp96** / **pp96_high** | **19,594,333** | 9 | 129 | **Winner** — preset default; high tier same bytes, ~22 min vs ~67 min encode |
| pp48 | 19,620,079 | 9 | 258 | −25.7 KiB vs pp96 |
| unified0 (legacy) | 19,620,079 | 9 | 258 | Same wire as pp48; `wire_unified=0`; ~60% slower encode |
| pp32 | 19,638,861 | 9 | 386 | |
| semantic1 | 19,630,917 | 9 | 258 | No gain vs pp48 |
| unified1 (pp48 + unified) | 19,620,079 | 9 | 258 | Matches pp48; first grid run (22 MiB / 11 min) was invalid |
| pp64 | 22,043,397 | 3 | 193 | Old default topology |

## Raw PAQ (dual-order)

| Tool | Bytes |
|------|------:|
| **phda9** | **15,010,414** (below Hutter 15,284,944) |
| zpaq_raw passthrough | 19,625,015 |

**Fast dual-order `.fz`:** sorted encode (~22 min) + replace with cached `benchmarks/.enwik8_paq_squash.fzpq` (no live phda9 re-run). One-time wire export if only JSON exists:

```bash
FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_export_wire.php   # ~hours once
php benchmarks/run_enwik8_encode_dual_order.php   # encode + verify
bash benchmarks/run_enwik8_dual_order_pipeline.sh
bash benchmarks/run_enwik8_dual_order_when_ready.sh   # poll until .fzpq, then pipeline
php benchmarks/bench_enwik8_paq_progress.php          # export archive growth
```

## Takeaways

1. **Topology:** 96 pages/member beats 48/32/64 on sorted fzc (fewer, larger virtual members → zpaq method 9).
2. **Unified stream:** Same compressed size as legacy per-member at pp48; faster encode — keep unified on.
3. **Semantic pack:** Slightly worse on this corpus; not in preset.
4. **Sub-15 MiB:** Needs stronger inner statistical modeling inside fz, not external PAQ wrap.
5. **Harmony light (2026-06-03):** full-blob boilerplate → **19,625,015 B** (+30.7 KiB vs pp96) — hurts article fractal; avoid.
6. **Harmony siteinfo (tier B):** header-only boilerplate → **19,594,449 B** (+116 B vs pp96), `verify_ok`, ~1,101 s — parity.
7. **Harmony text (tier C):** siteinfo + 56 text-region tokens → **19,594,823 B** (+490 B vs pp96), `verify_ok`, ~1,725 s — no wire win yet.

## Text codec lab (sample5, 2026-06-03)

`words_base94` + `sort_lines_alpha` wins on all 5 sample pages (~**15–20%** of raw text gzip-1 per page). Aggregate text-only preencode: **~18.6%** of raw text gzip-1 (`bench_enwik8_sample5_codec_preencode.php`). Full-corpus test:

```bash
bash benchmarks/run_enwik8_textcodec_when_idle.sh   # waits for no conflicting encode
php benchmarks/compare_enwik8_textcodec_vs_pp96.php
tail -f benchmarks/logs/enwik8_textcodec_encode.log
```

Env: `FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94`, `FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=sort_lines_alpha`. See `docs/ENWIK8_TEXT_CODEC_LAB.md`.

Idle detection ignores stale shell parents (~350 KiB RSS); only high-RSS runners (≥50 MiB) or `benchmarks/.enwik8_encode_test_files109.lock` block the queue (`benchmarks/enwik8_encode_busy_check.sh`).

**Implicit-space codecs (2026-06-03):** `words_base94_isp` / `words_id_varint_isp` — single ASCII space between words is **not** stored; punctuation, `[[wiki]]`, multi-space, and newlines go in a `gaps[]` sidecar. **Strict roundtrip** on full sample pages. On sample5 preencode gzip-1, `words_base94` + `sort_lines_alpha` still wins (~16% of text gzip-1); `words_base94_isp` ~23–24% (gap literal overhead) but preserves markup.

**Full-corpus `pp96_textcodec`:** Early run **42,028,375 B in ~230 s** was wire bloat: 12k EZTC entries inlined in phrase dict (~41 MiB FZEP). Fixed (2026-06-03): `~EZT#~` tokens + `meta/text_codec.eztb` member + EZTV-only dict (~574 KiB). Re-encode in progress — expect ~20–70 min. Compare: `php benchmarks/compare_enwik8_textcodec_vs_pp96.php`.

## Inner-first grid (2026-06-03)

pp96 reference: **19,594,333 B**. Inner presets omit `bench_world_record_apply_high_env()` (8 MiB outer-predict probe, not 128 MiB). See `benchmarks/ENWIK8_INNER_CAPS.md`.

| Case | fzc (B) | Δ vs pp96 | encode (s) | Notes |
|------|--------:|----------:|-----------:|-------|
| `inner_baseline` | 19,594,333 | 0 | 1,549 | Reference inner path |
| `inner_deep` | 19,594,333 | 0 | 1,036 | |
| `inner_allsub` | 19,594,333 | 0 | 1,034 | |
| `inner_multidiff_caps` | 19,594,333 | 0 | 1,011 | |
| `inner_combo` | 19,594,333 | 0 | 1,007 | All inner relaxations + `ALL_SUBSTRING_CANDIDATES` |
| **`inner_recursive0`** | **19,594,333** | **0** | **4,936** | combo + `RECURSIVE_ONLY=0` — **parity, ~5× slower** |

**Conclusion:** No inner-first preset beats pp96 on sorted fzc wire. `inner_recursive0` (run-grammar + peeler with `RECURSIVE_ONLY=0`) matches baseline bytes but costs ~82 min vs ~17 min for combo — not worth the encode-time hit. Sub-15 MiB still needs stronger inner statistical modeling, not these cap/grammar toggles alone.

```bash
export FRACTAL_ZIP_NO_CLI_OPACHE_REEXEC=1
/usr/bin/php -d memory_limit=4096M -d opcache.enable_cli=0 benchmarks/run_enwik8_inner_experiment.php --case=inner_baseline
bash benchmarks/run_enwik8_inner_experiment_grid.sh
php benchmarks/compare_enwik8_inner_vs_baseline.php
# Fast slice probe (~8 MiB, minutes):
php -d memory_limit=2048M benchmarks/bench_enwik8_inner_unified_probe.php
```

**Slice probe (2026-06-03):** `probe_baseline`, `probe_combo`, and `probe_recursive0` all **1,769,220 B** on 8 MiB unified — no wire delta at slice scale; see `benchmarks/.enwik8_inner_unified_probe.json`.

## Merged preset validation (`preset_verify`, 2026-06-03)

| Metric | Value |
|--------|------:|
| fzc | 19,594,333 B |
| encode | ~1,351 s (~22 min) |
| extract + SHA-256 | ~206 s, **verify_ok true** |

Confirms `bench_world_record_apply_high_env()` in the default preset: same bytes as pp96, faster encode, roundtrip intact.

## Dual-order fast path (cached raw PAQ)

Encode-time raw PAQ no longer re-runs phda9 (~7.5 h) on every world-record encode when squash wire is persisted:

| Artifact | Role |
|----------|------|
| `benchmarks/.enwik8_paq_squash.json` | phda9 archive bytes + tool metadata |
| `benchmarks/.enwik8_paq_squash.fzpq` | full `FZpq` wire from `fractal_zip_paq_wrap_wire()` |

Env (set by `bench_world_record_env.php`): `FRACTAL_ZIP_ENWIK_RAW_PAQ_USE_SQUASH_CACHE=1`. Live PAQ only when `FRACTAL_ZIP_ENWIK_RAW_PAQ_FORCE_LIVE=1`.

One-time wire export (~7.5 h phda9):

```bash
FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_export_wire.php
```

Dual-order encode + compare (~20–25 min with cache):

```bash
bash benchmarks/run_enwik8_dual_order_pipeline.sh
bash benchmarks/run_enwik8_dual_order_pipeline.sh --verify
```

## Commands

```bash
php benchmarks/summarize_enwik8_experiments.php
php benchmarks/compare_enwik8_world_record.php
bash benchmarks/run_enwik8_experiment_grid.sh   # skip-if-valid JSON
FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_squash.php
```
