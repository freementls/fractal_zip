#!/usr/bin/env bash
# Screen enwik9-global dict variants at 1MB then 10MB (one at a time).
# Usage: bash benchmarks/run_hutter_dict_global_screen.sh [prune0|prune1|reorder|all]
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
RUN="$ROOT/tools/hutter/fx2-cmix/run"
CMIX="${CMIX:-$RUN/cmix_disk}"
DICT_DIR="$ROOT/benchmarks/.ladder_cache/dicts"
OUT="$ROOT/benchmarks/.hutter_dict_global_screens.jsonl"
BASELINE_1M=198744
BASELINE_10M=1751514

screen_one() {
  local name=$1 rung=$2
  local dic="$DICT_DIR/enwik9_global_${name}.dic"
  [[ -f "$dic" ]] || { echo "missing $dic"; return 1; }
  local slice baseline tag
  case "$rung" in
    1m) slice="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"; baseline=$BASELINE_1M ;;
    10m) slice="$ROOT/benchmarks/.ladder_cache/enwik8_10m_skip20.mid"; baseline=$BASELINE_10M ;;
    *) echo "bad rung"; return 1 ;;
  esac
  tag="${name}_${rung}"
  local fx2="$RUN/dictg_${tag}.fx2"
  local rt="$RUN/dictg_${tag}.out"
  cd "$RUN"
  rm -f ppm.temp "$fx2" "$rt" "${fx2}.cmix.temp"
  echo "=== dict_global $tag baseline=$baseline ==="
  nice -n 19 /usr/bin/time -v "$CMIX" -c "$dic" "$slice" "$fx2" >"/tmp/dictg_${tag}_c.log" 2>"/tmp/dictg_${tag}_c.time"
  local bytes rss
  bytes=$(stat -c%s "$fx2")
  rss=$(awk -F: '/Maximum resident/ {gsub(/^[ \t]+/,"",$2); print $2+0}' "/tmp/dictg_${tag}_c.time")
  local delta=$((bytes - baseline)) gate=reject
  (( bytes < baseline )) && gate=KEEP
  rm -f ppm.temp
  nice -n 19 "$CMIX" -d "$dic" "$fx2" "$rt" >>"/tmp/dictg_${tag}_c.log" 2>&1
  local rt_ok=FAIL; cmp -s "$slice" "$rt" && rt_ok=OK
  printf '{"tag":"dict_global","name":"%s","rung":"%s","bytes":%s,"baseline":%s,"delta":%s,"gate":"%s","rt":"%s","peak_rss_kb":%s}\n' \
    "$name" "$rung" "$bytes" "$baseline" "$delta" "$gate" "$rt_ok" "${rss:-0}" | tee -a "$OUT"
}

variants="${1:-reorder}"
[[ "$variants" == all ]] && variants="prune0 prune1 reorder"

for v in $variants; do
  screen_one "$v" 1m
done

echo "1MB screens done; run 10MB manually for KEEP candidates only"
