#!/usr/bin/env bash
# Stage 4: after the dict-replace watcher finishes, screen the entity-fold
# binary (cmix_entity, FXCM_ENTITY_FOLD) with the stock dictionary.
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
RUN="$ROOT/tools/hutter/fx2-cmix/run"
PREVLOG="$ROOT/benchmarks/.hutter_logs/queue_dict_replace.log"
LOG="$ROOT/benchmarks/.hutter_logs/queue_entity.log"
DICT="$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B1=198744
B10=1751514
CMIX="$RUN/cmix_entity"

log() { echo "$(date -Iseconds) $*" >>"$LOG"; }
log "stage4 watcher start (waiting for stage3 done)"

for i in $(seq 1 3000); do
  if rg -q 'stage3 done' "$PREVLOG" 2>/dev/null; then
    log "stage3 done detected"
    break
  fi
  if (( i == 3000 )); then log "TIMEOUT; exiting"; exit 1; fi
  sleep 60
done

sleep 60
bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$LOG" 2>&1 || { log "guard blocked; exiting"; exit 2; }

log "screening entity_fold @1MB"
bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" entity_1m "$CMIX" "$DICT" "$SL1" "$B1" >>"$LOG" 2>&1
rm -f "$RUN/ppm.temp"
LAST=$(tail -1 "$ROOT/benchmarks/.hutter_slice_screens.jsonl")
log "1m result: $LAST"
GATE=$(python3 -c "import json;print(json.loads('''$LAST''').get('gate',''))" 2>/dev/null || echo parse_err)

if [[ "$GATE" == "KEEP" ]]; then
  bash "$ROOT/benchmarks/hutter_memory_guard.sh" >>"$LOG" 2>&1 || { log "guard blocked before 10m"; exit 2; }
  log "1m KEEP -> @10MB"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" entity_10m "$CMIX" "$DICT" "$SL10" "$B10" >>"$LOG" 2>&1
  rm -f "$RUN/ppm.temp"
  log "10m result: $(tail -1 "$ROOT/benchmarks/.hutter_slice_screens.jsonl")"
else
  log "1m gate=$GATE -> stop"
fi
log "stage4 done"
