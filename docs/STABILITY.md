# Stability Checklist

`fz` should be considered generally stable only when the release candidate passes:

- `bash tests/run_php_smokes.sh`
- `bash tests/run_pipeline_tests.sh`
- `bash tests/run_stability.sh`
- (Large-tree ratio spot-check) `bash benchmarks/run_large_corpus_bytes_push.sh --only=<corpus> --large --json --no-case-timeout` (add **`--out-json=/path/bench.json`** to keep a copy; default **`benchmarks/.last_bench.json`** is **gitignored** — **`benchmarks/LARGE_CORPUS_SPEED.md`**, JSON machine output) on a representative slice, then **`php benchmarks/report_bytes_wins.php --compress-time-audit /path/bench.json`**.

The stability gate performs isolated corpus round trips and randomized tiny-folder
round trips. It also checks that unsafe decoded member paths are rejected.

## Stable By Default

Default compression should favor bounded, verified paths:

- Folder **`.fz`** round-trip: on-disk member bytes must match strict SHA1 verify; literal semantic ZIP/7z/… shells that would decode to different bytes are coerced to raw (see **`literal_bundle_coerce_verbatim_disk_roundtrip`** in `fractal_zip.php`, smoke **`benchmarks/smoke_repro_folder_zip_roundtrip.php`**).
- Production fractal-inner candidates must verify through `unzip()`.
- Peeler and shuffle transforms must carry exact or typed restore metadata.
- Typed restore specs must round-trip through `FZR1` trailer encode/decode.
- Expensive deeper searches should remain behind conservative caps or explicit env overrides.

## Experimental Surface

These are production-accessible but should still be treated as evolving until
more cross-version compatibility tests exist:

- New `FZR1` typed restore kinds.
- New peeler/shuffle transforms.
- Newly composed marker rows.
- Deep fractal-inner search knobs and high-effort outer tournament settings.

## Release Notes To Capture

For each release candidate, record:

- Corpus list used for `tests/stability_harness.php`.
- Fuzz seed and iteration count.
- External codecs available on the machine.
- Any env overrides from defaults.
- Total failures, timeouts, and known skipped cases.
