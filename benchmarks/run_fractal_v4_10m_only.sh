#!/usr/bin/env bash
set -euo pipefail
ROOT=/srv/http/fractal_zip
LOG=$ROOT/benchmarks/.hutter_logs/fractal_v4_10m_only.log
# Must export — run_hutter_slice_screen.sh invokes the guard again with defaults.
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-6144}"
{
  echo "START_v4_10m $(date -Is)"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" fractal_v4_10m \
    "$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_fractal" \
    "$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic" \
    "$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid" \
    1750907
  D10=$(python3 -c "import json;print([json.loads(l)['delta'] for l in open('$ROOT/benchmarks/.hutter_slice_screens.jsonl') if 'fractal_v4_10m' in l][-1])")
  echo "Δ10m=$D10"
  if (( D10 >= 0 )); then echo "VERDICT: REJECT @10m (Δ1m=-1 Δ10m=$D10)"; else echo "VERDICT: SCALE_KEEP (Δ1m=-1 Δ10m=$D10)"; fi
  echo "DONE_v4_10m $(date -Is)"
} >>"$LOG" 2>&1
