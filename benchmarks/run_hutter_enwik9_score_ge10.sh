#!/usr/bin/env bash
# Official full-binary enwik9 score. Requires MemTotal >= 10GiB.
# Usage: HUTTER_PROFILE=full bash benchmarks/run_hutter_enwik9_score_ge10.sh [/path/to/enwik9]
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
export HUTTER_PROFILE="${HUTTER_PROFILE:-full}"
export HUTTER_MIN_GIB="${HUTTER_MIN_GIB:-10}"
# Prefer prize-shaped embedded binary if present
if [[ -z "${CMIX:-}" ]]; then
  for c in "$ROOT/tools/hutter/fx2-cmix/run/cmix" \
           "$ROOT/tools/hutter/fx2-cmix/run/cmix_improved" \
           "$ROOT/tools/hutter/fx2-cmix/cmix"; do
    if [[ -x "$c" ]]; then export CMIX="$c"; break; fi
  done
fi
exec bash "$ROOT/benchmarks/run_hutter_enwik9_verify.sh" "${1:-$ROOT/tools/hutter/data/enwik9}"
