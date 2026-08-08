#!/usr/bin/env bash
# Consistent Hutter ladder after 100MB baseline.
# Profiles A1 and B0 are ALTERNATIVES — see .hutter_preprocess_consistency.md
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
RUN="$ROOT/tools/hutter/fx2-cmix/run"
LOGDIR="$ROOT/benchmarks/.hutter_logs"
mkdir -p "$LOGDIR"
LOG="$LOGDIR/consistent_ladder.log"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B1=198744
B10_A0=1751514

log() { echo "$(date -Iseconds) $*" >>"$LOG"; }

guard() {
  HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-4096}" \
    bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$LOG" 2>&1
}

wait_guard() {
  local tag="$1" i
  for i in $(seq 1 120); do
    if guard; then return 0; fi
    log "wait_guard $tag attempt $i"
    sleep 30
  done
  log "wait_guard TIMEOUT $tag"
  return 2
}

# Prints ONLY the gate token on stdout. All chatter goes to $LOG.
screen() {
  local tag="$1" cmix="$2" dict="$3" slice="$4" base="$5"
  if ! wait_guard "$tag"; then
    echo "BLOCKED"
    return 0
  fi
  log "SCREEN $tag cmix=$(basename "$cmix") dict=$(basename "$dict")"
  if ! bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$tag" "$cmix" "$dict" "$slice" "$base" >>"$LOG" 2>&1; then
    log "screen script failed for $tag"
    echo "FAIL"
    return 0
  fi
  rm -f "$RUN/ppm.temp"
  local last gate
  last=$(tail -1 "$ROOT/benchmarks/.hutter_slice_screens.jsonl")
  log "RESULT $tag: $last"
  gate=$(python3 -c "import json,sys; print(json.loads(sys.argv[1]).get('gate',''))" "$last" 2>/dev/null || echo parse_err)
  echo "$gate"
}

log "=== consistent ladder START ==="
log "doc: benchmarks/.hutter_preprocess_consistency.md"
log "100m A0 baseline bytes=14966085 gate_need<=14786491"

G=$(screen A1_dict365e9_1m "$RUN/cmix_lto" \
  "$ROOT/benchmarks/.ladder_cache/dicts/english_append365_e9.dic" "$SL1" "$B1")
log "A1 1m gate=$G"
screen A1_dict365e9_10m "$RUN/cmix_lto" \
  "$ROOT/benchmarks/.ladder_cache/dicts/english_append365_e9.dic" "$SL10" "$B10_A0" >/dev/null
log "A1 10m done"

G=$(screen B0_entityfold_1m "$RUN/cmix_entity" \
  "$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic" "$SL1" "$B1")
log "B0 1m gate=$G"
screen B0_entityfold_10m "$RUN/cmix_entity" \
  "$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic" "$SL10" "$B10_A0" >/dev/null
log "B0 10m done"

log "=== consistent ladder DONE ==="
log "Pick better profile; deepen within it. Do NOT stack A1+B0."
