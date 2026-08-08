# Lossless convert × fractal_zip benchmark

Generated: 2026-06-04T20:28:06+00:00

KPI: **`.fz` bytes only** (baseline corpus vs convert-preprocessed corpus).
Bench uses `--no-best-ext` — no phda9/paq min-ext lane (world-record preset only).
Positive **Δ fzc** = lossless convert helped fractal_zip compress smaller.

**Total saved on wins:** 529,680 bytes across 6 actions.

## Wins

| Action | Baseline | Converted | Base fzc | Conv fzc | Δ fzc | % |
|--------|----------|-----------|--------:|---------:|------:|--:|
| wav_to_flac | test_files165 | test_files170 | 6,925,890 | 6,708,302 | 217,588 | 3.14% |
| bmp_to_png | test_files168 | test_files169 | 234,304 | 60,621 | 173,683 | 74.13% |
| xlsx_to_csv | test_files166 | test_files167 | 177,720 | 89,950 | 87,770 | 49.39% |
| png_to_bmp | test_files154 | test_files155 | 60,772 | 32,565 | 28,207 | 46.41% |
| zip_to_7z | test_files158 | test_files159 | 257,986 | 237,760 | 20,226 | 7.84% |
| csv_to_json | test_files151 | test_files152 | 80,753 | 78,547 | 2,206 | 2.73% |

## All experiments

| Action | Base fzc | Conv fzc | Δ fzc | Win | Raw base → conv |
|--------|--------:|---------:|------:|:---:|----------------:|
| wav_to_flac | 6,925,890 | 6,708,302 | +217,588 | yes | 10,584,498 → 6,729,755 |
| bmp_to_png | 234,304 | 60,621 | +173,683 | yes | 1,173,760 → 63,728 |
| xlsx_to_csv | 177,720 | 89,950 | +87,770 | yes | 184,089 → 1,313,208 |
| png_to_bmp | 60,772 | 32,565 | +28,207 | yes | 65,128 → 958,842 |
| zip_to_7z | 257,986 | 237,760 | +20,226 | yes | 270,468 → 237,733 |
| csv_to_json | 80,753 | 78,547 | +2,206 | yes | 940,298 → 12,869,272 |
| targz_to_zip | — | — | missing | — | — |
| svg_to_bmp | 237 | 877 | -640 | no | 328 → 40,138 |
| 7z_to_zip | 234,542 | 245,662 | -11,120 | no | 1,174,362 → 252,840 |
| csv_to_xlsx | 80,753 | 177,717 | -96,964 | no | 940,298 → 184,090 |
| flac_to_wav | 6,715,198 | 6,925,890 | -210,692 | no | 6,767,183 → 10,584,498 |

Re-run: `php benchmarks/run_convert_lossless_suite.php`