#!/usr/bin/env bash
# Wait for s5_r2@100m, log verdict, then screen ARCH+soft6 @1m (machine free).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/s5_r2_100m"
SPEED="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt"
LOG="$SPEED/after_s5r2_100m.log"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

log "WAIT s5_r2@100m pidfile=$OUT/run.pid"
while [[ -f "$OUT/run.pid" ]]; do
  if ! kill -0 "$(cat "$OUT/run.pid")" 2>/dev/null; then
    log "pidfile stale; checking completion"
    break
  fi
  sleep 60
done
# Also wait until no cmix_opt_s5_r2
while pgrep -f '/cmix_opt_s5_r2 ' >/dev/null; do sleep 30; done

log "s5_r2@100m finished; screen.log:"
tail -20 "$OUT/screen.log" 2>/dev/null | tee -a "$LOG" >&2 || true

# Refuse if another fractal cmix is live
if pgrep -f '/cmix_match3m_fractalv2' >/dev/null || pgrep -f '/cmix_opt_' >/dev/null; then
  log "REFUSE: another cmix still live — skip ARCH screen"
  exit 0
fi

log "START ARCH+soft6 @1m screen"
REFS='198791,322.1,198619,292.2,198673,408.4'
# arch384_only needs base_defs without soft — skip it; run first three
python3 -u "$ROOT/benchmarks/optimize_fxcm_lstm_speed.py" \
  --catalog "$ROOT/benchmarks/lstm_speed_opt_catalog_arch.json" \
  --slice 1m --rounds 1 \
  --reuse-refs "$REFS" \
  --only arch384_s6_r4,arch384_s6_r8,arch384_s6_r4_s5r2 \
  2>&1 | tee -a "$SPEED/screen_arch_1m.nohup.out" | tee -a "$LOG"
log "DONE ARCH 1m screen"
