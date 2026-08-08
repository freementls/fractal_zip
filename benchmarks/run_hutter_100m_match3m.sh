#!/usr/bin/env bash
# Time-safer 100m gate: MATCH3M + B0 entityfold (no LSTM growth).
# Use after m3l384 100m if that package fails the 50h time budget extrapolation
# or if a prize-legal path is needed. Still blocked from enwik9 until 1.2%.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
export CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_entity"
export DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
export BASELINE=14966085
export HUTTER_NICE="${HUTTER_NICE:-0}"
[[ -x "$CMIX" ]] || { echo "missing $CMIX — make match3m_entity"; exit 1; }
exec bash "$ROOT/benchmarks/run_hutter_100m_gate.sh" match3m
