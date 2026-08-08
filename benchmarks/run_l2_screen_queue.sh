#!/usr/bin/env bash
# After m3l384 100m frees the machine: RT-smoke + 1m + 10m screens for L2 on B0.
# Relies on hutter_memory_guard for single-cmix exclusion.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/l2_screen_queue.log"
exec >>"$LOG" 2>&1

echo "=== L2 screen queue START=$(date -Iseconds) ==="
# After a 7GiB cmix, swap can sit slightly over the 4GiB floor while MemAvailable
# is still ample — allow 5GiB unless overridden.
export HUTTER_MAX_SWAP_MIB="${HUTTER_MAX_SWAP_MIB:-5120}"
bash "$ROOT/benchmarks/hutter_memory_guard.sh"

CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_l2_entity"
DICT="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
[[ -x "$CMIX" ]] || { echo "missing $CMIX — make l2_entity first"; exit 1; }

SKIP_SMOKE="${SKIP_SMOKE:-0}"
if [[ "$SKIP_SMOKE" != 1 ]]; then
  SMOKE="$ROOT/benchmarks/.ladder_cache/l2_smoke64k.bin"
  head -c 65536 "$ROOT/enwik8" >"$SMOKE"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" l2_smoke64k \
    "$CMIX" "$DICT" "$SMOKE" 999999999
fi

# 1m vs A0 baseline 198744
bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" l2_entity_1m \
  "$CMIX" "$DICT" \
  "$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid" 198744

# 10m vs A0 baseline 1751514
bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" l2_entity_10m \
  "$CMIX" "$DICT" \
  "$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid" 1751514

echo "=== L2 screen queue DONE=$(date -Iseconds) ==="
