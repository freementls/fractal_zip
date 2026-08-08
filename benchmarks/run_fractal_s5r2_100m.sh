#!/usr/bin/env bash
# s5_r2 (soft5 + refresh/2) @100 MB vs banked fractalv2b / LSTM320.
# @10m: −661 vs320, +1325 vs384, 1.26× base — wall-legal, partial byte win.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_speed_opt/s5_r2_100m"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BIN="$ROOT/tools/hutter/fx2-cmix/run/cmix_opt_s5_r2"
SL="$ROOT/benchmarks/.ladder_cache/enwik8_100m_skip0.bin"
# Reuse banked 100m refs from lstm320_100m
REF_DIR="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m"
A0_REF=14966085
PRIZE=14786491
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -x "$BIN" && -s "$SL" ]] || { log "missing s5_r2 binary or 100m slice"; exit 1; }
[[ -s "$REF_DIR/B100m_base.fx2" && -s "$REF_DIR/B100m_lstm320.fx2" ]] || {
  log "missing banked 100m refs in $REF_DIR"; exit 1;
}

B=$(stat -c%s "$REF_DIR/B100m_base.fx2")
Bw=$(cat "$REF_DIR/B100m_base.wall")
F320=$(stat -c%s "$REF_DIR/B100m_lstm320.fx2")
Fw320=$(cat "$REF_DIR/B100m_lstm320.wall")
log "START s5_r2 @100m reuse base=$B/${Bw}s 320=$F320/${Fw320}s A0=$A0_REF"

t0=$(date +%s.%N)
env FXCM_RECIPE_MIXER_BITMASK=2 \
  "$BIN" -c "$DICT" "$SL" "$OUT/B100m_s5_r2.fx2" >/dev/null 2>"$OUT/B100m_s5_r2.err"
t1=$(date +%s.%N)
sz=$(stat -c%s "$OUT/B100m_s5_r2.fx2")
wall=$(python3 -c "print(f'{float('$t1')-float('$t0'):.1f}')")
echo "$wall" >"$OUT/B100m_s5_r2.wall"
log "B100m_s5_r2 bytes=$sz wall=${wall}s"

python3 - <<PY | tee -a "$LOG" >&2
B,Bw=$B,$Bw
F320,Fw320=$F320,$Fw320
F,Fw=$sz,float("$wall")
A0,PRIZE=$A0_REF,$PRIZE
print(f"100m base={B} 320={F320} s5_r2={F}")
print(f"Δ vs base: 320={F320-B:+d} s5_r2={F-B:+d}; s5_r2 vs320={F-F320:+d}")
print(f"time base={Bw/3600:.2f}h 320={Fw320/3600:.2f}h s5_r2={Fw/3600:.2f}h")
print(f"ratio vs base: 320={Fw320/Bw:.3f}x s5_r2={Fw/Bw:.3f}x; vs320_wall={Fw/Fw320:.3f}x")
print(f"prize_need≤{PRIZE}; s5_r2_gap={F-PRIZE:+d}")
if F <= 0:
    print("VERDICT: FAILED_RUN")
elif F < F320 and Fw <= Bw * 1.35:
    print("VERDICT: SCALE_KEEP_100m — beats 320 under 1.35×")
elif F < F320 and Fw <= Bw * 1.50:
    print("VERDICT: WEAK_KEEP_100m — beats 320, wall soft")
elif F >= F320:
    print("VERDICT: REJECT_vs_320 @100m")
else:
    print("VERDICT: MIXED")
PY
log DONE
rm -f "$OUT/run.pid"
