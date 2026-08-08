#!/usr/bin/env bash
# SEED micro-sweep on top of UPDATE_LIMIT=6000 at 10MB (one at a time).
# Only run AFTER 100MB baseline slot is free. Does NOT start enwik9.
# Usage: bash benchmarks/run_hutter_seed_sweep_ul6000.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX2="$ROOT/tools/hutter/fx2-cmix"
RUN="$FX2/run"
DICT="$FX2/dictionary/english.dic"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B10=1751514
LOG="$ROOT/benchmarks/.hutter_logs/hutter_slice_queue.log"
mkdir -p "$(dirname "$LOG")"
UL=6000
exec >>"$LOG" 2>&1
echo "=== SEED sweep UL=$UL START $(date -Iseconds) ==="

for SEED in 42 7 2024; do
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  tag="tuning_ul${UL}_s${SEED}_10m"
  echo "=== build $tag ==="
  make -C "$FX2" clean >/dev/null
  make -C "$FX2" lto CFLAGS_DEFINES="-DSEED=$SEED -DUPDATE_LIMIT=$UL"
  cp -a "$FX2/cmix" "$RUN/cmix_ul${UL}_s${SEED}"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$tag" "$RUN/cmix_ul${UL}_s${SEED}" "$DICT" "$SL10" "$B10"
  rm -f "$RUN/ppm.temp"
  # Update durable summary
  python3 - <<PY
import json
from pathlib import Path
rows=[json.loads(l) for l in Path("$ROOT/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines() if l.strip()]
keeps=[r for r in rows if r.get("gate")=="KEEP" and r.get("rt")=="OK"]
Path("$ROOT/benchmarks/.hutter_world_record_state.md").read_text()  # touch existence
print("latest", rows[-1].get("name"), "delta", rows[-1].get("delta"), "keeps", len(keeps))
PY
done
echo "=== SEED sweep DONE $(date -Iseconds) ==="
