# fzcodec

A **general-purpose** buffer compressor you can link, call from a CLI, or load as a [Squash](https://github.com/quixdb/squash) plugin (`fz-lifestyle`, `fz-ultra`).

It is meant for other people, not only our in-repo PHP pipeline. Encode does **not** look at corpus names. The steps are:

1. **Sniff** the bytes (JPEG / already-packed magic, printable ratio, NUL ratio, size).
2. **Transcode** when a format-aware lossless transform exists (JPEG → [Lepton](https://github.com/dropbox/lepton) today).
3. **Race** codecs that are generally strong for that *class* (zstd / brotli / zlib in-process; `bsc`, `zpaq`, `paq8px` when installed).
4. Wrap the winner in a **self-describing FZC1** header so decode never re-runs the policy.

Lifestyle is the default (strong bytes, bounded CPU). Ultra is bytes-first and will run `paq8px` on larger inputs.

## Build

```bash
cd tools/fzcodec
make
make test
```

Needs a C11 compiler plus **libzstd**, **libbrotli**, and **zlib** (`pacman -S zstd brotli zlib` / `apt install libzstd-dev libbrotli-dev zlib1g-dev`).

```bash
make install PREFIX=/usr/local          # libfzcodec.a, fzcodec.h, fzcodec CLI, pkg-config
make install-plugin PREFIX=/usr/local   # squash.ini + .so; export SQUASH_PLUGINS=$PREFIX/lib/fzcodec-squash
```

Optional tools on `PATH` (or absolute paths in the env vars below) make the *same* library stronger for everyone — they are not Squash-only knobs:

| Tool | Env override | Typical win |
|---|---|---|
| `lepton` | `FZCODEC_LEPTON` | JPEG photos |
| `bsc` | `FZCODEC_BSC` | large highly-redundant text |
| `zpaq` / `zpaqfranz` | `FZCODEC_ZPAQ` | decode fallback for old CLI-journal FZC1 payloads only (encode uses vendored libzpaq) |
| `paq8px` | `FZCODEC_PAQ8PX` | small-to-medium anything (slow) |

In this repo, `make` also searches `benchmarks/.tools/bin/bsc` and `tools/jpeg/lepton/build/lepton`. libzpaq is vendored at `third_party/libzpaq/` (Mahoney, public domain) so a Squash in-tree copy of this directory does not need the rest of fractal_zip.

```bash
./build/fzcodec compress [--lifestyle|--ultra] IN OUT
./build/fzcodec decompress IN OUT
./build/fzcodec sniff IN
```

`IN`/`OUT` may be `-` for stdin/stdout.

## C API

```c
#include <fzcodec.h>

size_t cap = fz_max_compressed_size(n);
fz_compress(src, n, dst, &cap, FZ_PRESET_LIFESTYLE);
fz_decompress(enc, enc_n, out, &out_n);
```

Thread-safe: no shared mutable buffers. External tools use a private `mkdtemp` per call.

## Wire (FZC1)

| Offset | Size | Meaning |
|---|---|---|
| 0 | 4 | `FZC1` |
| 4 | 1 | version `1` |
| 5 | 1 | codec id (store, zstd, brotli, zlib, bsc, zpaq, paq8px, lepton) |
| 6 | 1 | flags (reserved) |
| 7 | 1 | reserved |
| 8 | 8 | uncompressed size, little-endian |
| 16 | … | codec payload |

Worst-case encoded size is `n + 16` (store). Expanding candidates lose the race.

## Policy (content, not filenames)

| Class | How it is decided | Lifestyle | Ultra |
|---|---|---|---|
| JPEG | `FF D8 FF` | Lepton | Lepton, then `paq8px` if under the paq cap |
| Packed | gzip/zstd/xz/zip/png/webp/… magic | store vs cheap zstd | same |
| Textlike | ≥90% printable, ≤1% NULs | `bsc` if ≥16 MiB; else `paq8px` ≤12 MiB (else in-process libzpaq/5); zstd/brotli if those tools are missing | `bsc`, then libzpaq / paq up to 48 MiB |
| High-print | ≥75% printable | libzpaq/5; paq ≤12 MiB | + paq if in cap |
| Mid-print | 28–40% printable | libzpaq/5; paq ≤12 MiB | `paq8px` up to 48 MiB |
| Tiny / general | size or leftover | in-process codecs, then libzpaq / paq by size | more aggressive paq |

Zpaq is **in-process libzpaq** (same stream as Squash `zpaq/5`), not a CLI journal. SHA-1 is off — FZC1 already stores the uncompressed size. On buffers under 2 MiB we also race method 4 (Squash’s B-best is `zpaq/4` on a few small files). Caps: `FZCODEC_PAQ_MAX_BYTES` (0 = preset default), `FZCODEC_DISABLE_PAQ=1`, `FZCODEC_PAQ_TIMEOUT_SEC`.

## Tests

```bash
make test                 # sniff, empty/1-byte/random/text, threads, garbage
make test-jpeg            # real JPEG transcode if test_files111 is present
make test-plugin          # load fz-lifestyle / fz-ultra via libsquash (synthetic)
make test-plugin-files    # same .so on JPEG, small text, large text (if fixtures exist)
make test-corpus-smoke        # files ≤40 KiB vs published B-best (uses paq8px)
make test-corpus-ultra-smoke  # same slice, fz-ultra
make test-corpus-medium       # 40 KiB–1.1 MiB vs B-best (paq8px on)
make test-corpus-large        # files ≥2 MiB with paq disabled (roundtrip)
make test-corpus-large-compare # same files vs B-best (report only)
make test-corpus              # all 28 single-file trees (slow; may run paq8px)
php tests/plugin_review.php   # freeze C-plugin vs B-best → benchmarks/.squash_plugin_review.json
```

`make plugin` / `make test-plugin` need `squash-0.8.pc`. This repo can build a local copy (no system install):

```bash
# after a one-time tinycthread/glibc once_flag patch in the clone (see below)
bash scripts/setup_local_squash.sh
make plugin test-plugin
# optional: same sources as a quixdb in-tree plugin
bash scripts/try_squash_intree.sh
```

`tests/test_corpus.php` is a **harness**. It knows the published Squash CSV sizes so we can check we still win. The compressor itself only sees a buffer.

On structured binaries the in-process stream plus a 16-byte FZC1 header is a few bytes *under* published `zpaq/5` because we omit the redundant per-block SHA-1. Small text where B-best is brotli/lzma still needs `paq8px` on PATH (seconds, not hours). Large highly-redundant text uses `bsc`. JPEG uses lepton. `make test-corpus-large-compare` turns paq off so the ≥2 MiB slice stays a zpaq/bsc check.

## Squash plugin

Sources: `plugins/squash/`. Official listing needs a C plugin the Squash maintainers can build ([plugin guide](https://github.com/quixdb/squash/blob/master/docs/plugin-guide.md)).

```bash
make plugin
export SQUASH_PLUGINS=$PWD/build/squash-plugins
export LD_LIBRARY_PATH=$PWD/.deps/squash-prefix/lib${LD_LIBRARY_PATH:+:$LD_LIBRARY_PATH}
# layout: $SQUASH_PLUGINS/fzcodec/{squash.ini,libsquash0.8-plugin-fzcodec.so}
```

The library and CLI do not need Squash. Official quixdb listing is this plugin, not the PHP folder `.fz` pipeline.

CMake works two ways:

```bash
cmake -S . -B cmake-build && cmake --build cmake-build && ctest --test-dir cmake-build
```

Squash source tree: submodule or copy this directory as `plugins/fzcodec` and add `fzcodec` to `plugins/CMakeLists.txt`. The root `CMakeLists.txt` sees `SQUASH_VERSION_API` and builds only the plugin (embeds libfzcodec). Needs system **libzstd**, **libbrotli**, **zlib**.

## Relation to `fractal_zip.php`

The PHP encoder is a **folder** pipeline (preprocess, PAC, FZpq wrappers). fzcodec is the **single-buffer** path we can ship to Squash and to other programs. It reuses the same *ideas* (sniff → transcode → class-shaped race) so benchmark wins come from general rules, not `if (nci)`.
