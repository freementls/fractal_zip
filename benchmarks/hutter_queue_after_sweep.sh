#!/usr/bin/env bash
# Waits for the UL6000 seed sweep to finish, then screens the dict_append365
# candidate at 1MB; if KEEP, follows with 10MB. Never starts enwik9.
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
FX2="$ROOT/tools/hutter/fx2-cmix"
RUN="$FX2/run"
SWEEPLOG="$ROOT/benchmarks/.hutter_logs/hutter_slice_queue.log"
LOG="$ROOT/benchmarks/.hutter_logs/queue_after_sweep.log"
# enwik9-derived fill (prize target); enwik8-derived variant kept as backup.
DICT365="$ROOT/benchmarks/.ladder_cache/dicts/english_append365_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B1=198744
B10=1751514
CMIX="$RUN/cmix_lto"

log() { echo "$(date -Iseconds) $*" >>"$LOG"; }
log "watcher start (waiting for seed sweep DONE)"

# Wait up to 30h for the sweep to complete.
for i in $(seq 1 1800); do
  if rg -q 'SEED sweep DONE' "$SWEEPLOG" 2>/dev/null; then
    log "sweep DONE detected"
    break
  fi
  if (( i == 1800 )); then
    log "TIMEOUT waiting for sweep; exiting without screens"
    exit 1
  fi
  sleep 60
done

# Let the last sweep job fully release resources.
sleep 60
bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$LOG" 2>&1 || { log "guard blocked; exiting"; exit 2; }

log "screening dict_append365 @1MB"
bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" dict365e9_1m "$CMIX" "$DICT365" "$SL1" "$B1" >>"$LOG" 2>&1
rm -f "$RUN/ppm.temp"
LAST=$(tail -1 "$ROOT/benchmarks/.hutter_slice_screens.jsonl")
log "1m result: $LAST"
GATE=$(python3 -c "import json;print(json.loads('''$LAST''').get('gate',''))" 2>/dev/null || echo parse_err)

if [[ "$GATE" == "KEEP" ]]; then
  bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$LOG" 2>&1 || { log "guard blocked before 10m"; exit 2; }
  log "1m KEEP -> screening dict_append365 @10MB"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" dict365e9_10m "$CMIX" "$DICT365" "$SL10" "$B10" >>"$LOG" 2>&1
  rm -f "$RUN/ppm.temp"
  log "10m result: $(tail -1 "$ROOT/benchmarks/.hutter_slice_screens.jsonl")"
else
  log "1m gate=$GATE -> stopping ladder for dict365"
fi
log "watcher done"
