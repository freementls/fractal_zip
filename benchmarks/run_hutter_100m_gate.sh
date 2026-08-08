#!/usr/bin/env bash
# 100MB (full enwik8) gate: cmix_disk compress + RT vs baseline.
# Usage: CMIX=... DICT=... bash benchmarks/run_hutter_100m_gate.sh [name]
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
bash "$ROOT/benchmarks/hutter_memory_guard.sh"
RUN="${HUTTER_WORKDIR:-$ROOT/tools/hutter/fx2-cmix/run}"
CMIX="${CMIX:-$RUN/cmix_disk}"
DICT="${DICT:-$ROOT/tools/hutter/fx2-cmix/dictionary/english.dic}"
SLICE="${SLICE:-$ROOT/enwik8}"
NAME="${1:-cmix_disk}"
BASELINE="${BASELINE:-0}"  # set to published/ladder baseline bytes if known
OUT_JSON="$ROOT/benchmarks/.hutter_100m_gate.json"
PLAIN=100000000
# /tmp is wiped on reboot — keep run logs somewhere durable.
LOGDIR="${HUTTER_LOG_DIR:-$ROOT/benchmarks/.hutter_logs}"
mkdir -p "$LOGDIR"

bash "$ROOT/benchmarks/hutter_disk_prep.sh" || exit 2
[[ -x "$CMIX" ]] || { echo "missing $CMIX"; exit 1; }
[[ -f "$SLICE" ]] || { echo "missing $SLICE"; exit 1; }
[[ $(stat -c%s "$SLICE") -eq $PLAIN ]] || echo "WARN: slice size != $PLAIN"

cd "$RUN"
FX2="$RUN/${NAME}_100m.fx2"
RT="$RUN/${NAME}_100m.out"
rm -f ppm.temp "$FX2" "$RT" "${FX2}.cmix.temp"

echo "=== 100MB gate name=$NAME cmix=$CMIX ==="
echo "START=$(date -Iseconds)"
NICE_LEVEL="${HUTTER_NICE:-0}"
# Heartbeat for crash recovery
PROGRESS="$ROOT/benchmarks/.hutter_100m_progress.json"
python3 - <<PY
import json
open("$PROGRESS","w").write(json.dumps({
  "name":"$NAME","phase":"compress","start":"$(date -Iseconds)",
  "cmix":"$CMIX","plain":$PLAIN
}, indent=2)+"\n")
PY
nice -n "$NICE_LEVEL" /usr/bin/time -v "$CMIX" -c "$DICT" "$SLICE" "$FX2" \
  >"$LOGDIR/${NAME}_100m_c.log" 2>"$LOGDIR/${NAME}_100m_c.time"
BYTES=$(stat -c%s "$FX2")
PEAK_C=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' "$LOGDIR/${NAME}_100m_c.time")
ELAPSED_C=$(awk -F: '/Elapsed \(wall clock\)/ {sub(/^[^:]*: /,""); print}' "$LOGDIR/${NAME}_100m_c.time" | head -1)

rm -f ppm.temp
python3 - <<PY
import json
open("$PROGRESS","w").write(json.dumps({
  "name":"$NAME","phase":"decompress","bytes":$BYTES,
  "peak_rss_kb_compress":$PEAK_C,"elapsed_compress":"""$ELAPSED_C""",
  "start_decompress":"$(date -Iseconds)"
}, indent=2)+"\n")
PY
nice -n "$NICE_LEVEL" /usr/bin/time -v "$CMIX" -d "$DICT" "$FX2" "$RT" \
  >"$LOGDIR/${NAME}_100m_d.log" 2>"$LOGDIR/${NAME}_100m_d.time"
PEAK_D=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' "$LOGDIR/${NAME}_100m_d.time")
ELAPSED_D=$(awk -F: '/Elapsed \(wall clock\)/ {sub(/^[^:]*: /,""); print}' "$LOGDIR/${NAME}_100m_d.time" | head -1)

cmp -s "$SLICE" "$RT" && RT_OK=OK || RT_OK=FAIL
GATE=reject
if [[ "$RT_OK" == OK ]]; then
  if (( BASELINE > 0 && BYTES < BASELINE )); then
    GATE=KEEP_100m
  elif (( BASELINE == 0 )); then
    GATE=RT_OK
  fi
fi

python3 - <<PY
import json
payload = {
  "name": "$NAME",
  "plain": $PLAIN,
  "bytes": $BYTES,
  "baseline": $BASELINE,
  "delta": $BYTES - $BASELINE if $BASELINE else None,
  "bpc": round($BYTES * 8 / $PLAIN, 4),
  "rt": "$RT_OK",
  "gate": "$GATE",
  "peak_rss_kb_compress": $PEAK_C,
  "peak_rss_kb_decompress": $PEAK_D,
  "elapsed_compress": """$ELAPSED_C""",
  "elapsed_decompress": """$ELAPSED_D""",
  "cmix": "$CMIX",
  "dict": "$DICT",
}
print(json.dumps(payload, indent=2))
open("$OUT_JSON", "w").write(json.dumps(payload, indent=2) + "\\n")
PY

echo "bytes=$BYTES rt=$RT_OK gate=$GATE peak_c=${PEAK_C}KiB peak_d=${PEAK_D}KiB"
[[ "$RT_OK" == OK ]] || exit 1
