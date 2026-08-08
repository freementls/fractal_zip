#!/usr/bin/env bash
# LSTM320 middle ground: @1m then @10m if beats 256 by ≥8B with time < 1.45× base.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
L256="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
L320="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320"
L384="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm384"
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -x "$L320" ]] || { log "missing $L320"; exit 1; }

run_timed() {
  local tag="$1" bin="$2" sl="$3"
  local t0 t1 sz wall
  t0=$(date +%s.%N)
  env FXCM_RECIPE_MIXER_BITMASK=2 \
    "$bin" -c "$DICT" "$sl" "$OUT/${tag}.fx2" >/dev/null 2>"$OUT/${tag}.err" || true
  t1=$(date +%s.%N)
  sz=$(stat -c%s "$OUT/${tag}.fx2" 2>/dev/null || echo 0)
  wall=$(python3 -c "print(f'{float('$t1')-float('$t0'):.1f}')")
  echo "$wall" >"$OUT/${tag}.wall"
  log "$tag bytes=$sz wall=${wall}s"
}

SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
# Reuse 1m base/256/384 from lstm384_1m if present
P1="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm384_1m"
log "START lstm320 @1m"
if [[ -s "$P1/B1m_base.fx2" ]]; then
  for tag in B1m_base B1m_lstm256 B1m_lstm384; do
    cp -a "$P1/${tag}.fx2" "$OUT/${tag}.fx2"
    cp -a "$P1/${tag}.wall" "$OUT/${tag}.wall"
    log "REUSE $tag bytes=$(stat -c%s "$OUT/${tag}.fx2") wall=$(cat "$OUT/${tag}.wall")s"
  done
else
  run_timed B1m_base "$BASE" "$SL1"
  run_timed B1m_lstm256 "$L256" "$SL1"
  run_timed B1m_lstm384 "$L384" "$SL1"
fi
run_timed B1m_lstm320 "$L320" "$SL1"

python3 - <<'PY' | tee -a "$LOG" >&2
import os
OUT="/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm320"
def bw(t):
    return os.path.getsize(f"{OUT}/{t}.fx2"), float(open(f"{OUT}/{t}.wall").read())
B,Bw=bw("B1m_base"); a,aw=bw("B1m_lstm256"); c,cw=bw("B1m_lstm320"); d,dw=bw("B1m_lstm384")
print(f"1m base={B} 256={a}({a-B:+d}) 320={c}({c-B:+d}) 384={d}({d-B:+d})")
print(f"1m vs256: 320={c-a:+d} 384={d-a:+d}; time 320={cw/Bw:.2f}x 384={dw/Bw:.2f}x")
promote = (c <= a - 8) and (cw <= Bw * 1.45)
print("1m_VERDICT:", "PROMOTE_10m" if promote or c < a else "WEAK_or_FLAT",
      f"(320_vs_256={c-a:+d})")
open(f"{OUT}/promote_10m","w").write("1" if (c < a) else "0")
PY

if [[ "$(cat "$OUT/promote_10m")" != "1" ]]; then
  log "SKIP 10m — 320 not better than 256 @1m"
  log DONE
  rm -f "$OUT/run.pid"
  exit 0
fi

SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
P10="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_always_on_10m"
P384="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm384_10m"
log "START lstm320 @10m"
if [[ -s "$P10/B10m_base.fx2" ]]; then
  cp -a "$P10/B10m_base.fx2" "$OUT/B10m_base.fx2"
  cp -a "$P10/B10m_base.wall" "$OUT/B10m_base.wall"
  log "REUSE B10m_base"
fi
if [[ -s "$P10/B10m_lstm_full.fx2" ]]; then
  cp -a "$P10/B10m_lstm_full.fx2" "$OUT/B10m_lstm256.fx2"
  cp -a "$P10/B10m_lstm_full.wall" "$OUT/B10m_lstm256.wall"
  log "REUSE B10m_lstm256"
fi
if [[ -s "$P384/B10m_lstm384.fx2" ]]; then
  cp -a "$P384/B10m_lstm384.fx2" "$OUT/B10m_lstm384.fx2"
  cp -a "$P384/B10m_lstm384.wall" "$OUT/B10m_lstm384.wall"
  log "REUSE B10m_lstm384"
fi
run_timed B10m_lstm320 "$L320" "$SL10"

python3 - <<'PY' | tee -a "$LOG" >&2
import os
OUT="/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm320"
def bw(t):
    return os.path.getsize(f"{OUT}/{t}.fx2"), float(open(f"{OUT}/{t}.wall").read())
B,Bw=bw("B10m_base"); a,aw=bw("B10m_lstm256"); c,cw=bw("B10m_lstm320"); d,dw=bw("B10m_lstm384")
print(f"10m base={B} 256={a}({a-B:+d}) 320={c}({c-B:+d}) 384={d}({d-B:+d})")
print(f"10m vs256: 320={c-a:+d} 384={d-a:+d}; time 320={cw/Bw:.2f}x 384={dw/Bw:.2f}x")
if c <= a - 8:
    print("VERDICT: KEEP_320_over_256")
    if c <= d + 8:
        print("NOTE: 320≈384 bytes — prefer 320 for time" if cw < dw else "NOTE: 320≈384, 384 still")
elif c <= d + 8 and cw < dw * 0.9:
    print("VERDICT: 320_near_384_faster — candidate default")
else:
    print("VERDICT: prefer 384 for bytes / 256 for time; 320 middle")
PY
log DONE
rm -f "$OUT/run.pid"
