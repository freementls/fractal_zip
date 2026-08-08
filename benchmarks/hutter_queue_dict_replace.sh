#!/usr/bin/env bash
# Stage 3: after the dict365e9 watcher finishes, screen the stacked
# replace-low variant (append365_e9 + 235 in-place zone3 replacements).
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
RUN="$ROOT/tools/hutter/fx2-cmix/run"
PREVLOG="$ROOT/benchmarks/.hutter_logs/queue_after_sweep.log"
LOG="$ROOT/benchmarks/.hutter_logs/queue_dict_replace.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_replace_low_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B1=198744
B10=1751514
CMIX="$RUN/cmix_lto"

log() { echo "$(date -Iseconds) $*" >>"$LOG"; }
log "stage3 watcher start (waiting for dict365e9 watcher done)"

for i in $(seq 1 2400); do
  if rg -q 'watcher done' "$PREVLOG" 2>/dev/null; then
    log "stage2 done detected"
    break
  fi
  if (( i == 2400 )); then log "TIMEOUT; exiting"; exit 1; fi
  sleep 60
done

sleep 60
bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$LOG" 2>&1 || { log "guard blocked; exiting"; exit 2; }

log "screening dict_replace_low @1MB"
bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" dictrepl_1m "$CMIX" "$DICT" "$SL1" "$B1" >>"$LOG" 2>&1
rm -f "$RUN/ppm.temp"
LAST=$(tail -1 "$ROOT/benchmarks/.hutter_slice_screens.jsonl")
log "1m result: $LAST"
GATE=$(python3 -c "import json;print(json.loads('''$LAST''').get('gate',''))" 2>/dev/null || echo parse_err)

if [[ "$GATE" == "KEEP" ]]; then
  bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$LOG" 2>&1 || { log "guard blocked before 10m"; exit 2; }
  log "1m KEEP -> @10MB"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" dictrepl_10m "$CMIX" "$DICT" "$SL10" "$B10" >>"$LOG" 2>&1
  rm -f "$RUN/ppm.temp"
  log "10m result: $(tail -1 "$ROOT/benchmarks/.hutter_slice_screens.jsonl")"
else
  log "1m gate=$GATE -> stop"
fi
log "stage3 done"
