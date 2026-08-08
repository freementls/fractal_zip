# Whole-corpus convert × fractal_zip (ultra)

**Preset:** `FRACTAL_ZIP_ULTRA=1` (bytes-first; same as `run_benchmarks.php --ultra` / `zip --ultra`).

**Metric:** `.fz` size of the **entire** corpus folder after forward-convert on glob-matched members only.

**strict_win:** whole `.fz` smaller than baseline **and** extract → convert-reverse restores converted members with **`===` bytes**.

**Table refs:** published 59-case values (e.g. test_files53 = 74,167). Direct `zip_folder` baseline under ultra matches these on CSV/small corpora; may differ from `run_benchmarks.php` **fzc B** column (often arc/zpaq outer — e.g. test_files53 fzc B ≈ 80,753 while table/min-ext = 74,167).

## Table strict wins: **0**

No case beats the table reference **and** passes strict byte-identical convert-reverse.

## Strict bytes wins (non-table): **1**

| Corpus | Preprocess | Δ fzc | strict RT | Notes |
|--------|------------|------:|:---------:|-------|
| test_files61 | bmp_to_png | +675 | OK | 14 grid BMPs; `png_to_bmp` exact reverse |

## All cases (ultra)

| Corpus | Preprocess | Base fzc | Conv fzc | Δ | Table | sem RT | strict RT |
|--------|------------|--------:|---------:|--:|------:|:------:|:---------:|
| test_files53 | csv_to_json | 74,167 | 78,547 | −4,380 | 74,167 | FAIL | FAIL |
| test_files10 | bmp_to_png | 147 | — | — | 147 | FAIL | FAIL |
| test_files11 | bmp_to_png | 158 | — | — | 158 | FAIL | FAIL |
| test_files34 | bmp_to_png | 917 | 1,019 | −102 | 921 | OK | **FAIL** |
| test_files64 | bmp_to_png | 1,255 | 1,929 | −674 | 1,255 | OK | OK |
| test_files74 | csv_to_json | 1,301 | 1,348 | −47 | 1,307 | FAIL | FAIL |
| test_files74 | svg_to_bmp | 1,301 | 1,990 | −689 | 1,307 | FAIL | FAIL |
| test_files149 | zip_to_7z | 1,064 | — | — | 1,064 | FAIL | FAIL |
| test_files61 | png_to_bmp | 1,060,281 | 1,059,414 | +867 | — | OK | **FAIL** |
| test_files61 | bmp_to_png | 1,060,004 | 1,059,329 | +675 | — | OK | OK |

## Re-run

```bash
php benchmarks/run_convert_whole_corpus_bench.php --ultra
php benchmarks/run_convert_whole_corpus_bench.php --ultra --only=test_files53,test_files34
```

JSON: `benchmarks/.convert_whole_corpus_bench.json`
