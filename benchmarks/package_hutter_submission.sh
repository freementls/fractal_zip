#!/usr/bin/env bash
# Package a prize-shaped fx2-cmix submission directory (source + build + docs).
# Does NOT claim a record — packages the local fork for hrules-shaped work.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX2="$ROOT/tools/hutter/fx2-cmix"
OUT="$ROOT/tools/hutter/submission_package"
STAMP=$(date +%Y%m%d)
mkdir -p "$OUT"

# Snapshot pointers / docs (not a full recursive copy of .git)
cat >"$OUT/README_SUBMISSION.md" <<'EOF'
# Hutter Prize submission package (scaffold)

This package is the **submission shape** for Path 1 of the fractal_zip Hutter plan:
fork of fx2-cmix, not the PHP fractal_zip pipeline.

## Official rules (summary)

- Corpus: **enwik9**
- `S = compressor_executable + self_extracting_archive`
- Need `S < 109,685,197` (≥1% under L=110,793,128)
- Single-core CPU, **no GPU**, ≤10 GiB RAM, ≤100 GiB disk, ≲50 h/phase
- Lossless restore with **no external input** at decompress time
- OSI-approved source; 30-day public comment

## Build (on Ubuntu-like host with ≥10 GiB RAM)

```bash
cd tools/hutter/fx2-cmix
# Prefer official PGO path when clang-17 + llvm-profdata available:
#   ./build_and_construct_comp.sh
# Local adapted build (clang++ / upx, mmap_to_disk=false for stability on small RAM):
make CFLAGS_DEFINES='-DSEED=923 -DUPDATE_LIMIT=3000' cmix -j$(nproc)
# Then embed dict+order as in build_and_construct_comp.sh (run/ steps).
```

## Compress / decompress

```bash
cd tools/hutter/fx2-cmix/run
./cmix -e /path/to/enwik9 enwik9.comp   # produces archive9
./archive9                              # restores enwik9_restored
```

## Local machine status (2026-07-11)

- Built and smoke-tested on 7 GiB host.
- **Local enwik9 path:** `make ram7` → `run/cmix_ram7` (fits ≤7 GiB; S2 not prize-comparable).
- **Full prize-profile enwik9** still needs ≥~9.5 GiB peak (≤10 GiB cap).
- Do **not** submit fractal_zip `.fz` / PHP pipeline as a prize entry.

## License

fx2-cmix: GPL-2.0 (see `tools/hutter/fx2-cmix/LICENSE`).
EOF

cp "$ROOT/benchmarks/HUTTER_PRIZE_WIN_PATH.md" "$OUT/"
cp "$ROOT/docs/HUTTER_PRIZE_COMPLIANCE.md" "$OUT/" 2>/dev/null || true

# Record sizes
{
  echo "generated=$STAMP"
  echo "L=110793128"
  echo "prize_S_max=109685196"
  if [[ -f "$FX2/run/cmix_baseline" ]]; then echo "S1_local_baseline=$(stat -c%s "$FX2/run/cmix_baseline")"; fi
  if [[ -f "$FX2/run/cmix_improved" ]]; then echo "S1_local_upx=$(stat -c%s "$FX2/run/cmix_improved")"; fi
  echo "S1_published=441463"
  echo "S2_published=110351665"
  echo "note=local_enwik9_via_ram7;_full_fx2_needs_ge_10GiB_peak"
} >"$OUT/SIZES.txt"

# Manifest of source tree (paths only)
( cd "$ROOT/tools/hutter" && find fx2-cmix -type f ! -path '*/.git/*' ! -name '*.o' | sort ) >"$OUT/SOURCE_MANIFEST.txt"

echo "Wrote submission scaffold to $OUT"
ls -la "$OUT"
