#!/usr/bin/env bash
# Resume always-on LSTM256 @10m (base already banked-matched at 1750867).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_always_on_10m"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
LSTM="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
SL="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
BANKED_BASE=1750867
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

# Kill any stray half-started resume
if pgrep -f '/run/cmix_match3m_fractalv2_lstm256' >/dev/null 2>&1; then
  log "cmix already running — refuse resume"
  exit 1
fi

rm -f "$OUT/B10m_lstm_full.fx2" "$OUT/B10m_lstm_full.fx2.cmix.temp"
log "RESUME B10m_lstm_full"
t0=$(date +%s.%N)
env FXCM_RECIPE_MIXER_BITMASK=2 \
  "$LSTM" -c "$DICT" "$SL" "$OUT/B10m_lstm_full.fx2" >/dev/null 2>"$OUT/B10m_lstm_full.err" || true
t1=$(date +%s.%N)
sz=$(stat -c%s "$OUT/B10m_lstm_full.fx2" 2>/dev/null || echo 0)
wall=$(python3 -c "print(f'{float('$t1')-float('$t0'):.1f}')")
echo "$wall" >"$OUT/B10m_lstm_full.wall"
log "B10m_lstm_full bytes=$sz wall=${wall}s"

B=$(stat -c%s "$OUT/B10m_base.fx2")
Bw=$(cat "$OUT/B10m_base.wall")
python3 - "$B" "$sz" "$Bw" "$wall" "$BANKED_BASE" <<'PY' | tee -a "$LOG" >&2
import sys
B, F = int(sys.argv[1]), int(sys.argv[2])
Bw, Fw = float(sys.argv[3]), float(sys.argv[4])
banked = int(sys.argv[5])
d = F - B
print(f"B10m summary base={B} (banked_ref={banked} drift={B-banked:+d}) "
      f"lstm_full={F} (Δ={d:+d})")
print(f"B10m time base={Bw:.1f}s full={Fw:.1f}s ratio={Fw/Bw if Bw else 0:.3f}x "
      f"extrapolate_100m_full_h={(Fw/10)*100/3600:.1f}h")
if F <= 0:
    print("VERDICT: FAILED_RUN")
elif d <= -8:
    print(f"BYTES: KEEP_DIRECTIONAL Δ={d:+d} @10m (1m quiet was -40)")
    print(f"TIME: ratio={Fw/Bw:.2f}x")
    h100 = (Fw / 10.0) * 100.0 / 3600.0
    print(f"BUDGET: extrapolate_100m≈{h100:.1f}h {'<=40h' if h100 <= 40 else '>40h'}")
    print("VERDICT: SCALE_HOLD_CANDIDATE" if d <= -20 else "VERDICT: WEAK_KEEP")
elif d <= 8:
    print("VERDICT: FLAT @10m — LSTM256 does not scale; CLOSE for S2")
else:
    print(f"VERDICT: REJECT @10m Δ={d:+d}")
PY
log DONE
rm -f "$OUT/run.pid"
