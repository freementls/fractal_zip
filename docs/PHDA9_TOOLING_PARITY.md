# phda9 tooling parity (fractal_zip)

Concise matrix of upstream phda9 capabilities vs fractal_zip integration.

| Feature | phda9 (upstream) | fractal_zip |
|---------|------------------|-------------|
| Binary | `phda9`, `phda9dec` bundled at `tools/phda9/` | Auto-discovered; `FRACTAL_ZIP_PAQ` / `FRACTAL_ZIP_PAQ_PHDA9` override |
| External dictionary | 4th argv; `80000` + 12 empty CRLF lines; ≤188240 words; ≤1930550 B; CRLF | `fractal_zip_phda9_dict.php` + `FRACTAL_ZIP_PAQ_PHDA9_DICT`; build via `benchmarks/build_phda9_external_dict.php` |
| LSTM variant | `phda9_no_LSTM` (~2.5× faster, ~2% worse) | Discovered as `phda9_no_lstm`; same C/D + dict argv |
| Threads | Single-threaded per process | `FRACTAL_ZIP_PAQ_SWEEP=1` parallelizes **across tools**, not inside phda9 |
| Sorted fzc path | N/A (raw byte order) | Inner modeling on sorted wire (`FZDI`, boilerplate dict); **not** FZpq wrap — see `docs/ENWIK8_INTEGRATED_COMPRESSION.md` |

## Commands

**Full enwik8 squash (hours):**

```bash
FRACTAL_ZIP_PAQ_TOOLS=phda9 php benchmarks/bench_enwik8_paq_squash.php
```

**Build external dictionary:**

```bash
php benchmarks/build_phda9_external_dict.php --pages=384    # quick mine
php benchmarks/build_phda9_external_dict.php --full         # full corpus slice
export FRACTAL_ZIP_PAQ_PHDA9_DICT=benchmarks/.phda9_external_dict.txt
```

**Fast variant sweep:**

```bash
FRACTAL_ZIP_PAQ_TOOLS=phda9:phda9_no_lstm FRACTAL_ZIP_PAQ_SWEEP=1 php benchmarks/smoke_paq_tools.php
```

## Why sorted fzc still needs inner modeling

phda9 wins on **raw enwik8 byte order** (~15.01 MiB in our squash). Entry-sorted `.fz` optimizes fractal path-order and inner codecs on a different wire; wrapping raw phda9 output as FZpq does not transfer that ratio to the sorted member layout. phda9 ideas (boilerplate/static vocab, harmony preprocess) are integrated on the sorted path via `fractal_zip_enwik_dict_phda9_inner.php` and related inner folds — not by re-ordering phda9 archives inside fzc.
