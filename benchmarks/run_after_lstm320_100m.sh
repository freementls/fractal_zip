#!/usr/bin/env bash
# Wait for LSTM320 @100m scale-gate to finish, then run axis-4 fair@10m.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
GATE_LOG="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m/screen.log"
CHAIN_LOG="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/chain_after_100m.log"
mkdir -p "$(dirname "$CHAIN_LOG")"
echo $$ >"$ROOT/benchmarks/.ladder_cache/fractal_dictlab/chain_after_100m.pid"
log() { echo "$(date -Is) $*" | tee -a "$CHAIN_LOG" >&2; }

log "WAIT for DONE in $GATE_LOG"
found=0
for i in $(seq 1 1440); do  # up to 24h @60s
  if [[ -f "$GATE_LOG" ]] && rg -q ' DONE$' "$GATE_LOG" 2>/dev/null; then
    log "100m screen DONE — summary:"
    rg -n 'VERDICT:|100m base=|B100m_' "$GATE_LOG" | tee -a "$CHAIN_LOG" >&2 || true
    found=1
    break
  fi
  if (( i % 30 == 0 )); then
    prog=""; prog2=""
    if [[ -f "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m/B100m_base.err" ]]; then
      prog=$(tr '\r' '\n' < "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m/B100m_base.err" | rg 'progress' | tail -1 || true)
    fi
    if [[ -f "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m/B100m_lstm320.err" ]]; then
      prog2=$(tr '\r' '\n' < "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m/B100m_lstm320.err" | rg 'progress' | tail -1 || true)
    fi
    log "still waiting #$i ${prog:-} ${prog2:-}"
  fi
  sleep 60
done

if [[ "$found" != "1" ]]; then
  log "TIMEOUT waiting for 100m"
  rm -f "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/chain_after_100m.pid"
  exit 1
fi

for j in 1 2 3 4 5 6; do
  if pgrep -f '/run/cmix_match3m_fractalv2' >/dev/null 2>&1; then
    log "cmix still exiting… ($j)"
    sleep 10
  else
    break
  fi
done

# Append 100m verdict into the living lab log (no manual paste needed).
PROFILE="$ROOT/benchmarks/.hutter_fractal_block_profile.md"
{
  echo ""
  echo "### LSTM320 @100 MB scale-gate — auto-logged $(date -Is)"
  echo ""
  echo '```'
  rg -n 'VERDICT:|100m base=|B100m_|BUDGET:' "$GATE_LOG" || cat "$GATE_LOG"
  echo '```'
  echo ""
  echo "Artifacts: \`benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m/\`."
} >>"$PROFILE"
log "appended 100m result to profile"

# Prefer legalizing LSTM384 (bytes we already have) over axis-4 fair.
log "LAUNCH LSTM speed-opt @256k (catalog)"
mkdir -p "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt"
python3 "$ROOT/benchmarks/optimize_fxcm_lstm_speed.py" --slice 256k --rounds 2 \
  >>"$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/chain.nohup.out" 2>&1 \
  || log "speed_opt_256k exit=$?"
{
  echo ""
  echo "### LSTM speed-opt @256 k — auto-logged $(date -Is)"
  echo ""
  echo '```'
  python3 -c "import json;p='$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/leaderboard_256k.json';
import pathlib;print(pathlib.Path(p).read_text() if pathlib.Path(p).is_file() else 'missing')" 2>/dev/null || true
  echo '```'
  echo ""
  echo "See \`.hutter_lstm_speed_opt.md\`."
} >>"$PROFILE"
log "appended speed-opt result to profile"

# Promote any PROMOTE names to 1m
PROM=$(python3 - <<'PY'
import json
from pathlib import Path
p=Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/leaderboard_256k.json")
if not p.is_file():
    print(""); raise SystemExit
d=json.loads(p.read_text())
print(",".join(r["name"] for r in d.get("results",[]) if r.get("verdict")=="PROMOTE"))
PY
)
if [[ -n "$PROM" ]]; then
  log "LAUNCH speed-opt promote @1m only=$PROM"
  python3 "$ROOT/benchmarks/optimize_fxcm_lstm_speed.py" --slice 1m --rounds 2 \
    --only "$PROM" --skip-build \
    >>"$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/chain.nohup.out" 2>&1 \
    || log "speed_opt_1m exit=$?"
fi

log "LAUNCH axis-4 fair @10m"
mkdir -p "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/reorder_fair_10m"
bash "$ROOT/benchmarks/run_fractal_reorder_fair_10m.sh" \
  >>"$ROOT/benchmarks/.ladder_cache/fractal_dictlab/reorder_fair_10m/chain.nohup.out" 2>&1 \
  || log "fair_10m exit=$?"

FAIR_LOG="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/reorder_fair_10m/screen.log"
{
  echo ""
  echo "### Axis-4 fair@10 MB ladder — auto-logged $(date -Is)"
  echo ""
  echo '```'
  rg -n 'VERDICT:|RESULT |10m_fair|EARLY_STOP' "$FAIR_LOG" 2>/dev/null || echo "(no fair log)"
  echo '```'
  echo ""
  echo "Artifacts: \`benchmarks/.ladder_cache/fractal_dictlab/reorder_fair_10m/\`."
} >>"$PROFILE"
log "appended fair result to profile"

log "CHAIN_DONE"
rm -f "$ROOT/benchmarks/.ladder_cache/fractal_dictlab/chain_after_100m.pid"
