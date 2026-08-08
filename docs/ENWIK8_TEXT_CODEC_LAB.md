# enwik8 text codec lab (world-record dictionary research)

Experiment with **reversible** article-text representations on a fixed **5-page sample** before touching full-corpus encode. Complements title-sort (metadata outside bytes) and phrase-pack tiers in `fractal_zip_enwik.php`.

## Sample corpus

```bash
php benchmarks/build_enwik8_sample_pages.php
```

Writes `benchmarks/corpus/enwik8_sample5/`:

- `manifest.json` — titles, orig indices, byte sizes
- `pages/*.xml` — full `<page>` XML
- `text/*.txt` — concatenated `<text xml:space="preserve">` bodies only

Pages are the **top N by article text length** in enwik8 (default N=5, min ~8 KiB text each).

## Codecs (`fractal_zip_enwik_text_codec.php`)

| Scheme | Idea | Sidecar | Outer affinity (heuristic) |
|--------|------|---------|----------------------------|
| `raw` | baseline | — | zpaq, brotli, fractal |
| `morse` | ITU Morse on letters | reverse table + extras | brotli, zstd |
| `bits_msb` | UTF-8 → bit stream → packed bytes | pad byte | zpaq, xz |
| `bytes_decimal` | space-separated ordinals 0–255 | — | brotli, zstd |
| `letter_scrabble` | frequency-weighted VLC (E short, Q long) | code tree | zpaq, xz |
| `words_base94` | word IDs in base-94 ( **words-only, lossy** on preserve-text — lab only) | vocab + `l:` literals for OOV | zpaq, fractal |
| `words_base94_isp` | **Lossless** on preserve-text: words + gap literals; implicit **single space** between words and **trailing `.`** after final word (not `Dr.`); all other punctuation/markup stored as `g` gaps; OOV words as `l:word` (**no UNK**) | vocab + `gaps[]` | zpaq, fractal |
| `words_base256` | 3-byte big-endian word IDs | vocab | zpaq, xz |
| `words_id_varint` | varint word IDs (compact ids) | vocab | zpaq, fractal |
| `words_id_varint_isp` | varint words + gaps; implicit single space (tag `\x01` word / `\x02` gap) | vocab + `gaps[]` | zpaq, fractal |

**Higher-base word coding:** `words_base94` maps each token to one “digit string” in an alphabet of 94 printable ASCII symbols — approaching “one character per word” when vocab ≪ 94^k. Vocab is **frequency-sorted** so common words get small IDs.

**Lower-base:** `bits_msb` and `bytes_decimal` break UTF-8 below byte/chart level.

## Transforms (shuffle / sort)

Applied **after** codec on payload; sidecar stores permutation or snapshot for undo:

| Transform | Reversible | Notes |
|-----------|------------|-------|
| `sort_lines_alpha` | yes | stores `sort_lines_from` permutation (legacy archives may use `lines_before_sort`) |
| `reverse_lines` | yes | |
| `perm_lines` | yes | LCG seed + `perm_lines[]` |
| `perm_tokens` | yes | word-order shuffle (text tokens only) |
| `sort_tokens_alpha` | yes | stores `tokens_before_sort` |

**Conceptual parallel to title sort:** sort order can live in **sidecar / FZEP-style trailer** (e.g. `sortedIndex`, `perm_lines`, `concept_sort=title`) without mutating the conceptual article identity.

## Lab runner

```bash
php benchmarks/build_enwik8_sample_pages.php
php benchmarks/bench_enwik8_text_codec_lab.php          # full grid
php benchmarks/bench_enwik8_text_codec_lab.php --quick  # subset
php tests/enwik_text_codec_roundtrip_smoke.php
```

Output: `benchmarks/.enwik8_text_codec_lab.json` — per page × (codec, transform): payload bytes, **gzip-1** size, entropy, `outer_affinity`, roundtrip flag.

## Correctness before compression

1. **Enwik wire** must use a **preserve-text** codec (`words_base94_isp` or `words_id_varint_isp`). `words_base94` drops everything that is not an ASCII word token — fine for lab preencode, **not** for `.fz` roundtrip.
2. **Out-of-vocabulary words** are stored as **`l:word`** (or stream tag `3`), never collapsed to `UNK`.
3. **Implicit gaps** are only applied when decode can restore them unambiguously (inter-word space; lone trailing period). Other punctuation stays explicit in `gaps[]` — if that costs bytes, accept it; do not trade correctness for guesses.
4. Smoke: `php tests/enwik_text_codec_isp_lossless_smoke.php` (byte-exact cases + sample page).

## Enwik wire (opt-in)

```bash
export FRACTAL_ZIP_ENWIK_TEXT_CODEC=words_base94_isp
export FRACTAL_ZIP_ENWIK_TEXT_CODEC_TRANSFORM=sort_lines_alpha
export FRACTAL_ZIP_ENWIK_TEXT_CODEC_SEED=1
# then normal enwik encode (test_files109); FZEP semantic dict holds EZTC entries
php tests/enwik_text_codec_page_roundtrip_smoke.php
```

Replaces each `<text xml:space="preserve">` body with `~EZT#~` tokens. Shared vocab is one `EZTV\x01` phrase-dict pattern; per-region `EZTC\x01` payloads live in virtual member `meta/text_codec.eztb` (FZEP v4 flag), not 12k+ phrase-dict entries. Restore: `fractal_zip_enwik_text_codec_restore_eztokens_in_blob()` then `fractal_zip_enwik_semantic_pack_restore()`.

## Integration roadmap

1. Pick winning codec+transform on sample5 (`summarize_enwik8_text_codec_lab.php`, `bench_enwik8_sample5_codec_preencode.php`).
2. Full-corpus encode with `FRACTAL_ZIP_ENWIK_TEXT_CODEC` on sample5 winner; measure `.fz` vs pp96.
3. Combine with **entry sort** (title), **unified stream**, inner-first caps (`ENWIK8_INNER_CAPS.md`).
4. Tournament outers per `outer_affinity` (bit-like → zpaq/xz; morse → brotli/zstd).

### Sample5 page-XML gzip (sanity)

`bench_enwik8_sample5_page_xml_preencode.php`: raw page XML gzip-1 **200,062 B** → after textcodec **1,218 B** (~0.61%). Strong tokenization of article bodies; confirm on full `.fz` via `run_enwik8_textcodec_encode.php`.

### Full corpus

```bash
bash benchmarks/wait_inner_grid_then_textcodec.sh   # or when_idle after other encodes
php benchmarks/compare_enwik8_textcodec_vs_pp96.php
```

## Sub-1 bpc target (world-record extreme track)

Primary score for text experiments: **`text_bpc`** on extracted `<text>` char count. North star **&lt; 1.0**; stretch **&lt; 0.90** (literature ~0.86 on English wiki).

### LTCB anchors (enwik8 = 100,000,000 B)

| Program | Bytes | bpc |
|---------|------:|----:|
| cmix v21 | 14,623,723 | 1.17 |
| nncp v3.2 | 14,915,298 | 1.19 |
| phda9 1.8 | 15,010,414 | 1.20 |
| Hutter phda9 | 15,284,944 | 1.22 |
| fractal pp96 | 19,594,333 | 1.57 |

Probe rows use `text_bpc = 8 × compressed_bytes / text_chars` (scaled from full-corpus convention). Summarizers sort ascending and flag rows **&lt; 1.0** and **&lt; 0.90**.

### Probe commands

```bash
php benchmarks/build_enwik8_sample_pages.php
php benchmarks/bench_enwik8_sub1_bpc_matrix.php --pages=sample5 --quick
php benchmarks/summarize_enwik8_sub1_bpc_matrix.php
php benchmarks/bench_enwik8_text_inner_layout_probe.php --pages=sample5 --quick
php benchmarks/summarize_enwik8_text_inner_layout_probe.php
bash benchmarks/run_enwik8_sub1_gpu_matrix.sh --pages=sample5
php benchmarks/bench_enwik8_stacked_outer_probe.php
php benchmarks/bench_fractal_substring_gpu_spike.php --pages=sample5
```

Output JSON: `benchmarks/.enwik8_sub1_bpc_matrix.json`, `.enwik8_text_inner_layout_probe.json`, `.enwik8_stacked_outer_probe.json`.

### sample5 results (2026-06-05, quick matrix)

Best sub-1 paths (`text_bpc < 1.0`, status `ok`):

| path | text_bpc |
|------|--------:|
| `dict_nncp` + `mi_reorder` + `zpaq9` | **0.842** |
| `dict_nncp` + `sort_title` + `zpaq9` | 0.842 |
| `dict_nncp` + `mono_concat` + `zpaq9` | 0.843 |
| `none` + layouts + `zpaq9` | ~2.0 (baseline) |

Neural adapters (`nncp32`, `llmzip_llama7b`) use gzip CPU fallback when binaries absent; install real tools + run `bash benchmarks/run_enwik8_sub1_gpu_matrix.sh` for GPU overnight jobs.

### 384p slice (2026-06-05)

| metric | value |
|--------|------:|
| `split_inner` raw (sort_title) | 2,983,470 B |
| `split_best_stack` (`zpaq9_brotli11`) | **652,404 B** |
| wire slice baseline (`no_textcodec`) | 658,258 B |
| stacked delta vs `single_best` | −2,331,066 B |

384p stacked outer beats raw inner by ~2.3 MiB.

**Wire slice (2026-06-05):** `split_text_inner` **658,113 B** vs `no_textcodec` **658,258 B** (−145 B, verify OK). Enable with `FRACTAL_ZIP_TEXT_INNER=1` + `FRACTAL_ZIP_TEXT_INNER_LAYOUT=sort_title`. Below 5 KiB promotion gate for default wire — opt-in until sub-1 path + larger slice win proven at scale.

## Portability (non-enwik corpora)

Text-inner machinery is corpus-agnostic via [`fractal_zip_content_format_policy.php`](../fractal_zip_content_format_policy.php):

| Hook | Role |
|------|------|
| `fractal_zip_identify_for_policy()` | Route `text_xml` / `text_plain` to text-inner path |
| `fractal_zip_enwik_split_shell_and_text()` | enwik XML; generalize to any XML-with-text-body |
| `fractal_zip_enwik_text_layout_apply()` | Layout/reorder on text chunks |
| `fractal_zip_text_preprocess_apply()` | Dictionary / WRT / ISP preprocess |
| `fractal_zip_text_compressor_run()` | External neural/CM models |
| `FRACTAL_ZIP_TEXT_INNER=1` | Wire dual virtual members (`.text` + `.shell.xml`) |

Enwik8 remains the lab corpus under [`docs/WORLD_RECORD_PRESET.md`](WORLD_RECORD_PRESET.md). Winners enable `FRACTAL_ZIP_TEXT_INNER` on other folders without requiring entry sort when policy matches.

## Related

- `docs/ENWIK8_INTEGRATED_COMPRESSION.md` — harmony / phrase tiers
- `benchmarks/ENWIK8_GRID_CONCLUSIONS.md` — topology winners (pp96)
- `benchmarks/ENWIK8_BYTES_PROBE.md` — wire slice baselines
