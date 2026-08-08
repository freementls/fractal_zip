#!/usr/bin/env bash
# After soft6@100m: log verdict, then screen 4× Pareto (LSTM_STRIDE re-arch).
# Also briefly screens soft6+outer_fp if time — 4× is the priority.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/soft6_100m"
SPEED="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt"
LOG="$SPEED/after_soft6_100m.log"
mkdir -p "$SPEED"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

log "WAIT soft6@100m pidfile=$OUT/run.pid"
while [[ -f "$OUT/run.pid" ]] || pgrep -f 'tools/hutter/fx2-cmix/run/cmix_opt_softskip6' >/dev/null 2>&1; do
  sleep 60
done
log "soft6@100m finished; screen.log:"
tail -30 "$OUT/screen.log" | tee -a "$LOG" >&2

# --- 4× Pareto screen @1m (relaxed byte gates; sort by wall× vs 320) ---
log "START 4x catalog @1m (LSTM_STRIDE + mid LSTM + outer FP)"
python3 "$ROOT/benchmarks/optimize_fxcm_lstm_speed.py" \
  --catalog "$ROOT/benchmarks/lstm_speed_opt_catalog_4x.json" \
  --slice 1m --rounds 1 \
  --tag-prefix x4 \
  --baseline-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
  --beat-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
  --reuse-refs '198791,322.1,198619,292.2,198619,292.2' \
  --max-tax-vs-384 50000 \
  --min-beat-320 -50000 \
  --min-speedup-vs-384 3.5 \
  --max-vs-base 1.0 \
  2>&1 | tee -a "$LOG"

# Pick best arms: ≥3.5× vs 320 wall and lowest bytes among those; promote top to 10m
promotes=$(python3 - <<'PY'
import json
from pathlib import Path
p=Path('/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/leaderboard_1m.json')
d=json.loads(p.read_text())
w320=float(d.get('ref',{}).get('lstm320_wall') or 292.2)
b320=int(d.get('ref',{}).get('lstm320_bytes') or 198619)
cands=[]
for r in d.get('results',[]):
  if not r.get('bytes') or not r.get('wall_s'): continue
  if not any(r['name'].startswith(p) for p in ('l128_','l160_','l192_','l256_','l320_')):
    continue
  sp=w320/float(r['wall_s'])
  cands.append((r['bytes'], -sp, r['name'], sp, r['bytes']-b320))
# prefer fastest that is ≥3.5×, else best speed×bytes trade
fast=[c for c in cands if c[3]>=3.5]
fast.sort()  # lowest bytes first among 4×-ish
if not fast:
  cands.sort(key=lambda c:(c[1], c[0]))  # most speed, then bytes
  fast=cands[:4]
else:
  fast=fast[:4]
print(','.join(c[2] for c in fast))
for c in fast:
  print(f"# {c[2]} ×320={c[3]:.2f} ΔB={c[4]:+d}", flush=True)
PY
)
# strip comment lines from promotes
promotes=$(echo "$promotes" | rg -v '^#' | tr -d '\n')
if [[ -n "$promotes" ]]; then
  log "PROMOTE to 10m (4x pareto): $promotes"
  python3 "$ROOT/benchmarks/optimize_fxcm_lstm_speed.py" \
    --catalog "$ROOT/benchmarks/lstm_speed_opt_catalog_4x.json" \
    --slice 10m --rounds 1 --skip-build --only "$promotes" \
    --tag-prefix x410 \
    --baseline-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
    --beat-bin "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320" \
    --reuse-refs '1750867,2368.6,1747495,2918.1,1747495,2918.1' \
    --max-tax-vs-384 50000 \
    --min-beat-320 -50000 \
    --min-speedup-vs-384 3.5 \
    --max-vs-base 1.0 \
    2>&1 | tee -a "$LOG"
else
  log "No 4x candidates to promote"
fi
log DONE
