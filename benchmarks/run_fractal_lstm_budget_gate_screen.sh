#!/usr/bin/env bash
# Screen LSTM256 + FXCM_BUDGET_LSTM_GATE vs fractalv2b baseline on sentinel mid.
# Stacks promoted static MIX_NUMLEN (bitmask=2).
# Usage: bash benchmarks/run_fractal_lstm_budget_gate_screen.sh [1m|10m|both]
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_budget_gate"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
LSTM="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
FXPM1="$ROOT/benchmarks/.ladder_cache/fractal_p5/tuned_1m.fxpm"
SCALE="${1:-1m}"
mkdir -p "$OUT"

log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

run_timed() {
  local tag="$1" bin="$2" inp="$3" out="$4"
  shift 4
  local t0 t1 sz
  t0=$(date +%s.%N)
  # stderr to file (progress); avoid capture deadlock
  env FXCM_RECIPE_MIXER_BITMASK=2 "$@" \
    "$bin" -c "$DICT" "$inp" "$out" >/dev/null 2>"$OUT/${tag}.err" || true
  t1=$(date +%s.%N)
  sz=$(stat -c%s "$out" 2>/dev/null || echo 0)
  python3 -c "print(f'{float('$t1')-float('$t0'):.1f}')" >"$OUT/${tag}.wall"
  local wall
  wall=$(cat "$OUT/${tag}.wall")
  log "$tag bytes=$sz wall=${wall}s"
  echo "$sz"
}

[[ -x "$LSTM" ]] || { log "missing $LSTM — build match3m_fractalv2_lstm256"; exit 1; }
[[ -x "$BASE" ]] || { log "missing $BASE"; exit 1; }

log "START scale=$SCALE"

if [[ "$SCALE" == "1m" || "$SCALE" == "both" ]]; then
  SL="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
  SENT="$OUT/enwik8_1m_sentinel.mid"
  python3 "$ROOT/benchmarks/inject_article_sentinels.py" inject "$SL" "$SENT"
  [[ -s "$FXPM1" ]] || { log "missing $FXPM1"; exit 1; }

  B=$(run_timed B1m_base "$BASE" "$SENT" "$OUT/B1m_base.fx2")
  F=$(run_timed B1m_lstm_full "$LSTM" "$SENT" "$OUT/B1m_lstm_full.fx2")
  G=$(run_timed B1m_lstm_gated "$LSTM" "$SENT" "$OUT/B1m_lstm_gated.fx2" \
    FXCM_PROFILE_MAP_PATH="$FXPM1")
  Bw=$(cat "$OUT/B1m_base.wall")
  Fw=$(cat "$OUT/B1m_lstm_full.wall")
  Gw=$(cat "$OUT/B1m_lstm_gated.wall")
  python3 - <<PY | tee -a "$LOG" >&2
B,F,G=int("$B"),int("$F"),int("$G")
Bw,Fw,Gw=float("$Bw"),float("$Fw"),float("$Gw")
print(f"B1m summary base={B} full={F} (Δ={F-B}) gated={G} (Δ={G-B}) gated_vs_full={G-F}")
print(f"B1m time base={Bw:.1f}s full={Fw:.1f}s gated={Gw:.1f}s speedup_vs_full={Fw/Gw if Gw else 0:.3f}x speedup_vs_base={Bw/Gw if Gw else 0:.3f}x")
# KEEP if gated bytes ≤ full+8 and wall clearly better than full; prize path needs vs base
if G <= F + 8 and Gw < Fw * 0.95:
    print("B1m_VERDICT: GATE_HELPS_TIME (bytes≈full)")
elif G < B and Gw <= Bw * 1.05:
    print("B1m_VERDICT: GATE_BEATS_BASE_TIME_OK")
elif G < B:
    print("B1m_VERDICT: GATE_BEATS_BASE_BYTES but slower — check 40h budget")
else:
    print("B1m_VERDICT: REJECT_vs_base or no time win")
PY
fi

if [[ "$SCALE" == "10m" || "$SCALE" == "both" ]]; then
  # Build 10m fxpm from hardness if missing
  SL="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
  SENT="$OUT/enwik8_10m_sentinel.mid"
  FXPM10="$OUT/tuned_10m.fxpm"
  python3 "$ROOT/benchmarks/inject_article_sentinels.py" inject "$SL" "$SENT"
  if [[ ! -s "$FXPM10" ]]; then
    log "building 10m fxpm via tuner (hardness pass on base binary)"
    python3 "$ROOT/benchmarks/run_fractal_tuner.py" \
      --binary "$BASE" --dict "$DICT" --slice "$SENT" \
      --out "$OUT/tuned_10m" 2>&1 | tee -a "$LOG" >&2
  fi
  B=$(run_timed B10m_base "$BASE" "$SENT" "$OUT/B10m_base.fx2")
  F=$(run_timed B10m_lstm_full "$LSTM" "$SENT" "$OUT/B10m_lstm_full.fx2")
  G=$(run_timed B10m_lstm_gated "$LSTM" "$SENT" "$OUT/B10m_lstm_gated.fx2" \
    FXCM_PROFILE_MAP_PATH="$FXPM10")
  Bw=$(cat "$OUT/B10m_base.wall"); Fw=$(cat "$OUT/B10m_lstm_full.wall"); Gw=$(cat "$OUT/B10m_lstm_gated.wall")
  python3 - <<PY | tee -a "$LOG" >&2
B,F,G=int("$B"),int("$F"),int("$G")
Bw,Fw,Gw=float("$Bw"),float("$Fw"),float("$Gw")
print(f"B10m summary base={B} full={F} (Δ={F-B}) gated={G} (Δ={G-B}) gated_vs_full={G-F}")
print(f"B10m time base={Bw:.1f}s full={Fw:.1f}s gated={Gw:.1f}s speedup_vs_full={Fw/Gw if Gw else 0:.3f}x")
PY
fi

log "DONE"
