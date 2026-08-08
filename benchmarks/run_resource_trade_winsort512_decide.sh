#!/usr/bin/env bash
# After winsort512 @10m fair_delta: decide vs W=256; optionally screen W=1024.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_winsort512_decide.log"
DELTA="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort512_10m/fair_delta.txt"
S1_PUB=201019
S1_256=167627
S1_512=$(stat -c%s "$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort512_ascii")
FINDINGS="$ROOT/benchmarks/.hutter_resource_trade_findings.md"

echo "DECIDE512_START $(date -Is)" | tee "$LOG"
[[ -s "$DELTA" ]] || { echo "missing delta"; exit 1; }
D2=$(cat "$DELTA")
D1=$((S1_512 - S1_PUB))
JOINT=$((D1 + D2))
D1_256=$((S1_256 - S1_PUB))
echo "W512 ΔS1=$D1 ΔS2=$D2 joint=$JOINT | W256 ΔS1=$D1_256 ΔS2=-70 joint=$((D1_256-70))" | tee -a "$LOG"

HALF=$(( -D1 / 2 ))
if (( D2 < 0 )); then V=KEEP_STRONG
elif (( JOINT < 0 && D2 < HALF )); then V=KEEP_WEAK
elif (( JOINT < 0 )); then V=BORDERLINE
else V=reject
fi
# Prefer 512 over 256 if joint better than W256's -33462
BEST=256
if [[ "$V" == KEEP_STRONG || "$V" == KEEP_WEAK ]]; then
  if (( JOINT < D1_256 - 70 )); then BEST=512; fi
fi
echo "verdict=$V best_window=$BEST" | tee -a "$LOG"

{
  echo ""
  echo "## Winsort512 @10 MB decision ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| ΔS1 | $D1 |"
  echo "| Fair @10 MB ΔS2 | $D2 |"
  echo "| Joint | $JOINT |"
  echo "| Verdict | **$V** |"
  echo "| Prefer vs W256 | **W=$BEST** |"
} >> "$FINDINGS"

if [[ "$V" == reject || "$V" == BORDERLINE ]]; then
  echo "keep W256 banked candidate; try W=1024 @1m next" | tee -a "$LOG"
elif [[ "$BEST" == 512 ]]; then
  bash "$ROOT/benchmarks/bank_winsort_order.sh" 512 | tee -a "$LOG"
  # Screen W=1024 S1 + fair @1m for more headroom
  WS1024="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_winsort1024"
  COMP1024="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort1024_ascii"
  if [[ -s "$WS1024" && ! -s "$COMP1024" ]]; then
    /usr/bin/time -f 'wall=%e rss=%M' \
      "$ROOT/tools/hutter/fx2-cmix/run/cmix_orig" -c "$WS1024" "$COMP1024" >>"$LOG" 2>&1 || true
  fi
  echo "W1024_S1=$(stat -c%s "$COMP1024" 2>/dev/null || echo 0)" | tee -a "$LOG"
  ORDER_A="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_published_pages.published_backup"" \
  ORDER_B="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_pub_winsort1024_pages" \
  TAG=winsort1024_1m HEAD=1048576 \
  OUTDIR="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort1024_1m" \
  bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG" || true
  # If published_pages already overwritten, use backup for A
else
  bash "$ROOT/benchmarks/bank_winsort_order.sh" 256 | tee -a "$LOG"
fi
echo "DECIDE512_DONE $(date -Is)" | tee -a "$LOG"
