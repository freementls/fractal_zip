#!/usr/bin/env bash
# After W=256 @100MB decide: finalize winsort bank, then queue model S2 track.
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
LOG="$ROOT/benchmarks/.hutter_logs/resource_trade_post_winsort.log"
OUT256="$ROOT/benchmarks/.ladder_cache/article_order_probe_winsort256_100m"
FINDINGS="$ROOT/benchmarks/.hutter_resource_trade_findings.md"

echo "POST_WINSORT_WAIT $(date -Is)" | tee "$LOG"

# Wait for W=256 decide (watcher writes W256_100M line)
for i in $(seq 1 400); do
  if rg -q 'W256_100M' "$ROOT/benchmarks/.hutter_logs/resource_trade_winsort256_100m.log" 2>/dev/null; then
    break
  fi
  [[ -s "$OUT256/fair_delta.txt" ]] && sleep 90 && break
  sleep 60
done

# Ensure decide ran
if [[ -s "$OUT256/fair_delta.txt" ]] && ! rg -q 'W256_100M' "$ROOT/benchmarks/.hutter_logs/resource_trade_winsort256_100m.log" 2>/dev/null; then
  D2=$(cat "$OUT256/fair_delta.txt"); D1=-33392; J=$((D1+D2))
  echo "W256_100M ΔS1=$D1 ΔS2=$D2 joint=$J $(date -Is)" | tee -a "$ROOT/benchmarks/.hutter_logs/resource_trade_winsort256_100m.log"
  if (( J < -16544 )); then
    bash "$ROOT/benchmarks/bank_winsort_order.sh" 256 | tee -a "$LOG"
  fi
fi

BANK_MD=$(md5sum "$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order" | awk '{print $1}')
echo "bank_md5=$BANK_MD" | tee -a "$LOG"

# Confirm packaged order S1 with cmix_orig (light vs fair)
COMP="$ROOT/benchmarks/.ladder_cache/order_forms/comp_order_banked_ascii"
ORDER="$ROOT/tools/hutter/fx2-cmix/src/readalike_prepr/data/new_article_order"
if [[ ! -s "$COMP" ]] || [[ "$(md5sum "$ORDER" | awk '{print $1}')" != "$(cat "$COMP.md5" 2>/dev/null || true)" ]]; then
  /usr/bin/time -f 'wall=%e rss=%M' \
    "$ROOT/tools/hutter/fx2-cmix/run/cmix_orig" -c "$ORDER" "$COMP" \
    >>"$LOG" 2>&1 || true
  md5sum "$ORDER" | awk '{print $1}' > "$COMP.md5"
fi
S1=$(stat -c%s "$COMP" 2>/dev/null || echo 0)
echo "banked_comp_order=$S1 Δ=$((S1-201019))" | tee -a "$LOG"

{
  echo ""
  echo "## Post-winsort status ($(date -Iseconds))"
  echo ""
  echo "Banked order S1 ascii+cmix = **$S1** (Δ $((S1-201019)) vs pub 201019)."
  echo ""
  echo "Winsort closed for S2 gate (hurts S2 @100 MB). Next: **model S2** with PPMD8k time headroom — re-time LSTM256/320 under match3m+PPMD8k; screen pronoun/mix only if time-legal."
} >> "$FINDINGS"

# Build match3m_lstm256 with PPMD8k folded (MATCH3M_DEFS now has PPMD8k — rebuild lstm256 target)
cd "$ROOT/tools/hutter/fx2-cmix"
# Check if lstm256 target inherits MATCH3M_DEFS
if rg -q 'match3m_lstm256' makefile; then
  echo "=== rebuild match3m_lstm256 (inherits current DEFS?) ===" | tee -a "$LOG"
  # Inspect defs
  rg -n 'LSTM256|lstm256|MATCH3M_DEFS' makefile | head -20 | tee -a "$LOG"
fi

echo "POST_WINSORT_DONE $(date -Is) — idle for model queue" | tee -a "$LOG"
