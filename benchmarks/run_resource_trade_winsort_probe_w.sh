#!/usr/bin/env bash
# Generic winsort W probe: wait S1, fair @1MB, fair @10MB (reuse A), bank if joint beats CURRENT_BANK_S1.
# Usage: W=4096 BANK_S1=117373 BANK_D2=3050 bash benchmarks/run_resource_trade_winsort_probe_w.sh
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
W="${W:?set W}"
BANK_S1="${BANK_S1:?}"
BANK_D2="${BANK_D2:?}"
S1_PUB=201019
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_winsort${W}.log"
FINDINGS="$ROOT/benchmarks/.hutter_resource_trade_findings.md"
DATA="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data"
COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort${W}_ascii"
S1_LOG="$ROOT/benchmarks/.hutter_logs/winsort${W}_s1.log"
ORDER_A="$DATA/new_article_order_published_pages.published_backup"
ORDER_B="$DATA/new_article_order_pub_winsort${W}_pages"

echo "W${W}_PROBE_START $(date -Is)" | tee -a "$LOG"

for i in $(seq 1 120); do
  if [[ -s "$COMP" ]] && ! pgrep -x cmix_orig >/dev/null 2>&1; then break; fi
  if rg -q '^wall=' "$S1_LOG" 2>/dev/null && [[ -s "$COMP" ]]; then break; fi
  echo "wait_s1 $(date +%H:%M:%S) size=$(stat -c%s "$COMP" 2>/dev/null || echo 0)" | tee -a "$LOG"
  sleep 30
done
[[ -s "$COMP" ]] || { echo "S1 missing"; exit 1; }
S1=$(stat -c%s "$COMP")
D1=$((S1 - S1_PUB))
echo "W${W}_S1=$S1 ΔS1=$D1 vs bank S1=$BANK_S1" | tee -a "$LOG"

{
  echo ""
  echo "## Winsort${W} probe ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| S1 ascii+cmix | **$S1** (Δ **$D1**) |"
  echo "| vs bank S1 | $((S1 - BANK_S1)) |"
} >> "$FINDINGS"

OUT1="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort${W}_1m"
ORDER_A="$ORDER_A" ORDER_B="$ORDER_B" TAG="winsort${W}_1m" HEAD=1048576 \
  OUTDIR="$OUT1" LOG="$ROOT/benchmarks/.hutter_logs/article_order_winsort${W}_1m_fair.log" \
  REUSE_FAIR_A="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort2048_1m/fair_a.fx2" \
  bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"

D2_1M=$(cat "$OUT1/fair_delta.txt")
JOINT1=$((D1 + D2_1M))
echo "fair1m ΔS2=$D2_1M joint_naive=$JOINT1" | tee -a "$LOG"

if (( S1 >= BANK_S1 )) && (( D2_1M >= 0 )); then
  echo "verdict=SKIP_10M S1 not better" | tee -a "$LOG"
  echo "W${W}_PROBE_DONE $(date -Is)" | tee -a "$LOG"
  exit 0
fi

OUT10="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort${W}_10m"
FAIR_A_SRC="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort256_10m/fair_a.fx2"
ORDER_A="$ORDER_A" ORDER_B="$ORDER_B" TAG="winsort${W}_10m" HEAD=10485760 \
  OUTDIR="$OUT10" LOG="$ROOT/benchmarks/.hutter_logs/article_order_winsort${W}_10m_fair.log" \
  REUSE_FAIR_A="$FAIR_A_SRC" \
  bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"

D2=$(cat "$OUT10/fair_delta.txt")
JOINT=$((D1 + D2))
BANK_JOINT=$((BANK_S1 - S1_PUB + BANK_D2))

if (( D2 < 0 )); then V=KEEP_STRONG
elif (( JOINT < BANK_JOINT && JOINT < 0 )); then V=KEEP_WEAK
elif (( JOINT < 0 )); then V=BORDERLINE
else V=reject
fi

echo "W${W} @10m ΔS1=$D1 ΔS2=$D2 joint=$JOINT vs bank joint=$BANK_JOINT verdict=$V" | tee -a "$LOG"
{
  echo ""
  echo "## Winsort${W} @10 MB decision ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| ΔS1 | $D1 ($S1) |"
  echo "| Fair @10 MB ΔS2 | $D2 |"
  echo "| Joint | $JOINT |"
  echo "| vs bank joint $BANK_JOINT | $((JOINT - BANK_JOINT)) |"
  echo "| Verdict | **$V** |"
} >> "$FINDINGS"

if [[ "$V" == KEEP_STRONG || "$V" == KEEP_WEAK ]]; then
  if (( JOINT < BANK_JOINT )); then
    bash "$ROOT/benchmarks/bank_winsort_order.sh" "$W" | tee -a "$LOG"
  else
    echo "joint not better than bank; leave banked" | tee -a "$LOG"
  fi
fi
echo "W${W}_PROBE_DONE $(date -Is)" | tee -a "$LOG"
