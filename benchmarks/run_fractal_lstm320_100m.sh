#!/usr/bin/env bash
# LSTM320 @100 MB scale-gate vs fractalv2b (static MIX_NUMLEN). Serial A/B.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
L320="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320"
SL="$ROOT/benchmarks/.ladder_cache/enwik8_100m_skip0.bin"
# Banked A0 reference (entityfold match3m stack family)
A0_REF=14966085
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -x "$BASE" && -x "$L320" && -s "$SL" ]] || { log "missing binary or 100m slice"; exit 1; }

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
}

log "START LSTM320 @100m A0_ref=$A0_REF"
run_timed B100m_base "$BASE"
run_timed B100m_lstm320 "$L320"

python3 - <<'PY' | tee -a "$LOG" >&2
import os
OUT="/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm320_100m"
A0=14966085
def bw(t):
    return os.path.getsize(f"{OUT}/{t}.fx2"), float(open(f"{OUT}/{t}.wall").read())
B,Bw=bw("B100m_base"); F,Fw=bw("B100m_lstm320")
d=F-B
print(f"100m base={B} lstm320={F} (Δ={d:+d}) vs_A0_base={B-A0:+d} vs_A0_320={F-A0:+d}")
print(f"time base={Bw/3600:.2f}h 320={Fw/3600:.2f}h ratio={Fw/Bw if Bw else 0:.3f}x")
print(f"prize_need≤14786491; 320_gap_to_floor={F-14786491:+d}")
if F<=0 or B<=0:
    print("VERDICT: FAILED_RUN")
elif d <= -15000 and Fw <= Bw * 1.35:
    print("VERDICT: SCALE_KEEP_100m — bank confirmed")
elif d <= -5000 and Fw <= Bw * 1.50:
    print("VERDICT: WEAK_KEEP_100m — holds directionally")
elif d >= -5000:
    print("VERDICT: REJECT_evaporates @100m")
else:
    print("VERDICT: MIXED — see Δ/time")
PY
log DONE
rm -f "$OUT/run.pid"
