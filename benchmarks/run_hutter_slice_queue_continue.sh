#!/usr/bin/env bash
# Continue slice queue after disk10m_verify compress (skip completed steps).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG=/tmp/hutter_slice_queue.log
RUN="$ROOT/tools/hutter/fx2-cmix/run"
DICT_EN="$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic"
DICT_RE="$ROOT/benchmarks/.ladder_cache/dicts/enwik9_global_reorder.dic"
SL1="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B1=198744
B10=1751514
exec >>"$LOG" 2>&1
echo "=== hutter slice queue CONTINUE $(date -Iseconds) ==="

run_one() {
  echo "--- $(date -Iseconds) $1 ---"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$@"
  rm -f "$RUN/ppm.temp"
  sync; sleep 5
}

# disk10m compress done (+1B vs mmap); skip lengthy disk RT — use mmap for remaining screens.
run_one dict_reorder_1m "$RUN/cmix_mmap" "$DICT_RE" "$SL1" "$B1"

if python3 - <<PY
import json
from pathlib import Path
p=Path("$ROOT/benchmarks/.hutter_slice_screens.jsonl")
for line in p.read_text().splitlines():
    r=json.loads(line)
    if r.get("name")=="dict_reorder_1m" and r.get("gate")=="KEEP" and r.get("rt")=="OK":
        raise SystemExit(0)
raise SystemExit(1)
PY
then
  run_one dict_reorder_10m "$RUN/cmix_mmap" "$DICT_RE" "$SL10" "$B10"
else
  echo "SKIP dict_reorder_10m (1MB not KEEP)"
fi

# CAVER-15 10MB retry (mmap binary — faster than disk on 7GiB)
if [[ -x "$RUN/cmix_caver15" ]]; then
  run_one caver15_10m "$RUN/cmix_caver15" "$DICT_EN" "$SL10" "$B10"
fi

for UL in 1500 6000; do
  make -C "$ROOT/tools/hutter/fx2-cmix" clean >/dev/null
  make -C "$ROOT/tools/hutter/fx2-cmix" lto CFLAGS_DEFINES="-DSEED=923 -DUPDATE_LIMIT=$UL" >/dev/null
  cp -a "$ROOT/tools/hutter/fx2-cmix/cmix" "$RUN/cmix_ul${UL}"
  run_one "tuning_ul${UL}_10m" "$RUN/cmix_ul${UL}" "$DICT_EN" "$SL10" "$B10"
done

echo "=== hutter slice queue CONTINUE DONE $(date -Iseconds) ==="
