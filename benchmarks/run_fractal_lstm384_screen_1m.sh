#!/usr/bin/env bash
# Quiet 1m screen: fractalv2b vs lstm256 vs lstm384 (always-on, bitmask=2, raw mid).
# Uses raw mid (no sentinels) to match the banked 10m LSTM256 protocol.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm384_1m"
LOG="$OUT/screen.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
L256="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
L384="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm384"
SL="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
mkdir -p "$OUT"
echo $$ >"$OUT/run.pid"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -x "$L384" ]] || { log "missing $L384 — build match3m_fractalv2_lstm384"; exit 1; }

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

log "START lstm384 @1m raw mid"
B=$(run_timed B1m_base "$BASE")
F256=$(run_timed B1m_lstm256 "$L256")
F384=$(run_timed B1m_lstm384 "$L384")
Bw=$(cat "$OUT/B1m_base.wall")
W256=$(cat "$OUT/B1m_lstm256.wall")
W384=$(cat "$OUT/B1m_lstm384.wall")

python3 - "$B" "$F256" "$F384" "$Bw" "$W256" "$W384" <<'PY' | tee -a "$LOG" >&2
import sys
B, F256, F384 = map(int, sys.argv[1:4])
Bw, W256, W384 = map(float, sys.argv[4:7])
print(f"summary base={B} l256={F256} (Δ={F256-B:+d}) l384={F384} (Δ={F384-B:+d}) "
      f"l384_vs_l256={F384-F256:+d}")
print(f"time base={Bw:.1f}s l256={W256:.1f}s ({W256/Bw:.2f}x) "
      f"l384={W384:.1f}s ({W384/Bw:.2f}x vs base, {W384/W256:.2f}x vs l256)")
d = F384 - F256
if d <= -8 and W384 <= W256 * 1.50:
    print("VERDICT: LSTM384_BEATS_256 — promote to 10m scale-gate")
elif d <= -8:
    print("VERDICT: LSTM384_BYTES but time tax — check 10m + 40h budget")
elif d <= 8:
    print("VERDICT: FLAT vs LSTM256 — stay at 256")
else:
    print(f"VERDICT: REJECT vs LSTM256 Δ={d:+d}")
PY
log DONE
rm -f "$OUT/run.pid"
