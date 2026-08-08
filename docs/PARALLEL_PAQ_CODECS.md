# Parallel PAQ codecs (in-repo)

Hackable chunk-parallel CMIX/PAQ-style compressors under `tools/parallel_paq/`. These replace opaque `phda9` / `cmix` binaries for integration experiments where **source access** and **intra-compressor threading** matter.

## Architecture

| Layer | Role |
|-------|------|
| **Range coder** (`range.c`) | 12-bit probability arithmetic coding (encode/decode one bit at a time) |
| **Context mixer** (`mixer.c`) | Order 0–3 counter models (256 contexts × 4 orders), logistic mix, adaptive weights |
| **Chunk engine** (`parallel_paq.c`) | Splits input into 64 KiB chunks, 4 KiB cross-chunk warmup, pthread pool |

### FZPP container (`FZPP\x01`)

```
magic[5]      "FZPP\x01"
version u8    1
jobs u16      encode thread count used
chunk_size u32   65536
warmup_size u32  4096
dict_flag u8     1 if phda9 dict seeded models
orig_size u64
chunk_count u32
[chunk_off u64, chunk_len u32, raw_len u32, warm_len u32] × chunk_count
chunk bitstreams (concatenated)
```

Each chunk bitstream encodes **warmup prefix + raw chunk bytes**; `warm_len` records the warmup size stripped on decode so chunks stay **independently decodable** in parallel.

### `parallel_phda9` mode

Symlink to the same binary. When invoked as `parallel_phda9`, loads `FRACTAL_ZIP_PAQ_PHDA9_DICT` (or `--dict path`) and:

1. **Seeds** mixer counters from dictionary words (phda9 CRLF format: `80000` + 12 blank lines, CRLF word list).
2. **Tokenizes** input before chunking: dict words at word boundaries become `0xFD 0x00` + varint word-id escapes (`PP_DICT_FLAG_TOKENIZE` in header).

`parallel_cmix` uses the same engine without dict tokenization (seed-only if `--dict` passed manually).

### Spiral bigram seed (`FZBG\x01`)

Optional `--spiral-seed path` or `FRACTAL_ZIP_PAQ_SPIRAL_SEED` loads `FZBG\x01` (order-1 row) or `FZBG\x02` (+ sparse order-2 triple warmup). Export via `spiral/src/SpiralPaqSeed.php`. Probes: `bench_spiral_paq_seed.php` (seed regresses cmix), `bench_spiral_paq_residual.php` (cmix on SPRL residuals still ≫ gz9).

## FZPA member wrap (enwik text-inner)

When `FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_cmix|parallel_phda9`, `fractal_zip_parallel_paq_try_wrap_member()` may replace an fztx inner blob with FZPA wire. Auto-wrap applies only when:

1. Roundtrip check passes, and
2. FZPA wire is smaller than the raw inner blob, and
3. FZPA wire is smaller than `gzip -6` of the inner blob (outer fractal+zpaq usually beats naive member PAQ).

Set `FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ_FORCE=1` to wrap anyway (lab / codec tuning).

## Parallelism vs legacy tools

| Tool | Parallelism today | Ratio (enwik8 ref) |
|------|-------------------|---------------------|
| **phda9** (opaque) | Single-threaded; hours per file | ~15.01 MiB |
| **cmix** (opaque) | Single-threaded | ~14.6 MiB |
| **parallel_cmix** (`parallel_paq`) | `-j N` pthreads across 64 KiB chunks | **Much worse** (~gzip-1..gzip-6 class on text samples) — subset mixer only |
| **parallel_phda9** | Same + dict seed + word tokenization | **~76% of raw** on enwik 384p slice with mined dict (vs **~98%** cmix-only); still **~3.8× gzip-9** on that slice — far from opaque phda9 |

**Gap:** Production phda9/cmix use deep models (LSTM, sparse contexts, word handling, long training). Our in-repo subset is intentionally small so it can be modified. Expect **multi× larger** output than phda9 on enwik8 until models are extended.

## Environment

| Variable | Effect |
|----------|--------|
| `FRACTAL_ZIP_PAQ_PARALLEL_JOBS` | Default `-j` for `parallel_cmix` / `parallel_phda9` via `fractal_zip_paq.php` (defaults to detected CPU count) |
| `FRACTAL_ZIP_PAQ_PARALLEL_CMIX` | Path override for `parallel_cmix` binary |
| `FRACTAL_ZIP_PAQ_PARALLEL_PHDA9` | Path override for `parallel_phda9` binary |
| `FRACTAL_ZIP_PAQ_PHDA9_DICT` | phda9 dict file for `parallel_phda9` (and legacy phda9) |

Legacy opaque binaries remain the fallback when parallel tools are not built or when ratio matters more than hackability.

## Build & smoke

```bash
bash tools/parallel_paq/build.sh
php benchmarks/smoke_parallel_paq.php
php benchmarks/bench_parallel_paq_enwik_slice.php --pages=384
php benchmarks/smoke_paq_tools.php
```

## Sorted fzc integration

When `FRACTAL_ZIP_TEXT_INNER_MEMBER_PARALLEL_PAQ=parallel_phda9` (or `parallel_cmix`), `fractal_zip_parallel_paq.php` wraps each fztx **member inner blob** with **FZPA** wire if parallel compress beats raw inner size and roundtrips. For `dict_phda9_inner`, the shared mined vocab is written to a temp phda9 dict for tokenization.

Wire probe: `split_inner_fztx_mono_mi_dict_phda9_inner_parallel_paq`

## Extension path

1. Order-5+ / SSE contexts in `mixer.c` (order-4 added)
2. Shared frozen `.fzpm` stationary model + per-chunk suffix adaptation
3. OpenMP inside mixer update loop for single-chunk MT

See also: `docs/PHDA9_TOOLING_PARITY.md`, `docs/FRACTAL_ZIP_PARALLELIZATION.md`.
