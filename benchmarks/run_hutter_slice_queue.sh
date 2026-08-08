#!/usr/bin/env bash
# Run 1/10 MB slice screen queue — ONE job at a time, no nice throttle.
# Logs: /tmp/hutter_slice_queue.log
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
echo "=== hutter slice queue START $(date -Iseconds) ==="

run_one() {
  echo "--- $(date -Iseconds) $1 ---"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$@"
  sync; sleep 5
}

# 1) disk profile 10MB vs mmap baseline (disk-smoke confirm)
run_one disk10m_verify "$RUN/cmix_disk" "$DICT_EN" "$SL10" "$B10"

# 2) dict reorder 1MB
run_one dict_reorder_1m "$RUN/cmix_mmap" "$DICT_RE" "$SL1" "$B1"

# 3) dict reorder 10MB if 1MB KEEP (check jsonl)
if python3 - <<PY
import json
from pathlib import Path
for line in Path("$ROOT/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
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

# 4) UPDATE_LIMIT sweep at 10MB (build + screen)
for UL in 1500 6000; do
  make -C "$ROOT/tools/hutter/fx2-cmix" clean >/dev/null
  make -C "$ROOT/tools/hutter/fx2-cmix" lto CFLAGS_DEFINES="-DSEED=923 -DUPDATE_LIMIT=$UL" >/dev/null
  cp -a "$ROOT/tools/hutter/fx2-cmix/cmix" "$RUN/cmix_ul${UL}"
  run_one "tuning_ul${UL}_10m" "$RUN/cmix_ul${UL}" "$DICT_EN" "$SL10" "$B10"
done

echo "=== hutter slice queue DONE $(date -Iseconds) ==="
