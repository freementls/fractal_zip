#!/usr/bin/env bash
# Soften LSTM budget gate: measure hardness once, emit fxpm at 50%/85%, screen gated only.
# Banked refs (HIDDEN=256 rescreen): base=198726 full=198686 (Δ=-40) gated_f15=200197 wall≈239s
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
OUT="$ROOT/benchmarks/.ladder_cache/fractal_dictlab/lstm_budget_gate"
LOG="$OUT/soften.log"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
BASE="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2b"
LSTM="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractalv2_lstm256"
SL="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SENT="$OUT/enwik8_1m_sentinel.mid"
HTSV="$OUT/hardness_1m.tsv"
PIDF="$OUT/soften.pid"
mkdir -p "$OUT"
echo $$ >"$PIDF"
log() { echo "$(date -Is) $*" | tee -a "$LOG" >&2; }

[[ -x "$LSTM" && -x "$BASE" ]] || { log "missing binary"; exit 1; }
[[ -s "$SENT" ]] || python3 "$ROOT/benchmarks/inject_article_sentinels.py" inject "$SL" "$SENT"

export FXCM_RECIPE_MIXER_BITMASK=2

if [[ ! -s "$HTSV" ]]; then
  log "measuring hardness once (base binary + ARTICLE_HARDNESS_LOG)"
  python3 "$ROOT/benchmarks/run_fractal_tuner.py" \
    --binary "$BASE" --dict "$DICT" --slice "$SL" \
    --hard-fraction 0.15 --hardness-tsv "$HTSV" \
    --out "$OUT/tuned_1m_f15" 2>&1 | tee -a "$LOG" >&2
else
  log "reusing $HTSV"
fi

for frac in 0.50 0.85; do
  tag=$(printf 'f%02d' "$(python3 -c "print(int(round($frac*100)))")")
  log "building fxpm hard_fraction=$frac -> tuned_1m_${tag}.fxpm"
  python3 "$ROOT/benchmarks/run_fractal_tuner.py" \
    --binary "$BASE" --dict "$DICT" --slice "$SL" \
    --hard-fraction "$frac" --hardness-tsv "$HTSV" \
    --out "$OUT/tuned_1m_${tag}" 2>&1 | tee -a "$LOG" >&2
done

run_gated() {
  local tag="$1" fxpm="$2"
  local t0 t1 sz wall
  t0=$(date +%s.%N)
  env FXCM_RECIPE_MIXER_BITMASK=2 FXCM_PROFILE_MAP_PATH="$fxpm" \
    "$LSTM" -c "$DICT" "$SENT" "$OUT/${tag}.fx2" >/dev/null 2>"$OUT/${tag}.err" || true
  t1=$(date +%s.%N)
  sz=$(stat -c%s "$OUT/${tag}.fx2" 2>/dev/null || echo 0)
  wall=$(python3 -c "print(f'{float('$t1')-float('$t0'):.1f}')")
  echo "$wall" >"$OUT/${tag}.wall"
  log "$tag bytes=$sz wall=${wall}s fxpm=$fxpm"
}

log "refs base=198726 full=198686 (Δ=-40) gated_f15=200197 (Δ=+1471 wall≈239s) full_wall≈2221s"
run_gated B1m_gated_f50 "$OUT/tuned_1m_f50.fxpm"
run_gated B1m_gated_f85 "$OUT/tuned_1m_f85.fxpm"

python3 - <<'PY' | tee -a "$LOG" >&2
import os
OUT = "/srv/http/fractal_zip/benchmarks/.ladder_cache/fractal_dictlab/lstm_budget_gate"
base, full, f15 = 198726, 198686, 200197
full_wall, f15_wall = 2221.1, 239.1
print("frac | bytes | Δbase | Δfull | wall | speedup_vs_full")
print(f"1.00 full | {full} | {full-base:+d} | 0 | {full_wall:.1f}s | 1.00x")
print(f"0.15 f15  | {f15} | {f15-base:+d} | {f15-full:+d} | {f15_wall:.1f}s | {full_wall/f15_wall:.2f}x")
rows = []
for tag, frac in [("B1m_gated_f50", 0.50), ("B1m_gated_f85", 0.85)]:
    p = f"{OUT}/{tag}.fx2"
    w = f"{OUT}/{tag}.wall"
    if os.path.isfile(p) and os.path.getsize(p) > 0:
        sz = os.path.getsize(p)
        wall = float(open(w).read()) if os.path.isfile(w) else float("nan")
        sp = (full_wall / wall) if wall else float("nan")
        print(f"{frac:.2f} {tag} | {sz} | {sz-base:+d} | {sz-full:+d} | {wall:.1f}s | {sp:.2f}x")
        rows.append((tag, frac, sz, wall))
if not rows:
    print("SOFTEN_VERDICT: no results")
elif any(r[2] <= full + 8 and r[3] < full_wall * 0.95 for r in rows):
    best = min((r for r in rows if r[2] <= full + 8), key=lambda r: r[3])
    print(f"SOFTEN_VERDICT: GATE_HELPS_TIME bytes≈full via {best[0]}")
elif any(r[2] < base for r in rows):
    best = min(rows, key=lambda r: r[2])
    print(f"SOFTEN_VERDICT: {best[0]} beats base by {base-best[2]}B — candidate (check time)")
else:
    best = min(rows, key=lambda r: r[2])
    print(f"SOFTEN_VERDICT: REJECT_vs_base best={best[0]} Δ={best[2]-base:+d}")
PY
log DONE_soften
rm -f "$PIDF"
