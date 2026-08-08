#!/usr/bin/env bash
# After winsort256 @10m fair finishes: decide KEEP/reject and optionally screen W=128.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_winsort_decide.log"
DELTA_FILE="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort256_10m/fair_delta.txt"
S1_PUB=201019
# Prefer remap(winsort(pages)) — consistent with fair S2 transform.
WS_FILE="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_winsort256_frompages"
WS_COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort256_frompages_ascii"
NAIVE_COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort256_ascii"
FINDINGS="$ROOT/benchmarks/.hutter_resource_trade_findings.md"

echo "DECIDE_START $(date -Is)" | tee "$LOG"
if [[ ! -s "$DELTA_FILE" ]]; then
  echo "missing $DELTA_FILE — 10m fair not done" | tee -a "$LOG"
  exit 1
fi

# Authoritative S1 — frompages == naive winsort256 (verified identical); reuse ascii cmix.
if [[ -s "$NAIVE_COMP" && ! -s "$WS_COMP" ]]; then
  cp -a "$NAIVE_COMP" "$WS_COMP"
  echo "S1 reused naive winsort256 ascii cmix -> frompages label" | tee -a "$LOG"
fi
if [[ -s "$WS_FILE" && ! -s "$WS_COMP" ]]; then
  echo "=== S1 cmix remap(winsort256 pages) ===" | tee -a "$LOG"
  /usr/bin/time -f 'wall=%e rss=%M' \
    "$ROOT/tools/hutter/fx2-cmix/run/cmix_orig" -c "$WS_FILE" "$WS_COMP" >>"$LOG" 2>&1 || true
fi
if [[ -s "$WS_COMP" ]]; then
  S1_WS=$(stat -c%s "$WS_COMP")
else
  S1_WS=$(stat -c%s "$NAIVE_COMP")
  echo "WARN: using naive winsort S1=$S1_WS" | tee -a "$LOG"
fi
D2=$(cat "$DELTA_FILE")
D1=$((S1_WS - S1_PUB))
JOINT=$((D1 + D2))
echo "ΔS1=$D1 ΔS2_10m=$D2 joint_naive=$JOINT S1_ws=$S1_WS" | tee -a "$LOG"

# Keep if joint negative AND S2 loss under half the S1 save (margin for scale-up).
HALF=$(( -D1 / 2 ))
if (( D2 < 0 )); then
  VERDICT=KEEP_STRONG
elif (( JOINT < 0 && D2 < HALF )); then
  VERDICT=KEEP_WEAK
elif (( JOINT < 0 )); then
  VERDICT=BORDERLINE
else
  VERDICT=reject
fi
echo "verdict=$VERDICT (half_S1_save=$HALF)" | tee -a "$LOG"

{
  echo ""
  echo "## Winsort256 @10 MB decision ($(date -Iseconds))"
  echo ""
  echo "| | |"
  echo "|--|--:|"
  echo "| ΔS1 | $D1 |"
  echo "| Fair @10 MB ΔS2 | $D2 |"
  echo "| Joint naive | $JOINT |"
  echo "| Verdict | **$VERDICT** |"
} >> "$FINDINGS"

if [[ "$VERDICT" == reject || "$VERDICT" == BORDERLINE ]]; then
  echo "=== try winsort128 fair @10m ===" | tee -a "$LOG"
  # Need S1 for W=128 first
  WS128="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_winsort128"
  COMP128="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_winsort128_ascii"
  if [[ -s "$WS128" && ! -s "$COMP128" ]]; then
    /usr/bin/time -f 'wall=%e rss=%M' \
      "$ROOT/tools/hutter/fx2-cmix/run/cmix_orig" -c "$WS128" "$COMP128" >>"$LOG" 2>&1 || true
  fi
  echo "winsort128_S1=$(stat -c%s "$COMP128" 2>/dev/null || echo 0)" | tee -a "$LOG"
  ORDER_A="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_published_pages" \
  ORDER_B="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_pub_winsort128_pages" \
  TAG=winsort128_10m HEAD=10485760 \
  OUTDIR="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort128_10m" \
  bash "$ROOT/benchmarks/run_article_order_ab_fair.sh" | tee -a "$LOG"
fi

if [[ "$VERDICT" == KEEP_STRONG || "$VERDICT" == KEEP_WEAK ]]; then
  echo "=== install winsort256 as candidate new_article_order (backup published) ===" | tee -a "$LOG"
  PUB="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
  WS="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order_winsort256"
  if [[ ! -e "${PUB}.published_backup" ]]; then
    cp -a "$PUB" "${PUB}.published_backup"
  fi
  # Do NOT overwrite banked published yet — copy to candidate path only
  cp -a "$WS" "${PUB}.winsort256_candidate"
  echo "wrote ${PUB}.winsort256_candidate (published left intact)" | tee -a "$LOG"
fi

echo "DECIDE_DONE $(date -Is) verdict=$VERDICT" | tee -a "$LOG"
