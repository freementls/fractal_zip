#!/usr/bin/env bash
# VocabTrim-style dict S1+S2 queue. One heavy cmix at a time.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_entity"
ORIG="$ROOT/tools/hutter/fx2-cmix/run/cmix_orig"
DICDIR="$ROOT/benchmarks/.ladder_cache/dicts"
SLICE="$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid"
BASE=198794
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_dict.log"

busy_cmix() {
  # Real cmix PIDs only (exclude shells / sandbox wrappers that mention the path).
  pgrep -x cmix_orig >/dev/null 2>&1 && return 0
  pgrep -x cmix >/dev/null 2>&1 && return 0
  pgrep -x cmix_match3m_entity >/dev/null 2>&1 && return 0
  return 1
}

echo "QUEUE_START $(date -Iseconds)" | tee -a "$LOG"
while busy_cmix; do
  echo "wait_busy $(date +%H:%M:%S)" | tee -a "$LOG"
  sleep 60
done

for d in english_entityfold_e9.dic english_entityfold_top25000.dic \
         english_entityfold_top20000.dic english_entityfold_top15000.dic \
         english_entityfold_nomarkup.dic; do
  out="$DICDIR/comp_${d}"
  if [[ -s "$out" ]]; then
    echo "S1_skip $d $(stat -c%s "$out")" | tee -a "$LOG"
    continue
  fi
  echo "S1_comp $d" | tee -a "$LOG"
  /usr/bin/time -f "wall=%e rss=%M" "$ORIG" -c "$DICDIR/$d" "$out" >>"$LOG" 2>&1 || true
  ls -la "$out" | tee -a "$LOG"
done

for tag in dict_top25k_1m:english_entityfold_top25000.dic \
           dict_top20k_1m:english_entityfold_top20000.dic \
           dict_nomarkup_1m:english_entityfold_nomarkup.dic; do
  name=${tag%%:*}
  dic=${tag##*:}
  echo "S2_screen $name" | tee -a "$LOG"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" "$name" "$CMIX" "$DICDIR/$dic" "$SLICE" "$BASE" | tee -a "$LOG"
done
echo "QUEUE_DONE $(date -Iseconds)" | tee -a "$LOG"
