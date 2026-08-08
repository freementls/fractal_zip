#!/usr/bin/env bash
# Crash-proof campaign driver. Survives Cursor/session death (run under setsid).
# 1) Heartbeats the running 100MB baseline (RSS + input-read progress estimate).
# 2) When the gate script (run_hutter_100m_gate.sh) finishes, records outcome.
# 3) If RT_OK, auto-starts the UL6000 SEED sweep (one cmix at a time).
# Never touches enwik9.
set -uo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
PROGRESS="$ROOT/benchmarks/.hutter_100m_progress.json"
GATEJSON="$ROOT/benchmarks/.hutter_100m_gate.json"
STATE="$ROOT/benchmarks/.hutter_orchestrator.log"
PLAIN=100000000

log() { echo "$(date -Iseconds) $*" >>"$STATE"; }

gate_pid="$(pgrep -f 'run_hutter_100m_gate.sh' | head -1 || true)"
log "orchestrator start; gate_pid=${gate_pid:-none}"

# --- Phase 1: babysit the 100MB gate run ---
while [[ -n "${gate_pid:-}" ]] && kill -0 "$gate_pid" 2>/dev/null; do
  cpid="$(pgrep -x cmix_lto | head -1 || true)"
  if [[ -n "$cpid" ]]; then
    rss=$(awk '/VmRSS/ {print $2}' /proc/$cpid/status 2>/dev/null || echo 0)
    rbytes=$(awk '/^read_bytes/ {print $2}' /proc/$cpid/io 2>/dev/null || echo 0)
    # crude progress: bytes read vs input size (>100% possible: dict + multi-pass)
    pct=$(python3 -c "print(round(min($rbytes/$PLAIN*100,100),2))" 2>/dev/null || echo "?")
    python3 - <<PY 2>/dev/null
import json,datetime
json.dump({"name":"cmix_lto","phase":"compress-or-decompress","pid":$cpid,
  "rss_kb":$rss,"read_bytes":$rbytes,"read_pct_est":"$pct",
  "heartbeat":datetime.datetime.now().isoformat(timespec="seconds")},
  open("$PROGRESS","w"),indent=2)
PY
    log "heartbeat cmix pid=$cpid rss_kb=$rss read_est=${pct}%"
  else
    log "gate alive but no cmix process (between phases?)"
  fi
  sleep 300
done

log "gate script exited"
sleep 5

# --- Phase 2: record outcome ---
if [[ -f "$GATEJSON" ]]; then
  log "gate json: $(tr -d '\n' <"$GATEJSON")"
  rt=$(python3 -c "import json;print(json.load(open('$GATEJSON')).get('rt',''))" 2>/dev/null || echo "")
else
  rt=""
  log "WARN: no gate json found — gate run may have died; NOT starting sweep"
fi

# --- Phase 3: chain SEED sweep only on clean RT ---
if [[ "$rt" == "OK" ]]; then
  log "RT_OK — starting UL6000 seed sweep"
  bash "$ROOT/benchmarks/run_hutter_seed_sweep_ul6000.sh"
  log "seed sweep script returned $?"
else
  log "rt='$rt' — sweep NOT started (manual review needed)"
fi
log "orchestrator done"
