#!/usr/bin/env bash
# After order window settled: AdaCom-style PPMD shrink screens @1MB then @10MB if KEEP-ish.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_ppmd.log"
FX2="$ROOT/tools/hutter/fx2-cmix"
DIC="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_e9.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
# Baseline = match3m with banked order is NOT used for raw enwik8 slices (no reorder).
# These screens measure model-only S2 on enwik8 mid slice vs prior match3m entity baseline.
BASE1=198794
echo "PPMD_START $(date -Is)" | tee "$LOG"

busy() { pgrep -x cmix_orig >/dev/null || pgrep -x cmix_match3m_en >/dev/null; }
while busy; do echo "wait $(date +%H:%M:%S)"; sleep 30; done

cd "$FX2"
echo "=== build match3m_ppmd8k ===" | tee -a "$LOG"
make -j"$(nproc)" match3m_ppmd8k 2>&1 | tee -a "$LOG" | tail -20
echo "=== screen ppmd8k @1m ===" | tee -a "$LOG"
bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" ppmd8k_1m \
  "$FX2/run/cmix_match3m_ppmd8k" "$DIC" "$SL1" "$BASE1" | tee -a "$LOG"

# If ΔS2 at 1m is small loss and wall improves, promote @10m; else try 4k or stop.
python3 - <<'PY' | tee -a "$LOG"
import json
from pathlib import Path
rows=[]
for line in Path("/srv/http/fractal_zip/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
    if '"ppmd8k_1m"' in line:
        rows.append(json.loads(line))
if not rows:
    print("no ppmd8k_1m row"); raise SystemExit(0)
r=rows[-1]
print(f"ppmd8k_1m delta={r.get('delta')} elapsed_c={r.get('elapsed_c')} rss={r.get('peak_rss_kb_c')}")
# write gate file
Path("/srv/http/fractal_zip/benchmarks/.ladder_cache/ppmd8k_1m_delta.txt").write_text(str(r.get("delta",999999)))
PY

DELTA=$(cat "$ROOT/benchmarks/.ladder_cache/ppmd8k_1m_delta.txt" 2>/dev/null || echo 999999)
# Allow modest S2 loss if RSS/time improve for stacking; hard reject if >500B @1m
if (( DELTA > 500 )); then
  echo "ppmd8k reject @1m delta=$DELTA" | tee -a "$LOG"
else
  echo "ppmd8k promote @10m delta=$DELTA" | tee -a "$LOG"
  SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
  BASE10=1750907  # B_match3m_10m bytes
  [[ -f "$SL10" ]] || SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" ppmd8k_10m \
    "$FX2/run/cmix_match3m_ppmd8k" "$DIC" "$SL10" "$BASE10" | tee -a "$LOG" || true
fi
echo "PPMD_DONE $(date -Is)" | tee -a "$LOG"
