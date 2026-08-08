#!/usr/bin/env bash
# Single slice screen: compress + RT, record to .hutter_slice_screens.jsonl
# Usage: bash benchmarks/run_hutter_slice_screen.sh TAG CMIX DICT SLICE BASELINE
# Example:
#   bash benchmarks/run_hutter_slice_screen.sh disk10m_verify run/cmix_disk dictionary/english.dic benchmarks/.ladder_cache/enwik8_10m_skip20.mid 1751514
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
bash "$ROOT/benchmarks/hutter_memory_guard.sh"
TAG="${1:?tag}"; CMIX="${2:?cmix}"; DICT="${3:?dict}"; SLICE="${4:?slice}"; BASELINE="${5:?baseline}"
[[ "$CMIX" != /* ]] && CMIX="$ROOT/tools/hutter/fx2-cmix/$CMIX"
[[ "$DICT" != /* ]] && DICT="$ROOT/tools/hutter/fx2-cmix/$DICT"
[[ "$SLICE" != /* ]] && SLICE="$ROOT/$SLICE"
OUT="$ROOT/benchmarks/.hutter_slice_screens.jsonl"
RUN="${HUTTER_WORKDIR:-$ROOT/tools/hutter/fx2-cmix/run}"
mkdir -p "$RUN"
PLAIN=$(stat -c%s "$SLICE")
FX2="$RUN/screen_${TAG}.fx2"
RT="$RUN/screen_${TAG}.out"
cd "$RUN"
rm -f ppm.temp "$FX2" "$RT" "${FX2}.cmix.temp"
echo "=== slice_screen $TAG plain=$PLAIN baseline=$BASELINE ==="
echo "START=$(date -Iseconds)"
/usr/bin/time -v "$CMIX" -c "$DICT" "$SLICE" "$FX2" >"$ROOT/benchmarks/.hutter_logs/screen_${TAG}_c.log" 2>"$ROOT/benchmarks/.hutter_logs/screen_${TAG}_c.time"
BYTES=$(stat -c%s "$FX2")
PEAK_C=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' "$ROOT/benchmarks/.hutter_logs/screen_${TAG}_c.time")
ELAPSED_C=$(awk -F: '/Elapsed \(wall clock\)/ {sub(/^[^:]*: /,""); gsub(/[\t\r\n]/," "); print}' "$ROOT/benchmarks/.hutter_logs/screen_${TAG}_c.time" | head -1 | sed 's/^[[:space:]]*//;s/[[:space:]]*$//;s/"/\\"/g')
# Keep only the h:mm:ss token if present
ELAPSED_C=$(grep -oE '[0-9]+:[0-9]{2}(:[0-9]{2})?(\.[0-9]+)?' <<<"$ELAPSED_C" | tail -1)
DELTA=$((BYTES - BASELINE))
GATE=reject; (( BYTES < BASELINE )) && GATE=KEEP
rm -f ppm.temp
/usr/bin/time -v "$CMIX" -d "$DICT" "$FX2" "$RT" >"$ROOT/benchmarks/.hutter_logs/screen_${TAG}_d.log" 2>"$ROOT/benchmarks/.hutter_logs/screen_${TAG}_d.time"
PEAK_D=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' "$ROOT/benchmarks/.hutter_logs/screen_${TAG}_d.time")
ELAPSED_D=$(awk -F: '/Elapsed \(wall clock\)/ {sub(/^[^:]*: /,""); gsub(/[\t\r\n]/," "); print}' "$ROOT/benchmarks/.hutter_logs/screen_${TAG}_d.time" | head -1 | sed 's/^[[:space:]]*//;s/[[:space:]]*$//;s/"/\\"/g')
ELAPSED_D=$(grep -oE '[0-9]+:[0-9]{2}(:[0-9]{2})?(\.[0-9]+)?' <<<"$ELAPSED_D" | tail -1)
cmp -s "$SLICE" "$RT" && RT_OK=OK || RT_OK=FAIL
printf '{"tag":"slice","name":"%s","plain":%s,"bytes":%s,"baseline":%s,"delta":%s,"gate":"%s","rt":"%s","peak_rss_kb_c":%s,"peak_rss_kb_d":%s,"elapsed_c":"%s","elapsed_d":"%s","cmix":"%s","dict":"%s"}\n' \
  "$TAG" "$PLAIN" "$BYTES" "$BASELINE" "$DELTA" "$GATE" "$RT_OK" "${PEAK_C:-0}" "${PEAK_D:-0}" "$ELAPSED_C" "$ELAPSED_D" "$CMIX" "$DICT" | tee -a "$OUT"
echo "DONE bytes=$BYTES delta=$DELTA gate=$GATE rt=$RT_OK elapsed_c=$ELAPSED_C"
rm -f ppm.temp
[[ "$RT_OK" == OK ]] || exit 1
