#!/usr/bin/env bash
# 10m scale-gate: fractalv2b vs lstm256 vs lstm384 (always-on, bitmask=2, raw mid).
# Reuses banked B10m_base=1750867 / B10m_lstm_full=1748751 when present.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm384_10m"
PRIOR="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_always_on_10m"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
L256="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
L384="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm384"
SL="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

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

# Reuse prior quiet always-on arms when byte-identical protocol
if [[ -s "$PRIOR/B10m_base.fx2" && $(stat -c%s "$PRIOR/B10m_base.fx2") -eq 1750867 ]]; then
  cp -a "$PRIOR/B10m_base.fx2" "$OUT/B10m_base.fx2"
  cp -a "$PRIOR/B10m_base.wall" "$OUT/B10m_base.wall"
  cp -a "$PRIOR/B10m_base.err" "$OUT/B10m_base.err" 2>/dev/null || true
  log "REUSE B10m_base bytes=1750867 wall=$(cat "$OUT/B10m_base.wall")s"
else
  run_timed B10m_base "$BASE"
fi
if [[ -s "$PRIOR/B10m_lstm_full.fx2" && $(stat -c%s "$PRIOR/B10m_lstm_full.fx2") -eq 1748751 ]]; then
  cp -a "$PRIOR/B10m_lstm_full.fx2" "$OUT/B10m_lstm256.fx2"
  cp -a "$PRIOR/B10m_lstm_full.wall" "$OUT/B10m_lstm256.wall"
  log "REUSE B10m_lstm256 bytes=1748751 wall=$(cat "$OUT/B10m_lstm256.wall")s"
else
  run_timed B10m_lstm256 "$L256"
fi

log "START B10m_lstm384"
run_timed B10m_lstm384 "$L384"

python3 - <<'PY' | tee -a "$LOG" >&2
import os
OUT = "/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm384_10m"
def bw(tag):
    return os.path.getsize(f"{OUT}/{tag}.fx2"), float(open(f"{OUT}/{tag}.wall").read())
B, Bw = bw("B10m_base")
F256, W256 = bw("B10m_lstm256")
F384, W384 = bw("B10m_lstm384")
print(f"summary base={B} l256={F256} (Δ={F256-B:+d}) l384={F384} (Δ={F384-B:+d}) "
      f"l384_vs_l256={F384-F256:+d}")
print(f"time base={Bw:.1f}s l256={W256:.1f}s ({W256/Bw:.2f}x) "
      f"l384={W384:.1f}s ({W384/Bw:.2f}x vs base, {W384/W256:.2f}x vs l256)")
d = F384 - F256
if F384 <= 0:
    print("VERDICT: FAILED_RUN")
elif d <= -8:
    print("VERDICT: SCALE_KEEP_LSTM384 — bank 384 over 256 on fractal stack")
elif d <= 8:
    print("VERDICT: FLAT @10m — keep LSTM256")
else:
    print(f"VERDICT: REJECT_384 vs 256 Δ={d:+d} — keep LSTM256")
PY
log DONE
rm -f "$OUT/run.pid"
