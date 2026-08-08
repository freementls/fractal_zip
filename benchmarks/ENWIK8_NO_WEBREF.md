# enwik8 toward &lt;15 MiB (no web-ref)

## Headline metrics (competition-valid)

| Metric | Bytes | How |
|--------|------:|-----|
| `.fz` (pp96 preset, 129 members) | 19,594,333 | `php benchmarks/run_enwik8_encode_only.php --name=pp96` |
| `.fz` (pp64 legacy) | 22,043,397 | old default pages/member=64 |
| `best_any` (fzc vs zpaq_raw) | 19,625,015 | native zpaq on raw single file |
| Hutter reference (phda9) | 15,284,944 | external benchmark target |
| `best_any_dual` | **15,010,414** (phda9) | `php benchmarks/compare_enwik8_world_record.php` |

## Tier 0 tooling

```bash
php benchmarks/smoke_paq_tools.php          # phda9 in tools/phda9/
php benchmarks/bench_enwik8_zpaq_squash.php # ~15 min → .enwik8_zpaq_squash.json
FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_squash.php  # hours → .enwik8_paq_squash.json + .fzpq
FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_export_wire.php  # wire only if .fzpq missing
php benchmarks/merge_enwik8_squash_into_world_record.php
```

Encode-time raw PAQ: `FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=1` after sorted-folder encode. With `.enwik8_paq_squash.fzpq`, `FRACTAL_ZIP_ENWIK_RAW_PAQ_USE_SQUASH_CACHE=1` swaps cached FZpq in seconds (no live phda9). Dual-order `.fz`: `php benchmarks/run_enwik8_encode_dual_order.php`.

## Experiments

```bash
bash benchmarks/run_enwik8_experiment_grid.sh   # pp32/48/96, unified0/1, semantic1
php benchmarks/summarize_enwik8_experiments.php
```

See **`benchmarks/ENWIK8_GRID_CONCLUSIONS.md`** for full grid results and preset rationale.

Single encode: `php benchmarks/run_enwik8_encode_only.php --name=pp32` with env overrides.

**Grid winners (encode-only, no web-ref):** `pp96` → **19 594 333 B** fzc (preset default); `pp48`/`pp32` within ~50 KiB; `semantic1` +10 KiB (no gain). **Legacy `unified0`** (all three disables above): **19 620 079 B** — **same wire as `pp48` unified stream**, ~30% slower encode (~1803s vs ~1124s); `folder_unified_stream=false` confirmed. Unified stream is the right default for speed; **pp96** topology is the fzc win.

## Semantic pack (optional)

`FRACTAL_ZIP_ENWIK_SEMANTIC_PACK=1` — template `{{…}}` dictionary; smoke: `php tests/enwik_semantic_pack_smoke.php`.
