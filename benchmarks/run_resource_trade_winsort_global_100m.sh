#!/usr/bin/env bash
# Fair @100MB: published pages vs currently banked winsort (global).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DATA="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_winsort_global_100m.log"
FINDINGS="$ROOT/benchmarks/.hutter_resource_trade_findings.md"
S1_PUB=201019
S1_GLOBAL=$(stat -c%s "$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort172277_ascii")
D1=$((S1_GLOBAL - S1_PUB))

echo "GLOBAL_100M_START $(date -Is) ΔS1=$D1" | tee "$LOG"

ORDER_A="$DATA/new_article_order_published_pages.published_backup" \
ORDER_B="$DATA/new_article_order_pub_winsort172277_pages" \
TAG=winsort_global_100m HEAD=104857600 \
OUTDIR="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort_global_100m" \
LOG="$ROOT/benchmarks/.hutter_logs/article_order_winsort_global_100m_fair.log" \
bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"

D2=$(cat "$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort_global_100m/fair_delta.txt")
JOINT=$((D1 + D2))
echo "GLOBAL_100M ΔS1=$D1 ΔS2=$D2 joint=$JOINT" | tee -a "$LOG"

if (( JOINT < -147859 )); then V=KEEP_vs_65536
elif (( JOINT < 0 )); then V=KEEP_vs_pub_only
else V=reject_revert_65536
fi
echo "verdict=$V" | tee -a "$LOG"

{
  echo ""
  echo "## Winsort global @100 MB ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| ΔS1 | $D1 ($S1_GLOBAL) |"
  echo "| Fair @100 MB ΔS2 | $D2 |"
  echo "| Joint | $JOINT |"
  echo "| vs W65536 joint −147 859 | $((JOINT + 147859)) |"
  echo "| Verdict | **$V** |"
} >> "$FINDINGS"

if [[ "$V" == reject_revert_65536 ]]; then
  bash "$ROOT/benchmarks/bank_winsort_order.sh" 65536 | tee -a "$LOG"
fi
echo "GLOBAL_100M_DONE $(date -Is)" | tee -a "$LOG"
