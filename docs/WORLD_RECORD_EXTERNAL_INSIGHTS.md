# External approaches → fractal_zip world-record

How competition-class compressors relate to our **fz** preset and what we borrow (without web-ref).

## Reference approaches

| Approach | enwik8 size | What it does | fz analogue |
|----------|------------:|--------------|-------------|
| [Hutter / phda9](http://prize.hutter1.net/) | ~15.28 MiB | Deep statistical model on **raw byte order** | **Reference only** — integrated ideas in `docs/ENWIK8_INTEGRATED_COMPRESSION.md` (boilerplate dict, harmony); FZpq wrap opt-in |
| zpaq passthrough | ~19.63 MiB | Single-archive PPM/ZPAQ on raw tree | `best_ext` native folder compare; method **9** on sorted virtual folder |
| Our sorted `.fz` (pp96) | ~19.59 MiB | Entry sort + fractal inner + adaptive outer | `FRACTAL_ZIP_ENWIK_ENTRY_SORT` + unified stream + pp96 topology |

**Headline** for the prize metric is already **phda9** (~15.01 MiB in our squash). **Sorted fzc** tuning fights over the last ~4 MiB vs zpaq_raw.

## Integrated path (default)

See **`docs/ENWIK8_INTEGRATED_COMPRESSION.md`**. World-record preset keeps `FRACTAL_ZIP_ENWIK_RAW_PAQ_COMPARE=0`. Harmony encode: `php benchmarks/run_enwik8_harmony_encode.php`.

## phda9 documentation takeaways

From `tools/phda9/read_me.txt`:

- **Order matters:** compression is on the original file layout; our entry sort is a *different* order optimized for fractal path-order, then we still run raw PAQ compare on the original blob for the competition dual-order score.
- **External dictionary (optional):** phda9 supports a fixed-format word list (~1.9 MiB). Our **`FRACTAL_ZIP_ENWIK_SEMANTIC_PACK`** is a reversible template dictionary on XML-ish tokens — grid showed **no gain** on fzc at pp48; keep off in preset unless revisited with pp96.
- **Wall time:** full phda9 squash is ~1–8 h; comparable to a **high** fz encode, not the ~67 min pp96 base encode.
- **Boilerplate dictionary (fz-native):** static MediaWiki/XML phrases (`FRACTAL_ZIP_ENWIK_BOILERPLATE_PACK` / harmony) — borrows the *dictionary idea*, not the phda9 binary.

## Where base world-record was not “max” fz

The preset in `bench_world_record_env.php` is **ultra + enwik caps**, but several knobs are **below** ultra defaults to keep encode time ~1 h:

| Knob | Base world-record | Ultra / high | Why it matters |
|------|-------------------|--------------|----------------|
| `FRACTAL_ZIP_LITERAL_BUNDLE_FZBM_ORDER_RANDOM_TRIES` | **128** | **512** | More path-order permutations on ~129 members |
| `FRACTAL_ZIP_OUTER_PREDICT_PROBE_MAX_BYTES` | unset → **8 MiB** | **128 MiB** | Layered outer predict sees full enwik inner |
| `FRACTAL_ZIP_OUTER_PREDICT_LAYER3_HIGH` | off | **on** | 7z/zpaq high-tier confirm probes |
| `FRACTAL_ZIP_OUTER_PREDICT_TIMEOUT_SEC` | 30 | **60** | Longer predict subprocess budget |
| `FRACTAL_ZIP_IMPROVEMENT_THRESHOLD` | 0.01 | **0.005** | More fractal multipass passes |

Base preset **already** sets: 128 MiB zpaq high-method cap, outer sweep, unlimited multipass wall, native compares, entry sort, pp96, pipeline reorder.

## High tier — merged into preset (2026-06-03)

**A/B result:** `pp96_high` = **19,594,333 B** (identical to pp96), **1,336 s** encode vs **4,002 s** at old 128 FZBM / 8 MiB predict probe caps.

`bench_world_record_apply_env_defaults()` now calls `bench_world_record_apply_high_env()` automatically.

Standalone re-run or pipeline:

```bash
php benchmarks/run_enwik8_encode_high.php
bash benchmarks/run_enwik8_high_pipeline.sh   # encode + compare + metrics + smokes
```

## What probably will not move sorted fzc toward 15 MiB

- More outer codecs alone (zpaq m9 already near zpaq_raw on sorted wire).
- Semantic pack / phda9-style external dict without a stronger inner model.
- Web-ref track (out of competition scope).

Next meaningful fz work: confirm high preset bytes; optional **AUTO_TUNE** full bench (much longer than 2 h); inner modeling beyond PAQ passthrough.
