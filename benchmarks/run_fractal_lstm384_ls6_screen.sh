#!/usr/bin/env bash
# LSTM384 + match-skip MIN=6 speed recovery vs full 384 / banked 320.
# @1m first; promote @10m if bytes still beat 320 by ≥8 and wall ≤1.45× base.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm384_ls6"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
L320="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm320"
L384="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm384"
LS6="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm384_ls6"
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -x "$LS6" ]] || { log "missing $LS6"; exit 1; }

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

reuse_or_run() {
  local tag="$1" srcdir="$2" srctag="$3" bin="$4" sl="$5"
  if [[ -s "$srcdir/${srctag}.fx2" && -s "$srcdir/${srctag}.wall" ]]; then
    cp -a "$srcdir/${srctag}.fx2" "$OUT/${tag}.fx2"
    cp -a "$srcdir/${srctag}.wall" "$OUT/${tag}.wall"
    log "REUSE $tag bytes=$(stat -c%s "$OUT/${tag}.fx2") wall=$(cat "$OUT/${tag}.wall")s"
  else
    run_timed "$tag" "$bin" "$sl"
  fi
}

SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
P1="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm384_1m"
P320="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm320"
log "START ls6 @1m"
reuse_or_run B1m_base "$P1" B1m_base "$BASE" "$SL1"
reuse_or_run B1m_lstm320 "$P320" B1m_lstm320 "$L320" "$SL1"
reuse_or_run B1m_lstm384 "$P1" B1m_lstm384 "$L384" "$SL1"
run_timed B1m_ls6 "$LS6" "$SL1"

python3 - <<'PY' | tee -a "$LOG" >&2
import os
OUT="/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm384_ls6"
def bw(t):
    return os.path.getsize(f"{OUT}/{t}.fx2"), float(open(f"{OUT}/{t}.wall").read())
B,Bw=bw("B1m_base"); s,sw=bw("B1m_lstm320"); f,fw=bw("B1m_lstm384"); g,gw=bw("B1m_ls6")
print(f"1m base={B} 320={s}({s-B:+d}) 384={f}({f-B:+d}) ls6={g}({g-B:+d})")
print(f"1m ls6_vs_320={g-s:+d} ls6_vs_384={g-f:+d}; time ls6={gw/Bw:.2f}x 384={fw/Bw:.2f}x 320={sw/Bw:.2f}x")
# KEEP path: still beat 320, not much worse than full 384, and faster than full 384
beat320 = g <= s - 8
near384 = g <= f + 200
faster = gw < fw * 0.95
time_ok = gw <= Bw * 1.45
if beat320 and near384 and (faster or time_ok):
    print("1m_VERDICT: PROMOTE_10m")
    open(f"{OUT}/promote","w").write("1")
elif beat320 and time_ok:
    print("1m_VERDICT: PROMOTE_10m (beats 320; may lose some 384 bytes)")
    open(f"{OUT}/promote","w").write("1")
elif g > s:
    print("1m_VERDICT: REJECT_vs_320 — skip does not preserve win")
    open(f"{OUT}/promote","w").write("0")
else:
    print("1m_VERDICT: WEAK — manual look")
    open(f"{OUT}/promote","w").write("0")
PY

if [[ "$(cat "$OUT/promote")" != "1" ]]; then
  log "SKIP 10m"
  log DONE
  rm -f "$OUT/run.pid"
  exit 0
fi

SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
P10="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_always_on_10m"
P384="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm384_10m"
log "START ls6 @10m"
reuse_or_run B10m_base "$P10" B10m_base "$BASE" "$SL10"
# 320 may live under lstm320/
if [[ -s "$P320/B10m_lstm320.fx2" ]]; then
  reuse_or_run B10m_lstm320 "$P320" B10m_lstm320 "$L320" "$SL10"
else
  run_timed B10m_lstm320 "$L320" "$SL10"
fi
reuse_or_run B10m_lstm384 "$P384" B10m_lstm384 "$L384" "$SL10"
run_timed B10m_ls6 "$LS6" "$SL10"

python3 - <<'PY' | tee -a "$LOG" >&2
import os
OUT="/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm384_ls6"
def bw(t):
    return os.path.getsize(f"{OUT}/{t}.fx2"), float(open(f"{OUT}/{t}.wall").read())
B,Bw=bw("B10m_base"); s,sw=bw("B10m_lstm320"); f,fw=bw("B10m_lstm384"); g,gw=bw("B10m_ls6")
print(f"10m base={B} 320={s}({s-B:+d}) 384={f}({f-B:+d}) ls6={g}({g-B:+d})")
print(f"10m ls6_vs_320={g-s:+d} ls6_vs_384={g-f:+d}; time ls6={gw/Bw:.2f}x 384={fw/Bw:.2f}x 320={sw/Bw:.2f}x")
if g <= s - 8 and gw <= Bw * 1.35:
    print("VERDICT: BANK_LS6 — beats 320 at ≤1.35× (prefer over 320)")
elif g <= s - 8 and gw <= Bw * 1.50:
    print("VERDICT: KEEP_LS6_bytes — time still tax; compare to 384")
elif g <= f + 200 and gw <= fw * 0.85 and g < s:
    print("VERDICT: LS6_SPEED_PATH — near-384 bytes, clearly faster than full 384")
elif g >= s:
    print("VERDICT: REJECT_LS6 — worse than banked 320")
else:
    print("VERDICT: WEAK_LS6 — stay with 320 default / 384 max-bytes")
PY
log DONE
rm -f "$OUT/run.pid"
