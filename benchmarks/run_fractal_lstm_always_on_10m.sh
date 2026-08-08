#!/usr/bin/env bash
# Always-on LSTM256 scale-gate @10 MB vs fractalv2b (no profile-map / no gate).
# Protocol matches banked MIX_NUMLEN screens: raw mid, entityfold, bitmask=2.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_always_on_10m"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
LSTM="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
SL="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
# Banked static MIX_NUMLEN @10m reference (non-sentinel)
BANKED_BASE=1750867
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -x "$BASE" && -x "$LSTM" && -s "$SL" ]] || { log "missing binary or slice"; exit 1; }

run_timed() {
  local tag="$1" bin="$2"
  local t0 t1 sz wall
  t0=$(date +%s.%N)
  env FXCM_RECIPE_MIXER_BITMASK=2 \
    "$bin" -c "$DICT" "$SL" "$OUT/${tag}.fx2" >/dev/null 2>"$OUT/${tag}.err" || true
  t1=$(date +%s.%N)
  sz=$(stat -c%s "$OUT/${tag}.fx2" 2>/dev/null || echo 0)
  wall=$(python3 -c "print(f'{float('$t1')-float('$t0'):.1f}')")
  echo "$wall" >"$OUT/${tag}.wall"
  log "$tag bytes=$sz wall=${wall}s"
  echo "$sz"
}

log "START always-on LSTM256 @10m (no map) banked_base_ref=$BANKED_BASE"
B=$(run_timed B10m_base "$BASE")
F=$(run_timed B10m_lstm_full "$LSTM")
Bw=$(cat "$OUT/B10m_base.wall")
Fw=$(cat "$OUT/B10m_lstm_full.wall")

python3 - <<PY | tee -a "$LOG" >&2
B, F = int("$B"), int("$F")
Bw, Fw = float("$Bw"), float("$Fw")
banked = $BANKED_BASE
d = F - B
print(f"B10m summary base={B} (banked_ref={banked} drift={B-banked:+d}) "
      f"lstm_full={F} (Δ={d:+d})")
print(f"B10m time base={Bw:.1f}s full={Fw:.1f}s ratio={Fw/Bw if Bw else 0:.3f}x "
      f"extrapolate_100m_full_h={(Fw/10)*100/3600:.1f}h")
# Scale-consistency vs 1m quiet Δ=-40 on sentinel (directional)
if d <= -8:
    # rough: if 10m holds ~10× the 1m win → ~-400; if flat → small KEEP
    print(f"BYTES: KEEP_DIRECTIONAL Δ={d:+d} @10m (1m quiet was -40)")
    if Fw <= Bw * 1.35:
        print("TIME: OK_vs_base (<1.35× @10m)")
    else:
        print(f"TIME: TAX ratio={Fw/Bw:.2f}× — check 40h budget at full")
    # 40h class: match3m ~47h historically; flag if extrapolate >> 40
    h100 = (Fw / 10.0) * 100.0 / 3600.0
    if h100 <= 40:
        print(f"BUDGET: extrapolate_100m≈{h100:.1f}h ≤40h — time-legal candidate")
    else:
        print(f"BUDGET: extrapolate_100m≈{h100:.1f}h >40h — time-illegal unless faster host")
    if d <= -20:
        print("VERDICT: SCALE_HOLD_CANDIDATE — promote only after quiet confirm / 100m plan")
    else:
        print("VERDICT: WEAK_KEEP — Δ softens vs 1m; do not bank alone")
elif d <= 8:
    print("VERDICT: FLAT @10m — LSTM256 does not scale; CLOSE for S2")
else:
    print(f"VERDICT: REJECT @10m Δ={d:+d}")
PY
log DONE
rm -f "$OUT/run.pid"
