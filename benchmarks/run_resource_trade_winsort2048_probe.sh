#!/usr/bin/env bash
# Wait for winsort2048 S1 cmix, then fair @1MB; promote to @10MB if joint vs W1024 looks promising.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_winsort2048.log"
FINDINGS="$ROOT/benchmarks/.hutter_resource_trade_findings.md"
DATA="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data"
COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort2048_ascii"
S1_LOG="$ROOT/benchmarks/.hutter_logs/winsort2048_s1.log"
S1_PUB=201019
S1_1024=134019
ORDER_A="$DATA/new_article_order_published_pages.published_backup"
ORDER_B="$DATA/new_article_order_pub_winsort2048_pages"

echo "W2048_PROBE_START $(date -Is)" | tee -a "$LOG"

# Wait for S1 (cmix_orig writing COMP, or COMP already sized)
for i in $(seq 1 120); do
  if [[ -s "$COMP" ]] && ! pgrep -x cmix_orig >/dev/null 2>&1; then
    break
  fi
  # also accept finished log with wall=
  if rg -q '^wall=' "$S1_LOG" 2>/dev/null && [[ -s "$COMP" ]]; then
    break
  fi
  echo "wait_s1 $(date +%H:%M:%S) size=$(stat -c%s "$COMP" 2>/dev/null || echo 0)" | tee -a "$LOG"
  sleep 30
done

[[ -s "$COMP" ]] || { echo "S1 missing"; exit 1; }
S1=$(stat -c%s "$COMP")
D1=$((S1 - S1_PUB))
echo "W2048_S1=$S1 ΔS1=$D1 vs W1024 ΔS1=$((S1_1024 - S1_PUB))" | tee -a "$LOG"

{
  echo ""
  echo "## Winsort2048 probe ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| S1 ascii+cmix | **$S1** (Δ **$D1** vs pub) |"
  echo "| vs W1024 S1 | $((S1 - S1_1024)) |"
  echo "| Fair @1 MB | **running** |"
} >> "$FINDINGS"

OUT1="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort2048_1m"
mkdir -p "$OUT1"
# Reuse published fair_a @1m if identical page set
if [[ -s "$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort1024_1m/fair_a.fx2" ]]; then
  # Still rebuild bins to get order_moved; skip A compress if bin matches after emit
  :
fi

ORDER_A="$ORDER_A" ORDER_B="$ORDER_B" TAG=winsort2048_1m HEAD=1048576 \
  OUTDIR="$OUT1" LOG="$ROOT/benchmarks/.hutter_logs/article_order_winsort2048_1m_fair.log" \
  bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"

D2_1M=$(cat "$OUT1/fair_delta.txt")
JOINT1=$((D1 + D2_1M))
echo "fair1m ΔS2=$D2_1M joint_naive=$JOINT1" | tee -a "$LOG"

# Promote to 10m if S1 better than W1024 by enough that even large S2 could win,
# or joint @1m not catastrophic. Rule: need ΔS1 better than -67000 by ≥5KB OR joint1m < -60KB.
NEED_10M=0
if (( D1 < S1_1024 - S1_PUB - 5000 )); then NEED_10M=1; fi
if (( JOINT1 < -60000 )); then NEED_10M=1; fi
# Always promote if S1 improved at all vs W1024 (even tiny) — 10m is the real gate
if (( S1 < S1_1024 )); then NEED_10M=1; fi
# If S1 worse than W1024, skip unless somehow S2 hugely negative (won't be)
if (( S1 >= S1_1024 )); then
  echo "S1 not better than W1024; skip @10m unless ΔS2 strongly negative (won't screen)" | tee -a "$LOG"
  if (( D2_1M >= 0 )); then NEED_10M=0; fi
fi

if (( NEED_10M == 0 )); then
  echo "verdict=SKIP_10M keep W1024" | tee -a "$LOG"
  {
    echo ""
    echo "## Winsort2048 @1 MB — skip @10 MB"
    echo ""
    echo "| ΔS1 | $D1 |"
    echo "| Fair @1 MB ΔS2 | $D2_1M |"
    echo "| Verdict | keep W1024 |"
  } >> "$FINDINGS"
  echo "W2048_PROBE_DONE $(date -Is)" | tee -a "$LOG"
  exit 0
fi

OUT10="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort2048_10m"
mkdir -p "$OUT10"
FAIR_A_SRC="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort256_10m/fair_a.fx2"
ORDER_A="$ORDER_A" ORDER_B="$ORDER_B" TAG=winsort2048_10m HEAD=10485760 \
  OUTDIR="$OUT10" LOG="$ROOT/benchmarks/.hutter_logs/article_order_winsort2048_10m_fair.log" \
  REUSE_FAIR_A="$FAIR_A_SRC" \
  bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"

D2=$(cat "$OUT10/fair_delta.txt")
JOINT=$((D1 + D2))
D1_1024=$((S1_1024 - S1_PUB))
JOINT_1024=$((D1_1024 + 2053))

if (( D2 < 0 )); then V=KEEP_STRONG
elif (( JOINT < JOINT_1024 && JOINT < 0 )); then V=KEEP_WEAK
elif (( JOINT < 0 )); then V=BORDERLINE
else V=reject
fi

echo "W2048 @10m ΔS1=$D1 ΔS2=$D2 joint=$JOINT vs W1024 joint=$JOINT_1024 verdict=$V" | tee -a "$LOG"
{
  echo ""
  echo "## Winsort2048 @10 MB decision ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| ΔS1 | $D1 ($S1) |"
  echo "| Fair @10 MB ΔS2 | $D2 |"
  echo "| Joint | $JOINT |"
  echo "| vs W1024 joint −64947 | $((JOINT - JOINT_1024)) |"
  echo "| Verdict | **$V** |"
} >> "$FINDINGS"

if [[ "$V" == KEEP_STRONG || "$V" == KEEP_WEAK ]]; then
  if (( JOINT < JOINT_1024 )); then
    bash "$ROOT/benchmarks/bank_winsort_order.sh" 2048 | tee -a "$LOG"
  else
    echo "joint not better than W1024; leave banked" | tee -a "$LOG"
  fi
fi
echo "W2048_PROBE_DONE $(date -Is)" | tee -a "$LOG"
