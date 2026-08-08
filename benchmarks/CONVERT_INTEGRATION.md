# Convert × fractal_zip integration notes

## Recommended path: CLI staging preprocess

```bash
# Encode (staging copy; originals untouched)
FRACTAL_ZIP_SUPPRESS_HTML=1 php fractal_zip_cli.php zip --preprocess=png_to_bmp test_files177

# Extract + reverse convert (semantic PNG restore; not byte-identical)
FRACTAL_ZIP_SUPPRESS_HTML=1 php fractal_zip_cli.php extract --reverse-preprocess test_files177.fz
```

Sidecar: `{dir}.fz.preprocess.json` lists `forward`/`reverse` actions and `orig_sha256` per member.

Implementation: `fractal_zip_preprocess.php`, wired in `fractal_zip_cli.php`.

### Measured wins (whole-folder `.fz`, `--no-best-ext` env)

| Corpus | Baseline fzc | `--preprocess=png_to_bmp` | Δ | Notes |
|--------|-------------:|--------------------------:|--:|-------|
| test_files177 (7 PNGs only) | 60,608 | **24,269** | **+36,339** | Best case |
| test_files61/00_source_png (via-bench) | 60,608 | 30,203 | +30,405 | Disk-preprocessed copy |
| test_files61 (127 files, 25 PNGs) | 1,060,164 | 1,059,337 | +827 | Mixed corpus; PNGs are small fraction |

**Table corpora:** still **0 verified table wins** (bmp_to_png on BMP refs, csv_to_json, etc.).

## In-pipeline mode 28 (`FRACTAL_ZIP_CONVERT_PNG_TO_BMP=1`)

FZB literal mode 28 probes png→bmp before the literal tournament. **Blocked:** convert `bmp_to_png` re-encodes PNG (not byte-identical); `literal_bundle_coerce_verbatim_disk_roundtrip` rejects mode 28. In-pipeline fzc unchanged vs baseline on test_files177.

Files: `fractal_zip_convert_bridge.php`, `benchmarks/smoke_convert_png_mode28.php`.

## test_files61 full-tree encode — fixed

**Was:** `FRACTAL_ZIP_LITERAL_EXPAND_GZIP_INNER` fatal on `grid_01.gif.gz` (orig_len=11008) when gzip peel + literal transform could not bit-restore wrapper.

**Fix:** `literal_bundle_coerce_verbatim_disk_roundtrip` catches decode/restore failures and falls back to mode 0 verbatim (see `fractal_zip.php`).

Baseline encode now completes: **~1.06 MB fzc** in ~113s (`benchmarks/encode_test_files61_once.php`).

## Extract / round-trip expectations

| Path | Strict SHA1 vs originals | Semantic restore |
|------|:------------------------:|:----------------:|
| CLI `--preprocess` + `--reverse-preprocess` | No (PNG re-encoded) | Yes (Intervention/Image) |
| Disk-preprocessed BMP in `.fz` + manual `bmp_to_png` | No | Yes (convert QA) |
| In-pipeline mode 28 | Would require byte-identical reverse | Not active |

For strict byte-identical PNG restore, keep originals outside the archive or add optional verbatim sidecar storage (not implemented).

## Benchmark scripts

- `benchmarks/run_convert_whole_corpus_via_bench.php` — table-aligned via `run_benchmarks`
- `benchmarks/run_convert_whole_corpus_bench.php` — direct `zip_folder` whole-corpus
- `benchmarks/bench_test_files61_png_preprocess.php` — test_files61 staged png→bmp
- `benchmarks/CONVERT_WHOLE_CORPUS_VIA_BENCH.md` — latest via-bench report
