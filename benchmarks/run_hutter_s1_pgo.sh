#!/usr/bin/env bash
# S1 via the published fx2-cmix pipeline: PGO (prof_gen -> profile -> prof_use)
# + upx + self-extracting packaging. Requires llvm-profdata (user-local OK).
# Safe to run alongside the 100MB baseline: profiling input is 50 KB and the
# build stays out of run/ until the final copy.
set -euo pipefail
export PATH="$HOME/.local/bin:$PATH"
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX2="$ROOT/tools/hutter/fx2-cmix"
PUBLISHED_S1=441463

command -v llvm-profdata >/dev/null || { echo "need llvm-profdata"; exit 1; }
command -v upx >/dev/null || { echo "need upx"; exit 1; }

cd "$FX2"
echo "=== S1 PGO build START $(date -Iseconds) ==="
# nice 19 so the running 100MB baseline keeps CPU priority (it is single-threaded;
# host has 16 cores, so contention is minimal anyway).
nice -n 19 bash ./build_and_construct_comp.sh

S1=$(stat -c%s run/cmix)
python3 - <<PY
import json
s1=$S1; pub=$PUBLISHED_S1
print(f"S1={s1} published={pub} delta={s1-pub} {'BEATS' if s1<=pub else 'ABOVE'} published")
json.dump({"S1": s1, "published": pub, "delta": s1-pub,
  "beats_published": s1<=pub, "pipeline": "pgo+upx+selfextract (published)",
  "artifact": "$FX2/run/cmix"},
  open("$ROOT/benchmarks/.hutter_s1_pgo.json","w"), indent=2)
PY
# Preserve the PGO core for later prize packaging (SEED=923 UL=3000)
cp -a run/cmix run/cmix_s1_pgo_packaged
echo "=== S1 PGO build DONE $(date -Iseconds) ==="
