# Whole-corpus convert via run_benchmarks

**Metric:** whole-folder `.fz` from `run_benchmarks --no-best-ext`; round-trip verified by re-encode + extract→reverse.
**Win:** smaller converted whole `.fz` than baseline **and** verified lossless recovery.

## Verified wins (vs paired baseline)

- **53_xlsx_csv** `xlsx_to_csv`: 177,673 → 89,981 (Δ +87,692) — **but vs table ref 74,167: −15,814 (worse)**
- **61_png_bmp_whole** `png_to_bmp`: 60,608 → 30,203 (Δ +30,405)

## No verified table wins (none beat published fzc B with round-trip).

| Case | Preprocess | Base fzc | Conv fzc | Δ | Table ref | vs table | RT |
|------|------------|--------:|---------:|--:|----------:|---------:|:--:|
| 53_xlsx_csv | xlsx_to_csv | 177,673 | 89,981 | +87,692 | 74,167 | -15,814 | OK |
| 61_tarballs_zip7z | zip_to_7z | — | — | — | — | — | FAIL |
| 61_png_bmp_whole | png_to_bmp | 60,608 | 30,203 | +30,405 | — | — | OK |
