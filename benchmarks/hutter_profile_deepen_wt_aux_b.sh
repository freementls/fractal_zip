#!/usr/bin/env bash
# Resume Profile-B deepen at WT_AUX only, using the better of B0 vs B1 dict.
# B1 KEEP vs A0 is not enough — must beat B0's 10m delta.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
RUN="$ROOT/tools/hutter/fx2-cmix/run"
DEEP="$ROOT/benchmarks/.hutter_logs/profile_deepen.log"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B1=198744
B10=1751514
JSONL="$ROOT/benchmarks/.hutter_slice_screens.jsonl"
DICT_B="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
DICT_B1="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_replace_e9.dic"

log() { echo "$(date -Iseconds) $*" >>"$DEEP"; }

guard() {
  HUTTER_MAX_SWAP_MIB=4096 bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$DEEP" 2>&1
}
wait_guard() {
  for i in $(seq 1 120); do guard && return 0; sleep 30; done; return 2
}
screen() {
  local tag="$1" cmix="$2" dict="$3" slice="$4" base="$5"
  wait_guard || { log "guard timeout before $tag"; return 2; }
  log "SCREEN $tag cmix=$(basename "$cmix") dict=$(basename "$dict")"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$tag" "$cmix" "$dict" "$slice" "$base" >>"$DEEP" 2>&1
  rm -f "$RUN/ppm.temp"
  log "RESULT $tag: $(tail -1 "$JSONL")"
}

USE=$(python3 - <<'PY'
import json
from pathlib import Path
rows={}
for l in Path("/srv/http/fractal_zip/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
    try: r=json.loads(l)
    except Exception: continue
    rows[r["name"]]=r
b0=rows.get("B0_entityfold_10m",{}).get("delta")
b1=rows.get("B1_entityfold_replace_10m",{}).get("delta")
# Prefer replace only if strictly better (more negative) than B0.
if b1 is not None and b0 is not None and b1 < b0:
    print("replace")
else:
    print("b0")
print(f"select: b0={b0} b1={b1}", file=__import__("sys").stderr)
PY
)

if [[ "$USE" == "replace" ]]; then
  DICT="$DICT_B1"
else
  DICT="$DICT_B"
fi
log "WT_AUX resume on Profile B with $(basename "$DICT") (pick=$USE; B1 must beat B0, not merely beat A0)"
screen B_wt_aux_1m "$RUN/cmix_wt_aux_entity" "$DICT" "$SL1" "$B1" || true
screen B_wt_aux_10m "$RUN/cmix_wt_aux_entity" "$DICT" "$SL10" "$B10" || true
log "deepen done"
