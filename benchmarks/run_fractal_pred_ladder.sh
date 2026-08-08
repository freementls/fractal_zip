#!/usr/bin/env bash
# Fractal predictor ladder: 1m → 10m → (optional) 100m.
# Policy: NEVER bank from 1m alone (MIX_NUMLEN lesson: −16KB@1m → −40B@10m).
# KEEP only if ΔS2 < 0 at BOTH 1m and 10m; then promote 100m.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/fractal_pred_ladder.log"
CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractal"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
# match3m_entity baselines (same mid-slices as mixnum screens)
B1=198794
B10=1750907
# Export so nested run_hutter_slice_screen.sh guard inherits (default 4GiB blocks this host).
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-6144}"
mkdir -p "$ROOT/benchmarks/.hutter_logs"

{
  echo "START_fractal_v5 $(date -Is)"
  echo "cmix=$CMIX"
  ls -la "$CMIX" "$DICT" "$SL1" "$SL10"

  bash "$ROOT/benchmarks/hutter_memory_guard.sh"

  echo "=== fractal @1m (baseline=$B1) ==="
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" fractal_v5_1m "$CMIX" "$DICT" "$SL1" "$B1"
  D1=$(python3 -c "import json;print([json.loads(l)['delta'] for l in open('$ROOT/benchmarks/.hutter_slice_screens.jsonl') if 'fractal_v5_1m' in l][-1])")
  echo "Δ1m=$D1"
  # v5 deepens only at zoom≥2 (>1 MiB), so @1m may be bit-identical (Δ≈0).
  # Reject only a real sink; noop/KEEP both promote (scale is the test).
  if (( D1 > 8 )); then
    echo "VERDICT: REJECT @1m (Δ=$D1) — stop ladder"
    echo "DONE_fractal_v5 $(date -Is)"
    exit 0
  fi

  echo "PROMOTE_10m (1m Δ=$D1; noop-ok for deep-only fractal)"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"

  echo "=== fractal @10m (baseline=$B10) ==="
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" fractal_v5_10m "$CMIX" "$DICT" "$SL10" "$B10"
  D10=$(python3 -c "import json;print([json.loads(l)['delta'] for l in open('$ROOT/benchmarks/.hutter_slice_screens.jsonl') if 'fractal_v5_10m' in l][-1])")
  echo "Δ10m=$D10"
  if (( D10 >= 0 )); then
    echo "VERDICT: REJECT @10m (Δ1m=$D1 Δ10m=$D10) — scale-fail like MIX_NUMLEN"
  else
    echo "VERDICT: SCALE_KEEP (Δ1m=$D1 Δ10m=$D10) — queue @100m next"
  fi
  echo "DONE_fractal_v5 $(date -Is)"
} 2>&1 | tee -a "$LOG"
