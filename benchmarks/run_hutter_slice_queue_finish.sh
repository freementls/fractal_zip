#!/usr/bin/env bash
# Finish remaining slice screens after caver15 reject + ul1500 build-path fix.
# Builds binaries into run/ from FX2 tree (make outputs ./cmix, not run/cmix).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG=/tmp/hutter_slice_queue.log
FX2="$ROOT/tools/hutter/fx2-cmix"
RUN="$FX2/run"
DICT_EN="$FX2/dictionary/english.dic"
SL10="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"
B10=1751514
exec >>"$LOG" 2>&1
echo "=== hutter slice queue FINISH $(date -Iseconds) ==="

run_one() {
  echo "--- $(date -Iseconds) $1 ---"
  bash "$ROOT/benchmarks/hutter_memory_guard.sh"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$@"
  rm -f "$RUN/ppm.temp"
  sync; sleep 5
}

build_ul() {
  local ul=$1
  echo "=== build cmix_ul${ul} ==="
  make -C "$FX2" clean >/dev/null
  make -C "$FX2" lto CFLAGS_DEFINES="-DSEED=923 -DUPDATE_LIMIT=$ul"
  cp -a "$FX2/cmix" "$RUN/cmix_ul${ul}"
  echo "built $(stat -c '%s %y' "$RUN/cmix_ul${ul}")"
}

build_ul 1500
run_one "tuning_ul1500_10m" "$RUN/cmix_ul1500" "$DICT_EN" "$SL10" "$B10"

build_ul 6000
run_one "tuning_ul6000_10m" "$RUN/cmix_ul6000" "$DICT_EN" "$SL10" "$B10"

echo "=== hutter slice queue FINISH DONE $(date -Iseconds) ==="
python3 - <<'PY'
import json
from pathlib import Path
rows=[]
for line in Path("/srv/http/fractal_zip/benchmarks/.hutter_slice_screens.jsonl").read_text().splitlines():
    if line.strip():
        rows.append(json.loads(line))
print("=== SUMMARY ===")
for r in rows:
    print(f"{r.get('name'):22} bytes={r.get('bytes')} delta={r.get('delta',0):+d} gate={r.get('gate')} rt={r.get('rt')}")
keeps=[r for r in rows if r.get('gate')=='KEEP']
print(f"KEEPs: {len(keeps)}")
PY
