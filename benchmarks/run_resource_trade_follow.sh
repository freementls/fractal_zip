#!/usr/bin/env bash
# After dict VocabTrim queue: summarize S1, then fair-probe winsort256 vs published @1MB.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_follow.log"
ORIG="$ROOT/tools/hutter/fx2-cmix/run/cmix_orig"
CMIX="$ROOT/tools/hutter/fx2-cmix/run/cmix_match3m_entity"

# Match real compressor PIDs only (Linux comm is ≤15 chars; avoid cmdline false positives).
busy() {
  pgrep -x cmix_orig >/dev/null 2>&1 && return 0
  pgrep -x cmix >/dev/null 2>&1 && return 0
  # cmix_match3m_entity → first 15 chars
  pgrep -x cmix_match3m_en >/dev/null 2>&1 && return 0
  return 1
}

echo "FOLLOW_START $(date -Is)" | tee -a "$LOG"
while busy; do
  echo "wait $(date +%H:%M:%S)" | tee -a "$LOG"
  sleep 20
done

echo "=== dict S1 sizes ===" | tee -a "$LOG"
ls -la "$ROOT"/benchmarks/.ladder_cache/dicts/comp_*.dic | tee -a "$LOG" || true
rg -n 'dict_top|dict_nomarkup|"delta"' "$ROOT/benchmarks/.hutter_slice_screens.jsonl" | tee -a "$LOG" || true

# S1 apples-to-apples: cmix of winsort ascii
WS_ASCII="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_winsort256"
WS_COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort256_ascii"
if [[ -s "$WS_ASCII" && ! -s "$WS_COMP" ]]; then
  echo "=== winsort256 ascii S1 cmix ===" | tee -a "$LOG"
  /usr/bin/time -f 'wall=%e rss=%M' "$ORIG" -c "$WS_ASCII" "$WS_COMP" >>"$LOG" 2>&1 || true
  echo "winsort256_ascii_cmix=$(stat -c%s "$WS_COMP" 2>/dev/null || echo 0) pub=201019" | tee -a "$LOG"
fi

echo "=== winsort256 fair @1m vs published ===" | tee -a "$LOG"
ORDER_A="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_published_pages" \
ORDER_B="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_pub_winsort256_pages" \
TAG=winsort256_1m HEAD=1048576 \
OUTDIR="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort256_1m" \
bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"

# Softer count-threshold dict if tops rejected
GE100="$ROOT/benchmarks/.ladder_cache/dicts/english_entityfold_ge100.dic"
if [[ -s "$GE100" ]]; then
  echo "=== ge100 dict S1+S2 @1m ===" | tee -a "$LOG"
  OUT="$ROOT/benchmarks/.ladder_cache/dicts/comp_english_entityfold_ge100.dic"
  if [[ ! -s "$OUT" ]]; then
    /usr/bin/time -f 'wall=%e rss=%M' "$ORIG" -c "$GE100" "$OUT" >>"$LOG" 2>&1 || true
  fi
  echo "ge100_S1=$(stat -c%s "$OUT" 2>/dev/null || echo 0)" | tee -a "$LOG"
  bash "$ROOT/benchmarks/run_hutter_slice_screen.sh" dict_ge100_1m \
    "$CMIX" "$GE100" \
    "$ROOT/benchmarks/.ladder_cache/enwik8_1m_skip10.mid" \
    198794 | tee -a "$LOG" || true
fi

echo "FOLLOW_DONE $(date -Is)" | tee -a "$LOG"
