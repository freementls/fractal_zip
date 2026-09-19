# Squash plugin: fz-lifestyle / fz-ultra

This directory is the [Squash](https://github.com/quixdb/squash) drop-in for libfzcodec.

```
license=MIT
[fz-lifestyle]   # default, content-aware, speed-aware
[fz-ultra]       # same pipeline, bytes-first
```

## Out of tree

```bash
cd ../..
# optional local SDK: bash scripts/setup_local_squash.sh
make plugin test-plugin test-plugin-files
export SQUASH_PLUGINS=$PWD/build/squash-plugins
# or: make install-plugin PREFIX=/usr/local
#      export SQUASH_PLUGINS=/usr/local/lib/fzcodec-squash
```

## In a Squash source tree

Submodule or copy `tools/fzcodec` to `plugins/fzcodec`, then add `fzcodec` to `plugins/CMakeLists.txt` (`plugins_available`). The root CMakeLists detects `SQUASH_VERSION_API` and calls `squash_plugin()`. Squash’s `C_STANDARD` token is `c11` (not `11`). Verify a local clone with `bash scripts/try_squash_intree.sh` (after `setup_local_squash.sh`).

Needs system **libzstd**, **libbrotli**, and **zlib** at configure time. libzpaq is vendored in `third_party/libzpaq/` (method 4/5, no SHA-1). Copy or submodule the whole `tools/fzcodec` tree — do not expect the fractal_zip repo root.

Optional runtime tools on `PATH` (not compile-time): `lepton` (JPEG), `bsc` (large redundant text), `paq8px` (small files where B-best is brotli/lzma). The library degrades to the in-process codecs if they are missing. For the published Squash bars, install `paq8px` and `bsc` / lepton when you have those file types.

Thread-safe: buffer API only; each call uses private heap and `mkdtemp`.

## Upstream

Requested: [quixdb/squash#256](https://github.com/quixdb/squash/issues/256). Official numbers appear only after this plugin is in [quixdb/squash](https://github.com/quixdb/squash) and they re-run the harness. Typical ask: issue or PR that adds `plugins/fzcodec` (this directory as the `tools/fzcodec` tree), `fzcodec` in `plugins/CMakeLists.txt`, and system **libzstd** / **libbrotli** / **zlib**. Runtime tools (`paq8px`, `bsc`, `lepton`) are optional but needed to match the published bars on small brotli/lzma rows, large redundant text, and JPEG.
