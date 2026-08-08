#!/usr/bin/env bash
# After W=4096 @100MB fair_delta: decide, then chain next W @100MB (reuse fair_a).
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
DATA="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_winsort4096_100m.log"
FINDINGS="$ROOT/benchmarks/.hutter_resource_trade_findings.md"
OUT4096="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort4096_100m"
FAIR_A="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort_global_100m/fair_a.fx2"
S1_PUB=201019
ORDER_A="$DATA/new_article_order_published_pages.published_backup"

echo "CHAIN4096_WAIT $(date -Is)" | tee -a "$LOG"
for i in $(seq 1 300); do
  [[ -s "$OUT4096/fair_delta.txt" ]] && break
  sleep 60
done
[[ -s "$OUT4096/fair_delta.txt" ]] || { echo "timeout"; exit 1; }

D2=$(cat "$OUT4096/fair_delta.txt")
S1=$(stat -c%s "$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort4096_ascii")
D1=$((S1 - S1_PUB))
JOINT=$((D1 + D2))
echo "W4096_100M ΔS1=$D1 ΔS2=$D2 joint=$JOINT $(date -Is)" | tee -a "$LOG"

if (( JOINT < -20000 )); then V=KEEP_STRONG
elif (( JOINT < 0 )); then V=KEEP_WEAK
else V=reject
fi
{
  echo ""
  echo "## Winsort4096 @100 MB ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| ΔS1 | $D1 ($S1) |"
  echo "| Fair @100 MB ΔS2 | $D2 |"
  echo "| Joint | $JOINT |"
  echo "| Verdict | **$V** |"
} >> "$FINDINGS"
echo "verdict=$V" | tee -a "$LOG"

# Pick next probe: projection peak ~W512; measure W=512 and W=2048 empirically.
# Bank best so far among {4096 if KEEP, else none} then update after each probe.
BEST_W=0
BEST_J=0
if (( JOINT < 0 )); then
  BEST_W=4096
  BEST_J=$JOINT
  bash "$ROOT/benchmarks/bank_winsort_order.sh" 4096 | tee -a "$LOG"
else
  bash "$ROOT/benchmarks/bank_winsort_order.sh" 512 | tee -a "$LOG"
fi

run100() {
  local W=$1
  local S1W COMP D1W OUT D2W JW
  COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort${W}_ascii"
  [[ -s "$COMP" ]] || return 0
  S1W=$(stat -c%s "$COMP")
  D1W=$((S1W - S1_PUB))
  OUT="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort${W}_100m"
  if [[ -s "$OUT/fair_delta.txt" ]]; then
    D2W=$(cat "$OUT/fair_delta.txt")
  else
    echo "=== fair @100m W=$W ===" | tee -a "$LOG"
    ORDER_A="$ORDER_A" \
    ORDER_B="$DATA/new_article_order_pub_winsort${W}_pages" \
    TAG="winsort${W}_100m" HEAD=104857600 \
    OUTDIR="$OUT" \
    LOG="$ROOT/benchmarks/.hutter_logs/article_order_winsort${W}_100m_fair.log" \
    REUSE_FAIR_A="$FAIR_A" \
    bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"
    D2W=$(cat "$OUT/fair_delta.txt")
  fi
  JW=$((D1W + D2W))
  echo "W${W}_100M ΔS1=$D1W ΔS2=$D2W joint=$JW" | tee -a "$LOG"
  {
    echo ""
    echo "## Winsort${W} @100 MB ($(date -Iseconds))"
    echo ""
    echo "| | |"
    echo "|--|--:|"
    echo "| ΔS1 | $D1W |"
    echo "| Fair @100 MB ΔS2 | $D2W |"
    echo "| Joint | $JW |"
  } >> "$FINDINGS"
  if (( JW < BEST_J )) || (( BEST_W == 0 && JW < 0 )); then
    if (( JW < 0 )); then
      BEST_W=$W
      BEST_J=$JW
      bash "$ROOT/benchmarks/bank_winsort_order.sh" "$W" | tee -a "$LOG"
    fi
  fi
}

# Always measure W=512 (projected best) and W=2048 (mid); if 4096 KEEP also try 8192.
run100 512
run100 2048
if [[ "$V" == KEEP_STRONG || "$V" == KEEP_WEAK ]]; then
  run100 8192
else
  run100 1024
fi

echo "BEST_BANK W=$BEST_W joint=$BEST_J $(date -Is)" | tee -a "$LOG"
if (( BEST_W > 0 )); then
  bash "$ROOT/benchmarks/bank_winsort_order.sh" "$BEST_W" | tee -a "$LOG"
fi
echo "CHAIN4096_DONE $(date -Is)" | tee -a "$LOG"
